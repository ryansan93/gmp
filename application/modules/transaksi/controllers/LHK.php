<?php defined('BASEPATH') or exit('No direct script access allowed');

class LHK extends Public_Controller
{
    private $pathView = 'transaksi/lhk/';
    private $upload_path;
    private $url;
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->upload_path = FCPATH."//uploads/";
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index()
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->load->library('Mobile_Detect');
            $detect = new Mobile_Detect();

            $this->add_external_js(
                array(
                    "assets/select2/js/select2.min.js",
                    "assets/compress-image/js/compress-image.js",
                    'assets/transaksi/lhk/js/lhk.js'
                )
            );
            $this->add_external_css(
                array(
                    "assets/select2/css/select2.min.css",
                    'assets/transaksi/lhk/css/lhk.css'
                )
            );
            $data = $this->includes;

            $isMobile = true;
            if ( $detect->isMobile() ) {
                $isMobile = true;
            }
            
            $mitra = $this->get_mitra();

            $content['akses'] = $akses;
            $content['isMobile'] = $isMobile;
            $content['riwayat'] = $this->riwayat($mitra);
            $content['add_form'] = $this->add_form($mitra);

            $data['title_menu'] = 'Laporan Harian Kandang';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function riwayat($mitra)
    {
        $content['data_mitra'] = $mitra;
        $html = $this->load->view($this->pathView . 'riwayat', $content, TRUE);

        return $html;
    }

    public function list_riwayat()
    {
        $params = $this->input->post('params');

        $m_lhk = new \Model\Storage\Lhk_model();
        $d_lhk = $m_lhk->where('noreg', $params['noreg'])->with(['lhk_sekat', 'lhk_nekropsi', 'lhk_solusi'])->orderBy('id', 'asc')->get();

        $data = array();
        if ( $d_lhk->count() > 0 ) {
            $d_lhk = $d_lhk->toArray();
            foreach ($d_lhk as $k_lhk => $v_lhk) {
                $data[ $v_lhk['umur'] ] = array(
                    'id' => $v_lhk['id'],
                    'umur' => $v_lhk['umur'],
                    'pakai_pakan' => $v_lhk['pakai_pakan'],
                    'sisa_pakan' => $v_lhk['sisa_pakan'],
                    'ekor_mati' => $v_lhk['ekor_mati'],
                    'bb' => $v_lhk['bb'],
                    'fcr' => $v_lhk['fcr'],
                    'ip' => $v_lhk['ip'],
                );

                krsort($data);
            }
        }

        $content['data'] = $data;

        $html = $this->load->view($this->pathView . 'list_riwayat', $content, TRUE);

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
        $content['data_nekropsi'] = $this->get_nekropsi();
        $content['data_solusi'] = $this->get_solusi();

        $html = $this->load->view($this->pathView . 'add_form', $content, TRUE);

        return $html;
    }

    public function detail_form($id)
    {
        $m_lhk = new \Model\Storage\Lhk_model();
        $d_lhk = $m_lhk->where('id', $id)->with(['lhk_sekat', 'lhk_nekropsi', 'lhk_solusi', 'foto_sisa_pakan', 'foto_ekor_mati'])->orderBy('id', 'desc')->first()->toArray();

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('noreg', $d_lhk['noreg'])->with(['mitra'])->first()->toArray();

        $mitra = $d_rs['mitra']['d_mitra']['nama'];

        $data = array(
            'id' => $d_lhk['id'],
            'mitra' => $mitra,
            'noreg' => $d_lhk['noreg'],
            'umur' => $d_lhk['umur'],
            'bb' => $d_lhk['bb'],
            'adg' => $d_lhk['adg'],
            'fcr' => $d_lhk['fcr'],
            'ip' => $d_lhk['ip'],
            'pakai_pakan' => $d_lhk['pakai_pakan'],
            'sisa_pakan' => $d_lhk['sisa_pakan'],
            'foto_sisa_pakan' => $d_lhk['foto_sisa_pakan'],
            'ekor_mati' => $d_lhk['ekor_mati'],
            'foto_ekor_mati' => $d_lhk['foto_ekor_mati'],
            'lampiran_sisa_pakan' => $d_lhk['lampiran_sisa_pakan'],
            'lampiran_ekor_mati' => $d_lhk['lampiran_ekor_mati'],
            'lhk_sekat' => $d_lhk['lhk_sekat'],
            'lhk_nekropsi' => $d_lhk['lhk_nekropsi'],
            'lhk_solusi' => $d_lhk['lhk_solusi'],
            'keterangan' => $d_lhk['keterangan'],
            'tanggal' => $d_lhk['tanggal'],
            'lat' => !empty($d_lhk['lat_long']) ? trim(explode(',', $d_lhk['lat_long'])[0]) : null,
            'long' => !empty($d_lhk['lat_long']) ? trim(explode(',', $d_lhk['lat_long'])[1]) : null,
            'status' => $d_lhk['status']
        );

        $akses = hakAkses($this->url);
        $content['data'] = $data;
        $content['akses'] = $akses;

        $html = $this->load->view($this->pathView . 'detail_form', $content, TRUE);

        return $html;
    }

