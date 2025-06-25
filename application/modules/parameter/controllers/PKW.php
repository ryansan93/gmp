<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PKW extends Public_Controller {

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
                "assets/jquery/easy-autocomplete/jquery.easy-autocomplete.min.js",
                "assets/jquery/maskedinput/jquery.maskedinput.min.js",
                "assets/select2/js/select2.min.js",
                "assets/jquery/list.min.js",
                "assets/parameter/pkw/js/pkw.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/parameter/pkw/css/pkw.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;
            $content['title_panel'] = 'Master PERUSAHAAN, KOTA dan WILAYAH';

            // Load Indexx
            $data['title_menu'] = 'Master PERUSAHAAN, KOTA dan WILAYAH';
            $data['view'] = $this->load->view('parameter/pkw/index', $content, TRUE);
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

    public function autocomplete_lokasi()
    {
        $term = $this->input->get('term');
        $jenis = $this->input->get('tipe_lokasi');
        $induk = $this->input->get('induk');

        $data = array();

        $m_lokasi = new \Model\Storage\Lokasi_model();
        if (empty($induk)) {
            $d_lokasi = $m_lokasi->where('jenis', $jenis)
                             ->where('nama', 'LIKE', "%{$term}%")
                             ->orderBy('nama', 'ASC')->get();
        }else{
            $d_lokasi = $m_lokasi ->where('jenis', $jenis)
                              ->where('nama', 'LIKE', "%{$term}%")
                              ->where('induk', $induk)
                              ->orderBy('nama', 'ASC')->get();
        }
        foreach ($d_lokasi as $key => $val) {
            $data[] = array(
                'label'=>$val['nama'],
                'value'=>$val['nama'],
                'id' => $val['id']
            );
        }

        if (empty($data)) {
            $data = array(
                'label'=>"not found",
                'value'=>"",
                'id' => ""
            );
        }

        display_json($data);
    }

    public function autocomplete_wilayah()
    {
        $term = $this->input->get('term');
        $jenis = $this->input->get('tipe_wilayah');
        $induk = $this->input->get('induk');

        $data = array();

        $m_wilayah = new \Model\Storage\Wilayah_model();
        if (empty($induk)) {
            $d_wilayah = $m_wilayah->where('jenis', $jenis)
                             ->where('nama', 'LIKE', "%{$term}%")
                             ->orderBy('nama', 'ASC')->get();
        }else{
            $d_wilayah = $m_wilayah ->where('jenis', $jenis)
                              ->where('nama', 'LIKE', "%{$term}%")
                              ->where('induk', $induk)
                              ->orderBy('nama', 'ASC')->get();
        }
        foreach ($d_wilayah as $key => $val) {
            $data[] = array(
                'label'=>$val['nama'],
                'value'=>$val['nama'],
                'id' => $val['id']
            );
        }

        if (empty($data)) {
            $data = array(
                'label'=>"not found",
                'value'=>"",
                'id' => ""
            );
        }

        display_json($data);
    }

    public function autocomplete_kota_kab()
    {
        $term = $this->input->get('term');

        $data = array();

        $m_lokasi = new \Model\Storage\Lokasi_model();
        $d_lokasi = $m_lokasi->whereIn('jenis', array('KB', 'KT'))
                         ->where('nama', 'LIKE', "%{$term}%")
                         ->orderBy('nama', 'ASC')->get();
        
        foreach ($d_lokasi as $key => $val) {
            $data[] = array(
                'label'=>$val['nama'],
                'value'=>$val['nama'],
                'id' => $val['id']
            );
        }

        if (empty($data)) {
            $data = array(
                'label'=>"not found",
                'value'=>"",
                'id' => ""
            );
        }

        display_json($data);
    }

    public function add_form()
    {
        $jenis = $this->input->get('jenis');

        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['data'] = null;

        if ( $jenis == 'perusahaan' ) { 
            $html = $this->load->view('parameter/pkw/add_perusahaan', $content); 
        }
        if ( $jenis == 'wilayah' ) { 
            $html = $this->load->view('parameter/pkw/add_wilayah', $content); 
        }
        if ( $jenis == 'korwil' ) { 
            $html = $this->load->view('parameter/pkw/add_korwil', $content); 
        }
        
        echo $html;
    }

    public function edit_form($id = null, $resubmit = null)
    {
        $jenis = $this->input->get('jenis');
        $id = $this->input->get('id');

        $akses = hakAkses($this->url);

        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $d_perusahaan = $m_perusahaan->where('id', $id)->with(['d_kota'])->first();

        $content['akses'] = $akses;
        $content['data'] = $d_perusahaan;

        if ( $jenis == 'perusahaan' ) { 
            $html = $this->load->view('parameter/pkw/edit_perusahaan', $content); 
        }
        if ( $jenis == 'wilayah' ) { 
            $html = $this->load->view('parameter/pkw/edit_wilayah', $content); 
        }
        if ( $jenis == 'korwil' ) { 
            $html = $this->load->view('parameter/pkw/edit_korwil', $content); 
        }
        
        echo $html;
    }

    public function list_perusahaan()
    {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['data'] = $this->getList_Perusahaan();
        $html = $this->load->view('parameter/pkw/list_perusahaan', $content);
        
        echo $html;
    }

    public function list_wilayah()
    {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['data'] = $this->getList_Lokasi();

        // cetak_r($this->getList_Lokasi());

        $html = $this->load->view('parameter/pkw/list_wilayah', $content);
        
        echo $html;
    }

    public function list_korwil()
    {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['data'] = $this->getList_Korwil();

        // cetak_r( $this->getList_Korwil() );

        $html = $this->load->view('parameter/pkw/list_korwil', $content);
        
        echo $html;
    }

    public function getList_Perusahaan() {
        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $d_nomor = $m_perusahaan->select('kode')->distinct('kode')->get()->toArray();

        $datas = array();
        if ( !empty($d_nomor) ) {
            foreach ($d_nomor as $nomor) {
                $perusahaan = $m_perusahaan->where('kode', $nomor['kode'])
                                          ->orderBy('version', 'desc')
                                          ->orderBy('id', 'desc')
                                          ->with(['d_kota','logs'])
                                          ->first()->toArray();

                array_push($datas, $perusahaan);
            }
        }

        return $datas;
    }

    public function getList_Lokasi() {
        $m_lokasi = new \Model\Storage\Lokasi_model();
        $d_prov = $m_lokasi->where('jenis', 'PV')->get()->toArray();

        $datas = array();
        if ( count($d_prov) > 0 ) {            
            foreach ($d_prov as $k_prov => $v_prov) {
                $datas[ $v_prov['id'] ] = array(
                    'id' => $v_prov['id'],
                    'nama' => $v_prov['nama'],
                    'jenis' => $v_prov['jenis']
                );


                $d_kota_kab = $m_lokasi->where('induk', $v_prov['id'])->get()->toArray();

                if ( count($d_kota_kab) > 0 ) {
                    // NOTE : KOTA / KABUPATEN
                    foreach ($d_kota_kab as $k_kota_kab => $v_kota_kab) {
                        $datas[ $v_prov['id'] ]['kota_kab'][ $v_kota_kab['id'] ] = array(
                            'id' => $v_kota_kab['id'],
                            'nama' => $v_kota_kab['nama'],
                            'jenis' => $v_kota_kab['jenis']
                        );

                        $rowspan_kota_kab = 0;

                        $d_kec = $m_lokasi->where('induk', $v_kota_kab['id'])->where('jenis', 'KC')->get()->toArray();
                        if ( count($d_kec) > 0 ) {
                            // NOTE : KECAMATAN
                            foreach ($d_kec as $k_kec => $v_kec) {
                                $datas[ $v_prov['id'] ]['kota_kab'][ $v_kota_kab['id'] ]['kecamatan'][ $v_kec['id'] ] = array(
                                    'id' => $v_kec['id'],
                                    'nama' => $v_kec['nama'],
                                    'jenis' => $v_kec['jenis']
                                );

                                $rowspan_kec = 0;

                                $d_kel = $m_lokasi->where('induk', $v_kec['id'])->where('jenis', 'DS')->get()->toArray();
                                if ( count($d_kel) > 0 ) {
                                    // NOTE : KELUARAHAN / DESA
                                    foreach ($d_kel as $k_kel => $v_kel) {
                                        $datas[ $v_prov['id'] ]['kota_kab'][ $v_kota_kab['id'] ]['kecamatan'][ $v_kec['id'] ]['kelurahan'][ $v_kel['id'] ] = array(
                                            'id' => $v_kel['id'],
                                            'nama' => $v_kel['nama'],
                                            'jenis' => $v_kel['jenis']
                                        );

                                        $rowspan_kec++;
                                        $rowspan_kota_kab++;
                                    }
                                } else {
                                    $datas[ $v_prov['id'] ]['kota_kab'][ $v_kota_kab['id'] ]['kecamatan'][ $v_kec['id'] ]['kelurahan'] = array();
                                }

                                $datas[ $v_prov['id'] ]['kota_kab'][ $v_kota_kab['id'] ]['kecamatan'][ $v_kec['id'] ]['rowspan_kec'] = $rowspan_kec;
                            }
                        } else {
                            $datas[ $v_prov['id'] ]['kota_kab'][ $v_kota_kab['id'] ]['kecamatan'] = array();
                        }

                        $datas[ $v_prov['id'] ]['kota_kab'][ $v_kota_kab['id'] ]['rowspan_kota_kab'] = $rowspan_kota_kab;
                    }
                } else {
                    $datas[ $v_prov['id'] ]['kota_kab'] = array();
                }
            }
        }

        // cetak_r($datas);

        return $datas;
    }

    public function getList_Korwil()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_region = $m_wilayah->where('jenis', 'RG')->orderBy('nama', 'ASC')->get()->toArray();


        $datas = array();
        if ( count($d_region) > 0 ) {
            foreach ($d_region as $k_region => $v_region) {
                $datas[ $v_region['id'] ] = array(
                    'id' => $v_region['id'],
                    'nama' => $v_region['nama'],
                    'kode' => $v_region['kode'],
                    'jenis' => $v_region['jenis']
                );

                $d_pwk = $m_wilayah->where('jenis', 'PW')->where('induk', $v_region['id'])->orderBy('nama', 'ASC')->get()->toArray();
                if ( count($d_pwk) > 0 ) {
                    foreach ($d_pwk as $k_pwk => $v_pwk) {
                        $rowspan_pwk = 0;
                        $datas[ $v_region['id'] ]['perwakilan'][ $v_pwk['id'] ] = array(
                            'id' => $v_pwk['id'],
                            'nama' => $v_pwk['nama'],
                            'kode' => $v_pwk['kode'],
                            'jenis' => $v_pwk['jenis']
                        );

                        $d_unit = $m_wilayah->where('jenis', 'UN')->where('induk', $v_pwk['id'])->get()->toArray();
                        if ( count($d_unit) > 0 ) {
                            foreach ($d_unit as $k_unit => $v_unit) {
                                $datas[ $v_region['id'] ]['perwakilan'][ $v_pwk['id'] ]['unit'][ $v_unit['id'] ] = array(
                                    'id' => $v_unit['id'],
                                    'nama' => $v_unit['nama'],
                                    'kode' => $v_unit['kode'],
                                    'jenis' => $v_unit['jenis']
                                );

                                $rowspan_pwk++;
                            }
                        } else {
                            $datas[ $v_region['id'] ]['perwakilan'][ $v_pwk['id'] ]['unit'] = array();
                        }

                        $datas[ $v_region['id'] ]['perwakilan'][ $v_pwk['id'] ]['rowspan_pwk'] = $rowspan_pwk;
                    }
                } else {
                    $datas[ $v_region['id'] ]['perwakilan'] = array();
                }
            }
        }

        return $datas;
    }

    public function save_perusahaan()
    {
        $status = 1;

        $g_status = getStatus($status);

        $params = $this->input->post('params');
        try {
            if ( !empty($params) ) {
                $m_perusahaan = new \Model\Storage\Perusahaan_model();
                $id = $m_perusahaan->getNextIdentity();
                $nomor = $m_perusahaan->getNextNomor();

                $m_perusahaan->id = $id;
                $m_perusahaan->kode = $nomor;
                $m_perusahaan->perusahaan = $params['nama_perusahaan'];
                $m_perusahaan->alamat = $params['alamat'];
                $m_perusahaan->kota = $params['kota'];
                $m_perusahaan->npwp = $params['npwp'];

                $m_perusahaan->status = $g_status;
                $m_perusahaan->version = 1;
                $m_perusahaan->save();

                $deskripsi_log_perusahaan = 'di-'. $g_status .' oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $m_perusahaan, $deskripsi_log_perusahaan );

                $this->result['status'] = 1;
                $this->result['message'] = 'Data perusahaan sukses disimpan';
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit_perusahaan()
    {
        $status = 1;

        $g_status = getStatus($status);

        $params = $this->input->post('params');
        try {
            if ( !empty($params) ) {
                $m_perusahaan = new \Model\Storage\Perusahaan_model();
                $d_perusahaan = $m_perusahaan->where('id', $params['id'])->first();

                $id = $m_perusahaan->getNextIdentity();

                $m_perusahaan->id = $id;
                $m_perusahaan->kode = $params['nomor'];
                $m_perusahaan->perusahaan = $params['nama_perusahaan'];
                $m_perusahaan->alamat = $params['alamat'];
                $m_perusahaan->kota = $params['kota'];
                $m_perusahaan->npwp = $params['npwp'];

                $m_perusahaan->status = $d_perusahaan->status;
                $m_perusahaan->version = $d_perusahaan->version+1;
                $m_perusahaan->save();

                $deskripsi_log_perusahaan = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $m_perusahaan, $deskripsi_log_perusahaan );

                $this->result['status'] = 1;
                $this->result['message'] = 'Data perusahaan sukses disimpan';
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function save_lokasi()
    {
        $params = $this->input->post('params');
        $tipe = $this->input->post('tipe');

        try {
            if ( !empty($params) ) {
                $m_lokasi = new \Model\Storage\Lokasi_model();

                $m_prov = new \Model\Storage\Lokasi_model();

                $id_prov = null;
                if ( !empty($params['nama_prov']) ) {
                    $d_prov = $m_lokasi->where('jenis', 'PV')->where('nama', trim($params['nama_prov']))->first();
                    if ( empty($d_prov) ) {
                        $id_prov = $m_prov->getNextIdentity();

                        $m_prov->id = $id_prov;
                        $m_prov->nama = $params['nama_prov'];
                        $m_prov->jenis = 'PV';
                        $m_prov->induk = 1;
                        $m_prov->_awal = date('Y-m-d');
                        $m_prov->save();

                        $deskripsi_log_prov = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                        Modules::run( 'base/event/save', $m_prov, $deskripsi_log_prov );
                    } else {
                        $id_prov = $d_prov->id;
                    }
                }

                // cetak_r( $tipe, 1 );

                $tipe_lokasi = $tipe;
                // if ( $tipe == 'kabupaten' ) {
                //     $tipe_lokasi = 'KB';
                // } else {
                //     $tipe_lokasi = 'KT';
                // }

                $induk_prov = $id_prov;

                $m_kota_kab = new \Model\Storage\Lokasi_model();

                $id_kab_kota = null;
                if ( !empty($params['nama_lok']) ) {
                    $d_kota_kab = $m_lokasi->where('jenis', $tipe_lokasi)->where('induk', $induk_prov)->where('nama', trim($params['nama_lok']))->first();
                    if ( empty($d_kota_kab) ) {
                        $id_kab_kota = $m_kota_kab->getNextIdentity();

                        $m_kota_kab->id = $id_kab_kota;
                        $m_kota_kab->nama = ($tipe_lokasi == 'KT') ? 'Kota ' . $params['nama_lok'] : 'Kab ' . $params['nama_lok'];
                        $m_kota_kab->jenis = $tipe_lokasi;
                        $m_kota_kab->induk = $induk_prov;
                        $m_kota_kab->_awal = date('Y-m-d');
                        $m_kota_kab->save();

                        $deskripsi_log_kota_kab = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                        Modules::run( 'base/event/save', $m_kota_kab, $deskripsi_log_kota_kab );
                    } else {
                        $id_kab_kota = $d_kota_kab->id;
                    }
                }

                $induk_kec = $id_kab_kota;
                if ( isset($params['kecamatan']) ) {
                    foreach ($params['kecamatan'] as $k_kec => $v_kec) {
                        $d_kec = $m_lokasi->where('jenis', 'KC')->where('induk', $induk_kec)->where('nama', trim($v_kec['nama']))->first();

                        $id_kec = null;
                        if ( empty($d_kec) ) {
                            $m_kec = new \Model\Storage\Lokasi_model();
                            $id_kec = $m_kec->getNextIdentity();

                            $m_kec->id = $id_kec;
                            $m_kec->nama = $v_kec['nama'];
                            $m_kec->jenis = 'KC';
                            $m_kec->induk = $induk_kec;
                            $m_kec->_awal = date('Y-m-d');
                            $m_kec->save();

                            $deskripsi_log_kec = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                            Modules::run( 'base/event/save', $m_kec, $deskripsi_log_kec );
                        } else {
                            $id_kec = $d_kec->id;
                        }

                        $induk_kel = $id_kec;
                        if ( isset($v_kec['kelurahan']) ) {
                            foreach ($v_kec['kelurahan'] as $k_kel => $v_kel) {
                                $d_kel = $m_lokasi->where('jenis', 'DS')->where('induk', $induk_kel)->where('nama', trim($v_kel['nama']))->first();
                                if ( empty($d_kel) ) {
                                    $m_kel = new \Model\Storage\Lokasi_model();
                                    $m_kel->id = $m_kel->getNextIdentity();
                                    $m_kel->nama = $v_kel['nama'];
                                    $m_kel->jenis = 'DS';
                                    $m_kel->induk = $induk_kel;
                                    $m_kel->_awal = date('Y-m-d');
                                    $m_kel->save();

                                    $deskripsi_log_kelurahan = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                                    Modules::run( 'base/event/save', $m_kel, $deskripsi_log_kelurahan );
                                }
                            }
                        }
                    }
                }
                

                $this->result['status'] = 1;
                $this->result['message'] = 'Data lokasi sukses disimpan';
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function save_korwil()
    {
        $params = $this->input->post('params');

        try {
            if ( !empty($params) ) {
                $m_wilayah = new \Model\Storage\Wilayah_model();

                $nama_pwk = $params['nama_pwk'];
                $d_pwk = $m_wilayah->where('jenis', 'PW')->where('nama', trim($nama_pwk) )->first();

                $m_pwk = new \Model\Storage\Wilayah_model();
                $id = 0;
                if ( !isset($d_pwk['nama']) ) {
                    $id_pwk = $m_pwk->getNextIdentity();

                    $m_pwk->id = $id_pwk;
                    $m_pwk->nama = $nama_pwk;
                    $m_pwk->jenis = 'PW';
                    $m_pwk->induk = 1;
                    $m_pwk->_awal = date('Y-m-d');
                    $m_pwk->save();

                    $deskripsi_log_pwk = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/save', $m_pwk, $deskripsi_log_pwk );
                }

                if ( (count($params['kota']) > 0) && isset($params['kota']) ) {
                    foreach ($params['kota'] as $k_kota => $v_kota) {
                        $induk_kota = null;
                        if ( empty($d_pwk) ) {
                            $induk_kota = $id_pwk;
                        } else {
                            $induk_kota = $d_pwk->id;
                        }

                        $nama_kota = $v_kota['nama'];
                        $kode = $v_kota['kode'];
                        $d_kota = $m_wilayah->where('jenis', 'UN')->where('nama', trim($nama_kota) )->first();

                        $m_kota = new \Model\Storage\Wilayah_model();
                        if ( !isset($d_kota['nama']) ) {
                            $m_kota->id = $m_kota->getNextIdentity();
                            $m_kota->nama = $nama_kota;
                            $m_kota->jenis = 'UN';
                            $m_kota->induk = $induk_kota;
                            $m_kota->_awal = date('Y-m-d');
                            $m_kota->kode = $kode;
                            $m_kota->save();

                            $deskripsi_log_kota = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                            Modules::run( 'base/event/save', $m_kota, $deskripsi_log_kota );
                        }
                    }
                }

                $this->result['status'] = 1;
                $this->result['message'] = 'Data korwil sukses disimpan';
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function tes()
    {
        $m_lokasi = new \Model\Storage\Lokasi_model();
        $d_lokasi = $m_lokasi->whereIn('jenis', ['KB', 'KT'])
                         ->orderBy('nama', 'ASC')->get();
                         // ->where('jenis', 'KT')
                         // ->where('nama', 'LIKE', "%{$term}%")

        cetak_r($d_lokasi);
    }

    // public function model($status)
    // {
    //     $m_feed = new \Model\Storage\Barang_model();
    //     $dashboard = $m_feed->getDashboardAll($status);

    //     return $dashboard;
    // }
}