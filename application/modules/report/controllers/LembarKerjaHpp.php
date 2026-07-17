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

        // Filter Tutup Siklus: all (default) / sudah (tsk.tgl_tutup terisi) / belum (tsk.tgl_tutup masih null)
        $tutup_siklus = isset($params['tutup_siklus']) ? $params['tutup_siklus'] : 'all';
        $sql_tutup_siklus = null;
        if ( $tutup_siklus == 'sudah' ) {
            $sql_tutup_siklus = " and tsk.tgl_tutup is not null";
        } else if ( $tutup_siklus == 'belum' ) {
            $sql_tutup_siklus = " and tsk.tgl_tutup is null";
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
        // Hari setelah eom_end_date/bl_awal_end_date, dipakai utk cek "belum mulai chick-in di window ini" -
        // pakai batas awal hari BERIKUTNYA (bukan eom_end_date polos) supaya chick-in dgn jam > 00:00 di
        // TANGGAL TERAKHIR window itu sendiri tidak salah kena exclude (lihat jam di tgl_chick_in).
        $eom_end_date_next = date('Y-m-d', strtotime($eom_end_date.' +1 day'));
        // Saldo Awal BL/BTL = hitungan (nilai_bl/nilai_btl) bulan sebelumnya SAJA (bukan rekalkulasi kumulatif
        // dari tgl_chick_in spt kategori lain), krn nilai_bl/nilai_btl pakai rasio pool per-bulan - kalau
        // direkalkulasi dari awal siklus pakai 1 rasio historis, hasilnya beda dgn "Saldo Akhir bulan lalu".
        $bl_awal_end_date = date('Y-m-t', strtotime($start_date.' -1 day'));
        $bl_awal_start_date = date('Y-m-01', strtotime($bl_awal_end_date));
        $bl_awal_end_date_next = date('Y-m-d', strtotime($bl_awal_end_date.' +1 day'));
        // Saldo Awal BL/BTL = Saldo AKHIR bulan sebelumnya (Tersedia-Dijual, bukan produksi mentah). Tersedia
        // bulan sebelumnya = Saldo Awal bulan itu (dari bulan sblmnya lagi) + Produksi bulan itu - jadi perlu
        // window 1 bulan LEBIH mundur lagi (awal2) utk dapat Saldo Awal-nya. Dibatasi maks 2 bulan ke belakang
        // (Saldo Awal di window awal2 dianggap 0) - cukup utk siklus broiler normal, sesuai arahan user.
        $bl_awal2_end_date = date('Y-m-t', strtotime($bl_awal_start_date.' -1 day'));
        $bl_awal2_start_date = date('Y-m-01', strtotime($bl_awal2_end_date));
        $bl_awal2_end_date_next = date('Y-m-d', strtotime($bl_awal2_end_date.' +1 day'));
        // Tahun/bulan bulan sebelumnya - dipakai utk lookup snapshot histori (lembar_kerja_hpp_history,
        // diisi lewat fitur "Proses HPP"). Kalau ada baris histori utk noreg tsb di periode itu, Saldo
        // Awal SEMUA kategori ambil langsung dari saldo_akhir histori (lebih akurat, gak kena batasan
        // rantai/rekalkulasi apapun); kalau tidak ada, fallback ke rekalkulasi spt biasa.
        $bl_awal_tahun = (int) date('Y', strtotime($bl_awal_start_date));
        $bl_awal_bulan = (int) date('n', strtotime($bl_awal_start_date));

        // Catatan: start_date_proporsi / end_date_proporsi / hari / proporsi sekarang dihitung sekali
        // di CTE noreg_hari_proporsi (bukan di sini lagi), supaya bisa dipakai juga untuk alokasi BL/BTL per unit.

        // Jam ikut memengaruhi Hari/Proporsi (bukan cuma selisih tanggal bulat) - dikonfirmasi cocok dgn
        // file Excel referensi tim (lk_2026_proporsi_03_BL BTL.xlsx). tgl_chick_in dipakai APA ADANYA
        // (dgn jam-nya) saat jadi start_date_proporsi; batas akhir dihitung sbg (end_date_proporsi + 1 hari,
        // jam 00:00) supaya "end date" tetap dihitung penuh sampai akhir hari itu. Hari = selisih menit / 1440.
        $proporsi_start_case = "
            (case
                when psb.noreg is not null and pbe.noreg is null then null
                when nb.tgl_chick_in >= '".$eom_end_date_next."' then null
                when pbe.noreg is null then nb.tgl_chick_in
                when '".$start_date."' < nb.tgl_chick_in then nb.tgl_chick_in
                else '".$start_date."'
            end)
        ";
        $proporsi_end_case = "
            (case
                when psb.noreg is not null and pbe.noreg is null then null
                when nb.tgl_chick_in >= '".$eom_end_date_next."' then null
                when pbe.tgl_panen_terakhir_bulan is not null then pbe.tgl_panen_terakhir_bulan
                else '".$eom_end_date."'
            end)
        ";
        $proporsi_start_case_awal = "
            (case
                when psb.noreg is not null and pbe.noreg is null then null
                when nb.tgl_chick_in >= '".$bl_awal_end_date_next."' then null
                when pbe.noreg is null then nb.tgl_chick_in
                when '".$bl_awal_start_date."' < nb.tgl_chick_in then nb.tgl_chick_in
                else '".$bl_awal_start_date."'
            end)
        ";
        $proporsi_end_case_awal = "
            (case
                when psb.noreg is not null and pbe.noreg is null then null
                when nb.tgl_chick_in >= '".$bl_awal_end_date_next."' then null
                when pbe.tgl_panen_terakhir_bulan is not null then pbe.tgl_panen_terakhir_bulan
                else '".$bl_awal_end_date."'
            end)
        ";
        $proporsi_start_case_awal2 = "
            (case
                when psb.noreg is not null and pbe.noreg is null then null
                when nb.tgl_chick_in >= '".$bl_awal2_end_date_next."' then null
                when pbe.noreg is null then nb.tgl_chick_in
                when '".$bl_awal2_start_date."' < nb.tgl_chick_in then nb.tgl_chick_in
                else '".$bl_awal2_start_date."'
            end)
        ";
        $proporsi_end_case_awal2 = "
            (case
                when psb.noreg is not null and pbe.noreg is null then null
                when nb.tgl_chick_in >= '".$bl_awal2_end_date_next."' then null
                when pbe.tgl_panen_terakhir_bulan is not null then pbe.tgl_panen_terakhir_bulan
                else '".$bl_awal2_end_date."'
            end)
        ";
        // Hari (pecahan) & Proporsi, dipakai baik utk window periode berjalan maupun bulan sebelumnya -
        // tinggal oper pasangan start/end case yang sesuai.
        $build_hari_expr = function($start_case, $end_case) {
            return "isnull(cast(datediff(second, ".$start_case.", dateadd(day, 1, cast(".$end_case." as date))) as float) / 86400.0, 0)";
        };

        // BL (Biaya Langsung) / BTL (Biaya Tidak Langsung) - dibedakan dari no. COA. COA 60151.000
        // (Biaya Pengangkutan) dikecualikan total (tidak masuk BL atau BTL).
        // Aturan kepala COA (berlaku s/d periode Juni 2026, sesuai arahan user - direview lagi utk Juli 2026):
        //  - kepala 6 (COA 60xxx) = biaya eksternal: tetap dipisah BL/BTL apa adanya, diproporsi ke SEMUA noreg
        //  - kepala 5 (COA 50xxx) = biaya internal: BL & BTL digabung jadi satu (seluruhnya diperlakukan sbg BTL),
        //    dan kalau berupa pool level-unit (noreg null) hanya diproporsi ke noreg dgn jenis mitra internal (MI)
        $coa_bl_k6 = "'60801.000','60802.000','60803.000','60807.000','60807.001','60807.002','60807.003'";
        $coa_btl_k6 = "'60105.000','60151.001','60602.000','60604.000','60605.000','60854.000','60905.000','60922.000'";
        $coa_k5 = "'50101.000','50158.001','50300.000','50300.001','50300.002',
            '50102.000','50158.000','50500.000','50602.000','50604.000','50605.000','50901.000','50905.000','50911.000','50922.001'";

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
        // Fix: penyebut cukup (populasi - ekor_mati_awal), TANPA dikurangi ekor_panen_awal lagi -
        // dulu ekor_panen_awal ikut dikurangkan di penyebut padahal dia juga pembilangnya, jadi rasio
        // membengkak & gampang ke-cap 100% begitu panen sblm periode sudah lewat separuh populasi
        // (Saldo Awal jadi 0 padahal fisik masih ada sisa stok/populasi hidup). Pola ini disamakan dgn
        // $persen_dijual (periode berjalan) yg penyebutnya juga cuma dikurangi kematian, bukan panen.
        $persen_dijual_awal = "
            case
                when data.ekor_panen_awal > (data.populasi - data.ekor_mati_awal) then 1
                when (data.populasi - data.ekor_mati_awal) <> 0
                then cast(data.ekor_panen_awal as float) / (data.populasi - data.ekor_mati_awal)
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
        $sa_awal_doc_expr_calc = "(data.pemakaian_doc_awal - (".$persen_dijual_awal.") * data.pemakaian_doc_awal)";
        $sa_awal_pakan_expr_calc = "(data.pemakaian_pkn_awal - (".$persen_dijual_awal.") * data.pemakaian_pkn_awal)";
        $sa_awal_ovk_expr_calc = "(data.pemakaian_ovk_awal - (".$persen_dijual_awal.") * data.pemakaian_ovk_awal)";
        $sa_awal_oa_expr_calc = "(data.pemakaian_oa_awal - (".$persen_dijual_awal.") * data.pemakaian_oa_awal)";
        $sa_awal_rhpp_expr_calc = "((".$rhpp_awal_expr.") - (".$persen_dijual_awal.") * (".$rhpp_awal_expr."))";
        // BL/BTL: Saldo Awal = Saldo AKHIR bulan sebelumnya (bukan produksi mentah, bukan juga persen kumulatif
        // sejak chick-in) - jadi butuh persen dijual yg scope-nya KHUSUS bulan sebelumnya saja. Diturunkan dari
        // pola $persen_dijual (periode berjalan) yg sama, tapi "periode"-nya = bulan sebelumnya: ekor panen &
        // ekor mati SELAMA bulan itu didapat dgn selisihkan cumulative-before-start_date thd cumulative-before-
        // bl_awal_start_date (awal bulan itu).
        $ekor_panen_selama_bl_awal = "(data.ekor_panen_awal - data.ekor_panen_bl_awal_start)";
        $ekor_mati_selama_bl_awal = "(data.ekor_mati_awal - data.ekor_mati_bl_awal_start)";
        $stock_awal_bl_awal_month = "(data.populasi - data.ekor_mati_bl_awal_start - data.ekor_panen_bl_awal_start)";
        $persen_dijual_bl_awal = "
            case
                when (".$ekor_panen_selama_bl_awal.") > ((".$stock_awal_bl_awal_month.") - (".$ekor_mati_selama_bl_awal.")) then 1
                when ((".$stock_awal_bl_awal_month.") - (".$ekor_mati_selama_bl_awal.")) <> 0
                then cast((".$ekor_panen_selama_bl_awal.") as float) / ((".$stock_awal_bl_awal_month.") - (".$ekor_mati_selama_bl_awal."))
                else 0
            end
        ";
        // Tersedia bulan sebelumnya (mis. Maret) = Saldo Awal bulan itu (dari bulan sblmnya lagi, mis. Feb) +
        // Produksi bulan itu. Saldo Awal bulan sblmnya = Saldo AKHIR window awal2 (Feb), dgn Saldo Awal window
        // awal2 itu sendiri DIANGGAP 0 (dibatasi maks 2 bulan ke belakang, sesuai arahan user). persen_dijual
        // utk window awal2 diturunkan sama seperti persen_dijual_bl_awal, cuma digeser 1 bulan lagi.
        $ekor_panen_selama_bl_awal2 = "(data.ekor_panen_bl_awal_start - data.ekor_panen_bl_awal2_start)";
        $ekor_mati_selama_bl_awal2 = "(data.ekor_mati_bl_awal_start - data.ekor_mati_bl_awal2_start)";
        $stock_awal_bl_awal2_month = "(data.populasi - data.ekor_mati_bl_awal2_start - data.ekor_panen_bl_awal2_start)";
        $persen_dijual_bl_awal2 = "
            case
                when (".$ekor_panen_selama_bl_awal2.") > ((".$stock_awal_bl_awal2_month.") - (".$ekor_mati_selama_bl_awal2.")) then 1
                when ((".$stock_awal_bl_awal2_month.") - (".$ekor_mati_selama_bl_awal2.")) <> 0
                then cast((".$ekor_panen_selama_bl_awal2.") as float) / ((".$stock_awal_bl_awal2_month.") - (".$ekor_mati_selama_bl_awal2."))
                else 0
            end
        ";
        // Saldo Akhir window awal2 (Feb) = Produksi Feb - persen dijual selama Feb x Produksi Feb (Saldo Awal Feb dianggap 0)
        $saldo_akhir_bl_awal2_expr = "round(data.nilai_bl_awal2 - (".$persen_dijual_bl_awal2.") * data.nilai_bl_awal2, 0)";
        $saldo_akhir_btl_awal2_expr = "round(data.nilai_btl_awal2 - (".$persen_dijual_bl_awal2.") * data.nilai_btl_awal2, 0)";
        // Tersedia bulan sebelumnya (Maret) = Saldo Akhir Feb (jadi Saldo Awal Maret) + Produksi Maret
        $tersedia_bl_awal_month_expr = "round((".$saldo_akhir_bl_awal2_expr.") + data.nilai_bl_awal, 0)";
        $tersedia_btl_awal_month_expr = "round((".$saldo_akhir_btl_awal2_expr.") + data.nilai_btl_awal, 0)";
        // Saldo Awal periode ini = Saldo AKHIR bulan sebelumnya = Tersedia bulan sebelumnya - Dijual selama bulan itu
        $sa_awal_bl_expr_calc = "round((".$tersedia_bl_awal_month_expr.") - (".$persen_dijual_bl_awal.") * (".$tersedia_bl_awal_month_expr."), 0)";
        $sa_awal_btl_expr_calc = "round((".$tersedia_btl_awal_month_expr.") - (".$persen_dijual_bl_awal.") * (".$tersedia_btl_awal_month_expr."), 0)";

        // Saldo Awal FINAL (semua kategori) = ambil dari snapshot histori (data.hist_saldo_akhir_*, di-join
        // dari lembar_kerja_hpp_history di CTE history_saldo_awal) kalau ada utk noreg & bulan sebelumnya;
        // fallback ke hasil rekalkulasi (_calc) kalau belum pernah di-"Proses HPP" utk periode itu.
        // Pakai coalesce(), BUKAN isnull() - isnull() di SQL Server ambil TIPE DATA dari argumen PERTAMA
        // (hist_saldo_akhir_* = decimal(18,2) di skema histori), beda dgn coalesce() yg ikut aturan data
        // type precedence biasa. Dibungkus round(...,0) krn SEMUA kategori (bukan cuma BL/BTL) skrg
        // dibulatkan ke rupiah penuh (0 desimal), sesuai arahan user - presisi desimal asli sumbernya
        // sudah tidak dipertahankan lagi.
        $sa_awal_doc_expr = "round(coalesce(data.hist_saldo_akhir_doc, ".$sa_awal_doc_expr_calc."), 0)";
        $sa_awal_pakan_expr = "round(coalesce(data.hist_saldo_akhir_pakan, ".$sa_awal_pakan_expr_calc."), 0)";
        $sa_awal_ovk_expr = "round(coalesce(data.hist_saldo_akhir_ovk, ".$sa_awal_ovk_expr_calc."), 0)";
        $sa_awal_oa_expr = "round(coalesce(data.hist_saldo_akhir_oa, ".$sa_awal_oa_expr_calc."), 0)";
        $sa_awal_rhpp_expr = "round(coalesce(data.hist_saldo_akhir_rhpp, ".$sa_awal_rhpp_expr_calc."), 0)";
        $sa_awal_bl_expr = "round(coalesce(data.hist_saldo_akhir_bl, ".$sa_awal_bl_expr_calc."), 0)";
        $sa_awal_btl_expr = "round(coalesce(data.hist_saldo_akhir_btl, ".$sa_awal_btl_expr_calc."), 0)";

        // Catatan optimasi (2026-07-17): persen_dijual, sa_awal_*, produksi_rhpp & tersedia_* sekarang
        // dihitung SEKALI sbg kolom alias di subquery berlapis (lapisan "base" & "calc" di final SELECT),
        // BUKAN di-inline/tempel ulang tekstual di tiap kolom turunan spt sebelumnya. Ekspresi sa_awal
        // (yg paling gede - berisi rantai coalesce histori + fallback kalkulasi awal/awal2) dulunya
        // ter-copy puluhan kali (dijual_total & saldo_akhir_total masing2 expand 7-14x) - bikin teks SQL
        // bengkak ~160KB & SQL Server compile ulang query raksasa tiap request (tanggal inline = teks beda
        // per periode = plan cache miss). Hasil angka IDENTIK (ekspresi sama, cuma dihitung 1x), sudah
        // diverifikasi diff baris-per-baris output lama vs baru.

        $sql = "
            with
            -- ============================================================
            -- 0. SNAPSHOT HISTORI (dari fitur \"Proses HPP\") - kalau ada baris utk noreg di bulan
            -- sebelumnya, Saldo Awal SEMUA kategori ambil dari sini langsung (saldo_akhir_* sudah final,
            -- tersimpan permanen, tidak kena batasan rantai/rekalkulasi apapun). LEFT JOIN nullable -
            -- kalau tidak match, fallback ke rekalkulasi spt biasa (lihat sa_awal_*_expr di PHP).
            -- ============================================================
            history_saldo_awal as (
                select noreg, saldo_akhir_doc, saldo_akhir_pakan, saldo_akhir_ovk, saldo_akhir_oa,
                    saldo_akhir_bl, saldo_akhir_btl, saldo_akhir_rhpp
                from lembar_kerja_hpp_history
                where tahun = ".$bl_awal_tahun." and bulan = ".$bl_awal_bulan."
            ),
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
            -- Sama seperti lhk_upto_start/panen_upto_start, tapi filter < bl_awal_start_date (awal bulan
            -- sebelumnya) - dipakai utk hitung persen dijual SELAMA bulan sebelumnya saja (Saldo Akhir BL/BTL
            -- bulan lalu), dgn cara selisihkan dari ekor_mati_awal/ekor_panen_awal (kumulatif s/d start_date)
            lhk_upto_bl_awal_start as (
                select l1.noreg, l1.ekor_mati
                from lhk l1
                inner join (
                    select noreg, max(umur) as umur from lhk where tanggal < '".$bl_awal_start_date."' group by noreg
                ) l2 on l1.noreg = l2.noreg and l1.umur = l2.umur
            ),
            panen_upto_bl_awal_start as (
                select noreg, sum(netto_ekor) as ekor_panen
                from real_sj
                where tgl_panen < '".$bl_awal_start_date."'
                group by noreg
            ),
            -- Sama seperti panen_bulan_enddate/panen_sebelum_bulan, tapi utk bulan SEBELUM start_date -
            -- dipakai utk window Proporsi Saldo Awal BL/BTL (hitungan bulan sebelumnya saja)
            panen_bulan_enddate_prev as (
                select noreg, max(tgl_panen) as tgl_panen_terakhir_bulan
                from real_sj
                where tgl_panen between '".$bl_awal_start_date."' and '".$bl_awal_end_date."'
                group by noreg
            ),
            panen_sebelum_bulan_prev as (
                select distinct noreg
                from real_sj
                where tgl_panen < '".$bl_awal_start_date."'
            ),
            -- Sama seperti lhk_upto_bl_awal_start/panen_upto_bl_awal_start/panen_bulan_enddate_prev/
            -- panen_sebelum_bulan_prev, tapi 1 bulan LEBIH mundur lagi (awal2/prev2) - dipakai utk hitung
            -- Saldo Awal bulan sebelumnya (jadi Saldo Akhir bulan sebelumnya bisa dihitung proper, bkn cuma
            -- produksi mentah). Dibatasi maks 2 bulan ke belakang - Saldo Awal di window awal2 dianggap 0.
            lhk_upto_bl_awal2_start as (
                select l1.noreg, l1.ekor_mati
                from lhk l1
                inner join (
                    select noreg, max(umur) as umur from lhk where tanggal < '".$bl_awal2_start_date."' group by noreg
                ) l2 on l1.noreg = l2.noreg and l1.umur = l2.umur
            ),
            panen_upto_bl_awal2_start as (
                select noreg, sum(netto_ekor) as ekor_panen
                from real_sj
                where tgl_panen < '".$bl_awal2_start_date."'
                group by noreg
            ),
            panen_bulan_enddate_prev2 as (
                select noreg, max(tgl_panen) as tgl_panen_terakhir_bulan
                from real_sj
                where tgl_panen between '".$bl_awal2_start_date."' and '".$bl_awal2_end_date."'
                group by noreg
            ),
            panen_sebelum_bulan_prev2 as (
                select distinct noreg
                from real_sj
                where tgl_panen < '".$bl_awal2_start_date."'
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
            -- BL/BTL per noreg dari det_jurnal (coa_tujuan), sesuai periode berjalan, dibedakan dari no. COA.
            -- Kepala 5 (biaya internal) digabung total ke BTL, apapun jenis mitra noreg-nya (sudah tertag langsung).
            det_jurnal_bl_btl as (
                select dj.noreg,
                    sum(case when dj.coa_tujuan in (".$coa_bl_k6.") then dj.nominal else 0 end) as nilai_bl,
                    sum(case when dj.coa_tujuan in (".$coa_btl_k6.") then dj.nominal else 0 end
                        + case when dj.coa_tujuan in (".$coa_k5.") then dj.nominal else 0 end) as nilai_btl
                from det_jurnal dj
                where dj.tanggal between '".$start_date."' and '".$end_date."'
                    and dj.noreg is not null
                group by dj.noreg
            ),
            -- Sama seperti det_jurnal_bl_btl, tapi bulan SEBELUM start_date saja (Saldo Awal)
            det_jurnal_bl_btl_awal as (
                select dj.noreg,
                    sum(case when dj.coa_tujuan in (".$coa_bl_k6.") then dj.nominal else 0 end) as nilai_bl,
                    sum(case when dj.coa_tujuan in (".$coa_btl_k6.") then dj.nominal else 0 end
                        + case when dj.coa_tujuan in (".$coa_k5.") then dj.nominal else 0 end) as nilai_btl
                from det_jurnal dj
                where dj.tanggal between '".$bl_awal_start_date."' and '".$bl_awal_end_date."'
                    and dj.noreg is not null
                group by dj.noreg
            ),
            -- Sama seperti det_jurnal_bl_btl_awal, tapi 1 bulan lebih mundur lagi (awal2)
            det_jurnal_bl_btl_awal2 as (
                select dj.noreg,
                    sum(case when dj.coa_tujuan in (".$coa_bl_k6.") then dj.nominal else 0 end) as nilai_bl,
                    sum(case when dj.coa_tujuan in (".$coa_btl_k6.") then dj.nominal else 0 end
                        + case when dj.coa_tujuan in (".$coa_k5.") then dj.nominal else 0 end) as nilai_btl
                from det_jurnal dj
                where dj.tanggal between '".$bl_awal2_start_date."' and '".$bl_awal2_end_date."'
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
                    case when td.jml_ekor is not null then td.jml_ekor else rs.populasi end as populasi,
                    m.jenis as jenis_mitra
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
            -- Sama seperti noreg_base, tapi sumbernya rdim_submit langsung (bukan combined/all_noreg yg
            -- dibatasi noreg dgn transaksi periode APRIL) - supaya noreg yg aktif bulan sebelumnya tapi
            -- sudah tidak ada transaksi di periode berjalan tetap ikut sbg pembagi rasio pool BL/BTL Saldo Awal
            noreg_base_awal as (
                select rs.noreg,
                    w.kode as unit,
                    case when td.datang is not null then td.datang else rs.tgl_docin end as tgl_chick_in,
                    case when td.jml_ekor is not null then td.jml_ekor else rs.populasi end as populasi,
                    m.jenis as jenis_mitra
                from rdim_submit rs
                left join mitra_mapping_max mm on mm.nim = rs.nim
                left join mitra m on m.id = mm.id
                left join kandang k on k.id = rs.kandang
                left join wilayah w on k.unit = w.id
                left join order_doc_max od on rs.noreg = od.noreg
                left join terima_doc_max td on td.no_order = od.no_order
                where m.id is not null
                    and (case when td.datang is not null then td.datang else rs.tgl_docin end) < '".$bl_awal_end_date_next."'
            ),
            -- Sama seperti noreg_base_awal, tapi 1 bulan lebih mundur lagi (awal2)
            noreg_base_awal2 as (
                select rs.noreg,
                    w.kode as unit,
                    case when td.datang is not null then td.datang else rs.tgl_docin end as tgl_chick_in,
                    case when td.jml_ekor is not null then td.jml_ekor else rs.populasi end as populasi,
                    m.jenis as jenis_mitra
                from rdim_submit rs
                left join mitra_mapping_max mm on mm.nim = rs.nim
                left join mitra m on m.id = mm.id
                left join kandang k on k.id = rs.kandang
                left join wilayah w on k.unit = w.id
                left join order_doc_max od on rs.noreg = od.noreg
                left join terima_doc_max td on td.no_order = od.no_order
                where m.id is not null
                    and (case when td.datang is not null then td.datang else rs.tgl_docin end) < '".$bl_awal2_end_date_next."'
            ),
            noreg_hari_proporsi as (
                select nb.noreg, nb.unit, nb.populasi, nb.jenis_mitra,
                    ".$proporsi_start_case." as start_date_proporsi,
                    ".$proporsi_end_case." as end_date_proporsi,
                    round(".$build_hari_expr($proporsi_start_case, $proporsi_end_case).", 2) as hari,
                    round(".$build_hari_expr($proporsi_start_case, $proporsi_end_case)." * nb.populasi, 0) as proporsi
                from noreg_base nb
                left join panen_bulan_enddate pbe on pbe.noreg = nb.noreg
                left join panen_sebelum_bulan psb on psb.noreg = nb.noreg
            ),
            unit_proporsi_total as (
                select unit, sum(proporsi) as total_proporsi
                from noreg_hari_proporsi
                group by unit
            ),
            -- Sama seperti unit_proporsi_total, tapi hanya noreg internal (MI) - dipakai utk alokasi pool kepala 5
            unit_proporsi_total_internal as (
                select unit, sum(proporsi) as total_proporsi
                from noreg_hari_proporsi
                where jenis_mitra = 'MI'
                group by unit
            ),
            -- Sama persis strukturnya dgn noreg_hari_proporsi (termasuk exclude psb), tapi window-nya digeser
            -- ke bulan SEBELUM start_date - dipakai utk alokasi pool BL/BTL Saldo Awal (hitungan bulan lalu saja)
            noreg_hari_proporsi_awal as (
                select nb.noreg, nb.unit, nb.populasi, nb.jenis_mitra,
                    ".$proporsi_start_case_awal." as start_date_proporsi,
                    ".$proporsi_end_case_awal." as end_date_proporsi,
                    round(".$build_hari_expr($proporsi_start_case_awal, $proporsi_end_case_awal)." * nb.populasi, 0) as proporsi
                from noreg_base_awal nb
                left join panen_bulan_enddate_prev pbe on pbe.noreg = nb.noreg
                left join panen_sebelum_bulan_prev psb on psb.noreg = nb.noreg
            ),
            unit_proporsi_total_awal as (
                select unit, sum(proporsi) as total_proporsi
                from noreg_hari_proporsi_awal
                group by unit
            ),
            unit_proporsi_total_internal_awal as (
                select unit, sum(proporsi) as total_proporsi
                from noreg_hari_proporsi_awal
                where jenis_mitra = 'MI'
                group by unit
            ),
            -- Sama seperti noreg_hari_proporsi_awal/unit_proporsi_total_awal/unit_proporsi_total_internal_awal,
            -- tapi 1 bulan lebih mundur lagi (awal2) - dipakai utk hitung Saldo Awal bulan sebelumnya
            noreg_hari_proporsi_awal2 as (
                select nb.noreg, nb.unit, nb.populasi, nb.jenis_mitra,
                    round(".$build_hari_expr($proporsi_start_case_awal2, $proporsi_end_case_awal2)." * nb.populasi, 0) as proporsi
                from noreg_base_awal2 nb
                left join panen_bulan_enddate_prev2 pbe on pbe.noreg = nb.noreg
                left join panen_sebelum_bulan_prev2 psb on psb.noreg = nb.noreg
            ),
            unit_proporsi_total_awal2 as (
                select unit, sum(proporsi) as total_proporsi
                from noreg_hari_proporsi_awal2
                group by unit
            ),
            unit_proporsi_total_internal_awal2 as (
                select unit, sum(proporsi) as total_proporsi
                from noreg_hari_proporsi_awal2
                where jenis_mitra = 'MI'
                group by unit
            ),
            -- BL/BTL yang di-posting di level unit (noreg null), untuk dialokasikan ke tiap noreg pakai proporsi.
            -- pool_bl/pool_btl (kepala 6) dialokasikan ke SEMUA noreg; pool_k5 (kepala 5, digabung BL+BTL)
            -- hanya dialokasikan ke noreg internal (MI) dan seluruhnya masuk kolom BTL.
            det_jurnal_bl_btl_pool as (
                select dj.unit,
                    sum(case when dj.coa_tujuan in (".$coa_bl_k6.") then dj.nominal else 0 end) as pool_bl,
                    sum(case when dj.coa_tujuan in (".$coa_btl_k6.") then dj.nominal else 0 end) as pool_btl,
                    sum(case when dj.coa_tujuan in (".$coa_k5.") then dj.nominal else 0 end) as pool_k5
                from det_jurnal dj
                where dj.tanggal between '".$start_date."' and '".$end_date."'
                    and dj.noreg is null
                group by dj.unit
            ),
            -- Sama seperti det_jurnal_bl_btl_pool, tapi bulan SEBELUM start_date saja (Saldo Awal)
            det_jurnal_bl_btl_pool_awal as (
                select dj.unit,
                    sum(case when dj.coa_tujuan in (".$coa_bl_k6.") then dj.nominal else 0 end) as pool_bl,
                    sum(case when dj.coa_tujuan in (".$coa_btl_k6.") then dj.nominal else 0 end) as pool_btl,
                    sum(case when dj.coa_tujuan in (".$coa_k5.") then dj.nominal else 0 end) as pool_k5
                from det_jurnal dj
                where dj.tanggal between '".$bl_awal_start_date."' and '".$bl_awal_end_date."'
                    and dj.noreg is null
                group by dj.unit
            ),
            -- Sama seperti det_jurnal_bl_btl_pool_awal, tapi 1 bulan lebih mundur lagi (awal2)
            det_jurnal_bl_btl_pool_awal2 as (
                select dj.unit,
                    sum(case when dj.coa_tujuan in (".$coa_bl_k6.") then dj.nominal else 0 end) as pool_bl,
                    sum(case when dj.coa_tujuan in (".$coa_btl_k6.") then dj.nominal else 0 end) as pool_btl,
                    sum(case when dj.coa_tujuan in (".$coa_k5.") then dj.nominal else 0 end) as pool_k5
                from det_jurnal dj
                where dj.tanggal between '".$bl_awal2_start_date."' and '".$bl_awal2_end_date."'
                    and dj.noreg is null
                group by dj.unit
            )
            -- ============================================================
            -- 11. FINAL SELECT
            -- ============================================================
            select
                calc.unit,
                calc.noreg,
                calc.nama,
                calc.jenis_mitra,
                calc.tgl_chick_in,
                calc.tgl_tutup_siklus,
                calc.populasi,
                -- SALDO AWAL (grup Saldo Awal di view) = rumus Saldo Akhir (Tersedia - Dijual),
                -- tapi datanya dihitung sebelum start_date (kumulatif dari awal).
                -- Sudah dihitung sekali di lapisan base (lihat bawah), di sini tinggal pakai.
                calc.sa_awal_doc,
                calc.sa_awal_pakan,
                calc.sa_awal_ovk,
                calc.sa_awal_oa,
                calc.sa_awal_bl,
                calc.sa_awal_btl,
                calc.sa_awal_rhpp,
                round(calc.sa_awal_doc + calc.sa_awal_pakan + calc.sa_awal_ovk + calc.sa_awal_oa
                    + calc.sa_awal_bl + calc.sa_awal_btl + calc.sa_awal_rhpp, 0) as sa_awal_total,
                -- PRODUKSI (grup Produksi di view, sesuai periode start_date s/d end_date)
                round(calc.pemakaian_doc, 0) as produksi_doc,
                round(calc.pemakaian_pkn, 0) as produksi_pakan,
                round(calc.pemakaian_ovk, 0) as produksi_ovk,
                round(calc.pemakaian_oa, 0) as produksi_oa,
                calc.nilai_bl as produksi_bl,
                calc.nilai_btl as produksi_btl,
                round(calc.produksi_rhpp_calc, 0) as produksi_rhpp,
                -- Total = JUMLAH kolom kategori yg sudah dibulatkan masing2 (BUKAN dibulatkan sbg satu rumus
                -- besar) - supaya Total selalu persis sama dgn penjumlahan kategori yg tampil di layar
                -- (kalau dibulatkan terpisah, bulatkan-lalu-jumlah vs jumlah-lalu-bulatkan bisa beda
                -- 1 rupiah krn arah pembulatan tiap kategori beda2 - ketauan dari kasus Saldo Akhir Maret
                -- vs Saldo Awal April PANJI PAMUNGKAS/25120300201, 2026-07-17).
                (round(calc.pemakaian_doc, 0) + round(calc.pemakaian_pkn, 0) + round(calc.pemakaian_ovk, 0) + round(calc.pemakaian_oa, 0)
                    + calc.nilai_bl + calc.nilai_btl
                    + round(calc.produksi_rhpp_calc, 0)) as produksi_total,
                -- TERSEDIA (grup Tersedia di view) = Saldo Awal + Produksi (dihitung di lapisan calc)
                calc.tersedia_doc,
                calc.tersedia_pakan,
                calc.tersedia_ovk,
                calc.tersedia_oa,
                calc.tersedia_bl,
                calc.tersedia_btl,
                calc.tersedia_rhpp,
                round(calc.tersedia_doc + calc.tersedia_pakan + calc.tersedia_ovk + calc.tersedia_oa
                    + calc.tersedia_bl + calc.tersedia_btl + calc.tersedia_rhpp, 0) as tersedia_total,
                -- DIJUAL (grup Dijual di view) = persentase (Terjual / Stock Tersedia) x nilai Tersedia
                round(calc.persen_dijual * calc.tersedia_doc, 0) as dijual_doc,
                round(calc.persen_dijual * calc.tersedia_pakan, 0) as dijual_pakan,
                round(calc.persen_dijual * calc.tersedia_ovk, 0) as dijual_ovk,
                round(calc.persen_dijual * calc.tersedia_oa, 0) as dijual_oa,
                round(calc.persen_dijual * calc.tersedia_bl, 0) as dijual_bl,
                round(calc.persen_dijual * calc.tersedia_btl, 0) as dijual_btl,
                round(calc.persen_dijual * calc.tersedia_rhpp, 0) as dijual_rhpp,
                -- Total = jumlah kolom kategori yg sudah dibulatkan (lihat catatan di produksi_total)
                (round(calc.persen_dijual * calc.tersedia_doc, 0) + round(calc.persen_dijual * calc.tersedia_pakan, 0)
                    + round(calc.persen_dijual * calc.tersedia_ovk, 0) + round(calc.persen_dijual * calc.tersedia_oa, 0)
                    + round(calc.persen_dijual * calc.tersedia_bl, 0) + round(calc.persen_dijual * calc.tersedia_btl, 0)
                    + round(calc.persen_dijual * calc.tersedia_rhpp, 0)) as dijual_total,
                -- TERJUAL (ekor) = ekor panen di periode berjalan (start_date s/d end_date)
                calc.ekor_panen_periode as terjual,
                -- PERSENTASE = Terjual / Stock Tersedia x 100
                calc.persen_dijual * 100 as persentase_dijual,
                -- START/END DATE PROPORSI, HARI & PROPORSI (sudah dihitung sekali di noreg_hari_proporsi)
                calc.start_date_proporsi,
                calc.end_date_proporsi,
                calc.hari,
                calc.proporsi,
                -- SALDO AKHIR (grup Saldo Akhir di view) = Tersedia - Dijual
                round(calc.tersedia_doc - calc.persen_dijual * calc.tersedia_doc, 0) as saldo_akhir_doc,
                round(calc.tersedia_pakan - calc.persen_dijual * calc.tersedia_pakan, 0) as saldo_akhir_pakan,
                round(calc.tersedia_ovk - calc.persen_dijual * calc.tersedia_ovk, 0) as saldo_akhir_ovk,
                round(calc.tersedia_oa - calc.persen_dijual * calc.tersedia_oa, 0) as saldo_akhir_oa,
                round(calc.tersedia_bl - calc.persen_dijual * calc.tersedia_bl, 0) as saldo_akhir_bl,
                round(calc.tersedia_btl - calc.persen_dijual * calc.tersedia_btl, 0) as saldo_akhir_btl,
                round(calc.tersedia_rhpp - calc.persen_dijual * calc.tersedia_rhpp, 0) as saldo_akhir_rhpp,
                -- Total = jumlah kolom kategori yg sudah dibulatkan (lihat catatan di produksi_total)
                (round(calc.tersedia_doc - calc.persen_dijual * calc.tersedia_doc, 0)
                    + round(calc.tersedia_pakan - calc.persen_dijual * calc.tersedia_pakan, 0)
                    + round(calc.tersedia_ovk - calc.persen_dijual * calc.tersedia_ovk, 0)
                    + round(calc.tersedia_oa - calc.persen_dijual * calc.tersedia_oa, 0)
                    + round(calc.tersedia_bl - calc.persen_dijual * calc.tersedia_bl, 0)
                    + round(calc.tersedia_btl - calc.persen_dijual * calc.tersedia_btl, 0)
                    + round(calc.tersedia_rhpp - calc.persen_dijual * calc.tersedia_rhpp, 0)) as saldo_akhir_total,
                -- STOCK TERSEDIA (ekor) = populasi - ekor mati - ekor panen, kumulatif s/d end_date
                (calc.populasi - calc.ekor_mati - calc.ekor_panen) as stock_tersedia,
                -- SALDO AWAL STOK (ekor) = rumus sama, tapi kumulatif < start_date
                (calc.populasi - calc.ekor_mati_awal - calc.ekor_panen_awal) as saldo_awal_stok,
                -- SISA STOK (ekor) = saldo_awal_stok - jumlah kematian di periode berjalan
                -- (ekor_mati kumulatif s/d end_date - ekor_mati kumulatif < start_date)
                ((calc.populasi - calc.ekor_mati_awal - calc.ekor_panen_awal)
                    - (calc.ekor_mati - calc.ekor_mati_awal)) as sisa_stok,
                calc.sa_pkn,
                calc.beli_pkn,
                calc.mutasi_msk_pkn,
                calc.mutasi_klwr_pkn,
                calc.koreksi_pkn,
                calc.pemakaian_pkn,
                (calc.sa_pkn + calc.beli_pkn + calc.mutasi_msk_pkn)
                    - (calc.mutasi_klwr_pkn + calc.pemakaian_pkn) + calc.koreksi_pkn as sisa_pkn,
                calc.sa_ovk,
                calc.beli_ovk,
                calc.mutasi_msk_ovk,
                calc.mutasi_klwr_ovk,
                calc.pemakaian_ovk,
                (calc.sa_ovk + calc.beli_ovk + calc.mutasi_msk_ovk)
                    - (calc.mutasi_klwr_ovk + calc.pemakaian_ovk) as sisa_ovk,
                calc.sa_doc,
                calc.beli_doc,
                calc.mutasi_msk_doc,
                calc.mutasi_klwr_doc,
                calc.koreksi_doc,
                calc.pemakaian_doc,
                (calc.sa_doc + calc.beli_doc + calc.mutasi_msk_doc + calc.koreksi_doc)
                    - (calc.mutasi_klwr_doc + calc.pemakaian_doc) as sisa_doc,
                calc.sa_oa,
                calc.beli_oa,
                calc.mutasi_msk_oa,
                calc.mutasi_klwr_oa,
                calc.koreksi_oa,
                (calc.beli_oa + calc.mutasi_msk_oa + calc.koreksi_oa) - calc.mutasi_klwr_oa as pemakaian_oa,
                (calc.beli_oa + calc.mutasi_msk_oa) - calc.mutasi_klwr_oa + calc.koreksi_oa as net_oa,
                calc.pdpt_peternak,
                calc.pdpt_peternak + calc.pemakaian_pkn + calc.pemakaian_ovk
                    + calc.pemakaian_doc + calc.pemakaian_oa as total
            from
            (
                -- Lapisan calc: Tersedia per kategori = Saldo Awal (dari lapisan base) + Produksi.
                -- Dipisah dari lapisan base krn butuh referensi alias sa_awal_* (ga bisa refer alias
                -- sesama SELECT di SQL Server).
                select base.*,
                    round(base.sa_awal_doc + base.pemakaian_doc, 0) as tersedia_doc,
                    round(base.sa_awal_pakan + base.pemakaian_pkn, 0) as tersedia_pakan,
                    round(base.sa_awal_ovk + base.pemakaian_ovk, 0) as tersedia_ovk,
                    round(base.sa_awal_oa + base.pemakaian_oa, 0) as tersedia_oa,
                    round(base.sa_awal_bl + base.nilai_bl, 0) as tersedia_bl,
                    round(base.sa_awal_btl + base.nilai_btl, 0) as tersedia_btl,
                    round(base.sa_awal_rhpp + base.produksi_rhpp_calc, 0) as tersedia_rhpp
                from
                (
                -- Lapisan base: hitung SEKALI ekspresi2 berat yg dipakai berulang di kolom2 turunan -
                -- persen_dijual, produksi RHPP, dan Saldo Awal per kategori (coalesce snapshot histori
                -- vs fallback rekalkulasi awal/awal2).
                select data.*,
                    (".$persen_dijual.") as persen_dijual,
                    (".$produksi_rhpp_expr.") as produksi_rhpp_calc,
                    (".$sa_awal_doc_expr.") as sa_awal_doc,
                    (".$sa_awal_pakan_expr.") as sa_awal_pakan,
                    (".$sa_awal_ovk_expr.") as sa_awal_ovk,
                    (".$sa_awal_oa_expr.") as sa_awal_oa,
                    (".$sa_awal_bl_expr.") as sa_awal_bl,
                    (".$sa_awal_btl_expr.") as sa_awal_btl,
                    (".$sa_awal_rhpp_expr.") as sa_awal_rhpp
                from
                (
                select
                    w.kode as unit,
                    c.noreg,
                    m.nama,
                    j.kode as jenis_mitra,
                    case when td.datang is not null then td.datang else rs.tgl_docin end as tgl_chick_in,
                    tsk.tgl_tutup as tgl_tutup_siklus,
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
                    coalesce(lubs.ekor_mati, 0) as ekor_mati_bl_awal_start,
                    coalesce(pubs.ekor_panen, 0) as ekor_panen_bl_awal_start,
                    coalesce(lubs2.ekor_mati, 0) as ekor_mati_bl_awal2_start,
                    coalesce(pubs2.ekor_panen, 0) as ekor_panen_bl_awal2_start,
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
                    -- biaya level unit (det_jurnal.noreg is null), dibagi sesuai porsi Proporsi masing-masing noreg.
                    -- pool_bl/pool_btl (kepala 6) dibagi ke semua noreg; pool_k5 (kepala 5) hanya dibagi ke
                    -- noreg internal (MI) pakai total proporsi internal, dan seluruhnya masuk BTL.
                    -- round(...,0) krn rasio proporsi (cast as float) hasilkan desimal panjang - BL/BTL dibulatkan
                    -- ke rupiah penuh, beda dgn kategori lain yg masih ikut presisi desimal kolom DB aslinya.
                    round(coalesce(djbb.nilai_bl, 0) + coalesce(djp.pool_bl, 0) * case
                        when coalesce(upt.total_proporsi, 0) <> 0 then cast(nhp.proporsi as float) / upt.total_proporsi
                        else 0
                    end, 0) as nilai_bl,
                    round(coalesce(djbb.nilai_btl, 0) + coalesce(djp.pool_btl, 0) * case
                        when coalesce(upt.total_proporsi, 0) <> 0 then cast(nhp.proporsi as float) / upt.total_proporsi
                        else 0
                    end
                    + case when m.jenis = 'MI' then coalesce(djp.pool_k5, 0) * case
                        when coalesce(upti.total_proporsi, 0) <> 0 then cast(nhp.proporsi as float) / upti.total_proporsi
                        else 0
                    end else 0 end, 0) as nilai_btl,
                    -- Sama seperti nilai_bl/nilai_btl, tapi pakai window bulan sebelumnya (bl_awal_start/end_date),
                    -- dipakai utk Saldo Awal BL/BTL. coalesce nhp_a.proporsi krn noreg_base_awal exclude noreg
                    -- yg tgl_chick_in-nya SETELAH bulan sebelumnya (baru mulai periode ini, blm py histori)
                    round(coalesce(djbb_a.nilai_bl, 0) + coalesce(djp_a.pool_bl, 0) * case
                        when coalesce(upt_a.total_proporsi, 0) <> 0 then cast(coalesce(nhp_a.proporsi, 0) as float) / upt_a.total_proporsi
                        else 0
                    end, 0) as nilai_bl_awal,
                    round(coalesce(djbb_a.nilai_btl, 0) + coalesce(djp_a.pool_btl, 0) * case
                        when coalesce(upt_a.total_proporsi, 0) <> 0 then cast(coalesce(nhp_a.proporsi, 0) as float) / upt_a.total_proporsi
                        else 0
                    end
                    + case when m.jenis = 'MI' then coalesce(djp_a.pool_k5, 0) * case
                        when coalesce(upti_a.total_proporsi, 0) <> 0 then cast(coalesce(nhp_a.proporsi, 0) as float) / upti_a.total_proporsi
                        else 0
                    end else 0 end, 0) as nilai_btl_awal,
                    -- Sama seperti nilai_bl_awal/nilai_btl_awal, tapi 1 bulan lebih mundur lagi (awal2) - dipakai
                    -- utk hitung Saldo Awal bulan sebelumnya, spy Saldo Awal periode ini = Saldo AKHIR bulan
                    -- sebelumnya yg proper (bukan cuma produksi mentah bulan sebelumnya)
                    round(coalesce(djbb_a2.nilai_bl, 0) + coalesce(djp_a2.pool_bl, 0) * case
                        when coalesce(upt_a2.total_proporsi, 0) <> 0 then cast(coalesce(nhp_a2.proporsi, 0) as float) / upt_a2.total_proporsi
                        else 0
                    end, 0) as nilai_bl_awal2,
                    round(coalesce(djbb_a2.nilai_btl, 0) + coalesce(djp_a2.pool_btl, 0) * case
                        when coalesce(upt_a2.total_proporsi, 0) <> 0 then cast(coalesce(nhp_a2.proporsi, 0) as float) / upt_a2.total_proporsi
                        else 0
                    end
                    + case when m.jenis = 'MI' then coalesce(djp_a2.pool_k5, 0) * case
                        when coalesce(upti_a2.total_proporsi, 0) <> 0 then cast(coalesce(nhp_a2.proporsi, 0) as float) / upti_a2.total_proporsi
                        else 0
                    end else 0 end, 0) as nilai_btl_awal2,
                    -- Snapshot histori bulan sebelumnya (fitur \"Proses HPP\") - NULL kalau belum pernah
                    -- diproses utk periode itu, dipakai sbg fallback-check di sa_awal_*_expr (PHP)
                    hsa.saldo_akhir_doc as hist_saldo_akhir_doc,
                    hsa.saldo_akhir_pakan as hist_saldo_akhir_pakan,
                    hsa.saldo_akhir_ovk as hist_saldo_akhir_ovk,
                    hsa.saldo_akhir_oa as hist_saldo_akhir_oa,
                    hsa.saldo_akhir_bl as hist_saldo_akhir_bl,
                    hsa.saldo_akhir_btl as hist_saldo_akhir_btl,
                    hsa.saldo_akhir_rhpp as hist_saldo_akhir_rhpp
                from combined c
                left join rdim_submit rs on c.noreg = rs.noreg
                left join mitra_mapping_max mm on mm.nim = rs.nim
                left join mitra m on m.id = mm.id
                left join jenis j on m.jenis = j.kode
                left join kandang k on k.id = rs.kandang
                left join wilayah w on k.unit = w.id
                left join order_doc_max od on c.noreg = od.noreg
                left join terima_doc_max td on td.no_order = od.no_order
                -- Tgl Tutup Siklus (tampil di kolom sebelah Tgl CI) - noreg di tutup_siklus UNIK (1 baris per
                -- noreg, dicek: 3177 total baris = 3177 distinct noreg), jadi aman LEFT JOIN langsung tanpa
                -- risiko duplikasi baris
                left join tutup_siklus tsk on tsk.noreg = c.noreg
                left join panen_sebagian ps on ps.noreg = c.noreg
                left join panen_sebagian_awal psa on psa.noreg = c.noreg
                left join lhk_upto_end lu on lu.noreg = c.noreg
                left join panen_upto_end pu on pu.noreg = c.noreg
                left join lhk_upto_start lus on lus.noreg = c.noreg
                left join panen_upto_start pus on pus.noreg = c.noreg
                left join lhk_upto_bl_awal_start lubs on lubs.noreg = c.noreg
                left join panen_upto_bl_awal_start pubs on pubs.noreg = c.noreg
                left join lhk_upto_bl_awal2_start lubs2 on lubs2.noreg = c.noreg
                left join panen_upto_bl_awal2_start pubs2 on pubs2.noreg = c.noreg
                left join panen_periode pp on pp.noreg = c.noreg
                left join dss_awal dss_a on dss_a.noreg = c.noreg
                left join dsts_awal dsts_a on dsts_a.noreg = c.noreg
                left join mm_awal mm_a on mm_a.noreg = c.noreg
                left join doc_period_awal doc_a on doc_a.noreg = c.noreg
                left join rhpp_data_awal rhpp_a on rhpp_a.noreg = c.noreg
                left join noreg_hari_proporsi nhp on nhp.noreg = c.noreg
                left join unit_proporsi_total upt on upt.unit = w.kode
                left join unit_proporsi_total_internal upti on upti.unit = w.kode
                left join det_jurnal_bl_btl djbb on djbb.noreg = c.noreg
                left join det_jurnal_bl_btl_pool djp on djp.unit = w.kode
                left join noreg_hari_proporsi_awal nhp_a on nhp_a.noreg = c.noreg
                left join unit_proporsi_total_awal upt_a on upt_a.unit = w.kode
                left join unit_proporsi_total_internal_awal upti_a on upti_a.unit = w.kode
                left join det_jurnal_bl_btl_awal djbb_a on djbb_a.noreg = c.noreg
                left join det_jurnal_bl_btl_pool_awal djp_a on djp_a.unit = w.kode
                left join noreg_hari_proporsi_awal2 nhp_a2 on nhp_a2.noreg = c.noreg
                left join unit_proporsi_total_awal2 upt_a2 on upt_a2.unit = w.kode
                left join unit_proporsi_total_internal_awal2 upti_a2 on upti_a2.unit = w.kode
                left join det_jurnal_bl_btl_awal2 djbb_a2 on djbb_a2.noreg = c.noreg
                left join det_jurnal_bl_btl_pool_awal2 djp_a2 on djp_a2.unit = w.kode
                left join history_saldo_awal hsa on hsa.noreg = c.noreg
                where m.id is not null
                    ".$sql_unit."
                    ".$sql_tutup_siklus."
                ) data
                ) base
            ) calc
            order by
                calc.unit asc,
                calc.tgl_chick_in asc;
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

    /**
     * Proses HPP: jalankan getData() utk periode/unit terpilih, lalu simpan snapshot-nya ke
     * lembar_kerja_hpp_history (dipakai nanti sbg dasar Saldo Awal BL/BTL bulan berikutnya).
     * Idempotent: baris histori existing utk (tahun, bulan, unit yg sama) dihapus dulu sebelum
     * insert ulang, jadi aman dijalankan berkali-kali utk periode yg sama (mis. setelah ada koreksi).
     */
    public function prosesHpp()
    {
        $params = $this->input->post('params');

        try {
            $unit = $params['unit'];
            $start_date = $params['start_date'];
            $end_date = $params['end_date'];

            $data = $this->getData( $params );

            $tahun = (int) date('Y', strtotime($start_date));
            $bulan = (int) date('n', strtotime($start_date));

            $created_by = isset($this->userdata['username_user']) ? $this->userdata['username_user'] : null;

            $m_history = new \Model\Storage\LembarKerjaHppHistory_model();

            // Project ini ga bind Laravel container/Facade (cuma pakai Capsule Manager langsung utk Eloquent,
            // lihat MY_Controller::setDBManager) - jadi \Illuminate\Support\Facades\DB::transaction() resolve
            // ke null root & fatal. Pakai koneksi dari model instance (getConnection(), pola sama spt hydrateRaw
            // dkk yg sudah dipakai di codebase ini) yg support transaction() jg.
            $m_history->getConnection()->transaction(function() use ($m_history, $tahun, $bulan, $unit, $data, $start_date, $end_date, $created_by) {
                $q = $m_history->where('tahun', $tahun)->where('bulan', $bulan);
                if ( stristr('all', $unit) === false ) {
                    $q = $q->where('unit', $unit);
                }
                $q->delete();

                if ( !empty($data) ) {
                    // Kolom money/hitungan di tabel history NOT NULL DEFAULT 0 - tapi default itu cuma
                    // berlaku kalau kolom di-OMIT dari insert, bukan kalau eksplisit dikirim NULL. Sebagian
                    // noreg (mis. yg di-exclude dari alokasi krn sudah panen semua sblm periode ini) hasilkan
                    // NULL utk hari/proporsi/dsb dari query - wajib di-null-safe di sini, kalau tidak insert
                    // akan gagal krn constraint. start_date_proporsi/end_date_proporsi/populasi/tgl_chick_in/
                    // jenis_mitra memang NULLable di skema jadi dibiarkan apa adanya.
                    $num0 = function($val) {
                        return is_null($val) ? 0 : $val;
                    };

                    $rows = null;
                    foreach ($data as $v) {
                        $rows[] = array(
                            'unit' => $v['unit'],
                            'noreg' => $v['noreg'],
                            'nama' => $v['nama'],
                            'jenis_mitra' => $v['jenis_mitra'],
                            'tgl_chick_in' => $v['tgl_chick_in'],
                            'tgl_tutup_siklus' => $v['tgl_tutup_siklus'],
                            'populasi' => $v['populasi'],
                            'tahun' => $tahun,
                            'bulan' => $bulan,
                            'start_date' => $start_date,
                            'end_date' => $end_date,

                            'sa_awal_doc' => $num0($v['sa_awal_doc']),
                            'sa_awal_pakan' => $num0($v['sa_awal_pakan']),
                            'sa_awal_ovk' => $num0($v['sa_awal_ovk']),
                            'sa_awal_oa' => $num0($v['sa_awal_oa']),
                            'sa_awal_bl' => $num0($v['sa_awal_bl']),
                            'sa_awal_btl' => $num0($v['sa_awal_btl']),
                            'sa_awal_rhpp' => $num0($v['sa_awal_rhpp']),
                            'sa_awal_total' => $num0($v['sa_awal_total']),

                            'produksi_doc' => $num0($v['produksi_doc']),
                            'produksi_pakan' => $num0($v['produksi_pakan']),
                            'produksi_ovk' => $num0($v['produksi_ovk']),
                            'produksi_oa' => $num0($v['produksi_oa']),
                            'produksi_bl' => $num0($v['produksi_bl']),
                            'produksi_btl' => $num0($v['produksi_btl']),
                            'produksi_rhpp' => $num0($v['produksi_rhpp']),
                            'produksi_total' => $num0($v['produksi_total']),

                            'tersedia_doc' => $num0($v['tersedia_doc']),
                            'tersedia_pakan' => $num0($v['tersedia_pakan']),
                            'tersedia_ovk' => $num0($v['tersedia_ovk']),
                            'tersedia_oa' => $num0($v['tersedia_oa']),
                            'tersedia_bl' => $num0($v['tersedia_bl']),
                            'tersedia_btl' => $num0($v['tersedia_btl']),
                            'tersedia_rhpp' => $num0($v['tersedia_rhpp']),
                            'tersedia_total' => $num0($v['tersedia_total']),

                            'dijual_doc' => $num0($v['dijual_doc']),
                            'dijual_pakan' => $num0($v['dijual_pakan']),
                            'dijual_ovk' => $num0($v['dijual_ovk']),
                            'dijual_oa' => $num0($v['dijual_oa']),
                            'dijual_bl' => $num0($v['dijual_bl']),
                            'dijual_btl' => $num0($v['dijual_btl']),
                            'dijual_rhpp' => $num0($v['dijual_rhpp']),
                            'dijual_total' => $num0($v['dijual_total']),

                            'saldo_akhir_doc' => $num0($v['saldo_akhir_doc']),
                            'saldo_akhir_pakan' => $num0($v['saldo_akhir_pakan']),
                            'saldo_akhir_ovk' => $num0($v['saldo_akhir_ovk']),
                            'saldo_akhir_oa' => $num0($v['saldo_akhir_oa']),
                            'saldo_akhir_bl' => $num0($v['saldo_akhir_bl']),
                            'saldo_akhir_btl' => $num0($v['saldo_akhir_btl']),
                            'saldo_akhir_rhpp' => $num0($v['saldo_akhir_rhpp']),
                            'saldo_akhir_total' => $num0($v['saldo_akhir_total']),

                            'terjual' => $num0($v['terjual']),
                            'persentase_dijual' => $num0($v['persentase_dijual']),
                            'start_date_proporsi' => $v['start_date_proporsi'],
                            'end_date_proporsi' => $v['end_date_proporsi'],
                            'hari' => $num0($v['hari']),
                            'proporsi' => $num0($v['proporsi']),
                            'stock_tersedia' => $num0($v['stock_tersedia']),
                            'saldo_awal_stok' => $num0($v['saldo_awal_stok']),
                            'sisa_stok' => $num0($v['sisa_stok']),

                            'created_by' => $created_by,
                        );
                    }

                    // Batasi jumlah baris per query insert (kolom x baris jangan sampai kena limit
                    // parameter SQL Server ~2100) - 60 kolom x 30 baris = 1800, aman.
                    foreach ( array_chunk($rows, 30) as $chunk ) {
                        $m_history->insert($chunk);
                    }
                }
            });

            $this->result['status'] = 1;
            $this->result['message'] = 'Berhasil memproses HPP periode '.str_replace('-', '/', $start_date).' - '.str_replace('-', '/', $end_date).' ('.count($data ?: []).' baris) & menyimpan ke histori.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
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

        // Susunan kolom PERSIS sama dgn tabel di view (index.php/list.php): identitas (7 kolom, rowspan 2
        // baris header) -> Saldo Awal/Produksi/Tersedia (masing2 8 kolom: DOC/Pakan/OVK/OA/BL/BTL/RHPP/Total)
        // -> 8 kolom tengah tunggal (Buku Balik Cad RHPP s/d Proporsi) -> Dijual/Saldo Akhir (masing2 8 kolom).
        // Dibangun via loop (bukan hardcode huruf kolom spt versi lama) supaya selalu sinkron kalau view-nya
        // berubah lagi nanti - tinggal ubah array definisi di sini, gak perlu itung ulang huruf Excel manual.
        $sub_kategori = array(
            array('key' => 'doc', 'label' => 'DOC'),
            array('key' => 'pakan', 'label' => 'PAKAN'),
            array('key' => 'ovk', 'label' => 'OVK'),
            array('key' => 'oa', 'label' => 'OA'),
            array('key' => 'bl', 'label' => 'BL'),
            array('key' => 'btl', 'label' => 'BTL'),
            array('key' => 'rhpp', 'label' => 'RHPP'),
            array('key' => 'total', 'label' => 'TOTAL'),
        );

        $identitas = array(
            array('field' => 'unit', 'label' => 'UNIT', 'type' => 'string'),
            array('field' => 'noreg', 'label' => 'NOREG', 'type' => 'string'),
            array('field' => 'nama', 'label' => 'PLASMA', 'type' => 'string'),
            array('field' => 'jenis_mitra', 'label' => 'JENIS MITRA', 'type' => 'string'),
            array('field' => 'tgl_chick_in', 'label' => 'TGL CI', 'type' => 'date'),
            array('field' => 'tgl_tutup_siklus', 'label' => 'TGL TUTUP SIKLUS', 'type' => 'date'),
            array('field' => 'populasi', 'label' => 'POPULASI', 'type' => 'integer'),
        );

        // Buku Balik Cad RHPP msh placeholder "-" di view (blm diimplementasi, lihat komentar "menyusul"
        // di list.php) - disamakan di sini, BUKAN dihitung dari pdpt_peternak spt versi lama.
        $kolom_tengah = array(
            array('field' => null, 'label' => 'BUKU BALIK CAD RHPP', 'type' => 'string', 'static' => '-'),
            array('field' => 'sisa_stok', 'label' => 'STOCK TERSEDIA', 'type' => 'integer'),
            array('field' => 'terjual', 'label' => 'TERJUAL', 'type' => 'integer'),
            array('field' => 'persentase_dijual', 'label' => 'PERSENTASE (%)', 'type' => 'decimal2'),
            array('field' => 'start_date_proporsi', 'label' => 'START DATE PROPORSI', 'type' => 'date'),
            array('field' => 'end_date_proporsi', 'label' => 'END DATE PROPORSI', 'type' => 'date'),
            array('field' => 'hari', 'label' => 'HARI', 'type' => 'decimal2'),
            array('field' => 'proporsi', 'label' => 'PROPORSI', 'type' => 'decimal2'),
        );

        $grup_awal = array(
            array('prefix' => 'sa_awal', 'label' => 'SALDO AWAL'),
            array('prefix' => 'produksi', 'label' => 'PRODUKSI'),
            array('prefix' => 'tersedia', 'label' => 'TERSEDIA'),
        );
        $grup_akhir = array(
            array('prefix' => 'dijual', 'label' => 'DIJUAL'),
            array('prefix' => 'saldo_akhir', 'label' => 'SALDO AKHIR'),
        );

        $columns = array();
        foreach ($identitas as $c) {
            $columns[] = array('field' => $c['field'], 'label' => $c['label'], 'type' => $c['type'], 'group' => null);
        }
        foreach ($grup_awal as $g) {
            foreach ($sub_kategori as $s) {
                $columns[] = array('field' => $g['prefix'].'_'.$s['key'], 'label' => $s['label'], 'type' => 'decimal2', 'group' => $g['label']);
            }
        }
        foreach ($kolom_tengah as $c) {
            $columns[] = array('field' => $c['field'], 'label' => $c['label'], 'type' => $c['type'], 'group' => null, 'static' => isset($c['static']) ? $c['static'] : null);
        }
        foreach ($grup_akhir as $g) {
            foreach ($sub_kategori as $s) {
                $columns[] = array('field' => $g['prefix'].'_'.$s['key'], 'label' => $s['label'], 'type' => 'decimal2', 'group' => $g['label']);
            }
        }

        $arr_header = array();
        foreach ($columns as $i => $c) {
            $arr_header[] = toAlpha($i + 1);
        }

        $arr_column = null;
        $idx = 0;
        $arr_column[ $idx ] = array(
            'A' => array('value' => 'LEMBAR KERJA', 'data_type' => 'string', 'text_style' => 'bold')
        );
        $idx++;
        $arr_column[ $idx ] = array(
            'A' => array('value' => 'PERIODE '.str_replace('-', '/', $start_date).' - '.str_replace('-', '/', $end_date), 'data_type' => 'string', 'colspan' => array('A', toAlpha(count($identitas))), 'align' => 'left', 'text_style' => 'bold', 'border' => 'none'),
        );
        $idx++;

        // Row header 1 (kolom identitas & kolom tengah rowspan 2 baris; kolom grup colspan 8 di baris ini saja)
        $row_header1 = array();
        foreach ($columns as $i => $c) {
            $letter = $arr_header[ $i ];
            if ( $c['group'] === null ) {
                $row_header1[ $letter ] = array('value' => $c['label'], 'data_type' => 'string', 'rowspan' => array($letter.'3', $letter.'4'), 'align' => 'center', 'text_style' => 'bold');
            }
        }
        // Kolom grup: cari huruf awal & akhir tiap grup (8 sub-kategori berurutan) utk colspan-nya
        $group_ranges = array();
        foreach ($columns as $i => $c) {
            if ( $c['group'] !== null ) {
                if ( !isset($group_ranges[ $c['group'] ]) ) {
                    $group_ranges[ $c['group'] ] = array('start' => $arr_header[ $i ], 'end' => $arr_header[ $i ]);
                } else {
                    $group_ranges[ $c['group'] ]['end'] = $arr_header[ $i ];
                }
            }
        }
        foreach ($group_ranges as $label => $range) {
            $row_header1[ $range['start'] ] = array('value' => $label, 'data_type' => 'string', 'colspan' => array($range['start'], $range['end']), 'align' => 'center', 'text_style' => 'bold');
        }
        $arr_column[ $idx ] = $row_header1;
        $idx++;

        // Row header 2 (sub-label DOC/Pakan/OVK/OA/BL/BTL/RHPP/Total, hanya utk kolom yg masuk grup)
        $row_header2 = array();
        foreach ($columns as $i => $c) {
            if ( $c['group'] !== null ) {
                $row_header2[ $arr_header[ $i ] ] = array('value' => $c['label'], 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold');
            }
        }
        $arr_column[ $idx ] = $row_header2;
        $idx++;

        $start_row_header = $idx;

        if ( !empty($data) ) {
            foreach ($data as $value) {
                $row = array();
                foreach ($columns as $i => $c) {
                    $letter = $arr_header[ $i ];
                    if ( array_key_exists('static', $c) && $c['static'] !== null ) {
                        $row[ $letter ] = array('value' => $c['static'], 'data_type' => 'string');
                        continue;
                    }
                    $cell_value = $c['field'] === 'nama' ? strtoupper($value[ $c['field'] ]) : $value[ $c['field'] ];
                    $row[ $letter ] = array('value' => $cell_value, 'data_type' => $c['type']);
                }
                $arr_column[ $idx ] = $row;
                $idx++;
            }
        }

        Modules::run( 'base/ExportExcel/exportExcelUsingSpreadSheet', $filename, $arr_header, $arr_column, $start_row_header, 0 );

        $this->load->helper('download');
        force_download('export_excel/'.$filename.'.xlsx', NULL);
    }
}