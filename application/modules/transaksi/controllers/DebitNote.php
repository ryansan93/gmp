<?php defined('BASEPATH') OR exit('No direct script access allowed');

class DebitNote extends Public_Controller {

    private $path = 'transaksi/debit_note/';
    private $jenis_dn = array(
        'DOC' => array('nama' => 'DOC', 'jenis' => 'supplier'),
        'PKN' => array('nama' => 'PAKAN', 'jenis' => 'supplier'),
        'OVK' => array('nama' => 'OVK', 'jenis' => 'supplier'),
        'RHPP' => array('nama' => 'RHPP', 'jenis' => 'mitra'),
        'OA' => array('nama' => 'OA', 'jenis' => 'ekspedisi'),
        'BKL' => array('nama' => 'BAKUL', 'jenis' => 'bakul'),
        'NS' => array('nama' => 'NON SAPRONAK', 'jenis' => 'supplier')
    );
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
                "assets/transaksi/debit_note/js/debit-note.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/debit_note/css/debit-note.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->akses;

            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();

            // Load Indexx
            $data['title_menu'] = 'Debit Note';
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
        $jenis = ($params['supplier'] == 'all') ? null : $params['jenis'];

        $m_dn = new \Model\Storage\Dn_model();
        $data = $m_dn->getData(null, $start_date, $end_date, $supplier, $jenis);

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

        $m_supl = new \Model\Storage\Supplier_model();
        $m_plg = new \Model\Storage\Pelanggan_model();
        $m_eks = new \Model\Storage\Ekspedisi_model();
        $m_mitra = new \Model\Storage\Mitra_model();

        $content['supplier'] = $m_supl->getDataSupplier(0);
        $content['pelanggan'] = $m_plg->getDataPelanggan(0);
        $content['ekspedisi'] = $m_eks->getDataEskpedisi(0);
        $content['mitra'] = $m_mitra->getDataMitra(0);
        $content['akses'] = $this->akses;
        $html = $this->load->view($this->path.'riwayat', $content, TRUE);

        return $html;
    }

    public function addForm() {
        $html = null;

        $m_supl = new \Model\Storage\Supplier_model();
        $m_plg = new \Model\Storage\Pelanggan_model();
        $m_eks = new \Model\Storage\Ekspedisi_model();
        $m_mitra = new \Model\Storage\Mitra_model();

        $content['supplier'] = $m_supl->getDataSupplier(0);
        $content['pelanggan'] = $m_plg->getDataPelanggan(0);
        $content['ekspedisi'] = $m_eks->getDataEskpedisi(0);
        $content['mitra'] = $m_mitra->getDataMitra(0);
        $content['jenis_dn'] = $this->jenis_dn;
        $content['akses'] = $this->akses;
        $html = $this->load->view($this->path.'addForm', $content, TRUE);

        return $html;
    }

    public function editForm($id) {
        $html = null;

        $m_dn = new \Model\Storage\Dn_model();
        $data = $m_dn->getData($id)[0];

        $m_supl = new \Model\Storage\Supplier_model();
        $m_plg = new \Model\Storage\Pelanggan_model();
        $m_eks = new \Model\Storage\Ekspedisi_model();
        $m_mitra = new \Model\Storage\Mitra_model();

        $content['data'] = $data;
        $content['supplier'] = $m_supl->getDataSupplier(0);
        $content['pelanggan'] = $m_plg->getDataPelanggan(0);
        $content['ekspedisi'] = $m_eks->getDataEskpedisi(0);
        $content['mitra'] = $m_mitra->getDataMitra(0);
        $content['jenis_dn'] = $this->jenis_dn;
        $content['akses'] = $this->akses;
        $html = $this->load->view($this->path.'editForm', $content, TRUE);

        return $html;
    }

    public function viewForm($id) {
        $html = null;

        $m_dn = new \Model\Storage\Dn_model();
        $data = $m_dn->getData($id)[0];
        
        $content['data'] = $data;
        $content['jenis_dn'] = $this->jenis_dn;
        $content['akses'] = $this->akses;
        $html = $this->load->view($this->path.'viewForm', $content, TRUE);

        return $html;
    }

    public function save() {
        $data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

        try {
            // cetak_r( $data, 1 );

            $file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];

                $file_name = $moved['name'];
                $path_name = $moved['path'];
            }
            
            $m_dn = new \Model\Storage\Dn_model();
            $nomor = $m_dn->getNextNomor('DN/'.$data['jenis_dn']);

            $m_dn->nomor = $nomor;
            $m_dn->jenis_dn = $data['jenis_dn'];
            $m_dn->tanggal = $data['tgl_dn'];
            $m_dn->supplier = $data['supplier'];
            $m_dn->ket_dn = $data['ket_dn'];
            $m_dn->tot_dn = $data['nilai_dn'];
            $m_dn->no_dok = $data['no_dok'];
            $m_dn->path = $path_name;
            $m_dn->save();

            // Modules::run( 'base/InsertJurnal/exec', $this->url, $data['id'], $data['id'], 2, null, $data['tgl_bayar']);

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_dn, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $m_dn->id);
            $this->result['message'] = 'Data DN berhasil di simpan.';
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
                $m_dn = new \Model\Storage\Dn_model();
                $d_dn = $m_dn->where('id', $data['id'])->first();

                $path_name = $d_dn->path;
            }
            
            $m_dn = new \Model\Storage\Dn_model();
            $m_dn->where('id', $data['id'])->update(
                array(
                    'jenis_dn' => $data['jenis_dn'],
                    'tanggal' => $data['tgl_dn'],
                    'supplier' => $data['supplier'],
                    'ket_dn' => $data['ket_dn'],
                    'tot_dn' => $data['nilai_dn'],
                    'no_dok' => $data['no_dok'],
                    'path' => $path_name,
                )
            );

            $d_dn = $m_dn->where('id', $data['id'])->first();

            // Modules::run( 'base/InsertJurnal/exec', $this->url, $data['id'], $data['id'], 2, null, $data['tgl_bayar']);

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_dn, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $data['id']);
            $this->result['message'] = 'Data DN berhasil di update.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete() {
        $params = $this->input->post('params');

        try {
            $m_dn = new \Model\Storage\Dn_model();
            $d_dn = $m_dn->where('id', $params['id'])->first();
            
            $m_dn->where('id', $params['id'])->delete();

            // Modules::run( 'base/InsertJurnal/exec', $this->url, $data['id'], $data['id'], 2, null, $data['tgl_bayar']);

            $deskripsi_log = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_dn, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data DN berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes() {
        $m_dn = new \Model\Storage\Dn_model();
        $nomor = $m_dn->getNextNomor('DN/DOC');

        cetak_r( $nomor, 1 );
    }
}