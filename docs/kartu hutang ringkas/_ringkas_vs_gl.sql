SET NOCOUNT ON;
DECLARE @start date = '2026-06-01';

WITH trans AS (
    /* DEBET konfir DOC */
    SELECT kpdd.kode_unit unit, kpdd.total debet, 0.0 kredit
    FROM konfirmasi_pembayaran_doc_det kpdd
    JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
    WHERE kpd.tgl_bayar < @start

    UNION ALL
    /* DEBET inv_mm (INVOICE LEWAT MEMO) DOC */
    SELECT m.unit, mi.nilai, 0.0
    FROM mmitem mi
    LEFT JOIN mm m ON mi.no_mm=m.no_mm
    WHERE mi.coa_asal='21180.200'
      AND CAST(mi.tgl_mm AS date) < @start
      AND NOT EXISTS (
        SELECT 1 FROM (
          SELECT nomor FROM konfirmasi_pembayaran_doc
          UNION ALL SELECT nomor FROM konfirmasi_pembayaran_pakan
          UNION ALL SELECT nomor FROM konfirmasi_pembayaran_voadip
          UNION ALL SELECT nomor FROM konfirmasi_pembayaran_oa_pakan
          UNION ALL SELECT ISNULL(NULLIF(invoice,''),nomor) FROM konfirmasi_pembayaran_peternak
        ) kk WHERE kk.nomor=mi.no_invoice)

    UNION ALL
    /* KREDIT transfer+PPh DOC */
    SELECT konfir.kode_unit, 0.0,
      CASE WHEN konfir.tanggal <= '2025-09-20' THEN rpd.transfer ELSE rpd.transfer+konfir.pph END
    FROM realisasi_pembayaran_det rpd
    JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
    JOIN (
        SELECT kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar tanggal,
          ((kpdd.total - CASE WHEN kpd.tgl_bayar>='2026-01-01' THEN ISNULL((SELECT SUM(cpd.pakai) FROM cn_post_det cpd WHERE cpd.nomor=kpd.nomor),0) ELSE 0 END)*(0.25/100)) pph
        FROM konfirmasi_pembayaran_doc_det kpdd
        JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
        GROUP BY kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar, kpdd.total
    ) konfir ON rpd.no_bayar=konfir.nomor
    WHERE rp.tgl_realisasi IS NOT NULL AND rp.tgl_realisasi < @start
      AND rpd.transaksi = 'DOC'

    UNION ALL
    /* KREDIT cn_post DOC */
    SELECT kpdd.kode_unit, 0.0, cpd.pakai
    FROM cn_post_det cpd
    JOIN cn_post cp ON cpd.id_header=cp.id
    JOIN konfirmasi_pembayaran_doc kpd ON kpd.nomor=cpd.nomor
    JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id
    WHERE cp.tanggal < @start AND cp.jenis_cn='DOC'

    UNION ALL
    /* KREDIT BAYAR LEWAT MEMO DOC */
    SELECT m.unit, 0.0, mi.nilai
    FROM mmitem mi
    LEFT JOIN mm m ON mi.no_mm=m.no_mm
    WHERE mi.coa_tujuan='21180.200'
      AND CAST(mi.tgl_mm AS date) < @start
)
SELECT
  ISNULL(t.unit,'(null)') unit,
  CAST(SUM(t.debet) AS decimal(18,2)) debet,
  CAST(SUM(t.kredit) AS decimal(18,2)) kredit,
  CAST(SUM(t.debet)-SUM(t.kredit) AS decimal(18,2)) ringkas_saldo,
  CAST(-ISNULL(sb.saldo,0) AS decimal(18,2)) gl_saldo,
  CAST((SUM(t.debet)-SUM(t.kredit)) - (-ISNULL(sb.saldo,0)) AS decimal(18,2)) selisih
FROM trans t
LEFT JOIN (SELECT unit, SUM(saldo_awal) saldo FROM saldo_bulanan WHERE coa='21180.200' AND tanggal='2026-06-01' GROUP BY unit) sb
  ON sb.unit=t.unit
GROUP BY t.unit, sb.saldo
ORDER BY t.unit;
