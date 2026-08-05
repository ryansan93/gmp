<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class KartuPiutangRingkas extends Public_Controller {

    private $pathView = 'report/kartu_piutang_ringkas/';
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
                "assets/report/kartu_piutang_ringkas/js/kartu-piutang-ringkas.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/kartu_piutang_ringkas/css/kartu-piutang-ringkas.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['title_menu'] = 'Laporan Kartu Piutang Ringkas';

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
        $sql_old = "
            select
                data.*,
                plg.nama as nama_pelanggan
            from (
                select
                    d.pelanggan,
                    isnull(sum(d.saldo_awal), 0) as saldo_awal,
                    isnull(sum(d.debet), 0) as debet,
                    isnull(sum(d.kredit), 0) as kredit,
                    (isnull(sum(d.saldo_awal), 0)+isnull(sum(d.debet), 0))-isnull(sum(d.kredit), 0) as saldo_akhir
                from
                (
                    /* SALDO AWAL */
                    select 
                        '".$start_date."' as tanggal,
                        inv.pelanggan,
                        'Saldo Awal' as jenis_trans,
                        0 as debet,
                        0 as kredit,
                        sum( (inv.total+(isnull(byr.dn, 0))) - (isnull(byr.cn, 0)+isnull(byr.potongan, 0)+isnull(byr.uang_muka, 0)+isnull(byr.transfer, 0)+isnull(byr.saldo, 0)) ) as saldo_awal,
                        1 as urut
                    from (
                        select
                            drsi.no_inv as nomor,
                            drs.no_pelanggan as pelanggan,
                            drsi.total
                        from det_real_sj_inv drsi
                        left join
                            (select id_header, no_sj, no_pelanggan from det_real_sj group by id_header, no_sj, no_pelanggan) drs
                            on
                                drsi.no_sj = drs.no_sj
                        left join
                            real_sj rs
                            on
                                drs.id_header = rs.id
                        where
                            rs.tgl_panen < '".$start_date."'

                        union all

                        select
                            c.nomor,
                            c.pelanggan,
                            0 - (c.tot_cn - isnull(rpc.pakai, 0)) as total
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
                            (c.pelanggan is not null and c.pelanggan <> '')

                        union all

                        select
                            d.nomor,
                            d.pelanggan,
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
                            (d.pelanggan is not null and d.pelanggan <> '')
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
                                    dpp.no_inv as nomor, 
                                    sum(dppcd.nominal) as cn, 
                                    0 as dn, 
                                    0 as potongan, 
                                    0 as uang_muka, 
                                    0 as transfer, 
                                    0 as saldo 
                                from det_pembayaran_pelanggan_cn_dn dppcd
                                left join
                                    det_pembayaran_pelanggan dpp
                                    on
                                        dppcd.id_header = dpp.id
                                left join
                                    pembayaran_pelanggan pp
                                    on
                                        dpp.id_header = pp.id
                                left join
                                    (
                                        select nomor, tanggal from cn c
                                        union all
                                        select nomor, tanggal from dn d
                                    ) cn_dn
                                    on
                                        dppcd.nomor_cn_dn = cn_dn.nomor
                                where
                                    dppcd.nomor_cn_dn like '%CN%' and
                                    pp.tgl_bayar < '".$start_date."' and
                                    cn_dn.tanggal < '".$start_date."'
                                group by
                                    dpp.no_inv
        
                                union all
        
                                select 
                                    dpp.no_inv as nomor, 
                                    0 as cn, 
                                    sum(dppcd.nominal) as dn, 
                                    0 as potongan, 
                                    0 as uang_muka, 
                                    0 as transfer, 
                                    0 as saldo 
                                from det_pembayaran_pelanggan_cn_dn dppcd
                                left join
                                    det_pembayaran_pelanggan dpp
                                    on
                                        dppcd.id_header = dpp.id
                                left join
                                    pembayaran_pelanggan pp
                                    on
                                        dpp.id_header = pp.id
                                left join
                                    (
                                        select nomor, tanggal from cn c
                                        union all
                                        select nomor, tanggal from dn d
                                    ) cn_dn
                                    on
                                        dppcd.nomor_cn_dn = cn_dn.nomor
                                where
                                    dppcd.nomor_cn_dn like '%DN%' and
                                    pp.tgl_bayar < '".$start_date."' and
                                    cn_dn.tanggal < '".$start_date."'
                                group by
                                    dpp.no_inv
                                
                                union all
        
                                select 
                                    dpp.no_inv as nomor, 
                                    0 as cn, 
                                    0 as dn, 
                                    sum(dpp.penyesuaian) as potongan, 
                                    0 as uang_muka, 
                                    isnull(sum(dpp.tagihan-(dpp.penyesuaian+dpp.sisa_tagihan)), 0) as transfer,
                                    0 as saldo
                                from det_pembayaran_pelanggan dpp
                                left join
                                    pembayaran_pelanggan pp
                                    on
                                        dpp.id_header = pp.id
                                where
                                    pp.tgl_bayar < '".$start_date."'
                                group by
                                    dpp.no_inv
                            ) byr
                            group by
                                byr.nomor
                        ) byr
                        on
                            inv.nomor = byr.nomor
                    group by
                        inv.pelanggan
                    /* END - SALDO AWAL */

                    union all

                    /* TRANSAKSI DI BULAN ITU */
                    select 
                        inv.tanggal as tanggal,
                        inv.pelanggan, 
                        inv.kode_trans as jenis_trans,
                        inv.total as debet,
                        0 as kredit,
                        0 as saldo,
                        2 as urut
                    from (
                        select
                            rs.tgl_panen as tanggal,
                            drsi.no_inv as nomor,
                            drs.no_pelanggan as pelanggan,
                            drsi.total,
                            drsi.no_inv as kode_trans
                        from det_real_sj_inv drsi
                        left join
                            (select id_header, no_sj, no_pelanggan from det_real_sj group by id_header, no_sj, no_pelanggan) drs
                            on
                                drsi.no_sj = drs.no_sj
                        left join
                            real_sj rs
                            on
                                drs.id_header = rs.id
                        where
                            rs.tgl_panen between '".$start_date."' and '".$end_date."'

                        union all

                        select
                            d.tanggal,
                            d.nomor,
                            d.pelanggan,
                            d.tot_dn as total,
                            d.nomor as kode_trans
                        from dn d
                        where 
                            d.tanggal between '".$start_date."' and '".$end_date."' and
                            (d.pelanggan is not null and d.pelanggan <> '')
                    ) inv
                    /* END - TRANSAKSI DI BULAN ITU */

                    union all

                    select
                        byr.tanggal as tanggal,
                        byr.pelanggan, 
                        byr.kode_trans as jenis_trans,
                        byr.debet as debet,
                        byr.kredit as kredit,
                        0 as saldo,
                        2 as urut
                    from
                    (
                        select 
                            pp.tgl_bayar as tanggal,
                            pp.no_pelanggan as pelanggan,
                            dpp.no_inv as nomor,
                            0 as debet, 
                            isnull(dpp.tagihan-dpp.sisa_tagihan, 0) as kredit,
                            pp.nomor as kode_trans
                        from det_pembayaran_pelanggan dpp
                        left join
                            pembayaran_pelanggan pp
                            on
                                dpp.id_header = pp.id
                        where
                            pp.tgl_bayar between '".$start_date."' and '".$end_date."' and
                            isnull(dpp.tagihan-dpp.sisa_tagihan, 0) > 0

                        union all

                        select
                            c.tanggal,
                            c.pelanggan,
                            c.nomor,
                            0 as debet,
                            c.tot_cn as kredit,
                            c.nomor as kode_trans
                        from cn c
                        where 
                            c.tanggal between '".$start_date."' and '".$end_date."' and
                            (c.pelanggan is not null and c.pelanggan <> '')
                    ) byr
                ) d
                group by
                    d.pelanggan
            ) data
            left join
                (
                    select p1.nomor, p1.nama from pelanggan p1
                    right join
                        (select max(id) as id, nomor from pelanggan p where tipe = 'pelanggan' group by nomor) p2
                        on
                            p1.id = p2.id
                ) plg
                on
                    plg.nomor = data.pelanggan
            order by
                data.pelanggan asc
        ";
        $sql = "
            select
                data.*,
                plg.nama as nama_pelanggan
            from (
                select
                    d.pelanggan,
                    isnull(sum(d.saldo_awal), 0) as saldo_awal,
                    isnull(sum(d.debet), 0) as debet,
                    isnull(sum(d.kredit), 0) as kredit,
                    (isnull(sum(d.saldo_awal), 0)+isnull(sum(d.debet), 0))-isnull(sum(d.kredit), 0) as saldo_akhir
                from
                (
                    /* SALDO AWAL */
                    select 
                        '".$start_date."' as tanggal,
                        inv.pelanggan,
                        'Saldo Awal' as jenis_trans,
                        sum(isnull(inv.debet, 0 ) - isnull(inv.kredit, 0)) as saldo_awal,
                        0 as debet,
                        0 as kredit,
                        1 as urut
                    from (
                        select
                            rs.tgl_panen as tanggal,
                            drsi.no_inv as nomor,
                            drs.no_pelanggan as pelanggan,
                            drsi.total as debet,
                            0 as kredit,
                            drsi.no_inv as kode_trans
                        from det_real_sj_inv drsi
                        left join
                            (select id_header, no_sj, no_pelanggan from det_real_sj group by id_header, no_sj, no_pelanggan) drs
                            on
                                drsi.no_sj = drs.no_sj
                        left join
                            real_sj rs
                            on
                                drs.id_header = rs.id
                        where
                            rs.tgl_panen < '".$start_date."'

                        union all

                        select 
                            pp.tgl_bayar as tanggal,
                            dpp.no_inv as nomor,
                            pp.no_pelanggan as pelanggan,
                            0 as debet,
                            isnull(dpp.tagihan-dpp.sisa_tagihan, 0) as kredit,
                            pp.nomor as kode_trans
                        from det_pembayaran_pelanggan dpp
                        left join
                            pembayaran_pelanggan pp
                            on
                                dpp.id_header = pp.id
                        left join
                            det_real_sj_inv drsi
                            on
                                dpp.no_inv = drsi.no_inv 
                        left join
                            (select id_header, no_sj, no_pelanggan from det_real_sj group by id_header, no_sj, no_pelanggan) drs
                            on
                                drsi.no_sj = drs.no_sj
                        left join
                            real_sj rs
                            on
                                drs.id_header = rs.id
                        where
                            pp.tgl_bayar < '".$start_date."'
                            and isnull(dpp.tagihan-dpp.sisa_tagihan, 0) > 0
                    ) inv
                    group by
                        inv.pelanggan
                    having
                        sum(isnull(inv.debet, 0 ) - isnull(inv.kredit, 0)) <> 0
                    /* END - SALDO AWAL */

                    union all

                    /* TRANSAKSI DI BULAN ITU */
                    select 
                        inv.tanggal,
                        inv.pelanggan, 
                        inv.nomor as jenis_trans,
                        0 as saldo_awal,
                        isnull(sum(inv.total), 0) as debet,
                        0 as kredit,
                        2 as urut
                    from (
                        select
                            rs.tgl_panen as tanggal,
                            drsi.no_inv as nomor,
                            drs.no_pelanggan as pelanggan,
                            drsi.total,
                            drsi.no_inv as kode_trans
                        from det_real_sj_inv drsi
                        left join
                            (select id_header, no_sj, no_pelanggan from det_real_sj group by id_header, no_sj, no_pelanggan) drs
                            on
                                drsi.no_sj = drs.no_sj
                        left join
                            real_sj rs
                            on
                                drs.id_header = rs.id
                        where
                            rs.tgl_panen between '".$start_date."' and '".$end_date."'
                    ) inv
                    group by
                        inv.tanggal,
                        inv.pelanggan,
                        inv.nomor

                    union all

                    select
                        byr.tanggal,
                        byr.pelanggan,
                        byr.nomor as jenis_trans,
                        0 as saldo_awal,
                        0 as debet,
                        isnull(sum(byr.bayar), 0) as kredit,
                        3 as urut
                    from
                    (
                        select 
                            pp.tgl_bayar as tanggal,
                            pp.no_pelanggan as pelanggan,
                            dpp.no_inv as nomor,
                            isnull(dpp.tagihan-dpp.sisa_tagihan, 0) as bayar,
                            pp.nomor as kode_trans
                        from det_pembayaran_pelanggan dpp
                        left join
                            pembayaran_pelanggan pp
                            on
                                dpp.id_header = pp.id
                        where
                            pp.tgl_bayar between '".$start_date."' and '".$end_date."' 
                            and isnull(dpp.tagihan-dpp.sisa_tagihan, 0) <> 0
                    ) byr
                    group by
                        byr.tanggal,
                        byr.pelanggan,
                        byr.nomor
                    /* END - TRANSAKSI DI BULAN ITU */
                ) d
                group by
                    d.pelanggan
            ) data
            left join
                (
                    select p1.nomor, p1.nama, p1.tipe_plg from pelanggan p1
                    right join
                        (select max(id) as id, nomor from pelanggan p where tipe = 'pelanggan' group by nomor) p2
                        on
                            p1.id = p2.id
                ) plg
                on
                    plg.nomor = data.pelanggan
            left join
                tipe_pelanggan tp
                on
                    tp.id = plg.tipe_plg
            order by
                data.pelanggan asc
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

    /**
     * Rekap yang sama dengan getData(), tapi digrupkan per UNIT (bukan per pelanggan) --
     * unit diambil dari real_sj.id_unit (unit yg panen/jual), ditelusuri dari tiap invoice
     * (det_real_sj_inv) baik untuk baris debet (invoice) maupun kredit (pembayaran, ditelusuri
     * balik ke invoice yg dibayar). Dipakai untuk cocokkan ke GL per unit (COA Piutang Bakul).
     */
    public function getDataPerUnit() {
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
                isnull(w.kode, '-') as unit,
                isnull(REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', ''), 'TANPA UNIT') as nama_unit,
                isnull(sum(data.saldo_awal), 0) as saldo_awal,
                isnull(sum(data.debet), 0) as debet,
                isnull(sum(data.kredit), 0) as kredit,
                (isnull(sum(data.saldo_awal), 0)+isnull(sum(data.debet), 0))-isnull(sum(data.kredit), 0) as saldo_akhir
            from
            (
                /* SALDO AWAL -- saldo piutang per invoice sebelum start_date */
                select
                    rs.id_unit,
                    sum(isnull(x.debet, 0) - isnull(x.kredit, 0)) as saldo_awal,
                    0 as debet,
                    0 as kredit
                from (
                    select
                        drs.id_header,
                        drsi.total as debet,
                        0 as kredit
                    from det_real_sj_inv drsi
                    left join
                        (select id_header, no_sj from det_real_sj group by id_header, no_sj) drs
                        on
                            drsi.no_sj = drs.no_sj
                    left join
                        real_sj rs1
                        on
                            drs.id_header = rs1.id
                    where
                        rs1.tgl_panen < '".$start_date."'

                    union all

                    select
                        drs.id_header,
                        0 as debet,
                        isnull(dpp.tagihan-dpp.sisa_tagihan, 0) as kredit
                    from det_pembayaran_pelanggan dpp
                    left join
                        pembayaran_pelanggan pp
                        on
                            dpp.id_header = pp.id
                    left join
                        det_real_sj_inv drsi
                        on
                            dpp.no_inv = drsi.no_inv
                    left join
                        (select id_header, no_sj from det_real_sj group by id_header, no_sj) drs
                        on
                            drsi.no_sj = drs.no_sj
                    where
                        pp.tgl_bayar < '".$start_date."'
                        and isnull(dpp.tagihan-dpp.sisa_tagihan, 0) > 0
                ) x
                left join
                    real_sj rs
                    on
                        x.id_header = rs.id
                group by
                    rs.id_unit
                /* END - SALDO AWAL */

                union all

                /* TRANSAKSI DI BULAN ITU -- debet (invoice panen bulan ybs) */
                select
                    rs.id_unit,
                    0 as saldo_awal,
                    isnull(sum(drsi.total), 0) as debet,
                    0 as kredit
                from det_real_sj_inv drsi
                left join
                    (select id_header, no_sj from det_real_sj group by id_header, no_sj) drs
                    on
                        drsi.no_sj = drs.no_sj
                left join
                    real_sj rs
                    on
                        drs.id_header = rs.id
                where
                    rs.tgl_panen between '".$start_date."' and '".$end_date."'
                group by
                    rs.id_unit

                union all

                /* TRANSAKSI DI BULAN ITU -- kredit (pembayaran bulan ybs, unit ditelusuri dari invoice yg dibayar) */
                select
                    rs.id_unit,
                    0 as saldo_awal,
                    0 as debet,
                    isnull(sum(dpp.tagihan-dpp.sisa_tagihan), 0) as kredit
                from det_pembayaran_pelanggan dpp
                left join
                    pembayaran_pelanggan pp
                    on
                        dpp.id_header = pp.id
                left join
                    det_real_sj_inv drsi
                    on
                        dpp.no_inv = drsi.no_inv
                left join
                    (select id_header, no_sj from det_real_sj group by id_header, no_sj) drs
                    on
                        drsi.no_sj = drs.no_sj
                left join
                    real_sj rs
                    on
                        drs.id_header = rs.id
                where
                    pp.tgl_bayar between '".$start_date."' and '".$end_date."'
                    and isnull(dpp.tagihan-dpp.sisa_tagihan, 0) <> 0
                group by
                    rs.id_unit
            ) data
            left join
                wilayah w
                on
                    data.id_unit = w.id
            group by
                w.kode, REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', '')
            order by
                isnull(w.kode, '-') asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'listPerUnit', $content, TRUE);

        echo $html;
    }
}
