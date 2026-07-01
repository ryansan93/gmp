<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CreditNotePenjualan extends Public_Controller {

    private $path = 'transaksi/credit_note_penjualan/';
    // Credit Note Penjualan selalu untuk pelanggan/bakul
    private $jenis_cn = array(
        'BKL' => array('nama' => 'BAKUL', 'jenis' => 'bakul')
    );
    // Prefix nomor untuk memisahkan dari Credit Note biasa (yang memakai 'CN/')
    private $nomor_prefix = 'CNP/BKL';
    private $nomor_like = 'CNP/%';
    private $url;
    private $akses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->akses = hakAkses($this->url);
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index($segment=0)
    {
        if ( $this->akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/transaksi/credit_note_penjualan/js/credit-note-penjualan.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/credit_note_penjualan/css/credit-note-penjualan.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->akses;

            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();

            // Load Indexx
            $data['title_menu'] = 'Credit Note Penjualan';
            $data['view'] = $this->load->view($this->path.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $supplier = ($params['supplier'] == 'all') ? null : $params['supplier'];
        $jenis = ($params['supplier'] == 'all') ? null : 'bakul';

        $m_cn = new \Model\Storage\Cn_model();
        $data = $m_cn->getData(null, $start_date, $end_date, $supplier, $jenis, $this->nomor_like);

        $content['data'] = $data;

        $html = $this->load->view($this->path.'list', $content, TRUE);

        echo $html;
    }

    public function loadForm()
    {
        $params = $this->input->get('params');

        if ( isset($params['id']) && !empty($params['id']) ) {
            if ( isset($params['edit']) && !empty($params['edit']) ) {
                $html = $this->editForm( $params['id'] );
            } else {
                $html = $this->viewForm( $params['id'] );
            }
        } else {
            $html = $this->addForm();
        }

        echo $html;
    }

    public function riwayat() {
        $html = null;

        $m_plg = new \Model\Storage\Pelanggan_model();

        $content['pelanggan'] = $m_plg->getDataPelanggan(0);
        $content['akses'] = $this->akses;
        $html = $this->load->view($this->path.'riwayat', $content, TRUE);

        return $html;
    }

    public function addForm() {
        $html = null;

        $m_plg = new \Model\Storage\Pelanggan_model();

        $content['pelanggan'] = $m_plg->getDataPelanggan(0);
        $content['jenis_cn'] = $this->jenis_cn;
        $content['akses'] = $this->akses;
        $html = $this->load->view($this->path.'addForm', $content, TRUE);

        return $html;
    }

    public function editForm($id) {
        $html = null;

        $m_cn = new \Model\Storage\Cn_model();
        $data = $m_cn->getData($id)[0];

        $m_plg = new \Model\Storage\Pelanggan_model();

        $content['data'] = $data;
        $content['pelanggan'] = $m_plg->getDataPelanggan(0);
        $content['jenis_cn'] = $this->jenis_cn;
        $content['akses'] = $this->akses;
        $html = $this->load->view($this->path.'editForm', $content, TRUE);

        return $html;
    }

    public function viewForm($id) {
        $html = null;

        $m_cn = new \Model\Storage\Cn_model();
        $data = $m_cn->getData($id)[0];

        $content['data'] = $data;
        $content['jenis_cn'] = $this->jenis_cn;
        $content['akses'] = $this->akses;
        $html = $this->load->view($this->path.'viewForm', $content, TRUE);

        return $html;
    }

    public function save() {
        $data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

        try {
            $file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];

                $file_name = $moved['name'];
                $path_name = $moved['path'];
            }

            $m_cn = new \Model\Storage\Cn_model();
            $nomor = $m_cn->getNextNomor($this->nomor_prefix);

            $m_cn->nomor = $nomor;
            $m_cn->jenis_cn = 'BKL';
            $m_cn->tanggal = $data['tgl_cn'];
            $m_cn->supplier = $data['supplier'];
            $m_cn->ket_cn = $data['ket_cn'];
            $m_cn->tot_cn = $data['nilai_cn'];
            $m_cn->no_dok = null;
            $m_cn->path = $path_name;
            $m_cn->save();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_cn, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $m_cn->id);
            $this->result['message'] = 'Data CN Penjualan berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit() {
        $data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

        try {
            $file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];

                $file_name = $moved['name'];
                $path_name = $moved['path'];
            } else {
                $m_cn = new \Model\Storage\Cn_model();
                $d_cn = $m_cn->where('id', $data['id'])->first();

                $path_name = $d_cn->path;
            }

            $m_cn = new \Model\Storage\Cn_model();
            $m_cn->where('id', $data['id'])->update(
                array(
                    'jenis_cn' => 'BKL',
                    'tanggal' => $data['tgl_cn'],
                    'supplier' => $data['supplier'],
                    'ket_cn' => $data['ket_cn'],
                    'tot_cn' => $data['nilai_cn'],
                    'no_dok' => null,
                    'path' => $path_name,
                )
            );

            $d_cn = $m_cn->where('id', $data['id'])->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_cn, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $data['id']);
            $this->result['message'] = 'Data CN Penjualan berhasil di update.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete() {
        $params = $this->input->post('params');

        try {
            $m_cn = new \Model\Storage\Cn_model();
            $d_cn = $m_cn->where('id', $params['id'])->first();

            $m_cn->where('id', $params['id'])->delete();

            $deskripsi_log = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_cn, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data CN Penjualan berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}
