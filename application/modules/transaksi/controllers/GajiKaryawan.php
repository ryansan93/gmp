<?php defined('BASEPATH') or exit('No direct script access allowed');

class GajiKaryawan extends Public_Controller
{
    private $pathView = 'transaksi/gaji_karyawan/';
    private $jenis = 'karyawan';
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

    public function index()
    {
        if ( $this->akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/transaksi/gaji_karyawan/js/gaji-karyawan.js"
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/gaji_karyawan/css/gaji-karyawan.css"
            ));
            $data = $this->includes;

            $content['akses'] = $this->akses;
            $content['title_panel'] = 'Gaji Karyawan';

            $content['riwayat'] = $this->riwayat();
            $content['addForm'] = $this->addForm();

            // Load Indexx
            $data['title_menu'] = 'Gaji Karyawan';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getBulan() {
        $bulan = null;
        $bulan[1] = 'JANUARI';
        $bulan[2] = 'FEBRUARI';
        $bulan[3] = 'MARET';
        $bulan[4] = 'APRIL';
        $bulan[5] = 'MEI';
        $bulan[6] = 'JUNI';
        $bulan[7] = 'JULI';
        $bulan[8] = 'AGUSTUS';
        $bulan[9] = 'SEPTEMBER';
        $bulan[10] = 'OKTOBER';
        $bulan[11] = 'NOVEMBER';
        $bulan[12] = 'DESEMBER';

        return $bulan;
    }

    public function getUnit() {
        $data = null;
        
        $m_prs = new \Model\Storage\Conf();
        $sql = "
            select prs1.* from perusahaan prs1
            right join
                (select max(id) as id, kode from perusahaan group by kode) prs2
                on
                    prs1.id = prs2.id
        ";
        $d_prs = $m_prs->hydrateRaw( $sql );

        $kode_gmk = null; $nama_gmk = null; $alias_gmk = null;
        $kode_gml = null; $nama_gml = null; $alias_gml = null;
        $kode_ma = null; $nama_ma = null; $alias_ma = null;

        if ( $d_prs->count() > 0 ) {
            $d_prs = $d_prs->toArray();

            foreach ($d_prs as $k_prs => $v_prs) {
                if ( stristr($v_prs['perusahaan'], 'gemuk') !== FALSE ) {
                    $kode_gmk = $v_prs['kode'];
                    $nama_gmk = $v_prs['perusahaan'];
                    $alias_gmk = $v_prs['alias'];
                }
                if ( stristr($v_prs['perusahaan'], 'gemilang') !== FALSE ) {
                    $kode_gml = $v_prs['kode'];
                    $nama_gml = $v_prs['perusahaan'];
                    $alias_gml = $v_prs['alias'];
                }
                if ( stristr($v_prs['perusahaan'], 'marga') !== FALSE ) {
                    $kode_ma = $v_prs['kode'];
                    $nama_ma = $v_prs['perusahaan'];
                    $alias_ma = $v_prs['alias'];
                }
            }
        }

        $nama = 'PUSAT GEMUK';
        $kode = 'pusat';
        $data[ '1 - '.$nama.' - '.$kode ] = array(
            'nama' => $nama,
            'kode' => $kode,
            'kode_prs' => $kode_gmk,
            'nama_prs' => $nama_gmk,
            'alias_prs' => $alias_gmk
        );

        $nama = 'PUSAT GEMILANG';
        $kode = 'pusat_gml';
        $data[  '1 - '.$nama.' - '.$kode ] = array(
            'nama' => $nama,
            'kode' => $kode,
            'kode_prs' => $kode_gml,
            'nama_prs' => $nama_gml,
            'alias_prs' => $alias_gml
        );

        $nama = 'PUSAT MA';
        $kode = 'pusat_ma';
        $data[  '1 - '.$nama.' - '.$kode ] = array(
            'nama' => $nama,
            'kode' => $kode,
            'kode_prs' => $kode_ma,
            'nama_prs' => $nama_ma,
            'alias_prs' => $alias_ma
        );

        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'UN')->orderBy('nama', 'asc')->get();
        if ( $d_wilayah->count() > 0 ) {
            $d_wilayah = $d_wilayah->toArray();

            foreach ($d_wilayah as $k_wil => $v_wil) {
                $nama = trim(str_replace('KAB ', '', str_replace('KOTA ', '', strtoupper($v_wil['nama']))));
                if ( in_array($v_wil['kode'], array('BWI', 'JBR', 'LMJ', 'PRB', 'PSR')) ) {
                    $data[ '2 - '.$alias_gmk.' - '.$nama.' - '.$v_wil['kode'] ] = array(
                        'nama' => $nama,
                        'kode' => $v_wil['kode'],
                        'kode_prs' => $kode_gmk,
                        'nama_prs' => $nama_gmk,
                        'alias_prs' => $alias_gmk
                    );
                } else if ( in_array($v_wil['kode'], array('MLG', 'KDR', 'TAG', 'MJK')) ) {
                    $data[ '3 - '.$nama_ma.' - '.$nama.' - '.$v_wil['kode'] ] = array(
                        'nama' => $nama,
                        'kode' => $v_wil['kode'],
                        'kode_prs' => $kode_ma,
                        'nama_prs' => $nama_ma,
                        'alias_prs' => $alias_ma
                    );
                } else {
                    $data[ '2 - '.$nama_gml.' - '.$nama.' - '.$v_wil['kode'] ] = array(
                        'nama' => $nama,
                        'kode' => $v_wil['kode'],
                        'kode_prs' => $kode_gml,
                        'nama_prs' => $nama_gml,
                        'alias_prs' => $alias_gml
                    );
                }
            }
        }

