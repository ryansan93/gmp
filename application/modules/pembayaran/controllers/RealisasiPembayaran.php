<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RealisasiPembayaran extends Public_Controller
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
                'assets/pembayaran/realisasi_pembayaran/js/realisasi-pembayaran.js'
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/pembayaran/realisasi_pembayaran/css/realisasi-pembayaran.css'
            ));

            $data = $this->includes;

            $data['title_menu'] = 'Realisasi Pembayaran';

            $mitra = null;
            $perusahaan = $this->get_perusahaan();

            $content['add_form'] = $this->add_form($mitra, $perusahaan);
            $content['riwayat'] = $this->riwayat($mitra, $perusahaan);

            $content['akses'] = $this->hakAkses;
            $data['view'] = $this->load->view('pembayaran/realisasi_pembayaran/index', $content, true);

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
        $perusahaan = $params['perusahaan'];
        $jenis = $params['jenis'];

        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan = "and rp.perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $sql_jenis = "";
        if ( !in_array('all', $jenis) ) {
            $sql_jenis = "and rpd.transaksi in ('".implode("', '", array_map('strtoupper', $jenis))."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.id,
                data.tgl_bayar,
                data.nomor,
                data.perusahaan,
                data.nama_perusahaan,
                data.supplier,
                data.nama_supplier,
                data.ekspedisi,
                data.nama_ekspedisi,
                data.peternak,
                data.nama_mitra,
                data.lampiran,
                data.no_bukti,
                data.jml_transfer,
                data.uang_muka,
                data.cn,
                data.dn,
                data.jenis_transaksi,
                sum(data.jumlah) as jumlah
            from
            (
                select
                    rp.id,
                    rp.tgl_bayar,
                    rp.nomor,
                    rp.perusahaan,
                    prs.perusahaan as nama_perusahaan,
                    rp.supplier,
                    supl.nama as nama_supplier,
                    rp.ekspedisi,
                    eks.nama as nama_ekspedisi,
                    rp.peternak,
                    mtr.nama as nama_mitra,
                    cast(rp.lampiran as varchar(max)) as lampiran,
                    rp.no_bukti,
                    rp.jml_transfer,
                    rp.uang_muka,
                    rp.cn,
                    rp.dn,
                    rpd.transaksi as jenis_transaksi,
                    rpd.bayar as jumlah
                from realisasi_pembayaran_det rpd
                left join
                    realisasi_pembayaran rp
                    on
                        rpd.id_header = rp.id
                left join
                    (
                        select prs1.* from perusahaan prs1
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) prs2
                            on
                                prs1.id = prs2.id
                    ) prs
                    on
                        rp.perusahaan = prs.kode
                left join
                    (
                        select plg1.* from pelanggan plg1
                        right join
                            (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                            on
                                plg1.id = plg2.id
                        where
                            plg1.mstatus = 1
                    ) supl
                    on
                        rp.supplier = supl.nomor
                left join
                    (
                        select eks1.* from ekspedisi eks1
                        right join
                            (select max(id) as id, nomor from ekspedisi group by nomor) eks2
                            on
                                eks1.id = eks2.id
                    ) eks
                    on
                        rp.ekspedisi = eks.nomor
                left join
                    (
                        select mtr1.* from mitra mtr1
                        right join 
                            (select max(id) as id, nomor from mitra group by nomor) mtr2
                            on
                                mtr1.id = mtr2.id
                    ) mtr
                    on
                        rp.peternak = mtr.nomor
                where
                    rp.tgl_bayar between '".$start_date."' and '".$end_date."'
                    ".$sql_perusahaan."
                    ".$sql_jenis."
            ) data
            group by
                data.id,
                data.tgl_bayar,
                data.nomor,
                data.perusahaan,
                data.nama_perusahaan,
                data.supplier,
                data.nama_supplier,
                data.ekspedisi,
                data.nama_ekspedisi,
                data.peternak,
                data.nama_mitra,
                data.lampiran,
                data.no_bukti,
                data.jml_transfer,
                data.uang_muka,
                data.cn,
                data.dn,
                data.jenis_transaksi
            order by
                data.tgl_bayar desc,
                data.nomor desc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/realisasi_pembayaran/list_riwayat', $content, true);

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

    public function get_supplier()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                plg1.* 
            from pelanggan plg1
            right join
                (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                on
                    plg1.id = plg2.id
            where
                plg1.mstatus = 1
        ";
        $d_supl = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_supl->count() > 0 ) {
            $d_supl = $d_supl->toArray();

            foreach ($d_supl as $key => $value) {
                $key = strtoupper($value['nama']).' - '.$value['nomor'];
                $data[ $key ] = array(
                    'nama' => strtoupper($value['nama']),
                    'nomor' => $value['nomor']
                );
            }

            ksort($data);
        }
        
        // $m_supplier = new \Model\Storage\Supplier_model();
        // $nomor_supplier = $m_supplier->select('nomor')->distinct('nomor')->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->get();

        // $data = null;
        // if ( $nomor_supplier->count() > 0 ) {
        //     $nomor_supplier = $nomor_supplier->toArray();

        //     foreach ($nomor_supplier as $k => $val) {
        //         $m_supplier = new \Model\Storage\Supplier_model();
        //         $d_supplier = $m_supplier->where('nomor', $val['nomor'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('version', 'desc')->first();

        //         $key = strtoupper($d_supplier->nama).' - '.$d_supplier['nomor'];
        //         $data[ $key ] = array(
        //             'nama' => strtoupper($d_supplier->nama),
        //             'nomor' => $d_supplier->nomor
        //         );
        //     }

        //     ksort($data);
        // }

        return $data;
    }

    public function get_ekspedisi()
    {
        $data = null;

        $m_ekspedisi = new \Model\Storage\Ekspedisi_model();
        $sql = "
            select 
                eks.id,
                eks.nomor,
                eks.nama
            from ekspedisi eks 
            right join 
                (select max(id) as id, nomor from ekspedisi group by nomor) as e 
                on
                    eks.id = e.id
            where
                eks.mstatus = 1 
            group by
                eks.id,
                eks.nomor,
                eks.nama
            order by eks.nama asc
        ";
        $d_ekspedisi = $m_ekspedisi->hydrateRaw( $sql );
        if ( $d_ekspedisi->count() > 0 ) {
            $data = $d_ekspedisi->toArray();
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

        $sql_kode_unit = null;
        if ( !in_array('all', $params['kode_unit']) ) {
            $sql_kode_unit = "and w.kode in ('".implode("', '", $params['kode_unit'])."')";
        }

        $m_conf = new \Model\Storage\Conf();
        // $sql = "
        //     select 
        //         m.nomor,
        //         m.nama,
        //         w.kode as unit
        //     from kandang k
        //     right join
        //         wilayah w
        //         on
        //             k.unit = w.id
        //     right join
        //         mitra_mapping mm
        //         on
        //             k.mitra_mapping = mm.id
        //     right join
        //         mitra m
        //         on
        //             m.id = mm.mitra
        //     where
        //         w.kode is not null and
        //         m.mstatus = 1
        //         ".$sql_kode_unit."
        //     group by
        //         m.nomor,
        //         m.nama,
        //         w.kode
        //     order by
        //         m.nama asc
        // ";
        $sql = "
            select
                m.nomor,
                m.nama,
                w.kode as unit
            from konfirmasi_pembayaran_peternak kpp
            left join
                (
                    select m1.* from mitra m1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) m2
                        on
                            m1.id = m2.id
                ) m
                on
                    kpp.mitra = m.nomor
            left join
                mitra_mapping mm
                on
                    m.id = mm.mitra
            left join
                kandang k
                on
                    mm.id = k.mitra_mapping
            left join
                wilayah w
                on
                    k.unit = w.id
            where
                kpp.tgl_bayar > '2023-12-31' and
                not exists (
                    select data.* from
                    (
                        select no_bayar, sum(bayar) as bayar from realisasi_pembayaran_det rpd group by no_bayar
                    ) data
                    where 
                        data.no_bayar = kpp.nomor and
                        (kpp.total - data.bayar) < 10
                )
                ".$sql_kode_unit."
            group by
                m.nomor,
                m.nama,
                w.kode
            order by
                m.nama
        ";
        // cetak_r( $sql, 1 );
        $d_mitra = $m_conf->hydrateRaw( $sql );


        $data = null;
        if ( $d_mitra->count() > 0 ) {
            $data = $d_mitra->toArray();
        }

        // $kode_unit = array();
        // $kode_unit_all = null;
        // if ( !empty( $params['kode_unit'] ) ) {
        //     foreach ($params['kode_unit'] as $k_ku => $v_ku) {
        //         if ( stristr($v_ku, 'all') !== FALSE ) {
        //             $kode_unit_all = 'all';

        //             break;
        //         } else {
        //             array_push($kode_unit, $v_ku);
        //         }
        //     }
        // }

        // $m_mitra = new \Model\Storage\Mitra_model();
        // $_d_mitra = $m_mitra->select('nomor')->distinct('nomor')->get();

        // $_data = array();
        // if ( $_d_mitra->count() > 0 ) {
        //     $_d_mitra = $_d_mitra->toArray();
        //     foreach ($_d_mitra as $k_mitra => $v_mitra) {
        //         $d_mitra = $m_mitra->select('nama', 'nomor')->where('nomor', $v_mitra['nomor'])->orderBy('id', 'desc')->first();

        //         $m_mm = new \Model\Storage\MitraMapping_model();
        //         $d_mm = $m_mm->where('nomor', $d_mitra->nomor)->orderBy('id', 'desc')->first();

        //         if ( $d_mm ) {
        //             $m_kdg = new \Model\Storage\Kandang_model();
        //             $d_kdg = $m_kdg->where('mitra_mapping', $d_mm->id)->with(['d_unit'])->first();

        //             $key = $d_mitra->nama.' | '.$d_mitra->nomor;
        //             if ( empty($kode_unit_all) ) {
        //                 foreach ($kode_unit as $k_ku => $v_ku) {
        //                     if ( $v_ku == $d_kdg->d_unit->kode ) {
        //                         $_data[ $key ] = array(
        //                             'nomor' => $d_mitra->nomor,
        //                             'nama' => $d_mitra->nama,
        //                             'unit' => $d_kdg->d_unit->kode
        //                         );
        //                     }
        //                 }
        //             } else {
        //                 $_data[ $key ] = array(
        //                     'nomor' => $d_mitra->nomor,
        //                     'nama' => $d_mitra->nama,
        //                     'unit' => $d_kdg->d_unit->kode
        //                 );
        //             }
        //         }
        //     }

        //     ksort($_data);
        // }

        // $data = array();
        // if ( count( $_data ) ) {
        //     foreach ($_data as $k_data => $v_data) {
        //         $data[] = $v_data;
        //     }
        // }

        $this->result['content'] = $data;

        display_json( $this->result );
    }

    public function get_data_rencana_bayar()
    {
        $params = $this->input->post('params');

        $id = (isset($params['id']) && !empty($params['id'])) ? $params['id'] : null;

        $data = array();
        if ( $params['jenis_pembayaran'] == 'plasma' ) {
            // PETERNAK
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    kpp.*,
                    mtr.nama as nama_mitra
                from konfirmasi_pembayaran_peternak kpp
                left join
                    (
                        select mtr1.* from mitra mtr1
                        right join
                            (select max(id) as id, nomor from mitra group by nomor) mtr2
                            on
                                mtr1.id = mtr2.id
                    ) mtr
                    on
                        mtr.nomor = kpp.mitra
                where
                    kpp.tgl_bayar between '".$params['start_date']."' and '".$params['end_date']."' and
                    kpp.mitra in ('".implode("', '", $params['mitra'])."') and
                    kpp.perusahaan = '".$params['perusahaan']."'
            ";
            $d_kpp = $m_conf->hydrateRaw( $sql );

            if ( $d_kpp->count() > 0 ) {
                $d_kpp = $d_kpp->toArray();

                foreach ($d_kpp as $k_kpp => $v_kpp) {
                    $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
                    $d_rpd = $m_rpd->where('id_header', $id)->where('no_bayar', $v_kpp['nomor'])->first();
                    if ( !empty($id) ) {
                        $bayar = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpp['nomor'])->sum('bayar');
                        $dn = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpp['nomor'])->sum('dn');
                        $cn = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpp['nomor'])->sum('cn');
                        $transfer = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpp['nomor'])->sum('transfer');
                    } else {
                        $bayar = $m_rpd->where('no_bayar', $v_kpp['nomor'])->sum('bayar');
                        $dn = $m_rpd->where('no_bayar', $v_kpp['nomor'])->sum('dn');
                        $cn = $m_rpd->where('no_bayar', $v_kpp['nomor'])->sum('cn');
                        $transfer = $m_rpd->where('no_bayar', $v_kpp['nomor'])->sum('transfer');
                    }

                    $data[] = array(
                        'tgl_bayar' => $v_kpp['tgl_bayar'],
                        'transaksi' => 'PLASMA',
                        'no_bayar' => $v_kpp['nomor'],
                        'no_invoice' => $v_kpp['invoice'],
                        'periode' => $v_kpp['periode'],
                        'nama_penerima' => $v_kpp['nama_mitra'],
                        'tagihan' => $v_kpp['total'],
                        'dn' => $dn,
                        'cn' => $cn,
                        'transfer' => $transfer,
                        'jumlah' => ($v_kpp['total'] > $bayar) ? $v_kpp['total'] - $bayar : 0,
                        'checked' => ($d_rpd) ? true : false
                    );
                }
            }
        } else if ( $params['jenis_pembayaran'] == 'supplier' ) {
            if ( $params['jenis_transaksi'][0] == 'all' ) {
                $doc = $this->get_rencana_pembayaran_doc( $params, $id );
                if ( count($doc) > 0 ) {
                    foreach ($doc as $k => $v) {
                        $data[] = $v;
                    }
                }   
                $pakan = $this->get_rencana_pembayaran_pakan( $params, $id );
                if ( count($pakan) > 0 ) {
                    foreach ($pakan as $k => $v) {
                        $data[] = $v;
                    }
                }
                $voadip = $this->get_rencana_pembayaran_voadip( $params, $id );
                if ( count($voadip) > 0 ) {
                    foreach ($voadip as $k => $v) {
                        $data[] = $v;
                    }
                }
            } else {
                foreach ($params['jenis_transaksi'] as $k_jt => $v_jt) {
                    if ( $v_jt == 'doc' ) {
                        // DOC
                        $doc = $this->get_rencana_pembayaran_doc( $params, $id );
                        if ( count($doc) > 0 ) {
                            foreach ($doc as $k => $v) {
                                $data[] = $v;
                            }
                        }
                    }
                    if ( $v_jt == 'pakan' ) {
                        // PAKAN
                        $pakan = $this->get_rencana_pembayaran_pakan( $params, $id );
                        if ( count($pakan) > 0 ) {
                            foreach ($pakan as $k => $v) {
                                $data[] = $v;
                            }
                        }
                    }
                    if ( $v_jt == 'voadip' ) {
                        // VOADIP
                        $voadip = $this->get_rencana_pembayaran_voadip( $params, $id );
                        if ( count($voadip) > 0 ) {
                            foreach ($voadip as $k => $v) {
                                $data[] = $v;
                            }
                        }
                    }
                }
            }
        } else if ( $params['jenis_pembayaran'] == 'ekspedisi' ) {
            $data = $this->get_rencana_pembayaran_ekspedisi( $params, $id );
        }
        
        $content['data'] = $data;
        $html = $this->load->view('pembayaran/realisasi_pembayaran/list_rencana_pembayaran', $content, true);

        $this->result['html'] = $html;

        display_json( $this->result );
    }

    public function get_rencana_pembayaran_doc($params, $id)
    {
        $data = array();

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select kpd.*, kpdd.kode_unit, supl.nama as nama_supplier from konfirmasi_pembayaran_doc_det kpdd
            left join
                konfirmasi_pembayaran_doc kpd
                on
                    kpdd.id_header = kpd.id
            left join
                (
                    select 
                        plg1.* 
                    from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                        on
                            plg1.id = plg2.id
                ) supl
                on
                    kpd.supplier = supl.nomor
            where
                kpd.tgl_bayar between '".$params['start_date']."' and '".$params['end_date']."' and
                kpd.supplier = '".$params['supplier']."' and
                kpd.perusahaan = '".$params['perusahaan']."'
            group by
                kpd.id,
                kpd.nomor,
                kpd.tgl_bayar,
                kpd.periode,
                kpd.perusahaan,
                kpd.supplier,
                kpd.rekening,
                kpd.total,
                kpd.lunas,
                kpdd.kode_unit,
                supl.nama
        ";
        $d_kpd = $m_conf->hydrateRaw( $sql );

        if ( $d_kpd->count() > 0 ) {
            $d_kpd = $d_kpd->toArray();

            foreach ($d_kpd as $k_kpd => $v_kpd) {
                $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
                $d_rpd = $m_rpd->where('id_header', $id)->where('no_bayar', $v_kpd['nomor'])->first();
                if ( !empty($id) ) {
                    $bayar = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpd['nomor'])->sum('bayar');
                    $dn = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpd['nomor'])->sum('dn');
                    $cn = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpd['nomor'])->sum('cn');
                    $transfer = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpd['nomor'])->sum('transfer');
                } else {
                    $bayar = $m_rpd->where('no_bayar', $v_kpd['nomor'])->sum('bayar');
                    $dn = $m_rpd->where('no_bayar', $v_kpd['nomor'])->sum('dn');
                    $cn = $m_rpd->where('no_bayar', $v_kpd['nomor'])->sum('cn');
                    $transfer = $m_rpd->where('no_bayar', $v_kpd['nomor'])->sum('transfer');
                }

                $data[] = array(
                    'tgl_bayar' => $v_kpd['tgl_bayar'],
                    'transaksi' => 'DOC',
                    'no_bayar' => $v_kpd['nomor'],
                    'periode' => $v_kpd['periode'],
                    'nama_penerima' => $v_kpd['nama_supplier'],
                    'tagihan' => $v_kpd['total'],
                    'dn' => $dn,
                    'cn' => $cn,
                    'transfer' => $transfer,
                    'bayar' => $bayar,
                    'jumlah' => ($v_kpd['total'] > $bayar) ? $v_kpd['total'] - $bayar : 0,
                    'kode_unit' => $v_kpd['kode_unit'],
                    'checked' => ($d_rpd) ? true : false
                );
            }
        }

        return $data;
    }

    public function get_rencana_pembayaran_pakan($params, $id)
    {
        $data = array();

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select kpp.*, kppd.kode_unit, supl.nama as nama_supplier from konfirmasi_pembayaran_pakan_det kppd
            left join
                konfirmasi_pembayaran_pakan kpp
                on
                    kppd.id_header = kpp.id
            left join
                (
                    select 
                        plg1.* 
                    from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                        on
                            plg1.id = plg2.id
                ) supl
                on
                    kpp.supplier = supl.nomor
            where
                kpp.tgl_bayar between '".$params['start_date']."' and '".$params['end_date']."' and
                kpp.supplier = '".$params['supplier']."' and
                kpp.perusahaan = '".$params['perusahaan']."'
            group by
                kpp.id,
                kpp.nomor,
                kpp.tgl_bayar,
                kpp.periode,
                kpp.perusahaan,
                kpp.supplier,
                kpp.rekening,
                kpp.total,
                kpp.lunas,
                kpp.invoice,
                kppd.kode_unit,
                supl.nama
            order by
                kppd.kode_unit asc,
                kpp.invoice asc
        ";
        $d_kpp = $m_conf->hydrateRaw( $sql );

        if ( $d_kpp->count() > 0 ) {
            $d_kpp = $d_kpp->toArray();

            foreach ($d_kpp as $k_kpp => $v_kpp) {
                $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
                $d_rpd = $m_rpd->where('id_header', $id)->where('no_bayar', $v_kpp['nomor'])->first();
                if ( !empty($id) ) {
                    $bayar = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpp['nomor'])->sum('bayar');
                    $dn = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpp['nomor'])->sum('dn');
                    $cn = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpp['nomor'])->sum('cn');
                    $transfer = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpp['nomor'])->sum('transfer');
                } else {
                    $bayar = $m_rpd->where('no_bayar', $v_kpp['nomor'])->sum('bayar');
                    $dn = $m_rpd->where('no_bayar', $v_kpp['nomor'])->sum('dn');
                    $cn = $m_rpd->where('no_bayar', $v_kpp['nomor'])->sum('cn');
                    $transfer = $m_rpd->where('no_bayar', $v_kpp['nomor'])->sum('transfer');
                }

                $data[] = array(
                    'tgl_bayar' => $v_kpp['tgl_bayar'],
                    'transaksi' => 'PAKAN',
                    'no_bayar' => $v_kpp['nomor'],
                    'no_invoice' => $v_kpp['invoice'],
                    'periode' => $v_kpp['periode'],
                    'nama_penerima' => $v_kpp['nama_supplier'],
                    'tagihan' => $v_kpp['total'],
                    'dn' => $dn,
                    'cn' => $cn,
                    'transfer' => $transfer,
                    'bayar' => $bayar,
                    'jumlah' => ($v_kpp['total'] > $bayar) ? $v_kpp['total'] - $bayar : 0,
                    'kode_unit' => $v_kpp['kode_unit'],
                    'checked' => ($d_rpd) ? true : false
                );
            }
        }

        return $data;
    }

    public function get_rencana_pembayaran_voadip($params, $id)
    {
        $data = array();

        $sql_unit = "";
        if ( !in_array('all', $params['kode_unit_ovk']) ) {
            $sql_unit = "and kpvd.kode_unit in ('".implode("', '", $params['kode_unit_ovk'])."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select kpv.*, kpvd.kode_unit, supl.nama as nama_supplier from konfirmasi_pembayaran_voadip_det kpvd
            left join
                konfirmasi_pembayaran_voadip kpv
                on
                    kpvd.id_header = kpv.id
            left join
                (
                    select 
                        plg1.* 
                    from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                        on
                            plg1.id = plg2.id
                ) supl
                on
                    kpv.supplier = supl.nomor
            where
                kpv.tgl_bayar between '".$params['start_date']."' and '".$params['end_date']."' and
                kpv.supplier = '".$params['supplier']."' and
                kpv.perusahaan = '".$params['perusahaan']."'
                ".$sql_unit."
            group by
                kpv.id,
                kpv.nomor,
                kpv.tgl_bayar,
                kpv.periode,
                kpv.perusahaan,
                kpv.supplier,
                kpv.rekening,
                kpv.total,
                kpv.lunas,
                kpvd.kode_unit,
                supl.nama
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_kpv = $d_conf->toArray();

            foreach ($d_kpv as $k_kpv => $v_kpv) {
                $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
                $d_rpd = $m_rpd->where('id_header', $id)->where('no_bayar', $v_kpv['nomor'])->first();
                if ( !empty($id) ) {
                    $bayar = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpv['nomor'])->sum('bayar');
                    $dn = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpv['nomor'])->sum('dn');
                    $cn = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpv['nomor'])->sum('cn');
                    $transfer = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpv['nomor'])->sum('transfer');
                } else {
                    $bayar = $m_rpd->where('no_bayar', $v_kpv['nomor'])->sum('bayar');
                    $dn = $m_rpd->where('no_bayar', $v_kpv['nomor'])->sum('dn');
                    $cn = $m_rpd->where('no_bayar', $v_kpv['nomor'])->sum('cn');
                    $transfer = $m_rpd->where('no_bayar', $v_kpv['nomor'])->sum('transfer');
                }

                $data[] = array(
                    'tgl_bayar' => $v_kpv['tgl_bayar'],
                    'transaksi' => 'VOADIP',
                    'no_bayar' => $v_kpv['nomor'],
                    'periode' => $v_kpv['periode'],
                    'nama_penerima' => $v_kpv['nama_supplier'],
                    'tagihan' => $v_kpv['total'],
                    'bayar' => $bayar,
                    'cn' => $cn,
                    'dn' => $bayar,
                    'transfer' => $transfer,
                    'kode_unit' => $v_kpv['kode_unit'],
                    'jumlah' => ($v_kpv['total'] > $bayar) ? $v_kpv['total'] - $bayar : 0,
                    'checked' => ($d_rpd) ? true : false
                );
            }
        }

        return $data;
    }

    public function get_rencana_pembayaran_ekspedisi($params, $id)
    {
        $data = array();

        $m_kpoap = new \Model\Storage\KonfirmasiPembayaranOaPakan_model();
        $d_kpoap = $m_kpoap->whereBetween('tgl_bayar', [$params['start_date'], $params['end_date']])
                       ->where('ekspedisi_id', $params['ekspedisi'])
                       ->where('perusahaan', $params['perusahaan'])->get();

        if ( $d_kpoap->count() > 0 ) {
            $d_kpoap = $d_kpoap->toArray();

            foreach ($d_kpoap as $k_kpoap => $v_kpoap) {
                $m_ekspedisi = new \Model\Storage\Ekspedisi_model();
                $sql = "
                    select 
                        eks.id,
                        eks.nomor,
                        eks.nama
                    from ekspedisi eks 
                    right join 
                        (select max(id) as id, nomor from ekspedisi group by nomor) as e 
                        on
                            eks.id = e.id
                    where
                        eks.mstatus = 1 and
                        eks.nomor = '".$params['ekspedisi']."' 
                    group by
                        eks.id,
                        eks.nomor,
                        eks.nama
                    order by eks.nama asc
                ";
                $d_ekspedisi = $m_ekspedisi->hydrateRaw( $sql );
                if ( $d_ekspedisi->count() > 0 ) {
                    $d_ekspedisi = $d_ekspedisi->toArray();
                }

                $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
                $d_rpd = $m_rpd->where('id_header', $id)->where('no_bayar', $v_kpoap['nomor'])->first();
                if ( !empty($id) ) {
                    $bayar = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpoap['nomor'])->sum('bayar');
                    $dn = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpoap['nomor'])->sum('dn');
                    $cn = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpoap['nomor'])->sum('cn');
                    $transfer = $m_rpd->where('id_header', '<>', $id)->where('no_bayar', $v_kpoap['nomor'])->sum('transfer');
                } else {
                    $bayar = $m_rpd->where('no_bayar', $v_kpoap['nomor'])->sum('bayar');
                    $dn = $m_rpd->where('no_bayar', $v_kpoap['nomor'])->sum('dn');
                    $cn = $m_rpd->where('no_bayar', $v_kpoap['nomor'])->sum('cn');
                    $transfer = $m_rpd->where('no_bayar', $v_kpoap['nomor'])->sum('transfer');
                }

                $data[] = array(
                    'tgl_bayar' => $v_kpoap['tgl_bayar'],
                    'transaksi' => 'OA PAKAN',
                    'no_bayar' => $v_kpoap['nomor'],
                    'periode' => $v_kpoap['periode'],
                    'nama_penerima' => $d_ekspedisi[0]['nama'],
                    'tagihan' => $v_kpoap['total'],
                    'dn' => $dn,
                    'cn' => $cn,
                    'transfer' => $transfer,
                    'bayar' => $bayar,
                    'jumlah' => ($v_kpoap['total'] > $bayar) ? $v_kpoap['total'] - $bayar : 0,
                    'checked' => ($d_rpd) ? true : false
                );
            }
        }

        return $data;
    }

    public function riwayat($mitra, $perusahaan)
    {
        $content['unit'] = $this->get_unit();
        $content['mitra'] = $mitra;
        $content['perusahaan'] = $perusahaan;
        $html = $this->load->view('pembayaran/realisasi_pembayaran/riwayat', $content, true);

        return $html;
    }

    public function add_form($mitra, $perusahaan)
    {
        $content['unit'] = $this->get_unit();
        $content['supplier'] = $this->get_supplier();
        $content['ekspedisi'] = $this->get_ekspedisi();
        $content['perusahaan'] = $perusahaan;
        $html = $this->load->view('pembayaran/realisasi_pembayaran/add_form', $content, true);

        return $html;
    }

    public function detail_form($id)
    {
        $m_rp = new \Model\Storage\RealisasiPembayaran_model();
        $d_rp = $m_rp->where('id', $id)->with(['d_perusahaan', 'd_supplier', 'd_mitra', 'd_ekspedisi', 'detail', 'dn_realisasi_pembayaran', 'cn_realisasi_pembayaran', 'logs'])->first();

        $data = null;
        if ( $d_rp ) {
            $d_rp = $d_rp->toArray();

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    rpp.id,
                    rpp.id_header,
                    rpp.nominal,
                    djt.id as det_jurnal_trans_id,
                    djt.sumber_coa,
                    djt.nama
                from realisasi_pembayaran_potongan rpp
                left join
                    det_jurnal_trans _djt
                    on
                        rpp.det_jurnal_trans_id = _djt.id
                left join
                    jurnal_trans jt
                    on
                        jt.id = _djt.id_header
                left join
                    (
                        select 
                            djt.id, 
                            djt.id_header,
                            djt.sumber_coa, 
                            djt.tujuan_coa, 
                            djt.nama,
                            jt.nama as nama_aktif,
                            jt.kode
                        from det_jurnal_trans djt
                        left join
                            jurnal_trans jt
                            on
                                djt.id_header = jt.id
                        left join
                            coa c_sumber
                            on
                                djt.sumber_coa = c_sumber.coa 
                        left join
                            coa c_tujuan
                            on
                                djt.tujuan_coa = c_tujuan.coa
                        where
                            jt.mstatus = 1
                        group by
                            djt.id, 
                            djt.id_header,
                            djt.sumber_coa, 
                            djt.tujuan_coa, 
                            djt.nama,
                            jt.nama,
                            jt.kode
                    ) djt
                    on
                        djt.sumber_coa = _djt.sumber_coa and
                        djt.tujuan_coa = _djt.tujuan_coa 
                where
                    jt.kode = djt.kode and
                    rpp.nominal > 0 and
                    rpp.id_header = ".$id."
                group by
                    rpp.id,
                    rpp.id_header,
                    rpp.nominal,
                    djt.id,
                    djt.sumber_coa,
                    djt.nama
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $d_potongan = null;
            if ( $d_conf->count() > 0 ) {
                $d_potongan = $d_conf->toArray();
            }

            $jumlah = 0;
            $jenis_transaksi = null;
            foreach ($d_rp['detail'] as $k_det => $v_det) {
                $jumlah += $v_det['bayar'];
                $jenis_transaksi[] = $v_det['transaksi'];
            }

            $log = !empty($d_rp['logs']) ? $d_rp['logs'][ count($d_rp['logs'])-1 ] : null;
            $start_date = prev_date(date('Y-m-d')).' 00:00:00';
            $end_date = date('Y-m-d').' 23:59:59';

            $delete = 1;

            $jenis_pembayaran = null;
            if ( !empty($d_rp['supplier']) ) {
                $jenis_pembayaran = 'SUPPLIER';
            } else if ( !empty($d_rp['peternak']) ) {
                $jenis_pembayaran = 'PLASMA';
            } else if ( !empty($d_rp['ekspedisi']) ) {
                $jenis_pembayaran = 'EKSPEDISI';
            }

            $detail = null;
            foreach ($d_rp['detail'] as $k_det => $v_det) {
                $kode_unit = $this->getKodeUnit($v_det['transaksi'], $v_det['no_bayar']);

                if ( $v_det['transaksi'] == 'PAKAN' || $v_det['transaksi'] == 'PLASMA' ) {
                    $table_name = null;
                    if ( $v_det['transaksi'] == 'PAKAN' ) {
                        $table_name = 'konfirmasi_pembayaran_pakan';
                    }

                    if ( $v_det['transaksi'] == 'PLASMA' ) {
                        $table_name = 'konfirmasi_pembayaran_peternak';
                    }

                    $m_conf = new \Model\Storage\Conf();
                    $sql = "
                        select top 1
                            kpp.*
                        from ".$table_name." kpp
                        where
                            kpp.nomor = '".$v_det['no_bayar']."'
                    ";
                    $d_conf = $m_conf->hydrateRaw( $sql );

                    $invoice = null;
                    if ( $d_conf->count() > 0 ) {
                        $d_kpp = $d_conf->toArray()[0];

                        $invoice = $d_kpp['invoice'];
                    }

                    $detail[] = array(
                        'id_header' => $v_det['id_header'],
                        'transaksi' => $v_det['transaksi'],
                        'no_bayar' => !empty($invoice) ? $invoice: $v_det['no_bayar'],
                        'tagihan' => $v_det['tagihan'],
                        'dn' => $v_det['dn'],
                        'cn' => $v_det['cn'],
                        'transfer' => $v_det['transfer'],
                        'bayar' => $v_det['bayar'],
                        'kode_unit' => $kode_unit
                    );
                } else {
                    $detail[] = array(
                        'id_header' => $v_det['id_header'],
                        'transaksi' => $v_det['transaksi'],
                        'no_bayar' => $v_det['no_bayar'],
                        'tagihan' => $v_det['tagihan'],
                        'dn' => $v_det['dn'],
                        'cn' => $v_det['cn'],
                        'transfer' => $v_det['transfer'],
                        'bayar' => $v_det['bayar'],
                        'kode_unit' => $kode_unit
                    );
                }
            }

            $data = array(
                'id' => $d_rp['id'],
                'tgl_bayar' => $d_rp['tgl_bayar'],
                'no_bayar' => $d_rp['nomor'],
                'jml_transfer' => $d_rp['jml_transfer'],
                'total_potongan' => $d_rp['potongan'],
                'dn' => $d_rp['dn'],
                'cn' => $d_rp['cn'],
                'uang_muka' => $d_rp['uang_muka'],
                'jumlah_bayar' => $jumlah,
                'jenis_pembayaran' => $jenis_pembayaran,
                'jenis_transaksi' => implode(', ', $jenis_transaksi),
                'supplier' => $d_rp['d_supplier']['nama'],
                'peternak' => $d_rp['d_mitra']['nama'],
                'ekspedisi' => $d_rp['d_ekspedisi']['nama'],
                'perusahaan' => $d_rp['d_perusahaan']['perusahaan'],
                'detail' => $detail,
                'dn_realisasi_pembayaran' => $d_rp['dn_realisasi_pembayaran'],
                'cn_realisasi_pembayaran' => $d_rp['cn_realisasi_pembayaran'],
                'potongan' => $d_potongan,
                'delete' => $delete
            );
        }

        $content['akses'] = $this->hakAkses;
        $content['data'] = $data;
        $html = $this->load->view('pembayaran/realisasi_pembayaran/detail_form', $content, true);

        return $html;
    }

    public function edit_form($id, $perusahaan)
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                rpd.id_header as id,
                rp.supplier,
                rp.peternak,
                rp.ekspedisi,
                rp.perusahaan,
                case
                    when rpd.transaksi like 'plasma' then
                        'peternak'
                    else
                        LOWER(rpd.transaksi)
                end as jenis_transaksi,
                case
                    when rpd.transaksi like 'pakan' or rpd.transaksi like 'doc' or rpd.transaksi like 'voadip' then
                        'supplier'
                    when rpd.transaksi like 'plasma' then
                        'plasma'
                    else
                        'ekspedisi'
                end as jenis_pembayaran,
                min(kp.tgl_bayar) as start_date, 
                max(kp.tgl_bayar) as end_date 
            from realisasi_pembayaran_det rpd
            left join
                realisasi_pembayaran rp 
                on
                    rpd.id_header = rp.id
            left join
                (
                    select nomor, tgl_bayar from konfirmasi_pembayaran_doc kpd 
                    
                    union all
                    
                    select nomor, tgl_bayar from konfirmasi_pembayaran_pakan kpp 
                    
                    union all
                    
                    select nomor, tgl_bayar from konfirmasi_pembayaran_voadip kpv
                    
                    union all
                    
                    select nomor, tgl_bayar from konfirmasi_pembayaran_peternak kpp
                    
                    union all
                    
                    select nomor, tgl_bayar from konfirmasi_pembayaran_oa_pakan kpop
                ) kp
                on
                    kp.nomor = rpd.no_bayar
            where
                rpd.id_header = ".$id."
            group by
                rpd.id_header,
                rp.supplier,
                rp.peternak,
                rp.ekspedisi,
                rp.perusahaan,
                rpd.transaksi
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        $kode_unit = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray()[0];

            if ( $data['jenis_transaksi'] == 'peternak' ) {
                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select w.kode as kode_unit from konfirmasi_pembayaran_peternak_det2 kppd2
                    left join
                        (
                            select mm.nim, k.kandang, k.unit from
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            left join
                                kandang k
                                on
                                    k.mitra_mapping = mm.id
                            group by
                                mm.nim, k.kandang, k.unit
                        ) kdg
                        on
                            select SUBSTRING(kppd2.noreg, 1, 7) = kdg.nim and
                            kppd2.kandang = kdg.kandang
                    left join
                        wilayah w
                        on
                            w.id = kdg.unit
                    left join
                        konfirmasi_pembayaran_peternak_det kppd
                        on
                            kppd2.id_header = kppd.id
                    left join
                        konfirmasi_pembayaran_peternak kpp
                        on
                            kppd.id_header = kpp.id
                    left join
                        realisasi_pembayaran_det rpd
                        on
                            kpp.nomor = rpd.no_bayar
                    where
                        rpd.id_header = ".$id."
                    group by
                        w.kode as kode_unit
                ";
                $d_unit = $m_conf->hydrateRaw( $sql );

                if ( $d_unit->count() > 0 ) {
                    $d_unit = $d_unit->toArray();

                    foreach ($d_unit as $k_unit => $v_unit) {
                        $kode_unit[] = $v_unit['kode_unit'];
                    }
                }
            }

            if ( $data['jenis_transaksi'] == 'voadip' ) {
                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select kpvd.kode_unit from konfirmasi_pembayaran_voadip_det kpvd
                    left join
                        konfirmasi_pembayaran_voadip kpv
                        on
                            kpvd.id_header = kpv.id
                    left join
                        realisasi_pembayaran_det rpd
                        on
                            kpv.nomor = rpd.no_bayar
                    where
                        rpd.id_header = ".$id."
                    group by
                        kpvd.kode_unit
                ";
                $d_unit = $m_conf->hydrateRaw( $sql );

                if ( $d_unit->count() > 0 ) {
                    $d_unit = $d_unit->toArray();

                    foreach ($d_unit as $k_unit => $v_unit) {
                        $kode_unit[] = $v_unit['kode_unit'];
                    }
                }
            }
        }

        $content['unit'] = $this->get_unit();
        $content['supplier'] = $this->get_supplier();
        $content['ekspedisi'] = $this->get_ekspedisi();
        $content['perusahaan'] = $perusahaan;
        $content['data'] = $data;
        $content['kode_unit'] = $kode_unit;
        $html = $this->load->view('pembayaran/realisasi_pembayaran/edit_form', $content, true);

        return $html;
    }

    public function getKodeUnit($transaksi, $no_bayar) {
        $kode_unit = null;
        if ( stristr($transaksi, 'doc') !== false ) {
            $sql = "
                select kpd.*, kpdd.kode_unit from konfirmasi_pembayaran_doc_det kpdd
                left join
                    konfirmasi_pembayaran_doc kpd
                    on
                        kpdd.id_header = kpd.id
                where
                    kpd.nomor = '".$no_bayar."'
                group by
                    kpd.id, kpd.nomor, kpd.tgl_bayar, kpd.periode, kpd.perusahaan, kpd.supplier, kpd.rekening, kpd.total, kpd.lunas, kpdd.kode_unit
                    --, supl.nama
            ";
        }
        if ( stristr($transaksi, 'pakan') !== false ) {
            if ( stristr($transaksi, 'oa pakan') !== false ) {
                $sql = null;
            } else {
                $sql = "
                    select kpp.*, kppd.kode_unit from konfirmasi_pembayaran_pakan_det kppd
                    left join
                        konfirmasi_pembayaran_pakan kpp
                        on
                            kppd.id_header = kpp.id
                    where
                        kpp.nomor = '".$no_bayar."'
                    group by
                        kpp.id, kpp.nomor, kpp.tgl_bayar, kpp.periode, kpp.perusahaan, kpp.supplier, kpp.rekening, kpp.total, kpp.lunas, kpp.invoice, kppd.kode_unit
                ";
            }
        }
        if ( stristr($transaksi, 'voadip') !== false ) {
            $sql = "
                select kpv.*, kpvd.kode_unit from konfirmasi_pembayaran_voadip_det kpvd
                left join
                    konfirmasi_pembayaran_voadip kpv
                    on
                        kpvd.id_header = kpv.id
                where
                    kpv.nomor = '".$no_bayar."'
                group by
                    kpv.id, kpv.nomor, kpv.tgl_bayar, kpv.periode, kpv.perusahaan, kpv.supplier, kpv.rekening, kpv.total, kpv.lunas, kpvd.kode_unit
            ";
        }
        if ( stristr($transaksi, 'peternak') !== false ) {
            $sql = null;
        }

        if ( !empty($sql) ) {
            $m_conf = new \Model\Storage\Conf();
            $d_data = $m_conf->hydrateRaw( $sql );

            if ( $d_data->count() > 0 ) {
                $d_data = $d_data->toArray();
                $kode_unit = $d_data[0]['kode_unit'];
            }
        }

        return $kode_unit;
    }

    public function realisasi_pembayaran()
    {
        $params = $this->input->get('params');

        $id = (isset($params['id']) && !empty($params['id'])) ? $params['id'] : null;

        $jenis_transaksi = $params['jenis_transaksi'];
        $form_uang_muka = 0;
        if ( in_array('doc', $jenis_transaksi) || in_array('pakan', $jenis_transaksi) ) {
            $form_uang_muka = 1;
        }

        $data = null;

        $total_potongan = 0;
        $total = 0;
        $total_bayar = 0;
        $detail = null;
        foreach ($params['detail'] as $k_det => $v_det) {
            $total += $v_det['tagihan'];

            $bayar = 0;
            $cn = 0;
            $dn = 0;
            $transfer = 0;

            if ( !empty($id) ) {
                $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
                $d_rpd = $m_rpd->where('no_bayar', $v_det['no_bayar'])->where('id_header', $id)->first();

                if ( !empty($d_rpd) ) {
                    $bayar = $d_rpd->bayar;
                    $transfer = $d_rpd->transfer;
                    $cn = $d_rpd->cn;
                    $dn = $d_rpd->dn;
                    $total_bayar += $d_rpd->bayar;
                }
            }

            $detail[] = array(
                'transaksi' => $v_det['transaksi'],
                'no_bayar' => $v_det['no_bayar'],
                'tagihan' => $v_det['tagihan'],
                'dn' => $dn,
                'cn' => $cn,
                'transfer' => $transfer,
                'bayar' => $bayar
            );
        }

        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $d_perusahaan = $m_perusahaan->where('kode', $params['perusahaan'])->orderBy('version', 'desc')->first();

        $nomor = null;
        $tgl_bayar = null;
        $rekening = null;
        $no_bukti = null;
        $lampiran = null;
        $jml_transfer = 0;
        $uang_muka = 0;
        $total_dn = 0;
        $total_cn = 0;

        $d_cn = null;
        $d_dn = null;
        if ( !empty($id) ) {
            $m_rp = new \Model\Storage\RealisasiPembayaran_model();
            $d_rp = $m_rp->where('id', $id)->first();

            $nomor = $d_rp->nomor;
            $tgl_bayar = $d_rp->tgl_bayar;
            $rekening = $d_rp->no_rek;
            $no_bukti = $d_rp->no_bukti;
            $lampiran = $d_rp->lampiran;
            $jml_transfer = $d_rp->jml_transfer;
            $uang_muka = $d_rp->uang_muka;
            $total_dn = $d_rp->dn;
            $total_cn = $d_rp->cn;

            $m_rpcn = new \Model\Storage\RealisasiPembayaranCn_model();
            $d_rpcn = $m_rpcn->where('id_header', $id)->get();
            if ( $d_rpcn->count() > 0 ) {
                $d_cn = $d_rpcn->toArray();
            }

            $m_rpdn = new \Model\Storage\RealisasiPembayaranDn_model();
            $d_rpdn = $m_rpdn->where('id_header', $id)->get();
            if ( $d_rpdn->count() > 0 ) {
                $d_dn = $d_rpdn->toArray();
            }
        }

        $d_supplier = null;
        $d_mitra = null;
        $ekspedisi = null;
        $bank_ekspedisi = null;
        if ( stristr($params['jenis_pembayaran'], 'supplier') !== false ) {
            $m_supplier = new \Model\Storage\Supplier_model();
            $d_supplier = $m_supplier->where('nomor', $params['supplier'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('version', 'desc')->with(['banks'])->first();
        } else if ( stristr($params['jenis_pembayaran'], 'plasma') !== false ) {
            $m_mitra = new \Model\Storage\Mitra_model();
            $d_mitra = $m_mitra->where('nomor', $params['peternak'])->orderBy('version', 'desc')->first();

            $rekening = $d_mitra->rekening_nomor.' - '.$d_mitra->bank;
        } else if ( stristr($params['jenis_pembayaran'], 'ekspedisi') !== false ) {
            $m_ekspedisi = new \Model\Storage\Ekspedisi_model();
            $sql = "
                select 
                    eks.id,
                    eks.nomor,
                    eks.nama
                from ekspedisi eks 
                right join 
                    (select max(id) as id, nomor from ekspedisi group by nomor) as e 
                    on
                        eks.id = e.id
                where
                    eks.mstatus = 1 and
                    eks.nomor = '".$params['ekspedisi']."' 
                group by
                    eks.id,
                    eks.nomor,
                    eks.nama
                order by eks.nama asc
            ";
            $d_ekspedisi = $m_ekspedisi->hydrateRaw( $sql );
            if ( $d_ekspedisi->count() > 0 ) {
                $ekspedisi = $d_ekspedisi->toArray();
            }

            $m_bank_ekspedisi = new \Model\Storage\BankEkspedisi_model();
            $sql = "
                select be.* from bank_ekspedisi be
                right join
                    (
                        select e1.* from ekspedisi e1
                        right join
                            (select max(id) as id, nomor from ekspedisi group by nomor) e2
                            on
                                e1.id = e2.id

                    ) eks
                    on
                        be.ekspedisi_id = eks.id
                where
                    eks.nomor = '".$params['ekspedisi']."'
            ";
            $d_bank_ekspedisi = $m_bank_ekspedisi->hydrateRaw( $sql );
            if ( $d_bank_ekspedisi->count() > 0 ) {
                $bank_ekspedisi = $d_bank_ekspedisi->toArray();
            }
        }

        $data = array(
            'id' => !empty($id) ? $id : null,
            'jenis_pembayaran' => $params['jenis_pembayaran'],
            'uang_muka' => $uang_muka,
            'jml_transfer' => $jml_transfer,
            'total_dn' => $total_dn,
            'total_cn' => $total_cn,
            'total_potongan' => $total_potongan,
            'total' => $total,
            'total_bayar' => $total_bayar,
            'nomor' => $nomor,
            'tgl_bayar' => $tgl_bayar,
            'rekening' => $rekening,
            'no_bukti' => $no_bukti,
            'lampiran' => $lampiran,
            'no_perusahaan' => $d_perusahaan->kode,
            'perusahaan' => $d_perusahaan->perusahaan,
            'no_supplier' => !empty($d_supplier) ? $d_supplier->nomor : null,
            'supplier' => !empty($d_supplier) ? $d_supplier->nama : null,
            'bank_supplier' => !empty($d_supplier) ? $d_supplier->banks : null,
            'no_peternak' => !empty($d_mitra) ? $d_mitra->nomor : null,
            'peternak' => !empty($d_mitra) ? $d_mitra->nama : null,
            'no_ekspedisi' => !empty($ekspedisi) ? $ekspedisi[0]['nomor'] : null,
            'ekspedisi' => !empty($ekspedisi) ? $ekspedisi[0]['nama'] : null,
            'bank_ekspedisi' => $bank_ekspedisi,
            'form_uang_muka' => $form_uang_muka,
            'detail' => $detail
        );

        $content['data'] = $data;
        $content['d_cn'] = !empty($d_cn) ? json_encode($d_cn) : null;
        $content['d_dn'] = !empty($d_dn) ? json_encode($d_dn) : null;
        $html = $this->load->view('pembayaran/realisasi_pembayaran/realisasi_pembayaran', $content, true);

        echo $html;
    }

    public function save()
    {
        $data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

        try {
            $jenis_transaksi = null;

            $file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];
            }
            if ($isMoved) {
                $file_name = $moved['name'];
                $path_name = $moved['path'];

                $m_rp = new \Model\Storage\RealisasiPembayaran_model();
                $nomor = $m_rp->getNextNomor();

                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select prs1.* from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                    where
                        prs1.kode = '".$data['perusahaan']."'
                ";
                $d_conf = $m_conf->hydrateRaw( $sql );

                $no_bukti_auto = null;
                if ( $d_conf->count() > 0 ) {
                    $d_conf = $d_conf->toArray()[0];

                    $kode = $d_conf['kode_auto'].'-'.substr($d_conf['rekening'], 1, 3).'/BBM';
                    $no_bukti_auto = $m_rp->getNextNomorAuto( $kode );
                }

                $m_rp->nomor = $nomor;
                $m_rp->no_bukti_auto = $no_bukti_auto;
                $m_rp->tgl_bayar = $data['tgl_bayar'];
                $m_rp->perusahaan = $data['perusahaan'];
                $m_rp->supplier = isset($data['supplier']) ? $data['supplier'] : null;
                $m_rp->peternak = isset($data['peternak']) ? $data['peternak'] : null;
                $m_rp->no_rek = isset($data['no_rek']) ? $data['no_rek'] : null;
                $m_rp->no_bukti = $data['no_bukti'];
                $m_rp->lampiran = $path_name;
                $m_rp->dn = isset($data['total_dn']) ? $data['total_dn'] : 0;
                $m_rp->cn = isset($data['total_cn']) ? $data['total_cn'] : 0;
                $m_rp->jml_transfer = $data['jml_transfer'];
                $m_rp->jml_bayar = $data['bayar'];
                $m_rp->keterangan = isset($data['keterangan']) ? $data['keterangan'] : null;
                $m_rp->ekspedisi = isset($data['ekspedisi']) ? $data['ekspedisi'] : null;
                $m_rp->potongan = $data['total_potongan'];
                $m_rp->uang_muka = $data['uang_muka'];
                $m_rp->save();

                $potongan = $data['total_potongan'];
                $uang_muka = $data['uang_muka'];

                $id = $m_rp->id;
                foreach ($data['detail'] as $k_det => $v_det) {
                    $jenis_transaksi = $v_det['transaksi'];

                    $bayar = $v_det['bayar'];
                    $nominal_potongan = 0;
                    $nominal_uang_muka = 0;

                    if ( $potongan > 0 || $uang_muka > 0 ) {
                        if ( $potongan > 0 && $bayar > 0 ) {
                            if ( $bayar <= $potongan ) {
                                $potongan -= $bayar;
                                $nominal_potongan = $bayar;
                                $bayar = 0;
                            } else {
                                $bayar -= $potongan;
                                $nominal_potongan = $potongan;
                                $potongan = 0;
                            }
                        }

                        if ( $uang_muka > 0 && $bayar > 0 ) {
                            if ( $bayar <= $uang_muka ) {
                                $uang_muka -= $bayar;
                                $nominal_uang_muka = $bayar;
                                $bayar = 0;
                            } else {
                                $bayar -= $uang_muka;
                                $nominal_uang_muka = $uang_muka;
                                $uang_muka = 0;
                            }
                        }
                    }

                    $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
                    $m_rpd->id_header = $id;
                    $m_rpd->transaksi = $v_det['transaksi'];
                    $m_rpd->no_bayar = $v_det['no_bayar'];
                    $m_rpd->tagihan = $v_det['tagihan'];
                    $m_rpd->bayar = $v_det['bayar'];
                    $m_rpd->cn = $v_det['cn'];
                    $m_rpd->dn = $v_det['dn'];
                    $m_rpd->potongan = $nominal_potongan;
                    $m_rpd->uang_muka = $nominal_uang_muka;
                    $m_rpd->transfer = $v_det['transfer'];
                    $m_rpd->save();
                }

                if ( isset($data['dn']) && !empty($data['dn']) ) {
                    foreach ($data['dn'] as $k_dn => $v_dn) {
                        $m_rpd = new \Model\Storage\RealisasiPembayaranDn_model();
                        $m_rpd->id_header = $id;
                        $m_rpd->saldo = $v_dn['saldo'];
                        $m_rpd->sisa_saldo = $v_dn['sisa_saldo'];
                        $m_rpd->pakai = $v_dn['pakai'];
                        $m_rpd->id_dn = $v_dn['id'];
                        $m_rpd->save();

                        foreach ($v_dn['detail'] as $k_det => $v_det) {
                            $m_conf = new \Model\Storage\Conf();
                            $sql = "
                                select * from dn where id = ".$v_det['id_dn']."
                            ";
                            $d_dn = $m_conf->hydrateRaw( $sql );

                            $m_conf = new \Model\Storage\Conf();
                            $sql = "
                                select * from realisasi_pembayaran_det where no_bayar = '".$k_det."' and id_header = ".$id."
                            ";
                            $d_det = $m_conf->hydrateRaw( $sql );

                            if ( $d_dn->count() > 0 && $d_det->count() > 0 ) {
                                $d_dn = $d_dn->toArray()[0];
                                $d_det = $d_det->toArray()[0];

                                $m_rpdcd = new \Model\Storage\RealisasiPembayaranDetCnDn_model();
                                $m_rpdcd->id_header = $d_det['id'];
                                $m_rpdcd->nomor_cn_dn = $d_dn['nomor'];
                                $m_rpdcd->nominal = $v_det['jml_bayar'];
                                $m_rpdcd->save();
                            }
                        }
                    }
                }

                if ( isset($data['cn']) && !empty($data['cn']) ) {
                    foreach ($data['cn'] as $k_cn => $v_cn) {
                        $m_rpc = new \Model\Storage\RealisasiPembayaranCn_model();
                        $m_rpc->id_header = $id;
                        $m_rpc->saldo = $v_cn['saldo'];
                        $m_rpc->sisa_saldo = $v_cn['sisa_saldo'];
                        $m_rpc->pakai = $v_cn['pakai'];
                        $m_rpc->id_cn = $v_cn['id'];
                        $m_rpc->save();

                        foreach ($v_cn['detail'] as $k_det => $v_det) {
                            $m_conf = new \Model\Storage\Conf();
                            $sql = "
                                select * from cn where id = ".$v_det['id_cn']."
                            ";
                            $d_cn = $m_conf->hydrateRaw( $sql );

                            $m_conf = new \Model\Storage\Conf();
                            $sql = "
                                select * from realisasi_pembayaran_det where no_bayar = '".$k_det."' and id_header = ".$id."
                            ";
                            $d_det = $m_conf->hydrateRaw( $sql );

                            if ( $d_cn->count() > 0 && $d_det->count() > 0 ) {
                                $d_cn = $d_cn->toArray()[0];
                                $d_det = $d_det->toArray()[0];

                                $m_rpdcd = new \Model\Storage\RealisasiPembayaranDetCnDn_model();
                                $m_rpdcd->id_header = $d_det['id'];
                                $m_rpdcd->nomor_cn_dn = $d_cn['nomor'];
                                $m_rpdcd->nominal = $v_det['jml_bayar'];
                                $m_rpdcd->save();
                            }
                        }
                    }
                }

                if ( isset($data['potongan']) && !empty($data['potongan']) ) {
                    foreach ($data['potongan'] as $k_potongan => $v_potongan) {
                        if ( isset($v_potongan['id']) && !empty($v_potongan['id']) ) {
                            $m_rpp = new \Model\Storage\RealisasiPembayaranPotongan_model();
                            $m_rpp->id_header = $id;
                            $m_rpp->det_jurnal_trans_id = $v_potongan['id'];
                            $m_rpp->nominal = $v_potongan['nominal'];
                            $m_rpp->save();
                        }
                    }
                }

                $jenis_transaksi = ($jenis_transaksi == 'PLASMA') ? 'RHPP' : $jenis_transaksi;

                $m_conf = new \Model\Storage\Conf();
                $sql = "exec insert_jurnal '".$jenis_transaksi."', '".$nomor."', NULL, NULL, 'realisasi_pembayaran', ".$id.", NULL, 1";
                $d_conf = $m_conf->hydrateRaw( $sql );

                $d_rp = $m_rp->where('id', $id)->first();

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $d_rp, $deskripsi_log);

                $this->result['status'] = 1;
                $this->result['content'] = array('id' => $id);
                $this->result['message'] = 'Data berhasil di simpan.';
            } else {
                $this->result['message'] = 'Error, segera hubungi tim IT.';
            }
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit()
    {
        $data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

        try {
            // cetak_r( $data, 1 );
            
            $id = $data['id'];

            $jenis_transaksi = null;

            $file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];
            }

            $m_rp = new \Model\Storage\RealisasiPembayaran_model();
            if ($isMoved) {
                $file_name = $moved['name'];
                $path_name = $moved['path'];
            } else {
                $d_rp = $m_rp->where('id', $data['id'])->first();
                $path_name = $d_rp->lampiran;
            }

            $m_rpdn = new \Model\Storage\RealisasiPembayaranDn_model();
            $m_rpdn->where('id_header', $id)->delete();
        
            $m_rpcn = new \Model\Storage\RealisasiPembayaranCn_model();
            $m_rpcn->where('id_header', $id)->delete();

            $m_rpp = new \Model\Storage\RealisasiPembayaranPotongan_model();
            $m_rpp->where('id_header', $id)->delete();

            $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
            $d_rpd = $m_rpd->where('id_header', $id)->get();
            foreach ($d_rpd as $k_rpd => $v_rpd) {
                $m_rpdcd = new \Model\Storage\RealisasiPembayaranDetCnDn_model();
                $m_rpdcd->where('id_header', $v_rpd['id'])->delete();

                $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
                $m_rpd->where('id', $v_rpd['id'])->delete();
            }

            $m_rp->where('id', $id)->update(
                array(
                    'tgl_bayar' => $data['tgl_bayar'],
                    'perusahaan' => $data['perusahaan'],
                    'supplier' => isset($data['supplier']) ? $data['supplier'] : null,
                    'peternak' => isset($data['peternak']) ? $data['peternak'] : null,
                    'no_rek' => isset($data['no_rek']) ? $data['no_rek'] : null,
                    'no_bukti' => $data['no_bukti'],
                    'lampiran' => $path_name,
                    'dn' => isset($data['total_dn']) ? $data['total_dn'] : 0,
                    'cn' => isset($data['total_cn']) ? $data['total_cn'] : 0,
                    'jml_transfer' => $data['jml_transfer'],
                    'jml_bayar' => $data['bayar'],
                    'keterangan' => isset($data['keterangan']) ? $data['keterangan'] : null,
                    'ekspedisi' => isset($data['ekspedisi']) ? $data['ekspedisi'] : null,
                    'potongan' => $data['total_potongan'],
                    'uang_muka' => $data['uang_muka'],
                )
            );

            $potongan = $data['total_potongan'];
            $uang_muka = $data['uang_muka'];

            foreach ($data['detail'] as $k_det => $v_det) {
                $jenis_transaksi = $v_det['transaksi'];

                $bayar = $v_det['bayar'];
                $nominal_potongan = 0;
                $nominal_uang_muka = 0;

                if ( $potongan > 0 || $uang_muka > 0 ) {
                    if ( $potongan > 0 && $bayar > 0 ) {
                        if ( $bayar <= $potongan ) {
                            $potongan -= $bayar;
                            $nominal_potongan = $bayar;
                            $bayar = 0;
                        } else {
                            $bayar -= $potongan;
                            $nominal_potongan = $potongan;
                            $potongan = 0;
                        }
                    }

                    if ( $uang_muka > 0 && $bayar > 0 ) {
                        if ( $bayar <= $uang_muka ) {
                            $uang_muka -= $bayar;
                            $nominal_uang_muka = $bayar;
                            $bayar = 0;
                        } else {
                            $bayar -= $uang_muka;
                            $nominal_uang_muka = $uang_muka;
                            $uang_muka = 0;
                        }
                    }
                }

                $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
                $m_rpd->id_header = $id;
                $m_rpd->transaksi = $v_det['transaksi'];
                $m_rpd->no_bayar = $v_det['no_bayar'];
                $m_rpd->tagihan = $v_det['tagihan'];
                $m_rpd->bayar = $v_det['bayar'];
                $m_rpd->cn = $v_det['cn'];
                $m_rpd->dn = $v_det['dn'];
                $m_rpd->potongan = $nominal_potongan;
                $m_rpd->uang_muka = $nominal_uang_muka;
                $m_rpd->transfer = $v_det['transfer'];
                $m_rpd->save();
            }

            if ( isset($data['dn']) && !empty($data['dn']) ) {
                foreach ($data['dn'] as $k_dn => $v_dn) {
                    $m_rpd = new \Model\Storage\RealisasiPembayaranDn_model();
                    $m_rpd->id_header = $id;
                    $m_rpd->saldo = $v_dn['saldo'];
                    $m_rpd->sisa_saldo = $v_dn['sisa_saldo'];
                    $m_rpd->pakai = $v_dn['pakai'];
                    $m_rpd->id_dn = $v_dn['id'];
                    $m_rpd->save();

                    foreach ($v_dn['detail'] as $k_det => $v_det) {
                        $m_conf = new \Model\Storage\Conf();
                        $sql = "
                            select * from dn where id = ".$v_det['id_dn']."
                        ";
                        $d_dn = $m_conf->hydrateRaw( $sql );

                        $m_conf = new \Model\Storage\Conf();
                        $sql = "
                            select * from realisasi_pembayaran_det where no_bayar = '".$k_det."' and id_header = ".$id."
                        ";
                        $d_det = $m_conf->hydrateRaw( $sql );

                        if ( $d_dn->count() > 0 && $d_det->count() > 0 ) {
                            $d_dn = $d_dn->toArray()[0];
                            $d_det = $d_det->toArray()[0];

                            $m_rpdcd = new \Model\Storage\RealisasiPembayaranDetCnDn_model();
                            $m_rpdcd->id_header = $d_det['id'];
                            $m_rpdcd->nomor_cn_dn = $d_dn['nomor'];
                            $m_rpdcd->nominal = $v_det['jml_bayar'];
                            $m_rpdcd->save();
                        }
                    }
                }
            }

            if ( isset($data['cn']) && !empty($data['cn']) ) {
                foreach ($data['cn'] as $k_cn => $v_cn) {
                    $m_rpc = new \Model\Storage\RealisasiPembayaranCn_model();
                    $m_rpc->id_header = $id;
                    $m_rpc->saldo = $v_cn['saldo'];
                    $m_rpc->sisa_saldo = $v_cn['sisa_saldo'];
                    $m_rpc->pakai = $v_cn['pakai'];
                    $m_rpc->id_cn = $v_cn['id'];
                    $m_rpc->save();

                    foreach ($v_cn['detail'] as $k_det => $v_det) {
                        $m_conf = new \Model\Storage\Conf();
                        $sql = "
                            select * from cn where id = ".$v_det['id_cn']."
                        ";
                        $d_cn = $m_conf->hydrateRaw( $sql );

                        $m_conf = new \Model\Storage\Conf();
                        $sql = "
                            select * from realisasi_pembayaran_det where no_bayar = '".$k_det."' and id_header = ".$id."
                        ";
                        $d_det = $m_conf->hydrateRaw( $sql );

                        if ( $d_cn->count() > 0 && $d_det->count() > 0 ) {
                            $d_cn = $d_cn->toArray()[0];
                            $d_det = $d_det->toArray()[0];

                            $m_rpdcd = new \Model\Storage\RealisasiPembayaranDetCnDn_model();
                            $m_rpdcd->id_header = $d_det['id'];
                            $m_rpdcd->nomor_cn_dn = $d_cn['nomor'];
                            $m_rpdcd->nominal = $v_det['jml_bayar'];
                            $m_rpdcd->save();
                        }
                    }
                }
            }

            if ( isset($data['potongan']) && !empty($data['potongan']) ) {
                foreach ($data['potongan'] as $k_potongan => $v_potongan) {
                    if ( isset($v_potongan['id']) && !empty($v_potongan['id']) ) {
                        $m_rpp = new \Model\Storage\RealisasiPembayaranPotongan_model();
                        $m_rpp->id_header = $id;
                        $m_rpp->det_jurnal_trans_id = $v_potongan['id'];
                        $m_rpp->nominal = $v_potongan['nominal'];
                        $m_rpp->save();
                    }
                }
            }

            $d_rp = $m_rp->where('id', $id)->first();

            $jenis_transaksi = ($jenis_transaksi == 'PLASMA') ? 'RHPP' : $jenis_transaksi;

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal '".$jenis_transaksi."', '".$d_rp->nomor."', NULL, NULL, 'realisasi_pembayaran', ".$id.", ".$id.", 2";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_rp, $deskripsi_log);

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
            $id = $params['id'];

            $m_rp = new \Model\Storage\RealisasiPembayaran_model();
            $d_rp = $m_rp->where('id', $id)->first();

            $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
            $d_rpd = $m_rpd->where('id_header', $id)->get()->toArray();

            foreach ($d_rpd as $k_det => $v_det) {
                $m_rpdcd = new \Model\Storage\RealisasiPembayaranDetCnDn_model();
                $m_rpdcd->where('id_header', $v_det['id'])->delete();
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal NULL, NULL, NULL, NULL, 'realisasi_pembayaran', ".$id.", ".$id.", 3";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $m_rpd->where('id_header', $id)->delete();
            $m_rpcn = new \Model\Storage\RealisasiPembayaranCn_model();
            $m_rpcn->where('id_header', $id)->delete();
            $m_rpdn = new \Model\Storage\RealisasiPembayaranDn_model();
            $m_rpdn->where('id_header', $id)->delete();
            $m_rpp = new \Model\Storage\RealisasiPembayaranPotongan_model();
            $m_rpp->where('id_header', $id)->delete();
            $m_rp = new \Model\Storage\RealisasiPembayaran_model();
            $m_rp->where('id', $id)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_rp, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function modalPilihDN()
    {
        $params = $this->input->get('params');

        $supplier = $params['supplier'];
        $sql_supplier = null;
        if ( !empty($supplier) ) {
            $sql_supplier = "and d.supplier = '".$supplier."'";
        }
        $mitra = $params['mitra'];
        $sql_mitra = null;
        if ( !empty($mitra) ) {
            $sql_mitra = "and d.mitra = '".$mitra."'";
        }
        $id = (isset($params['id']) && !empty($params['id'])) ? $params['id'] : null;
        $sql_id = null;
        if ( !empty($id) ) {
            $sql_id = "where id_header <> ".$id;
        }

        $data = null;
        if ( in_array('doc', $params['jenis_transaksi']) ) {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    d.id,
                    d.nomor,
                    d.tanggal,
                    d.ket_dn as keterangan,
                    (d.tot_dn - isnull(rpd.pakai, 0)) as saldo
                from dn d
                left join
                    (
                        select
                            sum(isnull(pakai, 0)) as pakai, id_dn
                        from
                        (
                            select sum(pakai) as pakai, id_dn from realisasi_pembayaran_dn ".$sql_id." group by id_dn

                            union all

                            select sum(pakai) as pakai, id_dn from bayar_peralatan_dn group by id_dn
                        ) rpd
                        group by
                            rpd.id_dn
                    ) rpd
                    on
                        d.id = rpd.id_dn
                where
                    d.nomor like '%DOC%' and
                    (d.tot_dn - isnull(rpd.pakai, 0)) > 0
                    ".$sql_supplier."
                    ".$sql_mitra."
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $key => $value) {
                    $_key = str_replace('-', '', $value['tanggal']).' | '.$value['nomor'];

                    $data[ $_key ] = $value;
                }
            }
        }

        if ( in_array('pakan', $params['jenis_transaksi']) ) {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    d.id,
                    d.nomor,
                    d.tanggal,
                    d.ket_dn as keterangan,
                    (d.tot_dn - isnull(rpd.pakai, 0)) as saldo
                from dn d
                left join
                    (
                        select
                            sum(pakai) as pakai, id_dn
                        from
                        (
                            select sum(pakai) as pakai, id_dn from realisasi_pembayaran_dn ".$sql_id." group by id_dn

                            union all

                            select sum(pakai) as pakai, id_dn from bayar_peralatan_dn group by id_dn
                        ) rpd
                        group by
                            rpd.id_dn
                    ) rpd
                    on
                        d.id = rpd.id_dn
                where
                    d.nomor like '%PKN%' and
                    (d.tot_dn - isnull(rpd.pakai, 0)) > 0
                    ".$sql_supplier."
                    ".$sql_mitra."
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $key => $value) {
                    $_key = str_replace('-', '', $value['tanggal']).' | '.$value['nomor'];

                    $data[ $_key ] = $value;
                }
            }
        }

        if ( in_array('voadip', $params['jenis_transaksi']) ) {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    d.id,
                    d.nomor,
                    d.tanggal,
                    d.ket_dn as keterangan,
                    (d.tot_dn - isnull(rpd.pakai, 0)) as saldo
                from dn d
                left join
                    (
                        select
                            sum(pakai) as pakai, id_dn
                        from
                        (
                            select sum(pakai) as pakai, id_dn from realisasi_pembayaran_dn ".$sql_id." group by id_dn

                            union all

                            select sum(pakai) as pakai, id_dn from bayar_peralatan_dn group by id_dn
                        ) rpd
                        group by
                            rpd.id_dn
                    ) rpd
                    on
                        d.id = rpd.id_dn
                where
                    d.nomor like '%OVK%' and
                    (d.tot_dn - isnull(rpd.pakai, 0)) > 0
                    ".$sql_supplier."
                    ".$sql_mitra."
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $key => $value) {
                    $_key = str_replace('-', '', $value['tanggal']).' | '.$value['nomor'];

                    $data[ $_key ] = $value;
                }
            }
        }

        if ( in_array('peternak', $params['jenis_transaksi']) ) {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    d.id,
                    d.nomor,
                    d.tanggal,
                    d.ket_dn as keterangan,
                    (d.tot_dn - isnull(rpd.pakai, 0)) as saldo
                from dn d
                left join
                    (
                        select
                            sum(pakai) as pakai, id_dn
                        from
                        (
                            select sum(pakai) as pakai, id_dn from realisasi_pembayaran_dn ".$sql_id." group by id_dn

                            union all

                            select sum(pakai) as pakai, id_dn from bayar_peralatan_dn group by id_dn
                        ) rpd
                        group by
                            rpd.id_dn
                    ) rpd
                    on
                        d.id = rpd.id_dn
                where
                    d.nomor like '%RHPP%' and
                    (d.tot_dn - isnull(rpd.pakai, 0)) > 0
                    ".$sql_supplier."
                    ".$sql_mitra."
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $key => $value) {
                    $_key = str_replace('-', '', $value['tanggal']).' | '.$value['nomor'];

                    $data[ $_key ] = $value;
                }
            }
        }

        if ( !empty( $data ) ) {
            ksort( $data );
        }

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/realisasi_pembayaran/modal_pilih_dn', $content, true);

        echo $html;
    }

    public function modalPilihCN()
    {
        $params = $this->input->get('params');

        $supplier = $params['supplier'];
        $sql_supplier = null;
        if ( !empty($supplier) ) {
            $sql_supplier = "and c.supplier = '".$supplier."'";
        }
        $mitra = $params['mitra'];
        $sql_mitra = null;
        if ( !empty($mitra) ) {
            $sql_mitra = "and c.mitra = '".$mitra."'";
        }
        $id = (isset($params['id']) && !empty($params['id'])) ? $params['id'] : null;
        $sql_id = null;
        if ( !empty($id) ) {
            $sql_id = "where id_header <> ".$id;
        }

        $data = null;
        if ( in_array('doc', $params['jenis_transaksi']) ) {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    c.id,
                    c.nomor,
                    c.tanggal,
                    c.ket_cn as keterangan,
                    (c.tot_cn - isnull(rpc.pakai, 0)) as saldo
                from cn c
                left join
                    (
                        select
                            sum(isnull(pakai, 0)) as pakai, id_cn
                        from
                        (
                            select sum(pakai) as pakai, id_cn from realisasi_pembayaran_cn ".$sql_id." group by id_cn

                            union all

                            select sum(pakai) as pakai, id_cn from bayar_peralatan_cn group by id_cn
                        ) rpc
                        group by
                            rpc.id_cn
                    ) rpc
                    on
                        c.id = rpc.id_cn
                where
                    c.nomor like '%DOC%' and
                    (c.tot_cn - isnull(rpc.pakai, 0)) > 0
                    ".$sql_supplier."
                    ".$sql_mitra."
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $key => $value) {
                    $_key = str_replace('-', '', $value['tanggal']).' | '.$value['nomor'];

                    $data[ $_key ] = $value;
                }
            }
        }

        if ( in_array('pakan', $params['jenis_transaksi']) ) {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    c.id,
                    c.nomor,
                    c.tanggal,
                    c.ket_cn as keterangan,
                    (c.tot_cn - isnull(rpc.pakai, 0)) as saldo
                from cn c
                left join
                    (
                        select
                            sum(isnull(pakai, 0)) as pakai, id_cn
                        from
                        (
                            select sum(pakai) as pakai, id_cn from realisasi_pembayaran_cn ".$sql_id." group by id_cn

                            union all

                            select sum(pakai) as pakai, id_cn from bayar_peralatan_cn group by id_cn
                        ) rpc
                        group by
                            rpc.id_cn
                    ) rpc
                    on
                        c.id = rpc.id_cn
                where
                    c.nomor like '%PKN%' and
                    (c.tot_cn - isnull(rpc.pakai, 0)) > 0
                    ".$sql_supplier."
                    ".$sql_mitra."
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $key => $value) {
                    $_key = str_replace('-', '', $value['tanggal']).' | '.$value['nomor'];

                    $data[ $_key ] = $value;
                }
            }
        }

        if ( in_array('voadip', $params['jenis_transaksi']) ) {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    c.id,
                    c.nomor,
                    c.tanggal,
                    c.ket_cn as keterangan,
                    (c.tot_cn - isnull(rpc.pakai, 0)) as saldo
                from cn c
                left join
                    (
                        select
                            sum(isnull(pakai, 0)) as pakai, id_cn
                        from
                        (
                            select sum(pakai) as pakai, id_cn from realisasi_pembayaran_cn ".$sql_id." group by id_cn

                            union all

                            select sum(pakai) as pakai, id_cn from bayar_peralatan_cn group by id_cn
                        ) rpc
                        group by
                            rpc.id_cn
                    ) rpc
                    on
                        c.id = rpc.id_cn
                where
                    c.nomor like '%OVK%' and
                    (c.tot_cn - isnull(rpc.pakai, 0)) > 0
                    ".$sql_supplier."
                    ".$sql_mitra."
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $key => $value) {
                    $_key = str_replace('-', '', $value['tanggal']).' | '.$value['nomor'];

                    $data[ $_key ] = $value;
                }
            }
        }

        if ( in_array('peternak', $params['jenis_transaksi']) ) {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    c.id,
                    c.nomor,
                    c.tanggal,
                    c.ket_cn as keterangan,
                    (c.tot_cn - isnull(rpc.pakai, 0)) as saldo
                from cn c
                left join
                    (
                        select
                            sum(isnull(pakai, 0)) as pakai, id_cn
                        from
                        (
                            select sum(pakai) as pakai, id_cn from realisasi_pembayaran_cn ".$sql_id." group by id_cn

                            union all

                            select sum(pakai) as pakai, id_cn from bayar_peralatan_cn group by id_cn
                        ) rpc
                        group by
                            rpc.id_cn
                    ) rpc
                    on
                        c.id = rpc.id_cn
                where
                    c.nomor like '%RHPP%' and
                    (c.tot_cn - isnull(rpc.pakai, 0)) > 0
                    ".$sql_supplier."
                    ".$sql_mitra."
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $key => $value) {
                    $_key = str_replace('-', '', $value['tanggal']).' | '.$value['nomor'];

                    $data[ $_key ] = $value;
                }
            }
        }

        if ( !empty( $data ) ) {
            ksort( $data );
        }

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/realisasi_pembayaran/modal_pilih_cn', $content, true);

        echo $html;
    }

    public function modalPotongan()
    {
        $params = $this->input->get('params');

        $data = null;
        foreach ($params['jenis_transaksi'] as $k_jt => $v_jt) {
            $nama_transaksi = $v_jt;

            $sql_perusahaan = "and c.id_perusahaan = '".$params['perusahaan']."'";
            if ( stristr( $nama_transaksi, 'peternak' ) !== false or stristr( $nama_transaksi, 'doc' ) !== false ) {
                // $nama_transaksi = 'rhpp';
                // $list_coa_potongan = array('130611', '130513', '130621', '130523', '130612', '130622', '950100');
                $list_coa_potongan = array(
                    '130611',  
                    '130621', 
                    '130612', 
                    '130622',
                    '130623',
                    '130624',
                    '130513',
                    '130523',
                    '130525',
                    '950100'
                );
            }

            if ( stristr( $nama_transaksi, 'pakan' ) !== false ) {
                $nama_transaksi = 'pakan';
                $list_coa_potongan = array('130612', '130622', '130624', '130525', '130513', '130523');
            }

            if ( stristr( $nama_transaksi, 'oa pakan' ) !== false ) {
                $nama_transaksi = 'oa pakan';
                $list_coa_potongan = array('130514', '210401', '130524', '130206');
            }

            if ( stristr( $nama_transaksi, 'voadip' ) !== false ) {
                $nama_transaksi = 'ovk';
                $list_coa_potongan = array('160381');
                $sql_perusahaan = null;
            }

            $m_jurnalt = new \Model\Storage\JurnalTrans_model();
            $d_jurnalt = $m_jurnalt->where('nama', 'like', $nama_transaksi.'%')->where('mstatus', 1)->first();

            $det_jurnal_trans_id = null;
            if ( $d_jurnalt ) {
                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select 
                        djt.id,
                        djt.id_header,
                        djt.sumber_coa as no_coa,
                        djt.nama,
                        djt.sumber,
                        djt.sumber_coa,
                        djt.tujuan,
                        djt.tujuan_coa
                    from det_jurnal_trans djt
                    right join
                        coa c
                        on
                            djt.sumber_coa = c.coa
                    where
                        djt.id_header = ".$d_jurnalt->id." and
                        djt.sumber_coa in ('".implode("', '", $list_coa_potongan)."') and
                        djt.nama not in ('OA PAKAN')
                        ".$sql_perusahaan."
                    group by
                        djt.id,
                        djt.id_header,
                        djt.nama,
                        djt.sumber,
                        djt.sumber_coa,
                        djt.tujuan,
                        djt.tujuan_coa
                    order by
                        djt.nama asc
                ";
                $d_djt = $m_conf->hydrateRaw( $sql );

                if ( $d_djt->count() > 0 ) {
                    $d_djt = $d_djt->toArray();

                    foreach ($d_djt as $key => $value) {
                        $data[] = $value;
                    }
                }
            }
        }

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/realisasi_pembayaran/modal_potongan', $content, true);

        echo $html;
    }

    public function tes()
    {
        $rekening = '3337773888';

        cetak_r( substr($rekening, 1, 3) );
    }
}