SET NOCOUNT ON;
DECLARE @end date='2026-06-13';
SELECT CAST(SUM(CASE WHEN coa_asal='21180.200' THEN nominal ELSE 0 END)-SUM(CASE WHEN coa_tujuan='21180.200' THEN nominal ELSE 0 END) AS decimal(18,2)) gl_saldo
FROM det_jurnal WHERE (coa_asal='21180.200' OR coa_tujuan='21180.200') AND tanggal<=@end;
