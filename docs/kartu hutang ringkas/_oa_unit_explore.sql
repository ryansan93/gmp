SET NOCOUNT ON;
PRINT '=== kirim_pakan cols ===';
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='kirim_pakan' ORDER BY ORDINAL_POSITION;
PRINT '=== sample kirim_pakan (no_order, no_sj, unit?) ===';
SELECT TOP 5 id, no_order, no_sj, jenis_kirim, ekspedisi_id FROM kirim_pakan WHERE jenis_kirim='opkg' ORDER BY id DESC;
PRINT '=== oa_pindah_pakan cols ===';
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='oa_pindah_pakan' ORDER BY ORDINAL_POSITION;
PRINT '=== konfirmasi_pembayaran_oa_pakan_det cols ===';
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='konfirmasi_pembayaran_oa_pakan_det' ORDER BY ORDINAL_POSITION;
