SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
PRINT '=== MGT: BYO realized<start, report kredit vs GL turun (mismatch) ===';
;WITH ubyo AS (
  SELECT kpop.id kpop_id, kpop.nomor byo,
    MIN(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) unit
  FROM konfirmasi_pembayaran_oa_pakan kpop
  LEFT JOIN konfirmasi_pembayaran_oa_pakan_det kpopd ON kpopd.id_header=kpop.id
  LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
  GROUP BY kpop.id, kpop.nomor
)
SELECT k.nomor byo, rp.nomor byr, rp.tgl_realisasi,
  CAST(rpd.transfer AS decimal(18,2)) transfer, CAST(k.total AS decimal(18,2)) konfir_net, CAST(k.potongan_pph_23 AS decimal(18,2)) pph,
  CAST(rpd.transfer+isnull(k.potongan_pph_23,0) AS decimal(18,2)) report_kredit,
  CAST((SELECT SUM(dj.nominal) FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.invoice=k.nomor AND dj.tanggal<@start) AS decimal(18,2)) gl_turun
FROM konfirmasi_pembayaran_oa_pakan k
JOIN ubyo u ON u.kpop_id=k.id AND u.unit='MGT'
JOIN realisasi_pembayaran_det rpd ON rpd.no_bayar=k.nomor AND rpd.transaksi='OA PAKAN'
JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
WHERE rp.tgl_realisasi IS NOT NULL AND rp.tgl_realisasi<@start
  AND abs(isnull(rpd.transfer+isnull(k.potongan_pph_23,0),0) - isnull((SELECT SUM(dj.nominal) FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.invoice=k.nomor AND dj.tanggal<@start),0)) > 0.01
ORDER BY k.nomor;
