/* ============================================================================
   MIGRASI: Tambah kolom nama_coretax ke tabel ekspedisi (Parameter > Ekspedisi)
   ============================================================================
   Konteks: field baru "Nama Coretax" ditambahkan di bawah NPWP pada form
   Ekspedisi (application/modules/parameter/controllers/Ekspedisi.php +
   views/ekspedisi/addForm.php, editForm.php, viewForm.php).
   Sama seperti kolom nama_coretax yang sudah ditambahkan ke tabel mitra
   (docs/alter_mitra_nama_coretax.sql) dan pelanggan
   (docs/alter_pelanggan_nama_coretax.sql).

   Aman dijalankan kapan saja, additive, idempotent.
   ============================================================================ */

IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('ekspedisi') AND name = 'nama_coretax')
    ALTER TABLE ekspedisi ADD nama_coretax VARCHAR(100) NULL;

-- Verifikasi
SELECT table_name, column_name, data_type, character_maximum_length
FROM information_schema.columns
WHERE table_name = 'ekspedisi' AND column_name = 'nama_coretax';
