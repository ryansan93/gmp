<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BadanUsaha extends Public_Controller
{
	private $url;
    private $pathView = 'parameter/badan_usaha/';

	function __construct()
	{
		parent::__construct();
		$this->url = $this->current_base_uri;
	}

	public function index()
	{
		$akses = hakAkses($this->url);
		if ( $akses['a_view'] == 1 ) {
			$this->add_external_js(array(
				'assets/parameter/badan_usaha/js/badan-usaha.js'
			));
			$this->add_external_css(array(
				'assets/parameter/badan_usaha/css/badan-usaha.css'
			));

			$data = $this->includes;

			$content['akses'] = $akses;

			$data['title_menu'] = 'Master Badan Usaha';
			$data['view'] = $this->load->view($this->pathView.'index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function getLists()
	{
		$m_bu = new \Model\Storage\BadanUsaha_model();
		$d_bu = $m_bu->orderBy('id_badan_usaha', 'asc')->get();

		$data = null;
		if ( $d_bu->count() > 0 ) {
			$data = $d_bu->toArray();
		}

		$content['data'] = $data;
		$html = $this->load->view($this->pathView.'list', $content);

		echo $html;
	}

	public function addForm()
	{
        $content['title_panel'] = 'Master Badan Usaha';
        $this->load->view($this->pathView.'addForm', $content);
	}

	public function editForm()
	{
		$id = $this->input->get('id');

		$m_bu = new \Model\Storage\BadanUsaha_model();
		$d_bu = $m_bu->where('id_badan_usaha', $id)->first()->toArray();

        $content['data'] = $d_bu;
        $html = $this->load->view($this->pathView.'editForm', $content);

        echo $html;
	}

	public function save()
	{
		$params = $this->input->post('params');

		try {
			$m_bu = new \Model\Storage\BadanUsaha_model();
			$id_badan_usaha = $m_bu->getNextKode();

			$m_bu->id_badan_usaha = $id_badan_usaha;
			$m_bu->nama_badan_usaha = $params['nama_badan_usaha'];
			$m_bu->singkatan = !empty($params['singkatan']) ? $params['singkatan'] : null;
			$m_bu->status_hukum = $params['status_hukum'];
			$m_bu->is_terbuka = $params['is_terbuka'];
			$m_bu->save();

			$deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_bu, $deskripsi_log );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data badan usaha berhasil disimpan';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}

	public function edit()
	{
		$params = $this->input->post('params');

		try {
			$m_bu = new \Model\Storage\BadanUsaha_model();
			$m_bu->where('id_badan_usaha', $params['id_badan_usaha'])->update(
                array(
                    'nama_badan_usaha' => $params['nama_badan_usaha'],
                    'singkatan' => !empty($params['singkatan']) ? $params['singkatan'] : null,
                    'status_hukum' => $params['status_hukum'],
                    'is_terbuka' => $params['is_terbuka']
                )
            );

			$d_bu = $m_bu->where('id_badan_usaha', $params['id_badan_usaha'])->first();

			$deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_bu, $deskripsi_log );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data badan usaha berhasil di update';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}
}
