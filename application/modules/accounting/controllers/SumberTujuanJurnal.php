<?php defined('BASEPATH') or exit('No direct script access allowed');

class SumberTujuanJurnal extends Public_Controller
{
    private $pathView = 'accounting/sumber_tujuan_jurnal/';
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

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index()
    {
        if ( $this->akses['a_view'] == 1 ) {
            // $this->set_title('Berita Acara Serah Terima Titip Budidaya');
            $this->add_external_js(array(
                'assets/accounting/sumber_tujuan_jurnal/js/sumber-tujuan-jurnal.js')
            );
            $this->add_external_css(array(
                'assets/accounting/sumber_tujuan_jurnal/css/sumber-tujuan-jurnal.css')
            );
            $data = $this->includes;

            $content['akses'] = $this->akses;
            $content['datas'] = null;
            $content['title_panel'] = 'Sumber Tujuan Jurnal';

            $data['title_menu'] = 'Sumber Tujuan Jurnal';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getLists()
    {
        $m_jtst = new \Model\Storage\JurnalTransSumberTujuan_model();
        $d_jtst = $m_jtst->with(['jurnal_trans'])->orderBy('nama', 'asc')->get();

        $data = null;
        if ( $d_jtst->count() > 0 ) {
            $data = $d_jtst->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list', $content);

        echo $html;
    }

    public function getJurnalTrans()
    {
        $m_jt = new \Model\Storage\JurnalTrans_model();
        $d_jt = $m_jt->orderBy('nama', 'asc')->get();

        $data = null;
        if ( $d_jt->count() > 0 ) {
            $data = $d_jt->toArray();
        }

        return $data;
    }

    public function addForm()
    {
        $content['jurnal_trans'] = $this->getJurnalTrans();
        $html = $this->load->view($this->pathView . 'add_form', $content); 
        
        echo $html;
    }

    public function viewForm()
    {
        $id = $this->input->get('id');

        $m_jtst = new \Model\Storage\JurnalTransSumberTujuan_model();
        $d_jtst = $m_jtst->where('id', $id)->with(['jurnal_trans', 'logs'])->first()->toArray();

        $content['data'] = $d_jtst;
        $content['akses'] = $this->akses;

        $html = $this->load->view($this->pathView . 'view_form', $content); 
        
        echo $html;
    }

    public function editForm()
    {
        $id = $this->input->get('id');

        $m_jtst = new \Model\Storage\JurnalTransSumberTujuan_model();
        $d_jtst = $m_jtst->where('id', $id)->with(['jurnal_trans'])->first()->toArray();

        $content['data'] = $d_jtst;
        $content['jurnal_trans'] = $this->getJurnalTrans();
        $content['akses'] = $this->akses;

        $html = $this->load->view($this->pathView . 'edit_form', $content); 
        
        echo $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_jtst = new \Model\Storage\JurnalTransSumberTujuan_model();
            $m_jtst->id_header = $params['jurnal_trans_id'];
            $m_jtst->nama = $params['nama'];
            $m_jtst->save();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_jtst, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Sumber / Tujuan Jurnal berhasil disimpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit()
    {
        $params = $this->input->post('params');

        try {
            $id = $params['id'];

            $m_jtst = new \Model\Storage\JurnalTransSumberTujuan_model();
            $m_jtst->where('id', $id)->update(
                array(
                    'id_header' => $params['jurnal_trans_id'],
                    'nama' => $params['nama']
                )
            );

            $d_jtst = $m_jtst->where('id', $id)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_jtst, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Sumber / Tujuan Jurnal berhasil di update.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {
            $id = $params['id'];

            $m_jtst = new \Model\Storage\JurnalTransSumberTujuan_model();
            $d_jtst = $m_jtst->where('id', $id)->first();

            $m_jtst->where('id', $id)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_jtst, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Sumber / Tujuan Jurnal berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}