<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PosisiStok extends Public_Controller {

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
                'assets/select2/js/select2.min.js',
                "assets/report/posisi_stok/js/posisi-stok.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/posisi_stok/css/posisi-stok.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['gudang'] = $this->getGudang();
            $content['barang'] = $this->getBarang();
            $content['title_menu'] = 'Laporan Posisi Stok';

            // Load Indexx
            $data['view'] = $this->load->view('report/posisi_stok/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getGudang() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                gdg1.* 
            from gudang gdg1
            order by
                gdg1.jenis asc,
                gdg1.nama asc
        ";
        $d_gdg = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_gdg->count() > 0 ) {
            $data = $d_gdg->toArray();
        }

        return $data;
    }

    public function getBarang() {
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
                brg1.tipe in ('pakan', 'obat')
            order by
                brg1.tipe asc,
                brg1.nama asc
        ";
        $d_brg = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_brg->count() > 0 ) {
            $data = $d_brg->toArray();
        }

        return $data;
    }

    public function mappingDataReport($_kode_brg, $_kode_gudang, $_jenis, $_date)
    {
        $sql_jenis = null;
        $sql_jenis_trans_masuk = null;
        $sql_jenis_trans_keluar = null;
        $jenis = null;
        if ( !empty($_jenis) ) {
            $jenis = $_jenis;
            if ( stristr($jenis, 'obat') !== false ) {
                $jenis = 'voadip';

                $sql_jenis_trans_masuk = "
                    select kv.tgl_kirim as tanggal, kv.no_order as kode_trans, kv.no_order, 'ORDER' as jenis_trans from kirim_voadip kv where kv.tgl_kirim <= '".$_date."'

                    union all

                    select rv.tgl_retur as tanggal, rv.no_retur as kode_trans, rv.no_order, 'RETUR DARI PLASMA' as jenis_trans from retur_voadip rv where rv.tgl_retur <= '".$_date."'

                    union all

                    select av.tanggal, av.kode as kode_trans, av.kode as no_order, 'ADJUSTMENT IN' as jenis_trans from adjin_voadip av where av.tanggal <= '".$_date."'
                ";

                $sql_jenis_trans_keluar = "
                    select kv.tgl_kirim as tanggal, kv.no_order as kode_trans, kv.no_order, 'DISTRIBUSI' as jenis_trans from kirim_voadip kv where kv.tgl_kirim <= '".$_date."'

                    union all

                    select rv.tgl_retur as tanggal, rv.no_retur as kode_trans, rv.no_order, 'RETUR DARI GUDANG' as jenis_trans from retur_voadip rv where rv.tgl_retur <= '".$_date."'

                    union all

                    select av.tanggal, av.kode as kode_trans, av.kode as no_order, 'ADJUSTMENT OUT' as jenis_trans from adjout_voadip av where av.tanggal <= '".$_date."'
                ";
            } else {
                $sql_jenis_trans_masuk = "
                    select kp.tgl_kirim as tanggal, kp.no_order as kode_trans, kp.no_order, 'ORDER' as jenis_trans from kirim_pakan kp where kp.tgl_kirim <= '".$_date."'

                    union all

                    select rp.tgl_retur as tanggal, rp.no_retur as kode_trans, rp.no_order, 'RETUR DARI PLASMA' as jenis_trans from retur_pakan rp where rp.tgl_retur <= '".$_date."'

                    union all

                    select ap.tanggal, ap.kode as kode_trans, ap.kode as no_order, 'ADJUSTMENT IN' as jenis_trans from adjin_pakan ap where ap.tanggal <= '".$_date."'
                ";

                $sql_jenis_trans_keluar = "
                    select kp.tgl_kirim as tanggal, kp.no_order as kode_trans, kp.no_order, 'DISTRIBUSI' as jenis_trans from kirim_pakan kp where kp.tgl_kirim <= '".$_date."'

                    union all

                    select rp.tgl_retur as tanggal, rp.no_retur as kode_trans, rp.no_order, 'RETUR DARI GUDANG' as jenis_trans from retur_pakan rp where rp.tgl_retur <= '".$_date."'

                    union all

                    select ap.tanggal, ap.kode as kode_trans, ap.kode as no_order, 'ADJUSTMENT OUT' as jenis_trans from adjout_pakan ap where ap.tanggal <= '".$_date."'
                ";
            }

            $sql_jenis = "and ds.jenis_barang = '".$jenis."'";
        }

        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                -- data.*,
                data.tanggal,
                data.kode_gudang,
                data.kode_barang,
                data.jenis_barang,
                data.hrg_beli,
                data.kode_trans,
                data.jenis_trans,
                sum(data.jml_saldo_awal) as jml_saldo_awal,
                sum(data.saldo_awal) as saldo_awal,
                sum(data.jml_debet) as jml_debet,
                sum(data.debet) as debet,
                sum(data.jml_kredit) as jml_kredit,
                sum(data.kredit) as kredit,
                (isnull(sum(data.jml_saldo_awal), 0) + isnull(sum(data.jml_debet), 0)) - isnull(sum(data.jml_kredit), 0) as jml_saldo_akhir,
                (isnull(sum(data.saldo_awal), 0) + isnull(sum(data.debet), 0)) - isnull(sum(data.kredit), 0) as saldo_akhir,
                gdg.nama as nama_gudang,
                brg.nama as nama_barang
            from
            (
                /* SALDO AWAL */
                select
                    sa.tanggal as tanggal,
                    sa.kode_gudang,
                    sa.kode_barang,
                    sa.jenis_barang,
                    sa.hrg_beli,
                    sum(sa.jumlah) as jml_saldo_awal,
                    sum(sa.jumlah * sa.hrg_beli) as saldo_awal,
                    0 as jml_debet,
                    0 as debet,
                    0 as jml_kredit,
                    0 as kredit,
                    sa.kode_trans,
                    -- 'Saldo Awal' as kode_trans,
                    jt.jenis_trans as jenis_trans
                    -- 1 as urut
                from
                (
                    select
                        ds.tgl_trans as tanggal,
                        ds.kode_gudang,
                        ds.kode_barang,
                        ds.jenis_barang,
                        ds.hrg_beli,
                        ds.kode_trans,
                        sum(isnull(ds.jml_stok, 0) + isnull(dst.jumlah, 0)) as jumlah
                    from det_stok ds
                    left join
                        (select id_header, sum(jumlah) as jumlah from det_stok_trans group by id_header) dst
                        on
                            ds.id = dst.id_header
                    left join
                        stok s
                        on
                            ds.id_header = s.id
                    where
                        s.periode = '".$_date."' and
                        ds.tgl_trans < '".$_date."' and
                        (ds.kode_gudang = '".$_kode_gudang."' or '".$_kode_gudang."' = 'all') and
                        (ds.kode_barang = '".$_kode_brg."' or '".$_kode_brg."' = 'all')
                    group by
                        ds.tgl_trans,
                        ds.kode_gudang,
                        ds.kode_barang,
                        ds.jenis_barang,
                        ds.hrg_beli,
                        ds.kode_trans
                ) sa
                left join
                    (
                        ".$sql_jenis_trans_masuk."
                    ) jt
                    on
                        sa.kode_trans = jt.no_order and
                        sa.tanggal = jt.tanggal
                group by
                    sa.tanggal,
                    sa.kode_gudang,
                    sa.kode_barang,
                    sa.jenis_barang,
                    sa.hrg_beli,
                    sa.kode_trans,
                    jt.jenis_trans
                /* END - SALDO AWAL */

                union all

                /* MASUK */
                select
                    msk.tanggal,
                    msk.kode_gudang,
                    msk.kode_barang,
                    msk.jenis_barang,
                    msk.hrg_beli,
                    0 as jml_saldo_awal,
                    0 as saldo_awal,
                    sum(msk.jumlah) as jml_debet,
                    sum((msk.jumlah * msk.hrg_beli)) as debet,
                    0 as jml_kredit,
                    0 as kredit,
                    msk.kode_trans as kode_trans,
                    -- msk.jenis_trans as jenis_trans,
                    jt.jenis_trans
                    -- 2 as urut
                from
                (
                    select
                        ds.tgl_trans as tanggal,
                        ds.kode_gudang,
                        ds.kode_barang,
                        ds.jenis_barang,
                        ds.kode_trans,
                        ds.jenis_trans,
                        ds.hrg_beli,
                        sum(isnull(ds.jumlah, 0)) as jumlah
            --            sum(isnull(ds.jumlah, 0) + isnull(dst.jumlah, 0)) as jumlah
                    from
                    (
                        select ds1.* from det_stok ds1
                        right join
                            (
                                select min(ds.id) as id, ds.tgl_trans, ds.kode_gudang, ds.kode_barang, ds.kode_trans, ds.jenis_barang, ds.jenis_trans, ds.hrg_beli from det_stok ds
                                left join
                                    stok s
                                    on
                                        ds.id_header = s.id
                                where
                                    s.periode = '".$_date."' and
                                    ds.tgl_trans >= '".$_date."'
                                    -- ".$sql_jenis."
                                group by
                                    ds.tgl_trans, ds.kode_gudang, ds.kode_barang, ds.kode_trans, ds.jenis_barang, ds.jenis_trans, ds.hrg_beli
                            ) ds2
                            on
                                ds1.id = ds2.id
                    ) ds
                    left join
                        stok s
                        on
                            ds.id_header = s.id
                    where
                        s.periode = '".$_date."' and
                        ds.tgl_trans = '".$_date."' and
                        (ds.kode_gudang = '".$_kode_gudang."' or '".$_kode_gudang."' = 'all') and
                        (ds.kode_barang = '".$_kode_brg."' or '".$_kode_brg."' = 'all')
                    group by
                        ds.tgl_trans,
                        ds.kode_gudang,
                        ds.kode_barang,
                        ds.jenis_barang,
                        ds.kode_trans,
                        ds.jenis_trans,
                        ds.hrg_beli
                ) msk
                left join
                    (
                        ".$sql_jenis_trans_masuk."
                    ) jt
                    on
                        msk.kode_trans = jt.no_order and
                        msk.tanggal = jt.tanggal
                group by
                    msk.tanggal,
                    msk.kode_gudang,
                    msk.kode_barang,
                    msk.jenis_barang,
                    msk.hrg_beli,
                    msk.kode_trans,
                    jt.jenis_trans
                /* END - MASUK */

                union all

                /* KELUAR */
                select
                    klwr.tanggal,
                    klwr.kode_gudang,
                    klwr.kode_barang,
                    klwr.jenis_barang,
                    klwr.hrg_beli,
                    0 as jml_saldo_awal,
                    0 as saldo_awal,
                    0 as jml_debet,
                    0 as debet,
                    sum(klwr.jumlah) as jml_kredit,
                    sum(klwr.jumlah * klwr.hrg_beli) as kredit,
                    klwr.kode_trans as kode_trans,
                    -- klwr.jenis_trans as jenis_trans,
                    jt.jenis_trans
                    -- 3 as urut
                from
                (
                    select
                        ds.tgl_trans as tanggal,
                        ds.kode_gudang,
                        ds.kode_barang,
                        ds.jenis_barang,
                        ds.kode_trans,
                        ds.jenis_trans,
                        ds.hrg_beli,
                        sum(isnull(dst.jumlah, 0)) as jumlah
                    from det_stok_trans dst
                    left join
                        det_stok ds
                        on
                            ds.id = dst.id_header
                    left join
                        stok s
                        on
                            ds.id_header = s.id
                    where
                        s.periode = '".$_date."' and
                        (ds.kode_gudang = '".$_kode_gudang."' or '".$_kode_gudang."' = 'all') and
                        (dst.kode_barang = '".$_kode_brg."' or '".$_kode_brg."' = 'all')
                    group by
                        ds.tgl_trans,
                        ds.kode_gudang,
                        ds.kode_barang,
                        ds.jenis_barang,
                        ds.kode_trans,
                        ds.jenis_trans,
                        ds.hrg_beli
                ) klwr
                left join
                    (
                        ".$sql_jenis_trans_masuk."
                    ) jt
                    on
                        klwr.kode_trans = jt.no_order and
                        klwr.tanggal = jt.tanggal
                group by
                    klwr.tanggal,
                    klwr.kode_gudang,
                    klwr.kode_barang,
                    klwr.jenis_barang,
                    klwr.hrg_beli,
                    klwr.kode_trans,
                    jt.jenis_trans
                /* END - KELUAR */
            ) data
            left join
                (
                    select * from gudang
                ) gdg
                on
                    data.kode_gudang = gdg.id
            left join
                (
                    select brg1.* from barang brg1
                    right join
                        (
                            select max(id) as id, kode from barang group by kode
                        ) brg2
                        on
                            brg1.id = brg2.id
                ) brg
                on
                    data.kode_barang = brg.kode
            where
                data.jenis_barang like '%".$jenis."%'
            group by
                data.tanggal,
                data.kode_gudang,
                data.kode_barang,
                data.jenis_barang,
                data.hrg_beli,
                data.kode_trans,
                data.jenis_trans,
                gdg.nama,
                brg.nama
            order by
                data.kode_gudang asc,
                brg.nama asc
                -- ,
                -- data.tanggal asc,
                -- data.urut asc
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getData()
    {
        $params = $this->input->get('params');

        $date = $params['tanggal'];
        $kode_gudang = $params['gudang'];
        $kode_barang = $params['barang'];
        $jenis = $params['jenis'];

        $data = $this->mappingDataReport($kode_barang, $kode_gudang, $jenis, $date);

        $content['data'] = $data;
        $html = $this->load->view('report/posisi_stok/list', $content, TRUE);

        echo $html;
    }
}