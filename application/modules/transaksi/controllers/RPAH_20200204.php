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
                "assets/transaksi/rpah/js/rpah.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/rpah/css/rpah.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;

            $a_content['unit'] = $this->get_unit();
            $content['add_form'] = $this->load->view('transaksi/rpah/add_form', $a_content, TRUE);

            // Load Indexx
            $data['title_menu'] = 'Pengajuan Rencana Penjualan Harian';
            $data['view'] = $this->load->view('transaksi/rpah/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function load_form()
    {
        $id = $this->input->get('id');
        $resubmit = $this->input->get('resubmit');
        $content = array();
        $html = "url not found";
        
        if ( !empty($id) && is_numeric($id) && $resubmit != 'edit' ) {
            // NOTE: view data BASTTB (ajax)
            $html = $this->view($id);
        } else if ( !empty($id) && is_numeric($id) && $resubmit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $html = $this->update($id);
        }else{
            $content['unit'] = $this->get_unit();
            $html = $this->load->view('transaksi/rpah/add_form', $content, TRUE);
        }

        echo $html;
    }

    public function get_unit()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'UN')->orderBy('nama', 'asc')->get()->toArray();

        return $d_wilayah;
    }
}