SET NOCOUNT ON;
PRINT '=== baris 198768 ==='
SELECT id, kode_trans, tanggal, coa_asal, coa_tujuan, nominal, unit, invoice, tbl_name, tbl_id, keterangan
FROM det_jurnal WHERE id=198768;
PRINT '=== seluruh voucher yg sama (kode_trans) ==='
SELECT id, tanggal, coa_asal, coa_tujuan, nominal, unit, keterangan
FROM det_jurnal WHERE kode_trans=(SELECT kode_trans FROM det_jurnal WHERE id=198768) ORDER BY id;
