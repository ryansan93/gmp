SET NOCOUNT ON;
PRINT '=== kolom konfirmasi_pembayaran_oa_pakan ===';
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='konfirmasi_pembayaran_oa_pakan' ORDER BY ORDINAL_POSITION;
PRINT '=== 3 invoice: nilai sekarang + GL PPh aktual ===';
SELECT k.nomor, CAST(k.sub_total AS decimal(18,2)) sub_total, CAST(k.total AS decimal(18,2)) total, CAST(k.potongan_pph_23 AS decimal(18,2)) pph_konfir,
  CAST((SELECT SUM(dj.nominal) FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.coa_asal='24623.000' AND dj.invoice=k.nomor) AS decimal(18,2)) AS pph_gl,
  CAST((SELECT SUM(dj.nominal) FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.coa_asal='11130.002' AND dj.invoice=k.nomor) AS decimal(18,2)) AS transfer_gl
FROM konfirmasi_pembayaran_oa_pakan k
WHERE k.nomor IN ('BYO/02/26/00023','BYO/03/26/00029','BYO/10/25/00013') ORDER BY k.nomor;
