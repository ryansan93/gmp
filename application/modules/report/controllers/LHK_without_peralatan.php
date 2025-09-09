<?php defined('BASEPATH') OR exit('No direct script access allowed');

class LHK extends Public_Controller {

    private $pathView = 'report/lhk/';
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
    public function index()
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->load->library('Mobile_Detect');
            $detect = new Mobile_Detect();

            $this->add_external_js(
                array(
                    "assets/select2/js/select2.min.js",
                    'assets/report/lhk/js/lhk.js'
                )
            );
            $this->add_external_css(
                array(
                    "assets/select2/css/select2.min.css",
                    'assets/report/lhk/css/lhk.css'
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
            $content['data_mitra'] = $mitra;

            $data['title_menu'] = 'Laporan Harian Kandang';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
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

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                m.nomor,
                m.nama,
                w.kode as unit
            from kandang k
            right join
                (
                    select 
                        w1.id,
                        REPLACE(REPLACE(w1.nama, 'Kota ', ''), 'Kab ', '') as nama,
                        w1.kode
                    from wilayah w1
                    -- right join
                    --     (select max(id) as id, kode from wilayah group by kode) w2
                    --     on
                    --         w1.id = w2.id
                ) w
                on
                    k.unit = w.id
            right join
                (
                    select mm1.* from mitra_mapping mm1
                    right join
                        (select max(id) as id, nim from mitra_mapping group by nim) mm2
                        on
                            mm1.id = mm2.id
                ) mm
                on
                    k.mitra_mapping = mm.id
            right join
                mitra m
                on
                    m.id = mm.mitra
            where
                w.kode in ('".implode("', '", $kode_unit)."') and
                m.mstatus = 1
            group by
                m.nomor,
                m.nama,
                w.kode
            order by
                w.kode asc,
                m.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            $data = $d_conf;
        }

        return $data;
    }

    public function get_noreg()
    {
        $nomor_mitra = $this->input->post('params');

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.noreg,
                REPLACE(cast(cast(data.real_tgl_docin as date) as varchar(10)), '-', '/') as real_tgl_docin,
                REPLACE(cast(cast(data.tgl_docin as date) as varchar(10)), '-', '/') as tgl_docin,
                data.kandang
            from
            (
                select
                    rs.noreg,
                    case
                        when td.datang is not null then
                            td.datang
                        else
                            rs.tgl_docin
                    end as real_tgl_docin,
                    rs.tgl_docin as tgl_docin,
                    'KD - '+cast(cast(k.kandang as int) as varchar(2)) as kandang
                from rdim_submit rs
                right join
                    kandang k
                    on
                        rs.kandang = k.id
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
                left join
                    order_doc od
                    on
                        od.noreg = rs.noreg
                left join
                    (
                        select td1.* from terima_doc td1
                        right join
                            (select max(id) as id, no_order from terima_doc group by no_order) td2
                            on
                                td1.id = td2.id
                    ) td
                    on
                        td.no_order = od.no_order
                where
                    m.nomor = '".$nomor_mitra."' and
                    m.mstatus = 1 and
                    rs.noreg is not null
                group by
                    rs.noreg,
                    td.datang,
                    rs.tgl_docin,
                    k.kandang
            ) data
            group by
                data.noreg,
                data.real_tgl_docin,
                data.tgl_docin,
                data.kandang
            order by
                data.real_tgl_docin desc
        ";

        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        // $m_mm = new \Model\Storage\MitraMapping_model();
        // $d_mm = $m_mm->select('nim')->where('nomor', $nomor_mitra)->get()->toArray();

        // $m_rs = new \Model\Storage\RdimSubmit_model();
        // $d_rs = $m_rs->whereIn('nim', $d_mm)->get();

        // $_data = array();
        // if ( $d_rs->count() > 0 ) {
        //     $d_rs = $d_rs->toArray();
        //     foreach ($d_rs as $k_rs => $v_rs) {
        //         $m_od = new \Model\Storage\OrderDoc_model();
        //         $d_od = $m_od->where('noreg', $v_rs['noreg'])->first();

        //         $tgl_docin = substr($v_rs['tgl_docin'], 0, 10);
        //         if ( !empty($d_od) ) {
        //             $m_td = new \Model\Storage\TerimaDoc_model();
        //             $d_td = $m_td->where('no_order', $d_od->no_order)->first();

        //             if ( !empty($d_td) ) {
        //                 $tgl_docin = substr($d_td->datang, 0, 10);
        //             }
        //         }

        //         $kandang = (int) substr($v_rs['noreg'], -1);

        //         $key = str_replace('-', '', $tgl_docin).' - '.substr($v_rs['noreg'], -1);
        //         $_data[ $key ] = array(
        //             'noreg' => $v_rs['noreg'],
        //             'real_tgl_docin' => $tgl_docin,
        //             'tgl_docin' => strtoupper(tglIndonesia($tgl_docin, '-', ' ')),
        //             'kandang' => 'KD - '.$kandang
        //         );
        //     }
        // }

        // $data = array();
        // if ( !empty( $_data ) ) {
        //     ksort($_data);

        //     foreach ($_data as $k_data => $v_data) {
        //         $data[] = $v_data;
        //     }
        // }

        $this->result['content'] = $data;

        display_json( $this->result );
    }

