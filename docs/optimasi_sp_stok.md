# Rekomendasi Optimasi Stored Procedure Stok

Dokumen ini merangkum usulan optimasi untuk 3 stored procedure di database `gmp_erp_live`:

- `hitung_stok_pakan_by_transaksi`
- `hitung_stok_voadip_by_transaksi`
- `hitung_stok_siklus`

> **Catatan penting:** ini database **LIVE**. Semua script di bawah harus diuji di luar jam sibuk
> (idealnya di DB test `gmp_erp` dulu). Perubahan indeks aman terhadap logika; perubahan kode SP
> (Prio 2–5) harus di-review & dibandingkan hasilnya sebelum di-deploy.

Tanggal dibuat: 2026-06-22

---

## Ringkasan Prioritas

| Prio | Area | Dampak | Risiko | Ubah logika? |
|------|------|--------|--------|--------------|
| 1 | Indeks salah urutan / hilang | **Sangat tinggi** | Rendah | Tidak |
| 2 | Pola query mahal dieksekusi 2× | Tinggi | Sedang | Tidak (hasil sama) |
| 3 | Subquery `stok` tak di-cache (SP pakan) | Sedang | Rendah | Tidak |
| 4 | Dynamic SQL recompile (SP voadip) | Sedang | Sedang | Tidak (hasil sama) |
| 5 | `hitung_stok_siklus` dipanggil di dalam loop | Tinggi | Tinggi | Perlu kajian |
| 6 | Cleanup debug / dead code | Rendah | Rendah | Tidak |

**Urutan eksekusi yang disarankan:** mulai Prio 1 (indeks) → ukur dampak → lanjut Prio 2–3 → kaji Prio 4–5.

---

## 🔴 Prioritas 1 — Indeks salah urutan (dampak terbesar, risiko nol)

### Masalah
Semua nonclustered index di tabel panas **diawali kolom `id`** (identity/PK yang unik). Karena kolom
pertama sudah unik, index tidak bisa dipakai *seek* untuk filter yang sebenarnya dipakai SP — query
jatuh ke *scan*. Selain itu **tidak ada index pada `stok(periode)`** padahal dipakai di setiap iterasi
loop harian.

Index yang ada sekarang:

```
det_stok            det_stok_IDX             (id, id_header, kode_gudang, kode_barang, kode_trans)
det_stok_siklus     det_stok_siklus_IDX      (id, id_header, tgl_trans, noreg, kode_barang, kode_trans, jenis_barang)
det_stok_trans      det_stok_trans_IDX       (id, id_header, kode_barang, kode_trans)
det_stok_trans_siklus det_stok_trans_siklus_IDX (id, id_header, tgl_trans, kode_trans, kode_barang)
stok                stok_IDX                 (id)        <-- tidak ada index di kolom periode
```

Filter aktual di SP:
- `stok`            → `where periode = @tgl_transaksi`
- `det_stok`        → `id_header + kode_gudang + kode_barang + jml_stok (+ tgl_trans, jenis_trans)`
- `det_stok_siklus` → `noreg + jenis_barang + jml_stok + kode_trans + kode_barang (+ tgl_trans)`
- `det_stok_trans_siklus` → `id_header + tgl_trans`

### Usulan script (uji dulu di DB test)

```sql
-- 1) stok(periode): dipakai di SETIAP iterasi loop harian (paling sering)
CREATE NONCLUSTERED INDEX IX_stok_periode
    ON stok (periode);

-- 2) det_stok: lookup stok per gudang+barang yang masih ada sisa
CREATE NONCLUSTERED INDEX IX_det_stok_gudang_barang
    ON det_stok (id_header, kode_gudang, kode_barang, jml_stok)
    INCLUDE (tgl_trans, hrg_beli, hrg_jual, jenis_trans, jenis_barang, kode_trans);

-- 3) det_stok_siklus: lookup stok siklus per noreg+jenis yang masih ada sisa
CREATE NONCLUSTERED INDEX IX_det_stok_siklus_noreg
    ON det_stok_siklus (noreg, jenis_barang, jml_stok, kode_trans, kode_barang)
    INCLUDE (tgl_trans, hrg_beli, hrg_jual, oa, jumlah);

-- 4) det_stok_trans_siklus: agregasi & delete per id_header + tgl_trans
CREATE NONCLUSTERED INDEX IX_det_stok_trans_siklus_header_tgl
    ON det_stok_trans_siklus (id_header, tgl_trans)
    INCLUDE (jumlah, kode_trans, kode_barang, tbl_name);
```

