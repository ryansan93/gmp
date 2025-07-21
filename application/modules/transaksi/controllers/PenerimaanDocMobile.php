<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PenerimaanDocMobile extends Public_Controller {

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
                "assets/transaksi/penerimaan_doc_mobile/js/penerimaan-doc-mobile.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/penerimaan_doc_mobile/css/penerimaan-doc-mobile.css",
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
            $data['title_menu'] = 'Penerimaan DOC';
            $data['view'] = $this->load->view('transaksi/penerimaan_doc_mobile/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function riwayat($mitra)
    {
        $content['data_mitra'] = $mitra;
        $html = $this->load->view('transaksi/penerimaan_doc_mobile/riwayat', $content, TRUE);

        return $html;
    }

    public function list_riwayat()
    {
        $params = $this->input->post('params');

        $m_order_doc = new \Model\Storage\OrderDoc_model();
        $d_order_doc = $m_order_doc->where('noreg', $params['noreg'])->get();

        $data = array();
        if ( $d_order_doc->count() > 0 ) {
            $d_order_doc = $d_order_doc->toArray();
            foreach ($d_order_doc as $k_order_doc => $v_order_doc) {
                $m_terima_doc = new \Model\Storage\TerimaDoc_model();
                $d_terima_doc = $m_terima_doc->where('no_order', $v_order_doc['no_order'])->orderBy('id', 'desc')->first();

                if ( !empty($d_terima_doc) ) {
                    $data[ $v_order_doc['no_order'] ] = array(
                        'no_order' => $v_order_doc['no_order'],
                        'tiba' => $d_terima_doc->datang,
                        'ekor' => $d_terima_doc->jml_ekor,
                        'bb' => $d_terima_doc->bb,
                    );

                    krsort($data);
                }
            }
        }

        $content['data'] = $data;

        $html = $this->load->view('transaksi/penerimaan_doc_mobile/list_riwayat', $content, TRUE);

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
        $html = $this->load->view('transaksi/penerimaan_doc_mobile/add_form', $content, true);

        return $html;
    }

    public function detail_form($no_order)
    {
        $m_terima_doc = new \Model\Storage\TerimaDoc_model();
        $d_terima_doc = $m_terima_doc->where('no_order', $no_order)->orderBy('id', 'desc')->first();

        $m_terima_doc_ket = new \Model\Storage\TerimaDocKet_model();
        $d_terima_doc_ket = $m_terima_doc_ket->where('id_header', $d_terima_doc->id)->get();

        $m_order_doc = new \Model\Storage\OrderDoc_model();
        $d_order_doc = $m_order_doc->where('no_order', $no_order)->first();

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('noreg', $d_order_doc->noreg)->with(['mitra'])->first();

        $data = array(
            'no_order' => $no_order,
            'mitra' => $d_rs->mitra->dMitra->nama,
            'nomor' => $d_rs->mitra->dMitra->nomor,
            'noreg' => $d_order_doc->noreg,
            'no_sj' => $d_terima_doc->no_sj,
            'lampiran_sj' => $d_terima_doc->path,
            'kirim' => $d_terima_doc->kirim,
            'tiba' => $d_terima_doc->datang,
            'nopol' => $d_terima_doc->nopol,
            'kondisi' => $d_terima_doc->kondisi,
            'ekor' => $d_terima_doc->jml_ekor,
            'box' => $d_terima_doc->jml_box,
            'bb' => $d_terima_doc->bb,
            'data_ket' => ($d_terima_doc_ket->count() > 0) ? $d_terima_doc_ket->toArray() : null,
            'uniformity' => $d_terima_doc->uniformity
        );

        $content['data'] = $data;
        $html = $this->load->view('transaksi/penerimaan_doc_mobile/detail_form', $content, true);

        return $html;
    }

    public function edit_form($no_order, $mitra)
    {
        $m_terima_doc = new \Model\Storage\TerimaDoc_model();
        $d_terima_doc = $m_terima_doc->where('no_order', $no_order)->first();

        $m_terima_doc_ket = new \Model\Storage\TerimaDocKet_model();
        $d_terima_doc_ket = $m_terima_doc_ket->where('id_header', $d_terima_doc->id)->get();

        $m_order_doc = new \Model\Storage\OrderDoc_model();
        $d_order_doc = $m_order_doc->where('no_order', $no_order)->first();

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('noreg', $d_order_doc->noreg)->with(['mitra'])->first();

        $data = array(
            'no_order' => $no_order,
            'mitra' => $d_rs->mitra->dMitra->nama,
            'nomor' => $d_rs->mitra->dMitra->nomor,
            'noreg' => $d_order_doc->noreg,
            'no_sj' => $d_terima_doc->no_sj,
            'lampiran_sj' => $d_terima_doc->path,
            'kirim' => $d_terima_doc->kirim,
            'tiba' => $d_terima_doc->datang,
            'nopol' => $d_terima_doc->nopol,
            'kondisi' => $d_terima_doc->kondisi,
            'ekor' => $d_terima_doc->jml_ekor,
            'box' => $d_terima_doc->jml_box,
            'bb' => $d_terima_doc->bb,
            'data_ket' => ($d_terima_doc_ket->count() > 0) ? $d_terima_doc_ket->toArray() : null,
            'uniformity' => $d_terima_doc->uniformity
        );

        $content['data'] = $data;
        $content['data_mitra'] = $mitra;
        $html = $this->load->view('transaksi/penerimaan_doc_mobile/edit_form', $content, true);

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
        $jenis = $this->input->post('jenis');

        $end_date = next_date(date('Y-m-d'), 7).' 23:59:59.999';
        $start_date = prev_date(date('Y-m-d'), 60).' 00:00:00.000';

        $m_mm = new \Model\Storage\MitraMapping_model();
        $d_mm = $m_mm->select('nim')->where('nomor', $nomor_mitra)->get()->toArray();

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->whereIn('nim', $d_mm)->whereBetween('tgl_docin', [$start_date, $end_date])->get();

        $_data = array();
        if ( $d_rs->count() > 0 ) {
            $d_rs = $d_rs->toArray();
            foreach ($d_rs as $k_rs => $v_rs) {
                $tampil = 1;
                
                $m_ts = new \Model\Storage\TutupSiklus_model();
                $d_ts = $m_ts->where('noreg', $v_rs['noreg'])->first();

                if ( empty($d_ts) ) {
                    $m_od = new \Model\Storage\OrderDoc_model();
                    $d_od = $m_od->where('noreg', $v_rs['noreg'])->first();

                    $tgl_docin = substr($v_rs['tgl_docin'], 0, 10);
                    if ( !empty($d_od) ) {
                        $m_td = new \Model\Storage\TerimaDoc_model();
                        $d_td = $m_td->where('no_order', $d_od->no_order)->orderBy('id', 'desc')->first();

                        if ( $d_td ) {
                            $tgl_docin = substr($d_td->datang, 0, 10);
                            if ( $jenis == 'riwayat' ) {
                                $tampil = 1;
                            } else {
                                $tampil = 0;
                            }
                        }
                    }

                    if ( $tampil == 1 ) {
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

    public function get_no_order()
    {
        $noreg = $this->input->post('noreg');

        $m_order_doc = new \Model\Storage\OrderDoc_model();
        $d_order_doc = $m_order_doc->where('noreg', $noreg)->get();

        $_data = null;
        if ( $d_order_doc->count() > 0 ) {
            $d_order_doc = $d_order_doc->toArray();
            foreach ($d_order_doc as $k_order_doc => $v_order_doc) {
                // $m_terima_doc = new \Model\Storage\TerimaDoc_model();
                // $d_terima_doc = $m_terima_doc->where('no_order', $v_order_doc['no_order'])->first();

                // if ( !$d_terima_doc ) {
                //     $_data[ $v_order_doc['no_order'] ] = $v_order_doc;
                // }
                $_data[ $v_order_doc['no_order'] ] = $v_order_doc;
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

    public function save()
    {
        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['file']) ? $_FILES['file'] : null;
        $mappingFiles = !empty($files) ? $this->mappingFiles($files) : null;

        try {
            $m_order_doc = new \Model\Storage\OrderDoc_model();
            $d_order_doc = $m_order_doc->where('no_order', $params['no_order'])->first();

            $m_terima_doc = new \Model\Storage\TerimaDoc_model();
            $now = $m_terima_doc->getDate();

            $id = $m_terima_doc->getNextIdentity();
            $nomor = $m_terima_doc->getNextNomor();

            $path_name = null;
            $file = isset($mappingFiles[ $params['no_sj'] ]) ? $mappingFiles[ $params['no_sj'] ] : null;
            if ( !empty($file) ) {
                $moved = uploadFile($file);
                $isMoved = $moved['status'];
                if ($isMoved) {
                    $path_name = $moved['path'];
                }
            }

            $no_bbm = 'BBM/DOC'.str_replace('ODC', '', $params['no_order']);

            $m_terima_doc->id = $id;
            $m_terima_doc->no_terima = $nomor;
            $m_terima_doc->no_order = $params['no_order'];
            $m_terima_doc->no_sj = $params['no_sj'];
            $m_terima_doc->no_bbm = $no_bbm;
            $m_terima_doc->nopol = $params['nopol'];
            $m_terima_doc->datang = $params['tiba'];
            $m_terima_doc->supplier = $d_order_doc->supplier;
            $m_terima_doc->jml_ekor = $params['jml_ekor'];
            $m_terima_doc->jml_box = $params['jml_box'];
            $m_terima_doc->user_submit = $this->userid;
            $m_terima_doc->tgl_submit = $now['waktu'];
            $m_terima_doc->kondisi = $params['kondisi'];
            $m_terima_doc->keterangan = null;
            $m_terima_doc->version = 1;
            $m_terima_doc->kirim = $params['kirim'];
            $m_terima_doc->bb = $params['bb'];
            $m_terima_doc->harga = $d_order_doc->harga;
            $m_terima_doc->total = $d_order_doc->harga * $params['jml_ekor'];
            $m_terima_doc->path = $path_name;
            $m_terima_doc->uniformity = $params['uniformity'];
            $m_terima_doc->save();

            if ( !empty($params['data_ket']) ) {
                foreach ($params['data_ket'] as $k_dk => $v_dk) {
                    $path_name_ket = null;
                    $file = isset($mappingFiles[ $v_dk['keterangan'] ]) ? $mappingFiles[ $v_dk['keterangan'] ] : null;
                    if ( !empty($file) ) {
                        $moved = uploadFile($file);
                        $isMoved = $moved['status'];
                        if ($isMoved) {
                            $path_name_ket = $moved['path'];
                        }
                    }

                    $m_terima_doc_ket = new \Model\Storage\TerimaDocKet_model();
                    $m_terima_doc_ket->id_header = $id;
                    $m_terima_doc_ket->keterangan = $v_dk['keterangan'];
                    $m_terima_doc_ket->lampiran = $path_name_ket;
                    $m_terima_doc_ket->save();
                }
            }

            // $m_conf = new \Model\Storage\Conf();
            // $sql = "exec insert_jurnal 'DOC', '".$params['no_order']."', NULL, ".($d_order_doc->harga * $params['jml_ekor']).", 'terima_doc', ".$id.", NULL, 1";
            // $m_conf->hydrateRaw( $sql );
            Modules::run( 'base/InsertJurnal/exec', $this->url, $id, null, 1);

            $deskripsi_log_terima_doc = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_terima_doc, $deskripsi_log_terima_doc);


            $this->result['status'] = 1;
            $this->result['message'] = 'Data Terima DOC berhasil disimpan.';
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
            $m_order_doc = new \Model\Storage\OrderDoc_model();
            $d_order_doc = $m_order_doc->where('no_order', $params['no_order'])->first();

            $m_terima_doc = new \Model\Storage\TerimaDoc_model();
            $now = $m_terima_doc->getDate();

            $path_name = $params['lampiran_sj_old'];
            $file = isset($mappingFiles[ $params['no_sj'] ]) ? $mappingFiles[ $params['no_sj'] ] : null;
            if ( !empty($file) ) {
                $moved = uploadFile($file);
                $isMoved = $moved['status'];
                if ($isMoved) {
                    $path_name = $moved['path'];
                }
            }

            $d_terima_doc = $m_terima_doc->where('no_order', $params['no_order_old'])->orderBy('id', 'desc')->first();

            $id = $d_terima_doc->id;

            $no_bbm = 'BBM/DOC'.str_replace('ODC', '', $params['no_order']);

            $m_terima_doc->where('id', $id)->update(
                array(
                    'no_order' => $params['no_order'],
                    'no_sj' => $params['no_sj'],
                    'no_bbm' => $no_bbm,
                    'nopol' => $params['nopol'],
                    'datang' => $params['tiba'],
                    'supplier' => $d_order_doc->supplier,
                    'jml_ekor' => $params['jml_ekor'],
                    'jml_box' => $params['jml_box'],
                    'user_submit' => $this->userid,
                    'tgl_submit' => $now['waktu'],
                    'kondisi' => $params['kondisi'],
                    'keterangan' => null,
                    'kirim' => $params['kirim'],
                    'bb' => $params['bb'],
                    'harga' => $d_order_doc->harga,
                    'total' => $d_order_doc->harga * $params['jml_ekor'],
                    'path' => $path_name,
                    'uniformity' => $params['uniformity']
                )
            );

            $m_terima_doc_ket = new \Model\Storage\TerimaDocKet_model();
            $m_terima_doc_ket->where('id_header', $id)->delete();
            if ( !empty($params['data_ket']) ) {
                foreach ($params['data_ket'] as $k_dk => $v_dk) {
                    $path_name_ket = isset($v_dk['lampiran_old']) ? $v_dk['lampiran_old'] : null;
                    $file = isset($mappingFiles[ $v_dk['keterangan'] ]) ? $mappingFiles[ $v_dk['keterangan'] ] : null;
                    if ( !empty($file) ) {
                        $moved = uploadFile($file);
                        $isMoved = $moved['status'];
                        if ($isMoved) {
                            $path_name_ket = $moved['path'];
                        }
                    }

                    $m_terima_doc_ket = new \Model\Storage\TerimaDocKet_model();
                    $m_terima_doc_ket->id_header = $id;
                    $m_terima_doc_ket->keterangan = $v_dk['keterangan'];
                    $m_terima_doc_ket->lampiran = $path_name_ket;
                    $m_terima_doc_ket->save();
                }
            }

            // $m_conf = new \Model\Storage\Conf();
            // $sql = "exec insert_jurnal 'DOC', '".$params['no_order']."', NULL, ".($d_order_doc->harga * $params['jml_ekor']).", 'terima_doc', ".$id.", ".$id.", 2";
            // // $sql = "exec insert_jurnal 'DOC', '".$params['no_order']."', NULL, ".$params['total'].", 'terima_doc', ".$id_terima.", ".$id_old.", 2";
            // $m_conf->hydrateRaw( $sql );

            $d_terima_doc = $m_terima_doc->where('id', $id)->first();

            Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id, 2);

            $deskripsi_log_terima_doc = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_terima_doc, $deskripsi_log_terima_doc);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Terima DOC berhasil di-update.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {
            $m_order_doc = new \Model\Storage\OrderDoc_model();
            $d_order_doc = $m_order_doc->where('no_order', $params['no_order'])->first();

            $m_terima_doc = new \Model\Storage\TerimaDoc_model();
            $d_terima_doc = $m_terima_doc->where('no_order', $params['no_order'])->first();

            $m_terima_doc->where('id', $d_terima_doc->id)->delete();
            $m_terima_doc_ket = new \Model\Storage\TerimaDocKet_model();
            $m_terima_doc_ket->where('id_header', $d_terima_doc->id)->delete();

            // $m_conf = new \Model\Storage\Conf();
            // $sql = "exec insert_jurnal NULL, NULL, NULL, NULL, 'terima_doc', ".$d_terima_doc->id.", ".$d_terima_doc->id.", 3";
            // $m_conf->hydrateRaw( $sql );

            Modules::run( 'base/InsertJurnal/exec', $this->url, null, $d_terima_doc->id, 3);

            $deskripsi_log = 'hapus data penerimaan doc noreg '.$d_order_doc->noreg.' oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_terima_doc, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus';           
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
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

    public function tes() {
        Modules::run( 'base/InsertJurnal/exec', $this->url, null, 1, 3);
    }
}