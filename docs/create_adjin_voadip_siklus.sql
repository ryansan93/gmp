/* ============================================================================
   CREATE TABLE: adjin_voadip_siklus
   Sumber data baru untuk fitur "Adjustment In OVK (Siklus)"
   (controller: transaksi/AdjustmentInVoadipSiklus.php).

   Pasangan arah IN dari `adjout_voadip_siklus` (lihat
   docs/create_adjout_voadip_siklus.sql) -- struktur kolom sengaja dibuat
   identik supaya query di controller (getLists/viewForm) simetris. Dipakai
   untuk koreksi stok OVK di level siklus/kandang (noreg) saat opname fisik
   menemukan stok LEBIH dari catatan `det_stok_siklus` (kebalikan dari
   Adjustment Out yang menurunkan stok).

   Tidak ada kolom status/delete -- ikut pola `adjin_doc`/`adjout_voadip_siklus`:
   hapus = hard delete baris, efeknya otomatis lenyap saat
   `hitung_stok_siklus` recompute ulang (baris sumbernya sudah tidak ada).

   SUDAH DIJALANKAN ke gmp_erp_live (2026-09-08, dibuat manual oleh user).
   Struktur terverifikasi cocok persis dgn definisi di bawah (kolom, tipe,
   index) via query sys.columns/sys.indexes. Smoke-test end-to-end (insert
   baris uji -> EXEC hitung_stok_siklus -> baris baru muncul benar di
   det_stok_siklus dgn jenis_trans='ADJIN' -> ROLLBACK) LOLOS 2026-09-08,
   lot stok lain (ORDER) tidak terganggu. Fitur "Adjustment In OVK (Siklus)"
   sekarang FUNGSIONAL PENUH -- tinggal daftarkan menu/hak akses
   `transaksi/AdjustmentInVoadipSiklus` scr manual di UI.
   ============================================================================ */

CREATE TABLE adjin_voadip_siklus (
    id          int IDENTITY(1,1) PRIMARY KEY,
    kode        varchar(20) NOT NULL,
    tanggal     date NOT NULL,
    mitra       varchar(15) NOT NULL,   -- nomor mitra, buat filter getLists() spt adjin_doc
    noreg       varchar(15) NOT NULL,
    kode_barang varchar(20) NOT NULL,
    harga       decimal(12,4) NULL,
    jumlah      decimal(13,2) NOT NULL,
    keterangan  varchar(255) NULL
)

CREATE INDEX ix_adjin_voadip_siklus_noreg_barang_tanggal
    ON adjin_voadip_siklus (noreg, kode_barang, tanggal)