        ksort( $data );

        return $data;
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

    public function loadForm()
    {
        $params = $this->input->get('params');
        $edit = $this->input->get('edit');

        $periode = $params['periode'];
        $perusahaan = $params['perusahaan'];

        $content = array();
        $html = "url not found";
        
        if ( !empty($periode) && !empty($perusahaan) && $edit != 'edit' ) {
            // NOTE: view data BASTTB (ajax)
            $html = $this->viewForm($periode, $perusahaan);
        } else if ( !empty($periode) && !empty($perusahaan) && $edit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $html = $this->editForm($periode, $perusahaan);
        }else{
            $html = $this->addForm();
        }

        echo $html;
    }

    public function getLists() {
        $params = $this->input->get('params');

        $bulan = $params['bulan'];
        $tahun = $params['tahun'];
        $perusahaan = $params['perusahaan'];

        $start_date = null;
        $end_date = null;
        if ( stristr( $bulan, 'all' ) === FALSE ) {
            $bulan = (strlen($params['bulan']) == 1) ? '0'.$params['bulan'] : $params['bulan'];

            $start_date = $tahun.'-'.$bulan.'-01';
            $end_date = date("Y-m-t", strtotime($start_date));
        } else {
            $start_date = $tahun.'-01-01';
            $end_date = date("Y-m-t", strtotime($tahun.'-12-01'));
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                gk.periode as bulan,
                prs.kode_gabung_perusahaan as perusahaan,
                sum(gk.tot_gaji) as tot_gaji,
                sum(gk.bpjs_karyawan) as bpjs_karyawan,
                sum(gk.pot_hutang) as pot_hutang,
                sum(gk.pph21) as pph21,
                sum(gk.jml_transfer) as jml_transfer,
                sum(gk.bpjs_perusahaan) as bpjs_perusahaan,
                gk.tgl_transfer
            from gaji_karyawan gk
            left join
                (
                    select prs1.* from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    gk.perusahaan = prs.kode
            where
                prs.kode_gabung_perusahaan = '".$perusahaan."' and
                gk.periode between '".$start_date."' and '".$end_date."'
            group by
                gk.periode, prs.kode_gabung_perusahaan, gk.tgl_transfer
            order by
                gk.periode asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $data[ $value['bulan'] ] = array(
                    'bulan' => $this->getBulan()[(int)substr($value['bulan'], 5, 2)],
                    'periode' => $value['bulan'],
                    'perusahaan' => $value['perusahaan'],
                    'tot_gaji' => $value['tot_gaji'],
                    'bpjs_karyawan' => $value['bpjs_karyawan'],
                    'pot_hutang' => $value['pot_hutang'],
                    'pph21' => $value['pph21'],
                    'jml_transfer' => $value['jml_transfer'],
                    'bpjs_perusahaan' => $value['bpjs_perusahaan'],
                    'tgl_transfer' => $value['tgl_transfer']
                );
            }
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list', $content, TRUE);

        echo $html;
    }

    public function riwayat() {
        $content['akses'] = $this->akses;
        $content['bulan'] = $this->getBulan();
        $content['perusahaan'] = $this->getPerusahaan();
        $html = $this->load->view($this->pathView . 'riwayat', $content, TRUE);

        return $html;
    }

    public function addForm() {
        $content['bulan'] = $this->getBulan();
        $content['unit'] = $this->getUnit();

        $html = $this->load->view($this->pathView . 'addForm', $content, TRUE);

        return $html;
    }

    public function viewForm( $periode, $perusahaan ) {
        $start_date = $periode;
        $end_date = date("Y-m-t", strtotime($start_date));

        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                gk.periode as bulan,
                sum(gk.tot_gaji) as gt_gaji,
                sum(gk.bpjs_karyawan) as gt_bpjs_karyawan,
                sum(gk.pot_hutang) as gt_potongan_hutang,
                sum(gk.pph21) as gt_pph21_karyawan,
                sum(gk.jml_transfer) as gt_jumlah_transfer,
                sum(gk.bpjs_perusahaan) as gt_bpjs_perusahaan
            from gaji_karyawan gk
            left join
                (
                    select prs1.* from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    gk.perusahaan = prs.kode
            where
                prs.kode_gabung_perusahaan = '".$perusahaan."' and
                gk.periode between '".$start_date."' and '".$end_date."'
            group by
                gk.periode, gk.tgl_transfer
            order by
                gk.periode
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray()[0];

            $data['total'] = array(
                'gt_gaji' => $d_conf['gt_gaji'],
                'gt_bpjs_karyawan' => $d_conf['gt_bpjs_karyawan'],
                'gt_potongan_hutang' => $d_conf['gt_potongan_hutang'],
                'gt_pph21_karyawan' => $d_conf['gt_pph21_karyawan'],
                'gt_jumlah_transfer' => $d_conf['gt_jumlah_transfer'],
                'gt_bpjs_perusahaan' => $d_conf['gt_bpjs_perusahaan']
            );
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                gk.*
            from gaji_karyawan gk
            left join
                perusahaan prs
                on
                    gk.perusahaan = prs.kode
            where
                prs.kode_gabung_perusahaan = '".$perusahaan."' and
                gk.periode between '".$periode."' and '".$periode."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );
        
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                if ( $value['tot_gaji'] > 0 ) {
                    $key = $value['unit'].'-'.$value['perusahaan'];
    
                    $data[ $key ] = $value;
                }
            }
        }

        // cetak_r( $this->getUnit() );
        // cetak_r( $data, 1 );

        $content['akses'] = $this->akses;
        $content['data'] = $data;
        $content['periode'] = $periode;
        $content['perusahaan'] = $perusahaan;
        $content['bulan'] = $this->getBulan();
        $content['unit'] = $this->getUnit();
        $html = $this->load->view($this->pathView . 'viewForm', $content, TRUE);

        return $html;
    }

