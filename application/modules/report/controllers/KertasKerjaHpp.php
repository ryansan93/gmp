<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class KertasKerjaHpp extends Public_Controller {

    private $pathView = 'report/kertas_kerja_hpp/';
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
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/jquery/tupage-table/jquery.tupage.table.js",
                "assets/report/kertas_kerja_hpp/js/kertas-kerja-hpp.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/jquery/tupage-table/jquery.tupage.table.css",
                "assets/report/kertas_kerja_hpp/css/kertas-kerja-hpp.css",
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
            $content['title_menu'] = 'Laporan Kertas Kerja HPP';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
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
                data.pemakaian_pkn,
                ((isnull(data.sa_pkn, 0)+data.beli_pkn+data.mutasi_msk_pkn) - (data.mutasi_klwr_pkn+data.pemakaian_pkn)) as sisa_pkn,
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
                data.pemakaian_oa,
                ((isnull(data.sa_oa, 0)+data.beli_oa+data.mutasi_msk_oa) - (data.mutasi_klwr_oa+data.pemakaian_oa)) as sisa_oa,
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
                    sum(data.pemakaian_oa) as pemakaian_oa,
                    isnull(rhpp_p.pdpt_peternak_belum_pajak, 0) as pdpt_peternak,
                    isnull(rhpp_p.pdpt_peternak_belum_pajak, 0) + sum(data.pemakaian_pkn) + sum(data.pemakaian_ovk) + sum(data.pemakaian_doc) + sum(data.pemakaian_oa) as total
                from
                (
                    select
                        pkn.noreg,
                        isnull(sa.saldo_awal, 0) as sa_pkn,
                        pkn.beli_pkn,
                        pkn.mutasi_msk_pkn,
                        pkn.mutasi_klwr_pkn,
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
                        0 as pemakaian_oa
                    from
                    (
                        select
                            pkn.noreg,
                            isnull(sum(pkn.jml_beli * pkn.hrg_beli), 0) as beli_pkn,
                            isnull(sum(pkn.jml_mutasi_msk * pkn.hrg_mutasi_msk), 0) as mutasi_msk_pkn,
                            isnull(sum(pkn.jml_mutasi_klwr * pkn.hrg_mutasi_klwr), 0) as mutasi_klwr_pkn,
                            isnull(sum(pkn.jml_pemakaian * pkn.hrg_pemakaian), 0) as pemakaian_pkn,
                            isnull(sum(pkn.jml_beli * pkn.hrg_beli), 0) + isnull(sum(pkn.jml_mutasi_msk * pkn.hrg_mutasi_msk), 0) - (isnull(sum(pkn.jml_mutasi_klwr * pkn.hrg_mutasi_klwr), 0) + isnull(sum(pkn.jml_pemakaian * pkn.hrg_pemakaian), 0)) as sisa_pkn
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
                                sum(dsts.jumlah) as jml_pemakaian,
                                dss.hrg_beli as hrg_pemakaian
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
                        0 as pemakaian_oa
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
                        0 as pemakaian_oa
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
                        oa.pemakaian_oa
                    from 
                    (
                        select
                            oa.noreg,
                            isnull(sum(oa.jml_beli * oa.hrg_beli), 0) as beli_oa,
                            isnull(sum(oa.jml_mutasi_msk * oa.hrg_mutasi_msk), 0) as mutasi_msk_oa,
                            isnull(sum(oa.jml_mutasi_klwr * oa.hrg_mutasi_klwr), 0) as mutasi_klwr_oa,
                            isnull(sum(oa.jml_beli * oa.hrg_beli), 0) + isnull(sum(oa.jml_mutasi_msk * oa.hrg_mutasi_msk), 0) - isnull(sum(oa.jml_mutasi_klwr * oa.hrg_mutasi_klwr), 0) as pemakaian_oa
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
                                    dsts.tgl_trans < '".$start_date."'
                                group by
                                    dss.noreg, dss.oa
                            ) oa
                            group by
                                oa.noreg
                        ) sa
                        on
                            oa.noreg = sa.noreg
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
                left join
                    kandang k
                    on
                        k.mitra_mapping = mm.id and
                        k.kandang = cast(SUBSTRING(data.noreg, 10, 2) as int)
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
                where
                    m.id is not null
                    and td.id is not null
                    ".$sql_unit."
                group by
                    w.kode,
                    data.noreg,
                    m.nama,
                    td.datang,
                    rs.tgl_docin,
                    td.jml_ekor,
                    rs.populasi,
                    rhpp_p.pdpt_peternak_belum_pajak
            ) data
            order by
                data.noreg asc,
                data.tgl_chick_in asc
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

        $filename = strtoupper("KERTAS_KERJA_PERIODE_");
        $filename = $filename.str_replace('-', '', $start_date).'_'.str_replace('-', '', $end_date).'.xls';

        $arr_column = null;

        $idx = 0;
        $arr_column[ $idx ] = array(
            'A' => array('value' => 'KERTAS KERJA', 'data_type' => 'string', 'text_style' => 'bold')
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
            'AD' => array('value' => 'OA', 'data_type' => 'string', 'colspan' => array('Y','AD'), 'align' => 'center', 'text_style' => 'bold'),
            'AE' => array('value' => 'RHPP', 'data_type' => 'string','rowspan' => array('AE3','AE4'), 'align' => 'center', 'text_style' => 'bold'),
            'AF' => array('value' => 'TOTAL', 'data_type' => 'string','rowspan' => array('AF3','AF4'), 'align' => 'center', 'text_style' => 'bold'),
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
        );
        $idx++;

        $start_row_header = $idx;

        $arr_header = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF');
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
                    'J' => array('value' => (0), 'data_type' => 'decimal2'),
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
                    'AC' => array('value' => (0), 'data_type' => 'decimal2'),
                    'AD' => array('value' => ($value['pemakaian_oa']), 'data_type' => 'decimal2'),
                    'AE' => array('value' => ($value['pdpt_peternak']), 'data_type' => 'decimal2'),
                    'AF' => array('value' => ($value['total']), 'data_type' => 'decimal2'),
                );

                $idx++;
            }
        }

        Modules::run( 'base/ExportExcel/exportExcelUsingSpreadSheet', $filename, $arr_header, $arr_column, $start_row_header, 0 );

        $this->load->helper('download');
        force_download('export_excel/'.$filename.'.xlsx', NULL);
    }
}