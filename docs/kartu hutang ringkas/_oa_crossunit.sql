SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
PRINT '=== BYO dgn konfir det LINTAS >1 unit (penyebab kredit mis-atribusi) ===';
SELECT kpop.nomor AS byo, rp.tgl_realisasi,
  COUNT(DISTINCT substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) AS n_unit,
  CAST(kpop.total+kpop.potongan_pph_23 AS decimal(18,2)) bruto
FROM konfirmasi_pembayaran_oa_pakan kpop
LEFT JOIN konfirmasi_pembayaran_oa_pakan_det kpopd ON kpopd.id_header=kpop.id
LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
LEFT JOIN realisasi_pembayaran_det rpd ON rpd.no_bayar=kpop.nomor AND rpd.transaksi='OA PAKAN'
LEFT JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
GROUP BY kpop.nomor, kpop.total, kpop.potongan_pph_23, rp.tgl_realisasi
HAVING COUNT(DISTINCT substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) > 1
ORDER BY n_unit DESC, bruto DESC;
