<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ODVP extends Public_Controller {

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
                "assets/transaksi/odvp/js/odvp.js",
            ));
            $this->add_external_css(array(
                // "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                // "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/transaksi/odvp/css/odvp.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;

            // Load Indexx
            $data['title_menu'] = 'Order DOC, Pakan dan Voadip';
            $data['view'] = $this->load->view('transaksi/odvp/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_lists_doc()
    {
        $params = $this->input->post('params');

        $rdim = $this->get_data_rdim('DOC', $params['start_date'], $params['end_date']);

        $content['data'] = $rdim;
        $html = $this->load->view('transaksi/odvp/list_order_doc', $content, true);

        $this->result['status'] = 1;
        $this->result['content'] = $html;

        display_json($this->result);
    }

    public function get_lists_voadip()
    {
        $params = $this->input->post('params');

        $m_order_voadip = new \Model\Storage\OrderVoadip_model();
        $d_nomor = $m_order_voadip->select('no_order')
                         ->distinct('no_order')
                         ->whereBetween('tanggal', [$params['start_date'], $params['end_date']])
                         ->get()->toArray();

        $datas = array();
        if ( !empty($d_nomor) ) {
            foreach ($d_nomor as $nomor) {
                $order_voadip = $m_order_voadip->where('no_order', $nomor['no_order'])
                                               ->orderBy('version', 'desc')
                                               ->orderBy('id', 'desc')
                                               ->with(['logs', 'd_supplier'])
                                               ->first()->toArray();

                array_push($datas, $order_voadip);
            }
        }

        $content['data'] = $datas;
        $html = $this->load->view('transaksi/odvp/list_order_voadip', $content, true);

        $this->result['status'] = 1;
        $this->result['content'] = $html;

        display_json($this->result);
    }

    public function get_data_doc()
    {
        $m_brg = new \Model\Storage\Barang_model();
        $d_nomor = $m_brg->select('kode')->distinct('kode')->where('tipe', 'doc')->get()->toArray();

        $datas = array();
        if ( !empty($d_nomor) ) {
            foreach ($d_nomor as $nomor) {
                $brg = $m_brg->where('tipe', 'doc')
                             ->where('kode', $nomor['kode'])
                             ->orderBy('version', 'desc')
                             ->orderBy('id', 'desc')
                             ->with(['logs'])
                             ->first()->toArray();

                array_push($datas, $brg);
            }
        }

        return $datas;
    }

    public function order_doc_form()
    {
        $content['data_doc'] = $this->get_data_doc();
        $content['supplier'] = $this->get_data_supplier();
        $this->load->view('transaksi/odvp/order_doc_form', $content);
    }

    public function order_doc_edit_form()
    {
        $no_order = $this->input->get('no_order');

        $m_order_doc = new \Model\Storage\OrderDoc_model();
        $d_order_doc = $m_order_doc->where('no_order', $no_order)->orderBy('version', 'DESC')->first();

        $content['data_order_doc'] = $d_order_doc;
        $content['data_doc'] = $this->get_data_doc();
        $content['supplier'] = $this->get_data_supplier();
        $this->load->view('transaksi/odvp/order_doc_edit_form', $content);
    }

    public function order_doc_view_form()
    {
        $no_order = $this->input->get('no_order');

        $m_order_doc = new \Model\Storage\OrderDoc_model();
        $d_order_doc = $m_order_doc->where('no_order', $no_order)->orderBy('version', 'DESC')->first();

        $content['data_order_doc'] = $d_order_doc;
        $content['data_doc'] = $this->get_data_doc();
        $content['supplier'] = $this->get_data_supplier();
        $this->load->view('transaksi/odvp/order_doc_view_form', $content);
    }

    public function terima_doc_form()
    {
        $no_order = $this->input->get('no_order');

        $m_order_doc = new \Model\Storage\OrderDoc_model();
        $d_order_doc = $m_order_doc->where('no_order', $no_order)->orderBy('version', 'DESC')->first();

        $content['data_order_doc'] = $d_order_doc;
        $content['data_doc'] = $this->get_data_doc();
        $content['supplier'] = $this->get_data_supplier();
        $this->load->view('transaksi/odvp/terima_doc_form', $content);
    }

    public function terima_doc_edit_form()
    {
        $no_terima = $this->input->get('no_terima');

        $m_terima_doc = new \Model\Storage\TerimaDoc_model();
        $d_terima_doc = $m_terima_doc->where('no_terima', $no_terima)->with(['order_doc'])->orderBy('version', 'DESC')->first();

        $content['data_terima_doc'] = $d_terima_doc;
        $content['data_doc'] = $this->get_data_doc();
        $content['supplier'] = $this->get_data_supplier();
        $this->load->view('transaksi/odvp/terima_doc_edit_form', $content);
    }

    public function terima_doc_view_form()
    {
        $no_terima = $this->input->get('no_terima');

        $m_terima_doc = new \Model\Storage\TerimaDoc_model();
        $d_terima_doc = $m_terima_doc->where('no_terima', $no_terima)->with(['order_doc'])->orderBy('version', 'DESC')->first();

        $content['data_terima_doc'] = $d_terima_doc;
        $content['data_doc'] = $this->get_data_doc();
        $content['supplier'] = $this->get_data_supplier();
        $this->load->view('transaksi/odvp/terima_doc_view_form', $content);
    }

    public function order_voadip_form()
    {
        $content['kategori_voadip'] = array(
                'vitamin' => 'Vitamin',
                'desinfektan' => 'Desinfektan',
                'obat' => 'Obat',
                'vaksin' => 'Vaksin'
            );

        $content['supplier'] = $this->get_data_supplier();
        $content['perwakilan'] = $this->get_list_pwk();
            
        $this->load->view('transaksi/odvp/order_voadip_form', $content);
    }

    public function order_voadip_edit_form()
    {
        $no_order = $this->input->get('no_order');

        $data = array();

        $m_order_voadip = new \Model\Storage\OrderVoadip_model();
        $d_order_voadip = $m_order_voadip->where('no_order', $no_order)
                                         ->with(['d_supplier'])
                                         ->orderBy('version', 'DESC')->first()->toArray();

        $m_order_voadip_detail = new \Model\Storage\OrderVoadipDetail_model();
        $d_order_voadip_detail = $m_order_voadip_detail->where('id_order', $d_order_voadip['id'])
                                                       ->with(['d_barang'])
                                                       ->get()->toArray();

        $data['id'] = $d_order_voadip['id'];
        $data['no_order'] = $d_order_voadip['no_order'];
        $data['tanggal'] = $d_order_voadip['tanggal'];
        $data['no_supl'] = $d_order_voadip['supplier'];
        $data['version'] = $d_order_voadip['version'];

        foreach ($d_order_voadip_detail as $k_detail => $v_detail) {

            $peternak = '-';
            $id_peternak = null;
            $kantor = '-';
            $id_kantor = null;
            if ( $v_detail['kirim_ke'] == 'peternak' ) {
                $m_mitra = new \Model\Storage\Mitra_model();
                $d_mitra = $m_mitra->where('nomor', $v_detail['kirim'])
                                   ->with(['perwakilans'])
                                   ->orderBy('version', 'DESC')->first()->toArray();

                $peternak = $d_mitra['nama'];
                $id_peternak = $d_mitra['nomor'];
                $kantor = $d_mitra['perwakilans'][0]['d_perwakilan']['nama'];
                $id_kantor = $d_mitra['perwakilans'][0]['d_perwakilan']['id'];
            } else {
                $m_wilayah = new \Model\Storage\Wilayah_model();
                $d_wilayah = $m_wilayah->where('id', $v_detail['kirim'])->first();

                $kantor = $d_wilayah['nama'];
                $id_kantor = $d_wilayah['id'];
            }

            $data['detail'][] = array(
                'kategori' => $v_detail['d_barang']['kategori'],
                'barang' => $v_detail['d_barang']['nama'],
                'id_barang' => $v_detail['d_barang']['kode'],
                'kemasan' => $v_detail['kemasan'],
                'harga' => $v_detail['harga'],
                'jumlah' => $v_detail['jumlah'],
                'total' => $v_detail['total'],
                'peternak' => $peternak,
                'id_peternak' => $id_peternak,
                'kantor' => $kantor,
                'id_kantor' => $id_kantor,
                'alamat' => $v_detail['alamat']
            );
        }

        $content['kategori_voadip'] = array(
                'vitamin' => 'Vitamin',
                'desinfektan' => 'Desinfektan',
                'obat' => 'Obat',
                'vaksin' => 'Vaksin'
            );

        $content['supplier'] = $this->get_data_supplier();
        $content['perwakilan'] = $this->get_list_pwk();

        $content['data'] = $data;
        $this->load->view('transaksi/odvp/order_voadip_edit_form', $content);
    }

    public function order_voadip_view_form()
    {
        $no_order = $this->input->get('no_order');

        $data = array();

        $m_order_voadip = new \Model\Storage\OrderVoadip_model();
        $d_order_voadip = $m_order_voadip->where('no_order', $no_order)
                                         ->with(['d_supplier'])
                                         ->orderBy('version', 'DESC')->first()->toArray();

        $m_order_voadip_detail = new \Model\Storage\OrderVoadipDetail_model();
        $d_order_voadip_detail = $m_order_voadip_detail->where('id_order', $d_order_voadip['id'])
                                                       ->with(['d_barang'])
                                                       ->get()->toArray();

        $data['id'] = $d_order_voadip['id'];
        $data['no_order'] = $d_order_voadip['no_order'];
        $data['tanggal'] = $d_order_voadip['tanggal'];
        $data['no_supl'] = $d_order_voadip['supplier'];
        $data['nama_supl'] = $d_order_voadip['d_supplier']['nama'];

        foreach ($d_order_voadip_detail as $k_detail => $v_detail) {

            $peternak = '-';
            $kantor = '-';
            if ( $v_detail['kirim_ke'] == 'peternak' ) {
                $m_mitra = new \Model\Storage\Mitra_model();
                $d_mitra = $m_mitra->where('nomor', $v_detail['kirim'])
                                   ->with(['perwakilans'])
                                   ->orderBy('version', 'DESC')->first()->toArray();

                $peternak = $d_mitra['nama'];
                $kantor = $d_mitra['perwakilans'][0]['d_perwakilan']['nama'];
            } else {
                $m_wilayah = new \Model\Storage\Wilayah_model();
                $d_wilayah = $m_wilayah->where('id', $v_detail['kirim'])->first();

                $kantor = $d_wilayah['nama'];
            }

            $data['detail'][] = array(
                'kategori' => $v_detail['d_barang']['kategori'],
                'barang' => $v_detail['d_barang']['nama'],
                'kemasan' => $v_detail['kemasan'],
                'harga' => $v_detail['harga'],
                'jumlah' => $v_detail['jumlah'],
                'total' => $v_detail['total'],
                'peternak' => $peternak,
                'kantor' => $kantor,
                'alamat' => $v_detail['alamat']
            );
        }

        $content['data'] = $data;
        $this->load->view('transaksi/odvp/order_voadip_view_form', $content);
    }

    public function get_data_rdim($tipe = null, $start_date = null, $end_date = null)
    {
        if ( $start_date != null && $end_date != null ) {
            if ( $tipe == 'DOC' ) {
                $m_rdim_submit = new \Model\Storage\RdimSubmit_model();
                $d_rdim_submit = $m_rdim_submit->whereBetween('tgl_docin', [$start_date, $end_date])
                                 ->with(['dKandang', 'mitra', 'order_doc'])
                                 ->orderBy('tgl_docin', 'ASC')
                                 ->get()->toArray();
            } else if ( $tipe == 'VOADIP' ) {
                $m_rdim_submit = new \Model\Storage\RdimSubmit_model();
                $d_rdim_submit = $m_rdim_submit->whereBetween('tgl_docin', [$start_date, $end_date])
                                 ->with(['dKandang', 'mitra'])
                                 ->orderBy('tgl_docin', 'ASC')
                                 ->get()->toArray();
            }
        } else {
            if ( $tipe == 'DOC' ) {
                $m_rdim_submit = new \Model\Storage\RdimSubmit_model();
                $d_rdim_submit = $m_rdim_submit->with(['dKandang', 'mitra', 'order_doc'])
                                 ->orderBy('tgl_docin', 'ASC')
                                 ->take(20)
                                 ->get()->toArray();
            } else if ( $tipe == 'VOADIP' ) {
                $m_rdim_submit = new \Model\Storage\RdimSubmit_model();
                $d_rdim_submit = $m_rdim_submit->with(['dKandang', 'mitra'])
                                 ->orderBy('tgl_docin', 'ASC')
                                 ->get()->toArray();
            }
        }

        return $d_rdim_submit;
    }

    public function mapping_data_rdim($params=null)
    {
        $data = array();
        if ( !empty($params) ) {
            foreach ($params as $k_params => $v_params) {
                $data[ $v_params['d_kandang']['d_unit']['id'] ]['id'] = $v_params['d_kandang']['d_unit']['id'];
                $data[ $v_params['d_kandang']['d_unit']['id'] ]['nama'] = $v_params['d_kandang']['d_unit']['nama'];

                $data[ $v_params['d_kandang']['d_unit']['id'] ]['detail'][ $v_params['mitra']['d_mitra']['nomor'] ]['nomor'] = $v_params['mitra']['d_mitra']['nomor'];
                $data[ $v_params['d_kandang']['d_unit']['id'] ]['detail'][ $v_params['mitra']['d_mitra']['nomor'] ]['nama'] = $v_params['mitra']['d_mitra']['nama'];
                $data[ $v_params['d_kandang']['d_unit']['id'] ]['detail'][ $v_params['mitra']['d_mitra']['nomor'] ]['kandang'] = $v_params['d_kandang']['kandang'];
                $data[ $v_params['d_kandang']['d_unit']['id'] ]['detail'][ $v_params['mitra']['d_mitra']['nomor'] ]['populasi'] = $v_params['populasi'];
                $data[ $v_params['d_kandang']['d_unit']['id'] ]['detail'][ $v_params['mitra']['d_mitra']['nomor'] ]['noreg'] = $v_params['noreg'];
            }
        }

        ksort($data);

        return $data;
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

    public function get_data_voadip() {
        $kategori = $this->input->post('kategori');

        try {
            $m_brg = new \Model\Storage\Barang_model();
            $d_nomor = $m_brg->select('kode')->distinct('kode')->where('tipe', 'obat')->where('kategori', $kategori)->get()->toArray();

            $datas = array();
            if ( !empty($d_nomor) ) {
                foreach ($d_nomor as $nomor) {
                    $pelanggan = $m_brg->where('tipe', 'obat')
                                              ->where('kode', $nomor['kode'])
                                              ->orderBy('version', 'desc')
                                              ->orderBy('id', 'desc')
                                              ->with(['logs'])
                                              ->first()->toArray();

                    array_push($datas, $pelanggan);
                }
            }

            $this->result['status'] = 1;
            $this->result['content'] = $datas;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function get_list_pwk()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_region = $m_wilayah->where('jenis', 'RG')->orderBy('nama', 'ASC')->get()->toArray();


        $datas = array();
        if ( count($d_region) > 0 ) {
            foreach ($d_region as $k_region => $v_region) {
                $datas[ $v_region['id'] ] = array(
                    'id' => $v_region['id'],
                    'nama' => $v_region['nama'],
                    'jenis' => $v_region['jenis']
                );

                $d_pwk = $m_wilayah->where('jenis', 'PW')->where('induk', $v_region['id'])->orderBy('nama', 'ASC')->get()->toArray();
                if ( count($d_pwk) > 0 ) {
                    foreach ($d_pwk as $k_pwk => $v_pwk) {
                        $rowspan_pwk = 0;
                        $datas[ $v_region['id'] ]['perwakilan'][ $v_pwk['id'] ] = array(
                            'id' => $v_pwk['id'],
                            'nama' => $v_pwk['nama'],
                            'jenis' => $v_pwk['jenis']
                        );
                    }
                } else {
                    $datas[ $v_region['id'] ]['perwakilan'] = array();
                }
            }
        }

        return $datas;
    }

    public function get_list_peternak()
    {
        $id_pwk = $this->input->post('id_pwk');

        $datas = array();
        try {
            $m_mitra_mapping = new \Model\Storage\MitraMapping_model();
            $d_mitra_mapping = $m_mitra_mapping->where('perwakilan', $id_pwk)->with(['dMitra'])->get()->toArray();

            if ( !empty($d_mitra_mapping) ) {
                foreach ($d_mitra_mapping as $key => $val) {
                    $rt = !empty($val['d_mitra']['alamat_rt']) ? ' ,RT.'.$val['d_mitra']['alamat_rt'] : null;
                    $rw = !empty($val['d_mitra']['alamat_rw']) ? '/RW.'.$val['d_mitra']['alamat_rw'] : null;
                    $kelurahan = !empty($val['d_mitra']['alamat_kelurahan']) ? ' ,'.$val['d_mitra']['alamat_kelurahan'] : null;
                    $kecamatan = !empty($val['d_mitra']['d_kecamatan']) ? ' ,'.$val['d_mitra']['d_kecamatan']['nama'] : null;

                    $alamat = $val['d_mitra']['alamat_jalan'] . $rt . $rw . $kelurahan . $kecamatan;
                    $datas[] = array(
                        'nomor' => $val['d_mitra']['nomor'],
                        'nama' => $val['d_mitra']['nama'],
                        'alamat' => strtoupper($alamat)
                    );
                }
            }

            $this->result['status'] = 1;
            $this->result['content'] = $datas;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function save_order_doc()
    {
        $params = $this->input->post('params');

        try {
            $m_order_doc = new \Model\Storage\OrderDoc_model();
            $now = $m_order_doc->getDate();

            $nomor = $m_order_doc->getNextNomor();

            $m_order_doc->id = $m_order_doc->getNextIdentity();
            $m_order_doc->no_order = $nomor;
            $m_order_doc->noreg = $params['noreg'];
            $m_order_doc->supplier = $params['supplier'];
            $m_order_doc->item = $params['item'];
            $m_order_doc->jml_ekor = $params['jml_ekor'];
            $m_order_doc->jml_box = $params['jml_box'];
            $m_order_doc->rencana_tiba = $params['rencana_tiba'];
            $m_order_doc->user_submit = $this->userid;
            $m_order_doc->tgl_submit = $now['waktu'];
            $m_order_doc->keterangan = $params['keterangan'];
            $m_order_doc->version = 1;
            $m_order_doc->save();

            $deskripsi_log_order_doc = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_order_doc, $deskripsi_log_order_doc);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Order DOC berhasil disimpan.';

        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit_order_doc()
    {
        $params = $this->input->post('params');

        try {
            $m_order_doc = new \Model\Storage\OrderDoc_model();
            $now = $m_order_doc->getDate();

            $nomor = $params['no_order'];

            $m_order_doc->id = $m_order_doc->getNextIdentity();
            $m_order_doc->no_order = $nomor;
            $m_order_doc->noreg = $params['noreg'];
            $m_order_doc->supplier = $params['supplier'];
            $m_order_doc->item = $params['item'];
            $m_order_doc->jml_ekor = $params['jml_ekor'];
            $m_order_doc->jml_box = $params['jml_box'];
            $m_order_doc->rencana_tiba = $params['rencana_tiba'];
            $m_order_doc->user_submit = $this->userid;
            $m_order_doc->tgl_submit = $now['waktu'];
            $m_order_doc->keterangan = $params['keterangan'];
            $m_order_doc->version = $params['version'] + 1;
            $m_order_doc->save();

            $deskripsi_log_order_doc = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $m_order_doc, $deskripsi_log_order_doc);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Order DOC berhasil diupdate.';

        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function save_terima_doc()
    {
        $params = $this->input->post('params');

        try {
            $m_terima_doc = new \Model\Storage\TerimaDoc_model();
            $now = $m_terima_doc->getDate();

            $nomor = $m_terima_doc->getNextNomor();

            $m_terima_doc->id = $m_terima_doc->getNextIdentity();
            $m_terima_doc->no_terima = $nomor;
            $m_terima_doc->no_order = $params['no_order'];
            $m_terima_doc->no_sj = $params['no_sj'];
            $m_terima_doc->nopol = $params['nopol'];
            $m_terima_doc->datang = $params['datang'];
            $m_terima_doc->supplier = $params['supplier'];
            $m_terima_doc->jml_ekor = $params['jml_ekor'];
            $m_terima_doc->jml_box = $params['jml_box'];
            $m_terima_doc->user_submit = $this->userid;
            $m_terima_doc->tgl_submit = $now['waktu'];
            $m_terima_doc->kondisi = $params['kondisi'];
            $m_terima_doc->keterangan = $params['keterangan'];
            $m_terima_doc->version = 1;
            $m_terima_doc->kirim = $params['kirim'];
            $m_terima_doc->save();

            $deskripsi_log_terima_doc = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_terima_doc, $deskripsi_log_terima_doc);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Terima DOC berhasil disimpan.';

        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit_terima_doc()
    {
        $params = $this->input->post('params');

        try {
            $m_terima_doc = new \Model\Storage\TerimaDoc_model();
            $now = $m_terima_doc->getDate();

            $m_terima_doc->id = $m_terima_doc->getNextIdentity();
            $m_terima_doc->no_terima = $params['no_terima'];
            $m_terima_doc->no_order = $params['no_order'];
            $m_terima_doc->no_sj = $params['no_sj'];
            $m_terima_doc->nopol = $params['nopol'];
            $m_terima_doc->datang = $params['datang'];
            $m_terima_doc->supplier = $params['supplier'];
            $m_terima_doc->jml_ekor = $params['jml_ekor'];
            $m_terima_doc->jml_box = $params['jml_box'];
            $m_terima_doc->user_submit = $this->userid;
            $m_terima_doc->tgl_submit = $now['waktu'];
            $m_terima_doc->kondisi = $params['kondisi'];
            $m_terima_doc->keterangan = $params['keterangan'];
            $m_terima_doc->version = $params['version'] + 1;
            $m_terima_doc->kirim = $params['kirim'];
            $m_terima_doc->save();

            $deskripsi_log_terima_doc = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $m_terima_doc, $deskripsi_log_terima_doc);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Terima DOC berhasil diupdate.';

        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function save_order_voadip()
    {
        $params = $this->input->post('params');

        try {
            $m_order_voadip = new \Model\Storage\OrderVoadip_model();
            $now = $m_order_voadip->getDate();

            $nomor = $m_order_voadip->getNextNomor();

            $m_order_voadip->id = $m_order_voadip->getNextIdentity();
            $m_order_voadip->no_order = $nomor;
            $m_order_voadip->supplier = $params['supplier'];
            $m_order_voadip->tanggal = $params['tanggal'];
            $m_order_voadip->user_submit = $this->userid;
            $m_order_voadip->tgl_submit = $now['waktu'];
            $m_order_voadip->version = 1;
            $m_order_voadip->save();

            $deskripsi_log_order_voadip = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_order_voadip, $deskripsi_log_order_voadip);

            foreach ($params['detail'] as $k_detail => $v_detail) {
                $m_order_voadip_detail = new \Model\Storage\OrderVoadipDetail_model();

                $m_order_voadip_detail->id = $m_order_voadip_detail->getNextIdentity();
                $m_order_voadip_detail->id_order = $m_order_voadip->id;
                $m_order_voadip_detail->kode_barang = $v_detail['barang'];
                $m_order_voadip_detail->kemasan = $v_detail['kemasan'];
                $m_order_voadip_detail->harga = $v_detail['harga'];
                $m_order_voadip_detail->jumlah = $v_detail['jumlah'];
                $m_order_voadip_detail->total = $v_detail['total'];
                $m_order_voadip_detail->kirim_ke = $v_detail['kirim_ke'];
                $m_order_voadip_detail->alamat = $v_detail['alamat'];
                $m_order_voadip_detail->kirim = !empty($v_detail['peternak']) ? $v_detail['peternak'] : $v_detail['kantor'];
                $m_order_voadip_detail->save();
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Order Voadip berhasil disimpan.';

        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit_order_voadip()
    {
        $params = $this->input->post('params');

        try {
            $m_order_voadip = new \Model\Storage\OrderVoadip_model();
            $now = $m_order_voadip->getDate();

            $nomor = $params['no_order'];

            $m_order_voadip->id = $m_order_voadip->getNextIdentity();
            $m_order_voadip->no_order = $nomor;
            $m_order_voadip->supplier = $params['supplier'];
            $m_order_voadip->tanggal = $params['tanggal'];
            $m_order_voadip->user_submit = $this->userid;
            $m_order_voadip->tgl_submit = $now['waktu'];
            $m_order_voadip->version = $params['version']+1;
            $m_order_voadip->save();

            $deskripsi_log_order_voadip = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $m_order_voadip, $deskripsi_log_order_voadip);

            foreach ($params['detail'] as $k_detail => $v_detail) {
                $m_order_voadip_detail = new \Model\Storage\OrderVoadipDetail_model();

                $m_order_voadip_detail->id = $m_order_voadip_detail->getNextIdentity();
                $m_order_voadip_detail->id_order = $m_order_voadip->id;
                $m_order_voadip_detail->kode_barang = $v_detail['barang'];
                $m_order_voadip_detail->kemasan = $v_detail['kemasan'];
                $m_order_voadip_detail->harga = $v_detail['harga'];
                $m_order_voadip_detail->jumlah = $v_detail['jumlah'];
                $m_order_voadip_detail->total = $v_detail['total'];
                $m_order_voadip_detail->kirim_ke = $v_detail['kirim_ke'];
                $m_order_voadip_detail->alamat = $v_detail['alamat'];
                $m_order_voadip_detail->kirim = !empty($v_detail['peternak']) ? $v_detail['peternak'] : $v_detail['kantor'];
                $m_order_voadip_detail->save();
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Order Voadip berhasil diupdate.';

        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function tes()
    {
        $params = $this->get_data_rdim('VOADIP');
        $datas = $this->mapping_data_rdim($params);

        cetak_r($datas);
    }
}