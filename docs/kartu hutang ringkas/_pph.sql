SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
-- PPh DOC saldo awal (konfir tgl_bayar < start, gross sebelum Jan 2026, net-of-cn sesudah)
WITH pph_cn AS (
  SELECT kpd.nomor, SUM(cpd.pakai) cn FROM konfirmasi_pembayaran_doc kpd
  JOIN cn_post_det cpd ON cpd.nomor=kpd.nomor
  JOIN cn_post cp ON cpd.id_header=cp.id
  WHERE kpd.tgl_bayar < @start
  GROUP BY kpd.nomor
)
SELECT CAST(SUM(
  CASE WHEN kpd.tgl_bayar <= '2025-09-20' THEN 0
       WHEN kpd.tgl_bayar >= '2026-01-01' THEN (kpdd.total - ISNULL(pc.cn,0)) * 0.0025
       ELSE kpdd.total * 0.0025 END
) AS decimal(18,2)) pph_saldo_awal
FROM konfirmasi_pembayaran_doc_det kpdd
JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
LEFT JOIN pph_cn pc ON pc.nomor=kpd.nomor
WHERE kpd.tgl_bayar < @start;
