/* CEK PER-INVOICE DOC: Operasional vs GL (cutoff @end).
   bayar_op = transfer + cn + pph + memo_settlement      [operasional]
   gl_naik  = BBM (via no_order) + memo naik (invoice)    [GL 21180.200 kredit]
   gl_turun = bayar + memo turun (via invoice)            [GL 21180.200 debet]
   op_saldo = konfir - bayar_op
   gl_saldo = gl_naik - gl_turun
   selisih  = op_saldo - gl_saldo   (0 = cocok)
   NB: pasangan BBM-dobel (no_order sama) tidak bisa dipecah per-invoice di sisi
       gl_naik; nilainya menumpuk di invoice asli. Lihat secara berpasangan. */
SET NOCOUNT ON;
DECLARE @end date = '2026-05-31';
WITH konf AS (
  SELECT kpd.nomor inv, MIN(kpdd.kode_unit) unit, kpd.tgl_bayar,
         CAST(SUM(kpdd.total) AS decimal(18,2)) konfir
  FROM konfirmasi_pembayaran_doc kpd JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id
  WHERE kpd.tgl_bayar<=@end GROUP BY kpd.nomor, kpd.tgl_bayar
),
trf AS (SELECT rpd.no_bayar inv, SUM(rpd.transfer) transfer FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='DOC' AND rp.tgl_realisasi<=@end GROUP BY rpd.no_bayar),
cn AS (SELECT nomor inv, SUM(pakai) cn FROM cn_post_det cpd JOIN cn_post cp ON cpd.id_header=cp.id WHERE cp.tanggal<=@end GROUP BY nomor),
memo AS ( -- memo settlement (turun hutang lewat memorial) per invoice
  SELECT dj.invoice inv, SUM(dj.nominal) memo_set FROM det_jurnal dj
  WHERE dj.coa_tujuan='21180.200' AND dj.kode_trans LIKE 'MM%' AND dj.invoice LIKE 'BYD/%' AND dj.tanggal<=@end
  GROUP BY dj.invoice
),
gln AS ( -- GL naik per invoice: BBM via no_order + memo naik via invoice
  SELECT inv, SUM(v) gl_naik FROM (
    SELECT kpd.nomor inv, dj.nominal v FROM det_jurnal dj JOIN terima_doc td ON dj.tbl_name='terima_doc' AND dj.tbl_id=CAST(td.id AS varchar) JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.no_order=td.no_order JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id WHERE dj.coa_asal='21180.200' AND dj.tanggal<=@end
    UNION ALL
    SELECT dj.invoice, dj.nominal FROM det_jurnal dj WHERE dj.coa_asal='21180.200' AND dj.kode_trans LIKE 'MM%' AND dj.invoice LIKE 'BYD/%' AND dj.tanggal<=@end
  ) z GROUP BY inv
),
glt AS ( -- GL turun per invoice via invoice col
  SELECT dj.invoice inv, SUM(dj.nominal) gl_turun FROM det_jurnal dj WHERE dj.coa_tujuan='21180.200' AND dj.invoice LIKE 'BYD/%' AND dj.tanggal<=@end GROUP BY dj.invoice
),
calc AS (
  SELECT k.inv, k.unit, k.konfir,
    CAST(ISNULL(t.transfer,0) AS decimal(18,2)) bayar_transfer,
    CAST(ISNULL(c.cn,0) AS decimal(18,2)) bayar_cn,
    CAST( (CASE WHEN k.tgl_bayar<='2025-09-20' THEN 0 WHEN k.tgl_bayar>='2026-01-01' THEN (k.konfir-ISNULL(c.cn,0))*0.0025 ELSE k.konfir*0.0025 END) AS decimal(18,2)) bayar_pph,
    CAST(ISNULL(m.memo_set,0) AS decimal(18,2)) memo_settlement,
    CAST(ISNULL(g.gl_naik,0) AS decimal(18,2)) gl_naik,
    CAST(ISNULL(gt.gl_turun,0) AS decimal(18,2)) gl_turun
  FROM konf k
  LEFT JOIN trf t ON t.inv=k.inv LEFT JOIN cn c ON c.inv=k.inv
  LEFT JOIN memo m ON m.inv=k.inv
  LEFT JOIN gln g ON g.inv=k.inv LEFT JOIN glt gt ON gt.inv=k.inv
),
fin AS (
  SELECT *,
    (bayar_transfer+bayar_cn+bayar_pph+memo_settlement) AS bayar_op,
    (konfir-(bayar_transfer+bayar_cn+bayar_pph+memo_settlement)) AS op_saldo,
    (gl_naik-gl_turun) AS gl_saldo,
    (konfir-(bayar_transfer+bayar_cn+bayar_pph+memo_settlement))-(gl_naik-gl_turun) AS selisih
  FROM calc
)
SELECT inv, unit, konfir,
       CAST(bayar_op AS decimal(18,2)) bayar_op, memo_settlement,
       gl_naik, gl_turun,
       CAST(op_saldo AS decimal(18,2)) op_saldo,
       CAST(gl_saldo AS decimal(18,2)) gl_saldo,
       CAST(selisih AS decimal(18,2)) selisih
FROM fin
WHERE ABS(selisih) > 0.5
ORDER BY ABS(selisih) DESC;
