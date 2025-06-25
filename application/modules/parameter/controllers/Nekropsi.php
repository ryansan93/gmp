<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Nekropsi extends Public_Controller
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
				'assets/parameter/nekropsi/js/nekropsi.js'
			));
			$this->add_external_css(array(
				'assets/parameter/nekropsi/css/nekropsi.css'
			));

			$data = $this->includes;

			$content['akses'] = $akses;
			
			$data['title_menu'] = 'Master Nekropsi';
			$data['view'] = $this->load->view('parameter/nekropsi/index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function get_list()
	{
		$data = array();

		$m_nekropsi = new \Model\Storage\Nekropsi_model();
		$d_nekropsi = $m_nekropsi->get();

		if ( $d_nekropsi->count() > 0 ) {
			$d_nekropsi = $d_nekropsi->toArray();
			foreach ($d_nekropsi as $k => $val) {
				$data[ $val['keterangan'] ]['id'] = $val['id'];
				$data[ $val['keterangan'] ]['keterangan'] = $val['keterangan'];
			}

			ksort($data);
		}

		$content['data'] = $data;
		$html = $this->load->view('parameter/nekropsi/list', $content);

		echo $html;
	}

	public function add_form()
	{
        $content['title_panel'] = 'Master Nekropsi';
        $this->load->view('parameter/nekropsi/add_form', $content);
	}

	public function edit_form()
	{
		$id = $this->input->get('id');

		$m_nekropsi = new \Model\Storage\Nekropsi_model();
		$d_nekropsi = $m_nekropsi->where('id', $id)->first();

        $content['data'] = $d_nekropsi->toArray();
        $this->load->view('parameter/nekropsi/edit_form', $content);
	}

	public function save()
	{
		$params = $this->input->post('params');

		try {
			$m_nekropsi = new \Model\Storage\Nekropsi_model();

			$m_nekropsi->keterangan = $params;
			$m_nekropsi->save();

			$deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_nekropsi, $deskripsi_log );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data nekropsi berhasil di-simpan';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}

	public function edit()
	{
		$params = $this->input->post('params');

		try {
			$m_nekropsi = new \Model\Storage\Nekropsi_model();
			$m_nekropsi->where('id', $params['id'])->update(
				array(
					'keterangan' => $params['keterangan']
				)
			);

			$d_nekropsi = $m_nekropsi->where('id', $params['id'])->first();

			$deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_nekropsi, $deskripsi_log );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data nekropsi berhasil di-update';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}
}