    public function edit_form($id, $mitra)
    {
        $m_lhk = new \Model\Storage\Lhk_model();
        $d_lhk = $m_lhk->where('id', $id)->with(['lhk_sekat', 'lhk_nekropsi', 'lhk_solusi', 'foto_sisa_pakan', 'foto_ekor_mati'])->orderBy('id', 'desc')->first()->toArray();

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('noreg', $d_lhk['noreg'])->with(['mitra'])->first()->toArray();

        $nomor = $d_rs['mitra']['d_mitra']['nomor'];

        $data = array(
            'id' => $d_lhk['id'],
            'nomor' => $nomor,
            'noreg' => $d_lhk['noreg'],
            'umur' => $d_lhk['umur'],
            'pakai_pakan' => $d_lhk['pakai_pakan'],
            'sisa_pakan' => $d_lhk['sisa_pakan'],
            'foto_sisa_pakan' => $d_lhk['foto_sisa_pakan'],
            'ekor_mati' => $d_lhk['ekor_mati'],
            'foto_ekor_mati' => $d_lhk['foto_ekor_mati'],
            'lampiran_sisa_pakan' => $d_lhk['lampiran_sisa_pakan'],
            'lampiran_ekor_mati' => $d_lhk['lampiran_ekor_mati'],
            'lhk_sekat' => $d_lhk['lhk_sekat'],
            'lhk_nekropsi' => $d_lhk['lhk_nekropsi'],
            'lhk_solusi' => $d_lhk['lhk_solusi'],
            'keterangan' => $d_lhk['keterangan'],
            'tanggal' => $d_lhk['tanggal'],
            'lat' => !empty($d_lhk['lat_long']) ? trim(explode(',', $d_lhk['lat_long'])[0]) : null,
            'long' => !empty($d_lhk['lat_long']) ? trim(explode(',', $d_lhk['lat_long'])[1]) : null
        );

        $content['data_mitra'] = $this->get_mitra();
        $content['data_nekropsi'] = $this->get_nekropsi();
        $content['data_solusi'] = $this->get_solusi();
        $content['data'] = $data;

        $html = $this->load->view($this->pathView . 'edit_form', $content, TRUE);

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

        // $start_date = prev_date(date('Y-m-d'), 90).' 00:00:00.000';
        $start_date = prev_date(date('Y-m-d'), 120).' 00:00:00.000';

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
        $noreg = $this->input->post('noreg');
        $div_id = $this->input->post('div_id');

        $end_date = date('Y-m-d').' 23:59:59.999';
        // $start_date = prev_date(date('Y-m-d'), 60).' 00:00:00.999';
        $start_date = prev_date(date('Y-m-d'), 120).' 00:00:00.999';
        
        $sql_cek_ts = "and ts.id is null and rs.tgl_docin between '".$start_date."' and '".$end_date."'";
        if ( $div_id == 'riwayat' ) {
            $sql_cek_ts = "";
        } else {
            if ( !empty($noreg) ) {
                $sql_cek_ts = "and (ts.id is null or rs.noreg = '".$noreg."')";
            }
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                rs.noreg,
                case
                    when td.id is not null then
                        td.datang
                    else
                        rs.tgl_docin        
                end real_tgl_docin,
                case
                    when td.id is not null then
                        td.datang
                    else
                        rs.tgl_docin        
                end tgl_docin,
                max(l.tanggal) as tgl_lhk_terakhir,
                cast(SUBSTRING(rs.noreg, 10, 2) as int) as kandang,
                case
                    when td.id is not null then
                        DATEDIFF(day, td.datang, GETDATE())
                    else
                        DATEDIFF(day, rs.tgl_docin, GETDATE())
                end umur
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
                tutup_siklus ts
                on
                    rs.noreg = ts.noreg
            left join
                (
                    select od1.* from order_doc od1
                    right join
                        (select max(id) as id, no_order from order_doc group by no_order) od2
                        on
                            od1.id = od2.id
                ) od
                on
                    rs.noreg = od.noreg
            left join
                (
                    select td1.* from terima_doc td1
                    right join
                        (select max(id) as id, no_order from terima_doc group by no_order) td2
                        on
                            td1.id = td2.id
                ) td
                on
                    od.no_order = td.no_order
            left join
                lhk l
                on
                    rs.noreg = l.noreg
            where
                mm.nomor = '".$nomor_mitra."'
                ".$sql_cek_ts."
            group by
                rs.noreg,
                td.datang,
                rs.tgl_docin,
                td.id,
                ts.id
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $_data = array();
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $key = str_replace('-', '', $value['tgl_docin']).' - '.substr($value['noreg'], -1);
                $_data[ $key ] = array(
                    'noreg' => $value['noreg'],
                    'real_tgl_docin' => $value['tgl_docin'],
                    'tgl_docin' => strtoupper(tglIndonesia($value['tgl_docin'], '-', ' ')),
                    'tgl_lhk_terakhir' => (isset($value['tgl_lhk_terakhir']) && !empty($value['tgl_lhk_terakhir'])) ? $value['tgl_lhk_terakhir'] : '',
                    'kandang' => 'KD - '.$value['kandang'],
                    'umur' => $value['umur'],
                );
            }
        }

        // $end_date = date('Y-m-d').' 23:59:59.999';
        // $start_date = prev_date(date('Y-m-d'), 60).' 00:00:00.999';

        // $m_mm = new \Model\Storage\MitraMapping_model();
        // $d_mm = $m_mm->select('nim')->where('nomor', $nomor_mitra)->get()->toArray();

