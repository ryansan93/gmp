<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PostingUlang extends Public_Controller {

    private $pathView = 'accounting/posting_ulang/';
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
        // if ( $this->hakAkses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/jquery/easy-autocomplete/jquery.easy-autocomplete.min.js",
                "assets/select2/js/select2.min.js",
                "assets/accounting/posting_ulang/js/posting-ulang.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/accounting/posting_ulang/css/posting-ulang.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->hakAkses;
            $content['title_panel'] = 'Posting';

            // Load Indexx
            $data['title_menu'] = 'Posting';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);
            $this->load->view($this->template, $data);
        // } else {
        //     showErrorAkses();
        // }
    }

    public function postingUlang() {
        $params = $this->input->post('params');

        try {
            $bulan = $params['bulan'];
            $tahun = substr($params['tahun'], 0, 4);
            $jenis = $params['jenis'];
            
            $angka_bulan = (strlen($bulan) == 1) ? '0'.$bulan : $bulan;
            
            $date = $tahun.'-'.$angka_bulan.'-01';
            $start_date = date("Y-m-d", strtotime($date));
            $end_date = date("Y-m-t", strtotime($date));

            $m_conf = new \Model\Storage\Conf();
            $sql = "select * from periode_fiskal where start_date = '".$start_date."' and end_date = '".$end_date."' and status = 0";
            $d_conf = $m_conf->hydrateRaw( $sql );
            if ( $d_conf->count() > 0 ) {
                $this->result['message'] = '<span class="red">Periode fiskal sudah di tutup, tidak bisa melakukan posting ulang !!!</span>';
            } else {
                if ( $jenis == 'doc' ) {
                    $status = $this->postingUlangDoc($start_date, $end_date);
                }
    
                if ( $jenis == 'pakan' ) {
                    $status = $this->postingUlangPakan($start_date, $end_date);
                }
    
                if ( $jenis == 'voadip' ) {
                    $status = $this->postingUlangVoadip($start_date, $end_date);
                }

                if ( $jenis == 'bank' ) {
                    $status = $this->postingUlangBank($start_date, $end_date);
                }

                if ( $jenis == 'kas' ) {
                    $status = $this->postingUlangKas($start_date, $end_date);
                }

                if ( $jenis == 'bakul' ) {
                    $status = $this->postingUlangBakul($start_date, $end_date);
                }

                if ( $jenis == 'pembayaran' ) {
                    $status = $this->postingUlangPembayaran($start_date, $end_date);
                }

                $this->result['status'] = $status;
                $this->result['message'] = 'Data berhasil di posting ulang, cek laporan yang berhubung.';
            }
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function postingUlangDoc( $start_date, $end_date ) {
        /* TERIMA DOC DI KANDANG */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select td.* from terima_doc td 
            left join
                (select * from det_jurnal dj where coa_asal = '21180.200' and tbl_name = 'terima_doc') dj
                on
                    cast(td.id as varchar(max)) = dj.tbl_id
            where
                td.datang between '".$start_date."' and '".$end_date."' and
                (dj.id is null or dj.tanggal is null)
            order by
                dj.tanggal asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {
                Modules::run( 'base/InsertJurnal/exec', '/transaksi/PenerimaanDocMobile', $v_conf['id'], $v_conf['id'], 2);
            }
        }
        /* END - TERIMA DOC DI KANDANG */

        /* TERIMA DOC YANG SUDAH TERHAPUS */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select dj.* from det_jurnal dj 
            left join
                terima_doc td 
                on
                    td.id = dj.tbl_id
            where 
                dj.tbl_name = 'terima_doc' and
                td.id is null and
                dj.tanggal between '".$start_date."' and '".$end_date."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {            
                Modules::run( 'base/InsertJurnal/exec', '/transaksi/PenerimaanDocMobile', $v_conf['tbl_id'], $v_conf['tbl_id'], 3);
            }
        }
        /* END - TERIMA DOC YANG SUDAH TERHAPUS */

        return 1;
    }

    public function postingUlangPakan( $start_date, $end_date ) {
        /* PERSEDIAAN PAKAN DI GUDANG */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                dtp.tbl_id,
                dtp.tanggal,
                dtp.kode_trans,
                dj.nominal,
                dtp.nominal_terima,
                dtp.jenis_kirim,
                dj.nominal - dtp.nominal_terima as selisih
            from 
            (
                select tp.id as tbl_id, sum(dtp.jumlah * op.harga) as nominal_terima, kp.no_order as kode_trans, kp.jenis_kirim, tp.tgl_terima as tanggal from det_terima_pakan dtp 
                left join
                    terima_pakan tp 
                    on
                        dtp.id_header = tp.id
                left join
                    kirim_pakan kp 
                    on
                        tp.id_kirim_pakan = kp.id
                left join
                    (
                        select opd.*, op.no_order from order_pakan_detail opd 
                        left join
                            order_pakan op 
                            on
                                opd.id_header = op.id
                    ) op
                    on
                        op.no_order = kp.no_order and
                        op.barang = dtp.item
                where
                    kp.jenis_kirim = 'opks'
                group by
                    tp.id, kp.no_order, kp.jenis_kirim, tp.tgl_terima
            ) dtp
            left join
                (select tbl_id, tbl_name, sum(nominal) as nominal from det_jurnal where coa_asal = '21180.100' and tbl_name = 'terima_pakan' group by tbl_id, tbl_name) dj
                on
                    dj.tbl_id = cast(dtp.tbl_id as varchar(50))
            where
                isnull(dj.nominal, 0) - isnull(dtp.nominal_terima, 0) <> 0 and
                dtp.tanggal between '".$start_date."' and '".$end_date."'
            order by
                dtp.tanggal asc,
                dtp.kode_trans asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {
                Modules::run( 'base/InsertJurnal/exec', '/transaksi/PengirimanPenerimaanPakan', $v_conf['tbl_id'], $v_conf['tbl_id'], 2);
            }
        }
        /* END - PERSEDIAAN PAKAN DI GUDANG */

        /* PERSEDIAAN PAKAN DI KANDANG */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.tgl_trans,
                data.kode_trans,
                data.id_terima,
                isnull(sum(data.hrg_beli * data.jumlah), 0) as nominal_terima,
                dj.nominal as nominal_jurnal
            from
            (
                select tp.id as id_terima, dss.* from det_stok_siklus dss 
                left join
                    kirim_pakan kp 
                    on
                        dss.kode_trans = kp.no_order 
                left join
                    terima_pakan tp 
                    on
                        tp.id_kirim_pakan = kp.id
                where
                    dss.jenis_barang = 'pakan'
            ) data
            left join
                (select * from det_jurnal where coa_tujuan = '12041.000') dj
                on
                    cast(data.id_terima as varchar(50)) = dj.tbl_id
            where
            	data.tgl_trans between '".$start_date."' and '".$end_date."'
            group by
                data.tgl_trans,
                data.kode_trans,
                data.id_terima,
                dj.nominal
            having
                isnull(sum(data.hrg_beli * data.jumlah), 0) - isnull(dj.nominal, 0) <> 0
            order by
                data.tgl_trans asc,
                data.kode_trans asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {
                Modules::run( 'base/InsertJurnal/exec', '/transaksi/PengirimanPenerimaanPakan', $v_conf['id_terima'], $v_conf['id_terima'], 2);
            }
        }
        /* END - PERSEDIAAN PAKAN DI KANDANG */

        /* PEMAKAIAN PAKAN LHK DI KANDANG */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.tgl_trans,
                data.kode_trans,
                data.total as nominal_terima,
                dj.nominal as nominal_jurnal,
                isnull(data.total, 0) - isnull(dj.nominal, 0) as selisih
            from
            (
                select dsts.tgl_trans, dsts.kode_trans, sum(dsts.jumlah*dss.hrg_beli) as total from det_stok_trans_siklus dsts
                left join
                    det_stok_siklus dss 
                    on
                        dsts.id_header = dss.id
                where
                    dsts.tbl_name = 'lhk' and
                    dss.jenis_barang = 'pakan'
                group by
                    dsts.tgl_trans, dsts.kode_trans
            ) data
            left join
                (select tbl_id, tbl_name, sum(nominal) as nominal from det_jurnal where coa_tujuan = '71101.000' group by tbl_id, tbl_name) dj
                on
                    data.kode_trans = dj.tbl_id
            where
                data.tgl_trans between '".$start_date."' and '".$end_date."'
                and isnull(data.total, 0) - isnull(dj.nominal, 0) <> 0
            order by
                data.tgl_trans asc,
                data.kode_trans asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {
                Modules::run( 'base/InsertJurnal/exec', '/transaksi/LHK', $v_conf['kode_trans'], $v_conf['kode_trans'], 2);
            }
        }
        /* END - PEMAKAIAN PAKAN LHK DI KANDANG */

        /* RETUR PAKAN */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.tgl_trans,
                data.kode_trans,
                data.no_order,
                data.total as nominal_terima,
                isnull(dj.nominal, 0) as nominal_jurnal,
                isnull(data.total, 0) - isnull(dj.nominal, 0) as selisih
            from
            (
                select dsts.tgl_trans, rp.id as kode_trans, sum(dsts.jumlah*dss.hrg_beli) as total, dsts.kode_trans as no_order from det_stok_trans_siklus dsts
                left join
                    det_stok_siklus dss 
                    on
                        dsts.id_header = dss.id
                left join
                    retur_pakan rp 
                    on
                        rp.no_retur = dsts.kode_trans
                where
                    dsts.tbl_name = 'retur_pakan' and
                    dss.jenis_barang = 'pakan'
                group by
                    dsts.tgl_trans, rp.id, dsts.kode_trans
            ) data
            left join
                (select * from det_jurnal where coa_tujuan = '12030.000' and tbl_name = 'retur_pakan') dj
                on
                    data.kode_trans = dj.tbl_id
            where
                data.tgl_trans between '".$start_date."' and '".$end_date."' and
                isnull(data.total, 0) - isnull(dj.nominal, 0) <> 0
            order by
                data.tgl_trans asc,
                data.kode_trans asc
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {            
                Modules::run( 'base/InsertJurnal/exec', '/transaksi/ReturPakan', $v_conf['kode_trans'], $v_conf['kode_trans'], 2);
            }
        }
        /* END - RETUR PAKAN */

        /* PEMAKAIAN PAKAN YANG SUDAH TERHAPUS */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select dj.* from det_jurnal dj 
            left join
                lhk l
                on
                    l.id = dj.tbl_id
            where 
                dj.tbl_name = 'lhk' and
                l.id is null and
                dj.tanggal between '".$start_date."' and '".$end_date."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {            
                Modules::run( 'base/InsertJurnal/exec', '/transaksi/LHK', $v_conf['tbl_id'], $v_conf['tbl_id'], 3);
            }
        }
        /* END - PEMAKAIAN PAKAN YANG SUDAH TERHAPUS */

        /* TERIMA PAKAN YANG SUDAH TERHAPUS */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select dj.* from det_jurnal dj 
            left join
                terima_pakan tp 
                on
                    tp.id = dj.tbl_id
            where 
                dj.tbl_name = 'terima_pakan' and
                tp.id is null and
                dj.tanggal between '".$start_date."' and '".$end_date."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {            
                Modules::run( 'base/InsertJurnal/exec', '/transaksi/PengirimanPenerimaanPakan', $v_conf['tbl_id'], $v_conf['tbl_id'], 3);
            }
        }
        /* END - TERIMA PAKAN YANG SUDAH TERHAPUS */

        /* RETUR PAKAN YANG SUDAH TERHAPUS */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select dj.* from det_jurnal dj 
            left join
                retur_pakan rp 
                on
                    rp.id = dj.tbl_id
            where 
                dj.tbl_name = 'retur_pakan' and
                rp.id is null and
                dj.tanggal between '".$start_date."' and '".$end_date."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {            
                Modules::run( 'base/InsertJurnal/exec', '/transaksi/ReturPakan', $v_conf['tbl_id'], $v_conf['tbl_id'], 3);
            }
        }
        /* END - RETUR PAKAN YANG SUDAH TERHAPUS */

        return 1;
    }

    public function postingUlangVoadip( $start_date, $end_date ) {
        /* PERSEDIAAN PAKAN DI GUDANG */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.tgl_trans,
                data.kode_trans,
                data.id_terima,
                data.nominal_terima,
                dj.nominal as nominal_jurnal,
                data.nominal_terima - dj.nominal as selisih
                -- ,data.unit
            from
            (
                select
                    tv.tgl_terima as tgl_trans,
                    kv.no_order as kode_trans,
                    tv.id as id_terima,
                    sum(dtv.jumlah * ov.harga) as nominal_terima
                    -- , SUBSTRING(kv.no_order, 5, 3) as unit
                from det_terima_voadip dtv
                left join
                    terima_voadip tv
                    on
                        dtv.id_header = tv.id
                left join
                    kirim_voadip kv
                    on
                        tv.id_kirim_voadip = kv.id
                left join
                    (
                        select ov.no_order, ovd.kode_barang, ovd.harga from order_voadip_detail ovd 
                        left join
                            order_voadip ov
                            on
                                ovd.id_order = ov.id
                        group by
                            ov.no_order, ovd.kode_barang, ovd.harga
                    ) ov
                    on
                        dtv.item = ov.kode_barang and
                        kv.no_order = ov.no_order
                where
                    kv.jenis_kirim = 'opks'
                group by
                    tv.tgl_terima,
                    kv.no_order,
                    tv.id

                union all

                select
                    tv.tgl_terima as tgl_trans, 
                    kv.no_order as kode_trans, 
                    tv.id as id_terima, 
                    sum(ds.jumlah * ds.hrg_beli) as nominal_terima
                from (
                    select ds.* from det_stok ds
                    left join
                        stok s
                        on
                            ds.id_header = s.id
                    where
                        ds.jenis_barang = 'voadip' and
                        ds.tgl_trans = s.periode
                ) ds
                left join
                    kirim_voadip kv
                    on
                        ds.kode_trans = kv.no_order
                left join
                    terima_voadip tv
                    on
                        tv.id_kirim_voadip = kv.id
                where
                    kv.jenis_kirim = 'opkg' and kv.jenis_tujuan = 'gudang'
                group by
                    tv.tgl_terima, 
                    kv.no_order, 
                    tv.id
            ) data
            left join
                (select * from det_jurnal where coa_tujuan = '12050.000' and tbl_name = 'terima_voadip') dj
                on
                    data.id_terima = dj.tbl_id
            where
                isnull(data.nominal_terima, 0) - isnull(dj.nominal, 0) <> 0
                and data.tgl_trans between '".$start_date."' and '".$end_date."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {
                Modules::run( 'base/InsertJurnal/exec', '/transaksi/PenerimaanVoadip', $v_conf['id_terima'], $v_conf['id_terima'], 2);
            }
        }
        /* END - PERSEDIAAN VOADIP DI GUDANG */

        /* PERSEDIAAN VOADIP DI KANDANG */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.tgl_trans,
                data.kode_trans,
                data.id_terima,
                data.nominal_terima,
                dj.nominal as nominal_jurnal,
                data.nominal_terima - dj.nominal as selisih
            from
            (
                select tv.id as id_terima, tv.tgl_terima as tgl_trans, kv.no_order as kode_trans, sum(dss.jumlah * dss.hrg_beli) as nominal_terima from det_stok_siklus dss 
                left join
                    kirim_voadip kv
                    on
                        dss.kode_trans = kv.no_order 
                left join
                    terima_voadip tv
                    on
                        tv.id_kirim_voadip = kv.id
                where
                    dss.jenis_barang = 'voadip' and
                    kv.jenis_kirim = 'opkg'
                group by
                    tv.id, tv.tgl_terima, kv.no_order
            ) data
            left join
                (select * from det_jurnal where coa_tujuan = '12051.000' and tbl_name = 'terima_voadip') dj
                on
                    data.id_terima = dj.tbl_id
            where
                isnull(data.nominal_terima, 0) - isnull(dj.nominal, 0) <> 0
                and data.tgl_trans between '".$start_date."' and '".$end_date."'
            order by
                data.tgl_trans asc,
                data.kode_trans asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {
                Modules::run( 'base/InsertJurnal/exec', '/transaksi/PenerimaanVoadip', $v_conf['id_terima'], $v_conf['id_terima'], 2);
            }
        }
        /* END - PERSEDIAAN VOADIP DI KANDANG */

        /* RETUR VOADIP */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.tgl_trans,
                data.kode_trans,
                data.no_order,
                data.total as nominal_terima,
                dj.nominal as nominal_jurnal,
                data.total - dj.nominal as selisih
            from
            (
                select dsts.tgl_trans, rv.id as kode_trans, sum(dsts.jumlah*dss.hrg_beli) as total, dsts.kode_trans as no_order from det_stok_trans_siklus dsts
                left join
                    det_stok_siklus dss 
                    on
                        dsts.id_header = dss.id
                left join
                    retur_voadip rv 
                    on
                        rv.no_retur = dsts.kode_trans
                where
                    dsts.tbl_name = 'retur_voadip' and
                    dss.jenis_barang = 'voadip'
                group by
                    dsts.tgl_trans, rv.id, dsts.kode_trans
            ) data
            left join
                (select tbl_id, sum(nominal) as nominal from det_jurnal where coa_asal = '71102.000' and tbl_name = 'retur_voadip' group by tbl_id) dj
                on
                    data.kode_trans = dj.tbl_id
            where
                data.tgl_trans between '".$start_date."' and '".$end_date."' and
                (data.total - dj.nominal) <> 0
            order by
                data.tgl_trans asc,
                data.kode_trans asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {
                Modules::run( 'base/InsertJurnal/exec', '/transaksi/ReturVoadip', $v_conf['kode_trans'], $v_conf['kode_trans'], 2);
            }
        }
        /* END - RETUR VOADIP */

        /* TERIMA VOADIP YANG SUDAH TERHAPUS */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select dj.* from det_jurnal dj 
            left join
                terima_voadip tv 
                on
                    tv.id = dj.tbl_id
            where 
                dj.tbl_name = 'terima_voadip' and
                tv.id is null and
                dj.tanggal between '".$start_date."' and '".$end_date."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {            
                Modules::run( 'base/InsertJurnal/exec', '/transaksi/PenerimaanVoadip', $v_conf['tbl_id'], $v_conf['tbl_id'], 3);
            }
        }
        /* END - TERIMA VOADIP YANG SUDAH TERHAPUS */

        /* RETUR VOADIP YANG SUDAH TERHAPUS */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select dj.* from det_jurnal dj 
            left join
                retur_voadip rv 
                on
                    rv.id = dj.tbl_id
            where 
                dj.tbl_name = 'retur_voadip' and
                rv.id is null and
                dj.tanggal between '".$start_date."' and '".$end_date."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {            
                Modules::run( 'base/InsertJurnal/exec', '/transaksi/ReturVoadip', $v_conf['tbl_id'], $v_conf['tbl_id'], 3);
            }
        }
        /* END - RETUR VOADIP YANG SUDAH TERHAPUS */

        return 1;
    }

    /**
     * Bank Masuk & Bank Keluar tidak pakai setting_automatic_jurnal (jurnal-nya langsung dibuat
     * di controller masing-masing), jadi posting ulang di sini membangun ulang det_jurnal
     * langsung dari data km/kmitem (Bank Masuk) dan kk/kkitem (Bank Keluar) yang sudah tersimpan.
     */
    public function postingUlangBank( $start_date, $end_date ) {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select k.no_km from km k
            left join
                coa c
                on
                    c.coa = k.coa_bank
            where
                k.tgl_km between '".$start_date."' and '".$end_date."' and
                c.bank = 1
        ";
        $d_km = $m_conf->hydrateRaw( $sql );

        if ( $d_km->count() > 0 ) {
            foreach ($d_km->toArray() as $v_km) {
                $this->rebuildJurnalKm( $v_km['no_km'] );
            }
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select k.no_kk from kk k
            left join
                coa c
                on
                    c.coa = k.coa_bank
            where
                k.tgl_kk between '".$start_date."' and '".$end_date."' and
                c.bank = 1
        ";
        $d_kk = $m_conf->hydrateRaw( $sql );

        if ( $d_kk->count() > 0 ) {
            foreach ($d_kk->toArray() as $v_kk) {
                $this->rebuildJurnalKk( $v_kk['no_kk'] );
            }
        }

        return 1;
    }

    /**
     * Sama seperti postingUlangBank(), tapi untuk Kas Masuk & Kas Keluar (coa.kas = 1).
     */
    public function postingUlangKas( $start_date, $end_date ) {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select k.no_km from km k
            left join
                coa c
                on
                    c.coa = k.coa_bank
            where
                k.tgl_km between '".$start_date."' and '".$end_date."' and
                c.kas = 1
        ";
        $d_km = $m_conf->hydrateRaw( $sql );

        if ( $d_km->count() > 0 ) {
            foreach ($d_km->toArray() as $v_km) {
                $this->rebuildJurnalKm( $v_km['no_km'] );
            }
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select k.no_kk from kk k
            left join
                coa c
                on
                    c.coa = k.coa_bank
            where
                k.tgl_kk between '".$start_date."' and '".$end_date."' and
                c.kas = 1
        ";
        $d_kk = $m_conf->hydrateRaw( $sql );

        if ( $d_kk->count() > 0 ) {
            foreach ($d_kk->toArray() as $v_kk) {
                $this->rebuildJurnalKk( $v_kk['no_kk'] );
            }
        }

        return 1;
    }

    /**
     * Bangun ulang det_jurnal untuk 1 Bank Masuk/Kas Masuk (tabel km+kmitem) dari data yang
     * sudah tersimpan -- pola sama dengan BankMasuk::edit()/KasMasuk::edit(): N baris detail
     * (sisi asal) + 1 baris header (sisi bank/kas, nominal = total semua detail).
     */
    private function rebuildJurnalKm( $no_km ) {
        $m_km = new \Model\Storage\Km_model();
        $d_km = $m_km->where('no_km', $no_km)->first();

        if ( empty($d_km) ) {
            return;
        }

        $m_kmi = new \Model\Storage\KmItem_model();
        $d_kmi = $m_kmi->where('no_km', $no_km)->get();

        $m_djurnal = new \Model\Storage\DetJurnal_model();
        $m_djurnal->where('tbl_name', $m_km->getTable())->where('tbl_id', $no_km)->delete();

        $total_nilai = 0;
        foreach ($d_kmi as $v_det) {
            $id_djt = null;
            if ( !empty($v_det->det_jurnal_trans) ) {
                $m_djt = new \Model\Storage\DetJurnalTrans_model();
                $d_djt = $m_djt->where('kode', $v_det->det_jurnal_trans)->orderBy('id', 'desc')->first();
                $id_djt = !empty($d_djt) ? $d_djt->id : null;
            }

            $m_coa_asal = new \Model\Storage\Coa_model();
            $d_coa_asal = $m_coa_asal->where('coa', $v_det->coa_asal)->first();

            $m_dj = new \Model\Storage\DetJurnal_model();
            $m_dj->tanggal = $d_km->tgl_km;
            $m_dj->det_jurnal_trans_id = $id_djt;
            $m_dj->keterangan = $v_det->keterangan;
            $m_dj->nominal = $v_det->nilai;
            $m_dj->asal = !empty($d_coa_asal) ? $d_coa_asal->nama_coa : null;
            $m_dj->coa_asal = $v_det->coa_asal;
            $m_dj->tujuan = null;
            $m_dj->coa_tujuan = null;
            $m_dj->unit = $d_km->unit;
            $m_dj->tbl_name = $m_km->getTable();
            $m_dj->tbl_id = $no_km;
            $m_dj->invoice = $v_det->no_invoice;
            $m_dj->kode_trans = $no_km;
            $m_dj->kode_jurnal = $no_km;
            $m_dj->pelanggan = $d_km->no_pelanggan;
            $m_dj->save();

            $total_nilai += $v_det->nilai;
        }

        $m_coa_bank = new \Model\Storage\Coa_model();
        $d_coa_bank = $m_coa_bank->where('coa', $d_km->coa_bank)->first();

        $m_dj_header = new \Model\Storage\DetJurnal_model();
        $m_dj_header->tanggal = $d_km->tgl_km;
        $m_dj_header->det_jurnal_trans_id = null;
        $m_dj_header->keterangan = $d_km->keterangan;
        $m_dj_header->nominal = $total_nilai;
        $m_dj_header->asal = null;
        $m_dj_header->coa_asal = null;
        $m_dj_header->tujuan = !empty($d_coa_bank) ? $d_coa_bank->nama_coa : $d_km->nama_bank;
        $m_dj_header->coa_tujuan = $d_km->coa_bank;
        $m_dj_header->unit = $d_km->unit;
        $m_dj_header->tbl_name = $m_km->getTable();
        $m_dj_header->tbl_id = $no_km;
        $m_dj_header->invoice = null;
        $m_dj_header->kode_trans = $no_km;
        $m_dj_header->kode_jurnal = $no_km;
        $m_dj_header->pelanggan = $d_km->no_pelanggan;
        $m_dj_header->save();
    }

    /**
     * Sama seperti rebuildJurnalKm(), tapi untuk Bank Keluar/Kas Keluar (tabel kk+kkitem) --
     * arah kebalikannya: N baris detail (sisi tujuan) + 1 baris header (sisi bank/kas = asal).
     */
    private function rebuildJurnalKk( $no_kk ) {
        $m_kk = new \Model\Storage\Kk_model();
        $d_kk = $m_kk->where('no_kk', $no_kk)->first();

        if ( empty($d_kk) ) {
            return;
        }

        $m_kki = new \Model\Storage\KkItem_model();
        $d_kki = $m_kki->where('no_kk', $no_kk)->get();

        $m_djurnal = new \Model\Storage\DetJurnal_model();
        $m_djurnal->where('tbl_name', $m_kk->getTable())->where('tbl_id', $no_kk)->delete();

        $total_nilai = 0;
        foreach ($d_kki as $v_det) {
            $id_djt = null;
            if ( !empty($v_det->det_jurnal_trans) ) {
                $m_djt = new \Model\Storage\DetJurnalTrans_model();
                $d_djt = $m_djt->where('kode', $v_det->det_jurnal_trans)->orderBy('id', 'desc')->first();
                $id_djt = !empty($d_djt) ? $d_djt->id : null;
            }

            $m_coa_tujuan = new \Model\Storage\Coa_model();
            $d_coa_tujuan = $m_coa_tujuan->where('coa', $v_det->coa_tujuan)->first();

            $m_dj = new \Model\Storage\DetJurnal_model();
            $m_dj->tanggal = $d_kk->tgl_kk;
            $m_dj->det_jurnal_trans_id = $id_djt;
            $m_dj->supplier = $d_kk->no_supplier;
            $m_dj->keterangan = $v_det->keterangan;
            $m_dj->nominal = $v_det->nilai;
            $m_dj->asal = null;
            $m_dj->coa_asal = null;
            $m_dj->tujuan = !empty($d_coa_tujuan) ? $d_coa_tujuan->nama_coa : null;
            $m_dj->coa_tujuan = $v_det->coa_tujuan;
            $m_dj->unit = $d_kk->unit;
            $m_dj->tbl_name = $m_kk->getTable();
            $m_dj->tbl_id = $no_kk;
            $m_dj->invoice = $v_det->no_invoice;
            $m_dj->kode_trans = $no_kk;
            $m_dj->kode_jurnal = $no_kk;
            $m_dj->save();

            $total_nilai += $v_det->nilai;
        }

        $m_coa_bank = new \Model\Storage\Coa_model();
        $d_coa_bank = $m_coa_bank->where('coa', $d_kk->coa_bank)->first();

        $m_dj_header = new \Model\Storage\DetJurnal_model();
        $m_dj_header->tanggal = $d_kk->tgl_kk;
        $m_dj_header->det_jurnal_trans_id = null;
        $m_dj_header->supplier = $d_kk->no_supplier;
        $m_dj_header->keterangan = $d_kk->keterangan;
        $m_dj_header->nominal = $total_nilai;
        $m_dj_header->asal = !empty($d_coa_bank) ? $d_coa_bank->nama_coa : $d_kk->nama_bank;
        $m_dj_header->coa_asal = $d_kk->coa_bank;
        $m_dj_header->tujuan = null;
        $m_dj_header->coa_tujuan = null;
        $m_dj_header->unit = $d_kk->unit;
        $m_dj_header->tbl_name = $m_kk->getTable();
        $m_dj_header->tbl_id = $no_kk;
        $m_dj_header->invoice = null;
        $m_dj_header->kode_trans = $no_kk;
        $m_dj_header->kode_jurnal = $no_kk;
        $m_dj_header->save();
    }

    /**
     * Pembayaran Bakul pakai mesin setting_automatic_jurnal (base/InsertJurnal), jadi tinggal
     * jalankan ulang exec()-nya (action=2) per transaksi di periode terkait, plus bersihkan
     * jurnal punya transaksi yang sudah terhapus (action=3) -- karena Bakul::delete() saat ini
     * tidak memanggil InsertJurnal exec sama sekali.
     */
    public function postingUlangBakul( $start_date, $end_date ) {
        $cached_saj = $this->fetchCachedSaj('/pembayaran/Bakul');

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select pp.id from pembayaran_pelanggan pp
            where
                pp.tgl_bayar between '".$start_date."' and '".$end_date."'
                and pp.id = 37509
                and (pp.bad_debt is null or pp.bad_debt = 0)
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            foreach ($d_conf->toArray() as $v_conf) {
                Modules::run( 'base/InsertJurnal/exec', '/pembayaran/Bakul', $v_conf['id'], $v_conf['id'], 2, null, null, $cached_saj);
            }
        }

        /* PEMBAYARAN BAKUL YANG SUDAH TERHAPUS */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select dj.tbl_id from det_jurnal dj
            left join
                pembayaran_pelanggan pp
                on
                    cast(pp.id as varchar(50)) = dj.tbl_id
            where
                dj.tbl_name = 'pembayaran_pelanggan' and
                pp.id is null and
                dj.tanggal between '".$start_date."' and '".$end_date."'
            group by
                dj.tbl_id
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            foreach ($d_conf->toArray() as $v_conf) {
                Modules::run( 'base/InsertJurnal/exec', '/pembayaran/Bakul', $v_conf['tbl_id'], $v_conf['tbl_id'], 3, null, null, $cached_saj);
            }
        }
        /* END - PEMBAYARAN BAKUL YANG SUDAH TERHAPUS */

        return 1;
    }

    /**
     * Fetch setting_automatic_jurnal (_query, id, tbl_name) sekali saja, supaya InsertJurnal::exec()
     * tidak query ulang setting_automatic_jurnal+detail_fitur di setiap iterasi loop posting-ulang
     * (isinya sama persis utk semua transaksi dgn url yg sama dlm 1 run) -- ini yg bikin lambat
     * saat re-post ribuan transaksi (mis. Bakul), krn tiap iterasi jadi 1 round-trip DB extra.
     */
    private function fetchCachedSaj( $url ) {
        $m_saj = new \Model\Storage\Conf();
        $sql = "
            select
                saj.id,
                saj._query,
                saj.tbl_name
            from setting_automatic_jurnal saj
            left join
                detail_fitur df
                on
                    saj.det_fitur_id = df.id_detfitur
            where
                df.path_detfitur = '".substr($url, 1)."'
            order by
                saj.tgl_berlaku desc
        ";
        $d_saj = $m_saj->hydrateRaw( $sql );

        return $d_saj->count() > 0 ? $d_saj->toArray()[0] : null;
    }

    /**
     * Verifikasi Pembayaran (realisasi_pembayaran, piutang, bayar_peralatan) sama-sama pakai
     * mesin setting_automatic_jurnal (base/InsertJurnal) lewat url '/pembayaran/VerifikasiPembayaran',
     * cuma tbl_name-nya beda-beda -- jadi tinggal jalankan ulang exec()-nya (action=2) per transaksi
     * yang sudah realisasi di periode terkait, plus bersihkan jurnal punya transaksi yang sudah
     * terhapus (action=3).
     */
    public function postingUlangPembayaran( $start_date, $end_date ) {
        $tabel = array(
            'realisasi_pembayaran' => 'status',
            'piutang' => 'status',
            'bayar_peralatan' => 'mstatus',
        );

        $cached_saj = $this->fetchCachedSaj('/pembayaran/VerifikasiPembayaran');

        foreach ($tabel as $tbl_name => $kol_status) {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select id from ".$tbl_name."
                where
                    tgl_realisasi between '".$start_date."' and '".$end_date."' and
                    ".$kol_status." = 2
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                foreach ($d_conf->toArray() as $v_conf) {
                    Modules::run( 'base/InsertJurnal/exec', '/pembayaran/VerifikasiPembayaran', $v_conf['id'], $v_conf['id'], 2, $tbl_name, null, $cached_saj);
                }
            }

            /* YANG SUDAH TERHAPUS */
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select dj.tbl_id from det_jurnal dj
                left join
                    ".$tbl_name." t
                    on
                        cast(t.id as varchar(50)) = dj.tbl_id
                where
                    dj.tbl_name = '".$tbl_name."' and
                    t.id is null and
                    dj.tanggal between '".$start_date."' and '".$end_date."'
                group by
                    dj.tbl_id
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                foreach ($d_conf->toArray() as $v_conf) {
                    Modules::run( 'base/InsertJurnal/exec', '/pembayaran/VerifikasiPembayaran', $v_conf['tbl_id'], $v_conf['tbl_id'], 3, $tbl_name, null, $cached_saj);
                }
            }
            /* END - YANG SUDAH TERHAPUS */
        }

        return 1;
    }

    // public function postingUlangPembayaranPelanggan( $start_date, $end_date ) {
    //     /* PEMBAYARAN PELANGGAN BELUM BERJURNAL */
    //     $m_conf = new \Model\Storage\Conf();
    //     $sql = "
    //         select pp.id, pp.tgl_bayar from pembayaran_pelanggan pp
    //         left join
    //             (select distinct tbl_id from det_jurnal where tbl_name = 'pembayaran_pelanggan') dj
    //             on
    //                 cast(pp.id as varchar(max)) = dj.tbl_id
    //         where
    //             pp.tgl_bayar between '".$start_date."' and '".$end_date."' and
    //             dj.tbl_id is null
    //         order by
    //             pp.tgl_bayar asc
    //     ";
    //     $d_conf = $m_conf->hydrateRaw( $sql );

    //     if ( $d_conf->count() > 0 ) {
    //         $d_conf = $d_conf->toArray();

    //         foreach ($d_conf as $k_conf => $v_conf) {
    //             Modules::run( 'base/InsertJurnal/exec', '/pembayaran/Bakul', $v_conf['id'], $v_conf['id'], 2);
    //         }
    //     }
    //     /* END - PEMBAYARAN PELANGGAN BELUM BERJURNAL */

    //     /* PEMBAYARAN PELANGGAN DOBEL JURNAL */
    //     $m_conf = new \Model\Storage\Conf();
    //     $sql = "
    //         select dj.tbl_id from det_jurnal dj
    //         inner join
    //             pembayaran_pelanggan pp
    //             on
    //                 cast(pp.id as varchar(max)) = dj.tbl_id
    //         where
    //             dj.tbl_name = 'pembayaran_pelanggan' and
    //             pp.tgl_bayar between '".$start_date."' and '".$end_date."'
    //         group by
    //             dj.tbl_id
    //         having
    //             count(distinct dj.id_header) > 1
    //     ";
    //     $d_conf = $m_conf->hydrateRaw( $sql );

    //     if ( $d_conf->count() > 0 ) {
    //         $d_conf = $d_conf->toArray();

    //         foreach ($d_conf as $k_conf => $v_conf) {
    //             $this->wipeJurnalPembayaranPelanggan( $v_conf['tbl_id'] );
    //             Modules::run( 'base/InsertJurnal/exec', '/pembayaran/Bakul', $v_conf['tbl_id'], $v_conf['tbl_id'], 2);
    //         }
    //     }
    //     /* END - PEMBAYARAN PELANGGAN DOBEL JURNAL */

    //     /* PEMBAYARAN PELANGGAN YANG SUDAH TERHAPUS */
    //     $m_conf = new \Model\Storage\Conf();
    //     $sql = "
    //         select dj.tbl_id from det_jurnal dj
    //         left join
    //             pembayaran_pelanggan pp
    //             on
    //                 cast(pp.id as varchar(max)) = dj.tbl_id
    //         where
    //             dj.tbl_name = 'pembayaran_pelanggan' and
    //             pp.id is null and
    //             dj.tanggal between '".$start_date."' and '".$end_date."'
    //         group by
    //             dj.tbl_id
    //     ";
    //     $d_conf = $m_conf->hydrateRaw( $sql );

    //     if ( $d_conf->count() > 0 ) {
    //         $d_conf = $d_conf->toArray();

    //         foreach ($d_conf as $k_conf => $v_conf) {
    //             $this->wipeJurnalPembayaranPelanggan( $v_conf['tbl_id'] );
    //         }
    //     }
    //     /* END - PEMBAYARAN PELANGGAN YANG SUDAH TERHAPUS */

    //     return 1;
    // }

    // /**
    //  * Hapus TOTAL jurnal (semua header + det) untuk 1 tbl_id pembayaran_pelanggan.
    //  * InsertJurnal/exec hanya menghapus 1 header (top 1), jadi tidak bisa dipakai
    //  * untuk membereskan kasus dobel jurnal — helper ini menghapus semua header.
    //  */
    // private function wipeJurnalPembayaranPelanggan( $tbl_id ) {
    //     $DB = $this->load->database('default', TRUE);
    //     $sql = "
    //         DECLARE @ids TABLE (id int)
    //         INSERT INTO @ids (id) SELECT DISTINCT id_header FROM det_jurnal WHERE tbl_name = 'pembayaran_pelanggan' AND tbl_id = '".$tbl_id."'
    //         DELETE FROM det_jurnal WHERE tbl_name = 'pembayaran_pelanggan' AND tbl_id = '".$tbl_id."'
    //         DELETE FROM jurnal WHERE id IN (SELECT id FROM @ids)
    //     ";
    //     $DB->query($sql);
    // }

    public function tes() {
        Modules::run( 'base/InsertJurnal/exec', '/transaksi/PengirimanPenerimaanPakan', 51143, 51143, 2);
    }
}