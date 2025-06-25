<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RPAH extends Public_Controller {

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
                "assets/select2/js/select2.min.js",
                "assets/report/rpah/js/rpah.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/report/rpah/css/rpah.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;

            // $a_content['unit'] = $this->get_unit();
            // $content['add_form'] = $this->load->view('report/rpah/add_form', $a_content, TRUE);

            // Load Indexx
            $data['title_menu'] = 'Pengajuan Rencana Penjualan Harian';
            $data['view'] = $this->load->view('report/rpah/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_lists()
    {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $data = null;

        $m_rpah = new \Model\Storage\Rpah_model();
        $d_rpah = $m_rpah->whereBetween('tgl_panen', [$start_date, $end_date])->orderBy('unit', 'asc')->with(['det_rpah', 'logs'])->get()->toArray();

        $data = $this->mapping_data($d_rpah);

        $content['data'] = $data;
        $html = $this->load->view('report/rpah/list', $content, TRUE);

        echo $html;
    }

    public function mapping_data($params)
    {
        $data = null;

        if ( !empty($params) ) {
            $tonase_jual = 0;
            $ekor_jual = 0;
            $no_mitra_old = 0;
            foreach ($params as $k => $val) {
                $data[ $val['id_unit'] ]['id'] = $val['id_unit'];
                $data[ $val['id_unit'] ]['unit'] = $val['unit'];
                $data[ $val['id_unit'] ]['bottom_price'] = $val['bottom_price'];

                foreach ($val['det_rpah'] as $k_det => $v_det) {
                    $id_konfir = $v_det['data_konfir']['id'];
                    $no_mitra = $v_det['data_konfir']['rdim_submit']['d_mitra_mapping']['d_mitra']['nomor'];

                    if ( $no_mitra_old != $no_mitra ) {
                        $tonase_jual = 0;
                        $ekor_jual = 0;
                    }

                    $no_mitra_old = $no_mitra;

                    $data[ $val['id_unit'] ]['mitra'][ $id_konfir ]['id_konfir'] = $id_konfir;
                    $data[ $val['id_unit'] ]['mitra'][ $id_konfir ]['mitra'] = $v_det['data_konfir']['rdim_submit']['d_mitra_mapping']['d_mitra']['nama'];
                    $data[ $val['id_unit'] ]['mitra'][ $id_konfir ]['noreg'] = $v_det['data_konfir']['noreg'];
                    $data[ $val['id_unit'] ]['mitra'][ $id_konfir ]['kandang'] = $v_det['data_konfir']['rdim_submit']['d_kandang']['kandang'];
                    $data[ $val['id_unit'] ]['mitra'][ $id_konfir ]['tonase'] = $v_det['data_konfir']['total'];
                    $data[ $val['id_unit'] ]['mitra'][ $id_konfir ]['ekor'] = $v_det['data_konfir']['populasi'];

                    $data[ $val['id_unit'] ]['mitra'][ $id_konfir ]['pelanggan'][ $v_det['id'] ]['id'] = $v_det['id'];
                    $data[ $val['id_unit'] ]['mitra'][ $id_konfir ]['pelanggan'][ $v_det['id'] ]['no_plg'] = $v_det['no_pelanggan'];
                    $data[ $val['id_unit'] ]['mitra'][ $id_konfir ]['pelanggan'][ $v_det['id'] ]['plg'] = $v_det['pelanggan'];
                    $data[ $val['id_unit'] ]['mitra'][ $id_konfir ]['pelanggan'][ $v_det['id'] ]['outstanding'] = $v_det['outstanding'];
                    $data[ $val['id_unit'] ]['mitra'][ $id_konfir ]['pelanggan'][ $v_det['id'] ]['tonase'] = $v_det['tonase'];
                    $data[ $val['id_unit'] ]['mitra'][ $id_konfir ]['pelanggan'][ $v_det['id'] ]['ekor'] = $v_det['ekor'];
                    $data[ $val['id_unit'] ]['mitra'][ $id_konfir ]['pelanggan'][ $v_det['id'] ]['bb'] = $v_det['bb'];
                    $data[ $val['id_unit'] ]['mitra'][ $id_konfir ]['pelanggan'][ $v_det['id'] ]['harga'] = $v_det['harga'];

                    $tonase_jual += $v_det['tonase'];
                    $ekor_jual += $v_det['ekor'];

                    $data[ $val['id_unit'] ]['mitra'][ $id_konfir ]['tonase_jual'] = $tonase_jual;
                    $data[ $val['id_unit'] ]['mitra'][ $id_konfir ]['ekor_jual'] = $ekor_jual;
                }
            }
        }

        return $data;
    }
}