> Sebelum membuat, cek kardinalitas/ukuran tabel agar daftar kolom INCLUDE pas (jangan terlalu lebar).
> Index lama yang diawali `id` bisa dipertahankan dulu (duplikat PK, tidak mengganggu), evaluasi untuk
> di-drop setelah index baru terbukti dipakai.

### Cek pemakaian index setelahnya
```sql
SELECT OBJECT_NAME(s.object_id) AS tabel, i.name, s.user_seeks, s.user_scans, s.user_lookups
FROM sys.dm_db_index_usage_stats s
JOIN sys.indexes i ON i.object_id = s.object_id AND i.index_id = s.index_id
WHERE s.database_id = DB_ID() AND OBJECT_NAME(s.object_id) LIKE 'det_stok%';
```

---

## 🟠 Prioritas 2 — Pola "query mahal dieksekusi 2×"

### Masalah
Pola berulang di ketiga SP (di loop terdalam):

```sql
IF ( EXISTS( <query top-1 yang berat> ) )
BEGIN
    SELECT TOP 1 @vars = ... FROM <query yang SAMA PERSIS>
    ...
END
```

Query terberat dijalankan **dua kali**: sekali untuk `EXISTS`, sekali untuk ambil nilai.

### Usulan
Langsung `SELECT TOP 1 ... INTO @vars` lalu cek hasil:

```sql
SET @ds_id = NULL;
SELECT TOP 1
    @ds_id = id,
    @ds_jml_stok = jml_stok,
    @ds_kode_brg = kode_barang
FROM det_stok_siklus
WHERE noreg = @noreg AND tgl_trans <= @tgl_transaksi
  AND jenis_barang = @jenis AND jml_stok > 0
ORDER BY tgl_trans ASC, kode_trans ASC;

IF ( @ds_id IS NOT NULL )   -- atau cek @@ROWCOUNT > 0
BEGIN
    ...
END
ELSE
BEGIN
    SET @dk_jumlah = 0;     -- atau @_dk_jumlah = 0, sesuai blok
END
```

Lokasi yang terkena (perlu diterapkan di semua):
- `hitung_stok_pakan_by_transaksi` — blok HITUNG DATA KELUAR.
- `hitung_stok_voadip_by_transaksi` — blok pemilihan `#ds`.
- `hitung_stok_siklus` — blok DOC, PAKAN (cabang `>= 2026-05-01` dan lama), VOADIP.

> Hasil harus **identik**. Wajib bandingkan output sebelum/sesudah pada sample data.

---

## 🟠 Prioritas 3 — Subquery `stok` tak di-cache (khusus SP pakan)

### Masalah
`hitung_stok_voadip` & `hitung_stok_siklus` sudah meng-cache id periode (`@id_stok` / `@id_header`).
Tapi `hitung_stok_pakan_by_transaksi` mengulang `(SELECT id FROM stok WHERE periode = @tgl_transaksi)`
**belasan kali** di dalam inner loop (pada setiap INSERT/UPDATE/EXISTS ke `det_stok`).

### Usulan
Cache sekali per hari, sama seperti dua SP lain:

```sql
-- setelah memastikan row stok untuk @tgl_transaksi ada:
DECLARE @id_stok int;
SELECT TOP 1 @id_stok = id FROM stok WHERE periode = @tgl_transaksi ORDER BY id DESC;
```
Lalu ganti semua `(select id from stok where periode = @tgl_transaksi)` menjadi `@id_stok`.

---

## 🟡 Prioritas 4 — Dynamic SQL recompile (khusus SP voadip)

### Masalah
Di `hitung_stok_voadip_by_transaksi`, query pemilihan stok dibangun via string-concat lalu
`EXEC sp_executesql @query` **di dalam `WHILE (@jml_keluar > 0)`** → recompile tiap iterasi, tanpa
plan reuse. Versi statiknya bahkan sudah ada tepat di bawahnya.

