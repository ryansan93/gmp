SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
;WITH ubyo AS (
  SELECT kpop.nomor byo, MIN(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) unit
  FROM konfirmasi_pembayaran_oa_pakan kpop LEFT JOIN konfirmasi_pembayaran_oa_pakan_det kpopd ON kpopd.id_header=kpop.id LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
  GROUP BY kpop.nomor
),
-- per BYR: unit dari no_bayar realisasi (report) vs unit dari invoice GL
pay AS (
  SELECT rp.nomor byr, rpd.no_bayar AS rpd_byo, rp.tgl_realisasi,
    CAST(SUM(rpd.transfer) AS decimal(18,2)) transfer
  FROM realisasi_pembayaran rp JOIN realisasi_pembayaran_det rpd ON rpd.id_header=rp.id
  WHERE rpd.transaksi='OA PAKAN' AND rp.tgl_realisasi<@start GROUP BY rp.nomor, rpd.no_bayar, rp.tgl_realisasi
),
gltag AS (
  SELECT dj.kode_trans byr, dj.invoice AS gl_byo, CAST(SUM(dj.nominal) AS decimal(18,2)) gl_turun
  FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.tbl_name='realisasi_pembayaran' AND dj.tanggal<@start
  GROUP BY dj.kode_trans, dj.invoice
)
SELECT p.byr, p.rpd_byo, ur.unit AS report_unit, g.gl_byo, ug.unit AS gl_unit, p.transfer
FROM pay p
LEFT JOIN gltag g ON g.byr=p.byr
LEFT JOIN ubyo ur ON ur.byo=p.rpd_byo
LEFT JOIN ubyo ug ON ug.byo=g.gl_byo
WHERE isnull(ur.unit,'')<>isnull(ug.unit,'') AND (ur.unit='MLG' OR ug.unit='MLG')
ORDER BY p.byr;
