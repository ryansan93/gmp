/* ============================================================================
   BUG DITEMUKAN (BELUM DIPERBAIKI, per keputusan 2026-09-04): cabang
   `retur_voadip` di `hitung_stok_siklus` (kode LAMA, sudah ada jauh sebelum
   fitur adjout_voadip_siklus) bisa salah sasaran barang saat konsumsi stok
   siklus, kalau ada retur untuk barang yang TIDAK PUNYA baris di
   det_stok_siklus (belum pernah tercatat masuk stok siklus utk noreg itu).

   ----------------------------------------------------------------------------
   KRONOLOGI PENEMUAN
   ----------------------------------------------------------------------------
   Ditemukan saat verifikasi fitur "Adjustment Out OVK (Siklus)" (baru,
   docs/create_adjout_voadip_siklus.sql + docs/alter_hitung_stok_siklus_adjout_voadip_siklus.sql).
   Kasus nyata: noreg 25101650601, barang OB2509001 (VALOGRIN @ 50 GR), lot
   det_stok_siklus id=271674 (jumlah asli 10.00, tgl_trans 2026-07-09).

   Pada 2026-08-18 ada 2 retur yg sama-sama diproses hari itu:
     - RTV2608232, barang OB2509001, jumlah 10.00 (SESUAI, lot ini memang ada)
     - RTV2608233, barang OB2109035, jumlah 10.00 (barang LAIN)

   Hasil: ketiga-tiganya (2 baris RTV2608232 pecah 8.00+2.00, PLUS RTV2608233
   yg harusnya punya lot SENDIRI utk OB2109035) semua tercatat di
   det_stok_trans_siklus dengan id_header=271674 -- lot OB2109035 dari
   RTV2608233 SALAH KETEMPEL ke lot OB2509001!

   ----------------------------------------------------------------------------
   ROOT CAUSE
   ----------------------------------------------------------------------------
   Di dalam cabang `IF ( @dk_tbl_name = 'retur_voadip' )`, ada cursor lokal
   `d_retur` yg iterasi per baris `det_stok` (level gudang) yg cocok utk SJ+item
   retur tsb. Untuk TIAP baris itu:

       select top 1
           @ds_id = cast(id as int),
           @ds_jml_stok = cast(jml_stok as decimal(13, 2))
       from det_stok_siklus
       where
           noreg = @noreg and
           tgl_trans <= @tgl_transaksi and
           kode_trans = @dk_no_sj_asal and
           kode_barang = @dk_kode_barang and
           hrg_beli = @rv_hrg_beli
       order by
           tgl_trans asc, kode_trans asc

       insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
       values
       (@ds_id, @dk_tgl_trans, @dk_kode_trans, @rv_jumlah, @dk_kode_barang, @dk_tbl_name)

   Kalau SELECT di atas TIDAK dapat baris (barang itu memang belum pernah ada
   di det_stok_siklus utk noreg ini -- kasus RTV2608233/OB2109035), T-SQL
   TIDAK me-reset @ds_id/@ds_jml_stok ke NULL. Variabelnya tetap nyangkut nilai
   dari iterasi CURSOR SEBELUMNYA (RTV2608232/OB2509001, @ds_id=271674) --
   lalu INSERT berikutnya pakai @ds_id basi itu, salah nempel ke lot barang
   yang SALAH.

   ----------------------------------------------------------------------------
   DAMPAK
   ----------------------------------------------------------------------------
   Bug ini generik di cabang retur_voadip -- berpotensi kena SEMUA retur OVK
   dimanapun barangnya belum tercatat di det_stok_siklus utk noreg tsb, bukan
   spesifik ke fitur adjustment. Skala dampak ke data historis BELUM diaudit.

   ----------------------------------------------------------------------------
   USULAN FIX (BELUM DITERAPKAN -- keputusan 2026-09-04: catat dulu, jangan
   ubah SP produksi lagi di hari yang sama dgn 3 perubahan lain)
   ----------------------------------------------------------------------------
   Reset @ds_id/@ds_jml_stok jadi NULL di awal tiap iterasi WHILE d_retur,
   SEBELUM select top 1 -- kalau select tsb tidak dapat baris, @ds_id tetap
   NULL, dan insert+update di bawahnya di-skip (bukan salah sasaran):

       WHILE @@FETCH_STATUS = 0
       BEGIN
           SET @ds_id = NULL
           SET @ds_jml_stok = NULL

           select top 1 @ds_id = ..., @ds_jml_stok = ... from det_stok_siklus where ...

           IF ( @ds_id is not null )
           BEGIN
               insert into det_stok_trans_siklus (...) values (...)
               ...
               update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
           END

           FETCH NEXT FROM d_retur INTO @rv_jumlah, @rv_hrg_beli
       END

   Lokasi tepatnya: definisi live `hitung_stok_siklus` saat ini (setelah 3
   patch adjout_voadip_siklus diterapkan 2026-09-04), di dalam
   `IF ( @jenis like '%voadip%' )` -> `IF ( @dk_tbl_name = 'retur_voadip' )`.
   Ambil body LIVE terkini via OBJECT_DEFINITION() / sqlsrv sebelum patch,
   sama seperti cara kerja patch-patch sebelumnya -- JANGAN pakai nomor baris
   dari file ini sebagai acuan pasti, cuma kutipan text sbg anchor pencarian.
   ============================================================================ */
