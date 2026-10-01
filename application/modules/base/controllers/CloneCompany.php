<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Clone Perusahaan
 *
 * Fitur admin utk membuat instance database baru (perusahaan lain) dari
 * database GM ERP saat ini. TIDAK memakai BACKUP/RESTORE (riwayat: cara lama
 * berulang kali macet parah krn masalah I/O storage laten di server produksi,
 * bisa sampai puluhan menit, terlepas dari ukuran database - lihat memory
 * project utk detail). Sebagai gantinya:
 *   1) CREATE DATABASE baru KOSONG lalu replikasi SCHEMA-nya saja dari sumber
 *      (tabel, primary key, index, default constraint, stored procedure,
 *      fungsi - lewat query metadata sys.*, BUKAN operasi I/O berat).
 *   2) INSERT data cuma utk tabel master/config yg BENERAN dibutuhkan (daftar
 *      tetap di insertMasterData()), dgn ID/PK identik ke sumber lewat
 *      SET IDENTITY_INSERT per tabel.
 *   3) Foreign key (di schema live cuma ada 3: unit_karyawan->karyawan,
 *      detail_user->ms_group, detail_user->ms_user) BARU dibuat setelah semua
 *      data ter-insert - jadi urutan insert antar tabel bebas & tidak perlu
 *      NOCHECK/re-enable constraint sama sekali.
 * Tabel lain (semua tabel transaksi, pelanggan/mitra/ekspedisi/karyawan/dst)
 * otomatis KOSONG dari awal (schema-only, tanpa data) - TIDAK perlu TRUNCATE
 * krn memang tidak pernah diisi. pelanggan/mitra/ekspedisi dkk rencananya
 * dapat tombol clone terpisah di kemudian hari (di luar scope fitur ini).
 *
 * Database sumber/live TIDAK PERNAH ditulis oleh controller ini - semua query
 * ke sumber (lewat $DBSource) murni SELECT/metadata.
 *
 * Akses HARUS dibatasi hanya ke grup Super Admin lewat Master Fitur/Group
 * (path_detfitur = 'base/CloneCompany') karena endpoint ini menjalankan DDL
 * (CREATE DATABASE/CREATE TABLE/ALTER TABLE) langsung ke SQL Server.
 */
class CloneCompany extends Public_Controller {

    private $pathView = 'base/clone_company/';
    private $url;
    private $hakAkses;

    // Nama database LIVE - tidak boleh pernah jadi target clone/wipe.
    private $reservedDbNames = array('gmp_erp_live', 'log_history_gmp_erp_live', 'mgb_erp_live');

    // Tabel master/config yg di-INSERT full (ID/PK/FK identik ke sumber) saat clone
    // database utama. Disepakati manual bersama pemilik bisnis - lihat plan clone
    // perusahaan utk riwayat pembahasan. detail_user/ms_user/log_tables/perusahaan
    // ditangani terpisah (filtered/khusus) di insertMasterData().
    private $fullCopyTables = array(
        'akses_khusus', 'barang', 'coa', 'det_standart_budidaya', 'detail_fitur',
        'detail_group', 'detail_menu', 'ekspedisi_pph23', 'gudang', 'jabatan',
        'jabatan_atasan', 'jenis', 'jurnal_trans', 'jurnal_trans_fitur', 'det_jurnal_trans',
        'master_badan_usaha', 'ms_fitur', 'ms_group', 'ms_jenis_sewa', 'ms_kategori_supplier',
        'ms_menu', 'nama_lampiran', 'nekropsi', 'pola_kerjasama', 'potongan_pajak',
        'setting_automatic_jurnal', 'setting_automatic_jurnal_det', 'setting_report',
        'setting_report_group', 'setting_report_group_item', 'solusi', 'standart_budidaya',
        'tipe_pelanggan', 'vaksin', 'wilayah',
    );

    // Grup yg dianggap "administrator" - cuma user di grup ini yg ikut di-insert ke
    // detail_user/ms_user (disepakati manual bersama pemilik bisnis).
    private $adminGroupIds = array('GRP1901001', 'GRP1901002', 'GRP1901003'); // ROOT, BPM1, BPM2

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->hakAkses = hakAkses($this->url);
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/

