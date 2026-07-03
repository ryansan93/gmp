SET NOCOUNT ON;
PRINT '=== retur_pakan cols ===';
SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='retur_pakan' ORDER BY ORDINAL_POSITION;
PRINT '=== sample retur_pakan (cari unit / no_order / link) ===';
SELECT TOP 5 * FROM retur_pakan ORDER BY id DESC;
