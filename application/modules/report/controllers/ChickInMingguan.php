<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class ChickInMingguan extends Public_Controller {

    private $pathView = 'report/chick_in_mingguan/';
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
                "assets/report/chick_in_mingguan/js/chick-in-mingguan.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/chick_in_mingguan/css/chick-in-mingguan.css",
            ));

            $data = $this->includes;

            $content['perusahaan'] = $this->getPerusahaan();
            $content['unit'] = $this->getUnit();
            $content['akses'] = $akses;
            $content['title_menu'] = 'Laporan Chick In Mingguan';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getPerusahaan() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                prs1.kode,
                prs1.perusahaan as nama
            from perusahaan prs1
            right join
                (select max(id) as id, kode from perusahaan group by kode) prs2
                on
                    prs1.id = prs2.id
            order by
                prs1.kode
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getUnit() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                w1.kode,
                REPLACE(REPLACE(w1.nama, 'Kab ', ''), 'Kota ', '') as nama
            from wilayah w1
            right join
                (select max(id) as id, kode from wilayah group by kode) w2
                on
                    w1.id = w2.id
            where
                w1.jenis = 'UN'
            order by
                w1.kode
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getLists() {
        $params = $this->input->get('params');

        // cetak_r( $params, 1 );

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];

        $sql_perusahaan = null;
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan = "and data.kode_perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $sql_unit = null;
        if ( !in_array('all', $unit) ) {
            $sql_unit = "and data.kode_unit in ('".implode("', '", $unit)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                prev.start_date as prev_start_date,
                prev.end_date as prev_end_date,
                data.start_date,
                data.end_date,
                data.kode_perusahaan,
                prs.nama as nama_perusahaan,
                data.kode_unit,
                w.nama as nama_unit,
                isnull(sum(prev.jumlah_est), 0) as prev_jumlah_ekor_est,
                isnull(sum(prev.jumlah_real), 0) as prev_jumlah_ekor_real,
                isnull(sum(prev.jumlah_est), 0) / 100 as prev_jumlah_box_est,
                isnull(sum(prev.jumlah_real), 0) / 100 as prev_jumlah_box_real,
                (isnull(sum(prev.jumlah_real), 0) / 100) - (isnull(sum(prev.jumlah_est), 0) / 100) as selisih_box_prev,
                case
                    when isnull(sum(prev.jumlah_real), 0) > 0 and isnull(sum(prev.jumlah_est), 0) > 0 then
                        ((isnull(sum(prev.jumlah_real), 0) / 100) / (isnull(sum(prev.jumlah_est), 0) / 100)) * 100
                    else
                        0
                end as persentase_prev,
                sum(data.jumlah_est) as jumlah_ekor_est,
                sum(data.jumlah_real) as jumlah_ekor_real,
                sum(data.jumlah_est) / 100 as jumlah_box_est,
                sum(data.jumlah_real) / 100 as jumlah_box_real,
                (sum(data.jumlah_real) / 100) - (sum(data.jumlah_est) / 100) as selisih_box,
                case
                    when sum(data.jumlah_real) > 0 and sum(data.jumlah_est) > 0 then
                        ((sum(data.jumlah_real) / 100) / (sum(data.jumlah_est) / 100)) * 100
                    else
                        0
                end as persentase,
                (sum(data.jumlah_est) / 100) - (isnull(sum(prev.jumlah_est), 0) / 100) as box_est_prev_with_now,
                case
                    when isnull(sum(prev.jumlah_est), 0) > 0 and sum(data.jumlah_est) > 0 then
                        (cast(sum(data.jumlah_est) - isnull(sum(prev.jumlah_est), 0) as decimal(10, 2)) / isnull(sum(prev.jumlah_est), 0)) * 100
                    else
                        0
                end as persentase_est_prev_with_now,
                (sum(data.jumlah_real) / 100) - (isnull(sum(prev.jumlah_real), 0) / 100) as box_real_prev_with_now,
                case
                    when isnull(sum(prev.jumlah_real), 0) > 0 and sum(data.jumlah_real) > 0 then
                        (cast(sum(data.jumlah_real) - isnull(sum(prev.jumlah_real), 0) as decimal(10, 2)) / isnull(sum(prev.jumlah_real), 0)) * 100
                    else
                        0
                end as persentase_real_prev_with_now
            from
            (
                select
                    data.prev_start_date,
                    data.prev_end_date,
                    data.start_date,
                    data.end_date,
                    data.kode_perusahaan,
                    data.kode_unit,
                    isnull(sum(data.jumlah_est), 0) as jumlah_est,
                    isnull(sum(data.jumlah_real), 0) as jumlah_real
                from
                    (
                        select
                            dateadd(dd, -7, start_date) as prev_start_date,
                            dateadd(dd, -7, end_date) as prev_end_date,
                            start_date,
                            end_date,
                            kode_perusahaan,
                            kode_unit,
                            jumlah as jumlah_est,
                            0 as jumlah_real
                        from 
                            estimasi_chick_in_mingguan est
    
                        union all
    
                        select
                            dateadd(dd, -7, data.start_date) as prev_start_date,
                            dateadd(dd, -7, data.end_date) as prev_end_date,
                            data.start_date,
                            data.end_date,
                            data.kode_perusahaan,
                            data.kode_unit,
                            0 as jumlah_est,
                            sum(data.jumlah) as jumlah
                        from (
                            select  
                                case
                                    when DATEPART(dw, td.datang) = 1 then
                                        convert(varchar(10), DATEADD(dd, -6, td.datang), 120)
                                    else
                                        convert(varchar(10), DATEADD(dd, -(DATEPART(dw, td.datang)-2), td.datang), 120)
                                end as start_date,
                                case
                                    when DATEPART(dw, td.datang) = 1 then
                                        convert(varchar(10), DATEADD(dd, 6, DATEADD(dd, -6, td.datang)), 120)
                                    else
                                        convert(varchar(10), DATEADD(dd, 6, DATEADD(dd, -(DATEPART(dw, td.datang)-2), td.datang)), 120)
                                end as end_date,
                                od.perusahaan as kode_perusahaan,
                                SUBSTRING(od.no_order, 5, 3) as kode_unit,
                                td.jml_ekor as jumlah
                            from
                            (
                                select od1.* from order_doc od1
                                right join
                                    (select max(id) as id, noreg from order_doc group by noreg) od2
                                    on
                                        od1.id = od2.id
                            ) od
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
                            where
                                td.id is not null
                        ) data
                        group by
                            data.start_date,
                            data.end_date,
                            data.kode_perusahaan,
                            data.kode_unit
                    ) data
                    group by
                        data.prev_start_date,
                        data.prev_end_date,
                        data.start_date,
                        data.end_date,
                        data.kode_perusahaan,
                        data.kode_unit
            ) data
            left join
                (
                    select
                        data.start_date,
                        data.end_date,
                        data.kode_perusahaan,
                        data.kode_unit,
                        isnull(sum(data.jumlah_est), 0) as jumlah_est,
                        isnull(sum(data.jumlah_real), 0) as jumlah_real
                    from
                        (
                            select
                                start_date,
                                end_date,
                                kode_perusahaan,
                                kode_unit,
                                jumlah as jumlah_est,
                                0 as jumlah_real
                            from 
                                estimasi_chick_in_mingguan est
                        
                            union all
                        
                            select
                                data.start_date,
                                data.end_date,
                                data.kode_perusahaan,
                                data.kode_unit,
                                0 as jumlah_est,
                                sum(data.jumlah) as jumlah
                            from (
                                select  
                                    case
                                        when DATEPART(dw, td.datang) = 1 then
                                            convert(varchar(10), DATEADD(dd, -6, td.datang), 120)
                                        else
                                            convert(varchar(10), DATEADD(dd, -(DATEPART(dw, td.datang)-2), td.datang), 120)
                                    end as start_date,
                                    case
                                        when DATEPART(dw, td.datang) = 1 then
                                            convert(varchar(10), DATEADD(dd, 6, DATEADD(dd, -6, td.datang)), 120)
                                        else
                                            convert(varchar(10), DATEADD(dd, 6, DATEADD(dd, -(DATEPART(dw, td.datang)-2), td.datang)), 120)
                                    end as end_date,
                                    od.perusahaan as kode_perusahaan,
                                    SUBSTRING(od.no_order, 5, 3) as kode_unit,
                                    td.jml_ekor as jumlah
                                from
                                (
                                    select od1.* from order_doc od1
                                    right join
                                        (select max(id) as id, noreg from order_doc group by noreg) od2
                                        on
                                            od1.id = od2.id
                                ) od
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
                                where
                                    td.id is not null
                            ) data
                            group by
                                data.start_date,
                                data.end_date,
                                data.kode_perusahaan,
                                data.kode_unit
                        ) data
                        group by
                            data.start_date,
                            data.end_date,
                            data.kode_perusahaan,
                            data.kode_unit
                ) prev
                on
                    prev.start_date = data.prev_start_date and
                    prev.end_date = data.prev_end_date and
                    prev.kode_perusahaan = data.kode_perusahaan and
                    prev.kode_unit = data.kode_unit
            left join
                (
                    select
                        prs1.kode,
                        prs1.perusahaan as nama
                    from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    prs.kode = data.kode_perusahaan
            left join
                (
                    select
                        w1.kode,
                        REPLACE(REPLACE(w1.nama, 'Kab ', ''), 'Kota ', '') as nama
                    from wilayah w1
                    right join
                        (select max(id) as id, kode from wilayah group by kode) w2
                        on
                            w1.id = w2.id
                    where
                        w1.jenis = 'UN'
                ) w
                on
                    w.kode = data.kode_unit
            where
                data.start_date >= '".$start_date."' and data.end_date <= '".$end_date."'
                ".$sql_perusahaan."
                ".$sql_unit."
            group by
                prev.start_date,
                prev.end_date,
                data.start_date,
                data.end_date,
                data.kode_perusahaan,
                prs.nama,
                data.kode_unit,
                w.nama
            order by
                data.start_date desc,
                data.kode_perusahaan asc,
                data.kode_unit asc
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $content['data'] = $data;

        $html = $this->load->view($this->pathView.'list', $content);

        echo $html;
    }
}