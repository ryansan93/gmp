SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
PRINT '=== status BYR/09/25/00015 (MJK) sekarang ===';
SELECT rp.nomor byr, rpd.no_bayar byo, rp.tgl_realisasi, CAST(rpd.transfer AS decimal(18,2)) transfer
FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
WHERE rpd.no_bayar='BYO/09/25/00001' AND rpd.transaksi='OA PAKAN';
PRINT '';
PRINT '=== SEMUA BYO yg GL sdh turun (<Juni) TAPI realisasi msh NULL/blm realized (sisa per unit) ===';
;WITH ubyo AS (
  SELECT kpop.id kpop_id, kpop.nomor byo,
    MIN(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) unit
  FROM konfirmasi_pembayaran_oa_pakan kpop LEFT JOIN konfirmasi_pembayaran_oa_pakan_det kpopd ON kpopd.id_header=kpop.id LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
  GROUP BY kpop.id, kpop.nomor
)
SELECT u.unit, k.nomor byo, rp.nomor byr, rp.tgl_realisasi, CAST(k.total+k.potongan_pph_23 AS decimal(18,2)) bruto
FROM konfirmasi_pembayaran_oa_pakan k
JOIN ubyo u ON u.kpop_id=k.id
LEFT JOIN realisasi_pembayaran_det rpd ON rpd.no_bayar=k.nomor AND rpd.transaksi='OA PAKAN'
LEFT JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
WHERE EXISTS (SELECT 1 FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.invoice=k.nomor AND dj.tanggal<@start)
  AND (rp.tgl_realisasi IS NULL OR rp.tgl_realisasi>=@start)
ORDER BY u.unit, k.nomor;
