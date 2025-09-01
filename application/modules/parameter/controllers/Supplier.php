<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Supplier extends Public_Controller {

	private $pathView = 'parameter/supplier/';
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
				'assets/parameter/supplier/js/supplier.js'
			));

			$this->add_external_css(array(
				'assets/jquery/easy-autocomplete/easy-autocomplete.min.css',
				'assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css',
				'assets/parameter/supplier/css/supplier.css',
			));

			$data = $this->includes;

			$content['title_panel'] = 'Master Data Supplier';
			$content['akses'] = $akses;
			$content['addForm'] = $this->add_form();
			// $content['list_provinsi'] = $this->getLokasi('PV');
			// $content['list_lampiran_supplier'] = $this->getNamaLampiran("SUPPLIER")->first();
			// $content['list_lampiran_usaha_supplier'] = $this->getNamaLampiran("USAHA_SUPPLIER")->first();
			// $content['list_lampiran_rekening_supplier'] = $this->getNamaLampiran("REKENING_SUPPLIER")->first();
			// $content['list_lampiran_dds_supplier'] = $this->getNamaLampiran("LAMPIRAN_SUPPLIER")->first();

			// load list pelanggan
			// $detail_content['pelanggans'] = $this->getListSupplier();
			$data['title_menu'] = 'Master Supplier';
			$data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function load_form()
    {
        $id = $this->input->get('id');
        $resubmit = $this->input->get('resubmit');
        $html = '';

        if ( !empty($id) ) {
            if ( !empty($resubmit) ) {
                /* NOTE : untuk edit */
                $html = $this->edit_form($id, $resubmit);
            } else {
                /* NOTE : untuk view */
                $html = $this->view_form($id, $resubmit);
            }
        } else {
            /* NOTE : untuk add */
            $html = $this->add_form();
        }

        echo $html;
    }

    public function list_supl()
    {
        $akses = hakAkses($this->url);

        $data = $this->getListSupplier();

        $content['akses'] = $akses;
        $content['data'] = $data;

        $html = $this->load->view($this->pathView . 'list', $content);
        
        echo $html;
    }

    public function add_form()
    {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
		$m_model = new \Model\Storage\Jenis_model();
        $d_jns = $m_model->getData();
        $content['jenis'] = !empty($d_jns) ? $d_jns : null;
        $content['list_provinsi'] = $this->getLokasi('PV');
		$content['list_lampiran_supplier'] = $this->getNamaLampiran("SUPPLIER", "KTP Supplier")->first();
		$content['list_lampiran_usaha_supplier'] = $this->getNamaLampiran("SUPPLIER", "NPWP Supplier")->first();
		$content['list_lampiran_rekening_supplier'] = $this->getNamaLampiran("BANK_SUPPLIER", "Rekening Supplier")->first();
		$content['list_lampiran_dds_supplier'] = $this->getNamaLampiran("SUPPLIER", "DDS Supplier")->first();
        $content['data'] = null;
        $html = $this->load->view($this->pathView . 'add_form', $content, true);
        
        return $html;
    }

    public function edit_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        // mengambil data supplier
		$m_supplier = new \Model\Storage\Supplier_model();
		$d_supplier = $m_supplier->where('tipe', 'supplier')->where('id', $id)->with('telepons')->with('banks')->with('logs')->first();
		
		// mengambil lokasi
		$lokasi = new \Model\Storage\Lokasi_model();
		$kec = $lokasi->where('id', $d_supplier['alamat_kecamatan'])->first();
		$kota = $lokasi->where('id', $kec['induk'])->first();
		$prov = $lokasi->where('id', $kota['induk'])->first();
		$kec_usaha = $lokasi->where('id', $d_supplier['usaha_kecamatan'])->first();
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

		// mengambil lampiran supplier
		$m_nama_lampiran = new \Model\Storage\NamaLampiran_model;
		$d_nama_lampiran = $m_nama_lampiran->where('jenis', 'BANK_SUPPLIER')->first();

		$m_lampiran = new \Model\Storage\Lampiran_model;
		$d_lampiran = $m_lampiran->where('tabel', 'bank_supplier')->where('nama_lampiran', $d_nama_lampiran['id'])->get()->toArray();

		// cetak_r($d_pelanggan->toArray(), 1);

		$lampiran_ktp = $this->getLampiranSupplier($d_supplier['id'], 'KTP Supplier');
		$lampiran_npwp = $this->getLampiranSupplier($d_supplier['id'], 'NPWP Supplier');
		$lampiran_dds = $this->getLampiranSupplier($d_supplier['id'], 'DDS Supplier');

		$content['data'] = $d_supplier;
		$content['lokasi'] = $detail_lokasi;
		$content['l_ktp'] = $lampiran_ktp;
		$content['l_npwp'] = $lampiran_npwp;
		$content['l_dds'] = $lampiran_dds;
        $content['akses'] = $akses;

		$m_model = new \Model\Storage\Jenis_model();
        $d_jns = $m_model->getData();
        $content['jenis'] = !empty($d_jns) ? $d_jns : null;
        $content['list_provinsi'] = $this->getLokasi('PV');
		$content['list_lampiran_supplier'] = $this->getNamaLampiran("SUPPLIER", "KTP Supplier")->first();
		$content['list_lampiran_usaha_supplier'] = $this->getNamaLampiran("SUPPLIER", "NPWP Supplier")->first();
		$content['list_lampiran_rekening_supplier'] = $this->getNamaLampiran("BANK_SUPPLIER", "Rekening Supplier")->first();
		$content['list_lampiran_dds_supplier'] = $this->getNamaLampiran("SUPPLIER", "DDS Supplier")->first();

        $html = $this->load->view($this->pathView . 'edit_form', $content);
        
        return $html;
    }

    public function view_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        // mengambil data supplier
		$m_supplier = new \Model\Storage\Supplier_model();
		$d_supplier = $m_supplier->where('tipe', 'supplier')->where('id', $id)->with(['d_jenis', 'telepons', 'banks', 'logs'])->first();
		
		// mengambil lokasi
		$lokasi = new \Model\Storage\Lokasi_model();
		$kec = $lokasi->where('id', $d_supplier['alamat_kecamatan'])->first();
		$kota = $lokasi->where('id', $kec['induk'])->first();
		$prov = $lokasi->where('id', $kota['induk'])->first();
		$kec_usaha = $lokasi->where('id', $d_supplier['usaha_kecamatan'])->first();
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

		// mengambil lampiran pelanggan
		$m_nama_lampiran = new \Model\Storage\NamaLampiran_model;
		$d_nama_lampiran = $m_nama_lampiran->where('jenis', 'BANK_SUPPLIER')->first();

		$m_lampiran = new \Model\Storage\Lampiran_model;
		$d_lampiran = $m_lampiran->where('tabel', 'bank_supplier')->where('nama_lampiran', $d_nama_lampiran['id'])->get();

		$lampiran_ktp = $this->getLampiranSupplier($d_supplier['id'], 'KTP Supplier');
		$lampiran_npwp = $this->getLampiranSupplier($d_supplier['id'], 'NPWP Supplier');
		$lampiran_dds = $this->getLampiranSupplier($d_supplier['id'], 'DDS Supplier');

		$content['data'] = $d_supplier;
		$content['lokasi'] = $detail_lokasi;
		$content['l_ktp'] = $lampiran_ktp;
		$content['l_npwp'] = $lampiran_npwp;
		$content['l_dds'] = $lampiran_dds;
		$content['tbl_logs'] = $this->getLogs($d_supplier->nomor);
        $content['akses'] = $akses;

        $html = $this->load->view($this->pathView . 'view_form', $content);
        
        return $html;
    }

    public function getLogs($nomor = null) {
	    $m_supl = new \Model\Storage\Supplier_model;
    	$d_supl = $m_supl->where('nomor', $nomor)->where('tipe', 'supplier')->orderBy('version', 'asc')->get()->toArray();

    	$logs = array();
    	foreach ($d_supl as $key => $v_supl) {
	    	$m_log = new \Model\Storage\LogTables_model;
	    	$d_log = $m_log->where('tbl_name', 'pelanggan')->where('tbl_id', $v_supl['id'])->get()->toArray();

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

    public function getLampiranSupplier($id, $nama) {
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
			// supplier
			$m_supplier = new \Model\Storage\Supplier_model();
			$supplier_id = $m_supplier->getNextIdentity();

			$m_supplier->id = $supplier_id;		
			$m_supplier->jenis = $params['jenis_supplier'];
			$kode_jenis = 'S';

			$m_supplier->nomor = $m_supplier->getNextNomor($kode_jenis);
			$m_supplier->nama = $params['nama'];
			$m_supplier->nik = $params['ktp'];
			$m_supplier->cp = $params['cp'];
			$m_supplier->npwp = $params['npwp'];
			$m_supplier->skb = $params['skb'];
			$m_supplier->tgl_habis_skb = $params['tgl_habis_skb'];
			$m_supplier->alamat_kecamatan = $params['alamat_supplier']['kecamatan'];
			$m_supplier->alamat_kelurahan = $params['alamat_supplier']['kelurahan'];
			$m_supplier->alamat_rt = $params['alamat_supplier']['rt'] ?: null;
			$m_supplier->alamat_rw = $params['alamat_supplier']['rw'] ?: null;
			$m_supplier->alamat_jalan = $params['alamat_supplier']['alamat'] ?: null;
			$m_supplier->usaha_kecamatan = $params['alamat_usaha']['kecamatan'];
			$m_supplier->usaha_kelurahan = $params['alamat_usaha']['kelurahan'];
			$m_supplier->usaha_rt = $params['alamat_usaha']['rt'] ?: null;
			$m_supplier->usaha_rw = $params['alamat_usaha']['rw'] ?: null;
			$m_supplier->usaha_jalan = $params['alamat_usaha']['alamat'] ?: null;
			$m_supplier->status = $status;
			$m_supplier->mstatus = 1;
			$m_supplier->tipe = 'supplier';
			$m_supplier->plafon = $params['plafon'];
			$m_supplier->jatuh_tempo = $params['jatuh_tempo'];
			$m_supplier->version = 1;
			$m_supplier->save();

			$deskripsi_log_supplier = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
			Modules::run( 'base/event/save', $m_supplier, $deskripsi_log_supplier );

			// telepon supplier
			$telepons = $params['telepons'];
			foreach ($telepons as $k => $telepon) {
				$m_telp = new \Model\Storage\TelpPelanggan_model();
				$m_telp->id = $m_telp->getNextIdentity();
				$m_telp->pelanggan = $supplier_id;
				$m_telp->nomor = $telepon;
				$m_telp->save();
				Modules::run( 'base/event/save', $m_telp, $deskripsi_log_supplier );
			}

			// rekening dan bank supplier
			$banks = $params['banks'];
			foreach ($banks as $k => $bank) {
				$m_bank = new \Model\Storage\BankPelanggan_model();
				$bank_plg_id = $m_bank->getNextIdentity();

				$m_bank->id = $bank_plg_id;
				$m_bank->pelanggan = $supplier_id;
				$m_bank->bank = $bank['nama_bank'];
				$m_bank->rekening_nomor = $bank['nomer_rekening'];
				$m_bank->rekening_pemilik = $bank['nama_pemilik'];
				$m_bank->rekening_cabang_bank = $bank['cabang_bank'];
				$m_bank->save();
				Modules::run( 'base/event/save', $m_telp, $deskripsi_log_supplier );
			}

			$this->result['status'] = 1;
			$this->result['message'] = 'Data supplier sukses disimpan';
			$this->result['content'] = array('id' => $supplier_id);
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

    	display_json($this->result);
	}

	public function edit() {
		$params = $this->input->post('params');

		try {
			$supplier_id_old = $params['id'];
			$status = $params['status'];
			$mstatus = $params['mstatus'];
			$version = $params['version'] + 1;

			$m_supplier = new \Model\Storage\Supplier_model();
			$m_supplier->where('id', $supplier_id_old)->update(
				array(
					'mstatus' => 0
				)
			);

			// supplier
			$m_supplier = new \Model\Storage\Supplier_model();
			$supplier_id = $m_supplier->getNextIdentity();

			$m_supplier->id = $supplier_id;
			$m_supplier->jenis = $params['jenis_supplier'];
			$m_supplier->nomor = $params['nomor'];
			$m_supplier->nama = $params['nama'];
			$m_supplier->nik = $params['ktp'];
			$m_supplier->cp = $params['cp'];
			$m_supplier->npwp = $params['npwp'];
			$m_supplier->skb = $params['skb'];
			$m_supplier->tgl_habis_skb = $params['tgl_habis_skb'];
			$m_supplier->alamat_kecamatan = $params['alamat_supplier']['kecamatan'];
			$m_supplier->alamat_kelurahan = $params['alamat_supplier']['kelurahan'];
			$m_supplier->alamat_rt = $params['alamat_supplier']['rt'] ?: null;
			$m_supplier->alamat_rw = $params['alamat_supplier']['rw'] ?: null;
			$m_supplier->alamat_jalan = $params['alamat_supplier']['alamat'] ?: null;
			$m_supplier->usaha_kecamatan = $params['alamat_usaha']['kecamatan'];
			$m_supplier->usaha_kelurahan = $params['alamat_usaha']['kelurahan'];
			$m_supplier->usaha_rt = $params['alamat_usaha']['rt'] ?: null;
			$m_supplier->usaha_rw = $params['alamat_usaha']['rw'] ?: null;
			$m_supplier->usaha_jalan = $params['alamat_usaha']['alamat'] ?: null;
			$m_supplier->status = $status;
			$m_supplier->mstatus = $mstatus;
			$m_supplier->tipe = 'supplier';
			$m_supplier->plafon = $params['plafon'];
			$m_supplier->jatuh_tempo = $params['jatuh_tempo'];
			$m_supplier->version = $version;
			$m_supplier->save();

			$deskripsi_log_supplier = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
			Modules::run( 'base/event/update', $m_supplier, $deskripsi_log_supplier );

			// telepon supplier
			$telepons = $params['telepons'];
			foreach ($telepons as $k => $telepon) {
				$m_telp = new \Model\Storage\TelpPelanggan_model();
				$m_telp->id = $m_telp->getNextIdentity();

				$m_telp->pelanggan = $supplier_id;
				$m_telp->nomor = $telepon;
				$m_telp->save();
				Modules::run( 'base/event/update', $m_telp, $deskripsi_log_supplier );
			}

			// rekening dan bank supplier
			$banks = $params['banks'];
			foreach ($banks as $k => $bank) {
				$m_bank = new \Model\Storage\BankPelanggan_model();
				$bank_plg_id = $m_bank->getNextIdentity();

				$m_bank->id = $bank_plg_id;
				$m_bank->pelanggan = $supplier_id;
				$m_bank->bank = $bank['nama_bank'];
				$m_bank->rekening_nomor = $bank['nomer_rekening'];
				$m_bank->rekening_pemilik = $bank['nama_pemilik'];
				$m_bank->rekening_cabang_bank = $bank['cabang_bank'];
				$m_bank->save();
				Modules::run( 'base/event/update', $m_telp, $deskripsi_log_supplier );
			}

			$this->result['status'] = 1;
			$this->result['message'] = 'Data supplier sukses di edit';
			$this->result['content'] = array('id' => $supplier_id);
		} catch (Exception $e) {
			$this->resuls['message'] = $e->getMessage();
		}

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

				$table = 'pelanggan';
				$table_id = $id;
				if ( stristr($lampiran['key'], 'bank') !== FALSE ) {
					$table = 'bank_pelanggan';

					$split_key = explode('_', $lampiran['key']);
					$bank = $split_key[1];
					$rekening_nomor = $split_key[2];

					$m_bank = new \Model\Storage\BankPelanggan_model();
					$d_bank = $m_bank->where('pelanggan', $id)->where('bank', $bank)->where('rekening_nomor', $rekening_nomor)->first();

					$table_id = $d_bank->id;
				}

				$file_name = $path_name = null;
				$isMoved = 0;
				if (!empty($files)) {
					$mappingFiles = mappingFiles($files);

					$file = null;
					if ( isset($lampiran['sha1']) && !empty($lampiran['sha1']) ) {
						$file = $mappingFiles[ $lampiran['sha1'] . '_' . $lampiran['name'] ] ?: '';
					}

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
			$this->result['message'] = 'Data supplier sukses disimpan';
			$this->result['content'] = array('id' => $id);
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

		display_json( $this->result );
	}

	public function ack() {
		$id = $this->input->post('params');

		$status = getStatus(2);

		$m_supplier = new \Model\Storage\Supplier_model();
		$m_supplier->where('id', $id)->update(
			array(
				'status' => $status
			)
		);

		$d_supplier = $m_supplier->where('id', $id)->first();

		$deskripsi_log_supplier = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
		Modules::run( 'base/event/save', $d_supplier, $deskripsi_log_supplier );

    	$this->result['status'] = 1;
      	$this->result['message'] = 'Data supplier sukses di ACK';
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

		$m_supplier = new \Model\Storage\Supplier_model();
		$ket = null;
		if ( $params['tipe'] == 'aktif' ) {
			$m_supplier->where('nomor', trim( $params['nomor'] ) )->where('tipe', 'supplier')
									   ->update(
									   		array(
									   			'mstatus' => 1
									   		)
									   	);

			$ket = 'aktifkan';
		} else {
			$m_supplier->where('nomor', trim( $params['nomor'] ) )->where('tipe', 'supplier')
									   ->update(
									   		array(
									   			'mstatus' => 0
									   		)
									   	);

			$ket = 'non aktifkan';
		}

		$d_supplier = $m_supplier->where('nomor', trim( $params['nomor'] ) )->orderBy('id', 'desc')->first();

		$deskripsi_log_supplier = 'di-'.$ket.' oleh ' . $this->userdata['detail_user']['nama_detuser'];
		Modules::run( 'base/event/update', $d_supplier, $deskripsi_log_supplier );

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
	    			$m_lampiran->tabel = 'pelanggan';
	    			$m_lampiran->tabel_id = $d_supplier['nomor'];
	    			$m_lampiran->nama_lampiran = $l['id'];
	    			$m_lampiran->filename = $file_name ;
	    			$m_lampiran->path = $path_name;
	    			$m_lampiran->save();
	    			Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log_supplier );

	    		}else {
	    			display_json(['status'=>0, 'message'=>'error, segera hubungi tim IT']);
	    		}
			}
		}

		$this->result['status'] = 1;
      	$this->result['message'] = 'Data supplier sukses diperbaharui';
      	display_json($this->result);
	}

	// public function loadFormInput($tipe = null) {
	// 	// loading list yang diperlukan
	// 	$content['akses'] = $this->getAkses();
	// 	$content['list_provinsi'] = $this->getLokasi('PV');
	// 	$content['list_lampiran_pelanggan'] = $this->getNamaLampiran("PELANGGAN", "KTP Pelanggan")->first();
	// 	$content['list_lampiran_usaha_pelanggan'] = $this->getNamaLampiran("PELANGGAN", "NPWP Pelanggan")->first();
	// 	$content['list_lampiran_rekening_pelanggan'] = $this->getNamaLampiran("BANK_PELANGGAN", "Rekening Pelanggan")->first();
	// 	$content['list_lampiran_dds_pelanggan'] = $this->getNamaLampiran("PELANGGAN", "DDP Pelanggan")->first();

	// 	if ($tipe == null) {
	// 		echo $this->load->view($this->pathView . 'input_pelanggan', $content, true);
	// 	} else {
	// 		return $this->load->view($this->pathView . 'input_pelanggan', $content, true);
	// 	}
	// }

	public function loadFormStatus() {
		$nomor = $this->input->get('params');

		$akses = hakAkses($this->url);

		$content['akses'] = $akses;
		$content['data_detail'] = $this->getDataForStatus($nomor);
		$html = $this->load->view($this->pathView . 'form_status_supplier', $content, true);

		echo $html;
	}

	public function getDataForStatus($nomor) {
		$m_supplier = new \Model\Storage\Supplier_model();
		$d_supplier = $m_supplier->where('nomor', $nomor)->where('tipe', 'supplier')->first();

		return $d_supplier;
	}

	public function loadFormSldAwal() {
		$nomor = $this->input->get('params');

		$akses = hakAkses($this->url);

		$content['akses'] = $akses;
		$content['data_detail'] = null;
		$html = $this->load->view($this->pathView . 'form_saldo_awal', $content, true);

		echo $html;
	}

	public function getListSupplier() {
		$m_supplier = new \Model\Storage\Supplier_model();
		$d_nomor = $m_supplier->select('nomor')->distinct('nomor')->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->get()->toArray();

		$datas = array();
		if ( !empty($d_nomor) ) {
			foreach ($d_nomor as $nomor) {
				$supplier = $m_supplier->where('tipe', 'supplier')
										  ->where('nomor', $nomor['nomor'])
										  ->where('jenis', '<>', 'ekspedisi')
										  ->orderBy('version', 'desc')
										  ->orderBy('id', 'desc')
										  ->with(['logs'])
										  ->first()->toArray();

				$ket = [];
				$keterangan = '';
		        foreach ($supplier['logs'] as $log){
					$keterangan = $log['deskripsi'] . ' pada ' . dateTimeFormat($log['waktu']);
					array_push($ket, $keterangan);
				}

				$m_lokasi = new \Model\Storage\Lokasi_model();
				$detail_lokasi = $m_lokasi->where('id', '=', $supplier['alamat_kecamatan'])->first();

				$detail_kota = $m_lokasi->where('id', '=', $detail_lokasi['induk'])->first();

				$data_array = array(
					'id' => $supplier['id'],
					'nip' => $supplier['nomor'],
					'nama' => $supplier['nama'],
					'nik' => $supplier['nik'],
					'alamat' => $detail_kota['nama'],
					'mstatus' => $supplier['mstatus'],
					'status' => $supplier['status'],
					'saldo_awal' => 0,
					'keterangan' => $keterangan,
					'jenis' => ($supplier['jenis'] != 'ekspedisi') ? 'supplier' : 'ekspedisi'
				);

				array_push($datas, $data_array);
			}
		}

		return $datas;
	}

	public function injekDariMgb()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select p1.* from pelanggan p1
			right join
				(select max(id) as id, nomor from pelanggan p where tipe = 'supplier' and jenis <> 'ekspedisi' and mstatus = 1 group by nomor) p2
				on
					p1.id = p2.id
			order by
				p1.nama asc
        ";
        $d_plg = $m_conf->hydrateRaw( $sql );

        if ( $d_plg->count() > 0 ) {
            $d_plg = $d_plg->toArray();

            foreach ($d_plg as $k_plg => $v_plg) {
				$m_plg = new \Model\Storage\Pelanggan_model();
				$d_plg = $m_plg->where('nomor', $v_plg['nomor'])->first();

				if ( !$d_plg ) {
					$status = "submit";
	
					// ekspedisi
					$m_plg = new \Model\Storage\Pelanggan_model();
					$plg_id = $m_plg->getNextIdentity();
	
					$m_plg->id = $plg_id;
					$m_plg->nomor = $v_plg['nomor'];
					$m_plg->jenis = $v_plg['jenis'];
					$m_plg->nama = $v_plg['nama'];
					$m_plg->cp = $v_plg['cp'];
					$m_plg->nik = $v_plg['nik'];
					$m_plg->alamat_kecamatan = $v_plg['alamat_kecamatan'];
					$m_plg->alamat_kelurahan = $v_plg['alamat_kelurahan'];
					$m_plg->alamat_rt = $v_plg['alamat_rt'];
					$m_plg->alamat_rw = $v_plg['alamat_rw'];
					$m_plg->alamat_jalan = $v_plg['alamat_jalan'];
					$m_plg->npwp = $v_plg['npwp'];
					$m_plg->usaha_kecamatan = $v_plg['usaha_kecamatan'];
					$m_plg->usaha_kelurahan = $v_plg['usaha_kelurahan'];
					$m_plg->usaha_rt = $v_plg['usaha_rt'];
					$m_plg->usaha_rw = $v_plg['usaha_rw'];
					$m_plg->usaha_jalan = $v_plg['usaha_jalan'];
					$m_plg->status = $v_plg['status'];
					$m_plg->mstatus = $v_plg['mstatus'];
					$m_plg->platform = $v_plg['platform'];
					$m_plg->version = $v_plg['version'];
					$m_plg->skb = $v_plg['skb'];
					$m_plg->tgl_habis_skb = $v_plg['tgl_habis_skb'];
					$m_plg->tipe = 'supplier';
					$m_plg->save();
	
					$m_conf = new \Model\Storage\Conf();
					$sql = "
						select tp.* from mgb_erp_live.dbo.telp_pelanggan tp
						where
							tp.pelanggan = ".$v_plg['id']."
					";
					$d_telp = $m_conf->hydrateRaw( $sql );
					if ( $d_telp->count() > 0 ) {
						$d_telp = $d_telp->toArray();
	
						foreach ($d_telp as $k_telp => $v_telp) {
							$m_telp = new \Model\Storage\TelpPelanggan_model();
							$m_telp->id = $m_telp->getNextIdentity();
							$m_telp->pelanggan = $plg_id;
							$m_telp->nomor = $v_telp['nomor'];
							$m_telp->save();
						}
					}
	
					$m_conf = new \Model\Storage\Conf();
					$sql = "
						select bp.* from mgb_erp_live.dbo.bank_pelanggan bp
						where
							bp.pelanggan = ".$v_plg['id']."
					";
					$d_bank = $m_conf->hydrateRaw( $sql );
					if ( $d_bank->count() > 0 ) {
						$d_bank = $d_bank->toArray();
	
						foreach ($d_bank as $k_bank => $v_bank) {
							$m_bank = new \Model\Storage\BankPelanggan_model();
							$bank_plg_id = $m_bank->getNextIdentity();
	
							$m_bank->id = $bank_plg_id;
							$m_bank->pelanggan = $plg_id;
							$m_bank->bank = $v_bank['bank'];
							$m_bank->rekening_nomor = $v_bank['rekening_nomor'];
							$m_bank->rekening_pemilik = $v_bank['rekening_pemilik'];
							$m_bank->rekening_cabang_bank = $v_bank['rekening_cabang_bank'];
							$m_bank->save();
						}
					}
	
					$d_plg = $m_plg->where('id', $plg_id)->with(['telepons', 'banks'])->first();
	
					$deskripsi_log = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
					Modules::run( 'base/event/save', $d_plg, $deskripsi_log );
				}
            }
        }
    }

	public function model($status)
    {
        if ( is_numeric($status) ) {
            $status = getStatus($status);
        }

        $m_supplier = new \Model\Storage\Supplier_model();
        $dashboard = $m_supplier->getDashboard($status);

        return $dashboard;
    }
}