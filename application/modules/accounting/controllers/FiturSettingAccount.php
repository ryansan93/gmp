<?php defined('BASEPATH') or exit('No direct script access allowed');

class FiturSettingAccount extends Public_Controller
{
    private $pathView = 'accounting/fitur_setting_account/';
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
                'assets/accounting/fitur_setting_account/js/fitur-setting-account.js')
            );
            $this->add_external_css(array(
                'assets/accounting/fitur_setting_account/css/fitur-setting-account.css')
            );
            $data = $this->includes;

            $content['akses'] = $this->akses;
            $content['datas'] = null;
            $content['title_panel'] = 'Setting Fitur Account';

            // Load Indexx
            // $content['riwayat'] = $this->load->view($this->pathView . 'list_basttb', $content, true);
            // $content['action'] = $this->load->view($this->pathView . 'input_basttb', $content, true);

            $data['title_menu'] = 'Setting Fitur Account';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_lists()
    {
        $m_coa = new \Model\Storage\Coa_model();
        $d_coa = $m_coa->with(['d_perusahaan', 'logs'])->orderBy('kode', 'asc')->get();

        $data = null;
        if ( $d_coa->count() > 0 ) {
            $data = $d_coa->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list', $content);

        echo $html;
    }

    public function get_perusahaan()
    {
        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $kode_perusahaan = $m_perusahaan->select('kode')->distinct('kode')->get();

        $data = null;
        if ( $kode_perusahaan->count() > 0 ) {
            $kode_perusahaan = $kode_perusahaan->toArray();

            foreach ($kode_perusahaan as $k => $val) {
                $m_perusahaan = new \Model\Storage\Perusahaan_model();
                $d_perusahaan = $m_perusahaan->where('kode', $val['kode'])->orderBy('version', 'desc')->first();

                $key = strtoupper($d_perusahaan->perusahaan).' - '.$d_perusahaan['kode'];
                $data[ $key ] = array(
                    'nama' => strtoupper($d_perusahaan->perusahaan),
                    'kode' => $d_perusahaan->kode
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function add_form()
    {
        $content['perusahaan'] = $this->get_perusahaan();
        $html = $this->load->view($this->pathView . 'add_form', $content); 
        
        echo $html;
    }

    public function view_form()
    {
        $id = $this->input->get('id');

        $m_coa = new \Model\Storage\Coa_model();
        $d_coa = $m_coa->where('id', $id)->with(['d_perusahaan', 'logs'])->first()->toArray();

        $content['data'] = $d_coa;
        $content['akses'] = $this->akses;

        $html = $this->load->view($this->pathView . 'view_form', $content); 
        
        echo $html;
    }

    public function edit_form()
    {
        $id = $this->input->get('id');

        $m_coa = new \Model\Storage\Coa_model();
        $d_coa = $m_coa->where('id', $id)->with(['d_perusahaan', 'logs'])->first()->toArray();

        $content['data'] = $d_coa;
        $content['perusahaan'] = $this->get_perusahaan();
        $content['akses'] = $this->akses;

        $html = $this->load->view($this->pathView . 'edit_form', $content); 
        
        echo $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_coa = new \Model\Storage\Coa_model();
            $m_coa->kode_perusahaan = $params['perusahaan'];
            $m_coa->nama = $params['nama'];
            $m_coa->kode = $params['kode'];
            $m_coa->deskripsi = $params['deskripsi'];
            $m_coa->save();

            $id_coa = $m_coa->id;

            $d_coa = $m_coa->where('id', $id_coa)->first();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_coa, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data COA berhasil disimpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit()
    {
        $params = $this->input->post('params');

        try {
            $id_coa = $params['id'];

            $m_coa = new \Model\Storage\Coa_model();
            $m_coa->where('id', $id_coa)->update(
                array(
                    'kode_perusahaan' => $params['perusahaan'],
                    'nama' => $params['nama'],
                    'kode' => $params['kode'],
                    'deskripsi' => $params['deskripsi']
                )
            );

            $d_coa = $m_coa->where('id', $id_coa)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_coa, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data COA berhasil di update.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {
            $id_coa = $params['id'];

            $m_coa = new \Model\Storage\Coa_model();
            $d_coa = $m_coa->where('id', $id_coa)->first();

            $m_coa->where('id', $id_coa)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_coa, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data COA berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}
