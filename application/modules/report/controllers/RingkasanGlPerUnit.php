<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RingkasanGlPerUnit extends Public_Controller {

    private $path = 'report/ringkasan_gl_per_unit/';
    private $url;

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
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                "assets/report/ringkasan_gl_per_unit/js/ringkasan-gl-per-unit.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/ringkasan_gl_per_unit/css/ringkasan-gl-per-unit.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['perusahaan'] = $this->getPerusahaan();
            $content['title_menu'] = 'Ringkasan GL Per Unit';

            $data['view'] = $this->load->view($this->path.'index', $content, TRUE);
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

    /**
     * Ringkasan = rekap per unit dari data yang SAMA PERSIS dengan report/GeneralLedger
     * (Modules::run ke GeneralLedger::getData, yg sudah jadi acuan resmi utk Saldo Awal/Debet/
     * Kredit/Saldo Akhir per No.COA+Unit -- termasuk override coa.unit & fallback saldo_bulanan/
     * sacoa bulan sebelumnya), lalu dijumlahkan per unit di sini (bukan hitung ulang dari det_jurnal).
     */
    public function getData() {
        $params = $this->input->get('params');

        $bulan      = (int)$params['bulan'];
        $tahun      = substr($params['tahun'], 0, 4);
        $perusahaan = !empty($params['perusahaan']) ? $params['perusahaan'] : 'all';

        $angka_bulan = (strlen($bulan) == 1) ? '0'.$bulan : $bulan;
        $date = $tahun.'-'.$angka_bulan.'-01';
        $start_date = date("Y-m-d", strtotime($date));
        $end_date = date("Y-m-t", strtotime($date));

        $rows = Modules::run('report/GeneralLedger/getData', $start_date, $end_date, $perusahaan, 'all');

        $data = array();
        $tot_saldo_awal = 0;
        $tot_debet = 0;
        $tot_kredit = 0;
        $tot_saldo_akhir = 0;

        if ( !empty($rows) ) {
            $m_conf = new \Model\Storage\Conf();
            $d_wilayah = $m_conf->hydrateRaw("
                select w1.kode, REPLACE(REPLACE(w1.nama, 'Kota ', ''), 'Kab ', '') as nama from wilayah w1
                right join
                    (select max(id) as id, kode from wilayah where kode is not null group by kode) w2
                    on w1.id = w2.id
            ");
            $nama_unit_map = array();
            if ( $d_wilayah->count() > 0 ) {
                foreach ($d_wilayah->toArray() as $w) {
                    $nama_unit_map[ $w['kode'] ] = $w['nama'];
                }
            }

            foreach ($rows as $row) {
                $u = !empty($row['unit']) ? $row['unit'] : '-';

                if ( !isset($data[ $u ]) ) {
                    $data[ $u ] = array(
                        'unit' => $u,
                        'nama_unit' => isset($nama_unit_map[ $u ]) ? $nama_unit_map[ $u ] : '',
                        'saldo_awal' => 0,
                        'debet' => 0,
                        'kredit' => 0,
                        'saldo_akhir' => 0,
                    );
                }

                $data[ $u ]['saldo_awal']  += (float)$row['saldo_awal'];
                $data[ $u ]['debet']       += (float)$row['debet'];
                $data[ $u ]['kredit']      += (float)$row['kredit'];
                $data[ $u ]['saldo_akhir'] += (float)$row['saldo_akhir'];

                $tot_saldo_awal  += (float)$row['saldo_awal'];
                $tot_debet       += (float)$row['debet'];
                $tot_kredit      += (float)$row['kredit'];
                $tot_saldo_akhir += (float)$row['saldo_akhir'];
            }

            ksort($data);
        }

        $content['data'] = $data;
        $content['total'] = array(
            'saldo_awal' => $tot_saldo_awal,
            'debet' => $tot_debet,
            'kredit' => $tot_kredit,
            'saldo_akhir' => $tot_saldo_akhir,
        );
        $html = $this->load->view($this->path.'list', $content, TRUE);

        echo $html;
    }
}
