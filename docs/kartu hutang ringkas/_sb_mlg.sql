SET NOCOUNT ON;
PRINT '=== saldo_bulanan 21212: saldo_akhir per periode (MLG) — cari opening menjelang Juni ===';
SELECT periode_fiskal, tanggal, CAST(SUM(saldo_akhir) AS decimal(18,2)) sum_saldo_akhir, COUNT(*) n
FROM saldo_bulanan WHERE coa='21212.000' AND unit='MLG'
GROUP BY periode_fiskal, tanggal ORDER BY tanggal DESC;
