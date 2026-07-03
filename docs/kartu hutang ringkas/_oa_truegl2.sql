SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
;WITH ubyo AS (
  SELECT kpop.id kpop_id, kpop.nomor byo, MIN(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) unit
  FROM konfirmasi_pembayaran_oa_pakan kpop LEFT JOIN konfirmasi_pembayaran_oa_pakan_det kpopd ON kpopd.id_header=kpop.id LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj GROUP BY kpop.id, kpop.nomor
),
glnaik AS (select unit, sum(nominal) naik from det_jurnal where coa_asal='21212.000' and tanggal<@start group by unit),
glturun AS (select u.unit, sum(dj.nominal) turun from det_jurnal dj JOIN ubyo u ON u.byo=dj.invoice where dj.coa_tujuan='21212.000' and dj.tanggal<@start group by u.unit),
rdebet AS (
  select oa.unit, sum(oa.total) debet from (
    select substring(kp.no_sj, charindex('/',kp.no_sj+'//')+1, charindex('/',kp.no_sj+'//',charindex('/',kp.no_sj+'//')+1)-charindex('/',kp.no_sj+'//')-1) unit, sum(dtp.jumlah)*kp.ongkos_angkut total
    from det_terima_pakan dtp left join terima_pakan tp on dtp.id_header=tp.id left join kirim_pakan kp on tp.id_kirim_pakan=kp.id
    where tp.tgl_terima<@start and kp.jenis_kirim='opkg' group by tp.no_bbm, kp.ongkos_angkut, kp.no_sj
    union all
    select coalesce(nullif(substring(opp.no_sj, charindex('/',opp.no_sj+'//')+1, charindex('/',opp.no_sj+'//',charindex('/',opp.no_sj+'//')+1)-charindex('/',opp.no_sj+'//')-1),''), substring(krm.no_order, charindex('/',krm.no_order+'//')+1, charindex('/',krm.no_order+'//',charindex('/',krm.no_order+'//')+1)-charindex('/',krm.no_order+'//')-1)), opp.ongkos_angkut
    from oa_pindah_pakan opp left join (select kp.no_sj, tp.tgl_terima tanggal, kp.no_order from kirim_pakan kp left join terima_pakan tp on kp.id=tp.id_kirim_pakan group by kp.no_sj, tp.tgl_terima, kp.no_order union all select no_retur, tgl_retur, no_order from retur_pakan) krm on opp.no_sj=krm.no_sj
    where krm.tanggal<@start
  ) oa group by oa.unit
),
rkredit AS (
  select u.unit, sum(rpd.transfer+isnull(kpop.potongan_pph_23,0)) kredit
  from realisasi_pembayaran_det rpd join realisasi_pembayaran rp on rpd.id_header=rp.id
  left join konfirmasi_pembayaran_oa_pakan kpop on kpop.nomor=rpd.no_bayar left join ubyo u on u.kpop_id=kpop.id
  where rpd.transaksi='OA PAKAN' and rp.tgl_realisasi is not null and rp.tgl_realisasi<@start group by u.unit
)
SELECT isnull(isnull(rd.unit,rk.unit),isnull(gn.unit,gt.unit)) unit,
  CAST((isnull(rd.debet,0)-isnull(rk.kredit,0))-(isnull(gn.naik,0)-isnull(gt.turun,0)) AS decimal(18,2)) selisih
FROM rdebet rd FULL JOIN rkredit rk ON rd.unit=rk.unit FULL JOIN glnaik gn ON gn.unit=isnull(rd.unit,rk.unit) FULL JOIN glturun gt ON gt.unit=isnull(isnull(rd.unit,rk.unit),gn.unit)
ORDER BY unit;
