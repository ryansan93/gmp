/* ============================================================================
   BARU: Tabel Intercompany Pakan (Stok Rill vs Jurnal Manajemen lintas-GMP)
   ============================================================================
   Lihat plan: intercompany pakan - stok rill vs jurnal manajemen lintas-GMP.

   Konsep: 2 instance GMP terpisah (masing2 database sendiri, bisa beda server).
   Untuk transaksi pakan lintas-perusahaan:
     - Pihak PEMILIK FINANSIAL: jurnal tetap masuk tabel jurnal/det_jurnal ASLI
       (tidak berubah), tapi stok masuk ke stok_manajemen/det_stok_manajemen
       (shadow - visibilitas saja, BUKAN stok fisik nyata di gudangnya).
     - Pihak PEMEGANG FISIK: stok masuk ke det_stok/det_stok_trans ASLI (real,
       barang memang ada di gudangnya), tapi jurnal masuk ke
       jurnal_manajemen/det_jurnal_manajemen (shadow - visibilitas biaya,
       BUKAN hutang nyata ke supplier).

   Tabel2 shadow ini di-deploy IDENTIK di SETIAP instance GMP (satu codebase
   hasil Clone Perusahaan) karena arah transaksi generic - GMP manapun bisa
   jadi pemilik finansial ATAU pemegang fisik tergantung transaksinya.

   Transaksi domestik biasa (bukan lintas-GMP) TIDAK PERNAH menyentuh tabel2
   ini - default kosong, nol dampak ke laporan yang sudah ada.

   Aman dijalankan kapan saja, idempotent.
   ============================================================================ */

-- ----------------------------------------------------------------------------
-- 1) Registry partner (instance GMP lain yang dikenal)
-- ----------------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'intercompany_partner')
BEGIN
    CREATE TABLE intercompany_partner (
        kode_partner    VARCHAR(20) NOT NULL PRIMARY KEY,   -- mis. 'GMP2'
        nama_partner    VARCHAR(100) NOT NULL,
        base_url        VARCHAR(255) NOT NULL,              -- mis. https://gmp2.example.com/
        shared_secret   VARCHAR(255) NOT NULL,              -- HMAC shared secret, plaintext (konsisten dgn env.php)
        status          BIT NOT NULL DEFAULT 1,             -- 1=aktif, 0=nonaktif
        created_at      DATETIME NULL,
        updated_at      DATETIME NULL
    );
END

-- ----------------------------------------------------------------------------
-- 2) Referensi gudang milik partner (diisi manual, jarang berubah)
-- ----------------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'intercompany_partner_gudang')
BEGIN
    CREATE TABLE intercompany_partner_gudang (
        id                  INT IDENTITY(1,1) PRIMARY KEY,
        kode_partner        VARCHAR(20) NOT NULL,
        kode_gudang_partner VARCHAR(50) NOT NULL,   -- kode/id gudang di sisi partner (bukan id lokal)
        nama_gudang_partner VARCHAR(100) NOT NULL,
        status              BIT NOT NULL DEFAULT 1
    );
END

-- ----------------------------------------------------------------------------
-- 3) Log transaksi intercompany (basis rekonsiliasi + idempotency)
-- ----------------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'intercompany_pakan_log')
BEGIN
    CREATE TABLE intercompany_pakan_log (
        id                      INT IDENTITY(1,1) PRIMARY KEY,
        arah                    VARCHAR(10) NOT NULL,       -- 'KIRIM' atau 'TERIMA'
        kode_partner            VARCHAR(20) NOT NULL,
        referensi_idempotency   VARCHAR(64) NOT NULL,       -- GUID unik per transaksi, dicek dobel di sisi penerima
        kode_barang             VARCHAR(50) NOT NULL,
        jml_qty                 DECIMAL(18,4) NOT NULL,
        harga                   DECIMAL(18,4) NULL,         -- harga/nominal per satuan (dikirim dari sisi pemilik finansial)
        kode_gudang_tujuan      VARCHAR(50) NULL,           -- gudang tujuan di sisi penerima
        tbl_name_asal           VARCHAR(50) NULL,           -- nama tabel sumber di pengirim (mis. 'terima_pakan')
        tbl_id_asal             VARCHAR(50) NULL,           -- id baris sumber di pengirim
        tbl_name_tujuan         VARCHAR(50) NULL,           -- diisi setelah diproses di penerima (mis. 'det_stok')
        tbl_id_tujuan           VARCHAR(50) NULL,
        status                  VARCHAR(20) NOT NULL,       -- 'TERKIRIM' | 'DITERIMA' | 'GAGAL'
        waktu_kirim             DATETIME NULL,
        waktu_terima            DATETIME NULL,
        pesan_error             VARCHAR(500) NULL,
        CONSTRAINT UQ_intercompany_pakan_log_referensi UNIQUE (referensi_idempotency)
    );
END

-- ----------------------------------------------------------------------------
-- 4) Shadow stok (dipakai saat instance ini = PEMILIK FINANSIAL, bukan pemegang fisik)
--    Mirror struktur stok/det_stok/det_stok_trans apa adanya.
-- ----------------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'stok_manajemen')
BEGIN
    CREATE TABLE stok_manajemen (
        id          INT IDENTITY(1,1) PRIMARY KEY,
        periode     DATE NOT NULL,
        user_proses VARCHAR(10) NULL,
        tgl_proses  DATETIME NULL
    );
END

