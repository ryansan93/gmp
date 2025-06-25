<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KonfirmasiPanenMobile extends Public_Controller {

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
                "assets/transaksi/konfirmasi_panen_mobile/js/konfirmasi-panen-mobile.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/konfirmasi_panen_mobile/css/konfirmasi-panen-mobile.css",
            ));

            $data = $this->includes;

            $isMobile = false;
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
            $data['title_menu'] = 'Konfirmasi Panen Mobile';
            $data['view'] = $this->load->view('transaksi/konfirmasi_panen_mobile/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function riwayat($mitra)
    {
        $content['data_mitra'] = $mitra;
        $html = $this->load->view('transaksi/konfirmasi_panen_mobile/riwayat', $content, TRUE);

        return $html;
    }

    public function list_riwayat()
    {
        $params = $this->input->post('params');

        $m_konfir = new \Model\Storage\Konfir_model();
        $d_konfir = $m_konfir->where('noreg', $params['noreg'])->orderBy('tgl_panen', 'desc')->get();

        $data = array();
        if ( $d_konfir->count() > 0 ) {
            $d_konfir = $d_konfir->toArray();
            foreach ($d_konfir as $k_konfir => $v_konfir) {
                $data[ $v_konfir['tgl_panen'] ] = array(
                    'id' => $v_konfir['id'],
                    'tgl_panen' => $v_konfir['tgl_panen'],
                    'umur' => selisihTanggal($v_konfir['tgl_docin'], $v_konfir['tgl_panen']),
                    'total' => $v_konfir['total'],
                    'bb_rata2' => $v_konfir['bb_rata2'],
                );

                krsort($data);
            }
        }

        $content['data'] = $data;

        $html = $this->load->view('transaksi/konfirmasi_panen_mobile/list_riwayat', $content, TRUE);

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
        $html = $this->load->view('transaksi/konfirmasi_panen_mobile/add_form', $content, true);

        return $html;
    }

    public function detail_form($id)
    {
        $m_konfir = new \Model\Storage\Konfir_model();
        $d_konfir = $m_konfir->where('id', $id)->with(['det_konfir'])->first()->toArray();

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('noreg', $d_konfir['noreg'])->with(['mitra'])->first();

        $m_drpah = new \Model\Storage\DetRpah_model();
        $d_drpah = $m_drpah->where('id_konfir', $id)->first();

        $edit = 1;
        if ( $d_drpah ) {
            $edit = 0;
        }

        $data = array(
            'id' => $id,
            'mitra' => $d_rs->mitra->dMitra->nama,
            'noreg' => $d_konfir['noreg'],
            'tgl_panen' => $d_konfir['tgl_panen'],
            'umur' => selisihTanggal($d_konfir['tgl_docin'], $d_konfir['tgl_panen']),
            'populasi' => $d_konfir['populasi'],
            'bb' => $d_konfir['bb_rata2'],
            'total' => $d_konfir['total'],
            'detail' => $d_konfir['det_konfir'],
            'edit' => $edit
        );

        $content['data'] = $data;
        $html = $this->load->view('transaksi/konfirmasi_panen_mobile/detail_form', $content, true);

        return $html;
    }

    public function edit_form($id, $mitra)
    {
        $m_konfir = new \Model\Storage\Konfir_model();
        $d_konfir = $m_konfir->where('id', $id)->with(['det_konfir'])->first()->toArray();

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('noreg', $d_konfir['noreg'])->with(['mitra'])->first();

        $m_drpah = new \Model\Storage\DetRpah_model();
        $d_drpah = $m_drpah->where('id_konfir', $id)->first();

        $edit = 1;
        if ( $d_drpah ) {
            $edit = 0;
        }

        $data = array(
            'id' => $id,
            'mitra' => $d_rs->mitra->dMitra->nama,
            'nomor' => $d_rs->mitra->dMitra->nomor,
            'noreg' => $d_konfir['noreg'],
            'tgl_panen' => $d_konfir['tgl_panen'],
            'umur' => selisihTanggal($d_konfir['tgl_docin'], $d_konfir['tgl_panen']),
            'populasi' => $d_konfir['populasi'],
            'bb' => $d_konfir['bb_rata2'],
            'total' => $d_konfir['total'],
            'detail' => $d_konfir['det_konfir'],
            'edit' => $edit
        );

        $content['data_mitra'] = $mitra;
        $content['data'] = $data;
        $html = $this->load->view('transaksi/konfirmasi_panen_mobile/edit_form', $content, true);

        return $html;
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
            $d_ukaryawan = $m_ukaryawan->where('id_karyawan', $d_karyawan->id)->get()->toArray();

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

                $populasi = $v_rs['populasi'];

                if ( empty($d_ts) ) {
                    $m_od = new \Model\Storage\OrderDoc_model();
                    $d_od = $m_od->where('noreg', $v_rs['noreg'])->first();

                    $tgl_docin = substr($v_rs['tgl_docin'], 0, 10);
                    if ( !empty($d_od) ) {
                        $m_td = new \Model\Storage\TerimaDoc_model();
                        $d_td = $m_td->where('no_order', $d_od->no_order)->orderBy('id', 'desc')->first();

                        if ( !empty($d_td) ) {
                            $tgl_docin = substr($d_td->datang, 0, 10);
                            $populasi = $d_td['jml_ekor'];
                        }
                    }

                    $kandang = (int) substr($v_rs['noreg'], -1);

                    $key = str_replace('-', '', $tgl_docin).' - '.substr($v_rs['noreg'], -1);
                    $_data[ $key ] = array(
                        'noreg' => $v_rs['noreg'],
                        'populasi' => $populasi,
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

    public function get_unit()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'UN')->orderBy('nama', 'asc')->get()->toArray();

        return $d_wilayah;
    }

    public function get_lists()
    {
        $params = $this->input->get('params');

        $unit = $params['unit'];
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('id', $unit)->first();

        $data = null;

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->whereBetween('tgl_docin', [$start_date, $end_date])->with(['dKandang', 'data_konfir'])->orderBy('tgl_docin', 'asc')->get();

        if ( $d_rs->count() > 0 ) {
            $d_rs = $d_rs->toArray();
            foreach ($d_rs as $k => $v_rs) {
                $kdg = $v_rs['d_kandang'];
                if ( $d_wilayah['kode'] == $kdg['d_unit']['kode'] ) {
                    $m_mm = new \Model\Storage\MitraMapping_model();
                    $d_mm = $m_mm->where('nim', $v_rs['nim'])->orderBy('id', 'desc')->first();

                    $nama_mitra = null;
                    if ( $d_mm ) {
                        $m_mitra = new \Model\Storage\Mitra_model();
                        $d_mitra = $m_mitra->where('id', $d_mm->mitra)->orderBy('id', 'desc')->first();

                        $nama_mitra = $d_mitra->nama;
                    }

                    // $data[$v_rs['id']] = $v_rs;
                    $data[$v_rs['id']] = array(
                        'id' => !empty($v_rs['data_konfir']) ? $v_rs['data_konfir']['id'] : null,
                        'tgl_panen' => !empty($v_rs['data_konfir']) ? $v_rs['data_konfir']['tgl_panen'] : null,
                        'total' => !empty($v_rs['data_konfir']) ? $v_rs['data_konfir']['total'] : null,
                        'bb_rata2' => !empty($v_rs['data_konfir']) ? $v_rs['data_konfir']['bb_rata2'] : null,
                        'tgl_docin' => tglIndonesia($v_rs['tgl_docin'], '-', ' '),
                        'noreg' => $v_rs['noreg'],
                        'nama' => $nama_mitra,
                        'populasi' => angkaRibuan($v_rs['populasi']),
                        'kandang' => $v_rs['d_kandang']['kandang'],
                        'unit' => $d_wilayah['kode'],
                    );
                }
            }
        }

        $content['data'] = $data;
        $html = $this->load->view('transaksi/konfirmasi_panen_mobile/list', $content, true);
            
        echo $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_konfir = new \Model\Storage\Konfir_model();
            $m_konfir->noreg = $params['noreg'];
            $m_konfir->tgl_docin = $params['tgl_docin'];
            $m_konfir->tgl_panen = $params['tgl_panen'];
            $m_konfir->populasi = $params['populasi'];
            $m_konfir->bb_rata2 = $params['bb_rata2'];
            $m_konfir->total = $params['tot_sekat'];
            $m_konfir->save();

            $id_konfir = $m_konfir->id;

            foreach ($params['data_sekat'] as $k_ds => $v_ds) {
                $m_dkonfir = new \Model\Storage\DetKonfir_model();
                $m_dkonfir->id_konfir = $id_konfir;
                $m_dkonfir->jumlah = $v_ds['jumlah'];
                $m_dkonfir->bb = $v_ds['bb'];
                $m_dkonfir->save();
            }

            $m_konfir = new \Model\Storage\Konfir_model();
            $d_konfir  = $m_konfir->where('id', $id_konfir)->with(['det_konfir'])->first();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_konfir, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil disimpan';
            $this->result['content'] = array('id' => $id_konfir);            
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit()
    {
        $params = $this->input->post('params');

        try {
            $m_konfir = new \Model\Storage\Konfir_model();

            $id_konfir = $params['id'];

            $m_konfir->where('id', $id_konfir)->update(
                array(
                    'noreg' => $params['noreg'],
                    'tgl_docin' => $params['tgl_docin'],
                    'tgl_panen' => $params['tgl_panen'],
                    'populasi' => $params['populasi'],
                    'bb_rata2' => $params['bb_rata2'],
                    'total' => $params['tot_sekat']
                )
            );

            $m_dkonfir = new \Model\Storage\DetKonfir_model();
            $m_dkonfir->where('id_konfir', $id_konfir)->delete();

            foreach ($params['data_sekat'] as $k_ds => $v_ds) {
                $m_dkonfir = new \Model\Storage\DetKonfir_model();
                $m_dkonfir->id_konfir = $id_konfir;
                $m_dkonfir->jumlah = $v_ds['jumlah'];
                $m_dkonfir->bb = $v_ds['bb'];
                $m_dkonfir->save();
            }

            $d_konfir  = $m_konfir->where('id', $id_konfir)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_konfir, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di update';
            $this->result['content'] = array('id' => $id_konfir);            
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete()
    {
        $id = $this->input->post('params');

        try {
            $id_konfir = $id;

            $m_konfir = new \Model\Storage\Konfir_model();
            $d_konfir = $m_konfir->where('id', $id_konfir)->first();

            $deskripsi_log = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_konfir, $deskripsi_log);

            $m_dkonfir = new \Model\Storage\DetKonfir_model();
            $m_dkonfir->where('id_konfir', $id_konfir)->delete();

            $m_konfir->where('id', $id_konfir)->delete();

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus';       
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }
}