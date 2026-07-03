SET NOCOUNT ON;
DECLARE @inv varchar(50)='BYD/10/25/00195', @order varchar(50)='ODC/PSR/25/10011';

PRINT '=== TERIMA (terima_doc) ==='
SELECT id, no_terima, no_order, no_bbm, datang, jml_ekor, jml_box, harga, total
FROM terima_doc WHERE no_order=@order;

PRINT '=== BAYAR: konfirmasi ==='
SELECT kpd.nomor, kpd.tgl_bayar, kpdd.kode_unit, kpdd.no_order, kpdd.total
FROM konfirmasi_pembayaran_doc kpd JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id WHERE kpd.nomor=@inv;

PRINT '=== BAYAR: realisasi ==='
SELECT rpd.no_bayar, rpd.tagihan, rpd.transfer, rpd.cn, rpd.potongan, rpd.pph, rp.tgl_realisasi
FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.no_bayar=@inv AND rpd.transaksi='DOC';

PRINT '=== JURNAL: BBM naik (terima_doc) ==='
SELECT dj.id, dj.kode_trans, dj.tanggal, dj.coa_asal, dj.coa_tujuan, dj.nominal, dj.unit
FROM det_jurnal dj JOIN terima_doc td ON dj.tbl_name='terima_doc' AND dj.tbl_id=CAST(td.id AS varchar)
WHERE td.no_order=@order AND dj.coa_asal='21180.200';

PRINT '=== JURNAL: semua gerak 21180.200 (via invoice) ==='
SELECT dj.id, dj.kode_trans, dj.tanggal, dj.coa_asal, dj.coa_tujuan, dj.nominal, dj.unit, dj.keterangan
FROM det_jurnal dj WHERE dj.invoice=@inv AND (dj.coa_asal='21180.200' OR dj.coa_tujuan='21180.200') ORDER BY dj.tanggal, dj.id;
