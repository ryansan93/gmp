<?php

defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
class Mobile extends API_Controller {
	function __construct() {
		parent::__construct();
	}

	public function index() {
		$m_user = new \Model\Storage\User_model();
		$d_user = $m_user->get()->toArray();

		$this->result['message'] = "API MOBILE INDEX";
		$this->result['content'] = $d_user;
		display_json($this->result);
	}

	public function getUserMobile()
	{
		$listIdUser = $this->input->get('listIdUser');
		try {
			$m_um = new \Model\Storage\UserMobile_model();
			$d_um = $m_um->get();

			$data = null;
			if ( $d_um->count() > 0 ) {
				$d_um = $d_um->toArray();
				foreach ($d_um as $k_um => $v_um) {
					$data[] = array(
						'id_user' => $v_um['id_user'],
						'id_karyawan' => $v_um['id_karyawan'],
						'nik_karyawan' => $v_um['nik_karyawan'],
						'name' => $v_um['nama'],
						'jabatan' => $v_um['jabatan'],
						'username' => $v_um['username'],
						'password' => $v_um['password'],
						'status' => empty($v_um['status']) ? 0 : 1
					);
				}
			}

			$this->result['status'] = 1;
			$this->result['message'] = 'API GET USER MOBILE';
			$this->result['content'] = $data;
		} catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

		display_json($this->result);
	}

	public function getNoreg()
	{
		$listNoreg = $this->input->get('listNoreg');
		try {
			$today = date('Y-m-d');
			// $prev_date = prev_date($today, 60);
			$prev_date = '2021-04-01';

			$m_ts = new \Model\Storage\TutupSiklus_model();
			$d_ts = $m_ts->select('noreg')->where('tgl_docin', '>=', $prev_date)->get();

			$m_rs = new \Model\Storage\RdimSubmit_model();
			$list_data = null;
			if ( $d_ts->count() > 0 ) {
				$d_ts = $d_ts->toArray();
				$d_rs = $m_rs->whereNotIn('noreg', $d_ts)->where('tgl_docin', '>=', $prev_date)->with(['mitra'])->get();
			} else {
				$d_rs = $m_rs->where('tgl_docin', '>=', $prev_date)->with(['mitra'])->get();
			}

			if ( $d_rs->count() > 0 ) {
				$d_rs = $d_rs->toArray();
				foreach ($d_rs as $k_rs => $v_rs) {
					$list_data[ $v_rs['noreg'] ] = array(
						'noreg' => $v_rs['noreg'],
						'mitra' => $v_rs['mitra']['d_mitra']['nama'],
						'tgl_docin' => substr($v_rs['tgl_docin'], 0, 10),
						'pengawas' => $v_rs['pengawas'],
						'sampling' => $v_rs['sampling'],
						'koar' => $v_rs['koar'],
						'tutup_siklus' => 0
					);
				}
			}

			if ( !empty($listNoreg) ) {
				$listNoreg = json_decode($listNoreg);
				foreach ($listNoreg as $k_ln => $v_ln) {
					$m_ts = new \Model\Storage\TutupSiklus_model();
					$d_ts = $m_ts->where('noreg', $v_ln)->first();

					$m_rs = new \Model\Storage\RdimSubmit_model();
					$d_rs = $m_rs->where('noreg', $v_ln)->with(['mitra'])->first();
					if ( !$d_ts ) {
						$list_data[ $d_rs->noreg ] = array(
							'noreg' => $d_rs->noreg,
							'mitra' => $d_rs->mitra->dMitra->nama,							
							'tgl_docin' => substr($d_rs->tgl_docin, 0, 10),
							'pengawas' => $d_rs->pengawas,
							'sampling' => $d_rs->sampling,
							'koar' => $d_rs->koar,
							'tutup_siklus' => 0
						);
					} else {
						$list_data[ $d_rs->noreg ] = array(
							'noreg' => $d_rs->noreg,
							'mitra' => $d_rs->mitra->dMitra->nama,							
							'tgl_docin' => substr($d_rs->tgl_docin, 0, 10),
							'pengawas' => $d_rs->pengawas,
							'sampling' => $d_rs->sampling,
							'koar' => $d_rs->koar,
							'tutup_siklus' => 1
						);
					}
				}
			}

			$data = null;
			if ( !empty($list_data) && count($list_data) > 0 ) {
				ksort($list_data);
				foreach ($list_data as $k_ld => $v_ld) {
					$data[] = $v_ld;
				}
			}

			$this->result['status'] = 1;
			$this->result['message'] = 'API GET NOREG';
			$this->result['content'] = $data;
		} catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

		display_json($this->result);
	}