        // $m_rs = new \Model\Storage\RdimSubmit_model();
        // $d_rs = $m_rs->whereIn('nim', $d_mm)->whereBetween('tgl_docin', [$start_date, $end_date])->get();

        // $_data = array();
        // if ( $d_rs->count() > 0 ) {
        //     $d_rs = $d_rs->toArray();
        //     foreach ($d_rs as $k_rs => $v_rs) {
        //         $m_ts = new \Model\Storage\TutupSiklus_model();
        //         $d_ts = $m_ts->where('noreg', $v_rs['noreg'])->first();

        //         // if ( empty($d_ts) ) {
        //             $m_lhk = new \Model\Storage\Lhk_model();
        //             $d_lhk = $m_lhk->where('noreg', $v_rs['noreg'])->orderBy('umur', 'desc')->first();

        //             $m_od = new \Model\Storage\OrderDoc_model();
        //             $d_od = $m_od->where('noreg', $v_rs['noreg'])->first();

        //             $tgl_docin = substr($v_rs['tgl_docin'], 0, 10);
        //             if ( !empty($d_od) ) {
        //                 $m_td = new \Model\Storage\TerimaDoc_model();
        //                 $d_td = $m_td->where('no_order', $d_od->no_order)->orderBy('id', 'desc')->first();

        //                 if ( !empty($d_td) ) {
        //                     $tgl_docin = substr($d_td->datang, 0, 10);
        //                 }
        //             }

        //             $kandang = (int) substr($v_rs['noreg'], -1);

        //             $key = str_replace('-', '', $tgl_docin).' - '.substr($v_rs['noreg'], -1);
        //             $_data[ $key ] = array(
        //                 'noreg' => $v_rs['noreg'],
        //                 'real_tgl_docin' => $tgl_docin,
        //                 'tgl_docin' => strtoupper(tglIndonesia($tgl_docin, '-', ' ')),
        //                 'tgl_lhk_terakhir' => !empty($d_lhk) ? $d_lhk->tanggal : '',
        //                 'kandang' => 'KD - '.$kandang,
        //                 'umur' => selisihTanggal($tgl_docin, date('Y-m-d'))
        //             );
        //         // }
        //     }
        // }

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

    public function get_nekropsi()
    {
        $m_nekropsi = new \Model\Storage\Nekropsi_model();
        $d_nekropsi = $m_nekropsi->get();

        $data = null;
        if ( $d_nekropsi->count() > 0 ) {
            $data = $d_nekropsi->toArray();
        }

        return $data;
    }

    public function get_solusi()
    {
        $m_solusi = new \Model\Storage\Solusi_model();
        $d_solusi = $m_solusi->get();

        $data = null;
        if ( $d_solusi->count() > 0 ) {
            $data = $d_solusi->toArray();
        }

        return $data;
    }

    public function mappingFiles($files)
    {
        $mappingFiles = [];
        foreach ($files['tmp_name'] as $k_tmp => $v_tmp) {
            if ( $k_tmp != 'nekropsi' ) {
                foreach ($v_tmp as $key => $file) {
                    $sha1 = sha1_file($file);

                    $index = $key;

                    $mappingFiles[$k_tmp][$index] = [
                        'name' => $files['name'][$k_tmp][$key],
                        'tmp_name' => $file,
                        'type' => $files['type'][$k_tmp][$key],
                        'size' => $files['size'][$k_tmp][$key],
                        'error' => $files['error'][$k_tmp][$key]
                    ];
                }
            } else {
                foreach ($v_tmp as $k_nekropsi => $v_nekropsi) {
                    foreach ($v_nekropsi as $key => $file) {
                        $sha1 = sha1_file($file);

                        $index = $key;

                        $mappingFiles[ $k_tmp ][ $k_nekropsi ][$index] = [
                            'name' => $files['name'][$k_tmp][$k_nekropsi][$key],
                            'tmp_name' => $file,
                            'type' => $files['type'][$k_tmp][$k_nekropsi][$key],
                            'size' => $files['size'][$k_tmp][$k_nekropsi][$key],
                            'error' => $files['error'][$k_tmp][$k_nekropsi][$key]
                        ];
                    }
                }
            }
        }
        return $mappingFiles;
    }

