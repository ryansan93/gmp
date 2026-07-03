# Database — GM ERP

## Koneksi Database

Semua koneksi dikonfigurasi di `application/config/env.php`.

### Koneksi: `default`

| Parameter | Nilai |
|---|---|
| Driver | `sqlsrv` (SQL Server via PDO) |
| Host | `103.137.111.6:14330` |
| Database | `gmp_erp_live` |
| Username | `sa` |

Database utama — semua transaksi bisnis, master data, akuntansi.

### Koneksi: `log`

| Parameter | Nilai |
|---|---|
| Driver | `sqlsrv` |
| Host | `103.137.111.6:14330` |
| Database | `log_history_gmp_erp_live` |

Database log — menyimpan riwayat perubahan data (`LogTables_model`).

### Koneksi: `mgb`

| Parameter | Nilai |
|---|---|
| Driver | `sqlsrv` |
| Host | (dikonfigurasi di env.php) |
| Database | `mgb_erp_live` |

Database MGB — data mitra (`Mitra_model` via `ConfMgb`).

---

## Cara Eloquent Boot

Di `DB_Controller` (`application/core/MY_Controller.php`):

```php
$this->capsule = new Capsule;
$env = $this->config->item('connection'); // dari env.php
foreach ($env as $nm => $val) {
    $this->capsule->addConnection($val, $nm);
}
$this->capsule->setAsGlobal();
$this->capsule->bootEloquent();
```

---

## Query Patterns

### 1. Eloquent ORM Biasa

```php
$m = new \Model\Storage\Lhk_model();

// Get all dengan filter
$data = $m->where('id_kandang', $id)
           ->whereBetween('tgl_lhk', [$tgl_awal, $tgl_akhir])
           ->orderBy('tgl_lhk', 'desc')
           ->get()
           ->toArray();

// Find by primary key
$row = $m->find($id)->toArray();

// Eager loading
$row = $m->with('lhk_sekat', 'lhk_pakan', 'lhk_nekropsi')->find($id);

// Insert
$m->id = 'LHK2505001';
$m->tgl_lhk = '2025-05-01';
$m->save();

// Update
$m->where('id', $id)->update(['status' => 1, 'updated_at' => date('Y-m-d H:i:s')]);

// Delete
$m->where('id', $id)->delete();
```

### 2. Raw Query via hydrateRaw

```php
$m = new \Model\Storage\Conf();
$sql = "
    SELECT l.id, l.tgl_lhk, k.nama_kandang
    FROM lhk l
    LEFT JOIN kandang k ON l.id_kandang = k.id
    WHERE l.id_mitra = ?
    ORDER BY l.tgl_lhk DESC
";
$data = $m->hydrateRaw($sql, [$id_mitra]);
$data = $data->toArray();
```

### 3. Raw Query via DB::select

```php
use Illuminate\Database\Capsule\Manager as DB;
$data = DB::select("SELECT * FROM lhk WHERE id = ?", [$id]);
```

### 4. Stored Procedure

```php
$m = new \Model\Storage\Conf();
$result = $m->runSp("EXEC hitung_stok_siklus @tipe=?, @sumber=?, @id=?, @tanggal=?, @status=?, @noreg=?", [
    $tipe, $sumber, $id, $tanggal, $status, $noreg
]);
```

Stored procedure utama:
- `hitung_stok_siklus` — menghitung stok per siklus budidaya
- `tutup_bulan` — proses penutupan bulan akuntansi

---

## ID Generation

Format ID: `{KODE}{YY}{MM}{SEQ3digit}`

Contoh: `LHK2505001` = LHK + tahun 25 + bulan 05 + sequence 001

```php
// Dari core_helper.php
$id = getNextId('LHK', 'lhk', 'id');
// Parameter: prefix, nama_tabel, nama_kolom_id
```

Fungsi ini:
1. Query MAX id dari tabel dengan prefix + bulan/tahun saat ini
2. Increment sequence
3. Return string ID baru

---

## Skema Tabel Utama

### Tabel Transaksi Header-Detail

Pola umum: tabel header + tabel detail(s)

```
lhk (header)
  ├── lhk_sekat (sekat kandang)
  ├── lhk_pakan (pakan harian)
  ├── lhk_peralatan (peralatan)
  ├── lhk_solusi (solusi)
  ├── lhk_nekropsi (kematian)
  └── lhk_foto_* (foto dokumentasi)

penerimaan_pakan (header)
  └── terima_pakan_detail (detail item)

real_sj (header realisasi SJ)
  ├── det_real_sj (detail)
  └── det_real_sj_inv (invoice)
```

### Tabel Jurnal

```
jurnal (header)
  └── det_jurnal (detail debit/kredit)

jurnal_trans (jurnal otomatis dari transaksi)
  └── det_jurnal_trans
```

### Tabel Stok

```
stok (posisi stok saat ini)
det_stok (detail per siklus)
det_stok_trans (mutasi stok)
```

### Tabel Akses

```
fitur → det_fitur (path URL)
group → det_group (mapping fitur ke group + akses)
user → det_user (mapping user ke group)
akses_khusus (override akses individual)
```

---

## Setting Jurnal Otomatis

Tabel `setting_automatic_jurnal`:
- `det_fitur_id` → link ke URL controller
- `_query` → SQL template untuk generate debit/kredit
- `tbl_name` → nama tabel sumber data
- `tgl_berlaku` → tanggal mulai berlaku (versi query bisa berubah)

Flow:
1. Setelah save transaksi, controller panggil `InsertJurnal::exec()`
2. Lookup `setting_automatic_jurnal` by URL path
3. Jalankan `_query` dengan substitusi `@tbl_id`, `@tbl_id_old`, `@action`
4. Hasil query dimasukkan ke `jurnal_trans` dan `det_jurnal_trans`

---

## SQL Server Specific

- Driver: `sqlsrv` (tidak ada MySQL)
- Tidak ada `AUTO_INCREMENT` — ID di-generate manual
- Stored procedure menggunakan named parameter: `@tipe=?`
- Date format: `YYYY-MM-DD HH:MM:SS`
- `SET NOCOUNT ON` selalu di awal stored procedure
- `DECLARE` variabel sebelum digunakan
