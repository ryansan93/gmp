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

    public function get_data_barang_masuk_voadip($periode, $next_periode)
    {
        $m_stok = new \Model\Storage\Stok_model();

        $_d_stok = $m_stok->where('periode', '<', $next_periode)->orderBy('periode', 'desc')->first();
        $d_stok = null;
        $d_stok = $m_stok->where('periode', $periode)->first();
        if ( !$d_stok ) {
            $d_stok = $_d_stok;
        }

        $tgl_terakhir = next_date(date("Y-m-t", strtotime($next_periode)));

        $data = null;
        $data_retur = array();
        $data_beli = array();
        $d_rv = null;
        $d_kv = null;
        if ( !$_d_stok ) {
            $m_rv = new \Model\Storage\ReturVoadip_model();
            $d_rv = $m_rv->where('tujuan', 'gudang')->where('tgl_retur', '<', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->orderBy('id', 'asc')->get();

            $m_kv = new \Model\Storage\KirimVoadip_model();
            $d_kv = $m_kv->where('jenis_tujuan', 'gudang')->where('tgl_kirim', '<', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->orderBy('id', 'asc')->get();

        } else {
            // if ( $d_stok ) {
                $time = strtotime($d_stok->periode);
                $final = date("Y-m-d", strtotime("+1 month", $time));

                $m_rv = new \Model\Storage\ReturVoadip_model();
                $d_rv = $m_rv->where('tujuan', 'gudang')->where('tgl_retur', '>=', $final)->where('tgl_retur', '<', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->orderBy('id', 'asc')->get();

                $m_kv = new \Model\Storage\KirimVoadip_model();
                $d_kv = $m_kv->where('jenis_tujuan', 'gudang')->where('tgl_kirim', '>=', $final)->where('tgl_kirim', '<', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->orderBy('id', 'asc')->get();
            // } else {
            //     $m_rv = new \Model\Storage\ReturVoadip_model();
            //     $d_rv = $m_rv->where('tujuan', 'gudang')->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('id', 'asc')->orderBy('tgl_retur', 'asc')->get();

            //     $m_kv = new \Model\Storage\KirimVoadip_model();
            //     $d_kv = $m_kv->where('jenis_tujuan', 'gudang')->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('id', 'asc')->orderBy('tgl_kirim', 'asc')->get();
            // }
        }

        // STOK AWAL
        if ( $d_stok ) {
            $d_stok = $d_stok->toArray();

            $m_dstok = new \Model\Storage\DetStok_model();
            $d_dstok = $m_dstok->where('id_header', $d_stok['id'])->where('jenis_barang', 'voadip')->orderBy('tgl_trans', 'asc')->get()->toArray();

            foreach ($d_dstok as $k_det => $v_det) {
                $isi_retur = 1;
                if ( isset($data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]) ) {
                    foreach ($data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ] as $k => $val) {
                        if ( $val['kode_trans'] == $v_det['kode_trans'] ) {
                            $isi_retur = 0;
                        }
                    }
                }

                $isi_beli = 1;
                if ( isset($data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]) ) {
                    foreach ($data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ] as $k => $val) {
                        if ( $val['kode_trans'] == $v_det['kode_trans'] ) {
                            $isi_beli = 0;
                        }
                    }
                }

                if ( $isi_retur == 1 ) {
                    if ( $v_det['jenis_trans'] == 'RETUR' ) {
                        // if ( isset($data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][0]) ) {
                        //     $data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][0]['jumlah'] += $v_det['jumlah'];
                        // } else {
                        if ( $v_det['jumlah'] > 0 ) {
                            $data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][] = array(
                                'tgl_trans' => $v_det['tgl_trans'],
                                'kode_gudang' => $v_det['kode_gudang'],
                                'kode_barang' => $v_det['kode_barang'],
                                'jumlah' => $v_det['jumlah'],
                                'hrg_jual' => $v_det['hrg_jual'],
                                'hrg_beli' => $v_det['hrg_beli'],
                                'kode_trans' => $v_det['kode_trans'],
                                'dari' => $v_det['jenis_trans']
                            );
                        }
                        // }

                        // ksort($data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]);
                    }
                }

                if ( $isi_beli == 1 ) {
                    if ( $v_det['jenis_trans'] != 'RETUR' ) {
                        // if ( isset($data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][0]) ) {
                        //     $data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][0]['jumlah'] += $v_det['jumlah'];
                        // } else {
                        if ( $v_det['jumlah'] > 0 ) {
                            $data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][] = array(
                                'tgl_trans' => $v_det['tgl_trans'],
                                'kode_gudang' => $v_det['kode_gudang'],
                                'kode_barang' => $v_det['kode_barang'],
                                'jumlah' => $v_det['jumlah'],
                                'hrg_jual' => $v_det['hrg_jual'],
                                'hrg_beli' => $v_det['hrg_beli'],
                                'kode_trans' => $v_det['kode_trans'],
                                'dari' => $v_det['jenis_trans']
                            );
                        }
                        // }

                        // ksort($data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]);
                    }
                }
            }
        }

        // RETUR
        if ( $d_rv->count() > 0 ) {
            $d_rv = $d_rv->toArray();
            foreach ($d_rv as $k_rv => $v_rv) {
                $m_drv = new \Model\Storage\DetReturVoadip_model();
                $d_drv = $m_drv->where('id_header', $v_rv['id'])->get()->toArray();
                foreach ($d_drv as $k_det => $v_det) {
                    $m_kv = new \Model\Storage\KirimVoadip_model();
                    $_d_kv = $m_kv->where('no_order', $v_rv['no_order'])->orderBy('id', 'desc')->first();

                    $_d_kvd = null;
                    if ( $_d_kv ) {
                        $m_kvd = new \Model\Storage\KirimVoadipDetail_model();
                        $_d_kvd = $m_kvd->where('id_header', $_d_kv->id)->where('item', $v_det['item'])->orderBy('id', 'desc')->get();               
                    }

                    $isi = 1;
                    if ( isset($data_retur[ $v_rv['id_tujuan'] ][ $v_det['item'] ]) ) {
                        foreach ($data_retur[ $v_rv['id_tujuan'] ][ $v_det['item'] ] as $k => $val) {
                            if ( $val['kode_trans'] == $v_rv['id'] ) {
                                $isi = 0;
                            }
                        }
                    }

                    if ( $isi == 1 ) {
                        if ( !empty($_d_kvd) && $_d_kvd->count() > 0 ) {
                            $_d_kvd = $_d_kvd->toArray();
                            foreach ($_d_kvd as $k_kvd => $v_kvd) {
                                $harga_beli = ($v_kvd['nilai_beli'] > 0 && $v_kvd['jumlah'] > 0) ? $v_kvd['nilai_beli']/$v_kvd['jumlah'] : 0;
                                $harga_jual = ($v_kvd['nilai_jual'] > 0 && $v_kvd['jumlah'] > 0) ? $v_kvd['nilai_jual']/$v_kvd['jumlah'] : 0;

                                // if ( isset($data_retur[ $v_rv['id_tujuan'] ][ $v_det['item'] ][0]) ) {
                                //     $data_retur[ $v_rv['id_tujuan'] ][ $v_det['item'] ][0]['jumlah'] += $v_det['jumlah'];
                                // } else {
                                if ( $v_det['jumlah'] > 0 ) {
                                    $data_retur[ $v_rv['id_tujuan'] ][ $v_det['item'] ][] = array(
                                        'tgl_trans' => $v_rv['tgl_retur'],
                                        'kode_gudang' => $v_rv['id_tujuan'],
                                        'kode_barang' => $v_det['item'],
                                        'jumlah' => $v_det['jumlah'],
                                        'hrg_jual' => $harga_jual,
                                        'hrg_beli' => $harga_beli,
                                        'kode_trans' => $v_rv['id'],
                                        'dari' => 'RETUR'
                                    );
                                }
                                // }

                                $m_drv = new \Model\Storage\DetReturVoadip_model();
                                $m_drv->where('id', $v_det['id'])->update(
                                    array(
                                        'nilai_beli' => $v_det['jumlah']*$harga_beli,
                                        'nilai_jual' => $v_det['jumlah']*$harga_jual
                                    )
                                );

                                // ksort($data_retur[ $v_rv['id_tujuan'] ][ $v_det['item'] ]);
                            }
                        }
                    }
                }
            }
        }

        // ORDER
        if ( $d_kv->count() > 0 ) {
            $d_kv = $d_kv->toArray();

            foreach ($d_kv as $k_kv => $v_kv) {
                $m_tv = new \Model\Storage\TerimaVoadip_model();
                $d_tv = $m_tv->where('id_kirim_voadip', $v_kv['id'])->orderBy('id', 'desc')->first();

                $harga_beli = 0;
                $harga_jual = 0;

                $m_dkv = new \Model\Storage\KirimVoadipDetail_model();
                $d_dkv = $m_dkv->where('id_header', $v_kv['id'])->get()->toArray();
                foreach ($d_dkv as $k_det => $v_det) {
                    $jumlah_terima = 0;
                    if ( $d_tv ) {
                        $m_dtv = new \Model\Storage\TerimaVoadipDetail_model();
                        $jumlah_terima = $m_dtv->where('id_header', $d_tv->id)->where('item', $v_det['item'])->sum('jumlah');
                    }

                    $m_ov = new \Model\Storage\OrderVoadip_model();
                    $d_ov = $m_ov->where('no_order', $v_kv['no_order'])->orderBy('id', 'desc')->first();

                    $d_ovd = null;
                    if ( $d_ov ) {
                        $m_ovd = new \Model\Storage\OrderVoadipDetail_model();
                        $d_ovd = $m_ovd->where('id_order', $d_ov->id)->where('kode_barang', $v_det['item'])->orderBy('id', 'desc')->first();
                    }

                    $isi = 1;
                    if ( isset($data_beli[ $v_kv['tujuan'] ][ $v_det['item'] ]) ) {
                        foreach ($data_beli[ $v_kv['tujuan'] ][ $v_det['item'] ] as $k => $val) {
                            if ( $val['kode_trans'] == $v_kv['no_order'] ) {
                                $isi = 0;
                            }
                        }
                    }

                    if ( !empty($d_ovd) ) {
                        $d_ovd = $d_ovd->toArray();
                        $harga_beli = $d_ovd['harga'];
                        $harga_jual = $d_ovd['harga_jual'];
                    } else {
                        $harga_beli = ($v_det['jumlah'] > 0 && $v_det['nilai_beli'] > 0) ? $v_det['nilai_beli'] / $v_det['jumlah'] : 0;
                        $harga_jual = ($v_det['jumlah'] > 0 && $v_det['nilai_jual'] > 0) ? $v_det['nilai_jual'] / $v_det['jumlah'] : 0;
                    }

                    if ( $isi == 1 ) {                    
                        // if ( isset($data_beli[ $v_kv['tujuan'] ][ $v_det['item'] ][0]) ) {
                        //     $data_beli[ $v_kv['tujuan'] ][ $v_det['item'] ][0]['jumlah'] += $v_det['jumlah'];
                        // } else {
                        if ( $v_det['jumlah'] > 0 ) {
                            $data_beli[ $v_kv['tujuan'] ][ $v_det['item'] ][] = array(
                                'tgl_trans' => $v_kv['tgl_kirim'],
                                'kode_gudang' => $v_kv['tujuan'],
                                'kode_barang' => $v_det['item'],
                                'jumlah' => $jumlah_terima,
                                'hrg_jual' => $harga_jual,
                                'hrg_beli' => $harga_beli,
                                'kode_trans' => $v_kv['no_order'],
                                'dari' => 'ORDER'
                            );
                        }
                        // }

                        // ksort($data_beli[ $v_kv['tujuan'] ][ $v_det['item'] ]);
                    }

                    $m_dkv = new \Model\Storage\KirimVoadipDetail_model();
                    $m_dkv->where('id', $v_det['id'])->update(
                        array(
                            'nilai_beli' => $v_det['jumlah']*$harga_beli,
                            'nilai_jual' => $v_det['jumlah']*$harga_jual
                        )
                    );
                }
            }
        }

        $data = array(
            'retur' => $data_retur,
            'beli' => $data_beli
        );

        return $data;
    }

    public function get_data_barang_keluar_voadip($periode, $next_periode)
    {
        $m_stok = new \Model\Storage\Stok_model();

        $_d_stok = $m_stok->where('periode', '<', $next_periode)->with(['det_stok'])->orderBy('periode', 'desc')->first();
        $d_stok = null;
        $d_stok = $m_stok->where('periode', $periode)->with(['det_stok'])->first();
        if ( !$d_stok ) {
            $d_stok = $_d_stok;
        }

        $tgl_terakhir = date("Y-m-t", strtotime($next_periode));

        $data = null;
        $d_rv = null;
        $d_kv = null;
        if ( !$_d_stok ) {
            $m_rv = new \Model\Storage\ReturVoadip_model();
            $d_rv = $m_rv->where('asal', 'gudang')->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->orderBy('id', 'asc')->get();

            $m_kv = new \Model\Storage\KirimVoadip_model();
            $d_kv = $m_kv->where('jenis_kirim', 'opkg')->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->orderBy('id', 'asc')->get();
        } else {
            // if ( $d_stok ) {
                $time = strtotime($d_stok->periode);
                $final = date("Y-m-d", strtotime("+1 month", $time));

                $m_rv = new \Model\Storage\ReturVoadip_model();
                $d_rv = $m_rv->where('asal', 'gudang')->where('tgl_retur', '>=', $final)->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->orderBy('id', 'asc')->get();

                $m_kv = new \Model\Storage\KirimVoadip_model();
                $d_kv = $m_kv->where('jenis_kirim', 'opkg')->where('tgl_kirim', '>=', $final)->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->orderBy('id', 'asc')->get();
            // } else {
            //     $m_rv = new \Model\Storage\ReturVoadip_model();
            //     $d_rv = $m_rv->where('asal', 'gudang')->where('tujuan', 'supplier')->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->orderBy('id', 'asc')->get();

            //     $m_kv = new \Model\Storage\KirimVoadip_model();
            //     $d_kv = $m_kv->where('jenis_kirim', 'opkg')->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->orderBy('id', 'asc')->get();
            // }
        }

        // RETUR
        if ( $d_rv->count() > 0 ) {
            $d_rv = $d_rv->toArray();
            foreach ($d_rv as $k_rv => $v_rv) {
                $m_drv = new \Model\Storage\DetReturVoadip_model();
                $d_drv = $m_drv->where('id_header', $v_rv['id'])->orderBy('id', 'asc')->get()->toArray();
                foreach ($d_drv as $k_det => $v_det) {
                    $data[ $v_rv['id_asal'] ][ $v_det['item'] ][] = array(
                        'key' => $v_det['id'],
                        'tgl_trans' => $v_rv['tgl_retur'],
                        'kode_gudang' => $v_rv['id_asal'],
                        'kode_barang' => $v_det['item'],
                        'jumlah' => $v_det['jumlah'],
                        'kode_trans' => $v_rv['no_order'],
                        'dari' => 'RETUR'
                    );
                }
            }
        }

        // ORDER
        if ( $d_kv->count() > 0 ) {
            $d_kv = $d_kv->toArray();
            foreach ($d_kv as $k_kv => $v_kv) {
                $m_tv = new \Model\Storage\TerimaVoadip_model();
                $d_tv = $m_tv->where('id_kirim_voadip', $v_kv['id'])->orderBy('id', 'desc')->first();

                $m_dkv = new \Model\Storage\KirimVoadipDetail_model();
                $d_dkv = $m_dkv->where('id_header', $v_kv['id'])->orderBy('id', 'asc')->get()->toArray();
                foreach ($d_dkv as $k_det => $v_det) {
                    $jumlah_terima = 0;
                    if ( $d_tv ) {
                        $m_dtv = new \Model\Storage\TerimaVoadipDetail_model();
                        $jumlah_terima = $m_dtv->where('id_header', $d_tv->id)->where('item', $v_det['item'])->sum('jumlah');

                        // if ( $v_kv['no_order'] == 'OP/JBR/22/03145' && $v_det['item'] ) {
                        //     cetak_r( $d_tv, 1 );
                        // }
                    }

                    $data[ $v_kv['asal'] ][ $v_det['item'] ][] = array(
                        'key' => $v_det['id'],
                        'tgl_trans' => $v_kv['tgl_kirim'],
                        'kode_gudang' => $v_kv['asal'],
                        'kode_barang' => $v_det['item'],
                        'jumlah' => $jumlah_terima,
                        'kode_trans' => $v_kv['no_order'],
                        'dari' => 'ORDER'
                    );
                }
            }
        }

        return $data;
    }

    public function hitung_stok_voadip($periode, $next_periode)
    {
        $_data_masuk = $this->get_data_barang_masuk_voadip( $periode, $next_periode );
        $_data_keluar = $this->get_data_barang_keluar_voadip( $periode, $next_periode );

        // cetak_r( $_data_keluar, 1 );

        $m_gdg = new \Model\Storage\Gudang_model();
        $d_gdg = $m_gdg->where('jenis', 'OBAT')->get();

        $data = array();
        if ( $d_gdg->count() > 0 ) {
            $d_gdg = $d_gdg->toArray();
            foreach ($d_gdg as $k_gdg => $v_gdg) {
                $m_barang = new \Model\Storage\Barang_model();
                $d_barang = $m_barang->where('tipe', 'obat')->orderBy('kode', 'asc')->get();
                if ( $d_barang->count() > 0 ) {
                    $d_barang = $d_barang->toArray();
                    foreach ($d_barang as $k_brg => $v_brg) {
                        $data_keluar = isset($_data_keluar[ $v_gdg['id'] ][ $v_brg['kode'] ]) ? $_data_keluar[ $v_gdg['id'] ][ $v_brg['kode'] ] : null;

                        $data_masuk_retur = isset($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ]) ? $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ] : null;
                        $data_masuk_beli = isset($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ]) ? $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ] : null;

                        // if ( $v_gdg['id'] == 10 && $v_brg['kode'] == 'OB2109035' ) {
                        //     cetak_r( $data_masuk_retur );
                        //     cetak_r( $data_masuk_beli, 1 );
                        // }

                        if ( !empty($data_keluar) ) {
                            $jml_masuk = 0;
                            $jml_keluar = 0;
                            $idx_masuk_retur = 0;
                            $idx_masuk_beli = 0;
                            foreach ($data_keluar as $k_dk => $v_dk) {
                                $total_nilai_keluar_beli = 0;
                                $total_nilai_keluar_jual = 0;
                                $total_jml_keluar = 0;
                                $jml_keluar = $v_dk['jumlah'];
                                $tgl_keluar = $v_dk['tgl_trans'];

                                // if ( !empty($_data_masuk) ) {
                                if ( !empty($data_masuk_retur) ) {
                                    $_jml_keluar = $jml_keluar;
                                    while ($_jml_keluar > 0) {
                                        if ( isset($data_masuk_retur[$idx_masuk_retur]) ) {
                                            $hrg_beli = $data_masuk_retur[$idx_masuk_retur]['hrg_beli'];
                                            $hrg_jual = $data_masuk_retur[$idx_masuk_retur]['hrg_jual'];
                                            // $_hrg_jual = $hrg_beli + ($hrg_beli * 5/100);
                                            // $sisa_bagi = fmod($_hrg_jual, 50);
                                            // $hrg_jual = $_hrg_jual - $sisa_bagi;

                                            // if ( $v_gdg['id'] == 6 && $v_brg['kode'] == 'OB2109062' ) {
                                            //     if ( $v_dk['kode_trans'] == 'OP/JBR/22/03145' ) {
                                            //         cetak_r( $data_masuk_beli[$idx_masuk_beli]['kode_trans'].'|'.$hrg_beli.'|'.$hrg_jual );
                                            //         cetak_r( $data_masuk_beli[$idx_masuk_beli]['tgl_trans'].'<='.$tgl_keluar );
                                            //         if ( $_jml_keluar == 0 ) {
                                            //             cetak_r( $data_masuk_beli[$idx_masuk_beli]['jumlah'].' >= '.$jml_keluar );
                                            //         } else {
                                            //             cetak_r( $data_masuk_beli[$idx_masuk_beli]['jumlah'].' >= '.$jml_keluar );
                                            //         }
                                            //     }
                                            // }

                                            if ( $data_masuk_retur[$idx_masuk_retur]['tgl_trans'] <= $tgl_keluar ) {

                                                if ( $data_masuk_retur[$idx_masuk_retur]['jumlah'] >= $jml_keluar ) {
                                                    $data_masuk_retur[$idx_masuk_retur]['jumlah'] = $data_masuk_retur[$idx_masuk_retur]['jumlah'] - $jml_keluar;

                                                    $total_nilai_keluar_beli += $jml_keluar * $hrg_beli;
                                                    $total_nilai_keluar_jual += $jml_keluar * $hrg_jual;

                                                    $total_jml_keluar += $jml_keluar;

                                                    $jml_keluar = 0;
                                                    $_jml_keluar = $jml_keluar;
                                                } else {
                                                    $total_nilai_keluar_beli += $data_masuk_retur[$idx_masuk_retur]['jumlah'] * $hrg_beli;
                                                    $total_nilai_keluar_jual += $data_masuk_retur[$idx_masuk_retur]['jumlah'] * $hrg_jual;

                                                    $total_jml_keluar += $data_masuk_retur[$idx_masuk_retur]['jumlah'];

                                                    $jml_keluar = $jml_keluar - $data_masuk_retur[$idx_masuk_retur]['jumlah'];
                                                    $_jml_keluar = $jml_keluar;

                                                    $data_masuk_retur[$idx_masuk_retur]['jumlah'] = 0;

                                                    $idx_masuk_retur++;
                                                }
                                            } else {
                                                $_jml_keluar = 0;
                                            }
                                        } else {
                                            $_jml_keluar = 0;
                                        }
                                    }

                                    // $_jml_keluar = $jml_keluar;
                                    // while ($_jml_keluar > 0) {
                                    //     if ( isset($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]) ) {
                                    //         if ( $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['tgl_trans'] <= $tgl_keluar ) {

                                    //             $hrg_beli = $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['hrg_beli'];
                                    //             $hrg_jual = $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['hrg_jual'];

                                    //             if ( $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] >= $jml_keluar ) {
                                    //                 $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] = $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] - $jml_keluar;

                                    //                 $total_nilai_keluar_beli += $jml_keluar * $hrg_beli;
                                    //                 $total_nilai_keluar_jual += $jml_keluar * $hrg_jual;

                                    //                 $jml_keluar = 0;
                                    //                 $_jml_keluar = $jml_keluar;
                                    //             } else {
                                    //                 if ( $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] > 0 && isset($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur+1]) ) {

                                    //                     $jml_keluar = $jml_keluar - $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'];
                                    //                     $_jml_keluar = $jml_keluar;

                                    //                     $total_nilai_keluar_beli += $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] * $hrg_beli;
                                    //                     $total_nilai_keluar_jual += $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] * $hrg_jual;
                                    //                     $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] = 0;
                                    //                 } else {
                                    //                     if ( $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] > $jml_keluar ) {
                                    //                         $total_nilai_keluar_beli = $jml_keluar * $hrg_beli;
                                    //                         $total_nilai_keluar_jual = $jml_keluar * $hrg_jual;

                                    //                         // // $jml_keluar = 0;
                                    //                         // $jml_keluar = $jml_keluar - $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'];
                                    //                     } else {
                                    //                         $total_nilai_keluar_beli =  $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] * $hrg_beli;
                                    //                         $total_nilai_keluar_jual =  $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] * $hrg_jual;             
                                    //                     }

                                    //                     $jml_keluar = $jml_keluar - $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'];

                                    //                     // if ( $v_gdg['id'] == 26 && $v_brg['kode'] == 'OB2109055' ) {
                                    //                     //     cetak_r( 'RETUR : '.$_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'].' | '.$jml_keluar );
                                    //                     // }

                                    //                     $idx_masuk_retur++;
                                    //                 }

                                    //                 // if ( $v_gdg['id'] == 26 && $v_brg['kode'] == 'OB2109055' ) {
                                    //                 //     cetak_r( 'RETUR : '.$_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'].' | '.$jml_keluar );
                                    //                 // }

                                    //                 $idx_masuk_retur++;
                                    //             }
                                    //         } else {
                                    //             if ( !isset($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]) ) {
                                    //                 $total_nilai_keluar_beli = 0;
                                    //                 $total_nilai_keluar_jual = 0;
                                    //             }

                                    //             $idx_masuk_retur++;
                                    //         }
                                    //     } else {
                                    //         $_jml_keluar = 0;
                                    //     }
                                    // }
                                }

                                if ( !empty($data_masuk_beli) ) {
                                    $_jml_keluar = $jml_keluar;
                                    while ($_jml_keluar > 0) {
                                        if ( isset($data_masuk_beli[$idx_masuk_beli]) ) {
                                            $hrg_beli = $data_masuk_beli[$idx_masuk_beli]['hrg_beli'];
                                            $hrg_jual = $data_masuk_beli[$idx_masuk_beli]['hrg_jual'];
                                            // $_hrg_jual = $hrg_beli + ($hrg_beli * 5/100);
                                            // $sisa_bagi = fmod($_hrg_jual, 50);
                                            // $hrg_jual = $_hrg_jual - $sisa_bagi;

                                            // if ( $v_gdg['id'] == 6 && $v_brg['kode'] == 'OB2109062' ) {
                                            //     if ( $v_dk['kode_trans'] == 'OP/JBR/22/03145' ) {
                                            //         cetak_r( $data_masuk_beli[$idx_masuk_beli]['kode_trans'].'|'.$hrg_beli.'|'.$hrg_jual );
                                            //         cetak_r( $data_masuk_beli[$idx_masuk_beli]['tgl_trans'].'<='.$tgl_keluar );
                                            //         if ( $_jml_keluar == 0 ) {
                                            //             cetak_r( $data_masuk_beli[$idx_masuk_beli]['jumlah'].' >= '.$jml_keluar );
                                            //         } else {
                                            //             cetak_r( $data_masuk_beli[$idx_masuk_beli]['jumlah'].' >= '.$jml_keluar );
                                            //         }
                                            //     }
                                            // }

                                            if ( $data_masuk_beli[$idx_masuk_beli]['tgl_trans'] <= $tgl_keluar ) {

                                                if ( $data_masuk_beli[$idx_masuk_beli]['jumlah'] >= $jml_keluar ) {
                                                    $data_masuk_beli[$idx_masuk_beli]['jumlah'] = $data_masuk_beli[$idx_masuk_beli]['jumlah'] - $jml_keluar;

                                                    $total_nilai_keluar_beli += $jml_keluar * $hrg_beli;
                                                    $total_nilai_keluar_jual += $jml_keluar * $hrg_jual;

                                                    // if ( $v_gdg['id'] == 35 && $v_brg['kode'] == 'OB2109012' ) {
                                                    //     if ( $v_dk['kode_trans'] == 'OP/BYL/22/03004' ) {
                                                    //         cetak_r( $total_nilai_keluar_beli.' | '.$total_nilai_keluar_jual, 1 );
                                                    //     }
                                                    // }

                                                    $total_jml_keluar += $jml_keluar;

                                                    $jml_keluar = 0;
                                                    $_jml_keluar = $jml_keluar;
                                                } else {
                                                    $total_nilai_keluar_beli += $data_masuk_beli[$idx_masuk_beli]['jumlah'] * $hrg_beli;
                                                    $total_nilai_keluar_jual += $data_masuk_beli[$idx_masuk_beli]['jumlah'] * $hrg_jual;

                                                    $total_jml_keluar += $data_masuk_beli[$idx_masuk_beli]['jumlah'];

                                                    $jml_keluar = $jml_keluar - $data_masuk_beli[$idx_masuk_beli]['jumlah'];
                                                    $_jml_keluar = $jml_keluar;

                                                    $data_masuk_beli[$idx_masuk_beli]['jumlah'] = 0;

                                                    $idx_masuk_beli++;
                                                }
                                            } else {
                                                $_jml_keluar = 0;
                                            }
                                        } else {
                                            $_jml_keluar = 0;
                                        }
                                    }

                                    // $_jml_keluar = $jml_keluar;
                                    // while ($_jml_keluar > 0) {
                                    //     if ( isset($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]) ) {

                                    //         if ( $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['tgl_trans'] <= $tgl_keluar ) {
                                    //             $hrg_beli = $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['hrg_beli'];
                                    //             $hrg_jual = $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['hrg_jual'];

                                    //             if ( $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] >= $jml_keluar ) {

                                    //                 $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] = $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] - $jml_keluar;

                                    //                 $total_nilai_keluar_beli += $jml_keluar * $hrg_beli;
                                    //                 $total_nilai_keluar_jual += $jml_keluar * $hrg_jual;

                                    //                 $jml_keluar = 0;
                                    //                 $_jml_keluar = $jml_keluar;
                                    //             } else {
                                    //                 if ( $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] > 0 && isset($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli+1]) ) {

                                    //                     $jml_keluar = $jml_keluar - $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'];
                                    //                     $_jml_keluar = $jml_keluar;

                                    //                     $total_nilai_keluar_beli += $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] * $hrg_beli;
                                    //                     $total_nilai_keluar_jual += $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] * $hrg_jual;
                                    //                     $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] = 0;
                                    //                 } else {
                                    //                     if ( $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] > $jml_keluar ) {
                                    //                         $total_nilai_keluar_beli = $jml_keluar * $hrg_beli;
                                    //                         $total_nilai_keluar_jual = $jml_keluar * $hrg_jual;

                                    //                         $jml_keluar = $jml_keluar - $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'];
                                    //                         // $jml_keluar = 0;
                                    //                     } else {
                                    //                         $total_nilai_keluar_beli =  $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] * $hrg_beli;
                                    //                         $total_nilai_keluar_jual =  $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] * $hrg_jual;

                                    //                     }


                                    //                     $_jml_keluar = 0;
                                    //                 }

                                    //                 // if ( $v_gdg['id'] == 26 && $v_brg['kode'] == 'OB2109055' ) {
                                    //                 //     cetak_r( 'BELI : '.$_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'].' | '.$jml_keluar );
                                    //                 // }

                                    //                 $idx_masuk_beli++;
                                    //             }
                                    //         } else {
                                    //             if ( !isset($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]) ) {
                                    //                 $total_nilai_keluar_beli = 0;
                                    //                 $total_nilai_keluar_jual = 0;
                                    //             }

                                    //             $idx_masuk_retur++;
                                    //         }
                                    //     } else {
                                    //         $_jml_keluar = 0;
                                    //     }
                                    // }
                                }

                                $_harga_beli = ($total_nilai_keluar_beli > 0 && $total_jml_keluar > 0) ? $total_nilai_keluar_beli / $total_jml_keluar : 0;
                                $_harga_jual = ($total_nilai_keluar_jual > 0 && $total_jml_keluar > 0) ? $total_nilai_keluar_jual / $total_jml_keluar : 0;

                                if ( $v_dk['dari'] == 'RETUR' ) {
                                    $m_drv = new \Model\Storage\DetReturVoadip_model();
                                    $d_drv = $m_drv->where('id', $v_dk['key'])->first();

                                    $m_drv->where('id', $v_dk['key'])->update(
                                        array(
                                            'nilai_beli' => $d_drv->jumlah * $_harga_beli,
                                            'nilai_jual' => $d_drv->jumlah * $_harga_jual
                                        )
                                    );
                                }

                                if ( $v_dk['dari'] == 'ORDER' ) {
                                    $m_dkv = new \Model\Storage\KirimVoadipDetail_model();
                                    $d_dkv = $m_dkv->where('id', $v_dk['key'])->first();

                                    // if ( $v_dk['kode_trans'] == 'OP/BYL/22/03004' ) {
                                    //     cetak_r($d_dkv);
                                    //     cetak_r($_harga_beli);
                                    //     cetak_r($_harga_jual, 1);
                                    // }

                                    $m_dkv->where('id', $v_dk['key'])->update(
                                        array(
                                            'nilai_beli' => $d_dkv->jumlah * $_harga_beli,
                                            'nilai_jual' => $d_dkv->jumlah * $_harga_jual
                                        )
                                    );
                                }
                                // }
                            }
                        }

                        if ( !empty($data_masuk_retur) ) {
                            foreach ($data_masuk_retur as $k_dm => $val) {
                                if ( $val['jumlah'] > 0 ) {
                                    $data[ $v_gdg['id'] ][ $v_brg['kode'] ][ $val['kode_trans'] ] = array(
                                        'tgl_trans' => $val['tgl_trans'],
                                        'kode_gudang' => $val['kode_gudang'],
                                        'kode_barang' => $val['kode_barang'],
                                        'jumlah' => $val['jumlah'],
                                        'hrg_jual' => $val['hrg_jual'],
                                        'hrg_beli' => $val['hrg_beli'],
                                        'kode_trans' => $val['kode_trans'],
                                        'dari' => $val['dari'],
                                    );

                                    ksort( $data );
                                }
                            }
                        }
                        if ( !empty($data_masuk_beli) ) {
                            foreach ($data_masuk_beli as $k_dm => $val) {
                                if ( $val['jumlah'] > 0 ) {
                                    $data[ $v_gdg['id'] ][ $v_brg['kode'] ][ $val['kode_trans'] ] = array(
                                        'tgl_trans' => $val['tgl_trans'],
                                        'kode_gudang' => $val['kode_gudang'],
                                        'kode_barang' => $val['kode_barang'],
                                        'jumlah' => $val['jumlah'],
                                        'hrg_jual' => $val['hrg_jual'],
                                        'hrg_beli' => $val['hrg_beli'],
                                        'kode_trans' => $val['kode_trans'],
                                        'dari' => $val['dari'],
                                    );

                                    ksort( $data );
                                }
                            }
                        }

                        // if ( $v_gdg['id'] == 26 && $v_brg['kode'] == 'OB2109055' ) {
                        //     cetak_r( $data[ $v_gdg['id'] ][ $v_brg['kode'] ], 1 );
                        // }
                    }
                }
            }
        }

        return $data;
    }

    public function get_data_barang_masuk_pakan($periode, $next_periode)
    {
        $m_stok = new \Model\Storage\Stok_model();

        $_d_stok = $m_stok->where('periode', '<', $next_periode)->orderBy('periode', 'desc')->first();
        $d_stok = null;
        $d_stok = $m_stok->where('periode', $periode)->first();
        if ( !$d_stok ) {
            $d_stok = $_d_stok;
        }

        $tgl_terakhir = next_date(date("Y-m-t", strtotime($next_periode)));

        $data = null;
        $data_retur = array();
        $data_beli = array();
        $d_rp = null;
        $d_kp = null;
        if ( !$_d_stok ) {
            $m_rp = new \Model\Storage\ReturPakan_model();
            $d_rp = $m_rp->where('tujuan', 'gudang')->where('tgl_retur', '<', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->orderBy('id', 'asc')->get();

            $m_kp = new \Model\Storage\KirimPakan_model();
            $d_kp = $m_kp->where('jenis_tujuan', 'gudang')->where('tgl_kirim', '<', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->orderBy('id', 'asc')->get();

        } else {
            // if ( $d_stok ) {
                $time = strtotime($d_stok->periode);
                $final = date("Y-m-d", strtotime("+1 month", $time));

                $m_rp = new \Model\Storage\ReturPakan_model();
                $d_rp = $m_rp->where('tujuan', 'gudang')->where('tgl_retur', '>=', $final)->where('tgl_retur', '<', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->orderBy('id', 'asc')->get();

                $m_kp = new \Model\Storage\KirimPakan_model();
                $d_kp = $m_kp->where('jenis_tujuan', 'gudang')->where('tgl_kirim', '>=', $final)->where('tgl_kirim', '<', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->orderBy('id', 'asc')->get();
            // } else {
            //     $m_rp = new \Model\Storage\ReturPakan_model();
            //     $d_rp = $m_rp->where('tujuan', 'gudang')->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('id', 'asc')->orderBy('tgl_retur', 'asc')->get();

            //     $m_kp = new \Model\Storage\KirimPakan_model();
            //     $d_kp = $m_kp->where('jenis_tujuan', 'gudang')->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('id', 'asc')->orderBy('tgl_kirim', 'asc')->get();
            // }
        }

        // STOK AWAL
        if ( $d_stok ) {
            $d_stok = $d_stok->toArray();

            $m_dstok = new \Model\Storage\DetStok_model();
            $d_dstok = $m_dstok->where('id_header', $d_stok['id'])->where('jenis_barang', 'pakan')->orderBy('tgl_trans', 'asc')->get()->toArray();

            foreach ($d_dstok as $k_det => $v_det) {
                $isi_retur = 1;
                if ( isset($data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]) ) {
                    foreach ($data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ] as $k => $val) {
                        if ( $val['kode_trans'] == $v_det['kode_trans'] ) {
                            $isi_retur = 0;
                        }
                    }
                }

                $isi_beli = 1;
                if ( isset($data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]) ) {
                    foreach ($data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ] as $k => $val) {
                        if ( $val['kode_trans'] == $v_det['kode_trans'] ) {
                            $isi_beli = 0;
                        }
                    }
                }

                if ( $isi_retur == 1 ) {
                    if ( $v_det['jenis_trans'] == 'RETUR' ) {
                        // if ( isset($data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][0]) ) {
                        //     $data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][0]['jumlah'] += $v_det['jumlah'];
                        // } else {
                        if ( $v_det['jumlah'] > 0 ) {
                            $data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][] = array(
                                'tgl_trans' => $v_det['tgl_trans'],
                                'kode_gudang' => $v_det['kode_gudang'],
                                'kode_barang' => $v_det['kode_barang'],
                                'jumlah' => $v_det['jumlah'],
                                'hrg_jual' => $v_det['hrg_jual'],
                                'hrg_beli' => $v_det['hrg_beli'],
                                'kode_trans' => $v_det['kode_trans'],
                                'dari' => $v_det['jenis_trans']
                            );
                        }
                        // }

                        // ksort($data_retur[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]);
                    }
                }

                if ( $isi_beli == 1 ) {
                    if ( $v_det['jenis_trans'] != 'RETUR' ) {
                        // if ( isset($data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][0]) ) {
                        //     $data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][0]['jumlah'] += $v_det['jumlah'];
                        // } else {
                        if ( $v_det['jumlah'] > 0 ) {
                            $data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ][] = array(
                                'tgl_trans' => $v_det['tgl_trans'],
                                'kode_gudang' => $v_det['kode_gudang'],
                                'kode_barang' => $v_det['kode_barang'],
                                'jumlah' => $v_det['jumlah'],
                                'hrg_jual' => $v_det['hrg_jual'],
                                'hrg_beli' => $v_det['hrg_beli'],
                                'kode_trans' => $v_det['kode_trans'],
                                'dari' => $v_det['jenis_trans']
                            );
                        }
                        // }

                        // ksort($data_beli[ $v_det['kode_gudang'] ][ $v_det['kode_barang'] ]);
                    }
                }
            }
        }

        // RETUR
        if ( $d_rp->count() > 0 ) {
            $d_rp = $d_rp->toArray();
            foreach ($d_rp as $k_rp => $v_rp) {
                $m_drp = new \Model\Storage\DetReturPakan_model();
                $d_drp = $m_drp->where('id_header', $v_rp['id'])->get()->toArray();
                foreach ($d_drp as $k_det => $v_det) {
                    $m_kp = new \Model\Storage\KirimPakan_model();
                    $_d_kp = $m_kp->where('no_order', $v_rp['no_order'])->orderBy('id', 'desc')->first();

                    $_d_kpd = null;
                    if ( $_d_kp ) {
                        $m_kpd = new \Model\Storage\KirimPakanDetail_model();
                        $_d_kpd = $m_kpd->where('id_header', $_d_kp->id)->where('item', $v_det['item'])->orderBy('id', 'desc')->get();               
                    }

                    $isi = 1;
                    if ( isset($data_retur[ $v_rp['id_tujuan'] ][ $v_det['item'] ]) ) {
                        foreach ($data_retur[ $v_rp['id_tujuan'] ][ $v_det['item'] ] as $k => $val) {
                            if ( $val['kode_trans'] == $v_rp['id'] ) {
                                $isi = 0;
                            }
                        }
                    }

                    if ( $isi == 1 ) {
                        if ( !empty($_d_kpd) && $_d_kpd->count() > 0 ) {
                            $_d_kpd = $_d_kpd->toArray();
                            foreach ($_d_kpd as $k_kpd => $v_kpd) {
                                $harga_beli = $v_kpd['nilai_beli']/$v_kpd['jumlah'];
                                $harga_jual = $v_kpd['nilai_jual']/$v_kpd['jumlah'];

                                // if ( isset($data_retur[ $v_rp['id_tujuan'] ][ $v_det['item'] ][0]) ) {
                                //     $data_retur[ $v_rp['id_tujuan'] ][ $v_det['item'] ][0]['jumlah'] += $v_det['jumlah'];
                                // } else {
                                if ( $v_det['jumlah'] > 0 ) {
                                    $data_retur[ $v_rp['id_tujuan'] ][ $v_det['item'] ][] = array(
                                        'tgl_trans' => $v_rp['tgl_retur'],
                                        'kode_gudang' => $v_rp['id_tujuan'],
                                        'kode_barang' => $v_det['item'],
                                        'jumlah' => $v_det['jumlah'],
                                        'hrg_jual' => $harga_jual,
                                        'hrg_beli' => $harga_beli,
                                        'kode_trans' => $v_rp['id'],
                                        'dari' => 'RETUR'
                                    );
                                }
                                // }

                                $m_drp = new \Model\Storage\DetReturPakan_model();
                                $m_drp->where('id', $v_det['id'])->update(
                                    array(
                                        'nilai_beli' => $v_det['jumlah']*$harga_beli,
                                        'nilai_jual' => $v_det['jumlah']*$harga_jual
                                    )
                                );

                                // ksort($data_retur[ $v_rp['id_tujuan'] ][ $v_det['item'] ]);
                            }
                        }
                    }
                }
            }
        }

        // ORDER
        if ( $d_kp->count() > 0 ) {
            $d_kp = $d_kp->toArray();

            foreach ($d_kp as $k_kp => $v_kp) {
                $m_tp = new \Model\Storage\TerimaPakan_model();
                $d_tp = $m_tp->where('id_kirim_pakan', $v_kp['id'])->orderBy('id', 'desc')->first();
                
                $m_dkp = new \Model\Storage\KirimPakanDetail_model();
                $d_dkp = $m_dkp->where('id_header', $v_kp['id'])->get()->toArray();
                foreach ($d_dkp as $k_det => $v_det) {
                    $jumlah_terima = 0;
                    if ( $d_tp ) {
                        $m_dtp = new \Model\Storage\TerimaPakanDetail_model();
                        $jumlah_terima = $m_dtp->where('id_header', $d_tp->id)->where('item', $v_det['item'])->sum('jumlah');
                    }

                    $m_op = new \Model\Storage\OrderPakan_model();
                    $d_op = $m_op->where('no_order', $v_kp['no_order'])->orderBy('id', 'desc')->first();

                    $d_opd = null;
                    if ( $d_op ) {
                        $m_opd = new \Model\Storage\OrderPakanDetail_model();
                        $d_opd = $m_opd->where('id_header', $d_op->id)->where('barang', $v_det['item'])->orderBy('id', 'desc')->first();
                    }

                    $isi = 1;
                    if ( isset($data_beli[ $v_kp['tujuan'] ][ $v_det['item'] ]) ) {
                        foreach ($data_beli[ $v_kp['tujuan'] ][ $v_det['item'] ] as $k => $val) {
                            if ( $val['kode_trans'] == $v_kp['no_order'] ) {
                                $isi = 0;
                            }
                        }
                    }

                    if ( $isi == 1 ) {
                        $harga_beli = 0;
                        $harga_jual = 0;
                        if ( !empty($d_opd) ) {
                            $d_opd = $d_opd->toArray();
                            $harga_beli = $d_opd['harga'];
                            $harga_jual = $d_opd['harga'];
                        } else {
                            $harga_beli = ($v_det['jumlah'] > 0 && $v_det['nilai_beli'] > 0) ? $v_det['nilai_beli'] / $v_det['jumlah'] : 0;
                            $harga_jual = ($v_det['jumlah'] > 0 && $v_det['nilai_jual'] > 0) ? $v_det['nilai_jual'] / $v_det['jumlah'] : 0;
                        }

                        // if ( isset($data_beli[ $v_kp['tujuan'] ][ $v_det['item'] ][0]) ) {
                        //     $data_beli[ $v_kp['tujuan'] ][ $v_det['item'] ][0]['jumlah'] += $v_det['jumlah'];
                        // } else {
                        if ( $v_det['jumlah'] > 0 ) {
                            $data_beli[ $v_kp['tujuan'] ][ $v_det['item'] ][] = array(
                                'tgl_trans' => $v_kp['tgl_kirim'],
                                'kode_gudang' => $v_kp['tujuan'],
                                'kode_barang' => $v_det['item'],
                                'jumlah' => $jumlah_terima,
                                'hrg_jual' => $harga_jual,
                                'hrg_beli' => $harga_beli,
                                'kode_trans' => $v_kp['no_order'],
                                'dari' => 'ORDER'
                            );
                        }
                        // }

                        $m_dkp = new \Model\Storage\KirimPakanDetail_model();
                        $m_dkp->where('id', $v_det['id'])->update(
                            array(
                                'nilai_beli' => $v_det['jumlah']*$harga_beli,
                                'nilai_jual' => $v_det['jumlah']*$harga_jual
                            )
                        );

                        // ksort($data_beli[ $v_kp['tujuan'] ][ $v_det['item'] ]);
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

    public function get_data_barang_keluar_pakan($periode, $next_periode)
    {
        $m_stok = new \Model\Storage\Stok_model();

        $_d_stok = $m_stok->where('periode', '<', $next_periode)->with(['det_stok'])->orderBy('periode', 'desc')->first();
        $d_stok = null;
        $d_stok = $m_stok->where('periode', $periode)->with(['det_stok'])->first();
        if ( !$d_stok ) {
            $d_stok = $_d_stok;
        }

        $tgl_terakhir = date("Y-m-t", strtotime($next_periode));

        $data = null;
        $d_rp = null;
        $d_kp = null;
        if ( !$_d_stok ) {
            $m_rp = new \Model\Storage\ReturPakan_model();
            $d_rp = $m_rp->where('asal', 'gudang')->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->orderBy('id', 'asc')->get();

            $m_kp = new \Model\Storage\KirimPakan_model();
            $d_kp = $m_kp->where('jenis_kirim', 'opkg')->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->orderBy('id', 'asc')->get();
        } else {
            // if ( $d_stok ) {
                $time = strtotime($d_stok->periode);
                $final = date("Y-m-d", strtotime("+1 month", $time));

                $m_rp = new \Model\Storage\ReturPakan_model();
                $d_rp = $m_rp->where('asal', 'gudang')->where('tgl_retur', '>=', $final)->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->orderBy('id', 'asc')->get();

                $m_kp = new \Model\Storage\KirimPakan_model();
                $d_kp = $m_kp->where('jenis_kirim', 'opkg')->where('tgl_kirim', '>=', $final)->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->orderBy('id', 'asc')->get();
            // } else {
            //     $m_rp = new \Model\Storage\ReturPakan_model();
            //     $d_rp = $m_rp->where('asal', 'gudang')->where('tujuan', 'supplier')->where('tgl_retur', '<=', $tgl_terakhir)->orderBy('tgl_retur', 'asc')->orderBy('id', 'asc')->get();

            //     $m_kp = new \Model\Storage\KirimPakan_model();
            //     $d_kp = $m_kp->where('jenis_kirim', 'opkg')->where('tgl_kirim', '<=', $tgl_terakhir)->orderBy('tgl_kirim', 'asc')->orderBy('id', 'asc')->get();
            // }
        }

        // RETUR
        if ( $d_rp->count() > 0 ) {
            $d_rp = $d_rp->toArray();
            foreach ($d_rp as $k_rp => $v_rp) {
                $m_drv = new \Model\Storage\DetReturPakan_model();
                $d_drv = $m_drv->where('id_header', $v_rp['id'])->orderBy('id', 'asc')->get()->toArray();
                foreach ($d_drv as $k_det => $v_det) {
                    $data[ $v_rp['id_asal'] ][ $v_det['item'] ][] = array(
                        'key' => $v_det['id'],
                        'tgl_trans' => $v_rp['tgl_retur'],
                        'kode_gudang' => $v_rp['id_asal'],
                        'kode_barang' => $v_det['item'],
                        'jumlah' => $v_det['jumlah'],
                        'kode_trans' => $v_rp['no_order'],
                        'dari' => 'RETUR'
                    );
                }
            }
        }

        // ORDER
        if ( $d_kp->count() > 0 ) {
            $d_kp = $d_kp->toArray();
            foreach ($d_kp as $k_kp => $v_kp) {
                $m_tp = new \Model\Storage\TerimaPakan_model();
                $d_tp = $m_tp->where('id_kirim_pakan', $v_kp['id'])->orderBy('id', 'desc')->first();

                $m_dkv = new \Model\Storage\KirimPakanDetail_model();
                $d_dkv = $m_dkv->where('id_header', $v_kp['id'])->orderBy('id', 'asc')->get()->toArray();
                foreach ($d_dkv as $k_det => $v_det) {
                    $jumlah_terima = 0;
                    if ( $d_tp ) {
                        $m_dtp = new \Model\Storage\TerimaPakanDetail_model();
                        $jumlah_terima = $m_dtp->where('id_header', $d_tp->id)->where('item', $v_det['item'])->sum('jumlah');
                    }

                    $data[ $v_kp['asal'] ][ $v_det['item'] ][] = array(
                        'key' => $v_det['id'],
                        'tgl_trans' => $v_kp['tgl_kirim'],
                        'kode_gudang' => $v_kp['asal'],
                        'kode_barang' => $v_det['item'],
                        'jumlah' => $jumlah_terima,
                        'kode_trans' => $v_kp['no_order'],
                        'dari' => 'ORDER'
                    );
                }
            }
        }

        return $data;
    }

    public function hitung_stok_pakan($periode, $next_periode)
    {
        $_data_masuk = $this->get_data_barang_masuk_pakan( $periode, $next_periode );
        $_data_keluar = $this->get_data_barang_keluar_pakan( $periode, $next_periode );

        $m_gdg = new \Model\Storage\Gudang_model();
        $d_gdg = $m_gdg->where('jenis', 'PAKAN')->get();

        $data = array();
        if ( $d_gdg->count() > 0 ) {
            $d_gdg = $d_gdg->toArray();
            foreach ($d_gdg as $k_gdg => $v_gdg) {
                $m_barang = new \Model\Storage\Barang_model();
                $d_barang = $m_barang->where('tipe', 'pakan')->orderBy('kode', 'asc')->get();
                if ( $d_barang->count() > 0 ) {
                    $d_barang = $d_barang->toArray();
                    foreach ($d_barang as $k_brg => $v_brg) {
                        $data_keluar = isset($_data_keluar[ $v_gdg['id'] ][ $v_brg['kode'] ]) ? $_data_keluar[ $v_gdg['id'] ][ $v_brg['kode'] ] : null;

                        $data_masuk_retur = isset($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ]) ? $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ] : null;
                        $data_masuk_beli = isset($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ]) ? $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ] : null;

                        if ( !empty($data_keluar) ) {
                            $jml_masuk = 0;
                            $jml_keluar = 0;
                            $idx_masuk_retur = 0;
                            $idx_masuk_beli = 0;
                            foreach ($data_keluar as $k_dk => $v_dk) {
                                $total_nilai_keluar_beli = 0;
                                $total_nilai_keluar_jual = 0;
                                $total_jml_keluar = 0;
                                $jml_keluar = $v_dk['jumlah'];
                                $tgl_keluar = $v_dk['tgl_trans'];

                                // if ( !empty($_data_masuk) ) {
                                if ( !empty($data_masuk_retur) ) {
                                    $_jml_keluar = $jml_keluar;
                                    while ($_jml_keluar > 0) {
                                        if ( isset($data_masuk_retur[$idx_masuk_retur]) ) {
                                            $hrg_beli = $data_masuk_retur[$idx_masuk_retur]['hrg_beli'];
                                            $hrg_jual = $data_masuk_retur[$idx_masuk_retur]['hrg_jual'];
                                            if ( $data_masuk_retur[$idx_masuk_retur]['tgl_trans'] <= $tgl_keluar ) {
                                                if ( $data_masuk_retur[$idx_masuk_retur]['jumlah'] >= $jml_keluar ) {
                                                    $data_masuk_retur[$idx_masuk_retur]['jumlah'] = $data_masuk_retur[$idx_masuk_retur]['jumlah'] - $jml_keluar;

                                                    $total_nilai_keluar_beli += $jml_keluar * $hrg_beli;
                                                    $total_nilai_keluar_jual += $jml_keluar * $hrg_jual;

                                                    $total_jml_keluar += $jml_keluar;

                                                    $jml_keluar = 0;
                                                    $_jml_keluar = $jml_keluar;
                                                } else {
                                                    $total_nilai_keluar_beli += $data_masuk_retur[$idx_masuk_retur]['jumlah'] * $hrg_beli;
                                                    $total_nilai_keluar_jual += $data_masuk_retur[$idx_masuk_retur]['jumlah'] * $hrg_jual;

                                                    $total_jml_keluar += $data_masuk_retur[$idx_masuk_retur]['jumlah'];

                                                    $jml_keluar = $jml_keluar - $data_masuk_retur[$idx_masuk_retur]['jumlah'];
                                                    $_jml_keluar = $jml_keluar;

                                                    $data_masuk_retur[$idx_masuk_retur]['jumlah'] = 0;

                                                    $idx_masuk_retur++;
                                                }
                                            } else {
                                                $_jml_keluar = 0;
                                            }
                                        } else {
                                            $_jml_keluar = 0;
                                        }
                                    }

                                    // $_jml_keluar = $jml_keluar;
                                    // while ($_jml_keluar > 0) {
                                    //     if ( isset($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]) ) {
                                    //         if ( $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['tgl_trans'] <= $tgl_keluar ) {

                                    //             $hrg_beli = $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['hrg_beli'];
                                    //             $hrg_jual = $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['hrg_jual'];

                                    //             if ( $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] >= $jml_keluar ) {
                                    //                 $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] = $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] - $jml_keluar;

                                    //                 $total_nilai_keluar_beli += $jml_keluar * $hrg_beli;
                                    //                 $total_nilai_keluar_jual += $jml_keluar * $hrg_jual;

                                    //                 $jml_keluar = 0;
                                    //                 $_jml_keluar = $jml_keluar;
                                    //             } else {
                                    //                 if ( $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] > 0 && isset($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur+1]) ) {

                                    //                     $jml_keluar = $jml_keluar - $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'];
                                    //                     $_jml_keluar = $jml_keluar;

                                    //                     $total_nilai_keluar_beli += $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] * $hrg_beli;
                                    //                     $total_nilai_keluar_jual += $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] * $hrg_jual;
                                    //                     $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] = 0;
                                    //                 } else {
                                    //                     if ( $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] > $jml_keluar ) {
                                    //                         $total_nilai_keluar_beli = $jml_keluar * $hrg_beli;
                                    //                         $total_nilai_keluar_jual = $jml_keluar * $hrg_jual;

                                    //                         // // $jml_keluar = 0;
                                    //                         // $jml_keluar = $jml_keluar - $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'];
                                    //                     } else {
                                    //                         $total_nilai_keluar_beli =  $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] * $hrg_beli;
                                    //                         $total_nilai_keluar_jual =  $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'] * $hrg_jual;             
                                    //                     }

                                    //                     $jml_keluar = $jml_keluar - $_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'];

                                    //                     // if ( $v_gdg['id'] == 26 && $v_brg['kode'] == 'OB2109055' ) {
                                    //                     //     cetak_r( 'RETUR : '.$_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'].' | '.$jml_keluar );
                                    //                     // }

                                    //                     $idx_masuk_retur++;
                                    //                 }

                                    //                 // if ( $v_gdg['id'] == 26 && $v_brg['kode'] == 'OB2109055' ) {
                                    //                 //     cetak_r( 'RETUR : '.$_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]['jumlah'].' | '.$jml_keluar );
                                    //                 // }

                                    //                 $idx_masuk_retur++;
                                    //             }
                                    //         } else {
                                    //             if ( !isset($_data_masuk['retur'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_retur]) ) {
                                    //                 $total_nilai_keluar_beli = 0;
                                    //                 $total_nilai_keluar_jual = 0;
                                    //             }

                                    //             $idx_masuk_retur++;
                                    //         }
                                    //     } else {
                                    //         $_jml_keluar = 0;
                                    //     }
                                    // }
                                }

                                if ( !empty($data_masuk_beli) ) {
                                    $_jml_keluar = $jml_keluar;
                                    while ($_jml_keluar > 0) {
                                        if ( isset($data_masuk_beli[$idx_masuk_beli]) ) {
                                            $hrg_beli = ($data_masuk_beli[$idx_masuk_beli]['hrg_beli'] > 0) ? $data_masuk_beli[$idx_masuk_beli]['hrg_beli'] : $data_masuk_beli[$idx_masuk_beli-1]['hrg_beli'];
                                            $hrg_jual = ($data_masuk_beli[$idx_masuk_beli]['hrg_jual'] > 0) ? $data_masuk_beli[$idx_masuk_beli]['hrg_jual'] : $data_masuk_beli[$idx_masuk_beli-1]['hrg_jual'];
                                            if ( $data_masuk_beli[$idx_masuk_beli]['tgl_trans'] <= $tgl_keluar ) {
                                                if ( $data_masuk_beli[$idx_masuk_beli]['jumlah'] >= $jml_keluar ) {
                                                    $data_masuk_beli[$idx_masuk_beli]['jumlah'] = $data_masuk_beli[$idx_masuk_beli]['jumlah'] - $jml_keluar;

                                                    $total_nilai_keluar_beli += $jml_keluar * $hrg_beli;
                                                    $total_nilai_keluar_jual += $jml_keluar * $hrg_jual;

                                                    $total_jml_keluar += $jml_keluar;

                                                    $jml_keluar = 0;
                                                    $_jml_keluar = $jml_keluar;
                                                } else {
                                                    $total_nilai_keluar_beli += $data_masuk_beli[$idx_masuk_beli]['jumlah'] * $hrg_beli;
                                                    $total_nilai_keluar_jual += $data_masuk_beli[$idx_masuk_beli]['jumlah'] * $hrg_jual;

                                                    $total_jml_keluar += $data_masuk_beli[$idx_masuk_beli]['jumlah'];

                                                    $jml_keluar = $jml_keluar - $data_masuk_beli[$idx_masuk_beli]['jumlah'];
                                                    $_jml_keluar = $jml_keluar;

                                                    $data_masuk_beli[$idx_masuk_beli]['jumlah'] = 0;

                                                    $idx_masuk_beli++;
                                                }
                                            } else {
                                                $_jml_keluar = 0;
                                            }
                                        } else {
                                            $_jml_keluar = 0;
                                        }
                                    }

                                    // $_jml_keluar = $jml_keluar;
                                    // while ($_jml_keluar > 0) {
                                    //     if ( isset($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]) ) {

                                    //         if ( $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['tgl_trans'] <= $tgl_keluar ) {
                                    //             $hrg_beli = $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['hrg_beli'];
                                    //             $hrg_jual = $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['hrg_jual'];

                                    //             if ( $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] >= $jml_keluar ) {

                                    //                 $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] = $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] - $jml_keluar;

                                    //                 $total_nilai_keluar_beli += $jml_keluar * $hrg_beli;
                                    //                 $total_nilai_keluar_jual += $jml_keluar * $hrg_jual;

                                    //                 $jml_keluar = 0;
                                    //                 $_jml_keluar = $jml_keluar;
                                    //             } else {
                                    //                 if ( $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] > 0 && isset($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli+1]) ) {

                                    //                     $jml_keluar = $jml_keluar - $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'];
                                    //                     $_jml_keluar = $jml_keluar;

                                    //                     $total_nilai_keluar_beli += $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] * $hrg_beli;
                                    //                     $total_nilai_keluar_jual += $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] * $hrg_jual;
                                    //                     $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] = 0;
                                    //                 } else {
                                    //                     if ( $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] > $jml_keluar ) {
                                    //                         $total_nilai_keluar_beli = $jml_keluar * $hrg_beli;
                                    //                         $total_nilai_keluar_jual = $jml_keluar * $hrg_jual;

                                    //                         $jml_keluar = $jml_keluar - $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'];
                                    //                         // $jml_keluar = 0;
                                    //                     } else {
                                    //                         $total_nilai_keluar_beli =  $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] * $hrg_beli;
                                    //                         $total_nilai_keluar_jual =  $_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'] * $hrg_jual;

                                    //                     }


                                    //                     $_jml_keluar = 0;
                                    //                 }

                                    //                 // if ( $v_gdg['id'] == 26 && $v_brg['kode'] == 'OB2109055' ) {
                                    //                 //     cetak_r( 'BELI : '.$_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]['jumlah'].' | '.$jml_keluar );
                                    //                 // }

                                    //                 $idx_masuk_beli++;
                                    //             }
                                    //         } else {
                                    //             if ( !isset($_data_masuk['beli'][ $v_gdg['id'] ][ $v_brg['kode'] ][$idx_masuk_beli]) ) {
                                    //                 $total_nilai_keluar_beli = 0;
                                    //                 $total_nilai_keluar_jual = 0;
                                    //             }

                                    //             $idx_masuk_retur++;
                                    //         }
                                    //     } else {
                                    //         $_jml_keluar = 0;
                                    //     }
                                    // }
                                }

                                if ( $v_dk['dari'] == 'RETUR' ) {
                                    $_harga_beli = ($total_nilai_keluar_beli > 0 && $total_jml_keluar > 0) ? $total_nilai_keluar_beli / $total_jml_keluar : 0;
                                    $_harga_jual = ($total_nilai_keluar_jual > 0 && $total_jml_keluar > 0) ? $total_nilai_keluar_jual / $total_jml_keluar : 0;

                                    $m_drp = new \Model\Storage\DetReturPakan_model();
                                    $d_drp = $m_drp->where('id', $v_dk['key'])->first();

                                    $m_drp->where('id', $v_dk['key'])->update(
                                        array(
                                            'nilai_beli' => $d_drp->jumlah * $_harga_beli,
                                            'nilai_jual' => $d_drp->jumlah * $_harga_jual
                                        )
                                    );
                                }

                                if ( $v_dk['dari'] == 'ORDER' ) {
                                    $_harga_beli = ($total_nilai_keluar_beli > 0 && $total_jml_keluar > 0) ? $total_nilai_keluar_beli / $total_jml_keluar : 0;
                                    $_harga_jual = ($total_nilai_keluar_jual > 0 && $total_jml_keluar > 0) ? $total_nilai_keluar_jual / $total_jml_keluar : 0;

                                    $m_dkp = new \Model\Storage\KirimPakanDetail_model();
                                    $d_dkp = $m_dkp->where('id', $v_dk['key'])->first();

                                    $m_dkp->where('id', $v_dk['key'])->update(
                                        array(
                                            'nilai_beli' => $d_dkp->jumlah * $_harga_beli,
                                            'nilai_jual' => $d_dkp->jumlah * $_harga_jual
                                        )
                                    );
                                }
                                // }
                            }
                        }

                        if ( !empty($data_masuk_retur) ) {
                            foreach ($data_masuk_retur as $k_dm => $val) {
                                if ( $val['jumlah'] > 0 ) {
                                    $data[ $v_gdg['id'] ][ $v_brg['kode'] ][ $val['kode_trans'] ] = array(
                                        'tgl_trans' => $val['tgl_trans'],
                                        'kode_gudang' => $val['kode_gudang'],
                                        'kode_barang' => $val['kode_barang'],
                                        'jumlah' => $val['jumlah'],
                                        'hrg_jual' => $val['hrg_jual'],
                                        'hrg_beli' => $val['hrg_beli'],
                                        'kode_trans' => $val['kode_trans'],
                                        'dari' => $val['dari'],
                                    );

                                    ksort( $data );
                                }
                            }
                        }
                        if ( !empty($data_masuk_beli) ) {
                            foreach ($data_masuk_beli as $k_dm => $val) {
                                if ( $val['jumlah'] > 0 ) {
                                    $data[ $v_gdg['id'] ][ $v_brg['kode'] ][ $val['kode_trans'] ] = array(
                                        'tgl_trans' => $val['tgl_trans'],
                                        'kode_gudang' => $val['kode_gudang'],
                                        'kode_barang' => $val['kode_barang'],
                                        'jumlah' => $val['jumlah'],
                                        'hrg_jual' => $val['hrg_jual'],
                                        'hrg_beli' => $val['hrg_beli'],
                                        'kode_trans' => $val['kode_trans'],
                                        'dari' => $val['dari'],
                                    );

                                    ksort( $data );
                                }
                            }
                        }

                        // if ( $v_gdg['id'] == 26 && $v_brg['kode'] == 'OB2109055' ) {
                        //     cetak_r( $data[ $v_gdg['id'] ][ $v_brg['kode'] ], 1 );
                        // }
                    }
                }
            }
        }

        return $data;
    }

    public function hitung_stok()
    {
        $startDate = $this->input->post('startDate');
        $endDate = $this->input->post('endDate');
        $target = $this->input->post('target');

        // $startDate = '2022-02-01';
        // $endDate = '2022-02-01';
        // $target = '2022-02-01';

        try {
            $startDate = substr($startDate, 0, 7).'-01';
            $endDate = substr($endDate, 0, 7).'-01';

            $_target = substr($target, 0, 7).'-01';
            $tgl_proses = $_target;
            $_periode = $tgl_proses;
            $periode = substr($_periode, 0, 7).'-01';

            $exp_per = explode("-", $periode);
            $year = $exp_per[0];
            $month =  (int) $exp_per[1];

            // NOTE : JIKA PERIODE SALDO AWAL MELEWATI TAHUN
            if ($month == 12) {
                $next_year = $year + 1;
                $next_month = 1;
            } else {
                $next_year = $year;
                $next_month = $month+1;
            }
            if ($month == 1) {
                $prev_year = $year - 1;
                $prev_month = 12;
            } else {
                $prev_year = $year;
                $prev_month = $month-1;
            }

            // NOTE : JIKA PANJANG STRING BULAN 1 MAKAN TAMBAH 0 DI DEPAN
            if (strlen($month) == 1) {
                $month = '0'.$month;
            }

            if (strlen($next_month) == 1) {
                $next_month = '0'.$next_month;
            }

            if (strlen($prev_month) == 1) {
                $prev_month = '0'.$prev_month;
            }

            $prev_periode = $periode;
            $next_periode = $next_year.'-'.$next_month.'-01';
            $prev_periode = $prev_year.'-'.$prev_month.'-01';

            // $stok_voadip = $this->hitung_stok_voadip( $periode, $next_periode );
            // $stok_pakan = $this->hitung_stok_pakan( $periode, $next_periode );
            $stok_voadip = $this->hitung_stok_voadip( $prev_periode, $periode );
            // $stok_pakan = $this->hitung_stok_pakan( $prev_periode, $periode );

            // cetak_r( $stok_voadip[ 28 ]['OB2109010'], 1 );

            $m_stok = new \Model\Storage\Stok_model();
            $now = $m_stok->getDate();

            $d_stok = $m_stok->where('periode', $periode)->first();
            $id_stok = null;
            if ( $d_stok ) {
                $id_stok = $d_stok->id;
                $m_stok->where('id', $id_stok)->update(
                    array(
                        'user_proses' => $this->userid,
                        'tgl_proses' => $now['waktu']
                    )
                );
            } else {
                // $m_stok->periode = $next_periode;
                $m_stok->periode = $periode;
                $m_stok->user_proses = $this->userid;
                $m_stok->tgl_proses = $now['waktu'];
                $m_stok->save();

                $id_stok = $m_stok->id;
            }

            $m_sd = new \Model\Storage\DetStok_model();
            $d_sd = $m_sd->where('id_header', $id_stok)->where('jenis_barang', 'voadip')->delete();
            if ( !empty($stok_voadip) ) {
                foreach ($stok_voadip as $k_gdg => $v_gdg) {
                    foreach ($v_gdg as $k_brg => $v_brg) {
                        foreach ($v_brg as $k => $val) {
                            $m_sd = new \Model\Storage\DetStok_model();
                            $m_sd->id_header = $id_stok;
                            $m_sd->tgl_trans = !empty($val['tgl_trans']) ? $val['tgl_trans'] : null;
                            $m_sd->kode_gudang = $val['kode_gudang'];
                            $m_sd->kode_barang = $val['kode_barang'];
                            $m_sd->jumlah = $val['jumlah'];
                            $m_sd->hrg_jual = $val['hrg_jual'];
                            $m_sd->hrg_beli = $val['hrg_beli'];
                            $m_sd->kode_trans = !empty($val['kode_trans']) ? $val['kode_trans'] : null;
                            $m_sd->jenis_barang = 'voadip';
                            $m_sd->jenis_trans = $val['dari'];
                            $m_sd->save();
                        }
                    }
                }
            }

            // if ( !empty($stok_pakan) ) {
            //     foreach ($stok_pakan as $k_gdg => $v_gdg) {
            //         foreach ($v_gdg as $k_brg => $v_brg) {
            //             foreach ($v_brg as $k => $val) {
            //                 $m_sd = new \Model\Storage\DetStok_model();
            //                 $m_sd->id_header = $id_stok;
            //                 $m_sd->tgl_trans = !empty($val['tgl_trans']) ? $val['tgl_trans'] : null;
            //                 $m_sd->kode_gudang = $val['kode_gudang'];
            //                 $m_sd->kode_barang = $val['kode_barang'];
            //                 $m_sd->jumlah = $val['jumlah'];
            //                 $m_sd->hrg_jual = $val['hrg_jual'];
            //                 $m_sd->hrg_beli = $val['hrg_beli'];
            //                 $m_sd->kode_trans = !empty($val['kode_trans']) ? $val['kode_trans'] : null;
            //                 $m_sd->jenis_barang = 'pakan';
            //                 $m_sd->jenis_trans = $val['dari'];
            //                 $m_sd->save();
            //             }
            //         }
            //     }
            // }

            // $this->hitung_pindah_pakan( $periode );

            $d_stok = $m_stok->where('id', $id_stok)->with(['det_stok'])->first();
            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_stok, $deskripsi_log);

            $new_target = date("Y-m-d", strtotime ( '+1 month' , strtotime ( $_target ) )) ;
            $lanjut = 0;
            if ( $endDate > $_target ) {
                $lanjut = 1;
            }

            $params = array(
                'start_date' => $startDate,
                'end_date' => $endDate,
                'target' => $tgl_proses,
                'new_target' => $new_target,
                'text_target' => substr(tglIndonesia($new_target, '-', ' '), 3)
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

    public function hitung_pindah_pakan( $_start_date )
    {
        $timestamp = strtotime(substr($_start_date, 0, 10));

        $start_date = date('Y-m-01', $timestamp);
        $end_date = date('Y-m-t', $timestamp);

        $m_kp = new \Model\Storage\KirimPakan_model();
        $d_kp = $m_kp->where('jenis_kirim', 'opkp')->where('jenis_tujuan', 'peternak')->whereBetween('tgl_kirim', [$start_date, $end_date])->with(['detail'])->orderBy('tgl_kirim', 'asc')->get();

        if ( $d_kp->count() > 0 ) {
            $d_kp = $d_kp->toArray();

            foreach ($d_kp as $k_kp => $v_kp) {
                $id_kpd_before = null;
                $m_kp = new \Model\Storage\KirimPakan_model();
                $id_kpd_before = $m_kp->select('id')->where('tujuan', $v_kp['asal'])->where('tgl_kirim', '<', $v_kp['tgl_kirim'])->orderBy('tgl_kirim', 'desc')->get();

                if ( $id_kpd_before->count() > 0 ) {
                    $id_kpd_before = $id_kpd_before->toArray();
                    foreach ($v_kp['detail'] as $k_det => $v_det) {
                        $m_kpd = new \Model\Storage\KirimPakanDetail_model();
                        $d_kpd_before = $m_kpd->whereIn('id_header', $id_kpd_before)->where('item', $v_det['item'])->orderBy('id', 'desc')->get();

                        $jumlah_pindah_pakan = $v_det['jumlah'];
                        $nilai_beli = 0;
                        $nilai_jual = 0;
                        if ( $d_kpd_before->count() > 0 ) {
                            $d_kpd_before = $d_kpd_before->toArray();
                            $idx = 0;
                            while ($jumlah_pindah_pakan > 0) {
                                $harga_beli = $d_kpd_before[ $idx ]['nilai_beli'] / $d_kpd_before[ $idx ]['jumlah'];
                                $harga_jual = $d_kpd_before[ $idx ]['nilai_jual'] / $d_kpd_before[ $idx ]['jumlah'];

                                $nilai_beli += $harga_beli * $jumlah_pindah_pakan;
                                $nilai_jual += $harga_jual * $jumlah_pindah_pakan;

                                if ( $d_kpd_before[ $idx ]['jumlah'] > $v_det['jumlah'] ) {
                                    $jumlah_pindah_pakan = 0;
                                } else {
                                    $jumlah_pindah_pakan -= $v_det['jumlah'];
                                }

                                $idx = 0;
                            }

                            $m_kpd = new \Model\Storage\KirimPakanDetail_model();
                            $m_kpd->where('id', $v_det['id'])->update(
                                array(
                                    'nilai_beli' => $nilai_beli,
                                    'nilai_jual' => $nilai_jual
                                )
                            );
                        }
                    }
                }
            }
        }
    }

    public function tes()
    {
        $startDate = $this->input->post('startDate');
        $endDate = $this->input->post('endDate');
        $target = $this->input->post('target');

        try {
            $_target = substr($target, 0, 7).'-01';
            $tgl_proses = $_target;

            $new_target = date("Y-m-d", strtotime ( '+1 month' , strtotime ( $_target ) )) ;
            $lanjut = 0;
            if ( $endDate > $_target ) {
                $lanjut = 1;
            }

            $params = array(
                'start_date' => $startDate,
                'end_date' => $endDate,
                'target' => $tgl_proses,
                'new_target' => $new_target,
                'text_target' => substr(tglIndonesia($new_target, '-', ' '), 3)
            );
            
            $this->result['lanjut'] = $lanjut;
            $this->result['params'] = $params;
            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hitung.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}