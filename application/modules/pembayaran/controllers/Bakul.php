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
                $m_conf = new \Model\Storage\RealSJ_model();
                $sql = "
                    select rsj.noreg, rsj.tgl_panen, m.nama, drsi.* 
                    from det_real_sj_inv drsi
                    left join
                        (
                            select max(id_header) as id_header, no_sj, no_pelanggan
                            from det_real_sj drs
                            group by
                                no_sj,
                                no_pelanggan
                        ) drs
                        on
                            drs.no_sj = drsi.no_sj
                    left join
                        real_sj rsj
                        on
                            rsj.id = drs.id_header
                    left join
                        rdim_submit rs
                        on
                            rsj.noreg = rs.noreg 
                    left join
                        (
                            select mm1.* from mitra_mapping mm1
                            right join
                                (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                on
                                    mm1.id = mm2.id
                        ) mm
                        on
                            rs.nim = mm.nim
                    left join
                        mitra m
                        on
                            mm.mitra = m.id
                    where
                        drsi.no_inv = '".$v_det['no_inv']."'
                ";
                $d_rs = $m_conf->hydrateRaw( $sql );

                if ( $d_rs->count() > 0 ) {
                    $d_rs = $d_rs->toArray();

        			$detail[ $v_det['id'] ] = array(
        				'id' => $v_det['id'],
                        'id_header' => $v_det['id_header'],
                        'tgl_panen' => $d_rs[0]['tgl_panen'],
                        'no_sj' => $v_det['no_sj'],
                        'no_inv' => $v_det['no_inv'],
                        'ekor' => $d_rs[0]['ekor'],
                        'tonase' => $d_rs[0]['tonase'],
                        'cn' => $v_det['cn'],
                        'dn' => $v_det['dn'],
                        'nilai' => $v_det['nilai'],
                        'tagihan' => $v_det['tagihan'],
                        'jml_bayar' => ($v_det['tagihan'] - ($v_det['penyesuaian']+$v_det['sisa_tagihan'])),
                        'penyesuaian' => $v_det['penyesuaian'],
                        'ket_penyesuaian' => $v_det['ket_penyesuaian'],
                        'status' => $v_det['status'],
                        'sisa_tagihan' => $v_det['sisa_tagihan'],
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
				'total_cn' => $d_pp['total_cn'],
				'total_dn' => $d_pp['total_dn'],
				'total_nilai' => ($d_pp['total_bayar'] + $d_pp['total_cn'] + $d_pp['total_penyesuaian']) - $d_pp['total_dn'],
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
		$unit = $this->input->post('unit');
		$tgl_bayar = $this->input->post('tgl_bayar');
        $perusahaan = $this->input->post('perusahaan');
        $jenis_mitra = $this->input->post('jenis_mitra');

        try {
            $data = null;

            $m_sp = new \Model\Storage\SaldoPelanggan_model();
            $d_sp_by_perusahaan = $m_sp->where('no_pelanggan', $pelanggan)->where('perusahaan', $perusahaan)->orderBy('id', 'desc')->first();

            $saldo = 0;
            if ( $d_sp_by_perusahaan ) {
                $saldo = $d_sp_by_perusahaan->saldo;
            }

            $sql_unit_data = null;
            $sql_unit_min_date = null;
            if ( !in_array('all', $unit) ) {
                $sql_unit_data = "cast(SUBSTRING(drsi.no_inv, 5, 3) as varchar(5)) in ('".implode("', '", $unit)."') and";
                $sql_unit_min_date = "and cast(SUBSTRING(dpp.no_inv, 5, 3) as varchar(5)) in ('".implode("', '", $unit)."')";
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    rdim.nama as nama,
                    rs.noreg as kandang,
                    rs.tgl_panen as tgl_panen,
                    drsi.no_inv as no_inv,
                    drsi.no_sj as no_sj,
                    drsi.tonase as kg,
                    drsi.ekor as ekor,
                    drsi.bb as bb,
                    (((drsi.total+isnull(dpp.dn, 0))-isnull(dpp.cn, 0)) - isnull(dpp.tot_jumlah_bayar, 0)) as nilai,
                    (((drsi.total+isnull(dpp.dn, 0))-isnull(dpp.cn, 0)) - isnull(dpp.tot_jumlah_bayar, 0)) as tagihan,
                    0 as jml_bayar,
                    (((drsi.total+isnull(dpp.dn, 0))-isnull(dpp.cn, 0)) - isnull(dpp.tot_jumlah_bayar, 0)) as sisa_tagihan
                from det_real_sj_inv drsi
                left join
                    (
                        select max(id_header) as id_header, no_sj, no_pelanggan
                        from det_real_sj drs
                        group by
                            no_sj,
                            no_pelanggan
                    ) drs
                    on
                        drs.no_sj = drsi.no_sj
                left join
                    real_sj rs
                    on
                        rs.id = drs.id_header
                left join
                    (
                        select no_inv, isnull(sum(cn), 0) as cn, isnull(sum(dn), 0) as dn, isnull(sum(tagihan-(penyesuaian+sisa_tagihan)), 0) as tot_jumlah_bayar from det_pembayaran_pelanggan group by no_inv
                    ) dpp
                    on
                        dpp.no_inv = drsi.no_inv
                left join
                    (
                        select rs.noreg, m.jenis, m.nama, m.perusahaan from rdim_submit rs
                        left join
                            (
                                select m1.* from mitra_mapping m1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) m2
                                    on
                                        m1.id = m2.id
                            ) mm
                            on
                                rs.nim = mm.nim
                        left join
                            mitra m
                            on
                                mm.mitra = m.id
                        where
                            m.perusahaan = 'P001'
                    ) rdim
                    on
                        rdim.noreg = rs.noreg
                where 
                    ".$sql_unit_data."
                    drs.no_pelanggan = '".$pelanggan."'
                    and ((drsi.total+isnull(dpp.dn, 0))-isnull(dpp.cn, 0)) > isnull(dpp.tot_jumlah_bayar, 0)
                order by
                    rs.tgl_panen asc,
                    drsi.no_inv asc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $data = $d_conf->toArray();
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select max(pp.tgl_bayar) as tgl_bayar from det_pembayaran_pelanggan dpp
                left join
                    pembayaran_pelanggan pp
                    on
                        dpp.id_header = pp.id
                where
                    pp.no_pelanggan = '".$pelanggan."'
                    ".$sql_unit_min_date."
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $min_date = null;
            if ( $d_conf->count() > 0 ) {
                $min_date = $d_conf->toArray()[0]['tgl_bayar'];
            }

            $content['data'] = $data;
            $html = $this->load->view('pembayaran/bakul/list_do', $content, true);

            $this->result['status'] = 1;
            $this->result['saldo'] = $saldo;
            $this->result['min_date'] = $min_date;
            $this->result['html'] = $html;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

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
                $nomor = $m_pp->getNextNomor('BYR/BKL');
                // $m_pp->no_bukti_auto = $no_bukti_auto;
                $m_pp->nomor = $nomor;
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
				$m_pp->total_cn = $data['total_cn'];
				$m_pp->total_dn = $data['total_dn'];
				$m_pp->total_bayar = $data['total_tagihan'];
				$m_pp->lebih_kurang = $data['lebih_kurang'];
				$m_pp->lampiran_transfer = $path_name;
				$m_pp->perusahaan = $data['perusahaan'];
				$m_pp->save();

				$id = $m_pp->id;

				foreach ($data['detail'] as $k_det => $v_det) {
					$m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
                    $m_dpp->id_header = $id;
                    $m_dpp->no_sj = $v_det['no_sj'];
                    $m_dpp->no_inv = $v_det['no_inv'];
                    $m_dpp->cn = $v_det['cn'];
                    $m_dpp->dn = $v_det['dn'];
                    $m_dpp->nilai = $v_det['nilai'];
                    $m_dpp->tagihan = $v_det['tagihan'];
                    $m_dpp->penyesuaian = $v_det['penyesuaian'];
                    $m_dpp->ket_penyesuaian = $v_det['ket_penyesuaian'];
                    $m_dpp->status = $v_det['status'];
                    $m_dpp->sisa_tagihan = $v_det['sisa_tagihan'];
					$m_dpp->save();
				}

                if ( isset($data['dn']) && !empty($data['dn']) ) {
                    foreach ($data['dn'] as $k_dn => $v_dn) {
                        $m_ppdn = new \Model\Storage\PembayaranPelangganDn_model();
                        $m_ppdn->id_header = $id;
                        $m_ppdn->saldo = $v_dn['saldo'];
                        $m_ppdn->sisa_saldo = $v_dn['sisa_saldo'];
                        $m_ppdn->pakai = $v_dn['pakai'];
                        $m_ppdn->id_dn = $v_dn['id'];
                        $m_ppdn->save();

                        foreach ($v_dn['detail'] as $k_det => $v_det) {
                            $m_conf = new \Model\Storage\Conf();
                            $sql = "
                                select * from dn where id = ".$v_det['id_dn']."
                            ";
                            $d_dn = $m_conf->hydrateRaw( $sql );

                            $m_conf = new \Model\Storage\Conf();
                            $sql = "
                                select * from det_pembayaran_pelanggan where no_inv = '".$k_det."' and id_header = ".$id."
                            ";
                            $d_det = $m_conf->hydrateRaw( $sql );

                            if ( $d_dn->count() > 0 && $d_det->count() > 0 ) {
                                $d_dn = $d_dn->toArray()[0];
                                $d_det = $d_det->toArray()[0];

                                $m_dppcd = new \Model\Storage\DetPembayaranPelangganCnDn_model();
                                $m_dppcd->id_header = $d_det['id'];
                                $m_dppcd->nomor_cn_dn = $d_dn['nomor'];
                                $m_dppcd->nominal = $v_det['jml_bayar'];
                                $m_dppcd->save();
                            }
                        }
                    }
                }

                if ( isset($data['cn']) && !empty($data['cn']) ) {
                    foreach ($data['cn'] as $k_cn => $v_cn) {
                        $m_ppcn = new \Model\Storage\PembayaranPelangganCn_model();
                        $m_ppcn->id_header = $id;
                        $m_ppcn->saldo = $v_cn['saldo'];
                        $m_ppcn->sisa_saldo = $v_cn['sisa_saldo'];
                        $m_ppcn->pakai = $v_cn['pakai'];
                        $m_ppcn->id_cn = $v_cn['id'];
                        $m_ppcn->save();

                        foreach ($v_cn['detail'] as $k_det => $v_det) {
                            $m_conf = new \Model\Storage\Conf();
                            $sql = "
                                select * from cn where id = ".$v_det['id_cn']."
                            ";
                            $d_cn = $m_conf->hydrateRaw( $sql );

                            $m_conf = new \Model\Storage\Conf();
                            $sql = "
                                select * from det_pembayaran_pelanggan where no_inv = '".$k_det."' and id_header = ".$id."
                            ";
                            $d_det = $m_conf->hydrateRaw( $sql );

                            if ( $d_cn->count() > 0 && $d_det->count() > 0 ) {
                                $d_cn = $d_cn->toArray()[0];
                                $d_det = $d_det->toArray()[0];

                                $m_dppcd = new \Model\Storage\DetPembayaranPelangganCnDn_model();
                                $m_dppcd->id_header = $d_det['id'];
                                $m_dppcd->nomor_cn_dn = $d_cn['nomor'];
                                $m_dppcd->nominal = $v_det['jml_bayar'];
                                $m_dppcd->save();
                            }
                        }
                    }
                }

				$d_pp = $m_pp->where('id', $id)->with(['detail'])->first();

				$deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            	Modules::run( 'base/event/save', $d_pp, $deskripsi_log );

                if ( $data['saldo'] > 0 ) {
                    $m_conf = new \Model\Storage\Conf();
                    $sql = "
                        select * from saldo_pelanggan sp
                        where
                            sp.no_pelanggan = '".$data['pelanggan']."' and
                            sp.perusahaan = '".$data['perusahaan']."' and
                            sp.saldo > 0
                        order by
                            sp.tgl_trans desc,
                            sp.id desc
                    ";
                    $d_conf = $m_conf->hydrateRaw( $sql );

                    $saldo_lama = 0;
                    if ( $d_conf->count() > 0 ) {
                        $d_conf = $d_conf->toArray()[0];

                        $saldo_lama = $d_conf['saldo'];
                    }

                    $m_sp = new \Model\Storage\SaldoPelanggan_model();
                    $m_sp->jenis_saldo = 'K';
                    $m_sp->no_pelanggan = $data['pelanggan'];
                    $m_sp->id_trans = $id;
                    $m_sp->tgl_trans = date('Y-m-d');
                    $m_sp->jenis_trans = 'pembayaran_pelanggan';
                    $m_sp->nominal = $data['saldo'];
                    $m_sp->saldo = (($saldo_lama - $data['saldo']) > 0) ? ($saldo_lama - $data['saldo']) : 0;
                    $m_sp->perusahaan = $data['perusahaan'];
                    $m_sp->save();
                }

                if ( $data['lebih_kurang'] > 0 ) {
                    $m_conf = new \Model\Storage\Conf();
                    $sql = "
                        select * from saldo_pelanggan sp
                        where
                            sp.no_pelanggan = '".$data['pelanggan']."' and
                            sp.perusahaan = '".$data['perusahaan']."' and
                            sp.saldo > 0
                        order by
                            sp.tgl_trans desc,
                            sp.id desc
                    ";
                    $d_conf = $m_conf->hydrateRaw( $sql );

                    $saldo_lama = 0;
                    if ( $d_conf->count() > 0 ) {
                        $d_conf = $d_conf->toArray()[0];
                    
                        $saldo_lama = $d_conf['saldo'];
                    }

                    $m_sp = new \Model\Storage\SaldoPelanggan_model();
                    $m_sp->jenis_saldo = 'D';
                    $m_sp->no_pelanggan = $data['pelanggan'];
                    $m_sp->id_trans = $id;
                    $m_sp->tgl_trans = date('Y-m-d');
                    $m_sp->jenis_trans = 'pembayaran_pelanggan';
                    $m_sp->nominal = $data['lebih_kurang'];
                    $m_sp->saldo = ($saldo_lama + $data['lebih_kurang']);
                    $m_sp->perusahaan = $data['perusahaan'];
                    $m_sp->save();
                }

                // $m_conf = new \Model\Storage\Conf();
                // $sql = "exec insert_jurnal NULL, NULL, NULL, 0, 'pembayaran_pelanggan', ".$id.", NULL, 1";
                // $d_conf = $m_conf->hydrateRaw( $sql );

	        	$this->result['status'] = 1;
	        	$this->result['content'] = array('id' => $id);
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
			$_d_pp = $m_pp->where('id', $id)->with(['detail'])->first();

			$deskripsi_log = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
        	Modules::run( 'base/event/update', $_d_pp, $deskripsi_log );

            $m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
			$d_dpp = $m_dpp->where('id_header', $id)->get();

            if ( $d_dpp->count() > 0 ) {
                $d_dpp = $d_dpp->toArray();

                foreach ($d_dpp as $k_dpp => $v_dpp) {
                    $m_dppcd = new \Model\Storage\DetPembayaranPelangganCnDn_model();
                    $m_dppcd->where('id_header', $v_dpp['id'])->delete();

                    $m_dpp->where('id', $v_dpp['id'])->delete();
                }
            }

            $m_ppcn = new \Model\Storage\PembayaranPelangganCn_model();
            $m_ppcn->where('id_header', $id)->delete();

            $m_ppdn = new \Model\Storage\PembayaranPelangganDn_model();
            $m_ppdn->where('id_header', $id)->delete();

            $m_pp = new \Model\Storage\PembayaranPelanggan_model();
            $m_pp->where('id', $id)->delete();

            $saldo = $_d_pp->saldo;
            $lebih_kurang = $_d_pp->lebih_kurang;
            if ( $lebih_kurang > 0 ) {
                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select * from saldo_pelanggan sp
                    where
                        sp.no_pelanggan = '".$_d_pp->no_pelanggan."' and
                        sp.perusahaan = '".$_d_pp->perusahaan."' and
                        sp.saldo > 0
                    order by
                        sp.tgl_trans desc,
                        sp.id desc
                ";
                $d_conf = $m_conf->hydrateRaw( $sql );

                $saldo_lama = 0;
                if ( $d_conf->count() > 0 ) {
                    $d_conf = $d_conf->toArray()[0];

                    $saldo_lama = $d_conf['saldo'];
                }

                $m_sp = new \Model\Storage\SaldoPelanggan_model();
                $m_sp->jenis_saldo = 'K';
                $m_sp->no_pelanggan = $_d_pp->no_pelanggan;
                $m_sp->id_trans = $id;
                $m_sp->tgl_trans = date('Y-m-d');
                $m_sp->jenis_trans = 'pembayaran_pelanggan';
                $m_sp->nominal = $lebih_kurang;
                $m_sp->saldo = (($saldo_lama - $lebih_kurang) > 0) ? ($saldo_lama - $lebih_kurang) : 0;
                $m_sp->perusahaan = $_d_pp->perusahaan;
                $m_sp->save();
            }

            if ( $saldo > 0 ) {
                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select * from saldo_pelanggan sp
                    where
                        sp.no_pelanggan = '".$_d_pp->no_pelanggan."' and
                        sp.perusahaan = '".$_d_pp->perusahaan."' and
                        sp.saldo > 0
                    order by
                        sp.tgl_trans desc,
                        sp.id desc
                ";
                $d_conf = $m_conf->hydrateRaw( $sql );

                $saldo_lama = 0;
                if ( $d_conf->count() > 0 ) {
                    $d_conf = $d_conf->toArray()[0];

                    $saldo_lama = $d_conf['saldo'];
                }
                
                $m_sp = new \Model\Storage\SaldoPelanggan_model();
                $m_sp->jenis_saldo = 'D';
                $m_sp->no_pelanggan = $_d_pp->no_pelanggan;
                $m_sp->id_trans = $id;
                $m_sp->tgl_trans = date('Y-m-d');
                $m_sp->jenis_trans = 'pembayaran_pelanggan';
                $m_sp->nominal = $saldo;
                $m_sp->saldo = ($saldo_lama + $saldo);
                $m_sp->perusahaan = $_d_pp->perusahaan;
                $m_sp->save();
            }

            // $m_conf = new \Model\Storage\Conf();
            // $sql = "exec insert_jurnal NULL, NULL, NULL, 0, 'pembayaran_pelanggan', ".$id.", ".$id.", 3";
            // $d_conf = $m_conf->hydrateRaw( $sql );

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