<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class SisaTagihanBakulPerTanggal extends Public_Controller {

    private $pathView = 'report/sisa_tagihan_bakul_per_tanggal/';
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
                "assets/report/sisa_tagihan_bakul_per_tanggal/js/sisa-tagihan-bakul-per-tanggal.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/sisa_tagihan_bakul_per_tanggal/css/sisa-tagihan-bakul-per-tanggal.css",
            ));

            $data = $this->includes;

            $content['pelanggan'] = $this->getPelanggan();
            $content['unit'] = $this->getUnit();
            $content['perusahaan'] = $this->getPerusahaan();

            $content['akses'] = $akses;
            $content['title_menu'] = 'Laporan Sisa Tagihan Bakul Per Tanggal';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getPelanggan()
    {
        $m_plg = new \Model\Storage\Pelanggan_model();

        $sql = "
            select 
                p.*,
                REPLACE(REPLACE(l_kab.nama, 'Kota ', ''), 'Kab ', '') as nama_unit
            from pelanggan p
            right join
                (select max(id) as id, nomor from pelanggan where tipe = 'pelanggan' group by nomor) p1
                on
                    p1.id = p.id
            left join
                (select * from lokasi l where jenis = 'KC') l_kec
                on
                    l_kec.id = p.alamat_kecamatan
            left join
                (select * from lokasi l where (jenis = 'KB' or jenis = 'KT')) l_kab
                on
                    l_kab.id = l_kec.induk
            where
                p.mstatus = 1
            order by
                REPLACE(REPLACE(l_kab.nama, 'Kota ', ''), 'Kab ', '') asc,
                p.nama asc
        ";

        $d_plg = $m_plg->hydrateRaw( $sql );

        $data = null;
        if ( $d_plg->count() > 0 ) {
            $data = $d_plg->toArray();
        }

        return $data;
    }

    public function getUnit()
    {
        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                w1.kode,
                REPLACE(REPLACE(w1.nama, 'Kab ', ''), 'Kota ', '') as nama
            from wilayah w1
            right join
                (select max(id) as id, kode from wilayah group by kode) w2
                on
                    w1.id = w2.id
            where
                w1.jenis = 'UN'
            order by
                REPLACE(REPLACE(w1.nama, 'Kab ', ''), 'Kota ', '') asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getPerusahaan()
    {
        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                p1.kode,
                p1.perusahaan as nama
            from perusahaan p1
            right join
                (select max(id) as id, kode from perusahaan group by kode) p2
                on
                    p1.id = p2.id
            order by
                p1.perusahaan asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getLists() {
        $params = $this->input->get('params');

        $pelanggan = $params['pelanggan'];
        $unit = $params['unit'];
        $perusahaan = $params['perusahaan'];
        $tanggal = $params['tanggal'];

        $data = $this->getData( $pelanggan, $unit, $perusahaan, $tanggal );

        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }

    public function getData( $pelanggan, $unit, $perusahaan, $tanggal ) {
        $data = null;

        $sql_pelanggan = null;
        if ( !in_array('all', $pelanggan) ) {
            $sql_pelanggan = "and plg.nomor in ('".implode("', '", $pelanggan)."')";
        }

        $sql_unit = null;
        if ( !in_array('all', $unit) ) {
            $sql_unit = "and w.kode in ('".implode("', '", $unit)."')";
        }

        $sql_perusahaan = null;
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan = "and prs.kode in ('".implode("', '", $perusahaan)."')";
        }

        // $sql = '';
        // if ( !empty($sql_pelanggan) || !empty($sql_unit) || !empty($sql_perusahaan) ) {
        //     if ( !empty($sql_pelanggan) ) {
        //         if ( !empty($sql) ) {
        //             $sql .= ' and '.$sql_pelanggan;
        //         } else {
        //             $sql .= 'where '.$sql_pelanggan;
        //         }
        //     }

        //     if ( !empty($sql_unit) ) {
        //         if ( !empty($sql) ) {
        //             $sql .= ' and '.$sql_unit;
        //         } else {
        //             $sql .= 'where '.$sql_unit;
        //         }
        //     }

        //     if ( !empty($sql_perusahaan) ) {
        //         if ( !empty($sql) ) {
        //             $sql .= ' and '.$sql_perusahaan;
        //         } else {
        //             $sql .= 'where '.$sql_perusahaan;
        //         }
        //     }
        // }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                drsi.*,
                plg.nama as nama_pelanggan,
                rdims.nama as nama_mitra,
                prs.perusahaan as nama_perusahaan,
                rdims.tgl_tutup as tgl_tutup_siklus,
                rs.tgl_panen as tgl_invoice,
                isnull(dpp.cn, 0) as cn,
                isnull(dpp.dn, 0) as dn,
                isnull(dpp.penyesuaian, 0) as penyesuaian,
                isnull(dpp.bayar, 0) as bayar,
                ((drsi.total+isnull(dpp.dn, 0))-(isnull(dpp.cn, 0)+isnull(dpp.penyesuaian, 0)+isnull(dpp.bayar, 0))) as sisa_tagihan
            from det_real_sj_inv drsi
            left join
                det_real_sj drs
                on
                    drsi.no_sj = drs.no_sj
            left join
                real_sj rs
                on
                    drs.id_header = rs.id
            left join
                (
                    select 
                        dpp.no_inv,
                        sum(dpp.cn) as cn, 
                        sum(dpp.dn) as dn,
                        sum(dpp.penyesuaian) as penyesuaian,
                        sum(dpp.tagihan - (dpp.penyesuaian+dpp.sisa_tagihan)) as bayar
                    from det_pembayaran_pelanggan dpp
                    left join
                        pembayaran_pelanggan pp
                        on
                            dpp.id_header = pp.id
                    where
                        pp.tgl_bayar <= '".$tanggal."'
                    group by 
                        dpp.no_inv
                ) dpp
                on
                    dpp.no_inv = drsi.no_inv
            left join
            	(
            		select rs.noreg, m.nama, m.nomor, m.perusahaan, ts.tgl_tutup from rdim_submit rs
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
                        tutup_siklus ts
                        on
                            ts.noreg = rs.noreg
            	) rdims
            	on
            		rdims.noreg = rs.noreg
            left join
                (
                    select p1.* from pelanggan p1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'pelanggan' group by nomor) p2
                        on
                            p1.id = p2.id
                ) plg
                on
                    plg.nomor = drs.no_pelanggan
            left join
                (
                    select prs1.* from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    rdims.perusahaan = prs.kode
            left join
                (
                    select
                        w1.kode,
                        REPLACE(REPLACE(w1.nama, 'Kab ', ''), 'Kota ', '') as nama
                    from wilayah w1
                    right join
                        (select max(id) as id, kode from wilayah group by kode) w2
                        on
                            w1.id = w2.id
                    where
                        w1.jenis = 'UN'
                ) w
                on
                    w.kode = SUBSTRING(drs.no_do, 4, 3)
            where
                rs.tgl_panen <= '".$tanggal."'
                ".$sql_pelanggan."
                ".$sql_unit."
                ".$sql_perusahaan."
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
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

    public function exportExcel($params_encrypt)
    {
        $params = json_decode( exDecrypt($params_encrypt), true );

        $pelanggan = $params['pelanggan'];
        $unit = $params['unit'];
        $perusahaan = $params['perusahaan'];
        $tanggal = $params['tanggal'];

        $data = $this->getData( $pelanggan, $unit, $perusahaan, $tanggal );

        $filename = "LAPORAN_SISA_TAGIHAN_BAKUL_PER_";
        $filename = $filename.str_replace('-', '', $tanggal).'.xls';

        $arr_header = array('Perusahaan', 'Bakul', 'Plasma', 'Tgl Tutup Siklus', 'Tanggal', 'No. DO', 'No. Nota', 'Total', 'Bayar', 'Sisa');
        $arr_column = null;
        if ( !empty($data) ) {
            $idx = 0;

            $total = 0;
            $bayar = 0;
            $sisa = 0;

            foreach ($data as $key => $value) {
                $arr_column[ $idx ] = array(
                    'Perusahaan' => array('value' => strtoupper($value['nama_perusahaan']), 'data_type' => 'string'),
                    'Bakul' => array('value' => strtoupper($value['nama_pelanggan']), 'data_type' => 'string'),
                    'Plasma' => array('value' => strtoupper($value['nama_mitra']), 'data_type' => 'string'),
                    'Tgl Tutup Siklus' => array('value' => $value['tgl_tutup_siklus'], 'data_type' => 'date'),
                    'Tanggal' => array('value' => $value['tgl_panen'], 'data_type' => 'date'),
                    'No. DO' => array('value' => $value['no_do'], 'data_type' => 'string'),
                    'No. Nota' => array('value' => !empty($value['no_nota']) ? $value['no_nota'] : '', 'data_type' => 'string'),
                    'Total' => array('value' => $value['total'], 'data_type' => 'decimal2'),
                    'Bayar' => array('value' => $value['bayar'], 'data_type' => 'decimal2'),
                    'Sisa' => array('value' => $value['sisa'], 'data_type' => 'decimal2')
                );

                $total += $value['total'];
                $bayar += $value['bayar'];
                $sisa += $value['sisa'];

                $idx++;
            }

            $arr_column[ $idx ] = array(
                'No. Nota' => array('value' => 'TOTAL', 'data_type' => 'string', 'colspan' => array('A', 'F'), 'text_style' => 'bold'),
                'Total' => array('value' => $total, 'data_type' => 'decimal2', 'text_style' => 'bold'),
                'Bayar' => array('value' => $bayar, 'data_type' => 'decimal2', 'text_style' => 'bold'),
                'Sisa' => array('value' => $sisa, 'data_type' => 'decimal2', 'text_style' => 'bold')
            );
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
                    
                    if ( isset($arr_column[ $i ][ $arr_header[ $j ] ]) ) {
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
    
                        if ( isset($data['text_style']) ) {
                            if ( $data['text_style'] == 'bold' ) {
                                $sheet->getStyle($huruf.$baris)->getFont()->setBold(true);
                            }
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
}