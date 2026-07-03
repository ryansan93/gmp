SET NOCOUNT ON;
PRINT '=== 5 orphan pindah (no_sj tak ada di kirim/retur) — yang mana belum ada tgl ===';
SELECT opp.id, opp.no_sj, opp.tgl_terima, opp.ekspedisi, CAST(opp.ongkos_angkut AS decimal(18,2)) ongkos,
  substring(opp.no_sj, charindex('/',opp.no_sj+'//')+1, charindex('/',opp.no_sj+'//',charindex('/',opp.no_sj+'//')+1)-charindex('/',opp.no_sj+'//')-1) AS unit,
  CAST((SELECT SUM(nominal) FROM det_jurnal dj WHERE dj.coa_asal='21212.000' AND dj.tbl_name='oa_pindah_pakan' AND dj.tbl_id=opp.id) AS decimal(18,2)) AS gl_naik,
  (SELECT TOP 1 dj.tanggal FROM det_jurnal dj WHERE dj.coa_asal='21212.000' AND dj.tbl_name='oa_pindah_pakan' AND dj.tbl_id=opp.id) AS gl_tanggal
FROM oa_pindah_pakan opp
WHERE NOT EXISTS(SELECT 1 FROM kirim_pakan kp WHERE kp.no_sj=opp.no_sj)
  AND NOT EXISTS(SELECT 1 FROM retur_pakan rp WHERE rp.no_retur=opp.no_sj)
ORDER BY opp.id;
