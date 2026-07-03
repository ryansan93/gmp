SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
PRINT '=== oa_pindah_pakan naik 21212: GL unit vs report unit (krm.no_order) ===';
SELECT dj.unit AS gl_unit,
  substring(krm.no_order, charindex('/',krm.no_order+'//')+1, charindex('/',krm.no_order+'//',charindex('/',krm.no_order+'//')+1)-charindex('/',krm.no_order+'//')-1) AS report_unit,
  opp.no_sj, krm.no_order, CAST(dj.nominal AS decimal(18,2)) nominal, dj.tanggal
FROM det_jurnal dj
JOIN oa_pindah_pakan opp ON opp.id = dj.tbl_id
LEFT JOIN (
  select kp.no_sj, kp.no_order from kirim_pakan kp
  union all select no_retur, no_order from retur_pakan
) krm ON krm.no_sj = opp.no_sj
WHERE dj.coa_asal='21212.000' AND dj.tbl_name='oa_pindah_pakan' AND dj.tanggal<@start
  AND isnull(dj.unit,'') <> isnull(substring(krm.no_order, charindex('/',krm.no_order+'//')+1, charindex('/',krm.no_order+'//',charindex('/',krm.no_order+'//')+1)-charindex('/',krm.no_order+'//')-1),'')
ORDER BY dj.unit, dj.tanggal;
