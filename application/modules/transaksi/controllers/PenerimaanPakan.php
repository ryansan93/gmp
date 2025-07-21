<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PenerimaanPakan extends Public_Controller {

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
                "assets/transaksi/penerimaan_pakan/js/penerimaan-pakan.js",
            ));
            $this->add_external_css(array(
                "assets/transaksi/penerimaan_pakan/css/penerimaan-pakan.css",
            ));

            $data = $this->includes;

            $list_unit = $this->get_unit();

            $content['akses'] = $this->hakAkses;
            $content['unit'] = $list_unit;

            $a_content['get_sj_not_terima'] = null;
            $a_content['unit'] = $list_unit;

            $content['add_form'] = $this->load->view('transaksi/penerimaan_pakan/add_form', $a_content, TRUE);

            $data['title_menu'] = 'Penerimaan Pakan';
            $data['view'] = $this->load->view('transaksi/penerimaan_pakan/index', $content, TRUE);
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
            $m_tp = new \Model\Storage\TerimaPakan_model();
            $d_tp = $m_tp->where('id', $id)->with(['detail'])->first()->toArray();

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    kp.id,
                    kp.tgl_trans,
                    kp.tgl_kirim,
                    kp.no_order,
                    kp.jenis_kirim,
                    kp.asal,
                    kp.jenis_tujuan,
                    kp.tujuan,
                    kp.ekspedisi,
                    kp.no_polisi,
                    kp.sopir,
                    kp.no_sj,
                    kp.ongkos_angkut,
                    kp.ekspedisi_id,
                    dkp.id_header,
                    dkp.item,
                    sum(dkp.jumlah) as jumlah,
                    dkp.kondisi
                from det_kirim_pakan dkp
                left join
                    kirim_pakan kp
                    on
                        dkp.id_header = kp.id
                where
                    kp.id = ".$d_tp['id_kirim_pakan']."
                group by
                    kp.id,
                    kp.tgl_trans,
                    kp.tgl_kirim,
                    kp.no_order,
                    kp.jenis_kirim,
                    kp.asal,
                    kp.jenis_tujuan,
                    kp.tujuan,
                    kp.ekspedisi,
                    kp.no_polisi,
                    kp.sopir,
                    kp.no_sj,
                    kp.ongkos_angkut,
                    kp.ekspedisi_id,
                    dkp.id_header,
                    dkp.item,
                    dkp.kondisi

            ";
            $d_kp = $m_conf->hydrateRaw( $sql )->toArray();

            $tujuan = null;
            $asal = null;

            $m_supplier = new \Model\Storage\Pelanggan_model();
            $m_peternak = new \Model\Storage\RdimSubmit_model();
            $m_gudang = new \Model\Storage\Gudang_model();
            // ASAL
            if ( $d_kp[0]['jenis_kirim'] == 'opks' ) {
                $d_supplier = $m_supplier->where('nomor', $d_kp[0]['asal'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('id', 'desc')->first();
                $asal = $d_supplier->nama;
            } else if ( $d_kp[0]['jenis_kirim'] == 'opkp' ) {
                $d_peternak = $m_peternak->where('noreg', $d_kp[0]['asal'])->with(['mitra'])->orderBy('id', 'desc')->first();
                $asal = $d_peternak->mitra->dMitra->nama;
            } else if ( $d_kp[0]['jenis_kirim'] == 'opkg' ) {
                $d_gudang = $m_gudang->where('id', $d_kp[0]['asal'])->orderBy('id', 'desc')->first();
                $asal = $d_gudang->nama;
            }

            // TUJUAN
            if ( $d_kp[0]['jenis_tujuan'] == 'peternak' ) {
                $m_rs = new \Model\Storage\RdimSubmit_model();
                $d_rs = $m_rs->where('noreg', $d_kp[0]['tujuan'])->with(['mitra'])->orderBy('id', 'desc')->first();
                if ( !empty($d_rs) ) {
                    $tujuan = $d_rs->mitra->dMitra->nama;
                }
            } else {
                $m_gusang = new \Model\Storage\Gudang_model();
                $d_gudang = $m_gusang->where('id', $d_kp[0]['tujuan'])->orderBy('id', 'desc')->first();
                $tujuan = $d_gudang->nama;
            }

            $a_content['data'] = $d_tp;
            $a_content['data_kp'] = $d_kp;
            $a_content['asal'] = $asal;
            $a_content['tujuan'] = $tujuan;
            $html = $this->load->view('transaksi/penerimaan_pakan/edit_form', $a_content, TRUE);
        } else if ( !empty($id) && empty($resubmit) ) {
            $m_tp = new \Model\Storage\TerimaPakan_model();
            $d_tp = $m_tp->where('id', $id)->with(['detail'])->first()->toArray();

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    kp.id,
                    kp.tgl_trans,
                    kp.tgl_kirim,
                    kp.no_order,
                    kp.jenis_kirim,
                    kp.asal,
                    kp.jenis_tujuan,
                    kp.tujuan,
                    kp.ekspedisi,
                    kp.no_polisi,
                    kp.sopir,
                    kp.no_sj,
                    kp.ongkos_angkut,
                    kp.ekspedisi_id,
                    dkp.id_header,
                    dkp.item,
                    sum(dkp.jumlah) as jumlah,
                    dkp.kondisi
                from det_kirim_pakan dkp
                left join
                    kirim_pakan kp
                    on
                        dkp.id_header = kp.id
                where
                    kp.id = ".$d_tp['id_kirim_pakan']."
                group by
                    kp.id,
                    kp.tgl_trans,
                    kp.tgl_kirim,
                    kp.no_order,
                    kp.jenis_kirim,
                    kp.asal,
                    kp.jenis_tujuan,
                    kp.tujuan,
                    kp.ekspedisi,
                    kp.no_polisi,
                    kp.sopir,
                    kp.no_sj,
                    kp.ongkos_angkut,
                    kp.ekspedisi_id,
                    dkp.id_header,
                    dkp.item,
                    dkp.kondisi

            ";
            $d_kp = $m_conf->hydrateRaw( $sql )->toArray();

            $m_dkp_pp = new \Model\Storage\KirimPakanDetail_model();
            $d_dkp_pp = $m_dkp_pp->where('no_sj_asal', $d_kp[0]['no_sj'])->first();

            $m_rp = new \Model\Storage\ReturPakan_model();
            $d_rp = $m_rp->where('no_order', $d_kp[0]['no_order'])->first();

            $retur = 0;
            if ( $d_rp ) {
                $retur = 1;
            }

            $pp = 0;
            if ( $d_dkp_pp ) {
                $pp = 1;
            }

            $tujuan = null;
            $asal = null;

            $m_supplier = new \Model\Storage\Pelanggan_model();
            $m_peternak = new \Model\Storage\RdimSubmit_model();
            $m_gudang = new \Model\Storage\Gudang_model();
            // ASAL
            if ( $d_kp[0]['jenis_kirim'] == 'opks' ) {
                $d_supplier = $m_supplier->where('nomor', $d_kp[0]['asal'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('id', 'desc')->first();
                $asal = $d_supplier->nama;
            } else if ( $d_kp[0]['jenis_kirim'] == 'opkp' ) {
                $d_peternak = $m_peternak->where('noreg', $d_kp[0]['asal'])->with(['mitra'])->orderBy('id', 'desc')->first();
                $asal = $d_peternak->mitra->dMitra->nama;
            } else if ( $d_kp[0]['jenis_kirim'] == 'opkg' ) {
                $d_gudang = $m_gudang->where('id', $d_kp[0]['asal'])->orderBy('id', 'desc')->first();
                $asal = $d_gudang->nama;
            }

            // TUJUAN
            if ( $d_kp[0]['jenis_tujuan'] == 'peternak' ) {
                $m_rs = new \Model\Storage\RdimSubmit_model();
                $d_rs = $m_rs->where('noreg', $d_kp[0]['tujuan'])->with(['mitra'])->orderBy('id', 'desc')->first();
                if ( !empty($d_rs) ) {
                    $tujuan = $d_rs->mitra->dMitra->nama;
                }
            } else {
                $m_gusang = new \Model\Storage\Gudang_model();
                $d_gudang = $m_gusang->where('id', $d_kp[0]['tujuan'])->orderBy('id', 'desc')->first();
                $tujuan = $d_gudang->nama;
            }

            $a_content['akses'] = $this->hakAkses;
            $a_content['data'] = $d_tp;
            $a_content['data_kp'] = $d_kp;
            $a_content['asal'] = $asal;
            $a_content['tujuan'] = $tujuan;
            $a_content['retur'] = $retur;
            $a_content['pp'] = $pp;
            $html = $this->load->view('transaksi/penerimaan_pakan/view_form', $a_content, TRUE);
        } else {
            $html = $this->load->view('transaksi/penerimaan_pakan/add_form', $a_content, TRUE);
        }

        echo $html;
    }

    public function get_lists()
    {
        $params = $this->input->post('params');

        $kode_unit = $params['kode_unit'];

        $m_terima_pakan = new \Model\Storage\TerimaPakan_model();
        $d_terima_pakan = $m_terima_pakan->whereBetween('tgl_terima', [$params['start_date'], $params['end_date']])->get();
        $data = null;
        if ( $d_terima_pakan ) {
            $d_terima_pakan = $d_terima_pakan->toArray();
            foreach ($d_terima_pakan as $k_tp => $v_tp) {
                $tampil = 0;

        		$m_kp = new \Model\Storage\KirimPakan_model();
                $d_kp = $m_kp->where('id', $v_tp['id_kirim_pakan'])->first();

                if ( $d_kp ) {
                    $d_kp = $d_kp->toArray();

                    if ( $kode_unit != 'all' ) {
                        if ( $d_kp['jenis_kirim'] == 'opks' || $d_kp['jenis_kirim'] == 'opkg' ) {
                            if ( $kode_unit != 'all' ) {
                                if ( stristr($d_kp['no_order'], $kode_unit) !== false ) {
                                    $tampil = 1;
                                }
                            } else {
                                $tampil = 1;
                            }
                        } else if ( $d_kp['jenis_kirim'] == 'opkp' ) {
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
                                        rs.noreg = '".$d_kp['asal']."'
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
                                        rs.noreg = '".$d_kp['tujuan']."'
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
                        if ( $d_kp['jenis_kirim'] == 'opks' ) {
                            $d_supplier = $m_supplier->where('nomor', $d_kp['asal'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('id', 'desc')->first();
                            $asal = $d_supplier->nama;
                        } else if ( $d_kp['jenis_kirim'] == 'opkp' ) {
                            $d_peternak = $m_peternak->where('noreg', $d_kp['asal'])->with(['mitra'])->orderBy('id', 'desc')->first();
                            $asal = $d_peternak->mitra->dMitra->nama;
                        } else if ( $d_kp['jenis_kirim'] == 'opkg' ) {
                            $d_gudang = $m_gudang->where('id', $d_kp['asal'])->orderBy('id', 'desc')->first();
                            $asal = $d_gudang->nama;
                        }
                        // TUJUAN
                        if ( $d_kp['jenis_tujuan'] == 'peternak' ) {
                            $d_peternak = $m_peternak->where('noreg', $d_kp['tujuan'])->with(['mitra'])->orderBy('id', 'desc')->first();
                            if ( !empty($d_peternak) ) {
                                $tujuan = $d_peternak->mitra->dMitra->nama.' ('.$d_kp['tujuan'].')';
                            }
                        } else if ( $d_kp['jenis_tujuan'] == 'gudang' ) {
                            $d_gudang = $m_gudang->where('id', $d_kp['tujuan'])->orderBy('id', 'desc')->first();
                            $tujuan = $d_gudang->nama;
                        }

                        $key = str_replace('-', '', $v_tp['tgl_terima']).'|'.$v_tp['id_kirim_pakan'];
                        $data[ $key ] = array(
                            'id' => $v_tp['id'],
                            'no_sj' => $d_kp['no_sj'],
                            'tgl_terima' => $v_tp['tgl_terima'],
                            'asal' => $asal,
                            'tujuan' => $tujuan,
                            'nopol' => $d_kp['no_polisi'],
                        );
                    }
                }
            }
        }

        if ( !empty($data) ) {
            krsort($data);
        }

        $content['akses'] = $this->hakAkses;
        $content['data'] = $data;
        $html = $this->load->view('transaksi/penerimaan_pakan/list', $content, true);

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

        $m_kp = new \Model\Storage\KirimPakan_model();        
        $d_kp = $m_kp->select('id', 'no_sj')->whereBetween('tgl_kirim', [$tgl_kirim, $tgl_kirim])->where('no_order', 'like', '%'.$unit.'%')->with(['terima'])->orderBy('no_sj', 'asc')->get();
        if ( $d_kp->count() > 0 ) {
            $d_kp = $d_kp->toArray();

            foreach ($d_kp as $k_kp => $v_kp) {
                if ( empty($v_kp['terima']) ) {
                    array_push($data, $v_kp);
                }
            }
        } 
        // else {
        //     $d_kp_kosong = $m_kp->with(['detail'])->get();
        //     if ( $d_kp_kosong->count() > 0 ) {
        //         $data = $d_kp_kosong->toArray();
        //     }
        // }

        $this->result['content'] = $data;

        display_json( $this->result );
    }

    public function get_data_by_sj()
    {
        $id_kirim = $this->input->post('id_kirim');

        $m_kp = new \Model\Storage\KirimPakan_model();
        $d_kp = $m_kp->where('id', $id_kirim)->first()->toArray();

        $detail = null;
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                dkp.id_header,
                dkp.item,
                dkp.kondisi,
                brg.nama,
                sum(dkp.jumlah) as jumlah
            from det_kirim_pakan dkp
            left join
                (
                    select b1.* from barang b1
                    right join
                        (select max(id) as id, kode from barang group by kode) b2
                        on
                            b1.id = b2.id
                ) brg
                on
                    brg.kode = dkp.item
            where
                dkp.id_header = ".$id_kirim."
            group by
                dkp.id_header,
                dkp.item,
                dkp.kondisi,
                brg.nama
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $detail = $d_conf->toArray();
        }

        $tujuan = null;
        $asal = null;

        $m_supplier = new \Model\Storage\Pelanggan_model();
        $m_peternak = new \Model\Storage\RdimSubmit_model();
        $m_gudang = new \Model\Storage\Gudang_model();
        // ASAL
        if ( $d_kp['jenis_kirim'] == 'opks' ) {
            $d_supplier = $m_supplier->where('nomor', $d_kp['asal'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('id', 'desc')->first();
            $asal = $d_supplier->nama;
        } else if ( $d_kp['jenis_kirim'] == 'opkp' ) {
            $d_peternak = $m_peternak->where('noreg', $d_kp['asal'])->with(['dMitraMapping'])->orderBy('id', 'desc')->first();
            $asal = $d_peternak->dMitraMapping->dMitra->nama;
        } else if ( $d_kp['jenis_kirim'] == 'opkg' ) {
            $d_gudang = $m_gudang->where('id', $d_kp['asal'])->orderBy('id', 'desc')->first();
            $asal = $d_gudang->nama;
        }

        // TUJUAN
        if ( $d_kp['jenis_tujuan'] == 'peternak' ) {
            $m_rs = new \Model\Storage\RdimSubmit_model();
            $d_rs = $m_rs->where('noreg', $d_kp['tujuan'])->with(['mitra'])->orderBy('id', 'desc')->first();
            $tujuan = $d_rs->mitra->dMitra->nama;
        } else {
            $m_gusang = new \Model\Storage\Gudang_model();
            $d_gudang = $m_gusang->where('id', $d_kp['tujuan'])->orderBy('id', 'desc')->first();
            $tujuan = $d_gudang->nama;
        }

        $jenis_kirim = array(
            'opks' => 'Order Pabrik (OPKS)',
            'opkp' => 'Dari Peternak (OPKP)',
            'opkg' => 'Dari Gudang (OPKG)',
        );

        $data = array(
            'no_pol' => $d_kp['no_polisi'],
            'ekspedisi' => $d_kp['ekspedisi'],
            'sopir' => $d_kp['sopir'],
            'jenis_kirim' => $jenis_kirim[$d_kp['jenis_kirim']],
            'no_order' => strtoupper($d_kp['no_order']),
            'tgl_kirim' => tglIndonesia($d_kp['tgl_kirim'], '-', ' '),
            'asal' => $asal,
            'tujuan' => $tujuan,
            'detail' => $detail,
        );

        $this->result['status'] = 1;
        $this->result['content'] = $data;

        display_json($this->result);
    }

    public function save()
    {
        $params = json_decode($this->input->post('data'),TRUE);

        try {
            $path_name = null;

            $m_kp = new \Model\Storage\KirimPakan_model();
            $d_kp = $m_kp->where('id', $params['id_kirim_pakan'])->first();

            $no_bbm = null;
            if ( $d_kp->jenis_kirim == 'opks' ) {
                $no_bbm = 'BBM/PKN/S'.str_replace('OPK', '', $d_kp->no_order);
            } else if ( $d_kp->jenis_kirim == 'opkg' ) {
                $no_bbm = 'BBM/PKN/G'.str_replace('OP', '', $d_kp->no_order);
            } else if ( $d_kp->jenis_kirim == 'opkp' ) {
                $no_bbm = 'BBM/PKN/P'.str_replace('OP', '', $d_kp->no_order);
            }

            $m_terima_pakan = new \Model\Storage\TerimaPakan_model();
            $now = $m_terima_pakan->getDate();

            $m_terima_pakan->id_kirim_pakan = $params['id_kirim_pakan'];
            $m_terima_pakan->tgl_trans = $now['waktu'];
            $m_terima_pakan->tgl_terima = $params['tgl_terima'];
            $m_terima_pakan->path = $path_name;
            $m_terima_pakan->no_bbm = $no_bbm;
            $m_terima_pakan->save();

            $id_terima = $m_terima_pakan->id;

            foreach ($params['detail'] as $k_detail => $v_detail) {
                $m_terima_pakan_detail = new \Model\Storage\TerimaPakanDetail_model();
                $m_terima_pakan_detail->id_header = $id_terima;
                $m_terima_pakan_detail->item = $v_detail['barang'];
                $m_terima_pakan_detail->jumlah = $v_detail['jumlah'];
                $m_terima_pakan_detail->kondisi = $v_detail['kondisi'];
                $m_terima_pakan_detail->save();
            }

            $d_terima_pakan = $m_terima_pakan->where('id', $id_terima)->with(['detail'])->first();

            $deskripsi_log_terima_pakan = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_terima_pakan, $deskripsi_log_terima_pakan);

            $this->result['status'] = 1;
            // $this->result['content'] = array('id_terima' => $id_terima);
            $this->result['content'] = array(
                'id' => $id_terima,
                'tanggal' => $params['tgl_terima'],
                'delete' => 0,
                'message' => 'Data Penerimaan Pakan berhasil di simpan.',
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

                $m_kp = new \Model\Storage\KirimPakan_model();
                $d_kp = $m_kp->where('id', $params['id_kirim_pakan'])->first();

                $no_bbm = null;
                if ( $d_kp->jenis_kirim == 'opks' ) {
                    $no_bbm = 'BBM/PKN/S'.str_replace('OPK', '', $d_kp->no_order);
                } else if ( $d_kp->jenis_kirim == 'opkg' ) {
                    $no_bbm = 'BBM/PKN/G'.str_replace('OP', '', $d_kp->no_order);
                } else if ( $d_kp->jenis_kirim == 'opkp' ) {
                    $no_bbm = 'BBM/PKN/P'.str_replace('OP', '', $d_kp->no_order);
                }

                $m_terima_pakan = new \Model\Storage\TerimaPakan_model();
                $d_terima_pakan_old = $m_terima_pakan->where('id', $id_header)->first();

                $now = $m_terima_pakan->getDate();

                $m_terima_pakan->where('id', $params['id'])->update(
                    array(
                        'id_kirim_pakan' => $params['id_kirim_pakan'],
                        'tgl_trans' => $now['waktu'],
                        'tgl_terima' => $params['tgl_terima'],
                        'path' => $path_name,
                        'no_bbm' => $no_bbm
                    )
                );

                $m_terima_pakan_detail = new \Model\Storage\TerimaPakanDetail_model();
                $m_terima_pakan_detail->where('id_header', $id_header)->delete();

                foreach ($params['detail'] as $k_detail => $v_detail) {
                    $m_terima_pakan_detail = new \Model\Storage\TerimaPakanDetail_model();
                    $m_terima_pakan_detail->id_header = $id_header;
                    $m_terima_pakan_detail->item = $v_detail['barang'];
                    $m_terima_pakan_detail->jumlah = $v_detail['jumlah'];
                    $m_terima_pakan_detail->kondisi = $v_detail['kondisi'];
                    $m_terima_pakan_detail->save();
                }

                $d_terima_pakan = $m_terima_pakan->where('id', $id_header)->with(['detail'])->first();

                $tgl_trans = $d_terima_pakan->tgl_terima;
                if ( $d_terima_pakan_old->tgl_terima < $tgl_trans ) {
                    $tgl_trans = $d_terima_pakan_old->tgl_terima;
                }

                // $conf = new \Model\Storage\Conf();
                // $sql = "EXEC hitung_stok_pakan_by_transaksi 'terima_pakan', '".$d_terima_pakan->id."', '".$tgl_trans."', 0";

                // $d_conf = $conf->hydrateRaw($sql);

                $deskripsi_log_terima_pakan = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $d_terima_pakan, $deskripsi_log_terima_pakan);

                $this->result['status'] = 1;
                $this->result['content'] = array(
                    'id' => $d_terima_pakan->id,
                    'tanggal' => $tgl_trans,
                    'delete' => 0,
                    'message' => 'Data Penerimaan Pakan berhasil di ubah.',
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
            $m_terima_pakan = new \Model\Storage\TerimaPakan_model();
            $now = $m_terima_pakan->getDate();

            $d_terima_pakan = $m_terima_pakan->where('id', $params['id'])->with(['detail'])->first();

            $deskripsi_log_terima_pakan = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_terima_pakan, $deskripsi_log_terima_pakan);

            // $conf = new \Model\Storage\Conf();
            // $sql = "EXEC hitung_stok_pakan_by_transaksi 'terima_pakan', '".$d_terima_pakan->id."', '".$d_terima_pakan->tgl_terima."', 1";

            // $d_conf = $conf->hydrateRaw($sql);

            $this->result['status'] = 1;
            $this->result['content'] = array(
                'id' => $d_terima_pakan->id,
                'tanggal' => $d_terima_pakan->tgl_terima,
                'delete' => 1,
                'message' => 'Data Penerimaan Pakan berhasil di hapus.',
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
        $noreg1 = isset($params['noreg1']) ? $params['noreg1'] : null;
        $noreg2 = isset($params['noreg2']) ? $params['noreg2'] : null;;

        try {
            $noreg1 = null;
            $noreg2 = null;

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
					case
						when kp.jenis_kirim = 'opkp' then
							kp.asal
						else
							kp.tujuan
					end as noreg1,
					case
						when kp.jenis_kirim = 'opkp' then
							kp.tujuan
						else
							null
					end as noreg2,
                    kp.jenis_kirim
				from terima_pakan tp
				left join
					kirim_pakan kp
					on
						tp.id_kirim_pakan = kp.id
				where
					tp.id = '".$id."'
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray()[0];

                $noreg1 = $d_conf['noreg1'];
                $noreg2 = $d_conf['noreg2'];
            }

            $this->insertKonfirmasi( $id, $delete );

            $conf = new \Model\Storage\Conf();
            $sql = "EXEC hitung_stok_pakan_by_transaksi 'terima_pakan', '".$id."', '".$tanggal."', ".$delete.", ".$status_jurnal."";
            $d_conf = $conf->hydrateRaw($sql);

            $conf = new \Model\Storage\Conf();
            $sql = "EXEC hitung_stok_siklus 'pakan', 'terima_pakan', '".$id."', '".$tanggal."', ".$delete.", '".$noreg1."', '".$noreg2."'";
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
            select kp.* from terima_pakan tp
            left join
                kirim_pakan kp
                on
                    tp.id_kirim_pakan = kp.id
            where
                tp.id = '".$id_terima."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );
        
        $no_order = null;
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray()[0];

            $no_order = $d_conf['no_order'];
        }

        $m_kppd = new \Model\Storage\KonfirmasiPembayaranPakanDet_model();
        $d_kppd = $m_kppd->where('no_order', $no_order)->first();

        if ( $d_kppd ) {
            $m_kppd2 = new \Model\Storage\KonfirmasiPembayaranPakanDet2_model();
            $m_kppd2->where('id_header', $d_kppd->id)->delete();

            $m_kppd = new \Model\Storage\KonfirmasiPembayaranPakanDet_model();
            $m_kppd->where('id', $d_kppd->id)->delete();

            $m_kpp = new \Model\Storage\KonfirmasiPembayaranPakan_model();
            $m_kpp->where('id', $d_kppd->id_header)->delete();
        }

        if ( $delete == 0 ) {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    tp.tgl_terima as tgl_bayar,
                    tp.tgl_terima as periode_docin,
                    op.perusahaan,
                    op.supplier,
                    sum(dtp.jumlah * op.harga) as total,
                    kp.no_sj,
                    kp.tgl_kirim as tgl_sj,
                    SUBSTRING(op.no_order, 5, 3) as id_kab_kota,
                    op.no_order,
                    sum(dtp.jumlah) as jumlah
                from det_terima_pakan dtp
                left join
                    (
                        select tp1.* from terima_pakan tp1
                        right join
                            (select max(id) as id, id_kirim_pakan from terima_pakan group by id_kirim_pakan) tp2
                            on
                                tp1.id = tp2.id
                    ) tp
                    on
                        dtp.id_header = tp.id
                left join
                    kirim_pakan kp
                    on
                        tp.id_kirim_pakan = kp.id
                left join
                    (
                        select 
                            opd.*, 
                            op.no_order, 
                            op.tgl_trans, 
                            op.rcn_kirim, 
                            op.supplier 
                        from order_pakan_detail opd
                        left join
                            (
                                select op1.* from order_pakan op1
                                right join
                                    (select max(id) as id, no_order from order_pakan group by no_order) op2
                                    on
                                        op1.id = op2.id
                            ) op
                            on
                                opd.id_header = op.id
                    ) op
                    on
                        kp.no_order = op.no_order and
                        dtp.item = op.barang
                where
                    kp.jenis_kirim = 'opks' and
                    op.no_order = '".$no_order."'
                group by
                    tp.tgl_terima,
                    op.perusahaan,
                    op.supplier,
                    kp.no_sj,
                    kp.tgl_kirim,
                    op.no_order
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray()[0];

                $m_kpp = new \Model\Storage\KonfirmasiPembayaranPakan_model();
                $nomor = $m_kpp->getNextNomor();

                $m_kpp->nomor = $nomor;
                $m_kpp->tgl_bayar = $d_conf['tgl_bayar'];
                $m_kpp->periode = trim($d_conf['periode_docin']);
                $m_kpp->perusahaan = $d_conf['perusahaan'];
                $m_kpp->supplier = $d_conf['supplier'];
                $m_kpp->total = $d_conf['total'];
                $m_kpp->invoice = $d_conf['no_sj'];
                // $m_kpp->rekening = $d_conf['rekening'];
                $m_kpp->save();

                $id = $m_kpp->id;

                $m_kppd = new \Model\Storage\KonfirmasiPembayaranPakanDet_model();
                $m_kppd->id_header = $id;
                $m_kppd->tgl_sj = $d_conf['tgl_sj'];
                $m_kppd->kode_unit = $d_conf['id_kab_kota'];
                $m_kppd->no_order = $d_conf['no_order'];
                $m_kppd->no_sj = $d_conf['no_sj'];
                $m_kppd->jumlah = $d_conf['jumlah'];
                $m_kppd->total = $d_conf['total'];
                $m_kppd->save();

                // $id_det = $m_kppd->id;
                // foreach ($v_det['detail'] as $k_det2 => $v_det2) {
                //     $m_kppd2 = new \Model\Storage\KonfirmasiPembayaranPakanDet2_model();
                //     $m_kppd2->id_header = $id_det;
                //     $m_kppd2->id_gudang = $v_det2['id_gudang'];
                //     $m_kppd2->kode_brg = $v_det2['kode_brg'];
                //     $m_kppd2->jumlah = $v_det2['jumlah'];
                //     $m_kppd2->harga = $v_det2['harga'];
                //     $m_kppd2->total = $v_det2['total'];
                //     $m_kppd2->save();
                // }
                
                $d_kpd = $m_kpp->where('id', $id)->first();

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $d_kpd, $deskripsi_log);
            }
        }
    }

    // public function hitungStokAwal()
    // {
    //     $params = $this->input->post('params');

    //     try {
    //         $id_terima = $params['id_terima'];

    //         $date = date('Y-m-d');
            
    //         $m_stok = new \Model\Storage\Stok_model();
    //         $now = $m_stok->getDate();
    //         $d_stok = $m_stok->where('periode', $date)->first();

    //         $stok_id = null;
    //         if ( $d_stok ) {
    //             $stok_id = $d_stok->id;

    //             $this->hitungStok($id_terima, $stok_id);
    //         } else {
    //             $m_stok->periode = $date;
    //             $m_stok->user_proses = $this->userid;
    //             $m_stok->tgl_proses = $now['waktu'];
    //             $m_stok->save();

    //             $stok_id = $m_stok->id;

    //             $conf = new \Model\Storage\Conf();
    //             $sql = "EXEC get_data_stok_pakan_by_tanggal @date = '$date'";

    //             $d_conf = $conf->hydrateRaw($sql);

    //             if ( $d_conf->count() > 0 ) {
    //                 $d_conf = $d_conf->toArray();
    //                 $jml_data = count($d_conf);
    //                 $idx = 0;
    //                 foreach ($d_conf as $k_conf => $v_conf) {
    //                     $m_ds = new \Model\Storage\DetStok_model();
    //                     $m_ds->id_header = $stok_id;
    //                     $m_ds->tgl_trans = $v_conf['tgl_trans'];
    //                     $m_ds->kode_gudang = $v_conf['kode_gudang'];
    //                     $m_ds->kode_barang = $v_conf['kode_barang'];
    //                     $m_ds->jumlah = $v_conf['jumlah'];
    //                     $m_ds->hrg_jual = $v_conf['hrg_jual'];
    //                     $m_ds->hrg_beli = $v_conf['hrg_beli'];
    //                     $m_ds->kode_trans = $v_conf['kode_trans'];
    //                     $m_ds->jenis_barang = $v_conf['jenis_barang'];
    //                     $m_ds->jenis_trans = $v_conf['jenis_trans'];
    //                     $m_ds->jml_stok = $v_conf['jml_stok'];
    //                     $m_ds->save();

    //                     $idx++;

    //                     if ( $jml_data == $idx ) {
    //                         $this->hitungStok($id_terima, $stok_id);
    //                     }
    //                 }
    //             }
    //         }

    //         $this->result['status'] = 1;
    //         $this->result['message'] = 'Data Penerimaan Pakan berhasil di simpan.';
    //     } catch (\Illuminate\Database\QueryException $e) {
    //         $this->result['message'] = "Gagal : " . $e->getMessage();
    //     }

    //     display_json($this->result);
    // }

    // public function hitungStok($id_terima, $stok_id)
    // {
    //     $m_terima_pakan = new \Model\Storage\TerimaPakan_model();
    //     $d_terima_pakan = $m_terima_pakan->where('id', $id_terima)->with(['detail'])->first()->toArray();

    //     $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
    //     $d_kirim_pakan = $m_kirim_pakan->where('id', $d_terima_pakan['id_kirim_pakan'])->first()->toArray();

    //     $total = 0;

    //     foreach ($d_terima_pakan['detail'] as $k_detail => $v_detail) {
    //         if ( stristr($d_kirim_pakan['jenis_tujuan'], 'gudang') !== FALSE ) {
    //             if ( stristr($d_kirim_pakan['jenis_kirim'], 'opkg') === false ) {
    //                 // MASUK STOK GUDANG
    //                 $m_order_pakan = new \Model\Storage\OrderPakan_model();
    //                 $d_order_pakan = $m_order_pakan->where('no_order', $d_kirim_pakan['no_order'])->first();

    //                 $harga_jual = 0;
    //                 $harga_beli = 0;
    //                 if ( !empty($d_order_pakan) ) {
    //                     $m_dorder_pakan = new \Model\Storage\OrderPakanDetail_model();
    //                     $d_dorder_pakan = $m_dorder_pakan->where('id_header', $d_order_pakan->id)->where('barang', trim($v_detail['item']))->first();

    //                     $harga_jual = $d_dorder_pakan->harga_jual;
    //                     $harga_beli = $d_dorder_pakan->harga;
    //                 }

    //                 // MASUk STOK GUDANG
    //                 $m_dstok = new \Model\Storage\DetStok_model();
    //                 $m_dstok->id_header = $stok_id;
    //                 $m_dstok->tgl_trans = $d_terima_pakan['tgl_terima'];
    //                 $m_dstok->kode_gudang = $d_kirim_pakan['tujuan'];
    //                 $m_dstok->kode_barang = $v_detail['item'];
    //                 $m_dstok->jumlah = $v_detail['jumlah'];
    //                 $m_dstok->hrg_jual = $harga_jual;
    //                 $m_dstok->hrg_beli = $harga_beli;
    //                 $m_dstok->kode_trans = $d_kirim_pakan['no_order'];
    //                 $m_dstok->jenis_barang = 'pakan';
    //                 $m_dstok->jenis_trans = 'ORDER';
    //                 $m_dstok->jml_stok = $v_detail['jumlah'];
    //                 $m_dstok->save();

    //                 $total += $harga_beli * $v_detail['jumlah'];
    //             } else {
    //                 // KELUAR STOK GUDANG
    //                 $nilai_beli = 0;
    //                 $nilai_jual = 0;
    //                 $jml_keluar = $v_detail['jumlah'];
    //                 while ($jml_keluar > 0) {
    //                     $m_dstok = new \Model\Storage\DetStok_model();
    //                     $sql = "
    //                         select top 1 * from det_stok ds 
    //                         where
    //                             ds.id_header = ".$stok_id." and 
    //                             ds.kode_gudang = ".$d_kirim_pakan['asal']." and 
    //                             ds.kode_barang = '".$v_detail['item']."' and 
    //                             ds.jml_stok > 0
    //                         order by
    //                             ds.jenis_trans desc,
    //                             ds.tgl_trans asc,
    //                             ds.kode_trans asc,
    //                             ds.id asc
    //                     ";

    //                     $d_dstok = $m_dstok->hydrateRaw($sql);

    //                     if ( $d_dstok->count() > 0 ) {
    //                         $d_dstok = $d_dstok->toArray()[0];

    //                         $harga_jual = $d_dstok['hrg_jual'];
    //                         $harga_beli = $d_dstok['hrg_beli'];

    //                         $jml_stok = $d_dstok['jml_stok'];
    //                         if ( $jml_stok > $jml_keluar ) {
    //                             $jml_stok = $jml_stok - $jml_keluar;
    //                             $nilai_beli += $jml_keluar*$harga_beli;
    //                             $nilai_jual += $jml_keluar*$harga_jual;

    //                             $m_dstokt = new \Model\Storage\DetStokTrans_model();
    //                             $m_dstokt->id_header = $d_dstok['id'];
    //                             $m_dstokt->kode_trans = $d_kirim_pakan['no_order'];
    //                             $m_dstokt->jumlah = $jml_keluar;
    //                             $m_dstokt->kode_barang = $v_detail['item'];
    //                             $m_dstokt->save();

    //                             // MASUK STOK GUDANG
    //                             $m_dstok = new \Model\Storage\DetStok_model();
    //                             $m_dstok->id_header = $stok_id;
    //                             $m_dstok->tgl_trans = $d_terima_pakan['tgl_terima'];
    //                             $m_dstok->kode_gudang = $d_kirim_pakan['tujuan'];
    //                             $m_dstok->kode_barang = $v_detail['item'];
    //                             $m_dstok->jumlah = $jml_keluar;
    //                             $m_dstok->hrg_jual = $harga_jual;
    //                             $m_dstok->hrg_beli = $harga_beli;
    //                             $m_dstok->kode_trans = $d_kirim_pakan['no_order'];
    //                             $m_dstok->jenis_barang = 'pakan';
    //                             $m_dstok->jenis_trans = 'ORDER';
    //                             $m_dstok->jml_stok = $jml_keluar;
    //                             $m_dstok->save();

    //                             $total += $harga_beli * $jml_keluar;

    //                             $jml_keluar = 0;
    //                         } else {
    //                             $jml_keluar = $jml_keluar - $d_dstok['jml_stok'];
    //                             $nilai_beli += $d_dstok['jml_stok']*$harga_beli;
    //                             $nilai_jual += $d_dstok['jml_stok']*$harga_jual;

    //                             $m_dstokt = new \Model\Storage\DetStokTrans_model();
    //                             $m_dstokt->id_header = $d_dstok['id'];
    //                             $m_dstokt->kode_trans = $d_kirim_pakan['no_order'];
    //                             $m_dstokt->jumlah = $d_dstok['jml_stok'];
    //                             $m_dstokt->kode_barang = $v_detail['item'];
    //                             $m_dstokt->save();

    //                             // MASUK STOK GUDANG
    //                             $m_dstok = new \Model\Storage\DetStok_model();
    //                             $m_dstok->id_header = $stok_id;
    //                             $m_dstok->tgl_trans = $d_terima_pakan['tgl_terima'];
    //                             $m_dstok->kode_gudang = $d_kirim_pakan['tujuan'];
    //                             $m_dstok->kode_barang = $v_detail['item'];
    //                             $m_dstok->jumlah = $d_dstok['jml_stok'];
    //                             $m_dstok->hrg_jual = $harga_jual;
    //                             $m_dstok->hrg_beli = $harga_beli;
    //                             $m_dstok->kode_trans = $d_kirim_pakan['no_order'];
    //                             $m_dstok->jenis_barang = 'pakan';
    //                             $m_dstok->jenis_trans = 'ORDER';
    //                             $m_dstok->jml_stok = $d_dstok['jml_stok'];
    //                             $m_dstok->save();

    //                             $total += $harga_beli * $d_dstok['jml_stok'];

    //                             $jml_stok = 0;
    //                         }
    //                         $m_dstok->where('id', $d_dstok['id'])->update(
    //                             array(
    //                                 'jml_stok' => $jml_stok
    //                             )
    //                         );
    //                     } else {
    //                         $jml_keluar = 0;
    //                     }
    //                 }
    //             }
    //         } else {
    //             if ( stristr($d_kirim_pakan['jenis_kirim'], 'opkg') !== FALSE && stristr($d_kirim_pakan['jenis_tujuan'], 'gudang') === FALSE ) {
    //                 // KELUAR STOK GUDANG
    //                 $nilai_beli = 0;
    //                 $nilai_jual = 0;
    //                 $jml_keluar = $v_detail['jumlah'];
    //                 while ($jml_keluar > 0) {
    //                     $m_dstok = new \Model\Storage\DetStok_model();
    //                     $sql = "
    //                         select top 1 * from det_stok ds 
    //                         where
    //                             ds.id_header = ".$stok_id." and 
    //                             ds.kode_gudang = ".$d_kirim_pakan['asal']." and 
    //                             ds.kode_barang = '".$v_detail['item']."' and 
    //                             ds.jml_stok > 0
    //                         order by
    //                             ds.jenis_trans desc,
    //                             ds.tgl_trans asc,
    //                             ds.kode_trans asc,
    //                             ds.id asc
    //                     ";

    //                     $d_dstok = $m_dstok->hydrateRaw($sql);

    //                     if ( $d_dstok->count() > 0 ) {
    //                         $d_dstok = $d_dstok->toArray()[0];

    //                         $harga_jual = $d_dstok['hrg_jual'];
    //                         $harga_beli = $d_dstok['hrg_beli'];

    //                         $jml_stok = $d_dstok['jml_stok'];
    //                         if ( $jml_stok > $jml_keluar ) {
    //                             $jml_stok = $jml_stok - $jml_keluar;
    //                             $nilai_beli += $jml_keluar*$harga_beli;
    //                             $nilai_jual += $jml_keluar*$harga_jual;

    //                             $m_dstokt = new \Model\Storage\DetStokTrans_model();
    //                             $m_dstokt->id_header = $d_dstok['id'];
    //                             $m_dstokt->kode_trans = $d_kirim_pakan['no_order'];
    //                             $m_dstokt->jumlah = $jml_keluar;
    //                             $m_dstokt->kode_barang = $v_detail['item'];
    //                             $m_dstokt->save();

    //                             $total += $harga_beli * $jml_keluar;

    //                             $jml_keluar = 0;
    //                         } else {
    //                             $jml_keluar = $jml_keluar - $d_dstok['jml_stok'];
    //                             $nilai_beli += $d_dstok['jml_stok']*$harga_beli;
    //                             $nilai_jual += $d_dstok['jml_stok']*$harga_jual;

    //                             $m_dstokt = new \Model\Storage\DetStokTrans_model();
    //                             $m_dstokt->id_header = $d_dstok['id'];
    //                             $m_dstokt->kode_trans = $d_kirim_pakan['no_order'];
    //                             $m_dstokt->jumlah = $d_dstok['jml_stok'];
    //                             $m_dstokt->kode_barang = $v_detail['item'];
    //                             $m_dstokt->save();

    //                             $total += $harga_beli * $d_dstok['jml_stok'];

    //                             $jml_stok = 0;
    //                         }
    //                         $m_dstok->where('id', $d_dstok['id'])->update(
    //                             array(
    //                                 'jml_stok' => $jml_stok
    //                             )
    //                         );
    //                     } else {
    //                         $jml_keluar = 0;
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     $m_conf = new \Model\Storage\Conf();
    //     $sql = "exec insert_jurnal 'PAKAN', '".$d_kirim_pakan['no_order']."', NULL, ".$total.", 'terima_pakan', ".$id_terima.", NULL, 1";

    //     $d_conf = $m_conf->hydrateRaw( $sql );
    // }

    public function listActivity()
    {
        $params = $this->input->get('params');

        $m_terima_pakan = new \Model\Storage\TerimaPakan_model();
        $d_terima_pakan = $m_terima_pakan->where('id', $params['id'])->with(['logs'])->first()->toArray();

        $data = array(
            'no_sj' => $params['no_sj'],
            'tgl_terima' => $params['tgl_terima'],
            'asal' => $params['asal'],
            'tujuan' => $params['tujuan'],
            'nopol' => $params['nopol'],
            'logs' => $d_terima_pakan['logs']
        );

        $content['data'] = $data;
        $html = $this->load->view('transaksi/penerimaan_pakan/list_activity', $content, true);

        echo $html;
    }

    public function tes()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select kp.no_order, tp.* from terima_pakan tp 
            left join
                kirim_pakan kp
                on
                    tp.id_kirim_pakan = kp.id
            where
                kp.jenis_kirim = 'opks' and
                tp.tgl_terima >= '2024-02-27' and
                not EXISTS (select * from konfirmasi_pembayaran_pakan_det where no_sj = kp.no_sj)
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