SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
PRINT '=== FREIGHT rows <Juni dgn unit KOSONG (kp.no_sj aneh) ===';
SELECT kp.no_sj, kp.no_order, kp.jenis_kirim, CAST(SUM(dtp.jumlah)*kp.ongkos_angkut AS decimal(18,2)) freight
FROM det_terima_pakan dtp LEFT JOIN terima_pakan tp ON dtp.id_header=tp.id LEFT JOIN kirim_pakan kp ON tp.id_kirim_pakan=kp.id
WHERE tp.tgl_terima<@start AND kp.jenis_kirim='opkg'
  AND len(substring(kp.no_sj, charindex('/',kp.no_sj+'//')+1, charindex('/',kp.no_sj+'//',charindex('/',kp.no_sj+'//')+1)-charindex('/',kp.no_sj+'//')-1)) = 0
GROUP BY kp.no_sj, kp.no_order, kp.jenis_kirim, kp.ongkos_angkut ORDER BY freight DESC;

PRINT '=== PINDAH rows <Juni dgn unit KOSONG (opp.no_sj aneh) ===';
SELECT opp.no_sj, CAST(opp.ongkos_angkut AS decimal(18,2)) ongkos
FROM oa_pindah_pakan opp
LEFT JOIN (
  select kp.no_sj, tp.tgl_terima tanggal from kirim_pakan kp left join terima_pakan tp on kp.id=tp.id_kirim_pakan group by kp.no_sj, tp.tgl_terima
  union all select no_retur, tgl_retur from retur_pakan
) krm ON opp.no_sj=krm.no_sj
WHERE krm.tanggal<@start
  AND len(substring(opp.no_sj, charindex('/',opp.no_sj+'//')+1, charindex('/',opp.no_sj+'//',charindex('/',opp.no_sj+'//')+1)-charindex('/',opp.no_sj+'//')-1)) = 0
ORDER BY ongkos DESC;