	public function getNekropsi()
	{
		try {
			$m_nekropsi = new \Model\Storage\Nekropsi_model();
			$d_nekropsi = $m_nekropsi->get();

			$data = null;
			if ( $d_nekropsi->count() > 0 ) {
				$d_nekropsi = $d_nekropsi->toArray();
				foreach ($d_nekropsi as $k_nekropsi => $v_nekropsi) {
					$data[] = array(
						'id' => $v_nekropsi['id'],
						'keterangan' => $v_nekropsi['keterangan']
					);
				}
			}

			$this->result['status'] = 1;
			$this->result['message'] = 'API GET NEKROPSI';
			$this->result['content'] = $data;
		} catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

		display_json($this->result);
	}

	public function getSolusi()
	{
		try {
			$m_solusi = new \Model\Storage\Solusi_model();
			$d_solusi = $m_solusi->get();

			$data = null;
			if ( $d_solusi->count() > 0 ) {
				$d_solusi = $d_solusi->toArray();
				foreach ($d_solusi as $k_solusi => $v_solusi) {
					$data[] = array(
						'id' => $v_solusi['id'],
						'keterangan' => $v_solusi['keterangan']
					);
				}
			}

			$this->result['status'] = 1;
			$this->result['message'] = 'API GET SOLUSI';
			$this->result['content'] = $data;
		} catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

		display_json($this->result);
	}

	// public function login() {
	// 	$username = empty($this->input->get('username')) ? "" : $this->input->get('username');
	// 	$password = empty($this->input->get('password')) ? "" : $this->input->get('password');
	// 	// $deviceId = $this->input->get('deviceId');

	// 	// login by input username and password
	// 	if ($username != "" && $password != "") {
	// 		$m_user = new \Model\Storage\User_model();
	// 		$d_user = $m_user->where('username_user', $username)->first();

	// 		$m_detuser = new \Model\Storage\DetUser_model();
	// 		$d_detuser = $m_detuser->where('id_user', $d_user->id_user)->whereNull('nonaktif_detuser')->first();

	// 		$this->load->helper('phppass');
	// 		$hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);

	// 		if ( !empty($d_user) ) {
	// 			$hash_password = $d_user['pass_user'];
	// 			$success = $hasher->CheckPassword($password, $hash_password);

	// 			if ($success) {
	// 				$this->result['status'] = 1;
	// 				$this->result['message'] = "LOGIN SUKSES";
	// 				$this->result['id_user'] = $d_user->id_user;
	// 			} else {
	// 				$this->result['status'] = 0;
	// 				$this->result['message'] = "CEK KEMBALI USERNAME / PASSWORD ANDA";
	// 			}
	// 		} else {
	// 			$this->result['status'] = 0;
	// 			$this->result['message'] = "USERNAME TIDAK TERDAFTAR";
	// 		}
	// 	} else {
	// 		$this->result['status'] = 0;
	// 		$this->result['message'] = "USERNAME TIDAK TERDAFTAR";
	// 	}

	// 	display_json($this->result);
	// }

	// public function checkDevice($deviceServer, $deviceId) {
	// 	if ($deviceServer == $deviceId) {
	// 		return true;
	// 	}

	// 	return false;
	// }

	// public function get_planning_doc() {
	// 	$date = $this->input->get('date');

	// 	$m_rdimsubmit = new \Model\Storage\RdimSubmit();
	// 	$d_rdimsubmit = $m_rdimsubmit->where('tgl_docin', '>', $date)->with(['order_doc', 'dMitraMapping'])->get()->toArray();

	// 	// cetak_r($d_rdimsubmit);
	// 	$content = $this->mapping_planning($d_rdimsubmit);

	// 	$this->result['message'] = "GET PLANNING DOC";
	// 	$this->result = $content;
	// 	display_json($this->result);
	// }

	// public function get_doc_by_op() {
	// 	$noOp = $this->input->get("noOp");

	// 	$m_rdimsubmit = new \Model\Storage\RdimSubmit();
	// 	$d_rdimsubmit = $m_rdimsubmit->where('op_doc', '=', $noOp)->with(['order_doc', 'dMitraMapping'])->get()->toArray();

	// 	// cetak_r($d_rdimsubmit);
	// 	$content = $this->mapping_planning($d_rdimsubmit);

	// 	$this->result['message'] = "GET DOC BY OP";
	// 	$this->result = $content;
	// 	display_json($this->result[0]);
	// }

	// public function get_doc_by_noreg() {
	// 	$noreg = $this->input->get("noreg");

	// 	$m_rdimsubmit = new \Model\Storage\RdimSubmit();
	// 	$d_rdimsubmit = $m_rdimsubmit->where('noreg', '=', $noreg)->with(['order_doc', 'dMitraMapping'])->get()->toArray();

	// 	// cetak_r($d_rdimsubmit);
	// 	$content = $this->mapping_planning($d_rdimsubmit);

