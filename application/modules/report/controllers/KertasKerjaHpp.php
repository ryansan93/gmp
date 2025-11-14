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

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select data.* from
            (
                
            ) data
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