<?php defined('BASEPATH') OR exit('No direct script access allowed');

class LaporanHarianManajemen extends Public_Controller {

    private $url;
    private $pathView = 'report/laporan_harian_manajemen/';

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
                'assets/select2/js/select2.min.js',
                "assets/report/laporan_harian_manajemen/js/laporan-harian-manajemen.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/laporan_harian_manajemen/css/laporan-harian-manajemen.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['title_menu'] = 'Laporan Harian Manajemen';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getData($tanggal)
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.saldo_bank
            from (
                select sum(sb.saldo_akhir) as saldo_bank, 0 as tot_trf from saldo_bank sb where sb.tanggal = '".$tanggal."'

                union all

                select
                    0 as saldo_bank,
                    sum(rp.jml_transfer) as tot_trf
                from realisasi_pembayaran rp
                where
                    rp.tgl_realisasi = '".$tanggal."'
            ) data
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getLists()
    {
        $params = $this->input->post('params');

        try {
            $tanggal = $params['tanggal'];

            $data = $this->getData( $tanggal );

            $content['data'] = $data;
            $html = $this->load->view($this->pathView.'list', $content, TRUE);

            $this->result['status'] = 1;
            $this->result['html'] = $html;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}