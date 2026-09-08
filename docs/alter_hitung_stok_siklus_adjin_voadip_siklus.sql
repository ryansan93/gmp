/* ============================================================================
   PATCH: hitung_stok_siklus -- tambah sumber baru `adjin_voadip_siklus`
   (koreksi IN manual stok OVK per-siklus/kandang, controller
   transaksi/AdjustmentInVoadipSiklus.php).

   Pasangan arah IN dari patch `adjout_voadip_siklus` (lihat
   docs/alter_hitung_stok_siklus_adjout_voadip_siklus.sql, sudah diterapkan
   2026-09-04). Section `@jenis like '%voadip%'` sekarang kenal 3 sumber:
     - DATA MASUK : terima_voadip (kirim opkg ke peternak)
     - DATA KELUAR: retur_voadip (retur OVK dari peternak),
                    adjout_voadip_siklus (koreksi OUT manual, sudah live)
   Belum ada jalur "koreksi manual masuk" -- ini yang ditambahkan.

   SUDAH DIJALANKAN ke gmp_erp_live (2026-09-08), lewat PHP `sqlsrv` langsung
   (sama seperti patch adjout_voadip_siklus -- body live diambil dulu via
   `sys.sql_modules.definition`, bukan OBJECT_DEFINITION() yg kepotong utk
   objek sebesar ini). Titik sisip dicari by-line dari body live terkini
   (bukan diasumsikan dari referensi di bawah, karena patch adjout_voadip_siklus
   sudah masuk duluan), lalu divalidasi 2 lapis sebelum promote permanen:
     1. `SET PARSEONLY ON` atas teks ALTER PROCEDURE penuh -- lolos.
     2. Smoke-test dalam `BEGIN TRAN ... ROLLBACK` (1 batch, ALTER dibungkus
        `EXEC(@sql)` dynamic SQL spy bisa sebatch dgn BEGIN TRAN/ROLLBACK):
        ALTER berhasil (definition 259438 -> 260644 byte), lalu EXEC ulang
        cabang existing `terima_voadip` (noreg nyata 26080200101, tv_id
        17711) sbg regression check -- lolos tanpa error, lalu ROLLBACK
        dikonfirmasi definition balik ke 259438 byte.
   Baru setelah lolos kedua validasi, ALTER PROCEDURE yg SAMA PERSIS
   di-apply ulang di luar transaksi (permanen), dgn pre-check tambahan
   (bandingkan body live saat itu byte-per-byte thd body yg dipatch, tolak
   apply kalau beda -- jaga2 kalau ada sesi lain yg ubah SP di antara fetch
   dan apply). Verifikasi pasca-apply: body live baru mengandung kedua
   marker patch (`@tbl_name = 'adjin_voadip_siklus'` dan
   `from adjin_voadip_siklus ais`) -- confirmed. Backup body SEBELUM patch
   ini ada di
   `docs/backup_hitung_stok_siklus_before_adjin_voadip_siklus_20260908.sql`
   (untuk rollback kalau perlu; ganti CREATE jadi ALTER di file itu).

   CATATAN: patch ini baru separuh jalan -- tabel `adjin_voadip_siklus`
   SENDIRI belum dibuat (lihat docs/create_adjin_voadip_siklus.sql, masih
   "BELUM DIJALANKAN"). Cabang baru di SP ini aman menganggur (deferred name
   resolution SQL Server) sampai tabelnya ada; fitur belum benar2 hidup
   sampai create table itu juga di-apply.

   Beda dengan adjout_voadip_siklus: fitur ini arah MASUK, jadi tidak perlu
   konsumsi FIFO lot manapun (tidak ada WHILE loop di `data_keluar`) --
   cukup INSERT baris baru langsung ke `det_stok_siklus` di blok DATA MASUK,
   PERSIS pola `adjin_doc` di section `@jenis like '%doc%'` (lihat
   `union all ... from adjin_doc` di body SP, dekat baris ~312-330 versi
   backup DOC).

   Scope patch ini SEMPIT: hanya di dalam blok `IF ( @jenis like '%voadip%' )`,
   spesifik ke bagian #lnoreg registration dan DATA MASUK. Tidak menyentuh
   DATA KELUAR (retur_voadip / adjout_voadip_siklus) maupun section
   `doc`/`pakan` sama sekali.
   ============================================================================ */


