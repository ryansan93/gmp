SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
PRINT '=== D3 pindah (logika controller saat ini) — apakah 4 orphan kini MASUK? ===';
SELECT opp.id, opp.no_sj,
  coalesce(krm.tanggal, opp.tgl_terima) AS tgl_dipakai,
  coalesce(krm.ekspedisi_id, (select min(e.nomor) from ekspedisi e where e.nama=opp.ekspedisi)) AS supplier,
  coalesce(nullif(substring(opp.no_sj, charindex('/',opp.no_sj+'//')+1, charindex('/',opp.no_sj+'//',charindex('/',opp.no_sj+'//')+1)-charindex('/',opp.no_sj+'//')-1),''), substring(krm.no_order, charindex('/',krm.no_order+'//')+1, charindex('/',krm.no_order+'//',charindex('/',krm.no_order+'//')+1)-charindex('/',krm.no_order+'//')-1)) AS unit,
  CAST(opp.ongkos_angkut AS decimal(18,2)) ongkos,
  CASE WHEN coalesce(krm.tanggal, opp.tgl_terima) < @start THEN 'MASUK saldo awal' ELSE 'TIDAK' END AS status
FROM oa_pindah_pakan opp
LEFT JOIN (select kp.no_sj, tp.tgl_terima tanggal, kp.ekspedisi_id, kp.no_order from kirim_pakan kp left join terima_pakan tp on kp.id=tp.id_kirim_pakan group by kp.no_sj, tp.tgl_terima, kp.ekspedisi_id, kp.no_order union all select no_retur, tgl_retur, ekspedisi_id, no_order from retur_pakan) krm ON krm.no_sj=opp.no_sj
WHERE opp.id IN (639,907,908,957)
ORDER BY opp.id;
