SET NOCOUNT ON;
PRINT '=== KONFIRMASI det ===';
SELECT kpd.nomor, kpdd.no_order, kpdd.kode_unit, kpd.tgl_bayar, kpd.supplier, CAST(kpdd.total AS decimal(18,2)) total
FROM konfirmasi_pembayaran_doc kpd JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id
WHERE kpd.nomor IN ('BYD/11/25/00173','BYD/11/25/00100','BYD/11/25/00205') ORDER BY kpd.nomor;
PRINT '';
PRINT '=== terima_doc utk no_order tsb ===';
SELECT td.id, td.no_order, td.no_bbm, td.datang, CAST(td.total AS decimal(18,2)) total
FROM terima_doc td
WHERE td.no_order IN (SELECT kpdd.no_order FROM konfirmasi_pembayaran_doc kpd JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id WHERE kpd.nomor IN ('BYD/11/25/00173','BYD/11/25/00100','BYD/11/25/00205'));
PRINT '';
PRINT '=== det_jurnal 21180.200 yg menyebut invoice ini ===';
SELECT dj.id, dj.tanggal, dj.kode_trans, dj.coa_asal, dj.coa_tujuan, dj.invoice, dj.tbl_name, dj.tbl_id, CAST(dj.nominal AS decimal(18,2)) nominal
FROM det_jurnal dj
WHERE (dj.coa_asal='21180.200' OR dj.coa_tujuan='21180.200') AND dj.invoice IN ('BYD/11/25/00173','BYD/11/25/00100','BYD/11/25/00205')
ORDER BY dj.invoice, dj.tanggal;
PRINT '';
PRINT '=== det_jurnal BBM (terima_doc) utk no_order tsb ===';
SELECT dj.id, dj.tanggal, dj.kode_trans, dj.coa_asal, dj.coa_tujuan, dj.tbl_id, CAST(dj.nominal AS decimal(18,2)) nominal, dj.invoice
FROM det_jurnal dj
WHERE dj.tbl_name='terima_doc' AND dj.tbl_id IN (SELECT CAST(td.id AS varchar) FROM terima_doc td WHERE td.no_order IN (SELECT kpdd.no_order FROM konfirmasi_pembayaran_doc kpd JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id WHERE kpd.nomor IN ('BYD/11/25/00173','BYD/11/25/00100','BYD/11/25/00205')))
ORDER BY dj.tbl_id;
