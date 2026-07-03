SET NOCOUNT ON;
DECLARE @cut date='2026-05-01';
PRINT '=== MLG <Mei: per BYO, report kredit (transfer+konfir_pph) vs GL turun total, yg beda ===';
;WITH ubyo AS (
  SELECT kpop.id kpop_id, kpop.nomor byo, kpop.potongan_pph_23 konfir_pph,
    MIN(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) unit
  FROM konfirmasi_pembayaran_oa_pakan kpop LEFT JOIN konfirmasi_pembayaran_oa_pakan_det kpopd ON kpopd.id_header=kpop.id LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
  GROUP BY kpop.id, kpop.nomor, kpop.potongan_pph_23
)
SELECT u.byo, rp.tgl_realisasi,
  CAST(rpd.transfer+isnull(u.konfir_pph,0) AS decimal(18,2)) report_kredit,
  CAST((SELECT SUM(dj.nominal) FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.invoice=u.byo AND dj.tanggal<@cut) AS decimal(18,2)) gl_turun,
  CAST(rpd.transfer+isnull(u.konfir_pph,0) - (SELECT SUM(dj.nominal) FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.invoice=u.byo AND dj.tanggal<@cut) AS decimal(18,2)) selisih
FROM ubyo u
JOIN realisasi_pembayaran_det rpd ON rpd.no_bayar=u.byo AND rpd.transaksi='OA PAKAN'
JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
WHERE u.unit='MLG' AND rp.tgl_realisasi IS NOT NULL AND rp.tgl_realisasi<@cut
  AND abs(rpd.transfer+isnull(u.konfir_pph,0) - isnull((SELECT SUM(dj.nominal) FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.invoice=u.byo AND dj.tanggal<@cut),0)) BETWEEN 0.01 AND 10
ORDER BY u.byo;
