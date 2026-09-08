/* ============================================================================
   CREATE TABLE: adjout_voadip_siklus
   Sumber data baru untuk fitur "Adjustment Out OVK (Siklus)"
   (controller: transaksi/AdjustmentOutVoadipSiklus.php).

   Analog `adjin_doc` (koreksi DOC per-noreg) tapi untuk barang OVK arah
   OUT -- dipakai untuk koreksi stok OVK di level siklus/kandang (noreg)
   saat opname fisik tidak cocok dengan catatan `det_stok_siklus`.

   Tidak ada kolom status/delete -- ikut pola `adjin_doc`: hapus = hard
   delete baris, efeknya otomatis lenyap saat `hitung_stok_siklus`
   recompute ulang (baris sumbernya sudah tidak ada).

   BELUM DIJALANKAN -- apply manual ke database (dev dulu, baru produksi).
   ============================================================================ */

CREATE TABLE adjout_voadip_siklus (
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

CREATE INDEX ix_adjout_voadip_siklus_noreg_barang_tanggal
    ON adjout_voadip_siklus (noreg, kode_barang, tanggal)
