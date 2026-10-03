-- **SUDAH DI-REVERT & DIGANTIKAN oleh memo MM2607310053** (JANGAN dijalankan lagi).
-- Sempat dijalankan (29 Agu 2026) lalu ketahuan double-counting krn memo koreksi
-- (Hutang OVK lawan 71105.002 SELISIH HARGA OVK) JUGA mengubah hutang utk invoice
-- yang sama -- jadi kolom total di sini sudah dibalikkan lagi ke nilai desimal
-- semula, dan pembulatannya sekarang MURNI via memo (auditable, tidak menyentuh
-- data sumber). Disimpan di sini cuma sebagai riwayat/referensi.
--
-- Membulatkan konfirmasi_pembayaran_voadip (OVK) supplier PT AGRINUSA JAYA SANTOSA
-- (19B004) yang nilainya masih desimal, KE HANYA yang belum ada Realisasi
-- Pembayaran sama sekali (aman -- tidak menyentuh histori yang sudah dijurnal/
-- dibayar, supaya tidak menimbulkan selisih baru pada invoice yang sudah lunas).
-- Rounding normal (ROUND half-away-from-zero) ke rupiah penuh.
--
-- Sudah dicek: setiap konfirmasi yang kena filter ini punya PERSIS 1 baris det,
-- dan header.total = SUM(det.total) -- jadi header & det dibulatkan ke nilai
-- yang sama, invariant tetap terjaga.
--
-- Cakupan per 29 Agustus 2026: 23 invoice (14 s.d. 31 Juli 2026 + 9 Agustus 2026),
-- lihat docs/agrinusa_konfirmasi_belum_bayar_desimal_31jul2026.md untuk daftarnya.

SET NOCOUNT ON;
BEGIN TRANSACTION;

IF OBJECT_ID('tempdb..#target_nomor') IS NOT NULL DROP TABLE #target_nomor;
SELECT kpv.nomor
INTO #target_nomor
FROM konfirmasi_pembayaran_voadip kpv
WHERE kpv.supplier = '19B004'
  AND kpv.total <> FLOOR(kpv.total)
  AND NOT EXISTS (
      SELECT 1 FROM realisasi_pembayaran_det rpd
      WHERE rpd.transaksi = 'VOADIP' AND rpd.no_bayar = kpv.nomor
  );

PRINT '=== SEBELUM ===';
SELECT kpv.nomor, kpv.total as header_total, d.total as det_total
FROM konfirmasi_pembayaran_voadip kpv
LEFT JOIN konfirmasi_pembayaran_voadip_det d ON d.id_header = kpv.id
WHERE kpv.nomor IN (SELECT nomor FROM #target_nomor)
ORDER BY kpv.nomor;

UPDATE d
SET d.total = ROUND(d.total, 0)
FROM konfirmasi_pembayaran_voadip_det d
JOIN konfirmasi_pembayaran_voadip kpv ON kpv.id = d.id_header
WHERE kpv.nomor IN (SELECT nomor FROM #target_nomor);

UPDATE kpv
SET kpv.total = ROUND(kpv.total, 0)
FROM konfirmasi_pembayaran_voadip kpv
WHERE kpv.nomor IN (SELECT nomor FROM #target_nomor);

PRINT '=== SESUDAH ===';
SELECT kpv.nomor, kpv.total as header_total, d.total as det_total
FROM konfirmasi_pembayaran_voadip kpv
LEFT JOIN konfirmasi_pembayaran_voadip_det d ON d.id_header = kpv.id
WHERE kpv.nomor IN (SELECT nomor FROM #target_nomor)
ORDER BY kpv.nomor;

-- CATATAN: transaksi masih terbuka (BEGIN TRANSACTION di atas, belum ada
-- COMMIT/ROLLBACK). Cek dulu hasil SEBELUM/SESUDAH di atas, baru jalankan
-- salah satu baris di bawah ini secara manual:
-- COMMIT TRANSACTION;
-- ROLLBACK TRANSACTION;
