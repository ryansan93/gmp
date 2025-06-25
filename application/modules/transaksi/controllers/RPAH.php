<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RPAH extends Public_Controller {

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
            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/transaksi/rpah/js/rpah.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/rpah/css/rpah.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;

            $a_content['unit'] = $this->get_unit();
            $content['add_form'] = $this->load->view('transaksi/rpah/add_form', $a_content, TRUE);

            // Load Indexx
            $data['title_menu'] = 'Pengajuan Rencana Penjualan Harian';
            $data['view'] = $this->load->view('transaksi/rpah/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_lists()
    {
        $params = $this->input->get('params');

        $m_rpah = new \Model\Storage\Rpah_model();
        $d_rpah = $m_rpah->whereBetween('tgl_panen', [$params['start_date'], $params['end_date']])->orderBy('id', 'desc')->get();

        $data = null;
        if ( $d_rpah->count() > 0 ) {
            $d_rpah = $d_rpah->toArray();
            foreach ($d_rpah as $k => $val) {
                $m_lt = new \Model\Storage\LogTables_model();
                $d_lt = $m_lt->select('tbl_name', 'tbl_id', 'user_id', 'waktu', 'deskripsi', '_action')->where('tbl_name', 'rpah')->where('tbl_id', $val['id'])->orderBy('id', 'desc')->first();

                $data[ $val['id'] ] = array(
                    'id' => $val['id'],
                    'id_unit' => $val['id_unit'],
                    'unit' => $val['unit'],
                    'tgl_panen' => $val['tgl_panen'],
                    'bottom_price' => $val['bottom_price'],
                    'g_status' => $val['g_status'],
                    'log' => !empty($d_lt) ? $d_lt->toArray() : null
                );
            }
        }

        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['data'] = $data;
        $html = $this->load->view('transaksi/rpah/list', $content, TRUE);

        echo $html;

        // $this->result['status'] = 1;
        // $this->result['html'] = $html;

        // display_json( $this->result );
    }

    public function load_form()
    {
        $id = $this->input->get('id');
        $resubmit = $this->input->get('resubmit');
        $content = array();
        $html = "url not found";
        
        if ( !empty($id) && is_numeric($id) && $resubmit != 'edit' ) {
            // NOTE: view data BASTTB (ajax)
            $html = $this->view($id);
        } else if ( !empty($id) && is_numeric($id) && $resubmit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $html = $this->update($id);
        }else{
            $content['unit'] = $this->get_unit();
            $html = $this->load->view('transaksi/rpah/add_form', $content, TRUE);
        }

        echo $html;
    }

    public function get_mitra()
    {
        $data = array();

        $m_duser = new \Model\Storage\DetUser_model();
        $d_duser = $m_duser->where('id_user', $this->userid)->first();

        $m_karyawan = new \Model\Storage\Karyawan_model();
        $d_karyawan = $m_karyawan->where('nama', 'like', strtolower(trim($d_duser->nama_detuser)).'%')->first();

        $kode_unit = null;
        if ( $d_karyawan ) {
            $m_ukaryawan = new \Model\Storage\UnitKaryawan_model();
            $d_ukaryawan = $m_ukaryawan->where('id_karyawan', $d_karyawan->id)->get()->toArray();

            foreach ($d_ukaryawan as $k_ukaryawan => $v_ukaryawan) {
                if ( $v_ukaryawan['unit'] != 'all' ) {
                    $m_wil = new \Model\Storage\Wilayah_model();
                    $d_wil = $m_wil->where('id', $v_ukaryawan['unit'])->first();

                    $kode_unit = $d_wil->kode;
                } else {
                    $kode_unit = $v_ukaryawan['unit'];
                }
            }
        } else {
            $kode_unit = 'all';
        }

        $m_mitra = new \Model\Storage\Mitra_model();
        $_d_mitra = $m_mitra->select('nomor')->distinct('nomor')->get();

        if ( $_d_mitra->count() > 0 ) {
            $_d_mitra = $_d_mitra->toArray();
            foreach ($_d_mitra as $k_mitra => $v_mitra) {
                $d_mitra = $m_mitra->select('nama', 'nomor')->where('nomor', $v_mitra['nomor'])->orderBy('id', 'desc')->first();

                $m_mm = new \Model\Storage\MitraMapping_model();
                $d_mm = $m_mm->where('nomor', $d_mitra->nomor)->orderBy('id', 'desc')->first();

                if ( $d_mm ) {
                    $m_kdg = new \Model\Storage\Kandang_model();
                    $d_kdg = $m_kdg->where('mitra_mapping', $d_mm->id)->with(['d_unit'])->first();

                    $key = $d_mitra->nama.' | '.$d_mitra->nomor;
                    if ( $kode_unit != 'all' ) {
                        if ( $kode_unit == $d_kdg->d_unit->kode ) {
                            $data[ $key ] = array(
                                'nomor' => $d_mitra->nomor,
                                'nama' => $d_mitra->nama,
                                'unit' => $d_kdg->d_unit->kode
                            );
                        }
                    } else {
                        $data[ $key ] = array(
                            'nomor' => $d_mitra->nomor,
                            'nama' => $d_mitra->nama,
                            'unit' => $d_kdg->d_unit->kode
                        );
                    }
                }
            }

            ksort($data);
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
                    $m_lhk = new \Model\Storage\Lhk_model();
                    $d_lhk = $m_lhk->where('noreg', $v_rs['noreg'])->orderBy('umur', 'desc')->first();

                    $m_od = new \Model\Storage\OrderDoc_model();
                    $d_od = $m_od->where('noreg', $v_rs['noreg'])->first();

                    $tgl_docin = substr($v_rs['tgl_docin'], 0, 10);
                    if ( !empty($d_od) ) {
                        $m_td = new \Model\Storage\TerimaDoc_model();
                        $d_td = $m_td->where('no_order', $d_od->no_order)->first();

                        if ( !empty($d_td) ) {
                            $tgl_docin = substr($d_td->datang, 0, 10);
                        }
                    }

                    $kandang = (int) substr($v_rs['noreg'], -1);

                    $key = str_replace('-', '', $tgl_docin).' - '.substr($v_rs['noreg'], -1);
                    $_data[ $key ] = array(
                        'noreg' => $v_rs['noreg'],
                        'real_tgl_docin' => $tgl_docin,
                        'tgl_docin' => strtoupper(tglIndonesia($tgl_docin, '-', ' ')),
                        'tgl_lhk_terakhir' => !empty($d_lhk) ? $d_lhk->tanggal : '',
                        'kandang' => 'KD - '.$kandang,
                        'umur' => selisihTanggal($tgl_docin, date('Y-m-d'))
                    );
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

    public function get_unit()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'UN')->orderBy('nama', 'asc')->get()->toArray();

        return $d_wilayah;
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

    public function get_data()
    {
        $params = $this->input->get('params');

        $data = null;
        $data_rs = null;
        $data_pelanggan = null;

        $unit = $params['unit'];
        $tgl_panen = $params['tgl_panen'];
        if ( !empty($unit) ) {
            $m_wilayah = new \Model\Storage\Wilayah_model();
            $d_wilayah = $m_wilayah->where('id', $unit)->first();

            $kode_unit = $d_wilayah->kode;

            $m_konfir = new \Model\Storage\Konfir_model();
            $noreg = $m_konfir->select('noreg')->where('tgl_panen', $tgl_panen)->get()->toArray();

            if ( count($noreg) > 0 ) {
                $m_rs = new \Model\Storage\RdimSubmit_model();
                $d_rs = $m_rs->whereIn('noreg', $noreg)->orderBy('tgl_docin', 'asc')->get()->toArray();

                if ( !empty($d_rs) ) {
                    foreach ($d_rs as $k => $v_rs) {
                        $m_kdg = new \Model\Storage\Kandang_model();
                        $d_kdg = $m_kdg->where('id', $v_rs['kandang'])->with(['d_unit'])->first();

                        $kdg_kode_unit = $d_kdg['d_unit']['kode'];
                        if ( $kode_unit == $kdg_kode_unit ) {
                            // $data_rs[$v_rs['id']] = $v_rs;

                            // $m_konfir = new \Model\Storage\Konfir_model();
                            $d_konfir = $m_konfir->where('noreg', $v_rs['noreg'])->where('tgl_panen', $tgl_panen)->with(['det_konfir'])->first();

                            $m_mm = new \Model\Storage\MitraMapping_model();
                            $d_mm = $m_mm->where('nim', $v_rs['nim'])->with(['dMitra'])->orderBy('id', 'DESC')->first();

                            $m_kdg = new \Model\Storage\Kandang_model();
                            $d_kdg = $m_kdg->where('id', $v_rs['kandang'])->first();

                            if ( !empty($d_konfir) ) {
                                $data[ $v_rs['id'] ]['mitra'] = $d_mm->dMitra->nama;
                                $data[ $v_rs['id'] ]['kandang'] = $d_kdg->kandang;
                                $data[ $v_rs['id'] ]['unit'] = $kode_unit;
                                $data[ $v_rs['id'] ]['data_konfir'] = $d_konfir->toArray();
                            }
                        }
                    }
                }

                // if ( !empty($data_rs) ) {
                //     foreach ($data_rs as $k_rs => $v_rs) {
                //     }
                // }
            }
            
            $data_pelanggan = $this->get_pelanggan();
        }
        
        $content['data'] = $data;
        $content['data_pelanggan'] = $data_pelanggan;
        // $content['data_pelanggan'] = null;
        $html = $this->load->view('transaksi/rpah/list_data', $content, TRUE);

        echo $html;
    }

    public function view($id)
    {
        $data = null;

        $m_rpah = new \Model\Storage\Rpah_model();
        $d_rpah = $m_rpah->where('id', $id)->first()->toArray();

        $data = $this->mapping_data( $d_rpah );

        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['data'] = $data;
        $html = $this->load->view('transaksi/rpah/view_form', $content, TRUE);

        return $html;
    }

    public function update($id)
    {
        $data = null;

        $m_rpah = new \Model\Storage\Rpah_model();
        $d_rpah = $m_rpah->where('id', $id)->first()->toArray();

        $data = $this->mapping_data( $d_rpah );

        $content['data'] = $data;
        $content['data_pelanggan'] = $this->get_pelanggan();
        $content['unit'] = $this->get_unit();
        $html = $this->load->view('transaksi/rpah/edit_form', $content, TRUE);

        return $html;
    }

    public function mapping_data($params)
    {
        $data = null;

        if ( !empty($params) ) {
            $m_lt = new \Model\Storage\LogTables_model();
            $d_lt = $m_lt->select('waktu', 'deskripsi')->where('tbl_name', 'rpah')->where('tbl_id', $params['id'])->orderBy('id', 'desc')->get()->toArray();

            $data['id'] = $params['id'];
            $data['id_unit'] = $params['id_unit'];
            $data['unit'] = $params['unit'];
            $data['bottom_price'] = $params['bottom_price'];
            $data['tgl_panen'] = $params['tgl_panen'];
            $data['g_status'] = $params['g_status'];
            $data['logs'] = (count($d_lt) > 0) ? $d_lt : null;

            $m_drpah = new \Model\Storage\DetRpah_model();
            $d_drpah = $m_drpah->where('id_rpah', $params['id'])->get();

            $edit = 1;

            if ( $d_drpah->count() > 0 ) {
                $d_drpah = $d_drpah->toArray();
                foreach ($d_drpah as $k => $val) {
                    $m_konfir = new \Model\Storage\Konfir_model();
                    $d_konfir = $m_konfir->where('id', $val['id_konfir'])->with(['det_konfir'])->first();

                    if ( !empty($d_konfir) ) {
                        $d_konfir = $d_konfir->toArray();

                        $m_rs = new \Model\Storage\RdimSubmit_model();
                        $d_rs = $m_rs->where('noreg', $d_konfir['noreg'])->with(['mitra'])->first()->toArray();

                        $m_kandang = new \Model\Storage\Kandang_model();
                        $d_kandang = $m_kandang->select('unit')->where('id', $d_rs['kandang'])->first();

                        $m_wil = new \Model\Storage\Wilayah_model();
                        $d_wil = $m_wil->where('id', $d_kandang->unit)->first();

                        $data['konfir'][ $d_konfir['id'] ]['id'] = $d_konfir['id'];
                        $data['konfir'][ $d_konfir['id'] ]['mitra'] = $d_rs['mitra']['d_mitra']['nama'];
                        $data['konfir'][ $d_konfir['id'] ]['noreg'] = $d_konfir['noreg'];
                        $data['konfir'][ $d_konfir['id'] ]['unit'] = $d_wil->kode;
                        $data['konfir'][ $d_konfir['id'] ]['kandang'] = (int) substr($d_konfir['noreg'], -2);
                        $data['konfir'][ $d_konfir['id'] ]['tonase'] = $d_konfir['total'];

                        $total_ekor_konfir = 0;
                        foreach ($d_konfir['det_konfir'] as $k_dk => $v_dk) {
                            $total_ekor_konfir += $v_dk['jumlah'];
                        }
                        $data['konfir'][ $d_konfir['id'] ]['ekor'] = $total_ekor_konfir;

                        $data['konfir'][ $d_konfir['id'] ]['det_rpah'][ $val['id'] ]['id'] = $val['id'];
                        $data['konfir'][ $d_konfir['id'] ]['det_rpah'][ $val['id'] ]['no_do'] = $val['no_do'];
                        $data['konfir'][ $d_konfir['id'] ]['det_rpah'][ $val['id'] ]['no_sj'] = $val['no_sj'];
                        $data['konfir'][ $d_konfir['id'] ]['det_rpah'][ $val['id'] ]['no_plg'] = $val['no_pelanggan'];
                        $data['konfir'][ $d_konfir['id'] ]['det_rpah'][ $val['id'] ]['plg'] = $val['pelanggan'];
                        $data['konfir'][ $d_konfir['id'] ]['det_rpah'][ $val['id'] ]['outstanding'] = $val['outstanding'];
                        $data['konfir'][ $d_konfir['id'] ]['det_rpah'][ $val['id'] ]['tonase'] = $val['tonase'];
                        $data['konfir'][ $d_konfir['id'] ]['det_rpah'][ $val['id'] ]['ekor'] = $val['ekor'];
                        $data['konfir'][ $d_konfir['id'] ]['det_rpah'][ $val['id'] ]['bb'] = $val['bb'];
                        $data['konfir'][ $d_konfir['id'] ]['det_rpah'][ $val['id'] ]['harga'] = $val['harga'];

                        $m_drs = new \Model\Storage\DetRealSJ_model();
                        $d_drs = $m_drs->where('no_do', $val['no_do'])->first();

                        if ( $d_drs ) {
                            $edit = 0;
                        }
                    }
                }
            }

            $data['edit'] = $edit;
        }

        return $data;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_wilayah = new \Model\Storage\Wilayah_model();
            $d_wilayah = $m_wilayah->where('id', $params['id_unit'])->first();

            $kode_unit = $d_wilayah->kode;

            $d_wilayah_by_kode = $m_wilayah->select('id')->where('kode', $kode_unit)->get()->toArray();

            $m_rpah = new \Model\Storage\Rpah_model();
            $d_rpah = $m_rpah->whereIn('id_unit', $d_wilayah_by_kode)->where('tgl_panen', $params['tgl_panen'])->first();

            if ( $d_rpah ) {
                $id_rpah = $d_rpah->id;
            } else {
                $m_rpah = new \Model\Storage\Rpah_model();
                $m_rpah->id_unit = $params['id_unit'];
                $m_rpah->unit = $params['unit'];
                $m_rpah->tgl_panen = $params['tgl_panen'];
                $m_rpah->bottom_price = $params['bottom_price'];
                $m_rpah->g_status = 1;
                $m_rpah->save();

                $id_rpah = $m_rpah->id;
            }

            if (  !empty($params['data_detail'])) {
                foreach ($params['data_detail'] as $k => $val) {
                    $m_drpah = new \Model\Storage\DetRpah_model();

                    // $m_rs = new \Model\Storage\RdimSubmit_model();
                    // $d_rs = $m_rs->where('noreg', $val['noreg'])->first();

                    // $kode_unit = null;
                    // if ( $d_rs ) {
                    //     $m_kdg = new \Model\Storage\Kandang_model();
                    //     $d_kdg = $m_kdg->where('id', $d_rs->kandang)->first();

                    //     $m_wil = new \Model\Storage\Wilayah_model();
                    //     $d_wil = $m_wil->where('id', $d_kdg->unit)->first();

                    //     $kode_unit = $d_wil->kode;
                    // }

                    $no_do = $m_drpah->getNextNo('no_do','DO/'.$val['unit']);
                    $no_sj = $m_drpah->getNextNo('no_sj','SJ/'.$val['unit']);

                    $m_drpah->id_rpah = $id_rpah;
                    $m_drpah->id_konfir = $val['id_konfir'];
                    $m_drpah->noreg = $val['noreg'];
                    $m_drpah->no_pelanggan = $val['no_plg'];
                    $m_drpah->pelanggan = $val['plg'];
                    $m_drpah->outstanding = $val['outstanding'];
                    $m_drpah->tonase = $val['tonase'];
                    $m_drpah->ekor = $val['ekor'];
                    $m_drpah->bb = $val['bb'];
                    $m_drpah->harga = $val['harga'];
                    $m_drpah->no_do = $no_do;
                    $m_drpah->no_sj = $no_sj;
                    $m_drpah->save();
                }
            }

            $d_rpah = $m_rpah->where('id', $id_rpah)->with(['det_rpah_without_konfir'])->first();

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
            $m_rpah = new \Model\Storage\Rpah_model();
            $id_rpah = $params['id_rpah'];

            $m_rpah->where('id', $id_rpah)->update(
                array(
                    'id_unit' => $params['id_unit'],
                    'unit' => $params['unit'],
                    'tgl_panen' => $params['tgl_panen'],
                    'bottom_price' => $params['bottom_price']
                )
            );

            if (  !empty($params['data_detail'])) {
                foreach ($params['data_detail'] as $k => $val) {
                    $m_drpah = new \Model\Storage\DetRpah_model();
                    $d_drpah = $m_drpah->where('no_do', $val['no_do'])->first();

                    if ( $d_drpah ) {
                        $m_drpah->where('no_do', $val['no_do'])->update(
                            array(
                                'no_pelanggan' => $val['no_plg'],
                                'pelanggan' => $val['plg'],
                                'outstanding' => $val['outstanding'],
                                'tonase' => $val['tonase'],
                                'ekor' => $val['ekor'],
                                'bb' => $val['bb'],
                                'harga' => $val['harga']
                            )
                        );
                    } else {
                        // $m_rs = new \Model\Storage\RdimSubmit_model();
                        // $d_rs = $m_rs->where('noreg', $val['noreg'])->with(['dKandang'])->first();

                        // $kode_unit = null;
                        // if ( $d_rs ) {
                        //     $d_rs = $d_rs->toArray();
                        //     $kode_unit = $d_rs['d_kandang']['d_unit']['kode'];
                        // }

                        $no_do = $m_drpah->getNextNo('no_do','DO/'.$val['unit']);
                        $no_sj = $m_drpah->getNextNo('no_sj','SJ/'.$val['unit']);
                        
                        $m_drpah->id_rpah = $id_rpah;
                        $m_drpah->id_konfir = $val['id_konfir'];
                        $m_drpah->noreg = $val['noreg'];
                        $m_drpah->no_pelanggan = $val['no_plg'];
                        $m_drpah->pelanggan = $val['plg'];
                        $m_drpah->outstanding = $val['outstanding'];
                        $m_drpah->tonase = $val['tonase'];
                        $m_drpah->ekor = $val['ekor'];
                        $m_drpah->bb = $val['bb'];
                        $m_drpah->harga = $val['harga'];
                        $m_drpah->no_do = $no_do;
                        $m_drpah->no_sj = $no_sj;
                        $m_drpah->save();
                    }
                }
            }

            $d_rpah = $m_rpah->where('id', $id_rpah)->with(['det_rpah_without_konfir'])->first();

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
        $id = $this->input->post('id');

        try {
            $m_rpah = new \Model\Storage\Rpah_model();
            $id_rpah = $id;

            $d_rpah = $m_rpah->where('id', $id_rpah)->with(['det_rpah_without_konfir'])->first();

            $m_rpah = new \Model\Storage\Rpah_model();
            $m_rpah->where('id', $id_rpah)->delete();

            $m_drpah = new \Model\Storage\DetRpah_model();
            $m_drpah->where('id_rpah', $id_rpah)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_rpah, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus';
            $this->result['content'] = array('id' => $id_rpah);            
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function approve()
    {
        $id = $this->input->post('id');

        try {
            $m_rpah = new \Model\Storage\Rpah_model();
            $id_rpah = $id;

            $m_rpah->where('id', $id_rpah)->update(
                array(
                    'g_status' => getStatus('approve')
                )
            );

            $d_rpah = $m_rpah->where('id', $id_rpah)->with(['det_rpah_without_konfir'])->first();

            $deskripsi_log = 'di-approve oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_rpah, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di approve';
            $this->result['content'] = array('id' => $id_rpah);            
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function reject()
    {
        $id = $this->input->post('id');

        try {
            $m_rpah = new \Model\Storage\Rpah_model();
            $id_rpah = $id;

            $m_rpah->where('id', $id_rpah)->update(
                array(
                    'g_status' => getStatus('reject')
                )
            );

            $d_rpah = $m_rpah->where('id', $id_rpah)->with(['det_rpah_without_konfir'])->first();

            $deskripsi_log = 'di-reject oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_rpah, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di approve';
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

    public function model($status)
    {
        $m_rpah = new \Model\Storage\Rpah_model();
        $dashboard = $m_rpah->getDashboard($status);

        return $dashboard;
    }

    public function tes()
    {
        // $m_drpah = new \Model\Storage\DetRpah_model();
        // $kode_unit = 'JBR';
        // $no_do = $m_drpah->getNextNo('no_do','DO/'.$kode_unit);

        cetak_r($this->get_pelanggan());
    }
}