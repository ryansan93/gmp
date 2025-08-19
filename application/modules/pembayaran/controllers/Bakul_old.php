<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Bakul extends Public_Controller
{
	private $url;
	private $hakAkses;

	function __construct()
	{
		parent::__construct();
		$this->url = $this->current_base_uri;
		$this->hakAkses = hakAkses($this->url);
	}

	public function index( $key_rm = null )
	{
		if ( $this->hakAkses['a_view'] == 1 ) {
			$this->add_external_js(array(
				"assets/jquery/list.min.js",
				'assets/select2/js/select2.min.js',
				'assets/pembayaran/bakul/js/bakul.js'
			));
			$this->add_external_css(array(
				'assets/select2/css/select2.min.css',
				'assets/pembayaran/bakul/css/bakul.css'
			));

			$data = $this->includes;

			$data['title_menu'] = 'Pembayaran Bakul';

            $data_rm = $this->getDataRm( $key_rm );

			$content['rm'] = !empty($data_rm) ? 1 : 0;
			$content['add_form'] = $this->add_form( $data_rm );
			$content['akses'] = $this->hakAkses;
			$data['view'] = $this->load->view('pembayaran/bakul/index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

    public function getDataRm( $key ) {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select rm.* from rekening_masuk rm
            where
                rm.kode = '".exDecrypt($key)."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray()[0];
        }

        return $data;
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

    public function get_unit()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'UN')->orderBy('nama', 'asc')->get();

        $data = null;
        if ( $d_wilayah->count() > 0 ) {
            $d_wilayah = $d_wilayah->toArray();

            foreach ($d_wilayah as $k_wil => $v_wil) {
                $nama = trim(str_replace('KAB ', '', str_replace('KOTA ', '', strtoupper($v_wil['nama']))));
                $data[ $nama.' - '.$v_wil['kode'] ] = array(
                    'nama' => $nama,
                    'kode' => $v_wil['kode']
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function get_perusahaan()
    {
        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $kode_perusahaan = $m_perusahaan->select('kode')->distinct('kode')->get();

        $data = null;
        if ( $kode_perusahaan->count() > 0 ) {
            $kode_perusahaan = $kode_perusahaan->toArray();

            foreach ($kode_perusahaan as $k => $val) {
                $m_perusahaan = new \Model\Storage\Perusahaan_model();
                $d_perusahaan = $m_perusahaan->where('kode', $val['kode'])->orderBy('version', 'desc')->first();

                $key = strtoupper($d_perusahaan->perusahaan).' - '.$d_perusahaan['kode'];
                $data[ $key ] = array(
                    'nama' => strtoupper($d_perusahaan->perusahaan),
                    'kode' => $d_perusahaan->kode,
                    'jenis_mitra' => $d_perusahaan->jenis_mitra
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function getJenisMitra()
    {
        $jenis_mitra = $this->config->item('jenis_mitra');
        return $jenis_mitra;
    }

    public function modalPilihDN()
    {
        $params = $this->input->get('params');

        $pelanggan = $params['pelanggan'];
        $sql_pelanggan = null;
        if ( !empty($pelanggan) ) {
            $sql_pelanggan = "and d.pelanggan = '".$pelanggan."'";
        }
        $id = (isset($params['id']) && !empty($params['id'])) ? $params['id'] : null;
        $sql_id = null;
        if ( !empty($id) ) {
            $sql_id = "where id_header <> ".$id;
        }

        $data = null;
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                d.id,
                d.nomor,
                d.tanggal,
                d.ket_dn as keterangan,
                (d.tot_dn - isnull(rpd.pakai, 0)) as saldo
            from dn d
            left join
                (
                    select
                        sum(pakai) as pakai, id_dn
                    from
                    (
                        select sum(pakai) as pakai, id_dn from realisasi_pembayaran_dn ".$sql_id." group by id_dn

                        union all

                        select sum(pakai) as pakai, id_dn from bayar_peralatan_dn group by id_dn
                    ) rpd
                    group by
                        rpd.id_dn
                ) rpd
                on
                    d.id = rpd.id_dn
            where
                d.nomor like '%LB%' and
                (d.tot_dn - isnull(rpd.pakai, 0)) > 0
                ".$sql_pelanggan."
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $_key = str_replace('-', '', $value['tanggal']).' | '.$value['nomor'];

                $data[ $_key ] = $value;
            }
        }

        if ( !empty( $data ) ) {
            ksort( $data );
        }

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/bakul/modal_pilih_dn', $content, true);

        echo $html;
    }

    public function modalPilihCN()
    {
        $params = $this->input->get('params');

        $pelanggan = $params['pelanggan'];
        $sql_pelanggan = null;
        if ( !empty($pelanggan) ) {
            $sql_pelanggan = "and c.pelanggan = '".$pelanggan."'";
        }
        $id = (isset($params['id']) && !empty($params['id'])) ? $params['id'] : null;
        $sql_id = null;
        if ( !empty($id) ) {
            $sql_id = "where id_header <> ".$id;
        }

        $data = null;
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                c.id,
                c.nomor,
                c.tanggal,
                c.ket_cn as keterangan,
                (c.tot_cn - isnull(rpc.pakai, 0)) as saldo
            from cn c
            left join
                (
                    select
                        sum(isnull(pakai, 0)) as pakai, id_cn
                    from
                    (
                        select sum(pakai) as pakai, id_cn from realisasi_pembayaran_cn ".$sql_id." group by id_cn

                        union all

                        select sum(pakai) as pakai, id_cn from bayar_peralatan_cn group by id_cn
                    ) rpc
                    group by
                        rpc.id_cn
                ) rpc
                on
                    c.id = rpc.id_cn
            where
                c.nomor like '%LB%' and
                (c.tot_cn - isnull(rpc.pakai, 0)) > 0
                ".$sql_pelanggan."
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $_key = str_replace('-', '', $value['tanggal']).' | '.$value['nomor'];

                $data[ $_key ] = $value;
            }
        }

        if ( !empty( $data ) ) {
            ksort( $data );
        }

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/bakul/modal_pilih_cn', $content, true);

        echo $html;
    }

    public function add_form( $data_umb = null )
    {
        $d_content['akses'] = $this->hakAkses;
    	$d_content['unit'] = $this->get_unit();
    	$d_content['perusahaan'] = $this->get_perusahaan();
    	$d_content['pelanggan'] = $this->get_pelanggan();
        $d_content['jenis_mitra'] = $this->getJenisMitra();
        $d_content['data_umb'] = $data_umb;
		$html = $this->load->view('pembayaran/bakul/add_form', $d_content, true);

		return $html;
    }

    public function view_form($id)
    {
    	$m_pp = new \Model\Storage\PembayaranPelanggan_model();
    	$d_pp = $m_pp->where('id', $id)->with(['detail', 'pelanggan', 'logs', 'perusahaan'])->first();

    	$data = null;
    	if ( $d_pp ) {
    		$d_pp = $d_pp->toArray();

    		$_m_pp = new \Model\Storage\PembayaranPelanggan_model();
            $d_pp_before = $_m_pp->select('id')->where('no_pelanggan', $d_pp['no_pelanggan'])->where('perusahaan', $d_pp['perusahaan']['kode'])->where('id', '<', $id)->get();
    		$d_pp_next = $_m_pp->select('id')->where('no_pelanggan', $d_pp['no_pelanggan'])->where('perusahaan', $d_pp['perusahaan']['kode'])->where('id', '>', $id)->first();

    		$data_before = null;
    		if ( $d_pp_before->count() > 0 ) {
	    		$data_before = $d_pp_before->toArray();
	    	}

            $edit = 1;
            if ( $d_pp_next ) {
                $edit = 0;
            }

    		$detail = null;
    		foreach ($d_pp['detail'] as $k_det => $v_det) {
    			$sudah_bayar = 0;
    			if ( !empty($data_before) ) {$m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
	    			$d_dpp = $m_dpp->whereIn('id_header', $data_before)->where('id_do', $v_det['id_do'])->get();

	    			if ( $d_dpp->count() > 0 ) {
	    				$sudah_bayar = $d_dpp->sum('jumlah_bayar');
	    			}
	    		}

                $m_rs = new \Model\Storage\RealSJ_model();
                $sql = "
                    select rsj.noreg, m.nama, drs.* from det_real_sj drs
                    right join
                        real_sj rsj
                        on
                            drs.id_header = rsj.id 
                    right join
                        rdim_submit rs
                        on
                            rsj.noreg = rs.noreg 
                    right join
                        (
                            select mm1.* from mitra_mapping mm1
                            right join
                                (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                on
                                    mm1.id = mm2.id
                        ) mm
                        on
                            rs.nim = mm.nim
                    right join
                        mitra m
                        on
                            mm.mitra = m.id
                    where
                        drs.id = ".$v_det['id_do']."
                ";
                $d_rs = $m_rs->hydrateRaw( $sql );

                if ( $d_rs->count() > 0 ) {
                    $d_rs = $d_rs->toArray();

        			$detail[ $v_det['id'] ] = array(
        				'id' => $v_det['id'],
    					'id_header' => $v_det['id_header'],
    					'id_do' => $v_det['id_do'],
    					'total_bayar' => $v_det['total_bayar'],
    					'jumlah_bayar' => $v_det['jumlah_bayar'],
    					'penyesuaian' => $v_det['penyesuaian'],
    					'ket_penyesuaian' => $v_det['ket_penyesuaian'],
    					'status' => $v_det['status'],
    					'data_do' => $v_det['data_do'],
    					'sudah_bayar' => $sudah_bayar,
                        'nama' => $d_rs[0]['nama'],
                        'kandang' => substr($d_rs[0]['noreg'], -2)
        			);
                }
    		}
    		$data = array(
				'id' => $d_pp['id'],
				'no_pelanggan' => $d_pp['no_pelanggan'],
				'tgl_bayar' => $d_pp['tgl_bayar'],
				'urut_tf' => $d_pp['urut_tf'],
				'kode_umb' => $d_pp['kode_umb'],
				'jml_transfer' => $d_pp['jml_transfer'],
				'saldo' => $d_pp['saldo'],
				'nil_pajak' => $d_pp['nil_pajak'],
				'non_saldo' => $d_pp['non_saldo'],
				'total_uang' => $d_pp['total_uang'],
				'total_penyesuaian' => $d_pp['total_penyesuaian'],
				'total_bayar' => $d_pp['total_bayar'],
				'lebih_kurang' => $d_pp['lebih_kurang'],
				'lampiran_transfer' => $d_pp['lampiran_transfer'],
				'pelanggan' => $d_pp['pelanggan'],
                'logs' => $d_pp['logs'],
                'perusahaan' => $d_pp['perusahaan']['perusahaan'],
				'edit' => $edit,
				'detail' => $detail
    		);
    	}

    	$d_content['data'] = $data;
    	$d_content['akses'] = $this->hakAkses;
		$html = $this->load->view('pembayaran/bakul/view_form', $d_content, true);

		return $html;
    }

    public function edit_form($id)
    {
    	$m_pp = new \Model\Storage\PembayaranPelanggan_model();
    	$d_pp = $m_pp->where('id', $id)->with(['detail', 'pelanggan'])->first();

        $data = null;
        $kode_unit = null;
    	$kode_perusahaan = null;
    	if ( $d_pp ) {
    		$d_pp = $d_pp->toArray();

            $m_sp = new \Model\Storage\SaldoPelanggan_model();
            $d_sp = $m_sp->where('no_pelanggan', $d_pp['no_pelanggan'])->where('id_trans', $id)->first();

            $kode_perusahaan = $d_sp->perusahaan;

    		$_m_pp = new \Model\Storage\PembayaranPelanggan_model();
    		$d_pp_before = $_m_pp->select('id')->where('no_pelanggan', $d_pp['no_pelanggan'])->where('id', '<', $id)->get();

    		$data_before = null;
    		if ( $d_pp_before->count() > 0 ) {
	    		$data_before = $d_pp_before->toArray();
	    	}

    		$detail = null;
    		foreach ($d_pp['detail'] as $k_det => $v_det) {
    			$sudah_bayar = 0;
    			if ( !empty($data_before) ) {
	    			$m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
	    			$d_dpp = $m_dpp->whereIn('id_header', $data_before)->where('id_do', $v_det['id_do'])->get();

	    			if ( $d_dpp->count() > 0 ) {
	    				$sudah_bayar = $d_dpp->sum('jumlah_bayar');
	    			}
	    		}

                $m_rs = new \Model\Storage\RealSJ_model();
                $sql = "
                    select rsj.noreg, m.nama, drs.* from det_real_sj drs
                    right join
                        real_sj rsj
                        on
                            drs.id_header = rsj.id 
                    right join
                        rdim_submit rs
                        on
                            rsj.noreg = rs.noreg 
                    right join
                        (
                            select mm1.* from mitra_mapping mm1
                            right join
                                (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                on
                                    mm1.id = mm2.id
                        ) mm
                        on
                            rs.nim = mm.nim
                    right join
                        mitra m
                        on
                            mm.mitra = m.id
                    where
                        drs.id = ".$v_det['id_do']."
                ";
                $d_rs = $m_rs->hydrateRaw( $sql );

                if ( $d_rs->count() > 0 ) {
                    $d_rs = $d_rs->toArray();

        			$detail[ $v_det['id'] ] = array(
        				'id' => $v_det['id'],
    					'id_header' => $v_det['id_header'],
    					'id_do' => $v_det['id_do'],
    					'total_bayar' => $v_det['total_bayar'],
    					'jumlah_bayar' => $v_det['jumlah_bayar'],
    					'penyesuaian' => $v_det['penyesuaian'],
    					'ket_penyesuaian' => $v_det['ket_penyesuaian'],
    					'status' => $v_det['status'],
    					'data_do' => $v_det['data_do'],
    					'sudah_bayar' => $sudah_bayar,
                        'nama' => $d_rs[0]['nama'],
                        'kandang' => substr($d_rs[0]['noreg'], -2)
        			);

                    $kode_unit[] = substr($v_det['data_do']['no_do'], 3, 3);
                }
    		}
    		$data = array(
				'id' => $d_pp['id'],
				'no_pelanggan' => $d_pp['no_pelanggan'],
				'tgl_bayar' => $d_pp['tgl_bayar'],
				'jml_transfer' => $d_pp['jml_transfer'],
				'saldo' => $d_pp['saldo'],
                'nil_pajak' => $d_pp['nil_pajak'],
                'non_saldo' => $d_pp['non_saldo'],
				'total_uang' => $d_pp['total_uang'],
				'total_penyesuaian' => $d_pp['total_penyesuaian'],
				'total_bayar' => $d_pp['total_bayar'],
				'lebih_kurang' => $d_pp['lebih_kurang'],
				'lampiran_transfer' => $d_pp['lampiran_transfer'],
				'pelanggan' => $d_pp['pelanggan'],
				'detail' => $detail
    		);
    	}

        $d_content['kode_unit'] = $kode_unit;
        $d_content['kode_perusahaan'] = $kode_perusahaan;
    	$d_content['unit'] = $this->get_unit();
        $d_content['perusahaan'] = $this->get_perusahaan();
        $d_content['pelanggan'] = $this->get_pelanggan();
    	$d_content['data'] = $data;
		$html = $this->load->view('pembayaran/bakul/edit_form', $d_content, true);

		return $html;
    }

	public function get_list_pembayaran()
	{
		$params = $this->input->post('params');

        $data = null;

		$start_date = $params['start_date'];
		$end_date = $params['end_date'];

		$m_pp = new \Model\Storage\PembayaranPelanggan_model();
        $sql = "
            select 
                pp.id,
                pp.tgl_bayar,
                pp.jml_transfer,
                pp.lampiran_transfer,
                prs.perusahaan as nama_perusahaan,
                plg.nama as nama_pelanggan,
                lt.deskripsi,
                lt.waktu
            from pembayaran_pelanggan pp
            right join
                (
                    select p1.* from pelanggan p1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'pelanggan' group by nomor) p2
                        on
                            p1.id = p2.id
                ) plg
                on
                    pp.no_pelanggan = plg.nomor
            left join
                (
                    select p1.* from perusahaan p1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) p2
                        on
                            p1.id = p2.id
                ) prs
                on
                    pp.perusahaan = prs.kode
            left join
                (
                    select lt1.* from log_tables lt1
                    right join
                        (select max(id) as id, tbl_name, tbl_id from log_tables where tbl_name = 'pembayaran_pelanggan' group by tbl_name, tbl_id) lt2
                        on
                            lt1.id = lt2.id
                ) lt
                on
                    pp.id = lt.tbl_id
            where
                pp.tgl_bayar between '".$start_date."' and '".$end_date."' and
                (pp.bad_debt is null or pp.bad_debt = 0)
        ";
        $d_pp = $m_pp->hydrateRaw( $sql );

        if ( $d_pp->count() > 0 ) {
            $d_pp = $d_pp->toArray();

            foreach ($d_pp as $k_pp => $v_pp) {
                $key = strtotime($v_pp['waktu']).'-'.str_replace('-', '', $v_pp['tgl_bayar']).'-'.$v_pp['nama_perusahaan'].'-'.$v_pp['id'];

                $log = array(
                    'waktu' => $v_pp['waktu'],
                    'deskripsi' => $v_pp['deskripsi']
                );

                $data[$key] = array(
                    'id' => $v_pp['id'],
                    'tgl_bayar' => $v_pp['tgl_bayar'],
                    'perusahaan' => $v_pp['nama_perusahaan'],
                    'pelanggan' => $v_pp['nama_pelanggan'],
                    'jml_transfer' => $v_pp['jml_transfer'],
                    'lampiran_transfer' => $v_pp['lampiran_transfer'],
                    'log' => $log
                );

                krsort($data);
            }
        }

		// $d_pp = $m_pp->whereBetween('tgl_bayar', [$start_date, $end_date])->with(['perusahaan', 'pelanggan', 'logs'])->get();

		// if ( $d_pp->count() ) {
		// 	$d_pp = $d_pp->toArray();
		// 	foreach ($d_pp as $k_pp => $v_pp) {
		// 		$key = strtotime($v_pp['logs'][ count($v_pp['logs']) - 1 ]['waktu']).'-'.str_replace('-', '', $v_pp['tgl_bayar']).'-'.$v_pp['perusahaan']['perusahaan'].'-'.$v_pp['id'];
		// 		$data[$key] = array(
		// 			'id' => $v_pp['id'],
		// 			'tgl_bayar' => $v_pp['tgl_bayar'],
		// 			'perusahaan' => $v_pp['perusahaan']['perusahaan'],
		// 			'pelanggan' => $v_pp['pelanggan']['nama'],
		// 			'jml_transfer' => $v_pp['jml_transfer'],
		// 			'lampiran_transfer' => $v_pp['lampiran_transfer'],
  //                   'log' => $v_pp['logs'][ count($v_pp['logs']) - 1 ]
		// 		);
		// 	}

  //           krsort($data);
		// }

		$content['data'] = $data;
		$content['akses'] = $this->hakAkses;
		$html = $this->load->view('pembayaran/bakul/list_pembayaran', $content, true);

		$this->result['status'] = 1;
		$this->result['html'] = $html;

		display_json( $this->result );
	}

	public function get_pelanggan()
	{
		$data = null;

        $m_plg = new \Model\Storage\Pelanggan_model();
        $sql = "
            select
                p.*,
                kab_kota.nama as kab_kota
            from pelanggan p
            right join
                ( select max(id) as id, nomor from pelanggan where tipe='pelanggan' group by nomor ) p1
                on
                    p.id = p1.id
            right join
                lokasi kec
                on
                    kec.id = p.alamat_kecamatan
            right join
                lokasi kab_kota
                on
                    kab_kota.id = kec.induk
            where
                p.mstatus = 1 and
                p.tipe = 'pelanggan'
        ";
        $d_plg = $m_plg->hydrateRaw( $sql );
        if ( $d_plg->count() > 0 ) {
            $d_plg = $d_plg->toArray();

            foreach ($d_plg as $k_plg => $v_plg) {
                $kota_kab = str_replace('Kota ', '', str_replace('Kab ', '', $v_plg['kab_kota']));
                $key = $v_plg['nama'].'|'.$v_plg['nomor'];
                $data[$key] = $v_plg;
                $data[$key]['kab_kota'] = $kota_kab;

                ksort($data);
            }
        }

		return $data;
	}

	public function get_list_do()
	{
        $id = ($this->input->post('id') != null) ? $this->input->post('id') : null;
		$pelanggan = $this->input->post('pelanggan');
		$_unit = $this->input->post('unit');
		$tgl_bayar = $this->input->post('tgl_bayar');
        $perusahaan = $this->input->post('perusahaan');
        $jenis_mitra = $this->input->post('jenis_mitra');

        $id_unit = array();
        if ( !empty( $_unit ) ) {
            foreach ($_unit as $k_ku => $v_ku) {
                if ( stristr($v_ku, 'all') !== FALSE ) {
                    $m_wil = new \Model\Storage\Wilayah_model();
                    $d_wil = $m_wil->where('jenis', 'UN')->orderBy('nama', 'asc')->get()->toArray();

                    foreach ($d_wil as $k_wil => $v_wil) {
                        $id_unit[] = $v_wil['id'];
                    }

                    break;
                } else {
                    $m_wil = new \Model\Storage\Wilayah_model();
                    $d_wil = $m_wil->where('kode', $v_ku)->where('jenis', 'UN')->orderBy('nama', 'asc')->get()->toArray();

                    foreach ($d_wil as $k_wil => $v_wil) {
                        $id_unit[] = $v_wil['id'];
                    }
                }
            }
        }

		$data = null;

		$m_sp = new \Model\Storage\SaldoPelanggan_model();
		$d_sp_by_perusahaan = $m_sp->where('no_pelanggan', $pelanggan)->where('perusahaan', $perusahaan)->orderBy('id', 'desc')->first();

		$saldo = 0;
		if ( $d_sp_by_perusahaan ) {
			$saldo = $d_sp_by_perusahaan->saldo;
		}

		$no_pelanggan = $pelanggan;

        // $m_pelanggan = new \Model\Storage\Pelanggan_model();
        // $d_pelanggan = $m_pelanggan->where('nomor', $no_pelanggan)->where('tipe', 'pelanggan')->orderBy('id', 'desc')->first();

        // $nama_pelanggan = $d_pelanggan->nama;

        $tgl_mulai_bayar = null;

        $m_conf = new \Model\Storage\Conf();
        // $sql = "
        //     select
        //         min(rs.tgl_panen) as tgl_panen
        //     from det_pembayaran_pelanggan dpp
        //     right join
        //         (
        //             select pp1.* from pembayaran_pelanggan pp1
        //             right join
        //                 (select top 1 id from pembayaran_pelanggan where no_pelanggan = '".$pelanggan."' order by tgl_bayar desc) pp2
        //                 on
        //                     pp1.id = pp2.id
        //         ) pp
        //         on
        //             pp.id = dpp.id_header
        //     left join
        //         det_real_sj drs
        //         on
        //             drs.id = dpp.id_do
        //     left join
        //         real_sj rs
        //         on
        //             rs.id = drs.id_header
        // ";
        $sql = "
            select
                min(rs.tgl_panen) as tgl_panen
            from det_real_sj drs
            right join
                real_sj rs
                on
                    drs.id_header = rs.id
            left join
                (
                    select * from (
                        select dpp1.*, pp.perusahaan from det_pembayaran_pelanggan dpp1
                        right join
                            (select max(id) as id, id_do from det_pembayaran_pelanggan group by id_do) dpp2
                            on
                                dpp1.id = dpp2.id
                        left join
                            pembayaran_pelanggan pp
                            on
                                pp.id = dpp1.id_header
                    ) data
                    where
                        data.perusahaan = '".$perusahaan."'
                ) dpp
                on
                    drs.id = dpp.id_do
            left join
                (
                    select max(tgl_mulai_bayar) as tgl_mulai_bayar, no_pelanggan from saldo_pelanggan where tgl_mulai_bayar is not null group by no_pelanggan
                ) sp
                on
                    sp.no_pelanggan = drs.no_pelanggan 
            where
                rs.tgl_panen >= sp.tgl_mulai_bayar and
                rs.id_unit in ('".implode("', '", $id_unit)."') and
                drs.no_pelanggan = '".$pelanggan."' and
                drs.harga > 0 and
                drs.tonase > 0 and
                (dpp.status = 'BELUM' or dpp.id is null)
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $tgl_mulai_bayar_sp = null;
        $d_tgl_mulai_bayar = $m_sp->where('no_pelanggan', $pelanggan)->whereNotNull('tgl_mulai_bayar')->orderBy('id', 'desc')->first();
        if ( $d_tgl_mulai_bayar ) {
            $tgl_mulai_bayar_sp = $d_tgl_mulai_bayar->tgl_mulai_bayar;
        }

        if ( $d_conf->count() > 0 ) {
            $tgl_mulai_bayar = $d_conf->toArray()[0]['tgl_panen'];
            if ( $tgl_mulai_bayar <= $tgl_mulai_bayar_sp ) {
                $tgl_mulai_bayar = $tgl_mulai_bayar_sp;
            }
        } else {
            $tgl_mulai_bayar = $tgl_mulai_bayar_sp;
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                min(data.tgl_panen) as tgl_panen
            from
            (
                select
                    max(rs.tgl_panen) as tgl_panen,
                    rs.id_unit
                from det_real_sj drs
                left join
                    real_sj rs
                    on
                        drs.id_header = rs.id
                left join
                    (
                        select * from (
                            select dpp1.*, pp.perusahaan from det_pembayaran_pelanggan dpp1
                            right join
                                (select max(id) as id, id_do from det_pembayaran_pelanggan group by id_do) dpp2
                                on
                                    dpp1.id = dpp2.id
                            left join
                                pembayaran_pelanggan pp
                                on
                                    pp.id = dpp1.id_header
                        ) data
                        where
                            data.perusahaan = '".$perusahaan."'
                    ) dpp
                    on
                        drs.id = dpp.id_do
                where
                    drs.no_pelanggan = '".$pelanggan."' and
                    rs.id_unit in ('".implode("', '", $id_unit)."') and
                    drs.harga > 0 and
                    drs.tonase > 0 and
                    dpp.status = 'LUNAS'
                group by
                    rs.id_unit
            ) data
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $tgl_mulai_bayar = $d_conf->toArray()[0]['tgl_panen'];
            if ( $tgl_mulai_bayar <= $tgl_mulai_bayar_sp ) {
                $tgl_mulai_bayar = $tgl_mulai_bayar_sp;
            }
        }
        
        // cetak_r( $tgl_mulai_bayar, 1 );

        // $m_rs = new \Model\Storage\RealSJ_model();
        // $data_rs = null;
        // if ( !empty($tgl_mulai_bayar) ) {
        //     $m_conf = new \Model\Storage\Conf();
        //     $sql = "
        //         select
        //             rs.id as id,
        //             rs.id_unit as id_unit,
        //             rs.unit as unit,
        //             rs.tgl_panen as tgl_panen,
        //             rs.noreg as noreg,
        //             rs.ekor as ekor,
        //             rs.kg as kg,
        //             rs.bb as bb,
        //             rs.tara as tara,
        //             rs.netto_ekor as netto_ekor,
        //             rs.netto_kg as netto_kg,
        //             rs.netto_bb as netto_bb,
        //             rs.g_status as g_status,
        //             rdim.nama as nama
        //         from real_sj rs
        //         right join
        //             (select max(id) as id, noreg, tgl_panen from real_sj where tgl_panen >= '".$tgl_mulai_bayar."' and id_unit in ('".implode("', '", $id_unit)."') group by noreg, tgl_panen) rs2
        //             on
        //                 rs.id = rs2.id
        //         left join
        //             (
        //                 select rs.noreg, m.jenis, m.nama, m.perusahaan from rdim_submit rs
        //                 right join
        //                     (
        //                         select m1.* from mitra_mapping m1
        //                         right join
        //                             (select max(id) as id, nim from mitra_mapping group by nim) m2
        //                             on
        //                                 m1.id = m2.id
        //                     ) mm
        //                     on
        //                         rs.nim = mm.nim
        //                 right join
        //                     mitra m
        //                     on
        //                         mm.mitra = m.id
        //                 where
        //                     m.perusahaan = '".$perusahaan."'
        //             ) rdim
        //             on
        //                 rdim.noreg = rs.noreg
        //     ";
        //     $d_conf = $m_conf->hydrateRaw( $sql );

        //     if ( $d_conf->count() > 0 ) {
        //         $data_rs = $d_conf->toArray();
        //     }

        //     // $d_rs = $m_rs->where('tgl_panen', '>=', $tgl_mulai_bayar)->whereIn('id_unit', $id_unit)->get();

        //     // if ( $d_rs->count() > 0 ) {
        //     //     $d_rs = $d_rs->toArray();

        //     //     foreach ($d_rs as $k_rs => $v_rs) {
        //     //         $m_rdim_submit = new \Model\Storage\RdimSubmit_model();
        //     //         $sql = "
        //     //             select rs.*, m.jenis, m.nama from rdim_submit rs
        //     //             right join
        //     //                 mitra_mapping mm
        //     //                 on
        //     //                     rs.nim = mm.nim
        //     //             right join
        //     //                 mitra m
        //     //                 on
        //     //                     mm.mitra = m.id
        //     //             where
        //     //                 rs.noreg = '".$v_rs['noreg']."' and
        //     //                 m.perusahaan = '".$perusahaan."'
        //     //         ";

        //     //         $d_rdim_submit = $m_rdim_submit->hydrateRaw( $sql );

        //     //         if ( $d_rdim_submit->count() > 0 ) {
        //     //             $d_rdim_submit = $d_rdim_submit->toArray();

        //     //             $key = $v_rs['tgl_panen'].'|'.$v_rs['noreg'];
        //     //             if ( !isset($d_rs[ $key ]) ) {
        //     //                 $_d_rs = $m_rs->where('tgl_panen', $v_rs['tgl_panen'])->where('noreg', $v_rs['noreg'])->orderBy('id', 'desc')->first()->toArray();

        //     //                 $data_rs[ $key ] = array(
        //     //                     'id' => $_d_rs['id'],
        //     //                     'id_unit' => $_d_rs['id_unit'],
        //     //                     'unit' => $_d_rs['unit'],
        //     //                     'tgl_panen' => $_d_rs['tgl_panen'],
        //     //                     'noreg' => $_d_rs['noreg'],
        //     //                     'ekor' => $_d_rs['ekor'],
        //     //                     'kg' => $_d_rs['kg'],
        //     //                     'bb' => $_d_rs['bb'],
        //     //                     'tara' => $_d_rs['tara'],
        //     //                     'netto_ekor' => $_d_rs['netto_ekor'],
        //     //                     'netto_kg' => $_d_rs['netto_kg'],
        //     //                     'netto_bb' => $_d_rs['netto_bb'],
        //     //                     'g_status' => $_d_rs['g_status'],
        //     //                     'nama' => $d_rdim_submit[0]['nama']
        //     //                 );
        //     //             }
        //     //         }
        //     //     }
        //     // }
        // }

        $m_conf = new \Model\Storage\Conf();
        // $sql = "
        //     select
        //         drs.id as id,
        //         rs.nama as nama,
        //         rs.noreg as kandang,
        //         rs.tgl_panen as tgl_panen,
        //         drs.no_do as no_do,
        //         drs.no_sj as no_sj,
        //         drs.ekor as ekor,
        //         drs.tonase as kg,
        //         drs.harga as harga,
        //         (drs.tonase * drs.harga) as total,
        //         sum(dpp.tot_jumlah_bayar) as jumlah_bayar
        //     from 
        //         (select * from det_real_sj where no_pelanggan = '".$no_pelanggan."') drs
        //     right join
        //         (
        //             select
        //                 rs.id as id,
        //                 rs.id_unit as id_unit,
        //                 rs.unit as unit,
        //                 rs.tgl_panen as tgl_panen,
        //                 rs.noreg as noreg,
        //                 rs.ekor as ekor,
        //                 rs.kg as kg,
        //                 rs.bb as bb,
        //                 rs.tara as tara,
        //                 rs.netto_ekor as netto_ekor,
        //                 rs.netto_kg as netto_kg,
        //                 rs.netto_bb as netto_bb,
        //                 rs.g_status as g_status,
        //                 rdim.nama as nama
        //             from real_sj rs
        //             right join
        //                 (
        //                     select 
        //                 		max(rs.id) as id, 
        //                 		rs.noreg, 
        //                 		rs.tgl_panen 
        //                 	from det_real_sj drs
        //                 	left join
        //                 		real_sj rs
        //                 		on
        //                 			drs.id_header = rs.id
        //                 	where 
        //                 		rs.tgl_panen >= '".$tgl_mulai_bayar."' and 
        //                 		rs.id_unit in ('".implode("', '", $id_unit)."') and
        //                 		drs.no_pelanggan = '".$no_pelanggan."'
        //                 	group by 
        //                         rs.noreg, 
        //                         rs.tgl_panen

        //                     -- select max(id) as id, noreg, tgl_panen from real_sj where tgl_panen >= '".$tgl_mulai_bayar."' and id_unit in ('".implode("', '", $id_unit)."') group by noreg, tgl_panen
        //                 ) rs2
        //                 on
        //                     rs.id = rs2.id
        //             left join
        //                 (
        //                     select rs.noreg, m.jenis, m.nama, m.perusahaan from rdim_submit rs
        //                     right join
        //                         (
        //                             select m1.* from mitra_mapping m1
        //                             right join
        //                                 (select max(id) as id, nim from mitra_mapping group by nim) m2
        //                                 on
        //                                     m1.id = m2.id
        //                         ) mm
        //                         on
        //                             rs.nim = mm.nim
        //                     right join
        //                         mitra m
        //                         on
        //                             mm.mitra = m.id
        //                     where
        //                         m.perusahaan = '".$perusahaan."'
        //                 ) rdim
        //                 on
        //                     rdim.noreg = rs.noreg
        //             where
        //                 rs.id is not null and
        //                 rdim.noreg is not null
        //             group by
        //                 rs.id,
        //                 rs.id_unit,
        //                 rs.unit,
        //                 rs.tgl_panen,
        //                 rs.noreg,
        //                 rs.ekor,
        //                 rs.kg,
        //                 rs.bb,
        //                 rs.tara,
        //                 rs.netto_ekor,
        //                 rs.netto_kg,
        //                 rs.netto_bb,
        //                 rs.g_status,
        //                 rdim.nama
        //         ) rs
        //         on
        //             drs.id_header = rs.id
        //     left join
        //         (
        //             select dpp1.*, dpp2.tot_jumlah_bayar from det_pembayaran_pelanggan dpp1
        //             right join
        //                 (select max(id) as id, id_do, sum(jumlah_bayar) as tot_jumlah_bayar from det_pembayaran_pelanggan group by id_do) dpp2
        //                 on
        //                     dpp1.id = dpp2.id
        //         ) dpp
        //         on
        //             dpp.id_do = drs.id
        //     where
        //         (dpp.status = 'BELUM' or dpp.id is null) and
        //         drs.harga > 0 and
        //         drs.tonase > 0
        //     group by
        //         drs.id,
        //         rs.nama,
        //         rs.noreg,
        //         rs.tgl_panen,
        //         drs.no_do,
        //         drs.no_sj,
        //         drs.ekor,
        //         drs.tonase,
        //         drs.harga
        //     order by
        //         rs.tgl_panen asc,
        //         drs.no_do asc,
        //         drs.id asc
        // ";

        $sql_tgl = "rs.tgl_panen >= '".$tgl_mulai_bayar."' and";
        // if ( $no_pelanggan == '21B338' ) {
        //     $sql_tgl = "rs.tgl_panen >= '".$tgl_mulai_bayar."' and rs.tgl_panen < '2025-03-28' and";
        // }

        $sql = "
            select 
                drs.id as id,
                rdim.nama as nama,
                rs.noreg as kandang,
                rs.tgl_panen as tgl_panen,
                drs.no_do as no_do,
                drs.no_sj as no_sj,
                drs.no_nota,
                drs.ekor as ekor,
                drs.tonase as kg,
                drs.harga as harga,
                (drs.tonase * drs.harga) as total,
                sum(dpp.tot_jumlah_bayar) as jumlah_bayar
            from det_real_sj drs 
            left join
                (
                    select dpp1.*, dpp2.tot_jumlah_bayar from det_pembayaran_pelanggan dpp1
                    right join
                        (select max(id) as id, id_do, sum(jumlah_bayar) as tot_jumlah_bayar from det_pembayaran_pelanggan group by id_do) dpp2
                        on
                            dpp1.id = dpp2.id
                ) dpp
                on
                    dpp.id_do = drs.id
            left join
                (
                    select rs1.* from real_sj rs1
                    right join
                        (select max(rs.id) as id, rs.noreg, rs.tgl_panen from real_sj rs group by rs.noreg, rs.tgl_panen) rs2
                        on
                            rs1.id = rs2.id
                ) rs
                on
                    drs.id_header = rs.id
            left join
                (
                    select rs.noreg, m.jenis, m.nama, m.perusahaan from rdim_submit rs
                    right join
                        (
                            select m1.* from mitra_mapping m1
                            right join
                                (select max(id) as id, nim from mitra_mapping group by nim) m2
                                on
                                    m1.id = m2.id
                        ) mm
                        on
                            rs.nim = mm.nim
                    right join
                        mitra m
                        on
                            mm.mitra = m.id
                    where
                        m.perusahaan = '".$perusahaan."'
                ) rdim
                on
                    rdim.noreg = rs.noreg
            where 
                rs.id is not null and
                rdim.noreg is not null and
                -- rs.tgl_panen >= '".$tgl_mulai_bayar."' and
                ".$sql_tgl."
                rs.id_unit in ('".implode("', '", $id_unit)."') and
                drs.no_pelanggan = '".$no_pelanggan."' and
                drs.tonase > 0 and drs.harga > 0 and
                (dpp.status = 'BELUM' or dpp.id is null)
            group by
                drs.id,
                rdim.nama,
                rs.noreg,
                rs.tgl_panen,
                drs.no_do,
                drs.no_sj,
                drs.no_nota,
                drs.ekor,
                drs.tonase,
                drs.harga
            order by
                rs.tgl_panen asc,
                drs.no_do asc,
                drs.id asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $jumlah_bayar = 0;

        // if ( !empty($id) ) {
        //     $m_pp = new \Model\Storage\PembayaranPelanggan_model();
        //     $d_pp = $m_pp->where('id', $id)->with(['detail', 'pelanggan'])->first();

        //     if ( $d_pp ) {
        //         $d_pp = $d_pp->toArray();
        //         if ( $d_pp['no_pelanggan'] == $no_pelanggan ) {
        //             foreach ($d_pp['detail'] as $k_det => $v_det) {
        //                 $m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
        //                 $d_dpp = $m_dpp->where('id_do', $v_det['id_do'])->orderBy('id', 'desc')->first();

        //                 $jml_bayar = !empty($d_dpp) ? $m_dpp->where('id_do', $v_det['id_do'])->where('id', '<>', $v_det['id'])->sum('jumlah_bayar') : 0;

        //                 $m_rs = new \Model\Storage\RealSJ_model();
        //                 $sql = "
        //                     select rsj.noreg, m.nama, drs.* from det_real_sj drs
        //                     right join
        //                         real_sj rsj
        //                         on
        //                             drs.id_header = rsj.id 
        //                     right join
        //                         rdim_submit rs
        //                         on
        //                             rsj.noreg = rs.noreg 
        //                     right join
        //                         mitra_mapping mm
        //                         on
        //                             rs.nim = mm.nim
        //                     right join
        //                         mitra m
        //                         on
        //                             mm.mitra = m.id
        //                     where
        //                         drs.id = ".$v_det['id_do']."
        //                 ";
        //                 $d_rs = $m_rs->hydrateRaw( $sql );

        //                 if ( $d_rs->count() > 0 ) {
        //                     $d_rs = $d_rs->toArray();

        //                     $key = str_replace('-', '', $v_det['data_do']['header']['tgl_panen']).'|'.$v_det['id_do'];
        //                     $data[ $key ] = array(
        //                         'id' => $v_det['id_do'],
        //                         'nama' => $d_rs[0]['nama'],
        //                         'kandang' => substr($d_rs[0]['noreg'], -2),
        //                         'tgl_panen' => $v_det['data_do']['header']['tgl_panen'],
        //                         'no_do' => $v_det['data_do']['no_do'],
        //                         'no_sj' => $v_det['data_do']['no_sj'],
        //                         'ekor' => $v_det['data_do']['ekor'],
        //                         'kg' => $v_det['data_do']['tonase'],
        //                         'harga' => $v_det['data_do']['harga'],
        //                         'total' => $v_det['total_bayar'],
        //                         'jumlah_bayar' => $jml_bayar
        //                     );
        //                 }
        //             }
        //         }

        //         $saldo = $d_pp['saldo'];
        //     }
        // }

        // if ( !empty($data_rs) > 0 ) {
        //     foreach ($data_rs as $k_rs => $v_rs) {
        //         $m_drs = new \Model\Storage\DetRealSJ_model();
        //         $d_drs = $m_drs->where('no_pelanggan', $no_pelanggan)->where('id_header', $v_rs['id'])->where('harga', '>', 0)->get();

        //         if ( $d_drs->count() > 0 ) {
        //             $d_drs = $d_drs->toArray();

        //             foreach ($d_drs as $k_drs => $v_drs) {
        //                 $m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
        //                 $d_dpp = $m_dpp->where('id_do', $v_drs['id'])->orderBy('id', 'desc')->first();

        //                 $tampil = false;
        //                 if ( $d_dpp ) {
        //                     if ( $d_dpp->status == 'BELUM' ) {
        //                         $tampil = true;
        //                     }
        //                 } else {
        //                     $tampil = true;
        //                 }

        //                 if ( $tampil ) {
        //                     $key = str_replace('-', '', $v_rs['tgl_panen']).'|'.$v_drs['id'];
        //                     if ( !isset($data[ $key ]) ) {
        //                         $no_do = $v_drs['no_do'];
        //                         $harga = $v_drs['harga'];
        //                         $tonase = $v_drs['tonase'];
        //                         $jml_bayar = !empty($d_dpp) ? $m_dpp->where('id_do', $v_drs['id'])->sum('jumlah_bayar') : 0;

        //                         $total = $tonase * $harga;

    	// 						$data[ $key ] = array(
        //                             'id' => $v_drs['id'],
        //                             'nama' => $v_rs['nama'],
    	// 							'kandang' => substr($v_rs['noreg'], -2),
    	// 							'tgl_panen' => $v_rs['tgl_panen'],
    	// 							'no_do' => $v_drs['no_do'],
    	// 							'no_sj' => $v_drs['no_sj'],
    	// 							'ekor' => $v_drs['ekor'],
    	// 							'kg' => $v_drs['tonase'],
    	// 							'harga' => $v_drs['harga'],
    	// 							'total' => $total,
    	// 							'jumlah_bayar' => $jml_bayar
    	// 						);

    	// 						$jumlah_bayar += $jml_bayar;

    	//                     	ksort($data);
        //                     }
        //                 }
        //             }
        //         }
        //     }
        // }

		$content['jumlah_bayar'] = $jumlah_bayar;
		$content['data'] = $data;
		$html = $this->load->view('pembayaran/bakul/list_do', $content, true);

		$this->result['status'] = 1;
		$this->result['saldo'] = $saldo;
		$this->result['html'] = $html;

		display_json( $this->result );
	}

	public function save()
	{
		$data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

        try {
            $file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];
            }
            if ($isMoved) {
                $file_name = $moved['name'];
                $path_name = $moved['path'];

                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select prs1.* from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                    where
                        prs1.kode = '".$data['perusahaan']."'
                ";
                $d_conf = $m_conf->hydrateRaw( $sql );

                $no_bukti_auto = null;
                if ( $d_conf->count() > 0 ) {
                    $d_conf = $d_conf->toArray()[0];

                    $kode = $d_conf['kode_auto'].'-'.substr($d_conf['rekening'], 1, 3).'/BBK';

                    $m_pp = new \Model\Storage\PembayaranPelanggan_model();
                    $no_bukti_auto = $m_pp->getNextNomorAuto( $kode, $data['tgl_bayar'] );
                }

                $m_pp = new \Model\Storage\PembayaranPelanggan_model();
                // $m_pp->no_bukti_auto = $no_bukti_auto;
                $m_pp->no_pelanggan = $data['pelanggan'];
				$m_pp->tgl_bayar = $data['tgl_bayar'];
				$m_pp->urut_tf = ( isset($data['urut_tf']) && !empty($data['urut_tf']) ) ? $data['urut_tf'] : null;
				$m_pp->kode_umb = ( isset($data['kode_umb']) && !empty($data['kode_umb']) ) ? $data['kode_umb'] : null;
				$m_pp->jml_transfer = $data['jml_transfer'];
				$m_pp->saldo = $data['saldo'];
				$m_pp->nil_pajak = $data['nil_pajak'];
				$m_pp->non_saldo = $data['lebih_bayar_non_saldo'];
				$m_pp->total_uang = $data['total_uang'];
				$m_pp->total_penyesuaian = $data['total_penyesuaian'];
				$m_pp->total_bayar = $data['total_bayar'];
				$m_pp->lebih_kurang = $data['lebih_kurang'];
				$m_pp->lampiran_transfer = $path_name;
				$m_pp->perusahaan = $data['perusahaan'];
				$m_pp->save();

				$id = $m_pp->id;

				foreach ($data['detail'] as $k_det => $v_det) {
					$m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
					$m_dpp->id_header = $id;
					$m_dpp->id_do = $v_det['id'];
					$m_dpp->total_bayar = $v_det['total'];
					$m_dpp->jumlah_bayar = $v_det['jml_bayar'];
					$m_dpp->penyesuaian = $v_det['penyesuaian'];
					$m_dpp->ket_penyesuaian = $v_det['ket_penyesuaian'];
					$m_dpp->status = $v_det['status'];
					$m_dpp->save();
				}

				$d_pp = $m_pp->where('id', $id)->with(['detail'])->first();

				$deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            	Modules::run( 'base/event/save', $d_pp, $deskripsi_log );

        		$m_sp = new \Model\Storage\SaldoPelanggan_model();
        		$m_sp->jenis_saldo = 'D';
				$m_sp->no_pelanggan = $data['pelanggan'];
				$m_sp->id_trans = $id;
				$m_sp->tgl_trans = date('Y-m-d');
				$m_sp->jenis_trans = 'pembayaran_pelanggan';
				$m_sp->nominal = 0;
				$m_sp->saldo = ($data['lebih_kurang'] > 0) ? $data['lebih_kurang'] : 0;
				$m_sp->perusahaan = $data['perusahaan'];
				$m_sp->save();

                $m_conf = new \Model\Storage\Conf();
                $sql = "exec insert_jurnal NULL, NULL, NULL, 0, 'pembayaran_pelanggan', ".$id.", NULL, 1";
                $d_conf = $m_conf->hydrateRaw( $sql );

	        	$this->result['status'] = 1;
	        	$this->result['message'] = 'Data berhasil di simpan.';
            }else {
	        	$this->result['message'] = 'Error, segera hubungi tim IT.';
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json( $this->result );
	}

	public function edit()
	{
		$data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

        try {
            $id = $data['id'];

            $m_pp = new \Model\Storage\PembayaranPelanggan_model();
            $d_pp = $m_pp->where('id', $data['id'])->with(['detail'])->first()->toArray();

            $file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];

                $file_name = $moved['name'];
                $path_name = $moved['path'];
            } else {
            	$file_name = $d_pp['lampiran_transfer'];
                $path_name = $d_pp['lampiran_transfer'];
            }

            if ( $d_pp['no_pelanggan'] == $data['pelanggan'] && $d_pp['perusahaan'] == $data['perusahaan'] ) {
                $m_pp->where('id', $id)->update(
                	array(
    					'tgl_bayar' => $data['tgl_bayar'],
    					'jml_transfer' => $data['jml_transfer'],
    					'saldo' => $data['saldo'],
                        'nil_pajak' => $data['nil_pajak'],
                        'non_saldo' => $data['lebih_bayar_non_saldo'],
    					'total_uang' => $data['total_uang'],
    					'total_penyesuaian' => $data['total_penyesuaian'],
    					'total_bayar' => $data['total_bayar'],
    					'lebih_kurang' => $data['lebih_kurang'],
    					'lampiran_transfer' => $path_name
                	)
                );

                $m_sp = new \Model\Storage\SaldoPelanggan_model();
                $d_sp = $m_sp->where('no_pelanggan', $d_pp['no_pelanggan'])->where('id_trans', $id)->first();

                $jenis_saldo = '';
                $saldo = $d_sp->saldo;
                $selisih = 0;
                if ( $saldo > $data['lebih_kurang'] ) {
                    $jenis_saldo = 'K';
                    $selisih = abs($saldo - $data['lebih_kurang']);
                    $saldo -= $selisih;
                } else {
                    $jenis_saldo = 'D';
                    $selisih = abs($saldo - $data['lebih_kurang']);
                    $saldo += $selisih;
                }

                $m_sp = new \Model\Storage\SaldoPelanggan_model();
                $m_sp->jenis_saldo = $jenis_saldo;
                $m_sp->no_pelanggan = $d_pp['no_pelanggan'];
                $m_sp->id_trans = $id;
                $m_sp->tgl_trans = date('Y-m-d');
                $m_sp->jenis_trans = 'reverse_pembayaran_pelanggan';
                $m_sp->nominal = abs($selisih);
                $m_sp->saldo = ($saldo > 0) ? $saldo : 0;
                $m_sp->perusahaan = $d_sp->perusahaan;
                $m_sp->save();
            } else {
                // REMOVE SALDO
                $m_sp_prev = new \Model\Storage\SaldoPelanggan_model();
                $d_sp_prev = $m_sp_prev->where('no_pelanggan', $d_pp['no_pelanggan'])->where('id_trans', $id)->first();

                $m_sp_prev = new \Model\Storage\SaldoPelanggan_model();
                $m_sp_prev->jenis_saldo = 'K';
                $m_sp_prev->no_pelanggan = $d_sp_prev['no_pelanggan'];
                $m_sp_prev->id_trans = $id;
                $m_sp_prev->tgl_trans = date('Y-m-d');
                $m_sp_prev->jenis_trans = 'reverse_pembayaran_pelanggan';
                $m_sp_prev->nominal = $d_sp_prev->nominal;
                $m_sp_prev->saldo = (($d_sp_prev->saldo - $d_sp_prev->nominal) > 0) ? ($d_sp_prev->saldo - $d_sp_prev->nominal) : 0;
                $m_sp_prev->perusahaan = $d_sp_prev->perusahaan;
                $m_sp_prev->save();
                // END - REMOVE SALDO

                if ( $d_pp['no_pelanggan'] == $data['pelanggan']  ) {
                    $m_pp->where('id', $id)->update(
                        array(
                            'tgl_bayar' => $data['tgl_bayar'],
                            'jml_transfer' => $data['jml_transfer'],
                            'saldo' => $data['saldo'],
                            'nil_pajak' => $data['nil_pajak'],
                            'non_saldo' => $data['lebih_bayar_non_saldo'],
                            'total_uang' => $data['total_uang'],
                            'total_penyesuaian' => $data['total_penyesuaian'],
                            'total_bayar' => $data['total_bayar'],
                            'lebih_kurang' => $data['lebih_kurang'],
                            'lampiran_transfer' => $path_name,
                            'perusahaan' => $data['perusahaan']
                        )
                    );
                } else {
                    $m_pp_prev = new \Model\Storage\PembayaranPelanggan_model();
                    $m_pp_prev->where('id', $data['id'])->delete();

                    $m_pp_next = new \Model\Storage\PembayaranPelanggan_model();
                    $m_pp_next->no_pelanggan = $data['pelanggan'];
                    $m_pp_next->tgl_bayar = $data['tgl_bayar'];
                    $m_pp_next->jml_transfer = $data['jml_transfer'];
                    $m_pp_next->saldo = $data['saldo'];
                    $m_pp_next->nil_pajak = $data['nil_pajak'];
                    $m_pp_next->non_saldo = $data['lebih_bayar_non_saldo'];
                    $m_pp_next->total_uang = $data['total_uang'];
                    $m_pp_next->total_penyesuaian = $data['total_penyesuaian'];
                    $m_pp_next->total_bayar = $data['total_bayar'];
                    $m_pp_next->lebih_kurang = $data['lebih_kurang'];
                    $m_pp_next->lampiran_transfer = $path_name;
                    $m_pp_next->perusahaan = $data['perusahaan'];
                    $m_pp_next->save();

                    $id = $m_pp_next->id;
                }

                // ADD SALDO
                $m_sp_next = new \Model\Storage\SaldoPelanggan_model();
                $d_sp_next = $m_sp_next->where('no_pelanggan', $data['pelanggan'])->where('perusahaan', $data['perusahaan'])->orderBy('id', 'desc')->first();

                $m_sp_next = new \Model\Storage\SaldoPelanggan_model();
                $m_sp_next->jenis_saldo = 'D';
                $m_sp_next->no_pelanggan = $data['pelanggan'];
                $m_sp_next->id_trans = $id;
                $m_sp_next->tgl_trans = date('Y-m-d');
                $m_sp_next->jenis_trans = 'pembayaran_pelanggan';
                $m_sp_next->nominal = $data['lebih_kurang'];
                $m_sp_next->saldo = $d_sp_next->saldo + $data['lebih_kurang'];
                $m_sp_next->perusahaan = $data['perusahaan'];
                $m_sp_next->save();
                // END - ADD SALDO
            }

			$m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
			$m_dpp->where('id_header', $id)->delete();
			foreach ($data['detail'] as $k_det => $v_det) {
				$m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
				$m_dpp->id_header = $id;
				$m_dpp->id_do = $v_det['id'];
				$m_dpp->total_bayar = $v_det['total'];
				$m_dpp->jumlah_bayar = $v_det['jml_bayar'];
				$m_dpp->penyesuaian = $v_det['penyesuaian'];
				$m_dpp->ket_penyesuaian = $v_det['ket_penyesuaian'];
				$m_dpp->status = $v_det['status'];
				$m_dpp->save();
			}

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal NULL, NULL, NULL, 0, 'pembayaran_pelanggan', ".$id.", ".$id.", 2";
            $d_conf = $m_conf->hydrateRaw( $sql );

			$_d_pp = $m_pp->where('id', $id)->with(['detail'])->first();

			$deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
        	Modules::run( 'base/event/update', $_d_pp, $deskripsi_log );

   //      	$m_sp = new \Model\Storage\SaldoPelanggan_model();
   //      	$d_sp = $m_sp->where('no_pelanggan', $d_pp['no_pelanggan'])->orderBy('id', 'desc')->first();

   //      	$jenis_saldo = null;
   //      	$nominal = null;
   //      	$saldo = !empty($d_sp) ? $d_sp->saldo : 0;

   //      	if ( $data['lebih_kurang'] < 0 ) {
   //      		if ( $d_pp['lebih_kurang'] > 0 ) {
   //  				$nominal = $d_pp['lebih_kurang'];
   //      		} else {
   //      			$nominal = abs($data['lebih_kurang']) - abs($d_pp['lebih_kurang']);
   //      		}
   //      		$jenis_saldo = 'K';
   //      		$saldo -= abs($nominal);
   //      	} else {
   //      		if ( $d_pp['lebih_kurang'] > 0 ) {
   //      			$nominal = $data['lebih_kurang'] - $d_pp['lebih_kurang'];
   //      		} else {
   //      			$nominal = $data['lebih_kurang'];
   //      		}
   //      		$jenis_saldo = 'D';
   //      		$saldo += abs($nominal);
   //      	}

   //  		$m_sp = new \Model\Storage\SaldoPelanggan_model();
   //  		$m_sp->jenis_saldo = $jenis_saldo;
			// $m_sp->no_pelanggan = $d_pp['no_pelanggan'];
			// $m_sp->id_trans = $id;
			// $m_sp->tgl_trans = date('Y-m-d');
			// $m_sp->jenis_trans = 'reverse_pembayaran_pelanggan';
			// $m_sp->nominal = abs($nominal);
			// $m_sp->saldo = ($saldo > 0) ? $saldo : 0;
			// $m_sp->save();

        	$this->result['status'] = 1;
        	$this->result['message'] = 'Data berhasil di edit.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json( $this->result );
	}

	public function delete()
	{
		$id = $this->input->post('params');

        try {
            $m_pp = new \Model\Storage\PembayaranPelanggan_model();
            $d_pp = $m_pp->where('id', $id)->with(['detail'])->first()->toArray();

			$_d_pp = $m_pp->where('id', $id)->with(['detail'])->first();

			$deskripsi_log = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
        	Modules::run( 'base/event/update', $_d_pp, $deskripsi_log );

            $m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
			$m_dpp->where('id_header', $id)->delete();
            $m_pp->where('id', $id)->delete();

        	// $m_sp = new \Model\Storage\SaldoPelanggan_model();
        	// $d_sp = $m_sp->where('no_pelanggan', $d_pp['no_pelanggan'])->where('perusahaan', $d_pp['perusahaan'])->orderBy('id', 'desc')->first();

        	// $saldo = !empty($d_sp) ? $d_sp->saldo : 0;

        	$jenis_saldo = 'K';
        	// if ( $d_pp['lebih_kurang'] > 0 ) {
        	// 	$saldo -= $d_pp['lebih_kurang'];
        	// }

            // $saldo += $d_pp['saldo'];

            // $d_pp_prev = $m_pp->where('id', '<', $id)->orderBy('id', 'desc')->first();

            $saldo = $_d_pp->saldo;
            // if ( $d_pp_prev ) {
            //     $saldo = $d_pp_prev->saldo;
            // }

    		$m_sp = new \Model\Storage\SaldoPelanggan_model();
    		$m_sp->jenis_saldo = $jenis_saldo;
			$m_sp->no_pelanggan = $d_pp['no_pelanggan'];
			$m_sp->id_trans = $id;
			$m_sp->tgl_trans = date('Y-m-d');
			$m_sp->jenis_trans = 'reverse_pembayaran_pelanggan';
			$m_sp->nominal = $saldo;
			$m_sp->saldo = ($saldo > 0) ? $saldo : 0;
			$m_sp->perusahaan = $d_pp['perusahaan'];
			$m_sp->save();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal NULL, NULL, NULL, 0, 'pembayaran_pelanggan', ".$id.", ".$id.", 3";
            $d_conf = $m_conf->hydrateRaw( $sql );

        	$this->result['status'] = 1;
        	$this->result['message'] = 'Data berhasil di hapus.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json( $this->result );
	}

	public function tes()
	{
		$m_p = new \Model\Storage\Pelanggan_model();
		// $d_sp = $m_sp->select('nomor', 'tipe')->where('tipe', 'pelanggan')->groupBy('nomor', 'tipe')->get()->toArray();

		// foreach ($d_sp as $k_sp => $v_sp) {
		// 	$m_sp = new \Model\Storage\SaldoPelanggan_model();
		// 	$d_sp = $m_sp->where('no_pelanggan', $v_sp['nomor'])->first();

		// 	if ( $d_sp ) {
		// 		$m_sp->where('no_pelanggan', $v_sp['nomor'])->where('id', $d_sp->id)->update(
		// 			array(
		// 				'saldo' => 0
		// 			)
		// 		);
		// 	} else {
		// 		$m_sp->jenis_saldo = 'D';
		// 		$m_sp->no_pelanggan = $v_sp['nomor'];
		// 		$m_sp->id_trans = NULL;
		// 		$m_sp->tgl_trans = '2021-11-01';
		// 		$m_sp->jenis_trans = 'pembayaran_pelanggan';
		// 		$m_sp->nominal = 0;
		// 		$m_sp->saldo = 0;
		// 		$m_sp->save();
		// 	}
		// }

		$data_pelanggan = array(
			'Ali Zainal Mustofa', 'Bambang Brontoyono', 'Edy Santoso', 'Fitrah Hari Mukti', 'Imam Safii', 'Imawan Agus Mulyono', 'Lilik Soegiwati', 'Moh. Abdul Rohim', 'Muhammad Fadloli', 'Muslinin', 'Ngateno', 'Rani Sanjaya', 'Sahid', 'Siti Aisah', 'Supriyono'
			// 'Sugiarto',
		);

		$d_p = $m_p->whereIn('nama', $data_pelanggan)->get()->toArray();

		cetak_r( $d_p );
	}
}