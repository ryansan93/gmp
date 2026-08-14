/* ============================================================================
   BARU: Tabel referensi master_badan_usaha (Parameter > Badan Usaha)
   ============================================================================
   Lookup table jenis badan usaha (PT, CV, Koperasi, Perorangan, dst) agar
   pilihan badan usaha konsisten dan menghindari input teks manual.

   Aman dijalankan kapan saja, idempotent.
   ============================================================================ */

IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'master_badan_usaha')
BEGIN
    CREATE TABLE master_badan_usaha (
        id_badan_usaha   VARCHAR(10) NOT NULL PRIMARY KEY,  -- BU01, BU02, BU03 (kode unik, auto-generate)
        nama_badan_usaha VARCHAR(50) NOT NULL,               -- Perseroan Terbatas, Persekutuan Komanditer
        singkatan        VARCHAR(10) NULL,                   -- PT, CV, KOP, PO
        status_hukum     BIT NOT NULL DEFAULT 0               -- 1 = Berbadan Hukum, 0 = Bukan Berbadan Hukum
    );
END

-- Verifikasi
SELECT table_name, column_name, data_type, character_maximum_length
FROM information_schema.columns
WHERE table_name = 'master_badan_usaha'
ORDER BY ordinal_position;


/* ============================================================================
   SEED DATA -- jenis badan usaha standar Indonesia
   Aman dijalankan kapan saja, idempotent (skip kalau nama_badan_usaha sudah ada).
   Kode BU01, BU02, dst mengikuti pola getNextKode() di BadanUsaha_model.
   ============================================================================ */

IF NOT EXISTS (SELECT 1 FROM master_badan_usaha WHERE nama_badan_usaha = 'PERSEROAN TERBATAS')
    INSERT INTO master_badan_usaha (id_badan_usaha, nama_badan_usaha, singkatan, status_hukum) VALUES ('BU01', 'PERSEROAN TERBATAS', 'PT', 1);

IF NOT EXISTS (SELECT 1 FROM master_badan_usaha WHERE nama_badan_usaha = 'PERSEKUTUAN KOMANDITER')
    INSERT INTO master_badan_usaha (id_badan_usaha, nama_badan_usaha, singkatan, status_hukum) VALUES ('BU02', 'PERSEKUTUAN KOMANDITER', 'CV', 0);

IF NOT EXISTS (SELECT 1 FROM master_badan_usaha WHERE nama_badan_usaha = 'KOPERASI')
    INSERT INTO master_badan_usaha (id_badan_usaha, nama_badan_usaha, singkatan, status_hukum) VALUES ('BU03', 'KOPERASI', 'KOP', 1);

IF NOT EXISTS (SELECT 1 FROM master_badan_usaha WHERE nama_badan_usaha = 'PERUSAHAAN PERORANGAN')
    INSERT INTO master_badan_usaha (id_badan_usaha, nama_badan_usaha, singkatan, status_hukum) VALUES ('BU04', 'PERUSAHAAN PERORANGAN', 'PO', 0);

IF NOT EXISTS (SELECT 1 FROM master_badan_usaha WHERE nama_badan_usaha = 'FIRMA')
    INSERT INTO master_badan_usaha (id_badan_usaha, nama_badan_usaha, singkatan, status_hukum) VALUES ('BU05', 'FIRMA', 'FA', 0);

IF NOT EXISTS (SELECT 1 FROM master_badan_usaha WHERE nama_badan_usaha = 'USAHA DAGANG')
    INSERT INTO master_badan_usaha (id_badan_usaha, nama_badan_usaha, singkatan, status_hukum) VALUES ('BU06', 'USAHA DAGANG', 'UD', 0);

IF NOT EXISTS (SELECT 1 FROM master_badan_usaha WHERE nama_badan_usaha = 'YAYASAN')
    INSERT INTO master_badan_usaha (id_badan_usaha, nama_badan_usaha, singkatan, status_hukum) VALUES ('BU07', 'YAYASAN', 'YYS', 1);

IF NOT EXISTS (SELECT 1 FROM master_badan_usaha WHERE nama_badan_usaha = 'PERORANGAN')
    INSERT INTO master_badan_usaha (id_badan_usaha, nama_badan_usaha, singkatan, status_hukum) VALUES ('BU08', 'PERORANGAN', '-', 0);

-- Verifikasi seed data
SELECT * FROM master_badan_usaha ORDER BY id_badan_usaha;
