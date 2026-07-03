SET NOCOUNT ON;
DECLARE @cut date='2026-05-01';
;WITH glp AS (
  SELECT opp.no_sj, opp.id, CAST(SUM(dj.nominal) AS decimal(18,2)) gl_nom
  FROM det_jurnal dj JOIN oa_pindah_pakan opp ON opp.id=dj.tbl_id
  WHERE dj.coa_asal='21212.000' AND dj.tbl_name='oa_pindah_pakan' AND dj.unit='MLG' AND dj.tanggal<@cut
  GROUP BY opp.no_sj, opp.id
),
repp AS (
  SELECT opp.id, opp.no_sj, CAST(opp.ongkos_angkut AS decimal(18,2)) rep_nom
  FROM oa_pindah_pakan opp
  LEFT JOIN (select kp.no_sj, tp.tgl_terima tanggal, kp.no_order from kirim_pakan kp left join terima_pakan tp on kp.id=tp.id_kirim_pakan group by kp.no_sj, tp.tgl_terima, kp.no_order union all select no_retur, tgl_retur, no_order from retur_pakan) krm ON krm.no_sj=opp.no_sj
  WHERE coalesce(krm.tanggal, opp.tgl_terima)<@cut
    AND coalesce(nullif(substring(opp.no_sj, charindex('/',opp.no_sj+'//')+1, charindex('/',opp.no_sj+'//',charindex('/',opp.no_sj+'//')+1)-charindex('/',opp.no_sj+'//')-1),''), substring(krm.no_order, charindex('/',krm.no_order+'//')+1, charindex('/',krm.no_order+'//',charindex('/',krm.no_order+'//')+1)-charindex('/',krm.no_order+'//')-1))='MLG'
)
SELECT isnull(g.id,r.id) id, isnull(g.no_sj,r.no_sj) no_sj,
  isnull(r.rep_nom,0) report, isnull(g.gl_nom,0) gl, CAST(isnull(r.rep_nom,0)-isnull(g.gl_nom,0) AS decimal(18,2)) diff
FROM glp g FULL JOIN repp r ON g.id=r.id
WHERE isnull(r.rep_nom,0) <> isnull(g.gl_nom,0)
ORDER BY abs(isnull(r.rep_nom,0)-isnull(g.gl_nom,0)) DESC;
PRINT '=== total diff ===';