### Usulan
- Hilangkan dynamic SQL, pakai query statik dengan predikat opsional:
  `AND (@dk_kode_trans_tujuan = '' OR ds.kode_trans = @dk_kode_trans_tujuan)`, atau
- Jika tetap dinamis, parameterkan via `sp_executesql` dengan `@params` (bukan concat literal) agar plan ter-cache.

---

## 🟡 Prioritas 5 — Arsitektur: `hitung_stok_siklus` dipanggil di dalam loop

### Masalah
`hitung_stok_pakan` & `hitung_stok_voadip` memanggil `EXEC hitung_stok_siklus` **di dalam loop
per-hari per-transaksi**. Karena `hitung_stok_siklus` me-recompute dari `@_tgl_transaksi` s/d hari ini,
satu panggilan SP induk bisa memicu **banyak full-recompute siklus**. Ini penyebab struktural lambat
saat volume besar.

### Usulan (perlu kajian, jangan langsung)
- Kumpulkan kombinasi (`tbl_name`, `tbl_id`, `tgl`) yang perlu re-hitung siklus ke sebuah set,
  lalu panggil `hitung_stok_siklus` **sekali di akhir** (batch), bukan per baris.
- Atau pasang guard agar tidak memanggil ulang untuk (noreg, tgl) yang sudah dihitung pada eksekusi yang sama.

> Risiko tinggi karena menyentuh urutan perhitungan. Wajib uji rekonsiliasi penuh.

---

## 🧹 Prioritas 6 — Cleanup debug & dead code

- **`hitung_stok_pakan`**: hapus debug yang nyangkut di alur produksi —
  `IF (@kode_trans = 'OP/JBR/26/04067') BEGIN select * from #d_dst; print ... END`
  dan `IF (@dk_kode_trans = 'OP/MJK/26/04112') print ...`.
  Statement `select` ini **mengembalikan resultset tak terduga** ke client tiap kondisi kena.
- **`hitung_stok_siklus`**: `@start_date` / `@end_date` sudah jadi dead code (ada typo `:999`),
  filter sudah pindah ke `cast(... as date) BETWEEN`. Bisa dihapus.
- Tinjau `SET ANSI_WARNINGS OFF` — membatasi opsi plan (indexed view/computed column). Pastikan masih
  diperlukan (biasanya untuk menekan warning agregasi NULL).
- Temp table `#pp_pakan`, `#data_harian`, `#d_dst` tanpa index — tambah index bila jumlah baris besar.
- Banyak **tanggal hard-coded** (`'2026-05-01'`, `'2026-05-08'`, `'2022-09-06'`) sebagai percabangan
  logika. Bukan isu performa, tapi catat sebagai utang teknis (perilaku bergantung periode).

---

## Checklist penerapan

- [ ] Backup definisi SP saat ini (sudah ada salinan teks di sesi ini bila perlu).
- [ ] Uji semua script di DB test `gmp_erp` lebih dulu.
- [ ] Prio 1: buat index → ukur durasi SP sebelum/sesudah pada transaksi sample.
- [ ] Prio 2 & 3: refactor 1 SP dulu (mis. `hitung_stok_pakan`), bandingkan isi `det_stok` /
      `det_stok_trans` hasil lama vs baru → harus identik.
- [ ] Prio 4: voadip.
- [ ] Prio 5: kaji terpisah, jangan digabung dengan batch lain.
- [ ] Prio 6: cleanup setelah semua perubahan fungsional stabil.

---

# Update 2026-06-29 — Status terkini & temuan baru

Sesi telaah ulang `hitung_stok_pakan_by_transaksi` & `hitung_stok_siklus` di DB **LIVE** `gmp_erp_live`.
**Belum ada perubahan logika yang di-deploy ke produksi.** Eksekusi ditahan atas permintaan.

## Objek yang sudah ada di DB sekarang