    public function get_lists()
    {
        $akses = hakAkses($this->url);

        $params = $this->input->post('params');

        $noreg = $params['noreg'];

        $data = $this->mapping_data($noreg);

        $content['akses'] = $akses;
        $content['data'] = $data;
        $html = $this->load->view('report/lhk/list', $content, true);

        $this->result['status'] = 1;
        $this->result['content'] = $html;

        display_json($this->result);
    }

    public function mapping_data($noreg)
    {
        $data = array();

        $m_lhk = new \Model\Storage\Lhk_model();
        $d_lhk = $m_lhk->where('noreg', $noreg)->with(['lhk_sekat', 'lhk_nekropsi', 'lhk_solusi', 'foto_sisa_pakan', 'foto_ekor_mati'])->get();

        if ( $d_lhk->count() > 0 ) {
            $d_lhk = $d_lhk->toArray();

            $m_rs = new \Model\Storage\RdimSubmit_model();
            $d_rs = $m_rs->where('noreg', $noreg)->first();

            $sql_mp = "
                select mp.lat_long as lat_long_mitra from rdim_submit rs
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
					(
						select mp1.* from mitra_posisi mp1
						right join
							( select max(id) as id, nomor, kandang from mitra_posisi group by nomor, kandang ) mp2
							on
								mp1.id = mp2.id
					) mp
					on
						mp.nomor = mm.nomor and
                        mp.kandang = cast(SUBSTRING(rs.noreg, 10, 2) as int)
                where
                    rs.noreg = '".$noreg."'
            ";
            $d_mp = $m_rs->hydrateRaw( $sql_mp );
            $lat_long_mitra = null;
            if ( $d_mp->count() > 0 ) {
                $lat_long_mitra = $d_mp->toArray()[0]['lat_long_mitra'];
            }

            $pakai_pakan = 0;
            $populasi = $d_rs->populasi;
            $total_ekor = $populasi;
            
            $tgl_lhk = null;
            foreach ($d_lhk as $k_lhk => $v_lhk) {
                $tot_kirim_pakan = 0;
                
                $pakai_pakan = $v_lhk['pakai_pakan'];
                $total_ekor -= $v_lhk['ekor_mati'];

                $kirim_pakan = 0;

                // $deplesi = abs((($populasi - $total_ekor) / $populasi) * 100);
                $deplesi = abs(($v_lhk['ekor_mati'] / $populasi) * 100);
                $kons = $pakai_pakan * 50;

                $m_kp = new \Model\Storage\KirimPakan_model();

                $d_kp = null;
                if ( !empty($tgl_lhk) ) {
                    $d_kp = $m_kp->where('tujuan', $noreg)->where('tgl_kirim', '<=', $v_lhk['tanggal'])->get();
                } else {
                    $d_kp = $m_kp->where('tujuan', $noreg)->whereBetween('tgl_kirim', [next_date($tgl_lhk), $v_lhk['tanggal']])->get();
                }
                $tgl_lhk = $v_lhk['tanggal'];

                if ( $d_kp->count() > 0 ) {
                    $d_kp = $d_kp->toArray();

                    foreach ($d_kp as $k_kp => $v_kp) {
                        $m_tp = new \Model\Storage\TerimaPakan_model();
                        $d_tp = $m_tp->where('id_kirim_pakan', $v_kp['id'])->with(['detail'])->first();

                        if ( $d_tp ) {
                            $d_tp = $d_tp->toArray();

                            foreach ($d_tp['detail'] as $k_tpd => $v_tpd) {
                                $kirim_pakan += $v_tpd['jumlah'];
                            }
                        }
                    }
                }

                $tot_kirim_pakan += $kirim_pakan;

                $key = $v_lhk['noreg'].' - '.$v_lhk['umur'];
                $data[ $key ] = array(
                    'id' => $v_lhk['id'],
                    'umur' => $v_lhk['umur'],
                    'tgl_lhk' => $v_lhk['tanggal'],
                    'kons' => $kons,
                    'adg' => $v_lhk['adg'],
                    'deplesi' => $deplesi,
                    'bb' => $v_lhk['bb'],
                    'fcr' => $v_lhk['fcr'],
                    'ip' => $v_lhk['ip'],
                    'mati' => $v_lhk['ekor_mati'],
                    'foto_sisa_pakan' => $v_lhk['foto_sisa_pakan'],
                    'foto_ekor_mati' => $v_lhk['foto_ekor_mati'],
                    'kirim_pakan' => $kirim_pakan / 50,
                    'sisa_pakan' => ($tot_kirim_pakan - ($v_lhk['pakai_pakan'] * 50)) / 50,
                    'pakai_pakan' => $pakai_pakan,
                    'keterangan' => $v_lhk['keterangan'],
                    'posisi' => $v_lhk['lat_long'],
                    'lat_long_mitra' => $lat_long_mitra
                );
            }
        }

        return $data;
    }

