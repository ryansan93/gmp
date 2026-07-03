# mgberp_php7 — GM ERP (Poultry/Farming ERP)

ERP system untuk manajemen peternakan ayam broiler, dibangun dengan CodeIgniter 3 HMVC + Laravel Eloquent ORM + SQL Server.

---

## Tech Stack

| Komponen | Teknologi |
|---|---|
| Framework | CodeIgniter 3 (HMVC via MX_Controller) |
| ORM | Laravel Eloquent 5.0.28 (`illuminate/database`) |
| Database | Microsoft SQL Server (`sqlsrv`) |
| DB Name | `gmp_erp_live` (main), `log_history_gmp_erp_live` (log), `mgb_erp_live` |
| Frontend | jQuery, Bootstrap 3, Select2, Moment.js, Bootbox, DateTimePicker |

---

## Struktur Project

```
gmperp/
├── application/
│   ├── core/                     ← Base classes
│   │   ├── MY_Controller.php     ← DB_Controller, MY_Controller, Public_Controller, Admin_Controller
│   │   ├── MY_Config.php
│   │   ├── MY_Loader.php
│   │   ├── MY_Model.php
│   │   ├── MY_Router.php
│   │   └── MY_URI.php
│   ├── config/
│   │   ├── database.php          ← Eloquent Capsule boot
│   │   ├── env.php               ← DB connections (live/test/local)
│   │   ├── autoload.php          ← session, form_validation, helpers (core, url, form, date)
│   │   └── routes.php            ← default: home/Home
│   ├── modules/                  ← HMVC modules
│   │   ├── transaksi/controllers/ ← 69 controllers (inti bisnis)
│   │   ├── accounting/controllers/
│   │   ├── report/controllers/   ← 61 controllers
│   │   ├── pembayaran/controllers/
│   │   ├── storage/models/       ← 265 Eloquent models
│   │   ├── master/controllers/
│   │   ├── home/controllers/
│   │   ├── bantuan/controllers/
│   │   └── ...
│   ├── libraries/
│   │   ├── Eloquent.php          ← Bridge: CI → Laravel Eloquent Capsule
│   │   ├── Pdf.php, PDFGenerator.php, Fpdi.php, tcpdf/
│   │   ├── Excel.php
│   │   └── Mobile_Detect.php
│   └── helpers/
│       ├── core_helper.php       ← hakAkses(), display_json(), cetak_r(), dll (1212 baris)
│       ├── MY_date_helper.php
│       └── phppass_helper.php
├── assets/
│   ├── base/
│   │   ├── config.js             ← Config object (number formatting)
│   │   ├── common.js             ← Global utility functions (707 baris)
│   │   ├── app.js                ← App singleton (309 baris)
│   │   └── index.js              ← jQuery DOM ready bootstrap (166 baris)
│   └── transaksi/lhk/
│       ├── css/lhk.css
│       └── js/lhk.js
├── themes/
└── uploads/
```

---

## Arsitektur & Alur

### Inheritance Controller

```
MX_Controller (HMVC)
  └─ DB_Controller       ← boots Eloquent Capsule, sets DB connections
       └─ MY_Controller  ← template, assets, base_uri, pathView
            ├─ Public_Controller  ← session check, userdata (DEFAULT untuk semua modul)
            ├─ Admin_Controller
            └─ API_Controller
```

### Alur Request

```
HTTP Request → CI Router (HMVC) → Module/Controller/Method
                                        │
                          ┌─────────────┼─────────────┐
                          ▼             ▼             ▼
                   hakAkses()      add_external_js()   Logic bisnis
                                      add_external_css()  (Eloquent models)
                          │             │             │
                          └─────────────┼─────────────┘
                                        ▼
                              $this->load->view($pathView.'index', $content, TRUE)
                                        │
                                        ▼
                              Template themes/index.php
                                        ▼
                              Response HTML / JSON (display_json)
```

---

## Pola Coding

### PHP Controller

```php
class LHK extends Public_Controller {
    private $pathView = 'transaksi/lhk/';
    private $url;

    public function __construct() {
        parent::__construct();
        $this->url = $this->current_base_uri;
    }

    public function index() {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(['assets/transaksi/lhk/js/lhk.js']);
            $data = $this->includes;
            $content['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $data['view'] = $content['view'];
            $this->load->view($this->template, $data);
        }
    }
}
```

**Ciri khas:**
- Constructor: `parent::__construct()` + set `$this->url`
- Setiap method: cek `hakAkses()` dulu
- View: `$this->load->view($path, $data, TRUE)` → return string, masuk template
- AJAX: semua data via `$this->input->post('params')`, return `display_json($this->result)`
- Error handling: `try/catch` → `$this->result['message']`

### Eloquent Model

```php
namespace Model\Storage;

class Lhk_model extends Conf {
    protected $table = 'lhk';
    public $incrementing = false;
    public $timestamps = false;

    public function lhk_sekat() {
        return $this->hasMany('\Model\Storage\LhkSekat_model', 'id_header', 'id');
    }
}
```

