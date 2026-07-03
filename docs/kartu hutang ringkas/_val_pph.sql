SET NOCOUNT ON;
DECLARE @end date='2026-06-14';
-- PPh aktual GL per invoice (24622.000 -> 21180.200)
WITH gl_pph AS (
  SELECT dj.invoice inv, SUM(dj.nominal) pph_gl
  FROM det_jurnal dj WHERE dj.coa_asal='24622.000' AND dj.coa_tujuan='21180.200' AND dj.invoice LIKE 'BYD/%' AND dj.tanggal<=@end
  GROUP BY dj.invoice
),
op AS (
  SELECT rpd.no_bayar inv, SUM(rpd.transfer) transfer, MAX(kpd.tgl_bayar) tgl_bayar
  FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
  LEFT JOIN konfirmasi_pembayaran_doc kpd ON kpd.nomor=rpd.no_bayar
  WHERE rpd.transaksi='DOC' AND rp.tgl_realisasi<=@end GROUP BY rpd.no_bayar
)
SELECT TOP 15 o.inv, o.tgl_bayar, o.transfer,
   CAST(g.pph_gl AS decimal(18,2)) pph_gl,
   CAST(o.transfer*0.0025/0.9975 AS decimal(18,2)) pph_derived,
   CAST(g.pph_gl - o.transfer*0.0025/0.9975 AS decimal(18,4)) beda
FROM op o JOIN gl_pph g ON g.inv=o.inv
WHERE ABS(g.pph_gl - o.transfer*0.0025/0.9975) > 0.5
ORDER BY ABS(g.pph_gl - o.transfer*0.0025/0.9975) DESC;
-- ringkasan kecocokan
SELECT
  SUM(CASE WHEN ABS(g.pph_gl - o.transfer*0.0025/0.9975) <= 0.5 THEN 1 ELSE 0 END) cocok,
  SUM(CASE WHEN ABS(g.pph_gl - o.transfer*0.0025/0.9975) > 0.5 THEN 1 ELSE 0 END) beda
FROM op o JOIN gl_pph g ON g.inv=o.inv;
