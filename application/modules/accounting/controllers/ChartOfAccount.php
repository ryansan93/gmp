<?php defined('BASEPATH') or exit('No direct script access allowed');

class ChartOfAccount extends Public_Controller
{
    private $pathView = 'accounting/chart_of_account/';
    private $url;
    private $akses;
    /**
     * Constructor
     */
    public function __construct()
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
    public function index()
    {
        if ( $this->akses['a_view'] == 1 ) {
            // $this->set_title('Berita Acara Serah Terima Titip Budidaya');
            $this->add_external_js(array(
                'assets/jquery/maskedinput/jquery.maskedinput.min.js',
                'assets/accounting/chart_of_account/js/chart-of-account.js')
            );
            $this->add_external_css(array(
                'assets/accounting/chart_of_account/css/chart-of-account.css')
            );
            $data = $this->includes;

            $content['akses'] = $this->akses;
            $content['datas'] = null;
            $content['title_panel'] = 'Chart Of Account';

            // Load Indexx
            // $content['riwayat'] = $this->load->view($this->pathView . 'list_basttb', $content, true);
            // $content['action'] = $this->load->view($this->pathView . 'input_basttb', $content, true);

            $data['title_menu'] = 'Chart Of Account';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function cekNoCoa() {
        $params = $this->input->post('params');

        try {
            $m_coa = new \Model\Storage\Coa_model();
            $now = $m_coa->getDate();

            $gol1 = $m_coa->getGol1($params['gol1']);
            $gol2 = $m_coa->getGol2($params['gol1'].$params['gol2']);
            $gol3 = $m_coa->getGol3($params['gol1'].$params['gol2'].$params['gol3']);
            $gol4 = $m_coa->getGol4($params['gol1'].$params['gol2'].$params['gol3'].'.'.$params['gol4']);
            $gol5 = $m_coa->getGol5($params['gol1'].$params['gol2'].$params['gol3'].'.'.$params['gol4'].'.'.$params['gol5']);

            $this->result['status'] = 1;
            $this->result['content'] = array(
                'gol1' => $gol1,
                'gol2' => $gol2,
                'gol3' => $gol3,
                'gol4' => $gol4,
                'gol5' => $gol5
            );
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function get_lists()
    {
        $m_coa = new \Model\Storage\Coa_model();
        $d_coa = $m_coa->with(['d_perusahaan', 'logs'])->orderBy('coa', 'asc')->get();

        $data = null;
        if ( $d_coa->count() > 0 ) {
            $data = $d_coa->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list', $content);

        echo $html;
    }

    public function get_perusahaan()
    {
        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $kode_perusahaan = $m_perusahaan->select('kode')->distinct('kode')->get();

        $data = null;
        if ( $kode_perusahaan->count() > 0 ) {
            $kode_perusahaan = $kode_perusahaan->toArray();

            foreach ($kode_perusahaan as $k => $val) {
                $m_perusahaan = new \Model\Storage\Perusahaan_model();
                $d_perusahaan = $m_perusahaan->where('kode', $val['kode'])->orderBy('version', 'desc')->first();

                $key = strtoupper($d_perusahaan->perusahaan).' - '.$d_perusahaan['kode'];
                $data[ $key ] = array(
                    'nama' => strtoupper($d_perusahaan->perusahaan),
                    'kode' => $d_perusahaan->kode
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function add_form()
    {
        $content['perusahaan'] = $this->get_perusahaan();
        $html = $this->load->view($this->pathView . 'add_form', $content); 
        
        echo $html;
    }

    public function view_form()
    {
        $id = $this->input->get('id');

        $m_coa = new \Model\Storage\Coa_model();
        $d_coa = $m_coa->where('id', $id)->with(['d_perusahaan', 'logs'])->first()->toArray();

        $content['data'] = $d_coa;
        $content['akses'] = $this->akses;

        $html = $this->load->view($this->pathView . 'view_form', $content); 
        
        echo $html;
    }

    public function edit_form()
    {
        $id = $this->input->get('id');

        $m_coa = new \Model\Storage\Coa_model();
        $d_coa = $m_coa->where('id', $id)->with(['d_perusahaan', 'logs'])->first()->toArray();

        $content['data'] = $d_coa;
        $content['perusahaan'] = $this->get_perusahaan();
        $content['akses'] = $this->akses;

        $html = $this->load->view($this->pathView . 'edit_form', $content); 
        
        echo $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_coa = new \Model\Storage\Coa_model();
            $m_coa->id_perusahaan = $params['perusahaan'];
            $m_coa->id_unit = $params['unit'];
            $m_coa->coa = $params['coa'];
            $m_coa->nama_coa = $params['nama'];
            $m_coa->gol1 = $params['gol1'];
            $m_coa->gol2 = $params['gol2'];
            $m_coa->gol3 = $params['gol3'];
            $m_coa->gol4 = $params['gol4'];
            $m_coa->gol5 = $params['gol5'];
            $m_coa->lap = $params['laporan'];
            $m_coa->coa_pos = $params['posisi'];
            $m_coa->status = 1;
            $m_coa->save();

            $id_coa = $m_coa->id;

            $d_coa = $m_coa->where('id', $id_coa)->first();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_coa, $deskripsi_log, null, $params['coa'] );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data COA berhasil disimpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit()
    {
        $params = $this->input->post('params');

        try {
            $id_coa = $params['id'];

            $m_coa = new \Model\Storage\Coa_model();
            $m_coa->where('id', $id_coa)->update(
                array(
                    'id_perusahaan' => $params['perusahaan'],
                    'id_unit' => $params['unit'],
                    'nama_coa' => $params['nama'],
                    'coa' => $params['coa'],
                    'lap' => $params['laporan'],
                    'coa_pos' => $params['posisi'],
                    'gol1' => $params['gol1'],
                    'gol2' => $params['gol2'],
                    'gol3' => $params['gol3'],
                    'gol4' => $params['gol4'],
                    'gol5' => $params['gol5'],
                    'status' => 1
                )
            );

            $d_coa = $m_coa->where('id', $id_coa)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_coa, $deskripsi_log, null, $params['coa'] );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data COA berhasil di update.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {
            $id_coa = $params['id'];

            $m_coa = new \Model\Storage\Coa_model();
            $d_coa = $m_coa->where('id', $id_coa)->update(array('status' => 0));
            
            $d_coa = $m_coa->where('id', $id_coa)->first();

            $deskripsi_log = 'di-non aktifkan oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_coa, $deskripsi_log, null, $params['coa'] );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data COA berhasil di non aktifkan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function injek() {
        try {
            $arr = array(
                array('11110.000', 'Kas Kecil'),
                array('11111.000', 'Kas Operasional Unit'),
                array('11130.000', 'Bank Unit'),
                array('11300.000', 'Piutang Bakul '),
                array('11320.000', 'Piutang Bakul'),
                array('11351.000', 'Piutang Niaga RP'),
                array('11361.000', 'Piutang Niaga ORP'),
                array('11445.000', 'Cadangan Piutang Tak Tertagih'),
                array('11510.000', 'Piutang Lain-lain '),
                array('11520.000', 'Piutang Lain-lain Extern'),
                array('11521.000', 'Piutang Asuransi'),
                array('11522.000', 'Piutang BB+BP'),
                array('11523.000', 'Piutang Barang Promosi'),
                array('11524.000', 'Piutang DOC'),
                array('11525.000', 'Piutang Kredit Stock'),
                array('11526.000', 'Piutang Kendaraan'),
                array('11527.000', 'Piutang Karung'),
                array('11528.000', 'Piutang Kemitraan'),
                array('11529.000', 'Piutang Transport'),
                array('11530.000', 'Piutang Barang Tehnik'),
                array('11532.000', 'Piutang Karyawan'),
                array('11540.000', 'Piutang Lain-lain Internal'),
                array('11541.000', 'Piutang GMP HO'),
                array('11541.001', 'Piutang GMP Unit Banyuwangi'),
                array('11541.002', 'Piutang GMP Unit Jember '),
                array('11541.003', 'Piutang GMP Unit Lumajang'),
                array('11541.004', 'Piutang GMP Unit Probolinggo'),
                array('11541.005', 'Piutang GMP Unit Pasuruan'),
                array('11541.006', 'Piutang GMP Unit Mojokerto'),
                array('11541.007', 'Piutang GMP Unit Malang'),
                array('11541.008', 'Piutang GMP Unit Kediri'),
                array('11541.009', 'Piutang GMP Unit Tulungagung'),
                array('11541.010', 'Piutang GMP Unit Bojonegoro'),
                array('11541.011', 'Piutang GMP Unit Lamongan'),
                array('11541.012', 'Piutang GMP Unit Gresik'),
                array('11541.013', 'Piutang GMP Unit Magetan'),
                array('11551.000', 'Piutang Lain-lain Subsidiaries'),
                array('11561.000', 'Piutang Lain-lain Group'),
                array('11571.000', 'Piutang Lain-lain Other Related Parties'),
                array('12000.000', 'Persediaan'),
                array('12020.000', 'Persediaan Ayam Dalam Pemeliharaan'),
                array('12030.000', 'Persediaan Pakan'),
                array('12040.000', 'Persediaan DOC'),
                array('12050.000', 'Persediaan OVK'),
                array('12500.000', 'Uang Muka & Jaminan '),
                array('12501.000', 'Uang Muka Pembelian DOC'),
                array('12502.000', 'Uang Muka Pembelian Pakan'),
                array('12503.000', 'Uang Muka Pembelian Aktiva Tetap '),
                array('12504.000', 'Uang Muka Biaya Perjalanan Dinas'),
                array('12505.000', 'Uang Muka Pembelian Alat-alat Tulis & Cetak'),
                array('12506.000', 'Uang Muka Biaya Pengangkutan'),
                array('12550.000', 'Uang Muka Lain-lain'),
                array('12551.000', 'Uang Jaminan '),
                array('12552.000', 'Uang Jaminan Sewa Rumah'),
                array('12599.000', 'Uang Jaminan Lain-lain'),
                array('12600.000', 'Biaya Yang Dibayar di Muka '),
                array('12601.000', 'Biaya Produksi YDD'),
                array('12602.000', 'Biaya Barang YDD'),
                array('12603.000', 'Biaya Bangunan YDD'),
                array('12605.000', 'Biaya Pemeliharaan Mesin & Instalasi YDD'),
                array('12606.000', 'Biaya Pemeliharaan Inventaris YDD'),
                array('12607.000', 'Biaya Kendaraan YDD'),
                array('12608.000', 'Biaya Pegawai YDD'),
                array('12609.000', 'Biaya Penjualan YDD'),
                array('12610.000', 'Biaya Asuransi YDD'),
                array('12611.000', 'Biaya Umum YDD'),
                array('12700.000', 'Pajak-pajak Yang Dibayar di Muka '),
                array('12721.000', 'PPH Psl 21 '),
                array('12722.000', 'PPH Psl 22'),
                array('12723.000', 'PPH Psl 23 '),
                array('12725.000', 'PPH Psl 25'),
                array('12727.000', 'PPN Masukan'),
                array('12728.000', 'PPH Psl 28'),
                array('12800.000', 'Pendapatan YMH Diterima'),
                array('13000.000', 'Rekening Koran'),
                array('13001.001', 'RK Unit Banyuwangi'),
                array('13001.002', 'RK Unit Jember '),
                array('13001.003', 'RK Unit Lumajang'),
                array('13001.004', 'RK Unit Probolinggo'),
                array('13001.005', 'RK Unit Pasuruan'),
                array('13001.006', 'RK Unit Mojokerto'),
                array('13001.007', 'RK Unit Malang'),
                array('13001.008', 'RK Unit Kediri'),
                array('13001.009', 'RK Unit Tulungagung'),
                array('13001.010', 'RK Unit Bojonegoro'),
                array('13001.011', 'RK Unit Lamongan'),
                array('13001.012', 'RK Unit Gresik'),
                array('13001.013', 'RK Unit Magetan'),
                array('15000.000', 'Aktiva Pajak Tangguhan'),
                array('16000.000', 'Aktiva Tetap'),
                array('16100.000', 'Tanah'),
                array('16200.000', 'Site Fasilities'),
                array('16300.000', 'Bangunan'),
                array('16400.000', 'Mesin & Instalasi'),
                array('16500.000', 'Inventaris'),
                array('16600.000', 'Kendaraan'),
                array('16601.000', 'Kendaraan Fasilitas'),
                array('16700.000', 'Akumulasi Penyusutan Aktiva Tetap'),
                array('16705.000', 'Akumulasi Penyusutan Inventaris'),
                array('16706.000', 'Akumulasi Penyusutan Kendaraan'),
                array('16706.001', 'Akumulasi Penyusutan Kendaraan Fasilitas'),
                array('17000.000', 'Aktiva Sewa Guna Usaha'),
                array('18000.000', 'Aktiva Tak Berwujud'),
                array('19000.000', 'Aktiva Lain-lain'),
                array('19100.000', 'Biaya Yang Ditangguhkan'),
                array('19101.000', 'Biaya Produksi'),
                array('19102.000', 'Biaya Penjualan'),
                array('19103.000', 'Biaya Umum'),
                array('19200.000', 'Uang Jaminan Jangka Panjang'),
                array('19400.000', 'Investasi Belum Selesai'),
                array('19401.000', 'Tanah'),
                array('19451.000', 'Site Fasilities'),
                array('19501.000', 'Bangunan'),
                array('19551.000', 'Mesin & Instalasi'),
                array('19601.000', 'Inventaris'),
                array('19651.000', 'Kendaraan'),
                array('20000.000', 'Pasiva'),
                array('21000.000', 'Pasiva Lancar'),
                array('21100.000', 'Hutang'),
                array('21101.000', 'Hutang Bank'),
                array('21171.000', 'Hutang Niaga (Khusus DOC/Pakan/ OVK)'),
                array('21172.000', 'Hutang Niaga Extern (Pakan)'),
                array('21173.000', 'Hutang Niaga Extern (DOC)'),
                array('21174.000', 'Hutang Niaga Extern (OVK)'),
                array('21177.000', 'Hutang Niaga Intern'),
                array('21178.000', 'Hutang Niaga Antar Unit'),
                array('21179.000', 'Hutang Niaga Group'),
                array('21180.000', 'Hutang Niaga Other Related Parties'),
                array('21180.100', 'Hutang Niaga ORP (Pakan)'),
                array('21180.200', 'Hutang Niaga ORP (DOC)'),
                array('21180.300', 'Hutang Niaga ORP (OVK)'),
                array('21201.000', 'Hutang Lain-lain'),
                array('21211.000', 'Hutang Lain-lain Extern'),
                array('21212.000', 'Hutang Expedisi'),
                array('21240.000', 'Hutang Lain-lain Intern'),
                array('21241.000', 'Hutang Lain-lain Antar Unit'),
                array('21242.000', 'Hutang Lain-lain Group'),
                array('21243.000', 'Hutang Lain-lain Other Related Parties'),
                array('21299.000', 'Hutang Lain-lain'),
                array('23100.000', 'Uang Muka Yang Diterima'),
                array('23200.000', 'Jaminan Yang Diterima'),
                array('23500.000', 'Biaya Yang Masih Harus Dibayar'),
                array('23501.000', 'Biaya Produksi YMHD'),
                array('23503.000', 'Biaya Barang YMHD'),
                array('23504.000', 'Biaya Bangunan YMHD'),
                array('23505.000', 'Biaya Pemeliharaan Bangunan YMHD'),
                array('23506.000', 'Biaya Mesin & Instalasi YMHD'),
                array('23507.000', 'Biaya Pemeliharaan Inventaris YMHD'),
                array('23508.000', 'Biaya Kendaraan YMHD'),
                array('23509.000', 'Biaya Pegawai YMHD'),
                array('24600.000', 'Hutang Pajak'),
                array('24621.000', 'PPH Psl 21'),
                array('24621.100', 'PPH Psl 21 - Non Employee'),
                array('24622.000', 'PPH Psl 22'),
                array('24623.000', 'PPH Psl 23'),
                array('24625.000', 'PPH Psl 25'),
                array('24626.000', 'PPH Psl 26'),
                array('24627.000', 'PPH Final'),
                array('24628.000', 'PPN Keluaran'),
                array('24629.000', 'PPH Badan'),
                array('24700.000', 'Hutang Jangka Pendek'),
                array('25000.000', 'Rekening Netral'),
                array('25001.000', 'Ayat Silang'),
                array('25002.000', 'Pembukuan Sementara'),
                array('25100.000', 'Kewajiban Pajak Tangguhan'),
                array('26000.000', 'Hutang Jangka Panjang'),
                array('26001.000', 'Hutang Bank '),
                array('27000.000', 'Rekening Koran'),
                array('27001.001', 'RK Pusat Mutasi Berjalan '),
                array('27001.002', 'RK Pusat Ex Mutasi Tahun Lalu '),
                array('29000.000', 'Modal & Sisa Laba'),
                array('29100.000', 'Modal'),
                array('29200.000', 'Laba Rugi Tahun Lalu'),
                array('29300.000', 'Laba Rugi Tahun Berjalan'),
                array('60000.000', 'Biaya Tak Langsung'),
                array('60601.000', 'Sedan'),
                array('60602.000', 'Sepeda Motor / Sepeda'),
                array('60603.000', 'Jeep'),
                array('60604.000', 'Pick Up'),
                array('60605.000', 'Station / Combi'),
                array('60700.000', 'Biaya Penyusutan '),
                array('60701.000', 'Biaya Penyusutan Site Fasilities'),
                array('60702.000', 'Biaya Penyusutan Bangunan'),
                array('60703.000', 'Biaya Penyusutan Mesin & Instalasi'),
                array('60704.000', 'Biaya Penyusutan Inventaris Kelompok I'),
                array('60705.000', 'Biaya Penyusutan Inventaris Kelompok II'),
                array('60706.000', 'Biaya Penyusutan Kendaraan Sedan'),
                array('60707.000', 'Biaya Penyusutan Kendaraan Jeep'),
                array('60708.000', 'Biaya Penyusutan Kendaraan Pick Up'),
                array('60709.000', 'Biaya Penyusutan Kendaraan Station / Combi'),
                array('60710.000', 'Biaya Penyusutan Sepeda Motor / Sepeda'),
                array('60801.000', 'Gaji Pegawai'),
                array('60802.000', 'PPH Psl 21'),
                array('60803.000', 'THR Pegawai'),
                array('60803.001', 'Biaya Cadangan THR Pegawai'),
                array('60807.000', 'BPJS '),
                array('60808.000', 'Sumbangan untuk Karyawan'),
                array('60810.000', 'Bonus '),
                array('60851.000', 'Premi Asuransi THT'),
                array('60852.000', 'Biaya Pendidikan'),
                array('60854.000', 'Biaya Perlengkapan kerja'),
                array('60855.000', 'Tunjangan Perumahan Bagian Produksi'),
                array('60856.000', 'Rekreasi & Olahraga'),
                array('60901.000', 'Alat Tulis Kantor & Cetakan'),
                array('60902.000', 'Alat Tulis Komputer & Cetakan'),
                array('60903.000', 'Porto, Materai, & Perangko'),
                array('60904.000', 'Telepon, telegram, & Telex'),
                array('60905.000', 'Biaya Perjalanan Dinas'),
                array('60905.001', 'Biaya Perjalanan Dinas - PPh 21'),
                array('60906.000', 'Biaya Representasi'),
                array('60912.000', 'Biaya Meeting'),
                array('60913.000', 'Biaya Transport Lokal'),
                array('60990.000', 'Pemindahbukuan Biaya Produksi'),
                array('60998.000', 'Biaya Bagi Hasil Peternak (RHPP)'),
                array('71101.000', 'Pemakaian Pakan'),
                array('71102.000', 'Pemakaian OVK'),
                array('71103.000', 'Pemakaian DOC'),
                array('72000.000', 'Pemindahbukuan Biaya Tak Langsung'),
                array('71400.000', 'Pemindahbukuan Biaya Produksi'),
                array('71401.000', 'Koreksi Harga Pokok Penjualan'),
                array('80202.000', 'Biaya Pengangkutan Pakan Ternak'),
                array('80203.000', 'Penggantian Biaya Pengangkutan'),
                array('80301.000', 'Biaya Kendaraan Sedan'),
                array('80302.000', 'Biaya Kendaraan Sepeda Motor / Sepeda'),
                array('80303.000', 'Biaya Kendaraan Jeep'),
                array('80304.000', 'Biaya Kendaraan Pick Up'),
                array('80305.000', 'Biaya Kendaraan Station / Combi'),
                array('80401.000', 'Gaji Pegawai '),
                array('80402.000', 'PPH Psl 21'),
                array('80403.000', 'THR Pegawai'),
                array('80403.001', 'Biaya Cadangan THR Pegawai'),
                array('80407.000', 'BPJS '),
                array('80408.000', 'Sumbangan untuk Karyawan'),
                array('80410.000', 'Bonus '),
                array('80451.000', 'Premi Asuransi THT'),
                array('80452.000', 'Biaya Pendidikan'),
                array('80454.000', 'Biaya Perlengkapan Kerja'),
                array('80455.000', 'Tunjangan Perumahan Bagian Pemasaran'),
                array('80456.000', 'Biaya Rekreasi & Olah Raga'),
                array('80501.000', 'Alat Tulis Kantor & Cetakan'),
                array('80502.000', 'Alat Tulis Komputer & Cetakan'),
                array('80503.000', 'Porto, Materai, & Perangko'),
                array('80504.000', 'Telepon, telegram, & Telex'),
                array('80505.000', 'Biaya Perjalanan Dinas'),
                array('80505.000', 'Biaya Perjalanan Dinas - PPh 21'),
                array('80506.000', 'Biaya Representasi'),
                array('80512.000', 'Biaya Meeting'),
                array('80513.000', 'Biaya Transport Lokal'),
                array('80601.000', 'Biaya Penyusutan Site Fasilities'),
                array('80602.000', 'Biaya Penyusutan Bangunan'),
                array('80604.000', 'Biaya Penyusutan Inventaris'),
                array('80605.000', 'Biaya Penyusutan Inventaris Kelompok II'),
                array('80606.000', 'Biaya Penyusutan Kendaraan Sedan'),
                array('80607.000', 'Biaya Penyusutan Kendaraan Jeep'),
                array('80608.000', 'Biaya Penyusutan Kendaraan Pick Up'),
                array('80609.000', 'Biaya Penyusutan Kendaraan Station / Combi'),
                array('80610.000', 'Biaya Penyusutan Sepeda Motor / Sepeda'),
                array('80700.000', 'Biaya Penjualan Lain-lain'),
                array('85101.000', 'PBB '),
                array('85102.000', 'Premi Asuransi Bangunan'),
                array('85201.000', 'Biaya Pemeliharaan Bangunan Kantor'),
                array('85301.000', 'Biaya Pemeliharaan Inventaris Mesin Kantor'),
                array('85302.000', 'Biaya Pemeliharaan Inventaris Komputer'),
                array('85303.000', 'Biaya Pemeliharaan Perabot Kantor'),
                array('85306.000', 'Premi Asuransi Inventaris Kantor'),
                array('85401.000', 'Biaya Kendaraan Sedan'),
                array('85402.000', 'Biaya Kendaraan Sepeda Motor / Sepeda'),
                array('85403.000', 'Biaya Kendaraan Jeep'),
                array('85404.000', 'Biaya Kendaraan Pick Up'),
                array('85405.000', 'Biaya Kendaraan Station / Combi'),
                array('85501.000', 'Biaya Penyusutan Site Fasilities'),
                array('85502.000', 'Biaya Penyusutan Bangunan'),
                array('85503.000', 'Biaya Penyusutan Mesin & Instalasi'),
                array('85504.000', 'Biaya Penyusutan Inventaris'),
                array('85505.000', 'Biaya Penyusutan Inventaris Kelompok II'),
                array('85506.000', 'Biaya Penyusutan Kendaraan Sedan'),
                array('85507.000', 'Biaya Penyusutan Kendaraan Jeep'),
                array('85508.000', 'Biaya Penyusutan Kendaraan Pick Up'),
                array('85509.000', 'Biaya Penyusutan Kendaraan Station / Combi'),
                array('85510.000', 'Biaya Penyusutan Sepeda Motor / Sepeda'),
                array('85601.000', 'Gaji Pegawai '),
                array('85602.000', 'PPH Psl 21'),
                array('85603.000', 'THR Pegawai'),
                array('85603.001', 'Biaya Cadangan THR Pegawai'),
                array('85607.000', 'BPJS '),
                array('85608.000', 'Sumbangan untuk Karyawan'),
                array('85610.000', 'Bonus '),
                array('85616.000', 'Tunjangan Fasilitas Umum & Administrasi'),
                array('85619.000', 'Biaya Pegawai Lain-lain'),
                array('85651.000', 'Premi Asuransi THT'),
                array('85652.000', 'Biaya Pendidikan'),
                array('85654.000', 'Biaya Perlengkapan Kerja'),
                array('85655.000', 'Tunjangan Perumahan Bagian Umum & Administrasi'),
                array('85656.000', 'Biaya Rekreasi & Olah Raga'),
                array('85657.000', 'Obat-obatan untuk Karyawan Kantor'),
                array('85700.000', 'Biaya Umum'),
                array('85701.000', 'Alat Tulis Kantor & Cetakan'),
                array('85702.000', 'Alat Tulis Komputer & Cetakan'),
                array('85703.000', 'Porto, Materai, & Perangko'),
                array('85704.000', 'Telepon, telegram, & Telex'),
                array('85705.000', 'Biaya Perjalanan Dinas'),
                array('85705.001', 'Biaya Perjalanan Dinas - PPh 21'),
                array('85706.000', 'Biaya Representasi'),
                array('85707.000', 'Biaya Bank'),
                array('85708.000', 'Biaya Humas'),
                array('85709.000', 'Biaya Notaris'),
                array('85710.000', 'Sumbangan Bagian Kantor'),
                array('85711.000', 'Iuran & Langganan'),
                array('85712.000', 'Biaya Meeting'),
                array('85713.000', 'Biaya Transport Lokal'),
                array('85714.000', 'Biaya Program '),
                array('85715.000', 'Honor Konsultan dan Akuntan'),
                array('85719.000', 'Rekening Listrik'),
                array('85720.000', 'Rekening Air'),
                array('85721.000', 'Biaya Perijinan'),
                array('85722.000', 'Biaya Keperluan Kantor'),
                array('85723.000', 'Biaya Kebersihan Kantor'),
                array('85724.000', 'Biaya Evaluasi Calon Karyawan'),
                array('85726.000', 'Biaya Keamanan'),
                array('85728.000', 'Biaya Hari Besar Nasional'),
                array('85731.000', 'Premi Asuransi'),
                array('91000.000', 'Penjualan'),
                array('91120.000', 'Hasil Penjualan Eksternal Ayam Besar'),
                array('91130.000', 'Hasil Penjualan Intern'),
                array('91131.000', 'Hasil Penjualan Antar Unit'),
                array('91132.000', 'Hasil Penjualan Group'),
                array('91133.000', 'Hasil Penjualan Other Related Parties '),
                array('91220.000', 'Retur Penjualan ekstern'),
                array('91230.000', 'Retur Penjualan Intern'),
                array('91231.000', 'Retur Penjualan Antar Unit'),
                array('91232.000', 'Retur Penjualan Group'),
                array('91233.000', 'Retur Penjualan Other Related Parties'),
                array('92000.000', 'Harga Pokok Penjualan'),
                array('95220.001', 'Bunga Terima Extern - Jasa Giro'),
                array('95220.002', 'Bunga Terima Extern - Deposito'),
                array('96000.000', 'Pengeluaran Lain-lain'),
                array('96010.000', 'Pembulatan Rupiah Penuh'),
                array('96030.000', 'Kerugian Atas Penghapusan Aset'),
                array('96040.000', 'Pengeluaran Lain-lain (Termasuk Denda Pajak)'),
                array('96042.000', 'Biaya Kerugian Claim Asuransi'),
                array('97010.000', 'Pendapatan/ Kerugian Atas Penjualan Aktiva'),
                array('97120.000', 'Pendapatan Lain-lain'),
                array('98000.000', 'Pajak Kini'),
                array('98001.000', 'Beban/ (Penghasilan) Pajak Tangguhan'),
                array('99999.000', 'Sisa Laba Rugi'),
            );

            foreach ($arr as $k_arr => $v_arr) {
                $m_coa = new \Model\Storage\Coa_model();
                $d_coa = $m_coa->where('coa', $v_arr[0])->first();

                if ( !$d_coa ) {
                    $m_coa = new \Model\Storage\Coa_model();
                    $m_coa->coa = $v_arr[0];
                    $m_coa->nama_coa = $v_arr[1];
                    $m_coa->status = 1;
                    $m_coa->save();
    
                    $id_coa = $m_coa->id;
    
                    $d_coa = $m_coa->where('id', $id_coa)->first();
    
                    $deskripsi_log = 'di-import oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/save', $d_coa, $deskripsi_log, null, $v_arr[0] );
                }
            }
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}
