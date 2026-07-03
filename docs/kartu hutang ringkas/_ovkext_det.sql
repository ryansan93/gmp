SET NOCOUNT ON;
PRINT '=== kolom konfirmasi_pembayaran_voadip_det ==='
SELECT name FROM sys.columns WHERE object_id=OBJECT_ID('konfirmasi_pembayaran_voadip_det') ORDER BY column_id;
PRINT '=== kolom terima_voadip ==='
SELECT name FROM sys.columns WHERE object_id=OBJECT_ID('terima_voadip') ORDER BY column_id;
PRINT '=== konfir + det utk BYV/10/25/00107 & 00043 (MJK) ==='
SELECT kpv.nomor, kpv.tgl_bayar, kpv.total, kpvd.*
FROM konfirmasi_pembayaran_voadip kpv
LEFT JOIN konfirmasi_pembayaran_voadip_det kpvd ON kpvd.id_header=kpv.id
WHERE kpv.nomor IN ('BYV/10/25/00107','BYV/10/25/00043');
