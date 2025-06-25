<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class Bank extends Public_Controller {

    private $pathView = 'report/bank/';
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
                "assets/report/bank/js/bank.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/bank/css/bank.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['bank'] = $this->getBank();
            $content['akun_transaksi'] = $this->getAkunTransaksi();
            $content['title_menu'] = 'Laporan Bank';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getBank() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "select * from coa c where nama_coa like '%bca%' order by nama_coa asc";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getAkunTransaksi() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                djt.id,
                djt.sumber_coa,
                djt.tujuan_coa,
                djt_aktif.nama
            from det_jurnal_trans djt
            right join
                (
                    select djt.* from det_jurnal_trans djt
                    right join
                        jurnal_trans jt
                        on
                            djt.id_header = jt.id
                    where
                        jt.mstatus = 1
                ) djt_aktif
                on
                    djt.sumber_coa = djt_aktif.sumber_coa and
                    djt.tujuan_coa = djt_aktif.tujuan_coa and
                    djt_aktif.id = djt.id
            left join
                coa c_sumber
                on
                    djt.sumber_coa = c_sumber.coa 
            left join
                coa c_tujuan
                on
                    djt.tujuan_coa = c_tujuan.coa 
            where
                (c_sumber.nama_coa like '%bca%' or c_tujuan.nama_coa like '%bca%')
            order by
                djt.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $data[ $value['id'].'-'.$value['sumber_coa'].'-'.$value['tujuan_coa'] ]['nama'] = $value['nama'];
                $data[ $value['id'].'-'.$value['sumber_coa'].'-'.$value['tujuan_coa'] ]['id'][] = $value['id'];
            }
        }

        return $data;
    }

    public function getData( $params ) {
        $coa = $params['bank'];
        $akun_transaksi = $params['akun_transaksi'];

        $arr_coa = null;
        if ( stristr('- np', $coa) === false ) {
            $nama_coa = null;

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    c.nama_coa
                from coa c
                where
                    c.coa = '".$coa."'
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
            if ( $d_conf->count() > 0 ) {
                $nama_coa = $d_conf->toArray()[0]['nama_coa'];
            }

            // $sql_nama_coa = trim($nama_coa).'%';
            // if ( $tipe = 2 ) {
            $sql_nama_coa = '%'.trim($nama_coa).'%';
            // }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    c.coa
                from coa c
                where
                    c.nama_coa like '".$sql_nama_coa."' and
                    c.nama_coa not like '%- np%'
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $key => $value) {
                    $arr_coa[] = $value['coa'];
                }
            }
        } else {
            $arr_coa[] = $coa;
        }

        $start_date = null;
        $end_date = null;
        if ( $params['jenis_tanggal'] == 'bulanan' ) {
            $bulan = $params['bulan'];
            $tahun = substr($params['tahun'], 0, 4);

            $i = 0;
            $_bulan = 12;
            if ( $bulan != 'all' ) {
                $i = $bulan-1;
                $_bulan = $bulan;
            }

            $angka_bulan = (strlen($i+1) == 1) ? '0'.$i+1 : $i+1;

            $date = $tahun.'-'.$angka_bulan.'-01';
            $start_date = date("Y-m-d", strtotime($date));
            $end_date = date("Y-m-t", strtotime($date));
        } else {
            $start_date = $params['start_date'];
            $end_date = $params['end_date'];
        }

        $data = null;
        $data[0] = array(
            'id' => null,
            'tanggal' => $start_date,
            'keterangan' => 'SALDO AWAL',
            'debit' => 0,
            'kredit' => 0,
            'nama_jurnal_trans' => 'SALDO AWAL',
            'unit' => null
        );

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select top 1 sb.* from saldo_bank sb
            where
                sb.coa in ('".implode("', '", $arr_coa)."') and
                sb.tanggal = '".$start_date."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray()[0];

            if ( !empty($d_conf['saldo_akhir']) ) {
                $save = 0;
            }

            $data[0] = array(
                'id' => $d_conf['id'],
                'tanggal' => $start_date,
                'keterangan' => 'SALDO AWAL',
                'debit' => $d_conf['saldo_awal'],
                'kredit' => 0,
                'nama_jurnal_trans' => 'SALDO AWAL',
                'unit' => null
            );
        }

        $sql_akun_transaksi = "";
        if ( !in_array('all', $akun_transaksi) ) {
            $sql_akun_transaksi .= "and djt.id in ('".implode("', '", $akun_transaksi)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                dj.det_jurnal_trans_id,
                djt.id,
                dj.tanggal, 
                cast(dj.keterangan as varchar(250)) as keterangan,
                CASE 
                    WHEN djt.tujuan_coa in ('".implode("', '", $arr_coa)."') THEN
                        dj.nominal 
                    ELSE
                        0
                END as debit,
                CASE 
                    WHEN djt.sumber_coa in ('".implode("', '", $arr_coa)."') THEN
                        dj.nominal 
                    ELSE
                        0
                END as kredit,
                djt.nama_jurnal as nama_jurnal_trans,
                dj.unit,
                dj.no_bukti,
                case
                    when djt.sumber like 'BCA%' or djt.tujuan like 'BCA%' then
                        0
                    else
                        1
                end as jenis
            from det_jurnal dj 
            left join
                det_jurnal_trans _djt
                on
                    _djt.id = dj.det_jurnal_trans_id
            left join
                jurnal_trans jt
                on
                    jt.id = _djt.id_header
            left join
                (
                    select 
                        djt.id, 
                        djt.id_header,
                        djt.sumber, 
                        djt.sumber_coa, 
                        djt.tujuan, 
                        djt.tujuan_coa, 
                        djt.nama as nama_jurnal,
                        jt.nama as nama_aktif,
                        jt.kode,
                        djt.kode as kode_det
                    from det_jurnal_trans djt
                    left join
                        jurnal_trans jt
                        on
                            djt.id_header = jt.id
                    --right join
                    --    (
                    --        select djt.* from det_jurnal_trans djt
                    --        right join
                    --            jurnal_trans jt
                    --            on
                    --                djt.id_header = jt.id
                    --        where
                    --            jt.mstatus = 1
                    --    ) djt_aktif
                    --    on
                    --        djt.sumber_coa = djt_aktif.sumber_coa and 
                    --        djt.tujuan_coa = djt_aktif.tujuan_coa
                    --        -- and djt.id_header = djt_aktif.id_header
                    left join
                        coa c_sumber
                        on
                            djt.sumber_coa = c_sumber.coa 
                    left join
                        coa c_tujuan
                        on
                            djt.tujuan_coa = c_tujuan.coa
                    where
                        jt.mstatus = 1 and
                        (c_sumber.nama_coa like '%bca%' or c_tujuan.nama_coa like '%bca%')
                    group by
                        djt.id, 
                        djt.id_header,
                        djt.sumber, 
                        djt.sumber_coa, 
                        djt.tujuan,
                        djt.tujuan_coa,
                        djt.nama,
                        jt.nama,
                        jt.kode,
                        djt.kode
                ) djt
                on
                    djt.sumber_coa = dj.coa_asal and
                    djt.tujuan_coa = dj.coa_tujuan and
                    djt.kode_det = _djt.kode
                    -- and ((jt.kode = djt.kode) or (jt.kode is null and djt.kode is null) )
            where
                -- dj.nominal > 0 and
                -- ((jt.kode = djt.kode and djt.id = _djt.id) or jt.kode = djt.kode) and
                (djt.sumber_coa in ('".implode("', '", $arr_coa)."') or djt.tujuan_coa in ('".implode("', '", $arr_coa)."')) and
                dj.tanggal between '".$start_date."' and '".$end_date."'
                ".$sql_akun_transaksi."
            group by
                dj.det_jurnal_trans_id,
                djt.id,
                dj.id,
                dj.tanggal, 
                cast(dj.keterangan as varchar(250)),
                djt.sumber,
                djt.sumber_coa,
                djt.tujuan,
                djt.tujuan_coa,
                dj.nominal,
                djt.nama_jurnal,
                dj.unit,
                dj.no_bukti
            order by
                dj.tanggal asc,
                dj.id asc
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            // cetak_r( $d_conf, 1 );

            foreach ($d_conf as $key => $value) {
                array_push( $data, $value );
            }
        }

        return $data;
    }

    public function getLists() {
        $params = $this->input->get('params');

        $content['data'] = $this->getData( $params );
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

        $start_date = null;
        $end_date = null;
        if ( $params['jenis_tanggal'] == 'bulanan' ) {
            $bulan = $params['bulan'];
            $tahun = substr($params['tahun'], 0, 4);

            $i = 0;
            $_bulan = 12;
            if ( $bulan != 'all' ) {
                $i = $bulan-1;
                $_bulan = $bulan;
            }

            $angka_bulan = (strlen($i+1) == 1) ? '0'.$i+1 : $i+1;

            $date = $tahun.'-'.$angka_bulan.'-01';
            $start_date = date("Y-m-d", strtotime($date));
            $end_date = date("Y-m-t", strtotime($date));
        } else {
            $start_date = $params['start_date'];
            $end_date = $params['end_date'];
        }

        $data = $this->getData( $params );

        // cetak_r( $data, 1 );

        $filename = "LAPORAN_BANK_";
        $filename = $filename.str_replace('-', '', $start_date).'_'.str_replace('-', '', $end_date).'.xls';

        $arr_header = array('Tanggal', 'Akun Transaksi', 'Keterangan', 'No. Bukti', 'Unit', 'Debit', 'Kredit');
        $arr_column = null;
        if ( !empty($data) ) {
            $idx = 0;
            foreach ($data as $key => $value) {
                if ( $value['jenis'] <> 1 ) {
                    $arr_column[ $idx ] = array(
                        'Tanggal' => array('value' => $value['tanggal'], 'data_type' => 'date'),
                        'Akun Transaksi' => array('value' => $value['nama_jurnal_trans'], 'data_type' => 'string'),
                        'Keterangan' => array('value' => trim($value['keterangan']), 'data_type' => 'string'),
                        'No. Bukti' => array('value' => trim($value['no_bukti']), 'data_type' => 'string'),
                        'Unit' => array('value' => trim($value['unit']), 'data_type' => 'string'),
                        'Debit' => array('value' => $value['debit'], 'data_type' => 'decimal2'),
                        'Kredit' => array('value' => $value['kredit'], 'data_type' => 'decimal2')
                    );

                    $idx++;
                }
            }
        }

        $this->exportExcelUsingSpreadSheet( $filename, $arr_header, $arr_column );
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

        $this->load->helper('download');
        force_download('export_excel/'.$filename, NULL);
    }
}