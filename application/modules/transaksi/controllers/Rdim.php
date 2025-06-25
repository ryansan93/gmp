<?php defined('BASEPATH') or exit('No direct script access allowed');

class Rdim extends Public_Controller
{
    private $pathView = 'transaksi/rdim/';
    private $status_rdimsubmit = [
        2 => 'Dibatalkan',
        1 => 'Aktif',
        0 => 'Tidak Aktif'
    ];
    private $url;

    public function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
    }

    public function index()
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/jquery/list.min.js",
                "assets/toastr/js/toastr.js",
                "assets/jquery/tupage-table/jquery.tupage.table.js",
                "assets/transaksi/rdim/js/rdim.js"

            ));

            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/toastr/css/toastr.css",
                "assets/jquery/tupage-table/jquery.tupage.table.css",
                "assets/transaksi/rdim/css/rdim.css"
            ));

            $data = $this->includes;

            $content['title_panel'] = 'Rencana DOC in Mingguan';
            $content['current_uri'] = $this->current_uri;
            $content['akses'] = $akses;

            // $content['rdim_data_perwakilan_mitra'] = $this->getDataPerwakilan();
            // $content['tim_panens'] = $this->getDataTimpanen();
            // $content['vaksin'] = $this->getVaksin();

            // // cetak_r($this->getDataPerwakilan());

            // $status = getStatus('approve');
            // $content['periodes'] = $this->getPeriodeRdim($status);

            $content['add_form'] = $this->add_form();

            $data['title_menu'] = 'Rencana Chick In Mingguan';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, true);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function load_form()
    {
        $id = $this->input->get('id');
        $resubmit = $this->input->get('resubmit');
        $html = '';

        if ( !empty($id) ) {
            if ( !empty($resubmit) ) {
                /* NOTE : untuk edit */
                $html = $this->edit_form($id, $resubmit);
            } else {
                /* NOTE : untuk view */
                $html = $this->view_form($id, $resubmit);
            }
        } else {
            /* NOTE : untuk add */
            $html = $this->add_form();
        }

        echo $html;
    }

    public function add_form()
    {
        $akses = hakAkses($this->url);

        $content['rdim_data_perwakilan_mitra'] = $this->getDataPerwakilan();

        // cetak_r($content['rdim_data_perwakilan_mitra'], 1);

        $status = getStatus('approve');
        $content['periodes'] = $this->getPeriodeRdim($status);
        // $content['tim_panens'] = $this->getDataTimpanen();
        $content['vaksin'] = $this->getVaksin();

        $content['akses'] = $akses;
        $content['data'] = null;
        $html = $this->load->view('transaksi/rdim/add_form', $content, true);
        
        return $html;
    }

    public function view_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        $m_rdim = new \Model\Storage\Rdim_model();
        $d_rdim = $m_rdim->with(['logs'])->where('id', $id)->first();
        $dataMapping = $this->rdimMapping($d_rdim);
        $content['data'] = $dataMapping;

        $content['akses'] = $akses;
        $html = $this->load->view('transaksi/rdim/view_form', $content, true);
        
        return $html;
    }

    public function edit_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        $m_rdim = new \Model\Storage\Rdim_model();
        $d_rdim = $m_rdim->with(['logs'])->where('id', $id)->first();

        $dataMapping = $this->rdimMapping($d_rdim);

        $content['data'] = $dataMapping;
        $content['rdim_data_perwakilan_mitra'] = $this->getDataPerwakilan();

        // $content['data'] = null;
        // $content['rdim_data_perwakilan_mitra'] = null;
        // $content['tim_panens'] = $this->getDataTimpanen();
        $content['vaksin'] = $this->getVaksin();
        $content['akses'] = $akses;

        $html = $this->load->view('transaksi/rdim/edit_form', $content, true);
        
        return $html;
    }

    public function getTabContents($value='')
    {
        $tab_contents = array(
            'riwayat_tab_content',
            'rencana_doc_in_mingguan',
            'pembatalan_doc_in',
        );

        $content['akses'] = hakAkses($this->url);
        foreach ($tab_contents as $tab_content) {

            if ($tab_content == 'rencana_doc_in_mingguan') {
                $content['rdim_data_perwakilan_mitra'] = $this->getDataPerwakilan();
                // $content['tim_panens'] = $this->getDataTimpanen();
            }else if ($tab_content == 'pembatalan_doc_in') {
                $status = getStatus('approve');
                $content['periodes'] = $this->getPeriodeRdim($status);
            }
            $view_contents[ $tab_content ] = $this->load->view($this->pathView . $tab_content, $content, true);
        }

        return $view_contents;
    }

    public function getPeriodeRdim($status)
    {
        $m_rdim = new \Model\Storage\Rdim_model();
        $d_rdim = $m_rdim->where('g_status', $status)->orderBy('id','DESC')->take(2)->get();
        return $d_rdim;
    } // end - getPeriodeRdim

    public function getDataTimpanen()
    {
        // $m_timpanen = new \Model\Storage\MsTimPanen_model();
        // $d_timpanen = $m_timpanen->active()->orderBy('nama_timpanen', 'ASC')->get();

        $m_perwakilan = new \Model\Storage\Wilayah_model();
        $d_perwakilan = $m_perwakilan->perwakilan()->with(['mitra_mapping', 'dPerwakilanMapping','unit'])->orderBy('nama', 'ASC')->get();

        $penimbang = array();
        foreach ($d_perwakilan as $perwakilan) {
            if ( !empty($perwakilan->mitra_mapping) ) {
                $units = array_map(function($item){
                    return $item['nama'];
                }, $perwakilan->unit->toArray());

                $id_unit = array_map(function($item){
                    return $item['id'];
                }, $perwakilan->unit->toArray());

                if ( !empty($id_unit) ) {
                    $m_uk = new \Model\Storage\UnitKaryawan_model();
                    $d_uk = $m_uk->select('id_karyawan')->distinct('id_karyawan')->whereIn('unit', $id_unit)->get()->toArray();

                    if ( !empty($d_uk) ) {
                        foreach ($d_uk as $k_uk => $v_uk) {
                            $m_k = new \Model\Storage\Karyawan_model();
                            $d_k = $m_k->where('id', $v_uk['id_karyawan'])->where('jabatan', 'penimbang')->first();

                            if ( !empty($d_k) ) {
                                array_push($penimbang, $d_k->toArray());
                            }
                        }
                    }
                }
            }
        }


        return $penimbang;
    } // end - getDataTimpanen

    public function getDataPerwakilan()
    {
        // $m_perwakilan = new \Model\Storage\Wilayah_model();
        // $d_perwakilan = $m_perwakilan->perwakilan()->with(['unit'])->orderBy('nama', 'ASC')->get();

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from wilayah w
            where 
                w.jenis = 'PW'
        ";
        $d_perwakilan = $m_conf->hydrateRaw( $sql );

        if ( !empty($d_perwakilan) && $d_perwakilan->count() > 0 ) {
            $d_perwakilan = $d_perwakilan->toArray();

            foreach ($d_perwakilan as $k_pwk => $v_pwk) {
                $formatPb = array();

                $sql = "
                    select * from wilayah w
                    where 
                        w.induk = '".$v_pwk['id']."'
                ";
                $d_unit = $m_conf->hydrateRaw( $sql );

                $units = null;
                $id_units = null;
                if ( !empty($d_unit) && $d_unit->count() > 0 ) {
                    $units = array_map(function($item){
                        return $item['nama'];
                    }, $d_unit->toArray());

                    $id_units = array_map(function($item){
                        return $item['id'];
                    }, $d_unit->toArray());
                }

                if ( !empty($id_units) ) {
                    $sql = "
                        select
                            max(_hbi.id) as id,
                            sk.nomor as nomor,
                            sk.mulai as tgl_berlaku,
                            pk.item_code+' ('+ltrim(rtrim(sk.item_pola))+') '+p.perusahaan as format,
                            pk.item_code+' ('+ltrim(rtrim(sk.item_pola))+')' as pola,
                            p.kode as perusahaan
                        from sapronak_kesepakatan sk
                        right join
                            (select max(id) as id, nomor from sapronak_kesepakatan group by nomor) sk1
                            on
                                sk.id = sk1.id
                        left join
                            pola_kerjasama pk
                            on
                                sk.pola = pk.id
                        left join
                            (
                                select p1.* from perusahaan p1
                                right join
                                    (select max(id) as id, kode from perusahaan group by kode) p2
                                    on
                                        p1.id = p2.id
                            ) p
                            on
                                sk.perusahaan = p.kode
                        left join
                            (
                                select 
                                    _pm.id,
                                    _pm.id_hbi,
                                    _pm.id_pwk,
                                    hbi.id_sk
                                from (
                                    select max(pm.id) as id, pm.id_hbi, pm.id_pwk
                                    from perwakilan_maping pm
                                    group by
                                        pm.id_hbi,
                                        pm.id_pwk
                                ) _pm
                                left join
                                    hitung_budidaya_item hbi
                                    on
                                        _pm.id_hbi = hbi.id
                                group by
                                    _pm.id,
                                    _pm.id_hbi,
                                    _pm.id_pwk,
                                    hbi.id_sk
                            ) _hbi
                            on
                                _hbi.id_sk = sk.id
                        where
                            _hbi.id_pwk = ".$v_pwk['id']."
                        group by
                            sk.nomor,
                            sk.mulai,
                            pk.item_code,
                            sk.item_pola,
                            p.perusahaan,
                            p.kode
                        order by 
                            sk.nomor asc
                    ";
                    $d_pm = $m_conf->hydrateRaw( $sql );

                    if ( !empty($d_pm) && $d_pm->count() > 0 ) {
                        $d_pm = $d_pm->toArray();

                        $formatPb = $d_pm;
                    }

                    $ppl = null;
                    $kanit = null;
                    $marketing = null;
                    $koordinator = null;

                    $sql = "
                        select
                            k.id,
                            k.level,
                            k.nik,
                            k.atasan,
                            UPPER(k.nama) as nama,
                            k.wilayah,
                            k.kordinator,
                            k.marketing,
                            k.jabatan,
                            k.status
                        from karyawan k
                        right join
                            unit_karyawan uk
                            on
                                uk.id_karyawan = k.id
                        where
                            (uk.unit in ('".implode("', '", $id_units)."') or uk.unit like '%all%') and
                            k.status = 1
                        group by
                            k.id,
                            k.level,
                            k.nik,
                            k.atasan,
                            k.nama,
                            k.wilayah,
                            k.kordinator,
                            k.marketing,
                            k.jabatan,
                            k.status
                    ";
                    $d_karyawan = $m_conf->hydrateRaw( $sql );

                    if ( $d_karyawan->count() > 0 ) {
                        $d_karyawan = $d_karyawan->toArray();
                        foreach ($d_karyawan as $k => $val) {
                            if ( $val['jabatan'] == 'ppl' || $val['jabatan'] == 'kepala unit' ) {
                                $ppl[] = $val;
                            }
                            if ( $val['jabatan'] == 'kepala unit' || $val['jabatan'] == 'koordinator' ) {
                                $kanit[] = $val;
                            }
                            if ( $val['jabatan'] == 'marketing' ) {
                                $marketing[] = $val;
                            }
                            if ( $val['jabatan'] == 'koordinator' || $val['jabatan'] == 'penanggung jawab' ) {
                                $koordinator[] = $val;
                            }
                        }
                    }

                    $data_perwakilan = array(
                        'id' => $v_pwk['id'],
                        'nama' => $v_pwk['nama'],
                        'formatPb' => $formatPb,
                        'kanit' => $kanit,
                        'ppl' => $ppl,
                        'marketing' => $marketing,
                        'koordinator' => $koordinator,
                        'units' => $units
                    );

                    $sql = "
                        select
                            m.id as mitra_id,
                            m.nomor,
                            UPPER(m.nama) as nama,
                            m.jenis,
                            mm.id as mapping_id,
                            mm.nim,
                            kdg.id as kdg_id,
                            'G-'+cast(kdg.grup as varchar(5)) as _group,
                            CASE
                                WHEN kdg.kandang < 10 THEN
                                    '0'+cast(kdg.kandang as varchar(5))
                                ELSE
                                    cast(kdg.kandang as varchar(5))
                            END as kdg_nomor,
                            kdg.tipe,
                            kdg.ekor_kapasitas as kapasitas,
                            ((bk.meter_panjang * bk.meter_lebar) * bk.jumlah_unit) as luas,
                            CASE
                                WHEN ( ((bk.meter_panjang * bk.meter_lebar) * bk.jumlah_unit) > 0 and kdg.ekor_kapasitas > 0 ) THEN
                                    kdg.ekor_kapasitas / ((bk.meter_panjang * bk.meter_lebar) * bk.jumlah_unit)
                            END as densitas,
                            kdg.alamat_jalan as alamat,
                            kec.nama as kecamatan,
                            kab_kota.nama as kabupaten,
                            w.kode as unit,
                            prs.kode_auto as kode_perusahaan
                        from kandang kdg 
                        right join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim, nomor from mitra_mapping group by nim, nomor) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                mm.id = kdg.mitra_mapping
                        right join 
                            mitra m
                            on
                                m.id = mm.mitra
                        left join
                            (
                                select kandang, sum(meter_panjang) as meter_panjang, sum(meter_lebar) as meter_lebar, sum(jumlah_unit) as jumlah_unit from bangunan_kandang group by kandang
                            ) bk
                            on
                                bk.kandang = kdg.id
                        left join
                            (
                                select * from lokasi where jenis = 'KC'
                            ) kec
                            on
                                kdg.alamat_kecamatan = kec.id
                        left join
                            (
                                select * from lokasi where jenis = 'KT' or jenis = 'KB'
                            ) kab_kota
                            on
                                kec.induk = kab_kota.id
                        left join
                            wilayah w
                            on
                                w.id = kdg.unit
                        left join
                            (
                                select prs1.* from perusahaan prs1
                                right join
                                    (select max(id) as id, kode from perusahaan group by kode) prs2
                                    on
                                        prs1.id = prs2.id
                            ) prs
                            on
                                m.perusahaan = prs.kode
                        where
                            mm.perwakilan = ".$v_pwk['id']." and
                            m.mstatus = 1
                        group by
                            m.id,
                            m.nomor,
                            m.nama,
                            m.jenis,
                            mm.id,
                            mm.nim,
                            kdg.id,
                            kdg.grup,
                            kdg.kandang,
                            kdg.tipe,
                            kdg.ekor_kapasitas,
                            bk.meter_panjang,
                            bk.meter_lebar,
                            bk.jumlah_unit,
                            bk.jumlah_unit,
                            kdg.ekor_kapasitas,
                            kdg.alamat_jalan,
                            kec.nama,
                            kab_kota.nama,
                            w.kode,
                            prs.kode_auto
                        order by
                            m.nama asc
                    ";
                    $data_mitra = $m_conf->hydrateRaw( $sql );

                    $key_mitra_old = null;
                    $idx_kdg = 0;
                    if ( $data_mitra->count() > 0 ) {
                        $data_mitra = $data_mitra->toArray();

                        foreach ($data_mitra as $k_mitra => $v_mitra) {
                            $key_kdg = $v_mitra['kdg_id'];
                            $key_mitra = $v_mitra['mitra_id'];

                            if ( $key_mitra_old == $key_mitra ) {
                                $idx_kdg++;
                            } else {
                                $idx_kdg = 0;
                            }

                            $key_mitra_old = $key_mitra;

                            $data_kandangs[ $key_mitra ]['kandangs'][$idx_kdg] = array(
                                'id' => $v_mitra['kdg_id'],
                                'nim' => $v_mitra['nim'],
                                '_group' => $v_mitra['_group'],
                                'nomor' => $v_mitra['kdg_nomor'],
                                'tipe' => $v_mitra['tipe'],
                                'kapasitas' => $v_mitra['kapasitas'],
                                'luas' => $v_mitra['luas'],
                                'densitas' => $v_mitra['densitas'],
                                'alamat' => $v_mitra['alamat'],
                                'kecamatan' => $v_mitra['kecamatan'],
                                'kabupaten' => $v_mitra['kabupaten'],
                                'unit' => $v_mitra['unit']
                            );

                            $data[ $v_pwk['id'] ]['child'][ $key_mitra ] = array(
                                'mitra_id' => $v_mitra['mitra_id'],
                                'nomor' => $v_mitra['nomor'],
                                'nama' => $v_mitra['nama'],
                                'jenis' => $v_mitra['jenis'],
                                'mapping_id' => $v_mitra['mapping_id'],
                                'nim' => $v_mitra['nim'],
                                'kode_perusahaan' => $v_mitra['kode_perusahaan'],
                                'kandangs' => $data_kandangs[ $key_mitra ]['kandangs']
                            );

                            // $sql = "
                            //     select 
                            //         k.id,
                            //         '".$v_mitra['nim']."' as nim,
                            //         'G-'+cast(k.grup as varchar(5)) as _group,
                            //         CASE
                            //             WHEN k.kandang < 10 THEN
                            //                 '0'+cast(k.kandang as varchar(5))
                            //             ELSE
                            //                 cast(k.kandang as varchar(5))
                            //         END as nomor,
                            //         k.tipe,
                            //         k.ekor_kapasitas as kapasitas,
                            //         ((bk.meter_panjang * bk.meter_lebar) * bk.jumlah_unit) as luas,
                            //         CASE
                            //             WHEN ( ((bk.meter_panjang * bk.meter_lebar) * bk.jumlah_unit) > 0 and k.ekor_kapasitas > 0 ) THEN
                            //                 k.ekor_kapasitas / ((bk.meter_panjang * bk.meter_lebar) * bk.jumlah_unit)
                            //         END as densitas,
                            //         k.alamat_jalan as alamat,
                            //         kec.nama as kecamatan,
                            //         kab_kota.nama as kabupaten,
                            //         w.kode as unit
                            //     from kandang k
                            //     left join
                            //         (
                            //             select kandang, sum(meter_panjang) as meter_panjang, sum(meter_lebar) as meter_lebar, sum(jumlah_unit) as jumlah_unit from bangunan_kandang group by kandang
                            //         ) bk
                            //         on
                            //             bk.kandang = k.id
                            //     left join
                            //         (
                            //             select * from lokasi where jenis = 'KC'
                            //         ) kec
                            //         on
                            //             k.alamat_kecamatan = kec.id
                            //     left join
                            //         (
                            //             select * from lokasi where jenis = 'KT' or jenis = 'KB'
                            //         ) kab_kota
                            //         on
                            //             kec.induk = kab_kota.id
                            //     left join
                            //         wilayah w
                            //         on
                            //             w.id = k.unit
                            //     where
                            //         k.mitra_mapping = ".$v_mitra['mapping_id']."
                            // ";
                            // $d_kdg = $m_conf->hydrateRaw( $sql );

                            // if ( $d_kdg->count() > 0 ) {
                            //     $d_kdg = $d_kdg->toArray();

                            //     $data_mitra[ $k_mitra ]['kandangs'] = $d_kdg;
                            // }
                        }
                    }

                    $data[ $v_pwk['id'] ]['parent'] = $data_perwakilan;
                    // $data[ $v_pwk['id'] ]['child'] = $data_mitra;
                }
            }
        }

        return $data;

        // $data = array();
        // foreach ($d_perwakilan as $perwakilan) {
        //     $m_mm = new \Model\Storage\MitraMapping_model();
        //     $d_mm = $m_mm->selectRaw('max(id) as id')->where('perwakilan', $perwakilan['id'])->groupBy('nomor')->get();
        //     if ( $d_mm->count() > 0 ) {
        //         $d_mm = $d_mm->toArray();

        //         $m_pm = new \Model\Storage\PerwakilanMaping_model();
        //         $d_pm = $m_pm->selectRaw('max(id) as id, id_hbi')->where('id_pwk', $perwakilan['id'])->groupBy('id_hbi')->get();

        //         $formatPb = array();
        //         if ( $d_pm->count() > 0 ) {
        //             $d_pm = $d_pm->toArray();

        //             foreach ($d_pm as $pm) {
        //                 $m_hbi = new \Model\Storage\HitungBudidayaItem_model();
        //                 $d_hbi = $m_hbi->where('id', $pm['id_hbi'])->first();
        //                 if ( !empty($d_hbi) ) {
        //                     $m_sk = new \Model\Storage\SapronakKesepakatan_model();
        //                     $d_sk = $m_sk->where('id', $d_hbi->id_sk)->with(['data_perusahaan', 'pola_kerjasama'])->first();

        //                     if ($d_sk) {
        //                         $perusahaan = json_decode(json_encode($d_sk->data_perusahaan), True);

        //                         $format = $d_sk->pola_kerjasama->item_code . ' (' .  trim($d_sk->item_pola) . ') ' . $perusahaan['perusahaan'];
        //                         $pola = $d_sk->pola_kerjasama->item_code . ' (' .  trim($d_sk->item_pola) . ')';

        //                         $formatPb[$d_sk->nomor] = array(
        //                             'id' => $pm['id'],
        //                             'tgl_berlaku' => substr($d_sk->mulai, 0, 10),
        //                             'format' => $format,
        //                             'pola' => $pola,
        //                             'perusahaan' => $perusahaan['kode']
        //                         );
        //                     }
        //                 }
        //             }
        //         }

        //         $units = array_map(function($item){
        //             return $item['nama'];
        //         }, $perwakilan->unit->toArray());

        //         $id_unit = array_map(function($item){
        //             return $item['id'];
        //         }, $perwakilan->unit->toArray());

        //         $m_uk = new \Model\Storage\UnitKaryawan_model();
        //         $d_uk = $m_uk->select('id_karyawan')->distinct('id_karyawan')->whereIn('unit', $id_unit)->get()->toArray();

        //         $ppl = array();
        //         if ( !empty($id_unit) ) {
        //             if ( !empty($d_uk) ) {
        //                 foreach ($d_uk as $k_uk => $v_uk) {
        //                     $m_k = new \Model\Storage\Karyawan_model();
        //                     $d_k = $m_k->where('id', $v_uk['id_karyawan'])->where('jabatan', 'ppl')->first();

        //                     if ( !empty($d_k) ) {
        //                         array_push($ppl, $d_k->toArray());
        //                     }
        //                 }
        //             }
        //         }

        //         $kanit = array();
        //         if ( !empty($id_unit) ) {
        //             // $m_uk = new \Model\Storage\UnitKaryawan_model();
        //             // $d_uk = $m_uk->select('id_karyawan')->distinct('id_karyawan')->whereIn('unit', $id_unit)->get()->toArray();

        //             if ( !empty($d_uk) ) {
        //                 foreach ($d_uk as $k_uk => $v_uk) {
        //                     $m_k = new \Model\Storage\Karyawan_model();
        //                     $d_k = $m_k->where('id', $v_uk['id_karyawan'])->where('jabatan', 'kepala unit')->first();

        //                     if ( !empty($d_k) ) {
        //                         array_push($kanit, $d_k->toArray());
        //                     }
        //                 }
        //             }
        //         }

        //         $marketing = array();
        //         if ( !empty($id_unit) ) {
        //             // $m_uk = new \Model\Storage\UnitKaryawan_model();
        //             // $d_uk = $m_uk->select('id_karyawan')->distinct('id_karyawan')->whereIn('unit', $id_unit)->get()->toArray();

        //             if ( !empty($d_uk) ) {
        //                 foreach ($d_uk as $k_uk => $v_uk) {
        //                     $m_k = new \Model\Storage\Karyawan_model();
        //                     $d_k = $m_k->where('id', $v_uk['id_karyawan'])->where('jabatan', 'marketing')->first();

        //                     if ( !empty($d_k) ) {
        //                         array_push($marketing, $d_k->toArray());
        //                     }
        //                 }
        //             }
        //         }

        //         $koordinator = array();
        //         if ( !empty($id_unit) ) {
        //             // $m_uk = new \Model\Storage\UnitKaryawan_model();
        //             // $d_uk = $m_uk->select('id_karyawan')->distinct('id_karyawan')->whereIn('unit', $id_unit)->get()->toArray();

        //             if ( !empty($d_uk) ) {
        //                 foreach ($d_uk as $k_uk => $v_uk) {
        //                     $m_k = new \Model\Storage\Karyawan_model();
        //                     $d_k = $m_k->where('id', $v_uk['id_karyawan'])->where('jabatan', 'koordinator')->first();

        //                     if ( !empty($d_k) ) {
        //                         array_push($koordinator, $d_k->toArray());
        //                     }
        //                 }
        //             }
        //         }

        //         $data_perwakilan = array(
        //             'id' => $perwakilan->id,
        //             'nama' => $perwakilan->nama,
        //             'formatPb' => $formatPb,
        //             'kanit' => $kanit,
        //             'ppl' => $ppl,
        //             'marketing' => $marketing,
        //             'koordinator' => $koordinator,
        //             'units' => $units
        //         );

        //         $data_mitra = array();
        //         foreach ($d_mm as $mm) {
        //             $m_mapping = new \Model\Storage\MitraMapping_model();
        //             $d_mapping = $m_mapping->where('id', $mm['id'])->first();

        //             if ( !empty($d_mapping) ) {
        //                 $m_mitra = new \Model\Storage\Mitra_model();
        //                 $d_mitra = $m_mitra->select('id', 'nama', 'jenis')->where('nomor', $d_mapping->nomor)->orderBy('id', 'desc')->first();

        //                 if ( !empty($d_mitra) ) {
        //                     // $m_mm = new \Model\Storage\MitraMapping_model();
        //                     // $d_mm = $m_mm->select('id')->where('mitra', $d_mitra->id)->orderBy('id', 'desc')->get()->toArray();

        //                     $m_kdg = new \Model\Storage\Kandang_model();
        //                     $d_kdg = $m_kdg->where('mitra_mapping', $mm['id'])->with(['dKecamatan', 'bangunans'])->get();

        //                     $kandangs = array();
        //                     foreach ($d_kdg as $kandang) {
        //                         $total_luas = 0;
        //                         foreach ($kandang->bangunans as $bangunan) {
        //                             $panjang = $bangunan->meter_panjang ?: 0;
        //                             $lebar = $bangunan->meter_lebar ?: 0;
        //                             $jml = $bangunan->jumlah_unit ?: 0;
        //                             $luas = ( $panjang * $lebar ) * $jml;

        //                             $total_luas += $luas;
        //                         }

        //                         $kapasitas = $kandang->ekor_kapasitas ?: 0;
        //                         $kandangs[] = array(
        //                             'id' => $kandang->id,
        //                             'nim' => $d_mapping->nim,
        //                             'group' => 'G-' . $kandang->grup,
        //                             'nomor' => strlen($kandang->kandang) < 2 ? '0' . $kandang->kandang : $kandang->kandang,
        //                             'tipe' => $kandang->tipe,
        //                             'kapasitas' => $kapasitas,
        //                             'luas' => $total_luas,
        //                             'densitas' => ($kapasitas > 0 && $total_luas > 0) ? ($kapasitas / $total_luas) : 0,
        //                             'alamat'=> $kandang->alamat_jalan,
        //                             'kecamatan' => $kandang->dKecamatan->nama,
        //                             'kabupaten' => $kandang->dKecamatan->dKota->nama,
        //                             'unit' => $kandang->d_unit->kode,
        //                         );
        //                     }

        //                     // $mitra_key = $d_mitra->nama . $d_mapping->id;
        //                     $mitra_key = $d_mitra->nama . $d_mapping->nomor;
        //                     $data_mitra[$mitra_key] = array(
        //                         'mapping_id' => $d_mapping->id,
        //                         'mitra_id' => $d_mitra->id,
        //                         'nama' => $d_mitra->nama,
        //                         'nim' => $d_mapping->nim,
        //                         // 'alamat' => $d_mitra->alamat_jalan,
        //                         // 'kecamatan' => $d_mitra->dKecamatan->nama,
        //                         // 'kabupaten' => $d_mitra->dKecamatan->dKota->nama,
        //                         'jenis' => getJenisMitra($d_mitra->jenis),
        //                         'kandangs' => $kandangs,
        //                     );
        //                 }
        //             }
        //         }

        //         ksort($data_mitra);

        //         $data[ $perwakilan->id ]['parent'] = $data_perwakilan;
        //         $data[ $perwakilan->id ]['child'] = $data_mitra;
        //     }
        // }

        // return $data;
    } // end - getDataPerwakilan

    public function getDataKandangMitraRDIM()
    {
        $tgl_docin = $this->input->get('tgl_docin');
        $nim = $this->input->get('nim');
        $kandang = $this->input->get('kandang');

        $noreg = $this->generateNoreg($nim, $kandang, $tgl_docin);

        $m = new \Model\Storage\Mitra_model();

        $d = array(
            'ip1' => 0,
            'ip2' => 0,
            'ip3' => 0,
            'next_noreg' => $noreg,
        );

        if ($d) {
            $this->result['status'] = 1;
            $this->result['message'] = 'sukses';
            $this->result['content'] = $d;
        }else{
            $this->result['message'] = 'Data mitra tidak ditemukan, silakan konfirmasi IT!';
        }

        display_json( $this->result );
    } // end - getDataKandangMitraRDIM

    public function generateNoreg($nim = null, $kandang = null, $tgl_docin = null)
    {
        $noreg = null;

        $m_mmp = new \Model\Storage\MitraMapping_model();
        $d_mmp = $m_mmp->select('id')->where('nim', trim($nim))->get();

        $d_kandang = null;

        $d_rdim = null;
        if ( $d_mmp->count() > 0 ) {
            $d_mmp = $d_mmp->toArray();

            $m_kandang = new \Model\Storage\Kandang_model();
            $d_kandang = $m_kandang->select('id')->whereIn('mitra_mapping', $d_mmp)
                                   ->where('kandang', number_format($kandang))
                                   ->get();

            if ( $d_kandang->count() > 0 ) {
                $d_kandang = $d_kandang->toArray();

                // $_nim = substr($nim, 0, 2) . substr($nim, 4);
                $_nim = trim($nim);

                $m_rdim = new \Model\Storage\RdimSubmit_model();
                $d_rdim = $m_rdim->where('nim', trim($nim))
                                 ->whereIn('kandang', $d_kandang)
                                 ->orderBy('noreg', 'DESC')
                                 ->first();
            }
        }

        if ( empty($d_rdim) ) {
            $str_kandang = null;
            if ( strlen(number_format($kandang)) == 1) {
                $str_kandang = '0'.number_format($kandang);
            } else {
                $str_kandang = $kandang;
            }
            
            $noreg = trim($_nim) . '01' . $str_kandang;
        } else {
            $m_rdim = new \Model\Storage\RdimSubmit_model();
            $d_rdim_tanggal = $m_rdim->where('nim', trim($nim))
                             ->whereIn('kandang', $d_kandang)
                             ->where('tgl_docin', $tgl_docin)
                             ->orderBy('id', 'DESC')
                             ->first();

            if ( $d_rdim_tanggal ) {
                $noreg = $d_rdim_tanggal['noreg'];
            } else {
                $_noreg = $d_rdim['noreg'];
                $jml_nim = strlen(trim($nim));

                $_siklus = trim(substr($_noreg, $jml_nim, 2));
                $siklus = $_siklus + 1;

                $str_siklus = null;
                if ( strlen($siklus) == 1) {
                    $str_siklus = '0'.$siklus;
                } else {
                    $str_siklus = $siklus;
                }

                $str_kandang = null;
                if ( strlen(number_format($kandang)) == 1) {
                    $str_kandang = '0'.number_format($kandang);
                } else {
                    $str_kandang = $kandang;
                }

                $noreg = $_nim . $str_siklus . $str_kandang;
            }
        }

        return $noreg;
    } // end - generateNoreg

    public function getVaksin()
    {
        $m_vaksin = new \Model\Storage\Vaksin_model();
        $d_vaksin = $m_vaksin->where('status', getStatus('submit'))->get();

        return $d_vaksin;
    }

    public function addNewRdim()
    {
        $content['rdim_data_perwakilan_mitra'] = $this->getDataPerwakilan();
        // $content['tim_panens'] = $this->getDataTimpanen();
        $content['addNewRdim'] = true;
        $content['akses'] = hakAkses($this->url);
        $html = $this->load->view($this->pathView . 'rencana_doc_in_mingguan', $content, true);
        echo $html;
    } // end - addNewRdim

    public function saveRdim()
    {
        $params = $this->input->post('params');

        // NOTE: preparing data
        $periode = $params['periode'];
        $details = $params['details'];

        // NOTE: 1. save header -> rdim
        $m_rdim = new \Model\Storage\Rdim_model();
        $next_doc_number = $m_rdim->getNextDocNum('ADM/RDIM');

        $m_rdim->nomor = $next_doc_number;
        $m_rdim->mulai = $periode['start'];
        $m_rdim->selesai = $periode['end'];
        $m_rdim->g_status = getStatus('submit');
        $m_rdim->save();

        $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
        Modules::run( 'base/event/save', $m_rdim, $deskripsi_log);
        $id_rdim = $m_rdim->id;

        // NOTE: 2. save detail -> rdim_submit
        foreach ($details as $item) {
            $m_rs = new \Model\Storage\RdimSubmit_model();
            $m_rs->id_rdim = $id_rdim;
            $m_rs->tgl_docin = $item['tanggal'] ;
            $m_rs->nim = $item['nim'] ;
            $m_rs->kandang = $item['kandang'] ;
            $m_rs->populasi = $item['populasi'] ;
            $m_rs->noreg = $item['noreg'] ;
            // $m_rs->prokes = $item['program_kesehatan'] ;
            $m_rs->pengawas = $item['pengawas'] ;
            $m_rs->sampling = $item['tim_sampling'] ;
            $m_rs->tim_panen = $item['tim_panen'] ;
            $m_rs->koar = $item['koordinator_area'] ;
            $m_rs->format_pb = $item['formatPb'] ;
            $m_rs->pola_mitra = $item['pola'] ;
            $m_rs->grup = $item['group'];
            $m_rs->status = 1;
            // $m_rs->ip1 = $item['ip_terakhir_1'];
            // $m_rs->ip2 = $item['ip_terakhir_2'];
            // $m_rs->ip3 = $item['ip_terakhir_3'];
            $m_rs->tipe_densitas = $item['tipe_densitas'];
            $m_rs->perusahaan = $item['perusahaan'];
            $m_rs->vaksin = $item['vaksin'];
            $m_rs->save();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_rs, $deskripsi_log);
        }

        $this->result['status'] = 1;
        $this->result['message'] = 'Data berhasil disimpan';
        $this->result['content'] = array('id' => $id_rdim);
        display_json($this->result);
    } // end - saveRdim

    public function editRdim()
    {
        $params = $this->input->post('params');

        // NOTE: preparing data
        $periode = $params['periode'];
        $details = $params['details'];

        // NOTE: 1. update header -> rdim
        $id_rdim = $params['id'];
        $m_rdim = new \Model\Storage\Rdim_model();

        $m_rdim->where('id', $id_rdim)
               ->update(
                    array(
                        'mulai' => $periode['start'],
                        'selesai' => $periode['end'],
                        'g_status' => getStatus('submit')
                    )
                );

        $d_rdim = $m_rdim->where('id', $id_rdim)->first();

        $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
        Modules::run( 'base/event/update', $d_rdim, $deskripsi_log);

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $m_rs->where('id_rdim', $id_rdim)->delete();

        // NOTE: 2. save detail -> rdim_submit
        foreach ($details as $item) {
            $m_rs_new = new \Model\Storage\RdimSubmit_model();
            $m_rs_new->id_rdim = $id_rdim;
            $m_rs_new->tgl_docin = $item['tanggal'] ;
            $m_rs_new->nim = $item['nim'] ;
            $m_rs_new->kandang = $item['kandang'] ;
            $m_rs_new->populasi = $item['populasi'] ;
            $m_rs_new->noreg = $item['noreg'] ;
            // $m_rs_new->prokes = $item['program_kesehatan'] ;
            $m_rs_new->pengawas = $item['pengawas'] ;
            $m_rs_new->sampling = $item['tim_sampling'] ;
            $m_rs_new->tim_panen = $item['tim_panen'] ;
            $m_rs_new->koar = $item['koordinator_area'] ;
            $m_rs_new->format_pb = $item['formatPb'] ;
            $m_rs_new->pola_mitra = $item['pola'] ;
            $m_rs_new->grup = $item['group'];
            $m_rs_new->status = 1;
            // $m_rs_new->ip1 = $item['ip_terakhir_1'];
            // $m_rs_new->ip2 = $item['ip_terakhir_2'];
            // $m_rs_new->ip3 = $item['ip_terakhir_3'];
            $m_rs_new->tipe_densitas = $item['tipe_densitas'];
            $m_rs_new->perusahaan = $item['perusahaan'];
            $m_rs_new->vaksin = $item['vaksin'];
            $m_rs_new->save();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $m_rs_new, $deskripsi_log);
        }

        $this->result['status'] = 1;
        $this->result['message'] = 'Data berhasil diubah';
        $this->result['content'] = array('id' => $id_rdim);
        display_json($this->result);
    } // end - editRdim

    public function list_rdim()
    {
        $m_rdim = new \Model\Storage\Rdim_model();
        $d_rdim = $m_rdim->with(['logs'])->where('g_status', '<>', getStatus('delete') )->orderBy('id', 'DESC')->take(50)->get();

        $content['datas'] = $d_rdim;
        $content['akses'] = hakAkses($this->url);
        $html = $this->load->view($this->pathView . 'list', $content, true);

        echo $html;
    } // end - list_rdim

    public function viewRdim($id)
    {
        $m_rdim = new \Model\Storage\Rdim_model();
        $d_rdim = $m_rdim->with(['logs'])->find($id);
        $dataMapping = $this->rdimMapping($d_rdim);
        $content['data'] = $dataMapping;
        $content['akses'] = hakAkses($this->url);
        $html = $this->load->view($this->pathView . 'view_form', $content, true);
        return $html;
    } // end - viewRdim

    private function rdimMapping($d_rdim)
    {
        // NOTE: header -> rdim
        $rdim = array(
            'id' => $d_rdim->id,
            'nomor' => $d_rdim->nomor,
            'mulai' => $d_rdim->mulai,
            'selesai' => $d_rdim->selesai,
            'status'=> $d_rdim->g_status,
            'alasan_tolak' => $d_rdim->alasan_tolak,
            'logs' =>$d_rdim->logs
        );

        // NOTE: details -> rdim_submit
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                rs.id as id,
                rs.tgl_docin as tanggal,
                mtr.id as id_mitra,
                mtr.nama as mitra,
                kdg.kandang as kandang,
                rs.populasi as populasi,
                kdg.ekor_kapasitas as kapasitas,
                '-' as istirahat,
                '-' as hutang,
                '-' as jut,
                kdg.nama_kecamatan as kecamatan,
                kdg.nama_kab_kota as kabupaten,
                rs.noreg as noreg,
                kry_pengawas.nik as nik_pengawas,
                kry_pengawas.nama as pengawas,
                kry_sampling.nik as nik_sampling,
                kry_sampling.nama as sampling,
                kry_tp.nik as nik_tim_panen,
                kry_tp.nama as tim_panen,
                kry_koar.nik as nik_koar,
                kry_koar.nama as koar,
                rs.tipe_densitas as densitas,
                rs.pola_mitra+' '+prs.nama as format_pb,
                rs.format_pb as format_pb_id,
                sk.mulai as tgl_sk,
                sk.nomor as nomor_sk,
                CASE
                    WHEN mtr.jenis = 'MI' THEN
                        'Internal'
                    ELSE
                        'Eksternal'
                END as pola,
                rs.status as status,
                cast(rs.ket_alasan as varchar(250)) as ket_alasan,
                rs.grup as _group,
                -- as lampirans,
                mtr.perusahaan as perusahaan,
                v.nama_vaksin as vaksin,
                v.id as id_vaksin,
                mtr.perwakilan,
                prs.kode_perusahaan
            from rdim_submit rs
            left join
                (
                    select 
                        mtr.id,
                        mtr.nama,
                        mtr.jenis,
                        mtr.perusahaan,
                        mm.nim,
                        mm.perwakilan
                    from mitra mtr
                    right join
                        (
                            select mm1.* from mitra_mapping mm1
                            right join
                                (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                on
                                    mm1.id = mm2.id
                        ) mm
                        on
                            mtr.id = mm.mitra
                ) mtr
                on
                    rs.nim = mtr.nim
            left join
                (
                    select 
                        kdg.id,
                        kdg.kandang,
                        kdg.ekor_kapasitas,
                        kec.nama as nama_kecamatan,
                        kab_kota.nama as nama_kab_kota
                    from kandang kdg
                    left join
                        (
                            select kandang, sum(meter_panjang) as meter_panjang, sum(meter_lebar) as meter_lebar, sum(jumlah_unit) as jumlah_unit from bangunan_kandang group by kandang
                        ) bk
                        on
                            bk.kandang = kdg.id
                    left join
                        (
                            select * from lokasi where jenis = 'KC'
                        ) kec
                        on
                            kdg.alamat_kecamatan = kec.id
                    left join
                        (
                            select * from lokasi where jenis = 'KT' or jenis = 'KB'
                        ) kab_kota
                        on
                            kec.induk = kab_kota.id
                    left join
                        wilayah w
                        on
                            w.id = kdg.unit
                    group by
                        kdg.id,
                        kdg.kandang,
                        kdg.ekor_kapasitas,
                        kec.nama,
                        kab_kota.nama
                ) kdg
                on
                    rs.kandang = kdg.id
            left join
                karyawan kry_pengawas
                on
                    rs.pengawas = kry_pengawas.nik
            left join
                karyawan kry_sampling
                on
                    rs.sampling = kry_sampling.nik
            left join
                karyawan kry_tp
                on
                    rs.tim_panen = kry_tp.nik
            left join
                karyawan kry_koar
                on
                    rs.koar = kry_koar.nik
            left join
                (
                    select 
                        prs1.kode,
                        prs1.perusahaan as nama,
                        prs1.kode_auto as kode_perusahaan
                    from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    prs.kode = mtr.perusahaan
            left join
                vaksin v
                on
                    v.id = rs.vaksin
            left join
                (
                    select 
                        sk1.id,
                        sk1.nomor,
                        sk1.pola,
                        sk1.item_pola,
                        sk1.mulai,
                        pm.id as id_pm
                    from sapronak_kesepakatan sk1
                    right join
                        (select max(id) as id, nomor from sapronak_kesepakatan group by nomor) sk2
                        on
                            sk1.id = sk2.id
                    left join
                        hitung_budidaya_item hbi
                        on
                            sk1.id = hbi.id_sk
                    left join
                        perwakilan_maping pm
                        on
                            hbi.id = pm.id_hbi
                ) sk
                on
                    rs.format_pb = sk.id_pm
            where
                rs.id_rdim = ".$d_rdim->id."
            group by
                rs.id,
                rs.tgl_docin,
                mtr.id,
                mtr.nama,
                kdg.kandang,
                rs.populasi,
                kdg.ekor_kapasitas,
                kdg.nama_kecamatan,
                kdg.nama_kab_kota,
                rs.noreg,
                kry_pengawas.nik,
                kry_pengawas.nama,
                kry_sampling.nik,
                kry_sampling.nama,
                kry_tp.nik,
                kry_tp.nama,
                kry_koar.nik,
                kry_koar.nama,
                rs.tipe_densitas,
                rs.pola_mitra,
                prs.nama,
                rs.format_pb,
                sk.mulai,
                sk.nomor,
                mtr.jenis,
                rs.status,
                cast(rs.ket_alasan as varchar(250)),
                rs.grup,
                mtr.perusahaan,
                v.nama_vaksin,
                v.id,
                mtr.perwakilan,
                prs.kode_perusahaan
        ";
        $d_rs = $m_conf->hydrateRaw( $sql );

        $rdim_submit = array();
        if ( $d_rs->count() > 0 ) {
            $d_rs = $d_rs->toArray();

            $rdim_submit = array();
            foreach ($d_rs as $key => $value) {
                $status_rs = 'status-' . $value['status'];
                $perwakilan_id = $value['perwakilan'];

                $m_conf = new \Model\Storage\Conf();
                $sql_lampiran = "
                    select 
                        l.id,
                        l.path,
                        l.filename,
                        nl.nama as nama_lampiran
                    from lampiran l
                    left join
                        nama_lampiran nl
                        on
                            l.nama_lampiran = nl.id
                    where
                        l.tabel = 'rdim_submit' and
                        l.tabel_id = ".$value['id']."
                ";
                $d_lampiran = $m_conf->hydrateRaw( $sql_lampiran );
                $lampirans = array();
                if ( $d_lampiran->count() > 0 ) {
                    $d_lampiran = $d_lampiran->toArray();

                    foreach ($d_lampiran as $vLamp) {
                        if (isset($vLamp['nama_lampiran']) && $vLamp['nama_lampiran'] == 'Pembatalan Rencana DOC in Mingguan') {
                            $lampirans['batal'] = array(
                                'path' => 'uploads/' . $vLamp['path'],
                                'filename' => $vLamp['filename'],
                            );
                        }
                    }
                }

                $value['lampirans'] = $lampirans;
                $value['group'] = $value['_group'];

                if ( ! isset($rdim_submit[ $status_rs ][$perwakilan_id])) {
                    $m_wil = new \Model\Storage\Wilayah_model();
                    $d_wil = $m_wil->where('id', $perwakilan_id)->with(['unit'])->orderBy('id', 'desc')->first();

                    $units = array();
                    if ( !empty($d_wil->unit) ) {
                        $units = array_map(function($unit){
                            return $unit['nama'];
                        }, $d_wil->unit->toArray());
                    }

                    $header = array(
                        'id' => $perwakilan_id,
                        'perwakilan' => !empty($d_wil->nama) ? $d_wil->nama : null,
                        'units' => $units
                    );

                    $rdim_submit[ $status_rs ][$perwakilan_id]['header'] = $header;
                }
                
                $rdim_submit[ $status_rs ][$perwakilan_id]['details'][] = $value;
                if ( !isset($rdim_submit[ $status_rs ][$perwakilan_id]['header']['populasi']) ) {
                    $rdim_submit[ $status_rs ][$perwakilan_id]['header']['populasi'] = $value['populasi'];
                } else {
                    $rdim_submit[ $status_rs ][$perwakilan_id]['header']['populasi'] += $value['populasi'];
                }
            }
        }

        // $m_rs = new \Model\Storage\RdimSubmit_model();
        // $d_rs = $m_rs->where('id_rdim', $d_rdim->id)->with(['lampirans', 'data_perusahaan', 'data_vaksin'])->get();

        // $rdim_submit = array();
        // if ( $d_rs->count() > 0 ) {
        //     foreach ($d_rs as $rs) {
        //         $lampirans = array();
        //         foreach ($rs->lampirans as $vLamp) {
        //             if ($vLamp->d_nama_lampiran->nama == 'Pembatalan Rencana DOC in Mingguan') {
        //                 $lampirans['batal'] = array(
        //                     'path' => 'uploads/' . $vLamp->path,
        //                     'filename' => $vLamp->filename,
        //                 );
        //             }
        //         }

        //         $m_karyawan = new \Model\Storage\Karyawan_model();
        //         $pengawas = $m_karyawan->select('nik', 'nama')->where('nik', $rs->pengawas)->first();
        //         $sampling = $m_karyawan->select('nik', 'nama')->where('nik', $rs->sampling)->first();
        //         $tim_panen = $m_karyawan->select('nik', 'nama')->where('nik', $rs->tim_panen)->first();
        //         $koar = $m_karyawan->select('nik', 'nama')->where('nik', $rs->koar)->first();

        //         $m_mm = new \Model\Storage\MitraMapping_model();
        //         $d_mm = $m_mm->where('nim', $rs->nim)->with(['dPerwakilan'])->orderBy('id', 'desc')->first();

        //         $m_mitra = new \Model\Storage\Mitra_model();
        //         $d_mitra = $m_mitra->where('id', $d_mm->mitra)->orderBy('id', 'desc')->first();

        //         $m_kdg = new \Model\Storage\Kandang_model();
        //         $d_kdg = $m_kdg->where('id', $rs->kandang)->with(['dKecamatan'])->first();

        //         $m_conf = new \Model\Storage\Conf();
        //         $sql = "
        //             select
        //                 sk.nomor 
        //             from perwakilan_maping pm 
        //             left join
        //                 hitung_budidaya_item hbi 
        //                 on
        //                     pm.id_hbi = hbi.id
        //             left join
        //                 sapronak_kesepakatan sk 
        //                 on
        //                     hbi.id_sk = sk.id
        //             where 
        //                 pm.id = ".$rs->format_pb."
        //         ";
        //         $d_sk = $m_conf->hydrateRaw( $sql );

        //         $nomor_sk = null;
        //         if ( $d_sk->count() > 0 ) {
        //             $d_sk = $d_sk->toArray()[0];

        //             $nomor_sk = $d_sk['nomor'];
        //         }

        //         $item = array(
        //             'id' => $rs->id,
        //             'tanggal' => $rs->tgl_docin,
        //             'id_mitra' => $d_mitra->id,
        //             'mitra' => $d_mitra->nama,
        //             'kandang' => $rs->dKandang->kandang,
        //             'populasi' => $rs->populasi,
        //             // 'ip1' => $rs->ip1,
        //             // 'ip2' => $rs->ip2,
        //             // 'ip3' => $rs->ip3,
        //             'kapasitas' => $rs->dKandang->ekor_kapasitas,
        //             'istirahat' => '-',
        //             'hutang' => '-',
        //             'jut' => '-',
        //             'kecamatan' => isset($d_kdg->dKecamatan) ? $d_kdg->dKecamatan->nama : null,
        //             'kabupaten' => isset($d_kdg->dKecamatan->dKota) ? $d_kdg->dKecamatan->dKota->nama : null,
        //             'noreg' => $rs->noreg,
        //             // 'prokes' => $rs->prokes,
        //             // 'pengawas' => $rs->pengawas,
        //             // 'sampling' => $rs->sampling,
        //             // 'id_tim_panen' => $rs->dTimpanen->nik_timpanen,
        //             // 'tim_panen' => $rs->dTimpanen->nama_timpanen,
        //             // 'koar' => $rs->koar,
        //             'nik_pengawas' => $pengawas->nik,
        //             'pengawas' => $pengawas->nama,
        //             'nik_sampling' => $sampling->nik,
        //             'sampling' => $sampling->nama,
        //             'nik_tim_panen' => $tim_panen->nik,
        //             'tim_panen' => $tim_panen->nama,
        //             'nik_koar' => $koar->nik,
        //             'koar' => $koar->nama,
        //             'densitas' => $rs->tipe_densitas,
        //             'format_pb' => $rs->pola_mitra . ' ' . $rs->data_perusahaan->perusahaan,
        //             'format_pb_id' => $rs->format_pb,
        //             'nomor_sk' => $nomor_sk,
        //             'pola' => getJenisMitra( $d_mitra->jenis ),
        //             'status' => $rs->status,
        //             'ket_alasan' => $rs->ket_alasan,
        //             'group' => $rs->grup,
        //             'lampirans' => $lampirans,
        //             'perusahaan' => $rs->perusahaan,
        //             'vaksin' => $rs->data_vaksin->nama_vaksin,
        //             'id_vaksin' => $rs->data_vaksin->id
        //         );

        //         // NOTE: perwakilan -> header row
        //         $status_rs = 'status-' . $item['status'];
        //         $perwakilan_id = $d_mm->perwakilan;

        //         if ( ! isset($rdim_submit[ $status_rs ][$perwakilan_id])) {
        //             $units = array();
        //             if ( !empty($d_mm->dPerwakilan->unit) ) {
        //                 $units = array_map(function($unit){
        //                     return $unit['nama'];
        //                 }, $d_mm->dPerwakilan->unit->toArray());
        //             }

        //             $header = array(
        //                 'id' => $perwakilan_id,
        //                 'perwakilan' => !empty($d_mm->dPerwakilan->nama) ? $d_mm->dPerwakilan->nama : null,
        //                 'units' => $units
        //             );

        //             $rdim_submit[ $status_rs ][$perwakilan_id]['header'] = $header;
        //         }

        //         $rdim_submit[ $status_rs ][$perwakilan_id]['details'][] = $item;
        //     }
        // }

        $rdimMapping = array(
            'rdim' => $rdim,
            'rdim_submit' => $rdim_submit
        );

        return $rdimMapping;
    } // end - rdimMapping

    public function loadContentRdim()
    {
        $id = $this->input->get('id');
        $action = $this->input->get('action');
        $content = array();
        $html = "url not found";
        if ( !empty($id) && is_numeric($id) ) {
            // NOTE: view/edit data StandarBudidaya (ajax)
            $html = (strtolower($action) == 'edit') ? $this->editRdim($id) : $this->viewRdim($id);
        }else{
            $content['akses'] = hakAkses($this->url);
        }
        echo $html;
    } // end - loadContentRdim

    public function ack()
    {
        $params = $this->input->post('params');
        $id = $params['id'];
        $action = $params['action'];
        $event = null;

        $m_rdim = new \Model\Storage\Rdim_model();
        $update = $m_rdim->find($id);
        $g_status = '';
        if (!empty($update)) {
                $g_status = getStatus('ack');
                $update->g_status = $g_status;

            if (!empty($update)) {
                $update->save();
                $event = Modules::run( 'base/event/update', $update, 'di-'.$action.' oleh ' . $this->userdata['detail_user']['nama_detuser'] );
            }

            if ($event) {
                $this->result['status'] = 1;
                $this->result['message'] = 'Data berhasil di ack';
            }else{
                $this->result['message'] = 'Data gagal di ack';
            }

        }else{
            $this->result['message'] = 'Data not found';
        }

        $this->result['content'] = array('id' => $id);
        display_json($this->result);
    } // end - ack

    public function approveReject()
    {
        $params = $this->input->post('params');
        $id = $params['id'];
        $action = $params['action'];
        $event = null;

        $m_rdim = new \Model\Storage\Rdim_model();
        $update = $m_rdim->find($id);
        $g_status = '';
        if (!empty($update)) {

            if ($action == 'approve') {
                $g_status = getStatus('approve');
                $update->g_status = $g_status;
            } elseif ($action == 'reject') {
                $update->alasan_tolak = $params['alasan_tolak'];
                $g_status = getStatus('reject');
                $update->g_status = $g_status;
            }

            if (!empty($update)) {
                $update->save();
                $event = Modules::run( 'base/event/update', $update, 'di-'.$action.' oleh ' . $this->userdata['detail_user']['nama_detuser'] );
            }

            if ($event) {
                $this->result['status'] = 1;
                $this->result['message'] = 'Data berhasil di approve';
            }else{
                $this->result['message'] = 'Data gagal di approve';
            }

        }else{
            $this->result['message'] = 'Data not found';
        }

        $this->result['content'] = array('id' => $id);
        display_json($this->result);
    } // end - approveReject

    public function getDataPembatalanRdim()
    {
        $id_rdim = $this->input->get('id_rdim');

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('id_rdim', $id_rdim)->where('status', 1)->with(['dMitraMapping'])->get();
        $datas = array();
        foreach ($d_rs as $rs) {
            $data = array(
                'id' => $rs->id,
                'mitra' => $rs->dMitraMapping->dMitra->nama,
                'noreg' => $rs->noreg
            );
            $datas[ $data['id'] ] = $data;
        }
        ksort($datas);
        $content['lists'] = $datas;
        $html = $this->load->view($this->pathView . 'list_item_pembatalan_rdim', $content, true);
        echo $html;
    } // end - getDataPembatalanRdim

    public function savePembatalanRdim()
    {

        $data_rs = json_decode($this->input->post('data_rs'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];
        $mappingFiles = mappingFiles($files);

        // NOTE: cari id nama lampiran
        $m_lampiran = new \Model\Storage\NamaLampiran_model();
        $d_lampiran = $m_lampiran->where('nama', 'Pembatalan Rencana DOC in Mingguan')->first();
        $lampiranId = $d_lampiran->id;

        $deskripsi_log = 'pembatalan di-submit oleh ' . $this->userdata['Nama_User'];
        $idRdims = array();
        foreach ($data_rs as $rs) {
            $m_rs = new \Model\Storage\RdimSubmit_model();
            $update = $m_rs->find($rs['id_rs']);
            $update->status = 2;
            $update->ket_alasan = $rs['ket_alasan'];
            $update->save();
            Modules::run( 'base/event/save', $update, $deskripsi_log );
            $idRdims[$update->id_rdim] = $update->id_rdim;

            $file = $mappingFiles[ $rs['sha1'] ] ?: '';
            $file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($file)) {
                $moved = uploadFile($file);
                $file_name = $moved['name'];
                $path_name = $moved['path'];
                $isMoved = $moved['status'];
            }
            if ($isMoved) {
                $m_lampiran = new \Model\Storage\Lampiran_model();
                $m_lampiran->tabel = 'rdim_submit';
                $m_lampiran->tabel_id = $rs['id_rs'];
                $m_lampiran->nama_lampiran = $lampiranId;
                $m_lampiran->filename = $file_name ;
                $m_lampiran->path = $path_name;
                $m_lampiran->save();
                Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log );
            }
        }

        foreach ($idRdims as $idRdim) {
            $m_rdim = new \Model\Storage\Rdim_model();
            $update = $m_rdim->find($idRdim);
            $update->g_status = getStatus('submit');
            $update->save();
            Modules::run( 'base/event/save', $update, $deskripsi_log );
        }

        $this->result['status'] = 1;
        $this->result['message'] = 'Data berhasil disimpan';
        display_json($this->result);
    } // end - savePembatalanRdim

    public function cetak_kontrak($id)
    {
        $this->load->library('PDFGenerator');

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('id', $id)->where('status', 1)->with(['dMitraMapping'])->first()->toArray();

        $m_pm = new \Model\Storage\PerwakilanMaping_model();
        $d_pm = $m_pm->where('id', $d_rs['format_pb'])->first()->toArray();

        $m_hbi = new \Model\Storage\HitungBudidayaItem_model();
        $d_hbi = $m_hbi->where('id', $d_pm['id_hbi'])->first()->toArray();

        $id_sk = $d_hbi['id_sk'];

        $m_sk = new \Model\Storage\SapronakKesepakatan_model();
        $d_sk_old = $m_sk->where('id', $id_sk)->first()->toArray();

        $no_sk = $d_sk_old['nomor'];

        $m_rdim = new \Model\Storage\Rdim_model();
        $d_rdim = $m_rdim->with(['logs'])->find($d_rs['id_rdim'])->toArray();

        $periode = tglIndonesia($d_rdim['mulai'], '-', ' ', true) . ' - ' . tglIndonesia($d_rdim['selesai'], '-', ' ', true);
        $dataMapping = $this->rdimMappingCetak($d_rs, $periode);

        // $pola = $d_rs['pola_mitra'];

        // $coba = explode(' ', $pola);

        // $pola_budidaya = ($coba[0] == 'TR') ? 'tanggung rugi' : 'tidak tanggung rugi';
        // $pola = str_replace(')', '', $coba[1]);
        // $item_pola = str_replace('(', '', $pola);

        // $m_pk = new \Model\Storage\PolaKerjasama_model();
        // $d_pk = $m_pk->select('id')->where('item', trim($pola_budidaya))->first()->toArray();

        $m_sk = new \Model\Storage\SapronakKesepakatan_model();
        $d_sk = $m_sk->where('nomor', $no_sk)
                     ->with(['harga_sapronak', 'harga_performa', 'harga_sepakat', 'hitung_budidaya', 'selisih_pakan', 'lampiran', 'logs', 'pola_kerjasama', 'data_perusahaan'])
                     ->orderBy('id', 'DESC')->first();

        $content['data'] = $dataMapping;
        $content['data_kontrak'] = $d_sk->toArray();
        $res_view_html = $this->load->view('transaksi/rdim/cetak_kontrak', $content, true);

        $this->pdfgenerator->generate($res_view_html, "KONTRAK (".$d_sk['nomor'].")", 'a4', 'portrait');
    }

    public function rdimMappingCetak($data = null, $periode = null)
    {
        $v_data = $data;
        $_data = array();
        if ( !empty($v_data) ) {
            $provinsi = !empty($v_data['d_mitra_mapping']['d_mitra']['d_kecamatan']['d_kota']['d_provinsi']['nama']) ? ', '.$v_data['d_mitra_mapping']['d_mitra']['d_kecamatan']['d_kota']['d_provinsi']['nama'] : '';
            $kab_kota = !empty($v_data['d_mitra_mapping']['d_mitra']['d_kecamatan']['d_kota']['nama']) ? ', '.$v_data['d_mitra_mapping']['d_mitra']['d_kecamatan']['d_kota']['nama'] : '';
            $kecamatan = !empty($v_data['d_mitra_mapping']['d_mitra']['d_kecamatan']['nama']) ? ', Kec.'.$v_data['d_mitra_mapping']['d_mitra']['d_kecamatan']['nama'] : '';
            $kelurahan = !empty($v_data['d_mitra_mapping']['d_mitra']['alamat_kelurahan']) ? ', Kel.'.$v_data['d_mitra_mapping']['d_mitra']['alamat_kelurahan'] : '';
            $rt = !empty($v_data['d_mitra_mapping']['d_mitra']['alamat_rt']) ? ', RT.'.$v_data['d_mitra_mapping']['d_mitra']['alamat_rt'] : '';
            $rw = !empty($v_data['d_mitra_mapping']['d_mitra']['alamat_rw']) ? '/RW.'.$v_data['d_mitra_mapping']['d_mitra']['alamat_rw'] : '';
            $jalan = !empty($v_data['d_mitra_mapping']['d_mitra']['alamat_jalan']) ? $v_data['d_mitra_mapping']['d_mitra']['alamat_jalan'] : '';

            $alamat = $jalan.$rt.$rw.$kelurahan.$kecamatan.$kab_kota.$provinsi;

            $_data['periode'] = $periode;
            $_data['nama'] = $v_data['d_mitra_mapping']['d_mitra']['nama'];
            $_data['ktp'] = $v_data['d_mitra_mapping']['d_mitra']['ktp'];
            $_data['alamat'] = $alamat;
            $_data['alamat_kdg'] = $alamat;
            $_data['populasi'] = $v_data['populasi'];
        }

        return $_data;
    }

    public function formPenanggungJawabKandang()
    {
        $params = $this->input->get('params');

        $id = $params['id'];

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                rs.*,
                mtr.nama as nama_mitra
            from rdim_submit rs
            right join
                (
                    select 
                        mtr.*,
                        mm.nim
                    from mitra mtr
                    right join
                        (
                            select mm1.* from mitra_mapping mm1
                            right join
                                (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                on
                                    mm1.id = mm2.id
                        ) mm
                        on
                            mtr.id = mm.mitra
                ) mtr
                on
                    rs.nim = mtr.nim
            where
                rs.id = ".$id."
        ";
        $_d_rs = $m_conf->hydrateRaw( $sql );

        $d_rs = $_d_rs->toArray()[0];
    
        $m_kdg = new \Model\Storage\Kandang_model();
        $d_kdg = $m_kdg->where('id', $d_rs['kandang'])->with(['d_unit'])->first()->toArray();

        $m_wil = new \Model\Storage\Wilayah_model();
        $d_wil = $m_wil->select('id')->where('kode', $d_kdg['d_unit']['kode'])->get()->toArray();

        $id_units = null;
        foreach ($d_wil as $k_wil => $v_wil) {
            $id_units[] = $v_wil['id'];
        }

        $ppl = null;
        $kanit = null;
        $marketing = null;
        $koordinator = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                k.id,
                k.level,
                k.nik,
                k.atasan,
                UPPER(k.nama) as nama,
                k.wilayah,
                k.kordinator,
                k.marketing,
                k.jabatan,
                k.status
            from karyawan k
            right join
                unit_karyawan uk
                on
                    uk.id_karyawan = k.id
            where
                (uk.unit in ('".implode("', '", $id_units)."') or uk.unit like '%all%') and
                k.status = 1
            group by
                k.id,
                k.level,
                k.nik,
                k.atasan,
                k.nama,
                k.wilayah,
                k.kordinator,
                k.marketing,
                k.jabatan,
                k.status
            order by
                k.nama asc
        ";
        $d_karyawan = $m_conf->hydrateRaw( $sql );

        if ( $d_karyawan->count() > 0 ) {
            $d_karyawan = $d_karyawan->toArray();
            foreach ($d_karyawan as $k => $val) {
                if ( $val['jabatan'] == 'ppl' || $val['jabatan'] == 'kepala unit' ) {
                    $ppl[] = $val;
                }
                if ( $val['jabatan'] == 'kepala unit' || $val['jabatan'] == 'koordinator' ) {
                    $kanit[] = $val;
                }
                if ( $val['jabatan'] == 'marketing' ) {
                    $marketing[] = $val;
                }
                if ( $val['jabatan'] == 'koordinator' || $val['jabatan'] == 'penanggung jawab' ) {
                    $koordinator[] = $val;
                }
            }
        }

        $data = array(
            'id' => $d_rs['id'],
            'noreg' => $d_rs['noreg'],
            'nama_mitra' => $d_rs['nama_mitra'],
            'tgl_docin' => $d_rs['tgl_docin'],
            'nik_ppl' => $d_rs['sampling'],
            'nik_kanit' => $d_rs['pengawas'],
            'nik_marketing' => $d_rs['tim_panen'],
            'nik_koordinator' => $d_rs['koar'],
            'ppl' => $ppl,
            'kanit' => $kanit,
            'marketing' => $marketing,
            'koordinator' => $koordinator
        );

        $content['data'] = $data;
        $html = $this->load->view('transaksi/rdim/formPenanggungJawabKandang', $content, true);

        echo $html;
    }

    public function editPenanggungJawab()
    {
        $params = $this->input->post('params');
        try {
            $m_rs = new \Model\Storage\RdimSubmit_model();
            $m_rs->where('id', $params['id'])->update(
                array(
                    'pengawas' => $params['kanit'],
                    'sampling' => $params['ppl'],
                    'tim_panen' => $params['marketing'],
                    'koar' => $params['korwil']
                )
            );

            $m_rs = new \Model\Storage\RdimSubmit_model();
            $d_rs = $m_rs->where('id', $params['id'])->first();

            $deskripsi_log_dk = 'perubahan penanggung jawab oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_rs, $deskripsi_log_dk );

            $this->result['status'] = 1;
            $this->result['content'] = array('id_rdim' => $d_rs->id_rdim);
            $this->result['message'] = 'Data berhasil di ubah.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function model($status)
    {
        $m_rdim = new \Model\Storage\Rdim_model();
        $dashboard = $m_rdim->getDashboard($status);

        return $dashboard;
    }

    public function tes()
    {
        $m_rdim = new \Model\Storage\Rdim_model();
        $d_rdim = $m_rdim->where('id', 8179)->first();

        cetak_r( $this->rdimMapping( $d_rdim ) );
    }
}
