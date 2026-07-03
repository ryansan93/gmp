SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
PRINT '=== Invoice lewat memo per unit setelah fix ===';
SELECT m.unit,
    mi.no_mm, mi.no_invoice, CAST(mi.nilai AS decimal(18,2)) nilai,
    CASE WHEN EXISTS(SELECT 1 FROM (
        SELECT nomor FROM konfirmasi_pembayaran_doc UNION ALL SELECT nomor FROM konfirmasi_pembayaran_pakan
        UNION ALL SELECT nomor FROM konfirmasi_pembayaran_voadip UNION ALL SELECT nomor FROM konfirmasi_pembayaran_oa_pakan
        UNION ALL SELECT ISNULL(NULLIF(invoice,''),nomor) FROM konfirmasi_pembayaran_peternak
    ) kk WHERE kk.nomor=mi.no_invoice) THEN 'EXCLUDE (ada di konfir)' ELSE 'MASUK op debet' END status
FROM mmitem mi JOIN mm m ON mi.no_mm=m.no_mm
WHERE mi.coa_asal='21180.200' AND CAST(mi.tgl_mm AS date)<@start
  AND m.unit IN ('BJN','JBR','MLG','BWI')
ORDER BY m.unit;

PRINT '';
PRINT '=== Total inv_mm masuk op debet per unit ===';
SELECT m.unit, CAST(SUM(mi.nilai) AS decimal(18,2)) total_masuk FROM mmitem mi JOIN mm m ON mi.no_mm=m.no_mm
WHERE mi.coa_asal='21180.200' AND CAST(mi.tgl_mm AS date)<@start
AND NOT EXISTS(SELECT 1 FROM (
    SELECT nomor FROM konfirmasi_pembayaran_doc UNION ALL SELECT nomor FROM konfirmasi_pembayaran_pakan
    UNION ALL SELECT nomor FROM konfirmasi_pembayaran_voadip UNION ALL SELECT nomor FROM konfirmasi_pembayaran_oa_pakan
    UNION ALL SELECT ISNULL(NULLIF(invoice,''),nomor) FROM konfirmasi_pembayaran_peternak
) kk WHERE kk.nomor=mi.no_invoice)
AND m.unit IN ('BJN','JBR','MLG','BWI')
GROUP BY m.unit;
