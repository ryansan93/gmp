<?php defined('BASEPATH') OR exit('No direct script access allowed');

class LaporanHarianManajemen extends Public_Controller {

    private $url;
    private $pathView = 'report/laporan_harian_manajemen/';

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
                "assets/report/laporan_harian_manajemen/js/laporan-harian-manajemen.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/laporan_harian_manajemen/css/laporan-harian-manajemen.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['title_menu'] = 'Laporan Harian Manajemen';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getData($tanggal)
    {
        $tgl_awal_tahun = substr($tanggal, 0, 4).'-01-01';
        $tgl_prev = prev_date( $tanggal );

        $sql_lr_inti_prev = null;
        if ( $tanggal > $tgl_awal_tahun ) {
            $sql_lr_inti_prev = "
                union all

                /* LABA INTI PREV */
                select
                    0 as saldo_bank,
                    0 as tot_trf,
                    0 as hutang_doc,
                    -- 0 as hutang_pakan,
                    0 as hutang_pakan_jatim,
                    0 as hutang_pakan_jateng,
                    sum(data.lr_inti) as lr_inti_prev,
                    0 as lr_inti,
                    0 as jml_rhpp,
                    0 as jml_box,
                    0 as jml_doc,
                    0 as total_doc,
                    0 as tonase,
                    0 as tot_jual
                from
                (
                    select lr_inti from rhpp r
                    left join
                        tutup_siklus ts
                        on
                            r.id_ts = ts.id
                    where
                        r.jenis = 'rhpp_inti' and
                        not exists (select * from rhpp_group_noreg where noreg = r.noreg) and
                        ts.tgl_tutup between '".$tgl_awal_tahun."' and '".$tgl_prev."'
    
                    union all
    
                    select lr_inti from rhpp_group rg
                    left join
                        rhpp_group_header rgh
                        on
                            rg.id_header = rgh.id
                    where
                        rg.jenis = 'rhpp_inti' and
                        rgh.tgl_submit between '".$tgl_awal_tahun."' and '".$tgl_prev."'
                ) data
                /* END - LABA INTI PREV */
            ";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                sum(data.saldo_bank) as saldo_bank,
                sum(data.tot_trf) as tot_trf,
                sum(data.hutang_doc) as hutang_doc,
                -- sum(data.hutang_pakan) as hutang_pakan,
                sum(data.hutang_pakan_jatim) as hutang_pakan_jatim,
                sum(data.hutang_pakan_jateng) as hutang_pakan_jateng,
                sum(data.lr_inti_prev) as lr_inti_prev,
                sum(data.lr_inti) as lr_inti,
                sum(data.jml_rhpp) as jml_rhpp,
                sum(data.jml_box) as jml_box,
                sum(data.jml_doc) as jml_doc,
                sum(data.total_doc) as total_doc,
                sum(data.tonase) as tonase,
                sum(data.tot_jual) as tot_jual,
                -- isnull(sum(data.hutang_doc), 0) + isnull(sum(data.hutang_pakan), 0) as tot_hutang,
                isnull(sum(data.hutang_doc), 0) + isnull(sum(data.hutang_pakan_jatim), 0) + isnull(sum(data.hutang_pakan_jateng), 0) as tot_hutang,
                isnull(sum(data.lr_inti_prev), 0) + isnull(sum(data.lr_inti), 0) as lr_total,
                case
	                when sum(data.lr_inti) > 0 and sum(data.jml_doc) > 0 then
	                	sum(data.lr_inti) / sum(data.jml_doc)
	                else
	                	0
                end as lr_per_ekor,
                case
	                when sum(data.lr_inti) > 0 and sum(data.tonase) > 0 then
	                	sum(data.lr_inti) / sum(data.tonase)
	                else
	                	0
                end as lr_per_kg,
                case
	                when sum(data.total_doc) > 0 and sum(data.jml_doc) > 0 then
	                	sum(data.total_doc) / sum(data.jml_doc)
	                else
	                	0
                end as hrg_doc,
                case
	                when sum(data.tot_jual) > 0 and sum(data.tonase) > 0 then
	                	sum(data.tot_jual) / sum(data.tonase)
	                else
	                	0
                end as hrg_rata_ayam
            from (
                /* SALDO BANK */
                select 
                    sum(sb.saldo_akhir) as saldo_bank,
                    0 as tot_trf,
                    0 as hutang_doc,
                    -- 0 as hutang_pakan,
                    0 as hutang_pakan_jatim,
                    0 as hutang_pakan_jateng,
                    0 as lr_inti_prev,
                    0 as lr_inti,
                    0 as jml_rhpp,
                    0 as jml_box,
                    0 as jml_doc,
                    0 as total_doc,
                    0 as tonase,
                    0 as tot_jual
                from saldo_bank sb where sb.tanggal = '".$tanggal."'
                /* END - SALDO BANK */

                union all

                /* TRANSFER */
                /*
                select
                    0 as saldo_bank,
                    sum(rp.jml_transfer) as tot_trf,
                    0 as hutang_doc,
                    -- 0 as hutang_pakan,
                    0 as hutang_pakan_jatim,
                    0 as hutang_pakan_jateng,
                    0 as lr_inti_prev,
                    0 as lr_inti,
                    0 as jml_rhpp,
                    0 as jml_box,
                    0 as jml_doc,
                    0 as total_doc,
                    0 as tonase,
                    0 as tot_jual
                from realisasi_pembayaran rp
                where
                    rp.tgl_realisasi = '".$tanggal."'
                */

                select
                    0 as saldo_bank,
                    sum(tbl.nominal) as tot_trf,
                    0 as hutang_doc,
                    -- 0 as hutang_pakan,
                    0 as hutang_pakan_jatim,
                    0 as hutang_pakan_jateng,
                    0 as lr_inti_prev,
                    0 as lr_inti,
                    0 as jml_rhpp,
                    0 as jml_box,
                    0 as jml_doc,
                    0 as total_doc,
                    0 as tonase,
                    0 as tot_jual
                from no_bbk nb
                left join
                    (select * from coa where bank = 1) c 
                    on
                        SUBSTRING(nb.kode, 1, 4) = c.kode
                left join
                    (
                        select 
                            'kk' as tbl_name, 
                            k.no_kk as tbl_id, 
                            k.nilai as nominal,
                            k.tgl_kk as tanggal 
                        from kk k
                        left join
                            coa c
                            on
                                c.coa = k.coa_bank
                        where
                            c.kode = 'BCA2'

                        union all

                        select 
                            'kk' as tbl_name, 
                            k.no_kk as tbl_id, 
                            sum(ki.nilai) as nominal,
                            k.tgl_kk as tanggal 
                        from kkitem ki
                        left join
                            kk k
                            on
    	                        ki.no_kk = k.no_kk
                        left join
                            coa c
                            on
                                c.coa = k.coa_bank
                        where
                            c.kode = 'BCA1' and
                            ki.coa_tujuan <> '27001.000'
                        group by
                            k.no_kk,
                            k.tgl_kk
                        
                        union all
                        
                        select 'realisasi_pembayaran' as tbl_name, nomor as tbl_id, jml_transfer as nominal, tgl_realisasi as tanggal from realisasi_pembayaran
                    ) tbl
                    on
                        nb.tbl_name = tbl.tbl_name and
                        nb.tbl_id = tbl.tbl_id
                where
                    c.kode in ('BCA1', 'BCA2')
                    and tbl.tanggal = '".$tanggal."'
                /* END - TRANSFER */

                union all

                /* HUTANG */
                select
                    0 as saldo_bank,
                    0 as tot_trf,
                    case
                        when sa.jenis = 'DOC' then
                            (isnull(sum(sa.debet), 0) - isnull(sum(sa.kredit), 0))
                        else
                            0
                    end as hutang_doc,
                    /*
                    case
                        when sa.jenis = 'PAKAN' then
                            (isnull(sum(sa.debet), 0) - isnull(sum(sa.kredit), 0))
                        else
                            0
                    end as hutang_pakan,
                    */
                    case
                        when sa.jenis = 'PAKAN' then
                            case
                                when w.prov is null or w.prov like '%jawa timur%' then
                                    (isnull(sum(sa.debet), 0) - isnull(sum(sa.kredit), 0))
                                else
                                    0
                            end
                        else
                            0
                    end as hutang_pakan_jatim,
                    case
                        when sa.jenis = 'PAKAN' then
                            case
                                when w.prov is not null and w.prov not like '%jawa timur%' then
                                    (isnull(sum(sa.debet), 0) - isnull(sum(sa.kredit), 0))
                                else
                                    0
                            end
                        else
                            0
                    end as hutang_pakan_jateng,
                    0 as lr_inti_prev,
                    0 as lr_inti,
                    0 as jml_rhpp,
                    0 as jml_box,
                    0 as jml_doc,
                    0 as total_doc,
                    0 as tonase,
                    0 as tot_jual
                from
                (
                    select
                        data.nomor,
                        data.supplier,
                        sum(data.debet) as debet,
                        sum(data.kredit) as kredit,
                        data.jenis,
                        data.unit
                    from
                    (
                        /* DEBET */
                        /*
                        select
                            kpdd.no_order as nomor,
                            kpd.supplier,
                            kpdd.total as debet,
                            0 as kredit,
                            'DOC' as jenis,
                            kpdd.kode_unit as unit
                        from konfirmasi_pembayaran_doc_det kpdd
                        left join
                            konfirmasi_pembayaran_doc kpd
                            on
                                kpdd.id_header = kpd.id
                        where
                            kpd.tgl_bayar <= '".$tanggal."'

                        union all
                        */
    
                        select
                            od.no_order as nomor,
                            od.supplier,
                            case
                                when cast(od.tgl_submit as date) <= '2025-09-20' then
                                    od.total - isnull(cn.pakai, 0)
                                else
                                    case
                                        when cast(od.tgl_submit as date) < '2026-01-01' then
                                            (od.total - (od.total * (0.25/100))) - isnull(cn.pakai, 0)
                                            -- ((od.total - isnull(cn.pakai, 0)) - ((od.total - isnull(cn.pakai, 0)) * (0.25/100)))
                                        else
                                            ((od.total - isnull(cn.pakai, 0)) - ((od.total - isnull(cn.pakai, 0)) * (0.25/100)))
                                    end
                            end as debet,
                            -- od.total as debet,
                            0 as kredit,
                            'DOC' as jenis,
                            SUBSTRING(od.no_order, 5, 3) as unit
                        from
                        (
                            select od1.* from order_doc od1
                            right join
                                (select max(id) as id, no_order from order_doc group by no_order) od2
                                on
                                    od1.id = od2.id
                        ) od
                        left join
                            (
                                select isnull(sum(cpd.pakai), 0) as pakai, kpdd.no_order from cn_post_det cpd 
                                left join
                                    cn_post cp
                                    on
                                        cpd.id_header = cp.id
                                left join
                                    (
                                        select kpdd.*, kpd.nomor from konfirmasi_pembayaran_doc_det kpdd
                                        left join
                                            konfirmasi_pembayaran_doc kpd 
                                            on
                                                kpdd.id_header = kpd.id
                                    ) kpdd 
                                    on
                                        kpdd.nomor = cpd.nomor
                                where
                                    kpdd.no_order is not null
                                group by
                                    kpdd.no_order
                            ) cn
                            on
                                od.no_order = cn.no_order
                        where
                            cast(od.tgl_submit as date) <= '".$tanggal."' 
                            -- and not exists (select * from konfirmasi_pembayaran_doc_det where no_order = od.no_order)
    
                        union all
    
                        select 
                            kppd.no_order as nomor, 	
                            kpp.supplier, 
                            kppd.total as debet,
                            0 as kredit,
                            'PAKAN' as jenis,
                            kppd.kode_unit as unit
                        from konfirmasi_pembayaran_pakan_det kppd
                        left join
                            konfirmasi_pembayaran_pakan kpp
                            on
                                kppd.id_header = kpp.id
                        left join
                            order_pakan op
                            on
                                kppd.no_order = op.no_order
                        where
                            cast(op.tgl_trans as date) <= '".$tanggal."'
                        -- where kpp.tgl_bayar <= '".$tanggal."'

                        union all
    
                        select
                            op.no_order as nomor, 	
                            op.supplier, 
                            sum(opd.total) as debet,
                            0 as kredit,
                            'PAKAN' as jenis,
                            SUBSTRING(op.no_order , 5, 3)  as unit
                        from order_pakan_detail opd 
                        left join
                            order_pakan op 
                            on
                                opd.id_header = op.id 
                        where
                            op.tgl_trans <= '".$tanggal."' and
                            not exists (select * from konfirmasi_pembayaran_pakan_det where no_order = op.no_order)
                        group by
                            op.no_order, 	
                            op.supplier
    
                        union all
    
                        select
                            d.nomor,
                            d.supplier,
                            d.tot_dn as debet,
                            0 as kredit,
                            d.jenis_dn as jenis,
                            null as unit
                        from dn d
                        where
                            d.tanggal <= '".$tanggal."'
                        /* END - DEBET */
    
                        union all
    
                        /* KREDIT */
                        select
                            c.nomor,
                            c.supplier,
                            0 as debet,
                            c.tot_cn as kredit,
                            case
                                when c.jenis_cn = 'PKN' then
                                    'PAKAN'
                                else
                                    c.jenis_cn 
                            end as jenis,
                            null as unit
                        from cn c
                        where
                            c.tanggal <= '".$tanggal."' and
                            c.jenis_cn <> 'DOC'
                        
                        /*
                        select
                            konfir.no_order as nomor,
                            konfir.supplier,
                            0 as debet,
                            sum(cpd.pakai) as kredit,
                            case
                                when cp.jenis_cn = 'PKN' then
                                    'PAKAN'
                                else
                                    cp.jenis_cn 
                            end as jenis,
                            konfir.unit as unit
                        from cn_post_det cpd
                        left join
                            cn_post cp 
                            on
                                cpd.id_header = cp.id
                        left join
                            (
                                select kpdd.no_order, kpd.nomor, kpd.supplier, SUBSTRING(kpdd.no_order, 5, 3) as unit from konfirmasi_pembayaran_doc_det kpdd
                                left join
                                    konfirmasi_pembayaran_doc kpd 
                                    on
                                        kpdd.id_header = kpd.id
                                        
                                union all
                                
                                select kppd.no_order, kpp.nomor, kpp.supplier, SUBSTRING(kppd.no_order , 5, 3)  as unit from konfirmasi_pembayaran_pakan_det kppd
                                left join
                                    konfirmasi_pembayaran_pakan kpp
                                    on
                                        kppd.id_header = kpp.id
                            ) konfir
                            on
                                cpd.nomor = konfir.nomor
                        where
                            cp.tanggal <= '".$tanggal."'
                        group by
                            konfir.no_order,
                            konfir.supplier,
                            cp.jenis_cn,
                            konfir.unit
                        */
    
                        union all
                
                        select
                            bp.no_faktur,
                            op.supplier,
                            0 as debet,
                            bp.jml_bayar as kredit,
                            'PERALATAN' as jenis,
                            op.unit 
                        from bayar_peralatan bp 
                        left join
                            order_peralatan op 
                            on
                                op.no_order = bp.no_order 
                        where
                            bp.tgl_realisasi <= '".$tanggal."'
    
                        union all
    
                        select 
                            konfir.no_order as nomor,
                            rp.supplier,
                            0 as debet,
                            case
                                when rpd.transaksi = 'DOC' then
                                    case
                                        when konfir.tanggal <= '2025-09-20' then
                                            rpd.transfer
                                        else
                                            -- rpd.transfer+konfir.pph
                                            rpd.transfer
                                    end
                                else
                                    rpd.transfer
                            end as kredit,
                            rpd.transaksi as jenis,
                            konfir.kode_unit as unit
                        from realisasi_pembayaran_det rpd
                        left join
                            realisasi_pembayaran rp
                            on
                                rpd.id_header = rp.id
                        left join
                            (
                                select kpdd.kode_unit, kpd.nomor, kpdd.no_order, kpd.tgl_bayar as tanggal, (kpdd.total * (0.25/100)) as pph from konfirmasi_pembayaran_doc_det kpdd 
                                left join
                                    konfirmasi_pembayaran_doc kpd 
                                    on
                                        kpdd.id_header = kpd.id
                                left join
                                    (
                                        select td1.* from terima_doc td1
                                        right join
                                            (select max(id) as id, no_order from terima_doc group by no_order) td2
                                            on
                                                td1.id = td2.id
                                    ) td
                                    on
                                        td.no_order = kpdd.no_order
                                group by
                                    kpdd.kode_unit, kpd.nomor, kpdd.no_order, kpd.tgl_bayar, kpdd.total
                                    
                                union all
                                
                                select kppd.kode_unit, kpp.nomor, kppd.no_order, null as tanggal, 0 as pph from konfirmasi_pembayaran_pakan_det kppd 
                                left join
                                    konfirmasi_pembayaran_pakan kpp 
                                    on
                                        kppd.id_header = kpp.id
                                group by
                                    kppd.kode_unit, kpp.nomor, kppd.no_order
                            ) konfir
                            on
                                rpd.no_bayar = konfir.nomor
                        where
                            rp.tgl_bayar <= '".$tanggal."'
                        /* END - KREDIT */
                    ) data
                    group by
                        data.nomor,
                        data.supplier,
                        data.jenis,
                        data.unit
                ) sa
                left join
                    (
                        select w1.kode, w2.nama as prov from wilayah w1
                        left join
                            wilayah w2 
                            on
                                w1.induk = w2.id
                        where 
                            w1.kode is not null
                        group by
                            w1.kode, w2.nama
                    ) w
                    on
                        sa.unit = w.kode
                where
                    sa.jenis in ('DOC', 'PAKAN')
                group by
                    sa.jenis
                    , w.prov
                /* END - HUTANG */

                ".$sql_lr_inti_prev."

                union all

                /* LABA INTI */
                select
                    0 as saldo_bank,
                    0 as tot_trf,
                    0 as hutang_doc,
                    -- 0 as hutang_pakan,
                    0 as hutang_pakan_jatim,
                    0 as hutang_pakan_jateng,
                    0 as lr_inti_prev,
                    sum(data.lr_inti) as lr_inti,
                    count(data.lr_inti) as jml_rhpp,
                    sum(data.box) as jml_box,
                    sum(data.jumlah) as jml_doc,
                    sum(data.tot_doc) as total_doc,
                    -- sum(data.tonase) as tonase,
                    -- sum(data.tot_jual) as tot_jual,
                    sum(data.tonase_non_afkir) as tonase,
                    sum(data.tot_jual_non_afkir) as tot_jual
                from
                (
                    select r.lr_inti, rd.box, rd.jumlah, rd.total as tot_doc, rp.tonase, rp.total as tot_jual, rp_non_afkir.tonase as tonase_non_afkir, rp_non_afkir.total as tot_jual_non_afkir
                    from rhpp r
                    left join
                        (select id_header, sum(box) as box, sum(jumlah) as jumlah, sum(total) as total from rhpp_doc group by id_header) rd
                        on
                            r.id = rd.id_header
                    left join
                        (select id_header, sum(ekor) as ekor, sum(tonase) as tonase, sum(total_pasar) as total from rhpp_penjualan group by id_header) rp
                        on
                            r.id = rp.id_header
                    left join
                        (select id_header, sum(ekor) as ekor, sum(tonase) as tonase, sum(total_pasar) as total from rhpp_penjualan where jenis_ayam <> 'a' group by id_header) rp_non_afkir
                        on
                            r.id = rp_non_afkir.id_header
                    left join
                        tutup_siklus ts
                        on
                            r.id_ts = ts.id
                    where
                        r.jenis = 'rhpp_inti' and
                        not exists (select * from rhpp_group_noreg where noreg = r.noreg) and
                        ts.tgl_tutup = '".$tanggal."'
    
                    union all
    
                    select rg.lr_inti, rgd.box, rgd.jumlah, rgd.total as tot_doc, rgp.tonase, rgp.total as tot_jual, rgp_non_afkir.tonase as tonase_non_afkir, rgp_non_afkir.total as tot_jual_non_afkir
                    from rhpp_group rg
                    left join
                        (select id_header, sum(box) as box, sum(jumlah) as jumlah, sum(total) as total from rhpp_group_doc group by id_header) rgd
                        on
                            rg.id = rgd.id_header
                    left join
                        (select id_header, sum(ekor) as ekor, sum(tonase) as tonase, sum(total_pasar) as total from rhpp_group_penjualan group by id_header) rgp
                        on
                            rg.id = rgp.id_header
                    left join
                        (select id_header, sum(ekor) as ekor, sum(tonase) as tonase, sum(total_pasar) as total from rhpp_group_penjualan where jenis_ayam <> 'a' group by id_header) rgp_non_afkir
                        on
                            rg.id = rgp_non_afkir.id_header
                    left join
                        rhpp_group_header rgh
                        on
                            rg.id_header = rgh.id
                    where
                        rg.jenis = 'rhpp_inti' and
                        rgh.tgl_submit = '".$tanggal."'
                ) data
                /* END - LABA INTI */
            ) data
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        // cetak_r( $data, 1 );

        return $data;
    }

    public function getLists()
    {
        $params = $this->input->post('params');

        try {                
            $tanggal = $params['tanggal'];

            $data = $this->getData( $tanggal )[0];

            $content['tanggal'] = $tanggal;
            $content['data'] = $data;
            $html = $this->load->view($this->pathView.'list', $content, TRUE);

            $this->result['status'] = 1;
            $this->result['html'] = $html;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
    
    public function tes() {
        $tanggal = '2025-12-29';
        $tgl_awal_tahun = substr($tanggal, 0, 4);

        cetak_r( $tgl_awal_tahun );
    }
}