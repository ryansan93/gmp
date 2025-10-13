<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SaldoAwalCoa extends Public_Controller {

    private $pathView = 'accounting/saldo_awal_coa/';
    private $url;
    private $hakAkses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->hakAkses = hakAkses($this->url);
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index($segment=0)
    {
        if ( $this->hakAkses['a_view'] == 1 ) {
            $this->add_external_js(array(
                // "assets/jquery/easy-autocomplete/jquery.easy-autocomplete.min.js",
                "assets/select2/js/select2.min.js",
                "assets/accounting/saldo_awal_coa/js/saldo-awal-coa.js",
            ));
            $this->add_external_css(array(
                // "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                // "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/accounting/saldo_awal_coa/css/saldo-awal-coa.css",
            ));

            $data = $this->includes;

            $periode = null;
            if ( empty($periode) ) {
                $m_sa = new \Model\Storage\SaCoa_model();
                $d_sa = $m_sa->getSaldoAwal();
    
                if ( !empty($d_sa) ) {
                    $periode = $d_sa[0]['periode'];
                }
            }

            $content['akses'] = $this->hakAkses;
            $content['periode'] = !empty($periode) ? $periode.'-01' : null;
            $content['formData'] = $this->formData($periode);
            $content['title_panel'] = 'Saldo Awal COA';

            // Load Indexx
            $data['title_menu'] = 'Saldo Awal COA';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getDataByPeriode() {
        $params = $this->input->get('params');

        $periode = substr($params['periode'], 0, 7);

        $formData = $this->formData( $periode );

        echo $formData;
    }

    public function formData($periode = null) {
        $m_sa = new \Model\Storage\SaCoa_model();
        $d_sa = $m_sa->getSaldoAwal($periode, 'periode');

        $m_coa = new \Model\Storage\Coa_model();
        $d_coa = $m_coa->getDataCoa();

        $m_wil = new \Model\Storage\Wilayah_model();
        $d_wil = $m_wil->getDataUnit();

        $content['akses'] = $this->hakAkses;
        $content['unit'] = $d_wil;
        $content['coa'] = $d_coa;
        $content['data'] = !empty($d_sa) ? $d_sa : null;

        $html = $this->load->view($this->pathView . 'formData', $content, TRUE);

        return $html;
    }

    public function save() {
        $params = $this->input->post('params');

        try {            
            foreach ($params['data'] as $k_det => $v_det) {
                $m_sa = new \Model\Storage\SaCoa_model();
                $m_sa->periode = substr($v_det['periode'], 0, 7);
                $m_sa->no_coa = $v_det['no_coa'];
                $m_sa->nama_coa = $v_det['nama_coa'];
                $m_sa->unit = $v_det['unit'];
                $m_sa->debet = $v_det['nominal'];
                // $m_sa->kredit = $v_det['kredit'];
                // $m_sa->jenis = 1;
                $m_sa->save();
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit() {
        $params = $this->input->post('params');

        try {
            $periode = substr($params['data'][0]['periode'], 0, 7);

            $m_sa = new \Model\Storage\SaCoa_model();
            $m_sa->where('periode', $periode)->delete();

            foreach ($params['data'] as $k_det => $v_det) {
                $m_sa = new \Model\Storage\SaCoa_model();
                $m_sa->periode = substr($v_det['periode'], 0, 7);
                $m_sa->no_coa = $v_det['no_coa'];
                $m_sa->nama_coa = $v_det['nama_coa'];
                $m_sa->unit = $v_det['unit'];
                $m_sa->debet = $v_det['nominal'];
                // $m_sa->kredit = $v_det['kredit'];
                // $m_sa->jenis = 1;
                $m_sa->save();
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di ubah.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete() {
        $params = $this->input->post('params');

        try {
            $periode = substr($params['periode'], 0, 7);

            $m_sa = new \Model\Storage\SaCoa_model();
            $m_sa->where('periode', $periode)->delete();

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}