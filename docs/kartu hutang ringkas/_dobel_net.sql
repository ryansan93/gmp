SET NOCOUNT ON;
DECLARE @inv TABLE(invoice varchar(50));
INSERT @inv VALUES ('BYD/11/25/00053'),('BYD/11/25/00195'),('BYD/09/25/00109');

/* semua pergerakan 21180.200 utk invoice ini (via invoice col) */
SELECT 'via_invoice' as src, dj.invoice, dj.kode_trans, dj.tanggal,
       CASE WHEN dj.coa_asal='21180.200' THEN 'NAIK' ELSE 'TURUN' END as arah,
       dj.nominal, dj.tbl_name, dj.tbl_id
FROM det_jurnal dj
JOIN @inv i ON i.invoice = dj.invoice
WHERE dj.coa_asal='21180.200' OR dj.coa_tujuan='21180.200'
ORDER BY dj.invoice, dj.tanggal;

/* net per invoice (naik-turun via invoice col) */
SELECT dj.invoice,
  SUM(CASE WHEN dj.coa_asal='21180.200' THEN dj.nominal ELSE 0 END) as naik,
  SUM(CASE WHEN dj.coa_tujuan='21180.200' THEN dj.nominal ELSE 0 END) as turun,
  SUM(CASE WHEN dj.coa_asal='21180.200' THEN dj.nominal ELSE -dj.nominal END) as net
FROM det_jurnal dj
JOIN @inv i ON i.invoice = dj.invoice
WHERE dj.coa_asal='21180.200' OR dj.coa_tujuan='21180.200'
GROUP BY dj.invoice;
