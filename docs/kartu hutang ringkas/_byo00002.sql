SET NOCOUNT ON;
PRINT '=== BYO/09/25/00002: unit (via konfir det), GL turun, realisasi status ===';
SELECT
  (SELECT MIN(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1))
     FROM konfirmasi_pembayaran_oa_pakan_det kpopd LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
     WHERE kpopd.id_header=(SELECT id FROM konfirmasi_pembayaran_oa_pakan WHERE nomor='BYO/09/25/00002')) AS unit_konfir,
  CAST((SELECT SUM(nominal) FROM det_jurnal WHERE coa_tujuan='21212.000' AND invoice='BYO/09/25/00002') AS decimal(18,2)) AS gl_turun,
  (SELECT TOP 1 dj.unit FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.invoice='BYO/09/25/00002') AS gl_unit_tag;
PRINT '=== realisasi BYO/09/25/00002 ===';
SELECT rpd.no_bayar, rp.nomor byr, rp.tgl_realisasi, rp.status, CAST(rpd.transfer AS decimal(18,2)) transfer
FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.no_bayar='BYO/09/25/00002';
PRINT '=== konfir det no_sj BYO/09/25/00002 (cek format & unit) ===';
SELECT kpopd.no_sj, kp.no_order FROM konfirmasi_pembayaran_oa_pakan_det kpopd
LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
WHERE kpopd.id_header=(SELECT id FROM konfirmasi_pembayaran_oa_pakan WHERE nomor='BYO/09/25/00002');