    public function editForm( $periode, $perusahaan ) {
        $start_date = $periode;
        $end_date = date("Y-m-t", strtotime($start_date));

        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                gk.periode as bulan,
                sum(gk.tot_gaji) as gt_gaji,
                sum(gk.bpjs_karyawan) as gt_bpjs_karyawan,
                sum(gk.pot_hutang) as gt_potongan_hutang,
                sum(gk.pph21) as gt_pph21_karyawan,
                sum(gk.jml_transfer) as gt_jumlah_transfer,
                sum(gk.bpjs_perusahaan) as gt_bpjs_perusahaan
            from gaji_karyawan gk
            left join
                (
                    select prs1.* from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    gk.perusahaan = prs.kode
            where
                prs.kode_gabung_perusahaan = '".$perusahaan."' and
                gk.periode between '".$start_date."' and '".$end_date."'
            group by
                gk.periode, gk.tgl_transfer
            order by
                gk.periode
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray()[0];

            $data['total'] = array(
                'gt_gaji' => $d_conf['gt_gaji'],
                'gt_bpjs_karyawan' => $d_conf['gt_bpjs_karyawan'],
                'gt_potongan_hutang' => $d_conf['gt_potongan_hutang'],
                'gt_pph21_karyawan' => $d_conf['gt_pph21_karyawan'],
                'gt_jumlah_transfer' => $d_conf['gt_jumlah_transfer'],
                'gt_bpjs_perusahaan' => $d_conf['gt_bpjs_perusahaan']
            );
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                gk.*
            from gaji_karyawan gk
            left join
                perusahaan prs
                on
                    gk.perusahaan = prs.kode
            where
                prs.kode_gabung_perusahaan = '".$perusahaan."' and
                gk.periode between '".$periode."' and '".$periode."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );
        
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $key = $value['unit'].'-'.$value['perusahaan'];

                $data[ $key ] = $value;
            }
        }

