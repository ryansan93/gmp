<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class UmurKartuPiutang extends Public_Controller {

    private $pathView = 'report/umur_kartu_piutang/';
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
                "assets/report/umur_kartu_piutang/js/umur-kartu-piutang.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/umur_kartu_piutang/css/umur-kartu-piutang.css",
            ));

            $data = $this->includes;

            $m_tp = new \Model\Storage\TipePelanggan_model();
            $d_tp = $m_tp->getData();

            $content['akses'] = $akses;
            $content['title_menu'] = 'Laporan Umur Kartu Piutang';
            $content['tipe_pelanggan'] = $d_tp;

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getData( $params ) {
        $bulan = $params['bulan'];
        $tahun = substr($params['tahun'], 0, 4);
        $tipe_pelanggan = $params['tipe_pelanggan'];

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

        $today = date("Y-m-d");
        if ( $end_date > $today ) {
            $end_date = $today;
        }

        $sql_tipe_plg = "";
        if ( !empty($tipe_pelanggan) && stristr($tipe_pelanggan, 'all') === FALSE ) {
            $sql_tipe_plg = "where tp.id = ".$tipe_pelanggan."";
        }

        $m_conf = new \Model\Storage\Conf();
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
                    (isnull(sum(d.saldo_awal), 0)+isnull(sum(d.debet), 0))-isnull(sum(d.kredit), 0) as saldo_akhir,
                    isnull(sum(d._current), 0) as _current,
                    isnull(sum(d.umur1), 0) as umur1,
                    isnull(sum(d.umur2), 0) as umur2,
                    isnull(sum(d.umur3), 0) as umur3,
                    isnull(sum(d.umur4), 0) as umur4,
                    isnull(sum(d.umur5), 0) as umur5,
                    isnull(sum(d.umur6), 0) as umur6,
                    isnull(sum(d.umur7), 0) as umur7
                from
                (
                    /* SALDO AWAL */
                    select 
                        inv.pelanggan, 
                        sum(isnull(inv.debet, 0 ) - isnull(inv.kredit, 0)) as saldo_awal,
                        0 as debet,
                        0 as kredit,
                        0 as _current,
                        0 as umur1,
                        0 as umur2,
                        0 as umur3,
                        0 as umur4,
                        0 as umur5,
                        0 as umur6,
                        0 as umur7
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
                        inv.pelanggan, 
                        0 as saldo_awal,
                        isnull(sum(inv.total), 0) as debet,
                        0 as kredit,
                        0 as _current,
                        0 as umur1,
                        0 as umur2,
                        0 as umur3,
                        0 as umur4,
                        0 as umur5,
                        0 as umur6,
                        0 as umur7
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
                        inv.pelanggan,
                        inv.tanggal

                    union all
    
                    select
                        byr.pelanggan,
                        0 as saldo_awal,
                        0 as debet,
                        isnull(sum(byr.bayar), 0) as kredit,
                        0 as _current,
                        0 as umur1,
                        0 as umur2,
                        0 as umur3,
                        0 as umur4,
                        0 as umur5,
                        0 as umur6,
                        0 as umur7
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
                        byr.pelanggan,
                        byr.tanggal
                    /* END - TRANSAKSI DI BULAN ITU */

                    union all

                    /* TRANSAKSI DI BULAN ITU PER UMUR */
                    select 
                        inv.pelanggan, 
                        0 as saldo_awal,
                        0 as debet,
                        0 as kredit,
                        case
                            when datediff(day, inv.tanggal, '".$end_date."') <= 0 then
                                sum(isnull(inv.debet, 0 ) - isnull(inv.kredit, 0))
                            else
                                0
                        end as _current,
                        case
                            when datediff(day, inv.tanggal, '".$end_date."') >= 1 and datediff(day, inv.tanggal, '".$end_date."') <= 7 then
                                sum(isnull(inv.debet, 0 ) - isnull(inv.kredit, 0))
                            else
                                0
                        end as umur1,
                        case
                            when datediff(day, inv.tanggal, '".$end_date."') >= 8 and datediff(day, inv.tanggal, '".$end_date."') <= 14 then
                                sum(isnull(inv.debet, 0 ) - isnull(inv.kredit, 0))
                            else
                                0
                        end as umur2,
                        case
                            when datediff(day, inv.tanggal, '".$end_date."') >= 15 and datediff(day, inv.tanggal, '".$end_date."') <= 21 then
                                sum(isnull(inv.debet, 0 ) - isnull(inv.kredit, 0))
                            else
                                0
                        end as umur3,
                        case
                            when datediff(day, inv.tanggal, '".$end_date."') >= 22 and datediff(day, inv.tanggal, '".$end_date."') <= 30 then
                                sum(isnull(inv.debet, 0 ) - isnull(inv.kredit, 0))
                            else
                                0
                        end as umur4,
                        case
                            when datediff(day, inv.tanggal, '".$end_date."') >= 31 and datediff(day, inv.tanggal, '".$end_date."') <= 60 then
                                sum(isnull(inv.debet, 0 ) - isnull(inv.kredit, 0))
                            else
                                0
                        end as umur5,
                        case
                            when datediff(day, inv.tanggal, '".$end_date."') >= 61 and datediff(day, inv.tanggal, '".$end_date."') <= 90 then
                                sum(isnull(inv.debet, 0 ) - isnull(inv.kredit, 0))
                            else
                                0
                        end as umur6,
                        case
                            when datediff(day, inv.tanggal, '".$end_date."') >= 91 then
                                sum(isnull(inv.debet, 0 ) - isnull(inv.kredit, 0))
                            else
                                0
                        end as umur7
                    from (
                        select
                            rs.tgl_panen as tanggal,
                            drs.no_pelanggan as pelanggan,
                            drsi.no_inv as nomor,
                            drsi.total as debet,
                            0 as kredit
                            , drsi.no_inv as kode_trans
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
                            rs.tgl_panen <= '".$end_date."'

                        union all

                        select
                            rs.tgl_panen as tanggal,
                            -- pp.tgl_bayar as tanggal,
                            pp.no_pelanggan as pelanggan,
                            dpp.no_inv as nomor,
                            0 as debet,
                            isnull(dpp.tagihan-dpp.sisa_tagihan, 0) as kredit
                            , pp.nomor as kode_trans
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
                            pp.tgl_bayar <= '".$end_date."'
                            and isnull(dpp.tagihan-dpp.sisa_tagihan, 0) <> 0
                    ) inv
                    group by
                        inv.pelanggan,
                        inv.tanggal
                    /* END - TRANSAKSI DI BULAN ITU PER UMUR */
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
            ".$sql_tipe_plg."
            order by
                data.pelanggan asc
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

        $text_tgl = str_replace(' ', '_', substr(tglIndonesia( $end_date, '-', ' ', true ), 3, strlen(tglIndonesia( $end_date, '-', ' ', true ))));

        $filename = strtoupper("LAPORAN_UMUR_PIUTANG_PER_".$text_tgl).".xls";

        $gt_saldo_awal = 0;
        $gt_debet = 0;
        $gt_kredit = 0;
        $gt_saldo_akhir = 0;
        $gt_current = 0;
        $gt_umur1 = 0;
        $gt_umur2 = 0;
        $gt_umur3 = 0;
        $gt_umur4 = 0;
        $gt_umur5 = 0;
        $gt_umur6 = 0;
        $gt_umur7 = 0;

        for ($i=1; $i <= 16; $i++) {
            $arr_header[] = toAlpha($i);
        }

        $arr_column = null;

        $idx = 0;
        $arr_column[ $idx ] = array(
            'A' => array('value' => 'LAPORAN UMUR PIUTANG', 'data_type' => 'string', 'colspan' => array('A','F'), 'align' => 'left', 'text_style' => 'bold', 'border' => 'none'),
        );
        $idx++;
        $arr_column[ $idx ] = array(
            'A' => array('value' => 'PER '.strtoupper(substr(tglIndonesia( $end_date, '-', ' ', true ), 3, strlen(tglIndonesia( $end_date, '-', ' ', true )))), 'data_type' => 'string', 'colspan' => array('A','F'), 'align' => 'left', 'text_style' => 'bold', 'border' => 'none'),
        );
        $idx++;
        $arr_column[ $idx ] = array(
            'A' => array('value' => strtoupper('ID Customer'), 'data_type' => 'string', 'align' => 'left', 'text_style' => 'bold', 'border' => 'border'),
            'B' => array('value' => strtoupper('Nama Customer'), 'data_type' => 'string', 'align' => 'left', 'text_style' => 'bold', 'border' => 'border'),
            'C' => array('value' => strtoupper('Plafon (Juta)'), 'data_type' => 'string', 'align' => 'left', 'text_style' => 'bold', 'border' => 'border'),
            'D' => array('value' => strtoupper('JaTem (Hari)'), 'data_type' => 'string', 'align' => 'left', 'text_style' => 'bold', 'border' => 'border'),
            'E' => array('value' => strtoupper('Saldo Awal'), 'data_type' => 'string', 'align' => 'left', 'text_style' => 'bold', 'border' => 'border'),
            'F' => array('value' => strtoupper('Debet'), 'data_type' => 'string', 'align' => 'left', 'text_style' => 'bold', 'border' => 'border'),
            'G' => array('value' => strtoupper('Kredit'), 'data_type' => 'string', 'align' => 'left', 'text_style' => 'bold', 'border' => 'border'),
            'H' => array('value' => strtoupper('Saldo Akhir'), 'data_type' => 'string', 'align' => 'left', 'text_style' => 'bold', 'border' => 'border'),
            'I' => array('value' => strtoupper('Current'), 'data_type' => 'string', 'align' => 'left', 'text_style' => 'bold', 'border' => 'border'),
            'J' => array('value' => strtoupper('Umur 1-7 Hari'), 'data_type' => 'string', 'align' => 'left', 'text_style' => 'bold', 'border' => 'border'),
            'K' => array('value' => strtoupper('Umur 8-14 Hari'), 'data_type' => 'string', 'align' => 'left', 'text_style' => 'bold', 'border' => 'border'),
            'L' => array('value' => strtoupper('Umur 15-21 Hari'), 'data_type' => 'string', 'align' => 'left', 'text_style' => 'bold', 'border' => 'border'),
            'M' => array('value' => strtoupper('Umur 22-30 Hari'), 'data_type' => 'string', 'align' => 'left', 'text_style' => 'bold', 'border' => 'border'),
            'N' => array('value' => strtoupper('Umur 31-60 Hari'), 'data_type' => 'string', 'align' => 'left', 'text_style' => 'bold', 'border' => 'border'),
            'O' => array('value' => strtoupper('Umur 61-90 Hari'), 'data_type' => 'string', 'align' => 'left', 'text_style' => 'bold', 'border' => 'border'),
            'P' => array('value' => strtoupper('Umur > 90 Hari'), 'data_type' => 'string', 'align' => 'left', 'text_style' => 'bold', 'border' => 'border'),
        );

        foreach ($data as $key => $value) {
            $idx++;
            $arr_column[ $idx ] = array(
                'A' => array('value' => strtoupper($value['pelanggan']), 'data_type' => 'string', 'align' => 'left', 'border' => 'border'),
                'B' => array('value' => strtoupper($value['nama_pelanggan']), 'data_type' => 'string', 'align' => 'left', 'border' => 'border'),
                'C' => array('value' => null, 'data_type' => 'string', 'align' => 'left', 'border' => 'border'),
                'D' => array('value' => null, 'data_type' => 'string', 'align' => 'left', 'border' => 'border'),
                'E' => array('value' => $value['saldo_awal'], 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border'),
                'F' => array('value' => $value['debet'], 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border'),
                'G' => array('value' => $value['kredit'], 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border'),
                'H' => array('value' => $value['saldo_akhir'], 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border'),
                'I' => array('value' => $value['_current'], 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border'),
                'J' => array('value' => $value['umur1'], 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border'),
                'K' => array('value' => $value['umur2'], 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border'),
                'L' => array('value' => $value['umur3'], 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border'),
                'M' => array('value' => $value['umur4'], 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border'),
                'N' => array('value' => $value['umur5'], 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border'),
                'O' => array('value' => $value['umur6'], 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border'),
                'P' => array('value' => $value['umur7'], 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border'),
            );

            $gt_saldo_awal += $value['saldo_awal'];
            $gt_debet += $value['debet'];
            $gt_kredit += $value['kredit'];
            $gt_saldo_akhir += $value['saldo_akhir'];
            $gt_current += $value['_current'];
            $gt_umur1 += $value['umur1'];
            $gt_umur2 += $value['umur2'];
            $gt_umur3 += $value['umur3'];
            $gt_umur4 += $value['umur4'];
            $gt_umur5 += $value['umur5'];
            $gt_umur6 += $value['umur6'];
            $gt_umur7 += $value['umur7'];
        }

        $idx++;
        $arr_column[ $idx ] = array(
            'D' => array('value' => strtoupper('total keseluruhan'), 'colspan' => array('A', 'D'), 'data_type' => 'string', 'align' => 'left', 'text_style' => 'bold', 'border' => 'border'),
            'E' => array('value' => $gt_saldo_awal, 'data_type' => 'decimal2', 'align' => 'right', 'text_style' => 'bold', 'border' => 'border'),
            'F' => array('value' => $gt_debet, 'data_type' => 'decimal2', 'align' => 'right', 'text_style' => 'bold', 'border' => 'border'),
            'G' => array('value' => $gt_kredit, 'data_type' => 'decimal2', 'align' => 'right', 'text_style' => 'bold', 'border' => 'border'),
            'H' => array('value' => $gt_saldo_akhir, 'data_type' => 'decimal2', 'align' => 'right', 'text_style' => 'bold', 'border' => 'border'),
            'I' => array('value' => $gt_current, 'data_type' => 'decimal2', 'align' => 'right', 'text_style' => 'bold', 'border' => 'border'),
            'J' => array('value' => $gt_umur1, 'data_type' => 'decimal2', 'align' => 'right', 'text_style' => 'bold', 'border' => 'border'),
            'K' => array('value' => $gt_umur2, 'data_type' => 'decimal2', 'align' => 'right', 'text_style' => 'bold', 'border' => 'border'),
            'L' => array('value' => $gt_umur3, 'data_type' => 'decimal2', 'align' => 'right', 'text_style' => 'bold', 'border' => 'border'),
            'M' => array('value' => $gt_umur4, 'data_type' => 'decimal2', 'align' => 'right', 'text_style' => 'bold', 'border' => 'border'),
            'N' => array('value' => $gt_umur5, 'data_type' => 'decimal2', 'align' => 'right', 'text_style' => 'bold', 'border' => 'border'),
            'O' => array('value' => $gt_umur6, 'data_type' => 'decimal2', 'align' => 'right', 'text_style' => 'bold', 'border' => 'border'),
            'P' => array('value' => $gt_umur7, 'data_type' => 'decimal2', 'align' => 'right', 'text_style' => 'bold', 'border' => 'border'),
        );

        Modules::run( 'base/ExportExcel/exportExcelUsingSpreadSheet', $filename, $arr_header, $arr_column, 1, 0 );

        $this->load->helper('download');
        force_download('export_excel/'.$filename.'.xlsx', NULL);
    }
}