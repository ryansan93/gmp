# Pola Coding — GM ERP

## Controller

### Struktur Dasar

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class NamaController extends Public_Controller {
    private $pathView = 'module/nama_controller/';
    private $url;

    public function __construct() {
        parent::__construct();
        $this->url = $this->current_base_uri;
        // $this->url = '/module/NamaController'
    }

    // Page load
    public function index() {
        $akses = hakAkses($this->url);
        if ($akses['a_view'] == 1) {
            $this->add_external_js(['assets/module/nama/js/nama.js']);
            $this->add_external_css(['assets/module/nama/css/nama.css']);
            $data = $this->includes;
            $content['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $data['view'] = $content['view'];
            $this->load->view($this->template, $data);
        }
    }

    // AJAX endpoint
    public function getData() {
        $akses = hakAkses($this->url);
        if ($akses['a_view'] == 1) {
            try {
                $params = $this->input->post('params');
                $m = new \Model\Storage\SomeModel();
                $data = $m->where('id', $params['id'])->get()->toArray();
                $this->result['status'] = 1;
                $this->result['content'] = $data;
            } catch (\Exception $e) {
                $this->result['message'] = $e->getMessage();
            }
        }
        display_json($this->result);
    }

    // Save (add/edit)
    public function save() {
        $akses = hakAkses($this->url);
        $params = $this->input->post('params');
        $isNew = empty($params['id']);
        $aksesCheck = $isNew ? $akses['a_add'] : $akses['a_edit'];
        if ($aksesCheck == 1) {
            try {
                $m = new \Model\Storage\SomeModel();
                if ($isNew) {
                    $m->id = getNextId('prefix', 'table', 'id');
                    // fill fields
                    $m->save();
                } else {
                    $m->where('id', $params['id'])->update([...]);
                }
                // Insert jurnal otomatis
                $base = new \base\InsertJurnal();
                $base->exec($this->url, $m->id, null, $isNew ? 1 : 2);

                $this->result['status'] = 1;
                $this->result['message'] = 'Berhasil disimpan';
            } catch (\Exception $e) {
                $this->result['message'] = $e->getMessage();
            }
        }
        display_json($this->result);
    }

    // Delete
    public function delete() {
        $akses = hakAkses($this->url);
        if ($akses['a_delete'] == 1) {
            try {
                $params = $this->input->post('params');
                $m = new \Model\Storage\SomeModel();
                $m->where('id', $params['id'])->delete();
                $this->result['status'] = 1;
            } catch (\Exception $e) {
                $this->result['message'] = $e->getMessage();
            }
        }
        display_json($this->result);
    }
}
```

### Aturan Controller

| Aturan | Detail |
|---|---|
| Extends | Selalu `Public_Controller` (kecuali API: `API_Controller`) |
| Session check | `Public_Controller` sudah otomatis cek session |
| `$this->url` | Selalu set dari `$this->current_base_uri` di constructor |
| Hak akses | Setiap method wajib `hakAkses($this->url)` |
| Input AJAX | Selalu dari `$this->input->post('params')` |
| Output AJAX | Selalu `display_json($this->result)` |
| Error handling | Semua logic dalam `try/catch`, error ke `$this->result['message']` |

---

## Model (Eloquent)

### Struktur Dasar

```php
<?php
namespace Model\Storage;

class NamaEntitas_model extends Conf {
    protected $table = 'nama_tabel';
    protected $connection = 'default'; // atau 'log', 'mgb'
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'id';

    // Relasi hasMany
    public function detail() {
        return $this->hasMany('\Model\Storage\NamaEntitasDet_model', 'id_header', 'id');
    }

    // Relasi belongsTo
    public function kandang() {
        return $this->belongsTo('\Model\Storage\Kandang_model', 'id_kandang', 'id');
    }
}
```

### Helper ID Generation

```php
// Format: {KODE}{YY}{MM}{SEQ3digit}, contoh: LHK2505001
$id = getNextId('LHK', 'lhk', 'id');

// Format nomor dokumen
$nomor = getNextDocNum('SPM', 'pakan_spm', 'nomor_spm');
```

---

## JavaScript (Frontend)

### Struktur Singleton Object

```javascript
var namaModule = {

    // Inisialisasi saat DOM ready
    start_up: function() {
        namaModule.loadData();
        namaModule.bindEvents();
    },

    // Bind event listener
    bindEvents: function() {
        $(document).on('click', '#btn-save', function() {
            namaModule.save(this);
        });
    },

    // AJAX GET data
    loadData: function() {
        var params = {
            tgl_awal: $('#tgl_awal').val(),
            tgl_akhir: $('#tgl_akhir').val()
        };
        $.ajax({
            url: 'module/NamaController/getData',
            data: { 'params': params },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function() { showLoading(); },
            success: function(data) {
                hideLoading();
                if (data.status == 1) {
                    namaModule.renderTable(data.content);
                } else {
                    showError(data.message);
                }
            },
            error: function(xhr) {
                hideLoading();
                showError('Request gagal');
            }
        });
    },

    // Save / submit form
    save: function(elm) {
        var params = {
            id: $('#id').val(),
            nama: $('#nama').val()
        };
        $.ajax({
            url: 'module/NamaController/save',
            data: { 'params': params },
            type: 'POST',
            dataType: 'JSON',
            beforeSend: function() { showLoading(); },
            success: function(data) {
                hideLoading();
                if (data.status == 1) {
                    showSuccess(data.message || 'Berhasil');
                    namaModule.loadData();
                } else {
                    showError(data.message);
                }
            }
        });
    }
};

// Entry point
$(document).ready(function() {
    namaModule.start_up();
});
```

### Aturan JavaScript

| Aturan | Detail |
|---|---|
| Pattern | Singleton object global `var x = {...}` |
| Self-reference | Gunakan nama object (`namaModule.method()`) bukan `this` |
| AJAX URL | Format: `'module/Controller/method'` (relative) |
| AJAX data | Selalu `{ 'params': {...} }` |
| Response check | `if (data.status == 1)` untuk sukses |
| Loading | `showLoading()` / `hideLoading()` di beforeSend/success |
| No module bundler | Tidak ada webpack/rollup; semua global script |
| No ES6 class | Tidak ada `class`, `import`, arrow function (kompatibilitas) |

---

## AJAX Response Convention

```json
{
    "status": 1,
    "message": "Berhasil disimpan",
    "content": { ... }
}
```

- **Sukses**: `status = 1`, `content` berisi data
- **Error**: `status` tidak di-set (atau 0), `message` berisi pesan error
- Client selalu cek: `if (data.status == 1)`

---

## Hak Akses

```php
$akses = hakAkses($this->url);
// $akses['a_view']   = 1 → boleh lihat
// $akses['a_add']    = 1 → boleh tambah
// $akses['a_edit']   = 1 → boleh edit
// $akses['a_delete'] = 1 → boleh hapus
// $akses['a_print']  = 1 → boleh cetak
```

`hakAkses()` ada di `application/helpers/core_helper.php`.  
Akses dibaca dari tabel `det_group` dan `akses_khusus` berdasarkan user session.

---

## Export Excel

```php
// Dari controller, panggil ExportExcel di modul base
$base = new \base\ExportExcel();
$base->export($data, $headers, $filename);
```

Export Excel menggunakan **PhpSpreadsheet** (`phpoffice/phpspreadsheet`).

---

## Export PDF

```php
// Menggunakan library Pdf (wrapper TCPDF)
$this->load->library('Pdf');
$pdf = new Pdf();
$pdf->writeHTML($html);
$pdf->Output('filename.pdf', 'D');
```

---

## Insert Jurnal Otomatis

Jurnal akuntansi di-generate otomatis setelah save transaksi:

```php
$base = new \base\InsertJurnal();
$base->exec(
    $this->url,   // path URL controller (dipakai lookup setting_automatic_jurnal)
    $id,          // ID record baru
    $id_old,      // ID record lama (null jika tambah)
    $action,      // 1=tambah, 2=edit, 3=hapus
    $table,       // nama tabel (opsional)
    $tgl_trans    // tanggal transaksi (opsional)
);
```

Konfigurasi mapping jurnal ada di tabel `setting_automatic_jurnal` dan `setting_automatic_jurnal_det`.

---

## Stored Procedure

```php
$m = new \Model\Storage\Conf();
$result = $m->runSp("EXEC hitung_stok_siklus @tipe=?, @sumber=?, @id=?, @tanggal=?, @status=?, @noreg=?", [
    $tipe, $sumber, $id, $tanggal, $status, $noreg
]);
```

---

## Konvensi Penamaan

| Komponen | Format | Contoh |
|---|---|---|
| Controller | PascalCase | `PenerimaanPakan.php` |
| Model | PascalCase + `_model` | `PenerimaanPakan_model.php` |
| View folder | snake_case | `penerimaan_pakan/` |
| View file | snake_case | `index.php`, `addForm.php`, `editForm.php` |
| JS file | kebab-case | `penerimaan-pakan.js` |
| CSS file | kebab-case | `penerimaan-pakan.css` |
| JS object | camelCase | `var penerimaanPakan = {...}` |
| DB tabel | snake_case | `penerimaan_pakan` |
| ID record | `{KODE}{YY}{MM}{SEQ}` | `PKN2505001` |
