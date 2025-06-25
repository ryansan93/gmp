<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KPM extends Public_Controller {

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
                "assets/transaksi/kpm/js/kpm.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/transaksi/kpm/css/kpm.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;
            $content['jenis_pakan'] = $this->get_jenis_pakan();
            $content['supplier'] = $this->get_supplier();

            $content['add_form'] = $this->load->view('transaksi/kpm/add_form', $content, TRUE);

            // Load Indexx
            $data['title_menu'] = 'Kartu Pakan Peternak';
            $data['view'] = $this->load->view('transaksi/kpm/index', $content, TRUE);
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
                $html = $this->edit_form($id);
            } else {
                /* NOTE : untuk view */
                $html = $this->view_form($id);
            }
        } else {
            /* NOTE : untuk add */
            $html = $this->add_form();
        }

        echo $html;
    }

    public function add_form()
    {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['data'] = null;
        $content['jenis_pakan'] = $this->get_jenis_pakan();
        $content['supplier'] = $this->get_supplier();
        $html = $this->load->view('transaksi/kpm/add_form', $content);
        
        return $html;
    }

    public function view_form($id)
    {
        $akses = hakAkses($this->url);

        $m_kpm = new \Model\Storage\KartuPakan_model();
        $d_kpm = $m_kpm->with(['logs', 'kartu_pakan_detail', 'data_rdim_submit', 'barang'])->find($id);

        $m_sp = new \Model\Storage\StandarPerforma_model();
        $d_sp = $m_sp->whereNull('selesai')->first()->toArray();

        $m_det_sp = new \Model\Storage\DetStandarPerforma_model();
        $d_det_sp = $m_det_sp->where('id_performa', $d_sp['id'])->get()->toArray();

        $tgl_docin = substr($d_kpm['data_rdim_submit']['tgl_docin'], 0, 10);

        $std_kirim_pakan = 0;

        $rcn_kirim = null;
        $index_kirim = 0;
        $index_umur = 0;

        for ($i=0; $i < count($d_det_sp); $i++) {
            if ( $i > 0 ) {
                $d_det_sp_umur = $m_det_sp->where('id_performa', $d_sp['id'])->where('umur', $i)->first()->toArray();
                $std_kirim_pakan = $d_det_sp_umur['kons_pakan_harian'];

                $index_umur++;
                if ( $index_umur == 4 ) {
                    if ( $i != 35 ) {
                        $index_kirim = $index_umur + 1;
                        $data[$i]['rcn_kirim'] = $std_kirim_pakan;

                        $index_umur = 0;
                    }
                } else if ( $i == 35 ) {
                    $index_kirim = $index_umur + 1;
                    $data[$i]['rcn_kirim'] = $std_kirim_pakan;

                    $index_umur = 0;
                }
            }
            $_date = next_date($tgl_docin, $i);

            $setting = null;
            $rcn_kirim = null;
            $tgl_kirim = null;
            $jns_pakan = null;
            foreach ($d_kpm['kartu_pakan_detail'] as $k_kpm => $v_kpmdet) {
                if ( $v_kpmdet['umur'] == $i && $v_kpmdet['tgl_umur'] == $_date) {
                    $setting = $v_kpmdet['setting'];
                    $rcn_kirim = $v_kpmdet['rcn_kirim'];
                    $tgl_kirim = $v_kpmdet['tgl_kirim'];
                    $jns_pakan = $v_kpmdet['d_barang']['nama'];
                }
            }

            $data[$i] = array(
                'tanggal' => $_date,
                'umur' => $i,
                'std_kirim_pakan' => $std_kirim_pakan,
                'setting' => $setting,
                'rcn_kirim' => $rcn_kirim,
                'tgl_kirim' => $tgl_kirim,
                'jns_pakan' => $jns_pakan
            );
        }

        // cetak_r( $data );

        $content['data_sb'] = $data;
        $content['data'] = $d_kpm;
        $content['akses'] = $akses;
        $html = $this->load->view('transaksi/kpm/view_form', $content);
        
        return $html;
    }

    public function edit_form($id)
    {
        $akses = hakAkses($this->url);

        $m_kpm = new \Model\Storage\KartuPakan_model();
        $d_kpm = $m_kpm->with(['logs', 'kartu_pakan_detail', 'data_rdim_submit', 'barang'])->find($id);

        $m_sp = new \Model\Storage\StandarPerforma_model();
        $d_sp = $m_sp->whereNull('selesai')->first()->toArray();

        $m_det_sp = new \Model\Storage\DetStandarPerforma_model();
        $d_det_sp = $m_det_sp->where('id_performa', $d_sp['id'])->get()->toArray();

        $tgl_docin = substr($d_kpm['data_rdim_submit']['tgl_docin'], 0, 10);

        $std_kirim_pakan = 0;

        $rcn_kirim = null;
        $index_kirim = 0;
        $index_umur = 0;

        for ($i=0; $i < count($d_det_sp); $i++) {
            if ( $i > 0 ) {
                $d_det_sp_umur = $m_det_sp->where('id_performa', $d_sp['id'])->where('umur', $i)->first()->toArray();
                $std_kirim_pakan = $d_det_sp_umur['kons_pakan_harian'];

                $index_umur++;
                if ( $index_umur == 4 ) {
                    if ( $i != 35 ) {
                        $index_kirim = $index_umur + 1;
                        $data[$i]['rcn_kirim'] = $std_kirim_pakan;

                        $index_umur = 0;
                    }
                } else if ( $i == 35 ) {
                    $index_kirim = $index_umur + 1;
                    $data[$i]['rcn_kirim'] = $std_kirim_pakan;

                    $index_umur = 0;
                }
            }
            $_date = next_date($tgl_docin, $i);

            $setting = null;
            $rcn_kirim = null;
            $tgl_kirim = null;
            $jns_pakan = null;
            foreach ($d_kpm['kartu_pakan_detail'] as $k_kpm => $v_kpmdet) {
                if ( $v_kpmdet['umur'] == $i && $v_kpmdet['tgl_umur'] == $_date) {
                    $setting = $v_kpmdet['setting'];
                    $rcn_kirim = $v_kpmdet['rcn_kirim'];
                    $tgl_kirim = $v_kpmdet['tgl_kirim'];
                    $jns_pakan = $v_kpmdet['d_barang']['nama'];
                }
            }

            $data[$i] = array(
                'tanggal' => $_date,
                'umur' => $i,
                'std_kirim_pakan' => $std_kirim_pakan,
                'setting' => $setting,
                'rcn_kirim' => $rcn_kirim,
                'tgl_kirim' => $tgl_kirim,
                'jns_pakan' => $jns_pakan
            );
        }

        $content['data_sb'] = $data;
        $content['data'] = $d_kpm;
        $content['akses'] = $akses;
        $content['supplier'] = $this->get_supplier();
        $html = $this->load->view('transaksi/kpm/edit_form', $content);
        
        return $html;
    }

    public function list_kpm()
    {
        $m_kpm = new \Model\Storage\KartuPakan_model();
        $d_kpm = $m_kpm->with(['logs', 'data_rdim_submit'])->orderBy('id', 'DESC')->take(50)->get()->toArray();

        $content['data'] = $d_kpm;
        $content['akses'] = hakAkses($this->url);
        $html = $this->load->view('transaksi/kpm/list', $content, true);

        echo $html;
    } // end - list_rdim

    public function get_noreg()
    {
        $periode = $this->input->post('periode');

        $_data = array();

        $first_day_of_month = date('m-01-Y', strtotime($periode));
        $last_day_of_month  = date('m-t-Y', strtotime($periode));

        $m_rdim = new \Model\Storage\RdimSubmit_model();
        $d_rdim = $m_rdim->whereBetween('tgl_docin', [ $first_day_of_month, $last_day_of_month ] )
                         ->orderBy('noreg', 'ASC')
                         ->with(['dMitraMapping'])->get()->toArray();

        foreach ($d_rdim as $key => $value) {
            $_data[] = array(
              'id' => $value['id'],
              'noreg' => $value['noreg'],
              'id_mitra' => $value['id'],
              'mitra' => $value['d_mitra_mapping']['d_mitra']['nama'],
            );
        }

        $this->result['status'] = 1;
        $this->result['content'] = $_data;

        display_json($this->result);
    }

    public function get_jenis_pakan()
    {
        $m_brg = new \Model\Storage\Barang_model();
        $d_nomor = $m_brg->select('kode')->distinct('kode')->where('tipe', 'pakan')->get()->toArray();

        $datas = array();
        if ( !empty($d_nomor) ) {
            foreach ($d_nomor as $nomor) {
                $pelanggan = $m_brg->where('tipe', 'pakan')
                                          ->where('kode', $nomor['kode'])
                                          ->orderBy('version', 'desc')
                                          ->orderBy('id', 'desc')
                                          ->with(['logs'])
                                          ->first()->toArray();

                array_push($datas, $pelanggan);
            }
        }

        return $datas;
    }

    public function get_supplier()
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
                                          ->first()->toArray();

                array_push($datas, $supplier);
            }
        }

        return $datas;
    }

    public function get_data_rdim()
    {
        $_data = array();
        $data = array();

        $noreg = $this->input->post('noreg');
        $resubmit = $this->input->post('resubmit');

        if ( !empty($noreg) ) {
            $m_kp = new \Model\Storage\KartuPakan_model();
            $d_kp = $m_kp->where('noreg', trim($noreg))->first();

            $m_rdim = new \Model\Storage\RdimSubmit_model();
            $_d_rdim = $m_rdim->where('noreg', $noreg )->with(['dPerwakilanMapping', 'dMitraMapping', 'dBasttb'])->first();

            $d_rdim = $_d_rdim->toArray();

            $data['populasi'] = $d_rdim['populasi'];
            $data['nama_mitra'] = $d_rdim['d_mitra_mapping']['d_mitra']['nama'];
            $data['pakan1'] = $d_rdim['d_perwakilan_mapping']['hitung_budidaya_item_kpm']['d_sapronak_kesepakatan_no_check']['harga_performa'][0]['pakan1'];
            $data['tgl_docin'] = $d_rdim['tgl_docin'];
            $data['jenis_pakan'] = !empty($d_kp) ? $d_kp['jns_pakan'] : null;
            $data['id'] = !empty($d_kp) ? $d_kp['id'] : null;

            if ( !empty($d_rdim['d_basttb']) ) {
                $data['tgl_bapdoc'] = $d_rdim['d_basttb']['tgl_terima'];
            }

            $m_sp = new \Model\Storage\StandarPerforma_model();
            $d_sp = $m_sp->whereNull('selesai')->with(['details'])->first();

            $target = 0;
            foreach ($d_sp['details'] as $key => $v_sp) {
                if ( $v_sp['umur'] == 35 ) {
                    $target = $v_sp['kons_pakan'];
                }
            }

            $pakan1_sak = null;
            $pakan2_sak = null;
            $target_sak = null;
            $data['list'] =  $this->getDataKpm($noreg, $d_rdim['tgl_docin'], $d_rdim['populasi'], $pakan1_sak, $pakan2_sak, $target_sak, $resubmit);

            if ( count($data) > 0 ) {
                $this->result['status'] = 1;
                $this->result['content'] = $data;
            }
        } else {
            $tgl_docin = null;
            $populasi = null;
            $pakan1_sak = null;
            $pakan2_sak = null;
            $target_sak = null;

            $data['list'] =  $this->getDataKpm($noreg, $tgl_docin, $populasi, $pakan1_sak, $pakan2_sak, $target_sak, $resubmit);

            $this->result['status'] = 0;
            $this->result['content'] = $data;
        }

        display_json($this->result);
    }

    public function getDataKpm($noreg, $tgl_docin, $populasi, $pakan1_sak, $pakan2_sak, $target_sak, $resubmit = null)
    {
        $data = array();

        if ( empty($tgl_docin) && empty($populasi) && empty($pakan1_sak) && empty($pakan2_sak) && empty($target_sak) ) {
            $data = array();
        } else {
            $m_kpm = new \Model\Storage\KartuPakan_model();
            $d_kpm = $m_kpm->where('noreg', $noreg)->with(['logs', 'kartu_pakan_detail', 'data_rdim_submit', 'barang'])->first();

            $m_sp = new \Model\Storage\StandarPerforma_model();
            $d_sp = $m_sp->whereNull('selesai')->first()->toArray();

            $m_det_sp = new \Model\Storage\DetStandarPerforma_model();
            $d_det_sp = $m_det_sp->where('id_performa', $d_sp['id'])->get()->toArray();

            $std_kirim_pakan = 0;
            $index_kirim = 0;
            $index_umur = 0;

            for ($i=0; $i < count($d_det_sp); $i++) {
                if ( $i > 0 ) {
                    $d_det_sp_umur = $m_det_sp->where('id_performa', $d_sp['id'])->where('umur', $i)->first()->toArray();
                    $std_kirim_pakan = $d_det_sp_umur['kons_pakan_harian'];

                    $index_umur++;
                    if ( $index_umur == 4 ) {
                        if ( $i != 35 ) {
                            $index_kirim = $index_umur + 1;
                            $data[$i]['rcn_kirim'] = $std_kirim_pakan;

                            $index_umur = 0;
                        }
                    } else if ( $i == 35 ) {
                        $index_kirim = $index_umur + 1;
                        $data[$i]['rcn_kirim'] = $std_kirim_pakan;

                        $index_umur = 0;
                    }
                }
                $_date = next_date($tgl_docin, $i);

                $id = null;
                $setting = null;
                $rcn_kirim = null;
                $tgl_kirim = null;
                $exist = false;
                $jns_pakan = null;
                if ( count($d_kpm['kartu_pakan_detail']) > 0 ) {
                    foreach ($d_kpm['kartu_pakan_detail'] as $k_kpm => $v_kpmdet) {
                        if ( $v_kpmdet['umur'] == $i ) {
                            $id = $v_kpmdet['id'];
                            $setting = $v_kpmdet['setting'];
                            $rcn_kirim = $v_kpmdet['rcn_kirim'];
                            $tgl_kirim = $v_kpmdet['tgl_kirim'];
                            $exist = true;
                            $jns_pakan = $v_kpmdet['jns_pakan'];
                        }
                    }
                }

                $data[$i] = array(
                    'id' => $id,
                    'tanggal' => $_date,
                    'umur' => $i,
                    'std_kirim_pakan' => $std_kirim_pakan,
                    'setting' => $setting,
                    'rcn_kirim' => $rcn_kirim,
                    'tgl_kirim' => $tgl_kirim,
                    'exist' => $exist,
                    'jns_pakan' => $jns_pakan,
                );

                $id = null;
                $setting = null;
                $rcn_kirim = null;
                $tgl_kirim = null;
                $exist = false;
                $jns_pakan = null;
            }
        }

        $content['data'] = $data;
        $content['jenis_pakan'] = $this->get_jenis_pakan();
        $content['resubmit'] = $resubmit;
        $html = $this->load->view('transaksi/kpm/list_kpm', $content, true);

        return $html;
    }

    public function save_kpm()
    {
        $params = $this->input->post('params');

        try {
            $noreg = trim($params['noreg']);
            $jenis_pakan = $params['jenis_pakan'];
            $id_kp_exist = isset($params['id']) ? $params['id'] : null;

            $m_kp = new \Model\Storage\KartuPakan_model();
            $now = $m_kp->getDate();

            if ( empty($id_kp_exist) ) {
                $id_kp = $m_kp->getNextIdentity();
                $m_kp->id = $id_kp;
                $m_kp->noreg = $noreg;
                $m_kp->tgl_trans = $now['waktu'];
                $m_kp->jns_pakan = $jenis_pakan;
                $m_kp->supplier = $params['supplier'];
                $m_kp->save();
            } else {
                $id_kp = $id_kp_exist;
            }

            foreach ($params['detail'] as $k_val => $val) {
                $m_kpd = new \Model\Storage\KartuPakanDetail_model();

                $id_kpd = $m_kpd->getNextIdentity();
                $m_kpd->id = $id_kpd;
                $m_kpd->kartu_pakan = $id_kp;
                $m_kpd->tgl_trans = $now['waktu'];
                $m_kpd->umur = $val['umur'];
                $m_kpd->tgl_umur = $val['tgl_umur'];
                $m_kpd->setting = $val['setting'];
                $m_kpd->rcn_kirim = $val['rcn_kirim'];
                $m_kpd->tgl_kirim = $val['tgl_kirim'];
                $m_kpd->jns_pakan = $val['jns_pakan'];
                $m_kpd->save();
            }

            $d_kp = $m_kp->where('id', $id_kp)->with(['kartu_pakan_detail'])->first();

            $deskripsi_log_kpd = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_kp, $deskripsi_log_kpd);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil disimpan';
            $this->result['content'] = array('id' => $id_kp);            
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit_kpm()
    {
        $params = $this->input->post('params');

        try {
            $noreg = trim($params['noreg']);
            $jenis_pakan = $params['jenis_pakan'];
            $id_kp_exist = isset($params['id']) ? $params['id'] : null;
            $supplier = $params['supplier'];

            $m_kp = new \Model\Storage\KartuPakan_model();
            $now = $m_kp->getDate();

            $m_kp->where('id', $id_kp_exist)->update(
                array(
                    'supplier' => $supplier
                )
            );

            foreach ($params['detail'] as $k_val => $val) {
                $m_kpd = new \Model\Storage\KartuPakanDetail_model();

                $m_kpd->where('id', $val['id'])->update(
                    array(
                        'tgl_trans' => $now['waktu'],
                        'setting' => $val['setting'],
                        'rcn_kirim' => $val['rcn_kirim'],
                        'tgl_kirim' => $val['tgl_kirim'],
                        'jns_pakan' => $val['jns_pakan']
                    )
                );
            }

            $d_kp = $m_kp->where('id', $id_kp_exist)->with(['kartu_pakan_detail'])->first();

            $deskripsi_log_kpd = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_kp, $deskripsi_log_kpd);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di update';
            $this->result['content'] = array('id' => $id_kp_exist);            
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete_kpm()
    {
        $params = $this->input->post('params');

        try {
            $id = $params;

            $m_kp = new \Model\Storage\KartuPakan_model();

            $d_kp = $m_kp->where('id', $id)->with(['kartu_pakan_detail'])->first();

            $m_kp->where('id', $id)->delete();

            $m_kpd = new \Model\Storage\KartuPakanDetail_model();
            $m_kpd->where('kartu_pakan', $id)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_kp, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus';
            $this->result['content'] = array('id' => $id);            
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function tes()
    {
        cetak_r(strtotime('2019-04'));

        $first_day_this_month = date('m-01-Y', strtotime('2019-04'));
        $last_day_this_month  = date('m-t-Y', strtotime('2019-04'));

        cetak_r($first_day_this_month . '|' . $last_day_this_month);
    }
}