    // Compress image
    public function compressImage($source, $destination, $quality) 
    {
        $info = getimagesize($source);
        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($source);
        } elseif ($info['mime'] == 'image/gif') {
            $image = imagecreatefromgif($source);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source);
        }

        $img = imagejpeg($image, $destination, $quality);

        imagedestroy($image);

        return $img;
    }

    public function uploadSisaPakan()
    {
        $url = json_decode($this->input->post('url'),TRUE);
        $files = isset($_FILES['attachments']) ? $_FILES['attachments'] : [];
        $mappingFiles = !empty($files) ? $this->mappingFiles($files) : null;

        try {
            $m_conf = new \Model\Storage\Conf();
            $now = $m_conf->getDate();

            $tanggal = str_replace('-', '',  $now['tanggal']);
            $folder_name = $this->userid.'_'.$tanggal;
            $path_folder = "uploads/LHK/SISA_PAKAN/".$folder_name;

            if (!file_exists($path_folder)) {
                mkdir($path_folder, 0777, true);
            }

            // if ( !empty($url) ) {
            //     $path_folder = $url;
            // }

            // if ( !is_dir($path_folder) ) {
            //     mkdir($path_folder, 0777);
            // }

            if ( isset($mappingFiles['sisa_pakan']) ) {
                foreach ($mappingFiles['sisa_pakan'] as $k_mf => $v_mf) {
                    $path_name  = null;
                    $moved = uploadFile($v_mf, $path_folder.'/');
                    $isMoved = $moved['status'];
                    if ($isMoved) {
                        $path_name = $moved['path'];
                    }
                }
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di upload.';
            $this->result['content'] = array(
                'path_folder' => $path_folder
            );
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function uploadKematian()
    {
        $url = json_decode($this->input->post('url'),TRUE);
        $files = isset($_FILES['attachments']) ? $_FILES['attachments'] : [];
        $mappingFiles = !empty($files) ? $this->mappingFiles($files) : null;

        try {
            $m_conf = new \Model\Storage\Conf();
            $now = $m_conf->getDate();

            $tanggal = str_replace('-', '',  $now['tanggal']);
            $folder_name = $this->userid.'_'.$tanggal;
            $path_folder = "uploads/LHK/KEMATIAN/".$folder_name;

            if (!file_exists($path_folder)) {
                mkdir($path_folder, 0777, true);
            }

            // if ( !empty($url) ) {
            //     $path_folder = $url;
            // }

            // if ( !is_dir($path_folder) ) {
            //     mkdir($path_folder);
            // }

            if ( isset($mappingFiles['kematian']) ) {
                foreach ($mappingFiles['kematian'] as $k_mf => $v_mf) {
                    $path_name  = null;
                    $moved = uploadFile($v_mf, $path_folder.'/');
                    $isMoved = $moved['status'];
                    if ($isMoved) {
                        $path_name = $moved['path'];
                    }
                }
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di upload.';
            $this->result['content'] = array(
                'path_folder' => $path_folder
            );
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function createFolderNekropsi() {
        $id_nekropsi = $this->input->post('id_nekropsi');
        $checked = $this->input->post('checked');
        $dirStatus = $this->input->post('dirStatus');

        try {
            $m_conf = new \Model\Storage\Conf();
            $now = $m_conf->getDate();

            $tanggal = str_replace('-', '',  $now['tanggal']);
            $folder_name = $this->userid.'_'.$tanggal.'_'.$id_nekropsi;
            $path_folder = "uploads/LHK/NEKROPSI/".$folder_name;

            if ( $checked == 1 ) {
                if ( $dirStatus == 0 ) {
                    // if ( !is_dir($path_folder) ) {
                    //     mkdir($path_folder);
                    // }

                    if (!file_exists($path_folder)) {
                        mkdir($path_folder, 0777, true);
                    }
                }
            } else {
                if ( !is_dir($path_folder) ) {
                    $this->deleteDirectory($path_folder);
                }
            }

            $this->result['status'] = 1;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function uploadNekropsi()
    {
        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['attachments']) ? $_FILES['attachments'] : [];
        $mappingFiles = !empty($files) ? $this->mappingFiles($files) : null;

        try {
            $m_conf = new \Model\Storage\Conf();
            $now = $m_conf->getDate();

            $tanggal = str_replace('-', '',  $now['tanggal']);
            $folder_name = $this->userid.'_'.$tanggal.'_'.$params['id'];
            $path_folder = "uploads/LHK/NEKROPSI/".$folder_name;

            // if ( !is_dir($path_folder) ) {
            //     mkdir($path_folder);
            // }

            if (!file_exists($path_folder)) {
                mkdir($path_folder, 0777, true);
            }

            if ( isset($mappingFiles['nekropsi'][$params['id']]) ) {
                foreach ($mappingFiles['nekropsi'][$params['id']] as $k_mf => $v_mf) {
                    $path_name  = null;
                    $moved = uploadFile($v_mf, $path_folder.'/');
                    $isMoved = $moved['status'];
                    if ($isMoved) {
                        $path_name = $moved['path'];
                    }
                }
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di upload.';
            $this->result['content'] = array(
                'path_folder' => $path_folder
            );
            // $this->result['html'] = $html;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function preview_file_attachment()
    {
        $judul = $this->input->get('judul');
        $jenis = $this->input->get('jenis');
        $data_url = $this->input->get('data_url');
        
        $url = null;
        if ( is_dir( $data_url ) ) {
            $_url = array_diff(scandir($data_url), array('.', '..'));

            foreach ($_url as $key => $value) {
                $url[] = $data_url.'/'.$value;
            }
        }

        $content['judul'] = $judul;
        $content['jenis'] = $jenis;
        $content['url'] = $url;
        $html = $this->load->view($this->pathView . 'preview_file_attachment', $content, TRUE);

        echo $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_conf = new \Model\Storage\Conf();
            $now = $m_conf->getDate();

            $m_lhk = new \Model\Storage\Lhk_model();
            $d_lhk_last_data = $m_lhk->where('noreg', $params['noreg'])->with(['lhk_sekat', 'lhk_nekropsi', 'lhk_solusi'])->where('umur', '<', $params['umur'])->orderBy('umur', 'desc')->first();

            $pakai_pakan = $params['pakai_pakan'];
            $sisa_pakan = $params['sisa_pakan'];
            $ekor_mati = $params['ekor_mati'];

            $m_rs = new \Model\Storage\RdimSubmit_model();
            $d_rs = $m_rs->where('noreg', $params['noreg'])->first();

            $m_ov = new \Model\Storage\OrderDoc_model();
            $d_ov = $m_ov->where('noreg', $params['noreg'])->first();

            $populasi = $d_rs->populasi;
            $bb_first = 0;
            if ( $d_ov ) {
                $m_td = new \Model\Storage\TerimaDoc_model();
                $d_td = $m_td->where('no_order', $d_ov->no_order)->orderBy('version', 'desc')->first();
                if ( $d_td ) {
                    $bb_first = $d_td->bb;
                    $populasi = $d_td->jml_ekor;
                }
            }

            $umur = $params['umur'];

            $total = 0;
            for ($i = 0; $i < count($params['data_sekat']); $i++) {
                $total += $params['data_sekat'][$i]['bb'];
            }
            $bb_rata = $total / count($params['data_sekat']);

            // HITUNG ADG
            $selisih_umur = 0;

            if ( empty($d_lhk_last_data) ) {
                $selisih_umur = $umur;
            } else {
                $bb_first = $d_lhk_last_data->bb;

                $selisih_umur = $umur - $d_lhk_last_data->umur;
            }

            $adg = 0;
            if ( ($bb_rata - $bb_first) != 0 && $selisih_umur != 0 ) {
                $adg = ($bb_rata - $bb_first) / $selisih_umur;
            }
            // END - HITUNG ADG

            // HITUNG FCR
            $fcr = 0;
            $kg_konsumsi = $pakai_pakan * 50;
            $ekor_sisa = $populasi - $ekor_mati;
            $kg_panen = $ekor_sisa * $bb_rata;
            
            if ($kg_konsumsi > 0 && $kg_panen > 0) {
                $fcr = (($kg_konsumsi / $kg_panen) * 1000) / 1000;
            }
            // END - HITUNG FCR
            
            // HITUNG DH
            $ekor_panen = $ekor_sisa;
            $dh = 0;
            if ( $ekor_sisa != 0 && $populasi != 0 ) {
                $dh = (($ekor_sisa / $populasi) * 10000) / 10000;
            }

            $persen_dh = $dh * 100;
            // END - HITUNG DH

            // HITUNG IP
            // $up = (($params['umur'] * $ekor_panen) / $ekor_panen) * 100;
            $ip = 0;
            if ( ($dh * $bb_rata * 10000) != 0 && ($fcr * $umur) != 0 ) {
                $ip = ($dh * $bb_rata * 10000) / ($fcr * $umur);
            }
            // END - HIUTNG IP

            $m_kry = new \Model\Storage\Karyawan_model();
            $m_lhk = new \Model\Storage\Lhk_model();
            $m_lhk->noreg = $params['noreg'];
            $m_lhk->tgl_docin = substr($d_rs->tgl_docin, 0, 10);
            $m_lhk->umur = $umur;
            $m_lhk->bb = $bb_rata;
            $m_lhk->adg = $adg;
            $m_lhk->fcr = $fcr;
            $m_lhk->ip = $ip;
            $m_lhk->pakai_pakan = $pakai_pakan;
            $m_lhk->sisa_pakan = $sisa_pakan;
            $m_lhk->ekor_mati = $ekor_mati;
            $m_lhk->keterangan = $params['keterangan'];
            $m_lhk->tanggal = $params['tanggal'];
            $m_lhk->nik = $m_kry->getNik( $this->userdata['detail_user']['nama_detuser'] );
            $m_lhk->lat_long = $params['lat'].','.$params['long'];
            $m_lhk->status = 1;
            $m_lhk->save();

            $id_lhk = $m_lhk->id;

            /* SISA PAKAN */
            $tanggal = str_replace('-', '',  $now['tanggal']);
            $old_folder_name = $this->userid.'_'.$tanggal;
            $old_dir_name = "uploads/LHK/SISA_PAKAN/".$old_folder_name;

            $new_folder_name = $id_lhk;
            $new_dir_name = "uploads/LHK/SISA_PAKAN/".$new_folder_name;

            // Renames the directory
            if ( is_dir($old_dir_name) ) {
                renameWin($old_dir_name, $new_dir_name);
            }

            if ( is_dir( $new_dir_name ) ) {
                $_url = array_diff(scandir($new_dir_name), array('.', '..'));

                foreach ($_url as $key => $value) {
                    $m_lfsp = new \Model\Storage\LhkFotoSisaPakan_model();
                    $m_lfsp->id_header = $id_lhk;
                    $m_lfsp->filename = $value;
                    $m_lfsp->path = $new_dir_name.'/'.$value;
                    $m_lfsp->save();
                }
            }
            /* END - SISA PAKAN */

            /* KEMATIAN */
            $tanggal = str_replace('-', '',  $now['tanggal']);
            $old_folder_name = $this->userid.'_'.$tanggal;
            $old_dir_name = "uploads/LHK/KEMATIAN/".$old_folder_name;

            $new_folder_name = $id_lhk;
            $new_dir_name = "uploads/LHK/KEMATIAN/".$new_folder_name;

            // Renames the directory
            if ( is_dir($old_dir_name) ) {
                renameWin($old_dir_name, $new_dir_name);
            }

            if ( is_dir( $new_dir_name ) ) {
                $_url = array_diff(scandir($new_dir_name), array('.', '..'));

                foreach ($_url as $key => $value) {
                    $m_lfem = new \Model\Storage\LhkFotoEkorMati_model();
                    $m_lfem->id_header = $id_lhk;
                    $m_lfem->filename = $value;
                    $m_lfem->path = $new_dir_name.'/'.$value;
                    $m_lfem->save();
                }
            }
            /* END - KEMATIAN */

            if ( count($params['data_sekat']) > 0 ) {
                foreach ($params['data_sekat'] as $k_ds => $v_ds) {
                    $m_ls = new \Model\Storage\LhkSekat_model();
                    $m_ls->id_header = $id_lhk;
                    $m_ls->no = $v_ds['no'];
                    $m_ls->bb = $v_ds['bb'];
                    $m_ls->save();
                }
            }

            if ( count($params['data_nekropsi']) > 0 ) {
                foreach ($params['data_nekropsi'] as $k_dn => $v_dn) {
                    $m_ln = new \Model\Storage\LhkNekropsi_model();
                    $m_ln->id_header = $id_lhk;
                    $m_ln->id_nekropsi = $v_dn['id'];
                    $m_ln->keterangan = $v_dn['keterangan'];
                    $m_ln->save();

                    $id_lhk_nekropsi = $m_ln->id;

                    $tanggal = str_replace('-', '',  $now['tanggal']);
                    $old_folder_name = $this->userid.'_'.$tanggal.'_'.$v_dn['id'];
                    $old_dir_name = "uploads/LHK/NEKROPSI/".$old_folder_name;

                    $new_folder_name = $id_lhk.'_'.$v_dn['id'];
                    $new_dir_name = "uploads/LHK/NEKROPSI/".$new_folder_name;

                    // Renames the directory
                    if ( is_dir($old_dir_name) ) {
                        renameWin($old_dir_name, $new_dir_name);
                    }

                    if ( is_dir( $new_dir_name ) ) {
                        $_url = array_diff(scandir($new_dir_name), array('.', '..'));
        
                        foreach ($_url as $key => $value) {
                            $m_lfem = new \Model\Storage\LhkFotoNekropsi_model();
                            $m_lfem->id_header = $id_lhk_nekropsi;
                            $m_lfem->filename = $value;
                            $m_lfem->path = $new_dir_name.'/'.$value;
                            $m_lfem->save();
                        }
                    }
                }
            }

            if ( count($params['data_solusi']) > 0 ) {
                foreach ($params['data_solusi'] as $k_ds => $v_ds) {
                    $m_ls = new \Model\Storage\LhkSolusi_model();
                    $m_ls->id_header = $id_lhk;
                    $m_ls->id_solusi = $v_ds['id'];
                    $m_ls->save();
                }
            }

            $d_lhk = $m_lhk->where('id', $id_lhk)->orderBy('umur', 'desc')->first();

            $deskripsi_log = 'di-simpan oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_lhk, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
            $this->result['content'] = array('id' => $id_lhk);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit()
    {
        // $params = json_decode($this->input->post('data'),TRUE);
        // $files = isset($_FILES['attachments']) ? $_FILES['attachments'] : [];
        // $mappingFiles = !empty($files) ? $this->mappingFiles($files) : null;

        $params = $this->input->post('params');

        try {
            $m_conf = new \Model\Storage\Conf();
            $now = $m_conf->getDate();

            $m_lhk = new \Model\Storage\Lhk_model();
            $d_lhk_by_id = $m_lhk->where('id', $params['id'])->first();

            $d_lhk_last_data = $m_lhk->where('noreg', $params['noreg'])->where('umur', '<', $params['umur'])->with(['lhk_sekat', 'lhk_nekropsi', 'lhk_solusi'])->orderBy('umur', 'desc')->first();

            $pakai_pakan = $params['pakai_pakan'];
            $sisa_pakan = $params['sisa_pakan'];
            $ekor_mati = $params['ekor_mati'];

            $m_rs = new \Model\Storage\RdimSubmit_model();
            $d_rs = $m_rs->where('noreg', $params['noreg'])->first();

            $m_ov = new \Model\Storage\OrderDoc_model();
            $d_ov = $m_ov->where('noreg', $params['noreg'])->first();

            $populasi = $d_rs->populasi;
            $bb_first = 0;
            if ( $d_ov ) {
                $m_td = new \Model\Storage\TerimaDoc_model();
                $d_td = $m_td->where('no_order', $d_ov->no_order)->orderBy('version', 'desc')->first();
                if ( $d_td ) {
                    $bb_first = $d_td->bb;
                    $populasi = $d_td->jml_ekor;
                }
            }
            
            $umur = $params['umur'];

            $total = 0;
            for ($i = 0; $i < count($params['data_sekat']); $i++) {
                $total += $params['data_sekat'][$i]['bb'];
            }

            $bb_rata = 0;
            if ( $total != 0 && count($params['data_sekat']) != 0 ) {
                $bb_rata = $total / count($params['data_sekat']);
            }

            // HITUNG ADG
            $selisih_umur = 0;

            if ( empty($d_lhk_last_data) ) {
                $selisih_umur = $umur;
            } else {
                $bb_first = $d_lhk_last_data->bb;

                $selisih_umur = $umur - $d_lhk_last_data->umur;
            }


            $adg = 0;
            if ( ($bb_rata - $bb_first) != 0 && $selisih_umur != 0 ) {
                $adg = ($bb_rata - $bb_first) / $selisih_umur;
            }
            // END - HITUNG ADG

            // HITUNG FCR
            $fcr = 0;
            $kg_konsumsi = $pakai_pakan * 50;
            $ekor_sisa = $populasi - $ekor_mati;
            $kg_panen = $ekor_sisa * $bb_rata;
            
            if ($kg_konsumsi > 0 && $kg_panen > 0) {
                $fcr = (($kg_konsumsi / $kg_panen) * 1000) / 1000;
            }
            // END - HITUNG FCR
            
            // HITUNG DH
            $ekor_panen = $ekor_sisa;
            $dh = 0;
            if ( $ekor_sisa != 0 && $populasi != 0 ) {
                $dh = (($ekor_sisa / $populasi) * 10000) / 10000;
            }

            $persen_dh = $dh * 100;
            // END - HITUNG DH

            // HITUNG IP
            // $up = (($params['umur'] * $ekor_panen) / $ekor_panen) * 100;
            $ip = 0;
            if ( ($dh * $bb_rata * 10000) != 0 && ($fcr * $umur) != 0 ) {
                $ip = ($dh * $bb_rata * 10000) / ($fcr * $umur);
            }
            // END - HIUTNG IP

            $m_kry = new \Model\Storage\Karyawan_model();
            $m_lhk = new \Model\Storage\Lhk_model();
            $m_lhk->where('id', $params['id'])->update(
                array(
                    'noreg' => $params['noreg'],
                    'tgl_docin' => substr($d_rs->tgl_docin, 0, 10),
                    'umur' => $umur,
                    'bb' => $bb_rata,
                    'adg' => $adg,
                    'fcr' => $fcr,
                    'ip' => $ip,
                    'pakai_pakan' => $pakai_pakan,
                    'sisa_pakan' => $sisa_pakan,
                    'ekor_mati' => $ekor_mati,
                    'keterangan' => $params['keterangan'],
                    'nik' => $m_kry->getNik( $this->userdata['detail_user']['nama_detuser'] ),
                    'lat_long' => $params['lat'].','.$params['long'],
                    'status' => 1
                )
            );

            $id_lhk = $params['id'];

            /* SISA PAKAN */
            $m_lfsp = new \Model\Storage\LhkFotoSisaPakan_model();
            $m_lfsp->where('id_header', $id_lhk)->delete();

            $new_folder_name = $id_lhk;
            $new_dir_name = "uploads/LHK/SISA_PAKAN/".$new_folder_name;

            if ( is_dir( $new_dir_name ) ) {
                $_url = array_diff(scandir($new_dir_name), array('.', '..'));

                foreach ($_url as $key => $value) {
                    $m_lfsp = new \Model\Storage\LhkFotoSisaPakan_model();
                    $m_lfsp->id_header = $id_lhk;
                    $m_lfsp->filename = $value;
                    $m_lfsp->path = $new_dir_name.'/'.$value;
                    $m_lfsp->save();
                }
            }
            /* END - SISA PAKAN */

            /* KEMATIAN */
            $m_lfem = new \Model\Storage\LhkFotoEkorMati_model();
            $m_lfem->where('id_header', $id_lhk)->delete();

            $new_folder_name = $id_lhk;
            $new_dir_name = "uploads/LHK/KEMATIAN/".$new_folder_name;

            if ( is_dir( $new_dir_name ) ) {
                $_url = array_diff(scandir($new_dir_name), array('.', '..'));

                foreach ($_url as $key => $value) {
                    $m_lfem = new \Model\Storage\LhkFotoEkorMati_model();
                    $m_lfem->id_header = $id_lhk;
                    $m_lfem->filename = $value;
                    $m_lfem->path = $new_dir_name.'/'.$value;
                    $m_lfem->save();
                }
            }
            /* END - KEMATIAN */

            if ( count($params['data_sekat']) > 0 ) {
                $m_lfem = new \Model\Storage\LhkSekat_model();
                $m_lfem->where('id_header', $id_lhk)->delete();

                foreach ($params['data_sekat'] as $k_ds => $v_ds) {
                    $m_ls = new \Model\Storage\LhkSekat_model();
                    $m_ls->id_header = $id_lhk;
                    $m_ls->no = $v_ds['no'];
                    $m_ls->bb = $v_ds['bb'];
                    $m_ls->save();
                }
            }

            if ( count($params['data_nekropsi']) > 0 ) {
                foreach ($params['data_nekropsi'] as $k_dn => $v_dn) {
                    $m_ln = new \Model\Storage\LhkNekropsi_model();
                    $d_ln = $m_ln->where('id_header', $id_lhk)->where('id_nekropsi', $v_dn['id'])->first();

                    $id_lhk_nekropsi = null;
                    if ( $d_ln ) {
                        $m_ln->where('id_header', $id_lhk)->where('id_nekropsi', $v_dn['id'])->update(
                            array(
                                'keterangan' => $v_dn['keterangan']
                            )
                        );
                        $id_lhk_nekropsi = $d_ln->id;
                    } else {
                        $m_ln->id_header = $id_lhk;
                        $m_ln->id_nekropsi = $v_dn['id'];
                        $m_ln->keterangan = $v_dn['keterangan'];
                        $m_ln->save();

                        $id_lhk_nekropsi = $m_ln->id;
                    }

                    $m_lfem = new \Model\Storage\LhkFotoNekropsi_model();
                    $m_lfem->where('id_header', $id_lhk_nekropsi)->delete();

                    $tanggal = str_replace('-', '',  $now['tanggal']);
                    $old_folder_name = $this->userid.'_'.$tanggal.'_'.$v_dn['id'];
                    $old_dir_name = "uploads/LHK/NEKROPSI/".$old_folder_name;

                    $new_folder_name = $id_lhk.'_'.$v_dn['id'];
                    $new_dir_name = "uploads/LHK/NEKROPSI/".$new_folder_name;

                    // Renames the directory
                    if ( is_dir($old_dir_name) && !is_dir($new_dir_name)) {
                        renameWin($old_dir_name, $new_dir_name);
                    } 
                    // else {
                    //     // shell_exec("cp -r $old_dir_name $new_dir_name");
                    //     // deleteDirectory($old_dir_name);
                    //     copyDiectory($old_dir_name, $new_dir_name);
                    //     deleteDirectory($old_dir_name);
                    // }

                    if ( is_dir( $new_dir_name ) ) {
                        $_url = array_diff(scandir($new_dir_name), array('.', '..'));
        
                        foreach ($_url as $key => $value) {
                            $m_lfem = new \Model\Storage\LhkFotoNekropsi_model();
                            $m_lfem->id_header = $id_lhk_nekropsi;
                            $m_lfem->filename = $value;
                            $m_lfem->path = $new_dir_name.'/'.$value;
                            $m_lfem->save();
                        }
                    }
                }
            }

            if ( count($params['data_solusi']) > 0 ) {
                $m_ls = new \Model\Storage\LhkSolusi_model();
                $m_ls->where('id_header', $id_lhk)->delete();

                foreach ($params['data_solusi'] as $k_ds => $v_ds) {
                    $m_ls = new \Model\Storage\LhkSolusi_model();
                    $m_ls->id_header = $id_lhk;
                    $m_ls->id_solusi = $v_ds['id'];
                    $m_ls->save();
                }
            }

            $d_lhk = $m_lhk->where('id', $id_lhk)->orderBy('umur', 'desc')->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_lhk, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di update.';
            $this->result['content'] = array('id' => $params['id']);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete()
    {
        $id = $this->input->post('params');
        try {
            $m_lhk = new \Model\Storage\Lhk_model();
            $d_lhk = $m_lhk->where('id', $id)->first();

            $deskripsi_log = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_lhk, $deskripsi_log);

            $id_lhk = $id;

            /* SISA PAKAN */
            $folder_name = $id_lhk;
            $dir_name = "uploads/LHK/SISA_PAKAN/".$folder_name;
            if ( is_dir($dir_name) ) {
                $this->deleteDirectory($dir_name);
            }

            $m_lfsp = new \Model\Storage\LhkFotoSisaPakan_model();
            $m_lfsp->where('id_header', $id_lhk)->delete();
            /* END - SISA PAKAN */

            /* KEMATIAN */
            $folder_name = $id_lhk;
            $dir_name = "uploads/LHK/KEMATIAN/".$folder_name;
            if ( is_dir($dir_name) ) {
                $this->deleteDirectory($dir_name);
            }

            $m_lfem = new \Model\Storage\LhkFotoEkorMati_model();
            $m_lfem->where('id_header', $id_lhk)->delete();
            /* END - KEMATIAN */

            $m_lfem = new \Model\Storage\LhkSekat_model();
            $m_lfem->where('id_header', $id_lhk)->delete();

            $m_ln = new \Model\Storage\LhkNekropsi_model();
            $d_ln = $m_ln->where('id_header', $id_lhk)->get();
            if ( $d_ln->count() > 0 ) {
                $d_ln = $d_ln->toArray();

                foreach ($d_ln as $key => $value) {
                    $folder_name = $id_lhk.'_'.$value['id_nekropsi'];
                    $dir_name = "uploads/LHK/NEKROPSI/".$folder_name;
                    if ( is_dir($dir_name) ) {
                        $this->deleteDirectory($dir_name);
                    }

                    $m_lfem = new \Model\Storage\LhkFotoNekropsi_model();
                    $m_lfem->where('id_header', $value['id'])->delete();
    
                    $m_ln->where('id', $value['id'])->delete();
                }

            }

            $m_lhk->where('id', $id)->delete();

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function deleteDirectory($dir) {
        system('rm -rf -- ' . escapeshellarg($dir), $retval);
        return $retval == 0; // UNIX commands return zero on success
    }

    public function deleteFile() {
        $url = $this->input->post('url');

        try {
            $this->deleteDirectory($url);
            
            $this->result['status'] = 1;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function ack() {
        $params = $this->input->post('params');

        try {
            $list_id = $params['list_id'];

            foreach ($list_id as $key => $value) {
                $m_lhk = new \Model\Storage\Lhk_model();
                $m_lhk->where('id', $value)->update(
                    array(
                        'status' => getStatus('ack')
                    )
                );

                $d_lhk = $m_lhk->where('id', $value)->first();

                $deskripsi_log = 'di-ack oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $d_lhk, $deskripsi_log);
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di ack.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes() {
        echo is_dir('uploads\LHK\NEKROPSI\273286_42');
    }
}
