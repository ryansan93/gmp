<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KartuStok extends Public_Controller {

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
                "assets/report/kartu_stok/js/kartu-stok.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/kartu_stok/css/kartu-stok.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['gudang'] = $this->getGudang();
            $content['barang'] = $this->getBarang();
            $content['title_menu'] = 'Laporan Kartu Stok';

            // Load Indexx
            $data['view'] = $this->load->view('report/kartu_stok/index', $content, TRUE);
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

    public function mappingDataReport($_kode_brg, $_kode_gudang, $_jenis, $_start_date, $_end_date)
    {
        // cetak_r( $_jenis );

        $sql_jenis = null;
        $sql_jenis_trans_masuk = null;
        $sql_jenis_trans_keluar = null;
        if ( !empty($_jenis) ) {
            $jenis = $_jenis;
            if ( stristr($jenis, 'obat') !== false ) {
                $jenis = 'voadip';

                $sql_jenis_trans_masuk = "
                    select tv.tgl_terima as tanggal, kv.no_order as kode_trans, kv.no_order, 'ORDER' as jenis_trans from kirim_voadip kv left join terima_voadip tv on tv.id_kirim_voadip = kv.id -- where kv.tgl_kirim between '".$_start_date."' and '".$_end_date."'

                    union all

                    select rv.tgl_retur as tanggal, rv.no_retur as kode_trans, rv.no_order, 'RETUR DARI PLASMA' as jenis_trans from retur_voadip rv where rv.jenis_retur = 'opkp' -- where rv.tgl_retur between '".$_start_date."' and '".$_end_date."'

                    union all

                    select av.tanggal, av.kode as kode_trans, av.kode as no_order, 'ADJUSTMENT IN' as jenis_trans from adjin_voadip av -- where av.tanggal between '".$_start_date."' and '".$_end_date."'
                ";

                // Sumber jumlah keluar diambil langsung dari dokumen fisik (bukan det_stok_trans),
                // karena det_stok_trans berhenti dicatat begitu jml_stok layer mentok 0 -- order yang
                // melebihi stok yang tersedia jadi tidak pernah tercatat/terpotong di sana (lihat kasus
                // oversell). Ini membuat saldo bisa benar-benar minus sesuai kondisi fisik.
                // try_cast (bukan cast biasa) supaya kode gudang legacy yang tidak numerik/kepanjangan
                // (mis. id_asal retur yang ternyata noreg plasma 11 digit, overflow tipe int) tidak
                // membuat query error -- cukup jadi NULL dan otomatis tidak match gudang manapun.
                $sql_jenis_trans_keluar = "
                    select tv.tgl_terima as tanggal, try_cast(kv.asal as int) as kode_gudang, dkv.item as kode_barang, 'voadip' as jenis_barang, sum(dkv.jumlah) as jumlah, kv.no_order as kode_trans, 'DISTRIBUSI' as jenis_trans from kirim_voadip kv left join terima_voadip tv on tv.id_kirim_voadip = kv.id join det_kirim_voadip dkv on dkv.id_header = kv.id group by tv.tgl_terima, kv.asal, dkv.item, kv.no_order

                    union all

                    select rv.tgl_retur as tanggal, try_cast(rv.id_asal as int) as kode_gudang, drv.item as kode_barang, 'voadip' as jenis_barang, sum(drv.jumlah) as jumlah, rv.no_retur as kode_trans, 'RETUR DARI GUDANG' as jenis_trans from retur_voadip rv join det_retur_voadip drv on drv.id_header = rv.id where rv.jenis_retur = 'opkg' group by rv.tgl_retur, rv.id_asal, drv.item, rv.no_retur

                    union all

                    select av.tanggal, av.kode_gudang, av.kode_barang, 'voadip' as jenis_barang, av.jumlah, av.kode as kode_trans, 'ADJUSTMENT OUT' as jenis_trans from adjout_voadip av
                ";
            } else {
                $sql_jenis_trans_masuk = "
                    select tp.tgl_terima as tanggal, kp.no_order as kode_trans, kp.no_order, 'ORDER' as jenis_trans from kirim_pakan kp left join terima_pakan tp on tp.id_kirim_pakan = kp.id -- where kp.tgl_kirim between '".$_start_date."' and '".$_end_date."'

                    union all

                    select rp.tgl_retur as tanggal, rp.no_retur as kode_trans, rp.no_order, 'RETUR DARI PLASMA' as jenis_trans from retur_pakan rp where rp.jenis_retur = 'opkp' -- where rp.tgl_retur between '".$_start_date."' and '".$_end_date."'

                    union all

                    select ap.tanggal, ap.kode as kode_trans, ap.kode as no_order, 'ADJUSTMENT IN' as jenis_trans from adjin_pakan ap -- where ap.tanggal between '".$_start_date."' and '".$_end_date."'
                ";

                // Sumber jumlah keluar diambil langsung dari dokumen fisik (bukan det_stok_trans),
                // karena det_stok_trans berhenti dicatat begitu jml_stok layer mentok 0 -- order yang
                // melebihi stok yang tersedia jadi tidak pernah tercatat/terpotong di sana (lihat kasus
                // oversell). Ini membuat saldo bisa benar-benar minus sesuai kondisi fisik.
                // try_cast (bukan cast biasa) supaya kode gudang legacy yang tidak numerik/kepanjangan
                // (mis. id_asal retur yang ternyata noreg plasma 11 digit, overflow tipe int) tidak
                // membuat query error -- cukup jadi NULL dan otomatis tidak match gudang manapun.
                $sql_jenis_trans_keluar = "
                    select tp.tgl_terima as tanggal, try_cast(kp.asal as int) as kode_gudang, dkp.item as kode_barang, 'pakan' as jenis_barang, sum(dkp.jumlah) as jumlah, kp.no_order as kode_trans, 'DISTRIBUSI' as jenis_trans from kirim_pakan kp left join terima_pakan tp on tp.id_kirim_pakan = kp.id join det_kirim_pakan dkp on dkp.id_header = kp.id group by tp.tgl_terima, kp.asal, dkp.item, kp.no_order

                    union all

                    select rp.tgl_retur as tanggal, try_cast(rp.id_asal as int) as kode_gudang, drp.item as kode_barang, 'pakan' as jenis_barang, sum(drp.jumlah) as jumlah, rp.no_retur as kode_trans, 'RETUR DARI GUDANG' as jenis_trans from retur_pakan rp join det_retur_pakan drp on drp.id_header = rp.id where rp.jenis_retur = 'opkg' group by rp.tgl_retur, rp.id_asal, drp.item, rp.no_retur

                    union all

                    select ap.tanggal, ap.kode_gudang, ap.kode_barang, 'pakan' as jenis_barang, ap.jumlah, ap.kode as kode_trans, 'ADJUSTMENT OUT' as jenis_trans from adjout_pakan ap
                ";
            }

            $sql_jenis = "and ds.jenis_barang = '".$jenis."'";
        }

        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.*,
                gdg.nama as nama_gudang,
                brg.nama as nama_barang
            from
            (
                /* SALDO AWAL */
                select
                    '".$_start_date."' as tanggal,
                    sa.kode_gudang,
                    sa.kode_barang,
                    sa.jenis_barang,
                    sum(sa.jumlah * sa.hrg_beli) / sum(sa.jumlah) as hrg_beli,
                    sum(sa.jumlah) as jml_debet,
                    sum(sa.jumlah * sa.hrg_beli) as debet,
                    0 as jml_kredit,
                    0 as kredit,
                    'Saldo Awal' as kode_trans,
                    null as jenis_trans,
                    1 as urut
                from
                (
                    select
                        ds.kode_gudang,
                        ds.kode_barang,
                        ds.jenis_barang,
                        ds.hrg_beli,
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
                        s.periode = '".$_start_date."' and
                        ds.tgl_trans < '".$_start_date."' and
                        ds.kode_gudang = '".$_kode_gudang."' and
                        (ds.kode_barang = '".$_kode_brg."' or '".$_kode_brg."' = 'all')
                    group by
                        ds.kode_gudang,
                        ds.kode_barang,
                        ds.jenis_barang,
                        ds.hrg_beli
                ) sa
                group by
                    sa.kode_gudang,
                    sa.kode_barang,
                    sa.jenis_barang,
                    sa.hrg_beli
                /* END - SALDO AWAL */

                union all

                /* MASUK */
                select
                    msk.tanggal,
                    msk.kode_gudang,
                    msk.kode_barang,
                    msk.jenis_barang,
                    msk.hrg_beli,
                    msk.jumlah as jml_debet,
                    (msk.jumlah * msk.hrg_beli) as debet,
                    0 as jml_kredit,
                    0 as kredit,
                    msk.kode_trans as kode_trans,
                    -- msk.jenis_trans as jenis_trans,
                    jt.jenis_trans,
                    2 as urut
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
                                select min(ds.id_header) as id_header, ds.tgl_trans, ds.kode_gudang, ds.kode_barang, ds.kode_trans, ds.jenis_barang, ds.jenis_trans, ds.hrg_beli from det_stok ds
                                left join
                                    stok s
                                    on
                                        ds.id_header = s.id
                                where
                                    s.periode between '".$_start_date."' and '".$_end_date."' and
                                    ds.tgl_trans >= '".$_start_date."'
                                    -- ".$sql_jenis."
                                group by
                                    ds.tgl_trans, ds.kode_gudang, ds.kode_barang, ds.kode_trans, ds.jenis_barang, ds.jenis_trans, ds.hrg_beli
                            ) ds2
                            on
                                ds1.id_header = ds2.id_header and
                                ds1.tgl_trans = ds2.tgl_trans and
                                ds1.kode_gudang = ds2.kode_gudang and
                                ds1.kode_barang = ds2.kode_barang and
                                ds1.kode_trans = ds2.kode_trans and
                                ds1.jenis_barang = ds2.jenis_barang and
                                ds1.jenis_trans = ds2.jenis_trans and
                                ds1.hrg_beli = ds2.hrg_beli
                    ) ds
                    left join
                        stok s
                        on
                            ds.id_header = s.id
                    where
                        s.periode between '".$_start_date."' and '".$_end_date."' and
                        ds.tgl_trans between '".$_start_date."' and '".$_end_date."' and
                        ds.kode_gudang = '".$_kode_gudang."' and
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
                /* END - MASUK */

                union all

                /* KELUAR */
                -- Jumlah keluar diambil dari dokumen fisik (kirim/retur/adjustment), BUKAN dari
                -- det_stok_trans. det_stok_trans berhenti dicatat begitu jml_stok layer di det_stok
                -- mentok 0, jadi order yang melebihi stok yang tersedia (oversell) tidak pernah
                -- tercatat/terpotong di sana -- ini yang membuat saldo laporan sebelumnya tidak
                -- pernah bisa minus walau fisiknya sudah minus.
                select
                    klwr.tanggal,
                    klwr.kode_gudang,
                    klwr.kode_barang,
                    klwr.jenis_barang,
                    isnull(hrg.hrg_beli, hp.hrg_beli) as hrg_beli,
                    0 as jml_debet,
                    0 as debet,
                    hrg.jumlah as jml_kredit,
                    (hrg.jumlah * isnull(hrg.hrg_beli, hp.hrg_beli)) as kredit,
                    klwr.kode_trans as kode_trans,
                    klwr.jenis_trans,
                    3 as urut
                from
                (
                    ".$sql_jenis_trans_keluar."
                ) klwr
                left join
                    (
                        -- harga rata-rata tertimbang dari layer det_stok yang benar-benar
                        -- terpotong untuk kode_trans ini (kalau ada)
                        select
                            ds.kode_gudang,
                            ds.kode_barang,
                            dst.kode_trans,
                            sum(dst.jumlah) as jumlah,
                            sum(dst.jumlah * ds.hrg_beli) / sum(dst.jumlah) as hrg_beli
                        from det_stok_trans dst
                        left join
                            det_stok ds
                            on
                                ds.id = dst.id_header
                        where
                            dst.jumlah <> 0
                        group by
                            ds.kode_gudang,
                            ds.kode_barang,
                            dst.kode_trans,
                            ds.hrg_beli
                    ) hrg
                    on
                        hrg.kode_gudang = klwr.kode_gudang and
                        hrg.kode_barang = klwr.kode_barang and
                        hrg.kode_trans = klwr.kode_trans
                outer apply
                    (
                        -- fallback kalau kode_trans ini sama sekali tidak pernah kepotong stok
                        -- (order yang stoknya sudah habis duluan, spt OP/JBR/26/06054): pakai
                        -- harga layer det_stok terdekat tanggalnya untuk gudang+barang yang sama
                        select top 1
                            ds2.hrg_beli
                        from det_stok ds2
                        where
                            ds2.kode_gudang = klwr.kode_gudang and
                            ds2.kode_barang = klwr.kode_barang
                        order by
                            abs(datediff(day, ds2.tgl_trans, klwr.tanggal)) asc
                    ) hp
                where
                    klwr.kode_gudang = '".$_kode_gudang."' and
                    (klwr.kode_barang = '".$_kode_brg."' or '".$_kode_brg."' = 'all') and
                    klwr.tanggal between '".$_start_date."' and '".$_end_date."'
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
            order by
                data.kode_gudang asc,
                brg.nama asc,
                data.tanggal asc,
                data.urut asc,
                data.kode_trans asc
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

        $bulan = $params['bulan'];
        $kode_gudang = $params['gudang'];
        $kode_barang = $params['barang'];
        $jenis = $params['jenis'];
        $tahun = substr($params['tahun'], 0, 4);

        if ( $bulan != 'all' ) {
            $i = $bulan;

            $angka_bulan = (strlen($i) == 1) ? '0'.$i : $i;

            $date = $tahun.'-'.$angka_bulan.'-01';
            $start_date = date("Y-m-d", strtotime($date));
            $end_date = date("Y-m-t", strtotime($date));
        } else {
            $i = 1;
            $angka_bulan = (strlen($i) == 1) ? '0'.$i : $i;
            $_start_date = $tahun.'-'.$angka_bulan.'-01';
            $start_date = date("Y-m-d", strtotime($_start_date));

            $i = 12;
            $angka_bulan = (strlen($i) == 1) ? '0'.$i : $i;
            $_end_date = $tahun.'-'.$angka_bulan.'-01';
            $end_date = date("Y-m-t", strtotime($_end_date));
        }

        $data = $this->mappingDataReport($kode_barang, $kode_gudang, $jenis, $start_date, $end_date);

        $content['data'] = $data;
        $html = $this->load->view('report/kartu_stok/list', $content, TRUE);

        echo $html;
    }
}