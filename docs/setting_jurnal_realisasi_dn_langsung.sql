/* ============================================================================
   JURNAL OTOMATIS REALISASI PEMBAYARAN: DN LANGSUNG IKUT JENIS DN-NYA
   Database: gmp_erp_live          Tabel: setting_automatic_jurnal  (id = 24, tbl_name = realisasi_pembayaran)
   ============================================================================
   Latar belakang: realisasi pembayaran yang berisi DN langsung (dn.tipe_dn = 'LANGSUNG', transaksi = 'DN')
   sebelumnya TIDAK punya baris pengajuan di query jurnal, sehingga yang terbentuk cuma kredit bank
   tanpa debet hutang. Perubahan (4 penggantian teks pada _query):
     1. @jns_trans (pembulatan selisih)  -> jenis efektif
     2. keterangan jurnal                -> 'PEMBAYARAN <jenis efektif> <no DN>'
     3. jenis_transaksi cursor           -> jenis efektif
     4. tabel 'pengajuan' ditambah cabang: select nomor DN, unit DN (dn.unit), nomor DN, pph 0 (tipe LANGSUNG)
   Jenis efektif DN: DOC -> DOC, PKN -> PAKAN, OVK -> VOADIP, RHPP -> PLASMA, OA -> OA PAKAN; transaksi lain tidak berubah.
   Hasil jurnal = sama dengan pembayaran jenis itu: Dr Hutang (per jenis, unit = dn.unit) / Cr Bank / PCZB 27001 per unit.
   Teruji (ROLLBACK) utk DOC, OVK, PAKAN, OA, RHPP: struktur akun sama dgn realisasi biasa; realisasi PAKAN biasa
   (id 5568) hasilnya identik antara query lama dan baru.

   PENTING: DN langsung WAJIB berunit (dn.unit) - unit menentukan unit hutang di jurnal.
   Backup query lama otomatis disimpan di tabel setting_automatic_jurnal_bak_dn (id, _query).

   CARA PAKAI: jalankan SATU batch (DEFAULT ROLLBACK = simulasi). Kalau hasilnya benar, ganti "ROLLBACK TRAN" jadi
   "COMMIT TRAN" lalu jalankan ulang. Skrip aman diulang (kalau sudah terpasang, akan berhenti dengan pesan).
   ============================================================================ */

SET XACT_ABORT ON;
BEGIN TRAN;

-- Pengaman 1: setting id 24 ada & milik realisasi_pembayaran
IF NOT EXISTS (SELECT 1 FROM setting_automatic_jurnal WHERE id = 24 AND tbl_name = 'realisasi_pembayaran')
    THROW 50001, 'setting_automatic_jurnal id 24 (realisasi_pembayaran) tidak ditemukan. Dibatalkan.', 1;

-- Pengaman 2: belum pernah dipasang
IF EXISTS (SELECT 1 FROM setting_automatic_jurnal WHERE id = 24 AND _query LIKE '%dnl.tipe_dn%')
    THROW 50002, 'Perubahan DN langsung sudah terpasang di setting id 24. Dibatalkan.', 1;

-- Backup query lama (sekali)
IF OBJECT_ID('setting_automatic_jurnal_bak_dn') IS NULL
    CREATE TABLE setting_automatic_jurnal_bak_dn (id int, _query varchar(max), dibuat datetime default getdate());
INSERT INTO setting_automatic_jurnal_bak_dn (id, _query) SELECT id, _query FROM setting_automatic_jurnal WHERE id = 24;

DECLARE @q varchar(max) = (SELECT _query FROM setting_automatic_jurnal WHERE id = 24);
DECLARE @panjang_lama int = LEN(@q);

-- Penggantian 1: teks lama harus muncul tepat 1x
IF (LEN(@q) - LEN(REPLACE(@q, 'cast(rpd.transaksi as varchar(15))', ''))) <> LEN('cast(rpd.transaksi as varchar(15))')
    THROW 50011, 'Penggantian 1: teks lama tidak ditemukan tepat 1x. Dibatalkan.', 1;
