SET NOCOUNT ON;
DECLARE @cut date='2026-05-01';
PRINT '=== GL pindah MLG (<Mei): det_jurnal coa_asal=21212 tbl=oa_pindah_pakan unit=MLG ===';
SELECT opp.no_sj, opp.tgl_terima, CAST(dj.nominal AS decimal(18,2)) gl_nominal, dj.tbl_id
FROM det_jurnal dj JOIN oa_pindah_pakan opp ON opp.id=dj.tbl_id
WHERE dj.coa_asal='21212.000' AND dj.tbl_name='oa_pindah_pakan' AND dj.unit='MLG' AND dj.tanggal<@cut
ORDER BY opp.no_sj;
PRINT '';
PRINT '=== REPORT pindah MLG (<Mei): oa_pindah_pakan, unit via coalesce, date via coalesce ===';
SELECT opp.id, opp.no_sj, opp.tgl_terima, CAST(opp.ongkos_angkut AS decimal(18,2)) ongkos,
  coalesce(nullif(substring(opp.no_sj, charindex('/',opp.no_sj+'//')+1, charindex('/',opp.no_sj+'//',charindex('/',opp.no_sj+'//')+1)-charindex('/',opp.no_sj+'//')-1),''), substring(krm.no_order, charindex('/',krm.no_order+'//')+1, charindex('/',krm.no_order+'//',charindex('/',krm.no_order+'//')+1)-charindex('/',krm.no_order+'//')-1)) AS unit,
  krm.tanggal AS krm_tgl
FROM oa_pindah_pakan opp
LEFT JOIN (select kp.no_sj, tp.tgl_terima tanggal, kp.no_order from kirim_pakan kp left join terima_pakan tp on kp.id=tp.id_kirim_pakan group by kp.no_sj, tp.tgl_terima, kp.no_order union all select no_retur, tgl_retur, no_order from retur_pakan) krm ON krm.no_sj=opp.no_sj
WHERE coalesce(krm.tanggal, opp.tgl_terima)<@cut
  AND coalesce(nullif(substring(opp.no_sj, charindex('/',opp.no_sj+'//')+1, charindex('/',opp.no_sj+'//',charindex('/',opp.no_sj+'//')+1)-charindex('/',opp.no_sj+'//')-1),''), substring(krm.no_order, charindex('/',krm.no_order+'//')+1, charindex('/',krm.no_order+'//',charindex('/',krm.no_order+'//')+1)-charindex('/',krm.no_order+'//')-1))='MLG'
ORDER BY opp.no_sj;
