SET NOCOUNT ON;
PRINT '=== 4 pindah bermasalah: detail + cek ada-tidaknya di kirim_pakan/retur ===';
SELECT opp.id, opp.tgl_terima, opp.no_sj, CAST(opp.ongkos_angkut AS decimal(18,2)) ongkos, opp.ekspedisi,
  (SELECT COUNT(*) FROM kirim_pakan kp WHERE kp.no_sj=opp.no_sj) AS ada_di_kirim,
  (SELECT COUNT(*) FROM retur_pakan rp WHERE rp.no_retur=opp.no_sj) AS ada_di_retur
FROM oa_pindah_pakan opp
WHERE opp.no_sj IN ('SJ/MGT/26/02164','SJ/MGT/26/02163','SJ/MLG/26/01375','SJ/SLM/26/02070')
ORDER BY opp.no_sj;
PRINT '=== total pindah yg no_sj-nya TIDAK ada di kirim_pakan & retur (semua, <Juni by tgl_terima) ===';
SELECT opp.tgl_terima, opp.no_sj, CAST(opp.ongkos_angkut AS decimal(18,2)) ongkos,
  substring(opp.no_sj, charindex('/',opp.no_sj+'//')+1, charindex('/',opp.no_sj+'//',charindex('/',opp.no_sj+'//')+1)-charindex('/',opp.no_sj+'//')-1) AS unit
FROM oa_pindah_pakan opp
WHERE opp.tgl_terima < '2026-06-01'
  AND NOT EXISTS (SELECT 1 FROM kirim_pakan kp WHERE kp.no_sj=opp.no_sj)
  AND NOT EXISTS (SELECT 1 FROM retur_pakan rp WHERE rp.no_retur=opp.no_sj)
ORDER BY opp.no_sj;