    public function index()
    {
        // if ($this->hakAkses['a_view'] == 1) {
            $this->add_external_js(array(
                "assets/base/clone_company/js/clone-company.js",
            ));

            $content['akses'] = $this->hakAkses;
            $content['title_panel'] = 'Clone Perusahaan';

            $data = $this->includes;
            $data['title_menu'] = 'Clone Perusahaan';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);
            $this->load->view($this->template, $data);
        // } else {
        //     showErrorAkses();
        // }
    }

    /**
     * STEP 1 - Validasi nama database baru sebelum proses dimulai.
     */
    public function validasi()
    {
        // if ($this->hakAkses['a_submit'] != 1) { showErrorAkses(); return; }

        $params = $this->input->post('params');
        $dbBaru = trim($params['db_baru']);

        try {
            $this->assertNamaDbValid($dbBaru);

            $DBMaster = $this->connectAdHoc('default', 'master');
            $existing = $this->runQuery($DBMaster, "SELECT name FROM sys.databases WHERE name = ?", array($dbBaru))->result_array();

            if (count($existing) > 0) {
                throw new Exception("Database '{$dbBaru}' sudah ada di server. Pilih nama lain.");
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Validasi berhasil, nama database aman dipakai.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json($this->result);
    }

    /**
     * STEP 2 - CREATE DATABASE baru + replikasi SCHEMA saja dari sumber (tabel,
     * primary key, index, default constraint, stored procedure, fungsi) - TANPA
     * data & TANPA foreign key (FK dibuat belakangan di createForeignKeys(),
     * setelah insertMasterData() selesai).
     * params: sumber ('default'|'log'), db_baru
     */
    public function cloneSchema()
    {
        // if ($this->hakAkses['a_submit'] != 1) { showErrorAkses(); return; }

        $params = $this->input->post('params');
        $sumber = isset($params['sumber']) ? $params['sumber'] : null;
        $dbBaruRaw = trim($params['db_baru']);

        try {
            if (!in_array($sumber, array('default', 'log'))) {
                throw new Exception('Parameter sumber tidak valid.');
            }

            $env = $this->config->item('connection');
            $sourceDb = $env[$sumber]['database'];
            $targetDb = ($sumber == 'log') ? ('log_history_' . $dbBaruRaw) : $dbBaruRaw;
            $this->assertNamaDbValid($targetDb);

            $DBMaster = $this->connectAdHoc($sumber, 'master');

            // Tolak kalau target sudah terdaftar sbg database aktif - cegah menimpa
            // clone yg sudah ada/dipakai. Kalau memang mau clone ulang, DROP dulu
            // database lama secara sadar sebelum lanjut.
            $existing = $this->runQuery($DBMaster, "SELECT name FROM sys.databases WHERE name = ?", array($targetDb))->result_array();
            if (!empty($existing)) {
                throw new Exception("Database [{$targetDb}] sudah ada. Pilih nama lain, atau DROP database tersebut dulu kalau memang mau clone ulang.");
            }

            $this->runQuery($DBMaster, "CREATE DATABASE [{$targetDb}]");

            $DBSource = $this->connectAdHoc($sumber, $sourceDb);
            $DBTarget = $this->connectAdHoc($sumber, $targetDb);

            $this->cloneTablesSchema($DBSource, $DBTarget);
            $failedObjects = $this->cloneProceduresAndFunctions($DBSource, $DBTarget);

            $this->result['status'] = 1;
            $this->result['message'] = "Schema database [{$targetDb}] berhasil dibuat dari struktur [{$sourceDb}] (tanpa data)."
                . (!empty($failedObjects) ? ' PERINGATAN, gagal replikasi: ' . implode('; ', $failedObjects) : '');
            $this->result['content'] = array('target_db' => $targetDb);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json($this->result);
    }

    /**
     * STEP 3 - Insert data master/config yg dibutuhkan (lihat $fullCopyTables +
     * penanganan khusus detail_user/ms_user/log_tables/perusahaan) dari database
     * sumber ke database HASIL CLONE, dgn ID/PK identik (lewat IDENTITY_INSERT per
     * tabel). Cuma dipanggil utk sumber 'default' (log_history tetap kosong -
     * audit trail baru dimulai fresh utk perusahaan baru).
     */
    public function insertMasterData()
    {
        // if ($this->hakAkses['a_submit'] != 1) { showErrorAkses(); return; }

        $params = $this->input->post('params');
        $dbBaru = trim($params['db_baru']);

        try {
            $this->assertNamaDbValid($dbBaru);

            $env = $this->config->item('connection');
            $sourceDb = $env['default']['database'];

            $DBSource = $this->connectAdHoc('default', $sourceDb);
            $DBTarget = $this->connectAdHoc('default', $dbBaru);

            $inserted = array();

            foreach ($this->fullCopyTables as $tbl) {
                $inserted[$tbl] = $this->insertTableData($DBSource, $DBTarget, $sourceDb, $tbl);
            }

            $adminPlaceholders = implode(',', array_fill(0, count($this->adminGroupIds), '?'));

            // detail_user: cuma grup administrator (ROOT/BPM1/BPM2).
            $inserted['detail_user'] = $this->insertTableData(
                $DBSource, $DBTarget, $sourceDb, 'detail_user',
                "id_group IN ({$adminPlaceholders})", $this->adminGroupIds
            );

            // ms_user: cuma id_user yg muncul di detail_user administrator di atas.
            $inserted['ms_user'] = $this->insertTableData(
                $DBSource, $DBTarget, $sourceDb, 'ms_user',
                "id_user IN (SELECT id_user FROM [{$sourceDb}].dbo.[detail_user] WHERE id_group IN ({$adminPlaceholders}))",
                $this->adminGroupIds
            );

            // log_tables: cuma entri terkait tabel2 yg ikut di-insert (bukan seluruh
            // histori audit, yg jumlahnya ratusan ribu baris & tidak relevan utk
            // perusahaan baru).
            $logScope = array_merge($this->fullCopyTables, array('detail_user', 'ms_user'));
            $logPlaceholders = implode(',', array_fill(0, count($logScope), '?'));
            $inserted['log_tables'] = $this->insertTableData(
                $DBSource, $DBTarget, $sourceDb, 'log_tables',
                "tbl_name IN ({$logPlaceholders})", $logScope
            );

            // perusahaan: full copy semua baris (banyak entitas legal/CV/PT per
            // cabang) - baris utama di-rename ke nama perusahaan baru lewat step
            // terpisah updatePerusahaan(), sisanya diedit manual lewat menu
            // Parameter > PKW.
            $inserted['perusahaan'] = $this->insertTableData($DBSource, $DBTarget, $sourceDb, 'perusahaan');

            $this->result['status'] = 1;
            $this->result['message'] = count($inserted) . ' tabel master berhasil di-insert (' . array_sum($inserted) . ' baris total).';
            $this->result['content'] = array('inserted' => $inserted);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json($this->result);
    }

    /**
     * STEP 4 - Buat foreign key constraint di database HASIL CLONE, dibaca dinamis
     * dari definisi FK di database SUMBER (bukan hardcode) - dijalankan PALING
     * TERAKHIR setelah insertMasterData() selesai, supaya urutan insert antar
     * tabel di step sebelumnya bebas (tidak ada FK aktif yg perlu dijaga urutannya
     * saat itu) & tidak perlu NOCHECK/re-enable constraint sama sekali.
     */
    public function createForeignKeys()
    {
        // if ($this->hakAkses['a_submit'] != 1) { showErrorAkses(); return; }

        $params = $this->input->post('params');
        $dbBaru = trim($params['db_baru']);

        try {
            $this->assertNamaDbValid($dbBaru);

            $env = $this->config->item('connection');
            $sourceDb = $env['default']['database'];

            $DBSource = $this->connectAdHoc('default', $sourceDb);
            $DBTarget = $this->connectAdHoc('default', $dbBaru);

            $rows = $this->runQuery($DBSource, "
                SELECT fk.name AS fk_name, OBJECT_NAME(fk.parent_object_id) AS child_tbl,
                       OBJECT_NAME(fk.referenced_object_id) AS parent_tbl,
                       pc.name AS child_col, rc.name AS parent_col, fkc.constraint_column_id
                FROM sys.foreign_keys fk
                JOIN sys.foreign_key_columns fkc ON fkc.constraint_object_id = fk.object_id
                JOIN sys.columns pc ON pc.object_id = fkc.parent_object_id AND pc.column_id = fkc.parent_column_id
                JOIN sys.columns rc ON rc.object_id = fkc.referenced_object_id AND rc.column_id = fkc.referenced_column_id
                ORDER BY fk.name, fkc.constraint_column_id
            ")->result_array();

            $fks = array();
            foreach ($rows as $r) {
                $name = $r['fk_name'];
                if (!isset($fks[$name])) {
                    $fks[$name] = array('child_tbl' => $r['child_tbl'], 'parent_tbl' => $r['parent_tbl'], 'child_cols' => array(), 'parent_cols' => array());
                }
                $fks[$name]['child_cols'][] = $r['child_col'];
                $fks[$name]['parent_cols'][] = $r['parent_col'];
            }

            $created = array();
            $skipped = array();
            foreach ($fks as $fkName => $fk) {
                $childCols = '[' . implode('], [', $fk['child_cols']) . ']';
                $parentCols = '[' . implode('], [', $fk['parent_cols']) . ']';
                try {
                    $this->runQuery($DBTarget, "ALTER TABLE [{$fk['child_tbl']}] ADD CONSTRAINT [{$fkName}] FOREIGN KEY ({$childCols}) REFERENCES [{$fk['parent_tbl']}]({$parentCols})");
                    $created[] = $fkName;
                } catch (Exception $eFk) {
                    $skipped[] = $fkName . ': ' . $eFk->getMessage();
                }
            }

            $this->result['status'] = 1;
            $this->result['message'] = count($created) . ' foreign key berhasil dibuat.'
                . (!empty($skipped) ? ' Gagal: ' . implode('; ', $skipped) : '');
            $this->result['content'] = array('created' => $created, 'skipped' => $skipped);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json($this->result);
    }

    /**
     * STEP 5 - Update nama perusahaan utama di database hasil clone (baris
     * `perusahaan` sudah ter-insert lewat insertMasterData() - step ini cuma
     * me-rename baris pertama). Entitas legal lain (banyak CV/PT per cabang)
     * tetap dipertahankan apa adanya & diedit manual lewat menu Parameter > PKW
     * setelah clone selesai.
     */
    public function updatePerusahaan()
    {
        // if ($this->hakAkses['a_submit'] != 1) { showErrorAkses(); return; }

        $params = $this->input->post('params');
        $dbBaru = trim($params['db_baru']);
        $namaBaru = trim($params['nama_perusahaan']);

        try {
            $this->assertNamaDbValid($dbBaru);
            if (empty($namaBaru)) {
                throw new Exception('Nama perusahaan baru wajib diisi.');
            }

            $DBBaru = $this->connectAdHoc('default', $dbBaru);
            $first = $this->runQuery($DBBaru, "SELECT TOP 1 id FROM perusahaan ORDER BY id ASC")->row_array();

            if (!empty($first)) {
                $this->runQuery($DBBaru, "UPDATE perusahaan SET perusahaan = ? WHERE id = ?", array($namaBaru, $first['id']));
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Nama perusahaan utama berhasil diperbarui. Entitas legal lainnya bisa diedit lewat menu Parameter > PKW.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json($this->result);
    }

    /**
     * STEP 6 - Clone folder aplikasi ke direktori baru (sibling dari folder aplikasi
     * ini) lewat robocopy, lalu otomatis sesuaikan env.php (nama DB) & company.php
     * (nama perusahaan) DI FOLDER BARU - folder aplikasi sumber tidak pernah ditulis.
     *
     * Target folder dibatasi HANYA nama folder (bukan path bebas) dan HANYA boleh
     * dibuat sebagai sibling langsung dari folder aplikasi ini (mis. di dalam
     * htdocs yang sama) - mencegah path traversal / penulisan ke lokasi
     * sembarangan di filesystem. Setup web server (vhost/document root) TETAP
     * langkah manual - di luar scope fitur ini.
     */
    public function cloneFolder()
    {
        // if ($this->hakAkses['a_submit'] != 1) { showErrorAkses(); return; }

        $params = $this->input->post('params');
        $targetFolderName = trim($params['target_folder_name']);
        $dbBaru = trim($params['db_baru']);
        $namaBaru = trim($params['nama_perusahaan']);

        try {
            set_time_limit(0);

            if (empty($targetFolderName) || !preg_match('/^[A-Za-z0-9_\-]{1,60}$/', $targetFolderName)) {
                throw new Exception('Nama folder tujuan hanya boleh huruf, angka, underscore, strip, maksimal 60 karakter (bukan path lengkap).');
            }
            $this->assertNamaDbValid($dbBaru);
            if (empty($namaBaru)) {
                throw new Exception('Nama perusahaan baru wajib diisi.');
            }

            $sourceRoot = rtrim(FCPATH, '/\\');
            $parentDir = dirname($sourceRoot);
            $targetRoot = $parentDir . DIRECTORY_SEPARATOR . $targetFolderName;

            if (strcasecmp($targetRoot, $sourceRoot) == 0) {
                throw new Exception('Nama folder tujuan tidak boleh sama dengan folder aplikasi ini.');
            }
            if (is_dir($targetRoot) || file_exists($targetRoot)) {
                throw new Exception("Folder '{$targetRoot}' sudah ada. Pilih nama lain.");
            }

            // Copy folder pakai robocopy (Windows) - jauh lebih cepat & andal drpd copy
            // manual per-file lewat PHP untuk codebase sebesar ini (termasuk vendor/).
            // /XD kecualikan folder upload - berisi histori file (lampiran KTP, dokumen
            // import, dst) milik perusahaan SUMBER yg tabel referensinya (lampiran/
            // log_lampiran) sengaja TIDAK ikut di-insert ke database hasil clone (selalu
            // kosong utk perusahaan baru) - jadi file2 itu jadi "yatim" kalau ikut
            // di-copy: sia-sia (bisa GB-an, penyebab robocopy pernah lambat/macet) &
            // resiko privasi (dokumen sensitif perusahaan lama nempel fisik di instance
            // baru walau tidak pernah muncul di aplikasi).
            $excludeDirs = array(
                $sourceRoot . '\\uploads',
                $sourceRoot . '\\assets\\image\\real_image',
            );
            $xdArgs = '';
            foreach ($excludeDirs as $dir) {
                $xdArgs .= ' ' . escapeshellarg($dir);
            }
            $cmd = 'robocopy ' . escapeshellarg($sourceRoot) . ' ' . escapeshellarg($targetRoot) . ' /E /MT:16 /NFL /NDL /NJH /NJS /NP /XD' . $xdArgs . ' 2>&1';
            exec($cmd, $output, $exitCode);

            // Robocopy: exit code 0-7 = sukses (berbagai kombinasi file disalin/dilewati),
            // >=8 berarti ada error nyata (lihat https://ss64.com/nt/robocopy-exit.html).
            if ($exitCode >= 8) {
                throw new Exception('Robocopy gagal (exit code ' . $exitCode . '). Output terakhir: ' . implode(' | ', array_slice($output, -10)));
            }

            // Folder yg di-XD di atas TIDAK ikut dibuat sama sekali oleh robocopy - buat
            // ulang kosong di folder baru supaya upload berikutnya (perusahaan baru) tetap
            // punya folder tujuan yg valid, bukan folder hilang.
            foreach ($excludeDirs as $dir) {
                $relative = substr($dir, strlen($sourceRoot));
                $newDir = $targetRoot . $relative;
                if (!is_dir($newDir)) {
                    mkdir($newDir, 0755, true);
                }
            }

            // Update env.php DI FOLDER BARU saja - ganti nama database ke hasil clone.
            $envPath = $targetRoot . '/application/config/env.php';
            if (!file_exists($envPath)) {
                throw new Exception('Copy folder tampak tidak lengkap - env.php tidak ditemukan di folder baru.');
            }
            $envContent = file_get_contents($envPath);
            $envContent = str_replace("'gmp_erp_live'", "'{$dbBaru}'", $envContent);
            $envContent = str_replace("'log_history_gmp_erp_live'", "'log_history_{$dbBaru}'", $envContent);
            file_put_contents($envPath, $envContent);

            // Update company.php DI FOLDER BARU - ganti nama perusahaan.
            $companyPath = $targetRoot . '/application/config/company.php';
            if (file_exists($companyPath)) {
                $namaBaruEscaped = addslashes($namaBaru);
                $companyContent = file_get_contents($companyPath);
                $companyContent = preg_replace("/(\\\$config\['company_name'\]\s*=\s*)'[^']*'/", "$1'{$namaBaruEscaped}'", $companyContent);
                $companyContent = preg_replace("/(\\\$config\['login_heading'\]\s*=\s*)'[^']*'/", "$1'{$namaBaruEscaped}'", $companyContent);
                $companyContent = preg_replace("/(\\\$config\['judul_aplikasi'\]\s*=\s*)'[^']*'/", "$1'{$namaBaruEscaped}'", $companyContent);
                file_put_contents($companyPath, $companyContent);
            }

            $this->result['status'] = 1;
            $this->result['message'] = "Folder aplikasi berhasil di-clone ke [{$targetRoot}]. env.php & company.php di folder baru sudah otomatis disesuaikan. Folder uploads/ (dokumen lama) SENGAJA tidak ikut di-copy - dibuatkan folder kosong baru. Sisa langkah manual: ganti logo (kalau ada) & setup document root/virtual host web server.";
            $this->result['content'] = array('target_root' => $targetRoot);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json($this->result);
    }

    /**************************************************************************************
     * PRIVATE HELPERS
     **************************************************************************************/

    private function assertNamaDbValid($nama)
    {
        if (empty($nama) || !preg_match('/^[A-Za-z0-9_]{1,60}$/', $nama)) {
            throw new Exception('Nama database hanya boleh huruf, angka, underscore, maksimal 60 karakter.');
        }
        if (in_array(strtolower($nama), array_map('strtolower', $this->reservedDbNames))) {
            throw new Exception("Nama database '{$nama}' adalah database LIVE, tidak boleh dipakai sebagai target clone/wipe.");
        }
        return $nama;
    }

    /**
     * Jalankan query lewat koneksi ad-hoc & lempar Exception (pesan error SQL Server
     * asli) kalau gagal. WAJIB dipakai utk semua query lewat connectAdHoc() - koneksi
     * itu sengaja db_debug=FALSE (beda dari koneksi normal CI di app ini yang
     * db_debug=TRUE) supaya kegagalan query menghasilkan pesan JSON yang rapi lewat
     * try/catch, bukan CI menghentikan request dgn halaman HTML error - yang bikin
     * response AJAX gagal di-parse sbg JSON (muncul sbg "request error" generik di
     * JS, pesan SQL asli jadi tidak kelihatan).
     */
    private function runQuery($DB, $sql, $binds = array())
    {
        $result = $DB->query($sql, $binds);

        if ($result === false) {
            $err = $DB->error();
            $pesan = !empty($err['message']) ? $err['message'] : 'Query gagal tanpa pesan error dari driver.';
            throw new Exception('SQL Server error: ' . $pesan);
        }

        return $result;
    }

    /**
     * Replikasi SEMUA tabel di $DBSource ke $DBTarget: kolom (termasuk computed
     * column), primary key, index non-PK, default constraint. TIDAK termasuk
     * foreign key (lihat createForeignKeys()) krn sengaja ditunda ke setelah data
     * ter-insert. TIDAK termasuk view/trigger/check constraint krn schema live
     * saat ini tidak punya itu semua (dicek manual via sys.views/sys.triggers/
     * sys.check_constraints - kalau nanti ada, perlu ekstensi di sini).
     */
    private function cloneTablesSchema($DBSource, $DBTarget)
    {
        $tables = $this->runQuery($DBSource, "SELECT name FROM sys.tables ORDER BY name")->result_array();

        foreach ($tables as $t) {
            // Nama tabel berasal dari sys.tables (bukan input pengguna), aman diinterpolasi.
            $tbl = $t['name'];

            $cols = $this->runQuery($DBSource, "
                SELECT c.name, ty.name AS type_name, c.max_length, c.precision, c.scale,
                       c.is_nullable, c.is_identity, c.is_computed,
                       ISNULL(ic.seed_value, 1) AS seed_value, ISNULL(ic.increment_value, 1) AS increment_value,
                       cc.definition AS computed_definition
                FROM sys.columns c
                JOIN sys.types ty ON c.user_type_id = ty.user_type_id
                LEFT JOIN sys.identity_columns ic ON ic.object_id = c.object_id AND ic.column_id = c.column_id
                LEFT JOIN sys.computed_columns cc ON cc.object_id = c.object_id AND cc.column_id = c.column_id
                WHERE c.object_id = OBJECT_ID(?)
                ORDER BY c.column_id
            ", array($tbl))->result_array();

            if (empty($cols)) {
                continue;
            }

            $colDefs = array();
            foreach ($cols as $c) {
                $colDefs[] = $this->buildColumnDdl($c);
            }

            $this->runQuery($DBTarget, "CREATE TABLE [{$tbl}] (" . implode(', ', $colDefs) . ")");

            $this->addPrimaryKey($DBSource, $DBTarget, $tbl);
            $this->addIndexes($DBSource, $DBTarget, $tbl);
            $this->addDefaultConstraints($DBSource, $DBTarget, $tbl);
        }
    }

    private function addPrimaryKey($DBSource, $DBTarget, $tbl)
    {
        $rows = $this->runQuery($DBSource, "
            SELECT kc.name AS pk_name, i.type_desc, col.name AS col_name, ic.is_descending_key
            FROM sys.key_constraints kc
            JOIN sys.indexes i ON kc.parent_object_id = i.object_id AND kc.unique_index_id = i.index_id
            JOIN sys.index_columns ic ON ic.object_id = i.object_id AND ic.index_id = i.index_id
            JOIN sys.columns col ON col.object_id = ic.object_id AND col.column_id = ic.column_id
            WHERE kc.type = 'PK' AND kc.parent_object_id = OBJECT_ID(?)
            ORDER BY ic.key_ordinal
        ", array($tbl))->result_array();

        if (empty($rows)) {
            return;
        }

        $pkName = $rows[0]['pk_name'];
        $clustered = ($rows[0]['type_desc'] == 'CLUSTERED') ? 'CLUSTERED' : 'NONCLUSTERED';
        $colParts = array();
        foreach ($rows as $r) {
            $colParts[] = "[{$r['col_name']}]" . (!empty($r['is_descending_key']) ? ' DESC' : ' ASC');
        }

        $this->runQuery($DBTarget, "ALTER TABLE [{$tbl}] ADD CONSTRAINT [{$pkName}] PRIMARY KEY {$clustered} (" . implode(', ', $colParts) . ")");
    }

    private function addIndexes($DBSource, $DBTarget, $tbl)
    {
        $rows = $this->runQuery($DBSource, "
            SELECT i.name AS idx_name, i.type_desc, i.is_unique, col.name AS col_name,
                   ic.key_ordinal, ic.is_descending_key, ic.is_included_column
            FROM sys.indexes i
            JOIN sys.index_columns ic ON ic.object_id = i.object_id AND ic.index_id = i.index_id
            JOIN sys.columns col ON col.object_id = ic.object_id AND col.column_id = ic.column_id
            WHERE i.object_id = OBJECT_ID(?) AND i.is_primary_key = 0 AND i.name IS NOT NULL AND i.type IN (1,2)
            ORDER BY i.name, ic.is_included_column, ic.key_ordinal, ic.index_column_id
        ", array($tbl))->result_array();

        $indexes = array();
        foreach ($rows as $r) {
            $name = $r['idx_name'];
            if (!isset($indexes[$name])) {
                $indexes[$name] = array('type_desc' => $r['type_desc'], 'is_unique' => $r['is_unique'], 'key_cols' => array(), 'include_cols' => array());
            }
            if (!empty($r['is_included_column'])) {
                $indexes[$name]['include_cols'][] = "[{$r['col_name']}]";
            } else {
                $indexes[$name]['key_cols'][] = "[{$r['col_name']}]" . (!empty($r['is_descending_key']) ? ' DESC' : ' ASC');
            }
        }

        foreach ($indexes as $name => $idx) {
            if (empty($idx['key_cols'])) {
                continue;
            }
            $unique = !empty($idx['is_unique']) ? 'UNIQUE ' : '';
            $clustered = ($idx['type_desc'] == 'CLUSTERED') ? 'CLUSTERED' : 'NONCLUSTERED';
            $sql = "CREATE {$unique}{$clustered} INDEX [{$name}] ON [{$tbl}] (" . implode(', ', $idx['key_cols']) . ")";
            if (!empty($idx['include_cols'])) {
                $sql .= " INCLUDE (" . implode(', ', $idx['include_cols']) . ")";
            }
            $this->runQuery($DBTarget, $sql);
        }
    }

    private function addDefaultConstraints($DBSource, $DBTarget, $tbl)
    {
        $rows = $this->runQuery($DBSource, "
            SELECT dc.name AS dc_name, col.name AS col_name, dc.definition
            FROM sys.default_constraints dc
            JOIN sys.columns col ON col.object_id = dc.parent_object_id AND col.column_id = dc.parent_column_id
            WHERE dc.parent_object_id = OBJECT_ID(?)
        ", array($tbl))->result_array();

        foreach ($rows as $r) {
            $this->runQuery($DBTarget, "ALTER TABLE [{$tbl}] ADD CONSTRAINT [{$r['dc_name']}] DEFAULT {$r['definition']} FOR [{$r['col_name']}]");
        }
    }

    /**
     * Bangun 1 definisi kolom DDL dari baris sys.columns/sys.types (dipakai oleh
     * cloneTablesSchema()). Kolom computed diberi definisi `AS (<formula>)`, tidak
     * pakai tipe/panjang/identity spt kolom biasa.
     */
    private function buildColumnDdl($c)
    {
        if (!empty($c['is_computed'])) {
            return "[{$c['name']}] AS ({$c['computed_definition']})";
        }

        $type = strtolower($c['type_name']);
        $sized = array('varchar', 'nvarchar', 'char', 'nchar', 'varbinary', 'binary');
        $decimalTypes = array('decimal', 'numeric');

        $def = "[{$c['name']}] {$type}";

        if (in_array($type, $sized, true)) {
            $len = (int) $c['max_length'];
            if ($len == -1) {
                $def .= '(MAX)';
            } else {
                $isWide = in_array($type, array('nvarchar', 'nchar'), true);
                $def .= '(' . ($isWide ? $len / 2 : $len) . ')';
            }
        } elseif (in_array($type, $decimalTypes, true)) {
            $def .= "({$c['precision']},{$c['scale']})";
        }

        if (!empty($c['is_identity'])) {
            $def .= " IDENTITY({$c['seed_value']},{$c['increment_value']})";
        }

        $def .= $c['is_nullable'] ? ' NULL' : ' NOT NULL';

        return $def;
    }

    /**
     * Replay stored procedure & function dari $DBSource ke $DBTarget verbatim
     * (OBJECT_DEFINITION - sudah dicek manual tidak ada referensi cross-database/
     * USE statement). SET ANSI_NULLS/QUOTED_IDENTIFIER ON dulu krn definisi asli
     * biasanya dibuat dgn asumsi itu (default SSMS), tidak ikut kebawa di teks
     * OBJECT_DEFINITION. Urutan bebas - SQL Server men-defer name resolution di
     * badan SP/fungsi. Gagal per-objek TIDAK menghentikan proses (dikumpulkan &
     * dilaporkan sbg peringatan) krn ini bukan penghalang tabel/data utama.
     * Return: array pesan gagal (kosong kalau semua berhasil).
     */
    private function cloneProceduresAndFunctions($DBSource, $DBTarget)
    {
        $procs = $this->runQuery($DBSource, "SELECT name, OBJECT_DEFINITION(object_id) AS def FROM sys.procedures")->result_array();
        $funcs = $this->runQuery($DBSource, "SELECT name, OBJECT_DEFINITION(object_id) AS def FROM sys.objects WHERE type IN ('FN','IF','TF')")->result_array();

        $failed = array();
        foreach (array_merge($procs, $funcs) as $o) {
            if (empty($o['def'])) {
                continue;
            }
            try {
                $this->runQuery($DBTarget, "SET ANSI_NULLS ON");
                $this->runQuery($DBTarget, "SET QUOTED_IDENTIFIER ON");
                $this->runQuery($DBTarget, $o['def']);
            } catch (Exception $eObj) {
                $failed[] = $o['name'] . ': ' . $eObj->getMessage();
            }
        }

        return $failed;
    }

    /**
     * Insert data 1 tabel dari $sourceDb (via $DBSource, koneksi cuma dipakai baca
     * metadata kolom) ke tabel yg sama di $DBTarget, dgn kolom eksplisit (exclude
     * computed column) & SET IDENTITY_INSERT otomatis kalau tabel itu py identity
     * column - supaya ID sama persis dgn sumber. $whereSql (opsional) memfilter
     * baris yg di-insert (dgn $whereBinds sbg parameter bind, dipakai lagi krn
     * query SELECT-nya cross-database jadi butuh bind terpisah dari $DBTarget).
     * Return: jumlah baris ter-insert.
     */
    private function insertTableData($DBSource, $DBTarget, $sourceDb, $tbl, $whereSql = null, $whereBinds = array())
    {
        $cols = $this->runQuery($DBSource, "
            SELECT c.name, c.is_identity, c.is_computed
            FROM sys.columns c
            WHERE c.object_id = OBJECT_ID(?)
            ORDER BY c.column_id
        ", array($tbl))->result_array();

        $insertCols = array();
        $hasIdentity = false;
        foreach ($cols as $c) {
            if (!empty($c['is_computed'])) {
                continue;
            }
            $insertCols[] = $c['name'];
            if (!empty($c['is_identity'])) {
                $hasIdentity = true;
            }
        }

        if (empty($insertCols)) {
            return 0;
        }

        $colList = '[' . implode('], [', $insertCols) . ']';
        // Nama tabel/kolom berasal dari sys.columns (bukan input pengguna), aman diinterpolasi.
        $insertSql = "INSERT INTO [{$tbl}] ({$colList}) SELECT {$colList} FROM [{$sourceDb}].dbo.[{$tbl}]";
        if (!empty($whereSql)) {
            $insertSql .= " WHERE {$whereSql}";
        }

        // SET IDENTITY_INSERT scope-nya per SESI koneksi - digabung jadi SATU batch
        // dgn INSERT-nya (bukan 2 panggilan query() terpisah) supaya PASTI aktif di
        // statement yg sama. Dipisah jadi 2 query() terbukti gagal ("IDENTITY_INSERT
        // is set to OFF") krn driver ODBC/PDO SQL Server tidak menjamin keduanya
        // dieksekusi di scope sesi yg identik walau pakai objek $DB yg sama.
        $sql = $hasIdentity
            ? "SET IDENTITY_INSERT [{$tbl}] ON; {$insertSql}; SET IDENTITY_INSERT [{$tbl}] OFF;"
            : $insertSql;

        $this->runQuery($DBTarget, $sql, $whereBinds);

        $countRow = $this->runQuery($DBTarget, "SELECT COUNT(*) AS jml FROM [{$tbl}]")->row_array();
        return (int) $countRow['jml'];
    }

    /**
     * Koneksi CI native ad-hoc ke SQL Server, memakai kredensial dari koneksi
     * $sumber di env.php tapi diarahkan ke $targetDatabase ('master' atau nama
     * database hasil clone yang belum terdaftar di env.php). Sengaja TIDAK lewat
     * Eloquent Capsule (Capsule di-boot sekali saat request masuk lewat
     * DB_Controller, tidak ada jalur publik utk menambah koneksi baru saat
     * runtime) - pola sama seperti base/ExecStoredProcedure.php.
     */
    private function connectAdHoc($sumber, $targetDatabase)
    {
        $env = $this->config->item('connection');
        if (!isset($env[$sumber])) {
            throw new Exception("Koneksi sumber '{$sumber}' tidak dikenal.");
        }
        $cfg = $env[$sumber];

        // LoginTimeout supaya koneksi GAGAL CEPAT dgn pesan jelas kalau host tidak
        // terjangkau/lambat, drpd menggantung tanpa batas (koneksi ke DB live app ini
        // sudah py catatan "suka lambat/tidak stabil" di env.php, tapi konvensi
        // login_timeout/query_timeout di sana TIDAK otomatis kepakai di koneksi
        // ad-hoc terpisah ini - makanya di-set eksplisit lewat DSN di sini).
        $dsn = "sqlsrv:Server=" . $cfg['host'] . "," . $cfg['port'] . ";Database=" . $targetDatabase . ";LoginTimeout=30";

        $adHocConfig = array(
            'dsn'      => $dsn,
            'username' => $cfg['username'],
            'password' => $cfg['password'],
            'dbdriver' => 'pdo',
            'dbprefix' => '',
            'pconnect' => FALSE,
            'db_debug' => FALSE, // sengaja beda dr konvensi app ini - lihat komentar runQuery()
            'cache_on' => FALSE,
            'char_set' => 'utf8',
            'dbcollat' => 'utf8_general_ci',
            'swap_pre' => '',
        );

        $DB = $this->load->database($adHocConfig, TRUE);

        // Query timeout generous (300 dtk) - jaga2 kalau salah satu tabel (mis.
        // log_tables filtered insert) ternyata perlu waktu lebih dari biasanya.
        // Operasi di controller ini sekarang semua DDL/INSERT ringan (bukan lagi
        // BACKUP/RESTORE), jadi timeout puluhan menit spt sebelumnya tidak
        // dibutuhkan lagi.
        // Dibungkus try/catch krn constant SQLSRV_ATTR_QUERY_TIMEOUT hanya ada kalau
        // ekstensi sqlsrv aktif - jangan sampai fitur lain ikut gagal kalau tidak ada.
        try {
            if (defined('PDO::SQLSRV_ATTR_QUERY_TIMEOUT') && isset($DB->conn_id) && is_object($DB->conn_id)) {
                $DB->conn_id->setAttribute(constant('PDO::SQLSRV_ATTR_QUERY_TIMEOUT'), 300);
            }
        } catch (Exception $e) {
            // Abaikan - query timeout cuma nice-to-have, bukan syarat mutlak jalannya fitur.
        }

        return $DB;
    }
}
