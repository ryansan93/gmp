/* ============================================================================
   PATCH: hitung_stok_siklus -- tambah sumber baru `adjout_voadip_siklus`
   (koreksi OUT manual stok OVK per-siklus/kandang, controller
   transaksi/AdjustmentOutVoadipSiklus.php).

   Saat ini section `@jenis like '%voadip%'` cuma kenal 2 sumber:
     - DATA MASUK : terima_voadip (kirim opkg ke peternak)
     - DATA KELUAR: retur_voadip (retur OVK dari peternak)
   Belum ada jalur "koreksi manual keluar" -- ini yang ditambahkan.

   SUDAH DIJALANKAN ke gmp_erp_live (2026-09-04), via ALTER PROCEDURE
   langsung (bukan re-paste manual -- body live diambil dulu dengan PHP
   sqlsrv (`sqlcmd`/`OBJECT_DEFINITION()` kepotong di ~8000 char untuk objek
   sebesar ini, jadi dipakai koneksi sqlsrv langsung), potongan di bawah
   disisipkan di lokasi yang tepat berdasar cat -A byte-level pada file live
   (bukan diasumsikan dari baris referensi), divalidasi `SET PARSEONLY ON`
   dulu, lalu smoke-test fungsional di BEGIN TRAN...ROLLBACK, baru
   dipromote permanen. Backup body SEBELUM patch ada di
   `docs/backup_hitung_stok_siklus_before_adjout_voadip_siklus_20260904.sql`
   (untuk rollback kalau perlu).

   Diterapkan dalam 3 ronde di hari yang sama (2026-09-04):
     - Ronde 1: PATCH 1-3 di bawah (fitur dasar). Smoke-test: noreg
       25102550601 / OB2109008, 2 lot beda tanggal -- konsumsi FIFO turun
       ke lot TERTUA seperti didesain, lot lain tidak tersentuh.
     - Ronde 2: PATCH 3 direvisi (lihat "REVISI PATCH 3" di bawah) --
       awalnya kalau stok abis, adjustment didiamkan (tidak nge-apply).
       Diubah atas permintaan eksplisit: harus tetap ke-apply & boleh minus,
       supaya kelihatan di laporan sbg selisih nyata, bukan didiamkan.
     - Ronde 3: PATCH 4 (baru) -- ternyata "boleh minus" itu masih ketahan
       oleh formula cap LAMA (pra-existing, bukan bagian dari fitur ini)
       yang dipakai section PAKAN & VOADIP: kalau total konsumsi suatu lot
       melebihi jumlah aslinya, jml_stok di-reset BALIK ke jumlah penuh
       (bukan floor ke 0/minus). Section DOC tidak punya cap ini sama
       sekali (memang sudah bisa minus dari awal). PATCH 4 menyamakan
       PAKAN & VOADIP ke pola DOC (hapus cap, subtraksi polos).

   CATATAN TERPISAH (BUG LAMA, DITEMUKAN tapi BELUM diperbaiki, keputusan
   2026-09-04: dicatat dulu, tidak diubah hari yg sama): cabang retur_voadip
   (kode lama, tidak disentuh oleh patch manapun di sini) punya bug variabel
   @ds_id yang tidak di-reset antar iterasi cursor, bisa bikin retur salah
   sasaran ke lot barang lain kalau barangnya sendiri belum pernah tercatat
   di det_stok_siklus. Detail & usulan fix di
   `docs/bug_retur_voadip_ds_id_tidak_direset.sql`.

   Definisi lengkap SP referensi (per commit sebelumnya, mungkin sedikit
   beda dari live karena fix lain yang sudah jalan duluan) ada di
   `docs/query/hitung_stok_siklus_new.sql` (baris 1-2552 adalah body yang
   AKTIF; sisanya, mulai `/* 2025 */` di baris 2554, adalah versi lama yang
   di-comment, bukan bagian dari objek SP).

   Scope patch ini SEMPIT: hanya di dalam blok `IF ( @jenis like '%voadip%' )`.
   Tidak menyentuh section `doc`/`pakan` sama sekali -- regresi ke sana
   secara struktural tidak mungkin selama ke-3 potongan ini disisipkan di
   lokasi yang benar (di dalam blok voadip) dan tidak mengubah baris lain.
   ============================================================================ */


/* ----------------------------------------------------------------------------
   PATCH 1 -- Registrasi #lnoreg untuk tbl_name baru.

   Cari blok ini (sekitar baris 2179-2203 di referensi):

       IF ( @tbl_name = 'retur_voadip' )
       BEGIN
           IF ( EXISTS(
               select rv.* from retur_voadip rv where rv.id = @tbl_id
           ) and @noreg1 is null and @noreg2 is null )
           BEGIN
               ...
           END

           insert into #lnoreg (urut, noreg)
           values
           (1, @noreg1),
           (2, @noreg2)
       END

   Tambahkan blok BARU persis SETELAH `END` penutup blok retur_voadip di
   atas (masih sebelum `DECLARE noreg CURSOR LOCAL FOR`):
   ---------------------------------------------------------------------------- */

-- IF ( @tbl_name = 'adjout_voadip_siklus' )
-- BEGIN
--     insert into #lnoreg (urut, noreg)
--     values
--     (1, @noreg1),
--     (2, @noreg2)
-- END

-- Catatan: controller PHP SELALU kirim @noreg1 eksplisit (hasil kolom
-- `noreg` pada baris adjout_voadip_siklus yang bersangkutan, sama seperti
-- pola AdjustmentInDoc::execHitStokSiklus()), jadi tidak perlu query
-- resolusi noreg dari tabel seperti blok terima_voadip/retur_voadip.


/* ----------------------------------------------------------------------------
   PATCH 2 -- Tambah sumber DATA KELUAR baru di cursor `data_keluar`.

   Cari blok ini (sekitar baris 2393-2422 di referensi):

       DECLARE data_keluar CURSOR LOCAL FOR
           select
               dk.tgl_trans,
               dk.kode_trans,
               dk.jumlah,
               dk.kode_barang,
               dk.no_sj_asal,
               dk.tbl_name
           from (
               select
                   rv.tgl_retur as tgl_trans,
                   rv.no_retur as kode_trans,
                   drv.jumlah,
                   drv.item as kode_barang,
                   rv.no_order as no_sj_asal,
                   'retur_voadip' as tbl_name,
                   2 as urut
               from det_retur_voadip drv
               left join
                   retur_voadip rv
                   on
                       drv.id_header = rv.id
               where
                   rv.tgl_retur = @tgl_transaksi and
                   rv.id_asal = @noreg
           ) dk
           order by
               dk.urut asc

   Tambahkan `union all` BARU persis sebelum `) dk` (jadi sejajar dengan
   sub-select retur_voadip di atas, di dalam tanda kurung yang sama):
   ---------------------------------------------------------------------------- */

-- union all
--
-- select
--     aos.tanggal as tgl_trans,
--     aos.kode as kode_trans,
--     aos.jumlah,
--     aos.kode_barang,
--     null as no_sj_asal,
--     'adjout_voadip_siklus' as tbl_name,
--     3 as urut
-- from adjout_voadip_siklus aos
-- where
--     aos.tanggal = @tgl_transaksi and
--     aos.noreg = @noreg


/* ----------------------------------------------------------------------------
   PATCH 3 -- Konsumsi FIFO utk tbl_name baru, di dalam WHILE loop
   `data_keluar` (sekitar baris 2428-2534 di referensi).

   Cari blok:

       IF ( @dk_tbl_name = 'retur_voadip' )
       BEGIN
           ...
           CLOSE d_retur
           DEALLOCATE d_retur
       END

   Tambahkan blok BARU persis SETELAH `END` penutup blok retur_voadip di
   atas (masih di dalam WHILE loop `data_keluar`, sebelum
   `FETCH NEXT FROM data_keluar INTO ...` berikutnya). Beda dengan
   retur_voadip (exact-match ke kode_trans+hrg_beli karena tiap retur
   nunjuk balik ke SJ asal spesifik), adjustment manual ini FIFO polos ke
   lot manapun yang masih ada sisa utk noreg+kode_barang tsb -- polanya
   diadaptasi dari kode FIFO generik yang sudah ada tapi ter-comment di
   file referensi (baris 2482-2530), plus safety-cap spy `jml_stok` tidak
   pernah negatif (pola yang sama dipakai di update `dss.jml_stok` bagian
   atas section ini, baris ~2272-2279):
   ---------------------------------------------------------------------------- */

-- IF ( @dk_tbl_name = 'adjout_voadip_siklus' )
-- BEGIN
--     SET @_dk_jumlah = @dk_jumlah
--
--     WHILE ( @_dk_jumlah > 0 )
--     BEGIN
--         IF ( EXISTS (
--             select * from det_stok_siklus dss
--             where
--                 dss.noreg = @noreg and
--                 dss.jenis_barang = @jenis and
--                 dss.kode_barang = @dk_kode_barang and
--                 dss.jml_stok > 0
--         ) )
--         BEGIN
--             select top 1
--                 @ds_id = cast(id as int),
--                 @ds_jml_stok = cast(jml_stok as decimal(13, 2))
--             from det_stok_siklus dss
--             where
--                 dss.noreg = @noreg and
--                 dss.jenis_barang = @jenis and
--                 dss.kode_barang = @dk_kode_barang and
--                 dss.jml_stok > 0
--             order by
--                 dss.tgl_trans asc,
--                 dss.kode_trans asc
--
--             IF ( @_dk_jumlah <= @ds_jml_stok )
--             BEGIN
--                 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
--                 values
--                 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @_dk_jumlah, @dk_kode_barang, @dk_tbl_name)
--
--                 SET @ds_jml_stok = @ds_jml_stok - @_dk_jumlah
--                 SET @_dk_jumlah = 0
--
--                 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
--             END
--             ELSE
--             BEGIN
--                 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
--                 values
--                 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @dk_kode_barang, @dk_tbl_name)
--
--                 SET @_dk_jumlah = @_dk_jumlah - @ds_jml_stok
--                 SET @ds_jml_stok = 0
--
--                 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
--             END
--         END
--         ELSE
--         BEGIN
--             -- REVISI (ronde 2, lihat header): semua lot sudah 0/minus --
--             -- TETAP catat kekurangannya (jangan didiamkan), ditempel ke
--             -- lot PALING BARU (order by tgl_trans DESC) biar jml_stok
--             -- boleh minus dan kelihatan sbg selisih nyata di laporan.
--             IF ( EXISTS (
--                 select * from det_stok_siklus dss
--                 where
--                     dss.noreg = @noreg and
--                     dss.jenis_barang = @jenis and
--                     dss.kode_barang = @dk_kode_barang
--             ) )
--             BEGIN
--                 select top 1
--                     @ds_id = cast(id as int),
--                     @ds_jml_stok = cast(jml_stok as decimal(13, 2))
--                 from det_stok_siklus dss
--                 where
--                     dss.noreg = @noreg and
--                     dss.jenis_barang = @jenis and
--                     dss.kode_barang = @dk_kode_barang
--                 order by
--                     dss.tgl_trans desc,
--                     dss.kode_trans desc
--
--                 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
--                 values
--                 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @_dk_jumlah, @dk_kode_barang, @dk_tbl_name)
--
--                 SET @ds_jml_stok = @ds_jml_stok - @_dk_jumlah
--
--                 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
--             END
--
--             SET @_dk_jumlah = 0
--         END
--     END
-- END


/* ----------------------------------------------------------------------------
   PATCH 4 (ronde 3) -- Hapus cap lama yang bikin PATCH 3 revisi di atas
   tetap ketahan tidak bisa minus.

   Section PAKAN dan section VOADIP (masing-masing punya copy sendiri, cari
   dua-duanya, JANGAN sentuh copy yang sama persis di dalam blok comment
   `/* 2025 */` di bagian bawah file -- itu bukan bagian dari objek SP)
   sama-sama punya blok "prior-date recompute" begini, di awal loop per-hari
   (sebelum DATA MASUK/DATA KELUAR):

       update dss
       set
           dss.jml_stok = case
               when isnull(dtrans.jumlah, 0) > dss.jumlah then
                   dss.jumlah
               else
                   (dss.jumlah - isnull(dtrans.jumlah, 0))
           end
       from det_stok_siklus dss
       left join ( ...sum det_stok_trans_siklus dated < @tgl_transaksi... ) dtrans
           on dss.id = dtrans.id_header
       where
           dss.tgl_trans < @tgl_transaksi and
           dss.noreg = @noreg and
           dss.jenis_barang = @jenis

   Kalau total konsumsi (dtrans.jumlah) MELEBIHI jumlah asli lot, cabang
   `when` di atas mereset jml_stok BALIK ke `dss.jumlah` (jumlah PENUH) --
   bukan floor ke 0 apalagi minus. Section DOC (di atasnya, cari
   `IF ( @jenis like '%doc%' )`) TIDAK PERNAH punya cap ini -- polanya dari
   awal cuma subtraksi polos. PATCH 4 menyamakan PAKAN & VOADIP ke pola DOC:

   GANTI kedua copy (pakan & voadip) jadi:

       update dss
       set
           dss.jml_stok = (dss.jumlah - isnull(dtrans.jumlah, 0))
       from det_stok_siklus dss
       left join ( ...sama persis, tidak berubah... ) dtrans
           on dss.id = dtrans.id_header
       where
           dss.tgl_trans < @tgl_transaksi and
           dss.noreg = @noreg and
           dss.jenis_barang = @jenis

   (cukup ganti bagian `set dss.jml_stok = case ... end` jadi baris tunggal
   di atas, sisanya -- FROM/LEFT JOIN/WHERE -- tidak berubah sama sekali.)

   Efek: sekarang PAKAN & VOADIP konsisten sama DOC -- kalau suatu lot
   dikonsumsi lebih dari jumlah aslinya (baik krn adjustment yg sengaja
   dipaksa minus, ATAU krn ketahuan ada data lain yg sebelumnya ke-mask
   sama cap ini), jml_stok akan tampil apa adanya (boleh negatif), bukan
   diam-diam dikembalikan ke jumlah penuh.
   ---------------------------------------------------------------------------- */
