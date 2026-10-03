<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MasterKelompok extends Public_Controller {

    private $pathView = 'aset/master_kelompok/';
    private $url;
    private $hakAkses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->hakAkses = hakAkses($this->url);
    }

    public function index($segment = 0)
    {
        $this->add_external_js(array(
            "assets/aset/master_kelompok/js/master_kelompok.js",
        ));

        $data = $this->includes;
        $content['akses'] = $this->hakAkses;
        $content['title_panel'] = 'Master Kelompok';
        $data['title_menu'] = 'Master Kelompok';

        $data['view'] = $this->load->view($this->pathView . 'v_index', $content, TRUE);
        $this->load->view($this->template, $data);
    }

    public function list_data()
    {
        $akses      = hakAkses($this->url);
        $m_kelompok = new \Model\Storage\MsKelompok_model();
        $keyword    = trim($this->input->post('keyword'));
        $query      = $m_kelompok;

        if ( !empty($keyword) ) {
            $query->where(function($query) use ($keyword) {
                $query->where('nama_kelompok', 'like', '%' . $keyword . '%')
                      ->orWhere('deskripsi', 'like', '%' . $keyword . '%');
            });
        }

        $d_kelompok = $query->orderBy('id', 'asc')->get()->toArray();

        $m_kategori = new \Model\Storage\MsAsetKategori_model();
        
        $used_data = $m_kategori->select('id_kelompok')->distinct()->get()->toArray();
        
        $used_kelompok_ids = [];
        if (is_array($used_data)) {
            foreach ($used_data as $item) {
                $id = isset($item['id_kelompok']) ? $item['id_kelompok'] : (isset($item->id_kelompok) ? $item->id_kelompok : null);
                if ($id) {
                    $used_kelompok_ids[] = $id;
                }
            }
        }

        
        foreach ($d_kelompok as &$row) {
            $row_id = isset($row['id']) ? $row['id'] : (isset($row->id) ? $row->id : null);
            $row['is_used'] = in_array($row_id, $used_kelompok_ids);
        }
        unset($row); 

        // cetak_r($d_kelompok, 1);

        $content['akses'] = $akses;
        $content['list']  = $d_kelompok;
        $html = $this->load->view($this->pathView . 'v_list', $content, TRUE);

        echo $html;
    }

    public function add_form()
    {
        $data['data'] = null;
        $this->load->view($this->pathView . 'v_form', $data);
    }

    public function edit_form()
    {
        $id = $this->input->get('id');

        $m_kelompok = new \Model\Storage\MsKelompok_model();
        $d_kelompok = $m_kelompok->where('id', $id)->first();

        $data['data'] = $d_kelompok;
        $this->load->view($this->pathView . 'v_form', $data);
    }

    public function save_data()
    {
        $params = $this->input->post('params');

                    // cetak_r($params, 1);


        try {
            $m_kelompok = new \Model\Storage\MsKelompok_model();

            $nama_kelompok     = trim($params['nama_kelompok']);
            $umur_ekonomis     = isset($params['umur_ekonomis']) && $params['umur_ekonomis'] !== '' ? (int)$params['umur_ekonomis'] : null;
            $deskripsi         = trim($params['deskripsi']);
            $trf_garis_lurus   = isset($params['trf_garis_lurus']) && $params['trf_garis_lurus'] !== '' ? (float)$params['trf_garis_lurus'] : null;
            $trf_saldo_menurun = isset($params['trf_saldo_menurun']) && $params['trf_saldo_menurun'] !== '' ? (float)$params['trf_saldo_menurun'] : null;

            if ( empty($nama_kelompok) ) {
                $this->result['message'] = 'Nama kelompok wajib diisi.';
            } elseif ( empty($umur_ekonomis) || $umur_ekonomis <= 0 ) {
                $this->result['message'] = 'Umur ekonomis wajib diisi dan harus lebih dari 0.';
            } else {
                $existingByNama = $m_kelompok->where('nama_kelompok', $nama_kelompok)->first();

                if ( $existingByNama ) {
                    $this->result['message'] = 'Nama kelompok sudah ada.';
                } else {
                    $m_kelompok->nama_kelompok     = $nama_kelompok;
                    $m_kelompok->umur_ekonomis     = $umur_ekonomis;
                    $m_kelompok->deskripsi         = $deskripsi;
                    $m_kelompok->trf_garis_lurus   = $trf_garis_lurus;
                    $m_kelompok->trf_saldo_menurun = $trf_saldo_menurun;
                    $m_kelompok->save();

                    $id            = $m_kelompok->id;
                    $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run('base/event/save', $m_kelompok, $deskripsi_log, null, $id, $m_kelompok);

                    $this->result['status']  = 1;
                    $this->result['message'] = 'Data berhasil disimpan';
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit_data()
    {
        $params = $this->input->post('params');

        try {
            $m_kelompok = new \Model\Storage\MsKelompok_model();

            $nama_kelompok     = trim($params['nama_kelompok']);
            $umur_ekonomis     = isset($params['umur_ekonomis']) && $params['umur_ekonomis'] !== '' ? (int)$params['umur_ekonomis'] : null;
            $deskripsi         = trim($params['deskripsi']);
            $trf_garis_lurus   = isset($params['trf_garis_lurus']) && $params['trf_garis_lurus'] !== '' ? (float)$params['trf_garis_lurus'] : null;
            $trf_saldo_menurun = isset($params['trf_saldo_menurun']) && $params['trf_saldo_menurun'] !== '' ? (float)$params['trf_saldo_menurun'] : null;

            if ( empty($nama_kelompok) ) {
                $this->result['message'] = 'Nama kelompok wajib diisi.';
            } elseif ( empty($umur_ekonomis) || $umur_ekonomis <= 0 ) {
                $this->result['message'] = 'Umur ekonomis wajib diisi dan harus lebih dari 0.';
            } else {
                $kelompokLama = $m_kelompok->where('id', $params['id'])->first();
                
                $m_aset = new \Model\Storage\MsAsetKategori_model(); 
                $usedByAset = $m_aset->where('id_kelompok', $params['id'])->first();

                if ( $usedByAset && $kelompokLama->nama_kelompok !== $nama_kelompok ) {
                    $this->result['message'] = 'Nama Kelompok tidak bisa diubah karena sudah dipakai oleh data Aset.';
                } else {
                    $existingByNama = $m_kelompok
                        ->where('nama_kelompok', $nama_kelompok)
                        ->where('id', '!=', $params['id'])
                        ->first();

                    if ( $existingByNama ) {
                        $this->result['message'] = 'Nama kelompok sudah ada.';
                    } else {
                        $data_update = [
                            'nama_kelompok'     => $nama_kelompok,
                            'umur_ekonomis'     => $umur_ekonomis,
                            'deskripsi'         => $deskripsi,
                            'trf_garis_lurus'   => $trf_garis_lurus,
                            'trf_saldo_menurun' => $trf_saldo_menurun,
                        ];

                        $m_kelompok->where('id', $params['id'])->update($data_update);

                        $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
                        Modules::run('base/event/update', $m_kelompok, $deskripsi_log, null, $params['id'], $m_kelompok);

                        $this->result['status']  = 1;
                        $this->result['message'] = 'Data berhasil diubah';
                    }
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete_data()
    {
        $id = $this->input->post('params');

        try {
            $m_kelompok = new \Model\Storage\MsKelompok_model();
            $kelompok = $m_kelompok->where('id', $id)->first();

            if ( $kelompok ) {
                $m_aset = new \Model\Storage\MsAsetKategori_model();
                $usedByAset = $m_aset->where('id_kelompok', $id)->first();

                if ( $usedByAset ) {
                    $this->result['message'] = 'Kelompok aset sudah dipakai transaksi, tidak bisa dihapus.';
                } else {
                    $m_kelompok->where('id', $id)->delete();

                    $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run('base/event/delete', $m_kelompok, $deskripsi_log, null, $id, $m_kelompok);

                    $this->result['status']  = 1;
                    $this->result['message'] = 'Data berhasil dihapus';
                }
            } else {
                $this->result['message'] = 'Data tidak ditemukan.';
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        }

        display_json($this->result);
    }
}