	// 	$this->result['message'] = "GET DOC BY NOREG";
	// 	$this->result = $content;
	// 	display_json($this->result[0]);
	// }

	// public function save_doc() {
	// 	$data = $_GET['doc'];
	// 	$user = $_GET['id_user'];
	// 	$m_doc = json_decode($data, true);

	// 	try {
	// 		$m_realdocin = new \Model\Storage\Real_Docin();
	// 		$m_realdocin->id_user = $user;
	// 		$m_realdocin->tgl_trans = $m_doc['tanggalDoc'];
	// 		$m_realdocin->noreg = $m_doc['noreg'];
	// 		$m_realdocin->tgl_terima = $m_doc['penerimaan'];
	// 		$m_realdocin->no_sj = $m_doc['noSj'];
	// 		$m_realdocin->terima_box = $m_doc['terimaBox'];
	// 		$m_realdocin->terima_ekor = $m_doc['ekorTerima'];
	// 		$m_realdocin->terima_mati = $m_doc['ekorMati'];
	// 		$m_realdocin->terima_bb = $m_doc['bbRata'];
	// 		$m_realdocin->selisih_ekor = ($m_doc['jumlahBox'] * 100) - $m_doc['ekorTerima'];
	// 		$m_realdocin->ket_terima = $m_doc['keterangan'];
	// 		$m_realdocin->save();

	// 		$this->result['status'] = 1;
	// 		$this->result['message'] = "SAVE DOC SUCCESSFULLY";
	// 		$this->result['content'] = $m_doc;
	// 	} catch (\Illuminate\Database\QueryException $e) {
 //            $this->result['message'] = "Gagal : " . $e->getMessage();
 //        }

	// 	display_json($this->result);
	// }

	// public function upload_attachment() {
	// 	$type = $this->input->get("type");

	// 	try {
	// 		$file_path = basename($_FILES['image']['name']);
	// 		if (move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/photo/' . $type . "/" . $file_path)) {
	// 			$this->result['message'] = 'File successfully uploaded';
	// 		} else {
	// 			$this->result['message'] = 'Error uploading video';
	// 		}
	// 	} catch (Exception $exc) {
	// 		$this->result['message'] = $exc->getTraceAsString();
	// 	}

	// 	display_json($this->result);
	// }

	// public function get_voadip_by_noreg() {
	// 	$noreg = $this->input->get("noreg");
	// 	$tgl_docin = $this->input->get("tgldocin");
	// 	$tgl_docin = $tgl_docin ?: date('Y-m-d');

	// 	$m_voadip = new \Model\Storage\OrderVoadip();
	// 	$d_voadip = $m_voadip->where('noreg', '=', $noreg)->with(['detail', 'rdim_submit'])->get()->toArray();

	// 	// cetak_r($d_voadip);

	// 	$this->result = $this->mapping_voadip($d_voadip, $tgl_docin);
	// 	display_json($this->result);
	// }

	// public function get_voadip_by_op() {
	// 	$noop = $this->input->get("noOp");
	// 	$tgl_docin = $this->input->get("tgldocin");
	// 	$tgl_docin = $tgl_docin ?: date('Y-m-d');

	// 	$m_voadip = new \Model\Storage\OrderVoadip();
	// 	$d_voadip = $m_voadip->where('no_order', '=', $noop)->with(['detail', 'rdim_submit'])->get()->toArray();

	// 	// cetak_r($d_voadip);

	// 	$content = $this->mapping_voadip($d_voadip, $tgl_docin);
		
	// 	$this->result = $content;
	// 	display_json($this->result[0]);
	// }

	// private function mapping_planning($rdimsubmit) {
	// 	$contents = array();
	// 	foreach ($rdimsubmit as $data) {
	// 		$content = array(
	// 			"noOpDoc" => $data['op_doc'],
	// 			"tanggalDoc" => $data['tgl_docin'],
	// 			"mitra" => $data['mitra'],
	// 			"noreg" => $data['noreg'],
	// 			"kandang" => $data['kandang'],
	// 			"alamat" => empty($data['d_mitra_mapping']) ? 'KOSONG' : $data['d_mitra_mapping']['d_mitra']['alamat_jalan'],
	// 			"populasi" => $data['populasi'],
	// 			"jumlahBox" => empty($data['order_doc']) ? 200 : $data['order_doc']['jml_box'],
	// 			"nopol" => empty($data['spj_doc']) ? 'L 2549 AB' : $data['spj_doc']['nopol'],
	// 			"sopir" => empty($data['spj_doc']) ? 'DENNIS PRASETIA' : $data['spj_doc']['sopir'],
	// 			"kedatangan" => empty($data['spj_doc']) ? '9 November 2019 23:00' : $data['spj_doc']['tiba_farm'],
	// 			"prokes" => $data['prokes']
	// 		);

