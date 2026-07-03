# Overview — GM ERP

## Deskripsi Proyek

**GM ERP** adalah sistem ERP untuk manajemen peternakan ayam broiler secara end-to-end, mencakup:
- Manajemen DOC (bibit), pakan, obat/vaksin
- Laporan harian kandang (LHK)
- Proses panen dan penjualan
- Akuntansi dan laporan keuangan
- Pembayaran dan hutang/piutang

---

## Tech Stack

| Komponen | Teknologi |
|---|---|
| Framework | CodeIgniter 3 dengan HMVC (MX_Controller) |
| ORM | Laravel Eloquent 5.0.28 (`illuminate/database`) |
| Database | Microsoft SQL Server (`sqlsrv`) via PDO |
| Export | PhpSpreadsheet `^1.25`, DomPDF `^3.1`, TCPDF, FPDI |
| Frontend | jQuery, Bootstrap 3, Select2, Moment.js, Bootbox, DateTimePicker |
| PHP | >= 5.2.4 (PHP 7.x pada XAMPP) |

---

## Struktur Direktori

```
gmperp/
├── application/
│   ├── core/                     # Base class CI yang dioverride
│   │   ├── MY_Controller.php     # DB_Controller, MY_Controller, Public_Controller, Admin_Controller, API_Controller
│   │   ├── MY_Config.php
│   │   ├── MY_Loader.php
│   │   ├── MY_Model.php
│   │   ├── MY_Router.php
│   │   └── MY_URI.php
│   ├── config/
│   │   ├── database.php          # Eloquent Capsule boot + PDO SQLSRV setup
│   │   ├── env.php               # Konfigurasi koneksi DB (live/test/local)
│   │   ├── autoload.php          # session, form_validation, helpers
│   │   └── routes.php            # default_controller: home/Home
│   ├── modules/                  # HMVC modules
│   │   ├── accounting/           # Jurnal, memorial, kas, bank, tutup bulan
│   │   ├── api/                  # REST API untuk mobile app
│   │   ├── bantuan/              # Jurnal bantuan (LHK, pakan, OVK, doc)
│   │   ├── base/                 # Shared controllers (InsertJurnal, ExportExcel, TutupBulan)
│   │   ├── home/                 # Dashboard
│   │   ├── hris/                 # KPI karyawan
│   │   ├── import/               # Import order & terima (pakan, voadip, doc)
│   │   ├── marketing/            # Daftar kunjungan
│   │   ├── master/               # Manajemen user, group, fitur
│   │   ├── parameter/            # Master data (peternak, supplier, pelanggan, dll)
│   │   ├── pembayaran/           # Pembayaran bakul, konfirmasi, verifikasi
│   │   ├── report/               # 61 controller laporan
│   │   ├── storage/models/       # 263+ Eloquent model (namespace Model\Storage)
│   │   ├── transaksi/            # 69 controller transaksi inti
│   │   └── user/                 # Login, profile user
│   ├── libraries/
│   │   ├── Eloquent.php          # Bridge CI ke Laravel Eloquent Capsule
│   │   ├── Pdf.php               # Wrapper TCPDF
│   │   ├── PDFGenerator.php      # Generator PDF umum
│   │   ├── Fpdi.php / Pdfi.php   # FPDI PDF
│   │   ├── Excel.php             # Wrapper PhpSpreadsheet
│   │   └── Mobile_Detect.php     # Deteksi user agent mobile
│   └── helpers/
│       ├── core_helper.php       # hakAkses(), display_json(), cetak_r(), dll (1200+ baris)
│       ├── MY_date_helper.php    # Helper tanggal custom
│       └── phppass_helper.php    # Hash password
├── assets/
│   ├── base/                     # JS/CSS global (config.js, common.js, app.js, index.js)
│   ├── themes/                   # Template Bootstrap + Font Awesome
│   ├── accounting/               # JS per fitur accounting
│   ├── transaksi/                # JS per fitur transaksi
│   ├── report/                   # JS per fitur report
│   └── [modul lain]/             # JS per modul
├── themes/
│   └── index.php                 # Template HTML utama (header + sidebar + content)
├── uploads/                      # File upload (foto LHK, lampiran, dll)
├── export_excel/                 # Temporary Excel export files
├── export_xml/                   # Temporary XML export files
└── vendor/                       # Composer dependencies
```

---

## Arsitektur Controller

### Inheritance Chain

```
MX_Controller (HMVC wirecap)
  └─ DB_Controller
       │  • Boot Eloquent Capsule
       │  • Register semua koneksi DB dari env.php
       │  • setAsGlobal() + bootEloquent()
       └─ MY_Controller
            │  • Load asset JS/CSS global
            │  • Set $this->user dari session
            │  • Set template path
            ├─ Public_Controller   ← Semua modul bisnis (cek session login)
            ├─ Admin_Controller    ← Admin area
            └─ API_Controller      ← Mobile API (no session, JSON only)
```

### Alur Request HTTP

```
HTTP Request
    │
    ▼
CI Router (HMVC) → Module/Controller/Method
    │
    ▼
Constructor: parent::__construct() → set $this->url = $this->current_base_uri
    │
    ▼
method(): hakAkses($this->url) → cek a_view / a_add / a_edit / a_delete
    │
    ├─ (page load)  add_external_js([...]) + load->view($pathView.'index', $data, TRUE)
    │                   │
    │                   ▼
    │               themes/index.php (template)
    │
    └─ (AJAX POST)  $this->input->post('params') → logic bisnis → display_json($this->result)
```

---

## Konvensi Umum

- Setiap controller extends `Public_Controller`
- Setiap method cek `hakAkses()` sebelum logic bisnis
- AJAX response selalu via `display_json($this->result)` dengan format `{status, message, content}`
- Model selalu di namespace `Model\Storage`, extends `Conf` (base Eloquent model)
- ID generated dengan format `{KODE}{YY}{MM}{SEQ}`, contoh: `ODC2505001`
