SET NOCOUNT ON;
PRINT '=== kolom tabel rhpp ===';
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='rhpp' ORDER BY ORDINAL_POSITION;
PRINT '=== kolom tabel rhpp_group ===';
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='rhpp_group' ORDER BY ORDINAL_POSITION;
PRINT '=== det_jurnal naik 21213: ada unit? sample ===';
SELECT TOP 5 dj.tanggal, dj.tbl_name, dj.unit, dj.invoice, dj.no_bukti, CAST(dj.nominal AS decimal(18,2)) nominal FROM det_jurnal dj WHERE dj.coa_asal='21213.000' AND dj.tbl_name IN ('rhpp','rhpp_group') ORDER BY dj.id DESC;
