<?php defined('BASEPATH') or exit('No direct script access allowed');

class PostingJurnal extends Public_Controller
{
    private $pathView = 'transaksi/posting/';
    private $url;
    private $akses;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->akses = hakAkses($this->url);
    }

    public function index()
    {
        if ( $this->akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/transaksi/posting/js/posting.js"
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/posting/css/posting.css"
            ));
            $data = $this->includes;

            $content['akses'] = $this->akses;
            $content['title_panel'] = 'Posting Jurnal';

            // Load Indexx
            $data['title_menu'] = 'Posting Jurnal';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function posting() {
    }
}