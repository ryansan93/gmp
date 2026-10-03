/* ============================================================================
   BARU: Tabel RHPP versi MANAJEMEN (pakan kiriman intercompany diakui)
   ============================================================================
   Konteks: RHPP/RHPP Group tersimpan (tabel rhpp*, rhpp_group*) = versi RIIL
   (tanpa pakan yg ditransfer intercompany). Pakan transfer OPKG hanya ada di
   det_stok_siklus_manajemen (shadow), jadi vhost MANAJEMEN perlu versi lengkap.
   Tabel2 ini menyimpan versi lengkap itu HANYA untuk RHPP/RHPP Group yg noreg-nya
   punya pakan transfer (baris lain tdk diisi -> laporan fallback ke tabel riil).
   Dipakai laporan rekap (Laporan RHPP, RHPP V2, Laporan Harian Manajemen) supaya
   tdk hitung ulang per baris. Layar RHPP & export tetap hitung ulang.

   - *_manajemen           : angka ringkasan (1 baris per baris rhpp / rhpp_group)
   - *_pakan_manajemen     : baris pakan (tipe 'pakan') & pindah pakan ('pindah_pakan')
   - *_oa_pakan_manajemen  : ongkos angkut pakan ('oa_pakan') & pindah ('oa_pindah_pakan')

   Aman dijalankan kapan saja, idempotent.
   ============================================================================ */

-- rhpp_manajemen
IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'rhpp_manajemen')
BEGIN
    CREATE TABLE rhpp_manajemen (
        id          INT IDENTITY(1,1) PRIMARY KEY,
        id_rhpp     INT NOT NULL,            -- -> rhpp.id (baris plasma / inti)
        noreg       VARCHAR(20) NOT NULL,
        jenis       VARCHAR(20) NOT NULL,    -- 'rhpp_plasma' | 'rhpp_inti'
        jml_panen_ekor DECIMAL(18,2) NULL,
        jml_panen_kg DECIMAL(18,2) NULL,
        bb DECIMAL(18,4) NULL,
        fcr DECIMAL(18,4) NULL,
        deplesi DECIMAL(18,4) NULL,
        rata_umur DECIMAL(18,4) NULL,
        ip DECIMAL(18,4) NULL,
        tot_penjualan_ayam DECIMAL(22,2) NULL,
        tot_pembelian_sapronak DECIMAL(22,2) NULL,
        biaya_materai DECIMAL(22,2) NULL,
        bonus_pasar DECIMAL(22,2) NULL,
        bonus_kematian DECIMAL(22,2) NULL,
        bonus_insentif_fcr DECIMAL(22,2) NULL,
        total_bonus_insentif_listrik DECIMAL(22,2) NULL,
        pdpt_peternak_belum_pajak DECIMAL(22,2) NULL,
        lr_inti DECIMAL(22,2) NULL,
        jml_transfer INT NULL,          -- jumlah baris pakan transfer (det_stok_siklus_manajemen) saat dihitung -> deteksi usang
        tgl_hitung  DATETIME NULL
    );
    CREATE INDEX IX_rhpp_manajemen_id_rhpp ON rhpp_manajemen (id_rhpp);
    CREATE INDEX IX_rhpp_manajemen_noreg ON rhpp_manajemen (noreg);
END

-- rhpp_group_manajemen
IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'rhpp_group_manajemen')
BEGIN
    CREATE TABLE rhpp_group_manajemen (
        id              INT IDENTITY(1,1) PRIMARY KEY,
        id_rhpp_group   INT NOT NULL,        -- -> rhpp_group.id (baris plasma / inti)
        id_header       INT NOT NULL,        -- -> rhpp_group_header.id
        jenis           VARCHAR(20) NOT NULL,
        jml_panen_ekor DECIMAL(18,2) NULL,
        jml_panen_kg DECIMAL(18,2) NULL,
        bb DECIMAL(18,4) NULL,
        fcr DECIMAL(18,4) NULL,
        deplesi DECIMAL(18,4) NULL,
        rata_umur DECIMAL(18,4) NULL,
        ip DECIMAL(18,4) NULL,
        tot_penjualan_ayam DECIMAL(22,2) NULL,
        tot_pembelian_sapronak DECIMAL(22,2) NULL,
        biaya_materai DECIMAL(22,2) NULL,
        bonus_pasar DECIMAL(22,2) NULL,
        bonus_kematian DECIMAL(22,2) NULL,
        bonus_insentif_fcr DECIMAL(22,2) NULL,
        total_bonus_insentif_listrik DECIMAL(22,2) NULL,
        pdpt_peternak_belum_pajak DECIMAL(22,2) NULL,
        lr_inti DECIMAL(22,2) NULL,
        jml_transfer    INT NULL,
        tgl_hitung      DATETIME NULL
    );
    CREATE INDEX IX_rhpp_group_manajemen_id_rhpp_group ON rhpp_group_manajemen (id_rhpp_group);
    CREATE INDEX IX_rhpp_group_manajemen_id_header ON rhpp_group_manajemen (id_header);
