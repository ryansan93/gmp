SET NOCOUNT ON;
PRINT '=== det_jurnal MM2601310039 ==='
SELECT id, coa_asal, coa_tujuan, nominal, unit, invoice, keterangan FROM det_jurnal WHERE kode_trans='MM2601310039' ORDER BY id;
PRINT '=== kolom tabel coa ==='
SELECT name FROM sys.columns WHERE object_id=OBJECT_ID('coa') ORDER BY column_id;
