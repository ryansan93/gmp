/* ============================================================================
   MIGRASI: Tambah kolom badan_usaha ke tabel mitra
   ============================================================================
   Konteks: field baru "Badan Usaha" (referensi ke master_badan_usaha) di form
   Peternak (application/modules/parameter/controllers/Peternak.php +
   views/peternak/add_form.php, edit_form.php, view_form.php), sama seperti
   yang sudah dipasang di Supplier, Pelanggan & Ekspedisi.

   Aman dijalankan kapan saja, additive, idempotent.
   ============================================================================ */

IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('mitra') AND name = 'badan_usaha')
    ALTER TABLE mitra ADD badan_usaha VARCHAR(10) NULL;

-- Verifikasi
SELECT table_name, column_name, data_type, character_maximum_length
FROM information_schema.columns
WHERE table_name = 'mitra' AND column_name = 'badan_usaha';
