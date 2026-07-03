SET NOCOUNT ON;
;WITH ubyo AS (
  SELECT kpop.id kpop_id, kpop.nomor byo, MIN(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) unit
  FROM konfirmasi_pembayaran_oa_pakan kpop LEFT JOIN konfirmasi_pembayaran_oa_pakan_det kpopd ON kpopd.id_header=kpop.id LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
  GROUP BY kpop.id, kpop.nomor
)
SELECT u.unit, k.nomor byo, rp.nomor byr, rp.tgl_realisasi,
  CAST((SELECT SUM(nominal) FROM det_jurnal WHERE coa_tujuan='21212.000' AND invoice=k.nomor) AS decimal(18,2)) gl_turun
FROM konfirmasi_pembayaran_oa_pakan k
JOIN ubyo u ON u.kpop_id=k.id
LEFT JOIN realisasi_pembayaran_det rpd ON rpd.no_bayar=k.nomor AND rpd.transaksi='OA PAKAN'
LEFT JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
WHERE EXISTS (SELECT 1 FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.invoice=k.nomor)
  AND rp.tgl_realisasi IS NULL
ORDER BY u.unit, k.nomor;
