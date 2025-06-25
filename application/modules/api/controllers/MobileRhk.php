<?php

defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
class MobileRhk extends API_Controller {
	function __construct() {
		parent::__construct();
	}

	public function index() {
		$this->result['message'] = "API MOBILE RHK INDEX";
		$this->result['content'] = [];
		display_json($this->result);
	}

	public function get_mitra() {
		$m_rdimsubmit = new \Model\Storage\RdimSubmit_model();
		$d_rdimsubmit = $m_rdimsubmit->with(['dKandang', 'mitra'])->get()->toArray();

		// cetak_r($d_rdimsubmit);

		$contents = array();

		if ( !empty($d_rdimsubmit) ) {
			foreach ($d_rdimsubmit as $k_val => $val) {
				if ( ! empty($val['mitra']) ) {
					$docin = substr($val['tgl_docin'], 0, 10);
					$today = date('Y-m-d');

					$umur = selisihTanggal($docin, $today);

					$content = array(
						"idRdim" => $val['id'],
						'namaMitra' => $val['mitra']['d_mitra']['nama'],
						'kandang' => $val['d_kandang']['kandang'],
						'noreg' => $val['noreg'],
						'populasi' => $val['populasi'],
						'umur' => $umur
					);

					array_push($contents, $content);
				}
			}
		}

		$this->result = $contents;
		display_json($this->result);
	}

	public function save_rhk() {
		$dataDoc = $_GET['doc'];
		$dataScreen = $_GET['screen'];
		$dataFad = $_GET['fad'];
		$dataNecropsy = $_GET['necropsy'];
		$dataSolution = $_GET['solution'];
		$user = $_GET['id_user'];

		$m_doc = json_decode($dataDoc);
		$m_screen = json_decode($dataScreen);
		$m_fad = json_decode($dataFad);
		$m_necropsy = json_decode($dataNecropsy);
		$m_solution = json_decode($dataSolution);

		$rhk = new \Model\Storage\Rhk();

		$now = $rhk->getDate();

		$rhk->noreg = $m_doc->noreg;
		$rhk->id_rdim = $m_doc->idRdim;
		$rhk->tanggal = date("Y-m-d");
		$rhk->mitra = $m_doc->namaMitra;
		$rhk->kandang = $m_doc->kandang;
		$rhk->populasi = $m_doc->populasi;
		$rhk->umur = $m_doc->umur;
		$rhk->id_user = $user;
		$rhk->tgl_trans = $now['waktu'];
		$rhk->save();

		$this->save_rhk_sekat($m_screen, $m_doc->noreg);
		$this->save_rhk_pk($m_fad, $m_doc->noreg);
		$this->save_rhk_nekropsi($m_necropsy, $m_doc->noreg);
		$this->save_rhk_solusi($m_solution, $m_doc->noreg);

		$this->result['status'] = 1;
		$this->result['message'] = "Data saved successfully";
		display_json($this->result);
	}

	public function save_rhk_sekat($m_screen, $noreg) {
		foreach ($m_screen as $screen) {
			$d_screen = new \Model\Storage\RhkSekat();
			$d_screen->noreg = $noreg;
			$d_screen->urut = $screen->sequence;
			$d_screen->jumlah = $screen->quantity;
			$d_screen->bb_rata2 = $screen->bbavg;
			$d_screen->save();
		}
	}

	public function save_rhk_pk($m_fad, $noreg) {
		$d_fad = new \Model\Storage\RhkPakanKematian();
		$d_fad->noreg = $noreg;
		$d_fad->penerimaan_pakan = $m_fad->receive;
		$d_fad->sisa_pakan = $m_fad->remain;
		$d_fad->angka_kematian = $m_fad->death;
		$d_fad->keterangan = $m_fad->description;
		$d_fad->save();
	}

	public function save_rhk_nekropsi($m_necropsy, $noreg) {
		foreach ($m_necropsy as $necropsy) {
			if ($necropsy->status == true) {
				$d_necropsy = new \Model\Storage\RhkNekropsi();
				$d_necropsy->noreg = $noreg;
				$d_necropsy->param = $necropsy->parameter;
				$d_necropsy->keterangan = $necropsy->keterangan;
				$d_necropsy->save();
			}
		}
	}

	public function save_rhk_solusi($m_solution, $noreg) {
		foreach ($m_solution as $solution) {
			$d_solution = new \Model\Storage\RhkSolusi();
			$d_solution->noreg = $noreg;
			$d_solution->solusi = $solution->nama;
			$d_solution->save();
		}
	}

	public function save_rhk_lampiran() {

	}

	public function upload_attachment() {
		$type = $this->input->get("type");

		try {
			$file_path = basename($_FILES[$type]['name']);
			if (move_uploaded_file($_FILES[$type]['tmp_name'], 'uploads/muserp/' . $type . "/" . $file_path)) {
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