SET NOCOUNT ON;
PRINT '=== realisasi (transfer operasional) BYV/11/25/00124 ==='
SELECT rpd.no_bayar, rpd.transaksi, rpd.tagihan, rpd.transfer, rpd.cn, rpd.potongan, rpd.dn FROM realisasi_pembayaran_det rpd WHERE rpd.no_bayar='BYV/11/25/00124';
PRINT '=== net 21174 vs ringkas utk invoice ini ==='
SELECT
 (SELECT SUM(kpvd.total) FROM konfirmasi_pembayaran_voadip kpv JOIN konfirmasi_pembayaran_voadip_det kpvd ON kpvd.id_header=kpv.id WHERE kpv.nomor='BYV/11/25/00124') as konfir,
 (SELECT SUM(rpd.transfer) FROM realisasi_pembayaran_det rpd WHERE rpd.no_bayar='BYV/11/25/00124') as transfer_op,
 (SELECT SUM(dj.nominal) FROM det_jurnal dj WHERE dj.coa_tujuan='21174.000' AND dj.keterangan LIKE '%04776%') as gl_turun_extern;
