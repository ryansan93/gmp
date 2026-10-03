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

class PenerimaanAset extends Public_Controller {

    private $pathView = 'aset/penerimaan_aset/';
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
                "assets/aset/penerimaan_aset/js/penerimaan_aset.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/aset/penerimaan_aset/css/penerimaan_aset.css",
            ));

            $data = $this->includes;
            $content['akses']           = $this->hakAkses;
            $content['title_panel']     = 'Penerimaan Aset';
            $content['kategori_aset']   = $this->get_kategori_aset_list();
            $content['unit_pengguna']   = $this->get_unit_list();
            $data['title_menu']         = 'Penerimaan Aset';

            $data['view'] = $this->load->view($this->pathView . 'v_index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function list_data()
    {
        $akses           = hakAkses($this->url);
        $m_penerimaan    = new \Model\Storage\PenerimaanAset_model();
        
        $filter_kategori = trim($this->input->post('id_kategori'));
        $filter_tanggal  = trim($this->input->post('tanggal_mulai'));
        $search          = trim($this->input->post('search'));

        $query = $m_penerimaan
            ->select(
                'penerimaan_aset.*',
                'ma.kode_aset',
                'ma.deskripsi_aset',
                'ma.nilai_perolehan',
                'ma.tgl_perolehan',
                'ma.document_no',
                'ma.attachment as attachment_master',
                'mak.kategori_name as nama_kategori',
                'mak.kategori_kode',
                'karyawan.nama as nama_pic',
                new \Illuminate\Database\Query\Expression('(SELECT TOP 1 nama FROM wilayah WHERE kode = penerimaan_aset.lokasi_pengguna) as nama_unit'),
                
                new \Illuminate\Database\Query\Expression("
                    CASE WHEN EXISTS (
                        SELECT 1 FROM penyusutan_komersial_aset 
                        WHERE penyusutan_komersial_aset.kode_aset = ma.kode_aset 
                        AND penyusutan_komersial_aset.status = 1
                    ) OR EXISTS (
                        SELECT 1 FROM penyusutan_fiskal_aset 
                        WHERE penyusutan_fiskal_aset.kode_aset = ma.kode_aset 
                        AND penyusutan_fiskal_aset.status = 1
                    ) THEN 1 ELSE 0 END AS is_locked
                ")
            )
            ->leftJoin('ms_aset as ma', 'ma.kode_aset', '=', 'penerimaan_aset.kode_aset')
            ->leftJoin('ms_aset_kategori as mak', 'mak.id', '=', 'ma.id_kategori')
            ->leftJoin('karyawan', function ($join) {
                $join->on('karyawan.nik', '=', 'penerimaan_aset.pic')
                    ->where('karyawan.status', '=', '1');
            });

        if (!empty($filter_kategori)) {
            $query->where('mak.kategori_name', $filter_kategori);
        }

        if (!empty($filter_tanggal)) {
            $query->where('penerimaan_aset.tgl_penerimaan', $filter_tanggal);
        }

        if (!empty($search)) {
            $like = '%' . $search . '%';
            
            $query->where(function($q) use ($like) {
                $q->where('penerimaan_aset.kode_penerimaan', 'like', $like)
                ->orWhere('penerimaan_aset.kode_aset', 'like', $like)
                ->orWhere('ma.deskripsi_aset', 'like', $like)
                ->orWhere('mak.kategori_name', 'like', $like)
                ->orWhere('karyawan.nama', 'like', $like);
            });
        }

        $d_aset = $query->orderBy('penerimaan_aset.id', 'desc')->get()->toArray();

        // cetak_r($d_aset, 1);

        $content['akses'] = $akses;
        $content['list']  = $d_aset;
        
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

    private function generateKodePenerimaan()
    {
        $year   = date('Y');
        $month  = date('m');
        $prefix = 'TRM'; // Terima

        $m_penerimaan = new \Model\Storage\PenerimaanAset_model();
        
        $pattern = $prefix . $year . $month . '-%';
        $rows = $m_penerimaan->whereRaw('kode_penerimaan LIKE ?', [$pattern])->get();

        $lastSequence = 0;
        foreach ($rows as $row) {
            $kode = trim($row->kode_penerimaan);
            if (empty($kode)) continue;

            if (preg_match('/^' . preg_quote($prefix, '/') . $year . $month . '-(\d{4})$/i', $kode, $parts)) {
                $sequence = (int) $parts[1];
                if ($sequence > $lastSequence) {
                    $lastSequence = $sequence;
                }
            }
        }

        $sequence = $lastSequence + 1;
        return sprintf('%s%s%s-%04d', $prefix, $year, $month, $sequence);
    }

    private function get_kode_aset_list($current_kode_aset = null)
    {
        $m_aset = new \Model\Storage\MsAset_model();
        
        $sql = "SELECT ma.kode_aset, ma.deskripsi_aset, mak.kategori_name, ma.tgl_perolehan, ma.document_no
                FROM ms_aset ma
                LEFT JOIN ms_aset_kategori mak ON mak.id = ma.id_kategori ";

                if($current_kode_aset == null){
                    $sql .= " WHERE ma.kode_aset NOT IN (SELECT DISTINCT kode_aset FROM penerimaan_aset WHERE kode_aset IS NOT NULL) ";
                }

                $sql .= " ORDER BY ma.kode_aset ASC";

        // cetak_r($sql, 1);
        
        $m_conf = new \Model\Storage\Conf();
        $result = $m_conf->hydrateRaw($sql);
        return $result->count() > 0 ? $result->toArray() : [];
    }

    public function add_form()
    {
        $data['data']             = null;
        $data['kategori_aset']    = $this->get_kategori_aset_list();
        $data['unit']             = $this->get_unit_list();
        $data['pic']              = $this->get_pic_list();
        
        $data['list_kode_aset']   = $this->get_kode_aset_list();
        // cetak_r($data, 1);
        
        
        $this->load->view($this->pathView . 'v_form', $data);
    }

    public function edit_form()
    {
        $id = $this->input->get('id');

        $m_penerimaan = new \Model\Storage\PenerimaanAset_model();
        $d_penerimaan = $m_penerimaan->where('id', $id)->first();

        $data['data']             = $d_penerimaan ? $d_penerimaan->toArray() : null;
        $data['kategori_aset']    = $this->get_kategori_aset_list();
        $data['unit']             = $this->get_unit_list();
        $data['pic']              = $this->get_pic_list();
        
        $current_kode = $d_penerimaan ? $d_penerimaan->kode_aset : null;
        $data['list_kode_aset']   = $this->get_kode_aset_list($current_kode); 

        $this->load->view($this->pathView . 'v_form', $data);
    }

    public function save_data()
    {
        $params = $this->input->post('params');

        // cetak_r($params,1);

        try {
            $m_penerimaan = new \Model\Storage\PenerimaanAset_model();

            $kode_aset        = trim($params['kode_aset']);
            $tgl_penerimaan    = trim($params['tgl_penerimaan']);
            $keterangan_terima = trim($params['bukti_terima']);
            $pic               = trim($params['pic']);
            $lokasi_pengguna   = trim($params['lokasi_pengguna']);
            $no_bukti_terima   = trim($params['no_bukti_terima']);
            

            if (empty($kode_aset) || empty($tgl_penerimaan) || empty($pic) || empty($lokasi_pengguna)) {
                $this->result['message'] = 'Kode Aset, Tanggal Penerimaan, PIC, Lokasi digunakan wajib diisi.';
            } else {

                $m_aset = new \Model\Storage\MsAset_model();
                $aset = $m_aset->where('kode_aset', $kode_aset)->first();
                
                if (!$aset) {
                    $this->result['message'] = 'Kode aset tidak ditemukan di Master Aset.';
                    display_json($this->result);
                    return;
                }

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

                    $uploadPath = FCPATH . 'uploads/penerimaan_aset/'; 
                    if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);

                    $ext            = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $hash           = hash('sha256', time() . $file['name'] . uniqid('', true));
                    $attachmentName = $hash . '.' . $ext;

                    if (!move_uploaded_file($file['tmp_name'], $uploadPath . $attachmentName)) {
                        throw new \Exception('Gagal mengupload file ke server.');
                    }
                }

                $kode_penerimaan = $this->generateKodePenerimaan();

                $m_penerimaan->kode_penerimaan   = $kode_penerimaan;
                $m_penerimaan->kode_aset         = $kode_aset;
                $m_penerimaan->tgl_penerimaan    = $tgl_penerimaan;
                $m_penerimaan->keterangan_terima = !empty($keterangan_terima) ? $keterangan_terima : null;
                $m_penerimaan->pic               = $pic;
                $m_penerimaan->lokasi_pengguna   = $lokasi_pengguna;
                $m_penerimaan->no_bukti_terima   = $no_bukti_terima;
                $m_penerimaan->attachment        = $attachmentName;
                
                $m_penerimaan->save();
               
                $userNama      = $this->userdata['detail_user']['nama_detuser'] ?? 'System';
                $deskripsi_log = "Penerimaan aset {$kode_aset} (No: {$kode_penerimaan}) di-submit oleh {$userNama}";
                Modules::run('base/event/save', $m_penerimaan, $deskripsi_log, null, $m_penerimaan->id, null);

                $this->result['status']  = 1;
                $this->result['message'] = 'Data penerimaan aset berhasil disimpan';
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

        // cetak_r($params, 1);

        try {
            $m_penerimaan = new \Model\Storage\PenerimaanAset_model();
            
            $id                = $params['id'];
            $kode_aset         = trim($params['kode_aset']);
            $tgl_penerimaan    = trim($params['tgl_penerimaan']);
            $keterangan_terima = trim($params['bukti_terima']);
            $pic               = trim($params['pic']);
            $lokasi_pengguna   = trim($params['lokasi_pengguna']);
            $no_bukti_terima   = trim($params['no_bukti_terima']);

            if (empty($kode_aset) || empty($tgl_penerimaan) || empty($pic) || empty($lokasi_pengguna)) {
                $this->result['message'] = 'Kode Aset, Tanggal Penerimaan, PIC, Lokasi digunakan wajib diisi.';
                display_json($this->result); 
                return;
            }

            $current = $m_penerimaan->where('id', $id)->first();
            if (!$current) {
                $this->result['message'] = 'Data penerimaan aset tidak ditemukan.';
                display_json($this->result); 
                return;
            }

            $m_aset = new \Model\Storage\MsAset_model();
            $aset = $m_aset->where('kode_aset', $kode_aset)->first();
            
            if (!$aset) {
                $this->result['message'] = 'Kode aset tidak ditemukan di Master Aset.';
                display_json($this->result);
                return;
            }

            $oldAttachment = !empty($current->attachment) ? $current->attachment : null;
            $attachmentName = $oldAttachment; 

            if (!empty($_FILES['file_dokumen']['name'])) {
                $file = $_FILES['file_dokumen'];
                $allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                $maxSize = 5 * 1024 * 1024;

                if (!in_array($file['type'], $allowedTypes) || $file['size'] > $maxSize) {
                    $this->result['message'] = 'Format file harus PDF/JPG/PNG dan maksimal 5MB.';
                    display_json($this->result); 
                    return;
                }

                $uploadPath = FCPATH . 'uploads/penerimaan_aset/'; 
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
                'kode_aset'         => $kode_aset,
                'tgl_penerimaan'    => $tgl_penerimaan,
                'keterangan_terima' => !empty($keterangan_terima) ? $keterangan_terima : null,
                'pic'               => $pic,
                'no_bukti_terima'   => $no_bukti_terima,
                'lokasi_pengguna'   => $lokasi_pengguna,
                'attachment'        => $attachmentName,
            ];

            $m_penerimaan->where('id', $id)->update($data_update);
    
            $model_for_log = \Model\Storage\PenerimaanAset_model::find($id);

            if ($model_for_log) {
                $userNama      = $this->userdata['detail_user']['nama_detuser'] ?? 'System';
                $deskripsi_log = "Update data penerimaan aset {$current->kode_penerimaan} oleh {$userNama}";
                Modules::run('base/event/update', $model_for_log, $deskripsi_log, 'penerimaan_aset', $params['id'], $current);
            }

            $this->result['status']  = 1;
            $this->result['message'] = 'Data berhasil diubah';

        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal (Database) : ' . $e->getMessage();
        } catch (\Exception $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete_data()
    {
        $id = $this->input->post('params');

        try {
            $m_penerimaan = new \Model\Storage\PenerimaanAset_model();
            $current = $m_penerimaan->where('id', $id)->first();
            
            if (!$current) {
                $this->result['message'] = 'Data penerimaan aset tidak ditemukan.';
                display_json($this->result);
                return;
            }

            $kodePenerimaan = $current->kode_penerimaan;
            
            $dt_log = $m_penerimaan->where('id', $id)->first();
            if (!$dt_log) {
                $this->result['message'] = 'Data penerimaan aset tidak ditemukan.';
                display_json($this->result);
                return;
            }

            $userNama       = $this->userdata['detail_user']['nama_detuser'] ?? 'System';
            $deskripsi_log  = "Delete penerimaan aset {$kodePenerimaan} oleh {$userNama}";
            Modules::run('base/event/delete', $dt_log, $deskripsi_log, 'penerimaan_aset', $id, null);

            if (!empty($current->attachment)) {
                $uploadPath = FCPATH . 'uploads/penerimaan_aset/' . $current->attachment;
                if (file_exists($uploadPath)) {
                    @unlink($uploadPath);
                }
            }

            $m_penerimaan->where('id', $id)->delete();

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

        $type          = strtolower($forceString($this->input->get('type')));
        $id_kategori   = $forceString($this->input->get('id_kategori'));
        $tanggal_mulai = $forceString($this->input->get('tanggal_mulai'));
        $search        = $forceString($this->input->get('search'));

        $m_penerimaan = new \Model\Storage\PenerimaanAset_model();

        $query = $m_penerimaan
            ->select(
                'penerimaan_aset.*',
                'ma.kode_aset',
                'ma.deskripsi_aset',
                'ma.nilai_perolehan',
                'ma.tgl_perolehan',
                'ma.document_no',
                'ma.attachment as attachment_master',
                'mak.kategori_name as nama_kategori',
                'mak.kategori_kode',
                'karyawan.nama as nama_pic',
                new \Illuminate\Database\Query\Expression('(SELECT TOP 1 nama FROM wilayah WHERE kode = penerimaan_aset.lokasi_pengguna) as nama_unit'),
                new \Illuminate\Database\Query\Expression("
                    CASE WHEN EXISTS (
                        SELECT 1 FROM penyusutan_komersial_aset 
                        WHERE penyusutan_komersial_aset.kode_aset = ma.kode_aset 
                        AND penyusutan_komersial_aset.status = 1
                    ) OR EXISTS (
                        SELECT 1 FROM penyusutan_fiskal_aset 
                        WHERE penyusutan_fiskal_aset.kode_aset = ma.kode_aset 
                        AND penyusutan_fiskal_aset.status = 1
                    ) THEN 1 ELSE 0 END AS is_locked
                ")
            )
            ->leftJoin('ms_aset as ma', 'ma.kode_aset', '=', 'penerimaan_aset.kode_aset')
            ->leftJoin('ms_aset_kategori as mak', 'mak.id', '=', 'ma.id_kategori')
            ->leftJoin('karyawan', function ($join) {
                $join->on('karyawan.nik', '=', 'penerimaan_aset.pic')
                    ->where('karyawan.status', '=', '1');
            });

        if (!empty($id_kategori)) {
            $query->where('mak.kategori_name', $id_kategori);
        }

        if (!empty($tanggal_mulai)) {
            $query->where('penerimaan_aset.tgl_penerimaan', $tanggal_mulai);
        }

        if (!empty($search)) {
            $like = '%' . $search . '%';
            $query->where(function($q) use ($like) {
                $q->where('penerimaan_aset.kode_penerimaan', 'like', $like)
                  ->orWhere('penerimaan_aset.kode_aset', 'like', $like)
                  ->orWhere('ma.deskripsi_aset', 'like', $like)
                  ->orWhere('mak.kategori_name', 'like', $like)
                  ->orWhere('karyawan.nama', 'like', $like);
            });
        }

        $d_aset = $query->orderBy('penerimaan_aset.id', 'desc')->get();

        if (ob_get_level()) {
            ob_end_clean();
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Penerimaan Aset');

        $headers = [
            'No',
            'Kode Penerimaan',
            'Kode Aset',
            'Deskripsi Aset',
            'Kategori',
            'Tgl Perolehan',
            'Tgl Penerimaan',
            'No. Faktur',
            'Nilai Perolehan',
            'Unit Pengguna',
            'PIC',
            'Lokasi'
        ];

        $sheet->fromArray($headers, null, 'A1');

        $styleHeader = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4472C4'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1:L1')->applyFromArray($styleHeader);

        $rowNum = 2;
        foreach ($d_aset as $index => $row) {
            $sheet->setCellValue('A' . $rowNum, $index + 1);
            $sheet->setCellValue('B' . $rowNum, $row->kode_penerimaan ?? '-');
            $sheet->setCellValue('C' . $rowNum, $row->kode_aset ?? '-');
            $sheet->setCellValue('D' . $rowNum, $row->deskripsi_aset ?? '-');
            $sheet->setCellValue('E' . $rowNum, $row->nama_kategori ?? '-');
            
            $tgl_perolehan = !empty($row->tgl_perolehan) ? date('Y-m-d', strtotime($row->tgl_perolehan)) : '-';
            $tgl_penerimaan = !empty($row->tgl_penerimaan) ? date('Y-m-d', strtotime($row->tgl_penerimaan)) : '-';
            
            $sheet->setCellValue('F' . $rowNum, $tgl_perolehan);
            $sheet->setCellValue('G' . $rowNum, $tgl_penerimaan);
            $sheet->setCellValue('H' . $rowNum, $row->document_no ?? '-'); 
            $sheet->setCellValue('I' . $rowNum, $row->nilai_perolehan ?? 0);
            $sheet->setCellValue('J' . $rowNum, $row->nama_unit ?? '-');
            $sheet->setCellValue('K' . $rowNum, $row->nama_pic ?? '-');
            $sheet->setCellValue('L' . $rowNum, $row->lokasi_pengguna ?? '-');
            
            $rowNum++;
        }

        $borderThin = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $sheet->getStyle('A1:L' . ($rowNum - 1))->applyFromArray($borderThin);

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('I2:I' . ($rowNum - 1))
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="PENERIMAAN_ASET_' . date('Y-m-d_His') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    public function detail_data()
    {
        $id = $this->input->post('params');

        $viewData = [
            'data' => [],
        ];

        
        try {

            $m_conf = new \Model\Storage\Conf();

            $sql = "SELECT 
                        pa.kode_penerimaan,
                        pa.tgl_penerimaan,
                        pa.no_bukti_terima,
                        pa.keterangan_terima,
                        pa.attachment as file_penerimaan,
                        ma.attachment as file_pembelian,
                        ma.tgl_perolehan,
                        ma.kode_aset,
                        ma.nilai_perolehan,
                        ma.document_no as bukti_pembelian,
                        k.nama AS nama_karyawan,
                        w.nama as nama_wilayah
                    FROM penerimaan_aset pa
                    INNER JOIN ms_aset ma ON pa.kode_aset = ma.kode_aset
                    LEFT JOIN karyawan k ON pa.pic = k.nik AND k.status = 1
                    OUTER APPLY (
                        SELECT TOP 1 *
                        FROM wilayah w
                        WHERE w.kode = pa.lokasi_pengguna
                    ) w
                    where pa.id = ". $id ;

            // cetak_r($sql, 1);

            $d_conf = $m_conf->hydrateRaw($sql);
            $d_penerimaan =  $d_conf->count() > 0 ? $d_conf->toArray() : null;


            if ($d_penerimaan) {
                $viewData['data'] = $d_penerimaan;
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $viewData['data'] = [];
        }

        echo $this->load->view($this->pathView . 'v_detail_data', $viewData, TRUE);
    }

}