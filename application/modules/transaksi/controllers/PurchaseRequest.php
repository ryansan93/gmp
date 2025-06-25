<?php defined('BASEPATH') or exit('No direct script access allowed');

class PurchaseRequest extends Public_Controller
{
    private $pathView = 'transaksi/purchase_request/';
    private $upload_path;
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
        $this->upload_path = FCPATH."//uploads/";
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index()
    {
        if ( $this->akses['a_view'] == 1 ) {
            $this->load->library('Mobile_Detect');
            $detect = new Mobile_Detect();

            $this->add_external_js(
                array(
                    "assets/select2/js/select2.min.js",
                    "assets/compress-image/js/compress-image.js",
                    'assets/transaksi/purchase_request/js/purchase-request.js'
                )
            );
            $this->add_external_css(
                array(
                    "assets/select2/css/select2.min.css",
                    'assets/transaksi/purchase_request/css/purchase-request.css'
                )
            );
            $data = $this->includes;

            $isMobile = true;
            if ( $detect->isMobile() ) {
                $isMobile = true;
            }
            
            $content['akses'] = $this->akses;
            $content['isMobile'] = $isMobile;
            $content['riwayat'] = $this->riwayat();
            $content['addForm'] = $this->addForm();

            $data['title_menu'] = 'Purchase Request';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getUnit() {
        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                w1.kode,
                REPLACE(REPLACE(w1.nama, 'Kota ', ''), 'Kab ', '') as nama
            from wilayah w1
            right join
                (select max(id) as id, kode from wilayah where kode is not null group by kode) w2
                on
                    w1.id = w2.id
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function riwayat() {
        $content['unit'] = $this->getUnit();
        $html = $this->load->view($this->pathView . 'riwayat', $content, TRUE);

        return $html;
    }

    public function addForm() {
        $content['unit'] = $this->getUnit();
        $html = $this->load->view($this->pathView . 'addForm', $content, TRUE);

        return $html;
    }
}