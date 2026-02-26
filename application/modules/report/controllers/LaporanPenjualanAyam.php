<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class LaporanPenjualanAyam extends Public_Controller {

    private $pathView = 'report/laporan_penjualan_ayam/';
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
                "assets/report/laporan_penjualan_ayam/js/laporan-penjualan-ayam.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/laporan_penjualan_ayam/css/laporan-penjualan-ayam.css",
            ));

            $data = $this->includes;

            $m_wilayah = new \Model\Storage\Wilayah_model();
            $m_pelanggal = new \Model\Storage\Pelanggan_model();

            $content['unit'] = $m_wilayah->getDataUnit(1, $this->userid);
            $content['pelanggan'] = $m_pelanggal->getDataPelanggan(0, 1);
            $content['title_menu'] = 'Laporan Penjualan Ayam';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getData($params) {
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $unit = $params['unit'];
        $pelanggan = $params['pelanggan'];

        $sql_unit = null;
        if ( !in_array('all', $unit) ) {
            $sql_unit = "and SUBSTRING(drs.no_do, 4, 3) in ('".implode("', '", $unit)."')";
        }

        $sql_pelanggan = null;
        if ( !in_array('all', $pelanggan) ) {
            $sql_pelanggan = "and drs.no_pelanggan in ('".implode("', '", $pelanggan)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.*,
                m.nama as nama_mitra,
                w.nama as nama_unit
            from
            (
                select
                    drs.pelanggan as nama_pelanggan,
                    drs.tonase,
                    drs.ekor,
                    drs.bb,
                    drs.harga,
                    drs.no_do,
                    drs.no_sj,
                    drsi.no_inv,
                    drs.jenis_ayam,
                    drs.no_nota,
                    SUBSTRING(drs.no_do, 4, 3) as unit,
                    rs.tgl_panen,
                    rs.noreg
                from det_real_sj_inv drsi 
                right join
                    det_real_sj drs
                    on
                        drsi.no_sj = drs.no_sj
                right join
                    real_sj rs
                    on
                        drs.id_header = rs.id
                where
                    drs.ekor > 0 and
                    rs.tgl_panen between '".$start_date."' and '".$end_date."'
                    ".$sql_unit."
                    ".$sql_pelanggan."
            ) data
            left join
                rdim_submit rs
                on
                    rs.noreg = data.noreg
            left join
                (
                    select mm1.* from mitra_mapping mm1
                    right join
                        (select max(id) as id, nim from mitra_mapping group by nim) mm2
                        on
                            mm1.id = mm2.id
                ) mm
                on
                    rs.nim = mm.nim
            left join
                mitra m
                on
                    m.id = mm.mitra
            left join
                (
                    select *
                    from
                    (
                        select UPPER(REPLACE(REPLACE(w1.nama, 'Kota ', ''), 'Kab ', '')) as nama, w1.kode from wilayah w1
                        right join
                            (select max(id) as id, kode from wilayah group by kode) w2
                            on
                                w1.id = w2.id
                        where
                            w1.kode is not null
                    ) data
                    group by
                        data.nama,
                        data.kode
                ) w
                on
                    data.unit = w.kode
            order by
                data.unit asc,
                data.tgl_panen asc,
                data.no_do asc
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

        // cetak_r( $data, 1 );

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

        $filename = strtoupper("LAPORAN_PENJUALAN_AYAM_");
        $filename = $filename.str_replace('-', '', $start_date).'_'.str_replace('-', '', $end_date).'.xls';

        $arr_column = null;

        $idx = 0;
        $arr_column[ $idx ] = array(
            'A' => array('value' => 'LAPORAN PENJUALAN AYAM', 'data_type' => 'string', 'colspan' => array('A','F'), 'align' => 'left', 'text_style' => 'bold', 'border' => 'none'),
        );
        $idx++;
        $arr_column[ $idx ] = array(
            'A' => array('value' => 'PERIODE '.str_replace('-', '/', $start_date).' - '.str_replace('-', '/', $end_date), 'data_type' => 'string', 'colspan' => array('A','F'), 'align' => 'left', 'text_style' => 'bold', 'border' => 'none'),
        );
        $idx++;
        $arr_column[ $idx ] = array(
            'A' => array('value' => 'UNIT', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'B' => array('value' => 'TANGGAL', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'C' => array('value' => 'CUSTOMER', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'D' => array('value' => 'KANDANG', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'E' => array('value' => 'NOREG', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'F' => array('value' => 'NO. INVOICE', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'G' => array('value' => 'NOTA TIMBANG', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'H' => array('value' => 'JENIS AYAM', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'I' => array('value' => 'EKOR', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'J' => array('value' => 'TONASE', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'K' => array('value' => 'HARGA', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
            'L' => array('value' => 'TOTAL', 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold'),
        );
        $idx++;

        $start_row_header = $idx+1;

        $arr_header = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L');
        if ( !empty($data) ) {
            $gt_ekor = 0;
            $gt_tonase = 0;
            $gt_total = 0;
            foreach ($data as $k_data => $v_data) {
                $arr_column[ $idx ] = array(
                    'A' => array('value' => $v_data['nama_unit'], 'data_type' => 'string', 'align' => 'left'),
                    'B' => array('value' => $v_data['tgl_panen'], 'data_type' => 'date', 'align' => 'left'),
                    'C' => array('value' => $v_data['nama_pelanggan'], 'data_type' => 'string', 'align' => 'left'),
                    'D' => array('value' => $v_data['nama_mitra'], 'data_type' => 'string', 'align' => 'left'),
                    'E' => array('value' => $v_data['noreg'], 'data_type' => 'string', 'align' => 'left'),
                    'F' => array('value' => $v_data['no_inv'], 'data_type' => 'string', 'align' => 'left'),
                    'G' => array('value' => $v_data['no_nota'], 'data_type' => 'string', 'align' => 'left'),
                    'H' => array('value' => $this->config->item('jenis_ayam')[$v_data['jenis_ayam']], 'data_type' => 'string', 'align' => 'left'),
                    'I' => array('value' => $v_data['ekor'], 'data_type' => 'integer', 'align' => 'right'),
                    'J' => array('value' => $v_data['tonase'], 'data_type' => 'decimal2', 'align' => 'right'),
                    'K' => array('value' => $v_data['harga'], 'data_type' => 'decimal2', 'align' => 'right'),
                    'L' => array('value' => ($v_data['tonase'] * $v_data['harga']), 'data_type' => 'decimal2', 'align' => 'right'),
                );
                $idx++;

                $gt_ekor += $v_data['ekor'];
                $gt_tonase += $v_data['tonase'];
                $gt_total += ($v_data['tonase'] * $v_data['harga']);
            }

            $arr_column[ $idx ] = array(
                'H' => array('value' => 'TOTAL', 'colspan' => array('A','H'), 'data_type' => 'string', 'align' => 'left', 'text_style' => 'bold'),
                'I' => array('value' => $gt_ekor, 'data_type' => 'integer', 'align' => 'right', 'text_style' => 'bold'),
                'J' => array('value' => $gt_tonase, 'data_type' => 'decimal2', 'align' => 'right', 'text_style' => 'bold'),
                'K' => array('value' => '', 'data_type' => 'string', 'align' => 'right', 'text_style' => 'bold'),
                'L' => array('value' => $gt_total, 'data_type' => 'decimal2', 'align' => 'right', 'text_style' => 'bold'),
            );
        }

        Modules::run( 'base/ExportExcel/exportExcelUsingSpreadSheet', $filename, $arr_header, $arr_column, $start_row_header, 0 );

        $this->load->helper('download');
        force_download('export_excel/'.$filename.'.xlsx', NULL);
    }

    public function getDataInvoice($_no_inv) {
        $no_inv = exDecrypt( $_no_inv );

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.*,
                m.nama as nama_mitra,
                w.nama as nama_unit,
                data.tonase * data.harga as total,
                tp.pph
            from
            (
                select
                    drs.no_pelanggan,
                    drs.pelanggan as nama_pelanggan,
                    drs.tonase,
                    drs.ekor,
                    drs.bb,
                    drs.harga,
                    drs.no_do,
                    drs.no_sj,
                    drsi.no_inv,
                    drs.jenis_ayam,
                    drs.no_nota,
                    SUBSTRING(drs.no_do, 4, 3) as unit,
                    rs.tgl_panen,
                    rs.noreg
                from det_real_sj_inv drsi 
                right join
                    det_real_sj drs
                    on
                        drsi.no_sj = drs.no_sj
                right join
                    real_sj rs
                    on
                        drs.id_header = rs.id
                where
                    drs.ekor > 0 and
                    drsi.no_inv = '".$no_inv."'
            ) data
            left join
                rdim_submit rs
                on
                    rs.noreg = data.noreg
            left join
                (
                    select mm1.* from mitra_mapping mm1
                    right join
                        (select max(id) as id, nim from mitra_mapping group by nim) mm2
                        on
                            mm1.id = mm2.id
                ) mm
                on
                    rs.nim = mm.nim
            left join
                mitra m
                on
                    m.id = mm.mitra
            left join
                (
                    select *
                    from
                    (
                        select UPPER(REPLACE(REPLACE(w1.nama, 'Kota ', ''), 'Kab ', '')) as nama, w1.kode from wilayah w1
                        right join
                            (select max(id) as id, kode from wilayah group by kode) w2
                            on
                                w1.id = w2.id
                        where
                            w1.kode is not null
                    ) data
                    group by
                        data.nama,
                        data.kode
                ) w
                on
                    data.unit = w.kode
            left join
                (
                    select plg1.* from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan group by nomor) plg2
                        on
                            plg1.id = plg2.id
                ) plg
                on
                    plg.nomor = data.no_pelanggan
            left join
                tipe_pelanggan tp
                on
                    tp.id = plg.tipe_plg
            order by
                data.unit asc,
                data.tgl_panen asc,
                data.no_do asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ($d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function printPreview($_no_inv) {
        $m_conf = new \Model\Storage\Conf();
        $now = $m_conf->getDate()['waktu'];

        $_data = $this->getDataInvoice( $_no_inv );

        $data = array(
            'no_inv' => $_data[0]['no_inv'],
            'tanggal' => $_data[0]['tgl_panen'],
            'nama_pelanggan' => $_data[0]['nama_pelanggan'],
            'nama_mitra' => $_data[0]['nama_mitra'],
            'noreg' => $_data[0]['noreg'],
            'pph' => $_data[0]['pph'],
            'nama_karyawan' => $this->userdata['detail_user']['nama_detuser'],
            'waktu' => $now
        );
        $detail = $_data;

        $content['data'] = $data;
        $content['detail'] = $detail;

        // cetak_r( $content, 1 );

        $res_view_html = $this->load->view($this->pathView.'exportPdf', $content, true);

        echo $res_view_html;
    }
}