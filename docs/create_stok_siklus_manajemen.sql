/* ============================================================================
   BARU: Shadow stok siklus (pakan transfer OPKG intercompany diakui di MANAJEMEN)
   ============================================================================
   Dipakai IntercompanyPakanTerima.php (tulis/hapus) dan TSDRHPP.php + laporan
   RHPP (baca, UNION dgn det_stok_siklus asli di mode manajemen).

   Struktur det_stok_siklus_manajemen mirror det_stok_siklus asli (dicek ke DB
   live 2026-10-01) + id_intercompany_log (-> intercompany_pakan_log.id).
   stok_siklus_manajemen = header (periode, tgl_proses), 1 baris per periode.

   WAJIB dijalankan SETELAH create_intercompany_pakan.sql dan SEBELUM
   create_rhpp_manajemen.sql (script itu membuat index di tabel ini).
   Aman dijalankan kapan saja, idempotent.
   ============================================================================ */

IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'stok_siklus_manajemen')
BEGIN
    CREATE TABLE stok_siklus_manajemen (
        id          INT IDENTITY(1,1) PRIMARY KEY,
        periode     DATE NULL,
        tgl_proses  DATETIME NULL
    );
END

IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'det_stok_siklus_manajemen')
BEGIN
    CREATE TABLE det_stok_siklus_manajemen (
        id                  INT IDENTITY(1,1) PRIMARY KEY,
        id_header           INT NULL,                   -- -> stok_siklus_manajemen.id
        tgl_trans           DATE NULL,
        noreg               VARCHAR(15) NULL,
        kode_barang         VARCHAR(10) NULL,
        jumlah              DECIMAL(10,2) NULL,
        hrg_jual            DECIMAL(12,4) NULL,
        hrg_beli            DECIMAL(12,4) NULL,
        oa                  DECIMAL(12,2) NULL,
        kode_trans          VARCHAR(25) NULL,
        jenis_barang        VARCHAR(10) NULL,
        jenis_trans         VARCHAR(10) NULL,
        jml_stok            DECIMAL(10,2) NULL,
        id_intercompany_log INT NULL                    -- -> intercompany_pakan_log.id
    );
    CREATE INDEX IX_det_stok_siklus_manajemen_id_header ON det_stok_siklus_manajemen (id_header);
    CREATE INDEX IX_det_stok_siklus_manajemen_log ON det_stok_siklus_manajemen (id_intercompany_log);
END

-- Verifikasi
SELECT table_name FROM information_schema.tables
WHERE table_name IN ('stok_siklus_manajemen', 'det_stok_siklus_manajemen')
ORDER BY table_name;
