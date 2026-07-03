SET NOCOUNT ON;
PRINT '=== oa_pindah_pakan: berapa yg tgl_terima terisi vs NULL ===';
SELECT CASE WHEN tgl_terima IS NULL THEN 'NULL' ELSE 'ada' END status, COUNT(*) n
FROM oa_pindah_pakan GROUP BY CASE WHEN tgl_terima IS NULL THEN 'NULL' ELSE 'ada' END;
PRINT '=== status tgl_terima 4 orphan (639,907,908,957) ===';
SELECT id, no_sj, tgl_terima, CAST(ongkos_angkut AS decimal(18,2)) ongkos FROM oa_pindah_pakan WHERE id IN (639,907,908,957) ORDER BY id;
PRINT '=== brp pindah yg no_sj-nya orphan (tak ada di kirim/retur) — ini yg butuh tgl_terima sendiri ===';
SELECT COUNT(*) n_orphan, SUM(CASE WHEN tgl_terima IS NOT NULL THEN 1 ELSE 0 END) sudah_ada_tgl
FROM oa_pindah_pakan opp
WHERE NOT EXISTS(SELECT 1 FROM kirim_pakan kp WHERE kp.no_sj=opp.no_sj)
  AND NOT EXISTS(SELECT 1 FROM retur_pakan rp WHERE rp.no_retur=opp.no_sj);