/* ----------------------------------------------------------------------------
   PATCH 1 -- Registrasi #lnoreg untuk tbl_name baru.

   Cari blok berikut (setelah patch adjout_voadip_siklus PATCH 1 terpasang,
   ada 2 blok IF berurutan: retur_voadip lalu adjout_voadip_siklus):

       IF ( @tbl_name = 'adjout_voadip_siklus' )
       BEGIN
           insert into #lnoreg (urut, noreg)
           values
           (1, @noreg1),
           (2, @noreg2)
       END

   Tambahkan blok BARU persis SETELAH `END` penutup blok adjout_voadip_siklus
   di atas (masih sebelum `DECLARE noreg CURSOR LOCAL FOR`):
   ---------------------------------------------------------------------------- */

-- IF ( @tbl_name = 'adjin_voadip_siklus' )
-- BEGIN
--     insert into #lnoreg (urut, noreg)
--     values
--     (1, @noreg1),
--     (2, @noreg2)
-- END

-- Catatan: controller PHP SELALU kirim @noreg1 eksplisit (kolom `noreg` pada
-- baris adjin_voadip_siklus yang bersangkutan), sama seperti pola
-- AdjustmentOutVoadipSiklus::execHitStokSiklus() / AdjinDoc.


/* ----------------------------------------------------------------------------
   PATCH 2 -- Tambah sumber DATA MASUK baru, di dalam insert-select blok
   `/* DATA MASUK */` section voadip.

   Cari blok ini (union all terima_voadip, lihat baris ~2396-2480 di
   referensi backup):

       insert into det_stok_siklus (id_header, tgl_trans, noreg, kode_barang, jumlah, hrg_jual, hrg_beli, oa, kode_trans, jenis_barang, jenis_trans, jml_stok)
       select * from
       (
           select
               @id_header as id_header,
               tv.tgl_terima as tgl_trans,
               ...
               'voadip' as jenis_barang,
               'ORDER' as jenis_trans,
               sum(dst.jumlah) as jml_stok
           from det_terima_voadip dtv
           ...
           group by
               tv.tgl_terima,
               kv.tujuan,
               dtv.item,
               ds.hrg_jual,
               ds.hrg_beli,
               kv.ongkos_angkut,
               kv.no_order
       ) dm
       /* END - DATA MASUK */

   Tambahkan `union all` BARU persis SEBELUM `) dm` (sejajar dengan sub-select
   terima_voadip di atas, di dalam tanda kurung yang sama). Pola diadaptasi
   langsung dari `adjin_doc` (section DOC) -- straight insert per baris,
   TANPA agregasi/FIFO, karena satu baris adjustment = satu lot baru:
   ---------------------------------------------------------------------------- */

-- union all
--
-- select
--     @id_header as id_header,
--     ais.tanggal as tgl_trans,
--     ais.noreg as noreg,
--     ais.kode_barang,
--     ais.jumlah,
--     ais.harga as hrg_jual,
--     ais.harga as hrg_beli,
--     0 as oa,
--     ais.kode as kode_trans,
--     'voadip' as jenis_barang,
--     'ADJIN' as jenis_trans,
--     ais.jumlah as jml_stok
-- from adjin_voadip_siklus ais
-- where
--     ais.tanggal = @tgl_transaksi and
--     ais.noreg = @noreg

-- Catatan kolom: `harga` di adjin_voadip_siklus dipakai utk hrg_jual DAN
-- hrg_beli sekaligus, sama seperti adjin_doc (kolom `harga` tunggal, beda
-- dgn AdjinVoadip_model warehouse-level yg punya hrg_beli/hrg_jual terpisah).
-- Efeknya netral ke laporan HPP siklus karena baris ADJIN ini murni koreksi
-- opname fisik, bukan transaksi jual-beli.
