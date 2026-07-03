SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
;WITH debet AS (
  select oa.unit, sum(oa.total) as debet from (
    select tp.no_bbm as nomor, kp.ekspedisi_id as supplier, substring(kp.no_sj, charindex('/',kp.no_sj+'//')+1, charindex('/',kp.no_sj+'//',charindex('/',kp.no_sj+'//')+1)-charindex('/',kp.no_sj+'//')-1) as unit, sum(dtp.jumlah)*kp.ongkos_angkut as total
    from det_terima_pakan dtp left join terima_pakan tp on dtp.id_header=tp.id left join kirim_pakan kp on tp.id_kirim_pakan=kp.id
    where tp.tgl_terima<@start and kp.jenis_kirim='opkg'
    group by tp.no_bbm, kp.ekspedisi_id, kp.ongkos_angkut, kp.no_sj
    union all
    select opp.no_sj, krm.ekspedisi_id, substring(krm.no_order, charindex('/',krm.no_order+'//')+1, charindex('/',krm.no_order+'//',charindex('/',krm.no_order+'//')+1)-charindex('/',krm.no_order+'//')-1), opp.ongkos_angkut
    from oa_pindah_pakan opp
    left join (
      select kp.no_sj, tp.no_bbm kode_trans, kp.ekspedisi_id, tp.tgl_terima tanggal, kp.no_order from kirim_pakan kp left join terima_pakan tp on kp.id=tp.id_kirim_pakan group by kp.no_sj, tp.no_bbm, kp.ekspedisi_id, tp.tgl_terima, kp.no_order
      union all select no_retur, no_retur, ekspedisi_id, tgl_retur, no_order from retur_pakan
    ) krm on opp.no_sj=krm.no_sj
    where krm.tanggal<@start
  ) oa group by oa.unit
)
SELECT isnull(unit,'(KOSONG)') unit, CAST(debet AS decimal(18,2)) debet FROM debet ORDER BY unit;
