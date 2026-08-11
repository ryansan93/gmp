/* ============================================================================
   FIX: hitung_stok_siklus -- mutasi pakan (opkp) hilang dari stok siklus
   karena LHK menghabiskan batch yang direferensikan sebuah mutasi sebelum
   mutasinya sendiri sempat diproses.
   ============================================================================

   Konteks / kronologi bug:
     Peternak SUPOYO (noreg 25101370601) kirim 450kg pakan B-BR 2 GMP ke
     peternak lain lewat OP/KDR/26/08061, dengan det_kirim_pakan.no_sj_asal
     menunjuk ke SJ/KDR/26/08021. Batch itu SUDAH habis dimakan LHK sebelum
     tanggal kirim (09 Ags), padahal peternak itu masih punya sisa stok
     450kg dari batch LAIN (OP/KDR/26/08023, hasil mutasi masuk sebelumnya).

     Di hitung_stok_siklus, cabang non-LHK (mutasi/pindah/retur) MEMANG
     harus exact-match ke kode_trans = no_sj_asal (bukan FIFO sembarang
     batch) -- karena tiap batch beda hrg_beli, exact-match menjaga costing
     benar saat mutasinya dieksekusi. Tapi cabang LHK (siklus baru,
     @tgl_docin >= '2026-05-01') sebelumnya FIFO POLOS ke batch manapun yang
     masih ada sisa, TANPA tahu ada mutasi lain yang sudah "mengklaim" batch
     tsb -- jadi LHK bisa menghabiskan duluan batch yang sebenarnya sedang
     "dipesan" mutasi, dan begitu mutasinya benar2 dieksekusi (exact-match),
     stoknya sudah 0 -> silently gagal, tanpa error, tanpa baris tercatat.

     Cabang LHK utk siklus LAMA (@tgl_docin < '2026-05-01') SUDAH punya
     pengaman serupa (mekanisme #pp_pakan) -- fix ini pada dasarnya
     mengadaptasi ide yang sama (versi lebih sederhana, tanpa #pp_pakan) ke
     cabang baru.

   Root cause tambahan (upstream): cekStok() di PengirimanPenerimaanPakan.php
   validasi "jml_terima vs jml_pakai" BUTA terhadap pemakaian LHK, jadi user
   bisa lolos memilih SJ yang stoknya sebenarnya sudah habis tanpa warning.
   Sudah diperbaiki di kode PHP terpisah (commit yang sama dengan file ini).

   Detail investigasi lengkap: memori sesi "optimasi-sp-stok-pakan.md".
   Sudah diuji di sandbox `hitung_stok_siklus_new` (BEGIN TRAN...ROLLBACK,
   3 skenario: kasus SUPOYO + 2 regresi noreg lain, total per batch identik
   ke hasil lama utk kasus yang sudah benar) sebelum di-promote ke produksi
   via ALTER PROCEDURE pada `hitung_stok_siklus` langsung (2026-08-11).
   ============================================================================ */


/* ----------------------------------------------------------------------------
   STEP 1 -- Index pendukung (aditif, aman, idempotent)
   Tanpa ini query reservasi di STEP 2 full-scan det_kirim_pakan (74rb baris)
   dan det_stok_trans_siklus (236rb baris) tiap 1 baris LHK diproses.
   ---------------------------------------------------------------------------- */

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name='ix_det_kirim_pakan_no_sj_asal' AND object_id=OBJECT_ID('det_kirim_pakan'))
  CREATE INDEX ix_det_kirim_pakan_no_sj_asal ON det_kirim_pakan (no_sj_asal, item) INCLUDE (id_header, jumlah);

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name='ix_det_stok_trans_siklus_kodetrans_barang' AND object_id=OBJECT_ID('det_stok_trans_siklus'))
  CREATE INDEX ix_det_stok_trans_siklus_kodetrans_barang ON det_stok_trans_siklus (kode_trans, kode_barang) INCLUDE (id_header, jumlah, tbl_name);


