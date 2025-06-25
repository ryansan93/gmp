<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PenjualanPeralatan extends Public_Controller {

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
                "assets/select2/js/select2.min.js",
                "assets/transaksi/penjualan_peralatan/js/penjualan-peralatan.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/penjualan_peralatan/css/penjualan-peralatan.css",
            ));

            $data = $this->includes;

            $mitra = $this->get_mitra();
            $peralatan = $this->get_peralatan();

            $content['akses'] = $akses;

            $content['riwayat'] = $this->riwayat($mitra);
            $content['add_form'] = $this->add_form($mitra, $peralatan);

            // Load Indexx
            $data['title_menu'] = 'Penjualan Peralatan';
            $data['view'] = $this->load->view('transaksi/penjualan_peralatan/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function riwayat($mitra)
    {
        $content['data_mitra'] = $mitra;
        $html = $this->load->view('transaksi/penjualan_peralatan/riwayat', $content, TRUE);

        return $html;
    }

    public function list_riwayat()
    {
        $params = $this->input->post('params');

        $m_pp = new \Model\Storage\PenjualanPeralatan_model();
        $d_pp = $m_pp->where('mitra', $params['mitra'])->get();

        $content['data'] = ($d_pp->count() > 0) ? $d_pp->toArray() : null;

        $html = $this->load->view('transaksi/penjualan_peralatan/list_riwayat', $content, TRUE);

        $this->result['html'] = $html;

        display_json( $this->result );
    }

    public function load_form()
    {
        $params = $this->input->post('params');

        $html = null;
        if ( empty($params['id']) && empty($params['edit']) ) {
            $data_mitra = $this->get_mitra();
            $data_peralatan = $this->get_peralatan();
            $html = $this->add_form( $data_mitra, $data_peralatan );
        } else if ( !empty($params['id']) && empty($params['edit']) ) {
            $html = $this->detail_form( $params['id'] );
        } else if ( !empty($params['id']) && !empty($params['edit']) ) {
            $data_mitra = $this->get_mitra();
            $data_peralatan = $this->get_peralatan();
            $html = $this->edit_form( $params['id'], $data_mitra, $data_peralatan );
        }

        $this->result['html'] = $html;

        display_json( $this->result );
    }

    public function add_form($mitra, $peralatan)
    {
        $content['data_mitra'] = $mitra;
        $content['data_peralatan'] = $peralatan;
        $html = $this->load->view('transaksi/penjualan_peralatan/add_form', $content, true);

        return $html;
    }

    public function detail_form($id)
    {
        $m_pp = new \Model\Storage\PenjualanPeralatan_model();
        $d_pp = $m_pp->where('id', $id)->with(['d_mitra', 'detail'])->first();

        $content['data'] = !empty($d_pp) ? $d_pp->toArray() : null;
        $html = $this->load->view('transaksi/penjualan_peralatan/detail_form', $content, true);

        return $html;
    }

    public function edit_form($id, $mitra, $peralatan)
    {
        $m_pp = new \Model\Storage\PenjualanPeralatan_model();
        $d_pp = $m_pp->where('id', $id)->with(['d_mitra', 'detail'])->first();

        $content['data'] = !empty($d_pp) ? $d_pp->toArray() : null;
        $content['data_mitra'] = $mitra;
        $content['data_peralatan'] = $peralatan;
        $html = $this->load->view('transaksi/penjualan_peralatan/edit_form', $content, true);

        return $html;
    }

    public function get_unit()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'UN')->orderBy('nama', 'asc')->get()->toArray();

        return $d_wilayah;
    }

    public function get_mitra()
    {
        $data = array();

        $m_duser = new \Model\Storage\DetUser_model();
        $d_duser = $m_duser->where('id_user', $this->userid)->first();

        $m_karyawan = new \Model\Storage\Karyawan_model();
        $d_karyawan = $m_karyawan->where('nama', 'like', strtolower(trim($d_duser->nama_detuser)).'%')->first();

        $kode_unit = null;
        $kode_unit_all = null;
        if ( $d_karyawan ) {
            $m_ukaryawan = new \Model\Storage\UnitKaryawan_model();
            $d_ukaryawan = $m_ukaryawan->where('id_karyawan', $d_karyawan->id)->get()->toArray();

            foreach ($d_ukaryawan as $k_ukaryawan => $v_ukaryawan) {
                if ( $v_ukaryawan['unit'] != 'all' ) {
                    $m_wil = new \Model\Storage\Wilayah_model();
                    $d_wil = $m_wil->where('id', $v_ukaryawan['unit'])->first();

                    $kode_unit[ $d_wil->kode ] = $d_wil->kode;
                } else {
                    $kode_unit_all = $v_ukaryawan['unit'];
                }
            }
        } else {
            $kode_unit_all = 'all';
        }

        
        $m_mm = new \Model\Storage\MitraMapping_model();
        $d_mm = $m_mm->with(['dMitra'])->orderBy('id', 'desc')->get();

        if ( $d_mm->count() > 0 ) {
            $d_mm = $d_mm->toArray();

            foreach ($d_mm as $k_mm => $v_mm) {
                $m_kdg = new \Model\Storage\Kandang_model();
                $d_kdg = $m_kdg->where('mitra_mapping', $v_mm['id'])->with(['d_unit'])->first();

                if ( $d_kdg ) {
                    $key = $d_kdg->d_unit->kode.' | '.$v_mm['d_mitra']['nama'].' | '.$v_mm['d_mitra']['nomor'];
                    if ( empty($kode_unit_all) ) {
                        foreach ($kode_unit as $k_ku => $v_ku) {
                            if ( $v_ku == $d_kdg->d_unit->kode ) {
                                $data[ $key ] = array(
                                    'nomor' => $v_mm['d_mitra']['nomor'],
                                    'nama' => $v_mm['d_mitra']['nama'],
                                    'unit' => $d_kdg->d_unit->kode
                                );
                            }
                        }
                    } else {
                        $data[ $key ] = array(
                            'nomor' => $v_mm['d_mitra']['nomor'],
                            'nama' => $v_mm['d_mitra']['nama'],
                            'unit' => $d_kdg->d_unit->kode
                        );
                    }
                }
            }
        }

        ksort($data);

        /* GET ALL MITRA */
        // $m_mitra = new \Model\Storage\Mitra_model();
        // $_d_mitra = $m_mitra->select('nomor')->distinct('nomor')->get();

        // if ( $_d_mitra->count() > 0 ) {
        //     $_d_mitra = $_d_mitra->toArray();
        //     foreach ($_d_mitra as $k_mitra => $v_mitra) {
        //         $d_mitra = $m_mitra->select('nama', 'nomor')->where('nomor', $v_mitra['nomor'])->orderBy('id', 'desc')->first();

        //         $m_mm = new \Model\Storage\MitraMapping_model();
        //         $d_mm = $m_mm->where('nomor', $d_mitra->nomor)->orderBy('id', 'desc')->first();

        //         if ( $d_mm ) {
        //             $m_kdg = new \Model\Storage\Kandang_model();
        //             $d_kdg = $m_kdg->where('mitra_mapping', $d_mm->id)->with(['d_unit'])->first();

        //             $key = $d_mitra->nama.' | '.$d_mitra->nomor;
        //             if ( empty($kode_unit_all) ) {
        //                 foreach ($kode_unit as $k_ku => $v_ku) {
        //                     if ( $v_ku == $d_kdg->d_unit->kode ) {
        //                         $data[ $key ] = array(
        //                             'nomor' => $d_mitra->nomor,
        //                             'nama' => $d_mitra->nama,
        //                             'unit' => $d_kdg->d_unit->kode
        //                         );
        //                     }
        //                 }
        //             } else {
        //                 $data[ $key ] = array(
        //                     'nomor' => $d_mitra->nomor,
        //                     'nama' => $d_mitra->nama,
        //                     'unit' => $d_kdg->d_unit->kode
        //                 );
        //             }
        //         }
        //     }

        //     ksort($data);
        // }

        return $data;
    }

    public function get_peralatan()
    {
        $m_barang = new \Model\Storage\Barang_model();
        $d_barang = $m_barang->select('kode')->where('tipe', 'peralatan')->orderBy('nama', 'asc')->get();

        $data = null;
        if ( $d_barang->count() > 0 ) {
            foreach ($d_barang as $k_brg => $v_brg) {
                $d_brg = $m_barang->where('kode', $v_brg['kode'])->where('tipe', 'peralatan')->orderBy('version', 'desc')->first();

                if ( $d_brg ) {
                    $data[] = $d_brg;
                }
            }
        }

        return $data;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_pp = new \Model\Storage\PenjualanPeralatan_model();

            $nomor = $m_pp->getNextNomor();

            $m_pp->nomor = $nomor;
            $m_pp->mitra = $params['mitra'];
            $m_pp->tanggal = $params['tanggal'];
            $m_pp->total = $params['total'];
            $m_pp->sisa = $params['total'];
            // $m_pp->bayar = $params['bayar'];
            $m_pp->status = 'BELUM';
            $m_pp->save();

            $id_header = $m_pp->id;

            foreach ($params['data_brg'] as $k_detail => $v_detail) {
                $m_ppd = new \Model\Storage\PenjualanPeralatanDetail_model();
                $m_ppd->id_header = $id_header;
                $m_ppd->item = $v_detail['kode_brg'];
                $m_ppd->jumlah = $v_detail['jumlah'];
                $m_ppd->harga = $v_detail['harga'];
                $m_ppd->total = $v_detail['total'];
                $m_ppd->save();
            }

            $d_pp = $m_pp->where('id', $id_header)->first();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_pp, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Penjualan Peralatan berhasil di simpan.';
            $this->result['content'] = array('id' => $id_header);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit()
    {
        $params = $this->input->post('params');

        try {
            $m_bpp = new \Model\Storage\BayarPenjualanPeralatan_model();
            $jumlah_bayar = $m_bpp->where('id_penjualan_peralatan', $params['id'])->sum('bayar');

            $sisa_bayar = ($params['total'] < $jumlah_bayar) ? 0 : $params['total'] - $jumlah_bayar;

            $m_pp = new \Model\Storage\PenjualanPeralatan_model();
            $m_pp->where('id', $params['id'])->update(
                array(
                    'mitra' => $params['mitra'],
                    'tanggal' => $params['tanggal'],
                    'total' => $params['total'],
                    'sisa' => $sisa_bayar,
                    // 'bayar' => $params['bayar'],
                    // 'status' => ($params['sisa_bayar'] > 0) ? 'BELUM' : 'LUNAS',
                )
            );

            $id_header = $params['id'];

            $m_ppd = new \Model\Storage\PenjualanPeralatanDetail_model();
            $m_ppd->where('id_header', $id_header)->delete();

            foreach ($params['data_brg'] as $k_detail => $v_detail) {
                $m_ppd = new \Model\Storage\PenjualanPeralatanDetail_model();
                $m_ppd->id_header = $id_header;
                $m_ppd->item = $v_detail['kode_brg'];
                $m_ppd->jumlah = $v_detail['jumlah'];
                $m_ppd->harga = $v_detail['harga'];
                $m_ppd->total = $v_detail['total'];
                $m_ppd->save();
            }

            $d_pp = $m_pp->where('id', $id_header)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_pp, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Penjualan Peralatan berhasil di-update.';
            $this->result['content'] = array('id' => $id_header);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {
            $m_pp = new \Model\Storage\PenjualanPeralatan_model();
            $d_pp = $m_pp->where('id', $params['id'])->first();

            $m_pp->where('id', $params['id'])->delete();
            $m_ppd = new \Model\Storage\PenjualanPeralatanDetail_model();
            $m_ppd->where('id_header', $params['id'])->delete();

            $deskripsi_log = 'hapus data Penjualan Peralatan oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_pp, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus';           
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function mappingFiles($files)
    {
        $mappingFiles = [];
        foreach ($files['tmp_name'] as $key => $file) {
            $sha1 = sha1_file($file);
            $index = $key;
            $mappingFiles[$index] = [
                'name' => $files['name'][$key],
                'tmp_name' => $file,
                'type' => $files['type'][$key],
                'size' => $files['size'][$key],
                'error' => $files['error'][$key]
            ];
        }
        
        return $mappingFiles;
    }
}