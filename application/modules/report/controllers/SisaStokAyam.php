<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class SisaStokAyam extends Public_Controller {

    private $pathView = 'report/sisa_stok_ayam/';
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
                "assets/report/sisa_stok_ayam/js/sisa-stok-ayam.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/sisa_stok_ayam/css/sisa-stok-ayam.css",
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
            $sql_unit = "and w.kode = '".$unit."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                od.noreg, 
                w.kode, 
                k.kandang,
                td.datang as tgl_chick_in, 
                lhk.tanggal as tgl_datang_ppl,
                lhk.umur,
                (td.jml_ekor+isnull(ad.jumlah, 0)) as populasi,
                isnull(lhk.ekor_mati, 0) as ekor_mati_real,
                (td.jml_ekor+isnull(ad.jumlah, 0)) - isnull(lhk.ekor_mati, 0) as sisa_ekor_lhk,
                lhk.bb as bw_rata_lhk,
                (((td.jml_ekor+isnull(ad.jumlah, 0)) - isnull(lhk.ekor_mati, 0)) * lhk.bb) as tonase_lhk,
                panen.tgl_panen_terakhir as tgl_panen_terakhir,
                isnull(panen.ekor, 0) as ekor_jual,
                isnull(panen.tonase, 0) as tonase_jual,
                (td.jml_ekor+isnull(ad.jumlah, 0)) - isnull(lhk.ekor_mati, 0) - isnull(panen.ekor, 0) as sisa_ekor, 
                (((td.jml_ekor+isnull(ad.jumlah, 0)) - isnull(lhk.ekor_mati, 0)) * lhk.bb) - isnull(panen.tonase, 0) as sisa_tonase,
                ts.tgl_tutup as tgl_tutup_siklus
            from 
            (
                select td1.* from terima_doc td1
                right join
                    (select max(id) as id, no_order from terima_doc td group by no_order) td2
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
            left join
                (
                    select l1.tanggal, l1.noreg, l1.umur, l1.pakai_pakan, l1.sisa_pakan, l1.ekor_mati, l1.bb from lhk l1
                    right join
                        (select max(umur) as umur, noreg from lhk where tanggal <= '".$tanggal."' group by noreg) l2
                        on
                            l1.umur = l2.umur and
                            l1.noreg = l2.noreg
                ) lhk
                on
                    lhk.noreg = od.noreg
            left join
                (
                    select noreg, sum(netto_ekor) as ekor, sum(netto_kg) as tonase, max(tgl_panen) as tgl_panen_terakhir from real_sj rs where tgl_panen <= '".$tanggal."' group by noreg
                ) panen
                on
                    panen.noreg = od.noreg
            left join
                (
                    select noreg, sum(jumlah) as jumlah from adjin_doc group by noreg
                ) ad
                on
                    ad.noreg = od.noreg
            left join
                (select * from tutup_siklus where tgl_tutup <= '".$tanggal."' ) ts 
                on
                    ts.noreg = od.noreg
            left join
                rdim_submit rs 
                on
                    rs.noreg = od.noreg
            left join
                kandang k 
                on
                    rs.kandang = k.id
            left join
                wilayah w 
                on
                    w.id = k.unit
            where
                td.datang <= '".$tanggal."'
                -- and ts.id is null
                -- and td.jml_ekor - isnull(lhk.ekor_mati, 0) - isnull(panen.ekor, 0) > 0
                ".$sql_unit."
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

        $unit = $params['unit'];
        $tanggal = $params['tanggal'];

        $data = $this->getData( $params );

        $filename = strtoupper("LAPORAN_SISA_STOK_AYAM_PER_".str_replace('-', '', $tanggal)).".xls";

        $arr_column = null;

        $idx = 0;
        $arr_column[ $idx ] = array(
            'UMUR' => array('value' => 'LAPORAN SISA STOK AYAM GMP', 'data_type' => 'string', 'colspan' => array('A','F'), 'align' => 'left', 'text_style' => 'bold', 'border' => 'none'),
        );
        $idx++;
        $arr_column[ $idx ] = array(
            'UMUR' => array('value' => 'PER TANGGAL '.str_replace('-', '/', $tanggal), 'data_type' => 'string', 'colspan' => array('A','F'), 'align' => 'left', 'text_style' => 'bold', 'border' => 'none'),
        );
        $idx++;

        $start_row_header = $idx+1;

        $arr_header = array('NOREG', 'UNIT', 'KDG', 'TGL CHICK IN', 'TGL DATANG PPL', 'UMUR', 'POPULASI', 'EKOR MATI REAL', 'SISA EKOR LHK', 'BW RATA LHK', 'TONASE LHK', 'TGL PANEN TERAKHIR', 'EKOR JUAL', 'TONASE JUAL', 'SISA EKOR', 'SISA TONASE', 'TGL TUTUP SIKLUS');
        if ( !empty($data) ) {
            foreach ($data as $key => $value) {
                
                $arr_column[ $idx ] = array(
                    'NOREG' => array('value' => $value['noreg'], 'data_type' => 'string'),
                    'UNIT' => array('value' => $value['kode'], 'data_type' => 'string'),
                    'KDG' => array('value' => $value['noreg'], 'data_type' => 'string'),
                    'TGL CHICK IN' => array('value' => $value['tgl_chick_in'], 'data_type' => 'date'),
                    'TGL DATANG PPL' => array('value' => $value['tgl_datang_ppl'], 'data_type' => 'date'),
                    'UMUR' => array('value' => $value['umur'], 'data_type' => 'string'),
                    'POPULASI' => array('value' => $value['populasi'], 'data_type' => 'integer'),
                    'EKOR MATI REAL' => array('value' => $value['ekor_mati_real'], 'data_type' => 'integer'),
                    'SISA EKOR LHK' => array('value' => $value['sisa_ekor_lhk'], 'data_type' => 'integer'),
                    'BW RATA LHK' => array('value' => $value['bw_rata_lhk'], 'data_type' => 'decimal2'),
                    'TONASE LHK' => array('value' => $value['tonase_lhk'], 'data_type' => 'decimal2'),
                    'TGL PANEN TERAKHIR' => array('value' => $value['tgl_panen_terakhir'], 'data_type' => 'date'),
                    'EKOR JUAL' => array('value' => $value['ekor_jual'], 'data_type' => 'integer'),
                    'TONASE JUAL' => array('value' => $value['tonase_jual'], 'data_type' => 'decimal2'),
                    'SISA EKOR' => array('value' => $value['sisa_ekor'], 'data_type' => 'integer'),
                    'SISA TONASE' => array('value' => $value['sisa_tonase'], 'data_type' => 'decimal2'),
                    'TGL TUTUP SIKLUS' => array('value' => $value['tgl_tutup_siklus'], 'data_type' => 'date'),
                );

                $idx++;
            }
        }

        Modules::run( 'base/ExportExcel/exportExcelUsingSpreadSheet', $filename, $arr_header, $arr_column, $start_row_header );

        $this->load->helper('download');
        force_download('export_excel/'.$filename.'.xlsx', NULL);
    }
}