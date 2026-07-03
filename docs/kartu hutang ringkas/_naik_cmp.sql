SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
;WITH glnaik AS (select unit, sum(nominal) naik from det_jurnal where coa_asal='21212.000' and tanggal<@start group by unit),
rdebet AS (
  select oa.unit, sum(oa.total) debet from (
    select substring(kp.no_sj, charindex('/',kp.no_sj+'//')+1, charindex('/',kp.no_sj+'//',charindex('/',kp.no_sj+'//')+1)-charindex('/',kp.no_sj+'//')-1) unit, sum(dtp.jumlah)*kp.ongkos_angkut total
    from det_terima_pakan dtp left join terima_pakan tp on dtp.id_header=tp.id left join kirim_pakan kp on tp.id_kirim_pakan=kp.id
    where tp.tgl_terima<@start and kp.jenis_kirim='opkg' group by tp.no_bbm, kp.ongkos_angkut, kp.no_sj
    union all
    select substring(krm.no_order, charindex('/',krm.no_order+'//')+1, charindex('/',krm.no_order+'//',charindex('/',krm.no_order+'//')+1)-charindex('/',krm.no_order+'//')-1), opp.ongkos_angkut
    from oa_pindah_pakan opp left join (select kp.no_sj, tp.tgl_terima tanggal, kp.no_order from kirim_pakan kp left join terima_pakan tp on kp.id=tp.id_kirim_pakan group by kp.no_sj, tp.tgl_terima, kp.no_order union all select no_retur, tgl_retur, no_order from retur_pakan) krm on opp.no_sj=krm.no_sj
    where krm.tanggal<@start
  ) oa group by oa.unit
)
SELECT isnull(g.unit,r.unit) unit, CAST(isnull(r.debet,0) AS decimal(18,2)) report_debet, CAST(isnull(g.naik,0) AS decimal(18,2)) gl_naik,
  CAST(isnull(r.debet,0)-isnull(g.naik,0) AS decimal(18,2)) selisih
FROM glnaik g FULL JOIN rdebet r ON g.unit=r.unit
WHERE CAST(isnull(r.debet,0)-isnull(g.naik,0) AS decimal(18,2))<>0
ORDER BY unit;
