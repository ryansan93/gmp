SET NOCOUNT ON;
DECLARE @cut date='2026-05-01';
;WITH ubyo AS (
  SELECT kpop.id kpop_id, kpop.nomor byo, kpop.potongan_pph_23 konfir_pph,
    MIN(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) unit
  FROM konfirmasi_pembayaran_oa_pakan kpop LEFT JOIN konfirmasi_pembayaran_oa_pakan_det kpopd ON kpopd.id_header=kpop.id LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
  GROUP BY kpop.id, kpop.nomor, kpop.potongan_pph_23
)
SELECT
  CAST(SUM(rpd.transfer) AS decimal(18,2)) AS report_transfer,
  CAST(SUM(u.konfir_pph) AS decimal(18,2)) AS report_pph_konfir,
  CAST((SELECT SUM(dj.nominal) FROM det_jurnal dj JOIN ubyo u2 ON u2.byo=dj.invoice WHERE dj.coa_tujuan='21212.000' AND dj.coa_asal='11130.002' AND dj.tanggal<@cut AND u2.unit='MLG') AS decimal(18,2)) AS gl_transfer,
  CAST((SELECT SUM(dj.nominal) FROM det_jurnal dj JOIN ubyo u2 ON u2.byo=dj.invoice WHERE dj.coa_tujuan='21212.000' AND dj.coa_asal='24623.000' AND dj.tanggal<@cut AND u2.unit='MLG') AS decimal(18,2)) AS gl_pph
FROM ubyo u
JOIN realisasi_pembayaran_det rpd ON rpd.no_bayar=u.byo AND rpd.transaksi='OA PAKAN'
JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
WHERE u.unit='MLG' AND rp.tgl_realisasi IS NOT NULL AND rp.tgl_realisasi<@cut;