| Objek | Kondisi |
|---|---|
| `hitung_stok_pakan_by_transaksi` | Produksi — belum disentuh |
| `hitung_stok_pakan_by_transaksi_new` | **Sandbox baru** — sudah dapat Prio 3 (cache `@id_stok`, 15 subquery diganti) |
| `hitung_stok_siklus` | Produksi — belum disentuh |
| `hitung_stok_siklus_new` | **Sandbox baru** — copy identik (belum diperbaiki) |
| Indeks `ix_stok_periode` ON `stok(periode) INCLUDE(id)` | ✅ **SUDAH DIBUAT & aktif** (Prio 1 item #1). Dipakai kedua SP. |

> Catatan: indeks `det_stok`, `det_stok_siklus`, `det_stok_trans`, `det_stok_trans_siklus` (Prio 1 item #2–#4)
> **belum** dibuat.

## 🚩 TEMUAN BARU & KRITIS — badan `hitung_stok_siklus` terduplikasi

Belum tercatat di dokumen 2026-06-22. Diverifikasi via `sys.sql_modules` (sumber kanonik), bukan artefak dump:

- Preamble (`create table #pp_pakan`, blok `DECLARE`) dan seluruh logika cabang (doc/pakan/voadip)
  muncul **2×**: paruh-1 = baris 1–2608, paruh-2 = baris 2609–4171.
- Hanya **1** `CREATE PROCEDURE`, **tanpa `GO`**, **tanpa `RETURN`** di antara kedua paruh.
- Paruh-1 & paruh-2 **beda panjang** (2608 vs 1563 baris) → bukan copy persis, tampaknya **dua varian**
  (indikasi versi baru di-paste tanpa menghapus versi lama).
- Anomali: duplikat `create table #x` + `DECLARE` dalam satu proc **seharusnya gagal kompilasi**
  (sudah dibuktikan Msg 2714 & Msg 134), tapi proc ini tetap ada. Mekanisme persisnya belum dipastikan.

**Implikasi (WAJIB diuji aman dulu — `BEGIN TRAN … ROLLBACK` di `_new`):**
- Bila paruh-2 ikut jalan → semua kerja dilakukan ~**2×** (boros besar).
- Bila paruh-2 error runtime di `create table #pp_pakan` kedua → proc melempar error tiap dipanggil,
  padahal tulisan paruh-1 sudah terlanjur commit.

➡️ **Ini kandidat pembenahan #1 untuk `hitung_stok_siklus`** — dampaknya jauh di atas tuning indeks.
Langkah: identifikasi paruh kanonik (varian mana yang dipertahankan), buang duplikatnya, uji rekonsiliasi.

## Pemetaan status terhadap Prioritas dokumen 2026-06-22

| Prio | Item | Status per 2026-06-29 |
|---|---|---|
| 1 | Indeks `stok(periode)` | ✅ **Selesai** (`ix_stok_periode`) |
| 1 | Re-key `det_stok` / `det_stok_siklus` / `det_stok_trans*` | ⏳ Belum |
| 2 | Query mahal dieksekusi 2× (blok KELUAR) | ⏳ Belum |
| 3 | Cache `@id_stok` di SP pakan | ✅ **Selesai di `_new`** (belum promote) |
| 5 | `hitung_stok_siklus` dipanggil dalam loop | ⏳ Belum (kaji bareng temuan badan-dobel) |
| 6 | Hapus debug nyangkut SP pakan (baris 839–845, 1052–1057) | ⏳ Belum |
| 6 | Dead code `@start_date/@end_date` + typo `'23:59:59:999'` (baris 260) di siklus | ⏳ Belum |
| — | **Badan `hitung_stok_siklus` terduplikasi** | 🔬 **BARU — prioritas teratas, perlu uji aman** |

## Urutan eksekusi yang disarankan (revisi)
1. 🔬 **Uji aman badan-dobel `hitung_stok_siklus`** di `_new` (TRAN+ROLLBACK) → tentukan & buang paruh duplikat.
2. Prio 6 — hapus debug nyangkut SP pakan (murah, correctness) di `_new`.
3. Prio 2 & sisa Prio 3 (carry-forward `FORMAT`→`date`) di `_new`, bandingkan `det_stok` lama vs baru → harus identik.
4. Prio 1 sisa — re-key indeks detail (additive, low-risk), ukur dampak via `dm_db_index_usage_stats`.
5. Prio 5 + struktural (skip hari kosong, set-based FIFO) — kaji terpisah, paling akhir.

> Semua perubahan logika tetap diuji di `_*_new` & dibandingkan hasilnya sebelum promote ke produksi.
> Indeks boleh langsung (additive).
