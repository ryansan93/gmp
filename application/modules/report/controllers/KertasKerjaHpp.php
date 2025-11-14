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
        // if ( $akses['a_view'] == 1 ) {
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
                sum(beli_pkn) as beli_pkn,
                sum(mutasi_msk_pkn) as mutasi_msk_pkn,
                sum(mutasi_klwr_pkn) as mutasi_klwr_pkn,
                sum(pemakaian_pkn) as pemakaian_pkn,
                sum(beli_ovk) as beli_ovk,
                sum(mutasi_msk_ovk) as mutasi_msk_ovk,
                sum(mutasi_klwr_ovk) as mutasi_klwr_ovk,
                sum(pemakaian_ovk) as pemakaian_ovk,
                sum(beli_doc) as beli_doc,
                sum(mutasi_msk_doc) as mutasi_msk_doc,
                sum(mutasi_klwr_doc) as mutasi_klwr_doc,
                sum(pemakaian_doc) as pemakaian_doc,
                sum(beli_oa) as beli_oa,
                sum(mutasi_msk_oa) as mutasi_msk_oa,
                sum(mutasi_klwr_oa) as mutasi_klwr_oa,
                sum(pemakaian_oa) as pemakaian_oa,
                rhpp_p.pdpt_peternak_belum_pajak as pdpt_peternak,
                (sum(pemakaian_pkn) + (sum(pemakaian_ovk)-sum(mutasi_klwr_ovk)) + sum(pemakaian_doc) + sum(pemakaian_oa)) as total
            from
            (
                select
                    pkn.noreg,
                    sum(pkn.jml_beli * pkn.hrg_beli) as beli_pkn,
                    sum(pkn.jml_mutasi_msk * pkn.hrg_mutasi_msk) as mutasi_msk_pkn,
                    sum(pkn.jml_mutasi_klwr * pkn.hrg_mutasi_klwr) as mutasi_klwr_pkn,
                    sum(pkn.jml_pemakaian * pkn.hrg_pemakaian) as pemakaian_pkn,
                    0 as beli_ovk,
                    0 as mutasi_msk_ovk,
                    0 as mutasi_klwr_ovk,
                    0 as pemakaian_ovk,
                    0 as beli_doc,
                    0 as mutasi_msk_doc,
                    0 as mutasi_klwr_doc,
                    0 as pemakaian_doc,
                    0 as beli_oa,
                    0 as mutasi_msk_oa,
                    0 as mutasi_klwr_oa,
                    0 as pemakaian_oa
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

                union all

                select
                    ovk.noreg,
                    0 as beli_pkn,
                    0 as mutasi_msk_pkn,
                    0 as mutasi_klwr_pkn,
                    0 as pemakaian_pkn,
                    sum(ovk.jml_beli * ovk.hrg_beli) as beli_ovk,
                    sum(ovk.jml_mutasi_msk * ovk.hrg_mutasi_msk) as mutasi_msk_ovk,
                    sum(ovk.jml_mutasi_klwr * ovk.hrg_mutasi_klwr) as mutasi_klwr_ovk,
                    sum(ovk.jml_beli * ovk.hrg_beli) as pemakaian_ovk,
                    -- sum(ovk.jml_pemakaian * ovk.hrg_pemakaian) as pemakaian_ovk,
                    0 as beli_doc,
                    0 as mutasi_msk_doc,
                    0 as mutasi_klwr_doc,
                    0 as pemakaian_doc,
                    0 as beli_oa,
                    0 as mutasi_msk_oa,
                    0 as mutasi_klwr_oa,
                    0 as pemakaian_oa
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

                union all

                select
                    doc.noreg,
                    0 as beli_pkn,
                    0 as mutasi_msk_pkn,
                    0 as mutasi_klwr_pkn,
                    0 as pemakaian_pkn,
                    0 as beli_ovk,
                    0 as mutasi_msk_ovk,
                    0 as mutasi_klwr_ovk,
                    0 as pemakaian_ovk,
                    sum(doc.jml_beli * doc.hrg_beli) as beli_doc,
                    sum(doc.jml_mutasi_msk * doc.hrg_mutasi_msk) as mutasi_msk_doc,
                    sum(doc.jml_mutasi_klwr * doc.hrg_mutasi_klwr) as mutasi_klwr_doc,
                    sum(doc.jml_beli * doc.hrg_beli) as pemakaian_doc,
                    -- sum(doc.jml_pemakaian * doc.hrg_pemakaian) as pemakaian_doc,
                    0 as beli_oa,
                    0 as mutasi_msk_oa,
                    0 as mutasi_klwr_oa,
                    0 as pemakaian_oa
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
                    where
                        dss.jenis_barang = 'doc' and
                        dss.tgl_trans between '".$start_date."' and '".$end_date."'
                    group by
                        dss.noreg, dss.kode_trans, dss.hrg_beli
                ) doc
                group by
                    doc.noreg

                union all

                select
                    oa.noreg,
                    0 as beli_pkn,
                    0 as mutasi_msk_pkn,
                    0 as mutasi_klwr_pkn,
                    0 as pemakaian_pkn,
                    0 as beli_ovk,
                    0 as mutasi_msk_ovk,
                    0 as mutasi_klwr_ovk,
                    0 as pemakaian_ovk,
                    0 as beli_doc,
                    0 as mutasi_msk_doc,
                    0 as mutasi_klwr_doc,
                    0 as pemakaian_doc,
                    sum(oa.jml_beli * oa.hrg_beli) as beli_oa,
                    sum(oa.jml_mutasi_msk * oa.hrg_mutasi_msk) as mutasi_msk_oa,
                    sum(oa.jml_mutasi_klwr * oa.hrg_mutasi_klwr) as mutasi_klwr_oa,
                    sum(oa.jml_pemakaian * oa.hrg_pemakaian) as pemakaian_oa
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
                (select * from rhpp where jenis = 'rhpp_plasma') rhpp_p
                on
                    data.noreg = rhpp_p.noreg
            where
            	m.id is not null
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
            order by
				td.datang asc,
				rs.tgl_docin asc
        ";
        cetak_r( $sql, 1 );
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

        $kas = $params['kas'];
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

        $data = $this->getData( $params );

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from coa where coa = '".$kas."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $nama = null;
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray()[0];

            $nama = $d_conf['nama_coa'];
        }

        $filename = strtoupper("LAPORAN_BANK_".str_replace(' ', '_', $d_conf['nama_coa'])."_");
        $filename = $filename.str_replace('-', '', $start_date).'_'.str_replace('-', '', $end_date).'.xls';

        $arr_column = null;

        $idx = 0;
        $arr_column[ $idx ] = array(
            'Saldo' => array('value' => 'LAPORAN BANK '.strtoupper($nama), 'data_type' => 'string', 'colspan' => array('A','F'), 'align' => 'left', 'text_style' => 'bold', 'border' => 'none'),
        );
        $idx++;
        $arr_column[ $idx ] = array(
            'Saldo' => array('value' => 'PERIODE '.str_replace('-', '/', $start_date).' - '.str_replace('-', '/', $end_date), 'data_type' => 'string', 'colspan' => array('A','F'), 'align' => 'left', 'text_style' => 'bold', 'border' => 'none'),
        );
        $idx++;

        $start_row_header = $idx+1;

        $arr_header = array('Tanggal', 'No', 'Keterangan', 'Masuk', 'Keluar', 'Saldo');
        if ( !empty($data) ) {
            $kode_kas = null; 
            $idx_kas = 0;
            $saldo = 0;

            $saldo_kas = 0;

            $tot_debet_kas = 0;
            $tot_kredit_kas = 0;

            $gt_debet = 0;
            $gt_kredit = 0;
            $gt_saldo = 0;
            foreach ($data as $key => $value) {
                if ( $kode_kas <> $value['kas'] ) {
                    $idx_kas = 0;
                    $saldo = 0;
                    $kode_kas = $value['kas'];
                    
                    $tot_debet_kas = 0;
                    $tot_kredit_kas = 0;
                }

                $tanggal = !empty($value['tanggal']) ? (($value['tanggal'] < '2000-01-01') ? null : $value['tanggal']) : null;
                $kode_trans = $value['kode'];
                $keterangan = $value['keterangan'];

                $debet = $value['debet'];
                $kredit = $value['kredit'];
                $saldo = ($saldo+$debet)-$kredit;

                $tot_debet_kas += $debet;
                $tot_kredit_kas += $kredit;

                $gt_debet += $debet;
                $gt_kredit += $kredit;

                if ( $idx_kas == 0 ) {
                    if ( stristr($value['keterangan'], 'saldo awal') === false ) {
                        $arr_column[ $idx ] = array(
                            'Tanggal' => array('value' => '', 'data_type' => 'date'),
                            'No' => array('value' => '', 'data_type' => 'string'),
                            'Keterangan' => array('value' => 'Saldo Awal', 'data_type' => 'string'),
                            'Masuk' => array('value' => 0, 'data_type' => 'decimal2'),
                            'Keluar' => array('value' => 0, 'data_type' => 'decimal2'),
                            'Saldo' => array('value' => 0, 'data_type' => 'decimal2')
                        );

                        $idx++;
                    }
                }

                $arr_column[ $idx ] = array(
                    'Tanggal' => array('value' => !empty($tanggal) ? $tanggal : '', 'data_type' => 'date'),
                    'No' => array('value' => !empty($kode_trans) ? $kode_trans : '', 'data_type' => 'string'),
                    'Keterangan' => array('value' => $keterangan, 'data_type' => 'string'),
                    'Masuk' => array('value' => $debet, 'data_type' => 'decimal2'),
                    'Keluar' => array('value' => $kredit, 'data_type' => 'decimal2'),
                    'Saldo' => array('value' => $saldo, 'data_type' => 'decimal2')
                );

                if ( !empty($kode_kas) && (!isset($data[$key+1]) || $kode_kas <> $data[$key+1]['kas']) ) {
                    $idx++;

                    $arr_column[ $idx ] = array(
                        'Keterangan' => array('value' => 'Total', 'data_type' => 'string', 'colspan' => array('A','C'), 'align' => 'right', 'text_style' => 'bold'),
                        'Masuk' => array('value' => $gt_debet, 'data_type' => 'decimal2', 'text_style' => 'bold'),
                        'Keluar' => array('value' => $gt_kredit, 'data_type' => 'decimal2', 'text_style' => 'bold'),
                        'Saldo' => array('value' => $saldo, 'data_type' => 'decimal2', 'text_style' => 'bold')
                    );
                }

                $idx++;
                $idx_kas++;
            }
        }

        Modules::run( 'base/ExportExcel/exportExcelUsingSpreadSheet', $filename, $arr_header, $arr_column, $start_row_header );

        $this->load->helper('download');
        force_download('export_excel/'.$filename.'.xlsx', NULL);
    }
}