<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Fitur extends Public_Controller
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
				'assets/master/fitur/js/fitur.js'
			));
			$this->add_external_css(array(
				'assets/master/fitur/css/fitur.css'
			));

			$data = $this->includes;

			$data['title_menu'] = 'Master Fitur';

			$content['akses'] = $akses;
			$data['view'] = $this->load->view('master/fitur/index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function list_fitur()
	{
		$akses = hakAkses($this->url);

		$m_ftr = new \Model\Storage\Fitur_model();
		$d_ftr = $m_ftr->where('status', 1)->with(['detail_fitur'])->get()->toArray();

		$content['akses'] = $akses;
		$content['list'] = $d_ftr;
		$html = $this->load->view('master/fitur/list', $content, true);

		echo $html;
	}

	public function add_form()
	{
		$data['data'] = null;
		$this->load->view('master/fitur/add_form', $data);
	}

	public function edit_form()
	{
		$id_fitur = $this->input->get('id_fitur');

		$m_ftr = new \Model\Storage\Fitur_model();
		$d_ftr = $m_ftr->where('id_fitur', trim($id_fitur))->with(['detail_fitur'])->first();

		$data['data'] = $d_ftr;
		$this->load->view('master/fitur/edit_form', $data);
	}

	public function save_data()
	{
		$params = $this->input->post('params');

		try {
			$cek_parent = $this->cek_parent($params['nama_parent']);
			if ( !$cek_parent ) {
				$m_ftr = new \Model\Storage\Fitur_model();

				$id_fitur = $m_ftr->getNextId();

				$m_ftr->id_fitur = $id_fitur;
				$m_ftr->nama_fitur = $params['nama_parent'];
				$m_ftr->save();

				foreach ($params['detail_fitur'] as $key => $val) {
					$m_dftr = new \Model\Storage\DetFitur_model();

					$id_detfitur = $m_dftr->getNextId();

					$m_dftr->id_detfitur = $id_detfitur;
					$m_dftr->nama_detfitur = $val['nama_fitur'];
					$m_dftr->path_detfitur = $val['path_fitur'];
					$m_dftr->id_fitur = $id_fitur;
					$m_dftr->save();
				}

			    $this->result['status'] = 1;
				$this->result['message'] = 'Data berhasil di simpan';
			} else {
				$this->result['message'] = 'Nama judul menu yang anda masukkan sudah ada.';
			}
		} catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

		display_json($this->result);
	}

	public function edit_data()
	{
		$params = $this->input->post('params');

		try {
			$cek_parent = $this->cek_parent($params['nama_parent'], $params['id_parent']);
			if ( isset($cek_parent) ) {
				if ( $cek_parent['id_fitur'] == $params['id_parent'] ) {
					$this->exec_edit($params);

				    $this->result['status'] = 1;
					$this->result['message'] = 'Data berhasil di edit';
				} else {
					$this->result['message'] = 'Nama judul menu yang anda masukkan sudah ada.';	
				}
			} else {
				$this->exec_edit($params);

			    $this->result['status'] = 1;
				$this->result['message'] = 'Data berhasil di edit';
			}
		} catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

		display_json($this->result);
	}

	public function exec_edit($params)
	{
		$m_ftr = new \Model\Storage\Fitur_model();

		$id_fitur = $params['id_parent'];

		$m_ftr->where('id_fitur', $id_fitur)->update(
			array('nama_fitur'=>$params['nama_parent'])
		);

		// $m_dftr = new \Model\Storage\DetFitur_model();
		// $m_dftr->where('id_fitur', $id_fitur)->delete();

		foreach ($params['detail_fitur'] as $key => $val) {
			$m_dftr = new \Model\Storage\DetFitur_model();

			if ( !empty($val['id_detfitur']) ) {
				$m_dftr->where('id_detfitur', $val['id_detfitur'])->update(
						array(
							'nama_detfitur' => $val['nama_fitur'],
							'path_detfitur' => $val['path_fitur']
						)
					);
			} else {
				$id_detfitur = $m_dftr->getNextId();

				$m_dftr->id_detfitur = $id_detfitur;
				$m_dftr->nama_detfitur = $val['nama_fitur'];
				$m_dftr->path_detfitur = $val['path_fitur'];
				$m_dftr->id_fitur = $id_fitur;
				$m_dftr->save();
			}
		}
	}

	public function delete_data()
	{
		$id_fitur = $this->input->post('params');

		try {
			// $m_dftr = new \Model\Storage\DetFitur_model();			
			// $m_dftr->where('id_fitur', $id_fitur)->delete();

			$m_ftr = new \Model\Storage\Fitur_model();
			$m_ftr->where('id_fitur', $id_fitur)->update(
				array(
					'status' => 0
				)
			);

		    $this->result['status'] = 1;
			$this->result['message'] = 'Data berhasil di hapus';
		} catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

		display_json($this->result);
	}

	public function cek_parent($nama_fitur='', $id_fitur=null)
	{
		$m_ftr = new \Model\Storage\Fitur_model();

		if ( !isset($id_fitur) ) {
			$d_ftr = $m_ftr->where('nama_fitur', trim($nama_fitur))->first();
			$val = false;
			if ( isset($d_ftr['id_fitur']) ) {
				$val = true;
			}
			return $val;
		} else {
			$d_ftr = $m_ftr->where('nama_fitur', trim($nama_fitur))->first();
			$data = null;
			if ( isset($d_ftr['id_fitur']) ) {
				$data = array(
					'id_fitur'=>$d_ftr['id_fitur'],
					'nama_fitur'=>$d_ftr['nama_fitur']
				);
			}

			return $data;
		}

	}
}