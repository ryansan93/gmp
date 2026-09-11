<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RekapSewa extends Public_Controller {

    private $pathView = 'report/rekap_sewa/';
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
        // if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                "assets/report/rekap_sewa/js/rekap-sewa.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/rekap_sewa/css/rekap-sewa.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['jenis_sewa'] = $this->getJenisSewa();
            $content['supplier'] = $this->getSupplier();
            $content['title_menu'] = 'Rekap Sewa';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        // } else {
        //     showErrorAkses();
        // }
    }

    public function getJenisSewa()
    {
        $m_jenis = new \Model\Storage\MasterJenisSewa_model();
        return $m_jenis->orderBy('id', 'asc')->get()->toArray();
    }

    public function getSupplier()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from pelanggan
            where
                tipe = 'supplier' and
                mstatus = 1 and
                LOWER(LTRIM(RTRIM(kategori_supplier))) = 'sewa'
            order by nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        return $d_conf->count() > 0 ? $d_conf->toArray() : null;
    }

    public function getLists()
    {
        $params = $this->input->post();
        $data = $this->getData( $params );

        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }

    /*
     * Rekap kontrak sewa + progress waktu (tanggal_mulai vs durasi) dan progress amortisasi
     * (berapa termin di tabel_amortisasi_jadwal yg sudah berstatus selesai). Belum ada progress
     * pembayaran (DP/cicilan) karena mekanismenya belum dibangun di ms_sewa.
     */
    public function getData($params = array())
    {
        $jenisSewa = isset($params['jenis_sewa']) ? trim($params['jenis_sewa']) : '';
        $supplier  = isset($params['supplier']) ? trim($params['supplier']) : '';
        $status    = isset($params['status']) ? trim($params['status']) : '';
        $search    = isset($params['search']) ? trim($params['search']) : '';

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

        if ( !empty($supplier) ) {
            $query->whereRaw('LOWER(LTRIM(RTRIM(ms_sewa.no_supplier))) = ?', [strtolower($supplier)]);
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

        $noSewaList = array_map(function($row) { return trim($row['no_sewa']); }, $rows);
        $amortByNoSewa = $this->getAmortisasiSummary( $noSewaList );

        $today = date('Y-m-d');

        $data = array();
        foreach ( $rows as $row ) {
            $durasi = !empty($row['jumlah_bulan']) ? (int) $row['jumlah_bulan'] : (!empty($row['jumlah_siklus']) ? (int) $row['jumlah_siklus'] : 0);
            $tglMulai = !empty($row['tanggal_mulai']) ? substr($row['tanggal_mulai'], 0, 10) : null;
            $tglSelesai = ( $tglMulai && $durasi > 0 ) ? date('Y-m-d', strtotime($tglMulai.' + '.$durasi.' months')) : null;

            $bulanBerjalan = 0;
            if ( $tglMulai && $today >= $tglMulai ) {
                $diffBulan = (strtotime($today) - strtotime($tglMulai)) / (60 * 60 * 24 * 30.44);
                $bulanBerjalan = floor($diffBulan) + 1;
                if ( $durasi > 0 ) {
                    $bulanBerjalan = min($bulanBerjalan, $durasi);
                }
            }

            $progressWaktu = ( $durasi > 0 ) ? round( ($bulanBerjalan / $durasi) * 100, 1 ) : 0;

            $noSewa = trim($row['no_sewa']);
            $amort = isset($amortByNoSewa[ $noSewa ]) ? $amortByNoSewa[ $noSewa ] : array('total' => 0, 'selesai' => 0);
            $progressAmortisasi = ( $amort['total'] > 0 ) ? round( ($amort['selesai'] / $amort['total']) * 100, 1 ) : 0;

            $statusKontrak = ( $tglSelesai && $today > $tglSelesai ) ? 'Selesai' : 'Aktif';

            if ( !empty($status) && strtolower($statusKontrak) !== strtolower($status) ) {
                continue;
            }

            $row['tanggal_selesai']      = $tglSelesai;
            $row['durasi']               = $durasi;
            $row['bulan_berjalan']       = $bulanBerjalan;
            $row['progress_waktu']       = $progressWaktu;
            $row['progress_amortisasi']  = $progressAmortisasi;
            $row['status_kontrak']       = $statusKontrak;

            $data[] = $row;
        }

        return $data;
    }

    private function getAmortisasiSummary($noSewaList)
    {
        $summary = array();
        if ( empty($noSewaList) ) {
            return $summary;
        }

        $inList = implode(', ', array_map(function($v) {
            return "'".str_replace("'", "''", $v)."'";
        }, $noSewaList));

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                kode_transaksi,
                count(*) as total,
                sum(case when status = 1 then 1 else 0 end) as selesai
            from tabel_amortisasi_jadwal
            where kode_transaksi in (".$inList.")
            group by kode_transaksi
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            foreach ( $d_conf->toArray() as $row ) {
                $summary[ trim($row['kode_transaksi']) ] = array(
                    'total'   => (int) $row['total'],
                    'selesai' => (int) $row['selesai']
                );
            }
        }

        return $summary;
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
        $data = $this->getData( $params );

        $filename = 'REKAP_SEWA_'.date('Ymd_His');

        $arr_header = array('No. Sewa', 'No. Kontrak', 'Nama Sewa', 'Jenis Sewa', 'Supplier', 'Tanggal Mulai', 'Tanggal Selesai', 'Durasi (Bulan)', 'Bulan Berjalan', '% Progress Waktu', '% Progress Amortisasi', 'Status', 'Nominal Sewa');
        $arr_column = null;
        if ( !empty($data) ) {
            $idx = 0;

            $tot_nominal = 0;
            foreach ( $data as $row ) {
                $arr_column[ $idx ] = array(
                    'No. Sewa' => array('value' => $row['no_sewa'], 'data_type' => 'string'),
                    'No. Kontrak' => array('value' => $row['no_kontrak'], 'data_type' => 'string'),
                    'Nama Sewa' => array('value' => $row['nama_sewa'], 'data_type' => 'string'),
                    'Jenis Sewa' => array('value' => !empty($row['nama_jenis_sewa']) ? $row['nama_jenis_sewa'] : $row['jenis_sewa'], 'data_type' => 'string'),
                    'Supplier' => array('value' => !empty($row['nama_supplier']) ? $row['nama_supplier'] : '-', 'data_type' => 'string'),
                    'Tanggal Mulai' => array('value' => !empty($row['tanggal_mulai']) ? date('Y-m-d', strtotime($row['tanggal_mulai'])) : '-', 'data_type' => 'string'),
                    'Tanggal Selesai' => array('value' => !empty($row['tanggal_selesai']) ? $row['tanggal_selesai'] : '-', 'data_type' => 'string'),
                    'Durasi (Bulan)' => array('value' => $row['durasi'], 'data_type' => 'integer'),
                    'Bulan Berjalan' => array('value' => $row['bulan_berjalan'], 'data_type' => 'integer'),
                    '% Progress Waktu' => array('value' => $row['progress_waktu'], 'data_type' => 'decimal2'),
                    '% Progress Amortisasi' => array('value' => $row['progress_amortisasi'], 'data_type' => 'decimal2'),
                    'Status' => array('value' => $row['status_kontrak'], 'data_type' => 'string'),
                    'Nominal Sewa' => array('value' => $row['nominal_sewa'], 'data_type' => 'decimal2'),
                );

                $tot_nominal += $row['nominal_sewa'];
                $idx++;
            }

            $arr_column[] = array(
                'Status' => array('value' => 'Total', 'data_type' => 'string', 'colspan' => array('A','L'), 'align' => 'right', 'text_style' => 'bold'),
                'Nominal Sewa' => array('value' => $tot_nominal, 'data_type' => 'decimal2', 'text_style' => 'bold'),
            );
        }

        Modules::run( 'base/ExportExcel/exportExcelUsingSpreadSheet', $filename, $arr_header, $arr_column );

        $this->load->helper('download');
        force_download('export_excel/'.$filename.'.xlsx', NULL);
    }
}