/* ----------------------------------------------------------------------------
   STEP 2 -- Perubahan logika di hitung_stok_siklus (SUDAH DIJALANKAN via
   ALTER PROCEDURE langsung ke produksi, 2026-08-11). Disalin di sini sebagai
   dokumentasi diff -- BUKAN untuk dijalankan ulang (definisi lengkap SP ada
   4200+ baris, lihat OBJECT_DEFINITION(OBJECT_ID('hitung_stok_siklus')) di DB
   kalau perlu full body / rollback).

   Lokasi: cabang `IF ( @dk_tbl_name = 'lhk' )` -> `IF ( @tgl_docin >= '2026-05-01' )`
   (siklus baru), di dalam loop DATA KELUAR pakan.
   ---------------------------------------------------------------------------- */

-- Variabel baru (deklarasi di bagian atas SP):
--   DECLARE @ds_reserved decimal(13, 2)
--   DECLARE @ds_avail decimal(13, 2)

-- SEBELUM (FIFO polos, buta thd mutasi pending):
--
--   IF ( EXISTS ( select * from det_stok_siklus dss where dss.noreg=@noreg and
--        dss.jenis_barang=@jenis and dss.kode_barang=@dk_kode_barang and
--        dss.jml_stok > 0 ) )
--   BEGIN
--       select top 1 @ds_id=dss.id, @ds_jml_stok=dss.jml_stok
--       from det_stok_siklus dss
--       where dss.noreg=@noreg and dss.jenis_barang=@jenis and
--             dss.kode_barang=@dk_kode_barang and dss.jml_stok > 0
--       order by dss.tgl_trans asc, dss.kode_trans asc
--
--       IF ( @_dk_jumlah <= @ds_jml_stok ) BEGIN ... END ELSE BEGIN ... END
--   END

-- SESUDAH (kurangi jatah LHK dengan klaim mutasi yang belum tercatat):
--
--   SET @ds_reserved = 0
--   SET @ds_avail = 0
--
--   IF ( EXISTS (
--       select * from det_stok_siklus dss
--       left join (
--           select dkp.no_sj_asal, dkp.item, sum(dkp.jumlah) as jumlah
--           from det_kirim_pakan dkp
--           left join kirim_pakan kp on dkp.id_header = kp.id
--           where dkp.no_sj_asal is not null and
--                 not exists (
--                     select * from det_stok_trans_siklus dsts
--                     where dsts.kode_trans = kp.no_order and dsts.kode_barang = dkp.item
--                 )
--           group by dkp.no_sj_asal, dkp.item
--       ) mutasi
--       on REPLACE(mutasi.no_sj_asal, 'SJ', 'OP') = dss.kode_trans and mutasi.item = dss.kode_barang
--       where dss.noreg=@noreg and dss.jenis_barang=@jenis and dss.kode_barang=@dk_kode_barang and
--             (dss.jml_stok - isnull(mutasi.jumlah, 0)) > 0
--   ) )
--   BEGIN
--       select top 1 @ds_id=dss.id, @ds_jml_stok=dss.jml_stok, @ds_reserved=isnull(mutasi.jumlah,0)
--       from det_stok_siklus dss
--       left join ( ...subquery sama... ) mutasi on ...
--       where ...sama... order by dss.tgl_trans asc, dss.kode_trans asc
--
--       SET @ds_avail = @ds_jml_stok - @ds_reserved
--
--       IF ( @_dk_jumlah <= @ds_avail ) BEGIN
--           insert ... values (..., @_dk_jumlah, ...)
--           SET @ds_jml_stok = @ds_jml_stok - @_dk_jumlah
--           ...
--       END ELSE BEGIN
--           insert ... values (..., @ds_avail, ...)
--           SET @_dk_jumlah = @_dk_jumlah - @ds_avail
--           SET @ds_jml_stok = @ds_jml_stok - @ds_avail   -- saldo RIIL, bukan avail
--           ...
--       END
--   END

/* NOTE: jml_stok yang tersimpan di det_stok_siklus TETAP saldo riil penuh
   (bukan saldo-setelah-reservasi) -- reservasi cuma dipakai sesaat utk
   membatasi berapa banyak yang boleh diambil LHK saat itu. */
