SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
;WITH ubyo AS (
  SELECT kpop.id kpop_id, kpop.nomor byo,
    MIN(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) unit,
    kpop.total konfir_net, kpop.potongan_pph_23 konfir_pph
  FROM konfirmasi_pembayaran_oa_pakan kpop
  LEFT JOIN konfirmasi_pembayaran_oa_pakan_det kpopd ON kpopd.id_header=kpop.id
  LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
  GROUP BY kpop.id, kpop.nomor, kpop.total, kpop.potongan_pph_23
),
glturun_byo AS (
  SELECT dj.invoice byo,
    SUM(CASE WHEN dj.coa_asal='24623.000' THEN dj.nominal ELSE 0 END) gl_pph,
    SUM(dj.nominal) gl_turun
  FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.tanggal<@start GROUP BY dj.invoice
)
SELECT u.unit,
  CAST(SUM(u.konfir_pph) AS decimal(18,2)) konfir_pph,
  CAST(SUM(g.gl_pph) AS decimal(18,2)) gl_pph,
  CAST(SUM(u.konfir_pph) - SUM(g.gl_pph) AS decimal(18,2)) selisih_pph
FROM ubyo u JOIN glturun_byo g ON g.byo=u.byo
GROUP BY u.unit
HAVING CAST(SUM(u.konfir_pph) - SUM(g.gl_pph) AS decimal(18,2)) <> 0
ORDER BY u.unit;
PRINT '=== total selisih pph (konfir - GL) ===';
