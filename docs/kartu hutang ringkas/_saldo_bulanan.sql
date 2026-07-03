SET NOCOUNT ON;
PRINT '=== saldo_bulanan 21180.200 ===';
SELECT coa, tanggal, CAST(saldo_awal AS decimal(18,2)) saldo_awal, CAST(saldo_akhir AS decimal(18,2)) saldo_akhir, posisi, unit
FROM saldo_bulanan WHERE coa='21180.200' ORDER BY tanggal;

PRINT '';
PRINT '=== Laporan GL hutang: cek GeneralLedger menggunakan saldo_bulanan ===';
