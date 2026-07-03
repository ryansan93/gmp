SET NOCOUNT ON;
DECLARE @cut date='2026-05-01';
PRINT '=== MLG <Mei: report debet (freight+pindah) vs GL naik(det_jurnal.unit=MLG) ===';
SELECT
 (SELECT CAST(SUM(t) AS decimal(18,2)) FROM (
    SELECT sum(dtp.jumlah)*kp.ongkos_angkut t FROM det_terima_pakan dtp LEFT JOIN terima_pakan tp ON dtp.id_header=tp.id LEFT JOIN kirim_pakan kp ON tp.id_kirim_pakan=kp.id
    WHERE tp.tgl_terima<@cut AND kp.jenis_kirim='opkg' AND substring(kp.no_sj, charindex('/',kp.no_sj+'//')+1, charindex('/',kp.no_sj+'//',charindex('/',kp.no_sj+'//')+1)-charindex('/',kp.no_sj+'//')-1)='MLG'
    GROUP BY tp.no_bbm, kp.ongkos_angkut, kp.no_sj) x) AS rep_freight_MLG,
 (SELECT CAST(SUM(dj.nominal) AS decimal(18,2)) FROM det_jurnal dj WHERE dj.coa_asal='21212.000' AND dj.unit='MLG' AND dj.tanggal<@cut) AS gl_naik_MLG;
PRINT '=== MLG <Mei: report kredit (transfer+pph, BYO-unit=MLG) vs GL turun (invoice->ubyo=MLG) ===';
;WITH ubyo AS (
  SELECT kpop.id kpop_id, kpop.nomor byo, MIN(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) unit
  FROM konfirmasi_pembayaran_oa_pakan kpop LEFT JOIN konfirmasi_pembayaran_oa_pakan_det kpopd ON kpopd.id_header=kpop.id LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj GROUP BY kpop.id, kpop.nomor
)
SELECT
 (SELECT CAST(SUM(rpd.transfer+isnull(kpop.potongan_pph_23,0)) AS decimal(18,2))
   FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
   LEFT JOIN konfirmasi_pembayaran_oa_pakan kpop ON kpop.nomor=rpd.no_bayar
   LEFT JOIN ubyo u ON u.kpop_id=kpop.id
   WHERE rpd.transaksi='OA PAKAN' AND rp.tgl_realisasi IS NOT NULL AND rp.tgl_realisasi<@cut AND u.unit='MLG') AS rep_kredit_MLG,
 (SELECT CAST(SUM(dj.nominal) AS decimal(18,2)) FROM det_jurnal dj JOIN ubyo u ON u.byo=dj.invoice WHERE dj.coa_tujuan='21212.000' AND dj.tanggal<@cut AND u.unit='MLG') AS gl_turun_MLG_byBYO;
