<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Vaksin extends Public_Controller
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
				'assets/parameter/vaksin/js/vaksin.js'
			));
			$this->add_external_css(array(
				'assets/parameter/vaksin/css/vaksin.css'
			));

			$data = $this->includes;

			$content['akses'] = $akses;
			
			$data['title_menu'] = 'Master Vaksin';
			$data['view'] = $this->load->view('parameter/vaksin/index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function get_list()
	{
		$m_vaksin = new \Model\Storage\Vaksin_model();
		$d_vaksin = $m_vaksin->where('status', '<>', getStatus('delete'))->with(['logs'])->orderBy('id', 'desc')->get();

		$content['data'] = $d_vaksin;
		$html = $this->load->view('parameter/vaksin/list', $content);

		echo $html;
	}

	public function add_form()
	{
        $content['title_panel'] = 'Master Vaksin';
        $this->load->view('parameter/vaksin/add_form', $content);
	}

	public function edit_form()
	{
		$id = $this->input->get('id');

		$m_vaksin = new \Model\Storage\Vaksin_model();
		$d_vaksin = $m_vaksin->where('id', $id)->first();

        $content['data'] = $d_vaksin;
        $this->load->view('parameter/vaksin/edit_form', $content);
	}

	public function save()
	{
		$params = $this->input->post('params');

		try {
			$m_vaksin = new \Model\Storage\Vaksin_model();

			$id_vaksin = $m_vaksin->getNextIdentity();

			$m_vaksin->id = $id_vaksin;
			$m_vaksin->nama_vaksin = $params['nama'];
			$m_vaksin->harga = $params['harga'];
			$m_vaksin->status = getStatus('submit');
			$m_vaksin->save();

			$d_vaksin = $m_vaksin->where('id', $id_vaksin)->first();

			$deskripsi_log_vaksin = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_vaksin, $deskripsi_log_vaksin );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data vaksin berhasil disimpan';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}

	public function edit()
	{
		$params = $this->input->post('params');

		try {
			$m_vaksin = new \Model\Storage\Vaksin_model();

			$m_vaksin->where('id', $params['id'])->update(
					array(
						'nama_vaksin' => $params['nama'],
						'harga' => $params['harga']
					)
				);

			$d_vaksin = $m_vaksin->where('id', $params['id'])->first();

			$deskripsi_log_vaksin = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_vaksin, $deskripsi_log_vaksin );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data vaksin berhasil di update';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}
}