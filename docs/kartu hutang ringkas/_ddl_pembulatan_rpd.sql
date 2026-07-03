/* =====================================================================
   Kolom PEMBULATAN di realisasi_pembayaran_det
   ---------------------------------------------------------------------
   Tujuan: menampung selisih sub-rupiah antara tagihan dan transfer
   (artefak pembulatan rupiah penuh / akun 96010) supaya laporan hutang
   bisa meng-kredit 21213 sebesar tagihan saat selisihnya cuma pembulatan.

   Definisi: pembulatan = tagihan - transfer  HANYA bila |tagihan-transfer| < 1.
   - round-down (tagihan > transfer, < 1): pembulatan positif  -> kredit naik ke tagihan
   - jika |selisih| >= 1 (pembayaran parsial / overpay EPEK/THORIQ): pembulatan = 0
     -> ditangani cap min(transfer, tagihan, konfir.total) di controller.

   COMPUTED + PERSISTED:
   - tak perlu backfill (langsung terhitung utk semua baris lama),
   - tak perlu jaga sinkron di jalur create/edit/void (ikut tagihan/transfer),
   - deterministik (ABS/CASE/decimal) -> boleh PERSISTED.

   CATATAN: ini HEURISTIK pembulatan (turunan tagihan-transfer), BUKAN angka
   akun 96010 jurnal yang sebenarnya. Cukup untuk pelaporan hutang.

   Jalankan SEKALI di gmp_erp_live. Untuk rollback: ALTER TABLE ... DROP COLUMN pembulatan;
   ===================================================================== */

IF COL_LENGTH('realisasi_pembayaran_det', 'pembulatan') IS NULL
BEGIN
    ALTER TABLE realisasi_pembayaran_det
    ADD pembulatan AS (
        CASE
            WHEN tagihan IS NOT NULL AND ABS(tagihan - transfer) < 1
            THEN tagihan - transfer
            ELSE 0
        END
    ) PERSISTED;
END;

/* Verifikasi cepat per jenis transaksi setelah dibuat */
SELECT ISNULL(transaksi,'(null)') AS transaksi,
       COUNT(*)                                    AS total_rows,
       COUNT(CASE WHEN pembulatan <> 0 THEN 1 END) AS n_pembulatan,
       CAST(SUM(pembulatan) AS decimal(18,2))      AS sum_pembulatan
FROM realisasi_pembayaran_det
GROUP BY transaksi
ORDER BY transaksi;
