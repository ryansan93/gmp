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
                "assets/jquery/tupage-table/jquery.tupage.table.js",
                "assets/transaksi/odvp/js/odvp.js",
            ));
            $this->add_external_css(array(
                // "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                // "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/bootstrap-datetimepicker/css/bootstrap-datetimepicker.css",
                "assets/select2/css/select2.min.css",
                "assets/jquery/tupage-table/jquery.tupage.table.css",
                "assets/transaksi/odvp/css/odvp.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['perusahaan'] = $this->getPerusahaan();

            // Load Indexx
            $data['title_menu'] = 'Order DOC, Pakan dan Voadip';
            $data['view'] = $this->load->view('transaksi/odvp/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getPerusahaan()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select p1.* from perusahaan p1
            right join
                (select max(id) as id, kode from perusahaan group by kode) p2
                on
                    p1.id = p2.id
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
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

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                ov.*,
                supl.nama as nama_supplier,
                prs.perusahaan as nama_perusahaan,
                case
                    when kv.id is not null then
                        1
                    else
                        0
                end as status_kirim
            from order_voadip ov
            right join
                (
                    select max(id) as id, no_order from order_voadip group by no_order
                ) _ov
                on
                    ov.id = _ov.id
            left join
                (select id_order, perusahaan from order_voadip_detail group by id_order, perusahaan) ovd
                on
                    ov.id = ovd.id_order
            left join
                (
                    select p1.* from perusahaan p1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) p2
                        on
                            p1.id = p2.id
                ) prs
                on
                    prs.kode = ovd.perusahaan
            left join
                (
                    select supl1.* from pelanggan supl1
                    right join
                        (select max(id) as id, nomor from pelanggan p where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) supl2
                        on
                            supl1.id = supl2.id
                ) supl
                on
                    ov.supplier = supl.nomor
            left join
                kirim_voadip kv
                on
                    kv.no_order = ov.no_order 
            where
                ov.tanggal between '".$params['start_date']."' and '".$params['end_date']."'
            order by
                ov.tanggal desc,
                ov.no_order desc
        ";
        $d_ov = $m_conf->hydrateRaw( $sql );
        if ( $d_ov->count() > 0 ) {
            $d_ov = $d_ov->toArray();
        }

        // $m_order_voadip = new \Model\Storage\OrderVoadip_model();
        // $d_nomor = $m_order_voadip->select('no_order')
        //                  ->distinct('no_order')
        //                  ->whereBetween('tanggal', [$params['start_date'], $params['end_date']])
        //                  ->get()->toArray();

        // $datas = array();
        // if ( !empty($d_nomor) ) {
        //     foreach ($d_nomor as $nomor) {
        //         $order_voadip = $m_order_voadip->where('no_order', $nomor['no_order'])
        //                                        ->orderBy('version', 'desc')
        //                                        ->orderBy('id', 'desc')
        //                                        ->with(['d_supplier'])
        //                                        ->first()->toArray();

        //         $key = str_replace('-', '', $order_voadip['tanggal']).'-'.$nomor['no_order'];
        //         $datas[ $key ] = $order_voadip;
        //     }
        // }

        // if ( !empty($datas) ) {
        //     krsort( $datas );
        // }

        $content['data'] = $d_ov;
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

                $key = $brg['nama'].' | '.$brg['kode'];
                $datas[ $key ] = $brg;

                ksort($datas);

                // array_push($datas, $brg);
            }
        }

        return $datas;
    }

    public function order_doc_form()
    {
        $content['data_doc'] = $this->get_data_doc();
        $content['supplier'] = $this->get_data_supplier();
        $content['perusahaan'] = $this->get_data_perusahaan();
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
        $content['perusahaan'] = $this->get_data_perusahaan();
        $this->load->view('transaksi/odvp/order_doc_edit_form', $content);
    }

    public function order_doc_view_form()
    {
        $no_order = $this->input->get('no_order');

        $m_order_doc = new \Model\Storage\OrderDoc_model();
        $d_order_doc = $m_order_doc->where('no_order', $no_order)->with(['data_perusahaan'])->orderBy('version', 'DESC')->first();

        $content['data_order_doc'] = $d_order_doc;
        $content['data_doc'] = $this->get_data_doc();
        $content['supplier'] = $this->get_data_supplier();
        $this->load->view('transaksi/odvp/order_doc_view_form', $content);
    }

    public function terima_doc_form()
    {
        $no_order = $this->input->get('no_order');

        $m_order_doc = new \Model\Storage\OrderDoc_model();
        $d_order_doc = $m_order_doc->where('no_order', $no_order)->orderBy('id', 'DESC')->first();

        $content['data_order_doc'] = $d_order_doc;
        $content['data_doc'] = $this->get_data_doc();
        $content['supplier'] = $this->get_data_supplier();
        $this->load->view('transaksi/odvp/terima_doc_form', $content);
    }

    public function terima_doc_edit_form()
    {
        $id = $this->input->get('id');
        $no_order = $this->input->get('no_order');

        $m_terima_doc = new \Model\Storage\TerimaDoc_model();
        $d_terima_doc = $m_terima_doc->where('id', $id)->where('no_order', $no_order)->with(['order_doc'])->orderBy('id', 'DESC')->first();

        $content['data_terima_doc'] = $d_terima_doc;
        $content['data_doc'] = $this->get_data_doc();
        $content['supplier'] = $this->get_data_supplier();
        $this->load->view('transaksi/odvp/terima_doc_edit_form', $content);
    }

    public function terima_doc_view_form()
    {
        $id = $this->input->get('id');

        $m_terima_doc = new \Model\Storage\TerimaDoc_model();
        $d_terima_doc = $m_terima_doc->where('id', $id)->with(['order_doc', 'logs'])->orderBy('id', 'DESC')->first();

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

        $content['peternak'] = $this->get_peternak();
        $content['supplier'] = $this->get_data_supplier();
        $content['perwakilan'] = $this->get_list_pwk();
        $content['perusahaan'] = $this->get_data_perusahaan();
        $content['gudang'] = $this->get_data_gudang('obat');
            
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
                                                       ->with(['d_barang', 'data_perusahaan'])
                                                       ->get()->toArray();

        $data['id'] = $d_order_voadip['id'];
        $data['no_order'] = $d_order_voadip['no_order'];
        $data['tanggal'] = $d_order_voadip['tanggal'];
        $data['no_supl'] = $d_order_voadip['supplier'];
        $data['version'] = $d_order_voadip['version'];

        foreach ($d_order_voadip_detail as $k_detail => $v_detail) {
            $data['detail'][] = array(
                'perusahaan' => $v_detail['perusahaan'],
                'kategori' => $v_detail['d_barang']['kategori'],
                'barang' => $v_detail['d_barang']['nama'],
                'id_barang' => $v_detail['d_barang']['kode'],
                'kemasan' => $v_detail['kemasan'],
                'harga' => $v_detail['harga'],
                'harga_jual' => $v_detail['harga_jual'],
                'jumlah' => $v_detail['jumlah'],
                'total' => $v_detail['total'],
                'kirim_ke' => $v_detail['kirim_ke'],
                'kirim' => $v_detail['kirim'],
                'alamat' => $v_detail['alamat'],
                'tgl_kirim' => $v_detail['tgl_kirim']
            );
        }

        $content['kategori_voadip'] = array(
                'vitamin' => 'Vitamin',
                'desinfektan' => 'Desinfektan',
                'obat' => 'Obat',
                'vaksin' => 'Vaksin'
            );

        $content['peternak'] = $this->get_peternak();
        $content['supplier'] = $this->get_data_supplier();
        $content['perwakilan'] = $this->get_list_pwk();
        $content['perusahaan'] = $this->get_data_perusahaan();
        $content['gudang'] = $this->get_data_gudang('obat');

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
                                                       ->with(['d_barang', 'data_perusahaan'])
                                                       ->get()->toArray();

        $data['id'] = $d_order_voadip['id'];
        $data['no_order'] = $d_order_voadip['no_order'];
        $data['tanggal'] = $d_order_voadip['tanggal'];
        $data['no_supl'] = $d_order_voadip['supplier'];
        $data['nama_supl'] = $d_order_voadip['d_supplier']['nama'];

        foreach ($d_order_voadip_detail as $k_detail => $v_detail) {

            $kirim_ke = $v_detail['kirim_ke'];
            $kirim = '-';
            if ( $v_detail['kirim_ke'] == 'peternak' ) {
                $m_rs = new \Model\Storage\RdimSubmit_model();
                $d_rs = $m_rs->where('noreg', $v_detail['kirim'])->with(['dMitraMapping'])->first();

                $kirim = $d_rs->dMitraMapping->dMitra->nama;
            } else {
                $m_gudang = new \Model\Storage\Gudang_model();
                $d_gudang = $m_gudang->where('id', $v_detail['kirim'])->first();

                $kirim = !empty($d_gudang) ? $d_gudang->nama : '-';
            }

            $data['detail'][] = array(
                'perusahaan' => $v_detail['data_perusahaan']['perusahaan'],
                'kategori' => $v_detail['d_barang']['kategori'],
                'barang' => $v_detail['d_barang']['nama'],
                'kemasan' => $v_detail['kemasan'],
                'harga' => $v_detail['harga'],
                'harga_jual' => $v_detail['harga_jual'],
                'jumlah' => $v_detail['jumlah'],
                'total' => $v_detail['total'],
                'kirim_ke' => $kirim_ke,
                'kirim' => $kirim,
                'alamat' => $v_detail['alamat'],
                'tgl_kirim' => $v_detail['tgl_kirim']
            );
        }

        $content['data'] = $data;
        $this->load->view('transaksi/odvp/order_voadip_view_form', $content);
    }

    public function get_data_rdim($tipe = null, $start_date = null, $end_date = null)
    {
        $data = null;
        if ( $start_date != null && $end_date != null ) {
            if ( $tipe == 'DOC' ) {
                $m_rdim_submit = new \Model\Storage\RdimSubmit_model();
                $d_rdim_submit = $m_rdim_submit->whereBetween('tgl_docin', [$start_date, $end_date])
                                 ->with(['dKandang', 'mitra', 'order_doc'])
                                 ->orderBy('tgl_docin', 'desc')
                                 ->get()->toArray();

                foreach ($d_rdim_submit as $k => $val) {
                    $order_doc = null;
                    foreach ($val['order_doc'] as $k_od => $v_od) {
                        $order_doc[ $v_od['no_order'] ] = array(
                            'tgl_submit' => $v_od['tgl_submit'],
                            'no_order' => $v_od['no_order'],
                            'jml_ekor' => $v_od['jml_ekor'],
                            'terima_doc' => $v_od['terima_doc']
                        );
                    }

                    $data[] = array(
                        'tgl_docin' => $val['tgl_docin'],
                        'noreg' => $val['noreg'],
                        'populasi' => $val['populasi'],
                        'd_kandang' => $val['d_kandang'],
                        'mitra' => $val['mitra'],
                        'order_doc' => $order_doc
                    );
                }
            } else if ( $tipe == 'VOADIP' ) {
                $m_rdim_submit = new \Model\Storage\RdimSubmit_model();
                $d_rdim_submit = $m_rdim_submit->whereBetween('tgl_docin', [$start_date, $end_date])
                                 ->with(['dKandang', 'mitra'])
                                 ->orderBy('tgl_docin', 'desc')
                                 ->get()->toArray();

                $data = $d_rdim_submit;
            }
        } else {
            if ( $tipe == 'DOC' ) {
                $m_rdim_submit = new \Model\Storage\RdimSubmit_model();
                $d_rdim_submit = $m_rdim_submit->with(['dKandang', 'mitra', 'order_doc'])
                                 ->orderBy('tgl_docin', 'desc')
                                 ->take(20)
                                 ->get()->toArray();

                foreach ($d_rdim_submit as $k => $val) {
                    $order_doc = null;
                    foreach ($val['order_doc'] as $k_od => $v_od) {
                        $order_doc[ $v_od['no_order'] ] = array(
                            'tgl_submit' => $v_od['tgl_submit'],
                            'no_order' => $v_od['no_order'],
                            'jml_ekor' => $v_od['jml_ekor'],
                            'terima_doc' => $v_od['terima_doc']
                        );
                    }

                    $data[] = array(
                        'tgl_docin' => $val['tgl_docin'],
                        'noreg' => $val['noreg'],
                        'populasi' => $val['populasi'],
                        'd_kandang' => $val['d_kandang'],
                        'mitra' => $val['mitra'],
                        'order_doc' => $order_doc
                    );
                }
            } else if ( $tipe == 'VOADIP' ) {
                $m_rdim_submit = new \Model\Storage\RdimSubmit_model();
                $d_rdim_submit = $m_rdim_submit->with(['dKandang', 'mitra'])
                                 ->orderBy('tgl_docin', 'desc')
                                 ->get()->toArray();

                $data = $d_rdim_submit;
            }
        }

        return $data;
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
        $datas = array();

        $m_supplier = new \Model\Storage\Supplier_model();
        $sql = "
            select p.* from pelanggan p 
            right join
                (select max(id) as id from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) p2
                on
                    p.id = p2.id
        ";

        $d_supplier = $m_supplier->hydrateRaw( $sql );

        if ( $d_supplier->count() > 0 ) {
            $d_supplier = $d_supplier->toArray();
            foreach ($d_supplier as $k_supplier => $v_supplier) {
                $key = $v_supplier['nama'].' - '.$v_supplier['nomor'];
                $datas[ $key ] =  $v_supplier;
            }

            ksort($datas);
        }

        // $d_nomor = $m_supplier->select('nomor')->distinct('nomor')->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->get()->toArray();

        // if ( !empty($d_nomor) ) {
        //     foreach ($d_nomor as $nomor) {
        //         $supplier = $m_supplier->where('tipe', 'supplier')
        //                                   ->where('nomor', $nomor['nomor'])
        //                                   ->orderBy('version', 'desc')
        //                                   ->orderBy('id', 'desc')
        //                                   ->first()->toArray();

        //         $key = $supplier['nama'].' - '.$supplier['nomor'];
        //         $datas[ $key ] =  $supplier;

        //         ksort($datas);
        //         // array_push($datas, $supplier);
        //     }
        // }

        return $datas;
    }

    public function get_data_perusahaan()
    {
        $datas = array();

        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $sql = "
            select p.* from perusahaan p 
            right join
                (select max(id) as id from perusahaan group by kode) p2
                on
                    p.id = p2.id
        ";

        $d_perusahaan = $m_perusahaan->hydrateRaw( $sql );

        if ( $d_perusahaan->count() > 0 ) {
            $d_perusahaan = $d_perusahaan->toArray();

            foreach ($d_perusahaan as $k_perusahaan => $v_perusahaan) {
                $key = $v_perusahaan['perusahaan'].' | '.$v_perusahaan['kode'];
                $datas[ $key ] = $v_perusahaan;
            }

            ksort($datas);
        }
        // $d_nomor = $m_perusahaan->select('kode')->distinct('kode')->get()->toArray();

        // if ( !empty($d_nomor) ) {
        //     foreach ($d_nomor as $nomor) {
        //         $perusahaan = $m_perusahaan->where('kode', $nomor['kode'])
        //                                   ->orderBy('version', 'desc')
        //                                   ->orderBy('id', 'desc')
        //                                   ->first()->toArray();

        //         $key = $perusahaan['perusahaan'].' | '.$perusahaan['kode'];
        //         $datas[ $key ] = $perusahaan;

        //         ksort($datas);

        //         // array_push($datas, $perusahaan);
        //     }
        // }

        return $datas;
    }

    public function get_data_voadip() {
        $kategori = $this->input->post('kategori');

        try {
            $datas = array();

            $m_brg = new \Model\Storage\Barang_model();
            $sql = "
                select b.* from barang b 
                right join
                    (select max(id) as id from barang where tipe = 'obat' and kategori = '$kategori' group by kode) b2
                    on
                        b.id = b2.id
                order by
                    b.nama asc,
                    b.kode asc
            ";

            $d_brg = $m_brg->hydrateRaw( $sql );

            if ( $d_brg->count() > 0 ) {
                $datas = $d_brg->toArray();
            }

            // $d_nomor = $m_brg->select('kode')->distinct('kode')->where('tipe', 'obat')->where('kategori', $kategori)->get()->toArray();

            // $_datas = array();
            // if ( !empty($d_nomor) ) {
            //     foreach ($d_nomor as $nomor) {
            //         $brg = $m_brg->where('tipe', 'obat')
            //                       ->where('kode', $nomor['kode'])
            //                       ->orderBy('version', 'desc')
            //                       ->orderBy('id', 'desc')
            //                       ->with(['logs'])
            //                       ->first()->toArray();

            //         $key = $brg['nama'].' | '.$brg['kode'];
            //         $_datas[ $key ] = $brg;

            //         // array_push($datas, $pelanggan);
            //     }
            // }

            // $datas = array();
            // if ( !empty($_datas) ) {
            //     ksort( $_datas );

            //     foreach ($_datas as $key => $value) {
            //         array_push($datas, $value);
            //     }
            // }

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
        $params = $this->input->post('params');

        $id_pwk = $params['id_pwk'];
        $tgl_docin = $params['tgl_docin'];

        $datas = array();
        try {
            $m_rs = new \Model\Storage\RdimSubmit_model();
            $d_rs = $m_rs->where('tgl_docin', $tgl_docin)->get()->toArray();

            if ( count($d_rs) > 0 ) {
                foreach ($d_rs as $k_rs => $v_rs) {
                    $m_mitra_mapping = new \Model\Storage\MitraMapping_model();
                    $d_mitra_mapping = $m_mitra_mapping->where('perwakilan', $id_pwk)->where('nim', $v_rs['nim'])->with(['dMitra'])->first();

                    if ( !empty($d_mitra_mapping) ) {
                        $d_mitra_mapping = $d_mitra_mapping->toArray();

                        $rt = !empty($d_mitra_mapping['d_mitra']['alamat_rt']) ? ' ,RT.'.$d_mitra_mapping['d_mitra']['alamat_rt'] : null;
                        $rw = !empty($d_mitra_mapping['d_mitra']['alamat_rw']) ? '/RW.'.$d_mitra_mapping['d_mitra']['alamat_rw'] : null;
                        $kelurahan = !empty($d_mitra_mapping['d_mitra']['alamat_kelurahan']) ? ' ,'.$d_mitra_mapping['d_mitra']['alamat_kelurahan'] : null;
                        $kecamatan = !empty($d_mitra_mapping['d_mitra']['d_kecamatan']) ? ' ,'.$d_mitra_mapping['d_mitra']['d_kecamatan']['nama'] : null;

                        $alamat = $d_mitra_mapping['d_mitra']['alamat_jalan'] . $rt . $rw . $kelurahan . $kecamatan;
                        $datas[] = array(
                            'noreg' => $v_rs['noreg'],
                            'nomor' => $d_mitra_mapping['d_mitra']['nomor'],
                            'nama' => $d_mitra_mapping['d_mitra']['nama'],
                            'alamat' => strtoupper($alamat)
                        );
                    }
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

            $m_rs = new \Model\Storage\RdimSubmit_model();
            $d_rs = $m_rs->where('noreg', $params['noreg'])->with(['dKandang'])->first();

            $kode_unit = null;
            if ( $d_rs ) {
                $d_rs = $d_rs->toArray();

                $kandang = (int) substr($d_rs['noreg'], -2);

                $m_mitra = new \Model\Storage\MitraMapping_model();
                $d_mitra = $m_mitra->where('nim', $d_rs['nim'])->orderBy('id', 'desc')->first();

                $m_kdg = new \Model\Storage\Kandang_model();
                $d_kdg = $m_kdg->where('mitra_mapping', $d_mitra->id)->where('kandang', $kandang)->with(['d_unit'])->orderBy('id', 'desc')->first();

                $kode_unit = $d_kdg->d_unit->kode;
            }

            $nomor = $m_order_doc->getNextNomor('ODC/'.$kode_unit);

            $m_order_doc->id = $m_order_doc->getNextIdentity();
            $m_order_doc->no_order = $nomor;
            $m_order_doc->noreg = $params['noreg'];
            $m_order_doc->supplier = $params['supplier'];
            $m_order_doc->item = $params['item'];
            $m_order_doc->jml_ekor = $params['jml_ekor'];
            $m_order_doc->jml_box = $params['jml_box'];
            $m_order_doc->rencana_tiba = $params['rencana_tiba'];
            $m_order_doc->user_submit = $this->userid;
            $m_order_doc->tgl_submit = $params['tgl_order'].' '.substr($now['waktu'], 11, 5);
            $m_order_doc->keterangan = $params['keterangan'];
            $m_order_doc->version = 1;
            $m_order_doc->perusahaan = $params['perusahaan'];
            $m_order_doc->jns_box = $params['jns_box'];
            $m_order_doc->harga = $params['harga'];
            $m_order_doc->total = $params['total'];
            $m_order_doc->save();

            $this->insertKonfirmasi( $nomor );

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
            $m_order_doc->tgl_submit = $params['tgl_order'].' '.substr($now['waktu'], 11, 5);
            $m_order_doc->keterangan = $params['keterangan'];
            $m_order_doc->version = $params['version'] + 1;
            $m_order_doc->perusahaan = $params['perusahaan'];
            $m_order_doc->jns_box = $params['jns_box'];
            $m_order_doc->harga = $params['harga'];
            $m_order_doc->total = $params['total'];
            $m_order_doc->save();

            $this->insertKonfirmasi( $nomor );

            $deskripsi_log_order_doc = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $m_order_doc, $deskripsi_log_order_doc);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Order DOC berhasil diupdate.';

        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete_order_doc()
    {
        $params = $this->input->post('params');

        try {
            $m_order_doc = new \Model\Storage\OrderDoc_model();
            $d_order_doc = $m_order_doc->where('id', $params)->first();

            $m_order_doc->where('id', $params)->delete();

            $this->insertKonfirmasi( $d_order_doc->no_order );

            $deskripsi_log_order_doc = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_order_doc, $deskripsi_log_order_doc);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Order DOC berhasil dihapus.';

        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    function insertKonfirmasi($no_order) {
        $m_kpdd = new \Model\Storage\KonfirmasiPembayaranDocDet_model();
        $d_kpdd = $m_kpdd->where('no_order', $no_order)->first();

        if ( $d_kpdd ) {
            $m_kpdd = new \Model\Storage\KonfirmasiPembayaranDocDet_model();
            $m_kpdd->where('no_order', $no_order)->delete();

            $m_kpd = new \Model\Storage\KonfirmasiPembayaranDoc_model();
            $m_kpd->where('id', $d_kpdd->id_header)->delete();
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                od.tgl_submit as tgl_bayar,
                od.rencana_tiba as periode_docin,
                od.perusahaan,
                od.supplier,
                od.tgl_submit as tgl_order,
                SUBSTRING(od.no_order, 5, 3) as id_kab_kota,
                od.no_order,
                mm.nomor as no_peternak,
                cast(SUBSTRING(od.noreg, 10, 2) as int) as kandang,
                od.jml_ekor,
                od.harga,
                od.total,
                rs.populasi
            from
            (
                select od1.* from order_doc od1
                right join
                    (select max(id) as id, no_order from order_doc group by no_order) od2
                    on
                        od1.id = od2.id
            ) od
            left join
                rdim_submit rs
                on
                    rs.noreg = od.noreg
            left join
                (
                    select mm1.* from mitra_mapping mm1
                    right join
                        (select max(id) as id, nim from mitra_mapping group by nim) mm2
                        on
                            mm1.nim = mm2.nim
                ) mm
                on
                    mm.nim = rs.nim
            where
                od.no_order = '".$no_order."'
            group by
                od.tgl_submit,
                od.rencana_tiba,
                od.perusahaan,
                od.supplier,
                SUBSTRING(od.no_order, 5, 3),
                od.no_order,
                mm.nomor,
                cast(SUBSTRING(od.noreg, 10, 2) as int),
                od.jml_ekor,
                od.harga,
                od.total,
                rs.populasi
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray()[0];

            $m_kpd = new \Model\Storage\KonfirmasiPembayaranDoc_model();
            $nomor = $m_kpd->getNextNomor();

            $m_kpd->nomor = $nomor;
            $m_kpd->tgl_bayar = substr($d_conf['tgl_bayar'], 0, 10);
            $m_kpd->periode = trim($d_conf['periode_docin']);
            $m_kpd->perusahaan = $d_conf['perusahaan'];
            $m_kpd->supplier = $d_conf['supplier'];
            $m_kpd->total = $d_conf['total'];
            // $m_kpd->rekening = $d_conf['rekening'];
            // $m_kpd->total = $params['total'];
            $m_kpd->save();

            $id = $m_kpd->id;

            $m_kpdd = new \Model\Storage\KonfirmasiPembayaranDocDet_model();
            $m_kpdd->id_header = $id;
            $m_kpdd->tgl_order = substr($d_conf['tgl_order'], 0, 10);
            $m_kpdd->kode_unit = $d_conf['id_kab_kota'];
            $m_kpdd->no_order = $d_conf['no_order'];
            $m_kpdd->mitra = $d_conf['no_peternak'];
            $m_kpdd->kandang = $d_conf['kandang'];
            $m_kpdd->populasi = $d_conf['populasi'];
            $m_kpdd->harga = $d_conf['harga'];
            $m_kpdd->total = $d_conf['total'];
            $m_kpdd->save();

            $d_kpd = $m_kpd->where('id', $id)->first();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_kpd, $deskripsi_log);
        }
    }

    public function save_terima_doc()
    {
        // $params = $this->input->post('params');
        $params = json_decode($this->input->post('data'),TRUE);
        $file = isset($_FILES['file']) ? $_FILES['file'] : null;

        try {
            if ( !empty($file) ) {
                $moved = uploadFile($file);
                if ( $moved ) {
                    $path_name = $moved['path'];

                    $m_terima_doc = new \Model\Storage\TerimaDoc_model();
                    $now = $m_terima_doc->getDate();

                    $nomor = $m_terima_doc->getNextNomor();

                    $id_terima = $m_terima_doc->getNextIdentity();

                    $m_terima_doc->id = $id_terima;
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
                    $m_terima_doc->bb = $params['bb'];
                    $m_terima_doc->harga = $params['harga'];
                    $m_terima_doc->total = $params['total'];
                    $m_terima_doc->path = $path_name;
                    $m_terima_doc->uniformity = $params['uniformity'];
                    $m_terima_doc->save();

                    $m_conf = new \Model\Storage\Conf();
                    $sql = "exec insert_jurnal 'DOC', '".$params['no_order']."', NULL, ".$params['total'].", 'terima_doc', ".$id_terima.", NULL, 1";
                    $d_conf = $m_conf->hydrateRaw( $sql );

                    $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/save', $m_terima_doc, $deskripsi_log);

                    $this->result['status'] = 1;
                    $this->result['message'] = 'Data Terima DOC berhasil disimpan.';
                } else {
                    $this->result['message'] = 'Upload gagal, hubungi tim IT.';
                }
            } else {
                $this->result['message'] = 'File tidak ditemukan.';
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit_terima_doc()
    {
        // $params = $this->input->post('params');
        $params = json_decode($this->input->post('data'),TRUE);
        $file = isset($_FILES['file']) ? $_FILES['file'] : null;

        try {
            $execute = null;
            $path_name = null;
            $id_old = null;
            if ( !empty($file) ) {
                $moved = uploadFile($file);
                if ( $moved ) {
                    $path_name = $moved['path'];

                    $execute = 1;
                } else {
                    $this->result['message'] = 'Upload gagal, hubungi tim IT.';
                }
            } else {
                $m_terima_doc = new \Model\Storage\TerimaDoc_model();
                $d_terima_doc = $m_terima_doc->where('no_terima', $params['no_terima'])->orderBy('id', 'desc')->first();

                $path_name = $d_terima_doc->path;
                $id_old = $d_terima_doc->id;

                $execute = 1;
            }

            if ( $execute == 1 ) {
                $m_terima_doc = new \Model\Storage\TerimaDoc_model();
                $now = $m_terima_doc->getDate();

                $id_terima = $m_terima_doc->getNextIdentity();

                $m_terima_doc->id = $id_terima;
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
                $m_terima_doc->bb = $params['bb'];
                $m_terima_doc->harga = $params['harga'];
                $m_terima_doc->total = $params['total'];
                $m_terima_doc->path = $path_name;
                $m_terima_doc->uniformity = $params['uniformity'];
                $m_terima_doc->save();

                $m_conf = new \Model\Storage\Conf();
                $sql = "exec insert_jurnal 'DOC', '".$params['no_order']."', NULL, ".$params['total'].", 'terima_doc', ".$id_terima.", ".$id_old.", 2";
                $d_conf = $m_conf->hydrateRaw( $sql );

                $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $m_terima_doc, $deskripsi_log);

                $this->result['status'] = 1;
                $this->result['message'] = 'Data Terima DOC berhasil diupdate.';
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete_terima_doc()
    {
        $params = $this->input->post('params');

        try {
            $m_terima_doc = new \Model\Storage\TerimaDoc_model();
            $d_terima_doc = $m_terima_doc->where('id', $params)->first();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal NULL, NULL, NULL, NULL, 'terima_doc', ".$params.", ".$params.", 3";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $m_terima_doc->where('id', $params)->delete();

            $deskripsi_log_terima_doc = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_terima_doc, $deskripsi_log_terima_doc);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Terima DOC berhasil dihapus.';

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

            $kode_unit = null;
            $id_kirim = null;
            $jenis_kirim = null;
            foreach ($params['detail'] as $k_detail => $v_detail) {
                $id_kirim = $v_detail['kirim'];
                $jenis_kirim = $v_detail['kirim_ke'];
            }

            if ( stristr($jenis_kirim, 'gudang') !== FALSE ) {
                $m_gdg = new \Model\Storage\Gudang_model();
                $d_gdg = $m_gdg->where('id', $id_kirim)->with(['dUnit'])->first();

                if ( $d_gdg ) {
                    $d_gdg = $d_gdg->toArray();
                    $kode_unit = $d_gdg['d_unit']['kode'];
                }
            }

            $nomor = $m_order_voadip->getNextNomor('OVO/'.$kode_unit);

            $m_order_voadip->no_order = $nomor;
            $m_order_voadip->supplier = $params['supplier'];
            $m_order_voadip->tanggal = $params['tanggal'];
            $m_order_voadip->user_submit = $this->userid;
            $m_order_voadip->tgl_submit = $now['waktu'];
            $m_order_voadip->version = 1;
            $m_order_voadip->save();

            $id = $m_order_voadip->id;

            foreach ($params['detail'] as $k_detail => $v_detail) {
                $m_order_voadip_detail = new \Model\Storage\OrderVoadipDetail_model();
                $m_order_voadip_detail->id_order = $id;
                $m_order_voadip_detail->kode_barang = $v_detail['barang'];
                $m_order_voadip_detail->kemasan = $v_detail['kemasan'];
                $m_order_voadip_detail->harga = $v_detail['harga'];
                $m_order_voadip_detail->harga_jual = $v_detail['harga_jual'];
                $m_order_voadip_detail->jumlah = $v_detail['jumlah'];
                $m_order_voadip_detail->total = $v_detail['total'];
                $m_order_voadip_detail->kirim_ke = $v_detail['kirim_ke'];
                $m_order_voadip_detail->alamat = $v_detail['alamat'];
                $m_order_voadip_detail->kirim = $v_detail['kirim'];
                $m_order_voadip_detail->perusahaan = $v_detail['perusahaan'];
                $m_order_voadip_detail->tgl_kirim = $v_detail['tgl_kirim'];
                $m_order_voadip_detail->save();
            }

            $d_order_voadip = $m_order_voadip->where('id', $id)->with(['detail'])->first();

            $deskripsi_log_order_voadip = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_order_voadip, $deskripsi_log_order_voadip);

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

            $m_order_voadip->no_order = $nomor;
            $m_order_voadip->supplier = $params['supplier'];
            $m_order_voadip->tanggal = $params['tanggal'];
            $m_order_voadip->user_submit = $this->userid;
            $m_order_voadip->tgl_submit = $now['waktu'];
            $m_order_voadip->version = $params['version']+1;
            $m_order_voadip->save();

            $id = $m_order_voadip->id;

            foreach ($params['detail'] as $k_detail => $v_detail) {
                $m_order_voadip_detail = new \Model\Storage\OrderVoadipDetail_model();

                $m_order_voadip_detail->id_order = $id;
                $m_order_voadip_detail->kode_barang = $v_detail['barang'];
                $m_order_voadip_detail->kemasan = $v_detail['kemasan'];
                $m_order_voadip_detail->harga = $v_detail['harga'];
                $m_order_voadip_detail->harga_jual = $v_detail['harga_jual'];
                $m_order_voadip_detail->jumlah = $v_detail['jumlah'];
                $m_order_voadip_detail->total = $v_detail['total'];
                $m_order_voadip_detail->kirim_ke = $v_detail['kirim_ke'];
                $m_order_voadip_detail->alamat = $v_detail['alamat'];
                $m_order_voadip_detail->kirim = $v_detail['kirim'];
                $m_order_voadip_detail->perusahaan = $v_detail['perusahaan'];
                $m_order_voadip_detail->tgl_kirim = $v_detail['tgl_kirim'];
                $m_order_voadip_detail->save();
            }

            $d_order_voadip = $m_order_voadip->where('id', $id)->with(['detail'])->first();

            $deskripsi_log_order_voadip = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $m_order_voadip, $deskripsi_log_order_voadip);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Order Voadip berhasil diupdate.';
            $this->result['content'] = array('no_order' => $nomor);

        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function order_voadip_delete()
    {
        $params = $this->input->post('params');

        try {
            $m_op = new \Model\Storage\OrderVoadip_model();
            $d_order_voadip = $m_op->where('id', $params)->with(['detail'])->first();

            $m_opd = new \Model\Storage\OrderVoadipDetail_model();
            $m_opd->where('id_order', $params)->delete();

            $m_op = new \Model\Storage\OrderVoadip_model();
            $m_op->where('id', $params)->delete();


            $deskripsi_log_order_voadip = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_order_voadip, $deskripsi_log_order_voadip);
            
            $this->result['status'] = 1;
            $this->result['message'] = 'Data Order Voadip berhasil dihapus';

        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function hitungStokVoadipByTransaksi()
    {
        $params = $this->input->post('params');

        try {
            $no_order = $params['no_order'];

            $id = null;
            $tanggal = null;
            $delete = null;
            $status_jurnal = null;

            $m_kv = new \Model\Storage\KirimVoadip_model();
            $d_kv = $m_kv->where('no_order', $no_order)->first();

            if ( $d_kv ) {
                $m_tv = new \Model\Storage\TerimaVoadip_model();
                $d_tv = $m_tv->where('id_kirim_voadip', $d_kv->id)->first();

                if ( $d_tv ) {
                    $id = $d_tv->id;
                    $tanggal = $d_tv->tgl_terima;
                    $delete = 0;
                    $status_jurnal = 2;
                }
            }

            if ( !empty($id) ) {
                $conf = new \Model\Storage\Conf();
                $sql = "EXEC hitung_stok_voadip_by_transaksi 'terima_voadip', '".$id."', '".$tanggal."', ".$delete.", ".$status_jurnal."";

                $d_conf = $conf->hydrateRaw($sql);
            }

            $this->result['status'] = 1;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function get_lists_pakan()
    {
        $params = $this->input->post('params');

        $data = array();

        // $m_op = new \Model\Storage\OrderPakan_model();
        // $d_op = $m_op->whereBetween('tgl_trans', [$params['start_date'], $params['end_date']])->with(['detail', 'd_supplier'])->orderBy('tgl_trans', 'desc')->get();
        // if ( $d_op->count() > 0 ) {
        //     $d_op = $d_op->toArray();
        // }

        $sql_perusahaan = null;
        if ( !in_array('all', $params['perusahaan']) ) {
            $sql_perusahaan = "and prs.kode in ('".implode("', '", $params['perusahaan'])."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                op.*,
                supl.nama as nama_supplier,
                prs.perusahaan as nama_perusahaan,
                case
                    when kp.id is not null then
                        1
                    else
                        0
                end as status_kirim
            from order_pakan op
            left join
                (select id_header, perusahaan from order_pakan_detail group by id_header, perusahaan) opd
                on
                    op.id = opd.id_header
            left join
                (
                    select p1.* from perusahaan p1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) p2
                        on
                            p1.id = p2.id
                ) prs
                on
                    prs.kode = opd.perusahaan
            left join
                (
                    select supl1.* from pelanggan supl1
                    right join
                        (select max(id) as id, nomor from pelanggan p where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) supl2
                        on
                            supl1.id = supl2.id
                ) supl
                on
                    op.supplier = supl.nomor
            left join
                kirim_pakan kp 
                on
                    kp.no_order = op.no_order 
            where
                op.tgl_trans between '".$params['start_date']."' and '".$params['end_date']."'
                ".$sql_perusahaan."
            order by
                op.tgl_trans desc,
                supl.nama asc
        ";
        $d_op = $m_conf->hydrateRaw( $sql );
        if ( $d_op->count() > 0 ) {
            $d_op = $d_op->toArray();
        }

        $content['data'] = $d_op;
        $html = $this->load->view('transaksi/odvp/list_order_pakan', $content, true);

        $this->result['status'] = 1;
        $this->result['content'] = $html;

        display_json($this->result);
    }

    public function order_pakan_form()
    {
        $data_barang = array();

        $m_brg = new \Model\Storage\Barang_model();
        $sql = "
            select b.* from barang b 
            right join
                (select max(id) as id from barang where tipe = 'pakan' group by kode) b2
                on
                    b.id = b2.id
            order by
                b.nama asc,
                b.kode asc
        ";

        $d_brg = $m_brg->hydrateRaw( $sql );

        if ( $d_brg->count() > 0 ) {
            $data_barang = $d_brg->toArray();
        }

        $content['barang'] = $data_barang;
        $content['peternak'] = $this->get_peternak();
        $content['supplier'] = $this->get_data_supplier();
        $content['perusahaan'] = $this->get_data_perusahaan();
        $content['gudang'] = $this->get_data_gudang('pakan');
        $this->load->view('transaksi/odvp/order_pakan_form', $content);
    }

    public function order_pakan_edit_form()
    {
        $params = $this->input->get('params');

        $m_op = new \Model\Storage\OrderPakan_model();
        $d_op = $m_op->where('no_order', $params)->with(['detail', 'd_supplier'])->first();
        if ( $d_op ) {
            $d_op = $d_op->toArray();
        }

        $data_barang = array();

        $m_brg = new \Model\Storage\Barang_model();
        $sql = "
            select b.* from barang b 
            right join
                (select max(id) as id from barang where tipe = 'pakan' group by kode) b2
                on
                    b.id = b2.id
            order by
                b.nama asc,
                b.kode asc
        ";

        $d_brg = $m_brg->hydrateRaw( $sql );

        if ( $d_brg->count() > 0 ) {
            $data_barang = $d_brg->toArray();
        }

        $content['data'] = $d_op;
        $content['barang'] = $data_barang;
        $content['peternak'] = $this->get_peternak();
        $content['supplier'] = $this->get_data_supplier();
        $content['perusahaan'] = $this->get_data_perusahaan();
        $content['gudang'] = $this->get_data_gudang('pakan');

        // cetak_r( $content['gudang'] );
        // cetak_r( $content['data'], 1 );

        $this->load->view('transaksi/odvp/order_pakan_edit_form', $content);
    }

    public function order_pakan_view_form()
    {
        $params = $this->input->get('params');

        // $m_op = new \Model\Storage\OrderPakan_model();
        // $d_op = $m_op->where('no_order', $params)->with(['detail', 'd_supplier'])->first();
        // if ( $d_op ) {
        //     $d_op = $d_op->toArray();
        // }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                op.*,
                supl.nama as nama_supplier,
                case
                    when kp.id is not null then
                        1
                    else
                        0
                end as status_kirim
            from order_pakan op
            right join
                (
                    select supl1.* from pelanggan supl1
                    right join
                        (select max(id) as id, nomor from pelanggan p where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) supl2
                        on
                            supl1.id = supl2.id
                ) supl
                on
                    op.supplier = supl.nomor
            left join
                kirim_pakan kp 
                on
                    kp.no_order = op.no_order 
            where
                op.no_order = '".$params."'
        ";
        $d_op = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_op->count() > 0 ) {
            $d_op = $d_op->toArray()[0];

            $detail = null;

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    opd.*,
                    kirim.nama as nama_kirim,
                    prs.perusahaan as nama_perusahaan,
                    brg.nama as nama_barang,
                    brg.bentuk as bentuk_barang
                from order_pakan_detail opd 
                right join
                    (
                        select brg1.* from barang brg1
                        right join
                            (select max(id) as id, kode from barang group by kode) brg2
                            on
                                brg1.id = brg2.id
                    ) brg
                    on
                        opd.barang = brg.kode
                right join
                    (
                        select
                            cast(rs.noreg as varchar(15)) as id,
                            m.nama as nama,
                            'peternak' as jenis
                        from rdim_submit rs 
                        right join
                            (select max(mitra) as id_mitra, nim from mitra_mapping mm group by nim) mm
                            on
                                rs.nim = mm.nim
                        right join
                            mitra m
                            on
                                mm.id_mitra = m.id
                        where 
                            rs.noreg is not null
                                
                        union all
                        
                        select 
                            cast(g.id as varchar(15)) as id, 
                            g.nama,
                            'gudang' as jenis
                        from gudang g
                    ) kirim
                    on
                        opd.id_tujuan_kirim = kirim.id
                right join
                    (
                        select prs1.* from perusahaan prs1
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) prs2
                            on
                                prs1.id = prs2.id
                    ) prs
                    on
                        opd.perusahaan = prs.kode
                where
                    opd.id_header = ".$d_op['id']."
            ";
            $d_opd = $m_conf->hydrateRaw( $sql );

            if ( $d_opd->count() > 0 ) {
                $detail = $d_opd->toArray();
            }

            $data = $d_op;
            $data['detail'] = $detail;
        }

        $content['data'] = $data;
        // $content['barang'] = $data_barang;
        // $content['peternak'] = $this->get_peternak();
        // $content['supplier'] = $this->get_data_supplier();
        // $content['perusahaan'] = $this->get_data_perusahaan();
        // $content['gudang'] = $this->get_data_gudang('pakan');

        $this->load->view('transaksi/odvp/order_pakan_view_form', $content);
    }

    public function get_data_gudang($jenis)
    {
        $m_gudang = new \Model\Storage\Gudang_model();
        $d_gudang = $m_gudang->where('jenis', $jenis)->orderBy('nama', 'asc')->get();

        if ( $d_gudang->count() > 0 ) {
            $d_gudang = $d_gudang->toArray();
        }

        return $d_gudang;
    }

    public function get_peternak()
    {
        $data = array();

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $sql = "
            select 
                rs.noreg,
                m.nomor,
                m.nama,
                m.alamat_rt,
                m.alamat_rw,
                m.alamat_kelurahan,
                m.alamat_jalan,
                l.nama as kecamatan,
                w.kode 
            from rdim_submit rs 
            left join
                (select nim, nomor from mitra_mapping group by nim, nomor) mm
                on
                    rs.nim = mm.nim
            left join
                (
                    select m1.id, m1.nomor, m1.nama, m1.alamat_rt, m1.alamat_rw, m1.alamat_kelurahan, m1.alamat_jalan, m1.alamat_kecamatan from mitra m1
                    left join
                        (select max(id) as id, nomor from mitra group by nomor) m2
                        on
                            m1.id = m2.id
                ) m
                on
                    mm.nomor = m.nomor
            left join
                lokasi l 
                on
                    m.alamat_kecamatan = l.id
            left join
                kandang k 
                on
                    rs.kandang = k.id
            left join
                wilayah w 
                on
                    k.unit = w.id
            where
                rs.tgl_docin >= DATEADD(month, -2, GETDATE())
            group by
                rs.noreg,
                m.nomor,
                m.nama,
                m.alamat_rt,
                m.alamat_rw,
                m.alamat_kelurahan,
                m.alamat_jalan,
                l.nama,
                w.kode
        ";

        $d_rs = $m_rs->hydrateRaw($sql);

        if ( $d_rs->count() > 0 ) {
            $d_rs = $d_rs->toArray();

            foreach ($d_rs as $k_rs => $v_rs) {
                $rt = !empty($v_rs['alamat_rt']) ? ' ,RT.'.$v_rs['alamat_rt'] : null;
                $rw = !empty($v_rs['alamat_rw']) ? '/RW.'.$v_rs['alamat_rw'] : null;
                $kelurahan = !empty($v_rs['alamat_kelurahan']) ? ' ,'.$v_rs['alamat_kelurahan'] : null;
                $kecamatan = !empty($v_rs['kecamatan']) ? ' ,'.$v_rs['nama'] : null;

                $alamat = $v_rs['alamat_jalan'] . $rt . $rw . $kelurahan . $kecamatan;

                $key = $v_rs['kode'].' - '.$v_rs['nama'].' - '.$v_rs['noreg'];
                $data[ $key ] = array(
                    'noreg' => $v_rs['noreg'],
                    'kode_unit' => $v_rs['kode'],
                    'nomor' => $v_rs['nomor'],
                    'nama' => $v_rs['nama'],
                    'alamat' => strtoupper($alamat)
                );

                ksort($data);
            }
        }

        return $data;
    }

    public function save_order_pakan()
    {
        $params = $this->input->post('params');

        try {
            $m_op = new \Model\Storage\OrderPakan_model();

            $month = substr(date('Y-m-d'), 5, 2);
            $kode = 'OPKS/'.$month.'/';

            $kode_unit = null;
            $id_kirim = null;
            $jenis_kirim = null;
            foreach ($params['detail'] as $k => $val) {
                $id_kirim = $val['id_tujuan_kirim'];
                $jenis_kirim = $val['tujuan_kirim'];
            }

            if ( stristr($jenis_kirim, 'gudang') !== FALSE ) {
                $m_gdg = new \Model\Storage\Gudang_model();
                $d_gdg = $m_gdg->where('id', $id_kirim)->with(['dUnit'])->first();

                if ( $d_gdg ) {
                    $d_gdg = $d_gdg->toArray();
                    $kode_unit = $d_gdg['d_unit']['kode'];
                }
            }

            $nomor = $m_op->getNextNomor('OPK/'.$kode_unit);

            $m_op->no_order = $nomor;
            $m_op->tgl_trans = $params['tgl_trans'];
            $m_op->rcn_kirim = $params['rcn_kirim'];
            $m_op->supplier = $params['supplier'];
            $m_op->save();

            $id = $m_op->id;

            foreach ($params['detail'] as $k => $val) {
                $m_opd = new \Model\Storage\OrderPakanDetail_model();
                $m_opd->id_header = $id;
                $m_opd->barang = $val['barang'];
                $m_opd->harga = $val['harga'];
                $m_opd->harga_jual = $val['harga'];
                // $m_opd->harga_jual = $val['harga_jual'];
                $m_opd->jumlah = $val['jumlah'];
                $m_opd->total = $val['total'];
                $m_opd->tujuan_kirim  = $val['tujuan_kirim'];
                $m_opd->id_tujuan_kirim = $val['id_tujuan_kirim'];
                $m_opd->alamat = $val['alamat'];
                $m_opd->perusahaan = $val['perusahaan'];
                $m_opd->save();
            }

            $d_order_pakan = $m_op->where('id', $id)->with(['detail'])->first();

            $deskripsi_log_order_pakan = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_order_pakan, $deskripsi_log_order_pakan);
            
            $this->result['status'] = 1;
            $this->result['message'] = 'Data Order Pakan berhasil disimpan.';

        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit_order_pakan()
    {
        $params = $this->input->post('params');

        try {
            $m_op = new \Model\Storage\OrderPakan_model();
            $m_op->where('id', $params['id'])->update(
                array(
                    'tgl_trans' => $params['tgl_trans'],
                    'rcn_kirim' => $params['rcn_kirim'],
                    'supplier' => $params['supplier']
                )
            );

            $id = $params['id'];

            $m_opd = new \Model\Storage\OrderPakanDetail_model();
            $m_opd->where('id_header', $id)->delete();

            foreach ($params['detail'] as $k => $val) {
                $m_opd = new \Model\Storage\OrderPakanDetail_model();
                $m_opd->id_header = $id;
                $m_opd->barang = $val['barang'];
                $m_opd->harga = $val['harga'];
                $m_opd->harga_jual = $val['harga'];
                $m_opd->jumlah = $val['jumlah'];
                $m_opd->total = $val['total'];
                $m_opd->tujuan_kirim  = $val['tujuan_kirim'];
                $m_opd->id_tujuan_kirim = $val['id_tujuan_kirim'];
                $m_opd->alamat = $val['alamat'];
                $m_opd->perusahaan = $val['perusahaan'];
                $m_opd->save();
            }

            $d_order_pakan = $m_op->where('id', $id)->with(['detail'])->first();

            // $m_kp = new \Model\Storage\KirimPakan_model();
            // $d_kp = $m_kp->where('no_order', $d_order_pakan->no_order)->first();

            // if ( $d_kp ) {
            //     $m_tp = new \Model\Storage\TerimaPakan_model();
            //     $d_tp = $m_tp->where('id_kirim_pakan', $d_kp->id)->first();

            //     if ( $d_tp ) {
            //         $id_terima = $d_tp->id;
            //         $tgl_terima = $d_tp->tgl_terima;

            //         $conf = new \Model\Storage\Conf();
            //         $sql = "EXEC hitung_stok_pakan_by_transaksi 'terima_pakan', '".$id_terima."', '".$tgl_terima."', 0, 2";
        
            //         $conf->hydrateRaw($sql);
            //     }
            // }

            $deskripsi_log_order_pakan = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_order_pakan, $deskripsi_log_order_pakan);
            
            $this->result['status'] = 1;
            $this->result['message'] = 'Data Order Pakan berhasil diupdate';
            $this->result['content'] = array('no_order' => $d_order_pakan->no_order);

        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function order_pakan_delete()
    {
        $params = $this->input->post('params');

        try {
            $m_op = new \Model\Storage\OrderPakan_model();
            $d_order_pakan = $m_op->where('id', $params)->with(['detail'])->first();

            $m_opd = new \Model\Storage\OrderPakanDetail_model();
            $m_opd->where('id_header', $params)->delete();

            $m_op = new \Model\Storage\OrderPakan_model();
            $m_op->where('id', $params)->delete();

            $deskripsi_log_order_pakan = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_order_pakan, $deskripsi_log_order_pakan);
            
            $this->result['status'] = 1;
            $this->result['message'] = 'Data Order Pakan berhasil dihapus';

        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function hitungStokPakanByTransaksi()
    {
        $params = $this->input->post('params');

        try {
            $no_order = $params['no_order'];

            $id = null;
            $tanggal = null;
            $delete = null;
            $status_jurnal = null;

            $m_kp = new \Model\Storage\KirimPakan_model();
            $d_kp = $m_kp->where('no_order', $no_order)->first();

            if ( $d_kp ) {
                $m_tp = new \Model\Storage\TerimaPakan_model();
                $d_tp = $m_tp->where('id_kirim_pakan', $d_kp->id)->first();

                if ( $d_tp ) {
                    $id = $d_tp->id;
                    $tanggal = $d_tp->tgl_terima;
                    $delete = 0;
                    $status_jurnal = 2;
                }
            }

            if ( !empty($id) ) {
                $conf = new \Model\Storage\Conf();
                $sql = "EXEC hitung_stok_pakan_by_transaksi 'terima_pakan', '".$id."', '".$tanggal."', ".$delete.", ".$status_jurnal."";

                $d_conf = $conf->hydrateRaw($sql);
            }

            $this->result['status'] = 1;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function listActivity()
    {
        $params = $this->input->get('params');

        $data = null;
        if ( $params['jenis'] == 'voadip' ) {
            $m_order_voadip = new \Model\Storage\OrderVoadip_model();
            $d_order_voadip = $m_order_voadip->where('id', $params['id'])->with(['logs'])->first()->toArray();

            $data = array(
                'jenis' => $params['jenis'],
                'tanggal' => $params['tanggal'],
                'no_order' => $params['no_order'],
                'supplier' => $params['supplier'],
                'logs' => $d_order_voadip['logs']
            );
        } else {
            $m_order_pakan = new \Model\Storage\OrderPakan_model();
            $d_order_pakan = $m_order_pakan->where('id', $params['id'])->with(['logs'])->first()->toArray();

            $data = array(
                'jenis' => $params['jenis'],
                'tanggal' => $params['tanggal'],
                'supplier' => $params['supplier'],
                'rcn_kirim' => $params['rcn_kirim'],
                'no_order' => $params['no_order'],
                'logs' => $d_order_pakan['logs']
            );
        }

        $content['data'] = $data;
        $html = $this->load->view('transaksi/odvp/list_activity', $content, true);

        echo $html;
    }

    public function tes()
    {
        $this->insertKonfirmasi('ODC/MGT/24/04003');
    }
}