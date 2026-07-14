<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class LembarKerjaHpp extends Public_Controller {

    private $pathView = 'report/lembar_kerja_hpp/';
    private $url;
    private $akses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->akses = hakAkses($this->url);
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index($params = null)
    {
        $akses = $this->akses;
        // if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/jquery/tupage-table/jquery.tupage.table.js",
                "assets/report/lembar_kerja_hpp/js/lembar-kerja-hpp.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/jquery/tupage-table/jquery.tupage.table.css",
                "assets/report/lembar_kerja_hpp/css/lembar-kerja-hpp.css",
            ));

            $data = $this->includes;

            $kode_unit = null;
            $periode = null;

            if ( !empty($params) ) {
                $params = json_decode(exDecrypt($params), true);

                $kode_unit = $params['kode_unit'];
                $periode = $params['periode'];
            }

            $m_wil = new \Model\Storage\Wilayah_model();

            $content['unit'] = $m_wil->getDataUnit(1, $this->userid);
            $content['periode'] = $periode;
            $content['title_menu'] = 'Laporan Lembar Kerja HPP';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        // } else {
        //     showErrorAkses();
        // }
    }

    public function getData($params) {
        $unit = $params['unit'];
        // $bulan = $params['bulan'];
        // $tahun = substr($params['tahun'], 0, 4);

        // if ( $bulan != 'all' ) {
        //     $i = $bulan;

        //     $angka_bulan = (strlen($i) == 1) ? '0'.$i : $i;

        //     $date = $tahun.'-'.$angka_bulan.'-01';
        //     $start_date = date("Y-m-d", strtotime($date));
        //     $end_date = date("Y-m-t", strtotime($date));
        // } else {
        //     $i = 1;
        //     $angka_bulan = (strlen($i) == 1) ? '0'.$i : $i;
        //     $_start_date = $tahun.'-'.$angka_bulan.'-01';
        //     $start_date = date("Y-m-d", strtotime($_start_date));

        //     $i = 12;
        //     $angka_bulan = (strlen($i) == 1) ? '0'.$i : $i;
        //     $_end_date = $tahun.'-'.$angka_bulan.'-01';
        //     $end_date = date("Y-m-t", strtotime($_end_date));
        // }

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $sql_unit = null;
        if ( stristr('all', $unit) === false ) {
            $sql_unit = " and w.kode = '".$unit."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.unit,
                data.noreg,
                data.nama,
                data.tgl_chick_in,
                data.populasi,
                isnull(data.sa_pkn, 0) as sa_pkn,
                data.beli_pkn,
                data.mutasi_msk_pkn,
                data.mutasi_klwr_pkn,
                data.koreksi_pkn,
                data.pemakaian_pkn,
                ((isnull(data.sa_pkn, 0)+data.beli_pkn+data.mutasi_msk_pkn) - (data.mutasi_klwr_pkn+data.pemakaian_pkn)) + isnull(data.koreksi_pkn, 0) as sisa_pkn,
                isnull(data.sa_ovk, 0) as sa_ovk,
                data.beli_ovk,
                data.mutasi_msk_ovk,
                data.mutasi_klwr_ovk,
                data.pemakaian_ovk,
                ((isnull(data.sa_ovk, 0)+data.beli_ovk+data.mutasi_msk_ovk) - (data.mutasi_klwr_ovk+data.pemakaian_ovk)) as sisa_ovk,
                isnull(data.sa_doc, 0) as sa_doc,
                data.beli_doc,
                data.mutasi_msk_doc,
                data.mutasi_klwr_doc,
                data.koreksi_doc,
                data.pemakaian_doc,
                ((isnull(data.sa_doc, 0)+data.beli_doc+data.mutasi_msk_doc+data.koreksi_doc) - (data.mutasi_klwr_doc+data.pemakaian_doc)) as sisa_doc,
                isnull(data.sa_oa, 0) as sa_oa,
                data.beli_oa,
                data.mutasi_msk_oa,
                data.mutasi_klwr_oa,
                data.koreksi_oa,
                data.pemakaian_oa,
                ((data.beli_oa+isnull(data.mutasi_msk_oa, 0)) - isnull(data.mutasi_klwr_oa, 0)) + isnull(data.koreksi_oa, 0) as net_oa,
                ((isnull(data.sa_oa, 0)+data.beli_oa+isnull(data.mutasi_msk_oa, 0)) - isnull(data.mutasi_klwr_oa, 0)) + isnull(data.koreksi_oa, 0) as saldo_akhir_oa,
                -- ((isnull(data.sa_oa, 0)+data.beli_oa+data.mutasi_msk_oa) - (data.mutasi_klwr_oa+data.pemakaian_oa)) + isnull(data.koreksi_oa, 0) as sisa_oa,
                data.pdpt_peternak,
                data.pdpt_peternak + data.pemakaian_pkn + data.pemakaian_ovk + data.pemakaian_doc + data.pemakaian_oa as total
            from
            (
                select 
                    w.kode as unit,
                    data.noreg,
                    m.nama,
                    case
                        when td.datang is not null then
                            td.datang
                        else
                            rs.tgl_docin
                    end as tgl_chick_in,
                    case
                        when td.jml_ekor is not null then
                            td.jml_ekor
                        else
                            rs.populasi
                    end as populasi,
                    sum(data.sa_pkn) as sa_pkn,
                    sum(data.beli_pkn) as beli_pkn,
                    sum(data.mutasi_msk_pkn) as mutasi_msk_pkn,
                    sum(data.mutasi_klwr_pkn) as mutasi_klwr_pkn,
                    sum(data.koreksi_pkn) as koreksi_pkn,
                    sum(data.pemakaian_pkn) as pemakaian_pkn,
                    sum(data.sa_ovk) as sa_ovk,
                    sum(data.beli_ovk) as beli_ovk,
                    sum(data.mutasi_msk_ovk) as mutasi_msk_ovk,
                    sum(data.mutasi_klwr_ovk) as mutasi_klwr_ovk,
                    sum(data.pemakaian_ovk) as pemakaian_ovk,
                    sum(data.sa_doc) as sa_doc,
                    sum(data.beli_doc) as beli_doc,
                    sum(data.mutasi_msk_doc) as mutasi_msk_doc,
                    sum(data.mutasi_klwr_doc) as mutasi_klwr_doc,
                    sum(data.koreksi_doc) as koreksi_doc,
                    sum(data.pemakaian_doc) as pemakaian_doc,
                    sum(data.sa_oa) as sa_oa,
                    sum(data.beli_oa) as beli_oa,
                    sum(data.mutasi_msk_oa) as mutasi_msk_oa,
                    sum(data.mutasi_klwr_oa) as mutasi_klwr_oa,
                    sum(data.koreksi_oa) as koreksi_oa,
                    sum(data.pemakaian_oa) as pemakaian_oa,
                    sum(data.rhpp) as pdpt_peternak,
                    sum(data.rhpp) + sum(data.pemakaian_pkn) + sum(data.pemakaian_ovk) + sum(data.pemakaian_doc) + sum(data.pemakaian_oa) as total
                    -- isnull(rhpp_p.pdpt_peternak_belum_pajak, 0) as pdpt_peternak,
                    -- isnull(rhpp_p.pdpt_peternak_belum_pajak, 0) + sum(data.pemakaian_pkn) + sum(data.pemakaian_ovk) + sum(data.pemakaian_doc) + sum(data.pemakaian_oa) as total
                from
                (
                    select
                        pkn.noreg,
                        isnull(sa.saldo_awal, 0) as sa_pkn,
                        pkn.beli_pkn,
                        pkn.mutasi_msk_pkn,
                        pkn.mutasi_klwr_pkn,
                        pkn.koreksi_pkn,
                        pkn.pemakaian_pkn,
                        pkn.sisa_pkn,
                        0 as sa_ovk,
                        0 as beli_ovk,
                        0 as mutasi_msk_ovk,
                        0 as mutasi_klwr_ovk,
                        0 as pemakaian_ovk,
                        0 as sa_doc,
                        0 as beli_doc,
                        0 as mutasi_msk_doc,
                        0 as mutasi_klwr_doc,
                        0 as koreksi_doc,
                        0 as pemakaian_doc,
                        0 as sa_oa,
                        0 as beli_oa,
                        0 as mutasi_msk_oa,
                        0 as mutasi_klwr_oa,
                        0 as koreksi_oa,
                        0 as pemakaian_oa,
                        0 as rhpp
                    from
                    (
                        select
                            pkn.noreg,
                            isnull(sum(pkn.jml_beli * pkn.hrg_beli), 0) as beli_pkn,
                            isnull(sum(pkn.jml_mutasi_msk * pkn.hrg_mutasi_msk), 0) as mutasi_msk_pkn,
                            isnull(sum(pkn.jml_mutasi_klwr * pkn.hrg_mutasi_klwr), 0) as mutasi_klwr_pkn,
                            isnull(sum(pkn.jml_koreksi * pkn.hrg_koreksi), 0) as koreksi_pkn,
                            -- isnull(sum(pkn.jml_pemakaian * pkn.hrg_pemakaian), 0) as pemakaian_pkn,
                            isnull(sum(pkn.nominal_pemakaian), 0) as pemakaian_pkn,
                            -- isnull(sum(pkn.jml_beli * pkn.hrg_beli), 0) + isnull(sum(pkn.jml_mutasi_msk * pkn.hrg_mutasi_msk), 0) - (isnull(sum(pkn.jml_mutasi_klwr * pkn.hrg_mutasi_klwr), 0) + isnull(sum(pkn.jml_pemakaian * pkn.hrg_pemakaian), 0)) + isnull(sum(pkn.jml_koreksi * pkn.hrg_koreksi), 0) as sisa_pkn
                            isnull(sum(pkn.jml_beli * pkn.hrg_beli), 0) + isnull(sum(pkn.jml_mutasi_msk * pkn.hrg_mutasi_msk), 0) - (isnull(sum(pkn.jml_mutasi_klwr * pkn.hrg_mutasi_klwr), 0) + isnull(sum(pkn.nominal_pemakaian), 0)) + isnull(sum(pkn.jml_koreksi * pkn.hrg_koreksi), 0) as sisa_pkn
                        from (
                            select 
                                dss.noreg,
                                dss.kode_trans,
                                sum(dss.jumlah) as jml_beli,
                                dss.hrg_beli,
                                0 as jml_mutasi_msk,
                                0 as hrg_mutasi_msk,
                                0 as jml_mutasi_klwr,
                                0 as hrg_mutasi_klwr,
                                0 as jml_koreksi,
                                0 as hrg_koreksi,
                                0 as jml_pemakaian,
                                0 as hrg_pemakaian,
                                0 as nominal_pemakaian
                            from det_stok_siklus dss
                            left join
                                kirim_pakan kp
                                on
                                    dss.kode_trans = kp.no_order
                            where
                                dss.jenis_barang = 'pakan' and
                                dss.tgl_trans between '".$start_date."' and '".$end_date."' and
                                kp.jenis_kirim = 'opkg'
                            group by
                                dss.noreg, dss.kode_trans, dss.hrg_beli
    
                            union all
    
                            select 
                                dss.noreg,
                                dss.kode_trans,
                                0 as jml_beli,
                                0 as hrg_beli,
                                sum(dss.jumlah) as jml_mutasi_msk,
                                dss.hrg_beli as hrg_mutasi_msk,
                                0 as jml_mutasi_klwr,
                                0 as hrg_mutasi_klwr,
                                0 as jml_koreksi,
                                0 as hrg_koreksi,
                                0 as jml_pemakaian,
                                0 as hrg_pemakaian,
                                0 as nominal_pemakaian
                            from det_stok_siklus dss
                            left join
                                kirim_pakan kp
                                on
                                    dss.kode_trans = kp.no_order
                            where
                                dss.jenis_barang = 'pakan' and
                                dss.tgl_trans between '".$start_date."' and '".$end_date."' and
                                kp.jenis_kirim = 'opkp'
                            group by
                                dss.noreg, dss.kode_trans, dss.hrg_beli
    
                            union all
    
                            select 
                                dss.noreg,
                                dsts.kode_trans,
                                0 as jml_beli,
                                0 as hrg_beli,
                                0 as jml_mutasi_msk,
                                0 as hrg_mutasi_msk,
                                sum(dsts.jumlah) as jml_mutasi_klwr,
                                dss.hrg_beli as hrg_mutasi_klwr,
                                0 as jml_koreksi,
                                0 as hrg_koreksi,
                                0 as jml_pemakaian,
                                0 as hrg_pemakaian,
                                0 as nominal_pemakaian
                            from det_stok_trans_siklus dsts
                            left join 
                                det_stok_siklus dss
                                on
                                    dsts.id_header = dss.id
                            left join
                                kirim_pakan kp
                                on
                                    dsts.kode_trans = kp.no_order
                            where
                                dss.jenis_barang = 'pakan' and
                                dsts.tgl_trans between '".$start_date."' and '".$end_date."' and
                                kp.jenis_kirim = 'opkp'
                            group by
                                dss.noreg, dsts.kode_trans, dss.hrg_beli
    
                            union all

                            select 
                                dss.noreg,
                                dsts.kode_trans,
                                0 as jml_beli,
                                0 as hrg_beli,
                                0 as jml_mutasi_msk,
                                0 as hrg_mutasi_msk,
                                0 as jml_mutasi_klwr,
                                0 as hrg_mutasi_klwr,
                                0-isnull(sum(dsts.jumlah), 0) as jml_koreksi,
                                dss.hrg_beli as hrg_koreksi,
                                0 as jml_pemakaian,
                                0 as hrg_pemakaian,
                                0 as nominal_pemakaian
                            from det_stok_trans_siklus dsts
                            left join 
                                det_stok_siklus dss
                                on
                                    dsts.id_header = dss.id
                            left join
                                retur_pakan rp
                                on
                                    dsts.kode_trans = rp.no_retur
                            where
                                dss.jenis_barang = 'pakan' and
                                dsts.tgl_trans between '".$start_date."' and '".$end_date."' and
                                rp.jenis_retur = 'opkp'
                            group by
                                dss.noreg, dsts.kode_trans, dss.hrg_beli
    
                            union all
    
                            select * from (
                                select 
                                    dss.noreg,
                                    dsts.kode_trans,
                                    0 as jml_beli,
                                    0 as hrg_beli,
                                    0 as jml_mutasi_msk,
                                    0 as hrg_mutasi_msk,
                                    0 as jml_mutasi_klwr,
                                    0 as hrg_mutasi_klwr,
                                    0 as jml_koreksi,
                                    0 as hrg_koreksi,
                                    sum(dsts.jumlah) as jml_pemakaian,
                                    dss.hrg_beli as hrg_pemakaian,
                                    sum(dsts.jumlah) * dss.hrg_beli as nominal_pemakaian
                                from det_stok_trans_siklus dsts
                                left join 
                                    det_stok_siklus dss
                                    on
                                        dsts.id_header = dss.id
                                where
                                    dss.jenis_barang = 'pakan' and
                                    dsts.tgl_trans between '".$start_date."' and '".$end_date."' and
                                    dsts.tbl_name = 'lhk'
                                group by
                                    dss.noreg, dsts.kode_trans, dss.hrg_beli

                                union all

                                select
                                    mi.noreg,
                                    m.no_mm as kode_trans,
                                    0 as jml_beli,
                                    0 as hrg_beli,
                                    0 as jml_mutasi_msk,
                                    0 as hrg_mutasi_msk,
                                    0 as jml_mutasi_klwr,
                                    0 as hrg_mutasi_klwr,
                                    0 as jml_koreksi,
                                    0 as hrg_koreksi,
                                    0 as jml_pemakaian,
                                    0 as hrg_pemakaian,
                                    case
                                        when mi.coa_asal = '71101.000' then
                                            0-mi.nilai
                                        else
                                            mi.nilai
                                    end as nominal_pemakaian
                                from mmitem mi
                                left join
                                    mm m
                                    on
                                        mi.no_mm = m.no_mm
                                where
                                    (mi.coa_asal = '71101.000' or mi.coa_tujuan = '71101.000')
                                    and m.tgl_mm between '".$start_date."' and '".$end_date."'
                            ) pemakaian
                        ) pkn
                        group by
                            pkn.noreg
                    ) pkn
                    left join
                        (
                            select
                                pkn.noreg,
                                isnull(sum(pkn.jml_debet * pkn.hrg_debet), 0) - isnull(sum(pkn.jml_kredit * pkn.hrg_kredit), 0) as saldo_awal
                            from (
                                select 
                                    dss.noreg,
                                    sum(dss.jumlah) as jml_debet,
                                    dss.hrg_beli as hrg_debet,
                                    0 as jml_kredit,
                                    0 as hrg_kredit
                                from det_stok_siklus dss
                                where
                                    dss.jenis_barang = 'pakan' and
                                    dss.tgl_trans < '".$start_date."'
                                group by
                                    dss.noreg, dss.hrg_beli
                                    
                                union all
                
                                select
                                    dss.noreg,
                                    0 as jml_debet,
                                    0 as hrg_debet,
                                    sum(dsts.jumlah) as jml_kredit,
                                    dss.hrg_beli as hrg_kredit
                                from det_stok_trans_siklus dsts
                                left join 
                                    det_stok_siklus dss
                                    on
                                        dsts.id_header = dss.id
                                where
                                    dss.jenis_barang = 'pakan' and
                                    dsts.tgl_trans < '".$start_date."'
                                group by
                                    dss.noreg, dss.hrg_beli
                            ) pkn
                            group by
                                pkn.noreg
                            having
                                isnull(sum(pkn.jml_debet * pkn.hrg_debet), 0) - isnull(sum(pkn.jml_kredit * pkn.hrg_kredit), 0) <> 0
                        ) sa
                        on
                            pkn.noreg = sa.noreg

                    union all

                    select
                        ovk.noreg,
                        0 as sa_pkn,
                        0 as beli_pkn,
                        0 as mutasi_msk_pkn,
                        0 as mutasi_klwr_pkn,
                        0 as koreksi_pkn,
                        0 as pemakaian_pkn,
                        0 as sisa_pkn,
                        isnull(sa.saldo_awal, 0) as sa_ovk,
                        ovk.beli_ovk,
                        ovk.mutasi_msk_ovk,
                        ovk.mutasi_klwr_ovk,
                        ovk.pemakaian_ovk,
                        0 as sa_doc,
                        0 as beli_doc,
                        0 as mutasi_msk_doc,
                        0 as mutasi_klwr_doc,
                        0 as koreksi_doc,
                        0 as pemakaian_doc,
                        0 as sa_oa,
                        0 as beli_oa,
                        0 as mutasi_msk_oa,
                        0 as mutasi_klwr_oa,
                        0 as koreksi_oa,
                        0 as pemakaian_oa,
                        0 as rhpp
                    from
                    (
                        select
                            ovk.noreg,
                            isnull(sum(ovk.jml_beli * ovk.hrg_beli), 0) as beli_ovk,
                            isnull(sum(ovk.jml_mutasi_msk * ovk.hrg_mutasi_msk), 0) as mutasi_msk_ovk,
                            isnull(sum(ovk.jml_mutasi_klwr * ovk.hrg_mutasi_klwr), 0) as mutasi_klwr_ovk,
                            isnull(sum(ovk.jml_beli * ovk.hrg_beli), 0) + isnull(sum(ovk.jml_mutasi_msk * ovk.hrg_mutasi_msk), 0) - isnull(sum(ovk.jml_mutasi_klwr * ovk.hrg_mutasi_klwr), 0) as pemakaian_ovk
                        from (
                            select 
                                dss.noreg,
                                dss.kode_trans,
                                sum(dss.jumlah) as jml_beli,
                                dss.hrg_beli,
                                0 as jml_mutasi_msk,
                                0 as hrg_mutasi_msk,
                                0 as jml_mutasi_klwr,
                                0 as hrg_mutasi_klwr,
                                0 as jml_pemakaian,
                                0 as hrg_pemakaian
                            from det_stok_siklus dss
                            left join
                                kirim_voadip kv
                                on
                                    dss.kode_trans = kv.no_order
                            where
                                dss.jenis_barang = 'voadip' and
                                dss.tgl_trans between '".$start_date."' and '".$end_date."' and
                                kv.jenis_kirim = 'opkg'
                            group by
                                dss.noreg, dss.kode_trans, dss.hrg_beli
    
                            union all
    
                            select 
                                dss.noreg,
                                dss.kode_trans,
                                0 as jml_beli,
                                0 as hrg_beli,
                                sum(dss.jumlah) as jml_mutasi_msk,
                                dss.hrg_beli as hrg_mutasi_msk,
                                0 as jml_mutasi_klwr,
                                0 as hrg_mutasi_klwr,
                                0 as jml_pemakaian,
                                0 as hrg_pemakaian
                            from det_stok_siklus dss
                            left join
                                kirim_voadip kv
                                on
                                    dss.kode_trans = kv.no_order
                            where
                                dss.jenis_barang = 'voadip' and
                                dss.tgl_trans between '".$start_date."' and '".$end_date."' and
                                kv.jenis_kirim = 'opkp'
                            group by
                                dss.noreg, dss.kode_trans, dss.hrg_beli
    
                            union all
    
                            select 
                                dss.noreg,
                                dsts.kode_trans,
                                0 as jml_beli,
                                0 as hrg_beli,
                                0 as jml_mutasi_msk,
                                0 as hrg_mutasi_msk,
                                sum(dsts.jumlah) as jml_mutasi_klwr,
                                dss.hrg_beli as hrg_mutasi_klwr,
                                0 as jml_pemakaian,
                                0 as hrg_pemakaian
                            from det_stok_trans_siklus dsts
                            left join 
                                det_stok_siklus dss
                                on
                                    dsts.id_header = dss.id
                            where
                                dss.jenis_barang = 'voadip' and
                                dsts.tgl_trans between '".$start_date."' and '".$end_date."' and
                                dsts.tbl_name <> 'lhk'
                            group by
                                dss.noreg, dsts.kode_trans, dss.hrg_beli
    
                            union all
    
                            select 
                                dss.noreg,
                                dsts.kode_trans,
                                0 as jml_beli,
                                0 as hrg_beli,
                                0 as jml_mutasi_msk,
                                0 as hrg_mutasi_msk,
                                0 as jml_mutasi_klwr,
                                0 as hrg_mutasi_klwr,
                                sum(dsts.jumlah) as jml_pemakaian,
                                dss.hrg_beli as hrg_pemakaian
                            from det_stok_trans_siklus dsts
                            left join 
                                det_stok_siklus dss
                                on
                                    dsts.id_header = dss.id
                            where
                                dss.jenis_barang = 'voadip' and
                                dsts.tgl_trans between '".$start_date."' and '".$end_date."' and
                                dsts.tbl_name = 'lhk'
                            group by
                                dss.noreg, dsts.kode_trans, dss.hrg_beli
                        ) ovk
                        group by
                            ovk.noreg
                    ) ovk
                    left join
                        (
                            
                            select
                                ovk.noreg,
                                isnull(sum(ovk.jml_debet * ovk.hrg_debet), 0) - isnull(sum(ovk.jml_kredit * ovk.hrg_kredit), 0) as saldo_awal
                            from (
                                select 
                                    dss.noreg,
                                    sum(dss.jumlah) as jml_debet,
                                    dss.hrg_beli as hrg_debet,
                                    0 as jml_kredit,
                                    0 as hrg_kredit
                                from det_stok_siklus dss
                                where
                                    dss.jenis_barang = 'voadip' and
                                    dss.tgl_trans < '".$start_date."'
                                group by
                                    dss.noreg, dss.hrg_beli
                    
                                union all
                    
                                select 
                                    dss.noreg,
                                    0 as jml_debet,
                                    0 as hrg_debet,
                                    sum(dsts.jumlah) as jml_kredit,
                                    dss.hrg_beli as hrg_kredit
                                from det_stok_trans_siklus dsts
                                left join 
                                    det_stok_siklus dss
                                    on
                                        dsts.id_header = dss.id
                                where
                                    dss.jenis_barang = 'voadip' and
                                    dsts.tgl_trans < '".$start_date."'
                                group by
                                    dss.noreg, dss.hrg_beli
                            ) ovk
                            group by
                                ovk.noreg
                        ) sa
                        on
                            sa.noreg = ovk.noreg

                    union all

                    select
                        doc.noreg,
                        0 as sa_pkn,
                        0 as beli_pkn,
                        0 as mutasi_msk_pkn,
                        0 as mutasi_klwr_pkn,
                        0 as koreksi_pkn,
                        0 as pemakaian_pkn,
                        0 as sisa_pkn,
                        0 as sa_ovk,
                        0 as beli_ovk,
                        0 as mutasi_msk_ovk,
                        0 as mutasi_klwr_ovk,
                        0 as pemakaian_ovk,
                        isnull(sa.saldo_awal, 0) as sa_doc,
                        doc.beli_doc,
                        doc.mutasi_msk_doc,
                        doc.mutasi_klwr_doc,
                        doc.koreksi_doc,
                        doc.pemakaian_doc,
                        0 as sa_oa,
                        0 as beli_oa,
                        0 as mutasi_msk_oa,
                        0 as mutasi_klwr_oa,
                        0 as koreksi_oa,
                        0 as pemakaian_oa,
                        0 as rhpp
                    from
                    (
                        select
                            doc.noreg,
                            isnull(sum(doc.jml_beli * doc.hrg_beli), 0) as beli_doc,
                            isnull(sum(doc.jml_mutasi_msk * doc.hrg_mutasi_msk), 0) as mutasi_msk_doc,
                            isnull(sum(doc.jml_mutasi_klwr * doc.hrg_mutasi_klwr), 0) as mutasi_klwr_doc,
                            isnull(sum(doc.jml_koreksi * doc.hrg_koreksi), 0) as koreksi_doc,
                            isnull(sum(doc.jml_beli * doc.hrg_beli), 0) + isnull(sum(doc.jml_mutasi_msk * doc.hrg_mutasi_msk), 0) - isnull(sum(doc.jml_mutasi_klwr * doc.hrg_mutasi_klwr), 0) + isnull(sum(doc.jml_koreksi * doc.hrg_koreksi), 0) as pemakaian_doc
                        from (
                            select 
                                dss.noreg,
                                dss.kode_trans,
                                sum(dss.jumlah) as jml_beli,
                                dss.hrg_beli,
                                0 as jml_mutasi_msk,
                                0 as hrg_mutasi_msk,
                                0 as jml_mutasi_klwr,
                                0 as hrg_mutasi_klwr,
                                0 as jml_koreksi,
                                0 as hrg_koreksi,
                                0 as jml_pemakaian,
                                0 as hrg_pemakaian
                            from det_stok_siklus dss
                            right join
                                (
                                    select max(id) as id, noreg, jenis_trans from det_stok_siklus where jenis_barang = 'doc' and tgl_trans between '".$start_date."' and '".$end_date."' group by noreg, jenis_trans
                                ) dss2
                                on
                                    dss.id = dss2.id
                            where
                                dss.jenis_barang = 'doc' and
                                dss.jenis_trans like 'ORDER' and
                                dss.tgl_trans between '".$start_date."' and '".$end_date."'
                            group by
                                dss.noreg, dss.kode_trans, dss.hrg_beli
    
                            union all
    
                            select 
                                dss.noreg,
                                dss.kode_trans,
                                0 as jml_beli,
                                0 as hrg_beli,
                                0 as jml_mutasi_msk,
                                0 as hrg_mutasi_msk,
                                0 as jml_mutasi_klwr,
                                0 as hrg_mutasi_klwr,
                                sum(dss.jumlah) as jml_koreksi,
                                dss.hrg_beli as hrg_koreksi,
                                0 as jml_pemakaian,
                                0 as hrg_pemakaian
                            from det_stok_siklus dss
                            right join
                                (
                                    select max(id) as id, noreg, jenis_trans from det_stok_siklus where jenis_barang = 'doc' and tgl_trans between '".$start_date."' and '".$end_date."' group by noreg, jenis_trans
                                ) dss2
                                on
                                    dss.id = dss2.id
                            where
                                dss.jenis_barang = 'doc' and
                                dss.jenis_trans not like 'ORDER' and
                                dss.tgl_trans between '".$start_date."' and '".$end_date."'
                            group by
                                dss.noreg, dss.kode_trans, dss.hrg_beli
                        ) doc
                        group by
                            doc.noreg
                    ) doc
                    left join
                        (
                            select
                                doc.noreg,
                                isnull(sum(doc.jml_debet * doc.hrg_debet), 0) - isnull(sum(doc.jml_kredit * doc.hrg_kredit), 0) as saldo_awal
                            from
                            (
                                select 
                                    dss.noreg,
                                    sum(dss.jumlah) as jml_debet,
                                    dss.hrg_beli as hrg_debet,
                                    0 as jml_kredit,
                                    0 as hrg_kredit
                                from det_stok_siklus dss
                                right join
                                    (
                                        select max(id) as id, noreg, jenis_trans from det_stok_siklus where jenis_barang = 'doc' and tgl_trans between '".$start_date."' and '".$end_date."' group by noreg, jenis_trans
                                    ) dss2
                                    on
                                        dss.id = dss2.id
                                where
                                    dss.jenis_barang = 'doc' and
                                    dss.jenis_trans like 'ORDER' and
                                    dss.tgl_trans < '".$start_date."'
                                group by
                                    dss.noreg, dss.hrg_beli
                    
                                union all
                    
                                select 
                                    dss.noreg,
                                    0 as jml_debet,
                                    0 as hrg_debet,
                                    sum(dss.jumlah) as jml_kredit,
                                    dss.hrg_beli as hrg_kredit
                                from det_stok_siklus dss
                                right join
                                    (
                                        select max(id) as id, noreg, jenis_trans from det_stok_siklus where jenis_barang = 'doc' and tgl_trans between '".$start_date."' and '".$end_date."' group by noreg, jenis_trans
                                    ) dss2
                                    on
                                        dss.id = dss2.id
                                where
                                    dss.jenis_barang = 'doc' and
                                    dss.jenis_trans not like 'ORDER' and
                                    dss.tgl_trans < '".$start_date."'
                                group by
                                    dss.noreg, dss.hrg_beli
                            ) doc
                            group by
                                doc.noreg
                        ) sa
                        on
                            doc.noreg = sa.noreg

                    union all

                    select
                        oa.noreg,
                        0 as sa_pkn,
                        0 as beli_pkn,
                        0 as mutasi_msk_pkn,
                        0 as mutasi_klwr_pkn,
                        0 as koreksi_pkn,
                        0 as pemakaian_pkn,
                        0 as sisa_pkn,
                        0 as sa_ovk,
                        0 as beli_ovk,
                        0 as mutasi_msk_ovk,
                        0 as mutasi_klwr_ovk,
                        0 as pemakaian_ovk,
                        0 as sa_doc,
                        0 as beli_doc,
                        0 as mutasi_msk_doc,
                        0 as mutasi_klwr_doc,
                        0 as koreksi_doc,
                        0 as pemakaian_doc,
                        isnull(sa.saldo_awal, 0) as sa_oa,
                        oa.beli_oa,
                        oa.mutasi_msk_oa,
                        oa.mutasi_klwr_oa,
                        oa.koreksi_oa,
                        ((oa.beli_oa + oa.mutasi_msk_oa + oa.koreksi_oa) - oa.mutasi_klwr_oa) as pemakaian_oa,
                        -- oa.pemakaian_oa,
                        0 as rhpp
                    from 
                    (
                        select
                            oa.noreg,
                            isnull(sum(oa.jml_beli * oa.hrg_beli), 0) as beli_oa,
                            isnull(sum(oa.jml_mutasi_msk * oa.hrg_mutasi_msk), 0) as mutasi_msk_oa,
                            isnull(sum(oa.jml_mutasi_klwr * oa.hrg_mutasi_klwr), 0) as mutasi_klwr_oa,
                            isnull(sum(oa.jml_koreksi * oa.hrg_koreksi), 0) as koreksi_oa,
                            0 as pemakaian_oa
                            -- isnull(sum(oa.jml_beli * oa.hrg_beli), 0) + isnull(sum(oa.jml_mutasi_msk * oa.hrg_mutasi_msk), 0) - isnull(sum(oa.jml_mutasi_klwr * oa.hrg_mutasi_klwr), 0) as pemakaian_oa
                        from (
                            select 
                                dss.noreg,
                                dss.kode_trans,
                                sum(dss.jumlah) as jml_beli,
                                dss.oa as hrg_beli,
                                0 as jml_mutasi_msk,
                                0 as hrg_mutasi_msk,
                                0 as jml_mutasi_klwr,
                                0 as hrg_mutasi_klwr,
                                0 as jml_koreksi,
                                0 as hrg_koreksi,
                                0 as jml_pemakaian,
                                0 as hrg_pemakaian
                            from det_stok_siklus dss
                            left join
                                kirim_pakan kp
                                on
                                    dss.kode_trans = kp.no_order
                            where
                                dss.jenis_barang = 'pakan' and
                                dss.tgl_trans between '".$start_date."' and '".$end_date."' and
                                kp.jenis_kirim = 'opkg'
                            group by
                                dss.noreg, dss.kode_trans, dss.oa
    
                            union all
    
                            select 
                                dss.noreg,
                                dss.kode_trans,
                                0 as jml_beli,
                                0 as hrg_beli,
                                sum(dss.jumlah) as jml_mutasi_msk,
                                dss.oa as hrg_mutasi_msk,
                                0 as jml_mutasi_klwr,
                                0 as hrg_mutasi_klwr,
                                0 as jml_koreksi,
                                0 as hrg_koreksi,
                                0 as jml_pemakaian,
                                0 as hrg_pemakaian
                            from det_stok_siklus dss
                            left join
                                kirim_pakan kp
                                on
                                    dss.kode_trans = kp.no_order
                            where
                                dss.jenis_barang = 'pakan' and
                                dss.tgl_trans between '".$start_date."' and '".$end_date."' and
                                kp.jenis_kirim = 'opkp'
                            group by
                                dss.noreg, dss.kode_trans, dss.oa
    
                            union all
    
                            select 
                                dss.noreg,
                                dsts.kode_trans,
                                0 as jml_beli,
                                0 as hrg_beli,
                                0 as jml_mutasi_msk,
                                0 as hrg_mutasi_msk,
                                sum(dsts.jumlah) as jml_mutasi_klwr,
                                dss.oa as hrg_mutasi_klwr,
                                0 as jml_koreksi,
                                0 as hrg_koreksi,
                                0 as jml_pemakaian,
                                0 as hrg_pemakaian
                            from det_stok_trans_siklus dsts
                            left join 
                                det_stok_siklus dss
                                on
                                    dsts.id_header = dss.id
                            left join
                                kirim_pakan kp
                                on
                                    dsts.kode_trans = kp.no_order
                            where
                                dss.jenis_barang = 'pakan' and
                                dsts.tgl_trans between '".$start_date."' and '".$end_date."' and
                                kp.jenis_kirim = 'opkp'
                            group by
                                dss.noreg, dsts.kode_trans, dss.oa

                            union all
    
                            select 
                                dss.noreg,
                                dsts.kode_trans,
                                0 as jml_beli,
                                0 as hrg_beli,
                                0 as jml_mutasi_msk,
                                0 as hrg_mutasi_msk,
                                0 as jml_mutasi_klwr,
                                0 as hrg_mutasi_klwr,
                                0-isnull(sum(dsts.jumlah), 0) as jml_koreksi,
                                dss.oa as hrg_koreksi,
                                0 as jml_pemakaian,
                                0 as hrg_pemakaian
                            from det_stok_trans_siklus dsts
                            left join 
                                det_stok_siklus dss
                                on
                                    dsts.id_header = dss.id
                            left join
                                retur_pakan rp
                                on
                                    dsts.kode_trans = rp.no_retur
                            where
                                dss.jenis_barang = 'pakan' and
                                dsts.tgl_trans between '".$start_date."' and '".$end_date."' and
                                rp.jenis_retur = 'opkp'
                            group by
                                dss.noreg, dsts.kode_trans, dss.oa
    
                            /*
                            union all
    
                            select 
                                dss.noreg,
                                dsts.kode_trans,
                                0 as jml_beli,
                                0 as hrg_beli,
                                0 as jml_mutasi_msk,
                                0 as hrg_mutasi_msk,
                                0 as jml_mutasi_klwr,
                                0 as hrg_mutasi_klwr,
                                0 as jml_koreksi,
                                0 as hrg_koreksi,
                                sum(dsts.jumlah) as jml_pemakaian,
                                dss.oa as hrg_pemakaian
                            from det_stok_trans_siklus dsts
                            left join 
                                det_stok_siklus dss
                                on
                                    dsts.id_header = dss.id
                            where
                                dss.jenis_barang = 'pakan' and
                                dsts.tgl_trans between '".$start_date."' and '".$end_date."' and
                                dsts.tbl_name = 'lhk'
                            group by
                                dss.noreg, dsts.kode_trans, dss.oa
                            */
                        ) oa
                        group by
                            oa.noreg
                    ) oa
                    left join
                        (
                            select
                                oa.noreg,
                                isnull(sum(oa.jml_debet * oa.hrg_debet), 0) - isnull(sum(oa.jml_kredit * oa.hrg_kredit), 0) as saldo_awal
                            from
                            (
                                select 
                                    dss.noreg,
                                    sum(dss.jumlah) as jml_debet,
                                    dss.oa as hrg_debet,
                                    0 as jml_kredit,
                                    0 as hrg_kredit
                                from det_stok_siklus dss
                                where
                                    dss.jenis_barang = 'pakan' and
                                    dss.tgl_trans < '".$start_date."'
                                group by
                                    dss.noreg, dss.oa
                    
                                union all
                    
                                select 
                                    dss.noreg,
                                    0 as jml_debet,
                                    0 as hrg_debet,
                                    sum(dsts.jumlah) as jml_kredit,
                                    dss.oa as hrg_kredit
                                from det_stok_trans_siklus dsts
                                left join 
                                    det_stok_siklus dss
                                    on
                                        dsts.id_header = dss.id
                                where
                                    dss.jenis_barang = 'pakan' and
                                    dsts.tbl_name <> 'lhk' and
                                    dsts.tgl_trans < '".$start_date."'
                                group by
                                    dss.noreg, dss.oa
                            ) oa
                            group by
                                oa.noreg
                        ) sa
                        on
                            oa.noreg = sa.noreg

                    union all

                    select
                        rhpp.noreg,
                        0 as sa_pkn,
                        0 as beli_pkn,
                        0 as mutasi_msk_pkn,
                        0 as mutasi_klwr_pkn,
                        0 as koreksi_pkn,
                        0 as pemakaian_pkn,
                        0 as sisa_pkn,
                        0 as sa_ovk,
                        0 as beli_ovk,
                        0 as mutasi_msk_ovk,
                        0 as mutasi_klwr_ovk,
                        0 as pemakaian_ovk,
                        0 as sa_doc,
                        0 as beli_doc,
                        0 as mutasi_msk_doc,
                        0 as mutasi_klwr_doc,
                        0 as koreksi_doc,
                        0 as pemakaian_doc,
                        0 as sa_oa,
                        0 as beli_oa,
                        0 as mutasi_msk_oa,
                        0 as mutasi_klwr_oa,
                        0 as koreksi_oa,
                        0 as pemakaian_oa,
                        rhpp.pdpt_peternak_belum_pajak as rhpp
                    from
                    (
                        select r.noreg, r.pdpt_peternak_belum_pajak 
                        from rhpp r 
                        left join
                            tutup_siklus ts
                            on
                                r.id_ts = ts.id
                        where 
                            ts.tgl_tutup between '".$start_date."' and '".$end_date."' and
                            r.jenis = 'rhpp_plasma' and 
                            not exists (select * from rhpp_group_noreg where noreg = r.noreg)

                        union all
                        
                        select rgn.noreg, rg.pdpt_peternak_belum_pajak from rhpp_group rg
                        left join
                            rhpp_group_header rgh
                            on
                                rg.id_header = rgh.id
                        left join
                            (
                                select 
                                    rgn.id_header, min(rgn.noreg) as noreg
                                from 
                                (
                                    select rgn.*, lhk.tanggal from rhpp_group_noreg rgn
                                    left join
                                        (
                                            select l1.* from lhk l1
                                            right join
                                                (select noreg, max(umur) as umur from lhk l group by noreg) l2
                                                on
                                                    l1.noreg = l2.noreg and
                                                    l1.umur = l2.umur
                                        ) lhk
                                        on
                                            lhk.noreg = rgn.noreg
                                ) rgn
                                right join
                                    (
                                        select rgn.id_header, max(lhk.tanggal) as tgl_akhir_siklus from rhpp_group_noreg rgn
                                        left join
                                            (
                                                select l1.* from lhk l1
                                                right join
                                                    (select noreg, max(umur) as umur from lhk l group by noreg) l2
                                                    on
                                                        l1.noreg = l2.noreg and
                                                        l1.umur = l2.umur
                                            ) lhk
                                            on
                                                lhk.noreg = rgn.noreg
                                        group by
                                            rgn.id_header
                                    ) rgn_max
                                    on
                                        rgn.id_header = rgn_max.id_header and
                                        rgn.tanggal = rgn_max.tgl_akhir_siklus
                                group by
                                    rgn.id_header
                            ) rgn
                            on
                                rg.id = rgn.id_header
                        where
                            rg.jenis = 'rhpp_plasma' and
                            rgh.tgl_submit between '".$start_date."' and '".$end_date."'
                    ) rhpp
                ) data
                left join
                    rdim_submit rs
                    on
                        data.noreg = rs.noreg
                left join
                    (
                        select mm1.* from mitra_mapping mm1
                        right join
                            (select max(id) as id, nim from mitra_mapping group by nim) mm2
                            on
                                mm1.id = mm2.id
                    ) mm
                    on
                        mm.nim = rs.nim
                left join
                    mitra m
                    on
                        m.id = mm.id
                /*
                left join
                    kandang k
                    on
                        k.mitra_mapping = mm.id and
                        k.kandang = cast(SUBSTRING(data.noreg, 10, 2) as int)
                */
                left join
                    kandang k
                    on
                        k.id = rs.kandang
                left join
                    wilayah w
                    on
                        k.unit = w.id
                left join
                    (
                        select od1.* from order_doc od1
                        right join
                            (select max(id) as id, no_order from order_doc group by no_order) od2
                            on
                                od1.id = od2.id
                    ) od
                    on
                        data.noreg = od.noreg
                left join
                    (
                        select td1.* from terima_doc td1
                        right join
                            (select max(id) as id, no_order from terima_doc group by no_order) td2
                            on
                                td1.id = td2.id
                    ) td
                    on
                        td.no_order = od.no_order
                /*
                left join
                    (
                        select r.noreg, r.pdpt_peternak_belum_pajak 
                        from rhpp r 
                        left join
                            tutup_siklus ts
                            on
                                r.id_ts = ts.id
                        where 
                            ts.tgl_tutup between '".$start_date."' and '".$end_date."' and
                            r.jenis = 'rhpp_plasma' and 
                            not exists (select * from rhpp_group_noreg where noreg = r.noreg)

                        union all
                        
                        select rgn.noreg, rg.pdpt_peternak_belum_pajak from rhpp_group rg
                        left join
                            rhpp_group_header rgh
                            on
                                rg.id_header = rgh.id
                        left join
                            (
                                select 
                                    rgn.id_header, min(rgn.noreg) as noreg
                                from 
                                (
                                    select rgn.*, lhk.tanggal from rhpp_group_noreg rgn
                                    left join
                                        (
                                            select l1.* from lhk l1
                                            right join
                                                (select noreg, max(umur) as umur from lhk l group by noreg) l2
                                                on
                                                    l1.noreg = l2.noreg and
                                                    l1.umur = l2.umur
                                        ) lhk
                                        on
                                            lhk.noreg = rgn.noreg
                                ) rgn
                                right join
                                    (
                                        select rgn.id_header, max(lhk.tanggal) as tgl_akhir_siklus from rhpp_group_noreg rgn
                                        left join
                                            (
                                                select l1.* from lhk l1
                                                right join
                                                    (select noreg, max(umur) as umur from lhk l group by noreg) l2
                                                    on
                                                        l1.noreg = l2.noreg and
                                                        l1.umur = l2.umur
                                            ) lhk
                                            on
                                                lhk.noreg = rgn.noreg
                                        group by
                                            rgn.id_header
                                    ) rgn_max
                                    on
                                        rgn.id_header = rgn_max.id_header and
                                        rgn.tanggal = rgn_max.tgl_akhir_siklus
                                group by
                                    rgn.id_header
                            ) rgn
                            on
                                rg.id = rgn.id_header
                        where
                            rg.jenis = 'rhpp_plasma' and
                            rgh.tgl_submit between '".$start_date."' and '".$end_date."'
                    ) rhpp_p
                    on
                        data.noreg = rhpp_p.noreg
                */
                where
                    m.id is not null
                    -- and td.id is not null
                    ".$sql_unit."
                group by
                    w.kode,
                    data.noreg,
                    m.nama,
                    td.datang,
                    rs.tgl_docin,
                    td.jml_ekor,
                    rs.populasi
                    -- , rhpp_p.pdpt_peternak_belum_pajak
            ) data
            order by
                data.noreg asc,
                data.tgl_chick_in asc
        ";

        /* QUERY BARU */
        // Untuk kolom Proporsi: awal & akhir bulan dari end_date
        $eom_end_date = date('Y-m-t', strtotime($end_date));
        $month_start_end_date = date('Y-m-01', strtotime($end_date));

        // Catatan: start_date_proporsi / end_date_proporsi / hari / proporsi sekarang dihitung sekali
        // di CTE noreg_hari_proporsi (bukan di sini lagi), supaya bisa dipakai juga untuk alokasi BL/BTL per unit.

        // BL (Biaya Langsung) / BTL (Biaya Tidak Langsung) - dibedakan cukup dari no. COA saja (gabungan
        // COA 60xxx dan 50xxx), tidak perlu lihat jenis mitra. COA 60151.000 (Biaya Pengangkutan)
        // dikecualikan total (tidak masuk BL atau BTL)
        $coa_bl = "'60801.000','60802.000','60803.000','60807.000','60807.001','60807.002','60807.003',
            '50101.000','50158.001','50300.000','50300.001','50300.002'";
        $coa_btl = "'60105.000','60151.001','60602.000','60604.000','60605.000','60854.000','60905.000','60922.000',
            '50102.000','50158.000','50602.000','50604.000','50605.000','50901.000','50905.000','50911.000','50922.001'";

        // Persentase Dijual = Terjual / Stock Tersedia (sisa_stok), dipakai untuk kolom Dijual (persentase x nilai Tersedia)
        // Kalau ekor panen (terjual) periode ini > stok tersedia, persentase dianggap 100% (jangan lebih dari 1)
        $persen_dijual = "
            case
                when data.ekor_panen_periode > ((data.populasi - data.ekor_mati_awal - data.ekor_panen_awal) - (data.ekor_mati - data.ekor_mati_awal)) then 1
                when ((data.populasi - data.ekor_mati_awal - data.ekor_panen_awal) - (data.ekor_mati - data.ekor_mati_awal)) <> 0
                then cast(data.ekor_panen_periode as float) / ((data.populasi - data.ekor_mati_awal - data.ekor_panen_awal) - (data.ekor_mati - data.ekor_mati_awal))
                else 0
            end
        ";

        // Saldo Awal = Saldo Akhir punya rumus yang sama, tapi datanya "sebelum start_date" (kumulatif dari awal).
        // persen_dijual_awal = ekor panen sebelum start_date / stock tersedia sebelum start_date (saldo_awal_stok)
        // Sama juga: kalau ekor panen > stok tersedia sebelum start_date, persentase dianggap 100%
        $persen_dijual_awal = "
            case
                when data.ekor_panen_awal > (data.populasi - data.ekor_mati_awal - data.ekor_panen_awal) then 1
                when (data.populasi - data.ekor_mati_awal - data.ekor_panen_awal) <> 0
                then cast(data.ekor_panen_awal as float) / (data.populasi - data.ekor_mati_awal - data.ekor_panen_awal)
                else 0
            end
        ";
        // RHPP sebelum start_date: pakai rumus Saldo Akhir RHPP biasa (bukan versi zero-out/selisih)
        $rhpp_awal_expr = "
            case
                when data.pdpt_peternak_awal <> 0 then data.pdpt_peternak_awal
                when data.is_panen_sebagian_awal = 1 then 5000 * data.populasi
                else data.pdpt_peternak_awal
            end
        ";
        // RHPP periode berjalan (dipakai untuk Produksi/Tersedia/Dijual/Saldo Akhir)
        $produksi_rhpp_expr = "
            case
                when data.pdpt_peternak <> 0 then data.pdpt_peternak
                when data.is_panen_sebagian = 1 then 5000 * data.populasi
                else data.pdpt_peternak
            end
        ";
        // TERSEDIA per kategori = Saldo Awal + Produksi. Dipakai ulang oleh Dijual & Saldo Akhir
        // supaya keduanya konsisten dengan definisi Tersedia yang sesungguhnya (bukan cuma Produksi).
        $sa_awal_doc_expr = "(data.pemakaian_doc_awal - (".$persen_dijual_awal.") * data.pemakaian_doc_awal)";
        $sa_awal_pakan_expr = "(data.pemakaian_pkn_awal - (".$persen_dijual_awal.") * data.pemakaian_pkn_awal)";
        $sa_awal_ovk_expr = "(data.pemakaian_ovk_awal - (".$persen_dijual_awal.") * data.pemakaian_ovk_awal)";
        $sa_awal_oa_expr = "(data.pemakaian_oa_awal - (".$persen_dijual_awal.") * data.pemakaian_oa_awal)";
        $sa_awal_rhpp_expr = "((".$rhpp_awal_expr.") - (".$persen_dijual_awal.") * (".$rhpp_awal_expr."))";

        $tersedia_doc_expr = "(".$sa_awal_doc_expr." + data.pemakaian_doc)";
        $tersedia_pakan_expr = "(".$sa_awal_pakan_expr." + data.pemakaian_pkn)";
        $tersedia_ovk_expr = "(".$sa_awal_ovk_expr." + data.pemakaian_ovk)";
        $tersedia_oa_expr = "(".$sa_awal_oa_expr." + data.pemakaian_oa)";
        $tersedia_rhpp_expr = "(".$sa_awal_rhpp_expr." + (".$produksi_rhpp_expr."))";
        // BL/BTL: Saldo Awal-nya masih 0, jadi Tersedia BL/BTL = Produksi BL/BTL = nilai dari det_jurnal
        $tersedia_bl_expr = "data.nilai_bl";
        $tersedia_btl_expr = "data.nilai_btl";
        $tersedia_total_expr = "(".$tersedia_doc_expr." + ".$tersedia_pakan_expr." + ".$tersedia_ovk_expr." + ".$tersedia_oa_expr."
            + ".$tersedia_bl_expr." + ".$tersedia_btl_expr." + ".$tersedia_rhpp_expr.")";

        $sql = "
            with
            -- ============================================================
            -- 1. REFERENCE CTEs (small lookup tables)
            -- ============================================================
            lhk_last as (
                select l1.*
                from lhk l1
                inner join (select noreg, max(umur) as umur from lhk group by noreg) l2
                    on l1.noreg = l2.noreg and l1.umur = l2.umur
            ),
            -- Ekor mati kumulatif per noreg per akhir periode (end_date) - dari lhk terakhir s/d end_date
            lhk_upto_end as (
                select l1.noreg, l1.ekor_mati
                from lhk l1
                inner join (
                    select noreg, max(umur) as umur from lhk where tanggal <= '".$end_date."' group by noreg
                ) l2 on l1.noreg = l2.noreg and l1.umur = l2.umur
            ),
            -- Ekor panen kumulatif per noreg s/d end_date
            panen_upto_end as (
                select noreg, sum(netto_ekor) as ekor_panen
                from real_sj
                where tgl_panen <= '".$end_date."'
                group by noreg
            ),
            -- Panen terakhir dalam bulan end_date (s/d end_date), untuk kolom Proporsi
            panen_bulan_enddate as (
                select noreg, max(tgl_panen) as tgl_panen_terakhir_bulan
                from real_sj
                where tgl_panen between '".$month_start_end_date."' and '".$end_date."'
                group by noreg
            ),
            -- Noreg yang sudah pernah panen tapi sebelum bulan terpilih (sudah habis dipanen bulan lalu), untuk Proporsi
            panen_sebelum_bulan as (
                select distinct noreg
                from real_sj
                where tgl_panen < '".$month_start_end_date."'
            ),
            -- Sama seperti lhk_upto_end/panen_upto_end, tapi filter < start_date, untuk Saldo Awal Stok
            lhk_upto_start as (
                select l1.noreg, l1.ekor_mati
                from lhk l1
                inner join (
                    select noreg, max(umur) as umur from lhk where tanggal < '".$start_date."' group by noreg
                ) l2 on l1.noreg = l2.noreg and l1.umur = l2.umur
            ),
            panen_upto_start as (
                select noreg, sum(netto_ekor) as ekor_panen
                from real_sj
                where tgl_panen < '".$start_date."'
                group by noreg
            ),
            -- Ekor panen di periode berjalan (start_date s/d end_date), untuk kolom Terjual
            panen_periode as (
                select noreg, sum(netto_ekor) as ekor_panen
                from real_sj
                where tgl_panen between '".$start_date."' and '".$end_date."'
                group by noreg
            ),
            kp_opkg as (
                select no_order from kirim_pakan
                where jenis_kirim = 'opkg' and tgl_kirim between '".prev_date($start_date)."' and '".next_date($end_date)."'
            ),
            kp_opkp as (
                select no_order from kirim_pakan
                where jenis_kirim = 'opkp' and tgl_kirim between '".prev_date($start_date)."' and '".next_date($end_date)."'
            ),
            kv_opkg as (
                select no_order from kirim_voadip
                where jenis_kirim = 'opkg' and tgl_kirim between '".prev_date($start_date)."' and '".next_date($end_date)."'
            ),
            kv_opkp as (
                select no_order from kirim_voadip
                where jenis_kirim = 'opkp' and tgl_kirim between '".prev_date($start_date)."' and '".next_date($end_date)."'
            ),
            -- Sama seperti kp_opkg/kp_opkp/kv_opkg/kv_opkp, tapi untuk data sebelum start_date (Saldo Awal)
            kp_opkg_awal as (
                select no_order from kirim_pakan where jenis_kirim = 'opkg' and tgl_kirim < '".$start_date."'
            ),
            kp_opkp_awal as (
                select no_order from kirim_pakan where jenis_kirim = 'opkp' and tgl_kirim < '".$start_date."'
            ),
            kv_opkg_awal as (
                select no_order from kirim_voadip where jenis_kirim = 'opkg' and tgl_kirim < '".$start_date."'
            ),
            kv_opkp_awal as (
                select no_order from kirim_voadip where jenis_kirim = 'opkp' and tgl_kirim < '".$start_date."'
            ),

            -- ============================================================
            -- 2. PERIOD DATA: 1 scan det_stok_siklus (pakan + voadip)
            -- ============================================================
            dss_period as (
                select dss.noreg,
                    sum(case when dss.jenis_barang = 'pakan' and kp_opkg.no_order is not null
                        then dss.jumlah * dss.hrg_beli else 0 end) as beli_pkn,
                    sum(case when dss.jenis_barang = 'pakan' and kp_opkp.no_order is not null
                        then dss.jumlah * dss.hrg_beli else 0 end) as mutasi_msk_pkn,
                    sum(case when dss.jenis_barang = 'voadip' and kv_opkg.no_order is not null
                        then dss.jumlah * dss.hrg_beli else 0 end) as beli_ovk,
                    sum(case when dss.jenis_barang = 'voadip' and kv_opkp.no_order is not null
                        then dss.jumlah * dss.hrg_beli else 0 end) as mutasi_msk_ovk,
                    sum(case when dss.jenis_barang = 'pakan' and kp_opkg.no_order is not null
                        then dss.jumlah * dss.oa else 0 end) as beli_oa,
                    sum(case when dss.jenis_barang = 'pakan' and kp_opkp.no_order is not null
                        then dss.jumlah * dss.oa else 0 end) as mutasi_msk_oa
                from det_stok_siklus dss
                left join kp_opkg on dss.kode_trans = kp_opkg.no_order and dss.jenis_barang = 'pakan'
                left join kp_opkp on dss.kode_trans = kp_opkp.no_order and dss.jenis_barang = 'pakan'
                left join kv_opkg on dss.kode_trans = kv_opkg.no_order and dss.jenis_barang = 'voadip'
                left join kv_opkp on dss.kode_trans = kv_opkp.no_order and dss.jenis_barang = 'voadip'
                where dss.tgl_trans between '".$start_date."' and '".$end_date."'
                    and dss.jenis_barang in ('pakan', 'voadip')
                group by dss.noreg
            ),
            -- Sama seperti dss_period, tapi sebelum start_date (Saldo Awal) - hanya kolom yang dipakai untuk OVK/OA
            dss_awal as (
                select dss.noreg,
                    sum(case when dss.jenis_barang = 'voadip' and kv_opkg_awal.no_order is not null
                        then dss.jumlah * dss.hrg_beli else 0 end) as beli_ovk,
                    sum(case when dss.jenis_barang = 'voadip' and kv_opkp_awal.no_order is not null
                        then dss.jumlah * dss.hrg_beli else 0 end) as mutasi_msk_ovk,
                    sum(case when dss.jenis_barang = 'pakan' and kp_opkg_awal.no_order is not null
                        then dss.jumlah * dss.oa else 0 end) as beli_oa,
                    sum(case when dss.jenis_barang = 'pakan' and kp_opkp_awal.no_order is not null
                        then dss.jumlah * dss.oa else 0 end) as mutasi_msk_oa
                from det_stok_siklus dss
                left join kp_opkg_awal on dss.kode_trans = kp_opkg_awal.no_order and dss.jenis_barang = 'pakan'
                left join kp_opkp_awal on dss.kode_trans = kp_opkp_awal.no_order and dss.jenis_barang = 'pakan'
                left join kv_opkg_awal on dss.kode_trans = kv_opkg_awal.no_order and dss.jenis_barang = 'voadip'
                left join kv_opkp_awal on dss.kode_trans = kv_opkp_awal.no_order and dss.jenis_barang = 'voadip'
                where dss.tgl_trans < '".$start_date."'
                    and dss.jenis_barang in ('pakan', 'voadip')
                group by dss.noreg
            ),

            -- ============================================================
            -- 3. PERIOD DATA: 1 scan det_stok_trans_siklus (pakan + voadip)
            -- ============================================================
            dsts_period as (
                select dss.noreg,
                    sum(case when dss.jenis_barang = 'pakan' and kp_opkp.no_order is not null
                        then dsts.jumlah * dss.hrg_beli else 0 end) as mutasi_klwr_pkn,
                    sum(case when dss.jenis_barang = 'pakan' and rp.no_retur is not null
                        then -dsts.jumlah * dss.hrg_beli else 0 end) as koreksi_pkn,
                    sum(case when dss.jenis_barang = 'pakan' and dsts.tbl_name = 'lhk'
                        then dsts.jumlah * dss.hrg_beli else 0 end) as pemakaian_pkn,
                    sum(case when dss.jenis_barang = 'voadip' and dsts.tbl_name <> 'lhk'
                        then dsts.jumlah * dss.hrg_beli else 0 end) as mutasi_klwr_ovk,
                    sum(case when dss.jenis_barang = 'voadip' and dsts.tbl_name = 'lhk'
                        then dsts.jumlah * dss.hrg_beli else 0 end) as pemakaian_ovk,
                    sum(case when dss.jenis_barang = 'pakan' and kp_opkp.no_order is not null
                        then dsts.jumlah * dss.oa else 0 end) as mutasi_klwr_oa,
                    sum(case when dss.jenis_barang = 'pakan' and rp.no_retur is not null
                        then -dsts.jumlah * dss.oa else 0 end) as koreksi_oa
                from det_stok_trans_siklus dsts
                inner join det_stok_siklus dss on dsts.id_header = dss.id
                left join kp_opkp on dsts.kode_trans = kp_opkp.no_order
                left join retur_pakan rp on dsts.kode_trans = rp.no_retur and rp.jenis_retur = 'opkp'
                where dsts.tgl_trans between '".$start_date."' and '".$end_date."'
                    and dss.jenis_barang in ('pakan', 'voadip')
                group by dss.noreg
            ),
            -- Sama seperti dsts_period, tapi sebelum start_date (Saldo Awal) - hanya kolom yang dipakai
            dsts_awal as (
                select dss.noreg,
                    sum(case when dss.jenis_barang = 'pakan' and dsts.tbl_name = 'lhk'
                        then dsts.jumlah * dss.hrg_beli else 0 end) as pemakaian_pkn,
                    sum(case when dss.jenis_barang = 'voadip' and dsts.tbl_name <> 'lhk'
                        then dsts.jumlah * dss.hrg_beli else 0 end) as mutasi_klwr_ovk,
                    sum(case when dss.jenis_barang = 'pakan' and kp_opkp_awal.no_order is not null
                        then dsts.jumlah * dss.oa else 0 end) as mutasi_klwr_oa,
                    sum(case when dss.jenis_barang = 'pakan' and rp.no_retur is not null
                        then -dsts.jumlah * dss.oa else 0 end) as koreksi_oa
                from det_stok_trans_siklus dsts
                inner join det_stok_siklus dss on dsts.id_header = dss.id
                left join kp_opkp_awal on dsts.kode_trans = kp_opkp_awal.no_order
                left join retur_pakan rp on dsts.kode_trans = rp.no_retur and rp.jenis_retur = 'opkp'
                where dsts.tgl_trans < '".$start_date."'
                    and dss.jenis_barang in ('pakan', 'voadip')
                group by dss.noreg
            ),

            -- ============================================================
            -- 4. MM pemakaian (1 scan mmitem)
            -- ============================================================
            mm_agg as (
                select mi.noreg,
                    sum(case when mi.coa_asal = '71101.000' then -mi.nilai else mi.nilai end) as mm_pemakaian_pkn
                from mmitem mi
                inner join mm m on mi.no_mm = m.no_mm
                where (mi.coa_asal = '71101.000' or mi.coa_tujuan = '71101.000')
                    and m.tgl_mm between '".$start_date."' and '".$end_date."'
                group by mi.noreg
            ),
            -- Sama seperti mm_agg, tapi sebelum start_date (Saldo Awal)
            mm_awal as (
                select mi.noreg,
                    sum(case when mi.coa_asal = '71101.000' then -mi.nilai else mi.nilai end) as mm_pemakaian_pkn
                from mmitem mi
                inner join mm m on mi.no_mm = m.no_mm
                where (mi.coa_asal = '71101.000' or mi.coa_tujuan = '71101.000')
                    and m.tgl_mm < '".$start_date."'
                group by mi.noreg
            ),

            -- ============================================================
            -- 5. DOC: window function (1 scan det_stok_siklus)
            -- ============================================================
            doc_ranked as (
                select *, row_number() over (partition by noreg, jenis_trans order by id desc) as rn
                from det_stok_siklus
                where jenis_barang = 'doc' and tgl_trans between '".$start_date."' and '".$end_date."'
            ),
            doc_period as (
                select noreg,
                    sum(case when jenis_trans like 'ORDER' then jumlah * hrg_beli else 0 end) as beli_doc,
                    sum(case when jenis_trans not like 'ORDER' then jumlah * hrg_beli else 0 end) as koreksi_doc
                from doc_ranked
                where rn = 1
                group by noreg
            ),
            doc_ranked_before as (
                select *, row_number() over (partition by noreg, jenis_trans order by id desc) as rn
                from det_stok_siklus
                where jenis_barang = 'doc' and tgl_trans < '".$start_date."'
            ),
            doc_saldo as (
                select noreg,
                    sum(case when jenis_trans like 'ORDER' then jumlah * hrg_beli else 0 end) as doc_debet,
                    sum(case when jenis_trans not like 'ORDER' then jumlah * hrg_beli else 0 end) as doc_kredit
                from doc_ranked_before
                where rn = 1
                group by noreg
            ),
            -- Pemakaian DOC sebelum start_date (Saldo Awal), pakai doc_ranked_before yang sama
            doc_period_awal as (
                select noreg,
                    sum(case when jenis_trans like 'ORDER' then jumlah * hrg_beli else 0 end) as beli_doc,
                    sum(case when jenis_trans not like 'ORDER' then jumlah * hrg_beli else 0 end) as koreksi_doc
                from doc_ranked_before
                where rn = 1
                group by noreg
            ),

            -- ============================================================
            -- 6. PRE-PERIOD SALDO: 1 scan per table
            -- ============================================================
            sa_dss as (
                select noreg,
                    sum(case when jenis_barang = 'pakan' then jumlah * hrg_beli else 0 end) as pkn_debet,
                    sum(case when jenis_barang = 'voadip' then jumlah * hrg_beli else 0 end) as ovk_debet,
                    sum(case when jenis_barang = 'pakan' then jumlah * oa else 0 end) as oa_debet
                from det_stok_siklus
                where tgl_trans < '".$start_date."' and jenis_barang in ('pakan', 'voadip')
                group by noreg
            ),
            sa_dsts as (
                select dss.noreg,
                    sum(case when dss.jenis_barang = 'pakan' then dsts.jumlah * dss.hrg_beli else 0 end) as pkn_kredit,
                    sum(case when dss.jenis_barang = 'voadip' then dsts.jumlah * dss.hrg_beli else 0 end) as ovk_kredit,
                    sum(case when dss.jenis_barang = 'pakan' and dsts.tbl_name <> 'lhk' then dsts.jumlah * dss.oa else 0 end) as oa_kredit
                from det_stok_trans_siklus dsts
                inner join det_stok_siklus dss on dsts.id_header = dss.id
                where dsts.tgl_trans < '".$start_date."' and dss.jenis_barang in ('pakan', 'voadip')
                group by dss.noreg
            ),
            sa_pkn as (
                select coalesce(d.noreg, k.noreg) as noreg,
                    coalesce(d.pkn_debet, 0) - coalesce(k.pkn_kredit, 0) as saldo_awal
                from sa_dss d full outer join sa_dsts k on d.noreg = k.noreg
                where coalesce(d.pkn_debet, 0) - coalesce(k.pkn_kredit, 0) <> 0
            ),
            sa_ovk as (
                select coalesce(d.noreg, k.noreg) as noreg,
                    coalesce(d.ovk_debet, 0) - coalesce(k.ovk_kredit, 0) as saldo_awal
                from sa_dss d full outer join sa_dsts k on d.noreg = k.noreg
                where coalesce(d.ovk_debet, 0) - coalesce(k.ovk_kredit, 0) <> 0
            ),
            sa_oa as (
                select coalesce(d.noreg, k.noreg) as noreg,
                    coalesce(d.oa_debet, 0) - coalesce(k.oa_kredit, 0) as saldo_awal
                from sa_dss d full outer join sa_dsts k on d.noreg = k.noreg
                where coalesce(d.oa_debet, 0) - coalesce(k.oa_kredit, 0) <> 0
            ),

            -- ============================================================
            -- 7. RHPP
            -- ============================================================
            rhpp_data as (
                select r.noreg, r.pdpt_peternak_belum_pajak
                from rhpp r
                inner join tutup_siklus ts on r.id_ts = ts.id
                where ts.tgl_tutup between '".$start_date."' and '".$end_date."'
                    and r.jenis = 'rhpp_plasma'
                    and not exists (select * from rhpp_group_noreg where noreg = r.noreg)

                union all

                select rgn.noreg, rg.pdpt_peternak_belum_pajak
                from rhpp_group rg
                inner join rhpp_group_header rgh on rg.id_header = rgh.id
                inner join (
                    select rgn.id_header, min(rgn.noreg) as noreg
                    from (
                        select rgn.*, lhk.tanggal
                        from rhpp_group_noreg rgn
                        left join lhk_last lhk on lhk.noreg = rgn.noreg
                    ) rgn
                    inner join (
                        select rgn.id_header, max(lhk.tanggal) as tgl_akhir_siklus
                        from rhpp_group_noreg rgn
                        left join lhk_last lhk on lhk.noreg = rgn.noreg
                        group by rgn.id_header
                    ) rgn_max on rgn.id_header = rgn_max.id_header and rgn.tanggal = rgn_max.tgl_akhir_siklus
                    group by rgn.id_header
                ) rgn on rg.id = rgn.id_header
                where rg.jenis = 'rhpp_plasma' and rgh.tgl_submit between '".$start_date."' and '".$end_date."'
            ),
            -- Sama seperti rhpp_data, tapi sebelum start_date (Saldo Awal)
            rhpp_data_awal as (
                select r.noreg, r.pdpt_peternak_belum_pajak
                from rhpp r
                inner join tutup_siklus ts on r.id_ts = ts.id
                where ts.tgl_tutup < '".$start_date."'
                    and r.jenis = 'rhpp_plasma'
                    and not exists (select * from rhpp_group_noreg where noreg = r.noreg)

                union all

                select rgn.noreg, rg.pdpt_peternak_belum_pajak
                from rhpp_group rg
                inner join rhpp_group_header rgh on rg.id_header = rgh.id
                inner join (
                    select rgn.id_header, min(rgn.noreg) as noreg
                    from (
                        select rgn.*, lhk.tanggal
                        from rhpp_group_noreg rgn
                        left join lhk_last lhk on lhk.noreg = rgn.noreg
                    ) rgn
                    inner join (
                        select rgn.id_header, max(lhk.tanggal) as tgl_akhir_siklus
                        from rhpp_group_noreg rgn
                        left join lhk_last lhk on lhk.noreg = rgn.noreg
                        group by rgn.id_header
                    ) rgn_max on rgn.id_header = rgn_max.id_header and rgn.tanggal = rgn_max.tgl_akhir_siklus
                    group by rgn.id_header
                ) rgn on rg.id = rgn.id_header
                where rg.jenis = 'rhpp_plasma' and rgh.tgl_submit < '".$start_date."'
            ),

            -- Noreg yang belum tutup siklus per akhir periode (end_date) tapi sudah ada panen sebagian (real_sj) di periode ini
            panen_sebagian as (
                select distinct rs.noreg
                from real_sj rs
                where rs.tgl_panen between '".$start_date."' and '".$end_date."'
                    and not exists (
                        select 1 from tutup_siklus ts
                        where ts.noreg = rs.noreg and ts.tgl_tutup <= '".$end_date."'
                    )
            ),
            -- Sama seperti panen_sebagian, tapi dicek per awal periode (sebelum start_date) untuk Saldo Awal
            panen_sebagian_awal as (
                select distinct rs.noreg
                from real_sj rs
                where rs.tgl_panen < '".$start_date."'
                    and not exists (
                        select 1 from tutup_siklus ts
                        where ts.noreg = rs.noreg and ts.tgl_tutup < '".$start_date."'
                    )
            ),

            -- ============================================================
            -- 8. HELPERS for outer joins
            -- ============================================================
            mitra_mapping_max as (
                select mm1.*
                from mitra_mapping mm1
                inner join (select max(id) as id, nim from mitra_mapping group by nim) mm2
                    on mm1.id = mm2.id
            ),
            order_doc_max as (
                select od1.*
                from order_doc od1
                inner join (select max(id) as id, no_order from order_doc group by no_order) od2
                    on od1.id = od2.id
            ),
            terima_doc_max as (
                select td1.*
                from terima_doc td1
                inner join (select max(id) as id, no_order from terima_doc group by no_order) td2
                    on td1.id = td2.id
            ),
            -- BL/BTL per noreg dari det_jurnal (coa_tujuan), sesuai periode berjalan, dibedakan cukup dari no. COA
            det_jurnal_bl_btl as (
                select dj.noreg,
                    sum(case when dj.coa_tujuan in (".$coa_bl.") then dj.nominal else 0 end) as nilai_bl,
                    sum(case when dj.coa_tujuan in (".$coa_btl.") then dj.nominal else 0 end) as nilai_btl
                from det_jurnal dj
                where dj.tanggal between '".$start_date."' and '".$end_date."'
                    and dj.noreg is not null
                group by dj.noreg
            ),

            -- ============================================================
            -- 9. ALL UNIQUE NOREG (hanya yang punya data transaksi periode)
            all_noreg as (
                select noreg from dss_period
                union select noreg from dsts_period
                union select noreg from doc_period
                union select noreg from rhpp_data
            ),

            -- ============================================================
            -- 10. COMBINED DATA (all metrics per noreg, one LEFT JOIN per source)
            -- ============================================================
            combined as (
                select
                    n.noreg,
                    -- PKN
                    coalesce(sa_pkn.saldo_awal, 0) as sa_pkn,
                    coalesce(dss.beli_pkn, 0) as beli_pkn,
                    coalesce(dss.mutasi_msk_pkn, 0) as mutasi_msk_pkn,
                    coalesce(dsts.mutasi_klwr_pkn, 0) as mutasi_klwr_pkn,
                    coalesce(dsts.koreksi_pkn, 0) as koreksi_pkn,
                    coalesce(dsts.pemakaian_pkn, 0) + coalesce(mm.mm_pemakaian_pkn, 0) as pemakaian_pkn,
                    coalesce(dss.beli_pkn, 0) + coalesce(dss.mutasi_msk_pkn, 0)
                        - coalesce(dsts.mutasi_klwr_pkn, 0) - coalesce(dsts.pemakaian_pkn, 0)
                        - coalesce(mm.mm_pemakaian_pkn, 0) + coalesce(dsts.koreksi_pkn, 0) as sisa_pkn,
                    -- OVK
                    coalesce(sa_ovk.saldo_awal, 0) as sa_ovk,
                    coalesce(dss.beli_ovk, 0) as beli_ovk,
                    coalesce(dss.mutasi_msk_ovk, 0) as mutasi_msk_ovk,
                    coalesce(dsts.mutasi_klwr_ovk, 0) as mutasi_klwr_ovk,
                    coalesce(dss.beli_ovk, 0) 
                        + coalesce(dss.mutasi_msk_ovk, 0) - coalesce(dsts.mutasi_klwr_ovk, 0) as pemakaian_ovk,
                    -- coalesce(dsts.pemakaian_ovk, 0) as pemakaian_ovk,
                    -- DOC
                    coalesce(sa_doc.doc_debet, 0) - coalesce(sa_doc.doc_kredit, 0) as sa_doc,
                    coalesce(doc.beli_doc, 0) as beli_doc,
                    0 as mutasi_msk_doc,
                    0 as mutasi_klwr_doc,
                    coalesce(doc.koreksi_doc, 0) as koreksi_doc,
                    coalesce(doc.beli_doc, 0) + coalesce(doc.koreksi_doc, 0) as pemakaian_doc,
                    -- OA
                    coalesce(sa_oa.saldo_awal, 0) as sa_oa,
                    coalesce(dss.beli_oa, 0) as beli_oa,
                    coalesce(dss.mutasi_msk_oa, 0) as mutasi_msk_oa,
                    coalesce(dsts.mutasi_klwr_oa, 0) as mutasi_klwr_oa,
                    coalesce(dsts.koreksi_oa, 0) as koreksi_oa,
                    coalesce(dss.beli_oa, 0) + coalesce(dss.mutasi_msk_oa, 0)
                        + coalesce(dsts.koreksi_oa, 0) - coalesce(dsts.mutasi_klwr_oa, 0) as pemakaian_oa,
                    -- RHPP
                    coalesce(rhpp.pdpt_peternak_belum_pajak, 0) as pdpt_peternak
                from all_noreg n
                left join dss_period dss on n.noreg = dss.noreg
                left join dsts_period dsts on n.noreg = dsts.noreg
                left join mm_agg mm on n.noreg = mm.noreg
                left join doc_period doc on n.noreg = doc.noreg
                left join sa_pkn on n.noreg = sa_pkn.noreg
                left join sa_ovk on n.noreg = sa_ovk.noreg
                left join sa_oa on n.noreg = sa_oa.noreg
                left join doc_saldo sa_doc on n.noreg = sa_doc.noreg
                left join rhpp_data rhpp on n.noreg = rhpp.noreg
            ),

            -- ============================================================
            -- 10b. PROPORSI per noreg (dipakai untuk kolom Proporsi & alokasi BL/BTL per unit)
            -- ============================================================
            noreg_base as (
                select c.noreg,
                    w.kode as unit,
                    case when td.datang is not null then td.datang else rs.tgl_docin end as tgl_chick_in,
                    case when td.jml_ekor is not null then td.jml_ekor else rs.populasi end as populasi
                from combined c
                left join rdim_submit rs on c.noreg = rs.noreg
                left join mitra_mapping_max mm on mm.nim = rs.nim
                left join mitra m on m.id = mm.id
                left join kandang k on k.id = rs.kandang
                left join wilayah w on k.unit = w.id
                left join order_doc_max od on c.noreg = od.noreg
                left join terima_doc_max td on td.no_order = od.no_order
                where m.id is not null
            ),
            noreg_hari_proporsi as (
                select nb.noreg, nb.unit, nb.populasi,
                    (case
                        when psb.noreg is not null then null
                        when nb.tgl_chick_in > '".$eom_end_date."' then null
                        when '".$start_date."' < nb.tgl_chick_in then nb.tgl_chick_in
                        else '".$start_date."'
                    end) as start_date_proporsi,
                    (case
                        when psb.noreg is not null then null
                        when nb.tgl_chick_in > '".$eom_end_date."' then null
                        when pbe.tgl_panen_terakhir_bulan is not null then pbe.tgl_panen_terakhir_bulan
                        else '".$eom_end_date."'
                    end) as end_date_proporsi,
                    isnull(datediff(day,
                        (case
                            when psb.noreg is not null then null
                            when nb.tgl_chick_in > '".$eom_end_date."' then null
                            when '".$start_date."' < nb.tgl_chick_in then nb.tgl_chick_in
                            else '".$start_date."'
                        end),
                        (case
                            when psb.noreg is not null then null
                            when nb.tgl_chick_in > '".$eom_end_date."' then null
                            when pbe.tgl_panen_terakhir_bulan is not null then pbe.tgl_panen_terakhir_bulan
                            else '".$eom_end_date."'
                        end)
                    ), 0) as hari,
                    isnull(datediff(day,
                        (case
                            when psb.noreg is not null then null
                            when nb.tgl_chick_in > '".$eom_end_date."' then null
                            when '".$start_date."' < nb.tgl_chick_in then nb.tgl_chick_in
                            else '".$start_date."'
                        end),
                        (case
                            when psb.noreg is not null then null
                            when nb.tgl_chick_in > '".$eom_end_date."' then null
                            when pbe.tgl_panen_terakhir_bulan is not null then pbe.tgl_panen_terakhir_bulan
                            else '".$eom_end_date."'
                        end)
                    ), 0) * nb.populasi as proporsi
                from noreg_base nb
                left join panen_bulan_enddate pbe on pbe.noreg = nb.noreg
                left join panen_sebelum_bulan psb on psb.noreg = nb.noreg
            ),
            unit_proporsi_total as (
                select unit, sum(proporsi) as total_proporsi
                from noreg_hari_proporsi
                group by unit
            ),
            -- BL/BTL yang di-posting di level unit (noreg null), untuk dialokasikan ke tiap noreg pakai proporsi
            det_jurnal_bl_btl_pool as (
                select dj.unit,
                    sum(case when dj.coa_tujuan in (".$coa_bl.") then dj.nominal else 0 end) as pool_bl,
                    sum(case when dj.coa_tujuan in (".$coa_btl.") then dj.nominal else 0 end) as pool_btl
                from det_jurnal dj
                where dj.tanggal between '".$start_date."' and '".$end_date."'
                    and dj.noreg is null
                group by dj.unit
            )
            -- ============================================================
            -- 11. FINAL SELECT
            -- ============================================================
            select
                data.unit,
                data.noreg,
                data.nama,
                data.jenis_mitra,
                data.tgl_chick_in,
                data.populasi,
                -- SALDO AWAL (grup Saldo Awal di view) = rumus Saldo Akhir (Tersedia - Dijual),
                -- tapi datanya dihitung sebelum start_date (kumulatif dari awal)
                (".$sa_awal_doc_expr.") as sa_awal_doc,
                (".$sa_awal_pakan_expr.") as sa_awal_pakan,
                (".$sa_awal_ovk_expr.") as sa_awal_ovk,
                (".$sa_awal_oa_expr.") as sa_awal_oa,
                0 as sa_awal_bl,
                0 as sa_awal_btl,
                (".$sa_awal_rhpp_expr.") as sa_awal_rhpp,
                (".$sa_awal_doc_expr." + ".$sa_awal_pakan_expr." + ".$sa_awal_ovk_expr." + ".$sa_awal_oa_expr." + ".$sa_awal_rhpp_expr.") as sa_awal_total,
                -- PRODUKSI (grup Produksi di view, sesuai periode start_date s/d end_date)
                data.pemakaian_doc as produksi_doc,
                data.pemakaian_pkn as produksi_pakan,
                data.pemakaian_ovk as produksi_ovk,
                data.pemakaian_oa as produksi_oa,
                data.nilai_bl as produksi_bl,
                data.nilai_btl as produksi_btl,
                -- RHPP: normal dari tutup siklus/rhpp_group; kalau belum tutup siklus tapi sudah ada
                -- panen sebagian (real_sj) di periode ini, dipakai estimasi 5000 * populasi
                (".$produksi_rhpp_expr.") as produksi_rhpp,
                (data.pemakaian_doc + data.pemakaian_pkn + data.pemakaian_ovk + data.pemakaian_oa
                    + data.nilai_bl + data.nilai_btl
                    + (".$produksi_rhpp_expr.")) as produksi_total,
                -- TERSEDIA (grup Tersedia di view) = Saldo Awal + Produksi
                (".$tersedia_doc_expr.") as tersedia_doc,
                (".$tersedia_pakan_expr.") as tersedia_pakan,
                (".$tersedia_ovk_expr.") as tersedia_ovk,
                (".$tersedia_oa_expr.") as tersedia_oa,
                (".$tersedia_bl_expr.") as tersedia_bl,
                (".$tersedia_btl_expr.") as tersedia_btl,
                (".$tersedia_rhpp_expr.") as tersedia_rhpp,
                (".$tersedia_total_expr.") as tersedia_total,
                -- DIJUAL (grup Dijual di view) = persentase (Terjual / Stock Tersedia) x nilai Tersedia
                (".$persen_dijual.") * (".$tersedia_doc_expr.") as dijual_doc,
                (".$persen_dijual.") * (".$tersedia_pakan_expr.") as dijual_pakan,
                (".$persen_dijual.") * (".$tersedia_ovk_expr.") as dijual_ovk,
                (".$persen_dijual.") * (".$tersedia_oa_expr.") as dijual_oa,
                (".$persen_dijual.") * (".$tersedia_bl_expr.") as dijual_bl,
                (".$persen_dijual.") * (".$tersedia_btl_expr.") as dijual_btl,
                (".$persen_dijual.") * (".$tersedia_rhpp_expr.") as dijual_rhpp,
                (".$persen_dijual.") * (".$tersedia_total_expr.") as dijual_total,
                -- TERJUAL (ekor) = ekor panen di periode berjalan (start_date s/d end_date)
                data.ekor_panen_periode as terjual,
                -- PERSENTASE = Terjual / Stock Tersedia x 100
                (".$persen_dijual.") * 100 as persentase_dijual,
                -- START/END DATE PROPORSI, HARI & PROPORSI (sudah dihitung sekali di noreg_hari_proporsi)
                data.start_date_proporsi,
                data.end_date_proporsi,
                data.hari,
                data.proporsi,
                -- SALDO AKHIR (grup Saldo Akhir di view) = Tersedia - Dijual
                ((".$tersedia_doc_expr.") - (".$persen_dijual.") * (".$tersedia_doc_expr.")) as saldo_akhir_doc,
                ((".$tersedia_pakan_expr.") - (".$persen_dijual.") * (".$tersedia_pakan_expr.")) as saldo_akhir_pakan,
                ((".$tersedia_ovk_expr.") - (".$persen_dijual.") * (".$tersedia_ovk_expr.")) as saldo_akhir_ovk,
                ((".$tersedia_oa_expr.") - (".$persen_dijual.") * (".$tersedia_oa_expr.")) as saldo_akhir_oa,
                ((".$tersedia_bl_expr.") - (".$persen_dijual.") * (".$tersedia_bl_expr.")) as saldo_akhir_bl,
                ((".$tersedia_btl_expr.") - (".$persen_dijual.") * (".$tersedia_btl_expr.")) as saldo_akhir_btl,
                ((".$tersedia_rhpp_expr.") - (".$persen_dijual.") * (".$tersedia_rhpp_expr.")) as saldo_akhir_rhpp,
                ((".$tersedia_total_expr.") - (".$persen_dijual.") * (".$tersedia_total_expr.")) as saldo_akhir_total,
                -- STOCK TERSEDIA (ekor) = populasi - ekor mati - ekor panen, kumulatif s/d end_date
                (data.populasi - data.ekor_mati - data.ekor_panen) as stock_tersedia,
                -- SALDO AWAL STOK (ekor) = rumus sama, tapi kumulatif < start_date
                (data.populasi - data.ekor_mati_awal - data.ekor_panen_awal) as saldo_awal_stok,
                -- SISA STOK (ekor) = saldo_awal_stok - jumlah kematian di periode berjalan
                -- (ekor_mati kumulatif s/d end_date - ekor_mati kumulatif < start_date)
                ((data.populasi - data.ekor_mati_awal - data.ekor_panen_awal)
                    - (data.ekor_mati - data.ekor_mati_awal)) as sisa_stok,
                data.sa_pkn,
                data.beli_pkn,
                data.mutasi_msk_pkn,
                data.mutasi_klwr_pkn,
                data.koreksi_pkn,
                data.pemakaian_pkn,
                (data.sa_pkn + data.beli_pkn + data.mutasi_msk_pkn)
                    - (data.mutasi_klwr_pkn + data.pemakaian_pkn) + data.koreksi_pkn as sisa_pkn,
                data.sa_ovk,
                data.beli_ovk,
                data.mutasi_msk_ovk,
                data.mutasi_klwr_ovk,
                data.pemakaian_ovk,
                (data.sa_ovk + data.beli_ovk + data.mutasi_msk_ovk)
                    - (data.mutasi_klwr_ovk + data.pemakaian_ovk) as sisa_ovk,
                data.sa_doc,
                data.beli_doc,
                data.mutasi_msk_doc,
                data.mutasi_klwr_doc,
                data.koreksi_doc,
                data.pemakaian_doc,
                (data.sa_doc + data.beli_doc + data.mutasi_msk_doc + data.koreksi_doc)
                    - (data.mutasi_klwr_doc + data.pemakaian_doc) as sisa_doc,
                data.sa_oa,
                data.beli_oa,
                data.mutasi_msk_oa,
                data.mutasi_klwr_oa,
                data.koreksi_oa,
                (data.beli_oa + data.mutasi_msk_oa + data.koreksi_oa) - data.mutasi_klwr_oa as pemakaian_oa,
                (data.beli_oa + data.mutasi_msk_oa) - data.mutasi_klwr_oa + data.koreksi_oa as net_oa,
                data.pdpt_peternak,
                data.pdpt_peternak + data.pemakaian_pkn + data.pemakaian_ovk
                    + data.pemakaian_doc + data.pemakaian_oa as total
            from
            (
                select
                    w.kode as unit,
                    c.noreg,
                    m.nama,
                    j.kode as jenis_mitra,
                    case when td.datang is not null then td.datang else rs.tgl_docin end as tgl_chick_in,
                    case when td.jml_ekor is not null then td.jml_ekor else rs.populasi end as populasi,
                    c.sa_pkn, c.beli_pkn, c.mutasi_msk_pkn, c.mutasi_klwr_pkn,
                    c.koreksi_pkn, c.pemakaian_pkn, c.sisa_pkn,
                    c.sa_ovk, c.beli_ovk, c.mutasi_msk_ovk, c.mutasi_klwr_ovk, c.pemakaian_ovk,
                    c.sa_doc, c.beli_doc, c.mutasi_msk_doc, c.mutasi_klwr_doc, c.koreksi_doc, c.pemakaian_doc,
                    c.sa_oa, c.beli_oa, c.mutasi_msk_oa, c.mutasi_klwr_oa, c.koreksi_oa, c.pemakaian_oa,
                    c.pdpt_peternak,
                    case when ps.noreg is not null then 1 else 0 end as is_panen_sebagian,
                    case when psa.noreg is not null then 1 else 0 end as is_panen_sebagian_awal,
                    coalesce(lu.ekor_mati, 0) as ekor_mati,
                    coalesce(pu.ekor_panen, 0) as ekor_panen,
                    coalesce(lus.ekor_mati, 0) as ekor_mati_awal,
                    coalesce(pus.ekor_panen, 0) as ekor_panen_awal,
                    coalesce(pp.ekor_panen, 0) as ekor_panen_periode,
                    -- Pemakaian & RHPP sebelum start_date, untuk Saldo Awal
                    (coalesce(dsts_a.pemakaian_pkn, 0) + coalesce(mm_a.mm_pemakaian_pkn, 0)) as pemakaian_pkn_awal,
                    (coalesce(dss_a.beli_ovk, 0) + coalesce(dss_a.mutasi_msk_ovk, 0) - coalesce(dsts_a.mutasi_klwr_ovk, 0)) as pemakaian_ovk_awal,
                    (coalesce(doc_a.beli_doc, 0) + coalesce(doc_a.koreksi_doc, 0)) as pemakaian_doc_awal,
                    (coalesce(dss_a.beli_oa, 0) + coalesce(dss_a.mutasi_msk_oa, 0)
                        + coalesce(dsts_a.koreksi_oa, 0) - coalesce(dsts_a.mutasi_klwr_oa, 0)) as pemakaian_oa_awal,
                    coalesce(rhpp_a.pdpt_peternak_belum_pajak, 0) as pdpt_peternak_awal,
                    -- Proporsi (dihitung sekali di noreg_hari_proporsi, dipakai juga untuk alokasi BL/BTL)
                    nhp.start_date_proporsi,
                    nhp.end_date_proporsi,
                    nhp.hari,
                    nhp.proporsi,
                    -- BL/BTL = langsung tertag ke noreg (det_jurnal_bl_btl) + alokasi proporsional dari pool
                    -- biaya level unit (det_jurnal.noreg is null), dibagi sesuai porsi Proporsi masing-masing noreg
                    (coalesce(djbb.nilai_bl, 0) + coalesce(djp.pool_bl, 0) * case
                        when coalesce(upt.total_proporsi, 0) <> 0 then cast(nhp.proporsi as float) / upt.total_proporsi
                        else 0
                    end) as nilai_bl,
                    (coalesce(djbb.nilai_btl, 0) + coalesce(djp.pool_btl, 0) * case
                        when coalesce(upt.total_proporsi, 0) <> 0 then cast(nhp.proporsi as float) / upt.total_proporsi
                        else 0
                    end) as nilai_btl
                from combined c
                left join rdim_submit rs on c.noreg = rs.noreg
                left join mitra_mapping_max mm on mm.nim = rs.nim
                left join mitra m on m.id = mm.id
                left join jenis j on m.jenis = j.kode
                left join kandang k on k.id = rs.kandang
                left join wilayah w on k.unit = w.id
                left join order_doc_max od on c.noreg = od.noreg
                left join terima_doc_max td on td.no_order = od.no_order
                left join panen_sebagian ps on ps.noreg = c.noreg
                left join panen_sebagian_awal psa on psa.noreg = c.noreg
                left join lhk_upto_end lu on lu.noreg = c.noreg
                left join panen_upto_end pu on pu.noreg = c.noreg
                left join lhk_upto_start lus on lus.noreg = c.noreg
                left join panen_upto_start pus on pus.noreg = c.noreg
                left join panen_periode pp on pp.noreg = c.noreg
                left join dss_awal dss_a on dss_a.noreg = c.noreg
                left join dsts_awal dsts_a on dsts_a.noreg = c.noreg
                left join mm_awal mm_a on mm_a.noreg = c.noreg
                left join doc_period_awal doc_a on doc_a.noreg = c.noreg
                left join rhpp_data_awal rhpp_a on rhpp_a.noreg = c.noreg
                left join noreg_hari_proporsi nhp on nhp.noreg = c.noreg
                left join unit_proporsi_total upt on upt.unit = w.kode
                left join det_jurnal_bl_btl djbb on djbb.noreg = c.noreg
                left join det_jurnal_bl_btl_pool djp on djp.unit = w.kode
                where m.id is not null
                    ".$sql_unit."
            ) data
            order by
                data.unit asc,
                data.tgl_chick_in asc;
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ($d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $data = $this->getData( $params );

        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }

    public function excryptParams()
    {
        $params = $this->input->post('params');

        try {
            $params_encrypt = exEncrypt( json_encode($params) );

            $this->result['status'] = 1;
            $this->result['content'] = $params_encrypt;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function exportExcel($params_encrypt)
    {
        $params = json_decode( exDecrypt($params_encrypt), true );

        $data = $this->getData( $params );

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $filename = strtoupper("LEMBAR_KERJA_PERIODE_");
        $filename = $filename.str_replace('-', '', $start_date).'_'.str_replace('-', '', $end_date).'.xls';

        $arr_column = null;

        $idx = 0;
        $arr_column[ $idx ] = array(
            'A' => array('value' => 'LEMBAR KERJA', 'data_type' => 'string', 'text_style' => 'bold')
        );
        $idx++;
        $arr_column[ $idx ] = array(
            'A' => array('value' => 'PERIODE '.str_replace('-', '/', $start_date).' - '.str_replace('-', '/', $end_date), 'data_type' => 'string', 'colspan' => array('A','F'), 'align' => 'left', 'text_style' => 'bold', 'border' => 'none'),
        );
        $idx++;
        $arr_column[ $idx ] = array(
            'A' => array('value' => 'UNIT', 'data_type' => 'string', 'rowspan' => array('A3','A4'), 'align' => 'center', 'text_style' => 'bold'),
            'B' => array('value' => 'NOREG', 'data_type' => 'string', 'rowspan' => array('B3','B4'), 'align' => 'center', 'text_style' => 'bold'),
            'C' => array('value' => 'NAMA', 'data_type' => 'string', 'rowspan' => array('C3','C4'), 'align' => 'center', 'text_style' => 'bold'),
            'E' => array('value' => 'CHICK IN', 'data_type' => 'string', 'colspan' => array('D','E'), 'align' => 'center', 'text_style' => 'bold'),
            'L' => array('value' => 'PAKAN', 'data_type' => 'string', 'colspan' => array('F','L'), 'align' => 'center', 'text_style' => 'bold'),
            'R' => array('value' => 'OVK', 'data_type' => 'string', 'colspan' => array('M','R'), 'align' => 'center', 'text_style' => 'bold'),
            'X' => array('value' => 'DOC', 'data_type' => 'string', 'colspan' => array('S','X'), 'align' => 'center', 'text_style' => 'bold'),
            'AD' => array('value' => 'OA', 'data_type' => 'string', 'colspan' => array('Y','AE'), 'align' => 'center', 'text_style' => 'bold'),
            'AF' => array('value' => 'RHPP', 'data_type' => 'string','rowspan' => array('AF3','AF4'), 'align' => 'center', 'text_style' => 'bold'),
            'AG' => array('value' => 'TOTAL', 'data_type' => 'string','rowspan' => array('AG3','AG4'), 'align' => 'center', 'text_style' => 'bold'),
        );
        $idx++;
        $arr_column[ $idx ] = array(
            'D' => array('value' => 'TGL', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'E' => array('value' => 'POPULASI', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'F' => array('value' => 'SALDO AWAL', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'G' => array('value' => 'BELI', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'H' => array('value' => 'MUTASI (+)', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'I' => array('value' => 'MUTASI (-)', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'J' => array('value' => 'KOREKSI (+/-)', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'K' => array('value' => 'PEMAKAIAN', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'L' => array('value' => 'SISA', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'M' => array('value' => 'SALDO AWAL', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'N' => array('value' => 'BELI', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'O' => array('value' => 'MUTASI (+)', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'P' => array('value' => 'MUTASI (-)', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'Q' => array('value' => 'KOREKSI (+/-)', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'R' => array('value' => 'PEMAKAIAN', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'S' => array('value' => 'SALDO AWAL', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'T' => array('value' => 'BELI', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'U' => array('value' => 'MUTASI (+)', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'V' => array('value' => 'MUTASI (-)', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'W' => array('value' => 'KOREKSI (+/-)', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'X' => array('value' => 'PEMAKAIAN', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'Y' => array('value' => 'SALDO AWAL', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'Z' => array('value' => 'BELI', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'AA' => array('value' => 'MUTASI (+)', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'AB' => array('value' => 'MUTASI (-)', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'AC' => array('value' => 'KOREKSI (+/-)', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'AD' => array('value' => 'NET OA', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'AE' => array('value' => 'SALDO AKHIR', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
        );
        $idx++;

        $start_row_header = $idx;

        $arr_header = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF', 'AG');
        if ( !empty($data) ) {
            foreach ($data as $key => $value) {
                $arr_column[ $idx ] = array(
                    'A' => array('value' => ($value['unit']), 'data_type' => 'string'),
                    'B' => array('value' => ($value['noreg']), 'data_type' => 'string'),
                    'C' => array('value' => (strtoupper($value['nama'])), 'data_type' => 'string'),
                    'D' => array('value' => ($value['tgl_chick_in']), 'data_type' => 'date'),
                    'E' => array('value' => ($value['populasi']), 'data_type' => 'integer'),
                    'F' => array('value' => ($value['sa_pkn']), 'data_type' => 'decimal2'),
                    'G' => array('value' => ($value['beli_pkn']), 'data_type' => 'decimal2'),
                    'H' => array('value' => ($value['mutasi_msk_pkn']), 'data_type' => 'decimal2'),
                    'I' => array('value' => ($value['mutasi_klwr_pkn']), 'data_type' => 'decimal2'),
                    'J' => array('value' => ($value['koreksi_pkn']), 'data_type' => 'decimal2'),
                    'K' => array('value' => ($value['pemakaian_pkn']), 'data_type' => 'decimal2'),
                    'L' => array('value' => ($value['sisa_pkn']), 'data_type' => 'decimal2'),
                    'M' => array('value' => ($value['sa_ovk']), 'data_type' => 'decimal2'),
                    'N' => array('value' => ($value['beli_ovk']), 'data_type' => 'decimal2'),
                    'O' => array('value' => ($value['mutasi_msk_ovk']), 'data_type' => 'decimal2'),
                    'P' => array('value' => ($value['mutasi_klwr_ovk']), 'data_type' => 'decimal2'),
                    'Q' => array('value' => (0), 'data_type' => 'decimal2'),
                    'R' => array('value' => ($value['pemakaian_ovk']), 'data_type' => 'decimal2'),
                    'S' => array('value' => ($value['sa_doc']), 'data_type' => 'decimal2'),
                    'T' => array('value' => ($value['beli_doc']), 'data_type' => 'decimal2'),
                    'U' => array('value' => ($value['mutasi_msk_doc']), 'data_type' => 'decimal2'),
                    'V' => array('value' => ($value['mutasi_klwr_doc']), 'data_type' => 'decimal2'),
                    'W' => array('value' => ($value['koreksi_doc']), 'data_type' => 'decimal2'),
                    'X' => array('value' => ($value['pemakaian_doc']), 'data_type' => 'decimal2'),
                    'Y' => array('value' => ($value['sa_oa']), 'data_type' => 'decimal2'),
                    'Z' => array('value' => ($value['beli_oa']), 'data_type' => 'decimal2'),
                    'AA' => array('value' => ($value['mutasi_msk_oa']), 'data_type' => 'decimal2'),
                    'AB' => array('value' => ($value['mutasi_klwr_oa']), 'data_type' => 'decimal2'),
                    'AC' => array('value' => ($value['koreksi_oa']), 'data_type' => 'decimal2'),
                    'AD' => array('value' => ($value['net_oa']), 'data_type' => 'decimal2'),
                    'AE' => array('value' => ($value['saldo_akhir_oa']), 'data_type' => 'decimal2'),
                    'AF' => array('value' => ($value['pdpt_peternak']), 'data_type' => 'decimal2'),
                    'AG' => array('value' => ($value['total']), 'data_type' => 'decimal2'),
                );

                $idx++;
            }
        }

        Modules::run( 'base/ExportExcel/exportExcelUsingSpreadSheet', $filename, $arr_header, $arr_column, $start_row_header, 0 );

        $this->load->helper('download');
        force_download('export_excel/'.$filename.'.xlsx', NULL);
    }
}