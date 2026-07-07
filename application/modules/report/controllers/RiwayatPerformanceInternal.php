<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Report Performa Internal — TANPA login.
 *
 * Sengaja extends MY_Controller (bukan Public_Controller) supaya tidak
 * kena pengecekan isLogin / redirect ke user/Login.
 * View di-render standalone (tanpa app-shell themes/index.php) karena
 * app-shell membaca menu & hak akses dari sesi login.
 */
class RiwayatPerformanceInternal extends MY_Controller {

    private $pathView = 'report/riwayat_performance_internal/';

    private $standarCache = null;

    private $farm_group = array(
        1 => array(
            'urut' => 1,
            'db' => 'default',
            'nama' => 'FARM KEDIRI 3,4 & 5',
            'mitra' => array(
                '25M0483' => array(
                    'kandang' => array(1),
                ),
                '25M0486' => array(
                    'kandang' => array(1),
                ),
                '25M0487' => array(
                    'kandang' => array(1),
                ),
            )
        ),
        2 => array(
            'urut' => 2,
            'db' => 'mgb',
            'nama' => 'FARM NGANJUK 1 & 2',
            'mitra' => array(
                '22M0887' => array(
                    'kandang' => array(1, 2),
                ),
                '22M0888' => array(
                    'kandang' => array(1, 2),
                ),
            )
        ),
        3 => array(
            'urut' => 3,
            'db' => 'default',
            'nama' => 'FARM TULUNGAGUNG',
            'mitra' => array(
                '26M0645' => array(
                    'kandang' => array(1),
                ),
            )
        ),
        4 => array(
            'urut' => 4,
            'db' => 'default',
            'nama' => 'FARM MALANG 1 & 2',
            'mitra' => array(
                '26M0620' => array(
                    'kandang' => array(1),
                ),
                '26M0621' => array(
                    'kandang' => array(1),
                ),
            )
        ),
        5 => array(
            'urut' => 5,
            'db' => 'default',
            'nama' => 'FARM PASURUAN 1 & 2',
            'mitra' => array(
                '26M0655' => array(
                    'kandang' => array(1),
                ),
                '26M0662' => array(
                    'kandang' => array(1),
                ),
            )
        ),
        6 => array(
            'urut' => 6,
            'db' => 'default',
            'nama' => 'FARM GRESIK',
            'mitra' => array(
                '26M0676' => array(
                    'kandang' => array(1),
                ),
            )
        ),
        7 => array(
            'urut' => 7,
            'db' => 'default',
            'nama' => 'FARM LAMONGAN 1',
            'mitra' => array(
                '25M0561' => array(
                    'kandang' => array(1),
                ),
            )
        ),
        8 => array(
            'urut' => 8,
            'db' => 'mgb',
            'nama' => 'FARM LAMONGAN 2',
            'mitra' => array(
                '25M1694' => array(
                    'kandang' => array(1, 2),
                ),
            )
        ),
        9 => array(
            'urut' => 9,
            'db' => 'default',
            'nama' => 'FARM TUBAN 1 & 2',
            'mitra' => array(
                '26M0716' => array(
                    'kandang' => array(1),
                ),
                '26M0717' => array(
                    'kandang' => array(1),
                ),
            )
        ),
        10 => array(
            'urut' => 10,
            'db' => 'mgb',
            'nama' => 'FARM TUBAN 3',
            'mitra' => array(
                '25M1712' => array(
                    'kandang' => array(1, 2),
                ),
            )
        ),
        11 => array(
            'urut' => 11,
            'db' => 'mgb',
            'nama' => 'FARM TUBAN 4',
            'mitra' => array(
                '25M1720' => array(
                    'kandang' => array(1, 2),
                ),
            )
        ),
    );

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Halaman utama report performa internal.
     * URL: report/RiwayatPerformanceInternal
     */
    public function index()
    {
        $tgl = $this->resolveTgl();

        // fragment (dipanggil AJAX): kirim HANYA daftar kartu, tanpa app-shell.
        // Query berat (semua farm) hanya jalan di sini, bukan saat load pertama.
        if ($this->input->get('fragment')) {
            echo $this->load->view($this->pathView . 'list_body', array(
                'data' => $this->getData($tgl),
                'tgl'  => $tgl,
            ), TRUE);
            return;
        }

        // shell: render instan (tanpa query) -> skeleton -> data ditarik via AJAX
        $this->load->view($this->pathView . 'index', array('tgl' => $tgl));
    }

