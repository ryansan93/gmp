<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BayarPenjualanPeralatan extends Public_Controller {

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
                "assets/transaksi/bayar_penjualan_peralatan/js/bayar-penjualan-peralatan.js",
            ));
            $this->add_external_css(array(
                "assets/transaksi/bayar_penjualan_peralatan/css/bayar-penjualan-peralatan.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;

            $content['data_mitra'] = $this->get_mitra();

            // Load Indexx
            $data['title_menu'] = 'Bayar Penjualan Peralatan';
            $data['view'] = $this->load->view('transaksi/bayar_penjualan_peralatan/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_lists()
    {
        $params = $this->input->post('params');

        $data = null;

        // $m_pp = new \Model\Storage\PenjualanPeralatan_model();
        // if ( stristr($params['filter'], 'all') !== FALSE ) {
        //     $d_pp = $m_pp->where('mitra', $params['mitra'])->whereBetween('tanggal', [$params['start_date'], $params['end_date']])->with(['d_bayar'])->get();
        // } else if ( stristr($params['filter'], 'lunas') !== FALSE ) {
        //     $d_pp = $m_pp->where('mitra', $params['mitra'])->whereBetween('tanggal', [$params['start_date'], $params['end_date']])->with(['d_bayar'])->where('sisa', 0)->get();
        // } else if ( stristr($params['filter'], 'belum') !== FALSE ) {
        //     $d_pp = $m_pp->where('mitra', $params['mitra'])->whereBetween('tanggal', [$params['start_date'], $params['end_date']])->with(['d_bayar'])->where('sisa', '>', 0)->get();
        // }

        // if ( $d_pp->count() > 0 ) {
        //     $d_pp = $d_pp->toArray();

        //     foreach ($d_pp as $k_pp => $v_pp) {
        //         $m_bpp = new \Model\Storage\BayarPenjualanPeralatan_model();
        //         $d_bpp = $m_bpp->where('id_penjualan_peralatan', $v_pp['id'])->get();

        //         $jml_bayar = 0;
        //         if ( $d_bpp->count() > 0 ) {
        //             $d_bpp = $d_bpp->toArray();
        //             foreach ($d_bpp as $k_bpp => $v_bpp) {
        //                 $jml_bayar += $v_bpp['bayar'] + $v_bpp['saldo'];
        //             }
        //         }

        //         $sisa = ( $v_pp['total'] > $jml_bayar ) ? $v_pp['total'] - $jml_bayar : 0;

        //         $data[ $v_pp['tanggal'] ] = array(
        //             'id' => $v_pp['id'],
        //             'tanggal' => $v_pp['tanggal'],
        //             'total' => $v_pp['total'],
        //             'sisa' => $sisa,
        //             'status' => ($sisa > 0) ? 'BELUM' : 'LUNAS',
        //             'd_bayar' => $v_pp['d_bayar']
        //         );
        //     }
        // }

        $sql_filter = null;
        if ( stristr($params['filter'], 'lunas') !== FALSE ) {
            $sql_filter = "and pp.sisa = 0";
        } else if ( stristr($params['filter'], 'belum') !== FALSE ) {
            $sql_filter = "and pp.sisa > 0";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                pp.id,
                pp.nomor,
                pp.tanggal,
                pp.total,
                (pp.total - isnull(bp.nominal, 0)) as sisa,
                case
                    when (pp.total - isnull(bp.nominal, 0)) > 0 then
                        'BELUM'
                    else
                        'LUNAS'
                end as status
            from penjualan_peralatan pp
            left join
                (
                    select
                        sum(data.nominal) as nominal,
                        data.piutang_kode
                    from (
                        select sum(bpp.saldo+bpp.bayar) as nominal, pp.nomor as piutang_kode from bayar_penjualan_peralatan bpp
                        left join
                            penjualan_peralatan pp
                            on
                                bpp.id_penjualan_peralatan = pp.id
                        group by
                            pp.nomor
                        
                        union all

                        select sum(nominal) as nominal, piutang_kode from rhpp_piutang group by piutang_kode

                        union all

                        select sum(nominal) as nominal, piutang_kode from rhpp_group_piutang group by piutang_kode

                        union all

                        select sum(rp.jumlah_bayar) as nominal, pp.nomor as piutang_kode from rhpp_potongan rp 
                        left join
                            penjualan_peralatan pp
                            on
                                rp.id_trans = pp.id
                        group by 
                            pp.nomor

                        union all

                        select sum(rgp.jumlah_bayar) as nominal, pp.nomor as piutang_kode from rhpp_group_potongan rgp 
                        left join
                            penjualan_peralatan pp
                            on
                                rgp.id_trans = pp.id
                        group by 
                            pp.nomor
                    ) data
                    group by
                        data.piutang_kode
                ) bp
                on
                    pp.nomor = bp.piutang_kode
            where
                pp.tanggal between '".$params['start_date']."' and '".$params['end_date']."' and
                pp.mitra = '".$params['mitra']."'
                ".$sql_filter."
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();
            $idx = 0;
            foreach ($d_conf as $key => $value) {
                $m_detail = new \Model\Storage\Conf();
                $sql = "
                    select
                        data.*
                    from (
                        select bpp.id, bpp.tanggal as tanggal, bpp.saldo, bpp.bayar, pp.nomor as piutang_kode, 'NON RHPP' as jenis_bayar from bayar_penjualan_peralatan bpp
                        left join
                            penjualan_peralatan pp
                            on
                                bpp.id_penjualan_peralatan = pp.id
                        
                        union all

                        select 
                            '' as id,
                            ts.tgl_tutup as tanggal, 
                            0 as saldo, 
                            rp.nominal as bayar, 
                            rp.piutang_kode, 
                            'RHPP' as jenis_bayar 
                        from (
                            select
                                data.id_header,
                                sum(data.nominal) as nominal,
                                data.piutang_kode
                            from
                            (
                                select id_header, nominal, piutang_kode from rhpp_piutang
    
                                union all
    
                                select rp.id_header, rp.jumlah_bayar as nominal, pp.nomor as piutang_kode from rhpp_potongan rp
                                left join
                                    penjualan_peralatan pp
                                    on
                                        rp.id_trans = pp.id
                            ) data
                            group by
                                data.id_header,
                                data.piutang_kode
                        ) rp
                        left join
                            rhpp r
                            on
                                rp.id_header = r.id
                        left join
                            tutup_siklus ts
                            on
                                ts.id = r.id_ts

                        union all

                        select 
                            '' as id,
                            rgh.tgl_submit as tanggal, 
                            0 as sisa, 
                            nominal as bayar, 
                            rgp.piutang_kode, 
                            'RHPP GROUP' as jenis_bayar 
                        from
                        (
                            select
                                data.id_header,
                                sum(data.nominal) as nominal,
                                data.piutang_kode
                            from
                            (
                                select id_header, nominal, piutang_kode from rhpp_group_piutang

                                union all

                                select rgp.id_header, rgp.jumlah_bayar as nominal, pp.nomor as piutang_kode from rhpp_group_potongan rgp
                                left join
                                    penjualan_peralatan pp
                                    on
                                        rgp.id_trans = pp.id
                            ) data
                            group by
                                data.id_header,
                                data.piutang_kode
                        ) rgp
                        left join
                            rhpp_group rg
                            on
                                rgp.id_header = rg.id
                        left join
                            rhpp_group_header rgh
                            on
                                rg.id_header = rgh.id
                    ) data
                    where
                        data.piutang_kode = '".$value['nomor']."'
                ";
                $d_detail = $m_detail->hydrateRaw( $sql );

                $detail = null;
                if ( $d_detail->count() > 0 ) {
                    $detail = $d_detail->toArray();
                }

                $data[ $idx ] = $value;
                $data[ $idx ]['d_bayar'] = $detail;

                $idx++;
            }
        }

        // cetak_r( $data, 1 );

        $content['data'] = $data;

        $html = $this->load->view('transaksi/bayar_penjualan_peralatan/list', $content, TRUE);

        $this->result['html'] = $html;

        display_json( $this->result );
    }

    public function add_form()
    {
        $params = $this->input->get('params');

        $m_pp = new \Model\Storage\PenjualanPeralatan_model();
        $d_pp = $m_pp->where('id', $params['id'])->with(['d_bayar'])->first();

        $m_sm = new \Model\Storage\SaldoMitra_model();
        $d_sm = $m_sm->where('no_mitra', $d_pp['mitra'])->orderBy('id', 'desc')->first();

        $saldo = 0;
        if ( $d_sm ) {
            $saldo = $d_sm->saldo;
        }

        $data = null;
        if ( $d_pp ) {
            $d_pp = $d_pp->toArray();

            $m_bpp = new \Model\Storage\BayarPenjualanPeralatan_model();
            $d_bpp = $m_bpp->where('id_penjualan_peralatan', $params['id'])->get();

            $_saldo = ($saldo < 0) ? 0 : $saldo;

            $sisa_bayar = 0;
            $total_bayar = 0;
            if ( $d_bpp->count() > 0 ) {
                $d_bpp = $d_bpp->toArray();
                foreach ($d_bpp as $k_bpp => $v_bpp) {
                    $total_bayar += $v_bpp['bayar'] + $v_bpp['saldo'];
                }

                $sisa_bayar = ($d_pp['total'] < $total_bayar) ? 0 : $d_pp['total'] - $total_bayar;
            } else {
                $sisa_bayar = $d_pp['total'];
            }

            $data = array(
                'id' => $d_pp['id'],
                'tagihan' => $sisa_bayar,
                'saldo' => $_saldo,
                'tanggal' => $d_pp['tanggal'],
                'total_bayar' => $total_bayar,
            );
        }

        $content['data'] = $data;
        $html = $this->load->view('transaksi/bayar_penjualan_peralatan/add_form', $content, true);

        echo $html;
    }

    public function edit_form()
    {
        $params = $this->input->get('params');

        $m_bpp = new \Model\Storage\BayarPenjualanPeralatan_model();
        $d_bpp = $m_bpp->where('id', $params['id'])->first();

        $m_pp = new \Model\Storage\PenjualanPeralatan_model();
        $d_pp = $m_pp->where('id', $d_bpp->id_penjualan_peralatan)->with(['d_bayar'])->first();

        $m_sm = new \Model\Storage\SaldoMitra_model();
        $d_sm = $m_sm->where('no_mitra', $d_pp['mitra'])->orderBy('id', 'desc')->first();

        $saldo = 0;
        if ( $d_sm ) {
            $saldo = $d_sm->saldo;
        }

        $data = null;
        if ( $d_pp ) {
            $d_pp = $d_pp->toArray();

            $total_bayar = $d_bpp->total_bayar;

            $data = array(
                'id' => $d_pp['id'],
                'id_bayar' => $d_bpp->id,
                'tgl_bayar' => $d_bpp->tanggal,
                'tagihan' => $d_bpp->tagihan,
                'saldo' => $d_bpp->saldo,
                'tanggal' => $d_pp['tanggal'],
                'total_bayar' => $d_bpp->bayar,
            );
        }

        $content['data'] = $data;
        $html = $this->load->view('transaksi/bayar_penjualan_peralatan/edit_form', $content, true);

        echo $html;
    }

    public function detail_form()
    {
        $params = $this->input->get('params');

        $m_pp = new \Model\Storage\PenjualanPeralatan_model();
        $d_pp = $m_pp->where('id', $params['id'])->with(['d_bayar'])->first();

        $data = null;
        if ( $d_pp ) {
            $d_pp = $d_pp->toArray();

            $m_bpp = new \Model\Storage\BayarPenjualanPeralatan_model();
            $total_bayar = $m_bpp->where('id_penjualan_peralatan', $params['id'])->sum('bayar');

            $sisa_bayar = ($d_pp['total'] < $total_bayar) ? 0 : $d_pp['total'] - $total_bayar;

            $data = array(
                'id' => $d_pp['id'],
                'tanggal' => $d_pp['tanggal'],
                'total' => $d_pp['total'],
                'total_bayar' => $total_bayar,
                'sisa_bayar' => $sisa_bayar,
                'status' => ($sisa_bayar > 0) ? 'BELUM' : 'LUNAS'
            );
        }

        $content['data'] = $data;
        $html = $this->load->view('transaksi/bayar_penjualan_peralatan/detail_form', $content, true);

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
        $kode_unit_all = null;
        if ( $d_karyawan ) {
            $m_ukaryawan = new \Model\Storage\UnitKaryawan_model();
            $d_ukaryawan = $m_ukaryawan->where('id_karyawan', $d_karyawan->id)->get()->toArray();

            foreach ($d_ukaryawan as $k_ukaryawan => $v_ukaryawan) {
                if ( $v_ukaryawan['unit'] != 'all' ) {
                    $m_wil = new \Model\Storage\Wilayah_model();
                    $d_wil = $m_wil->where('id', $v_ukaryawan['unit'])->first();

                    $kode_unit[ $d_wil->kode ] = $d_wil->kode;
                } else {
                    $kode_unit_all = $v_ukaryawan['unit'];
                }
            }
        } else {
            $kode_unit_all = 'all';
        }

        $sql_unit = "";
        if ( empty($kode_unit_all) ) {
            $sql_unit = "and w.kode in ('".implode( $kode_unit )."')";
        }
        
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                mtr.nomor,
                mtr.nama,
                w.kode as unit
            from 
            (
                select mtr1.* from mitra mtr1
                right join
                    (select max(id) as id, nomor from mitra group by nomor) mtr2
                    on 
                        mtr1.id = mtr2.id
            ) mtr
            left join
                mitra_mapping mm
                on
                    mm.mitra = mtr.id
            left join
                kandang k
                on
                    k.mitra_mapping = mm.id
            left join
                wilayah w
                on
                    w.id = k.unit
            where
                mtr.mstatus = 1
                ".$sql_unit."
            group by
                mtr.nomor,
                mtr.nama,
                w.kode 
            order by
                w.kode asc,
                mtr.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );
        
        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        // $m_mm = new \Model\Storage\MitraMapping_model();
        // $d_mm = $m_mm->with(['dMitra'])->orderBy('id', 'desc')->get();

        // if ( $d_mm->count() > 0 ) {
        //     $d_mm = $d_mm->toArray();

        //     foreach ($d_mm as $k_mm => $v_mm) {
        //         $m_kdg = new \Model\Storage\Kandang_model();
        //         $d_kdg = $m_kdg->where('mitra_mapping', $v_mm['id'])->with(['d_unit'])->first();

        //         if ( $d_kdg ) {
        //             $key = $d_kdg->d_unit->kode.' | '.$v_mm['d_mitra']['nama'].' | '.$v_mm['d_mitra']['nomor'];
        //             if ( empty($kode_unit_all) ) {
        //                 foreach ($kode_unit as $k_ku => $v_ku) {
        //                     if ( $v_ku == $d_kdg->d_unit->kode ) {
        //                         $data[ $key ] = array(
        //                             'nomor' => $v_mm['d_mitra']['nomor'],
        //                             'nama' => $v_mm['d_mitra']['nama'],
        //                             'unit' => $d_kdg->d_unit->kode
        //                         );
        //                     }
        //                 }
        //             } else {
        //                 $data[ $key ] = array(
        //                     'nomor' => $v_mm['d_mitra']['nomor'],
        //                     'nama' => $v_mm['d_mitra']['nama'],
        //                     'unit' => $d_kdg->d_unit->kode
        //                 );
        //             }
        //         }
        //     }
        // }

        // ksort($data);

        /* GET ALL MITRA */
        // $m_mitra = new \Model\Storage\Mitra_model();
        // $_d_mitra = $m_mitra->select('nomor')->distinct('nomor')->get();

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
        //                         $data[ $key ] = array(
        //                             'nomor' => $d_mitra->nomor,
        //                             'nama' => $d_mitra->nama,
        //                             'unit' => $d_kdg->d_unit->kode
        //                         );
        //                     }
        //                 }
        //             } else {
        //                 $data[ $key ] = array(
        //                     'nomor' => $d_mitra->nomor,
        //                     'nama' => $d_mitra->nama,
        //                     'unit' => $d_kdg->d_unit->kode
        //                 );
        //             }
        //         }
        //     }

        //     ksort($data);
        // }

        return $data;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $sisa_tagihan = $params['tagihan'] - ($params['jumlah'] + $params['saldo']);

            $status = ($sisa_tagihan > 0) ? 'BELUM' : 'LUNAS';

            $m_bpp = new \Model\Storage\BayarPenjualanPeralatan_model();
            $m_bpp->id_penjualan_peralatan = $params['id_jual'];
            $m_bpp->tanggal = $params['tgl_bayar'];
            $m_bpp->tagihan = $params['tagihan'];
            $m_bpp->saldo = isset($params['saldo']) ? $params['saldo'] : 0;
            $m_bpp->bayar = $params['jumlah'];
            $m_bpp->jenis_bayar = 'non_rhpp';
            $m_bpp->status = $status;
            $m_bpp->lampiran = null;
            $m_bpp->save();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal NULL, NULL, NULL, 0, 'bayar_penjualan_peralatan', ".$m_bpp->id.", NULL, 1";

            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_bpp, $deskripsi_log);

            $m_pp = new \Model\Storage\PenjualanPeralatan_model();
            $m_pp->where('id', $params['id_jual'])->update(
                array(
                    'status' => $status
                )
            );

            $d_pp = $m_pp->where('id', $params['id_jual'])->first();

            $saldo = 0;
            if ( $params['tagihan'] < ($params['jumlah'] + $params['saldo']) ) {
                $saldo = ($params['jumlah'] + $params['saldo']) - $params['tagihan'];
            }

            $m_sm = new \Model\Storage\SaldoMitra_model();
            $d_sm = $m_sm->where('no_mitra', $d_pp->mitra)->orderBy('id', 'desc')->first();

            $_saldo = (isset($d_sm->saldo)) ? ($d_sm->saldo - $params['saldo']) : 0;

            $m_sm->jenis_saldo = 'D';
            $m_sm->no_mitra = $d_pp->mitra;
            $m_sm->tbl_name = 'bayar_penjualan_peralatan';
            $m_sm->tbl_id = $m_bpp->id;
            $m_sm->tgl_trans = date('Y-m-d');
            $m_sm->jenis_trans = 'pembayaran_mitra';
            $m_sm->nominal = $params['saldo'];
            $m_sm->saldo = $_saldo + $saldo;
            $m_sm->save();

            $this->result['status'] = 1;
            $this->result['message'] = 'Data bayar penjualan peralatan berhasil di simpan.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit()
    {
        $params = $this->input->post('params');

        try {
            $m_bpp = new \Model\Storage\BayarPenjualanPeralatan_model();
            $d_bpp = $m_bpp->where('id', $params['id_bayar'])->first();

            $m_pp = new \Model\Storage\PenjualanPeralatan_model();
            $d_pp = $m_pp->where('id', $d_bpp->id_penjualan_peralatan)->first();

            $m_sm = new \Model\Storage\SaldoMitra_model();
            $d_sm = $m_sm->where('no_mitra', $d_pp['mitra'])->orderBy('id', 'desc')->first();

            $jenis_saldo = null;
            $nominal = null;
            $saldo = !empty($d_sm) ? $d_sm->saldo : 0;

            $lebih_kurang = $params['jumlah'] - $d_bpp['bayar'];

            if ( $params['jumlah'] < $d_bpp['bayar'] ) {
                $nominal = $params['jumlah'] - $d_bpp['bayar'];

                $jenis_saldo = 'K';
                $saldo -= abs($nominal);
            } else {
                $nominal = $d_bpp['bayar'] - $params['jumlah'];

                $jenis_saldo = 'D';
                $saldo += abs($nominal);
            }

            $m_sm = new \Model\Storage\SaldoMitra_model();
            $m_sm->jenis_saldo = $jenis_saldo;
            $m_sm->no_mitra = $d_pp->mitra;
            $m_sm->tbl_name = 'bayar_penjualan_peralatan';
            $m_sm->tbl_id = $params['id_bayar'];
            $m_sm->tgl_trans = date('Y-m-d');
            $m_sm->jenis_trans = 'reverse_pembayaran_mitra';
            $m_sm->nominal = abs($nominal);
            $m_sm->saldo = ($saldo < 0) ? 0 : $saldo;
            $m_sm->save();

            $sisa_tagihan = $params['tagihan'] - ($params['jumlah'] + $params['saldo']);

            $status = ($sisa_tagihan > 0) ? 'BELUM' : 'LUNAS';

            $m_bpp->where('id', $params['id_bayar'])->update(
                array(
                    'tanggal' => $params['tgl_bayar'],
                    'tagihan' => $params['tagihan'],
                    'saldo' => isset($params['saldo']) ? $params['saldo'] : 0,
                    'bayar' => $params['jumlah'],
                    'jenis_bayar' => 'non_rhpp',
                    'status' => $status,
                    'lampiran' => null
                )
            );

            $d_bpp_new = $m_bpp->where('id', $params['id_bayar'])->first();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal NULL, NULL, NULL, 0, 'bayar_penjualan_peralatan', ".$params['id_bayar'].", ".$params['id_bayar'].", 2";

            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_bpp_new, $deskripsi_log);

            $m_pp->where('id', $d_bpp->id_penjualan_peralatan)->update(
                array(
                    'status' => $status
                )
            );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data bayar penjualan peralatan berhasil di update.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {
            $m_bpp = new \Model\Storage\BayarPenjualanPeralatan_model();
            $d_bpp = $m_bpp->where('id', $params['id'])->first();

            $m_bpp->where('id', $params['id'])->delete();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal NULL, NULL, NULL, 0, 'bayar_penjualan_peralatan', ".$params['id'].", ".$params['id'].", 3";

            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'hapus data bayar penjualan peralatan oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_bpp, $deskripsi_log);

            $m_pp = new \Model\Storage\PenjualanPeralatan_model();
            $m_pp->where('id', $d_bpp->id_penjualan_peralatan)->update(
                array(
                    'status' => 'BELUM'
                )
            );
            $d_pp = $m_pp->where('id', $d_bpp->id_penjualan_peralatan)->first();

            $m_sm = new \Model\Storage\SaldoMitra_model();
            $d_sm = $m_sm->where('no_mitra', $d_pp['mitra'])->orderBy('id', 'desc')->first();

            $saldo = !empty($d_sm) ? $d_sm->saldo : 0;

            $jenis_saldo = 'K';
            if ( $d_bpp['bayar'] > 0 ) {
                $saldo -= $d_bpp['bayar'] + $d_bpp['saldo'];
            }

            $m_sm = new \Model\Storage\SaldoMitra_model();
            $m_sm->jenis_saldo = $jenis_saldo;
            $m_sm->no_mitra = $d_pp->mitra;
            $m_sm->tbl_name = 'bayar_penjualan_peralatan';
            $m_sm->tbl_id = $params['id'];
            $m_sm->tgl_trans = date('Y-m-d');
            $m_sm->jenis_trans = 'reverse_pembayaran_mitra';
            $m_sm->nominal = $d_bpp['bayar'] + $d_bpp['saldo'];
            $m_sm->saldo = ($saldo < 0) ? 0 : $saldo;
            $m_sm->save();

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
}