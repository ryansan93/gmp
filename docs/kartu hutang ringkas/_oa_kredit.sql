SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

PRINT '=== L1: rpd.no_bayar OA = BYO (konfir) atau BYR? + apakah nyambung ke kpop.nomor ===';
SELECT TOP 8 rpd.no_bayar, rp.nomor AS rp_nomor, CAST(rpd.transfer AS decimal(18,2)) transfer,
  kpop.nomor AS konfir_nomor, CAST(kpop.total AS decimal(18,2)) konfir_total, CAST(kpop.potongan_pph_23 AS decimal(18,2)) pph
FROM realisasi_pembayaran_det rpd
JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
LEFT JOIN konfirmasi_pembayaran_oa_pakan kpop ON kpop.nomor=rpd.no_bayar
WHERE rpd.transaksi='OA PAKAN' AND rp.tgl_realisasi<@start ORDER BY rp.tgl_realisasi DESC;

PRINT '=== L2: KREDIT bruto-driven-by-realisasi = SUM(kpop bruto) utk konfir yg no_bayar realized<start ===';
SELECT CAST(SUM(kpop.total+kpop.potongan_pph_23) AS decimal(18,2)) kredit_bruto_realized,
       COUNT(*) n_match,
       CAST(SUM(rpd.transfer) AS decimal(18,2)) transfer_check
FROM realisasi_pembayaran_det rpd
JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
JOIN konfirmasi_pembayaran_oa_pakan kpop ON kpop.nomor=rpd.no_bayar
WHERE rpd.transaksi='OA PAKAN' AND rp.tgl_realisasi<@start;

PRINT '=== L3: berapa OA realisasi rows yg TIDAK match konfir (no_bayar bukan BYO)? ===';
SELECT COUNT(*) n_nomatch, CAST(SUM(rpd.transfer) AS decimal(18,2)) transfer_nomatch
FROM realisasi_pembayaran_det rpd
JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
LEFT JOIN konfirmasi_pembayaran_oa_pakan kpop ON kpop.nomor=rpd.no_bayar
WHERE rpd.transaksi='OA PAKAN' AND rp.tgl_realisasi<@start AND kpop.nomor IS NULL;

PRINT '=== L4 target: GL turun = 20.714.084.752,50 ===';
