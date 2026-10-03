/* ============================================================================
   MIGRASI: Setting Report + Neraca -- adopsi struktur bagia_sinar_jaya_corp
   ============================================================================
   Konteks: fitur Setting Report & laporan Neraca di-copy dari project
   bagia_sinar_jaya_corp yang pakai skema baru (tipe/urut/ref_group_ids/sign)
   menggantikan skema lama gmperp (posisi/posisi_jurnal/posisi_data).

   Dampak ke laporan lain yang masih pakai kolom lama:
     - report/controllers/Neraca.php                  -> SUDAH di-replace (pakai skema baru + no_op)
     - report/controllers/LabaRugiSummaryBulanan.php   -> SUDAH diperbaiki, tidak baca `posisi` lagi
       (catatan: perhitungan `posisi` di kode LAMA controller ini SELALU menghasilkan
        nominal = kredit-debet apa pun isi `posisi`-nya -- jadi kolom itu sebenarnya
        sudah tidak berpengaruh ke output sebelum migrasi ini, cuma dibaca tanpa efek)
     - accounting/controllers/SettingReport.php + views -> SUDAH di-replace total

   Jalankan STEP 1 dulu (aman, additive, idempotent -- boleh dijalankan berkali-kali).
   Jalankan STEP 2 HANYA SETELAH memverifikasi SettingReport, Neraca, dan
   LabaRugiSummaryBulanan semua jalan normal dengan kode yang baru di-deploy --
   STEP 2 sifatnya DESTRUKTIF (drop kolom) dan tidak bisa dibatalkan tanpa restore backup.
   ============================================================================ */


/* ============================================================================
   STEP 1 -- ADDITIVE (aman dijalankan kapan saja, idempotent)
   ============================================================================ */

-- 1. Tambah kolom tipe, urut, ref_group_ids ke setting_report_group
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('setting_report_group') AND name = 'tipe')
    ALTER TABLE setting_report_group ADD tipe VARCHAR(20) NOT NULL DEFAULT 'data';

IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('setting_report_group') AND name = 'urut')
    ALTER TABLE setting_report_group ADD urut INT NULL;

IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('setting_report_group') AND name = 'ref_group_ids')
    ALTER TABLE setting_report_group ADD ref_group_ids VARCHAR(500) NULL;

-- 2. Tambah kolom sign ke setting_report_group_item (kolom lama posisi/posisi_jurnal/posisi_data
--    DIBIARKAN dulu di STEP 1 -- baru di-drop di STEP 2 setelah semua laporan dipastikan aman)
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('setting_report_group_item') AND name = 'sign')
    ALTER TABLE setting_report_group_item ADD sign INT NOT NULL DEFAULT 1;

-- 3. Tambah kolom tipe ke item_report (dipakai utk 'spacer' -- baris kosong pemisah di Neraca)
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('item_report') AND name = 'tipe')
    ALTER TABLE item_report ADD tipe VARCHAR(20) NOT NULL DEFAULT 'item';

-- Verifikasi STEP 1
SELECT table_name, column_name, data_type, column_default
FROM information_schema.columns
WHERE table_name IN ('setting_report_group','setting_report_group_item','item_report')
  AND column_name IN ('tipe','urut','ref_group_ids','sign')
ORDER BY table_name, column_name;


/* ============================================================================
   STEP 2 -- DESTRUKTIF (drop kolom lama) -- JANGAN dijalankan otomatis.
   Cek dulu satu-satu di bawah SEBELUM uncomment & jalankan:
     [ ] SettingReport (accounting/SettingReport.php) sudah dites: tambah/edit/lihat/hapus laporan OK
     [ ] Neraca sudah dites: angka cocok dgn Neraca versi lama (posisi_data='saldo') utk periode yg sama
     [ ] LabaRugiSummaryBulanan sudah dites: angka tidak berubah dibanding sebelum migrasi
     [ ] Tidak ada laporan/fitur LAIN yang masih query kolom posisi/posisi_jurnal/posisi_data
         (grep ulang: grep -rn "posisi" application/modules --include=*.php)
     [ ] Backup database sudah diambil
   ============================================================================ */

-- IF EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('setting_report_group_item') AND name = 'posisi')
--     ALTER TABLE setting_report_group_item DROP COLUMN posisi;
--
-- IF EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('setting_report_group_item') AND name = 'posisi_jurnal')
--     ALTER TABLE setting_report_group_item DROP COLUMN posisi_jurnal;
--
-- IF EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('setting_report_group_item') AND name = 'posisi_data')
--     ALTER TABLE setting_report_group_item DROP COLUMN posisi_data;
