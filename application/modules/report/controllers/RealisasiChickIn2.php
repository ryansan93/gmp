<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class RealisasiChickIn2 extends Public_Controller {

    private $pathView = 'report/realisasi_chick_in2/';
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
                "assets/report/realisasi_chick_in2/js/realisasi-chick-in.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/realisasi_chick_in2/css/realisasi-chick-in.css",
            ));

            $data = $this->includes;

            $m_wilayah = new \Model\Storage\Wilayah_model();

            $content['unit'] = $m_wilayah->getDataUnit(1, $this->userid);
            $content['akses'] = $akses;
            $content['title_menu'] = 'Laporan Realisasi Chick In 2';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getData($start_date, $end_date, $unit, $jenis) {
        $sql_unit = null;
        if ( !in_array('all', $unit) ) {
            $sql_unit = "and w.kode in ('".implode("', '", $unit)."')";
        }

        $sql_periode = "
            where
                rs.tgl_docin between '".$start_date."' and '".$end_date."'
        ";
        if ( stristr($jenis, 'realisasi') !== FALSE ) {
            $sql_periode = "
                where
                    td.datang between '".$start_date."' and '".$end_date."'
            ";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                m.nama as nama_plasma,
                mm.nim,
                k.kandang,
                w.kode as kode_unit,
                rs.noreg,
                rs.tgl_docin as rcn_tgl_docin,
                rs.populasi as rcn_jml_ekor,
                (rs.populasi/100) as rcn_jml_box,
                td.datang as real_tgl_docin,
                td.jml_ekor as real_jml_ekor,
                td.jml_box as real_jml_box,
                -- td.bb,
                td.harga,
                k.alamat_jalan+' RT.'+cast(k.alamat_rt as varchar(3))+'/RW.'+cast(k.alamat_rw as varchar(3))+', '+k.alamat_kelurahan+', '+k.kecamatan+', '+k.kab_kota+', '+k.provinsi as alamat,
                k.kecamatan,
                k.kab_kota,
                panen.tgl_panen,
                ts.tgl_tutup
            from rdim_submit rs
            left join
                kandang kdg
                on
                    rs.kandang = kdg.id
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
                (
                    select k.*, l_kec.nama as kecamatan, l_kab_kota.nama as kab_kota, l_prov.nama as provinsi from kandang k 
                    left join
                        lokasi l_kec
                        on
                            k.alamat_kecamatan = l_kec.id
                    left join
                        lokasi l_kab_kota
                        on
                            l_kec.induk = l_kab_kota.id
                    left join
                        lokasi l_prov
                        on
                            l_kab_kota.induk = l_prov.id
                ) k
                on
                    kdg.kandang = k.kandang and
                    mm.id = k.mitra_mapping
            left join
                mitra m
                on
                    mm.mitra = m.id
            left join
                wilayah w
                on
                    k.unit = w.id
            left join
                (
                    select
                        od.noreg,
                        td.datang,
                        sum(td.jml_ekor) as jml_ekor,
                        sum(td.jml_box) as jml_box,
                        -- td.bb,
                        td.harga
                    from
                    (
                        select td1.* from terima_doc td1
                        right join
                            (select max(version) as version, no_order from terima_doc group by no_order) td2
                            on
                                td1.version = td2.version and
                                td1.no_order = td2.no_order
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
                    group by
                        od.noreg,
                        td.datang,
                        td.bb,
                        td.harga

                    union all

                    select
                        noreg,
                        tanggal as datang,
                        jumlah as jml_ekor,
                        jumlah / 100 as jml_box,
                        -- td.bb,
                        harga
                    from adjin_doc
                ) td
                on
                    rs.noreg = td.noreg
            left join
                (
                    select noreg, min(tgl_panen) as tgl_panen from real_sj group by noreg
                ) panen
                on
                    rs.noreg = panen.noreg
            left join
                tutup_siklus ts
                on
                    rs.noreg = ts.noreg
            ".$sql_periode."
            ".$sql_unit."
            order by
                w.kode asc,
                rs.tgl_docin asc,
                m.nama asc
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

        // cetak_r( $params, 1 );

        $start_date = $params['start_date'].' 00:00:00.001';
        $end_date = $params['end_date'].' 23:59:59.999';
        $unit = $params['unit'];
        $jenis = $params['jenis'];

        $data = $this->getData($start_date, $end_date, $unit, $jenis);

        $content['data'] = $data;

        $html = $this->load->view($this->pathView.'list', $content);

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

        $start_date = $params['start_date'].' 00:00:00.001';
        $end_date = $params['end_date'].' 23:59:59.999';
        $unit = $params['unit'];
        $jenis = $params['jenis'];

        $data = $this->getData($start_date, $end_date, $unit, $jenis);
            
        $filename = 'REPORT_CHICK_IN_BY_'.strtoupper($jenis).'_'.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']).'.xlsx';

        $arr_header = array('Unit', 'Nim', 'Noreg', 'Nama', 'Alamat', 'Kecamatan', 'Kabupaten/Kota', 'Tanggal Rencana', 'Box Rencana', 'Ekor Rencana', 'Tanggal Realisasi', 'Box Realisasi', 'Ekor Realisasi', 'Tgl Panen', 'Tgl Tutup Siklus');
        $arr_column = null;
        if ( !empty($data) ) {
            $idx = 0;
            foreach ($data as $key => $value) {
                $arr_column[ $idx ] = array(
                    'Unit' => array('value' => strtoupper($value['kode_unit']), 'data_type' => 'string'),
                    'Nim' => array('value' => strtoupper($value['nim']), 'data_type' => 'string'),
                    'Noreg' => array('value' => strtoupper($value['noreg']), 'data_type' => 'string'),
                    'Nama' => array('value' => strtoupper($value['nama_plasma']), 'data_type' => 'string'),
                    // 'Kandang' => array('value' => strtoupper($value['kandang']), 'data_type' => 'string'),
                    'Alamat' => array('value' => strtoupper($value['alamat']), 'data_type' => 'string'),
                    'Kecamatan' => array('value' => strtoupper($value['kecamatan']), 'data_type' => 'string'),
                    'Kabupaten/Kota' => array('value' => strtoupper($value['kab_kota']), 'data_type' => 'string'),
                    'Tanggal Rencana' => array('value' => $value['rcn_tgl_docin'], 'data_type' => 'date'),
                    'Box Rencana' => array('value' => $value['rcn_jml_box'], 'data_type' => 'integer'),
                    'Ekor Rencana' => array('value' => $value['rcn_jml_ekor'], 'data_type' => 'integer'),
                    'Tanggal Realisasi' => array('value' => $value['real_tgl_docin'], 'data_type' => 'datetime'),
                    'Box Realisasi' => array('value' => $value['real_jml_box'], 'data_type' => 'integer'),
                    'Ekor Realisasi' => array('value' => $value['real_jml_ekor'], 'data_type' => 'integer'),
                    'Tgl Panen' => array('value' => $value['tgl_panen'], 'data_type' => 'date'),
                    'Tgl Tutup Siklus' => array('value' => $value['tgl_tutup'], 'data_type' => 'date'),
                );

                $idx++;
            }
        }

        $this->exportExcelUsingSpreadSheet( $filename, $arr_header, $arr_column );
    }

    public function tes() {
        $dt = DateTime::createFromFormat('!Y-m-d H:i:s', '2025-09-29 13:05:00');

        cetak_r( $dt );
    }
}