SET NOCOUNT ON;
PRINT '=== coa table columns ===';
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='coa' ORDER BY ORDINAL_POSITION;
PRINT '=== COA hutang RHPP via det_jurnal (invoice INV/RHPP) ===';
SELECT dj.coa_asal, dj.coa_tujuan, dj.tbl_name, COUNT(*) n, CAST(SUM(dj.nominal) AS decimal(18,2)) total
FROM det_jurnal dj WHERE dj.invoice LIKE 'INV/RHPP%'
GROUP BY dj.coa_asal, dj.coa_tujuan, dj.tbl_name ORDER BY COUNT(*) DESC;
