SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
PRINT '=== GL 21212 TURUN per det_jurnal.unit (cek apakah real-unit atau JTM) ===';
SELECT dj.unit, COUNT(*) n, CAST(SUM(dj.nominal) AS decimal(18,2)) total FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.tanggal<@start GROUP BY dj.unit ORDER BY SUM(dj.nominal) DESC;