        $content['data'] = $data;
        $content['periode'] = $periode;
        $content['perusahaan'] = $perusahaan;
        $content['bulan'] = $this->getBulan();
        $content['unit'] = $this->getUnit();
        $html = $this->load->view($this->pathView . 'editForm', $content, TRUE);

        return $html;
    }

    public function save() {
        $params = $this->input->post('params');

        try {
            $periode = null;
            $kode_perusahaan = null;
            foreach ($params as $key => $value) {
                $m_gk = new \Model\Storage\GajiKaryawan_model();
                $now = $m_gk->getDate();

                $tgl_trans = $now['waktu'];

                $bulan = (strlen($value['bulan']) == 1) ? '0'.$value['bulan'] : $value['bulan'];
                $tahun = $value['tahun'];

                $periode = $tahun.'-'.$bulan.'-01';

                $m_gk->periode = $periode;
                $m_gk->tanggal_trans = $tgl_trans;
                $m_gk->perusahaan = $value['perusahaan'];
                $m_gk->unit = $value['unit'];
                $m_gk->tot_gaji = $value['tot_gaji'];
                $m_gk->bpjs_karyawan = $value['bpjs_karyawan'];
                $m_gk->pot_hutang = $value['pot_hutang'];
                $m_gk->pph21 = $value['pph21'];
                $m_gk->jml_transfer = $value['jml_transfer'];
                $m_gk->bpjs_perusahaan = $value['bpjs_perusahaan'];
                $m_gk->tgl_transfer = $value['tgl_transfer'];
                $m_gk->save();

                $m_conf = new \Model\Storage\Conf();
                $sql = "exec insert_jurnal null, null, null, 0, 'gaji_karyawan', ".$m_gk->id.", null, 1";
                $d_conf = $m_conf->hydrateRaw( $sql );

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $m_gk, $deskripsi_log );

                $kode_perusahaan = $value['perusahaan'];
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    prs1.kode_gabung_perusahaan
                from perusahaan prs1
                right join
                    (select max(id) as id, kode from perusahaan group by kode) prs2
                    on
                        prs1.id = prs2.id
                where
                    prs1.kode = '".$kode_perusahaan."'
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
            
            $perusahaan = null;
            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray()[0];

                $perusahaan = $d_conf['kode_gabung_perusahaan'];
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
            $this->result['content'] = array(
                'periode' => $periode,
                'perusahaan' => $perusahaan
            );
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit() {
        $params = $this->input->post('params');
        $periode_before = $this->input->post('periode_before');

        try {
            $periode = null;
            $kode_perusahaan = null;
            foreach ($params as $key => $value) {
                $m_gk = new \Model\Storage\GajiKaryawan_model();
                $now = $m_gk->getDate();

                $tgl_trans = $now['waktu'];

                $bulan = (strlen($value['bulan']) == 1) ? '0'.$value['bulan'] : $value['bulan'];
                $tahun = $value['tahun'];

                $periode = $tahun.'-'.$bulan.'-01';

                $_d_gk_last = $m_gk->where('periode', $periode)->where('unit', $value['unit'])->where('perusahaan', $value['perusahaan'])->orderBy('id', 'desc')->first();
                $_d_gk = $m_gk->where('periode', $periode)->where('unit', $value['unit'])->where('perusahaan', $value['perusahaan'])->where('id', '<>', $_d_gk_last->id)->get();

                if ( $_d_gk->count() > 0 ) {
                    $_d_gk = $_d_gk->toArray();

                    foreach ($_d_gk as $k_gk => $v_gk) {
                        $m_conf = new \Model\Storage\Conf();
                        $sql = "exec insert_jurnal null, null, null, 0, 'gaji_karyawan', ".$v_gk['id'].", ".$v_gk['id'].", 3";
                        $d_conf = $m_conf->hydrateRaw( $sql );

                        $m_gk = new \Model\Storage\GajiKaryawan_model();
                        $m_gk->where('id', $v_gk['id'])->delete();
                    }

                }

                $m_gk = new \Model\Storage\GajiKaryawan_model();
                $m_gk->where('id', $_d_gk_last->id)->update(
                    array(
                        'periode' => $periode,
                        'tanggal_trans' => $tgl_trans,
                        'perusahaan' => $value['perusahaan'],
                        'unit' => $value['unit'],
                        'tot_gaji' => $value['tot_gaji'],
                        'bpjs_karyawan' => $value['bpjs_karyawan'],
                        'pot_hutang' => $value['pot_hutang'],
                        'pph21' => $value['pph21'],
                        'jml_transfer' => $value['jml_transfer'],
                        'bpjs_perusahaan' => $value['bpjs_perusahaan'],
                        'tgl_transfer' => $value['tgl_transfer']
                    )
                );

                $d_gk = $m_gk->where('id', $_d_gk_last->id)->first();

                $m_conf = new \Model\Storage\Conf();
                $sql = "exec insert_jurnal null, null, null, 0, 'gaji_karyawan', ".$_d_gk_last->id.", ".$_d_gk_last->id.", 2";
                $d_conf = $m_conf->hydrateRaw( $sql );

                $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $d_gk, $deskripsi_log );

                $kode_perusahaan = $value['perusahaan'];
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    prs1.kode_gabung_perusahaan
                from perusahaan prs1
                right join
                    (select max(id) as id, kode from perusahaan group by kode) prs2
                    on
                        prs1.id = prs2.id
                where
                    prs1.kode = '".$kode_perusahaan."'
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
            
            $perusahaan = null;
            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray()[0];

                $perusahaan = $d_conf['kode_gabung_perusahaan'];
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di ubah.';
            $this->result['content'] = array(
                'periode' => $periode,
                'perusahaan' => $perusahaan
            );
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete() {
        $params = $this->input->post('params');

        try {
            $periode = $params['periode'];
            $kode_gabung_perusahaan = $params['perusahaan'];

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    prs1.kode
                from perusahaan prs1
                right join
                    (select max(id) as id, kode from perusahaan group by kode) prs2
                    on
                        prs1.id = prs2.id
                where
                    prs1.kode_gabung_perusahaan = '".$kode_gabung_perusahaan."'
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
            
            $perusahaan = null;
            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $key => $value) {
                    $perusahaan[] = $value['kode'];
                }
            }

            $m_gk = new \Model\Storage\GajiKaryawan_model();
            $d_gk = $m_gk->where('periode', $periode)->whereIn('perusahaan', $perusahaan)->get();
            if ( $d_gk->count() > 0 ) {
                $d_gk = $d_gk->toArray();

                foreach ($d_gk as $key => $value) {
                    $m_conf = new \Model\Storage\Conf();
                    $sql = "exec insert_jurnal null, null, null, 0, 'gaji_karyawan', ".$value['id'].", null, 3";
                    $d_conf = $m_conf->hydrateRaw( $sql );
                }
            }

            $m_gk->where('periode', $periode)->whereIn('perusahaan', $perusahaan)->delete();

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                gk.*
            from gaji_karyawan gk
            left join
                (
                    select prs1.* from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    gk.perusahaan = prs.kode
            where
                prs.kode_gabung_perusahaan = '1' and
                gk.periode between '2025-04-01' and '2025-04-01' and
                gk.tot_gaji > 0
        ";
        $d_conf = $m_conf->hydrateRaw($sql);

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $m_conf = new \Model\Storage\Conf();
                $sql = "exec insert_jurnal null, null, null, 0, 'gaji_karyawan', ".$value['id'].", ".$value['id'].", 2";
                $d_conf = $m_conf->hydrateRaw( $sql );
            }
        }
    }
}