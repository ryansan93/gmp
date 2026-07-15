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
     * Posisi stok per tanggal, dihitung SEPENUHNYA dari dokumen fisik (kirim/retur/adjustment),
     * bukan dari det_stok/det_stok_trans.
     *
     * KENAPA DIROMBAK TOTAL (bukan cuma KELUAR): det_stok_trans berhenti dicatat begitu
     * jml_stok layer di det_stok mentok 0 -- order yang melebihi stok yang tersedia (oversell)
     * tidak pernah tercatat/terpotong di sana. Tapi ternyata Saldo Awal (dari det_stok) JUGA
     * tidak bisa dipakai apa adanya: jml_stok didesain tidak pernah negatif, jadi begitu satu
     * hari mengalami oversell dan turun ke 0, hari-hari SESUDAHNYA mulai menghitung dari 0 lagi
     * -- defisitnya "hilang" dan tidak pernah terbawa ke Saldo Awal hari berikutnya. Karena itu
     * seluruh SALDO AWAL/MASUK/KELUAR di sini dihitung dari ledger dokumen fisik yang sama,
     * supaya konsisten dan defisit ikut terbawa.
     *
     * Struktur:
     * - SALDO AWAL: satu baris teragregasi per gudang+barang = seluruh masuk dikurangi seluruh
     *   keluar SEBELUM tanggal laporan (tidak diitemisasi per transaksi -- kalau diitemisasi,
     *   baris bisa menumpuk ratusan sejak barang itu ada).
     * - MASUK/KELUAR: diitemisasi per transaksi, HANYA PADA tanggal laporan itu sendiri (supaya
     *   tidak dobel-hitung dengan yang sudah masuk Saldo Awal).
     * - Harga per baris: rata-rata tertimbang dari layer det_stok yang benar-benar terpotong
     *   untuk kode_trans itu (kalau ada), fallback ke harga layer det_stok terdekat tanggalnya.
     */
    public function mappingDataReport($_kode_brg, $_kode_gudang, $_jenis, $_date)
    {
        $jenis = ( stristr($_jenis, 'obat') !== false ) ? 'voadip' : $_jenis;

        $m_conf = new \Model\Storage\Conf();

        // MASUK mentah: barang tiba di gudang -- lewat kirim (supplier->gudang ATAU
        // gudang->gudang, ditandai jenis_tujuan='gudang'), retur dari plasma (jenis_retur=
        // 'opkp'), atau adjustment in.
        $sql_masuk_raw = "
            select kv.tgl_kirim as tanggal, try_cast(kv.tujuan as int) as kode_gudang, dkv.item as kode_barang, '".$jenis."' as jenis_barang, sum(dkv.jumlah) as jumlah, kv.no_order as kode_trans, 'ORDER' as jenis_trans
            from kirim_".$jenis." kv
            join det_kirim_".$jenis." dkv on dkv.id_header = kv.id
            where kv.jenis_tujuan = 'gudang'
            group by kv.tgl_kirim, kv.tujuan, dkv.item, kv.no_order

            union all

            select rv.tgl_retur as tanggal, try_cast(rv.id_tujuan as int) as kode_gudang, drv.item as kode_barang, '".$jenis."' as jenis_barang, sum(drv.jumlah) as jumlah, rv.no_retur as kode_trans, 'RETUR DARI PLASMA' as jenis_trans
            from retur_".$jenis." rv
            join det_retur_".$jenis." drv on drv.id_header = rv.id
            where rv.jenis_retur = 'opkp'
            group by rv.tgl_retur, rv.id_tujuan, drv.item, rv.no_retur

            union all

            select av.tanggal, av.kode_gudang, av.kode_barang, '".$jenis."' as jenis_barang, av.jumlah, av.kode as kode_trans, 'ADJUSTMENT IN' as jenis_trans
            from adjin_".$jenis." av
        ";

        // KELUAR mentah: barang keluar gudang -- kirim ke peternak/gudang lain (asal=gudang),
        // retur gudang ke atas (jenis_retur='opkg', saat ini tidak ada data), atau adjustment out.
        $sql_keluar_raw = "
            select kv.tgl_kirim as tanggal, try_cast(kv.asal as int) as kode_gudang, dkv.item as kode_barang, '".$jenis."' as jenis_barang, sum(dkv.jumlah) as jumlah, kv.no_order as kode_trans, 'DISTRIBUSI' as jenis_trans
            from kirim_".$jenis." kv
            join det_kirim_".$jenis." dkv on dkv.id_header = kv.id
            group by kv.tgl_kirim, kv.asal, dkv.item, kv.no_order

            union all

            select rv.tgl_retur as tanggal, try_cast(rv.id_asal as int) as kode_gudang, drv.item as kode_barang, '".$jenis."' as jenis_barang, sum(drv.jumlah) as jumlah, rv.no_retur as kode_trans, 'RETUR DARI GUDANG' as jenis_trans
            from retur_".$jenis." rv
            join det_retur_".$jenis." drv on drv.id_header = rv.id
            where rv.jenis_retur = 'opkg'
            group by rv.tgl_retur, rv.id_asal, drv.item, rv.no_retur

            union all

            select av.tanggal, av.kode_gudang, av.kode_barang, '".$jenis."' as jenis_barang, av.jumlah, av.kode as kode_trans, 'ADJUSTMENT OUT' as jenis_trans
            from adjout_".$jenis." av
        ";

        $data = null;

        $sql = "
            ;with ledger as (
                select tanggal, kode_gudang, kode_barang, jenis_barang, jumlah, kode_trans, jenis_trans, 'masuk' as arah from ( ".$sql_masuk_raw." ) m
                union all
                select tanggal, kode_gudang, kode_barang, jenis_barang, jumlah, kode_trans, jenis_trans, 'keluar' as arah from ( ".$sql_keluar_raw." ) k
            ),
            priced as (
                select
                    l.tanggal, l.kode_gudang, l.kode_barang, l.jenis_barang, l.jumlah, l.kode_trans, l.jenis_trans, l.arah,
                    isnull(hrg.hrg_beli, hp.hrg_beli) as hrg_beli
                from ledger l
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
                    on hrg.kode_gudang = l.kode_gudang and hrg.kode_barang = l.kode_barang and hrg.kode_trans = l.kode_trans
                outer apply
                    (
                        -- fallback: harga layer det_stok terdekat tanggalnya untuk gudang+barang
                        -- yang sama, kalau kode_trans ini sama sekali tidak pernah kepotong stok
                        select top 1 ds2.hrg_beli
                        from det_stok ds2
                        where ds2.kode_gudang = l.kode_gudang and ds2.kode_barang = l.kode_barang
                        order by abs(datediff(day, ds2.tgl_trans, l.tanggal)) asc
                    ) hp
                where
                    l.kode_gudang is not null and
                    (l.kode_gudang = '".$_kode_gudang."' or '".$_kode_gudang."' = 'all') and
                    (l.kode_barang = '".$_kode_brg."' or '".$_kode_brg."' = 'all')
            )
            select
                data.tanggal,
                data.kode_gudang,
                data.kode_barang,
                data.jenis_barang,
                data.hrg_beli,
                sum(data.jml_saldo_awal) as jml_saldo_awal,
                sum(data.saldo_awal) as saldo_awal,
                sum(data.jml_debet) as jml_debet,
                sum(data.debet) as debet,
                sum(data.jml_kredit) as jml_kredit,
                sum(data.kredit) as kredit,
                (isnull(sum(data.jml_saldo_awal), 0) + isnull(sum(data.jml_debet), 0)) - isnull(sum(data.jml_kredit), 0) as jml_saldo_akhir,
                (isnull(sum(data.saldo_awal), 0) + isnull(sum(data.debet), 0)) - isnull(sum(data.kredit), 0) as saldo_akhir,
                data.kode_trans,
                data.jenis_trans,
                gdg.nama as nama_gudang,
                brg.nama as nama_barang
            from
            (
                /* SALDO AWAL - satu baris teragregasi per gudang+barang, seluruh histori
                   sebelum tanggal laporan */
                select
                    '".$_date."' as tanggal,
                    kode_gudang,
                    kode_barang,
                    jenis_barang,
                    sum(case when arah = 'masuk' then jumlah * hrg_beli else -(jumlah * hrg_beli) end) / nullif(sum(case when arah = 'masuk' then jumlah else -jumlah end), 0) as hrg_beli,
                    sum(case when arah = 'masuk' then jumlah else -jumlah end) as jml_saldo_awal,
                    sum(case when arah = 'masuk' then jumlah * hrg_beli else -(jumlah * hrg_beli) end) as saldo_awal,
                    0 as jml_debet,
                    0 as debet,
                    0 as jml_kredit,
                    0 as kredit,
                    'Saldo Awal' as kode_trans,
                    null as jenis_trans
                from priced
                where tanggal < '".$_date."'
                group by kode_gudang, kode_barang, jenis_barang
                /* END - SALDO AWAL */

                union all

                /* MASUK - diitemisasi, hanya pada tanggal laporan */
                select
                    tanggal, kode_gudang, kode_barang, jenis_barang, hrg_beli,
                    0 as jml_saldo_awal, 0 as saldo_awal,
                    jumlah as jml_debet, (jumlah * hrg_beli) as debet,
                    0 as jml_kredit, 0 as kredit,
                    kode_trans, jenis_trans
                from priced
                where arah = 'masuk' and tanggal = '".$_date."'
                /* END - MASUK */

                union all

                /* KELUAR - diitemisasi, hanya pada tanggal laporan */
                select
                    tanggal, kode_gudang, kode_barang, jenis_barang, hrg_beli,
                    0 as jml_saldo_awal, 0 as saldo_awal,
                    0 as jml_debet, 0 as debet,
                    jumlah as jml_kredit, (jumlah * hrg_beli) as kredit,
                    kode_trans, jenis_trans
                from priced
                where arah = 'keluar' and tanggal = '".$_date."'
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
                brg.nama asc,
                data.tanggal asc
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
