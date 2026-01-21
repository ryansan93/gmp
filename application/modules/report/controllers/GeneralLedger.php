<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class GeneralLedger extends Public_Controller {

    private $pathView = 'report/general_ledger/';
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
                "assets/report/general_ledger/js/general-ledger.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/general_ledger/css/general-ledger.css",
            ));

            $data = $this->includes;

            $m_wilayah = new \Model\Storage\Wilayah_model();

            $content['akses'] = $akses;
            $content['perusahaan'] = $this->getPerusahaan();
            $content['unit'] = $m_wilayah->getDataUnit();
            $content['title_menu'] = 'Laporan GL (Buku Besar)';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getPerusahaan()
    {
        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $kode_perusahaan = $m_perusahaan->select('kode')->distinct('kode')->get();

        $data = null;
        if ( $kode_perusahaan->count() > 0 ) {
            $kode_perusahaan = $kode_perusahaan->toArray();

            foreach ($kode_perusahaan as $k => $val) {
                $m_perusahaan = new \Model\Storage\Perusahaan_model();
                $d_perusahaan = $m_perusahaan->where('kode', $val['kode'])->orderBy('version', 'desc')->first();

                $key = $d_perusahaan['kode_gabung_perusahaan'];
                $key_detail = strtoupper($d_perusahaan->perusahaan).' | '.$d_perusahaan->kode;

                $data[ $key ]['kode_gabung_perusahaan'] = $d_perusahaan['kode_gabung_perusahaan'];
                $data[ $key ]['detail'][ $key_detail ] = array(
                    'nama' => strtoupper($d_perusahaan->perusahaan),
                    'kode' => $d_perusahaan->kode,
                    'jenis_mitra' => $d_perusahaan->jenis_mitra
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function getData($start_date, $end_date, $kode_gabung_perusahaan, $unit) {
        $sql_kode_gabung_perusahaan = "and dj.perusahaan in (select kode from perusahaan where kode_gabung_perusahaan = '".$kode_gabung_perusahaan."')";
        if ( $kode_gabung_perusahaan == 'all' ) {
            $sql_kode_gabung_perusahaan = null;
        }

        $sql_unit = null;
        if ( $unit != 'all' ) {
            $sql_unit = "where data.unit = '".$unit."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql_sa = "
            /* SALDO AWAL */
            select
                sb.no_coa as no_coa,
                sb.unit,
                c.nama_coa,
                case
                    when sb.debet2 <> 0 then
                        -- sb.debet2
                        0
                    else
                        sb.debet1
                end as saldo_awal,
                -- sb.saldo_awal,
                0 as kredit,
                0 as debet
            from (
                select
                    sa.no_coa,
                    sa.unit,
                    sum(sa.debet1) as debet1,
                    sum(sa.kredit1) as kredit1,
                    sum(sa.debet2) as debet2,
                    sum(sa.kredit2) as kredit2
                from
                (
                    select
                        sb.coa as no_coa,
                        sb.unit,
                        isnull(sb.saldo_awal, 0) as debet1,
                        0 as kredit1,
                        0 as debet2,
                        0 as kredit2
                    from saldo_bulanan sb 
                    where 
                        sb.tanggal between '".$start_date."' and '".$end_date."' and
                        isnull(sb.saldo_awal, 0) <> 0

                    union all

                    select
                        sc.no_coa,
                        sc.unit,
                        0 as debet1,
                        0 as kredit1,
                        isnull(sc.debet, 0) as debet2,
                        0 as kredit2
                    from sacoa sc
                    where
                        sc.periode = '".substr($start_date, 0, 7)."' and
                        sc.debet <> 0
                ) sa
                group by
                    sa.no_coa,
                    sa.unit
            ) sb 
            left join
                coa c
                on
                    sb.no_coa = c.coa
            /* END - SALDO AWAL */
        ";
        $d_conf = $m_conf->hydrateRaw( $sql_sa );
        if ( $d_conf->count() <= 0 ) {
            $end_date_new = prev_date($start_date);
            $start_date_new = substr($end_date_new, 0, 7).'-01';

            $sql_sa = "
                select
                    data.no_coa,
                    data.unit,
                    data.nama_coa,
                    sum(isnull(data.saldo_awal, 0)) + sum(isnull(data.debet, 0)) + sum(isnull(data.kredit, 0)) as saldo_awal,
                    0 as kredit,
                    0 as debet
                from
                (
                    select
                        sb.no_coa as no_coa,
                        sb.unit,
                        c.nama_coa,
                        case
                            when sb.debet2 <> 0 then
                                -- sb.debet2
                                0
                            else
                                sb.debet1
                        end as saldo_awal,
                        -- sb.saldo_awal,
                        0 as kredit,
                        0 as debet
                    from (
                        select
                            sa.no_coa,
                            sa.unit,
                            sum(sa.debet1) as debet1,
                            sum(sa.kredit1) as kredit1,
                            sum(sa.debet2) as debet2,
                            sum(sa.kredit2) as kredit2
                        from
                        (
                            select
                                sb.coa as no_coa,
                                sb.unit,
                                isnull(sb.saldo_awal, 0) as debet1,
                                0 as kredit1,
                                0 as debet2,
                                0 as kredit2
                            from saldo_bulanan sb 
                            where 
                                sb.tanggal between '".$start_date_new."' and '".$end_date_new."' and
                                isnull(sb.saldo_awal, 0) <> 0

                            union all

                            select
                                sc.no_coa,
                                sc.unit,
                                0 as debet1,
                                0 as kredit1,
                                isnull(sc.debet, 0) as debet2,
                                0 as kredit2
                            from sacoa sc
                            where
                                sc.periode = '".substr($start_date_new, 0, 7)."' and
                                sc.debet <> 0
                        ) sa
                        group by
                            sa.no_coa,
                            sa.unit
                    ) sb 
                    left join
                        coa c
                        on
                            sb.no_coa = c.coa

                    union all

                    select
                        sc.no_coa,
                        sc.unit,
                        c.nama_coa,
                        0 as saldo_awal,
                        case
                            when isnull(sc.debet, 0) < 0 then
                                isnull(sc.debet, 0)
                            else
                                0
                        end as kredit,
                        case
                            when isnull(sc.debet, 0) >= 0 then
                                isnull(sc.debet, 0)
                            else
                                0
                        end as debet
                    from sacoa sc
                    left join
                        coa c
                        on
                            sc.no_coa = c.coa
                    where
                        sc.periode = '".substr($start_date_new, 0, 7)."' and
                        sc.debet <> 0

                    union all

                    select
                        c.coa as no_coa,
                        case
                            when c.unit is not null and c.unit <> '' then
                                c.unit
                            else
                                dj.unit
                        end as unit,
                        c.nama_coa,
                        0 as saldo_awal,
                        (0-isnull(dj.kredit, 0)) as kredit,
                        isnull(dj.debet, 0) as debet
                    from coa c
                    left join
                        (
                            select no_coa, sum(kredit) as kredit, sum(debet) as debet, unit from (
                                select 
                                    dj.coa_asal as no_coa, 
                                    sum(dj.nominal) as kredit, 
                                    0 as debet, 
                                    dj.unit
                                from det_jurnal dj 
                                where 
                                    dj.tanggal between '".$start_date_new."' and '".$end_date_new."'
                                    -- and dj.perusahaan in (select kode from perusahaan where kode_gabung_perusahaan = '1')
                                group by dj.coa_asal, dj.unit
                                
                                union all
                                
                                select 
                                    dj.coa_tujuan as no_coa, 
                                    0 as kredit, 
                                    sum(dj.nominal) as debet, 
                                    case
                                        when dj.unit_tujuan is not null then
                                            dj.unit_tujuan
                                        else
                                            dj.unit
                                    end as unit
                                from det_jurnal dj 
                                where 
                                    dj.tanggal between '".$start_date_new."' and '".$end_date_new."'
                                    -- and dj.perusahaan in (select kode from perusahaan where kode_gabung_perusahaan = '1')
                                group by dj.coa_tujuan, dj.unit, dj.unit_tujuan
                            ) data
                            group by
                                no_coa, unit
                        ) dj
                        on
                            dj.no_coa = c.coa
                    where
                        (0-isnull(dj.kredit, 0)) <> 0 or
                        isnull(dj.debet, 0) <> 0
                ) data
                ".$sql_unit."
                group by
                    data.no_coa,
                    data.unit,
                    data.nama_coa
            ";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.no_coa,
                data.unit,
                data.nama_coa,
                sum(isnull(data.saldo_awal, 0)) as saldo_awal,
                sum(isnull(data.kredit, 0)) as kredit,
                sum(isnull(data.debet, 0)) as debet,
                sum(isnull(data.saldo_awal, 0)) + sum(isnull(data.debet, 0)) + sum(isnull(data.kredit, 0)) as saldo_akhir
            from
            (
                /*
                select
                    sb.no_coa as no_coa,
                    sb.unit,
                    c.nama_coa,
                    case
                        when sb.debet2 <> 0 then
                            -- sb.debet2
                            0
                        else
                            sb.debet1
                    end as saldo_awal,
                    -- sb.saldo_awal,
                    0 as kredit,
                    0 as debet
                from (
                    select
                        sa.no_coa,
                        sa.unit,
                        sum(sa.debet1) as debet1,
                        sum(sa.kredit1) as kredit1,
                        sum(sa.debet2) as debet2,
                        sum(sa.kredit2) as kredit2
                    from
                    (
                        select
                            sb.coa as no_coa,
                            sb.unit,
                            isnull(sb.saldo_awal, 0) as debet1,
                            0 as kredit1,
                            0 as debet2,
                            0 as kredit2
                        from saldo_bulanan sb 
                        where 
                            sb.tanggal between '".$start_date."' and '".$end_date."'

                        union all

                        select
                            sc.no_coa,
                            sc.unit,
                            0 as debet1,
                            0 as kredit1,
                            isnull(sc.debet, 0) as debet2,
                            0 as kredit2
                        from sacoa sc
                        where
                            sc.periode = '".substr($start_date, 0, 7)."' and
                        	sc.debet <> 0
                    ) sa
                    group by
                        sa.no_coa,
                        sa.unit
                ) sb 
                left join
                    coa c
                    on
                        sb.no_coa = c.coa
                */

                ".$sql_sa."

                union all

                select
                    sc.no_coa,
                    sc.unit,
                    c.nama_coa,
                    0 as saldo_awal,
                    case
                        when isnull(sc.debet, 0) < 0 then
                            isnull(sc.debet, 0)
                        else
                            0
                    end as kredit,
                    case
                        when isnull(sc.debet, 0) >= 0 then
                            isnull(sc.debet, 0)
                        else
                            0
                    end as debet
                from sacoa sc
                left join
                    coa c
                    on
                        sc.no_coa = c.coa
                where
                    sc.periode = '".substr($start_date, 0, 7)."' and
                    sc.debet <> 0

                union all

                select
                    c.coa as no_coa,
                    case
                        when c.unit is not null and c.unit <> '' then
                            c.unit
                        else
                            dj.unit
                    end as unit,
                    c.nama_coa,
                    0 as saldo_awal,
                    (0-isnull(dj.kredit, 0)) as kredit,
                    isnull(dj.debet, 0) as debet
                from coa c
                left join
                    (
                        select no_coa, sum(kredit) as kredit, sum(debet) as debet, unit from (
                            select 
                                dj.coa_asal as no_coa, 
                                sum(dj.nominal) as kredit, 
                                0 as debet, 
                                dj.unit
                            from det_jurnal dj 
                            where 
                                dj.tanggal between '".$start_date."' and '".$end_date."'
                                -- and dj.perusahaan in (select kode from perusahaan where kode_gabung_perusahaan = '1')
                            group by dj.coa_asal, dj.unit
                            
                            union all
                            
                            select 
                                dj.coa_tujuan as no_coa, 
                                0 as kredit, 
                                sum(dj.nominal) as debet, 
                                case
                                    when dj.unit_tujuan is not null then
                                        dj.unit_tujuan
                                    else
                                        dj.unit
                                end as unit
                            from det_jurnal dj 
                            where 
                                dj.tanggal between '".$start_date."' and '".$end_date."'
                                -- and dj.perusahaan in (select kode from perusahaan where kode_gabung_perusahaan = '1')
                            group by dj.coa_tujuan, dj.unit, dj.unit_tujuan
                        ) data
                        group by
                            no_coa, unit
                    ) dj
                    on
                        dj.no_coa = c.coa
                where
                    (0-isnull(dj.kredit, 0)) <> 0 or
                    isnull(dj.debet, 0) <> 0
            ) data
            ".$sql_unit."
            group by
                data.no_coa,
                data.unit,
                data.nama_coa
            order by
                data.no_coa asc,
                data.unit asc
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }
        
        return $data;
    }

    public function getDetail($periode, $unit, $no_coa) {
        $start_date = $periode;
        $end_date = date("Y-m-t", strtotime($start_date));
        
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.tanggal,
                data.keterangan,
                data.kode_trans,
                data.no_coa,
                data.unit,
                data.nama_coa,
                isnull(data.kredit, 0) as kredit,
                isnull(data.debet, 0) as debet
            from
            (
                select
                    '' as tanggal,
                    'Saldo Awal' as keterangan,
                    '' as kode_trans,
                    sb.no_coa as no_coa,
                    sb.unit,
                    c.nama_coa,
                    case
                        when sb.kredit2 <> 0 then
                            -- sb.kredit2
                            0
                        else
                            case
                                when sb.debet1 + sb.kredit1 < 0 then
                                    sb.debet1 + sb.kredit1
                                else
                                    0
                            end
                    end as kredit,
                    case
                        when sb.debet2 <> 0 then
                            -- sb.debet2
                            0
                        else
                            case
                                when sb.debet1 + sb.kredit1 >= 0 then
                                    sb.debet1 + sb.kredit1
                                else
                                    0
                            end
                    end as debet,
                    0 as urut
                from (
                    select
                        sa.no_coa,
                        sa.unit,
                        sum(sa.debet1) as debet1,
                        sum(sa.kredit1) as kredit1,
                        sum(sa.debet2) as debet2,
                        sum(sa.kredit2) as kredit2
                    from
                    (
                        select
                            sb.coa as no_coa,
                            sb.unit,
                            case
                                when isnull(sb.saldo_awal, 0) >= 0 then
                                    isnull(sb.saldo_awal, 0)
                                else
                                    0
                            end as debet1,
                            case
                                when isnull(sb.saldo_awal, 0) < 0 then
                                    isnull(sb.saldo_awal, 0)
                                else
                                    0
                            end as kredit1,
                            0 as debet2,
                            0 as kredit2
                        from saldo_bulanan sb 
                        where 
                            sb.tanggal between '".$start_date."' and '".$end_date."'

                        union all

                        select
                            sc.no_coa,
                            sc.unit,
                            0 as debet1,
                            0 as kredit1,
                            case
                                when isnull(sc.debet, 0) >= 0 then
                                    isnull(sc.debet, 0)
                                else
                                    0
                            end as debet2,
                            case
                                when isnull(sc.debet, 0) < 0 then
                                    isnull(sc.debet, 0)
                                else
                                    0
                            end as kredit2
                        from sacoa sc
                        where
                            sc.periode = '".substr($start_date, 0, 7)."' and
                        	sc.debet <> 0
                    ) sa
                    group by
                        sa.no_coa,
                        sa.unit
                ) sb 
                left join
                    coa c
                    on
                        sb.no_coa = c.coa

                union all

                select
                    sc.periode+'-01' as tanggal,
                    'Initial Balance' as keterangan,
                    'INIT'+REPLACE(sc.periode, '-', '') as kode_trans,
                    sc.no_coa,
                    sc.unit,
                    c.nama_coa,
                    case
                        when isnull(sc.debet, 0) < 0 then
                            isnull(sc.debet, 0)
                        else
                            0
                    end as kredit,
                    case
                        when isnull(sc.debet, 0) >= 0 then
                            isnull(sc.debet, 0)
                        else
                            0
                    end as debet,
                    1 as urut
                from sacoa sc
                left join
                    coa c
                    on
                        sc.no_coa = c.coa
                where
                    sc.periode = '".substr($start_date, 0, 7)."' and
                    sc.debet <> 0

                union all

                select
                    dj.tanggal, 
                    dj.keterangan,
                    dj.kode_trans,
                    c.coa as no_coa,
                    case
                        when c.unit is not null and c.unit <> '' then
                            c.unit
                        else
                            dj.unit
                    end as unit,
                    c.nama_coa,
                    (0-isnull(dj.kredit, 0)) as kredit,
                    isnull(dj.debet, 0) as debet,
                    2 as urut
                from coa c
                left join
                    (
                        select 
                            tanggal,
                            cast(keterangan as varchar(max)) as keterangan,
                            kode_trans,
                            no_coa,
                            kredit as kredit,
                            debet as debet,
                            unit
                        from (
                            select 
                                dj.tanggal,
                                cast(dj.keterangan as varchar(max)) as keterangan,
                                dj.kode_trans,
                                dj.coa_asal as no_coa, 
                                sum(dj.nominal) as kredit, 
                                0 as debet, 
                                dj.unit
                            from det_jurnal dj 
                            where 
                                dj.tanggal between '".$start_date."' and '".$end_date."'
                            group by
                                dj.tanggal,
                                cast(dj.keterangan as varchar(max)),
                                dj.kode_trans,
                                dj.coa_asal,
                                dj.unit
                            
                            union all
                            
                            select 
                                dj.tanggal,
                                cast(dj.keterangan as varchar(max)) as keterangan,
                                dj.kode_trans,
                                dj.coa_tujuan as no_coa, 
                                0 as kredit, 
                                sum(dj.nominal) as debet, 
                                case
                                    when dj.unit_tujuan is not null then
                                        dj.unit_tujuan
                                    else
                                        dj.unit
                                end as unit
                            from det_jurnal dj 
                            where 
                                dj.tanggal between '".$start_date."' and '".$end_date."'
                            group by
                                dj.tanggal,
                                cast(dj.keterangan as varchar(max)),
                                dj.kode_trans,
                                dj.coa_tujuan,
                                dj.unit,
                                dj.unit_tujuan
                        ) data
                        group by
                            tanggal, cast(keterangan as varchar(max)), kode_trans, no_coa, unit, kredit, debet
                    ) dj
                    on
                        dj.no_coa = c.coa
                where
                    (0-isnull(dj.kredit, 0)) <> 0 or
                    isnull(dj.debet, 0) <> 0
            ) data
            where
                data.no_coa = '".$no_coa."' and
                data.unit = '".$unit."'
            order by
                data.tanggal asc,
                data.urut asc,
                data.kode_trans asc
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }
        
        return $data;
    }

    public function getLists() {
        $params = $this->input->get('params');

        $start_date = null;
        $end_date = null;

        $bulan = $params['bulan'];
        $tahun = substr($params['tahun'], 0, 4);
        $kode_gabung_perusahaan = $params['perusahaan'];
        $unit = $params['unit'];

        $i = $bulan-1;

        $angka_bulan = (strlen($i+1) == 1) ? '0'.($i+1) : $i+1;

        $date = $tahun.'-'.$angka_bulan.'-01';
        $start_date = date("Y-m-d", strtotime($date));
        $end_date = date("Y-m-t", strtotime($date));

        $data = $this->getData( $start_date, $end_date, $kode_gabung_perusahaan, $unit );

        $content['data'] = $data;
        $content['periode'] = $start_date;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }

    public function formDetail()
    {
        $params = $this->input->get('params');

        $detail = $this->getDetail( $params['periode'], $params['unit'], $params['no_coa'] );

        $content['data'] = $params;
        $content['detail'] = $detail;
        $html = $this->load->view($this->pathView.'detail', $content, TRUE);

        echo $html;
    }

    public function encryptParams()
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

    public function exportExcelUsingSpreadSheet( $file_name, $arr_header, $arr_column ) {
        /* Spreadsheet Init */
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        /* Excel Header */
        for ($i=0; $i < count($arr_header); $i++) { 
            $huruf = toAlpha($i+1);

            $posisi = $huruf.'1';
            $sheet->setCellValue($posisi, $arr_header[$i]);

            $styleBold = [
                'font' => [
                    'bold' => true,
                ]
            ];
            $spreadsheet->getActiveSheet()->getStyle($posisi)->applyFromArray($styleBold);
        }

        $baris = 2;
        if ( !empty($arr_column) && count($arr_column) ) {
            for ($i=0; $i < count($arr_column); $i++) {
                for ($j=0; $j < count($arr_header); $j++) {
                    $huruf = toAlpha($j+1);

                    $data = $arr_column[ $i ][ $arr_header[ $j ] ];

                    if ( !empty($data['value']) ) {
                        if ( isset($data['rowspan']) && $data['rowspan'] > 1 ) {
                            $spreadsheet->getActiveSheet()->mergeCells($huruf.$baris.':'.$huruf.(($baris+$data['rowspan'])-1));
                        }

                        if ( $data['data_type'] == 'string' ) {
                            $sheet->setCellValue($huruf.$baris, strtoupper($data['value']));
                        }

                        if ( $data['data_type'] == 'nik' ) {
                            $sheet->getCell($huruf.$baris)->setValueExplicit($data['value'], DataType::TYPE_STRING);
                            // $sheet->setCellValue($huruf.$baris, strtoupper($data['value']));
                            // $spreadsheet->getActiveSheet()->getStyle('A9')
                            //             ->getNumberFormat()
                            //             ->setFormatCode(
                            //                 '00000000000'
                            //             );
                        }

                        if ( $data['data_type'] == 'text' ) {
                            $sheet->setCellValue($huruf.$baris, strtoupper($data['value']));
                            $spreadsheet->getActiveSheet()->getStyle($huruf.$baris)
                                        ->getNumberFormat()
                                        ->setFormatCode(NumberFormat::FORMAT_GENERAL);
                        }

                        if ( $data['data_type'] == 'date' ) {
                            $dt = Date::PHPToExcel(DateTime::createFromFormat('!Y-m-d', substr($data['value'], 0, 10)));
                            $sheet->setCellValue($huruf.$baris, $dt);
                            $spreadsheet->getActiveSheet()->getStyle($huruf.$baris)
                                        ->getNumberFormat()
                                        ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
                        }

                        if ( $data['data_type'] == 'datetime' ) {
                            $dt = Date::PHPToExcel(new DateTimeImmutable($data['value']));
                            $sheet->setCellValue($huruf.$baris, $dt);
                            $spreadsheet->getActiveSheet()->getStyle($huruf.$baris)
                                        ->getNumberFormat()
                                        ->setFormatCode(NumberFormat::FORMAT_DATE_XLSX14);
                        }

                        if ( $data['data_type'] == 'integer' ) {
                            $sheet->setCellValue($huruf.$baris, $data['value']);
                            $spreadsheet->getActiveSheet()->getStyle($huruf.$baris)
                                        ->getNumberFormat()
                                        ->setFormatCode(NumberFormat::FORMAT_NUMBER);
                        }

                        if ( $data['data_type'] == 'decimal2' ) {
                            $sheet->setCellValue($huruf.$baris, $data['value']);
                            $spreadsheet->getActiveSheet()->getStyle($huruf.$baris)
                                        ->getNumberFormat()
                                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
                        }
                    }
                }

                $baris++;
            }
        } else {
            $range1 = 'A'.$baris;
            $range2 = toAlpha(count($arr_header)).$baris;

            $spreadsheet->getActiveSheet()->mergeCells("$range1:$range2");
            $sheet->setCellValue($range1, 'Data tidak ditemukan.');
        }

        $styleArray = [
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
                'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
                'left' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
            ],
        ];
        
        $spreadsheet->getActiveSheet()->getStyle('A1:'.toAlpha(count($arr_header)).$baris)->applyFromArray($styleArray, false);

        /* Excel File Format */
        $writer = new Xlsx($spreadsheet);
        $filename = $file_name;
        $writer->save('export_excel/'.$filename);

        // cetak_r( FCPATH.'/export_excel/', 1 );

        $this->load->helper('download');
        $data = file_get_contents(FCPATH.'/export_excel/'.$filename);

        // cetak_r( $filename, 1);
        // cetak_r( $data );

        force_download($filename, $data);
    }

    public function exportExcel($params_encrypt)
    {
        $params = json_decode( exDecrypt($params_encrypt), true );

        $start_date = null;
        $end_date = null;

        $bulan = $params['bulan'];
        $tahun = substr($params['tahun'], 0, 4);
        $kode_gabung_perusahaan = $params['perusahaan'];
        $unit = $params['unit'];

        $i = $bulan-1;

        $angka_bulan = (strlen($i+1) == 1) ? '0'.($i+1) : $i+1;

        $date = $tahun.'-'.$angka_bulan.'-01';
        $start_date = date("Y-m-d", strtotime($date));
        $end_date = date("Y-m-t", strtotime($date));

        $data = $this->getData( $start_date, $end_date, $kode_gabung_perusahaan, $unit );
            
        $filename = 'GL_PERIODE_'.$tahun.$bulan.'_'.strtoupper($unit);

        $arr_header = array('No. COA', 'Unit', 'Nama COA', 'Saldo Awal', 'Debet', 'Kredit', 'Saldo Akhir');
        $arr_column = null;
        if ( !empty($data) ) {
            $idx = 0;

            $tot_saldo_awal = 0;
            $tot_debet = 0;
            $tot_kredit = 0;
            $tot_saldo_akhir = 0;

            foreach ($data as $key => $value) {
                $arr_column[ $idx ] = array(
                    'No. COA' => array('value' => strtoupper($value['no_coa']), 'data_type' => 'nik'),
                    'Unit' => array('value' => strtoupper($value['unit']), 'data_type' => 'string'),
                    'Nama COA' => array('value' => strtoupper($value['nama_coa']), 'data_type' => 'string'),
                    'Saldo Awal' => array('value' => $value['saldo_awal'], 'data_type' => 'decimal2'),
                    'Debet' => array('value' => $value['debet'], 'data_type' => 'decimal2'),
                    'Kredit' => array('value' => $value['kredit'], 'data_type' => 'decimal2'),
                    'Saldo Akhir' => array('value' => $value['saldo_akhir'], 'data_type' => 'decimal2'),
                );

                $tot_saldo_awal += $value['saldo_awal'];
                $tot_debet += $value['debet'];
                $tot_kredit += $value['kredit'];
                $tot_saldo_akhir += $value['saldo_akhir'];

                $idx++;
            }

            $arr_column[] = array(
                'Nama COA' => array('value' => 'Total', 'data_type' => 'string', 'colspan' => array('A','C'), 'align' => 'right', 'text_style' => 'bold'),
                'Saldo Awal' => array('value' => $tot_saldo_awal, 'data_type' => 'decimal2', 'text_style' => 'bold'),
                'Debet' => array('value' => $tot_debet, 'data_type' => 'decimal2', 'text_style' => 'bold'),
                'Kredit' => array('value' => $tot_kredit, 'data_type' => 'decimal2', 'text_style' => 'bold'),
                'Saldo Akhir' => array('value' => $tot_saldo_akhir, 'data_type' => 'decimal2', 'text_style' => 'bold'),
            );
        }

        // $this->exportExcelUsingSpreadSheet( $filename, $arr_header, $arr_column );

        Modules::run( 'base/ExportExcel/exportExcelUsingSpreadSheet', $filename, $arr_header, $arr_column );

        $this->load->helper('download');
        force_download('export_excel/'.$filename.'.xlsx', NULL);
    }
}