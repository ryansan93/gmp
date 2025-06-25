<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Solusi extends Public_Controller
{
	private $url;

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
				'assets/parameter/solusi/js/solusi.js'
			));
			$this->add_external_css(array(
				'assets/parameter/solusi/css/solusi.css'
			));

			$data = $this->includes;

			$content['akses'] = $akses;
			
			$data['title_menu'] = 'Master Solusi';
			$data['view'] = $this->load->view('parameter/solusi/index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function get_list()
	{
		$data = array();

		$m_solusi = new \Model\Storage\Solusi_model();
		$d_solusi = $m_solusi->get();

		if ( $d_solusi->count() > 0 ) {
			$d_solusi = $d_solusi->toArray();
			foreach ($d_solusi as $k => $val) {
				$data[ $val['keterangan'] ]['id'] = $val['id'];
				$data[ $val['keterangan'] ]['keterangan'] = $val['keterangan'];
			}

			ksort($data);
		}

		$content['data'] = $data;
		$html = $this->load->view('parameter/solusi/list', $content);

		echo $html;
	}

	public function add_form()
	{
        $content['title_panel'] = 'Master Solusi';
        $this->load->view('parameter/solusi/add_form', $content);
	}

	public function edit_form()
	{
		$id = $this->input->get('id');

		$m_solusi = new \Model\Storage\Solusi_model();
		$d_solusi = $m_solusi->where('id', $id)->first();

        $content['data'] = $d_solusi->toArray();
        $this->load->view('parameter/solusi/edit_form', $content);
	}

	public function save()
	{
		$params = $this->input->post('params');

		try {
			$m_solusi = new \Model\Storage\Solusi_model();

			$m_solusi->keterangan = $params;
			$m_solusi->save();

			$deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_solusi, $deskripsi_log );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data solusi berhasil di-simpan';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}

	public function edit()
	{
		$params = $this->input->post('params');

		try {
			$m_solusi = new \Model\Storage\Solusi_model();
			$m_solusi->where('id', $params['id'])->update(
				array(
					'keterangan' => $params['keterangan']
				)
			);

			$d_solusi = $m_solusi->where('id', $params['id'])->first();

			$deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_solusi, $deskripsi_log );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data solusi berhasil di-update';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}
}