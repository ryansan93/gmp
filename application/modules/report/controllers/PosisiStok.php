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

    /**
     * Posisi stok akhir per tanggal, dihitung dari det_stok/det_stok_trans -- metodologi
     * yang SAMA dengan Kartu Stok (report/KartuStok.php), supaya kedua laporan konsisten.
     *
     * Cuma tampilkan SISA (saldo akhir positif) per gudang+barang, diitemisasi per kode_trans
     * (layer stok) -- tidak ada baris minus/keluar sendiri. "Saldo akhir tanggal X" ekuivalen
     * dengan "Saldo Awal tanggal X+1" versi Kartu Stok: pakai stok.periode = X+1 (snapshot
     * proses hitung-stok yang jalan di awal hari X+1, sudah mencakup semua transaksi s/d
     * akhir hari X) dan det_stok.tgl_trans < X+1.
     *
     * GAP: batch hitung-stok jalan sekali semalam, jadi periode = X+1 belum tentu ada (mis.
     * tanggal laporan = hari ini, batch besok pagi belum jalan). Kalau begitu, pakai periode
     * TERAKHIR yang tersedia sebagai dasar, lalu tambahkan transaksi fisik (kirim/retur/
     * adjustment, sama seperti sumber Kartu Stok) dari SETELAH periode itu s/d tanggal laporan,
     * supaya transaksi hari berjalan tetap ikut kehitung walau belum diproses batch.
     *
     * NETTING: keluar di masa gap TIDAK ditampilkan sebagai baris minus sendiri -- dipakai
     * (FIFO, layer terlama dulu) utk mengurangi layer yang ada, baru SISA per layer yang
     * ditampilkan. Layer yang habis terpakai (sisa 0) tidak ditampilkan sama sekali.
     */
    public function mappingDataReport($_kode_brg, $_kode_gudang, $_jenis, $_date)
    {
        $jenis = ( stristr($_jenis, 'obat') !== false ) ? 'voadip' : $_jenis;
        $next_date = date('Y-m-d', strtotime($_date.' +1 day'));

        $m_conf = new \Model\Storage\Conf();

        $data = null;

        // Transaksi fisik "gap" -- masuk & keluar, sumber sama dgn sql_jenis_trans_masuk/keluar
        // di KartuStok. Cuma dipakai kalau ada tanggal setelah periode batch terakhir s/d
        // tanggal laporan (biasanya cuma "hari ini").
        $sql_gap_masuk = "
            -- Pakai tgl_terima (bukan tgl_kirim) -- det_stok/HitungStok baru menambah layer stok
            -- begitu barang DITERIMA gudang tujuan, bukan saat dikirim. Kalau pakai tgl_kirim,
            -- kiriman yang kirim & terimanya beda hari bisa jatuh di celah (tidak kehitung di
            -- 'supply' base -- karena tgl_trans sudah >= eff.p -- ataupun di gap ini -- karena
            -- tgl_kirim < eff.p) sehingga stok yang sudah diterima hilang dari laporan.
            select tv.tgl_terima as tanggal, try_cast(kv.tujuan as int) as kode_gudang, dkv.item as kode_barang, sum(dkv.jumlah) as jumlah, kv.no_order as kode_trans
            from kirim_".$jenis." kv
            join det_kirim_".$jenis." dkv on dkv.id_header = kv.id
            join terima_".$jenis." tv on tv.id_kirim_".$jenis." = kv.id
            where kv.jenis_tujuan = 'gudang'
            group by tv.tgl_terima, kv.tujuan, dkv.item, kv.no_order

            union all

            select rv.tgl_retur as tanggal, try_cast(rv.id_tujuan as int) as kode_gudang, drv.item as kode_barang, sum(drv.jumlah) as jumlah, rv.no_retur as kode_trans
            from retur_".$jenis." rv
            join det_retur_".$jenis." drv on drv.id_header = rv.id
            where rv.jenis_retur = 'opkp'
            group by rv.tgl_retur, rv.id_tujuan, drv.item, rv.no_retur

            union all

            select av.tanggal, av.kode_gudang, av.kode_barang, av.jumlah, av.kode as kode_trans
            from adjin_".$jenis." av
        ";

        $sql_gap_keluar = "
            select kv.tgl_kirim as tanggal, try_cast(kv.asal as int) as kode_gudang, dkv.item as kode_barang, sum(dkv.jumlah) as jumlah, kv.no_order as kode_trans
            from kirim_".$jenis." kv
            join det_kirim_".$jenis." dkv on dkv.id_header = kv.id
            group by kv.tgl_kirim, kv.asal, dkv.item, kv.no_order

            union all

            select rv.tgl_retur as tanggal, try_cast(rv.id_asal as int) as kode_gudang, drv.item as kode_barang, sum(drv.jumlah) as jumlah, rv.no_retur as kode_trans
            from retur_".$jenis." rv
            join det_retur_".$jenis." drv on drv.id_header = rv.id
            where rv.jenis_retur = 'opkg'
            group by rv.tgl_retur, rv.id_asal, drv.item, rv.no_retur

            union all

            select av.tanggal, av.kode_gudang, av.kode_barang, av.jumlah, av.kode as kode_trans
            from adjout_".$jenis." av
        ";

        $sql = "
            ;with eff as (
                -- periode terbaru yang <= next_date -- kalau batch hitung-stok untuk next_date
                -- belum jalan (mis. tanggal laporan = hari ini, batch besok pagi belum ada),
                -- jatuh ke periode terakhir yang tersedia daripada kosong sama sekali.
                select max(periode) as p from stok where periode <= '".$next_date."'
            ),
            existing_gb as (
                -- gudang+barang yg SUDAH kecover di 2 sumber supply utama (snapshot det_stok
                -- ATAU gap masuk) -- dipakai NOT EXISTS oleh cabang fallback 'ORDER TERAKHIR'
                -- di bawah. PENTING kalau lihat tanggal MASA LALU setelah batch sudah lanjut
                -- (mis. buka laporan tgl 31 Juli tapi hari ini sudah 1 Agustus & batch utk
                -- 1 Agustus sudah jalan): eff.p jadi 1 Agustus (> tanggal laporan) shg gap
                -- masuk (yg butuh eff.p <= tanggal laporan) otomatis KOSONG, DAN layer yg
                -- sudah 0 sebelum eff.p tidak pernah dibawa det_stok ke snapshot berikutnya
                -- -- gudang+barang itu jadi lenyap total tanpa fallback ini.
                select distinct ds.kode_gudang, ds.kode_barang
                from det_stok ds
                left join stok s on ds.id_header = s.id
                cross join eff
                where
                    s.periode = eff.p and
                    ds.tgl_trans < eff.p and
                    ds.jenis_barang = '".$jenis."' and
                    (ds.kode_gudang = '".$_kode_gudang."' or '".$_kode_gudang."' = 'all') and
                    (ds.kode_barang = '".$_kode_brg."' or '".$_kode_brg."' = 'all')

                union

                select g.kode_gudang, g.kode_barang
                from ( ".$sql_gap_masuk." ) g
                cross join eff
                where
                    g.kode_gudang is not null and
                    g.tanggal >= eff.p and g.tanggal <= '".$_date."' and
                    (g.kode_gudang = '".$_kode_gudang."' or '".$_kode_gudang."' = 'all') and
                    (g.kode_barang = '".$_kode_brg."' or '".$_kode_brg."' = 'all')
            ),
            supply as (
                -- layer dari snapshot det_stok periode terakhir yang tersedia (selalu lebih
                -- lama drpd transaksi gap manapun -- FIFO alami lewat urutan tanggal)
                select
                    ds.kode_gudang, ds.kode_barang, ds.kode_trans, ds.hrg_beli, ds.tgl_trans as tanggal,
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
                cross join eff
                where
                    s.periode = eff.p and
                    ds.tgl_trans < eff.p and
                    ds.jenis_barang = '".$jenis."' and
                    (ds.kode_gudang = '".$_kode_gudang."' or '".$_kode_gudang."' = 'all') and
                    (ds.kode_barang = '".$_kode_brg."' or '".$_kode_brg."' = 'all')
                group by
                    ds.kode_gudang, ds.kode_barang, ds.kode_trans, ds.hrg_beli, ds.tgl_trans

                union all

                -- masuk di masa gap: tanggal >= periode terakhir s/d tanggal laporan (inklusif),
                -- belum ikut batch hitung-stok manapun
                select
                    g.kode_gudang, g.kode_barang, g.kode_trans,
                    isnull(hrg.hrg_beli, hp.hrg_beli) as hrg_beli, g.tanggal, g.jumlah
                from ( ".$sql_gap_masuk." ) g
                cross join eff
                left join
                    (
                        -- harga rata-rata tertimbang dari layer det_stok yang benar-benar
                        -- terpotong untuk kode_trans ini (kalau ada)
                        select
                            ds.kode_gudang, ds.kode_barang, dst.kode_trans,
                            sum(dst.jumlah * ds.hrg_beli) / sum(dst.jumlah) as hrg_beli
                        from det_stok_trans dst
                        left join det_stok ds on ds.id = dst.id_header
                        where dst.jumlah <> 0
                        group by ds.kode_gudang, ds.kode_barang, dst.kode_trans
                    ) hrg
                    on hrg.kode_gudang = g.kode_gudang and hrg.kode_barang = g.kode_barang and hrg.kode_trans = g.kode_trans
                outer apply
                    (
                        -- fallback: harga layer det_stok terdekat tanggalnya untuk gudang+barang
                        -- yang sama, kalau kode_trans ini sama sekali tidak pernah kepotong stok
                        select top 1 ds2.hrg_beli
                        from det_stok ds2
                        where ds2.kode_gudang = g.kode_gudang and ds2.kode_barang = g.kode_barang
                        order by abs(datediff(day, ds2.tgl_trans, g.tanggal)) asc
                    ) hp
                where
                    g.kode_gudang is not null and
                    g.tanggal >= eff.p and g.tanggal <= '".$_date."' and
                    (g.kode_gudang = '".$_kode_gudang."' or '".$_kode_gudang."' = 'all') and
                    (g.kode_barang = '".$_kode_brg."' or '".$_kode_brg."' = 'all')

                union all

                -- FALLBACK: gudang+barang yg sudah HABIS SEBELUM eff.p (jadi tidak kebawa
                -- snapshot det_stok manapun lagi) & TIDAK punya gap masuk yg relevan (mis.
                -- tanggal laporan sudah lewat, batch sudah lanjut ke hari berikutnya). Cari
                -- order/layer TERAKHIR dari SELURUH histori det_stok (bukan cuma snapshot
                -- eff.p), tampilkan dgn jumlah 0 spy gudang+barang itu tidak lenyap total.
                select
                    lst.kode_gudang, lst.kode_barang, lst.kode_trans, lst.hrg_beli, lst.tanggal, 0 as jumlah
                from
                (
                    select
                        ds.kode_gudang, ds.kode_barang, ds.kode_trans, ds.hrg_beli, ds.tgl_trans as tanggal,
                        row_number() over (partition by ds.kode_gudang, ds.kode_barang order by ds.tgl_trans desc, ds.kode_trans desc) as rn
                    from det_stok ds
                    where
                        ds.jenis_barang = '".$jenis."' and
                        ds.tgl_trans <= '".$_date."' and
                        (ds.kode_gudang = '".$_kode_gudang."' or '".$_kode_gudang."' = 'all') and
                        (ds.kode_barang = '".$_kode_brg."' or '".$_kode_brg."' = 'all') and
                        not exists (
                            select 1 from existing_gb eg
                            where eg.kode_gudang = ds.kode_gudang and eg.kode_barang = ds.kode_barang
                        )
                ) lst
                where lst.rn = 1
            ),
            demand as (
                -- total keluar di masa gap per gudang+barang -- dipakai (bukan ditampilkan)
                -- utk netting FIFO thd supply, sama seperti det_stok_trans akan lakukan
                -- begitu batch hitung-stok jalan
                select kode_gudang, kode_barang, sum(jumlah) as total_keluar
                from ( ".$sql_gap_keluar." ) k
                cross join eff
                where
                    k.tanggal >= eff.p and k.tanggal <= '".$_date."' and
                    (k.kode_gudang = '".$_kode_gudang."' or '".$_kode_gudang."' = 'all') and
                    (k.kode_barang = '".$_kode_brg."' or '".$_kode_brg."' = 'all')
                group by kode_gudang, kode_barang
            ),
            fifo as (
                select
                    s.kode_gudang, s.kode_barang, s.kode_trans, s.hrg_beli, s.jumlah, s.tanggal,
                    sum(s.jumlah) over (partition by s.kode_gudang, s.kode_barang order by s.tanggal, s.kode_trans rows unbounded preceding) as running_after,
                    isnull(d.total_keluar, 0) as total_demand
                from supply s
                left join demand d on d.kode_gudang = s.kode_gudang and d.kode_barang = s.kode_barang
            )
            select
                sa.kode_gudang,
                sa.kode_barang,
                '".$jenis."' as jenis_barang,
                '".$_date."' as tanggal,
                '' as jenis_trans,
                sa.kode_trans,
                sa.hrg_beli,
                sa.sisa as jml_saldo_akhir,
                (sa.sisa * sa.hrg_beli) as saldo_akhir,
                gdg.nama as nama_gudang,
                brg.nama as nama_barang
            from
            (
                select
                    kode_gudang, kode_barang, kode_trans, hrg_beli,
                    case
                        when running_after <= total_demand then 0
                        when (running_after - jumlah) >= total_demand then jumlah
                        else running_after - total_demand
                    end as sisa,
                    -- order/layer TERAKHIR (tanggal terbaru) per gudang+barang -- dipakai supaya
                    -- tetap tampil (sisa 0) kalau itu order terakhir yg sudah full terdistribusi,
                    -- bukan hilang total dari laporan.
                    row_number() over (partition by kode_gudang, kode_barang order by tanggal desc, kode_trans desc) as rn_terakhir
                from fifo
            ) sa
            left join
                (
                    select * from gudang
                ) gdg
                on
                    sa.kode_gudang = gdg.id
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
                    sa.kode_barang = brg.kode
            where
                sa.sisa <> 0 or sa.rn_terakhir = 1
            order by
                sa.kode_gudang asc,
                brg.nama asc,
                sa.kode_trans asc
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
