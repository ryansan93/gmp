SET NOCOUNT ON;
DECLARE @orders TABLE(no_order varchar(50));
INSERT @orders VALUES
 ('ODC/MLG/25/11026'),('ODC/MLG/25/11007'),('ODC/TAG/25/09010'),('ODC/BJN/25/11030');

/* penerimaan + jurnal BBM per terima_doc */
SELECT td.no_order, td.id as terima_id, td.no_terima, td.no_bbm, td.datang, td.total as nilai_terima,
       dj.kode_trans, dj.tanggal as tgl_jurnal, dj.nominal as nilai_jurnal, dj.invoice
FROM terima_doc td
JOIN @orders o ON o.no_order = td.no_order
LEFT JOIN det_jurnal dj ON dj.tbl_name='terima_doc' AND dj.tbl_id=CAST(td.id AS varchar) AND dj.coa_asal='21180.200'
ORDER BY td.no_order, td.id;

/* konfirmasi yang menagih order ini */
SELECT kpd.nomor as invoice, kpd.tgl_bayar, kpdd.no_order, kpdd.total
FROM konfirmasi_pembayaran_doc_det kpdd
JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
JOIN @orders o ON o.no_order = kpdd.no_order
ORDER BY kpdd.no_order, kpd.nomor;
