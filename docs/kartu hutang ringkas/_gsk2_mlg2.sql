SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
PRINT '=== realisasi BYR/02/26/00046: no_bayar sebenarnya apa? ===';
SELECT rpd.no_bayar, rpd.transaksi, rp.tgl_realisasi, CAST(rpd.transfer AS decimal(18,2)) transfer
FROM realisasi_pembayaran rp JOIN realisasi_pembayaran_det rpd ON rpd.id_header=rp.id
WHERE rp.nomor='BYR/02/26/00046';
PRINT '=== saldo_bulanan 21212 MLG (semua periode, lihat saldo_awal/akhir) ===';
SELECT periode_fiskal, tanggal, unit, CAST(saldo_awal AS decimal(18,2)) saldo_awal, CAST(saldo_akhir AS decimal(18,2)) saldo_akhir
FROM saldo_bulanan WHERE coa='21212.000' AND unit='MLG' ORDER BY tanggal;
