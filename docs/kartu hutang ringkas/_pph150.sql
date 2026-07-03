SET NOCOUNT ON;
DECLARE @cut date='2026-05-01';
PRINT '=== MLG: konfir PPh vs GL PPh (24623) per BYO, beda <> 0, realized<Mei ===';
;WITH ubyo AS (
  SELECT kpop.id kpop_id, kpop.nomor byo, kpop.potongan_pph_23 konfir_pph,
    MIN(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) unit
  FROM konfirmasi_pembayaran_oa_pakan kpop LEFT JOIN konfirmasi_pembayaran_oa_pakan_det kpopd ON kpopd.id_header=kpop.id LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
  GROUP BY kpop.id, kpop.nomor, kpop.potongan_pph_23
)
SELECT u.byo, rp.tgl_realisasi,
  CAST(u.konfir_pph AS decimal(18,2)) konfir_pph,
  CAST((SELECT SUM(dj.nominal) FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.coa_asal='24623.000' AND dj.invoice=u.byo) AS decimal(18,2)) gl_pph,
  CAST((SELECT SUM(dj.nominal) FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.coa_asal='24623.000' AND dj.invoice=u.byo) - u.konfir_pph AS decimal(18,2)) selisih_pph
FROM ubyo u
JOIN realisasi_pembayaran_det rpd ON rpd.no_bayar=u.byo AND rpd.transaksi='OA PAKAN'
JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
WHERE u.unit='MLG' AND rp.tgl_realisasi IS NOT NULL AND rp.tgl_realisasi<@cut
  AND CAST((SELECT SUM(dj.nominal) FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.coa_asal='24623.000' AND dj.invoice=u.byo) - u.konfir_pph AS decimal(18,2)) <> 0
ORDER BY u.byo;
