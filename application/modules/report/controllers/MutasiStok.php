<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MutasiStok extends Public_Controller {

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
                "assets/report/mutasi_stok/js/mutasi-stok.js",
            ));
            $this->add_external_css(array(
                "assets/report/mutasi_stok/css/mutasi-stok.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;
            $content['title_menu'] = 'Laporan Mutasi Stok';

            // Load Indexx
            $data['view'] = $this->load->view('report/mutasi_stok/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_gudang_dan_barang()
    {
        $params = $this->input->post('params');

        // $m_gdg = new \Model\Storage\Gudang_model();
        // $d_gdg = $m_gdg->where('jenis', 'like', '%'.$params.'%')->orderBy('nama', 'asc')->get();
        // $data_gdg = null;
        // if ( $d_gdg->count() > 0 ) {
        //     $data_gdg = $d_gdg->toArray();
        // }

        // $m_barang = new \Model\Storage\Barang_model();
        // $d_barang = $m_barang->select('kode')->distinct('kode')->where('tipe', $params)->get();
        // $_data_brg = null;
        // if ( $d_barang->count() > 0 ) {
        //     $d_barang = $d_barang->toArray();
        //     foreach ($d_barang as $k_brg => $v_brg) {
        //         $m_barang = new \Model\Storage\Barang_model();
        //         $_d_barang = $m_barang->where('kode', $v_brg)->where('tipe', $params)->orderBy('version', 'desc')->first();
        //         if ( !empty($_d_barang) ) {
        //             $key = $_d_barang->nama.' | '.$_d_barang->kode;
        //             $_data_brg[$key] = $_d_barang->toArray();
        //         }
        //     }
        // }

        // $data_brg = null;
        // if ( !empty($_data_brg) ) {
        //     ksort($_data_brg);
        //     foreach ($_data_brg as $k_brg => $v_brg) {
        //         $data_brg[] = $v_brg;
        //     }
        // }

        $data_gdg = null;
        $data_brg = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                gdg1.* 
            from gudang gdg1
            where
                gdg1.jenis like '%".$params."%'
            order by
                gdg1.nama asc
        ";
        $d_gdg = $m_conf->hydrateRaw( $sql );
        if ( $d_gdg->count() > 0 ) {
            $data_gdg = $d_gdg->toArray();
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                brg1.* 
            from barang brg1
            right join
                (select max(id) as id, kode from barang group by kode) brg2
                on
                    brg1.id = brg2.id
            where
                brg1.tipe = '".$params."'
            order by
                brg1.nama asc
        ";
        $d_brg = $m_conf->hydrateRaw( $sql );
        if ( $d_brg->count() > 0 ) {
            $data_brg = $d_brg->toArray();
        }

        $data = array(
            'gudang' => $data_gdg,
            'barang' => $data_brg
        );

        $this->result['list_data'] = $data;

        display_json( $this->result );
    }

    // public function get_data_barang_masuk_voadip($periode, $next_periode, $stok, $kode_gudang, $kode_barang)
    // {
    //     $m_stok = new \Model\Storage\Stok_model();

    //     if ( empty($stok) ) {
    //         $_d_stok = $m_stok->where('periode', '<', $next_periode)->orderBy('periode', 'desc')->first();
    //     } else {
    //         $_d_stok = $m_stok->where('periode', '<', $periode)->orderBy('periode', 'desc')->first();
    //     }
    //     $d_stok = null;
    //     $d_stok = $m_stok->where('periode', $periode)->first();
    //     if ( !$d_stok ) {
    //         $d_stok = $_d_stok;
    //     } 
    //     // else {
    //     //     if ( $d_stok->periode >= $periode  ) {
    //     //         $d_stok = null;
    //     //     }
    //     // }

    //     if ( !empty($stok) ) {
    //         $tgl_terakhir = date("Y-m-t", strtotime($next_periode));
    //     } else {
    //         $tgl_terakhir = $next_periode;
    //     }

    //     // $_d_stok = $m_stok->where('periode', '<', $periode)->orderBy('periode', 'desc')->first();
    //     // $d_stok = null;
    //     // // $d_stok = $m_stok->where('periode', $periode)->first();
    //     // // if ( !$d_stok ) {
    //     //     $d_stok = $_d_stok;
    //     // // }

    //     // $tgl_terakhir = date("Y-m-t", strtotime($next_periode));

    //     $data = null;
    //     $data_retur = null;
    //     $data_beli = null;
    //     $d_rv = null;
    //     $d_kv = null;
    //     if ( !$_d_stok ) {
    //         if ( $kode_gudang != 'all' ) {
    //             $m_rv = new \Model\Storage\ReturVoadip_model();
    //             $d_rv = $m_rv->where('tujuan', 'gudang')->where('id_tujuan', $kode_gudang)->where('tgl_retur', '<', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //             $m_kv = new \Model\Storage\KirimVoadip_model();
    //             $d_kv = $m_kv->where('jenis_tujuan', 'gudang')->where('tujuan', $kode_gudang)->where('tgl_kirim', '<', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //         } else {
    //             $m_rv = new \Model\Storage\ReturVoadip_model();
    //             $d_rv = $m_rv->where('tujuan', 'gudang')->where('tgl_retur', '<', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //             $m_kv = new \Model\Storage\KirimVoadip_model();
    //             $d_kv = $m_kv->where('jenis_tujuan', 'gudang')->where('tgl_kirim', '<', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //         }
    //     } else {
    //         if ( $d_stok ) {
    //             $final = $periode;

    //             if ( $kode_gudang != 'all' ) {
    //                 $m_rv = new \Model\Storage\ReturVoadip_model();
    //                 $d_rv = $m_rv->where('tujuan', 'gudang')->where('id_tujuan', $kode_gudang)->where('tgl_retur', '>=', $final)->where('tgl_retur', '<', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //                 $m_kv = new \Model\Storage\KirimVoadip_model();
    //                 $d_kv = $m_kv->where('jenis_tujuan', 'gudang')->where('tujuan', $kode_gudang)->where('tgl_kirim', '>=', $final)->where('tgl_kirim', '<', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //             } else {
    //                 $m_rv = new \Model\Storage\ReturVoadip_model();
    //                 $d_rv = $m_rv->where('tujuan', 'gudang')->where('tgl_retur', '>=', $final)->where('tgl_retur', '<', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //                 $m_kv = new \Model\Storage\KirimVoadip_model();
    //                 $d_kv = $m_kv->where('jenis_tujuan', 'gudang')->where('tgl_kirim', '>=', $final)->where('tgl_kirim', '<', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //             }
    //         } else {
    //             if ( $kode_gudang != 'all' ) {
    //                 $m_rv = new \Model\Storage\ReturVoadip_model();
    //                 $d_rv = $m_rv->where('tujuan', 'gudang')->where('id_tujuan', $kode_gudang)->where('tgl_retur', '<', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //                 $m_kv = new \Model\Storage\KirimVoadip_model();
    //                 $d_kv = $m_kv->where('jenis_tujuan', 'gudang')->where('tujuan', $kode_gudang)->where('tgl_kirim', '<', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //             } else {
    //                 $m_rv = new \Model\Storage\ReturVoadip_model();
    //                 $d_rv = $m_rv->where('tujuan', 'gudang')->where('tgl_retur', '<', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //                 $m_kv = new \Model\Storage\KirimVoadip_model();
    //                 $d_kv = $m_kv->where('jenis_tujuan', 'gudang')->where('tgl_kirim', '<', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //             }
    //         }
    //     }

    //     // if ( empty($stok) ) {
    //         // STOK AWAL
    //         if ( $_d_stok ) {
    //             // $d_stok = $d_stok->toArray();
    //             $m_dstok = new \Model\Storage\DetStok_model();
    //             if ( $kode_barang != 'all' && $kode_gudang != 'all' ) {
    //                 $d_dstok = $m_dstok->where('id_header', $_d_stok->id)->where('jenis_barang', 'voadip')->where('kode_barang', $kode_barang)->where('kode_gudang', $kode_gudang)->orderBy('tgl_trans', 'asc')->get();
    //             } else if ( $kode_barang != 'all' && $kode_gudang == 'all' ) {
    //                 $d_dstok = $m_dstok->where('id_header', $_d_stok->id)->where('jenis_barang', 'voadip')->where('kode_barang', $kode_barang)->orderBy('tgl_trans', 'asc')->get();
    //             } else if ( $kode_barang == 'all' && $kode_gudang != 'all' ) {
    //                 $d_dstok = $m_dstok->where('id_header', $_d_stok->id)->where('jenis_barang', 'voadip')->where('kode_gudang', $kode_gudang)->orderBy('tgl_trans', 'asc')->get();
    //             } else if ( $kode_barang == 'all' && $kode_gudang == 'all' ) {
    //                 $d_dstok = $m_dstok->where('id_header', $_d_stok->id)->where('jenis_barang', 'voadip')->orderBy('tgl_trans', 'asc')->get();
    //             }

    //             if ( $d_dstok->count() > 0 ) {
    //                 $d_dstok = $d_dstok->toArray();
    //                 foreach ($d_dstok as $k_det => $v_det) {
    //                     $isi = 1;
    //                     if ( isset($data[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]) ) {
    //                         foreach ($data[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ] as $k => $val) {
    //                             if ( $val['kode_trans'] == $v_det['kode_trans'] ) {
    //                                 $isi = 0;
    //                             }
    //                         }
    //                     }

    //                     if ( $isi == 1 ) {
    //                         if ( $v_det['jenis_trans'] == 'RETUR' ) {
    //                             if ( empty($stok) ) {
    //                                 $data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][] = array(
    //                                     'tgl_trans' => $v_det['tgl_trans'],
    //                                     'kode_gudang' => $v_det['kode_gudang'],
    //                                     'kode_barang' => $v_det['kode_barang'],
    //                                     'jumlah' => $v_det['jumlah'],
    //                                     'hrg_jual' => $v_det['hrg_jual'],
    //                                     'hrg_beli' => $v_det['hrg_beli'],
    //                                     'kode_trans' => $v_det['kode_trans'],
    //                                     'dari' => $v_det['jenis_trans']
    //                                 );
    //                             } else {
    //                                 $data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][ $v_det['tgl_trans'] ][] = array(
    //                                     'tgl_trans' => $v_det['tgl_trans'],
    //                                     'kode_gudang' => $v_det['kode_gudang'],
    //                                     'kode_barang' => $v_det['kode_barang'],
    //                                     'jumlah' => $v_det['jumlah'],
    //                                     'hrg_jual' => $v_det['hrg_jual'],
    //                                     'hrg_beli' => $v_det['hrg_beli'],
    //                                     'kode_trans' => $v_det['kode_trans'],
    //                                     'dari' => $v_det['jenis_trans']
    //                                 );
    //                             }

    //                             // ksort($data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]);
    //                         } else {
    //                             if ( empty($stok) ) {
    //                                 $data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][] = array(
    //                                     'tgl_trans' => $v_det['tgl_trans'],
    //                                     'kode_gudang' => $v_det['kode_gudang'],
    //                                     'kode_barang' => $v_det['kode_barang'],
    //                                     'jumlah' => $v_det['jumlah'],
    //                                     'hrg_jual' => $v_det['hrg_jual'],
    //                                     'hrg_beli' => $v_det['hrg_beli'],
    //                                     'kode_trans' => $v_det['kode_trans'],
    //                                     'dari' => $v_det['jenis_trans']
    //                                 );
    //                             } else {
    //                                 $data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][ $v_det['tgl_trans'] ][] = array(
    //                                     'tgl_trans' => $v_det['tgl_trans'],
    //                                     'kode_gudang' => $v_det['kode_gudang'],
    //                                     'kode_barang' => $v_det['kode_barang'],
    //                                     'jumlah' => $v_det['jumlah'],
    //                                     'hrg_jual' => $v_det['hrg_jual'],
    //                                     'hrg_beli' => $v_det['hrg_beli'],
    //                                     'kode_trans' => $v_det['kode_trans'],
    //                                     'dari' => $v_det['jenis_trans']
    //                                 );
    //                             }

    //                             // ksort($data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]);
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     // }

    //     // RETUR
    //     if ( $d_rv->count() > 0 ) {
    //         $d_rv = $d_rv->toArray();
    //         foreach ($d_rv as $k_rv => $v_rv) {
    //             $m_drv = new \Model\Storage\DetReturVoadip_model();

    //             $d_drv = null;
    //             if ( $kode_barang != 'all' ) {
    //                 $d_drv = $m_drv->where('id_header', $v_rv['id'])->where('item', $kode_barang)->get();
    //             } else {
    //                 $d_drv = $m_drv->where('id_header', $v_rv['id'])->get();
    //             }

    //             if ( $d_drv->count() > 0 ) {
    //                 $d_drv = $d_drv->toArray();
    //                 foreach ($d_drv as $k_det => $v_det) {
    //                     $m_kv = new \Model\Storage\KirimVoadip_model();
    //                     $_d_kv = $m_kv->where('no_order', $v_rv['no_order'])->orderBy('id', 'desc')->first();

    //                     $_d_kvd = null;
    //                     if ( !empty($_d_kv) ) {
    //                         $m_kvd = new \Model\Storage\KirimVoadipDetail_model();
    //                         $_d_kvd = $m_kvd->where('id_header', $_d_kv->id)->where('item', $v_det['item'])->orderBy('id', 'desc')->first();
    //                     }

    //                     $harga_beli = (!empty($_d_kvd) && $_d_kvd->nilai_beli > 0 && $_d_kvd->jumlah > 0) ? $_d_kvd->nilai_beli/$_d_kvd->jumlah : 0;
    //                     $harga_jual = (!empty($_d_kvd) && $_d_kvd->nilai_jual > 0 && $_d_kvd->jumlah > 0) ? $_d_kvd->nilai_jual/$_d_kvd->jumlah : 0;

    //                     $isi = 1;
    //                     if ( isset($data[ $v_rv['id_tujuan'] ][ $v_det['item'] ]) ) {
    //                         foreach ($data[ $v_rv['id_tujuan'] ][ $v_det['item'] ] as $k => $val) {
    //                             if ( $val['kode_trans'] == $v_rv['id'] ) {
    //                                 $isi = 0;
    //                             }
    //                         }
    //                     }

    //                     if ( $isi == 1 ) {
    //                         if ( empty($stok) ) {
    //                             $data_retur[ $v_rv['id_tujuan'] ][ $v_det['item'] ][] = array(
    //                                 'tgl_trans' => $v_rv['tgl_retur'],
    //                                 'kode_gudang' => $v_rv['id_tujuan'],
    //                                 'kode_barang' => $v_det['item'],
    //                                 'jumlah' => $v_det['jumlah'],
    //                                 'hrg_jual' => $harga_jual,
    //                                 'hrg_beli' => $harga_beli,
    //                                 'kode_trans' => $v_rv['id'],
    //                                 'dari' => 'RETUR'
    //                             );
    //                         } else {
    //                             $data_retur[ $v_rv['id_tujuan'] ][ $v_det['item'] ][ $v_rv['tgl_retur'] ][] = array(
    //                                 'tgl_trans' => $v_rv['tgl_retur'],
    //                                 'kode_gudang' => $v_rv['id_tujuan'],
    //                                 'kode_barang' => $v_det['item'],
    //                                 'jumlah' => $v_det['jumlah'],
    //                                 'hrg_jual' => $harga_jual,
    //                                 'hrg_beli' => $harga_beli,
    //                                 'kode_trans' => $v_rv['id'],
    //                                 'dari' => 'RETUR'
    //                             );
    //                         }

    //                         // ksort($data_retur[ $v_rv['id_tujuan'] ][ $v_det['item'] ]);
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     // ORDER
    //     if ( $d_kv->count() > 0 ) {
    //         $d_kv = $d_kv->toArray();
    //         foreach ($d_kv as $k_kv => $v_kv) {
    //             $m_tv = new \Model\Storage\TerimaVoadip_model();
    //             $d_tv = $m_tv->where('id_kirim_voadip', $v_kv['id'])->orderBy('id', 'desc')->first();
                
    //             $m_kvd = new \Model\Storage\KirimVoadipDetail_model();

    //             $d_kvd = null;
    //             if ( $kode_barang != 'all' ) {
    //                 $d_kvd = $m_kvd->where('id_header', $v_kv['id'])->where('item', $kode_barang)->get();
    //             } else {
    //                 $d_kvd = $m_kvd->where('id_header', $v_kv['id'])->get();
    //             }

    //             if ( $d_kvd->count() > 0 ) {
    //                 $d_kvd = $d_kvd->toArray();
    //                 foreach ($d_kvd as $k_det => $v_det) {
    //                     $jumlah_terima = 0;
    //                     if ( $d_tv ) {
    //                         $m_dtv = new \Model\Storage\TerimaVoadipDetail_model();
    //                         $jumlah_terima = $m_dtv->where('id_header', $d_tv->id)->where('item', $v_det['item'])->sum('jumlah');
    //                     }

    //                     $hrg_beli = ($v_det['nilai_beli'] > 0 && $v_det['jumlah'] > 0) ? $v_det['nilai_beli'] / $v_det['jumlah'] : 0;
    //                     $hrg_jual = ($v_det['nilai_jual'] > 0 && $v_det['jumlah'] > 0) ? $v_det['nilai_jual'] / $v_det['jumlah'] : 0;

    //                     $m_ov = new \Model\Storage\OrderVoadip_model();
    //                     $d_ov = $m_ov->where('no_order', $v_kv['no_order'])->orderBy('id', 'desc')->first();

    //                     $d_ovd = null;
    //                     if ( !empty($d_ov) ) {
    //                         $m_ovd = new \Model\Storage\OrderVoadipDetail_model();
    //                         $d_ovd = $m_ovd->where('id_order', $d_ov->id)->where('kode_barang', $v_det['item'])->orderBy('id', 'desc')->first();

    //                         $hrg_beli = !empty($d_ovd) ? $d_ovd->harga : 0;
    //                         $hrg_jual = !empty($d_ovd) ? $d_ovd->harga_jual : 0;
    //                     }

    //                     $isi = 1;
    //                     if ( isset($data[ $v_kv['tujuan'] ][ $v_det['item'] ]) ) {
    //                         foreach ($data[ $v_kv['tujuan'] ][ $v_det['item'] ] as $k => $val) {
    //                             if ( $val['kode_trans'] == $v_kv['no_order'] ) {
    //                                 $isi = 0;
    //                             }
    //                         }
    //                     }

    //                     if ( $isi == 1 ) {
    //                         if ( empty($stok) ) {
    //                             $data_beli[ $v_kv['tujuan'] ][ $v_det['item'] ][] = array(
    //                                 'tgl_trans' => $v_kv['tgl_kirim'],
    //                                 'kode_gudang' => $v_kv['tujuan'],
    //                                 'kode_barang' => $v_det['item'],
    //                                 'jumlah' => $jumlah_terima,
    //                                 'hrg_jual' => $hrg_jual,
    //                                 'hrg_beli' => $hrg_beli,
    //                                 'kode_trans' => $v_kv['no_order'],
    //                                 'dari' => 'ORDER'
    //                             );
    //                         } else {
    //                             $data_beli[ $v_kv['tujuan'] ][ $v_det['item'] ][ $v_kv['tgl_kirim'] ][] = array(
    //                                 'tgl_trans' => $v_kv['tgl_kirim'],
    //                                 'kode_gudang' => $v_kv['tujuan'],
    //                                 'kode_barang' => $v_det['item'],
    //                                 'jumlah' => $jumlah_terima,
    //                                 'hrg_jual' => $hrg_jual,
    //                                 'hrg_beli' => $hrg_beli,
    //                                 'kode_trans' => $v_kv['no_order'],
    //                                 'dari' => 'ORDER'
    //                             );
    //                         }

    //                         // ksort($data_beli[ $v_kv['tujuan'] ][ $v_det['item'] ]);
    //                     }

    //                     // if ( $v_kv['no_order'] == 'OP/LMJ/22/03047' ) {
    //                     //     cetak_r( $data_beli, 1 );
    //                     // }
    //                 }
    //             }
    //         }
    //     }

    //     $data = array(
    //         'retur' => $data_retur,
    //         'beli' => $data_beli
    //     );

    //     return $data;
    // }

    // public function get_data_barang_keluar_voadip($periode, $next_periode, $stok, $kode_gudang, $kode_barang)
    // {
    //     $m_stok = new \Model\Storage\Stok_model();

    //     $_d_stok = $m_stok->where('periode', '<', $periode)->with(['det_stok'])->orderBy('periode', 'desc')->first();
    //     $d_stok = null;
    //     // $d_stok = $m_stok->where('periode', $periode)->with(['det_stok'])->first();
    //     // if ( !$d_stok ) {
    //         $d_stok = $_d_stok;
    //     // }

    //     $tgl_terakhir = date("Y-m-t", strtotime($next_periode));

    //     $data = null;
    //     $d_rv = null;
    //     $d_kv = null;
    //     if ( !$_d_stok ) {
    //         if ( $kode_gudang != 'all' ) {
    //             $m_rv = new \Model\Storage\ReturVoadip_model();
    //             $d_rv = $m_rv->where('asal', 'gudang')->where('id_asal', $kode_gudang)->where('tujuan', 'supplier')->where('tgl_retur', '<', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //             $m_kv = new \Model\Storage\KirimVoadip_model();
    //             $d_kv = $m_kv->where('jenis_kirim', 'opkg')->where('asal', $kode_gudang)->where('tgl_kirim', '<', $tgl_terakhir)->with(['detail'])->orderBy('tgl_kirim', 'asc')->get();
    //         } else {
    //             $m_rv = new \Model\Storage\ReturVoadip_model();
    //             $d_rv = $m_rv->where('asal', 'gudang')->where('tujuan', 'supplier')->where('tgl_retur', '<', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //             $m_kv = new \Model\Storage\KirimVoadip_model();
    //             $d_kv = $m_kv->where('jenis_kirim', 'opkg')->where('tgl_kirim', '<', $tgl_terakhir)->with(['detail'])->orderBy('tgl_kirim', 'asc')->get();
    //         }
    //     } else {
    //         if ( $d_stok ) {
    //             $final = $periode;

    //             if ( $kode_gudang != 'all' ) {
    //                 $m_rv = new \Model\Storage\ReturVoadip_model();
    //                 $d_rv = $m_rv->where('asal', 'gudang')->where('id_asal', $kode_gudang)->where('tujuan', 'supplier')->where('tgl_retur', '>=', $final)->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //                 $m_kv = new \Model\Storage\KirimVoadip_model();
    //                 $d_kv = $m_kv->where('jenis_kirim', 'opkg')->where('asal', $kode_gudang)->where('tgl_kirim', '>=', $final)->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //             } else {
    //                 $m_rv = new \Model\Storage\ReturVoadip_model();
    //                 $d_rv = $m_rv->where('asal', 'gudang')->where('tujuan', 'supplier')->where('tgl_retur', '>=', $final)->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //                 $m_kv = new \Model\Storage\KirimVoadip_model();
    //                 $d_kv = $m_kv->where('jenis_kirim', 'opkg')->where('tgl_kirim', '>=', $final)->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //             }
    //         } else {
    //             if ( $kode_gudang != 'all' ) {
    //                 $m_rv = new \Model\Storage\ReturVoadip_model();
    //                 $d_rv = $m_rv->where('asal', 'gudang')->where('id_asal', $kode_gudang)->where('tujuan', 'supplier')->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //                 $m_kv = new \Model\Storage\KirimVoadip_model();
    //                 $d_kv = $m_kv->where('jenis_kirim', 'opkg')->where('asal', $kode_gudang)->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //             } else {
    //                 $m_rv = new \Model\Storage\ReturVoadip_model();
    //                 $d_rv = $m_rv->where('asal', 'gudang')->where('tujuan', 'supplier')->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //                 $m_kv = new \Model\Storage\KirimVoadip_model();
    //                 $d_kv = $m_kv->where('jenis_kirim', 'opkg')->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //             }
    //         }
    //     }

    //     // RETUR
    //     if ( $d_rv->count() > 0 ) {
    //         $d_rv = $d_rv->toArray();
    //         foreach ($d_rv as $k_rv => $v_rv) {
    //             $m_drv = new \Model\Storage\DetReturVoadip_model();
    //             $d_drv = null;
    //             if ( $kode_barang != 'all' ) {
    //                 $d_drv = $m_drv->where('id_header', $v_rv['id'])->where('item', $kode_barang)->get();
    //             } else {
    //                 $d_drv = $m_drv->where('id_header', $v_rv['id'])->get();
    //             }

    //             if ( $d_drv->count() > 0 ) {
    //                 $d_drv = $d_drv->toArray();
    //                 foreach ($d_drv as $k_det => $v_det) {
    //                     if ( empty($stok) ) {
    //                         $data[ $v_rv['id_asal'] ][ $v_det['item'] ][] = array(
    //                             'key' => $v_det['id'],
    //                             'tgl_trans' => $v_rv['tgl_retur'],
    //                             'kode_gudang' => $v_rv['id_asal'],
    //                             'kode_barang' => $v_det['item'],
    //                             'jumlah' => $v_det['jumlah'],
    //                             'kode_trans' => $v_rv['no_order'],
    //                             'dari' => 'RETUR'
    //                         );
    //                     } else {
    //                         $data[ $v_rv['id_asal'] ][ $v_det['item'] ][ $v_rv['tgl_retur'] ][] = array(
    //                             'key' => $v_det['id'],
    //                             'tgl_trans' => $v_rv['tgl_retur'],
    //                             'kode_gudang' => $v_rv['id_asal'],
    //                             'kode_barang' => $v_det['item'],
    //                             'jumlah' => $v_det['jumlah'],
    //                             'hrg_beli' => ($v_det['nilai_beli'] > 0 && $v_det['jumlah'] > 0) ? $v_det['nilai_beli']/$v_det['jumlah'] : 0,
    //                             'hrg_jual' => ($v_det['nilai_jual'] > 0 && $v_det['jumlah'] > 0) ? $v_det['nilai_jual']/$v_det['jumlah'] : 0,
    //                             'kode_trans' => $v_rv['no_order'],
    //                             'dari' => 'RETUR'
    //                         );
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     // ORDER
    //     if ( $d_kv->count() > 0 ) {
    //         $d_kv = $d_kv->toArray();
    //         foreach ($d_kv as $k_kv => $v_kv) {
    //             $m_tv = new \Model\Storage\TerimaVoadip_model();
    //             $d_tv = $m_tv->where('id_kirim_voadip', $v_kv['id'])->orderBy('id', 'desc')->first();

    //             $m_kvd = new \Model\Storage\KirimVoadipDetail_model();
    //             $d_kvd = null;
    //             if ( $kode_barang != 'all' ) {
    //                 $d_kvd = $m_kvd->where('id_header', $v_kv['id'])->where('item', $kode_barang)->get();
    //             } else {
    //                 $d_kvd = $m_kvd->where('id_header', $v_kv['id'])->get();
    //             }

    //             if ( $d_kvd->count() > 0 ) {
    //                 $d_kvd = $d_kvd->toArray();
    //                 foreach ($d_kvd as $k_det => $v_det) {
    //                     $jumlah_terima = 0;
    //                     if ( $d_tv ) {
    //                         $m_dtv = new \Model\Storage\TerimaVoadipDetail_model();
    //                         $jumlah_terima = $m_dtv->where('id_header', $d_tv->id)->where('item', $v_det['item'])->sum('jumlah');
    //                     }

    //                     if ( empty($stok) ) {
    //                         $data[ $v_kv['asal'] ][ $v_det['item'] ][] = array(
    //                             'key' => $v_det['id'],
    //                             'tgl_trans' => $v_kv['tgl_kirim'],
    //                             'kode_gudang' => $v_kv['asal'],
    //                             'kode_barang' => $v_det['item'],
    //                             'jumlah' => $jumlah_terima,
    //                             'kode_trans' => $v_kv['no_order'],
    //                             'dari' => 'ORDER'
    //                         );
    //                     } else {
    //                         $data[ $v_kv['asal'] ][ $v_det['item'] ][ $v_kv['tgl_kirim'] ][] = array(
    //                             'key' => $v_det['id'],
    //                             'tgl_trans' => $v_kv['tgl_kirim'],
    //                             'kode_gudang' => $v_kv['asal'],
    //                             'kode_barang' => $v_det['item'],
    //                             'jumlah' => $jumlah_terima,
    //                             'hrg_beli' => ($v_det['nilai_beli'] > 0 && $v_det['jumlah'] > 0) ? $v_det['nilai_beli']/$v_det['jumlah'] : 0,
    //                             'hrg_jual' => ($v_det['nilai_jual'] > 0 && $v_det['jumlah'] > 0) ? $v_det['nilai_jual']/$v_det['jumlah'] : 0,
    //                             'kode_trans' => $v_kv['no_order'],
    //                             'dari' => 'ORDER'
    //                         );
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     return $data;
    // }

    // public function hitung_stok_awal_voadip($periode, $next_periode, $stok, $kode_gudang, $kode_barang)
    // {
    //     $_data_masuk = $this->get_data_barang_masuk_voadip( $periode, $next_periode, $stok, $kode_gudang, $kode_barang );
    //     $_data_keluar = $this->get_data_barang_keluar_voadip( $periode, $next_periode, $stok, $kode_gudang, $kode_barang );

    //     // cetak_r('MASUK -----------------');
    //     // cetak_r( $_data_masuk );
    //     // cetak_r('KELUAR -----------------');
    //     // cetak_r( $_data_keluar, 1 );
    //     // cetak_r( $periode.'|'.$next_periode, 1 );

    //     $m_gdg = new \Model\Storage\Gudang_model();
    //     $d_gdg = null;
    //     if ( $kode_gudang != 'all' ) {
    //         $d_gdg = $m_gdg->where('jenis', 'OBAT')->where('id', $kode_gudang)->get();
    //     } else {
    //         $d_gdg = $m_gdg->where('jenis', 'OBAT')->get();
    //     }

    //     $data = null;
    //     if ( $d_gdg->count() > 0 ) {
    //         $d_gdg = $d_gdg->toArray();
    //         foreach ($d_gdg as $k_gdg => $v_gdg) {
    //             $m_barang = new \Model\Storage\Barang_model();
    //             $d_barang = null;
    //             $data_barang = null;
    //             if ( $kode_barang != 'all' ) {
    //                 $d_barang = $m_barang->select('kode')->distinct('kode')->where('tipe', 'obat')->where('kode', $kode_barang)->get();
    //                 $data_barang = null;
    //                 if ( $d_barang->count() > 0 ) {
    //                     $d_barang = $d_barang->toArray();
    //                     foreach ($d_barang as $k_brg => $v_brg) {
    //                         $m_barang = new \Model\Storage\Barang_model();
    //                         $_d_barang = $m_barang->where('kode', $v_brg)->where('tipe', 'obat')->orderBy('version', 'desc')->first();
    //                         if ( !empty($_d_barang) ) {
    //                             $key = $_d_barang->nama.' | '.$_d_barang->kode;
    //                             $data_barang[$key] = $_d_barang->toArray();

    //                             ksort($data_barang);
    //                         }
    //                     }
    //                 }
    //             } else {
    //                 $d_barang = $m_barang->select('kode')->distinct('kode')->where('tipe', 'obat')->get();
    //                 $data_barang = null;
    //                 if ( $d_barang->count() > 0 ) {
    //                     $d_barang = $d_barang->toArray();
    //                     foreach ($d_barang as $k_brg => $v_brg) {
    //                         $m_barang = new \Model\Storage\Barang_model();
    //                         $_d_barang = $m_barang->where('kode', $v_brg)->where('tipe', 'obat')->orderBy('version', 'desc')->first();
    //                         if ( !empty($_d_barang) ) {
    //                             $key = $_d_barang->nama.' | '.$_d_barang->kode;
    //                             $data_barang[$key] = $_d_barang->toArray();

    //                             ksort($data_barang);
    //                         }
    //                     }
    //                 }
    //             }
    //             if ( count($data_barang) > 0 ) {
    //                 // $d_barang = $d_barang->toArray();
    //                 foreach ($data_barang as $k_brg => $v_brg) {
    //                     $data_keluar = isset($_data_keluar[ $v_gdg['id'] ][ $v_brg['kode'] ]) ? $_data_keluar[ $v_gdg['id'] ][ $v_brg['kode'] ] : null;

    //                     if ( !empty($data_keluar) ) {
    //                         $jml_masuk = 0;
    //                         $jml_keluar = 0;
    //                         foreach ($data_keluar as $k_dk => $v_dk) {
    //                             $total_nilai_keluar_beli = 0;
    //                             $total_nilai_keluar_jual = 0;
    //                             $jml_keluar = $v_dk['jumlah'];
    //                             $tgl_keluar = $v_dk['tgl_trans'];
    //                             if ( !empty($_data_masuk) ) {
    //                                 if ( !empty($_data_masuk['retur']) ) {
    //                                     $data_masuk_retur = isset($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ]) ? $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ] : null;
    //                                     $idx_masuk_retur = 0;

    //                                     $_jml_keluar = $jml_keluar;
    //                                     while ($_jml_keluar > 0) {
    //                                         if ( isset($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ]) && $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['tgl_trans'] <= $tgl_keluar ) {

    //                                             $hrg_beli = $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['hrg_beli'];
    //                                             $hrg_jual = $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['hrg_jual'];

    //                                             if ( $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] >= $jml_keluar ) {
    //                                                 $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] = $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] - $jml_keluar;

    //                                                 $total_nilai_keluar_beli += $jml_keluar * $hrg_beli;
    //                                                 $total_nilai_keluar_jual += $jml_keluar * $hrg_jual;

    //                                                 $jml_keluar = 0;
    //                                                 $_jml_keluar = $jml_keluar;
    //                                             } else {
    //                                                 $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] = 0;
    //                                                 $jml_keluar = $jml_keluar - $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'];
    //                                                 $_jml_keluar = $jml_keluar;

    //                                                 $total_nilai_keluar_beli += $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] * $hrg_beli;
    //                                                 $total_nilai_keluar_jual += $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] * $hrg_jual;

    //                                                 $idx_masuk_retur++;
    //                                             }
    //                                         } else {
    //                                             $_jml_keluar = 0;
    //                                         }
    //                                     }
    //                                 }
    //                                 if ( !empty($_data_masuk['beli']) ) {
    //                                     $data_masuk_beli = isset($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ]) ? $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ] : null;
    //                                     $idx_masuk_beli = 0;

    //                                     $_jml_keluar = $jml_keluar;
    //                                     while ($_jml_keluar > 0) {
    //                                         if ( isset($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ]) && $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['tgl_trans'] <= $tgl_keluar ) {

    //                                             $hrg_beli = $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['hrg_beli'];
    //                                             $hrg_jual = $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['hrg_jual'];

    //                                             if ( $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] >= $jml_keluar ) {
    //                                                 $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] = $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] - $jml_keluar;

    //                                                 $total_nilai_keluar_beli += $jml_keluar * $hrg_beli;
    //                                                 $total_nilai_keluar_jual += $jml_keluar * $hrg_jual;

    //                                                 $jml_keluar = 0;
    //                                                 $_jml_keluar = $jml_keluar;
    //                                             } else {
    //                                                 $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] = 0;
    //                                                 $jml_keluar = $jml_keluar - $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'];
    //                                                 $_jml_keluar = $jml_keluar;

    //                                                 $total_nilai_keluar_beli += $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] * $hrg_beli;
    //                                                 $total_nilai_keluar_jual += $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] * $hrg_jual;

    //                                                 $idx_masuk_beli++;
    //                                             }
    //                                         } else {
    //                                             $_jml_keluar = 0;
    //                                         }
    //                                     }
    //                                 }
    //                             }
    //                         }
    //                     }

    //                     if ( !empty($_data_masuk) ) {
    //                         if ( isset($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ]) ) {
    //                             foreach ($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ] as $k_dm => $val) {
    //                                 if ( $val['jumlah'] > 0 ) {
    //                                     $data[ $v_gdg['id'] ][ $v_brg['kode'] ][ $val['kode_trans'] ] = array(
    //                                         'tgl_trans' => $val['tgl_trans'],
    //                                         'kode_gudang' => $val['kode_gudang'],
    //                                         'kode_barang' => $val['kode_barang'],
    //                                         'jumlah' => $val['jumlah'],
    //                                         'hrg_jual' => $val['hrg_jual'],
    //                                         'hrg_beli' => $val['hrg_beli'],
    //                                         'kode_trans' => $val['kode_trans'],
    //                                         'dari' => $val['dari'],
    //                                     );

    //                                     ksort( $data );
    //                                 }
    //                             }
    //                         }
    //                         if ( isset($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ]) ) {
    //                             foreach ($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ] as $k_dm => $val) {
    //                                 if ( $val['jumlah'] > 0 ) {
    //                                     $data[ $v_gdg['id'] ][ $v_brg['kode'] ][ $val['kode_trans'] ] = array(
    //                                         'tgl_trans' => $val['tgl_trans'],
    //                                         'kode_gudang' => $val['kode_gudang'],
    //                                         'kode_barang' => $val['kode_barang'],
    //                                         'jumlah' => $val['jumlah'],
    //                                         'hrg_jual' => $val['hrg_jual'],
    //                                         'hrg_beli' => $val['hrg_beli'],
    //                                         'kode_trans' => $val['kode_trans'],
    //                                         'dari' => $val['dari'],
    //                                     );

    //                                     ksort( $data );
    //                                 }
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     // cetak_r( $data, 1 );

    //     return $data;
    // }

    // public function get_data_voadip($start_date, $end_date, $jenis, $kode_gudang, $kode_brg)
    // {
    //     // $start_date = '2021-03-25';
    //     // $end_date = '2021-04-05';
    //     // $jenis = 'obat';
    //     // $kode_gudang = 'all';
    //     // $kode_brg = 'all';

    //     $exp_per_start = explode("-", $start_date);
    //     $year_start = $exp_per_start[0];
    //     $month_start =  $exp_per_start[1];
    //     $day_start =  $exp_per_start[2];

    //     $exp_per_end = explode("-", $end_date);
    //     $year_end = $exp_per_end[0];
    //     $month_end =  $exp_per_end[1];
    //     $day_end =  $exp_per_end[2];

    //     $start_date_stok = null;
    //     $end_date_stok = null;

    //     $stok_awal = null;
    //     $_start_date = substr($start_date, 0, 7).'-01';

    //     $m_stok = new \Model\Storage\Stok_model();
    //     $d_stok = $m_stok->where('periode', '<=', $_start_date)->with(['det_stok'])->orderBy('periode', 'desc')->first();
    //     if ( $d_stok ) {
    //         $start_date_stok = $d_stok->periode;
    //         $end_date_stok = prev_date($start_date);
    //     } else {
    //         $start_date_stok = substr($start_date, 0, 7).'-01';
    //         $end_date_stok = prev_date($start_date);
    //     }

    //     $_stok_awal = $this->hitung_stok_awal_voadip( $start_date_stok, $end_date_stok, null, $kode_gudang, $kode_brg );
    //     $_data_masuk = $this->get_data_barang_masuk_voadip( $start_date, $end_date, 'non stok', $kode_gudang, $kode_brg );
    //     $_data_keluar = $this->get_data_barang_keluar_voadip( $start_date, $end_date, 'non stok', $kode_gudang, $kode_brg );

    //     // cetak_r('STOK --------------------------------');
    //     // cetak_r($_stok_awal, 1);
    //     // cetak_r('MASUK --------------------------------');
    //     // cetak_r($_data_masuk);
    //     // cetak_r('KELUAR --------------------------------');
    //     // cetak_r($_data_keluar);

    //     $m_gdg = new \Model\Storage\Gudang_model();
    //     if ( $kode_gudang != 'all' ) {
    //         $d_gdg = $m_gdg->where('id', $kode_gudang)->where('jenis', 'like', '%'.$jenis.'%')->get();
    //     } else {
    //         $d_gdg = $m_gdg->where('jenis', 'like', '%'.$jenis.'%')->get();
    //     }

    //     $data = null;
    //     if ( $d_gdg->count() > 0 ) {
    //         $d_gdg = $d_gdg->toArray();
    //         foreach ($d_gdg as $k_gdg => $v_gdg) {
    //             $data[ $v_gdg['id'] ]['gudang'] = $v_gdg['nama'];

    //             $m_barang = new \Model\Storage\Barang_model();
    //             if ( $kode_brg != 'all' ) {
    //                 $d_barang = $m_barang->distinct('kode')->select('kode')->where('kode', $kode_brg)->where('tipe', 'like', '%'.$jenis.'%')->orderBy('kode', 'asc')->get();
    //             } else {
    //                 $d_barang = $m_barang->distinct('kode')->select('kode')->where('tipe', 'like', '%'.$jenis.'%')->orderBy('kode', 'asc')->get();
    //             }
    //             if ( $d_barang->count() > 0 ) {
    //                 $d_barang = $d_barang->toArray();
    //                 foreach ($d_barang as $k_brg => $val_brg) {
    //                     $v_brg = $m_barang->where('kode', $val_brg['kode'])->orderBy('version', 'desc')->first()->toArray();
    //                     $stok_awal = isset($_stok_awal[ $v_gdg['id'] ][ $v_brg['kode'] ]) ? $_stok_awal[ $v_gdg['id'] ][ $v_brg['kode'] ] : null;

    //                     if ( !empty($stok_awal) ) {
    //                         $jumlah = 0;
    //                         $total_beli = 0;
    //                         $total_jual = 0;
    //                         foreach ($stok_awal as $k_sa => $v_sa) {
    //                             $jumlah += $v_sa['jumlah'];
    //                             $total_beli += $v_sa['jumlah']*$v_sa['hrg_beli'];
    //                             $total_jual += $v_sa['jumlah']*$v_sa['hrg_jual'];
    //                         }

    //                         $harga_beli = $total_beli / $jumlah;
    //                         $harga_jual = $total_jual / $jumlah;

    //                         if ( $jumlah > 0 ) {
    //                             $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][] = array(
    //                                 'kode' => $v_brg['kode'],
    //                                 'nama' => $v_brg['nama'],
    //                                 'transaksi' => 'STOK AWAL',
    //                                 'tgl_trans' => null,
    //                                 'kode' => '-',
    //                                 'tujuan' => '-',
    //                                 'jumlah' => $jumlah,
    //                                 'hrg_beli' => $harga_beli,
    //                                 'hrg_jual' => $harga_jual,
    //                                 'total_beli' => $total_beli,
    //                                 'total_jual' => $total_jual,
    //                                 'saldo' => $jumlah,
    //                                 'nilai_beli_saldo' => $total_beli,
    //                                 'nilai_jual_saldo' => $total_jual,
    //                             );
    //                         }
    //                     } else {
    //                         $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][] = array(
    //                             'kode' => $v_brg['kode'],
    //                             'nama' => $v_brg['nama'],
    //                             'transaksi' => 'STOK AWAL',
    //                             'tgl_trans' => null,
    //                             'kode' => '-',
    //                             'tujuan' => '-',
    //                             'jumlah' => 0,
    //                             'hrg_beli' => 0,
    //                             'hrg_jual' => 0,
    //                             'total_beli' => 0,
    //                             'total_jual' => 0,
    //                             'saldo' => 0,
    //                             'nilai_beli_saldo' => 0,
    //                             'nilai_jual_saldo' => 0,
    //                         );
    //                     }

    //                     $date = $start_date;
    //                     while ( $date <= $end_date) {
    //                         $data_masuk_retur = isset($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][ $date ]) ? $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][ $date ] : null;
    //                         $data_masuk_beli = isset($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][ $date ]) ? $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][ $date ] : null;
    //                         $data_keluar = isset($_data_keluar[ $v_gdg['id'] ][ $v_brg['kode'] ][ $date ]) ? $_data_keluar[ $v_gdg['id'] ][ $v_brg['kode'] ][ $date ] : null;

    //                         if ( !empty($data_masuk_retur) ) {
    //                             foreach ($data_masuk_retur as $k_dm => $v_dm) {
    //                                 $idx_data_sebelum = count($data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ])-1;
    //                                 $saldo = $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][ $idx_data_sebelum ]['saldo'] + $v_dm['jumlah'];
    //                                 $nilai_beli_saldo = $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][ $idx_data_sebelum ]['nilai_beli_saldo'] + ($v_dm['hrg_beli'] * $v_dm['jumlah']);
    //                                 $nilai_jual_saldo = $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][ $idx_data_sebelum ]['nilai_jual_saldo'] + ($v_dm['hrg_jual'] * $v_dm['jumlah']);

    //                                 $jumlah = $v_dm['jumlah'];
    //                                 $total_beli = ($v_dm['hrg_beli'] * $v_dm['jumlah']);
    //                                 $total_jual = ($v_dm['hrg_jual'] * $v_dm['jumlah']);
    //                                 $harga_beli = ($total_beli > 0 && $jumlah > 0) ? $total_beli / $jumlah : 0;
    //                                 $harga_jual = ($total_jual > 0 && $jumlah > 0) ? $total_jual / $jumlah : 0;

    //                                 $kode = null;
    //                                 $tujuan = null;
    //                                 if ( is_numeric($v_dm['kode_trans']) ) {
    //                                     $m_rv = new \Model\Storage\ReturVoadip_model();
    //                                     $d_rv = $m_rv->where('id', $v_dm['kode_trans'])->first();

    //                                     if ( $d_rv ) {
    //                                         $kode = $d_rv->no_retur;
    //                                         if ( $d_rv->tujuan == 'gudang' ) {
    //                                             $m_gdg = new \Model\Storage\Gudang_model();
    //                                             $d_gdg = $m_gdg->where('id', $d_rv->id_tujuan)->first();

    //                                             $tujuan = strtoupper($d_gdg->nama);
    //                                         } else {
    //                                             $m_rs = new \Model\Storage\RdimSubmit_model();
    //                                             $d_rs = $m_rs->where('noreg', $d_rv->id_tujuan)->with(['dMitraMapping'])->first();

    //                                             $tujuan = strtoupper($d_rs->dMitraMapping->dMitra->nama);
    //                                         }
    //                                     }
    //                                 } else {
    //                                     $kode = $v_dm['kode_trans'];

    //                                     $m_kv = new \Model\Storage\KirimVoadip_model();
    //                                     $d_kv = $m_kv->where('no_order', $v_dm['kode_trans'])->first();

    //                                     if ( $d_kv ) {
    //                                         if ( $d_kv->jenis_tujuan == 'gudang' ) {
    //                                             $m_gdg = new \Model\Storage\Gudang_model();
    //                                             $d_gdg = $m_gdg->where('id', $d_kv->tujuan)->first();

    //                                             $tujuan = strtoupper($d_gdg->nama);
    //                                         } else {
    //                                             $m_rs = new \Model\Storage\RdimSubmit_model();
    //                                             $d_rs = $m_rs->where('noreg', $d_kv->tujuan)->with(['dMitraMapping'])->first();

    //                                             $tujuan = strtoupper($d_rs->dMitraMapping->dMitra->nama);
    //                                         }
    //                                     }
    //                                 }

    //                                 if ( $jumlah > 0 ) {
    //                                     $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][] = array(
    //                                         'kode' => $v_brg['kode'],
    //                                         'nama' => $v_brg['nama'],
    //                                         'transaksi' => 'MASUK',
    //                                         'tgl_trans' => $v_dm['tgl_trans'],
    //                                         'kode' => $kode,
    //                                         'tujuan' => $tujuan,
    //                                         'jumlah' => $jumlah,
    //                                         'hrg_beli' => $harga_beli,
    //                                         'hrg_jual' => $harga_jual,
    //                                         'total_beli' => $total_beli,
    //                                         'total_jual' => $total_jual,
    //                                         'saldo' => $saldo,
    //                                         'nilai_beli_saldo' => $nilai_beli_saldo,
    //                                         'nilai_jual_saldo' => $nilai_jual_saldo,
    //                                     );
    //                                 }
    //                             }
    //                         }

    //                         if ( !empty($data_masuk_beli) ) {
    //                             foreach ($data_masuk_beli as $k_dm => $v_dm) {
    //                                 $idx_data_sebelum = count($data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ])-1;
    //                                 $saldo = $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][ $idx_data_sebelum ]['saldo'] + $v_dm['jumlah'];
    //                                 $nilai_beli_saldo = $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][ $idx_data_sebelum ]['nilai_beli_saldo'] + ($v_dm['hrg_beli'] * $v_dm['jumlah']);
    //                                 $nilai_jual_saldo = $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][ $idx_data_sebelum ]['nilai_jual_saldo'] + ($v_dm['hrg_jual'] * $v_dm['jumlah']);

    //                                 $jumlah = $v_dm['jumlah'];
    //                                 $total_beli = ($v_dm['hrg_beli'] * $v_dm['jumlah']);
    //                                 $total_jual = ($v_dm['hrg_jual'] * $v_dm['jumlah']);
    //                                 $harga_beli = ($total_beli > 0 && $jumlah > 0) ? $total_beli / $jumlah : 0;
    //                                 $harga_jual = ($total_jual > 0 && $jumlah > 0) ? $total_jual / $jumlah : 0;

    //                                 $kode = null;
    //                                 $tujuan = null;
    //                                 if ( is_numeric($v_dm['kode_trans']) ) {
    //                                     $m_rv = new \Model\Storage\ReturVoadip_model();
    //                                     $d_rv = $m_rv->where('id', $v_dm['kode_trans'])->first();

    //                                     if ( $d_rv ) {
    //                                         $kode = $d_rv->no_retur;
    //                                         if ( $d_rv->tujuan == 'gudang' ) {
    //                                             $m_gdg = new \Model\Storage\Gudang_model();
    //                                             $d_gdg = $m_gdg->where('id', $d_rv->id_tujuan)->first();

    //                                             $tujuan = strtoupper($d_gdg->nama);
    //                                         } else {
    //                                             $m_rs = new \Model\Storage\RdimSubmit_model();
    //                                             $d_rs = $m_rs->where('noreg', $d_rv->id_tujuan)->with(['dMitraMapping'])->first();

    //                                             $tujuan = strtoupper($d_rs->dMitraMapping->dMitra->nama);
    //                                         }
    //                                     }
    //                                 } else {
    //                                     $kode = $v_dm['kode_trans'];

    //                                     $m_kv = new \Model\Storage\KirimVoadip_model();
    //                                     $d_kv = $m_kv->where('no_order', $v_dm['kode_trans'])->first();

    //                                     if ( $d_kv ) {
    //                                         if ( $d_kv->jenis_tujuan == 'gudang' ) {
    //                                             $m_gdg = new \Model\Storage\Gudang_model();
    //                                             $d_gdg = $m_gdg->where('id', $d_kv->tujuan)->first();

    //                                             $tujuan = strtoupper($d_gdg->nama);
    //                                         } else {
    //                                             $m_rs = new \Model\Storage\RdimSubmit_model();
    //                                             $d_rs = $m_rs->where('noreg', $d_kv->tujuan)->with(['dMitraMapping'])->first();

    //                                             $tujuan = strtoupper($d_rs->dMitraMapping->dMitra->nama);
    //                                         }
    //                                     }
    //                                 }

    //                                 if ( $jumlah > 0 ) {
    //                                     $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][] = array(
    //                                         'kode' => $v_brg['kode'],
    //                                         'nama' => $v_brg['nama'],
    //                                         'transaksi' => 'MASUK',
    //                                         'tgl_trans' => $v_dm['tgl_trans'],
    //                                         'kode' => $kode,
    //                                         'tujuan' => $tujuan,
    //                                         'jumlah' => $jumlah,
    //                                         'hrg_beli' => $harga_beli,
    //                                         'hrg_jual' => $harga_jual,
    //                                         'total_beli' => $total_beli,
    //                                         'total_jual' => $total_jual,
    //                                         'saldo' => $saldo,
    //                                         'nilai_beli_saldo' => $nilai_beli_saldo,
    //                                         'nilai_jual_saldo' => $nilai_jual_saldo,
    //                                     );
    //                                 }
    //                             }
    //                         }

    //                         if ( !empty($data_keluar) ) {
    //                             foreach ($data_keluar as $k_dk => $v_dk) {
    //                                 $idx_data_sebelum = count($data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ])-1;
    //                                 $saldo = $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][ $idx_data_sebelum ]['saldo'] - $v_dk['jumlah'];

    //                                 $nilai_beli_saldo = ($v_dk['hrg_beli'] > 0) ? $saldo * $v_dk['hrg_beli'] : $saldo * $v_dk['hrg_beli'];
    //                                 $nilai_jual_saldo = ($v_dk['hrg_jual'] > 0) ? $v_dk['jumlah'] * $v_dk['hrg_jual'] : $v_dk['jumlah'] * $v_dk['hrg_jual'];

    //                                 $jumlah = $v_dk['jumlah'];
    //                                 $total_beli = ($saldo < 0) ? (($v_dk['jumlah'] - abs($saldo)) * $v_dk['hrg_beli']) : ($v_dk['hrg_beli'] * $v_dk['jumlah']);
    //                                 $total_jual = ($v_dk['hrg_jual'] > 0) ? $v_dk['jumlah'] * $v_dk['hrg_jual'] : $v_dk['jumlah'] * $v_dk['hrg_jual'];
    //                                 $harga_beli = ($nilai_beli_saldo > 0 && $saldo > 0) ? $nilai_beli_saldo / $saldo : 0;
    //                                 $harga_jual = ($nilai_jual_saldo > 0 && $v_dk['jumlah'] > 0) ? $nilai_jual_saldo / $v_dk['jumlah'] : 0;
                                    
    //                                 $kode = null;
    //                                 $tujuan = null;
    //                                 if ( is_numeric($v_dk['kode_trans']) ) {
    //                                     $m_rv = new \Model\Storage\ReturVoadip_model();
    //                                     $d_rv = $m_rv->where('id', $v_dk['kode_trans'])->first();

    //                                     if ( $d_rv ) {
    //                                         $kode = $d_rv->no_retur;
    //                                         if ( $d_rv->tujuan == 'gudang' ) {
    //                                             $m_gdg = new \Model\Storage\Gudang_model();
    //                                             $d_gdg = $m_gdg->where('id', $d_rv->id_tujuan)->first();

    //                                             $tujuan = strtoupper($d_gdg->nama);
    //                                         } else {
    //                                             $m_rs = new \Model\Storage\RdimSubmit_model();
    //                                             $d_rs = $m_rs->where('noreg', $d_rv->id_tujuan)->with(['dMitraMapping'])->first();

    //                                             $tujuan = strtoupper($d_rs->dMitraMapping->dMitra->nama);
    //                                         }
    //                                     }
    //                                 } else {
    //                                     $kode = $v_dk['kode_trans'];

    //                                     $m_kv = new \Model\Storage\KirimVoadip_model();
    //                                     $d_kv = $m_kv->where('no_order', $v_dk['kode_trans'])->first();

    //                                     if ( $d_kv ) {
    //                                         if ( $d_kv->jenis_tujuan == 'gudang' ) {
    //                                             $m_gdg = new \Model\Storage\Gudang_model();
    //                                             $d_gdg = $m_gdg->where('id', $d_kv->tujuan)->first();

    //                                             $tujuan = strtoupper($d_gdg->nama);
    //                                         } else {
    //                                             $m_rs = new \Model\Storage\RdimSubmit_model();
    //                                             $d_rs = $m_rs->where('noreg', $d_kv->tujuan)->with(['mitra'])->first();

    //                                             if ( !$d_rs ) {
    //                                                 cetak_r( $v_dk['kode_trans'] );
    //                                             }

    //                                             $tujuan = strtoupper($d_rs->mitra->dMitra->nama);
    //                                         }
    //                                     }
    //                                 }

    //                                 if ( $jumlah > 0 ) {
    //                                     $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][] = array(
    //                                         'kode' => $v_brg['kode'],
    //                                         'nama' => $v_brg['nama'],
    //                                         'transaksi' => 'KELUAR',
    //                                         'tgl_trans' => $v_dk['tgl_trans'],
    //                                         'kode' => $kode,
    //                                         'tujuan' => $tujuan,
    //                                         'jumlah' => $jumlah,
    //                                         'hrg_beli' => $harga_beli,
    //                                         'hrg_jual' => $harga_jual,
    //                                         'total_beli' => $total_beli,
    //                                         'total_jual' => $total_jual,
    //                                         'saldo' => $saldo,
    //                                         'nilai_beli_saldo' => $nilai_beli_saldo,
    //                                         'nilai_jual_saldo' => $nilai_jual_saldo,
    //                                     );
    //                                 }
    //                             }
    //                         }

    //                         $date = next_date($date);
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     return $data;
    // }

    // public function get_data_barang_masuk_pakan($periode, $next_periode, $stok, $kode_gudang, $kode_barang)
    // {
    //     $m_stok = new \Model\Storage\Stok_model();

    //     if ( empty($stok) ) {
    //         $_d_stok = $m_stok->where('periode', '<', $periode)->orderBy('periode', 'desc')->first();
    //     } else {
    //         // $_d_stok = $m_stok->where('periode', '<', $next_periode)->orderBy('periode', 'desc')->first();
    //         $_d_stok = null;
    //     }
    //     $d_stok = null;
    //     $d_stok = $m_stok->where('periode', $periode)->first();
    //     if ( !$d_stok ) {
    //         $d_stok = $_d_stok;
    //     } 
    //     // else {
    //     //     if ( $d_stok->periode >= $periode  ) {
    //     //         $d_stok = null;
    //     //     }
    //     // }

    //     if ( !empty($stok) ) {
    //         $tgl_terakhir = date("Y-m-t", strtotime($next_periode));
    //     } else {
    //         $tgl_terakhir = $next_periode;
    //     }

    //     // $_d_stok = $m_stok->where('periode', '<', $periode)->orderBy('periode', 'desc')->first();
    //     // $d_stok = null;
    //     // // $d_stok = $m_stok->where('periode', $periode)->first();
    //     // // if ( !$d_stok ) {
    //     //     $d_stok = $_d_stok;
    //     // // }

    //     // $tgl_terakhir = date("Y-m-t", strtotime($next_periode));

    //     $data = null;
    //     $data_retur = null;
    //     $data_beli = null;
    //     $d_rp = null;
    //     $d_kp = null;
    //     if ( !$_d_stok ) {
    //         if ( $kode_gudang != 'all' ) {
    //             $m_rp = new \Model\Storage\ReturPakan_model();
    //             $d_rp = $m_rp->where('tujuan', 'gudang')->where('id_tujuan', $kode_gudang)->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //             $m_kp = new \Model\Storage\KirimPakan_model();
    //             $d_kp = $m_kp->where('jenis_tujuan', 'gudang')->where('tujuan', $kode_gudang)->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //         } else {
    //             $m_rp = new \Model\Storage\ReturPakan_model();
    //             $d_rp = $m_rp->where('tujuan', 'gudang')->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //             $m_kp = new \Model\Storage\KirimPakan_model();
    //             $d_kp = $m_kp->where('jenis_tujuan', 'gudang')->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //         }
    //     } else {
    //         if ( $d_stok ) {
    //             $final = $periode;

    //             if ( $kode_gudang != 'all' ) {
    //                 $m_rp = new \Model\Storage\ReturPakan_model();
    //                 $d_rp = $m_rp->where('tujuan', 'gudang')->where('id_tujuan', $kode_gudang)->where('tgl_retur', '>=', $final)->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //                 $m_kp = new \Model\Storage\KirimPakan_model();
    //                 $d_kp = $m_kp->where('jenis_tujuan', 'gudang')->where('tujuan', $kode_gudang)->where('tgl_kirim', '>=', $final)->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //             } else {
    //                 $m_rp = new \Model\Storage\ReturPakan_model();
    //                 $d_rp = $m_rp->where('tujuan', 'gudang')->where('tgl_retur', '>=', $final)->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //                 $m_kp = new \Model\Storage\KirimPakan_model();
    //                 $d_kp = $m_kp->where('jenis_tujuan', 'gudang')->where('tgl_kirim', '>=', $final)->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //             }
    //         } else {
    //             if ( $kode_gudang != 'all' ) {
    //                 $m_rp = new \Model\Storage\ReturPakan_model();
    //                 $d_rp = $m_rp->where('tujuan', 'gudang')->where('id_tujuan', $kode_gudang)->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //                 $m_kp = new \Model\Storage\KirimPakan_model();
    //                 $d_kp = $m_kp->where('jenis_tujuan', 'gudang')->where('tujuan', $kode_gudang)->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //             } else {
    //                 $m_rp = new \Model\Storage\ReturPakan_model();
    //                 $d_rp = $m_rp->where('tujuan', 'gudang')->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //                 $m_kp = new \Model\Storage\KirimPakan_model();
    //                 $d_kp = $m_kp->where('jenis_tujuan', 'gudang')->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //             }
    //         }
    //     }

    //     // if ( empty($stok) ) {
    //         // STOK AWAL
    //         if ( $_d_stok ) {
    //             // $d_stok = $d_stok->toArray();
    //             $m_dstok = new \Model\Storage\DetStok_model();
    //             if ( $kode_barang != 'all' && $kode_gudang != 'all' ) {
    //                 $d_dstok = $m_dstok->where('id_header', $_d_stok->id)->where('jenis_barang', 'pakan')->where('kode_barang', $kode_barang)->where('kode_gudang', $kode_gudang)->orderBy('tgl_trans', 'asc')->get();
    //             } else if ( $kode_barang != 'all' && $kode_gudang == 'all' ) {
    //                 $d_dstok = $m_dstok->where('id_header', $_d_stok->id)->where('jenis_barang', 'pakan')->where('kode_barang', $kode_barang)->orderBy('tgl_trans', 'asc')->get();
    //             } else if ( $kode_barang == 'all' && $kode_gudang != 'all' ) {
    //                 $d_dstok = $m_dstok->where('id_header', $_d_stok->id)->where('jenis_barang', 'pakan')->where('kode_gudang', $kode_gudang)->orderBy('tgl_trans', 'asc')->get();
    //             } else if ( $kode_barang == 'all' && $kode_gudang == 'all' ) {
    //                 $d_dstok = $m_dstok->where('id_header', $_d_stok->id)->where('jenis_barang', 'pakan')->orderBy('tgl_trans', 'asc')->get();
    //             }

    //             if ( $d_dstok->count() > 0 ) {
    //                 $d_dstok = $d_dstok->toArray();
    //                 foreach ($d_dstok as $k_det => $v_det) {
    //                     $isi = 1;
    //                     if ( isset($data[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]) ) {
    //                         foreach ($data[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ] as $k => $val) {
    //                             if ( $val['kode_trans'] == $v_det['kode_trans'] ) {
    //                                 $isi = 0;
    //                             }
    //                         }
    //                     }

    //                     if ( $isi == 1 ) {
    //                         if ( $v_det['jenis_trans'] == 'RETUR' ) {
    //                             if ( empty($stok) ) {
    //                                 $data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][] = array(
    //                                     'tgl_trans' => $v_det['tgl_trans'],
    //                                     'kode_gudang' => $v_det['kode_gudang'],
    //                                     'kode_barang' => $v_det['kode_barang'],
    //                                     'jumlah' => $v_det['jumlah'],
    //                                     'hrg_jual' => $v_det['hrg_jual'],
    //                                     'hrg_beli' => $v_det['hrg_beli'],
    //                                     'kode_trans' => $v_det['kode_trans'],
    //                                     'dari' => $v_det['jenis_trans']
    //                                 );
    //                             } else {
    //                                 $data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][ $v_det['tgl_trans'] ][] = array(
    //                                     'tgl_trans' => $v_det['tgl_trans'],
    //                                     'kode_gudang' => $v_det['kode_gudang'],
    //                                     'kode_barang' => $v_det['kode_barang'],
    //                                     'jumlah' => $v_det['jumlah'],
    //                                     'hrg_jual' => $v_det['hrg_jual'],
    //                                     'hrg_beli' => $v_det['hrg_beli'],
    //                                     'kode_trans' => $v_det['kode_trans'],
    //                                     'dari' => $v_det['jenis_trans']
    //                                 );
    //                             }

    //                             // ksort($data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]);
    //                         } else {
    //                             if ( empty($stok) ) {
    //                                 $data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][] = array(
    //                                     'tgl_trans' => $v_det['tgl_trans'],
    //                                     'kode_gudang' => $v_det['kode_gudang'],
    //                                     'kode_barang' => $v_det['kode_barang'],
    //                                     'jumlah' => $v_det['jumlah'],
    //                                     'hrg_jual' => $v_det['hrg_jual'],
    //                                     'hrg_beli' => $v_det['hrg_beli'],
    //                                     'kode_trans' => $v_det['kode_trans'],
    //                                     'dari' => $v_det['jenis_trans']
    //                                 );
    //                             } else {
    //                                 $data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][ $v_det['tgl_trans'] ][] = array(
    //                                     'tgl_trans' => $v_det['tgl_trans'],
    //                                     'kode_gudang' => $v_det['kode_gudang'],
    //                                     'kode_barang' => $v_det['kode_barang'],
    //                                     'jumlah' => $v_det['jumlah'],
    //                                     'hrg_jual' => $v_det['hrg_jual'],
    //                                     'hrg_beli' => $v_det['hrg_beli'],
    //                                     'kode_trans' => $v_det['kode_trans'],
    //                                     'dari' => $v_det['jenis_trans']
    //                                 );
    //                             }

    //                             // ksort($data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]);
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     // }

    //     // RETUR
    //     if ( $d_rp->count() > 0 ) {
    //         $d_rp = $d_rp->toArray();
    //         foreach ($d_rp as $k_rp => $v_rp) {
    //             $m_drp = new \Model\Storage\DetReturPakan_model();

    //             $d_drp = null;
    //             if ( $kode_barang != 'all' ) {
    //                 $d_drp = $m_drp->where('id_header', $v_rp['id'])->where('item', $kode_barang)->get();
    //             } else {
    //                 $d_drp = $m_drp->where('id_header', $v_rp['id'])->get();
    //             }

    //             if ( $d_drp->count() > 0 ) {
    //                 $d_drp = $d_drp->toArray();
    //                 foreach ($d_drp as $k_det => $v_det) {
    //                     $m_kp = new \Model\Storage\KirimPakan_model();
    //                     $_d_kp = $m_kp->where('no_order', $v_rp['no_order'])->orderBy('id', 'desc')->first();

    //                     $_d_kpd = null;
    //                     if ( !empty($_d_kp) ) {
    //                         $m_kpd = new \Model\Storage\KirimPakanDetail_model();
    //                         $_d_kpd = $m_kpd->where('id_header', $_d_kp->id)->where('item', $v_det['item'])->orderBy('id', 'desc')->first();
    //                     }

    //                     $harga_beli = !empty($_d_kpd) ? $_d_kpd->nilai_beli/$_d_kpd->jumlah : 0;
    //                     $harga_jual = !empty($_d_kpd) ? $_d_kpd->nilai_jual/$_d_kpd->jumlah : 0;

    //                     $isi = 1;
    //                     if ( isset($data[ $v_rp['id_tujuan'] ][ $v_det['item'] ]) ) {
    //                         foreach ($data[ $v_rp['id_tujuan'] ][ $v_det['item'] ] as $k => $val) {
    //                             if ( $val['kode_trans'] == $v_rp['id'] ) {
    //                                 $isi = 0;
    //                             }
    //                         }
    //                     }

    //                     if ( $isi == 1 ) {
    //                         if ( empty($stok) ) {
    //                             $data_retur[ $v_rp['id_tujuan'] ][ $v_det['item'] ][] = array(
    //                                 'tgl_trans' => $v_rp['tgl_retur'],
    //                                 'kode_gudang' => $v_rp['id_tujuan'],
    //                                 'kode_barang' => $v_det['item'],
    //                                 'jumlah' => $v_det['jumlah'],
    //                                 'hrg_jual' => $harga_jual,
    //                                 'hrg_beli' => $harga_beli,
    //                                 'kode_trans' => $v_rp['id'],
    //                                 'dari' => 'RETUR'
    //                             );
    //                         } else {
    //                             $data_retur[ $v_rp['id_tujuan'] ][ $v_det['item'] ][ $v_rp['tgl_retur'] ][] = array(
    //                                 'tgl_trans' => $v_rp['tgl_retur'],
    //                                 'kode_gudang' => $v_rp['id_tujuan'],
    //                                 'kode_barang' => $v_det['item'],
    //                                 'jumlah' => $v_det['jumlah'],
    //                                 'hrg_jual' => $harga_jual,
    //                                 'hrg_beli' => $harga_beli,
    //                                 'kode_trans' => $v_rp['id'],
    //                                 'dari' => 'RETUR'
    //                             );
    //                         }

    //                         // ksort($data_retur[ $v_rp['id_tujuan'] ][ $v_det['item'] ]);
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     // ORDER
    //     if ( $d_kp->count() > 0 ) {
    //         $d_kp = $d_kp->toArray();
    //         foreach ($d_kp as $k_kp => $v_kp) {
    //             $m_tp = new \Model\Storage\TerimaPakan_model();
    //             $d_tp = $m_tp->where('id_kirim_pakan', $v_kp['id'])->first();

    //             $m_kpd = new \Model\Storage\KirimPakanDetail_model();

    //             $d_kpd = null;
    //             if ( $kode_barang != 'all' ) {
    //                 $d_kpd = $m_kpd->where('id_header', $v_kp['id'])->where('item', $kode_barang)->get();
    //             } else {
    //                 $d_kpd = $m_kpd->where('id_header', $v_kp['id'])->get();
    //             }

    //             if ( $d_kpd->count() > 0 ) {
    //                 $d_kpd = $d_kpd->toArray();
    //                 foreach ($d_kpd as $k_det => $v_det) {
    //                     $jumlah_terima = 0;
    //                     if ( $d_tp ) {
    //                         $m_dtp = new \Model\Storage\TerimaPakanDetail_model();
    //                         $jumlah_terima = $m_dtp->where('id_header', $d_tp->id)->where('item', $v_det['item'])->sum('jumlah');
    //                     }

    //                     $hrg_beli = ($v_det['nilai_beli'] > 0 && $v_det['jumlah'] > 0) ? $v_det['nilai_beli'] / $v_det['jumlah'] : 0;
    //                     $hrg_jual = ($v_det['nilai_jual'] > 0 && $v_det['jumlah'] > 0) ? $v_det['nilai_jual'] / $v_det['jumlah'] : 0;

    //                     $m_op = new \Model\Storage\OrderPakan_model();
    //                     $d_op = $m_op->where('no_order', $v_kp['no_order'])->orderBy('id', 'desc')->first();

    //                     if ( !empty($d_op) ) {
    //                         $m_opd = new \Model\Storage\OrderPakanDetail_model();
    //                         $d_opd = $m_opd->where('id_header', $d_op->id)->where('barang', $v_det['item'])->orderBy('id', 'desc')->first();

    //                         $hrg_beli = !empty($d_opd) ? $d_opd->harga : 0;
    //                         $hrg_jual = !empty($d_opd) ? $d_opd->harga_jual : 0;
    //                     }

    //                     $isi = 1;
    //                     if ( isset($data[ $v_kp['tujuan'] ][ $v_det['item'] ]) ) {
    //                         foreach ($data[ $v_kp['tujuan'] ][ $v_det['item'] ] as $k => $val) {
    //                             if ( $val['kode_trans'] == $v_kp['no_order'] ) {
    //                                 $isi = 0;
    //                             }
    //                         }
    //                     }

    //                     if ( $isi == 1 ) {
    //                         if ( empty($stok) ) {
    //                             $data_beli[ $v_kp['tujuan'] ][ $v_det['item'] ][] = array(
    //                                 'tgl_trans' => $v_kp['tgl_kirim'],
    //                                 'kode_gudang' => $v_kp['tujuan'],
    //                                 'kode_barang' => $v_det['item'],
    //                                 'jumlah' => $jumlah_terima,
    //                                 'hrg_jual' => $hrg_jual,
    //                                 'hrg_beli' => $hrg_beli,
    //                                 'kode_trans' => $v_kp['no_order'],
    //                                 'dari' => 'ORDER'
    //                             );
    //                         } else {
    //                             $data_beli[ $v_kp['tujuan'] ][ $v_det['item'] ][ $v_kp['tgl_kirim'] ][] = array(
    //                                 'tgl_trans' => $v_kp['tgl_kirim'],
    //                                 'kode_gudang' => $v_kp['tujuan'],
    //                                 'kode_barang' => $v_det['item'],
    //                                 'jumlah' => $jumlah_terima,
    //                                 'hrg_jual' => $hrg_jual,
    //                                 'hrg_beli' => $hrg_beli,
    //                                 'kode_trans' => $v_kp['no_order'],
    //                                 'dari' => 'ORDER'
    //                             );
    //                         }

    //                         // ksort($data_beli[ $v_kp['tujuan'] ][ $v_det['item'] ]);
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     $data = array(
    //         'retur' => $data_retur,
    //         'beli' => $data_beli
    //     );

    //     return $data;
    // }

    // public function get_data_barang_keluar_pakan($periode, $next_periode, $stok, $kode_gudang, $kode_barang)
    // {
    //     $m_stok = new \Model\Storage\Stok_model();

    //     $_d_stok = $m_stok->where('periode', '<', $periode)->with(['det_stok'])->orderBy('periode', 'desc')->first();
    //     $d_stok = null;
    //     // $d_stok = $m_stok->where('periode', $periode)->with(['det_stok'])->first();
    //     // if ( !$d_stok ) {
    //         $d_stok = $_d_stok;
    //     // }

    //     $tgl_terakhir = date("Y-m-t", strtotime($next_periode));

    //     $data = null;
    //     $d_rp = null;
    //     $d_kp = null;
    //     if ( !$_d_stok ) {
    //         if ( $kode_gudang != 'all' ) {
    //             $m_rp = new \Model\Storage\ReturPakan_model();
    //             $d_rp = $m_rp->where('asal', 'gudang')->where('id_asal', $kode_gudang)->where('tujuan', 'supplier')->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //             $m_kp = new \Model\Storage\KirimPakan_model();
    //             $d_kp = $m_kp->where('jenis_kirim', 'opkg')->where('asal', $kode_gudang)->where('tgl_kirim', '<=', $tgl_terakhir)->with(['detail'])->orderBy('tgl_kirim', 'asc')->get();
    //         } else {
    //             $m_rp = new \Model\Storage\ReturPakan_model();
    //             $d_rp = $m_rp->where('asal', 'gudang')->where('tujuan', 'supplier')->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //             $m_kp = new \Model\Storage\KirimPakan_model();
    //             $d_kp = $m_kp->where('jenis_kirim', 'opkg')->where('tgl_kirim', '<=', $tgl_terakhir)->with(['detail'])->orderBy('tgl_kirim', 'asc')->get();
    //         }
    //     } else {
    //         if ( $d_stok ) {
    //             $final = $periode;

    //             if ( $kode_gudang != 'all' ) {
    //                 $m_rp = new \Model\Storage\ReturPakan_model();
    //                 $d_rp = $m_rp->where('asal', 'gudang')->where('id_asal', $kode_gudang)->where('tujuan', 'supplier')->where('tgl_retur', '>=', $final)->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //                 $m_kp = new \Model\Storage\KirimPakan_model();
    //                 $d_kp = $m_kp->where('jenis_kirim', 'opkg')->where('asal', $kode_gudang)->where('tgl_kirim', '>=', $final)->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //             } else {
    //                 $m_rp = new \Model\Storage\ReturPakan_model();
    //                 $d_rp = $m_rp->where('asal', 'gudang')->where('tujuan', 'supplier')->where('tgl_retur', '>=', $final)->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //                 $m_kp = new \Model\Storage\KirimPakan_model();
    //                 $d_kp = $m_kp->where('jenis_kirim', 'opkg')->where('tgl_kirim', '>=', $final)->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //             }
    //         } else {
    //             if ( $kode_gudang != 'all' ) {
    //                 $m_rp = new \Model\Storage\ReturPakan_model();
    //                 $d_rp = $m_rp->where('asal', 'gudang')->where('id_asal', $kode_gudang)->where('tujuan', 'supplier')->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //                 $m_kp = new \Model\Storage\KirimPakan_model();
    //                 $d_kp = $m_kp->where('jenis_kirim', 'opkg')->where('asal', $kode_gudang)->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //             } else {
    //                 $m_rp = new \Model\Storage\ReturPakan_model();
    //                 $d_rp = $m_rp->where('asal', 'gudang')->where('tujuan', 'supplier')->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->get();

    //                 $m_kp = new \Model\Storage\KirimPakan_model();
    //                 $d_kp = $m_kp->where('jenis_kirim', 'opkg')->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->get();
    //             }
    //         }
    //     }

    //     // RETUR
    //     if ( $d_rp->count() > 0 ) {
    //         $d_rp = $d_rp->toArray();
    //         foreach ($d_rp as $k_rp => $v_rp) {
    //             $m_drv = new \Model\Storage\DetReturPakan_model();
    //             $d_drv = null;
    //             if ( $kode_barang != 'all' ) {
    //                 $d_drv = $m_drv->where('id_header', $v_rp['id'])->where('item', $kode_barang)->get();
    //             } else {
    //                 $d_drv = $m_drv->where('id_header', $v_rp['id'])->get();
    //             }

    //             if ( $d_drv->count() > 0 ) {
    //                 $d_drv = $d_drv->toArray();
    //                 foreach ($d_drv as $k_det => $v_det) {
    //                     if ( empty($stok) ) {
    //                         $data[ $v_rp['id_asal'] ][ $v_det['item'] ][] = array(
    //                             'key' => $v_det['id'],
    //                             'tgl_trans' => $v_rp['tgl_retur'],
    //                             'kode_gudang' => $v_rp['id_asal'],
    //                             'kode_barang' => $v_det['item'],
    //                             'jumlah' => $v_det['jumlah'],
    //                             'kode_trans' => $v_rp['no_order'],
    //                             'dari' => 'RETUR'
    //                         );
    //                     } else {
    //                         $data[ $v_rp['id_asal'] ][ $v_det['item'] ][ $v_rp['tgl_retur'] ][] = array(
    //                             'key' => $v_det['id'],
    //                             'tgl_trans' => $v_rp['tgl_retur'],
    //                             'kode_gudang' => $v_rp['id_asal'],
    //                             'kode_barang' => $v_det['item'],
    //                             'jumlah' => $v_det['jumlah'],
    //                             'hrg_beli' => ($v_det['nilai_beli'] > 0 && $v_det['jumlah'] > 0) ? $v_det['nilai_beli']/$v_det['jumlah'] : 0,
    //                             'hrg_jual' => ($v_det['nilai_jual'] > 0 && $v_det['jumlah'] > 0) ? $v_det['nilai_jual']/$v_det['jumlah'] : 0,
    //                             'kode_trans' => $v_rp['no_order'],
    //                             'dari' => 'RETUR'
    //                         );
    //                     }
    //                 }
    //             }
    //         }
    //     }


    //     // ORDER
    //     if ( $d_kp->count() > 0 ) {
    //         $d_kp = $d_kp->toArray();
    //         foreach ($d_kp as $k_kp => $v_kp) {
    //             $m_tp = new \Model\Storage\TerimaPakan_model();
    //             $d_tp = $m_tp->where('id_kirim_pakan', $v_kp['id'])->orderBy('id', 'desc')->first();

    //             $m_kpd = new \Model\Storage\KirimPakanDetail_model();
    //             $d_kpd = null;
    //             if ( $kode_barang != 'all' ) {
    //                 $d_kpd = $m_kpd->where('id_header', $v_kp['id'])->where('item', $kode_barang)->get();
    //             } else {
    //                 $d_kpd = $m_kpd->where('id_header', $v_kp['id'])->get();
    //             }

    //             if ( $d_kpd->count() > 0 ) {
    //                 $d_kpd = $d_kpd->toArray();
    //                 foreach ($d_kpd as $k_det => $v_det) {
    //                     $jumlah_terima = 0;
    //                     if ( $d_tp ) {
    //                         $m_dtp = new \Model\Storage\TerimaPakanDetail_model();
    //                         $jumlah_terima = $m_dtp->where('id_header', $d_tp->id)->where('item', $v_det['item'])->sum('jumlah');
    //                     }

    //                     if ( empty($stok) ) {
    //                         $data[ $v_kp['asal'] ][ $v_det['item'] ][] = array(
    //                             'key' => $v_det['id'],
    //                             'tgl_trans' => $v_kp['tgl_kirim'],
    //                             'kode_gudang' => $v_kp['asal'],
    //                             'kode_barang' => $v_det['item'],
    //                             'jumlah' => $jumlah_terima,
    //                             'kode_trans' => $v_kp['no_order'],
    //                             'dari' => 'ORDER'
    //                         );
    //                     } else {
    //                         $data[ $v_kp['asal'] ][ $v_det['item'] ][ $v_kp['tgl_kirim'] ][] = array(
    //                             'key' => $v_det['id'],
    //                             'tgl_trans' => $v_kp['tgl_kirim'],
    //                             'kode_gudang' => $v_kp['asal'],
    //                             'kode_barang' => $v_det['item'],
    //                             'jumlah' => $jumlah_terima,
    //                             'hrg_beli' => ($v_det['nilai_beli'] > 0 && $v_det['jumlah'] > 0) ? $v_det['nilai_beli']/$v_det['jumlah'] : 0,
    //                             'hrg_jual' => ($v_det['nilai_jual'] > 0 && $v_det['jumlah'] > 0) ? $v_det['nilai_jual']/$v_det['jumlah'] : 0,
    //                             'kode_trans' => $v_kp['no_order'],
    //                             'dari' => 'ORDER'
    //                         );
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     return $data;
    // }

    // public function hitung_stok_awal_pakan($periode, $next_periode, $stok, $kode_gudang, $kode_barang)
    // {
    //     $_data_masuk = $this->get_data_barang_masuk_pakan( $periode, $next_periode, $stok, $kode_gudang, $kode_barang );
    //     $_data_keluar = $this->get_data_barang_keluar_pakan( $periode, $next_periode, $stok, $kode_gudang, $kode_barang );

    //     $m_gdg = new \Model\Storage\Gudang_model();
    //     $d_gdg = $m_gdg->where('jenis', 'PAKAN')->get();

    //     $data = array();
    //     if ( $d_gdg->count() > 0 ) {
    //         $d_gdg = $d_gdg->toArray();
    //         foreach ($d_gdg as $k_gdg => $v_gdg) {
    //             $m_barang = new \Model\Storage\Barang_model();
    //             $d_barang = $m_barang->where('tipe', 'pakan')->orderBy('kode', 'asc')->get();
    //             if ( $d_barang->count() > 0 ) {
    //                 $d_barang = $d_barang->toArray();
    //                 foreach ($d_barang as $k_brg => $v_brg) {
    //                     $data_keluar = isset($_data_keluar[ $v_gdg['id'] ][ $v_brg['kode'] ]) ? $_data_keluar[ $v_gdg['id'] ][ $v_brg['kode'] ] : null;

    //                     $data_masuk_retur = isset($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ]) ? $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ] : null;
    //                     $data_masuk_beli = isset($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ]) ? $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ] : null;

    //                     if ( !empty($data_keluar) ) {
    //                         $jml_masuk = 0;
    //                         $jml_keluar = 0;
    //                         $idx_masuk_retur = 0;
    //                         $idx_masuk_beli = 0;
    //                         foreach ($data_keluar as $k_dk => $v_dk) {
    //                             $total_nilai_keluar_beli = 0;
    //                             $total_nilai_keluar_jual = 0;
    //                             $jml_keluar = $v_dk['jumlah'];
    //                             $tgl_keluar = $v_dk['tgl_trans'];

    //                             // if ( !empty($_data_masuk) ) {
    //                             if ( !empty($data_masuk_retur) ) {
    //                                 $_jml_keluar = $jml_keluar;
    //                                 while ($_jml_keluar > 0) {
    //                                     if ( isset($data_masuk_retur[$idx_masuk_retur]) ) {
    //                                         $hrg_beli = $data_masuk_retur[$idx_masuk_retur]['hrg_beli'];
    //                                         $hrg_jual = $data_masuk_retur[$idx_masuk_retur]['hrg_jual'];
    //                                         if ( $data_masuk_retur[$idx_masuk_retur]['tgl_trans'] <= $tgl_keluar ) {
    //                                             if ( $data_masuk_retur[$idx_masuk_retur]['jumlah'] >= $jml_keluar ) {
    //                                                 $data_masuk_retur[$idx_masuk_retur]['jumlah'] = $data_masuk_retur[$idx_masuk_retur]['jumlah'] - $jml_keluar;

    //                                                 $total_nilai_keluar_beli += $jml_keluar * $hrg_beli;
    //                                                 $total_nilai_keluar_jual += $jml_keluar * $hrg_jual;

    //                                                 $jml_keluar = 0;
    //                                                 $_jml_keluar = $jml_keluar;
    //                                             } else {
    //                                                 $total_nilai_keluar_beli += $data_masuk_retur[$idx_masuk_retur]['jumlah'] * $hrg_beli;
    //                                                 $total_nilai_keluar_jual += $data_masuk_retur[$idx_masuk_retur]['jumlah'] * $hrg_jual;

    //                                                 $jml_keluar = $jml_keluar - $data_masuk_retur[$idx_masuk_retur]['jumlah'];
    //                                                 $_jml_keluar = $jml_keluar;

    //                                                 $data_masuk_retur[$idx_masuk_retur]['jumlah'] = 0;

    //                                                 $idx_masuk_retur++;
    //                                             }
    //                                         } else {
    //                                             $_jml_keluar = 0;
    //                                         }
    //                                     } else {
    //                                         $_jml_keluar = 0;
    //                                     }
    //                                 }
    //                             }

    //                             if ( !empty($data_masuk_beli) ) {
    //                                 $_jml_keluar = $jml_keluar;
    //                                 while ($_jml_keluar > 0) {
    //                                     if ( isset($data_masuk_beli[$idx_masuk_beli]) ) {
    //                                         $hrg_beli = ($data_masuk_beli[$idx_masuk_beli]['hrg_beli'] > 0) ? $data_masuk_beli[$idx_masuk_beli]['hrg_beli'] : $data_masuk_beli[$idx_masuk_beli-1]['hrg_beli'];
    //                                         $hrg_jual = ($data_masuk_beli[$idx_masuk_beli]['hrg_jual'] > 0) ? $data_masuk_beli[$idx_masuk_beli]['hrg_jual'] : $data_masuk_beli[$idx_masuk_beli-1]['hrg_jual'];
    //                                         if ( $data_masuk_beli[$idx_masuk_beli]['tgl_trans'] <= $tgl_keluar ) {
    //                                             if ( $data_masuk_beli[$idx_masuk_beli]['jumlah'] >= $jml_keluar ) {
    //                                                 $data_masuk_beli[$idx_masuk_beli]['jumlah'] = $data_masuk_beli[$idx_masuk_beli]['jumlah'] - $jml_keluar;

    //                                                 $total_nilai_keluar_beli += $jml_keluar * $hrg_beli;
    //                                                 $total_nilai_keluar_jual += $jml_keluar * $hrg_jual;

    //                                                 $jml_keluar = 0;
    //                                                 $_jml_keluar = $jml_keluar;
    //                                             } else {
    //                                                 $total_nilai_keluar_beli += $data_masuk_beli[$idx_masuk_beli]['jumlah'] * $hrg_beli;
    //                                                 $total_nilai_keluar_jual += $data_masuk_beli[$idx_masuk_beli]['jumlah'] * $hrg_jual;

    //                                                 $jml_keluar = $jml_keluar - $data_masuk_beli[$idx_masuk_beli]['jumlah'];
    //                                                 $_jml_keluar = $jml_keluar;

    //                                                 $data_masuk_beli[$idx_masuk_beli]['jumlah'] = 0;

    //                                                 $idx_masuk_beli++;
    //                                             }
    //                                         } else {
    //                                             $_jml_keluar = 0;
    //                                         }
    //                                     } else {
    //                                         $_jml_keluar = 0;
    //                                     }
    //                                 }
    //                             }
    //                         }
    //                     }

    //                     if ( !empty($data_masuk_retur) ) {
    //                         foreach ($data_masuk_retur as $k_dm => $val) {
    //                             if ( $val['jumlah'] > 0 ) {
    //                                 $data[ $v_gdg['id'] ][ $v_brg['kode'] ][ $val['kode_trans'] ] = array(
    //                                     'tgl_trans' => $val['tgl_trans'],
    //                                     'kode_gudang' => $val['kode_gudang'],
    //                                     'kode_barang' => $val['kode_barang'],
    //                                     'jumlah' => $val['jumlah'],
    //                                     'hrg_jual' => $val['hrg_jual'],
    //                                     'hrg_beli' => $val['hrg_beli'],
    //                                     'kode_trans' => $val['kode_trans'],
    //                                     'dari' => $val['dari'],
    //                                 );

    //                                 ksort( $data );
    //                             }
    //                         }
    //                     }
    //                     if ( !empty($data_masuk_beli) ) {
    //                         foreach ($data_masuk_beli as $k_dm => $val) {
    //                             if ( $val['jumlah'] > 0 ) {
    //                                 $data[ $v_gdg['id'] ][ $v_brg['kode'] ][ $val['kode_trans'] ] = array(
    //                                     'tgl_trans' => $val['tgl_trans'],
    //                                     'kode_gudang' => $val['kode_gudang'],
    //                                     'kode_barang' => $val['kode_barang'],
    //                                     'jumlah' => $val['jumlah'],
    //                                     'hrg_jual' => $val['hrg_jual'],
    //                                     'hrg_beli' => $val['hrg_beli'],
    //                                     'kode_trans' => $val['kode_trans'],
    //                                     'dari' => $val['dari'],
    //                                 );

    //                                 ksort( $data );
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     return $data;

    //     // $_data_masuk = $this->get_data_barang_masuk_pakan( $periode, $next_periode, $stok, $kode_gudang, $kode_barang );
    //     // $_data_keluar = $this->get_data_barang_keluar_pakan( $periode, $next_periode, $stok, $kode_gudang, $kode_barang );

    //     // // cetak_r('MASUK -----------------');
    //     // // cetak_r( $_data_masuk, 1);
    //     // // cetak_r('KELUAR -----------------');
    //     // // cetak_r( $_data_keluar, 1 );

    //     // // $jml_masuk = 0;
    //     // // foreach ($_data_masuk as $k_dm => $v_dm) {
    //     // //     if ( isset($v_dm[16]['PK2106003']) ) {
    //     // //         foreach ($v_dm[16]['PK2106003'] as $key => $val) {
    //     // //             $jml_masuk += $val['jumlah'];
    //     // //         }
    //     // //     }
    //     // // }

    //     // // $jml_keluar = 0;
    //     // // foreach ($_data_keluar[16]['PK2106003'] as $k_dk => $v_dk) {
    //     // //     $jml_keluar += $v_dk['jumlah'];
    //     // // }

    //     // // cetak_r( $jml_masuk.'|'.$jml_keluar );
    //     // // cetak_r( $periode.'|'.$next_periode, 1 );

    //     // $m_gdg = new \Model\Storage\Gudang_model();
    //     // $d_gdg = null;
    //     // if ( $kode_gudang != 'all' ) {
    //     //     $d_gdg = $m_gdg->where('jenis', 'PAKAN')->where('id', $kode_gudang)->get();
    //     // } else {
    //     //     $d_gdg = $m_gdg->where('jenis', 'PAKAN')->get();
    //     // }

    //     // $data = null;
    //     // if ( $d_gdg->count() > 0 ) {
    //     //     $d_gdg = $d_gdg->toArray();
    //     //     foreach ($d_gdg as $k_gdg => $v_gdg) {
    //     //         $m_barang = new \Model\Storage\Barang_model();
    //     //         $d_barang = null;
    //     //         $data_barang = null;
    //     //         if ( $kode_barang != 'all' ) {
    //     //             $d_barang = $m_barang->select('kode')->distinct('kode')->where('tipe', 'pakan')->where('kode', $kode_barang)->get();
    //     //             $data_barang = null;
    //     //             if ( $d_barang->count() > 0 ) {
    //     //                 $d_barang = $d_barang->toArray();
    //     //                 foreach ($d_barang as $k_brg => $v_brg) {
    //     //                     $m_barang = new \Model\Storage\Barang_model();
    //     //                     $_d_barang = $m_barang->where('kode', $v_brg)->where('tipe', 'pakan')->orderBy('version', 'desc')->first();
    //     //                     if ( !empty($_d_barang) ) {
    //     //                         $key = $_d_barang->nama.' | '.$_d_barang->kode;
    //     //                         $data_barang[$key] = $_d_barang->toArray();

    //     //                         ksort($data_barang);
    //     //                     }
    //     //                 }
    //     //             }
    //     //         } else {
    //     //             $d_barang = $m_barang->select('kode')->distinct('kode')->where('tipe', 'pakan')->get();
    //     //             $data_barang = null;
    //     //             if ( $d_barang->count() > 0 ) {
    //     //                 $d_barang = $d_barang->toArray();
    //     //                 foreach ($d_barang as $k_brg => $v_brg) {
    //     //                     $m_barang = new \Model\Storage\Barang_model();
    //     //                     $_d_barang = $m_barang->where('kode', $v_brg)->where('tipe', 'pakan')->orderBy('version', 'desc')->first();
    //     //                     if ( !empty($_d_barang) ) {
    //     //                         $key = $_d_barang->nama.' | '.$_d_barang->kode;
    //     //                         $data_barang[$key] = $_d_barang->toArray();

    //     //                         ksort($data_barang);
    //     //                     }
    //     //                 }
    //     //             }
    //     //         }
    //     //         if ( count($data_barang) > 0 ) {
    //     //             // $d_barang = $d_barang->toArray();
    //     //             foreach ($data_barang as $k_brg => $v_brg) {
    //     //                 $data_keluar = isset($_data_keluar[ $v_gdg['id'] ][ $v_brg['kode'] ]) ? $_data_keluar[ $v_gdg['id'] ][ $v_brg['kode'] ] : null;

    //     //                 if ( !empty($data_keluar) ) {
    //     //                     $jml_masuk = 0;
    //     //                     $jml_keluar = 0;
    //     //                     foreach ($data_keluar as $k_dk => $v_dk) {
    //     //                         $total_nilai_keluar_beli = 0;
    //     //                         $total_nilai_keluar_jual = 0;
    //     //                         $jml_keluar = $v_dk['jumlah'];
    //     //                         $tgl_keluar = $v_dk['tgl_trans'];
    //     //                         if ( !empty($_data_masuk) ) {
    //     //                             if ( !empty($_data_masuk['retur']) ) {
    //     //                                 $data_masuk_retur = isset($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ]) ? $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ] : null;
    //     //                                 $idx_masuk_retur = 0;

    //     //                                 $_jml_keluar = $jml_keluar;
    //     //                                 while ($_jml_keluar > 0) {
    //     //                                     if ( isset($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ]) && $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['tgl_trans'] <= $tgl_keluar ) {

    //     //                                         $hrg_beli = $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['hrg_beli'];
    //     //                                         $hrg_jual = $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['hrg_jual'];

    //     //                                         if ( $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] >= $jml_keluar ) {
    //     //                                             $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] = $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] - $jml_keluar;

    //     //                                             $total_nilai_keluar_beli += $jml_keluar * $hrg_beli;
    //     //                                             $total_nilai_keluar_jual += $jml_keluar * $hrg_jual;

    //     //                                             $jml_keluar = 0;
    //     //                                             $_jml_keluar = $jml_keluar;
    //     //                                         } else {
    //     //                                             $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] = 0;
    //     //                                             $jml_keluar = $jml_keluar - $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'];
    //     //                                             $_jml_keluar = $jml_keluar;

    //     //                                             $total_nilai_keluar_beli += $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] * $hrg_beli;
    //     //                                             $total_nilai_keluar_jual += $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] * $hrg_jual;

    //     //                                             $idx_masuk_retur++;
    //     //                                         }
    //     //                                     } else {
    //     //                                         $_jml_keluar = 0;
    //     //                                     }
    //     //                                 }
    //     //                             }
    //     //                             if ( !empty($_data_masuk['beli']) ) {
    //     //                                 $data_masuk_beli = isset($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ]) ? $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ] : null;
    //     //                                 $idx_masuk_beli = 0;

    //     //                                 $_jml_keluar = $jml_keluar;
    //     //                                 while ($_jml_keluar > 0) {
    //     //                                     if ( isset($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ]) && $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['tgl_trans'] <= $tgl_keluar ) {

    //     //                                         $hrg_beli = $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['hrg_beli'];
    //     //                                         $hrg_jual = $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['hrg_jual'];

    //     //                                         if ( $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] >= $jml_keluar ) {
    //     //                                             $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] = $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] - $jml_keluar;

    //     //                                             $total_nilai_keluar_beli += $jml_keluar * $hrg_beli;
    //     //                                             $total_nilai_keluar_jual += $jml_keluar * $hrg_jual;

    //     //                                             $jml_keluar = 0;
    //     //                                             $_jml_keluar = $jml_keluar;
    //     //                                         } else {
    //     //                                             $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] = 0;
    //     //                                             $jml_keluar = $jml_keluar - $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'];
    //     //                                             $_jml_keluar = $jml_keluar;

    //     //                                             $total_nilai_keluar_beli += $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] * $hrg_beli;
    //     //                                             $total_nilai_keluar_jual += $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] * $hrg_jual;

    //     //                                             $idx_masuk_beli++;
    //     //                                         }
    //     //                                     } else {
    //     //                                         $_jml_keluar = 0;
    //     //                                     }
    //     //                                 }
    //     //                             }
    //     //                         }
    //     //                     }
    //     //                 }

    //     //                 if ( !empty($_data_masuk) ) {
    //     //                     if ( isset($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ]) ) {
    //     //                         foreach ($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ] as $k_dm => $val) {
    //     //                             if ( $val['jumlah'] > 0 ) {
    //     //                                 $data[ $v_gdg['id'] ][ $v_brg['kode'] ][ $val['kode_trans'] ] = array(
    //     //                                     'tgl_trans' => $val['tgl_trans'],
    //     //                                     'kode_gudang' => $val['kode_gudang'],
    //     //                                     'kode_barang' => $val['kode_barang'],
    //     //                                     'jumlah' => $val['jumlah'],
    //     //                                     'hrg_jual' => $val['hrg_jual'],
    //     //                                     'hrg_beli' => $val['hrg_beli'],
    //     //                                     'kode_trans' => $val['kode_trans'],
    //     //                                     'dari' => $val['dari'],
    //     //                                 );

    //     //                                 ksort( $data );
    //     //                             }
    //     //                         }
    //     //                     }
    //     //                     if ( isset($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ]) ) {
    //     //                         foreach ($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ] as $k_dm => $val) {
    //     //                             if ( $val['jumlah'] > 0 ) {
    //     //                                 $data[ $v_gdg['id'] ][ $v_brg['kode'] ][ $val['kode_trans'] ] = array(
    //     //                                     'tgl_trans' => $val['tgl_trans'],
    //     //                                     'kode_gudang' => $val['kode_gudang'],
    //     //                                     'kode_barang' => $val['kode_barang'],
    //     //                                     'jumlah' => $val['jumlah'],
    //     //                                     'hrg_jual' => $val['hrg_jual'],
    //     //                                     'hrg_beli' => $val['hrg_beli'],
    //     //                                     'kode_trans' => $val['kode_trans'],
    //     //                                     'dari' => $val['dari'],
    //     //                                 );

    //     //                                 ksort( $data );
    //     //                             }
    //     //                         }
    //     //                     }
    //     //                 }
    //     //             }
    //     //         }
    //     //     }
    //     // }

    //     // // cetak_r( $data, 1 );

    //     // return $data;
    // }

    // public function get_data_pakan($start_date, $end_date, $jenis, $kode_gudang, $kode_brg)
    // {
    //     // $start_date = '2021-03-25';
    //     // $end_date = '2021-04-05';
    //     // $jenis = 'obat';
    //     // $kode_gudang = 'all';
    //     // $kode_brg = 'all';

    //     $exp_per_start = explode("-", $start_date);
    //     $year_start = $exp_per_start[0];
    //     $month_start =  $exp_per_start[1];
    //     $day_start =  $exp_per_start[2];

    //     $exp_per_end = explode("-", $end_date);
    //     $year_end = $exp_per_end[0];
    //     $month_end =  $exp_per_end[1];
    //     $day_end =  $exp_per_end[2];

    //     $start_date_stok = null;
    //     $end_date_stok = null;

    //     $stok_awal = null;
    //     $_start_date = substr($start_date, 0, 7).'-01';

    //     $m_stok = new \Model\Storage\Stok_model();
    //     $d_stok = $m_stok->where('periode', '<', $_start_date)->with(['det_stok'])->orderBy('periode', 'desc')->first();
    //     if ( $d_stok ) {
    //         $start_date_stok = $d_stok->periode;
    //         $end_date_stok = prev_date($start_date);
    //     } else {
    //         $start_date_stok = substr($start_date, 0, 7).'-01';
    //         $end_date_stok = prev_date($start_date);
    //     }

    //     $_stok_awal = $this->hitung_stok_awal_pakan( $start_date_stok, $end_date_stok, null, $kode_gudang, $kode_brg );
    //     $_data_masuk = $this->get_data_barang_masuk_pakan( $start_date, $end_date, 'non stok', $kode_gudang, $kode_brg );
    //     $_data_keluar = $this->get_data_barang_keluar_pakan( $start_date, $end_date, 'non stok', $kode_gudang, $kode_brg );

    //     // cetak_r('STOK --------------------------------');
    //     // cetak_r($_stok_awal, 1);
    //     // cetak_r('MASUK --------------------------------');
    //     // cetak_r($_data_masuk);
    //     // cetak_r('KELUAR --------------------------------');
    //     // cetak_r($_data_keluar);

    //     $m_gdg = new \Model\Storage\Gudang_model();
    //     if ( $kode_gudang != 'all' ) {
    //         $d_gdg = $m_gdg->where('id', $kode_gudang)->where('jenis', 'like', '%'.$jenis.'%')->get();
    //     } else {
    //         $d_gdg = $m_gdg->where('jenis', 'like', '%'.$jenis.'%')->get();
    //     }

    //     $data = null;
    //     if ( $d_gdg->count() > 0 ) {
    //         $d_gdg = $d_gdg->toArray();
    //         foreach ($d_gdg as $k_gdg => $v_gdg) {
    //             $data[ $v_gdg['id'] ]['gudang'] = $v_gdg['nama'];

    //             $m_barang = new \Model\Storage\Barang_model();
    //             if ( $kode_brg != 'all' ) {
    //                 $d_barang = $m_barang->distinct('kode')->select('kode')->where('kode', $kode_brg)->where('tipe', 'like', '%'.$jenis.'%')->orderBy('kode', 'asc')->get();
    //             } else {
    //                 $d_barang = $m_barang->distinct('kode')->select('kode')->where('tipe', 'like', '%'.$jenis.'%')->orderBy('kode', 'asc')->get();
    //             }
    //             if ( $d_barang->count() > 0 ) {
    //                 $d_barang = $d_barang->toArray();
    //                 foreach ($d_barang as $k_brg => $val_brg) {
    //                     $v_brg = $m_barang->where('kode', $val_brg['kode'])->orderBy('version', 'desc')->first()->toArray();
    //                     $stok_awal = isset($_stok_awal[ $v_gdg['id'] ][ $v_brg['kode'] ]) ? $_stok_awal[ $v_gdg['id'] ][ $v_brg['kode'] ] : null;

    //                     if ( !empty($stok_awal) ) {
    //                         $jumlah = 0;
    //                         $total_beli = 0;
    //                         $total_jual = 0;
    //                         foreach ($stok_awal as $k_sa => $v_sa) {
    //                             $jumlah += $v_sa['jumlah'];
    //                             $total_beli += $v_sa['jumlah']*$v_sa['hrg_beli'];
    //                             $total_jual += $v_sa['jumlah']*$v_sa['hrg_jual'];
    //                         }

    //                         $harga_beli = $total_beli / $jumlah;
    //                         $harga_jual = $total_jual / $jumlah;

    //                         if ( $jumlah > 0 ) {
    //                             $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][] = array(
    //                                 'kode' => $v_brg['kode'],
    //                                 'nama' => $v_brg['nama'],
    //                                 'transaksi' => 'STOK AWAL',
    //                                 'tgl_trans' => null,
    //                                 'kode' => '-',
    //                                 'tujuan' => '-',
    //                                 'jumlah' => $jumlah,
    //                                 'hrg_beli' => $harga_beli,
    //                                 'hrg_jual' => $harga_jual,
    //                                 'total_beli' => $total_beli,
    //                                 'total_jual' => $total_jual,
    //                                 'saldo' => $jumlah,
    //                                 'nilai_beli_saldo' => $total_beli,
    //                                 'nilai_jual_saldo' => $total_jual,
    //                             );
    //                         }
    //                     } else {
    //                         $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][] = array(
    //                             'kode' => $v_brg['kode'],
    //                             'nama' => $v_brg['nama'],
    //                             'transaksi' => 'STOK AWAL',
    //                             'tgl_trans' => null,
    //                             'kode' => '-',
    //                             'tujuan' => '-',
    //                             'jumlah' => 0,
    //                             'hrg_beli' => 0,
    //                             'hrg_jual' => 0,
    //                             'total_beli' => 0,
    //                             'total_jual' => 0,
    //                             'saldo' => 0,
    //                             'nilai_beli_saldo' => 0,
    //                             'nilai_jual_saldo' => 0,
    //                         );
    //                     }

    //                     $date = $start_date;
    //                     while ( $date <= $end_date) {
    //                         $data_masuk_retur = isset($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][ $date ]) ? $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][ $date ] : null;
    //                         $data_masuk_beli = isset($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][ $date ]) ? $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][ $date ] : null;
    //                         $data_keluar = isset($_data_keluar[ $v_gdg['id'] ][ $v_brg['kode'] ][ $date ]) ? $_data_keluar[ $v_gdg['id'] ][ $v_brg['kode'] ][ $date ] : null;

    //                         if ( !empty($data_masuk_retur) ) {
    //                             foreach ($data_masuk_retur as $k_dm => $v_dm) {
    //                                 $idx_data_sebelum = count($data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ])-1;
    //                                 $saldo = $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][ $idx_data_sebelum ]['saldo'] + $v_dm['jumlah'];
    //                                 $nilai_beli_saldo = $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][ $idx_data_sebelum ]['nilai_beli_saldo'] + ($v_dm['hrg_beli'] * $v_dm['jumlah']);
    //                                 $nilai_jual_saldo = $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][ $idx_data_sebelum ]['nilai_jual_saldo'] + ($v_dm['hrg_jual'] * $v_dm['jumlah']);

    //                                 $jumlah = $v_dm['jumlah'];
    //                                 $total_beli = ($v_dm['hrg_beli'] * $v_dm['jumlah']);
    //                                 $total_jual = ($v_dm['hrg_jual'] * $v_dm['jumlah']);
    //                                 $harga_beli = ($total_beli > 0 && $jumlah > 0) ? $total_beli / $jumlah : 0;
    //                                 $harga_jual = ($total_jual > 0 && $jumlah > 0) ? $total_jual / $jumlah : 0;

    //                                 $kode = null;
    //                                 $tujuan = null;
    //                                 if ( is_numeric($v_dm['kode_trans']) ) {
    //                                     $m_rp = new \Model\Storage\ReturPakan_model();
    //                                     $d_rp = $m_rp->where('id', $v_dm['kode_trans'])->first();

    //                                     if ( $d_rp ) {
    //                                         $kode = $d_rp->no_retur;
    //                                         if ( $d_rp->tujuan == 'gudang' ) {
    //                                             $m_gdg = new \Model\Storage\Gudang_model();
    //                                             $d_gdg = $m_gdg->where('id', $d_rp->id_tujuan)->first();

    //                                             $tujuan = strtoupper($d_gdg->nama);
    //                                         } else {
    //                                             $m_rs = new \Model\Storage\RdimSubmit_model();
    //                                             $d_rs = $m_rs->where('noreg', $d_rp->id_tujuan)->with(['dMitraMapping'])->first();

    //                                             $tujuan = strtoupper($d_rs->dMitraMapping->dMitra->nama);
    //                                         }
    //                                     }
    //                                 } else {
    //                                     $kode = $v_dm['kode_trans'];

    //                                     $m_kp = new \Model\Storage\KirimPakan_model();
    //                                     $d_kp = $m_kp->where('no_order', $v_dm['kode_trans'])->first();

    //                                     if ( $d_kp ) {
    //                                         if ( $d_kp->jenis_tujuan == 'gudang' ) {
    //                                             $m_gdg = new \Model\Storage\Gudang_model();
    //                                             $d_gdg = $m_gdg->where('id', $d_kp->tujuan)->first();

    //                                             $tujuan = strtoupper($d_gdg->nama);
    //                                         } else {
    //                                             $m_rs = new \Model\Storage\RdimSubmit_model();
    //                                             $d_rs = $m_rs->where('noreg', $d_kp->tujuan)->with(['dMitraMapping'])->first();

    //                                             $tujuan = strtoupper($d_rs->dMitraMapping->dMitra->nama);
    //                                         }
    //                                     }
    //                                 }

    //                                 if ( $jumlah > 0 ) {
    //                                     $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][] = array(
    //                                         'kode' => $v_brg['kode'],
    //                                         'nama' => $v_brg['nama'],
    //                                         'transaksi' => 'MASUK',
    //                                         'tgl_trans' => $v_dm['tgl_trans'],
    //                                         'kode' => $kode,
    //                                         'tujuan' => $tujuan,
    //                                         'jumlah' => $jumlah,
    //                                         'hrg_beli' => $harga_beli,
    //                                         'hrg_jual' => $harga_jual,
    //                                         'total_beli' => $total_beli,
    //                                         'total_jual' => $total_jual,
    //                                         'saldo' => $saldo,
    //                                         'nilai_beli_saldo' => $nilai_beli_saldo,
    //                                         'nilai_jual_saldo' => $nilai_jual_saldo,
    //                                     );
    //                                 }
    //                             }
    //                         }

    //                         if ( !empty($data_masuk_beli) ) {
    //                             foreach ($data_masuk_beli as $k_dm => $v_dm) {
    //                                 $idx_data_sebelum = count($data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ])-1;
    //                                 $saldo = $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][ $idx_data_sebelum ]['saldo'] + $v_dm['jumlah'];
    //                                 $nilai_beli_saldo = $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][ $idx_data_sebelum ]['nilai_beli_saldo'] + ($v_dm['hrg_beli'] * $v_dm['jumlah']);
    //                                 $nilai_jual_saldo = $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][ $idx_data_sebelum ]['nilai_jual_saldo'] + ($v_dm['hrg_jual'] * $v_dm['jumlah']);

    //                                 $jumlah = $v_dm['jumlah'];
    //                                 $total_beli = ($v_dm['hrg_beli'] * $v_dm['jumlah']);
    //                                 $total_jual = ($v_dm['hrg_jual'] * $v_dm['jumlah']);
    //                                 $harga_beli = ($total_beli > 0 && $jumlah > 0) ? $total_beli / $jumlah : 0;
    //                                 $harga_jual = ($total_jual > 0 && $jumlah > 0) ? $total_jual / $jumlah : 0;

    //                                 $kode = null;
    //                                 $tujuan = null;
    //                                 if ( is_numeric($v_dm['kode_trans']) ) {
    //                                     $m_rp = new \Model\Storage\ReturPakan_model();
    //                                     $d_rp = $m_rp->where('id', $v_dm['kode_trans'])->first();

    //                                     if ( $d_rp ) {
    //                                         $kode = $d_rp->no_retur;
    //                                         if ( $d_rp->tujuan == 'gudang' ) {
    //                                             $m_gdg = new \Model\Storage\Gudang_model();
    //                                             $d_gdg = $m_gdg->where('id', $d_rp->id_tujuan)->first();

    //                                             $tujuan = strtoupper($d_gdg->nama);
    //                                         } else {
    //                                             $m_rs = new \Model\Storage\RdimSubmit_model();
    //                                             $d_rs = $m_rs->where('noreg', $d_rp->id_tujuan)->with(['dMitraMapping'])->first();

    //                                             $tujuan = strtoupper($d_rs->dMitraMapping->dMitra->nama);
    //                                         }
    //                                     }
    //                                 } else {
    //                                     $kode = $v_dm['kode_trans'];

    //                                     $m_kp = new \Model\Storage\KirimPakan_model();
    //                                     $d_kp = $m_kp->where('no_order', $v_dm['kode_trans'])->first();

    //                                     if ( $d_kp ) {
    //                                         if ( $d_kp->jenis_tujuan == 'gudang' ) {
    //                                             $m_gdg = new \Model\Storage\Gudang_model();
    //                                             $d_gdg = $m_gdg->where('id', $d_kp->tujuan)->first();

    //                                             $tujuan = strtoupper($d_gdg->nama);
    //                                         } else {
    //                                             $m_rs = new \Model\Storage\RdimSubmit_model();
    //                                             $d_rs = $m_rs->where('noreg', $d_kp->tujuan)->with(['dMitraMapping'])->first();

    //                                             $tujuan = strtoupper($d_rs->dMitraMapping->dMitra->nama);
    //                                         }
    //                                     }
    //                                 }

    //                                 if ( $jumlah > 0 ) {
    //                                     $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][] = array(
    //                                         'kode' => $v_brg['kode'],
    //                                         'nama' => $v_brg['nama'],
    //                                         'transaksi' => 'MASUK',
    //                                         'tgl_trans' => $v_dm['tgl_trans'],
    //                                         'kode' => $kode,
    //                                         'tujuan' => $tujuan,
    //                                         'jumlah' => $jumlah,
    //                                         'hrg_beli' => $harga_beli,
    //                                         'hrg_jual' => $harga_jual,
    //                                         'total_beli' => $total_beli,
    //                                         'total_jual' => $total_jual,
    //                                         'saldo' => $saldo,
    //                                         'nilai_beli_saldo' => $nilai_beli_saldo,
    //                                         'nilai_jual_saldo' => $nilai_jual_saldo,
    //                                     );
    //                                 }
    //                             }
    //                         }

    //                         if ( !empty($data_keluar) ) {
    //                             foreach ($data_keluar as $k_dk => $v_dk) {
    //                                 $idx_data_sebelum = count($data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ])-1;
    //                                 $saldo = $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][ $idx_data_sebelum ]['saldo'] - $v_dk['jumlah'];

    //                                 $nilai_beli_saldo = ($harga_beli > 0) ? $saldo * $harga_beli : $saldo * $v_dk['hrg_beli'];
    //                                 $nilai_jual_saldo = ($harga_jual > 0) ? $v_dk['jumlah'] * $harga_jual : $v_dk['jumlah'] * $v_dk['hrg_jual'];

    //                                 $jumlah = $v_dk['jumlah'];
    //                                 $total_beli = ($saldo < 0) ? (($v_dk['jumlah'] - abs($saldo)) * $v_dk['hrg_beli']) : ($v_dk['hrg_beli'] * $v_dk['jumlah']);
    //                                 $total_jual = ($harga_jual > 0) ? $v_dk['jumlah'] * $harga_jual : $v_dk['jumlah'] * $v_dk['hrg_jual'];
    //                                 $harga_beli = ($nilai_beli_saldo > 0 && $saldo > 0) ? $nilai_beli_saldo / $saldo : 0;
    //                                 $harga_jual = ($nilai_jual_saldo > 0 && $v_dk['jumlah'] > 0) ? $nilai_jual_saldo / $v_dk['jumlah'] : 0;
                                    
    //                                 $kode = null;
    //                                 $tujuan = null;
    //                                 if ( is_numeric($v_dk['kode_trans']) ) {
    //                                     $m_rp = new \Model\Storage\ReturPakan_model();
    //                                     $d_rp = $m_rp->where('id', $v_dk['kode_trans'])->first();

    //                                     if ( $d_rp ) {
    //                                         $kode = $d_rp->no_retur;
    //                                         if ( $d_rp->tujuan == 'gudang' ) {
    //                                             $m_gdg = new \Model\Storage\Gudang_model();
    //                                             $d_gdg = $m_gdg->where('id', $d_rp->id_tujuan)->first();

    //                                             $tujuan = strtoupper($d_gdg->nama);
    //                                         } else {
    //                                             $m_rs = new \Model\Storage\RdimSubmit_model();
    //                                             $d_rs = $m_rs->where('noreg', $d_rp->id_tujuan)->with(['dMitraMapping'])->first();

    //                                             $tujuan = strtoupper($d_rs->dMitraMapping->dMitra->nama);
    //                                         }
    //                                     }
    //                                 } else {
    //                                     $kode = $v_dk['kode_trans'];

    //                                     $m_kp = new \Model\Storage\KirimPakan_model();
    //                                     $d_kp = $m_kp->where('no_order', $v_dk['kode_trans'])->first();

    //                                     if ( $d_kp ) {
    //                                         if ( $d_kp->jenis_tujuan == 'gudang' ) {
    //                                             $m_gdg = new \Model\Storage\Gudang_model();
    //                                             $d_gdg = $m_gdg->where('id', $d_kp->tujuan)->first();

    //                                             $tujuan = strtoupper($d_gdg->nama);
    //                                         } else {
    //                                             $m_rs = new \Model\Storage\RdimSubmit_model();
    //                                             $d_rs = $m_rs->where('noreg', $d_kp->tujuan)->with(['dMitraMapping'])->first();

    //                                             $tujuan = strtoupper($d_rs->dMitraMapping->dMitra->nama);
    //                                         }
    //                                     }
    //                                 }

    //                                 if ( $jumlah > 0 ) {
    //                                     $data[ $v_gdg['id'] ]['barang'][ $v_brg['kode'] ][] = array(
    //                                         'kode' => $v_brg['kode'],
    //                                         'nama' => $v_brg['nama'],
    //                                         'transaksi' => 'KELUAR',
    //                                         'tgl_trans' => $v_dk['tgl_trans'],
    //                                         'kode' => $kode,
    //                                         'tujuan' => $tujuan,
    //                                         'jumlah' => $jumlah,
    //                                         'hrg_beli' => $harga_beli,
    //                                         'hrg_jual' => $harga_jual,
    //                                         'total_beli' => $total_beli,
    //                                         'total_jual' => $total_jual,
    //                                         'saldo' => $saldo,
    //                                         'nilai_beli_saldo' => $nilai_beli_saldo,
    //                                         'nilai_jual_saldo' => $nilai_jual_saldo,
    //                                     );
    //                                 }
    //                             }
    //                         }

    //                         $date = next_date($date);
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     return $data;
    // }

    public function get_data_voadip($start_date, $end_date, $kode_gudang, $kode_brg, $jenis)
    {
        $data = null;

        $m_stok = new \Model\Storage\Stok_model();
        $d_stok = $m_stok->whereBetween('periode', [$start_date, $end_date])->orderBy('periode', 'asc')->get();

        $data = null;
        if ( $d_stok->count() > 0 ) {
            $data = $d_stok->toArray();
        }

        $mappingDataReport = $this->mappingDataReport( $data, $kode_brg, $kode_gudang, $jenis );

        return $mappingDataReport;
    }

    public function get_data_pakan($start_date, $end_date, $kode_gudang, $kode_brg, $jenis)
    {
        $data = null;

        $m_stok = new \Model\Storage\Stok_model();
        $d_stok = $m_stok->whereBetween('periode', [$start_date, $end_date])->orderBy('periode', 'asc')->get();

        $data = null;
        if ( $d_stok->count() > 0 ) {
            $data = $d_stok->toArray();
        }

        $mappingDataReport = $this->mappingDataReport( $data, $kode_brg, $kode_gudang, $jenis );

        return $mappingDataReport;
    }

    public function mappingDataReport($_data, $_kode_brg, $_kode_gudang, $_jenis)
    {
        // $kode_brg = array();
        // if ( !empty( $_kode_brg ) ) {
        //     if ( stristr($_kode_brg, 'all') !== FALSE ) {
        //         $m_brg = new \Model\Storage\Barang_model();
        //         $d_brg = $m_brg->where('tipe', 'like', '%'.$_jenis.'%')->orderBy('nama', 'asc')->get()->toArray();

        //         foreach ($d_brg as $k_brg => $v_brg) {
        //             $kode_brg[ $_kode_brg['kode'] ] = trim($v_brg['kode']);
        //         }
        //     } else {
        //         $m_brg = new \Model\Storage\Barang_model();
        //         $d_brg = $m_brg->where('kode', $_kode_brg)->where('tipe', 'like', '%'.$_jenis.'%')->orderBy('nama', 'asc')->get()->toArray();

        //         foreach ($d_brg as $k_brg => $_kode_brg) {
        //             $kode_brg[ $_kode_brg['kode'] ] = trim($_kode_brg['kode']);
        //         }
        //     }
        // }
        $sql_brg = null;
        if ( stristr($_kode_brg, 'all') === FALSE ) {
            $sql_brg = "and ds.kode_barang in ('".$_kode_brg."')";
        }

        // $kode_gdg = array();
        // if ( !empty( $_kode_gudang ) ) {
        //     if ( stristr($_kode_gudang, 'all') !== FALSE ) {
        //         $m_gdg = new \Model\Storage\Gudang_model();
        //         $d_gdg = $m_gdg->where('jenis', 'like', '%'.$_jenis.'%')->orderBy('nama', 'asc')->get()->toArray();

        //         foreach ($d_gdg as $k_gdg => $v_gdg) {
        //             $kode_gdg[] = trim($v_gdg['id']);
        //         }
        //     } else {
        //         $m_gdg = new \Model\Storage\Gudang_model();
        //         $d_gdg = $m_gdg->where('id', $_kode_gudang)->where('jenis', 'like', '%'.$_jenis.'%')->orderBy('nama', 'asc')->get()->toArray();

        //         foreach ($d_gdg as $k_gdg => $v_gdg) {
        //             $kode_gdg[] = trim($v_gdg['id']);
        //         }
        //     }
        // }
        $sql_gudang = null;
        if ( stristr($_kode_gudang, 'all') === FALSE ) {
            $sql_gudang = "and ds.kode_gudang in (".$_kode_gudang.")";
        }

        $data = null;
        if ( !empty($_data) ) {
            // cetak_r( $_data );
            foreach ($_data as $k_data => $v_data) {
                // $gdg = implode(',', $kode_gdg);

                if ( $_jenis == 'obat') {
                    $_jenis = 'voadip';
                }

                $sql_adjin = "
                    UNION ALL
                                
                    select 
                        cast(ai.kode_gudang as varchar(10)) as tujuan,
                        ai.kode as no_order,
                        'ORDER' as jenis_trans,
                        'ADJUSTMENT IN' as nama_jenis_trans
                    from adjin_".$_jenis." ai
                ";

                $m_ds = new \Model\Storage\DetStok_model();
                $sql = "
                    select 
                        ds.id_header as id_header,
                        ds.kode_barang as kode_barang,
                        ds.kode_gudang as kode_gudang,
                        b.nama as nama_barang,
                        b.desimal_harga as decimal,
                        g.nama as nama_gudang,
                        ds.hrg_beli as harga_beli,
                        ds.hrg_jual as harga_jual,
                        ds.tgl_trans as tgl_trans,
                        ds.kode_trans as kode_trans,
                        (cast(sum(ds.jml_stok) as float) + cast(case when sum(dst.jumlah) is null then 0 else sum(dst.jumlah) end as float)) as jumlah,
                        kirim.nama_jenis_trans as jenis_trans,
                        dari.nama as dari
                    from det_stok ds
                    left join 
                        (select id_header, kode_barang, sum(jumlah) as jumlah from det_stok_trans group by id_header, kode_barang) dst
                        on
                            ds.id = dst.id_header
                    left join
                        (select b2.* from barang b2 
                        right join  
                            (select max(b.id) as id, b.kode from barang b group by b.kode) b3
                            on
                                b2.id = b3.id) b
                        on
                            ds.kode_barang = b.kode
                    left join
                        gudang g
                        on
                            ds.kode_gudang = g.id
                    left join
                        (
                            select * from (
                                select 
                                    case
                                        when k.jenis_kirim = 'opkg' then
                                            case
                                                when k.jenis_tujuan <> 'peternak' then
                                                    k.asal
                                                else
                                                    rs.nim
                                            end
                                        else
                                            case
                                                when k.jenis_tujuan <> 'peternak' then
                                                    case
                                                        when k.jenis_tujuan = 'gudang' then
                                                            k.asal
                                                        else
                                                            k.tujuan
                                                    end
                                                else
                                                    rs.nim
                                            end
                                    end as tujuan,
                                    k.no_order,
                                    case
                                        when k.jenis_kirim = 'opkg' then
                                            case
                                                when k.jenis_tujuan <> 'peternak' then
                                                    case
                                                        when k.jenis_tujuan = 'gudang' then
                                                            'ORDER'
                                                        else
                                                            'RETUR'
                                                    end
                                                else
                                                    'ORDER'
                                            end
                                        else
                                            'ORDER'
                                    end as jenis_trans,
                                    case
                                        when k.jenis_kirim = 'opkg' then
                                            case
                                                when k.jenis_tujuan <> 'peternak' then
                                                    'PINDAH'
                                                else
                                                    'ORDER'
                                            end
                                        else
                                            'ORDER'
                                    end as nama_jenis_trans
                                from kirim_".$_jenis." k
                                left join
                                    rdim_submit rs 
                                    on
                                        k.tujuan = rs.noreg
                                left join
                                    gudang g 
                                    on
                                        k.tujuan = g.id 
                                        
                                UNION ALL
                                
                                select 
                                    case
                                        when r.asal <> 'peternak' then
                                            r.id_asal
                                        else
                                            rs.nim          
                                    end as tujuan,
                                    r.no_order,
                                    'RETUR' as jenis_trans,
                                    'RETUR' as nama_jenis_trans
                                from retur_".$_jenis." r
                                left join
                                    rdim_submit rs 
                                    on
                                        r.id_asal = rs.noreg
                                left join
                                    gudang g 
                                    on
                                        r.id_asal = g.id

                                ".$sql_adjin."
                            ) as data
                            group by
                                data.tujuan,
                                data.no_order,
                                data.jenis_trans,
                                data.nama_jenis_trans
                        ) as kirim
                        on
                            kirim.no_order = ds.kode_trans and
                            kirim.jenis_trans = ds.jenis_trans
                    left join
                        (
                            select * from (
                                select cast(mm.nim as varchar(15)) as id, m.nama as nama from mitra m
                                right join
                                    (
                                        select max(id) as id, nomor from mitra 
                                        group by
                                            nomor
                                    ) as group_mitra
                                    on
                                        m.id = group_mitra.id
                                right join
                                    mitra_mapping mm 
                                    on
                                        m.nomor = mm.nomor
                                group by
                                    m.nama, mm.nim
                                    
                                UNION ALL
                                    
                                select cast(g.id as varchar(15)) as id, g.nama as nama from gudang g 
                                    
                                UNION ALL
                                
                                select cast(p.nomor as varchar(15)) as id, max(p.nama) as nama from pelanggan p
                                left join
                                    (
                                        select max(id) as id, nomor from pelanggan
                                        group by
                                            nomor
                                    ) as group_pelanggan
                                    on
                                        p.id = group_pelanggan.id
                                where
                                    p.tipe = 'supplier' and
                                    p.jenis <> 'ekspedisi'
                                group by
                                    p.nomor
                            ) as supplier
                        ) as dari
                        on
                            dari.id = cast(kirim.tujuan as varchar(15))
                    where
                        ds.id_header = ".$v_data['id']." and
                        ds.jml_stok is not null and
                        (ds.tgl_trans >= g.tgl_stok_opaname or g.tgl_stok_opaname is null)
                        ".$sql_brg."
                        ".$sql_gudang."
                    group by
                        ds.id_header,
                        ds.kode_barang,
                        ds.kode_gudang,
                        b.nama,
                        b.desimal_harga,
                        g.nama,
                        ds.hrg_beli,
                        ds.hrg_jual,
                        ds.tgl_trans,
                        ds.kode_trans,
                        ds.jenis_trans,
                        kirim.nama_jenis_trans,
                        dari.nama,
                        g.tgl_stok_opaname
                    order by
                        ds.tgl_trans asc,
                        ds.jenis_trans desc
                ";

                $d_ds = $m_ds->hydrateRaw( $sql );

                if ( $d_ds->count() > 0 ) {
                    $d_ds = $d_ds->toArray();

                    // cetak_r($d_ds, 1);

                    foreach ($d_ds as $k_ds => $v_ds) {
                        // if ( trim($v_ds['kode_barang']) == 'OB2109012' && $v_ds['kode_trans'] == 'OP/LMJ/22/09044' ) {
                        //     cetak_r( $v_ds );
                        // }

                        // if ( in_array(trim($v_ds['kode_barang']), $kode_brg) ) {
                            $data[ $v_ds['kode_gudang'] ]['kode'] = $v_ds['kode_gudang'];
                            $data[ $v_ds['kode_gudang'] ]['nama'] = $v_ds['nama_gudang'];

                            $key_brg = $v_ds['nama_barang'].' | '.$v_ds['kode_barang'];

                            $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['kode'] = $v_ds['kode_barang'];
                            $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['nama'] = $v_ds['nama_barang'];

                            $key_masuk = str_replace('-', '', $v_ds['tgl_trans']).'-'.$v_ds['kode_trans'].'-'.$v_ds['harga_beli'].'-'.$v_ds['harga_jual'];

                            if ( !isset($data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]) ) {
                                
                                $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['kode'] = $v_ds['kode_trans'];
                                $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['dari'] = $v_ds['dari'];
                                $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['tgl_trans'] = $v_ds['tgl_trans'];
                                $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['masuk'] = $v_ds['jumlah'];
                                $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['keluar'] = 0;
                                $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['stok_akhir'] = $v_ds['jumlah'];
                                $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['harga_beli'] = $v_ds['harga_beli'];
                                $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['nilai_beli'] = ($v_ds['jumlah'] * $v_ds['harga_beli']);
                                $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['harga_jual'] = $v_ds['harga_jual'];
                                $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['decimal'] = $v_ds['decimal'];
                                $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['nilai_jual'] = ($v_ds['jumlah'] * $v_ds['harga_jual']);
                                $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['jenis_trans'] = $v_ds['jenis_trans'];
                            } 
                            // else {
                            //     $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['masuk'] += $v_ds['jumlah'];
                            //     $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['stok_akhir'] += $v_ds['jumlah'];
                            //     $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['nilai_beli'] += ($v_ds['jumlah'] * $v_ds['harga_beli']);
                            //     $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['nilai_jual'] += ($v_ds['jumlah'] * $v_ds['harga_jual']);
                            // }

                            ksort( $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk']);

                            $harga_beli = ($v_ds['harga_beli'] > 0) ? $v_ds['harga_beli'] : 0;

                            $m_dst = new \Model\Storage\DetStokTrans_model();
                            $sql = "
                                select 
                                    dst.id as id,
                                    dst.kode_barang as kode_barang,
                                    b.nama as nama_barang,
                                    b.desimal_harga as decimal,
                                    dst.kode_trans as kode_trans,
                                    dst.jumlah as jumlah,
                                    ke.nama as ke,
                                    kirim.jenis_trans
                                from det_stok_trans dst
                                left join
                                    det_stok ds
                                    on
                                        dst.id_header = ds.id
                                left join
                                    (
                                        select b1.* from barang b1 
                                        right join  
                                            (select max(id) as id, kode from barang group by kode) b2
                                            on
                                                b1.id = b2.id
                                    ) b
                                    on
                                        dst.kode_barang = b.kode
                                left join
                                    (
                                        select * from (
                                            select 
                                                case
                                                    when k.jenis_tujuan <> 'peternak' then
                                                        case
                                                            when k.jenis_tujuan = 'gudang' then
                                                                k.tujuan
                                                            else
                                                                k.asal
                                                        end
                                                    else
                                                        rs.nim
                                                end as tujuan,
                                                k.no_order,
                                                'ORDER' as jenis_trans
                                            from kirim_".$_jenis." k
                                            left join
                                                rdim_submit rs 
                                                on
                                                    k.tujuan = rs.noreg
                                            left join
                                                gudang g 
                                                on
                                                    k.tujuan = g.id 
                                                    
                                            UNION ALL
                                            
                                            select 
                                                r.id_tujuan as tujuan,
                                                /* case
                                                    when r.asal <> 'peternak' then
                                                        r.id_tujuan
                                                    else
                                                        rs.nim          
                                                end as tujuan,
                                                */
                                                r.no_order,
                                                'RETUR' as jenis_trans
                                            from retur_".$_jenis." r
                                            where
                                                r.asal = 'gudang'
                                            /* left join
                                                rdim_submit rs 
                                                on
                                                    r.id_asal = rs.noreg
                                            left join
                                                gudang g 
                                                on
                                                    r.id_asal = g.id
                                            */

                                            UNION ALL
                                
                                            select 
                                                adjout.kode as tujuan,
                                                adjout.kode as no_order,
                                                'ADJOUT' as jenis_trans
                                            from adjout_".$_jenis." adjout
                                        ) as data
                                    ) as kirim
                                    on
                                        kirim.no_order = dst.kode_trans
                                left join
                                    (
                                        select * from (
                                            select cast(mm.nim as varchar(15)) as id, m.nama as nama from mitra m
                                            right join
                                                (
                                                    select max(id) as id, nomor from mitra 
                                                    group by
                                                        nomor
                                                ) as group_mitra
                                                on
                                                    m.id = group_mitra.id
                                            right join
                                                mitra_mapping mm 
                                                on
                                                    m.nomor = mm.nomor
                                            group by
                                                m.nama, mm.nim
                                                
                                            UNION ALL
                                                
                                            select cast(g.id as varchar(15)) as id, g.nama as nama from gudang g 
                                                
                                            UNION ALL
                                            
                                            select cast(p.nomor as varchar(15)) as id, p.nama as nama from pelanggan p
                                            right join
                                                (
                                                    select max(id) as id, nomor from pelanggan
                                                    where
                                                        tipe = 'supplier' and
                                                        jenis <> 'ekspedisi'
                                                    group by
                                                        nomor
                                                ) as group_pelanggan
                                                on
                                                    p.id = group_pelanggan.id

                                            UNION ALL
                                
                                            select 
                                                cast(adjout.kode as varchar(15)) as id,
                                                cast(adjout.keterangan as varchar(max)) as nama
                                            from adjout_".$_jenis." adjout
                                        ) as supplier
                                    ) as ke
                                    on
                                        ke.id = cast(kirim.tujuan as varchar(15))
                                where
                                    ds.id_header = ".$v_ds['id_header']." and
                                    ds.tgl_trans = '".$v_ds['tgl_trans']."' and
                                    ds.kode_trans = '".$v_ds['kode_trans']."' and
                                    ds.hrg_beli = ".$harga_beli."
                                    -- ".$sql_brg."
                                    and ds.kode_barang = '".$v_ds['kode_barang']."'
                                order by
                                    dst.kode_trans asc,
                                    dst.id asc

                            ";

                            $d_dst = $m_dst->hydrateRaw( $sql );

                            if ( $d_dst->count() > 0 ) {
                                $d_dst = $d_dst->toArray();

                                foreach ($d_dst as $k_dst => $v_dst) {
                                    // if ( stristr($key_brg, 'OB2109007') !== false && $v_dst['kode_trans'] == 'OP/KDR/22/10014' ) {
                                    //     cetak_r( $key_brg );
                                    //     cetak_r( $v_dst );
                                    // }

                                    // if ( stristr($key_brg, $v_dst['kode_barang']) !== false ) {
                                        $tanggal = $v_data['periode'];
                                        // $m_conf = new \Model\Storage\Conf();

                                        // $tbl_name = $v_dst['tbl_name'];
                                        // $column_name = 'kode_'.$tbl_name;

                                        // $kode_trans = $v_dst['kode_trans'];

                                        // $sql = "
                                        //     select * from $tbl_name where $column_name = '$kode_trans'
                                        // ";

                                        // $d_conf = $m_conf->hydrateRaw($sql);
                                        // if ( $d_conf->count() > 0 ) {
                                        //     $d_trans = $d_conf->toArray();

                                        //     foreach ($d_trans as $k_trans => $v_trans) {
                                        //         $column_name = 'tgl_'.$tbl_name;

                                        //         $tanggal = $v_trans[$column_name];
                                        //     }
                                        // }

                                        $key_keluar = str_replace('-', '', $tanggal).'-'.$v_dst['id'];

                                        $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $tanggal ]['keluar'][ $key_keluar ]['kode'] = $v_dst['kode_trans'];
                                        $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $tanggal ]['keluar'][ $key_keluar ]['ke'] = $v_dst['ke'];
                                        $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $tanggal ]['keluar'][ $key_keluar ]['tgl_trans'] = $tanggal;
                                        $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $tanggal ]['keluar'][ $key_keluar ]['tgl_stok'] = $v_ds['tgl_trans'];
                                        $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $tanggal ]['keluar'][ $key_keluar ]['masuk'] = 0;
                                        $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $tanggal ]['keluar'][ $key_keluar ]['keluar'] = $v_dst['jumlah'];
                                        $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $tanggal ]['keluar'][ $key_keluar ]['stok_akhir'] = $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['masuk'] - $v_dst['jumlah'];
                                        $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $tanggal ]['keluar'][ $key_keluar ]['harga_beli'] = $v_ds['harga_beli'];
                                        $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $tanggal ]['keluar'][ $key_keluar ]['nilai_beli'] = ($v_dst['jumlah'] * $v_ds['harga_beli']);
                                        $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $tanggal ]['keluar'][ $key_keluar ]['harga_jual'] = $v_ds['harga_jual'];
                                        $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $tanggal ]['keluar'][ $key_keluar ]['decimal'] = $v_ds['decimal'];
                                        $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $tanggal ]['keluar'][ $key_keluar ]['nilai_jual'] = ($v_dst['jumlah'] * $v_ds['harga_jual']);
                                        $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $tanggal ]['keluar'][ $key_keluar ]['jenis_trans'] = $v_dst['jenis_trans'];

                                        ksort( $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $tanggal ]['keluar']);
                                    // }
                                }

                                // if ( isset($data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk']) && !empty($data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk']) ) {
                                //     ksort( $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk']);
                                // }

                                // if ( isset($data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['keluar']) && !empty($data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['keluar']) ) {
                                //     ksort( $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['keluar']);
                                // }
                            }

                            ksort( $data[ $v_ds['kode_gudang'] ]['detail'] );
                            ksort( $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'] );
                        // }
                    }
                }
            }
        }

        return $data;
    }

    public function get_data()
    {
        $params = $this->input->post('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $jenis = $params['jenis'];
        $kode_gudang = $params['kode_gudang'];
        $kode_brg = $params['kode_brg'];

        // $start_date = '2021-05-01';
        // $end_date = '2021-05-31';
        // $jenis = 'pakan';
        // $kode_gudang = 'all';
        // $kode_brg = 'all';
        // $kode_brg = 'OB1907001';

        $data = null;
        if ( $jenis == 'obat' ) {
            $data = $this->get_data_voadip($start_date, $end_date, $kode_gudang, $kode_brg, $jenis);
        } else {
            $data = $this->get_data_pakan($start_date, $end_date, $kode_gudang, $kode_brg, $jenis);
        }

        cetak_r( $data, 1 );

        $content['data'] = $data;
        $html = $this->load->view('report/mutasi_stok/list', $content, TRUE);

        $this->result['html'] = $html;

        display_json( $this->result );
    }
}