**Ciri khas:**
- `Conf` (extends `Eloquent\Model`) sebagai base — ada di `storage/models/Conf.php`
- `$incrementing = false`, `$timestamps = false`
- Relasi pakai full classpath: `'\Model\Storage\X_model'`
- Sering chain `.with()` untuk eager loading
- Factory ID via `getNextId()`, `getNextDocNum()`, `getNextNomor()` — format `ODC2505001`

### JavaScript Module

```javascript
var lhk = {
    start_up: function() {
        lhk.setting_lhk('transaksi', 'div#transaksi');
    },

    save: function(elm) {
        $.ajax({
            url: 'transaksi/LHK/save',
            data: { 'params': data },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function() { showLoading(); },
            success: function(data) {
                if ( data.status == 1 ) { ... }
            }
        });
    }
};

lhk.start_up();
```

**Ciri khas:**
- Singleton object global (`var lhk = {...}`, `var odvp = {...}`)
- Method panggil diri sendiri: `lhk.methodName()`
- AJAX: `url = 'module/controller/method'`, data dalam `{ 'params': data }`
- Response: `{ status: 1, message: "...", content: {...} }`
- No module bundler, no ES6 classes, no frameworks

### AJAX Response Convention

```json
{
    "status": 1,        // 1 = sukses, undefined/hilang = error
    "message": "...",   // error message (hanya diisi saat error)
    "content": { ... }  // data response (hanya diisi saat sukses)
}
```

Catatan: Saat error, `$this->result['status']` tidak di-set (undefined) — hanya `message`.  
Client cek: `if ( data.status == 1 ) { ... }`

---

## Domain Bisnis

Rantai bisnis peternakan ayam broiler **end-to-end**:

```
DOC (bibit) ──→ Kandang ──→ Pakan ──→ Obat/Vaksin ──→ Panen ──→ Penjualan
     │              │          │            │              │           │
     ▼              ▼          ▼            ▼              ▼           ▼
  OrderDoc      RDIM      KirimPakan   KirimVoadip    RPAH/RealSJ  Piutang
  TerimaDoc     Submit    TerimaPakan  TerimaVoadip   Konfirmasi   Pembayaran
                                                       Panen
     │              │          │            │              │           │
     └──────────────┴──────────┴────────────┴──────────────┴───────────┘
                                    │
                                    ▼
                              LHK (Laporan Harian Kandang)
                              ────────────────────────────
                              • BB, ADG, FCR, IP, DH
                              • Pemakaian & sisa pakan
                              • Kematian & nekropsi
                              • Solusi & peralatan kandang
                              • Foto dokumentasi
                                    │
                                    ▼
                              Hitung Stok DOC & Pakan
                                    │
                                    ▼
                              Insert Jurnal Akuntansi
                                    │
                                    ▼
                              Laporan Keuangan (Neraca, LabaRugi, Kartu Stok, dll)
```

---

## Modul Transaksi — Controller List (69)

| Area | Controller |
|---|---|
| **DOC (Bibit)** | ODVP, PenerimaanDocMobile, Rdim, Rdim_2_tanggal |
| **Pakan** | PenerimaanPakan, PenerimaanPakanMobile, PengirimanPakan, KirimPakan, ReturPakan, SPM, OrderPeralatan |
| **Obat/Vaksin** | PenerimaanVoadip, PenerimaanVoadipMobile, PengirimanVoadip, KirimVoadip, ReturVoadip, DistribusiVoadip |
| **Kandang** | LHK, LHK_WITH_PAKAN, LHK_without_peralatan, HarianKandang |
| **Panen** | KonfirmasiPanen, KonfirmasiPanenMobile, RealisasiSJ, RealisasiSjMobile, RPAH, RpahMobile, ApprovalRpah, BASTTB |
| **Keuangan** | KreditBank, KreditKendaraan, GajiKaryawan, PiutangKaryawan, PiutangMitra, SisaBayarPelanggan, BayarPenjualanPeralatan, PenjualanPeralatan |
| **Hutang/Piutang** | HutangUsaha, PiutangMitra, CreditNote, DebitNote, CnPembelian, CnPenjualan, DnPembelian, DnPenjualan, Posting |
| **Stock/Accounting** | HitungStok, AdjustmentInDoc, AdjustmentInPakan, AdjustmentInVoadip, AdjustmentOutPakan, AdjustmentOutVoadip, PindahBudidaya, UpdateHargaPakan, TSDRHPP, RhppGroup, PurchaseRequest |
| **Mobile** | PenerimaanDocMobile, PenerimaanPakanMobile, PenerimaanVoadipMobile, KonfirmasiPanenMobile, RealisasiSjMobile, RpahMobile |
| **Lain** | EstimasiChickInMingguan, OngkosAngkutPindahPakan, PengirimanPenerimaanOvk, PengirimanPenerimaanPakan, KPM |

---

## Database

- **SQL Server** via driver `sqlsrv`
- 3 koneksi: `default` (gmp_erp_live), `log` (log_history), `mgb` (mgb_erp_live)
- Stored procedure: `EXEC hitung_stok_siklus` — parameter: `tipe, sumber, id, tanggal, status, noreg`
- Query raw via `Conf::hydrateRaw($sql)` dan `Conf::runSp($sp, $bind)`
- ID generation: format `{kodeTable}{YY}{MM}{seq}` via `getNextId()`
