<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class SisaStokAyamMinMax extends Public_Controller {

    private $pathView = 'report/sisa_stok_ayam_min_max/';
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
                "assets/jquery/tupage-table/jquery.tupage.table.js",
                "assets/report/sisa_stok_ayam_min_max/js/sisa-stok-ayam-min-max.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/jquery/tupage-table/jquery.tupage.table.css",
                "assets/report/sisa_stok_ayam_min_max/css/sisa-stok-ayam-min-max.css",
            ));

            $data = $this->includes;

            $m_wil = new \Model\Storage\Wilayah_model();

            $content['unit'] = $m_wil->getDataUnit(1, $this->userid);
            $content['title_menu'] = 'Laporan Sisa Stok Ayam';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getData($params) {
        $unit = $params['unit'];
        $tanggal = $params['tanggal'];

        $sql_unit = null;
        if ( stristr($unit, 'all') === false ) {
            $sql_unit = "where data.kode = '".$unit."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.kode,
                data.umur,
                min(data.bb) as min_bw,
                max(data.bb) as max_bw,
                round(sum(data.tonase) / sum(data.sisa_ekor), 3) as rata_bw,
                sum(data.sisa_ekor) as total_ekor
            from
            (
                select
                    l.tanggal,
                    w.kode,
                    l.noreg,
                    (td.jml_ekor+isnull(ad.jumlah, 0)) as jml_ekor,
                    l.umur,
                    l.ekor_mati,
                    l.bb,
                    ((td.jml_ekor+isnull(ad.jumlah, 0)) - l.ekor_mati) as sisa_ekor,
                    ((td.jml_ekor+isnull(ad.jumlah, 0)) - l.ekor_mati) * l.bb as tonase
                from 
                (
                    select max(id) as id, kode from wilayah
                    where
                        kode is not null
                    group by
                        kode
                ) w
                left join
                    (
                        select
                            w.kode, l.*
                        from
                        (
                            select l1.* from lhk l1
                            right join
                            (
                                select max(tanggal) as tanggal, noreg from lhk where tanggal <= '".$tanggal."' group by noreg
                            ) l2
                            on
                                l1.tanggal = l2.tanggal and
                                l1.noreg = l2.noreg
                        ) l
                        left join
                            rdim_submit rs
                            on
                                l.noreg = rs.noreg
                        left join
                            kandang k
                            on
                                rs.kandang = k.id
                        left join
                            wilayah w
                            on
                                k.unit = w.id
                    ) l
                    on
                        l.kode = w.kode
                left join
                    (select * from tutup_siklus where tgl_tutup <= '".$tanggal."') ts
                    on
                        l.noreg = ts.noreg
                left join
                    (
                        select od.noreg, td.* from (
                            select td1.* from terima_doc td1
                            right join
                                (select max(id) as id, no_order from terima_doc group by no_order) td2
                                on
                                    td1.id = td2.id
                        ) td
                        left join
                            (
                                select od1.* from order_doc od1
                                right join
                                    (select max(id) as id, no_order from order_doc group by no_order) od2
                                    on
                                        od1.id = od2.id
                            ) od
                            on
                                td.no_order = od.no_order
                    ) td
                    on
                        l.noreg = td.noreg
                left join
                    (
                        select noreg, sum(jumlah) as jumlah from adjin_doc group by noreg
                    ) ad
                    on
                        l.noreg = ad.noreg
                where
                    ts.id is null and
                    l.id is not null
            ) data
            ".$sql_unit."
            group by
                data.kode,
                data.umur
            order by
                data.umur asc,
                data.kode asc
        ";

        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ($d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $key = $value['kode'].'|'.$value['umur'];
                $data[$key] = $value;
            }
        }

        return $data;
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $m_wil = new \Model\Storage\Wilayah_model();

        $data = $this->getData( $params );
        
        $content['data'] = $data;
        // echo "<pre>";
        // print_r($content);
        // die;
        $content['unit'] = $m_wil->getDataUnit(1, $this->userid);
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

        $unit = $params['unit'];
        $tanggal = $params['tanggal'];

        $m_wil = new \Model\Storage\Wilayah_model();
        $d_unit = $m_wil->getDataUnit(1, $this->userid);

        for ($i=1; $i <= (((count( $d_unit )-2)*4)+1); $i++) {
            $arr_header[] = toAlpha($i);
        }

        $data = $this->getData( $params );

        $filename = strtoupper("LAPORAN_SISA_STOK_AYAM_PER_".str_replace('-', '', $tanggal)).".xls";

        $arr_column = null;

        $idx = 0;
        $arr_column[ $idx ] = array(
            'A' => array('value' => 'LAPORAN SISA STOK AYAM GMP', 'data_type' => 'string', 'colspan' => array('A','F'), 'align' => 'left', 'text_style' => 'bold', 'border' => 'none'),
        );
        $idx++;
        $arr_column[ $idx ] = array(
            'A' => array('value' => 'PER TANGGAL '.str_replace('-', '/', $tanggal), 'data_type' => 'string', 'colspan' => array('A','F'), 'align' => 'left', 'text_style' => 'bold', 'border' => 'none'),
        );
        $idx++;
        $arr_column[ $idx ] = array(
            'A' => array('value' => 'UMUR', 'data_type' => 'string', 'rowspan' => array('A'.($idx+1),'A'.($idx+2)), 'align' => 'center', 'text_style' => 'bold', 'border' => 'border'),
        );
        $idx_kolom = 2;
        foreach ($d_unit as $k_unit => $v_unit) {
            if ( $v_unit['kode'] != 'JTM' && $v_unit['kode'] != 'PST' ) {
                $alphaCol1 = toAlpha($idx_kolom);
                $alphaCol2 = toAlpha($idx_kolom+3);

                $arr_column[ $idx ][ $alphaCol2 ] = array('value' => $v_unit['nama'], 'data_type' => 'string', 'colspan' => array($alphaCol1,$alphaCol2), 'align' => 'center', 'text_style' => 'bold', 'border' => 'border');

                $idx_kolom = $idx_kolom+4;
            }
        }

        $idx++;
        $idx_kolom = 2;
        foreach ($d_unit as $k_unit => $v_unit) {
            if ( $v_unit['kode'] != 'JTM' && $v_unit['kode'] != 'PST' ) {
                $alphaCol = toAlpha($idx_kolom);
                $arr_column[ $idx ][ $alphaCol ] = array('value' => 'BW MIN', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold', 'border' => 'border');
                $idx_kolom++;
                $alphaCol = toAlpha($idx_kolom);
                $arr_column[ $idx ][ $alphaCol ] = array('value' => 'BW MAX', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold', 'border' => 'border');
                $idx_kolom++;
                $alphaCol = toAlpha($idx_kolom);
                $arr_column[ $idx ][ $alphaCol ] = array('value' => 'RATA BW', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold', 'border' => 'border');
                $idx_kolom++;
                $alphaCol = toAlpha($idx_kolom);
                $arr_column[ $idx ][ $alphaCol ] = array('value' => 'JML EKOR', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold', 'border' => 'border');
                $idx_kolom++;
            }
        }
        $idx++;

        $arr_total = null;

        if ( !empty($data) ) {
            for ($i=0; $i <= 50; $i++) {
                $idx_kolom = 1;
                $alphaCol = toAlpha($idx_kolom);
                $arr_column[ $idx ][ $alphaCol ] = array('value' => $i, 'data_type' => 'integer', 'align' => 'right', 'border' => 'border');
                $idx_kolom++;
                foreach ($d_unit as $k_unit => $v_unit) {
                    if ( $v_unit['kode'] != 'JTM' && $v_unit['kode'] != 'PST' ) {
                        if ( isset($data[ $v_unit['kode'].'|'.$i ]) ) {
                            $alphaCol = toAlpha($idx_kolom);
                            $arr_column[ $idx ][ $alphaCol ] = array('value' => $data[ $v_unit['kode'].'|'.$i ]['min_bw'], 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border');
                            $idx_kolom++;
                            $alphaCol = toAlpha($idx_kolom);
                            $arr_column[ $idx ][ $alphaCol ] = array('value' => $data[ $v_unit['kode'].'|'.$i ]['max_bw'], 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border');
                            $idx_kolom++;
                            $alphaCol = toAlpha($idx_kolom);
                            $arr_column[ $idx ][ $alphaCol ] = array('value' => $data[ $v_unit['kode'].'|'.$i ]['rata_bw'], 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border');
                            $idx_kolom++;
                            $alphaCol = toAlpha($idx_kolom);
                            $arr_column[ $idx ][ $alphaCol ] = array('value' => $data[ $v_unit['kode'].'|'.$i ]['total_ekor'], 'data_type' => 'integer', 'align' => 'right', 'border' => 'border');
                            $idx_kolom++;
                        } else {
                            $alphaCol = toAlpha($idx_kolom);
                            $arr_column[ $idx ][ $alphaCol ] = array('value' => null, 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border');
                            $idx_kolom++;
                            $alphaCol = toAlpha($idx_kolom);
                            $arr_column[ $idx ][ $alphaCol ] = array('value' => null, 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border');
                            $idx_kolom++;
                            $alphaCol = toAlpha($idx_kolom);
                            $arr_column[ $idx ][ $alphaCol ] = array('value' => null, 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border');
                            $idx_kolom++;
                            $alphaCol = toAlpha($idx_kolom);
                            $arr_column[ $idx ][ $alphaCol ] = array('value' => null, 'data_type' => 'integer', 'align' => 'right', 'border' => 'border');
                            $idx_kolom++;
                        }

                        if ( isset($arr_total[ $v_unit['kode'] ]) ) {
                            $arr_total[ $v_unit['kode'] ] += isset($data[ $v_unit['kode'].'|'.$i ]) ? $data[ $v_unit['kode'].'|'.$i ]['total_ekor'] : 0;
                        } else {
                            $arr_total[ $v_unit['kode'] ] = isset($data[ $v_unit['kode'].'|'.$i ]) ? $data[ $v_unit['kode'].'|'.$i ]['total_ekor'] : 0;
                        }
                    }
                }
                $idx++;
            }

            $idx_kolom = 1;
            $alphaCol = toAlpha($idx_kolom);
            $arr_column[ $idx ][ $alphaCol ] = array('value' => '', 'data_type' => 'integer', 'align' => 'right', 'border' => 'border');
            $idx_kolom++;
            foreach ($d_unit as $k_unit => $v_unit) {
                if ( $v_unit['kode'] != 'JTM' && $v_unit['kode'] != 'PST' ) {
                    $alphaCol = toAlpha($idx_kolom);
                    $arr_column[ $idx ][ $alphaCol ] = array('value' => '', 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border');
                    $idx_kolom++;
                    $alphaCol = toAlpha($idx_kolom);
                    $arr_column[ $idx ][ $alphaCol ] = array('value' => '', 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border');
                    $idx_kolom++;
                    $alphaCol = toAlpha($idx_kolom);
                    $arr_column[ $idx ][ $alphaCol ] = array('value' => '', 'data_type' => 'decimal2', 'align' => 'right', 'border' => 'border');
                    $idx_kolom++;
                    $alphaCol = toAlpha($idx_kolom);
                    $arr_column[ $idx ][ $alphaCol ] = array('value' => $arr_total[ $v_unit['kode'] ], 'data_type' => 'integer', 'align' => 'right', 'text_style' => 'bold', 'border' => 'border');
                    $idx_kolom++;
                }
            }
        }

        Modules::run( 'base/ExportExcel/exportExcelUsingSpreadSheet', $filename, $arr_header, $arr_column, 1, 0 );

        $this->load->helper('download');
        force_download('export_excel/'.$filename.'.xlsx', NULL);
    }

    public function showDetailStokAyam()
    {
        $params = $this->input->post('params');
        $m_wil  = new \Model\Storage\Wilayah_model();
        $m_conf = new \Model\Storage\Conf();

        $sql = " select
                data.kode,
                data.noreg,
                data.tanggal,
                data.umur,
                data.jml_ekor,
                data.ekor_mati,
                data.sisa_ekor,
                data.bb,
                data.tonase
            from
            (
                select
                    l.tanggal,
                    w.kode,
                    l.noreg,
                    (td.jml_ekor+isnull(ad.jumlah, 0)) as jml_ekor,
                    l.umur,
                    l.ekor_mati,
                    l.bb,
                    ((td.jml_ekor+isnull(ad.jumlah, 0)) - l.ekor_mati) as sisa_ekor,
                    ((td.jml_ekor+isnull(ad.jumlah, 0)) - l.ekor_mati) * l.bb as tonase
                from 
                (
                    select max(id) as id, kode 
                    from wilayah
                    where kode is not null
                    group by kode
                ) w
                left join
                (
                    select
                        w.kode, l.*
                    from
                    (
                        select l1.* 
                        from lhk l1
                        right join
                        (
                            select max(tanggal) as tanggal, noreg 
                            from lhk 
                            where tanggal <= '". $params['tanggal'] ."' 
                            group by noreg
                        ) l2
                        on
                            l1.tanggal = l2.tanggal and
                            l1.noreg = l2.noreg
                    ) l
                    left join rdim_submit rs on l.noreg = rs.noreg
                    left join kandang k on rs.kandang = k.id
                    left join wilayah w on k.unit = w.id
                ) l on l.kode = w.kode
                left join
                    (select * from tutup_siklus where tgl_tutup <= '". $params['tanggal'] ."') ts
                    on l.noreg = ts.noreg
                left join
                (
                    select od.noreg, td.* 
                    from (
                        select td1.* 
                        from terima_doc td1
                        right join
                            (select max(id) as id, no_order from terima_doc group by no_order) td2
                            on td1.id = td2.id
                    ) td
                    left join
                    (
                        select od1.* 
                        from order_doc od1
                        right join
                            (select max(id) as id, no_order from order_doc group by no_order) od2
                            on od1.id = od2.id
                    ) od
                    on td.no_order = od.no_order
                ) td on l.noreg = td.noreg
                left join
                (
                    select noreg, sum(jumlah) as jumlah 
                    from adjin_doc 
                    group by noreg
                ) ad on l.noreg = ad.noreg
                where
                    ts.id is null and
                    l.id is not null
            ) data
            where
                data.tanggal <= '". $params['tanggal'] ."' 
                and data.umur = ". $params['umur'] ."
                and data.kode = '". $params['unit'] ."'
            order by
                data.kode asc,
                data.noreg asc
        
        ";

        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ($d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $noreg = array_column($data, 'noreg');
        $result = "'" . implode("','", $noreg) . "'";
        
        $content['data_header'] = $params;
        $content['data_detail'] = $data;
        $content['plasma']      = $this->getDataMitra($result);
        $content['unit']        = $m_wil->getDataUnit(1, $this->userid);

        // echo "<pre>";
        // print_r($content);
        // die;
        
        echo $this->load->view($this->pathView.'list_detail', $content, TRUE);

    }

    public function getDataMitra($noreg)
    {
        
        $m_conf = new \Model\Storage\Conf();

        $sql = " select distinct(rs.noreg), m.nama from rdim_submit rs
                inner join mitra_mapping mm on rs.nim = mm.nim
                inner join mitra m on mm.mitra = m.id 
                where rs.noreg in ($noreg) ";

        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ($d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        // $data_temp = [];
        // foreach($data as $d){
        //     $data
        // }

        return $data;

        // echo "<pre>";
        // print_r($data);
        // die;
    }
}