SET NOCOUNT ON;
PRINT '=== simulasi: 4 orphan pindah dgn ekspresi BARU (unit + supplier) ===';
SELECT opp.id, opp.no_sj,
  coalesce((SELECT min(e.nomor) FROM ekspedisi e WHERE e.nama = opp.ekspedisi), 'NULL') AS supplier_baru,
  coalesce(nullif(substring(opp.no_sj, charindex('/',opp.no_sj+'//')+1, charindex('/',opp.no_sj+'//',charindex('/',opp.no_sj+'//')+1)-charindex('/',opp.no_sj+'//')-1),''), 'NULL') AS unit_baru,
  CAST(opp.ongkos_angkut AS decimal(18,2)) ongkos
FROM oa_pindah_pakan opp WHERE opp.id IN (639,907,908,957) ORDER BY opp.id;
PRINT '=== cek: normal pindah (yg sudah ada krm) tetap benar? sample 5 ===';
SELECT TOP 5 opp.no_sj, krm.ekspedisi_id AS supplier_krm,
  coalesce(krm.ekspedisi_id, (select min(e.nomor) from ekspedisi e where e.nama = opp.ekspedisi)) AS supplier_final
FROM oa_pindah_pakan opp
LEFT JOIN (select kp.no_sj, kp.ekspedisi_id from kirim_pakan kp union all select no_retur, ekspedisi_id from retur_pakan) krm ON krm.no_sj=opp.no_sj
WHERE krm.ekspedisi_id IS NOT NULL ORDER BY opp.id DESC;
