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

class MasterAset extends Public_Controller {

    private $pathView = 'aset/master_aset/';
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
                "assets/aset/master_aset/js/master_aset.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/aset/master_aset/css/master_aset.css",
            ));

            $data = $this->includes;
            $content['akses']           = $this->hakAkses;
            $content['title_panel']     = 'Master Aset';
            $content['kategori_aset']   = $this->get_kategori_aset_list();
            $content['unit_pengguna']   = $this->get_unit_list();
            $data['title_menu']         = 'Master Aset';

            $data['view'] = $this->load->view($this->pathView . 'v_index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    // public function list_data()
    // {
    //     $akses           = hakAkses($this->url);
    //     $m_aset          = new \Model\Storage\MsAset_model();
        
    //     $filter_kategori = trim($this->input->post('id_kategori'));
    //     $filter_tanggal  = trim($this->input->post('tanggal_mulai'));
        
    //     $search          = trim($this->input->post('search'));

    //     $query = $m_aset
    //         ->select(
    //             'ms_aset.*', 
    //             'ms_aset_kategori.kategori_name as nama_kategori',
    //             'ms_aset_kategori.kategori_kode', 
    //             'karyawan.nama as nama_pic',
    //             new \Illuminate\Database\Query\Expression('(SELECT TOP 1 nama FROM wilayah WHERE kode = ms_aset.lokasi_pengguna) as nama_unit'),
                
    //             // new \Illuminate\Database\Query\Expression("
    //             //     CASE WHEN EXISTS (
    //             //         SELECT 1 FROM penyusutan_komersial_aset 
    //             //         WHERE penyusutan_komersial_aset.kode_aset = ms_aset.kode_aset 
    //             //         AND penyusutan_komersial_aset.status = 1
    //             //     ) OR EXISTS (
    //             //         SELECT 1 FROM penyusutan_fiskal_aset 
    //             //         WHERE penyusutan_fiskal_aset.kode_aset = ms_aset.kode_aset 
    //             //         AND penyusutan_fiskal_aset.status = 1
    //             //     ) THEN 1 ELSE 0 END AS is_locked
    //             // ")

    //             new \Illuminate\Database\Query\Expression("
    //                 CASE WHEN EXISTS (
    //                     SELECT 1 FROM penyusutan_komersial_aset 
    //                     WHERE penyusutan_komersial_aset.kode_aset = ms_aset.kode_aset 
    //                     AND penyusutan_komersial_aset.status = 1
    //                 ) OR EXISTS (
    //                     SELECT 1 FROM penyusutan_fiskal_aset 
    //                     WHERE penyusutan_fiskal_aset.kode_aset = ms_aset.kode_aset 
    //                     AND penyusutan_fiskal_aset.status = 1
    //                 ) OR EXISTS (
    //                     SELECT 1 FROM penerimaan_aset 
    //                     WHERE penerimaan_aset.kode_aset = ms_aset.kode_aset
    //                 ) THEN 1 ELSE 0 END AS is_locked
    //             ")
    //         )
    //         ->leftJoin('ms_aset_kategori', 'ms_aset_kategori.id', '=', 'ms_aset.id_kategori')
    //         ->leftJoin('karyawan', function ($join) {
    //             $join->on('karyawan.nik', '=', 'ms_aset.pic')
    //                 ->where('karyawan.status', '=', '1');
    //         });

    //     if (!empty($filter_kategori)) {
    //         $query->where('ms_aset_kategori.kategori_name', $filter_kategori);
    //     }

    //     if (!empty($filter_tanggal)) {
    //         $query->where('ms_aset.tgl_perolehan', $filter_tanggal);
    //     }

    //     if (!empty($search)) {

    //         $like = '%' . $search . '%';
            
    //         $query->where(function($q) use ($like) {
    //             $q->where('ms_aset.kode_aset', 'like', $like)
    //             ->orWhere('ms_aset.deskripsi_aset', 'like', $like)
    //             ->orWhere('ms_aset_kategori.kategori_name', 'like', $like)
    //             ->orWhere('karyawan.nama', 'like', $like);
    //         });
    //     }

    //     $d_aset = $query->orderBy('ms_aset.id', 'desc')->get()->toArray();

    //     $content['akses']   = $akses;
    //     $content['list']    = $d_aset;
        
    //     $html = $this->load->view($this->pathView . 'v_list', $content, TRUE);
    //     echo $html;
    // }

    public function list_data()
    {
        $akses           = hakAkses($this->url);
        $m_aset          = new \Model\Storage\MsAset_model();
        
        $filter_kategori = trim($this->input->post('id_kategori'));
        $filter_tanggal  = trim($this->input->post('tanggal_mulai'));
        $search          = trim($this->input->post('search'));

        $query = $m_aset
            ->select(
                'ms_aset.*', 
                'ms_aset_kategori.kategori_name as nama_kategori',
                'ms_aset_kategori.kategori_kode', 
                'karyawan.nama as nama_pic',
                new \Illuminate\Database\Query\Expression('(SELECT TOP 1 nama FROM wilayah WHERE kode = ms_aset.lokasi_pengguna) as nama_unit'),
                
                new \Illuminate\Database\Query\Expression("
                    CASE WHEN EXISTS (
                        SELECT 1 FROM penerimaan_aset 
                        WHERE penerimaan_aset.kode_aset = ms_aset.kode_aset
                    ) THEN 1 ELSE 0 END AS is_received
                "),

                new \Illuminate\Database\Query\Expression("
                    CASE WHEN EXISTS (
                        SELECT 1 FROM penyusutan_komersial_aset 
                        WHERE penyusutan_komersial_aset.kode_aset = ms_aset.kode_aset 
                        AND penyusutan_komersial_aset.status = 1
                    ) OR EXISTS (
                        SELECT 1 FROM penyusutan_fiskal_aset 
                        WHERE penyusutan_fiskal_aset.kode_aset = ms_aset.kode_aset 
                        AND penyusutan_fiskal_aset.status = 1
                    ) THEN 1 ELSE 0 END AS is_locked
                ")
            )
            ->leftJoin('ms_aset_kategori', 'ms_aset_kategori.id', '=', 'ms_aset.id_kategori')
            ->leftJoin('karyawan', function ($join) {
                $join->on('karyawan.nik', '=', 'ms_aset.pic')
                    ->where('karyawan.status', '=', '1');
            });

        if (!empty($filter_kategori)) {
            $query->where('ms_aset_kategori.kategori_name', $filter_kategori);
        }

        if (!empty($filter_tanggal)) {
            $query->where('ms_aset.tgl_perolehan', $filter_tanggal);
        }

        if (!empty($search)) {
            $like = '%' . $search . '%';
            $query->where(function($q) use ($like) {
                $q->where('ms_aset.kode_aset', 'like', $like)
                ->orWhere('ms_aset.deskripsi_aset', 'like', $like)
                ->orWhere('ms_aset_kategori.kategori_name', 'like', $like)
                ->orWhere('karyawan.nama', 'like', $like);
            });
        }

        $d_aset = $query->orderBy('ms_aset.id', 'desc')->get()->toArray();

        $content['akses']   = $akses;
        $content['list']    = $d_aset;
        
        $html = $this->load->view($this->pathView . 'v_list', $content, TRUE);
        echo $html;
    }

    private function get_kategori_aset_list()
    {
        $m_kategori = new \Model\Storage\MsAsetKategori_model();
        return $m_kategori->orderBy('id', 'asc')->get()->toArray();
    }

    private function get_unit_list()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = " SELECT kode, MAX(nama) AS nama
                    FROM wilayah
                    WHERE jenis = 'UN'
                    GROUP BY kode
                    ORDER BY kode ASC; ";

        $d_conf = $m_conf->hydrateRaw($sql);
        return $d_conf->count() > 0 ? $d_conf->toArray() : null;
    }

    private function get_pic_list()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = " select k.nik, k.nama, j.nama as nama_jabatan  from karyawan k
                inner join jabatan j on k.jabatan = j.kode
                where status = 1";

        $d_conf = $m_conf->hydrateRaw($sql);
        return $d_conf->count() > 0 ? $d_conf->toArray() : null;
    }

    public function add_form()
    {
        $data['data']             = null;
        $data['kategori_aset']    = $this->get_kategori_aset_list();
        $data['unit']             = $this->get_unit_list();
        $data['pic']              = $this->get_pic_list();
        $this->load->view($this->pathView . 'v_form', $data);
    }

    public function edit_form()
    {
        $id = $this->input->get('id');

        $m_aset = new \Model\Storage\MsAset_model();
        $d_aset = $m_aset->where('id', $id)->first();

        $m_penerimaan = new \Model\Storage\PenerimaanAset_model();
        $is_received = $m_penerimaan->where('kode_aset', $d_aset->kode_aset)->exists();
        // cetak_r($is_received, 1);

        $data['data']             = $d_aset->toArray();
        $data['kategori_aset']    = $this->get_kategori_aset_list();
        $data['unit']             = $this->get_unit_list();
        $data['pic']              = $this->get_pic_list();
        $data['data']['is_received'] = $is_received;

        // $data['status_aset']      = $this->check_status_aset($data['data']['kode_aset']);

        $this->load->view($this->pathView . 'v_form', $data);
    }


    public function check_status_aset($kode_aset)
    {
        if (empty($kode_aset)) {
            return 0;
        }

        $countKomersial = \Model\Storage\PenyusutanKomersialAset_model::where('kode_aset', $kode_aset)
            ->where('status', 1)
            ->count();


            //  cetak_r($countKomersial, 1);


        $countFiskal = \Model\Storage\PenyusutanFiskalAset_model::where('kode_aset', $kode_aset)
            ->where('status', 1)
            ->count();

        return ($countKomersial > 0 || $countFiskal > 0) ? 1 : 0;
    }

    private function generateKodeAsset($id_kategori, $tgl_perolehan = null)
    {
        if (empty($id_kategori)) return null;

        $m_kategori = new \Model\Storage\MsAsetKategori_model();
        $kategori = $m_kategori->where('id', $id_kategori)->first();
        
        $prefix = $kategori ? strtoupper(trim($kategori->kategori_kode)) : 'AST';
        $year   = !empty($tgl_perolehan) ? date('y', strtotime($tgl_perolehan)) : date('y');
        $month  = !empty($tgl_perolehan) ? date('m', strtotime($tgl_perolehan)) : date('m');

        $m_aset = new \Model\Storage\MsAset_model();
        
        $pattern = $prefix . '-' . $year . $month . '-%';
        $rows = $m_aset->whereRaw('kode_aset LIKE ?', [$pattern])->get();

        $lastSequence = 0;
        foreach ($rows as $row) {
            $kode = trim($row->kode_aset);
            if (empty($kode)) continue;

            if (preg_match('/^' . preg_quote($prefix, '/') . '-' . $year . $month . '-(\d{4})$/i', $kode, $parts)) {
                $sequence = (int) $parts[1];
                if ($sequence > $lastSequence) {
                    $lastSequence = $sequence;
                }
            }
        }

        $sequence = $lastSequence + 1;
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }

    public function save_data()
    {
        $params = $this->input->post('params');

        try {
            $m_aset = new \Model\Storage\MsAset_model();

            $id_kategori        = trim($params['id_kategori']);
            $tgl_perolehan      = trim($params['tgl_perolehan']);
            $deskripsi          = trim($params['deskripsi_aset']);
            $nilai_perolehan    = !empty($params['nilai_perolehan']) ? (float) str_replace('.', '', $params['nilai_perolehan']) : 0;

            if (empty($id_kategori) || empty($tgl_perolehan) || empty($deskripsi) || $nilai_perolehan <= 0) {
                $this->result['message'] = 'Kategori, Tanggal Perolehan, Deskripsi, dan Nilai Perolehan wajib diisi.';
            } else {
                $attachmentName = null;
                if (!empty($_FILES['file_dokumen']['name'])) {
                    $file           = $_FILES['file_dokumen'];
                    $allowedTypes   = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                    $maxSize        = 5 * 1024 * 1024;

                    if (!in_array($file['type'], $allowedTypes) || $file['size'] > $maxSize) {
                        $this->result['message'] = 'Format file harus PDF/JPG/PNG dan maksimal 5MB.';
                        display_json($this->result);
                        return;
                    }

                    $uploadPath = FCPATH . 'uploads/aset/'; 
                    if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);

                    $ext            = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $hash           = hash('sha256', time() . $file['name'] . uniqid('', true));
                    $attachmentName = $hash . '.' . $ext;

                    if (!move_uploaded_file($file['tmp_name'], $uploadPath . $attachmentName)) {
                        throw new \Exception('Gagal mengupload file ke server.');
                    }
                }

                $kode_aset = $this->generateKodeAsset($id_kategori, $tgl_perolehan);

                $m_aset->kode_aset       = $kode_aset;
                $m_aset->id_kategori     = $id_kategori;
                $m_aset->deskripsi_aset  = $deskripsi;
                $m_aset->document_no      = trim($params['document_no']);
                $m_aset->tgl_perolehan    = $tgl_perolehan;
                $m_aset->nilai_perolehan  = $nilai_perolehan;
                $m_aset->unit_pengguna    = trim($params['unit_pengguna']);
                // $m_aset->lokasi_pengguna  = trim($params['lokasi_pengguna']);
                // $m_aset->pic              = trim($params['pic']);
                $m_aset->keterangan       = trim($params['keterangan']);
                $m_aset->attachment       = $attachmentName;
                // cetak_r($m_aset, 1);
                $m_aset->save();
                
                
                if (!empty($kode_aset)) {
                    $this->syncKomersial($kode_aset, $id_kategori, $tgl_perolehan, $nilai_perolehan);
                    $this->syncFiskal($kode_aset, $id_kategori, $tgl_perolehan, $nilai_perolehan);
                    
                }

                $m_aset->load(['penyusutan_komersial_aset', 'penyusutan_fiskal_aset']);
                $userNama      = $this->userdata['detail_user']['nama_detuser'] ?? 'System';
                $deskripsi_log = "Data aset {$kode_aset} di-submit oleh {$userNama}";
                Modules::run('base/event/save', $m_aset, $deskripsi_log, null, $m_aset->id, null);

                // $userNama      = $this->userdata['detail_user']['nama_detuser'] ?? 'System';
                // $deskripsi_log = "Data aset {$kode_aset} di-submit oleh {$userNama}";
                // Modules::run('base/event/save', $m_aset, $deskripsi_log, null, $m_aset->id, null);

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


    private function syncKomersial($kode_aset, $id_kategori, $tgl_perolehan, $nilai_perolehan)
    {
        try {
            if (empty($kode_aset) || empty($id_kategori)) {
                throw new \Exception("Kode aset atau ID kategori kosong.");
            }

            $kategori = \Model\Storage\MsAsetKategori_model::find($id_kategori);

            if (!$kategori) {
                throw new \Exception("Kategori dengan ID {$id_kategori} tidak ditemukan.");
            }

            $masa_bulan = (int) $kategori->masa_manfaat_komersial;

            if ($masa_bulan <= 0) {
                throw new \Exception("Masa manfaat komersial pada kategori ini kosong/nol.");
            }
        
            $jadwal = new \Model\Storage\PenyusutanKomersialAset_model();
            $jadwal->where('kode_aset', $kode_aset)->delete();
            
            $beban_per_bulan = round($nilai_perolehan / $masa_bulan, 2);
            $akumulasi = 0;

            for ($i = 1; $i <= $masa_bulan; $i++) {
                $row = new \Model\Storage\PenyusutanKomersialAset_model();
            
                $bulan_ke = $i - 1; 
                $tgl_jatuh_tempo = date('Y-m-d H:i:s', strtotime("+{$bulan_ke} month", strtotime($tgl_perolehan)));

                $akumulasi += $beban_per_bulan;
                $nilai_buku  = $nilai_perolehan - $akumulasi;

                if ($i == $masa_bulan && $nilai_buku < 0) {
                    $akumulasi -= $beban_per_bulan; 
                    $beban_per_bulan = $nilai_perolehan - $akumulasi; 
                    $akumulasi += $beban_per_bulan;
                    $nilai_buku = 0;
                }

                $row->kode_aset              = $kode_aset;
                $row->kode_komersial         = $kode_aset . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
                $row->tanggal_jatuh_tempo    = $tgl_jatuh_tempo;
                $row->beban_penyusutan       = round($beban_per_bulan, 2);
                $row->akumulasi_penyusutan   = round($akumulasi, 2);
                $row->nilai_buku_akhir       = round($nilai_buku, 2);
                $row->status                 = 0; 
                
                $row->save();
            }
        } catch (\Exception $e) {
            throw new \Exception("Gagal sync komersial: " . $e->getMessage());
        }
    }

    public function edit_data()
    {
        $params = $this->input->post('params');

        try {
            $m_aset = new \Model\Storage\MsAset_model();
            
            $id_kategori     = trim($params['id_kategori']);
            $tgl_perolehan   = trim($params['tgl_perolehan']);
            $deskripsi       = trim($params['deskripsi_aset']);
            $unit_pengguna   = trim($params['unit_pengguna']);
            $nilai_perolehan = !empty($params['nilai_perolehan']) ? (float) str_replace('.', '', $params['nilai_perolehan']) : 0;

            if (empty($id_kategori) || empty($tgl_perolehan) || empty($deskripsi) || $nilai_perolehan <= 0) {
                $this->result['message'] = 'Kategori, Tanggal Perolehan, Deskripsi, dan Nilai Perolehan wajib diisi.';
                display_json($this->result); return;
            }

            $current = $m_aset->where('id', $params['id'])->first();
            if (!$current) {
                $this->result['message'] = 'Data aset tidak ditemukan.';
                display_json($this->result); return;
            }

            $oldKodeAsset    = !empty($current->kode_aset) ? trim($current->kode_aset) : '';
            $oldIdKategori   = !empty($current->id_kategori) ? trim($current->id_kategori) : '';
            $oldTglPerolehan = !empty($current->tgl_perolehan) ? date('Y-m-d', strtotime($current->tgl_perolehan)) : '';
            $oldAttachment   = !empty($current->attachment) ? $current->attachment : null;

            $kode_aset = $oldKodeAsset;
            if ($oldIdKategori != $id_kategori || $oldTglPerolehan != date('Y-m-d', strtotime($tgl_perolehan))) {
                $kode_aset = $this->generateKodeAsset($id_kategori, $tgl_perolehan);
            }

            if ($m_aset->whereRaw('LOWER(LTRIM(RTRIM(kode_aset))) = ?', [strtolower(trim($kode_aset))])->where('id', '!=', $params['id'])->first()) {
                $this->result['message'] = 'Kode aset sudah digunakan oleh data lain.';
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

                $uploadPath = FCPATH . 'uploads/aset/'; 
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
                'kode_aset'      => $kode_aset,
                'id_kategori'     => $id_kategori,
                'deskripsi_aset' => $deskripsi,
                'document_no'     => trim($params['document_no']),
                'tgl_perolehan'   => $tgl_perolehan,
                'unit_pengguna'   => $unit_pengguna,
                'nilai_perolehan' => $nilai_perolehan,
                // 'lokasi_pengguna'    => trim($params['lokasi_pengguna']),
                // 'pic'             => trim($params['pic']),
                'keterangan'      => trim($params['keterangan']),
                'attachment'      => $attachmentName,
            ];

            $m_aset->where('id', $params['id'])->update($data_update);

            if (!empty($oldKodeAsset)) {
                (new \Model\Storage\PenyusutanKomersialAset_model())->where('kode_aset', $oldKodeAsset)->delete();
            }

            if (!empty($kode_aset)) {
                $this->syncKomersial($kode_aset, $id_kategori, $tgl_perolehan, $nilai_perolehan);
                $this->syncFiskal($kode_aset, $id_kategori, $tgl_perolehan, $nilai_perolehan);
            }

            $model_for_log  = $m_aset->with(['penyusutan_komersial_aset', 'penyusutan_fiskal_aset'])->where('id', $params['id'])->first();
            $userNama       = $this->userdata['detail_user']['nama_detuser'] ?? 'System';
            $deskripsi_log  = "Update data aset {$kode_aset} oleh {$userNama}";
            Modules::run('base/event/update', $model_for_log, $deskripsi_log, 'ms_aset', $params['id'], null);

            $this->result['status']  = 1;
            $this->result['message'] = 'Data berhasil diubah';

        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        } catch (\Exception $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete_data()
    {
        $id = $this->input->post('params');

        try {
            $m_aset         = new \Model\Storage\MsAset_model();
            $current        = $m_aset->where('id', $id)->first();
            
            if (!$current) {
                $this->result['message'] = 'Data aset tidak ditemukan.';
                display_json($this->result);
                return;
            }

            $kode_aset = $current->kode_aset;

            $dt_log = $m_aset->with(['penyusutan_komersial_aset', 'penyusutan_fiskal_aset'])->where('id', $id)->first();
            if (!$dt_log) {
                $this->result['message'] = 'Data aset tidak ditemukan.';
                display_json($this->result);
                return;

            }

            $kdAsset         = !empty($dt_log->kode_aset) ? trim($dt_log->kode_aset) : '';
            $userNama       = $this->userdata['detail_user']['nama_detuser'] ?? 'System';
            $deskripsi_log  = "di-delete oleh {$userNama}";
            Modules::run('base/event/delete', $dt_log, $deskripsi_log, 'ms_aset', $id, null);

            
        
            \Model\Storage\PenyusutanKomersialAset_model::where('kode_aset', $kode_aset)->delete();
            \Model\Storage\PenyusutanFiskalAset_model::where('kode_aset', $kode_aset)->delete();

            if (!empty($current->attachment)) {
                $uploadPath = FCPATH . 'uploads/aset/' . $current->attachment;
                if (file_exists($uploadPath)) {
                    @unlink($uploadPath);
                }
            }

        
            $m_aset->where('id', $id)->delete();

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil dihapus';
            
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal (Database) : ' . $e->getMessage();
        } catch (\Exception $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        }

        display_json($this->result);
    }


    public function export_data()
    {

        $forceString = function($val) {
            if (is_array($val)) {
                return isset($val[0]) ? trim((string)$val[0]) : '';
            }
            return trim((string)($val ?? ''));
        };

        // cetak_r($_GET, 1);

        $type         = strtolower($forceString($this->input->get('type')));
        $id_kategori  = $forceString($this->input->get('id_kategori'));
        $tanggal_mulai = $forceString($this->input->get('tanggal_mulai'));
        $search       = $forceString($this->input->get('search'));

        $m_aset = new \Model\Storage\MsAset_model();
        $query = $m_aset
            ->select('ms_aset.*', 
                    'ms_aset_kategori.kategori_name as nama_kategori', 
                    'ms_aset_kategori.masa_manfaat_komersial', 
                    'ms_aset_kategori.masa_manfaat_fiskal',
                    'ms_kelompok.nama_kelompok',
                    'ms_kelompok.trf_garis_lurus',
                    'ms_kelompok.trf_saldo_menurun')
            ->leftJoin('ms_aset_kategori', 'ms_aset_kategori.id', '=', 'ms_aset.id_kategori')
            ->leftJoin('ms_kelompok', 'ms_kelompok.id', '=', 'ms_aset_kategori.id_kelompok');

        if (!empty($id_kategori)) {
            $query->where('ms_aset_kategori.kategori_name', $id_kategori);
            // $query->where('ms_aset.id_kategori', $id_kategori);
        }
        // if (!empty($lokasi_pengguna)) {
        //     $query->whereRaw('LOWER(LTRIM(RTRIM(ms_aset.lokasi_pengguna))) LIKE ?', ['%' . strtolower($lokasi_pengguna) . '%']);
        // }

        if ( !empty($tanggal_mulai) ) {
            $query->where('ms_aset.tgl_perolehan', $tanggal_mulai);
        }

        if (!empty($search)) {
            $like = '%' . $search . '%';
            $query->where(function($q) use ($like) {
                $q->whereRaw('LOWER(LTRIM(RTRIM(ms_aset.kode_aset))) LIKE ?', [strtolower($like)])
                ->orWhereRaw('LOWER(LTRIM(RTRIM(ms_aset.deskripsi_aset))) LIKE ?', [strtolower($like)]);
            });
        }

        $rows = $query->orderBy('ms_aset.id', 'desc')->get()->toArray();

        // cetak_r($rows, 1);

        $kd_asset = [];
        foreach ($rows as $d) {
            if (!empty($d['kode_aset'])) {
                $kd_asset[] = $d['kode_aset'];
            }
        }

        $map_komersial = [];
        $map_fiskal = [];

        if (!empty($kd_asset)) {
            $implode_kd_asset = "'" . implode("','", $kd_asset) . "'";
            $m_conf = new \Model\Storage\Conf();
            
            $sql_komersial = "select kode_aset, kode_komersial, tanggal_jatuh_tempo, beban_penyusutan, akumulasi_penyusutan, nilai_buku_akhir 
                            from penyusutan_komersial_aset 
                            where kode_aset in (" . $implode_kd_asset . ") 
                            order by kode_aset, kode_komersial asc";
            $data_komersial = $m_conf->hydrateRaw($sql_komersial)->toArray();
            foreach ($data_komersial as $d) {
                $map_komersial[$d['kode_aset']][] = $d;
            }

            $sql_fiskal = "select kode_aset, kode_fiskal, tanggal_jatuh_tempo, beban_penyusutan, akumulasi_penyusutan, nilai_buku_akhir 
                        from penyusutan_fiskal_aset 
                        where kode_aset in (" . $implode_kd_asset . ") 
                        order by kode_aset, kode_fiskal asc";
            $data_fiskal = $m_conf->hydrateRaw($sql_fiskal)->toArray();
            foreach ($data_fiskal as $d) {
                $map_fiskal[$d['kode_aset']][] = $d;
            }
        }

        if (ob_get_level()) ob_end_clean();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Master Aset');

        $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $nama_bulan_singkat = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];


        $base_year = date('Y');
        if (!empty($rows)) {
            $years = array_map(function($r) {
                return !empty($r['tgl_perolehan']) ? (int)date('Y', strtotime($r['tgl_perolehan'])) : 0;
            }, $rows);
            $base_year = min(array_filter($years));
        }

        $max_month_index = 0;
        foreach ($rows as $row) {
            if (!empty($row['tgl_perolehan'])) {
                $kom_data = $map_komersial[$row['kode_aset']] ?? [];
                $jumlah_bulan_aset = count($kom_data);
                
                if ($jumlah_bulan_aset > 0) {
                    $asset_year = (int) date('Y', strtotime($row['tgl_perolehan']));
                    $asset_month = (int) date('n', strtotime($row['tgl_perolehan']));
                    
                    $start_month_index = (($asset_year - $base_year) * 12) + ($asset_month - 1);
                    $end_month_index = $start_month_index + $jumlah_bulan_aset - 1;
                    
                    if ($end_month_index > $max_month_index) {
                        $max_month_index = $end_month_index;
                    }
                }
            }
        }

        $jumlah_tahun       = ceil(($max_month_index + 1) / 12);
        $jumlah_tahun       = max(4, $jumlah_tahun);
        $tahun_referensi    = $base_year;

        $row_grouping   = 2;
        $row_detail_1   = 3;
        $row_detail_2   = 4;
        $row_data       = 5;

        $col_data_start     = 2;
        $data_asset_start   = $col_data_start;
        $data_asset_end     = $data_asset_start + 13;

        $sep1_col           = $data_asset_end + 1;

        $kom_per_tahun  = 15;
        $total_kom      = $kom_per_tahun * $jumlah_tahun;
        $kom_start      = $sep1_col + 1;
        $kom_end        = $kom_start + $total_kom - 1;

        $sep2_col   = $kom_end + 1;

        $total_fis  = 48 * 2;
        $fis_start  = $sep2_col + 1;
        $fis_end    = $fis_start + $total_fis - 1;

        $colLetter = function($col) {
            return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        };

        $sheet->getRowDimension(1)->setRowHeight(10);

        $sheet->setCellValue('B' . $row_grouping, 'Data Asset');
        $sheet->mergeCells('B' . $row_grouping . ':' . $colLetter($data_asset_end) . $row_grouping);
        $sheet->getStyle('B' . $row_grouping . ':' . $colLetter($data_asset_end) . $row_grouping)
            ->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FFFFFF');
        $sheet->getStyle('B' . $row_grouping . ':' . $colLetter($data_asset_end) . $row_grouping)
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('4472C4');
        $sheet->getStyle('B' . $row_grouping . ':' . $colLetter($data_asset_end) . $row_grouping)
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $sheet->setCellValue($colLetter($kom_start) . $row_grouping, 'Table Komersial');
        $sheet->mergeCells($colLetter($kom_start) . $row_grouping . ':' . $colLetter($kom_end) . $row_grouping);
        $sheet->getStyle($colLetter($kom_start) . $row_grouping . ':' . $colLetter($kom_end) . $row_grouping)
            ->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FFFFFF');
        $sheet->getStyle($colLetter($kom_start) . $row_grouping . ':' . $colLetter($kom_end) . $row_grouping)
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('70AD47');
        $sheet->getStyle($colLetter($kom_start) . $row_grouping . ':' . $colLetter($kom_end) . $row_grouping)
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $sheet->setCellValue($colLetter($fis_start) . $row_grouping, 'Fiskal');
        $sheet->mergeCells($colLetter($fis_start) . $row_grouping . ':' . $colLetter($fis_end) . $row_grouping);
        $sheet->getStyle($colLetter($fis_start) . $row_grouping . ':' . $colLetter($fis_end) . $row_grouping)
            ->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FFFFFF');
        $sheet->getStyle($colLetter($fis_start) . $row_grouping . ':' . $colLetter($fis_end) . $row_grouping)
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ED7D31');
        $sheet->getStyle($colLetter($fis_start) . $row_grouping . ':' . $colLetter($fis_end) . $row_grouping)
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $headers_detail = ['Kode Aset', 'Kategori', 'Deskripsi', 'No. Bukti', 'Tgl Perolehan', 
                        'Nilai Perolehan', 'Unit Pengguna', 'Lokasi Pengguna', 'PIC', 'Kelompok', 'Trf Garis Lurus (%)', 'Trf Saldo Menurun (%)', 'Masa Manfaat Komersial', 'Masa Manfaat Fiskal'];
        $sheet->fromArray($headers_detail, null, 'B' . $row_detail_1);
        $sheet->getStyle('B' . $row_detail_1 . ':' . $colLetter($data_asset_end) . $row_detail_1)
            ->getFont()->setBold(true);
        $sheet->getStyle('B' . $row_detail_1 . ':' . $colLetter($data_asset_end) . $row_detail_1)
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('D9EAF7');
        $sheet->getStyle('B' . $row_detail_1 . ':' . $colLetter($data_asset_end) . $row_detail_1)
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        for ($t = 0; $t < $jumlah_tahun; $t++) {
            $tahun = $tahun_referensi + $t;
            $col_awal = $kom_start + ($t * $kom_per_tahun);
            $col_akhir = $col_awal + $kom_per_tahun - 1;
            
            $sheet->setCellValue($colLetter($col_awal) . $row_detail_1, $tahun);
            $sheet->mergeCells($colLetter($col_awal) . $row_detail_1 . ':' . $colLetter($col_akhir) . $row_detail_1);
            $sheet->getStyle($colLetter($col_awal) . $row_detail_1 . ':' . $colLetter($col_akhir) . $row_detail_1)
                ->getFont()->setBold(true);
            $sheet->getStyle($colLetter($col_awal) . $row_detail_1 . ':' . $colLetter($col_akhir) . $row_detail_1)
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('A9D18E');
            $sheet->getStyle($colLetter($col_awal) . $row_detail_1 . ':' . $colLetter($col_akhir) . $row_detail_1)
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        }

        $sheet->getStyle($colLetter($fis_start) . $row_detail_1 . ':' . $colLetter($fis_end) . $row_detail_1)
        ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB('ED7D31'); 

        $col_idx = $kom_start;
        for ($t = 0; $t < $jumlah_tahun; $t++) {
            $tahun = $tahun_referensi + $t;
            for ($b = 0; $b < 12; $b++) {
                $sheet->setCellValueByColumnAndRow($col_idx, $row_detail_2, $nama_bulan[$b]);
                $col_idx++;
            }
            $sheet->setCellValueByColumnAndRow($col_idx, $row_detail_2, "Beban Penyusutan {$tahun}");
            $col_idx++;
            $sheet->setCellValueByColumnAndRow($col_idx, $row_detail_2, "Akumulasi Penyusutan s.d {$tahun}");
            $col_idx++;
            $sheet->setCellValueByColumnAndRow($col_idx, $row_detail_2, "Nilai Buku {$tahun}");
            $col_idx++;
        }

        $col_idx = $fis_start;
        for ($m = 1; $m <= 48; $m++) {
            $bulan_idx = ($m - 1) % 12;
            $tahun_fis = $tahun_referensi + floor(($m - 1) / 12);
            $tahun_2digit = substr($tahun_fis, 2);
            
            $sheet->setCellValueByColumnAndRow($col_idx, $row_detail_2, "Beban Penyusutan {$nama_bulan_singkat[$bulan_idx]} {$tahun_2digit}");
            $col_idx++;
            $sheet->setCellValueByColumnAndRow($col_idx, $row_detail_2, "Nilai Buku {$nama_bulan_singkat[$bulan_idx]} {$tahun_2digit}");
            $col_idx++;
        }

        $sheet->getStyle('B' . $row_detail_2 . ':' . $colLetter($fis_end) . $row_detail_2)
            ->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle('B' . $row_detail_2 . ':' . $colLetter($fis_end) . $row_detail_2)
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('D9EAF7');
        $sheet->getStyle('B' . $row_detail_2 . ':' . $colLetter($fis_end) . $row_detail_2)
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $sheet->getRowDimension($row_grouping)->setRowHeight(25);
        $sheet->getRowDimension($row_detail_1)->setRowHeight(20);
        $sheet->getRowDimension($row_detail_2)->setRowHeight(35);

        $currentRow = $row_data;
        $lastDataRow = $currentRow;

        foreach ($rows as $row) {
            $lastDataRow = $currentRow;
            
            $colIndex = $data_asset_start;
            $sheet->setCellValueExplicitByColumnAndRow($colIndex++, $currentRow, $row['kode_aset'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($colIndex++, $currentRow, $row['nama_kategori'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($colIndex++, $currentRow, $row['deskripsi_aset'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($colIndex++, $currentRow, $row['document_no'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, isset($row['tgl_perolehan']) ? date('Y-m-d', strtotime($row['tgl_perolehan'])) : '-');
            $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, (float)($row['nilai_perolehan'] ?? 0));
            $sheet->setCellValueExplicitByColumnAndRow($colIndex++, $currentRow, $row['unit_pengguna'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($colIndex++, $currentRow, $row['nama_wilayah'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($colIndex++, $currentRow, $row['nama_karyawan'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($colIndex++, $currentRow, $row['nama_kelompok'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($colIndex++, $currentRow, $row['trf_garis_lurus'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($colIndex++, $currentRow, $row['trf_saldo_menurun'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($colIndex++, $currentRow, $row['masa_manfaat_komersial'] . ' (Bln)' ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow($colIndex++, $currentRow, $row['masa_manfaat_fiskal'] . ' (Bln)' ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $bulan_perolehan    = !empty($row['tgl_perolehan']) ? (int) date('n', strtotime($row['tgl_perolehan'])) : 1;
            $offset_bulan       = $bulan_perolehan - 1;

            $colIndex = $kom_start;
            $data_kom = $map_komersial[$row['kode_aset']] ?? [];
            
            for ($t = 0; $t < $jumlah_tahun; $t++) {
                $total_beban_tahun = 0;
                $akumulasi_akhir_tahun = 0;
                $nb_akhir_tahun = 0;
                
                for ($b = 0; $b < 12; $b++) {
                    $bulan_absolut = ($t * 12) + $b;
                    $idx_data = $bulan_absolut - $offset_bulan;
                    
                    $d = ($idx_data >= 0 && isset($data_kom[$idx_data])) ? $data_kom[$idx_data] : null;
                    
                    $beban = $d ? (float)$d['beban_penyusutan'] : 0;
                    $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, $beban);
                    $total_beban_tahun += $beban;
                    
                    if ($d) {
                        $akumulasi_akhir_tahun = (float)$d['akumulasi_penyusutan'];
                        $nb_akhir_tahun = (float)$d['nilai_buku_akhir'];
                    }
                }
                
                $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, round($total_beban_tahun, 2));
                $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, $akumulasi_akhir_tahun);
                $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, $nb_akhir_tahun);
            }

            $colIndex = $fis_start;
            $data_fis = $map_fiskal[$row['kode_aset']] ?? [];
            
            for ($m = 1; $m <= 48; $m++) {
                $idx_data = ($m - 1) - $offset_bulan;
                $d = ($idx_data >= 0 && isset($data_fis[$idx_data])) ? $data_fis[$idx_data] : null;
                
                $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, $d ? (float)$d['beban_penyusutan'] : 0);
                $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, $d ? (float)$d['nilai_buku_akhir'] : 0);
            }

            $currentRow++;
        }

        $sheet->getStyle('F' . $row_data . ':F' . $lastDataRow)->getNumberFormat()->setFormatCode('yyyy-mm-dd');
        $sheet->getStyle('G' . $row_data . ':G' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle($colLetter($kom_start) . $row_data . ':' . $colLetter($fis_end) . $lastDataRow)
            ->getNumberFormat()->setFormatCode('#,##0.00');

        $borderThin = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ];

        $sheet->getStyle('B' . $row_grouping . ':' . $colLetter($data_asset_end) . $lastDataRow)->applyFromArray($borderThin);
        $sheet->getStyle($colLetter($kom_start) . $row_grouping . ':' . $colLetter($kom_end) . $lastDataRow)->applyFromArray($borderThin);
        $sheet->getStyle($colLetter($fis_start) . $row_grouping . ':' . $colLetter($fis_end) . $lastDataRow)->applyFromArray($borderThin);

        $sheet->getStyle($colLetter($sep1_col) . '1:' . $colLetter($sep1_col) . ($lastDataRow + 5))
            ->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE,
                    ],
                ],
            ]);

        $sheet->getStyle($colLetter($sep2_col) . '1:' . $colLetter($sep2_col) . ($lastDataRow + 5))
            ->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE,
                    ],
                ],
            ]);


        $sheet->getColumnDimension('A')->setWidth(3);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(50);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(14); 
        $sheet->getColumnDimension('G')->setWidth(20); 
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(20);
        $sheet->getColumnDimension('K')->setWidth(20);
        $sheet->getColumnDimension('L')->setWidth(20);
        $sheet->getColumnDimension('M')->setWidth(20);
        $sheet->getColumnDimension('N')->setWidth(20);
        $sheet->getColumnDimension('O')->setWidth(20);

        $sheet->getColumnDimension($colLetter($sep1_col))->setWidth(3);
        $sheet->getColumnDimension($colLetter($sep2_col))->setWidth(3);

        for ($i = $kom_start; $i <= $fis_end; $i++) {
            $is_summary_kom = false;
            if ($i >= $kom_start && $i <= $kom_end) {
                $pos_in_kom = $i - $kom_start;
                if ($pos_in_kom % 15 >= 12) { 
                    $is_summary_kom = true;
                }
            }
            
            if ($is_summary_kom) {
                $sheet->getColumnDimension($colLetter($i))->setWidth(20); 
            } else {
                $sheet->getColumnDimension($colLetter($i))->setWidth(14); 
            }
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="MASTER_ASET_FULL_' . date('Y-m-d_His') . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function detail_data()
    {
        $id = $this->input->post('params');
        $viewData = [
            'data'          => [],
            'komersial'     => [],
            'fiskal'        => [],
        ];

        try {
            $m_aset = new \Model\Storage\MsAset_model();
            $d_aset = $m_aset->select(
                    'ms_aset.*',
                    'ms_aset_kategori.kategori_name',
                    'ms_aset_kategori.masa_manfaat_komersial',
                    'ms_aset_kategori.masa_manfaat_fiskal',
                    'ms_kelompok.nama_kelompok',
                    'ms_kelompok.trf_garis_lurus',
                    'ms_kelompok.trf_saldo_menurun'
                )
                ->leftJoin('ms_aset_kategori', 'ms_aset_kategori.id', '=', 'ms_aset.id_kategori')
                ->leftJoin('ms_kelompok', 'ms_kelompok.id', '=', 'ms_aset_kategori.id_kelompok')
                ->where('ms_aset.id', $id)
                ->first();

            // cetak_r($d_aset, 1);

            if ( $d_aset ) {
                $viewData['data'] = $d_aset->toArray();

                if ( !empty($d_aset->kode_aset) ) {
                    $m_conf = new \Model\Storage\Conf();

                    $sql_komersial  = "select id, kode_aset, kode_komersial, tanggal_jatuh_tempo, beban_penyusutan, akumulasi_penyusutan, nilai_buku_akhir, status from penyusutan_komersial_aset where kode_aset = '" . trim($d_aset->kode_aset) . "'";
                    $komersial      = $m_conf->hydrateRaw($sql_komersial);

                    $sql_fiskal     = "select id, kode_aset, kode_fiskal, tanggal_jatuh_tempo, beban_penyusutan, akumulasi_penyusutan, nilai_buku_akhir, status from penyusutan_fiskal_aset where kode_aset = '" . trim($d_aset->kode_aset) . "'";
                    $fiskal      = $m_conf->hydrateRaw($sql_fiskal);
                    

                    if ( $komersial && method_exists($komersial, 'count') && $komersial->count() > 0 ) {
                        $viewData['komersial'] = $komersial->toArray();
                    }

                    if ( $fiskal && method_exists($fiskal, 'count') && $fiskal->count() > 0 ) {
                        $viewData['fiskal'] = $fiskal->toArray();
                    }
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $viewData['data']       = [];
            $viewData['komersial']   = [];
            $viewData['fiskal']   = [];
        }

        // cetak_r($viewData);die;

        echo $this->load->view($this->pathView . 'v_detail_data', $viewData, TRUE);
    }


    private function syncFiskal($kode_aset, $id_kategori, $tgl_perolehan, $nilai_perolehan)
    {
        try {
            if (empty($kode_aset) || empty($id_kategori)) {
                throw new \Exception("Kode aset atau ID kategori kosong.");
            }

            $kategori = \Model\Storage\MsAsetKategori_model::find($id_kategori);

            if (!$kategori) {
                throw new \Exception("Kategori dengan ID {$id_kategori} tidak ditemukan.");
            }

            if (empty($kategori->id_kelompok)) {
                throw new \Exception("Kategori ini belum memiliki Kelompok Aset yang dipilih.");
            }

            $kelompok = \Model\Storage\MsKelompok_model::find($kategori->id_kelompok);

            if (!$kelompok) {
                throw new \Exception("Data Kelompok Aset tidak ditemukan.");
            }

            $masa_bulan    = (int) $kategori->masa_manfaat_fiskal;       
            $tarif_tahunan = (float) $kelompok->trf_saldo_menurun;  

            if ($masa_bulan <= 0 || $tarif_tahunan <= 0) {
                throw new \Exception("Masa manfaat fiskal (di kategori) atau Tarif Saldo Menurun (di kelompok) kosong/nol.");
            }

            $jadwal = new \Model\Storage\PenyusutanFiskalAset_model();
            $jadwal->where('kode_aset', $kode_aset)->delete();
            
            $akumulasi          = 0;
            $nilai_buku_awal    = $nilai_perolehan;
            $beban_per_bulan    = 0;
            
            $tahun_perolehan    = (int) date('Y', strtotime($tgl_perolehan));
            $tahun_kalkulasi    = $tahun_perolehan; 

            for ($i = 1; $i <= $masa_bulan; $i++) {
                $row                = new \Model\Storage\PenyusutanFiskalAset_model();
                $bulan_ke           = $i - 1; 
                $tgl_periode        = strtotime("+{$bulan_ke} month", strtotime($tgl_perolehan));
                $tgl_jatuh_tempo    = date('Y-m-d H:i:s', $tgl_periode);
                $tahun_periode      = (int) date('Y', $tgl_periode);

                if ($tahun_periode > $tahun_kalkulasi) {
                    $beban_tahunan = $nilai_buku_awal * ($tarif_tahunan / 100);
                    $beban_per_bulan = $beban_tahunan / 12;
                    $tahun_kalkulasi = $tahun_periode;
                    
                } elseif ($i == 1) {
            
                    $beban_tahunan      = $nilai_buku_awal * ($tarif_tahunan / 100);
                    $beban_per_bulan    = $beban_tahunan / 12;
                }

                if ($i == $masa_bulan) {
                    $sisa_yang_harus_disusutkan = $nilai_perolehan - $akumulasi; 
                    $beban_per_bulan            = $sisa_yang_harus_disusutkan; 
                    $akumulasi                  = $nilai_perolehan;
                    $nilai_buku_akhir           = 0;
                } else {
                    $akumulasi          += $beban_per_bulan;
                    $nilai_buku_akhir   = $nilai_perolehan - $akumulasi;
                }

                $row->kode_aset              = $kode_aset;
                $row->kode_fiskal            = $kode_aset . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
                $row->tanggal_jatuh_tempo    = $tgl_jatuh_tempo;
                $row->beban_penyusutan       = round($beban_per_bulan, 2);
                $row->akumulasi_penyusutan   = round($akumulasi, 2);
                $row->nilai_buku_akhir       = round($nilai_buku_akhir, 2);
                $row->status                 = 0; 
                
                $row->save();

                $nilai_buku_awal = $nilai_buku_akhir;
            }
            
        } catch (\Exception $e) {
            throw new \Exception("Gagal sync fiskal: " . $e->getMessage());
        }
    }



    public function generateUlang()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "select * from ms_aset where keterangan = 'inject_data'";

        $d_conf = $m_conf->hydrateRaw($sql);
        $result = $d_conf->count() > 0 ? $d_conf->toArray() : null;

        // cetak_r($result, 1);

        foreach ($result as $d) {
            $kode_aset      = $d['kode_aset'] ?? null;
            $id_kategori    = $d['id_kategori'] ?? null;
            $tgl_perolehan  = $d['tgl_perolehan'] ?? null;
            $nilai_perolehan= $d['nilai_perolehan'] ?? null;

            if (!empty($kode_aset)) {
                try {
                    $this->syncKomersial($kode_aset, $id_kategori, $tgl_perolehan, $nilai_perolehan);
                    $this->syncFiskal($kode_aset, $id_kategori, $tgl_perolehan, $nilai_perolehan);
                } catch (\Exception $e) {
                    echo "Error syncing asset {$kode_aset}: " . $e->getMessage() . "<br>";
                }
            }
        }

        // if (!empty($kode_aset)) {
        //     $this->syncKomersial($kode_aset, $id_kategori, $tgl_perolehan, $nilai_perolehan);
        //     $this->syncFiskal($kode_aset, $id_kategori, $tgl_perolehan, $nilai_perolehan);
        // }
    }

    
}