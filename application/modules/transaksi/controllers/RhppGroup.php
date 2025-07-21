<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RhppGroup extends Public_Controller {

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
                "assets/transaksi/rhpp_group/js/rhpp-group.js",
            ));
            $this->add_external_css(array(
                "assets/transaksi/rhpp_group/css/rhpp-group.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;

            $peternak = $this->getPeternak();

            $content['mitra'] = $peternak;
            $content['add_form'] = $this->addForm($peternak);

            // Load Indexx
            $data['title_menu'] = 'RHPP Group';
            $data['view'] = $this->load->view('transaksi/rhpp_group/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getLists()
    {
        $params = $this->input->post('params');

        try {
            $m_rhpp_group_header = new \Model\Storage\RhppGroupHeader_model();
            $d_rhpp_group_header = $m_rhpp_group_header->where('nomor', $params['nomor'])->whereBetween('tgl_submit', [$params['start_date'], $params['end_date']])->with(['rhpp'])->get();

            $data = null;
            if ( $d_rhpp_group_header->count() > 0 ) {
                $d_rhpp_group_header = $d_rhpp_group_header->toArray();

                foreach ($d_rhpp_group_header as $k_rgh => $v_rgh) {
                    $list_noreg = null;
                    foreach ($v_rgh['rhpp'] as $k_rhpp => $v_rhpp) {
                        $list_noreg = null;
                        foreach ($v_rhpp['list_noreg'] as $k_ln => $v_ln) {
                            $list_noreg[] = array(
                                'noreg' => $v_ln['noreg']
                            );
                        }
                    }

                    $data[] = array(
                        'id' => $v_rgh['id'],
                        'nomor' => $v_rgh['nomor'],
                        'mitra' => $v_rgh['mitra'],
                        'list_noreg' => $list_noreg,
                        'tgl_submit' => $v_rgh['tgl_submit']
                    );
                }
            }

            $content['data'] = $data;
            $html = $this->load->view('transaksi/rhpp_group/list', $content, TRUE);

            $this->result['status'] = 1;
            $this->result['content'] = $html;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function addForm($peternak)
    {
        $content['mitra'] = $peternak;
        $html = $this->load->view('transaksi/rhpp_group/add_form', $content, TRUE);

        return $html;
    }

    public function getPeternak()
    {
        $data = array();

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
        //             $data[ $key ] = array(
        //                 'nomor' => $d_mitra->nomor,
        //                 'nama' => $d_mitra->nama,
        //                 'unit' => $d_kdg->d_unit->kode
        //             );
        //         }
        //     }

        //     ksort($data);
        // }

        $m_mitra = new \Model\Storage\Mitra_model();
        $sql = "
            select 
                m.nama, 
                m2.nomor,
                m2.kode
            from 
                mitra m 
            right join
                (
                    select 
                        max(m.id) as id,
                        m.nomor,
                        w.kode 
                    from kandang k 
                    right join
                        mitra_mapping mm 
                        on
                            k.mitra_mapping = mm.id
                    right join
                        mitra m 
                        on
                            mm.mitra = m.id
                    right join
                        wilayah w 
                        on
                            k.unit = w.id 
                    where
                        m.nomor is not null and
                        w.kode is not null
                    group by
                        m.nomor,
                        w.kode
                ) m2
                on
                    m.id = m2.id
            group by
                m.nama, 
                m2.nomor,
                m2.kode
        ";
        $d_mitra = $m_mitra->hydrateRaw( $sql );

        if ( $d_mitra->count() > 0 ) {
            $d_mitra = $d_mitra->toArray();
            foreach ($d_mitra as $k_mitra => $v_mitra) {
                $key = $v_mitra['kode'].' | '.$v_mitra['nama'].' | '.$v_mitra['nomor'];

                $data[ $key ] = array(
                    'nomor' => $v_mitra['nomor'],
                    'nama' => $v_mitra['nama'],
                    'unit' => $v_mitra['kode']
                );

                // $d_mitra = $m_mitra->select('nama', 'nomor')->where('nomor', $v_mitra['nomor'])->orderBy('id', 'desc')->first();

                // $m_mm = new \Model\Storage\MitraMapping_model();
                // $d_mm = $m_mm->where('nomor', $d_mitra->nomor)->orderBy('id', 'desc')->first();

                // if ( $d_mm ) {
                //     $m_kdg = new \Model\Storage\Kandang_model();
                //     $d_kdg = $m_kdg->where('mitra_mapping', $d_mm->id)->with(['d_unit'])->first();

                //     $key = $d_kdg->d_unit->kode.' | '.$d_mitra->nama.' | '.$d_mitra->nomor;
                //     if ( empty($kode_unit_all) ) {
                //         foreach ($kode_unit as $k_ku => $v_ku) {
                //             if ( $v_ku == $d_kdg->d_unit->kode ) {
                //                 $data[ $key ] = array(
                //                     'nomor' => $d_mm->dMitra->nomor,
                //                     'nama' => $d_mm->dMitra->nama,
                //                     'unit' => $d_kdg->d_unit->kode
                //                 );
                //             }
                //         }
                //     } else {
                //         $data[ $key ] = array(
                //             'nomor' => $d_mitra->nomor,
                //             'nama' => $d_mitra->nama,
                //             'unit' => $d_kdg->d_unit->kode
                //         );
                //     }
                // }
            }

            ksort($data);
        }

        return $data;
    }

    public function get_noreg()
    {
        $params = $this->input->get('params');

        $start_date = $params['start_date'].' 00:00:00.000';
        $end_date = $params['end_date'].' 23:59:59.999';

        $m_mitra = new \Model\Storage\Mitra_model();
        $d_mitra = $m_mitra->select('id')->where('nomor', $params['nomor'])->get();

        $data = array();
        if ( $d_mitra->count() > 0 ) {
            $d_mitra = $d_mitra->toArray();

            $m_mm = new \Model\Storage\MitraMapping_model();
            $d_mm = $m_mm->select('nim')->whereIn('mitra', $d_mitra)->get();

            if ( $d_mm->count() > 0 ) {
                $d_mm = $d_mm->toArray();

                $m_rs = new \Model\Storage\RdimSubmit_model();
                $d_rs = $m_rs->select('noreg')->whereIn('nim', $d_mm)->get();

                if ( $d_rs->count() > 0 ) {
                    $d_rs = $d_rs->toArray();

                    $m_ts = new \Model\Storage\TutupSiklus_model();
                    $d_ts = $m_ts->select('noreg', 'tgl_docin')->whereIn('noreg', $d_rs)->get();

                    if ( $d_ts->count() > 0 ) {
                        $d_ts = $d_ts->toArray();

                        foreach ($d_ts as $k_ts => $v_ts) {
                            $m_od = new \Model\Storage\OrderDoc_model();
                            $d_od = $m_od->where('noreg', $v_ts['noreg'])->first();

                            $m_td = new \Model\Storage\TerimaDoc_model();
                            $d_td = $m_td->where('no_order', $d_od->no_order)->whereBetween('datang', [$start_date, $end_date])->first();

                            if ( !empty($d_td) ) {
                                $m_rgn = new \Model\Storage\RhppGroupNoreg_model();
                                $d_rgn = $m_rgn->where('noreg', $v_ts['noreg'])->first();

                                if ( !$d_rgn ) {
                                    $data[ $v_ts['noreg'] ] = array(
                                        'noreg' => $v_ts['noreg'],
                                        'tgl_docin' => $v_ts['tgl_docin'],
                                        'kandang' => substr($v_ts['noreg'], -2),
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        $content['data'] = $data;
        $html = $this->load->view('transaksi/rhpp_group/list_noreg', $content, TRUE);

        echo $html;
    }

    public function proses_hitung()
    {
        $params = $this->input->get('params');

        $data_rhpp_plasma = null;
        $data_rhpp_int = null;
        $data = null;

        $data_header = array();

        $id_tutup_siklus = null; $mitra = array(); $noreg = array(); $populasi = null; $populasi_ch = null; $kandang = null; $tgl_docin = null; $tutup_siklus = null; $biaya_materai = null; $potongan_pajak = null; $tgl_tutup = null; $rata_umur_panen = null; $biaya_opr = null; $tipe_kandang = null;

        $data_doc_plasma = null; $data_pakan_plasma = null; $data_pindah_pakan_plasma = null; $data_retur_pakan_plasma = null; $data_voadip_plasma = null; $data_retur_voadip_plasma = null; $data_rpah_plasma = null;
        $data_doc_inti = null; $data_pakan_inti = null; $data_pindah_pakan_inti = null; $data_oa_pindah_pakan_inti = null; $data_oa_pakan_inti = null; $data_retur_pakan_inti = null; $data_oa_retur_pakan_inti = null; $data_voadip_inti = null; $data_retur_voadip_inti = null; $data_data_rpah_inti = null;

        $bonus_pasar = 0; $fcr = 0; $bb = 0; $deplesi = 0; $ip = 0;

        $data_potongan = null;
        $data_bonus = null;
        $_noreg = null;

        $jenis_mitra = null;
        $nomor_mitra = null;
        $cn = '';

        if ( empty($params['id']) ) {
            $total_jumlah_pakan = 0;
            $total_tonase = 0;
            $total_ekor = 0;
            $tgl_sk = null;
            $sapronak_kesepakatan = null;

            $total_tonase_sj = 0;
            $total_ekor_sj = 0;

            $selisih_pakan = null;

            $kontrak_bonus_listrik = null;

            foreach ($params['list_noreg'] as $k => $v) {
                $sk = $this->get_harga_kontrak( $v['noreg'] );
                if ( empty($tgl_sk) ) {
                    $tgl_sk = $sk['mulai'];
                    $sapronak_kesepakatan = $sk;
                } else {
                    if ( $sk['mulai'] < $tgl_sk ) {
                        $tgl_sk = $sk['mulai'];
                        $sapronak_kesepakatan = $sk;
                    }
                }

                $kontrak_bonus_listrik = $sk['bonus_insentif_listrik'];

                $selisih_pakan = $sk['selisih_pakan'];

                $m_ts = new \Model\Storage\TutupSiklus_model();
                $d_ts = $m_ts->where('noreg', $v['noreg'])->with(['potongan_pajak'])->first();

                $m_rs = new \Model\Storage\RdimSubmit_model();
                $d_rs = $m_rs->where('noreg', $v['noreg'])->with(['mitra', 'dKandang'])->first();

                $jenis_mitra = $d_rs->mitra->dMitra->jenis;
                $nomor_mitra = $d_rs->mitra->dMitra->nomor;

                $m_rhpp = new \Model\Storage\Rhpp_model();
                $d_rhpp_inti = $m_rhpp->where('noreg', $v['noreg'])->where('jenis', 'rhpp_inti')->with(['doc', 'pakan', 'oa_pakan', 'pindah_pakan', 'oa_pindah_pakan', 'retur_pakan', 'oa_retur_pakan', 'voadip', 'retur_voadip', 'penjualan'])->orderBy('id', 'desc')->first();
                $d_rhpp_plasma = $m_rhpp->where('noreg', $v['noreg'])->where('jenis', 'rhpp_plasma')->with(['doc', 'pakan', 'oa_pakan', 'pindah_pakan', 'oa_pindah_pakan', 'retur_pakan', 'oa_retur_pakan', 'voadip', 'retur_voadip', 'penjualan', 'potongan', 'bonus', 'piutang'])->orderBy('id', 'desc')->first();

                $d_rhpp_inti = $d_rhpp_inti->toArray();
                $d_rhpp_plasma = !empty($d_rhpp_plasma) ? $d_rhpp_plasma->toArray() : null;

                $m_kdg = new \Model\Storage\Conf();
                $sql = "
                    select k.* from kandang k
                    left join
                        (
                            select mm1.* from mitra_mapping mm1
                            right join
                                (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                on
                                    mm1.id = mm2.id
                        ) mm
                        on
                            mm.id = k.mitra_mapping
                    where
                        mm.id is not null and
                        mm.nim = SUBSTRING('".$v['noreg']."', 1, 7) and
                        k.kandang = CAST(SUBSTRING('".$v['noreg']."', 10, 2) as int)
                ";
                $d_kdg = $m_kdg->hydrateRaw( $sql );

                if ( $d_kdg->count() > 0 ) {
                    $d_kdg = $d_kdg->toArray()[0];
                    if ( $d_kdg['tipe'] == 'CH' ) {
                        $populasi_ch += $d_rhpp_inti['populasi'];
                    }
                }

                $noreg = $d_rhpp_inti['noreg'];
                $populasi += $d_rhpp_inti['populasi'];
                if ( empty($data_header) ) {
                    $data_header['nomor'] = $d_rs->mitra->dMitra->nomor;
                    $data_header['mitra'] = $d_rhpp_inti['mitra'];
                    $data_header['tot_populasi'] = $d_rhpp_inti['populasi'];
                    $data_header['biaya_opr'] = $d_rhpp_inti['biaya_operasional'];
                    $data_header['bonus_insentif_fcr'] = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['bonus_insentif_fcr'] : 0;
                    // $data_header['total_bonus_insentif_listrik'] = $d_rhpp_plasma['total_bonus_insentif_listrik'];
                } else {
                    $data_header['tot_populasi'] += $d_rhpp_inti['populasi'];
                    $data_header['biaya_opr'] += $d_rhpp_inti['biaya_operasional'];
                    $data_header['bonus_insentif_fcr'] += !empty($d_rhpp_plasma) ? $d_rhpp_plasma['bonus_insentif_fcr'] : 0;
                    // $data_header['total_bonus_insentif_listrik'] += $d_rhpp_plasma['total_bonus_insentif_listrik'];
                }
                $data_header['biaya_materai'] = 0;
                $data_header['potongan_pajak'] = 0;

                if ( $d_rs->dKandang->tipe == 'CH' && $ip >= 380 ) {
                    $data_header['total_bonus_insentif_listrik'] = 0;
                }

                $data_header['detail'][ $noreg ] = array(
                    'noreg' => $noreg,
                    'populasi' => $d_rhpp_inti['populasi'],
                    'kandang' => $d_rhpp_inti['kandang'],
                    'tgl_docin' => $d_rhpp_inti['tgl_docin'],
                    'tgl_tutup' => $d_ts->tgl_tutup
                );

                if ( !empty($d_rhpp_plasma) ) {
                    $key_doc = $d_rhpp_plasma['doc']['tanggal'].' | '.$d_rhpp_plasma['doc']['nota'];

                    $data_doc_plasma['doc'][ $key_doc ] = array(
                        'tgl_docin' => $d_rhpp_plasma['doc']['tanggal'],
                        'sj' => $d_rhpp_plasma['doc']['nota'],
                        'barang' => $d_rhpp_plasma['doc']['barang'],
                        'box' => $d_rhpp_plasma['doc']['box'],
                        'jumlah' => $d_rhpp_plasma['doc']['jumlah'],
                        'harga' => $d_rhpp_plasma['doc']['harga'],
                        'total' => $d_rhpp_plasma['doc']['total']
                    );
                    ksort($data_doc_plasma['doc']);

                    $data_doc_plasma['vaksin'][ $key_doc ] = array(
                        'barang' => $d_rhpp_plasma['doc']['vaksin'],
                        'harga' => $d_rhpp_plasma['doc']['harga_vaksin'],
                        'total' => $d_rhpp_plasma['doc']['total_vaksin']
                    );
                    ksort($data_doc_plasma['vaksin']);

                    foreach ($d_rhpp_plasma['pakan'] as $k_pakan => $v_pakan) {
                        // $key_pakan = $v_pakan['tanggal'].' | '.$v_pakan['nota'].' | '.$v_pakan['barang'].' | '.$v_pakan['jumlah'];
                        $data_pakan_plasma[] = array(
                            'tanggal' => $v_pakan['tanggal'],
                            'sj' => $v_pakan['nota'],
                            'barang' => $v_pakan['barang'],
                            'zak' => $v_pakan['zak'],
                            'jumlah' => $v_pakan['jumlah'],
                            'harga' => $v_pakan['harga'],
                            'total' => $v_pakan['total']
                        );
                    }
                    ksort($data_pakan_plasma);

                    if ( !empty($d_rhpp_plasma['pindah_pakan']) ) {
                        foreach ($d_rhpp_plasma['pindah_pakan'] as $k_pindah_pakan => $v_pindah_pakan) {
                            // $key_pakan = $v_pindah_pakan['tanggal'].' | '.$v_pindah_pakan['nota'].' | '.$v_pindah_pakan['barang'].' | '.$v_pindah_pakan['jumlah'];
                            $data_pindah_pakan_plasma[] = array(
                                'tanggal' => $v_pindah_pakan['tanggal'],
                                'sj' => $v_pindah_pakan['nota'],
                                'barang' => $v_pindah_pakan['barang'],
                                'zak' => $v_pindah_pakan['zak'],
                                'jumlah' => $v_pindah_pakan['jumlah'],
                                'harga' => $v_pindah_pakan['harga'],
                                'total' => $v_pindah_pakan['total']
                            );
                        }

                        ksort($data_pindah_pakan_plasma);
                    }

                    if ( !empty($d_rhpp_plasma['retur_pakan']) ) {
                        foreach ($d_rhpp_plasma['retur_pakan'] as $k_retur_pakan => $v_retur_pakan) {
                            // $key_pakan = $v_retur_pakan['tanggal'].' | '.$v_retur_pakan['nota'].' | '.$v_retur_pakan['barang'].' | '.$v_retur_pakan['jumlah'];
                            $data_retur_pakan_plasma[] = array(
                                'tanggal' => $v_retur_pakan['tanggal'],
                                'sj' => $v_retur_pakan['nota'],
                                'barang' => $v_retur_pakan['barang'],
                                'zak' => $v_retur_pakan['zak'],
                                'jumlah' => $v_retur_pakan['jumlah'],
                                'harga' => $v_retur_pakan['harga'],
                                'total' => $v_retur_pakan['total']
                            );
                        }

                        ksort($data_retur_pakan_plasma);
                    }

                    foreach ($d_rhpp_plasma['voadip'] as $k_voadip => $v_voadip) {
                        $m_brg = new \Model\Storage\Barang_model();
                        $d_brg = $m_brg->where('nama', 'like', '%'.$v_voadip['barang'].'%')->orderBy('id', 'desc')->first();

                        $key_voadip = $v_voadip['tanggal'].' | '.$v_voadip['nota'].' | '.$v_voadip['barang'].' | '.$v_voadip['harga'];
                        $data_voadip_plasma[ $key_voadip ] = array(
                            'tanggal' => $v_voadip['tanggal'],
                            'sj' => $v_voadip['nota'],
                            'barang' => $v_voadip['barang'],
                            'jumlah' => $v_voadip['jumlah'],
                            'harga' => $v_voadip['harga'],
                            'total' => $v_voadip['total'],
                            'decimal' => !empty($d_brg) ? $d_brg->desimal_harga : 2
                        );
                    }
                    ksort($data_voadip_plasma);

                    if ( !empty($d_rhpp_plasma['retur_voadip']) ) {
                        foreach ($d_rhpp_plasma['retur_voadip'] as $k_rvoadip => $v_rvoadip) {
                            $m_brg = new \Model\Storage\Barang_model();
                            $d_brg = $m_brg->where('nama', 'like', '%'.$v_rvoadip['barang'].'%')->orderBy('id', 'desc')->first();

                            $key_voadip = $v_rvoadip['tanggal'].' | '.$v_rvoadip['nota'].' | '.$v_rvoadip['barang'].' | '.$v_rvoadip['harga'];
                            $data_retur_voadip_plasma[ $key_voadip ] = array(
                                'tanggal' => $v_rvoadip['tanggal'],
                                'no_retur' => $v_rvoadip['nota'],
                                'barang' => $v_rvoadip['barang'],
                                'jumlah' => $v_rvoadip['jumlah'],
                                'harga' => $v_rvoadip['harga'],
                                'total' => $v_rvoadip['total'],
                                'decimal' => !empty($d_brg) ? $d_brg->desimal_harga : 2
                            );
                        }

                        ksort( $data_retur_voadip_plasma );
                    }

                    foreach ($d_rhpp_plasma['penjualan'] as $k_penjualan => $v_penjualan) {
                        $key = $v_penjualan['tanggal'].' | '.$v_penjualan['pembeli'].' | '.$v_penjualan['nota'].' | '.$v_penjualan['bb'].' | '.$v_penjualan['id'];
                        $data_rpah_plasma[ $key ] = array(
                            'tanggal' => $v_penjualan['tanggal'],
                            'pembeli' => $v_penjualan['pembeli'],
                            'do' => $v_penjualan['nota'],
                            'ekor' => $v_penjualan['ekor'],
                            'tonase' => $v_penjualan['tonase'],
                            'bb' => $v_penjualan['bb'],
                            'hrg_kontrak' => $v_penjualan['harga_kontrak'],
                            'total_kontrak' => $v_penjualan['total_kontrak'],
                            'hrg_pasar' => $v_penjualan['harga_pasar'],
                            'total_pasar' => $v_penjualan['total_pasar'],
                            'selisih' => $v_penjualan['selisih'],
                            'insentif' => $v_penjualan['insentif'],
                            'total_insentif' => $v_penjualan['total_insentif']
                        );
                    }
                    ksort( $data_rpah_plasma );
                    
                    foreach ($d_rhpp_plasma['potongan'] as $k_potongan => $v_potongan) {
                        // $key = $v_penjualan['tanggal'].' | '.$v_penjualan['nota'].' | '.$v_penjualan['bb'];
                        $m_bpp = new \Model\Storage\BayarPenjualanPeralatan_model();
                        $d_bpp = $m_bpp->where('id_penjualan_peralatan', $v_potongan['id_trans'])->get();

                        $m_pp = new \Model\Storage\PenjualanPeralatan_model();
                        $d_pp = $m_pp->where('id', $v_potongan['id_trans'])->first();

                        $sudah_bayar = 0;
                        if ( $d_bpp->count() > 0 ) {
                            foreach ($d_bpp as $k_bpp => $v_bpp) {
                                $sudah_bayar += $v_bpp['saldo'] + $v_bpp['bayar'];
                            }
                        }

                        $data_potongan[ $v_potongan['id'] ] = array(
                            'id_jual' => $v_potongan['id_trans'],
                            'tanggal' => (isset($d_pp)) ? $d_pp->tanggal : null,
                            'keterangan' => $v_potongan['keterangan'],
                            'tagihan' => $v_potongan['jumlah_tagihan'],
                            'sudah_bayar' => $v_potongan['jumlah_bayar'],
                            'sisa_bayar' => ( $v_potongan['jumlah_bayar'] < $v_potongan['jumlah_tagihan'] ) ? $v_potongan['jumlah_tagihan'] - $v_potongan['jumlah_bayar'] : 0
                        );
                    }
                                    
                    foreach ($d_rhpp_plasma['bonus'] as $k_bonus => $v_bonus) {
                        $data_bonus[ $v_bonus['id'] ] = array(
                            'id_trans' => $v_bonus['id_trans'],
                            'keterangan' => $v_bonus['keterangan'],
                            'jumlah' => $v_bonus['jumlah'],
                        );
                    }
                }
                
                $key_doc = $d_rhpp_inti['doc']['tanggal'].' | '.$d_rhpp_inti['doc']['nota'];
                $data_doc_inti['doc'][ $key_doc ] = array(
                    'tgl_docin' => $d_rhpp_inti['doc']['tanggal'],
                    'sj' => $d_rhpp_inti['doc']['nota'],
                    'barang' => $d_rhpp_inti['doc']['barang'],
                    'box' => $d_rhpp_inti['doc']['box'],
                    'jumlah' => $d_rhpp_inti['doc']['jumlah'],
                    'harga' => $d_rhpp_inti['doc']['harga'],
                    'total' => $d_rhpp_inti['doc']['total']
                );
                ksort($data_doc_inti['doc']);

                foreach ($d_rhpp_inti['pakan'] as $k_pakan => $v_pakan) {
                    // $key_pakan = $v_pakan['tanggal'].' | '.$v_pakan['nota'].' | '.$v_pakan['barang'].' | '.$v_pakan['jumlah'];
                    $data_pakan_inti[] = array(
                        'tanggal' => $v_pakan['tanggal'],
                        'sj' => $v_pakan['nota'],
                        'barang' => $v_pakan['barang'],
                        'zak' => $v_pakan['zak'],
                        'jumlah' => $v_pakan['jumlah'],
                        'harga' => $v_pakan['harga'],
                        'total' => $v_pakan['total']
                    );

                    $total_jumlah_pakan += $v_pakan['jumlah'];
                }
                ksort($data_pakan_inti);

                foreach ($d_rhpp_inti['oa_pakan'] as $k_oa_pakan => $v_oa_pakan) {
                    // $key = $v_oa_pakan['tanggal'].' | '.$v_oa_pakan['nota'].' | '.$v_oa_pakan['nopol'].' | '.$v_oa_pakan['barang'].' | '.$v_oa_pakan['jumlah'];
                    $data_oa_pakan_inti[] = $v_oa_pakan;
                }
                ksort($data_oa_pakan_inti);

                if ( !empty($d_rhpp_inti['pindah_pakan']) ) {
                    foreach ($d_rhpp_inti['pindah_pakan'] as $k_pindah_pakan => $v_pindah_pakan) {
                        // $key_pakan = $v_pindah_pakan['tanggal'].' | '.$v_pindah_pakan['nota'].' | '.$v_pindah_pakan['barang'].' | '.$v_pindah_pakan['jumlah'];
                        $data_pindah_pakan_inti[] = array(
                            'tanggal' => $v_pindah_pakan['tanggal'],
                            'sj' => $v_pindah_pakan['nota'],
                            'barang' => $v_pindah_pakan['barang'],
                            'zak' => $v_pindah_pakan['zak'],
                            'jumlah' => $v_pindah_pakan['jumlah'],
                            'harga' => $v_pindah_pakan['harga'],
                            'total' => $v_pindah_pakan['total']
                        );

                        $total_jumlah_pakan -= $v_pindah_pakan['jumlah'];
                    }

                    ksort($data_pindah_pakan_inti);

                    foreach ($d_rhpp_inti['oa_pindah_pakan'] as $k_oa_pindah_pakan => $v_oa_pindah_pakan) {
                        // $key = $v_oa_pindah_pakan['tanggal'].' | '.$v_oa_pindah_pakan['nota'].' | '.$v_oa_pindah_pakan['nopol'].' | '.$v_oa_pindah_pakan['barang'].' | '.$v_oa_pindah_pakan['jumlah'];
                        $data_oa_pindah_pakan_inti[] = $v_oa_pindah_pakan;
                    }
                    if ( !empty($data_oa_pindah_pakan_inti) ) {
                        ksort($data_oa_pindah_pakan_inti);
                    }
                }

                if ( !empty($d_rhpp_inti['retur_pakan']) ) {
                    foreach ($d_rhpp_inti['retur_pakan'] as $k_retur_pakan => $v_retur_pakan) {
                        // $key_pakan = $v_retur_pakan['tanggal'].' | '.$v_retur_pakan['nota'].' | '.$v_retur_pakan['barang'].' | '.$v_retur_pakan['jumlah'];
                        $data_retur_pakan_inti[] = array(
                            'tanggal' => $v_retur_pakan['tanggal'],
                            'sj' => $v_retur_pakan['nota'],
                            'barang' => $v_retur_pakan['barang'],
                            'zak' => $v_retur_pakan['zak'],
                            'jumlah' => $v_retur_pakan['jumlah'],
                            'harga' => $v_retur_pakan['harga'],
                            'total' => $v_retur_pakan['total']
                        );

                        $total_jumlah_pakan -= $v_retur_pakan['jumlah'];
                    }

                    ksort($data_retur_pakan_inti);

                    foreach ($d_rhpp_inti['oa_retur_pakan'] as $k_oa_retur_pakan => $v_oa_retur_pakan) {
                        // $key = $v_oa_retur_pakan['tanggal'].' | '.$v_oa_retur_pakan['nota'].' | '.$v_oa_retur_pakan['nopol'].' | '.$v_oa_retur_pakan['barang'].' | '.$v_oa_retur_pakan['jumlah'];
                        $data_oa_retur_pakan_inti[] = $v_oa_retur_pakan;
                    }
                    ksort($data_oa_retur_pakan_inti);
                }

                foreach ($d_rhpp_inti['voadip'] as $k_voadip => $v_voadip) {
                    $m_brg = new \Model\Storage\Barang_model();
                    $d_brg = $m_brg->where('nama', 'like', '%'.$v_voadip['barang'].'%')->orderBy('id', 'desc')->first();

                    $key_voadip = $v_voadip['tanggal'].' | '.$v_voadip['nota'].' | '.$v_voadip['barang'].' | '.$v_voadip['harga'];
                    $data_voadip_inti[ $key_voadip ] = array(
                        'tanggal' => $v_voadip['tanggal'],
                        'sj' => $v_voadip['nota'],
                        'barang' => $v_voadip['barang'],
                        'jumlah' => $v_voadip['jumlah'],
                        'harga' => $v_voadip['harga'],
                        'total' => $v_voadip['total'],
                        'decimal' => !empty($d_brg) ? $d_brg->desimal_harga : 2
                    );
                }
                ksort($data_voadip_inti);

                if ( !empty($d_rhpp_inti['retur_voadip']) ) {
                    foreach ($d_rhpp_inti['retur_voadip'] as $k_rvoadip => $v_rvoadip) {
                        $m_brg = new \Model\Storage\Barang_model();
                        $d_brg = $m_brg->where('nama', 'like', '%'.$v_rvoadip['barang'].'%')->orderBy('id', 'desc')->first();

                        $key_voadip = $v_rvoadip['tanggal'].' | '.$v_rvoadip['nota'].' | '.$v_rvoadip['barang'].' | '.$v_rvoadip['harga'];
                        $data_retur_voadip_inti[ $key_voadip ] = array(
                            'tanggal' => $v_rvoadip['tanggal'],
                            'no_retur' => $v_rvoadip['nota'],
                            'barang' => $v_rvoadip['barang'],
                            'jumlah' => $v_rvoadip['jumlah'],
                            'harga' => $v_rvoadip['harga'],
                            'total' => $v_rvoadip['total'],
                            'decimal' => !empty($d_brg) ? $d_brg->desimal_harga : 2
                        );
                    }

                    ksort( $data_retur_voadip_inti );
                }
                
                foreach ($d_rhpp_inti['penjualan'] as $k_penjualan => $v_penjualan) {
                    $key = $v_penjualan['tanggal'].' | '.$v_penjualan['pembeli'].' | '.$v_penjualan['nota'].' | '.$v_penjualan['bb'].' | '.$v_penjualan['id'];
                    $data_rpah_inti[ $key ] = array(
                        'tanggal' => $v_penjualan['tanggal'],
                        'pembeli' => $v_penjualan['pembeli'],
                        'do' => $v_penjualan['nota'],
                        'ekor' => $v_penjualan['ekor'],
                        'tonase' => $v_penjualan['tonase'],
                        'bb' => $v_penjualan['bb'],
                        'hrg_kontrak' => $v_penjualan['harga_kontrak'],
                        'total_kontrak' => $v_penjualan['total_kontrak'],
                        'hrg_pasar' => $v_penjualan['harga_pasar'],
                        'total_pasar' => $v_penjualan['total_pasar'],
                        'selisih' => $v_penjualan['selisih'],
                        'insentif' => $v_penjualan['insentif'],
                        'total_insentif' => $v_penjualan['total_insentif']
                    );

                    $umur_panen = abs(selisihTanggal($v_penjualan['tanggal'], $d_rhpp_inti['tgl_docin']));

                    $total_ekor_sj += $v_penjualan['ekor'];
                    $total_tonase_sj += $umur_panen * $v_penjualan['ekor'];

                    $total_tonase += $v_penjualan['tonase'];
                    $total_ekor += $v_penjualan['ekor'];
                }
                ksort( $data_rpah_inti );
            }

            $data_piutang_plasma = $this->get_data_piutang( $nomor_mitra );

            $rata_umur_panen = ($total_tonase_sj > 0 && $total_ekor_sj > 0) ? $total_tonase_sj / $total_ekor_sj : 0;
            $data_header['rata_umur_panen'] = $rata_umur_panen;

            if ( $total_jumlah_pakan > 0 && $total_tonase > 0 ) {
                $fcr = $total_jumlah_pakan / $total_tonase;
            }
            if ( $total_ekor > 0 && $total_tonase > 0 ) {
                $bb = $total_tonase / $total_ekor;
            }
            if ( $populasi > 0 && $total_ekor > 0 ) {
                $deplesi = abs((($populasi - $total_ekor) / $populasi) * 100);
            }
            if ( $deplesi > 0 && $bb > 0 && $fcr > 0 && $rata_umur_panen > 0 ) {
                $ip = round((((100 - $deplesi) * $bb) / ($fcr * $rata_umur_panen) * 100), 2);
            }

            foreach ($sapronak_kesepakatan['hitung_budidaya_item'] as $k => $val) {
                if ( $val['ip_akhir'] > 0 ) {
                    if ( ($ip >= $val['ip_awal']) && ($ip <= $val['ip_akhir']) ) {
                        $bonus_pasar = $val['bonus_ip'];
                    }
                } else {
                    if ( $ip >= $val['ip_awal'] ) {
                        $bonus_pasar = $val['bonus_ip'];
                    }
                }
            }

            if ( isset($data_rpah_plasma) && !empty($data_rpah_plasma) ) {
                foreach ($data_rpah_plasma as $k_data => $v_data) {
                    $selisih = $data_rpah_plasma[$k_data]['selisih'];
                    if ( $selisih > 0 ) {
                        $insentif = $selisih * $bonus_pasar/100;
                        $data_rpah_plasma[$k_data]['insentif'] = $insentif;
                        $data_rpah_plasma[$k_data]['total_insentif'] = $insentif * $data_rpah_plasma[$k_data]['tonase'];
                    }
                }
            }

            foreach ($data_rpah_inti as $k_data => $v_data) {
                $selisih = $data_rpah_inti[$k_data]['selisih'];
                if ( $selisih > 0 ) {
                    $insentif = $selisih * $bonus_pasar/100;
                    $data_rpah_inti[$k_data]['insentif'] = $insentif;
                    $data_rpah_inti[$k_data]['total_insentif'] = $insentif * $data_rpah_inti[$k_data]['tonase'];
                }
            }            

            if ( isset($data_rpah_plasma) && !empty($data_rpah_plasma) ) {
                $data_header['total_bonus_insentif_listrik'] = 0;
                if ( $populasi_ch > 0 && !empty($kontrak_bonus_listrik) ) {
                    foreach ($kontrak_bonus_listrik as $k => $val) {
                        if ( $ip >= $val['ip_awal'] ) {
                            $data_header['total_bonus_insentif_listrik'] = $populasi_ch * $val['bonus'];
                        }
                    }
                }
            }

            // if ( $d_rs->dKandang->tipe == 'CH' && $ip < 380 ) {
            //     $data_header['total_bonus_insentif_listrik'] = 0;
            // }

            // if ( $tipe_kandang == 'CH' && $ip >= 380 ) {
            //     $data_rpah_plasma[$k_data]['insentif'] = 0;
            //     $data_rpah_plasma[$k_data]['total_insentif'] = $insentif * $data_rpah_plasma[$k_data]['tonase'];

            $selisih_fcr = round(($bb - $fcr), 3);

            $bonus_fcr = 0;
            foreach ($selisih_pakan as $k_sp => $v_sp) {
                $range_awal = (float) $v_sp['range_awal'];
                $range_akhir = (float) $v_sp['range_akhir'];

                if ( $v_sp['range_akhir'] > 0 ) {
                    if ( $selisih_fcr >= $range_awal && $selisih_fcr <= $range_akhir ) {
                        $bonus_fcr = $v_sp['tarif'];
                    }
                } else {
                    if ( $selisih_fcr >= $range_awal ) {
                        $bonus_fcr = $v_sp['tarif'];
                    }
                }
            }

            // cetak_r( $bonus_fcr.' | '.$data_rpah_plasma[$k_data]['tonase'] );

            $bonus_insentif_fcr = $bonus_fcr * $total_tonase;
            $data_header['bonus_insentif_fcr'] = $bonus_insentif_fcr;

            $data_header['fcr'] = $fcr;
            $data_header['bb'] = $bb;
            $data_header['deplesi'] = $deplesi;
            $data_header['ip'] = $ip;
            $data_header['bonus_pasar'] = $bonus_pasar;
        } else {
            $m_rhpp_group_header = new \Model\Storage\RhppGroupHeader_model();
            $d_rhpp_group_header = $m_rhpp_group_header->where('id', $params['id'])->with(['rhpp'])->first()->toArray();

            $d_rhpp_inti = null;
            $d_rhpp_plasma = null;
            foreach ($d_rhpp_group_header['rhpp'] as $k_rgh=> $v_rgh) {
                if ( $v_rgh['jenis'] == 'rhpp_inti' ) {
                    $d_rhpp_inti = $v_rgh;
                } else {
                    $d_rhpp_plasma = $v_rgh;
                }
            }

            $data_header['nomor'] = $d_rhpp_group_header['nomor'];
            $data_header['mitra'] = $d_rhpp_group_header['mitra'];
            $data_header['biaya_materai'] = $d_rhpp_inti['biaya_materai'];
            $data_header['biaya_opr'] = $d_rhpp_inti['biaya_operasional'];
            $data_header['bonus_insentif_fcr'] = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['bonus_insentif_fcr'] : 0;
            $data_header['total_bonus_insentif_listrik'] = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['total_bonus_insentif_listrik'] : 0;
            $data_header['potongan_pajak'] = $d_rhpp_inti['prs_potongan_pajak'];
            $data_header['fcr'] = $d_rhpp_inti['fcr'];
            $data_header['bb'] = $d_rhpp_inti['bb'];
            $data_header['deplesi'] = $d_rhpp_inti['deplesi'];
            $data_header['ip'] = $d_rhpp_inti['ip'];
            $data_header['bonus_pasar'] = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['persen_bonus_pasar'] : 0;
            $cn = $d_rhpp_inti['cn'];

            $tot_populasi = 0;
            foreach ($d_rhpp_inti['list_noreg'] as $k_ln => $v_ln) {
                $data_header['detail'][ $v_ln['noreg'] ] = array(
                    'noreg' => $v_ln['noreg'],
                    'populasi' => $v_ln['populasi'],
                    'kandang' => $v_ln['kandang'],
                    'tgl_docin' => $v_ln['tgl_docin'],
                    'tgl_tutup' => $v_ln['tgl_tutup_siklus']
                );

                $tot_populasi += $v_ln['populasi'];

                $m_rs = new \Model\Storage\RdimSubmit_model();
                $d_rs = $m_rs->where('noreg', $v_ln['noreg'])->with(['mitra', 'dKandang'])->first();

                $jenis_mitra = $d_rs->mitra->dMitra->jenis;
            }

            $data_header['tot_populasi'] = $tot_populasi;

            if ( !empty($d_rhpp_plasma) ) {
                foreach ($d_rhpp_plasma['doc'] as $k_doc => $v_doc) {
                    $key_doc = $v_doc['tanggal'].' | '.$v_doc['nota'];
                    $data_doc_plasma['doc'][ $key_doc ] = array(
                        'tgl_docin' => $v_doc['tanggal'],
                        'sj' => $v_doc['nota'],
                        'barang' => $v_doc['barang'],
                        'box' => $v_doc['box'],
                        'jumlah' => $v_doc['jumlah'],
                        'harga' => $v_doc['harga'],
                        'total' => $v_doc['total']
                    );

                    $data_doc_plasma['vaksin'][ $key_doc ] = array(
                        'barang' => $v_doc['vaksin'],
                        'harga' => $v_doc['harga_vaksin'],
                        'total' => $v_doc['total_vaksin']
                    );
                }
                ksort($data_doc_plasma['doc']);
                ksort($data_doc_plasma['vaksin']);

                foreach ($d_rhpp_plasma['pakan'] as $k_pakan => $v_pakan) {
                    // $key_pakan = $v_pakan['tanggal'].' | '.$v_pakan['nota'].' | '.$v_pakan['barang'].' | '.$v_pakan['jumlah'];
                    $data_pakan_plasma[] = array(
                        'tanggal' => $v_pakan['tanggal'],
                        'sj' => $v_pakan['nota'],
                        'barang' => $v_pakan['barang'],
                        'zak' => $v_pakan['zak'],
                        'jumlah' => $v_pakan['jumlah'],
                        'harga' => $v_pakan['harga'],
                        'total' => $v_pakan['total']
                    );
                }
                ksort($data_pakan_plasma);

                if ( !empty($d_rhpp_plasma['pindah_pakan']) ) {
                    foreach ($d_rhpp_plasma['pindah_pakan'] as $k_pindah_pakan => $v_pindah_pakan) {
                        // $key_pakan = $v_pindah_pakan['tanggal'].' | '.$v_pindah_pakan['nota'].' | '.$v_pindah_pakan['barang'].' | '.$v_pindah_pakan['jumlah'];
                        $data_pindah_pakan_plasma[] = array(
                            'tanggal' => $v_pindah_pakan['tanggal'],
                            'sj' => $v_pindah_pakan['nota'],
                            'barang' => $v_pindah_pakan['barang'],
                            'zak' => $v_pindah_pakan['zak'],
                            'jumlah' => $v_pindah_pakan['jumlah'],
                            'harga' => $v_pindah_pakan['harga'],
                            'total' => $v_pindah_pakan['total']
                        );
                    }

                    ksort($data_pindah_pakan_plasma);
                }

                if ( !empty($d_rhpp_plasma['retur_pakan']) ) {
                    foreach ($d_rhpp_plasma['retur_pakan'] as $k_retur_pakan => $v_retur_pakan) {
                        // $key_pakan = $v_retur_pakan['tanggal'].' | '.$v_retur_pakan['nota'].' | '.$v_retur_pakan['barang'].' | '.$v_retur_pakan['jumlah'];
                        $data_retur_pakan_plasma[] = array(
                            'tanggal' => $v_retur_pakan['tanggal'],
                            'sj' => $v_retur_pakan['nota'],
                            'barang' => $v_retur_pakan['barang'],
                            'zak' => $v_retur_pakan['zak'],
                            'jumlah' => $v_retur_pakan['jumlah'],
                            'harga' => $v_retur_pakan['harga'],
                            'total' => $v_retur_pakan['total']
                        );
                    }

                    ksort($data_retur_pakan_plasma);
                }

                foreach ($d_rhpp_plasma['voadip'] as $k_voadip => $v_voadip) {
                    $m_brg = new \Model\Storage\Barang_model();
                    $d_brg = $m_brg->where('nama', 'like', '%'.$v_voadip['barang'].'%')->orderBy('id', 'desc')->first();
                    // $key_voadip = $v_voadip['tanggal'].' | '.$v_voadip['nota'].' | '.$v_voadip['barang'];
                    $data_voadip_plasma[] = array(
                        'tanggal' => $v_voadip['tanggal'],
                        'sj' => $v_voadip['nota'],
                        'barang' => $v_voadip['barang'],
                        'jumlah' => $v_voadip['jumlah'],
                        'harga' => $v_voadip['harga'],
                        'total' => $v_voadip['total'],
                        'decimal' => !empty($d_brg) ? $d_brg->desimal_harga : 2
                    );
                }
                ksort($data_voadip_plasma);

                if ( !empty($d_rhpp_plasma['retur_voadip']) ) {
                    foreach ($d_rhpp_plasma['retur_voadip'] as $k_rvoadip => $v_rvoadip) {
                        $m_brg = new \Model\Storage\Barang_model();
                        $d_brg = $m_brg->where('nama', 'like', '%'.$v_rvoadip['barang'].'%')->orderBy('id', 'desc')->first();
                        // $key_voadip = $v_rvoadip['tanggal'].' | '.$v_rvoadip['nota'].' | '.$v_rvoadip['barang'];
                        $data_retur_voadip_plasma[] = array(
                            'tanggal' => $v_rvoadip['tanggal'],
                            'no_retur' => $v_rvoadip['nota'],
                            'barang' => $v_rvoadip['barang'],
                            'jumlah' => $v_rvoadip['jumlah'],
                            'harga' => $v_rvoadip['harga'],
                            'total' => $v_rvoadip['total'],
                            'decimal' => !empty($d_brg) ? $d_brg->desimal_harga : 2
                        );
                    }

                    ksort( $data_retur_voadip_plasma );
                }

                $total_tonase_sj = 0;
                $total_ekor_sj = 0;
                foreach ($d_rhpp_plasma['penjualan'] as $k_penjualan => $v_penjualan) {
                    $key = $v_penjualan['tanggal'].' | '.$v_penjualan['pembeli'].' | '.$v_penjualan['nota'].' | '.$v_penjualan['bb'].' | '.$v_penjualan['id'];
                    $data_rpah_plasma[ $key ] = array(
                        'tanggal' => $v_penjualan['tanggal'],
                        'pembeli' => $v_penjualan['pembeli'],
                        'do' => $v_penjualan['nota'],
                        'ekor' => $v_penjualan['ekor'],
                        'tonase' => $v_penjualan['tonase'],
                        'bb' => $v_penjualan['bb'],
                        'hrg_kontrak' => $v_penjualan['harga_kontrak'],
                        'total_kontrak' => $v_penjualan['total_kontrak'],
                        'hrg_pasar' => $v_penjualan['harga_pasar'],
                        'total_pasar' => $v_penjualan['total_pasar'],
                        'selisih' => $v_penjualan['selisih'],
                        'insentif' => $v_penjualan['insentif'],
                        'total_insentif' => $v_penjualan['total_insentif']
                    );
                }

                if ( !empty($data_rpah_plasma) ) {
                    ksort( $data_rpah_plasma );
                }

                foreach ($d_rhpp_plasma['potongan'] as $k_potongan => $v_potongan) {
                    // $key = $v_penjualan['tanggal'].' | '.$v_penjualan['nota'].' | '.$v_penjualan['bb'];
                    $m_bpp = new \Model\Storage\BayarPenjualanPeralatan_model();
                    $d_bpp = $m_bpp->where('id_penjualan_peralatan', $v_potongan['id_trans'])->get();

                    $m_pp = new \Model\Storage\PenjualanPeralatan_model();
                    $d_pp = $m_pp->where('id', $v_potongan['id_trans'])->first();

                    $sudah_bayar = 0;
                    if ( $d_bpp->count() > 0 ) {
                        foreach ($d_bpp as $k_bpp => $v_bpp) {
                            $sudah_bayar += $v_bpp['saldo'] + $v_bpp['bayar'];
                        }
                    }

                    $data_potongan[ $v_potongan['id'] ] = array(
                        'id_jual' => $v_potongan['id_trans'],
                        'tanggal' => (isset($d_pp)) ? $d_pp->tanggal : null,
                        'keterangan' => $v_potongan['keterangan'],
                        'tagihan' => $v_potongan['jumlah_tagihan'],
                        'sudah_bayar' => $v_potongan['jumlah_bayar'],
                        'sisa_bayar' => ( $v_potongan['jumlah_bayar'] < $v_potongan['jumlah_tagihan'] ) ? $v_potongan['jumlah_tagihan'] - $v_potongan['jumlah_bayar'] : 0
                    );
                }
                                
                foreach ($d_rhpp_plasma['bonus'] as $k_bonus => $v_bonus) {
                    $data_bonus[ $v_bonus['id'] ] = array(
                        'id_trans' => $v_bonus['id_trans'],
                        'keterangan' => $v_bonus['keterangan'],
                        'jumlah' => $v_bonus['jumlah'],
                    );
                }

                $data_piutang_plasma = null;
                foreach ($d_rhpp_plasma['piutang'] as $k_piutang => $v_piutang) {
                    $data_piutang_plasma[ $v_piutang['id'] ] = array(
                        'id' => $v_piutang['id'],
                        'kode' => $v_piutang['piutang_kode'],
                        'nama_perusahaan' => $v_piutang['nama_perusahaan'],
                        'tanggal' => $v_piutang['piutang']['tanggal'],
                        'keterangan' => $v_piutang['piutang']['keterangan'],
                        'sisa_piutang' => $v_piutang['sisa_piutang'],
                        'nominal' => $v_piutang['nominal']
                    );
                }
            }

            $rata_umur_panen = $d_rhpp_inti['rata_umur'];
            $data_header['rata_umur_panen'] = $rata_umur_panen;

            foreach ($d_rhpp_inti['doc'] as $k_doc => $v_doc) {
                $key_doc = $v_doc['tanggal'].' | '.$v_doc['nota'];
                $data_doc_inti['doc'][ $key_doc ] = array(
                    'tgl_docin' => $v_doc['tanggal'],
                    'sj' => $v_doc['nota'],
                    'barang' => $v_doc['barang'],
                    'box' => $v_doc['box'],
                    'jumlah' => $v_doc['jumlah'],
                    'harga' => $v_doc['harga'],
                    'total' => $v_doc['total']
                );
            }
            ksort($data_doc_inti['doc']);

            foreach ($d_rhpp_inti['pakan'] as $k_pakan => $v_pakan) {
                // $key_pakan = $v_pakan['tanggal'].' | '.$v_pakan['nota'].' | '.$v_pakan['barang'].' | '.$v_pakan['jumlah'];
                $data_pakan_inti[] = array(
                    'tanggal' => $v_pakan['tanggal'],
                    'sj' => $v_pakan['nota'],
                    'barang' => $v_pakan['barang'],
                    'zak' => $v_pakan['zak'],
                    'jumlah' => $v_pakan['jumlah'],
                    'harga' => $v_pakan['harga'],
                    'total' => $v_pakan['total']
                );
            }
            ksort($data_pakan_inti);

            foreach ($d_rhpp_inti['oa_pakan'] as $k_oa_pakan => $v_oa_pakan) {
                // $key = $v_oa_pakan['tanggal'].' | '.$v_oa_pakan['nota'].' | '.$v_oa_pakan['nopol'].' | '.$v_oa_pakan['barang'].' | '.$v_oa_pakan['jumlah'];
                $data_oa_pakan_inti[] = $v_oa_pakan;
            }
            ksort($data_oa_pakan_inti);

            if ( !empty($d_rhpp_inti['pindah_pakan']) ) {
                foreach ($d_rhpp_inti['pindah_pakan'] as $k_pindah_pakan => $v_pindah_pakan) {
                    // $key_pakan = $v_pindah_pakan['tanggal'].' | '.$v_pindah_pakan['nota'].' | '.$v_pindah_pakan['barang'].' | '.$v_pindah_pakan['jumlah'];
                    $data_pindah_pakan_inti[] = array(
                        'tanggal' => $v_pindah_pakan['tanggal'],
                        'sj' => $v_pindah_pakan['nota'],
                        'barang' => $v_pindah_pakan['barang'],
                        'zak' => $v_pindah_pakan['zak'],
                        'jumlah' => $v_pindah_pakan['jumlah'],
                        'harga' => $v_pindah_pakan['harga'],
                        'total' => $v_pindah_pakan['total']
                    );
                }

                ksort($data_pindah_pakan_inti);

                foreach ($d_rhpp_inti['oa_pindah_pakan'] as $k_oa_pindah_pakan => $v_oa_pindah_pakan) {
                    // $key = $v_oa_pindah_pakan['tanggal'].' | '.$v_oa_pindah_pakan['nota'].' | '.$v_oa_pindah_pakan['nopol'].' | '.$v_oa_pindah_pakan['barang'].' | '.$v_oa_pindah_pakan['jumlah'];
                    $data_oa_pindah_pakan_inti[] = $v_oa_pindah_pakan;
                }
                if ( !empty($data_oa_pindah_pakan_inti) ) {
                    ksort($data_oa_pindah_pakan_inti);
                }
            }

            if ( !empty($d_rhpp_inti['retur_pakan']) ) {
                foreach ($d_rhpp_inti['retur_pakan'] as $k_retur_pakan => $v_retur_pakan) {
                    // $key_pakan = $v_retur_pakan['tanggal'].' | '.$v_retur_pakan['nota'].' | '.$v_retur_pakan['barang'].' | '.$v_retur_pakan['jumlah'];
                    $data_retur_pakan_inti[] = array(
                        'tanggal' => $v_retur_pakan['tanggal'],
                        'sj' => $v_retur_pakan['nota'],
                        'barang' => $v_retur_pakan['barang'],
                        'zak' => $v_retur_pakan['zak'],
                        'jumlah' => $v_retur_pakan['jumlah'],
                        'harga' => $v_retur_pakan['harga'],
                        'total' => $v_retur_pakan['total']
                    );
                }

                ksort($data_retur_pakan_inti);

                foreach ($d_rhpp_inti['oa_retur_pakan'] as $k_oa_retur_pakan => $v_oa_retur_pakan) {
                    // $key = $v_oa_retur_pakan['tanggal'].' | '.$v_oa_retur_pakan['nota'].' | '.$v_oa_retur_pakan['nopol'].' | '.$v_oa_retur_pakan['barang'].' | '.$v_oa_retur_pakan['jumlah'];
                    $data_oa_retur_pakan_inti[] = $v_oa_retur_pakan;
                }
                ksort($data_oa_retur_pakan_inti);
            }

            foreach ($d_rhpp_inti['voadip'] as $k_voadip => $v_voadip) {
                $m_brg = new \Model\Storage\Barang_model();
                $d_brg = $m_brg->where('nama', 'like', '%'.$v_voadip['barang'].'%')->orderBy('id', 'desc')->first();

                $key_voadip = $v_voadip['tanggal'].' | '.$v_voadip['nota'].' | '.$v_voadip['barang'].' | '.$v_voadip['harga'];
                $data_voadip_inti[ $key_voadip ] = array(
                    'tanggal' => $v_voadip['tanggal'],
                    'sj' => $v_voadip['nota'],
                    'barang' => $v_voadip['barang'],
                    'jumlah' => $v_voadip['jumlah'],
                    'harga' => $v_voadip['harga'],
                    'total' => $v_voadip['total'],
                    'decimal' => !empty($d_brg) ? $d_brg->desimal_harga : 2
                );
            }
            ksort($data_voadip_inti);

            if ( !empty($d_rhpp_inti['retur_voadip']) ) {
                foreach ($d_rhpp_inti['retur_voadip'] as $k_rvoadip => $v_rvoadip) {
                    $m_brg = new \Model\Storage\Barang_model();
                    $d_brg = $m_brg->where('nama', 'like', '%'.$v_rvoadip['barang'].'%')->orderBy('id', 'desc')->first();

                    $key_voadip = $v_rvoadip['tanggal'].' | '.$v_rvoadip['nota'].' | '.$v_rvoadip['barang'].' | '.$v_rvoadip['harga'];
                    $data_retur_voadip_inti[ $key_voadip ] = array(
                        'tanggal' => $v_rvoadip['tanggal'],
                        'no_retur' => $v_rvoadip['nota'],
                        'barang' => $v_rvoadip['barang'],
                        'jumlah' => $v_rvoadip['jumlah'],
                        'harga' => $v_rvoadip['harga'],
                        'total' => $v_rvoadip['total'],
                        'decimal' => !empty($d_brg) ? $d_brg->desimal_harga : 2
                    );
                }

                ksort( $data_retur_voadip_inti );
            }
            
            foreach ($d_rhpp_inti['penjualan'] as $k_penjualan => $v_penjualan) {
                $key = $v_penjualan['tanggal'].' | '.$v_penjualan['pembeli'].' | '.$v_penjualan['nota'].' | '.$v_penjualan['bb'].' | '.$v_penjualan['id'];
                $data_rpah_inti[ $key ] = array(
                    'tanggal' => $v_penjualan['tanggal'],
                    'pembeli' => $v_penjualan['pembeli'],
                    'do' => $v_penjualan['nota'],
                    'ekor' => $v_penjualan['ekor'],
                    'tonase' => $v_penjualan['tonase'],
                    'bb' => $v_penjualan['bb'],
                    'hrg_kontrak' => $v_penjualan['harga_kontrak'],
                    'total_kontrak' => $v_penjualan['total_kontrak'],
                    'hrg_pasar' => $v_penjualan['harga_pasar'],
                    'total_pasar' => $v_penjualan['total_pasar'],
                    'selisih' => $v_penjualan['selisih'],
                    'insentif' => $v_penjualan['insentif'],
                    'total_insentif' => $v_penjualan['total_insentif']
                );
            }

            if ( !empty($data_rpah_inti) ) {
                ksort( $data_rpah_inti );
            } else {
                $data_rpah_inti = null;
            }
        }

        $data_detail_plasma = array(
            'data_doc' => $data_doc_plasma,
            'data_pakan' => $data_pakan_plasma,
            'data_pindah_pakan' => $data_pindah_pakan_plasma,
            'data_retur_pakan' => $data_retur_pakan_plasma,
            'data_voadip' => $data_voadip_plasma,
            'data_retur_voadip' => $data_retur_voadip_plasma,
            'data_rpah' => $data_rpah_plasma,
            'data_potongan' => $data_potongan,
            'data_bonus' => $data_bonus,
            'data_piutang_plasma' => isset($data_piutang_plasma) ? $data_piutang_plasma : null
        );

        $data_detail_inti = array(
            'data_doc' => $data_doc_inti,
            'data_pakan' => $data_pakan_inti,
            'data_oa_pakan' => $data_oa_pakan_inti,
            'data_pindah_pakan' => $data_pindah_pakan_inti,
            'data_oa_pindah_pakan' => $data_oa_pindah_pakan_inti,
            'data_retur_pakan' => $data_retur_pakan_inti,
            'data_oa_retur_pakan' => $data_oa_retur_pakan_inti,
            'data_voadip' => $data_voadip_inti,
            'data_retur_voadip' => $data_retur_voadip_inti,
            'data_rpah' => $data_rpah_inti
        );

        $data_rhpp_plasma = array(
            'detail' => $data_detail_plasma
        );

        $data_rhpp_inti = array(
            'detail' => $data_detail_inti
        );

        $data_potongan_pajak = $this->get_data_potongan_pajak();
        $data_header['jenis_mitra'] = $jenis_mitra;
        $data_header['cn'] = $cn;
        $data_header['data_potongan_pajak'] = $data_potongan_pajak;
        $data_header['potongan_pajak'] = isset($params['id']) ? $data_header['potongan_pajak'] : $data_header['potongan_pajak'] / count($params['list_noreg']);

        $akses = hakAkses($this->url);

        $form_rhpp_inti = 0;
        if ( !empty($akses['a_khusus']) && in_array('rhpp_inti', $akses['a_khusus']) ) {
            $form_rhpp_inti = 1;
        }

        $content['form_rhpp_inti'] = $form_rhpp_inti;
        $content['id'] = isset($params['id']) ? $params['id'] : null;
        $content['data'] = $data_header;
        $content['data_plasma'] = $data_rhpp_plasma;
        $content['data_inti'] = $data_rhpp_inti;

        // cetak_r( $data_rhpp_inti, 1 );

        $m_conf = new \Model\Storage\Conf();
        $now = $m_conf->getDate();

        $content['tanggal'] = $now['tanggal'];

        $html = $this->load->view('transaksi/rhpp_group/view_rhpp', $content, TRUE);

        echo $html;
    }

    public function get_data_potongan_pajak()
    {
        $data = null;

        $m_pp = new \Model\Storage\PotonganPajak_model();
        $d_pp = $m_pp->get();

        if ( $d_pp->count() > 0 ) {
            $data = $d_pp->toArray();
        }

        return $data;
    }

    public function get_harga_kontrak($noreg)
    {
        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('noreg', $noreg)->with(['mitra'])->first()->toArray();

        $data = null;
        if ( count($d_rs) > 0 ) {
            $m_pm = new \Model\Storage\PerwakilanMaping_model();
            $d_pm = $m_pm->select('id_hbi')->where('id_pwk', $d_rs['mitra']['perwakilan'])->orderBy('id', 'desc')->get();

            if ( $d_pm->count() > 0 ) {
                $d_pm = $d_pm->toArray();

                $m_hbi = new \Model\Storage\HitungBudidayaItem_model();
                $d_hbi = $m_hbi->select('id_sk')->whereIn('id', $d_pm)->get();

                if ( $d_hbi->count() > 0 ) {
                    $d_hbi = $d_hbi->toArray();

                    $m_sk = new \Model\Storage\SapronakKesepakatan_model();
                    $d_sk = $m_sk->whereIn('id', $d_hbi)->where('mulai', '<=', substr($d_rs['tgl_docin'], 0, 10))->orderBy('id', 'desc')->with(['pola_kerjasama','harga_sapronak','harga_performa','harga_sepakat', 'bonus_insentif_listrik', 'selisih_pakan', 'hitung_budidaya_item'])->first();

                    if ( $d_sk ) {
                        $data = $d_sk->toArray();
                    }
                }

            }

            return $data;
        }
    }

    public function get_data_piutang( $nomor )
    {
        $data = null;

        $mitra = $nomor;
        // $perusahaan = $params['perusahaan'];
        // $piutang_kode = $params['piutang_kode'];

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                data.*,
                p.perusahaan as nama_perusahaan
            from 
            (
                select 
                    p.tanggal,
                    p.kode,
                    p.perusahaan,
                    p.keterangan,
                    (p.nominal - isnull(bp.nominal, 0)) as sisa_piutang
                from piutang p
                left join
                    (
                        select
                            sum(data.nominal) as nominal,
                            data.piutang_kode
                        from (
                            select sum(nominal) as nominal, piutang_kode from bayar_piutang group by piutang_kode
                            
                            union all

                            select sum(nominal) as nominal, piutang_kode from rhpp_piutang group by piutang_kode

                            union all

                            select sum(nominal) as nominal, piutang_kode from rhpp_group_piutang group by piutang_kode
                        ) data
                        group by
                            data.piutang_kode
                    ) bp
                    on
                        p.kode = bp.piutang_kode
                where
                    p.nominal > isnull(bp.nominal, 0) and
                    p.mitra = '".$mitra."'
            ) data
            left join
                (
                    select p1.* from perusahaan p1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) p2
                        on
                            p1.id = p2.id
                ) p
                on
                    p.kode = data.perusahaan
            order by
                data.tanggal asc,
                data.kode asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = array();
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function save()
    {
        $params = $this->input->post('params');

        // cetak_r( $params, 1 );

        try {
            // cetak_r( $params, 1 );

            $list_noreg = null;
            foreach ($params['data_rhpp'][0]['data_list_noreg'] as $k_ln => $v_ln) {
                $list_noreg[] = $v_ln['noreg'];
            }

            $invoice = null;
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select top 1
                    w.kode as kode_unit
                from rdim_submit rs
                left join
                    kandang k
                    on
                        rs.kandang = k.id
                left join
                    wilayah w
                    on
                        k.unit = w.id
                where
                    rs.noreg in ('".implode("', '", $list_noreg)."')
                group by
                    w.kode
            ";
            $d_unit = $m_conf->hydrateRaw( $sql );

            if ( $d_unit->count() ) {
                $kode_unit = $d_unit->toArray()[0]['kode_unit'];

                $m_rhpp = new \Model\Storage\RhppGroup_model();
                $invoice = $m_rhpp->getNoInvoice('INV/RHPP/G/'.$kode_unit);
            }

            $m_rhpp_header = new \Model\Storage\RhppGroupHeader_model();
            $m_rhpp_header->nomor = $params['nomor'];
            $m_rhpp_header->mitra = $params['mitra'];
            $m_rhpp_header->tgl_submit = $params['tgl_tutup'];
            $m_rhpp_header->save();

            $id_header = $m_rhpp_header->id;

            foreach ($params['data_rhpp'] as $k_rhpp => $v_rhpp) {
                $m_rhpp = new \Model\Storage\RhppGroup_model();
                $m_rhpp->id_header = $id_header;
                $m_rhpp->jenis = $v_rhpp['jenis'];
                $m_rhpp->jml_panen_ekor = $v_rhpp['jml_panen_ekor'];
                $m_rhpp->jml_panen_kg = $v_rhpp['jml_panen_kg'];
                $m_rhpp->bb = round($v_rhpp['bb'], 2);
                $m_rhpp->fcr = round($v_rhpp['fcr'], 2);
                $m_rhpp->deplesi = round($v_rhpp['deplesi'], 2);
                $m_rhpp->rata_umur = round($v_rhpp['rata_umur'], 2);
                $m_rhpp->ip = round($v_rhpp['ip'], 2);
                $m_rhpp->tot_penjualan_ayam = $v_rhpp['tot_penjualan_ayam'];
                $m_rhpp->tot_pembelian_sapronak = $v_rhpp['tot_pembelian_sapronak'];
                $m_rhpp->biaya_materai = $v_rhpp['biaya_materai'];
                $m_rhpp->bonus_pasar = $v_rhpp['bonus_pasar'];
                $m_rhpp->bonus_kematian = $v_rhpp['bonus_kematian'];
                $m_rhpp->bonus_insentif_fcr = $v_rhpp['bonus_insentif_fcr'];
                $m_rhpp->biaya_operasional = $v_rhpp['biaya_operasional'];
                $m_rhpp->pdpt_peternak_belum_pajak = $v_rhpp['pdpt_peternak_belum_pajak'];
                $m_rhpp->prs_potongan_pajak = $v_rhpp['prs_potongan_pajak'];
                $m_rhpp->potongan_pajak = $v_rhpp['potongan_pajak'];
                $m_rhpp->pdpt_peternak_sudah_pajak = $v_rhpp['pdpt_peternak_sudah_pajak'];
                $m_rhpp->lr_inti = $v_rhpp['lr_inti'];
                $m_rhpp->total_bonus_insentif_listrik = $v_rhpp['total_bonus_insentif_listrik'];
                $m_rhpp->persen_bonus_pasar = $v_rhpp['persen_bonus_pasar'];
                $m_rhpp->total_bonus = $v_rhpp['total_bonus'];
                $m_rhpp->total_potongan = $v_rhpp['total_potongan'];
                $m_rhpp->cn = !empty($v_rhpp['cn']) ? $v_rhpp['cn'] : null;
                $m_rhpp->invoice = $invoice;
                $m_rhpp->save();

                $id_rhpp = $m_rhpp->id;

                if ( !empty($v_rhpp['data_doc']) ) {
                    foreach ($v_rhpp['data_doc'] as $k_doc => $v_doc) {
                        $m_rhpp_doc = new \Model\Storage\RhppGroupDoc_model();
                        $m_rhpp_doc->id_header = $id_rhpp;
                        $m_rhpp_doc->tanggal = substr($v_doc['tanggal'], 0, 10);
                        $m_rhpp_doc->nota = $v_doc['nota'];
                        $m_rhpp_doc->barang = $v_doc['barang'];
                        $m_rhpp_doc->box = $v_doc['box_zak'];
                        $m_rhpp_doc->jumlah = $v_doc['jumlah'];
                        $m_rhpp_doc->harga = $v_doc['harga'];
                        $m_rhpp_doc->total = $v_doc['total'];
                        $m_rhpp_doc->vaksin = $v_doc['vaksin'];
                        $m_rhpp_doc->harga_vaksin = $v_doc['harga_vaksin'];
                        $m_rhpp_doc->total_vaksin = $v_doc['total_vaksin'];
                        $m_rhpp_doc->save();
                    }
                }

                $noreg = null;
                foreach ($v_rhpp['data_list_noreg'] as $k_ln => $v_ln) {
                    $m_rhpp_noreg = new \Model\Storage\RhppGroupNoreg_model();
                    $m_rhpp_noreg->id_header = $id_rhpp;
                    $m_rhpp_noreg->noreg = $v_ln['noreg'];
                    $m_rhpp_noreg->kandang = $v_ln['kandang'];
                    $m_rhpp_noreg->populasi = $v_ln['populasi'];
                    $m_rhpp_noreg->tgl_docin = $v_ln['tgl_docin'];
                    $m_rhpp_noreg->tgl_tutup_siklus = $v_ln['tgl_tutup'];
                    $m_rhpp_noreg->save();

                    $noreg = $v_ln['noreg'];
                }

                if ( !empty($v_rhpp['data_pakan']) ) {
                    foreach ($v_rhpp['data_pakan'] as $k_pakan => $v_pakan) {
                        $m_rhpp_pakan = new \Model\Storage\RhppGroupPakan_model();
                        $m_rhpp_pakan->id_header = $id_rhpp;
                        $m_rhpp_pakan->tanggal = substr($v_pakan['tanggal'], 0, 10);
                        $m_rhpp_pakan->nota = $v_pakan['nota'];
                        $m_rhpp_pakan->barang = $v_pakan['barang'];
                        $m_rhpp_pakan->zak = $v_pakan['box_zak'];
                        $m_rhpp_pakan->jumlah = $v_pakan['jumlah'];
                        $m_rhpp_pakan->harga = $v_pakan['harga'];
                        $m_rhpp_pakan->total = $v_pakan['total'];
                        $m_rhpp_pakan->save();
                    }
                }

                if ( !empty($v_rhpp['data_oa_pakan']) ) {
                    foreach ($v_rhpp['data_oa_pakan'] as $k_oa_pakan => $v_oa_pakan) {
                        $m_rhpp_oa_pakan = new \Model\Storage\RhppGroupOaPakan_model();
                        $m_rhpp_oa_pakan->id_header = $id_rhpp;
                        $m_rhpp_oa_pakan->tanggal = substr($v_oa_pakan['tanggal'], 0, 10);
                        $m_rhpp_oa_pakan->nota = $v_oa_pakan['nota'];
                        $m_rhpp_oa_pakan->nopol = $v_oa_pakan['nopol'];
                        $m_rhpp_oa_pakan->barang = $v_oa_pakan['barang'];
                        $m_rhpp_oa_pakan->zak = $v_oa_pakan['box_zak'];
                        $m_rhpp_oa_pakan->jumlah = $v_oa_pakan['jumlah'];
                        $m_rhpp_oa_pakan->harga = $v_oa_pakan['harga'];
                        $m_rhpp_oa_pakan->total = $v_oa_pakan['total'];
                        $m_rhpp_oa_pakan->save();
                    }
                }

                if ( !empty($v_rhpp['data_pindah_pakan']) ) {
                    foreach ($v_rhpp['data_pindah_pakan'] as $k_pindah_pakan => $v_pindah_pakan) {
                        $m_rhpp_pindah_pakan = new \Model\Storage\RhppGroupPindahPakan_model();
                        $m_rhpp_pindah_pakan->id_header = $id_rhpp;
                        $m_rhpp_pindah_pakan->tanggal = substr($v_pindah_pakan['tanggal'], 0, 10);
                        $m_rhpp_pindah_pakan->nota = $v_pindah_pakan['nota'];
                        $m_rhpp_pindah_pakan->barang = $v_pindah_pakan['barang'];
                        $m_rhpp_pindah_pakan->zak = $v_pindah_pakan['box_zak'];
                        $m_rhpp_pindah_pakan->jumlah = $v_pindah_pakan['jumlah'];
                        $m_rhpp_pindah_pakan->harga = $v_pindah_pakan['harga'];
                        $m_rhpp_pindah_pakan->total = $v_pindah_pakan['total'];
                        $m_rhpp_pindah_pakan->save();
                    }
                }

                if ( !empty($v_rhpp['data_oa_pindah_pakan']) ) {
                    foreach ($v_rhpp['data_oa_pindah_pakan'] as $k_oa_pindah_pakan => $v_oa_pindah_pakan) {
                        $m_rhpp_oa_pindah_pakan = new \Model\Storage\RhppGroupOaPindahPakan_model();
                        $m_rhpp_oa_pindah_pakan->id_header = $id_rhpp;
                        $m_rhpp_oa_pindah_pakan->tanggal = substr($v_oa_pindah_pakan['tanggal'], 0, 10);
                        $m_rhpp_oa_pindah_pakan->nota = $v_oa_pindah_pakan['nota'];
                        $m_rhpp_oa_pindah_pakan->nopol = $v_oa_pindah_pakan['nopol'];
                        $m_rhpp_oa_pindah_pakan->barang = $v_oa_pindah_pakan['barang'];
                        $m_rhpp_oa_pindah_pakan->zak = $v_oa_pindah_pakan['box_zak'];
                        $m_rhpp_oa_pindah_pakan->jumlah = $v_oa_pindah_pakan['jumlah'];
                        $m_rhpp_oa_pindah_pakan->harga = $v_oa_pindah_pakan['harga'];
                        $m_rhpp_oa_pindah_pakan->total = $v_oa_pindah_pakan['total'];
                        $m_rhpp_oa_pindah_pakan->save();
                    }
                }

                if ( !empty($v_rhpp['data_retur_pakan']) ) {
                    foreach ($v_rhpp['data_retur_pakan'] as $k_retur_pakan => $v_retur_pakan) {
                        $m_rhpp_retur_pakan = new \Model\Storage\RhppGroupReturPakan_model();
                        $m_rhpp_retur_pakan->id_header = $id_rhpp;
                        $m_rhpp_retur_pakan->tanggal = substr($v_retur_pakan['tanggal'], 0, 10);
                        $m_rhpp_retur_pakan->nota = $v_retur_pakan['nota'];
                        $m_rhpp_retur_pakan->barang = $v_retur_pakan['barang'];
                        $m_rhpp_retur_pakan->zak = $v_retur_pakan['box_zak'];
                        $m_rhpp_retur_pakan->jumlah = $v_retur_pakan['jumlah'];
                        $m_rhpp_retur_pakan->harga = $v_retur_pakan['harga'];
                        $m_rhpp_retur_pakan->total = $v_retur_pakan['total'];
                        $m_rhpp_retur_pakan->save();
                    }
                }

                if ( !empty($v_rhpp['data_oa_retur_pakan']) ) {
                    foreach ($v_rhpp['data_oa_retur_pakan'] as $k_oa_retur_pakan => $v_oa_retur_pakan) {
                        $m_rhpp_oa_retur_pakan = new \Model\Storage\RhppGroupOaReturPakan_model();
                        $m_rhpp_oa_retur_pakan->id_header = $id_rhpp;
                        $m_rhpp_oa_retur_pakan->tanggal = substr($v_oa_retur_pakan['tanggal'], 0, 10);
                        $m_rhpp_oa_retur_pakan->nota = $v_oa_retur_pakan['nota'];
                        $m_rhpp_oa_retur_pakan->nopol = $v_oa_retur_pakan['nopol'];
                        $m_rhpp_oa_retur_pakan->barang = $v_oa_retur_pakan['barang'];
                        $m_rhpp_oa_retur_pakan->zak = $v_oa_retur_pakan['box_zak'];
                        $m_rhpp_oa_retur_pakan->jumlah = $v_oa_retur_pakan['jumlah'];
                        $m_rhpp_oa_retur_pakan->harga = $v_oa_retur_pakan['harga'];
                        $m_rhpp_oa_retur_pakan->total = $v_oa_retur_pakan['total'];
                        $m_rhpp_oa_retur_pakan->save();
                    }
                }

                if ( !empty($v_rhpp['data_voadip']) ) {
                    foreach ($v_rhpp['data_voadip'] as $k_voadip => $v_voadip) {
                        $m_rhpp_voadip = new \Model\Storage\RhppGroupVoadip_model();
                        $m_rhpp_voadip->id_header = $id_rhpp;
                        $m_rhpp_voadip->tanggal = substr($v_voadip['tanggal'], 0, 10);
                        $m_rhpp_voadip->nota = $v_voadip['nota'];
                        $m_rhpp_voadip->barang = $v_voadip['barang'];
                        $m_rhpp_voadip->jumlah = $v_voadip['jumlah'];
                        $m_rhpp_voadip->harga = $v_voadip['harga'];
                        $m_rhpp_voadip->total = $v_voadip['total'];
                        $m_rhpp_voadip->save();
                    }
                }

                if ( !empty($v_rhpp['data_retur_voadip']) ) {
                    foreach ($v_rhpp['data_retur_voadip'] as $k_retur_voadip => $v_retur_voadip) {
                        $m_rhpp_retur_voadip = new \Model\Storage\RhppGroupReturVoadip_model();
                        $m_rhpp_retur_voadip->id_header = $id_rhpp;
                        $m_rhpp_retur_voadip->tanggal = substr($v_retur_voadip['tanggal'], 0, 10);
                        $m_rhpp_retur_voadip->nota = $v_retur_voadip['nota'];
                        $m_rhpp_retur_voadip->barang = $v_retur_voadip['barang'];
                        $m_rhpp_retur_voadip->jumlah = $v_retur_voadip['jumlah'];
                        $m_rhpp_retur_voadip->harga = $v_retur_voadip['harga'];
                        $m_rhpp_retur_voadip->total = $v_retur_voadip['total'];
                        $m_rhpp_retur_voadip->save();
                    }
                }

                if ( !empty($v_rhpp['data_penjualan']) ) {
                    foreach ($v_rhpp['data_penjualan'] as $k_penjualan => $v_penjualan) {
                        $m_rhpp_penjualan = new \Model\Storage\RhppGroupPenjualan_model();
                        $m_rhpp_penjualan->id_header = $id_rhpp;
                        $m_rhpp_penjualan->tanggal = substr($v_penjualan['tanggal'], 0, 10);
                        $m_rhpp_penjualan->nota = $v_penjualan['nota'];
                        $m_rhpp_penjualan->pembeli = $v_penjualan['pembeli'];
                        $m_rhpp_penjualan->ekor = $v_penjualan['ekor'];
                        $m_rhpp_penjualan->tonase = $v_penjualan['tonase'];
                        $m_rhpp_penjualan->bb = $v_penjualan['bb'];
                        $m_rhpp_penjualan->harga_kontrak = $v_penjualan['harga_kontrak'];
                        $m_rhpp_penjualan->total_kontrak = $v_penjualan['total_kontrak'];
                        $m_rhpp_penjualan->harga_pasar = $v_penjualan['harga_pasar'];
                        $m_rhpp_penjualan->total_pasar = $v_penjualan['total_pasar'];
                        $m_rhpp_penjualan->selisih = $v_penjualan['selisih'];
                        $m_rhpp_penjualan->insentif = $v_penjualan['insentif'];
                        $m_rhpp_penjualan->total_insentif = $v_penjualan['total_insentif'];
                        $m_rhpp_penjualan->save();
                    }
                }

                if ( !empty($v_rhpp['data_potongan']) ) {
                    foreach ($v_rhpp['data_potongan'] as $k_potongan => $v_potongan) {
                        $m_rhpp_potongan = new \Model\Storage\RhppGroupPotongan_model();
                        $m_rhpp_potongan->id_header = $id_rhpp;
                        $m_rhpp_potongan->id_trans = isset($v_potongan['id_jual']) ? $v_potongan['id_jual'] : null;
                        $m_rhpp_potongan->keterangan = $v_potongan['keterangan'];
                        $m_rhpp_potongan->jumlah_tagihan = $v_potongan['jumlah_tagihan'];
                        $m_rhpp_potongan->jumlah_bayar = $v_potongan['jumlah_bayar'];
                        $m_rhpp_potongan->save();
                    }
                }

                if ( !empty($v_rhpp['data_bonus']) ) {
                    foreach ($v_rhpp['data_bonus'] as $k_bonus => $v_bonus) {
                        $m_rhpp_bonus = new \Model\Storage\RhppGroupBonus_model();
                        $m_rhpp_bonus->id_header = $id_rhpp;
                        $m_rhpp_bonus->id_trans = null;
                        $m_rhpp_bonus->keterangan = $v_bonus['keterangan'];
                        $m_rhpp_bonus->jumlah = $v_bonus['jumlah_bonus'];
                        $m_rhpp_bonus->save();
                    }
                }

                if ( !empty($v_rhpp['data_piutang']) ) {
                    foreach ($v_rhpp['data_piutang'] as $k_piutang => $v_piutang) {
                        $m_rhpp_piutang = new \Model\Storage\RhppGroupPiutang_model();
                        $m_rhpp_piutang->id_header = $id_rhpp;
                        $m_rhpp_piutang->piutang_kode = $v_piutang['piutang_kode'];
                        $m_rhpp_piutang->nama_perusahaan = $v_piutang['nama_perusahaan'];
                        $m_rhpp_piutang->sisa_piutang = $v_piutang['sisa_piutang'];
                        $m_rhpp_piutang->nominal = $v_piutang['nominal'];
                        $m_rhpp_piutang->save();
                    }
                }

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $m_rhpp, $deskripsi_log);
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal 'RHPP', NULL, NULL, 0, 'rhpp_group_header', ".$id_header.", NULL, 1";

            $d_conf = $m_conf->hydrateRaw( $sql );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil disimpan';
            $this->result['content'] = array('id' => $id_header);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete()
    {
        $id = $this->input->post('params');
        try {
            $m_rgh = new \Model\Storage\RhppGroupHeader_model();
            $d_rgh = $m_rgh->where('id', $id)->first();

            $m_rg = new \Model\Storage\RhppGroup_model();
            $d_rg_plasma = $m_rg->where('id_header', $id)->where('jenis', 'rhpp_plasma')->first();

            $ket_delete = null;
            $delete = 1;
            
            if ( $d_rg_plasma ) {
                $m_kppd = new \Model\Storage\Conf();
                $sql = "
                    select
                        kpp.nomor,
                        kpp.tgl_bayar,
                        kppd.*
                    from konfirmasi_pembayaran_peternak_det kppd
                    left join
                        konfirmasi_pembayaran_peternak kpp
                        on
                            kppd.id_header = kpp.id
                    where
                        kppd.jenis = 'RHPP GROUP' and
                        kppd.id_trans = ".$d_rg_plasma->id."
                ";
                $d_kppd = $m_kppd->hydrateRaw( $sql );
                if ( $d_kppd->count() > 0 ) {
                    $d_kppd = $d_kppd->toArray()[0];
    
                    $ket_delete = 'Tidak bisa di hapus, karena data rhpp sudah di ajukan pembayaran dengan nomor pengajuan <b>'.$d_kppd['nomor'].'</b> dengan tanggal bayar <b>'.strtoupper(tglIndonesia($d_kppd['tgl_bayar'], '-', ' ')).'</b>.';
    
                    $delete = 0;
    
                    $m_rpd = new \Model\Storage\Conf();
                    $sql = "
                        select
                            rp.nomor,
                            rp.tgl_bayar,
                            rpd.*
                        from realisasi_pembayaran_det rpd
                        left join
                            realisasi_pembayaran rp
                            on
                                rpd.id_header = rp.id
                        where
                            rpd.no_bayar = '".$d_kppd['nomor']."'
                    ";
                    $d_rpd = $m_rpd->hydrateRaw( $sql );
                    if ( $d_rpd->count() > 0 ) {
                        $d_rpd = $d_rpd->toArray()[0];
    
                        $ket_delete = 'Tidak bisa di hapus, karena rhpp sudah di transfer dengan nomor pembayaran <b>'.$d_rpd['nomor'].'</b> dengan tanggal bayar <b>'.strtoupper(tglIndonesia($d_rpd['tgl_bayar'], '-', ' ')).'</b>.';
    
                        $delete = 0;
                    }
                }
            }

            if ( $delete == 1 ) {
                $m_rg = new \Model\Storage\RhppGroup_model();
                $id_rg = $m_rg->select('id')->where('id_header', $id)->get()->toArray();
    
                $m_rgd = new \Model\Storage\RhppGroupDoc_model();
                $m_rgn = new \Model\Storage\RhppGroupNoreg_model();
                $m_rgp = new \Model\Storage\RhppGroupPakan_model();
                $m_rgop = new \Model\Storage\RhppGroupOaPakan_model();
                $m_rgpp = new \Model\Storage\RhppGroupPindahPakan_model();
                $m_rgopp = new \Model\Storage\RhppGroupOaPindahPakan_model();
                $m_rgrp = new \Model\Storage\RhppGroupReturPakan_model();
                $m_rgorp = new \Model\Storage\RhppGroupOaReturPakan_model();
                $m_rgv = new \Model\Storage\RhppGroupVoadip_model();
                $m_rgrv = new \Model\Storage\RhppGroupReturVoadip_model();
                $m_rgpenjualan = new \Model\Storage\RhppGroupPenjualan_model();
                $m_rgpiutang = new \Model\Storage\RhppGroupPiutang_model();
    
                $m_rgpiutang->whereIn('id_header', $id_rg)->delete();
                $m_rgpenjualan->whereIn('id_header', $id_rg)->delete();
                $m_rgrv->whereIn('id_header', $id_rg)->delete();
                $m_rgv->whereIn('id_header', $id_rg)->delete();
                $m_rgorp->whereIn('id_header', $id_rg)->delete();
                $m_rgrp->whereIn('id_header', $id_rg)->delete();
                $m_rgopp->whereIn('id_header', $id_rg)->delete();
                $m_rgpp->whereIn('id_header', $id_rg)->delete();
                $m_rgop->whereIn('id_header', $id_rg)->delete();
                $m_rgp->whereIn('id_header', $id_rg)->delete();
                $m_rgn->whereIn('id_header', $id_rg)->delete();
                $m_rgd->whereIn('id_header', $id_rg)->delete();
                $m_rg->whereIn('id', $id_rg)->delete();
                $m_rgh->where('id', $id)->delete();
    
                $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/delete', $d_rgh, $deskripsi_log);
    
                $m_conf = new \Model\Storage\Conf();
                $sql = "exec insert_jurnal 'RHPP', NULL, NULL, 0, 'rhpp_group_header', NULL, ".$id.", 3";
    
                $d_conf = $m_conf->hydrateRaw( $sql );
                
                $this->result['status'] = 1;
                $this->result['message'] = 'Data berhasil dihapus';
            } else {
                $this->result['message'] = $ket_delete;
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function export_excel_plasma($_id)
    {
        $id = exDecrypt( $_id );

        // cetak_r( $id, 1 );

        $m_rhpp_group_header = new \Model\Storage\RhppGroupHeader_model();
        $d_rhpp_group_header = $m_rhpp_group_header->where('id', $id)->with(['rhpp'])->first()->toArray();

        $d_rhpp_plasma = null;
        foreach ($d_rhpp_group_header['rhpp'] as $k_rgh=> $v_rgh) {
            if ( $v_rgh['jenis'] == 'rhpp_plasma' ) {
                $d_rhpp_plasma = $v_rgh;
            }
        }

        $data_header['nomor'] = $d_rhpp_group_header['nomor'];
        $data_header['mitra'] = $d_rhpp_group_header['mitra'];
        $data_header['biaya_materai'] = $d_rhpp_plasma['biaya_materai'];
        $data_header['biaya_opr'] = 0;
        $data_header['bonus_insentif_fcr'] = $d_rhpp_plasma['bonus_insentif_fcr'];
        $data_header['total_bonus_insentif_listrik'] = $d_rhpp_plasma['total_bonus_insentif_listrik'];
        $data_header['potongan_pajak'] = $d_rhpp_plasma['prs_potongan_pajak'];
        $data_header['fcr'] = $d_rhpp_plasma['fcr'];
        $data_header['bb'] = $d_rhpp_plasma['bb'];
        $data_header['deplesi'] = $d_rhpp_plasma['deplesi'];
        $data_header['ip'] = $d_rhpp_plasma['ip'];
        $data_header['bonus_pasar'] = $d_rhpp_plasma['persen_bonus_pasar'];

        $periode = '';

        $tot_populasi = 0;
        $kanit = null;

        $jml_data = 0;
        foreach ($d_rhpp_plasma['list_noreg'] as $k_ln => $v_ln) {
            $data_header['detail'][ $v_ln['noreg'] ] = array(
                'noreg' => $v_ln['noreg'],
                'populasi' => $v_ln['populasi'],
                'kandang' => $v_ln['kandang'],
                'tgl_docin' => $v_ln['tgl_docin'],
                'tgl_tutup' => $v_ln['tgl_tutup_siklus']
            );

            $m_rs = new \Model\Storage\RdimSubmit_model();
            $d_rs = $m_rs->where('noreg', $v_ln['noreg'])->with(['dPengawas'])->first();

            $kanit = $d_rs->dPengawas->nama;

            $periode .= tglIndonesia($v_ln['tgl_docin'], '-', ' ');
            if ( $jml_data < count($d_rhpp_plasma['list_noreg'])-1 ) {
                $periode .= '_';
            }

            $jml_data++;

            $tot_populasi += $v_ln['populasi'];
        }

        $data_header['tot_populasi'] = $tot_populasi;
        $data_header['user_cetak'] = $this->userdata['detail_user']['nama_detuser'];
        $data_header['kanit'] = $kanit;

        foreach ($d_rhpp_plasma['doc'] as $k_doc => $v_doc) {
            $key_doc = $v_doc['tanggal'].' | '.$v_doc['nota'];
            $data_doc_plasma['doc'][ $key_doc ] = array(
                'tgl_docin' => $v_doc['tanggal'],
                'sj' => $v_doc['nota'],
                'barang' => $v_doc['barang'],
                'box' => $v_doc['box'],
                'jumlah' => $v_doc['jumlah'],
                'harga' => $v_doc['harga'],
                'total' => $v_doc['total']
            );

            $data_doc_plasma['vaksin'][ $key_doc ] = array(
                'barang' => $v_doc['vaksin'],
                'harga' => $v_doc['harga_vaksin'],
                'total' => $v_doc['total_vaksin']
            );
        }
        ksort($data_doc_plasma['doc']);
        ksort($data_doc_plasma['vaksin']);

        foreach ($d_rhpp_plasma['pakan'] as $k_pakan => $v_pakan) {
            // $key_pakan = $v_pakan['tanggal'].' | '.$v_pakan['nota'].' | '.$v_pakan['barang'].' | '.$v_pakan['jumlah'];
            $data_pakan_plasma[] = array(
                'tanggal' => $v_pakan['tanggal'],
                'sj' => $v_pakan['nota'],
                'barang' => $v_pakan['barang'],
                'zak' => $v_pakan['zak'],
                'jumlah' => $v_pakan['jumlah'],
                'harga' => $v_pakan['harga'],
                'total' => $v_pakan['total']
            );
        }
        ksort($data_pakan_plasma);

        if ( !empty($d_rhpp_plasma['pindah_pakan']) ) {
            foreach ($d_rhpp_plasma['pindah_pakan'] as $k_pindah_pakan => $v_pindah_pakan) {
                // $key_pakan = $v_pindah_pakan['tanggal'].' | '.$v_pindah_pakan['nota'].' | '.$v_pindah_pakan['barang'].' | '.$v_pindah_pakan['jumlah'];
                $data_pindah_pakan_plasma[] = array(
                    'tanggal' => $v_pindah_pakan['tanggal'],
                    'sj' => $v_pindah_pakan['nota'],
                    'barang' => $v_pindah_pakan['barang'],
                    'zak' => $v_pindah_pakan['zak'],
                    'jumlah' => $v_pindah_pakan['jumlah'],
                    'harga' => $v_pindah_pakan['harga'],
                    'total' => $v_pindah_pakan['total']
                );
            }

            ksort($data_pindah_pakan_plasma);
        }

        $data_retur_pakan_plasma = null;
        if ( !empty($d_rhpp_plasma['retur_pakan']) ) {
            foreach ($d_rhpp_plasma['retur_pakan'] as $k_retur_pakan => $v_retur_pakan) {
                // $key_pakan = $v_retur_pakan['tanggal'].' | '.$v_retur_pakan['nota'].' | '.$v_retur_pakan['barang'].' | '.$v_retur_pakan['jumlah'];
                $data_retur_pakan_plasma[] = array(
                    'tanggal' => $v_retur_pakan['tanggal'],
                    'sj' => $v_retur_pakan['nota'],
                    'barang' => $v_retur_pakan['barang'],
                    'zak' => $v_retur_pakan['zak'],
                    'jumlah' => $v_retur_pakan['jumlah'],
                    'harga' => $v_retur_pakan['harga'],
                    'total' => $v_retur_pakan['total']
                );
            }

            ksort($data_retur_pakan_plasma);
        }

        foreach ($d_rhpp_plasma['voadip'] as $k_voadip => $v_voadip) {
            // $key_voadip = $v_voadip['tanggal'].' | '.$v_voadip['nota'].' | '.$v_voadip['barang'];
            $data_voadip_plasma[] = array(
                'tanggal' => $v_voadip['tanggal'],
                'sj' => $v_voadip['nota'],
                'barang' => $v_voadip['barang'],
                'jumlah' => $v_voadip['jumlah'],
                'harga' => $v_voadip['harga'],
                'total' => $v_voadip['total'],
            );
        }
        ksort($data_voadip_plasma);

        $data_retur_voadip_plasma = null;
        if ( !empty($d_rhpp_plasma['retur_voadip']) ) {
            foreach ($d_rhpp_plasma['retur_voadip'] as $k_rvoadip => $v_rvoadip) {
                // $key_voadip = $v_rvoadip['tanggal'].' | '.$v_rvoadip['nota'].' | '.$v_rvoadip['barang'];
                $data_retur_voadip_plasma[] = array(
                    'tanggal' => $v_rvoadip['tanggal'],
                    'no_retur' => $v_rvoadip['nota'],
                    'barang' => $v_rvoadip['barang'],
                    'jumlah' => $v_rvoadip['jumlah'],
                    'harga' => $v_rvoadip['harga'],
                    'total' => $v_rvoadip['total'],
                );
            }

            ksort( $data_retur_voadip_plasma );
        }

        $total_tonase_sj = 0;
        $total_ekor_sj = 0;
        foreach ($d_rhpp_plasma['penjualan'] as $k_penjualan => $v_penjualan) {
            $key = $v_penjualan['tanggal'].' | '.$v_penjualan['pembeli'].' | '.$v_penjualan['nota'].' | '.$v_penjualan['bb'].' | '.$v_penjualan['id'];
            $data_rpah_plasma[ $key ] = array(
                'tanggal' => $v_penjualan['tanggal'],
                'pembeli' => $v_penjualan['pembeli'],
                'do' => $v_penjualan['nota'],
                'ekor' => $v_penjualan['ekor'],
                'tonase' => $v_penjualan['tonase'],
                'bb' => $v_penjualan['bb'],
                'hrg_kontrak' => $v_penjualan['harga_kontrak'],
                'total_kontrak' => $v_penjualan['total_kontrak'],
                'hrg_pasar' => $v_penjualan['harga_pasar'],
                'total_pasar' => $v_penjualan['total_pasar'],
                'selisih' => $v_penjualan['selisih'],
                'insentif' => $v_penjualan['insentif'],
                'total_insentif' => $v_penjualan['total_insentif']
            );
        }
        ksort( $data_rpah_plasma );

        $rata_umur_panen = $d_rhpp_plasma['rata_umur'];
        $data_header['rata_umur_panen'] = $rata_umur_panen;

        $data_potongan = null;
        foreach ($d_rhpp_plasma['potongan'] as $k_potongan => $v_potongan) {
            $m_bpp = new \Model\Storage\BayarPenjualanPeralatan_model();
            $d_bpp = $m_bpp->where('id_penjualan_peralatan', $v_potongan['id_trans'])->get();

            $m_pp = new \Model\Storage\PenjualanPeralatan_model();
            $d_pp = $m_pp->where('id', $v_potongan['id_trans'])->first();

            $sudah_bayar = 0;
            if ( $d_bpp->count() > 0 ) {
                foreach ($d_bpp as $k_bpp => $v_bpp) {
                    $sudah_bayar += $v_bpp['saldo'] + $v_bpp['bayar'];
                }
            }

            $data_potongan[] = array(
                'id_jual' => $v_potongan['id_trans'],
                'tanggal' => (isset($d_pp)) ? $d_pp->tanggal : null,
                'keterangan' => $v_potongan['keterangan'],
                'tagihan' => $v_potongan['jumlah_tagihan'],
                'sudah_bayar' => $v_potongan['jumlah_bayar'],
                'sisa_bayar' => ( $v_potongan['jumlah_bayar'] < $v_potongan['jumlah_tagihan'] ) ? $v_potongan['jumlah_tagihan'] - $v_potongan['jumlah_bayar'] : 0
            );
        }
        
        $data_bonus = null;
        foreach ($d_rhpp_plasma['bonus'] as $k_bonus => $v_bonus) {
            $data_bonus[] = array(
                'id_trans' => $v_bonus['id_trans'],
                'keterangan' => $v_bonus['keterangan'],
                'jumlah' => $v_bonus['jumlah'],
            );
        }

        $total_bayar_hutang = 0;
        $data_piutang_plasma = null;
        foreach ($d_rhpp_plasma['piutang'] as $k_piutang => $v_piutang) {
            $data_piutang_plasma[ $v_piutang['id'] ] = array(
                'id' => $v_piutang['id'],
                'kode' => $v_piutang['piutang_kode'],
                'nama_perusahaan' => $v_piutang['nama_perusahaan'],
                'tanggal' => $v_piutang['piutang']['tanggal'],
                'keterangan' => $v_piutang['piutang']['keterangan'],
                'sisa_piutang' => $v_piutang['sisa_piutang'],
                'nominal' => $v_piutang['nominal']
            );

            $total_bayar_hutang += $v_piutang['nominal'];
        }

        $data_header['total_bayar_hutang'] = $total_bayar_hutang;

        $data_detail_plasma = array(
            'data_doc' => $data_doc_plasma,
            'data_pakan' => $data_pakan_plasma,
            'data_pindah_pakan' => $data_pindah_pakan_plasma,
            'data_retur_pakan' => $data_retur_pakan_plasma,
            'data_voadip' => $data_voadip_plasma,
            'data_retur_voadip' => $data_retur_voadip_plasma,
            'data_rpah' => $data_rpah_plasma,
            'data_potongan' => $data_potongan,
            'data_bonus' => $data_bonus,
            'data_piutang_plasma' => $data_piutang_plasma
        );

        $data_rhpp_plasma = array(
            'detail' => $data_detail_plasma
        );

        $data_header['potongan_pajak'] = isset($id) ? $data_header['potongan_pajak'] : $data_header['potongan_pajak'] / count($params['list_noreg']);

        $content['id'] = isset($id) ? $id : null;
        $content['data'] = $data_header;
        $content['data_plasma'] = $data_rhpp_plasma;

        $res_view_html = $this->load->view('transaksi/rhpp_group/export_excel_plasma', $content, TRUE);

        
        $filename = 'RHPP_GROUP_PLASMA_'.str_replace('.', '_', str_replace(',', '_', $d_rhpp_group_header['mitra'])).'_PERIODE_('.$periode.').xls';
        header("Content-type: application/xls");
        header("Content-Disposition: attachment; filename=".$filename."");
        echo $res_view_html;
    }

    public function export_excel_inti($_id)
    {
        $id = exDecrypt( $_id );

        $m_rhpp_group_header = new \Model\Storage\RhppGroupHeader_model();
        $d_rhpp_group_header = $m_rhpp_group_header->where('id', $id)->with(['rhpp'])->first()->toArray();

        $m_mitra = new \Model\Storage\Mitra_model();
        $d_mitra = $m_mitra->where('nomor', $d_rhpp_group_header['nomor'])->orderBy('id', 'desc')->first()->toArray();

        $d_rhpp_inti = null;
        $d_rhpp_plasma = null;
        foreach ($d_rhpp_group_header['rhpp'] as $k_rgh=> $v_rgh) {
            if ( $v_rgh['jenis'] == 'rhpp_inti' ) {
                $d_rhpp_inti = $v_rgh;
            } else {
                $d_rhpp_plasma = $v_rgh;
            }
        }

        $data_header['nomor'] = $d_rhpp_group_header['nomor'];
        $data_header['mitra'] = $d_rhpp_group_header['mitra'];
        $data_header['jenis_mitra'] = $d_mitra['jenis'];
        $data_header['biaya_materai'] = $d_rhpp_inti['biaya_materai'];
        $data_header['biaya_opr'] = $d_rhpp_inti['biaya_operasional'];
        $data_header['bonus_insentif_fcr'] = 0;
        $data_header['total_bonus_insentif_listrik'] = 0;
        $data_header['potongan_pajak'] = $d_rhpp_inti['prs_potongan_pajak'];
        $data_header['fcr'] = $d_rhpp_inti['fcr'];
        $data_header['bb'] = $d_rhpp_inti['bb'];
        $data_header['deplesi'] = $d_rhpp_inti['deplesi'];
        $data_header['ip'] = $d_rhpp_inti['ip'];
        $data_header['bonus_pasar'] = $d_rhpp_plasma['bonus_pasar'];
        $data_header['pendapatan_plasma'] = $d_rhpp_plasma['pdpt_peternak_belum_pajak'];
        $data_header['cn'] = $d_rhpp_inti['cn'];

        $periode = '';

        $tot_populasi = 0;
        $kanit = null;

        $jml_data = 0;
        foreach ($d_rhpp_inti['list_noreg'] as $k_ln => $v_ln) {
            $data_header['detail'][ $v_ln['noreg'] ] = array(
                'noreg' => $v_ln['noreg'],
                'populasi' => $v_ln['populasi'],
                'kandang' => $v_ln['kandang'],
                'tgl_docin' => $v_ln['tgl_docin'],
                'tgl_tutup' => $v_ln['tgl_tutup_siklus']
            );

            $m_rs = new \Model\Storage\RdimSubmit_model();
            $d_rs = $m_rs->where('noreg', $v_ln['noreg'])->with(['dPengawas'])->first();

            $kanit = $d_rs->dPengawas->nama;

            $periode .= tglIndonesia($v_ln['tgl_docin'], '-', ' ');
            if ( $jml_data < count($d_rhpp_inti['list_noreg'])-1 ) {
                $periode .= '_';
            }

            $jml_data++;

            $tot_populasi += $v_ln['populasi'];
        }

        $data_header['tot_populasi'] = $tot_populasi;
        $data_header['user_cetak'] = $this->userdata['detail_user']['nama_detuser'];
        $data_header['kanit'] = $kanit;

        $rata_umur_panen = $d_rhpp_inti['rata_umur'];
        $data_header['rata_umur_panen'] = $rata_umur_panen;

        foreach ($d_rhpp_inti['doc'] as $k_doc => $v_doc) {
            $key_doc = $v_doc['tanggal'].' | '.$v_doc['nota'];
            $data_doc_inti['doc'][ $key_doc ] = array(
                'tgl_docin' => $v_doc['tanggal'],
                'sj' => $v_doc['nota'],
                'barang' => $v_doc['barang'],
                'box' => $v_doc['box'],
                'jumlah' => $v_doc['jumlah'],
                'harga' => $v_doc['harga'],
                'total' => $v_doc['total']
            );
        }
        ksort($data_doc_inti['doc']);

        foreach ($d_rhpp_inti['pakan'] as $k_pakan => $v_pakan) {
            // $key_pakan = $v_pakan['tanggal'].' | '.$v_pakan['nota'].' | '.$v_pakan['barang'].' | '.$v_pakan['jumlah'];
            $data_pakan_inti[] = array(
                'tanggal' => $v_pakan['tanggal'],
                'sj' => $v_pakan['nota'],
                'barang' => $v_pakan['barang'],
                'zak' => $v_pakan['zak'],
                'jumlah' => $v_pakan['jumlah'],
                'harga' => $v_pakan['harga'],
                'total' => $v_pakan['total']
            );
        }
        ksort($data_pakan_inti);

        foreach ($d_rhpp_inti['oa_pakan'] as $k_oa_pakan => $v_oa_pakan) {
            // $key = $v_oa_pakan['tanggal'].' | '.$v_oa_pakan['nota'].' | '.$v_oa_pakan['nopol'].' | '.$v_oa_pakan['barang'].' | '.$v_oa_pakan['jumlah'];
            $data_oa_pakan_inti[] = $v_oa_pakan;
        }
        ksort($data_oa_pakan_inti);

        if ( !empty($d_rhpp_inti['pindah_pakan']) ) {
            foreach ($d_rhpp_inti['pindah_pakan'] as $k_pindah_pakan => $v_pindah_pakan) {
                // $key_pakan = $v_pindah_pakan['tanggal'].' | '.$v_pindah_pakan['nota'].' | '.$v_pindah_pakan['barang'].' | '.$v_pindah_pakan['jumlah'];
                $data_pindah_pakan_inti[] = array(
                    'tanggal' => $v_pindah_pakan['tanggal'],
                    'sj' => $v_pindah_pakan['nota'],
                    'barang' => $v_pindah_pakan['barang'],
                    'zak' => $v_pindah_pakan['zak'],
                    'jumlah' => $v_pindah_pakan['jumlah'],
                    'harga' => $v_pindah_pakan['harga'],
                    'total' => $v_pindah_pakan['total']
                );
            }

            ksort($data_pindah_pakan_inti);

            foreach ($d_rhpp_inti['oa_pindah_pakan'] as $k_oa_pindah_pakan => $v_oa_pindah_pakan) {
                // $key = $v_oa_pindah_pakan['tanggal'].' | '.$v_oa_pindah_pakan['nota'].' | '.$v_oa_pindah_pakan['nopol'].' | '.$v_oa_pindah_pakan['barang'].' | '.$v_oa_pindah_pakan['jumlah'];
                $data_oa_pindah_pakan_inti[] = $v_oa_pindah_pakan;
            }
            if ( !empty($data_oa_pindah_pakan_inti) ) {
                ksort($data_oa_pindah_pakan_inti);
            }
        }

        $data_retur_pakan_inti = null;
        $data_oa_retur_pakan_inti = null;
        if ( !empty($d_rhpp_inti['retur_pakan']) ) {
            foreach ($d_rhpp_inti['retur_pakan'] as $k_retur_pakan => $v_retur_pakan) {
                // $key_pakan = $v_retur_pakan['tanggal'].' | '.$v_retur_pakan['nota'].' | '.$v_retur_pakan['barang'].' | '.$v_retur_pakan['jumlah'];
                $data_retur_pakan_inti[] = array(
                    'tanggal' => $v_retur_pakan['tanggal'],
                    'sj' => $v_retur_pakan['nota'],
                    'barang' => $v_retur_pakan['barang'],
                    'zak' => $v_retur_pakan['zak'],
                    'jumlah' => $v_retur_pakan['jumlah'],
                    'harga' => $v_retur_pakan['harga'],
                    'total' => $v_retur_pakan['total']
                );
            }

            ksort($data_retur_pakan_inti);

            foreach ($d_rhpp_inti['oa_retur_pakan'] as $k_oa_retur_pakan => $v_oa_retur_pakan) {
                // $key = $v_oa_retur_pakan['tanggal'].' | '.$v_oa_retur_pakan['nota'].' | '.$v_oa_retur_pakan['nopol'].' | '.$v_oa_retur_pakan['barang'].' | '.$v_oa_retur_pakan['jumlah'];
                $data_oa_retur_pakan_inti[] = $v_oa_retur_pakan;
            }
            ksort($data_oa_retur_pakan_inti);
        }

        foreach ($d_rhpp_inti['voadip'] as $k_voadip => $v_voadip) {
            $m_brg = new \Model\Storage\Barang_model();
            $d_brg = $m_brg->where('nama', 'like', '%'.$v_voadip['barang'].'%')->orderBy('id', 'desc')->first();

            $key_voadip = $v_voadip['tanggal'].' | '.$v_voadip['nota'].' | '.$v_voadip['barang'].' | '.$v_voadip['harga'];
            $data_voadip_inti[ $key_voadip ] = array(
                'tanggal' => $v_voadip['tanggal'],
                'sj' => $v_voadip['nota'],
                'barang' => $v_voadip['barang'],
                'jumlah' => $v_voadip['jumlah'],
                'harga' => $v_voadip['harga'],
                'total' => $v_voadip['total'],
                'decimal' => !empty($d_brg) ? $d_brg->desimal_harga : 2
            );
        }
        ksort($data_voadip_inti);

        if ( !empty($d_rhpp_inti['retur_voadip']) ) {
            foreach ($d_rhpp_inti['retur_voadip'] as $k_rvoadip => $v_rvoadip) {
                $m_brg = new \Model\Storage\Barang_model();
                $d_brg = $m_brg->where('nama', 'like', '%'.$v_rvoadip['barang'].'%')->orderBy('id', 'desc')->first();

                $key_voadip = $v_rvoadip['tanggal'].' | '.$v_rvoadip['nota'].' | '.$v_rvoadip['barang'].' | '.$v_rvoadip['harga'];
                $data_retur_voadip_inti[ $key_voadip ] = array(
                    'tanggal' => $v_rvoadip['tanggal'],
                    'no_retur' => $v_rvoadip['nota'],
                    'barang' => $v_rvoadip['barang'],
                    'jumlah' => $v_rvoadip['jumlah'],
                    'harga' => $v_rvoadip['harga'],
                    'total' => $v_rvoadip['total'],
                    'decimal' => !empty($d_brg) ? $d_brg->desimal_harga : 2
                );
            }

            ksort( $data_retur_voadip_inti );
        }
        
        foreach ($d_rhpp_inti['penjualan'] as $k_penjualan => $v_penjualan) {
            $key = $v_penjualan['tanggal'].' | '.$v_penjualan['pembeli'].' | '.$v_penjualan['nota'].' | '.$v_penjualan['bb'].' | '.$v_penjualan['id'];
            $data_rpah_inti[ $key ] = array(
                'tanggal' => $v_penjualan['tanggal'],
                'pembeli' => $v_penjualan['pembeli'],
                'do' => $v_penjualan['nota'],
                'ekor' => $v_penjualan['ekor'],
                'tonase' => $v_penjualan['tonase'],
                'bb' => $v_penjualan['bb'],
                'hrg_kontrak' => $v_penjualan['harga_kontrak'],
                'total_kontrak' => $v_penjualan['total_kontrak'],
                'hrg_pasar' => $v_penjualan['harga_pasar'],
                'total_pasar' => $v_penjualan['total_pasar'],
                'selisih' => $v_penjualan['selisih'],
                'insentif' => $v_penjualan['insentif'],
                'total_insentif' => $v_penjualan['total_insentif']
            );
        }
        ksort( $data_rpah_inti );

        $data_detail_inti = array(
            'data_doc' => $data_doc_inti,
            'data_pakan' => $data_pakan_inti,
            'data_oa_pakan' => $data_oa_pakan_inti,
            'data_pindah_pakan' => $data_pindah_pakan_inti,
            'data_oa_pindah_pakan' => $data_oa_pindah_pakan_inti,
            'data_retur_pakan' => $data_retur_pakan_inti,
            'data_oa_retur_pakan' => $data_oa_retur_pakan_inti,
            'data_voadip' => $data_voadip_inti,
            'data_retur_voadip' => $data_retur_voadip_inti,
            'data_rpah' => $data_rpah_inti
        );

        $data_rhpp_inti = array(
            'detail' => $data_detail_inti
        );

        $data_header['potongan_pajak'] = isset($id) ? $data_header['potongan_pajak'] : $data_header['potongan_pajak'] / count($params['list_noreg']);

        $content['id'] = isset($id) ? $id : null;
        $content['data'] = $data_header;
        $content['data_inti'] = $data_rhpp_inti;

        $res_view_html = $this->load->view('transaksi/rhpp_group/export_excel_inti', $content, TRUE);

        header("Content-type: application/xls");
        $filename = 'RHPP_GROUP_INTI_'.str_replace('.', '_', str_replace(',', '_', $d_rhpp_group_header['mitra'])).'_PERIODE_('.$periode.').xls';
        header("Content-Disposition: attachment; filename=".$filename."");
        echo $res_view_html;
    }

    public function export_pdf($_id)
    {
        $id = exDecrypt( $_id );

        $data_rhpp_plasma = null;
        $data_rhpp_int = null;
        $data = null;

        $data_header = array();

        $id_tutup_siklus = null; $mitra = array(); $noreg = array(); $populasi = null; $populasi_ch = null; $kandang = null; $tgl_docin = null; $tutup_siklus = null; $biaya_materai = null; $potongan_pajak = null; $tgl_tutup = null; $rata_umur_panen = null; $biaya_opr = null; $tipe_kandang = null;

        $data_doc_plasma = null; $data_pakan_plasma = null; $data_pindah_pakan_plasma = null; $data_retur_pakan_plasma = null; $data_voadip_plasma = null; $data_retur_voadip_plasma = null; $data_data_rpah_plasma = null;

        $bonus_pasar = 0; $fcr = 0; $bb = 0; $deplesi = 0; $ip = 0;

        $data_potongan = null;
        $data_bonus = null;
        $_noreg = null;

        $m_rhpp_group_header = new \Model\Storage\RhppGroupHeader_model();
        $d_rhpp_group_header = $m_rhpp_group_header->where('id', $id)->with(['rhpp'])->first()->toArray();

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                mtr.nomor,
                mtr.nama,
                prs.kode as kode_perusahaan,
                prs.perusahaan as nama_perusahaan
            from
                (
                    select mtr1.* from mitra mtr1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) mtr2
                        on
                            mtr1.id = mtr2.id
                ) mtr
            left join
                (
                    select prs1.* from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    prs.kode = mtr.perusahaan
            where
                mtr.nomor = '".$d_rhpp_group_header['nomor']."'
        ";
        $d_conf = $m_conf->hydrateRaw($sql);

        $nama_perusahaan = null;
        if ( $d_conf->count() > 0 ) {
            $nama_perusahaan = $d_conf->toArray()[0]['nama_perusahaan'];
        }

        $d_rhpp_inti = null;
        $d_rhpp_plasma = null;
        foreach ($d_rhpp_group_header['rhpp'] as $k_rgh=> $v_rgh) {
            if ( $v_rgh['jenis'] == 'rhpp_plasma' ) {
                $d_rhpp_plasma = $v_rgh;
            }
        }

        $rata_harga_panen = 0;
        $data_pemakaian_pakan = null;
        $hasil = null;
        $biaya_produksi = null;
        $hasil_produksi = null;

        $data_header['nomor'] = $d_rhpp_group_header['nomor'];
        $data_header['mitra'] = $d_rhpp_group_header['mitra'];
        $data_header['nama_perusahaan'] = $nama_perusahaan;
        $data_header['biaya_materai'] = $d_rhpp_plasma['biaya_materai'];
        $data_header['biaya_opr'] = $d_rhpp_plasma['biaya_operasional'];
        $data_header['fcr'] = $d_rhpp_plasma['fcr'];
        $data_header['bb'] = $d_rhpp_plasma['bb'];
        $data_header['deplesi'] = $d_rhpp_plasma['deplesi'];
        $data_header['ip'] = $d_rhpp_plasma['ip'];
        $data_header['bonus_insentif_fcr'] = $d_rhpp_plasma['bonus_insentif_fcr'];
        $data_header['total_bonus_insentif_listrik'] = $d_rhpp_plasma['total_bonus_insentif_listrik'];
        $data_header['prs_bonus_pasar'] = $d_rhpp_plasma['persen_bonus_pasar'];
        $data_header['bonus_pasar'] = $d_rhpp_plasma['bonus_pasar'];
        $data_header['bonus_kematian'] = $d_rhpp_plasma['bonus_kematian'];
        $data_header['pdpt_peternak_belum_pajak'] = $d_rhpp_plasma['pdpt_peternak_belum_pajak'];
        $data_header['prs_potongan_pajak'] = $d_rhpp_plasma['prs_potongan_pajak'];
        $data_header['potongan_pajak'] = $d_rhpp_plasma['potongan_pajak'];
        $data_header['pdpt_peternak_sudah_pajak'] = $d_rhpp_plasma['pdpt_peternak_belum_pajak'] - $d_rhpp_plasma['potongan_pajak'];
        $data_header['catatan'] = !empty($d_rhpp_group_header) ? $d_rhpp_group_header['catatan_print'] : 0;
        $data_header['user_cetak'] = $this->userdata['detail_user']['nama_detuser'];
        $data_header['tot_penjualan_ayam'] = $d_rhpp_plasma['tot_penjualan_ayam'];
        $data_header['tot_pembelian_sapronak'] = $d_rhpp_plasma['tot_pembelian_sapronak'];

        $nama_user_cetak = $this->userdata['detail_user']['nama_detuser'];
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                uk.* 
            from unit_karyawan uk
            right join
                karyawan k
                on
                    uk.id_karyawan =  k.id
            where
                k.nama like '".$nama_user_cetak."' and
                k.status = 1

        ";
        $d_karyawan = $m_conf->hydrateRaw( $sql );

        $unit_karyawan = null;
        if ( $d_karyawan->count() > 0 ) {
            $d_karyawan = $d_karyawan->toArray()[0];

            if ( stristr($d_karyawan['unit'], 'all') !== false ) {
                $sql = "select * from wilayah w where w.pusat = 1";
            } else {
                $sql = "select * from wilayah w where w.id = ".$d_karyawan['unit']."";
            }

            $d_unit = $m_conf->hydrateRaw( $sql );

            if ( $d_unit->count() > 0 ) {
                $unit_karyawan = str_replace('Kab ', '', str_replace('Kota ', '', $d_unit->toArray()[0]['nama']));
            }
        } else {
            $sql = "select * from wilayah w where w.pusat = 1";
            $d_unit = $m_conf->hydrateRaw( $sql );

            if ( $d_unit->count() > 0 ) {
                $unit_karyawan = str_replace('Kab ', '', str_replace('Kota ', '', $d_unit->toArray()[0]['nama']));
            }
        }

        $data_header['unit_karyawan'] = $unit_karyawan;
        $data_header['tgl_submit'] = $d_rhpp_group_header['tgl_submit'];

        $tot_populasi = 0;
        foreach ($d_rhpp_plasma['list_noreg'] as $k_ln => $v_ln) {
            $data_header['detail'][ $v_ln['noreg'] ] = array(
                'noreg' => $v_ln['noreg'],
                'populasi' => $v_ln['populasi'],
                'kandang' => $v_ln['kandang'],
                'tgl_docin' => $v_ln['tgl_docin'],
                'tgl_tutup' => $v_ln['tgl_tutup_siklus']
            );

            $m_rs = new \Model\Storage\RdimSubmit_model();
            $d_rs = $m_rs->where('noreg', $v_ln['noreg'])->with(['dPengawas', 'dSampling', 'mitra', 'dKandang'])->first()->toArray();

            $npwp = $d_rs['mitra']['d_mitra']['npwp'];

            $rt_mitra = !empty($d_rs['mitra']['d_mitra']['alamat_rt']) ? ', RT.'.$d_rs['mitra']['d_mitra']['alamat_rt'] : null;
            $rw_mitra = !empty($d_rs['mitra']['d_mitra']['alamat_rw']) ? ' / RW.'.$d_rs['mitra']['d_mitra']['alamat_rw'] : null;
            $kelurahan_mitra = !empty($d_rs['mitra']['d_mitra']['alamat_kelurahan']) ? ', Kel. '.$d_rs['mitra']['d_mitra']['alamat_kelurahan'] : null;
            $kecamatan_mitra = !empty($d_rs['mitra']['d_mitra']['d_kecamatan']) ? ', Kec. '.$d_rs['mitra']['d_mitra']['d_kecamatan']['nama'] : null;
            $kab_kota_mitra = !empty($d_rs['mitra']['d_mitra']['d_kecamatan']['d_kota']) ? ', '.str_replace('Kota ', '', str_replace('Kab ', '', $d_rs['mitra']['d_mitra']['d_kecamatan']['d_kota']['nama'])) : null;

            $alamat_mitra = $d_rs['mitra']['d_mitra']['alamat_jalan'] . $rt_mitra . $rw_mitra . $kelurahan_mitra . $kecamatan_mitra . $kab_kota_mitra;

            $rt_kdg = !empty($d_rs['d_kandang']['alamat_rt']) ? ', RT.'.$d_rs['d_kandang']['alamat_rt'] : null;
            $rw_kdg = !empty($d_rs['d_kandang']['alamat_rw']) ? ' / RW.'.$d_rs['d_kandang']['alamat_rw'] : null;
            $kelurahan_kdg = !empty($d_rs['d_kandang']['alamat_kelurahan']) ? ', Kel. '.$d_rs['d_kandang']['alamat_kelurahan'] : null;
            $kecamatan_kdg = !empty($d_rs['d_kandang']['d_kecamatan']) ? ', Kec. '.$d_rs['d_kandang']['d_kecamatan']['nama'] : null;
            $kab_kota_kdg = !empty($d_rs['d_kandang']['d_kecamatan']['d_kota']) ? ', '.str_replace('Kota ', '', str_replace('Kab ', '', $d_rs['d_kandang']['d_kecamatan']['d_kota']['nama'])) : null;

            $alamat_kdg = $d_rs['d_kandang']['alamat_jalan'] . $rt_kdg . $rw_kdg . $kelurahan_kdg . $kecamatan_kdg . $kab_kota_kdg;

            $unit = str_replace('Kota ', '', str_replace('Kab ', '', $d_rs['d_kandang']['d_unit']['nama']));

            $data_header['npwp'] = $npwp;
            $data_header['alamat_mitra'] = $alamat_mitra;
            $data_header['alamat_kdg'][ $alamat_kdg ] = $alamat_kdg;
            $data_header['tgl_docin'][ $v_ln['tgl_docin'] ] = tglIndonesia($v_ln['tgl_docin'], '-', ' ', true);
            $data_header['ppl'][ $d_rs['sampling'] ] = $d_rs['d_sampling']['nama'];
            $data_header['kanit'] = $d_rs['d_pengawas']['nama'];
            $data_header['unit'][ $d_rs['d_kandang']['d_unit']['kode'] ] = $unit;

            $tot_populasi += $v_ln['populasi'];
        }

        $data_header['populasi'] = $tot_populasi;

        foreach ($d_rhpp_plasma['doc'] as $k_doc => $v_doc) {
            $key_doc = $v_doc['tanggal'].' | '.$v_doc['nota'];
            $data_doc_plasma['doc'][ $key_doc ] = array(
                'tgl_docin' => $v_doc['tanggal'],
                'sj' => $v_doc['nota'],
                'barang' => $v_doc['barang'],
                'box' => $v_doc['box'],
                'jumlah' => $v_doc['jumlah'],
                'harga' => $v_doc['harga'],
                'total' => $v_doc['total']
            );

            $data_doc_plasma['vaksin'][ $key_doc ] = array(
                'barang' => $v_doc['vaksin'],
                'harga' => $v_doc['harga_vaksin'],
                'total' => $v_doc['total_vaksin']
            );

            $key_bp = $v_doc['barang'];
            if ( !isset($biaya_produksi['doc'][ $key_bp ]) ) {
                $biaya_produksi['doc'][ $key_bp ] = array(
                    'nama' => $v_doc['barang'],
                    'jumlah' => $v_doc['jumlah'],
                    'harga' => $v_doc['harga'],
                    'total' => $v_doc['total']
                );

                $biaya_produksi['vaksin'][ $key_bp ] = array(
                    'nama' => $v_doc['vaksin'],
                    'jumlah' => $v_doc['jumlah'],
                    'harga' => $v_doc['harga_vaksin'],
                    'total' => $v_doc['total_vaksin']
                );
            } else {
                $biaya_produksi['doc'][ $key_bp ]['jumlah'] += $v_doc['jumlah'];
                $biaya_produksi['doc'][ $key_bp ]['total'] += $v_doc['total'];
                $biaya_produksi['vaksin'][ $key_bp ]['jumlah'] += $v_doc['jumlah'];
                $biaya_produksi['vaksin'][ $key_bp ]['total'] += $v_doc['total_vaksin'];
            }
        }
        ksort($data_doc_plasma['doc']);
        ksort($data_doc_plasma['vaksin']);

        foreach ($d_rhpp_plasma['pakan'] as $k_pakan => $v_pakan) {
            // $key_pakan = $v_pakan['tanggal'].' | '.$v_pakan['nota'].' | '.$v_pakan['barang'].' | '.$v_pakan['jumlah'];
            $data_pakan_plasma[] = array(
                'tanggal' => $v_pakan['tanggal'],
                'sj' => $v_pakan['nota'],
                'barang' => $v_pakan['barang'],
                'zak' => $v_pakan['zak'],
                'jumlah' => $v_pakan['jumlah'],
                'harga' => $v_pakan['harga'],
                'total' => $v_pakan['total']
            );

            if ( !isset($data_pemakaian_pakan[ $v_pakan['barang'] ]) ) {
                $data_pemakaian_pakan[ $v_pakan['barang'] ] = array(
                    'nama' => $v_pakan['barang'],
                    'jumlah' => $v_pakan['jumlah'],
                    'zak' => $v_pakan['zak']
                );
            } else {
                $data_pemakaian_pakan[ $v_pakan['barang'] ]['jumlah'] += $v_pakan['jumlah'];
                $data_pemakaian_pakan[ $v_pakan['barang'] ]['zak'] += $v_pakan['zak'];
            }

            if ( !isset($biaya_produksi['pakan'][ $v_pakan['barang'].' | '.$v_pakan['harga'] ]) ) {
                $biaya_produksi['pakan'][ $v_pakan['barang'].' | '.$v_pakan['harga'] ] = array(
                    'nama' => $v_pakan['barang'],
                    'jumlah' => $v_pakan['jumlah'],
                    'zak' => $v_pakan['zak'],
                    'harga' => $v_pakan['harga'],
                    'total' => $v_pakan['total']
                );
            } else {
                $biaya_produksi['pakan'][ $v_pakan['barang'].' | '.$v_pakan['harga'] ]['jumlah'] += $v_pakan['jumlah'];
                $biaya_produksi['pakan'][ $v_pakan['barang'].' | '.$v_pakan['harga'] ]['zak'] += $v_pakan['zak'];
                $biaya_produksi['pakan'][ $v_pakan['barang'].' | '.$v_pakan['harga'] ]['total'] += $v_pakan['total'];
            }
        }
        ksort($data_pakan_plasma);

        if ( !empty($d_rhpp_plasma['pindah_pakan']) ) {
            foreach ($d_rhpp_plasma['pindah_pakan'] as $k_pindah_pakan => $v_pindah_pakan) {
                // $key_pakan = $v_pindah_pakan['tanggal'].' | '.$v_pindah_pakan['nota'].' | '.$v_pindah_pakan['barang'].' | '.$v_pindah_pakan['jumlah'];
                $data_pindah_pakan_plasma[] = array(
                    'tanggal' => $v_pindah_pakan['tanggal'],
                    'sj' => $v_pindah_pakan['nota'],
                    'barang' => $v_pindah_pakan['barang'],
                    'zak' => $v_pindah_pakan['zak'],
                    'jumlah' => $v_pindah_pakan['jumlah'],
                    'harga' => $v_pindah_pakan['harga'],
                    'total' => $v_pindah_pakan['total']
                );

                $data_pemakaian_pakan[ $v_pindah_pakan['barang'] ]['jumlah'] -= $v_pindah_pakan['jumlah'];
                $data_pemakaian_pakan[ $v_pindah_pakan['barang'] ]['zak'] -= $v_pindah_pakan['zak'];

                $biaya_produksi['pakan'][ $v_pindah_pakan['barang'].' | '.$v_pindah_pakan['harga'] ]['jumlah'] -= $v_pindah_pakan['jumlah'];
                $biaya_produksi['pakan'][ $v_pindah_pakan['barang'].' | '.$v_pindah_pakan['harga'] ]['zak'] -= $v_pindah_pakan['zak'];
                $biaya_produksi['pakan'][ $v_pindah_pakan['barang'].' | '.$v_pindah_pakan['harga'] ]['total'] -= $v_pindah_pakan['total'];
            }

            ksort($data_pindah_pakan_plasma);
        }

        if ( !empty($d_rhpp_plasma['retur_pakan']) ) {
            foreach ($d_rhpp_plasma['retur_pakan'] as $k_retur_pakan => $v_retur_pakan) {
                // $key_pakan = $v_retur_pakan['tanggal'].' | '.$v_retur_pakan['nota'].' | '.$v_retur_pakan['barang'].' | '.$v_retur_pakan['jumlah'];
                $data_retur_pakan_plasma[] = array(
                    'tanggal' => $v_retur_pakan['tanggal'],
                    'sj' => $v_retur_pakan['nota'],
                    'barang' => $v_retur_pakan['barang'],
                    'zak' => $v_retur_pakan['zak'],
                    'jumlah' => $v_retur_pakan['jumlah'],
                    'harga' => $v_retur_pakan['harga'],
                    'total' => $v_retur_pakan['total']
                );

                $data_pemakaian_pakan[ $v_retur_pakan['barang'] ]['jumlah'] -= $v_retur_pakan['jumlah'];
                $data_pemakaian_pakan[ $v_retur_pakan['barang'] ]['zak'] -= $v_retur_pakan['zak'];

                $biaya_produksi['pakan'][ $v_retur_pakan['barang'].' | '.$v_retur_pakan['harga'] ]['jumlah'] -= $v_retur_pakan['jumlah'];
                $biaya_produksi['pakan'][ $v_retur_pakan['barang'].' | '.$v_retur_pakan['harga'] ]['zak'] -= $v_retur_pakan['zak'];
                $biaya_produksi['pakan'][ $v_retur_pakan['barang'].' | '.$v_retur_pakan['harga'] ]['total'] -= $v_retur_pakan['total'];
            }

            ksort($data_retur_pakan_plasma);
        }

        $total_voadip = 0;

        foreach ($d_rhpp_plasma['voadip'] as $k_voadip => $v_voadip) {
            $m_brg = new \Model\Storage\Barang_model();
            $d_brg = $m_brg->where('nama', 'like', '%'.$v_voadip['barang'].'%')->orderBy('id', 'desc')->first();
            // $key_voadip = $v_voadip['tanggal'].' | '.$v_voadip['nota'].' | '.$v_voadip['barang'];
            $data_voadip_plasma[] = array(
                'tanggal' => $v_voadip['tanggal'],
                'sj' => $v_voadip['nota'],
                'barang' => $v_voadip['barang'],
                'jumlah' => $v_voadip['jumlah'],
                'harga' => $v_voadip['harga'],
                'total' => $v_voadip['total'],
                'decimal' => !empty($d_brg) ? $d_brg->desimal_harga : 2
            );

            $total_voadip += $v_voadip['total'];
        }
        ksort($data_voadip_plasma);

        if ( !empty($d_rhpp_plasma['retur_voadip']) ) {
            foreach ($d_rhpp_plasma['retur_voadip'] as $k_rvoadip => $v_rvoadip) {
                $m_brg = new \Model\Storage\Barang_model();
                $d_brg = $m_brg->where('nama', 'like', '%'.$v_rvoadip['barang'].'%')->orderBy('id', 'desc')->first();
                // $key_voadip = $v_rvoadip['tanggal'].' | '.$v_rvoadip['nota'].' | '.$v_rvoadip['barang'];
                $data_retur_voadip_plasma[] = array(
                    'tanggal' => $v_rvoadip['tanggal'],
                    'no_retur' => $v_rvoadip['nota'],
                    'barang' => $v_rvoadip['barang'],
                    'jumlah' => $v_rvoadip['jumlah'],
                    'harga' => $v_rvoadip['harga'],
                    'total' => $v_rvoadip['total'],
                    'decimal' => !empty($d_brg) ? $d_brg->desimal_harga : 2
                );

                $total_voadip -= $v_rvoadip['total'];
            }

            ksort( $data_retur_voadip_plasma );
        }

        $biaya_produksi['voadip'] = array(
            'total' => $total_voadip
        );

        $data_header['biaya_produksi'] = $biaya_produksi;

        $total_tonase = 0;
        $total_ekor = 0;
        $total_nilai = 0;
        foreach ($d_rhpp_plasma['penjualan'] as $k_penjualan => $v_penjualan) {
            $key = $v_penjualan['tanggal'].' | '.$v_penjualan['pembeli'].' | '.$v_penjualan['nota'].' | '.$v_penjualan['bb'].' | '.$v_penjualan['id'];
            $data_rpah_plasma[ $key ] = array(
                'tanggal' => $v_penjualan['tanggal'],
                'pembeli' => $v_penjualan['pembeli'],
                'do' => $v_penjualan['nota'],
                'ekor' => $v_penjualan['ekor'],
                'tonase' => $v_penjualan['tonase'],
                'bb' => $v_penjualan['bb'],
                'hrg_kontrak' => $v_penjualan['harga_kontrak'],
                'total_kontrak' => $v_penjualan['total_kontrak'],
                'hrg_pasar' => $v_penjualan['harga_pasar'],
                'total_pasar' => $v_penjualan['total_pasar'],
                'selisih' => $v_penjualan['selisih'],
                'insentif' => $v_penjualan['insentif'],
                'total_insentif' => $v_penjualan['total_insentif']
            );

            $m_drs = new \Model\Storage\DetRealSJ_model();
            $d_drs = $m_drs->where('no_do', $v_penjualan['nota'])->where('ekor', $v_penjualan['ekor'])->where('tonase', $v_penjualan['tonase'])->where('bb', $v_penjualan['bb'])->orderBy('id', 'desc')->first();

            $jenis_ayam = $d_drs->jenis_ayam;

            if ( !isset($hasil[ $jenis_ayam ]) ) {
                $hasil[ $jenis_ayam ] = array(
                    'jenis_ayam' => $this->config->item('jenis_ayam')[ $jenis_ayam ],
                    'jumlah_kg' => $v_penjualan['tonase'],
                    'jumlah_ekor' => $v_penjualan['ekor']
                );
            } else {
                $hasil[ $jenis_ayam ]['jumlah_kg'] += $v_penjualan['tonase'];
                $hasil[ $jenis_ayam ]['jumlah_ekor'] += $v_penjualan['ekor'];
            }

            if ( !isset($hasil_produksi[ $jenis_ayam.' | '.$v_penjualan['harga_kontrak'] ]) ) {
                $hasil_produksi[ $jenis_ayam.' | '.$v_penjualan['harga_kontrak'] ] = array(
                    'jenis_ayam' => $this->config->item('jenis_ayam')[ $jenis_ayam ],
                    'jumlah_kg' => $v_penjualan['tonase'],
                    'jumlah_ekor' => $v_penjualan['ekor'],
                    'harga' => $v_penjualan['harga_kontrak'],
                    'total' => $v_penjualan['total_kontrak']
                );
            } else {
                $hasil_produksi[ $jenis_ayam.' | '.$v_penjualan['harga_kontrak'] ]['jumlah_kg'] += $v_penjualan['tonase'];
                $hasil_produksi[ $jenis_ayam.' | '.$v_penjualan['harga_kontrak'] ]['jumlah_ekor'] += $v_penjualan['ekor'];
                $hasil_produksi[ $jenis_ayam.' | '.$v_penjualan['harga_kontrak'] ]['total'] += $v_penjualan['total_kontrak'];
            }

            $total_tonase += $v_penjualan['tonase'];
            $total_ekor += $v_penjualan['ekor'];
            $total_nilai += $v_penjualan['total_pasar'];
        }
        ksort( $data_rpah_plasma );

        $rata_harga_panen = $total_nilai / $total_tonase;
        $data_header['rata_harga_panen'] = $rata_harga_panen;
        $data_header['total_tonase_panen'] = $total_tonase;
        $data_header['total_ekor_panen'] = $total_ekor;
        $data_header['hasil_produksi'] = $hasil_produksi;

        foreach ($d_rhpp_plasma['potongan'] as $k_potongan => $v_potongan) {
            // $key = $v_penjualan['tanggal'].' | '.$v_penjualan['nota'].' | '.$v_penjualan['bb'];
            $m_bpp = new \Model\Storage\BayarPenjualanPeralatan_model();
            $d_bpp = $m_bpp->where('id_penjualan_peralatan', $v_potongan['id_trans'])->get();

            $m_pp = new \Model\Storage\PenjualanPeralatan_model();
            $d_pp = $m_pp->where('id', $v_potongan['id_trans'])->first();

            $sudah_bayar = 0;
            if ( $d_bpp->count() > 0 ) {
                foreach ($d_bpp as $k_bpp => $v_bpp) {
                    $sudah_bayar += $v_bpp['saldo'] + $v_bpp['bayar'];
                }
            }

            $data_potongan[ $v_potongan['id'] ] = array(
                'id_jual' => $v_potongan['id_trans'],
                'tanggal' => (isset($d_pp)) ? $d_pp->tanggal : null,
                'keterangan' => $v_potongan['keterangan'],
                'tagihan' => $v_potongan['jumlah_tagihan'],
                'sudah_bayar' => $v_potongan['jumlah_bayar'],
                'sisa_bayar' => ( $v_potongan['jumlah_bayar'] < $v_potongan['jumlah_tagihan'] ) ? $v_potongan['jumlah_tagihan'] - $v_potongan['jumlah_bayar'] : 0
            );
        }
                        
        foreach ($d_rhpp_plasma['bonus'] as $k_bonus => $v_bonus) {
            $data_bonus[ $v_bonus['id'] ] = array(
                'id_trans' => $v_bonus['id_trans'],
                'keterangan' => $v_bonus['keterangan'],
                'jumlah' => $v_bonus['jumlah'],
            );
        }

        $total_bayar_hutang = 0;
        $data_piutang_plasma = null;
        foreach ($d_rhpp_plasma['piutang'] as $k_piutang => $v_piutang) {
            $data_piutang_plasma[ $v_piutang['id'] ] = array(
                'id' => $v_piutang['id'],
                'kode' => $v_piutang['piutang_kode'],
                'nama_perusahaan' => $v_piutang['nama_perusahaan'],
                'tanggal' => $v_piutang['piutang']['tanggal'],
                'keterangan' => $v_piutang['piutang']['keterangan'],
                'sisa_piutang' => $v_piutang['sisa_piutang'],
                'nominal' => $v_piutang['nominal']
            );

            $total_bayar_hutang = $v_piutang['nominal'];
        }

        $data_header['total_bayar_hutang'] = $total_bayar_hutang;
        $data_header['pdpt_peternak_sudah_potong_hutang'] = ($data_header['pdpt_peternak_sudah_pajak'] - $total_bayar_hutang);

        $rata_umur_panen = $d_rhpp_plasma['rata_umur'];
        $data_header['rata_umur_panen'] = $rata_umur_panen;

        $data_detail_plasma = array(
            'data_doc' => $data_doc_plasma,
            'data_pakan' => $data_pakan_plasma,
            'data_pindah_pakan' => $data_pindah_pakan_plasma,
            'data_retur_pakan' => $data_retur_pakan_plasma,
            'data_voadip' => $data_voadip_plasma,
            'data_retur_voadip' => $data_retur_voadip_plasma,
            'data_rpah' => $data_rpah_plasma,
            'data_potongan' => $data_potongan,
            'data_bonus' => $data_bonus,
            'data_piutang_plasma' => $data_piutang_plasma
        );

        $data_rhpp_plasma = array(
            'detail' => $data_detail_plasma
        );

        $data_header['potongan_pajak'] = isset($id) ? $data_header['potongan_pajak'] : $data_header['potongan_pajak'] / count($params['list_noreg']);

        $content['id'] = isset($id) ? $id : null;
        $content['data'] = $data_header;
        $content['data_plasma'] = $data_rhpp_plasma;

        $res_view_html = $this->load->view('transaksi/rhpp_group/export_to_pdf', $content, true);

        // cetak_r( $content['data_plasma'], 1 );
        // cetak_r( $res_view_html );

        $this->load->library('PDFGenerator');
        $this->pdfgenerator->generate($res_view_html, "coba", 'legal', 'portrait');
    }

    public function updateCatatan()
    {
        $id = $this->input->post('id');
        $keterangan = $this->input->post('keterangan');

        try {
            $_id = exDecrypt( $id );

            $m_rhpp = new \Model\Storage\RhppGroupHeader_model();
            $m_rhpp->where('id', $_id)->update(
                array(
                    'catatan_print' => $keterangan
                )
            );

            $this->result['status'] = 1;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function submitCn()
    {
        $params = $this->input->post('params');

        try {
            // cetak_r( $params, 1 );

            $id = $params['id'];

            foreach ($params['data_rhpp'] as $k_rhpp => $v_rhpp) {
                $m_rhpp = new \Model\Storage\RhppGroup_model();
                $m_rhpp->where('id_header', $id)->where('jenis', $v_rhpp['jenis'])->update(
                    array(
                        'cn' => $params['nilai_cn'],
                        'biaya_operasional' => $params['nilai_opr'],
                        'lr_inti' => $v_rhpp['lr_inti']
                    )
                );
            }

            $m_rgh = new \Model\Storage\RhppGroupHeader_model();
            $d_rgh = $m_rgh->where('id', $id)->first();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal 'RHPP', NULL, NULL, 0, 'rhpp_group_header', ".$id.", ".$id.", 2";

            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'submit cn oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_rgh, $deskripsi_log);
            
            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil disimpan';
            $this->result['content'] = array('id' => $id);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function modalPiutang() {
        $no_mitra = $this->input->get('kode');

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                p.*,
                bp.nominal as tot_bayar,
                (p.nominal - isnull(bp.nominal, 0)) as sisa_piutang,
                mtr.nama as nama_mitra,
                mtr.kode_unit,
                prs.perusahaan as nama_perusahaan
            from piutang p
            right join
                (
                    select m1.*, w.kode as kode_unit from mitra m1
                    right join
                        (
                            select max(id) as id, nomor from mitra group by nomor
                        ) m2
                        on
                            m1.id = m2.id
                    left join
                        mitra_mapping mm
                        on
                            mm.mitra = m1.id
                    left join
                        (
                            select kdg1.* from kandang kdg1
                            right join
                                (select max(id) as id, mitra_mapping from kandang group by mitra_mapping) kdg2
                                on
                                    kdg1.id = kdg2.id
                        ) kdg
                        on
                            kdg.mitra_mapping = mm.id
                    left join
                        wilayah w
                        on
                            w.id = kdg.unit
                ) mtr
                on
                    mtr.nomor = p.mitra
            right join
                (
                    select 
                        p1.*
                    from perusahaan p1
                    right join
                        (
                            select max(id) as id, kode from perusahaan group by kode
                        ) p2
                        on
                            p1.id = p2.id
                ) prs
                on
                    prs.kode = p.perusahaan
            left join
                (
                    select
                        data.piutang_kode,
                        sum(data.nominal) as nominal
                    from (
                        select piutang_kode, sum(nominal) as nominal from bayar_piutang group by piutang_kode

                        union all

                        select piutang_kode, sum(nominal) as nominal from rhpp_piutang group by piutang_kode

                        union all

                        select piutang_kode, sum(nominal) as nominal from rhpp_group_piutang group by piutang_kode
                    ) data
                    group by
                        data.piutang_kode
                ) bp
                on
                    p.kode = bp.piutang_kode
            where
                p.jenis = 'mitra' and
                (p.nominal - isnull(bp.nominal, 0)) > 0 and
                mtr.pemilik = '".$no_mitra."'
            order by
                p.tanggal desc,
                mtr.nama asc
        ";
        $d_piutang = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_piutang->count() > 0 ) {
            $data = $d_piutang->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view('transaksi/rhpp_group/modal_piutang', $content, TRUE);

        echo $html;
    }

    public function tes()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from rhpp_group_header where tgl_submit >= '2023-08-01'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $id = $value['id'];

                $m_conf = new \Model\Storage\Conf();
                $sql = "exec insert_jurnal 'RHPP', NULL, NULL, 0, 'rhpp_group_header', ".$id.", ".$id.", 2";
        
                $d_conf = $m_conf->hydrateRaw( $sql );
            }
        }

    }
}