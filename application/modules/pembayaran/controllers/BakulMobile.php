<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BakulMobile extends Public_Controller {

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
            $this->load->library('Mobile_Detect');
            $detect = new Mobile_Detect();

            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/pembayaran/bakul_mobile/js/bakul-mobile.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/pembayaran/bakul_mobile/css/bakul-mobile.css",
            ));

            $data = $this->includes;

            $isMobile = true;
            if ( $detect->isMobile() ) {
                $isMobile = true;
            }

            $content['akses'] = $akses;
            $content['isMobile'] = $isMobile;

            $pelanggan = $this->get_pelanggan();
            // $unit = $this->get_unit();
            $content['add_form'] = $this->add_form($pelanggan);
            $content['riwayat'] = $this->riwayat($pelanggan);

            // Load Indexx
            $data['title_menu'] = 'Pembayaran Bakul';
            $data['view'] = $this->load->view('pembayaran/bakul_mobile/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_pelanggan()
    {
        $m_pelanggan = new \Model\Storage\Pelanggan_model();
        $d_no = $m_pelanggan->distinct('nomor')->select('nomor')->where('tipe', 'pelanggan')->get()->toArray();

        $data_pelanggan = null;
        if ( count($d_no) > 0 ) {
            foreach ($d_no as $k => $val) {
                $m_plg = new \Model\Storage\Pelanggan_model();
                $d_pelanggan = $m_plg->select('nomor', 'nama', 'alamat_kecamatan')->where('nomor', $val['nomor'])->where('tipe', 'pelanggan')->orderBy('version', 'desc')->first()->toArray();

                $m_kecamatan = new \Model\Storage\Lokasi_model();
                $d_kecamatan = $m_kecamatan->where('id', $d_pelanggan['alamat_kecamatan'])->first();
                $d_kab_kota = $m_kecamatan->where('id', $d_kecamatan->induk)->first();

                $key = $d_pelanggan['nama'].' - '.$val['nomor'];
                $data_pelanggan[ $key ] = $d_pelanggan;
                $data_pelanggan[ $key ]['kab_kota'] = $d_kab_kota->nama;
            }
        }

        if ( !empty($data_pelanggan) ) {
            ksort($data_pelanggan);
        }

        return $data_pelanggan;
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

    public function get_list_pembayaran()
    {
        $params = $this->input->post('params');

        $pelanggan = $params['pelanggan'];
        $tgl_bayar = $params['tgl_bayar'];

        $m_pp = new \Model\Storage\PembayaranPelanggan_model();
        $d_pp = null;
        if ( $pelanggan != 'all' ) {
            $d_pp = $m_pp->where('tgl_bayar', $tgl_bayar)->where('no_pelanggan', $pelanggan)->with(['pelanggan'])->get();
        } else {
            $d_pp = $m_pp->where('tgl_bayar', $tgl_bayar)->with(['pelanggan'])->get();
        }

        $data = null;
        if ( !empty($d_pp) ) {
            $d_pp = $d_pp->toArray();
            foreach ($d_pp as $k_pp => $v_pp) {
                $key = str_replace('-', '', $v_pp['tgl_bayar']).'-'.$v_pp['id'];
                $data[$key] = array(
                    'id' => $v_pp['id'],
                    'tgl_bayar' => $v_pp['tgl_bayar'],
                    'pelanggan' => $v_pp['pelanggan']['nama'],
                    'jml_transfer' => $v_pp['jml_transfer']
                );

                krsort($data);
            }
        }

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/bakul_mobile/list_pembayaran', $content, true);

        $this->result['status'] = 1;
        $this->result['html'] = $html;

        display_json( $this->result );
    }

    public function get_list_do()
    {
        $pelanggan = $this->input->post('pelanggan');
        $tgl_bayar = $this->input->post('tgl_bayar');
        $edit = $this->input->post('edit');

        $saldo = 0;
        $id_pp = null;
        if ( $edit != 'edit' ) {
            $m_sp = new \Model\Storage\SaldoPelanggan_model();
            $d_sp = $m_sp->where('no_pelanggan', $pelanggan)->orderBy('id', 'desc')->first();

            if ( $d_sp ) {
                $saldo = $d_sp->saldo;
            }
        } else {
            $m_pp = new \Model\Storage\PembayaranPelanggan_model();
            $d_pp = $m_pp->where('no_pelanggan', $pelanggan)->where('tgl_bayar', $tgl_bayar)->orderBy('id')->first();

            if ( $d_pp ) {
                $saldo = $d_pp->saldo;
                $id_pp = $d_pp->id;
            } else {
                $m_sp = new \Model\Storage\SaldoPelanggan_model();
                $d_sp = $m_sp->where('no_pelanggan', $pelanggan)->orderBy('id', 'desc')->first();

                if ( $d_sp ) {
                    $saldo = $d_sp->saldo;
                }
            }
        }

        $m_pp = new \Model\Storage\PembayaranPelanggan_model();
        $d_pp = $m_pp->select('id')->where('no_pelanggan', $pelanggan)->get();

        $d_dpp_lunas = null;
        if ( $d_pp->count() > 0 ) {
            $d_pp = $d_pp->toArray();

            if ( !$id_pp ) {
                $m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
                $_d_dpp_lunas = $m_dpp->select('id_do')->distinct('id_do')->whereIn('id_header', $d_pp)->where('status', 'LUNAS')->get();

                if ( $_d_dpp_lunas->count() > 0 ) {
                    $d_dpp_lunas = $_d_dpp_lunas->toArray();
                }
            }
        }

        $m_drs = new \Model\Storage\DetRealSJ_model();

        $d_drs = null;
        if ( empty($d_dpp_lunas) ) {
            $d_drs = $m_drs->where('no_pelanggan', $pelanggan)->whereNotNull('no_do')->get();
        } else {
            $d_drs = $m_drs->where('no_pelanggan', $pelanggan)->whereNotNull('no_do')->whereNotIn('id', $d_dpp_lunas)->get();
        }

        $jumlah_bayar = 0;

        $data = null;
        if ( !empty($d_drs) ) {
            $d_drs = $d_drs->toArray();
            foreach ($d_drs as $k_drs => $v_drs) {
                $m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
                $hit = true;

                $jml_bayar = 0;
                $sudah_bayar = 0;
                if ( !$id_pp ) {
                    $jml_bayar = $m_dpp->where('id_do', $v_drs['id'])->sum('jumlah_bayar');
                    $sudah_bayar = $jml_bayar;
                } else {
                    $sudah_bayar = $m_dpp->where('id_do', $v_drs['id'])->where('id_header', '<>', $id_pp)->sum('jumlah_bayar');
                    $jml_bayar = $m_dpp->where('id_do', $v_drs['id'])->where('id_header', $id_pp)->sum('jumlah_bayar');
                    $d_dpp = $m_dpp->where('id_do', $v_drs['id'])->where('id_header', $id_pp)->get();
                    if ( $d_dpp->count() == 0 ) {
                        $hit = false;
                    }
                }

                if ( $hit ) {
                    $jumlah_bayar += $jml_bayar;

                    $m_rs = new \Model\Storage\RealSJ_model();
                    $d_rs = $m_rs->where('id', $v_drs['id_header'])->first();

                    $total = $v_drs['tonase'] * $v_drs['harga'];

                    $key = str_replace('-', '', $d_rs->tgl_panen).'-'.$v_drs['no_do'];
                    $data[ $key ] = array(
                        'id' => $v_drs['id'],
                        'tgl_panen' => $d_rs->tgl_panen,
                        'no_do' => $v_drs['no_do'],
                        'no_sj' => $v_drs['no_sj'],
                        'ekor' => $v_drs['ekor'],
                        'kg' => $v_drs['tonase'],
                        'harga' => $v_drs['harga'],
                        'total' => $total,
                        'sudah_bayar' => $sudah_bayar,
                        'jumlah_bayar' => ($edit == 'edit') ? 0 : $jml_bayar,
                    );

                    ksort($data);
                }
            }
        }

        $content['jumlah_bayar'] = $jumlah_bayar;
        $content['data'] = $data;
        $content['edit'] = $edit;

        $html = $this->load->view('pembayaran/bakul_mobile/list_do', $content, true);

        $this->result['status'] = 1;
        $this->result['saldo'] = $saldo;
        $this->result['html'] = $html;

        display_json( $this->result );
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
            $html = $this->detail_form($id);
        } else if ( !empty($id) && $edit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $pelanggan = $this->get_pelanggan();
            $html = $this->edit_form($id, $pelanggan);
        }else{
            $pelanggan = $this->get_pelanggan();
            $html = $this->add_form($pelanggan);
        }

        echo $html;
    }

    public function riwayat($pelanggan)
    {
        $content['data_pelanggan'] = $pelanggan;
        $html = $this->load->view('pembayaran/bakul_mobile/riwayat', $content, TRUE);

        return $html;
    }

    public function add_form($pelanggan)
    {
        $content['data_pelanggan'] = $pelanggan;
        $html = $this->load->view('pembayaran/bakul_mobile/add_form', $content, TRUE);

        return $html;
    }

    public function detail_form($id)
    {
        $m_pp = new \Model\Storage\PembayaranPelanggan_model();
        $d_pp = $m_pp->where('id', $id)->with(['detail', 'pelanggan'])->first();

        $data = null;
        if ( $d_pp ) {
            $d_pp = $d_pp->toArray();

            $_m_pp = new \Model\Storage\PembayaranPelanggan_model();
            $d_pp_before = $_m_pp->select('id')->where('no_pelanggan', $d_pp['no_pelanggan'])->where('tgl_bayar', '<=', $d_pp['tgl_bayar'])->get();

            $data_before = null;
            if ( $d_pp_before->count() > 0 ) {
                $data_before = $d_pp_before->toArray();
            }

            $detail = null;
            foreach ($d_pp['detail'] as $k_det => $v_det) {
                $sudah_bayar = 0;
                if ( !empty($data_before) ) {
                    $m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
                    $d_dpp = $m_dpp->whereIn('id_header', $data_before)->where('id_do', $v_det['id_do'])->get();

                    if ( $d_dpp->count() > 0 ) {
                        $sudah_bayar = $d_dpp->sum('jumlah_bayar');
                    }
                }

                $detail[ $v_det['id'] ] = array(
                    'id' => $v_det['id'],
                    'id_header' => $v_det['id_header'],
                    'id_do' => $v_det['id_do'],
                    'total_bayar' => $v_det['total_bayar'],
                    'jumlah_bayar' => $v_det['jumlah_bayar'],
                    'penyesuaian' => $v_det['penyesuaian'],
                    'ket_penyesuaian' => $v_det['ket_penyesuaian'],
                    'status' => $v_det['status'],
                    'data_do' => $v_det['data_do'],
                    'sudah_bayar' => $sudah_bayar
                );
            }
            $data = array(
                'id' => $d_pp['id'],
                'no_pelanggan' => $d_pp['no_pelanggan'],
                'tgl_bayar' => $d_pp['tgl_bayar'],
                'jml_transfer' => $d_pp['jml_transfer'],
                'saldo' => $d_pp['saldo'],
                'total_uang' => $d_pp['total_uang'],
                'total_penyesuaian' => $d_pp['total_penyesuaian'],
                'total_bayar' => $d_pp['total_bayar'],
                'lebih_kurang' => $d_pp['lebih_kurang'],
                'lampiran_transfer' => $d_pp['lampiran_transfer'],
                'pelanggan' => $d_pp['pelanggan'],
                'detail' => $detail
            );
        }

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/bakul_mobile/detail_form', $content, true);

        return $html;
    }

    public function edit_form($id, $pelanggan)
    {
        $m_pp = new \Model\Storage\PembayaranPelanggan_model();
        $d_pp = $m_pp->where('id', $id)->with(['detail', 'pelanggan'])->first();

        $data = null;
        if ( $d_pp ) {
            $d_pp = $d_pp->toArray();

            $_m_pp = new \Model\Storage\PembayaranPelanggan_model();
            $d_pp_before = $_m_pp->select('id')->where('no_pelanggan', $d_pp['no_pelanggan'])->where('tgl_bayar', '<=', $d_pp['tgl_bayar'])->get();

            $data_before = null;
            if ( $d_pp_before->count() > 0 ) {
                $data_before = $d_pp_before->toArray();
            }

            $detail = null;
            foreach ($d_pp['detail'] as $k_det => $v_det) {
                $sudah_bayar = 0;
                if ( !empty($data_before) ) {
                    $m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
                    $d_dpp = $m_dpp->whereIn('id_header', $data_before)->where('id_do', $v_det['id_do'])->get();

                    if ( $d_dpp->count() > 0 ) {
                        $sudah_bayar = $d_dpp->sum('jumlah_bayar');
                    }
                }

                $detail[ $v_det['id'] ] = array(
                    'id' => $v_det['id'],
                    'id_header' => $v_det['id_header'],
                    'id_do' => $v_det['id_do'],
                    'total_bayar' => $v_det['total_bayar'],
                    'jumlah_bayar' => $v_det['jumlah_bayar'],
                    'penyesuaian' => $v_det['penyesuaian'],
                    'ket_penyesuaian' => $v_det['ket_penyesuaian'],
                    'status' => $v_det['status'],
                    'data_do' => $v_det['data_do'],
                    'sudah_bayar' => $sudah_bayar
                );
            }
            $data = array(
                'id' => $d_pp['id'],
                'no_pelanggan' => $d_pp['no_pelanggan'],
                'tgl_bayar' => $d_pp['tgl_bayar'],
                'jml_transfer' => $d_pp['jml_transfer'],
                'saldo' => $d_pp['saldo'],
                'total_uang' => $d_pp['total_uang'],
                'total_penyesuaian' => $d_pp['total_penyesuaian'],
                'total_bayar' => $d_pp['total_bayar'],
                'lebih_kurang' => $d_pp['lebih_kurang'],
                'lampiran_transfer' => $d_pp['lampiran_transfer'],
                'pelanggan' => $d_pp['pelanggan'],
                'detail' => $detail
            );
        }

        $content['data_pelanggan'] = $pelanggan;
        $content['data'] = $data;
        $html = $this->load->view('pembayaran/bakul_mobile/edit_form', $content, true);

        return $html;
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

                $m_pp = new \Model\Storage\PembayaranPelanggan_model();
                $m_pp->no_pelanggan = $data['pelanggan'];
                $m_pp->tgl_bayar = $data['tgl_bayar'];
                $m_pp->jml_transfer = $data['jml_transfer'];
                $m_pp->saldo = $data['saldo'];
                $m_pp->total_uang = $data['total_uang'];
                $m_pp->total_penyesuaian = $data['total_penyesuaian'];
                $m_pp->total_bayar = $data['total_bayar'];
                $m_pp->lebih_kurang = $data['lebih_kurang'];
                $m_pp->lampiran_transfer = $path_name;
                $m_pp->save();

                $id = $m_pp->id;

                foreach ($data['detail'] as $k_det => $v_det) {
                    $m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
                    $m_dpp->id_header = $id;
                    $m_dpp->id_do = $v_det['id'];
                    $m_dpp->total_bayar = $v_det['total'];
                    $m_dpp->jumlah_bayar = $v_det['jml_bayar'];
                    $m_dpp->penyesuaian = $v_det['penyesuaian'];
                    $m_dpp->ket_penyesuaian = $v_det['ket_penyesuaian'];
                    $m_dpp->status = $v_det['status'];
                    $m_dpp->save();
                }

                $d_pp = $m_pp->where('id', $id)->with(['detail'])->first();

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $d_pp, $deskripsi_log );

                $m_sp = new \Model\Storage\SaldoPelanggan_model();
                $m_sp->jenis_saldo = 'D';
                $m_sp->no_pelanggan = $data['pelanggan'];
                $m_sp->id_trans = $id;
                $m_sp->tgl_trans = date('Y-m-d');
                $m_sp->jenis_trans = 'pembayaran_pelanggan';
                $m_sp->nominal = 0;
                $m_sp->saldo = ($data['lebih_kurang'] > 0) ? $data['lebih_kurang'] : 0;
                $m_sp->save();

                $this->result['status'] = 1;
                $this->result['content'] = array('id' => $id);
                $this->result['message'] = 'Data berhasil di simpan.';
            }else {
                $this->result['message'] = 'Error, segera hubungi tim IT.';
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit()
    {
        $data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];
        
        try {
            $m_pp = new \Model\Storage\PembayaranPelanggan_model();
            $d_pp = $m_pp->where('id', $data['id'])->with(['detail'])->first()->toArray();

            $file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];

                $file_name = $moved['name'];
                $path_name = $moved['path'];
            } else {
                $file_name = $d_pp['lampiran_transfer'];
                $path_name = $d_pp['lampiran_transfer'];
            }

            $m_pp->where('id', $data['id'])->update(
                array(
                    'no_pelanggan' => $data['pelanggan'],
                    'tgl_bayar' => $data['tgl_bayar'],
                    'jml_transfer' => $data['jml_transfer'],
                    'saldo' => $data['saldo'],
                    'total_uang' => $data['total_uang'],
                    'total_penyesuaian' => $data['total_penyesuaian'],
                    'total_bayar' => $data['total_bayar'],
                    'lebih_kurang' => $data['lebih_kurang'],
                    'lampiran_transfer' => $path_name
                )
            );

            $id = $data['id'];

            $m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
            $m_dpp->where('id_header', $id)->delete();
            foreach ($data['detail'] as $k_det => $v_det) {
                $m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
                $m_dpp->id_header = $id;
                $m_dpp->id_do = $v_det['id'];
                $m_dpp->total_bayar = $v_det['total'];
                $m_dpp->jumlah_bayar = $v_det['jml_bayar'];
                $m_dpp->penyesuaian = $v_det['penyesuaian'];
                $m_dpp->ket_penyesuaian = !empty($v_det['ket_penyesuaian']) ? $v_det['ket_penyesuaian'] : '';
                $m_dpp->status = trim($v_det['status']);
                $m_dpp->save();
            }

            $_d_pp = $m_pp->where('id', $id)->with(['detail'])->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $_d_pp, $deskripsi_log );

            $m_sp = new \Model\Storage\SaldoPelanggan_model();
            $d_sp_old = $m_sp->where('no_pelanggan', $d_pp['no_pelanggan'])->where('id_trans', $data['id'])->orderBy('id', 'desc')->first();
            $d_sp_new = $m_sp->where('no_pelanggan', $data['pelanggan'])->orderBy('id', 'desc')->first();

            $jenis_saldo = null;
            $nominal = null;
            $saldo = !empty($d_sp_new) ? $d_sp_new->saldo : 0;
            /* JIKA PELANGGAN BERUBAH */
            if ( $d_pp['no_pelanggan'] != $data['pelanggan'] ) {
                $saldo_plg_old = !empty($d_sp_old) ? $d_sp_old->saldo : 0;

                if ( $d_pp['lebih_kurang'] > 0 ) {
                    $saldo_plg_old -= $d_pp['lebih_kurang'];
                }

                $m_sp = new \Model\Storage\SaldoPelanggan_model();
                $m_sp->jenis_saldo = 'K';
                $m_sp->no_pelanggan = $d_pp['no_pelanggan'];
                $m_sp->id_trans = $id;
                $m_sp->tgl_trans = date('Y-m-d');
                $m_sp->jenis_trans = 'reverse_pembayaran_pelanggan';
                $m_sp->nominal = $d_pp['lebih_kurang'];
                $m_sp->saldo = ($saldo_plg_old > 0) ? $saldo_plg_old : 0;
                $m_sp->save();

                /* HITUNG SALDO */
                $jenis_saldo = null;
                $nominal = null;
                $saldo = !empty($d_sp_new) ? $d_sp_new->saldo : 0;
                
                if ( $data['lebih_kurang'] < 0 ) {
                    $nominal = $data['lebih_kurang'];
                    $jenis_saldo = 'K';
                    $saldo -= abs($nominal);
                } else {
                    $nominal = $data['lebih_kurang'];
                    $jenis_saldo = 'D';
                    $saldo += abs($nominal);
                }
            } else {
                /* HITUNG SALDO */                
                if ( $data['lebih_kurang'] < 0 ) {
                    if ( $d_pp['lebih_kurang'] > 0 ) {
                        $nominal = $d_pp['lebih_kurang'];
                    } else {
                        $nominal = abs($data['lebih_kurang']) - abs($d_pp['lebih_kurang']);
                    }
                    $jenis_saldo = 'K';
                    $saldo -= abs($nominal);
                } else {
                    if ( $d_pp['lebih_kurang'] > 0 ) {
                        $nominal = $data['lebih_kurang'] - $d_pp['lebih_kurang'];
                    } else {
                        $nominal = $data['lebih_kurang'];
                    }
                    $jenis_saldo = 'D';
                    $saldo += abs($nominal);
                }
            }

            $m_sp = new \Model\Storage\SaldoPelanggan_model();
            $m_sp->jenis_saldo = $jenis_saldo;
            $m_sp->no_pelanggan = $data['pelanggan'];
            $m_sp->id_trans = $id;
            $m_sp->tgl_trans = date('Y-m-d');
            $m_sp->jenis_trans = 'reverse_pembayaran_pelanggan';
            $m_sp->nominal = abs($nominal);
            $m_sp->saldo = ($saldo > 0) ? $saldo : 0;
            $m_sp->save();

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $id);
            $this->result['message'] = 'Data berhasil di edit.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete()
    {
        $id = $this->input->post('params');

        try {
            $m_pp = new \Model\Storage\PembayaranPelanggan_model();
            $d_pp = $m_pp->where('id', $id)->with(['detail'])->first()->toArray();

            $_d_pp = $m_pp->where('id', $id)->with(['detail'])->first();

            $deskripsi_log = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $_d_pp, $deskripsi_log );

            $m_dpp = new \Model\Storage\DetPembayaranPelanggan_model();
            $m_dpp->where('id_header', $id)->delete();
            $m_pp->where('id', $id)->delete();

            $m_sp = new \Model\Storage\SaldoPelanggan_model();
            $d_sp = $m_sp->where('no_pelanggan', $d_pp['no_pelanggan'])->orderBy('id', 'desc')->first();

            $saldo = !empty($d_sp) ? $d_sp->saldo : 0;

            $jenis_saldo = 'K';
            if ( $d_pp['lebih_kurang'] > 0 ) {
                $saldo -= $d_pp['lebih_kurang'];
            }

            $m_sp = new \Model\Storage\SaldoPelanggan_model();
            $m_sp->jenis_saldo = $jenis_saldo;
            $m_sp->no_pelanggan = $d_pp['no_pelanggan'];
            $m_sp->id_trans = $id;
            $m_sp->tgl_trans = date('Y-m-d');
            $m_sp->jenis_trans = 'reverse_pembayaran_pelanggan';
            $m_sp->nominal = $d_pp['lebih_kurang'];
            $m_sp->saldo = ($saldo > 0) ? $saldo : 0;
            $m_sp->save();

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json( $this->result );
    }
}