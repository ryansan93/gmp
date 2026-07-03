SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

-- helper: segmen ke-2 antar-slash via CHARINDEX
-- p1=CHARINDEX('/',X); p2=CHARINDEX('/',X,p1+1); SUBSTRING(X,p1+1,p2-p1-1)

PRINT '=== DEBET per unit (freight no_sj SJ/XXX, segmen-2) total harus 21.267.716.250 ===';
SELECT unit, CAST(SUM(t) AS decimal(18,2)) freight FROM (
  SELECT SUBSTRING(kp.no_sj, CHARINDEX('/',kp.no_sj)+1, CHARINDEX('/',kp.no_sj,CHARINDEX('/',kp.no_sj)+1)-CHARINDEX('/',kp.no_sj)-1) AS unit,
         SUM(dtp.jumlah)*kp.ongkos_angkut AS t
  FROM det_terima_pakan dtp LEFT JOIN terima_pakan tp ON dtp.id_header=tp.id LEFT JOIN kirim_pakan kp ON tp.id_kirim_pakan=kp.id
  WHERE tp.tgl_terima<@start AND kp.jenis_kirim='opkg'
  GROUP BY tp.no_bbm, kp.no_sj, kp.ongkos_angkut) x
GROUP BY unit ORDER BY unit;
PRINT '';
PRINT '=== KREDIT per unit (BYO via konfir det -> kirim_pakan.no_order segmen-2) ===';
SELECT u.unit, CAST(SUM(rpd.transfer + isnull(kpop.potongan_pph_23,0)) AS decimal(18,2)) kredit
FROM realisasi_pembayaran_det rpd
LEFT JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
LEFT JOIN konfirmasi_pembayaran_oa_pakan kpop ON kpop.nomor=rpd.no_bayar
LEFT JOIN (
  SELECT kpopd.id_header,
    MIN(SUBSTRING(kp.no_order, CHARINDEX('/',kp.no_order)+1, CHARINDEX('/',kp.no_order,CHARINDEX('/',kp.no_order)+1)-CHARINDEX('/',kp.no_order)-1)) AS unit
  FROM konfirmasi_pembayaran_oa_pakan_det kpopd LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
  GROUP BY kpopd.id_header
) u ON u.id_header=kpop.id
WHERE rpd.transaksi='OA PAKAN' AND rp.tgl_realisasi IS NOT NULL AND rp.tgl_realisasi<@start
GROUP BY u.unit ORDER BY u.unit;
PRINT '';
PRINT '=== GRAND TOTAL check ===';
SELECT CAST(SUM(t) AS decimal(18,2)) debet_total FROM (
  SELECT SUM(dtp.jumlah)*kp.ongkos_angkut AS t FROM det_terima_pakan dtp LEFT JOIN terima_pakan tp ON dtp.id_header=tp.id LEFT JOIN kirim_pakan kp ON tp.id_kirim_pakan=kp.id
  WHERE tp.tgl_terima<@start AND kp.jenis_kirim='opkg' GROUP BY tp.no_bbm, kp.no_sj, kp.ongkos_angkut) x;
