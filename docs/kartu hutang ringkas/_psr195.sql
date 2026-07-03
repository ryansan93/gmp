SET NOCOUNT ON;
DECLARE @i varchar(50)='BYD/10/25/00195';
SELECT 'konfir' src, kpd.nomor, kpd.tgl_bayar, kpdd.kode_unit, kpdd.no_order, kpdd.total
FROM konfirmasi_pembayaran_doc kpd JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id WHERE kpd.nomor=@i;
SELECT 'realisasi' src, rpd.no_bayar, rpd.transaksi, rpd.transfer, rp.tgl_realisasi FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.no_bayar=@i;
SELECT 'cn' src, cpd.nomor, cpd.pakai, cp.tanggal, cp.jenis_cn FROM cn_post_det cpd JOIN cn_post cp ON cpd.id_header=cp.id WHERE cpd.nomor=@i;
SELECT 'GL_21180200' src, dj.id, dj.kode_trans, dj.tanggal, dj.coa_asal, dj.coa_tujuan, dj.nominal, dj.unit, dj.tbl_name, dj.keterangan
FROM det_jurnal dj WHERE dj.invoice=@i AND (dj.coa_asal='21180.200' OR dj.coa_tujuan='21180.200') ORDER BY dj.tanggal, dj.id;
