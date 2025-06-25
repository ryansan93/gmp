<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RpahMobile extends Public_Controller {

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
    public function index($params=null)
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->load->library('Mobile_Detect');
            $detect = new Mobile_Detect();

            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/transaksi/rpah_mobile/js/rpah-mobile.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/rpah_mobile/css/rpah-mobile.css",
            ));

            $data = $this->includes;

            $isMobile = false;
            if ( $detect->isMobile() ) {
                $isMobile = true;
            }

            $content['akses'] = $akses;
            $content['isMobile'] = $isMobile;

            $mitra = $this->get_mitra();
            $pelanggan = $this->get_pelanggan();
            $unit = $this->get_unit();
            $content['add_form'] = $this->add_form($mitra, $pelanggan);
            $content['riwayat'] = $this->riwayat($unit, $params);

            // Load Indexx
            $data['title_menu'] = 'Pengajuan Rencana Penjualan Harian';
            $data['view'] = $this->load->view('transaksi/rpah_mobile/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function riwayat($unit, $params)
    {
        $kode_unit = null;
        $tgl_panen = null;
        if ( !empty($params) ) {
            $params = json_decode(exDecrypt($params), true);

            $kode_unit = $params['kode_unit'];
            $tgl_panen = $params['tgl_panen'];
        }

        $content['kode_unit'] = $kode_unit;
        $content['tgl_panen'] = $tgl_panen;
        $content['unit'] = $unit;
        $html = $this->load->view('transaksi/rpah_mobile/riwayat', $content, TRUE);

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

        $m_rpah = new \Model\Storage\Rpah_model();
        $d_rpah = $m_rpah->whereIn('id_unit', $d_wilayah)->where('tgl_panen', $params['tgl_panen'])->get();

        $data = null;
        if ( $d_rpah->count() > 0 ) {
            $d_rpah = $d_rpah->toArray();
            foreach ($d_rpah as $k_rpah => $v_rpah) {
                $m_drpah = new \Model\Storage\DetRpah_model();
                $d_drpah = $m_drpah->where('id_rpah', $v_rpah['id'])->get();

                if ( $d_drpah->count() > 0 ) {
                    $d_drpah = $d_drpah->toArray();

                    $nama = null;
                    foreach ($d_drpah as $k_drpah => $v_drpah) {
                        $m_rs = new \Model\Storage\RdimSubmit_model();
                        $d_rs = $m_rs->where('noreg', $v_drpah['noreg'])->with(['mitra'])->orderBy('id', 'desc')->first();

                        if ( !isset($data[ $v_drpah['noreg'] ]) ) {
                            $nama = $d_rs->mitra->dMitra->nama;
                            $data[ $nama.'-'.$v_drpah['noreg'] ] = array(
                                'noreg' => $v_drpah['noreg'],
                                'nomor' => $d_rs->mitra->dMitra->nomor,
                                'mitra' => $nama,
                                'kandang' => substr($v_drpah['noreg'], -2),
                                'tgl_panen' => $params['tgl_panen'],
                                'bottom_price' => $v_rpah['bottom_price'],
                                'ekor' => $v_drpah['ekor'],
                                'tonase' => $v_drpah['tonase'],
                                'bb' => ($v_drpah['tonase'] > 0 && $v_drpah['ekor'] > 0) ? $v_drpah['tonase'] / $v_drpah['ekor'] : 0,
                                'g_status' => $v_rpah['g_status']
                            );
                        } else {
                            $data[ $nama.'-'.$v_drpah['noreg'] ]['ekor'] += $v_drpah['ekor'];
                            $data[ $nama.'-'.$v_drpah['noreg'] ]['tonase'] += $v_drpah['tonase'];
                            $data[ $nama.'-'.$v_drpah['noreg'] ]['bb'] = $data[ $nama.'-'.$v_drpah['noreg'] ]['tonase'] / $data[ $nama.'-'.$v_drpah['noreg'] ]['ekor'];
                        }
                    }
                }
            }

            if ( !empty($data) ) {
                ksort($data);
            }
        }

        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['data'] = $data;
        $html = $this->load->view('transaksi/rpah_mobile/list', $content, TRUE);

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
            $html = $this->edit_form($noreg, $tgl_panen, $nomor);
        }else{
            $mitra = $this->get_mitra();
            $pelanggan = $this->get_pelanggan();
            $html = $this->add_form($mitra, $pelanggan);
        }

        echo $html;
    }

    public function add_form($mitra, $pelanggan)
    {
        $content['data_mitra'] = $mitra;
        $content['data_pelanggan'] = $pelanggan;
        $html = $this->load->view('transaksi/rpah_mobile/add_form', $content, TRUE);

        return $html;
    }

    public function detail_form($noreg, $tgl_panen, $nomor)
    {
        $data = null;

        $edit = 1;

        $m_rpah = new \Model\Storage\Rpah_model();
        $d_rpah = $m_rpah->where('tgl_panen', $tgl_panen)->get();

        if ( $d_rpah->count() > 0 ) {
            $detail = null;
            $harga_dasar = null;
            $id_unit = null;

            $d_rpah = $d_rpah->toArray();
            foreach ($d_rpah as $k_rpah => $v_rpah) {
                $m_drpah = new \Model\Storage\DetRpah_model();
                $d_drpah = $m_drpah->where('id_rpah', $v_rpah['id'])->where('noreg', $noreg)->get();


                if ( $d_drpah->count() > 0 ) {
                    $d_drpah = $d_drpah->toArray();

                    foreach ($d_drpah as $k_drpah => $v_drpah) {
                        $m_dsj = new \Model\Storage\DetRealSJ_model();
                        $d_dsj = $m_dsj->where('id_det_rpah', $v_drpah['id'])->first();

                        if ( $d_dsj ) {
                            $edit = 0;
                        }

                        $detail[ $v_drpah['no_pelanggan'] ]['pelanggan'] = $v_drpah['pelanggan'];
                        $detail[ $v_drpah['no_pelanggan'] ]['no_pelanggan'] = $v_drpah['no_pelanggan'];
                        $detail[ $v_drpah['no_pelanggan'] ]['rencana_panen'][ $v_drpah['id'] ] = array(
                            'pelanggan' => $v_drpah['pelanggan'],
                            'no_pelanggan' => $v_drpah['no_pelanggan'],
                            'tonase' => $v_drpah['tonase'],
                            'ekor' => $v_drpah['ekor'],
                            'bb' => $v_drpah['bb'],
                            'harga' => $v_drpah['harga'],
                        );

                        $harga_dasar = $v_rpah['bottom_price'];
                        $id_unit = $v_rpah['id_unit'];
                    }
                }
            }

            $m_rs = new \Model\Storage\RdimSubmit_model();
            $d_rs = $m_rs->where('noreg', $noreg)->with(['mitra'])->orderBy('id', 'desc')->first();

            $m_konfir = new \Model\Storage\Konfir_model();
            $_d_konfir = $m_konfir->where('noreg', $noreg)->where('tgl_panen', $tgl_panen)->with(['det_konfir'])->orderBy('id', 'desc')->first();


            $populasi = 0;
            $total = 0;
            if ( $_d_konfir ) {
                $_d_konfir = $_d_konfir->toArray();
                foreach ($_d_konfir['det_konfir'] as $k_det => $v_det) {
                    $populasi += $v_det['jumlah'];
                    $total += $v_det['jumlah'] * $v_det['bb'];
                }
            }

            $m_wilayah = new \Model\Storage\Wilayah_model();
            $d_wilayah = $m_wilayah->where('id', $id_unit)->orderBy('id', 'desc')->first();

            $data = array(
                'kode_unit' => $d_wilayah->kode,
                'noreg' => $noreg,
                'mitra' => $d_rs->mitra->dMitra->nama,
                'nomor' => $nomor,
                'tgl_panen' => $tgl_panen,
                'ekor' => $populasi,
                'tonase' => $total,
                'umur' => abs(selisihTanggal(substr($d_rs->tgl_docin, 0, 10), $tgl_panen)),
                'harga_dasar' => $harga_dasar,
                'detail' => $detail
            );
        }

        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['data'] = $data;
        $content['edit'] = $edit;
        $html = $this->load->view('transaksi/rpah_mobile/detail_form', $content, TRUE);

        return $html;
    }

    public function edit_form($noreg, $tgl_panen, $nomor)
    {
        $data = null;

        $m_rpah = new \Model\Storage\Rpah_model();
        $d_rpah = $m_rpah->where('tgl_panen', $tgl_panen)->get();

        if ( $d_rpah->count() > 0 ) {
            $detail = null;
            $harga_dasar = null;
            $id_unit = null;

            $d_rpah = $d_rpah->toArray();
            foreach ($d_rpah as $k_rpah => $v_rpah) {
                $m_drpah = new \Model\Storage\DetRpah_model();
                $d_drpah = $m_drpah->where('id_rpah', $v_rpah['id'])->where('noreg', $noreg)->get();

                if ( $d_drpah->count() > 0 ) {
                    $d_drpah = $d_drpah->toArray();

                    foreach ($d_drpah as $k_drpah => $v_drpah) {
                        $detail[ $v_drpah['no_pelanggan'] ]['pelanggan'] = $v_drpah['pelanggan'];
                        $detail[ $v_drpah['no_pelanggan'] ]['no_pelanggan'] = $v_drpah['no_pelanggan'];
                        $detail[ $v_drpah['no_pelanggan'] ]['rencana_panen'][ $v_drpah['id'] ] = array(
                            'pelanggan' => $v_drpah['pelanggan'],
                            'no_pelanggan' => $v_drpah['no_pelanggan'],
                            'tonase' => $v_drpah['tonase'],
                            'ekor' => $v_drpah['ekor'],
                            'bb' => $v_drpah['bb'],
                            'harga' => $v_drpah['harga'],
                        );

                        $harga_dasar = $v_rpah['bottom_price'];
                        $id_unit = $v_rpah['id_unit'];
                    }
                }
            }

            $m_rs = new \Model\Storage\RdimSubmit_model();
            $d_rs = $m_rs->where('noreg', $noreg)->with(['mitra'])->orderBy('id', 'desc')->first();

            $m_konfir = new \Model\Storage\Konfir_model();
            $_d_konfir = $m_konfir->where('noreg', $noreg)->where('tgl_panen', $tgl_panen)->with(['det_konfir'])->orderBy('id', 'desc')->first();


            $populasi = 0;
            $total = 0;
            if ( $_d_konfir ) {
                $_d_konfir = $_d_konfir->toArray();
                foreach ($_d_konfir['det_konfir'] as $k_det => $v_det) {
                    $populasi += $v_det['jumlah'];
                    $total += $v_det['jumlah'] * $v_det['bb'];
                }
            }


            $m_wilayah = new \Model\Storage\Wilayah_model();
            $d_wilayah = $m_wilayah->where('id', $id_unit)->orderBy('id', 'desc')->first();

            $data = array(
                'kode_unit' => $d_wilayah->kode,
                'noreg' => $noreg,
                'mitra' => $d_rs->mitra->dMitra->nama,
                'nomor' => $nomor,
                'tgl_panen' => $tgl_panen,
                'ekor' => $populasi,
                'tonase' => $total,
                'umur' => abs(selisihTanggal(substr($d_rs->tgl_docin, 0, 10), $tgl_panen)),
                'harga_dasar' => $harga_dasar,
                'detail' => $detail
            );
        }

        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['data'] = $data;
        $content['data_mitra'] = $this->get_mitra();
        $content['data_pelanggan'] = $this->get_pelanggan();
        $html = $this->load->view('transaksi/rpah_mobile/edit_form', $content, TRUE);

        return $html;
    }

    public function get_pelanggan()
    {
        // $m_plg = new \Model\Storage\Pelanggan_model();
        // $d_plg = $m_plg->select('nomor')->where('mstatus', 1)->get();

        // $data = null;
        // if ( $d_plg->count() > 0 ) {
        //     $d_plg = $d_plg->toArray();
        //     foreach ($d_plg as $k_plg => $v_plg) {
        //         $_d_plg = $m_plg->where('nomor', $v_plg['nomor'])->where('tipe', 'pelanggan')->where('mstatus', 1)->orderBy('id', 'desc')->first();

        //         if ( $_d_plg ) {
        //             $_d_plg = $_d_plg->toArray();

        //             $m_lokasi = new \Model\Storage\Lokasi_model();
        //             $d_kec = $m_lokasi->where('id', $_d_plg['alamat_kecamatan'])->first();
        //             $d_kab = $m_lokasi->where('id', $d_kec['induk'])->first();

        //             $kota_kab = str_replace('Kota ', '', str_replace('Kab ', '', $d_kab->nama));
        //             $key = $_d_plg['nama'].'|'.$_d_plg['nomor'];
        //             $data[$key] = $_d_plg;
        //             $data[$key]['kab_kota'] = $kota_kab;

        //             ksort($data);
        //         }
        //     }
        // }

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
            $d_ukaryawan = $m_ukaryawan->where('id_karyawan', $d_karyawan->id)->get();

            if ( $d_ukaryawan->count() > 0 ) {
                $d_ukaryawan = $d_ukaryawan->toArray();

                foreach ($d_ukaryawan as $k_ukaryawan => $v_ukaryawan) {
                    if ( stristr($v_ukaryawan['unit'], 'all') === false ) {
                        $m_wil = new \Model\Storage\Wilayah_model();
                        $d_wil = $m_wil->where('id', $v_ukaryawan['unit'])->first();

                        array_push($kode_unit, $d_wil->kode);
                        // $kode_unit = $d_wil->kode;
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

        $end_date = date('Y-m-d').' 00:00:00.000';
        $start_date = prev_date(date('Y-m-d'), 60).' 23:59:59.999';

        $m_mm = new \Model\Storage\MitraMapping_model();
        $d_mm = $m_mm->select('nim')->where('nomor', $nomor_mitra)->get()->toArray();

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->whereIn('nim', $d_mm)->whereBetween('tgl_docin', [$start_date, $end_date])->get();

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
                        $d_td = $m_td->where('no_order', $d_od->no_order)->orderBy('id', 'desc')->first();

                        if ( !empty($d_td) ) {
                            $tgl_docin = substr($d_td->datang, 0, 10);
                            
                            $kandang = (int) substr($v_rs['noreg'], -1);
        
                            $key = str_replace('-', '', $tgl_docin).' - '.substr($v_rs['noreg'], -1);
                            $_data[ $key ] = array(
                                'noreg' => $v_rs['noreg'],
                                'real_tgl_docin' => $tgl_docin,
                                'tgl_docin' => strtoupper(tglIndonesia($tgl_docin, '-', ' ')),
                                'kandang' => 'KD - '.$kandang,
                                'umur' => selisihTanggal($tgl_docin, date('Y-m-d'))
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

    public function get_tgl_konfir()
    {
        $noreg = $this->input->post('params');

        $m_konfir = new \Model\Storage\Konfir_model();
        $d_konfir = $m_konfir->where('noreg', $noreg)->get();

        $_data = null;
        if ( $d_konfir->count() > 0 ) {
            $d_konfir = $d_konfir->toArray();

            foreach ($d_konfir as $k_konfir => $v_konfir) {
                $_data[ $v_konfir['tgl_panen'] ] = array(
                    'tgl_panen' => $v_konfir['tgl_panen'],
                    'tgl_panen_after_format' => strtoupper(tglIndonesia($v_konfir['tgl_panen'], '-', ' ', true))
                );
            }
        }

        $data = null;
        if ( !empty($_data) ) {
            krsort($_data);
            foreach ($_data as $k_data => $v_data) {
                $data[] = $v_data;
            }
        }

        $this->result['content'] = $data;

        display_json( $this->result );
    }

    public function get_konfir()
    {
        $params = $this->input->post('params');

        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->select('id')->where('kode', $params['kode_unit'])->get()->toArray();

        $m_konfir = new \Model\Storage\Konfir_model();
        $_d_konfir = $m_konfir->where('noreg', $params['noreg'])->where('tgl_panen', $params['tgl_panen'])->with(['det_konfir'])->first();

        $d_konfir = null;
        if ( $_d_konfir ) {
            $_d_konfir = $_d_konfir->toArray();

            $ekor_konfir = 0;
            $tonase_konfir = 0;
            foreach ($_d_konfir['det_konfir'] as $k_det => $v_det) {
                $ekor_konfir += $v_det['jumlah'];
                $tonase_konfir += $v_det['jumlah'] * $v_det['bb'];
            }

            $d_konfir = array(
                'populasi' => $ekor_konfir,
                'total' => $tonase_konfir
            );
        }

        $m_rpah = new \Model\Storage\Rpah_model();
        $d_rpah = $m_rpah->whereIn('id_unit', $d_wilayah)->where('tgl_panen', $params['tgl_panen'])->first();

        if ( $d_konfir ) {
            $this->result['status'] = 1;
            $this->result['content'] = array(
                'konfir' => $d_konfir,
                'harga_dasar' => !empty($d_rpah) ? $d_rpah->bottom_price : 0
            );
        } else {
            $m_mitra = new \Model\Storage\Mitra_model();
            $d_mitra = $m_mitra->where('nomor', $params['mitra'])->orderBy('id', 'desc')->first();

            $this->result['message'] = 'Data konfirmasi pada mitra <b>'.strtoupper($d_mitra->nama).'</b> dan tanggal panen <b>'.strtoupper(tglIndonesia($params['tgl_panen'], '-', ' ', true)).'</b> tidak ditemukan.';
        }

        display_json( $this->result );
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_wilayah = new \Model\Storage\Wilayah_model();
            $d_wilayah = $m_wilayah->select('id')->where('kode', $params['kode_unit'])->get()->toArray();

            $m_rpah = new \Model\Storage\Rpah_model();
            $d_rpah = $m_rpah->whereIn('id_unit', $d_wilayah)->where('tgl_panen', $params['tgl_panen'])->first();

            $id_rpah = null;
            if ( $d_rpah ) {
                $id_rpah = $d_rpah->id;
            } else {
                $_d_wilayah = $m_wilayah->where('kode', $params['kode_unit'])->first();

                $m_rpah->id_unit = $_d_wilayah->id;
                $m_rpah->unit = strtoupper($_d_wilayah->nama);
                $m_rpah->tgl_panen = $params['tgl_panen'];
                $m_rpah->bottom_price = $params['harga_dasar'];
                $m_rpah->g_status = 1;
                $m_rpah->save();

                $id_rpah = $m_rpah->id;
            }

            $m_konfir = new \Model\Storage\Konfir_model();
            $d_konfir = $m_konfir->where('noreg', $params['noreg'])->where('tgl_panen', $params['tgl_panen'])->first();

            if (  !empty($params['detail'])) {
                foreach ($params['detail'] as $k => $val) {
                    $m_drpah = new \Model\Storage\DetRpah_model();

                    $no_do = $m_drpah->getNextNo('no_do','DO/'.$params['kode_unit']);
                    $no_sj = $m_drpah->getNextNo('no_sj','SJ/'.$params['kode_unit']);

                    $m_drpah->id_rpah = $id_rpah;
                    $m_drpah->id_konfir = $d_konfir->id;
                    $m_drpah->noreg = $params['noreg'];
                    $m_drpah->no_pelanggan = $val['no_pelanggan'];
                    $m_drpah->pelanggan = $val['pelanggan'];
                    $m_drpah->outstanding = 0;
                    $m_drpah->tonase = $val['tonase'];
                    $m_drpah->ekor = $val['ekor'];
                    $m_drpah->bb = $val['bb'];

                    $m_drpah->harga = $val['harga'];
                    $m_drpah->no_do = $no_do;
                    $m_drpah->no_sj = $no_sj;
                    $m_drpah->save();
                }
            }

            $d_rpah = $m_rpah->where('id', $id_rpah)->first();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_rpah, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil disimpan';
            $this->result['content'] = array('id' => $id_rpah);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit()
    {
        $params = $this->input->post('params');

        try {
            $id_rpah = null;
            if ( $params['kode_unit'] == $params['kode_unit_old'] &&
                 $params['tgl_panen'] == $params['tgl_panen_old'] ) {

                /* JIKA TIDAK ADA PERUBAHAN NOREG DAN MITRA */
                $m_wilayah = new \Model\Storage\Wilayah_model();
                $d_wilayah = $m_wilayah->select('id')->where('kode', $params['kode_unit'])->get()->toArray();

                $m_rpah = new \Model\Storage\Rpah_model();
                $d_rpah = $m_rpah->whereIn('id_unit', $d_wilayah)->where('tgl_panen', $params['tgl_panen'])->orderBy('id', 'desc')->first();

                $id_rpah = $d_rpah->id;

                $m_rpah->where('id', $id_rpah)->update(
                    array(
                        'bottom_price' => $params['harga_dasar']
                    )
                );
                
                $m_konfir = new \Model\Storage\Konfir_model();
                $d_konfir = $m_konfir->where('noreg', $params['noreg'])->where('tgl_panen', $params['tgl_panen'])->first();

                $m_drpah = new \Model\Storage\DetRpah_model();
                $m_drpah->where('id_rpah', $id_rpah)->whereIn('noreg', [$params['noreg'], $params['noreg_old']])->delete();
                if ( !empty($params['detail']) ) {
                    foreach ($params['detail'] as $k => $val) {
                        $m_drpah = new \Model\Storage\DetRpah_model();

                        $no_do = $m_drpah->getNextNo('no_do','DO/'.$params['kode_unit']);
                        $no_sj = $m_drpah->getNextNo('no_sj','SJ/'.$params['kode_unit']);

                        $m_drpah->id_rpah = $id_rpah;
                        $m_drpah->id_konfir = $d_konfir->id;
                        $m_drpah->noreg = $params['noreg'];
                        $m_drpah->no_pelanggan = $val['no_pelanggan'];
                        $m_drpah->pelanggan = $val['pelanggan'];
                        $m_drpah->outstanding = 0;
                        $m_drpah->tonase = $val['tonase'];
                        $m_drpah->ekor = $val['ekor'];
                        $m_drpah->bb = $val['bb'];

                        $m_drpah->harga = $val['harga'];
                        $m_drpah->no_do = $no_do;
                        $m_drpah->no_sj = $no_sj;
                        $m_drpah->save();
                    }
                }
            } else {
                /* JIKA TIDAK ADA PERUBAHAN NOREG DAN MITRA */
                $m_wilayah = new \Model\Storage\Wilayah_model();
                $d_wilayah_new = $m_wilayah->select('id')->where('kode', $params['kode_unit'])->get()->toArray();
                $d_wilayah_old = $m_wilayah->select('id')->where('kode', $params['kode_unit_old'])->get()->toArray();

                $m_rpah = new \Model\Storage\Rpah_model();
                $m_drpah = new \Model\Storage\DetRpah_model();

                $m_konfir = new \Model\Storage\Konfir_model();
                $d_konfir = $m_konfir->where('noreg', $params['noreg'])->where('tgl_panen', $params['tgl_panen'])->first();

                $d_rpah_new = $m_rpah->whereIn('id_unit', $d_wilayah_new)->where('tgl_panen', $params['tgl_panen'])->orderBy('id', 'desc')->first();

                /* CEK APAKAN DATA RPAH SUDAH ADA JIKA SUDAH AMBIL ID JIKA BELUM SAVE BARU */
                if ( $d_rpah_new ) {
                    $id_rpah = $d_rpah_new->id;

                    $m_rpah->where('id', $id_rpah)->update(
                        array(
                            'bottom_price' => $params['harga_dasar']
                        )
                    );
                } else {
                    $m_wilayah = new \Model\Storage\Wilayah_model();
                    $_d_wilayah = $m_wilayah->where('kode', $params['kode_unit'])->first();

                    $m_rpah->id_unit = $_d_wilayah->id;
                    $m_rpah->unit = strtoupper($_d_wilayah->nama);
                    $m_rpah->tgl_panen = $params['tgl_panen'];
                    $m_rpah->bottom_price = $params['harga_dasar'];
                    $m_rpah->g_status = 1;
                    $m_rpah->save();

                    $id_rpah = $m_rpah->id;
                }

                /* SAVE ULANG BERDASARKAN NOREG */
                $m_drpah->where('id_rpah', $id_rpah)->whereIn('noreg', [$params['noreg'], $params['noreg_old']])->delete();
                if ( !empty($params['detail']) ) {
                    foreach ($params['detail'] as $k => $val) {
                        $m_drpah = new \Model\Storage\DetRpah_model();

                        $no_do = $m_drpah->getNextNo('no_do','DO/'.$params['kode_unit']);
                        $no_sj = $m_drpah->getNextNo('no_sj','SJ/'.$params['kode_unit']);

                        $m_drpah->id_rpah = $id_rpah;
                        $m_drpah->id_konfir = $d_konfir->id;
                        $m_drpah->noreg = $params['noreg'];
                        $m_drpah->no_pelanggan = $val['no_pelanggan'];
                        $m_drpah->pelanggan = $val['pelanggan'];
                        $m_drpah->outstanding = 0;
                        $m_drpah->tonase = $val['tonase'];
                        $m_drpah->ekor = $val['ekor'];
                        $m_drpah->bb = $val['bb'];

                        $m_drpah->harga = $val['harga'];
                        $m_drpah->no_do = $no_do;
                        $m_drpah->no_sj = $no_sj;
                        $m_drpah->save();
                    }
                }

                /* HAPUS DATA LAMA */
                $d_rpah_old = $m_rpah->whereIn('id_unit', $d_wilayah_old)->where('tgl_panen', $params['tgl_panen_old'])->orderBy('id', 'desc')->first();
                if ( $d_rpah_old ) {
                    $m_drpah->where('id_rpah', $d_rpah_old->id)->whereIn('noreg', [$params['noreg'], $params['noreg_old']])->delete();
                }
            }

            $d_rpah = $m_rpah->where('id', $id_rpah)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_rpah, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di update';
            $this->result['content'] = array('id' => $id_rpah);            
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {
            $m_rpah = new \Model\Storage\Rpah_model();
            $id_rpah = $m_rpah->select('id')->where('tgl_panen', $params['tgl_panen'])->get()->toArray();

            $m_drpah = new \Model\Storage\DetRpah_model();
            $d_drpah = $m_drpah->whereIn('id_rpah', $id_rpah)->where('noreg', $params['noreg'])->first();

            if ( !empty($d_drpah) ) {
                $id = $d_drpah->id_rpah;

                $m_drpah->whereIn('id_rpah', $id_rpah)->where('noreg', $params['noreg'])->delete();
                $d_rpah = $m_rpah->where('id', $id)->first();

                $deskripsi_log = 'hapus data rpah noreg '+$params['noreg']+' oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/delete', $d_rpah, $deskripsi_log);
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus';
            $this->result['content'] = array('id' => $id_rpah);            
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function cekPelanggan() {
        $params = $this->input->post('params');
        try {
            $no_pelanggan = $params['no_pelanggan'];

            $m_conf = new \Model\Storage\Conf();
            $now = $m_conf->getDate();

            $sql = "
                select
                    d_rs.no_pelanggan,
                    plg.nama as nama_pelanggan,
                    min(d_rs.tgl_panen) as tgl_terkecil,
                    count(d_rs.tgl_panen) as jumlah_do
                from
                (
                    select 
                        drs.no_pelanggan, 
                        rs.tgl_panen
                    from det_real_sj drs
                    right join	
                        (
                            select rs1.* from real_sj rs1
                            right join
                                (select max(id) as id, noreg, tgl_panen from real_sj group by noreg, tgl_panen) rs2
                                on
                                    rs1.id = rs2.id
                        ) rs
                        on
                            drs.id_header = rs.id
                    left join
                        (
                            select dpp1.* from det_pembayaran_pelanggan dpp1
                            right join
                                (select max(id) as id, id_do from det_pembayaran_pelanggan group by id_do) dpp2
                                on
                                    dpp1.id = dpp2.id
                        ) dpp
                        on
                            dpp.id_do =  drs.id
                    left join
                        (
                            select no_pelanggan, max(tgl_min) as tgl_min from (
                                select no_pelanggan, max(tgl_mulai_bayar) as tgl_min from saldo_pelanggan sp group by no_pelanggan
                                
                                union all
                                
                                select drs.no_pelanggan, max(rs.tgl_panen) as tgl_min from det_real_sj drs 
                                right join
                                    (
                                        select dpp1.* from det_pembayaran_pelanggan dpp1
                                        right join
                                            (select max(id) as id, id_do from det_pembayaran_pelanggan group by id_do) dpp2
                                            on
                                                dpp1.id = dpp2.id
                                        where
                                            dpp1.status = 'LUNAS'
                                    ) dpp
                                    on
                                        drs.id = dpp.id_do
                                right join
                                    real_sj rs 
                                    on
                                        drs.id_header = rs.id
                                group by
                                    drs.no_pelanggan
                            ) data
                            where
                                no_pelanggan is not null
                            group by
                                no_pelanggan
                        ) tgl_min_bayar
                        on
                            drs.no_pelanggan = tgl_min_bayar.no_pelanggan
                    where
                        drs.no_pelanggan is not null and
                        drs.harga > 0 and
                        (dpp.status = 'BELUM' or dpp.id is null) and
                        rs.tgl_panen >= tgl_min_bayar.tgl_min
                    group by
                        drs.no_pelanggan, 
                        rs.tgl_panen
                ) d_rs
                left join
                    (
                        select plg1.* from pelanggan plg1
                        right join
                            (select max(id) as id, nomor from pelanggan where jenis <> 'ekspedisi' and tipe = 'pelanggan' group by nomor) plg2
                            on
                                plg1.id = plg2.id
                    ) plg
                    on
                        plg.nomor = d_rs.no_pelanggan
                where
                    d_rs.no_pelanggan = '".$no_pelanggan."'
                group by
                    plg.nama,
                    d_rs.no_pelanggan
            ";
            $d_drpah = $m_conf->hydrateRaw( $sql );

            $fulfil = 1;
            $selisih_hari = 0;
            $jumlah_do = 0;
            $html = null;
            if ( $d_drpah->count() > 0 ) {
                $d_drpah = $d_drpah->toArray()[0];

                $selisih_hari = (selisihTanggal($d_drpah['tgl_terkecil'], $now['tanggal']));
                $jumlah_do = $d_drpah['jumlah_do'];

                if ( $selisih_hari > 3 || $jumlah_do > 3 ) {
                    $fulfil = 0;
                    $html = "
                        Bakul <b>".$d_drpah['nama_pelanggan']."</b> tidak memenuhi syarat pengambilan DO.<br>
                        <b>Syarat & Ketentuan :</b><br>
                        1. Maximal tempo DO yang belum terbayar adalah 3 hari.<br>
                        2. Maximal jumlah DO yang belum terbayar adalah 3.<br><br>
                        <b>Kelayakan Bakul :</b><br>
                        1. Tempo DO yang belum terbayar <b>".$selisih_hari."</b> hari.<br>
                        2. Jumlah DO yang belum terbayar <b>".$jumlah_do."</b>.<br><br>

                        Apakah anda yakin ingin tetap memasukkan bakul ini ?<br><br>
                        <b style='color: red; text-transform: uppercase;'>* Bakul ini membutuhkan persetujuan atasan *</b>
                    ";
                }
            }

            $this->result['status'] = 1;
            $this->result['content'] = array(
                'fulfil' => $fulfil,
                'selisih_hari' => $selisih_hari,
                'jumlah_do' => $jumlah_do,
                'html' => $html
            );
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes()
    {
        // $m_drpah = new \Model\Storage\DetRpah_model();
        // $kode_unit = 'JBR';
        // $no_do = $m_drpah->getNextNo('no_do','DO/'.$kode_unit);

        cetak_r($this->get_pelanggan());
    }
}