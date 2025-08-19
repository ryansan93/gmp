<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Pelanggan extends Public_Controller {

	private $pathView = 'parameter/pelanggan/';
	private $url;
	private $isMobile = false;

	function __construct()
	{
		parent::__construct();
		$this->url = $this->current_base_uri;

		$this->load->library('Mobile_Detect');
        $detect = new Mobile_Detect();

        if ( $detect->isMobile() ) {
            $this->isMobile = true;
        }
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
				'assets/select2/js/select2.min.js',
				'assets/pagination-ry/pagination.js',
				'assets/parameter/pelanggan/js/pelanggan.js'
			));

			$this->add_external_css(array(
				'assets/jquery/easy-autocomplete/easy-autocomplete.min.css',
				'assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css',
				'assets/select2/css/select2.min.css',
				'assets/pagination-ry/pagination.css',
				'assets/parameter/pelanggan/css/pelanggan.css',
			));

			$data = $this->includes;

			$content['title_panel'] = 'Master Data Pelanggan';
			$content['akses'] = $akses;
			// $content['list_provinsi'] = $this->getLokasi('PV');
			// $content['list_lampiran_pelanggan'] = $this->getNamaLampiran("PELANGGAN")->first();
			// $content['list_lampiran_usaha_pelanggan'] = $this->getNamaLampiran("USAHA_PELANGGAN")->first();
			// $content['list_lampiran_rekening_pelanggan'] = $this->getNamaLampiran("REKENING_PELANGGAN")->first();
			// $content['list_lampiran_ddp_pelanggan'] = $this->getNamaLampiran("LAMPIRAN_PELANGGAN")->first();

			$content['add_form'] = $this->add_form();
			$content['isMobile'] = $this->isMobile;
            if ( $this->isMobile ) {
                $content['kecamatan'] = $this->getKecamatan();
                $content['pelanggan'] = $this->getPelanggan();
            }

			// load list pelanggan
			// $detail_content['pelanggans'] = $this->getListPelanggan();

			$data['title_menu'] = 'Master Pelanggan';
			$data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function getKecamatan() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
			select * from 
			(
				select
					l.*,
					REPLACE(REPLACE(induk_kec.nama, 'Kab ', ''), 'Kota ', '') as nama_header
				from lokasi l
				left join
					lokasi induk_kec
					on
						l.induk = induk_kec.id
				where 
					l.jenis = 'KC' and
					induk_kec.id is not null
			) data
			order by
				data.nama_header asc,
				data.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ($d_conf->count() > 0) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

	public function getPelanggan()
	{
		$m_conf = new \Model\Storage\Conf();
        $sql = "
			select 
				plg.*,
				REPLACE(REPLACE(kab_kota.nama, 'Kab ', ''), 'Kota ', '') as kab_kota
			from 
			(
				select plg1.* from pelanggan plg1
				right join
					(select max(id) as id, nomor from pelanggan where tipe = 'pelanggan' and jenis <> 'ekspedisi' group by nomor) plg2
					on
						plg1.id = plg2.id
			) plg
			left join
                lokasi kec
                on
                    kec.id = plg.usaha_kecamatan
            left join
                lokasi kab_kota
                on
                    kab_kota.id = kec.induk
			where
				plg.mstatus = 1
			order by
				plg.nama asc
    	";
        $d_conf = $m_conf->hydrateRaw($sql);

		$data = null;
		if ( $d_conf->count() > 0 ) {
			$data = $d_conf->toArray();
		}

		return $data;
	}

	public function getListMobile()
    {
        $params = $this->input->get('params');

        $sql = null;
		if ( isset($params['kecamatan']) && !empty($params['kecamatan']) ) {
            if ( empty( $sql ) ) {
                $sql = "and plg.usaha_kecamatan = '".$params['kecamatan']."'";
            } else {
                $sql .= "and plg.usaha_kecamatan = '".$params['kecamatan']."'";
            }
        }

        if ( isset($params['pelanggan']) && !empty($params['pelanggan']) ) {
            if ( empty( $sql ) ) {
                $sql = "and plg.nomor = '".$params['pelanggan']."'";
            } else {
                $sql .= "and plg.nomor = '".$params['pelanggan']."'";
            }
        }

        $m_pp = new \Model\Storage\MitraPosisi_model();
        $sql = "
            select 
				plg.*,
				REPLACE(REPLACE(kab_kota.nama, 'Kab ', ''), 'Kota ', '') as kab_kota,
				rs.max_tgl_ambil
			from 
			(
				select plg1.* from pelanggan plg1
				right join
					(select max(id) as id, nomor from pelanggan where tipe = 'pelanggan' and jenis <> 'ekspedisi' group by nomor) plg2
					on
						plg1.id = plg2.id
			) plg
			left join
                lokasi kec
                on
                    kec.id = plg.usaha_kecamatan
            left join
                lokasi kab_kota
                on
                    kab_kota.id = kec.induk
			left join
				(
					select
						drs.no_pelanggan,
						max(rs.tgl_panen) as max_tgl_ambil
					from det_real_sj drs 
					left join
						real_sj rs 
						on
							drs.id_header = rs.id
					group by
						drs.no_pelanggan
				) rs
				on
					rs.no_pelanggan = plg.nomor
			where
				plg.mstatus = 1
				".$sql."
			order by
				plg.nama asc
        ";
        $d_pp = $m_pp->hydrateRaw( $sql );

        $data = null;
        if ( $d_pp->count() > 0 ) {
            $data = $d_pp->toArray();
        }

        $content['isMobile'] = $this->isMobile;
        $content['data'] = $data;
        $html = $this->load->view('parameter/pelanggan/listMobile', $content, TRUE);

        echo $html;
    }

	public function detailMobile() {
        $params = $this->input->get('params');

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
				plg.*,
				REPLACE(REPLACE(kec.nama, 'Kab ', ''), 'Kota ', '') as nama_kecamatan,
				REPLACE(REPLACE(kab_kota.nama, 'Kab ', ''), 'Kota ', '') as kab_kota,
				REPLACE(REPLACE(kec_usaha.nama, 'Kab ', ''), 'Kota ', '') as nama_kecamatan_usaha,
				REPLACE(REPLACE(kab_kota_usaha.nama, 'Kab ', ''), 'Kota ', '') as kab_kota_usaha,
				rs.max_tgl_ambil,
				rs.ekor,
				rs.tonase
			from 
			(
				select plg1.* from pelanggan plg1
				right join
					(select max(id) as id, nomor from pelanggan where tipe = 'pelanggan' and jenis <> 'ekspedisi' group by nomor) plg2
					on
						plg1.id = plg2.id
			) plg
			left join
                lokasi kec
                on
                    kec.id = plg.alamat_kecamatan
            left join
                lokasi kab_kota
                on
                    kab_kota.id = kec.induk
			left join
                lokasi kec_usaha
                on
                    kec_usaha.id = plg.usaha_kecamatan
            left join
                lokasi kab_kota_usaha
                on
                    kab_kota_usaha.id = kec_usaha.induk
			left join
				(
					select
						drs.no_pelanggan,
						sum(drs.ekor) as ekor,
						sum(drs.tonase) as tonase,
						max(rs.tgl_panen) as max_tgl_ambil
					from det_real_sj drs 
					left join
						real_sj rs 
						on
							drs.id_header = rs.id
					group by
						drs.no_pelanggan
				) rs
				on
					rs.no_pelanggan = plg.nomor
			where
				plg.mstatus = 1
				and plg.nomor = '".$params['nomor']."'
			order by
				plg.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;

        $data_pelanggan = null;
        $alamat_pelanggan = null;
        $alamat_usaha = null;
        if ( $d_conf->count() > 0 ) {
            $data_pelanggan = $d_conf->toArray()[0];

            $jalan = empty($data_pelanggan['alamat_jalan']) ? '' : strtoupper('DSN.'.trim(str_replace('DSN.', '', $data_pelanggan['alamat_jalan'])));
            $rt = empty($data_pelanggan['alamat_rt']) ? '' : strtoupper(' RT.'.$data_pelanggan['alamat_rt']);
            $rw = empty($data_pelanggan['alamat_rw']) ? '' : strtoupper('/RW.'.$data_pelanggan['alamat_rw']);
            $kelurahan = empty($data_pelanggan['alamat_kelurahan']) ? '' : strtoupper(' ,'.$data_pelanggan['alamat_kelurahan']);
            $kecamatan = empty($data_pelanggan['nama_kecamatan']) ? '' : strtoupper(' ,'.$data_pelanggan['nama_kecamatan']);

            $alamat_pelanggan = $jalan.$rt.$rw.$kelurahan.$kecamatan;

			$jalan = empty($data_pelanggan['usaha_jalan']) ? '' : strtoupper('DSN.'.trim(str_replace('DSN.', '', $data_pelanggan['usaha_jalan'])));
            $rt = empty($data_pelanggan['usaha_rt']) ? '' : strtoupper(' RT.'.$data_pelanggan['usaha_rt']);
            $rw = empty($data_pelanggan['usaha_rw']) ? '' : strtoupper('/RW.'.$data_pelanggan['usaha_rw']);
            $kelurahan = empty($data_pelanggan['usaha_kelurahan']) ? '' : strtoupper(' ,'.$data_pelanggan['usaha_kelurahan']);
            $kecamatan = empty($data_pelanggan['nama_kecamatan_usaha']) ? '' : strtoupper(' ,'.$data_pelanggan['nama_kecamatan_usaha']);

            $alamat_usaha = $jalan.$rt.$rw.$kelurahan.$kecamatan;

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    *
                from telp_pelanggan tp
                where
                    tp.pelanggan = ".$data_pelanggan['id']."
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
    
            $data_telp = null;
            if ( $d_conf->count() > 0 ) {
                $data_telp = $d_conf->toArray();
            }
    
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    dk.*
                from daftar_kunjungan dk
                where
                    dk.no_pelanggan = '".$data_pelanggan['nomor']."'
				order by
					dk.tanggal desc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
    
            $data_kunjungan = null;
            if ( $d_conf->count() > 0 ) {
                $data_kunjungan = $d_conf->toArray();
            }
    
            $data = array(
                'nama' => $data_pelanggan['nama'],
                'alamat' => $alamat_pelanggan,
                'alamat_usaha' => $alamat_usaha,
                'ekor' => $data_pelanggan['ekor'],
                'tonase' => $data_pelanggan['tonase'],
                'max_tgl_ambil' => $data_pelanggan['max_tgl_ambil'],
                'telpon' => $data_telp,
                'kunjungan' => $data_kunjungan
            );
    
        }
        
        // cetak_r( $data, 1 );

        $content['data'] = $data;
        $html = $this->load->view('parameter/pelanggan/detailMobile', $content, TRUE);

        echo $html;
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

    public function amount_of_data()
    {
    	$search_by = $this->input->post('search_by');
        $search_val = $this->input->post('search_val');

        $m_plg = new \Model\Storage\Pelanggan_model();
        $d_nomor = null;

        $sql = "
    		select max(plg.id)
    		from
    			pelanggan plg
    		group by
    			plg.nomor
    	";

        $d_id = $m_plg->hydrateRaw($sql);
        if ( $d_id->count() > 0 ) {
        	$d_id = $d_id->toArray();

	        if ( !empty($search_by) && !empty($search_val) ) {
	            if ( stristr($search_by, 'nama') !== FALSE ) {
	            	$d_nomor = $m_plg->select('nomor', 'nama')->distinct('nomor')->where($search_by, 'like', '%'.$search_val.'%')->whereIn('id', $d_id)->where('tipe', 'pelanggan')->orderBy('nama', 'asc')->get()->toArray();
	            }
	        } else {
	        	$d_nomor = $m_plg->select('nomor', 'nama')->distinct('nomor')->whereIn('id', $d_id)->where('tipe', 'pelanggan')->orderBy('nama', 'asc')->get()->toArray();
	        }
        }


        $list_nomor = array();
        $jml_row = 25;
        $jml_page = 0;
        $idx_row = 0;
        if ( !empty($d_nomor) ) {
	        foreach ($d_nomor as $k_nomor => $v_nomor) {
	            if ( $idx_row == $jml_row ) {
	                $idx_row = 0;
	                $jml_page++;
	            }

	            $list_nomor[$jml_page][$idx_row] = trim($v_nomor['nomor']);

	            $idx_row++;
	        }
        }

        $this->result['content'] = array(
            'jml_row' => $jml_row,
            'jml_page' => count($list_nomor),
            'list' => $list_nomor
        );                     

        display_json( $this->result );
    }

    public function list_plg()
    {
    	$list_nomor = $this->input->get('params');

        $akses = hakAkses($this->url);

        $data = $this->getListPelanggan($list_nomor);

        $content['akses'] = $akses;
        $content['data'] = $data;

        $html = $this->load->view('parameter/pelanggan/list', $content);
        
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
		$content['list_lampiran_pelanggan'] = $this->getNamaLampiran("PELANGGAN", "KTP Pelanggan")->first();
		$content['list_lampiran_usaha_pelanggan'] = $this->getNamaLampiran("PELANGGAN", "NPWP Pelanggan")->first();
		$content['list_lampiran_rekening_pelanggan'] = $this->getNamaLampiran("BANK_PELANGGAN", "Rekening Pelanggan")->first();
		$content['list_lampiran_ddp_pelanggan'] = $this->getNamaLampiran("PELANGGAN", "DDP Pelanggan")->first();
        $content['data'] = null;
        $html = $this->load->view('parameter/pelanggan/add_form', $content, true);
        
        return $html;
    }

    public function edit_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        // mengambil data pelanggan
		$m_pelanggan = new \Model\Storage\Pelanggan_model();
		$d_pelanggan = $m_pelanggan->where('tipe', 'pelanggan')->where('id', $id)->with('telepons')->with('banks')->with('logs')->first();
		
		// mengambil lokasi
		$lokasi = new \Model\Storage\Lokasi_model();
		$kec = $lokasi->where('id', $d_pelanggan['alamat_kecamatan'])->first();
		$kota = $lokasi->where('id', $kec['induk'])->first();
		$prov = $lokasi->where('id', $kota['induk'])->first();
		$kec_usaha = $lokasi->where('id', $d_pelanggan['usaha_kecamatan'])->first();
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

		// mengambil lampiran pelanggan
		$m_nama_lampiran = new \Model\Storage\NamaLampiran_model;
		$d_nama_lampiran = $m_nama_lampiran->where('jenis', 'BANK_PELANGGAN')->first();

		$m_lampiran = new \Model\Storage\Lampiran_model;
		$d_lampiran = $m_lampiran->where('tabel', 'bank_pelanggan')->where('nama_lampiran', $d_nama_lampiran['id'])->get()->toArray();

		// cetak_r($d_pelanggan->toArray(), 1);

		$lampiran_ktp = $this->getLampiranPelanggan($d_pelanggan['id'], 'KTP Pelanggan');
		$lampiran_npwp = $this->getLampiranPelanggan($d_pelanggan['id'], 'NPWP Pelanggan');
		$lampiran_ddp = $this->getLampiranPelanggan($d_pelanggan['id'], 'DDP Pelanggan');

		$content['data'] = $d_pelanggan;
		$content['lokasi'] = $detail_lokasi;
		$content['l_ktp'] = $lampiran_ktp;
		$content['l_npwp'] = $lampiran_npwp;
		$content['l_ddp'] = $lampiran_ddp;
        $content['akses'] = $akses;

		$m_model = new \Model\Storage\Jenis_model();
        $d_jns = $m_model->getData();
        $content['jenis'] = !empty($d_jns) ? $d_jns : null;
        $content['list_provinsi'] = $this->getLokasi('PV');
		$content['list_lampiran_pelanggan'] = $this->getNamaLampiran("PELANGGAN", "KTP Pelanggan")->first();
		$content['list_lampiran_usaha_pelanggan'] = $this->getNamaLampiran("PELANGGAN", "NPWP Pelanggan")->first();
		$content['list_lampiran_rekening_pelanggan'] = $this->getNamaLampiran("BANK_PELANGGAN", "Rekening Pelanggan")->first();
		$content['list_lampiran_ddp_pelanggan'] = $this->getNamaLampiran("PELANGGAN", "DDP Pelanggan")->first();

        $html = $this->load->view('parameter/pelanggan/edit_form', $content, true);
        
        return $html;
    }

    public function view_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        // mengambil data pelanggan
		$m_pelanggan = new \Model\Storage\Pelanggan_model();
		$d_pelanggan = $m_pelanggan->where('tipe', 'pelanggan')->where('id', $id)->with(['d_jenis', 'telepons', 'banks', 'posisi', 'logs'])->first();
		
		// mengambil lokasi
		$lokasi = new \Model\Storage\Lokasi_model();
		$kec = $lokasi->where('id', $d_pelanggan['alamat_kecamatan'])->first();
		$kota = $lokasi->where('id', $kec['induk'])->first();
		$prov = $lokasi->where('id', $kota['induk'])->first();
		$kec_usaha = $lokasi->where('id', $d_pelanggan['usaha_kecamatan'])->first();
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
		$d_nama_lampiran = $m_nama_lampiran->where('jenis', 'BANK_PELANGGAN')->first();

		$m_lampiran = new \Model\Storage\Lampiran_model;
		$d_lampiran = $m_lampiran->where('tabel', 'bank_pelanggan')->where('nama_lampiran', $d_nama_lampiran['id'])->get();

		$lampiran_ktp = $this->getLampiranPelanggan($d_pelanggan['id'], 'KTP Pelanggan');
		$lampiran_npwp = $this->getLampiranPelanggan($d_pelanggan['id'], 'NPWP Pelanggan');
		$lampiran_ddp = $this->getLampiranPelanggan($d_pelanggan['id'], 'DDP Pelanggan');

		$content['data'] = !empty($d_pelanggan) ? $d_pelanggan->toArray() : null;
		$content['lokasi'] = $detail_lokasi;
		$content['l_ktp'] = $lampiran_ktp;
		$content['l_npwp'] = $lampiran_npwp;
		$content['l_ddp'] = $lampiran_ddp;
		$content['tbl_logs'] = $this->getLogs($d_pelanggan->nomor);
        $content['akses'] = $akses;

        $html = $this->load->view('parameter/pelanggan/view_form', $content, true);
        
        return $html;
    }

    public function getLogs($nomor = null) {
	    $m_plg = new \Model\Storage\Pelanggan_model;
    	$d_plg = $m_plg->where('nomor', $nomor)->where('tipe', 'pelanggan')->orderBy('version', 'asc')->get()->toArray();

    	$logs = array();
    	foreach ($d_plg as $key => $v_plg) {
	    	$m_log = new \Model\Storage\LogTables_model;
	    	$d_log = $m_log->select('deskripsi', 'waktu')->where('tbl_name', 'pelanggan')->where('tbl_id', $v_plg['id'])->get()->toArray();

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

    public function getLampiranPelanggan($id, $nama) {
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

		$data = $this->getLokasi($jenis, $induk);

		$this->result['content'] = $data;
		$this->result['status'] = 1;

		display_json($this->result);
	}

	public function save() {
		$params = $this->input->post('params');

		try {
			$status = "submit";
			// pelanggan
			$m_pelanggan = new \Model\Storage\Pelanggan_model();
			$pelanggan_id = $m_pelanggan->getNextIdentity();

			$kode_jenis = 'P';

			$no_pelanggan = $m_pelanggan->getNextNomor($kode_jenis);

			$m_pelanggan->id = $pelanggan_id;		
			$m_pelanggan->jenis = $params['jenis_pelanggan'];
			$m_pelanggan->nomor = $no_pelanggan;
			$m_pelanggan->nama = $params['nama'];
			$m_pelanggan->nik = $params['ktp'];
			$m_pelanggan->cp = $params['cp'];
			$m_pelanggan->npwp = $params['npwp'];
			$m_pelanggan->skb = $params['skb'];
			$m_pelanggan->tgl_habis_skb = $params['tgl_habis_skb'];
			$m_pelanggan->alamat_kecamatan = $params['alamat_pelanggan']['kecamatan'];
			$m_pelanggan->alamat_kelurahan = $params['alamat_pelanggan']['kelurahan'];
			$m_pelanggan->alamat_rt = $params['alamat_pelanggan']['rt'] ?: null;
			$m_pelanggan->alamat_rw = $params['alamat_pelanggan']['rw'] ?: null;
			$m_pelanggan->alamat_jalan = $params['alamat_pelanggan']['alamat'] ?: null;
			$m_pelanggan->usaha_kecamatan = $params['alamat_usaha']['kecamatan'];
			$m_pelanggan->usaha_kelurahan = $params['alamat_usaha']['kelurahan'];
			$m_pelanggan->usaha_rt = $params['alamat_usaha']['rt'] ?: null;
			$m_pelanggan->usaha_rw = $params['alamat_usaha']['rw'] ?: null;
			$m_pelanggan->usaha_jalan = $params['alamat_usaha']['alamat'] ?: null;
			$m_pelanggan->status = $status;
			$m_pelanggan->mstatus = 1;
			$m_pelanggan->tipe = 'pelanggan';
			$m_pelanggan->plafon = $params['plafon'];
			$m_pelanggan->jatuh_tempo = $params['jatuh_tempo'];
			$m_pelanggan->version = 1;
			$m_pelanggan->save();

			$deskripsi_log_pelanggan = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
			Modules::run( 'base/event/save', $m_pelanggan, $deskripsi_log_pelanggan );

			// telepon pelanggan
			$telepons = $params['telepons'];
			foreach ($telepons as $k => $telepon) {
				$m_telp = new \Model\Storage\TelpPelanggan_model();
				$m_telp->id = $m_telp->getNextIdentity();
				$m_telp->pelanggan = $pelanggan_id;
				$m_telp->nomor = $telepon;
				$m_telp->save();
				Modules::run( 'base/event/save', $m_telp, $deskripsi_log_pelanggan );
			}

			// rekening dan bank pelanggan
			$banks = $params['banks'];
			foreach ($banks as $k => $bank) {
				$m_bank = new \Model\Storage\BankPelanggan_model();
				$bank_plg_id = $m_bank->getNextIdentity();

				$m_bank->id = $bank_plg_id;
				$m_bank->pelanggan = $pelanggan_id;
				$m_bank->bank = $bank['nama_bank'];
				$m_bank->rekening_nomor = $bank['nomer_rekening'];
				$m_bank->rekening_pemilik = $bank['nama_pemilik'];
				$m_bank->rekening_cabang_bank = $bank['cabang_bank'];
				$m_bank->save();
				Modules::run( 'base/event/save', $m_telp, $deskripsi_log_pelanggan );
			}

			$m_sp = new \Model\Storage\SaldoPelanggan_model();
			$m_sp->jenis_saldo = 'D';
			$m_sp->no_pelanggan = $no_pelanggan;
			$m_sp->id_trans = NULL;
			$m_sp->tgl_trans = date('Y-m-d');
			$m_sp->jenis_trans = 'pembayaran_pelanggan';
			$m_sp->nominal = 0;
			$m_sp->saldo = 0;
			$m_sp->tgl_mulai_bayar = prev_date(date('Y-m-d'), 7);
			$m_sp->save();

			$this->result['status'] = 1;
			$this->result['message'] = 'Data pelanggan sukses disimpan';
			$this->result['content'] = array('id'=>$pelanggan_id);
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

    	display_json($this->result);
	}

	public function edit() {
		$params = $this->input->post('params');

		try {
			$pelanggan_id_old = $params['id'];
			$status = $params['status'];
			$mstatus = $params['mstatus'];
			$version = $params['version'] + 1;

			$m_pelanggan = new \Model\Storage\Pelanggan_model();
			$m_pelanggan->where('id', $pelanggan_id_old)->update(
				array(
					'mstatus' => 0
				)
			);

			// pelanggan
			$m_pelanggan = new \Model\Storage\Pelanggan_model();
			$pelanggan_id = $m_pelanggan->getNextIdentity();

			$m_pelanggan->id = $pelanggan_id;
			$m_pelanggan->jenis = $params['jenis_pelanggan'];
			$m_pelanggan->nomor = $params['nomor'];
			$m_pelanggan->nama = $params['nama'];
			$m_pelanggan->nik = $params['ktp'];
			$m_pelanggan->cp = $params['cp'];
			$m_pelanggan->npwp = $params['npwp'];
			$m_pelanggan->skb = $params['skb'];
			$m_pelanggan->tgl_habis_skb = $params['tgl_habis_skb'];
			$m_pelanggan->alamat_kecamatan = $params['alamat_pelanggan']['kecamatan'];
			$m_pelanggan->alamat_kelurahan = $params['alamat_pelanggan']['kelurahan'];
			$m_pelanggan->alamat_rt = $params['alamat_pelanggan']['rt'] ?: null;
			$m_pelanggan->alamat_rw = $params['alamat_pelanggan']['rw'] ?: null;
			$m_pelanggan->alamat_jalan = $params['alamat_pelanggan']['alamat'] ?: null;
			$m_pelanggan->usaha_kecamatan = $params['alamat_usaha']['kecamatan'];
			$m_pelanggan->usaha_kelurahan = $params['alamat_usaha']['kelurahan'];
			$m_pelanggan->usaha_rt = $params['alamat_usaha']['rt'] ?: null;
			$m_pelanggan->usaha_rw = $params['alamat_usaha']['rw'] ?: null;
			$m_pelanggan->usaha_jalan = $params['alamat_usaha']['alamat'] ?: null;
			$m_pelanggan->status = $status;
			$m_pelanggan->mstatus = $mstatus;
			$m_pelanggan->tipe = 'pelanggan';
			$m_pelanggan->plafon = $params['plafon'];
			$m_pelanggan->jatuh_tempo = $params['jatuh_tempo'];
			$m_pelanggan->version = $version;
			$m_pelanggan->save();

			$deskripsi_log_pelanggan = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
			Modules::run( 'base/event/update', $m_pelanggan, $deskripsi_log_pelanggan );

			// telepon pelanggan
			$telepons = $params['telepons'];
			foreach ($telepons as $k => $telepon) {
				$m_telp = new \Model\Storage\TelpPelanggan_model();
				$m_telp->id = $m_telp->getNextIdentity();

				$m_telp->pelanggan = $pelanggan_id;
				$m_telp->nomor = $telepon;
				$m_telp->save();
				Modules::run( 'base/event/update', $m_telp, $deskripsi_log_pelanggan );
			}

			// rekening dan bank pelanggan
			$banks = isset($params['banks']) ? $params['banks'] : null;
			if ( !empty($banks) ) {
				foreach ($banks as $k => $bank) {
					$m_bank = new \Model\Storage\BankPelanggan_model();
					$bank_plg_id = $m_bank->getNextIdentity();
		
					$m_bank->id = $bank_plg_id;
					$m_bank->pelanggan = $pelanggan_id;
					$m_bank->bank = $bank['nama_bank'];
					$m_bank->rekening_nomor = $bank['nomer_rekening'];
					$m_bank->rekening_pemilik = $bank['nama_pemilik'];
					$m_bank->rekening_cabang_bank = $bank['cabang_bank'];
					$m_bank->save();
					Modules::run( 'base/event/update', $m_telp, $deskripsi_log_pelanggan );
				}
			}

			$this->result['status'] = 1;
			$this->result['message'] = 'Data pelanggan sukses di edit';
			$this->result['content'] = array('id'=>$pelanggan_id);
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
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
			$this->result['message'] = 'Data pelanggan sukses disimpan';
			$this->result['content'] = array('id' => $id);
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

		display_json( $this->result );
	}

	public function ack() {
		$id = $this->input->post('params');

		$status = getStatus(2);

		$m_pelanggan = new \Model\Storage\Pelanggan_model();
		$m_pelanggan->where('id', $id)->update(
			array(
				'status' => $status
			)
		);

		$d_pelanggan = $m_pelanggan->where('id', $id)->first();

		$deskripsi_log_pelanggan = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
		Modules::run( 'base/event/save', $d_pelanggan, $deskripsi_log_pelanggan );

    	$this->result['status'] = 1;
      	$this->result['message'] = 'Data pelanggan sukses di ACK';
      	$this->result['content'] = array('id' => $id);

    	display_json($this->result);
	}

	public function nonAktif() {
		$params = json_decode($this->input->post('data'),TRUE);
		$files = isset($_FILES['files']) ? $_FILES['files'] : [];

		if ( !empty($files) ) {
			$mappingFiles = mappingFiles($files);
		}

		$m_pelanggan = new \Model\Storage\Pelanggan_model();
		$ket = null;
		if ( $params['tipe'] == 'aktif' ) {
			$m_pelanggan->where('nomor', trim( $params['nomor'] ) )->where('tipe', 'pelanggan')
									   ->update(
									   		array(
									   			'mstatus' => 1
									   		)
									   	);
			
			$ket = 'aktifkan';
		} else {
			$m_pelanggan->where('nomor', trim( $params['nomor'] ) )->where('tipe', 'pelanggan')
									   ->update(
									   		array(
									   			'mstatus' => 0
									   		)
									   	);

			$ket = 'non aktifkan';
		}

		$d_pelanggan = $m_pelanggan->where('nomor', trim( $params['nomor'] ) )->orderBy('id', 'desc')->first();

		$deskripsi_log_pelanggan = 'di-'.$ket.' oleh ' . $this->userdata['detail_user']['nama_detuser'];
		Modules::run( 'base/event/update', $d_pelanggan, $deskripsi_log_pelanggan );

		$lampirans = $params['lampiran'];
		if ( !empty($lampirans) ) {
			foreach ($lampirans as $l) {
				$file = $mappingFiles[ $l['sha1'] . '_' . $l['name'] ] ?: '';
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
	    			$m_lampiran->tabel_id = $d_pelanggan['id'];
	    			$m_lampiran->nama_lampiran = $l['id'];
	    			$m_lampiran->filename = $file_name ;
	    			$m_lampiran->path = $path_name;
	    			$m_lampiran->save();
	    			// Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log_pelanggan );

	    		}else {
	    			display_json(['status'=>0, 'message'=>'error, segera hubungi tim IT']);
	    		}
			}
		}

		$this->result['status'] = 1;
      	$this->result['message'] = 'Data pelanggan sukses diperbaharui';
      	display_json($this->result);
	}

	public function loadFormStatus() {
		$nomor = $this->input->get('params');

		$akses = hakAkses($this->url);

		$content['akses'] = $akses;
		$content['data_detail'] = $this->getDataForStatus($nomor);
		$html = $this->load->view($this->pathView . 'form_status_pelanggan', $content, true);

		echo $html;
	}

	public function getDataForStatus($nomor) {
		$m_pelanggan = new \Model\Storage\Pelanggan_model();
		$d_pelanggan = $m_pelanggan->where('nomor', $nomor)->where('tipe', 'pelanggan')->first();

		return $d_pelanggan;
	}

	public function loadFormSldAwal() {
		$nomor = $this->input->get('params');

		$akses = hakAkses($this->url);

		$content['akses'] = $akses;
		$content['data_detail'] = null;
		$html = $this->load->view($this->pathView . 'form_saldo_awal', $content, true);

		echo $html;
	}

	public function getListPelanggan($list_nomor) {
		$datas = array();

		if ( !empty($list_nomor) && $list_nomor != 'undifined' ) {
			foreach ($list_nomor as $nomor) {
				$m_pelanggan = new \Model\Storage\Pelanggan_model();
				$pelanggan = $m_pelanggan->where('tipe', 'pelanggan')
										  ->where('nomor', $nomor)
										  ->orderBy('id', 'desc')
										  ->first();

				if ( $pelanggan ) {
					$pelanggan = $pelanggan->toArray();

					$ket = [];
					$keterangan = '';

					$m_lt = new \Model\Storage\LogTables_model();
                    $d_lt = $m_lt->select('deskripsi', 'waktu')->where('tbl_name', 'pelanggan')->where('tbl_id', $pelanggan['id'])->get()->toArray();

			        foreach ($d_lt as $log){
						$keterangan = $log['deskripsi'] . ' pada ' . dateTimeFormat($log['waktu']);
						array_push($ket, $keterangan);
					}

					$m_lokasi = new \Model\Storage\Lokasi_model();
					$detail_lokasi = $m_lokasi->where('id', '=', $pelanggan['alamat_kecamatan'])->first();

					$detail_kota = $m_lokasi->where('id', '=', $detail_lokasi['induk'])->first();

					$key = $pelanggan['nama'].'|'.$pelanggan['nomor'];
					$datas[$key] = array(
						'id' => $pelanggan['id'],
						'nip' => $pelanggan['nomor'],
						'nama' => $pelanggan['nama'],
						'nik' => $pelanggan['nik'],
						'alamat' => $detail_kota['nama'],
						'mstatus' => $pelanggan['mstatus'],
						'status' => $pelanggan['status'],
						'saldo_awal' => 0,
						'keterangan' => $keterangan
					);

					ksort($datas);
				}
			}
		}

		return $datas;
	}

	public function form_export_excel()
    {
        $html = $this->load->view('parameter/pelanggan/form_export_excel', null); 
        
        echo $html;
    }

    public function verifikasi_export_excel()
    {
        $params = $this->input->post('params');

        $username = $params['username'];
        $password = $params['password'];

        $admins = $this->config->item('auth_export_excel')['auth_pelanggan'];

        if ( stristr($username, $admins[0]['user']) !== FALSE && $password == $admins[0]['pin'] ) {
            $this->result['status'] = 1;
        } else {
            $this->result['message'] = 'Username dan Password yang anda masukkan tidak cocok.';
        }

        display_json($this->result);
    }

	public function export_excel()
    {
        $m_pelanggan = new \Model\Storage\Pelanggan_model();
        $list_nomor = $m_pelanggan->select('nomor')->distinct('nomor')->where('tipe', 'pelanggan')->get()->toArray();

        $data = array();
        foreach ($list_nomor as $k_nomor => $nomor) {
            $pelanggan = $m_pelanggan->where('nomor', $nomor)
            				 ->where('tipe', 'pelanggan')
                             ->with(['kecamatan'])
                             ->orderBy('version', 'desc')
                             ->orderBy('id', 'desc')
                             ->first()->toArray();

            $jalan = empty($pelanggan['alamat_jalan']) ? '' : strtoupper('DSN.'.trim(str_replace('DSN.', '', $pelanggan['alamat_jalan'])));
            $rt = empty($pelanggan['alamat_rt']) ? '' : strtoupper(' RT.'.$pelanggan['alamat_rt']);
            $rw = empty($pelanggan['alamat_rw']) ? '' : strtoupper('/RW.'.$pelanggan['alamat_rw']);
            $kelurahan = empty($pelanggan['alamat_kelurahan']) ? '' : strtoupper(' ,'.$pelanggan['alamat_kelurahan']);
            $kecamatan = empty($pelanggan['alamat_kecamatan']) ? '' : strtoupper(' ,'.$pelanggan['kecamatan']['nama']);
            $kabupaten = empty($pelanggan['kecamatan']['d_kota']) ? '' : strtoupper(' ,'.$pelanggan['kecamatan']['d_kota']['nama']);
            $provinsi = empty($pelanggan['kecamatan']['d_kota']['d_provinsi']) ? '' : strtoupper(' ,'.$pelanggan['kecamatan']['d_kota']['d_provinsi']['nama']);

            $alamat = $jalan.$rt.$rw.$kelurahan.$kecamatan.$kabupaten.$provinsi;

            $key = $pelanggan['nama'].'|'.$pelanggan['nomor'];
            $data[ $key ] = array(
                'id' => $pelanggan['id'],
                'nomor' => $pelanggan['nomor'],
                'ktp' => $pelanggan['nik'],
                'npwp' => $pelanggan['npwp'],
                'nama' => $pelanggan['nama'],
                'alamat' => $alamat,
                'unit' => str_replace(' ,', '', $kabupaten),
                'status' => $pelanggan['mstatus']
            );

            ksort($data);
        }

        $content['data'] = $data;
        $res_view_html = $this->load->view('parameter/pelanggan/export_excel', $content, true);

        $filename = 'export-pelanggan-'.str_replace('-', '', date('Y-m-d')).'.xls';

        header("Content-type: application/xls");
        header("Content-Disposition: attachment; filename=".$filename."");
        echo $res_view_html;

        // cetak_r($data);
    }

	public function model($status)
    {
        if ( is_numeric($status) ) {
            $status = getStatus($status);
        }

        $m_pelanggan = new \Model\Storage\Pelanggan_model();
        $dashboard = $m_pelanggan->getDashboard($status);

        return $dashboard;
    }

    public function tes()
    {
    	$m_pelanggan = new \Model\Storage\Pelanggan_model();
    	$nomor = $m_pelanggan->getNextNomor('A');

    	cetak_r( $nomor );
    }

	public function injekDariMgb()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select plg1.* from mgb_erp_live.dbo.pelanggan plg1
            right join
                (select max(id) as id, nomor from mgb_erp_live.dbo.pelanggan where tipe <> 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                on
                    plg1.id = plg2.id
            where
                plg1.mstatus = 1
            order by
                plg1.nama asc
        ";
        $d_plg = $m_conf->hydrateRaw( $sql );

        if ( $d_plg->count() > 0 ) {
            $d_plg = $d_plg->toArray();

            foreach ($d_plg as $k_plg => $v_plg) {
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