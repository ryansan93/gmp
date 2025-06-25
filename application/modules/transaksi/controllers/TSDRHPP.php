<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TSDRHPP extends Public_Controller {

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
                "assets/transaksi/tsdrhpp/js/tsdrhpp.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/tsdrhpp/css/tsdrhpp.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;

            // Load Indexx
            $data['title_menu'] = 'Tutup Siklus & RHPP';
            $data['view'] = $this->load->view('transaksi/tsdrhpp/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_lists()
    {
        $params = $this->input->get('params');

        $filter = $params['filter'];
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $sql_filter = "";
        if ( $filter != 0 ) {
            $sql_filter = "where data.tutup_siklus = ".$filter."";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.mitra,
                data.noreg,
                data.kandang,
                data.populasi,
                data.tgl_docin_real,
                data.tgl_panen,
                data.tutup_siklus,
                data.deskripsi,
                data.waktu
            from
            (
                select
                    m.nama as mitra,
                    _noreg.noreg,
                    SUBSTRING(_noreg.noreg, 10, 2) as kandang,
                    rdim.populasi,
                    CONVERT(varchar(10), _noreg.tgl_docin, 120) as tgl_docin_real, 
                    rs.tgl_panen,
                    case
                        when ts.id is not null then
                            2
                        else
                            1
                    end as tutup_siklus,
                    case
                        when ts.id is not null then
                            lt.deskripsi
                        else
                            null
                    end as deskripsi,
                    case
                        when ts.id is not null then
                            lt.waktu
                        else
                            null
                    end as waktu
                from
                (
                    select
                        od.noreg,
                        td.datang as tgl_docin
                    from
                    (
                        select td1.* from terima_doc td1
                        right join
                            (select max(id) as id, no_order from terima_doc group by no_order) td2
                            on
                                td1.id = td2.id
                        where
                            td1.datang between '".$start_date.' 00:00:00.000'."' and '".$end_date.' 23:59:59.999'."'
                    ) td
                    left join
                        (
                            select od1.* from order_doc od1
                            right join
                                (select max(id) as id, no_order from order_doc group by no_order) od2
                                on
                                    od1.id = od2.id
                        ) od
                        on
                            td.no_order = od.no_order

                    union all

                    select 
                        rs.noreg,
                        rs.tgl_docin
                    from rdim_submit rs
                    left join
                        (
                            select
                                od.noreg
                            from
                            (
                                select td1.* from terima_doc td1
                                right join
                                    (select max(id) as id, no_order from terima_doc group by no_order) td2
                                    on
                                        td1.id = td2.id
                            ) td
                            left join
                                (
                                    select od1.* from order_doc od1
                                    right join
                                        (select max(id) as id, no_order from order_doc group by no_order) od2
                                        on
                                            od1.id = od2.id
                                ) od
                                on
                                    td.no_order = od.no_order
                        ) td
                        on
                            td.noreg = rs.noreg
                    where
                        rs.tgl_docin between '".$start_date."' and '".$end_date."' and
                        td.noreg is null
                ) _noreg
                left join
                    (
                        select mm1.* from mitra_mapping mm1
                        right join
                            (select max(id) as id, nim from mitra_mapping group by nim) mm2
                            on
                                mm1.id = mm2.id
                    ) mm
                    on
                        SUBSTRING(_noreg.noreg, 1, 7) = mm.nim
                left join
                    mitra m
                    on
                        m.id = mm.mitra
                left join
                    (
                        select min(tgl_panen) as tgl_panen, noreg from real_sj rs group by noreg
                    ) rs
                    on
                        _noreg.noreg = rs.noreg
                left join
                    rdim_submit rdim
                    on
                        _noreg.noreg = rdim.noreg
                left join
                    tutup_siklus ts
                    on
                        ts.noreg = _noreg.noreg
                left join
                    (
                        select lt1.* from log_tables lt1
                        right join
                            (select max(id) as id, tbl_name, tbl_id from log_tables where tbl_name = 'tutup_siklus' group by tbl_name, tbl_id) lt2
                            on
                                lt1.id = lt2.id
                    ) lt
                    on
                        lt.tbl_id = ts.id
            ) data
            ".$sql_filter."
            group by
                data.mitra,
                data.noreg,
                data.kandang,
                data.populasi,
                data.tgl_docin_real,
                data.tgl_panen,
                data.tutup_siklus,
                data.deskripsi,
                data.waktu
            order by
                -- data.noreg asc,
                data.tgl_docin_real asc,
                data.mitra asc,
                data.kandang asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        // cetak_r( $data, 1 );

        // $m_ts = new \Model\Storage\TutupSiklus_model();
        // $m_konfir = new \Model\Storage\Konfir_model();
        // $m_rs = new \Model\Storage\RdimSubmit_model();

        // $noreg_rs = array();

        // $m_td = new \Model\Storage\TerimaDoc_model();
        // $no_order = $m_td->select('no_order')->whereBetween('datang', [$start_date.' 00:00:00.000', $end_date.' 23:59:59.999'])->get();
        // if ( $no_order->count() > 0 ) {
        //     $no_order = $no_order->toArray();

        //     $m_od = new \Model\Storage\OrderDoc_model();
        //     $d_od = $m_od->select('noreg')->whereIn('no_order', $no_order)->get();

        //     if ( $d_od->count() > 0 ) {
        //         $d_od = $d_od->toArray();

        //         foreach ($d_od as $k_od => $v_od) {
        //             array_push($noreg_rs, $v_od['noreg']);
        //         }
        //     }
        // }

        // $noreg_d_rs = $m_rs->select('noreg')->whereBetween('tgl_docin', [$start_date, $end_date])->get();
        // if ( $noreg_d_rs->count() > 0 ) {
        //     $noreg_d_rs = $noreg_d_rs->toArray();

        //     foreach ($noreg_d_rs as $k_rs => $v_rs) {
        //         array_push($noreg_rs, $v_rs['noreg']);
        //     }
        // }

        // $d_rs = null;
        // if ( count($noreg_rs) > 0 ) {
        //     if ( $filter == 1 ) {
        //         $noreg = $m_ts->select('noreg')->whereIn('noreg', $noreg_rs)->get()->toArray();

        //         if ( count($noreg) > 0 ) {
        //             $d_rs = $m_rs->whereNotIn('noreg', $noreg)->whereBetween('tgl_docin', [$start_date, $end_date])->with(['mitra'])->get()->toArray();
        //         } else {
        //             $d_rs = $m_rs->whereIn('noreg', $noreg_rs)->with(['mitra'])->get()->toArray();
        //         }
        //     } else {
        //         if ( $filter == 0 ) {
        //             $d_rs = $m_rs->whereIn('noreg', $noreg_rs)->with(['mitra'])->get()->toArray();
        //         } else {
        //             $noreg = $m_ts->select('noreg')->whereIn('noreg', $noreg_rs)->get();

        //             if ( $noreg->count() > 0 ) {
        //                 $noreg = $noreg->toArray();
        //                 $d_rs = $m_rs->whereIn('noreg', $noreg)->with(['mitra'])->get()->toArray();
        //             }
        //         }
        //     }
        // }

        // $data = $this->mapping_data_ts( $d_rs );

        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['data'] = $data;
        $html = $this->load->view('transaksi/tsdrhpp/list', $content, TRUE);

        echo $html;
    }

    public function mapping_data_ts($params)
    {
        $data = null;
        if ( !empty($params) ) {
            foreach ($params as $k => $val) {
                $m_konfir = new \Model\Storage\Konfir_model();
                $d_konfir = $m_konfir->where('noreg', $val['noreg'])->orderBy('tgl_panen', 'asc')->first();

                $m_od = new \Model\Storage\OrderDoc_model();
                $d_od = $m_od->where('noreg', $val['noreg'])->orderBy('id', 'desc')->first();

                $tgl_docin = !empty($d_od) ? $d_od->rencanaa_tiba : null;
                if ( $d_od ) {
                    $d_od = $d_od->toArray();

                    $m_td = new \Model\Storage\TerimaDoc_model();
                    $d_td = $m_td->where('no_order', $d_od['no_order'])->orderBy('id', 'desc')->first();

                    $tgl_docin = !empty($d_td) ? $d_td->datang : null;
                }

                $key = $val['mitra']['d_mitra']['nama'].' - '.str_replace('-', '', $val['tgl_docin']).' - '.$val['noreg'];

                $data[$key]['mitra'] = $val['mitra']['d_mitra']['nama'];
                $data[$key]['noreg'] = $val['noreg'];
                $data[$key]['kandang'] = (int) substr($val['noreg'], -2);
                $data[$key]['populasi'] = $val['populasi'];
                $data[$key]['tgl_docin_rdim'] = $val['tgl_docin'];
                $data[$key]['tgl_docin_real'] = $tgl_docin;
                $data[$key]['tgl_panen'] = !empty($d_konfir) ? $d_konfir->tgl_panen : null;
                $data[$key]['tutup_siklus'] = 0;
                $data[$key]['logs'] = null;

                $m_ts = new \Model\Storage\TutupSiklus_model();
                $d_ts = $m_ts->where('noreg', $val['noreg'])->first();

                if ( $d_ts ) {
                    $d_ts = $d_ts->toArray();

                    $m_lt = new \Model\Storage\LogTables_model();
                    $d_lt = $m_lt->select('deskripsi', 'waktu')->where('tbl_name', 'tutup_siklus')->where('tbl_id', $d_ts['id'])->orderBy('id', 'asc')->get()->toArray();

                    $data[$key]['tutup_siklus'] = 1;
                    $data[$key]['logs'] = $d_lt;
                }

                ksort($data);
            }
        }

        return $data;
    }

    public function load_form()
    {
        $noreg = $this->input->get('noreg');
        $id = $this->input->get('id');
        $content = array();
        $html = "<h3>RHPP</h3>";

        if ( !empty($noreg) && !is_numeric($id) ) {
            // NOTE: view data BASTTB (ajax)
            $html = $this->view($noreg);
        } else if ( !empty($noreg) && is_numeric($id) ) {
            $html = $this->edit_form($noreg);
        };

        echo $html;
    }

    public function view($_noreg)
    {
        $data_rhpp_plasma = null;
        $data_rhpp_int = null;
        $data = null;

        $m_ts = new \Model\Storage\TutupSiklus_model();
        $d_ts = $m_ts->where('noreg', $_noreg)->with(['potongan_pajak'])->first();

        $id_tutup_siklus = null; $mitra = null; $noreg = null; $populasi = null; $kandang = null; $tgl_docin = null; $tutup_siklus = null; $biaya_materai = null; $potongan_pajak = null; $tgl_tutup = null; $rata_umur_panen = null; $biaya_opr = null;

        $data_potongan_pajak = $this->get_data_potongan_pajak();

        $data_doc_plasma = null; $data_pakan_plasma = null; $data_pindah_pakan_plasma = null; $data_retur_pakan_plasma = null; $data_voadip_plasma = null; $data_retur_voadip_plasma = null; $data_rpah_plasma = null; $data_piutang_plasma = null;
        $data_doc_inti = null; $data_pakan_inti = null; $data_pindah_pakan_inti = null; $data_oa_pakan_inti = null; $data_retur_pakan_inti = null; $data_oa_retur_pakan_inti = null; $data_voadip_inti = null; $data_retur_voadip_inti = null; $data_rpah_inti = null;

        $cn = null;

        $bonus_pasar = 0; $bonus_kematian = 0; $nilai_bonus_kematian = 0; $fcr = 0; $bb = 0; $deplesi = 0; $ip = 0;
        $bonus_insentif_fcr = 0;

        $sk = $this->get_harga_kontrak( $_noreg );
        $selisih_pakan = $sk['selisih_pakan'];

        $data_potongan = null;
        $data_bonus = null;

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('noreg', $_noreg)->with(['mitra'])->first()->toArray();

        $jenis_mitra = $d_rs['mitra']['d_mitra']['jenis'];

        if ( $d_ts ) {
            $m_rhpp = new \Model\Storage\Rhpp_model();
            $d_rhpp_inti = $m_rhpp->where('noreg', $_noreg)->where('jenis', 'rhpp_inti')->with(['doc', 'pakan', 'oa_pakan', 'pindah_pakan', 'oa_pindah_pakan', 'retur_pakan', 'oa_retur_pakan', 'voadip', 'retur_voadip', 'penjualan', 'potongan', 'bonus'])->orderBy('id', 'desc')->first();
            $d_rhpp_plasma = $m_rhpp->where('noreg', $_noreg)->where('jenis', 'rhpp_plasma')->with(['doc', 'pakan', 'oa_pakan', 'pindah_pakan', 'oa_pindah_pakan', 'retur_pakan', 'oa_retur_pakan', 'voadip', 'retur_voadip', 'penjualan', 'potongan', 'bonus', 'piutang'])->orderBy('id', 'desc')->first();

            $d_rhpp_inti = !empty($d_rhpp_inti) ? $d_rhpp_inti->toArray() : null;
            $d_rhpp_plasma = !empty($d_rhpp_plasma) ? $d_rhpp_plasma->toArray() : null;

            $id_tutup_siklus = $d_rhpp_inti['id_ts'];
            $mitra = $d_rhpp_inti['mitra'];
            $noreg = $d_rhpp_inti['noreg'];
            $populasi = $d_rhpp_inti['populasi'];
            $kandang = $d_rhpp_inti['kandang'];
            $tgl_docin = $d_rhpp_inti['tgl_docin'];
            $tutup_siklus = 1;
            $biaya_materai = $d_rhpp_inti['biaya_materai'];
            $potongan_pajak = $d_rhpp_inti['prs_potongan_pajak'];
            $tgl_tutup = $d_ts->tgl_tutup;
            $rata_umur_panen = $d_rhpp_inti['rata_umur'];
            $biaya_opr = $d_rhpp_inti['biaya_operasional'];
            $bonus_insentif_fcr = $d_rhpp_plasma['bonus_insentif_fcr'];
            $populasi_bonus_insentif_listrik = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['populasi_bonus_insentif_listrik'] : 0;
            $bonus_insentif_listrik = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['bonus_insentif_listrik'] : 0;
            $total_bonus_insentif_listrik = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['total_bonus_insentif_listrik'] : 0;

            if ( !empty($d_rhpp_plasma) ) {
                $data_doc_plasma['doc'] = array(
                    'tgl_docin' => $d_rhpp_plasma['doc']['tanggal'],
                    'sj' => $d_rhpp_plasma['doc']['nota'],
                    'barang' => $d_rhpp_plasma['doc']['barang'],
                    'box' => $d_rhpp_plasma['doc']['box'],
                    'jumlah' => $d_rhpp_plasma['doc']['jumlah'],
                    'harga' => $d_rhpp_plasma['doc']['harga'],
                    'total' => $d_rhpp_plasma['doc']['total']
                );
                $data_doc_plasma['vaksin'] = array(
                    'barang' => $d_rhpp_plasma['doc']['vaksin'],
                    'harga' => $d_rhpp_plasma['doc']['harga_vaksin'],
                    'total' => $d_rhpp_plasma['doc']['total_vaksin']
                );
                $data_pakan_plasma = null;
                foreach ($d_rhpp_plasma['pakan'] as $k_pakan => $v_pakan) {
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
                $data_pindah_pakan_plasma = $d_rhpp_plasma['pindah_pakan'];
                $data_retur_pakan_plasma = $d_rhpp_plasma['retur_pakan'];
                $data_voadip_plasma = null;
                foreach ($d_rhpp_plasma['voadip'] as $k_voadip => $v_voadip) {
                    $m_brg = new \Model\Storage\Barang_model();
                    $d_brg = $m_brg->where('nama', 'like', '%'.$v_voadip['barang'].'%')->orderBy('id', 'desc')->first();

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
                $data_retur_voadip_plasma = null;
                foreach ($d_rhpp_plasma['retur_voadip'] as $k_rvoadip => $v_rvoadip) {
                    $m_brg = new \Model\Storage\Barang_model();
                    $d_brg = $m_brg->where('nama', 'like', '%'.$v_rvoadip['barang'].'%')->orderBy('id', 'desc')->first();

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
                $data_rpah_plasma = null;
                foreach ($d_rhpp_plasma['penjualan'] as $k_penjualan => $v_penjualan) {
                    // $key = $v_penjualan['tanggal'].' | '.$v_penjualan['nota'].' | '.$v_penjualan['bb'];
                    $data_rpah_plasma[] = array(
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

                $data_potongan = null;
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
                
                $data_bonus = null;
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
            
            $data_doc_inti['doc'] = array(
                'tgl_docin' => $d_rhpp_inti['doc']['tanggal'],
                'sj' => $d_rhpp_inti['doc']['nota'],
                'barang' => $d_rhpp_inti['doc']['barang'],
                'box' => $d_rhpp_inti['doc']['box'],
                'jumlah' => $d_rhpp_inti['doc']['jumlah'],
                'harga' => $d_rhpp_inti['doc']['harga'],
                'total' => $d_rhpp_inti['doc']['total']
            );
            $data_pakan_inti = null;
            foreach ($d_rhpp_inti['pakan'] as $k_pakan => $v_pakan) {
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

            // $_data_oa_pakan_inti = $this->get_data_oa_pakan( $d_rhpp_inti['noreg'] );
            foreach ($d_rhpp_inti['oa_pakan'] as $k_oa_pakan => $v_oa_pakan) {
                $key = str_replace('-', '', $v_oa_pakan['tanggal']).' | '.$v_oa_pakan['nota'].' | '.$v_oa_pakan['barang'].' | '.$v_oa_pakan['id'];

                $data_oa_pakan_inti[ $v_oa_pakan['tanggal'] ][ $v_oa_pakan['nopol'] ][ $key ] = $v_oa_pakan;
            }
            $data_oa_pakan_inti = empty($data_oa_pakan_inti) ? $_data_oa_pakan_inti['ongkos_angkut'] : $data_oa_pakan_inti;

            $data_pindah_pakan_inti = $d_rhpp_inti['pindah_pakan'];
            $data_oa_pindah_pakan_inti = null;
            // $_data_oa_pindah_pakan_inti = $this->get_data_oa_pindah_pakan( $d_rhpp_inti['noreg'] );
            foreach ($d_rhpp_inti['oa_pindah_pakan'] as $k_oa_pindah_pakan => $v_oa_pindah_pakan) {
                $key = str_replace('-', '', $v_oa_pindah_pakan['tanggal']).' | '.$v_oa_pindah_pakan['nota'].' | '.$v_oa_pindah_pakan['barang'].' | '.$v_oa_pindah_pakan['id'];

                $data_oa_pindah_pakan_inti[ $v_oa_pindah_pakan['tanggal'] ][ $v_oa_pindah_pakan['nopol'] ][ $key ] = $v_oa_pindah_pakan;
            }
            // $data_oa_pindah_pakan_inti = empty($data_oa_pindah_pakan_inti) ? $_data_oa_pindah_pakan_inti['ongkos_angkut'] : $data_oa_pindah_pakan_inti;

            $data_retur_pakan_inti = $d_rhpp_inti['retur_pakan'];
            $data_oa_retur_pakan_inti = null;
            foreach ($d_rhpp_inti['oa_retur_pakan'] as $k_oa_retur_pakan => $v_oa_retur_pakan) {
                $key = str_replace('-', '', $v_oa_retur_pakan['tanggal']).' | '.$v_oa_retur_pakan['nota'].' | '.$v_oa_retur_pakan['barang'].' | '.$v_oa_retur_pakan['id'];

                $data_oa_retur_pakan_inti[ $v_oa_retur_pakan['tanggal'] ][ $v_oa_retur_pakan['nopol'] ][ $key ] = $v_oa_retur_pakan;
            }
            $data_voadip_inti = null;
            foreach ($d_rhpp_inti['voadip'] as $k_voadip => $v_voadip) {
                $m_brg = new \Model\Storage\Barang_model();
                $d_brg = $m_brg->where('nama', 'like', '%'.$v_voadip['barang'].'%')->orderBy('id', 'desc')->first();

                $data_voadip_inti[] = array(
                    'tanggal' => $v_voadip['tanggal'],
                    'sj' => $v_voadip['nota'],
                    'barang' => $v_voadip['barang'],
                    'jumlah' => $v_voadip['jumlah'],
                    'harga' => $v_voadip['harga'],
                    'total' => $v_voadip['total'],
                    'decimal' => !empty($d_brg) ? $d_brg->desimal_harga : 2
                );
            }
            $data_retur_voadip_inti = null;
            foreach ($d_rhpp_inti['retur_voadip'] as $k_rvoadip => $v_rvoadip) {
                $m_brg = new \Model\Storage\Barang_model();
                $d_brg = $m_brg->where('nama', 'like', '%'.$v_rvoadip['barang'].'%')->orderBy('id', 'desc')->first();

                $data_retur_voadip_inti[] = array(
                    'tanggal' => $v_rvoadip['tanggal'],
                    'no_retur' => $v_rvoadip['nota'],
                    'barang' => $v_rvoadip['barang'],
                    'jumlah' => $v_rvoadip['jumlah'],
                    'harga' => $v_rvoadip['harga'],
                    'total' => $v_rvoadip['total'],
                    'decimal' => !empty($d_brg) ? $d_brg->desimal_harga : 2
                );
            }
            $data_rpah_inti = null;
            foreach ($d_rhpp_inti['penjualan'] as $k_penjualan => $v_penjualan) {
                // $key = $v_penjualan['tanggal'].' | '.$v_penjualan['nota'].' | '.$v_penjualan['bb'];
                $data_rpah_inti[] = array(
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

            $bonus_pasar = $d_rhpp_plasma['persen_bonus_pasar'];
            $bonus_kematian = $d_rhpp_plasma['bonus_kematian'];
            $fcr = $d_rhpp_inti['fcr'];
            $bb = $d_rhpp_inti['bb'];
            $deplesi = $d_rhpp_inti['deplesi'];
            $ip = $d_rhpp_inti['ip'];
            $cn = $d_rhpp_inti['cn'];
        } else {
            // $m_pp = new \Model\Storage\PenjualanPeralatan_model();
            // $d_pp = $m_pp->where('mitra', $d_rs['mitra']['nomor'])->where('status', 'BELUM')->get();

            // if ( $d_pp->count() > 0 ) {
            //     $d_pp = $d_pp->toArray();

            //     $m_sm = new \Model\Storage\SaldoMitra_model();
            //     $d_sm = $m_sm->where('no_mitra', $d_rs['mitra']['nomor'])->orderBy('id', 'desc')->first();

            //     foreach ($d_pp as $k_pp => $v_pp) {
            //         $m_bpp = new \Model\Storage\BayarPenjualanPeralatan_model();
            //         $d_bpp = $m_bpp->where('id_penjualan_peralatan', $v_pp['id'])->get();

            //         $sudah_bayar = 0;
            //         if ( $d_bpp->count() > 0 ) {
            //             foreach ($d_bpp as $k_bpp => $v_bpp) {
            //                 $sudah_bayar += $v_bpp['saldo'] + $v_bpp['bayar'];
            //             }
            //         }

            //         $data_potongan[ $v_pp['id'] ] = array(
            //             'id_jual' => $v_pp['id'],
            //             'tanggal' => $v_pp['tanggal'],
            //             'tagihan' => $v_pp['total'],
            //             'sudah_bayar' => $sudah_bayar,
            //             'sisa_bayar' => $v_pp['total'] - $sudah_bayar,
            //             'saldo' => (isset($d_sm->saldo) && $d_sm->saldo > 0) ? $d_sm->saldo : 0
            //         );
            //     }
            // }

            $m_bo = new \Model\Storage\BiayaOperasional_model();
            $d_bo = $m_bo->where('tgl_berlaku', '<=', date('Y-m-d'))->orderBy('tgl_berlaku', 'desc')->first();

            $biaya_opr = 0;
            if ( $d_bo && $jenis_mitra == 'ME' ) {
                $biaya_opr = $d_bo->biaya_opr;
            } else {
                $m_dj = new \Model\Storage\DetJurnal_model();
                $biaya_opr = $m_dj->where('noreg', $_noreg)->sum('nominal');
            }

            $data_piutang_plasma = $this->get_data_piutang( $d_rs['mitra']['d_mitra']['nomor'] );
            
            $mitra = $d_rs['mitra']['d_mitra']['nama'];
            $noreg = $_noreg;
            $kandang = (int) substr($noreg, -2);
            
            $bonus_insentif_listrik = $sk['bonus_insentif_listrik'];
            $total_bonus_insentif_listrik = 0;
            
            $get_data_doc = $this->get_data_doc( $_noreg );
            $tgl_docin = $get_data_doc['plasma']['doc']['tgl_docin'];
            $populasi = $get_data_doc['plasma']['doc']['jumlah'];
            $populasi_bonus_insentif_listrik = $populasi;
            
            $get_data_pakan = $this->get_data_pakan( $_noreg );
            $get_data_pindah_pakan = $this->get_data_pindah_pakan( $_noreg, $get_data_pakan );
            // cetak_r( $get_data_pindah_pakan, 1 );
            $get_data_retur_pakan = $this->get_data_retur_pakan( $_noreg );
            
            $get_data_voadip = $this->get_data_voadip( $_noreg );
            // $get_data_retur_voadip = $this->get_data_retur_voadip( $_noreg );
            $get_data_retur_voadip = $this->get_data_retur_voadip( $_noreg, $get_data_voadip );
            // cetak_r( $get_data_retur_voadip, 1 );

            if ( $jenis_mitra == 'ME' ) {
                $data_doc_plasma = isset($get_data_doc['plasma']) ? $get_data_doc['plasma'] : null;
                $data_pakan_plasma = $get_data_pakan['plasma'];
                $data_pindah_pakan_plasma = $get_data_pindah_pakan['plasma'];
                $data_retur_pakan_plasma = $get_data_retur_pakan['plasma'];
                $data_voadip_plasma = $get_data_voadip['plasma'];
                $data_retur_voadip_plasma = $get_data_retur_voadip['plasma'];
            }

            $data_doc_inti = isset($get_data_doc['inti']) ? $get_data_doc['inti'] : null;
            $data_pakan_inti = $get_data_pakan['inti'];
            $data_oa_pakan_inti = isset($get_data_pakan['ongkos_angkut']) ? $get_data_pakan['ongkos_angkut'] : null;
            $data_pindah_pakan_inti = $get_data_pindah_pakan['inti'];
            $data_oa_pindah_pakan_inti = isset($get_data_pindah_pakan['ongkos_angkut']) ? $get_data_pindah_pakan['ongkos_angkut'] : null;;
            $data_retur_pakan_inti = $get_data_retur_pakan['inti'];
            $data_oa_retur_pakan_inti = isset($get_data_retur_pakan['ongkos_angkut']) ? $get_data_retur_pakan['ongkos_angkut'] : null;
            $data_voadip_inti = $get_data_voadip['inti'];
            $data_retur_voadip_inti = $get_data_retur_voadip['inti'];

            $total_jumlah_pakan = 0;
            if ( !empty($data_pakan_inti) ) {
                foreach ($data_pakan_inti as $k_dpi => $v_dpi) {
                    $total_jumlah_pakan += $v_dpi['jumlah'];
                }
            }
            if ( !empty($data_pindah_pakan_inti) ) {
                foreach ($data_pindah_pakan_inti as $k_dppi => $v_dppi) {
                    $total_jumlah_pakan -= $v_dppi['jumlah'];
                }
            }
            if ( !empty($data_retur_pakan_inti) ) {
                foreach ($data_retur_pakan_inti as $k_drpi => $v_drpi) {
                    $total_jumlah_pakan -= $v_drpi['jumlah'];
                }
            }

            $data_potongan_pajak = $this->get_data_potongan_pajak();

            $tgl_docin = isset($data_doc_inti['doc']['tgl_docin']) ? substr($data_doc_inti['doc']['tgl_docin'], 0, 10) : null;

            $id_tutup_siklus = null;
            $tutup_siklus = 0;
            $biaya_materai = 0;
            $potongan_pajak = 0;
            $tgl_tutup = null;
            $rata_umur_panen = 0;
            $total_ekor_sj = 0;
            $total_tonase_sj = 0;

            $m_ts = new \Model\Storage\TutupSiklus_model();
            $d_ts = $m_ts->where('noreg', $_noreg)->with(['potongan_pajak'])->first();

            if ( $d_ts ) {
                $id_tutup_siklus = $d_ts->id;

                $tutup_siklus = 1;
                $biaya_materai = $d_ts->biaya_materai;
                $potongan_pajak = !empty($d_ts->potongan_pajak) ? $d_ts->potongan_pajak->prs_potongan : 0;
                $tgl_tutup = $d_ts->tgl_tutup;
            }

            $data_rpah = $this->get_data_rpah( $_noreg, $populasi, $total_jumlah_pakan, $tgl_docin );
            $data_rpah_inti = $data_rpah['data'];
            if ( $jenis_mitra == 'ME' ) {
                $data_rpah_plasma = $data_rpah['data'];
            }

            $bonus_pasar = $data_rpah['bonus_pasar'];
            $nilai_bonus_kematian = $data_rpah['bonus_kematian'];
            $fcr = $data_rpah['fcr'];
            $bb = $data_rpah['bb'];
            $deplesi = $data_rpah['deplesi'];
            $ip = $data_rpah['ip'];
            $rata_umur_panen = $data_rpah['rata_umur_panen'];
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
            'data_piutang_plasma' => $data_piutang_plasma
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

        $data = array(
            'id' => $id_tutup_siklus,
            'mitra' => $mitra,
            'jenis_mitra' => $jenis_mitra,
            'noreg' => $noreg,
            'populasi' => $populasi,
            'kandang' => $kandang,
            'tgl_docin' => $tgl_docin,
            'tutup_siklus' => $tutup_siklus,
            'biaya_materai' => $biaya_materai,
            'potongan_pajak' => $potongan_pajak,
            'tgl_tutup' => $tgl_tutup,
            'rata_umur_panen' => $rata_umur_panen,
            'data_potongan_pajak' => $data_potongan_pajak,
            'populasi_bonus_insentif_listrik' => $populasi_bonus_insentif_listrik,
            'bonus_insentif_listrik' => $bonus_insentif_listrik,
            'total_bonus_insentif_listrik' => $total_bonus_insentif_listrik,
            'bonus_insentif_fcr' => $bonus_insentif_fcr,
            'selisih_pakan' => $selisih_pakan,
            'biaya_opr' => $biaya_opr,
            'bonus_pasar' => $bonus_pasar,
            'nilai_bonus_kematian' => $nilai_bonus_kematian,
            'bonus_kematian' => $bonus_kematian,
            'fcr' => $fcr,
            'bb' => $bb,
            'deplesi' => $deplesi,
            'ip' => $ip,
            'cn' => $cn
        );

        $akses = hakAkses($this->url);

        $form_rhpp_inti = 0;
        if ( !empty($akses['a_khusus']) && in_array('rhpp_inti', $akses['a_khusus']) ) {
            $form_rhpp_inti = 1;
        }

        $content['form_rhpp_inti'] = $form_rhpp_inti;
        $content['data'] = $data;
        $content['data_plasma'] = $data_rhpp_plasma;
        $content['data_inti'] = $data_rhpp_inti;
        $html = $this->load->view('transaksi/tsdrhpp/view_rhpp', $content, TRUE);

        return $html;
    }

    public function edit_form($_noreg)
    {
        $data_rhpp_plasma = null;
        $data_rhpp_int = null;
        $data = null;

        $m_ts = new \Model\Storage\TutupSiklus_model();
        $d_ts = $m_ts->where('noreg', $_noreg)->with(['potongan_pajak'])->first();

        $id_tutup_siklus = null; $mitra = null; $noreg = null; $populasi = null; $kandang = null; $tgl_docin = null; $tutup_siklus = null; $biaya_materai = null; $potongan_pajak = null; $tgl_tutup = null; $rata_umur_panen = null; $biaya_opr = null;

        $data_potongan_pajak = $this->get_data_potongan_pajak();

        $data_doc_plasma = null; $data_pakan_plasma = null; $data_pindah_pakan_plasma = null; $data_retur_pakan_plasma = null; $data_voadip_plasma = null; $data_retur_voadip_plasma = null; $data_rpah_plasma = null;
        $data_doc_inti = null; $data_pakan_inti = null; $data_pindah_pakan_inti = null; $data_oa_pakan_inti = null; $data_retur_pakan_inti = null; $data_oa_retur_pakan_inti = null; $data_voadip_inti = null; $data_retur_voadip_inti = null; $data_rpah_inti = null;

        $bonus_pasar = 0; $bonus_kematian = 0; $nilai_bonus_kematian = 0; $fcr = 0; $bb = 0; $deplesi = 0; $ip = 0;
        $bonus_insentif_fcr = 0;

        $sk = $this->get_harga_kontrak( $_noreg );
        $selisih_pakan = $sk['selisih_pakan'];

        $data_potongan = null;
        $data_bonus = null;

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('noreg', $_noreg)->with(['mitra'])->first()->toArray();

        $jenis_mitra = $d_rs['mitra']['d_mitra']['jenis'];

        $m_rhpp = new \Model\Storage\Rhpp_model();
        $d_rhpp_inti = $m_rhpp->where('noreg', $_noreg)->where('jenis', 'rhpp_inti')->with(['doc', 'pakan', 'oa_pakan', 'pindah_pakan', 'oa_pindah_pakan', 'retur_pakan', 'oa_retur_pakan', 'voadip', 'retur_voadip', 'penjualan', 'potongan', 'bonus'])->orderBy('id', 'desc')->first();
        $d_rhpp_plasma = $m_rhpp->where('noreg', $_noreg)->where('jenis', 'rhpp_plasma')->with(['doc', 'pakan', 'oa_pakan', 'pindah_pakan', 'oa_pindah_pakan', 'retur_pakan', 'oa_retur_pakan', 'voadip', 'retur_voadip', 'penjualan', 'potongan', 'bonus'])->orderBy('id', 'desc')->first();

        $d_rhpp_inti = !empty($d_rhpp_inti) ? $d_rhpp_inti->toArray() : null;
        $d_rhpp_plasma = !empty($d_rhpp_plasma) ? $d_rhpp_plasma->toArray() : null;

        $id_tutup_siklus = $d_rhpp_inti['id_ts'];
        $mitra = $d_rhpp_inti['mitra'];
        $noreg = $d_rhpp_inti['noreg'];
        $populasi = $d_rhpp_inti['populasi'];
        $kandang = $d_rhpp_inti['kandang'];
        $tgl_docin = $d_rhpp_inti['tgl_docin'];
        $tutup_siklus = 1;
        $biaya_materai = $d_rhpp_inti['biaya_materai'];
        $potongan_pajak = $d_rhpp_inti['prs_potongan_pajak'];
        $tgl_tutup = $d_ts->tgl_tutup;
        $rata_umur_panen = $d_rhpp_inti['rata_umur'];
        $biaya_opr = $d_rhpp_inti['biaya_operasional'];
        $bonus_insentif_fcr = $d_rhpp_plasma['bonus_insentif_fcr'];
        $populasi_bonus_insentif_listrik = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['populasi_bonus_insentif_listrik'] : 0;
        $bonus_insentif_listrik = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['bonus_insentif_listrik'] : 0;
        $total_bonus_insentif_listrik = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['total_bonus_insentif_listrik'] : 0;

        if ( !empty($d_rhpp_plasma) ) {
            $data_doc_plasma['doc'] = array(
                'tgl_docin' => $d_rhpp_plasma['doc']['tanggal'],
                'sj' => $d_rhpp_plasma['doc']['nota'],
                'barang' => $d_rhpp_plasma['doc']['barang'],
                'box' => $d_rhpp_plasma['doc']['box'],
                'jumlah' => $d_rhpp_plasma['doc']['jumlah'],
                'harga' => $d_rhpp_plasma['doc']['harga'],
                'total' => $d_rhpp_plasma['doc']['total']
            );
            $data_doc_plasma['vaksin'] = array(
                'barang' => $d_rhpp_plasma['doc']['vaksin'],
                'harga' => $d_rhpp_plasma['doc']['harga_vaksin'],
                'total' => $d_rhpp_plasma['doc']['total_vaksin']
            );
            $data_pakan_plasma = null;
            foreach ($d_rhpp_plasma['pakan'] as $k_pakan => $v_pakan) {
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
            $data_pindah_pakan_plasma = $d_rhpp_plasma['pindah_pakan'];
            $data_retur_pakan_plasma = $d_rhpp_plasma['retur_pakan'];
            $data_voadip_plasma = null;
            foreach ($d_rhpp_plasma['voadip'] as $k_voadip => $v_voadip) {
                $m_brg = new \Model\Storage\Barang_model();
                $d_brg = $m_brg->where('nama', 'like', '%'.$v_voadip['barang'].'%')->orderBy('id', 'desc')->first();

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
            $data_retur_voadip_plasma = null;
            foreach ($d_rhpp_plasma['retur_voadip'] as $k_rvoadip => $v_rvoadip) {
                $m_brg = new \Model\Storage\Barang_model();
                $d_brg = $m_brg->where('nama', 'like', '%'.$v_rvoadip['barang'].'%')->orderBy('id', 'desc')->first();

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
            $data_rpah_plasma = null;
            foreach ($d_rhpp_plasma['penjualan'] as $k_penjualan => $v_penjualan) {
                // $key = $v_penjualan['tanggal'].' | '.$v_penjualan['nota'].' | '.$v_penjualan['bb'];
                $data_rpah_plasma[] = array(
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

            $data_potongan = null;
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
            
            $data_bonus = null;
            foreach ($d_rhpp_plasma['bonus'] as $k_bonus => $v_bonus) {
                $data_bonus[ $v_bonus['id'] ] = array(
                    'id_trans' => $v_bonus['id_trans'],
                    'keterangan' => $v_bonus['keterangan'],
                    'jumlah' => $v_bonus['jumlah'],
                );
            }
        }
        
        $data_doc_inti['doc'] = array(
            'tgl_docin' => $d_rhpp_inti['doc']['tanggal'],
            'sj' => $d_rhpp_inti['doc']['nota'],
            'barang' => $d_rhpp_inti['doc']['barang'],
            'box' => $d_rhpp_inti['doc']['box'],
            'jumlah' => $d_rhpp_inti['doc']['jumlah'],
            'harga' => $d_rhpp_inti['doc']['harga'],
            'total' => $d_rhpp_inti['doc']['total']
        );
        $data_pakan_inti = null;
        foreach ($d_rhpp_inti['pakan'] as $k_pakan => $v_pakan) {
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

        // $_data_oa_pakan_inti = $this->get_data_oa_pakan( $d_rhpp_inti['noreg'] );
        foreach ($d_rhpp_inti['oa_pakan'] as $k_oa_pakan => $v_oa_pakan) {
            $key = str_replace('-', '', $v_oa_pakan['tanggal']).' | '.$v_oa_pakan['nota'].' | '.$v_oa_pakan['barang'].' | '.$v_oa_pakan['id'];

            $data_oa_pakan_inti[ $v_oa_pakan['tanggal'] ][ $v_oa_pakan['nopol'] ][ $key ] = $v_oa_pakan;
        }
        $data_oa_pakan_inti = empty($data_oa_pakan_inti) ? $_data_oa_pakan_inti['ongkos_angkut'] : $data_oa_pakan_inti;

        $data_pindah_pakan_inti = $d_rhpp_inti['pindah_pakan'];
        $data_oa_pindah_pakan_inti = null;
        // $_data_oa_pindah_pakan_inti = $this->get_data_oa_pindah_pakan( $d_rhpp_inti['noreg'] );
        foreach ($d_rhpp_inti['oa_pindah_pakan'] as $k_oa_pindah_pakan => $v_oa_pindah_pakan) {
            $key = str_replace('-', '', $v_oa_pindah_pakan['tanggal']).' | '.$v_oa_pindah_pakan['nota'].' | '.$v_oa_pindah_pakan['barang'].' | '.$v_oa_pindah_pakan['id'];

            $data_oa_pindah_pakan_inti[ $v_oa_pindah_pakan['tanggal'] ][ $v_oa_pindah_pakan['nopol'] ][ $key ] = $v_oa_pindah_pakan;
        }
        // $data_oa_pindah_pakan_inti = empty($data_oa_pindah_pakan_inti) ? $_data_oa_pindah_pakan_inti['ongkos_angkut'] : $data_oa_pindah_pakan_inti;

        $data_retur_pakan_inti = $d_rhpp_inti['retur_pakan'];
        $data_oa_retur_pakan_inti = null;
        foreach ($d_rhpp_inti['oa_retur_pakan'] as $k_oa_retur_pakan => $v_oa_retur_pakan) {
            $key = str_replace('-', '', $v_oa_retur_pakan['tanggal']).' | '.$v_oa_retur_pakan['nota'].' | '.$v_oa_retur_pakan['barang'].' | '.$v_oa_retur_pakan['id'];

            $data_oa_retur_pakan_inti[ $v_oa_retur_pakan['tanggal'] ][ $v_oa_retur_pakan['nopol'] ][ $key ] = $v_oa_retur_pakan;
        }
        $data_voadip_inti = null;
        foreach ($d_rhpp_inti['voadip'] as $k_voadip => $v_voadip) {
            $m_brg = new \Model\Storage\Barang_model();
            $d_brg = $m_brg->where('nama', 'like', '%'.$v_voadip['barang'].'%')->orderBy('id', 'desc')->first();

            $data_voadip_inti[] = array(
                'tanggal' => $v_voadip['tanggal'],
                'sj' => $v_voadip['nota'],
                'barang' => $v_voadip['barang'],
                'jumlah' => $v_voadip['jumlah'],
                'harga' => $v_voadip['harga'],
                'total' => $v_voadip['total'],
                'decimal' => !empty($d_brg) ? $d_brg->desimal_harga : 2
            );
        }
        $data_retur_voadip_inti = null;
        foreach ($d_rhpp_inti['retur_voadip'] as $k_rvoadip => $v_rvoadip) {
            $m_brg = new \Model\Storage\Barang_model();
            $d_brg = $m_brg->where('nama', 'like', '%'.$v_rvoadip['barang'].'%')->orderBy('id', 'desc')->first();

            $data_retur_voadip_inti[] = array(
                'tanggal' => $v_rvoadip['tanggal'],
                'no_retur' => $v_rvoadip['nota'],
                'barang' => $v_rvoadip['barang'],
                'jumlah' => $v_rvoadip['jumlah'],
                'harga' => $v_rvoadip['harga'],
                'total' => $v_rvoadip['total'],
                'decimal' => !empty($d_brg) ? $d_brg->desimal_harga : 2
            );
        }
        $data_rpah_inti = null;
        foreach ($d_rhpp_inti['penjualan'] as $k_penjualan => $v_penjualan) {
            // $key = $v_penjualan['tanggal'].' | '.$v_penjualan['nota'].' | '.$v_penjualan['bb'];
            $data_rpah_inti[] = array(
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

        $bonus_pasar = $d_rhpp_plasma['persen_bonus_pasar'];
        $bonus_kematian = $d_rhpp_plasma['bonus_kematian'];
        $fcr = $d_rhpp_inti['fcr'];
        $bb = $d_rhpp_inti['bb'];
        $deplesi = $d_rhpp_inti['deplesi'];
        $ip = $d_rhpp_inti['ip'];

        $data_detail_plasma = array(
            'data_doc' => $data_doc_plasma,
            'data_pakan' => $data_pakan_plasma,
            'data_pindah_pakan' => $data_pindah_pakan_plasma,
            'data_retur_pakan' => $data_retur_pakan_plasma,
            'data_voadip' => $data_voadip_plasma,
            'data_retur_voadip' => $data_retur_voadip_plasma,
            'data_rpah' => $data_rpah_plasma,
            'data_potongan' => $data_potongan,
            'data_bonus' => $data_bonus
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

        $data = array(
            'id' => $id_tutup_siklus,
            'mitra' => $mitra,
            'jenis_mitra' => $jenis_mitra,
            'noreg' => $noreg,
            'populasi' => $populasi,
            'kandang' => $kandang,
            'tgl_docin' => $tgl_docin,
            'tutup_siklus' => $tutup_siklus,
            'biaya_materai' => $biaya_materai,
            'potongan_pajak' => $potongan_pajak,
            'tgl_tutup' => $tgl_tutup,
            'rata_umur_panen' => $rata_umur_panen,
            'data_potongan_pajak' => $data_potongan_pajak,
            'populasi_bonus_insentif_listrik' => $populasi_bonus_insentif_listrik,
            'bonus_insentif_listrik' => $bonus_insentif_listrik,
            'total_bonus_insentif_listrik' => $total_bonus_insentif_listrik,
            'bonus_insentif_fcr' => $bonus_insentif_fcr,
            'selisih_pakan' => $selisih_pakan,
            'biaya_opr' => $biaya_opr,
            'bonus_pasar' => $bonus_pasar,
            'nilai_bonus_kematian' => $nilai_bonus_kematian,
            'bonus_kematian' => $bonus_kematian,
            'fcr' => $fcr,
            'bb' => $bb,
            'deplesi' => $deplesi,
            'ip' => $ip,
            'cn' => 0
        );

        $content['data'] = $data;
        $content['data_plasma'] = $data_rhpp_plasma;
        $content['data_inti'] = $data_rhpp_inti;
        $html = $this->load->view('transaksi/tsdrhpp/edit_form', $content, TRUE);

        return $html;
    }

    public function get_harga_kontrak($noreg)
    {
        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('noreg', $noreg)->with(['mitra'])->orderBy('id', 'desc')->first()->toArray();

        $data = null;
        if ( count($d_rs) > 0 ) {
            $m_od = new \Model\Storage\OrderDoc_model();
            $d_od = $m_od->where('noreg', $noreg)->with(['terima_doc'])->orderBy('id', 'desc')->first();

            if ( $d_od ) {
                $d_od = $d_od->toArray();

                $m_pm = new \Model\Storage\PerwakilanMaping_model();
                $d_pm = $m_pm->select('id_hbi')->where('id', $d_rs['format_pb'])->orderBy('id', 'desc')->get();

                if ( $d_pm->count() > 0 ) {
                    $d_pm = $d_pm->toArray();                

                    $m_hbi = new \Model\Storage\HitungBudidayaItem_model();
                    $d_hbi = $m_hbi->select('id_sk')->whereIn('id', $d_pm)->get();

                    if ( $d_hbi->count() > 0 ) {
                        $d_hbi = $d_hbi->toArray();

                        $m_sk = new \Model\Storage\SapronakKesepakatan_model();
                        $__d_sk = $m_sk->whereIn('id', $d_hbi)->first();
                        $_d_sk = $m_sk->where('nomor', $__d_sk->nomor)->orderBy('id', 'desc')->first();
                        // $_d_sk_tgl = $m_sk->where('id', $_d_sk->id)->where('pola', $_d_sk->pola)->where('item_pola', $_d_sk->item_pola)->where('mulai', '<=', $d_od['terima_doc']['datang'])->where('note', 'like', '%'.$_d_sk->note.'%')->orderBy('mulai', 'desc')->first();
                        $_d_sk_tgl = $m_sk->where('id', $_d_sk->id)->where('pola', $_d_sk->pola)->where('item_pola', $_d_sk->item_pola)->where('note', 'like', '%'.$_d_sk->note.'%')->orderBy('mulai', 'desc')->first();

                        // cetak_r( $_d_sk_tgl );

                        $d_sk = null;
                        if ( $_d_sk_tgl ) {
                            // $d_sk = $m_sk->where('pola', $_d_sk->pola)->where('item_pola', $_d_sk->item_pola)->where('mulai', $_d_sk_tgl->mulai)->where('note', 'like', '%'.$_d_sk->note.'%')->orderBy('version', 'desc')->with(['pola_kerjasama','harga_sapronak','harga_performa','harga_sepakat', 'bonus_insentif_listrik', 'selisih_pakan', 'hitung_budidaya_item'])->first();

                            // $d_sk = $m_sk->where('id', $_d_sk->id)->with(['pola_kerjasama','harga_sapronak','harga_performa','harga_sepakat', 'bonus_insentif_listrik', 'selisih_pakan', 'hitung_budidaya_item'])->first();
                            $d_sk = $m_sk->where('id', $_d_sk_tgl->id)->with(['pola_kerjasama','harga_sapronak','harga_performa','harga_sepakat', 'bonus_insentif_listrik', 'selisih_pakan', 'hitung_budidaya_item'])->first();
                        }

                        if ( $d_sk ) {
                            $data = $d_sk->toArray();
                        }
                    }
                }
            }

            return $data;
        }
    }

    public function get_data_doc($noreg)
    {
        $data = null;

        $sapronak_kesepakatan = $this->get_harga_kontrak( $noreg );

        $harga_sapronak = null;
        if ( !empty($sapronak_kesepakatan) ) {
            $harga_sapronak = $sapronak_kesepakatan['harga_sapronak'];
        }

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('noreg', $noreg)->with(['data_vaksin'])->first()->toArray();

        // DOCIN
        $m_od = new \Model\Storage\OrderDoc_model();
        $d_od = $m_od->where('noreg', $noreg)->with(['d_supplier', 'd_barang'])->orderBy('id', 'desc')->first();

        if ( $d_od ) {
            $d_od = $d_od->toArray();

            $m_td = new \Model\Storage\TerimaDoc_model();
            $d_td = $m_td->where('no_order', $d_od['no_order'])->orderBy('id', 'desc')->first();

            if ( $d_td ) {
                $d_td = $d_td->toArray();

                $jml_ekor = $d_td['jml_ekor'];
                $jml_box = $d_td['jml_box'];

                $harga_vaksin = $d_rs['data_vaksin']['harga'];
                $total_vaksin = $jml_ekor * $harga_vaksin;

                $harga_kontrak_doc_peternak = 0;
                $harga_kontrak_doc_supplier = 0;
                if ( !empty($harga_sapronak) && count($harga_sapronak) > 0 ) {
                    foreach ($harga_sapronak as $k_hs => $v_hs) {
                        foreach ($v_hs['detail'] as $k_det => $v_det) {
                            if ( $v_det['kode_brg'] == $d_od['item'] ) {
                                $harga_kontrak_doc_peternak = $v_det['hrg_peternak'];
                            }
                        }
                    }
                }
                $harga_kontrak_doc_supplier = $d_od['harga'];

                $total_doc_peternak = $harga_kontrak_doc_peternak * $jml_ekor;
                $total_doc_supplier = $harga_kontrak_doc_supplier * $jml_ekor;

                $data['plasma']['doc'] = array(
                    'tgl_docin' => $d_td['datang'],
                    'sj' => $d_td['no_sj'],
                    'barang' => strtoupper($d_od['d_barang']['nama']) . ' BOX ' . strtoupper($d_od['jns_box']),
                    'box' => $jml_box,
                    'jumlah' => $jml_ekor,
                    'harga' => $harga_kontrak_doc_peternak,
                    'total' => $total_doc_peternak
                );
                $data['inti']['doc'] = array(
                    'tgl_docin' => $d_td['datang'],
                    'sj' => $d_td['no_sj'],
                    'barang' => strtoupper($d_od['d_barang']['nama']) . ' BOX ' . strtoupper($d_od['jns_box']),
                    'box' => $jml_box,
                    'jumlah' => $jml_ekor,
                    'harga' => $harga_kontrak_doc_supplier,
                    'total' => $total_doc_supplier
                );
                $data['plasma']['vaksin'] = array(
                    'barang' => $d_rs['data_vaksin']['nama_vaksin'],
                    'harga' => $harga_vaksin,
                    'total' => $total_vaksin
                );
            }
        }

        return $data;
    }

    public function get_data_pakan($noreg)
    {
        $sapronak_kesepakatan = $this->get_harga_kontrak( $noreg );

        $harga_sapronak = null;
        if ( !empty($sapronak_kesepakatan) ) {
            $harga_sapronak = $sapronak_kesepakatan['harga_sapronak'];
        }

        $m_kp = new \Model\Storage\KirimPakan_model();
        $d_kp = $m_kp->where('tujuan', $noreg)->where('jenis_tujuan', 'peternak')->orderBy('tgl_kirim', 'asc')->with(['detail'])->get();

        $data = null;
        if ( $d_kp->count() > 0 ) {
            $d_kp = $d_kp->toArray();
            foreach ($d_kp as $k_kp => $v_kp) {
                $m_tp = new \Model\Storage\TerimaPakan_model();
                $d_tp = $m_tp->where('id_kirim_pakan', $v_kp['id'])->orderBy('id', 'desc')->first();

                if ( $d_tp ) {
                    $arr_id_det_terima = array(0);
                    $id_det_terima = null;
                    $id_det_terima_old = null;
                    $jml_terima = 0;

                    foreach ($v_kp['detail'] as $k_kpd => $v_kpd) {
                        $harga_kontrak_pakan_peternak = 0;
                        $harga_kontrak_pakan_supplier = 0;
                        if ( count($harga_sapronak) > 0 ) {
                            foreach ($harga_sapronak as $k_hs => $v_hs) {
                                foreach ($v_hs['detail'] as $k_det => $v_det) {
                                    // cetak_r( $v_det['kode_brg'].'|'.$v_kpd['item'].' = '.$v_det['hrg_peternak'] );
                                    if ( trim($v_det['kode_brg']) == trim($v_kpd['item']) ) {
                                        $harga_kontrak_pakan_peternak = $v_det['hrg_peternak'];
                                    }
                                }
                            }
                        }

                        $m_tpd = new \Model\Storage\TerimaPakanDetail_model();
                        $d_tpd = $m_tpd->whereNotIn('id', $arr_id_det_terima)->where('id_header', $d_tp->id)->where('item', $v_kpd['item'])->orderBy('jumlah', 'asc')->orderBy('item', 'asc')->with(['d_barang'])->first();

                        if ( $d_tpd ) {
                            $id_det_terima = $d_tpd->id;

                            $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item;
                            if ( $id_det_terima != $id_det_terima_old ) {
                                $jml_terima = $d_tpd->jumlah;
                            }

                            if ( isset($v_kp['detail'][ $k_kpd+1 ]) ) {
                                $jumlah = ($v_kpd['jumlah'] < $jml_terima) ? $v_kpd['jumlah'] : $jml_terima;
                            } else {
                                $jumlah = $jml_terima;
                            }

                            // $total_peternak = $jumlah * $harga_kontrak_pakan_peternak;
                            // if ( !isset($data['plasma'][ $key ]) ) {
                            //     $data['plasma'][ $key ] = array(
                            //         'tanggal' => $v_kp['tgl_kirim'],
                            //         'sj' => $v_kp['no_sj'],
                            //         'barang' => $d_tpd->d_barang->nama,
                            //         'zak' => ceil($jumlah / 50),
                            //         'jumlah' => $jumlah,
                            //         'harga' => $harga_kontrak_pakan_peternak,
                            //         'total' => $total_peternak
                            //     );
                            // } else {
                            //     $data['plasma'][ $key ]['zak'] += ceil($jumlah / 50);
                            //     $data['plasma'][ $key ]['jumlah'] += $jumlah;
                            //     $data['plasma'][ $key ]['total'] += $total_peternak;
                            // }

                            $total_peternak = $jumlah * $harga_kontrak_pakan_peternak;
                            if ( !isset($data['plasma'][ $key ]) ) {
                                $data['plasma'][ $key ] = array(
                                    'tanggal' => $v_kp['tgl_kirim'],
                                    'sj' => $v_kp['no_sj'],
                                    'barang' => $d_tpd->d_barang->nama,
                                    'zak' => ceil($jumlah / 50),
                                    'jumlah' => $jumlah,
                                    'harga' => $harga_kontrak_pakan_peternak,
                                    'total' => $total_peternak
                                );
                            } else {
                                $data['plasma'][ $key ]['zak'] += ceil($jumlah / 50);
                                $data['plasma'][ $key ]['jumlah'] += $jumlah;
                                $data['plasma'][ $key ]['total'] += $total_peternak;
                            }

                            $m_dst = new \Model\Storage\DetStokTrans_model();
                            $d_dst = $m_dst->where('kode_trans', $v_kp['no_order'])->where('kode_barang', trim($v_kpd['item']))->get();

                            if ( $d_dst->count() > 0 ) {
                                $d_dst = $d_dst->toArray();
                                foreach ($d_dst as $k_dst => $v_dst) {
                                    $m_ds = new \Model\Storage\DetStok_model();
                                    $d_ds = $m_ds->where('id', $v_dst['id_header'])->first();

                                    // $total_peternak = $v_dst['jumlah'] * $harga_kontrak_pakan_peternak;
                                    // if ( !isset($data['plasma'][ $key ]) ) {
                                    //     $data['plasma'][ $key ] = array(
                                    //         'tanggal' => $v_kp['tgl_kirim'],
                                    //         'sj' => $v_kp['no_sj'],
                                    //         'barang' => $d_tpd->d_barang->nama,
                                    //         'zak' => ceil($v_dst['jumlah'] / 50),
                                    //         'jumlah' => $v_dst['jumlah'],
                                    //         'harga' => $harga_kontrak_pakan_peternak,
                                    //         'total' => $total_peternak
                                    //     );
                                    // } else {
                                    //     $data['plasma'][ $key ]['zak'] += ceil($v_dst['jumlah'] / 50);
                                    //     $data['plasma'][ $key ]['jumlah'] += $v_dst['jumlah'];
                                    //     $data['plasma'][ $key ]['total'] += $total_peternak;
                                    // }

                                    if ( !empty($d_ds) ) {
                                        $harga = !empty($d_ds) ? $d_ds->hrg_beli : 0;

                                        $total_supplier = $v_dst['jumlah'] * $harga;

                                        $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$harga;

                                        if ( !isset($data['inti'][ $key ]) ) {
                                            $data['inti'][ $key ] = array(
                                                'tanggal' => $v_kp['tgl_kirim'],
                                                'sj' => $v_kp['no_sj'],
                                                'barang' => $d_tpd->d_barang->nama,
                                                'zak' => ceil($v_dst['jumlah'] / 50),
                                                'jumlah' => $v_dst['jumlah'],
                                                'harga' => $harga,
                                                'total' => $total_supplier
                                            );
                                        } else {
                                            $data['inti'][ $key ]['zak'] += ceil($v_dst['jumlah'] / 50);
                                            $data['inti'][ $key ]['jumlah'] += $v_dst['jumlah'];
                                            $data['inti'][ $key ]['total'] += $total_supplier;
                                        }
                                    }
                                }
                            } else {                                
                                $harga_kontrak_pakan_supplier = 0;
                                $total_supplier = 0;

                                $jml_kirim = $jumlah;

                                if ( $v_kp['jenis_kirim'] == 'opkp' ) {
                                    $pp = 0;
                                    $m_conf = new \Model\Storage\Conf();
                                    $sql = "
                                        select top 1 * from kirim_pakan where no_sj = '".$v_kpd['no_sj_asal']."'
                                    ";
                                    $d_conf_no_sj_asal = $m_conf->hydrateRaw( $sql );

                                    if ( $d_conf_no_sj_asal->count() > 0 ) {
                                        $d_conf_no_sj_asal = $d_conf_no_sj_asal->toArray()[0];

                                        if ( $d_conf_no_sj_asal['jenis_kirim'] == 'opkp' ) {
                                            $pp = 1;
                                        }
                                    }

                                    $m_conf = new \Model\Storage\Conf();
                                    $sql = "EXEC get_data_harga_pakan @no_order = '".$v_kp['no_order']."', @item = '".$v_kpd['item']."', @jumlah = '".$jumlah."', @no_sj_asal = '".$v_kpd['no_sj_asal']."', @pp = ".$pp;

                                    $d_data = $m_conf->hydrateRaw( $sql );

                                    if ( $d_data->count() > 0 ) {
                                        $d_data = $d_data->toArray();

                                        foreach ($d_data as $k_hrgpp => $v_hrgpp) {
                                            if ( $jml_kirim > 0 ) {
                                                $jml_kirim_simpan = ($jml_kirim <= $v_hrgpp['jumlah']) ? $jml_kirim : $v_hrgpp['jumlah'];
                                                $jml_kirim -= $v_hrgpp['jumlah'];
                                                $total_supplier = $v_hrgpp['harga'] * $jml_kirim_simpan;
    
                                                $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$v_hrgpp['harga'];
    
                                                if ( !isset($data['inti'][ $key ]) ) {
                                                    $data['inti'][ $key ] = array(
                                                        'tanggal' => $v_kp['tgl_kirim'],
                                                        'sj' => $v_kp['no_sj'],
                                                        'barang' => $d_tpd->d_barang->nama,
                                                        'zak' => ceil($jml_kirim_simpan / 50),
                                                        'jumlah' => $jml_kirim_simpan,
                                                        'harga' => $v_hrgpp['harga'],
                                                        'total' => $total_supplier
                                                    );
                                                } else {
                                                    $data['inti'][ $key ]['zak'] += ceil($jml_kirim_simpan / 50);
                                                    $data['inti'][ $key ]['jumlah'] += $jml_kirim_simpan;
                                                    $data['inti'][ $key ]['total'] += $total_supplier;
                                                }
                                            }
                                        }
                                    }
                                }

                                // if ( $v_kp['jenis_kirim'] == 'opkp' ) {
                                //     $jml_pindah = ($v_kpd['jumlah'] < $jumlah) ? $v_kpd['jumlah'] : $jumlah;
                                //     // // $jml_pindah = ($d_tpd->jumlah > $v_kpd['jumlah']) ? $v_kpd['jumlah'] : $d_tpd->jumlah;

                                //     $m_conf = new \Model\Storage\Conf();
                                //     $sql = "EXEC get_data_harga_pakan @no_order = '".$v_kp['no_order']."', @item = '".$v_kpd['item']."', @jumlah = '".$d_tpd->jumlah."', @_no_sj_asal = '".$v_kpd['no_sj_asal']."'";

                                //     $d_data = $m_conf->hydrateRaw( $sql );

                                //     if ( $d_data->count() > 0 ) {
                                //         $d_data = $d_data->toArray();

                                //         foreach ($d_data as $k_hrgpp => $v_hrgpp) {
                                //             $total_supplier = $v_hrgpp['harga'] * $v_hrgpp['jumlah'];

                                //             $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$v_hrgpp['harga'];

                                //             if ( !isset($data['inti'][ $key ]) ) {
                                //                 $data['inti'][ $key ] = array(
                                //                     'tanggal' => $v_kp['tgl_kirim'],
                                //                     'sj' => $v_kp['no_sj'],
                                //                     'barang' => $d_tpd->d_barang->nama,
                                //                     'zak' => ceil($v_hrgpp['jumlah'] / 50),
                                //                     'jumlah' => $v_hrgpp['jumlah'],
                                //                     'harga' => $v_hrgpp['harga'],
                                //                     'total' => $total_supplier
                                //                 );
                                //             } else {
                                //                 $data['inti'][ $key ]['zak'] += ceil($v_hrgpp['jumlah'] / 50);
                                //                 $data['inti'][ $key ]['jumlah'] += $v_hrgpp['jumlah'];
                                //                 $data['inti'][ $key ]['total'] += $total_supplier;
                                //             }
                                //         }
                                //     }

                                //     // $m_kp = new \Model\Storage\KirimPakan_model();
                                //     // $sql = "
                                //     //     select 
                                //     //         kp.tgl_kirim,
                                //     //         kp.jenis_kirim,
                                //     //         kp.no_order,
                                //     //         kp.asal,
                                //     //         dkp.item,
                                //     //         dkp.jumlah,
                                //     //         dkp.nilai_beli,
                                //     //         dkp.nilai_jual
                                //     //     from det_kirim_pakan dkp 
                                //     //     left join
                                //     //         kirim_pakan kp 
                                //     //         on
                                //     //             dkp.id_header = kp.id
                                //     //     left join
                                //     //         det_stok_trans dst 
                                //     //         on
                                //     //             dst.kode_trans = kp.no_order
                                //     //     left join
                                //     //         det_stok ds 
                                //     //         on
                                //     //             ds.id = dst.id_header
                                //     //     where
                                //     //         dkp.item = '".$v_kpd['item']."' and
                                //     //         -- kp.tujuan = '".$v_kp['asal']."' and
                                //     //         kp.no_sj = '".$v_kpd['no_sj_asal']."' 
                                //     //         -- and
                                //     //         -- kp.tgl_kirim <= '".$v_kp['tgl_kirim']."'
                                //     //     group by
                                //     //         kp.tgl_kirim,
                                //     //         kp.jenis_kirim,
                                //     //         kp.no_order,
                                //     //         kp.asal,
                                //     //         dkp.item,
                                //     //         dkp.jumlah,
                                //     //         dkp.nilai_beli,
                                //     //         dkp.nilai_jual
                                //     //     order by
                                //     //         kp.tgl_kirim desc,
                                //     //         kp.no_order desc
                                //     // ";

                                //     // $d_kp_pindah = $m_kp->hydrateRaw($sql);
                                //     // if ( $d_kp_pindah->count() > 0 ) {
                                //     //     $d_kp_pindah = $d_kp_pindah->toArray();

                                //     //     $_jml_pindah = $jml_pindah;

                                //     //     foreach ($d_kp_pindah as $k => $val) {
                                //     //         if ( $_jml_pindah > 0 ) {
                                //     //             $asal = $val['asal'];
                                //     //             $tgl_kirim = $val['tgl_kirim'];
                                //     //             $jenis_kirim = $val['jenis_kirim'];

                                //     //             if ( $jenis_kirim == 'opkg' ) {
                                //     //                 $m_dst = new \Model\Storage\DetStokTrans_model();
                                //     //                 $d_dst = $m_dst->where('kode_trans', $val['no_order'])->where('kode_barang', trim($v_kpd['item']))->get();

                                //     //                 if ( $d_dst->count() > 0 ) {
                                //     //                     $d_dst = $d_dst->toArray();
                                //     //                     foreach ($d_dst as $k_dst => $v_dst) {
                                //     //                         $m_ds = new \Model\Storage\DetStok_model();
                                //     //                         $d_ds = $m_ds->where('id', $v_dst['id_header'])->first();
                                //     //                         if ( $_jml_pindah > $v_dst['jumlah'] ) {

                                //     //                             $total_supplier = $d_ds->hrg_beli * $v_dst['jumlah'];

                                //     //                             $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$d_ds->hrg_beli;

                                //     //                             if ( !isset($data['inti'][ $key ]) ) {
                                //     //                                 $data['inti'][ $key ] = array(
                                //     //                                     'tanggal' => $v_kp['tgl_kirim'],
                                //     //                                     'sj' => $v_kp['no_sj'],
                                //     //                                     'barang' => $d_tpd->d_barang->nama,
                                //     //                                     'zak' => ceil($v_dst['jumlah'] / 50),
                                //     //                                     'jumlah' => $v_dst['jumlah'],
                                //     //                                     'harga' => $d_ds->hrg_beli,
                                //     //                                     'total' => $total_supplier
                                //     //                                 );
                                //     //                             } else {
                                //     //                                 $data['inti'][ $key ]['zak'] += ceil($v_dst['jumlah'] / 50);
                                //     //                                 $data['inti'][ $key ]['jumlah'] += $v_dst['jumlah'];
                                //     //                                 $data['inti'][ $key ]['total'] += $total_supplier;
                                //     //                             }

                                //     //                             $_jml_pindah -= $v_dst['jumlah'];
                                //     //                         } else {
                                //     //                             $total_supplier = $d_ds->hrg_beli * $_jml_pindah;

                                //     //                             $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$d_ds->hrg_beli;

                                //     //                             if ( !isset($data['inti'][ $key ]) ) {
                                //     //                                 $data['inti'][ $key ] = array(
                                //     //                                     'tanggal' => $v_kp['tgl_kirim'],
                                //     //                                     'sj' => $v_kp['no_sj'],
                                //     //                                     'barang' => $d_tpd->d_barang->nama,
                                //     //                                     'zak' => ceil($_jml_pindah / 50),
                                //     //                                     'jumlah' => $_jml_pindah,
                                //     //                                     'harga' => $d_ds->hrg_beli,
                                //     //                                     'total' => $total_supplier
                                //     //                                 );
                                //     //                             } else {
                                //     //                                 $data['inti'][ $key ]['zak'] += ceil($_jml_pindah / 50);
                                //     //                                 $data['inti'][ $key ]['jumlah'] += $_jml_pindah;
                                //     //                                 $data['inti'][ $key ]['total'] += $total_supplier;
                                //     //                             }

                                //     //                             $_jml_pindah = 0;
                                //     //                         }
                                //     //                     }
                                //     //                 } else {
                                //     //                     $harga_beli = $val['nilai_beli'] / $val['jumlah'];
                                //     //                     $total_supplier = $harga_beli * $_jml_pindah;

                                //     //                     $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$val['nilai_beli'];

                                //     //                     if ( !isset($data['inti'][ $key ]) ) {
                                //     //                         $data['inti'][ $key ] = array(
                                //     //                             'tanggal' => $v_kp['tgl_kirim'],
                                //     //                             'sj' => $v_kp['no_sj'],
                                //     //                             'barang' => $d_tpd->d_barang->nama,
                                //     //                             'zak' => ceil($_jml_pindah / 50),
                                //     //                             'jumlah' => $_jml_pindah,
                                //     //                             'harga' => $harga_beli,
                                //     //                             'total' => $total_supplier
                                //     //                         );
                                //     //                     } else {
                                //     //                         $data['inti'][ $key ]['zak'] += ceil($_jml_pindah / 50);
                                //     //                         $data['inti'][ $key ]['jumlah'] += $_jml_pindah;
                                //     //                         $data['inti'][ $key ]['total'] += $total_supplier;
                                //     //                     }

                                //     //                     $_jml_pindah = 0;
                                //     //                 }
                                //     //             } else {
                                //     //                 cetak_r($val['item']);
                                //     //                 cetak_r($asal);
                                //     //                 cetak_r($tgl_kirim, 1);

                                //     //                 $_data = null;
                                //     //                 while ($jenis_kirim == 'opkp') {
                                //     //                     $m_kp = new \Model\Storage\KirimPakan_model();
                                //     //                     $sql = "
                                //     //                         select 
                                //     //                             kp.*,
                                //     //                             dkp.item,
                                //     //                             dkp.jumlah,
                                //     //                             dkp.nilai_beli,
                                //     //                             dkp.nilai_jual
                                //     //                         from det_kirim_pakan dkp 
                                //     //                         left join
                                //     //                             kirim_pakan kp 
                                //     //                             on
                                //     //                                 dkp.id_header = kp.id
                                //     //                         where
                                //     //                             dkp.item = '".$val['item']."' and
                                //     //                             kp.tujuan = '".$asal."' and
                                //     //                             kp.tgl_kirim <= '".$tgl_kirim."'
                                //     //                         order by
                                //     //                             kp.tgl_kirim desc,
                                //     //                             kp.no_order desc
                                //     //                     ";

                                //     //                     $d_kp_pindah = $m_kp->hydrateRaw($sql);
                                //     //                     if ( $d_kp_pindah->count() > 0 ) {
                                //     //                         $d_kp_pindah = $d_kp_pindah->toArray()[0];

                                //     //                         $asal = $d_kp_pindah['asal'];
                                //     //                         $jenis_kirim = $d_kp_pindah['jenis_kirim'];
                                //     //                         $tgl_kirim = $d_kp_pindah['tgl_kirim'];

                                //     //                         if ( $jenis_kirim == 'opkg' ) {
                                //     //                             $_data = $d_kp_pindah;
                                //     //                         }
                                //     //                     } else {
                                //     //                         $jenis_kirim = 'opkg';
                                //     //                     }
                                //     //                 }

                                //     //                 if ( !empty($_data) ) {
                                //     //                     $m_dst = new \Model\Storage\DetStokTrans_model();
                                //     //                     $d_dst = $m_dst->where('kode_trans', $_data['no_order'])->where('kode_barang', trim($_data['item']))->get();

                                //     //                     if ( $d_dst->count() > 0 ) {
                                //     //                         $d_dst = $d_dst->toArray();
                                //     //                         foreach ($d_dst as $k_dst => $v_dst) {
                                //     //                             $m_ds = new \Model\Storage\DetStok_model();
                                //     //                             $d_ds = $m_ds->where('id', $v_dst['id_header'])->first();
                                //     //                             if ( $_jml_pindah > $v_dst['jumlah'] ) {

                                //     //                                 $total_supplier = $d_ds->hrg_beli * $v_dst['jumlah'];

                                //     //                                 $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$d_ds->hrg_beli;

                                //     //                                 if ( !isset($data['inti'][ $key ]) ) {
                                //     //                                     $data['inti'][ $key ] = array(
                                //     //                                         'tanggal' => $v_kp['tgl_kirim'],
                                //     //                                         'sj' => $v_kp['no_sj'],
                                //     //                                         'barang' => $d_tpd->d_barang->nama,
                                //     //                                         'zak' => ceil($v_dst['jumlah'] / 50),
                                //     //                                         'jumlah' => $v_dst['jumlah'],
                                //     //                                         'harga' => $d_ds->hrg_beli,
                                //     //                                         'total' => $total_supplier
                                //     //                                     );
                                //     //                                 } else {
                                //     //                                     $data['inti'][ $key ]['zak'] += ceil($v_dst['jumlah'] / 50);
                                //     //                                     $data['inti'][ $key ]['jumlah'] += $v_dst['jumlah'];
                                //     //                                     $data['inti'][ $key ]['total'] += $total_supplier;
                                //     //                                 }

                                //     //                                 $_jml_pindah -= $v_dst['jumlah'];
                                //     //                             } else {
                                //     //                                 $total_supplier = $d_ds->hrg_beli * $_jml_pindah;

                                //     //                                 $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$d_ds->hrg_beli;

                                //     //                                 if ( !isset($data['inti'][ $key ]) ) {
                                //     //                                     $data['inti'][ $key ] = array(
                                //     //                                         'tanggal' => $v_kp['tgl_kirim'],
                                //     //                                         'sj' => $v_kp['no_sj'],
                                //     //                                         'barang' => $d_tpd->d_barang->nama,
                                //     //                                         'zak' => ceil($_jml_pindah / 50),
                                //     //                                         'jumlah' => $_jml_pindah,
                                //     //                                         'harga' => $d_ds->hrg_beli,
                                //     //                                         'total' => $total_supplier
                                //     //                                     );
                                //     //                                 } else {
                                //     //                                     $data['inti'][ $key ]['zak'] += ceil($_jml_pindah / 50);
                                //     //                                     $data['inti'][ $key ]['jumlah'] += $_jml_pindah;
                                //     //                                     $data['inti'][ $key ]['total'] += $total_supplier;
                                //     //                                 }

                                //     //                                 $_jml_pindah = 0;
                                //     //                             }
                                //     //                         }
                                //     //                     } else {
                                //     //                         $harga_beli = $val['nilai_beli'] / $val['jumlah'];
                                //     //                         $total_supplier = $harga_beli * $_jml_pindah;

                                //     //                         $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$val['nilai_beli'];

                                //     //                         if ( !isset($data['inti'][ $key ]) ) {
                                //     //                             $data['inti'][ $key ] = array(
                                //     //                                 'tanggal' => $v_kp['tgl_kirim'],
                                //     //                                 'sj' => $v_kp['no_sj'],
                                //     //                                 'barang' => $d_tpd->d_barang->nama,
                                //     //                                 'zak' => ceil($_jml_pindah / 50),
                                //     //                                 'jumlah' => $_jml_pindah,
                                //     //                                 'harga' => $harga_beli,
                                //     //                                 'total' => $total_supplier
                                //     //                             );
                                //     //                         } else {
                                //     //                             $data['inti'][ $key ]['zak'] += ceil($_jml_pindah / 50);
                                //     //                             $data['inti'][ $key ]['jumlah'] += $_jml_pindah;
                                //     //                             $data['inti'][ $key ]['total'] += $total_supplier;
                                //     //                         }

                                //     //                         $_jml_pindah = 0;
                                //     //                     }
                                //     //                 }
                                //     //             }
                                //     //         }
                                //     //     }
                                //     // }
                                // } else {
                                //     $harga_kontrak_pakan_supplier = ($v_kpd['nilai_beli'] > 0 && $v_kpd['jumlah'] > 0) ? $v_kpd['nilai_beli'] / $v_kpd['jumlah'] : 0;

                                //     $total_supplier = $v_kpd['nilai_beli'];

                                //     $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$v_kpd['nilai_beli'];

                                //     if ( !isset($data['inti'][ $key ]) ) {
                                //         $data['inti'][ $key ] = array(
                                //             'tanggal' => $v_kp['tgl_kirim'],
                                //             'sj' => $v_kp['no_sj'],
                                //             'barang' => $d_tpd->d_barang->nama,
                                //             'zak' => ceil($v_kpd['jumlah'] / 50),
                                //             'jumlah' => $v_kpd['jumlah'],
                                //             'harga' => $harga_kontrak_pakan_supplier,
                                //             'total' => $total_supplier
                                //         );
                                //     } else {
                                //         $data['inti'][ $key ]['zak'] += ceil($v_kpd['jumlah'] / 50);
                                //         $data['inti'][ $key ]['jumlah'] += $v_kpd['jumlah'];
                                //         $data['inti'][ $key ]['total'] += $total_supplier;
                                //     }
                                // }
                            }

                            if ( $v_kp['jenis_kirim'] == 'opkg' ) {
                                $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item;

                                if ( !isset( $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] ) ) {
                                    if ( $v_kp['ongkos_angkut'] > 0 ) {
                                        $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] = array(
                                            'nota' => $v_kp['no_sj'],
                                            'tanggal' => $v_kp['tgl_kirim'],
                                            'nopol' => $v_kp['ekspedisi'].' - '.$v_kp['no_polisi'],
                                            'barang' => $d_tpd->d_barang->nama,
                                            'zak' => ceil($d_tpd->jumlah / 50),
                                            'jumlah' => $d_tpd->jumlah,
                                            'harga' => $v_kp['ongkos_angkut'],
                                            'total' => $v_kp['ongkos_angkut'] * $d_tpd->jumlah
                                        );
                                    }
                                } else {
                                    $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['zak'] += ceil($d_tpd->jumlah / 50);
                                    $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['jumlah'] += $d_tpd->jumlah;
                                    $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['total'] += ($v_kp['ongkos_angkut'] * $d_tpd->jumlah);
                                    $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['harga'] = $v_kp['ongkos_angkut'];
                                }

                                if ( !empty($data['ongkos_angkut']) ) {
                                    ksort($data['ongkos_angkut']);
                                }
                            } else {
                                $sql = null;

                                if ( !empty($v_kpd['no_sj_asal']) ) {
                                    $sql = "
                                        select kp.no_order, kp.jenis_kirim, kp.ongkos_angkut, tp.tgl_terima, dtp.item, dtp.jumlah from det_terima_pakan dtp 
                                        right join
                                            terima_pakan tp 
                                            on
                                                dtp.id_header = tp.id
                                        right join
                                            kirim_pakan kp 
                                            on
                                                tp.id_kirim_pakan = kp.id
                                        where
                                            kp.no_sj = '".$v_kpd['no_sj_asal']."'
                                        group by
                                            kp.no_order, kp.jenis_kirim, kp.ongkos_angkut, tp.tgl_terima, dtp.item, dtp.jumlah
                                        order by
                                            tp.tgl_terima desc,
                                            kp.no_order desc
                                    ";
                                } else {
                                    $sql = "
                                        select kp.no_order, kp.jenis_kirim, kp.ongkos_angkut, tp.tgl_terima, dtp.item, dtp.jumlah from det_terima_pakan dtp 
                                        right join
                                            terima_pakan tp 
                                            on
                                                dtp.id_header = tp.id
                                        right join
                                            kirim_pakan kp 
                                            on
                                                tp.id_kirim_pakan = kp.id
                                        where
                                            kp.tujuan = '".$v_kp['asal']."' and
                                            tp.tgl_terima <= '".$v_kp['tgl_kirim']."' and
                                            dtp.item = '".$v_kpd['item']."'
                                        group by
                                            kp.no_order, kp.jenis_kirim, kp.ongkos_angkut, tp.tgl_terima, dtp.item, dtp.jumlah
                                        order by
                                            tp.tgl_terima desc,
                                            kp.no_order desc
                                    ";
                                }
                                
                                $m_kp = new \Model\Storage\KirimPakan_model();
                                $d_kp = $m_kp->hydrateRaw( $sql );

                                if ( $d_kp->count() > 0 ) {
                                    $d_kp = $d_kp->toArray();
                                    
                                    $idx_kp = 0;
                                    
                                    $jml_pindah_oa = $jumlah;

                                    while ( $jml_pindah_oa > 0 && isset($d_kp[ $idx_kp ]) ) {
                                        $jumlah_pindah = 0;

                                        // if ( !isset($d_kp[ $idx_kp ]) ) {
                                        //     cetak_r($d_kp);
                                        // }

                                        $hrg_oa = $d_kp[ $idx_kp ]['ongkos_angkut'];

                                        // if ( $v_kp['no_order'] == 'OP/KDR/25/01004' ) {
                                        //     cetak_r( $d_kp[ $idx_kp ], 1 );
                                        // }

                                        if ( $hrg_oa == 0 ) {
                                            $m_kp = new \Model\Storage\KirimPakan_model();
                                            $sql = "
                                                EXEC get_data_oa_pakan_new @no_order = '".$v_kp['no_order']."', @item = '".$v_kpd['item']."', @jumlah = ".$jml_pindah_oa.", @no_sj_asal = '".$v_kpd['no_sj_asal']."', @pp = 1
                                            ";

                                            $d_oa_pindah_pakan = $m_kp->hydrateRaw( $sql );

                                            if ( $d_oa_pindah_pakan->count() > 0 ) {
                                                $d_oa_pindah_pakan = $d_oa_pindah_pakan->toArray();

                                                // cetak_r( $v_kp['no_order'] );


                                                foreach ($d_oa_pindah_pakan as $key => $value) {
                                                    if ( $jml_pindah_oa <= $value['jumlah'] ) {
                                                        $jumlah_pindah = $jml_pindah_oa;
                                                        $jml_pindah_oa = 0;
                                                        $value['jumlah'] = $value['jumlah'] - $jml_pindah_oa;
                                                    } else {
                                                        $jumlah_pindah = $value['jumlah'];
                                                        $jml_pindah_oa = $jml_pindah_oa - $value['jumlah'];
                                                        $value['jumlah'] = 0;
                                                    }

                                                    if ( $jumlah_pindah > 0 ) {
                                                        $hrg_oa = $value['oa'];

                                                        $k_no_sj = !empty($v_kpd['no_sj_asal']) ? $v_kpd['no_sj_asal'] : $v_kp['no_sj'];

                                                        $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$v_kpd['item'].' | '.$hrg_oa;

                                                        if ( !isset( $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] ) ) {
                                                            $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] = array(
                                                                'nota' => $v_kp['no_sj'],
                                                                'tanggal' => $v_kp['tgl_kirim'],
                                                                'nopol' => $v_kp['ekspedisi'].' - '.$v_kp['no_polisi'],
                                                                'barang' => $d_tpd->d_barang->nama,
                                                                'zak' => ceil($jumlah_pindah / 50),
                                                                'jumlah' => $jumlah_pindah,
                                                                'harga' => $hrg_oa,
                                                                'total' => $hrg_oa * $jumlah_pindah
                                                            );
                                                        } else {
                                                            $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['zak'] += ceil($jumlah_pindah / 50);
                                                            $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['jumlah'] += $jumlah_pindah;
                                                            $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['total'] += $hrg_oa * $jumlah_pindah;
                                                        }

                                                        if ( !empty($data['ongkos_angkut']) ) {
                                                            ksort($data['ongkos_angkut']);
                                                        }
                                                    }

                                                }
                                            }

                                            $idx_kp++;
                                        } else {
                                            if ( $jml_pindah_oa <= $d_kp[ $idx_kp ]['jumlah'] ) {
                                                $jumlah_pindah = $jml_pindah_oa;
                                                $jml_pindah_oa = 0;
                                                $d_kp[ $idx_kp ]['jumlah'] = $d_kp[ $idx_kp ]['jumlah'] - $jml_pindah_oa;
                                            } else {
                                                $jumlah_pindah = $d_kp[ $idx_kp ]['jumlah'];
                                                $jml_pindah_oa -= $d_kp[ $idx_kp ]['jumlah'];
                                                $d_kp[ $idx_kp ]['jumlah'] = 0;

                                                $idx_kp++;
                                            }

                                            if ( $jumlah_pindah > 0 ) {
                                                $k_no_sj = !empty($v_kpd['no_sj_asal']) ? $v_kpd['no_sj_asal'] : $v_kp['no_sj'];

                                                $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$hrg_oa;

                                                if ( !isset( $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] ) ) {
                                                    $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] = array(
                                                        'nota' => $v_kp['no_sj'],
                                                        'tanggal' => $v_kp['tgl_kirim'],
                                                        'nopol' => $v_kp['ekspedisi'].' - '.$v_kp['no_polisi'],
                                                        'barang' => $d_tpd->d_barang->nama,
                                                        'zak' => ceil($jumlah_pindah / 50),
                                                        'jumlah' => $jumlah_pindah,
                                                        'harga' => $hrg_oa,
                                                        'total' => $hrg_oa * $jumlah_pindah
                                                    );
                                                } else {
                                                    $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['zak'] += ceil($jumlah_pindah / 50);
                                                    $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['jumlah'] += $jumlah_pindah;
                                                    $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['total'] += $hrg_oa * $jumlah_pindah;
                                                }

                                                if ( !empty($data['ongkos_angkut']) ) {
                                                    ksort($data['ongkos_angkut']);
                                                }

                                                // if ( $v_kp['no_order'] == 'OP/KDR/25/01004' ) {
                                                //     cetak_r( $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ], 1 );
                                                // }
                                            }
                                        }
                                    }
                                }
                            }

                            $jml_terima = $jml_terima - $v_kpd['jumlah'];

                            $id_det_terima_old = $id_det_terima;
                            if ( $jml_terima == 0  ) {
                                array_push($arr_id_det_terima, $d_tpd->id);
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }

    public function get_data_pindah_pakan($noreg, $_data_pakan)
    {
        // cetak_r($_data_pakan, 1);

        $sapronak_kesepakatan = $this->get_harga_kontrak( $noreg );
        // $_data_pakan = $this->get_data_pakan( $noreg );
        $data_pakan = $_data_pakan['inti'];
        $data_pakan_oa = $_data_pakan['ongkos_angkut'];

        $harga_sapronak = null;
        if ( !empty($sapronak_kesepakatan) ) {
            $harga_sapronak = $sapronak_kesepakatan['harga_sapronak'];
        }

        $m_kp = new \Model\Storage\KirimPakan_model();
        $d_kp = $m_kp->where('asal', $noreg)->where('jenis_tujuan', 'peternak')->with(['detail'])->get();

        $terpakai = null;

        $data = null;
        if ( $d_kp->count() > 0 ) {
            $d_kp = $d_kp->toArray();

            foreach ($d_kp as $k_kp => $v_kp) {
                $m_tp = new \Model\Storage\TerimaPakan_model();
                $d_tp = $m_tp->where('id_kirim_pakan', $v_kp['id'])->first();

                if ( $d_tp ) {
                    $arr_id_det_terima = array(0);
                    $id_det_terima = null;
                    $id_det_terima_old = null;
                    $jml_terima = null;
                    foreach ($v_kp['detail'] as $k_kpd => $v_kpd) {
                        if ( $v_kpd['jumlah'] > 0 ) {
                            $harga_kontrak_pakan_peternak = 0;
                            $harga_kontrak_pakan_supplier = 0;
                            if ( count($harga_sapronak) > 0 ) {
                                foreach ($harga_sapronak as $k_hs => $v_hs) {
                                    foreach ($v_hs['detail'] as $k_det => $v_det) {
                                        if ( $v_det['kode_brg'] == $v_kpd['item'] ) {
                                            $harga_kontrak_pakan_peternak = $v_det['hrg_peternak'];
                                        }
                                    }
                                }
                            }
                            $harga_kontrak_pakan_supplier = ($v_kpd['nilai_beli'] > 0 && $v_kpd['jumlah'] > 0) ? $v_kpd['nilai_beli'] / $v_kpd['jumlah'] : 0;

                            $m_tpd = new \Model\Storage\TerimaPakanDetail_model();
                            $d_tpd = $m_tpd->whereNotIn('id', $arr_id_det_terima)->where('id_header', $d_tp->id)->where('item', $v_kpd['item'])->with(['d_barang'])->first();

                            if ( $d_tpd ) {
                                $id_det_terima = $d_tpd->id;
                                
                                $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item;
                                if ( $id_det_terima != $id_det_terima_old ) {
                                    $jml_terima = $d_tpd->jumlah;

                                    $id_det_terima_old = $id_det_terima;
                                }

                                if ( isset($v_kp['detail'][ $k_kpd+1 ]) ) {
                                    $jml_pindah = ($v_kpd['jumlah'] < $jml_terima) ? $v_kpd['jumlah'] : $jml_terima;
                                    $jml_terima -= $jml_pindah;
                                } else {
                                    $jml_pindah = $jml_terima;
                                }

                                $total_peternak = $jml_pindah * $harga_kontrak_pakan_peternak;
                                if ( !isset($data['plasma'][ $key ]) ) {
                                    $data['plasma'][ $key ] = array(
                                        'tanggal' => $v_kp['tgl_kirim'],
                                        'sj' => $v_kp['no_sj'],
                                        'barang' => $d_tpd->d_barang->nama,
                                        'zak' => ceil($jml_pindah / 50),
                                        'jumlah' => $jml_pindah,
                                        'harga' => $harga_kontrak_pakan_peternak,
                                        'total' => $total_peternak
                                    );
                                } else {
                                    $data['plasma'][ $key ]['zak'] += ceil($jml_pindah / 50);
                                    $data['plasma'][ $key ]['jumlah'] += $jml_pindah;
                                    $data['plasma'][ $key ]['total'] += $total_peternak;
                                }

                                if ( empty($v_kpd['no_sj_asal']) ) {
                                    $sql = "
                                        select 
                                            kp.*,
                                            dkp.item,
                                            dkp.jumlah,
                                            dkp.nilai_beli,
                                            dkp.nilai_jual
                                        from det_kirim_pakan dkp 
                                        left join
                                            kirim_pakan kp 
                                            on
                                                dkp.id_header = kp.id
                                        left join
                                            det_stok_trans dst 
                                            on
                                                dst.kode_trans = kp.no_order
                                        left join
                                            det_stok ds 
                                            on
                                                ds.id = dst.id_header
                                        where
                                            dkp.item = '".$v_kpd['item']."' and
                                            kp.tujuan = '".$v_kp['asal']."' and
                                            kp.tgl_kirim <= '".$v_kp['tgl_kirim']."'
                                        order by
                                            kp.tgl_kirim desc,
                                            kp.no_order desc
                                    ";
                                } else {
                                    $sql = "
                                        select 
                                            kp.*,
                                            dkp.item,
                                            dkp.jumlah,
                                            dkp.nilai_beli,
                                            dkp.nilai_jual
                                        from det_kirim_pakan dkp 
                                        left join
                                            kirim_pakan kp 
                                            on
                                                dkp.id_header = kp.id
                                        left join
                                            det_stok_trans dst 
                                            on
                                                dst.kode_trans = kp.no_order
                                        left join
                                            det_stok ds 
                                            on
                                                ds.id = dst.id_header
                                        where
                                            dkp.item = '".$v_kpd['item']."' and
                                            kp.no_sj = '".$v_kpd['no_sj_asal']."'
                                        order by
                                            kp.tgl_kirim desc,
                                            kp.no_order desc
                                    ";
                                }

                                $m_kp = new \Model\Storage\KirimPakan_model();
                                $d_kp_pindah = $m_kp->hydrateRaw($sql);

                                if ( $v_kp['jenis_kirim'] == 'opkp' ) { //&& $v_kp['asal'] != $noreg ) {
                                    $m_conf = new \Model\Storage\Conf();
                                    $sql = "EXEC get_data_harga_pakan @no_order = '".$v_kp['no_order']."', @item = '".$v_kpd['item']."', @jumlah = '".$jml_pindah."', @no_sj_asal = '".$v_kpd['no_sj_asal']."', @pp = 1";

                                    // cetak_r( $sql, 1);

                                    $d_data = $m_conf->hydrateRaw( $sql );

                                    if ( $d_data->count() > 0 ) {
                                        $d_data = $d_data->toArray();

                                        if ( $v_kp['no_order'] == 'OP/KDR/25/01022' && $v_kpd['no_sj_asal'] == 'SJ/KDR/25/01004' ) {
                                            // cetak_r( $v_kpd['no_sj_asal'] );
                                            // cetak_r( $jumlah );
                                            // cetak_r( $d_data );
                                        }

                                        foreach ($d_data as $k_hrgpp => $v_hrgpp) {
                                            if ( $v_hrgpp['jumlah'] > 0 ) {
                                                $total_supplier = $v_hrgpp['harga'] * $v_hrgpp['jumlah'];
    
                                                $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$v_hrgpp['harga'];
    
                                                if ( !isset($data['inti'][ $key ]) ) {
                                                    $data['inti'][ $key ] = array(
                                                        'tanggal' => $v_kp['tgl_kirim'],
                                                        'sj' => $v_kp['no_sj'],
                                                        'barang' => $d_tpd->d_barang->nama,
                                                        'zak' => ceil($v_hrgpp['jumlah'] / 50),
                                                        'jumlah' => $v_hrgpp['jumlah'],
                                                        'harga' => $v_hrgpp['harga'],
                                                        'total' => $total_supplier
                                                    );
                                                } else {
                                                    $data['inti'][ $key ]['zak'] += ceil($v_hrgpp['jumlah'] / 50);
                                                    $data['inti'][ $key ]['jumlah'] += $v_hrgpp['jumlah'];
                                                    $data['inti'][ $key ]['total'] += $total_supplier;
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $input = $v_kpd['no_sj_asal'];
                                    $brg = $d_tpd->d_barang->nama;
                                    // $input = 'SJ/MJK/24/05008';
                                    $result = array_filter($data_pakan, function ($item) use ($input, $brg) {
                                        // cetak_r( $item );
                                        // cetak_r( $input );

                                        // return 'coba';
                                        if (stripos($item['sj'], $input) !== false && stripos($item['barang'], $brg) !== false) {
                                            return true;
                                        }
                                        return false;
                                    });
                                    
                                    if ( !empty($result) ) {
                                        $_jml = $jml_pindah;

                                        foreach ($result as $key => $value) {
                                            $__jml = ($_jml < $value['jumlah']) ? $_jml : $value['jumlah'];
                                            $_hrg = round($value['harga'], 1);

                                            $jml_simpan = 0;

                                            $key_terpakai = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kpd['no_sj_asal'].' | '.$d_tpd->item.' | '.$_hrg;

                                            // if ( $v_kp['no_sj'] == 'SJ/MJK/24/05034' ) {
                                            //     cetak_r( $result );
                                            //     cetak_r( $__jml );
                                            // }

                                            if ( !isset($terpakai[ $key_terpakai ]) ) {
                                                $terpakai[ $key_terpakai ]['total_pindah'] = $__jml;
                                                $terpakai[ $key_terpakai ]['jml_pindah'] = $__jml;

                                                $jml_simpan = $__jml;
                                            } else {
                                                // if ( $v_kp['no_sj'] == 'SJ/KDR/24/05061' ) {
                                                //     cetak_r( '-- HITUNG --' );
                                                //     cetak_r( $value['jumlah'] );
                                                //     cetak_r( $terpakai[ $key_terpakai ]['total_pindah'] );
                                                //     cetak_r( $terpakai[ $key_terpakai ]['jml_pindah'] );
                                                //     cetak_r( $__jml );
                                                //     cetak_r( '-- END - HITUNG --' );
                                                // }

                                                if ( $terpakai[ $key_terpakai ]['jml_pindah'] > $value['jumlah'] ) {
                                                    $terpakai[ $key_terpakai ]['jml_pindah'] -= $value['jumlah'];

                                                    $jml_simpan = 0;

                                                    $next = 1;
                                                    $__jml = 0;
                                                } else {
                                                    $sisa = $value['jumlah'] - $terpakai[ $key_terpakai ]['total_pindah'];
                                                    $jml_simpan = ($sisa > $__jml) ? $__jml : $sisa;
                                                    $terpakai[ $key_terpakai ]['jml_pindah'] = $terpakai[ $key_terpakai ]['total_pindah'] + $__jml;
                                                    $terpakai[ $key_terpakai ]['total_pindah'] = $terpakai[ $key_terpakai ]['jml_pindah'];
                                                }
                                            }

                                            if ( $jml_simpan > 0 ) {
                                                $total_supplier = $value['harga'] * $jml_simpan;
        
                                                $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$_hrg;
        
                                                if ( !isset($data['inti'][ $key ]) ) {
                                                    $data['inti'][ $key ] = array(
                                                        'tanggal' => $v_kp['tgl_kirim'],
                                                        'sj' => $v_kp['no_sj'],
                                                        'barang' => $d_tpd->d_barang->nama,
                                                        'zak' => ceil($jml_simpan / 50),
                                                        'jumlah' => $jml_simpan,
                                                        'harga' => $value['harga'],
                                                        'total' => $total_supplier
                                                    );
                                                } else {
                                                    $data['inti'][ $key ]['zak'] += ceil($jml_simpan / 50);
                                                    $data['inti'][ $key ]['jumlah'] += $jml_simpan;
                                                    $data['inti'][ $key ]['total'] += $total_supplier;
                                                }

                                                $_jml -= $jml_simpan;
                                            }
                                        }
                                    }
                                }

                                // if ( $d_kp_pindah->count() > 0 ) {
                                //     $d_kp_pindah = $d_kp_pindah->toArray();

                                //     $_jml_pindah = $jml_pindah;

                                //     foreach ($d_kp_pindah as $k => $val) {
                                //         if ( $_jml_pindah > 0 ) {
                                //             $asal = $val['asal'];
                                //             $tgl_kirim = $val['tgl_kirim'];
                                //             $jenis_kirim = $val['jenis_kirim'];

                                //             $key_inti = null;
                                //             if ( isset($data_pakan) && count($data_pakan) > 0 ) {
                                //                 foreach ($data_pakan as $k_inti => $v_inti) {
                                //                     if ( stristr($k_inti, $v_kpd['no_sj_asal']) !== false && stristr($k_inti, $d_tpd->item) !== false ) {
                                //                         $key_inti = $k_inti;
                                //                     }
                                //                 }
                                //             }

                                //             if ( isset($data_pakan[ $key_inti ]) ) {
                                //                 $pindah = ($d_tpd->jumlah < $v_kpd['jumlah']) ? $d_tpd->jumlah : $v_kpd['jumlah'];

                                //                 $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$data_pakan[ $key_inti ]['harga'].' | '.$data_pakan[ $key_inti ]['sj'];

                                //                 $total_supplier = $data_pakan[ $key_inti ]['harga'] * $pindah;

                                //                 $data['inti'][ $key ] = array(
                                //                     'tanggal' => $v_kp['tgl_kirim'],
                                //                     'sj' => $v_kp['no_sj'],
                                //                     'barang' => $d_tpd->d_barang->nama,
                                //                     'zak' => ceil($pindah / 50),
                                //                     'jumlah' => $pindah,
                                //                     'harga' => $data_pakan[ $key_inti ]['harga'],
                                //                     'total' => $total_supplier
                                //                 );
                                //             } else {
                                //                 if ( $jenis_kirim == 'opkg' ) {
                                //                     $m_dst = new \Model\Storage\DetStokTrans_model();
                                //                     $d_dst = $m_dst->where('kode_trans', $val['no_order'])->where('kode_barang', trim($v_kpd['item']))->get();

                                //                     if ( $d_dst->count() > 0 ) {
                                //                         $d_dst = $d_dst->toArray();
                                //                         foreach ($d_dst as $k_dst => $v_dst) {
                                //                             // if ( $v_kp['no_sj'] == 'SJ/LMJ/23/03099' ) {
                                //                             //     cetak_r( $_jml_pindah );
                                //                             //     cetak_r( $v_dst['jumlah'] );
                                //                             // }

                                //                             $m_ds = new \Model\Storage\DetStok_model();
                                //                             $d_ds = $m_ds->where('id', $v_dst['id_header'])->first();
                                //                             if ( $_jml_pindah > $v_dst['jumlah'] ) {

                                //                                 $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$d_ds->hrg_beli;

                                //                                 $total_supplier = $d_ds->hrg_beli * $v_dst['jumlah'];

                                //                                 // if ( $v_kp['no_sj'] == 'SJ/LMJ/23/03099' ) {
                                //                                 //     cetak_r( $d_ds->hrg_beli );
                                //                                 //     cetak_r( $v_dst['jumlah'] );
                                //                                 //     cetak_r( $total_supplier );
                                //                                 // }

                                //                                 if ( !isset($data['inti'][ $key ]) ) {
                                //                                     $data['inti'][ $key ] = array(
                                //                                         'tanggal' => $v_kp['tgl_kirim'],
                                //                                         'sj' => $v_kp['no_sj'],
                                //                                         'barang' => $d_tpd->d_barang->nama,
                                //                                         'zak' => ceil($v_dst['jumlah'] / 50),
                                //                                         'jumlah' => $v_dst['jumlah'],
                                //                                         'harga' => $d_ds->hrg_beli,
                                //                                         'total' => $total_supplier
                                //                                     );
                                //                                 } else {
                                //                                     $data['inti'][ $key ]['zak'] += ceil($v_dst['jumlah'] / 50);
                                //                                     $data['inti'][ $key ]['jumlah'] += $v_dst['jumlah'];
                                //                                     $data['inti'][ $key ]['total'] += $total_supplier;
                                //                                 }

                                //                                 $_jml_pindah -= $v_dst['jumlah'];
                                //                             } else {
                                //                                 $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$d_ds->hrg_beli;

                                //                                 $total_supplier = $d_ds->hrg_beli * $_jml_pindah;

                                //                                 // if ( $v_kp['no_sj'] == 'SJ/LMJ/23/03099' ) {
                                //                                 //     cetak_r( $d_ds->hrg_beli );
                                //                                 //     cetak_r( $_jml_pindah );
                                //                                 //     cetak_r( $total_supplier );
                                //                                 // }

                                //                                 if ( !isset($data['inti'][ $key ]) ) {
                                //                                     $data['inti'][ $key ] = array(
                                //                                         'tanggal' => $v_kp['tgl_kirim'],
                                //                                         'sj' => $v_kp['no_sj'],
                                //                                         'barang' => $d_tpd->d_barang->nama,
                                //                                         'zak' => ceil($_jml_pindah / 50),
                                //                                         'jumlah' => $_jml_pindah,
                                //                                         'harga' => $d_ds->hrg_beli,
                                //                                         'total' => $total_supplier
                                //                                     );
                                //                                 } else {
                                //                                     $data['inti'][ $key ]['zak'] += ceil($_jml_pindah / 50);
                                //                                     $data['inti'][ $key ]['jumlah'] += $_jml_pindah;
                                //                                     $data['inti'][ $key ]['total'] += $total_supplier;
                                //                                 }

                                //                                 $_jml_pindah = 0;
                                //                             }
                                //                         }
                                //                     } else {
                                //                         $harga_beli = $val['nilai_beli'] / $val['jumlah'];

                                //                         $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$harga_beli;

                                //                         $total_supplier = $harga_beli * $_jml_pindah;

                                //                         if ( !isset($data['inti'][ $key ]) ) {
                                //                             $data['inti'][ $key ] = array(
                                //                                 'tanggal' => $v_kp['tgl_kirim'],
                                //                                 'sj' => $v_kp['no_sj'],
                                //                                 'barang' => $d_tpd->d_barang->nama,
                                //                                 'zak' => ceil($_jml_pindah / 50),
                                //                                 'jumlah' => $_jml_pindah,
                                //                                 'harga' => $harga_beli,
                                //                                 'total' => $total_supplier
                                //                             );
                                //                         } else {
                                //                             $data['inti'][ $key ]['zak'] += ceil($_jml_pindah / 50);
                                //                             $data['inti'][ $key ]['jumlah'] += $_jml_pindah;
                                //                             $data['inti'][ $key ]['total'] += $total_supplier;
                                //                         }

                                //                         $_jml_pindah = 0;
                                //                     }
                                //                 } else {
                                //                     $_data = null;
                                //                     while ($jenis_kirim == 'opkp') {
                                //                         $m_kp = new \Model\Storage\KirimPakan_model();
                                //                         $sql = "
                                //                             select 
                                //                                 kp.*,
                                //                                 dkp.item,
                                //                                 dkp.jumlah,
                                //                                 dkp.nilai_beli,
                                //                                 dkp.nilai_jual
                                //                             from det_kirim_pakan dkp 
                                //                             left join
                                //                                 kirim_pakan kp 
                                //                                 on
                                //                                     dkp.id_header = kp.id
                                //                             where
                                //                                 dkp.item = '".$val['item']."' and
                                //                                 kp.tujuan = '".$asal."' and
                                //                                 kp.tgl_kirim <= '".$tgl_kirim."'
                                //                             order by
                                //                                 kp.tgl_kirim desc,
                                //                                 kp.no_order desc
                                //                         ";

                                //                         $d_kp_pindah = $m_kp->hydrateRaw($sql);
                                //                         if ( $d_kp_pindah->count() > 0 ) {
                                //                             $d_kp_pindah = $d_kp_pindah->toArray()[0];

                                //                             $asal = $d_kp_pindah['asal'];
                                //                             $jenis_kirim = $d_kp_pindah['jenis_kirim'];
                                //                             $tgl_kirim = $d_kp_pindah['tgl_kirim'];

                                //                             if ( $jenis_kirim == 'opkg' ) {
                                //                                 $_data = $d_kp_pindah;
                                //                             }
                                //                         } else {
                                //                             $jenis_kirim = 'opkg';
                                //                         }
                                //                     }

                                //                     if ( !empty($_data) ) {
                                //                         $m_dst = new \Model\Storage\DetStokTrans_model();
                                //                         $d_dst = $m_dst->where('kode_trans', $_data['no_order'])->where('kode_barang', trim($_data['item']))->get();

                                //                         if ( $d_dst->count() > 0 ) {
                                //                             $d_dst = $d_dst->toArray();
                                //                             foreach ($d_dst as $k_dst => $v_dst) {
                                //                                 $m_ds = new \Model\Storage\DetStok_model();
                                //                                 $d_ds = $m_ds->where('id', $v_dst['id_header'])->first();
                                //                                 if ( $_jml_pindah > $v_dst['jumlah'] ) {
                                //                                     $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$d_ds->hrg_beli;

                                //                                     $total_supplier = $d_ds->hrg_beli * $v_dst['jumlah'];

                                //                                     if ( !isset($data['inti'][ $key ]) ) {
                                //                                         $data['inti'][ $key ] = array(
                                //                                             'tanggal' => $v_kp['tgl_kirim'],
                                //                                             'sj' => $v_kp['no_sj'],
                                //                                             'barang' => $d_tpd->d_barang->nama,
                                //                                             'zak' => ceil($v_dst['jumlah'] / 50),
                                //                                             'jumlah' => $v_dst['jumlah'],
                                //                                             'harga' => $d_ds->hrg_beli,
                                //                                             'total' => $total_supplier
                                //                                         );
                                //                                     } else {
                                //                                         $data['inti'][ $key ]['zak'] += ceil($v_dst['jumlah'] / 50);
                                //                                         $data['inti'][ $key ]['jumlah'] += $v_dst['jumlah'];
                                //                                         $data['inti'][ $key ]['total'] += $total_supplier;
                                //                                     }

                                //                                     $_jml_pindah -= $v_dst['jumlah'];
                                //                                 } else {
                                //                                     $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$d_ds->hrg_beli;

                                //                                     $total_supplier = $d_ds->hrg_beli * $_jml_pindah;

                                //                                     if ( !isset($data['inti'][ $key ]) ) {
                                //                                         $data['inti'][ $key ] = array(
                                //                                             'tanggal' => $v_kp['tgl_kirim'],
                                //                                             'sj' => $v_kp['no_sj'],
                                //                                             'barang' => $d_tpd->d_barang->nama,
                                //                                             'zak' => ceil($_jml_pindah / 50),
                                //                                             'jumlah' => $_jml_pindah,
                                //                                             'harga' => $d_ds->hrg_beli,
                                //                                             'total' => $total_supplier
                                //                                         );
                                //                                     } else {
                                //                                         $data['inti'][ $key ]['zak'] += ceil($_jml_pindah / 50);
                                //                                         $data['inti'][ $key ]['jumlah'] += $_jml_pindah;
                                //                                         $data['inti'][ $key ]['total'] += $total_supplier;
                                //                                     }

                                //                                     $_jml_pindah = 0;
                                //                                 }
                                //                             }
                                //                         } else {
                                //                             $harga_beli = $val['nilai_beli'] / $val['jumlah'];

                                //                             $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$harga_beli;

                                //                             $total_supplier = $harga_beli * $_jml_pindah;

                                //                             if ( !isset($data['inti'][ $key ]) ) {
                                //                                 $data['inti'][ $key ] = array(
                                //                                     'tanggal' => $v_kp['tgl_kirim'],
                                //                                     'sj' => $v_kp['no_sj'],
                                //                                     'barang' => $d_tpd->d_barang->nama,
                                //                                     'zak' => ceil($_jml_pindah / 50),
                                //                                     'jumlah' => $_jml_pindah,
                                //                                     'harga' => $harga_beli,
                                //                                     'total' => $total_supplier
                                //                                 );
                                //                             } else {
                                //                                 $data['inti'][ $key ]['zak'] += ceil($_jml_pindah / 50);
                                //                                 $data['inti'][ $key ]['jumlah'] += $_jml_pindah;
                                //                                 $data['inti'][ $key ]['total'] += $total_supplier;
                                //                             }

                                //                             $_jml_pindah = 0;
                                //                         }
                                //                     }
                                //                 }
                                //             }
                                //         }
                                //     }
                                // }

                                // if ( $v_kp['no_order'] == 'OP/KDR/25/01022' && $v_kpd['no_sj_asal'] == 'SJ/KDR/25/01004' ) {
                                //     cetak_r( $data_pakan_oa );
                                // }

                                $jml_pindah_oa = $jml_pindah;
                                $ada = 0;
                                if ( isset($data_pakan_oa) && count($data_pakan_oa) > 0 ) {
                                    krsort( $data_pakan_oa );
                                    foreach ($data_pakan_oa as $k_tanggal => $v_tanggal) {
                                        foreach ($v_tanggal as $k_nopol => $v_nopol) {
                                            krsort( $v_nopol );
                                            foreach ($v_nopol as $k_oa => $v_oa) {
                                                if ( stristr($v_oa['nota'], $v_kpd['no_sj_asal']) !== false && stristr($k_oa, $d_tpd->item) !== false && $jml_pindah_oa > 0 ) {
                                                    $ada = 1;

                                                    $_jumlah = 0;
                                                    if ( $jml_pindah_oa <= $v_oa['jumlah']) {
                                                        $_jumlah = $jml_pindah_oa;

                                                        $jml_pindah_oa = 0;
                                                    } else {
                                                        $_jumlah = $v_oa['jumlah'];

                                                        $jml_pindah_oa = $jml_pindah_oa - $v_oa['jumlah'];
                                                    }

                                                    $hrg_oa = $v_oa['harga'];
                                                    $key = $v_kp['no_sj'].' | '.$d_tpd->d_barang->kode.' | '.$hrg_oa;

                                                    // cetak_r($v_kpd['no_sj_asal']);
                                                    // cetak_r($_jumlah);
                                                    // cetak_r($hrg_oa);

                                                    if ( !isset( $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] ) ) {
                                                        $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] = array(
                                                            'nota' => $v_kp['no_sj'],
                                                            'tanggal' => $v_kp['tgl_kirim'],
                                                            'nopol' => $v_kp['ekspedisi'].' - '.$v_kp['no_polisi'],
                                                            'barang' => $d_tpd->d_barang->nama,
                                                            'zak' => ceil($_jumlah / 50),
                                                            'jumlah' => $_jumlah,
                                                            'harga' => $hrg_oa,
                                                            'total' => $hrg_oa * $_jumlah
                                                        );
                                                    } else {
                                                        $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['zak'] += ceil($_jumlah / 50);
                                                        $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['jumlah'] += $_jumlah;
                                                        $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['total'] += $hrg_oa * $_jumlah;
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    if ( $ada == 0 && !isset( $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ] ) ) {
                                        $_jumlah = $jml_pindah_oa;
                                        
                                        $hrg_oa = 0;
                                        $key = $v_kp['no_sj'].' | '.$d_tpd->d_barang->kode.' | '.$hrg_oa;
                                        $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] = array(
                                            'nota' => $v_kp['no_sj'],
                                            'tanggal' => $v_kp['tgl_kirim'],
                                            'nopol' => $v_kp['ekspedisi'].' - '.$v_kp['no_polisi'],
                                            'barang' => $d_tpd->d_barang->nama,
                                            'zak' => ceil($_jumlah / 50),
                                            'jumlah' => $_jumlah,
                                            'harga' => $hrg_oa,
                                            'total' => $hrg_oa * $_jumlah
                                        );
                                    }
                                }

                                // $sql = null;
                                // if ( !empty($v_kpd['no_sj_asal']) ) {
                                //     $sql = "
                                //         select kp.no_order, kp.jenis_kirim, kp.ongkos_angkut, tp.tgl_terima, dtp.* from det_terima_pakan dtp 
                                //         right join
                                //             terima_pakan tp 
                                //             on
                                //                 dtp.id_header = tp.id
                                //         right join
                                //             kirim_pakan kp 
                                //             on
                                //                 tp.id_kirim_pakan = kp.id
                                //         where
                                //             kp.no_sj = '".$v_kpd['no_sj_asal']."'
                                //         order by
                                //             tp.tgl_terima desc,
                                //             kp.no_order desc
                                //     ";
                                // } else {
                                //     $sql = "
                                //         select kp.no_order, kp.jenis_kirim, kp.ongkos_angkut, tp.tgl_terima, dtp.* from det_terima_pakan dtp 
                                //         right join
                                //             terima_pakan tp 
                                //             on
                                //                 dtp.id_header = tp.id
                                //         right join
                                //             kirim_pakan kp 
                                //             on
                                //                 tp.id_kirim_pakan = kp.id
                                //         where
                                //             kp.tujuan = '".$v_kp['asal']."' and
                                //             tp.tgl_terima <= '".$v_kp['tgl_kirim']."' and
                                //             dtp.item = '".$v_kpd['item']."'
                                //         order by
                                //             tp.tgl_terima desc,
                                //             kp.no_order desc
                                //     ";
                                // }

                                // $m_kp = new \Model\Storage\KirimPakan_model();
                                // $d_kp = $m_kp->hydrateRaw( $sql );

                                // if ( $d_kp->count() > 0 ) {
                                //     $d_kp = $d_kp->toArray();

                                //     $idx_kp = 0;

                                //     $jml_pindah_oa = $jml_pindah;

                                //     while ( $jml_pindah_oa > 0 && isset($d_kp[ $idx_kp ]) ) {
                                //         $jumlah = 0;

                                //         $hrg_oa = $d_kp[ $idx_kp ]['ongkos_angkut'];

                                //         if ( $hrg_oa == 0 ) {
                                //             $m_kp = new \Model\Storage\KirimPakan_model();
                                //             $sql = "
                                //                 EXEC get_data_oa_pakan_new @no_order = '".$v_kp['no_order']."', @item = '".$v_kpd['item']."', @jumlah = ".$jml_pindah_oa.", @no_sj_asal = '".$v_kpd['no_sj_asal']."'
                                //             ";

                                //             $d_oa_pindah_pakan = $m_kp->hydrateRaw( $sql );

                                //             if ( $d_oa_pindah_pakan->count() > 0 ) {
                                //                 $d_oa_pindah_pakan = $d_oa_pindah_pakan->toArray();

                                //                 foreach ($d_oa_pindah_pakan as $k_oapp => $value) {
                                //                     if ( $jml_pindah_oa <= $value['jumlah'] ) {
                                //                         $jumlah = $jml_pindah_oa;
                                //                         $jml_pindah_oa = 0;
                                //                         $value['jumlah'] = $value['jumlah'] - $jml_pindah_oa;
                                //                     } else {
                                //                         $jumlah = $value['jumlah'];
                                //                         $jml_pindah_oa = $jml_pindah_oa - $value['jumlah'];
                                //                         $value['jumlah'] = 0;
                                //                     }

                                //                     if ( $jumlah > 0 ) {
                                //                         $hrg_oa = $value['oa'];

                                //                         $key = $v_kp['no_sj'].' | '.$d_tpd->d_barang->kode.' | '.$hrg_oa;

                                //                         if ( !isset( $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] ) ) {
                                //                             $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] = array(
                                //                                 'nota' => $v_kp['no_sj'],
                                //                                 'tanggal' => $v_kp['tgl_kirim'],
                                //                                 'nopol' => $v_kp['ekspedisi'].' - '.$v_kp['no_polisi'],
                                //                                 'barang' => $d_tpd->d_barang->nama,
                                //                                 'zak' => ceil($jumlah / 50),
                                //                                 'jumlah' => $jumlah,
                                //                                 'harga' => $hrg_oa,
                                //                                 'total' => $hrg_oa * $jumlah
                                //                             );
                                //                         } else {
                                //                             $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['zak'] += ceil($jumlah / 50);
                                //                             $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['jumlah'] += $jumlah;
                                //                             $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['total'] += $hrg_oa * $jumlah;
                                //                         }

                                //                         if ( !empty($data['ongkos_angkut']) ) {
                                //                             ksort($data['ongkos_angkut']);
                                //                         }
                                //                     }
                                //                 }
                                //             }

                                //             $idx_kp++;
                                //         } else {
                                //             if ( $jml_pindah_oa <= $d_kp[ $idx_kp ]['jumlah'] ) {
                                //                 $jumlah = $jml_pindah_oa;
                                //                 $jml_pindah_oa = 0;
                                //                 $d_kp[ $idx_kp ]['jumlah'] = $d_kp[ $idx_kp ]['jumlah'] - $jml_pindah_oa;
                                //             } else {
                                //                 $jumlah = $d_kp[ $idx_kp ]['jumlah'];
                                //                 $jml_pindah_oa -= $d_kp[ $idx_kp ]['jumlah'];
                                //                 $d_kp[ $idx_kp ]['jumlah'] = 0;

                                //                 $idx_kp++;
                                //             }

                                //             if ( $jumlah > 0 ) {
                                //                 $key = $v_kp['no_sj'].' | '.$d_tpd->d_barang->kode.' | '.$hrg_oa;

                                //                 if ( !isset( $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] ) ) {
                                //                     $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] = array(
                                //                         'nota' => $v_kp['no_sj'],
                                //                         'tanggal' => $v_kp['tgl_kirim'],
                                //                         'nopol' => $v_kp['ekspedisi'].' - '.$v_kp['no_polisi'],
                                //                         'barang' => $d_tpd->d_barang->nama,
                                //                         'zak' => ceil($jumlah / 50),
                                //                         'jumlah' => $jumlah,
                                //                         'harga' => $hrg_oa,
                                //                         'total' => $hrg_oa * $jumlah
                                //                     );
                                //                 } else {
                                //                     $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['zak'] += ceil($jumlah / 50);
                                //                     $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['jumlah'] += $jumlah;
                                //                     $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['total'] += $hrg_oa * $jumlah;
                                //                 }

                                //                 if ( !empty($data['ongkos_angkut']) ) {
                                //                     ksort($data['ongkos_angkut']);
                                //                 }
                                //             }
                                //         }
                                //     }
                                // }

                                // // cetak_r('JUMLAH PINDAH : '.$jml_pindah);
                                // // cetak_r('JUMLAH KIRIM : '.$v_kpd['jumlah']);

                                $jml_pindah = $jml_pindah - $d_tpd->jumlah;

                                $id_det_terima_old = $id_det_terima;
                                if ( $jml_pindah == 0  ) {
                                    array_push($arr_id_det_terima, $d_tpd->id);
                                }
                            }
                        }
                    }
                }
            }
        }

        // if ( $v_kpd['no_sj_asal'] == 'SJ/MJK/23/03082' ) {
        // cetak_r($data['ongkos_angkut']);
        // }

        return $data;
    }

    public function get_data_oa_pakan($noreg)
    {
        $m_kp = new \Model\Storage\KirimPakan_model();
        $d_kp = $m_kp->where('tujuan', $noreg)->where('jenis_tujuan', 'peternak')->orderBy('tgl_kirim', 'asc')->with(['detail'])->get();

        $data = null;
        if ( $d_kp->count() > 0 ) {
            $d_kp = $d_kp->toArray();
            foreach ($d_kp as $k_kp => $v_kp) {
                $m_tp = new \Model\Storage\TerimaPakan_model();
                $d_tp = $m_tp->where('id_kirim_pakan', $v_kp['id'])->orderBy('id', 'desc')->first();

                if ( $d_tp ) {
                    $arr_id_det_terima = array(0);
                    foreach ($v_kp['detail'] as $k_kpd => $v_kpd) {
                        $m_tpd = new \Model\Storage\TerimaPakanDetail_model();
                        $d_tpd = $m_tpd->whereNotIn('id', $arr_id_det_terima)->where('id_header', $d_tp->id)->where('item', $v_kpd['item'])->with(['d_barang'])->first();

                        if ( $d_tpd ) {
                            array_push($arr_id_det_terima, $d_tpd->id);

                            if ( $v_kp['jenis_kirim'] == 'opkg' ) {
                                $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item;

                                if ( !isset( $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] ) ) {
                                    if ( $v_kp['ongkos_angkut'] > 0 ) {
                                        $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] = array(
                                            'nota' => $v_kp['no_sj'],
                                            'tanggal' => $v_kp['tgl_kirim'],
                                            'nopol' => $v_kp['ekspedisi'].' - '.$v_kp['no_polisi'],
                                            'barang' => $d_tpd->d_barang->nama,
                                            'zak' => ceil($d_tpd->jumlah / 50),
                                            'jumlah' => $d_tpd->jumlah,
                                            // 'harga' => ($v_kp['ongkos_angkut'] > 0 && $d_tpd->jumlah > 0) ? $v_kp['ongkos_angkut'] / $d_tpd->jumlah : 0,
                                            'harga' => $v_kp['ongkos_angkut'],
                                            'total' => $v_kp['ongkos_angkut'] * $d_tpd->jumlah
                                        );
                                    }
                                } else {
                                    $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['zak'] += ceil($d_tpd->jumlah / 50);
                                    $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['jumlah'] += $d_tpd->jumlah;
                                    $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['total'] += ($v_kp['ongkos_angkut'] * $d_tpd->jumlah);
                                    $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['harga'] = $v_kp['ongkos_angkut'];
                                }

                                if ( !empty($data['ongkos_angkut']) ) {
                                    ksort($data['ongkos_angkut']);
                                }
                            } else {
                                $sql = null;
                                if ( !empty($v_kpd['no_sj_asal']) ) {
                                    $sql = "
                                        select kp.no_order, kp.jenis_kirim, kp.ongkos_angkut, tp.tgl_terima, dtp.* from det_terima_pakan dtp 
                                        right join
                                            terima_pakan tp 
                                            on
                                                dtp.id_header = tp.id
                                        right join
                                            kirim_pakan kp 
                                            on
                                                tp.id_kirim_pakan = kp.id
                                        where
                                            kp.no_sj = '".$v_kpd['no_sj_asal']."'
                                        order by
                                            tp.tgl_terima desc,
                                            kp.no_order desc
                                    ";
                                } else {
                                    $sql = "
                                        select kp.no_order, kp.jenis_kirim, kp.ongkos_angkut, tp.tgl_terima, dtp.* from det_terima_pakan dtp 
                                        right join
                                            terima_pakan tp 
                                            on
                                                dtp.id_header = tp.id
                                        right join
                                            kirim_pakan kp 
                                            on
                                                tp.id_kirim_pakan = kp.id
                                        where
                                            kp.tujuan = '".$v_kp['asal']."' and
                                            tp.tgl_terima <= '".$v_kp['tgl_kirim']."' and
                                            dtp.item = '".$v_kpd['item']."'
                                        order by
                                            tp.tgl_terima desc,
                                            kp.no_order desc
                                    ";
                                }
                                
                                $m_kp = new \Model\Storage\KirimPakan_model();
                                $d_kp = $m_kp->hydrateRaw( $sql );

                                if ( $d_kp->count() > 0 ) {
                                    $d_kp = $d_kp->toArray();

                                    $idx_kp = 0;

                                    $jml_pindah = $d_tpd->jumlah;
                                    while ( $jml_pindah > 0 && isset($d_kp[ $idx_kp ]) ) {
                                        $jumlah = 0;
                                        $hrg_oa = $d_kp[ $idx_kp ]['ongkos_angkut'];

                                        if ( $hrg_oa == 0 ) {
                                            $m_kp = new \Model\Storage\KirimPakan_model();
                                            $sql = "
                                                EXEC get_data_oa_pakan_new @no_order = '".$v_kp['no_order']."', @item = '".$v_kpd['item']."', @jumlah = ".$jml_pindah.", @no_sj_asal = '".$v_kpd['no_sj_asal']."', @pp = 1
                                            ";

                                            $d_oa_pindah_pakan = $m_kp->hydrateRaw( $sql );

                                            if ( $d_oa_pindah_pakan->count() > 0 ) {
                                                $d_oa_pindah_pakan = $d_oa_pindah_pakan->toArray();

                                                foreach ($d_oa_pindah_pakan as $key => $value) {
                                                    if ( $jml_pindah <= $value['jumlah'] ) {
                                                        $jumlah = $jml_pindah;
                                                        $jml_pindah = 0;
                                                        $value['jumlah'] = $value['jumlah'] - $jml_pindah;
                                                    } else {
                                                        $jumlah = $value['jumlah'];
                                                        $jml_pindah = $jml_pindah - $value['jumlah'];
                                                        $value['jumlah'] = 0;
                                                    }

                                                    if ( $jumlah > 0 ) {
                                                        $hrg_oa = $value['oa'];

                                                        $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$v_kpd['item'].' | '.$hrg_oa;

                                                        if ( !isset( $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] ) ) {
                                                            $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] = array(
                                                                'nota' => $v_kp['no_sj'],
                                                                'tanggal' => $v_kp['tgl_kirim'],
                                                                'nopol' => $v_kp['ekspedisi'].' - '.$v_kp['no_polisi'],
                                                                'barang' => $d_tpd->d_barang->nama,
                                                                'zak' => ceil($jumlah / 50),
                                                                'jumlah' => $jumlah,
                                                                'harga' => $hrg_oa,
                                                                'total' => $hrg_oa * $jumlah
                                                            );
                                                        } else {
                                                            $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['zak'] += ceil($jumlah / 50);
                                                            $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['jumlah'] += $jumlah;
                                                            $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['total'] += $hrg_oa * $jumlah;
                                                        }

                                                        if ( !empty($data['ongkos_angkut']) ) {
                                                            ksort($data['ongkos_angkut']);
                                                        }
                                                    }
                                                }
                                            }

                                            $idx_kp++;
                                        } else {
                                            if ( $jml_pindah <= $d_kp[ $idx_kp ]['jumlah'] ) {
                                                $jumlah = $jml_pindah;
                                                $jml_pindah = 0;
                                                $d_kp[ $idx_kp ]['jumlah'] = $d_kp[ $idx_kp ]['jumlah'] - $jml_pindah;
                                            } else {
                                                $jumlah = $d_kp[ $idx_kp ]['jumlah'];
                                                $jml_pindah -= $d_kp[ $idx_kp ]['jumlah'];
                                                $d_kp[ $idx_kp ]['jumlah'] = 0;

                                                $idx_kp++;
                                            }

                                            if ( $jumlah > 0 ) {
                                                $key = str_replace('-', '', $v_kp['tgl_kirim']).' | '.$v_kp['no_sj'].' | '.$d_tpd->item.' | '.$hrg_oa;

                                                if ( !isset( $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] ) ) {
                                                    $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] = array(
                                                        'nota' => $v_kp['no_sj'],
                                                        'tanggal' => $v_kp['tgl_kirim'],
                                                        'nopol' => $v_kp['ekspedisi'].' - '.$v_kp['no_polisi'],
                                                        'barang' => $d_tpd->d_barang->nama,
                                                        'zak' => ceil($jumlah / 50),
                                                        'jumlah' => $jumlah,
                                                        'harga' => $hrg_oa,
                                                        'total' => $hrg_oa * $jumlah
                                                    );
                                                } else {
                                                    $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['zak'] += ceil($jumlah / 50);
                                                    $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['jumlah'] += $jumlah;
                                                    $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['total'] += $hrg_oa * $jumlah;
                                                }

                                                if ( !empty($data['ongkos_angkut']) ) {
                                                    ksort($data['ongkos_angkut']);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }

    public function get_data_oa_pindah_pakan($noreg)
    {
        $m_kp = new \Model\Storage\KirimPakan_model();
        $d_kp = $m_kp->where('asal', $noreg)->where('jenis_tujuan', 'peternak')->with(['detail'])->get();

        $data = null;
        if ( $d_kp->count() > 0 ) {
            $d_kp = $d_kp->toArray();
            foreach ($d_kp as $k_kp => $v_kp) {
                $m_tp = new \Model\Storage\TerimaPakan_model();
                $d_tp = $m_tp->where('id_kirim_pakan', $v_kp['id'])->first();

                if ( $d_tp ) {
                    $arr_id_det_terima = array(0);
                    foreach ($v_kp['detail'] as $k_kpd => $v_kpd) {
                        if ( $v_kpd['jumlah'] > 0 ) {
                            $m_tpd = new \Model\Storage\TerimaPakanDetail_model();
                            $d_tpd = $m_tpd->whereNotIn('id', $arr_id_det_terima)->where('id_header', $d_tp->id)->where('item', $v_kpd['item'])->with(['d_barang'])->first();

                            if ( $d_tpd ) {
                                array_push($arr_id_det_terima, $d_tpd->id);

                                $sql = null;
                                if ( !empty($v_kpd['no_sj_asal']) ) {
                                    $sql = "
                                        select kp.no_order, kp.jenis_kirim, kp.ongkos_angkut, tp.tgl_terima, dtp.* from det_terima_pakan dtp 
                                        right join
                                            terima_pakan tp 
                                            on
                                                dtp.id_header = tp.id
                                        right join
                                            kirim_pakan kp 
                                            on
                                                tp.id_kirim_pakan = kp.id
                                        where
                                            kp.no_sj = '".$v_kpd['no_sj_asal']."'
                                        order by
                                            tp.tgl_terima desc,
                                            kp.no_order desc
                                    ";
                                } else {
                                    $sql = "
                                        select kp.no_order, kp.jenis_kirim, kp.ongkos_angkut, tp.tgl_terima, dtp.* from det_terima_pakan dtp 
                                        right join
                                            terima_pakan tp 
                                            on
                                                dtp.id_header = tp.id
                                        right join
                                            kirim_pakan kp 
                                            on
                                                tp.id_kirim_pakan = kp.id
                                        where
                                            kp.tujuan = '".$v_kp['asal']."' and
                                            tp.tgl_terima <= '".$v_kp['tgl_kirim']."' and
                                            dtp.item = '".$v_kpd['item']."'
                                        order by
                                            tp.tgl_terima desc,
                                            kp.no_order desc
                                    ";
                                }

                                $m_kp = new \Model\Storage\KirimPakan_model();
                                $d_kp = $m_kp->hydrateRaw( $sql );

                                $jml_pindah = $d_tpd->jumlah;

                                // cetak_r($v_kpd['no_sj_asal']);
                                // cetak_r($jml_pindah);

                                // if ( $d_kp->count() > 0 ) {
                                //     $d_kp = $d_kp->toArray();

                                //     $idx_kp = 0;

                                //     $jml_pindah = $d_tpd->jumlah;
                                //     while ( $jml_pindah > 0 && isset($d_kp[ $idx_kp ]) ) {
                                //         $jumlah = 0;
                                //         $hrg_oa = $d_kp[ $idx_kp ]['ongkos_angkut'];

                                //         if ( $hrg_oa == 0 ) {
                                //             $m_kp = new \Model\Storage\KirimPakan_model();
                                //             $sql = "
                                //                 EXEC get_data_oa_pakan_new @no_order = '".$v_kp['no_order']."', @item = '".$v_kpd['item']."', @jumlah = ".$jml_pindah.", @no_sj_asal = '".$v_kpd['no_sj_asal']."'
                                //             ";

                                //             $d_oa_pindah_pakan = $m_kp->hydrateRaw( $sql );

                                //             if ( $d_oa_pindah_pakan->count() > 0 ) {
                                //                 $d_oa_pindah_pakan = $d_oa_pindah_pakan->toArray();

                                //                 foreach ($d_oa_pindah_pakan as $key => $value) {
                                //                     if ( $jml_pindah <= $value['jumlah'] ) {
                                //                         $jumlah = $jml_pindah;
                                //                         $jml_pindah = 0;
                                //                         $value['jumlah'] = $value['jumlah'] - $jml_pindah;
                                //                     } else {
                                //                         $jumlah = $value['jumlah'];
                                //                         $jml_pindah = $jml_pindah - $value['jumlah'];
                                //                         $value['jumlah'] = 0;
                                //                     }

                                //                     if ( $jumlah > 0 ) {
                                //                         $hrg_oa = $value['oa'];

                                //                         $key = $v_kp['no_sj'].' | '.$d_tpd->item.' | '.$hrg_oa;

                                //                         if ( !isset( $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] ) ) {
                                //                             $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] = array(
                                //                                 'nota' => $v_kp['no_sj'],
                                //                                 'tanggal' => $v_kp['tgl_kirim'],
                                //                                 'nopol' => $v_kp['ekspedisi'].' - '.$v_kp['no_polisi'],
                                //                                 'barang' => $d_tpd->d_barang->nama,
                                //                                 'zak' => ceil($jumlah / 50),
                                //                                 'jumlah' => $jumlah,
                                //                                 'harga' => $hrg_oa,
                                //                                 'total' => $hrg_oa * $jumlah
                                //                             );
                                //                         } else {
                                //                             $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['zak'] += ceil($jumlah / 50);
                                //                             $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['jumlah'] += $jumlah;
                                //                             $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['total'] += $hrg_oa * $jumlah;
                                //                         }

                                //                         if ( !empty($data['ongkos_angkut']) ) {
                                //                             ksort($data['ongkos_angkut']);
                                //                         }
                                //                     }
                                //                 }
                                //             }

                                //             $idx_kp++;
                                //         } else {
                                //             if ( $jml_pindah <= $d_kp[ $idx_kp ]['jumlah'] ) {
                                //                 $jumlah = $jml_pindah;
                                //                 $jml_pindah = 0;
                                //                 $d_kp[ $idx_kp ]['jumlah'] = $d_kp[ $idx_kp ]['jumlah'] - $jml_pindah;
                                //             } else {
                                //                 $jumlah = $d_kp[ $idx_kp ]['jumlah'];
                                //                 $jml_pindah -= $d_kp[ $idx_kp ]['jumlah'];
                                //                 $d_kp[ $idx_kp ]['jumlah'] = 0;

                                //                 $idx_kp++;
                                //             }

                                //             if ( $jumlah > 0 ) {
                                //                 $key = $v_kp['no_sj'].' | '.$d_tpd->item.' | '.$hrg_oa;

                                //                 if ( !isset( $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] ) ) {
                                //                     $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ] = array(
                                //                         'nota' => $v_kp['no_sj'],
                                //                         'tanggal' => $v_kp['tgl_kirim'],
                                //                         'nopol' => $v_kp['ekspedisi'].' - '.$v_kp['no_polisi'],
                                //                         'barang' => $d_tpd->d_barang->nama,
                                //                         'zak' => ceil($jumlah / 50),
                                //                         'jumlah' => $jumlah,
                                //                         'harga' => $hrg_oa,
                                //                         'total' => $hrg_oa * $jumlah
                                //                     );
                                //                 } else {
                                //                     $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['zak'] += ceil($jumlah / 50);
                                //                     $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['jumlah'] += $jumlah;
                                //                     $data['ongkos_angkut'][ $v_kp['tgl_kirim'] ][ $v_kp['no_polisi'] ][ $key ]['total'] += $hrg_oa * $jumlah;
                                //                 }

                                //                 if ( !empty($data['ongkos_angkut']) ) {
                                //                     ksort($data['ongkos_angkut']);
                                //                 }
                                //             }
                                //         }
                                //     }
                                // }
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }

    public function get_data_retur_pakan($noreg)
    {
        $sapronak_kesepakatan = $this->get_harga_kontrak( $noreg );

        $harga_sapronak = null;
        if ( !empty($sapronak_kesepakatan) ) {
            $harga_sapronak = $sapronak_kesepakatan['harga_sapronak'];
        }

        $data = null;

        $m_kp = new \Model\Storage\KirimPakan_model();
        $d_kp = $m_kp->where('tujuan', $noreg)->where('jenis_tujuan', 'peternak')->with(['detail'])->get();

        if ( $d_kp->count() > 0 ) {
            $d_kp = $d_kp->toArray();

            foreach ($d_kp as $k_kp => $v_kp) {
                $m_rp = new \Model\Storage\ReturPakan_model();
                $d_rp = $m_rp->where('no_order', $v_kp['no_order'])->with(['det_retur_pakan'])->get()->toArray();

                if ( count($d_rp) > 0 ) {
                    foreach ($d_rp as $k_rp => $v_rp) {
                        foreach ($v_rp['det_retur_pakan'] as $k_rpd => $v_rpd) {
                            if ( $v_rpd['jumlah'] > 0 ) {
                                $harga_kontrak_pakan_peternak = 0;
                                $harga_kontrak_pakan_supplier = 0;
                                if ( count($harga_sapronak) > 0 ) {
                                    foreach ($harga_sapronak as $k_hs => $v_hs) {
                                        foreach ($v_hs['detail'] as $k_det => $v_det) {
                                            if ( $v_det['kode_brg'] == $v_rpd['item'] ) {
                                                $harga_kontrak_pakan_peternak = $v_det['hrg_peternak'];
                                            }
                                        }
                                    }
                                }
                                $harga_kontrak_pakan_supplier = ($v_rpd['nilai_beli'] > 0 && $v_rpd['jumlah'] > 0) ? $v_rpd['nilai_beli'] / $v_rpd['jumlah'] : 0;

                                $key = str_replace('-', '', $v_rp['tgl_retur']).' | '.$v_kp['no_sj'].' | '.$v_rpd['item'];

                                $jml_retur = $v_rpd['jumlah'];

                                $total_jual = $harga_kontrak_pakan_peternak * $jml_retur;
                                $data['plasma'][ $key ] = array(
                                    'tanggal' => $v_rp['tgl_retur'],
                                    'nota' => empty($v_rp['no_retur']) ? '-' : $v_rp['no_retur'],
                                    'sj' => null,
                                    'barang' => $v_rpd['d_barang']['nama'],
                                    'jumlah' => $jml_retur,
                                    'harga' => $harga_kontrak_pakan_peternak,
                                    'total' => $total_jual,
                                    'decimal' => $v_rpd['d_barang']['desimal_harga']
                                );

                                if ( $v_kp['jenis_kirim'] == 'opkg' ) {
                                    $m_dst = new \Model\Storage\DetStokTrans_model();
                                    $sql = "
                                        select dst.* from det_stok_trans dst
                                        left join
                                            det_stok ds
                                            on
                                                dst.id_header = ds.id
                                        where
                                            dst.kode_trans = '".$v_rp['no_order']."' and
                                            ds.kode_barang = '".trim($v_rpd['item'])."'
                                        order by
                                            ds.tgl_trans desc
                                        
                                    ";
                                    $d_dst = $m_dst->hydrateRaw( $sql );
                                    // $d_dst = $m_dst->where('kode_trans', $v_rp['no_order'])->where('kode_barang', trim($v_rpd['item']))->get();

                                    if ( $d_dst->count() > 0 ) {
                                        $d_dst = $d_dst->toArray();
                                        $_jml_retur = $jml_retur;
                                        foreach ($d_dst as $k_dst => $v_dst) {
                                            $m_ds = new \Model\Storage\DetStok_model();
                                            $d_ds = $m_ds->where('id', $v_dst['id_header'])->first();
                                            if ( $_jml_retur > $v_dst['jumlah'] ) {
                                                $total_supplier = $d_ds->hrg_beli * $v_dst['jumlah'];

                                                if ( !isset($data['inti'][ $key ]) ) {
                                                    $data['inti'][ $key ] = array(
                                                        'tanggal' => $v_rp['tgl_retur'],
                                                        'nota' => empty($v_rp['no_retur']) ? '-' : $v_rp['no_retur'],
                                                        'sj' => null,
                                                        'barang' => $v_rpd['d_barang']['nama'],
                                                        'zak' => ceil($v_dst['jumlah'] / 50),
                                                        'jumlah' => $v_dst['jumlah'],
                                                        'harga' => $d_ds->hrg_beli,
                                                        'total' => $total_supplier
                                                    );
                                                } else {
                                                    $data['inti'][ $key ]['zak'] += ceil($v_dst['jumlah'] / 50);
                                                    $data['inti'][ $key ]['jumlah'] += $v_dst['jumlah'];
                                                    $data['inti'][ $key ]['total'] += $total_supplier;
                                                }

                                                $_jml_retur = $_jml_retur-$v_dst['jumlah'];
                                            } else {
                                                $total_supplier = $d_ds->hrg_beli * $_jml_retur;

                                                if ( !isset($data['inti'][ $key ]) ) {
                                                    $data['inti'][ $key ] = array(
                                                        'tanggal' => $v_rp['tgl_retur'],
                                                        'nota' => empty($v_rp['no_retur']) ? '-' : $v_rp['no_retur'],
                                                        'sj' => null,
                                                        'barang' => $v_rpd['d_barang']['nama'],
                                                        'zak' => ceil($jml_retur / 50),
                                                        'jumlah' => $_jml_retur,
                                                        'harga' => $d_ds->hrg_beli,
                                                        'total' => $total_supplier
                                                    );
                                                } else {
                                                    $data['inti'][ $key ]['zak'] += ceil($_jml_retur / 50);
                                                    $data['inti'][ $key ]['jumlah'] += $_jml_retur;
                                                    $data['inti'][ $key ]['total'] += $total_supplier;
                                                }

                                                $_jml_retur = 0;
                                            }
                                        }
                                    } else {
                                        $harga_beli = 0;
                                        $total_supplier = $harga_beli * $jml_retur;

                                        if ( !isset($data['inti'][ $key ]) ) {
                                            $data['inti'][ $key ] = array(
                                                'tanggal' => $v_rp['tgl_retur'],
                                                'nota' => empty($v_rp['no_retur']) ? '-' : $v_rp['no_retur'],
                                                'sj' => null,
                                                'barang' => $v_rpd['d_barang']['nama'],
                                                'zak' => ceil($jml_retur / 50),
                                                'jumlah' => $jml_retur,
                                                'harga' => $harga_beli,
                                                'total' => $total_supplier
                                            );
                                        } else {
                                            $data['inti'][ $key ]['zak'] += ceil($jml_retur / 50);
                                            $data['inti'][ $key ]['jumlah'] += $jml_retur;
                                            $data['inti'][ $key ]['total'] += $total_supplier;
                                        }
                                    }
                                } else {
                                    $m_kp = new \Model\Storage\KirimPakan_model();
                                    $sql = "
                                        select 
                                            kp.*,
                                            dkp.item,
                                            dkp.jumlah,
                                            dkp.nilai_beli,
                                            dkp.nilai_jual
                                        from det_kirim_pakan dkp 
                                        left join
                                            kirim_pakan kp 
                                            on
                                                dkp.id_header = kp.id
                                        left join
                                            det_stok_trans dst 
                                            on
                                                dst.kode_trans = kp.no_order
                                        left join
                                            det_stok ds 
                                            on
                                                ds.id = dst.id_header
                                        where
                                            dkp.item = '".$v_rpd['item']."' and
                                            kp.tujuan = '".$v_kp['asal']."' and
                                            kp.tgl_kirim <= '".$v_kp['tgl_kirim']."'
                                        order by
                                            kp.tgl_kirim desc,
                                            kp.no_order desc
                                    ";

                                    $d_kp_pindah = $m_kp->hydrateRaw($sql);
                                    if ( $d_kp_pindah->count() > 0 ) {
                                        $d_kp_pindah = $d_kp_pindah->toArray();

                                        $_jml_pindah = $jml_retur;

                                        foreach ($d_kp_pindah as $k => $val) {
                                            if ( $_jml_pindah > 0 ) {
                                                $asal = $val['asal'];
                                                $tgl_kirim = $val['tgl_kirim'];
                                                $jenis_kirim = $val['jenis_kirim'];

                                                if ( $jenis_kirim == 'opkg' ) {
                                                    $m_dst = new \Model\Storage\DetStokTrans_model();
                                                    $d_dst = $m_dst->where('kode_trans', $val['no_order'])->where('kode_barang', trim($v_rpd['item']))->get();

                                                    if ( $d_dst->count() > 0 ) {
                                                        $d_dst = $d_dst->toArray();
                                                        foreach ($d_dst as $k_dst => $v_dst) {
                                                            $m_ds = new \Model\Storage\DetStok_model();
                                                            $d_ds = $m_ds->where('id', $v_dst['id_header'])->first();
                                                            if ( $_jml_pindah > $v_dst['jumlah'] ) {

                                                                $total_supplier = $d_ds->hrg_beli * $v_dst['jumlah'];

                                                                if ( !isset($data['inti'][ $key ]) ) {
                                                                    $data['inti'][ $key ] = array(
                                                                        'tanggal' => $v_rp['tgl_retur'],
                                                                        'nota' => empty($v_rp['no_retur']) ? '-' : $v_rp['no_retur'],
                                                                        'sj' => null,
                                                                        'barang' => $v_rpd['d_barang']['nama'],
                                                                        'zak' => ceil($v_dst['jumlah'] / 50),
                                                                        'jumlah' => $v_dst['jumlah'],
                                                                        'harga' => $d_ds->hrg_beli,
                                                                        'total' => $total_supplier
                                                                    );
                                                                } else {
                                                                    $data['inti'][ $key ]['zak'] += ceil($v_dst['jumlah'] / 50);
                                                                    $data['inti'][ $key ]['jumlah'] += $v_dst['jumlah'];
                                                                    $data['inti'][ $key ]['total'] += $total_supplier;
                                                                }

                                                                $_jml_pindah -= $v_dst['jumlah'];
                                                            } else {
                                                                $total_supplier = $d_ds->hrg_beli * $_jml_pindah;

                                                                if ( !isset($data['inti'][ $key ]) ) {
                                                                    $data['inti'][ $key ] = array(
                                                                        'tanggal' => $v_rp['tgl_retur'],
                                                                        'nota' => empty($v_rp['no_retur']) ? '-' : $v_rp['no_retur'],
                                                                        'sj' => null,
                                                                        'barang' => $v_rpd['d_barang']['nama'],
                                                                        'zak' => ceil($_jml_pindah / 50),
                                                                        'jumlah' => $_jml_pindah,
                                                                        'harga' => $d_ds->hrg_beli,
                                                                        'total' => $total_supplier
                                                                    );
                                                                } else {
                                                                    $data['inti'][ $key ]['zak'] += ceil($_jml_pindah / 50);
                                                                    $data['inti'][ $key ]['jumlah'] += $_jml_pindah;
                                                                    $data['inti'][ $key ]['total'] += $total_supplier;
                                                                }

                                                                $_jml_pindah = 0;
                                                            }
                                                        }
                                                    } else {
                                                        $harga_beli = $val['nilai_beli'] / $val['jumlah'];
                                                        $total_supplier = $harga_beli * $_jml_pindah;

                                                        if ( !isset($data['inti'][ $key ]) ) {
                                                            $data['inti'][ $key ] = array(
                                                                'tanggal' => $v_rp['tgl_retur'],
                                                                'nota' => empty($v_rp['no_retur']) ? '-' : $v_rp['no_retur'],
                                                                'sj' => null,
                                                                'barang' => $v_rpd['d_barang']['nama'],
                                                                'zak' => ceil($_jml_pindah / 50),
                                                                'jumlah' => $_jml_pindah,
                                                                'harga' => $harga_beli,
                                                                'total' => $total_supplier
                                                            );
                                                        } else {
                                                            $data['inti'][ $key ]['zak'] += ceil($_jml_pindah / 50);
                                                            $data['inti'][ $key ]['jumlah'] += $_jml_pindah;
                                                            $data['inti'][ $key ]['total'] += $total_supplier;
                                                        }

                                                        $_jml_pindah = 0;
                                                    }
                                                } else {
                                                    $_data = null;
                                                    while ($jenis_kirim == 'opkp') {
                                                        $m_kp = new \Model\Storage\KirimPakan_model();
                                                        $sql = "
                                                            select 
                                                                kp.*,
                                                                dkp.item,
                                                                dkp.jumlah,
                                                                dkp.nilai_beli,
                                                                dkp.nilai_jual
                                                            from det_kirim_pakan dkp 
                                                            left join
                                                                kirim_pakan kp 
                                                                on
                                                                    dkp.id_header = kp.id
                                                            where
                                                                dkp.item = '".$val['item']."' and
                                                                kp.tujuan = '".$asal."' and
                                                                kp.tgl_kirim <= '".$tgl_kirim."'
                                                            order by
                                                                kp.tgl_kirim desc,
                                                                kp.no_order desc
                                                        ";

                                                        $d_kp_pindah = $m_kp->hydrateRaw($sql);
                                                        if ( $d_kp_pindah->count() > 0 ) {
                                                            $d_kp_pindah = $d_kp_pindah->toArray()[0];

                                                            $asal = $d_kp_pindah['asal'];
                                                            $jenis_kirim = $d_kp_pindah['jenis_kirim'];
                                                            $tgl_kirim = $d_kp_pindah['tgl_kirim'];

                                                            if ( $jenis_kirim == 'opkg' ) {
                                                                $_data = $d_kp_pindah;
                                                            }
                                                        } else {
                                                            $jenis_kirim = 'opkg';
                                                        }
                                                    }

                                                    if ( !empty($_data) ) {
                                                        $m_dst = new \Model\Storage\DetStokTrans_model();
                                                        $d_dst = $m_dst->where('kode_trans', $_data['no_order'])->where('kode_barang', trim($_data['item']))->get();

                                                        if ( $d_dst->count() > 0 ) {
                                                            $d_dst = $d_dst->toArray();
                                                            foreach ($d_dst as $k_dst => $v_dst) {
                                                                $m_ds = new \Model\Storage\DetStok_model();
                                                                $d_ds = $m_ds->where('id', $v_dst['id_header'])->first();
                                                                if ( $_jml_pindah > $v_dst['jumlah'] ) {

                                                                    $total_supplier = $d_ds->hrg_beli * $v_dst['jumlah'];

                                                                    if ( !isset($data['inti'][ $key ]) ) {
                                                                        $data['inti'][ $key ] = array(
                                                                            'tanggal' => $v_rp['tgl_retur'],
                                                                            'nota' => empty($v_rp['no_retur']) ? '-' : $v_rp['no_retur'],
                                                                            'sj' => null,
                                                                            'barang' => $v_rpd['d_barang']['nama'],
                                                                            'zak' => ceil($v_dst['jumlah'] / 50),
                                                                            'jumlah' => $v_dst['jumlah'],
                                                                            'harga' => $d_ds->hrg_beli,
                                                                            'total' => $total_supplier
                                                                        );
                                                                    } else {
                                                                        $data['inti'][ $key ]['zak'] += ceil($v_dst['jumlah'] / 50);
                                                                        $data['inti'][ $key ]['jumlah'] += $v_dst['jumlah'];
                                                                        $data['inti'][ $key ]['total'] += $total_supplier;
                                                                    }

                                                                    $_jml_pindah -= $v_dst['jumlah'];
                                                                } else {
                                                                    $total_supplier = $d_ds->hrg_beli * $_jml_pindah;

                                                                    if ( !isset($data['inti'][ $key ]) ) {
                                                                        $data['inti'][ $key ] = array(
                                                                            'tanggal' => $v_rp['tgl_retur'],
                                                                            'nota' => empty($v_rp['no_retur']) ? '-' : $v_rp['no_retur'],
                                                                            'sj' => null,
                                                                            'barang' => $v_rpd['d_barang']['nama'],
                                                                            'zak' => ceil($_jml_pindah / 50),
                                                                            'jumlah' => $_jml_pindah,
                                                                            'harga' => $d_ds->hrg_beli,
                                                                            'total' => $total_supplier
                                                                        );
                                                                    } else {
                                                                        $data['inti'][ $key ]['zak'] += ceil($_jml_pindah / 50);
                                                                        $data['inti'][ $key ]['jumlah'] += $_jml_pindah;
                                                                        $data['inti'][ $key ]['total'] += $total_supplier;
                                                                    }

                                                                    $_jml_pindah = 0;
                                                                }
                                                            }
                                                        } else {
                                                            $harga_beli = $val['nilai_beli'] / $val['jumlah'];
                                                            $total_supplier = $harga_beli * $_jml_pindah;

                                                        if ( !isset($data['inti'][ $key ]) ) {
                                                                $data['inti'][ $key ] = array(
                                                                    'tanggal' => $v_rp['tgl_retur'],
                                                                    'nota' => empty($v_rp['no_retur']) ? '-' : $v_rp['no_retur'],
                                                                    'sj' => null,
                                                                    'barang' => $v_rpd['d_barang']['nama'],
                                                                    'zak' => ceil($_jml_pindah / 50),
                                                                    'jumlah' => $_jml_pindah,
                                                                    'harga' => $harga_beli,
                                                                    'total' => $total_supplier
                                                                );
                                                            } else {
                                                                $data['inti'][ $key ]['zak'] += ceil($_jml_pindah / 50);
                                                                $data['inti'][ $key ]['jumlah'] += $_jml_pindah;
                                                                $data['inti'][ $key ]['total'] += $total_supplier;
                                                            }

                                                            $_jml_pindah = 0;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                $sql = "
                                    select kp.no_order, kp.jenis_kirim, kp.ongkos_angkut, tp.tgl_terima, dkp.* 
                                    from det_kirim_pakan dkp 
                                    right join
                                        kirim_pakan kp 
                                        on
                                            dkp.id_header = kp.id
                                    right join
                                        terima_pakan tp 
                                        on
                                            kp.id = tp.id_kirim_pakan
                                    where
                                        kp.no_sj = '".$v_kp['no_sj']."' and
                                        dkp.item = '".$v_rpd['item']."'
                                    order by
                                        tp.tgl_terima desc,
                                        kp.no_order desc
                                ";

                                $m_kp = new \Model\Storage\KirimPakan_model();
                                $d_kp = $m_kp->hydrateRaw( $sql );

                                if ( $d_kp->count() > 0 ) {
                                    $d_kp = $d_kp->toArray();

                                    $idx_kp = 0;

                                    $jml_pindah = $jml_retur;
                                    while ( $jml_pindah > 0 && isset($d_kp[ $idx_kp ]) ) {
                                        $jumlah = 0;
                                        $hrg_oa = $d_kp[ $idx_kp ]['ongkos_angkut'];

                                        if ( $hrg_oa == 0 ) {
                                            if ( $v_kp['jenis_kirim'] == 'opkp' ) {
                                                $m_kp = new \Model\Storage\KirimPakan_model();
                                                $sql = "
                                                    EXEC get_data_oa_pakan_new @no_order = '".$v_kp['no_order']."', @item = '".$v_rpd['item']."', @jumlah = ".$jml_pindah.", @no_sj_asal = '".$d_kp[ $idx_kp ]['no_sj_asal']."', @retur = 1
                                                ";
                                                // cetak_r($sql);
                                                $d_oa_pindah_pakan = $m_kp->hydrateRaw( $sql );

                                                if ( $d_oa_pindah_pakan->count() > 0 ) {
                                                    $d_oa_pindah_pakan = $d_oa_pindah_pakan->toArray();

                                                    foreach ($d_oa_pindah_pakan as $key => $value) {
                                                        if ( $jml_pindah <= $value['jumlah'] ) {
                                                            $jumlah = $jml_pindah;
                                                            $jml_pindah = 0;
                                                            $value['jumlah'] = $value['jumlah'] - $jml_pindah;
                                                        } else {
                                                            $jumlah = $value['jumlah'];
                                                            $jml_pindah = $jml_pindah - $value['jumlah'];
                                                            $value['jumlah'] = 0;
                                                        }

                                                        if ( $jumlah > 0 ) {
                                                            $hrg_oa = $value['oa'];

                                                            $key = $v_kp['no_sj'].' | '.$v_det['d_barang']['kode'].' | '.$hrg_oa;

                                                            if ( !isset( $data['ongkos_angkut'][ $v_rp['tgl_retur'] ][ $v_kp['no_polisi'] ][ $key ] ) ) {
                                                                $data['ongkos_angkut'][ $v_rp['tgl_retur'] ][ $v_kp['no_polisi'] ][ $key ] = array(
                                                                    'nota' => $v_kp['no_sj'],
                                                                    'tanggal' => $v_rp['tgl_retur'],
                                                                    'nopol' => $v_kp['ekspedisi'].' - '.$v_kp['no_polisi'],
                                                                    'barang' => $v_rpd['d_barang']['nama'],
                                                                    'zak' => ceil($jumlah / 50),
                                                                    'jumlah' => $jumlah,
                                                                    'harga' => $hrg_oa,
                                                                    'total' => $hrg_oa * $jumlah
                                                                );
                                                            } else {
                                                                $data['ongkos_angkut'][ $v_rp['tgl_retur'] ][ $v_kp['no_polisi'] ][ $key ]['zak'] += ceil($jumlah / 50);
                                                                $data['ongkos_angkut'][ $v_rp['tgl_retur'] ][ $v_kp['no_polisi'] ][ $key ]['jumlah'] += $jumlah;
                                                                $data['ongkos_angkut'][ $v_rp['tgl_retur'] ][ $v_kp['no_polisi'] ][ $key ]['total'] += $hrg_oa * $jumlah;
                                                            }

                                                            if ( !empty($data['ongkos_angkut']) ) {
                                                                ksort($data['ongkos_angkut']);
                                                            }
                                                        }
                                                    }
                                                }
                                            } else {
                                                $m_conf = new \Model\Storage\Conf();
                                                $sql = "
                                                    select
                                                        kp.ongkos_angkut
                                                    from det_kirim_pakan dkp
                                                    left join
                                                        kirim_pakan kp
                                                        on
                                                            dkp.id_header = kp.id
                                                    where
                                                        kp.no_order = '".$v_kp['no_order']."'
                                                ";
                                                $d_kp = $m_conf->hydrateRaw( $sql );

                                                $ongkos_angkut = 0;
                                                if ( $d_kp->count() > 0 ) {
                                                    $ongkos_angkut = $d_kp->toArray()[0]['ongkos_angkut'];
                                                }

                                                $key = $v_kp['no_sj'].' | '.$v_det['d_barang']['kode'].' | '.$hrg_oa;
                                                $data['ongkos_angkut'][ $v_rp['tgl_retur'] ][ $v_kp['no_polisi'] ][ $key ] = array(
                                                    'nota' => $v_kp['no_sj'],
                                                    'tanggal' => $v_rp['tgl_retur'],
                                                    'nopol' => $v_kp['ekspedisi'].' - '.$v_kp['no_polisi'],
                                                    'barang' => $v_rpd['d_barang']['nama'],
                                                    'zak' => ceil($jml_pindah / 50),
                                                    'jumlah' => $jml_pindah,
                                                    'harga' => $ongkos_angkut,
                                                    'total' => $ongkos_angkut * $jml_pindah
                                                );

                                                $jml_pindah = 0;
                                            }

                                            $idx_kp++;
                                        } else {
                                            if ( $jml_pindah <= $d_kp[ $idx_kp ]['jumlah'] ) {
                                                $jumlah = $jml_pindah;
                                                $jml_pindah = 0;
                                                $d_kp[ $idx_kp ]['jumlah'] = $d_kp[ $idx_kp ]['jumlah'] - $jml_pindah;
                                            } else {
                                                $jumlah = $d_kp[ $idx_kp ]['jumlah'];
                                                $jml_pindah -= $d_kp[ $idx_kp ]['jumlah'];
                                                $d_kp[ $idx_kp ]['jumlah'] = 0;

                                                $idx_kp++;
                                            }

                                            if ( $jumlah > 0 ) {
                                                $key = $v_kp['no_sj'].' | '.$v_det['d_barang']['kode'].' | '.$hrg_oa;

                                                if ( !isset( $data['ongkos_angkut'][ $v_rp['tgl_retur'] ][ $v_kp['no_polisi'] ][ $key ] ) ) {
                                                    $data['ongkos_angkut'][ $v_rp['tgl_retur'] ][ $v_kp['no_polisi'] ][ $key ] = array(
                                                        'nota' => $v_kp['no_sj'],
                                                        'tanggal' => $v_rp['tgl_retur'],
                                                        'nopol' => $v_kp['ekspedisi'].' - '.$v_kp['no_polisi'],
                                                        'barang' => $v_rpd['d_barang']['nama'],
                                                        'zak' => ceil($jumlah / 50),
                                                        'jumlah' => $jumlah,
                                                        'harga' => $hrg_oa,
                                                        'total' => $hrg_oa * $jumlah
                                                    );
                                                } else {
                                                    $data['ongkos_angkut'][ $v_rp['tgl_retur'] ][ $v_kp['no_polisi'] ][ $key ]['zak'] += ceil($jumlah / 50);
                                                    $data['ongkos_angkut'][ $v_rp['tgl_retur'] ][ $v_kp['no_polisi'] ][ $key ]['jumlah'] += $jumlah;
                                                    $data['ongkos_angkut'][ $v_rp['tgl_retur'] ][ $v_kp['no_polisi'] ][ $key ]['total'] += $hrg_oa * $jumlah;
                                                }

                                                if ( !empty($data['ongkos_angkut']) ) {
                                                    ksort($data['ongkos_angkut']);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $data;
    }

    public function get_data_voadip($noreg)
    {
        $m_kv = new \Model\Storage\KirimVoadip_model();
        $d_kv = $m_kv->where('tujuan', $noreg)->where('jenis_tujuan', 'peternak')->orderBy('tgl_kirim', 'asc')->with(['detail'])->get();

        $data = null;
        if ( $d_kv->count() > 0 ) {
            $d_kv = $d_kv->toArray();
            foreach ($d_kv as $k_kv => $v_kv) {
                $m_tv = new \Model\Storage\TerimaVoadip_model();
                $d_tv = $m_tv->where('id_kirim_voadip', $v_kv['id'])->orderBy('id', 'desc')->first();


                if ( $d_tv ) {
                    if ( !empty($v_kv['detail']) ) {
                        foreach ($v_kv['detail'] as $k_kvd => $v_kvd) {
                            if ( $v_kvd['jumlah'] > 0 ) {
                                $_harga_jual = ($v_kvd['nilai_jual'] > 0 && $v_kvd['jumlah'] > 0) ? $v_kvd['nilai_jual'] / $v_kvd['jumlah'] : 0;
                                $harga_jual = $_harga_jual;
                                $harga_beli = ($v_kvd['nilai_beli'] > 0 && $v_kvd['jumlah'] > 0) ? $v_kvd['nilai_beli'] / $v_kvd['jumlah'] : 0;

                                if ( $harga_jual > 0 && $harga_beli > 0 ) {
                                    $m_tvd = new \Model\Storage\TerimaVoadipDetail_model();
                                    $d_tvd = $m_tvd->where('id_header', $d_tv->id)->where('item', $v_kvd['item'])->with(['d_barang'])->first();

                                    if ( $d_tvd ) {
                                        $total_jual = $d_tvd->jumlah * $harga_jual;
                                        $total_beli = $d_tvd->jumlah * $harga_beli;

                                        $key_jual = $harga_jual.'_'.$v_kv['no_sj'];
                                        $key_beli = $harga_beli.'_'.$v_kv['no_sj'];

                                        $data['plasma'][$key_jual] = array(
                                            'tanggal' => $v_kv['tgl_kirim'],
                                            'sj' => $v_kv['no_sj'],
                                            'barang' => $d_tvd->d_barang->nama,
                                            'jumlah' => $d_tvd->jumlah,
                                            'harga' => $harga_jual,
                                            'total' => $total_jual,
                                            'decimal' => $d_tvd->d_barang->desimal_harga,
                                            'tgl_trans' => $v_kv['tgl_kirim']
                                        );

                                        $data['inti'][$key_beli] = array(
                                            'tanggal' => $v_kv['tgl_kirim'],
                                            'sj' => $v_kv['no_sj'],
                                            'barang' => $d_tvd->d_barang->nama,
                                            'jumlah' => $d_tvd->jumlah,
                                            'harga' => $harga_beli,
                                            'total' => $total_beli,
                                            'decimal' => $d_tvd->d_barang->desimal_harga,
                                            'tgl_trans' => $v_kv['tgl_kirim']
                                        );
                                    }
                                } else {
                                    $m_dstokt = new \Model\Storage\DetStokTrans_model();
                                    $d_dstokt = $m_dstokt->where('kode_trans', trim($v_kv['no_order']))->where('kode_barang', trim($v_kvd['item']))->where('jumlah', '>', 0)->whereNotNull('id_header')->with(['d_barang'])->get();

                                    if ( $d_dstokt->count() > 0 ) {
                                        $d_dstokt = $d_dstokt->toArray();

                                        $_data = null;
                                        foreach ($d_dstokt as $k_dstokt => $v_dstokt) {
                                            $m_dstok = new \Model\Storage\DetStok_model();
                                            $d_dstok = $m_dstok->where('id', $v_dstokt['id_header'])->first();

                                            $harga_jual = $d_dstok->hrg_jual;
                                            $harga_beli = $d_dstok->hrg_beli;

                                            $total_jual = $v_dstokt['jumlah'] * $harga_jual;
                                            $total_beli = $v_dstokt['jumlah'] * $harga_beli;

                                            $key_jual = $v_kv['no_sj'].'_'.$v_kvd['item'].'_'.$harga_jual;
                                            $key_beli = $v_kv['no_sj'].'_'.$v_kvd['item'].'_'.$harga_beli;

                                            if ( !isset($data['plasma'][$key_jual]) ) {
                                                $data['plasma'][$key_jual] = array(
                                                    'tanggal' => $v_kv['tgl_kirim'],
                                                    'sj' => $v_kv['no_sj'],
                                                    'barang' => $v_dstokt['d_barang']['nama'],
                                                    'jumlah' => $v_dstokt['jumlah'],
                                                    'harga' => $harga_jual,
                                                    'total' => $total_jual,
                                                    'decimal' => $v_dstokt['d_barang']['desimal_harga'],
                                                    'tgl_trans' => $d_dstok->tgl_trans
                                                );
                                            } else {
                                                $data['plasma'][$key_jual]['jumlah'] += $v_dstokt['jumlah'];
                                                $data['plasma'][$key_jual]['total'] += $total_jual;
                                            }

                                            // krsort( $data['plasma'] );

                                            if ( !isset($data['inti'][$key_beli]) ) {
                                                $data['inti'][$key_beli] = array(
                                                    'tanggal' => $v_kv['tgl_kirim'],
                                                    'sj' => $v_kv['no_sj'],
                                                    'barang' => $v_dstokt['d_barang']['nama'],
                                                    'jumlah' => $v_dstokt['jumlah'],
                                                    'harga' => $harga_beli,
                                                    'total' => $total_beli,
                                                    'decimal' => $v_dstokt['d_barang']['desimal_harga'],
                                                    'tgl_trans' => $d_dstok->tgl_trans
                                                );
                                            } else {
                                                $data['inti'][$key_beli]['jumlah'] += $v_dstokt['jumlah'];
                                                $data['inti'][$key_beli]['total'] += $total_beli;
                                            }

                                            // krsort( $data['inti'] );
                                        }

                                        // if ( !empty($_data) ) {
                                        //     $data = $_data;
                                        //     // foreach ($_data as $k_jrhpp => $v_jrhpp) {
                                        //     //     foreach ($v_jrhpp as $key => $value) {
                                        //     //         $data[ $k_jrhpp ][] = $value;
                                        //     //     }
                                        //     // }
                                        // }
                                    }
                                }

                                // if ( $v_kv['tgl_kirim'] < '2022-09-07' ) {
                                //     $m_tvd = new \Model\Storage\TerimaVoadipDetail_model();
                                //     $d_tvd = $m_tvd->where('id_header', $d_tv->id)->where('item', $v_kvd['item'])->with(['d_barang'])->first();

                                //     if ( $d_tvd ) {
                                //         $total_jual = $d_tvd->jumlah * $harga_jual;
                                //         $total_beli = $d_tvd->jumlah * $harga_beli;

                                //         $data['plasma'][] = array(
                                //             'tanggal' => $v_kv['tgl_kirim'],
                                //             'sj' => $v_kv['no_sj'],
                                //             'barang' => $d_tvd->d_barang->nama,
                                //             'jumlah' => $d_tvd->jumlah,
                                //             'harga' => $harga_jual,
                                //             'total' => $total_jual,
                                //             'decimal' => $d_tvd->d_barang->desimal_harga
                                //         );

                                //         $data['inti'][] = array(
                                //             'tanggal' => $v_kv['tgl_kirim'],
                                //             'sj' => $v_kv['no_sj'],
                                //             'barang' => $d_tvd->d_barang->nama,
                                //             'jumlah' => $d_tvd->jumlah,
                                //             'harga' => $harga_beli,
                                //             'total' => $total_beli,
                                //             'decimal' => $d_tvd->d_barang->desimal_harga
                                //         );
                                //     }
                                // } else {
                                //     $m_dstokt = new \Model\Storage\DetStokTrans_model();
                                //     $d_dstokt = $m_dstokt->where('kode_trans', trim($v_kv['no_order']))->where('kode_barang', trim($v_kvd['item']))->where('jumlah', '>', 0)->whereNotNull('id_header')->with(['d_barang'])->get();

                                //     if ( $d_dstokt->count() > 0 ) {
                                //         $d_dstokt = $d_dstokt->toArray();

                                //         $_data = null;
                                //         foreach ($d_dstokt as $k_dstokt => $v_dstokt) {
                                //             $m_dstok = new \Model\Storage\DetStok_model();
                                //             $d_dstok = $m_dstok->where('id', $v_dstokt['id_header'])->first();

                                //             $harga_jual = $d_dstok->hrg_jual;
                                //             $harga_beli = $d_dstok->hrg_beli;

                                //             $total_jual = $v_dstokt['jumlah'] * $harga_jual;
                                //             $total_beli = $v_dstokt['jumlah'] * $harga_beli;

                                //             $key_jual = $harga_jual.' | '.$v_kv['no_sj'];
                                //             $key_beli = $harga_beli.' | '.$v_kv['no_sj'];

                                //             if ( !isset($_data['plasma'][$key_jual]) ) {
                                //                 $_data['plasma'][$key_jual] = array(
                                //                     'tanggal' => $v_kv['tgl_kirim'],
                                //                     'sj' => $v_kv['no_sj'],
                                //                     'barang' => $v_dstokt['d_barang']['nama'],
                                //                     'jumlah' => $v_dstokt['jumlah'],
                                //                     'harga' => $harga_jual,
                                //                     'total' => $total_jual,
                                //                     'decimal' => $v_dstokt['d_barang']['desimal_harga']
                                //                 );
                                //             } else {
                                //                 $_data['plasma'][$key_jual]['jumlah'] += $v_dstokt['jumlah'];
                                //                 $_data['plasma'][$key_jual]['total'] += $total_jual;
                                //             }

                                //             if ( !isset($_data['inti'][$key_beli]) ) {
                                //                 $_data['inti'][$key_beli] = array(
                                //                     'tanggal' => $v_kv['tgl_kirim'],
                                //                     'sj' => $v_kv['no_sj'],
                                //                     'barang' => $v_dstokt['d_barang']['nama'],
                                //                     'jumlah' => $v_dstokt['jumlah'],
                                //                     'harga' => $harga_beli,
                                //                     'total' => $total_beli,
                                //                     'decimal' => $v_dstokt['d_barang']['desimal_harga']
                                //                 );
                                //             } else {
                                //                 $_data['inti'][$key_beli]['jumlah'] += $v_dstokt['jumlah'];
                                //                 $_data['inti'][$key_beli]['total'] += $total_beli;
                                //             }
                                //         }

                                //         if ( !empty($_data) ) {
                                //             foreach ($_data as $k_jrhpp => $v_jrhpp) {
                                //                 foreach ($v_jrhpp as $key => $value) {
                                //                     $data[ $k_jrhpp ][] = $value;
                                //                 }
                                //             }
                                //         }
                                //     }
                                // }
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }

    // public function get_data_retur_voadip($noreg)
    public function get_data_retur_voadip($noreg, $data_voadip)
    {
        $data = null;

        // $data_voadip_inti = null;
        // foreach ($data_voadip['inti'] as $key => $value) {
        //     $k_key = $key.'_'.str_replace('-', '', $value['tgl_trans']);

        //     $data_voadip_inti[ $k_key ] = $value;
        // }

        // $data_voadip_plasma = null;
        // foreach ($data_voadip['plasma'] as $key => $value) {
        //     $k_key = $key.'_'.str_replace('-', '', $value['tgl_trans']);

        //     $data_voadip_plasma[ $k_key ] = $value;
        // }

        // $data_voadip = $this->get_data_voadip( $noreg );

        // $data_voadip_inti = $data_voadip['inti'];
        // $data_voadip_plasma = $data_voadip['plasma'];

        // krsort( $data_voadip_inti );
        // krsort( $data_voadip_plasma );

        // cetak_r($data_voadip_inti, 1);

        $m_kv = new \Model\Storage\KirimVoadip_model();
        $d_kv = $m_kv->where('tujuan', $noreg)->where('jenis_tujuan', 'peternak')->with(['detail'])->get();

        if ( $d_kv->count() > 0 ) {
            $d_kv = $d_kv->toArray();

            foreach ($d_kv as $k_kv => $v_kv) {
                $m_rv = new \Model\Storage\ReturVoadip_model();
                $d_rv = $m_rv->where('no_order', $v_kv['no_order'])->with(['det_retur_voadip'])->get()->toArray();

                if ( count($d_rv) > 0 ) {
                    foreach ($d_rv as $k_rv => $v_rv) {
                        foreach ($v_rv['det_retur_voadip'] as $k_det => $v_det) {
                            if ( $v_det['jumlah'] > 0 ) {
                                $jumlah_retur = $v_det['jumlah'];

                                $arr_id = array();

                                while( $jumlah_retur > 0 ) {
                                    $sql_id = null;
                                    if ( !empty( $arr_id ) ) {
                                        $sql_id = "where data.id not in ('".implode("', '", $arr_id)."')";
                                    }

                                    $m_conf = new \Model\Storage\Conf();
                                    $sql = "
                                        select * from 
                                        (
                                            select 
                                                ds.id,
                                                ds.tgl_trans,
                                                ds.kode_gudang,
                                                ds.kode_barang,
                                                sum(ds.jumlah) as jumlah,
                                                ds.hrg_jual,
                                                ds.hrg_beli,
                                                ds.kode_trans,
                                                ds.jenis_barang,
                                                ds.jenis_trans
                                            from det_stok ds
                                            right join
                                                stok s
                                                on
                                                    ds.id_header = s.id
                                            where
                                                s.periode = '".$v_rv['tgl_retur']."' and
                                                ds.kode_trans = '".$v_kv['no_order']."' and
                                                ds.kode_barang = '".$v_det['item']."'
                                            group by
                                                ds.id,
                                                ds.tgl_trans,
                                                ds.kode_gudang,
                                                ds.kode_barang,
                                                ds.hrg_jual,
                                                ds.hrg_beli,
                                                ds.kode_trans,
                                                ds.jenis_barang,
                                                ds.jenis_trans
                                        ) data
                                        ".$sql_id."
                                    ";
                                    $d_ds = $m_conf->hydrateRaw( $sql );

                                    if ( $d_ds->count() > 0 ) {
                                        $d_ds = $d_ds->toArray()[0];

                                        $jumlah_simpan = 0;
                                        if ( $d_ds['jumlah'] < $jumlah_retur ) {
                                            $jumlah_simpan = $d_ds['jumlah'];
                                            $jumlah_retur = $jumlah_retur - $d_ds['jumlah'];

                                            array_push( $arr_id, $d_ds['id'] );
                                        } else {
                                            $jumlah_simpan = $jumlah_retur;
                                            $jumlah_retur = 0;
                                        }

                                        $harga_beli = $d_ds['hrg_beli'];
                                        $total_beli = $harga_beli * $jumlah_simpan;

                                        $key_inti = $v_rv['no_retur'].'_'.$v_det['item'].'_'.$harga_beli;
                                        if ( !isset($data['inti'][ $key_inti ]) ) {
                                            $data['inti'][ $key_inti ] = array(
                                                'tanggal' => $v_rv['tgl_retur'],
                                                'no_retur' => empty($v_rv['no_retur']) ? '-' : $v_rv['no_retur'],
                                                'sj' => null,
                                                'barang' => $v_det['d_barang']['nama'],
                                                'jumlah' => $jumlah_simpan,
                                                'harga' => $harga_beli,
                                                'total' => $total_beli,
                                                'decimal' => $v_det['d_barang']['desimal_harga']
                                            );
                                        } else {
                                            $data['inti'][ $key_inti ]['jumlah'] += $jumlah_simpan;
                                            $data['inti'][ $key_inti ]['total'] += $total_beli;
                                        }

                                        $harga_jual = $d_ds['hrg_jual'];
                                        $total_jual = $harga_jual * $jumlah_simpan;

                                        $key_plasma = $v_rv['no_retur'].'_'.$v_det['item'].'_'.$harga_jual;
                                        if ( !isset($data['plasma'][ $key_plasma ]) ) {
                                            $data['plasma'][ $key_plasma ] = array(
                                                'tanggal' => $v_rv['tgl_retur'],
                                                'no_retur' => empty($v_rv['no_retur']) ? '-' : $v_rv['no_retur'],
                                                'sj' => null,
                                                'barang' => $v_det['d_barang']['nama'],
                                                'jumlah' => $jumlah_simpan,
                                                'harga' => $harga_jual,
                                                'total' => $total_jual,
                                                'decimal' => $v_det['d_barang']['desimal_harga']
                                            );
                                        } else {
                                            $data['plasma'][ $key_plasma ]['jumlah'] += $jumlah_simpan;
                                            $data['plasma'][ $key_plasma ]['total'] += $total_jual;
                                        }
                                    } else {
                                        $jumlah_retur = 0;
                                    }
                                }

                                // foreach ($data_voadip_inti as $k_inti => $v_inti) {
                                //     if ( stristr($v_inti['sj'], str_replace('OP', 'SJ', $v_kv['no_order'])) !== false && 
                                //          stristr($v_inti['barang'], $v_det['d_barang']['nama']) !== false ) {

                                //         if ( $jumlah_retur_inti > 0 ) {
                                //             $key = $v_inti['sj'].' | '.$v_inti['harga'].' | '.$v_det['d_barang']['kode'];

                                //             $jumlah_simpan = ($v_inti['jumlah'] > $jumlah_retur_inti) ? $jumlah_retur_inti : $v_inti['jumlah'];

                                //             $harga = $v_inti['harga'];
                                //             $total = $harga * $jumlah_simpan;

                                //             $data['inti'][ $key ] = array(
                                //                 'tanggal' => $v_rv['tgl_retur'],
                                //                 'no_retur' => empty($v_rv['no_retur']) ? '-' : $v_rv['no_retur'],
                                //                 'sj' => null,
                                //                 'barang' => $v_det['d_barang']['nama'],
                                //                 'jumlah' => $jumlah_simpan,
                                //                 'harga' => $harga,
                                //                 'total' => $total,
                                //                 'decimal' => $v_det['d_barang']['desimal_harga']
                                //             );

                                //             $jumlah_retur_inti -= $jumlah_simpan;
                                //         }
                                //     }
                                // }

                                // $jumlah_retur_plasma = $v_det['jumlah'];
                                // foreach ($data_voadip_plasma as $k_plasma => $v_plasma) {
                                //     if ( stristr($v_plasma['sj'], str_replace('OP', 'SJ', $v_kv['no_order'])) !== false && 
                                //          stristr($v_plasma['barang'], $v_det['d_barang']['nama']) !== false ) {

                                //         // if ( stristr($v_plasma['barang'], 'LEVOCAP @ 100 GR') !== false ) {
                                //         //     cetak_r( 'plasma' );
                                //         //     cetak_r( $v_plasma );
                                //         // }

                                //         if ( $jumlah_retur_plasma > 0 ) {
                                //             $key = $v_plasma['sj'].' | '.$v_plasma['harga'].' | '.$v_det['d_barang']['kode'];

                                //             $jumlah_simpan = ($v_plasma['jumlah'] > $jumlah_retur_plasma) ? $jumlah_retur_plasma : $v_plasma['jumlah'];

                                //             $harga = $v_plasma['harga'];
                                //             $total = $harga * $jumlah_simpan;

                                //             $data['plasma'][ $key ] = array(
                                //                 'tanggal' => $v_rv['tgl_retur'],
                                //                 'no_retur' => empty($v_rv['no_retur']) ? '-' : $v_rv['no_retur'],
                                //                 'sj' => null,
                                //                 'barang' => $v_det['d_barang']['nama'],
                                //                 'jumlah' => $jumlah_simpan,
                                //                 'harga' => $harga,
                                //                 'total' => $total,
                                //                 'decimal' => $v_det['d_barang']['desimal_harga']
                                //             );

                                //             $jumlah_retur_plasma -= $jumlah_simpan;
                                //         }
                                //     }
                                // }

                                // $harga_jual = ($v_det['nilai_jual'] > 0) ? $v_det['nilai_jual'] / $v_det['jumlah'] : 0;
                                // $harga_beli = ($v_det['nilai_beli'] > 0) ? $v_det['nilai_beli'] / $v_det['jumlah'] : 0;

                                // if ( $harga_jual <= 0 && $harga_beli <= 0 ) {
                                //     foreach ($v_kv['detail'] as $k_kvd => $v_kvd) {
                                //         if ( $v_kvd['item'] == $v_det['item'] ) {
                                //             $_harga_jual = ($v_kvd['jumlah'] > 0 && $v_kvd['nilai_jual'] > 0) ? round($v_kvd['nilai_jual'] / $v_kvd['jumlah']) : 0;
                                //             $harga_jual = $_harga_jual;

                                //             // $dua_angka = (int) substr($_harga_jual, -2);
                                //             // if ( $dua_angka > 50 ) {
                                //             //     $harga_jual = substr($_harga_jual, 0, (strlen($_harga_jual)-2)).'50';
                                //             // } else {
                                //             //     $harga_jual = substr($_harga_jual, 0, (strlen($_harga_jual)-2)).'00';
                                //             // }

                                //             $harga_beli = ($v_kvd['jumlah'] > 0 && $v_kvd['nilai_beli'] > 0) ? $v_kvd['nilai_beli'] / $v_kvd['jumlah'] : 0;
                                //         }
                                //     }
                                // }

                                // $total_jual = $harga_jual * $v_det['jumlah'];
                                // $total_beli = $harga_beli * $v_det['jumlah'];

                                // if ( $harga_beli > 0 && $harga_jual > 0 ) {
                                //     $data['plasma'][] = array(
                                //         'tanggal' => $v_rv['tgl_retur'],
                                //         'no_retur' => empty($v_rv['no_retur']) ? '-' : $v_rv['no_retur'],
                                //         'sj' => null,
                                //         'barang' => $v_det['d_barang']['nama'],
                                //         'jumlah' => $v_det['jumlah'],
                                //         'harga' => $harga_jual,
                                //         'total' => $total_jual,
                                //         'decimal' => $v_det['d_barang']['desimal_harga']
                                //     );

                                //     $data['inti'][] = array(
                                //         'tanggal' => $v_rv['tgl_retur'],
                                //         'no_retur' => empty($v_rv['no_retur']) ? '-' : $v_rv['no_retur'],
                                //         'sj' => null,
                                //         'barang' => $v_det['d_barang']['nama'],
                                //         'jumlah' => $v_det['jumlah'],
                                //         'harga' => $harga_beli,
                                //         'total' => $total_beli,
                                //         'decimal' => $v_det['d_barang']['desimal_harga']
                                //     );
                                // } else {
                                //     // $sql = "
                                //     //     select ds.hrg_beli as hrg_beli, ds.hrg_jual as hrg_jual, dst.*, b.nama as nama from det_stok_trans dst 
                                //     //     left join
                                //     //         det_stok ds 
                                //     //         on
                                //     //             dst.id_header = ds.id 
                                //     //     left join
                                //     //         barang b
                                //     //         on
                                //     //             dst.kode_barang = b.kode 
                                //     //     where
                                //     //         dst.kode_trans = '".$v_kv['no_order']."' and
                                //     //         dst.kode_barang = '".$v_det['item']."'
                                //     //     group by

                                //     //     order by
                                //     //         ds.tgl_trans desc,
                                //     //         ds.kode_trans desc,
                                //     //         ds.jenis_trans asc
                                //     // ";

                                //     $sql = "
                                //         select ds.*, b.nama as nama, b.desimal_harga as desimal_harga from det_stok ds 
                                //         left join
                                //             stok s
                                //             on
                                //                 ds.id_header = s.id
                                //         left join
                                //             barang b
                                //             on
                                //                 ds.kode_barang = b.kode 
                                //         where
                                //             ds.kode_trans = '".$v_kv['no_order']."' and
                                //             ds.kode_barang = '".$v_det['item']."'
                                //         order by
                                //             ds.tgl_trans desc,
                                //             ds.kode_trans desc,
                                //             ds.jenis_trans asc
                                //     ";

                                //     $m_dstokt = new \Model\Storage\DetStokTrans_model();
                                //     // $d_dstokt = $m_dstokt->where('kode_trans', $v_kv['no_order'])->where('kode_barang', $v_det['item'])->with(['d_barang'])->orderBy('id', 'desc')->get();
                                //     $d_dstokt = $m_dstokt->hydrateRaw( $sql );

                                //     if ( $d_dstokt->count() > 0 ) {
                                //         $jml_keluar = $v_det['jumlah'];

                                //         $d_dstokt = $d_dstokt->toArray();
                                        
                                //         foreach ($d_dstokt as $k_dstokt => $v_dstokt) {
                                //             if ( $jml_keluar > 0 ) {
                                //                 $jumlah = 0;
                                //                 if ( $jml_keluar > $v_dstokt['jumlah'] ) {
                                //                     $jumlah = $v_dstokt['jumlah'];
                                //                     $jml_keluar -= $v_dstokt['jumlah'];
                                //                 } else {
                                //                     $jumlah = $jml_keluar;
                                //                     $jml_keluar = 0;
                                //                 }

                                //                 // $m_dstok = new \Model\Storage\DetStok_model();
                                //                 // $d_dstok = $m_dstok->where('id', $v_dstokt['id_header'])->first();

                                //                 // $harga_jual = $d_dstok->hrg_jual;
                                //                 // $harga_beli = $d_dstok->hrg_beli;

                                //                 // $total_jual = $jumlah * $harga_jual;
                                //                 // $total_beli = $jumlah * $harga_beli;

                                //                 // $data['plasma'][] = array(
                                //                 //     'tanggal' => $v_rv['tgl_retur'],
                                //                 //     'no_retur' => empty($v_rv['no_retur']) ? '-' : $v_rv['no_retur'],
                                //                 //     'sj' => $v_kv['no_sj'],
                                //                 //     'barang' => $v_dstokt['d_barang']['nama'],
                                //                 //     'jumlah' => $jumlah,
                                //                 //     'harga' => $harga_jual,
                                //                 //     'total' => $total_jual
                                //                 // );

                                //                 // $data['inti'][] = array(
                                //                 //     'tanggal' => $v_rv['tgl_retur'],
                                //                 //     'no_retur' => empty($v_rv['no_retur']) ? '-' : $v_rv['no_retur'],
                                //                 //     'sj' => $v_kv['no_sj'],
                                //                 //     'barang' => $v_dstokt['d_barang']['nama'],
                                //                 //     'jumlah' => $jumlah,
                                //                 //     'harga' => $harga_beli,
                                //                 //     'total' => $total_beli
                                //                 // );

                                //                 $harga_jual = $v_dstokt['hrg_jual'];
                                //                 $harga_beli = $v_dstokt['hrg_beli'];

                                //                 $total_jual = $jumlah * $harga_jual;
                                //                 $total_beli = $jumlah * $harga_beli;

                                //                 $key_plasma = $v_rv['tgl_retur'].'-'.$v_rv['no_retur'].'-'.$harga_jual.'-'.$v_dstokt['nama'];
                                //                 $key_inti = $v_rv['tgl_retur'].'-'.$v_rv['no_retur'].'-'.$harga_beli.'-'.$v_dstokt['nama'];

                                //                 if ( !isset($data['plasma'][$key_plasma]) ) {
                                //                     $data['plasma'][$key_plasma] = array(
                                //                         'tanggal' => $v_rv['tgl_retur'],
                                //                         'no_retur' => empty($v_rv['no_retur']) ? '-' : $v_rv['no_retur'],
                                //                         'sj' => $v_kv['no_sj'],
                                //                         'barang' => $v_dstokt['nama'],
                                //                         'jumlah' => $jumlah,
                                //                         'harga' => $harga_jual,
                                //                         'total' => $total_jual,
                                //                         'decimal' => $v_dstokt['desimal_harga']
                                //                     );
                                //                 } else {
                                //                     $data['plasma'][$key_plasma]['jumlah'] += $jumlah;
                                //                     $data['plasma'][$key_plasma]['total'] += $total_jual;
                                //                 }

                                //                 if ( !isset($data['inti'][$key_inti]) ) {
                                //                     $data['inti'][$key_inti] = array(
                                //                         'tanggal' => $v_rv['tgl_retur'],
                                //                         'no_retur' => empty($v_rv['no_retur']) ? '-' : $v_rv['no_retur'],
                                //                         'sj' => $v_kv['no_sj'],
                                //                         'barang' => $v_dstokt['nama'],
                                //                         'jumlah' => $jumlah,
                                //                         'harga' => $harga_beli,
                                //                         'total' => $total_beli,
                                //                         'decimal' => $v_dstokt['desimal_harga']
                                //                     );
                                //                 } else {
                                //                     $data['inti'][$key_inti]['jumlah'] += $jumlah;
                                //                     $data['inti'][$key_inti]['total'] += $total_beli;
                                //                 }
                                //             }
                                //         }
                                //     } else {
                                //         $data['plasma'][] = array(
                                //             'tanggal' => $v_rv['tgl_retur'],
                                //             'no_retur' => empty($v_rv['no_retur']) ? '-' : $v_rv['no_retur'],
                                //             'sj' => null,
                                //             'barang' => $v_det['d_barang']['nama'],
                                //             'jumlah' => $v_det['jumlah'],
                                //             'harga' => $harga_jual,
                                //             'total' => $total_jual,
                                //             'decimal' => $v_det['d_barang']['desimal_harga']
                                //         );

                                //         $data['inti'][] = array(
                                //             'tanggal' => $v_rv['tgl_retur'],
                                //             'no_retur' => empty($v_rv['no_retur']) ? '-' : $v_rv['no_retur'],
                                //             'sj' => null,
                                //             'barang' => $v_det['d_barang']['nama'],
                                //             'jumlah' => $v_det['jumlah'],
                                //             'harga' => $harga_beli,
                                //             'total' => $total_beli,
                                //             'decimal' => $v_det['d_barang']['desimal_harga']
                                //         );
                                //     }
                                // }

                                // // $harga_jual = 0;
                                // // $harga_beli = 0;
                                // // foreach ($v_kv['detail'] as $k_kvd => $v_kvd) {
                                // //     if ( $v_kvd['item'] == $v_det['item'] ) {
                                // //         $_harga_jual = ($v_kvd['jumlah'] > 0 && $v_kvd['nilai_jual'] > 0) ? round($v_kvd['nilai_jual'] / $v_kvd['jumlah']) : 0;
                                // //         $harga_jual = $_harga_jual;

                                // //         // $dua_angka = (int) substr($_harga_jual, -2);
                                // //         // if ( $dua_angka > 50 ) {
                                // //         //     $harga_jual = substr($_harga_jual, 0, (strlen($_harga_jual)-2)).'50';
                                // //         // } else {
                                // //         //     $harga_jual = substr($_harga_jual, 0, (strlen($_harga_jual)-2)).'00';
                                // //         // }

                                // //         // $harga_beli = ($v_kvd['jumlah'] > 0 && $v_kvd['nilai_beli'] > 0) ? $v_kvd['nilai_beli'] / $v_kvd['jumlah'] : 0;
                                // //     }
                                // // }

                                // // $total_jual = $harga_jual * $v_det['jumlah'];
                                // // $total_beli = $harga_beli * $v_det['jumlah'];

                                // // $data['plasma'][] = array(
                                // //     'tanggal' => $v_rv['tgl_retur'],
                                // //     'no_retur' => empty($v_rv['no_retur']) ? '-' : $v_rv['no_retur'],
                                // //     'sj' => null,
                                // //     'barang' => $v_det['d_barang']['nama'],
                                // //     'jumlah' => $v_det['jumlah'],
                                // //     'harga' => $harga_jual,
                                // //     'total' => $total_jual
                                // // );

                                // // $data['inti'][] = array(
                                // //     'tanggal' => $v_rv['tgl_retur'],
                                // //     'no_retur' => empty($v_rv['no_retur']) ? '-' : $v_rv['no_retur'],
                                // //     'sj' => null,
                                // //     'barang' => $v_det['d_barang']['nama'],
                                // //     'jumlah' => $v_det['jumlah'],
                                // //     'harga' => $harga_beli,
                                // //     'total' => $total_beli
                                // // );
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }

    public function get_data_rpah($noreg, $populasi, $total_jumlah_pakan, $tgl_docin)
    {
        $data = null;

        // cetak_r( $total_jumlah_pakan );

        $sapronak_kesepakatan = $this->get_harga_kontrak( $noreg );

        $harga_sepakat = null;
        $hitung_budidaya_item = null;
        if ( !empty($sapronak_kesepakatan) ) {
            $harga_sepakat = $sapronak_kesepakatan['harga_sepakat'];
            $hitung_budidaya_item = $sapronak_kesepakatan['hitung_budidaya_item'];
        }

        $m_drpah = new \Model\Storage\DetRpah_model();
        $d_drpah = $m_drpah->where('noreg', $noreg)->with(['data_real_sj'])->get()->toArray();

        $total_tonase = 0;
        $total_ekor = 0;
        $total_ekor_sj = 0;
        $total_tonase_sj = 0;
        if ( count($d_drpah) > 0 ) {
            $_data_real = null;
            foreach ($d_drpah as $k_det => $v_det) { 
                $m_rpah = new \Model\Storage\Rpah_model();
                $d_rpah = $m_rpah->where('id', $v_det['id_rpah'])->orderBy('id', 'desc')->first();

                $m_real_sj = new \Model\Storage\RealSJ_model();
                $d_real_sj = $m_real_sj->where('noreg', $noreg)->where('tgl_panen', $d_rpah->tgl_panen)->orderBy('id', 'desc')->first();

                foreach ($v_det['data_real_sj'] as $k_drs => $v_drs) {
                    if ( $d_real_sj ) {
                        if ( $d_real_sj->id == $v_drs['id_header'] ) {
                            $jenis_plg = null;
                            $m_conf = new \Model\Storage\Conf();
                            $sql = "
                                select top 1 p1.* from pelanggan p1
                                right join
                                    (select max(id) as id, nomor from pelanggan group by nomor) p2
                                    on
                                        p1.id = p2.id
                                where
                                    p1.nomor = '".$v_det['no_pelanggan']."'
                            ";
                            $d_conf = $m_conf->hydrateRaw($sql);

                            if ( $d_conf->count() > 0 ) {
                                $d_conf = $d_conf->toArray()[0];

                                $jenis_plg = $d_conf['jenis'];
                            }

                            if ( $v_drs['tonase'] > 0 ) {
                                $harga_pasar = ($v_drs['harga'] > 0) ? $v_drs['harga'] : 0;
                                $harga_kontrak = 0;

                                $idx = 0;
                                foreach ($harga_sepakat as $k => $val) {
                                    if ( $val['range_max'] > 0 ) {
                                        if ( stristr($jenis_plg, 'internal') === false ) {
                                            if ( $idx == 0 ) {
                                                if ( ($v_drs['bb'] >= $val['range_min']) && ($v_drs['bb'] <= $val['range_max']) ) {
                                                    $harga_kontrak = $val['harga'];
                                                }
                                            } else {
                                                if ( ($v_drs['bb'] >= $val['range_min']) && ($v_drs['bb'] <= $val['range_max']) ) {
                                                    $harga_kontrak = $val['harga'];
                                                }
                                            }
                                        }
                                    } else {
                                        if ( $v_drs['bb'] >= $val['range_min'] || stristr($jenis_plg, 'internal') !== false ) {
                                            $harga_kontrak = $val['harga'];
                                        }
                                    }

                                    // if ( $v_drs['no_do'] == 'DO/JBR/24/01494' ) {
                                    //     cetak_r( $val['range_min'].' | '.$val['range_max'] );
                                    //     cetak_r( $v_drs['bb'] );
                                    //     cetak_r( $harga_kontrak );
                                    // }

                                    $idx++;
                                }

                                if ( stristr($v_drs['jenis_ayam'], 'a') !== FALSE ) {
                                    if ( $harga_kontrak > $harga_pasar ) {
                                        $harga_kontrak = $harga_pasar;
                                    }
                                }

                                $tonase = $v_drs['tonase'];

                                $total_kontrak = $harga_kontrak * $tonase;
                                $total_pasar = $v_drs['harga'] * $tonase;
                                $selisih = $harga_pasar - $harga_kontrak;
                                $insentif = 0;
                                if ( $selisih > 0 ) {
                                    $insentif = $selisih * 0.35;
                                }
                                $total_insentif = $insentif * $tonase;

                                $key_do = str_replace('-', '', $d_rpah->tgl_panen).' - '.$v_det['no_do'];

                                $key = str_replace('-', '', $d_rpah->tgl_panen).' - '.$v_drs['id'].'-'.$v_det['no_do'].'-'.$v_det['pelanggan'].'-'.$v_drs['ekor'].'-'.$tonase.'-'.$v_drs['bb'].'-'.$harga_kontrak.'-'.$harga_pasar;

                                if ( !isset($_data_real[$key_do][ $key ]) ) {
                                    $_data_real[$key_do][ $key ] = array(
                                        'tanggal' => $d_rpah->tgl_panen,
                                        'do' => $v_det['no_do'],
                                        'pembeli' => $v_det['pelanggan'],
                                        'ekor' => $v_drs['ekor'],
                                        'tonase' => $tonase,
                                        'bb' => $v_drs['bb'],
                                        'hrg_kontrak' => $harga_kontrak,
                                        'total_kontrak' => $total_kontrak,
                                        'hrg_pasar' => $harga_pasar,
                                        'total_pasar' => $total_pasar,
                                        'selisih' => $selisih,
                                        'insentif' => $insentif,
                                        'total_insentif' => $total_insentif
                                    );

                                    ksort( $_data_real );

                                    $total_tonase += $tonase;
                                    $total_ekor += $v_drs['ekor'];

                                    $umur_panen = abs(selisihTanggal($d_real_sj->tgl_panen, $tgl_docin));

                                    $total_ekor_sj += $v_drs['ekor'];
                                    $total_tonase_sj += $umur_panen * $v_drs['ekor'];
                                }
                            }
                        }
                    }
                }
            }

            if ( !empty( $_data_real ) ) {
                ksort( $_data_real );

                foreach ($_data_real as $k_data => $v_data) {
                    foreach ($v_data as $key => $value) {
                        $data[ $key ] = $value;
                    }
                }
            }
        }

        // $rata_umur_panen = ($total_tonase_sj > 0 && $total_ekor_sj > 0) ? fDecimal($total_tonase_sj / $total_ekor_sj, 2) : 0;
        $rata_umur_panen = ($total_tonase_sj > 0 && $total_ekor_sj > 0) ? $total_tonase_sj / $total_ekor_sj : 0;

        // $umur_panen = abs(selisihTanggal($v_rsj['tgl_panen'], $tgl_docin));

        //                         $total_ekor_sj += $v_rsj['ekor'];
        //                         $total_tonase_sj += $umur_panen * $v_rsj['ekor'];

        $bonus_pasar = 0; $bonus_kematian = 0; $fcr = 0; $bb = 0; $deplesi = 0; $ip = 0;
        if ( !empty($data) ) {
            // ksort($data);

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

            foreach ($hitung_budidaya_item as $k => $val) {
                if ( $val['ip_akhir'] > 0 ) {
                    if ( ($ip >= $val['ip_awal']) && ($ip <= $val['ip_akhir']) ) {
                        $bonus_pasar = $val['bonus_ip'];
                        $bonus_kematian = $val['bonus_dh'];
                    }
                } else {
                    if ( $ip >= $val['ip_awal'] ) {
                        $bonus_pasar = $val['bonus_ip'];
                        $bonus_kematian = $val['bonus_dh'];
                    }
                }
            }

            foreach ($data as $k_data => $v_data) {
                $selisih = $data[$k_data]['selisih'];
                if ( $selisih > 0 ) {
                    $insentif = $selisih * $bonus_pasar/100;
                    $data[$k_data]['insentif'] = $insentif;
                    $data[$k_data]['total_insentif'] = $insentif * $data[$k_data]['tonase'];
                }
            }
        }

        $_data = array(
            'data' => $data,
            'bonus_pasar' => $bonus_pasar,
            'bonus_kematian' => $bonus_kematian,
            'fcr' => $fcr,
            'bb' => $bb,
            'deplesi' => $deplesi,
            'ip' => number_format($ip, 4),
            'rata_umur_panen' => $rata_umur_panen
        );

        return $_data;
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

                union all

                select
                    pp.tanggal,
                    pp.nomor as kode,
                    mtr.perusahaan,
                    'HUTANG PERALATAN' as keterangan,
                    (pp.total - isnull(bp.nominal, 0)) as sisa_piutang
                from penjualan_peralatan pp
                left join
                    (
                        select
                            sum(data.nominal) as nominal,
                            data.piutang_kode
                        from (
                            select sum(saldo+bayar) as nominal, pp.nomor as piutang_kode from bayar_penjualan_peralatan bpp
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
                left join
                    (
                        select mtr1.* from mitra mtr1
                        right join
                            (select max(id) as id, nomor from mitra group by nomor) mtr2
                            on
                                mtr1.id = mtr2.id
                    ) mtr
                    on
                        pp.mitra = mtr.nomor
                where
                    pp.total > isnull(bp.nominal, 0) and
                    pp.mitra = '".$mitra."'
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

    public function tutup_siklus()
    {
        $params = $this->input->post('params');

        try {
            $m_conf = new \Model\Storage\Conf();
            $now = $m_conf->getDate();

            $sql = "
                select ln.* from lhk_nekropsi ln
                right join
                    lhk l
                    on
                        ln.id_header = l.id
                where 
                    l.noreg = '".$params['noreg']."'
                order by
                    l.umur desc
            ";
            $d_ln = $m_conf->hydrateRaw( $sql );

            $tutup = 1;
            if ( $d_ln->count() > 0 ) {
                $tutup = 1;
            } else {
                if ( $now['tanggal'] >= '2023-11-01' ) {
                    $tutup = 0;
                    $this->result['message'] = 'Belum ada data nekropsi yang di submit, harap konfirmasi pada bagian terkait.';
                }
            }

            if ( $tutup == 1 ) {
                $m_ts = new \Model\Storage\TutupSiklus_model();

                $invoice = null;
                if ( $params['jenis_rhpp'] == 0 ) {
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
                            rs.noreg = '".$params['noreg']."'
                    ";
                    $d_unit = $m_conf->hydrateRaw( $sql );

                    if ( $d_unit->count() ) {
                        $kode_unit = $d_unit->toArray()[0]['kode_unit'];

                        $m_rhpp = new \Model\Storage\Rhpp_model();
                        $invoice = $m_rhpp->getNoInvoice('INV/RHPP/'.$kode_unit);
                    }
                }

                $m_ts->noreg = $params['noreg'];
                $m_ts->tgl_docin = $params['tgl_docin'];
                $m_ts->tgl_tutup = $params['tgl_tutup_siklus'];
                $m_ts->biaya_materai = $params['biaya_materai'];
                $m_ts->id_potongan_pajak = $params['id_potongan_pajak'];
                $m_ts->save();

                $id = $m_ts->id;

                foreach ($params['data_rhpp'] as $k_rhpp => $v_rhpp) {
                    $m_rhpp = new \Model\Storage\Rhpp_model();
                    $m_rhpp->id_ts = $id;
                    $m_rhpp->jenis = $v_rhpp['jenis'];
                    $m_rhpp->mitra = $v_rhpp['mitra'];
                    $m_rhpp->noreg = $v_rhpp['noreg'];
                    $m_rhpp->populasi = $v_rhpp['populasi'];
                    $m_rhpp->kandang = $v_rhpp['kandang'];
                    $m_rhpp->tgl_docin = substr($v_rhpp['tgl_docin'], 0, 10);
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
                    $m_rhpp->pdpt_peternak_sudah_pajak = ($v_rhpp['pdpt_peternak_belum_pajak'] > 0) ? $v_rhpp['pdpt_peternak_sudah_pajak'] : 0;
                    $m_rhpp->lr_inti = $v_rhpp['lr_inti'];
                    $m_rhpp->populasi_bonus_insentif_listrik = $v_rhpp['populasi_bonus_insentif_listrik'];
                    $m_rhpp->bonus_insentif_listrik = $v_rhpp['bonus_insentif_listrik'];
                    $m_rhpp->total_bonus_insentif_listrik = $v_rhpp['total_bonus_insentif_listrik'];
                    $m_rhpp->persen_bonus_pasar = $v_rhpp['persen_bonus_pasar'];
                    $m_rhpp->total_bonus = $v_rhpp['total_bonus'];
                    $m_rhpp->total_potongan = $v_rhpp['total_potongan'];
                    $m_rhpp->cn = !empty($v_rhpp['cn']) ? $v_rhpp['cn'] : null;
                    $m_rhpp->invoice = $invoice;
                    $m_rhpp->save();

                    $id_rhpp = $m_rhpp->id;

                    $m_rhpp_doc = new \Model\Storage\RhppDoc_model();
                    $m_rhpp_doc->id_header = $id_rhpp;
                    $m_rhpp_doc->tanggal = substr($v_rhpp['data_doc']['tanggal'], 0, 10);
                    $m_rhpp_doc->nota = $v_rhpp['data_doc']['nota'];
                    $m_rhpp_doc->barang = $v_rhpp['data_doc']['barang'];
                    $m_rhpp_doc->box = $v_rhpp['data_doc']['box_zak'];
                    $m_rhpp_doc->jumlah = $v_rhpp['data_doc']['jumlah'];
                    $m_rhpp_doc->harga = $v_rhpp['data_doc']['harga'];
                    $m_rhpp_doc->total = $v_rhpp['data_doc']['total'];
                    $m_rhpp_doc->vaksin = $v_rhpp['data_doc']['vaksin'];
                    $m_rhpp_doc->harga_vaksin = $v_rhpp['data_doc']['harga_vaksin'];
                    $m_rhpp_doc->total_vaksin = $v_rhpp['data_doc']['total_vaksin'];
                    $m_rhpp_doc->save();

                    if ( !empty($v_rhpp['data_pakan']) ) {
                        foreach ($v_rhpp['data_pakan'] as $k_pakan => $v_pakan) {
                            $m_rhpp_pakan = new \Model\Storage\RhppPakan_model();
                            $m_rhpp_pakan->id_header = $id_rhpp;
                            $m_rhpp_pakan->tanggal = substr($v_pakan['tanggal'], 0, 10);
                            $m_rhpp_pakan->nota = $v_pakan['nota'];
                            $m_rhpp_pakan->barang = $v_pakan['barang'];
                            $m_rhpp_pakan->zak = $v_pakan['box_zak'];
                            $m_rhpp_pakan->jumlah = $v_pakan['jumlah'];
                            $m_rhpp_pakan->harga = (isset($v_pakan['harga']) && $v_pakan['harga'] > 0) ? $v_pakan['harga'] : 0;
                            $m_rhpp_pakan->total = (isset($v_pakan['total']) && $v_pakan['total'] > 0) ? $v_pakan['total'] : 0;
                            $m_rhpp_pakan->save();
                        }
                    }

                    if ( !empty($v_rhpp['data_oa_pakan']) ) {
                        foreach ($v_rhpp['data_oa_pakan'] as $k_oa_pakan => $v_oa_pakan) {
                            $m_rhpp_oa_pakan = new \Model\Storage\RhppOaPakan_model();
                            $m_rhpp_oa_pakan->id_header = $id_rhpp;
                            $m_rhpp_oa_pakan->tanggal = substr($v_oa_pakan['tanggal'], 0, 10);
                            $m_rhpp_oa_pakan->nota = $v_oa_pakan['nota'];
                            $m_rhpp_oa_pakan->nopol = $v_oa_pakan['nopol'];
                            $m_rhpp_oa_pakan->barang = $v_oa_pakan['barang'];
                            $m_rhpp_oa_pakan->zak = $v_oa_pakan['box_zak'];
                            $m_rhpp_oa_pakan->jumlah = $v_oa_pakan['jumlah'];
                            $m_rhpp_oa_pakan->harga = (isset($v_oa_pakan['harga']) && $v_oa_pakan['harga'] > 0) ? $v_oa_pakan['harga'] : 0;
                            $m_rhpp_oa_pakan->total = (isset($v_oa_pakan['total']) && $v_oa_pakan['total'] > 0) ? $v_oa_pakan['total'] : 0;
                            $m_rhpp_oa_pakan->save();
                        }
                    }

                    if ( !empty($v_rhpp['data_pindah_pakan']) ) {
                        foreach ($v_rhpp['data_pindah_pakan'] as $k_pindah_pakan => $v_pindah_pakan) {
                            $m_rhpp_pindah_pakan = new \Model\Storage\RhppPindahPakan_model();
                            $m_rhpp_pindah_pakan->id_header = $id_rhpp;
                            $m_rhpp_pindah_pakan->tanggal = substr($v_pindah_pakan['tanggal'], 0, 10);
                            $m_rhpp_pindah_pakan->nota = $v_pindah_pakan['nota'];
                            $m_rhpp_pindah_pakan->barang = $v_pindah_pakan['barang'];
                            $m_rhpp_pindah_pakan->zak = $v_pindah_pakan['box_zak'];
                            $m_rhpp_pindah_pakan->jumlah = $v_pindah_pakan['jumlah'];
                            $m_rhpp_pindah_pakan->harga = (isset($v_pindah_pakan['harga']) && $v_pindah_pakan['harga'] > 0) ? $v_pindah_pakan['harga'] : 0;
                            $m_rhpp_pindah_pakan->total = (isset($v_pindah_pakan['total']) && $v_pindah_pakan['total'] > 0) ? $v_pindah_pakan['total'] : 0;
                            $m_rhpp_pindah_pakan->save();
                        }
                    }

                    if ( !empty($v_rhpp['data_oa_pindah_pakan']) ) {
                        foreach ($v_rhpp['data_oa_pindah_pakan'] as $k_oa_pindah_pakan => $v_oa_pindah_pakan) {
                            $m_rhpp_oa_pindah_pakan = new \Model\Storage\RhppOaPindahPakan_model();
                            $m_rhpp_oa_pindah_pakan->id_header = $id_rhpp;
                            $m_rhpp_oa_pindah_pakan->tanggal = substr($v_oa_pindah_pakan['tanggal'], 0, 10);
                            $m_rhpp_oa_pindah_pakan->nota = $v_oa_pindah_pakan['nota'];
                            $m_rhpp_oa_pindah_pakan->nopol = $v_oa_pindah_pakan['nopol'];
                            $m_rhpp_oa_pindah_pakan->barang = $v_oa_pindah_pakan['barang'];
                            $m_rhpp_oa_pindah_pakan->zak = $v_oa_pindah_pakan['box_zak'];
                            $m_rhpp_oa_pindah_pakan->jumlah = $v_oa_pindah_pakan['jumlah'];
                            $m_rhpp_oa_pindah_pakan->harga = (isset($v_oa_pindah_pakan['harga']) && $v_oa_pindah_pakan['harga'] > 0) ? $v_oa_pindah_pakan['harga'] : 0;
                            $m_rhpp_oa_pindah_pakan->total = (isset($v_oa_pindah_pakan['total']) && $v_oa_pindah_pakan['total'] > 0) ? $v_oa_pindah_pakan['total'] : 0;
                            $m_rhpp_oa_pindah_pakan->save();
                        }
                    }

                    if ( !empty($v_rhpp['data_retur_pakan']) ) {
                        foreach ($v_rhpp['data_retur_pakan'] as $k_retur_pakan => $v_retur_pakan) {
                            $m_rhpp_retur_pakan = new \Model\Storage\RhppReturPakan_model();
                            $m_rhpp_retur_pakan->id_header = $id_rhpp;
                            $m_rhpp_retur_pakan->tanggal = substr($v_retur_pakan['tanggal'], 0, 10);
                            $m_rhpp_retur_pakan->nota = $v_retur_pakan['nota'];
                            $m_rhpp_retur_pakan->barang = $v_retur_pakan['barang'];
                            $m_rhpp_retur_pakan->zak = $v_retur_pakan['box_zak'];
                            $m_rhpp_retur_pakan->jumlah = $v_retur_pakan['jumlah'];
                            $m_rhpp_retur_pakan->harga = (isset($v_retur_pakan['harga']) && $v_retur_pakan['harga'] > 0) ? $v_retur_pakan['harga'] : 0;
                            $m_rhpp_retur_pakan->total = (isset($v_retur_pakan['total']) && $v_retur_pakan['total'] > 0) ? $v_retur_pakan['total'] : 0;
                            $m_rhpp_retur_pakan->save();
                        }
                    }

                    if ( !empty($v_rhpp['data_oa_retur_pakan']) ) {
                        foreach ($v_rhpp['data_oa_retur_pakan'] as $k_oa_retur_pakan => $v_oa_retur_pakan) {
                            $m_rhpp_oa_retur_pakan = new \Model\Storage\RhppOaReturPakan_model();
                            $m_rhpp_oa_retur_pakan->id_header = $id_rhpp;
                            $m_rhpp_oa_retur_pakan->tanggal = substr($v_oa_retur_pakan['tanggal'], 0, 10);
                            $m_rhpp_oa_retur_pakan->nota = $v_oa_retur_pakan['nota'];
                            $m_rhpp_oa_retur_pakan->nopol = $v_oa_retur_pakan['nopol'];
                            $m_rhpp_oa_retur_pakan->barang = $v_oa_retur_pakan['barang'];
                            $m_rhpp_oa_retur_pakan->zak = $v_oa_retur_pakan['box_zak'];
                            $m_rhpp_oa_retur_pakan->jumlah = $v_oa_retur_pakan['jumlah'];
                            $m_rhpp_oa_retur_pakan->harga = (isset($v_oa_retur_pakan['harga']) && $v_oa_retur_pakan['harga'] > 0) ? $v_oa_retur_pakan['harga'] : 0;
                            $m_rhpp_oa_retur_pakan->total = (isset($v_oa_retur_pakan['total']) && $v_oa_retur_pakan['total'] > 0) ? $v_oa_retur_pakan['total'] : 0;
                            $m_rhpp_oa_retur_pakan->save();
                        }
                    }

                    if ( !empty($v_rhpp['data_voadip']) ) {
                        foreach ($v_rhpp['data_voadip'] as $k_voadip => $v_voadip) {
                            $m_rhpp_voadip = new \Model\Storage\RhppVoadip_model();
                            $m_rhpp_voadip->id_header = $id_rhpp;
                            $m_rhpp_voadip->tanggal = substr($v_voadip['tanggal'], 0, 10);
                            $m_rhpp_voadip->nota = $v_voadip['nota'];
                            $m_rhpp_voadip->barang = $v_voadip['barang'];
                            $m_rhpp_voadip->jumlah = $v_voadip['jumlah'];
                            $m_rhpp_voadip->harga = (isset($v_voadip['harga']) && $v_voadip['harga'] > 0) ? $v_voadip['harga'] : 0;
                            $m_rhpp_voadip->total = (isset($v_voadip['total']) && $v_voadip['total'] > 0) ? $v_voadip['total'] : 0;
                            $m_rhpp_voadip->save();
                        }
                    }

                    if ( !empty($v_rhpp['data_retur_voadip']) ) {
                        foreach ($v_rhpp['data_retur_voadip'] as $k_retur_voadip => $v_retur_voadip) {
                            $m_rhpp_retur_voadip = new \Model\Storage\RhppReturVoadip_model();
                            $m_rhpp_retur_voadip->id_header = $id_rhpp;
                            $m_rhpp_retur_voadip->tanggal = substr($v_retur_voadip['tanggal'], 0, 10);
                            $m_rhpp_retur_voadip->nota = $v_retur_voadip['nota'];
                            $m_rhpp_retur_voadip->barang = $v_retur_voadip['barang'];
                            $m_rhpp_retur_voadip->jumlah = $v_retur_voadip['jumlah'];
                            $m_rhpp_retur_voadip->harga = (isset($v_retur_voadip['harga']) && $v_retur_voadip['harga'] > 0) ? $v_retur_voadip['harga'] : 0;
                            $m_rhpp_retur_voadip->total = (isset($v_retur_voadip['total']) && $v_retur_voadip['total'] > 0) ? $v_retur_voadip['total'] : 0;
                            $m_rhpp_retur_voadip->save();
                        }
                    }

                    if ( !empty($v_rhpp['data_penjualan']) ) {
                        foreach ($v_rhpp['data_penjualan'] as $k_penjualan => $v_penjualan) {
                            $m_rhpp_penjualan = new \Model\Storage\RhppPenjualan_model();
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
                            $m_rhpp_potongan = new \Model\Storage\RhppPotongan_model();
                            $m_rhpp_potongan->id_header = $id_rhpp;
                            $m_rhpp_potongan->id_trans = isset($v_potongan['id_jual']) ? $v_potongan['id_jual'] : null;
                            $m_rhpp_potongan->keterangan = $v_potongan['keterangan'];
                            $m_rhpp_potongan->jumlah_tagihan = $v_potongan['jumlah_tagihan'];
                            $m_rhpp_potongan->jumlah_bayar = $v_potongan['jumlah_bayar'];
                            $m_rhpp_potongan->save();

                            if ( !empty($v_potongan['id_jual']) ) {
                                $sisa_tagihan = $v_potongan['jumlah_tagihan'] - $v_potongan['jumlah_bayar'];

                                $status = ($sisa_tagihan > 0) ? 'BELUM' : 'LUNAS';

                                // $m_bpp = new \Model\Storage\BayarPenjualanPeralatan_model();
                                // $m_bpp->id_penjualan_peralatan = $v_potongan['id_jual'];
                                // $m_bpp->tanggal = $params['tgl_tutup_siklus'];
                                // $m_bpp->tagihan = $v_potongan['jumlah_tagihan'];
                                // $m_bpp->saldo = 0;
                                // $m_bpp->bayar = $v_potongan['jumlah_bayar'];
                                // $m_bpp->jenis_bayar = 'rhpp';
                                // $m_bpp->status = $status;
                                // $m_bpp->lampiran = null;
                                // $m_bpp->save();

                                // $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                                // Modules::run( 'base/event/save', $m_bpp, $deskripsi_log);

                                $m_pp = new \Model\Storage\PenjualanPeralatan_model();
                                $m_pp->where('id', $v_potongan['id_jual'])->update(
                                    array(
                                        'status' => $status
                                    )
                                );

                                // $d_pp = $m_pp->where('id', $v_potongan['id_jual'])->first();

                                // $saldo = 0;
                                // if ( $v_potongan['jumlah_tagihan'] < $v_potongan['jumlah_bayar'] ) {
                                //     $saldo = $v_potongan['jumlah_bayar'] - $v_potongan['jumlah_tagihan'];
                                // }

                                // $m_sm = new \Model\Storage\SaldoMitra_model();
                                // $d_sm = $m_sm->where('no_mitra', $d_pp->mitra)->orderBy('id', 'desc')->first();

                                // $_saldo = (isset($d_sm->saldo)) ? ($d_sm->saldo - 0) : 0;

                                // $m_sm->jenis_saldo = 'D';
                                // $m_sm->no_mitra = $d_pp->mitra;
                                // $m_sm->tbl_name = 'bayar_penjualan_peralatan';
                                // $m_sm->tbl_id = $m_bpp->id;
                                // $m_sm->tgl_trans = date('Y-m-d');
                                // $m_sm->jenis_trans = 'pembayaran_mitra';
                                // $m_sm->nominal = 0;
                                // $m_sm->saldo = $_saldo + $saldo;
                                // $m_sm->save();
                            }
                        }
                    }

                    if ( !empty($v_rhpp['data_bonus']) ) {
                        foreach ($v_rhpp['data_bonus'] as $k_bonus => $v_bonus) {
                            $m_rhpp_bonus = new \Model\Storage\RhppBonus_model();
                            $m_rhpp_bonus->id_header = $id_rhpp;
                            $m_rhpp_bonus->id_trans = null;
                            $m_rhpp_bonus->keterangan = $v_bonus['keterangan'];
                            $m_rhpp_bonus->jumlah = $v_bonus['jumlah_bonus'];
                            $m_rhpp_bonus->save();
                        }
                    }

                    if ( !empty($v_rhpp['data_piutang']) ) {
                        foreach ($v_rhpp['data_piutang'] as $k_piutang => $v_piutang) {
                            $m_rhpp_piutang = new \Model\Storage\RhppPiutang_model();
                            $m_rhpp_piutang->id_header = $id_rhpp;
                            $m_rhpp_piutang->piutang_kode = $v_piutang['piutang_kode'];
                            $m_rhpp_piutang->nama_perusahaan = $v_piutang['nama_perusahaan'];
                            $m_rhpp_piutang->sisa_piutang = $v_piutang['sisa_piutang'];
                            $m_rhpp_piutang->nominal = $v_piutang['nominal'];
                            $m_rhpp_piutang->save();
                        }
                    }
                }

                $d_ts = $m_ts->where('id', $id)->first();

                $m_conf = new \Model\Storage\Conf();
                $sql = "exec insert_jurnal 'RHPP', NULL, NULL, 0, 'tutup_siklus', ".$id.", NULL, 1";

                $d_conf = $m_conf->hydrateRaw( $sql );

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $d_ts, $deskripsi_log);
                
                $this->result['status'] = 1;
                $this->result['message'] = 'Data berhasil disimpan';
                $this->result['content'] = array('id' => $id);
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
            $id = $params['id'];

            $m_ts = new \Model\Storage\TutupSiklus_model();
            $d_ts = $m_ts->where('id', $id)->first();

            $m_ts->where('id', $id)->update(
                array(
                    'tgl_docin' => $params['tgl_docin'],
                    'tgl_tutup' => $params['tgl_tutup_siklus'],
                    'biaya_materai' => $params['biaya_materai'],
                    'id_potongan_pajak' => $params['id_potongan_pajak']
                )
            );

            foreach ($params['data_rhpp'] as $k_rhpp => $v_rhpp) {
                $m_rhpp = new \Model\Storage\Rhpp_model();
                $m_rhpp->where('id_ts', $params['id'])->where('jenis', $v_rhpp['jenis'])->update(
                    array(
                        'mitra' => $v_rhpp['mitra'],
                        'noreg' => $v_rhpp['noreg'],
                        'populasi' => $v_rhpp['populasi'],
                        'kandang' => $v_rhpp['kandang'],
                        'tgl_docin' => substr($v_rhpp['tgl_docin'], 0, 10),
                        'jml_panen_ekor' => $v_rhpp['jml_panen_ekor'],
                        'jml_panen_kg' => $v_rhpp['jml_panen_kg'],
                        'bb' => round($v_rhpp['bb'], 2),
                        'fcr' => round($v_rhpp['fcr'], 2),
                        'deplesi' => round($v_rhpp['deplesi'], 2),
                        'rata_umur' => round($v_rhpp['rata_umur'], 2),
                        'ip' => round($v_rhpp['ip'], 2),
                        'tot_penjualan_ayam' => $v_rhpp['tot_penjualan_ayam'],
                        'tot_pembelian_sapronak' => $v_rhpp['tot_pembelian_sapronak'],
                        'biaya_materai' => $v_rhpp['biaya_materai'],
                        'bonus_pasar' => $v_rhpp['bonus_pasar'],
                        'bonus_kematian' => $v_rhpp['bonus_kematian'],
                        'bonus_insentif_fcr' => $v_rhpp['bonus_insentif_fcr'],
                        'biaya_operasional' => $v_rhpp['biaya_operasional'],
                        'pdpt_peternak_belum_pajak' => $v_rhpp['pdpt_peternak_belum_pajak'],
                        'prs_potongan_pajak' => $v_rhpp['prs_potongan_pajak'],
                        'potongan_pajak' => $v_rhpp['potongan_pajak'],
                        'pdpt_peternak_sudah_pajak' => $v_rhpp['pdpt_peternak_sudah_pajak'],
                        'populasi_bonus_insentif_listrik' => $v_rhpp['populasi_bonus_insentif_listrik'],
                        'bonus_insentif_listrik' => $v_rhpp['bonus_insentif_listrik'],
                        'total_bonus_insentif_listrik' => $v_rhpp['total_bonus_insentif_listrik'],
                        'lr_inti' => $v_rhpp['lr_inti'],
                        'persen_bonus_pasar' => $v_rhpp['persen_bonus_pasar'],
                    )
                );

                $d_rhpp = $m_rhpp->where('id_ts', $params['id'])->where('jenis', $v_rhpp['jenis'])->first();

                $m_rhpp_potongan = new \Model\Storage\RhppPotongan_model();
                $m_rhpp_potongan->where('id_header', $d_rhpp->id)->whereNull('id_trans')->delete();

                if ( !empty($v_rhpp['data_potongan']) ) {
                    foreach ($v_rhpp['data_potongan'] as $k_potongan => $v_potongan) {
                        if ( isset($v_potongan['id_jual']) && !empty($v_potongan['id_jual']) ) {
                            $m_bpp = new \Model\Storage\BayarPenjualanPeralatan_model();
                            $d_bpp = $m_bpp->where('id_penjualan_peralatan', $v_potongan['id_jual'])->where('tanggal', $d_ts->tgl_tutup)->where('jenis_bayar', 'rhpp')->orderBy('id', 'desc')->first();

                            $m_pp = new \Model\Storage\PenjualanPeralatan_model();
                            $d_pp = $m_pp->where('id', $d_bpp->id_penjualan_peralatan)->first();

                            $m_sm = new \Model\Storage\SaldoMitra_model();
                            $d_sm = $m_sm->where('no_mitra', $d_pp['mitra'])->orderBy('id', 'desc')->first();

                            $jenis_saldo = null;
                            $nominal = null;
                            $saldo = !empty($d_sm) ? $d_sm->saldo : 0;

                            $lebih_kurang = $v_potongan['jumlah_bayar'] - $d_bpp->bayar;

                            if ( $v_potongan['jumlah_bayar'] < $d_bpp->bayar ) {
                                $nominal = $v_potongan['jumlah_bayar'] - $d_bpp->bayar;

                                $jenis_saldo = 'K';
                                $saldo -= abs($nominal);
                            } else {
                                $nominal = $d_bpp->bayar - $v_potongan['jumlah_bayar'];

                                $jenis_saldo = 'D';
                                $saldo += abs($nominal);
                            }

                            $m_sm = new \Model\Storage\SaldoMitra_model();
                            $m_sm->jenis_saldo = $jenis_saldo;
                            $m_sm->no_mitra = $d_pp->mitra;
                            $m_sm->tbl_name = 'bayar_penjualan_peralatan';
                            $m_sm->tbl_id = $d_bpp['id'];
                            $m_sm->tgl_trans = date('Y-m-d');
                            $m_sm->jenis_trans = 'reverse_pembayaran_mitra';
                            $m_sm->nominal = abs($nominal);
                            $m_sm->saldo = ($saldo < 0) ? 0 : $saldo;
                            $m_sm->save();

                            $sisa_tagihan = $v_potongan['jumlah_tagihan'] - $v_potongan['jumlah_bayar'];

                            $status = ($sisa_tagihan > 0) ? 'BELUM' : 'LUNAS';

                            $m_bpp->where('id', $d_bpp['id'])->update(
                                array(
                                    'tanggal' => $params['tgl_tutup_siklus'],
                                    'tagihan' => $v_potongan['jumlah_tagihan'],
                                    'saldo' => 0,
                                    'bayar' => $v_potongan['jumlah_bayar'],
                                    'jenis_bayar' => 'rhpp',
                                    'status' => $status,
                                    'lampiran' => null
                                )
                            );

                            $m_rhpp_potongan = new \Model\Storage\RhppPotongan_model();
                            $m_rhpp_potongan->where('id_header', $d_rhpp->id)->where('id_trans', $v_potongan['id_jual'])->update(
                                array(
                                    'jumlah_bayar' => $v_potongan['jumlah_bayar']
                                )
                            );

                            $m_pp->where('id', $d_bpp->id_penjualan_peralatan)->update(
                                array(
                                    'status' => $status
                                )
                            );
                        } else {
                            $m_rhpp_potongan = new \Model\Storage\RhppPotongan_model();
                            $m_rhpp_potongan->id_header = $d_rhpp->id;
                            $m_rhpp_potongan->id_trans = isset($v_potongan['id_jual']) ? $v_potongan['id_jual'] : null;
                            $m_rhpp_potongan->keterangan = $v_potongan['keterangan'];
                            $m_rhpp_potongan->jumlah_tagihan = $v_potongan['jumlah_tagihan'];
                            $m_rhpp_potongan->jumlah_bayar = $v_potongan['jumlah_bayar'];
                            $m_rhpp_potongan->save();
                        }
                    }
                }


                if ( !empty($v_rhpp['data_bonus']) ) {
                    $m_rhpp_bonus = new \Model\Storage\RhppBonus_model();
                    $m_rhpp_bonus->where('id_header', $d_rhpp->id)->delete();

                    foreach ($v_rhpp['data_bonus'] as $k_bonus => $v_bonus) {
                        $m_rhpp_bonus = new \Model\Storage\RhppBonus_model();
                        $m_rhpp_bonus->id_header = $d_rhpp->id;
                        $m_rhpp_bonus->id_trans = null;
                        $m_rhpp_bonus->keterangan = $v_bonus['keterangan'];
                        $m_rhpp_bonus->jumlah = $v_bonus['jumlah_bonus'];
                        $m_rhpp_bonus->save();
                    }
                }
            }

            $d_ts = $m_ts->where('id', $id)->first();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal 'RHPP', NULL, NULL, 0, 'tutup_siklus', ".$id.", ".$id.", 2";

            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_ts, $deskripsi_log);
            
            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di update';
            $this->result['content'] = array('id' => $params['id']);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {
            $m_ts = new \Model\Storage\TutupSiklus_model();
            $d_ts = $m_ts->where('id', $params['id'])->first();

            $m_rhpp = new \Model\Storage\Rhpp_model();
            $d_rhpp_inti = $m_rhpp->where('id_ts', $params['id'])->where('jenis', 'rhpp_inti')->first()->toArray();

            $d_rhpp_plasma = null;
            $_d_rhpp_plasma = $m_rhpp->where('id_ts', $params['id'])->where('jenis', 'rhpp_plasma')->first();
            if ( $_d_rhpp_plasma ) {
                $d_rhpp_plasma = $_d_rhpp_plasma->toArray();
            }

            $ket_delete = null;
            $delete = 1;

            $m_rg = new \Model\Storage\Conf();
            $sql = "
                select
                    rgn.noreg,
                    rgh.tgl_submit
                from rhpp_group_noreg rgn
                left join
                    rhpp_group rg
                    on
                        rgn.id_header = rg.id
                left join
                    rhpp_group_header rgh
                    on
                        rg.id_header = rgh.id
                where
                    rgn.noreg = '".$d_rhpp_inti['noreg']."'
                group by
                    rgn.noreg,
                    rgh.tgl_submit
            ";
            $d_rg = $m_rg->hydrateRaw( $sql );
            if ( $d_rg->count() > 0 ) {
                $d_rg = $d_rg->toArray()[0];

                $ket_delete = 'Tidak bisa di hapus, karena data rhpp sudah di lakukan penggabungan rhpp pada tanggal <b>'.strtoupper(tglIndonesia($d_rg['tgl_submit'], '-', ' ')).'</b>.';

                $delete = 0;
            } else {
                if ( !empty($d_rhpp_plasma) ) {
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
                            kppd.jenis = 'RHPP' and
                            kppd.id_trans = ".$d_rhpp_plasma['id']."
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
            }

            if ( $delete == 1 ) {
                $m_rhpp = new \Model\Storage\Rhpp_model();
                $d_rhpp = $m_rhpp->where('id_ts', $params['id'])->with(['potongan'])->get();
    
                if ( $d_rhpp->count() > 0 ) {
                    $d_rhpp = $d_rhpp->toArray();
    
                    foreach ($d_rhpp as $k_rhpp => $v_rhpp) {
                        if ( !empty($v_rhpp['potongan']) ) {
                            foreach ($v_rhpp['potongan'] as $k_potongan => $v_potongan) {
                                if ( !empty($v_potongan['id_trans']) ) {
                                    $m_bpp = new \Model\Storage\BayarPenjualanPeralatan_model();
                                    $d_bpp = $m_bpp->where('id_penjualan_peralatan', $v_potongan['id_trans'])->where('tanggal', $d_ts->tgl_tutup)->where('jenis_bayar', 'rhpp')->orderBy('id', 'desc')->first();
    
                                    $m_bpp->where('id', $d_bpp->id)->delete();
    
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
                                    $m_sm->tbl_id = $d_bpp->id;
                                    $m_sm->tgl_trans = date('Y-m-d');
                                    $m_sm->jenis_trans = 'reverse_pembayaran_mitra';
                                    $m_sm->nominal = $d_bpp['bayar'] + $d_bpp['saldo'];
                                    $m_sm->saldo = ($saldo < 0) ? 0 : $saldo;
                                    $m_sm->save();
                                }
                            }
                        }
                    }
    
                    $id_rhpp = $m_rhpp->select('id')->where('id_ts', $params['id'])->get()->toArray();
    
                    $m_rhpp_piutang = new \Model\Storage\RhppPiutang_model();
                    $m_rhpp_piutang->whereIn('id_header', $id_rhpp)->delete();
    
                    $m_rhpp_bonus = new \Model\Storage\RhppBonus_model();
                    $m_rhpp_bonus->whereIn('id_header', $id_rhpp)->delete();
    
                    $m_rhpp_potongan = new \Model\Storage\RhppPotongan_model();
                    $m_rhpp_potongan->whereIn('id_header', $id_rhpp)->delete();
    
                    $m_rhpp_voadip = new \Model\Storage\RhppVoadip_model();
                    $m_rhpp_voadip->whereIn('id_header', $id_rhpp)->delete();
    
                    $m_rhpp_retur_voadip = new \Model\Storage\RhppReturVoadip_model();
                    $m_rhpp_retur_voadip->whereIn('id_header', $id_rhpp)->delete();
    
                    $m_rhpp_retur_pakan = new \Model\Storage\RhppReturPakan_model();
                    $m_rhpp_retur_pakan->whereIn('id_header', $id_rhpp)->delete();
    
                    $m_rhpp_pindah_pakan = new \Model\Storage\RhppPindahPakan_model();
                    $m_rhpp_pindah_pakan->whereIn('id_header', $id_rhpp)->delete();
    
                    $m_rhpp_penjualan = new \Model\Storage\RhppPenjualan_model();
                    $m_rhpp_penjualan->whereIn('id_header', $id_rhpp)->delete();
    
                    $m_rhpp_pakan = new \Model\Storage\RhppPakan_model();
                    $m_rhpp_pakan->whereIn('id_header', $id_rhpp)->delete();
    
                    $m_rhpp_oa_retur_pakan = new \Model\Storage\RhppOaReturPakan_model();
                    $m_rhpp_oa_retur_pakan->whereIn('id_header', $id_rhpp)->delete();
    
                    $m_rhpp_oa_pindah_pakan = new \Model\Storage\RhppOaPindahPakan_model();
                    $m_rhpp_oa_pindah_pakan->whereIn('id_header', $id_rhpp)->delete();
    
                    $m_rhpp_oa_pakan = new \Model\Storage\RhppOaPakan_model();
                    $m_rhpp_oa_pakan->whereIn('id_header', $id_rhpp)->delete();
    
                    $m_rhpp_doc = new \Model\Storage\RhppDoc_model();
                    $m_rhpp_doc->whereIn('id_header', $id_rhpp)->delete();
    
                    $m_rhpp = new \Model\Storage\Rhpp_model();
                    $m_rhpp->whereIn('id', $id_rhpp)->delete();
                }
    
                $m_ts->where('id', $params['id'])->delete();
    
                $m_conf = new \Model\Storage\Conf();
                $sql = "exec insert_jurnal NULL, NULL, NULL, NULL, 'tutup_siklus', ".$params['id'].", ".$params['id'].", 3";
    
                $d_conf = $m_conf->hydrateRaw( $sql );
    
                $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $d_ts, $deskripsi_log);
                
                $this->result['status'] = 1;
                $this->result['message'] = 'Data berhasil di hapus';
                $this->result['content'] = array('id' => $params['id']);
            } else {
                $this->result['message'] = $ket_delete;
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function export_excel($_noreg)
    {
        $noreg = exDecrypt( $_noreg );

        $m_ts = new \Model\Storage\TutupSiklus_model();
        $d_ts = $m_ts->where('noreg', $noreg)->with(['potongan_pajak'])->first();

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('noreg', $noreg)->with(['dPengawas'])->first()->toArray();

        $m_rhpp = new \Model\Storage\Rhpp_model();
        $d_rhpp_plasma = $m_rhpp->where('noreg', $noreg)->where('jenis', 'rhpp_plasma')->with(['doc', 'pakan', 'oa_pakan', 'pindah_pakan', 'oa_pindah_pakan', 'retur_pakan', 'oa_retur_pakan', 'voadip', 'retur_voadip', 'penjualan', 'potongan', 'bonus', 'piutang'])->orderBy('id', 'desc')->first();

        $d_rhpp_plasma = !empty($d_rhpp_plasma) ? $d_rhpp_plasma->toArray() : null;

        $id_tutup_siklus = $d_rhpp_plasma['id_ts'];
        $mitra = $d_rhpp_plasma['mitra'];
        $noreg = $d_rhpp_plasma['noreg'];
        $populasi = $d_rhpp_plasma['populasi'];
        $kandang = $d_rhpp_plasma['kandang'];
        $tgl_docin = $d_rhpp_plasma['tgl_docin'];
        $tutup_siklus = 1;
        $biaya_materai = $d_rhpp_plasma['biaya_materai'];
        $potongan_pajak = $d_rhpp_plasma['prs_potongan_pajak'];
        $tgl_tutup = $d_ts->tgl_tutup;
        $rata_umur_panen = $d_rhpp_plasma['rata_umur'];
        $biaya_opr = $d_rhpp_plasma['biaya_operasional'];
        $bonus_kematian = $d_rhpp_plasma['bonus_kematian'];
        $bonus_insentif_fcr = $d_rhpp_plasma['bonus_insentif_fcr'];
        $populasi_bonus_insentif_listrik = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['populasi_bonus_insentif_listrik'] : 0;
        $bonus_insentif_listrik = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['bonus_insentif_listrik'] : 0;
        $total_bonus_insentif_listrik = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['total_bonus_insentif_listrik'] : 0;

        $data = null;
        $data_rhpp_plasma = null;

        if ( !empty($d_rhpp_plasma) ) {
            $data_doc_plasma['doc'] = array(
                'tgl_docin' => $d_rhpp_plasma['doc']['tanggal'],
                'sj' => $d_rhpp_plasma['doc']['nota'],
                'barang' => $d_rhpp_plasma['doc']['barang'],
                'box' => $d_rhpp_plasma['doc']['box'],
                'jumlah' => $d_rhpp_plasma['doc']['jumlah'],
                'harga' => $d_rhpp_plasma['doc']['harga'],
                'total' => $d_rhpp_plasma['doc']['total']
            );
            $data_doc_plasma['vaksin'] = array(
                'barang' => $d_rhpp_plasma['doc']['vaksin'],
                'harga' => $d_rhpp_plasma['doc']['harga_vaksin'],
                'total' => $d_rhpp_plasma['doc']['total_vaksin']
            );
            $data_pakan_plasma = null;
            foreach ($d_rhpp_plasma['pakan'] as $k_pakan => $v_pakan) {
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
            $data_pindah_pakan_plasma = $d_rhpp_plasma['pindah_pakan'];
            $data_retur_pakan_plasma = $d_rhpp_plasma['retur_pakan'];
            $data_voadip_plasma = null;
            foreach ($d_rhpp_plasma['voadip'] as $k_voadip => $v_voadip) {
                $data_voadip_plasma[] = array(
                    'tanggal' => $v_voadip['tanggal'],
                    'sj' => $v_voadip['nota'],
                    'barang' => $v_voadip['barang'],
                    'jumlah' => $v_voadip['jumlah'],
                    'harga' => $v_voadip['harga'],
                    'total' => $v_voadip['total'],
                );
            }
            $data_retur_voadip_plasma = null;
            foreach ($d_rhpp_plasma['retur_voadip'] as $k_rvoadip => $v_rvoadip) {
                $data_retur_voadip_plasma[] = array(
                    'tanggal' => $v_rvoadip['tanggal'],
                    'no_retur' => $v_rvoadip['nota'],
                    'barang' => $v_rvoadip['barang'],
                    'jumlah' => $v_rvoadip['jumlah'],
                    'harga' => $v_rvoadip['harga'],
                    'total' => $v_rvoadip['total'],
                );
            }
            $data_rpah_plasma = null;
            foreach ($d_rhpp_plasma['penjualan'] as $k_penjualan => $v_penjualan) {
                // $key = $v_penjualan['tanggal'].' | '.$v_penjualan['nota'].' | '.$v_penjualan['bb'];
                $data_rpah_plasma[] = array(
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

            $bonus_pasar = $d_rhpp_plasma['persen_bonus_pasar'];
            $fcr = $d_rhpp_plasma['fcr'];
            $bb = $d_rhpp_plasma['bb'];
            $deplesi = $d_rhpp_plasma['deplesi'];
            $ip = $d_rhpp_plasma['ip'];

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
            $data = array(
                'id' => $id_tutup_siklus,
                'mitra' => $mitra,
                'noreg' => $noreg,
                'populasi' => $populasi,
                'kandang' => $kandang,
                'tgl_docin' => $tgl_docin,
                'tutup_siklus' => $tutup_siklus,
                'biaya_materai' => $biaya_materai,
                'potongan_pajak' => $potongan_pajak,
                'tgl_tutup' => $tgl_tutup,
                'rata_umur_panen' => $rata_umur_panen,
                // 'data_potongan_pajak' => $data_potongan_pajak,
                'populasi_bonus_insentif_listrik' => $populasi_bonus_insentif_listrik,
                'bonus_insentif_listrik' => $bonus_insentif_listrik,
                'total_bonus_insentif_listrik' => $total_bonus_insentif_listrik,
                'bonus_kematian' => $bonus_kematian,
                'bonus_insentif_fcr' => $bonus_insentif_fcr,
                // 'selisih_pakan' => $selisih_pakan,
                'biaya_opr' => $biaya_opr,
                'bonus_pasar' => $bonus_pasar,
                'fcr' => $fcr,
                'bb' => $bb,
                'deplesi' => $deplesi,
                'ip' => $ip,
                'user_cetak' => $this->userdata['detail_user']['nama_detuser'],
                'kanit' => !empty($d_rs['d_pengawas']) ? $d_rs['d_pengawas']['nama'] : '-'
            );
        }

        $content['data'] = $data;
        $content['data_plasma'] = $data_rhpp_plasma;

        $res_view_html = $this->load->view('transaksi/tsdrhpp/export_to_excel', $content, true);

        // header("Content-type: application/xls");
        // header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        // header("Content-type:   application/x-msexcel; charset=utf-8");
        header("Content-type:   application/ms-excel; charset=utf-8");
        $filename = 'RHPP_PLASMA_'.$noreg.'_'.str_replace(' ', '_', str_replace(',', '', $mitra)).'.xls';
        header("Content-Disposition: attachment; filename=".$filename."");
        echo $res_view_html;
    }

    public function export_excel_inti($_noreg)
    {
        $noreg = exDecrypt( $_noreg );

        $m_ts = new \Model\Storage\TutupSiklus_model();
        $d_ts = $m_ts->where('noreg', $noreg)->with(['potongan_pajak'])->first();

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('noreg', $noreg)->with(['mitra', 'dPengawas'])->first()->toArray();

        $m_rhpp = new \Model\Storage\Rhpp_model();
        $d_rhpp_plasma = $m_rhpp->where('noreg', $noreg)->where('jenis', 'rhpp_plasma')->first();
        $d_rhpp_inti = $m_rhpp->where('noreg', $noreg)->where('jenis', 'rhpp_inti')->with(['doc', 'pakan', 'oa_pakan', 'pindah_pakan', 'oa_pindah_pakan', 'retur_pakan', 'oa_retur_pakan', 'voadip', 'retur_voadip', 'penjualan', 'potongan', 'bonus'])->orderBy('id', 'desc')->first();

        $d_rhpp_inti = !empty($d_rhpp_inti) ? $d_rhpp_inti->toArray() : null;
        $d_rhpp_plasma = !empty($d_rhpp_plasma) ? $d_rhpp_plasma->toArray() : null;

        $jenis_mitra = $d_rs['mitra']['d_mitra']['jenis'];

        $id_tutup_siklus = $d_rhpp_inti['id_ts'];
        $mitra = $d_rhpp_inti['mitra'];
        $noreg = $d_rhpp_inti['noreg'];
        $populasi = $d_rhpp_inti['populasi'];
        $kandang = $d_rhpp_inti['kandang'];
        $tgl_docin = $d_rhpp_inti['tgl_docin'];
        $tutup_siklus = 1;
        $biaya_materai = $d_rhpp_inti['biaya_materai'];
        $potongan_pajak = $d_rhpp_inti['prs_potongan_pajak'];
        $tgl_tutup = $d_ts->tgl_tutup;
        $rata_umur_panen = $d_rhpp_inti['rata_umur'];
        $biaya_opr = $d_rhpp_inti['biaya_operasional'];
        $bonus_insentif_fcr = 0;
        $populasi_bonus_insentif_listrik = 0;
        $bonus_insentif_listrik = 0;
        $total_bonus_insentif_listrik = 0;
        $pendapatan_plasma = $d_rhpp_plasma['pdpt_peternak_belum_pajak'];

        $data_doc_inti['doc'] = array(
            'tgl_docin' => $d_rhpp_inti['doc']['tanggal'],
            'sj' => $d_rhpp_inti['doc']['nota'],
            'barang' => $d_rhpp_inti['doc']['barang'],
            'box' => $d_rhpp_inti['doc']['box'],
            'jumlah' => $d_rhpp_inti['doc']['jumlah'],
            'harga' => $d_rhpp_inti['doc']['harga'],
            'total' => $d_rhpp_inti['doc']['total']
        );
        $data_pakan_inti = null;
        foreach ($d_rhpp_inti['pakan'] as $k_pakan => $v_pakan) {
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

        foreach ($d_rhpp_inti['oa_pakan'] as $k_oa_pakan => $v_oa_pakan) {
            $key = str_replace('-', '', $v_oa_pakan['tanggal']).' | '.$v_oa_pakan['nota'].' | '.$v_oa_pakan['barang'].' | '.$v_oa_pakan['id'];

            $data_oa_pakan_inti[ $v_oa_pakan['tanggal'] ][ $v_oa_pakan['nopol'] ][ $key ] = $v_oa_pakan;
        }
        $data_oa_pakan_inti = empty($data_oa_pakan_inti) ? $_data_oa_pakan_inti['ongkos_angkut'] : $data_oa_pakan_inti;

        $data_pindah_pakan_inti = $d_rhpp_inti['pindah_pakan'];
        foreach ($d_rhpp_inti['oa_pindah_pakan'] as $k_oa_pindah_pakan => $v_oa_pindah_pakan) {
            $key = str_replace('-', '', $v_oa_pindah_pakan['tanggal']).' | '.$v_oa_pindah_pakan['nota'].' | '.$v_oa_pindah_pakan['barang'].' | '.$v_oa_pindah_pakan['id'];

            $data_oa_pindah_pakan_inti[ $v_oa_pindah_pakan['tanggal'] ][ $v_oa_pindah_pakan['nopol'] ][ $key ] = $v_oa_pindah_pakan;
        }
        $data_oa_pindah_pakan_inti = empty($data_oa_pindah_pakan_inti) ? $_data_oa_pindah_pakan_inti['ongkos_angkut'] : $data_oa_pindah_pakan_inti;

        $data_retur_pakan_inti = $d_rhpp_inti['retur_pakan'];
        $data_oa_retur_pakan_inti = null;
        foreach ($d_rhpp_inti['oa_retur_pakan'] as $k_oa_retur_pakan => $v_oa_retur_pakan) {
            $key = str_replace('-', '', $v_oa_retur_pakan['tanggal']).' | '.$v_oa_retur_pakan['nota'].' | '.$v_oa_retur_pakan['barang'].' | '.$v_oa_retur_pakan['id'];

            $data_oa_retur_pakan_inti[ $v_oa_retur_pakan['tanggal'] ][ $v_oa_retur_pakan['nopol'] ][ $key ] = $v_oa_retur_pakan;
        }
        $data_voadip_inti = null;
        foreach ($d_rhpp_inti['voadip'] as $k_voadip => $v_voadip) {
            $m_brg = new \Model\Storage\Barang_model();
            $d_brg = $m_brg->where('nama', 'like', '%'.$v_voadip['barang'].'%')->orderBy('id', 'desc')->first();

            $data_voadip_inti[] = array(
                'tanggal' => $v_voadip['tanggal'],
                'sj' => $v_voadip['nota'],
                'barang' => $v_voadip['barang'],
                'jumlah' => $v_voadip['jumlah'],
                'harga' => $v_voadip['harga'],
                'total' => $v_voadip['total'],
                'decimal' => !empty($d_brg) ? $d_brg->desimal_harga : 2
            );
        }
        $data_retur_voadip_inti = null;
        foreach ($d_rhpp_inti['retur_voadip'] as $k_rvoadip => $v_rvoadip) {
            $m_brg = new \Model\Storage\Barang_model();
            $d_brg = $m_brg->where('nama', 'like', '%'.$v_rvoadip['barang'].'%')->orderBy('id', 'desc')->first();

            $data_retur_voadip_inti[] = array(
                'tanggal' => $v_rvoadip['tanggal'],
                'no_retur' => $v_rvoadip['nota'],
                'barang' => $v_rvoadip['barang'],
                'jumlah' => $v_rvoadip['jumlah'],
                'harga' => $v_rvoadip['harga'],
                'total' => $v_rvoadip['total'],
                'decimal' => !empty($d_brg) ? $d_brg->desimal_harga : 2
            );
        }
        $data_rpah_inti = null;
        foreach ($d_rhpp_inti['penjualan'] as $k_penjualan => $v_penjualan) {
            $data_rpah_inti[] = array(
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

        $bonus_pasar = $d_rhpp_plasma['bonus_pasar'];
        $bonus_kematian = $d_rhpp_inti['bonus_kematian'];
        $fcr = $d_rhpp_inti['fcr'];
        $bb = $d_rhpp_inti['bb'];
        $deplesi = $d_rhpp_inti['deplesi'];
        $ip = $d_rhpp_inti['ip'];
        $cn = $d_rhpp_inti['cn'];

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

        // cetak_r( $data_detail_inti );

        $data_rhpp_inti = array(
            'detail' => $data_detail_inti
        );
        $data = array(
            'id' => $id_tutup_siklus,
            'jenis_mitra' => $jenis_mitra,
            'mitra' => $mitra,
            'noreg' => $noreg,
            'populasi' => $populasi,
            'kandang' => $kandang,
            'tgl_docin' => $tgl_docin,
            'tutup_siklus' => $tutup_siklus,
            'biaya_materai' => $biaya_materai,
            'potongan_pajak' => $potongan_pajak,
            'tgl_tutup' => $tgl_tutup,
            'rata_umur_panen' => $rata_umur_panen,
            'data_potongan_pajak' => 0,
            'populasi_bonus_insentif_listrik' => $populasi_bonus_insentif_listrik,
            'bonus_insentif_listrik' => $bonus_insentif_listrik,
            'total_bonus_insentif_listrik' => $total_bonus_insentif_listrik,
            'bonus_insentif_fcr' => $bonus_insentif_fcr,
            'selisih_pakan' => 0,
            'biaya_opr' => $biaya_opr,
            'bonus_pasar' => $bonus_pasar,
            'fcr' => $fcr,
            'bb' => $bb,
            'deplesi' => $deplesi,
            'ip' => $ip,
            'pendapatan_plasma' => $pendapatan_plasma,
            'user_cetak' => $this->userdata['detail_user']['nama_detuser'],
            'kanit' => !empty($d_rs['d_pengawas']) ? $d_rs['d_pengawas']['nama'] : '-',
            'cn' => $cn
        );

        $content['data'] = $data;
        $content['data_inti'] = $data_rhpp_inti;

        // cetak_r( $content['data_inti'], 1 );

        $res_view_html = $this->load->view('transaksi/tsdrhpp/export_to_excel_inti', $content, true);

        header("Content-type: application/xls");
        $filename = 'RHPP_INTI_'.$noreg.'_'.str_replace(' ', '_', str_replace(',', '', $mitra)).'.xls';
        header("Content-Disposition: attachment; filename=".$filename."");
        echo $res_view_html;
    }

    public function export_pdf($_noreg)
    {
        $noreg = exDecrypt( $_noreg );

        $m_ts = new \Model\Storage\TutupSiklus_model();
        $d_ts = $m_ts->where('noreg', $noreg)->with(['potongan_pajak'])->first();

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('noreg', $noreg)->with(['dPengawas', 'dSampling', 'mitra', 'dKandang', 'data_perusahaan'])->first()->toArray();

        $npwp = $d_rs['mitra']['d_mitra']['npwp'];

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select prs.* from perusahaan prs
            where
                prs.kode = '".$d_rs['mitra']['d_mitra']['perusahaan']."'
            order by
                prs.id desc
        ";
        $d_prs = $m_conf->hydrateRaw( $sql );

        $perusahaan = null;
        if ( $d_prs->count() > 0 ) {
            $d_prs = $d_prs->toArray()[0];

            $perusahaan = $d_prs['perusahaan'];
        }

        $rt_mitra = !empty($d_rs['mitra']['d_mitra']['alamat_rt']) ? ', RT.'.$d_rs['mitra']['d_mitra']['alamat_rt'] : null;
        $rw_mitra = !empty($d_rs['mitra']['d_mitra']['alamat_rw']) ? ' / RW.'.$d_rs['mitra']['d_mitra']['alamat_rw'] : null;
        $kelurahan_mitra = !empty($d_rs['mitra']['d_mitra']['alamat_kelurahan']) ? ', Kel. '.$d_rs['mitra']['d_mitra']['alamat_kelurahan'] : null;
        $kecamatan_mitra = !empty($d_rs['mitra']['d_mitra']['d_kecamatan']) ? ', Kec. '.$d_rs['mitra']['d_mitra']['d_kecamatan']['nama'] : null;
        $kab_kota_mitra = !empty($d_rs['mitra']['d_mitra']['d_kecamatan']['d_kota']) ? ', '.str_replace('Kota ', '', str_replace('Kab ', '', $d_rs['mitra']['d_mitra']['d_kecamatan']['d_kota']['nama'])) : null;

        $alamat_mitra = $d_rs['mitra']['d_mitra']['alamat_jalan'] . $rt_mitra . $rw_mitra . $kelurahan_mitra . $kecamatan_mitra . $kab_kota_mitra;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select mm.* from mitra_mapping mm
            where
                mm.nomor = '".$d_rs['mitra']['d_mitra']['nomor']."'
        ";
        $d_mm = $m_conf->hydrateRaw( $sql );

        $mitra_mapping = null;
        if ( $d_mm->count() > 0 ) {
            $d_mm = $d_mm->toArray();

            foreach ($d_mm as $k_mm => $v_mm) {
                $mitra_mapping[] = $v_mm['id'];
            }
        }

        $m_kdg = new \Model\Storage\Kandang_model();
        $d_kdg = $m_kdg->whereIn('mitra_mapping', $mitra_mapping)->where('kandang', $d_rs['d_kandang']['kandang'])->orderBy('id', 'desc')->with(['dKecamatan', 'd_unit'])->first()->toArray();

        $rt_kdg = !empty($d_kdg['alamat_rt']) ? ', RT.'.$d_kdg['alamat_rt'] : null;
        $rw_kdg = !empty($d_kdg['alamat_rw']) ? ' / RW.'.$d_kdg['alamat_rw'] : null;
        $kelurahan_kdg = !empty($d_kdg['alamat_kelurahan']) ? ', Kel. '.$d_kdg['alamat_kelurahan'] : null;
        $kecamatan_kdg = !empty($d_kdg['d_kecamatan']) ? ', Kec. '.$d_kdg['d_kecamatan']['nama'] : null;
        $kab_kota_kdg = !empty($d_kdg['d_kecamatan']['d_kota']) ? ', '.str_replace('Kota ', '', str_replace('Kab ', '', $d_kdg['d_kecamatan']['d_kota']['nama'])) : null;

        $alamat_kdg = $d_kdg['alamat_jalan'] . $rt_kdg . $rw_kdg . $kelurahan_kdg . $kecamatan_kdg . $kab_kota_kdg;

        $unit = str_replace('Kota ', '', str_replace('Kab ', '', $d_kdg['d_unit']['nama']));

        $m_real = new \Model\Storage\RealSJ_model();
        $d_real = $m_real->where('noreg', $noreg)->orderBy('tgl_panen', 'desc')->first();

        $tgl_selesai_panen = $d_ts->tgl_tutup;
        if ( $d_real ) {
            $tgl_selesai_panen = $d_real->tgl_panen;
        }

        $m_rhpp = new \Model\Storage\Rhpp_model();
        $d_rhpp_plasma = $m_rhpp->where('noreg', $noreg)->where('jenis', 'rhpp_plasma')->with(['doc', 'pakan', 'oa_pakan', 'pindah_pakan', 'oa_pindah_pakan', 'retur_pakan', 'oa_retur_pakan', 'voadip', 'retur_voadip', 'penjualan', 'potongan', 'bonus', 'piutang'])->orderBy('id', 'desc')->first();

        $d_rhpp_plasma = !empty($d_rhpp_plasma) ? $d_rhpp_plasma->toArray() : null;

        $id_tutup_siklus = $d_rhpp_plasma['id_ts'];
        $mitra = $d_rhpp_plasma['mitra'];
        $noreg = $d_rhpp_plasma['noreg'];
        $populasi = $d_rhpp_plasma['populasi'];
        $kandang = $d_rhpp_plasma['kandang'];
        $tgl_docin = $d_rhpp_plasma['tgl_docin'];
        $tutup_siklus = 1;
        $biaya_materai = $d_rhpp_plasma['biaya_materai'];
        $tgl_tutup = $d_ts->tgl_tutup;
        $rata_umur_panen = $d_rhpp_plasma['rata_umur'];
        $tot_penjualan_ayam = $d_rhpp_plasma['tot_penjualan_ayam'];
        $tot_pembelian_sapronak = $d_rhpp_plasma['tot_pembelian_sapronak'];
        $biaya_opr = $d_rhpp_plasma['biaya_operasional'];
        $bonus_insentif_fcr = $d_rhpp_plasma['bonus_insentif_fcr'];
        $bonus_kematian = $d_rhpp_plasma['bonus_kematian'];
        $populasi_bonus_insentif_listrik = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['populasi_bonus_insentif_listrik'] : 0;
        $bonus_insentif_listrik = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['bonus_insentif_listrik'] : 0;
        $total_bonus_insentif_listrik = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['total_bonus_insentif_listrik'] : 0;
        $biaya_materai = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['biaya_materai'] : 0;
        $pdpt_peternak_belum_pajak_dan_materai = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['pdpt_peternak_belum_pajak'] + $biaya_materai : 0;
        $pdpt_peternak_belum_pajak = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['pdpt_peternak_belum_pajak'] : 0;
        $prs_potongan_pajak = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['prs_potongan_pajak'] : 0;
        $potongan_pajak = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['potongan_pajak'] : 0;
        $pdpt_peternak_sudah_pajak = $pdpt_peternak_belum_pajak - $potongan_pajak;
        $rata_harga_panen = 0;
        $data_pemakaian_pakan = null;
        $hasil = null;
        $biaya_produksi = null;
        $hasil_produksi = null;
        $catatan = !empty($d_rhpp_plasma) ? $d_rhpp_plasma['catatan_print'] : 0;

        $data = null;
        $data_rhpp_plasma = null;

        if ( !empty($d_rhpp_plasma) ) {
            $biaya_produksi['doc'] = array(
                'nama' => $d_rhpp_plasma['doc']['barang'],
                'jumlah' => $d_rhpp_plasma['doc']['jumlah'],
                'harga' => $d_rhpp_plasma['doc']['harga'],
                'total' => $d_rhpp_plasma['doc']['total']
            );

            $biaya_produksi['vaksin'] = array(
                'nama' => $d_rhpp_plasma['doc']['vaksin'],
                'jumlah' => $d_rhpp_plasma['doc']['jumlah'],
                'harga' => $d_rhpp_plasma['doc']['harga_vaksin'],
                'total' => $d_rhpp_plasma['doc']['total_vaksin']
            );

            $data_doc_plasma['doc'] = array(
                'tgl_docin' => $d_rhpp_plasma['doc']['tanggal'],
                'sj' => $d_rhpp_plasma['doc']['nota'],
                'barang' => $d_rhpp_plasma['doc']['barang'],
                'box' => $d_rhpp_plasma['doc']['box'],
                'jumlah' => $d_rhpp_plasma['doc']['jumlah'],
                'harga' => $d_rhpp_plasma['doc']['harga'],
                'total' => $d_rhpp_plasma['doc']['total']
            );
            $data_doc_plasma['vaksin'] = array(
                'barang' => $d_rhpp_plasma['doc']['vaksin'],
                'harga' => $d_rhpp_plasma['doc']['harga_vaksin'],
                'total' => $d_rhpp_plasma['doc']['total_vaksin']
            );
            $data_pakan_plasma = null;
            foreach ($d_rhpp_plasma['pakan'] as $k_pakan => $v_pakan) {
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
            $data_pindah_pakan_plasma = $d_rhpp_plasma['pindah_pakan'];
            if ( !empty($data_pindah_pakan_plasma) && count($data_pindah_pakan_plasma) > 0 ) {
                foreach ($data_pindah_pakan_plasma as $k_ppp => $v_ppp) {
                    $data_pemakaian_pakan[ $v_ppp['barang'] ]['jumlah'] -= $v_ppp['jumlah'];
                    $data_pemakaian_pakan[ $v_ppp['barang'] ]['zak'] -= $v_ppp['zak'];

                    $biaya_produksi['pakan'][ $v_ppp['barang'].' | '.$v_ppp['harga'] ]['jumlah'] -= $v_ppp['jumlah'];
                    $biaya_produksi['pakan'][ $v_ppp['barang'].' | '.$v_ppp['harga'] ]['zak'] -= $v_ppp['zak'];
                    $biaya_produksi['pakan'][ $v_ppp['barang'].' | '.$v_ppp['harga'] ]['total'] -= $v_ppp['total'];
                }
            }
            $data_retur_pakan_plasma = $d_rhpp_plasma['retur_pakan'];
            if ( !empty($data_retur_pakan_plasma) && count($data_retur_pakan_plasma) > 0 ) {
                foreach ($data_retur_pakan_plasma as $k_rpp => $v_rpp) {
                    $data_pemakaian_pakan[ $v_rpp['barang'] ]['jumlah'] -= $v_rpp['jumlah'];
                    $data_pemakaian_pakan[ $v_rpp['barang'] ]['zak'] -= $v_rpp['zak'];

                    $biaya_produksi['pakan'][ $v_rpp['barang'].' | '.$v_rpp['harga'] ]['jumlah'] -= $v_rpp['jumlah'];
                    $biaya_produksi['pakan'][ $v_rpp['barang'].' | '.$v_rpp['harga'] ]['zak'] -= $v_rpp['zak'];
                    $biaya_produksi['pakan'][ $v_rpp['barang'].' | '.$v_rpp['harga'] ]['total'] -= $v_rpp['total'];
                }
            }

            $total_voadip = 0;

            $data_voadip_plasma = null;
            foreach ($d_rhpp_plasma['voadip'] as $k_voadip => $v_voadip) {
                $m_brg = new \Model\Storage\Barang_model();
                $d_brg = $m_brg->where('nama', 'like', '%'.$v_voadip['barang'].'%')->orderBy('id', 'desc')->first();

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
            $data_retur_voadip_plasma = null;
            foreach ($d_rhpp_plasma['retur_voadip'] as $k_rvoadip => $v_rvoadip) {
                $m_brg = new \Model\Storage\Barang_model();
                $d_brg = $m_brg->where('nama', 'like', '%'.$v_rvoadip['barang'].'%')->orderBy('id', 'desc')->first();

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

            $biaya_produksi['voadip'] = array(
                'total' => $total_voadip
            );

            $data_rpah_plasma = null;

            $total_tonase = 0;
            $total_ekor = 0;
            $total_nilai = 0;
            foreach ($d_rhpp_plasma['penjualan'] as $k_penjualan => $v_penjualan) {
                // $key = $v_penjualan['tanggal'].' | '.$v_penjualan['nota'].' | '.$v_penjualan['bb'];
                $data_rpah_plasma[] = array(
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

            $rata_harga_panen = $total_nilai / $total_tonase;

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

            $prs_bonus_pasar = $d_rhpp_plasma['persen_bonus_pasar'];
            $bonus_pasar = $d_rhpp_plasma['bonus_pasar'];
            $fcr = $d_rhpp_plasma['fcr'];
            $bb = $d_rhpp_plasma['bb'];
            $deplesi = $d_rhpp_plasma['deplesi'];
            $ip = $d_rhpp_plasma['ip'];

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

            $data_rhpp_plasma = array(
                'detail' => $data_detail_plasma
            );
            $data = array(
                'id' => $id_tutup_siklus,
                'unit' => $unit,
                'mitra' => $mitra,
                'alamat_mitra' => $alamat_mitra,
                'alamat_kdg' => $alamat_kdg,
                'npwp' => $npwp,
                'noreg' => $noreg,
                'populasi' => $populasi,
                'kandang' => $kandang,
                'tgl_docin' => $tgl_docin,
                'tgl_selesai_panen' => $tgl_selesai_panen,
                'tutup_siklus' => $tutup_siklus,
                'biaya_materai' => $biaya_materai,
                'tgl_tutup' => $tgl_tutup,
                'rata_umur_panen' => $rata_umur_panen,
                'total_tonase_panen' => $total_tonase,
                'total_ekor_panen' => $total_ekor,
                'tot_penjualan_ayam' => $tot_penjualan_ayam,
                'tot_pembelian_sapronak' => $tot_pembelian_sapronak,
                'populasi_bonus_insentif_listrik' => $populasi_bonus_insentif_listrik,
                'bonus_insentif_listrik' => $bonus_insentif_listrik,
                'total_bonus_insentif_listrik' => $total_bonus_insentif_listrik,
                'bonus_insentif_fcr' => $bonus_insentif_fcr,
                'bonus_kematian' => $bonus_kematian,
                'pdpt_peternak_belum_pajak_dan_materai' => $pdpt_peternak_belum_pajak_dan_materai,
                'biaya_materai' => $biaya_materai,
                'pdpt_peternak_belum_pajak' => $pdpt_peternak_belum_pajak,
                'prs_potongan_pajak' => $prs_potongan_pajak,
                'potongan_pajak' => $potongan_pajak,
                'pdpt_peternak_sudah_pajak' => $pdpt_peternak_sudah_pajak,
                'total_bayar_hutang' => $total_bayar_hutang,
                'pdpt_peternak_sudah_potong_hutang' => ($pdpt_peternak_sudah_pajak - $total_bayar_hutang),
                'rata_harga_panen' => $rata_harga_panen,
                'biaya_opr' => $biaya_opr,
                'prs_bonus_pasar' => $prs_bonus_pasar,
                'bonus_pasar' => $bonus_pasar,
                'fcr' => $fcr,
                'bb' => $bb,
                'deplesi' => $deplesi,
                'ip' => $ip,
                'user_cetak' => $this->userdata['detail_user']['nama_detuser'],
                'unit_karyawan' => $unit_karyawan,
                'ppl' => !empty($d_rs['d_sampling']) ? $d_rs['d_sampling']['nama'] : '-',
                'kanit' => !empty($d_rs['d_pengawas']) ? $d_rs['d_pengawas']['nama'] : '-',
                'data_pemakaian_pakan' => $data_pemakaian_pakan,
                'hasil' => $hasil,
                'biaya_produksi' => $biaya_produksi,
                'hasil_produksi' => $hasil_produksi,
                'catatan' => trim($catatan),
                'perusahaan' => $perusahaan
            );
        }

        $content['data'] = $data;
        $content['data_plasma'] = $data_rhpp_plasma;

        // cetak_r( $content['data_plasma'] );

        $res_view_html = $this->load->view('transaksi/tsdrhpp/export_to_pdf', $content, true);

        // cetak_r( $res_view_html );

        $this->load->library('PDFGenerator');
        $this->pdfgenerator->generate($res_view_html, "coba", 'legal', 'portrait');
    }

    public function updateCatatan()
    {
        $noreg = $this->input->post('noreg');
        $keterangan = $this->input->post('keterangan');

        try {
            $_noreg = exDecrypt( $noreg );

            $m_rhpp = new \Model\Storage\Rhpp_model();
            $m_rhpp->where('noreg', $_noreg)->update(
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
                $m_rhpp = new \Model\Storage\Rhpp_model();
                $m_rhpp->where('id_ts', $id)->where('jenis', $v_rhpp['jenis'])->update(
                    array(
                        'cn' => $params['nilai_cn'],
                        'biaya_operasional' => $params['nilai_opr'],
                        'lr_inti' => $v_rhpp['lr_inti']
                    )
                );
            }

            $m_ts = new \Model\Storage\TutupSiklus_model();
            $d_ts = $m_ts->where('id', $id)->first();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal 'RHPP', NULL, NULL, 0, 'tutup_siklus', ".$id.", ".$id.", 2";

            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'submit cn oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_ts, $deskripsi_log);
            
            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil disimpan';
            $this->result['content'] = array('id' => $id);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function modalPiutang() {
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
                (p.nominal - isnull(bp.nominal, 0)) > 0
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
        $html = $this->load->view('transaksi/tsdrhpp/modal_piutang', $content, TRUE);

        echo $html;
    }

    public function tes()
    {
        $kode_unit = 'MLG';

        $m_rhpp = new \Model\Storage\Rhpp_model();
        $invoice = $m_rhpp->getNoInvoice('INV/RHPP/'.$kode_unit);

        cetak_r( $invoice, 1 );

        $_noreg = '22090170801';

        $get_data_pakan = $this->get_data_pakan( $_noreg );
        // // $get_data_pindah_pakan = $this->get_data_pindah_pakan( $_noreg, $get_data_pakan );
        // // $get_data_retur_pakan = $this->get_data_retur_pakan( $_noreg );

        // // $get_data_voadip = $this->get_data_voadip( $_noreg );
        // // $get_data_retur_voadip = $this->get_data_retur_voadip( $_noreg, $get_data_voadip );
        cetak_r( $get_data_pakan, 1 );

        // $m_conf = new \Model\Storage\Conf();
        // $sql = "
        //     select * from tutup_siklus where tgl_tutup >= '2023-08-01'
        // ";
        // $d_conf = $m_conf->hydrateRaw( $sql );

        // if ( $d_conf->count() > 0 ) {
        //     $d_conf = $d_conf->toArray();

        //     foreach ($d_conf as $key => $value) {
        //         $id = $value['id'];

        //         $m_conf = new \Model\Storage\Conf();
        //         $sql = "exec insert_jurnal 'RHPP', NULL, NULL, 0, 'tutup_siklus', ".$id.", ".$id.", 2";
        
        //         $d_conf = $m_conf->hydrateRaw( $sql );
        //     }
        // }
    }
}