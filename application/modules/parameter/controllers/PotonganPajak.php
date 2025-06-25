<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PotonganPajak extends Public_Controller
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
				'assets/parameter/potongan_pajak/js/potongan-pajak.js'
			));
			$this->add_external_css(array(
				'assets/parameter/potongan_pajak/css/potongan-pajak.css'
			));

			$data = $this->includes;

			$content['akses'] = $akses;
			
			$data['title_menu'] = 'Master Potongan Pajak';
			$data['view'] = $this->load->view('parameter/potongan_pajak/index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function get_list()
	{
		$data = array();

		$m_pp = new \Model\Storage\PotonganPajak_model();
		$d_pp = $m_pp->get();

		if ( $d_pp->count() > 0 ) {
			$d_pp = $d_pp->toArray();
			foreach ($d_pp as $k => $val) {
				$data[ $val['prs_potongan'] ]['id'] = $val['id'];
				$data[ $val['prs_potongan'] ]['prs_potongan'] = $val['prs_potongan'];
			}

			ksort($data);
		}

		$content['data'] = $data;
		$html = $this->load->view('parameter/potongan_pajak/list', $content);

		echo $html;
	}

	public function add_form()
	{
        $content['title_panel'] = 'Master Potongan Pajak';
        $html = $this->load->view('parameter/potongan_pajak/add_form', $content);

        echo $html;
	}

	public function edit_form()
	{
		$id = $this->input->get('id');

		$m_pp = new \Model\Storage\PotonganPajak_model();
		$d_pp = $m_pp->where('id', $id)->first();

        $content['data'] = $d_pp->toArray();
        $this->load->view('parameter/potongan_pajak/edit_form', $content);
	}

	public function save()
	{
		$params = $this->input->post('params');

		try {
			$m_pp = new \Model\Storage\PotonganPajak_model();

			$m_pp->prs_potongan = $params;
			$m_pp->save();

			$deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_pp, $deskripsi_log );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data potongan pajak berhasil di-simpan';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}

	public function edit()
	{
		$params = $this->input->post('params');

		try {
			$m_pp = new \Model\Storage\PotonganPajak_model();
			$m_pp->where('id', $params['id'])->update(
				array(
					'prs_potongan' => $params['prs_potongan']
				)
			);

			$d_pp = $m_pp->where('id', $params['id'])->first();

			$deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_pp, $deskripsi_log );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data potongan pajak berhasil di-update';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}

	public function delete()
	{
		$id = $this->input->post('id');

		try {
			$m_pp = new \Model\Storage\PotonganPajak_model();
			$d_pp = $m_pp->where('id', $id)->first();

			$m_pp->where('id', $id)->delete();

			$deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_pp, $deskripsi_log );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data potongan pajak berhasil di-hapus';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}
}