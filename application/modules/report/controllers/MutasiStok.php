<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MutasiStok extends Public_Controller {

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
                "assets/report/mutasi_stok/js/mutasi-stok.js",
            ));
            $this->add_external_css(array(
                "assets/report/mutasi_stok/css/mutasi-stok.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;
            $content['title_menu'] = 'Laporan Mutasi Stok';

            // Load Indexx
            $data['view'] = $this->load->view('report/mutasi_stok/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_gudang_dan_barang()
    {
        $params = $this->input->post('params');

        $data_gdg = null;
        $data_brg = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                gdg1.*
            from gudang gdg1
            where
                gdg1.jenis like '%".$params."%'
            order by
                gdg1.nama asc
        ";
        $d_gdg = $m_conf->hydrateRaw( $sql );
        if ( $d_gdg->count() > 0 ) {
            $data_gdg = $d_gdg->toArray();
        }

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
                brg1.tipe = '".$params."'
            order by
                brg1.nama asc
        ";
        $d_brg = $m_conf->hydrateRaw( $sql );
        if ( $d_brg->count() > 0 ) {
            $data_brg = $d_brg->toArray();
        }

        $data = array(
            'gudang' => $data_gdg,
            'barang' => $data_brg
        );

        $this->result['list_data'] = $data;

        display_json( $this->result );
    }

    public function get_data_voadip($start_date, $end_date, $kode_gudang, $kode_brg, $jenis)
    {
        return $this->mappingDataReport($kode_brg, $kode_gudang, $jenis, $start_date, $end_date);
    }

    public function get_data_pakan($start_date, $end_date, $kode_gudang, $kode_brg, $jenis)
    {
        return $this->mappingDataReport($kode_brg, $kode_gudang, $jenis, $start_date, $end_date);
    }

    /**
     * Satu query untuk MASUK (dari det_stok, tidak terpengaruh masalah di bawah) dan satu
     * query untuk KELUAR (dari dokumen fisik kirim/retur/adjustment) -- menggantikan loop
     * per-baris det_stok + query det_stok_trans per-layer yang dipakai versi lama.
     *
     * KENAPA DIROMBAK: versi lama mengambil jumlah KELUAR dari det_stok_trans, yang berhenti
     * dicatat begitu jml_stok layer di det_stok mentok 0 -- order yang melebihi stok yang
     * tersedia (oversell) tidak pernah tercatat/terpotong di sana, sehingga mutasi & saldo
     * akhir tidak pernah bisa menunjukkan kondisi minus walau fisiknya sudah minus. Di sini,
     * jumlah KELUAR diambil langsung dari dokumen pengiriman/retur/adjustment fisik, dengan
     * harga per baris diambil dari layer det_stok yang benar-benar terpotong (kalau ada),
     * fallback ke harga layer terdekat tanggalnya kalau kode_trans itu sama sekali tidak
     * pernah kepotong stok (order yang stoknya sudah habis duluan).
     *
     * Perubahan tampilan yang disengaja: baris KELUAR sekarang dikelompokkan per tanggal
     * transaksi asli (tgl kirim/retur/adjustment), bukan per tanggal snapshot periode stok
     * seperti versi lama -- lebih konsisten dengan baris MASUK yang sudah begitu.
     */
    public function mappingDataReport($_kode_brg, $_kode_gudang, $_jenis, $_start_date, $_end_date)
    {
        $jenis = ( $_jenis == 'obat' ) ? 'voadip' : $_jenis;

        $sql_gudang_ds = null;
        $sql_gudang_klwr = null;
        if ( stristr($_kode_gudang, 'all') === FALSE ) {
            $sql_gudang_ds = "and ds.kode_gudang in (".$_kode_gudang.")";
            $sql_gudang_klwr = "and klwr.kode_gudang in (".$_kode_gudang.")";
        }

        $sql_brg_ds = null;
        $sql_brg_klwr = null;
        if ( stristr($_kode_brg, 'all') === FALSE ) {
            $sql_brg_ds = "and ds.kode_barang in ('".$_kode_brg."')";
            $sql_brg_klwr = "and klwr.kode_barang in ('".$_kode_brg."')";
        }

        $data = null;

        $m_conf = new \Model\Storage\Conf();

        /* =============================== MASUK =============================== */
        $sql_adjin = "
            union all

            select cast(ai.kode_gudang as varchar(10)) as tujuan, ai.kode as no_order, 'ORDER' as jenis_trans, 'ADJUSTMENT IN' as nama_jenis_trans
            from adjin_".$jenis." ai
        ";

        $sql_masuk = "
            select
                msk.tanggal,
                msk.kode_gudang,
                msk.kode_barang,
                b.nama as nama_barang,
                b.desimal_harga as decimal,
                g.nama as nama_gudang,
                msk.hrg_beli as harga_beli,
                msk.hrg_jual as harga_jual,
                msk.kode_trans,
                msk.jumlah,
                kirim.nama_jenis_trans as jenis_trans,
                dari.nama as dari
            from
            (
                select
                    ds.tgl_trans as tanggal,
                    ds.kode_gudang,
                    ds.kode_barang,
                    ds.kode_trans,
                    ds.jenis_barang,
                    ds.jenis_trans,
                    ds.hrg_beli,
                    ds.hrg_jual,
                    sum(isnull(ds.jml_stok, 0) + isnull(dst.jumlah, 0)) as jumlah
                from
                (
                    select ds1.* from det_stok ds1
                    right join
                        (
                            select min(ds.id_header) as id_header, ds.tgl_trans, ds.kode_gudang, ds.kode_barang, ds.kode_trans, ds.jenis_barang, ds.jenis_trans, ds.hrg_beli, ds.hrg_jual from det_stok ds
                            left join
                                stok s
                                on
                                    ds.id_header = s.id
                            where
                                s.periode between '".$_start_date."' and '".$_end_date."' and
                                ds.tgl_trans between '".$_start_date."' and '".$_end_date."' and
                                ds.jenis_barang = '".$jenis."' and
                                ds.jml_stok is not null
                                ".$sql_gudang_ds."
                                ".$sql_brg_ds."
                            group by
                                ds.tgl_trans, ds.kode_gudang, ds.kode_barang, ds.kode_trans, ds.jenis_barang, ds.jenis_trans, ds.hrg_beli, ds.hrg_jual
                        ) ds2
                        on
                            ds1.id_header = ds2.id_header and
                            ds1.tgl_trans = ds2.tgl_trans and
                            ds1.kode_gudang = ds2.kode_gudang and
                            ds1.kode_barang = ds2.kode_barang and
                            ds1.kode_trans = ds2.kode_trans and
                            ds1.jenis_barang = ds2.jenis_barang and
                            ds1.jenis_trans = ds2.jenis_trans and
                            ds1.hrg_beli = ds2.hrg_beli and
                            ds1.hrg_jual = ds2.hrg_jual
                ) ds
                left join
                    (select id_header, sum(jumlah) as jumlah from det_stok_trans group by id_header) dst
                    on
                        ds.id = dst.id_header
                group by
                    ds.tgl_trans, ds.kode_gudang, ds.kode_barang, ds.kode_trans, ds.jenis_barang, ds.jenis_trans, ds.hrg_beli, ds.hrg_jual
            ) msk
            left join
                (
                    select b2.* from barang b2
                    right join
                        (select max(b.id) as id, b.kode from barang b group by b.kode) b3
                        on
                            b2.id = b3.id
                ) b
                on
                    msk.kode_barang = b.kode
            left join
                gudang g
                on
                    msk.kode_gudang = g.id
            left join
                (
                    select * from (
                        select
                            case
                                when k.jenis_kirim = 'opkg' then
                                    case
                                        when k.jenis_tujuan <> 'peternak' then k.asal
                                        else rs.nim
                                    end
                                else
                                    case
                                        when k.jenis_tujuan <> 'peternak' then
                                            case when k.jenis_tujuan = 'gudang' then k.asal else k.tujuan end
                                        else rs.nim
                                    end
                            end as tujuan,
                            k.no_order,
                            case
                                when k.jenis_kirim = 'opkg' then
                                    case
                                        when k.jenis_tujuan <> 'peternak' then
                                            case when k.jenis_tujuan = 'gudang' then 'ORDER' else 'RETUR' end
                                        else 'ORDER'
                                    end
                                else 'ORDER'
                            end as jenis_trans,
                            case
                                when k.jenis_kirim = 'opkg' then
                                    case when k.jenis_tujuan <> 'peternak' then 'PINDAH' else 'ORDER' end
                                else 'ORDER'
                            end as nama_jenis_trans
                        from kirim_".$jenis." k
                        left join rdim_submit rs on k.tujuan = rs.noreg

                        union all

                        select
                            case when r.asal <> 'peternak' then r.id_asal else rs.nim end as tujuan,
                            r.no_order,
                            'RETUR' as jenis_trans,
                            'RETUR' as nama_jenis_trans
                        from retur_".$jenis." r
                        left join rdim_submit rs on r.id_asal = rs.noreg

                        ".$sql_adjin."
                    ) as data
                    group by
                        data.tujuan, data.no_order, data.jenis_trans, data.nama_jenis_trans
                ) as kirim
                on
                    kirim.no_order = msk.kode_trans and
                    kirim.jenis_trans = msk.jenis_trans
            left join
                (
                    select * from (
                        select cast(mm.nim as varchar(15)) as id, m.nama as nama from mitra m
                        right join
                            (select max(id) as id, nomor from mitra group by nomor) as group_mitra
                            on m.id = group_mitra.id
                        right join mitra_mapping mm on m.nomor = mm.nomor
                        group by m.nama, mm.nim

                        union all

                        select cast(g3.id as varchar(15)) as id, g3.nama as nama from gudang g3

                        union all

                        select cast(p.nomor as varchar(15)) as id, max(p.nama) as nama from pelanggan p
                        left join
                            (select max(id) as id, nomor from pelanggan group by nomor) as group_pelanggan
                            on p.id = group_pelanggan.id
                        where p.tipe = 'supplier' and p.jenis <> 'ekspedisi'
                        group by p.nomor
                    ) as supplier
                ) as dari
                on
                    dari.id = cast(kirim.tujuan as varchar(15))
            where
                (msk.tanggal >= g.tgl_stok_opaname or g.tgl_stok_opaname is null)
            order by
                msk.tanggal asc
        ";

        $d_masuk = $m_conf->hydrateRaw( $sql_masuk );
        if ( $d_masuk->count() > 0 ) {
            $d_masuk = $d_masuk->toArray();

            foreach ($d_masuk as $k_ds => $v_ds) {
                $data[ $v_ds['kode_gudang'] ]['kode'] = $v_ds['kode_gudang'];
                $data[ $v_ds['kode_gudang'] ]['nama'] = $v_ds['nama_gudang'];

                $key_brg = $v_ds['nama_barang'].' | '.$v_ds['kode_barang'];

                $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['kode'] = $v_ds['kode_barang'];
                $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['nama'] = $v_ds['nama_barang'];

                $key_masuk = str_replace('-', '', $v_ds['tanggal']).'-'.$v_ds['kode_trans'].'-'.$v_ds['harga_beli'].'-'.$v_ds['harga_jual'];

                $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tanggal'] ]['masuk'][ $key_masuk ] = array(
                    'kode' => $v_ds['kode_trans'],
                    'dari' => $v_ds['dari'],
                    'tgl_trans' => $v_ds['tanggal'],
                    'masuk' => $v_ds['jumlah'],
                    'keluar' => 0,
                    'stok_akhir' => $v_ds['jumlah'],
                    'harga_beli' => $v_ds['harga_beli'],
                    'nilai_beli' => ($v_ds['jumlah'] * $v_ds['harga_beli']),
                    'harga_jual' => $v_ds['harga_jual'],
                    'decimal' => $v_ds['decimal'],
                    'nilai_jual' => ($v_ds['jumlah'] * $v_ds['harga_jual']),
                    'jenis_trans' => $v_ds['jenis_trans'],
                );

                ksort( $data[ $v_ds['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_ds['tanggal'] ]['masuk'] );
            }
        }

        /* =============================== KELUAR =============================== */
        // try_cast (bukan cast biasa) supaya kode gudang legacy yang tidak numerik/kepanjangan
        // (mis. id_asal retur yang ternyata noreg plasma 11 digit, overflow tipe int) tidak
        // membuat query error -- cukup jadi NULL & tidak match gudang manapun.
        $sql_keluar = "
            select
                klwr.tanggal,
                klwr.kode_gudang,
                klwr.kode_barang,
                b.nama as nama_barang,
                b.desimal_harga as decimal,
                g.nama as nama_gudang,
                klwr.kode_trans,
                klwr.jumlah,
                klwr.jenis_trans,
                isnull(hrg.harga_beli, hp.hrg_beli) as harga_beli,
                isnull(hrg.harga_jual, hp.hrg_jual) as harga_jual,
                ke.nama as ke
            from
            (
                select
                    tv.tgl_terima as tanggal, try_cast(kv.asal as int) as kode_gudang, dkv.item as kode_barang, sum(dkv.jumlah) as jumlah, kv.no_order as kode_trans, 'DISTRIBUSI' as jenis_trans,
                    case
                        when kv.jenis_tujuan <> 'peternak' then
                            case when kv.jenis_tujuan = 'gudang' then kv.tujuan else kv.asal end
                        else rs.nim
                    end as tujuan_raw
                from kirim_".$jenis." kv
                left join terima_".$jenis." tv on tv.id_kirim_".$jenis." = kv.id
                left join rdim_submit rs on kv.tujuan = rs.noreg
                join det_kirim_".$jenis." dkv on dkv.id_header = kv.id
                where kv.tgl_kirim between '".$_start_date."' and '".$_end_date."'
                group by tv.tgl_terima, kv.asal, dkv.item, kv.no_order, kv.jenis_tujuan, kv.tujuan, rs.nim

                union all

                select rv.tgl_retur as tanggal, try_cast(rv.id_asal as int) as kode_gudang, drv.item as kode_barang, sum(drv.jumlah) as jumlah, rv.no_retur as kode_trans, 'RETUR' as jenis_trans, rv.id_tujuan as tujuan_raw
                from retur_".$jenis." rv
                join det_retur_".$jenis." drv on drv.id_header = rv.id
                where rv.asal = 'gudang' and rv.tgl_retur between '".$_start_date."' and '".$_end_date."'
                group by rv.tgl_retur, rv.id_asal, drv.item, rv.no_retur, rv.id_tujuan

                union all

                select av.tanggal, av.kode_gudang, av.kode_barang, av.jumlah, av.kode as kode_trans, 'ADJUSTMENT OUT' as jenis_trans, av.kode as tujuan_raw
                from adjout_".$jenis." av
                where av.tanggal between '".$_start_date."' and '".$_end_date."'
            ) klwr
            left join
                (
                    select b2.* from barang b2
                    right join
                        (select max(b.id) as id, b.kode from barang b group by b.kode) b3
                        on
                            b2.id = b3.id
                ) b
                on
                    klwr.kode_barang = b.kode
            left join
                gudang g
                on
                    klwr.kode_gudang = g.id
            left join
                (
                    -- harga rata-rata tertimbang dari layer det_stok yang benar-benar
                    -- terpotong untuk kode_trans ini (kalau ada)
                    select
                        ds.kode_gudang,
                        ds.kode_barang,
                        dst.kode_trans,
                        sum(dst.jumlah * ds.hrg_beli) / sum(dst.jumlah) as harga_beli,
                        sum(dst.jumlah * ds.hrg_jual) / sum(dst.jumlah) as harga_jual
                    from det_stok_trans dst
                    left join
                        det_stok ds
                        on
                            ds.id = dst.id_header
                    where
                        dst.jumlah <> 0
                    group by
                        ds.kode_gudang, ds.kode_barang, dst.kode_trans
                ) hrg
                on
                    hrg.kode_gudang = klwr.kode_gudang and
                    hrg.kode_barang = klwr.kode_barang and
                    hrg.kode_trans = klwr.kode_trans
            outer apply
                (
                    -- fallback kalau kode_trans ini sama sekali tidak pernah kepotong stok
                    -- (order yang stoknya sudah habis duluan): pakai harga layer det_stok
                    -- terdekat tanggalnya untuk gudang+barang yang sama
                    select top 1
                        ds2.hrg_beli, ds2.hrg_jual
                    from det_stok ds2
                    where
                        ds2.kode_gudang = klwr.kode_gudang and
                        ds2.kode_barang = klwr.kode_barang
                    order by
                        abs(datediff(day, ds2.tgl_trans, klwr.tanggal)) asc
                ) hp
            left join
                (
                    select * from (
                        select cast(mm.nim as varchar(15)) as id, m.nama as nama from mitra m
                        right join
                            (select max(id) as id, nomor from mitra group by nomor) as group_mitra
                            on m.id = group_mitra.id
                        right join mitra_mapping mm on m.nomor = mm.nomor
                        group by m.nama, mm.nim

                        union all

                        select cast(g3.id as varchar(15)) as id, g3.nama as nama from gudang g3

                        union all

                        select cast(p.nomor as varchar(15)) as id, max(p.nama) as nama from pelanggan p
                        left join
                            (select max(id) as id, nomor from pelanggan group by nomor) as group_pelanggan
                            on p.id = group_pelanggan.id
                        where p.tipe = 'supplier' and p.jenis <> 'ekspedisi'
                        group by p.nomor

                        union all

                        select cast(ao2.kode as varchar(15)) as id, cast(ao2.keterangan as varchar(max)) as nama from adjout_".$jenis." ao2
                    ) as supplier
                ) ke
                on
                    ke.id = cast(klwr.tujuan_raw as varchar(15))
            where
                (klwr.tanggal >= g.tgl_stok_opaname or g.tgl_stok_opaname is null)
                ".$sql_gudang_klwr."
                ".$sql_brg_klwr."
            order by
                klwr.tanggal asc
        ";

        $d_keluar = $m_conf->hydrateRaw( $sql_keluar );
        if ( $d_keluar->count() > 0 ) {
            $d_keluar = $d_keluar->toArray();

            foreach ($d_keluar as $k_dst => $v_dst) {
                $data[ $v_dst['kode_gudang'] ]['kode'] = $v_dst['kode_gudang'];
                if ( empty($data[ $v_dst['kode_gudang'] ]['nama']) ) {
                    $data[ $v_dst['kode_gudang'] ]['nama'] = $v_dst['nama_gudang'];
                }

                $key_brg = $v_dst['nama_barang'].' | '.$v_dst['kode_barang'];

                $data[ $v_dst['kode_gudang'] ]['detail'][ $key_brg ]['kode'] = $v_dst['kode_barang'];
                if ( empty($data[ $v_dst['kode_gudang'] ]['detail'][ $key_brg ]['nama']) ) {
                    $data[ $v_dst['kode_gudang'] ]['detail'][ $key_brg ]['nama'] = $v_dst['nama_barang'];
                }

                $key_keluar = str_replace('-', '', $v_dst['tanggal']).'-'.$v_dst['kode_trans'];

                $data[ $v_dst['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_dst['tanggal'] ]['keluar'][ $key_keluar ] = array(
                    'kode' => $v_dst['kode_trans'],
                    'ke' => $v_dst['ke'],
                    'tgl_trans' => $v_dst['tanggal'],
                    'tgl_stok' => $v_dst['tanggal'],
                    'masuk' => 0,
                    'keluar' => $v_dst['jumlah'],
                    'stok_akhir' => 0,
                    'harga_beli' => $v_dst['harga_beli'],
                    'nilai_beli' => ($v_dst['jumlah'] * $v_dst['harga_beli']),
                    'harga_jual' => $v_dst['harga_jual'],
                    'decimal' => $v_dst['decimal'],
                    'nilai_jual' => ($v_dst['jumlah'] * $v_dst['harga_jual']),
                    'jenis_trans' => $v_dst['jenis_trans'],
                );

                ksort( $data[ $v_dst['kode_gudang'] ]['detail'][ $key_brg ]['detail'][ $v_dst['tanggal'] ]['keluar'] );
            }
        }

        if ( !empty($data) ) {
            foreach ($data as $k_gdg => $v_gdg) {
                if ( !empty($v_gdg['detail']) ) {
                    ksort( $data[ $k_gdg ]['detail'] );
                    foreach ($v_gdg['detail'] as $k_brg => $v_brg) {
                        if ( !empty($v_brg['detail']) ) {
                            ksort( $data[ $k_gdg ]['detail'][ $k_brg ]['detail'] );
                        }
                    }
                }
            }
        }

        return $data;
    }

    public function get_data()
    {
        $params = $this->input->post('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $jenis = $params['jenis'];
        $kode_gudang = $params['kode_gudang'];
        $kode_brg = $params['kode_brg'];

        $data = null;
        if ( $jenis == 'obat' ) {
            $data = $this->get_data_voadip($start_date, $end_date, $kode_gudang, $kode_brg, $jenis);
        } else {
            $data = $this->get_data_pakan($start_date, $end_date, $kode_gudang, $kode_brg, $jenis);
        }

        $content['data'] = $data;
        $html = $this->load->view('report/mutasi_stok/list', $content, TRUE);

        $this->result['html'] = $html;

        display_json( $this->result );
    }
}
