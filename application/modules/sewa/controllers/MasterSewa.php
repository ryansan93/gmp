<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use Dompdf\Dompdf;

class MasterSewa extends Public_Controller {

    private $pathView = 'sewa/master_sewa/';
    private $url;
    private $hakAkses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->hakAkses = hakAkses($this->url);
    }

    public function index($segment=0)
    {

        if ( $this->hakAkses['a_view'] == 1 ) {

            $this->add_external_js(array(
                "assets/jquery/easy-autocomplete/jquery.easy-autocomplete.min.js",
                "assets/select2/js/select2.min.js",
                "assets/sewa/master_sewa/js/master_sewa.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/sewa/master_sewa/css/master_sewa.css",
            ));

            $data = $this->includes;
            $content['akses']       = $this->hakAkses;
            $content['title_panel'] = 'Master Sewa';
            $content['jenis_sewa']  = $this->get_jenis_sewa_list();
            $content['supplier']    = $this->get_supplier_list();
            $data['title_menu']     = 'Master Sewa';

            $data['view'] = $this->load->view($this->pathView . 'v_index', $content, TRUE);
            $this->load->view($this->template, $data);

        } else {
            showErrorAkses();
        }
    }

    public function list_data()
    {
        $akses              = hakAkses($this->url);
        $m_sewa             = new \Model\Storage\MasterSewa_model();
        $jenisSewa          = trim($this->input->post('jenis_sewa'));
        $tanggalMulai       = trim($this->input->post('tanggal_mulai'));
        $search             = trim($this->input->post('search'));
        $supplier           = trim($this->input->post('supplier'));
        
        $query = $m_sewa
        ->select('ms_sewa.*', 'ms_jenis_sewa.nama_jenis_sewa', 'p.nama as nama_supplier')
        ->leftJoin('ms_jenis_sewa', 'ms_jenis_sewa.kode_jenis_sewa', '=', 'ms_sewa.jenis_sewa')
        ->leftJoin('pelanggan as p', function($join) {
            $join->on('p.nomor', '=', 'ms_sewa.no_supplier')
                 ->where('p.tipe', '=', 'supplier')
                 ->where('p.mstatus', '=', 1);
        });

        if ( !empty($jenisSewa) ) {
            $query->where('ms_sewa.jenis_sewa', $jenisSewa);
        }

        if ( !empty($tanggalMulai) ) {
            $query->whereRaw('CAST(ms_sewa.tanggal_mulai AS DATE) = ?', [$tanggalMulai]);
        }

        if ( !empty($supplier) ) {
            $query->whereRaw('LOWER(LTRIM(RTRIM(ms_sewa.no_supplier))) = ?', [strtolower(trim($supplier))]);
        }

        if ( !empty($search) ) {
            $like = '%' . $search . '%';
            $query->where(function($q) use ($like) {
                $q->whereRaw('LOWER(LTRIM(RTRIM(ms_sewa.no_sewa))) LIKE ?', [strtolower($like)])
                  ->orWhereRaw('LOWER(LTRIM(RTRIM(ms_sewa.nama_sewa))) LIKE ?', [strtolower($like)])
                  ->orWhereRaw('LOWER(LTRIM(RTRIM(ms_sewa.no_kontrak))) LIKE ?', [strtolower($like)])
                  ->orWhereRaw('LOWER(LTRIM(RTRIM(p.nama))) LIKE ?', [strtolower($like)]);
            });
        }

        $d_sewa = $query->orderBy('ms_sewa.id', 'desc')->get()->toArray();

        // cetak_r($d_sewa);die;

        $content['akses']   = $akses;
        $content['list']    = $d_sewa;
        $html = $this->load->view($this->pathView . 'v_list', $content, TRUE);

        echo $html;
    }

    private function get_jenis_sewa_list()
    {
        $m_jenis = new \Model\Storage\MasterJenisSewa_model();
        return $m_jenis->orderBy('id', 'asc')->get()->toArray();
    }

    public function add_form()
    {
        $data['data'] = null;
        $data['jenis_sewa'] = $this->get_jenis_sewa_list();
        $data['supplier']   = $this->get_supplier_list();
        $this->load->view($this->pathView . 'v_form', $data);
    }

    public function get_supplier_list()
    {
        $m_conf     = new \Model\Storage\Conf();
        $sql = "
            select * from pelanggan
            where
                tipe = 'supplier' and
                mstatus = 1 and
                LOWER(LTRIM(RTRIM(kategori_supplier))) = 'sewa'
            order by nama asc
        ";

        $d_conf     = $m_conf->hydrateRaw( $sql );
        
        $data       = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function edit_form()
    {
        $id = $this->input->get('id');

        $m_sewa = new \Model\Storage\MasterSewa_model();
        $d_sewa = $m_sewa->where('id', $id)->first();

        $data['data'] = $d_sewa;
        $data['jenis_sewa'] = $this->get_jenis_sewa_list();
        $data['supplier'] = $this->get_supplier_list();
        $this->load->view($this->pathView . 'v_form', $data);
    }

    private function generateNoSewa($jenis_sewa, $tanggal_mulai = null, $excludeId = null)
    {
        $jenis = strtoupper(trim($jenis_sewa));
        if (empty($jenis)) {
            return null;
        }

        $year = !empty($tanggal_mulai) ? date('Y', strtotime($tanggal_mulai)) : date('Y');
        $month = !empty($tanggal_mulai) ? date('m', strtotime($tanggal_mulai)) : date('m');

        $m_sewa = new \Model\Storage\MasterSewa_model();
        $rows = $m_sewa
            ->whereRaw('UPPER(jenis_sewa) = ?', [$jenis])
            ->get();

        $lastSequence = 0;
        $hasExistingNoSewa = false;

        foreach ($rows as $row) {
            if (!empty($excludeId) && (int) $row->id === (int) $excludeId) {
                continue;
            }

            $no = trim($row->no_sewa);
            if (empty($no)) {
                continue;
            }

            if (preg_match('/^SW\/' . preg_quote($jenis, '/') . '\/(\d{4})\/(\d{2})\/(\d{4})$/i', $no, $parts)) {
                if ((int) $parts[1] === (int) $year && (int) $parts[2] === (int) $month) {
                    $hasExistingNoSewa = true;
                    $sequence = (int) $parts[3];
                    if ($sequence > $lastSequence) {
                        $lastSequence = $sequence;
                    }
                }
            }
        }

        if ($hasExistingNoSewa) {
            $sequence = $lastSequence + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('SW/%s/%s/%s/%04d', $jenis, $year, $month, $sequence);
    }

    private function syncAmortisasi($noSewa, $nominalSewa, $durasi)
    {
        if (empty($noSewa)) {
            return;
        }

        $amortisasi = new \Model\Storage\AmortisasiJadwal_model();
        $amortisasi->where('kode_transaksi', $noSewa)->delete();

        $durasi = (int) $durasi;
        if ($durasi <= 0) {
            return;
        }

        $nilaiPerBulan = (float) $nominalSewa / $durasi;

        for ($i = 1; $i <= $durasi; $i++) {
            $row = new \Model\Storage\AmortisasiJadwal_model();
            $row->kode_amortisasi = $noSewa . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $row->kode_transaksi  = $noSewa;
            $row->nilai           = $nilaiPerBulan;
            $row->status          = 0;
            $row->save();
        }
    }

    public function save_data()
    {
        $params = $this->input->post('params');

        try {
            $m_sewa = new \Model\Storage\MasterSewa_model();

            $namaSewa       = trim($params['nama_sewa']);
            $noKontrak      = trim($params['no_kontrak']);
            $jenisSewa      = strtoupper(trim($params['jenis_sewa']));
            $tanggalMulai   = trim($params['tanggal_mulai']);
            $noSupplier     = trim($params['no_supplier']);

            if ( empty($namaSewa) || empty($noKontrak) || empty($jenisSewa) || empty($tanggalMulai) ) {
                $this->result['message'] = 'Nama sewa, nomor kontrak, jenis sewa, dan tanggal mulai wajib diisi.';
            } else {
                $noSewa = $this->generateNoSewa($jenisSewa, $tanggalMulai);

                // Insert MS Sewa
                    $m_sewa->no_sewa        = $noSewa;
                    $m_sewa->nama_sewa      = $namaSewa;
                    $m_sewa->no_kontrak     = $noKontrak;
                    $m_sewa->jumlah_bulan   = !empty($params['jumlah_bulan']) ? (int) $params['jumlah_bulan'] : 0;
                    $m_sewa->jumlah_siklus  = !empty($params['jumlah_siklus']) ? (int) $params['jumlah_siklus'] : 0;
                    $m_sewa->jenis_sewa     = $jenisSewa;
                    $m_sewa->tanggal_mulai  = $tanggalMulai;
                    $m_sewa->no_supplier    = $noSupplier;
                    $m_sewa->nominal_sewa   = !empty($params['nominal_sewa']) ? str_replace('.', '', $params['nominal_sewa']) : 0;
                    $m_sewa->save();
                // End Insert MS Sewa

                // Insert Amortisasi
                    $nominalSewa = $m_sewa->nominal_sewa;
                    $durasi = $m_sewa->jumlah_bulan > 0 ? $m_sewa->jumlah_bulan : $m_sewa->jumlah_siklus;
                    $this->syncAmortisasi($noSewa, $nominalSewa, $durasi);
                // End Insert Amortisasi

                // Insert log event
                    $id            = $m_sewa->id;
                    $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run('base/event/save', $m_sewa, $deskripsi_log, null, $id, $m_sewa);
                // End Insert log event

                $this->result['status'] = 1;
                $this->result['message'] = 'Data berhasil disimpan';
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit_data()
    {
        $params = $this->input->post('params');

        try {
            $m_sewa = new \Model\Storage\MasterSewa_model();
            $namaSewa = trim($params['nama_sewa']);
            $noKontrak = trim($params['no_kontrak']);
            $jenisSewa = strtoupper(trim($params['jenis_sewa']));
            $tanggalMulai = trim($params['tanggal_mulai']);
            $noSupplier = trim($params['no_supplier']);

            if ( empty($namaSewa) || empty($noKontrak) || empty($jenisSewa) || empty($tanggalMulai) || empty($noSupplier) ) {
                $this->result['message'] = 'Nama sewa, nomor kontrak, jenis sewa, tanggal mulai, dan no supplier wajib diisi.';
            } else {
                $current = $m_sewa->where('id', $params['id'])->first();
                $oldType = $current && !empty($current->jenis_sewa) ? strtoupper(trim($current->jenis_sewa)) : '';
                $oldTanggalMulai = $current && !empty($current->tanggal_mulai) ? date('Y-m-d', strtotime($current->tanggal_mulai)) : '';
                $noSewa = $current && !empty($current->no_sewa) ? trim($current->no_sewa) : '';

                if ($oldType !== $jenisSewa || $oldTanggalMulai !== date('Y-m-d', strtotime($tanggalMulai))) {
                    $noSewa = $this->generateNoSewa($jenisSewa, $tanggalMulai, $params['id']);
                }

                $existing = $m_sewa
                    ->whereRaw('LOWER(LTRIM(RTRIM(no_sewa))) = ?', [strtolower(trim($noSewa))])
                    ->where('id', '!=', $params['id'])
                    ->first();

                if ( $existing ) {
                    $this->result['message'] = 'Nomor sewa sudah ada.';
                } else {
                    $data_update = [
                        'nama_sewa' => $namaSewa,
                        'no_kontrak' => $noKontrak,
                        'jumlah_bulan' => !empty($params['jumlah_bulan']) ? (int) $params['jumlah_bulan'] : 0,
                        'jumlah_siklus' => !empty($params['jumlah_siklus']) ? (int) $params['jumlah_siklus'] : 0,
                        'jenis_sewa' => $jenisSewa,
                        'tanggal_mulai' => $tanggalMulai,
                        'no_supplier' => $noSupplier,
                        'nominal_sewa' => !empty($params['nominal_sewa']) ? str_replace('.', '', $params['nominal_sewa']) : 0,
                        'no_sewa' => $noSewa,
                    ];

                    $m_sewa->where('id', $params['id'])->update($data_update);

                    $oldNoSewa = $current && !empty($current->no_sewa) ? trim($current->no_sewa) : '';
                    if (!empty($oldNoSewa)) {
                        $amortDel = new \Model\Storage\AmortisasiJadwal_model();
                        $amortDel->where('kode_transaksi', $oldNoSewa)->delete();
                    }

                    $nominalSewa = !empty($params['nominal_sewa']) ? str_replace('.', '', $params['nominal_sewa']) : 0;
                    $durasi = !empty($params['jumlah_bulan']) ? (int) $params['jumlah_bulan'] : (!empty($params['jumlah_siklus']) ? (int) $params['jumlah_siklus'] : 0);
                    $this->syncAmortisasi($noSewa, $nominalSewa, $durasi);

                    $this->result['status'] = 1;
                    $this->result['message'] = 'Data berhasil diubah';
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        }

        display_json($this->result);
    }

    public function export_data()
    {
        $type = strtolower(trim($this->input->get('type')));
        $jenisSewa = trim($this->input->get('jenis_sewa'));
        $tanggalMulai = trim($this->input->get('tanggal_mulai'));
        $supplier = trim($this->input->get('supplier'));
        $search = trim($this->input->get('search'));

        $m_sewa = new \Model\Storage\MasterSewa_model();
        $query = $m_sewa
            ->select('ms_sewa.*', 'ms_jenis_sewa.nama_jenis_sewa', 'p.nama as nama_supplier')
            ->leftJoin('ms_jenis_sewa', 'ms_jenis_sewa.kode_jenis_sewa', '=', 'ms_sewa.jenis_sewa')
            ->leftJoin('pelanggan as p', function($join) {
                $join->on('p.nomor', '=', 'ms_sewa.no_supplier')
                    ->where('p.tipe', '=', 'supplier')
                    ->where('p.mstatus', '=', 1);
            });

        if ( !empty($jenisSewa) ) {
            $query->where('ms_sewa.jenis_sewa', $jenisSewa);
        }

        if ( !empty($tanggalMulai) ) {
            $query->whereRaw('CAST(ms_sewa.tanggal_mulai AS DATE) = ?', [$tanggalMulai]);
        }

        if ( !empty($supplier) ) {
            $query->whereRaw('LOWER(LTRIM(RTRIM(ms_sewa.no_supplier))) = ?', [strtolower(trim($supplier))]);
        }

        if ( !empty($search) ) {
            $like = '%' . $search . '%';
            $query->where(function($q) use ($like) {
                $q->whereRaw('LOWER(LTRIM(RTRIM(ms_sewa.no_sewa))) LIKE ?', [strtolower($like)])
                  ->orWhereRaw('LOWER(LTRIM(RTRIM(ms_sewa.nama_sewa))) LIKE ?', [strtolower($like)])
                  ->orWhereRaw('LOWER(LTRIM(RTRIM(ms_sewa.no_kontrak))) LIKE ?', [strtolower($like)])
                  ->orWhereRaw('LOWER(LTRIM(RTRIM(p.nama))) LIKE ?', [strtolower($like)]);
            });
        }

        $rows = $query->orderBy('ms_sewa.id', 'desc')->get()->toArray();

        if ( $type === 'pdf' ) {
            $html = '<table border="1" cellpadding="4" cellspacing="0" style="border-collapse:collapse;width:100%;font-family:Arial;font-size:11px;">
                <thead>
                    <tr>
                        <th>No. Sewa</th>
                        <th>Nama Sewa</th>
                        <th>No. Kontrak</th>
                        <th>Jenis Sewa</th>
                        <th>Tanggal Mulai</th>
                        <th>Jumlah Bulan</th>
                        <th>Jumlah Siklus</th>
                        <th>No. Supplier</th>
                        <th>Nama Supplier</th>
                        <th>Nominal Sewa</th>
                    </tr>
                </thead>
                <tbody>';

            foreach ( $rows as $row ) {
                $html .= '<tr>'
                    . '<td>' . (isset($row['no_sewa']) ? $row['no_sewa'] : '-') . '</td>'
                    . '<td>' . (isset($row['nama_sewa']) ? $row['nama_sewa'] : '-') . '</td>'
                    . '<td>' . (isset($row['no_kontrak']) ? $row['no_kontrak'] : '-') . '</td>'
                    . '<td>' . (isset($row['nama_jenis_sewa']) ? $row['nama_jenis_sewa'] : (isset($row['jenis_sewa']) ? $row['jenis_sewa'] : '-')) . '</td>'
                    . '<td>' . (isset($row['tanggal_mulai']) ? date('d F Y', strtotime($row['tanggal_mulai'])) : '-') . '</td>'
                    . '<td>' . (isset($row['jumlah_bulan']) ? $row['jumlah_bulan'] : '-') . '</td>'
                    . '<td>' . (isset($row['jumlah_siklus']) ? $row['jumlah_siklus'] : '-') . '</td>'
                    . '<td>' . (isset($row['no_supplier']) ? $row['no_supplier'] : '-') . '</td>'
                    . '<td>' . (isset($row['nama_supplier']) ? $row['nama_supplier'] : '-') . '</td>'
                    . '<td>' . (isset($row['nominal_sewa']) ? number_format($row['nominal_sewa'], 0, ',', '.') : '0') . '</td>'
                    . '</tr>';
            }

            $html .= '</tbody></table>';

            if (ob_get_level()) {
                ob_end_clean();
            }

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream('MASTER_SEWA_' . date('Y-m-d H:i:s') . '.pdf', ['Attachment' => TRUE]);
            exit;
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Master Sewa');

        $headers = [
            'No. Sewa',
            'Nama Sewa',
            'No. Kontrak',
            'Jenis Sewa',
            'Tanggal Mulai',
            'Jumlah Bulan',
            'Jumlah Siklus',
            'No. Supplier',
            'Nama Supplier',
            'Nominal Sewa'
        ];

        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('A1:J1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D9EAF7');

        foreach ( $rows as $i => $row ) {
            $r = $i + 2;
            $sheet->setCellValueExplicit('A' . $r, isset($row['no_sewa']) ? $row['no_sewa'] : '-', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B' . $r, isset($row['nama_sewa']) ? $row['nama_sewa'] : '-', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $r, isset($row['no_kontrak']) ? $row['no_kontrak'] : '-', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $r, isset($row['nama_jenis_sewa']) ? $row['nama_jenis_sewa'] : (isset($row['jenis_sewa']) ? $row['jenis_sewa'] : '-'), DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $r, isset($row['tanggal_mulai']) ? date('Y-m-d', strtotime($row['tanggal_mulai'])) : '-');
            $sheet->setCellValue('F' . $r, isset($row['jumlah_bulan']) ? $row['jumlah_bulan'] : 0);
            $sheet->setCellValue('G' . $r, isset($row['jumlah_siklus']) ? $row['jumlah_siklus'] : 0);
            $sheet->setCellValueExplicit('H' . $r, isset($row['no_supplier']) ? $row['no_supplier'] : '-', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('I' . $r, isset($row['nama_supplier']) ? $row['nama_supplier'] : '-', DataType::TYPE_STRING);
            $sheet->setCellValue('J' . $r, isset($row['nominal_sewa']) ? (int) $row['nominal_sewa'] : 0);
        }

        $sheet->getStyle('E2:E' . ($r ?? 1 + 1))->getNumberFormat()->setFormatCode('yyyy-mm-dd');
        $sheet->getStyle('J2:J' . ($r ?? 1 + 1))->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(30);
        $sheet->getColumnDimension('J')->setWidth(20);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="MASTER_SEWA_' . date('Y-m-d H:i:s') . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function delete_data()
    {
        $id = $this->input->post('params');

        try {
            $m_sewa = new \Model\Storage\MasterSewa_model();
            $current = $m_sewa->where('id', $id)->first();
            $noSewa = $current && !empty($current->no_sewa) ? trim($current->no_sewa) : '';

            if (!empty($noSewa)) {
                $amort = new \Model\Storage\AmortisasiJadwal_model();
                $amort->where('kode_transaksi', $noSewa)->delete();
            }

            $m_sewa->where('id', $id)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run('base/event/delete', $m_sewa, $deskripsi_log, null, $id, $m_sewa);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil dihapus';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        }

        display_json($this->result);
    }


    public function detail_data()
    {
        $id = $this->input->post('params');
        $viewData = [
            'data'          => [],
            'supplier'      => [],
            'amortisasi'    => []
        ];

        try {
            $m_sewa = new \Model\Storage\MasterSewa_model();
            $d_sewa = $m_sewa->where('id', $id)->first();

            if ( $d_sewa ) {
                $viewData['data'] = $d_sewa->toArray();

                if ( !empty($d_sewa->no_supplier) ) {
                    $m_conf = new \Model\Storage\Conf();
                    $sql_supplier = "select * from pelanggan where tipe = 'supplier' and mstatus = 1 and nomor = '" . trim($d_sewa->no_supplier) . "'";
                    $supplier = $m_conf->hydrateRaw($sql_supplier);

                    $sql_amortisasi = "select * from tabel_amortisasi_jadwal where kode_transaksi = '" . trim($d_sewa->no_sewa) . "'";
                    $amortisasi = $m_conf->hydrateRaw($sql_amortisasi);

                    if ( $supplier && method_exists($supplier, 'count') && $supplier->count() > 0 ) {
                        $viewData['supplier'] = $supplier->toArray();
                    }

                    if ( $amortisasi && method_exists($amortisasi, 'count') && $amortisasi->count() > 0 ) {
                        $viewData['amortisasi'] = $amortisasi->toArray();
                    }
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $viewData['data']       = [];
            $viewData['supplier']   = [];
            $viewData['amortisasi'] = [];
        }

        // cetak_r($viewData);die;

        echo $this->load->view($this->pathView . 'v_detail_data', $viewData, TRUE);
    }
}