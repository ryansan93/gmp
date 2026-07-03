SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
-- unit per BYO
;WITH ubyo AS (
  SELECT kpop.id AS kpop_id, kpop.nomor AS byo,
    MIN(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) AS unit
  FROM konfirmasi_pembayaran_oa_pakan kpop
  LEFT JOIN konfirmasi_pembayaran_oa_pakan_det kpopd ON kpopd.id_header=kpop.id
  LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
  GROUP BY kpop.id, kpop.nomor
)
-- clearing 27001 per unit
SELECT u.unit, CAST(SUM(dj.nominal) AS decimal(18,2)) AS clearing_27001
FROM det_jurnal dj
JOIN ubyo u ON u.byo = dj.invoice
WHERE dj.coa_tujuan='21212.000' AND dj.coa_asal='27001.000' AND dj.tanggal<@start
GROUP BY u.unit ORDER BY u.unit;

PRINT '=== total clearing ===';
SELECT CAST(SUM(dj.nominal) AS decimal(18,2)) total FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.coa_asal='27001.000' AND dj.tanggal<@start;
