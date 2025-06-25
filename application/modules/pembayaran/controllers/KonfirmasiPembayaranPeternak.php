<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KonfirmasiPembayaranPeternak extends Public_Controller
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
                "assets/compress-image/js/compress-image.js",
                'assets/pembayaran/konfirmasi_pembayaran_peternak/js/konfirmasi-pembayaran-peternak.js'
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/pembayaran/konfirmasi_pembayaran_peternak/css/konfirmasi-pembayaran-peternak.css'
            ));

            $data = $this->includes;

            $data['title_menu'] = 'Konfirmasi Pembayaran Peternak';

            $mitra = null;
            $perusahaan = $this->get_perusahaan();

            $content['add_form'] = $this->add_form($mitra, $perusahaan);
            $content['riwayat'] = $this->riwayat($mitra, $perusahaan);

            $content['akses'] = $this->hakAkses;
            $data['view'] = $this->load->view('pembayaran/konfirmasi_pembayaran_peternak/index', $content, true);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function load_form()
    {
        $params = $this->input->get('params');
        $edit = $this->input->get('edit');

        $id = $params['id'];

        $content = array();
        $html = "url not found";
        
        if ( !empty($id) && $edit != 'edit' ) {
            // NOTE: view data BASTTB (ajax)
            $html = $this->detail_form( $id );
        } else if ( !empty($id) && $edit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $perusahaan = $this->get_perusahaan();
            $html = $this->edit_form($id, $perusahaan);
        }else{
            $perusahaan = $this->get_perusahaan();
            $html = $this->add_form(null, $perusahaan);
        }

        echo $html;
    }

    public function get_lists()
    {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $sql_where = '';

        // $m_perusahaan = new \Model\Storage\Perusahaan_model();
        // foreach ($params['perusahaan'] as $k => $val) {
        //     $d_perusahaan = null;
        //     if ( $val != 'all' ) {
        //         $d_perusahaan = $m_perusahaan->where('kode', $val)->get();
        //     } else {
        //         $d_perusahaan = $m_perusahaan->get();
        //     }

        //     if ( !empty($d_perusahaan) ) {
        //         $d_perusahaan = $d_perusahaan->toArray();

        //         foreach ($d_perusahaan as $k_perusahaan => $v_perusahaan) {
        //             $kode_perusahaan[] = $v_perusahaan['kode'];
        //         }
        //     }
        // }

        // $d_kpp = $m_kpp->whereBetween('tgl_bayar', [$start_date, $end_date])
        //                ->where('mitra', $kode_mitra)
        //                ->whereIn('perusahaan', $kode_perusahaan)
        //                ->with(['d_mitra', 'd_perusahaan'])->orderBy('tgl_bayar', 'desc')->get();

        $kode_mitra = null;
        $kode_perusahaan = null;

        if ( $params['mitra'][0] != 'all' ) {
            $kode_mitra = $params['mitra'];

            $sql_where .= "and kpp.mitra in ('".implode("', '", $kode_mitra)."')";
        }

        if ( $params['perusahaan'][0] != 'all' ) {
            $kode_perusahaan = $params['perusahaan'];

            $sql_where .= "and kpp.perusahaan in ('".implode("', '", $kode_perusahaan)."')";
        }

        $m_kpp = new \Model\Storage\KonfirmasiPembayaranPeternak_model();
        $sql = "
            select kpp.*, m.nama as nama_mitra, p.perusahaan as nama_perusahaan from konfirmasi_pembayaran_peternak kpp
            right join
                (
                    select m1.* from mitra m1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) m2
                        on
                            m2.id = m1.id
                ) m
                on
                    m.nomor = kpp.mitra
            right join
                (
                    select p1.* from perusahaan p1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) p2
                        on
                            p2.id = p1.id
                ) p
                on
                    p.kode = kpp.perusahaan
            where
                kpp.tgl_bayar between '".$start_date."' and '".$end_date."'
                ".$sql_where."
            order by
                kpp.tgl_bayar desc
        ";

        $d_kpp = $m_kpp->hydrateRaw( $sql );

        if ( $d_kpp->count() > 0 ) {
            $d_kpp = $d_kpp->toArray();
        }

        $content['data'] = $d_kpp;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_peternak/list_riwayat', $content, true);

        echo $html;
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

    public function get_perusahaan()
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

    public function get_mitra()
    {
        $params = $this->input->post('params');

        $m_duser = new \Model\Storage\DetUser_model();
        $d_duser = $m_duser->where('id_user', $this->userid)->first();

        $m_karyawan = new \Model\Storage\Karyawan_model();
        $d_karyawan = $m_karyawan->where('nama', 'like', strtolower(trim($d_duser->nama_detuser)).'%')->first();

        $kode_unit = array();
        $kode_unit_all = null;
        if ( !empty( $params['kode_unit'] ) ) {
            foreach ($params['kode_unit'] as $k_ku => $v_ku) {
                if ( stristr($v_ku, 'all') !== FALSE ) {
                    $kode_unit_all = 'all';

                    break;
                } else {
                    array_push($kode_unit, $v_ku);
                }
            }
        }

        if ( !empty($kode_unit_all) ) {
            $m_wil = new \Model\Storage\Wilayah_model();
            $sql = "
                select kode from wilayah group by kode
            ";
            $d_wil = $m_wil->hydrateRaw($sql);

            if ( $d_wil ) {
                $d_wil = $d_wil->toArray();

                foreach ($d_wil as $key => $value) {
                    if ( !empty($value['kode']) ) {
                        array_push($kode_unit, $value['kode']);
                    }
                }
            }
        }

        $m_mitra = new \Model\Storage\Mitra_model();
        $sql = "
            select 
                mtr.nama, 
                m2.nomor,
                m2.kode
            from 
                mitra mtr
            right join
                (
                    select 
                        max(m.id) as id,
                        m.nomor,
                        w.kode 
                    from kandang k 
                    right join
                        mitra_mapping mm 
                        on
                            k.mitra_mapping = mm.id
                    right join
                        mitra m 
                        on
                            mm.mitra = m.id
                    right join
                        wilayah w 
                        on
                            k.unit = w.id 
                    where
                        m.nomor is not null and
                        w.kode is not null and
                        m.mstatus = 1
                    group by
                        m.nomor,
                        w.kode
                ) m2
                on
                    mtr.id = m2.id
            where
                m2.kode in ('".implode("', '", $kode_unit)."') and
                mtr.mstatus = 1
            group by
                mtr.nama, 
                m2.nomor,
                m2.kode
        ";
        $d_mitra = $m_mitra->hydrateRaw( $sql );

        $_data = null;
        if ( $d_mitra->count() > 0 ) {
            $d_mitra = $d_mitra->toArray();

            foreach ($d_mitra as $k_mitra => $v_mitra) {
                $key = $v_mitra['nama'].' | '.$v_mitra['nomor'];

                $_data[ $key ] = array(
                    'nomor' => $v_mitra['nomor'],
                    'nama' => $v_mitra['nama'],
                    'unit' => $v_mitra['kode']
                );
            }

            ksort($_data);
        }

        $data = array();
        if ( !empty($_data) ) {
            foreach ($_data as $key => $value) {
                array_push($data, $value);
            }
        }

        $this->result['content'] = $data;

        display_json( $this->result );
    }

    public function get_data_rhpp()
    {
        $params = $this->input->post('params');

        $nomor_mitra = $params['nomor'];
        $start_date = $params['start_date'].' 00:00:00.000';
        $end_date = $params['end_date'].' 23:59:59.999';

        $sql_where = '';
        if ( $nomor_mitra[0] != 'all' ) {
            $kode_mitra = $nomor_mitra;

            $sql_where .= "and mtr.nomor in ('".implode("', '", $kode_mitra)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select k.id from kandang k
            right join
                mitra_mapping mm
                on
                    k.mitra_mapping = mm.id
            right join
                (select id, nomor from mitra) mtr
                on
                    mm.mitra = mtr.id
            where
                k.id is not null
                ".$sql_where."
        ";
        $d_kdg = $m_conf->hydrateRaw( $sql );

        $id_kdg = array();
        if ( $d_kdg->count() > 0 ) {
            foreach ($d_kdg as $key => $value) {
                array_push($id_kdg, $value['id']);
            }
        }

        // $m_mm = new \Model\Storage\MitraMapping_model();
        // $d_mm = $m_mm->whereIn('nomor', $nomor_mitra)->get();

        // $id_kdg = array();
        // if ( $d_mm->count() > 0 ) {
        //     $d_mm = $d_mm->toArray();
        //     foreach ($d_mm as $k_mm => $v_mm) {
        //         $m_kdg = new \Model\Storage\Kandang_model();
        //         $d_kdg = $m_kdg->where('mitra_mapping', $v_mm['id'])->get();

        //         if ( $d_kdg->count() > 0 ) {
        //             $d_kdg = $d_kdg->toArray();
        //             foreach ($d_kdg as $k_kdg => $v_kdg) {
        //                 array_push($id_kdg, $v_kdg['id']);
        //             }
        //         }
        //     }
        // }

        // $m_td = new \Model\Storage\TerimaDoc_model();
        // $d_td = $m_td->select('no_order')->distinct('no_order')->whereBetween('datang', [$start_date, $end_date])->get();

        // $data = array();
        // if ( $d_td->count() > 0 ) {
        //     $d_td = $d_td->toArray();

        //     $m_od = new \Model\Storage\OrderDoc_model();
        //     $d_od = $m_od->select('noreg')->distinct('noreg')->whereIn('no_order', $d_td)->get();

        //     if ( $d_od->count() > 0 ) {
        //         $d_od = $d_od->toArray();

        $data = array();
        // $m_ts = new \Model\Storage\TutupSiklus_model();
        // $d_ts = $m_ts->select('noreg')->whereBetween('tgl_tutup', [$start_date, $end_date])->get();

        $sql_mtr = '';
        if ( $nomor_mitra[0] != 'all' ) {
            $kode_mitra = $nomor_mitra;

            $sql_mtr .= "and mm.nomor in ('".implode("', '", $kode_mitra)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                ts.noreg 
            from 
                (
                    select ts1.* from tutup_siklus ts1
                    right join
                        (select max(id) as id, noreg from tutup_siklus group by noreg) ts2
                        on
                            ts1.id = ts2.id
                ) ts
            left join
                (
                    select mm1.* from mitra_mapping mm1
                    right join
                        (select max(id) as id, nim from mitra_mapping group by nim) mm2
                        on
                            mm1.id = mm2.id
                ) mm
                on
                    mm.nim = SUBSTRING(ts.noreg, 0, 8)
            where
                ts.tgl_tutup between '".substr($start_date, 0, 10)."' and '".substr($end_date, 0, 10)."'
                ".$sql_mtr."
            group by
                ts.noreg
        ";
        $d_ts = $m_conf->hydrateRaw( $sql );

        if ( $d_ts->count() > 0 ) {
            $d_ts = $d_ts->toArray();

            $m_rs = new \Model\Storage\RdimSubmit_model();
            $d_rs = $m_rs->whereIn('noreg', $d_ts)->get();

            if ( $d_rs->count() > 0 ) {
                $d_rs = $d_rs->toArray();

                foreach ($d_rs as $k_rs => $v_rs) {
                    $m_rgn = new \Model\Storage\RhppGroupNoreg_model();
                    $d_rgn = $m_rgn->select('id_header')->where('noreg', $v_rs['noreg'])->get();

                    if ( $d_rgn->count() > 0 ) {
                        $d_rgn = $d_rgn->toArray();

                        $m_rg = new \Model\Storage\RhppGroup_model();
                        $d_rg = $m_rg->whereIn('id', $d_rgn)->where('jenis', 'like', '%rhpp_plasma%')->with(['list_noreg'])->first();

                        if ( !empty($d_rg) && !isset($data[$d_rg->id]) ) {
                            $tgl_docin = '';
                            $tgl_docin_real = '';
                            $noreg = '';
                            $kandang = '';
                            $populasi = '';
                            $populasi_real = '';

                            $jml_noreg = 1;
                            foreach ($d_rg->list_noreg as $k_ln => $v_ln) {
                                $tgl_docin .= tglIndonesia($v_ln->tgl_docin, '-', ' ');
                                $tgl_docin_real .= $v_ln->tgl_docin;
                                $noreg .= $v_ln->noreg;
                                $kandang .= $v_ln->kandang;
                                $populasi .= angkaRibuan($v_ln->populasi);
                                $populasi_real .= $v_ln->populasi;
                                if ( count($d_rg->list_noreg) > $jml_noreg ) {
                                    $tgl_docin .= '<br>';
                                    $tgl_docin_real .= '<br>';
                                    $noreg .= '<br>';
                                    $kandang .= '<br>';
                                    $populasi .= '<br>';
                                    $populasi_real .= '<br>';
                                }

                                $jml_noreg++;
                            }

                            $m_conf = new \Model\Storage\Conf();
                            $sql = "
                                select id_header, sum(nominal) as nominal
                                from rhpp_group_piutang
                                where
                                    id_header = ".$d_rg->id."
                                group by
                                    id_header
                            ";
                            $d_conf = $m_conf->hydrateRaw( $sql );

                            $nominal_piutang = 0;
                            if ( $d_conf->count() > 0 ) {
                                $nominal_piutang = $d_conf->toArray()[0]['nominal'];
                            }

                            $data[$d_rg->id] = array(
                                'jenis' => 'rhpp group',
                                'tgl_docin' => $tgl_docin,
                                'tgl_docin_real' => $tgl_docin_real,
                                'noreg' => $noreg,
                                'kandang' => $kandang,
                                'populasi' => $populasi,
                                'populasi_real' => $populasi_real,
                                'invoice' => $d_rg->invoice,
                                'total' => ($d_rg->pdpt_peternak_belum_pajak - (($d_rg->prs_potongan_pajak / 100) * $d_rg->pdpt_peternak_belum_pajak)) - $nominal_piutang
                            );
                        }
                    } else {
                        $m_r = new \Model\Storage\Rhpp_model();
                        $d_r = $m_r->where('noreg', $v_rs['noreg'])->where('jenis', 'rhpp_plasma')->first();

                        if ( !empty($d_r) ) {
                            $m_conf = new \Model\Storage\Conf();
                            $sql = "
                                select id_header, sum(nominal) as nominal
                                from rhpp_piutang
                                where
                                    id_header = ".$d_r->id."
                                group by
                                    id_header
                            ";
                            $d_conf = $m_conf->hydrateRaw( $sql );

                            $nominal_piutang = 0;
                            if ( $d_conf->count() > 0 ) {
                                $nominal_piutang = $d_conf->toArray()[0]['nominal'];
                            }

                            $data[$d_r->id] = array(
                                'jenis' => 'rhpp',
                                'tgl_docin' => tglIndonesia($d_r->tgl_docin, '-', ' '),
                                'tgl_docin_real' => $d_r->tgl_docin,
                                'noreg' => $d_r->noreg,
                                'kandang' => $d_r->kandang,
                                'populasi' => angkaRibuan($d_r->populasi),
                                'populasi_real' => $d_r->populasi,
                                'invoice' => $d_r->invoice,
                                'total' => ($d_r->pdpt_peternak_belum_pajak - (($d_r->prs_potongan_pajak / 100) * $d_r->pdpt_peternak_belum_pajak)) - $nominal_piutang
                            );
                        }
                    }
                }
            }
        }
        //     }
        // }
        
        $content['data'] = $data;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_peternak/list_rhpp', $content, true);

        $this->result['html'] = $html;

        display_json( $this->result );
    }

    public function riwayat($mitra, $perusahaan)
    {
        $content['unit'] = $this->get_unit();
        $content['mitra'] = $mitra;
        $content['perusahaan'] = $perusahaan;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_peternak/riwayat', $content, true);

        return $html;
    }

    public function add_form($mitra, $perusahaan)
    {
        $content['unit'] = $this->get_unit();
        $content['mitra'] = $mitra;
        $content['perusahaan'] = $perusahaan;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_peternak/add_form', $content, true);

        return $html;
    }

    public function detail_form($id)
    {
        $m_kpp = new \Model\Storage\KonfirmasiPembayaranPeternak_model();
        $d_kpp = $m_kpp->where('id', $id)->with(['d_mitra', 'd_perusahaan', 'detail', 'd_realisasi'])->first();

        $data = null;
        if ( $d_kpp ) {
            $d_kpp = $d_kpp->toArray();

            $data = $d_kpp;
        }

        $content['akses'] = $this->hakAkses;
        $content['data'] = $data;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_peternak/detail_form', $content, true);

        return $html;
    }

    public function edit_form($id, $perusahaan)
    {
        $m_kpp = new \Model\Storage\KonfirmasiPembayaranPeternak_model();
        $d_kpp = $m_kpp->where('id', $id)->with(['d_perusahaan', 'detail'])->first()->toArray();

        $start_date = null;
        $end_date = null;

        $total = 0;

        $detail = null;
        foreach ($d_kpp['detail'] as $k_det => $v_det) {
            if ( $v_det['jenis'] == 'RHPP GROUP' ) {
                $tgl_docin = '';
                $tgl_docin_real = '';
                $noreg = '';
                $kandang = '';
                $populasi = '';
                $populasi_real = '';

                $jml_noreg = 1;
                foreach ($v_det['detail2'] as $k_det2 => $v_det2) {
                    if ( empty($start_date) && empty($end_date) ) {
                        $start_date = $v_det2['tgl_docin'];
                        $end_date = $v_det2['tgl_docin'];
                    } else {
                        if ( $start_date > $v_det2['tgl_docin'] ) {
                            $start_date = $v_det2['tgl_docin'];
                        }

                        if ( $end_date < $v_det2['tgl_docin'] ) {
                            $end_date = $v_det2['tgl_docin'];
                        }
                    }

                    $tgl_docin .= tglIndonesia($v_det2['tgl_docin'], '-', ' ');
                    $tgl_docin_real .= $v_det2['tgl_docin'];
                    $noreg .= $v_det2['noreg'];
                    $kandang .= $v_det2['kandang'];
                    $populasi .= angkaRibuan($v_det2['populasi']);
                    $populasi_real .= $v_det2['populasi'];
                    if ( count($v_det['detail2']) > $jml_noreg ) {
                        $tgl_docin .= '<br>';
                        $tgl_docin_real .= '<br>';
                        $noreg .= '<br>';
                        $kandang .= '<br>';
                        $populasi .= '<br>';
                        $populasi_real .= '<br>';
                    }

                    $jml_noreg++;
                }

                $detail[$v_det['id_trans']] = array(
                    'jenis' => 'rhpp group',
                    'tgl_docin' => $tgl_docin,
                    'tgl_docin_real' => $tgl_docin_real,
                    'noreg' => $noreg,
                    'kandang' => $kandang,
                    'populasi' => $populasi,
                    'populasi_real' => $populasi_real,
                    'total' => $v_det['sub_total']
                );

                $total += $v_det['sub_total'];
            } else {
                if ( empty($start_date) && empty($end_date) ) {
                    $start_date = $v_det['detail2'][0]['tgl_docin'];
                    $end_date = $v_det['detail2'][0]['tgl_docin'];
                } else {
                    if ( $start_date > $v_det['detail2'][0]['tgl_docin'] ) {
                        $start_date = $v_det['detail2'][0]['tgl_docin'];
                    }

                    if ( $end_date < $v_det['detail2'][0]['tgl_docin'] ) {
                        $end_date = $v_det['detail2'][0]['tgl_docin'];
                    }
                }

                $detail[$v_det['id_trans']] = array(
                    'jenis' => 'rhpp',
                    'tgl_docin' => tglIndonesia($v_det['detail2'][0]['tgl_docin'], '-', ' '),
                    'tgl_docin_real' => $v_det['detail2'][0]['tgl_docin'],
                    'noreg' => $v_det['detail2'][0]['noreg'],
                    'kandang' => $v_det['detail2'][0]['kandang'],
                    'populasi' => angkaRibuan($v_det['detail2'][0]['populasi']),
                    'populasi_real' => $v_det['detail2'][0]['populasi'],
                    'total' => $v_det['sub_total']
                );

                $total += $v_det['sub_total'];
            }
        }

        $m_mitra = new \Model\Storage\Mitra_model();
        $d_mitra = $m_mitra->where('nomor', $d_kpp['mitra'])->orderBy('version', 'desc')->first()->toArray();

        $m_mm = new \Model\Storage\MitraMapping_model();
        $d_mm = $m_mm->where('mitra', $d_mitra['id'])->orderBy('id', 'desc')->first()->toArray();

        $m_kdg = new \Model\Storage\Kandang_model();
        $d_kdg = $m_kdg->where('mitra_mapping', $d_mm['id'])->with(['d_unit'])->orderBy('id', 'desc')->first()->toArray();

        $data = array(
            'id' => $d_kpp['id'],
            'start_date' => $start_date,
            'end_date' => $end_date,
            'unit' => $d_kdg['d_unit']['kode'],
            'mitra' => $d_kpp['mitra'],
            'perusahaan' => $d_kpp['perusahaan'],
            'total' => $total,
            'detail' => $detail
        );

        $content['data'] = $data;
        $content['unit'] = $this->get_unit();
        $content['perusahaan'] = $perusahaan;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_peternak/edit_form', $content, true);

        return $html;
    }

    public function konfirmasi_pembayaran()
    {
        $params = $this->input->get('params');

        $nomor = null;
        $rekening = null;
        $tgl_bayar = null;
        $lampiran = null;
        if ( isset($params['id']) ) {
            $m_kpp = new \Model\Storage\KonfirmasiPembayaranPeternak_model();
            $d_kpp = $m_kpp->where('id', $params['id'])->first();

            $nomor = $d_kpp->nomor;
            $rekening = $d_kpp->rekening;
            $tgl_bayar = $d_kpp->tgl_bayar;
            $lampiran = $d_kpp->lampiran;
        }

        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $d_perusahaan = $m_perusahaan->where('kode', $params['perusahaan'])->orderBy('version', 'desc')->first();

        $m_mitra = new \Model\Storage\Mitra_model();
        $d_mitra = $m_mitra->where('nomor', $params['mitra'])->orderBy('version', 'desc')->first();

        $rekening = ($d_mitra) ? $d_mitra->rekening_pemilik : null;
        $rekening_nomor = ($d_mitra) ? $d_mitra->rekening_nomor : null;
        $rekening_bank = ($d_mitra) ? $d_mitra->bank : null;

        $first_date = null;
        $last_date = null;

        $total = 0;
        foreach ($params['detail'] as $k_det => $v_det) {
            $total += $v_det['sub_total'];
            foreach ($v_det['detail2'] as $k_det2 => $v_det2) {
                if ( empty($first_date) ) {
                    $first_date = $v_det2['tgl_docin'];
                }
                if ( empty($first_date) ) {
                    $last_date = $v_det2['tgl_docin'];
                }

                if ( $v_det2['tgl_docin'] < $first_date ) {
                    $first_date = $v_det2['tgl_docin'];
                }

                if ( $v_det2['tgl_docin'] > $last_date ) {
                    $last_date = $v_det2['tgl_docin'];
                }
            }
        }

        $data = array(
            'id' => isset($params['id']) ? $params['id'] : null,
            'nomor' => $nomor,
            'rekening' => $rekening,
            'tgl_bayar' => $tgl_bayar,
            'rekening_nomor' => $rekening_nomor,
            'rekening_bank' => $rekening_bank,
            'total' => $total,
            'first_date' => $first_date,
            'last_date' => $last_date,
            'perusahaan' => $d_perusahaan->perusahaan,
            'no_perusahaan' => $d_perusahaan->kode,
            'mitra' => ($d_mitra) ? $d_mitra->nama : null,
            'no_mitra' => ($d_mitra) ? $d_mitra->nomor : null,
            'lampiran' => $lampiran
        );

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_peternak/konfirmasi_pembayaran', $content, true);

        echo $html;
    }

    public function save()
    {
        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];
        $mappingFiles = !empty($files) ? mappingFiles($files) : null;

        try {
            $path_name  = null;
            $moved = null;

            foreach ($mappingFiles as $k_mf => $v_mf) {
                $moved = uploadFile($v_mf);
            }
            $isMoved = $moved['status'];
            if ($isMoved) {
                $path_name = $moved['path'];

                $id = null;
                foreach ($params['detail'] as $k_det => $v_det) {
                    $m_kpp = new \Model\Storage\KonfirmasiPembayaranPeternak_model();
                    $nomor = $m_kpp->getNextNomor();

                    $no_mitra = null;
                    if ( $v_det['tipe_rhpp'] == 'RHPP' ) {
                        $m_rhpp = new \Model\Storage\Rhpp_model();
                        $d_rhpp = $m_rhpp->where('id', $v_det['id_trans'])->orderBy('id', 'desc')->first();

                        $m_mm = new \Model\Storage\MitraMapping_model();
                        $d_mm = $m_mm->where('nim', substr($d_rhpp->noreg, 0, 7))->orderBy('id', 'desc')->first();

                        $no_mitra = $d_mm->nomor;
                    } else {
                        $m_rg = new \Model\Storage\RhppGroup_model();
                        $d_rg = $m_rg->where('id', $v_det['id_trans'])->orderBy('id', 'desc')->first();

                        $m_rgh = new \Model\Storage\RhppGroupHeader_model();
                        $d_rgh = $m_rgh->where('id', $d_rg->id_header)->orderBy('id', 'desc')->first();

                        $no_mitra = $d_rgh->nomor;
                    }

                    $m_mitra = new \Model\Storage\Mitra_model();
                    $d_mitra = $m_mitra->where('nomor', $no_mitra)->orderBy('version', 'desc')->first();

                    $rekening_nomor = ($d_mitra) ? $d_mitra->rekening_nomor : null;

                    $m_kpp->nomor = $nomor;
                    $m_kpp->tgl_bayar = $params['tgl_bayar'];
                    $m_kpp->periode = trim($params['periode_docin']);
                    $m_kpp->perusahaan = $params['perusahaan'];
                    $m_kpp->mitra = $no_mitra;
                    $m_kpp->rekening = $rekening_nomor;
                    $m_kpp->total = $v_det['sub_total'];
                    // $m_kpp->rekening = $params['rekening'];
                    // $m_kpp->total = $params['total'];
                    $m_kpp->lampiran = $path_name;
                    $m_kpp->invoice = $v_det['invoice'];
                    $m_kpp->save();

                    $id = $m_kpp->id;
                    
                    $m_kppd = new \Model\Storage\KonfirmasiPembayaranPeternakDet_model();
                    $m_kppd->id_header = $id;
                    $m_kppd->id_trans = $v_det['id_trans'];
                    $m_kppd->jenis = $v_det['tipe_rhpp'];
                    $m_kppd->sub_total = $v_det['sub_total'];
                    $m_kppd->save();

                    $id_det = $m_kppd->id;
                    foreach ($v_det['detail2'] as $k_det2 => $v_det2) {
                        $m_kppd2 = new \Model\Storage\KonfirmasiPembayaranPeternakDet2_model();
                        $m_kppd2->id_header = $id_det;
                        $m_kppd2->tgl_docin = $v_det2['tgl_docin'];
                        $m_kppd2->noreg = $v_det2['noreg'];
                        $m_kppd2->kandang = $v_det2['kandang'];
                        $m_kppd2->populasi = $v_det2['populasi'];
                        $m_kppd2->save();
                    }

                    $d_kpp = $m_kpp->where('id', $id)->first();
    
                    $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/save', $d_kpp, $deskripsi_log);
                }

                $this->result['status'] = 1;
                $this->result['content'] = array('id' => $id);
                $this->result['message'] = 'Data berhasil di simpan.';
            } else {
                $this->result['message'] = 'Upload lampiran gagal, harap di coba kembali.';
            }
            
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit()
    {
        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];
        $mappingFiles = !empty($files) ? mappingFiles($files) : null;

        try {
            $m_kpp = new \Model\Storage\KonfirmasiPembayaranPeternak_model();
            $d_kpp = $m_kpp->where('id', $params['id'])->first();

            $path_name  = $d_kpp->lampiran;
            $moved = null;
            if ( !empty($mappingFiles) ) {
                foreach ($mappingFiles as $k_mf => $v_mf) {
                    $moved = uploadFile($v_mf);
                }
            }
            $isMoved = $moved['status'];
            if ($isMoved) {
                $path_name = $moved['path'];
            }

            $m_kpp->where('id', $params['id'])->update(
                array(
                    'tgl_bayar' => $params['tgl_bayar'],
                    'periode' => trim($params['periode_docin']),
                    'perusahaan' => $params['perusahaan'],
                    'mitra' => $params['mitra'],
                    'rekening' => $params['rekening'],
                    'total' => $params['total'],
                    'lampiran' => $path_name
                )
            );

            $m_kppd2 = new \Model\Storage\KonfirmasiPembayaranPeternakDet2_model();

            $m_kppd = new \Model\Storage\KonfirmasiPembayaranPeternakDet_model();
            $d_kppd = $m_kppd->select('id')->where('id_header', $params['id'])->get()->toArray();

            $m_kppd2 = new \Model\Storage\KonfirmasiPembayaranPeternakDet2_model();
            $m_kppd2->whereIn('id_header', $d_kppd)->delete();
            $m_kppd->where('id_header', $params['id'])->delete();

            $id = $params['id'];
            foreach ($params['detail'] as $k_det => $v_det) {
                $m_kppd = new \Model\Storage\KonfirmasiPembayaranPeternakDet_model();
                $m_kppd->id_header = $id;
                $m_kppd->id_trans = $v_det['id_trans'];
                $m_kppd->jenis = $v_det['tipe_rhpp'];
                $m_kppd->sub_total = $v_det['sub_total'];
                $m_kppd->save();

                $id_det = $m_kppd->id;
                foreach ($v_det['detail2'] as $k_det2 => $v_det2) {
                    $m_kppd2 = new \Model\Storage\KonfirmasiPembayaranPeternakDet2_model();
                    $m_kppd2->id_header = $id_det;
                    $m_kppd2->tgl_docin = $v_det2['tgl_docin'];
                    $m_kppd2->noreg = $v_det2['noreg'];
                    $m_kppd2->kandang = $v_det2['kandang'];
                    $m_kppd2->populasi = $v_det2['populasi'];
                    $m_kppd2->save();
                }
            }

            $d_kpp = $m_kpp->where('id', $id)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_kpp, $deskripsi_log);

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
            $m_kpp = new \Model\Storage\KonfirmasiPembayaranPeternak_model();
            $d_kpp = $m_kpp->where('id', $params['id'])->first();

            $m_kppd2 = new \Model\Storage\KonfirmasiPembayaranPeternakDet2_model();

            $m_kppd = new \Model\Storage\KonfirmasiPembayaranPeternakDet_model();
            $d_kppd = $m_kppd->select('id')->where('id_header', $params['id'])->get()->toArray();

            $m_kppd2 = new \Model\Storage\KonfirmasiPembayaranPeternakDet2_model();
            $m_kppd2->whereIn('id_header', $d_kppd)->delete();
            $m_kppd->where('id_header', $params['id'])->delete();
            $m_kpp->where('id', $params['id'])->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_kpp, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes()
    {
        // $selisih_umur = abs(selisihTanggal('2022-01-12', '2021-12-11'));

        // cetak_r( $selisih_umur );

        $m_kpp = new \Model\Storage\KonfirmasiPembayaranPeternak_model();
        $no_invoice = $m_kpp->getNextNoInvoice('INV/RHPP/MLG');

        cetak_r( $no_invoice );
    }
}