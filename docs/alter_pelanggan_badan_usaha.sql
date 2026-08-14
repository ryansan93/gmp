/* ============================================================================
   MIGRASI: Tambah kolom badan_usaha ke tabel pelanggan (dipakai Supplier)
   ============================================================================
   Konteks: field baru "Badan Usaha" (referensi ke master_badan_usaha) di form
   Supplier (application/modules/parameter/controllers/Supplier.php +
   views/supplier/add_form.php, edit_form.php, view_form.php).

   Tabel `pelanggan` dipakai bersama oleh modul Pelanggan DAN Supplier
   (dibedakan lewat kolom `tipe`), jadi kolom ini otomatis juga tersedia utk
   Pelanggan kalau nanti mau dipakai di sana juga -- untuk saat ini hanya
   modul Supplier yang membaca/menulis kolom ini.

   PENTING: jalankan docs/create_master_badan_usaha.sql (+ alter Tbk-nya)
   dulu sebelum ini, karena kolom badan_usaha mereferensikan
   master_badan_usaha.id_badan_usaha.

   Aman dijalankan kapan saja, additive, idempotent.
   ============================================================================ */

IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('pelanggan') AND name = 'badan_usaha')
    ALTER TABLE pelanggan ADD badan_usaha VARCHAR(10) NULL;

-- Verifikasi
SELECT table_name, column_name, data_type, character_maximum_length
FROM information_schema.columns
WHERE table_name = 'pelanggan' AND column_name = 'badan_usaha';
