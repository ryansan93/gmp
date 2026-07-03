SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

PRINT '=== (1) OVERLAP D1/D2: no_sj freight-opkg yg blm-konfir TAPI sebetulnya ADA di konfir det ===';
PRINT '    (NOT EXISTS gagal dedup karena format/leg beda). Hitung total nilai freight yg double-count.';
-- freight opkg blm-konfir, tapi cek apakah no_sj-nya ada di konfir det dgn LIKE/trim
SELECT TOP 20 kp.no_sj, CAST(SUM(dtp.jumlah)*kp.ongkos_angkut AS decimal(18,2)) freight
FROM det_terima_pakan dtp
LEFT JOIN terima_pakan tp ON dtp.id_header=tp.id
LEFT JOIN kirim_pakan kp ON tp.id_kirim_pakan=kp.id
WHERE tp.tgl_terima<@start AND kp.jenis_kirim='opkg'
  AND NOT EXISTS(SELECT * FROM konfirmasi_pembayaran_oa_pakan_det kpopd WHERE kpopd.no_sj=kp.no_sj)
GROUP BY kp.no_sj, tp.no_bbm, kp.ongkos_angkut
ORDER BY freight DESC;

PRINT '=== (2) 27001 clearing: detail 26 entri (no_jurnal, tanggal, nominal, keterangan) ===';
SELECT TOP 30 dj.tanggal, dj.no_jurnal, dj.tbl_name, CAST(dj.nominal AS decimal(18,2)) nominal, dj.keterangan
FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.coa_asal='27001.000' AND dj.tanggal<@start
ORDER BY dj.tanggal;

PRINT '=== (3a) kolom realisasi_pembayaran_det ===';
SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='realisasi_pembayaran_det' ORDER BY ORDINAL_POSITION;
PRINT '=== (3b) sample OA realisasi rows ===';
SELECT TOP 10 rpd.no_bayar, rpd.transaksi, CAST(rpd.transfer AS decimal(18,2)) transfer, rp.ekspedisi
FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
WHERE rpd.transaksi='OA PAKAN' AND rp.tgl_realisasi<@start ORDER BY rp.tgl_realisasi DESC;
PRINT '=== (3c) apakah no_bayar OA = nomor konfirmasi BYO? ===';
SELECT TOP 10 kpop.nomor, kpop.tgl_bayar, CAST(kpop.total AS decimal(18,2)) total, CAST(kpop.potongan_pph_23 AS decimal(18,2)) pph FROM konfirmasi_pembayaran_oa_pakan kpop WHERE kpop.tgl_bayar<@start ORDER BY kpop.tgl_bayar DESC;
