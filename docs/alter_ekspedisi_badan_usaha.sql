/* ============================================================================
   MIGRASI: Tambah kolom badan_usaha ke tabel ekspedisi
   ============================================================================
   Konteks: field baru "Badan Usaha" (referensi ke master_badan_usaha) di form
   Ekspedisi (application/modules/parameter/controllers/Ekspedisi.php +
   views/ekspedisi/addForm.php, editForm.php, viewForm.php), sama seperti yang
   sudah dipasang di Supplier & Pelanggan.

   Aman dijalankan kapan saja, additive, idempotent.
   ============================================================================ */

IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('ekspedisi') AND name = 'badan_usaha')
    ALTER TABLE ekspedisi ADD badan_usaha VARCHAR(10) NULL;

-- Verifikasi
SELECT table_name, column_name, data_type, character_maximum_length
FROM information_schema.columns
WHERE table_name = 'ekspedisi' AND column_name = 'badan_usaha';
