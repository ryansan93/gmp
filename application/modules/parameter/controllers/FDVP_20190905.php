<?php defined('BASEPATH') OR exit('No direct script access allowed');

class FDVP extends Public_Controller {

    private $url;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index($segment=0)
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                // "assets/multiselect/multiselect.js",
                "assets/select2/js/select2.min.js",
                "assets/jquery/list.min.js",
                "assets/parameter/fdvp/js/fdvp.js",
            ));
            $this->add_external_css(array(
                // "assets/multiselect/multiselect.css",
                "assets/select2/css/select2.min.css",
                "assets/parameter/fdvp/css/fdvp.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;
            $content['title_panel'] = 'Master FEED, DOC, VOADIP dan PERALATAN';

            // Load Indexx
            $data['title_menu'] = 'Master FEED, DOC, VOADIP dan PERALATAN';
            $data['view'] = $this->load->view('parameter/fdvp/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function load_form()
    {
        $id = $this->input->get('id');
        $resubmit = $this->input->get('resubmit');
        $html = '';

        if ( !empty($id) ) {
            if ( !empty($resubmit) ) {
                /* NOTE : untuk edit */
                $html = $this->edit_form($id, $resubmit);
            } else {
                /* NOTE : untuk view */
                $html = $this->view_form($id, $resubmit);
            }
        } else {
            /* NOTE : untuk add */
            $html = $this->add_form();
        }

        echo $html;
    }

    public function add_form()
    {
        $jenis = $this->input->get('jenis');

        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['data'] = null;

        if ( $jenis == 'feed' ) { 
            $content['kategori'] = array(
                'pakan0' => 'Pakan 0',
                'pakan1' => 'Pakan 1',
                'pakan2' => 'Pakan 2',
                'pakan3' => 'Pakan 3',
                'mix' => 'Mix',
            );

            $content['bentuk'] = array(
                'crumble' => 'Crumble',
                'pellet' => 'Pellet'
            );

            $content['list_supplier'] = $this->list_supplier();

            $html = $this->load->view('parameter/fdvp/add_feed', $content); 
        }
        if ( $jenis == 'doc' ) { 
            $content['list_supplier'] = $this->list_supplier();

            $html = $this->load->view('parameter/fdvp/add_doc', $content); 
        }
        if ( $jenis == 'voadip' ) { 
            $content['kategori'] = array(
                'vitamin' => 'Vitamin',
                'desinfektan' => 'Desinfektan',
                'obat' => 'Obat',
                'vaksin' => 'Vaksin'
            );

            $content['bentuk'] = array(
                'cair' => 'Cair',
                'kaplet' => 'Kaplet',
                'serbuk' => 'Serbuk'
            );

            $content['list_supplier'] = $this->list_supplier();

            $html = $this->load->view('parameter/fdvp/add_voadip', $content); 
        }
        if ( $jenis == 'peralatan' ) { 
            $content['list_supplier'] = $this->list_supplier();

            $html = $this->load->view('parameter/fdvp/add_peralatan', $content); 
        }
        
        echo $html;
    }

    public function view_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        $m_doc = new \Model\Storage\Doc_model();
        $d_doc = $m_doc->where('id', $id)
                           ->with(['lampiran', 'logs'])
                           ->orderBy('id', 'DESC')
                           ->first()->toArray();

        $content['akses'] = $akses;
        $content['data'] = $d_doc;
        $html = $this->load->view('parameter/doc/view_form', $content);
        
        return $html;
    }

    public function edit_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        $m_doc = new \Model\Storage\Doc_model();
        $d_doc = $m_doc->where('id', $id)
                           ->with(['lampiran', 'logs'])
                           ->orderBy('id', 'DESC')
                           ->first();

        $content['akses'] = $akses;
        $content['data'] = $d_doc;
        $html = $this->load->view('parameter/doc/edit_form', $content);
        
        return $html;
    }

    public function list_supplier()
    {
        $m_supplier = new \Model\Storage\Supplier_model();
        $d_nomor = $m_supplier->select('nomor')->distinct('nomor')->where('tipe', 'supplier')->get()->toArray();

        $datas = array();
        if ( !empty($d_nomor) ) {
            foreach ($d_nomor as $nomor) {
                $supplier = $m_supplier->where('tipe', 'supplier')
                                          ->where('nomor', $nomor['nomor'])
                                          ->orderBy('version', 'desc')
                                          ->orderBy('id', 'desc')
                                          ->with(['logs'])
                                          ->first()->toArray();

                if ( $supplier['mstatus'] == 1 && $supplier['status'] == getStatus(2) ) {
                    $data_array = array(
                        'id' => $supplier['id'],
                        'nip' => $supplier['nomor'],
                        'nama' => $supplier['nama'],
                        'nik' => $supplier['nik']
                    );

                    array_push($datas, $data_array);
                }
            }
        }

        return $datas;
    }

    public function list_feed()
    {
        $akses = hakAkses($this->url);

        $m_brg = new \Model\Storage\Barang_model();
        $d_brg = $m_brg->where('tipe', 'pakan')->with(['supplier','logs'])->orderBy('id', 'DESC')->get()->toArray();

        $content['kategori'] = array(
            'pakan0' => 'Pakan 0',
            'pakan1' => 'Pakan 1',
            'pakan2' => 'Pakan 2',
            'pakan3' => 'Pakan 3',
            'mix' => 'Mix',
        );

        $content['bentuk'] = array(
            'crumble' => 'Crumble',
            'pellet' => 'Pellet'
        );

        $content['list_supplier'] = $this->list_supplier();

        $content['akses'] = $akses;
        // $content['data'] = $d_brg;
        $content['data'] = $this->getList('pakan');
        $html = $this->load->view('parameter/fdvp/list_feed', $content);
        
        echo $html;
    }

    public function list_doc()
    {
        $akses = hakAkses($this->url);

        $m_brg = new \Model\Storage\Barang_model();
        $d_brg = $m_brg->where('kategori', 'doc')->with(['supplier', 'logs'])->orderBy('id', 'DESC')->get()->toArray();

        $content['list_supplier'] = $this->list_supplier();

        $content['akses'] = $akses;
        $content['data'] = $this->getList('doc');
        $html = $this->load->view('parameter/fdvp/list_doc', $content);
        
        echo $html;
    }

    public function list_voadip()
    {
        $akses = hakAkses($this->url);

        $m_brg = new \Model\Storage\Barang_model();
        $d_brg = $m_brg->where('kategori', 'voadip')->with(['supplier', 'logs'])->orderBy('id', 'DESC')->get()->toArray();

        $content['kategori'] = array(
            'vitamin' => 'Vitamin',
            'desinfektan' => 'Desinfektan',
            'obat' => 'Obat',
            'vaksin' => 'Vaksin'
        );

        $content['bentuk'] = array(
            'cair' => 'Cair',
            'kaplet' => 'Kaplet',
            'serbuk' => 'Serbuk'
        );

        $content['list_supplier'] = $this->list_supplier();

        $content['akses'] = $akses;
        $content['data'] = $this->getList('obat');
        $html = $this->load->view('parameter/fdvp/list_voadip', $content);
        
        echo $html;
    }

    public function list_peralatan()
    {
        $akses = hakAkses($this->url);

        $m_brg = new \Model\Storage\Barang_model();
        $d_brg = $m_brg->where('kategori', 'peralatan')->with(['logs'])->orderBy('id', 'DESC')->get()->toArray();

        $content['list_supplier'] = $this->list_supplier();

        $content['akses'] = $akses;
        $content['data'] = $this->getList('peralatan');
        $html = $this->load->view('parameter/fdvp/list_peralatan', $content);
        
        echo $html;
    }

    public function getList($tipe = null) {
        $m_brg = new \Model\Storage\Barang_model();
        $d_nomor = $m_brg->select('kode')->distinct('kode')->where('tipe', $tipe)->get()->toArray();

        $supplier = 'supplier_not_pakan';
        if ( $tipe == 'pakan' ) {
            $supplier = 'supplier';
        }

        $datas = array();
        if ( !empty($d_nomor) ) {
            foreach ($d_nomor as $nomor) {
                $pelanggan = $m_brg->where('tipe', $tipe)
                                          ->where('kode', $nomor['kode'])
                                          ->orderBy('version', 'desc')
                                          ->orderBy('id', 'desc')
                                          ->with([$supplier,'logs'])
                                          ->first()->toArray();

                array_push($datas, $pelanggan);
            }
        }

        return $datas;
    }

    public function save_feed()
    {
        $status = 'submit';

        $g_status = getStatus($status);

        $params = $this->input->post('params');
        // cetak_r($params, 1);
        try {
            if ( !empty($params) ) {
                foreach ($params as $key => $v_data) {
                    $m_brg = new \Model\Storage\Barang_model();
                    $nomor = $m_brg->getNextIdFeed();

                    $m_brg->kode = $nomor;
                    $m_brg->kategori = $v_data['kategori'];
                    $m_brg->nama = $v_data['nama_pakan'];
                    $m_brg->kode_item = $v_data['kode_item'];
                    $m_brg->umur = $v_data['umur'];
                    $m_brg->berat = $v_data['berat_pakan'];
                    $m_brg->bentuk = $v_data['bentuk_pakan'];
                    $m_brg->simpan = $v_data['masa_simpan'];

                    $m_brg->g_status = $g_status;
                    $m_brg->tipe = 'pakan';
                    $m_brg->version = 1;
                    $m_brg->save();

                    $id_pakan = $m_brg->id;

                    if ( !empty($v_data['supl']) ) {
                        foreach ($v_data['supl'] as $key => $val) {
                            $m_supl_pakan = new \Model\Storage\SupplierPakan_model();
                            $m_supl_pakan->id_pakan = $id_pakan;
                            $m_supl_pakan->id_supl = $val;
                            $m_supl_pakan->save();
                        }
                    }

                    $deskripsi_log_feed = 'di-'. $status .' oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/save', $m_brg, $deskripsi_log_feed );

                    $this->result['status'] = 1;
                    $this->result['message'] = 'Data pakan sukses disimpan';
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit_feed()
    {
        $params = $this->input->post('params');

        try {
            if ( !empty($params) ) {
                foreach ($params as $key => $v_data) {
                    $m_brg = new \Model\Storage\Barang_model();

                    $m_brg->kode = $v_data['kode'];
                    $m_brg->kategori = $v_data['kategori'];
                    $m_brg->nama = $v_data['nama_pakan'];
                    $m_brg->kode_item = $v_data['kode_item'];
                    $m_brg->umur = $v_data['umur'];
                    $m_brg->berat = $v_data['berat_pakan'];
                    $m_brg->bentuk = $v_data['bentuk_pakan'];
                    $m_brg->simpan = $v_data['masa_simpan'];

                    $m_brg->g_status = $v_data['status'];
                    $m_brg->tipe = 'pakan';
                    $m_brg->version = $v_data['version'] + 1;
                    $m_brg->save();

                    $id_pakan = $m_brg->id;

                    if ( !empty($v_data['supl']) ) {
                        foreach ($v_data['supl'] as $key => $val) {
                            $m_supl_pakan = new \Model\Storage\SupplierPakan_model();
                            $m_supl_pakan->id_pakan = $id_pakan;
                            $m_supl_pakan->id_supl = $val;
                            $m_supl_pakan->save();
                        }
                    }

                    $deskripsi_log_feed = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/update', $m_brg, $deskripsi_log_feed );

                    $this->result['status'] = 1;
                    $this->result['message'] = 'Data pakan sukses diubah';
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function save_doc()
    {
        $status = 'submit';

        $g_status = getStatus($status);

        $params = $this->input->post('params');
        // cetak_r($params, 1);
        try {
            if ( !empty($params) ) {
                foreach ($params as $key => $v_data) {
                    $m_brg = new \Model\Storage\Barang_model();
                    $nomor = $m_brg->getNextIdDoc();

                    $m_brg->kode = $nomor;
                    $m_brg->kategori = $v_data['kategori'];
                    $m_brg->nama = $v_data['nama_doc'];
                    $m_brg->kode_item = $v_data['kode_item'];
                    $m_brg->kode_supplier = $v_data['supl'];
                    $m_brg->berat = $v_data['berat'];
                    $m_brg->isi = $v_data['isi'];

                    $m_brg->g_status = $g_status;
                    $m_brg->tipe = 'doc';
                    $m_brg->version = 1;
                    $m_brg->save();

                    $deskripsi_log_feed = 'di-'. $status .' oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/save', $m_brg, $deskripsi_log_feed );

                    $this->result['status'] = 1;
                    $this->result['message'] = 'Data doc sukses disimpan';
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit_doc()
    {
        $params = $this->input->post('params');

        try {
            if ( !empty($params) ) {
                foreach ($params as $key => $v_data) {
                    $m_brg = new \Model\Storage\Barang_model();

                    $m_brg->kode = $v_data['kode'];
                    $m_brg->kategori = $v_data['kategori'];
                    $m_brg->nama = $v_data['nama_doc'];
                    $m_brg->kode_item = $v_data['kode_item'];
                    $m_brg->kode_supplier = $v_data['supl'];
                    $m_brg->berat = $v_data['berat'];
                    $m_brg->isi = $v_data['isi'];

                    $m_brg->g_status = $v_data['status'];
                    $m_brg->tipe = 'doc';
                    $m_brg->version = $v_data['version'] + 1;
                    $m_brg->save();

                    $deskripsi_log_feed = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/update', $m_brg, $deskripsi_log_feed );

                    $this->result['status'] = 1;
                    $this->result['message'] = 'Data doc sukses diubah';
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function save_voadip()
    {
        $status = 'submit';

        $g_status = getStatus($status);

        $params = $this->input->post('params');
        // cetak_r($params, 1);
        try {
            if ( !empty($params) ) {
                foreach ($params as $key => $v_data) {
                    $m_brg = new \Model\Storage\Barang_model();
                    $nomor = $m_brg->getNextIdVoadip();

                    $m_brg->kode = $nomor;
                    $m_brg->kode_supplier = $v_data['supl'];
                    $m_brg->kategori = $v_data['kategori'];
                    $m_brg->nama = $v_data['nama_voadip'];
                    $m_brg->kode_item = $v_data['kode_item'];
                    $m_brg->berat = $v_data['berat'];
                    $m_brg->bentuk = $v_data['bentuk'];
                    $m_brg->simpan = $v_data['masa_simpan'];
                    $m_brg->isi = $v_data['isi'];
                    $m_brg->satuan = $v_data['satuan'];

                    $m_brg->g_status = $g_status;
                    $m_brg->tipe = 'obat';
                    $m_brg->version = 1;
                    $m_brg->save();

                    $deskripsi_log_voadip = 'di-'. $status .' oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/save', $m_brg, $deskripsi_log_voadip );

                    $this->result['status'] = 1;
                    $this->result['message'] = 'Data voadip sukses disimpan';
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit_voadip()
    {
        $params = $this->input->post('params');

        try {
            if ( !empty($params) ) {
                foreach ($params as $key => $v_data) {
                    $m_brg = new \Model\Storage\Barang_model();

                    $m_brg->kode = $v_data['kode'];
                    $m_brg->kode_supplier = $v_data['supl'];
                    $m_brg->kategori = $v_data['kategori'];
                    $m_brg->nama = $v_data['nama_voadip'];
                    $m_brg->kode_item = $v_data['kode_item'];
                    $m_brg->berat = $v_data['berat'];
                    $m_brg->bentuk = $v_data['bentuk'];
                    $m_brg->simpan = $v_data['masa_simpan'];
                    $m_brg->isi = $v_data['isi'];
                    $m_brg->satuan = $v_data['satuan'];

                    $m_brg->g_status = $v_data['status'];
                    $m_brg->tipe = 'obat';
                    $m_brg->version = $v_data['version'] + 1;
                    $m_brg->save();

                    $deskripsi_log_voadip = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/update', $m_brg, $deskripsi_log_voadip );

                    $this->result['status'] = 1;
                    $this->result['message'] = 'Data voadip sukses diubah';
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function save_peralatan()
    {
        $status = 'submit';

        $g_status = getStatus($status);

        $params = $this->input->post('params');
        // cetak_r($params, 1);
        try {
            if ( !empty($params) ) {
                foreach ($params as $key => $v_data) {
                    $m_brg = new \Model\Storage\Barang_model();
                    $nomor = $m_brg->getNextIdPeralatan();

                    $m_brg->kode = $nomor;
                    $m_brg->kategori = $v_data['kategori'];
                    $m_brg->nama = $v_data['nama_peralatan'];
                    $m_brg->kode_supplier = $v_data['supl'];
                    $m_brg->kode_item = $v_data['kode_item'];
                    $m_brg->simpan = $v_data['masa_simpan'];
                    $m_brg->isi = $v_data['isi'];
                    $m_brg->satuan = $v_data['satuan'];

                    $m_brg->g_status = $g_status;
                    $m_brg->tipe = 'peralatan';
                    $m_brg->version = 1;
                    $m_brg->save();

                    $deskripsi_log_peralatan = 'di-'. $status .' oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/save', $m_brg, $deskripsi_log_peralatan );

                    $this->result['status'] = 1;
                    $this->result['message'] = 'Data voadip sukses disimpan';
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit_peralatan()
    {
        $params = $this->input->post('params');

        try {
            if ( !empty($params) ) {
                foreach ($params as $key => $v_data) {
                    $m_brg = new \Model\Storage\Barang_model();

                    $m_brg->kode = $v_data['kode'];
                    $m_brg->kategori = $v_data['kategori'];
                    $m_brg->nama = $v_data['nama_peralatan'];
                    $m_brg->kode_supplier = $v_data['supl'];
                    $m_brg->kode_item = $v_data['kode_item'];
                    $m_brg->simpan = $v_data['masa_simpan'];
                    $m_brg->isi = $v_data['isi'];
                    $m_brg->satuan = $v_data['satuan'];

                    $m_brg->g_status = $v_data['status'];
                    $m_brg->tipe = 'peralatan';
                    $m_brg->version = $v_data['version'] + 1;
                    $m_brg->save();

                    $deskripsi_log_peralatan = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/update', $m_brg, $deskripsi_log_peralatan );

                    $this->result['status'] = 1;
                    $this->result['message'] = 'Data voadip sukses diubah';
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function ack()
    {
        $params = $this->input->post('params');

        $status = 'ack';

        $g_status = getStatus($status);

        $m_brg = new \Model\Storage\Barang_model();
        $m_brg->where('id', $params['id'])
              ->where('tipe', $params['tipe'])
              ->update(
                    array('g_status' => $g_status)
                );

        $d_brg = $m_brg->where('id', $params['id'])->where('tipe', $params['tipe'])->first();

        $deskripsi_log_brg = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
        Modules::run( 'base/event/update', $d_brg, $deskripsi_log_brg );

        $this->result['status'] = 1;
        $this->result['message'] = 'Data ' . $params['kode'] . ' berhasil di ACK';

        display_json($this->result);
    }

    public function model($status)
    {
        $m_feed = new \Model\Storage\Barang_model();
        $dashboard = $m_feed->getDashboardAll($status);

        return $dashboard;
    }
}