-- Kolom di bawah mirror PERSIS det_stok asli (dikonfirmasi via INFORMATION_SCHEMA
-- ke DB live 2026-09-17: id, id_header, tgl_trans, kode_gudang(int), kode_barang,
-- jumlah, hrg_jual, hrg_beli, kode_trans, jenis_barang, jenis_trans, jml_stok).
-- kode_gudang INT krn itu FK ke gudang.id (bukan string kode).
IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'det_stok_manajemen')
BEGIN
    CREATE TABLE det_stok_manajemen (
        id                      INT IDENTITY(1,1) PRIMARY KEY,
        id_header               INT NOT NULL,               -- -> stok_manajemen.id
        tgl_trans               DATE NOT NULL,
        kode_gudang             INT NULL,                   -- gudang TUJUAN (di sisi partner) - id gudang partner, bukan lokal
        kode_barang             VARCHAR(10) NOT NULL,
        jumlah                  DECIMAL(10,2) NOT NULL,
        hrg_jual                DECIMAL(12,4) NULL,
        hrg_beli                DECIMAL(12,4) NULL,
        kode_trans              VARCHAR(25) NULL,
        jenis_barang            VARCHAR(10) NULL,
        jenis_trans             VARCHAR(10) NULL,
        jml_stok                DECIMAL(10,2) NULL,
        id_intercompany_log     INT NULL                     -- -> intercompany_pakan_log.id
    );
END

-- Kolom inti mirror det_stok_trans asli (id, id_header, kode_trans, jumlah,
-- kode_barang - dikonfirmasi live, TIDAK ada tbl_name di tabel aslinya). Tambah
-- id_intercompany_log di sini (bukan tbl_name) krn ini tabel BARU milik kita
-- sendiri, jadi FK eksplisit lebih baik drpd konvensi tbl_name/tbl_id generik.
IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'det_stok_trans_manajemen')
BEGIN
    CREATE TABLE det_stok_trans_manajemen (
        id                  INT IDENTITY(1,1) PRIMARY KEY,
        id_header           INT NOT NULL,                   -- -> det_stok_manajemen.id
        kode_trans          VARCHAR(20) NULL,
        jumlah              DECIMAL(10,2) NOT NULL,
        kode_barang         VARCHAR(10) NOT NULL,
        id_intercompany_log INT NULL                        -- -> intercompany_pakan_log.id
    );
END

-- ----------------------------------------------------------------------------
-- 5) Shadow jurnal (dipakai saat instance ini = PEMEGANG FISIK, bukan pemilik finansial)
--    Mirror struktur jurnal/det_jurnal apa adanya.
-- ----------------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'jurnal_manajemen')
BEGIN
    CREATE TABLE jurnal_manajemen (
        id              INT IDENTITY(1,1) PRIMARY KEY,
        tanggal         DATE NOT NULL,
        unit            VARCHAR(20) NULL,       -- ikut konvensi jurnal.unit = wilayah.kode (string)
        jurnal_trans_id INT NULL
    );
END

-- Kolom & tipe mirror PERSIS det_jurnal asli (dikonfirmasi via INFORMATION_SCHEMA
-- ke DB live 2026-09-17). Perhatikan: gudang di det_jurnal asli bertipe INT
-- (bukan varchar spt sempat diasumsikan sebelum verifikasi).
IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'det_jurnal_manajemen')
BEGIN
    CREATE TABLE det_jurnal_manajemen (
        id                              INT IDENTITY(1,1) PRIMARY KEY,
        id_header                       INT NOT NULL,       -- -> jurnal_manajemen.id
        tanggal                         DATE NOT NULL,
        det_jurnal_trans_id             INT NULL,
        jurnal_trans_sumber_tujuan_id   INT NULL,
        supplier                        VARCHAR(10) NULL,
        perusahaan                      VARCHAR(10) NULL,
        keterangan                      VARCHAR(MAX) NULL,  -- det_jurnal asli bertipe text (tanpa batas)
        nominal                         DECIMAL(22,2) NOT NULL,
        saldo                           DECIMAL(22,2) NULL,
        ref_id                          INT NULL,
        asal                            VARCHAR(100) NULL,
        coa_asal                        VARCHAR(10) NULL,
        tujuan                          VARCHAR(100) NULL,
        coa_tujuan                      VARCHAR(10) NULL,
        unit                            VARCHAR(10) NULL,   -- wilayah.kode (string), ikut konvensi det_jurnal.unit
        pic                             VARCHAR(100) NULL,
        tbl_name                        VARCHAR(100) NULL,  -- 'intercompany_pakan_log'
        tbl_id                          VARCHAR(100) NULL,  -- -> intercompany_pakan_log.id
        noreg                           VARCHAR(15) NULL,
        periode                         DATE NULL,
        invoice                         VARCHAR(50) NULL,
        no_bukti                        VARCHAR(50) NULL,
        kode_trans                      VARCHAR(50) NULL,
        kode_jurnal                     VARCHAR(50) NULL,
        gudang                          INT NULL,
        pelanggan                       VARCHAR(15) NULL,
        mitra                           VARCHAR(15) NULL,
        ekspedisi                       VARCHAR(15) NULL,
        unit_tujuan                     VARCHAR(5) NULL,
        ref_kode                        VARCHAR(50) NULL,
        id_intercompany_log             INT NULL            -- -> intercompany_pakan_log.id
    );
END

-- Verifikasi
SELECT table_name FROM information_schema.tables
WHERE table_name IN (
    'intercompany_partner', 'intercompany_partner_gudang', 'intercompany_pakan_log',
    'stok_manajemen', 'det_stok_manajemen', 'det_stok_trans_manajemen',
    'jurnal_manajemen', 'det_jurnal_manajemen'
)
ORDER BY table_name;
