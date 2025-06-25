<?php defined('BASEPATH') OR exit('No direct script access allowed');

class DistribusiVoadip extends Public_Controller {

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
                // "assets/jquery/easy-autocomplete/jquery.easy-autocomplete.min.js",
                // "assets/jquery/maskedinput/jquery.maskedinput.min.js",
                "assets/select2/js/select2.min.js",
                // "assets/jquery/list.min.js",
                "assets/transaksi/distribusi_voadip/js/distribusi_voadip.js",
            ));
            $this->add_external_css(array(
                // "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                // "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/transaksi/distribusi_voadip/css/distribusi_voadip.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;

            // Load Indexx
            $data['title_menu'] = 'Distribusi Voadip';
            $data['view'] = $this->load->view('transaksi/distribusi_voadip/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_lists()
    {
        $params = $this->input->post('params');

        $rdim = $this->get_data_rdim('VOADIP', $params['start_date'], $params['end_date']);
        $datas = $this->mapping_data_rdim($rdim);

        $content['kategori_voadip'] = array(
            'vitamin'       => 'Vitamin',
            'desinfektan'   => 'Desinfektan',
            'obat'          => 'Obat',
            'vaksin'        => 'Vaksin'
        );

        $content['data'] = $datas;
        $content['supplier'] = $this->get_data_supplier();
        $html = $this->load->view('transaksi/distribusi_voadip/list_dv', $content, true);

        $this->result['status'] = 1;
        $this->result['content'] = $html;

        display_json($this->result);
    }

    public function get_data_rdim($tipe = null, $start_date = null, $end_date = null)
    {
        $d_rdim_submit = array();
        if ( $start_date != null && $end_date != null ) {
            $m_rdim_submit = new \Model\Storage\RdimSubmit_model();
            $d_rdim_submit = $m_rdim_submit->whereBetween('tgl_docin', [$start_date, $end_date])
                             ->with(['dKandang', 'mitra'])
                             ->orderBy('tgl_docin', 'ASC')
                             ->get()->toArray();
        } 
        // else {
        //     if ( $tipe == 'VOADIP' ) {
        //         $m_rdim_submit = new \Model\Storage\RdimSubmit_model();
        //         $d_rdim_submit = $m_rdim_submit->with(['dKandang', 'mitra'])
        //                          ->orderBy('tgl_docin', 'ASC')
        //                          ->get()->toArray();
        //     }
        // }

        return $d_rdim_submit;
    }

    public function mapping_data_rdim($params=null)
    {
        $data = array();
        if ( !empty($params) ) {
            foreach ($params as $k_params => $v_params) {
                $data[ $v_params['d_kandang']['d_unit']['id'] ]['id'] = $v_params['d_kandang']['d_unit']['id'];
                $data[ $v_params['d_kandang']['d_unit']['id'] ]['nama'] = $v_params['d_kandang']['d_unit']['nama'];

                $data[ $v_params['d_kandang']['d_unit']['id'] ]['detail'][ $v_params['mitra']['d_mitra']['nomor'] ]['nomor'] = $v_params['mitra']['d_mitra']['nomor'];
                $data[ $v_params['d_kandang']['d_unit']['id'] ]['detail'][ $v_params['mitra']['d_mitra']['nomor'] ]['nama'] = $v_params['mitra']['d_mitra']['nama'];
                $data[ $v_params['d_kandang']['d_unit']['id'] ]['detail'][ $v_params['mitra']['d_mitra']['nomor'] ]['kandang'] = $v_params['d_kandang']['kandang'];
                $data[ $v_params['d_kandang']['d_unit']['id'] ]['detail'][ $v_params['mitra']['d_mitra']['nomor'] ]['populasi'] = $v_params['populasi'];
                $data[ $v_params['d_kandang']['d_unit']['id'] ]['detail'][ $v_params['mitra']['d_mitra']['nomor'] ]['noreg'] = $v_params['noreg'];
                $data[ $v_params['d_kandang']['d_unit']['id'] ]['detail'][ $v_params['mitra']['d_mitra']['nomor'] ]['tgl_docin'] = $v_params['tgl_docin'];
            }
        }

        ksort($data);

        return $data;
    }

    public function get_data_voadip() {
        $kategori = $this->input->post('kategori');

        try {
            $m_brg = new \Model\Storage\Barang_model();
            $d_nomor = $m_brg->select('kode')->distinct('kode')->where('tipe', 'obat')->where('kategori', $kategori)->get()->toArray();

            $datas = array();
            if ( !empty($d_nomor) ) {
                foreach ($d_nomor as $nomor) {
                    $pelanggan = $m_brg->where('tipe', 'obat')
                                              ->where('kode', $nomor['kode'])
                                              ->orderBy('version', 'desc')
                                              ->orderBy('id', 'desc')
                                              ->with(['logs'])
                                              ->first()->toArray();

                    array_push($datas, $pelanggan);
                }
            }

            $this->result['status'] = 1;
            $this->result['content'] = $datas;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function get_data_supplier()
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

    public function save()
    {
        $params = $this->input->post('params');


        try {
            foreach ($params as $k_data => $v_data) {
                $m_dis_voadip = new \Model\Storage\DisVoadip_model();
                $now = $m_dis_voadip->getDate();

                $m_dis_voadip->id = $m_dis_voadip->getNextIdentity();
                $m_dis_voadip->noreg = $v_data['noreg'];
                $m_dis_voadip->umur = $v_data['umur'];
                $m_dis_voadip->user_submit = $this->userid;
                $m_dis_voadip->tgl_submit = $now['waktu'];
                $m_dis_voadip->save();

                $id_dis = $m_dis_voadip->id;

                $deskripsi_log_dis_voadip = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $m_dis_voadip, $deskripsi_log_dis_voadip);

                foreach ($v_data['detail'] as $k_detail => $v_detail) {
                    $m_dis_voadip_detail = new \Model\Storage\DisVoadipDetail_model();

                    $m_dis_voadip_detail->id = $m_dis_voadip_detail->getNextIdentity();
                    $m_dis_voadip_detail->id_dis = $id_dis;
                    $m_dis_voadip_detail->kode_barang = $v_detail['kode_barang'];
                    $m_dis_voadip_detail->tgl_kirim = $v_detail['tanggal'];
                    $m_dis_voadip_detail->jumlah_kemasan = $v_detail['jml_kemasan'];
                    $m_dis_voadip_detail->jumlah_isi = $v_detail['jml_isi'];
                    $m_dis_voadip_detail->jumlah_do = $v_detail['jml_do'];
                    $m_dis_voadip_detail->supplier = $v_detail['supplier'];
                    $m_dis_voadip_detail->user_submit = $this->userid;
                    $m_dis_voadip_detail->tgl_submit = $now['waktu'];
                    $m_dis_voadip_detail->save();
                }
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Ditribusi Voadip berhasil disimpan.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function tes()
    {
        // $params = $this->get_data_rdim('VOADIP');
        // $datas = $this->mapping_data_rdim($params);

        $selisih = selisihTanggal('2019-06-25', '2019-07-03');

        cetak_r($selisih);
    }
}