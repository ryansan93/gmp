SET NOCOUNT ON;
DECLARE @inv TABLE(nomor varchar(50), no_order varchar(50));
INSERT @inv VALUES
 ('BYD/09/25/00093','ODC/MLG/25/09023'),
 ('BYD/09/25/00106',NULL),
 ('BYD/10/25/00241',NULL);

/* BBM naik (terima_doc) + unit-nya */
SELECT 'BBM_naik' src, kpd.nomor invoice, dj.unit, dj.nominal, dj.tanggal, td.no_order
FROM det_jurnal dj
JOIN terima_doc td ON dj.tbl_name='terima_doc' AND dj.tbl_id=CAST(td.id AS varchar)
JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.no_order=td.no_order
JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
WHERE dj.coa_asal='21180.200'
  AND kpd.nomor IN ('BYD/09/25/00093','BYD/09/25/00106','BYD/10/25/00241')
ORDER BY kpd.nomor;

/* Rekap 21180.200 per-unit utk 3 invoice ini (semua kode_trans) */
SELECT dj.invoice, dj.unit,
  SUM(CASE WHEN dj.coa_asal='21180.200' THEN dj.nominal ELSE 0 END) naik,
  SUM(CASE WHEN dj.coa_tujuan='21180.200' THEN dj.nominal ELSE 0 END) turun,
  SUM(CASE WHEN dj.coa_asal='21180.200' THEN dj.nominal ELSE -dj.nominal END) net
FROM det_jurnal dj
WHERE dj.invoice IN ('BYD/09/25/00093','BYD/09/25/00106','BYD/10/25/00241')
  AND (dj.coa_asal='21180.200' OR dj.coa_tujuan='21180.200')
GROUP BY dj.invoice, dj.unit
ORDER BY dj.invoice, dj.unit;
