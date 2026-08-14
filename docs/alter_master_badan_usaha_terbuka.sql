/* ============================================================================
   MIGRASI: Tambah kolom is_terbuka ke tabel master_badan_usaha
   ============================================================================
   Konteks: menandai badan usaha yang berstatus "Terbuka" (Tbk / go public),
   contoh: PT. JAPFA COMFEED INDONESIA, Tbk. Ditaruh di form Master Badan Usaha
   (per-baris di tabel referensi), bukan per-perusahaan.

   Aman dijalankan kapan saja, additive, idempotent.
   ============================================================================ */

IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('master_badan_usaha') AND name = 'is_terbuka')
    ALTER TABLE master_badan_usaha ADD is_terbuka BIT NOT NULL DEFAULT 0;

-- Verifikasi
SELECT table_name, column_name, data_type
FROM information_schema.columns
WHERE table_name = 'master_badan_usaha' AND column_name = 'is_terbuka';


/* ============================================================================
   SEED DATA -- entri Perseroan Terbatas Terbuka (Tbk)
   Aman dijalankan kapan saja, idempotent (skip kalau nama_badan_usaha sudah ada).
   ============================================================================ */

IF NOT EXISTS (SELECT 1 FROM master_badan_usaha WHERE nama_badan_usaha = 'PERSEROAN TERBATAS TERBUKA')
    INSERT INTO master_badan_usaha (id_badan_usaha, nama_badan_usaha, singkatan, status_hukum, is_terbuka) VALUES ('BU09', 'PERSEROAN TERBATAS TERBUKA', 'PT Tbk', 1, 1);

-- Verifikasi seed data
SELECT * FROM master_badan_usaha ORDER BY id_badan_usaha;
