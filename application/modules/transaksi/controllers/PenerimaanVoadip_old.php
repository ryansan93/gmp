<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PenerimaanVoadip extends Public_Controller {

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
                // "assets/jquery/easy-autocomplete/jquery.easy-autocomplete.min.js",
                // "assets/jquery/maskedinput/jquery.maskedinput.min.js",
                "assets/select2/js/select2.min.js",
                // "assets/jquery/list.min.js",
                "assets/transaksi/penerimaan_voadip/js/penerimaan-voadip.js",
            ));
            $this->add_external_css(array(
                // "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                // "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/transaksi/penerimaan_voadip/css/penerimaan-voadip.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;

            $content['supplier'] = $this->get_data_supplier();
            $data['title_menu'] = 'Penerimaan VOADIP';
            $data['view'] = $this->load->view('transaksi/penerimaan_voadip/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_data_supplier()
    {
        $m_supplier = new \Model\Storage\Supplier_model();
        $d_nomor = $m_supplier->select('nomor')->distinct('nomor')->where('tipe', 'supplier')->get()->toArray();

        $datas = array();
        if ( !empty($d_nomor) ) {
            foreach ($d_nomor as $nomor) {
                $supplier = $m_supplier->where('tipe', 'supplier')
                                          ->where('nomor', $nomor['nomor'])
                                          ->orderBy('version', 'desc')
                                          ->orderBy('id', 'desc')
                                          ->first()->toArray();

                array_push($datas, $supplier);
            }
        }

        return $datas;
    }

    public function get_data_order_voadip_by_supplier()
    {
        $params = $this->input->post('params');

        $m_order_voadip = new \Model\Storage\OrderVoadip_model();
        $d_nomor = $m_order_voadip->select('no_order')
                         ->distinct('no_order')
                         ->get()->toArray();

        $datas = array();
        if ( !empty($d_nomor) ) {
            foreach ($d_nomor as $nomor) {
                $order_voadip = $m_order_voadip->where('no_order', $nomor['no_order'])
                                               ->orderBy('version', 'desc')
                                               ->orderBy('id', 'desc')
                                               ->with(['logs', 'd_supplier', 'detail'])
                                               ->first();

                if ( !empty( $order_voadip ) ) {
                    if ( $params == $order_voadip['supplier'] ) {
                        array_push($datas, $order_voadip->toArray());
                    }
                }
            }
        }

        $content['data'] = $this->mapping_data($datas);
        $html = $this->load->view('transaksi/penerimaan_voadip/list_penerimaan_voadip', $content, true);

        $this->result['status'] = 1;
        $this->result['content'] = $html;

        display_json($this->result);
    }

    public function mapping_data($data)
    {
        $datas = array();

        if ( !empty($data) ) {
            foreach ($data as $k_data => $v_data) {
                $m_terima_voadip = new \Model\Storage\TerimaVoadip_model();
                $d_terima_voadip = $m_terima_voadip->where('no_order', $v_data['no_order'])->first();

                foreach ($v_data['detail'] as $k_detail => $v_detail) {
                    $tujuan_kirim = '-';
                    $id_tujuan_kirim = null;
                    if ( $v_detail['kirim_ke'] == 'peternak' ) {
                        $m_mitra = new \Model\Storage\Mitra_model();
                        $d_mitra = $m_mitra->where('nomor', $v_detail['kirim'])
                                           ->with(['perwakilans'])
                                           ->orderBy('version', 'DESC')->first()->toArray();

                        $tujuan_kirim = $d_mitra['nama'];
                        $id_tujuan_kirim = $d_mitra['nomor'];
                    } else {
                        $m_wilayah = new \Model\Storage\Wilayah_model();
                        $d_wilayah = $m_wilayah->where('id', $v_detail['kirim'])->first();

                        $tujuan_kirim = $d_wilayah['nama'];
                        $id_tujuan_kirim = $d_wilayah['id'];
                    }

                    $m_terima_voadip_detail = new \Model\Storage\TerimaVoadipDetail_model();
                    $d_terima_voadip_detail = $m_terima_voadip_detail->where('id_terima', $d_terima_voadip['id'])
                                                                     ->where('kode_barang', $v_detail['d_barang']['kode'])
                                                                     ->where('tujuan_kirim', $v_detail['kirim'])
                                                                     ->get();

                    $jml_terima = 0;
                    if ( !empty($d_terima_voadip_detail) ) {
                        $data_terima = $d_terima_voadip_detail->toArray();
                        foreach ($data_terima as $k_data_terima => $v_data_terima) {
                            $jml_terima += $v_data_terima['jumlah'];
                        }
                    }

                    // cetak_r($jml_terima . '<' . $v_detail['jumlah']);

                    if ( $jml_terima < $v_detail['jumlah'] ) {
                        $jumlah = $v_detail['jumlah'] - $jml_terima;
                        $datas[ $v_data['no_order'] ]['detail'][] = array(
                            'id_tujuan_kirim' => $id_tujuan_kirim,
                            'tujuan_kirim' => $tujuan_kirim,
                            'item' => $v_detail['d_barang']['nama'],
                            'kode_item' => $v_detail['d_barang']['kode'],
                            'jumlah' => $jumlah,
                            'alamat' => $v_detail['alamat'],
                            'kirim_ke' => $v_detail['kirim_ke'],
                        );

                        $datas[ $v_data['no_order'] ]['no_order'] = $v_data['no_order'];
                        $datas[ $v_data['no_order'] ]['tanggal'] = $v_data['tanggal'];
                        $datas[ $v_data['no_order'] ]['supplier'] = $v_data['supplier'];
                    }
                }
            }
        }

        return $datas;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            foreach ($params as $k_data => $v_data) {
                $m_terima_voadip = new \Model\Storage\TerimaVoadip_model();

                $d_terima_voadip = $m_terima_voadip->where('no_order', $v_data['no_order'])->first();

                $now = $m_terima_voadip->getDate();

                $id_terima = null;
                if ( empty($d_terima_voadip) ) {
                    $m_terima_voadip->id = $m_terima_voadip->getNextIdentity();
                    $m_terima_voadip->no_order = $v_data['no_order'];
                    $m_terima_voadip->supplier = $v_data['supplier'];
                    $m_terima_voadip->user_submit = $this->userid;
                    $m_terima_voadip->tgl_submit = $now['waktu'];
                    $m_terima_voadip->save();

                    $id_terima = $m_terima_voadip->id;

                    $deskripsi_log_terima_voadip = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/save', $m_terima_voadip, $deskripsi_log_terima_voadip);
                } else {
                    $id_terima = $d_terima_voadip->id;
                }

                foreach ($v_data['detail'] as $k_detail => $v_detail) {
                    $m_terima_voadip_detail = new \Model\Storage\TerimaVoadipDetail_model();

                    $m_terima_voadip_detail->id = $m_terima_voadip_detail->getNextIdentity();
                    $m_terima_voadip_detail->id_terima = $id_terima;
                    $m_terima_voadip_detail->nosj = $v_detail['no_sj'];
                    $m_terima_voadip_detail->kirim_ke = $v_detail['kirim_ke'];
                    $m_terima_voadip_detail->tujuan_kirim = $v_detail['id_tujuan_kirim'];
                    $m_terima_voadip_detail->alamat_terima = $v_detail['alamat'];
                    $m_terima_voadip_detail->kode_barang = $v_detail['kode_brg'];
                    $m_terima_voadip_detail->jumlah = $v_detail['jumlah'];
                    $m_terima_voadip_detail->baik = ($v_detail['kondisi'] == 'baik') ? 1 : 0;
                    $m_terima_voadip_detail->rusak =($v_detail['kondisi'] == 'rusak') ? 1 : 0;
                    $m_terima_voadip_detail->tgl_terima = $v_detail['tanggal'];
                    $m_terima_voadip_detail->keterangan = $v_detail['keterangan'];
                    $m_terima_voadip_detail->user_submit = $this->userid;
                    $m_terima_voadip_detail->tgl_submit = $now['waktu'];
                    $m_terima_voadip_detail->save();
                }
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Penerimaan Voadip berhasil disimpan.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function tes($no_spm='')
    {
        
    }
}