<?php defined('BASEPATH') OR exit('No direct script access allowed');

class HitungStok extends Public_Controller {

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
                "assets/transaksi/hitung_stok/js/hitung-stok.js",
            ));
            $this->add_external_css(array(
                "assets/transaksi/hitung_stok/css/hitung-stok.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;

            // Load Indexx
            $data['title_menu'] = 'Hitung Stok';
            $data['view'] = $this->load->view('transaksi/hitung_stok/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function hitung_stok()
    {
        $startDate = $this->input->post('startDate');
        $endDate = $this->input->post('endDate');
        $target = $this->input->post('target');

        try {
            $_target = date('Y-m-t', strtotime($target));
            $tgl_proses = $_target;
            
            $startDate = substr($startDate, 0, 7).'-01';
            $_endDate = date('Y-m-t', strtotime($startDate));

            // $m_stok = new \Model\Storage\Stok_model();
            // $d_stok = $m_stok->select('id')->whereBetween('periode', [$startDate, $_endDate])->get();
            // if ( $d_stok->count() > 0 ) {
            //     $d_stok = $d_stok->toArray();

            //     foreach ($d_stok as $k_stok => $v_stok) {
            //         $m_dstok = new \Model\Storage\DetStok_model();
            //         $d_dstok = $m_dstok->select('id')->where('id_header', $v_stok['id'])->get();
            //         if ( $d_dstok->count() > 0 ) {
            //             $d_dstok = $d_dstok->toArray();

            //             foreach ($d_dstok as $k_dstok => $v_dstok) {
            //                 $m_dstokt = new \Model\Storage\DetStokTrans_model();
            //                 $m_dstokt->where('id_header', $v_dstok['id'])->delete();
            //             }
            //         }

            //         $m_dstok->where('id_header', $v_stok['id'])->delete();
            //     }
            // }

            $stok_voadip = $this->hitung_stok_voadip( $startDate, $_endDate );
            $stok_pakan = $this->hitung_stok_pakan( $startDate, $_endDate );

            $lanjut = 0;

            if ( substr($startDate, 0, 7) <= substr($endDate, 0, 7) ) {
                $lanjut = 1;
            }

            $new_start_date = date("Y-m-d", strtotime ( '+1 month' , strtotime ( $startDate ) ));

            $params = array(
                'start_date' => $new_start_date,
                'end_date' => $endDate,
                'target' => $_target,
                'text_target' => strtoupper(substr(tglIndonesia($new_start_date, '-', ' '), 3)),
            );
            
            $this->result['lanjut'] = $lanjut;
            $this->result['params'] = $params;
            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di proses';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function get_data_masuk_voadip($startDate, $endDate)
    {
        $m_stok = new \Model\Storage\Stok_model();

        $tgl_opname = '2022-09-06';

        $d_stok = $m_stok->where('periode', '>', $tgl_opname)->where('periode', '<', $startDate)->orderBy('periode', 'desc')->first();

        $data = null;
        $_data_retur = array();
        $_data_beli = array();

        // STOK AWAL
        if ( $d_stok ) {
            $d_stok = $d_stok->toArray();

            $m_dstok = new \Model\Storage\DetStok_model();
            $d_dstok = $m_dstok->where('id_header', $d_stok['id'])->where('jenis_barang', 'voadip')->orderBy('tgl_trans', 'asc')->get()->toArray();

            foreach ($d_dstok as $k_det => $v_det) {
                $isi_retur = 1;
                if ( isset($_data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]) ) {
                    foreach ($_data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ] as $k => $val) {
                        if ( $val['kode_trans'] == $v_det['kode_trans'] ) {
                            $isi_retur = 0;
                        }
                    }
                }

                $isi_beli = 1;
                if ( isset($_data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]) ) {
                    foreach ($_data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ] as $k => $val) {
                        if ( $val['kode_trans'] == $v_det['kode_trans'] ) {
                            $isi_beli = 0;
                        }
                    }
                }

                if ( $isi_retur == 1 ) {
                    if ( $v_det['jenis_trans'] == 'RETUR' ) {
                        if ( $v_det['jumlah'] > 0 ) {
                            $key = str_replace('-', '', $v_det['tgl_trans']).' | '.$v_det['kode_trans'];

                            $_data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][ $key ] = array(
                                'tgl_trans' => $v_det['tgl_trans'],
                                'kode_gudang' => $v_det['kode_gudang'],
                                'kode_barang' => $v_det['kode_barang'],
                                'jumlah_real' => $v_det['jumlah'],
                                'jumlah' => $v_det['jumlah'],
                                'hrg_jual' => $v_det['hrg_jual'],
                                'hrg_beli' => $v_det['hrg_beli'],
                                'kode_trans' => $v_det['kode_trans'],
                                'dari' => $v_det['jenis_trans']
                            );

                            ksort($_data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]);
                        }
                    }
                }

                if ( $isi_beli == 1 ) {
                    if ( $v_det['jenis_trans'] != 'RETUR' ) {
                        if ( $v_det['jumlah'] > 0 ) {
                            $key = str_replace('-', '', $v_det['tgl_trans']).' | '.$v_det['kode_trans'];

                            $_data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][ $key ] = array(
                                'tgl_trans' => $v_det['tgl_trans'],
                                'kode_gudang' => $v_det['kode_gudang'],
                                'kode_barang' => $v_det['kode_barang'],
                                'jumlah_real' => $v_det['jumlah'],
                                'jumlah' => $v_det['jumlah'],
                                'hrg_jual' => $v_det['hrg_jual'],
                                'hrg_beli' => $v_det['hrg_beli'],
                                'kode_trans' => $v_det['kode_trans'],
                                'dari' => $v_det['jenis_trans']
                            );


                            ksort($_data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]);
                        }
                    }
                }
            }
        }

        // RETUR
        $m_rv = new \Model\Storage\ReturVoadip_model();
        $sql = "
            select 
                rv.tgl_retur as tgl_trans, 
                rv.id_tujuan as kode_gudang,
                drv.item as kode_barang,
                drv.jumlah as jumlah_real,
                drv.jumlah as jumlah,
                (dkv.nilai_beli / dkv.jumlah) as hrg_jual,
                (dkv.nilai_jual / dkv.jumlah) as hrg_beli,
                rv.id as kode_trans,
                'RETUR' as 'dari'
            from det_retur_voadip drv 
            left join
                retur_voadip rv 
                on
                    drv.id_header = rv.id
            left join
                kirim_voadip kv 
                on
                    rv.no_order = kv.no_order
            left join
                det_kirim_voadip dkv 
                on
                    kv.id = dkv.id_header and
                    drv.item = dkv.item 
            where
                rv.tgl_retur BETWEEN '$startDate' AND '$endDate' and
                rv.tujuan = 'gudang' and
                drv.jumlah > 0
        ";

        $d_rv = $m_rv->hydrateRaw($sql);
        if ( $d_rv->count() > 0 ) {
            $d_rv = $d_rv->toArray();

            foreach ($d_rv as $k_rv => $v_rv) {
                if ( $v_rv['tgl_trans'] > $tgl_opname ) {
                    $key = str_replace('-', '', $v_rv['kode_barang']).' | '.$v_rv['kode_trans'];

                    $_data_retur[ $v_rv['kode_gudang'] ][ $v_rv['kode_barang'] ][ $key ] = array(
                        'tgl_trans' => $v_rv['tgl_trans'],
                        'kode_gudang' => $v_rv['kode_gudang'],
                        'kode_barang' => $v_rv['kode_barang'],
                        'jumlah_real' => $v_rv['jumlah_real'],
                        'jumlah' => $v_rv['jumlah'],
                        'hrg_jual' => $v_rv['hrg_jual'],
                        'hrg_beli' => $v_rv['hrg_beli'],
                        'kode_trans' => $v_rv['kode_trans'],
                        'dari' => $v_rv['dari'],
                    );

                    ksort($_data_retur[ $v_rv['kode_gudang'] ][ $v_rv['kode_barang'] ]);
                }
            }
        }

        // ORDER
        $m_kv = new \Model\Storage\KirimVoadip_model();
        $sql = "
            select 
                kv.tgl_kirim as tgl_trans, 
                kv.tujuan as kode_gudang,
                dkv.item as kode_barang,
                dtv.jumlah as jumlah_real,
                dtv.jumlah as jumlah,
                CASE
                    WHEN kv.jenis_kirim = 'opks'
                        THEN ovd.harga_jual
                        ELSE dkv.nilai_jual / dkv.jumlah 
                END as hrg_jual,
                CASE
                    WHEN kv.jenis_kirim = 'opks'
                        THEN ovd.harga
                        ELSE dkv.nilai_beli / dkv.jumlah 
                END as hrg_beli,
                kv.no_order as kode_trans,
                'ORDER' as 'dari'
            from det_kirim_voadip dkv 
            left join
                kirim_voadip kv 
                on
                    dkv.id_header = kv.id
            left join
                terima_voadip tv 
                on
                    kv.id = tv.id_kirim_voadip 
            left join
                det_terima_voadip dtv 
                on
                    tv.id = dtv.id_header and
                    dkv.item = dtv.item
            left join
                order_voadip ov 
                on
                    kv.no_order = ov.no_order 
            left join
                order_voadip_detail ovd 
                on
                    ov.id = ovd.id_order and
                    ovd.kode_barang = dkv.item 
            where
                kv.tgl_kirim BETWEEN '$startDate' AND '$endDate' and
                kv.jenis_tujuan = 'gudang' and
                dtv.jumlah > 0
        ";

        $d_kv = $m_kv->hydrateRaw($sql);
        if ( $d_kv->count() > 0 ) {
            $d_kv = $d_kv->toArray();

            foreach ($d_kv as $k_kv => $v_kv) {
                if ( $v_kv['tgl_trans'] > $tgl_opname ) {
                    $key = str_replace('-', '', $v_kv['kode_barang']).' | '.$v_kv['kode_trans'];

                    $_data_beli[ $v_kv['kode_gudang'] ][ $v_kv['kode_barang'] ][ $key ] = array(
                        'tgl_trans' => $v_kv['tgl_trans'],
                        'kode_gudang' => $v_kv['kode_gudang'],
                        'kode_barang' => $v_kv['kode_barang'],
                        'jumlah_real' => $v_kv['jumlah_real'],
                        'jumlah' => $v_kv['jumlah'],
                        'hrg_jual' => $v_kv['hrg_jual'],
                        'hrg_beli' => $v_kv['hrg_beli'],
                        'kode_trans' => $v_kv['kode_trans'],
                        'dari' => $v_kv['dari'],
                    );

                    ksort($_data_beli[ $v_kv['kode_gudang'] ][ $v_kv['kode_barang'] ]);
                }
            }
        }

        $data_retur = array();
        $data_beli = array();

        if ( !empty($_data_retur) ) {
            foreach ($_data_retur as $k_gdg => $v_gdg) {
                foreach ($v_gdg as $k_item => $v_item) {
                    foreach ($v_item as $key => $value) {
                        $data_retur[ $k_gdg ][ $k_item ][] = $value;
                    }
                }
            }
        }

        if ( !empty($_data_beli) ) {
            foreach ($_data_beli as $k_gdg => $v_gdg) {
                foreach ($v_gdg as $k_item => $v_item) {
                    foreach ($v_item as $key => $value) {
                        $data_beli[ $k_gdg ][ $k_item ][] = $value;
                    }
                }
            }
        }

        $data = array(
            'retur' => $data_retur,
            'beli' => $data_beli
        );

        return $data;
    }

    public function get_data_keluar_voadip($startDate, $endDate)
    {
        $_data = null;

        $tgl_opname = '2022-09-06';

        // RETUR
        $m_rv = new \Model\Storage\ReturVoadip_model();
        $sql = "
            select 
                drv.id as _key,
                rv.no_retur as no_retur,
                rv.tgl_retur as tgl_trans, 
                g.id as kode_gudang,
                drv.item as kode_barang,
                drv.jumlah as jumlah,
                rv.no_order as kode_trans,
                'RETUR' as 'dari'
            from det_retur_voadip drv 
            left join
                retur_voadip rv 
                on
                    drv.id_header = rv.id
            left join
                kirim_voadip kv 
                on
                    rv.no_order = kv.no_order
            left join
                det_kirim_voadip dkv 
                on
                    kv.id = dkv.id_header and
                    drv.item = dkv.item 
            left join
                (select max(id) as id, nama from gudang group by nama) as g
                on
                    rv.id_asal = g.nama
            where
                rv.tgl_retur BETWEEN '$startDate' AND '$endDate' and
                rv.asal = 'gudang' and
                drv.jumlah > 0
        ";

        $d_rv = $m_rv->hydrateRaw($sql);
        if ( $d_rv->count() > 0 ) {
            $d_rv = $d_rv->toArray();

            foreach ($d_rv as $k_rv => $v_rv) {
                if ( $v_rv['tgl_trans'] > $tgl_opname ) {
                    $key = str_replace('-', '', $v_rv['tgl_trans']).' | '.$v_rv['no_retur'];

                    $_data[ $v_rv['kode_gudang'] ][ $v_rv['kode_barang'] ][ $key ] = array(
                        'key' => $v_rv['_key'],
                        'tgl_trans' => $v_rv['tgl_trans'],
                        'kode_gudang' => $v_rv['kode_gudang'],
                        'kode_barang' => $v_rv['kode_barang'],
                        'jumlah' => $v_rv['jumlah'],
                        'kode_trans' => $v_rv['kode_trans'],
                        'kode_trans_tujuan' => null,
                        'jenis_tujuan' => null,
                        'tujuan' => null,
                        'dari' => 'RETUR'
                    );

                    ksort($_data[ $v_rv['kode_gudang'] ][ $v_rv['kode_barang'] ]);
                }
            }
        }

        // ORDER
        $m_kv = new \Model\Storage\KirimVoadip_model();
        $sql = "
            select 
                dkv.id as _key,
                kv.tgl_kirim as tgl_trans, 
                kv.asal as kode_gudang,
                kv.jenis_tujuan as jenis_tujuan,
                kv.tujuan as tujuan,
                dkv.item as kode_barang,
                dtv.jumlah as jumlah,
                kv.no_order as kode_trans,
                'ORDER' as 'dari'
            from det_kirim_voadip dkv 
            left join
                kirim_voadip kv 
                on
                    dkv.id_header = kv.id
            left join
                terima_voadip tv 
                on
                    kv.id = tv.id_kirim_voadip 
            left join
                det_terima_voadip dtv 
                on
                    tv.id = dtv.id_header and
                    dkv.item = dtv.item
            where
                kv.tgl_kirim BETWEEN '$startDate' AND '$endDate' and
                kv.jenis_kirim = 'opkg' and
                dtv.jumlah > 0
        ";

        $d_kv = $m_kv->hydrateRaw($sql);
        if ( $d_kv->count() > 0 ) {
            $d_kv = $d_kv->toArray();

            foreach ($d_kv as $k_kv => $v_kv) {
                if ( $v_kv['tgl_trans'] > $tgl_opname ) {
                    $key = str_replace('-', '', $v_kv['tgl_trans']).' | '.$v_kv['kode_trans'];

                    $_data[ $v_kv['kode_gudang'] ][ $v_kv['kode_barang'] ][ $key ] = array(
                        'key' => $v_kv['_key'],
                        'tgl_trans' => $v_kv['tgl_trans'],
                        'kode_gudang' => $v_kv['kode_gudang'],
                        'kode_barang' => $v_kv['kode_barang'],
                        'jumlah' => $v_kv['jumlah'],
                        'kode_trans' => $v_kv['kode_trans'],
                        'kode_trans_tujuan' => null,
                        'jenis_tujuan' => $v_kv['jenis_tujuan'],
                        'tujuan' => $v_kv['tujuan'],
                        'dari' => $v_kv['dari']
                    );

                    ksort($_data[ $v_kv['kode_gudang'] ][ $v_kv['kode_barang'] ]);
                }
            }
        }

        // ADJUSTMENT OUT
        $m_adjout = new \Model\Storage\AdjoutVoadip_model();
        $sql = "
            select
                adjout.id as _key,
                adjout.tanggal as tgl_trans, 
                adjout.kode_gudang as kode_gudang,
                '' as jenis_tujuan,
                '' as tujuan,
                adjout.kode_barang as kode_barang,
                adjout.jumlah as jumlah,
                adjout.kode as kode_trans,
                adjout.kode_trans as kode_trans_tujuan,
                'ADJOUT' as 'dari'
            from adjout_voadip adjout
            where
                adjout.tanggal BETWEEN '$startDate' AND '$endDate'
        ";

        $d_adjout = $m_adjout->hydrateRaw($sql);
        if ( $d_adjout->count() > 0 ) {
            $d_adjout = $d_adjout->toArray();

            foreach ($d_adjout as $k_adjout => $v_adjout) {
                if ( $v_adjout['tgl_trans'] > $tgl_opname ) {
                    $key = str_replace('-', '', $v_adjout['tgl_trans']).' | '.$v_adjout['kode_trans'];

                    $_data[ $v_adjout['kode_gudang'] ][ $v_adjout['kode_barang'] ][ $key ] = array(
                        'key' => $v_adjout['_key'],
                        'tgl_trans' => $v_adjout['tgl_trans'],
                        'kode_gudang' => $v_adjout['kode_gudang'],
                        'kode_barang' => $v_adjout['kode_barang'],
                        'jumlah' => $v_adjout['jumlah'],
                        'kode_trans' => $v_adjout['kode_trans'],
                        'kode_trans_tujuan' => $v_adjout['kode_trans_tujuan'],
                        'jenis_tujuan' => null,
                        'tujuan' => null,
                        'dari' => $v_adjout['dari']
                    );

                    ksort($_data[ $v_adjout['kode_gudang'] ][ $v_adjout['kode_barang'] ]);
                }
            }
        }

        $data = array();
        if ( !empty($_data) ) {
            foreach ($_data as $k_gdg => $v_gdg) {
                foreach ($v_gdg as $k_item => $v_item) {
                    foreach ($v_item as $k_data => $v_data) {
                        $data[ $k_gdg ][ $k_item ][] = $v_data;
                    }
                }
            }
        }

        return $data;
    }

    public function hitung_stok_voadip($startDate, $endDate)
    {
        $_startDate = $startDate;

        $today = date('Y-m-d');

        while ($startDate <= $endDate) {
            if ( $startDate <= $today ) {
                $m_stok = new \Model\Storage\Stok_model();
                $now = $m_stok->getDate();
                $d_stok = $m_stok->where('periode', $startDate)->first();

                $stok_id = null;
                if ( $d_stok ) {
                    $stok_id = $d_stok->id;
                } else {
                    $m_stok->periode = $startDate;
                    $m_stok->user_proses = $this->userid;
                    $m_stok->tgl_proses = $now['waktu'];
                    $m_stok->save();

                    $stok_id = $m_stok->id;
                }

                $conf = new \Model\Storage\Conf();
                // $sql = "EXEC get_data_stok_voadip_by_tanggal @date = '$startDate'";
                $sql = "EXEC copy_stok '$startDate', 'voadip'";

                $d_conf = $conf->hydrateRaw($sql);

                if ( $d_conf->count() > 0 ) {
                    $_data_keluar = $this->get_data_keluar_voadip($startDate, $startDate);

                    // $d_conf = $d_conf->toArray();
                    // $jml_data = count($d_conf);
                    // $idx = 0;
                    // foreach ($d_conf as $k_conf => $v_conf) {
                    //     $m_ds = new \Model\Storage\DetStok_model();
                    //     $m_ds->id_header = $stok_id;
                    //     $m_ds->tgl_trans = $v_conf['tgl_trans'];
                    //     $m_ds->kode_gudang = $v_conf['kode_gudang'];
                    //     $m_ds->kode_barang = $v_conf['kode_barang'];
                    //     $m_ds->jumlah = $v_conf['jumlah'];
                    //     $m_ds->hrg_jual = $v_conf['hrg_jual'];
                    //     $m_ds->hrg_beli = $v_conf['hrg_beli'];
                    //     $m_ds->kode_trans = $v_conf['kode_trans'];
                    //     $m_ds->jenis_barang = $v_conf['jenis_barang'];
                    //     $m_ds->jenis_trans = $v_conf['jenis_trans'];
                    //     $m_ds->jml_stok = $v_conf['jml_stok'];
                    //     $m_ds->save();

                    //     $idx++;

                    //     if ( $jml_data == $idx ) {
                            if ( !empty($_data_keluar) ) {
                                foreach ($_data_keluar as $k_gdg => $v_gdg) {
                                    foreach ($v_gdg as $k_brg => $v_brg) {
                                        foreach ($v_brg as $k_dk => $v_dk) {
                                            $jml_keluar = (float) $v_dk['jumlah'];

                                            while ($jml_keluar > 0) {
                                                // $m_ds = new \Model\Storage\DetStok_model();
                                                // $d_ds = $m_ds->where('id_header', $stok_id)->where('tgl_trans', '<=', $v_dk['tgl_trans'])->where('kode_gudang', $k_gdg)->where('kode_barang', $k_brg)->where('jml_stok', '>', 0)->orderBy('id', 'asc')->first();

                                                $sql_kode_trans_tujuan = '';
                                                if ( isset($v_dk['kode_trans_tujuan']) && !empty($v_dk['kode_trans_tujuan']) ) {
                                                    $kode_trans_tujuan = str_replace('SJ', 'OP', $v_dk['kode_trans_tujuan']);

                                                    $sql_kode_trans_tujuan = "ds.kode_trans = '".$kode_trans_tujuan."' and";
                                                }

                                                $m_ds = new \Model\Storage\DetStok_model();
                                                $sql = "
                                                    select top 1 * from det_stok ds 
                                                    where
                                                        ds.id_header = ".$stok_id." and 
                                                        ds.tgl_trans <= '".$v_dk['tgl_trans']."' and 
                                                        ds.kode_gudang = ".$v_dk['kode_gudang']." and 
                                                        ds.kode_barang = '".$v_dk['kode_barang']."' and 
                                                        ".$sql_kode_trans_tujuan."
                                                        ds.jml_stok > 0
                                                    order by
                                                        ds.jenis_trans desc,
                                                        ds.tgl_trans asc,
                                                        ds.kode_trans asc,
                                                        ds.id asc
                                                ";

                                                $d_ds = $m_ds->hydrateRaw($sql);

                                                // if ( $v_dk['kode_barang'] == 'OB2109046' && $v_dk['kode_gudang'] == 30 ) {
                                                //     cetak_r( $v_dk['kode_trans'] );
                                                // }

                                                // if ( $v_dk['kode_trans'] == 'OP/MJK/22/10067' && $v_dk['kode_barang'] == 'OB2109010' ) {
                                                //     cetak_r($v_dk, 1);
                                                //     // cetak_r($d_ds);
                                                //     // cetak_r($jml_keluar);
                                                // }

                                                if ( $d_ds->count() > 0 ) {
                                                    $d_ds = $d_ds->toArray()[0];

                                                    $jml_masuk = $d_ds['jml_stok'];
                                                    if ( $jml_keluar <= $jml_masuk ) {
                                                        $m_dst = new \Model\Storage\DetStokTrans_model();
                                                        $m_dst->id_header = $d_ds['id'];
                                                        $m_dst->kode_trans = $v_dk['kode_trans'];
                                                        $m_dst->jumlah = $jml_keluar;
                                                        $m_dst->kode_barang = $v_dk['kode_barang'];
                                                        $m_dst->save();

                                                        $sisa_stok = $jml_masuk - $jml_keluar;
                                                        $m_ds->where('id', $d_ds['id'])->update(
                                                            array(
                                                                'jml_stok' => $sisa_stok
                                                            )
                                                        );

                                                        if ( !empty($v_dk['jenis_tujuan']) && $v_dk['jenis_tujuan'] == 'gudang' ) {
                                                            // if ( $v_dk['kode_trans'] == 'OP/KDR/22/09021' && $k_brg == 'OB2109007' ) {
                                                            //     cetak_r( $v_conf );
                                                            // }
                                                            $m_ds = new \Model\Storage\DetStok_model();
                                                            $_d_ds = $m_ds->where('id_header', $stok_id)
                                                                         ->where('tgl_trans', $v_dk['tgl_trans'])
                                                                         ->where('kode_gudang', $v_dk['tujuan'])
                                                                         ->where('kode_barang', $k_brg)
                                                                         ->where('hrg_jual', $d_ds['hrg_jual'])
                                                                         ->where('hrg_beli', $d_ds['hrg_beli'])
                                                                         ->where('kode_trans', $v_dk['kode_trans'])
                                                                         ->first();

                                                            if ( !$_d_ds ) {
                                                                $m_ds = new \Model\Storage\DetStok_model();
                                                                $m_ds->id_header = $stok_id;
                                                                $m_ds->tgl_trans = $v_dk['tgl_trans'];
                                                                $m_ds->kode_gudang = $v_dk['tujuan'];
                                                                $m_ds->kode_barang = $k_brg;
                                                                $m_ds->jumlah = $jml_keluar;
                                                                $m_ds->hrg_jual = $d_ds['hrg_jual'];
                                                                $m_ds->hrg_beli = $d_ds['hrg_beli'];
                                                                $m_ds->kode_trans = $v_dk['kode_trans'];
                                                                $m_ds->jenis_barang = $d_ds['jenis_barang'];
                                                                $m_ds->jenis_trans = $v_dk['dari'];
                                                                $m_ds->jml_stok = $jml_keluar;
                                                                $m_ds->save();
                                                            } else {
                                                                $m_ds = new \Model\Storage\DetStok_model();
                                                                $m_ds->where('id_header', $stok_id)
                                                                     ->where('tgl_trans', $v_dk['tgl_trans'])
                                                                     ->where('kode_gudang', $v_dk['tujuan'])
                                                                     ->where('kode_barang', $k_brg)
                                                                     ->where('hrg_jual', $d_ds['hrg_jual'])
                                                                     ->where('hrg_beli', $d_ds['hrg_beli'])
                                                                     ->where('kode_trans', $v_dk['kode_trans'])
                                                                     ->update(
                                                                        array(
                                                                            'jumlah' => ($jml_keluar + $_d_ds->jumlah),
                                                                            'jml_stok' => ($jml_keluar + $_d_ds->jml_stok)
                                                                        )
                                                                     );
                                                            }
                                                        }

                                                        $jml_keluar = 0;
                                                    } else {
                                                        $m_dst = new \Model\Storage\DetStokTrans_model();
                                                        $m_dst->id_header = $d_ds['id'];
                                                        $m_dst->kode_trans = $v_dk['kode_trans'];
                                                        $m_dst->jumlah = $jml_masuk;
                                                        $m_dst->kode_barang = $v_dk['kode_barang'];
                                                        $m_dst->save();
                                                        
                                                        $sisa_stok = 0;
                                                        $m_ds->where('id', $d_ds['id'])->update(
                                                            array(
                                                                'jml_stok' => $sisa_stok
                                                            )
                                                        );

                                                        if ( !empty($v_dk['jenis_tujuan']) && $v_dk['jenis_tujuan'] == 'gudang' ) {
                                                            // if ( $v_dk['kode_trans'] == 'OP/KDR/22/09021' && $k_brg == 'OB2109007' ) {
                                                            //     cetak_r( $v_conf );
                                                            // }

                                                            $m_ds = new \Model\Storage\DetStok_model();
                                                            $_d_ds = $m_ds->where('id_header', $stok_id)
                                                                         ->where('tgl_trans', $v_dk['tgl_trans'])
                                                                         ->where('kode_gudang', $v_dk['tujuan'])
                                                                         ->where('kode_barang', $k_brg)
                                                                         ->where('hrg_jual', $d_ds['hrg_jual'])
                                                                         ->where('hrg_beli', $d_ds['hrg_beli'])
                                                                         ->where('kode_trans', $v_dk['kode_trans'])
                                                                         ->first();

                                                            if ( !$_d_ds ) {
                                                                $m_ds = new \Model\Storage\DetStok_model();
                                                                $m_ds->id_header = $stok_id;
                                                                $m_ds->tgl_trans = $v_dk['tgl_trans'];
                                                                $m_ds->kode_gudang = $v_dk['tujuan'];
                                                                $m_ds->kode_barang = $k_brg;
                                                                $m_ds->jumlah = $jml_masuk;
                                                                $m_ds->hrg_jual = $d_ds['hrg_jual'];
                                                                $m_ds->hrg_beli = $d_ds['hrg_beli'];
                                                                $m_ds->kode_trans = $v_dk['kode_trans'];
                                                                $m_ds->jenis_barang = $d_ds['jenis_barang'];
                                                                $m_ds->jenis_trans = $v_dk['dari'];
                                                                $m_ds->jml_stok = $jml_masuk;
                                                                $m_ds->save();
                                                            } else {
                                                                $m_ds = new \Model\Storage\DetStok_model();
                                                                $m_ds->where('id_header', $stok_id)
                                                                     ->where('tgl_trans', $v_dk['tgl_trans'])
                                                                     ->where('kode_gudang', $v_dk['tujuan'])
                                                                     ->where('kode_barang', $k_brg)
                                                                     ->where('hrg_jual', $d_ds['hrg_jual'])
                                                                     ->where('hrg_beli', $d_ds['hrg_beli'])
                                                                     ->where('kode_trans', $v_dk['kode_trans'])
                                                                     ->update(
                                                                        array(
                                                                            'jumlah' => ($jml_masuk + $_d_ds->jumlah),
                                                                            'jml_stok' => ($jml_masuk + $_d_ds->jml_stok)
                                                                        )
                                                                     );
                                                            }
                                                        }

                                                        $jml_keluar = $jml_keluar - $jml_masuk;
                                                    }
                                                } else {
                                                    $jml_keluar = 0;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                    //     }
                    // }
                }
            }

            $startDate = next_date( $startDate );
        }
    }

    public function get_data_masuk_pakan($startDate, $endDate)
    {
        $m_stok = new \Model\Storage\Stok_model();

        $d_stok = $m_stok->where('periode', '<', $startDate)->orderBy('periode', 'desc')->first();

        $data = null;
        $_data_retur = array();
        $_data_beli = array();

        // STOK AWAL
        if ( $d_stok ) {
            $d_stok = $d_stok->toArray();

            $m_dstok = new \Model\Storage\DetStok_model();
            $d_dstok = $m_dstok->where('id_header', $d_stok['id'])->where('jenis_barang', 'pakan')->orderBy('tgl_trans', 'asc')->get()->toArray();

            foreach ($d_dstok as $k_det => $v_det) {
                $isi_retur = 1;
                if ( isset($_data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]) ) {
                    foreach ($_data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ] as $k => $val) {
                        if ( $val['kode_trans'] == $v_det['kode_trans'] ) {
                            $isi_retur = 0;
                        }
                    }
                }

                $isi_beli = 1;
                if ( isset($_data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]) ) {
                    foreach ($_data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ] as $k => $val) {
                        if ( $val['kode_trans'] == $v_det['kode_trans'] ) {
                            $isi_beli = 0;
                        }
                    }
                }

                if ( $isi_retur == 1 ) {
                    if ( $v_det['jenis_trans'] == 'RETUR' ) {
                        if ( $v_det['jumlah'] > 0 ) {
                            $key = str_replace('-', '', $v_det['tgl_trans']).' | '.$v_det['kode_trans'];

                            $_data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][ $key ] = array(
                                'tgl_trans' => $v_det['tgl_trans'],
                                'kode_gudang' => $v_det['kode_gudang'],
                                'kode_barang' => $v_det['kode_barang'],
                                'jumlah_real' => $v_det['jumlah'],
                                'jumlah' => $v_det['jumlah'],
                                'hrg_jual' => $v_det['hrg_jual'],
                                'hrg_beli' => $v_det['hrg_beli'],
                                'kode_trans' => $v_det['kode_trans'],
                                'dari' => $v_det['jenis_trans']
                            );

                            ksort($_data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]);
                        }
                    }
                }

                if ( $isi_beli == 1 ) {
                    if ( $v_det['jenis_trans'] != 'RETUR' ) {
                        if ( $v_det['jumlah'] > 0 ) {
                            $key = str_replace('-', '', $v_det['tgl_trans']).' | '.$v_det['kode_trans'];

                            $_data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][ $key ] = array(
                                'tgl_trans' => $v_det['tgl_trans'],
                                'kode_gudang' => $v_det['kode_gudang'],
                                'kode_barang' => $v_det['kode_barang'],
                                'jumlah_real' => $v_det['jumlah'],
                                'jumlah' => $v_det['jumlah'],
                                'hrg_jual' => $v_det['hrg_jual'],
                                'hrg_beli' => $v_det['hrg_beli'],
                                'kode_trans' => $v_det['kode_trans'],
                                'dari' => $v_det['jenis_trans']
                            );


                            ksort($_data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]);
                        }
                    }
                }
            }
        }

        // RETUR
        $m_rp = new \Model\Storage\ReturPakan_model();
        $sql = "
            select 
                rp.tgl_retur as tgl_trans, 
                rp.id_tujuan as kode_gudang,
                drp.item as kode_barang,
                drp.jumlah as jumlah_real,
                drp.jumlah as jumlah,
                (dkp.nilai_jual / dkp.jumlah) as hrg_jual,
                (dkp.nilai_beli / dkp.jumlah) as hrg_beli,
                rp.id as kode_trans,
                'RETUR' as 'dari'
            from det_retur_pakan drp 
            left join
                retur_pakan rp 
                on
                    drp.id_header = rp.id
            left join
                kirim_pakan kp 
                on
                    rp.no_order = kp.no_order
            left join
                det_kirim_pakan dkp 
                on
                    kp.id = dkp.id_header and
                    drp.item = dkp.item 
            where
                rp.tgl_retur BETWEEN '$startDate' AND '$endDate' and
                rp.tujuan = 'gudang' and
                drp.jumlah > 0
        ";

        $d_rp = $m_rp->hydrateRaw($sql);
        if ( $d_rp->count() > 0 ) {
            $d_rp = $d_rp->toArray();

            foreach ($d_rp as $k_rp => $v_rp) {
                $key = str_replace('-', '', $v_rp['kode_barang']).' | '.$v_rp['kode_trans'];

                $_data_retur[ $v_rp['kode_gudang'] ][ $v_rp['kode_barang'] ][ $key ] = array(
                    'tgl_trans' => $v_rp['tgl_trans'],
                    'kode_gudang' => $v_rp['kode_gudang'],
                    'kode_barang' => $v_rp['kode_barang'],
                    'jumlah_real' => $v_rp['jumlah_real'],
                    'jumlah' => $v_rp['jumlah'],
                    'hrg_jual' => $v_rp['hrg_jual'],
                    'hrg_beli' => $v_rp['hrg_beli'],
                    'kode_trans' => $v_rp['kode_trans'],
                    'dari' => $v_rp['dari'],
                );

                ksort($_data_retur[ $v_rp['kode_gudang'] ][ $v_rp['kode_barang'] ]);
            }
        }

        // ORDER
        $m_kp = new \Model\Storage\KirimPakan_model();
        $sql = "
            select 
                kp.tgl_kirim as tgl_trans, 
                kp.tujuan as kode_gudang,
                dkp.item as kode_barang,
                dtp.jumlah as jumlah_real,
                dtp.jumlah as jumlah,
                CASE
                    WHEN kp.jenis_kirim = 'opks'
                        THEN ovd.harga_jual
                        ELSE dkp.nilai_jual / dkp.jumlah 
                END as hrg_jual,
                CASE
                    WHEN kp.jenis_kirim = 'opks'
                        THEN ovd.harga
                        ELSE dkp.nilai_beli / dkp.jumlah 
                END as hrg_beli,
                kp.no_order as kode_trans,
                'ORDER' as 'dari'
            from det_kirim_pakan dkp 
            left join
                kirim_pakan kp 
                on
                    dkp.id_header = kp.id
            left join
                terima_pakan tp 
                on
                    kp.id = tp.id_kirim_pakan 
            left join
                det_terima_pakan dtp 
                on
                    tp.id = dtp.id_header and
                    dkp.item = dtp.item
            left join
                order_pakan ov 
                on
                    kp.no_order = ov.no_order 
            left join
                order_pakan_detail ovd 
                on
                    ov.id = ovd.id_header and
                    ovd.barang = dkp.item 
            where
                kp.tgl_kirim BETWEEN '$startDate' AND '$endDate' and
                kp.jenis_tujuan = 'gudang' and
                dtp.jumlah > 0
        ";

        $d_kp = $m_kp->hydrateRaw($sql);
        if ( $d_kp->count() > 0 ) {
            $d_kp = $d_kp->toArray();

            foreach ($d_kp as $k_kp => $v_kp) {
                $key = str_replace('-', '', $v_kp['kode_barang']).' | '.$v_kp['kode_trans'];

                $_data_beli[ $v_kp['kode_gudang'] ][ $v_kp['kode_barang'] ][ $key ] = array(
                    'tgl_trans' => $v_kp['tgl_trans'],
                    'kode_gudang' => $v_kp['kode_gudang'],
                    'kode_barang' => $v_kp['kode_barang'],
                    'jumlah_real' => $v_kp['jumlah_real'],
                    'jumlah' => $v_kp['jumlah'],
                    'hrg_jual' => $v_kp['hrg_jual'],
                    'hrg_beli' => $v_kp['hrg_beli'],
                    'kode_trans' => $v_kp['kode_trans'],
                    'dari' => $v_kp['dari'],
                );

                ksort($_data_beli[ $v_kp['kode_gudang'] ][ $v_kp['kode_barang'] ]);
            }
        }

        $data_retur = array();
        $data_beli = array();

        if ( !empty($_data_retur) ) {
            foreach ($_data_retur as $k_gdg => $v_gdg) {
                foreach ($v_gdg as $k_item => $v_item) {
                    foreach ($v_item as $key => $value) {
                        $data_retur[ $k_gdg ][ $k_item ][] = $value;
                    }
                }
            }
        }

        if ( !empty($_data_beli) ) {
            foreach ($_data_beli as $k_gdg => $v_gdg) {
                foreach ($v_gdg as $k_item => $v_item) {
                    foreach ($v_item as $key => $value) {
                        $data_beli[ $k_gdg ][ $k_item ][] = $value;
                    }
                }
            }
        }

        $data = array(
            'retur' => $data_retur,
            'beli' => $data_beli
        );

        return $data;
    }

    public function get_data_keluar_pakan($startDate, $endDate)
    {
        $_data = null;

        // RETUR
        $m_rp = new \Model\Storage\ReturPakan_model();
        $sql = "
            select 
                drp.id as _key,
                rp.tgl_retur as tgl_trans, 
                rp.id_asal as kode_gudang,
                drp.item as kode_barang,
                drp.jumlah as jumlah,
                rp.no_order as kode_trans,
                'RETUR' as 'dari'
            from det_retur_pakan drp 
            left join
                retur_pakan rp 
                on
                    drp.id_header = rp.id
            left join
                kirim_pakan kp 
                on
                    rp.no_order = kp.no_order
            left join
                det_kirim_pakan dkp 
                on
                    kp.id = dkp.id_header and
                    drp.item = dkp.item 
            where
                rp.tgl_retur BETWEEN '$startDate' AND '$endDate' and
                rp.asal = 'gudang' and
                drp.jumlah > 0
        ";

        $d_rp = $m_rp->hydrateRaw($sql);
        if ( $d_rp->count() > 0 ) {
            $d_rp = $d_rp->toArray();

            foreach ($d_rp as $k_rp => $v_rp) {
                $key = str_replace('-', '', $v_rp['tgl_trans']).' | '.$v_rp['_key'];

                $_data[ $v_rp['kode_gudang'] ][ $v_rp['kode_barang'] ][ $key ] = array(
                    'key' => $v_rp['_key'],
                    'tgl_trans' => $v_rp['tgl_trans'],
                    'kode_gudang' => $v_rp['kode_gudang'],
                    'kode_barang' => $v_rp['kode_barang'],
                    'jumlah' => $v_rp['jumlah'],
                    'kode_trans' => $v_rp['kode_trans'],
                    'jenis_tujuan' => null,
                    'tujuan' => null,
                    'dari' => 'RETUR'
                );

                ksort($_data[ $v_rp['kode_gudang'] ][ $v_rp['kode_barang'] ]);
            }
        }

        // ORDER
        $m_kp = new \Model\Storage\KirimPakan_model();
        $sql = "
            select 
                dkp.id as _key,
                kp.tgl_kirim as tgl_trans, 
                kp.asal as kode_gudang,
                dkp.item as kode_barang,
                dtp.jumlah as jumlah,
                kp.no_order as kode_trans,
                kp.jenis_tujuan as jenis_tujuan,
                kp.tujuan as tujuan,
                'ORDER' as 'dari'
            from det_kirim_pakan dkp 
            left join
                kirim_pakan kp 
                on
                    dkp.id_header = kp.id
            left join
                terima_pakan tp 
                on
                    kp.id = tp.id_kirim_pakan 
            left join
                det_terima_pakan dtp 
                on
                    tp.id = dtp.id_header and
                    dkp.item = dtp.item
            where
                kp.tgl_kirim BETWEEN '$startDate' AND '$endDate' and
                kp.jenis_kirim = 'opkg' and
                dtp.jumlah > 0
        ";

        $d_kp = $m_kp->hydrateRaw($sql);
        if ( $d_kp->count() > 0 ) {
            $d_kp = $d_kp->toArray();

            foreach ($d_kp as $k_kp => $v_kp) {
                $key = str_replace('-', '', $v_kp['tgl_trans']).' | '.$v_kp['kode_trans'];

                $_data[ $v_kp['kode_gudang'] ][ $v_kp['kode_barang'] ][ $key ] = array(
                    'key' => $v_kp['_key'],
                    'tgl_trans' => $v_kp['tgl_trans'],
                    'kode_gudang' => $v_kp['kode_gudang'],
                    'kode_barang' => $v_kp['kode_barang'],
                    'jumlah' => $v_kp['jumlah'],
                    'kode_trans' => $v_kp['kode_trans'],
                    'jenis_tujuan' => $v_kp['jenis_tujuan'],
                    'tujuan' => $v_kp['tujuan'],
                    'dari' => 'RETUR'
                );

                ksort($_data[ $v_kp['kode_gudang'] ][ $v_kp['kode_barang'] ]);
            }
        }

        // ADJUSTMENT OUT
        $m_adjout = new \Model\Storage\AdjoutPakan_model();
        $sql = "
            select
                adjout.id as _key,
                adjout.tanggal as tgl_trans, 
                adjout.kode_gudang as kode_gudang,
                '' as jenis_tujuan,
                '' as tujuan,
                adjout.kode_barang as kode_barang,
                adjout.jumlah as jumlah,
                adjout.kode as kode_trans,
                '' as kode_trans_tujuan,
                'ADJOUT' as 'dari'
            from adjout_pakan adjout
            where
                adjout.tanggal BETWEEN '$startDate' AND '$endDate'
        ";

        $d_adjout = $m_adjout->hydrateRaw($sql);
        if ( $d_adjout->count() > 0 ) {
            $d_adjout = $d_adjout->toArray();

            foreach ($d_adjout as $k_adjout => $v_adjout) {
                $key = str_replace('-', '', $v_adjout['tgl_trans']).' | '.$v_adjout['kode_trans'];

                $_data[ $v_adjout['kode_gudang'] ][ $v_adjout['kode_barang'] ][ $key ] = array(
                    'key' => $v_adjout['_key'],
                    'tgl_trans' => $v_adjout['tgl_trans'],
                    'kode_gudang' => $v_adjout['kode_gudang'],
                    'kode_barang' => $v_adjout['kode_barang'],
                    'jumlah' => $v_adjout['jumlah'],
                    'kode_trans' => $v_adjout['kode_trans'],
                    'kode_trans_tujuan' => null,
                    'jenis_tujuan' => null,
                    'tujuan' => null,
                    'dari' => $v_adjout['dari']
                );

                ksort($_data[ $v_adjout['kode_gudang'] ][ $v_adjout['kode_barang'] ]);
            }
        }

        $data = array();
        if ( !empty($_data) ) {
            foreach ($_data as $k_gdg => $v_gdg) {
                foreach ($v_gdg as $k_item => $v_item) {
                    foreach ($v_item as $k_data => $v_data) {
                        $data[ $k_gdg ][ $k_item ][] = $v_data;
                    }
                }
            }
        }

        return $data;
    }

    public function hitung_stok_pakan($startDate, $endDate)
    {
        $_startDate = $startDate;

        $today = date('Y-m-d');

        while ($startDate <= $endDate) {
            if ( $startDate <= $today ) {
                $m_stok = new \Model\Storage\Stok_model();
                $d_stok = $m_stok->where('periode', $startDate)->first();

                $stok_id = null;
                if ( $d_stok ) {
                    $stok_id = $d_stok->id;
                } else {
                    $m_stok->periode = $startDate;
                    $m_stok->user_proses = $this->userid;
                    $m_stok->tgl_proses = $now['waktu'];
                    $m_stok->save();

                    $stok_id = $m_stok->id;
                }

                $conf = new \Model\Storage\Conf();
                // $sql = "EXEC get_data_stok_pakan_by_tanggal @date = '$startDate'";
                $sql = "EXEC copy_stok '$startDate', 'pakan'";

                $d_conf = $conf->hydrateRaw($sql);

                if ( $d_conf->count() > 0 ) {
                    $_data_keluar = $this->get_data_keluar_pakan($startDate, $startDate);

                    // $d_conf = $d_conf->toArray();
                    // $jml_data = count($d_conf);
                    // $idx = 0;
                    // foreach ($d_conf as $k_conf => $v_conf) {
                    //     $m_ds = new \Model\Storage\DetStok_model();
                    //     $m_ds->id_header = $stok_id;
                    //     $m_ds->tgl_trans = $v_conf['tgl_trans'];
                    //     $m_ds->kode_gudang = $v_conf['kode_gudang'];
                    //     $m_ds->kode_barang = $v_conf['kode_barang'];
                    //     $m_ds->jumlah = $v_conf['jumlah'];
                    //     $m_ds->hrg_jual = $v_conf['hrg_jual'];
                    //     $m_ds->hrg_beli = $v_conf['hrg_beli'];
                    //     $m_ds->kode_trans = $v_conf['kode_trans'];
                    //     $m_ds->jenis_barang = $v_conf['jenis_barang'];
                    //     $m_ds->jenis_trans = $v_conf['jenis_trans'];
                    //     $m_ds->jml_stok = $v_conf['jml_stok'];
                    //     $m_ds->save();

                    //     $idx++;

                    //     if ( $jml_data == $idx ) {
                            if ( !empty($_data_keluar) ) {
                                foreach ($_data_keluar as $k_gdg => $v_gdg) {
                                    foreach ($v_gdg as $k_brg => $v_brg) {
                                        foreach ($v_brg as $k_dk => $v_dk) {
                                            $jml_keluar = $v_dk['jumlah'];

                                            while ($jml_keluar > 0) {
                                                // $m_ds = new \Model\Storage\DetStok_model();
                                                // $d_ds = $m_ds->where('id_header', $stok_id)->where('tgl_trans', '<=', $v_dk['tgl_trans'])->where('kode_gudang', $k_gdg)->where('kode_barang', $k_brg)->where('jml_stok', '>', 0)->orderBy('id', 'asc')->first();

                                                $m_ds = new \Model\Storage\DetStok_model();
                                                $sql = "
                                                    select top 1 * from det_stok ds 
                                                    where
                                                        ds.id_header = ".$stok_id." and 
                                                        ds.tgl_trans <= '".$v_dk['tgl_trans']."' and 
                                                        ds.kode_gudang = ".$k_gdg." and 
                                                        ds.kode_barang = '".$k_brg."' and 
                                                        ds.jml_stok > 0
                                                    order by
                                                        ds.jenis_trans desc,
                                                        ds.tgl_trans asc,
                                                        ds.id asc
                                                ";

                                                $d_ds = $m_ds->hydrateRaw($sql);

                                                if ( $d_ds->count() > 0 ) {
                                                    $d_ds = $d_ds->toArray()[0];

                                                    $jml_masuk = $d_ds['jml_stok'];
                                                    if ( $jml_keluar <= $jml_masuk ) {
                                                        $m_dst = new \Model\Storage\DetStokTrans_model();
                                                        $m_dst->id_header = $d_ds['id'];
                                                        $m_dst->kode_trans = $v_dk['kode_trans'];
                                                        $m_dst->jumlah = $jml_keluar;
                                                        $m_dst->kode_barang = $v_dk['kode_barang'];
                                                        $m_dst->save();

                                                        $sisa_stok = $jml_masuk - $jml_keluar;
                                                        $m_ds->where('id', $d_ds['id'])->update(
                                                            array(
                                                                'jml_stok' => $sisa_stok
                                                            )
                                                        );

                                                        if ( !empty($v_dk['jenis_tujuan']) && $v_dk['jenis_tujuan'] == 'gudang' ) {
                                                            $m_ds = new \Model\Storage\DetStok_model();
                                                            $_d_ds = $m_ds->where('id_header', $stok_id)
                                                                         ->where('tgl_trans', $v_dk['tgl_trans'])
                                                                         ->where('kode_gudang', $v_dk['tujuan'])
                                                                         ->where('kode_barang', $k_brg)
                                                                         ->where('hrg_jual', $d_ds['hrg_jual'])
                                                                         ->where('hrg_beli', $d_ds['hrg_beli'])
                                                                         ->where('kode_trans', $v_dk['kode_trans'])
                                                                         ->first();

                                                            if ( !$_d_ds ) {
                                                                $m_ds = new \Model\Storage\DetStok_model();
                                                                $m_ds->id_header = $stok_id;
                                                                $m_ds->tgl_trans = $v_dk['tgl_trans'];
                                                                $m_ds->kode_gudang = $v_dk['tujuan'];
                                                                $m_ds->kode_barang = $k_brg;
                                                                $m_ds->jumlah = $jml_keluar;
                                                                $m_ds->hrg_jual = $d_ds['hrg_jual'];
                                                                $m_ds->hrg_beli = $d_ds['hrg_beli'];
                                                                $m_ds->kode_trans = $v_dk['kode_trans'];
                                                                $m_ds->jenis_barang = $d_ds['jenis_barang'];
                                                                $m_ds->jenis_trans = $v_dk['dari'];
                                                                $m_ds->jml_stok = $jml_keluar;
                                                                $m_ds->save();
                                                            } else {
                                                                $m_ds = new \Model\Storage\DetStok_model();
                                                                $m_ds->where('id_header', $stok_id)
                                                                     ->where('tgl_trans', $v_dk['tgl_trans'])
                                                                     ->where('kode_gudang', $v_dk['tujuan'])
                                                                     ->where('kode_barang', $k_brg)
                                                                     ->where('hrg_jual', $d_ds['hrg_jual'])
                                                                     ->where('hrg_beli', $d_ds['hrg_beli'])
                                                                     ->where('kode_trans', $v_dk['kode_trans'])
                                                                     ->update(
                                                                        array(
                                                                            'jumlah' => ($jml_keluar + $_d_ds->jumlah),
                                                                            'jml_stok' => ($jml_keluar + $_d_ds->jml_stok)
                                                                        )
                                                                     );
                                                            }
                                                        }

                                                        $jml_keluar = 0;
                                                    } else {
                                                        $m_dst = new \Model\Storage\DetStokTrans_model();
                                                        $m_dst->id_header = $d_ds['id'];
                                                        $m_dst->kode_trans = $v_dk['kode_trans'];
                                                        $m_dst->jumlah = $jml_masuk;
                                                        $m_dst->kode_barang = $v_dk['kode_barang'];
                                                        $m_dst->save();
                                                        
                                                        $sisa_stok = 0;
                                                        $m_ds->where('id', $d_ds['id'])->update(
                                                            array(
                                                                'jml_stok' => $sisa_stok
                                                            )
                                                        );

                                                        if ( !empty($v_dk['jenis_tujuan']) && $v_dk['jenis_tujuan'] == 'gudang' ) {
                                                            $m_ds = new \Model\Storage\DetStok_model();
                                                            $_d_ds = $m_ds->where('id_header', $stok_id)
                                                                         ->where('tgl_trans', $v_dk['tgl_trans'])
                                                                         ->where('kode_gudang', $v_dk['tujuan'])
                                                                         ->where('kode_barang', $k_brg)
                                                                         ->where('hrg_jual', $d_ds['hrg_jual'])
                                                                         ->where('hrg_beli', $d_ds['hrg_beli'])
                                                                         ->where('kode_trans', $v_dk['kode_trans'])
                                                                         ->first();

                                                            if ( !$_d_ds ) {
                                                                $m_ds = new \Model\Storage\DetStok_model();
                                                                $m_ds->id_header = $stok_id;
                                                                $m_ds->tgl_trans = $v_dk['tgl_trans'];
                                                                $m_ds->kode_gudang = $v_dk['tujuan'];
                                                                $m_ds->kode_barang = $k_brg;
                                                                $m_ds->jumlah = $jml_masuk;
                                                                $m_ds->hrg_jual = $d_ds['hrg_jual'];
                                                                $m_ds->hrg_beli = $d_ds['hrg_beli'];
                                                                $m_ds->kode_trans = $v_dk['kode_trans'];
                                                                $m_ds->jenis_barang = $d_ds['jenis_barang'];
                                                                $m_ds->jenis_trans = $v_dk['dari'];
                                                                $m_ds->jml_stok = $jml_masuk;
                                                                $m_ds->save();
                                                            } else {
                                                                $m_ds = new \Model\Storage\DetStok_model();
                                                                $m_ds->where('id_header', $stok_id)
                                                                     ->where('tgl_trans', $v_dk['tgl_trans'])
                                                                     ->where('kode_gudang', $v_dk['tujuan'])
                                                                     ->where('kode_barang', $k_brg)
                                                                     ->where('hrg_jual', $d_ds['hrg_jual'])
                                                                     ->where('hrg_beli', $d_ds['hrg_beli'])
                                                                     ->where('kode_trans', $v_dk['kode_trans'])
                                                                     ->update(
                                                                        array(
                                                                            'jumlah' => ($jml_masuk + $_d_ds->jumlah),
                                                                            'jml_stok' => ($jml_masuk + $_d_ds->jml_stok)
                                                                        )
                                                                     );
                                                            }
                                                        }

                                                        $jml_keluar = $jml_keluar - $jml_masuk;
                                                    }
                                                } else {
                                                    $jml_keluar = 0;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                    //     }
                    // }
                }
            }

            $startDate = next_date( $startDate );
        }
    }

    public function hitungStokPakanByTanggal($start_date, $end_date)
    {
        $this->hitung_stok_pakan( $start_date, $end_date );
    }

    public function hitungStokVoadipByTanggal($start_date, $end_date)
    {
        $this->hitung_stok_voadip( $start_date, $end_date );
    }

    public function tes()
    {
        // $params = $this->get_data_keluar_pakan('2023-01-01', '2023-01-01');

        // cetak_r( $params );

        $this->hitung_stok_pakan( '2023-03-30', '2023-04-04' );
    }
}