<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Gudang extends Public_Controller {

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
                "assets/jquery/list.min.js",
                "assets/parameter/gudang/js/gudang.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/parameter/gudang/css/gudang.css",
            ));

            $data = $this->includes;

            $m_gudang = new \Model\Storage\Gudang_model();
            $d_gudang = $m_gudang->with(['logs'])->orderBy('id', 'DESC')->get()->toArray();

            $content['akses'] = $akses;
            $content['data'] = $d_gudang;
            $content['title_menu'] = 'Master Gudang';

            // Load Indexx
            $data['view'] = $this->load->view('parameter/gudang/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_list()
    {
        $m_gudang = new \Model\Storage\Gudang_model();
        $d_gudang = $m_gudang->with(['dUnit', 'd_perusahaan', 'logs'])->orderBy('id', 'desc')->get();

        $data = null;
        if ( $d_gudang->count() > 0 ) {
            $data = $d_gudang->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view('parameter/gudang/list', $content);

        echo $html;
    }

    public function get_unit()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'UN')->orderBy('nama', 'desc')->get();

        $data = null;
        if ( $d_wilayah->count() > 0 ) {
            $data = $d_wilayah->toArray();
        }

        return $data;
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
                    'kode' => $d_perusahaan->kode,
                    'jenis_mitra' => $d_perusahaan->jenis_mitra
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function add_form()
    {
        $content['unit'] = $this->get_unit();
        $content['perusahaan'] = $this->get_perusahaan();
        $html = $this->load->view('parameter/gudang/add_form', $content); 
        
        echo $html;
    }

    public function view_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        $m_gudang = new \Model\Storage\Gudang_model();
        $d_gudang = $m_gudang->where('id', $id)
                           ->with(['logs'])
                           ->orderBy('id', 'DESC')
                           ->first()->toArray();

        $content['akses'] = $akses;
        $content['data'] = $d_gudang;
        $html = $this->load->view('parameter/gudang/view_form', $content);
        
        return $html;
    }

    public function edit_form()
    {
        $id = $this->input->get('id');

        $akses = hakAkses($this->url);

        $m_gudang = new \Model\Storage\Gudang_model();
        $d_gudang = $m_gudang->where('id', $id)
                           ->with(['logs'])
                           ->orderBy('id', 'DESC')
                           ->first();

        $content['akses'] = $akses;
        $content['data'] = $d_gudang;
        $content['unit'] = $this->get_unit();
        $content['perusahaan'] = $this->get_perusahaan();
        $html = $this->load->view('parameter/gudang/edit_form', $content);
        
        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_gudang = new \Model\Storage\Gudang_model();

            $m_gudang->nama = $params['nama'];
            $m_gudang->alamat = $params['alamat'];
            $m_gudang->jenis = $params['jenis'];
            $m_gudang->penanggung_jawab = $params['penanggung_jawab'];
            $m_gudang->unit = $params['unit'];
            $m_gudang->perusahaan = $params['perusahaan'];
            $m_gudang->save();

            $id_gudang = $m_gudang->id;

            $d_gudang = $m_gudang->where('id', $id_gudang)->first();

            $deskripsi_log_gudang = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_gudang, $deskripsi_log_gudang );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data gudang berhasil disimpan';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit()
    {
        $params = $this->input->post('params');

        try {
            $m_gudang = new \Model\Storage\Gudang_model();

            $m_gudang->where('id', $params['id'])->update(
                array(
                    'nama' => $params['nama'],
                    'alamat' => $params['alamat'],
                    'jenis' => $params['jenis'],
                    'penanggung_jawab' => $params['penanggung_jawab'],
                    'unit' => $params['unit'],
                    'perusahaan' => $params['perusahaan']
                )
            );

            $id_gudang = $params['id'];

            $d_gudang = $m_gudang->where('id', $id_gudang)->first();

            $deskripsi_log_gudang = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_gudang, $deskripsi_log_gudang );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data gudang berhasil di update';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }
}