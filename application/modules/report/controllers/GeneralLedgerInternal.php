<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class GeneralLedgerInternal extends Public_Controller {

    private $pathView = 'report/general_ledger_internal/';
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
                "assets/report/general_ledger_internal/js/general-ledger-internal.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/general_ledger_internal/css/general-ledger-internal.css",
            ));

            $data = $this->includes;

            $m_wilayah = new \Model\Storage\Wilayah_model();

            $content['akses'] = $akses;
            $content['perusahaan'] = $this->getPerusahaan();
            $content['unit'] = $m_wilayah->getDataUnit();
            $content['title_menu'] = 'Laporan GL Internal';

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
        $sql = "select
                    data.no_coa,
                    data.unit,
                    data.nama_coa,
                    data.noreg,
                    data.nama_mitra,
                    sum(isnull(data.debet, 0)) as debet,
                    -sum(isnull(data.kredit, 0)) as kredit
                from
                (
                    select
                        case
                            when SUBSTRING(data.coa_asal, 1, 1) in (5, 6) then
                                data.coa_asal
                            else
                                data.coa_tujuan
                        end as no_coa,
                        case
                            when SUBSTRING(data.coa_asal, 1, 1) in (5, 6) then
                                data.unit
                            else
                                isnull(data.unit_tujuan, data.unit)
                        end as unit,
                        case
                            when SUBSTRING(data.coa_asal, 1, 1) in (5, 6) then
                                data.asal
                            else
                                data.tujuan
                        end as nama_coa,
                        data.noreg,
                        data.nama_mitra,
                        case
                            when SUBSTRING(data.coa_asal, 1, 1) in (5, 6) then
                                0
                            else
                                data.nominal
                        end as debet,
                        case
                            when SUBSTRING(data.coa_asal, 1, 1) in (5, 6) then
                                data.nominal
                            else
                                0
                        end as kredit
                    from
                    (
                        select dj.*, m.nama as nama_mitra from det_jurnal dj
                        left join
                            rdim_submit rs
                            on
                                dj.noreg = rs.noreg
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
                            jenis j
                            on
                                m.jenis = j.kode
                        where
                            dj.tanggal between '".$start_date."' and '".$end_date."' and
                            (SUBSTRING(dj.coa_asal, 1, 1) in (5, 6) or SUBSTRING(dj.coa_tujuan, 1, 1) in (5, 6)) and
                            dj.noreg is not null and
                            j.kode = 'MI'
                    ) data
                ) data ";

                if($unit != 'all' ){
                    $sql .=" where data.unit = '".$unit."' ";
                } 

                $sql .= "group by
                    data.no_coa,
                    data.unit,
                    data.nama_coa,
                    data.noreg,
                    data.nama_mitra
                order by
                    data.no_coa asc,
                    data.unit asc,
                    data.nama_mitra asc ";

        $m_conf = new \Model\Storage\Conf();
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }
        
        return $data;
    }

    public function getDetail($periode, $unit, $no_coa, $noreg) {
        $start_date = $periode;
        $end_date = date("Y-m-t", strtotime($start_date));
        
        $m_conf = new \Model\Storage\Conf();
        $sql = "select
                    data.no_coa,
                    data.unit,
                    data.nama_coa,
                    data.noreg,
                    data.tanggal,
                    data.kode_trans,
                    cast(data.keterangan as varchar(max)) as keterangan,
                    data.nama_mitra,
                    sum(data.debet) as debet,
                    sum(data.kredit) as kredit
                from
                (
                    select
                        case
                            when SUBSTRING(data.coa_asal, 1, 1) in (5, 6) then
                                data.coa_asal
                            else
                                data.coa_tujuan
                        end as no_coa,
                        case
                            when SUBSTRING(data.coa_asal, 1, 1) in (5, 6) then
                                data.unit
                            else
                                isnull(data.unit_tujuan, data.unit)
                        end as unit,
                        case
                            when SUBSTRING(data.coa_asal, 1, 1) in (5, 6) then
                                data.asal
                            else
                                data.tujuan
                        end as nama_coa,
                        data.noreg,
                        data.tanggal,
                        data.kode_trans,
                        cast(data.keterangan as varchar(max)) as keterangan,
                        data.nama_mitra,
                        case
                            when SUBSTRING(data.coa_asal, 1, 1) in (5, 6) then
                                0
                            else
                                data.nominal
                        end as debet,
                        case
                            when SUBSTRING(data.coa_asal, 1, 1) in (5, 6) then
                                data.nominal
                            else
                                0
                        end as kredit
                    from
                    (
                        select dj.*, m.nama as nama_mitra from det_jurnal dj
                        left join
                            rdim_submit rs
                            on
                                dj.noreg = rs.noreg
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
                            jenis j
                            on
                                m.jenis = j.kode
                        where
                            dj.tanggal between '".$start_date."' and '".$end_date."' and
                            (SUBSTRING(dj.coa_asal, 1, 1) in (5, 6) or SUBSTRING(dj.coa_tujuan, 1, 1) in (5, 6)) and
                            dj.noreg is not null and
                            j.kode = 'MI'
                    ) data
                ) data 
                 
                where data.noreg = '".$noreg."' and data.no_coa = '".$no_coa."'";

                if($unit != 'all' ){
                    $sql .="and data.unit = '".$unit."' ";
                } 

                $sql .= "

                group by
                    data.no_coa,
                    data.unit,
                    data.nama_coa,
                    data.noreg,
                    data.tanggal,
                    data.kode_trans,
                    cast(data.keterangan as varchar(max)),
                    data.nama_mitra
                order by
                    data.no_coa asc,
                    data.unit asc,
                    data.nama_mitra asc ";

                    // echo "<pre>";
                    // print_r($sql);
                    // die;

        $m_conf = new \Model\Storage\Conf();
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
       
        $grouped = [];

        foreach ($data as $row) {

            $noreg = $row['noreg'];
            $coa   = $row['no_coa'];

            $grouped[$coa][$noreg][] = $row;
        }
        // echo "<pre>";
        // print_r($grouped);
        // die;

        $content['data'] = $grouped;
        $content['periode'] = $start_date;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }

    public function formDetail()
    {
        $params = $this->input->get('params');

        $detail = $this->getDetail( $params['periode'], $params['unit'], $params['no_coa'],  $params['noreg'] );

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

        $grouped = [];

        foreach ($data as $row) {
            $noreg = $row['noreg'];
            $coa   = $row['no_coa'];

            $grouped[$coa][$noreg][] = $row;
        }

        // echo "<pre>";
        // print_r($grouped);
        // die;
            
        $if_unit = '';
        if ($unit !== 'all') {
            $if_unit = '_'.strtoupper($params['unit']);
        }


        $filename = 'GL_INTERNAL_PERIODE_'.$tahun.$bulan. $if_unit;

        $arr_header = array('No. COA', 'Unit', 'Nama COA', 'No. Reg' , 'Plasma' ,'Saldo Awal', 'Debet', 'Kredit', 'Saldo Akhir');
        $arr_column = null;
        if (!empty($grouped)) {

            $idx = 0;

            $tot_saldo_awal = 0;
            $tot_debet = 0;
            $tot_kredit = 0;
            $tot_saldo_akhir = 0;

            foreach ($grouped as $coa => $noregs) {
                foreach ($noregs as $noreg => $rows) {
                    foreach ($rows as $value) {

                        $saldo_awal = isset($value['saldo_awal']) ? (float)$value['saldo_awal'] : 0;
                        $debet = isset($value['debet']) ? (float)$value['debet'] : 0;
                        $kredit = isset($value['kredit']) ? (float)$value['kredit'] : 0;
                        $saldo_akhir = $saldo_awal + $debet + $kredit;

                        $arr_column[$idx] = array(
                            'No. COA' => array('value' => strtoupper($value['no_coa']), 'data_type' => 'nik'),
                            'Unit' => array('value' => strtoupper($value['unit']), 'data_type' => 'string'),
                            'Nama COA' => array('value' => strtoupper($value['nama_coa']), 'data_type' => 'string'),
                            'No. Reg' => array('value' => $value['noreg'], 'data_type' => 'string'),
                            'Plasma' => array('value' => strtoupper($value['nama_mitra']), 'data_type' => 'string'),
                            'Saldo Awal' => array('value' => $saldo_awal, 'data_type' => 'decimal2'),
                            'Debet' => array('value' => $debet, 'data_type' => 'decimal2'),
                            'Kredit' => array('value' => $kredit, 'data_type' => 'decimal2'),
                            'Saldo Akhir' => array('value' => $saldo_akhir, 'data_type' => 'decimal2'),
                        );

                        $tot_saldo_awal += $saldo_awal;
                        $tot_debet += $debet;
                        $tot_kredit += $kredit;
                        $tot_saldo_akhir += $saldo_akhir;

                        $idx++;
                    }
                }
            }

            $arr_column[] = array(
                'Nama COA' => array('value' => 'Total', 'data_type' => 'string', 'colspan' => array('A','E'), 'align' => 'right', 'text_style' => 'bold'),
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