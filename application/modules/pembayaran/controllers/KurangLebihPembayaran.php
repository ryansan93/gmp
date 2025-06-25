<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KurangLebihPembayaran extends Public_Controller
{
    private $url;
    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
    }

    public function index()
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                'assets/pembayaran/kurang_lebih_pembayaran/js/kurang-lebih-pembayaran.js'
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/pembayaran/kurang_lebih_pembayaran/css/kurang-lebih-pembayaran.css'
            ));

            $data = $this->includes;

            $data['title_menu'] = 'Kurang / Lebih Pembayaran';

            $mitra = null;
            $perusahaan = $this->get_perusahaan();

            $content['add_form'] = $this->add_form($mitra, $perusahaan);
            $content['riwayat'] = $this->riwayat($mitra, $perusahaan);

            $content['akses'] = $akses;
            $data['view'] = $this->load->view('pembayaran/kurang_lebih_pembayaran/index', $content, true);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function load_form()
    {
        $params = $this->input->get('params');
        $edit = $this->input->get('edit');

        $id = $params['id'];

        $content = array();
        $html = "url not found";
        
        if ( !empty($id) && $edit != 'edit' ) {
            // NOTE: view data BASTTB (ajax)
            $html = $this->detail_form( $id );
        } else if ( !empty($id) && $edit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $perusahaan = $this->get_perusahaan();
            $html = $this->edit_form($id, $perusahaan);
        }else{
            $perusahaan = $this->get_perusahaan();
            $html = $this->add_form(null, $perusahaan);
        }

        echo $html;
    }

    public function get_lists()
    {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $kode_perusahaan = null;

        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        foreach ($params['perusahaan'] as $k => $val) {
            $d_perusahaan = null;
            if ( $val != 'all' ) {
                $d_perusahaan = $m_perusahaan->select('kode')->where('kode', $val)->groupBy('kode')->get();
            } else {
                $d_perusahaan = $m_perusahaan->select('kode')->groupBy('kode')->get();
            }

            if ( !empty($d_perusahaan) ) {
                $d_perusahaan = $d_perusahaan->toArray();

                foreach ($d_perusahaan as $k_perusahaan => $v_perusahaan) {
                    $kode_perusahaan[] = $v_perusahaan['kode'];
                }
            }
        }

        $m_rp = new \Model\Storage\RealisasiPembayaran_model();
        $d_rp = $m_rp->whereBetween('tgl_bayar', [$start_date, $end_date])
                     ->whereIn('perusahaan', $kode_perusahaan)->orderBy('tgl_bayar', 'desc')->with(['d_perusahaan', 'd_supplier', 'd_mitra', 'detail'])->get();

        $data = null;
        if ( $d_rp->count() > 0 ) {
            $d_rp = $d_rp->toArray();

            foreach ($d_rp as $k_rp => $v_rp) {
                $jumlah = 0;
                foreach ($v_rp['detail'] as $k_det => $v_det) {
                    $jumlah += $v_det['bayar'];
                }

                $data[ $k_rp ] = array(
                    'id' => $v_rp['id'],
                    'tgl_bayar' => $v_rp['tgl_bayar'],
                    'nomor' => $v_rp['nomor'],
                    'd_perusahaan' => $v_rp['d_perusahaan'],
                    'supplier' => $v_rp['supplier'],
                    'd_supplier' => $v_rp['d_supplier'],
                    'peternak' => $v_rp['peternak'],
                    'd_peternak' => $v_rp['d_mitra'],
                    'lampiran' => $v_rp['lampiran'],
                    'no_bukti' => $v_rp['no_bukti'],
                    'jumlah' => $jumlah
                );
            }
        }

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/kurang_lebih_pembayaran/list_riwayat', $content, true);

        echo $html;
    }

    public function get_unit()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'UN')->orderBy('nama', 'asc')->get();

        $data = null;
        if ( $d_wilayah->count() > 0 ) {
            $d_wilayah = $d_wilayah->toArray();

            foreach ($d_wilayah as $k_wil => $v_wil) {
                $nama = trim(str_replace('KAB ', '', str_replace('KOTA ', '', strtoupper($v_wil['nama']))));
                $data[ $nama.' - '.$v_wil['kode'] ] = array(
                    'nama' => $nama,
                    'kode' => $v_wil['kode']
                );
            }

            ksort($data);
        }

        return $data;
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

    public function get_supplier()
    {
        $m_supplier = new \Model\Storage\Supplier_model();
        $nomor_supplier = $m_supplier->select('nomor')->distinct('nomor')->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->get();

        $data = null;
        if ( $nomor_supplier->count() > 0 ) {
            $nomor_supplier = $nomor_supplier->toArray();

            foreach ($nomor_supplier as $k => $val) {
                $m_supplier = new \Model\Storage\Supplier_model();
                $d_supplier = $m_supplier->where('nomor', $val['nomor'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('version', 'desc')->first();

                $key = strtoupper($d_supplier->nama).' - '.$d_supplier['nomor'];
                $data[ $key ] = array(
                    'nama' => strtoupper($d_supplier->nama),
                    'nomor' => $d_supplier->nomor
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function get_mitra()
    {
        $params = $this->input->post('params');

        $m_duser = new \Model\Storage\DetUser_model();
        $d_duser = $m_duser->where('id_user', $this->userid)->first();

        $m_karyawan = new \Model\Storage\Karyawan_model();
        $d_karyawan = $m_karyawan->where('nama', 'like', strtolower(trim($d_duser->nama_detuser)).'%')->first();

        $kode_unit = array();
        $kode_unit_all = null;
        if ( !empty( $params['kode_unit'] ) ) {
            foreach ($params['kode_unit'] as $k_ku => $v_ku) {
                if ( stristr($v_ku, 'all') !== FALSE ) {
                    $kode_unit_all = 'all';

                    break;
                } else {
                    array_push($kode_unit, $v_ku);
                }
            }
        }

        $m_mitra = new \Model\Storage\Mitra_model();
        $_d_mitra = $m_mitra->select('nomor')->distinct('nomor')->get();

        $_data = array();
        if ( $_d_mitra->count() > 0 ) {
            $_d_mitra = $_d_mitra->toArray();
            foreach ($_d_mitra as $k_mitra => $v_mitra) {
                $d_mitra = $m_mitra->select('nama', 'nomor')->where('nomor', $v_mitra['nomor'])->orderBy('id', 'desc')->first();

                $m_mm = new \Model\Storage\MitraMapping_model();
                $d_mm = $m_mm->where('nomor', $d_mitra->nomor)->orderBy('id', 'desc')->first();

                if ( $d_mm ) {
                    $m_kdg = new \Model\Storage\Kandang_model();
                    $d_kdg = $m_kdg->where('mitra_mapping', $d_mm->id)->with(['d_unit'])->first();

                    $key = $d_mitra->nama.' | '.$d_mitra->nomor;
                    if ( empty($kode_unit_all) ) {
                        foreach ($kode_unit as $k_ku => $v_ku) {
                            if ( $v_ku == $d_kdg->d_unit->kode ) {
                                $_data[ $key ] = array(
                                    'nomor' => $d_mitra->nomor,
                                    'nama' => $d_mitra->nama,
                                    'unit' => $d_kdg->d_unit->kode
                                );
                            }
                        }
                    } else {
                        $_data[ $key ] = array(
                            'nomor' => $d_mitra->nomor,
                            'nama' => $d_mitra->nama,
                            'unit' => $d_kdg->d_unit->kode
                        );
                    }
                }
            }

            ksort($_data);
        }

        $data = array();
        if ( count( $_data ) ) {
            foreach ($_data as $k_data => $v_data) {
                $data[] = $v_data;
            }
        }

        $this->result['content'] = $data;

        display_json( $this->result );
    }

    public function get_data_rencana_bayar()
    {
        $params = $this->input->post('params');

        $data = array();
        if ( $params['jenis_pembayaran'] == 'plasma' ) {
            // PETERNAK
            $m_kpp = new \Model\Storage\KonfirmasiPembayaranPeternak_model();
            $d_kpp = $m_kpp->whereBetween('tgl_bayar', [$params['start_date'], $params['end_date']])
                           ->whereIn('mitra', $params['mitra'])
                           ->where('perusahaan', $params['perusahaan'])->get();

            if ( $d_kpp->count() > 0 ) {
                $d_kpp = $d_kpp->toArray();

                foreach ($d_kpp as $k_kpp => $v_kpp) {
                    $m_mitra = new \Model\Storage\Mitra_model();
                    $d_mitra = $m_mitra->where('nomor', $v_kpp['mitra'])->orderBy('version', 'desc')->first();

                    $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
                    $bayar = $m_rpd->where('no_bayar', $v_kpp['nomor'])->sum('bayar');

                    $data[] = array(
                        'tgl_bayar' => $v_kpp['tgl_bayar'],
                        'transaksi' => 'PLASMA',
                        'no_bayar' => $v_kpp['nomor'],
                        'periode' => $v_kpp['periode'],
                        'nama_penerima' => $d_mitra->nama,
                        'tagihan' => $v_kpp['total'],
                        'bayar' => $bayar,
                        'jumlah' => ($v_kpp['total'] > $bayar) ? $v_kpp['total'] - $bayar : 0
                    );
                }
            }
        } else {
            if ( $params['jenis_transaksi'][0] == 'all' ) {
                $doc = $this->get_rencana_pembayaran_doc( $params );
                if ( count($doc) > 0 ) {
                    foreach ($doc as $k => $v) {
                        $data[] = $v;
                    }
                }   
                $pakan = $this->get_rencana_pembayaran_pakan( $params );
                if ( count($pakan) > 0 ) {
                    foreach ($pakan as $k => $v) {
                        $data[] = $v;
                    }
                }
                $voadip = $this->get_rencana_pembayaran_voadip( $params );
                if ( count($voadip) > 0 ) {
                    foreach ($voadip as $k => $v) {
                        $data[] = $v;
                    }
                }
            } else {
                foreach ($params['jenis_transaksi'] as $k_jt => $v_jt) {
                    if ( $v_jt == 'doc' ) {
                        // DOC
                        $doc = $this->get_rencana_pembayaran_doc( $params );
                        if ( count($doc) > 0 ) {
                            foreach ($doc as $k => $v) {
                                $data[] = $v;
                            }
                        }
                    }
                    if ( $v_jt == 'pakan' ) {
                        // PAKAN
                        $pakan = $this->get_rencana_pembayaran_pakan( $params );
                        if ( count($pakan) > 0 ) {
                            foreach ($pakan as $k => $v) {
                                $data[] = $v;
                            }
                        }
                    }
                    if ( $v_jt == 'voadip' ) {
                        // VOADIP
                        $voadip = $this->get_rencana_pembayaran_voadip( $params );
                        if ( count($voadip) > 0 ) {
                            foreach ($voadip as $k => $v) {
                                $data[] = $v;
                            }
                        }
                    }
                }
            }
        }
        
        $content['data'] = $data;
        $html = $this->load->view('pembayaran/kurang_lebih_pembayaran/list_rencana_pembayaran', $content, true);

        $this->result['html'] = $html;

        display_json( $this->result );
    }

    public function get_rencana_pembayaran_doc($params)
    {
        $data = array();

        $m_kpd = new \Model\Storage\KonfirmasiPembayaranDoc_model();
        $d_kpd = $m_kpd->whereBetween('tgl_bayar', [$params['start_date'], $params['end_date']])
                       ->where('supplier', $params['supplier'])
                       ->where('perusahaan', $params['perusahaan'])->get();

        if ( $d_kpd->count() > 0 ) {
            $d_kpd = $d_kpd->toArray();

            foreach ($d_kpd as $k_kpd => $v_kpd) {
                $m_supplier = new \Model\Storage\Supplier_model();
                $d_supplier = $m_supplier->where('tipe', 'supplier')->where('nomor', $params['supplier'])->orderBy('version', 'desc')->first();

                $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
                $bayar = $m_rpd->where('no_bayar', $v_kpd['nomor'])->sum('bayar');

                $data[] = array(
                    'tgl_bayar' => $v_kpd['tgl_bayar'],
                    'transaksi' => 'DOC',
                    'no_bayar' => $v_kpd['nomor'],
                    'periode' => $v_kpd['periode'],
                    'nama_penerima' => $d_supplier->nama,
                    'tagihan' => $v_kpd['total'],
                    'bayar' => $bayar,
                    'jumlah' => ($v_kpd['total'] > $bayar) ? $v_kpd['total'] - $bayar : 0
                );
            }
        }

        return $data;
    }

    public function get_rencana_pembayaran_pakan($params)
    {
        $data = array();

        $m_kpp = new \Model\Storage\KonfirmasiPembayaranPakan_model();
        $d_kpp = $m_kpp->whereBetween('tgl_bayar', [$params['start_date'], $params['end_date']])
                       ->where('supplier', $params['supplier'])
                       ->where('perusahaan', $params['perusahaan'])->get();

        if ( $d_kpp->count() > 0 ) {
            $d_kpp = $d_kpp->toArray();

            foreach ($d_kpp as $k_kpp => $v_kpp) {
                $m_supplier = new \Model\Storage\Supplier_model();
                $d_supplier = $m_supplier->where('tipe', 'supplier')->where('nomor', $params['supplier'])->orderBy('version', 'desc')->first();

                $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
                $bayar = $m_rpd->where('no_bayar', $v_kpp['nomor'])->sum('bayar');

                $data[] = array(
                    'tgl_bayar' => $v_kpp['tgl_bayar'],
                    'transaksi' => 'PAKAN',
                    'no_bayar' => $v_kpp['nomor'],
                    'periode' => $v_kpp['periode'],
                    'nama_penerima' => $d_supplier->nama,
                    'tagihan' => $v_kpp['total'],
                    'bayar' => $bayar,
                    'jumlah' => ($v_kpp['total'] > $bayar) ? $v_kpp['total'] - $bayar : 0
                );
            }
        }

        return $data;
    }

    public function get_rencana_pembayaran_voadip($params)
    {
        $data = array();

        $m_kpv = new \Model\Storage\KonfirmasiPembayaranVoadip_model();
        $d_kpv = $m_kpv->whereBetween('tgl_bayar', [$params['start_date'], $params['end_date']])
                       ->where('supplier', $params['supplier'])
                       ->where('perusahaan', $params['perusahaan'])->get();

        if ( $d_kpv->count() > 0 ) {
            $d_kpv = $d_kpv->toArray();

            foreach ($d_kpv as $k_kpv => $v_kpv) {
                $m_supplier = new \Model\Storage\Supplier_model();
                $d_supplier = $m_supplier->where('tipe', 'supplier')->where('nomor', $params['supplier'])->orderBy('version', 'desc')->first();

                $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
                $bayar = $m_rpd->where('no_bayar', $v_kpv['nomor'])->sum('bayar');

                $data[] = array(
                    'tgl_bayar' => $v_kpv['tgl_bayar'],
                    'transaksi' => 'VOADIP',
                    'no_bayar' => $v_kpv['nomor'],
                    'periode' => $v_kpv['periode'],
                    'nama_penerima' => $d_supplier->nama,
                    'tagihan' => $v_kpv['total'],
                    'bayar' => $bayar,
                    'jumlah' => ($v_kpv['total'] > $bayar) ? $v_kpv['total'] - $bayar : 0
                );
            }
        }

        return $data;
    }

    public function riwayat($mitra, $perusahaan)
    {
        $content['unit'] = $this->get_unit();
        $content['mitra'] = $mitra;
        $content['perusahaan'] = $perusahaan;
        $html = $this->load->view('pembayaran/kurang_lebih_pembayaran/riwayat', $content, true);

        return $html;
    }

    public function add_form($mitra, $perusahaan)
    {
        $content['unit'] = $this->get_unit();
        $content['supplier'] = $this->get_supplier();
        $content['perusahaan'] = $perusahaan;
        $html = $this->load->view('pembayaran/kurang_lebih_pembayaran/add_form', $content, true);

        return $html;
    }

    public function detail_form($id)
    {
        $m_rp = new \Model\Storage\RealisasiPembayaran_model();
        $d_rp = $m_rp->where('id', $id)->with(['d_perusahaan', 'd_supplier', 'd_mitra', 'detail'])->first();

        $data = null;
        if ( $d_rp ) {
            $d_rp = $d_rp->toArray();

            $jumlah = 0;
            $jenis_transaksi = null;
            foreach ($d_rp['detail'] as $k_det => $v_det) {
                $jumlah += $v_det['bayar'];
                $jenis_transaksi[] = $v_det['transaksi'];
            }

            $data = array(
                'id' => $d_rp['id'],
                'tgl_bayar' => $d_rp['tgl_bayar'],
                'no_bayar' => $d_rp['nomor'],
                'jumlah_bayar' => $jumlah,
                'jenis_pembayaran' => !empty($d_rp['supplier']) ? 'SUPPLIER' : 'PLASMA',
                'jenis_transaksi' => implode(', ', $jenis_transaksi),
                'supplier' => $d_rp['d_supplier']['nama'],
                'peternak' => $d_rp['d_mitra']['nama'],
                'perusahaan' => $d_rp['d_perusahaan']['perusahaan'],
                'detail' => $d_rp['detail']
            );
        }

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/kurang_lebih_pembayaran/detail_form', $content, true);

        return $html;
    }

    public function edit_form($id, $perusahaan)
    {
        $m_rp = new \Model\Storage\RealisasiPembayaran_model();
        $d_rp = $m_rp->where('id', $id)->with(['d_perusahaan', 'd_supplier', 'detail'])->first();

        $data = null;
        if ( $d_rp ) {
            $d_rp = $d_rp->toArray();

            $jumlah = 0;
            $jenis_pembayaran = null;
            $jenis_transaksi = null;
            $start_date = null;
            $end_date = null;
            $nama_penerima = null;
            $detail = null;
            foreach ($d_rp['detail'] as $k_det => $v_det) {
                $d_konfirmasi = null;
                $nama_penerima = null;
                if ( stristr($v_det['transaksi'], 'doc') !== false ) {
                    $m_kpd = new \Model\Storage\KonfirmasiPembayaranDoc_model();
                    $d_konfirmasi = $m_kpd->where('nomor', $v_det['no_bayar'])->first();

                    $m_supplier = new \Model\Storage\Supplier_model();
                    $d_supplier = $m_supplier->where('tipe', 'supplier')->where('nomor', $d_konfirmasi->supplier)->orderBy('version', 'desc')->first();

                    $nama_penerima = $d_supplier->nama;
                }
                if ( stristr($v_det['transaksi'], 'pakan') !== false ) {
                    $m_kpp = new \Model\Storage\KonfirmasiPembayaranPakan_model();
                    $d_konfirmasi = $m_kpp->where('nomor', $v_det['no_bayar'])->first();

                    $m_supplier = new \Model\Storage\Supplier_model();
                    $d_supplier = $m_supplier->where('tipe', 'supplier')->where('nomor', $d_konfirmasi->supplier)->orderBy('version', 'desc')->first();

                    $nama_penerima = $d_supplier->nama;
                }
                if ( stristr($v_det['transaksi'], 'voadip') !== false ) {
                    $m_kpv = new \Model\Storage\KonfirmasiPembayaranVoadip_model();
                    $d_konfirmasi = $m_kpv->where('nomor', $v_det['no_bayar'])->first();

                    $m_supplier = new \Model\Storage\Supplier_model();
                    $d_supplier = $m_supplier->where('tipe', 'supplier')->where('nomor', $d_konfirmasi->supplier)->orderBy('version', 'desc')->first();

                    $nama_penerima = $d_supplier->nama;
                }
                if ( stristr($v_det['transaksi'], 'peternak') !== false ) {
                    $m_kpp = new \Model\Storage\KonfirmasiPembayaranPeternak_model();
                    $d_konfirmasi = $m_kpp->where('nomor', $v_det['no_bayar'])->first();

                    $m_mitra = new \Model\Storage\Mitra_model();
                    $d_mitra = $m_mitra->where('nomor', $d_konfirmasi->mitra)->orderBy('version', 'desc')->first();

                    $nama_penerima = $d_mitra->nama;
                }

                if ( !empty($d_konfirmasi) ) {
                    if ( empty($start_date) ) {
                        $start_date = $d_konfirmasi->tgl_bayar;
                    } else {
                        if ( $start_date > $d_konfirmasi->tgl_bayar ) {
                            $start_date = $d_konfirmasi->tgl_bayar;
                        }
                    }
                    if ( empty($end_date) ) {
                        $end_date = $d_konfirmasi->tgl_bayar;
                    } else {
                        if ( $end_date < $d_konfirmasi->tgl_bayar ) {
                            $end_date = $d_konfirmasi->tgl_bayar;
                        }
                    }
                }

                $jumlah += $v_det['bayar'];
                $jenis_transaksi[] = $v_det['transaksi'];

                $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
                $bayar = $m_rpd->where('no_bayar', $v_det['no_bayar'])->where('id_header', '<>', $id)->sum('bayar');

                $detail[] = array(
                    'tgl_rcn_bayar' => $d_konfirmasi->tgl_bayar,
                    'transaksi' => (stristr($v_det['transaksi'], 'peternak') !== false) ? 'PLASMA' : $v_det['transaksi'],
                    'no_bayar' => $v_det['no_bayar'],
                    'periode' => $d_konfirmasi->periode,
                    'nama_penerima' => $nama_penerima,
                    'tagihan' => $v_det['tagihan'],
                    'bayar' => 0,
                    'jumlah' => $v_det['tagihan'],
                );
            }

            $data = array(
                'id' => $d_rp['id'],
                'start_date' => $start_date,
                'end_date' => $end_date,
                'tgl_bayar' => $d_rp['tgl_bayar'],
                'no_bayar' => $d_rp['nomor'],
                'jumlah_bayar' => $jumlah,
                'jenis_pembayaran' => !empty($d_rp['supplier']) ? 'SUPPLIER' : 'PLASMA',
                'jenis_transaksi' => $jenis_transaksi,
                'supplier' => $d_rp['supplier'],
                'unit' => null,
                'peternak' => null,
                'perusahaan' => $d_rp['perusahaan'],
                'detail' => $detail
            );
        }

        $content['unit'] = $this->get_unit();
        $content['supplier'] = $this->get_supplier();
        $content['perusahaan'] = $perusahaan;
        $content['data'] = $data;
        $html = $this->load->view('pembayaran/kurang_lebih_pembayaran/edit_form', $content, true);

        return $html;
    }

    public function realisasi_pembayaran()
    {
        $params = $this->input->get('params');

        $data = null;

        $total = 0;
        $total_bayar = 0;
        $detail = null;
        foreach ($params['detail'] as $k_det => $v_det) {
            $total += $v_det['tagihan'];

            $bayar = 0;

            if ( isset($params['id']) ) {
                $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
                $d_rpd = $m_rpd->where('no_bayar', $v_det['no_bayar'])->where('id_header', $params['id'])->first();

                if ( !empty($d_rpd) ) {
                    $bayar = $d_rpd->bayar;
                    $total_bayar += $d_rpd->bayar;
                }
            }

            $detail[] = array(
                'transaksi' => $v_det['transaksi'],
                'no_bayar' => $v_det['no_bayar'],
                'tagihan' => $v_det['tagihan'],
                'bayar' => $bayar
            );
        }

        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $d_perusahaan = $m_perusahaan->where('kode', $params['perusahaan'])->orderBy('version', 'desc')->first();

        $nomor = null;
        $tgl_bayar = null;
        $rekening = null;
        $no_bukti = null;
        $lampiran = null;
        if ( isset($params['id']) ) {
            $m_rp = new \Model\Storage\RealisasiPembayaran_model();
            $d_rp = $m_rp->where('id', $params['id'])->first();

            $nomor = $d_rp->nomor;
            $tgl_bayar = $d_rp->tgl_bayar;
            $rekening = $d_rp->no_rek;
            $no_bukti = $d_rp->no_bukti;
            $lampiran = $d_rp->lampiran;
        }

        $d_supplier = null;
        $d_mitra = null;
        if ( stristr($params['jenis_pembayaran'], 'supplier') !== false ) {
            $m_supplier = new \Model\Storage\Supplier_model();
            $d_supplier = $m_supplier->where('nomor', $params['supplier'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('version', 'desc')->with(['banks'])->first();
        } else {
            $m_mitra = new \Model\Storage\Mitra_model();
            $d_mitra = $m_mitra->where('nomor', $params['peternak'])->orderBy('version', 'desc')->first();

            $rekening = $d_mitra->rekening_nomor.' - '.$d_mitra->bank;
        }

        $data = array(
            'id' => isset($params['id']) ? $params['id'] : null,
            'jenis_pembayaran' => $params['jenis_pembayaran'],
            'total' => $total,
            'total_bayar' => $total_bayar,
            'nomor' => $nomor,
            'tgl_bayar' => $tgl_bayar,
            'rekening' => $rekening,
            'no_bukti' => $no_bukti,
            'lampiran' => $lampiran,
            'no_perusahaan' => $d_perusahaan->kode,
            'perusahaan' => $d_perusahaan->perusahaan,
            'no_supplier' => !empty($d_supplier) ? $d_supplier->nomor : null,
            'supplier' => !empty($d_supplier) ? $d_supplier->nama : null,
            'bank_supplier' => !empty($d_supplier) ? $d_supplier->banks : null,
            'no_peternak' => !empty($d_mitra) ? $d_mitra->nomor : null,
            'peternak' => !empty($d_mitra) ? $d_mitra->nama : null,
            'detail' => $detail
        );

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/kurang_lebih_pembayaran/realisasi_pembayaran', $content, true);

        echo $html;
    }

    public function save()
    {
        $data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

        try {
            $file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];
            }
            if ($isMoved) {
                $file_name = $moved['name'];
                $path_name = $moved['path'];

                $m_rp = new \Model\Storage\RealisasiPembayaran_model();
                $nomor = $m_rp->getNextNomor();

                $m_rp->nomor = $nomor;
                $m_rp->tgl_bayar = $data['tgl_bayar'];
                $m_rp->perusahaan = $data['perusahaan'];
                $m_rp->supplier = isset($data['supplier']) ? $data['supplier'] : null;
                $m_rp->peternak = isset($data['peternak']) ? $data['peternak'] : null;
                $m_rp->no_rek = $data['no_rek'];
                $m_rp->no_bukti = $data['no_bukti'];
                $m_rp->lampiran = $path_name;
                $m_rp->save();

                $id = $m_rp->id;
                foreach ($data['detail'] as $k_det => $v_det) {
                    $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
                    $m_rpd->id_header = $id;
                    $m_rpd->transaksi = $v_det['transaksi'];
                    $m_rpd->no_bayar = $v_det['no_bayar'];
                    $m_rpd->tagihan = $v_det['tagihan'];
                    $m_rpd->bayar = $v_det['bayar'];
                    $m_rpd->save();

                    if ( stristr($v_det['transaksi'], 'doc') !== false ) {
                        if ( $v_det['tagihan'] == $v_det['bayar'] ) {
                            $m_kpd = new \Model\Storage\KonfirmasiPembayaranDoc_model();
                            $m_kpd->where('nomor', $v_det['no_bayar'])->update(
                                array( 'lunas' => 1 )
                            );
                        }
                    }
                    if ( stristr($v_det['transaksi'], 'pakan') !== false ) {
                        if ( $v_det['tagihan'] == $v_det['bayar'] ) {
                            $m_kpp = new \Model\Storage\KonfirmasiPembayaranPakan_model();
                            $m_kpp->where('nomor', $v_det['no_bayar'])->update(
                                array( 'lunas' => 1 )
                            );
                        }
                    }
                    if ( stristr($v_det['transaksi'], 'voadip') !== false ) {
                        if ( $v_det['tagihan'] == $v_det['bayar'] ) {
                            $m_kpv = new \Model\Storage\KonfirmasiPembayaranVoadip_model();
                            $m_kpv->where('nomor', $v_det['no_bayar'])->update(
                                array( 'lunas' => 1 )
                            );
                        }
                    }
                    if ( stristr($v_det['transaksi'], 'peternak') !== false ) {
                        if ( $v_det['tagihan'] == $v_det['bayar'] ) {
                            $m_kpp = new \Model\Storage\KonfirmasiPembayaranPeternak_model();
                            $m_kpp->where('nomor', $v_det['no_bayar'])->update(
                                array( 'lunas' => 1 )
                            );
                        }
                    }
                }

                $d_rp = $m_rp->where('id', $id)->first();

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $d_rp, $deskripsi_log);

                $this->result['status'] = 1;
                $this->result['content'] = array('id' => $id);
                $this->result['message'] = 'Data berhasil di simpan.';
            } else {
                $this->result['message'] = 'Error, segera hubungi tim IT.';
            }
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit()
    {
        $data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

        try {
            $file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];
            }

            $m_rp = new \Model\Storage\RealisasiPembayaran_model();
            if ($isMoved) {
                $file_name = $moved['name'];
                $path_name = $moved['path'];
            } else {
                $d_rp = $m_rp->where('id', $data['id'])->first();
                $path_name = $d_rp->lampiran;
            }

            $m_rp->where('id', $data['id'])->update(
                array(
                    'tgl_bayar' => $data['tgl_bayar'],
                    'perusahaan' => $data['perusahaan'],
                    'supplier' => isset($data['supplier']) ? $data['supplier'] : null,
                    'peternak' => isset($data['peternak']) ? $data['peternak'] : null,
                    'no_rek' => $data['no_rek'],
                    'no_bukti' => $data['no_bukti'],
                    'lampiran' => $path_name
                )
            );

            $id = $data['id'];

            $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
            $d_rpd = $m_rpd->where('id_header', $id)->delete();

            foreach ($data['detail'] as $k_det => $v_det) {
                $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
                $m_rpd->id_header = $id;
                $m_rpd->transaksi = $v_det['transaksi'];
                $m_rpd->no_bayar = $v_det['no_bayar'];
                $m_rpd->tagihan = $v_det['tagihan'];
                $m_rpd->bayar = $v_det['bayar'];
                $m_rpd->save();

                if ( stristr($v_det['transaksi'], 'doc') !== false ) {
                    $lunas = 0;
                    if ( $v_det['tagihan'] == $v_det['bayar'] ) {
                        $lunas = 1;
                    }
                    $m_kpd = new \Model\Storage\KonfirmasiPembayaranDoc_model();
                    $m_kpd->where('nomor', $v_det['no_bayar'])->update(
                        array( 'lunas' => $lunas )
                    );
                }
                if ( stristr($v_det['transaksi'], 'pakan') !== false ) {
                    $lunas = 0;
                    if ( $v_det['tagihan'] == $v_det['bayar'] ) {
                        $lunas = 1;
                    }
                    $m_kpp = new \Model\Storage\KonfirmasiPembayaranPakan_model();
                    $m_kpp->where('nomor', $v_det['no_bayar'])->update(
                        array( 'lunas' => $lunas )
                    );
                }
                if ( stristr($v_det['transaksi'], 'voadip') !== false ) {
                    $lunas = 0;
                    if ( $v_det['tagihan'] == $v_det['bayar'] ) {
                        $lunas = 1;
                    }
                    $m_kpv = new \Model\Storage\KonfirmasiPembayaranVoadip_model();
                    $m_kpv->where('nomor', $v_det['no_bayar'])->update(
                        array( 'lunas' => $lunas )
                    );
                }
                if ( stristr($v_det['transaksi'], 'peternak') !== false ) {
                    $lunas = 0;
                    if ( $v_det['tagihan'] == $v_det['bayar'] ) {
                        $lunas = 1;
                    }
                    $m_kpp = new \Model\Storage\KonfirmasiPembayaranPeternak_model();
                    $m_kpp->where('nomor', $v_det['no_bayar'])->update(
                        array( 'lunas' => $lunas )
                    );
                }
            }

            $d_rp = $m_rp->where('id', $id)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_rp, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $id);
            $this->result['message'] = 'Data berhasil di update.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete()
    {
        $params = $this->input->post('params');
        try {
            $id = $params['id'];

            $m_rp = new \Model\Storage\RealisasiPembayaran_model();
            $d_rp = $m_rp->where('id', $id)->first();

            $m_rpd = new \Model\Storage\RealisasiPembayaranDet_model();
            $d_rpd = $m_rpd->where('id_header', $id)->get()->toArray();

            foreach ($d_rpd as $k_det => $v_det) {
                if ( stristr($v_det['transaksi'], 'doc') !== false ) {
                    $lunas = 0;
                    $m_kpd = new \Model\Storage\KonfirmasiPembayaranDoc_model();
                    $m_kpd->where('nomor', $v_det['no_bayar'])->update(
                        array( 'lunas' => $lunas )
                    );
                }
                if ( stristr($v_det['transaksi'], 'pakan') !== false ) {
                    $lunas = 0;
                    $m_kpp = new \Model\Storage\KonfirmasiPembayaranPakan_model();
                    $m_kpp->where('nomor', $v_det['no_bayar'])->update(
                        array( 'lunas' => $lunas )
                    );
                }
                if ( stristr($v_det['transaksi'], 'voadip') !== false ) {
                    $lunas = 0;
                    $m_kpv = new \Model\Storage\KonfirmasiPembayaranVoadip_model();
                    $m_kpv->where('nomor', $v_det['no_bayar'])->update(
                        array( 'lunas' => $lunas )
                    );
                }
                if ( stristr($v_det['transaksi'], 'peternak') !== false ) {
                    $lunas = 0;
                    $m_kpp = new \Model\Storage\KonfirmasiPembayaranPeternak_model();
                    $m_kpp->where('nomor', $v_det['no_bayar'])->update(
                        array( 'lunas' => $lunas )
                    );
                }
            }

            $m_rp->where('id', $id)->delete();
            $m_rpd->where('id_header', $id)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_rp, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes()
    {
        $selisih_umur = abs(selisihTanggal('2022-01-12', '2021-12-11'));

        cetak_r( $selisih_umur );
    }
}