    /**
     * Tanggal batas ("s/d") dari query string ?tgl=YYYY-MM-DD.
     * Semua metrik diambil sebagai kondisi PADA / SEBELUM tanggal ini.
     * Jika kosong / format salah -> hari ini (perilaku = sebelum ada filter).
     *
     * @return string YYYY-MM-DD
     */
    private function resolveTgl()
    {
        $tgl = $this->input->get('tgl');

        if (is_string($tgl) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) {
            list($y, $m, $d) = array_map('intval', explode('-', $tgl));
            if (checkdate($m, $d, $y)) {
                return $tgl;
            }
        }

        return date('Y-m-d');
    }

    /**
     * Susun data per FARM (grup) -> per KANDANG mengikuti konfigurasi $farm_group.
     *
     * @param string $tgl tanggal batas (s/d)
     */
    private function getData($tgl)
    {
        $data = array();

        foreach ($this->farm_group as $key => $farm) {
            $row = array(
                'urut'    => $farm['urut'],
                'nama'    => $farm['nama'],
                'kandang' => array(),
            );

            foreach ($farm['mitra'] as $nomor => $mitra) {
                foreach ($mitra['kandang'] as $no_kandang) {
                    $row['kandang'][] = $this->getPerforma($farm['db'], $nomor, $no_kandang, $tgl);
                }
            }

            $data[] = $row;
        }

        // urutkan sesuai 'urut'
        usort($data, function ($a, $b) {
            return $a['urut'] - $b['urut'];
        });

        return $data;
    }

    /**
     * Muat semua standar budidaya yang aktif PADA tanggal batas ke cache (per umur).
     *
     * @param string $tgl tanggal batas (s/d)
     */
    private function loadAllStandar($tgl)
    {
        if ($this->standarCache !== null) {
            return;
        }

        $this->standarCache = array();
        $m_conf = new \Model\Storage\Conf();

        $sqlHeader = "select top 1 id from standart_budidaya
                      where mulai <= convert(date, ?)
                        and (selesai is null or selesai >= convert(date, ?))
                        -- and g_status = 3
                      order by mulai desc, nomor desc";
        $d_header = $m_conf->hydrateRaw($sqlHeader, array($tgl, $tgl), 'default');

        if ($d_header->count() == 0) {
            return;
        }

        $id_budidaya = $d_header->toArray()[0]['id'];

        $sqlDetails = "select umur, bb, dg, fcr, ip from det_standart_budidaya
                       where id_budidaya = ?
                       order by umur asc";
        $d_details = $m_conf->hydrateRaw($sqlDetails, array($id_budidaya), 'default');

        foreach ($d_details->toArray() as $row) {
            $this->standarCache[$row['umur']] = $row;
        }
    }

    /**
     * Ambil standar budidaya untuk umur tertentu.
     *
     * @param int    $umur
     * @param string $tgl  tanggal batas (s/d) — menentukan standar mana yang aktif
     * @return array|null
     */
    private function getStandarPerUmur($umur, $tgl)
    {
        $this->loadAllStandar($tgl);

        if (isset($this->standarCache[$umur])) {
            return $this->standarCache[$umur];
        }

        return null;
    }

