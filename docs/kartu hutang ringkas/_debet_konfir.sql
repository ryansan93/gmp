SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
PRINT '=== DEBET OA versi KONFIRMASI per unit (<Juni) — total & per unit ===';
SELECT oa_unit.unit, CAST(SUM(kpop.total + kpop.potongan_pph_23) AS decimal(18,2)) debet, COUNT(*) n
FROM konfirmasi_pembayaran_oa_pakan kpop
LEFT JOIN (
  select kpopd.id_header, min(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) as unit
  from konfirmasi_pembayaran_oa_pakan_det kpopd left join kirim_pakan kp on kp.no_sj=kpopd.no_sj group by kpopd.id_header
) oa_unit ON oa_unit.id_header=kpop.id
WHERE kpop.tgl_bayar < @start
GROUP BY oa_unit.unit ORDER BY oa_unit.unit;
