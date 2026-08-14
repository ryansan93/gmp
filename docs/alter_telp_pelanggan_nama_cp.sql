/* ============================================================================
   MIGRASI: Tambah kolom nama_cp ke tabel telp_pelanggan (dipakai Supplier)
   ============================================================================
   Konteks: Parameter > Supplier -- field "Contact Person" (dulu satu nilai
   per supplier) digabung jadi 1 baris dengan Telepon, supaya 1 supplier bisa
   punya banyak Contact Person (misal CP untuk DOC & CP untuk Pakan, contoh
   kasus supplier JAPFA).

   Tabel `telp_pelanggan` dipakai bersama oleh Pelanggan DAN Supplier (satu
   tabel `pelanggan`, dibedakan kolom `tipe`), jadi kolom ini nullable dan
   untuk saat ini hanya diisi/dibaca oleh modul Supplier.

   Aman dijalankan kapan saja, additive, idempotent.
   ============================================================================ */

IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('telp_pelanggan') AND name = 'nama_cp')
    ALTER TABLE telp_pelanggan ADD nama_cp VARCHAR(100) NULL;

-- Verifikasi
SELECT table_name, column_name, data_type, character_maximum_length
FROM information_schema.columns
WHERE table_name = 'telp_pelanggan' AND column_name = 'nama_cp';
