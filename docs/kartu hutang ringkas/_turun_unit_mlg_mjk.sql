SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
PRINT '=== Pembayaran (turun 21212) yg BYO-unit (report) = MLG/MJK tapi det_jurnal.unit BEDA ===';
;WITH ubyo AS (
  SELECT kpop.nomor byo,
    MIN(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) unit
  FROM konfirmasi_pembayaran_oa_pakan kpop
  LEFT JOIN konfirmasi_pembayaran_oa_pakan_det kpopd ON kpopd.id_header=kpop.id
  LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
  GROUP BY kpop.nomor
)
SELECT u.unit AS byo_unit, dj.unit AS gl_unit, dj.invoice AS byo, dj.coa_asal,
  CAST(SUM(dj.nominal) AS decimal(18,2)) nominal
FROM det_jurnal dj
JOIN ubyo u ON u.byo = dj.invoice
WHERE dj.coa_tujuan='21212.000' AND dj.tanggal<@start
  AND u.unit IN ('MLG','MJK') AND dj.unit <> u.unit
GROUP BY u.unit, dj.unit, dj.invoice, dj.coa_asal
ORDER BY u.unit, dj.invoice;
