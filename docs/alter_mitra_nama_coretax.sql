/* ============================================================================
   MIGRASI: Tambah kolom nama_coretax ke tabel mitra (Parameter > Peternak)
   ============================================================================
   Konteks: field baru "Nama Coretax" ditambahkan di bawah NPWP pada form
   Peternak (application/modules/parameter/controllers/Peternak.php +
   views/peternak/add_form.php, edit_form.php, view_form.php).

   Aman dijalankan kapan saja, additive, idempotent.
   ============================================================================ */

IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('mitra') AND name = 'nama_coretax')
    ALTER TABLE mitra ADD nama_coretax VARCHAR(100) NULL;

-- Verifikasi
SELECT table_name, column_name, data_type, character_maximum_length
FROM information_schema.columns
WHERE table_name = 'mitra' AND column_name = 'nama_coretax';
