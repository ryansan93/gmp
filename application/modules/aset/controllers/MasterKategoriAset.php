<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MasterKategoriAset extends Public_Controller {

    private $pathView = 'aset/master_kategori_aset/';
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
                "assets/aset/master_kategori_aset/js/master_kategori_aset.js",
            ));

            $data = $this->includes;
            $content['akses'] = $this->hakAkses;
            $content['title_panel'] = 'Master Kategori Aset';
            $data['title_menu'] = 'Master Kategori Aset';

            $data['view'] = $this->load->view($this->pathView . 'v_index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function list_data()
    {
        $akses          = hakAkses($this->url);
        $m_kategori     = new \Model\Storage\MsAsetKategori_model();
        $keyword        = trim($this->input->post('keyword'));
        
        $query = $m_kategori->select(
            'ms_aset_kategori.id as id',              
            'ms_aset_kategori.kategori_kode',
            'ms_aset_kategori.kategori_name',
            'ms_aset_kategori.masa_manfaat_komersial',
            'ms_aset_kategori.masa_manfaat_fiskal',
            'ms_aset_kategori.id_kelompok',
            'ms_kelompok.id as kelompok_id',          
            'ms_kelompok.nama_kelompok',
            'ms_kelompok.umur_ekonomis',
            'ms_kelompok.trf_garis_lurus',
            'ms_kelompok.trf_saldo_menurun'
        )
        ->selectRaw('CASE WHEN EXISTS (SELECT 1 FROM ms_aset WHERE ms_aset.id_kategori = ms_aset_kategori.id) THEN 1 ELSE 0 END as is_used')
        ->leftJoin('ms_kelompok', 'ms_aset_kategori.id_kelompok', '=', 'ms_kelompok.id');

        if ( !empty($keyword) ) {
            $query->where(function($q) use ($keyword) {
                $q->where('ms_aset_kategori.kategori_kode', 'like', '%' . $keyword . '%')
                ->orWhere('ms_aset_kategori.kategori_name', 'like', '%' . $keyword . '%')
                ->orWhere('ms_kelompok.nama_kelompok', 'like', '%' . $keyword . '%');
            });
        }
        

        $d_kategori = $query->orderBy('ms_aset_kategori.kategori_kode', 'asc') 
                            ->orderBy('ms_aset_kategori.id', 'asc')
                            ->get()->toArray();

        $content['akses'] = $akses;
        $content['list']  = $d_kategori;
        $html = $this->load->view($this->pathView . 'v_list', $content, TRUE);

        echo $html;
    }

    public function add_form()
    {
        $data['data'] = null;

        $m_kelompok = new \Model\Storage\MsKelompok_model();
        $d_kelompok = $m_kelompok->orderBy('id', 'asc')->get()->toArray();

        $data['kelompok'] = $d_kelompok;

        // cetak_r($d_kelompok, 1);
        $this->load->view($this->pathView . 'v_form', $data);
    }

    public function edit_form()
    {
        $id = $this->input->get('id');

        $m_kelompok = new \Model\Storage\MsKelompok_model();
        $d_kelompok = $m_kelompok->orderBy('nama_kelompok', 'asc')->get()->toArray();

        $m_kategori = new \Model\Storage\MsAsetKategori_model();
        $d_kategori = $m_kategori->where('id', $id)->first();

        $data['kelompok'] = $d_kelompok;
        $data['data']     = $d_kategori;
        
        $this->load->view($this->pathView . 'v_form', $data);
    }

    public function save_data()
    {
        $params = $this->input->post('params');

        try {
            $m_kategori = new \Model\Storage\MsAsetKategori_model();

            $kategori_kode = trim($params['kategori_kode']);
            $kategori_name = trim($params['kategori_name']);
            
            $masa_manfaat_komersial = isset($params['masa_manfaat_komersial']) && $params['masa_manfaat_komersial'] !== '' ? (int)$params['masa_manfaat_komersial'] : null;
            $masa_manfaat_fiskal = isset($params['masa_manfaat_fiskal']) && $params['masa_manfaat_fiskal'] !== '' ? (int)$params['masa_manfaat_fiskal'] : null;
            $id_kelompok = isset($params['id_kelompok']) && $params['id_kelompok'] !== '' ? $params['id_kelompok'] : null;

            if ( empty($kategori_kode) || empty($kategori_name) ) {
                $this->result['message'] = 'Kode kategori dan nama kategori wajib diisi.';
            } else {
                $existingByKode = $m_kategori->where('kategori_kode', $kategori_kode)->first();
                $existingByNama = $m_kategori->where('kategori_name', $kategori_name)->first();

                // if ( $existingByKode || $existingByNama ) {
                //     $this->result['message'] = 'Kode atau nama kategori sudah ada.';
                // } else {
                    $m_kategori->kategori_kode = $kategori_kode;
                    $m_kategori->kategori_name = $kategori_name;
                    $m_kategori->masa_manfaat_komersial = $masa_manfaat_komersial;
                    $m_kategori->masa_manfaat_fiskal = $masa_manfaat_fiskal;
                    $m_kategori->id_kelompok = $id_kelompok;
                    $m_kategori->save();

                    $id            = $m_kategori->id;
                    $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run('base/event/save', $m_kategori, $deskripsi_log, null, $id, $m_kategori);

                    $this->result['status'] = 1;
                    $this->result['message'] = 'Data berhasil disimpan';
                // }
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
            $m_kategori = new \Model\Storage\MsAsetKategori_model();

            $kategori_kode = trim($params['kategori_kode']);
            $kategori_name = trim($params['kategori_name']);
            $masa_manfaat_komersial = isset($params['masa_manfaat_komersial']) && $params['masa_manfaat_komersial'] !== '' ? (int)$params['masa_manfaat_komersial'] : null;
            $masa_manfaat_fiskal = isset($params['masa_manfaat_fiskal']) && $params['masa_manfaat_fiskal'] !== '' ? (int)$params['masa_manfaat_fiskal'] : null;
            $id_kelompok = isset($params['id_kelompok']) && $params['id_kelompok'] !== '' ? (float)$params['id_kelompok'] : null;

            if ( empty($kategori_kode) || empty($kategori_name) ) {
                $this->result['message'] = 'Kode kategori dan nama kategori wajib diisi.';
            } else {
                $kategoriLama = $m_kategori->where('id', $params['id'])->first();
                
                $m_aset = new \Model\Storage\MsAset_model(); 
                $usedByAset = $m_aset->where('id_kategori', $params['id'])->first();

                if ( $usedByAset && ($kategoriLama->kategori_kode !== $kategori_kode || $kategoriLama->kategori_name !== $kategori_name) ) {
                    $this->result['message'] = 'Kode dan Nama Kategori tidak bisa diubah karena sudah dipakai oleh data Aset.';
                } else {
                    $existingByKode = $m_kategori
                        ->where('kategori_kode', $kategori_kode)
                        ->where('id', '!=', $params['id'])
                        ->first();
                    $existingByNama = $m_kategori
                        ->where('kategori_name', $kategori_name)
                        ->where('id', '!=', $params['id'])
                        ->first();

                    // if ( $existingByKode || $existingByNama ) {
                    //     $this->result['message'] = 'Kode atau nama kategori sudah ada.';
                    // } else {
                        $data_update = [
                            'kategori_kode'             => $kategori_kode,
                            'kategori_name'             => $kategori_name,
                            'masa_manfaat_komersial'    => $masa_manfaat_komersial,
                            'masa_manfaat_fiskal'       => $masa_manfaat_fiskal,
                            'id_kelompok'               => $id_kelompok,
                        ];

                        $m_kategori->where('id', $params['id'])->update($data_update);

                        $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
                        Modules::run('base/event/update', $m_kategori, $deskripsi_log, null, $params['id'], $m_kategori);

                        $this->result['status'] = 1;
                        $this->result['message'] = 'Data berhasil diubah';
                    // }
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
            $m_kategori = new \Model\Storage\MsAsetKategori_model();
            $kategori = $m_kategori->where('id', $id)->first();

            if ( $kategori ) {
                $m_aset = new \Model\Storage\MsAset_model();
                $usedByAset = $m_aset->where('id_kategori', $id)->first();

                if ( $usedByAset ) {
                    $this->result['message'] = 'Kategori aset sudah dipakai transaksi, tidak bisa dihapus.';
                } else {
                    $m_kategori->where('id', $id)->delete();

                    $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run('base/event/delete', $m_kategori, $deskripsi_log, null, $id, $m_kategori);

                    $this->result['status'] = 1;
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