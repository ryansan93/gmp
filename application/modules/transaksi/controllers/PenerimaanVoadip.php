<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PenerimaanVoadip extends Public_Controller {

    private $url;
    private $hakAkses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->hakAkses = hakAkses($this->url);
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index($segment=0)
    {
        if ( $this->hakAkses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/jquery/list.min.js",
                "assets/transaksi/penerimaan_voadip/js/penerimaan-voadip.js",
            ));
            $this->add_external_css(array(
                "assets/transaksi/penerimaan_voadip/css/penerimaan-voadip.css",
            ));

            $data = $this->includes;

            $list_unit = $this->get_unit();

            $content['akses'] = $this->hakAkses;
            $content['unit'] = $list_unit;

            $a_content['get_sj_not_terima'] = null;
            $a_content['unit'] = $list_unit;

            $content['add_form'] = $this->load->view('transaksi/penerimaan_voadip/add_form', $a_content, TRUE);

            $data['title_menu'] = 'Penerimaan Voadip';
            $data['view'] = $this->load->view('transaksi/penerimaan_voadip/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_unit()
    {
        $m_duser = new \Model\Storage\DetUser_model();
        $d_duser = $m_duser->where('id_user', $this->userid)->first();

        $m_karyawan = new \Model\Storage\Karyawan_model();
        $d_karyawan = $m_karyawan->where('nama', 'like', strtolower(trim($d_duser->nama_detuser)).'%')->orderBy('id', 'desc')->first();

        $data = null;

        // $kode_unit = array();
        // $kode_unit_all = null;
        $data = null;
        if ( $d_karyawan ) {
            $m_ukaryawan = new \Model\Storage\UnitKaryawan_model();
            $d_ukaryawan = $m_ukaryawan->where('id_karyawan', $d_karyawan->id)->get();

            if ( $d_ukaryawan->count() > 0 ) {
                $d_ukaryawan = $d_ukaryawan->toArray();

                foreach ($d_ukaryawan as $k_ukaryawan => $v_ukaryawan) {
                    if ( stristr($v_ukaryawan['unit'], 'all') === false ) {
                        $m_wil = new \Model\Storage\Wilayah_model();
                        $d_wil = $m_wil->where('id', $v_ukaryawan['unit'])->first();

                        $nama = str_replace('Kab ', '', str_replace('Kota ', '', $d_wil->nama));
                        $kode = $d_wil->kode;

                        $key = $nama.' - '.$kode;

                        $data[$key] = array(
                            'nama' => $nama,
                            'kode' => $kode
                        );
                    } else {
                        $m_wil = new \Model\Storage\Wilayah_model();
                        $d_wil = $m_wil->select('nama', 'kode')->where('jenis', 'UN')->get();

                        if ( $d_wil->count() > 0 ) {
                            $d_wil = $d_wil->toArray();
                            foreach ($d_wil as $k_wil => $v_wil) {
                                $nama = str_replace('Kab ', '', str_replace('Kota ', '', $v_wil['nama']));
                                $kode = $v_wil['kode'];

                                $key = $nama.' - '.$kode;
                                $data[$key] = array(
                                    'nama' => $nama,
                                    'kode' => $kode
                                );
                            }
                        }
                    }
                }
            } else {
                $m_wil = new \Model\Storage\Wilayah_model();
                $d_wil = $m_wil->select('nama', 'kode')->where('jenis', 'UN')->get();

                if ( $d_wil->count() > 0 ) {
                    $d_wil = $d_wil->toArray();
                    foreach ($d_wil as $k_wil => $v_wil) {
                        $nama = str_replace('Kab ', '', str_replace('Kota ', '', $v_wil['nama']));
                        $kode = $v_wil['kode'];

                        $key = $nama.' - '.$kode;
                        $data[$key] = array(
                            'nama' => $nama,
                            'kode' => $kode
                        );
                    }
                }
            }
        } else {
            $m_wil = new \Model\Storage\Wilayah_model();
            $d_wil = $m_wil->select('nama', 'kode')->where('jenis', 'UN')->get();

            if ( $d_wil->count() > 0 ) {
                $d_wil = $d_wil->toArray();
                foreach ($d_wil as $k_wil => $v_wil) {
                    $nama = str_replace('Kab ', '', str_replace('Kota ', '', $v_wil['nama']));
                    $kode = $v_wil['kode'];

                    $key = $nama.' - '.$kode;
                    $data[$key] = array(
                        'nama' => $nama,
                        'kode' => $kode
                    );
                }
            }
        }

        if ( !empty($data) ) {
            ksort($data);
        }

        return $data;
    }

    public function load_form()
    {
        $id = $this->input->get('id');
        $resubmit = $this->input->get('resubmit');

        $list_unit = $this->get_unit();

        $a_content['get_sj_not_terima'] = null;
        $a_content['unit'] = $list_unit;
        // $a_content['get_sj_not_terima'] = $this->get_sj_not_terima();

        $html = null;
        if ( !empty($id) && !empty($resubmit) ) {
            $m_tv = new \Model\Storage\TerimaVoadip_model();
            $d_tv = $m_tv->where('id', $id)->with(['detail', 'kirim_voadip'])->first()->toArray();

            $tujuan = null;
            $asal = null;

            $m_supplier = new \Model\Storage\Pelanggan_model();
            $m_peternak = new \Model\Storage\RdimSubmit_model();
            $m_gudang = new \Model\Storage\Gudang_model();
            // ASAL
            if ( $d_tv['kirim_voadip']['jenis_kirim'] == 'opks' ) {
                $d_supplier = $m_supplier->where('nomor', $d_tv['kirim_voadip']['asal'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('id', 'desc')->first();
                $asal = $d_supplier->nama;
            } else if ( $d_tv['kirim_voadip']['jenis_kirim'] == 'opkp' ) {
                $d_peternak = $m_peternak->where('noreg', $d_tv['kirim_voadip']['asal'])->with(['mitra'])->orderBy('id', 'desc')->first();
                $asal = $d_peternak->mitra->dMitra->nama;
            } else if ( $d_tv['kirim_voadip']['jenis_kirim'] == 'opkg' ) {
                $d_gudang = $m_gudang->where('id', $d_tv['kirim_voadip']['asal'])->orderBy('id', 'desc')->first();
                $asal = $d_gudang->nama;
            }

            // TUJUAN
            if ( $d_tv['kirim_voadip']['jenis_tujuan'] == 'peternak' ) {
                $m_rs = new \Model\Storage\RdimSubmit_model();
                $d_rs = $m_rs->where('noreg', $d_tv['kirim_voadip']['tujuan'])->with(['mitra'])->orderBy('id', 'desc')->first();
                $tujuan = $d_rs->mitra->dMitra->nama;
            } else {
                $m_gusang = new \Model\Storage\Gudang_model();
                $d_gudang = $m_gusang->where('id', $d_tv['kirim_voadip']['tujuan'])->orderBy('id', 'desc')->first();
                $tujuan = $d_gudang->nama;
            }

            $a_content['data'] = $d_tv;
            $a_content['asal'] = $asal;
            $a_content['tujuan'] = $tujuan;
            $html = $this->load->view('transaksi/penerimaan_voadip/edit_form', $a_content, TRUE);
        } else if ( !empty($id) && empty($resubmit) ) {
            $m_tv = new \Model\Storage\TerimaVoadip_model();
            $d_tv = $m_tv->where('id', $id)->with(['detail', 'kirim_voadip'])->first()->toArray();

            $m_rv = new \Model\Storage\ReturVoadip_model();
            $d_rv = $m_rv->where('no_order', $d_tv['kirim_voadip']['no_order'])->first();

            $retur = 0;
            if ( $d_rv ) {
                $retur = 1;
            }

            $tujuan = null;
            $asal = null;

            $m_supplier = new \Model\Storage\Pelanggan_model();
            $m_peternak = new \Model\Storage\RdimSubmit_model();
            $m_gudang = new \Model\Storage\Gudang_model();
            // ASAL
            if ( $d_tv['kirim_voadip']['jenis_kirim'] == 'opks' ) {
                $d_supplier = $m_supplier->where('nomor', $d_tv['kirim_voadip']['asal'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('id', 'desc')->first();
                $asal = $d_supplier->nama;
            } else if ( $d_tv['kirim_voadip']['jenis_kirim'] == 'opkp' ) {
                $d_peternak = $m_peternak->where('noreg', $d_tv['kirim_voadip']['asal'])->with(['mitra'])->orderBy('id', 'desc')->first();
                $asal = $d_peternak->mitra->dMitra->nama;
            } else if ( $d_tv['kirim_voadip']['jenis_kirim'] == 'opkg' ) {
                $d_gudang = $m_gudang->where('id', $d_tv['kirim_voadip']['asal'])->orderBy('id', 'desc')->first();
                $asal = $d_gudang->nama;
            }

            // TUJUAN
            if ( $d_tv['kirim_voadip']['jenis_tujuan'] == 'peternak' ) {
                $m_rs = new \Model\Storage\RdimSubmit_model();
                $d_rs = $m_rs->where('noreg', $d_tv['kirim_voadip']['tujuan'])->with(['mitra'])->orderBy('id', 'desc')->first();
                $tujuan = $d_rs->mitra->dMitra->nama;
            } else {
                $m_gusang = new \Model\Storage\Gudang_model();
                $d_gudang = $m_gusang->where('id', $d_tv['kirim_voadip']['tujuan'])->orderBy('id', 'desc')->first();
                $tujuan = $d_gudang->nama;
            }

            $a_content['akses'] = $this->hakAkses;
            $a_content['data'] = $d_tv;
            $a_content['asal'] = $asal;
            $a_content['tujuan'] = $tujuan;
            $a_content['retur'] = $retur;
            $html = $this->load->view('transaksi/penerimaan_voadip/view_form', $a_content, TRUE);
        } else {
            $html = $this->load->view('transaksi/penerimaan_voadip/add_form', $a_content, TRUE);
        }

        echo $html;
    }

    public function get_lists()
    {
        $params = $this->input->post('params');

        $kode_unit = $params['kode_unit'];

        $m_terima_voadip = new \Model\Storage\TerimaVoadip_model();
        $d_terima_voadip = $m_terima_voadip->whereBetween('tgl_terima', [$params['start_date'], $params['end_date']])->get();
        $data = null;
        if ( $d_terima_voadip ) {
            $d_terima_voadip = $d_terima_voadip->toArray();
            foreach ($d_terima_voadip as $k_tv => $v_tv) {
                $tampil = 0;

                $m_kv = new \Model\Storage\KirimVoadip_model();
                $d_kv = $m_kv->where('id', $v_tv['id_kirim_voadip'])->first();

                if ( $d_kv ) {
                    $d_kv = $d_kv->toArray();

                    if ( $kode_unit != 'all' ) {
                        if ( $d_kv['jenis_kirim'] == 'opks' || $d_kv['jenis_kirim'] == 'opkg' ) {
                            if ( $kode_unit != 'all' ) {
                                if ( stristr($d_kv['no_order'], $kode_unit) ) {
                                    $tampil = 1;
                                }
                            } else {
                                $tampil = 1;
                            }
                        } else if ( $d_kv['jenis_kirim'] == 'opkp' ) {
                            if ( $kode_unit != 'all' ) {
                                $m_conf = new \Model\Storage\Conf();
                                $sql = "
                                    select w.kode from rdim_submit rs
                                    right join
                                        kandang k
                                        on
                                            rs.kandang = k.id
                                    right join
                                        wilayah w
                                        on
                                            k.unit = w.id
                                    where
                                        rs.noreg = '".$d_kv['asal']."'
                                    group by
                                        w.kode
                                ";
                                $d_asal = $m_conf->hydrateRaw( $sql );
                                $kode_unit_asal = null;
                                if ( $d_asal->count() > 0 ) {
                                    $d_asal = $d_asal->toArray()[0];
                                    $kode_unit_asal = $d_asal['kode'];
                                }

                                $sql = "
                                    select w.kode from rdim_submit rs
                                    right join
                                        kandang k
                                        on
                                            rs.kandang = k.id
                                    right join
                                        wilayah w
                                        on
                                            k.unit = w.id
                                    where
                                        rs.noreg = '".$d_kv['tujuan']."'
                                    group by
                                        w.kode
                                ";
                                $d_tujuan = $m_conf->hydrateRaw( $sql );
                                $kode_unit_tujuan = null;
                                if ( $d_tujuan->count() > 0 ) {
                                    $d_tujuan = $d_tujuan->toArray()[0];
                                    $kode_unit_tujuan = $d_tujuan['kode'];
                                }

                                if ( stristr($kode_unit_asal, $kode_unit) || stristr($kode_unit_tujuan, $kode_unit) ) {
                                    $tampil = 1;
                                }
                            } else {
                                $tampil = 1;
                            }
                        }
                    } else {
                        $tampil = 1;
                    }

                    if ( $tampil == 1 ) {
                        $asal = null;
                        $tujuan = null;

                        $m_supplier = new \Model\Storage\Pelanggan_model();
                        $m_peternak = new \Model\Storage\RdimSubmit_model();
                        $m_gudang = new \Model\Storage\Gudang_model();
                        // ASAL
                        if ( $d_kv['jenis_kirim'] == 'opks' ) {
                            $d_supplier = $m_supplier->where('nomor', $d_kv['asal'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('id', 'desc')->first();
                            $asal = $d_supplier->nama;
                        } else if ( $d_kv['jenis_kirim'] == 'opkp' ) {
                            $d_peternak = $m_peternak->where('noreg', $d_kv['asal'])->with(['mitra'])->orderBy('id', 'desc')->first();
                            $asal = $d_peternak->mitra->dMitra->nama;
                        } else if ( $d_kv['jenis_kirim'] == 'opkg' ) {
                            $d_gudang = $m_gudang->where('id', $d_kv['asal'])->orderBy('id', 'desc')->first();
                            $asal = $d_gudang->nama;
                        }
                        // TUJUAN
                        if ( $d_kv['jenis_tujuan'] == 'peternak' ) {
                            $d_peternak = $m_peternak->where('noreg', $d_kv['tujuan'])->with(['mitra'])->orderBy('id', 'desc')->first();
                            if ( $d_peternak ) {
                                $tujuan = $d_peternak->mitra->dMitra->nama.' ('.$d_kv['tujuan'].')';
                            }
                        } else if ( $d_kv['jenis_tujuan'] == 'gudang' ) {
                            $d_gudang = $m_gudang->where('id', $d_kv['tujuan'])->orderBy('id', 'desc')->first();
                            $tujuan = $d_gudang->nama;
                        }

                        $key = str_replace('-', '', $v_tv['tgl_terima']).'|'.$v_tv['id_kirim_voadip'];
                        $data[ $key ] = array(
                            'id' => $v_tv['id'],
                            'no_sj' => $d_kv['no_sj'],
                            'tgl_terima' => $v_tv['tgl_terima'],
                            'asal' => $asal,
                            'tujuan' => $tujuan,
                            'nopol' => $d_kv['no_polisi'],
                        );
                    }
                }

                if ( !empty($data) ) {
                    krsort($data);
                }
            }
        }

        $content['data'] = $data;
        $html = $this->load->view('transaksi/penerimaan_voadip/list', $content, true);

        $this->result['status'] = 1;
        $this->result['content'] = $html;

        display_json($this->result);
    }

    public function get_sj_not_terima()
    {
        $params = $this->input->post('params');

        $unit = $params['unit'];
        $tgl_kirim = $params['tgl_kirim'];

        $idx = 0;
        $data = array();

        $m_kv = new \Model\Storage\KirimVoadip_model();        
        $d_kv = $m_kv->select('id', 'no_sj')->whereBetween('tgl_kirim', [$tgl_kirim, $tgl_kirim])->where('no_order', 'like', '%'.$unit.'%')->with(['terima'])->orderBy('no_sj', 'asc')->get();
        if ( $d_kv->count() > 0 ) {
            $d_kv = $d_kv->toArray();

            foreach ($d_kv as $k_kv => $v_kv) {
                if ( empty($v_kv['terima']) ) {
                    array_push($data, $v_kv);
                }
            }
        } 
        // else {
        //     $d_kv_kosong = $m_kv->with(['detail'])->get();
        //     if ( $d_kv_kosong->count() > 0 ) {
        //         $data = $d_kv_kosong->toArray();
        //     }
        // }

        $this->result['content'] = $data;

        display_json( $this->result );
    }

    public function get_data_by_sj()
    {
        $id_kirim = $this->input->post('id_kirim');

        $m_kv = new \Model\Storage\KirimVoadip_model();
        $d_kv = $m_kv->where('id', $id_kirim)->with(['detail'])->first()->toArray();

        $detail = null;
        foreach ($d_kv['detail'] as $k_det => $v_det) {
            $detail[] = array(
                'id' => $v_det['id'],
                'id_header' => $v_det['id_header'],
                'item' => $v_det['item'],
                'jumlah' => $v_det['jumlah'],
                'kondisi' => $v_det['kondisi'],
                'hrg_beli' => 0,
                'hrg_jual' => 0,
                'nilai_beli' => $v_det['nilai_beli'],
                'nilai_jual' => $v_det['nilai_jual'],
                'd_barang' => $v_det['d_barang']
            );
        }

        $tujuan = null;
        $asal = null;

        $m_supplier = new \Model\Storage\Pelanggan_model();
        $m_peternak = new \Model\Storage\RdimSubmit_model();
        $m_gudang = new \Model\Storage\Gudang_model();
        // ASAL
        if ( $d_kv['jenis_kirim'] == 'opks' ) {
            $detail = null;

            $d_supplier = $m_supplier->where('nomor', $d_kv['asal'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('id', 'desc')->first();
            $asal = $d_supplier->nama;

            $m_ov = new \Model\Storage\OrderVoadip_model();
            $d_ov = $m_ov->where('no_order', $d_kv['no_order'])->orderBy('version', 'desc')->first()->toArray();
            foreach ($d_kv['detail'] as $k_det => $v_det) {
                $m_ovd = new \Model\Storage\OrderVoadipDetail_model();
                $d_ovd = $m_ovd->where('id_order', $d_ov['id'])->where('kode_barang', $v_det['item'])->first();

                $detail[] = array(
                    'id' => $v_det['id'],
                    'id_header' => $v_det['id_header'],
                    'item' => $v_det['item'],
                    'jumlah' => $v_det['jumlah'],
                    'kondisi' => $v_det['kondisi'],
                    'hrg_beli' => $d_ovd->harga,
                    'hrg_jual' => $d_ovd->harga_jual,
                    'nilai_beli' => $v_det['nilai_beli'],
                    'nilai_jual' => $v_det['nilai_jual'],
                    'd_barang' => $v_det['d_barang']
                );
            }
        } else if ( $d_kv['jenis_kirim'] == 'opkp' ) {
            $d_peternak = $m_peternak->where('noreg', $d_kv['asal'])->with(['dMitraMapping'])->orderBy('id', 'desc')->first();
            $asal = $d_peternak->dMitraMapping->dMitra->nama;
        } else if ( $d_kv['jenis_kirim'] == 'opkg' ) {
            $d_gudang = $m_gudang->where('id', $d_kv['asal'])->orderBy('id', 'desc')->first();
            $asal = $d_gudang->nama;
        }

        // TUJUAN
        if ( $d_kv['jenis_tujuan'] == 'peternak' ) {
            $m_rs = new \Model\Storage\RdimSubmit_model();
            $d_rs = $m_rs->where('noreg', $d_kv['tujuan'])->with(['mitra'])->orderBy('id', 'desc')->first();
            $tujuan = $d_rs->mitra->dMitra->nama;
        } else {
            $m_gusang = new \Model\Storage\Gudang_model();
            $d_gudang = $m_gusang->where('id', $d_kv['tujuan'])->orderBy('id', 'desc')->first();
            $tujuan = $d_gudang->nama;
        }

        $jenis_kirim = array(
            'opks' => 'Order Pabrik (OPKS)',
            'opkp' => 'Dari Peternak (OPKP)',
            'opkg' => 'Dari Gudang (OPKG)',
        );

        $data = array(
            'no_pol' => $d_kv['no_polisi'],
            'ekspedisi' => $d_kv['ekspedisi'],
            'sopir' => $d_kv['sopir'],
            'jenis_kirim' => $jenis_kirim[$d_kv['jenis_kirim']],
            'no_order' => strtoupper($d_kv['no_order']),
            'tgl_kirim' => tglIndonesia($d_kv['tgl_kirim'], '-', ' '),
            'asal' => $asal,
            'tujuan' => $tujuan,
            'detail' => $detail,
        );

        // cetak_r( $data );

        $this->result['status'] = 1;
        $this->result['content'] = $data;

        display_json($this->result);
    }

    public function save()
    {
        $params = json_decode($this->input->post('data'),TRUE);

        try {
            $path_name = null;

            $m_kv = new \Model\Storage\KirimVoadip_model();
            $d_kv = $m_kv->where('id', $params['id_kirim_voadip'])->first();

            $no_bbm = null;
            if ( $d_kv->jenis_kirim == 'opks' ) {
                $no_bbm = 'BBM/OVK/S'.str_replace('OVO', '', $d_kv->no_order);
            } else if ( $d_kv->jenis_kirim == 'opkg' ) {
                $no_bbm = 'BBM/OVK/G'.str_replace('OP', '', $d_kv->no_order);
            } else if ( $d_kv->jenis_kirim == 'opkp' ) {
                $no_bbm = 'BBM/OVK/P'.str_replace('OP', '', $d_kv->no_order);
            }

            $m_terima_voadip = new \Model\Storage\TerimaVoadip_model();
            $now = $m_terima_voadip->getDate();

            $m_terima_voadip->id_kirim_voadip = $params['id_kirim_voadip'];
            $m_terima_voadip->tgl_trans = $now['waktu'];
            $m_terima_voadip->tgl_terima = $params['tgl_terima'];
            $m_terima_voadip->path = $path_name;
            $m_terima_voadip->no_bbm = $no_bbm;
            $m_terima_voadip->save();

            $id_terima = $m_terima_voadip->id;

            foreach ($params['detail'] as $k_detail => $v_detail) {
                $m_terima_voadip_detail = new \Model\Storage\TerimaVoadipDetail_model();
                $m_terima_voadip_detail->id_header = $id_terima;
                $m_terima_voadip_detail->item = $v_detail['barang'];
                $m_terima_voadip_detail->jumlah = $v_detail['jumlah'];
                $m_terima_voadip_detail->kondisi = $v_detail['kondisi'];
                $m_terima_voadip_detail->save();
            }

            $d_terima_voadip = $m_terima_voadip->where('id', $id_terima)->with(['detail'])->first();

            $deskripsi_log_terima_voadip = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_terima_voadip, $deskripsi_log_terima_voadip);

            $this->result['status'] = 1;
            // $this->result['content'] = array('id_terima' => $id_terima);
            $this->result['content'] = array(
                'id' => $id_terima,
                'tanggal' => $params['tgl_terima'],
                'delete' => 0,
                'message' => 'Data Penerimaan Voadip berhasil di simpan.',
                'status_jurnal' => 1
            );
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit()
    {
        $params = json_decode($this->input->post('data'),TRUE);

        try {
            $execute = 1;
            $path_name = null;
            if ( $execute == 1 ) {
                $id_header = $params['id'];

                $m_kv = new \Model\Storage\KirimVoadip_model();
                $d_kv = $m_kv->where('id', $params['id_kirim_voadip'])->first();

                $no_bbm = null;
                if ( $d_kv->jenis_kirim == 'opks' ) {
                    $no_bbm = 'BBM/OVK/S'.str_replace('OVO', '', $d_kv->no_order);
                } else if ( $d_kv->jenis_kirim == 'opkg' ) {
                    $no_bbm = 'BBM/OVK/G'.str_replace('OP', '', $d_kv->no_order);
                } else if ( $d_kv->jenis_kirim == 'opkp' ) {
                    $no_bbm = 'BBM/OVK/P'.str_replace('OP', '', $d_kv->no_order);
                }
                
                $m_terima_voadip = new \Model\Storage\TerimaVoadip_model();
                $d_terima_voadip_old = $m_terima_voadip->where('id', $id_header)->first();

                $now = $m_terima_voadip->getDate();

                $m_terima_voadip->where('id', $params['id'])->update(
                    array(
                        'id_kirim_voadip' => $params['id_kirim_voadip'],
                        'tgl_trans' => $now['waktu'],
                        'tgl_terima' => $params['tgl_terima'],
                        'path' => $path_name,
                        'no_bbm' => $no_bbm
                    )
                );

                $m_terima_voadip_detail = new \Model\Storage\TerimaVoadipDetail_model();
                $m_terima_voadip_detail->where('id_header', $id_header)->delete();

                foreach ($params['detail'] as $k_detail => $v_detail) {
                    $m_terima_voadip_detail = new \Model\Storage\TerimaVoadipDetail_model();
                    $m_terima_voadip_detail->id_header = $id_header;
                    $m_terima_voadip_detail->item = $v_detail['barang'];
                    $m_terima_voadip_detail->jumlah = $v_detail['jumlah'];
                    $m_terima_voadip_detail->kondisi = $v_detail['kondisi'];
                    $m_terima_voadip_detail->save();
                }

                $d_terima_voadip = $m_terima_voadip->where('id', $id_header)->with(['detail'])->first();

                $tgl_trans = $d_terima_voadip->tgl_terima;
                if ( $d_terima_voadip_old->tgl_terima < $tgl_trans ) {
                    $tgl_trans = $d_terima_voadip_old->tgl_terima;
                }

                $deskripsi_log_terima_voadip = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $d_terima_voadip, $deskripsi_log_terima_voadip);

                $this->result['status'] = 1;
                $this->result['content'] = array(
                    'id' => $d_terima_voadip->id,
                    'tanggal' => $tgl_trans,
                    'delete' => 0,
                    'message' => 'Data Penerimaan Voadip berhasil di ubah.',
                    'status_jurnal' => 2
                );
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {
            $m_terima_voadip = new \Model\Storage\TerimaVoadip_model();
            $now = $m_terima_voadip->getDate();

            $d_terima_voadip = $m_terima_voadip->where('id', $params['id'])->with(['detail'])->first();

            $deskripsi_log_terima_voadip = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_terima_voadip, $deskripsi_log_terima_voadip);

            $this->result['status'] = 1;
            $this->result['content'] = array(
                'id' => $d_terima_voadip->id,
                'tanggal' => $d_terima_voadip->tgl_terima,
                'delete' => 1,
                'message' => 'Data Penerimaan Voadip berhasil di hapus.',
                'status_jurnal' => 3
            );
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function hitungStokByTransaksi()
    {
        $params = $this->input->post('params');

        $id = $params['id'];
        $tanggal = $params['tanggal'];
        $delete = $params['delete'];
        $message = $params['message'];
        $status_jurnal = $params['status_jurnal'];

        try {
            $noreg1 = null;
            $noreg2 = null;

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
					case
						when kv.jenis_kirim = 'opkp' then
							kv.asal
						else
							kv.tujuan
					end as noreg1,
					case
						when kv.jenis_kirim = 'opkp' then
							kv.tujuan
						else
							null
					end as noreg2,
                    kv.jenis_kirim
				from terima_voadip tv
				left join
					kirim_voadip kv
					on
						tv.id_kirim_voadip = kv.id
				where
					tv.id = '".$id."'
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray()[0];

                $noreg1 = $d_conf['noreg1'];
                $noreg2 = $d_conf['noreg2'];
            }
            
            $this->insertKonfirmasi( $id, $delete );

            $conf = new \Model\Storage\Conf();
            $sql = "EXEC hitung_stok_voadip_by_transaksi 'terima_voadip', '".$id."', '".$tanggal."', ".$delete.", ".$status_jurnal."";
            $d_conf = $conf->hydrateRaw($sql);

            $conf = new \Model\Storage\Conf();
            $sql = "EXEC hitung_stok_siklus 'voadip', 'terima_voadip', '".$id."', '".$tanggal."', ".$delete.",'".$noreg1."', '".$noreg2."'";
            $d_conf = $conf->hydrateRaw($sql);

            $id_old = null;
            if ( $status_jurnal <> 1 ) {
                $id_old = $id;
            }

            Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, $status_jurnal);

            $this->result['status'] = 1;
            $this->result['message'] = $message;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    function insertKonfirmasi($id_terima, $delete = 0) {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select kv.* from terima_voadip tv
            left join
                kirim_voadip kv
                on
                    tv.id_kirim_voadip = kv.id
            where
                tv.id = '".$id_terima."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );
        
        $no_order = null;
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray()[0];

            $no_order = $d_conf['no_order'];
        }

        $m_kpvd = new \Model\Storage\KonfirmasiPembayaranVoadipDet_model();
        $d_kpvd = $m_kpvd->where('no_order', $no_order)->first();

        if ( $d_kpvd ) {
            $m_kpvd2 = new \Model\Storage\KonfirmasiPembayaranVoadipDet2_model();
            $m_kpvd2->where('id_header', $d_kpvd->id)->delete();

            $m_kpvd = new \Model\Storage\KonfirmasiPembayaranVoadipDet_model();
            $m_kpvd->where('id', $d_kpvd->id)->delete();

            $m_kpv = new \Model\Storage\KonfirmasiPembayaranVoadip_model();
            $m_kpv->where('id', $d_kpvd->id_header)->delete();
        }

        if ( $delete == 0 ) {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    ov.rcn_kirim as tgl_bayar,
                    ov.rcn_kirim as periode_docin,
                    ov.perusahaan,
                    ov.supplier,
                    sum(dtv.jumlah * ov.harga) as total,
                    kv.no_sj,
                    ov.rcn_kirim as tgl_sj,
                    SUBSTRING(ov.no_order, 5, 3) as id_kab_kota,
                    ov.no_order,
                    sum(dtv.jumlah) as jumlah
                from det_terima_voadip dtv
                left join
                    (
                        select tv1.* from terima_voadip tv1
                        right join
                            (select max(id) as id, id_kirim_voadip from terima_voadip group by id_kirim_voadip) tv2
                            on
                                tv1.id = tv2.id
                    ) tv
                    on
                        dtv.id_header = tv.id
                left join
                    kirim_voadip kv
                    on
                        tv.id_kirim_voadip = kv.id
                left join
                    (
                        select 
                            ovd.*, 
                            ov.no_order, 
                            ov.tgl_submit as tgl_trans, 
                            ov.tanggal as rcn_kirim, 
                            ov.supplier 
                        from order_voadip_detail ovd
                        left join
                            (
                                select ov1.* from order_voadip ov1
                                right join
                                    (select max(id) as id, no_order from order_voadip group by no_order) ov2
                                    on
                                        ov1.id = ov2.id
                            ) ov
                            on
                                ovd.id_order = ov.id
                    ) ov
                    on
                        kv.no_order = ov.no_order and
                        dtv.item = ov.kode_barang
                where
                    kv.jenis_kirim = 'opks' and
                    ov.no_order = '".$no_order."'
                group by
                    ov.rcn_kirim,
                    ov.perusahaan,
                    ov.supplier,
                    kv.no_sj,
                    ov.no_order
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray()[0];

                $m_kpv = new \Model\Storage\KonfirmasiPembayaranVoadip_model();
                $nomor = $m_kpv->getNextNomor();

                $m_kpv->nomor = $nomor;
                $m_kpv->tgl_bayar = $d_conf['tgl_bayar'];
                $m_kpv->periode = trim($d_conf['periode_docin']);
                $m_kpv->perusahaan = $d_conf['perusahaan'];
                $m_kpv->supplier = $d_conf['supplier'];
                $m_kpv->total = $d_conf['total'];
                // $m_kpv->invoice = $d_conf['no_sj'];
                // $m_kpv->rekening = $d_conf['rekening'];
                $m_kpv->save();

                $id = $m_kpv->id;

                $m_kpvd = new \Model\Storage\KonfirmasiPembayaranVoadipDet_model();
                $m_kpvd->id_header = $id;
                $m_kpvd->tgl_sj = $d_conf['tgl_sj'];
                $m_kpvd->kode_unit = $d_conf['id_kab_kota'];
                $m_kpvd->no_order = $d_conf['no_order'];
                $m_kpvd->no_sj = $d_conf['no_sj'];
                $m_kpvd->jumlah = $d_conf['jumlah'];
                $m_kpvd->total = $d_conf['total'];
                $m_kpvd->save();

                // $id_det = $m_kpvd->id;
                // foreach ($v_det['detail'] as $k_det2 => $v_det2) {
                //     $m_kpvd2 = new \Model\Storage\KonfirmasiPembayaranVoadipDet2_model();
                //     $m_kpvd2->id_header = $id_det;
                //     $m_kpvd2->id_gudang = $v_det2['id_gudang'];
                //     $m_kpvd2->kode_brg = $v_det2['kode_brg'];
                //     $m_kpvd2->jumlah = $v_det2['jumlah'];
                //     $m_kpvd2->harga = $v_det2['harga'];
                //     $m_kpvd2->total = $v_det2['total'];
                //     $m_kpvd2->save();
                // }
                
                $d_kpd = $m_kpv->where('id', $id)->first();

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $d_kpd, $deskripsi_log);
            }
        }
    }

    public function listActivity()
    {
        $params = $this->input->get('params');

        $m_terima_voadip = new \Model\Storage\TerimaVoadip_model();
        $d_terima_voadip = $m_terima_voadip->where('id', $params['id'])->with(['logs'])->first()->toArray();

        $data = array(
            'no_sj' => $params['no_sj'],
            'tgl_terima' => $params['tgl_terima'],
            'asal' => $params['asal'],
            'tujuan' => $params['tujuan'],
            'nopol' => $params['nopol'],
            'logs' => $d_terima_voadip['logs']
        );

        $content['data'] = $data;
        $html = $this->load->view('transaksi/penerimaan_voadip/list_activity', $content, true);

        echo $html;
    }

    public function tes()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select kv.no_order, tv.* from terima_voadip tv 
            left join
                kirim_voadip kv 
                on
                    tv.id_kirim_voadip = kv.id
            where
                kv.jenis_kirim = 'opks' and
                tv.tgl_terima >= '2024-02-01' and
                not EXISTS (select * from konfirmasi_pembayaran_voadip_det where no_sj = kv.no_sj)
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $this->insertKonfirmasi($value['id']);
            }
        }
    }
}