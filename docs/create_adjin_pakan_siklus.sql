/* ============================================================================
   CREATE TABLE: adjin_pakan_siklus
   Sumber data baru untuk fitur "Adjustment In Pakan (Siklus)"
   (controller: transaksi/AdjustmentInPakanSiklus.php).

   Pasangan arah IN dari `adjout_pakan_siklus` (lihat
   docs/create_adjout_pakan_siklus.sql) -- struktur kolom identik dengan
   adjin_voadip_siklus/adjout_voadip_siklus (lihat
   docs/create_adjin_voadip_siklus.sql), hanya beda tabel sumber untuk
   barang jenis PAKAN, bukan OVK. Dipakai untuk koreksi stok PAKAN di level
   siklus/kandang (noreg) saat opname fisik menemukan stok LEBIH dari
   catatan `det_stok_siklus` (kebalikan dari Adjustment Out yang menurunkan
   stok).

   Tidak ada kolom status/delete -- ikut pola `adjin_doc`/`adjin_voadip_siklus`:
   hapus = hard delete baris, efeknya otomatis lenyap saat
   `hitung_stok_siklus` recompute ulang (baris sumbernya sudah tidak ada).

   SUDAH DIJALANKAN ke gmp_erp_live (2026-09-08, dibuat via PHP sqlsrv
   langsung dari Claude Code, bareng dengan `adjout_pakan_siklus`).
   Patch SP terkait ada di
   docs/alter_hitung_stok_siklus_adjin_adjout_pakan_siklus.sql -- sudah
   di-apply juga (smoke-test lolos, lihat detail di file itu). Fitur
   "Adjustment In Pakan (Siklus)" sekarang FUNGSIONAL PENUH -- tinggal
   daftarkan menu/hak akses `transaksi/AdjustmentInPakanSiklus` scr manual
   di UI (sama seperti AdjustmentInVoadipSiklus sebelumnya).
   ============================================================================ */

CREATE TABLE adjin_pakan_siklus (
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

CREATE INDEX ix_adjin_pakan_siklus_noreg_barang_tanggal
    ON adjin_pakan_siklus (noreg, kode_barang, tanggal)
