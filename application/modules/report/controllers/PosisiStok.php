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
        // Mode RIIL (lihat application/config/app_mode.php): sembunyikan
        // baris det_stok utk order_pakan (OPKS) yg SUDAH ditransfer ke partner
        // (intercompany_pakan_log.status='DITERIMA') - order itu bukan lagi
        // tanggung jawab GML di buku RIIL. Stok yg BELUM ditransfer tetap
        // tampil normal. det_stok.kode_trans = order_pakan.no_order utk baris
        // OPKS (lihat IntercompanyPakan::kirimKePartner()), jadi match-nya
        // lewat itu - HANYA dipakai di titik yg menentukan "supply"
        // (existing_gb & supply CTE, snapshot + fallback ORDER TERAKHIR) -
        // TIDAK disentuh di NOT EXISTS anti-dobel-hitung (ds3/dst_chk) atau
        // lookup harga (hrg/hp/ds2) supaya logika FIFO/gap yg sudah rumit &
        // teruji tidak ikut berubah perilakunya. Mode MANAJEMEN sengaja TIDAK
        // difilter (tetap perlu terlihat sbg riwayat/tracking). Dicek DUA
        // arah: order ini yg DIKIRIM keluar (id lokal di tbl_id_asal) MAUPUN
        // hasil DITERIMA dari partner (id lokal di tbl_id_tujuan).
        $sql_filter_stok_transfer = (defined('APP_MODE') && APP_MODE === 'riil') ? "
                    and not exists (
                        select 1 from intercompany_pakan_log ipl
                        inner join order_pakan op on op.id = ipl.tbl_id_asal
                        where ipl.tbl_name_asal = 'order_pakan' and op.no_order = ds.kode_trans and ipl.status = 'DITERIMA'
                    )
                    and not exists (
                        select 1 from intercompany_pakan_log ipl
                        inner join order_pakan op on op.id = ipl.tbl_id_tujuan
                        where ipl.tbl_name_tujuan = 'order_pakan' and op.no_order = ds.kode_trans and ipl.status = 'DITERIMA'
                    )" : "";

        $jenis = ( stristr($_jenis, 'obat') !== false ) ? 'voadip' : $_jenis;
        $next_date = date('Y-m-d', strtotime($_date.' +1 day'));

        // Sama spt di atas, tapi utk sisi KELUAR (OPKG, kirim_pakan) - dokumen
        // kirim_pakan yg SUDAH ditransfer TIDAK PERNAH nulis det_stok sama
        // sekali, tapi tetap muncul di $sql_gap_keluar (dibaca dari dokumen
        // fisik kv/kirim_pakan). HANYA berlaku utk jenis 'pakan' - intercompany
        // belum ada utk voadip/obat, dan `kv.id` beda ID-space antara
        // kirim_pakan vs kirim_voadip (hindari kebetulan tabrakan id). Dicek
        // DUA arah spt di atas.
        $sql_filter_kirim_transfer = (defined('APP_MODE') && APP_MODE === 'riil' && $jenis === 'pakan') ? "
            and not exists (
                select 1 from intercompany_pakan_log ipl
                where ipl.status = 'DITERIMA' and (
                    (ipl.tbl_name_asal = 'kirim_pakan' and ipl.tbl_id_asal = kv.id)
                    or (ipl.tbl_name_tujuan = 'kirim_pakan' and ipl.tbl_id_tujuan = kv.id)
                )
            )" : "";

        // Sisi MASUK (OPKS, order_pakan) versi $sql_gap_masuk - order yg SUDAH
        // ditransfer tapi belum sempat kebentuk det_stok-nya (masih di jendela
        // gap) - sama alasan dgn $sql_filter_stok_transfer, cuma match-nya
        // lewat kv.no_order (bukan ds.kode_trans, beda alias di konteks ini).
        // Dicek DUA arah spt di atas.
        $sql_filter_order_gap = (defined('APP_MODE') && APP_MODE === 'riil' && $jenis === 'pakan') ? "
            and not exists (
                select 1 from intercompany_pakan_log ipl
                inner join order_pakan op on op.id = ipl.tbl_id_asal
                where ipl.tbl_name_asal = 'order_pakan' and op.no_order = kv.no_order and ipl.status = 'DITERIMA'
            )
            and not exists (
                select 1 from intercompany_pakan_log ipl
                inner join order_pakan op on op.id = ipl.tbl_id_tujuan
                where ipl.tbl_name_tujuan = 'order_pakan' and op.no_order = kv.no_order and ipl.status = 'DITERIMA'
            )" : "";

        // Mode MANAJEMEN: stok hasil TERIMA dari partner (intercompany) HANYA
        // ditulis ke det_stok_manajemen/stok_manajemen (shadow) - TIDAK PERNAH
        // ke det_stok riil (lihat IntercompanyPakanTerima::terima()). Supaya
        // ikut muncul di laporan MANAJEMEN, snapshot det_stok di existing_gb &
        // supply (+ fallback ORDER TERAKHIR) di-UNION dgn versi shadow-nya,
        // masing2 pakai periode "eff" SENDIRI (dihitung dari stok_manajemen,
        // BUKAN eff.p yg dihitung dari stok riil - batch riil & manajemen bisa
        // beda kecepatan). SENGAJA TIDAK menyentuh cabang GAP (baca dokumen
        // fisik kirim_pakan langsung, bukan det_stok, jadi sumbernya sudah sama
        // utk riil/manajemen) atau lookup harga (hrg/hp) atau NOT EXISTS
        // anti-dobel-hitung (ds3/dst_chk) - sama alasan spt
        // $sql_filter_stok_transfer di atas (logika FIFO/gap sudah rumit &
        // teruji). CATATAN RISIKO: krn cabang gap tidak disentuh, transaksi
        // manajemen yg KEBETULAN jatuh di jendela gap (blm sempat ke-snapshot)
        // DAN transaksi yg sudah ke-snapshot scr teori bisa dobel-hitung dlm
        // skenario tertentu - blm ditangani, cukup jarang terjadi (baru
        // relevan kalau lihat laporan persis di hari yg sama batch berjalan).
        $sql_manajemen_existing_gb = (defined('APP_MODE') && APP_MODE === 'manajemen') ? "

                union

                select ds.kode_gudang, ds.kode_barang
                from det_stok_manajemen ds
                left join stok_manajemen s on ds.id_header = s.id
                cross join
                    (select max(periode) as p from stok_manajemen where periode <= '".$next_date."') eff_mnj
                where
                    s.periode = eff_mnj.p and
                    ds.jenis_barang = '".$jenis."' and
                    (ds.kode_gudang = '".$_kode_gudang."' or '".$_kode_gudang."' = 'all') and
                    (ds.kode_barang = '".$_kode_brg."' or '".$_kode_brg."' = 'all')" : "";

        $sql_manajemen_supply_snapshot = (defined('APP_MODE') && APP_MODE === 'manajemen') ? "

                union all

                select
                    ds.kode_gudang, ds.kode_barang, ds.kode_trans, ds.hrg_beli, ds.tgl_trans as tanggal,
                    sum(isnull(ds.jml_stok, 0)) as jumlah
                from det_stok_manajemen ds
                left join
                    stok_manajemen s
                    on
                        ds.id_header = s.id
                cross join
                    (select max(periode) as p from stok_manajemen where periode <= '".$next_date."') eff_mnj
                where
                    s.periode = eff_mnj.p and
                    ds.jenis_barang = '".$jenis."' and
                    (ds.kode_gudang = '".$_kode_gudang."' or '".$_kode_gudang."' = 'all') and
                    (ds.kode_barang = '".$_kode_brg."' or '".$_kode_brg."' = 'all')
                group by
                    ds.kode_gudang, ds.kode_barang, ds.kode_trans, ds.hrg_beli, ds.tgl_trans" : "";

        // Guard tambahan (2026-09-28) - dipasang di NOT EXISTS anti-dobel-hitung cabang gap
        // (ds3 di 'supply' & dst_chk di 'demand') - kode_trans yg SUDAH tercatat sbg layer
        // di det_stok_manajemen (shadow, periode eff_mnj sendiri) jangan direkonstruksi lagi
        // dari dokumen fisik gap. TANPA ini, order intercompany yg diterima (cuma ada di
        // shadow, bukan det_stok riil) lolos dari guard ds3/dst_chk (yg cuma cek det_stok
        // riil) & muncul DOBEL: 1x dari $sql_manajemen_supply_snapshot (harga asli, benar)
        // + 1x lagi dari cabang gap (harga hasil fallback lookup, salah) - kejadian nyata di
        // laporan Posisi Stok mode MANAJEMEN utk OPK/MLG/26/09213 (2 baris beda harga).
        $sql_manajemen_guard_supply = (defined('APP_MODE') && APP_MODE === 'manajemen') ? "
                        and not exists (
                            select 1 from det_stok_manajemen ds3m
                            left join stok_manajemen s3m on ds3m.id_header = s3m.id
                            cross join
                                (select max(periode) as p from stok_manajemen where periode <= '".$next_date."') eff_mnj3
                            where
                                s3m.periode = eff_mnj3.p and
                                ds3m.kode_gudang = g.kode_gudang and
                                ds3m.kode_barang = g.kode_barang and
                                ds3m.kode_trans = g.no_order_asal
                        )" : "";

        $sql_manajemen_guard_demand = (defined('APP_MODE') && APP_MODE === 'manajemen') ? "
                        and not exists (
                            select 1 from det_stok_trans_manajemen dst_chkm
                            left join det_stok_manajemen ds_chkm on ds_chkm.id = dst_chkm.id_header
                            left join stok_manajemen s_chkm on ds_chkm.id_header = s_chkm.id
                            cross join
                                (select max(periode) as p from stok_manajemen where periode <= '".$next_date."') eff_mnj4
                            where
                                s_chkm.periode = eff_mnj4.p and
                                ds_chkm.kode_gudang = k.kode_gudang and
                                ds_chkm.kode_barang = k.kode_barang and
                                dst_chkm.kode_trans = k.kode_trans
                        )" : "";

        $sql_manajemen_order_terakhir = (defined('APP_MODE') && APP_MODE === 'manajemen') ? "

                union all

                select
                    lst.kode_gudang, lst.kode_barang, lst.kode_trans, lst.hrg_beli, lst.tanggal, 0 as jumlah
                from
                (
                    select
                        ds.kode_gudang, ds.kode_barang, ds.kode_trans, ds.hrg_beli, ds.tgl_trans as tanggal,
                        row_number() over (partition by ds.kode_gudang, ds.kode_barang order by ds.tgl_trans desc, ds.kode_trans desc) as rn
                    from det_stok_manajemen ds
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
                where lst.rn = 1" : "";

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
            select tv.tgl_terima as tanggal, try_cast(kv.tujuan as int) as kode_gudang, dkv.item as kode_barang, sum(dkv.jumlah) as jumlah, kv.no_order as kode_trans, kv.no_order as no_order_asal
            from kirim_".$jenis." kv
            join det_kirim_".$jenis." dkv on dkv.id_header = kv.id
            join terima_".$jenis." tv on tv.id_kirim_".$jenis." = kv.id
            where kv.jenis_tujuan = 'gudang'
            ".$sql_filter_order_gap."
            group by tv.tgl_terima, kv.tujuan, dkv.item, kv.no_order

            union all

            -- kode_trans = no_retur (dokumen retur sendiri, dipakai utk DISPLAY & FIFO netting),
            -- TAPI det_stok menyimpan layer RETUR di bawah kode_trans = no_order ASAL (bukan
            -- no_retur) -- no_order_asal disediakan terpisah spy NOT EXISTS di bawah bisa
            -- mengorelasikan ke det_stok dgn kunci yang BENAR, bukan ketipu kode_trans yang beda skema.
            select rv.tgl_retur as tanggal, try_cast(rv.id_tujuan as int) as kode_gudang, drv.item as kode_barang, sum(drv.jumlah) as jumlah, rv.no_retur as kode_trans, rv.no_order as no_order_asal
            from retur_".$jenis." rv
            join det_retur_".$jenis." drv on drv.id_header = rv.id
            where rv.jenis_retur = 'opkp'
            group by rv.tgl_retur, rv.id_tujuan, drv.item, rv.no_retur, rv.no_order

            union all

            select av.tanggal, av.kode_gudang, av.kode_barang, av.jumlah, av.kode as kode_trans, av.kode as no_order_asal
            from adjin_".$jenis." av
        ";

        // Guard tambahan (2026-09-28, direvisi setelah klarifikasi user) - dokumen anchor
        // kirim_pakan sisi PENERIMA intercompany ADA 2 jenis, kv.asal-nya beda makna:
        //   - jenis_kirim='opks' (IntercompanyPakanTerima::prosesTerima()): asal diisi KODE
        //     SUPPLIER (bukan gudang) - kalau ikut di-join ke tabel gudang, itu salah sasaran
        //     (beda skema ID), HARUS dikecualikan dari gap keluar gudang.
        //   - jenis_kirim='opkg' (IntercompanyPakanTerima::prosesTerimaOpkg()): asal diisi KODE
        //     GUDANG, dan ID gudang memang disinkronkan antar-instance (konfirmasi user) - jadi
        //     ini betul2 penarikan stok riil dari gudang tsb, JANGAN dikecualikan.
        // Guard versi lama (2026-09-28 pagi) salah - mengecualikan KEDUANYA tanpa bedakan
        // jenis_kirim, jadi transaksi OPKG intercompany ikut hilang dari perhitungan stok.
        $sql_guard_intercompany_masuk = ($jenis === 'pakan') ? "
            and (
                kv.jenis_kirim = 'opkg' or
                not exists (
                    select 1 from intercompany_pakan_log ipl
                    where ipl.tbl_name_tujuan = 'kirim_pakan' and ipl.tbl_id_tujuan = kv.id
                )
            )" : "";

        $sql_gap_keluar = "
            select kv.tgl_kirim as tanggal, try_cast(kv.asal as int) as kode_gudang, dkv.item as kode_barang, sum(dkv.jumlah) as jumlah, kv.no_order as kode_trans
            from kirim_".$jenis." kv
            join det_kirim_".$jenis." dkv on dkv.id_header = kv.id
            where 1=1
            ".$sql_filter_kirim_transfer."
            ".$sql_guard_intercompany_masuk."
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
                    ds.jenis_barang = '".$jenis."' and
                    (ds.kode_gudang = '".$_kode_gudang."' or '".$_kode_gudang."' = 'all') and
                    (ds.kode_barang = '".$_kode_brg."' or '".$_kode_brg."' = 'all')
                    ".$sql_filter_stok_transfer."
                    ".$sql_manajemen_existing_gb."

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
                -- layer dari snapshot det_stok periode terakhir yang tersedia. Pakai jml_stok
                -- APA ADANYA (bukan jml_stok + det_stok_trans) -- jml_stok SUDAH mencerminkan
                -- konsumsi nyata yang sudah tercatat, kapanpun batch itu memprosesnya. Menambah
                -- balik det_stok_trans lalu menghitung ulang FIFO sendiri (versi lama) salah
                -- kalau batch TERNYATA sudah memproses sebagian/semua transaksi gap di bawah --
                -- alokasi FIFO buatan sendiri (asumsi tertua dulu) bisa beda dari alokasi nyata
                -- yang sudah kepakai (mis. lot RETUR yang lebih baru malah kepakai duluan),
                -- menghasilkan sisa per-lot yang salah walau totalnya kebetulan sama. Lihat
                -- memory posisi-stok-vs-kartu-stok-selisih-gap.
                --
                -- SENGAJA tidak difilter `ds.tgl_trans < eff.p` (beda dari versi lama) -- ada
                -- barang (mis. jenis pakan) yang snapshot periode-nya TIDAK di-roll-forward,
                -- jadi baris utk transaksi HARI INI sendiri (tgl_trans = eff.p, bukan < eff.p)
                -- muncul LANGSUNG sbg baris det_stok periode=eff.p, bukan lewat jalur gap. Kalau
                -- baris begini di-exclude di sini, dia jatuh ke cabang gap-masuk yg salah pakai
                -- jumlah bruto dari dokumen fisik alih2 jml_stok yg sudah benar. Aman diambil
                -- semua tanpa syarat tanggal krn cabang gap-masuk & demand di bawah sudah
                -- meng-exclude kode_trans yg TERNYATA sudah exist di sini (lihat NOT EXISTS).
                select
                    ds.kode_gudang, ds.kode_barang, ds.kode_trans, ds.hrg_beli, ds.tgl_trans as tanggal,
                    sum(isnull(ds.jml_stok, 0)) as jumlah
                from det_stok ds
                left join
                    stok s
                    on
                        ds.id_header = s.id
                cross join eff
                where
                    s.periode = eff.p and
                    ds.jenis_barang = '".$jenis."' and
                    (ds.kode_gudang = '".$_kode_gudang."' or '".$_kode_gudang."' = 'all') and
                    (ds.kode_barang = '".$_kode_brg."' or '".$_kode_brg."' = 'all')
                    ".$sql_filter_stok_transfer."
                group by
                    ds.kode_gudang, ds.kode_barang, ds.kode_trans, ds.hrg_beli, ds.tgl_trans
                    ".$sql_manajemen_supply_snapshot."

                union all

                -- masuk di masa gap: tanggal >= periode terakhir s/d tanggal laporan (inklusif),
                -- belum ikut batch hitung-stok manapun. NOT EXISTS thd det_stok -- kalau kode_trans
                -- ini TERNYATA sudah kebentuk jadi layer det_stok sendiri (batch sudah proses),
                -- jangan ditambah lagi di sini supaya tidak dobel-hitung.
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
                    (g.kode_barang = '".$_kode_brg."' or '".$_kode_brg."' = 'all') and
                    not exists (
                        -- Discope ke periode = eff.p SAJA (bukan seluruh histori det_stok) --
                        -- kode_trans yang sama bisa muncul lagi di banyak snapshot periode lain
                        -- selama layer itu belum habis (batch membawa layer belum-habis maju ke
                        -- snapshot berikutnya), jadi match TANPA guard periode ini nyaris selalu
                        -- true untuk kode_trans manapun yang pernah ada -- salah total. TIDAK ada
                        -- syarat tgl_trans di sini -- sengaja sama liberalnya dgn cabang snapshot
                        -- di atas (yang sekarang juga tidak difilter tgl_trans) supaya keduanya
                        -- konsisten: begitu det_stok punya barisnya sendiri utk periode ini
                        -- (berapapun tgl_trans-nya), percaya jml_stok-nya, jangan direkonstruksi
                        -- lagi dari dokumen fisik di sini (dobel-hitung).
                        --
                        -- Match ke g.no_order_asal (BUKAN g.kode_trans) -- utk RETUR, det_stok
                        -- menyimpan layernya di bawah kode_trans = no_order ASAL (dari order yang
                        -- diretur), sedangkan g.kode_trans di sini = no_retur (dokumen retur
                        -- sendiri, skema kode yang beda). Match ke kode_trans langsung akan
                        -- SELALU gagal utk RETUR, membuat layer yang SUDAH ada di det_stok
                        -- (kode_trans = no_order asal) ikut ditambahkan LAGI di sini scr dobel.
                        select 1 from det_stok ds3
                        left join stok s3 on ds3.id_header = s3.id
                        where
                            s3.periode = eff.p and
                            ds3.kode_gudang = g.kode_gudang and
                            ds3.kode_barang = g.kode_barang and
                            ds3.kode_trans = g.no_order_asal
                    )".$sql_manajemen_guard_supply."

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
                        ".$sql_filter_stok_transfer."
                ) lst
                where lst.rn = 1
                ".$sql_manajemen_order_terakhir."
            ),
            demand as (
                -- total keluar di masa gap per gudang+barang -- dipakai (bukan ditampilkan)
                -- utk netting FIFO thd supply, HANYA utk transaksi yang BELUM kepotong via
                -- det_stok_trans manapun (NOT EXISTS di bawah). Kalau sudah ada rekamannya,
                -- berarti batch SUDAH memprosesnya (sudah tercermin di jml_stok masing-masing
                -- layer lewat supply di atas) -- dihitung lagi di sini akan dobel-hitung DAN
                -- alokasi FIFO buatan sendiri bisa beda dari alokasi nyata yang sudah kepakai.
                select kode_gudang, kode_barang, sum(jumlah) as total_keluar
                from ( ".$sql_gap_keluar." ) k
                cross join eff
                where
                    k.tanggal >= eff.p and k.tanggal <= '".$_date."' and
                    (k.kode_gudang = '".$_kode_gudang."' or '".$_kode_gudang."' = 'all') and
                    (k.kode_barang = '".$_kode_brg."' or '".$_kode_brg."' = 'all') and
                    not exists (
                        -- Discope ke periode = eff.p SAJA (bukan seluruh histori det_stok_trans)
                        -- -- id_header yang sama (dan kode_trans yang sama) bisa muncul lagi tiap
                        -- kali batch membawa layer yang belum habis maju ke snapshot berikutnya,
                        -- jadi match TANPA guard periode ini nyaris selalu true -- salah total.
                        select 1 from det_stok_trans dst_chk
                        left join det_stok ds_chk on ds_chk.id = dst_chk.id_header
                        left join stok s_chk on ds_chk.id_header = s_chk.id
                        where
                            s_chk.periode = eff.p and
                            ds_chk.kode_gudang = k.kode_gudang and
                            ds_chk.kode_barang = k.kode_barang and
                            dst_chk.kode_trans = k.kode_trans
                    )".$sql_manajemen_guard_demand."
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
