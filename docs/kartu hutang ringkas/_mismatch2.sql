SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
PRINT '=== Freight (terima_pakan) naik 21212: GL unit != SJ unit, fokus MGT/SLM/MLG ===';
SELECT dj.unit AS gl_unit,
  substring(kp.no_sj, charindex('/',kp.no_sj+'//')+1, charindex('/',kp.no_sj+'//',charindex('/',kp.no_sj+'//')+1)-charindex('/',kp.no_sj+'//')-1) AS sj_unit,
  tp.no_bbm, kp.no_sj, kp.no_order, CAST(dj.nominal AS decimal(18,2)) nominal, dj.tanggal
FROM det_jurnal dj
JOIN terima_pakan tp ON tp.id = dj.tbl_id
JOIN kirim_pakan kp ON tp.id_kirim_pakan = kp.id
WHERE dj.coa_asal='21212.000' AND dj.tbl_name='terima_pakan' AND dj.tanggal<@start
  AND dj.unit <> substring(kp.no_sj, charindex('/',kp.no_sj+'//')+1, charindex('/',kp.no_sj+'//',charindex('/',kp.no_sj+'//')+1)-charindex('/',kp.no_sj+'//')-1)
  AND (dj.unit IN ('MGT','SLM','MLG') OR substring(kp.no_sj, charindex('/',kp.no_sj+'//')+1, charindex('/',kp.no_sj+'//',charindex('/',kp.no_sj+'//')+1)-charindex('/',kp.no_sj+'//')-1) IN ('MGT','SLM','MLG'))
ORDER BY dj.unit, dj.tanggal;
