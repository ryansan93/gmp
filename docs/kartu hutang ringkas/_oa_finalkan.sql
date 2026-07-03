SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

PRINT '=== BRUTO (clearing+pph) per unit utk 26 BYO NULL-realisasi = harusnya = selisih user ===';
;WITH ubyo AS (
  SELECT kpop.id AS kpop_id, kpop.nomor AS byo,
    MIN(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) AS unit
  FROM konfirmasi_pembayaran_oa_pakan kpop
  LEFT JOIN konfirmasi_pembayaran_oa_pakan_det kpopd ON kpopd.id_header=kpop.id
  LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
  GROUP BY kpop.id, kpop.nomor
)
SELECT u.unit, CAST(SUM(dj.nominal) AS decimal(18,2)) AS bruto_belum_dikredit
FROM det_jurnal dj JOIN ubyo u ON u.byo = dj.invoice
WHERE dj.coa_tujuan='21212.000' AND dj.coa_asal IN ('27001.000','24623.000') AND dj.tanggal<@start
  AND dj.invoice IN (SELECT DISTINCT invoice FROM det_jurnal WHERE coa_tujuan='21212.000' AND coa_asal='27001.000' AND tanggal<@start)
GROUP BY u.unit ORDER BY u.unit;