	// 		array_push($contents, $content);
	// 	}

	// 	return $contents;
	// }

	// private function mapping_voadip($voadips, $tgl_docin) {
	// 	$contents = array();
	// 	foreach ($voadips as $voadip) {

	// 		if ($voadip['tgl_kirim'] == null) {
	// 			$date = date_create($tgl_docin);
	// 			date_sub($date,date_interval_create_from_date_string("2 days"));
	// 			$resultdate = date_format($date,"Y-m-d");
	// 		} else {
	// 			$resultdate = $voadip['tgl_kirim'];
	// 		}

	// 		$content = array(
	// 			"mitra" => $voadip['rdim_submit']['mitra']['d_mitra']['nama'],
	// 			"alamat" => $voadip['rdim_submit']['mitra']['d_mitra']['alamat_jalan'],
	// 			"noOp" => $voadip['no_order'],
	// 			"noreg" => $voadip['noreg'],
	// 			"supplier" => $voadip['supplier'],
	// 			"tglKirim" => $resultdate,
	// 			"itemVoadip" => $this->mapping_item($voadip['detail'])
	// 		);

	// 		array_push($contents, $content);
	// 	}

	// 	return $contents;
	// }

	// private function mapping_item($itemvoadip) {
	// 	$contents = array();
	// 	foreach ($itemvoadip as $item) {
	// 		$i = explode(" ", $item['nama_barang']);

	// 		$content = array(
	// 			"name" => $item['nama_barang'],
	// 			"packing" => $i[1] . $i[2],
	// 			"ammount" => $item['jumlah']
	// 		);

	// 		array_push($contents, $content);
	// 	}

	// 	return $contents;
	// }

	// public function save_voadip() {
	// 	$data = $_GET['voadip'];
	// 	$user = $_GET['id_user'];
	// 	$m_data = json_decode($data, true);

	// 	$ov = new \Model\Storage\OrderVoadip();
	// 	$dov = $ov->where('no_order', $m_data['noOp'])->first();

	// 	$array_sj = explode('/', $m_data['url']);
	// 	$array_sign = explode('/', $m_data['urlSign']);

	// 	try {
	// 		$m_voadip = new \Model\Storage\TerimaVoadip();
	// 		$m_voadip->id = $m_voadip->getNextIdentity();
	// 		$m_voadip->no_bukti = $m_voadip->getNextNomor("BPB/VOA/");
	// 		$m_voadip->no_order = $m_data['noOp'];
	// 		$m_voadip->no_sj = $m_data['noSj'];
	// 		$m_voadip->lampiran_tt = 'upload/photo/VOADIP/' . $array_sign[count($array_sign) - 1];
	// 		$m_voadip->supplier = $m_data['supplier'];
	// 		$m_voadip->user_submit = "USR1612001";
	// 		$m_voadip->tgl_submit = $m_data['tglTerima'];
	// 		$m_voadip->penerima = $m_data['penerima'];
	// 		$m_voadip->lampiran_sj = 'upload/photo/VOADIP/' . $array_sj[count($array_sj) - 1];
	// 		$m_voadip->user_submit = $user;
	// 		$m_voadip->save();

	// 		$this->result['status'] = 1;
	// 		$this->result['message'] = "SAVE VOADIP";
	// 	} catch (Exception $e) {
	// 		$this->result['message'] = "Gagal : " . $e->getMessage();
	// 	}

	// 	try {
	// 		if (count($m_data['itemVoadip']) > 0) {
	// 			// $v = new \Model\Storage\TerimaVoadip();
	// 			// $d = $v->where('no_order', $m_data['noOp'])->first();

	// 			for ($i = 0; $i < count($m_data['itemVoadip']); $i++) {
	// 				$m_detailvoadip = new \Model\Storage\TerimaVoadipDetail();
	// 				$m_detailvoadip->id = $m_detailvoadip->getNextIdentity();
	// 				$m_detailvoadip->id_terima = $m_voadip->id;
	// 				$m_detailvoadip->nama_barang = $m_data['itemVoadip'][$i]['name'];
	// 				$m_detailvoadip->satuan = $m_data['itemVoadip'][$i]['packing'];
	// 				$m_detailvoadip->jumlah = $m_data['itemVoadip'][$i]['ammount'];
	// 				$m_detailvoadip->keterangan = $m_data['itemVoadip'][$i]['keterangan'];
	// 				$m_detailvoadip->save();
	// 			}
	// 		}

	// 		$this->result['status'] = 1;
	// 		$this->result['message'] = $this->result['message'] . " AND DETAIL SUCCESSFULLY";
	// 	} catch (Exception $e) {
	// 		$this->result['message'] = $this->result['message'] . ", Gagal2 : " . $e->getMessage();
	// 	}

	// 	display_json($this->result);
	// }
}

?>