<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PengirimanVoadip extends Public_Controller {

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
                "assets/select2/js/select2.min.js",
                "assets/transaksi/pengiriman_voadip/js/pengiriman-voadip.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/pengiriman_voadip/css/pengiriman-voadip.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['unit'] = $this->get_unit();

            $a_content['order_voadip'] = null;
            $a_content['gudang_asal'] = $this->get_gudang_asal();
            $a_content['gudang_tujuan'] = $this->get_gudang_tujuan();
            $a_content['peternak'] = null;
            $a_content['voadip'] = $this->get_voadip();
            $a_content['unit'] = $this->get_unit();

            $content['add_form'] = $this->load->view('transaksi/pengiriman_voadip/add_form', $a_content, TRUE);

            $data['title_menu'] = 'Pengiriman Voadip';
            $data['view'] = $this->load->view('transaksi/pengiriman_voadip/index', $content, TRUE);
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

    public function get_lists()
    {
        $params = $this->input->post('params');

        $data = null;

        $kode_unit = $params['kode_unit'];

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                kv.*,
                tv.tgl_terima
            from kirim_voadip kv
            left join
                terima_voadip tv
                on
                    kv.id = tv.id_kirim_voadip
            where
                kv.tgl_kirim between '".$params['start_date']."' and '".$params['end_date']."'
        ";

        $d_kirim_voadip = $m_conf->hydrateRaw( $sql );

        if ( $d_kirim_voadip->count() > 0 ) {
            $d_kirim_voadip = $d_kirim_voadip->toArray();
            foreach ($d_kirim_voadip as $k_kp => $v_kp) {
                $tampil = 0;

                if ( $v_kp['jenis_kirim'] == 'opks' || $v_kp['jenis_kirim'] == 'opkg' ) {
                    if ( $kode_unit != 'all' ) {
                        if ( stristr($v_kp['no_order'], $kode_unit) ) {
                            $tampil = 1;
                        }
                    } else {
                        $tampil = 1;
                    }
                } else if ( $v_kp['jenis_kirim'] == 'opkp' ) {
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
                                rs.noreg = '".$v_kp['asal']."'
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
                                rs.noreg = '".$v_kp['tujuan']."'
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

                if ( $tampil == 1 ) {
                    $asal = null;
                    $tujuan = null;

                    $m_supplier = new \Model\Storage\Pelanggan_model();
                    $m_peternak = new \Model\Storage\RdimSubmit_model();
                    $m_gudang = new \Model\Storage\Gudang_model();
                    // ASAL
                    if ( $v_kp['jenis_kirim'] == 'opks' ) {
                        $d_supplier = $m_supplier->where('nomor', $v_kp['asal'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('id', 'desc')->first();
                        $asal = $d_supplier->nama;
                    } else if ( $v_kp['jenis_kirim'] == 'opkp' ) {
                        $d_peternak = $m_peternak->where('noreg', $v_kp['asal'])->with(['mitra'])->orderBy('id', 'desc')->first();
                        $asal = $d_peternak->mitra->dMitra->nama;
                    } else if ( $v_kp['jenis_kirim'] == 'opkg' ) {
                        $d_gudang = $m_gudang->where('id', $v_kp['asal'])->orderBy('id', 'desc')->first();
                        $asal = $d_gudang->nama;
                    }
                    // TUJUAN
                    if ( $v_kp['jenis_tujuan'] == 'peternak' ) {
                        $d_peternak = $m_peternak->where('noreg', $v_kp['tujuan'])->with(['mitra'])->orderBy('id', 'desc')->first();
                        $tujuan = !empty($d_peternak) ? $d_peternak->mitra->dMitra->nama.' ('.$v_kp['tujuan'].')' : null;
                    } else if ( $v_kp['jenis_tujuan'] == 'gudang' ) {
                        $d_gudang = $m_gudang->where('id', $v_kp['tujuan'])->orderBy('id', 'desc')->first();
                        $tujuan = $d_gudang->nama;
                    }

                    $key = str_replace('-', '', $v_kp['tgl_kirim']).'|'.$v_kp['id'];
                    $data[ $key ] = array(
                        'id' => $v_kp['id'],
                        'no_order' => $v_kp['no_order'],
                        'tgl_kirim' => $v_kp['tgl_kirim'],
                        'asal' => $asal,
                        'tujuan' => $tujuan,
                        'nopol' => $v_kp['no_polisi'],
                        'tgl_terima' => !empty($v_kp['tgl_terima']) ? $v_kp['tgl_terima'] : null
                    );
                }
            }
        }

        if ( !empty($data) ) {
            krsort($data);
        }

        $content['data'] = $data;
        $html = $this->load->view('transaksi/pengiriman_voadip/list', $content, true);

        $this->result['status'] = 1;
        $this->result['content'] = $html;

        display_json($this->result);
    }

    public function load_form()
    {
        $id = $this->input->get('id');
        $resubmit = $this->input->get('resubmit');

        $a_content['order_voadip'] = null;
        $a_content['gudang_asal'] = $this->get_gudang_asal();
        $a_content['gudang_tujuan'] = $this->get_gudang_tujuan();
        $a_content['peternak'] = null;
        $a_content['voadip'] = $this->get_voadip();
        $a_content['unit'] = $this->get_unit();

        $html = null;
        if ( !empty($id) ) {
            $m_kv = new \Model\Storage\KirimVoadip_model();
            $d_kv = $m_kv->where('id', $id)->with(['terima', 'detail'])->first()->toArray();

            $asal = null;
            $tujuan = null;
            $tgl_docin_asal = null;
            $tgl_docin_tujuan = null;
            if ( $d_kv['jenis_kirim'] == 'opkp' ) {
                $m_rs = new \Model\Storage\RdimSubmit_model();
                $d_rs_asal = $m_rs->where('noreg', $d_kv['asal'])->with(['mitra'])->orderBy('id', 'desc')->first()->toArray();
                $tgl_docin_asal = $d_rs_asal['tgl_docin'];
                $asal = $d_rs_asal['mitra']['d_mitra']['nama'].' ('.$d_kv['asal'].')';

                if ( $d_kv['jenis_tujuan'] == 'peternak' ) {
                    $d_rs_tujuan = $m_rs->where('noreg', $d_kv['tujuan'])->with(['mitra'])->orderBy('id', 'desc')->first()->toArray();
                    $tgl_docin_tujuan = $d_rs_tujuan['tgl_docin'];
                    $tujuan = $d_rs_tujuan['mitra']['d_mitra']['nama'].' ('.$d_kv['tujuan'].')';
                }
            } else if ( $d_kv['jenis_kirim'] == 'opkg' ) {
                $m_gudang = new \Model\Storage\Gudang_model();
                $d_gudang = $m_gudang->where('id', $d_kv['asal'])->orderBy('id', 'desc')->first();

                $asal = $d_gudang->nama;

                if ( $d_kv['jenis_tujuan'] == 'peternak' ) {
                    if ( !empty($d_kv['tujuan']) ) {
                        $m_rs = new \Model\Storage\RdimSubmit_model();
                        $d_rs_tujuan = $m_rs->where('noreg', $d_kv['tujuan'])->with(['mitra'])->orderBy('id', 'desc')->first();

                        if ( $d_rs_tujuan ) {
                            $d_rs_tujuan = $d_rs_tujuan->toArray();
                            
                            $tgl_docin_tujuan = $d_rs_tujuan['tgl_docin'];
                            $tujuan = $d_rs_tujuan['mitra']['d_mitra']['nama'].' ('.$d_kv['tujuan'].')';
                        }
                    }
                } else {
                    $m_gudang = new \Model\Storage\Gudang_model();
                    $d_gudang = $m_gudang->where('id', $d_kv['tujuan'])->orderBy('id', 'desc')->first();

                    $tujuan = $d_gudang->nama;
                }
            } else {
                $m_supplier = new \Model\Storage\Supplier_model();
                $d_supplier = $m_supplier->where('nomor', $d_kv['asal'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('id', 'desc')->first();
                $asal = $d_supplier->nama;

                $m_gudang = new \Model\Storage\Gudang_model();
                $d_gudang = $m_gudang->where('id', $d_kv['tujuan'])->orderBy('id', 'desc')->first();

                $tujuan = $d_gudang->nama;
            }

            // $m_ov = new \Model\Storage\OrderVoadip_model();
            // $d_ov = $m_ov->where('no_order', $d_kv['no_order'])->with(['d_supplier'])->first();

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    ov.no_order,
                    ov.supplier,
                    ov.tanggal,
                    supl.nomor as supl_nomor,
                    supl.nama as supl_nama,
                    ovd.perusahaan as kode_prs,
                    prs.perusahaan as nama_prs
                from order_voadip ov
                left join
                    (select id_order, perusahaan from order_voadip_detail group by id_order, perusahaan) ovd
                    on
                        ov.id = ovd.id_order
                left join
                    (
                        select plg1.* from pelanggan plg1
                        right join
                            (select max(id) as id, nomor from pelanggan group by nomor) plg2
                            on
                                plg1.id = plg2.id
                    ) supl
                    on
                        ov.supplier = supl.nomor
                left join
                    (
                        select prs1.* from perusahaan prs1
                        right join
                            (select kode, max(id) as id from perusahaan group by kode) prs2
                            on
                                prs1.id = prs2.id
                    ) prs
                    on
                        ovd.perusahaan = prs.kode
                where
                    ov.no_order = '".$d_kv['no_order']."'
                group by
                    ov.no_order,
                    ov.supplier,
                    ov.tanggal,
                    supl.nomor,
                    supl.nama,
                    ovd.perusahaan,
                    prs.perusahaan
                order by
                    ov.no_order asc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $d_ov = null;
            if ( $d_conf->count() > 0 ) {
                $d_ov = $d_conf->toArray()[0];
            }

            $a_content['asal'] = $asal;
            $a_content['tujuan'] = $tujuan;
            $a_content['tgl_docin_asal'] = substr($tgl_docin_asal, 0, 10);
            $a_content['tgl_docin_tujuan'] = !empty($tgl_docin_tujuan) ? substr($tgl_docin_tujuan, 0, 10) : null;
            $a_content['data'] = $d_kv;
            $a_content['data_ov'] = !empty($d_ov) ? $d_ov : null;
            $a_content['terima'] = !empty($d_kv['terima']) ? 1 : 0;

            if ( $resubmit == 'edit' ) {
                $html = $this->load->view('transaksi/pengiriman_voadip/edit_form', $a_content, TRUE);
            } else {
                $html = $this->load->view('transaksi/pengiriman_voadip/view_form', $a_content, TRUE);
            }
        } else {
            $html = $this->load->view('transaksi/pengiriman_voadip/add_form', $a_content, TRUE);
        }

        echo $html;
    }

    public function get_op_not_kirim()
    {
        $params = $this->input->post('params');

        $unit = $params['unit'];
        $tgl_kirim = $params['tgl_kirim'];

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                ov.no_order,
                ov.supplier,
                ov.tanggal,
                supl.nomor as supl_nomor,
                supl.nama as supl_nama,
                ovd.perusahaan as kode_prs,
                prs.perusahaan as nama_prs
            from order_voadip ov
            left join
                (select id_order, perusahaan from order_voadip_detail group by id_order, perusahaan) ovd
                on
                    ov.id = ovd.id_order
            left join
                (
                    select plg1.* from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan group by nomor) plg2
                        on
                            plg1.id = plg2.id
                ) supl
                on
                    ov.supplier = supl.nomor
            left join
                (
                    select prs1.* from perusahaan prs1
                    right join
                        (select kode, max(id) as id from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    ovd.perusahaan = prs.kode
            where
                ov.tanggal between '".$tgl_kirim."' and '".$tgl_kirim."' and
                not exists (select * from kirim_voadip where no_order = ov.no_order) and
                SUBSTRING(ov.no_order, 5, 3) = '".$unit."'
            group by
                ov.no_order,
                ov.supplier,
                ov.tanggal,
                supl.nomor,
                supl.nama,
                ovd.perusahaan,
                prs.perusahaan
            order by
                ov.no_order asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = array();
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        // $m_ov = new \Model\Storage\OrderVoadip_model();
        // $d_ov = $m_ov->whereBetween('tanggal', [$tgl_kirim, $tgl_kirim])->with(['d_supplier', 'kirim'])->orderBy('no_order', 'asc')->get();

        // $data = array();
        // $_data = array();
        // if ( $d_ov->count() > 0 ) {
        //     $d_ov = $d_ov->toArray();
        //     foreach ($d_ov as $k => $v) {
        //         if ( empty($v['kirim']) ) {
        //             $_data[ $v['no_order'] ] = $v;
        //         }
        //     }

        //     foreach ($_data as $k => $v) {
        //         array_push($data, $v);
        //     }
        // }

        $this->result['content'] = $data;

        display_json( $this->result );
    }

    public function get_gudang_asal()
    {
        $unit = $this->get_unit();

        $data = null;
        foreach ($unit as $k_unit => $v_unit) {
            $m_wilayah = new \Model\Storage\Wilayah_model();
            $d_wilayah = $m_wilayah->select('id')->where('kode', $v_unit['kode'])->get();

            if ( $d_wilayah->count() > 0 ) {
                $d_wilayah = $d_wilayah->toArray();                

                $m_gudang = new \Model\Storage\Gudang_model();
                $d_gudang = $m_gudang->where('jenis', 'OBAT')->whereIn('unit', $d_wilayah)->orderBy('nama', 'asc')->get();

                if ( $d_gudang->count() > 0 ) {
                    $d_gudang = $d_gudang->toArray();

                    foreach ($d_gudang as $k_gdg => $v_gdg) {
                        $key = $v_gdg['nama'].'-'.$v_gdg['id'];

                        $data[ $key ] = $v_gdg;
                    }
                }
            }
        }

        return $data;
    }

    public function get_gudang_tujuan()
    {
        $m_gudang = new \Model\Storage\Gudang_model();
        $d_gudang = $m_gudang->where('jenis', 'OBAT')->orderBy('nama', 'asc')->get();

        if ( $d_gudang->count() > 0 ) {
            $d_gudang = $d_gudang->toArray();
        }

        return $d_gudang;
    }

    public function get_peternak()
    {
        $params = $this->input->post('params');

        $timestamp = strtotime(substr($params, 0, 10));
        $first_date_of_month = date('Y-m-01', $timestamp);
        $last_date_of_month  = date('Y-m-t', $timestamp); // A leap year!

        $_data = array();

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->select('nim', 'kandang', 'noreg', 'tgl_docin')->distinct('nim', 'kandang', 'noreg', 'tgl_docin')->whereBetween('tgl_docin', [$first_date_of_month, $last_date_of_month])->with(['mitra', 'kandang'])->get();

        if ( $d_rs->count() > 0 ) {
            $d_rs = $d_rs->toArray();
            foreach ($d_rs as $k_rs => $v_rs) {
                $m_od = new \Model\Storage\OrderDoc_model();
                $d_od = $m_od->where('noreg', $v_rs['noreg'])->orderBy('id', 'desc')->first();

                $tgl_terima = $v_rs['tgl_docin'];
                if ( $d_od ) {
                    $m_td = new \Model\Storage\TerimaDoc_model();
                    $d_td = $m_td->where('no_order', $d_od->no_order)->orderBy('id', 'desc')->first();

                    if ( $d_td ) {
                        $tgl_terima = $d_td->datang;
                    }
                }

                $rt = !empty($v_rs['mitra']['d_mitra']['alamat_rt']) ? ' ,RT.'.$v_rs['mitra']['d_mitra']['alamat_rt'] : null;
                $rw = !empty($v_rs['mitra']['d_mitra']['alamat_rw']) ? '/RW.'.$v_rs['mitra']['d_mitra']['alamat_rw'] : null;
                $kelurahan = !empty($v_rs['mitra']['d_mitra']['alamat_kelurahan']) ? ' ,'.$v_rs['mitra']['d_mitra']['alamat_kelurahan'] : null;
                $kecamatan = !empty($v_rs['mitra']['d_mitra']['d_kecamatan']) ? ' ,'.$v_rs['mitra']['d_mitra']['d_kecamatan']['nama'] : null;

                $alamat = $v_rs['mitra']['d_mitra']['alamat_jalan'] . $rt . $rw . $kelurahan . $kecamatan;

                $key = $v_rs['kandang']['d_unit']['kode'].'-'.$tgl_terima.' - '.$v_rs['mitra']['d_mitra']['nama'].' - '.$v_rs['noreg'];
                $_data[ $key ] = array(
                    'tgl_terima' => strtoupper(tglIndonesia($tgl_terima, '-', ' ')),
                    'noreg' => $v_rs['noreg'],
                    'kode_unit' => $v_rs['kandang']['d_unit']['kode'],
                    'nomor' => $v_rs['mitra']['d_mitra']['nomor'],
                    'nama' => $v_rs['mitra']['d_mitra']['nama'],
                    'alamat' => strtoupper($alamat)
                );
            }
        }

        $data = array();
        if ( !empty($_data) ) {
            ksort($_data);
            foreach ($_data as $k_data => $v_data) {
                $data[] = $v_data;
            }
        }

        $this->result['status'] = !empty($data) ? 1 : 0;
        $this->result['content'] = $data;

        display_json( $this->result );
    }

    public function get_voadip()
    {
        $m_brg = new \Model\Storage\Barang_model();
        $d_nomor = $m_brg->select('kode')->distinct('kode')->where('tipe', 'obat')->get()->toArray();

        $datas = array();
        if ( !empty($d_nomor) ) {
            foreach ($d_nomor as $nomor) {
                $brg = $m_brg->where('tipe', 'obat')
                                          ->where('kode', $nomor['kode'])
                                          ->orderBy('version', 'desc')
                                          ->orderBy('id', 'desc')
                                          ->first()->toArray();

                $key = $brg['nama'].' | '.$brg['kode'];
                $datas[$key] = $brg;

                ksort( $datas );
            }
        }

        return $datas;
    }

    public function get_list_table()
    {
        $jenis_pengiriman = $this->input->post('jenis_pengiriman');
        $no_order = $this->input->post('no_order');

        try {
            $data = null;
            if ( $jenis_pengiriman == 'opks' ) {
                $m_ov = new \Model\Storage\OrderVoadip_model();
                $d_ov = $m_ov->where('no_order', $no_order)->orderBy('id', 'desc')->first();

                if ( $d_ov ) {
                    $m_ovd = new \Model\Storage\OrderVoadipDetail_model();
                    $d_ovd = $m_ovd->where('id_order', $d_ov->id)->get();

                    if ( $d_ovd->count() > 0 ) {
                        $data = $d_ovd->toArray();
                    }
                }
            }

            $content['jenis_pengiriman'] = $jenis_pengiriman;
            $content['obat'] = $this->get_voadip();
            $content['data'] = $data;
            $html = $this->load->view('transaksi/pengiriman_voadip/list_order_voadip', $content, TRUE);
            
            $this->result['status'] = 1;
            $this->result['content'] = $html;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_kirim_voadip = new \Model\Storage\KirimVoadip_model();
            $now = $m_kirim_voadip->getDate();

            $no_order = null;
            $no_sj = null;
            $kode_unit = null;
            if ( $params['jenis_kirim'] == 'opks' ) {
                $no_order = $params['no_order'];
                $no_sj = $params['no_sj'];
                $kode_unit = 1;
            } else {
                if ( $params['jenis_tujuan'] == 'peternak' ) {
                    $m_rs = new \Model\Storage\RdimSubmit_model();
                    $d_rs = $m_rs->where('noreg', $params['tujuan'])->with(['dKandang'])->first();

                    if ( $d_rs ) {
                        $d_rs = $d_rs->toArray();
                        $kode_unit = strtoupper($d_rs['d_kandang']['d_unit']['kode']);
                    }
                } else {
                    $m_gdg = new \Model\Storage\Gudang_model();
                    $d_gdg = $m_gdg->where('id', $params['tujuan'])->with(['dUnit'])->first();

                    if ( $d_gdg ) {
                        $d_gdg = $d_gdg->toArray();
                        $kode_unit = strtoupper($d_gdg['d_unit']['kode']);
                    }
                }

                $no_order = $m_kirim_voadip->getNextIdOrder('OP/'.$kode_unit);
                $no_sj = $m_kirim_voadip->getNextIdSj('SJ/'.$kode_unit);
            }

            if ( !empty($kode_unit) ) {
                $m_kirim_voadip->tgl_trans = $now['waktu'];
                $m_kirim_voadip->tgl_kirim = $params['tgl_kirim'];
                $m_kirim_voadip->no_order = $no_order;
                $m_kirim_voadip->jenis_kirim = $params['jenis_kirim'];
                $m_kirim_voadip->asal = $params['asal'];
                $m_kirim_voadip->jenis_tujuan = $params['jenis_tujuan'];
                $m_kirim_voadip->tujuan = $params['tujuan'];
                $m_kirim_voadip->ekspedisi = $params['ekspedisi'];
                $m_kirim_voadip->no_polisi = $params['nopol'];
                $m_kirim_voadip->sopir = $params['sopir'];
                $m_kirim_voadip->no_sj = $no_sj;
                $m_kirim_voadip->ongkos_angkut = $params['ongkos_angkut'];
                $m_kirim_voadip->save();

                $id_header = $m_kirim_voadip->id;

                foreach ($params['detail'] as $k_detail => $v_detail) {
                    $m_kirim_apakn_detail = new \Model\Storage\KirimVoadipDetail_model();
                    $m_kirim_apakn_detail->id_header = $id_header;
                    $m_kirim_apakn_detail->item = $v_detail['barang'];
                    $m_kirim_apakn_detail->jumlah = $v_detail['jumlah'];
                    $m_kirim_apakn_detail->kondisi = $v_detail['kondisi'];
                    $m_kirim_apakn_detail->save();
                }

                $d_kirim_voadip = $m_kirim_voadip->where('id', $id_header)->with(['detail'])->first();

                $deskripsi_log_kirim_voadip = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $d_kirim_voadip, $deskripsi_log_kirim_voadip);

                $this->result['status'] = 1;
                $this->result['message'] = 'Data Pengiriman Voadip berhasil di simpan.';
            } else {
                $this->result['message'] = 'Kode unit masih kosong, harap lengkapi kode unit terlebih dahulu.';
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit()
    {
        $params = $this->input->post('params');

        try {
            $m_kirim_voadip = new \Model\Storage\KirimVoadip_model();
            $now = $m_kirim_voadip->getDate();

            $m_kirim_voadip->where('id', $params['id'])->update(
                array(
                    'tgl_trans' => $now['waktu'],
                    'tgl_kirim' => $params['tgl_kirim'],
                    'no_order' => $params['no_order'],
                    'jenis_kirim' => $params['jenis_kirim'],
                    'asal' => $params['asal'],
                    'jenis_tujuan' => $params['jenis_tujuan'],
                    'tujuan' => $params['tujuan'],
                    'ekspedisi' => $params['ekspedisi'],
                    'no_polisi' => $params['nopol'],
                    'sopir' => $params['sopir'],
                    'no_sj' => $params['no_sj'],
                    'ongkos_angkut' => $params['ongkos_angkut']
                )
            );

            $id_header = $params['id'];

            $m_kirim_voadip_detail = new \Model\Storage\KirimVoadipDetail_model();
            $m_kirim_voadip_detail->where('id_header', $id_header)->delete();

            foreach ($params['detail'] as $k_detail => $v_detail) {
                $m_kirim_voadip_detail = new \Model\Storage\KirimVoadipDetail_model();
                $m_kirim_voadip_detail->id_header = $id_header;
                $m_kirim_voadip_detail->item = $v_detail['barang'];
                $m_kirim_voadip_detail->jumlah = $v_detail['jumlah'];
                $m_kirim_voadip_detail->kondisi = $v_detail['kondisi'];
                $m_kirim_voadip_detail->save();
            }

            $d_kirim_voadip = $m_kirim_voadip->where('id', $id_header)->with(['detail'])->first();

            $deskripsi_log_kirim_voadip = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_kirim_voadip, $deskripsi_log_kirim_voadip);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Pengiriman Voadip berhasil di ubah.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {
            $m_kirim_voadip = new \Model\Storage\KirimVoadip_model();
            $now = $m_kirim_voadip->getDate();

            $d_kirim_voadip = $m_kirim_voadip->where('id', $params['id'])->with(['detail'])->first();

            $deskripsi_log_kirim_voadip = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_kirim_voadip, $deskripsi_log_kirim_voadip);

            $m_kirim_voadip_detail = new \Model\Storage\KirimVoadipDetail_model();
            $m_kirim_voadip_detail->where('id_header', $params['id'])->delete();

            $m_kirim_voadip->where('id', $params['id'])->delete();

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Pengiriman Voadip berhasil di hapus.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function cek_stok_gudang()
    {
        $params = $this->input->post('params');

        try {
            // $today = date('Y-m-d');

            // $m_stok = new \Model\Storage\Stok_model();
            // $d_stok = $m_stok->where('periode', '<', substr($today, 0, 7).'-01')->orderBy('periode', 'desc')->first();

            // $stok_masuk = 0;
            // $stok_keluar = 0;
            // if ( $d_stok ) {
            //     $tgl_awal = next_date(date("Y-m-t", strtotime($d_stok->periode)));
            //     $tgl_akhir = $today;

            //     /* BARANG MASUK */
            //     $m_dstok = new \Model\Storage\DetStok_model();
            //     $stok_masuk += $m_dstok->where('id_header', $d_stok->id)->where('kode_gudang', $params['gudang'])->where('kode_barang', $params['item'])->sum('jumlah');

            //     /* KIRIM VOADIP */
            //     $m_kirim_voadip = new \Model\Storage\KirimVoadip_model();
            //     $d_kv_ke_gudang = $m_kirim_voadip->select('id')->whereBetween('tgl_kirim', [$tgl_awal, $tgl_akhir])->where('jenis_tujuan', 'gudang')->where('tujuan', $params['gudang'])->get();

            //     if ( $d_kv_ke_gudang->count() > 0 ) {
            //         $d_kv_ke_gudang = $d_kv_ke_gudang->toArray();
            //         foreach ($d_kv_ke_gudang as $key => $value) {
            //             $m_tv = new \Model\Storage\TerimaVoadip_model();
            //             $d_tv = $m_tv->select('id')->where('id_kirim_voadip', $value['id'])->orderBy('id', 'desc')->first();

            //             if ( !empty($d_tv) ) {
            //                 $d_tv = $d_tv->toArray();

            //                 $m_dtp = new \Model\Storage\TerimaVoadipDetail_model();
            //                 $stok_masuk += $m_dtp->whereIn('id_header', $d_tv)->where('item', $params['item'])->sum('jumlah');
            //             }
            //         }
            //     }

            //     /* RETUR VOADIP */
            //     $m_retur_voadip = new \Model\Storage\ReturVoadip_model();
            //     $d_rv_ke_gudang = $m_retur_voadip->select('id')->whereBetween('tgl_retur', [$tgl_awal, $tgl_akhir])->where('tujuan', 'gudang')->where('id_tujuan', $params['gudang'])->get();

            //     if ( $d_rv_ke_gudang->count() > 0 ) {
            //         $d_rv_ke_gudang = $d_rv_ke_gudang->toArray();
            //         $m_drv = new \Model\Storage\DetReturVoadip_model();
            //         $stok_masuk += $m_drv->whereIn('id_header', $d_rv_ke_gudang)->where('item', $params['item'])->sum('jumlah');
            //     }
            //     /* END - BARANG MASUK */

            //     /* BARANG KELUAR  */
            //     /* KIRIM VOADIP */
            //     $m_kirim_voadip = new \Model\Storage\KirimVoadip_model();
            //     $d_kv_ke_peternak = $m_kirim_voadip->select('id')->whereBetween('tgl_kirim', [$tgl_awal, $tgl_akhir])->where('jenis_kirim', 'opkg')->where('asal', $params['gudang'])->where('jenis_tujuan', 'peternak')->get();

            //     if ( $d_kv_ke_peternak->count() > 0 ) {
            //         $d_kv_ke_peternak = $d_kv_ke_peternak->toArray();
            //         foreach ($d_kv_ke_peternak as $key => $value) {
            //             $m_tv = new \Model\Storage\TerimaVoadip_model();
            //             $d_tv = $m_tv->select('id')->where('id_kirim_voadip', $value['id'])->orderBy('id', 'desc')->first();

            //             if ( !empty($d_tv) ) {
            //                 $d_tv = $d_tv->toArray();

            //                 $m_dtp = new \Model\Storage\TerimaVoadipDetail_model();
            //                 $stok_keluar += $m_dtp->whereIn('id_header', $d_tv)->where('item', $params['item'])->sum('jumlah');
            //             }
            //         }
            //     }

            //     /* RETUR VOADIP */
            //     $m_retur_voadip = new \Model\Storage\ReturVoadip_model();
            //     $d_rv_dari_gudang = $m_retur_voadip->select('id')->whereBetween('tgl_retur', [$tgl_awal, $tgl_akhir])->where('asal', 'gudang')->where('id_asal', $params['gudang'])->get();

            //     if ( $d_rv_dari_gudang->count() > 0 ) {
            //         $d_rv_dari_gudang = $d_rv_dari_gudang->toArray();
            //         $m_drv = new \Model\Storage\DetReturVoadip_model();
            //         $stok_keluar += $m_drv->whereIn('id_header', $d_rv_dari_gudang)->where('item', $params['item'])->sum('jumlah');
            //     }
            //     /* END - BARANG MASUK */
            // }

            // $stok = (($stok_masuk - $stok_keluar) > 0) ? $stok_masuk - $stok_keluar : 0;

            // $message = null;
            // $status_stok = 1;
            // if ( $stok < $params['jml'] ) {
            //     $status_stok = 0;
            //     $message = '<b style="color: red;">STOK TIDAK MENCUKUPI !!!</b><br><br>STOK GUDANG : '.$stok.'<br>JUMLAH YANG ANDA INPUT : '.$params['jml'].'<br>JUMLAH YANG ANDA MASUKKAN MELEBIHI STOK YANG ADA.';
            // }

            $tanggal = date('Y-m-d');

            $start_date = prev_date($tanggal, 7);
            $end_date = $tanggal;

            $sql = "
                select sum(dkv.jumlah) as jumlah from det_kirim_voadip dkv
                left join 
                    kirim_voadip kv
                    on
                        dkv.id_header = kv.id
                where
                    dkv.item = '".$params['item']."' and
                    kv.tgl_kirim between '".$start_date."' and '".$end_date."' and
                    kv.asal = '".$params['gudang']."' and
                    not exists (
                        select * from terima_voadip tv where tv.id_kirim_voadip = kv.id
                    )
            ";

            $m_conf = new \Model\Storage\Conf();
            $d_conf = $m_conf->hydrateRaw( $sql );

            $jml_kirim = 0;
            if ( $d_conf->count() > 0 ) {
                $jml_kirim = $d_conf->toArray()[0]['jumlah'];
            }

            $m_stok = new \Model\Storage\Stok_model();
            $sql = "
                select sum(ds.jml_stok) as jumlah from det_stok ds
                where 
                    ds.id_header in (
                        select max(id) as id from stok s where s.periode in (
                            select max(cast(s.periode as date)) as periode from det_stok ds 
                            left join
                                stok s 
                                on
                                    ds.id_header = s.id 
                            where 
                                ds.kode_barang = '".$params['item']."' and 
                                ds.kode_gudang = ".$params['gudang']." and
                                s.periode <= '".$tanggal."'
                            group by
                                ds.kode_barang,
                                ds.kode_gudang
                        )
                    ) and
                    ds.kode_barang = '".$params['item']."' and 
                    ds.kode_gudang = ".$params['gudang']."
            ";

            $d_stok = $m_stok->hydrateRaw( $sql );

            $jml_stok = 0;
            if ( $d_stok->count() > 0 ) {
                $jml_stok = $d_stok->toArray()[0]['jumlah'];
            }

            // $d_dstok = null;
            // while ( empty($d_dstok) ) {
            //     $m_stok = new \Model\Storage\Stok_model();
            //     $d_stok = $m_stok->where('periode', '<=', $tanggal)->orderBy('periode', 'desc')->first();

            //     $m_dstok = new \Model\Storage\DetStok_model();
            //     $d_dstok = $m_dstok->where('id_header', $d_stok->id)->where('kode_gudang', $params['gudang'])->where('kode_barang', $params['item'])->first();

            //     if ( !$d_dstok ) {
            //         $d_dstok = null;
            //         $tanggal = prev_date( $tanggal );
            //     }
            // }

            // if ( $d_dstok ) {
            //     $m_dstok = new \Model\Storage\DetStok_model();
            //     $d_dstok = $m_dstok->where('id_header', $d_stok->id)->where('kode_gudang', $params['gudang'])->where('kode_barang', $params['item'])->sum('jml_stok');
            // }

            $stok = (($jml_stok - ($params['jml'] + $jml_kirim)) > 0) ? $jml_stok - ($params['jml'] + $jml_kirim) : 0;

            $message = null;
            $status_stok = 1;

            if ( $jml_stok < ($params['jml'] + $jml_kirim) ) {
                $status_stok = 0;
                $message = '<b style="color: red;">STOK TIDAK MENCUKUPI !!!</b><br><br>STOK GUDANG : '.($jml_stok - $jml_kirim).'<br>JUMLAH YANG ANDA INPUT : '.$params['jml'].'<br>JUMLAH YANG ANDA MASUKKAN MELEBIHI STOK YANG ADA.';
            }
            
            $this->result['status'] = 1;
            $this->result['stok'] = $stok;
            $this->result['status_stok'] = $status_stok;
            $this->result['message'] = $message;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function listActivity()
    {
        $params = $this->input->get('params');

        $m_kirim_voadip = new \Model\Storage\KirimVoadip_model();
        $d_kirim_voadip = $m_kirim_voadip->where('id', $params['id'])->with(['logs'])->first()->toArray();

        $data = array(
            'no_order' => $params['no_order'],
            'tgl_kirim' => $params['tgl_kirim'],
            'asal' => $params['asal'],
            'tujuan' => $params['tujuan'],
            'nopol' => $params['nopol'],
            'logs' => $d_kirim_voadip['logs']
        );

        $content['data'] = $data;
        $html = $this->load->view('transaksi/pengiriman_voadip/list_activity', $content, true);

        echo $html;
    }

    public function tes()
    {
        $m_kirim_voadip = new \Model\Storage\KirimVoadip_model();
        $d_kirim_voadip = $m_kirim_voadip->where('no_order', 'OP/GSK/22/02098')->where('id', '<>', 8023)->get()->toArray();

        foreach ($d_kirim_voadip as $k_kv => $v_kv) {
            $no_order = $m_kirim_voadip->getNextIdOrder('OP/GSK');
            $no_sj = $m_kirim_voadip->getNextIdSj('SJ/GSK');

            $m_kirim_voadip->where('id', $v_kv['id'])->update(
                array(
                    'no_order' => $no_order,
                    'no_sj' => $no_sj
                )
            );
        }
    }
}