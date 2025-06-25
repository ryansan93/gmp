<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RealisasiSjMobile extends Public_Controller {

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
    public function index($segment=0)
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->load->library('Mobile_Detect');
            $detect = new Mobile_Detect();

            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/compress-image/js/compress-image.js",
                "assets/transaksi/realisasi_sj_mobile/js/realisasi-sj-mobile.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/realisasi_sj_mobile/css/realisasi-sj-mobile.css",
            ));

            $data = $this->includes;

            $isMobile = false;
            if ( $detect->isMobile() ) {
                $isMobile = true;
            }

            $content['akses'] = $akses;
            $content['isMobile'] = $isMobile;

            $mitra = $this->get_mitra();
            $unit = $this->get_unit();
            $content['add_form'] = $this->add_form($mitra);
            $content['riwayat'] = $this->riwayat($unit);

            // Load Indexx
            $data['title_menu'] = 'Realisai Panen';
            $data['view'] = $this->load->view('transaksi/realisasi_sj_mobile/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function riwayat($unit)
    {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['unit'] = $unit;
        $html = $this->load->view('transaksi/realisasi_sj_mobile/riwayat', $content, TRUE);

        return $html;
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

    public function get_lists()
    {
        $params = $this->input->get('params');

        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->select('id')->where('kode', $params['kode_unit'])->get()->toArray();

        $m_real_sj = new \Model\Storage\RealSJ_model();
        $d_real_sj = $m_real_sj->whereIn('id_unit', $d_wilayah)->where('tgl_panen', $params['tgl_panen'])->get();

        $data = null;
        if ( $d_real_sj->count() > 0 ) {
            $d_real_sj = $d_real_sj->toArray();
            foreach ($d_real_sj as $k_real_sj => $v_real_sj) {
                $m_rs = new \Model\Storage\RdimSubmit_model();
                $d_rs = $m_rs->where('noreg', $v_real_sj['noreg'])->with(['mitra'])->orderBy('id', 'desc')->first();

                $nama = $d_rs->mitra->dMitra->nama;
                if ( !isset($data[ $v_real_sj['noreg'] ]) ) {
                    $data[ $nama.'-'.$v_real_sj['noreg'] ] = array(
                        'noreg' => $v_real_sj['noreg'],
                        'nomor' => $d_rs->mitra->dMitra->nomor,
                        'mitra' => $nama,
                        'kandang' => substr($v_real_sj['noreg'], -2),
                        'tgl_panen' => $params['tgl_panen'],
                        'ekor' => $v_real_sj['ekor'],
                        'tonase' => $v_real_sj['kg'],
                        'bb' => ($v_real_sj['kg'] > 0 && $v_real_sj['ekor'] > 0) ? $v_real_sj['kg'] / $v_real_sj['ekor'] : 0,
                        'g_status' => $v_real_sj['g_status']
                    );
                } else {
                    $data[ $nama.'-'.$v_real_sj['noreg'] ]['ekor'] += $v_real_sj['ekor'];
                    $data[ $nama.'-'.$v_real_sj['noreg'] ]['kg'] += $v_real_sj['kg'];
                    $data[ $nama.'-'.$v_real_sj['noreg'] ]['bb'] = $data[ $nama.'-'.$v_real_sj['noreg'] ]['kg'] / $data[ $nama.'-'.$v_real_sj['noreg'] ]['ekor'];
                }
            }

            if ( !empty($data) ) {
                ksort($data);
            }
        }

        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['data'] = $data;
        $html = $this->load->view('transaksi/realisasi_sj_mobile/list', $content, TRUE);

        echo $html;
    }

    public function load_form()
    {
        $params = $this->input->get('params');
        $edit = $this->input->get('edit');

        $noreg = $params['noreg'];
        $tgl_panen = $params['tgl_panen'];
        $nomor = $params['nomor'];

        $content = array();
        $html = "url not found";
        
        if ( !empty($noreg) && $edit != 'edit' ) {
            // NOTE: view data BASTTB (ajax)
            $html = $this->detail_form($noreg, $tgl_panen, $nomor);
        } else if ( !empty($noreg) && $edit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $mitra = $this->get_mitra();
            $html = $this->edit_form($noreg, $tgl_panen, $nomor, $mitra);
        }else{
            $mitra = $this->get_mitra();
            $html = $this->add_form($mitra);
        }

        echo $html;
    }

    public function add_form($mitra)
    {
        $content['data_mitra'] = $mitra;
        $html = $this->load->view('transaksi/realisasi_sj_mobile/add_form', $content, TRUE);

        return $html;
    }

    public function detail_form($noreg, $tgl_panen, $nomor)
    {
        $edit_data = 0;
        $hapus_data = 0;

        $m_real_sj = new \Model\Storage\RealSJ_model();
        $d_real_sj = $m_real_sj->where('tgl_panen', $tgl_panen)->where('noreg', $noreg)->orderBy('id', 'desc')->first();

        $data = null;
        if ( $d_real_sj ) {
            $m_wilayah = new \Model\Storage\Wilayah_model();
            $d_wilayah = $m_wilayah->where('id', $d_real_sj->id_unit)->orderBy('id', 'desc')->first();

            $m_rs = new \Model\Storage\RdimSubmit_model();
            $d_rs = $m_rs->where('noreg', $noreg)->with(['mitra'])->orderBy('id', 'desc')->first();

            $m_rpah = new \Model\Storage\Rpah_model();
            $d_rpah = $m_rpah->where('tgl_panen', $tgl_panen)->get();

            $ekor = 0;
            $tonase = 0;
            $harga_dasar = 0;
            foreach ($d_rpah as $k_rpah => $v_rpah) {
                $m_drpah = new \Model\Storage\DetRpah_model();
                $d_drpah = $m_drpah->where('id_rpah', $v_rpah['id'])->where('noreg', $noreg)->get();

                if ( $d_drpah->count() > 0 ) {
                    $d_drpah = $d_drpah->toArray();

                    foreach ($d_drpah as $k_drpah => $v_drpah) {
                        $ekor += $v_drpah['ekor'];
                        $tonase += $v_drpah['tonase'];

                        $harga_dasar = $v_rpah['bottom_price'];
                    }
                }
            }

            $m_dreal_sj = new \Model\Storage\DetRealSJ_model();
            $d_dreal_sj = $m_dreal_sj->where('id_header', $d_real_sj->id)->get();

            $detail = null;
            if ( $d_dreal_sj->count() > 0 ) {
                $d_dreal_sj = $d_dreal_sj->toArray();

                foreach ($d_dreal_sj as $k_dreal_sj => $v_dreal_sj) {
                    $m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
                    $d_dpp = $m_dpp->where('id_do', $v_dreal_sj['id'])->where('jumlah_bayar', '>', 0)->first();

                    if ( !$d_dpp ) {
                        $edit_data = 1;
                    } else {
                        $hapus_data = 0;
                    }
                    // else {
                    //     $hapus_data = 0;
                    //     $edit_data = 0;
                    // }

                    $detail[ $v_dreal_sj['no_do'] ]['pelanggan'] = $v_dreal_sj['pelanggan'];
                    $detail[ $v_dreal_sj['no_do'] ]['no_pelanggan'] = $v_dreal_sj['no_pelanggan'];
                    $detail[ $v_dreal_sj['no_do'] ]['id_det_rpah'] = $v_dreal_sj['id_det_rpah'];
                    $detail[ $v_dreal_sj['no_do'] ]['no_do'] = $v_dreal_sj['no_do'];
                    $detail[ $v_dreal_sj['no_do'] ]['no_sj'] = $v_dreal_sj['no_sj'];
                    $detail[ $v_dreal_sj['no_do'] ]['lampiran'] = $v_dreal_sj['lampiran'];
                    $detail[ $v_dreal_sj['no_do'] ]['realisasi'][ $v_dreal_sj['id'] ] = array(
                        'tonase' => $v_dreal_sj['tonase'],
                        'ekor' => $v_dreal_sj['ekor'],
                        'bb' => $v_dreal_sj['bb'],
                        'harga' => $v_dreal_sj['harga'],
                        'jenis_ayam' => $v_dreal_sj['jenis_ayam'],
                        'no_nota' => $v_dreal_sj['no_nota']
                    );
                }
            }

            $data = array(
                'kode_unit' => $d_wilayah->kode,
                'noreg' => $noreg,
                'mitra' => $d_rs->mitra->dMitra->nama,
                'nomor' => $nomor,
                'tgl_panen' => $tgl_panen,
                'ekor' => $ekor,
                'tonase' => $tonase,
                'umur' => abs(selisihTanggal(substr($d_rs->tgl_docin, 0, 10), $tgl_panen)),
                'harga_dasar' => $harga_dasar,
                'detail' => $detail
            );
        }

        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['jenis_ayam'] = $this->config->item('jenis_ayam');
        $content['data'] = $data;
        $content['edit_data'] = $edit_data;
        $content['hapus_data'] = $hapus_data;
        $html = $this->load->view('transaksi/realisasi_sj_mobile/detail_form', $content, TRUE);

        return $html;
    }

    public function edit_form($noreg, $tgl_panen, $nomor, $mitra)
    {
        $data = null;

        $m_real_sj = new \Model\Storage\RealSJ_model();
        $d_real_sj = $m_real_sj->where('tgl_panen', $tgl_panen)->where('noreg', $noreg)->orderBy('id', 'desc')->first();

        $data = null;
        if ( $d_real_sj ) {
            $m_wilayah = new \Model\Storage\Wilayah_model();
            $d_wilayah = $m_wilayah->where('id', $d_real_sj->id_unit)->orderBy('id', 'desc')->first();

            $m_rs = new \Model\Storage\RdimSubmit_model();
            $d_rs = $m_rs->where('noreg', $noreg)->with(['mitra'])->orderBy('id', 'desc')->first();

            $m_rpah = new \Model\Storage\Rpah_model();
            $d_rpah = $m_rpah->where('tgl_panen', $tgl_panen)->orderBy('id', 'desc')->get();

            $ekor = 0;
            $tonase = 0;
            $harga_dasar = 0;
            foreach ($d_rpah as $k_rpah => $v_rpah) {
                $m_drpah = new \Model\Storage\DetRpah_model();
                $d_drpah = $m_drpah->where('id_rpah', $v_rpah['id'])->where('noreg', $noreg)->get();

                if ( $d_drpah->count() > 0 ) {
                    $d_drpah = $d_drpah->toArray();

                    foreach ($d_drpah as $k_drpah => $v_drpah) {
                        $ekor += $v_drpah['ekor'];
                        $tonase += $v_drpah['tonase'];

                        $harga_dasar = $v_rpah['bottom_price'];

                        $m_dreal_sj = new \Model\Storage\DetRealSJ_model();
                        $d_dreal_sj = $m_dreal_sj->where('id_header', $d_real_sj->id)->where('no_do', $v_drpah['no_do'])->get();

                        if ( $d_dreal_sj->count() > 0 ) {
                            $d_dreal_sj = $d_dreal_sj->toArray();
                            foreach ($d_dreal_sj as $k_dreal_sj => $v_dreal_sj) {
                                $m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
                                $d_dpp = $m_dpp->where('id_do', $v_dreal_sj['id'])->sum('jumlah_bayar');

                                $edit_data = 1;
                                if ( $d_dpp > 0 ) {
                                    $edit_data = 0;
                                }

                                $detail[ $v_dreal_sj['no_do'] ]['pelanggan'] = $v_dreal_sj['pelanggan'];
                                $detail[ $v_dreal_sj['no_do'] ]['no_pelanggan'] = $v_dreal_sj['no_pelanggan'];
                                $detail[ $v_dreal_sj['no_do'] ]['id_det_rpah'] = $v_drpah['id'];
                                $detail[ $v_dreal_sj['no_do'] ]['no_do'] = $v_dreal_sj['no_do'];
                                $detail[ $v_dreal_sj['no_do'] ]['no_sj'] = $v_dreal_sj['no_sj'];
                                $detail[ $v_dreal_sj['no_do'] ]['lampiran'] = $v_dreal_sj['lampiran'];
                                $detail[ $v_dreal_sj['no_do'] ]['edit_data'] = $edit_data;
                                $detail[ $v_dreal_sj['no_do'] ]['realisasi'][ $v_dreal_sj['id'] ] = array(
                                        'tonase' => $v_dreal_sj['tonase'],
                                        'ekor' => $v_dreal_sj['ekor'],
                                        'bb' => $v_dreal_sj['bb'],
                                        'harga' => $v_dreal_sj['harga'],
                                        'jenis_ayam' => $v_dreal_sj['jenis_ayam'],
                                        'no_nota' => $v_dreal_sj['no_nota']
                                    );
                            }
                        } else {
                            $detail[ $v_drpah['no_do'] ]['pelanggan'] = $v_drpah['pelanggan'];
                            $detail[ $v_drpah['no_do'] ]['no_pelanggan'] = $v_drpah['no_pelanggan'];
                            $detail[ $v_drpah['no_do'] ]['id_det_rpah'] = $v_drpah['id'];
                            $detail[ $v_drpah['no_do'] ]['no_do'] = $v_drpah['no_do'];
                            $detail[ $v_drpah['no_do'] ]['no_sj'] = $v_drpah['no_sj'];
                            $detail[ $v_drpah['no_do'] ]['lampiran'] = null;
                            $detail[ $v_drpah['no_do'] ]['edit_data'] = 1;
                            $detail[ $v_drpah['no_do'] ]['realisasi'] = null;
                        }
                    }
                }
            }

            $data = array(
                'kode_unit' => $d_wilayah->kode,
                'noreg' => $noreg,
                'mitra' => $d_rs->mitra->dMitra->nama,
                'nomor' => $nomor,
                'tgl_panen' => $tgl_panen,
                'ekor' => $ekor,
                'tonase' => $tonase,
                'umur' => abs(selisihTanggal(substr($d_rs->tgl_docin, 0, 10), $tgl_panen)),
                'harga_dasar' => $harga_dasar,
                'detail' => $detail
            );
        }

        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['jenis_ayam'] = $this->config->item('jenis_ayam');
        $content['data_mitra'] = $mitra;
        $content['data'] = $data;
        $html = $this->load->view('transaksi/realisasi_sj_mobile/edit_form', $content, TRUE);

        return $html;
    }

    public function get_mitra()
    {
        $data = array();

        $m_duser = new \Model\Storage\DetUser_model();
        $d_duser = $m_duser->where('id_user', $this->userid)->orderBy('id_detuser', 'desc')->first();

        $m_karyawan = new \Model\Storage\Karyawan_model();
        $d_karyawan = $m_karyawan->where('nama', 'like', strtolower(trim($d_duser->nama_detuser)).'%')->orderBy('id', 'desc')->first();

        $kode_unit = array();
        $kode_unit_all = null;
        if ( $d_karyawan ) {
            $m_ukaryawan = new \Model\Storage\UnitKaryawan_model();
            $d_ukaryawan = $m_ukaryawan->where('id_karyawan', $d_karyawan->id)->get()->toArray();

            foreach ($d_ukaryawan as $k_ukaryawan => $v_ukaryawan) {
                if ( stristr($v_ukaryawan['unit'], 'all') === false ) {
                    $m_wil = new \Model\Storage\Wilayah_model();
                    $d_wil = $m_wil->where('id', $v_ukaryawan['unit'])->first();

                    $kode_unit[ $d_wil->kode ] = $d_wil->kode;
                } else {
                    $m_wil = new \Model\Storage\Wilayah_model();
                    $sql = "
                        select kode from wilayah where kode is not null group by kode
                    ";
                    $d_wil = $m_wil->hydrateRaw($sql);

                    if ( $d_wil->count() > 0 ) {
                        $d_wil = $d_wil->toArray();

                        foreach ($d_wil as $key => $value) {
                            array_push($kode_unit, $value['kode']);
                        }
                    }
                }
            }
        } else {
            $m_wil = new \Model\Storage\Wilayah_model();
            $sql = "
                select kode from wilayah where kode is not null group by kode
            ";
            $d_wil = $m_wil->hydrateRaw($sql);

            if ( $d_wil->count() > 0 ) {
                $d_wil = $d_wil->toArray();

                foreach ($d_wil as $key => $value) {
                    array_push($kode_unit, $value['kode']);
                }
            }
        }

        $start_date = prev_date(date('Y-m-d'), 60).' 00:00:00.000';

        $m_od = new \Model\Storage\OrderDoc_model();
        $sql = "
            select 
                data.nomor,
                data.nama,
                data.unit
            from
                (
                select
                    od.no_order,
                    od.noreg,
                    m.nomor,
                    m.nama,
                    (SUBSTRING(od.no_order, 5, 3)) as unit
                from 
                    (
                        select od1.* from order_doc od1
                        right join
                            (select max(id) as id from order_doc group by no_order, noreg) od2
                            on
                                od1.id = od2.id
                    ) od 
                right join
                    rdim_submit rs 
                    on
                        rs.noreg = od.noreg 
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
                        m.id = mm.mitra
                where
                    rs.tgl_docin >= '".$start_date."'
                group by
                    od.no_order,
                    od.noreg,
                    m.nomor,
                    m.nama
            ) data
            where
                data.unit in ('".implode("', '", $kode_unit)."')
            group by
                data.nomor,
                data.nama,
                data.unit
            order by
                data.unit asc,
                data.nama asc
        ";
        $d_od = $m_od->hydrateRaw( $sql );

        if ( $d_od->count() > 0 ) {
            $d_od = $d_od->toArray();

            $data = $d_od;
        }

        return $data;
    }

    public function get_noreg()
    {
        $nomor_mitra = $this->input->post('params');

        $end_date = date('Y-m-d').' 23:59:59.999';
        $start_date = prev_date(date('Y-m-d'), 61).' 00:00:00.000';

        // $m_mm = new \Model\Storage\MitraMapping_model();
        // $d_mm = $m_mm->select('nim')->where('nomor', $nomor_mitra)->get()->toArray();

        // $m_rs = new \Model\Storage\RdimSubmit_model();
        // $d_rs = $m_rs->whereIn('nim', $d_mm)->whereBetween('tgl_docin', [$start_date, $end_date])->get();

        $m_rs = new \Model\Storage\Conf();
        $sql = "
            select 
                rs.*,
                w.kode as kode_unit
            from rdim_submit rs
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
                kandang k
                on
                    k.id = rs.kandang
            left join
                wilayah w
                on
                    w.id = k.unit
            where
                mm.nomor = '".$nomor_mitra."' and
                rs.tgl_docin between '".$start_date."' and '".$end_date."'
        ";
        $d_rs = $m_rs->hydrateRaw( $sql );

        $_data = array();
        if ( $d_rs->count() > 0 ) {
            $d_rs = $d_rs->toArray();
            foreach ($d_rs as $k_rs => $v_rs) {
                $m_ts = new \Model\Storage\TutupSiklus_model();
                $d_ts = $m_ts->where('noreg', $v_rs['noreg'])->first();

                if ( empty($d_ts) ) {
                    $m_od = new \Model\Storage\OrderDoc_model();
                    $d_od = $m_od->where('noreg', $v_rs['noreg'])->first();

                    $tgl_docin = substr($v_rs['tgl_docin'], 0, 10);
                    if ( !empty($d_od) ) {
                        $m_td = new \Model\Storage\TerimaDoc_model();
                        $d_td = $m_td->where('no_order', $d_od->no_order)->first();

                        if ( !empty($d_td) ) {
                            $tgl_docin = substr($d_td->datang, 0, 10);
                            
                            $kandang = (int) substr($v_rs['noreg'], -1);
        
                            $key = str_replace('-', '', $tgl_docin).' - '.substr($v_rs['noreg'], -1);
                            $_data[ $key ] = array(
                                'noreg' => $v_rs['noreg'],
                                'real_tgl_docin' => $tgl_docin,
                                'tgl_docin' => strtoupper(tglIndonesia($tgl_docin, '-', ' ')),
                                'kandang' => 'KD - '.$kandang,
                                'umur' => selisihTanggal($tgl_docin, date('Y-m-d')),
                                'kode_unit' => $v_rs['kode_unit'],
                            );
                        }
                    }

                }
            }
        }

        $data = array();
        if ( !empty( $_data ) ) {
            ksort($_data);

            foreach ($_data as $k_data => $v_data) {
                $data[] = $v_data;
            }
        }

        $this->result['content'] = $data;

        display_json( $this->result );
    }

    public function get_data_rpah()
    {
        $params = $this->input->post('params');

        $data_cek = $this->cekRealSj($params['noreg'], $params['tgl_panen']);

        if ( $data_cek['status'] == 0 ) {
            $m_wilayah = new \Model\Storage\Wilayah_model();
            $d_wilayah = $m_wilayah->select('id')->where('kode', $params['kode_unit'])->get()->toArray();

            $m_rpah = new \Model\Storage\Rpah_model();
            $d_rpah = $m_rpah->select('id')->whereIn('id_unit', $d_wilayah)->where('tgl_panen', $params['tgl_panen'])->get();

            $bottom_price = 0;
            $ekor = 0;
            $tonase = 0;
            $data_drpah = null;
            if ( $d_rpah->count() > 0 ) {
                $d_rpah = $d_rpah->toArray();

                $m_drpah = new \Model\Storage\DetRpah_model();
                $d_drpah = $m_drpah->whereIn('id_rpah', $d_rpah)->where('noreg', $params['noreg'])->get();

                if ( $d_drpah->count() > 0 ) {
                    $data_drpah = $d_drpah->toArray();

                    foreach ($data_drpah as $k_drpah => $v_drpah) {
                        $d_rpah = $m_rpah->where('id', $v_drpah['id_rpah'])->first();

                        $bottom_price = $d_rpah->bottom_price;
                        $ekor += $v_drpah['ekor'];
                        $tonase += $v_drpah['tonase'];
                    }
                }            
            }

            if ( !empty($data_drpah) ) {
                $content['data'] = $data_drpah;
                $content['jenis_ayam'] = $this->config->item('jenis_ayam');
                $html = $this->load->view('transaksi/realisasi_sj_mobile/list_rpah', $content, TRUE);
    
                $this->result['status'] = 1;
                $this->result['content'] = array(
                    'html' => $html,
                    'harga_dasar' => $bottom_price,
                    'ekor' => $ekor,
                    'tonase' => $tonase
                );
            } else {
                $m_mitra = new \Model\Storage\Mitra_model();
                $d_mitra = $m_mitra->where('nomor', $params['mitra'])->orderBy('id', 'desc')->first();
    
                $this->result['message'] = 'Data rencana penjualan pada mitra <b>'.strtoupper($d_mitra->nama).'</b> dan tanggal panen <b>'.strtoupper(tglIndonesia($params['tgl_panen'], '-', ' ', true)).'</b> tidak ditemukan / belum di acc oleh <b>KADIV MARKETING</b>.';
            }
        } else {
            $this->result['message'] = $data_cek['message'];
        }


        display_json( $this->result );
    }

    public function save()
    {
        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['file']) ? $_FILES['file'] : null;
        $mappingFiles = !empty($files) ? $this->mappingFiles($files) : null;

        try {
            $data_cek = $this->cekRealSj($params['noreg'], $params['tgl_panen']);

            if ( $data_cek['status'] == 0 ) {
                $m_wilayah = new \Model\Storage\Wilayah_model();
                $_d_wilayah = $m_wilayah->where('kode', $params['kode_unit'])->first();

                $m_real_sj = new \Model\Storage\RealSJ_model();
                $m_real_sj->id_unit = $_d_wilayah->id;
                $m_real_sj->unit = strtoupper($_d_wilayah->nama);
                $m_real_sj->tgl_panen = $params['tgl_panen'];
                $m_real_sj->noreg = $params['noreg'];
                $m_real_sj->ekor = $params['ekor'];
                $m_real_sj->kg = $params['kg'];
                $m_real_sj->bb = $params['bb'];
                $m_real_sj->tara = 0;
                $m_real_sj->netto_ekor = $params['ekor'];
                $m_real_sj->netto_kg = $params['kg'];
                $m_real_sj->netto_bb = $params['bb'];
                $m_real_sj->save();

                $id_real_sj = $m_real_sj->id;

                if ( !empty($params['detail']) ) {
                    foreach ($params['detail'] as $k => $val) {
                        $path_name = null;
                        $file = $mappingFiles[ $val['no_do'] ] ?: '';
                        if ( !empty($file) ) {
                            $moved = uploadFile($file);
                            $isMoved = $moved['status'];

                            if ($isMoved) {
                                $path_name = $moved['path'];
                            }
                        }

                        $m_det_real_sj = new \Model\Storage\DetRealSJ_model();
                        $m_det_real_sj->id = $m_det_real_sj->getNextIdentity();
                        $m_det_real_sj->id_header = $id_real_sj;
                        $m_det_real_sj->id_det_rpah = $val['id_det_rpah'];
                        $m_det_real_sj->no_pelanggan = $val['no_pelanggan'];
                        $m_det_real_sj->pelanggan = $val['pelanggan'];
                        $m_det_real_sj->tonase = $val['tonase'];
                        $m_det_real_sj->ekor = $val['ekor'];
                        $m_det_real_sj->bb = $val['bb'];
                        $m_det_real_sj->harga = $val['harga'];
                        $m_det_real_sj->no_do = $val['no_do'];
                        $m_det_real_sj->no_sj = $val['no_sj'];
                        $m_det_real_sj->jenis_ayam = $val['jenis_ayam'];
                        $m_det_real_sj->lampiran = $path_name;
                        $m_det_real_sj->no_nota = $val['no_nota'];
                        $m_det_real_sj->save();
                    }
                }

                $m_conf = new \Model\Storage\Conf();
                $sql = "exec insert_jurnal NULL, NULL, NULL, 0, 'real_sj', ".$id_real_sj.", NULL, 1";
                $d_conf = $m_conf->hydrateRaw( $sql );

                $d_real_sj = $m_real_sj->where('id', $id_real_sj)->first();

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $d_real_sj, $deskripsi_log);

                $this->result['status'] = 1;
                $this->result['message'] = 'Data berhasil disimpan';
            } else {
                $this->result['message'] = $data_cek['message'];
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit()
    {
        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['file']) ? $_FILES['file'] : null;
        $mappingFiles = !empty($files) ? $this->mappingFiles($files) : null;

        try {
            $akses = hakAkses($this->url);
            
            $m_wilayah = new \Model\Storage\Wilayah_model();
            $_d_wilayah = $m_wilayah->where('kode', $params['kode_unit'])->orderBy('id', 'desc')->first();

            $m_real_sj = new \Model\Storage\RealSJ_model();
            $d_real_sj = $m_real_sj->where('noreg', $params['noreg_old'])->where('tgl_panen', $params['tgl_panen_old'])->orderBy('id', 'desc')->first();

            $id_real_sj = $d_real_sj->id;

            $m_real_sj->where('id', $id_real_sj)->update(
                array(
                    'id_unit' => $_d_wilayah->id,
                    'unit' => strtoupper($_d_wilayah->nama),
                    'tgl_panen' => $params['tgl_panen'],
                    'noreg' => $params['noreg'],
                    'ekor' => $params['ekor'],
                    'kg' => $params['kg'],
                    'bb' => $params['bb'],
                    'tara' => 0,
                    'netto_ekor' => $params['ekor'],
                    'netto_kg' => $params['kg'],
                    'netto_bb' => $params['bb'],
                    'g_status' => ( !empty($akses['a_khusus']) && in_array('input harga realisasi sj', $akses['a_khusus']) ) ? 1 : 0
                )
            );

            $arr_id = null;
            if ( !empty($params['detail']) ) {
                foreach ($params['detail'] as $k => $val) {
                    $path_name = isset($val['lampiran_old']) ? $val['lampiran_old'] : null;

                    $file = isset($mappingFiles[ $val['no_do'] ]) ? $mappingFiles[ $val['no_do'] ] : null;
                    if ( !empty($file) ) {
                        $moved = uploadFile($file);
                        $isMoved = $moved['status'];
                        if ($isMoved) {
                            $path_name = $moved['path'];
                        }
                    }

                    $m_det_real_sj = new \Model\Storage\DetRealSJ_model();
                    $id = $m_det_real_sj->getNextIdentity();
                    if ( !empty($val['id_det_real_sj']) ) {
                        $id = $val['id_det_real_sj'];

                        $m_det_real_sj->where('id', $id)->update(
                            array(
                                'id_header' => $id_real_sj,
                                'id_det_rpah' => $val['id_det_rpah'],
                                'no_pelanggan' => $val['no_pelanggan'],
                                'pelanggan' => $val['pelanggan'],
                                'tonase' => $val['tonase'],
                                'ekor' => $val['ekor'],
                                'bb' => $val['bb'],
                                'harga' => $val['harga'],
                                'no_do' => $val['no_do'],
                                'no_sj' => $val['no_sj'],
                                'jenis_ayam' => $val['jenis_ayam'],
                                'lampiran' => $path_name,
                                'no_nota' => $val['no_nota'],
                            )
                        );
                    } else {
                        $m_det_real_sj->id = $id;
                        $m_det_real_sj->id_header = $id_real_sj;
                        $m_det_real_sj->id_det_rpah = $val['id_det_rpah'];
                        $m_det_real_sj->no_pelanggan = $val['no_pelanggan'];
                        $m_det_real_sj->pelanggan = $val['pelanggan'];
                        $m_det_real_sj->tonase = $val['tonase'];
                        $m_det_real_sj->ekor = $val['ekor'];
                        $m_det_real_sj->bb = $val['bb'];
                        $m_det_real_sj->harga = $val['harga'];
                        $m_det_real_sj->no_do = $val['no_do'];
                        $m_det_real_sj->no_sj = $val['no_sj'];
                        $m_det_real_sj->jenis_ayam = $val['jenis_ayam'];
                        $m_det_real_sj->lampiran = $path_name;
                        $m_det_real_sj->no_nota = $val['no_nota'];
                        $m_det_real_sj->save();
                    }

                    $arr_id[] = $id;
                }
                
                $m_det_real_sj = new \Model\Storage\DetRealSJ_model();
                $m_det_real_sj->where('id_header', $id_real_sj)->whereNotIn('id', $arr_id)->delete();
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal NULL, NULL, NULL, 0, 'real_sj', ".$id_real_sj.", ".$id_real_sj.", 2";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $d_real_sj = $m_real_sj->where('id', $id_real_sj)->first();

            if ( !empty($akses['a_khusus']) && in_array('input harga realisasi sj', $akses['a_khusus']) ) {
                $deskripsi_log = 'update harga oleh ' . $this->userdata['detail_user']['nama_detuser'];
            } else {
                $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            }
            Modules::run( 'base/event/update', $d_real_sj, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di update';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {
            $m_real_sj = new \Model\Storage\RealSJ_model();
            $d_real_sj = $m_real_sj->where('tgl_panen', $params['tgl_panen'])->where('noreg', $params['noreg'])->first();

            $m_real_sj->where('id', $d_real_sj->id)->delete();
            $m_dreal_sj = new \Model\Storage\DetRealSJ_model();
            $m_dreal_sj->where('id_header', $d_real_sj->id)->delete();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal NULL, NULL, NULL, 0, 'real_sj', ".$d_real_sj->id.", ".$d_real_sj->id.", 3";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'hapus data realisasi sj noreg '+$params['noreg']+' oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_real_sj, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus';           
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function cekRealSj($noreg, $tgl_panen) {
        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                rs.*,
                lt.deskripsi,
                lt.waktu
            from real_sj rs
            left join
                (select * from log_tables lt where tbl_name = 'real_sj' and _action = 'insert') lt
                on
                    rs.id = lt.tbl_id
            where
                rs.noreg = '".$noreg."' and
                rs.tgl_panen = '".$tgl_panen."'
        ";
        $d_rs = $m_conf->hydrateRaw( $sql );

        if ( $d_rs->count() > 0 ) {
            $d_rs = $d_rs->toArray()[0];
            $data['status'] = 1;
            $data['message'] = 'Data realisasi panen sudah di input <b>'.strtoupper(str_replace('di-submit oleh ', '', $d_rs['deskripsi'])).'</b> pada <b>'.strtoupper(tglIndonesia(substr($d_rs['waktu'], 0, 10), '-', ' ')).' '.substr($d_rs['waktu'], 11, 5).'</b>';
        } else {
            $data['status'] = 0;
        }

        return $data;
    }

    public function mappingFiles($files)
    {
        $mappingFiles = [];
        foreach ($files['tmp_name'] as $key => $file) {
            $sha1 = sha1_file($file);
            $index = $key;
            $mappingFiles[$index] = [
                'name' => $files['name'][$key],
                'tmp_name' => $file,
                'type' => $files['type'][$key],
                'size' => $files['size'][$key],
                'error' => $files['error'][$key]
            ];
        }
        
        return $mappingFiles;
    }

    public function tes()
    {
        // $m_drpah = new \Model\Storage\DetRpah_model();
        // $kode_unit = 'JBR';
        // $no_do = $m_drpah->getNextNo('no_do','DO/'.$kode_unit);

        $m_det_real_sj = new \Model\Storage\DetRealSJ_model();

        cetak_r($m_det_real_sj->getNextIdentity());
    }
}