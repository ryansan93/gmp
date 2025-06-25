<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends MY_Controller
{
	private $permission;
	private $username;
	private $password;
	private $isLogin = FALSE;
	function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$this->add_external_js(array('assets/login/js/login.js'));
		$this->add_external_css(array('assets/login/css/login.css'));
		
		$data = $this->includes;
		$this->load->view('user/login', $data);
	}

	public function checkLogin()
	{
		$username = $this->input->post('username');
		$password = $this->input->post('password');

		$m_user = new \Model\Storage\User_model();
		$user = $m_user->where('username_user', trim($username))->with(['detail_user'])->get()->first();

		if($username == $user['username_user']) {
			if ( $user['status_user'] == 1 ) {
				$m_group = new \Model\Storage\Group_model();
				$group = $m_group->where('id_group', $user['detail_user']['id_group'])->with(['detail_group'])->get()->first();

				$listUrl = \Model\Storage\DetGroup_model::whereIdGroup($user['detail_user']['id_group'])->with('detail_fitur')->get()->lists('detail_fitur.path_detfitur');

				$akses_khusus = null;
				$fitur = array();
				if ( $group ) {
					$group = $group->toArray();
					foreach ($group['detail_group'] as $key => $v_group) {
						$detail = $v_group['detail_fitur'];
						if ( !empty($detail) ) {
							if ( $v_group['detail_fitur']['fitur']['status'] == 1 ) {
								$fitur[$v_group['detail_fitur']['fitur']['id_fitur']]['header_fitur'] = $v_group['detail_fitur']['fitur']['nama_fitur'];
								$fitur[$v_group['detail_fitur']['fitur']['id_fitur']]['id_header_fitur'] = $v_group['detail_fitur']['fitur']['id_fitur'];
								$fitur[$v_group['detail_fitur']['fitur']['id_fitur']]['urut'] = $v_group['detail_fitur']['fitur']['urut'];
								$fitur[$v_group['detail_fitur']['fitur']['id_fitur']]['detail'][] = $v_group['detail_fitur'];
							}
						}
					}

					$m_ak = new \Model\Storage\AksesKhusus_model();
					$akses_khusus = $m_ak->select('akses_khusus')->where('id_group', $user['detail_user']['id_group'])->get();

					if ( $akses_khusus->count() > 0 ) {
						$akses_khusus = $akses_khusus->toArray();
					}
				}


				// MAPPING HAK AKSES PER FITUR PER GROUP
				foreach ($fitur as $k_fitur => $v_fitur) {
					foreach ($v_fitur['detail'] as $k_dfitur => $v_dfitur) {
						foreach ($group['detail_group'] as $key => $v_group) {
							if ( $v_group['id_detfitur'] == $v_dfitur['id_detfitur'] ) {
								// cetak_r( $user['detail_user']['id_group'].' | '.$v_dfitur['id_detfitur'] );
								$ak = null;
								$_ak = null;
								if ( empty($ak) ) {
									$m_ak = new \Model\Storage\AksesKhusus_model();
									// $ak = $m_ak->where('id_group', $user['detail_user']['id_group'])->where('id_detfitur', $v_dfitur['id_detfitur'])->get();
									$ak = $m_ak->select('akses_khusus')->where('id_group', $user['detail_user']['id_group'])->get();

									if ( $ak->count() > 0 ) {
										$ak = $ak->toArray();
										foreach ($ak as $k_ak => $v_ak) {
											$_ak[] = $v_ak['akses_khusus'];
										}
									} else {
										$ak = null;
									}
								}

								$fitur[$k_fitur]['detail'][$k_dfitur]['akses']['a_view'] = $v_group['a_view'];
								$fitur[$k_fitur]['detail'][$k_dfitur]['akses']['a_submit'] = $v_group['a_submit'];
								$fitur[$k_fitur]['detail'][$k_dfitur]['akses']['a_edit'] = $v_group['a_edit'];
								$fitur[$k_fitur]['detail'][$k_dfitur]['akses']['a_delete'] = $v_group['a_delete'];
								$fitur[$k_fitur]['detail'][$k_dfitur]['akses']['a_ack'] = $v_group['a_ack'];
								$fitur[$k_fitur]['detail'][$k_dfitur]['akses']['a_approve'] = $v_group['a_approve'];
								$fitur[$k_fitur]['detail'][$k_dfitur]['akses']['a_khusus'] = $_ak;
							}
						}
					}
				}

				foreach ($fitur as $key => $val) {
					$sort_fitur = $this->msort($val['detail'], 'nama_detfitur');
					$fitur[$val['id_header_fitur']]['detail'] = $sort_fitur;
				}

				$data_fitur = $this->msort($fitur, 'urut');

				// ENCRYPT PASSWORD
				$this->load->helper('phppass');
				$hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);

				$hash_password = $user['pass_user'];
				$success = $hasher->CheckPassword($password,$hash_password);

				if ( $success ) {
					$dataUser = $user->toArray();
					$dataUser['isLogin'] = 1;
					$dataUser['Fitur'] = $data_fitur;
					$dataUser['AksesKhusus'] = $akses_khusus;

					$this->session->set_userdata($dataUser);
					$this->session->set_userdata('url',json_encode($listUrl));
					
					$this->result['status'] = 1;
				} else {
					/* password tidak sesuai */
					$this->result['message'] = 'Password tidak sesuai';
				}
			} else {
				$this->result['message'] = 'User yang anda masukkan sudah tidak aktif.<br>Hubungi administrator untuk mengaktifkan kembali.<br>Terima Kasih';
			}
		} else {
			/* user tidak ditemukan */
			$this->result['message'] = 'User tidak ditemukan';
		}

		echo display_json($this->result);
	}

	public function logout()
	{
		$this->session->sess_destroy();
		redirect('user/Login');
	}

	public function isLogin()
	{
		return $this->session->userdata('isLogin');
	}

	public function reset($iduser){
		$this->load->helper('phppass');
		$hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
		$password = $iduser;
		$hash_password = $hasher->HashPassword($password);

		$m_usr = new \Model\Storage\User_model();
		$d_usr = $m_usr->where('id_user', $iduser)->with(['detail_user'])->first();

		if( isset($d_usr['id_user']) ){
			$m_usr->where('id_user', $iduser)->update(array('pass_user'=>$hash_password));

			$m_dusr = new \Model\Storage\DetUser_model();
			$m_dusr->where('id_detuser', $d_usr['detail_user']['id_detuser'])->update(array(
				'pass_detuser' => $hash_password
			));

			echo 'reset password berhasil';
			echo 'password baru adalah <strong>'.$password.'</strong>';
		}else{
			echo 'reset password gagal';
		}
	}

	public function msort($array, $key, $sort_flags = SORT_REGULAR) {
        if (is_array($array) && count($array) > 0) {
            if (!empty($key)) {
                $mapping = array();
                foreach ($array as $k => $v) {
                    $sort_key = '';
                    if (!is_array($key)) {
                        $sort_key = $v[$key];
                    } else {
                        // @TODO This should be fixed, now it will be sorted as string
                        foreach ($key as $key_key) {
                            $sort_key .= $v[$key_key];
                        }
                        $sort_flags = SORT_STRING;
                    }
                    $mapping[$k] = $sort_key;
                }
                asort($mapping, $sort_flags);
                $sorted = array();
                foreach ($mapping as $k => $v) {
                    $sorted[] = $array[$k];
                }
                return $sorted;
            }
        }
        return $array;
    }

    public function tes()
    {
    	$password = 'waitress';

		$hash_password = password_hash($password, PASSWORD_BCRYPT);

		cetak_r( $hash_password );

		// $hash_password = $user['pass_user'];
		// $success = $hasher->CheckPassword($password,$hash_password);
    }
}