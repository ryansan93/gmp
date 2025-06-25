<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KonfirmasiPembayaranOaPakan extends Public_Controller
{
    private $url;
	private $hakAkses;
	function __construct()
	{
		parent::__construct();
        $this->url = $this->current_base_uri;
		$this->hakAkses = hakAkses($this->url);
	}

	public function index()
	{
		if ( $this->hakAkses['a_view'] == 1 ) {
			$this->add_external_js(array(
				'assets/select2/js/select2.min.js',
				'assets/pembayaran/konfirmasi_pembayaran_oa_pakan/js/konfirmasi-pembayaran-oa-pakan.js'
			));
			$this->add_external_css(array(
				'assets/select2/css/select2.min.css',
				'assets/pembayaran/konfirmasi_pembayaran_oa_pakan/css/konfirmasi-pembayaran-oa-pakan.css'
			));

			$data = $this->includes;

			$data['title_menu'] = 'Konfirmasi Pembayaran OA Pakan';

            $perusahaan = $this->getPerusahaan();

			$content['add_form'] = $this->addForm($perusahaan);
            $content['riwayat'] = $this->riwayat($perusahaan);

			$content['akses'] = $this->hakAkses;
			$data['view'] = $this->load->view('pembayaran/konfirmasi_pembayaran_oa_pakan/index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

    public function loadForm()
    {
        $params = $this->input->get('params');
        $edit = $this->input->get('edit');

        $id = $params['id'];

        $content = array();
        $html = "url not found";
        
        if ( !empty($id) && $edit != 'edit' ) {
            // NOTE: view data BASTTB (ajax)
            $html = $this->viewForm( $id );
        } else if ( !empty($id) && $edit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $perusahaan = $this->getPerusahaan();
            $html = $this->editForm($id, $perusahaan);
        }else{
            $perusahaan = $this->getPerusahaan();
            $html = $this->addForm($perusahaan);
        }

        echo $html;
    }

	public function getUnit()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'UN')->orderBy('nama', 'asc')->get();

        $data = null;
        if ( $d_wilayah->count() > 0 ) {
            $d_wilayah = $d_wilayah->toArray();

            foreach ($d_wilayah as $k_wil => $v_wil) {
                $nama = trim(str_replace('KAB', '', str_replace('KOTA', '', strtoupper($v_wil['nama']))));
                $data[ $nama.' - '.$v_wil['kode'] ] = array(
                    'nama' => $nama,
                    'kode' => $v_wil['kode']
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function getPerusahaan()
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
                    'kode' => $d_perusahaan->kode
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function getEkspedisi()
    {
        $data = null;

        $m_ekspedisi = new \Model\Storage\Ekspedisi_model();
        $sql = "
            select 
                eks.id,
                eks.nomor,
                eks.nama
            from ekspedisi eks 
            right join 
                (select max(id) as id, nomor from ekspedisi group by nomor) as e 
                on
                    eks.id = e.id
            where
                eks.mstatus = 1 
            group by
                eks.id,
                eks.nomor,
                eks.nama
            order by eks.nama asc
        ";
        $d_ekspedisi = $m_ekspedisi->hydrateRaw( $sql );
        if ( $d_ekspedisi->count() > 0 ) {
            $data = $d_ekspedisi->toArray();
        }

        return $data;
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        foreach ($params['perusahaan'] as $k => $val) {
            $d_perusahaan = null;
            if ( $val != 'all' ) {
                $d_perusahaan = $m_perusahaan->where('kode', $val)->get();
            } else {
                $d_perusahaan = $m_perusahaan->get();
            }

            if ( !empty($d_perusahaan) ) {
                $d_perusahaan = $d_perusahaan->toArray();

                foreach ($d_perusahaan as $k_perusahaan => $v_perusahaan) {
                    $kode_perusahaan[] = $v_perusahaan['kode'];
                }
            }
        }

        $m_ekspedisi = new \Model\Storage\Ekspedisi_model();
        foreach ($params['ekspedisi'] as $k => $val) {
            $d_ekspedisi = null;
            if ( $val != 'all' ) {
                $d_ekspedisi = $m_ekspedisi->where('nomor', $val)->get();
            } else {
                $d_ekspedisi = $m_ekspedisi->get();
            }

            if ( !empty($d_ekspedisi) ) {
                $d_ekspedisi = $d_ekspedisi->toArray();

                foreach ($d_ekspedisi as $k_ekspedisi => $v_ekspedisi) {
                    $kode_ekspedisi[] = $v_ekspedisi['nomor'];
                }
            }
        }

        $m_kpd = new \Model\Storage\KonfirmasiPembayaranOaPakan_model();
        $d_kpd = $m_kpd->whereBetween('tgl_bayar', [$start_date, $end_date])
                       ->whereIn('perusahaan', $kode_perusahaan)
                       ->whereIn('ekspedisi_id', $kode_ekspedisi)
                       ->with(['d_perusahaan'])->orderBy('tgl_bayar', 'desc')->get();

        if ( $d_kpd->count() > 0 ) {
            $d_kpd = $d_kpd->toArray();
        }

        $content['data'] = $d_kpd;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_oa_pakan/listRiwayat', $content, true);

        echo $html;
    }

    public function getDataOa()
    {
    	$params = $this->input->get('params');

    	$html = $this->getDataOaHtml( $params );

        echo $html;
    }

    public function getDataOaHtml($params, $edit = null)
    {
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];
        $ekspedisi = $params['ekspedisi'];
        $filter = $params['filter'];
        $jenis_kirim = $params['jenis_kirim'];

        $m_wilayah = new \Model\Storage\Wilayah_model();

        $kode_unit = null;
        foreach ($params['kode_unit'] as $k_ku => $v_ku) {
            $d_wilayah = null;
            if ( $v_ku != 'all' ) {
                $d_wilayah = $m_wilayah->where('jenis', 'UN')->where('kode', $v_ku)->get();
            } else {
                $d_wilayah = $m_wilayah->where('jenis', 'UN')->get();
            }

            if ( !empty($d_wilayah) ) {
                $d_wilayah = $d_wilayah->toArray();

                foreach ($d_wilayah as $k_wil => $v_wil) {
                    $kode_unit[] = $v_wil['kode'];
                }
            }
        }

        $m_kp = new \Model\Storage\KirimPakan_model();
        $sql = "
            select 
                kp.*, 
                tp.tgl_terima, 
                kpoapd.no_sj as sj_bayar 
            from kirim_pakan kp
            right join
                (
                    select max(id) as id, tgl_terima, id_kirim_pakan from terima_pakan where tgl_terima between '".$start_date."' and '".$end_date."' group by tgl_terima, id_kirim_pakan
                ) tp
                on
                    kp.id = tp.id_kirim_pakan
            right join
                wilayah w
                on
                    kp.no_order like '%'+w.kode+'%'
            left join
                konfirmasi_pembayaran_oa_pakan_det kpoapd
                on
                    kpoapd.no_sj = kp.no_sj
            where
                kp.jenis_kirim in ('".implode("', '", $jenis_kirim)."') and
                tp.tgl_terima between '".$start_date."' and '".$end_date."' and
                w.kode in ('".implode("', '", $kode_unit)."') and
                kpoapd.no_sj is null and
                kp.ekspedisi_id = '".$ekspedisi."'
            group by
                kp.id,
                kp.tgl_trans,
                kp.tgl_kirim,
                kp.no_order,
                kp.jenis_kirim,
                kp.asal,
                kp.jenis_tujuan,
                kp.tujuan,
                kp.ekspedisi,
                kp.ekspedisi_id,
                kp.no_polisi,
                kp.sopir,
                kp.no_sj,
                kp.ongkos_angkut,
                tp.tgl_terima,
                kpoapd.no_sj,
                kp.no_order
        ";
        $d_kp = $m_kp->hydrateRaw( $sql );

        // cetak_r( $sql );

        $data = null;
        if ( $d_kp->count() > 0 ) {
            $d_kp = $d_kp->toArray();

            foreach ($d_kp as $k => $val) {
                if ( in_array('not_mutasi', $filter) ) {
                    if ( $val['jenis_kirim'] == 'opks' && in_array('opks', $jenis_kirim) ) {
                        $m_kp_oa = new \Model\Storage\KirimPakan_model();
                        $sql = "
                            select 
                                tp.tgl_terima,
                                kp.no_order,
                                kp.jenis_kirim,
                                kp.asal,
                                kp.jenis_tujuan,
                                kp.tujuan,
                                kp.ekspedisi,
                                kp.no_polisi,
                                kp.sopir,
                                kp.no_sj,
                                kp.ongkos_angkut,
                                supl.nama as asal,
                                g.nama as tujuan,
                                kp.tujuan as kode_tujuan,
                                (sum(dkp.jumlah) * kp.ongkos_angkut) as sub_total
                            from kirim_pakan kp
                            right join
                                (
                                    select max(id) as id, tgl_terima, id_kirim_pakan from terima_pakan group by tgl_terima, id_kirim_pakan
                                ) tp
                                on
                                    kp.id = tp.id_kirim_pakan
                            right join
                                det_kirim_pakan dkp
                                on
                                    kp.id = dkp.id_header
                            right join
                                (
                                    select p1.* from pelanggan p1
                                    right join
                                        (
                                            select max(id) as id, nomor from pelanggan p group by nomor 
                                        ) p2
                                        on
                                            p1.id = p2.id
                                    where 
                                        p1.tipe = 'supplier' and p1.jenis <> 'ekspedisi'
                                ) supl
                                on
                                    kp.asal = supl.nomor
                            right join
                                gudang g
                                on
                                    kp.tujuan = g.id
                            where
                                kp.no_order = '".$val['no_order']."' and
                                kp.ekspedisi_id = '".$ekspedisi."' and
                                kp.ongkos_angkut > 0
                            group by
                                tp.tgl_terima,
                                kp.no_order,
                                kp.jenis_kirim,
                                kp.asal,
                                kp.jenis_tujuan,
                                kp.tujuan,
                                kp.ekspedisi,
                                kp.ekspedisi_id,
                                kp.no_polisi,
                                kp.sopir,
                                kp.no_sj,
                                kp.ongkos_angkut,
                                supl.nama,
                                g.nama,
                                kp.tujuan
                        ";
                        $d_kp_oa = $m_kp_oa->hydrateRaw( $sql );

                        // cetak_r( $sql );

                        if ( $d_kp_oa->count() > 0 ) {
                            $d_kp_oa = $d_kp_oa->toArray();

                            $key = str_replace('-', '', $d_kp_oa[0]['tgl_terima']).' | '.$val['no_order'];

                            $data[ $key ] = array(
                                'tgl_mutasi' => $d_kp_oa[0]['tgl_terima'],
                                'ekspedisi' => $d_kp_oa[0]['ekspedisi'],
                                'no_polisi' => $d_kp_oa[0]['no_polisi'],
                                'no_sj' => $d_kp_oa[0]['no_sj'],
                                'asal' => $d_kp_oa[0]['asal'],
                                'tujuan' => $d_kp_oa[0]['tujuan'],
                                'sub_total' => $d_kp_oa[0]['sub_total'],
                            );
                        }
                    }

                    if ( $val['jenis_kirim'] == 'opkg' && in_array('opkg', $jenis_kirim) ) {
                        $m_kp_oa = new \Model\Storage\KirimPakan_model();
                        $sql = "
                            select 
                                tp.tgl_terima,
                                kp.no_order,
                                kp.jenis_kirim,
                                kp.asal,
                                kp.jenis_tujuan,
                                kp.tujuan,
                                kp.ekspedisi,
                                kp.no_polisi,
                                kp.sopir,
                                kp.no_sj,
                                kp.ongkos_angkut,
                                g.nama as asal,
                                m.nama as tujuan,
                                SUBSTRING(kp.tujuan, 10, 2) as kandang,
                                (sum(dkp.jumlah) * kp.ongkos_angkut) as sub_total
                            from kirim_pakan kp
                            right join
                                (
                                    select max(id) as id, tgl_terima, id_kirim_pakan from terima_pakan group by tgl_terima, id_kirim_pakan
                                ) tp
                                on
                                    kp.id = tp.id_kirim_pakan
                            right join
                                det_kirim_pakan dkp
                                on
                                    kp.id = dkp.id_header
                            right join
                                gudang g
                                on
                                    kp.asal = g.id
                            right join
                                (select nim, max(mitra) as id_mitra from mitra_mapping group by nim) mm
                                on
                                    SUBSTRING(kp.tujuan, 0, 8) = mm.nim
                            right join
                                mitra m
                                on
                                    mm.id_mitra = m.id
                            where
                                kp.no_order = '".$val['no_order']."' and
                                m.perusahaan = '".$perusahaan."' and
                                kp.ekspedisi_id = '".$ekspedisi."' and
                                (g.mutasi = 0 or g.mutasi is null)
                            group by
                                tp.tgl_terima,
                                kp.no_order,
                                kp.jenis_kirim,
                                kp.asal,
                                kp.jenis_tujuan,
                                kp.tujuan,
                                kp.ekspedisi,
                                kp.ekspedisi_id,
                                kp.no_polisi,
                                kp.sopir,
                                kp.no_sj,
                                kp.ongkos_angkut,
                                g.nama,
                                m.nama,
                                kp.tujuan
                        ";
                        $d_kp_oa = $m_kp_oa->hydrateRaw( $sql );

                        // cetak_r($sql, 1);

                        if ( $d_kp_oa->count() > 0 ) {
                            $d_kp_oa = $d_kp_oa->toArray();

                            $key = str_replace('-', '', $d_kp_oa[0]['tgl_terima']).' | '.$val['no_order'];

                            $data[ $key ] = array(
                                'tgl_mutasi' => $d_kp_oa[0]['tgl_terima'],
                                'ekspedisi' => $d_kp_oa[0]['ekspedisi'],
                                'no_polisi' => $d_kp_oa[0]['no_polisi'],
                                'no_sj' => $d_kp_oa[0]['no_sj'],
                                'asal' => $d_kp_oa[0]['asal'],
                                'tujuan' => $d_kp_oa[0]['tujuan'].' ( KDG : '.(int)$d_kp_oa[0]['kandang'].' )',
                                'sub_total' => $d_kp_oa[0]['sub_total'],
                            );
                        }
                    }
                }

                // if ( in_array('mutasi', $filter) ) {
                //     if ( $val['jenis_kirim'] == 'opkp' && in_array('opkp', $jenis_kirim) ) {
                //     }
                // }
            }
        }

        if ( in_array('mutasi', $filter) ) {
            $m_kp_oa = new \Model\Storage\KirimPakan_model();
            $sql = "
                select 
                    _data.tgl_terima,
                    _data.jenis_kirim,
                    rs_asal.nama as asal,
                    rs_tujuan.nama as tujuan,
                    _data.ekspedisi,
                    _data.no_polisi,
                    _data.sopir,
                    _data.no_sj,
                    oapp.ongkos_angkut as sub_total,
                    _data.kode_trans
                from (
                    select 
                        tp.tgl_terima, 
                        kp.no_sj, 
                        kp.jenis_kirim, 
                        cast(kp.asal as varchar(11)) as asal, 
                        cast(kp.tujuan as varchar(11)) as tujuan, 
                        kp.no_order as kode_trans,
                        kp.ekspedisi,
                        kp.no_polisi,
                        kp.sopir,
                        kp.ekspedisi_id
                    from kirim_pakan kp
                    right join
                        (
                            select max(id) as id, tgl_terima, id_kirim_pakan from terima_pakan group by tgl_terima, id_kirim_pakan
                        ) tp
                        on
                            kp.id = tp.id_kirim_pakan

                    union all

                    select 
                        rp.tgl_retur as tgl_terima, 
                        rp.no_retur as no_sj, 
                        'opkp' as jenis_kirim, 
                        cast(rp.id_asal as varchar(11)) as asal, 
                        cast(rp.id_tujuan as varchar(11)) as tujuan, 
                        rp.no_order as kode_trans,
                        rp.ekspedisi,
                        rp.no_polisi,
                        rp.sopir,
                        rp.ekspedisi_id
                    from retur_pakan rp
                ) _data
                right join
                    (
                        select cast(rs1.noreg as varchar(11)) as id_asal, m.nama+' (KDG : '+cast(cast(SUBSTRING(rs1.noreg, 10, 2) as int) as varchar(2))+')' as nama, m.perusahaan from rdim_submit rs1
                        right join
                            (select max(mitra) as id, nim from mitra_mapping group by nim) mm
                            on
                                rs1.nim = mm.nim
                        right join
                            mitra m
                            on
                                mm.id = m.id
                        
                        union all
                        
                        select cast(g.id as varchar(11)) as id_asal, g.nama as nama, g.perusahaan from gudang g
                    ) rs_asal
                    on
                        _data.asal = rs_asal.id_asal
                right join
                    (
                        select cast(rs1.noreg as varchar(11)) as id_tujuan, m.nama+' (KDG : '+cast(cast(SUBSTRING(rs1.noreg, 10, 2) as int) as varchar(2))+')' as nama, m.perusahaan from rdim_submit rs1
                        right join
                            (select max(mitra) as id, nim from mitra_mapping group by nim) mm
                            on
                                rs1.nim = mm.nim
                        right join
                            mitra m
                            on
                                mm.id = m.id
                        
                        union all
                        
                        select cast(g.id as varchar(11)) as id_tujuan, g.nama as nama, g.perusahaan from gudang g
                    ) rs_tujuan
                    on
                        _data.tujuan = rs_tujuan.id_tujuan
                right join
                    oa_pindah_pakan oapp
                    on
                        _data.no_sj = oapp.no_sj
                where
                    _data.jenis_kirim in ('".implode("', '", array_map('strtolower', $jenis_kirim))."') and
                    rs_asal.perusahaan = '".$perusahaan."' and
                    rs_tujuan.perusahaan = '".$perusahaan."' and
                    _data.ekspedisi_id = '".$ekspedisi."' and
                    _data.tgl_terima between '".$start_date."' and '".$end_date."' and
                    SUBSTRING(_data.kode_trans, 4, 3) in ('".implode("', '", $kode_unit)."')
                group by
                    _data.tgl_terima,
                    _data.jenis_kirim,
                    rs_asal.nama,
                    rs_tujuan.nama,
                    _data.ekspedisi,
                    _data.ekspedisi_id,
                    _data.no_polisi,
                    _data.sopir,
                    _data.no_sj,
                    _data.asal,
                    _data.tujuan,
                    oapp.ongkos_angkut,
                    _data.kode_trans
            ";

            $d_kp_oa = $m_kp_oa->hydrateRaw( $sql );

            if ( $d_kp_oa->count() > 0 ) {
                $d_kp_oa = $d_kp_oa->toArray();

                foreach ($d_kp_oa as $key => $value) {
                    $key = str_replace('-', '', $value['tgl_terima']).' | '.$value['kode_trans'];

                    $data[ $key ] = array(
                        'tgl_mutasi' => $value['tgl_terima'],
                        'ekspedisi' => $value['ekspedisi'],
                        'no_polisi' => $value['no_polisi'],
                        'no_sj' => $value['no_sj'],
                        'asal' => $value['asal'], //.' ( KDG : '.(int)$value['kandang_asal'].' )',
                        'tujuan' => $value['tujuan'], //.' ( KDG : '.(int)$value['kandang_tujuan'].' )',
                        'sub_total' => $value['sub_total']
                    );
                }
            }
        }

        if ( !empty($data) ) {
            ksort($data);
        }

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_oa_pakan/listOa', $content, true);

        return $html;
    }

    public function riwayat($perusahaan)
    {
        $content['unit'] = $this->getUnit();
        $content['ekspedisi'] = $this->getEkspedisi();
        $content['perusahaan'] = $perusahaan;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_oa_pakan/riwayat', $content, true);

        return $html;
    }

	public function addForm($perusahaan)
	{
        $content['unit'] = $this->getUnit();
        $content['ekspedisi'] = $this->getEkspedisi();
		$content['perusahaan'] = $perusahaan;
		$html = $this->load->view('pembayaran/konfirmasi_pembayaran_oa_pakan/addForm', $content, true);

		return $html;
	}

    public function viewForm($id)
    {
        $m_kpoap = new \Model\Storage\KonfirmasiPembayaranOaPakan_model();
        $sql = "
            select data.*, kpoap1.lampiran from (
                select 
                    kpoap.id,
                    kpoap.nomor,
                    kpoap.tgl_bayar,
                    kpoap.periode,
                    kpoap.perusahaan,
                    kpoap.ekspedisi,
                    kpoap.bank,
                    kpoap.rekening,
                    kpoap.total,
                    kpoap.sub_total,
                    kpoap.potongan_pph_23,
                    kpoap.materai,
                    kpoap.lunas,
                    kpoap.ekspedisi_id,
                    kpoap.invoice,
                    p.perusahaan as nama_perusahaan,
                    kpoapd.id_header,
                    kpoapd.tgl_mutasi,
                    kpoapd.no_sj,
                    kpoapd.no_polisi,
                    kpoapd.total as det_total,
                    kp.jenis_kirim
                from konfirmasi_pembayaran_oa_pakan_det kpoapd
                right join
                    konfirmasi_pembayaran_oa_pakan kpoap
                    on
                        kpoapd.id_header = kpoap.id
                right join
                    (
                        select * from (

                            select no_sj, jenis_kirim from kirim_pakan

                            union all

                            select no_retur as no_sj, jenis_retur as jenis_kirim from retur_pakan
                        ) data
                    ) kp
                    on
                        kp.no_sj = kpoapd.no_sj
                right join
                    (
                        select p1.* from perusahaan p1
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) p2
                            on
                                p1.id = p2.id
                    ) p
                    on
                        kpoap.perusahaan = p.kode
                where
                    kpoap.id = ".$id."
                group by
                    kpoap.id,
                    kpoap.nomor,
                    kpoap.tgl_bayar,
                    kpoap.periode,
                    kpoap.perusahaan,
                    kpoap.ekspedisi,
                    kpoap.bank,
                    kpoap.rekening,
                    kpoap.total,
                    kpoap.sub_total,
                    kpoap.potongan_pph_23,
                    kpoap.materai,
                    kpoap.lunas,
                    kpoap.ekspedisi_id,
                    kpoap.invoice,
                    p.perusahaan,
                    kpoapd.id_header,
                    kpoapd.tgl_mutasi,
                    kpoapd.no_sj,
                    kpoapd.no_polisi,
                    kpoapd.total,
                    kp.jenis_kirim
            ) data
            right join
                konfirmasi_pembayaran_oa_pakan kpoap1
                on
                    data.id = kpoap1.id
            where 
                data.id is not null
        ";
        $d_kpoap = $m_kpoap->hydrateRaw( $sql );

        $data = null;
        if ( $d_kpoap ) {
            $d_kpoap = $d_kpoap->toArray();

            $detail = null;
            foreach ($d_kpoap as $k_kpoap => $v_kpoap) {
                $asal = null;
                $tujuan = null;

                if ( $v_kpoap['jenis_kirim'] == 'opks' ) {
                    $m_kp_oa = new \Model\Storage\KirimPakan_model();
                    $sql = "
                        select 
                            supl.nama as asal,
                            g.nama as tujuan,
                            kp.tujuan as kode_tujuan
                        from kirim_pakan kp
                        right join
                            (
                                select p1.* from pelanggan p1
                                right join
                                    (
                                        select max(id) as id, nomor from pelanggan p group by nomor 
                                    ) p2
                                    on
                                        p1.id = p2.id
                                where 
                                    p1.tipe = 'supplier' and p1.jenis <> 'ekspedisi'
                            ) supl
                            on
                                kp.asal = supl.nomor
                        right join
                            gudang g
                            on
                                kp.tujuan = g.id
                        where
                            kp.no_sj = '".$v_kpoap['no_sj']."'
                        group by
                            supl.nama,
                            g.nama,
                            kp.tujuan
                    ";
                    $d_kp_oa = $m_kp_oa->hydrateRaw( $sql );

                    if ( $d_kp_oa->count() > 0 ) {
                        $d_kp_oa = $d_kp_oa->toArray();

                        $asal = $d_kp_oa[0]['asal'];
                        $tujuan = $d_kp_oa[0]['tujuan'];
                    }
                }

                if ( $v_kpoap['jenis_kirim'] == 'opkg' ) {
                    $m_kp_oa = new \Model\Storage\KirimPakan_model();
                    $sql = "
                        select 
                            g.nama as asal,
                            m.nama as tujuan,
                            SUBSTRING(kp.tujuan, 10, 2) as kandang
                        from kirim_pakan kp
                        right join
                            gudang g
                            on
                                kp.asal = g.id
                        right join
                            (select nim, max(mitra) as id_mitra from mitra_mapping group by nim) mm
                            on
                                SUBSTRING(kp.tujuan, 0, 8) = mm.nim
                        right join
                            mitra m
                            on
                                mm.id_mitra = m.id
                        where
                            kp.no_sj = '".$v_kpoap['no_sj']."'
                        group by
                            g.nama,
                            m.nama,
                            kp.tujuan
                    ";
                    $d_kp_oa = $m_kp_oa->hydrateRaw( $sql );

                    if ( $d_kp_oa->count() > 0 ) {
                        $d_kp_oa = $d_kp_oa->toArray();

                        $asal = $d_kp_oa[0]['asal'];
                        $tujuan = $d_kp_oa[0]['tujuan'].' ( KDG : '.(int)$d_kp_oa[0]['kandang'].' )';
                    }
                }

                if ( $v_kpoap['jenis_kirim'] == 'opkp' ) {
                    $m_kp_oa = new \Model\Storage\KirimPakan_model();
                    $sql = "
                        select 
                            mitra_asal.nama as asal,
                            mitra_tujuan.nama as tujuan,
                            mitra_asal.kdg as kandang_asal,
                            mitra_tujuan.kdg as kandang_tujuan
                        from (
                            select * from (
                                select no_sj, jenis_kirim, cast(asal as varchar(11)) as asal, cast(tujuan as varchar(11)) as tujuan from kirim_pakan
    
                                union all
    
                                select no_retur as no_sj, jenis_retur as jenis_kirim, cast(id_asal as varchar(11)) as asal, cast(id_tujuan as varchar(11)) as tujuan from retur_pakan
                            ) data
                        ) kp
                        right join
                            (
                                select 
                                    cast(rs.noreg as varchar(11)) as id_asal,
                                    SUBSTRING(cast(rs.noreg as varchar(11)), 10, 2) as kdg,
                                    m.nama, 
                                    m.perusahaan
                                from rdim_submit rs
                                right join
                                    (select nim, max(mitra) as id_mitra from mitra_mapping group by nim) mm
                                    on
                                        rs.nim = mm.nim
                                right join
                                    mitra m
                                    on
                                        m.id = mm.id_mitra

                                union all
                
                                select cast(g.id as varchar(11)) as id_asal, '' as kdg, g.nama as nama, g.perusahaan from gudang g
                            ) mitra_asal
                            on
                                kp.asal = mitra_asal.id_asal
                        right join
                            (
                                select 
                                    cast(rs.noreg as varchar(11)) as id_tujuan,
                                    SUBSTRING(cast(rs.noreg as varchar(11)), 10, 2) as kdg,
                                    m.nama, 
                                    m.perusahaan
                                from rdim_submit rs
                                right join
                                    (select nim, max(mitra) as id_mitra from mitra_mapping group by nim) mm
                                    on
                                        rs.nim = mm.nim
                                right join
                                    mitra m
                                    on
                                        m.id = mm.id_mitra

                                union all
                
                                select cast(g.id as varchar(11)) as id_tujuan, '' as kdg, g.nama as nama, g.perusahaan from gudang g
                            ) mitra_tujuan
                            on
                                kp.tujuan = mitra_tujuan.id_tujuan
                        where
                            kp.no_sj = '".$v_kpoap['no_sj']."'
                        group by
                            mitra_asal.nama,
                            mitra_tujuan.nama,
                            mitra_asal.kdg,
                            mitra_tujuan.kdg
                    ";
                    $d_kp_oa = $m_kp_oa->hydrateRaw( $sql );

                    if ( $d_kp_oa->count() > 0 ) {
                        $d_kp_oa = $d_kp_oa->toArray();
                        
                        $asal = $d_kp_oa[0]['asal'];
                        if ( !empty($d_kp_oa[0]['kandang_asal']) ) {
                            $asal = $d_kp_oa[0]['asal'].' ( KDG : '.(int)$d_kp_oa[0]['kandang_asal'].' )';
                        }
                        $tujuan = $d_kp_oa[0]['tujuan'];
                        if ( !empty($d_kp_oa[0]['kandang_tujuan']) ) {
                            $tujuan = $d_kp_oa[0]['tujuan'].' ( KDG : '.(int)$d_kp_oa[0]['kandang_tujuan'].' )';
                        }
                    }
                }

                $detail[] = array(
                    'id' => $v_kpoap['id'],
                    'id_header' => $v_kpoap['id_header'],
                    'tgl_mutasi' => $v_kpoap['tgl_mutasi'],
                    'no_sj' => $v_kpoap['no_sj'],
                    'ekspedisi' => $v_kpoap['ekspedisi'],
                    'no_polisi' => $v_kpoap['no_polisi'],
                    'sub_total' => $v_kpoap['det_total'],
                    'asal' => $asal,
                    'tujuan' => $tujuan
                );
            }

            $data = array(
                'id' => $d_kpoap[0]['id'],
                'nomor' => $d_kpoap[0]['nomor'],
                'tgl_bayar' => $d_kpoap[0]['tgl_bayar'],
                'periode' => $d_kpoap[0]['periode'],
                'nama_perusahaan' => $d_kpoap[0]['nama_perusahaan'],
                'perusahaan' => $d_kpoap[0]['perusahaan'],
                'ekspedisi' => $d_kpoap[0]['ekspedisi'],
                'bank' => $d_kpoap[0]['bank'],
                'rekening' => $d_kpoap[0]['rekening'],
                'total' => $d_kpoap[0]['total'],
                'sub_total' => $d_kpoap[0]['sub_total'],
                'potongan_pph_23' => $d_kpoap[0]['potongan_pph_23'],
                'materai' => $d_kpoap[0]['materai'],
                'lunas' => $d_kpoap[0]['lunas'],
                'invoice' => $d_kpoap[0]['invoice'],
                'lampiran' => $d_kpoap[0]['lampiran'],
                'detail' => $detail
            );
        }

        $content['akses'] = $this->hakAkses;
        $content['data'] = $data;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_oa_pakan/viewForm', $content, true);

        return $html;
    }

    public function editForm($id, $perusahaan)
    {
        $m_kpoap = new \Model\Storage\KonfirmasiPembayaranOaPakan_model();
        $sql = "
            select 
                kpoap.id,
                kpoap.nomor,
                kpoap.tgl_bayar,
                kpoap.periode,
                kpoap.perusahaan,
                kpoap.ekspedisi,
                kpoap.bank,
                kpoap.rekening,
                kpoap.total,
                kpoap.sub_total,
                kpoap.potongan_pph_23,
                kpoap.materai,
                kpoap.lunas,
                kpoap.ekspedisi_id,
                kpoap.invoice,
                p.perusahaan as nama_perusahaan,
                kpoapd.id_header,
                kpoapd.tgl_mutasi,
                kpoapd.no_sj,
                kpoapd.ekspedisi,
                kpoapd.no_polisi,
                kpoapd.total as det_total,
                kp.jenis_kirim
            from 
                konfirmasi_pembayaran_oa_pakan_det kpoapd
            right join
                konfirmasi_pembayaran_oa_pakan kpoap
                on
                    kpoapd.id_header = kpoap.id
            right join
                kirim_pakan kp
                on
                    kp.no_sj = kpoapd.no_sj
            right join
                (
                    select p1.* from perusahaan p1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) p2
                        on
                            p1.id = p2.id
                ) p
                on
                    kpoap.perusahaan = p.kode
            where
                kpoap.id = ".$id."
            group by
                kpoap.id,
                kpoap.nomor,
                kpoap.tgl_bayar,
                kpoap.periode,
                kpoap.perusahaan,
                kpoap.ekspedisi,
                kpoap.bank,
                kpoap.rekening,
                kpoap.total,
                kpoap.sub_total,
                kpoap.potongan_pph_23,
                kpoap.materai,
                kpoap.lunas,
                kpoap.ekspedisi_id,
                kpoap.invoice,
                p.perusahaan,
                kpoapd.id_header,
                kpoapd.tgl_mutasi,
                kpoapd.no_sj,
                kpoapd.ekspedisi,
                kpoapd.no_polisi,
                kpoapd.total,
                kp.jenis_kirim
        ";
        $d_kpoap = $m_kpoap->hydrateRaw( $sql );

        $first_date = null;
        $last_date = null;

        $data = null;
        $unit = null;
        if ( $d_kpoap ) {
            $d_kpoap = $d_kpoap->toArray();

            $detail = null;
            foreach ($d_kpoap as $k_kpoap => $v_kpoap) {
                $asal = null;
                $tujuan = null;

                if ( $v_kpoap['jenis_kirim'] == 'opks' ) {
                    $m_kp_oa = new \Model\Storage\KirimPakan_model();
                    $sql = "
                        select 
                            supl.nama as asal,
                            g.nama as tujuan,
                            kp.tujuan as kode_tujuan
                        from kirim_pakan kp
                        right join
                            (
                                select * from pelanggan p1
                                right join
                                    (
                                        select max(id) as id, nomor from pelanggan p group by nomor 
                                    ) p2
                                    on
                                        p1.id = p2.id
                                where 
                                    p1.tipe = 'supplier' and p1.jenis <> 'ekspedisi'
                            ) supl
                            on
                                kp.asal = supl.nomor
                        right join
                            gudang g
                            on
                                kp.tujuan = g.id
                        where
                            kp.no_sj = '".$v_kpoap['no_sj']."'
                        group by
                            supl.nama,
                            g.nama,
                            kp.tujuan
                    ";
                    $d_kp_oa = $m_kp_oa->hydrateRaw( $sql );

                    if ( $d_kp_oa->count() > 0 ) {
                        $d_kp_oa = $d_kp_oa->toArray();

                        $asal = $d_kp_oa[0]['asal'];
                        $tujuan = $d_kp_oa[0]['tujuan'];
                    }
                }

                if ( $v_kpoap['jenis_kirim'] == 'opkg' ) {
                    $m_kp_oa = new \Model\Storage\KirimPakan_model();
                    $sql = "
                        select 
                            g.nama as asal,
                            m.nama as tujuan,
                            SUBSTRING(kp.tujuan, 10, 2) as kandang
                        from kirim_pakan kp
                        right join
                            gudang g
                            on
                                kp.asal = g.id
                        right join
                            (select nim, max(mitra) as id_mitra from mitra_mapping group by nim) mm
                            on
                                SUBSTRING(kp.tujuan, 0, 8) = mm.nim
                        right join
                            mitra m
                            on
                                mm.id_mitra = m.id
                        where
                            kp.no_sj = '".$v_kpoap['no_sj']."'
                        group by
                            g.nama,
                            m.nama,
                            kp.tujuan
                    ";
                    $d_kp_oa = $m_kp_oa->hydrateRaw( $sql );

                    if ( $d_kp_oa->count() > 0 ) {
                        $d_kp_oa = $d_kp_oa->toArray();

                        $asal = $d_kp_oa[0]['asal'];
                        $tujuan = $d_kp_oa[0]['tujuan'].' ( KDG : '.(int)$d_kp_oa[0]['kandang'].' )';
                    }
                }

                if ( $v_kpoap['jenis_kirim'] == 'opkp' ) {
                    $m_kp_oa = new \Model\Storage\KirimPakan_model();
                    $sql = "
                        select 
                            mitra_asal.nama as asal,
                            mitra_tujuan.nama as tujuan,
                            SUBSTRING(kp.asal, 10, 2) as kandang_asal,
                            SUBSTRING(kp.tujuan, 10, 2) as kandang_tujuan
                        from kirim_pakan kp
                        right join
                            (
                                select m.*, mm.nim from mitra m
                                right join
                                    (select nim, max(mitra) as id_mitra from mitra_mapping group by nim) mm
                                    on
                                        m.id = mm.id_mitra
                            ) mitra_asal
                            on
                                SUBSTRING(kp.asal, 0, 8) = mitra_asal.nim
                        right join
                            (
                                select m.*, mm.nim from mitra m
                                right join
                                    (select nim, max(mitra) as id_mitra from mitra_mapping group by nim) mm
                                    on
                                        m.id = mm.id_mitra
                            ) mitra_tujuan
                            on
                                SUBSTRING(kp.tujuan, 0, 8) = mitra_tujuan.nim
                        where
                            kp.no_sj = '".$v_kpoap['no_sj']."'
                        group by
                            mitra_asal.nama,
                            mitra_tujuan.nama,
                            kp.asal,
                            kp.tujuan
                    ";
                    $d_kp_oa = $m_kp_oa->hydrateRaw( $sql );

                    if ( $d_kp_oa->count() > 0 ) {
                        $d_kp_oa = $d_kp_oa->toArray();
                        
                        $asal = $d_kp_oa[0]['asal'];
                        $tujuan = $d_kp_oa[0]['tujuan'].' ( KDG : '.(int)$d_kp_oa[0]['kandang_tujuan'].' )';
                    }
                }

                $unit[ substr($v_kpoap['no_sj'], 3, 2) ] = substr($v_kpoap['no_sj'], 3, 3);

                $detail[] = array(
                    'id' => $v_kpoap['id'],
                    'id_header' => $v_kpoap['id_header'],
                    'tgl_mutasi' => $v_kpoap['tgl_mutasi'],
                    'no_sj' => $v_kpoap['no_sj'],
                    'ekspedisi' => $v_kpoap['ekspedisi'],
                    'no_polisi' => $v_kpoap['no_polisi'],
                    'sub_total' => $v_kpoap['det_total'],
                    'asal' => $asal,
                    'tujuan' => $tujuan
                );

                if ( empty($first_date) ) {
                    $first_date = $v_kpoap['tgl_mutasi'];
                } else {
                    if ( $first_date > $v_kpoap['tgl_mutasi'] ) {
                        $first_date = $v_kpoap['tgl_mutasi'];
                    }
                }

                if ( empty($last_date) ) {
                    $last_date = $v_kpoap['tgl_mutasi'];
                } else {
                    if ( $last_date < $v_kpoap['tgl_mutasi'] ) {
                        $last_date = $v_kpoap['tgl_mutasi'];
                    }
                }
            }

            $data = array(
                'id' => $d_kpoap[0]['id'],
                'first_date' => $first_date,
                'last_date' => $last_date,
                'nomor' => $d_kpoap[0]['nomor'],
                'tgl_bayar' => $d_kpoap[0]['tgl_bayar'],
                'periode' => $d_kpoap[0]['periode'],
                'nama_perusahaan' => $d_kpoap[0]['nama_perusahaan'],
                'perusahaan' => $d_kpoap[0]['perusahaan'],
                'ekspedisi' => $d_kpoap[0]['ekspedisi'],
                'ekspedisi_id' => $d_kpoap[0]['ekspedisi_id'],
                'bank' => $d_kpoap[0]['bank'],
                'rekening' => $d_kpoap[0]['rekening'],
                'sub_total' => $d_kpoap[0]['sub_total'],
                'potongan_pph_23' => $d_kpoap[0]['potongan_pph_23'],
                'materai' => $d_kpoap[0]['materai'],
                'total' => $d_kpoap[0]['total'],
                'lunas' => $d_kpoap[0]['lunas'],
                'unit' => $unit,
                'detail' => $detail
            );
        }

        $content['unit'] = $this->getUnit();
        $content['ekspedisi'] = $this->getEkspedisi();
        $content['perusahaan'] = $perusahaan;
        $content['data'] = $data;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_oa_pakan/editForm', $content, true);

        return $html;
    }

    public function konfirmasiPembayaran()
    {
        $params = $this->input->get('params');

        $nomor = null;
        $bank = null;
        $rekening = null;
        $invoice = null;
        $lampiran = null;
        $tgl_bayar = null;
        $biaya_materai = 0;
        if ( isset($params['id']) ) {
            $m_kpd = new \Model\Storage\KonfirmasiPembayaranOaPakan_model();
            $d_kpd = $m_kpd->where('id', $params['id'])->first();

            $nomor = $d_kpd->nomor;
            $bank = $d_kpd->bank;
            $rekening = $d_kpd->rekening;
            $invoice = $d_kpd->invoice;
            $lampiran = $d_kpd->lampiran;
            $tgl_bayar = $d_kpd->tgl_bayar;
            $biaya_materai = $d_kpd->materai;
        }

        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $d_perusahaan = $m_perusahaan->where('kode', $params['perusahaan'])->orderBy('version', 'desc')->first();

        $first_date = null;
        $last_date = null;

        $ekspedisi = $params['ekspedisi'];
        $ekspedisi_id = $params['ekspedisi_id'];

        $m_ekspedisi = new \Model\Storage\Ekspedisi_model();
        $d_ekspedisi = $m_ekspedisi->where('nomor', $ekspedisi_id)->with(['potongan_pph'])->orderBy('id', 'desc')->first();

        $potongan_pph = $d_ekspedisi->potongan_pph->persen / 100;

        $bank_ekspedisi = null;
        $m_bank_ekspedisi = new \Model\Storage\BankEkspedisi_model();
        $sql = "
            select be.* from bank_ekspedisi be
            right join
                (
                    select e1.* from ekspedisi e1
                    right join
                        (select max(id) as id, nomor from ekspedisi group by nomor) e2
                        on
                            e1.id = e2.id

                ) eks
                on
                    be.ekspedisi_id = eks.id
            where
                eks.nomor = '".$ekspedisi_id."'
        ";
        $d_bank_ekspedisi = $m_bank_ekspedisi->hydrateRaw( $sql );
        if ( $d_bank_ekspedisi->count() > 0 ) {
            $bank_ekspedisi = $d_bank_ekspedisi->toArray();
        }

        $total = $params['total'];
        $first_date = $params['first_date'];
        $last_date = $params['last_date'];

        $data = array(
            'id' => isset($params['id']) ? $params['id'] : null,
            'tgl_bayar' => $tgl_bayar,
            'nomor' => $nomor,
            'bank' => $bank,
            'rekening' => $rekening,
            'total' => $total,
            'total_pph' => $total * $potongan_pph,
            'first_date' => $first_date,
            'last_date' => $last_date,
            'perusahaan' => $d_perusahaan->perusahaan,
            'no_perusahaan' => $d_perusahaan->kode,
            'ekspedisi' => $ekspedisi,
            'ekspedisi_id' => $ekspedisi_id,
            'invoice' => $invoice,
            'lampiran' => $lampiran,
            'biaya_materai' => $biaya_materai
        );

        $content['bank_ekspedisi'] = $bank_ekspedisi;
        $content['data'] = $data;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_oa_pakan/konfirmasiPembayaran', $content, true);

        echo $html;
    }

    public function save()
    {
        $params = json_decode($this->input->post('params'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

        try {
            $file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];
                if ($isMoved) {
                    $file_name = $moved['name'];
                    $path_name = $moved['path'];
                }
            }

            $m_kpoap = new \Model\Storage\KonfirmasiPembayaranOaPakan_model();
            $nomor = $m_kpoap->getNextNomor();

            $m_kpoap->nomor = $nomor;
            $m_kpoap->tgl_bayar = $params['tgl_bayar'];
            $m_kpoap->periode = $params['periode_mutasi'];
            $m_kpoap->perusahaan = $params['perusahaan'];
            $m_kpoap->ekspedisi = $params['ekspedisi'];
            $m_kpoap->ekspedisi_id = $params['ekspedisi_id'];
            $m_kpoap->bank = $params['bank'];
            $m_kpoap->rekening = $params['rekening'];
            $m_kpoap->total = $params['total'];
            $m_kpoap->invoice = $params['invoice'];
            $m_kpoap->lampiran = $path_name;
            $m_kpoap->sub_total = $params['sub_total'];
            $m_kpoap->potongan_pph_23 = $params['potongan_pph_23'];
            $m_kpoap->materai = $params['biaya_materai'];
            $m_kpoap->save();

            $id = $m_kpoap->id;
            foreach ($params['detail'] as $k_det => $v_det) {
                $m_kpoapd = new \Model\Storage\KonfirmasiPembayaranOaPakanDet_model();
                $m_kpoapd->id_header = $id;
                $m_kpoapd->tgl_mutasi = $v_det['tgl_mutasi'];
                $m_kpoapd->no_sj = $v_det['no_sj'];
                $m_kpoapd->ekspedisi = $v_det['ekspedisi'];
                $m_kpoapd->no_polisi = $v_det['no_polisi'];
                $m_kpoapd->total = $v_det['sub_total'];
                $m_kpoapd->save();
            }

            $d_kpoap = $m_kpoap->where('id', $id)->first();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_kpoap, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $id);
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit()
    {
        $params = json_decode($this->input->post('params'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

        try {
            $file_name = $path_name = $params['lampiran_old'];
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];
                if ($isMoved) {
                    $file_name = $moved['name'];
                    $path_name = $moved['path'];
                }
            }

            $id = $params['id'];

            $m_kpoap = new \Model\Storage\KonfirmasiPembayaranOaPakan_model();
            $m_kpoap->where('id', $params['id'])->update(
                array(
                    'tgl_bayar' => $params['tgl_bayar'],
                    'periode' => $params['periode_mutasi'],
                    'perusahaan' => $params['perusahaan'],
                    'ekspedisi' => $params['ekspedisi'],
                    'ekspedisi_id' => $params['ekspedisi_id'],
                    'bank' => $params['bank'],
                    'rekening' => $params['rekening'],
                    'total' => $params['total'],
                    'invoice' => $params['invoice'],
                    'lampiran' => $path_name,
                    'sub_total' => $params['sub_total'],
                    'potongan_pph_23' => $params['potongan_pph_23'],
                    'materai' => $params['biaya_materai']
                )
            );

            $m_kpoapd = new \Model\Storage\KonfirmasiPembayaranOaPakanDet_model();
            $m_kpoapd->where('id_header', $id)->delete();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_kpoapd = new \Model\Storage\KonfirmasiPembayaranOaPakanDet_model();
                $m_kpoapd->id_header = $id;
                $m_kpoapd->tgl_mutasi = $v_det['tgl_mutasi'];
                $m_kpoapd->no_sj = $v_det['no_sj'];
                $m_kpoapd->ekspedisi = $v_det['ekspedisi'];
                $m_kpoapd->no_polisi = $v_det['no_polisi'];
                $m_kpoapd->total = $v_det['sub_total'];
                $m_kpoapd->save();
            }

            $d_kpoap = $m_kpoap->where('id', $id)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_kpoap, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $id);
            $this->result['message'] = 'Data berhasil di update.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete()
    {
        $params = $this->input->post('params');
        try {
            $id = $params['id'];

            $m_kpoapd = new \Model\Storage\KonfirmasiPembayaranOaPakan_model();
            $d_kpoapd = $m_kpoapd->where('id', $id)->first();

            $m_kpoapdd = new \Model\Storage\KonfirmasiPembayaranOaPakanDet_model();
            $m_kpoapdd->where('id_header', $id)->delete();

            $m_kpoapd->where('id', $id)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_kpoapd, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}