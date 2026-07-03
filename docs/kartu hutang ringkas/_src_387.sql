SET NOCOUNT ON;
SELECT 'GL_turun' src, dj.invoice, dj.id, dj.kode_trans, dj.tanggal, dj.coa_asal, dj.coa_tujuan, dj.nominal, dj.unit, dj.keterangan
FROM det_jurnal dj WHERE dj.invoice='BYD/09/25/00387' AND dj.coa_tujuan='21180.200' ORDER BY dj.tanggal, dj.id;
SELECT 'realisasi' src, rpd.no_bayar, rpd.transaksi, rpd.transfer FROM realisasi_pembayaran_det rpd WHERE rpd.no_bayar='BYD/09/25/00387';
SELECT 'konfir' src, kpd.nomor, kpd.tgl_bayar, SUM(kpdd.total) total FROM konfirmasi_pembayaran_doc kpd JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id WHERE kpd.nomor='BYD/09/25/00387' GROUP BY kpd.nomor, kpd.tgl_bayar;
