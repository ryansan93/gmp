SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
;WITH ubyo AS (
  SELECT kpop.id AS kpop_id, kpop.nomor AS byo,
    MIN(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) AS unit
  FROM konfirmasi_pembayaran_oa_pakan kpop
  LEFT JOIN konfirmasi_pembayaran_oa_pakan_det kpopd ON kpopd.id_header=kpop.id
  LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
  GROUP BY kpop.id, kpop.nomor
)
SELECT rp.nomor AS byr, k.nomor AS byo, u.unit, rp.tgl_realisasi, CAST(k.total+k.potongan_pph_23 AS decimal(18,2)) bruto
FROM konfirmasi_pembayaran_oa_pakan k
JOIN ubyo u ON u.kpop_id=k.id
LEFT JOIN realisasi_pembayaran_det rpd ON rpd.no_bayar=k.nomor AND rpd.transaksi='OA PAKAN'
LEFT JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
WHERE k.nomor IN (SELECT DISTINCT invoice FROM det_jurnal WHERE coa_tujuan='21212.000' AND coa_asal='27001.000' AND tanggal<@start)
ORDER BY u.unit, rp.nomor;
