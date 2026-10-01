<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use Dompdf\Dompdf;

class MasterSewa extends Public_Controller {

    private $pathView = 'sewa/master_sewa/';
    private $url;
    private $hakAkses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->hakAkses = hakAkses($this->url);
    }

    public function index($segment=0)
    {

        if ( $this->hakAkses['a_view'] == 1 ) {

            $this->add_external_js(array(
                "assets/jquery/easy-autocomplete/jquery.easy-autocomplete.min.js",
                "assets/select2/js/select2.min.js",
                "assets/sewa/master_sewa/js/master_sewa.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/sewa/master_sewa/css/master_sewa.css",
            ));

            $data = $this->includes;
            $content['akses']       = $this->hakAkses;
            $content['title_panel'] = 'Master Sewa';
            $content['jenis_sewa']  = $this->get_jenis_sewa_list();
            $content['supplier']    = $this->get_supplier_list();
            // $content['unit']        = $this->get_unit_list();
            $data['title_menu']     = 'Master Sewa';

            $data['view'] = $this->load->view($this->pathView . 'v_index', $content, TRUE);
            $this->load->view($this->template, $data);

        } else {
            showErrorAkses();
        }
    }

    public function list_data()
    {
        $akses              = hakAkses($this->url);
        $m_sewa             = new \Model\Storage\MsSewa_model();
        $jenisSewa          = trim($this->input->post('jenis_sewa'));
        $tanggalMulai       = trim($this->input->post('tanggal_mulai'));
        $search             = trim($this->input->post('search'));
        $supplier           = trim($this->input->post('supplier'));
        
        $query = $m_sewa
        // ->select('ms_sewa.*', 'ms_jenis_sewa.nama_jenis_sewa', 'p.nama as nama_supplier', 'wilayah.nama as nama_unit')
        // ->leftJoin('ms_jenis_sewa', 'ms_jenis_sewa.kode_jenis_sewa', '=', 'ms_sewa.jenis_sewa')
        // ->leftJoin('wilayah', 'wilayah.kode', '=', 'ms_sewa.unit')
        // ->leftJoin('pelanggan as p', function($join) {
        //     $join->on('p.nomor', '=', 'ms_sewa.no_supplier')
        //          ->where('p.tipe', '=', 'supplier')
        //          ->where('p.mstatus', '=', 1);
        // });

        ->select(
            'ms_sewa.*', 
            'ms_jenis_sewa.nama_jenis_sewa', 
            'p.nama as nama_supplier', 
         
            new \Illuminate\Database\Query\Expression('(SELECT TOP 1 nama FROM wilayah WHERE kode = ms_sewa.unit) as nama_unit')
        )
        ->leftJoin('ms_jenis_sewa', 'ms_jenis_sewa.kode_jenis_sewa', '=', 'ms_sewa.jenis_sewa')
        ->leftJoin('pelanggan as p', function($join) {
            $join->on('p.nomor', '=', 'ms_sewa.no_supplier')
                ->where('p.tipe', '=', 'supplier')
                ->where('p.mstatus', '=', 1);
        });


        if ( !empty($jenisSewa) ) {
            $query->where('ms_sewa.jenis_sewa', $jenisSewa);
        }

        if ( !empty($tanggalMulai) ) {
            $query->whereRaw('CAST(ms_sewa.tanggal_mulai AS DATE) = ?', [$tanggalMulai]);
        }

        if ( !empty($supplier) ) {
            $query->whereRaw('LOWER(LTRIM(RTRIM(ms_sewa.no_supplier))) = ?', [strtolower(trim($supplier))]);
        }

        if ( !empty($search) ) {
            $like = '%' . $search . '%';
            $query->where(function($q) use ($like) {
                $q->whereRaw('LOWER(LTRIM(RTRIM(ms_sewa.no_sewa))) LIKE ?', [strtolower($like)])
                  ->orWhereRaw('LOWER(LTRIM(RTRIM(ms_sewa.nama_sewa))) LIKE ?', [strtolower($like)])
                  ->orWhereRaw('LOWER(LTRIM(RTRIM(ms_sewa.no_kontrak))) LIKE ?', [strtolower($like)])
                  ->orWhereRaw('LOWER(LTRIM(RTRIM(p.nama))) LIKE ?', [strtolower($like)]);
            });
        }

        $d_sewa = $query->orderBy('ms_sewa.id', 'desc')->get()->toArray();

        // cetak_r($d_sewa);die;

        $content['akses']   = $akses;
        $content['list']    = $d_sewa;
        $html = $this->load->view($this->pathView . 'v_list', $content, TRUE);

        echo $html;
    }

    private function get_jenis_sewa_list()
    {
        $m_jenis = new \Model\Storage\MasterJenisSewa_model();
        return $m_jenis->orderBy('id', 'asc')->get()->toArray();
    }

    public function add_form()
    {
        $data['data'] = null;
        $data['jenis_sewa'] = $this->get_jenis_sewa_list();
        $data['supplier']   = $this->get_supplier_list();
        $data['unit']       = $this->get_unit_list();
        $this->load->view($this->pathView . 'v_form', $data);
    }

    public function get_supplier_list()
    {
        $m_conf     = new \Model\Storage\Conf();
        $sql = " select * from pelanggan where tipe = 'supplier' and mstatus = 1 and kategori_supplier = 'SEWA' order by nama asc ";

        $d_conf     = $m_conf->hydrateRaw( $sql );
        
        $data       = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function get_unit_list()
    {
        $m_conf     = new \Model\Storage\Conf();
        $sql = " SELECT kode, MAX(nama) AS nama
                    FROM wilayah
                    WHERE jenis = 'UN'
                    GROUP BY kode
                    ORDER BY kode ASC; ";

        $d_conf     = $m_conf->hydrateRaw( $sql );
        
        $data       = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function edit_form()
    {
        $id = $this->input->get('id');

        $m_sewa = new \Model\Storage\MsSewa_model();
        $d_sewa = $m_sewa->where('id', $id)->first();

        $data['data']       = $d_sewa;
        $data['jenis_sewa'] = $this->get_jenis_sewa_list();
        $data['supplier']   = $this->get_supplier_list();
        $data['unit']       = $this->get_unit_list();

        $data['cek_amortisasi'] = $this->check_status_amortisasi($d_sewa->no_sewa);
        // cetak_r($data['cek_amortisasi'], 1);

        $this->load->view($this->pathView . 'v_form', $data);
    }

    public function check_status_amortisasi($noSewa)
    {
        if (empty($noSewa)) {
            return 0;
        }

        $m_amortisasi = new \Model\Storage\AmortisasiJadwal_model();

        $countDone = $m_amortisasi
            ->where('kode_transaksi', $noSewa)
            ->where('status', 1)
            ->count();

        return $countDone > 0 ? 1 : 0;
    }

    private function generateNoSewa($jenis_sewa, $tanggal_mulai = null, $excludeId = null)
    {
        $jenis = strtoupper(trim($jenis_sewa));
        if (empty($jenis)) {
            return null;
        }

        $year = !empty($tanggal_mulai) ? date('Y', strtotime($tanggal_mulai)) : date('Y');
        $month = !empty($tanggal_mulai) ? date('m', strtotime($tanggal_mulai)) : date('m');

        $m_sewa = new \Model\Storage\MsSewa_model();
        $rows = $m_sewa
            ->whereRaw('UPPER(jenis_sewa) = ?', [$jenis])
            ->get();

        $lastSequence = 0;
        $hasExistingNoSewa = false;

        foreach ($rows as $row) {
            if (!empty($excludeId) && (int) $row->id === (int) $excludeId) {
                continue;
            }

            $no = trim($row->no_sewa);
            if (empty($no)) {
                continue;
            }

            if (preg_match('/^SW\/' . preg_quote($jenis, '/') . '\/(\d{4})\/(\d{2})\/(\d{4})$/i', $no, $parts)) {
                if ((int) $parts[1] === (int) $year && (int) $parts[2] === (int) $month) {
                    $hasExistingNoSewa = true;
                    $sequence = (int) $parts[3];
                    if ($sequence > $lastSequence) {
                        $lastSequence = $sequence;
                    }
                }
            }
        }

        if ($hasExistingNoSewa) {
            $sequence = $lastSequence + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('SW/%s/%s/%s/%04d', $jenis, $year, $month, $sequence);
    }

    private function syncAmortisasi($noSewa, $nominalSewa, $durasi)
    {
        if (empty($noSewa)) {
            return;
        }

        $amortisasi = new \Model\Storage\AmortisasiJadwal_model();
        $amortisasi->where('kode_transaksi', $noSewa)->delete();

        $durasi = (int) $durasi;
        if ($durasi <= 0) {
            return;
        }

        $nilaiPerBulan = (float) $nominalSewa / $durasi;

        for ($i = 1; $i <= $durasi; $i++) {
            $row = new \Model\Storage\AmortisasiJadwal_model();
            $row->kode_amortisasi = $noSewa . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $row->kode_transaksi  = $noSewa;
            $row->nilai           = $nilaiPerBulan;
            $row->status          = 0;
            $row->save();
        }

        
    }

    private function syncTermin($noSewa, $tanggalMulai, $nominalSewa, $durasi, $dp, $nominalCicilan, $durasiCicilan, $tglJatuhTempo)
    {
        if (empty($noSewa)) return;

        $termin = new \Model\Storage\MsSewaTermin_model();
    
        $termin->where('no_sewa', $noSewa)->delete();

        // if ($durasi <= 0) {
        //     return; 
        // }

        // cetak_r($dp, 1);

        if ($dp > 0) {
            $rowDp = new \Model\Storage\MsSewaTermin_model();
            $rowDp->no_sewa          = $noSewa;
            $rowDp->no_termin        = '0'; 
            $rowDp->jenis_termin     = 'dp';
            $rowDp->tgl_jatuh_tempo  = $tanggalMulai; 
            $rowDp->nominal          = $dp;
            $rowDp->nominal_terbayar = 0;
            $rowDp->status           = 0;
            $rowDp->save();
        }

        $sisaPokok = $nominalSewa - $dp;
        $nilaiCicilan = $durasiCicilan > 0 ? $sisaPokok / $durasiCicilan : 0;

        for ($i = 1; $i <= $durasi; $i++) {
            $row = new \Model\Storage\MsSewaTermin_model();
            $row->no_sewa          = $noSewa;
            $row->no_termin        = (string) $i;
            $row->jenis_termin     = 'cicilan';
            
            $row->tgl_jatuh_tempo  = date('Y-m-d', strtotime($tanggalMulai . " +{$i} month"));
            
            $row->nominal          = $nilaiCicilan;
            $row->nominal_terbayar = 0;
            $row->status           = 0;
            $row->save();
        }
    }



    public function save_data()
    {
        $params = $this->input->post('params');

        try {
            $m_sewa = new \Model\Storage\MsSewa_model();

            $namaSewa     = trim($params['nama_sewa']);
            $noKontrak    = trim($params['no_kontrak']);
            $jenisSewa    = strtoupper(trim($params['jenis_sewa']));
            $tanggalMulai = trim($params['tanggal_mulai']);
            $noSupplier   = trim($params['no_supplier']);
            $unit         = trim($params['unit']);

            if (empty($namaSewa) || empty($noKontrak) || empty($jenisSewa) || empty($tanggalMulai)) {
                $this->result['message'] = 'Nama sewa, nomor kontrak, jenis sewa, dan tanggal mulai wajib diisi.';
            } else {
                $attachmentName = null;
                
                if (!empty($_FILES['file_dokumen']['name'])) {
                    $file = $_FILES['file_dokumen'];
                    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                    $maxSize = 5 * 1024 * 1024;

                    if (!in_array($file['type'], $allowedTypes) || $file['size'] > $maxSize) {
                        $this->result['message'] = 'Format file harus PDF/JPG/PNG dan maksimal 5MB.';
                        display_json($this->result);
                        return;
                    }

                    $uploadPath = FCPATH . 'uploads/sewa/'; 
                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }

                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $hash = hash('sha256', time() . $file['name'] . uniqid('', true));
                    $attachmentName = $hash . '.' . $ext;

                    if (!move_uploaded_file($file['tmp_name'], $uploadPath . $attachmentName)) {
                        throw new \Exception('Gagal mengupload file ke server.');
                    }
                }

                $noSewa = $this->generateNoSewa($jenisSewa, $tanggalMulai);

                $m_sewa->no_sewa         = $noSewa;
                $m_sewa->nama_sewa       = $namaSewa;
                $m_sewa->no_kontrak      = $noKontrak;
                $m_sewa->jumlah_bulan    = !empty($params['jumlah_bulan']) ? (int) $params['jumlah_bulan'] : 0;
                $m_sewa->jumlah_siklus   = !empty($params['jumlah_siklus']) ? (int) $params['jumlah_siklus'] : 0;
                $m_sewa->jenis_sewa      = $jenisSewa;
                $m_sewa->tanggal_mulai   = $tanggalMulai;
                $m_sewa->no_supplier     = $noSupplier;
                $m_sewa->nominal_sewa    = !empty($params['nominal_sewa']) ? (int) str_replace('.', '', $params['nominal_sewa']) : 0;
                $m_sewa->dp              = !empty($params['dp']) ? (int) str_replace('.', '', $params['dp']) : 0;
                $m_sewa->durasi_cicilan  = !empty($params['durasi_cicilan']) ? (int) $params['durasi_cicilan'] : 0;
                $m_sewa->tgl_jatuh_tempo = !empty($params['tgl_jatuh_tempo']) ? (int) $params['tgl_jatuh_tempo'] : 1;
                $m_sewa->nominal_cicilan = !empty($params['nominal_cicilan']) ? (int) str_replace('.', '', $params['nominal_cicilan']) : 0;
                $m_sewa->unit            = $unit;
                $m_sewa->attachment      = $attachmentName; 
                $m_sewa->save();

                $durasiAmortisasi = $m_sewa->jumlah_bulan > 0 ? $m_sewa->jumlah_bulan : $m_sewa->jumlah_siklus;
                $durasiTermin     = $m_sewa->durasi_cicilan;

                if ($durasiAmortisasi > 0 && !empty($noSewa)) {
                    $this->syncAmortisasi($noSewa, $m_sewa->nominal_sewa, $durasiAmortisasi);
                }

                if ($durasiTermin > 0 && !empty($noSewa)) {
                // if (!empty($noSewa)) {

                    $tglJatuhTempo = $m_sewa->tgl_jatuh_tempo > 0 ? $m_sewa->tgl_jatuh_tempo : 1;
                    
                    $this->syncTermin(
                        $noSewa, $tanggalMulai, $m_sewa->nominal_sewa, 
                        $durasiTermin, $m_sewa->dp, $m_sewa->nominal_cicilan, 
                        $durasiTermin, $tglJatuhTempo
                    );
                }

                $m_sewa->load(['table_amortisasi_jadwal', 'ms_termin_sewa']);
                $userNama      = $this->userdata['detail_user']['nama_detuser'] ?? 'System';
                $deskripsi_log = "Data sewa {$noSewa} di-submit oleh {$userNama}";
                Modules::run('base/event/save', $m_sewa, $deskripsi_log, null, $m_sewa->id, null);

                $this->result['status']  = 1;
                $this->result['message'] = 'Data berhasil disimpan';
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        } catch (\Exception $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit_data()
    {
        $params = $this->input->post('params');

        try {
            $m_sewa = new \Model\Storage\MsSewa_model();
            
            $namaSewa     = trim($params['nama_sewa']);
            $noKontrak    = trim($params['no_kontrak']);
            $jenisSewa    = strtoupper(trim($params['jenis_sewa']));
            $tanggalMulai = trim($params['tanggal_mulai']);
            $noSupplier   = trim($params['no_supplier']);
            $unit         = trim($params['unit']);

            $dp            = !empty($params['dp']) ? (int) str_replace('.', '', $params['dp']) : 0;
            $durasiCicilan = !empty($params['durasi_cicilan']) ? (int) $params['durasi_cicilan'] : 0;

            if (empty($namaSewa) || empty($noKontrak) || empty($jenisSewa) || empty($tanggalMulai) || empty($noSupplier)) {
                $this->result['message'] = 'Nama sewa, nomor kontrak, jenis sewa, tanggal mulai, dan no supplier wajib diisi.';
                display_json($this->result); return;
            }

            // if ($dp > 0 && $durasiCicilan <= 0) {
            //     $this->result['message'] = 'Jika ada DP, Durasi Cicilan wajib diisi.';
            //     display_json($this->result); return;
            // }

            $current = $m_sewa->where('id', $params['id'])->first();
            if (!$current) {
                $this->result['message'] = 'Data sewa tidak ditemukan.';
                display_json($this->result); return;
            }

            $oldNoSewa     = !empty($current->no_sewa) ? trim($current->no_sewa) : '';
            $oldType       = !empty($current->jenis_sewa) ? strtoupper(trim($current->jenis_sewa)) : '';
            $oldTglMulai   = !empty($current->tanggal_mulai) ? date('Y-m-d', strtotime($current->tanggal_mulai)) : '';
            $oldAttachment = !empty($current->attachment) ? $current->attachment : null;

            $noSewa = $oldNoSewa;
            if ($oldType !== $jenisSewa || $oldTglMulai !== date('Y-m-d', strtotime($tanggalMulai))) {
                $noSewa = $this->generateNoSewa($jenisSewa, $tanggalMulai, $params['id']);
            }

            if ($m_sewa->whereRaw('LOWER(LTRIM(RTRIM(no_sewa))) = ?', [strtolower(trim($noSewa))])->where('id', '!=', $params['id'])->first()) {
                $this->result['message'] = 'Nomor sewa sudah ada.';
                display_json($this->result); return;
            }

            $attachmentName = $oldAttachment; 
            
            if (!empty($_FILES['file_dokumen']['name'])) {
                $file = $_FILES['file_dokumen'];
                $allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                $maxSize = 5 * 1024 * 1024;

                if (!in_array($file['type'], $allowedTypes) || $file['size'] > $maxSize) {
                    $this->result['message'] = 'Format file harus PDF/JPG/PNG dan maksimal 5MB.';
                    display_json($this->result); return;
                }

                $uploadPath = FCPATH . 'uploads/sewa/'; 
                if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $hash = hash('sha256', time() . $file['name'] . uniqid('', true));
                $attachmentName = $hash . '.' . $ext;

                if (move_uploaded_file($file['tmp_name'], $uploadPath . $attachmentName)) {
                    if ($oldAttachment && file_exists($uploadPath . $oldAttachment)) {
                        @unlink($uploadPath . $oldAttachment);
                    }
                } else {
                    throw new \Exception('Gagal mengupload file ke server.');
                }
            }

            $data_update = [
                'nama_sewa'       => $namaSewa,
                'no_kontrak'      => $noKontrak,
                'jumlah_bulan'    => !empty($params['jumlah_bulan']) ? (int)$params['jumlah_bulan'] : 0,
                'jumlah_siklus'   => !empty($params['jumlah_siklus']) ? (int)$params['jumlah_siklus'] : 0,
                'jenis_sewa'      => $jenisSewa,
                'tanggal_mulai'   => $tanggalMulai,
                'no_supplier'     => $noSupplier,
                'nominal_sewa'    => !empty($params['nominal_sewa']) ? (int)str_replace('.', '', $params['nominal_sewa']) : 0,
                'no_sewa'         => $noSewa,
                'dp'              => $dp,
                'nominal_cicilan' => !empty($params['nominal_cicilan']) ? (int)str_replace('.', '', $params['nominal_cicilan']) : 0,
                'durasi_cicilan'  => $durasiCicilan,
                'tgl_jatuh_tempo' => !empty($params['tgl_jatuh_tempo']) ? (int)$params['tgl_jatuh_tempo'] : 0,
                'unit'            => $unit,
                'attachment'      => $attachmentName,
            ];

            $m_sewa->where('id', $params['id'])->update($data_update);

            $durasiAmortisasi = $data_update['jumlah_bulan'] > 0 ? $data_update['jumlah_bulan'] : $data_update['jumlah_siklus'];
            $durasiTermin     = $data_update['durasi_cicilan'];

            if (!empty($oldNoSewa)) {
                (new \Model\Storage\AmortisasiJadwal_model())->where('kode_transaksi', $oldNoSewa)->delete();
                (new \Model\Storage\MsSewaTermin_model())->where('no_sewa', $oldNoSewa)->delete();
            }

            if ($durasiAmortisasi > 0 && !empty($noSewa)) {
                $this->syncAmortisasi($noSewa, $data_update['nominal_sewa'], $durasiAmortisasi);
            }

            if ($durasiTermin > 0 && !empty($noSewa)) {
                $tglJatuhTempo = $data_update['tgl_jatuh_tempo'] > 0 ? $data_update['tgl_jatuh_tempo'] : 1;
                
                $this->syncTermin(
                    $noSewa, 
                    $data_update['tanggal_mulai'], 
                    $data_update['nominal_sewa'], 
                    $durasiTermin, 
                    $data_update['dp'], 
                    $data_update['nominal_cicilan'], 
                    $durasiTermin, 
                    $tglJatuhTempo
                );
            }

            $model_for_log  = $m_sewa->with(['table_amortisasi_jadwal', 'ms_termin_sewa'])->where('id', $params['id'])->first();
            $userNama       = $this->userdata['detail_user']['nama_detuser'] ?? 'System';
            $deskripsi_log  = "Update data sewa {$noSewa} oleh {$userNama}";
            Modules::run('base/event/update', $model_for_log, $deskripsi_log, 'ms_sewa', $params['id'], null);

            $this->result['status']  = 1;
            $this->result['message'] = 'Data berhasil diubah';

        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        } catch (\Exception $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        }

        display_json($this->result);
    }

    public function export_data()
    {
        $type = strtolower(trim($this->input->get('type')));
        $jenisSewa = trim($this->input->get('jenis_sewa'));
        $tanggalMulai = trim($this->input->get('tanggal_mulai'));
        $supplier = trim($this->input->get('supplier'));
        $search = trim($this->input->get('search'));

        $m_sewa = new \Model\Storage\MsSewa_model();
        $query = $m_sewa
            ->select('ms_sewa.*', 'ms_jenis_sewa.nama_jenis_sewa', 'p.nama as nama_supplier')
            ->leftJoin('ms_jenis_sewa', 'ms_jenis_sewa.kode_jenis_sewa', '=', 'ms_sewa.jenis_sewa')
            ->leftJoin('pelanggan as p', function($join) {
                $join->on('p.nomor', '=', 'ms_sewa.no_supplier')
                    ->where('p.tipe', '=', 'supplier')
                    ->where('p.mstatus', '=', 1);
            });

        if ( !empty($jenisSewa) ) {
            $query->where('ms_sewa.jenis_sewa', $jenisSewa);
        }

        if ( !empty($tanggalMulai) ) {
            $query->whereRaw('CAST(ms_sewa.tanggal_mulai AS DATE) = ?', [$tanggalMulai]);
        }

        if ( !empty($supplier) ) {
            $query->whereRaw('LOWER(LTRIM(RTRIM(ms_sewa.no_supplier))) = ?', [strtolower(trim($supplier))]);
        }

        if ( !empty($search) ) {
            $like = '%' . $search . '%';
            $query->where(function($q) use ($like) {
                $q->whereRaw('LOWER(LTRIM(RTRIM(ms_sewa.no_sewa))) LIKE ?', [strtolower($like)])
                  ->orWhereRaw('LOWER(LTRIM(RTRIM(ms_sewa.nama_sewa))) LIKE ?', [strtolower($like)])
                  ->orWhereRaw('LOWER(LTRIM(RTRIM(ms_sewa.no_kontrak))) LIKE ?', [strtolower($like)])
                  ->orWhereRaw('LOWER(LTRIM(RTRIM(p.nama))) LIKE ?', [strtolower($like)]);
            });
        }

        $rows = $query->orderBy('ms_sewa.id', 'desc')->get()->toArray();

        if ( $type === 'pdf' ) {
            $html = '<table border="1" cellpadding="4" cellspacing="0" style="border-collapse:collapse;width:100%;font-family:Arial;font-size:11px;">
                <thead>
                    <tr>
                        <th>No. Sewa</th>
                        <th>Nama Sewa</th>
                        <th>No. Kontrak</th>
                        <th>Jenis Sewa</th>
                        <th>Tanggal Mulai</th>
                        <th>Jumlah Bulan</th>
                        <th>Jumlah Siklus</th>
                        <th>No. Supplier</th>
                        <th>Nama Supplier</th>
                        <th>Nominal Sewa</th>
                    </tr>
                </thead>
                <tbody>';

            foreach ( $rows as $row ) {
                $html .= '<tr>'
                    . '<td>' . (isset($row['no_sewa']) ? $row['no_sewa'] : '-') . '</td>'
                    . '<td>' . (isset($row['nama_sewa']) ? $row['nama_sewa'] : '-') . '</td>'
                    . '<td>' . (isset($row['no_kontrak']) ? $row['no_kontrak'] : '-') . '</td>'
                    . '<td>' . (isset($row['nama_jenis_sewa']) ? $row['nama_jenis_sewa'] : (isset($row['jenis_sewa']) ? $row['jenis_sewa'] : '-')) . '</td>'
                    . '<td>' . (isset($row['tanggal_mulai']) ? date('d F Y', strtotime($row['tanggal_mulai'])) : '-') . '</td>'
                    . '<td>' . (isset($row['jumlah_bulan']) ? $row['jumlah_bulan'] : '-') . '</td>'
                    . '<td>' . (isset($row['jumlah_siklus']) ? $row['jumlah_siklus'] : '-') . '</td>'
                    . '<td>' . (isset($row['no_supplier']) ? $row['no_supplier'] : '-') . '</td>'
                    . '<td>' . (isset($row['nama_supplier']) ? $row['nama_supplier'] : '-') . '</td>'
                    . '<td>' . (isset($row['nominal_sewa']) ? number_format($row['nominal_sewa'], 0, ',', '.') : '0') . '</td>'
                    . '</tr>';
            }

            $html .= '</tbody></table>';

            if (ob_get_level()) {
                ob_end_clean();
            }

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream('MASTER_SEWA_' . date('Y-m-d H:i:s') . '.pdf', ['Attachment' => TRUE]);
            exit;
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Master Sewa');

        $headers = [
            'No. Sewa',
            'Nama Sewa',
            'No. Kontrak',
            'Jenis Sewa',
            'Tanggal Mulai',
            'Jumlah Bulan',
            'Jumlah Siklus',
            'No. Supplier',
            'Nama Supplier',
            'Nominal Sewa'
        ];

        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('A1:J1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D9EAF7');

        foreach ( $rows as $i => $row ) {
            $r = $i + 2;
            $sheet->setCellValueExplicit('A' . $r, isset($row['no_sewa']) ? $row['no_sewa'] : '-', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B' . $r, isset($row['nama_sewa']) ? $row['nama_sewa'] : '-', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $r, isset($row['no_kontrak']) ? $row['no_kontrak'] : '-', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $r, isset($row['nama_jenis_sewa']) ? $row['nama_jenis_sewa'] : (isset($row['jenis_sewa']) ? $row['jenis_sewa'] : '-'), DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $r, isset($row['tanggal_mulai']) ? date('Y-m-d', strtotime($row['tanggal_mulai'])) : '-');
            $sheet->setCellValue('F' . $r, isset($row['jumlah_bulan']) ? $row['jumlah_bulan'] : 0);
            $sheet->setCellValue('G' . $r, isset($row['jumlah_siklus']) ? $row['jumlah_siklus'] : 0);
            $sheet->setCellValueExplicit('H' . $r, isset($row['no_supplier']) ? $row['no_supplier'] : '-', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('I' . $r, isset($row['nama_supplier']) ? $row['nama_supplier'] : '-', DataType::TYPE_STRING);
            $sheet->setCellValue('J' . $r, isset($row['nominal_sewa']) ? (int) $row['nominal_sewa'] : 0);
        }

        $sheet->getStyle('E2:E' . ($r ?? 1 + 1))->getNumberFormat()->setFormatCode('yyyy-mm-dd');
        $sheet->getStyle('J2:J' . ($r ?? 1 + 1))->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(30);
        $sheet->getColumnDimension('J')->setWidth(20);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="MASTER_SEWA_' . date('Y-m-d H:i:s') . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    // public function delete_data()
    // {
    //     $id = $this->input->post('params');

    //     try {
    //         $m_sewa = new \Model\Storage\MsSewa_model();
    //         $current = $m_sewa->where('id', $id)->first();
            
    //         // Validasi: Pastikan data ada sebelum diproses
    //         if (!$current) {
    //             $this->result['message'] = 'Data sewa tidak ditemukan.';
    //             display_json($this->result);
    //             return;
    //         }

    //         $noSewa = !empty($current->no_sewa) ? trim($current->no_sewa) : '';
    //         $userNama = $this->userdata['detail_user']['nama_detuser'] ?? 'System';

    //         if (!empty($noSewa)) {
    //             // ===== 1. HAPUS AMORTISASI =====
    //             $amort = new \Model\Storage\AmortisasiJadwal_model();
                
    //             // ✅ PERBAIKAN: Gunakan ->get() bukan ->findAll()
    //             $dataAmortLama = $amort->where('kode_transaksi', $noSewa)->get();
    //             $jumlahAmort = $dataAmortLama->count();

    //             if ($jumlahAmort > 0) {
    //                 $amort->where('kode_transaksi', $noSewa)->delete();
                    
    //                 $deskripsi_log = "Amortisasi dihapus ({$jumlahAmort} data) untuk No. Sewa: {$noSewa} oleh {$userNama}";
    //                 // Gunakan ->toArray() agar aman dikirim ke sistem log
    //                 Modules::run('base/event/delete', $amort, $deskripsi_log, null, $noSewa, $dataAmortLama->toArray());
    //             }

    //             // ===== 2. HAPUS TERMIN =====
    //             $terminDel = new \Model\Storage\MsSewaTermin_model();
                
    //             // ✅ PERBAIKAN: Gunakan ->get() bukan ->findAll()
    //             $dataTerminLama = $terminDel->where('no_sewa', $noSewa)->get();
    //             $jumlahTermin = $dataTerminLama->count();

    //             if ($jumlahTermin > 0) {
    //                 $terminDel->where('no_sewa', $noSewa)->delete();
                    
    //                 $deskripsi_log = "Termin dihapus ({$jumlahTermin} data) untuk No. Sewa: {$noSewa} oleh {$userNama}";
    //                 Modules::run('base/event/delete', $terminDel, $deskripsi_log, null, $noSewa, $dataTerminLama->toArray());
    //             }
    //         }

    //         // ===== 3. HAPUS DATA SEWA UTAMA =====
    //         $m_sewa->where('id', $id)->delete();

    //         // ✅ PERBAIKAN: Log lebih informatif & kirim data '$current' (bukan object model '$m_sewa')
    //         $deskripsi_log = "Data Sewa [{$noSewa}] (ID: {$id}) dihapus oleh {$userNama}";
    //         Modules::run('base/event/delete', $m_sewa, $deskripsi_log, null, $id, $current);

    //         $this->result['status'] = 1;
    //         $this->result['message'] = 'Data berhasil dihapus';
            
    //     } catch (\Illuminate\Database\QueryException $e) {
    //         $this->result['message'] = 'Gagal : ' . $e->getMessage();
    //     }

    //     display_json($this->result);
    // }

    public function delete_data()
    {
        $id = $this->input->post('params');

        try {
            $m_sewa = new \Model\Storage\MsSewa_model();
            
            // 1. AMBIL DATA DULU (Wajib, agar object punya properti 'id' untuk Event controller)
            $current = $m_sewa->where('id', $id)->first();
            
            if (!$current) {
                $this->result['message'] = 'Data sewa tidak ditemukan.';
                display_json($this->result);
                return;
            }


            $current = $m_sewa->with(['table_amortisasi_jadwal', 'ms_termin_sewa'])->where('id', $id)->first();
            if (!$current) {
                $this->result['message'] = 'Data sewa tidak ditemukan.';
                display_json($this->result);
                return;

            }

            $noSewa         = !empty($current->no_sewa) ? trim($current->no_sewa) : '';
            $userNama       = $this->userdata['detail_user']['nama_detuser'] ?? 'System';
            $deskripsi_log  = "di-delete oleh {$userNama}";
            Modules::run('base/event/delete', $current, $deskripsi_log, 'ms_sewa', $id, null);
            

            $m_sewa->where('id', $id)->delete();

            if (!empty($noSewa)) {
                
                $amort          = new \Model\Storage\AmortisasiJadwal_model();
                $dataAmortLama  = $amort->where('kode_transaksi', $noSewa)->get();
                $jumlahAmort    = $dataAmortLama->count();

                if ($jumlahAmort > 0) {
                    $amort->where('kode_transaksi', $noSewa)->delete();
                    // $deskripsi_log = "di-delete oleh {$userNama}";
                    // Modules::run('base/event/delete', $amort, $deskripsi_log, null, $noSewa, $dataAmortLama->toArray());
                }

                $terminDel      = new \Model\Storage\MsSewaTermin_model();
                $dataTerminLama = $terminDel->where('no_sewa', $noSewa)->get();
                $jumlahTermin   = $dataTerminLama->count();

                if ($jumlahTermin > 0) {
                    $terminDel->where('no_sewa', $noSewa)->delete();
                    
                    // $deskripsi_log = "delete oleh {$userNama}";
                    // Modules::run('base/event/delete', $terminDel, $deskripsi_log, null, $noSewa, $dataTerminLama->toArray());
                }
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil dihapus';
            
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        }

        display_json($this->result);
    }


    public function detail_data()
    {
        $id = $this->input->post('params');
        $viewData = [
            'data'          => [],
            'supplier'      => [],
            'amortisasi'    => [],
            'nama_unit'     => '-',
        ];

        try {
            $m_sewa = new \Model\Storage\MsSewa_model();
            $d_sewa = $m_sewa->where('id', $id)->first();

            if ( $d_sewa ) {
                $viewData['data'] = $d_sewa->toArray();

                if ( !empty($d_sewa->no_supplier) ) {
                    $m_conf = new \Model\Storage\Conf();
                    $sql_supplier = "select * from pelanggan where tipe = 'supplier' and mstatus = 1 and nomor = '" . trim($d_sewa->no_supplier) . "'";
                    $supplier = $m_conf->hydrateRaw($sql_supplier);

                    $sql_amortisasi = "select * from tabel_amortisasi_jadwal where kode_transaksi = '" . trim($d_sewa->no_sewa) . "'";
                    $amortisasi = $m_conf->hydrateRaw($sql_amortisasi);

                    $sql_termin = "select * from ms_sewa_termin where no_sewa = '" . trim($d_sewa->no_sewa) . "'";
                    $termin = $m_conf->hydrateRaw($sql_termin);

                    $sql_unit = "select top 1 nama as nama_unit from wilayah where kode = '" . trim($d_sewa->unit) . "'";
                    $unit = $m_conf->hydrateRaw($sql_unit);
                     

                    if ( $supplier && method_exists($supplier, 'count') && $supplier->count() > 0 ) {
                        $viewData['supplier'] = $supplier->toArray();
                    }

                    if ( $amortisasi && method_exists($amortisasi, 'count') && $amortisasi->count() > 0 ) {
                        $viewData['amortisasi'] = $amortisasi->toArray();
                    }

                    if ( $termin && method_exists($termin, 'count') && $termin->count() > 0 ) {
                        $viewData['termin'] = $termin->toArray();
                    }

                    if ( $unit && method_exists($unit, 'count') && $unit->count() > 0 ) {
                        $viewData['nama_unit'] = $unit->toArray();
                    }
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $viewData['data']       = [];
            $viewData['supplier']   = [];
            $viewData['amortisasi'] = [];
            $viewData['termin']     = [];
            $viewData['nama_unit']  = [];
        }

        // cetak_r($viewData);die;

        echo $this->load->view($this->pathView . 'v_detail_data', $viewData, TRUE);
    }

    public function detailAmortisasi()
    {
        $id = $this->input->post('params');
        
        $viewData = [
            'sewa'       => null,
            'amortisasi' => []
        ];

        try {
            $m_sewa = new \Model\Storage\MsSewa_model();
            $d_sewa = $m_sewa->where('id', $id)->first();

            if ($d_sewa) {
                $viewData['sewa'] = $d_sewa;

                $m_amort = new \Model\Storage\AmortisasiJadwal_model();
                $viewData['amortisasi'] = $m_amort->where('kode_transaksi', $d_sewa->no_sewa)
                                                  ->orderBy('kode_amortisasi', 'asc')
                                                  ->get()
                                                  ->toArray();
            }
        } catch (\Illuminate\Database\QueryException $e) {  
            $viewData['sewa']       = null;
            $viewData['amortisasi'] = [];
        }

        // cetak_r($viewData);die; 

        echo $this->load->view($this->pathView . 'v_detail_amortisasi', $viewData, TRUE);
    }


         
    public function saveAmortisasi()
    {
        $this->result['status'] = 0;
        $this->result['message'] = 'Gagal menyimpan data.';

        try {
            $params = $this->input->post('params');
            $idSewa = isset($params['id_sewa']) ? $params['id_sewa'] : 0;
            $amortisasiData = isset($params['amortisasi']) ? $params['amortisasi'] : [];

            if (empty($idSewa) || empty($amortisasiData)) {
                throw new Exception('Data tidak valid atau kosong.');
            }

            $m_amort = new \Model\Storage\AmortisasiJadwal_model();
            $updatedCount = 0;
            $deletedCount = 0;

            foreach ($amortisasiData as $item) {
                if (!isset($item['id']) || empty($item['id'])) continue;

                $isDeleted = isset($item['is_deleted']) && $item['is_deleted'] == 1;

                if ($isDeleted) {
                    $deskripsi_log = " dihapus oleh " . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run('base/event/delete', $m_amort, $deskripsi_log, null, $item['id'], $m_amort);
            
                    $m_amort->where('id', $item['id'])->delete();
                    $deletedCount++;
                } else {
                    $updateData = [
                        'nilai' => (float) $item['nilai']
                    ];

                    $deskripsi_log = " di-update oleh " . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run('base/event/update', $m_amort, $deskripsi_log, null, $item['id'], $m_amort);

                    $m_amort->where('id', $item['id'])->update($updateData);
                    $updatedCount++;
                }
            }

            // Kunci ms_swa 
                $m_sewa = new \Model\Storage\MsSewa_model();
                $data_update = [
                    'is_locked'      => 1,
                ];
                $m_sewa->where('id', $params['id_sewa'])->update($data_update);

            // End Kunci ms_sewa

            // // Log Event
            // if ($updatedCount > 0 || $deletedCount > 0) {
            //     $m_sewa = new \Model\Storage\MsSewa_model();
            //     $d_sewa = $m_sewa->where('id', $idSewa)->first();
            //     if ($d_sewa) {
            //         $deskripsi_log = 'amortisasi di-update (' . $updatedCount . ' data) dan dihapus (' . $deletedCount . ' data) oleh ' . $this->userdata['detail_user']['nama_detuser'];
            //         Modules::run('base/event/update', $m_sewa, $deskripsi_log, null, $idSewa, $d_sewa);
            //     }
            // }

            $this->result['status'] = 1;
            $this->result['message'] = "Berhasil menyimpan {$updatedCount} data dan menghapus {$deletedCount} data amortisasi.";

        } catch (\Exception $e) {
            $this->result['message'] = 'Error: ' . $e->getMessage();
        }

        display_json($this->result);
    }


}