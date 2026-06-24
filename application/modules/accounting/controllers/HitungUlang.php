<?php defined('BASEPATH') OR exit('No direct script access allowed');

class HitungUlang extends Public_Controller {

    private $pathView = 'accounting/hitung_ulang/';
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
                "assets/accounting/hitung_ulang/js/hitung-ulang.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/accounting/hitung_ulang/css/hitung-ulang.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->hakAkses;
            $content['title_panel'] = 'Hitung Ulang';

            // Load Indexx
            $data['title_menu'] = 'Hitung Ulang';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);
            $this->load->view($this->template, $data);
        // } else {
        //     showErrorAkses();
        // }
    }

    public function hitungUlang() {
        $params = $this->input->post('params');

        try {
            $bulan = $params['bulan'];
            $tahun = substr($params['tahun'], 0, 4);
            $jenis = $params['jenis'];
            
            $angka_bulan = (strlen($bulan) == 1) ? '0'.$bulan : $bulan;
            
            $date = $tahun.'-'.$angka_bulan.'-01';
            $start_date = date("Y-m-d", strtotime($date));
            $end_date = date("Y-m-t", strtotime($date));

            if ( $jenis == 'doc' ) {
                $status = $this->hitungUlangDoc($start_date, $end_date);
            }

            if ( $jenis == 'pakan' ) {
                $status = $this->hitungUlangPakan($start_date, $end_date);
            }

            if ( $jenis == 'voadip' ) {
                $status = $this->hitungUlangVoadip($start_date, $end_date);
            }

            $this->result['status'] = $status;
            $this->result['message'] = 'Data berhasil di hitung ulang, cek laporan yang berhubung.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function hitungUlangDoc( $start_date, $end_date ) {
        /* TERIMA DOC DI KANDANG */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                td.*,
                cast(td.datang as date) as tanggal,
                td.no_order as kode_trans,
                od.noreg,
                m.nama as nama_mitra,
                td.jml_ekor as jml_terima,
                dss.jumlah as jml_stok,
                isnull(td.jml_ekor, 0) - isnull(dss.jumlah, 0) as selisih,
                td.total as total_stok,
                dj.nominal as nominal_jurnal,
                isnull(dj.nominal, 0) - isnull(td.total, 0) as selisih_jurnal
            from terima_doc td
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
            left join
                rdim_submit rs
                on
                    od.noreg = rs.noreg
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
                (select kode_trans, sum(jumlah) as jumlah from det_stok_siklus where jenis_barang = 'doc' group by kode_trans) dss
                on
                    dss.kode_trans = td.no_order
            left join
                (select tbl_id, tbl_name, sum(nominal) as nominal from det_jurnal dj where coa_asal = '21180.200' and tbl_name = 'terima_doc' group by tbl_id, tbl_name) dj
                on
                    cast(td.id as varchar(max)) = dj.tbl_id
            where
                cast(td.datang as date) between '".$start_date."' and '".$end_date."' and
                (isnull(dss.jumlah, 0) - isnull(td.jml_ekor, 0) <> 0 or isnull(dj.nominal, 0) - isnull(td.total, 0) <> 0)
            order by
                cast(td.datang as date) asc,
                m.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {
                $conf = new \Model\Storage\Conf();
                $sql = "EXEC hitung_stok_siklus 'doc', 'terima_doc', '".$v_conf['id']."', '".substr($v_conf['datang'], 0, 10)."', 2, null, null";
                $d_conf = $conf->hydrateRaw($sql);
            }
        }
        /* END - TERIMA DOC DI KANDANG */

        return 1;
    }

    public function hitungUlangPakan( $start_date, $end_date ) {
        /* TERIMA PAKAN DI KANDANG */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                dtp.tbl_id,
                dtp.tgl_trans as tanggal,
                dtp.kode_trans,
                sum(dss.jumlah) as jml_stok,
                dtp.jml_terima,
                dtp.jenis_kirim
            from 
            (
                    select tp.id as tbl_id, sum(dtp.jumlah) as jml_terima, kp.no_order as kode_trans, kp.jenis_kirim, tp.tgl_terima as tgl_trans from det_terima_pakan dtp 
                    left join
                        terima_pakan tp 
                        on
                            dtp.id_header = tp.id
                    left join
                        kirim_pakan kp 
                        on
                            tp.id_kirim_pakan = kp.id
                    where
                        kp.jenis_kirim <> 'opks'
                    group by
                        tp.id, kp.no_order, kp.jenis_kirim, tp.tgl_terima
                ) dtp
            left join
                (
                    select tgl_trans, kode_trans, jumlah from det_stok_siklus where jenis_barang = 'pakan'
                ) dss
                on
                    dss.kode_trans = dtp.kode_trans
            where 
                dtp.tgl_trans between '".$start_date."' and '".$end_date."'
            group by
                dtp.tbl_id,
                dtp.tgl_trans,
                dtp.kode_trans,
                dtp.jml_terima,
                dtp.jenis_kirim
            having
                isnull(sum(dss.jumlah), 0) - isnull(dtp.jml_terima, 0) <> 0
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {
                $conf = new \Model\Storage\Conf();
                $sql = "EXEC hitung_stok_siklus 'pakan', 'terima_pakan', '".$v_conf['tbl_id']."', '".$v_conf['tanggal']."', 2, null, null";
                $d_conf = $conf->hydrateRaw($sql);
            }
        }
        /* END - TERIMA PAKAN DI KANDANG */

        /* LHK */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                l.tanggal as tanggal,
                l.noreg,
                l.id as kode_trans,
                isnull(sum(dsts.jumlah), 0) as jml_stok,
                (l.selisih * 50) as jml_lhk,
                isnull(sum(dsts.jumlah), 0) - (l.selisih * 50) as selisih,
                dj.nominal
            from 
            (
                    select l.noreg, l.tanggal, isnull(max(l_prev.umur), 0) as umur_prev, l.id, l.umur, l.pakai_pakan, isnull(max(l_prev.pakai_pakan), 0) as pp_prev, l.pakai_pakan-isnull(max(l_prev.pakai_pakan), 0) as selisih from lhk l
                    left join
                        (select * from lhk) l_prev
                        on
                            l_prev.noreg = l.noreg and
                            l_prev.umur < l.umur
                    group by
                        l.noreg, l.tanggal, l.id, l.umur, l.pakai_pakan
                ) l
            left join
                (
                    select
                        dsts.kode_trans,
                        sum(dsts.jumlah) as jumlah
                    from det_stok_trans_siklus dsts
                    left join
                        det_stok_siklus dss
                        on
                            dss.id = dsts.id_header
                    where
                        dsts.tbl_name = 'lhk' and
                        dss.jenis_barang = 'pakan'
                    group by
                        dsts.kode_trans
                ) dsts 
                on
                    dsts.kode_trans = l.id
            left join
                (select * from det_jurnal where tbl_name = 'lhk' and coa_tujuan = '71101.000') dj
                on
                    dj.tbl_id = l.id
            where
                l.tanggal between '".$start_date."' and '".$end_date."'
            group by
                l.tanggal,
                l.noreg,
                l.id,
                l.selisih,
                dj.nominal
            having
                isnull(sum(dsts.jumlah), 0) - (l.selisih * 50) <> 0
            order by
                l.tanggal asc,
                l.noreg asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {
                $conf = new \Model\Storage\Conf();
                $sql = "EXEC hitung_stok_siklus 'pakan', 'lhk', '".$v_conf['kode_trans']."', '".$v_conf['tanggal']."', 2, null, null";
                $d_conf = $conf->hydrateRaw($sql);
            }
        }
        /* END - LHK */

        return 1;
    }

    public function hitungUlangVoadip( $start_date, $end_date ) {
        /* RETUR VOADIP */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                retur.tbl_id,
                retur.tanggal as tanggal,
                retur.noreg,
                retur.kode_trans,
                sum(dsts.jumlah) as jml_stok,
                sum(retur.jumlah) as jml_retur,
                sum(dsts.jumlah) - sum(retur.jumlah) as selisih
            from 
            (			
                    select rv.id as tbl_id, rv.no_retur as kode_trans, rv.id_asal as noreg, rv.tgl_retur as tanggal, sum(drv.jumlah) as jumlah from det_retur_voadip drv 
                    left join
                        retur_voadip rv 
                        on
                            drv.id_header = rv.id
                    group by
                        rv.id, rv.no_retur, rv.id_asal, rv.tgl_retur
                ) retur
            left join
                (
                    select
                        dsts.kode_trans,
                        sum(dsts.jumlah) as jumlah
                    from det_stok_trans_siklus dsts
                    left join
                        det_stok_siklus dss
                        on
                            dss.id = dsts.id_header
                    where
                        dsts.tbl_name = 'retur_voadip' and
                        dss.jenis_barang = 'voadip'
                    group by
                        dsts.kode_trans
                ) dsts 
                on
                    dsts.kode_trans = retur.kode_trans
            where
                retur.tanggal between '".$start_date."' and '".$end_date."'
            group by
                retur.tbl_id,
                retur.tanggal,
                retur.noreg,
                retur.kode_trans
            having
                isnull(sum(dsts.jumlah), 0) - isnull(sum(retur.jumlah), 0) <> 0
            order by
                retur.tanggal asc,
                retur.noreg asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {
                $conf = new \Model\Storage\Conf();
                $sql = "EXEC hitung_stok_siklus 'voadip', 'retur_voadip', '".$v_conf['tbl_id']."', '".$v_conf['tanggal']."', 2, null, null";
                $d_conf = $conf->hydrateRaw($sql);
            }
        }
        /* END - RETUR VOADIP */

        /* TERIMA DI KANDANG */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                dtv.tbl_id,
                dtv.tgl_trans as tanggal,
                dtv.kode_trans,
                sum(dss.jumlah) as jml_stok,
                dtv.jml_terima,
                dtv.jenis_kirim
            from 
            (
                    select tv.id as tbl_id, sum(dtv.jumlah) as jml_terima, kv.no_order as kode_trans, kv.jenis_kirim, tv.tgl_terima as tgl_trans from det_terima_voadip dtv 
                    left join
                        terima_voadip tv 
                        on
                            dtv.id_header = tv.id
                    left join
                        kirim_voadip kv
                        on
                            tv.id_kirim_voadip = kv.id
                    where
                        kv.jenis_kirim <> 'opks'
                    group by
                        tv.id, kv.no_order, kv.jenis_kirim, tv.tgl_terima
                ) dtv
            left join
                (
                    select tgl_trans, kode_trans, jumlah from det_stok_siklus where jenis_barang = 'voadip'
                ) dss
                on
                    dss.kode_trans = dtv.kode_trans
            where 
                dtv.tgl_trans between '".$start_date."' and '".$end_date."'
            group by
                dtv.tbl_id,
                dtv.tgl_trans,
                dtv.kode_trans,
                dtv.jml_terima,
                dtv.jenis_kirim
            having
                isnull(sum(dss.jumlah), 0) - isnull(dtv.jml_terima, 0) <> 0
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {
                $conf = new \Model\Storage\Conf();
                $sql = "EXEC hitung_stok_voadip_by_transaksi 'terima_voadip', '".$v_conf['tbl_id']."', '".$v_conf['tanggal']."', 0, 0";
                $d_conf = $conf->hydrateRaw($sql);

                $conf = new \Model\Storage\Conf();
                $sql = "EXEC hitung_stok_siklus 'voadip', 'terima_voadip', '".$v_conf['tbl_id']."', '".$v_conf['tanggal']."', 2, null, null";
                $d_conf = $conf->hydrateRaw($sql);
            }
        }
        /* END - TERIMA DI KANDANG */

        /* NOMINAL */
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                dtv.tbl_id,
                dtv.tanggal,
                dtv.kode_trans,
                dss.nominal as nominal_stok,
                dtv.nominal_terima,
                dtv.jenis_kirim
            from 
            (
                    select data.*, tv.tgl_terima as tanggal, tv.id as tbl_id, kv.jenis_kirim from (
                        select dst.kode_trans, sum(dst.jumlah*ds.hrg_beli) as nominal_terima from det_stok_trans dst
                        left join
                            det_stok ds
                            on
                                dst.id_header = ds.id
                        where
                            ds.jenis_barang = 'voadip'
                        group by
                            dst.kode_trans 
                    ) data
                    left join
                        kirim_voadip kv
                        on
                            data.kode_trans = kv.no_order
                    left join
                        terima_voadip tv
                        on
                            kv.id = tv.id_kirim_voadip
                ) dtv
            left join
                (
                    select dss.kode_trans, sum(dss.jumlah * dss.hrg_beli) as nominal from det_stok_siklus dss
                    where 
                        dss.jenis_barang = 'voadip' 
                    group by 
                        dss.kode_trans
                ) dss
                on
                    dss.kode_trans = dtv.kode_trans
            where
                dtv.tanggal between '".$start_date."' and '".$end_date."' and
                isnull(dss.nominal, 0) - isnull(dtv.nominal_terima, 0) <> 0
            order by
                dtv.tanggal asc,
                dtv.kode_trans asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $k_conf => $v_conf) {
                $conf = new \Model\Storage\Conf();
                $sql = "EXEC hitung_stok_siklus 'voadip', 'terima_voadip', '".$v_conf['tbl_id']."', '".$v_conf['tanggal']."', 2, null, null";
                $d_conf = $conf->hydrateRaw($sql);
            }
        }
        /* END - NOMINAL */

        return 1;
    }

    public function tes() {
    }
}