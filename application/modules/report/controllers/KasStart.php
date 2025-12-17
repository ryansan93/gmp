<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KasStart extends Public_Controller {

    private $pathView = 'report/kas_start/';
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
                'assets/select2/js/select2.min.js',
                "assets/report/kas_start/js/kas-start.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/kas_start/css/kas-start.css",
            ));

            $data = $this->includes;

            $kode_unit = null;
            $periode = null;

            if ( !empty($params) ) {
                $params = json_decode(exDecrypt($params), true);

                $kode_unit = $params['kode_unit'];
                $periode = $params['periode'];
            }

            $m_coa = new \Model\Storage\Coa_model();

            $content['kas'] = $m_coa->getDataKas(1, $this->userid);
            $content['periode'] = $periode;
            $content['perusahaan'] = $this->getPerusahaan();
            $content['title_menu'] = 'Laporan Kas';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getPerusahaan()
    {
        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                UPPER(prs1.kode) as kode,
                UPPER(prs1.perusahaan) as nama_perusahaan
            from perusahaan prs1
            right join
                (select max(id) as id, kode from perusahaan group by kode) prs2
                on
                    prs1.id = prs2.id
            order by
                prs1.perusahaan asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getData($params) {
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

        $sql_kas = null;
        if ( stristr($kas, 'all') === false ) {
            $sql_kas = "where data.kas = '".$kas."'";
        } else {
            $m_coa = new \Model\Storage\Coa_model();
            $kas = $m_coa->getDataKas(1, $this->userid, 2);

            $arr_kas = null;
            foreach ($kas as $key => $value) {
                $arr_kas[] = $value['no_coa'];
            }

            $sql_kas = "where data.kas in ('".implode("', '", $arr_kas)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql_sa = "
            /* SALDO AWAL */
            select
                '' as tanggal,
                '' as kode,
                'Saldo Awal' as keterangan,
                case
                    when sa.debet2 > 0 then
                        -- sa.debet2
                        0
                    else
                        sa.debet1
                end as debet,
                0 as kredit,
                sa.kas
            from
            (
                select
                    sum(sa.debet1) as debet1,
                    sum(sa.kredit1) as kredit1,
                    sum(sa.debet2) as debet2,
                    sum(sa.kredit2) as kredit2,
                    sa.kas
                from
                (
                    select
                        sb.saldo_awal as debet1,
                        0 as kredit1,
                        0 as debet2,
                        0 as kredit2,
                        sb.coa as kas
                    from saldo_bulanan sb 
                    where
                        sb.tanggal = '".$start_date."'

                    union all

                    select
                        0 as debet1,
                        0 as kredit1,
                        sc.debet as debet2,
                        0 as kredit2,
                        sc.no_coa as kas
                    from sacoa sc
                    where
                        sc.periode = '".substr($start_date, 0, 7)."'
                ) sa
                group by
                    sa.kas
            ) sa
            /* END - SALDO AWAL */
        ";
        $d_conf = $m_conf->hydrateRaw( $sql_sa );
        if ( $d_conf->count() <= 0 ) {
            $end_date_new = prev_date($start_date);
            $start_date_new = substr($end_date_new, 0, 7).'-01';

            $sql_sa = "
                select
                    '' as tanggal,
                    '' as kode,
                    'Saldo Awal' as keterangan,
                    sum(isnull(data.debet, 0)) - sum(isnull(data.kredit, 0)) as debet,
                    0 as kredit,
                    data.kas
                from
                (
                    /* SALDO AWAL */
                    select
                        '' as tanggal,
                        '' as kode,
                        'Saldo Awal' as keterangan,
                        case
                            when sa.debet2 <> 0 then
                                sa.debet2
                            else
                                sa.debet1
                        end as debet,
                        0 as kredit,
                        sa.kas
                    from
                    (
                        select
                            sum(sa.debet1) as debet1,
                            sum(sa.kredit1) as kredit1,
                            sum(sa.debet2) as debet2,
                            sum(sa.kredit2) as kredit2,
                            sa.kas
                        from
                        (
                            select
                                sb.saldo_awal as debet1,
                                0 as kredit1,
                                0 as debet2,
                                0 as kredit2,
                                sb.coa as kas
                            from saldo_bulanan sb 
                            where
                                sb.tanggal = '".$start_date_new."'
        
                            union all
        
                            select
                                0 as debet1,
                                0 as kredit1,
                                sc.debet as debet2,
                                0 as kredit2,
                                sc.no_coa as kas
                            from sacoa sc
                            where
                                sc.periode = '".substr($start_date_new, 0, 7)."'
                        ) sa
                        group by
                            sa.kas
                    ) sa
                    /* END - SALDO AWAL */

                    union all

                    /* TRANSAKSI */
                    select
                        data.tanggal,
                        nb.kode,
                        data.keterangan,
                        data.debet,
                        data.kredit,
                        data.kas
                    from (
                        select * from no_bbk nb 

                        union all

                        select * from no_bbm nb
                    ) nb
                    left join
                        (
                            select 
                                'kk' as tbl_name,
                                ki.no_kk as tbl_id,
                                ki.tgl_kk as tanggal,
                                ki.keterangan,
                                0 as debet,
                                ki.nilai as kredit,
                                k.coa_bank as kas
                            from kkitem ki 
                            left join
                                kk k
                                on
                                    ki.no_kk = k.no_kk
                            where 
                                ki.no_kk not like '%BCA%' and 
                                ki.tgl_kk between '".$start_date_new."' and '".$end_date_new."'

                            union all

                            select 
                                'kk' as tbl_name,
                                ki.no_km as tbl_id,
                                ki.tgl_km as tanggal,
                                ki.keterangan,
                                ki.nilai as debet,
                                0 as kredit,
                                k.coa_bank as kas
                            from kmitem ki 
                            left join
                                km k
                                on
                                    ki.no_km = k.no_km
                            where 
                                ki.no_km not like '%BCA%' and 
                                ki.tgl_km between '".$start_date_new."' and '".$end_date_new."'
                        ) data
                        on
                            nb.tbl_id = data.tbl_id
                    where
                        data.tanggal between '".$start_date_new."' and '".$end_date_new."'
                    /* END - TRANSAKSI */
                ) data
                ".$sql_kas."
                group by
                    data.kas
            ";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.*,
                c.nama_coa as nama_kas
            from
            (
                /* SALDO AWAL */
                /*
                select
                    '' as tanggal,
                    '' as kode,
                    'Saldo Awal' as keterangan,
                    sb.saldo_awal as debet,
                    0 as kredit,
                    sb.coa as kas
                from saldo_bulanan sb 
                where
                    sb.tgl_trans = '".$start_date."'
                */
                select * from
                (
                    ".$sql_sa."
                ) data
                /* END - SALDO AWAL */

                union all

                /* INITIAL BALANCE */
                select
                    sc.periode+'-01' as tanggal,
                    'INIT'+REPLACE(sc.periode, '-', '') as kode,
                    'Initial Balance' as keterangan,
                    sc.debet as debet,
                    0 as kredit,
                    sc.no_coa as kas
                from sacoa sc
                where
                    sc.periode = '".substr($start_date, 0, 7)."' and
                    sc.debet <> 0
                /* END - INITIAL BALANCE */

                union all

                /* TRANSAKSI */
                select
                    data.tanggal,
                    nb.kode,
                    data.keterangan,
                    data.debet,
                    data.kredit,
                    data.kas
                from (
                    select * from no_bbk nb 

                    union all

                    select * from no_bbm nb
                ) nb
                left join
                    (
                        select 
                            'kk' as tbl_name,
                            ki.no_kk as tbl_id,
                            ki.tgl_kk as tanggal,
                            ki.keterangan,
                            0 as debet,
                            ki.nilai as kredit,
                            k.coa_bank as kas
                        from kkitem ki 
                        left join
                            kk k
                            on
                                ki.no_kk = k.no_kk
                        where 
                            ki.no_kk not like '%BCA%' and 
                            ki.tgl_kk between '".$start_date."' and '".$end_date."'

                        union all

                        select 
                            'kk' as tbl_name,
                            ki.no_km as tbl_id,
                            ki.tgl_km as tanggal,
                            ki.keterangan,
                            ki.nilai as debet,
                            0 as kredit,
                            k.coa_bank as kas
                        from kmitem ki 
                        left join
                            km k
                            on
                                ki.no_km = k.no_km
                        where 
                            ki.no_km not like '%BCA%' and 
                            ki.tgl_km between '".$start_date."' and '".$end_date."'
                    ) data
                    on
                        nb.tbl_id = data.tbl_id
                where
                    data.tanggal between '".$start_date."' and '".$end_date."'
                /* END - TRANSAKSI */
            ) data
            left join
                coa c
                on
                    c.coa = data.kas
            ".$sql_kas."
            order by
                data.kas asc,
                data.tanggal asc,
                data.kode asc
        ";
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

        $filename = strtoupper("LAPORAN_KAS_".str_replace(' ', '_', $d_conf['nama_coa'])."_");
        $filename = $filename.str_replace('-', '', $start_date).'_'.str_replace('-', '', $end_date).'.xls';

        $arr_column = null;

        $idx = 0;
        $arr_column[ $idx ] = array(
            'Saldo' => array('value' => 'LAPORAN KAS '.strtoupper($nama), 'data_type' => 'string', 'colspan' => array('A','F'), 'align' => 'left', 'text_style' => 'bold', 'border' => 'none'),
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