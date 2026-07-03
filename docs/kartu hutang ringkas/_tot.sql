SET NOCOUNT ON;
DECLARE @end date='2026-05-31';
SELECT 'GL_naik-turun' lbl, CAST(SUM(CASE WHEN coa_asal='21180.200' THEN nominal ELSE 0 END)-SUM(CASE WHEN coa_tujuan='21180.200' THEN nominal ELSE 0 END) AS decimal(18,2)) v
FROM det_jurnal WHERE (coa_asal='21180.200' OR coa_tujuan='21180.200') AND tanggal<=@end;
