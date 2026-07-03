SET NOCOUNT ON;
PRINT '=== Jurnal semua COA untuk BYD/11/25/00147 ===';
SELECT dj.tanggal, dj.kode_trans, dj.coa_asal, dj.coa_tujuan,
       CAST(dj.nominal AS decimal(18,2)) nominal, dj.invoice
FROM det_jurnal dj WHERE dj.invoice='BYD/11/25/00147' ORDER BY dj.kode_trans, dj.coa_asal;

PRINT '';
PRINT '=== Konfirmasi BYD/11/25/00147 - jumlah det rows ===';
SELECT kpd.nomor, kpd.tgl_bayar, CAST(SUM(kpdd.total) AS decimal(18,2)) total_konfir,
       COUNT(*) jml_det, COUNT(DISTINCT kpdd.kode_unit) jml_unit,
       COUNT(DISTINCT kpdd.no_order) jml_order
FROM konfirmasi_pembayaran_doc kpd JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id
WHERE kpd.nomor='BYD/11/25/00147' GROUP BY kpd.nomor, kpd.tgl_bayar;

PRINT '';
PRINT '=== Realisasi BYD/11/25/00147 ===';
SELECT rpd.no_bayar, rpd.transaksi, CAST(rpd.transfer AS decimal(18,2)) transfer, rp.tgl_realisasi
FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
WHERE rpd.no_bayar='BYD/11/25/00147';
