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
		$m_kategori = new \Model\Storage\KategoriSupplier_model();
        $d_kategori = $m_kategori->orderBy('nama_kategori', 'asc')->get()->toArray();
        $content['kategori_supplier'] = !empty($d_kategori) ? $d_kategori : null;
		$m_bu = new \Model\Storage\BadanUsaha_model();
        $d_bu = $m_bu->getData();
        $content['badan_usaha'] = !empty($d_bu) ? $d_bu : null;
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
		$m_kategori = new \Model\Storage\KategoriSupplier_model();
        $d_kategori = $m_kategori->orderBy('nama_kategori', 'asc')->get()->toArray();
        $content['kategori_supplier'] = !empty($d_kategori) ? $d_kategori : null;
		$m_bu = new \Model\Storage\BadanUsaha_model();
        $d_bu = $m_bu->getData();
        $content['badan_usaha'] = !empty($d_bu) ? $d_bu : null;
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
		$d_supplier = $m_supplier->where('tipe', 'supplier')->where('id', $id)->with(['d_jenis', 'd_kategori_supplier', 'd_badan_usaha', 'telepons', 'banks', 'logs'])->first();
		
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
			$contacts = !empty($params['contacts']) ? $params['contacts'] : array();

			// supplier
			$m_supplier = new \Model\Storage\Supplier_model();
			$supplier_id = $m_supplier->getNextIdentity();

			$m_supplier->id = $supplier_id;
			$m_supplier->jenis = $params['jenis_supplier'];
			$m_supplier->kategori_supplier = !empty($params['kategori_supplier']) ? $params['kategori_supplier'] : null;
			$kode_jenis = 'S';

			$m_supplier->nomor = $m_supplier->getNextNomor($kode_jenis);
			$m_supplier->nama = $params['nama'];
			$m_supplier->nik = $params['ktp'];
			$m_supplier->cp = !empty($contacts) ? $contacts[0]['nama_cp'] : null;
			$m_supplier->npwp = $params['npwp'];
			$m_supplier->badan_usaha = !empty($params['badan_usaha']) ? $params['badan_usaha'] : null;
			$m_supplier->skb = !empty($params['skb']) ? $params['skb'] : null;
			$m_supplier->tgl_habis_skb = !empty($params['tgl_habis_skb']) ? $params['tgl_habis_skb'] : null;
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

			// contact person & telepon supplier
			foreach ($contacts as $k => $contact) {
				$m_telp = new \Model\Storage\TelpPelanggan_model();
				$m_telp->id = $m_telp->getNextIdentity();
				$m_telp->pelanggan = $supplier_id;
				$m_telp->nama_cp = !empty($contact['nama_cp']) ? $contact['nama_cp'] : null;
				$m_telp->nomor = $contact['telepon'];
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
			$contacts = !empty($params['contacts']) ? $params['contacts'] : array();

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
			$m_supplier->kategori_supplier = !empty($params['kategori_supplier']) ? $params['kategori_supplier'] : null;
			$m_supplier->nomor = $params['nomor'];
			$m_supplier->nama = $params['nama'];
			$m_supplier->nik = $params['ktp'];
			$m_supplier->cp = !empty($contacts) ? $contacts[0]['nama_cp'] : null;
			$m_supplier->npwp = $params['npwp'];
			$m_supplier->badan_usaha = !empty($params['badan_usaha']) ? $params['badan_usaha'] : null;
			$m_supplier->skb = !empty($params['skb']) ? $params['skb'] : null;
			$m_supplier->tgl_habis_skb = !empty($params['tgl_habis_skb']) ? $params['tgl_habis_skb'] : null;
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

			// contact person & telepon supplier
			foreach ($contacts as $k => $contact) {
				$m_telp = new \Model\Storage\TelpPelanggan_model();
				$m_telp->id = $m_telp->getNextIdentity();

				$m_telp->pelanggan = $supplier_id;
				$m_telp->nama_cp = !empty($contact['nama_cp']) ? $contact['nama_cp'] : null;
				$m_telp->nomor = $contact['telepon'];
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
            select p1.* from mgb_erp_live.dbo.pelanggan p1
			right join
				(select max(id) as id, nomor from mgb_erp_live.dbo.pelanggan p where tipe = 'supplier' and jenis <> 'ekspedisi' and mstatus = 1 group by nomor) p2
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

	// public function model($status)
    // {
    //     if ( is_numeric($status) ) {
    //         $status = getStatus($status);
    //     }

    //     $m_supplier = new \Model\Storage\Supplier_model();
    //     $dashboard = $m_supplier->getDashboard($status);

    //     return $dashboard;
    // }

	/**************************************************************************************
	 * CLONE SUPPLIER KE DATABASE GML
	 *
	 * Clone 1 data supplier (baris pelanggan versi terbaru dgn tipe='supplier' beserta
	 * struktur turunannya - telp_pelanggan, bank_pelanggan, lampiran + file fisiknya) dari
	 * database aplikasi ini ke database GML (perusahaan lain hasil fitur "Clone Perusahaan" -
	 * lihat base/CloneCompany.php). ID/nomor dipertahankan identik ke sumber; ditolak kalau
	 * nomor itu ternyata SUDAH ADA di GML (mencegah clone dobel/menimpa data orang lain).
	 *
	 * Pola & helper (dbConn/getGmlDbName/cloneTableRows/cloneLokasiChain/copyLampiranFiles)
	 * SENGAJA diduplikasi persis dari parameter/Peternak.php (bukan trait/base class bersama)
	 * - konsisten dgn konvensi codebase ini yg memang menduplikasi helper per controller
	 * (mis. getLokasi()/getNamaLampiran() ada salinannya sendiri2 di Peternak & Supplier).
	 * Detail teknis (kenapa pakai getConnection() bukan facade DB::, kenapa fetch mode
	 * array bukan object, dst) ada di komentar versi Peternak.php.
	 **************************************************************************************/

	public function cloneToGml()
	{
		$akses = hakAkses($this->url);
		if ( empty($akses['a_submit']) || $akses['a_submit'] != 1 ) {
			$this->result['message'] = 'Anda tidak memiliki akses untuk clone data supplier ke GML.';
			display_json($this->result);
			return;
		}

		$nomor = $this->input->post('nomor');

		try {
			if ( empty($nomor) ) {
				throw new Exception('Nomor supplier tidak boleh kosong.');
			}

			$gmlDb = $this->getGmlDbName();

			// NOTE: 1. ambil versi TERBARU pelanggan tipe='supplier' (konvensi max(version)/
			// max(id) per nomor - lihat getListSupplier() & edit() yg selalu bikin baris baru).
			$m_supplier = new \Model\Storage\Supplier_model();
			$sql = "
				SELECT p.* FROM pelanggan p
				RIGHT JOIN (
					SELECT MAX(id) AS id, nomor FROM pelanggan
					WHERE nomor = ? AND tipe = 'supplier'
					GROUP BY nomor
				) p2 ON p.id = p2.id
			";
			$d_supplier = $m_supplier->hydrateRaw($sql, array($nomor));
			if ( $d_supplier->count() == 0 ) {
				throw new Exception("Data supplier dengan nomor '{$nomor}' tidak ditemukan.");
			}
			$supplier = $d_supplier->first();
			$supplierId = $supplier->id;

			// NOTE: 2. guard - kalau nomor ini sudah ada di GML: tolak total kalau MASIH
			// AKTIF (mstatus=1), tapi kalau NONAKTIF (mis. sisa clone lama yg sudah di-non
			// aktifkan lagi di GML) tawarkan konfirmasi injek ulang (force=1) alih2 langsung
			// menolak - data lama di GML dihapus dulu (cascade) baru clone ulang.
			$force = $this->input->post('force');
			$existing = $this->dbConn()->selectOne("SELECT id, mstatus FROM [{$gmlDb}].dbo.[pelanggan] WHERE nomor = ? AND tipe = 'supplier'", array($nomor));
			if ( !empty($existing) ) {
				$isAktif = ( (int) $existing['mstatus'] === 1 );
				if ( $isAktif ) {
					throw new Exception("Supplier nomor {$nomor} sudah pernah di-clone ke GML (id GML #{$existing['id']}) dan masih aktif.");
				}
				if ( empty($force) ) {
					$this->result['status'] = 0;
					$this->result['need_confirm'] = 1;
					$this->result['message'] = "Supplier nomor {$nomor} sudah pernah di-clone ke GML (id GML #{$existing['id']}) tapi berstatus tidak aktif. Injek ulang? Data lama di GML akan dihapus & diganti data terbaru dari GMP.";
					display_json($this->result);
					return;
				}
				$this->deleteGmlSupplierCascade($gmlDb, $existing['id']);
			}

			// NOTE: 3. kumpulkan id bank_pelanggan (utk cascade lampiran).
			$bankIds = array_map(function($r){ return $r['id']; }, $this->dbConn()->select("SELECT id FROM bank_pelanggan WHERE pelanggan = ?", array($supplierId)));

			$report = array();

			// NOTE: 4. lookup pendukung - lokasi (kecamatan alamat pribadi + alamat usaha),
			// supaya alamat tidak tampil kosong di GML. kategori_supplier/jenis/badan_usaha
			// TIDAK perlu ditangani di sini krn sudah ikut allowlist Clone Perusahaan.
			$kecamatanIds = array($supplier->alamat_kecamatan, $supplier->usaha_kecamatan);
			$report['lokasi_baru'] = $this->cloneLokasiChain($kecamatanIds);

			// NOTE: 5. data inti supplier.
			$report['pelanggan'] = $this->cloneTableRows('pelanggan', 'id = ?', array($supplierId));
			$report['telp_pelanggan'] = $this->cloneTableRows('telp_pelanggan', 'pelanggan = ?', array($supplierId));
			$report['bank_pelanggan'] = $this->cloneTableRows('bank_pelanggan', 'pelanggan = ?', array($supplierId));

			// NOTE: 6. lampiran (pelanggan + tiap bank_pelanggan) + file fisiknya.
			$lampiranWhere = "(tabel = 'pelanggan' AND tabel_id = ?)";
			$lampiranBinds = array($supplierId);
			if ( !empty($bankIds) ) {
				$inBank = implode(',', array_map('intval', $bankIds));
				$lampiranWhere .= " OR (tabel = 'bank_pelanggan' AND tabel_id IN ({$inBank}))";
			}

			$lampiranRows = $this->dbConn()->select("SELECT * FROM lampiran WHERE {$lampiranWhere}", $lampiranBinds);
			$report['lampiran'] = $this->cloneTableRows('lampiran', $lampiranWhere, $lampiranBinds);
			$report['lampiran_file'] = $this->copyLampiranFiles($lampiranRows);

			// NOTE: 7. riwayat log_tables (kolom "Keterangan" di list Supplier dibaca dari
			// sini) + snapshot log_history (write-only, tidak ada fitur yg membacanya balik,
			// tetap di-clone atas permintaan eksplisit utk kelengkapan audit) + 1 catatan baru
			// langsung di GML sbg jejak provenance data hasil clone.
			$logIds = array_map(function($r){ return $r['id']; }, $this->dbConn()->select("SELECT id FROM log_tables WHERE tbl_name = 'pelanggan' AND tbl_id = ?", array($supplierId)));
			$report['log_tables'] = $this->cloneTableRows('log_tables', "tbl_name = 'pelanggan' AND tbl_id = ?", array($supplierId));
			$report['log_history'] = $this->cloneLogHistoryRows($logIds);

			$deskripsi_provenance = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
			$this->dbConn()->statement("
				INSERT INTO [{$gmlDb}].dbo.[log_tables] ([tbl_name],[tbl_id],[user_id],[waktu],[deskripsi],[_action])
				VALUES ('pelanggan', ?, ?, GETDATE(), ?, 'insert')
			", array($supplierId, $this->userid, $deskripsi_provenance));

			$this->result['status'] = 1;
			$this->result['message'] = "Data supplier {$supplier->nama} ({$nomor}) berhasil di-clone ke GML.";
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
	 * Hapus tuntas (cascade) 1 supplier beserta seluruh struktur turunannya LANGSUNG DI
	 * GML - dipakai guard cloneToGml() saat user pilih "injek ulang" utk baris GML yg
	 * statusnya sudah nonaktif. $supplierId di sini id versi GML yg mau dihapus (BUKAN id
	 * sumber - bisa beda kalau ada edit lanjutan di sumber sejak clone pertama). log_history
	 * TIDAK ikut dibersihkan (write-only, orphan row di situ tidak berdampak apa2 - lihat
	 * catatan cloneLogHistoryRows()).
	 */
	private function deleteGmlSupplierCascade($gmlDb, $supplierId)
	{
		$bankIds = array_map(function($r){ return $r['id']; }, $this->dbConn()->select("SELECT id FROM [{$gmlDb}].dbo.[bank_pelanggan] WHERE pelanggan = ?", array($supplierId)));

		$lampiranWhere = "(tabel = 'pelanggan' AND tabel_id = ?)";
		$lampiranBinds = array($supplierId);
		if ( !empty($bankIds) ) {
			$inBank = implode(',', array_map('intval', $bankIds));
			$lampiranWhere .= " OR (tabel = 'bank_pelanggan' AND tabel_id IN ({$inBank}))";
		}
		$this->dbConn()->statement("DELETE FROM [{$gmlDb}].dbo.[lampiran] WHERE {$lampiranWhere}", $lampiranBinds);
		$this->dbConn()->statement("DELETE FROM [{$gmlDb}].dbo.[log_tables] WHERE tbl_name = 'pelanggan' AND tbl_id = ?", array($supplierId));

		$this->dbConn()->statement("DELETE FROM [{$gmlDb}].dbo.[bank_pelanggan] WHERE pelanggan = ?", array($supplierId));
		$this->dbConn()->statement("DELETE FROM [{$gmlDb}].dbo.[telp_pelanggan] WHERE pelanggan = ?", array($supplierId));
		$this->dbConn()->statement("DELETE FROM [{$gmlDb}].dbo.[pelanggan] WHERE id = ?", array($supplierId));
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
			throw new Exception("Database GML harus berada di SQL Server yang sama dengan database utama untuk fitur clone-per-supplier ini.");
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