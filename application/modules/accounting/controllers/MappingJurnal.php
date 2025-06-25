<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MappingJurnal extends Public_Controller
{
    private $pathView = 'accounting/mapping_jurnal/';
    private $url;
    private $hakAkses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->hakAkses = hakAkses($this->url);
    }

    public function index()
    {
        if ( $this->hakAkses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                'assets/accounting/mapping_jurnal/js/mapping-jurnal.js'
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/accounting/mapping_jurnal/css/mapping-jurnal.css'
            ));

            $data = $this->includes;

            $data['title_menu'] = 'Mapping Jurnal';

            $content['add_form'] = $this->add_form();
            $content['riwayat'] = $this->riwayat();
            // $content['riwayat'] = 'RIWAYAT';

            $content['akses'] = $this->hakAkses;
            $data['view'] = $this->load->view($this->pathView . 'index', $content, true);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $jurnal_report_id = $params['jurnal_report_id'];

        $m_jm = new \Model\Storage\JurnalMapping_model();
        $d_jm = $m_jm->where('jurnal_report_id', $jurnal_report_id)->with(['det_jurnal_trans', 'jurnal_report'])->get();

        $data = null;
        if ( $d_jm->count() > 0 ) {
            $data = $d_jm->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list', $content, true);

        echo $html;
    }

    public function load_form()
    {
        $params = $this->input->get('params');
        $edit = $this->input->get('edit');

        $id = $params['id'];

        $content = array();
        $html = "url not found";
        
        if ( !empty($id) && $edit != 'edit' ) {
            // NOTE: view data BASTTB (ajax)
            $html = $this->detail_form($id);
        } else if ( !empty($id) && $edit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $html = $this->edit_form($id);
        }else{
            $html = $this->add_form();
        }

        echo $html;
    }

    public function getDetJurnalTrans()
    {
        $m_djt = new \Model\Storage\DetJurnalTrans_model();
        $d_djt = $m_djt->orderBy('nama', 'asc')->with(['jurnal_trans'])->get();

        $data = null;
        if ( $d_djt->count() > 0 ) {
            $data = $d_djt->toArray();
        }

        return $data;
    }

    public function getJurnalReport()
    {
        $m_jr = new \Model\Storage\JurnalReport_model();
        $d_jr = $m_jr->where('mstatus', 1)->orderBy('nama', 'asc')->get();

        $data = null;
        if ( $d_jr->count() > 0 ) {
            $data = $d_jr->toArray();
        }

        return $data;
    }

    public function riwayat()
    {
        $html = $this->load->view($this->pathView . 'riwayat', null, true);

        return $html;
    }

    public function add_form()
    {
        $content['det_jurnal_trans'] = $this->getDetJurnalTrans();
        $content['jurnal_report'] = $this->getJurnalReport();

        $html = $this->load->view($this->pathView . 'add_form', $content, true);

        return $html;
    }

    public function detail_form($id)
    {
        $m_jm = new \Model\Storage\JurnalMapping_model();
        $d_jm = $m_jm->where('id', $id)->with(['det_jurnal_trans', 'jurnal_report'])->first();

        $data = null;
        if ( $d_jm ) {
            $data = $d_jm->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'view_form', $content, true);

        return $html;
    }

    public function edit_form($id)
    {
        $m_jm = new \Model\Storage\JurnalMapping_model();
        $d_jm = $m_jm->where('id', $id)->with(['det_jurnal_trans', 'jurnal_report'])->first();

        $data = null;
        if ( $d_jm ) {
            $data = $d_jm->toArray();
        }

        $content['det_jurnal_trans'] = $this->getDetJurnalTrans();
        $content['jurnal_report'] = $this->getJurnalReport();
        $content['data'] = $data;

        $html = $this->load->view($this->pathView . 'edit_form', $content, true);

        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');
        try {
            $m_jm = new \Model\Storage\JurnalMapping_model();

            $m_jm->det_jurnal_trans_id = $params['det_jurnal_trans_id'];
            $m_jm->jurnal_report_id = $params['jurnal_report_id'];
            $m_jm->posisi = $params['posisi'];
            $m_jm->save();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_jm, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $m_jm->id);
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit()
    {
        $params = $this->input->post('params');
        try {
            $m_jm = new \Model\Storage\JurnalMapping_model();

            $m_jm->where('id', $params['id'])->update(
                array(
                    'det_jurnal_trans_id' => $params['det_jurnal_trans_id'],
                    'jurnal_report_id' => $params['jurnal_report_id'],
                    'posisi' => $params['posisi'],
                    'mstatus' => 1
                )
            );

            $d_jm = $m_jm->where('id', $params['id'])->first();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_jm, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $params['id']);
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {
            $m_jm = new \Model\Storage\JurnalMapping_model();
            $d_jm = $m_jm->where('id', $params['id'])->first();

            $m_jm->where('id', $params['id'])->delete();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_jm, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}