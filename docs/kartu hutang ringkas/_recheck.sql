SET NOCOUNT ON;
PRINT '=== BYO/09/25/00002: SEMUA baris realisasi (cek apakah ada >1) ===';
SELECT rp.nomor byr, rp.tgl_realisasi, rp.status, CAST(rpd.transfer AS decimal(18,2)) transfer, rpd.id rpd_id
FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.no_bayar='BYO/09/25/00002';
PRINT '=== Scan ulang: BYO OA realisasi NULL (pakai NOT EXISTS realized) — hindari masalah multi-row ===';
;WITH ubyo AS (
  SELECT kpop.id kpop_id, kpop.nomor byo, MIN(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) unit
  FROM konfirmasi_pembayaran_oa_pakan kpop LEFT JOIN konfirmasi_pembayaran_oa_pakan_det kpopd ON kpopd.id_header=kpop.id LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj GROUP BY kpop.id, kpop.nomor
)
SELECT u.unit, k.nomor byo,
  CAST((SELECT SUM(nominal) FROM det_jurnal WHERE coa_tujuan='21212.000' AND invoice=k.nomor) AS decimal(18,2)) gl_turun
FROM konfirmasi_pembayaran_oa_pakan k JOIN ubyo u ON u.kpop_id=k.id
WHERE EXISTS (SELECT 1 FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.invoice=k.nomor)
  AND NOT EXISTS (SELECT 1 FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.no_bayar=k.nomor AND rpd.transaksi='OA PAKAN' AND rp.tgl_realisasi IS NOT NULL)
ORDER BY u.unit, k.nomor;