    public function preview_file_attachment()
    {
        $judul = $this->input->get('judul');
        $url = $this->input->get('params');

        $content['judul'] = $judul;
        $content['url'] = $url;
        $html = $this->load->view($this->pathView . 'preview_file_attachment', $content, TRUE);

        echo $html;
    }

    public function nekropsi()
    {
        $id = $this->input->get('id');

        $m_lhk_nekropsi = new \Model\Storage\LhkNekropsi_model();
        $d_lhk_nekropsi = $m_lhk_nekropsi->where('id_header', $id)->with(['d_nekropsi', 'foto_nekropsi'])->get();

        $content['data'] = ($d_lhk_nekropsi->count() > 0) ? $d_lhk_nekropsi->toArray() : null;
        $html = $this->load->view($this->pathView . 'nekropsi', $content, TRUE);

        echo $html;
    }

    public function solusi()
    {
        $id = $this->input->get('id');

        $m_lhk_solusi = new \Model\Storage\LhkSolusi_model();
        $d_lhk_solusi = $m_lhk_solusi->where('id_header', $id)->with(['d_solusi'])->get();

        $content['data'] = ($d_lhk_solusi->count() > 0) ? $d_lhk_solusi->toArray() : null;
        $html = $this->load->view($this->pathView . 'solusi', $content, TRUE);

        echo $html;
    }

    public function sekat()
    {
        $id = $this->input->get('id');

        $m_lhk_sekat = new \Model\Storage\LhkSekat_model();
        $d_lhk_sekat = $m_lhk_sekat->where('id_header', $id)->get();

        $content['data'] = ($d_lhk_sekat->count() > 0) ? $d_lhk_sekat->toArray() : null;
        $html = $this->load->view($this->pathView . 'sekat', $content, TRUE);

        echo $html;
    }

    public function msort($array, $key, $sort_flags = SORT_REGULAR) {
        if (is_array($array) && count($array) > 0) {
            if (!empty($key)) {
                $mapping = array();
                foreach ($array as $k => $v) {
                    $sort_key = '';
                    if (!is_array($key)) {
                        $sort_key = $v[$key];
                    } else {
                        // @TODO This should be fixed, now it will be sorted as string
                        foreach ($key as $key_key) {
                            $sort_key .= $v[$key_key];
                        }
                        $sort_flags = SORT_STRING;
                    }
                    $mapping[$k] = $sort_key;
                }
                asort($mapping, $sort_flags);
                $sorted = array();
                foreach ($mapping as $k => $v) {
                    $sorted[] = $array[$k];
                }
                return $sorted;
            }
        }
        return $array;
    }

    public function formAck($params)
    {
        $params = json_decode(exDecrypt($params), true);
            
        $nik = $params['nik'];
        $status = $params['status'];
        $url_akses = $params['url_akses'];

        $akses = hakAkses($url_akses);
        if ( $akses['a_ack'] == 1 ) {
            $this->load->library('Mobile_Detect');
            $detect = new Mobile_Detect();

            $this->add_external_js(
                array(
                    'assets/report/lhk/js/lhk.js'
                )
            );
            $this->add_external_css(
                array(
                    'assets/report/lhk/css/lhk.css'
                )
            );
            $data = $this->includes;

            $isMobile = false;
            if ( $detect->isMobile() ) {
                $isMobile = true;
            }
            
            $m_lhk = new \Model\Storage\Lhk_model();
            $data_lhk = $m_lhk->getDataAck($nik, $status);

            $content['akses'] = $akses;
            $content['isMobile'] = $isMobile;
            $content['data'] = $data_lhk;

            $data['title_menu'] = 'ACK Laporan Harian Kandang';
            $data['view'] = $this->load->view($this->pathView . 'formAck', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }
}