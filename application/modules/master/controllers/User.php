<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User extends Public_Controller
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
				'assets/master/user/js/user.js'
			));
			$this->add_external_css(array(
				'assets/master/user/css/user.css'
			));

			$data = $this->includes;

			$data['title_menu'] = 'Master User';

			$content['akses'] = $akses;
			$data['view'] = $this->load->view('master/user/index', $content, true);

			$this->load->view($this->template, $data);
		} else {
        	showErrorAkses();
		}
	}

	public function profile()
	{
		$this->add_external_js(array(
			'assets/master/user/js/user.js'
		));
		$this->add_external_css(array(
			'assets/master/user/css/user.css'
		));

		$data = $this->includes;

		$akses = hakAkses($this->url);

		$id_user = $this->userid;

		$m_usr = new \Model\Storage\User_model();
		$d_usr = $m_usr->where('id_user', $id_user)->with(['detail_user'])->first();

		$data['title_menu'] = 'Personal Information';

		$content['akses'] = $akses;
		$content['data_user'] = $d_usr;
		$data['view'] = $this->load->view('master/user/profile/edit_profile', $content, true);

		$this->load->view($this->template, $data);
	}

	public function list_user()
	{
		$akses = hakAkses($this->url);

		$m_usr = new \Model\Storage\User_model();
		$d_usr = $m_usr->with(['detail_user'])->get()->toArray();

		$content['akses'] = $akses;
		$content['list'] = $d_usr;
		$html = $this->load->view('master/user/list', $content, true);

		echo $html;
	}

	public function get_karyawan()
	{
		$m_karyawan = new \Model\Storage\Karyawan_model();
		$d_karyawan = $m_karyawan->where('status', 1)->orderBy('nama', 'asc')->get()->toArray();

		return $d_karyawan;
	}

	public function add_form()
	{
		$m_grp = new \Model\Storage\Group_model();
		$d_grp = $m_grp->get()->toArray();

		$data['data_karyawan'] = $this->get_karyawan();
		$data['data_group'] = $d_grp;
		$this->load->view('master/user/add_form', $data);
	}

	public function edit_form()
	{
		$id_user = $this->input->get('id_user');

		$m_usr = new \Model\Storage\User_model();
		$d_usr = $m_usr->where('id_user', trim($id_user))->with(['detail_user'])->first();

		$m_grp = new \Model\Storage\Group_model();
		$d_grp = $m_grp->get()->toArray();

		$data['data_user'] = $d_usr;
		$data['data_karyawan'] = $this->get_karyawan();
		$data['data_group'] = $d_grp;

		$this->load->view('master/user/edit_form', $data);
	}

	public function save_data()
	{
		$params = json_decode($this->input->post('data'),1);
		$files = isset($_FILES['file']) ? $_FILES['file'] : [];

		try {
			$cek_parent = $this->cek_parent($params['username']);
			if ( !$cek_parent ) {
				$file_name = null;
		        $path_name = null;
		        $isMoved = 0;
		        if (!empty($files)) {
		        	// UPLOAD FILE AND GET INFORMATION FILE
		            $moved = uploadFile($files);
		            $isMoved = $moved['status'];

		        	// GET FILE NAME AND PATH NAME
		        	$file_name = $moved['name'];
	        		$path_name = $moved['path'];
		        }


				$m_usr = new \Model\Storage\User_model();
				$now = $m_usr->getDate();

				// GET ID FOR SAVE TO ms_user
				$id_user = $m_usr->getNextId();

				// ENCRYPT PASSWORD
				// $this->load->helper('phppass');
				// $hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
				$password = $id_user;
				// $hash_password = $hasher->HashPassword($password);
				$hash_password = password_hash($password, PASSWORD_BCRYPT);

				// INSERT TO TABLE ms_user
				$m_usr->id_user = $id_user;
				$m_usr->username_user = $params['username'];
				$m_usr->status_user = 1;
				$m_usr->pass_user = $hash_password;
				$m_usr->save();
				// $m_usr->nama_user = $params['nama_user'];
				// $m_usr->jk_user = $params['jenis_kelamin'];
				// $m_usr->email_user = $params['email'];
				// $m_usr->id_group = $params['id_group'];

				$m_dusr = new \Model\Storage\DetUser_model();

				// GET ID FOR SAVE TO detail_user
				$id_detuser = $m_dusr->getNextId();

				// INSERT TO TABLE detail_user
				$m_dusr->id_detuser = $id_detuser;
				$m_dusr->id_user = $id_user;
				$m_dusr->aktif_detuser = $now['tanggal'];
				$m_dusr->jk_detuser = $params['jenis_kelamin'];
				$m_dusr->email_detuser = $params['email'];
				$m_dusr->nama_detuser = $params['nama_user'];
				$m_dusr->username_detuser = $params['username'];
				$m_dusr->pass_detuser = $hash_password;
				$m_dusr->telp_detuser = $params['no_tlp'];
				$m_dusr->id_group = $params['id_group'];
				$m_dusr->avatar_detuser = $path_name;
				$m_dusr->edit_detuser = $now['waktu'];
				$m_dusr->useredit_detuser = $this->userid;
				$m_dusr->save();

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
		$params = json_decode($this->input->post('data'),1);
		$files = isset($_FILES['file']) ? $_FILES['file'] : [];

		try {
			$cek_parent = $this->cek_parent($params['username'], $params['id_user']);
			if ( isset($cek_parent) ) {
				if ( $cek_parent['id_user'] == $params['id_user'] ) {
					$this->exec_edit($params, $files);

				    $this->result['status'] = 1;
					$this->result['message'] = 'Data berhasil di edit';
				} else {
					$this->result['message'] = 'Nama group yang anda masukkan sudah ada.';	
				}
			} else {
				$this->exec_edit($params, $files);

			    $this->result['status'] = 1;
				$this->result['message'] = 'Data berhasil di edit';
			}
		} catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

		display_json($this->result);
	}

	public function exec_edit($params, $files)
	{
		$file_name = null;
        $path_name = null;
        $isMoved = 0;
        if (!empty($files)) {
        	// UPLOAD FILE AND GET INFORMATION FILE
            $moved = uploadFile($files);
            $isMoved = $moved['status'];

            // GET FILE NAME AND PATH NAME
        	$file_name = $moved['name'];
    		$path_name = $moved['path'];
        } else {
        	// IF FILE NULL GET PATH FILE FROM DATABASE
        	// GET FILE NAME AND PATH NAME
        	$m_dusr = new \Model\Storage\DetUser_model();
        	$d_dusr = $m_dusr->where('id_detuser', $params['id_detuser'])->first();

        	$file_name = $d_dusr['avatar_detuser'];
    		$path_name = $d_dusr['avatar_detuser'];
        }

		$m_usr = new \Model\Storage\User_model();
		$now = $m_usr->getDate();

		$m_usr->where('id_user', $params['id_user'])->update(
				array('username_user'=>$params['username'],
					  'status_user'=>$params['status_user'])
			);
					  // 'nama_user'=>$params['nama_user'],
					  // 'jk_user'=>$params['jenis_kelamin'],
					  // 'email_user'=>$params['email'],
					  // 'id_group'=>$params['id_group']

		$m_dusr = new \Model\Storage\DetUser_model();
		$d_dusr = $m_dusr->where('id_detuser', $params['id_detuser'])->first();
		// UPDATE NONAKTIF_DETUSER IN detail_user
		$m_dusr->where('id_detuser', $params['id_detuser'])->update(
				array('nonaktif_detuser'=>$now['tanggal'])
			);

		// GET ID FOR SAVE TO detail_user
		$id_detuser = $m_dusr->getNextId();

		// INSERT TO TABLE detail_user
		$m_dusr->id_detuser = $id_detuser;
		$m_dusr->id_user = $params['id_user'];
		$m_dusr->aktif_detuser = $now['tanggal'];
		$m_dusr->jk_detuser = $params['jenis_kelamin'];
		$m_dusr->email_detuser = $params['email'];
		$m_dusr->nama_detuser = $params['nama_user'];
		$m_dusr->username_detuser = $params['username'];
		$m_dusr->pass_detuser = $d_dusr['pass_detuser'];
		$m_dusr->telp_detuser = $params['no_tlp'];
		$m_dusr->id_group = $params['id_group'];
		$m_dusr->avatar_detuser = $path_name;
		$m_dusr->edit_detuser = $now['waktu'];
		$m_dusr->useredit_detuser = $this->userid;
		$m_dusr->save();
	}

	public function delete_data()
	{
		$id_user = $this->input->post('params');

		try {
			$m_dusr = new \Model\Storage\DetUser_model();			
			$m_dusr->where('id_user', $id_user)->delete();

			$m_usr = new \Model\Storage\User_model();
			$m_usr->where('id_user', $id_user)->delete();

		    $this->result['status'] = 1;
			$this->result['message'] = 'Data berhasil di hapus';
		} catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

		display_json($this->result);
	}

	public function change_password()
	{
		$id_user = $this->input->post('id_user');
		$username = $this->input->post('username');
		$old_password = $this->input->post('old_password');
		$new_password = $this->input->post('new_password');

		$cek_parent = $this->cek_parent($username, $id_user);
		if ( isset($cek_parent) ) {
			if ( $cek_parent['id_user'] == $id_user ) {
				$message = $this->exec_change_password($id_user, $username, $old_password, $new_password);

			    $this->result['status'] = 1;
				$this->result['message'] = $message;
			} else {
				$this->result['message'] = 'Username yang anda masukkan sudah ada.';	
			}
		} else {
			$message = $this->exec_change_password($id_user, $username, $old_password, $new_password);

		    $this->result['status'] = 1;
			$this->result['message'] = $message;
		}

		display_json($this->result);
	}

	public function exec_change_password($id_user, $username, $old_password, $new_password)
	{
		$this->load->helper('phppass');
		$hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
		// $hash_password_new = $hasher->HashPassword($new_password);
		$hash_password_old = password_hash($old_password, PASSWORD_BCRYPT);
		$hash_password_new = password_hash($new_password, PASSWORD_BCRYPT);

		$m_usr = new \Model\Storage\User_model();
		$now = $m_usr->getDate();
		
		$d_usr = $m_usr->where('id_user', $id_user)->with(['detail_user'])->first();
		$success = password_verify(trim($old_password), $hash_password_old);
		// $success = $hasher->CheckPassword(trim($old_password), $d_usr['pass_user']);
		
		$d_dusr = $d_usr['detail_user'];

		if($success){
			// UPDATE USERNAME AND PASSWORD
			$m_usr->where('id_user', $id_user)->update(
					array('username_user'=>$username,
						  'pass_user'=>$hash_password_new)
				);

			$m_dusr = new \Model\Storage\DetUser_model();
			// UPDATE NONAKTIF_DETUSER IN detail_user
			$m_dusr->where('id_detuser', $d_usr['detail_user']['id_detuser'])->update(
					array('nonaktif_detuser'=>$now['tanggal'])
				);

			// GET ID FOR SAVE TO detail_user
			$id_detuser = $m_dusr->getNextId();

			// INSERT TO TABLE detail_user
			$m_dusr->id_detuser = $id_detuser;
			$m_dusr->id_user = $id_user;
			$m_dusr->aktif_detuser = $now['tanggal'];
			$m_dusr->jk_detuser = $d_dusr['jk_detuser'];
			$m_dusr->email_detuser = $d_dusr['email_detuser'];
			$m_dusr->nama_detuser = $d_dusr['nama_detuser'];
			$m_dusr->username_detuser = $username;
			$m_dusr->pass_detuser = $hash_password_new;
			$m_dusr->telp_detuser = $d_dusr['telp_detuser'];
			$m_dusr->id_group = $d_dusr['id_group'];
			$m_dusr->avatar_detuser = $d_dusr['avatar_detuser'];
			$m_dusr->edit_detuser = $now['waktu'];
			$m_dusr->useredit_detuser = $this->userid;
			$m_dusr->save();

			$message = 'Password telah berhasil dirubah.';
			return $message;
		} else {
			$message = 'Password gagal dirubah, password lama mungkin tidak sesuai.';
			return $message;
		}
	}

	public function reset_password()
	{
		$id_user = $this->input->post('id_user');

		try {
			$this->load->helper('phppass');
			$hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
			// $hash_password = $hasher->HashPassword($id_user);
			$hash_password = password_hash($id_user, PASSWORD_BCRYPT);

			$m_usr = new \Model\Storage\User_model();
			$now = $m_usr->getDate();
			// UPDATE PASSWORD ms_user
			$m_usr->where('id_user', $id_user)->update(
					array('pass_user'=>$hash_password)
				);

			$m_dusr = new \Model\Storage\DetUser_model();
			$d_dusr = $m_dusr->where('id_user', $id_user)->orderBy('id_detuser', 'DESC')->first();
			// UPDATE NONAKTIF_DETUSER IN detail_user
			$m_dusr->where('id_detuser', $d_dusr['id_detuser'])->update(
					array('nonaktif_detuser'=>$now['tanggal'])
				);

			// GET ID FOR SAVE TO detail_user
			$id_detuser = $m_dusr->getNextId();

			// INSERT TO TABLE detail_user
			$m_dusr->id_detuser = $id_detuser;
			$m_dusr->id_user = $id_user;
			$m_dusr->aktif_detuser = $now['tanggal'];
			$m_dusr->jk_detuser = $d_dusr['jk_detuser'];
			$m_dusr->email_detuser = $d_dusr['email_detuser'];
			$m_dusr->nama_detuser = $d_dusr['nama_detuser'];
			$m_dusr->username_detuser = $d_dusr['username_detuser'];
			$m_dusr->pass_detuser = $hash_password;
			$m_dusr->telp_detuser = $d_dusr['telp_detuser'];
			$m_dusr->id_group = $d_dusr['id_group'];
			$m_dusr->avatar_detuser = $d_dusr['avatar_detuser'];
			$m_dusr->edit_detuser = $now['waktu'];
			$m_dusr->useredit_detuser = $this->userid;
			$m_dusr->save();

		    $this->result['status'] = 1;
			$this->result['message'] = 'Password berhasil di reset.<br>Password baru anda adalah : <b>'.$id_user.'</b>';
		} catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

		display_json($this->result);
	}

	public function cek_parent($username='', $id_user=null)
	{
		$m_usr = new \Model\Storage\User_model();

		if ( !isset($id_user) ) {
			$d_usr = $m_usr->where('username_user', trim($username))->first();
			$val = false;
			if ( isset($d_usr['id_user']) ) {
				$val = true;
			}
			return $val;
		} else {
			$d_usr = $m_usr->where('username_user', trim($username))->first();
			$data = null;
			if ( isset($d_usr['id_user']) ) {
				$data = array(
					'id_user'=>$d_usr['id_user'],
					'username'=>$d_usr['username']
				);
			}

			return $data;
		}
	}

	public function decryptPass()
	{
		$str = '$2a$08$hlLRIS180Uom1gZ/p2DjSeMS13xlR3CFd6fYMNQ7e4eLRI/vMQvQa';
		for($i=0; $i<12;$i++)
		{
			$str=base64_decode(strrev($str)); //apply base64 first and then reverse the string
			cetak_r( $str );
		}

		// return $str;
	}

	public function tes()
	{
		$this->load->helper('phppass');
		// $hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
		$password = 'supernimda';
		// $hash = $hasher->HashPassword($password);
		$hash = password_hash($password, PASSWORD_BCRYPT);

		cetak_r( $hash );
	}
}