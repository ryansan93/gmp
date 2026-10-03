<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Ekspedisi extends Public_Controller {

	private $pathView = 'parameter/ekspedisi/';
	private $url;

	function __construct()
	{
		parent::__construct();
		$this->url = $this->current_base_uri;
	}

	/**************************************************************************************
	 * PUBLIC FUNCTIONS
	 **************************************************************************************/
	/**
	 * Default
	 */
	public function index() {
		$akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
			$this->add_external_js(array(
				'assets/jquery/easy-autocomplete/jquery.easy-autocomplete.min.js',
				'assets/jquery/maskedinput/jquery.maskedinput.min.js',
				'assets/parameter/ekspedisi/js/ekspedisi.js'
			));

			$this->add_external_css(array(
				'assets/jquery/easy-autocomplete/easy-autocomplete.min.css',
				'assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css',
				'assets/parameter/ekspedisi/css/ekspedisi.css',
			));

			$data = $this->includes;

			$content['title_panel'] = 'Master Data Ekspedisi';
			$content['akses'] = $akses;
			// $content['list_provinsi'] = $this->getLokasi('PV');
			// $content['list_lampiran_ekspedisi'] = $this->getNamaLampiran("EKSPEDISI", "KTP Ekspedisi")->first();
			// $content['list_lampiran_usaha_ekspedisi'] = $this->getNamaLampiran("EKSPEDISI", "NPWP Ekspedisi")->first();
			// $content['list_lampiran_rekening_ekspedisi'] = $this->getNamaLampiran("BANK_EKSPEDISI", "Rekening Ekspedisi")->first();
			// $content['list_lampiran_dds_ekspedisi'] = $this->getNamaLampiran("EKSPEDISI", "DDP Ekspedisi")->first();
			// $content['ekspedisi_pph23'] = $this->getEkspedisiPph23();

			$content['add_form'] = $this->addForm();

			// load list ekspedisi
			// $detail_content['ekspedisis'] = $this->getListEkspedisi();
			$data['title_menu'] = 'Master Ekspedisi';
			$data['view'] = $this->load->view($this->pathView . 'index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function loadForm()
    {
        $id = $this->input->get('id');
        $resubmit = $this->input->get('resubmit');
        $html = '';

        if ( !empty($id) ) {
            if ( !empty($resubmit) ) {
                /* NOTE : untuk edit */
                $html = $this->editForm($id, $resubmit);
            } else {
                /* NOTE : untuk view */
                $html = $this->viewForm($id, $resubmit);
            }
        } else {
            /* NOTE : untuk add */
            $html = $this->addForm();
        }

        echo $html;
    }

    public function list_ekspedisi()
    {
        $akses = hakAkses($this->url);

        $data = $this->getListEkspedisi();

        $content['akses'] = $akses;
        $content['data'] = $data;

        $html = $this->load->view($this->pathView . 'list', $content);
        
        echo $html;
    }

    public function getEkspedisiPph23()
    {
    	$m_ekspph23 = new \Model\Storage\EkspedisiPph23_model();
    	$d_ekspph23 = $m_ekspph23->get();

    	$data = null;
    	if ( $d_ekspph23->count() > 0 ) {
    		$data = $d_ekspph23->toArray();
    	}

    	return $data;
    }

    public function addForm()
    {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['list_provinsi'] = $this->getLokasi('PV');
		$content['list_lampiran_ekspedisi'] = $this->getNamaLampiran("EKSPEDISI", "KTP Ekspedisi")->first();
		$content['list_lampiran_usaha_ekspedisi'] = $this->getNamaLampiran("EKSPEDISI", "NPWP Ekspedisi")->first();
		$content['list_lampiran_rekening_ekspedisi'] = $this->getNamaLampiran("BANK_EKSPEDISI", "Rekening Ekspedisi")->first();
		$content['list_lampiran_dds_ekspedisi'] = $this->getNamaLampiran("EKSPEDISI", "DDP Ekspedisi")->first();
		$content['ekspedisi_pph23'] = $this->getEkspedisiPph23();
		$m_bu = new \Model\Storage\BadanUsaha_model();
        $d_bu = $m_bu->getData();
        $content['badan_usaha'] = !empty($d_bu) ? $d_bu : null;
        // $content['data'] = null;
        $html = $this->load->view($this->pathView . 'addForm', $content, true);
        
        return $html;
    }

    public function editForm($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        // mengambil data ekspedisi
		$m_ekspedisi = new \Model\Storage\Ekspedisi_model();
		$d_ekspedisi = $m_ekspedisi->where('id', $id)->with(['telepons', 'banks', 'potongan_pph'])->with('logs')->first();
		
		// mengambil lokasi
		$lokasi = new \Model\Storage\Lokasi_model();
		$kec = $lokasi->where('id', $d_ekspedisi['alamat_kecamatan'])->first();
		$kota = $lokasi->where('id', $kec['induk'])->first();
		$prov = $lokasi->where('id', $kota['induk'])->first();
		$kec_usaha = $lokasi->where('id', $d_ekspedisi['usaha_kecamatan'])->first();
		$kota_usaha = $lokasi->where('id', $kec_usaha['induk'])->first();
		$prov_usaha = $lokasi->where('id', $kota_usaha['induk'])->first();

		// simpan data lokasi
		$detail_lokasi = array(
			'prov_id' => $prov['id'],
			'prov' => $prov['nama'],
			'kota_id' => $kota['id'],
			'kota_jenis' => $kota['jenis'],
			'kota' => $kota['nama'],
			'kec_id' => $kec['id'],
			'kec' => $kec['nama'],
			'prov_usaha_id' => $prov_usaha['id'],
			'prov_usaha' => $prov_usaha['nama'],
			'kota_usaha_id' => $kota_usaha['id'],
			'kota_usaha' => $kota_usaha['nama'],
			'kota_usaha_jenis' => $kota_usaha['jenis'],
			'kec_usaha_id' => $kec_usaha['id'],
			'kec_usaha' => $kec_usaha['nama']
		);

		// mengambil lampiran ekspedisi
		$m_nama_lampiran = new \Model\Storage\NamaLampiran_model;
		$d_nama_lampiran = $m_nama_lampiran->where('jenis', 'BANK_EKSPEDISI')->first();

		$m_lampiran = new \Model\Storage\Lampiran_model;
		$d_lampiran = $m_lampiran->where('tabel', 'bank_ekspedisi')->where('nama_lampiran', $d_nama_lampiran['id'])->get()->toArray();

		$lampiran_ktp = $this->getLampiranEkspedisi($d_ekspedisi['id'], 'KTP Ekspedisi');
		$lampiran_npwp = $this->getLampiranEkspedisi($d_ekspedisi['id'], 'NPWP Ekspedisi');
		$lampiran_dds = $this->getLampiranEkspedisi($d_ekspedisi['id'], 'DDP Ekspedisi');

		$content['data'] = $d_ekspedisi;
		$content['lokasi'] = $detail_lokasi;
		$content['l_ktp'] = $lampiran_ktp;
		$content['l_npwp'] = $lampiran_npwp;
		$content['l_dds'] = $lampiran_dds;
        $content['akses'] = $akses;

        $content['list_provinsi'] = $this->getLokasi('PV');
		$content['list_lampiran_ekspedisi'] = $this->getNamaLampiran("EKSPEDISI", "KTP Ekspedisi")->first();
		$content['list_lampiran_usaha_ekspedisi'] = $this->getNamaLampiran("EKSPEDISI", "NPWP Ekspedisi")->first();
		$content['list_lampiran_rekening_ekspedisi'] = $this->getNamaLampiran("BANK_EKSPEDISI", "Rekening Ekspedisi")->first();
		$content['list_lampiran_dds_ekspedisi'] = $this->getNamaLampiran("EKSPEDISI", "DDP Ekspedisi")->first();
		$content['ekspedisi_pph23'] = $this->getEkspedisiPph23();
		$m_bu = new \Model\Storage\BadanUsaha_model();
        $d_bu = $m_bu->getData();
        $content['badan_usaha'] = !empty($d_bu) ? $d_bu : null;

        $html = $this->load->view($this->pathView . 'editForm', $content, true);
        
        return $html;
    }

    public function viewForm($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        // mengambil data ekspedisi
		$m_ekspedisi = new \Model\Storage\Ekspedisi_model();
		$d_ekspedisi = $m_ekspedisi->where('id', $id)->with(['telepons', 'banks', 'potongan_pph', 'd_badan_usaha'])->with('logs')->first();

		// mengambil lokasi
		$lokasi = new \Model\Storage\Lokasi_model();
		$kec = $lokasi->where('id', $d_ekspedisi['alamat_kecamatan'])->first();
		$kota = $lokasi->where('id', $kec['induk'])->first();
		$prov = $lokasi->where('id', $kota['induk'])->first();
		$kec_usaha = $lokasi->where('id', $d_ekspedisi['usaha_kecamatan'])->first();
		$kota_usaha = $lokasi->where('id', $kec_usaha['induk'])->first();
		$prov_usaha = $lokasi->where('id', $kota_usaha['induk'])->first();

		// simpan data lokasi
		$detail_lokasi = array(
			'prov_id' => $prov['id'],
			'prov' => $prov['nama'],
			'kota_id' => $kota['id'],
			'kota' => $kota['nama'],
			'kec_id' => $kec['id'],
			'kec' => $kec['nama'],
			'prov_usaha_id' => $prov_usaha['id'],
			'prov_usaha' => $prov_usaha['nama'],
			'kota_usaha_id' => $kota_usaha['id'],
			'kota_usaha' => $kota_usaha['nama'],
			'kec_usaha_id' => $kec_usaha['id'],
			'kec_usaha' => $kec_usaha['nama']
		);

		// mengambil lampiran ekspedisi
		$m_nama_lampiran = new \Model\Storage\NamaLampiran_model;
		$d_nama_lampiran = $m_nama_lampiran->where('jenis', 'BANK_EKSPEDISI')->first();

		$m_lampiran = new \Model\Storage\Lampiran_model;
		$d_lampiran = $m_lampiran->where('tabel', 'bank_ekspedisi')->where('nama_lampiran', $d_nama_lampiran['id'])->get();

		$lampiran_ktp = $this->getLampiranEkspedisi($d_ekspedisi['id'], 'KTP Ekspedisi');
		$lampiran_npwp = $this->getLampiranEkspedisi($d_ekspedisi['id'], 'NPWP Ekspedisi');
		$lampiran_dds = $this->getLampiranEkspedisi($d_ekspedisi['id'], 'DDP Ekspedisi');

		$content['data'] = $d_ekspedisi;
		$content['lokasi'] = $detail_lokasi;
		$content['l_ktp'] = $lampiran_ktp;
		$content['l_npwp'] = $lampiran_npwp;
		$content['l_dds'] = $lampiran_dds;
		$content['tbl_logs'] = $this->getLogs($d_ekspedisi->nomor);
        $content['akses'] = $akses;

        $html = $this->load->view($this->pathView . 'viewForm', $content, true);
        
        return $html;
    }

    public function getLogs($nomor = null) {
	    $m_ekspedisi = new \Model\Storage\Ekspedisi_model;
    	$d_ekspedisi = $m_ekspedisi->where('nomor', $nomor)->orderBy('version', 'asc')->get()->toArray();

    	$logs = array();
    	foreach ($d_ekspedisi as $key => $v_ekspedisi) {
	    	$m_log = new \Model\Storage\LogTables_model;
	    	$d_log = $m_log->where('tbl_name', 'ekspedisi')->where('tbl_id', $v_ekspedisi['id'])->get()->toArray();

	    	if ( !empty($d_log) ) {
	    		foreach ($d_log as $key => $v_log) {
    				$logs[] = $v_log;
	    		}
	    	}
    	}

    	return $logs;
    }

	public function getNamaLampiran($jenis = null, $nama = null) {
		$m_lampiran = new \Model\Storage\NamaLampiran_model();
		$d_lampiran = $m_lampiran->where('jenis', $jenis)->where('nama', $nama)->get();
		return $d_lampiran;
    }

    public function getLampiranEkspedisi($id, $nama) {
    	$m_lampiran = new \Model\Storage\Lampiran_model();
    	$d_lampiran = $m_lampiran->where('tabel_id', $id)->with(['d_nama_lampiran'])->get()->toArray();

    	$data = null;
    	foreach ($d_lampiran as $key => $v_lampiran) {
    		$nama_lampiran = $v_lampiran['d_nama_lampiran']['nama'];
    		if ( $nama_lampiran == $nama ) {
    			$data = $v_lampiran;
    		}
    	}

    	return $data;
    }

	public function getLokasi($jenis, $induk = null) {
		$m_lokasi = new \Model\Storage\Lokasi_model();
		if ($induk == null) {
			$d_lokasi = $m_lokasi ->where('jenis', $jenis)->orderBy('nama', 'ASC')->get();
		}else{
			$d_lokasi = $m_lokasi ->where('jenis', $jenis)->where('induk', $induk)->orderBy('nama', 'ASC')->get();
		}
		return $d_lokasi;
    }

	public function getLokasiJson() {
		$jenis = $this->input->get('jenis');
		$induk = $this->input->get('induk');

		$result = $this->getLokasi($jenis, $induk);
		$this->result['content'] = $result;
		$this->result['status'] = 1;
		display_json($this->result);
	}

	public function save() {
		$params = $this->input->post('params');

		try {
			$status = "submit";

			// ekspedisi
			$m_ekspedisi = new \Model\Storage\Ekspedisi_model();
			$ekspedisi_id = $m_ekspedisi->getNextIdentity();

			$m_ekspedisi->id = $ekspedisi_id;		
			$m_ekspedisi->jenis = $params['jenis_ekspedisi'];
			$kode_jenis = ($params['jenis_ekspedisi'] == "internal") ? "A" : "B";
			$m_ekspedisi->nomor = $m_ekspedisi->getNextNomor($kode_jenis);
			$m_ekspedisi->nama = $params['nama'];
			$m_ekspedisi->nik = $params['ktp'];
			$m_ekspedisi->cp = $params['cp'];
			$m_ekspedisi->npwp = $params['npwp'];
			$m_ekspedisi->nama_coretax = isset($params['nama_coretax']) ? $params['nama_coretax'] : null;
		$m_ekspedisi->badan_usaha = !empty($params['badan_usaha']) ? $params['badan_usaha'] : null;
			$m_ekspedisi->skb = isset($params['skb']) ? $params['skb'] : null;
			$m_ekspedisi->tgl_habis_skb = isset($params['tgl_habis_skb']) ? $params['tgl_habis_skb'] : null;
			$m_ekspedisi->alamat_kecamatan = $params['alamat_ekspedisi']['kecamatan'];
			$m_ekspedisi->alamat_kelurahan = $params['alamat_ekspedisi']['kelurahan'];
			$m_ekspedisi->alamat_rt = $params['alamat_ekspedisi']['rt'] ?: null;
			$m_ekspedisi->alamat_rw = $params['alamat_ekspedisi']['rw'] ?: null;
			$m_ekspedisi->alamat_jalan = $params['alamat_ekspedisi']['alamat'] ?: null;
			$m_ekspedisi->usaha_kecamatan = $params['alamat_usaha']['kecamatan'];
			$m_ekspedisi->usaha_kelurahan = $params['alamat_usaha']['kelurahan'];
			$m_ekspedisi->usaha_rt = $params['alamat_usaha']['rt'] ?: null;
			$m_ekspedisi->usaha_rw = $params['alamat_usaha']['rw'] ?: null;
			$m_ekspedisi->usaha_jalan = $params['alamat_usaha']['alamat'] ?: null;
			$m_ekspedisi->status = $status;
			$m_ekspedisi->mstatus = 1;
			$m_ekspedisi->platform = $params['platform'];
			$m_ekspedisi->version = 1;
			$m_ekspedisi->potongan_pph_id = $params['potongan_pph'];
			$m_ekspedisi->save();

			$deskripsi_log = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
			Modules::run( 'base/event/save', $m_ekspedisi, $deskripsi_log );

			// telepon ekspedisi
			$telepons = $params['telepons'];
			foreach ($telepons as $k => $telepon) {
				$m_telp = new \Model\Storage\TelpEkspedisi_model();
				$m_telp->id = $m_telp->getNextIdentity();
				$m_telp->ekspedisi_id = $ekspedisi_id;
				$m_telp->nomor = $telepon;
				$m_telp->save();
				Modules::run( 'base/event/save', $m_telp, $deskripsi_log );
			}

			// rekening dan bank ekspedisi
			$banks = $params['banks'];
			foreach ($banks as $k => $bank) {
				$m_bank = new \Model\Storage\BankEkspedisi_model();
				$bank_ekspedisi_id = $m_bank->getNextIdentity();

				$m_bank->id = $bank_ekspedisi_id;
				$m_bank->ekspedisi_id = $ekspedisi_id;
				$m_bank->bank = $bank['nama_bank'];
				$m_bank->rekening_nomor = $bank['nomer_rekening'];
				$m_bank->rekening_pemilik = $bank['nama_pemilik'];
				$m_bank->rekening_cabang_bank = $bank['cabang_bank'];
				$m_bank->save();
				Modules::run( 'base/event/save', $m_telp, $deskripsi_log );
			}

			$this->result['status'] = 1;
			$this->result['content'] = array('id' => $ekspedisi_id);
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

    	display_json($this->result);
	}

	public function edit() {
		$params = $this->input->post('params');

		$ekspedisi_id_old = $params['id'];
		$status = $params['status'];
		$mstatus = $params['mstatus'];
		$version = $params['version'] + 1;

		// ekspedisi
		$m_ekspedisi = new \Model\Storage\Ekspedisi_model();
		$ekspedisi_id = $m_ekspedisi->getNextIdentity();

		$m_ekspedisi->id = $ekspedisi_id;
		$m_ekspedisi->jenis = $params['jenis_ekspedisi'];
		$m_ekspedisi->nomor = $params['nomor'];
		$m_ekspedisi->nama = $params['nama'];
		$m_ekspedisi->nik = $params['ktp'];
		$m_ekspedisi->cp = $params['cp'];
		$m_ekspedisi->npwp = $params['npwp'];
		$m_ekspedisi->nama_coretax = isset($params['nama_coretax']) ? $params['nama_coretax'] : null;
		$m_ekspedisi->badan_usaha = !empty($params['badan_usaha']) ? $params['badan_usaha'] : null;
		$m_ekspedisi->skb = $params['skb'];
		$m_ekspedisi->tgl_habis_skb = $params['tgl_habis_skb'];
		$m_ekspedisi->alamat_kecamatan = $params['alamat_ekspedisi']['kecamatan'];
		$m_ekspedisi->alamat_kelurahan = $params['alamat_ekspedisi']['kelurahan'];
		$m_ekspedisi->alamat_rt = $params['alamat_ekspedisi']['rt'] ?: null;
		$m_ekspedisi->alamat_rw = $params['alamat_ekspedisi']['rw'] ?: null;
		$m_ekspedisi->alamat_jalan = $params['alamat_ekspedisi']['alamat'] ?: null;
		$m_ekspedisi->usaha_kecamatan = $params['alamat_usaha']['kecamatan'];
		$m_ekspedisi->usaha_kelurahan = $params['alamat_usaha']['kelurahan'];
		$m_ekspedisi->usaha_rt = $params['alamat_usaha']['rt'] ?: null;
		$m_ekspedisi->usaha_rw = $params['alamat_usaha']['rw'] ?: null;
		$m_ekspedisi->usaha_jalan = $params['alamat_usaha']['alamat'] ?: null;
		$m_ekspedisi->status = $status;
		$m_ekspedisi->mstatus = $mstatus;
		$m_ekspedisi->platform = $params['platform'];
		$m_ekspedisi->version = $version;
		$m_ekspedisi->potongan_pph_id = $params['potongan_pph'];
		$m_ekspedisi->save();

		$deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
		Modules::run( 'base/event/update', $m_ekspedisi, $deskripsi_log );

		// telepon ekspedisi
		$telepons = $params['telepons'];
		foreach ($telepons as $k => $telepon) {
			$m_telp = new \Model\Storage\TelpEkspedisi_model();
			$m_telp->id = $m_telp->getNextIdentity();

			$m_telp->ekspedisi_id = $ekspedisi_id;
			$m_telp->nomor = $telepon;
			$m_telp->save();
			Modules::run( 'base/event/update', $m_telp, $deskripsi_log );
    	}

    	// rekening dan bank ekspedisi
    	$banks = $params['banks'];
    	foreach ($banks as $k => $bank) {
    		$m_bank = new \Model\Storage\BankEkspedisi_model();
    		$bank_ekspedisi_id = $m_bank->getNextIdentity();

    		$m_bank->id = $bank_ekspedisi_id;
    		$m_bank->ekspedisi_id = $ekspedisi_id;
    		$m_bank->bank = $bank['nama_bank'];
    		$m_bank->rekening_nomor = $bank['nomer_rekening'];
    		$m_bank->rekening_pemilik = $bank['nama_pemilik'];
    		$m_bank->rekening_cabang_bank = $bank['cabang_bank'];
    		$m_bank->save();
    		Modules::run( 'base/event/update', $m_telp, $deskripsi_log );
    	}

    	$this->result['status'] = 1;
      	$this->result['message'] = 'Data ekspedisi sukses di edit';
      	$this->result['content'] = array('id' => $ekspedisi_id);

    	display_json($this->result);
	}

	public function uploadFile() {
		$params = json_decode($this->input->post('data'),TRUE);
		$files = isset($_FILES['files']) ? $_FILES['files'] : [];

		try {
			$id = $params['id'];
			$idx_upload = $params['idx_upload'];
			if ( isset($params['lampirans'][ $idx_upload ]) ) {
				$lampiran = $params['lampirans'][ $idx_upload ];
				$id_lampiran_old = isset($lampiran['old']) ? $lampiran['old'] : null;

				$table = 'ekspedisi';
				$table_id = $id;
				if ( stristr($lampiran['key'], 'bank') !== FALSE ) {
					$table = 'bank_ekspedisi';

					$split_key = explode('_', $lampiran['key']);
					$bank = $split_key[1];
					$rekening_nomor = $split_key[2];

					$m_bank = new \Model\Storage\BankEkspedisi_model();
					$d_bank = $m_bank->where('ekspedisi_id', $id)->where('bank', $bank)->where('rekening_nomor', $rekening_nomor)->first();

					$table_id = $d_bank->id;
				}

				$file_name = $path_name = null;
				$isMoved = 0;
				if (!empty($files)) {
					$mappingFiles = mappingFiles($files);
					$file = $mappingFiles[ $lampiran['sha1'] . '_' . $lampiran['name'] ] ?: '';

					if ( !empty($file) ) {
						$moved = uploadFile($file);
						$isMoved = $moved['status'];

						if ($isMoved) {
							$file_name = $moved['name'];
							$path_name = $moved['path'];

							$m_lampiran = new \Model\Storage\Lampiran_model();
							$m_lampiran->tabel = $table;
							$m_lampiran->tabel_id = $table_id;
							$m_lampiran->nama_lampiran = isset($lampiran['id']) ? $lampiran['id'] : null;
							$m_lampiran->filename = $file_name;
							$m_lampiran->path = $path_name;
							$m_lampiran->status = 1;
							$m_lampiran->save();

							$deskripsi_log = 'di-upload oleh ' . $this->userdata['detail_user']['nama_detuser'];
							Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log );
						} else {
							display_json(['status'=>0, 'message'=>'error, segera hubungi tim IT', 'cek' => 2]);
						}
					} else {
						$m_lampiran = new \Model\Storage\Lampiran_model();
						$d_lampiran_old = $m_lampiran->where('id', $id_lampiran_old)->first();

						if ( $d_lampiran_old ) {
							$m_lampiran = new \Model\Storage\Lampiran_model();
							$m_lampiran->tabel = $d_lampiran_old['tabel'];
							$m_lampiran->tabel_id = $table_id;
							$m_lampiran->nama_lampiran = $d_lampiran_old['nama_lampiran'];
							$m_lampiran->filename = $d_lampiran_old['filename'];
							$m_lampiran->path = $d_lampiran_old['path'];
							$m_lampiran->status = $d_lampiran_old['status'];
							$m_lampiran->save();
						}
					}
				} else {
					$m_lampiran = new \Model\Storage\Lampiran_model();
					$d_lampiran_old = $m_lampiran->where('id', $id_lampiran_old)->first();

					if ( $d_lampiran_old ) {
						$m_lampiran = new \Model\Storage\Lampiran_model();
						$m_lampiran->tabel = $d_lampiran_old['tabel'];
						$m_lampiran->tabel_id = $table_id;
						$m_lampiran->nama_lampiran = $d_lampiran_old['nama_lampiran'];
						$m_lampiran->filename = $d_lampiran_old['filename'];
						$m_lampiran->path = $d_lampiran_old['path'];
						$m_lampiran->status = $d_lampiran_old['status'];
						$m_lampiran->save();
					}
				}
			}

			$this->result['status'] = 1;
			$this->result['message'] = 'Data ekspedisi sukses disimpan';
			$this->result['content'] = array('id' => $id);
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

		display_json( $this->result );
	}

	public function ack() {
		$id = $this->input->post('params');

		$status = getStatus(2);

		$m_ekspedisi = new \Model\Storage\Ekspedisi_model();
		$m_ekspedisi->where('id', $id)->update(
			array(
				'status' => $status
			)
		);

		$d_ekspedisi = $m_ekspedisi->where('id', $id)->first();

		$deskripsi_log = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
		Modules::run( 'base/event/save', $d_ekspedisi, $deskripsi_log );

    	$this->result['status'] = 1;
      	$this->result['message'] = 'Data ekspedisi sukses di ACK';
      	$this->result['content'] = array('id' => $id);

    	display_json($this->result);
	}

	public function approve() {}

	public function reject() {}

	public function nonAktif() {
		$params = json_decode($this->input->post('data'),TRUE);
		$files = isset($_FILES['files']) ? $_FILES['files'] : [];

		if ( !empty($files) ) {
			$mappingFiles = mappingFiles($files);
		}

		$m_ekspedisi = new \Model\Storage\Ekspedisi_model();
		if ( $params['tipe'] == 'aktif' ) {
			$m_ekspedisi->where('nomor', trim( $params['nomor'] ) )
									   ->update(
									   		array(
									   			'mstatus' => 1
									   		)
									   	);
		} else {
			$m_ekspedisi->where('nomor', trim( $params['nomor'] ) )
									   ->update(
									   		array(
									   			'mstatus' => 0
									   		)
									   	);
		}

		$d_ekspedisi = $m_ekspedisi->where('nomor', trim( $params['nomor'] ) )->first();

		$deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
		Modules::run( 'base/event/update', $d_ekspedisi, $deskripsi_log );

		$lampirans = $params['lampiran'];
		if ( !empty($lampirans) ) {
			foreach ($lampirans as $l) {
				$file = $mappingFiles[ $lampiran['sha1'] . '_' . $lampiran['name'] ] ?: '';
	    		$file_name = $path_name = null;
	    		$isMoved = 0;
	    		if (!empty($file)) {
	    			$moved = uploadFile($file);
	    			$isMoved = $moved['status'];
	    		}
	    		if ($isMoved) {
	    			$file_name = $moved['name'];
	    			$path_name = $moved['path'];

	    			$m_lampiran = new \Model\Storage\Lampiran_model();
	    			$m_lampiran->tabel = 'ekspedisi';
	    			$m_lampiran->tabel_id = $d_ekspedisi['nomor'];
	    			$m_lampiran->nama_lampiran = $l['id'];
	    			$m_lampiran->filename = $file_name ;
	    			$m_lampiran->path = $path_name;
	    			$m_lampiran->save();
	    			Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log );

	    		}else {
	    			display_json(['status'=>0, 'message'=>'error, segera hubungi tim IT']);
	    		}
			}
		}

		$this->result['status'] = 1;
      	$this->result['message'] = 'Data Ekspedisi sukses di perbaharui';
      	display_json($this->result);
	}

	public function loadFormStatus() {
		$nomor = $this->input->get('params');

		$akses = hakAkses($this->url);

		$content['akses'] = $akses;
		$content['data_detail'] = $this->getDataForStatus($nomor);
		$html = $this->load->view($this->pathView . 'form_status_supplier', $content, true);

		echo $html;
	}

	public function getDataForStatus($nomor) {
		$m_ekspedisi = new \Model\Storage\Ekspedisi_model();
		$d_ekspedisi = $m_ekspedisi->where('nomor', $nomor)->first();

		return $d_ekspedisi;
	}

	public function loadFormSldAwal() {
		$nomor = $this->input->get('params');

		$akses = hakAkses($this->url);

		$content['akses'] = $akses;
		$content['data_detail'] = null;
		$html = $this->load->view($this->pathView . 'form_saldo_awal', $content, true);

		echo $html;
	}

	public function getListEkspedisi() {
		$datas = array();

		$m_ekspedisi = new \Model\Storage\Ekspedisi_model();
		$sql = "
			select 
				eks.id,
				eks.nomor,
				eks.jenis,
				eks.nama,
				eks.nik,
				eks.mstatus,
				eks.status,
				kk.nama as kab_kota,
				lt.deskripsi,
				lt.waktu
			from ekspedisi eks 
			right join 
				(select max(id) as id, nomor from ekspedisi group by nomor) as e 
				on
					eks.id = e.id
			right join 
				lokasi l 
				on l.id = eks.alamat_kecamatan 
			right join 
				lokasi kk 
				on kk.id = l.induk 
			left join 
				( 
					select lt1.* from log_tables lt1 
					right join 
						(select max(id) as id from log_tables where tbl_name = 'ekspedisi' group by tbl_name, tbl_id) lt2 
						on lt1.id = lt2.id
				) lt 
				on lt.tbl_id = eks.id 
			where
				eks.mstatus = 1 
			group by
				eks.id,
				eks.nomor,
				eks.jenis,
				eks.nama,
				eks.nik,
				eks.mstatus,
				eks.status,
				kk.nama,
				lt.deskripsi,
				lt.waktu
			order by eks.nama asc
		";
		$d_ekspedisi = $m_ekspedisi->hydrateRaw( $sql );
		if ( $d_ekspedisi->count() > 0 ) {
			$d_ekspedisi = $d_ekspedisi->toArray();

			foreach ($d_ekspedisi as $k_eks => $v_eks) {
				$keterangan = !empty($v_eks['deskripsi'] && $v_eks['waktu']) ? $v_eks['deskripsi'] . ' pada ' . dateTimeFormat($v_eks['waktu']) : '';

				$datas[] = array(
					'id' => $v_eks['id'],
					'nip' => $v_eks['nomor'],
					'nama' => $v_eks['nama'],
					'nik' => $v_eks['nik'],
					'alamat' => $v_eks['kab_kota'],
					'mstatus' => $v_eks['mstatus'],
					'status' => $v_eks['status'],
					'saldo_awal' => 0,
					'keterangan' => $keterangan,
					'jenis' => $v_eks['jenis']
				);
			}
		}

		// $d_nomor = $m_ekspedisi->select('nomor')->distinct('nomor')->get()->toArray();

		// if ( !empty($d_nomor) ) {
		// 	foreach ($d_nomor as $nomor) {
		// 		$ekspedisi = $m_ekspedisi->where('nomor', $nomor['nomor'])
		// 								->orderBy('version', 'desc')
		// 								->orderBy('id', 'desc')
		// 								->with(['logs'])
		// 								->first()->toArray();

		// 		$ket = [];
		// 		$keterangan = '';
		//         foreach ($ekspedisi['logs'] as $log){
		// 			$keterangan = $log['deskripsi'] . ' pada ' . dateTimeFormat($log['waktu']);
		// 			array_push($ket, $keterangan);
		// 		}

		// 		$m_lokasi = new \Model\Storage\Lokasi_model();
		// 		$detail_lokasi = $m_lokasi->where('id', '=', $ekspedisi['alamat_kecamatan'])->first();

		// 		$detail_kota = $m_lokasi->where('id', '=', $detail_lokasi['induk'])->first();

		// 		$data_array = array(
		// 			'id' => $ekspedisi['id'],
		// 			'nip' => $ekspedisi['nomor'],
		// 			'nama' => $ekspedisi['nama'],
		// 			'nik' => $ekspedisi['nik'],
		// 			'alamat' => $detail_kota['nama'],
		// 			'mstatus' => $ekspedisi['mstatus'],
		// 			'status' => $ekspedisi['status'],
		// 			'saldo_awal' => 0,
		// 			'keterangan' => $keterangan,
		// 			'jenis' => $ekspedisi['jenis']
		// 		);

		// 		array_push($datas, $data_array);
		// 	}
		// }

		return $datas;
	}

	public function export_excel()
    {
        $m_ekspedisi = new \Model\Storage\Ekspedisi_model();
        $list_nomor = $m_ekspedisi->select('nomor')->distinct('nomor')->get()->toArray();

        $data = array();
        foreach ($list_nomor as $k_nomor => $nomor) {
            $ekspedisi = $m_ekspedisi->where('nomor', $nomor)
                             ->with(['kecamatan'])
                             ->orderBy('version', 'desc')
                             ->orderBy('id', 'desc')
                             ->first()->toArray();

            $jalan = empty($ekspedisi['alamat_jalan']) ? '' : strtoupper('DSN.'.trim(str_replace('DSN.', '', $ekspedisi['alamat_jalan'])));
            $rt = empty($ekspedisi['alamat_rt']) ? '' : strtoupper(' RT.'.$ekspedisi['alamat_rt']);
            $rw = empty($ekspedisi['alamat_rw']) ? '' : strtoupper('/RW.'.$ekspedisi['alamat_rw']);
            $kelurahan = empty($ekspedisi['alamat_kelurahan']) ? '' : strtoupper(' ,'.$ekspedisi['alamat_kelurahan']);
            $kecamatan = empty($ekspedisi['alamat_kecamatan']) ? '' : strtoupper(' ,'.$ekspedisi['kecamatan']['nama']);
            $kabupaten = empty($ekspedisi['kecamatan']['d_kota']) ? '' : strtoupper(' ,'.$ekspedisi['kecamatan']['d_kota']['nama']);
            $provinsi = empty($ekspedisi['kecamatan']['d_kota']['d_provinsi']) ? '' : strtoupper(' ,'.$ekspedisi['kecamatan']['d_kota']['d_provinsi']['nama']);

            $alamat = $jalan.$rt.$rw.$kelurahan.$kecamatan.$kabupaten.$provinsi;

            $key = $ekspedisi['nama'].'|'.$ekspedisi['nomor'];
            $data[ $key ] = array(
                'id' => $ekspedisi['id'],
                'nomor' => $ekspedisi['nomor'],
                'ktp' => $ekspedisi['nik'],
                'npwp' => $ekspedisi['npwp'],
                'nama_coretax' => $ekspedisi['nama_coretax'],
                'nama' => $ekspedisi['nama'],
                'alamat' => $alamat,
                'status' => $ekspedisi['mstatus']
            );

            ksort($data);
        }

        $content['data'] = $data;
        $res_view_html = $this->load->view('parameter/ekspedisi/export_excel', $content, true);

        $filename = 'export-ekspedisi-'.str_replace('-', '', date('Y-m-d')).'.xls';

        header("Content-type: application/xls");
        header("Content-Disposition: attachment; filename=".$filename."");
        echo $res_view_html;
    }

	public function model($status)
    {
        if ( is_numeric($status) ) {
            $status = getStatus($status);
        }

        $m_ekspedisi = new \Model\Storage\Ekspedisi_model();
        $dashboard = $m_ekspedisi->getDashboard($status);

        return $dashboard;
    }

    public function injekDariMgb()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select eks1.* from mgb_erp_live.dbo.ekspedisi eks1
            right join
                (select max(id) as id, nomor from mgb_erp_live.dbo.ekspedisi group by nomor) eks2
                on
                    eks1.id = eks2.id
            where
                eks1.mstatus = 1
            order by
                eks1.nama asc
        ";
        $d_eks = $m_conf->hydrateRaw( $sql );

        if ( $d_eks->count() > 0 ) {
            $d_eks = $d_eks->toArray();

            foreach ($d_eks as $k_eks => $v_eks) {
                $status = "submit";

				// ekspedisi
				$m_ekspedisi = new \Model\Storage\Ekspedisi_model();
				$ekspedisi_id = $m_ekspedisi->getNextIdentity();

				$m_ekspedisi->id = $ekspedisi_id;
				$m_ekspedisi->jenis = $v_eks['jenis'];
				$m_ekspedisi->nomor = $v_eks['nomor'];
				$m_ekspedisi->nama = $v_eks['nama'];
				$m_ekspedisi->nik = $v_eks['nik'];
				$m_ekspedisi->cp = $v_eks['cp'];
				$m_ekspedisi->npwp = $v_eks['npwp'];
				$m_ekspedisi->skb = $v_eks['skb'];
				$m_ekspedisi->tgl_habis_skb = $v_eks['tgl_habis_skb'];
				$m_ekspedisi->alamat_kecamatan = $v_eks['alamat_kecamatan'];
				$m_ekspedisi->alamat_kelurahan = $v_eks['alamat_kelurahan'];
				$m_ekspedisi->alamat_rt = $v_eks['alamat_rt'];
				$m_ekspedisi->alamat_rw = $v_eks['alamat_rw'];
				$m_ekspedisi->alamat_jalan = $v_eks['alamat_jalan'];
				$m_ekspedisi->usaha_kecamatan = $v_eks['usaha_kecamatan'];
				$m_ekspedisi->usaha_kelurahan = $v_eks['usaha_kelurahan'];
				$m_ekspedisi->usaha_rt = $v_eks['usaha_rt'];
				$m_ekspedisi->usaha_rw = $v_eks['usaha_rw'];
				$m_ekspedisi->usaha_jalan = $v_eks['usaha_jalan'];
				$m_ekspedisi->status = $v_eks['status'];
				$m_ekspedisi->mstatus = $v_eks['mstatus'];
				$m_ekspedisi->platform = $v_eks['platform'];
				$m_ekspedisi->version = $v_eks['version'];
				$m_ekspedisi->potongan_pph_id = $v_eks['potongan_pph_id'];
				$m_ekspedisi->save();

                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select te.* from mgb_erp_live.dbo.telp_ekspedisi te
                    where
                        te.ekspedisi_id = ".$v_eks['id']."
                ";
                $d_telp = $m_conf->hydrateRaw( $sql );
                if ( $d_telp->count() > 0 ) {
                    $d_telp = $d_telp->toArray();

                    foreach ($d_telp as $k_telp => $v_telp) {
                        $m_telp = new \Model\Storage\TelpEkspedisi_model();
						$m_telp->id = $m_telp->getNextIdentity();
						$m_telp->ekspedisi_id = $ekspedisi_id;
						$m_telp->nomor = $v_telp['nomor'];
						$m_telp->save();
                    }
                }

                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select be.* from mgb_erp_live.dbo.bank_ekspedisi be
                    where
                        be.ekspedisi_id = ".$v_eks['id']."
                ";
                $d_bank = $m_conf->hydrateRaw( $sql );
                if ( $d_bank->count() > 0 ) {
                    $d_bank = $d_bank->toArray();

                    foreach ($d_bank as $k_bank => $v_bank) {
                        $m_bank = new \Model\Storage\BankEkspedisi_model();
						$bank_ekspedisi_id = $m_bank->getNextIdentity();

						$m_bank->id = $bank_ekspedisi_id;
						$m_bank->ekspedisi_id = $ekspedisi_id;
						$m_bank->bank = $v_bank['bank'];
						$m_bank->rekening_nomor = $v_bank['rekening_nomor'];
						$m_bank->rekening_pemilik = $v_bank['rekening_pemilik'];
						$m_bank->rekening_cabang_bank = $v_bank['rekening_cabang_bank'];
						$m_bank->save();
                    }
                }

                $d_eks = $m_ekspedisi->where('id', $ekspedisi_id)->with(['telepons', 'banks'])->first();

                $deskripsi_log = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
				Modules::run( 'base/event/save', $d_eks, $deskripsi_log );
            }
        }
    }

	/**************************************************************************************
	 * CLONE EKSPEDISI KE DATABASE GML
	 *
	 * Clone 1 data ekspedisi (baris ekspedisi versi terbaru beserta struktur turunannya -
	 * telp_ekspedisi, bank_ekspedisi, lampiran + file fisiknya) dari database aplikasi ini
	 * ke database GML (perusahaan lain hasil fitur "Clone Perusahaan" - lihat
	 * base/CloneCompany.php). ID/nomor dipertahankan identik ke sumber. Beda dari
	 * Supplier/Pelanggan: ekspedisi py tabel SENDIRI (bukan numpang di tabel `pelanggan`),
	 * jadi tidak perlu filter `tipe='...'` sama sekali.
	 *
	 * Guard: kalau nomor ini sudah ada di GML dan MASIH AKTIF (mstatus=1) -> ditolak total.
	 * Kalau sudah ada tapi NONAKTIF (mstatus=0) -> tawarkan konfirmasi injek ulang (param
	 * 'force'=1 dari frontend); kalau disetujui, data lama di GML dihapus dulu (cascade)
	 * baru clone ulang jalan dari awal.
	 *
	 * Pola & helper (dbConn/getGmlDbName/cloneTableRows/cloneLokasiChain/copyLampiranFiles/
	 * cloneLogHistoryRows/deleteGmlEkspedisiCascade) SENGAJA diduplikasi persis dari
	 * parameter/Supplier.php & Pelanggan.php (bukan trait/base class bersama) - konsisten
	 * dgn konvensi codebase ini yg memang menduplikasi helper per controller. Detail teknis
	 * (kenapa pakai getConnection() bukan facade DB::, kenapa fetch mode array bukan object,
	 * dst) ada di komentar versi Peternak.php/Supplier.php.
	 **************************************************************************************/

	public function cloneToGml()
	{
		$akses = hakAkses($this->url);
		if ( empty($akses['a_submit']) || $akses['a_submit'] != 1 ) {
			$this->result['message'] = 'Anda tidak memiliki akses untuk clone data ekspedisi ke GML.';
			display_json($this->result);
			return;
		}

		$nomor = $this->input->post('nomor');

		try {
			if ( empty($nomor) ) {
				throw new Exception('Nomor ekspedisi tidak boleh kosong.');
			}

			$gmlDb = $this->getGmlDbName();

			// NOTE: 1. ambil versi TERBARU ekspedisi (konvensi max(id) per nomor - lihat
			// getListEkspedisi() & edit() yg selalu bikin baris baru).
			$m_ekspedisi = new \Model\Storage\Ekspedisi_model();
			$sql = "
				SELECT e.* FROM ekspedisi e
				RIGHT JOIN (
					SELECT MAX(id) AS id, nomor FROM ekspedisi
					WHERE nomor = ?
					GROUP BY nomor
				) e2 ON e.id = e2.id
			";
			$d_ekspedisi = $m_ekspedisi->hydrateRaw($sql, array($nomor));
			if ( $d_ekspedisi->count() == 0 ) {
				throw new Exception("Data ekspedisi dengan nomor '{$nomor}' tidak ditemukan.");
			}
			$ekspedisi = $d_ekspedisi->first();
			$ekspedisiId = $ekspedisi->id;

			// NOTE: 2. guard - kalau nomor ini sudah ada di GML: tolak total kalau MASIH
			// AKTIF (mstatus=1), tapi kalau NONAKTIF tawarkan konfirmasi injek ulang
			// (force=1) alih2 langsung menolak - data lama di GML dihapus dulu (cascade)
			// baru clone ulang.
			$force = $this->input->post('force');
			$existing = $this->dbConn()->selectOne("SELECT id, mstatus FROM [{$gmlDb}].dbo.[ekspedisi] WHERE nomor = ?", array($nomor));
			if ( !empty($existing) ) {
				$isAktif = ( (int) $existing['mstatus'] === 1 );
				if ( $isAktif ) {
					throw new Exception("Ekspedisi nomor {$nomor} sudah pernah di-clone ke GML (id GML #{$existing['id']}) dan masih aktif.");
				}
				if ( empty($force) ) {
					$this->result['status'] = 0;
					$this->result['need_confirm'] = 1;
					$this->result['message'] = "Ekspedisi nomor {$nomor} sudah pernah di-clone ke GML (id GML #{$existing['id']}) tapi berstatus tidak aktif. Injek ulang? Data lama di GML akan dihapus & diganti data terbaru dari GMP.";
					display_json($this->result);
					return;
				}
				$this->deleteGmlEkspedisiCascade($gmlDb, $existing['id']);
			}

			// NOTE: 3. kumpulkan id bank_ekspedisi (utk cascade lampiran).
			$bankIds = array_map(function($r){ return $r['id']; }, $this->dbConn()->select("SELECT id FROM bank_ekspedisi WHERE ekspedisi_id = ?", array($ekspedisiId)));

			$report = array();

			// NOTE: 4. lookup pendukung - lokasi (kecamatan alamat pribadi + alamat usaha),
			// supaya alamat tidak tampil kosong di GML. platform/badan_usaha/potongan_pph_id
			// TIDAK perlu ditangani di sini krn sudah ikut allowlist Clone Perusahaan
			// (ekspedisi_pph23, master_badan_usaha).
			$kecamatanIds = array($ekspedisi->alamat_kecamatan, $ekspedisi->usaha_kecamatan);
			$report['lokasi_baru'] = $this->cloneLokasiChain($kecamatanIds);

			// NOTE: 5. data inti ekspedisi.
			$report['ekspedisi'] = $this->cloneTableRows('ekspedisi', 'id = ?', array($ekspedisiId));
			$report['telp_ekspedisi'] = $this->cloneTableRows('telp_ekspedisi', 'ekspedisi_id = ?', array($ekspedisiId));
			$report['bank_ekspedisi'] = $this->cloneTableRows('bank_ekspedisi', 'ekspedisi_id = ?', array($ekspedisiId));

			// NOTE: 6. lampiran (ekspedisi + tiap bank_ekspedisi) + file fisiknya.
			$lampiranWhere = "(tabel = 'ekspedisi' AND tabel_id = ?)";
			$lampiranBinds = array($ekspedisiId);
			if ( !empty($bankIds) ) {
				$inBank = implode(',', array_map('intval', $bankIds));
				$lampiranWhere .= " OR (tabel = 'bank_ekspedisi' AND tabel_id IN ({$inBank}))";
			}

			$lampiranRows = $this->dbConn()->select("SELECT * FROM lampiran WHERE {$lampiranWhere}", $lampiranBinds);
			$report['lampiran'] = $this->cloneTableRows('lampiran', $lampiranWhere, $lampiranBinds);
			$report['lampiran_file'] = $this->copyLampiranFiles($lampiranRows);

			// NOTE: 7. riwayat log_tables (kolom "Keterangan" di list Ekspedisi dibaca dari
			// sini) + snapshot log_history (write-only, tidak ada fitur yg membacanya balik,
			// tetap di-clone atas permintaan eksplisit utk kelengkapan audit) + 1 catatan baru
			// langsung di GML sbg jejak provenance data hasil clone.
			$logIds = array_map(function($r){ return $r['id']; }, $this->dbConn()->select("SELECT id FROM log_tables WHERE tbl_name = 'ekspedisi' AND tbl_id = ?", array($ekspedisiId)));
			$report['log_tables'] = $this->cloneTableRows('log_tables', "tbl_name = 'ekspedisi' AND tbl_id = ?", array($ekspedisiId));
			$report['log_history'] = $this->cloneLogHistoryRows($logIds);

			$deskripsi_provenance = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
			$this->dbConn()->statement("
				INSERT INTO [{$gmlDb}].dbo.[log_tables] ([tbl_name],[tbl_id],[user_id],[waktu],[deskripsi],[_action])
				VALUES ('ekspedisi', ?, ?, GETDATE(), ?, 'insert')
			", array($ekspedisiId, $this->userid, $deskripsi_provenance));

			$this->result['status'] = 1;
			$this->result['message'] = "Data ekspedisi {$ekspedisi->nama} ({$nomor}) berhasil di-clone ke GML.";
			// Ringkasan salin file lampiran supaya kelihatan kalau ada file yg dilewati (mis. app_path salah / file sumber tdk ada)
			$lf = $report['lampiran_file'];
			$this->result['message'] .= '<br><br>File lampiran: ' . (int) $lf['copied'] . ' disalin, ' . (int) $lf['identik'] . ' identik (dilewati), ' . (int) $lf['renamed'] . ' diganti nama, ' . (int) $lf['dilewati'] . ' dilewati.'
			    . ( empty($lf['catatan']) ? '' : '<br>' . implode('<br>', array_map('htmlspecialchars', $lf['catatan'])) );
			$this->result['content'] = $report;
		} catch (\Exception $e) {
			$this->result['message'] = 'Gagal clone ke GML: ' . $e->getMessage();
		}

		display_json($this->result);
	}

	/**
	 * Hapus tuntas (cascade) 1 ekspedisi beserta seluruh struktur turunannya LANGSUNG DI
	 * GML - dipakai guard cloneToGml() saat user pilih "injek ulang" utk baris GML yg
	 * statusnya sudah nonaktif. $ekspedisiId di sini id versi GML yg mau dihapus (BUKAN id
	 * sumber - bisa beda kalau ada edit lanjutan di sumber sejak clone pertama). log_history
	 * TIDAK ikut dibersihkan (write-only, orphan row di situ tidak berdampak apa2 - lihat
	 * catatan cloneLogHistoryRows()).
	 */
	private function deleteGmlEkspedisiCascade($gmlDb, $ekspedisiId)
	{
		$bankIds = array_map(function($r){ return $r['id']; }, $this->dbConn()->select("SELECT id FROM [{$gmlDb}].dbo.[bank_ekspedisi] WHERE ekspedisi_id = ?", array($ekspedisiId)));

		$lampiranWhere = "(tabel = 'ekspedisi' AND tabel_id = ?)";
		$lampiranBinds = array($ekspedisiId);
		if ( !empty($bankIds) ) {
			$inBank = implode(',', array_map('intval', $bankIds));
			$lampiranWhere .= " OR (tabel = 'bank_ekspedisi' AND tabel_id IN ({$inBank}))";
		}
		$this->dbConn()->statement("DELETE FROM [{$gmlDb}].dbo.[lampiran] WHERE {$lampiranWhere}", $lampiranBinds);
		$this->dbConn()->statement("DELETE FROM [{$gmlDb}].dbo.[log_tables] WHERE tbl_name = 'ekspedisi' AND tbl_id = ?", array($ekspedisiId));

		$this->dbConn()->statement("DELETE FROM [{$gmlDb}].dbo.[bank_ekspedisi] WHERE ekspedisi_id = ?", array($ekspedisiId));
		$this->dbConn()->statement("DELETE FROM [{$gmlDb}].dbo.[telp_ekspedisi] WHERE ekspedisi_id = ?", array($ekspedisiId));
		$this->dbConn()->statement("DELETE FROM [{$gmlDb}].dbo.[ekspedisi] WHERE id = ?", array($ekspedisiId));
	}

	/**
	 * Koneksi database (Eloquent) yg dipakai semua query raw di fitur clone-ke-GML ini.
	 * Lihat komentar versi Peternak.php::dbConn() utk detail kenapa TIDAK pakai facade
	 * \Illuminate\Support\Facades\DB (belum ter-bootstrap di app ini - Capsule::setAsGlobal()
	 * di MY_Controller.php tidak memanggil Facade::setFacadeApplication()).
	 */
	private function dbConn()
	{
		return (new \Model\Storage\Conf())->getConnection();
	}

	/**
	 * Nama database GML dari konfigurasi koneksi ('gml' di application/config/env.php).
	 * Divalidasi harus 1 SQL Server yg sama dgn koneksi 'default' krn seluruh clone di
	 * sini pakai query cross-database T-SQL langsung, bukan replikasi via jaringan.
	 */
	private function getGmlDbName()
	{
		$conn = $this->config->item('connection');
		if ( empty($conn['gml']['database']) ) {
			throw new Exception("Koneksi 'gml' belum dikonfigurasi di application/config/env.php.");
		}
		if ( $conn['gml']['host'] !== $conn['default']['host'] || (string) $conn['gml']['port'] !== (string) $conn['default']['port'] ) {
			throw new Exception("Database GML harus berada di SQL Server yang sama dengan database utama untuk fitur clone-per-ekspedisi ini.");
		}
		return $conn['gml']['database'];
	}

	/**
	 * Copy baris dari tabel $tbl (skema sumber/default) yg cocok $whereSql ke tabel yg
	 * sama persis di database GML (nama tabel/kolom identik, hasil clone skema fitur
	 * Clone Perusahaan) lewat query cross-database T-SQL langsung di koneksi yg sama.
	 * Kolom & status identity dideteksi dinamis lewat sys.columns (bukan hardcode) -
	 * kalau tabelnya punya identity column, otomatis dibungkus SET IDENTITY_INSERT dlm
	 * SATU batch bareng INSERT-nya (dipisah jadi 2 query terpisah terbukti gagal di
	 * CloneCompany krn IDENTITY_INSERT scope-nya per sesi koneksi).
	 * Return: jumlah baris yg akhirnya ada di GML sesuai $whereSql (0 kalau tidak ada).
	 */
	private function cloneTableRows($tbl, $whereSql, $whereBinds = array())
	{
		$gmlDb = $this->getGmlDbName();

		$cols = $this->dbConn()->select("
			SELECT c.name, c.is_identity, c.is_computed
			FROM sys.columns c
			WHERE c.object_id = OBJECT_ID(?)
			ORDER BY c.column_id
		", array($tbl));

		$insertCols = array();
		$hasIdentity = false;
		foreach ($cols as $c) {
			if ( !empty($c['is_computed']) ) {
				continue;
			}
			$insertCols[] = $c['name'];
			if ( !empty($c['is_identity']) ) {
				$hasIdentity = true;
			}
		}

		if ( empty($insertCols) ) {
			throw new Exception("Tabel '{$tbl}' tidak ditemukan di skema (pastikan skema GML sudah di-clone lewat fitur Clone Perusahaan).");
		}

		$colList = '[' . implode('], [', $insertCols) . ']';
		$targetTbl = "[{$gmlDb}].dbo.[{$tbl}]";

		$insertSql = "INSERT INTO {$targetTbl} ({$colList}) SELECT {$colList} FROM [{$tbl}] WHERE {$whereSql}";

		$sql = $hasIdentity
			? "SET IDENTITY_INSERT {$targetTbl} ON; {$insertSql}; SET IDENTITY_INSERT {$targetTbl} OFF;"
			: $insertSql;

		$this->dbConn()->statement($sql, $whereBinds);

		$countRow = $this->dbConn()->selectOne("SELECT COUNT(*) AS jml FROM {$targetTbl} WHERE {$whereSql}", $whereBinds);
		return (int) $countRow['jml'];
	}

	/**
	 * Clone baris log_history.log_tables (snapshot JSON per perubahan, ditulis Event.php
	 * saat save/update/delete) yg id_header-nya ada di $logTableIds, dari koneksi 'log'
	 * (log_history_gmp_erp2) ke 'gml_log' (log_history_GML_ERP_TEST) - pasangan database
	 * TERPISAH dari default/gml, tapi tetap 1 SQL Server yg sama jadi cukup query
	 * cross-database biasa lewat koneksi 'default' yg sudah ada (bukan koneksi PDO baru).
	 * Data ini WRITE-ONLY di seluruh aplikasi (dicek: tidak ada fitur yg membacanya balik)
	 * - di-clone murni utk kelengkapan audit trail atas permintaan eksplisit, bukan krn
	 * ada tampilan di GML yg butuh ini. Return 0 (skip diam2, bukan error) kalau koneksi
	 * 'log'/'gml_log' belum lengkap dikonfigurasi.
	 */
	private function cloneLogHistoryRows($logTableIds)
	{
		$logTableIds = array_values(array_unique(array_filter($logTableIds, function($v){ return !empty($v); })));
		if ( empty($logTableIds) ) {
			return 0;
		}

		$conn = $this->config->item('connection');
		if ( empty($conn['log']['database']) || empty($conn['gml_log']['database']) ) {
			return 0;
		}
		if ( $conn['gml_log']['host'] !== $conn['log']['host'] || (string) $conn['gml_log']['port'] !== (string) $conn['log']['port'] ) {
			throw new Exception("Database 'gml_log' harus berada di SQL Server yang sama dengan koneksi 'log'.");
		}

		$sourceLogDb = $conn['log']['database'];
		$targetLogDb = $conn['gml_log']['database'];
		$inIds = implode(',', array_map('intval', $logTableIds));

		$this->dbConn()->statement("
			INSERT INTO [{$targetLogDb}].dbo.[log_tables] ([id_header], [_json])
			SELECT [id_header], [_json] FROM [{$sourceLogDb}].dbo.[log_tables]
			WHERE id_header IN ({$inIds})
		");

		$countRow = $this->dbConn()->selectOne("SELECT COUNT(*) AS jml FROM [{$targetLogDb}].dbo.[log_tables] WHERE id_header IN ({$inIds})");
		return (int) $countRow['jml'];
	}

	/**
	 * Clone baris lokasi (kecamatan dari $ids) + seluruh rantai induknya (kab/kota,
	 * provinsi, dst) ke GML kalau belum ada di sana - tabel lokasi SENGAJA tidak
	 * termasuk allowlist Clone Perusahaan (beda dgn wilayah yg sudah ikut ter-copy).
	 * Return: jumlah baris lokasi baru yg ditambahkan.
	 */
	private function cloneLokasiChain($ids)
	{
		$gmlDb = $this->getGmlDbName();

		$queue = array_values(array_unique(array_filter($ids, function($v){ return !empty($v); })));
		$visited = array();
		$cloned = 0;

		while ( !empty($queue) ) {
			$id = array_shift($queue);
			if ( in_array($id, $visited) ) {
				continue;
			}
			$visited[] = $id;

			$existing = $this->dbConn()->selectOne("SELECT id FROM [{$gmlDb}].dbo.[lokasi] WHERE id = ?", array($id));
			if ( !empty($existing) ) {
				continue;
			}

			$row = $this->dbConn()->selectOne("SELECT * FROM lokasi WHERE id = ?", array($id));
			if ( empty($row) ) {
				continue;
			}

			$this->cloneTableRows('lokasi', 'id = ?', array($id));
			$cloned++;

			if ( !empty($row['induk']) ) {
				$queue[] = $row['induk'];
			}
		}

		return $cloned;
	}

	/**
	 * Copy file fisik lampiran (dari uploads/ app ini) ke uploads/ app GML, memakai
	 * path folder aplikasi GML dari config('connection.gml.app_path') - lihat
	 * env.php. Kalau path itu belum diset/tidak valid, langkah ini di-skip dgn
	 * catatan jelas (baris lampiran di database TETAP ter-clone; cuma file fisiknya
	 * yg perlu dipindah manual). Kalau nama file sudah ada di tujuan tapi ISINYA
	 * beda (sha1 beda), file di-rename otomatis & baris lampiran yg baru di-insert
	 * di GML ikut di-update supaya path-nya tetap match dgn file fisiknya.
	 */
	private function copyLampiranFiles($lampiranRows)
	{
		$summary = array('copied' => 0, 'identik' => 0, 'renamed' => 0, 'dilewati' => 0, 'catatan' => array());

		if ( empty($lampiranRows) ) {
			return $summary;
		}

		$gmlConn = $this->config->item('connection')['gml'];
		if ( empty($gmlConn['app_path']) ) {
			$summary['dilewati'] = count($lampiranRows);
			$summary['catatan'][] = "'app_path' utk koneksi 'gml' belum diset di application/config/env.php - file lampiran tidak ikut ter-copy (baris lampiran di database tetap ter-clone).";
			return $summary;
		}

		$sourceDir = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
		$targetDir = rtrim($gmlConn['app_path'], '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

		if ( !is_dir($targetDir) ) {
			$summary['dilewati'] = count($lampiranRows);
			$summary['catatan'][] = "Folder uploads GML tidak ditemukan di '{$targetDir}'.";
			return $summary;
		}

		$gmlDb = $this->getGmlDbName();

		foreach ($lampiranRows as $row) {
			$fileName = $row['path'];
			if ( empty($fileName) ) {
				$summary['dilewati']++;
				continue;
			}

			$sourceFile = $sourceDir . $fileName;
			if ( !file_exists($sourceFile) ) {
				$summary['dilewati']++;
				$summary['catatan'][] = "File sumber tidak ditemukan: {$fileName}";
				continue;
			}

			$targetFile = $targetDir . $fileName;
			$finalName = $fileName;

			if ( file_exists($targetFile) ) {
				if ( sha1_file($targetFile) === sha1_file($sourceFile) ) {
					$summary['identik']++;
					continue;
				}

				$ext_pos = strrpos($fileName, '.');
				$base = $ext_pos !== false ? substr($fileName, 0, $ext_pos) : $fileName;
				$ext = $ext_pos !== false ? substr($fileName, $ext_pos) : '';
				$increment = 0;
				do {
					$increment++;
					$finalName = $base . '_gml' . $increment . $ext;
					$targetFile = $targetDir . $finalName;
				} while ( file_exists($targetFile) );

				$this->dbConn()->statement("UPDATE [{$gmlDb}].dbo.[lampiran] SET path = ? WHERE id = ?", array($finalName, $row['id']));
				$summary['renamed']++;
			}

			if ( @copy($sourceFile, $targetFile) ) {
				$summary['copied']++;
			} else {
				$summary['dilewati']++;
				$summary['catatan'][] = "Gagal copy file: {$fileName}";
			}
		}

		return $summary;
	}
}