END

-- rhpp_pakan_manajemen
IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'rhpp_pakan_manajemen')
BEGIN
    CREATE TABLE rhpp_pakan_manajemen (
        id          INT IDENTITY(1,1) PRIMARY KEY,
        id_header   INT NOT NULL,            -- -> rhpp_manajemen.id
        tipe        VARCHAR(20) NOT NULL,    -- 'pakan' | 'pindah_pakan'
        tanggal     DATE NULL,
        nota        VARCHAR(50) NULL,
        barang      VARCHAR(100) NULL,
        zak         DECIMAL(18,2) NULL,
        jumlah      DECIMAL(18,4) NULL,
        harga       DECIMAL(22,4) NULL,
        total       DECIMAL(22,2) NULL
    );
    CREATE INDEX IX_rhpp_pakan_manajemen_id_header ON rhpp_pakan_manajemen (id_header);
END

-- rhpp_group_pakan_manajemen
IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'rhpp_group_pakan_manajemen')
BEGIN
    CREATE TABLE rhpp_group_pakan_manajemen (
        id          INT IDENTITY(1,1) PRIMARY KEY,
        id_header   INT NOT NULL,            -- -> rhpp_group_manajemen.id
        tipe        VARCHAR(20) NOT NULL,    -- 'pakan' | 'pindah_pakan'
        tanggal     DATE NULL,
        nota        VARCHAR(50) NULL,
        barang      VARCHAR(100) NULL,
        zak         DECIMAL(18,2) NULL,
        jumlah      DECIMAL(18,4) NULL,
        harga       DECIMAL(22,4) NULL,
        total       DECIMAL(22,2) NULL
    );
    CREATE INDEX IX_rhpp_group_pakan_manajemen_id_header ON rhpp_group_pakan_manajemen (id_header);
END

-- rhpp_oa_pakan_manajemen
IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'rhpp_oa_pakan_manajemen')
BEGIN
    CREATE TABLE rhpp_oa_pakan_manajemen (
        id          INT IDENTITY(1,1) PRIMARY KEY,
        id_header   INT NOT NULL,            -- -> rhpp_manajemen.id
        tipe        VARCHAR(20) NOT NULL,    -- 'oa_pakan' | 'oa_pindah_pakan'
        tanggal     DATE NULL,
        nota        VARCHAR(50) NULL,
        nopol       VARCHAR(50) NULL,
        barang      VARCHAR(100) NULL,
        zak         DECIMAL(18,2) NULL,
        jumlah      DECIMAL(18,4) NULL,
        harga       DECIMAL(22,4) NULL,
        total       DECIMAL(22,2) NULL
    );
    CREATE INDEX IX_rhpp_oa_pakan_manajemen_id_header ON rhpp_oa_pakan_manajemen (id_header);
END

-- rhpp_group_oa_pakan_manajemen
IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'rhpp_group_oa_pakan_manajemen')
BEGIN
    CREATE TABLE rhpp_group_oa_pakan_manajemen (
        id          INT IDENTITY(1,1) PRIMARY KEY,
        id_header   INT NOT NULL,            -- -> rhpp_group_manajemen.id
        tipe        VARCHAR(20) NOT NULL,    -- 'oa_pakan' | 'oa_pindah_pakan'
        tanggal     DATE NULL,
        nota        VARCHAR(50) NULL,
        nopol       VARCHAR(50) NULL,
        barang      VARCHAR(100) NULL,
        zak         DECIMAL(18,2) NULL,
        jumlah      DECIMAL(18,4) NULL,
        harga       DECIMAL(22,4) NULL,
        total       DECIMAL(22,2) NULL
    );
    CREATE INDEX IX_rhpp_group_oa_pakan_manajemen_id_header ON rhpp_group_oa_pakan_manajemen (id_header);
END

-- Index bantu deteksi "noreg mana yg punya pakan transfer" (dipakai semua laporan manajemen)
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_det_stok_siklus_manajemen_noreg_jenis')
    CREATE INDEX IX_det_stok_siklus_manajemen_noreg_jenis ON det_stok_siklus_manajemen (noreg, jenis_barang);

-- Verifikasi
SELECT table_name FROM information_schema.tables
WHERE table_name IN (
    'rhpp_manajemen', 'rhpp_group_manajemen',
    'rhpp_pakan_manajemen', 'rhpp_group_pakan_manajemen',
    'rhpp_oa_pakan_manajemen', 'rhpp_group_oa_pakan_manajemen'
)
ORDER BY table_name;
