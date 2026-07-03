SET NOCOUNT ON;
PRINT '=== GL det_jurnal utk BYR/03/26/00196 & BYR/03/26/00197 (apa yg masing-masing posting?) ===';
SELECT dj.tanggal, dj.kode_trans, dj.coa_asal, dj.coa_tujuan, dj.tbl_name, dj.invoice, CAST(dj.nominal AS decimal(18,2)) nominal, CAST(CAST(dj.keterangan AS varchar(70)) AS varchar(70)) ket
FROM det_jurnal dj WHERE dj.kode_trans IN ('BYR/03/26/00196','BYR/03/26/00197') ORDER BY dj.kode_trans, dj.tanggal;
PRINT '';
PRINT '=== header realisasi BYR/03/26/00196 & 00197 (status, tgl, keterangan) ===';
SELECT nomor, tgl_realisasi, status, no_bukti, CAST(jml_transfer AS decimal(18,2)) jml_transfer, CAST(CAST(keterangan AS varchar(60)) AS varchar(60)) ket
FROM realisasi_pembayaran WHERE nomor IN ('BYR/03/26/00196','BYR/03/26/00197');
