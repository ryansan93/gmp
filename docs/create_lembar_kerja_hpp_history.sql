/*
 * Tabel snapshot histori bulanan utk laporan Lembar Kerja HPP (report/LembarKerjaHpp.php).
 * Isi: semua kolom laporan (Saldo Awal/Produksi/Tersedia/Dijual/Saldo Akhir x 8 kategori
 * DOC/Pakan/OVK/OA/BL/BTL/RHPP/Total) per noreg per bulan, plus info noreg/proporsi/stok.
 *
 * Tujuan: dipakai sbg dasar Saldo Awal bulan berikutnya (ambil saldo_akhir_* langsung dari
 * snapshot bulan sebelumnya kalau ada, drpd rekalkulasi rantai 2-bulan yg dipakai sekarang).
 * Kunci unik (noreg, tahun, bulan) - support upsert/re-save idempotent kalau digenerate ulang
 * utk periode yg sama.
 *
 * Jalankan sekali di database (gmp_erp_live). Idempotent.
 */
IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'lembar_kerja_hpp_history')
BEGIN
    CREATE TABLE lembar_kerja_hpp_history (
        id int IDENTITY(1,1) PRIMARY KEY,
        unit varchar(10) NOT NULL,
        noreg varchar(20) NOT NULL,
        nama varchar(150) NULL,
        jenis_mitra varchar(5) NULL,
        tgl_chick_in datetime NULL,
        populasi int NULL,
        tahun int NOT NULL,
        bulan tinyint NOT NULL,
        start_date date NOT NULL,
        end_date date NOT NULL,

        sa_awal_doc decimal(18,2) NOT NULL DEFAULT 0,
        sa_awal_pakan decimal(18,2) NOT NULL DEFAULT 0,
        sa_awal_ovk decimal(18,2) NOT NULL DEFAULT 0,
        sa_awal_oa decimal(18,2) NOT NULL DEFAULT 0,
        sa_awal_bl decimal(18,2) NOT NULL DEFAULT 0,
        sa_awal_btl decimal(18,2) NOT NULL DEFAULT 0,
        sa_awal_rhpp decimal(18,2) NOT NULL DEFAULT 0,
        sa_awal_total decimal(18,2) NOT NULL DEFAULT 0,

        produksi_doc decimal(18,2) NOT NULL DEFAULT 0,
        produksi_pakan decimal(18,2) NOT NULL DEFAULT 0,
        produksi_ovk decimal(18,2) NOT NULL DEFAULT 0,
        produksi_oa decimal(18,2) NOT NULL DEFAULT 0,
        produksi_bl decimal(18,2) NOT NULL DEFAULT 0,
        produksi_btl decimal(18,2) NOT NULL DEFAULT 0,
        produksi_rhpp decimal(18,2) NOT NULL DEFAULT 0,
        produksi_total decimal(18,2) NOT NULL DEFAULT 0,

        tersedia_doc decimal(18,2) NOT NULL DEFAULT 0,
        tersedia_pakan decimal(18,2) NOT NULL DEFAULT 0,
        tersedia_ovk decimal(18,2) NOT NULL DEFAULT 0,
        tersedia_oa decimal(18,2) NOT NULL DEFAULT 0,
        tersedia_bl decimal(18,2) NOT NULL DEFAULT 0,
        tersedia_btl decimal(18,2) NOT NULL DEFAULT 0,
        tersedia_rhpp decimal(18,2) NOT NULL DEFAULT 0,
        tersedia_total decimal(18,2) NOT NULL DEFAULT 0,

        dijual_doc decimal(18,2) NOT NULL DEFAULT 0,
        dijual_pakan decimal(18,2) NOT NULL DEFAULT 0,
        dijual_ovk decimal(18,2) NOT NULL DEFAULT 0,
        dijual_oa decimal(18,2) NOT NULL DEFAULT 0,
        dijual_bl decimal(18,2) NOT NULL DEFAULT 0,
        dijual_btl decimal(18,2) NOT NULL DEFAULT 0,
        dijual_rhpp decimal(18,2) NOT NULL DEFAULT 0,
        dijual_total decimal(18,2) NOT NULL DEFAULT 0,

        saldo_akhir_doc decimal(18,2) NOT NULL DEFAULT 0,
        saldo_akhir_pakan decimal(18,2) NOT NULL DEFAULT 0,
        saldo_akhir_ovk decimal(18,2) NOT NULL DEFAULT 0,
        saldo_akhir_oa decimal(18,2) NOT NULL DEFAULT 0,
        saldo_akhir_bl decimal(18,2) NOT NULL DEFAULT 0,
        saldo_akhir_btl decimal(18,2) NOT NULL DEFAULT 0,
        saldo_akhir_rhpp decimal(18,2) NOT NULL DEFAULT 0,
        saldo_akhir_total decimal(18,2) NOT NULL DEFAULT 0,

        terjual int NOT NULL DEFAULT 0,
        persentase_dijual decimal(9,4) NOT NULL DEFAULT 0,
        start_date_proporsi datetime NULL,
        end_date_proporsi datetime NULL,
        hari decimal(10,2) NOT NULL DEFAULT 0,
        proporsi decimal(18,2) NOT NULL DEFAULT 0,
        stock_tersedia int NOT NULL DEFAULT 0,
        saldo_awal_stok int NOT NULL DEFAULT 0,
        sisa_stok int NOT NULL DEFAULT 0,

        created_at datetime NOT NULL DEFAULT GETDATE(),
        created_by varchar(50) NULL,

        CONSTRAINT UQ_lembar_kerja_hpp_history_noreg_periode UNIQUE (noreg, tahun, bulan)
    );

    CREATE INDEX IX_lembar_kerja_hpp_history_unit_periode ON lembar_kerja_hpp_history (unit, tahun, bulan);
END
GO
