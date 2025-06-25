<?php

defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
class MobileMitra extends API_Controller {
	function __construct() {
		parent::__construct();
	}

	public function index() {
		$this->result['status'] = 1;
		$this->result['message'] = "API MOBILE MITRA INDEX";
		$this->result['content'] = [];
		display_json($this->result);
	}

	public function get_mitra() {
		$m_mitra = new \Model\Storage\Mitra();
		$d_mitra = $m_mitra->get()->toArray();

		$contents = array();

		if ( !empty($d_mitra)) {
			foreach ($d_mitra as $key => $val) {
				$content = array(
					"nomor" => $val['nomor'],
					"nama" => $val['nama']
				);
				
				array_push($contents, $content);
			}
		}

		$this->result = $contents;
		display_json($this->result);
	}

	public function save_mitra_ktp() {
		$nomor_mitra = $_GET['nomor'];
		$m_ktp = json_decode($_GET['ktp']);

		$ktp = new \Model\Storage\MitraKtp();
		$ktp->nomor_mitra = $nomor_mitra;
		$ktp->provinsi = $m_ktp->provinsi;
		$ktp->kota = $m_ktp->kota_kab;
		$ktp->nik = $m_ktp->nik;
		$ktp->nama = $m_ktp->nama;
		$ktp->alamat = $m_ktp->alamat;
		$ktp->rt_rw = $m_ktp->rt_rw;
		$ktp->kel_desa = $m_ktp->kel_desa;
		$ktp->kecamatan = $m_ktp->kecamatan;
		$ktp->status = $m_ktp->status;
		$ktp->save();

		$this->result['status'] = 1;
		$this->result['message'] = "Data KTP tersimpan";
		$this->result['id_ktp'] = $ktp->id;
		display_json($this->result);
	}

	public function upload_foto_ktp() {
		$type = $this->input->get("type");
		$id_ktp = $this->input->get("id_ktp");

		try {
			$file_path = basename($_FILES['image']['name']);
			if (move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/muserp/' . $type . "/" . $file_path)) {

				$m_ktp = new \Model\Storage\MitraKtp();
				$d_ktp = $m_ktp->where('id', '=', $id_ktp)->first();
				$d_ktp->uri_ktp = '/uploads/muserp/' . $type . "/" . $file_path;
				$d_ktp->save();

				$this->result['message'] = 'File successfully uploaded';
			} else {
				$this->result['message'] = 'Error uploading video';
			}
		} catch (Exception $exc) {
			$this->result['message'] = $exc->getTraceAsString();
		}

		display_json($this->result);
	}
}

?>