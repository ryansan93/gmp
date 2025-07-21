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
			$content['list_provinsi'] = $this->getLokasi('PV');
			$content['list_lampiran_ekspedisi'] = $this->getNamaLampiran("EKSPEDISI", "KTP Ekspedisi")->first();
			$content['list_lampiran_usaha_ekspedisi'] = $this->getNamaLampiran("EKSPEDISI", "NPWP Ekspedisi")->first();
			$content['list_lampiran_rekening_ekspedisi'] = $this->getNamaLampiran("BANK_EKSPEDISI", "Rekening Ekspedisi")->first();
			$content['list_lampiran_dds_ekspedisi'] = $this->getNamaLampiran("EKSPEDISI", "DDP Ekspedisi")->first();
			$content['ekspedisi_pph23'] = $this->getEkspedisiPph23();

			// load list ekspedisi
			// $detail_content['ekspedisis'] = $this->getListEkspedisi();
			$data['title_menu'] = 'Master Ekspedisi';
			$data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

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
        $content['data'] = null;
        $html = $this->load->view($this->pathView . 'addForm', $content);
        
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

        $html = $this->load->view($this->pathView . 'editForm', $content);
        
        return $html;
    }

    public function viewForm($id, $resubmit)
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

        $html = $this->load->view($this->pathView . 'viewForm', $content);
        
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
		$html = $this->load->view($this->pathView . 'form_status_ekspedisi', $content, true);

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
}