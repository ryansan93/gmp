SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
PRINT '=== Freight naik 21212 dgn det_jurnal.unit != unit(no_sj) ===';
SELECT dj.unit AS gl_unit,
  substring(kp.no_sj, charindex('/',kp.no_sj+'//')+1, charindex('/',kp.no_sj+'//',charindex('/',kp.no_sj+'//')+1)-charindex('/',kp.no_sj+'//')-1) AS sj_unit,
  kp.no_sj, kp.no_order, tp.no_bbm, CAST(dj.nominal AS decimal(18,2)) nominal, dj.tanggal
FROM det_jurnal dj
JOIN terima_pakan tp ON tp.id=dj.tbl_id AND dj.tbl_name='terima_pakan'
JOIN kirim_pakan kp ON tp.id_kirim_pakan=kp.id
WHERE dj.coa_asal='21212.000' AND dj.tanggal<@start
  AND dj.unit <> substring(kp.no_sj, charindex('/',kp.no_sj+'//')+1, charindex('/',kp.no_sj+'//',charindex('/',kp.no_sj+'//')+1)-charindex('/',kp.no_sj+'//')-1)
ORDER BY dj.unit, dj.tanggal;
