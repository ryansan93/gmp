SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

PRINT '=== G1: BYO clearing-settled (Sept) — apakah PUNYA baris realisasi_pembayaran_det? ===';
SELECT k.nomor AS byo, CAST(k.total+k.potongan_pph_23 AS decimal(18,2)) bruto,
  (SELECT COUNT(*) FROM realisasi_pembayaran_det rpd WHERE rpd.no_bayar=k.nomor AND rpd.transaksi='OA PAKAN') AS n_realisasi,
  (SELECT CAST(SUM(rpd.transfer) AS decimal(18,2)) FROM realisasi_pembayaran_det rpd WHERE rpd.no_bayar=k.nomor AND rpd.transaksi='OA PAKAN') AS sum_transfer
FROM konfirmasi_pembayaran_oa_pakan k
WHERE k.nomor IN (SELECT DISTINCT dj.invoice FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.coa_asal='27001.000' AND dj.tanggal<@start)
ORDER BY k.nomor;

PRINT '=== G2: rekonstruksi kredit EXACT = SUM bruto utk SEMUA BYO yg muncul di GL-turun (by GL), + cek BYO tanpa konfir ===';
;WITH glbyo AS (SELECT DISTINCT dj.invoice AS byo FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.tanggal<@start)
SELECT CAST(SUM(k.total+k.potongan_pph_23) AS decimal(18,2)) bruto_all_glbyo,
       SUM(CASE WHEN k.nomor IS NULL THEN 1 ELSE 0 END) n_tanpa_konfir
FROM glbyo g LEFT JOIN konfirmasi_pembayaran_oa_pakan k ON k.nomor=g.byo;

PRINT '=== G3: BYO di GL-turun TANPA konfir (yg 12,8jt) — apa itu? ===';
;WITH glbyo AS (SELECT dj.invoice AS byo, SUM(dj.nominal) tot FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.tanggal<@start GROUP BY dj.invoice)
SELECT g.byo, CAST(g.tot AS decimal(18,2)) gl_turun FROM glbyo g
LEFT JOIN konfirmasi_pembayaran_oa_pakan k ON k.nomor=g.byo WHERE k.nomor IS NULL;

PRINT '=== G4: cek tgl_realisasi vs tgl jurnal — adakah BYO realized>=start tapi GL turun<start (atau sebaliknya) ===';
PRINT '   total transfer realisasi<start (20.263.390.094) vs GL 11130.002<start (20.263.390.097): beda 3 (rounding)';
