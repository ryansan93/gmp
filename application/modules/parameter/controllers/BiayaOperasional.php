<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BiayaOperasional extends Public_Controller
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
				'assets/parameter/biaya_operasional/js/biaya-operasional.js'
			));
			$this->add_external_css(array(
				'assets/parameter/biaya_operasional/css/biaya-operasional.css'
			));

			$data = $this->includes;

			$content['akses'] = $akses;
			
			$data['title_menu'] = 'Master Biaya Operasional';
			$data['view'] = $this->load->view('parameter/biaya_operasional/index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function get_list()
	{
		$m_bo = new \Model\Storage\BiayaOperasional_model();
		$d_bo = $m_bo->orderBy('tgl_berlaku', 'desc')->get();

		$data = null;
		if ( $d_bo->count() > 0 ) {
			$data = $d_bo->toArray();
		}

		$content['data'] = $data;
		$html = $this->load->view('parameter/biaya_operasional/list', $content);

		echo $html;
	}

	public function add_form()
	{
        $content['title_panel'] = 'Master Biaya Operasioanl';
        $this->load->view('parameter/biaya_operasional/add_form', $content);
	}

	public function edit_form()
	{
		$id = $this->input->get('id');

		$m_bo = new \Model\Storage\BiayaOperasional_model();
		$d_bo = $m_bo->where('id', $id)->first()->toArray();

        $content['data'] = $d_bo;
        $this->load->view('parameter/biaya_operasional/edit_form', $content);
	}

	public function save()
	{
		$params = $this->input->post('params');

		try {
			$m_bo = new \Model\Storage\BiayaOperasional_model();

			$m_bo->tgl_berlaku = $params['tgl_berlaku'];
			$m_bo->biaya_opr = $params['biaya_opr'];
			$m_bo->save();

			$deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_bo, $deskripsi_log );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data biaya operasional berhasil disimpan';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}

	public function edit()
	{
		$params = $this->input->post('params');

		try {
			$m_bo = new \Model\Storage\BiayaOperasional_model();

			$m_bo->where('id', $params['id'])->update(
					array(
						'tgl_berlaku' => $params['tgl_berlaku'],
						'biaya_opr' => $params['biaya_opr']
					)
				);

			$d_bo = $m_bo->where('id', $params['id'])->first();

			$deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_bo, $deskripsi_log );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data biaya operasional berhasil di update';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}
}