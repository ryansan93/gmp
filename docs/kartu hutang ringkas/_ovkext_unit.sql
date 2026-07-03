SET NOCOUNT ON;
DECLARE @end date='2026-06-14';
-- Σ konfir per unit (OVK EXTERN) vs Σ BBM naik per unit, + cek desimal
SELECT 'konfir' src, kpvd.kode_unit unit, CAST(SUM(kpvd.total) AS decimal(18,2)) total,
   CAST(SUM(kpvd.total) - FLOOR(SUM(kpvd.total)) AS decimal(18,2)) desimal
FROM konfirmasi_pembayaran_voadip kpv JOIN konfirmasi_pembayaran_voadip_det kpvd ON kpvd.id_header=kpv.id
WHERE kpv.tgl_bayar<=@end AND kpvd.kode_unit IN ('MJK','JBR') GROUP BY kpvd.kode_unit;

SELECT 'gl_naik' src, dj.unit, CAST(SUM(dj.nominal) AS decimal(18,2)) total,
   CAST(SUM(dj.nominal) - FLOOR(SUM(dj.nominal)) AS decimal(18,2)) desimal
FROM det_jurnal dj WHERE dj.coa_asal='21174.000' AND dj.tanggal<=@end AND dj.unit IN ('MJK','JBR') GROUP BY dj.unit;
