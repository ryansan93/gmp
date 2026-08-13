/* ============================================================================
   MIGRASI: Tambah kolom nama_coretax ke tabel pelanggan (Parameter > Pelanggan)
   ============================================================================
   Konteks: field baru "Nama Coretax" ditambahkan di bawah NPWP pada form
   Pelanggan (application/modules/parameter/controllers/Pelanggan.php +
   views/pelanggan/add_form.php, edit_form.php, view_form.php, export_excel.php).
   Sama seperti kolom nama_coretax yang sudah ditambahkan ke tabel mitra
   (docs/alter_mitra_nama_coretax.sql) untuk Parameter > Peternak.

   Aman dijalankan kapan saja, additive, idempotent.
   ============================================================================ */

IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('pelanggan') AND name = 'nama_coretax')
    ALTER TABLE pelanggan ADD nama_coretax VARCHAR(100) NULL;

-- Verifikasi
SELECT table_name, column_name, data_type, character_maximum_length
FROM information_schema.columns
WHERE table_name = 'pelanggan' AND column_name = 'nama_coretax';