SET @q = REPLACE(@q, 'cast(rpd.transaksi as varchar(15))', 'cast((case when rpd.transaksi = ''DN'' then isnull((select case x.jenis_dn when ''DOC'' then ''DOC'' when ''PKN'' then ''PAKAN'' when ''OVK'' then ''VOADIP'' when ''RHPP'' then ''PLASMA'' when ''OA'' then ''OA PAKAN'' else null end from dn x where x.nomor = _rpd.no_bayar), rpd.transaksi) else rpd.transaksi end) as varchar(15))');

-- Penggantian 2: teks lama harus muncul tepat 1x
IF (LEN(@q) - LEN(REPLACE(@q, '''PEMBAYARAN ''+rpd.transaksi+'' ''+pengajuan.kode_trans as keterangan,', ''))) <> LEN('''PEMBAYARAN ''+rpd.transaksi+'' ''+pengajuan.kode_trans as keterangan,')
    THROW 50012, 'Penggantian 2: teks lama tidak ditemukan tepat 1x. Dibatalkan.', 1;
SET @q = REPLACE(@q, '''PEMBAYARAN ''+rpd.transaksi+'' ''+pengajuan.kode_trans as keterangan,', '''PEMBAYARAN ''+(case when rpd.transaksi = ''DN'' then isnull((select case x.jenis_dn when ''DOC'' then ''DOC'' when ''PKN'' then ''PAKAN'' when ''OVK'' then ''VOADIP'' when ''RHPP'' then ''PLASMA'' when ''OA'' then ''OA PAKAN'' else null end from dn x where x.nomor = rpd.no_bayar), rpd.transaksi) else rpd.transaksi end)+'' ''+pengajuan.kode_trans as keterangan,');

-- Penggantian 3: teks lama harus muncul tepat 1x
IF (LEN(@q) - LEN(REPLACE(@q, 'rpd.transaksi as jenis_transaksi,', ''))) <> LEN('rpd.transaksi as jenis_transaksi,')
    THROW 50013, 'Penggantian 3: teks lama tidak ditemukan tepat 1x. Dibatalkan.', 1;
SET @q = REPLACE(@q, 'rpd.transaksi as jenis_transaksi,', '(case when rpd.transaksi = ''DN'' then isnull((select case x.jenis_dn when ''DOC'' then ''DOC'' when ''PKN'' then ''PAKAN'' when ''OVK'' then ''VOADIP'' when ''RHPP'' then ''PLASMA'' when ''OA'' then ''OA PAKAN'' else null end from dn x where x.nomor = rpd.no_bayar), rpd.transaksi) else rpd.transaksi end) as jenis_transaksi,');

-- Penggantian 4: teks lama harus muncul tepat 1x
IF (LEN(@q) - LEN(REPLACE(@q, ') pengajuan', ''))) <> LEN(') pengajuan')
    THROW 50014, 'Penggantian 4: teks lama tidak ditemukan tepat 1x. Dibatalkan.', 1;
SET @q = REPLACE(@q, ') pengajuan', 'union all select dnl.nomor, dnl.unit as kode_unit, dnl.nomor as kode_trans, 0 as pph23, 0 as pph22 from dn dnl where dnl.tipe_dn = ''LANGSUNG'' ) pengajuan');

UPDATE setting_automatic_jurnal SET _query = @q WHERE id = 24;

-- Hasil: panjang query lama -> baru, dan 4 penanda baru ada
SELECT @panjang_lama AS panjang_lama, LEN(_query) AS panjang_baru,
       CASE WHEN _query LIKE '%dnl.tipe_dn%' THEN 1 ELSE 0 END AS cabang_dn_ada,
       CASE WHEN _query LIKE '%from dn x where x.nomor = _rpd.no_bayar%' THEN 1 ELSE 0 END AS jns_trans_dn,
       CASE WHEN _query LIKE '%as jenis_transaksi,%' THEN 1 ELSE 0 END AS jenis_transaksi_ada
FROM setting_automatic_jurnal WHERE id = 24;   -- harapan: panjang_baru > panjang_lama, semua penanda = 1

-- >>> DEFAULT = SIMULASI. Setelah angka di atas cocok, ganti ROLLBACK jadi COMMIT lalu jalankan ulang. <<<
ROLLBACK TRAN;
-- COMMIT TRAN;
