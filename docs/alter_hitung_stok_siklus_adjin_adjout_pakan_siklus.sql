/* ============================================================================
   PATCH: hitung_stok_siklus -- tambah sumber baru `adjin_pakan_siklus` dan
   `adjout_pakan_siklus` (koreksi IN/OUT manual stok PAKAN per-siklus/kandang,
   controller transaksi/AdjustmentInPakanSiklus.php dan
   transaksi/AdjustmentOutPakanSiklus.php).

   Pasangan feature dari "Adjustment In/Out OVK (Siklus)" yang dibangun hari
   yang sama (lihat docs/alter_hitung_stok_siklus_adjin_voadip_siklus.sql
   dan docs/alter_hitung_stok_siklus_adjout_voadip_siklus.sql) -- pola
   IDENTIK, hanya beda section (`@jenis like '%pakan%'`, bukan '%voadip%')
   dan tabel sumber.

   SUDAH DIJALANKAN ke gmp_erp_live (2026-09-08), lewat PHP `sqlsrv`
   langsung dari Claude Code (bukan CLI `php` di Laragon 8.2 yang tidak
   punya ekstensi sqlsrv/pdo_sqlsrv -- dipakai `/c/xampp_php7/php/php.exe`,
   PHP 7.4). Metodologi identik dengan patch voadip sebelumnya:
     1. Fetch body live via `sys.sql_modules.definition` (bukan
        `OBJECT_DEFINITION()` yang kepotong ~8000 char untuk objek sebesar
        ini).
     2. Sisip patch berbasis OFFSET (bukan line number) -- cari anchor unik
        (mis. `IF ( @tbl_name = 'retur_pakan' )`, `'MUTASI' as jenis_trans`,
        `'retur_pakan' as tbl_name`, dan FETCH NEXT ke-2 dalam WHILE loop)
        via `strpos()`, insert dengan `substr_replace(..., $pos, 0)` --
        diterapkan dari offset TERBESAR ke TERKECIL supaya insersi
        berikutnya tidak menggeser offset yang sudah dihitung.
     3. Body SP ini punya beberapa blok dead-code campur (baik `--` per
        baris maupun `/* ... */` blok besar mulai marker `/* 2025 */` di
        akhir) -- beberapa anchor sederhana (mis. `'MUTASI' as jenis_trans`,
        `'pakan' as jenis_barang`) TERNYATA muncul lebih dari sekali karena
        ada salinan dead-code serupa (di section DOC yang di-comment, dan
        di section VOADIP yang di-comment). Fix: search anchor dimulai
        SETELAH posisi `IF ( @tbl_name = 'retur_pakan' )` (anchor yang
        sudah terbukti unik) sebagai lower-bound, bukan dari awal string.
     4. `SET PARSEONLY ON` untuk cek syntax -- TIDAK BISA digabung dalam
        satu batch dengan ALTER PROCEDURE langsung (error 1059: "Cannot
        set or reset the 'parseonly' option within a procedure or
        function") kalau dikirim sebagai satu string panjang; harus 3
        pemanggilan `sqlsrv_query()` terpisah (SET PARSEONLY ON / ALTER /
        SET PARSEONLY OFF) di connection yang sama, supaya ALTER PROCEDURE
        jadi satu-satunya statement di batch-nya sendiri.
     5. Smoke-test fungsional PENUH dalam satu batch
        `BEGIN TRAN; EXEC(N'...'); INSERT test row; EXEC hitung_stok_siklus
        ...; SELECT verifikasi; ROLLBACK;` (ALTER dibungkus dynamic SQL
        `EXEC()` supaya bisa sebatch dengan BEGIN TRAN). Hasil: noreg nyata
        26020070401 / kode_barang PK2106001 (stok ORDER 2500 di
        det_stok_siklus) --
          - insert adjin_pakan_siklus jumlah=10 -> EXEC 'adjin_pakan_siklus'
            -> baris baru muncul di det_stok_siklus (jenis_trans='ADJIN',
            jml_stok=10), lot ORDER lama (2500) tidak terganggu.
          - insert adjout_pakan_siklus jumlah=5 -> EXEC
            'adjout_pakan_siklus' -> FIFO turun ke lot TERTUA (ORDER,
            berdasar tgl_trans asc, kode_trans asc), jml_stok 2500 -> 2495;
            lot ADJIN (10) tidak tersentuh. det_stok_trans_siklus tercatat
            benar (tbl_name='adjout_pakan_siklus', jumlah=5, id_header
            menunjuk ke lot ORDER).
          - regresi: re-run EXEC untuk tbl_name='lhk' pada noreg+tanggal
            yang sama tidak error.
        Semua di dalam transaksi yang di-ROLLBACK, jadi tidak ada
        perubahan permanen dari smoke-test ini.
     6. Baru setelah smoke-test lolos, ALTER PROCEDURE yang SAMA PERSIS
        di-apply ulang di luar transaksi (permanen), dengan pre-check
        byte-diff body live saat itu vs body yang dipatch (tolak apply
        kalau beda -- jaga2 race condition sesi lain mengubah SP di
        antara fetch awal dan apply). Verifikasi pasca-apply: body live
        baru mengandung `adjin_pakan_siklus` dan `adjout_pakan_siklus` --
        confirmed. Backup body SEBELUM patch ini ada di
        `docs/backup_hitung_stok_siklus_before_adjin_adjout_pakan_siklus_20260908.sql`.

   Detail 4 titik sisip (semua di dalam blok `IF ( @jenis like '%pakan%' )`,
   tidak menyentuh section `doc`/`voadip` sama sekali):

   PATCH 1 -- Registrasi #lnoreg, disisipkan tepat sebelum
   `DECLARE noreg CURSOR LOCAL FOR` (setelah blok registrasi `retur_pakan`):
       IF ( @tbl_name = 'adjin_pakan_siklus' )
       BEGIN
           insert into #lnoreg (urut, noreg) values (1, @noreg1), (2, @noreg2)
       END
       IF ( @tbl_name = 'adjout_pakan_siklus' )
       BEGIN
           insert into #lnoreg (urut, noreg) values (1, @noreg1), (2, @noreg2)
       END
   (Controller PHP selalu kirim @noreg1 eksplisit, sama seperti pola
   AdjustmentIn/OutVoadipSiklus -- tidak perlu query resolusi noreg dari
   tabel manapun.)

   PATCH 2 -- Sumber DATA MASUK baru (union all, straight insert tanpa FIFO,
   pola sama seperti adjin_doc / adjin_voadip_siklus -- satu baris
   adjustment = satu lot baru), disisipkan sebelum `) dm` di blok DATA
   MASUK section pakan:
       union all
       select
           @id_header as id_header, aps.tanggal as tgl_trans, aps.noreg as noreg,
           aps.kode_barang, aps.jumlah, aps.harga as hrg_jual, aps.harga as hrg_beli,
           0 as oa, aps.kode as kode_trans, 'pakan' as jenis_barang,
           'ADJIN' as jenis_trans, aps.jumlah as jml_stok
       from adjin_pakan_siklus aps
       where aps.tanggal = @tgl_transaksi and aps.noreg = @noreg
   (Kolom `harga` dipakai untuk hrg_jual DAN hrg_beli sekaligus, sama
   seperti adjin_doc/adjin_voadip_siklus -- netral ke HPP siklus karena
   murni koreksi opname fisik.)

   PATCH 3 -- Sumber DATA KELUAR baru di cursor `data_keluar`, disisipkan
   sebelum `) dk` (union all baru, urut=4, setelah retur_pakan yang urut=3):
       union all
       select
           aos.tanggal as tgl_trans, aos.kode as kode_trans, aos.jumlah,
           aos.kode_barang, null as no_sj_asal, 'adjout_pakan_siklus' as tbl_name,
           4 as urut, null as noreg_tujuan
       from adjout_pakan_siklus aos
       where aos.tanggal = @tgl_transaksi and aos.noreg = @noreg

   PATCH 4 -- Konsumsi FIFO untuk tbl_name baru, disisipkan sebagai blok
   IF baru SETELAH `END` yang menutup `IF ( @dk_tbl_name = 'lhk' )` (masih
   di dalam WHILE loop `data_keluar`), SEBELUM `FETCH NEXT FROM data_keluar`
   berikutnya:
       IF ( @dk_tbl_name = 'adjout_pakan_siklus' )
       BEGIN
           SET @_dk_jumlah = @dk_jumlah
           WHILE ( @_dk_jumlah > 0 )
           BEGIN
               -- cari lot tersisa (jml_stok > 0) untuk noreg+kode_barang ini,
               -- FIFO polos (order by tgl_trans asc, kode_trans asc)
               -- kalau semua lot sudah 0/minus, TETAP catat kekurangan ke
               -- lot PALING BARU (order by tgl_trans desc, kode_trans desc)
               -- supaya boleh minus dan kelihatan sbg selisih nyata --
               -- pola SAMA PERSIS dengan adjout_voadip_siklus (ronde 2 & 4
               -- patch voadip, cap lama sudah dihapus duluan 2026-09-04
               -- untuk section PAKAN & VOADIP sekaligus, jadi tidak perlu
               -- diulang di sini).
           END
       END
   PENTING: sebelum patch ini, section PAKAN di `data_keluar` cursor loop
   HANYA punya penanganan aktif untuk `@dk_tbl_name = 'lhk'` -- tidak ada
   ELSE fallback generik untuk tbl_name lain (`terima_pakan` untuk mutasi
   opkp-keluar, `retur_pakan`) karena kode ELSE lama sudah di-comment
   (dead code, lihat catatan di [[optimasi-sp-stok-pakan]] soal mutasi
   pakan opkp yang "buta thd LHK"). Patch ini TIDAK mengubah/mengaktifkan
   fallback itu -- hanya menambah cabang baru yang independent untuk
   `adjout_pakan_siklus`, jadi scope-nya sempit dan tidak menyentuh bug
   lama tersebut.

   Cap lama "prior-date recompute" (yang dulu mereset jml_stok balik ke
   jumlah penuh kalau konsumsi melebihi jumlah asli) SUDAH dihapus untuk
   section PAKAN sejak patch voadip ronde 3 (2026-09-04, lihat PATCH 4 di
   docs/alter_hitung_stok_siklus_adjout_voadip_siklus.sql) -- dikonfirmasi
   ulang lewat inspeksi body live sebelum patch ini (baris
   `dss.jml_stok = (dss.jumlah - isnull(dtrans.jumlah, 0))`, bukan `case`),
   jadi section PAKAN & VOADIP sudah konsisten boleh minus sebelum patch
   hari ini pun dimulai.
   ============================================================================ */
