<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class KartuHutangRingkas extends Public_Controller {

    private $pathView = 'report/kartu_hutang_ringkas/';
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
                "assets/report/kartu_hutang_ringkas/js/kartu-hutang-ringkas.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/kartu_hutang_ringkas/css/kartu-hutang-ringkas.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['title_menu'] = 'Laporan Kartu Hutang Ringkas';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getData() {
        $params = $this->input->get('params');

        $bulan = $params['bulan'];
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

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.*,
                supl.nama as nama_supplier
            from (
                select
                    d.supplier,
                    isnull(sum(d.saldo_awal), 0) as saldo_awal,
                    isnull(sum(d.debet), 0) as debet,
                    isnull(sum(d.kredit), 0) as kredit,
                    (isnull(sum(d.saldo_awal), 0)+isnull(sum(d.debet), 0))-isnull(sum(d.kredit), 0) as saldo_akhir
                from
                (
                    /* SALDO AWAL */
                    select 
                        inv.supplier,
                        sum( (inv.total+(isnull(byr.dn, 0))) - (isnull(byr.cn, 0)+isnull(byr.potongan, 0)+isnull(byr.uang_muka, 0)+isnull(byr.transfer, 0)+isnull(byr.saldo, 0)) ) as saldo_awal,
                        0 as debet,
                        0 as kredit
                    from (
                        select kpd.nomor, kpd.supplier, kpd.total from konfirmasi_pembayaran_doc kpd
                        where
                            kpd.tgl_bayar < '".$start_date."'
        
                        union all
        
                        select kpp.nomor, kpp.supplier, kpp.total from konfirmasi_pembayaran_pakan kpp
                        where
                            kpp.tgl_bayar < '".$start_date."'
        
                        union all
    
                        select kpop.nomor, kpop.ekspedisi_id as supplier, kpop.total from konfirmasi_pembayaran_oa_pakan kpop
                        where
                            kpop.tgl_bayar < '".$start_date."'
        
                        union all
        
                        select kpv.nomor, kpv.supplier, kpv.total from konfirmasi_pembayaran_voadip kpv
                        where
                            kpv.tgl_bayar < '".$start_date."'
        
                        union all
        
                        select kpp.nomor, kpp.mitra as supplier, kpp.total from konfirmasi_pembayaran_peternak kpp
                        where
                            kpp.tgl_bayar < '".$start_date."'
        
                        union all
        
                        select op.no_order as nomor, op.supplier, op.total from order_peralatan op
                        where
                            op.tgl_order < '".$start_date."'

                        union all

                        select
                            c.nomor,
                            case
                                when (c.supplier is not null and c.supplier <> '') then
                                    c.supplier
                                when (c.mitra is not null and c.mitra <> '') then
                                    c.mitra
                            end as supplier,
                            0 - ((c.tot_cn - isnull(rpc.pakai, 0))) as total
                        from cn c
                        left join
                            (
                                select
                                    sum(isnull(pakai, 0)) as pakai, id_cn
                                from
                                (
                                    select sum(rpc.pakai) as pakai, rpc.id_cn from realisasi_pembayaran_cn rpc
                                    left join
                                        realisasi_pembayaran rp
                                        on
                                            rpc.id_header = rp.id
                                    where
                                        rp.tgl_bayar < '".$start_date."'
                                    group by 
                                        rpc.id_cn

                                    union all

                                    select sum(bpc.pakai) as pakai, bpc.id_cn from bayar_peralatan_cn bpc
                                    left join
                                        bayar_peralatan bp
                                        on
                                            bpc.id_header = bp.id
                                    where
                                        bp.tgl_bayar < '".$start_date."'
                                    group by 
                                        bpc.id_cn

                                    union all

                                    select sum(ppc.pakai) as pakai, ppc.id_cn from pembayaran_pelanggan_cn ppc
                                    left join
                                        pembayaran_pelanggan pp
                                        on
                                            ppc.id_header = pp.id
                                    where
                                        pp.tgl_bayar < '".$start_date."'
                                    group by 
                                        ppc.id_cn
                                ) rpc
                                group by
                                    rpc.id_cn
                            ) rpc
                            on
                                c.id = rpc.id_cn
                        where 
                            c.tanggal < '".$start_date."' and
                            c.tot_cn > isnull(rpc.pakai, 0) and
                            ((c.supplier is not null and c.supplier <> '') or (c.mitra is not null and c.mitra <> ''))

                        union all

                        select
                            d.nomor,
                            case
                                when (d.supplier is not null and d.supplier <> '') then
                                    d.supplier
                                when (d.mitra is not null and d.mitra <> '') then
                                    d.mitra
                            end as supplier,
                            (d.tot_dn - isnull(rpd.pakai, 0)) as total
                        from dn d
                        left join
                            (
                                select
                                    sum(isnull(pakai, 0)) as pakai, id_dn
                                from
                                (
                                    select sum(rpd.pakai) as pakai, rpd.id_dn from realisasi_pembayaran_dn rpd
                                    left join
                                        realisasi_pembayaran rp
                                        on
                                            rpd.id_header = rp.id
                                    where
                                        rp.tgl_bayar < '".$start_date."'
                                    group by 
                                        rpd.id_dn

                                    union all

                                    select sum(bpd.pakai) as pakai, bpd.id_dn from bayar_peralatan_dn bpd
                                    left join
                                        bayar_peralatan bp
                                        on
                                            bpd.id_header = bp.id
                                    where
                                        bp.tgl_bayar < '".$start_date."'
                                    group by 
                                        bpd.id_dn

                                    union all

                                    select sum(ppd.pakai) as pakai, ppd.id_dn from pembayaran_pelanggan_dn ppd
                                    left join
                                        pembayaran_pelanggan pp
                                        on
                                            ppd.id_header = pp.id
                                    where
                                        pp.tgl_bayar < '".$start_date."'
                                    group by 
                                        ppd.id_dn
                                ) rpd
                                group by
                                    rpd.id_dn
                            ) rpd
                            on
                                d.id = rpd.id_dn
                        where 
                            d.tanggal < '".$start_date."' and
                            d.tot_dn > isnull(rpd.pakai, 0) and
                            ((d.supplier is not null and d.supplier <> '') or (d.mitra is not null and d.mitra <> ''))
                    ) inv
                    left join
                        (
                            select
                                byr.nomor, 
                                sum(byr.cn) as cn, 
                                sum(byr.dn) as dn, 
                                sum(byr.potongan) as potongan, 
                                sum(byr.uang_muka) as uang_muka, 
                                sum(byr.transfer) as transfer, 
                                sum(byr.saldo) as saldo 
                            from
                            (
                                select 
                                    rpd.no_bayar as nomor, 
                                    sum(rpdcd.nominal) as cn, 
                                    0 as dn, 
                                    0 as potongan, 
                                    0 as uang_muka, 
                                    0 as transfer, 
                                    0 as saldo 
                                from realisasi_pembayaran_det_cn_dn rpdcd
                                left join
                                    realisasi_pembayaran_det rpd
                                    on
                                        rpdcd.id_header = rpd.id
                                left join
                                    realisasi_pembayaran rp
                                    on
                                        rpd.id_header = rp.id
                                left join
                                    (
                                        select nomor, tanggal from cn c
                                        union all
                                        select nomor, tanggal from dn d
                                    ) cn_dn
                                    on
                                        rpdcd.nomor_cn_dn = cn_dn.nomor
                                where
                                    rpdcd.nomor_cn_dn like '%CN%' and
                                    rp.tgl_bayar < '".$start_date."' and
                                    cn_dn.tanggal < '".$start_date."'
                                group by
                                    rpd.no_bayar

                                union all

                                select 
                                    rpd.no_bayar as nomor, 
                                    0 as cn, 
                                    sum(rpdcd.nominal) as dn, 
                                    0 as potongan, 
                                    0 as uang_muka, 
                                    0 as transfer, 
                                    0 as saldo 
                                from realisasi_pembayaran_det_cn_dn rpdcd
                                left join
                                    realisasi_pembayaran_det rpd
                                    on
                                        rpdcd.id_header = rpd.id
                                left join
                                    realisasi_pembayaran rp
                                    on
                                        rpd.id_header = rp.id
                                left join
                                    (
                                        select nomor, tanggal from cn c
                                        union all
                                        select nomor, tanggal from dn d
                                    ) cn_dn
                                    on
                                        rpdcd.nomor_cn_dn = cn_dn.nomor
                                where
                                    rpdcd.nomor_cn_dn like '%DN%' and
                                    rp.tgl_bayar < '".$start_date."' and
                                    cn_dn.tanggal < '".$start_date."'
                                group by
                                    rpd.no_bayar
                                
                                union all

                                select 
                                    rpd.no_bayar as nomor, 
                                    0 as cn, 
                                    0 as dn, 
                                    sum(rpd.potongan) as potongan, 
                                    sum(rpd.uang_muka) as uang_muka, 
                                    sum(rpd.transfer) as transfer, 
                                    0 as saldo 
                                from realisasi_pembayaran_det rpd
                                left join
                                    realisasi_pembayaran rp
                                    on
                                        rpd.id_header = rp.id
                                where
                                    rp.tgl_bayar < '".$start_date."'
                                group by
                                    rpd.no_bayar

                                union all

                                select 
                                    bp.no_order as nomor, 
                                    sum(bpc.pakai) as cn, 
                                    0 as dn, 
                                    0 as potongan, 
                                    0 as uang_muka, 
                                    0 as transfer, 
                                    0 as saldo 
                                from bayar_peralatan_cn bpc
                                left join
                                    bayar_peralatan bp
                                    on
                                        bpc.id_header = bp.id
                                left join
                                    cn c
                                    on
                                        bpc.id_cn = c.id
                                where
                                    bp.tgl_bayar < '".$start_date."' and
                                    c.tanggal < '".$start_date."'
                                group by
                                    bp.no_order

                                union all

                                select 
                                    bp.no_order as nomor, 
                                    0 as cn, 
                                    sum(bpd.pakai) as dn, 
                                    0 as potongan, 
                                    0 as uang_muka, 
                                    0 as transfer, 
                                    0 as saldo 
                                from bayar_peralatan_dn bpd
                                left join
                                    bayar_peralatan bp
                                    on
                                        bpd.id_header = bp.id
                                left join
                                    dn d
                                    on
                                        bpd.id_dn = d.id
                                where
                                    bp.tgl_bayar < '".$start_date."' and
                                    d.tanggal < '".$start_date."'
                                group by
                                    bp.no_order

                                union all

                                select 
                                    bp.no_order as nomor, 
                                    0 as cn, 
                                    0 as dn, 
                                    0 as potongan, 
                                    0 as uang_muka, 
                                    sum(bp.jml_bayar) as transfer, 
                                    sum(bp.saldo) as saldo 
                                from bayar_peralatan bp
                                where
                                    bp.tgl_bayar < '".$start_date."'
                                group by
                                    bp.no_order

                                /*
                                select rpd.no_bayar as nomor, sum(rpd.cn) as cn, sum(rpd.dn) as dn, sum(rpd.potongan) as potongan, sum(rpd.uang_muka) as uang_muka, sum(rpd.transfer) as transfer, 0 as saldo from realisasi_pembayaran_det rpd
                                left join
                                    realisasi_pembayaran rp
                                    on
                                        rpd.id_header = rp.id
                                where
                                    rp.tgl_bayar < '".$start_date."'
                                group by
                                    rpd.no_bayar
            
                                union all
            
                                select bp.no_order as nomor, sum(bp.tot_cn) as cn, sum(bp.tot_dn) as dn, 0 as potongan, 0 as uang_muka, sum(bp.jml_bayar) as transfer, sum(bp.saldo) as saldo from bayar_peralatan bp
                                where
                                    bp.tgl_bayar < '".$start_date."'
                                group by
                                    bp.no_order
                                */
                            ) byr
                            group by
                                byr.nomor
                        ) byr
                        on
                            inv.nomor = byr.nomor
                    group by
                        inv.supplier
                    /* END - SALDO AWAL */
    
                    union all
    
                    /* TRANSAKSI DI BULAN ITU */
                    select 
                        inv.supplier, 
                        0 as saldo_awal,
                        sum(inv.total) as debet,
                        0 as kredit
                    from (
                        select kpd.tgl_bayar as tanggal, kpd.nomor, kpd.supplier, kpdd.total, td.no_bbm as kode_trans from konfirmasi_pembayaran_doc_det kpdd
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
                        where
                            kpd.tgl_bayar between '".$start_date."' and '".$end_date."'
        
                        union all
        
                        select kpp.tgl_bayar as tanggal, kpp.nomor, kpp.supplier, kppd.total, tp.no_bbm as kode_trans from konfirmasi_pembayaran_pakan_det kppd
                        left join
                            konfirmasi_pembayaran_pakan kpp
                            on
                                kppd.id_header = kpp.id
                        left join
                            kirim_pakan kp
                            on
                                kppd.no_order = kp.no_order
                        left join
                            terima_pakan tp
                            on
                                kp.id = tp.id_kirim_pakan
                        where
                            kpp.tgl_bayar between '".$start_date."' and '".$end_date."'
        
                        union all
    
                        select kpop.tgl_bayar as tanggal, kpop.nomor, kpop.ekspedisi_id as supplier, kpopd.total, tp.no_bbm as kode_trans from konfirmasi_pembayaran_oa_pakan_det kpopd
                        left join
                            konfirmasi_pembayaran_oa_pakan kpop
                            on
                                kpopd.id_header = kpop.id
                        left join
                            kirim_pakan kp
                            on
                                kpopd.no_sj = kp.no_sj
                        left join
                            terima_pakan tp
                            on
                                kp.id = tp.id_kirim_pakan
                        where
                            kpop.tgl_bayar between '".$start_date."' and '".$end_date."'
        
                        union all
        
                        select kpv.tgl_bayar as tanggal, kpv.nomor, kpv.supplier, kpvd.total, tv.no_bbm as kode_trans from konfirmasi_pembayaran_voadip_det kpvd
                        left join
                            konfirmasi_pembayaran_voadip kpv
                            on
                                kpvd.id_header = kpv.id
                        left join
                            kirim_voadip kv
                            on
                                kpvd.no_order = kv.no_order
                        left join
                            terima_voadip tv
                            on
                                kv.id = tv.id_kirim_voadip
                        where
                            kpv.tgl_bayar between '".$start_date."' and '".$end_date."'
        
                        union all
        
                        select kpp.tgl_bayar as tanggal, kpp.nomor, kpp.mitra as supplier, kppd.sub_total as total, rhpp.invoice as kode_trans from konfirmasi_pembayaran_peternak_det kppd
                        left join
                            konfirmasi_pembayaran_peternak kpp
                            on
                                kppd.id_header = kpp.id
                        left join
                            (
                                select r.id, r.invoice, 'RHPP' as jenis_rhpp from rhpp r where r.jenis = 'rhpp_plasma' and not exists(select * from rhpp_group_noreg rgn where rgn.noreg = r.noreg)
    
                                union all
    
                                select rg.id, rg.invoice, 'RHPP GROUP' as jenis_rhpp from rhpp_group rg where rg.jenis = 'rhpp_plasma'
                            ) rhpp
                            on
                                kppd.id_trans = rhpp.id and
                                kppd.jenis = rhpp.jenis_rhpp
                        where
                            kpp.tgl_bayar between '".$start_date."' and '".$end_date."'
        
                        union all
        
                        select op.tgl_order as tanggal, op.no_order as nomor, op.supplier, op.total, op.no_order as kode_trans from order_peralatan op
                        where
                            op.tgl_order between '".$start_date."' and '".$end_date."'
    
                        union all

                        select
                            d.tanggal,
                            d.nomor,
                            case
                                when (d.supplier is not null and d.supplier <> '') then
                                    d.supplier
                                when (d.mitra is not null and d.mitra <> '') then
                                    d.mitra
                            end as supplier,
                            d.tot_dn as total,
                            d.nomor as kode_trans
                        from dn d
                        where 
                            d.tanggal between '".$start_date."' and '".$end_date."' and
                            ((d.supplier is not null and d.supplier <> '') or (d.mitra is not null and d.mitra <> ''))
                    ) inv
                    group by
                        inv.supplier
                    /* END - TRANSAKSI DI BULAN ITU */
    
                    union all
    
                    select
                        byr.supplier,
                        0 as saldo_awal,
                        0 as debet,
                        sum(byr.kredit) as kredit
                    from
                    (
                        /*
                        select 
                            c.tanggal as tanggal,
                            case
                                when rp.supplier is not null and rp.supplier <> '' then
                                    rp.supplier
                                when rp.peternak is not null and rp.peternak <> '' then
                                    rp.peternak
                                when rp.ekspedisi is not null and rp.ekspedisi <> '' then
                                    rp.ekspedisi
                            end as supplier,
                            rpdcd.nomor_cn_dn as nomor,
                            0 as debet,
                            sum(rpd.cn) as kredit,
                            rpdcd.nomor_cn_dn as kode_trans
                        from realisasi_pembayaran_det_cn_dn rpdcd
                        left join
                            realisasi_pembayaran_det rpd
                            on
                                rpdcd.id_header = rpd.id
                        left join
                            realisasi_pembayaran rp
                            on
                                rpd.id_header = rp.id
                        left join
                            cn c
                            on
                                rpdcd.nomor_cn_dn = c.nomor
                        where
                            rpdcd.nomor_cn_dn like 'CN%' and
                            c.tanggal between '".$start_date."' and '".$end_date."' and
                            (c.nomor is not null and c.nomor <> '')
                        group by
                            c.tanggal,
                            rp.supplier,
                            rp.peternak,
                            rp.ekspedisi,
                            rpdcd.nomor_cn_dn

                        union all
                        */

                        select 
                            rp.tgl_bayar as tanggal,
                            case
                                when rp.supplier is not null and rp.supplier <> '' then
                                    rp.supplier
                                when rp.peternak is not null and rp.peternak <> '' then
                                    rp.peternak
                                when rp.ekspedisi is not null and rp.ekspedisi <> '' then
                                    rp.ekspedisi
                            end as supplier,
                            rpd.no_bayar as nomor,
                            0 as debet,
                            sum(rpd.potongan+rpd.uang_muka+rpd.transfer) as kredit,
                            rpd.no_bayar as kode_trans
                        from realisasi_pembayaran_det rpd
                        left join
                            realisasi_pembayaran rp
                            on
                                rpd.id_header = rp.id
                        where
                            rp.tgl_bayar between '".$start_date."' and '".$end_date."'
                        group by
                            rp.tgl_bayar,
                            rp.supplier,
                            rp.peternak,
                            rp.ekspedisi,
                            rpd.no_bayar

                        /*
                        union all

                        select bp.tgl_bayar as tanggal, op.supplier, bp.no_order as nomor, 0 as debet, sum(bpc.pakai) as kredit, c.nomor as kode_trans from bayar_peralatan_cn bpc
                        left join
                            bayar_peralatan bp
                            on
                                bpc.id_header = bp.id
                        left join
                            order_peralatan op
                            on
                                op.no_order = bp.no_order
                        left join
                            cn c
                            on
                                bpc.id_cn = c.id
                        where
                            bp.tgl_bayar between '".$start_date."' and '".$end_date."'
                        group by
                            bp.tgl_bayar,
                            op.supplier,
                            bp.no_order,
                            c.nomor
                        */

                        union all

                        select bp.tgl_bayar as tanggal, op.supplier, bp.no_order as nomor, 0 as debet, sum(bp.jml_bayar+bp.saldo) as kredit, bp.no_faktur as kode_trans from bayar_peralatan bp
                        left join
                            order_peralatan op
                            on
                                op.no_order = bp.no_order
                        where
                            bp.tgl_bayar between '".$start_date."' and '".$end_date."'
                        group by
                            bp.tgl_bayar,
                            op.supplier,
                            bp.no_order,
                            bp.no_faktur

                        union all

                        select
                            c.tanggal,
                            case
                                when (c.supplier is not null and c.supplier <> '') then
                                    c.supplier
                                when (c.mitra is not null and c.mitra <> '') then
                                    c.mitra
                            end as supplier,
                            c.nomor,
                            0 as debet,
                            c.tot_cn as kredit,
                            c.nomor as kode_trans
                        from cn c
                        where 
                            c.tanggal between '".$start_date."' and '".$end_date."' and
                            ((c.supplier is not null and c.supplier <> '') or (c.mitra is not null and c.mitra <> ''))

                        /*
                        select
                            c.tanggal,
                            case
                                when (c.supplier is not null and c.supplier <> '') then
                                    c.supplier
                                when (c.mitra is not null and c.mitra <> '') then
                                    c.mitra
                            end as supplier,
                            c.nomor,
                            0 as debet,
                            (c.tot_cn - isnull(rpc.pakai, 0)) as kredit,
                            c.nomor as kode_trans
                        from cn c
                        left join
                            (
                                select
                                    sum(isnull(pakai, 0)) as pakai, id_cn
                                from
                                (
                                    select sum(rpc.pakai) as pakai, rpc.id_cn from realisasi_pembayaran_cn rpc
                                    left join
                                        realisasi_pembayaran rp
                                        on
                                            rpc.id_header = rp.id
                                    where
                                        rp.tgl_bayar <= '".$end_date."'
                                    group by 
                                        rpc.id_cn

                                    union all

                                    select sum(bpc.pakai) as pakai, bpc.id_cn from bayar_peralatan_cn bpc
                                    left join
                                        bayar_peralatan bp
                                        on
                                            bpc.id_header = bp.id
                                    where
                                        bp.tgl_bayar <= '".$end_date."'
                                    group by 
                                        bpc.id_cn

                                    union all

                                    select sum(ppc.pakai) as pakai, ppc.id_cn from pembayaran_pelanggan_cn ppc
                                    left join
                                        pembayaran_pelanggan pp
                                        on
                                            ppc.id_header = pp.id
                                    where
                                        pp.tgl_bayar <= '".$end_date."'
                                    group by 
                                        ppc.id_cn
                                ) rpc
                                group by
                                    rpc.id_cn
                            ) rpc
                            on
                                c.id = rpc.id_cn
                        where 
                            c.tanggal between '".$start_date."' and '".$end_date."' and
                            c.tot_cn > isnull(rpc.pakai, 0) and
                            ((c.supplier is not null and c.supplier <> '') and (c.mitra is not null and c.mitra <> ''))
                        */
                    ) byr
                    group by
                        byr.supplier
                ) d
                group by
                    d.supplier
            ) data
            left join
                (
                    select p1.nomor, p1.nama from pelanggan p1
                    right join
                        (select max(id) as id, nomor from pelanggan p where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) p2
                        on
                            p1.id = p2.id

                    union all
                            
                    select e1.nomor, e1.nama from ekspedisi e1
                    right join
                        (select max(id) as id, nomor from ekspedisi e group by nomor) e2
                        on
                            e1.id = e2.id

                    union all

                    select m1.nomor, m1.nama from mitra m1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) m2
                        on
                            m1.id = m2.id
                ) supl
                on
                    supl.nomor = data.supplier
            order by
                data.supplier asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }
}
