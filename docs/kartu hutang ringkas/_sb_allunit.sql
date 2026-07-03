SET NOCOUNT ON;
PRINT '=== snapshot saldo_bulanan 21212 end-April (tanggal=2026-05-01) per unit ===';
SELECT unit, CAST(SUM(saldo_akhir) AS decimal(18,2)) snapshot_endApr
FROM saldo_bulanan WHERE coa='21212.000' AND tanggal='2026-05-01'
GROUP BY unit ORDER BY unit;
