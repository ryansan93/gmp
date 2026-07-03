# Catatan Sesi Diskusi — 3 Juni 2026

## Topik yang Dibahas

---

## 1. Analisa Selisih GL vs Kartu Hutang Ringkas (per 30 April 2026)

### File detail lengkap: `docs/analisa_selisih_gl_vs_khr_doc.md`

### Perbaikan Controller KartuHutangRingkas.php (SUDAH DIIMPLEMENTASI)

| # | Perubahan | Dampak |
|---|---|---|
| 1 | Tambah `NOT IN ('PLASMA', 'OA PAKAN')` di kredit realisasi | Pisahkan handling PLASMA & OA PAKAN |
| 2 | UNION kredit PLASMA pakai `rp.peternak` | RHPP: 181B → 7.6 juta |
| 3 | UNION kredit OA PAKAN pakai `rp.ekspedisi` | OA PAKAN: 17.9B → 273 juta |
| 4 | Expand filter memo ke `21180.200` & `21180.100` | Memo DOC & PAKAN ter-capture |
| 5 | Fallback supplier bayar memo ke `m.no_supplier` | Memo tanpa no_invoice bisa resolve |

### Selisih DOC — Prioritas Tindak Lanjut Akuntansi

| Prioritas | Aksi | Nilai |
|---|---|---|
| ⭐ Tinggi | Generate jurnal GL untuk **15 CN DOC** yang belum ter-post | 67,030,484 |
| ⭐ Tinggi | Konfirmasi 5 BBM November 2025 tanpa jurnal GL | 505,946,000 |
| Medium | Cek double-post 3 BBM MLG/TAG | 388,922,200 |
| Medium | Investigasi RHPP sisa ~1M di COA 21213 | 1,052,303,046 |

### 15 CN DOC yang Harus Di-post ke GL
```sql
SELECT cp.id as cn_post_id, cp.no_cn, cp.tanggal, cpd.nomor as byd_nomor, cpd.pakai
FROM cn_post cp
LEFT JOIN cn_post_det cpd ON cpd.id_header = cp.id
WHERE cp.jenis_cn = 'DOC'
  AND cpd.nomor IN (
    'BYD/12/25/00181','BYD/02/26/00017','BYD/02/26/00333',
    'BYD/11/25/00189','BYD/02/26/00238','BYD/12/25/00365',
    'BYD/11/25/00274','BYD/11/25/00147','BYD/11/25/00314',
    'BYD/03/26/00109','BYD/01/26/00001','BYD/12/25/00056',
    'BYD/01/26/00228','BYD/03/26/00320','BYD/04/26/00266'
  )
ORDER BY cp.tanggal
```

---

## 2. ODVP — Fitur Edit Terima DOC dari Pusat (SUDAH DIIMPLEMENTASI)

### File yang Diubah
- `application/modules/transaksi/controllers/ODVP.php`
- `application/modules/transaksi/views/odvp/terima_doc_edit_form.php`
- `assets/transaksi/odvp/js/odvp.js`

### Apa yang Ditambahkan
- Tombol **"Edit dari Pusat"** (kuning) di form edit terima DOC
- Modal berisi:
  - Textarea **alasan edit** (wajib)
  - Upload **dokumen Berita Acara** (wajib, maks 5MB)
- Data disimpan ke kolom `terima_doc.keterangan_salah` dan `terima_doc.path_ba`
- Juga disimpan ke tabel `terima_doc_ket`
- Log event: "Edit dari Pusat oleh [nama] — Alasan: ..."

### Kontrol Akses
Tombol hanya tampil jika user punya akses khusus `edit_doc_dari_pusat`.

Cara aktifkan: tambah data di tabel `akses_khusus` via fitur Master → Akses Khusus:
- **Group**: group yang diizinkan (misal BPM1, ADMIN)
- **Fitur**: kosongkan (berlaku semua fitur) atau pilih ODVP
- **Akses Khusus**: `edit_doc_dari_pusat`

Controller method baru: `edit_terima_doc_pusat()`

---

## 3. Master Akses Khusus — Fitur Baru (SUDAH DIIMPLEMENTASI)

### File yang Dibuat
| File | Keterangan |
|---|---|
| `application/modules/master/controllers/AksesKhusus.php` | Controller CRUD |
| `application/modules/master/views/akses_khusus/index.php` | Halaman utama |
| `application/modules/master/views/akses_khusus/list.php` | Tabel data |
| `application/modules/master/views/akses_khusus/form.php` | Modal add/edit |
| `assets/master/akses_khusus/js/akses-khusus.js` | JS handler |

### Endpoint
| Method | URL | Fungsi |
|---|---|---|
| GET | `master/AksesKhusus` | Halaman utama |
| GET | `master/AksesKhusus/get_lists` | AJAX list data |
| GET | `master/AksesKhusus/get_form?id=X` | Form add/edit |
| POST | `master/AksesKhusus/save_data` | Simpan baru |
| POST | `master/AksesKhusus/update_data` | Update data |
| POST | `master/AksesKhusus/delete_data` | Hapus data |

### Aktivasi
Daftarkan di **Master → Fitur** dengan path `master/AksesKhusus`, lalu assign ke group yang perlu.

### Bug Fix yang Ditemukan
- **Stacked bootbox issue**: `$('input.ak-key').val()` return `undefined` karena saat callback `bootbox.confirm()` berjalan, elemen form tidak bisa diakses. Fix: kumpulkan data dengan `_collect()` **sebelum** memanggil `bootbox.confirm()`.

---

## 4. Status Perubahan Kode

> Semua perubahan **sudah ada di working directory** (belum di-commit ke git).
> File-file berubah bisa dilihat dengan `git status`.

### File Modified
- `application/modules/report/controllers/KartuHutangRingkas.php`
- `application/modules/transaksi/controllers/ODVP.php`
- `application/modules/transaksi/views/odvp/terima_doc_edit_form.php`
- `assets/transaksi/odvp/js/odvp.js`

### File New (Untracked)
- `application/modules/master/controllers/AksesKhusus.php`
- `application/modules/master/views/akses_khusus/` (3 file)
- `assets/master/akses_khusus/js/akses-khusus.js`
- `docs/analisa_selisih_gl_vs_khr_doc.md`
- `docs/session_notes_2026_06_03.md` (file ini)

---

*Catatan dibuat: 3 Juni 2026*
