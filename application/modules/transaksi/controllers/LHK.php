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
        $d_lhk = $m_lhk->where('id', $id)->with(['lhk_sekat', 'lhk_nekropsi', 'lhk_solusi', 'lhk_peralatan', 'foto_sisa_pakan', 'foto_ekor_mati'])->orderBy('id', 'desc')->first()->toArray();

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('noreg', $d_lhk['noreg'])->with(['mitra'])->first()->toArray();

        $mitra = $d_rs['mitra']['d_mitra']['nama'];

        $status = 1;

        $m_lhk = new \Model\Storage\Lhk_model();
        $d_lhk_cek = $m_lhk->where('noreg', $d_lhk['noreg'])->where('umur', '>', $d_lhk['umur'])->first();
        if ( $d_lhk_cek ) {
            $status = 2;
        }

        $m_ts = new \Model\Storage\TutupSiklus_model();
        $d_ts = $m_ts->where('noreg', $d_lhk['noreg'])->first();
        if ( $d_ts ) {
            $status = 2;
        }

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
            'lhk_peralatan' => $d_lhk['lhk_peralatan'],
            'keterangan' => $d_lhk['keterangan'],
            'tanggal' => $d_lhk['tanggal'],
            'lat' => !empty($d_lhk['lat_long']) ? trim(explode(',', $d_lhk['lat_long'])[0]) : null,
            'long' => !empty($d_lhk['lat_long']) ? trim(explode(',', $d_lhk['lat_long'])[1]) : null,
            'status' => $status
            // ,'status' => $d_lhk['status']
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
        $d_lhk = $m_lhk->where('id', $id)->with(['lhk_sekat', 'lhk_nekropsi', 'lhk_solusi', 'lhk_peralatan', 'foto_sisa_pakan', 'foto_ekor_mati'])->orderBy('id', 'desc')->first()->toArray();

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
            'lhk_peralatan' => $d_lhk['lhk_peralatan'],
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

        // $m_duser = new \Model\Storage\DetUser_model();
        // $d_duser = $m_duser->where('id_user', $this->userid)->first();

        // $m_karyawan = new \Model\Storage\Karyawan_model();
        // $d_karyawan = $m_karyawan->where('nama', 'like', strtolower(trim($d_duser->nama_detuser)).'%')->orderBy('id', 'desc')->first();

        // $kode_unit = array();
        // $kode_unit_all = null;
        // if ( $d_karyawan ) {
        //     $m_ukaryawan = new \Model\Storage\UnitKaryawan_model();
        //     $d_ukaryawan = $m_ukaryawan->where('id_karyawan', $d_karyawan->id)->get();

        //     if ( $d_ukaryawan->count() > 0 ) {
        //         $d_ukaryawan = $d_ukaryawan->toArray();

        //         foreach ($d_ukaryawan as $k_ukaryawan => $v_ukaryawan) {
        //             if ( stristr($v_ukaryawan['unit'], 'all') === false ) {
        //                 $m_wil = new \Model\Storage\Wilayah_model();
        //                 $d_wil = $m_wil->where('id', $v_ukaryawan['unit'])->first();

        //                 array_push($kode_unit, $d_wil->kode);
        //                 // $kode_unit = $d_wil->kode;
        //             } else {
        //                 $m_wil = new \Model\Storage\Wilayah_model();
        //                 $sql = "
        //                     select kode from wilayah where kode is not null group by kode
        //                 ";
        //                 $d_wil = $m_wil->hydrateRaw($sql);

        //                 if ( $d_wil->count() > 0 ) {
        //                     $d_wil = $d_wil->toArray();

        //                     foreach ($d_wil as $key => $value) {
        //                         array_push($kode_unit, $value['kode']);
        //                     }
        //                 }
        //             }
        //         }
        //     } else {
        //         $m_wil = new \Model\Storage\Wilayah_model();
        //         $sql = "
        //             select kode from wilayah where kode is not null group by kode
        //         ";
        //         $d_wil = $m_wil->hydrateRaw($sql);

        //         if ( $d_wil->count() > 0 ) {
        //             $d_wil = $d_wil->toArray();

        //             foreach ($d_wil as $key => $value) {
        //                 array_push($kode_unit, $value['kode']);
        //             }
        //         }
        //     }
        // } else {
        //     $m_wil = new \Model\Storage\Wilayah_model();
        //     $sql = "
        //         select kode from wilayah where kode is not null group by kode
        //     ";
        //     $d_wil = $m_wil->hydrateRaw($sql);

        //     if ( $d_wil->count() > 0 ) {
        //         $d_wil = $d_wil->toArray();

        //         foreach ($d_wil as $key => $value) {
        //             array_push($kode_unit, $value['kode']);
        //         }
        //     }
        // }

        $m_wil = new \Model\Storage\Wilayah_model();
        $d_wil = $m_wil->getDataUnit(1, $this->user);

        $kode_unit = array();
        foreach ($d_wil as $key => $value) {
            $kode_unit[] = $value['kode'];
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
                left join
                    rdim_submit rs 
                    on
                        rs.noreg = od.noreg 
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

        $sql_cek_ts = null;

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

    public function cekDataPrev()
    {
        $params = $this->input->post('params');

        try {
            $id = (isset($params['id']) && !empty($params['id'])) ? $params['id'] : null;
            $umur = $params['umur'];
            $noreg = $params['noreg'];
            $pakai_pakan = $params['pakai_pakan'];
            $ekor_mati = $params['ekor_mati'];
            $tanggal = $params['tanggal'];

            $status = 1;
            $message = null;

            $sql_id = null;
            if ( !empty($id) ) {
                $sql_id = " and id <> '".$id."'";
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select top 1 * from lhk where noreg = '".$noreg."' and umur = '".$umur."' ".$sql_id." order by umur desc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray()[0];

                $status = 0;
                $message = 'Data LHK umur '.$umur.' sudah ada, cek kembali data yang anda masukkan.';
            } else {
                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select top 1 * from lhk where noreg = '".$noreg."' and umur < '".$umur."' order by umur desc
                ";
                $d_conf_prev = $m_conf->hydrateRaw( $sql );

                if ( $d_conf_prev->count() > 0 ) {
                    $d_conf_prev = $d_conf_prev->toArray()[0];

                    if ( $d_conf_prev['pakai_pakan'] >= $pakai_pakan || $d_conf_prev['ekor_mati'] > $ekor_mati ) {
                        $status = 0;

                        $message = '<span style="color: red;">Data LHK yang anda masukkan tidak sesuai !!!</span>';
                        $message .= '<br>';
                        $message .= '<b><u>PAKAI PAKAN</u></b><br>';
                        $message .= 'UMUR '.$d_conf_prev['umur'].' = '.$d_conf_prev['pakai_pakan'].' Zak<br>';
                        $message .= 'UMUR '.$umur.' = '.$pakai_pakan.' Zak<br>';
                        $message .= '<br>';
                        $message .= '<b><u>EKOR MATI</u></b><br>';
                        $message .= 'UMUR '.$d_conf_prev['umur'].' = '.$d_conf_prev['ekor_mati'].' Ekor<br>';
                        $message .= 'UMUR '.$umur.' = '.$ekor_mati.' Ekor<br>';
                        $message .= '<br>';
                        $message .= 'Data yang di masukkan adalah data akumulasi, cek kembali data yang anda masukkan.';
                    }
                }
            }

            $this->result['status'] = $status;
            $this->result['message'] = $message;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            // cetak_r( $params, 1 );

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

            if ( count($params['data_peralatan']) > 0 ) {
                $d_dp = $params['data_peralatan'];

                $m_lp = new \Model\Storage\LhkPeralatan_model();
                $m_lp->id_header = $id_lhk;
                $m_lp->umur = $d_dp['umur'];
                $m_lp->waktu = $d_dp['waktu'];
                $m_lp->flok_lantai = $d_dp['flok_lantai'];
                $m_lp->tipe_controller = $d_dp['tipe_controller'];
                $m_lp->kelembapan1 = $d_dp['kelembapan1'];
                $m_lp->kelembapan2 = $d_dp['kelembapan2'];
                $m_lp->suhu_current1 = $d_dp['suhu_current1'];
                $m_lp->suhu_current2 = $d_dp['suhu_current2'];
                $m_lp->suhu_experience1 = $d_dp['suhu_experience1'];
                $m_lp->suhu_experience2 = $d_dp['suhu_experience2'];
                $m_lp->air_speed_depan_inlet1 = $d_dp['air_speed_depan_inlet1'];
                $m_lp->air_speed_depan_inlet2 = $d_dp['air_speed_depan_inlet2'];
                $m_lp->kerataan_air_speed1 = $d_dp['kerataan_air_speed1'];
                $m_lp->kerataan_air_speed2 = $d_dp['kerataan_air_speed2'];
                $m_lp->ukuran_kipas1 = $d_dp['ukuran_kipas1'];
                $m_lp->ukuran_kipas2 = $d_dp['ukuran_kipas2'];
                $m_lp->jumlah_kipas1 = $d_dp['jumlah_kipas1'];
                $m_lp->jumlah_kipas2 = $d_dp['jumlah_kipas2'];
                $m_lp->jumlah_kipas_on1 = $d_dp['jumlah_kipas_on1'];
                $m_lp->jumlah_kipas_on2 = $d_dp['jumlah_kipas_on2'];
                $m_lp->jumlah_kipas_off1 = $d_dp['jumlah_kipas_off1'];
                $m_lp->jumlah_kipas_off2 = $d_dp['jumlah_kipas_off2'];
                $m_lp->waktu_kipas_on1 = $d_dp['waktu_kipas_on1'];
                $m_lp->waktu_kipas_on2 = $d_dp['waktu_kipas_on2'];
                $m_lp->waktu_kipas_off1 = $d_dp['waktu_kipas_off1'];
                $m_lp->waktu_kipas_off2 = $d_dp['waktu_kipas_off2'];
                $m_lp->cooling_pad_status1 = $d_dp['cooling_pad_status1'];
                $m_lp->cooling_pad_status2 = $d_dp['cooling_pad_status2'];
                $m_lp->save();
            }

            $d_lhk = $m_lhk->where('id', $id_lhk)->orderBy('umur', 'desc')->first();

            // $conf = new \Model\Storage\Conf();
            // $sql = "EXEC hitung_stok_siklus 'doc', 'lhk', '".$d_lhk->id."', '".$d_lhk->tanggal."', 1, null, null";
            // $d_conf = $conf->hydrateRaw($sql);

            // $conf = new \Model\Storage\Conf();
            // $sql = "EXEC hitung_stok_siklus 'pakan', 'lhk', '".$d_lhk->id."', '".$d_lhk->tanggal."', 1, null, null";
            // $d_conf = $conf->hydrateRaw($sql);

            // $id = $d_lhk->id;
            // $id_old = null;

            // Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, 1);

            $deskripsi_log = 'di-simpan oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_lhk, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
            // $this->result['content'] = array('id' => $id_lhk);
            $this->result['content'] = array(
                'id' => $id_lhk,
                'tanggal' => $params['tanggal'],
                'noreg' => $params['noreg'],
                'status' => 2,
                'message' => 'Data berhasil di simpan.'
            );
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
            // cetak_r( $params, 1 );

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

            if ( count($params['data_peralatan']) > 0 ) {
                $d_dp = $params['data_peralatan'];

                $m_lp = new \Model\Storage\LhkPeralatan_model();
                $m_lp->where('id_header', $id_lhk)->update(
                    array(
                        'umur' => $d_dp['umur'],
                        'waktu' => $d_dp['waktu'],
                        'flok_lantai' => $d_dp['flok_lantai'],
                        'tipe_controller' => $d_dp['tipe_controller'],
                        'kelembapan1' => $d_dp['kelembapan1'],
                        'kelembapan2' => $d_dp['kelembapan2'],
                        'suhu_current1' => $d_dp['suhu_current1'],
                        'suhu_current2' => $d_dp['suhu_current2'],
                        'suhu_experience1' => $d_dp['suhu_experience1'],
                        'suhu_experience2' => $d_dp['suhu_experience2'],
                        'air_speed_depan_inlet1' => $d_dp['air_speed_depan_inlet1'],
                        'air_speed_depan_inlet2' => $d_dp['air_speed_depan_inlet2'],
                        'kerataan_air_speed1' => $d_dp['kerataan_air_speed1'],
                        'kerataan_air_speed2' => $d_dp['kerataan_air_speed2'],
                        'ukuran_kipas1' => $d_dp['ukuran_kipas1'],
                        'ukuran_kipas2' => $d_dp['ukuran_kipas2'],
                        'jumlah_kipas1' => $d_dp['jumlah_kipas1'],
                        'jumlah_kipas2' => $d_dp['jumlah_kipas2'],
                        'jumlah_kipas_on1' => $d_dp['jumlah_kipas_on1'],
                        'jumlah_kipas_on2' => $d_dp['jumlah_kipas_on2'],
                        'jumlah_kipas_off1' => $d_dp['jumlah_kipas_off1'],
                        'jumlah_kipas_off2' => $d_dp['jumlah_kipas_off2'],
                        'waktu_kipas_on1' => $d_dp['waktu_kipas_on1'],
                        'waktu_kipas_on2' => $d_dp['waktu_kipas_on2'],
                        'waktu_kipas_off1' => $d_dp['waktu_kipas_off1'],
                        'waktu_kipas_off2' => $d_dp['waktu_kipas_off2'],
                        'cooling_pad_status1' => $d_dp['cooling_pad_status1'],
                        'cooling_pad_status2' => $d_dp['cooling_pad_status2']
                    )
                );
            }

            $d_lhk = $m_lhk->where('id', $id_lhk)->orderBy('umur', 'desc')->first();

            // $conf = new \Model\Storage\Conf();
            // $sql = "EXEC hitung_stok_siklus 'doc', 'lhk', '".$d_lhk->id."', '".$d_lhk->tanggal."', 2, null, null";
            // $d_conf = $conf->hydrateRaw($sql);

            // $conf = new \Model\Storage\Conf();
            // $sql = "EXEC hitung_stok_siklus 'pakan', 'lhk', '".$d_lhk->id."', '".$d_lhk->tanggal."', 2, null, null";
            // $d_conf = $conf->hydrateRaw($sql);

            // $id = $d_lhk->id;
            // $id_old = $d_lhk->id;

            // Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, 2);

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_lhk, $deskripsi_log);

            $this->result['status'] = 1;
            // $this->result['message'] = 'Data berhasil di update.';
            // $this->result['content'] = array('id' => $params['id']);
            $this->result['content'] = array(
                'id' => $params['id'],
                'tanggal' => $d_lhk_by_id->tanggal,
                'noreg' => $params['noreg'],
                'status' => 2,
                'message' => 'Data berhasil di update.'
            );
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

            $id_lhk = $id;

            // $conf = new \Model\Storage\Conf();
            // $sql = "EXEC hitung_stok_siklus 'doc', 'lhk', '".$d_lhk->id."', '".$d_lhk->tanggal."', 3, '".$d_lhk->noreg."', null";
            // $d_conf = $conf->hydrateRaw($sql);

            // $conf = new \Model\Storage\Conf();
            // $sql = "EXEC hitung_stok_siklus 'pakan', 'lhk', '".$d_lhk->id."', '".$d_lhk->tanggal."', 3, '".$d_lhk->noreg."', null";
            // $d_conf = $conf->hydrateRaw($sql);

            // $id_old = $d_lhk->id;

            // Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, 3);

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

            $m_lp = new \Model\Storage\LhkPeralatan_model();
            $m_lp->where('id_header', $id_lhk)->delete();

            $m_lhk->where('id', $id)->delete();

            $deskripsi_log = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_lhk, $deskripsi_log);

            $this->result['status'] = 1;
            // $this->result['message'] = 'Data berhasil di hapus.';
            $this->result['content'] = array(
                'id' => $id,
                'tanggal' => $d_lhk->tanggal,
                'noreg' => $d_lhk->noreg,
                'status' => 3,
                'message' => 'Data berhasil di hapus.'
            );
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function execHitStokDoc() {
        $params = $this->input->post('params');

        try {
            $id = $params['id'];
            $tanggal = $params['tanggal'];
            $noreg = $params['noreg'];
            $status = $params['status'];

            $conf = new \Model\Storage\Conf();
            $sql = "EXEC hitung_stok_siklus 'doc', 'lhk', '".$id."', '".$tanggal."', ".$status.", '".$noreg."', null";
            $d_conf = $conf->hydrateRaw($sql);

            $this->result['status'] = 1;
            $this->result['content'] = $params;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function execHitStokPakan() {
        $params = $this->input->post('params');

        try {
            $id = $params['id'];
            $tanggal = $params['tanggal'];
            $noreg = $params['noreg'];
            $status = $params['status'];

            $conf = new \Model\Storage\Conf();
            $sql = "EXEC hitung_stok_siklus 'pakan', 'lhk', '".$id."', '".$tanggal."', ".$status.", '".$noreg."', null";
            $d_conf = $conf->hydrateRaw($sql);

            $this->result['status'] = 1;
            $this->result['content'] = $params;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function execInsertJurnal() {
        $params = $this->input->post('params');

        try {
            $id = $params['id'];
            $id_old = $params['id'];
            $status = $params['status'];

            Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, $status);

            $this->result['status'] = 1;
            $this->result['content'] = $params;
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
                /* CEK KESESUAIAN PERALATAN */
                $m_lhk = new \Model\Storage\Lhk_model();
                $_d_lhk = $m_lhk->where('id', $value)->with(['lhk_peralatan'])->first();

                $status_peralatan = 2;
                if ( $_d_lhk ) {
                    $_d_lhk = $_d_lhk->toArray();

                    if ( !empty($_d_lhk['lhk_peralatan']) ) {
                        $d_lp = $_d_lhk['lhk_peralatan'];

                        $m_sb = new \Model\Storage\StandarBudidaya_model();
                        $d_sb = $m_sb->where('mulai', '<=', $_d_lhk['tanggal'])->orderBy('mulai', 'DESC')->orderBy('nomor', 'DESC')->first();

                        if ( $d_sb ) {
                            $m_dsb = new \Model\Storage\DetStandarBudidaya_model();
                            $d_dsb = $m_dsb->where('id_budidaya', $d_sb->id)->where('umur', $_d_lhk['umur'])->first();

                            if ( $d_dsb ) {
                                $d_dsb = $d_dsb->toArray();

                                if ( $d_lp['suhu_experience1'] <> $d_dsb['suhu_experience'] ) {
                                    $status_peralatan = 1;
                                }

                                if ( $d_lp['suhu_experience2'] <> $d_dsb['suhu_experience'] ) {
                                    $status_peralatan = 1;
                                }

                                if ( $d_lp['air_speed_depan_inlet1'] < $d_dsb['min_air_speed'] || $d_lp['air_speed_depan_inlet1'] > $d_dsb['max_air_speed'] ) {
                                    $status_peralatan = 1;
                                }

                                if ( $d_lp['air_speed_depan_inlet2'] < $d_dsb['min_air_speed'] || $d_lp['air_speed_depan_inlet2'] > $d_dsb['max_air_speed'] ) {
                                    $status_peralatan = 1;
                                }

                                if ( $d_lp['kerataan_air_speed1'] < $d_dsb['min_air_speed'] || $d_lp['kerataan_air_speed1'] > $d_dsb['max_air_speed'] ) {
                                    $status_peralatan = 1;
                                }

                                if ( $d_lp['kerataan_air_speed2'] < $d_dsb['min_air_speed'] || $d_lp['kerataan_air_speed2'] > $d_dsb['max_air_speed'] ) {
                                    $status_peralatan = 1;
                                }
                            }
                        }
                    }
                }

                $m_lhk = new \Model\Storage\Lhk_model();
                $m_lhk->where('id', $value)->update(
                    array(
                        'status_peralatan' => 2,
                        'kesesuaian_peralatan' => ($status_peralatan == 2) ? 1 : 0
                    )
                );
                /* END - CEK KESESUAIAN PERALATAN */

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

    public function ackPeralatan() {
        $params = $this->input->post('params');

        try {
            $id = $params['id'];

            $m_lhk = new \Model\Storage\Lhk_model();
            $m_lhk->where('id', $id)->update(
                array(
                    'status_peralatan' => getStatus('ack')
                )
            );

            $d_lhk = $m_lhk->where('id', $id)->first();

            $deskripsi_log = 'ack peralatan oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_lhk, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di ack.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function hitStokTanpaJurnal($noreg) {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select data.*, saj.path from 
            (
                select l.id, 'lhk' as tipe, l.noreg, l.tanggal from lhk l
    
                union all
    
                select tp.id, 'terima_pakan' as tipe, kp.tujuan as noreg, tp.tgl_terima as tanggal from terima_pakan tp
                left join
                    kirim_pakan kp
                    on
                        tp.id_kirim_pakan = kp.id

                union all
    
                select tp.id, 'terima_pakan' as tipe, kp.asal as noreg, tp.tgl_terima as tanggal from terima_pakan tp
                left join
                    kirim_pakan kp
                    on
                        tp.id_kirim_pakan = kp.id
            ) data
            left join
                (
                    select saj.*, '/'+df.path_detfitur as path from
                    (
                        select saj1.* from setting_automatic_jurnal saj1
                        right join
                            (select max(id) as id, tbl_name from setting_automatic_jurnal group by tbl_name) saj2
                            on
                                saj1.id = saj2.id
                    ) saj
                    left join
                        detail_fitur df 
                        on
                            saj.det_fitur_id = df.id_detfitur 
                ) saj
                on
                    saj.tbl_name = data.tipe
            where
                data.noreg = '".$noreg."'
            order by
                data.tanggal asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );
    
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            // cetak_r( $d_conf, 1 );

            foreach ($d_conf as $key => $value) {
                $id = $value['id'];
                $id_old = $value['id'];
                $tanggal = $value['tanggal'];
                $tipe = $value['tipe'];

                cetak_r( $value['noreg'].' -> '.$value['tanggal'].' -> '.$value['tipe'] );

                // $conf = new \Model\Storage\Conf();
                // $sql = "EXEC hitung_stok_siklus 'doc', 'lhk', '".$id."', '".$d_lhk->tanggal."', 2, null, null";
                // $d_conf = $conf->hydrateRaw($sql);
        
                $conf = new \Model\Storage\Conf();
                $sql = "EXEC hitung_stok_siklus 'pakan', '".$tipe."', '".$id."', '".$tanggal."', 2, null, null";
                $d_conf = $conf->hydrateRaw($sql);
            }
        }
    }

    public function jurnalTanpaStok($start_date = '2025-10-01', $noreg = null) {
        $sql_noreg = null;
        if ( !empty($noreg) ) {
            $sql_noreg = "where data.noreg = '".$noreg."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select data.*, saj.path from 
            (
                select l.id, 'lhk' as tipe, l.noreg from lhk l where l.tanggal >= '".$start_date."'
    
                union all
    
                select tp.id, 'terima_pakan' as tipe, kp.tujuan as noreg from terima_pakan tp
                left join
                    kirim_pakan kp
                    on
                        tp.id_kirim_pakan = kp.id
                where
                    -- kp.jenis_kirim = 'opkp' and
                    tp.tgl_terima >= '".$start_date."'
            ) data
            left join
                (
                    select saj.*, '/'+df.path_detfitur as path from
                    (
                        select saj1.* from setting_automatic_jurnal saj1
                        right join
                            (select max(id) as id, tbl_name from setting_automatic_jurnal group by tbl_name) saj2
                            on
                                saj1.id = saj2.id
                    ) saj
                    left join
                        detail_fitur df 
                        on
                            saj.det_fitur_id = df.id_detfitur 
                ) saj
                on
                    saj.tbl_name = data.tipe
            ".$sql_noreg."
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            $idx = 1;
            foreach ($d_conf as $key => $value) {
                $id = $value['id'];
                $id_old = $value['id'];
                $path = $value['path'];

                cetak_r( $idx.' - '.$id.' ('.$path.')' );

                Modules::run( 'base/InsertJurnal/exec', $path, $id, $id_old, 2);

                $idx++;
            }
        }
    }

    public function hitPakaiPakanByNoreg($sep, $noreg = null) {
        if ( $sep == 1 ) {
            $sql_noreg = null;
            if ( !empty($noreg) ) {
                $sql_noreg = "and l.noreg = '".$noreg."'";
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select l.* from 
                (select min(l.tanggal) as tanggal, l.noreg from lhk l where l.tanggal < '2025-10-01' ".$sql_noreg." group by l.noreg) data
                left join
                    lhk l
                    on
                        data.tanggal = l.tanggal and
                        data.noreg = l.noreg
                where
                    l.tanggal < '2025-10-01'
                order by
                    data.tanggal
            ";
            // cetak_r( $sql, 1 );
            $d_conf = $m_conf->hydrateRaw( $sql );
    
            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();
    
                foreach ($d_conf as $key => $value) {
                    $id = $value['id'];
                    $id_old = $value['id'];
                    $tanggal = $value['tanggal'];
    
                    // $conf = new \Model\Storage\Conf();
                    // $sql = "EXEC hitung_stok_siklus 'doc', 'lhk', '".$id."', '".$d_lhk->tanggal."', 2, null, null";
                    // $d_conf = $conf->hydrateRaw($sql);
            
                    $conf = new \Model\Storage\Conf();
                    $sql = "EXEC hitung_stok_siklus 'pakan', 'lhk', '".$id."', '".$tanggal."', 2, null, null";
                    $d_conf = $conf->hydrateRaw($sql);
            
                    // Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, 2);
                }
            }
        }

        if ( $sep == 0 ) {
            $sql_noreg = null;
            if ( !empty($noreg) ) {
                $sql_noreg = "and l.noreg = '".$noreg."'";
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select l.* from 
                (select min(l.tanggal) as tanggal, l.noreg from lhk l where l.tanggal >= '2025-10-01' ".$sql_noreg." group by l.noreg) data
                left join
                    lhk l
                    on
                        data.tanggal = l.tanggal and
                        data.noreg = l.noreg
                where
                    l.tanggal >= '2025-10-01'
                order by
                    data.tanggal
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
    
            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();
    
                foreach ($d_conf as $key => $value) {
                    $id = $value['id'];
                    $id_old = $value['id'];
                    $tanggal = $value['tanggal'];

                    // cetak_r( $value['id'] );
    
                    // $conf = new \Model\Storage\Conf();
                    // $sql = "EXEC hitung_stok_siklus 'doc', 'lhk', '".$id."', '".$d_lhk->tanggal."', 2, null, null";
                    // $d_conf = $conf->hydrateRaw($sql);
            
                    $conf = new \Model\Storage\Conf();
                    $sql = "EXEC hitung_stok_siklus 'pakan', 'lhk', '".$id."', '".$tanggal."', 2, null, null";
                    $d_conf = $conf->hydrateRaw($sql);
            
                    // Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, 2);
                }
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select l.* from lhk l where l.tanggal >= '2025-10-01' ".$sql_noreg."
                order by
                    l.tanggal
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();
    
                foreach ($d_conf as $key => $value) {
                    $id = $value['id'];
                    $id_old = $value['id'];
            
                    Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, 2);
                }
            }
        }
    }

    public function updateDataLHK() {
        $arr = array(
            array('25100570101', 1, 3, 57, 20),
array('25100570101', 3, 9, 51, 69),
array('25100570101', 6, 29, 31, 109),
array('25100570101', 9, 54, 106, 145),
array('25091180101', 3, 17, 143, 33),
array('25091180101', 5, 33, 287, 55),
array('25091180101', 7, 51, 269, 80),
array('25091180101', 10, 81, 239, 160),
array('25091180101', 12, 119, 201, 209),
array('25091180101', 14, 160, 320, 242),
array('25091180101', 17, 235, 245, 300),
array('25091180101', 20, 323, 317, 353),
array('25101170101', 2, 14, 146, 42),
array('25100590101', 2, 2, 18, 11),
array('25100590101', 4, 5, 15, 22),
array('25091170101', 2, 10, 60, 32),
array('25091170101', 4, 21, 49, 63),
array('25091170101', 6, 33, 37, 90),
array('25091170101', 10, 62, 108, 151),
array('25091170101', 12, 78, 92, 170),
array('25091170101', 14, 102, 68, 190),
array('25091170101', 18, 166, 104, 225),
array('25091610101', 2, 10, 150, 40),
array('25091610101', 4, 22, 138, 71),
array('25091610101', 6, 46, 114, 104),
array('25091610101', 9, 93, 227, 174),
array('25091610101', 12, 149, 331, 231),
array('25091160101', 2, 5, 35, 11),
array('25091160101', 4, 9, 31, 35),
array('25091160101', 6, 18, 122, 51),
array('25091160101', 8, 27, 113, 67),
array('25091160101', 11, 44, 96, 82),
array('25091160101', 13, 60, 80, 92),
array('25091160101', 15, 78, 122, 101),
array('25091160101', 18, 112, 88, 116),
array('25091160101', 20, 136, 64, 127),
array('25091160101', 22, 162, 98, 137),
array('25091600102', 1, 3, 37, 10),
array('25091600102', 3, 7, 33, 43),
array('25091600102', 6, 16, 24, 70),
array('25091600102', 8, 24, 76, 87),
array('25091600102', 10, 35, 65, 105),
array('25091600102', 13, 55, 45, 129),
array('25091600102', 15, 71, 29, 139),
array('25091600102', 17, 91, 69, 149),
array('25100580101', 2, 4, 36, 17),
array('25100580101', 4, 9, 31, 31),
array('25091620101', 3, 11, 49, 52),
array('25091620101', 5, 21, 39, 77),
array('25091620101', 7, 33, 127, 96),
array('25091620101', 10, 53, 107, 125),
array('25091620101', 13, 76, 184, 154),
array('25100560101', 3, 8, 32, 46),
array('25100560101', 5, 15, 25, 64),
array('25100560101', 7, 24, 116, 77),
array('25091630101', 2, 4, 16, 62),
array('25091630101', 5, 10, 10, 115),
array('25091630101', 7, 14, 6, 132),
array('25091630101', 9, 20, 40, 152),
array('25091630101', 12, 28, 32, 181),
array('25091630101', 15, 39, 21, 184),
        );

        foreach ($arr as $k_arr => $v_arr) {
            $m_lhk = new \Model\Storage\Lhk_model();
            $d_lhk = $m_lhk->where('noreg', $v_arr[0])->where('umur', $v_arr[1])->first();

            if ( $d_lhk ) {
                $m_lhk = new \Model\Storage\Lhk_model();
                $m_lhk->where('id', $d_lhk->id)->update(
                    array(
                        'pakai_pakan' => $v_arr[2],
                        'sisa_pakan' => $v_arr[3],
                        'ekor_mati' => $v_arr[4],
                    )
                );
            }
        }
    }

    public function tes() {
        //  cetak_r( $this->url );

        // $conf = new \Model\Storage\Conf();
        // $sql = "
        //     select tp.*, kp.no_order from terima_pakan tp
        //     left join
        //         kirim_pakan kp
        //         on
        //             tp.id_kirim_pakan = kp.id
        //     where
        //         kp.jenis_kirim = 'opkp' and
        //         tp.tgl_terima >= '2025-10-01'
        //     order by
        //         tp.tgl_terima asc,
        //         kp.no_order asc
        // ";
        // $d_conf = $conf->hydrateRaw($sql);

        // if ( $d_conf->count() > 0 ) {
        //     $d_conf = $d_conf->toArray();

        //     foreach ($d_conf as $key => $value) {
                $id = '6849';
                $id_old = '6849';
                $tanggal = '2025-11-12';
        
                $conf = new \Model\Storage\Conf();
                $sql = "EXEC hitung_stok_siklus 'pakan', 'lhk', '".$id."', '".$tanggal."', 2, null, null";
                $d_conf = $conf->hydrateRaw($sql);
        
                Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, 2);
        //     }
        // }
    }
}
