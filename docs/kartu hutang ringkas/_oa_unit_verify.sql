SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
PRINT '=== format kode_unit DOC/PAKAN (utk konsistensi) ===';
SELECT DISTINCT TOP 8 kode_unit FROM konfirmasi_pembayaran_pakan_det WHERE kode_unit IS NOT NULL ORDER BY kode_unit;
PRINT '=== oa_pindah_pakan no_sj format ===';
SELECT TOP 5 no_sj, SUBSTRING(no_sj,4,3) AS unit_extract FROM oa_pindah_pakan ORDER BY id DESC;
PRINT '=== DEBET per unit (freight) <Juni — cek total tetap 21.267.716.250 ===';
SELECT SUBSTRING(kp.no_sj,4,3) AS unit, CAST(SUM(x.t) AS decimal(18,2)) freight FROM (
  SELECT tp.no_bbm, kp_.no_sj, sum(dtp.jumlah)*kp_.ongkos_angkut t, kp_.no_sj sj2
  FROM det_terima_pakan dtp LEFT JOIN terima_pakan tp ON dtp.id_header=tp.id LEFT JOIN kirim_pakan kp_ ON tp.id_kirim_pakan=kp_.id
  WHERE tp.tgl_terima<@start AND kp_.jenis_kirim='opkg'
  GROUP BY tp.no_bbm, kp_.no_sj, kp_.ongkos_angkut) x
  LEFT JOIN kirim_pakan kp ON kp.no_sj=x.sj2
GROUP BY SUBSTRING(kp.no_sj,4,3) ORDER BY freight DESC;
PRINT '=== KREDIT per unit (BYO=1unit) <Juni ===';
SELECT u.unit, CAST(SUM(rpd.transfer + isnull(kpop.potongan_pph_23,0)) AS decimal(18,2)) kredit
FROM realisasi_pembayaran_det rpd
LEFT JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
LEFT JOIN konfirmasi_pembayaran_oa_pakan kpop ON kpop.nomor=rpd.no_bayar
LEFT JOIN (SELECT id_header, MIN(SUBSTRING(no_sj,4,3)) unit FROM konfirmasi_pembayaran_oa_pakan_det GROUP BY id_header) u ON u.id_header=kpop.id
WHERE rpd.transaksi='OA PAKAN' AND rp.tgl_realisasi IS NOT NULL AND rp.tgl_realisasi<@start
GROUP BY u.unit ORDER BY kredit DESC;
