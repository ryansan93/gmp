<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PenerimaanPakanMobile extends Public_Controller {

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
                "assets/transaksi/penerimaan_pakan_mobile/js/penerimaan-pakan-mobile.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/penerimaan_pakan_mobile/css/penerimaan-pakan-mobile.css",
            ));

            $data = $this->includes;

            $isMobile = true;
            if ( $detect->isMobile() ) {
                $isMobile = true;
            }

            $mitra = $this->get_mitra();

            $content['akses'] = $akses;
            $content['isMobile'] = $isMobile;
            $content['unit'] = $this->get_unit();

            $content['riwayat'] = $this->riwayat($mitra);
            $content['add_form'] = $this->add_form($mitra);

            // Load Indexx
            $data['title_menu'] = 'Penerimaan Pakan';
            $data['view'] = $this->load->view('transaksi/penerimaan_pakan_mobile/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function riwayat($mitra)
    {
        $content['data_mitra'] = $mitra;
        $html = $this->load->view('transaksi/penerimaan_pakan_mobile/riwayat', $content, TRUE);

        return $html;
    }

    public function list_riwayat()
    {
        $params = $this->input->post('params');

        $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
        $d_kirim_pakan = $m_kirim_pakan->where('tujuan', $params['noreg'])->get();

        $data = array();
        if ( $d_kirim_pakan->count() > 0 ) {
            $d_kirim_pakan = $d_kirim_pakan->toArray();
            foreach ($d_kirim_pakan as $k_kirim_pakan => $v_kirim_pakan) {
                $m_terima_pakan = new \Model\Storage\TerimaPakan_model();
                $d_terima_pakan = $m_terima_pakan->where('id_kirim_pakan', $v_kirim_pakan['id'])->first();

                if ( !empty($d_terima_pakan) ) {
                    $asal = null;
                    if ( $v_kirim_pakan['jenis_kirim'] == 'opkg' ) {
                        $m_gdg = new \Model\Storage\Gudang_model();
                        $d_gdg = $m_gdg->where('id', $v_kirim_pakan['asal'])->first();

                        $asal = $d_gdg->nama;
                    } else if ( $v_kirim_pakan['jenis_kirim'] == 'opkp' ) {
                        $m_rdim_submit = new \Model\Storage\RdimSubmit_model();
                        $d_rdim_submit = $m_rdim_submit->where('noreg', $v_kirim_pakan['asal'])->with(['mitra'])->first();

                        $asal = $d_rdim_submit->mitra->dMitra->nama;
                    }

                    $key = str_replace('-', '', $d_terima_pakan->tgl_terima).' - '.$v_kirim_pakan['no_sj'];
                    $data[ $key ] = array(
                        'no_sj' => $v_kirim_pakan['no_sj'],
                        'tiba' => $d_terima_pakan->tgl_terima,
                        'asal' => $asal
                    );

                    krsort($data);
                }
            }
        }

        $content['data'] = $data;

        $html = $this->load->view('transaksi/penerimaan_pakan_mobile/list_riwayat', $content, TRUE);

        $this->result['html'] = $html;

        display_json( $this->result );
    }

    public function load_form()
    {
        $params = $this->input->post('params');

        $html = null;
        if ( empty($params['id']) && empty($params['edit']) ) {
            $data_mitra = $this->get_mitra();
            $html = $this->add_form( $data_mitra );
        } else if ( !empty($params['id']) && empty($params['edit']) ) {
            $html = $this->detail_form( $params['id'] );
        } else if ( !empty($params['id']) && !empty($params['edit']) ) {
            $data_mitra = $this->get_mitra();
            $html = $this->edit_form( $params['id'], $data_mitra );
        }

        $this->result['html'] = $html;

        display_json( $this->result );
    }

    public function add_form($mitra)
    {
        $content['data_mitra'] = $mitra;
        $html = $this->load->view('transaksi/penerimaan_pakan_mobile/add_form', $content, true);

        return $html;
    }

    public function detail_form($no_sj)
    {
        $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
        $d_kirim_pakan = $m_kirim_pakan->where('no_sj', $no_sj)->with(['detail'])->first()->toArray();

        $m_terima_pakan = new \Model\Storage\TerimaPakan_model();
        $d_terima_pakan = $m_terima_pakan->where('id_kirim_pakan', $d_kirim_pakan['id'])->first();

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('noreg', $d_kirim_pakan['tujuan'])->with(['mitra'])->first();

        $asal = null;
        if ( $d_kirim_pakan['jenis_kirim'] == 'opkg' ) {
            $m_gdg = new \Model\Storage\Gudang_model();
            $d_gdg = $m_gdg->where('id', $d_kirim_pakan['asal'])->first();

            $asal = $d_gdg->nama;
        } else if ( $d_kirim_pakan['jenis_kirim'] == 'opkp' ) {
            $m_rdim_submit = new \Model\Storage\RdimSubmit_model();
            $d_rdim_submit = $m_rdim_submit->where('noreg', $d_kirim_pakan['asal'])->with(['mitra'])->first();

            $asal = $d_rdim_submit->mitra->dMitra->nama;
        }

        $data_brg = null;
        foreach ($d_kirim_pakan['detail'] as $k_det => $v_det) {
            if ( !isset( $data_brg[ $v_det['item'] ] ) ) {
                $m_dterima_pakan = new \Model\Storage\TerimaPakanDetail_model();
                $d_dterima_pakan = $m_dterima_pakan->where('id_header', $d_terima_pakan->id)->where('item', $v_det['item'])->first();

                $data_brg[ $v_det['item'] ] = array(
                    'kode_brg' => $v_det['item'],
                    'nama_brg' => $v_det['d_barang']['nama'],
                    'jml_kirim' => $v_det['jumlah'],
                    'jml_terima' => !empty($d_dterima_pakan) ? $d_dterima_pakan->jumlah : 0,
                    'kondisi' => !empty($d_dterima_pakan) ? $d_dterima_pakan->kondisi : null
                );
            } else {
                $data_brg[ $v_det['item'] ]['jml_kirim'] += $v_det['jumlah'];
            }
        }

        $data = array(
            'no_sj' => $no_sj,
            'mitra' => $d_rs->mitra->dMitra->nama,
            'nomor' => $d_rs->mitra->dMitra->nomor,
            'noreg' => $d_rs->noreg,
            'tiba' => $d_terima_pakan->tgl_terima,
            'asal' => $asal,
            'nopol' => $d_kirim_pakan['no_polisi'],
            'sopir' => $d_kirim_pakan['sopir'],
            'ekspedisi' => $d_kirim_pakan['ekspedisi'],
            'data_brg' => $data_brg
        );

        $content['data'] = $data;
        $html = $this->load->view('transaksi/penerimaan_pakan_mobile/detail_form', $content, true);

        return $html;
    }

    public function edit_form($no_sj, $mitra)
    {
        $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
        $d_kirim_pakan = $m_kirim_pakan->where('no_sj', $no_sj)->with(['detail'])->first()->toArray();

        $m_terima_pakan = new \Model\Storage\TerimaPakan_model();
        $d_terima_pakan = $m_terima_pakan->where('id_kirim_pakan', $d_kirim_pakan['id'])->first();

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('noreg', $d_kirim_pakan['tujuan'])->with(['mitra'])->first();

        $asal = null;
        if ( $d_kirim_pakan['jenis_kirim'] == 'opkg' ) {
            $m_gdg = new \Model\Storage\Gudang_model();
            $d_gdg = $m_gdg->where('id', $d_kirim_pakan['asal'])->first();

            $asal = $d_gdg->nama;
        } else if ( $d_kirim_pakan['jenis_kirim'] == 'opkp' ) {
            $m_rdim_submit = new \Model\Storage\RdimSubmit_model();
            $d_rdim_submit = $m_rdim_submit->where('noreg', $d_kirim_pakan['asal'])->with(['mitra'])->first();

            $asal = $d_rdim_submit->mitra->dMitra->nama;
        }

        $data_brg = null;
        foreach ($d_kirim_pakan['detail'] as $k_det => $v_det) {
            if ( !isset( $data_brg[ $v_det['item'] ] ) ) {
                $m_dterima_pakan = new \Model\Storage\TerimaPakanDetail_model();
                $d_dterima_pakan = $m_dterima_pakan->where('id_header', $d_terima_pakan->id)->where('item', $v_det['item'])->first();

                $data_brg[ $v_det['item'] ] = array(
                    'kode_brg' => $v_det['item'],
                    'nama_brg' => $v_det['d_barang']['nama'],
                    'jml_kirim' => $v_det['jumlah'],
                    'jml_terima' => !empty($d_dterima_pakan) ? $d_dterima_pakan->jumlah : 0,
                    'kondisi' => !empty($d_dterima_pakan) ? $d_dterima_pakan->kondisi : null
                );
            } else {
                $data_brg[ $v_det['item'] ]['jml_kirim'] += $v_det['jumlah'];
            }
        }

        $data = array(
            'no_sj' => $no_sj,
            'mitra' => $d_rs->mitra->dMitra->nama,
            'nomor' => $d_rs->mitra->dMitra->nomor,
            'noreg' => $d_rs->noreg,
            'tiba' => $d_terima_pakan->tgl_terima,
            'asal' => $asal,
            'nopol' => $d_kirim_pakan['no_polisi'],
            'sopir' => $d_kirim_pakan['sopir'],
            'ekspedisi' => $d_kirim_pakan['ekspedisi'],
            'data_brg' => $data_brg
        );

        $content['data'] = $data;
        $content['data_mitra'] = $mitra;
        $html = $this->load->view('transaksi/penerimaan_pakan_mobile/edit_form', $content, true);

        return $html;
    }

    public function get_unit()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'UN')->orderBy('nama', 'asc')->get()->toArray();

        return $d_wilayah;
    }

    public function get_mitra()
    {
        $data = array();

        $m_duser = new \Model\Storage\DetUser_model();
        $d_duser = $m_duser->where('id_user', $this->userid)->first();

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

        $end_date = date('Y-m-d').' 23:59:59.999';
        $start_date = prev_date(date('Y-m-d'), 60).' 00:00:00.000';

        $m_kp = new \Model\Storage\KirimPakan_model();
        $sql = "
            select 
                data.nomor,
                data.nama,
                data.unit
            from
                (
                select
                    kp.no_order,
                    kp.tujuan,
                    m.nomor,
                    m.nama,
                    (SUBSTRING(kp.no_order, 4, 3)) as unit
                from kirim_pakan kp
                right join
                    rdim_submit rs 
                    on
                        rs.noreg = kp.tujuan
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
                    kp.no_order,
                    kp.tujuan,
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
        $d_kp = $m_kp->hydrateRaw( $sql );

        if ( $d_kp->count() > 0 ) {
            $d_kp = $d_kp->toArray();

            $data = $d_kp;
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
        // $d_rs = $m_rs->whereIn('nim', $d_mm)->whereBetween('tgl_docin', [$start_date, $end_date])->get();
        $d_rs = $m_rs->whereIn('nim', $d_mm)->where('tgl_docin', '>', $start_date)->get();

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
                        }
                    }

                    $kandang = (int) substr($v_rs['noreg'], -1);

                    $key = str_replace('-', '', $tgl_docin).' - '.substr($v_rs['noreg'], -1);
                    $_data[ $key ] = array(
                        'noreg' => $v_rs['noreg'],
                        'populasi' => $v_rs['populasi'],
                        'real_tgl_docin' => $tgl_docin,
                        'tgl_docin' => strtoupper(tglIndonesia($tgl_docin, '-', ' ')),
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

    public function get_no_sj()
    {
        $noreg = $this->input->post('noreg');
        $no_sj = $this->input->post('no_sj');

        $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
        $d_kirim_pakan = $m_kirim_pakan->where('tujuan', 'like', '%'.$noreg.'%')->get();

        $_data = null;
        if ( $d_kirim_pakan->count() > 0 ) {
            $d_kirim_pakan = $d_kirim_pakan->toArray();
            foreach ($d_kirim_pakan as $k_kirim_pakan => $v_kirim_pakan) {
                $d_terima_pakan = null;
                if ( $no_sj != $v_kirim_pakan['no_sj'] ) {
                    $m_terima_pakan = new \Model\Storage\TerimaPakan_model();
                    $d_terima_pakan = $m_terima_pakan->where('id_kirim_pakan', $v_kirim_pakan['id'])->first();
                }

                if ( !$d_terima_pakan ) {
                    $_data[ $v_kirim_pakan['no_sj'] ] = array(
                        'no_sj' => $v_kirim_pakan['no_sj'],
                        'tgl_kirim' => $v_kirim_pakan['tgl_kirim'],
                        'tgl_kirim_after_format' => strtoupper(tglIndonesia($v_kirim_pakan['tgl_kirim'], '-', ' '))
                    );
                }

                // $_data[ $v_kirim_pakan['no_sj'] ] = array(
                //         'no_sj' => $v_kirim_pakan['no_sj'],
                //         'tgl_kirim' => $v_kirim_pakan['tgl_kirim'],
                //         'tgl_kirim_after_format' => strtoupper(tglIndonesia($v_kirim_pakan['tgl_kirim'], '-', ' '))
                //     );
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

    public function get_data_sj()
    {
        $no_sj = $this->input->post('no_sj');

        $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
        $d_kirim_pakan = $m_kirim_pakan->where('no_sj', $no_sj)->with(['detail'])->first()->toArray();

        $asal = null;
        if ( $d_kirim_pakan['jenis_kirim'] == 'opkg' ) {
            $m_gdg = new \Model\Storage\Gudang_model();
            $d_gdg = $m_gdg->where('id', $d_kirim_pakan['asal'])->first();

            $asal = $d_gdg->nama;
        } else if ( $d_kirim_pakan['jenis_kirim'] == 'opkp' ) {
            $m_rdim_submit = new \Model\Storage\RdimSubmit_model();
            $d_rdim_submit = $m_rdim_submit->where('noreg', $d_kirim_pakan['asal'])->with(['mitra'])->first();

            $asal = $d_rdim_submit->mitra->dMitra->nama;
        }

        $_data_detail = null;
        foreach ($d_kirim_pakan['detail'] as $k_det => $v_det) {
            if ( !isset( $_data_detail[ $v_det['item'] ] ) ) {
                $_data_detail[ $v_det['item'] ] = array(
                    'kode_brg' => $v_det['item'],
                    'nama_brg' => $v_det['d_barang']['nama'],
                    'jumlah' => $v_det['jumlah']
                );
            } else {
                $_data_detail[ $v_det['item'] ]['jumlah'] += $v_det['jumlah'];
            }
        }

        $data_detail = null;
        if ( !empty($_data_detail) ) {
            ksort($_data_detail);
            foreach ($_data_detail as $k_dd => $v_dd) {
                $data_detail[] = $v_dd;
            }
        }

        $content['data'] = $data_detail;
        $html_detail = $this->load->view('transaksi/penerimaan_pakan_mobile/list_kirim_pakan', $content, true);

        $data = array(
            'asal' => $asal,
            'nopol' => $d_kirim_pakan['no_polisi'],
            'sopir' => $d_kirim_pakan['sopir'],
            'ekspedisi' => $d_kirim_pakan['ekspedisi'],
            'tgl_kirim' => $d_kirim_pakan['tgl_kirim'],
            'html_detail' => $html_detail
        );

        $this->result['content'] = $data;

        display_json( $this->result );
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
            $d_kirim_pakan = $m_kirim_pakan->where('no_sj', $params['no_sj'])->first();

            $m_terima_pakan = new \Model\Storage\TerimaPakan_model();
            $now = $m_terima_pakan->getDate();

            $m_terima_pakan->id_kirim_pakan = $d_kirim_pakan->id;
            $m_terima_pakan->tgl_trans = $now['waktu'];
            $m_terima_pakan->tgl_terima = $params['tiba'];
            $m_terima_pakan->save();

            $id_terima = $m_terima_pakan->id;

            foreach ($params['data_brg'] as $k_detail => $v_detail) {
                $m_terima_pakan_detail = new \Model\Storage\TerimaPakanDetail_model();
                $m_terima_pakan_detail->id_header = $id_terima;
                $m_terima_pakan_detail->item = $v_detail['kode_brg'];
                $m_terima_pakan_detail->jumlah = $v_detail['jumlah'];
                $m_terima_pakan_detail->kondisi = !empty($v_detail['kondisi']) ? strtoupper($v_detail['kondisi']) : null;
                $m_terima_pakan_detail->save();
            }

            $d_terima_pakan = $m_terima_pakan->where('id', $id_terima)->first();

            $deskripsi_log_terima_pakan = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_terima_pakan, $deskripsi_log_terima_pakan);

            $this->result['status'] = 1;
            // $this->result['content'] = array('id_terima' => $id_terima);
            $this->result['content'] = array(
                'id' => $id_terima,
                'tanggal' => $params['tiba'],
                'delete' => 0,
                'message' => 'Data Penerimaan Pakan berhasil di simpan.',
                'status_jurnal' => 1
            );
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function hitungStokByTransaksi()
    {
        $params = $this->input->post('params');

        $id = $params['id'];
        $tanggal = $params['tanggal'];
        $delete = $params['delete'];
        $message = $params['message'];
        $status_jurnal = $params['status_jurnal'];

        try {
            $this->insertKonfirmasi( $id, $delete );

            $conf = new \Model\Storage\Conf();
            $sql = "EXEC hitung_stok_pakan_by_transaksi 'terima_pakan', '".$id."', '".$tanggal."', ".$delete.", ".$status_jurnal."";

            $d_conf = $conf->hydrateRaw($sql);

            $this->result['status'] = 1;
            $this->result['message'] = $message;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    function insertKonfirmasi($id_terima, $delete = 0) {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select kp.* from terima_pakan tp
            left join
                kirim_pakan kp
                on
                    tp.id_kirim_pakan = kp.id
            where
                tp.id = '".$id_terima."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );
        
        $no_order = null;
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray()[0];

            $no_order = $d_conf['no_order'];
        }

        $m_kppd = new \Model\Storage\KonfirmasiPembayaranPakanDet_model();
        $d_kppd = $m_kppd->where('no_order', $no_order)->first();

        if ( $d_kppd ) {
            $m_kppd2 = new \Model\Storage\KonfirmasiPembayaranPakanDet2_model();
            $m_kppd2->where('id_header', $d_kppd->id)->delete();

            $m_kppd = new \Model\Storage\KonfirmasiPembayaranPakanDet_model();
            $m_kppd->where('id', $d_kppd->id)->delete();

            $m_kpp = new \Model\Storage\KonfirmasiPembayaranPakan_model();
            $m_kpp->where('id', $d_kppd->id_header)->delete();
        }

        if ( $delete == 0 ) {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    tp.tgl_terima as tgl_bayar,
                    tp.tgl_terima as periode_docin,
                    op.perusahaan,
                    op.supplier,
                    sum(dtp.jumlah * op.harga) as total,
                    kp.no_sj,
                    kp.tgl_kirim as tgl_sj,
                    SUBSTRING(op.no_order, 5, 3) as id_kab_kota,
                    op.no_order,
                    sum(dtp.jumlah) as jumlah
                from det_terima_pakan dtp
                left join
                    (
                        select tp1.* from terima_pakan tp1
                        right join
                            (select max(id) as id, id_kirim_pakan from terima_pakan group by id_kirim_pakan) tp2
                            on
                                tp1.id = tp2.id
                    ) tp
                    on
                        dtp.id_header = tp.id
                left join
                    kirim_pakan kp
                    on
                        tp.id_kirim_pakan = kp.id
                left join
                    (
                        select 
                            opd.*, 
                            op.no_order, 
                            op.tgl_trans, 
                            op.rcn_kirim, 
                            op.supplier 
                        from order_pakan_detail opd
                        left join
                            (
                                select op1.* from order_pakan op1
                                right join
                                    (select max(id) as id, no_order from order_pakan group by no_order) op2
                                    on
                                        op1.id = op2.id
                            ) op
                            on
                                opd.id_header = op.id
                    ) op
                    on
                        kp.no_order = op.no_order and
                        dtp.item = op.barang
                where
                    kp.jenis_kirim = 'opks' and
                    op.no_order = '".$no_order."'
                group by
                    tp.tgl_terima,
                    op.perusahaan,
                    op.supplier,
                    kp.no_sj,
                    kp.tgl_kirim,
                    op.no_order
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray()[0];

                $m_kpp = new \Model\Storage\KonfirmasiPembayaranPakan_model();
                $nomor = $m_kpp->getNextNomor();

                $m_kpp->nomor = $nomor;
                $m_kpp->tgl_bayar = $d_conf['tgl_bayar'];
                $m_kpp->periode = trim($d_conf['periode_docin']);
                $m_kpp->perusahaan = $d_conf['perusahaan'];
                $m_kpp->supplier = $d_conf['supplier'];
                $m_kpp->total = $d_conf['total'];
                $m_kpp->invoice = $d_conf['no_sj'];
                // $m_kpp->rekening = $d_conf['rekening'];
                $m_kpp->save();

                $id = $m_kpp->id;

                $m_kppd = new \Model\Storage\KonfirmasiPembayaranPakanDet_model();
                $m_kppd->id_header = $id;
                $m_kppd->tgl_sj = $d_conf['tgl_sj'];
                $m_kppd->kode_unit = $d_conf['id_kab_kota'];
                $m_kppd->no_order = $d_conf['no_order'];
                $m_kppd->no_sj = $d_conf['no_sj'];
                $m_kppd->jumlah = $d_conf['jumlah'];
                $m_kppd->total = $d_conf['total'];
                $m_kppd->save();

                // $id_det = $m_kppd->id;
                // foreach ($v_det['detail'] as $k_det2 => $v_det2) {
                //     $m_kppd2 = new \Model\Storage\KonfirmasiPembayaranPakanDet2_model();
                //     $m_kppd2->id_header = $id_det;
                //     $m_kppd2->id_gudang = $v_det2['id_gudang'];
                //     $m_kppd2->kode_brg = $v_det2['kode_brg'];
                //     $m_kppd2->jumlah = $v_det2['jumlah'];
                //     $m_kppd2->harga = $v_det2['harga'];
                //     $m_kppd2->total = $v_det2['total'];
                //     $m_kppd2->save();
                // }
                
                $d_kpd = $m_kpp->where('id', $id)->first();

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $d_kpd, $deskripsi_log);
            }
        }
    }

    public function hitungStokAwal()
    {
        $params = $this->input->post('params');

        try {
            $id_terima = $params['id_terima'];

            $date = date('Y-m-d');
            
            $m_stok = new \Model\Storage\Stok_model();
            $now = $m_stok->getDate();
            $d_stok = $m_stok->where('periode', $date)->first();

            $stok_id = null;
            if ( $d_stok ) {
                $stok_id = $d_stok->id;

                $this->hitungStok($id_terima, $stok_id);
            } else {
                $m_stok->periode = $date;
                $m_stok->user_proses = $this->userid;
                $m_stok->tgl_proses = $now['waktu'];
                $m_stok->save();

                $stok_id = $m_stok->id;

                $conf = new \Model\Storage\Conf();
                $sql = "EXEC get_data_stok_pakan_by_tanggal @date = '$date'";

                $d_conf = $conf->hydrateRaw($sql);

                if ( $d_conf->count() > 0 ) {
                    $d_conf = $d_conf->toArray();
                    $jml_data = count($d_conf);
                    $idx = 0;
                    foreach ($d_conf as $k_conf => $v_conf) {
                        $m_ds = new \Model\Storage\DetStok_model();
                        $m_ds->id_header = $stok_id;
                        $m_ds->tgl_trans = $v_conf['tgl_trans'];
                        $m_ds->kode_gudang = $v_conf['kode_gudang'];
                        $m_ds->kode_barang = $v_conf['kode_barang'];
                        $m_ds->jumlah = $v_conf['jumlah'];
                        $m_ds->hrg_jual = $v_conf['hrg_jual'];
                        $m_ds->hrg_beli = $v_conf['hrg_beli'];
                        $m_ds->kode_trans = $v_conf['kode_trans'];
                        $m_ds->jenis_barang = $v_conf['jenis_barang'];
                        $m_ds->jenis_trans = $v_conf['jenis_trans'];
                        $m_ds->jml_stok = $v_conf['jml_stok'];
                        $m_ds->save();

                        $idx++;

                        if ( $jml_data == $idx ) {
                            $this->hitungStok($id_terima, $stok_id);
                        }
                    }
                }
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Penerimaan Pakan berhasil di simpan.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function hitungStok($id_terima, $stok_id)
    {
        $m_terima_pakan = new \Model\Storage\TerimaPakan_model();
        $d_terima_pakan = $m_terima_pakan->where('id', $id_terima)->with(['detail'])->first()->toArray();

        $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
        $d_kirim_pakan = $m_kirim_pakan->where('id', $d_terima_pakan['id_kirim_pakan'])->first()->toArray();

        $total = 0;

        foreach ($d_terima_pakan['detail'] as $k_detail => $v_detail) {
            if ( stristr($d_kirim_pakan['jenis_tujuan'], 'gudang') !== FALSE ) {
                if ( stristr($d_kirim_pakan['jenis_kirim'], 'opkg') === false ) {
                    // MASUK STOK GUDANG
                    $m_order_pakan = new \Model\Storage\OrderPakan_model();
                    $d_order_pakan = $m_order_pakan->where('no_order', $d_kirim_pakan['no_order'])->first();

                    $harga_jual = 0;
                    $harga_beli = 0;
                    if ( !empty($d_order_pakan) ) {
                        $m_dorder_pakan = new \Model\Storage\OrderPakanDetail_model();
                        $d_dorder_pakan = $m_dorder_pakan->where('id_header', $d_order_pakan->id)->where('barang', trim($v_detail['item']))->first();

                        $harga_jual = $d_dorder_pakan->harga_jual;
                        $harga_beli = $d_dorder_pakan->harga;
                    }

                    // MASUk STOK GUDANG
                    $m_dstok = new \Model\Storage\DetStok_model();
                    $m_dstok->id_header = $stok_id;
                    $m_dstok->tgl_trans = $d_terima_pakan['tgl_terima'];
                    $m_dstok->kode_gudang = $d_kirim_pakan['tujuan'];
                    $m_dstok->kode_barang = $v_detail['item'];
                    $m_dstok->jumlah = $v_detail['jumlah'];
                    $m_dstok->hrg_jual = $harga_jual;
                    $m_dstok->hrg_beli = $harga_beli;
                    $m_dstok->kode_trans = $d_kirim_pakan['no_order'];
                    $m_dstok->jenis_barang = 'pakan';
                    $m_dstok->jenis_trans = 'ORDER';
                    $m_dstok->jml_stok = $v_detail['jumlah'];
                    $m_dstok->save();

                    $total += $harga_beli * $v_detail['jumlah'];
                } else {
                    // KELUAR STOK GUDANG
                    $nilai_beli = 0;
                    $nilai_jual = 0;
                    $jml_keluar = $v_detail['jumlah'];
                    while ($jml_keluar > 0) {
                        $m_dstok = new \Model\Storage\DetStok_model();
                        $sql = "
                            select top 1 * from det_stok ds 
                            where
                                ds.id_header = ".$stok_id." and 
                                ds.kode_gudang = ".$d_kirim_pakan['asal']." and 
                                ds.kode_barang = '".$v_detail['item']."' and 
                                ds.jml_stok > 0
                            order by
                                ds.jenis_trans desc,
                                ds.tgl_trans asc,
                                ds.kode_trans asc,
                                ds.id asc
                        ";

                        $d_dstok = $m_dstok->hydrateRaw($sql);

                        if ( $d_dstok->count() > 0 ) {
                            $d_dstok = $d_dstok->toArray()[0];

                            $harga_jual = $d_dstok['hrg_jual'];
                            $harga_beli = $d_dstok['hrg_beli'];

                            $jml_stok = $d_dstok['jml_stok'];
                            if ( $jml_stok > $jml_keluar ) {
                                $jml_stok = $jml_stok - $jml_keluar;
                                $nilai_beli += $jml_keluar*$harga_beli;
                                $nilai_jual += $jml_keluar*$harga_jual;

                                $m_dstokt = new \Model\Storage\DetStokTrans_model();
                                $m_dstokt->id_header = $d_dstok['id'];
                                $m_dstokt->kode_trans = $d_kirim_pakan['no_order'];
                                $m_dstokt->jumlah = $jml_keluar;
                                $m_dstokt->kode_barang = $v_detail['item'];
                                $m_dstokt->save();

                                // MASUK STOK GUDANG
                                $m_dstok = new \Model\Storage\DetStok_model();
                                $m_dstok->id_header = $stok_id;
                                $m_dstok->tgl_trans = $d_terima_pakan['tgl_terima'];
                                $m_dstok->kode_gudang = $d_kirim_pakan['tujuan'];
                                $m_dstok->kode_barang = $v_detail['item'];
                                $m_dstok->jumlah = $jml_keluar;
                                $m_dstok->hrg_jual = $harga_jual;
                                $m_dstok->hrg_beli = $harga_beli;
                                $m_dstok->kode_trans = $d_kirim_pakan['no_order'];
                                $m_dstok->jenis_barang = 'pakan';
                                $m_dstok->jenis_trans = 'ORDER';
                                $m_dstok->jml_stok = $jml_keluar;
                                $m_dstok->save();

                                $total += $harga_beli * $jml_keluar;

                                $jml_keluar = 0;
                            } else {
                                $jml_keluar = $jml_keluar - $d_dstok['jml_stok'];
                                $nilai_beli += $d_dstok['jml_stok']*$harga_beli;
                                $nilai_jual += $d_dstok['jml_stok']*$harga_jual;

                                $m_dstokt = new \Model\Storage\DetStokTrans_model();
                                $m_dstokt->id_header = $d_dstok['id'];
                                $m_dstokt->kode_trans = $d_kirim_pakan['no_order'];
                                $m_dstokt->jumlah = $d_dstok['jml_stok'];
                                $m_dstokt->kode_barang = $v_detail['item'];
                                $m_dstokt->save();

                                // MASUK STOK GUDANG
                                $m_dstok = new \Model\Storage\DetStok_model();
                                $m_dstok->id_header = $stok_id;
                                $m_dstok->tgl_trans = $d_terima_pakan['tgl_terima'];
                                $m_dstok->kode_gudang = $d_kirim_pakan['tujuan'];
                                $m_dstok->kode_barang = $v_detail['item'];
                                $m_dstok->jumlah = $d_dstok['jml_stok'];
                                $m_dstok->hrg_jual = $harga_jual;
                                $m_dstok->hrg_beli = $harga_beli;
                                $m_dstok->kode_trans = $d_kirim_pakan['no_order'];
                                $m_dstok->jenis_barang = 'pakan';
                                $m_dstok->jenis_trans = 'ORDER';
                                $m_dstok->jml_stok = $d_dstok['jml_stok'];
                                $m_dstok->save();

                                $total += $harga_beli * $d_dstok['jml_stok'];

                                $jml_stok = 0;
                            }
                            $m_dstok->where('id', $d_dstok['id'])->update(
                                array(
                                    'jml_stok' => $jml_stok
                                )
                            );
                        } else {
                            $jml_keluar = 0;
                        }
                    }
                }
            } else {
                if ( stristr($d_kirim_pakan['jenis_kirim'], 'opkg') !== FALSE && stristr($d_kirim_pakan['jenis_tujuan'], 'gudang') === FALSE ) {
                    // KELUAR STOK GUDANG
                    $nilai_beli = 0;
                    $nilai_jual = 0;
                    $jml_keluar = $v_detail['jumlah'];
                    while ($jml_keluar > 0) {
                        $m_dstok = new \Model\Storage\DetStok_model();
                        $sql = "
                            select top 1 * from det_stok ds 
                            where
                                ds.id_header = ".$stok_id." and 
                                ds.kode_gudang = ".$d_kirim_pakan['asal']." and 
                                ds.kode_barang = '".$v_detail['item']."' and 
                                ds.jml_stok > 0
                            order by
                                ds.jenis_trans desc,
                                ds.tgl_trans asc,
                                ds.kode_trans asc,
                                ds.id asc
                        ";

                        $d_dstok = $m_dstok->hydrateRaw($sql);

                        if ( $d_dstok->count() > 0 ) {
                            $d_dstok = $d_dstok->toArray()[0];

                            $harga_jual = $d_dstok['hrg_jual'];
                            $harga_beli = $d_dstok['hrg_beli'];

                            $jml_stok = $d_dstok['jml_stok'];
                            if ( $jml_stok > $jml_keluar ) {
                                $jml_stok = $jml_stok - $jml_keluar;
                                $nilai_beli += $jml_keluar*$harga_beli;
                                $nilai_jual += $jml_keluar*$harga_jual;

                                $m_dstokt = new \Model\Storage\DetStokTrans_model();
                                $m_dstokt->id_header = $d_dstok['id'];
                                $m_dstokt->kode_trans = $d_kirim_pakan['no_order'];
                                $m_dstokt->jumlah = $jml_keluar;
                                $m_dstokt->kode_barang = $v_detail['item'];
                                $m_dstokt->save();

                                $total += $harga_beli * $jml_keluar;

                                $jml_keluar = 0;
                            } else {
                                $jml_keluar = $jml_keluar - $d_dstok['jml_stok'];
                                $nilai_beli += $d_dstok['jml_stok']*$harga_beli;
                                $nilai_jual += $d_dstok['jml_stok']*$harga_jual;

                                $m_dstokt = new \Model\Storage\DetStokTrans_model();
                                $m_dstokt->id_header = $d_dstok['id'];
                                $m_dstokt->kode_trans = $d_kirim_pakan['no_order'];
                                $m_dstokt->jumlah = $d_dstok['jml_stok'];
                                $m_dstokt->kode_barang = $v_detail['item'];
                                $m_dstokt->save();

                                $total += $harga_beli * $d_dstok['jml_stok'];

                                $jml_stok = 0;
                            }
                            $m_dstok->where('id', $d_dstok['id'])->update(
                                array(
                                    'jml_stok' => $jml_stok
                                )
                            );
                        } else {
                            $jml_keluar = 0;
                        }
                    }
                }
            }
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "exec insert_jurnal 'PAKAN', '".$d_kirim_pakan['no_order']."', NULL, ".$total.", 'terima_pakan', ".$id_terima.", NULL, 1";

        $d_conf = $m_conf->hydrateRaw( $sql );
    }

    // public function edit()
    // {
    //     $params = $this->input->post('params');

    //     try {
    //         $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
    //         $d_kirim_pakan_old = $m_kirim_pakan->where('no_sj', $params['no_sj_old'])->first();
    //         $d_kirim_pakan_new = $m_kirim_pakan->where('no_sj', $params['no_sj'])->first();

    //         $m_terima_pakan = new \Model\Storage\TerimaPakan_model();
    //         $now = $m_terima_pakan->getDate();

    //         $d_terima_pakan = $m_terima_pakan->where('id_kirim_pakan', $d_kirim_pakan_old->id)->first();
    //         $m_terima_pakan->where('id_kirim_pakan', $d_kirim_pakan_old->id)->update(
    //             array(
    //                 'id_kirim_pakan' => $d_kirim_pakan_new->id,
    //                 'tgl_trans' => $now['waktu'],
    //                 'tgl_terima' => $params['tiba']
    //             )
    //         );

    //         $id_header = $d_terima_pakan->id;

    //         $m_terima_pakan_detail = new \Model\Storage\TerimaPakanDetail_model();
    //         $m_terima_pakan_detail->where('id_header', $id_header)->delete();
    //         foreach ($params['data_brg'] as $k_detail => $v_detail) {
    //             $m_terima_pakan_detail = new \Model\Storage\TerimaPakanDetail_model();
    //             $m_terima_pakan_detail->id_header = $id_header;
    //             $m_terima_pakan_detail->item = $v_detail['kode_brg'];
    //             $m_terima_pakan_detail->jumlah = $v_detail['jumlah'];
    //             $m_terima_pakan_detail->kondisi = !empty($v_detail['kondisi']) ? strtoupper($v_detail['kondisi']) : null;
    //             $m_terima_pakan_detail->save();
    //         }

    //         $d_terima_pakan = $m_terima_pakan->where('id', $id_header)->first();

    //         $deskripsi_log_terima_pakan = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
    //         Modules::run( 'base/event/update', $d_terima_pakan, $deskripsi_log_terima_pakan);

    //         $this->result['status'] = 1;
    //         $this->result['message'] = 'Data Penerimaan Pakan berhasil di-update.';
    //     } catch (\Illuminate\Database\QueryException $e) {
    //         $this->result['message'] = "Gagal : " . $e->getMessage();
    //     }

    //     display_json($this->result);
    // }

    // public function delete()
    // {
    //     $params = $this->input->post('params');

    //     try {
    //         $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
    //         $d_kirim_pakan = $m_kirim_pakan->where('no_sj', $params['no_sj'])->first();

    //         $m_terima_pakan = new \Model\Storage\TerimaPakan_model();
    //         $d_terima_pakan = $m_terima_pakan->where('id_kirim_pakan', $d_kirim_pakan->id)->first();

    //         $m_terima_pakan->where('id', $d_terima_pakan->id)->delete();
    //         $m_dterima_pakan = new \Model\Storage\TerimaPakanDetail_model();
    //         $m_dterima_pakan->where('id_header', $d_terima_pakan->id)->delete();

    //         $deskripsi_log = 'hapus data penerimaan pakan noreg '+$d_kirim_pakan->tujuan+' oleh ' . $this->userdata['detail_user']['nama_detuser'];
    //         Modules::run( 'base/event/delete', $d_terima_pakan, $deskripsi_log);

    //         $this->result['status'] = 1;
    //         $this->result['message'] = 'Data berhasil di hapus';           
    //     } catch (\Illuminate\Database\QueryException $e) {
    //         $this->result['message'] = "Gagal : " . $e->getMessage();
    //     }

    //     display_json($this->result);
    // }

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
}