    /**
     * Ambil 1 baris performa untuk satu mitra + kandang pada siklus AKTIF.
     *
     * TODO: SUMBER DATA belum ditentukan (bukan rhpp_inti). Sementara mengembalikan
     * struktur field lengkap bernilai null supaya layout bisa dirender. Setelah
     * sumber & rumus (BW/DG/FCR/IP/deplesi) fix, isi query di sini — hormati
     * parameter $db (koneksi 'default' vs 'mgb') pakai:
     *     $DB = $this->load->database($db, TRUE);
     *
     * @param string $db        nama koneksi database ('default' | 'mgb')
     * @param string $mitra      nomor mitra (mis. '25M0483')
     * @param int    $no_kandang nomor kandang (mis. 1)
     * @param string $tgl        tanggal batas (s/d)
     * @return array
     */
    private function getPerforma($db, $mitra, $no_kandang, $tgl)
    {
        $nama_mitra = $this->getNamaMitra($db, $mitra);
        $label = trim($nama_mitra) . ' - KDG ' . $no_kandang;

        $info   = $this->getInfoSiklus($db, $mitra, $no_kandang, $tgl);
        $metrik = $this->getMetrikLhk($db, $info['noreg'], $tgl);

        $std = $this->getStandarPerUmur($metrik['umur'], $tgl);

        return array(
            'db'           => $db,
            'mitra'        => $mitra,
            'no_kandang'   => $no_kandang,
            'label'        => $label,
            'noreg'        => $info['noreg'],
            'populasi'     => $info['populasi'],
            'tgl_chick_in' => $info['tgl_chick_in'],
            'ppl'          => $info['ppl'],
            'supervisor'   => $info['supervisor'],
            'umur'         => $metrik['umur'],
            'bw'           => $metrik['bw'],
            'dg'           => $metrik['dg'],
            'fcr'          => $metrik['fcr'],
            'ekor_mati'    => $metrik['ekor_mati'],
            'ip'           => $metrik['ip'],
            'keterangan'   => $metrik['keterangan'],
            // standar bb & dg dalam GRAM, LHK dalam KG -> setarakan ke kg utk perbandingan warna
            'std_bw'       => isset($std['bb']) ? (float)$std['bb'] / 1000 : null,
            'std_dg'       => isset($std['dg']) ? (float)$std['dg'] / 1000 : null,
            'std_fcr'      => $std['fcr'] ?? null,
            'std_ip'       => $std['ip'] ?? null,
        );
    }

    /**
     * Nama mitra (mis. "GMP KDR 2") dari tabel mitra pada koneksi yang sesuai.
     * mitra ter-versi (banyak baris per nomor) -> ambil yang terbaru (id desc).
     *
     * @param string $db    'default' | 'mgb'
     * @param string $mitra nomor mitra
     * @return string
     */
    private function getNamaMitra($db, $mitra)
    {
        $m_conf = new \Model\Storage\Conf();

        $sql = "select top 1 nama from mitra where nomor = '" . $mitra . "' order by id desc";
        $d_conf = $m_conf->hydrateRaw($sql, array(), $db);

        if ($d_conf->count() > 0) {
            return $d_conf->toArray()[0]['nama'];
        }

        return $mitra; // fallback kalau tidak ketemu
    }

