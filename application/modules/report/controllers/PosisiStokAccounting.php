<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PosisiStokAccounting extends Public_Controller {

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
                "assets/report/posisi_stok_accounting/js/posisi-stok-accounting.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/posisi_stok_accounting/css/posisi-stok-accounting.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['gudang'] = $this->getGudang();
            $content['barang'] = $this->getBarang();
            $content['title_menu'] = 'Laporan Posisi Stok';

            // Load Indexx
            $data['view'] = $this->load->view('report/posisi_stok_accounting/index', $content, TRUE);
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
            }

            $sql_jenis = "and ds.jenis_barang = '".$jenis."'";
        }

        $sql_ds_kode_gudang = null;
        if ( stristr($_kode_gudang, 'all') === false ) {
            $sql_ds_kode_gudang = "and ds.kode_gudang = '".$_kode_gudang."'";
        }

        $sql_ds_kode_barang = null;
        if ( stristr($_kode_brg, 'all') === false ) {
            $sql_ds_kode_barang = "and ds.kode_barang = '".$_kode_brg."'";
        }

        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.kode_stok as kode_trans,
                data.tanggal,
                data.kode_gudang,
                data.kode_barang,
                sum(data.jml_debet) as jml_debet,
                sum(data.debet) as debet,
                sum(data.jml_kredit) as jml_kredit,
                sum(data.kredit) as kredit,
                sum(data.jml_debet) - sum(data.jml_kredit) as jml_saldo_akhir,
                sum(data.debet) - sum(data.kredit) as saldo_akhir,
                case
                    when (sum(data.debet) - sum(data.kredit)) > 0 and (sum(data.jml_debet) - sum(data.jml_kredit)) > 0 then
                        (sum(data.debet) - sum(data.kredit)) / (sum(data.jml_debet) - sum(data.jml_kredit))
                    else
                        0
                end as hrg_beli,
                gdg.nama as nama_gudang,
                brg.nama as nama_barang
            from
            (
                select
                    data.kode_stok,
                    data.tanggal,
                    data.kode_gudang,
                    data.kode_barang,
                    data.jml_debet,
                    sum(data.tot_baru) as debet,
                    0 as jml_kredit,
                    0 as kredit
                from
                (
                    select 
                        ds.kode_trans as kode_stok,
                        ds.tgl_trans as tanggal,
                        ds.kode_gudang,
                        ds.kode_barang,
                        ds.jumlah as jml_debet,
                        ds.total as debet,
                        0 as jml_kredit,
                        0 as kredit,
                        tbl.tbl_name,
                        ds_tot.total,
                        sum(isnull(dj.nominal, 0)) as jurnal,
                        case
                            when ds_tot.total <> sum(isnull(dj.nominal, 0)) then
                                round((sum(isnull(dj.nominal, 0)) / ds_tot.jumlah) * ds.jumlah, 2)
                            else
                                ds.jumlah * ds.hrg_beli
                        end as tot_baru
                    from 
                    (
                        select
                            ds.kode_trans,
                            ds.tgl_trans,
                            ds.kode_gudang,
                            ds.jenis_barang,
                            ds.kode_barang,
                            ds.jumlah,
                            ds.hrg_beli,
                            ds.jumlah * ds.hrg_beli as total
                        from det_stok ds
                        where
                            ds.tgl_trans <= '".$_date."'
                            and ds.jenis_barang = '".$jenis."'
                            ".$sql_ds_kode_gudang."
                            ".$sql_ds_kode_barang."
                            and ds.id_header is not null
                        group by
                            ds.kode_trans,
                            ds.tgl_trans,
                            ds.kode_gudang,
                            ds.jenis_barang,
                            ds.kode_barang,
                            ds.jumlah,
                            ds.hrg_beli
                    ) ds
                    left join
                        (
                            select
                                data.kode_trans,
                                data.jenis_barang,
                                sum(data.jumlah) as jumlah,
                                sum(data.total) as total
                            from
                            (
                                select
                                    ds.kode_trans,
                                    ds.jenis_barang,
                                    ds.kode_barang,
                                    ds.jumlah,
                                    ds.hrg_beli,
                                    ds.jumlah * ds.hrg_beli as total
                                from det_stok ds
                                where
                                    ds.tgl_trans <= '".$_date."'
                                    and ds.jenis_barang = '".$jenis."'
                                    ".$sql_ds_kode_gudang."
                                    ".$sql_ds_kode_barang."
                                    and ds.id_header is not null
                                group by
                                    ds.kode_trans,
                                    ds.jenis_barang,
                                    ds.kode_barang,
                                    ds.jumlah,
                                    ds.hrg_beli
                            ) data
                            group by
                                data.kode_trans,
                                data.jenis_barang
                        ) ds_tot
                        on
                            ds.kode_trans = ds_tot.kode_trans and
                            ds.jenis_barang = ds_tot.jenis_barang
                    left join
                        (
                            select tv.id as tbl_id, 'terima_voadip' as tbl_name, kv.tgl_kirim as tanggal, kv.no_order as kode_trans, kv.no_order, 'ORDER' as jenis_trans, 'voadip' as jenis
                            from terima_voadip tv
                            left join
                                kirim_voadip kv 
                                on
                                    tv.id_kirim_voadip = kv.id
                            where 
                                (kv.jenis_kirim = 'opks' or (kv.jenis_kirim = 'opkg' and kv.jenis_tujuan = 'gudang')) and
                                kv.tgl_kirim <= '".$_date."'
                    
                            union all
                    
                            select rv.id as tbl_id, 'retur_voadip' as tbl_name, rv.tgl_retur as tanggal, rv.no_retur as kode_trans, rv.no_order, 'RETUR DARI PLASMA' as jenis_trans, 'voadip' as jenis from retur_voadip rv where rv.tgl_retur <= '".$_date."' and rv.jenis_retur = 'opkp'
                    
                            union all
                    
                            select av.id as tbl_id, 'adjin_voadip' as tbl_name, av.tanggal, av.kode as kode_trans, av.kode as no_order, 'ADJUSTMENT IN' as jenis_trans, 'voadip' as jenis from adjin_voadip av where av.tanggal <= '".$_date."'
                            
                            union all
                            
                            select tp.id as tbl_id, 'terima_pakan' as tbl_name, kp.tgl_kirim as tanggal, kp.no_order as kode_trans, kp.no_order, 'ORDER' as jenis_trans, 'pakan' as jenis from terima_pakan tp
                            left join
                                kirim_pakan kp 
                                on
                                    tp.id_kirim_pakan = kp.id
                            where 
                                kp.jenis_kirim = 'opks' and
                                kp.tgl_kirim <= '".$_date."'
                                and tp.no_bbm not like '%-1'
                    
                            union all
                    
                            select rp.id as tbl_id, 'retur_pakan' as tbl_name, rp.tgl_retur as tanggal, rp.no_retur as kode_trans, rp.no_order, 'RETUR DARI PLASMA' as jenis_trans, 'pakan' as jenis from retur_pakan rp where rp.tgl_retur <= '".$_date."' and rp.jenis_retur = 'opkp'
                    
                            union all
                    
                            select ap.id as tbl_id, 'adjin_voadip' as tbl_name, ap.tanggal, ap.kode as kode_trans, ap.kode as no_order, 'ADJUSTMENT IN' as jenis_trans, 'pakan' as jenis from adjin_pakan ap where ap.tanggal <= '".$_date."'
                        ) tbl
                        on
                            tbl.jenis = ds.jenis_barang and
                            tbl.no_order = ds.kode_trans
                    left join
                        (
                            select
                                dj.tbl_id,
                                dj.ref_kode,
                                dj.tbl_name,
                                case
                                    when dj.coa_asal = '21180.100' then
                                        dj.nominal
                                    else
                                        0-dj.nominal
                                end as nominal
                            from det_jurnal dj 
                            where 
                                dj.tanggal <= '".$_date."' and (dj.coa_asal = '21180.100' or dj.coa_tujuan = '21180.100')
                                
                            union all
                            
                            select
                                dj.tbl_id,
                                dj.ref_kode,
                                dj.tbl_name,
                                case
                                    when dj.coa_asal = '12041.000' then
                                        dj.nominal
                                    else
                                        0-dj.nominal
                                end as nominal
                            from det_jurnal dj 
                            where 
                                dj.tanggal <= '".$_date."' and (dj.coa_asal = '12041.000' or dj.coa_tujuan = '12041.000')
                                
                            union all
                            
                            select
                                dj.tbl_id,
                                dj.ref_kode,
                                dj.tbl_name,
                                case
                                    when dj.coa_tujuan = '12050.000' then
                                        dj.nominal
                                    else
                                        0-dj.nominal
                                end as nominal
                            from det_jurnal dj 
                            where 
                                dj.tanggal <= '".$_date."' and (dj.coa_tujuan = '12050.000')
                        ) dj
                        on
                            (dj.ref_kode is null and dj.tbl_name = tbl.tbl_name and dj.tbl_id = cast(tbl.tbl_id as varchar(50))) or
                            (dj.ref_kode is not null and dj.ref_kode = cast(ds.kode_trans as varchar(50)))
                    group by
                        ds.kode_trans,
                        ds.tgl_trans,
                        ds.kode_gudang,
                        ds.kode_barang,
                        ds.jumlah,
                        ds.total,
                        tbl.tbl_name,
                        ds_tot.total,
                        ds_tot.jumlah,
                        ds.hrg_beli
                ) data
                group by
                    data.kode_stok,
                    data.tanggal,
                    data.kode_gudang,
                    data.kode_barang,
                    data.jml_debet
                        
                union all
                    
                select 
                    data.kode_stok,
                    data.tanggal,
                    data.kode_gudang,
                    data.kode_barang,
                    0 as jml_debet,
                    0 as debet,
                    data.jumlah as jml_kredit,
                    sum(data.tot_baru) as kredit
                from
                (
                    select 
                        dst.kode_stok,
                        dst.kode_gudang,
                        dst.jumlah,
                        dst.kode_barang,
                        dst.jumlah * dst.hrg_beli as total,
                        dst_tot.total as tot,
                        isnull(dj.nominal, 0) as jurnal,
                        dst_tot.jumlah as tot_jumlah,
                        round((isnull(dj.nominal, 0) / dst_tot.jumlah), 0) as harga,
                        case
                            when dst_tot.total <> isnull(dj.nominal, 0) then
                                round((isnull(dj.nominal, 0) / dst_tot.jumlah) * dst.jumlah, 2)
                            else
                                dst.jumlah * dst.hrg_beli
                        end as tot_baru,
                        tbl.tbl_id,
                        tbl.tbl_name,
                        dst.tgl_trans as tanggal
                    from
                        (
                            select * from
                            (
                                select	
                                    ds.tgl_trans,
                                    ds.kode_trans as kode_stok,
                                    ds.kode_gudang,
                                    ds.jenis_barang,
                                    ds.hrg_beli,
                                    dst.kode_trans,
                                    dst.jumlah,
                                    dst.kode_barang
                                from det_stok_trans dst 
                                left join
                                    det_stok ds
                                    on
                                        dst.id_header = ds.id
                                where
                                    ds.tgl_trans <= '".$_date."' and
                                    ds.jenis_barang = '".$jenis."'
                                    
                                union all
                                
                                select
                                    tv.tgl_terima as tgl_trans,
                                    SUBSTRING(REPLACE(REPLACE(dj.kode_trans, 'OVO/', ''), 'OP/', ''), 1, 3) as kode_stok,
                                    dj.kode_gudang,
                                    'voadip' as jenis_barang,
                                    0 as hrg_beli,
                                    dj.kode_trans,
                                    sum(dtv.jumlah) as jumlah,
                                    dtv.item as kode_barang
                                from det_terima_voadip dtv 
                                left join
                                    terima_voadip tv 
                                    on
                                        tv.id = dtv.id_header
                                left join
                                    (
                                        select supplier as kode_gudang, tbl_id, REPLACE(REPLACE(kode_trans, 'BBM/OVK/G', 'OP'), 'BBM/OVK/S', 'OVO') as kode_trans from det_jurnal where tbl_name = 'terima_voadip' and tanggal <= '2025-12-31' group by supplier, tbl_id, kode_trans
                                    ) dj
                                    on
                                        dj.tbl_id = tv.id
                                where
                                    tv.tgl_terima <= '".$_date."' and
                                    not exists (select * from kirim_voadip kv where id = tv.id_kirim_voadip) and
                                    '".$jenis."' like '%voadip%'
                                group by
                                    tv.tgl_terima,
                                    dj.kode_gudang,
                                    dj.kode_trans,
                                    dtv.item
                            ) ds
                            where
                                ds.jenis_barang = '".$jenis."'
                                ".$sql_ds_kode_gudang."
                                ".$sql_ds_kode_barang."
                        ) dst
                    left join
                        (
                            select * from 
                            (
                                select
                                    dst.kode_trans,
                                    ds.jenis_barang,
                                    sum(dst.jumlah) as jumlah,
                                    sum(dst.jumlah * ds.hrg_beli) as total
                                from det_stok_trans dst
                                right join
                                    det_stok ds 
                                    on
                                        dst.id_header = ds.id
                                right join
                                    stok s
                                    on
                                        s.id = ds.id_header
                                where
                                    ds.id_header is not null and
                                    ds.jenis_barang = '".$jenis."'
                                group by
                                    dst.kode_trans,
                                    ds.jenis_barang 
                                    
                                union all
                                
                                select
                                    dj.kode_trans,
                                    'voadip' as jenis_barang,
                                    sum(dtv.jumlah) as jumlah,
                                    0 as total
                                from det_terima_voadip dtv 
                                left join
                                    terima_voadip tv 
                                    on
                                        tv.id = dtv.id_header
                                left join
                                    (
                                        select tbl_id, REPLACE(REPLACE(kode_trans, 'BBM/OVK/G', 'OP'), 'BBM/OVK/S', 'OVO') as kode_trans from det_jurnal where tbl_name = 'terima_voadip' and tanggal <= '2025-12-31' group by tbl_id, kode_trans
                                    ) dj
                                    on
                                        dj.tbl_id = tv.id
                                where
                                    tv.tgl_terima <= '".$_date."' and
                                    not exists (select * from kirim_voadip kv where id = tv.id_kirim_voadip) and
                                    '".$jenis."' like '%voadip%'
                                group by
                                    dj.kode_trans
                            ) ds
                        ) dst_tot
                        on
                            dst.kode_trans = dst_tot.kode_trans and
                            dst.jenis_barang = dst_tot.jenis_barang
                    left join
                        (
                            select
                            *
                            from 
                            (
                                select 
                                    tv.id as tbl_id, 
                                    'terima_voadip' as tbl_name, 
                                    tv.tgl_terima as tanggal, 
                                    case
                                        when kv.id is not null then
                                            kv.no_order
                                        else
                                            dj.kode_trans
                                    end as kode_trans,
                                    case
                                        when kv.id is not null then
                                            kv.no_order
                                        else
                                            dj.kode_trans
                                    end as no_order,
                                    'DISTRIBUSI' as jenis_trans, 
                                    'voadip' as jenis 
                                from terima_voadip tv
                                left join
                                    kirim_voadip kv 
                                    on
                                        tv.id_kirim_voadip = kv.id
                                left join
                                    (
                                        select tbl_id, REPLACE(REPLACE(kode_trans, 'BBM/OVK/G', 'OP'), 'BBM/OVK/S', 'OVO') as kode_trans from det_jurnal where tbl_name = 'terima_voadip' and tanggal <= '2025-12-31' group by tbl_id, kode_trans
                                    ) dj
                                    on
                                        dj.tbl_id = tv.id
                                where 
                                    tv.tgl_terima <= '".$_date."'
                            ) data
                            where
                                data.kode_trans not like 'OVO%'
                    
                            union all
                    
                            select rv.id as tbl_id, 'retur_voadip' as tbl_name, rv.tgl_retur as tanggal, rv.no_retur as kode_trans, rv.no_order, 'RETUR DARI GUDANG' as jenis_trans, 'voadip' as jenis from retur_voadip rv where rv.tgl_retur <= '".$_date."' and rv.jenis_retur = 'opkg'
                    
                            union all
                    
                            select av.id as tbl_id, 'adjout_voadip' as tbl_name, av.tanggal, av.kode as kode_trans, av.kode as no_order, 'ADJUSTMENT OUT' as jenis_trans, 'voadip' as jenis from adjout_voadip av where av.tanggal <= '".$_date."'
                            
                            union all
                            
                            select tp.id as tbl_id, 'terima_pakan' as tbl_name, kp.tgl_kirim as tanggal, kp.no_order as kode_trans, kp.no_order, 'DISTRIBUSI' as jenis_trans, 'pakan' as jenis from terima_pakan tp
                            left join
                                kirim_pakan kp 
                                on
                                    tp.id_kirim_pakan = kp.id
                            where 
                                kp.jenis_kirim <> 'opks' and
                                kp.tgl_kirim <= '".$_date."'
                    
                            union all
                    
                            select rp.id as tbl_id, 'retur_pakan' as tbl_name, rp.tgl_retur as tanggal, rp.no_retur as kode_trans, rp.no_order, 'RETUR DARI GUDANG' as jenis_trans, 'pakan' as jenis from retur_pakan rp where rp.tgl_retur <= '".$_date."' and rp.jenis_retur = 'opkg'
                    
                            union all
                    
                            select ap.id as tbl_id, 'adjout_voadip' as tbl_name, ap.tanggal, ap.kode as kode_trans, ap.kode as no_order, 'ADJUSTMENT OUT' as jenis_trans, 'pakan' as jenis from adjout_pakan ap where ap.tanggal <= '".$_date."'
                        ) tbl
                        on
                            dst.kode_trans = tbl.no_order and
                            dst.jenis_barang = tbl.jenis
                    left join
                        (
                            select
                                dj.tbl_id,
                                dj.ref_kode,
                                dj.tbl_name,
                                case
                                    when dj.coa_asal = '12030.000' then
                                        dj.nominal
                                    else
                                        0-dj.nominal
                                end as nominal,
                                'pakan' as jenis_barang
                            from det_jurnal dj 
                            where 
                                dj.tanggal <= '".$_date."' and (dj.coa_asal = '12030.000' or dj.coa_tujuan = '12030.000')
                                
                            union all
                            
                            select
                                dj.tbl_id,
                                dj.ref_kode,
                                dj.tbl_name,
                                case
                                    when dj.coa_asal = '12050.000' then
                                        dj.nominal
                                    else
                                        0-dj.nominal
                                end as nominal,
                                'voadip' as jenis_barang
                            from det_jurnal dj 
                            where 
                                dj.tanggal <= '".$_date."' and (dj.coa_asal = '12050.000' or dj.coa_tujuan = '12050.000')
                        ) dj
                        on
                            (dj.ref_kode is null and dj.tbl_name = tbl.tbl_name and dj.tbl_id = cast(tbl.tbl_id as varchar(50))) or
                            (dj.ref_kode is not null and dj.jenis_barang = dst.jenis_barang and dj.ref_kode = cast(dst.kode_trans as varchar(50)))
                    where
                        dst.kode_trans is not null
                ) data
                group by
                    data.kode_stok,
                    data.tanggal,
                    data.kode_gudang,
                    data.jumlah,
                    data.kode_barang,
                    data.total,
                    data.tbl_id,
                    data.tbl_name
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
            group by
                data.kode_stok,
                data.tanggal,
                data.kode_gudang,
                data.kode_barang,
                gdg.nama,
                brg.nama
            having
                sum(data.jml_debet) - sum(data.jml_kredit) <> 0 or
                sum(data.debet) - sum(data.kredit) <> 0
            order by
                data.kode_gudang asc,
                brg.nama asc
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

        // cetak_r( $data, 1 );

        $content['data'] = $data;
        $html = $this->load->view('report/posisi_stok_accounting/list', $content, TRUE);

        echo $html;
    }
}