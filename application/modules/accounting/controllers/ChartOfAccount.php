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
                "assets/select2/js/select2.min.js",
                'assets/accounting/chart_of_account/js/chart-of-account.js')
            );
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
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
        $m_wilayah = new \Model\Storage\Wilayah_model();

        $content['unit'] = $m_wilayah->getDataUnit(1, $this->userid);
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

        $m_wilayah = new \Model\Storage\Wilayah_model();
        
        $m_coa = new \Model\Storage\Coa_model();
        $d_coa = $m_coa->where('id', $id)->with(['d_perusahaan', 'logs'])->first()->toArray();
        
        $content['data'] = $d_coa;
        $content['unit'] = $m_wilayah->getDataUnit(1, $this->userid);
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
            $m_coa->lap = $params['laporan'];
            $m_coa->coa_pos = $params['posisi'];
            $m_coa->status = 1;
            $m_coa->bank = $params['bank'];
            $m_coa->kas = $params['kas'];
            $m_coa->unit = $params['unit'];
            $m_coa->kode = $params['kode'];
            $m_coa->save();
            // $m_coa->gol1 = $params['gol1'];
            // $m_coa->gol2 = $params['gol2'];
            // $m_coa->gol3 = $params['gol3'];
            // $m_coa->gol4 = $params['gol4'];
            // $m_coa->gol5 = $params['gol5'];

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
                    'status' => 1,
                    'bank' => $params['bank'],
                    'kas' => $params['kas'],
                    'unit' => $params['unit'],
                    'kode' => $params['kode']
                    // 'gol1' => $params['gol1'],
                    // 'gol2' => $params['gol2'],
                    // 'gol3' => $params['gol3'],
                    // 'gol4' => $params['gol4'],
                    // 'gol5' => $params['gol5'],
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
            $m_coa->where('id', $id_coa)->update(array('status' => 0));

            $d_coa = $m_coa->where('id', $id_coa)->first();

            $deskripsi_log = 'di-non aktifkan oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_coa, $deskripsi_log, null, $d_coa['coa'] );

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
                array('11110.000', 'Kas Kecil' ),
                array('11111.001', 'Kas Pusat' ),
                array('11111.002', 'Kas Jember' ),
                array('11111.003', 'Kas Banyuwangi' ),
                array('11111.004', 'Kas Lumajang' ),
                array('11111.005', 'Kas Probolinggo' ),
                array('11111.006', 'Kas Pasuruan' ),
                array('11111.007', 'Kas Malang' ),
                array('11111.008', 'Kas Mojokerto' ),
                array('11111.009', 'Kas Kediri' ),
                array('11111.010', 'Kas Tulungagung' ),
                array('11111.011', 'Kas Bojonegoro' ),
                array('11111.012', 'Kas Lamongan' ),
                array('11111.013', 'Kas Gresik' ),
                array('11111.014', 'Kas Magetan' ),
                array('11119.000', 'Cash In Transit' ),
                array('11130.001', 'BCA  0889156688 (HO)' ),
                array('11130.002', 'BCA 0888135700 (JBR OPR)' ),
                array('11130.003', 'BCA 0888396333 (JBR PENJ)' ),
                array('11139.000', 'Bank In Transit' ),
                array('11300.000', 'Piutang Bakul ' ),
                array('11320.000', 'Piutang Bakul xxx' ),
                array('11351.000', 'Piutang Niaga RP xxx' ),
                array('11361.000', 'Piutang Niaga ORP xxx' ),
                array('11445.000', 'Cadangan Piutang Tak Tertagih' ),
                array('11532.000', 'Piutang Karyawan' ),
                array('11541.001', 'Piutang GMP Unit Banyuwangi' ),
                array('11541.002', 'Piutang GMP Unit Jember ' ),
                array('11541.003', 'Piutang GMP Unit Lumajang' ),
                array('11541.004', 'Piutang GMP Unit Probolinggo' ),
                array('11541.005', 'Piutang GMP Unit Pasuruan' ),
                array('11541.006', 'Piutang GMP Unit Mojokerto' ),
                array('11541.007', 'Piutang GMP Unit Malang' ),
                array('11541.008', 'Piutang GMP Unit Kediri' ),
                array('11541.009', 'Piutang GMP Unit Tulungagung' ),
                array('11541.010', 'Piutang GMP Unit Bojonegoro' ),
                array('11541.011', 'Piutang GMP Unit Lamongan' ),
                array('11541.012', 'Piutang GMP Unit Gresik' ),
                array('11541.013', 'Piutang GMP Unit Magetan' ),
                array('11571.000', 'Piutang Lain-lain Other Related Parties' ),
                array('12020.000', 'Persediaan Ayam Dalam Pemeliharaan' ),
                array('12030.000', 'Persediaan Pakan' ),
                array('12040.000', 'Persediaan DOC' ),
                array('12050.000', 'Persediaan OVK' ),
                array('12501.000', 'Uang Muka Pembelian DOC' ),
                array('12725.000', 'PPH Psl 25' ),
                array('12728.000', 'PPH Psl 28' ),
                array('13001.001', 'RK Unit Banyuwangi' ),
                array('13001.002', 'RK Unit Jember ' ),
                array('13001.003', 'RK Unit Lumajang' ),
                array('13001.004', 'RK Unit Probolinggo' ),
                array('13001.005', 'RK Unit Pasuruan' ),
                array('13001.006', 'RK Unit Mojokerto' ),
                array('13001.007', 'RK Unit Malang' ),
                array('13001.008', 'RK Unit Kediri' ),
                array('13001.009', 'RK Unit Tulungagung' ),
                array('13001.010', 'RK Unit Bojonegoro' ),
                array('13001.011', 'RK Unit Lamongan' ),
                array('13001.012', 'RK Unit Gresik' ),
                array('13001.013', 'RK Unit Magetan' ),
                array('15000.000', 'Aktiva Pajak Tangguhan' ),
                array('16500.000', 'Inventaris' ),
                array('16600.000', 'Kendaraan' ),
                array('16601.000', 'Kendaraan Fasilitas' ),
                array('16706.000', 'Akumulasi Penyusutan Kendaraan' ),
                array('16706.001', 'Akumulasi Penyusutan Kendaraan Fasilitas' ),
                array('19601.000', 'Inventaris' ),
                array('19651.000', 'Kendaraan' ),
                array('21172.000', 'Hutang Niaga Extern (Pakan)' ),
                array('21173.000', 'Hutang Niaga Extern (DOC)' ),
                array('21174.000', 'Hutang Niaga Extern (OVK)' ),
                array('21180.100', 'Hutang Niaga ORP (Pakan)' ),
                array('21180.200', 'Hutang Niaga ORP (DOC)' ),
                array('21180.300', 'Hutang Niaga ORP (OVK)' ),
                array('21212.000', 'Hutang Expedisi' ),
                array('21243.000', 'Hutang Lain-lain Other Related Parties' ),
                array('23100.000', 'Uang Muka Yang Diterima' ),
                array('23509.000', 'Biaya Pegawai YMHD' ),
                array('24621.000', 'PPH Psl 21' ),
                array('24621.100', 'PPH Psl 21 - Non Employee' ),
                array('24622.000', 'PPH Psl 22' ),
                array('24623.000', 'PPH Psl 23' ),
                array('24626.000', 'PPH Psl 26' ),
                array('24629.000', 'PPH Badan' ),
                array('25001.000', 'Ayat Silang' ),
                array('25002.000', 'Pembukuan Sementara' ),
                array('25100.000', 'Kewajiban Pajak Tangguhan' ),
                array('27001.000', 'Profit Center Zero Balance' ),
                array('27001.001', 'RK Pusat Mutasi Berjalan ' ),
                array('29100.000', 'Modal' ),
                array('29200.000', 'Laba Rugi Tahun Lalu' ),
                array('29300.000', 'Laba Rugi Tahun Berjalan' ),
                array('60602.000', 'Sepeda Motor / Sepeda' ),
                array('60605.000', 'Station / Combi' ),
                array('60709.000', 'Biaya Penyusutan Kendaraan Station / Combi' ),
                array('60710.000', 'Biaya Penyusutan Sepeda Motor / Sepeda' ),
                array('60801.000', 'Gaji Pegawai' ),
                array('60803.000', 'THR Pegawai' ),
                array('60803.001', 'Biaya Cadangan THR Pegawai' ),
                array('60807.000', 'BPJS ' ),
                array('60808.000', 'Sumbangan untuk Karyawan' ),
                array('60810.000', 'Bonus ' ),
                array('60854.000', 'Biaya Perlengkapan kerja' ),
                array('60904.000', 'Telepon, telegram, & Telex' ),
                array('60905.000', 'Biaya Perjalanan Dinas' ),
                array('60905.001', 'Biaya Perjalanan Dinas - PPh 21' ),
                array('60906.000', 'Biaya Representasi' ),
                array('60912.000', 'Biaya Meeting' ),
                array('60990.000', 'Pemindahbukuan Biaya Produksi' ),
                array('60998.000', 'Biaya Bagi Hasil Peternak (RHPP)' ),
                array('71101.000', 'Pemakaian Pakan' ),
                array('71102.000', 'Pemakaian OVK' ),
                array('71103.000', 'Pemakaian DOC' ),
                array('71300.000', 'Pemindahbukuan Biaya Tak Langsung' ),
                array('71400.000', 'Pemindahbukuan Biaya Produksi' ),
                array('71401.000', 'Koreksi Harga Pokok Penjualan' ),
                array('80202.000', 'Biaya Pengangkutan Pakan Ternak' ),
                array('80203.000', 'Penggantian Biaya Pengangkutan' ),
                array('80302.000', 'Biaya Kendaraan Sepeda Motor / Sepeda' ),
                array('80305.000', 'Biaya Kendaraan Station / Combi' ),
                array('80401.000', 'Gaji Pegawai ' ),
                array('80403.000', 'THR Pegawai' ),
                array('80407.000', 'BPJS ' ),
                array('80408.000', 'Sumbangan untuk Karyawan' ),
                array('80410.000', 'Bonus ' ),
                array('80451.000', 'Premi Asuransi THT' ),
                array('80501.000', 'Alat Tulis Kantor & Cetakan' ),
                array('80502.000', 'Alat Tulis Komputer & Cetakan' ),
                array('80503.000', 'Porto, Materai, & Perangko' ),
                array('80504.000', 'Telepon, telegram, & Telex' ),
                array('80505.000', 'Biaya Perjalanan Dinas' ),
                array('80505.000', 'Biaya Perjalanan Dinas - PPh 21' ),
                array('80604.000', 'Biaya Penyusutan Inventaris' ),
                array('80605.000', 'Biaya Penyusutan Inventaris Kelompok II' ),
                array('80609.000', 'Biaya Penyusutan Kendaraan Station / Combi' ),
                array('80610.000', 'Biaya Penyusutan Sepeda Motor / Sepeda' ),
                array('85306.000', 'Premi Asuransi Inventaris Kantor' ),
                array('85402.000', 'Biaya Kendaraan Sepeda Motor / Sepeda' ),
                array('85405.000', 'Biaya Kendaraan Station / Combi' ),
                array('85504.000', 'Biaya Penyusutan Inventaris' ),
                array('85505.000', 'Biaya Penyusutan Inventaris Kelompok II' ),
                array('85509.000', 'Biaya Penyusutan Kendaraan Station / Combi' ),
                array('85510.000', 'Biaya Penyusutan Sepeda Motor / Sepeda' ),
                array('85601.000', 'Gaji Pegawai ' ),
                array('85603.000', 'THR Pegawai' ),
                array('85603.001', 'Biaya Cadangan THR Pegawai' ),
                array('85607.000', 'BPJS ' ),
                array('85608.000', 'Sumbangan untuk Karyawan' ),
                array('85610.000', 'Bonus ' ),
                array('85701.000', 'Alat Tulis Kantor & Cetakan' ),
                array('85702.000', 'Alat Tulis Komputer & Cetakan' ),
                array('85703.000', 'Porto, Materai, & Perangko' ),
                array('85704.000', 'Telepon, telegram, & Telex' ),
                array('85705.000', 'Biaya Perjalanan Dinas' ),
                array('85705.001', 'Biaya Perjalanan Dinas - PPh 21' ),
                array('85706.000', 'Biaya Representasi' ),
                array('85707.000', 'Biaya Bank' ),
                array('85708.000', 'Biaya Humas' ),
                array('85709.000', 'Biaya Notaris' ),
                array('85710.000', 'Sumbangan Bagian Kantor' ),
                array('85711.000', 'Iuran & Langganan' ),
                array('85712.000', 'Biaya Meeting' ),
                array('85715.000', 'Honor Konsultan dan Akuntan' ),
                array('85719.000', 'Rekening Listrik' ),
                array('85720.000', 'Rekening Air' ),
                array('85722.000', 'Biaya Keperluan Kantor' ),
                array('85723.000', 'Biaya Kebersihan Kantor' ),
                array('85731.000', 'Premi Asuransi' ),
                array('91120.000', 'Hasil Penjualan Eksternal Ayam Besar' ),
                array('91133.000', 'Hasil Penjualan Other Related Parties ' ),
                array('91233.000', 'Retur Penjualan Other Related Parties' ),
                array('92000.000', 'Harga Pokok Penjualan' ),
                array('95220.001', 'Bunga Terima Extern - Jasa Giro' ),
                array('95220.002', 'Bunga Terima Extern - Deposito' ),
                array('96000.000', 'Pengeluaran Lain-lain' ),
                array('96010.000', 'Pembulatan Rupiah Penuh' ),
                array('96030.000', 'Kerugian Atas Penghapusan Aset' ),
                array('96040.000', 'Pengeluaran Lain-lain (Termasuk Denda Pajak)' ),
                array('97010.000', 'Pendapatan/ Kerugian Atas Penjualan Aktiva' ),
                array('97120.000', 'Pendapatan Lain-lain' ),
                array('98000.000', 'Pajak Kini' ),
                array('98001.000', 'Beban/ (Penghasilan) Pajak Tangguhan' ),
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
                } else {
                    $m_coa = new \Model\Storage\Coa_model();
                    $m_coa->where('coa', $v_arr[0])->update(
                        array(
                            'nama_coa' => $v_arr[1],
                            'status' => 1,
                        )
                    );
                }
            }
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}
