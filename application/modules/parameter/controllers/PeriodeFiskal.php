<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PeriodeFiskal extends Public_Controller
{
	private $url;
    private $pathView = 'parameter/periode_fiskal/';

	function __construct()
	{
		parent::__construct();
		$this->url = $this->current_base_uri;
	}

	public function index()
	{
		$akses = hakAkses($this->url);
		// if ( $akses['a_view'] == 1 ) {
			$this->add_external_js(array(
				'assets/parameter/periode_fiskal/js/periode-fiskal.js'
			));
			$this->add_external_css(array(
				'assets/parameter/periode_fiskal/css/periode-fiskal.css'
			));

			$data = $this->includes;

			$content['akses'] = $akses;
			
			$data['title_menu'] = 'Periode Fiskal';
			$data['view'] = $this->load->view($this->pathView.'index', $content, true);

			$this->load->view($this->template, $data);
		// } else {
		// 	showErrorAkses();
		// }
	}

	public function get_list()
	{
		$m_pf = new \Model\Storage\PeriodeFiskal_model();
		$d_pf = $m_pf->orderBy('periode', 'desc')->get();

		$data = null;
		if ( $d_pf->count() > 0 ) {
			$data = $d_pf->toArray();
		}

		$content['data'] = $data;
		$html = $this->load->view($this->pathView.'list', $content);

		echo $html;
	}

	public function add_form()
	{
        $content = null;
        $this->load->view($this->pathView.'add_form', $content);
	}

	public function edit_form()
	{
		$id = $this->input->get('id');

		$m_pf = new \Model\Storage\PeriodeFiskal_model();
		$d_pf = $m_pf->where('id', $id)->first()->toArray();

        $content['data'] = $d_pf;
        $this->load->view($this->pathView.'edit_form', $content);
	}

	public function save()
	{
		$params = $this->input->post('params');

		try {
			$m_bo = new \Model\Storage\PeriodeFiskal_model();

			$m_bo->periode = substr($params['periode'], 0, 7);
			$m_bo->start_date = $params['start_date'];
			$m_bo->end_date = $params['end_date'];
			$m_bo->status = $params['status'];
			$m_bo->save();

			$deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_bo, $deskripsi_log );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data biaya periode fiskal berhasil disimpan';
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