<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class FpOrtax extends Public_Controller {

    private $pathView = 'report/fp_ortax/';
    private $url;

    const NPWP_PENJUAL = '1000000002244403';
    const ID_TKU_PENJUAL = '1000000002244403000000';

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    public function index($segment=0)
    {
        $akses = hakAkses($this->url);
        // if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                "assets/report/fp_ortax/js/fp-ortax.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/fp_ortax/css/fp-ortax.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;

            $content['perusahaan'] = $this->getPerusahaan();
            $content['unit'] = $this->getUnit();

            $content['title_menu'] = 'Faktur Pajak (FP Ortax) - Penjualan LB';

            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        // } else {
        //     showErrorAkses();
        // }
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
            select p1.* from perusahaan p1
            right join
                (select max(id) as id, kode from perusahaan group by kode) p2
                on
                    p1.id = p2.id
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    /**************************************************************************************
     * DATA
     **************************************************************************************/
    /**
     * Ambil data penjualan LB per baris det_real_sj (grain detail), sudah termasuk
     * pemecahan alamat pembeli per komponen (jalan/rt/rw/kelurahan/kecamatan/kabupaten/propinsi)
     * supaya bisa disusun ulang mengikuti format kolom "Alamat Pembeli" template FP Ortax.
     */
    public function getDataFpOrtax( $params ) {
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];
        $tutup_siklus = $params['tutup_siklus'];

        $sql_tutup_siklus = "";
        if ( stristr($tutup_siklus, 'all') === false ) {
            if ( $tutup_siklus == '1' ) {
                $sql_tutup_siklus .= "and rhpp.tgl_tutup is not null";
            } else {
                $sql_tutup_siklus .= "and rhpp.tgl_tutup is null";
            }
        }

        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan .= "and rdim.perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit .= "and w.kode in ('".implode("', '", $unit)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.id,
                data.no_nota,
                data.tanggal_panen,
                data.npwp_bakul,
                data.nik_bakul,
                data.nama_bakul,
                data.alamat_jalan,
                data.alamat_rt,
                data.alamat_rw,
                data.alamat_kelurahan,
                data.kecamatan,
                data.kabupaten,
                data.propinsi,
                data.kode_barang,
                data.deskripsi_barang,
                data.kuantitas,
                data.harga_per_satuan_kuantitas
            from
            (
                select
                    drs.id,
                    drsi.no_inv as no_nota,
                    rs.tgl_panen as tanggal_panen,
                    cast(plg.npwp as varchar(100)) as npwp_bakul,
                    cast(plg.nik as varchar(100)) as nik_bakul,
                    UPPER(plg.nama) as nama_bakul,
                    plg.alamat_jalan as alamat_jalan,
                    plg.alamat_rt as alamat_rt,
                    plg.alamat_rw as alamat_rw,
                    UPPER(plg.alamat_kelurahan) as alamat_kelurahan,
                    UPPER(l.nama) as kecamatan,
                    UPPER(REPLACE(REPLACE(l_head.nama, 'Kab ', ''), 'Kota ', '')) as kabupaten,
                    UPPER(l_prov.nama) as propinsi,
                    UPPER(drs.jenis_ayam) as kode_barang,
                    CASE
                        WHEN drs.jenis_ayam = 'n' THEN 'NORMAL'
                        WHEN drs.jenis_ayam = 'a' THEN 'AFKIR'
                        WHEN drs.jenis_ayam = 's' THEN 'SPESIAL'
                    END as deskripsi_barang,
                    drs.tonase as kuantitas,
                    drs.harga as harga_per_satuan_kuantitas
                from det_real_sj drs
                left join
                    det_real_sj_inv drsi
                    on
                        drsi.no_sj = drs.no_sj
                left join
                    (
                        select rs1.* from real_sj rs1
                        right join
                            (select max(id) as id, noreg, tgl_panen from real_sj group by noreg, tgl_panen) rs2
                            on
                                rs1.id = rs2.id
                    ) rs
                    on
                        drs.id_header = rs.id
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
                    lokasi l
                    on
                        plg.alamat_kecamatan = l.id
                left join
                    lokasi l_head
                    on
                        l.induk = l_head.id
                left join
                    lokasi l_prov
                    on
                        l_head.induk = l_prov.id
                left join
                    (
                        select data.noreg, data.tgl_docin, max(tgl_tutup) as tgl_tutup
                        from
                        (
                            select ts.noreg, ts.tgl_docin, ts.tgl_tutup from tutup_siklus ts

                            union all

                            select rgn.noreg, rgn.tgl_docin, rgh.tgl_submit as tgl_tutup from (
                                select rgn1.* from rhpp_group_noreg rgn1
                                right join
                                    (select max(id) as id, noreg from rhpp_group_noreg group by noreg) rgn2
                                    on
                                        rgn1.id = rgn2.id
                            ) rgn
                            left join
                                rhpp_group rg
                                on
                                    rgn.id_header = rg.id
                            left join
                                rhpp_group_header rgh
                                on
                                    rg.id_header = rgh.id
                            group by
                                rgn.noreg, rgn.tgl_docin, rgh.tgl_submit
                        ) data
                        group by
                            data.noreg, data.tgl_docin
                    ) rhpp
                    on
                        rs.noreg = rhpp.noreg
                left join
                    (
                        select r.noreg, r.nim, m.perusahaan from rdim_submit r
                        left join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                r.nim = mm.nim
                        left join
                            mitra m
                            on
                                m.id = mm.mitra
                    ) rdim
                    on
                        rs.noreg = rdim.noreg
                left join
                    wilayah w
                    on
                        rs.id_unit = w.id
                where
                    drs.tonase > 0 and
                    drs.harga > 0 and
                    drsi.no_inv is not null and
                    drsi.no_inv <> '' and
                    rs.tgl_panen between '".$start_date."' and '".$end_date."'
                    ".$sql_tutup_siklus."
                    ".$sql_perusahaan."
                    ".$sql_unit."
            ) data
            order by
                data.tanggal_panen asc,
                data.no_nota asc,
                data.nama_bakul asc,
                data.id asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    /**
     * Kelompokkan data per-baris det_real_sj menjadi per-invoice (Faktur),
     * masing-masing dengan daftar detail barang di dalamnya (DetailFaktur).
     */
    public function groupByInvoice( $data ) {
        $grouped = array();
        if ( !empty($data) ) {
            foreach ($data as $row) {
                $grouped[ $row['no_nota'] ][] = $row;
            }
        }

        $invoices = array();
        $baris = 1;
        foreach ($grouped as $no_nota => $rows) {
            $invoices[] = array(
                'baris' => $baris,
                'no_nota' => $no_nota,
                'header' => $rows[0],
                'details' => $rows,
                'identitas' => $this->getIdentitasPembeli( $rows[0] ),
            );
            $baris++;
        }

        return $invoices;
    }

    public function stripPrefixBadanUsaha( $nama ) {
        return trim(preg_replace('/^(PT|CV|UD|PD)\.?\s+/i', '', trim($nama)));
    }

    public function buildAlamatPembeli( $row ) {
        $rt = str_pad((int)$row['alamat_rt'], 3, '0', STR_PAD_LEFT);
        $rw = str_pad((int)$row['alamat_rw'], 3, '0', STR_PAD_LEFT);

        $parts = array(
            trim($row['alamat_jalan']),
            'RT'.$rt.'/RW'.$rw,
            $row['alamat_kelurahan'],
            $row['kecamatan'],
            'KAB. '.$row['kabupaten'],
            $row['propinsi'],
        );

        return strtoupper(implode(', ', array_filter($parts, function($p) { return !empty($p); })));
    }

    public function getIdentitasPembeli( $row ) {
        $npwp = trim((string)$row['npwp_bakul']);
        $nik = trim((string)$row['nik_bakul']);

        // Sebagian bakul disimpan dengan NPWP placeholder isi nol semua (mis. '0000000000000000'),
        // itu bukan NPWP asli. Sejak kebijakan NIK=NPWP (pemadanan NIK-NPWP), individu tanpa NPWP
        // terdaftar tetap dipakaikan Jenis ID TIN dengan nomor = NIK-nya (dicek ke sample FP Ortax
        // asli: dari 5763 faktur, 5399 TIN vs cuma 364 National ID -- artinya default-nya TIN+NIK,
        // bukan National ID). "National ID" murni cuma valid utk kasus yg benar2 tidak ada NIK/NPWP
        // sama sekali, yang di data kita nyaris tidak pernah terjadi.
        $has_npwp = !empty($npwp) && !preg_match('/^0+$/', $npwp);
        $id_value = $has_npwp ? $npwp : $nik;

        if ( !empty($id_value) ) {
            return array(
                'npwp_nik' => $id_value,
                'jenis_id' => 'TIN',
                'nomor_dokumen' => '-',
                'id_tku' => $id_value.'000000',
            );
        }

        return array(
            'npwp_nik' => '0000000000000000',
            'jenis_id' => 'National ID',
            'nomor_dokumen' => '-',
            'id_tku' => '0000000000000000000000',
        );
    }

    /**************************************************************************************
     * AJAX
     **************************************************************************************/
    public function getLists() {
        $params = $this->input->get('params');

        $data = $this->getDataFpOrtax( $params );
        $invoices = $this->groupByInvoice( $data );

        $content['invoices'] = $invoices;
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

    /**************************************************************************************
     * EXPORT
     **************************************************************************************/
    public function exportExcel($params_encrypt)
    {
        $params = json_decode( exDecrypt($params_encrypt), true );

        $data = $this->getDataFpOrtax( $params );
        $invoices = $this->groupByInvoice( $data );

        $filename = "FP_ORTAX_".str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']).'.xlsx';

        $this->exportExcelFpOrtax( $filename, $invoices );
    }

    public function exportExcelFpOrtax( $file_name, $invoices ) {
        $spreadsheet = new Spreadsheet();

        /* ===== Sheet Faktur ===== */
        $sheetFaktur = $spreadsheet->getActiveSheet();
        $sheetFaktur->setTitle('Faktur');

        $sheetFaktur->setCellValue('A1', 'NPWP Penjual');
        $sheetFaktur->setCellValueExplicit('C1', self::NPWP_PENJUAL, DataType::TYPE_STRING);
        $sheetFaktur->getStyle('A1')->applyFromArray(array('font' => array('bold' => true)));

        $headerFaktur = array('Baris', 'Tanggal Faktur', 'Jenis Faktur', 'Kode Transaksi', 'Keterangan Tambahan', 'Dokumen Pendukung', 'Referensi', 'Cap Fasilitas', 'ID TKU Penjual', 'NPWP/NIK Pembeli', 'Jenis ID Pembeli', 'Negara Pembeli', 'Nomor Dokumen Pembeli', 'Nama Pembeli', 'Alamat Pembeli', 'Email Pembeli', 'ID TKU Pembeli');
        // Kolom yang di file asli diformat sebagai teks ('@') walau isinya kode angka,
        // supaya leading zero (mis. kode '08', '10') tidak hilang saat dibuka di Excel.
        $textFormatColsFaktur = array('D', 'E', 'F', 'G', 'H', 'I', 'K', 'L', 'M');

        for ($i=0; $i < count($headerFaktur); $i++) {
            $huruf = toAlpha($i+1);
            $sheetFaktur->setCellValue($huruf.'3', $headerFaktur[$i]);
            $sheetFaktur->getStyle($huruf.'3')->applyFromArray(array(
                'font' => array('bold' => true),
                'borders' => array('top' => array('borderStyle' => Border::BORDER_THIN)),
            ));
        }

        $baris = 4;
        foreach ($invoices as $inv) {
            $header = $inv['header'];
            $identitas = $inv['identitas'];

            $sheetFaktur->setCellValue('A'.$baris, $inv['baris']);

            $dt = Date::PHPToExcel(DateTime::createFromFormat('!Y-m-d', substr($header['tanggal_panen'], 0, 10)));
            $sheetFaktur->setCellValue('B'.$baris, $dt);

            $sheetFaktur->setCellValue('C'.$baris, 'Normal');
            $sheetFaktur->setCellValueExplicit('D'.$baris, '08', DataType::TYPE_STRING);
            $sheetFaktur->setCellValueExplicit('E'.$baris, '10', DataType::TYPE_STRING);
            $sheetFaktur->setCellValue('F'.$baris, '(Ternak dan Unggas Hidup, Karkas, Nonkarkas, Jeroan)');
            $sheetFaktur->setCellValue('G'.$baris, $inv['no_nota']);
            $sheetFaktur->setCellValueExplicit('H'.$baris, '10', DataType::TYPE_STRING);
            $sheetFaktur->setCellValueExplicit('I'.$baris, self::ID_TKU_PENJUAL, DataType::TYPE_STRING);
            $sheetFaktur->setCellValueExplicit('J'.$baris, $identitas['npwp_nik'], DataType::TYPE_STRING);
            $sheetFaktur->setCellValue('K'.$baris, $identitas['jenis_id']);
            $sheetFaktur->setCellValue('L'.$baris, 'IDN');
            $sheetFaktur->setCellValueExplicit('M'.$baris, $identitas['nomor_dokumen'], DataType::TYPE_STRING);
            $sheetFaktur->setCellValue('N'.$baris, $this->stripPrefixBadanUsaha($header['nama_bakul']));
            $sheetFaktur->setCellValue('O'.$baris, $this->buildAlamatPembeli($header));
            // Email Pembeli sengaja dibiarkan kosong/null (bukan string kosong) supaya persis sama dengan sample.
            $sheetFaktur->setCellValueExplicit('Q'.$baris, $identitas['id_tku'], DataType::TYPE_STRING);

            $baris++;
        }

        $lastRowFaktur = $baris - 1;
        $sheetFaktur->setAutoFilter('A3:Q'.$lastRowFaktur);

        // Format per-kolom diterapkan sekaligus atas satu range (bukan per-cell dalam loop)
        // karena getStyle() per-cell jauh lebih lambat pada ribuan baris.
        $sheetFaktur->getStyle('B4:B'.$lastRowFaktur)->getNumberFormat()->setFormatCode('dd/mm/yyyy;@');
        foreach ($textFormatColsFaktur as $col) {
            $sheetFaktur->getStyle($col.'4:'.$col.$lastRowFaktur)->getNumberFormat()->setFormatCode('@');
        }

        $colWidthsFaktur = array('B'=>16.18,'C'=>11,'D'=>4,'E'=>10.82,'F'=>15.45,'G'=>12.54,'H'=>11.73,'I'=>24.27,'J'=>21.18,'K'=>15.27,'L'=>6,'M'=>24.45,'N'=>27.45,'O'=>12,'P'=>4.54,'Q'=>24.27);
        foreach ($colWidthsFaktur as $col => $width) {
            $sheetFaktur->getColumnDimension($col)->setWidth($width);
        }

        /* ===== Sheet DetailFaktur ===== */
        $sheetDetail = $spreadsheet->createSheet();
        $sheetDetail->setTitle('DetailFaktur');

        $headerDetail = array('Baris', 'Barang/Jasa', 'Kode Barang Jasa', 'Nama Barang/Jasa', 'Nama Satuan Ukur', 'Harga Satuan', 'Jumlah Barang Jasa', 'Total Diskon', 'DPP', 'DPP Nilai Lain', 'Tarif PPN', 'PPN', 'Tarif PPnBM', 'PPnBM');
        for ($i=0; $i < count($headerDetail); $i++) {
            $huruf = toAlpha($i+1);
            $sheetDetail->setCellValue($huruf.'1', $headerDetail[$i]);
            $sheetDetail->getStyle($huruf.'1')->applyFromArray(array('font' => array('bold' => true)));
        }

        $barisDetail = 2;
        foreach ($invoices as $inv) {
            foreach ($inv['details'] as $det) {
                $harga = (float)$det['harga_per_satuan_kuantitas'];
                $kuantitas = (float)$det['kuantitas'];
                $dpp = $harga * $kuantitas;
                $dpp_nilai_lain = $dpp * 11 / 12;
                $ppn = round($dpp_nilai_lain * 0.12);

                $sheetDetail->setCellValue('A'.$barisDetail, $inv['baris']);
                $sheetDetail->setCellValue('B'.$barisDetail, 'A');
                $sheetDetail->setCellValueExplicit('C'.$barisDetail, '000000', DataType::TYPE_STRING);
                $sheetDetail->setCellValue('D'.$barisDetail, 'AYAM '.$det['deskripsi_barang']);
                $sheetDetail->setCellValueExplicit('E'.$barisDetail, 'UM.0033', DataType::TYPE_STRING);
                $sheetDetail->setCellValue('F'.$barisDetail, $harga);
                $sheetDetail->setCellValue('G'.$barisDetail, $kuantitas);
                $sheetDetail->setCellValue('H'.$barisDetail, 0);
                $sheetDetail->setCellValue('I'.$barisDetail, $dpp);
                $sheetDetail->setCellValue('J'.$barisDetail, $dpp_nilai_lain);
                $sheetDetail->setCellValue('K'.$barisDetail, 12);
                $sheetDetail->setCellValue('L'.$barisDetail, $ppn);
                $sheetDetail->setCellValue('M'.$barisDetail, 0);
                $sheetDetail->setCellValue('N'.$barisDetail, 0);

                $barisDetail++;
            }
        }

        $lastRowDetail = $barisDetail - 1;
        $sheetDetail->setAutoFilter('A1:N'.$lastRowDetail);

        // Di file asli kolom numerik F-L diformat teks ('@') & M-N pakai '#,##0'.
        // Diterapkan sekaligus per-range (bukan per-cell) karena jauh lebih cepat pada ribuan baris.
        foreach (array('F','G','H','I','J','K','L') as $col) {
            $sheetDetail->getStyle($col.'2:'.$col.$lastRowDetail)->getNumberFormat()->setFormatCode('@');
        }
        foreach (array('M','N') as $col) {
            $sheetDetail->getStyle($col.'2:'.$col.$lastRowDetail)->getNumberFormat()->setFormatCode('#,##0');
        }

        $colWidthsDetail = array('C'=>16.82,'D'=>17.27,'E'=>17.45,'F'=>12.45,'G'=>16,'H'=>11.82,'I'=>19.54,'J'=>13.27,'L'=>11.82,'M'=>11.73);
        foreach ($colWidthsDetail as $col => $width) {
            $sheetDetail->getColumnDimension($col)->setWidth($width);
        }

        // File asli aktif dibuka di sheet DetailFaktur, bukan Faktur.
        $spreadsheet->setActiveSheetIndex(1);

        $writer = new Xlsx($spreadsheet);
        $writer->save('export_excel/'.$file_name);

        $this->load->helper('download');
        force_download('export_excel/'.$file_name, NULL);
    }
}
