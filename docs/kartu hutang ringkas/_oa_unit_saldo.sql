SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
;WITH debet AS (
  select oa.unit, sum(oa.total) as debet from (
    select tp.no_bbm as nomor, kp.ekspedisi_id as supplier, substring(kp.no_sj, charindex('/',kp.no_sj+'//')+1, charindex('/',kp.no_sj+'//',charindex('/',kp.no_sj+'//')+1)-charindex('/',kp.no_sj+'//')-1) as unit, sum(dtp.jumlah)*kp.ongkos_angkut as total
    from det_terima_pakan dtp left join terima_pakan tp on dtp.id_header=tp.id left join kirim_pakan kp on tp.id_kirim_pakan=kp.id
    where tp.tgl_terima<@start and kp.jenis_kirim='opkg'
    group by tp.no_bbm, kp.ekspedisi_id, kp.ongkos_angkut, kp.no_sj
    union all
    select opp.no_sj, krm.ekspedisi_id, substring(opp.no_sj, charindex('/',opp.no_sj+'//')+1, charindex('/',opp.no_sj+'//',charindex('/',opp.no_sj+'//')+1)-charindex('/',opp.no_sj+'//')-1), opp.ongkos_angkut
    from oa_pindah_pakan opp
    left join (
      select kp.no_sj, tp.no_bbm kode_trans, kp.ekspedisi_id, tp.tgl_terima tanggal from kirim_pakan kp left join terima_pakan tp on kp.id=tp.id_kirim_pakan group by kp.no_sj, tp.no_bbm, kp.ekspedisi_id, tp.tgl_terima
      union all select no_retur, no_retur, ekspedisi_id, tgl_retur from retur_pakan
    ) krm on opp.no_sj=krm.no_sj
    where krm.tanggal<@start
  ) oa group by oa.unit
),
kredit AS (
  select oa_unit.unit, sum(rpd.transfer + isnull(kpop.potongan_pph_23,0)) as kredit
  from realisasi_pembayaran_det rpd
  left join realisasi_pembayaran rp on rpd.id_header=rp.id
  left join konfirmasi_pembayaran_oa_pakan kpop on kpop.nomor=rpd.no_bayar
  left join (select kpopd.id_header, min(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) as unit from konfirmasi_pembayaran_oa_pakan_det kpopd left join kirim_pakan kp on kp.no_sj=kpopd.no_sj group by kpopd.id_header) oa_unit on oa_unit.id_header=kpop.id
  where rpd.transaksi='OA PAKAN' and rp.tgl_realisasi is not null and rp.tgl_realisasi<@start
  group by oa_unit.unit
)
SELECT isnull(d.unit,k.unit) AS unit, CAST(isnull(d.debet,0) AS decimal(18,2)) debet, CAST(isnull(k.kredit,0) AS decimal(18,2)) kredit, CAST(isnull(d.debet,0)-isnull(k.kredit,0) AS decimal(18,2)) saldo
FROM debet d FULL OUTER JOIN kredit k ON d.unit=k.unit ORDER BY unit;
PRINT '=== GRAND TOTAL ===';
