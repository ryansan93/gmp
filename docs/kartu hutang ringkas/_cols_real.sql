SET NOCOUNT ON;
SELECT 'rpd' tbl, name FROM sys.columns WHERE object_id=OBJECT_ID('realisasi_pembayaran_det') ORDER BY column_id;
SELECT 'rp' tbl, name FROM sys.columns WHERE object_id=OBJECT_ID('realisasi_pembayaran') ORDER BY column_id;
