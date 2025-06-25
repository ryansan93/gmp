<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PindahBudidaya extends Public_Controller {

    private $pathView = 'transaksi/pindah_budidaya/';
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
    public function index($segment=0)
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/transaksi/pindah_budidaya/js/pindah-budidaya.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/pindah_budidaya/css/pindah-budidaya.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;

            $a_content['unit'] = $this->get_unit();
            $content['add_form'] = $this->load->view($this->pathView.'add_form', $a_content, TRUE);

            // Load Indexx
            $data['title_menu'] = 'Pindah Budidaya';
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_unit()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'UN')->orderBy('nama', 'asc')->get()->toArray();

        return $d_wilayah;
    }

    public function get_data_asal()
    {
        $params = $this->input->post('params');

        try {
            $_data = null;

            $start_date = $params['tanggal'].' 00:00:00.000';
            $end_date = $params['tanggal'].' 23:59:59.999';

            $m_td = new \Model\Storage\TerimaDoc_model();
            $d_td = $m_td->where('no_order', 'like', '%'.$params['unit'].'%')->whereBetween('datang', [$start_date, $end_date])->get();

            if ( $d_td->count() > 0 ) {
                $d_td = $d_td->toArray();

                foreach ($d_td as $k_td => $v_td) {
                    $m_od = new \Model\Storage\OrderDoc_model();
                    $d_od = $m_od->where('no_order', $v_td['no_order'])->first();

                    if ( $d_od ) {
                        $m_rs = new \Model\Storage\RdimSubmit_model();
                        $d_rs = $m_rs->where('noreg', $d_od->noreg)->with(['mitra'])->first();

                        if ( $d_rs ) {
                            $d_rs = $d_rs->toArray();

                            $_data[ $d_rs['noreg'] ] = array(
                                'noreg' => $d_rs['noreg'],
                                'mitra' => $d_rs['mitra']['d_mitra']['nama'],
                                'populasi' => $v_td['jml_ekor']
                            );
                        }
                    }
                }
            }

            $data = null;
            if ( !empty($_data) ) {
                foreach ($_data as $key => $value) {
                    $data[] = $value;
                }
            }
            
            $this->result['status'] = 1;
            $this->result['data'] = $data;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function get_data_tujuan()
    {
        $params = $this->input->post('params');

        try {
            $_data = null;
            
            $m_wilayah = new \Model\Storage\Wilayah_model();
            $d_wilayah = $m_wilayah->select('id')->where('kode', $params['unit'])->get()->toArray();

            $m_kdg = new \Model\Storage\Kandang_model();
            $d_kdg = $m_kdg->select('mitra_mapping')->whereIn('unit', $d_wilayah)->get();

            if ( $d_kdg->count() > 0 ) {
                $d_kdg = $d_kdg->toArray();

                $m_mm = new \Model\Storage\MitraMapping_model();
                $d_mm = $m_mm->whereIn('id', $d_kdg)->with(['dMitra'])->get();

                if ( $d_mm->count() > 0 ) {
                    $d_mm = $d_mm->toArray();

                    foreach ($d_mm as $k_mm => $v_mm) {
                        $_data[ $v_mm['d_mitra']['nama'].'|'.$v_mm['nomor'] ] = array(
                            'nomor' => $v_mm['nomor'],
                            'mitra' => $v_mm['d_mitra']['nama']
                        );
                    }
                }
            }

            $mitra = null;
            if ( !empty($_data) ) {
                ksort($_data);
                foreach ($_data as $key => $value) {
                    $mitra[] = $value;
                }
            }

            $karyawan = $this->get_karyawan($params['unit']);
            $kontrak = $this->get_kontrak($params['unit']);

            $data = array(
                'mitra' => $mitra,
                'karyawan' => $karyawan,
                'kontrak' => $kontrak
            );

            cetak_r( $data, 1 );
            
            $this->result['status'] = 1;
            $this->result['data'] = $data;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function get_data_kandang_tujuan()
    {
        $params = $this->input->post('params');

        try {
            $_data = null;
            
            $m_mitra = new \Model\Storage\Mitra_model();
            $d_mitra = $m_mitra->where('nomor', $params['mitra'])->orderBy('id', 'desc')->first()->toArray();

            $m_mm = new \Model\Storage\MitraMapping_model();
            $d_mm = $m_mm->where('mitra', $d_mitra['id'])->get()->toArray();

            foreach ($d_mm as $k_mm => $v_mm) {
                $m_kdg = new \Model\Storage\Kandang_model();
                $d_kdg = $m_kdg->where('mitra_mapping', $v_mm['id'])->with(['bangunans'])->get()->toArray();

                foreach ($d_kdg as $k_kdg => $v_kdg) {
                    $total_luas = 0;
                    foreach ($v_kdg['bangunans'] as $bangunan) {
                        $panjang = $bangunan['meter_panjang'] ?: 0;
                        $lebar = $bangunan['meter_lebar'] ?: 0;
                        $jml = $bangunan['jumlah_unit'] ?: 0;
                        $luas = ( $panjang * $lebar ) * $jml;

                        $total_luas += $luas;
                    }
                    $kapasitas = $v_kdg['ekor_kapasitas'];

                    $_data[] = array(
                        'id' => $v_kdg['id'],
                        'nim' => $v_mm['nim'],
                        'kandang' => $v_kdg['kandang'],
                        'group' => $v_kdg['grup'],
                        'densitas' => ($kapasitas > 0 && $total_luas > 0) ? ($kapasitas / $total_luas) : 0,
                        'tipe_kandang' => $v_kdg['tipe']
                    );
                }
            }

            $data = null;
            if ( !empty($_data) ) {
                ksort($_data);
                foreach ($_data as $key => $value) {
                    $data[] = $value;
                }
            }
            
            $this->result['status'] = 1;
            $this->result['data'] = $data;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function get_karyawan($unit)
    {
        $m_unit = new \Model\Storage\Wilayah_model();
        $d_unit = $m_unit->select('id')->where('kode', $unit)->get()->toArray();

        $m_uk = new \Model\Storage\UnitKaryawan_model();
        $d_uk = $m_uk->select('id_karyawan')->distinct('id_karyawan')->whereIn('unit', $d_unit)->get()->toArray();

        $ppl = array();
        if ( !empty($d_uk) ) {
            foreach ($d_uk as $k_uk => $v_uk) {
                $m_k = new \Model\Storage\Karyawan_model();
                $d_k = $m_k->where('id', $v_uk['id_karyawan'])->where('jabatan', 'ppl')->first();

                if ( !empty($d_k) ) {
                    array_push($ppl, $d_k->toArray());
                }
            }
        }

        $kanit = array();
        if ( !empty($d_uk) ) {
            foreach ($d_uk as $k_uk => $v_uk) {
                $m_k = new \Model\Storage\Karyawan_model();
                $d_k = $m_k->where('id', $v_uk['id_karyawan'])->where('jabatan', 'kepala unit')->first();

                if ( !empty($d_k) ) {
                    array_push($kanit, $d_k->toArray());
                }
            }
        }

        $marketing = array();
        if ( !empty($d_uk) ) {
            foreach ($d_uk as $k_uk => $v_uk) {
                $m_k = new \Model\Storage\Karyawan_model();
                $d_k = $m_k->where('id', $v_uk['id_karyawan'])->where('jabatan', 'marketing')->first();

                if ( !empty($d_k) ) {
                    array_push($marketing, $d_k->toArray());
                }
            }
        }

        $koordinator = array();
        if ( !empty($d_uk) ) {
            foreach ($d_uk as $k_uk => $v_uk) {
                $m_k = new \Model\Storage\Karyawan_model();
                $d_k = $m_k->where('id', $v_uk['id_karyawan'])->where('jabatan', 'koordinator')->first();

                if ( !empty($d_k) ) {
                    array_push($koordinator, $d_k->toArray());
                }
            }
        }

        $data_karyawan = array(
            'kanit' => $kanit,
            'ppl' => $ppl,
            'marketing' => $marketing,
            'koordinator' => $koordinator
        );

        return $data_karyawan;
    }

    public function get_kontrak($unit)
    {
        $m_unit = new \Model\Storage\Wilayah_model();
        $d_unit = $m_unit->where('kode', $unit)->orderBy('id', 'desc')->first();

        $m_pwk = new \Model\Storage\Wilayah_model();
        $d_pwk = $m_pwk->where('id', $d_unit->induk)->first();

        $m_pm = new \Model\Storage\PerwakilanMaping_model();
        $d_pm = $m_pm->where('id_pwk', $d_pwk->id)->with(['hitung_budidaya_item_kpm'])->get();

        $_data = null;
        if ( $d_pm->count() > 0 ) {
            $d_pm = $d_pm->toArray();

            foreach ($d_pm as $k_pm => $v_pm) {
                $m_perusahaan = new \Model\Storage\Perusahaan_model();
                $d_perusahaan = $m_perusahaan->where('kode', $v_pm['hitung_budidaya_item_kpm']['d_sapronak_kesepakatan_no_check']['perusahaan'])->orderBy('id', 'desc')->first();

                $_data[ $v_pm['hitung_budidaya_item_kpm']['d_sapronak_kesepakatan_no_check']['nomor'] ] = array(
                    'id' => $v_pm['hitung_budidaya_item_kpm']['d_sapronak_kesepakatan_no_check']['id'],
                    'nomor' => $v_pm['hitung_budidaya_item_kpm']['d_sapronak_kesepakatan_no_check']['nomor'],
                    'pola' => $v_pm['hitung_budidaya_item_kpm']['d_sapronak_kesepakatan_no_check']['pola'],
                    'item_pola' => $v_pm['hitung_budidaya_item_kpm']['d_sapronak_kesepakatan_no_check']['item_pola'],
                    'mulai' => $v_pm['hitung_budidaya_item_kpm']['d_sapronak_kesepakatan_no_check']['mulai'],
                    'pola_kerjasama' => $v_pm['hitung_budidaya_item_kpm']['d_sapronak_kesepakatan_no_check']['pola_kerjasama'],
                    'nama_perusahaan' => $d_perusahaan->perusahaan,
                    'id_perwakilan_mapping' => $v_pm['id']
                );
            }
        }

        return $_data;
    }

    public function generateNoreg($nim = null, $kandang = null, $tgl_docin = null)
    {
        $noreg = null;

        $m_mmp = new \Model\Storage\MitraMapping_model();
        $d_mmp = $m_mmp->select('id')->where('nim', trim($nim))->get();

        $d_kandang = null;

        $d_rdim = null;
        if ( $d_mmp->count() > 0 ) {
            $d_mmp = $d_mmp->toArray();

            $m_kandang = new \Model\Storage\Kandang_model();
            $d_kandang = $m_kandang->select('id')->whereIn('mitra_mapping', $d_mmp)
                                   ->where('kandang', number_format($kandang))
                                   ->get();

            if ( $d_kandang->count() > 0 ) {
                $d_kandang = $d_kandang->toArray();

                // $_nim = substr($nim, 0, 2) . substr($nim, 4);
                $_nim = trim($nim);

                $m_rdim = new \Model\Storage\RdimSubmit_model();
                $d_rdim = $m_rdim->where('nim', trim($nim))
                                 ->whereIn('kandang', $d_kandang)
                                 ->orderBy('tgl_docin', 'DESC')
                                 ->first();
            }
        }

        if ( empty($d_rdim) ) {
            $str_kandang = null;
            if ( strlen(number_format($kandang)) == 1) {
                $str_kandang = '0'.number_format($kandang);
            } else {
                $str_kandang = $kandang;
            }
            
            $noreg = trim($_nim) . '01' . $str_kandang;
        } else {
            $m_rdim = new \Model\Storage\RdimSubmit_model();
            $d_rdim_tanggal = $m_rdim->where('nim', trim($nim))
                             ->whereIn('kandang', $d_kandang)
                             ->where('tgl_docin', $tgl_docin)
                             ->orderBy('id', 'DESC')
                             ->first();

            if ( !empty($d_rdim_tanggal) ) {
                $noreg = $d_rdim_tanggal['noreg'];
            } else {
                $_noreg = $d_rdim['noreg'];
                $jml_nim = strlen(trim($nim));

                $_siklus = trim(substr($_noreg, $jml_nim, 2));
                $siklus = $_siklus + 1;

                $str_siklus = null;
                if ( strlen($siklus) == 1) {
                    $str_siklus = '0'.$siklus;
                } else {
                    $str_siklus = $siklus;
                }

                $str_kandang = null;
                if ( strlen(number_format($kandang)) == 1) {
                    $str_kandang = '0'.number_format($kandang);
                } else {
                    $str_kandang = $kandang;
                }

                $noreg = $_nim . $str_siklus . $str_kandang;                
            }
        }

        return $noreg;
    } // end - generateNoreg

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $noreg_tujuan = $this->generateNoreg($params['tujuan']['nim'], $params['tujuan']['kandang'], $params['asal']['tgl_docin']);

            cetak_r( $noreg_tujuan, 1 );

            // $m_pb = new \Model\Storage\PindahBudidaya_model();
            // $m_pb->tgl_docin = $params['asal']['tgl_docin'];
            // $m_pb->tgl_pindah = $params['tujuan']['tgl_pindah'];
            // $m_pb->noreg_asal = $params['asal']['noreg'];
            // $m_pb->noreg_tujuan = $noreg_tujuan;
            // $m_pb->populasi_awal = $params['asal']['populasi'];
            // $m_pb->populasi_pindah = $params['tujuan']['populasi'];
            // $m_pb->save();

            // $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            // Modules::run( 'base/event/save', $m_pb, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}