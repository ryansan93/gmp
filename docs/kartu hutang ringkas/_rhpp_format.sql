SET NOCOUNT ON;
PRINT '=== konfirmasi_pembayaran_peternak: nomor & invoice (sumber unit debet) ===';
SELECT TOP 6 nomor, invoice, mitra, CAST(total AS decimal(18,2)) total FROM konfirmasi_pembayaran_peternak ORDER BY id DESC;
PRINT '=== realisasi_pembayaran_det PLASMA: no_bayar (sumber unit kredit) ===';
SELECT TOP 6 rpd.no_bayar, rp.peternak, CAST(rpd.transfer AS decimal(18,2)) transfer FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='PLASMA' ORDER BY rpd.id DESC;
PRINT '=== rhpp: invoice & noreg (sumber unit GL naik) ===';
SELECT TOP 6 noreg, invoice, mitra FROM rhpp ORDER BY id DESC;
PRINT '=== distribusi prefix no_bayar PLASMA ===';
SELECT LEFT(rpd.no_bayar,9) prefix, COUNT(*) n FROM realisasi_pembayaran_det rpd WHERE rpd.transaksi='PLASMA' GROUP BY LEFT(rpd.no_bayar,9) ORDER BY COUNT(*) DESC;
