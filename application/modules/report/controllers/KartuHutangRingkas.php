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
            $content['supplier'] = $this->getSupplier();
            $content['jenis'] = $this->getJenis();
            $content['title_menu'] = 'Laporan Kartu Hutang Ringkas';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getJenis() {
        $arr = array('ekspedisi', 'plasma', 'supplier');

        return $arr;
    }

    public function getSupplier() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from 
            (
                select p1.nomor, p1.nama, 'supplier' as tipe from pelanggan p1
                right join
                    (select max(id) as id, nomor from pelanggan p where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) p2
                    on
                        p1.id = p2.id

                union all
                        
                select e1.nomor, e1.nama, 'ekspedisi' as tipe from ekspedisi e1
                right join
                    (select max(id) as id, nomor from ekspedisi e group by nomor) e2
                    on
                        e1.id = e2.id

                union all

                select m1.nomor, m1.nama, 'plasma' as tipe from mitra m1
                right join
                    (select max(id) as id, nomor from mitra group by nomor) m2
                    on
                        m1.id = m2.id
                where
                    m1.mstatus = 1
            ) supl
            order by
                supl.tipe asc,
                supl.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getData() {
        $params = $this->input->get('params');

        $bulan = $params['bulan'];
        $tahun = substr($params['tahun'], 0, 4);
        $jenis = $params['jenis'];
        $supplier = $params['supplier'];

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

        $where = null;
        if ( $jenis != 'all' ) {
            if ( empty( $where ) ) {
                $where = "where supl.tipe = '".$jenis."'";
            } else {
                $where .= "and supl.tipe = '".$jenis."'";
            }
        }

        if ( $supplier != 'all' ) {
            if ( empty( $where ) ) {
                $where = "where supl.nomor = '".$supplier."'";
            } else {
                $where .= "and supl.nomor = '".$supplier."'";
            }
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
        select 
        --    data.*
            data.supplier,
            supl.nama as nama_supplier,
        --    data.jenis,
        --    data.unit,
            isnull(sum(data.saldo_awal), 0) as saldo_awal,
            isnull(sum(data.debet), 0) as debet,
            isnull(sum(data.kredit), 0) as kredit,
            isnull(sum(data.saldo_akhir), 0) as saldo_akhir
        from
        (
            select
                _data.supplier,
                _data.jenis,
                _data.unit,
                isnull(sum(_data.saldo_awal), 0) as saldo_awal,
                isnull(sum(_data.debet), 0) as debet,
                isnull(sum(_data.kredit), 0) as kredit,
                (isnull(sum(_data.saldo_awal), 0)+isnull(sum(_data.debet), 0))-isnull(sum(_data.kredit), 0) as saldo_akhir
            from
            (
                /* SALDO AWAL */
                select
                    sa.supplier,
                    (isnull(sum(sa.debet), 0) - isnull(sum(sa.kredit), 0)) as saldo_awal,
                    0 as debet,
                    0 as kredit,
                    sa.jenis,
                    sa.unit
                from
                (
                    /* DEBET */
                    select 
                        kpd.nomor,
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
                        cast(td.datang as date) < '".$start_date."'

                    union all

                    select 
                        kpp.nomor, 	
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
                    where
                        kpp.tgl_bayar < '".$start_date."'

                    union all

                    /* OA PAKAN */
                    select
                        oa.nomor,
                        oa.supplier,
                        sum(oa.total) as debet,
                        0 as kredit,
                        'OA PAKAN' as jenis,
                        null as unit
                    from (
                        select kpop.nomor, kpop.ekspedisi_id as supplier, (kpop.total+kpop.potongan_pph_23) as total from konfirmasi_pembayaran_oa_pakan kpop
                        where
                            kpop.tgl_bayar < '".$start_date."'

                        union all

                        select tp.no_bbm as nomor, kp.ekspedisi_id as supplier, sum(dtp.jumlah)*kp.ongkos_angkut as total from det_terima_pakan dtp
                        left join
                            terima_pakan tp
                            on
                                dtp.id_header = tp.id
                        left join
                            kirim_pakan kp
                            on
                                tp.id_kirim_pakan = kp.id
                        where
                            tp.tgl_terima < '".$start_date."' and
                            kp.jenis_kirim = 'opkg' and
                            not exists (select * from konfirmasi_pembayaran_oa_pakan_det kpopd where no_sj = kp.no_sj)
                        group by
                            tp.no_bbm, kp.ekspedisi_id, kp.ongkos_angkut

                        union all

                        select opp.no_sj as nomor, krm.ekspedisi_id as supplier, opp.ongkos_angkut as total from oa_pindah_pakan opp
                        left join
                            (
                                select kp.no_sj, tp.no_bbm as kode_trans, kp.ekspedisi_id, tp.tgl_terima as tanggal from kirim_pakan kp
                                left join
                                    terima_pakan tp 
                                    on
                                        kp.id = tp.id_kirim_pakan
                                group by
                                    kp.no_sj, tp.no_bbm, kp.ekspedisi_id, tp.tgl_terima
                                
                                union all
                                
                                select no_retur as no_sj, no_retur as kode_trans, ekspedisi_id, tgl_retur as tanggal from retur_pakan rp 
                            ) krm
                            on
                                opp.no_sj = krm.no_sj
                        where
                            krm.tanggal < '".$start_date."' and
                            not exists (select * from konfirmasi_pembayaran_oa_pakan_det kpopd where no_sj = opp.no_sj)
                    ) oa
                    group by
                        oa.nomor,
                        oa.supplier
                    /* END - OA PAKAN */

                    union all

                    select
                        kpv.nomor, 
                        kpv.supplier, 
                        kpv.total as debet,
                        0 as kredit,
                        case
                            when kpv.supplier = '19B004' then
                                'OVK'
                            else
                                'OVK EXTERN'
                        end as jenis,
        --                'OVK' as jenis,
                        null as unit
                    from konfirmasi_pembayaran_voadip kpv
                    where
                        kpv.tgl_bayar < '".$start_date."'

                    union all

                    select
                        kpp.nomor,
                        kpp.mitra as supplier,
                        kpp.total as debet,
                        0 as kredit,
                        'RHPP' as jenis,
                        null as unit
                    from konfirmasi_pembayaran_peternak kpp
                    where
                        kpp.tgl_bayar < '".$start_date."'

                    union all

                    select
                        op.no_order as nomor,
                        op.supplier,
                        op.total as debet,
                        0 as kredit,
                        'PERALATAN' as jenis,
                        op.unit 
                    from order_peralatan op
                    where
                        op.tgl_order < '".$start_date."'

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
                        d.tanggal < '".$start_date."'

                    union all

                    /* INVOICE LEWAT MEMO */
                    select * from (
                        select
                            mi.no_mm as nomor,
                            m.no_supplier as supplier,
                            mi.nilai as debet,
                            0 as kredit,
                            case
                                when mi.coa_asal = '21180.300' then
                                    'OVK EXTERN'
                                when mi.coa_asal = '21174.000' then
                                    'OVK'
                            end as jenis,
                            m.unit
                        from mmitem mi
                        left join
                            mm m
                            on
                                mi.no_mm = m.no_mm
                        where 
                            mi.coa_asal in ('21180.300', '21174.000') and
                            cast(mi.tgl_mm as date) < '".$start_date."'
                    ) inv_mm
                    /* END - INVOICE LEWAT MEMO */
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
                        c.tanggal < '".$start_date."'

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
                        bp.tgl_realisasi < '".$start_date."'

                    union all

                    select 
                        rp.nomor,
                        rp.supplier,
                        0 as debet,
                        case
                            when rpd.transaksi = 'DOC' then
                                case
                                    when konfir.tanggal <= '2025-09-20' then
                                        rpd.transfer
                                    else
                                        rpd.transfer+konfir.pph
                                        -- rpd.transfer
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
                            select kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar as tanggal, (kpdd.total * (0.25/100)) as pph from konfirmasi_pembayaran_doc_det kpdd 
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
                                kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar, kpdd.total
                                
                            union all
                            
                            select kppd.kode_unit, kpp.nomor, null as tanggal, 0 as pph from konfirmasi_pembayaran_pakan_det kppd 
                            left join
                                konfirmasi_pembayaran_pakan kpp 
                                on
                                    kppd.id_header = kpp.id
                            group by
                                kppd.kode_unit, kpp.nomor
                        ) konfir
                        on
                            rpd.no_bayar = konfir.nomor
                    where
                        rp.tgl_bayar < '".$start_date."'

                    union all

                    /* BAYAR LEWAT MEMO */
                    select * from (
                        select
                            mi.no_invoice as nomor,
                            konfir.supplier,
                            0 as debet,
                            mi.nilai as kredit,
                            case
                                when mi.coa_tujuan = '21180.300' then
                                    'OVK EXTERN'
                                when mi.coa_tujuan = '21174.000' then
                                    'OVK'
                            end as jenis,
                            m.unit
                        from mmitem mi
                        left join
                        	mm m
                        	on
                        		mi.no_mm = m.no_mm
                        left join
                            (
                                select nomor, supplier from konfirmasi_pembayaran_voadip group by nomor, supplier

                                union all

                                select nomor, supplier from konfirmasi_pembayaran_pakan group by nomor, supplier

                                union all

                                select nomor, supplier from konfirmasi_pembayaran_doc group by nomor, supplier

                                union all

                                select nomor, ekspedisi_id as supplier from konfirmasi_pembayaran_oa_pakan group by nomor, ekspedisi_id

                                union all

                                select nomor, mitra as supplier from konfirmasi_pembayaran_peternak group by nomor, mitra
                            ) konfir
                            on
                                mi.no_invoice = konfir.nomor
                        where 
                            mi.coa_tujuan in ('21180.300', '21174.000') and
                            cast(mi.tgl_mm as date) between '".$start_date."' and '".$end_date."'
                    ) byr_mm
                    /* END - BAYAR LEWAT MEMO */
                    /* END - KREDIT */
                ) sa
                group by
                    sa.supplier,
                    sa.jenis,
                    sa.unit
                /* END - SALDO AWAL */

                union all

                /* TRANSAKSI BULAN BERJALAN */
                select
                    trans.supplier,
                    0 as saldo_awal,
                    isnull(sum(trans.debet), 0) as debet,
                    isnull(sum(trans.kredit), 0) as kredit,
                    trans.jenis,
                    trans.unit
                from
                (
                    /* DEBET */
        --        	select
        --        	sum(data.debet)
        --        	from
        --        	(
                    select 
                        kpd.nomor,
                        kpd.supplier,
                        kpdd.total as debet,
        --                dj.nominal,
        --                kpdd.total - dj.nominal as selisih,
                        0 as kredit,
                        'DOC' as jenis,
                        kpdd.kode_unit as unit
        --                ,td.no_order
                    from konfirmasi_pembayaran_doc_det kpdd
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
        --            left join
        --            	(select * from det_jurnal where tbl_name = 'terima_doc' and coa_asal = '21180.200' and unit = 'MLG' and tanggal between '".$start_date."' and '".$end_date."') dj
        --            	on
        --            		cast(td.id as varchar(10)) = dj.tbl_id
                    where
                        cast(td.datang as date) between '".$start_date."' and '".$end_date."'
        --                and kpdd.kode_unit = 'LMG'
        --        	) data

                    union all

                    select 
                        kpp.nomor, 	
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
                    where
                        kpp.tgl_bayar between '".$start_date."' and '".$end_date."'

                    union all

                    /* OA PAKAN */
                    select
                        oa.nomor,
                        oa.supplier,
                        sum(oa.total) as debet,
                        0 as kredit,
                        'OA PAKAN' as jenis,
                        null as unit
                    from (
                        select kpop.nomor, kpop.ekspedisi_id as supplier, (kpop.total+kpop.potongan_pph_23) as total from konfirmasi_pembayaran_oa_pakan kpop
                        where
                            kpop.tgl_bayar between '".$start_date."' and '".$end_date."'

                        union all

                        select tp.no_bbm as nomor, kp.ekspedisi_id as supplier, sum(dtp.jumlah)*kp.ongkos_angkut as total from det_terima_pakan dtp
                        left join
                            terima_pakan tp
                            on
                                dtp.id_header = tp.id
                        left join
                            kirim_pakan kp
                            on
                                tp.id_kirim_pakan = kp.id
                        where
                            tp.tgl_terima between '".$start_date."' and '".$end_date."' and
                            kp.jenis_kirim = 'opkg' and
                            not exists (select * from konfirmasi_pembayaran_oa_pakan_det kpopd where no_sj = kp.no_sj)
                        group by
                            tp.no_bbm, kp.ekspedisi_id, kp.ongkos_angkut

                        union all

                        select opp.no_sj as nomor, krm.ekspedisi_id as supplier, opp.ongkos_angkut as total from oa_pindah_pakan opp
                        left join
                            (
                                select kp.no_sj, tp.no_bbm as kode_trans, kp.ekspedisi_id, tp.tgl_terima as tanggal from kirim_pakan kp
                                left join
                                    terima_pakan tp 
                                    on
                                        kp.id = tp.id_kirim_pakan
                                group by
                                    kp.no_sj, tp.no_bbm, kp.ekspedisi_id, tp.tgl_terima
                                
                                union all
                                
                                select no_retur as no_sj, no_retur as kode_trans, ekspedisi_id, tgl_retur as tanggal from retur_pakan rp 
                            ) krm
                            on
                                opp.no_sj = krm.no_sj
                        where
                            krm.tanggal between '".$start_date."' and '".$end_date."' and
                            not exists (select * from konfirmasi_pembayaran_oa_pakan_det kpopd where no_sj = opp.no_sj)
                    ) oa
                    group by
                        oa.nomor,
                        oa.supplier
                    /* END - OA PAKAN */

                    union all

                    select
                        kpv.nomor, 
                        kpv.supplier, 
                        kpv.total as debet,
                        0 as kredit,
                        case
                            when kpv.supplier = '19B004' then
                                'OVK'
                            else
                                'OVK EXTERN'
                        end as jenis,
        --                'OVK' as jenis,
                        kpvd.kode_unit as unit
                    from konfirmasi_pembayaran_voadip_det kpvd
                    left join
                        konfirmasi_pembayaran_voadip kpv
                        on
                            kpvd.id_header = kpv.id
                    where
                        kpv.tgl_bayar between '".$start_date."' and '".$end_date."'

                    union all

                    select
                        kpp.nomor,
                        kpp.mitra as supplier,
                        kpp.total as debet,
                        0 as kredit,
                        'RHPP' as jenis,
                        null as unit
                    from konfirmasi_pembayaran_peternak kpp
                    where
                        kpp.tgl_bayar between '".$start_date."' and '".$end_date."'

                    union all

                    select
                        op.no_order as nomor,
                        op.supplier,
                        op.total as debet,
                        0 as kredit,
                        'PERALATAN' as jenis,
                        op.unit
                    from order_peralatan op
                    where
                        op.tgl_order between '".$start_date."' and '".$end_date."'

                    union all

                    select
                        d.nomor,
                        d.supplier,
                        d.tot_dn as debet,
                        0 as kredit,
                        case
                            when d.jenis_dn = 'PKN' then
                                'PAKAN'
                            else
                                d.jenis_dn 
                        end as jenis,
                        null as unit
                    from dn d
                    where
                        d.tanggal between '".$start_date."' and '".$end_date."'

                    union all

                    /* INVOICE LEWAT MEMO */
                    select * from (
                        select
                            mi.no_mm as nomor,
                            m.no_supplier as supplier,
                            mi.nilai as debet,
                            0 as kredit,
                            case
                                when mi.coa_asal = '21180.300' then
                                    'OVK EXTERN'
                                when mi.coa_asal = '21174.000' then
                                    'OVK'
                            end as jenis,
                            m.unit
                        from mmitem mi
                        left join
                            mm m
                            on
                                mi.no_mm = m.no_mm
                        where 
                            mi.coa_asal in ('21180.300', '21174.000') and
                            cast(mi.tgl_mm as date) between '".$start_date."' and '".$end_date."'
                    ) inv_mm
                    /* END - INVOICE LEWAT MEMO */
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
                        c.tanggal between '".$start_date."' and '".$end_date."'

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
                        bp.tgl_realisasi between '".$start_date."' and '".$end_date."'

                    union all

                    select 
                        rp.nomor,
                        rp.supplier,
                        0 as debet,
                        case
                            when rpd.transaksi = 'DOC' then
                                case
                                    when konfir.tanggal <= '2025-09-20' then
                                        rpd.transfer
                                    else
                                        rpd.transfer+konfir.pph
                                        -- rpd.transfer
                                end
                            else
                                rpd.transfer
                        end as kredit,
                        case
                            when rpd.transaksi = 'VOADIP' then
                                case
                                    when rp.supplier = '19B004' then
                                        'OVK'
                                    else
                                        'OVK EXTERN'
                                end
                            else
                                rpd.transaksi
                        end as jenis,
        --                rpd.transaksi as jenis,
                        konfir.kode_unit as unit
                    from realisasi_pembayaran_det rpd
                    left join
                        realisasi_pembayaran rp
                        on
                            rpd.id_header = rp.id
                    left join
                        (
                            select kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar as tanggal, (kpdd.total * (0.25/100)) as pph from konfirmasi_pembayaran_doc_det kpdd 
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
                                kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar, kpdd.total
                                
                            union all
                            
                            select kppd.kode_unit, kpp.nomor, null as tanggal, 0 as pph from konfirmasi_pembayaran_pakan_det kppd 
                            left join
                                konfirmasi_pembayaran_pakan kpp 
                                on
                                    kppd.id_header = kpp.id
                            group by
                                kppd.kode_unit, kpp.nomor
                                
                            union all
                            
                            select kpvd.kode_unit, kpv.nomor, null as tanggal, 0 as pph from konfirmasi_pembayaran_voadip_det kpvd 
                            left join
                                konfirmasi_pembayaran_voadip kpv 
                                on
                                    kpvd.id_header = kpv.id
                            group by
                                kpvd.kode_unit, kpv.nomor
                        ) konfir
                        on
                            rpd.no_bayar = konfir.nomor
                    where
                        rp.tgl_bayar between '".$start_date."' and '".$end_date."'

                    union all

                    /* BAYAR LEWAT MEMO */
                    select * from (
                        select
                            mi.no_invoice as nomor,
                            konfir.supplier,
                            0 as debet,
                            mi.nilai as kredit,
                            case
                                when mi.coa_tujuan = '21180.300' then
                                    'OVK EXTERN'
                                when mi.coa_tujuan = '21174.000' then
                                    'OVK'
                            end as jenis,
                            m.unit
                        from mmitem mi
                        left join
                        	mm m
                        	on
                        		mi.no_mm = m.no_mm
                        left join
                            (
                                select nomor, supplier from konfirmasi_pembayaran_voadip group by nomor, supplier

                                union all

                                select nomor, supplier from konfirmasi_pembayaran_pakan group by nomor, supplier

                                union all

                                select nomor, supplier from konfirmasi_pembayaran_doc group by nomor, supplier

                                union all

                                select nomor, ekspedisi_id as supplier from konfirmasi_pembayaran_oa_pakan group by nomor, ekspedisi_id

                                union all

                                select nomor, mitra as supplier from konfirmasi_pembayaran_peternak group by nomor, mitra
                            ) konfir
                            on
                                mi.no_invoice = konfir.nomor
                        where 
                            mi.coa_tujuan in ('21180.300', '21174.000') and
                            cast(mi.tgl_mm as date) between '".$start_date."' and '".$end_date."'
                    ) byr_mm
                    /* END - BAYAR LEWAT MEMO */
                    /* END - KREDIT */
                ) trans
                group by
                    trans.supplier,
                    trans.jenis,
                    trans.unit
                /* END - TRANSAKSI BULAN BERJALAN */
            ) _data
            group by
                _data.supplier,
                _data.jenis,
                _data.unit
        ) data
        left join
            (
                select p1.nomor, p1.nama, p1.tipe from pelanggan p1
                right join
                    (select max(id) as id, nomor from pelanggan p where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) p2
                    on
                        p1.id = p2.id

                union all
                        
                select e1.nomor, e1.nama, 'ekspedisi' as tipe from ekspedisi e1
                right join
                    (select max(id) as id, nomor from ekspedisi e group by nomor) e2
                    on
                        e1.id = e2.id

                union all

                select m1.nomor, m1.nama, 'plasma' as tipe from mitra m1
                right join
                    (select max(id) as id, nomor from mitra group by nomor) m2
                    on
                        m1.id = m2.id
            ) supl
            on
                supl.nomor = data.supplier
        ".$where."
        group by
            -- data.jenis,
            -- data.unit
            data.supplier,
            supl.nama
        order by
            supl.nama asc
        ";
        // cetak_r( $sql, 1 );
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