    /**
     * Info siklus TERBARU untuk mitra + kandang: populasi, tgl chick-in,
     * PPL (rdim_submit.sampling) & supervisor (rdim_submit.pengawas).
     *
     * Populasi/tgl: rencana dari rdim_submit (rs.populasi, rs.tgl_docin); jika
     * DOC sudah diterima (ada terima_doc utk noreg tsb) pakai realisasi:
     * populasi = sum(terima_doc.jml_ekor), tgl = terima_doc.datang (paling awal).
     * terima_doc diambil versi terbaru per no_order lalu dipetakan ke noreg
     * lewat order_doc (pola sama dengan RealisasiChickIn).
     * PPL & supervisor = nama karyawan (join nik, versi terbaru per nik).
     *
     * Batas tanggal: hanya siklus yang tgl chick-in (rs.tgl_docin) <= tgl yang
     * dipertimbangkan, lalu diambil yang paling baru -> siklus yang aktif pada
     * tanggal batas (bisa siklus lama, bukan yang sekarang).
     *
     * @param string $db         'default' | 'mgb'
     * @param string $mitra      nomor mitra
     * @param int    $no_kandang nomor kandang
     * @param string $tgl        tanggal batas (s/d)
     * @return array ['populasi', 'tgl_chick_in', 'ppl', 'supervisor']
     */
    private function getInfoSiklus($db, $mitra, $no_kandang, $tgl)
    {
        $m_conf = new \Model\Storage\Conf();

        $sql = "
            select top 1
                rs.noreg,
                rs.tgl_docin as rcn_tgl,
                rs.populasi  as rcn_ekor,
                td.datang    as real_tgl,
                td.jml_ekor  as real_ekor,
                k_ppl.nama   as ppl,
                k_spv.nama   as supervisor
            from rdim_submit rs
            left join
                kandang kdg
                on
                    rs.kandang = kdg.id
            left join
                mitra_mapping mm
                on
                    kdg.mitra_mapping = mm.id
            left join
                mitra m
                on
                    mm.mitra = m.id
            left join
                (
                    select k1.* from karyawan k1
                    right join
                        (select max(id) as id, nik from karyawan group by nik) k2
                        on
                            k1.id = k2.id
                ) k_ppl
                on
                    k_ppl.nik = rs.sampling
            left join
                (
                    select k1.* from karyawan k1
                    right join
                        (select max(id) as id, nik from karyawan group by nik) k2
                        on
                            k1.id = k2.id
                ) k_spv
                on
                    k_spv.nik = rs.pengawas
            left join
                (
                    select
                        od.noreg,
                        min(td.datang) as datang,
                        sum(td.jml_ekor) as jml_ekor
                    from
                    (
                        select td1.* from terima_doc td1
                        right join
                            (select max(version) as version, no_order from terima_doc group by no_order) td2
                            on
                                td1.version = td2.version and
                                td1.no_order = td2.no_order
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
                            td.no_order = od.no_order
                    group by
                        od.noreg
                ) td
                on
                    rs.noreg = td.noreg
            where
                m.nomor = '" . $mitra . "' and
                kdg.kandang = '" . $no_kandang . "' and
                convert(date, rs.tgl_docin) <= convert(date, ?)
            order by
                rs.tgl_docin desc
        ";
        $d_conf = $m_conf->hydrateRaw($sql, array($tgl), $db);

        $res = array(
            'noreg'        => null,
            'populasi'     => null,
            'tgl_chick_in' => null,
            'ppl'          => null,
            'supervisor'   => null,
        );

        if ($d_conf->count() > 0) {
            $row = $d_conf->toArray()[0];

            // pakai realisasi terima_doc jika ada, selain itu rencana rdim_submit
            $ada_terima_doc = ($row['real_ekor'] !== null);

            $res['noreg']        = $row['noreg'];
            $res['populasi']     = $ada_terima_doc ? $row['real_ekor'] : $row['rcn_ekor'];
            $tgl                 = $ada_terima_doc ? $row['real_tgl']  : $row['rcn_tgl'];
            $res['tgl_chick_in'] = !empty($tgl) ? substr($tgl, 0, 10) : null;
            $res['ppl']          = $row['ppl'];
            $res['supervisor']   = $row['supervisor'];
        }

        return $res;
    }

    /**
     * Metrik performa dari baris lhk TERAKHIR (umur tertinggi) untuk noreg.
     * lhk: bb (BW), fcr, ip, ekor_mati, umur, keterangan.
     * DG dihitung: BB LHK terakhir - BB LHK sebelumnya (konsisten StandarBudidaya calcDG).
     *
     * @param string $db    'default' | 'mgb'
     * @param string $noreg noreg siklus
     * @param string $tgl   tanggal batas (s/d) — LHK hanya s/d tanggal ini
     * @return array ['umur','bw','dg','fcr','ekor_mati','ip','keterangan']
     */
    private function getMetrikLhk($db, $noreg, $tgl)
    {
        $res = array(
            'umur'       => null,
            'bw'         => null,
            'dg'         => null,
            'fcr'        => null,
            'ekor_mati'  => null,
            'ip'         => null,
            'keterangan' => null,
        );

        if (empty($noreg)) {
            return $res;
        }

        $m_conf = new \Model\Storage\Conf();

        // ambil semua LHK (urut umur desc). DG = BB LHK terakhir - BB penimbangan sebelumnya.
        // pembanding = baris pertama dgn umur LEBIH KECIL dan bb > 0 (skip umur sama & bb 0).
        $sql = "
            select
                umur,
                bb,
                fcr,
                ip,
                ekor_mati,
                keterangan
            from lhk
            where
                noreg = '" . $noreg . "' and
                convert(date, tanggal) <= convert(date, ?)
            order by
                umur desc,
                id desc
        ";
        $d_conf = $m_conf->hydrateRaw($sql, array($tgl), $db);

        if ($d_conf->count() > 0) {
            $rows = $d_conf->toArray();
            $last = $rows[0];

            $prev_bb = 0;
            foreach ($rows as $r) {
                if ((int)$r['umur'] < (int)$last['umur'] && (float)$r['bb'] > 0) {
                    $prev_bb = (float)$r['bb'];
                    break;
                }
            }

            $res['umur']       = $last['umur'];
            $res['bw']         = $last['bb'];
            $res['dg']         = ($prev_bb > 0) ? ((float)$last['bb'] - $prev_bb) : null;
            $res['fcr']        = $last['fcr'];
            $res['ekor_mati']  = $last['ekor_mati'];
            $res['ip']         = $last['ip'];
            $res['keterangan'] = $last['keterangan'];
        }

        // cetak_r( $res );

        return $res;
    }

    /**
     * Halaman detail: seluruh riwayat LHK untuk 1 kandang (mitra + no_kandang).
     * Navigasi tab yang sama; kembali via tombol back / link topbar.
     * URL: report/RiwayatPerformanceInternal/detail/{db}/{mitra}/{no_kandang}
     */
    public function detail($db = 'default', $mitra = null, $no_kandang = null)
    {
        if ($db != 'mgb') {
            $db = 'default';
        }

        $tgl  = $this->resolveTgl();
        $info = $this->getInfoSiklus($db, $mitra, $no_kandang, $tgl);

        $content['label'] = trim($this->getNamaMitra($db, $mitra)) . ' - KDG ' . $no_kandang;
        $content['info']  = $info;
        $content['tgl']   = $tgl;
        $content['lhk']   = $this->getAllLhk($db, $info['noreg'], $tgl);

        // fragment isi detail (dipakai overlay via AJAX & full-page)
        $body = $this->load->view($this->pathView . 'detail_body', $content, TRUE);

        if ($this->input->get('ajax')) {
            // dipanggil overlay: cukup kirim fragment
            echo $body;
        } else {
            // akses URL langsung / non-JS: bungkus full-page
            $this->load->view($this->pathView . 'detail', array('body' => $body, 'tgl' => $tgl));
        }
    }

    /**
     * Seluruh baris LHK untuk noreg (urut umur naik), lengkap DG per baris.
     * DG baris = BB penimbangan ini - BB penimbangan sebelumnya (bb > 0).
     *
     * @param string $db    'default' | 'mgb'
     * @param string $noreg
     * @param string $tgl   tanggal batas (s/d) — LHK hanya s/d tanggal ini
     * @return array
     */
    private function getAllLhk($db, $noreg, $tgl)
    {
        if (empty($noreg)) {
            return array();
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                umur,
                tanggal,
                bb,
                fcr,
                ip,
                ekor_mati,
                keterangan
            from lhk
            where
                noreg = '" . $noreg . "' and
                convert(date, tanggal) <= convert(date, ?)
            order by
                umur asc,
                id asc
        ";
        $d_conf = $m_conf->hydrateRaw($sql, array($tgl), $db);

        $rows = array();
        if ($d_conf->count() > 0) {
            $prev_bb = null;
            foreach ($d_conf->toArray() as $r) {
                $bb = (float)$r['bb'];
                $dg = null;
                if ($bb > 0) {
                    $dg = ($prev_bb !== null) ? ($bb - $prev_bb) : null;
                    $prev_bb = $bb;
                }
                $r['dg'] = $dg;

                $std = $this->getStandarPerUmur($r['umur'], $tgl);
                // standar bb & dg dalam GRAM, LHK dalam KG -> setarakan ke kg utk perbandingan warna
                $r['std_bw']  = isset($std['bb']) ? (float)$std['bb'] / 1000 : null;
                $r['std_dg']  = isset($std['dg']) ? (float)$std['dg'] / 1000 : null;
                $r['std_fcr'] = $std['fcr'] ?? null;
                $r['std_ip']  = $std['ip'] ?? null;

                $rows[] = $r;
            }
        }

        return $rows;
    }
}
