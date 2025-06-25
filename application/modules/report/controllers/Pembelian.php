<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class Pembelian extends Public_Controller {

    private $pathView = 'report/pembelian/';
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
                "assets/report/pembelian/js/pembelian.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/pembelian/css/pembelian.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['unit'] = $this->getUnit();
            $content['perusahaan'] = $this->getPerusahaan();

            $content['title_menu'] = 'Pembelian';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getUnit()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', '') as nama,
                w.kode
            from wilayah w
            where
                w.jenis = 'UN'
            group by
                REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', ''),
                w.kode
            order by
                REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', '') asc
        ";
        $d_unit = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_unit->count() > 0 ) {
            $data = $d_unit->toArray();
        }

        return $data;
    }

    public function getPerusahaan()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select p.id, p.kode, p.perusahaan as nama from perusahaan p
            right join
                (select max(id) as id, kode from perusahaan group by kode) _p
                on
                    _p.id = p.id
            order by
                p.perusahaan asc
        ";
        $d_perusahaan = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_perusahaan->count() > 0 ) {
            $data = $d_perusahaan->toArray();
        }

        return $data;
    }

    public function getDataDoc( $start_date, $end_date, $unit, $perusahaan )
    {
        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit .= "and data.unit in ('".implode("', '", $unit)."')";
        }

        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan .= "and data.kode_perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.noreg,
                data.no_order,
                data.no_sj,
                data.datang,
                data.nama,
                data.kandang,
                data.unit,
                data.supplier,
                data.barang,
                data.nama_perusahaan,
                data.jumlah,
                data.harga,
                data.total
            from
            (
                select
                    od.noreg,
                    od.no_order,
                    td.no_sj,
                    cast(td.datang as date) as datang,
                    rs.nama,
                    rs.kandang,
                    SUBSTRING(od.no_order, 5, 3)  as unit,
                    supl.nama as supplier,
                    (brg.nama + ' BOX ' + isnull(od.jns_box, '')) as barang,
                    prs.nama as nama_perusahaan,
                    prs.kode as kode_perusahaan,
                    td.jml_ekor as jumlah,
                    od.harga,
                    (td.jml_ekor * od.harga) as total
                from 
                    (
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
                        od.no_order = td.no_order 
                left join
                    (
                        select 
                            rs.noreg, 
                            m.nama,
                            SUBSTRING(rs.noreg, 10, 2) as kandang
                        from rdim_submit rs 
                        right join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                rs.nim = mm.nim
                        right join
                            mitra m 
                            on
                                mm.mitra = m.id
                    ) rs
                    on
                        rs.noreg = od.noreg
                left join
                    (
                        select p1.* from pelanggan p1
                        right join
                            (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' and mstatus = 1 group by nomor) p2
                            on
                                p1.id = p2.id
                    ) supl
                    on
                        supl.nomor = od.supplier 
                left join
                    (
                        select b1.* from barang b1
                        right join
                            (select max(id) as id, kode from barang b group by kode) b2
                            on
                                b1.id = b2.id
                    ) brg
                    on
                        brg.kode = od.item
                left join
                    (
                        select p.id, p.kode, p.perusahaan as nama from perusahaan p
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) _p
                            on
                                _p.id = p.id
                    ) prs
                    on
                        prs.kode = od.perusahaan 
            ) data
            where
                data.datang between '".$start_date."' and '".$end_date."'
                ".$sql_perusahaan."
                ".$sql_unit."
            group by
                data.noreg,
                data.no_order,
                data.no_sj,
                data.datang,
                data.nama,
                data.kandang,
                data.unit,
                data.supplier,
                data.barang,
                data.nama_perusahaan,
                data.kode_perusahaan,
                data.jumlah,
                data.harga,
                data.total
            order by
                data.datang asc,
                data.nama asc
        ";
        $d_beli = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_beli->count() ) {
            $data = $d_beli->toArray();
        }

        return $data;
    }

    public function getDataPakan( $start_date, $end_date, $unit, $perusahaan )
    {
        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit .= "and data.unit in ('".implode("', '", $unit)."')";
        }

        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan .= "and data.kode_perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.no_order,
                data.no_sj,
                data.datang,
                data.nama,
                data.kandang,
                data.unit,
                data.supplier,
                data.barang,
                data.nama_perusahaan,
                data.jumlah,
                data.harga,
                data.total
            from
            (
                select
                    op.no_order,
                    kp.no_sj,
                    tp.tgl_terima as datang,
                    null as nama,
                    null as kandang,
                    SUBSTRING(op.no_order, 5, 3)  as unit,
                    supl.nama as supplier,
                    brg.nama as barang,
                    prs.nama as nama_perusahaan,
                    prs.kode as kode_perusahaan,
                    dtp.jumlah,
                    op.harga,
                    dtp.jumlah * op.harga as total
                from det_terima_pakan dtp 
                right join
                    terima_pakan tp 
                    on
                        dtp.id_header = tp.id
                right join
                    kirim_pakan kp 
                    on
                        tp.id_kirim_pakan = kp.id
                right join
                    (
                        select opd.*, _op.no_order, _op.tgl_trans, _op.rcn_kirim, _op.supplier from order_pakan_detail opd 
                        right join
                            order_pakan _op
                            on
                                opd.id_header = _op.id
                    ) op 
                    on
                        op.no_order = kp.no_order and
                        op.barang = dtp.item 
                right join
                    (
                        select p1.* from pelanggan p1
                        right join
                            (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' and mstatus = 1 group by nomor) p2
                            on
                                p1.id = p2.id
                    ) supl
                    on
                        supl.nomor = op.supplier 
                right join
                    (
                        select b1.* from barang b1
                        right join
                            (select max(id) as id, kode from barang b group by kode) b2
                            on
                                b1.id = b2.id
                    ) brg
                    on
                        brg.kode = dtp.item 
                right join
                    (
                        select p.id, p.kode, p.perusahaan as nama from perusahaan p
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) _p
                            on
                                _p.id = p.id
                    ) prs
                    on
                        prs.kode = op.perusahaan
            ) data
            where
                data.datang is not null and
                data.datang between '".$start_date."' and '".$end_date."'
                ".$sql_perusahaan."
                ".$sql_unit."
            group by
                data.no_order,
                data.no_sj,
                data.datang,
                data.nama,
                data.kandang,
                data.unit,
                data.supplier,
                data.barang,
                data.nama_perusahaan,
                data.kode_perusahaan,
                data.jumlah,
                data.harga,
                data.total
            order by
                data.datang asc
        ";
        $d_beli = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_beli->count() ) {
            $data = $d_beli->toArray();
        }

        return $data;
    }

    public function getDataVoadip( $start_date, $end_date, $unit, $perusahaan )
    {
        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit .= "and data.unit in ('".implode("', '", $unit)."')";
        }

        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan .= "and data.kode_perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.no_order,
                data.no_sj,
                data.datang,
                data.nama,
                data.kandang,
                data.unit,
                data.supplier,
                data.barang,
                data.nama_perusahaan,
                data.jumlah,
                data.harga,
                data.total
            from
            (
                select
                    ov.no_order,
                    kv.no_sj,
                    tv.tgl_terima as datang,
                    null as nama,
                    null as kandang,
                    SUBSTRING(ov.no_order, 5, 3)  as unit,
                    supl.nama as supplier,
                    brg.nama as barang,
                    prs.nama as nama_perusahaan,
                    prs.kode as kode_perusahaan,
                    dtv.jumlah,
                    ov.harga,
                    dtv.jumlah * ov.harga as total
                from det_terima_voadip dtv 
                left join
                    terima_voadip tv 
                    on
                        dtv.id_header = tv.id
                left join
                    kirim_voadip kv 
                    on
                        tv.id_kirim_voadip = kv.id
                left join
                    (
                        select ovd.*, _ov.no_order, _ov.supplier from order_voadip_detail ovd 
                        right join
                            (
                                select ov1.* from order_voadip ov1
                                right join
                                    (select max(id) as id, no_order from order_voadip group by no_order) ov2
                                    on
                                        ov1.id = ov2.id
                            ) _ov
                            on
                                ovd.id_order = _ov.id
                    ) ov 
                    on
                        ov.no_order = kv.no_order and
                        ov.kode_barang = dtv.item 
                left join
                    (
                        select p1.* from pelanggan p1
                        right join
                            (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' and mstatus = 1 group by nomor) p2
                            on
                                p1.id = p2.id
                    ) supl
                    on
                        supl.nomor = ov.supplier 
                left join
                    (
                        select b1.* from barang b1
                        right join
                            (select max(id) as id, kode from barang b group by kode) b2
                            on
                                b1.id = b2.id
                    ) brg
                    on
                        brg.kode = dtv.item 
                left join
                    (
                        select p.id, p.kode, p.perusahaan as nama from perusahaan p
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) _p
                            on
                                _p.id = p.id
                    ) prs
                    on
                        prs.kode = ov.perusahaan
                where
                    kv.jenis_kirim = 'opks'
            ) data
            where
                data.datang is not null and
                data.datang between '".$start_date."' and '".$end_date."'
                ".$sql_perusahaan."
                ".$sql_unit."
            group by
                data.no_order,
                data.no_sj,
                data.datang,
                data.nama,
                data.kandang,
                data.unit,
                data.supplier,
                data.barang,
                data.nama_perusahaan,
                data.kode_perusahaan,
                data.jumlah,
                data.harga,
                data.total
            order by
                data.datang asc
        ";
        $d_beli = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_beli->count() ) {
            $data = $d_beli->toArray();
        }

        return $data;
    }

    public function getLists()
    {
        $params = $this->input->post('params');

        try {
            $jenis = $params['jenis'];
            $start_date = $params['start_date'].' 00:00:00.000';
            $end_date = $params['end_date'].' 23:59:59.999';
            $unit = $params['unit'];
            $perusahaan = $params['perusahaan'];

            $data = null;
            if ( stristr($jenis, 'doc') !== FALSE ) {
                $data = $this->getDataDoc( $start_date, $end_date, $unit, $perusahaan );
            } else if ( stristr($jenis, 'pakan') !== FALSE ) {
                $data = $this->getDataPakan( $start_date, $end_date, $unit, $perusahaan );
            } else if ( stristr($jenis, 'voadip') !== FALSE ) {
                $data = $this->getDataVoadip( $start_date, $end_date, $unit, $perusahaan );
            }

            $content['data'] = $data;

            $html = $this->load->view($this->pathView.'list', $content, TRUE);

            $this->result['status'] = 1;
            $this->result['html'] = $html;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
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

        $this->load->helper('download');
        force_download('export_excel/'.$filename, NULL);
    }

    public function exportExcel($params_encrypt)
    {
        $params = json_decode( exDecrypt($params_encrypt), true );

        $jenis = $params['jenis'];
        $start_date = $params['start_date'].' 00:00:00.000';
        $end_date = $params['end_date'].' 23:59:59.999';
        $unit = $params['unit'];
        $perusahaan = $params['perusahaan'];

        $data = null;
        if ( stristr($jenis, 'doc') !== FALSE ) {
            $data = $this->getDataDoc( $start_date, $end_date, $unit, $perusahaan );
        } else if ( stristr($jenis, 'pakan') !== FALSE ) {
            $data = $this->getDataPakan( $start_date, $end_date, $unit, $perusahaan );
        } else if ( stristr($jenis, 'voadip') !== FALSE ) {
            $data = $this->getDataVoadip( $start_date, $end_date, $unit, $perusahaan );
        }
            
        $filename = 'PEMBELIAN_'.strtoupper($jenis).'_'.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']).'.xls';

        $arr_header = array('Tanggal', 'No. SJ', 'Periode', 'Unit', 'Supplier', 'Jenis', 'DO', 'Ekor / Tonase', 'Harga Beli', 'Total');
        $arr_column = null;
        if ( !empty($data) ) {
            $idx = 0;
            foreach ($data as $key => $value) {
                $arr_column[ $idx ] = array(
                    'Tanggal' => array('value' => $value['datang'], 'data_type' => 'date'),
                    'No. SJ' => array('value' => $value['no_sj'], 'data_type' => 'nik'),
                    'Periode' => array('value' => '-', 'data_type' => 'string'),
                    'Unit' => array('value' => $value['unit'], 'data_type' => 'string'),
                    'Supplier' => array('value' => $value['supplier'], 'data_type' => 'string'),
                    'Jenis' => array('value' => $value['barang'], 'data_type' => 'string'),
                    'DO' => array('value' => $value['nama_perusahaan'], 'data_type' => 'string'),
                    'Ekor / Tonase' => array('value' => $value['jumlah'], 'data_type' => 'decimal2'),
                    'Harga Beli' => array('value' => $value['harga'], 'data_type' => 'decimal2'),
                    'Total' => array('value' => $value['total'], 'data_type' => 'decimal2'),
                );

                $idx++;
            }
        }

        $this->exportExcelUsingSpreadSheet( $filename, $arr_header, $arr_column );
    }
}