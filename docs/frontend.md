# Frontend — GM ERP

## Library & Framework

| Library | Versi/Sumber | Fungsi |
|---|---|---|
| jQuery | `assets/themes/lib/jquery/jquery.min.js` | DOM manipulation, AJAX |
| jQuery UI | `assets/jquery-ui/` | Datepicker, interaksi |
| Bootstrap | `assets/themes/vendor/bootstrap/` (v3.x + v4?) | Layout, komponen UI |
| Bootstrap Select | `bootstrap-select.min.js` | Dropdown dengan search |
| Bootstrap DateTimePicker | `assets/bootstrap-datetimepicker/` | Input tanggal/waktu |
| Moment.js | `assets/moments/` | Parsing & format tanggal |
| Bootbox | `assets/bootbox/` | Dialog konfirmasi (alert, confirm) |
| Select2 | `assets/select2/` | Dropdown dengan search & AJAX |
| jQuery Price Format | `jquery.price_format.min.js` | Format angka/mata uang |
| Font Awesome | `assets/themes/lib/font-awesome/` | Icon |
| CryptoJS | `assets/crypto/` | SHA1 hash |

---

## Struktur Asset

```
assets/
├── base/
│   ├── config.js          # Config global (format angka, dsb)
│   ├── common.js          # Utility functions global (700+ baris)
│   ├── app.js             # App singleton (300+ baris)
│   ├── index.js           # jQuery DOM ready bootstrap (160+ baris)
│   └── css/base.css       # CSS global
├── themes/
│   ├── index.php          # Template HTML utama
│   ├── lib/               # jQuery, Font Awesome
│   └── vendor/bootstrap/  # Bootstrap CSS & JS
├── accounting/
│   ├── memorial/js/memorial.js
│   ├── hitung_ulang/
│   ├── posting_ulang/
│   └── [nama_fitur]/js/[nama-fitur].js
├── transaksi/
│   ├── lhk/js/lhk.js
│   ├── realisasi_sj_mobile/js/realisasi-sj-mobile.js
│   └── [nama_fitur]/js/[nama-fitur].js
├── report/
│   ├── general_ledger/js/general-ledger.js
│   └── [nama_laporan]/js/[nama-laporan].js
├── pembayaran/
├── parameter/
└── [modul lain]/
```

---

## Template HTML

File: `themes/index.php`

Struktur halaman:
```html
<!DOCTYPE html>
<html>
<head>
    <!-- CSS global dari $includes['css'] -->
    <!-- CSS tambahan per halaman -->
</head>
<body>
    <!-- Sidebar navigasi -->
    <!-- Header/navbar -->
    <div id="content">
        <!-- $view (output dari controller) -->
    </div>
    <!-- JS global dari $includes['js'] -->
    <!-- JS tambahan per halaman -->
</body>
</html>
```

Controller memasukkan JS/CSS tambahan via:
```php
$this->add_external_js(['assets/modul/fitur/js/fitur.js']);
$this->add_external_css(['assets/modul/fitur/css/fitur.css']);
```

---

## Global Utilities (common.js)

Fungsi-fungsi tersedia secara global:

| Fungsi | Kegunaan |
|---|---|
| `showLoading()` / `hideLoading()` | Tampilkan/sembunyikan loading overlay |
| `showSuccess(msg)` | Toast/alert sukses |
| `showError(msg)` | Toast/alert error |
| `formatNumber(n)` | Format angka dengan pemisah ribuan |
| `unformatNumber(s)` | Hapus formatting, ambil angka |
| `formatDate(d)` | Format tanggal ke `DD/MM/YYYY` |
| `parseDate(s)` | Parse string tanggal |
| `confirmDelete(callback)` | Dialog konfirmasi hapus (Bootbox) |

---

## Pola View (PHP)

File view berada di `application/modules/{modul}/views/{nama_controller}/`.

### File-file umum per fitur

| File | Isi |
|---|---|
| `index.php` | Halaman utama + tabel/grid |
| `addForm.php` | Form tambah data |
| `editForm.php` | Form edit data |
| `detail.php` | Halaman detail (opsional) |
| `print.php` | Layout cetak/PDF (opsional) |

### Pola View index

```php
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Judul Fitur</h4>
            </div>
            <div class="panel-body">
                <!-- Filter form -->
                <form id="form-filter">
                    <input type="text" id="tgl_awal" class="form-control datepicker">
                    <button type="button" id="btn-search">Cari</button>
                </form>
                <!-- Tabel data -->
                <table id="table-data" class="table table-bordered table-hover table-striped">
                    <thead>...</thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Modal tambah/edit -->
<div class="modal" id="modal-form">...</div>
```

---

## Datepicker

```javascript
// Inisialisasi
$('.datepicker').datepicker({
    format: 'dd/mm/yyyy',
    language: 'id',
    autoclose: true
});

// DateTimePicker (Moment.js based)
$('#tgl').datetimepicker({
    format: 'DD/MM/YYYY',
    locale: 'id'
});
```

---

## Select2

```javascript
// Select2 statis
$('#select-pelanggan').select2({
    placeholder: 'Pilih Pelanggan',
    allowClear: true
});

// Select2 dengan AJAX search
$('#select-mitra').select2({
    ajax: {
        url: 'parameter/Peternak/search',
        dataType: 'json',
        data: function(params) {
            return { 'params': { q: params.term } };
        },
        processResults: function(data) {
            return { results: data.content };
        }
    }
});
```

---

## Format Angka

```javascript
// Dari config.js dan common.js
var cfg = {
    thousandSeparator: '.',
    decimalSeparator: ',',
    precision: 0
};

// Input format otomatis
$('.input-number').priceFormat({
    prefix: '',
    centsSeparator: ',',
    thousandsSeparator: '.',
    limit: 15
});
```

---

## Mobile Support

- Deteksi mobile via `Mobile_Detect` library (PHP)
- Beberapa fitur punya versi mobile tersendiri (suffix `Mobile`)
  - `PenerimaanPakanMobile`, `KonfirmasiPanenMobile`, `RpahMobile`, dll
- API mobile tersendiri di modul `api/` (extends `API_Controller`)
