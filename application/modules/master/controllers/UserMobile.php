<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UserMobile extends Public_Controller
{
	private $url;
	function __construct()
	{
		parent::__construct();
		$this->url = $this->current_base_uri;
	}

	public function index()
	{
		$url = $this->current_uri;
		$akses = hakAkses($url);

		if ( $akses['a_view'] == 1 ) {
			$this->add_external_js(array(
				'assets/master/user_mobile/js/user-mobile.js'
			));
			$this->add_external_css(array(
				'assets/master/user_mobile/css/user-mobile.css'
			));

			$data = $this->includes;

			$data['title_menu'] = 'Master User Mobile';

			$content['akses'] = $akses;
			$data['view'] = $this->load->view('master/user_mobile/index', $content, true);

			$this->load->view($this->template, $data);
		} else {
        	showErrorAkses();
		}
	}

	public function list_user()
	{
		$akses = hakAkses($this->url);

		$m_um = new \Model\Storage\UserMobile_model();
		$d_um = $m_um->get()->toArray();

		$content['akses'] = $akses;
		$content['list'] = $d_um;
		$html = $this->load->view('master/user_mobile/list', $content, true);

		echo $html;
	}

	public function add_form()
	{
		$m_karyawan = new \Model\Storage\Karyawan_model();
		$d_karyawan = $m_karyawan->where('status', 1)->orderBy('jabatan', 'asc')->get();

		$data = null;
		if ( $d_karyawan->count() > 0 ) {
			$d_karyawan = $d_karyawan->toArray();
			foreach ($d_karyawan as $k_karyawan => $v_karyawan) {
				$key = $v_karyawan['jabatan'].'|'.$v_karyawan['nama'];
				$data[$key] = $v_karyawan;

				ksort($data);
			}
		}

		$data['karyawan'] = $data;
		$this->load->view('master/user_mobile/add_form', $data);
	}

	public function edit_form()
	{
		$id_user = $this->input->get('id_user');

		$m_karyawan = new \Model\Storage\Karyawan_model();
		$d_karyawan = $m_karyawan->where('status', 1)->orderBy('jabatan', 'asc')->get();

		$data = null;
		if ( $d_karyawan->count() > 0 ) {
			$d_karyawan = $d_karyawan->toArray();
			foreach ($d_karyawan as $k_karyawan => $v_karyawan) {
				$key = $v_karyawan['jabatan'].'|'.$v_karyawan['nama'];
				$data[$key] = $v_karyawan;

				ksort($data);
			}
		}


		$m_um = new \Model\Storage\UserMobile_model();
		$d_um = $m_um->where('id', trim($id_user))->first();

		$data['karyawan'] = $data;
		$data['data_user'] = $d_um;

		$this->load->view('master/user_mobile/edit_form', $data);
	}

	public function save_data()
	{
		$params = $this->input->post('params');

		try {
			$cek_parent = $this->cek_parent($params['username']);
			if ( !$cek_parent ) {
				$m_um = new \Model\Storage\UserMobile_model();
				$now = $m_um->getDate();

				// GET ID FOR SAVE TO ms_user
				$id_user = $m_um->getNextId();

				// INSERT TO TABLE ms_user
				$m_um->id_user = $id_user;
				$m_um->id_karyawan = $params['id_karyawan'];
				$m_um->nik_karyawan = $params['nik_karyawan'];
				$m_um->nama = $params['nama'];
				$m_um->jabatan = $params['jabatan'];
				$m_um->username = $params['username'];
				$m_um->password = $params['password'];
				$m_um->status = 1;
				$m_um->save();

				$deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
        		Modules::run( 'base/event/save', $m_um, $deskripsi_log);

			    $this->result['status'] = 1;
				$this->result['message'] = 'Data berhasil di simpan';
			} else {
				$this->result['message'] = 'Username yang anda masukkan sudah ada.';
			}
		} catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

		display_json($this->result);
	}

	public function edit_data()
	{
		$params = $this->input->post('params');

		$m_um = new \Model\Storage\UserMobile_model();
		$d_um = $m_um->where('id', $params['id_user'])->first();

		try {
			$cek_parent = $this->cek_parent($params['username'], $d_um->id);
			if ( $cek_parent ) {
				$this->result['message'] = 'Username yang anda masukkan sudah ada.';
			} else {
				$m_um = new \Model\Storage\UserMobile_model();
				$now = $m_um->getDate();

				$m_um->where('id', $params['id_user'])->update(
						array(
							'id_karyawan' => $params['id_karyawan'],
							'nik_karyawan' => $params['nik_karyawan'],
							'nama' => $params['nama'],
							'jabatan' => $params['jabatan'],
							'username' => $params['username'],
							'password' => $params['password'],
							'status' => $params['status']
						));

				$d_um = $m_um->where('id', $params['id_user'])->first();

				$deskripsi_log = 'di-edit oleh ' . $this->userdata['detail_user']['nama_detuser'];
		        Modules::run( 'base/event/update', $d_um, $deskripsi_log);

			    $this->result['status'] = 1;
				$this->result['message'] = 'Data berhasil di edit';
			}
		} catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

		display_json($this->result);
	}

	public function delete_data()
	{
		$id_user = $this->input->post('params');

		try {
			$m_um = new \Model\Storage\UserMobile_model();			
			$m_um->where('id', $id_user)->update(
				array(
					'status' => 0
				)
			);

			$d_um = $m_um->where('id', $id_user)->first();

			$deskripsi_log = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
		    Modules::run( 'base/event/update', $d_um, $deskripsi_log);

		    $this->result['status'] = 1;
			$this->result['message'] = 'Data berhasil di hapus';
		} catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

		display_json($this->result);
	}

	public function cek_parent($username='', $id=null)
	{
		$m_um = new \Model\Storage\UserMobile_model();

		$val = false;
		if ( empty($id) ) {
			$d_um = $m_um->where('username', trim($username))->first();
			if ( $d_um ) {
				$val = true;
			}
		} else {
			$d_um = $m_um->whereNotIn('id', [$id])->where('username', trim($username))->first();
			if ( $d_um ) {
				$val = true;
			}
		}

		return $val;
	}
}