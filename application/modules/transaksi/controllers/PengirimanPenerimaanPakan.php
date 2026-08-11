<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PengirimanPenerimaanPakan extends Public_Controller {

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
                "assets/transaksi/pengiriman_penerimaan_pakan/js/pengiriman-penerimaan-pakan.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/pengiriman_penerimaan_pakan/css/pengiriman-penerimaan-pakan.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['unit'] = $this->get_unit();

            $a_content['order_pakan']   = null;
            $a_content['gudang_asal']   = $this->get_gudang_asal();
            $a_content['gudang_tujuan'] = $this->get_gudang_tujuan();
            $a_content['peternak']      = null;
            $a_content['pakan']         = $this->get_pakan();
            $a_content['unit']          = $this->get_unit();
            $a_content['ekspedisi']     = $this->get_ekspedisi();

            $content['add_form']        = $this->load->view('transaksi/pengiriman_penerimaan_pakan/v_add_form', $a_content, TRUE);

            $data['title_menu']         = 'Pengiriman & Penerimaan Pakan';
            $data['view'] = $this->load->view('transaksi/pengiriman_penerimaan_pakan/v_index', $content, TRUE);
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

        $kode_unit = $params['kode_unit'];

        // $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
        // $d_kirim_pakan = $m_kirim_pakan->whereBetween('tgl_kirim', [$params['start_date'], $params['end_date']])->with(['terima'])->get();

        $m_conf = new \Model\Storage\Conf();
        $sql_asal_tujuan = "
            (
                select cast(plg1.nomor as varchar(15)) as kode, plg1.nama, null as unit from pelanggan plg1
                right join
                    (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                    on
                        plg1.id = plg2.id

                union all

                select 
                    cast(gdg.id as varchar(15)) as kode, 
                    gdg.nama,
                    w.kode as unit
                from gudang gdg
                left join
                    wilayah w
                    on
                        gdg.unit = w.id

                union all

                select
                    cast(rs.noreg as varchar(15)) as kode,
                    mtr.nama,
                    w.kode as unit
                from rdim_submit rs
                left join
                    kandang k
                    on
                        rs.kandang = k.id
                left join
                    wilayah w
                    on
                        k.unit = w.id
                left join
                    (
                        select mm1.* from mitra_mapping mm1
                        right join
                            (select max(id) as id, nim from mitra_mapping group by nim) mm2
                            on
                                mm1.id = mm2.id
                    ) mm
                    on
                        rs.nim = mm.nim
                left join
                    mitra mtr
                    on
                        mtr.id = mm.mitra
            )
        ";
        $sql = "
            select 
                kp.id,
                kp.no_order,
                kp.tgl_kirim,
                asal.nama as asal,
                tujuan.nama as tujuan,
                kp.no_polisi as nopol,
                tp.tgl_terima
            from kirim_pakan kp
            left join
                terima_pakan tp
                on
                    kp.id = tp.id_kirim_pakan
            left join
                ".$sql_asal_tujuan." asal
                on
                    kp.asal = asal.kode
            left join
                ".$sql_asal_tujuan." tujuan
                on
                    kp.tujuan = tujuan.kode
            where
                kp.tgl_kirim between '".$params['start_date']."' and '".$params['end_date']."' ";

            if ($kode_unit != 'all'){
               $sql .= " and ((asal.unit = '".$kode_unit."') or (tujuan.unit = '".$kode_unit."')) ";
            }
            
            $sql .= " group by
                kp.id,
                kp.no_order,
                kp.tgl_kirim,
                asal.nama,
                tujuan.nama,
                kp.no_polisi,
                tp.tgl_terima
            order by
                kp.tgl_kirim desc,
                kp.id desc
        ";
        $d_kirim_pakan = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( !empty($d_kirim_pakan) ) {
            $data = $d_kirim_pakan->toArray();

            // foreach ($d_kirim_pakan as $k_kp => $v_kp) {
            //     $tampil = 0;

            //     if ( $v_kp['jenis_kirim'] == 'opks' || $v_kp['jenis_kirim'] == 'opkg' ) {
            //         if ( $kode_unit != 'all' ) {
            //             if ( stristr($v_kp['no_order'], $kode_unit) ) {
            //                 $tampil = 1;
            //             }
            //         } else {
            //             $tampil = 1;
            //         }
            //     } else if ( $v_kp['jenis_kirim'] == 'opkp' ) {
            //         if ( $kode_unit != 'all' ) {
            //             $m_conf = new \Model\Storage\Conf();
            //             $sql = "
            //                 select w.kode from rdim_submit rs
            //                 right join
            //                     kandang k
            //                     on
            //                         rs.kandang = k.id
            //                 right join
            //                     wilayah w
            //                     on
            //                         k.unit = w.id
            //                 where
            //                     rs.noreg = '".$v_kp['asal']."'
            //                 group by
            //                     w.kode
            //             ";
            //             $d_asal = $m_conf->hydrateRaw( $sql );
            //             $kode_unit_asal = null;
            //             if ( $d_asal->count() > 0 ) {
            //                 $d_asal = $d_asal->toArray()[0];
            //                 $kode_unit_asal = $d_asal['kode'];
            //             }

            //             $sql = "
            //                 select w.kode from rdim_submit rs
            //                 right join
            //                     kandang k
            //                     on
            //                         rs.kandang = k.id
            //                 right join
            //                     wilayah w
            //                     on
            //                         k.unit = w.id
            //                 where
            //                     rs.noreg = '".$v_kp['tujuan']."'
            //                 group by
            //                     w.kode
            //             ";
            //             $d_tujuan = $m_conf->hydrateRaw( $sql );
            //             $kode_unit_tujuan = null;
            //             if ( $d_tujuan->count() > 0 ) {
            //                 $d_tujuan = $d_tujuan->toArray()[0];
            //                 $kode_unit_tujuan = $d_tujuan['kode'];
            //             }

            //             if ( stristr($kode_unit_asal, $kode_unit) || stristr($kode_unit_tujuan, $kode_unit) ) {
            //                 $tampil = 1;
            //             }
            //         } else {
            //             $tampil = 1;
            //         }
            //     }

            //     if ( $tampil == 1 ) {
            //         $asal = null;
            //         $tujuan = null;

            //         $m_supplier = new \Model\Storage\Pelanggan_model();
            //         $m_peternak = new \Model\Storage\RdimSubmit_model();
            //         $m_gudang = new \Model\Storage\Gudang_model();
            //         // ASAL
            //         if ( $v_kp['jenis_kirim'] == 'opks' ) {
            //             $d_supplier = $m_supplier->where('nomor', $v_kp['asal'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('id', 'desc')->first();
            //             $asal = $d_supplier->nama;
            //         } else if ( $v_kp['jenis_kirim'] == 'opkp' ) {
            //             $d_peternak = $m_peternak->where('noreg', $v_kp['asal'])->with(['mitra'])->orderBy('id', 'desc')->first();
            //             if ( !$d_peternak ) {
            //                 // cetak_r( $v_kp['asal'] );
            //             } else {
            //                 $asal = $d_peternak->mitra->dMitra->nama;
            //             }
            //         } else if ( $v_kp['jenis_kirim'] == 'opkg' ) {
            //             $d_gudang = $m_gudang->where('id', $v_kp['asal'])->orderBy('id', 'desc')->first();
            //             $asal = $d_gudang->nama;
            //         }
            //         // TUJUAN
            //         if ( $v_kp['jenis_tujuan'] == 'peternak' ) {
            //             $d_peternak = $m_peternak->where('noreg', $v_kp['tujuan'])->with(['mitra'])->orderBy('id', 'desc')->first();
            //             if ( $d_peternak ) {
            //                 $tujuan = $d_peternak->mitra->dMitra->nama.' ('.$v_kp['tujuan'].')';
            //             }
            //         } else if ( $v_kp['jenis_tujuan'] == 'gudang' ) {
            //             $d_gudang = $m_gudang->where('id', $v_kp['tujuan'])->orderBy('id', 'desc')->first();
            //             $tujuan = $d_gudang->nama;
            //         }

            //         $key = str_replace('-', '', $v_kp['tgl_kirim']).'|'.$v_kp['id'];
            //         $data[ $key ] = array(
            //             'id' => $v_kp['id'],
            //             'no_order' => $v_kp['no_order'],
            //             'tgl_kirim' => $v_kp['tgl_kirim'],
            //             'asal' => $asal,
            //             'tujuan' => $tujuan,
            //             'nopol' => $v_kp['no_polisi'],
            //             'tgl_terima' => !empty($v_kp['tgl_terima']) ? $v_kp['tgl_terima'] : null
            //         );
            //     }
            // }
        }

    	// if ( !empty($data) ) {
    	// 	krsort($data);
    	// }

        // echo "<pre>";
        // print_r($sql);
        // die;

        $content['data'] = $data;
        $html = $this->load->view('transaksi/pengiriman_penerimaan_pakan/v_list', $content, true);

        $this->result['status'] = 1;
        $this->result['content'] = $html;

        display_json($this->result);
    }

    public function load_form()
    {
        $id = $this->input->get('id');
        $resubmit = $this->input->get('resubmit');

        $a_content['order_pakan'] = null;
        $a_content['gudang_asal'] = $this->get_gudang_asal();
        $a_content['gudang_tujuan'] = $this->get_gudang_tujuan();
        $a_content['peternak'] = null;
        $a_content['pakan'] = $this->get_pakan();
        $a_content['unit'] = $this->get_unit();
        $a_content['ekspedisi'] = $this->get_ekspedisi();

        $html = null;
        if ( !empty($id) ) {
            $m_kp = new \Model\Storage\KirimPakan_model();
            $d_kp = $m_kp->where('id', $id)->with(['terima', 'detail'])->first()->toArray();

            $asal = null;
            $tujuan = null;
            $tgl_docin_asal = null;
            $tgl_docin_tujuan = null;
            if ( $d_kp['jenis_kirim'] == 'opkp' ) {
                $m_rs = new \Model\Storage\RdimSubmit_model();
                $d_rs_asal = $m_rs->where('noreg', $d_kp['asal'])->with(['mitra'])->orderBy('id', 'desc')->first()->toArray();
                $tgl_docin_asal = $d_rs_asal['tgl_docin'];
                $asal = $d_rs_asal['mitra']['d_mitra']['nama'].' ('.$d_kp['asal'].')';

                if ( $d_kp['jenis_tujuan'] == 'peternak' ) {
                    if ( !empty($d_kp['tujuan']) ) {
                        $d_rs_tujuan = $m_rs->where('noreg', $d_kp['tujuan'])->with(['mitra'])->orderBy('id', 'desc')->first()->toArray();
                        $tgl_docin_tujuan = $d_rs_tujuan['tgl_docin'];
                        $tujuan = $d_rs_tujuan['mitra']['d_mitra']['nama'].' ('.$d_kp['tujuan'].')';
                    }
                }

                $a_content['no_sj_asal'] = $data = $this->getDataSjAsal( $d_kp['asal'] );
            } else if ( $d_kp['jenis_kirim'] == 'opkg' ) {
                $m_gudang = new \Model\Storage\Gudang_model();
                $d_gudang = $m_gudang->where('id', $d_kp['asal'])->orderBy('id', 'desc')->first();

                $asal = $d_gudang->nama;

                if ( $d_kp['jenis_tujuan'] == 'peternak' ) {
                    $m_rs = new \Model\Storage\RdimSubmit_model();
                    $d_rs_tujuan = $m_rs->where('noreg', $d_kp['tujuan'])->with(['mitra'])->orderBy('id', 'desc')->first();

                    $tgl_docin_tujuan = ($d_rs_tujuan) ? $d_rs_tujuan->tgl_docin : null;
                    $tujuan = ($d_rs_tujuan) ? $d_rs_tujuan->mitra->dMitra->nama.' ('.$d_kp['tujuan'].')' : null;
                }
            } else {
                $m_supplier = new \Model\Storage\Supplier_model();
                $d_supplier = $m_supplier->where('nomor', $d_kp['asal'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('id', 'desc')->first();
                $asal = $d_supplier->nama;

                if ( $d_kp['jenis_tujuan'] == 'peternak' ) {
                    $m_rs = new \Model\Storage\RdimSubmit_model();
                    $d_rs_tujuan = $m_rs->where('noreg', $d_kp['tujuan'])->with(['mitra'])->orderBy('id', 'desc')->first();

                    $tgl_docin_tujuan = ($d_rs_tujuan) ? $d_rs_tujuan->tgl_docin : null;
                    $tujuan = ($d_rs_tujuan) ? $d_rs_tujuan->mitra->dMitra->nama.' ('.$d_kp['tujuan'].')' : null;
                } else {
                    $m_gudang = new \Model\Storage\Gudang_model();
                    $d_gudang = $m_gudang->where('id', $d_kp['tujuan'])->orderBy('id', 'desc')->first();

                    $tujuan = $d_gudang->nama;
                }
            }

            // $m_op = new \Model\Storage\OrderPakan_model();
            // $d_op = $m_op->where('no_order', $d_kp['no_order'])->with(['d_supplier'])->first();

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    op.*,
                    supl.nomor as supl_nomor,
                    supl.nama as supl_nama,
                    opd.perusahaan as kode_prs,
                    prs.perusahaan as nama_prs
                from order_pakan op
                left join
                    (select id_header, perusahaan from order_pakan_detail group by id_header, perusahaan) opd
                    on
                        op.id = opd.id_header
                left join
                    pelanggan supl
                    on
                        op.supplier = supl.nomor
                left join
                    (
                        select prs1.* from perusahaan prs1
                        right join
                            (select kode, max(id) as id from perusahaan group by kode) prs2
                            on
                                prs1.id = prs2.id
                    ) prs
                    on
                        opd.perusahaan = prs.kode
                where
                    op.no_order = '".$d_kp['no_order']."'
                group by
                    op.id,
                    op.no_order,
                    op.tgl_trans,
                    op.rcn_kirim,
                    op.supplier,
                    op.no_po,
                    supl.nomor,
                    supl.nama,
                    opd.perusahaan,
                    prs.perusahaan
                order by
                    op.no_order asc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $d_op = null;
            if ( $d_conf->count() > 0 ) {
                $d_op = $d_conf->toArray()[0];
            }

            $a_content['asal'] = $asal;
            $a_content['tujuan'] = $tujuan;
            $a_content['tgl_docin_asal'] = substr($tgl_docin_asal, 0, 10);
            $a_content['tgl_docin_tujuan'] = substr($tgl_docin_tujuan, 0, 10);
            $a_content['data'] = $d_kp;
            $a_content['data_op'] = !empty($d_op) ? $d_op : null;
            $a_content['terima'] = !empty($d_kp['terima']) ? 1 : 0;            

            if ( $resubmit == 'edit' ) {
                $html = $this->load->view('transaksi/pengiriman_penerimaan_pakan/v_edit_form', $a_content, TRUE);
            } else {
                $html = $this->load->view('transaksi/pengiriman_penerimaan_pakan/v_view_form', $a_content, TRUE);
            }
        } else {
            $html = $this->load->view('transaksi/pengiriman_penerimaan_pakan/v_add_form', $a_content, TRUE);
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
                op.*,
                supl.nomor as supl_nomor,
                supl.nama as supl_nama,
                opd.perusahaan as kode_prs,
                prs.perusahaan as nama_prs
            from order_pakan op
            left join
                (select id_header, perusahaan from order_pakan_detail group by id_header, perusahaan) opd
                on
                    op.id = opd.id_header
            left join
                pelanggan supl
                on
                    op.supplier = supl.nomor
            left join
                (
                    select prs1.* from perusahaan prs1
                    right join
                        (select kode, max(id) as id from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    opd.perusahaan = prs.kode
            where
                op.rcn_kirim between '".$tgl_kirim."' and '".$tgl_kirim."' and
                not exists (select * from kirim_pakan where no_order = op.no_order) and
                SUBSTRING(op.no_order, 5, 3) = '".$unit."'
            group by
                op.id,
                op.no_order,
                op.tgl_trans,
                op.rcn_kirim,
                op.supplier,
                op.no_po,
                supl.nomor,
                supl.nama,
                opd.perusahaan,
                prs.perusahaan
            order by
                op.no_order asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = array();
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        // $m_op = new \Model\Storage\OrderPakan_model();
        // $d_op = $m_op->whereBetween('rcn_kirim', [$tgl_kirim, $tgl_kirim])->with(['d_supplier', 'kirim'])->orderBy('no_order', 'asc')->get();

        // $data = array();
        // if ( $d_op->count() > 0 ) {
        //     $d_op = $d_op->toArray();
        //     foreach ($d_op as $k => $v) {
        //         if ( empty($v['kirim']) ) {
        //             array_push($data, $v);
        //         }
        //     }
        // }

        // $m_terima_pakan = new \Model\Storage\TerimaPakan_model();
        // $d_terima_pakan = $m_terima_pakan->select('id_kirim_pakan')->whereBetween('tgl_terima', [$prev_date, $today])->get();

        // $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
        // if ( $d_terima_pakan->count() > 0 ) {
        //     $d_terima_pakan = $d_terima_pakan->toArray();
        //     $d_kirim_pakan = $m_kirim_pakan->select('no_order')->whereNotIn('id', $d_terima_pakan)->whereBetween('tgl_kirim', [$prev_date, $today])->get();
        // } else {
        //     $d_kirim_pakan = $m_kirim_pakan->select('no_order')->whereBetween('tgl_kirim', [$prev_date, $today])->get();
        // }

        // $m_op = new \Model\Storage\OrderPakan_model();
        // if ( $d_kirim_pakan->count() > 0 ) {
        //     $d_kirim_pakan = $d_kirim_pakan->toArray();
        //     $d_op = $m_op->whereNotIn('no_order', $d_kirim_pakan)->whereBetween('rcn_kirim', [$prev_date, $today])->with(['d_supplier'])->orderBy('no_order', 'asc')->get();
        // } else {
        //     $d_op = $m_op->with(['d_supplier'])->orderBy('no_order', 'asc')->get();
        // }

        // if ( $d_op->count() > 0 ) {
        //     $d_op = $d_op->toArray();
        // }

        $this->result['content'] = $data;

        display_json( $this->result );
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
                $d_gudang = $m_gudang->where('jenis', 'PAKAN')->whereIn('unit', $d_wilayah)->orderBy('nama', 'asc')->get();

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
        $d_gudang = $m_gudang->where('jenis', 'PAKAN')->orderBy('nama', 'asc')->get();

        if ( $d_gudang->count() > 0 ) {
            $d_gudang = $d_gudang->toArray();
        }

        return $d_gudang;
    }

    public function get_peternak()
    {
        $params = $this->input->post('params');
        $noreg = trim((string)$this->input->post('noreg'));
        if ( !preg_match('/^[A-Za-z0-9]*$/', $noreg) ) {
            $noreg = '';
        }

        $timestamp = strtotime(substr($params, 0, 10));
        $first_date_of_month = date('Y-m-01', $timestamp);
        $last_date_of_month  = date('Y-m-t', $timestamp); // A leap year!

        // Kalau lagi edit data dengan noreg yang sudah tutup siklus, noreg itu tetap harus
        // muncul di list (supaya pilihan yang sudah tersimpan tetap kelihatan), walau
        // biasanya di-exclude oleh filter "ts.id is null" di bawah.
        $sql_tutup_siklus = "ts.id is null";
        if ( !empty($noreg) ) {
            $sql_tutup_siklus = "(ts.id is null or rs.noreg = '".$noreg."')";
        }

        $_data = array();

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.*,
                data.alamat_jalan+data.rt+data.rw+data.kelurahan+data.kecamatan as alamat
            from
            (
                select
                    rs.noreg,
                    CONVERT(varchar(10), d_noreg.tgl_docin, 103) as tgl_terima,
                    w.kode as kode_unit,
                    case when ts.id is not null then 1 else 0 end as tutup_siklus,
                    case
                        when m.alamat_rt is not null and m.alamat_rt <> '' then
                            ' ,RT.'+cast(m.alamat_rt as varchar(5))
                        else
                            ''
                    end as rt,
                    case
                        when m.alamat_rw is not null and m.alamat_rw <> '' then
                            '/RW.'+cast(m.alamat_rw as varchar(5))
                        else
                            ''
                    end as rw,
                    case
                        when m.alamat_kelurahan is not null and m.alamat_kelurahan <> '' then
                            ' ,'+m.alamat_kelurahan
                        else
                            ''
                    end as kelurahan,
                    case
                        when l_kec.nama is not null and l_kec.nama <> '' then
                            ' ,'+l_kec.nama
                        else
                            ''
                    end as kecamatan,
                    m.alamat_jalan,
                    m.nomor,
                    m.nama
                from
                ( 
                    select
                        dt.noreg,
                        dt.tgl_docin
                    from
                    (
                        select 
                            rs.noreg, 
                            cast(rs.tgl_docin as date) as tgl_docin
                        from rdim_submit rs 
                        left join
                            (
                                select od1.* from order_doc od1
                                right join
                                    (select max(id) as id, noreg from order_doc group by noreg) od2
                                    on
                                        od1.id = od2.id
                            ) od
                            on
                                rs.noreg = od.noreg
                        left join
                            (
                                select td1.* from terima_doc td1
                                right join
                                    (select max(id) as id, no_order from terima_doc group by no_order) td2
                                    on
                                        td1.id = td2.id
                            ) td
                            on
                                od.no_order = td.no_order
                        where 
                            rs.status = 1 and
                            td.id is null and
                            cast(rs.tgl_docin as date) between '".$first_date_of_month."' and '".$last_date_of_month."'
                            
                        union all
                        
                        select
                            od.noreg,
                            cast(td.datang as date) as tgl_docin
                        from terima_doc td 
                        left join
                            order_doc od 
                            on
                                td.no_order = od.no_order 
                        where
                            cast(td.datang as date) between '".$first_date_of_month."' and '".$last_date_of_month."'
                    ) dt
                    group by
                        dt.noreg,
                        dt.tgl_docin
                ) d_noreg
                left join
                    rdim_submit rs 
                    on
                        rs.noreg = d_noreg.noreg
                left join
                    kandang k
                    on
                        rs.kandang = k.id
                left join
                    wilayah w
                    on
                        w.id = k.unit 
                left join
                    (
                        select mm1.* from mitra_mapping mm1
                        right join
                            (select max(id) as id, nim from mitra_mapping group by nim) mm2
                            on
                                mm1.id = mm2.id
                    ) mm
                    on
                        rs.nim = mm.nim
                left join
                    mitra m
                    on
                        m.id = mm.mitra
                left join
                    lokasi l_kec
                    on
                        m.alamat_kecamatan = l_kec.id
                left join
                    tutup_siklus ts
                    on
                        rs.noreg = ts.noreg
                where
                    ".$sql_tutup_siklus."
            ) data
            order by
                data.kode_unit asc,
                data.tgl_terima,
                data.nama asc,
                data.noreg asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = array();
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        /*
        $m_rs = new \Model\Storage\RdimSubmit_model();
        // $d_rs = $m_rs->where('tgl_docin', '>=', $date)->get()->toArray();
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
        */

        $this->result['status'] = !empty($data) ? 1 : 0;
        $this->result['content'] = $data;

        display_json( $this->result );
    }

    public function get_pakan()
    {
        $m_brg = new \Model\Storage\Barang_model();
        $d_nomor = $m_brg->select('kode')->distinct('kode')->where('tipe', 'pakan')->get()->toArray();

        $datas = array();
        if ( !empty($d_nomor) ) {
            foreach ($d_nomor as $nomor) {
                $pelanggan = $m_brg->where('tipe', 'pakan')
                                          ->where('kode', $nomor['kode'])
                                          ->orderBy('version', 'desc')
                                          ->orderBy('id', 'desc')
                                          ->first()->toArray();

                array_push($datas, $pelanggan);
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
                $m_op = new \Model\Storage\OrderPakan_model();
                $d_op = $m_op->where('no_order', $no_order)->first();

                $m_opd = new \Model\Storage\OrderPakanDetail_model();
                $d_opd = $m_opd->where('id_header', $d_op->id)->get();

                if ( $d_opd->count() > 0 ) {
                    $data = $d_opd->toArray();
                }
            }
            $content['jenis_pengiriman'] = $jenis_pengiriman;
            $content['pakan'] = $this->get_pakan();
            $content['data'] = $data;
            $html = $this->load->view('transaksi/pengiriman_penerimaan_pakan/v_list_order_pakan', $content, TRUE);
            
            $this->result['status'] = 1;
            $this->result['content'] = $html;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function cekPembayaran()
    {
        $params = $this->input->post('params');

        try {
            $id = (isset($params['id']) && !empty($params['id'])) ? $params['id']: null;

            $status = 1;
            $message = '';

            /* CEK PEMBAYARAN */
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    kppd.*, 
                    rp.id as id_pembayaran, 
                    rp.tgl_bayar,
                    rp.tgl_realisasi,
                    case
                        when rp.tgl_realisasi is not null then
                            1
                        when rp.tgl_realisasi is null and rp.id is not null then
                            2
                        else
                            0
                    end as status
                from konfirmasi_pembayaran_pakan_det kppd
                left join
                    konfirmasi_pembayaran_pakan kpp
                    on
                        kppd.id_header = kpp.id
                left join
                    (
                        select * from realisasi_pembayaran_det where transaksi = 'PAKAN'
                    ) rpd
                    on
                        kpp.nomor = rpd.no_bayar
                left join
                    realisasi_pembayaran rp
                    on
                        rpd.id_header = rp.id
                left join
                    kirim_pakan kp
                    on
                        kppd.no_order = kp.no_order
                where
                    kp.id = '".$id."'
            ";
            $d_pembayaran = $m_conf->hydrateRaw( $sql );
            /* END - CEK PEMBAYARAN */

            if ( $d_pembayaran->count() > 0 ) {
                $d_pembayaran = $d_pembayaran->toArray()[0];

                if ( $d_pembayaran['status'] == 1 || $d_pembayaran['status'] == 2 ) {
                    $status = 0;
                    if ( empty($message)  ) {
                        if ( $d_pembayaran['status'] == 1 ) {
                            $message = 'Data sudah di lakukan pembayaran pada tanggal <b>'.tglIndonesia($d_pembayaran['tgl_realisasi'], '-', ' ').'</b>.';
                        }

                        if ( $d_pembayaran['status'] == 2 ) {
                            $message = 'Data sudah di lakukan pengajuan pembayaran pada tanggal <b>'.tglIndonesia($d_pembayaran['tgl_bayar'], '-', ' ').'</b><br><br>Jika ingin mengubah data hubungi finance untuk menghapus data pengajuan.';
                        }
                    }
                }
            }

            /* CEK CN */
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    kppd.*, 
                    cp.tanggal,
                    case
                        when cpd.nomor is not null then
                            1
                        else
                            0
                    end as status
                from konfirmasi_pembayaran_pakan_det kppd
                left join
                    konfirmasi_pembayaran_pakan kpp
                    on
                        kppd.id_header = kpp.id
                left join
                    cn_post_det cpd
                    on
                        kpp.nomor = cpd.nomor
                left join
                    cn_post cp
                    on
                        cpd.id_header = cp.id
                left join
                    kirim_pakan kp
                    on
                        kppd.no_order = kp.no_order
                where
                    kp.id = '".$id."'
            ";
            $d_cn = $m_conf->hydrateRaw( $sql );
            /* END - CEK CN */

            if ( $d_cn->count() > 0 ) {
                $d_cn = $d_cn->toArray()[0];

                if ( $d_cn['status'] == 1 ) {
                    $status = 0;
                    if ( empty($message)  ) {
                        $message = 'Data sudah di lakukan post Credit Note (CN) pada tanggal <b>'.tglIndonesia($d_cn['tanggal'], '-', ' ').'</b><br><br>Jika ingin mengubah data hubungi finance untuk menghapus data post Credit Note (CN).';
                    }
                }
            }

            $this->result['status'] = $status;
            $this->result['message'] = $message;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function cekStok()
    {
        $params = $this->input->post('params');

        try {
            $id = (isset($params['id']) && !empty($params['id'])) ? $params['id']: null;
            $no_order = (isset($params['no_order']) && !empty($params['no_order'])) ? $params['no_order'] : null;
            $tgl_kirim = $params['tgl_kirim'];
            $jenis_kirim = $params['jenis_kirim'];
            $asal = $params['asal'];
            $detail = $params['detail'];

            $status = 1;
            $message = '';

            if ( $jenis_kirim == 'opkg' ) {
                foreach ($detail as $k_det => $v_det) {
                    $kode_brg = $v_det['barang'];

                    $m_conf = new \Model\Storage\Conf();
                    $sql = "
                        select * from
                        (
                            select brg1.* from barang brg1
                            right join
                                (select max(id) as id, kode from barang group by kode) brg2
                                on
                                    brg1.id = brg2.id
                        ) brg
                        where
                            brg.kode = '".$kode_brg."'
                    ";
                    $d_brg = $m_conf->hydrateRaw( $sql );

                    $nama_brg = '';
                    if ( $d_brg->count() > 0 ) {
                        $nama_brg = $d_brg->toArray()[0]['nama'];
                    }

                    $m_conf = new \Model\Storage\Conf();
                    $sql = "
                        select
                            data.kode_gudang,
                            data.kode_barang,
                            sum(isnull(data.jumlah, 0)) as jumlah
                        from
                        (
                            select
                                ds.kode_gudang,
                                ds.kode_barang,
                                sum(isnull(ds.jml_stok, 0)) as jumlah
                            from det_stok ds
                            left join
                                stok s
                                on
                                    ds.id_header = s.id
                            where
                                s.periode = '".$tgl_kirim."' and
                                ds.kode_gudang = '".$asal."' and
                                ds.kode_barang = '".$kode_brg."'
                            group by
                                ds.kode_gudang,
                                ds.kode_barang

                            union all

                            select
                                kp.asal as kode_gudang,
                                dkp.item as kode_barang,
                                sum(isnull(dkp.jumlah, 0)) as jumlah
                            from det_kirim_pakan dkp
                            left join
                                kirim_pakan kp
                                on
                                    dkp.id_header = kp.id
                            where
                                kp.id = '".$id."' and
                                dkp.item = '".$kode_brg."'
                            group by
                                kp.asal,
                                dkp.item
                        ) data
                        group by
                            data.kode_gudang,
                            data.kode_barang
                    ";
                    $d_conf = $m_conf->hydrateRaw( $sql );

                    $jml_stok = 0;
                    if ( $d_conf->count() > 0 ) {
                        $jml_stok = $d_conf->toArray()[0]['jumlah'];
                    }

                    $m_conf = new \Model\Storage\Conf();
                    $sql = "
                        select
                            kp.asal as kode_gudang,
                            dkp.item as kode_barang,
                            sum(isnull(dkp.jumlah, 0)) as jumlah
                        from det_kirim_pakan dkp
                        left join
                            kirim_pakan kp
                            on
                                dkp.id_header = kp.id
                        left join
                            terima_pakan tp
                            on
                                kp.id = tp.id_kirim_pakan
                        where
                            kp.tgl_kirim = '".$tgl_kirim."' and
                            kp.asal = '".$asal."' and
                            dkp.item = '".$kode_brg."' and
                            kp.id <> '".$id."' and
                            tp.id is null
                        group by
                            kp.asal,
                            dkp.item
                    ";
                    $d_conf = $m_conf->hydrateRaw( $sql );

                    $jml_kirim = 0;
                    if ( $d_conf->count() > 0 ) {
                        $jml_kirim = $d_conf->toArray()[0]['jumlah'];
                    }

                    if ( $jml_stok < ($jml_kirim+$v_det['jumlah']) ) {
                        $status = 0;
                        if ( empty($message)  ) {
                            $message = 'Data yang anda masukkan tidak sesuai !!!<br><br>';
                        }

                        $message .= '<b>'.$nama_brg.'</b><br>';
                        $message .= 'STOK : '.angkaRibuan($jml_stok).' KG<br>';
                        $message .= 'PENGIRIMAN : '.angkaRibuan($jml_kirim).' KG<br>';
                        $message .= 'JUMLAH ANDA : '.angkaRibuan($v_det['jumlah']).' KG<br><br>';
                    }
                }

                if ( $status == 0 ) {
                    $message .= 'Cek data stok dan pengiriman anda.';
                }
            } else if ( $jenis_kirim == 'opkp' ) {
                foreach ($detail as $k_det => $v_det) {
                    $m_conf = new \Model\Storage\Conf();
                    $sql = "
                        select * from
                        (
                            select brg1.* from barang brg1
                            right join
                                (select max(id) as id, kode from barang group by kode) brg2
                                on
                                    brg1.id = brg2.id
                        ) brg
                        where
                            brg.kode = '".$v_det['barang']."'
                    ";
                    $d_brg = $m_conf->hydrateRaw( $sql );

                    $nama_brg = '';
                    if ( $d_brg->count() > 0 ) {
                        $nama_brg = $d_brg->toArray()[0]['nama'];
                    }

                    /*
                     * NOTE (fix 2026-08-11): sebelumnya cek "jml_terima vs jml_pakai" cuma
                     * bandingkan total pernah diterima di SJ itu vs total sudah diredistribusi
                     * via kirim_pakan LAIN yang refer ke SJ sama -- BUTA terhadap pemakaian LHK,
                     * jadi user bisa lolos memilih SJ yang stoknya sebenarnya sudah habis dimakan
                     * ayam (lihat noreg 25101370601 / OP/KDR/26/08061). Diganti cek langsung ke
                     * det_stok_siklus.jml_stok (sumber kebenaran yang sama dipakai hitung_stok_siklus,
                     * sudah otomatis memperhitungkan LHK/mutasi/retur), dikurangi klaim mutasi LAIN
                     * yang masih pending (belum tercatat di det_stok_trans_siklus) -- pola yang sama
                     * dengan reservasi yang baru ditambahkan di SP.
                     */
                    $m_conf = new \Model\Storage\Conf();
                    $sql = "
                        select
                            isnull((
                                select top 1 dss.jml_stok
                                from det_stok_siklus dss
                                where
                                    dss.noreg = '".$asal."' and
                                    dss.jenis_barang = 'pakan' and
                                    dss.kode_trans = REPLACE('".$v_det['no_sj_asal']."', 'SJ', 'OP') and
                                    dss.kode_barang = '".$v_det['barang']."'
                            ), 0) as jml_stok,
                            isnull((
                                select sum(dkp.jumlah)
                                from det_kirim_pakan dkp
                                left join
                                    kirim_pakan kp
                                    on
                                        dkp.id_header = kp.id
                                where
                                    dkp.no_sj_asal = '".$v_det['no_sj_asal']."' and
                                    dkp.item = '".$v_det['barang']."' and
                                    dkp.id_header <> '".$id."' and
                                    not exists (
                                        select * from det_stok_trans_siklus dsts
                                        where dsts.kode_trans = kp.no_order and dsts.kode_barang = dkp.item
                                    )
                            ), 0) as jml_reserved
                    ";
                    $d_stok = $m_conf->hydrateRaw( $sql );

                    $jml_stok = 0;
                    $jml_reserved = 0;
                    if ( $d_stok->count() > 0 ) {
                        $row_stok = $d_stok->toArray()[0];
                        $jml_stok = (float) $row_stok['jml_stok'];
                        $jml_reserved = (float) $row_stok['jml_reserved'];
                    }

                    $jml_tersedia = $jml_stok - $jml_reserved;

                    if ( $v_det['jumlah'] > $jml_tersedia ) {
                        $status = 0;
                        if ( empty($message)  ) {
                            $message = 'Data yang anda masukkan tidak sesuai !!!<br><br>';
                        }

                        $message .= '<b>'.$nama_brg.'</b><br>';
                        $message .= 'SJ ASAL : <b>'.$v_det['no_sj_asal'].'</b><br>';
                        $message .= 'SISA STOK (setelah pemakaian LHK & mutasi lain) : '.angkaRibuan($jml_tersedia).' KG<br>';
                        $message .= 'PINDAH : '.angkaRibuan($v_det['jumlah']).' KG<br><br>';
                    }
                }

                if ( $status == 0 ) {
                    $message .= 'Cek data sj asal yang anda masukkan.';
                }
            }

            $this->result['status'] = $status;
            $this->result['message'] = $message;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {

            // Pengiriman Pakan
                $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
                $now = $m_kirim_pakan->getDate();

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

                    $no_order = $m_kirim_pakan->getNextIdOrder('OP/'.$kode_unit);
                    $no_sj = $m_kirim_pakan->getNextIdSj('SJ/'.$kode_unit);
                }

                if ( !empty($kode_unit) ) {
                    $m_kirim_pakan->tgl_trans       = $now['waktu'];
                    $m_kirim_pakan->tgl_kirim       = $params['tgl_kirim'];
                    $m_kirim_pakan->no_order        = $no_order;
                    $m_kirim_pakan->jenis_kirim     = $params['jenis_kirim'];
                    $m_kirim_pakan->asal            = $params['asal'];
                    $m_kirim_pakan->jenis_tujuan    = $params['jenis_tujuan'];
                    $m_kirim_pakan->tujuan          = $params['tujuan'];
                    $m_kirim_pakan->ekspedisi       = $params['ekspedisi'];
                    $m_kirim_pakan->no_polisi       = $params['nopol'];
                    $m_kirim_pakan->sopir           = $params['sopir'];
                    $m_kirim_pakan->no_sj           = $no_sj;
                    $m_kirim_pakan->ongkos_angkut   = $params['ongkos_angkut'];
                    $m_kirim_pakan->ekspedisi_id    = $params['ekspedisi_id'];
                    $m_kirim_pakan->save();

                    $id_header = $m_kirim_pakan->id;

                    foreach ($params['detail'] as $k_detail => $v_detail) {
                        $m_kirim_pakan_detail = new \Model\Storage\KirimPakanDetail_model();
                        $m_kirim_pakan_detail->id_header = $id_header;
                        $m_kirim_pakan_detail->item = $v_detail['barang'];
                        $m_kirim_pakan_detail->jumlah = $v_detail['jumlah'];
                        $m_kirim_pakan_detail->kondisi = $v_detail['kondisi'];
                        $m_kirim_pakan_detail->no_sj_asal = $v_detail['no_sj_asal'];
                        $m_kirim_pakan_detail->save();
                    }

                    $d_kirim_pakan = $m_kirim_pakan->where('id', $id_header)->with(['detail'])->first();
                    $deskripsi_log_kirim_pakan = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/save', $d_kirim_pakan, $deskripsi_log_kirim_pakan);

                // End Pengiriman Pakan

                // Penerimaan Pakan

                    $path_name = null;

                    $m_kp = new \Model\Storage\KirimPakan_model();
                    $d_kp = $m_kp->where('id', $id_header)->first();

                    $no_bbm = null;
                    if ( $d_kp->jenis_kirim == 'opks' ) {
                        $no_bbm = 'BBM/PKN/S'.str_replace('OPK', '', $d_kp->no_order);
                    } else if ( $d_kp->jenis_kirim == 'opkg' ) {
                        $no_bbm = 'BBM/PKN/G'.str_replace('OP', '', $d_kp->no_order);
                    } else if ( $d_kp->jenis_kirim == 'opkp' ) {
                        $no_bbm = 'BBM/PKN/P'.str_replace('OP', '', $d_kp->no_order);
                    }

                    $m_terima_pakan                 = new \Model\Storage\TerimaPakan_model();
                    $now                            = $m_terima_pakan->getDate();
                    $m_terima_pakan->id_kirim_pakan = $id_header;
                    $m_terima_pakan->tgl_trans      = $now['waktu'];
                    $m_terima_pakan->tgl_terima     = $params['tgl_terima'];
                    $m_terima_pakan->path           = $path_name;
                    $m_terima_pakan->no_bbm         = $no_bbm;
                    $m_terima_pakan->save();

                    $id_terima = $m_terima_pakan->id;

                    $detail_merge = [];

                    foreach ($params['detail'] as $v_detail) {
                        $key = $v_detail['barang'];

                        if ( isset($detail_merge[$key]) ) {
                            $detail_merge[$key]['jumlah'] += $v_detail['jumlah'];
                        } else {
                            $detail_merge[$key] = [
                                'barang' => $v_detail['barang'],
                                'jumlah' => $v_detail['jumlah'],
                                'kondisi' => $v_detail['kondisi'],
                                'no_sj_asal' => $v_detail['no_sj_asal']
                            ];
                        }
                    }

                    // foreach ($params['detail'] as $k_detail => $v_detail) {
                    //     $m_terima_pakan_detail              = new \Model\Storage\TerimaPakanDetail_model();
                    //     $m_terima_pakan_detail->id_header   = $id_terima;
                    //     $m_terima_pakan_detail->item        = $v_detail['barang'];
                    //     $m_terima_pakan_detail->jumlah      = $v_detail['jumlah'];
                    //     $m_terima_pakan_detail->kondisi     = $v_detail['kondisi'];
                    //     $m_terima_pakan_detail->save();
                    // }

                    foreach ($detail_merge as $v_detail) {
                        $m_terima_pakan_detail              = new \Model\Storage\TerimaPakanDetail_model();
                        $m_terima_pakan_detail->id_header   = $id_terima;
                        $m_terima_pakan_detail->item        = $v_detail['barang'];
                        $m_terima_pakan_detail->jumlah      = $v_detail['jumlah'];
                        $m_terima_pakan_detail->kondisi     = $v_detail['kondisi'];
                        $m_terima_pakan_detail->save();
                    }

                    $d_terima_pakan = $m_terima_pakan->where('id', $id_terima)->with(['detail'])->first();
                    $deskripsi_log_terima_pakan = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/save', $d_terima_pakan, $deskripsi_log_terima_pakan);

                    $noreg1 = null;
                    $noreg2 = null;
                    $m_conf = new \Model\Storage\Conf();
                    $sql = "
                        select tp.id, kp.jenis_kirim, kp.jenis_tujuan, kp.asal, kp.tujuan from terima_pakan tp
                        left join
                            kirim_pakan kp
                            on
                                tp.id_kirim_pakan = kp.id
                        where
                            tp.id = '".$id_terima."'
                    ";
                    $d_conf = $m_conf->hydrateRaw( $sql );
                    if ( $d_conf->count() > 0 ) {
                        $d_conf = $d_conf->toArray()[0];

                        if ( $d_conf['jenis_kirim'] == 'opkg' ) {
                            if ( $d_conf['jenis_tujuan'] == 'peternak' ) {
                                $noreg1 = $d_conf['tujuan'];
                            }
                        }

                        if ( $d_conf['jenis_kirim'] == 'opkp' ) {
                            $noreg1 = $d_conf['asal'];
                            $noreg2 = $d_conf['tujuan'];
                        }
                    }
                // End Penerimaan Pakan

                $tgl_trans = $params['tgl_terima'];

                /* JIKA PINDAH PAKAN, CEK TANGGAL */
                if ( $params['jenis_kirim'] == 'opkp' ) {
                    $m_conf = new \Model\Storage\Conf();
                    $sql = "
                        select min(tp_asal.tgl_terima) as tgl_trans from terima_pakan tp
                        left join
                            kirim_pakan kp
                            on
                                tp.id_kirim_pakan = kp.id
                        left join
                            det_kirim_pakan dkp
                            on
                                dkp.id_header = kp.id
                        left join
                            kirim_pakan kp_asal
                            on
                                dkp.no_sj_asal = kp_asal.no_sj
                        left join
                            terima_pakan tp_asal
                            on
                                kp_asal.id = tp_asal.id_kirim_pakan
                        where
                            tp.id = '".$id_terima."'
                    ";
                    $d_tgl_pp = $m_conf->hydrateRaw( $sql );
        
                    if ( $d_tgl_pp->count() > 0 ) {
                        $d_tgl_pp = $d_tgl_pp->toArray()[0]['tgl_trans'];
        
                        if ( $d_tgl_pp < $tgl_trans ) {
                            $tgl_trans = $d_tgl_pp;
                        }
                    }
                }
                /* END - JIKA PINDAH PAKAN, CEK TANGGAL */

                $id = $id_terima;
                $id_old = $id_terima;
                $tanggal = $params['tgl_terima'];
                $status = 2;
                $status_jurnal = 2;
                $delete = 0;

                $noreg1_old = $noreg1;
                $noreg2_old = $noreg2;

                $this->insertKonfirmasi( $id, $delete );

                $sql = "EXEC hitung_stok_pakan_by_transaksi 'terima_pakan', '".$id."', '".$tanggal."', ".$delete.", ".$status_jurnal."";
                $return = Modules::run( 'base/ExecStoredProcedure/exec', $sql);

                $sql = "EXEC hitung_stok_siklus 'pakan', 'terima_pakan', '".$id."', '".$tanggal."', ".$status.", '".$noreg1."', '".$noreg2."'";
                $return = Modules::run( 'base/ExecStoredProcedure/exec', $sql);

                if ( !empty($noreg1_old) || !empty($noreg2_old) ) {
                    if ( $noreg1 <> $noreg1_old || $noreg2 <> $noreg2_old ) {
                        $sql = "EXEC hitung_stok_siklus 'pakan', 'terima_pakan', '".$id."', '".$tanggal."', ".$status.", '".$noreg1_old."', '".$noreg2_old."'";
                        $return = Modules::run( 'base/ExecStoredProcedure/exec', $sql);
                    }
                }

                $return = Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, $status);

                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select id_kirim_pakan from terima_pakan tp where tp.id = '".$id_terima."'
                ";
                $d_conf = $m_conf->hydrateRaw( $sql );

                $id_kirim_pakan = null;
                if ( $d_conf->count() > 0 ) {
                    $id_kirim_pakan = $d_conf->toArray()[0]['id_kirim_pakan'];
                }

                $this->result['status'] = 1;
                $this->result['message'] = 'Data Pengiriman Pakan berhasil di simpan.';
                $this->result['content'] = array(
                    'id' => $id_terima,
                    'tanggal' => $tgl_trans,
                    'delete' => 0,
                    'message' => 'Data Penerimaan Pakan berhasil di simpan.',
                    'status_jurnal' => 2,
                    'status' => 2,
                    'noreg1' => $noreg1,
                    'noreg2' => $noreg2,
                    'noreg1_old' => $noreg1,
                    'noreg2_old' => $noreg2,
                    'id_kirim_paakn' => $id_kirim_pakan
                );
            } else {
                $this->result['message'] = 'Kode unit masih kosong, harap lengkapi kode unit terlebih dahulu.';
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit(){
        $params = $this->input->post('params');

        try {
            // Pengiriman 
            $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
            $now = $m_kirim_pakan->getDate();
            $d_kp_old = $m_kirim_pakan->where('id', $params['id'])->first();

            $noreg1_old = null;
            $noreg2_old = null;
            if ( $d_kp_old->jenis_kirim == 'opkg' ) {
                if ( $d_kp_old->jenis_tujuan == 'peternak' ) {
                    $noreg1_old = $d_kp_old->tujuan;
                }
            }

            if ( $d_kp_old->jenis_kirim == 'opkp' ) {
                $noreg1_old = $d_kp_old->asal;
                $noreg2_old = $d_kp_old->tujuan;
            }

            $m_kirim_pakan->where('id', $params['id'])->update(
                array(
                    'tgl_trans'     => $now['waktu'],
                    'tgl_kirim'     => $params['tgl_kirim'],
                    'no_order'      => $params['no_order'],
                    'jenis_kirim'   => $params['jenis_kirim'],
                    'asal'          => $params['asal'],
                    'jenis_tujuan'  => $params['jenis_tujuan'],
                    'tujuan'        => $params['tujuan'],
                    'ekspedisi'     => $params['ekspedisi'],
                    'no_polisi'     => $params['nopol'],
                    'sopir'         => $params['sopir'],
                    'no_sj'         => $params['no_sj'],
                    'ongkos_angkut' => $params['ongkos_angkut'],
                    'ekspedisi_id'  => $params['ekspedisi_id']
                )
            );

            $id_header = $params['id'];

            $m_kirim_pakan_detail = new \Model\Storage\KirimPakanDetail_model();
            $m_kirim_pakan_detail->where('id_header', $id_header)->delete();

            foreach ($params['detail'] as $k_detail => $v_detail) {
                $m_kirim_pakan_detail = new \Model\Storage\KirimPakanDetail_model();
                $m_kirim_pakan_detail->id_header = $id_header;
                $m_kirim_pakan_detail->item = $v_detail['barang'];
                $m_kirim_pakan_detail->jumlah = $v_detail['jumlah'];
                $m_kirim_pakan_detail->kondisi = $v_detail['kondisi'];
                $m_kirim_pakan_detail->no_sj_asal = $v_detail['no_sj_asal'];
                $m_kirim_pakan_detail->save();
            }
            // End Pengiriman

            // Penerimaan Pakan
            $path_name = null;

            $m_kp = new \Model\Storage\KirimPakan_model();
            $d_kp = $m_kp->where('id', $id_header)->first();

            $m_terima_pakan                 = new \Model\Storage\TerimaPakan_model();
            $d_terima_old = $m_terima_pakan->where('id_kirim_pakan', $params['id'])->first();

            $no_bbm = null;
            if ( $d_kp->jenis_kirim == 'opks' ) {
                $no_bbm = 'BBM/PKN/S'.str_replace('OPK', '', $d_kp->no_order);
            } else if ( $d_kp->jenis_kirim == 'opkg' ) {
                $no_bbm = 'BBM/PKN/G'.str_replace('OP', '', $d_kp->no_order);
            } else if ( $d_kp->jenis_kirim == 'opkp' ) {
                $no_bbm = 'BBM/PKN/P'.str_replace('OP', '', $d_kp->no_order);
            }

            if ( !$d_terima_old ) {
                
                $m_terima_pakan                 = new \Model\Storage\TerimaPakan_model();
                $now                            = $m_terima_pakan->getDate();

                $m_terima_pakan->id_kirim_pakan = $params['id'];
                $m_terima_pakan->tgl_trans      = $now['waktu'];
                $m_terima_pakan->tgl_terima     = $params['tgl_terima'];
                $m_terima_pakan->path           = $path_name;
                $m_terima_pakan->no_bbm         = $no_bbm;
                $m_terima_pakan->save();
    
                $id_terima = $m_terima_pakan->id;
    
                $detail_merge = [];
    
                foreach ($params['detail'] as $v_detail) {
                    $key = $v_detail['barang'];
    
                    if ( isset($detail_merge[$key]) ) {
                        $detail_merge[$key]['jumlah'] += $v_detail['jumlah'];
                    } else {
                        $detail_merge[$key] = [
                            'barang' => $v_detail['barang'],
                            'jumlah' => $v_detail['jumlah'],
                            'kondisi' => $v_detail['kondisi'],
                            'no_sj_asal' => $v_detail['no_sj_asal']
                        ];
                    }
                }

                foreach ($detail_merge as $v_detail) {
                    $m_terima_pakan_detail              = new \Model\Storage\TerimaPakanDetail_model();
                    $m_terima_pakan_detail->id_header   = $id_terima;
                    $m_terima_pakan_detail->item        = $v_detail['barang'];
                    $m_terima_pakan_detail->jumlah      = $v_detail['jumlah'];
                    $m_terima_pakan_detail->kondisi     = $v_detail['kondisi'];
                    $m_terima_pakan_detail->save();
                }
            } else {    
                $m_terima_pakan                 = new \Model\Storage\TerimaPakan_model();
                $now                            = $m_terima_pakan->getDate();
    
                $d_terima_old = $m_terima_pakan->where('id_kirim_pakan', $params['id'])->first();
    
                $m_terima_pakan->where('id_kirim_pakan', $params['id'])->update(
                    array(
                        'tgl_trans'     => $now['waktu'],
                        'tgl_terima'    => $params['tgl_terima'],
                        'no_bbm'        => $no_bbm,
                        'path'          => $params['jenis_kirim'],
                    )
                );
    
                
                $d_terima = $m_terima_pakan->where('id_kirim_pakan', $params['id'])->first();
                $id_terima = !empty($d_terima) ? $d_terima->id : null;
    
                if ( !empty($id_terima) ) {
                    $m_terima_pakan_detail = new \Model\Storage\TerimaPakanDetail_model();
                    $m_terima_pakan_detail->where('id_header', $id_terima)->delete();
    
                    $detail_merge = [];
    
                    foreach ($params['detail'] as $v_detail) {
                        $key = $v_detail['barang'];
    
                        if ( isset($detail_merge[$key]) ) {
                            $detail_merge[$key]['jumlah'] += $v_detail['jumlah'];
                        } else {
                            $detail_merge[$key] = [
                                'barang' => $v_detail['barang'],
                                'jumlah' => $v_detail['jumlah'],
                                'kondisi' => $v_detail['kondisi'],
                                'no_sj_asal' => $v_detail['no_sj_asal']
                            ];
                        }
                    }
    
                    // foreach ($params['detail'] as $k_detail => $v_detail) {
                    //     $m_terima_pakan_detail              = new \Model\Storage\TerimaPakanDetail_model();
                    //     $m_terima_pakan_detail->id_header   = $id_terima;
                    //     $m_terima_pakan_detail->item        = $v_detail['barang'];
                    //     $m_terima_pakan_detail->jumlah      = $v_detail['jumlah'];
                    //     $m_terima_pakan_detail->kondisi     = $v_detail['kondisi'];
                    //     $m_terima_pakan_detail->save();
                    // }
    
                    foreach ($detail_merge as $v_detail) {
                        $m_terima_pakan_detail              = new \Model\Storage\TerimaPakanDetail_model();
                        $m_terima_pakan_detail->id_header   = $id_terima;
                        $m_terima_pakan_detail->item        = $v_detail['barang'];
                        $m_terima_pakan_detail->jumlah      = $v_detail['jumlah'];
                        $m_terima_pakan_detail->kondisi     = $v_detail['kondisi'];
                        $m_terima_pakan_detail->save();
                    }
                }
            }
            // End Penerimaan Pakan

            // Log Update
            $d_kirim_pakan  = $m_kirim_pakan->where('id', $id_header)->with(['detail'])->first();
            $d_terima_pakan = $m_terima_pakan->where('id', $id_terima)->with(['detail'])->first();
                    
            $deskripsi_log_kirim_pakan = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_kirim_pakan, $deskripsi_log_kirim_pakan);

            $deskripsi_log_terima_pakan = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_terima_pakan, $deskripsi_log_terima_pakan);
            // End Log Update

            $tgl_trans = $d_terima_pakan->tgl_terima;
            if ( !empty($d_terima_old) && $d_terima_old->tgl_terima < $tgl_trans ) {
                $tgl_trans = $d_terima_old->tgl_terima;
            }

            /* JIKA PINDAH PAKAN, CEK TANGGAL */
            if ( $params['jenis_kirim'] == 'opkp' ) {
                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select min(tp_asal.tgl_terima) as tgl_trans from terima_pakan tp
                    left join
                        kirim_pakan kp
                        on
                            tp.id_kirim_pakan = kp.id
                    left join
                        det_kirim_pakan dkp
                        on
                            dkp.id_header = kp.id
                    left join
                        kirim_pakan kp_asal
                        on
                            dkp.no_sj_asal = kp_asal.no_sj
                    left join
                        terima_pakan tp_asal
                        on
                            kp_asal.id = tp_asal.id_kirim_pakan
                    where
                        tp.id = '".$id_terima."'
                ";
                $d_tgl_pp = $m_conf->hydrateRaw( $sql );
    
                if ( $d_tgl_pp->count() > 0 ) {
                    $d_tgl_pp = $d_tgl_pp->toArray()[0]['tgl_trans'];
    
                    if ( $d_tgl_pp < $tgl_trans ) {
                        $tgl_trans = $d_tgl_pp;
                    }
                }
            }
            /* END - JIKA PINDAH PAKAN, CEK TANGGAL */

            $noreg1 = null;
            $noreg2 = null;
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select tp.id, kp.jenis_kirim, kp.jenis_tujuan, kp.asal, kp.tujuan from terima_pakan tp
                left join
                    kirim_pakan kp
                    on
                        tp.id_kirim_pakan = kp.id
                where
                    tp.id = '".$id_terima."'
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray()[0];

                if ( $d_conf['jenis_kirim'] == 'opkg' ) {
                    if ( $d_conf['jenis_tujuan'] == 'peternak' ) {
                        $noreg1 = $d_conf['tujuan'];
                    }
                }

                if ( $d_conf['jenis_kirim'] == 'opkp' ) {
                    $noreg1 = $d_conf['asal'];
                    $noreg2 = $d_conf['tujuan'];
                }
            }

            $id = $d_terima_pakan->id;
            $id_old = $d_terima_pakan->id;
            $tanggal = $tgl_trans;
            $status = 2;
            $status_jurnal = 2;
            $delete = 0;

            $this->insertKonfirmasi( $id, $delete );

            $sql = "EXEC hitung_stok_pakan_by_transaksi 'terima_pakan', '".$id."', '".$tanggal."', ".$delete.", ".$status_jurnal."";
            $return = Modules::run( 'base/ExecStoredProcedure/exec', $sql);

            $sql = "EXEC hitung_stok_siklus 'pakan', 'terima_pakan', '".$id."', '".$tanggal."', ".$status.", '".$noreg1."', '".$noreg2."'";
            $return = Modules::run( 'base/ExecStoredProcedure/exec', $sql);

            if ( !empty($noreg1_old) || !empty($noreg2_old) ) {
                if ( $noreg1 <> $noreg1_old || $noreg2 <> $noreg2_old ) {
                    $sql = "EXEC hitung_stok_siklus 'pakan', 'terima_pakan', '".$id."', '".$tanggal."', ".$status.", '".$noreg1_old."', '".$noreg2_old."'";
                    $return = Modules::run( 'base/ExecStoredProcedure/exec', $sql);
                }
            }

            $return = Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, $status);

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select id_kirim_pakan from terima_pakan tp where tp.id = '".$d_terima_pakan->id."'
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $id_kirim_pakan = null;
            if ( $d_conf->count() > 0 ) {
                $id_kirim_pakan = $d_conf->toArray()[0]['id_kirim_pakan'];
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Pengiriman Pakan berhasil di ubah.';
            $this->result['content'] = array(
                    'id' => $d_terima_pakan->id,
                    'tanggal' => $tgl_trans,
                    'delete' => 0,
                    'message' => 'Data Penerimaan Pakan berhasil di ubah.',
                    'status_jurnal' => 2,
                    'status' => 2,
                    'noreg1' => $noreg1,
                    'noreg2' => $noreg2,
                    'noreg1_old' => $noreg1_old,
                    'noreg2_old' => $noreg2_old,
                    'id_kirim_pakan' => $id_kirim_pakan
                );
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }


    public function delete()
    {
        $params = $this->input->post('params');

        try {
            $noreg1 = null;
            $noreg2 = null;
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select tp.id, kp.jenis_kirim, kp.jenis_tujuan, kp.asal, kp.tujuan from terima_pakan tp
                left join
                    kirim_pakan kp
                    on
                        tp.id_kirim_pakan = kp.id
                where
                    tp.id_kirim_pakan = '".$params['id']."'
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray()[0];

                if ( $d_conf['jenis_kirim'] == 'opkg' ) {
                    if ( $d_conf['jenis_tujuan'] == 'peternak' ) {
                        $noreg1 = $d_conf['tujuan'];
                    }
                }

                if ( $d_conf['jenis_kirim'] == 'opkp' ) {
                    $noreg1 = $d_conf['asal'];
                    $noreg2 = $d_conf['tujuan'];
                }
            }

            // Pengiriman
            $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
            $now = $m_kirim_pakan->getDate();

            $d_kirim_pakan = $m_kirim_pakan->where('id', $params['id'])->with(['detail'])->first();
            $deskripsi_log_kirim_pakan = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_kirim_pakan, $deskripsi_log_kirim_pakan);

            // $m_kirim_pakan_detail = new \Model\Storage\KirimPakanDetail_model();
            // $m_kirim_pakan_detail->where('id_header', $params['id'])->delete();
            // $m_kirim_pakan->where('id', $params['id'])->delete();
            // End Pengiriman

            $m_terima_pakan = new \Model\Storage\TerimaPakan_model();
            $now = $m_terima_pakan->getDate();

            $d_terima_pakan = $m_terima_pakan->where('id_kirim_pakan', $params['id'])->with(['detail'])->first();

            if ( $d_terima_pakan ) {
                $deskripsi_log_terima_pakan = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $d_terima_pakan, $deskripsi_log_terima_pakan);
    
                $tgl_trans = $d_terima_pakan->tgl_terima;
    
                /* JIKA PINDAH PAKAN, CEK TANGGAL */
                if ( $d_kirim_pakan->jenis_kirim == 'opkp' ) {
                    $m_conf = new \Model\Storage\Conf();
                    $sql = "
                        select min(tp_asal.tgl_terima) as tgl_trans from terima_pakan tp
                        left join
                            kirim_pakan kp
                            on
                                tp.id_kirim_pakan = kp.id
                        left join
                            det_kirim_pakan dkp
                            on
                                dkp.id_header = kp.id
                        left join
                            kirim_pakan kp_asal
                            on
                                dkp.no_sj_asal = kp_asal.no_sj
                        left join
                            terima_pakan tp_asal
                            on
                                kp_asal.id = tp_asal.id_kirim_pakan
                        where
                            tp.id = '".$d_terima_pakan->id."'
                    ";
                    $d_tgl_pp = $m_conf->hydrateRaw( $sql );
        
                    if ( $d_tgl_pp->count() > 0 ) {
                        $d_tgl_pp = $d_tgl_pp->toArray()[0]['tgl_trans'];
        
                        if ( $d_tgl_pp < $tgl_trans ) {
                            $tgl_trans = $d_tgl_pp;
                        }
                    }
                }
                /* END - JIKA PINDAH PAKAN, CEK TANGGAL */
    
                $id = $d_terima_pakan->id;
                $id_old = $d_terima_pakan->id;
                $tanggal = $tgl_trans;
                $status = 3;
                $status_jurnal = 3;
                $delete = 1;
    
                $noreg1_old = $noreg1;
                $noreg2_old = $noreg2;
    
                $this->insertKonfirmasi( $id, $delete );
    
                $sql = "EXEC hitung_stok_pakan_by_transaksi 'terima_pakan', '".$id."', '".$tanggal."', ".$delete.", ".$status_jurnal."";
                $return = Modules::run( 'base/ExecStoredProcedure/exec', $sql);
    
                $sql = "EXEC hitung_stok_siklus 'pakan', 'terima_pakan', '".$id."', '".$tanggal."', ".$status.", '".$noreg1."', '".$noreg2."'";
                $return = Modules::run( 'base/ExecStoredProcedure/exec', $sql);
    
                if ( !empty($noreg1_old) || !empty($noreg2_old) ) {
                    if ( $noreg1 <> $noreg1_old || $noreg2 <> $noreg2_old ) {
                        $sql = "EXEC hitung_stok_siklus 'pakan', 'terima_pakan', '".$id."', '".$tanggal."', ".$status.", '".$noreg1_old."', '".$noreg2_old."'";
                        $return = Modules::run( 'base/ExecStoredProcedure/exec', $sql);
                    }
                }
    
                $return = Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, $status);
            }
                
            $this->result['message'] = 'Data Pengiriman Pakan berhasil di hapus.';
            $this->result['status'] = 1;
            $this->result['content'] = array(
                // 'id' => $d_terima_pakan->id,
                'id' => null,
                'tanggal' => $tgl_trans,
                'delete' => 1,
                'message' => 'Data Penerimaan Pakan berhasil di hapus.',
                'status_jurnal' => 3,
                'status' => 3,
                'noreg1' => $noreg1,
                'noreg2' => $noreg2,
                'id_kirim_pakan' => null
            );
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function execInsertKonfirmasi()
    {
        $params = $this->input->post('params');

        try {
            $id = $params['id'];
            $status = $params['status'];
            $delete = ($status == 3) ? 1 : 0;

            $this->insertKonfirmasi( $id, $delete );

            $this->result['status'] = 1;
            $this->result['content'] = $params;
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
                
                $d_kpd = $m_kpp->where('id', $id)->first();

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $d_kpd, $deskripsi_log);
            }
        }
    }

    public function hitungStokByTransaksi()
    {
        $params = $this->input->post('params');

        $id = $params['id'];
        $tanggal = $params['tanggal'];
        $delete = $params['delete'];
        $status_jurnal = $params['status_jurnal'];

        try {
            $sql = "EXEC hitung_stok_pakan_by_transaksi 'terima_pakan', '".$id."', '".$tanggal."', ".$delete.", ".$status_jurnal."";
            $return = Modules::run( 'base/ExecStoredProcedure/exec', $sql);

            $this->result['status'] = $return['status'];
            $this->result['message'] = json_encode($return);
            $this->result['content'] = $params;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function execHitStokSiklus() {
        $params = $this->input->post('params');

        try {
            $id = $params['id'];
            $tanggal = $params['tanggal'];
            $status = $params['status'];
            $noreg1 = $params['noreg1'];
            $noreg2 = $params['noreg2'];
            $noreg1_old = (!empty($params['noreg1_old']) && isset($params['noreg1_old'])) ? $params['noreg1_old'] : null;
            $noreg2_old = (!empty($params['noreg2_old']) && isset($params['noreg2_old'])) ? $params['noreg2_old'] : null;

            $sql = "EXEC hitung_stok_siklus 'pakan', 'terima_pakan', '".$id."', '".$tanggal."', ".$status.", '".$noreg1."', '".$noreg2."'";
            $return = Modules::run( 'base/ExecStoredProcedure/exec', $sql);

            if ( !empty($noreg1_old) || !empty($noreg2_old) ) {
                if ( $noreg1 <> $noreg1_old || $noreg2 <> $noreg2_old ) {
                    $sql = "EXEC hitung_stok_siklus 'pakan', 'terima_pakan', '".$id."', '".$tanggal."', ".$status.", '".$noreg1_old."', '".$noreg2_old."'";
                    $return = Modules::run( 'base/ExecStoredProcedure/exec', $sql);
                }
            }

            $this->result['status'] = $return['status'];
            $this->result['message'] = json_encode($return);
            $this->result['content'] = $params;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }


    public function execInsertJurnal() {
        $params = $this->input->post('params');

        try {
            $id = $params['id'];
            $id_old = $params['id'];
            $status = $params['status'];

            $return = Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, $status);

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select id_kirim_pakan from terima_pakan tp where tp.id = '".$id."'
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $id_kirim_pakan = null;
            if ( $d_conf->count() > 0 ) {
                $id_kirim_pakan = $d_conf->toArray()[0]['id_kirim_pakan'];
            }

            $_params = $params;
            $_params['id_kirim_pakan'] = $id_kirim_pakan;

            $this->result['status'] = $return['status'];
            $this->result['message'] = json_encode($return);
            $this->result['content'] = $_params;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
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
            //     // $tgl_akhir = '2022-07-25';

            //     /* BARANG MASUK */
            //     $m_dstok = new \Model\Storage\DetStok_model();
            //     $stok_masuk += $m_dstok->where('id_header', $d_stok->id)->where('kode_gudang', $params['gudang'])->where('kode_barang', $params['item'])->sum('jumlah');

            //     /* KIRIM PAKAN */
            //     $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
            //     $d_kp_ke_gudang = $m_kirim_pakan->select('id')->whereBetween('tgl_kirim', [$tgl_awal, $tgl_akhir])->where('jenis_tujuan', 'gudang')->where('tujuan', $params['gudang'])->get();

            //     if ( $d_kp_ke_gudang->count() > 0 ) {
            //         $d_kp_ke_gudang = $d_kp_ke_gudang->toArray();
            //         foreach ($d_kp_ke_gudang as $key => $value) {
            //             $m_tp = new \Model\Storage\TerimaPakan_model();
            //             $d_tp = $m_tp->select('id')->where('id_kirim_pakan', $value['id'])->orderBy('id', 'desc')->first();

            //             if ( !empty($d_tp) ) {
            //                 $d_tp = $d_tp->toArray();

            //                 $m_dtp = new \Model\Storage\TerimaPakanDetail_model();
            //                 $stok_masuk += $m_dtp->whereIn('id_header', $d_tp)->where('item', $params['item'])->sum('jumlah');
            //             }
            //         }
            //     }

            //     /* RETUR PAKAN */
            //     $m_retur_pakan = new \Model\Storage\ReturPakan_model();
            //     $d_rp_ke_gudang = $m_retur_pakan->select('id')->whereBetween('tgl_retur', [$tgl_awal, $tgl_akhir])->where('tujuan', 'gudang')->where('id_tujuan', $params['gudang'])->get();

            //     if ( $d_rp_ke_gudang->count() > 0 ) {
            //         $d_rp_ke_gudang = $d_rp_ke_gudang->toArray();
            //         $m_drp = new \Model\Storage\DetReturPakan_model();
            //         $stok_masuk += $m_drp->whereIn('id_header', $d_rp_ke_gudang)->where('item', $params['item'])->sum('jumlah');
            //     }
            //     /* END - BARANG MASUK */

            //     /* BARANG KELUAR  */
            //     /* KIRIM PAKAN */
            //     $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
            //     $d_kp_ke_peternak = $m_kirim_pakan->select('id')->whereBetween('tgl_kirim', [$tgl_awal, $tgl_akhir])->where('jenis_kirim', 'opkg')->where('asal', $params['gudang'])->where('jenis_tujuan', 'peternak')->get();

            //     if ( $d_kp_ke_peternak->count() > 0 ) {
            //         $d_kp_ke_peternak = $d_kp_ke_peternak->toArray();
            //         foreach ($d_kp_ke_peternak as $key => $value) {
            //             $m_tp = new \Model\Storage\TerimaPakan_model();
            //             $d_tp = $m_tp->select('id')->where('id_kirim_pakan', $value['id'])->orderBy('id', 'desc')->first();

            //             if ( !empty($d_tp) ) {
            //                 $d_tp = $d_tp->toArray();

            //                 $m_dtp = new \Model\Storage\TerimaPakanDetail_model();
            //                 $stok_keluar += $m_dtp->whereIn('id_header', $d_tp)->where('item', $params['item'])->sum('jumlah');
            //             }
            //         }
            //     }

            //     /* RETUR PAKAN */
            //     $m_retur_pakan = new \Model\Storage\ReturPakan_model();
            //     $d_rp_dari_gudang = $m_retur_pakan->select('id')->whereBetween('tgl_retur', [$tgl_awal, $tgl_akhir])->where('asal', 'gudang')->where('id_asal', $params['gudang'])->get();

            //     if ( $d_rp_dari_gudang->count() > 0 ) {
            //         $d_rp_dari_gudang = $d_rp_dari_gudang->toArray();
            //         $m_drp = new \Model\Storage\DetReturPakan_model();
            //         $stok_keluar += $m_drp->whereIn('id_header', $d_rp_dari_gudang)->where('item', $params['item'])->sum('jumlah');
            //     }
            //     /* END - BARANG KELUAR */
            // }

            $tanggal = date('Y-m-d');

            $start_date = prev_date($tanggal, 7);
            $end_date = $tanggal;

            $sql = "
                select sum(dkp.jumlah) as jumlah from det_kirim_pakan dkp
                left join 
                    kirim_pakan kp
                    on
                        dkp.id_header = kp.id
                where
                    dkp.item = '".$params['item']."' and
                    kp.tgl_kirim between '".$start_date."' and '".$end_date."' and
                    kp.asal = '".$params['gudang']."' and
                    not exists (
                        select * from terima_pakan tp where tp.id_kirim_pakan = kp.id
                    ) and
                    dkp.no_sj_asal is null and
                    kp.jenis_kirim = 'opkg'
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
    

        $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
        $d_kirim_pakan = $m_kirim_pakan->where('id', $params['id'])->with(['logs'])->first()->toArray();

        $data_kirim_pakan = array(
            'no_order'      => $params['no_order'],
            'tgl_kirim'     => $params['tgl_kirim'],
            'asal'          => $params['asal'],
            'tujuan'        => $params['tujuan'],
            'nopol'         => $params['nopol'],
            'logs'          => $d_kirim_pakan['logs']
        );

        $m_terima_pakan = new \Model\Storage\TerimaPakan_model();
        $d_terima_pakan = $m_terima_pakan->where('id_kirim_pakan', $params['id'])->with(['logs'])->first()->toArray();

        $data_terima_pakan = array(
            'no_sj'         => $d_kirim_pakan['no_sj'],
            'tgl_terima'    => $d_terima_pakan['tgl_terima'],
            'asal'          => $params['asal'],
            'tujuan'        => $params['tujuan'],
            'nopol'         => $params['nopol'],
            'logs'          => $d_terima_pakan['logs']
        );

        // echo "<pre>";
        // print_r($data_terima_pakan);
        // die;

        $content['data_kirim']    = $data_kirim_pakan;
        $content['data_terima']   = $data_terima_pakan;

        $html = $this->load->view('transaksi/pengiriman_penerimaan_pakan/v_list_activity', $content, true);

        echo $html;
    }

    public function getSjAsal()
    {
        $params = $this->input->post('params');
        try {
            $noreg = $params['noreg'];

            $data = $this->getDataSjAsal( $noreg );
            
            $this->result['status'] = 1;
            $this->result['content'] = array('data' => $data);
        } catch (Exception $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json( $this->result );
    }

    public function getDataSjAsal($noreg)
    {
        $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
        $d_kirim_pakan = $m_kirim_pakan->where('tujuan', $noreg)->with(['terima'])->get();

        $data = array();
        if ( $d_kirim_pakan->count() > 0 ) {
            $d_kirim_pakan = $d_kirim_pakan->toArray();

            $_data = null;
            foreach ($d_kirim_pakan as $k => $v) {
                if ( !empty($v['terima']) ) {
                    $m_dtp = new \Model\Storage\TerimaPakanDetail_model();
                    $d_dtp = $m_dtp->where('id_header', $v['terima']['id'])->where('jumlah', '>', 0)->with(['d_barang'])->get();

                    $barang = null;
                    if ( $d_dtp->count() > 0 ) {
                        $d_dtp = $d_dtp->toArray();

                        $_barang = null;
                        foreach ($d_dtp as $k_dtp => $v_dtp) {
                            $key = $v_dtp['item'];

                            if ( !isset($_barang[ $key ]) ) {
                                $_barang[ $key ] = array(
                                    'kode' => $v_dtp['item'],
                                    'nama' => $v_dtp['d_barang']['nama'],
                                    'jumlah' => $v_dtp['jumlah']
                                );
                            } else {
                                $_barang[ $key ]['jumlah'] += $v_dtp['jumlah'];
                            }
                        }

                        if ( !empty($_barang) ) {
                            foreach ($_barang as $key => $value) {
                                $barang[] = $value;
                            }
                        }
                    }

                    $key = str_replace('-', '', $v['terima']['tgl_terima']).'|'.$v['no_sj'];
                    $_data[ $key ] = array(
                        'text_tgl' => strtoupper((tglIndonesia($v['terima']['tgl_terima'], '-', ' '))), 
                        'no_sj' => $v['no_sj'],
                        'barang' => $barang
                    );
                }
            }

            if ( !empty($_data) ) {
                ksort($_data);

                foreach ($_data as $k_data => $v_data) {
                    $data[] = $v_data;
                }
            }
        }

        return $data;
    }

    public function cetak_nota_kiriman($id) {
        $this->load->library('PDFGenerator');

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                kp.no_sj,
                kp.tgl_trans as tanggal,
                kp.no_polisi,
                kp.sopir,
                mtr.*,
                dkp.item,
                dkp.jumlah,
                (dkp.jumlah / 50) as zak,
                brg.nama as nama_barang,
                dkp.kondisi as keterangan
            from det_kirim_pakan dkp 
            left join
                kirim_pakan kp
                on
                    dkp.id_header = kp.id
            left join
                (
                    select brg1.* from barang brg1
                    right join
                        (select max(id) as id, kode from barang where tipe = 'pakan' group by kode) brg2
                        on
                            brg1.id = brg2.id
                ) brg
                on
                    dkp.item = brg.kode
            left join
                (
                    select
                        rs.noreg,
                        rs.nim,
                        m.nama,
                        k.alamat_jalan,
                        k.alamat_rt,
                        k.alamat_rw,
                        k.alamat_kelurahan,
                        l_kec.nama as alamat_kecamatan,
                        l_kab_kota.nama as alamat_kab_kota,
                        l_prov.nama as alamat_prov
                    from rdim_submit rs
                    left join
                        (
                            select mm1.* from mitra_mapping mm1
                            right join
                                (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                on
                                    mm1.id = mm2.id
                        ) mm
                        on
                            rs.nim = mm.nim
                    left join
                        mitra m 
                        on
                            mm.mitra = m.id
                    left join
                        kandang k 
                        on
                            k.mitra_mapping = mm.id and
                            k.kandang = cast(SUBSTRING(rs.noreg, 10, 2) as int)
                    left join
                        lokasi l_kec 
                        on
                            l_kec.id = k.alamat_kecamatan
                    left join
                        lokasi l_kab_kota
                        on
                            l_kab_kota.id = l_kec.induk
                    left join
                        lokasi l_prov
                        on
                            l_prov.id = l_kab_kota.induk
                ) mtr
                on
                    kp.tujuan = mtr.noreg
            where
                kp.id = ".$id."
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $no_sj = null;
        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
            $no_sj = $data[0]['no_sj'];
        }

        $content['data'] = $data;
        $res_view_html = $this->load->view('transaksi/pengiriman_penerimaan_pakan/v_cetak_nota_kiriman', $content, true);

        $this->pdfgenerator->generate($res_view_html, "pengiriman_penerimaan_pakan".$no_sj, 'a5', 'portrait');
    }

    public function tes($no_spm='')
    {
        // $m_gdg = new \Model\Storage\Gudang_model();
        // $d_gdg = $m_gdg->where('id', 8)->with(['dUnit'])->first();

        // $kode_unit = null;
        // if ( $d_gdg ) {
        //     $d_gdg = $d_gdg->toArray();
        //     $kode_unit = $d_gdg['d_unit']['kode'];
        // }

        // cetak_r( $kode_unit );

        $id = 52028;
        $id_old = 52028;
        $tanggal = '2026-06-15';
        $status = 2;
        $status_jurnal = 2;
        $delete = 0;
        $noreg1 = '25110670401';
        $noreg2 = '25100150201';

        $sql = "EXEC hitung_stok_pakan_by_transaksi 'terima_pakan', '".$id."', '".$tanggal."', ".$delete.", ".$status_jurnal."";
        $return = Modules::run( 'base/ExecStoredProcedure/exec', $sql);

        $sql = "EXEC hitung_stok_siklus 'pakan', 'terima_pakan', '".$id."', '".$tanggal."', ".$status.", '".$noreg1."', '".$noreg2."'";
        $return = Modules::run( 'base/ExecStoredProcedure/exec', $sql);

        $return = Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, $status);
    }
}