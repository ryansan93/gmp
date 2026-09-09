/* ============================================================================
   CREATE TABLE: adjout_pakan_siklus
   Sumber data baru untuk fitur "Adjustment Out Pakan (Siklus)"
   (controller: transaksi/AdjustmentOutPakanSiklus.php).

   Analog `adjout_voadip_siklus` (lihat docs/create_adjout_voadip_siklus.sql)
   tapi untuk barang jenis PAKAN -- dipakai untuk koreksi stok PAKAN di
   level siklus/kandang (noreg) saat opname fisik tidak cocok dengan
   catatan `det_stok_siklus`.

   Tidak ada kolom status/delete -- ikut pola `adjin_doc`: hapus = hard
   delete baris, efeknya otomatis lenyap saat `hitung_stok_siklus`
   recompute ulang (baris sumbernya sudah tidak ada).

   SUDAH DIJALANKAN ke gmp_erp_live (2026-09-08, dibuat via PHP sqlsrv
   langsung dari Claude Code, bareng dengan `adjin_pakan_siklus`).
   Patch SP terkait ada di
   docs/alter_hitung_stok_siklus_adjin_adjout_pakan_siklus.sql -- sudah
   di-apply juga (smoke-test lolos: FIFO turun ke lot tertua dgn benar,
   lihat detail di file itu). Fitur "Adjustment Out Pakan (Siklus)"
   sekarang FUNGSIONAL PENUH -- tinggal daftarkan menu/hak akses
   `transaksi/AdjustmentOutPakanSiklus` scr manual di UI.
   ============================================================================ */

CREATE TABLE adjout_pakan_siklus (
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

CREATE INDEX ix_adjout_pakan_siklus_noreg_barang_tanggal
    ON adjout_pakan_siklus (noreg, kode_barang, tanggal)
