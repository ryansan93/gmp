<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KonfirmasiPembayaranVoadip extends Public_Controller
{
    private $url;
    private $hakAkses;
    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->hakAkses = hakAkses($this->url);
    }

    public function index()
    {
        if ( $this->hakAkses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                'assets/pembayaran/konfirmasi_pembayaran_voadip/js/konfirmasi-pembayaran-voadip.js'
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/pembayaran/konfirmasi_pembayaran_voadip/css/konfirmasi-pembayaran-voadip.css'
            ));

            $data = $this->includes;

            $data['title_menu'] = 'Konfirmasi Pembayaran OVK';

            $supplier = $this->get_supplier();
            $perusahaan = $this->get_perusahaan();

            $content['add_form'] = $this->add_form($supplier, $perusahaan);
            $content['riwayat'] = $this->riwayat($supplier, $perusahaan);

            $content['akses'] = $this->hakAkses;
            $data['view'] = $this->load->view('pembayaran/konfirmasi_pembayaran_voadip/index', $content, true);

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
            $supplier = $this->get_supplier();
            $perusahaan = $this->get_perusahaan();
            $html = $this->edit_form($id, $supplier, $perusahaan);
        }else{
            $supplier = $this->get_supplier();
            $perusahaan = $this->get_perusahaan();
            $html = $this->add_form($supplier, $perusahaan);
        }

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

    public function get_supplier()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                plg1.* 
            from pelanggan plg1
            right join
                (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                on
                    plg1.id = plg2.id
            where
                plg1.mstatus = 1
        ";
        $d_supl = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_supl->count() > 0 ) {
            $d_supl = $d_supl->toArray();

            foreach ($d_supl as $key => $value) {
                $key = strtoupper($value['nama']).' - '.$value['nomor'];
                $data[ $key ] = array(
                    'nama' => strtoupper($value['nama']),
                    'nomor' => $value['nomor']
                );
            }

            ksort($data);
        }

        // $m_supplier = new \Model\Storage\Supplier_model();
        // $nomor_supplier = $m_supplier->select('nomor')->distinct('nomor')->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->get();

        // $data = null;
        // if ( $nomor_supplier->count() > 0 ) {
        //     $nomor_supplier = $nomor_supplier->toArray();

        //     foreach ($nomor_supplier as $k => $val) {
        //         $m_supplier = new \Model\Storage\Supplier_model();
        //         $d_supplier = $m_supplier->where('nomor', $val['nomor'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('version', 'desc')->first();

        //         $key = strtoupper($d_supplier->nama).' - '.$d_supplier['nomor'];
        //         $data[ $key ] = array(
        //             'nama' => strtoupper($d_supplier->nama),
        //             'nomor' => $d_supplier->nomor
        //         );
        //     }

        //     ksort($data);
        // }

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

    public function get_lists()
    {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $kode_supplier = null;
        $kode_perusahaan = null;

        $m_supplier = new \Model\Storage\Supplier_model();
        foreach ($params['supplier'] as $k => $val) {
            $d_supplier = null;
            if ( $val != 'all' ) {
                $d_supplier = $m_supplier->where('nomor', $val)->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->get();
            } else {
                $d_supplier = $m_supplier->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->get();
            }

            if ( !empty($d_supplier) ) {
                $d_supplier = $d_supplier->toArray();

                foreach ($d_supplier as $k_supl => $v_supl) {
                    $kode_supplier[] = $v_supl['nomor'];
                }
            }
        }

        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        foreach ($params['perusahaan'] as $k => $val) {
            $d_perusahaan = null;
            if ( $val != 'all' ) {
                $d_perusahaan = $m_perusahaan->where('kode', $val)->get();
            } else {
                $d_perusahaan = $m_perusahaan->get();
            }

            if ( !empty($d_perusahaan) ) {
                $d_perusahaan = $d_perusahaan->toArray();

                foreach ($d_perusahaan as $k_perusahaan => $v_perusahaan) {
                    $kode_perusahaan[] = $v_perusahaan['kode'];
                }
            }
        }

        $m_kpv = new \Model\Storage\KonfirmasiPembayaranVoadip_model();
        $d_kpv = $m_kpv->whereBetween('tgl_bayar', [$start_date, $end_date])
                       ->whereIn('supplier', $kode_supplier)
                       ->whereIn('perusahaan', $kode_perusahaan)
                       ->with(['d_supplier', 'd_perusahaan'])->orderBy('tgl_bayar', 'desc')->get();

        if ( $d_kpv->count() > 0 ) {
            $d_kpv = $d_kpv->toArray();
        }

        $content['data'] = $d_kpv;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_voadip/list_riwayat', $content, true);

        echo $html;
    }

    public function get_data_voadip()
    {
        $params = $this->input->get('params');

        $html = $this->get_data_voadip_html( $params );

        echo $html;
    }

    public function get_data_voadip_html($params, $edit = null)
    {
        $start_date = $params['start_date'].' 00:00:00.000';
        $end_date = $params['end_date'].' 23:59:59.999';

        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $d_perusahaan = $m_perusahaan->where('kode', $params['perusahaan'])->orderBy('id', 'desc')->first();

        $m_supplier = new \Model\Storage\Supplier_model();
        $d_supplier = $m_supplier->where('nomor', $params['supplier'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('id', 'desc')->first();

        $m_wilayah = new \Model\Storage\Wilayah_model();
        $kode_unit = null;
        foreach ($params['kode_unit'] as $k_ku => $v_ku) {
            $d_wilayah = null;
            if ( $v_ku != 'all' ) {
                $d_wilayah = $m_wilayah->where('jenis', 'UN')->where('kode', $v_ku)->get();
            } else {
                $d_wilayah = $m_wilayah->where('jenis', 'UN')->get();
            }

            if ( !empty($d_wilayah) ) {
                $d_wilayah = $d_wilayah->toArray();

                foreach ($d_wilayah as $k_wil => $v_wil) {
                    $kode_unit[ $v_wil['kode'] ] = $v_wil['kode'];
                }
            }
        }

        $no_order = null;
        foreach ($kode_unit as $k_ku => $v_ku) {
            $m_terima_voadip = new \Model\Storage\TerimaVoadip_model();
            $sql = "
                select * from terima_voadip tp 
                left join
                    kirim_voadip kp 
                    on tp.id_kirim_voadip = kp.id
                where
                    tp.tgl_terima between '$start_date' and '$end_date' and
                    kp.jenis_tujuan = 'gudang' and
                    kp.no_order like '%".$v_ku."%' and
                    kp.asal = '".$params['supplier']."'
            ";
            $d_terima_voadip = $m_terima_voadip->hydrateRaw($sql);
            
            if ( $d_terima_voadip->count() > 0 ) {
                $d_terima_voadip = $d_terima_voadip->toArray();
                foreach ($d_terima_voadip as $k => $val) {
                    $m_order_voadip = new \Model\Storage\OrderVoadip_model();
                    $d_order_voadip = $m_order_voadip->where('no_order', $val['no_order'])->orderBy('id', 'desc')->first();

                    $m_order_voadip_det = new \Model\Storage\OrderVoadipDetail_model();
                    $d_order_voadip_det = $m_order_voadip_det->where('id_order', $d_order_voadip->id)->where('perusahaan', $params['perusahaan'])->orderBy('id', 'desc')->first();

                    if ( $d_order_voadip_det ) {
                        $no_order[ $val['no_order'] ] = array(
                            'no_order' => $val['no_order'],
                            'unit' => $v_ku
                        );
                    }
                }
            }
        }

        $data = null;
        if ( !empty($no_order) ) {
            foreach ($no_order as $k => $val) {
                $m_kirim_voadip = new \Model\Storage\KirimVoadip_model();
                $d_kirim_voadip = $m_kirim_voadip->where('no_order', trim($val['no_order']))->first();

                if ( $d_kirim_voadip ) {
                    $m_order_voadip = new \Model\Storage\OrderVoadip_model();
                    $d_order_voadip = $m_order_voadip->where('no_order', $d_kirim_voadip->no_order)->orderBy('id', 'desc')->first();

                    $m_terima_voadip = new \Model\Storage\TerimaVoadip_model();
                    $d_terima_voadip = $m_terima_voadip->where('id_kirim_voadip', $d_kirim_voadip->id)->with(['detail'])->first();

                    if ( $d_terima_voadip ) {
                        $m_gudang = new \Model\Storage\Gudang_model();
                        $d_gudang = $m_gudang->where('id', $d_kirim_voadip->tujuan)->first();

                        $m_wilayah = new \Model\Storage\Wilayah_model();
                        $kota_kab = $m_wilayah->where('kode', $val['unit'])->first();

                        $detail = null;

                        $jumlah = 0;
                        $total = 0;
                        foreach ($d_terima_voadip->detail as $k_det => $v_det) {
                            $m_kirim_voadip_det = new \Model\Storage\KirimVoadipDetail_model();
                            $d_kirim_voadip_det = $m_kirim_voadip_det->where('id_header', $d_kirim_voadip->id)->where('item', $v_det->item)->get();

                            $harga = 0;
                            if ( $d_kirim_voadip_det->count() > 0 ) {
                                $jml = 0;
                                $nilai_beli = 0;

                                $d_kirim_voadip_det = $d_kirim_voadip_det->toArray();
                                foreach ($d_kirim_voadip_det as $k_kpv => $v_kpv) {
                                    $m_order_voadip_det = new \Model\Storage\OrderVoadipDetail_model();
                                    $d_order_voadip_det = $m_order_voadip_det->where('id_order', $d_order_voadip->id)->where('kode_barang', $v_det->item)->orderBy('id', 'desc')->first();

                                    $jml += $v_kpv['jumlah'];
                                    $harga = !empty($d_order_voadip_det) ? $d_order_voadip_det->harga : 0;
                                    $nilai_beli += $harga * $jml;
                                }
                            }

                            $jumlah += $v_det->jumlah;
                            $sub_total = $v_det->jumlah * $harga;
                            $total += $sub_total;

                            $key = "ORDER - ".$v_det->d_barang->nama." - ".$v_det->item;
                            $detail[ $key ] = array(
                                'id_tujuan' => $d_gudang->id,
                                'tujuan' => $d_gudang->nama,
                                'kode_brg' => $v_det->item,
                                'nama_brg' => $v_det->d_barang->nama,
                                'jumlah' => $v_det->jumlah,
                                'harga' => $harga,
                                'total' => $sub_total
                            );

                            $m_conf = new \Model\Storage\Conf();
                            $sql = "
                                select
                                    rv.no_order,
                                    supl.nomor as id_tujuan,
                                    supl.nama as tujuan,
                                    drv.item as kode_brg,
                                    brg.nama as nama_brg,
                                    sum(drv.jumlah) as jumlah
                                from det_retur_voadip drv
                                right join
                                    retur_voadip rv
                                    on
                                        drv.id_header = rv.id
                                left join
                                    (
                                        select p1.* from pelanggan p1
                                        right join
                                            (select max(id) as id, nomor from pelanggan p where tipe = 'supplier' and jenis <> 'ekspedisi' and mstatus = 1 group by nomor) p2
                                            on
                                                p1.id = p2.id
                                    ) supl
                                    on
                                        supl.nomor = rv.id_tujuan
                                left join
                                    (
                                        select brg1.* from barang brg1
                                        right join
                                            (select max(id) as id, kode from barang group by kode) brg2
                                            on
                                                brg1.id = brg2.id
                                    ) brg
                                    on
                                        brg.kode =  drv.item
                                where
                                    rv.no_order = '".$d_kirim_voadip->no_order."' and
                                    drv.item = '".$v_det->item."'
                                group by
                                    rv.no_order,
                                    supl.nomor,
                                    supl.nama,
                                    drv.item,
                                    brg.nama
                            ";
                            $d_retur = $m_conf->hydrateRaw( $sql );

                            if ( $d_retur->count() > 0 ) {
                                $d_retur = $d_retur->toArray()[0];

                                $jumlah -= $v_det->jumlah;
                                $sub_total = $v_det->jumlah * $harga;
                                $total -= $sub_total;

                                $key = "RETUR - ".$d_retur['nama_brg']." - ".$d_retur['kode_brg'];
                                $detail[ $key ] = array(
                                    'id_tujuan' => $d_retur['id_tujuan'],
                                    'tujuan' => $d_retur['tujuan']." (<b>RETUR</b>)",
                                    'kode_brg' => $d_retur['kode_brg'],
                                    'nama_brg' => $d_retur['nama_brg'],
                                    'jumlah' => $d_retur['jumlah'],
                                    'harga' => $harga,
                                    'total' => $sub_total
                                );
                            }

                            ksort($detail);
                        }

                        $m_kppd = new \Model\Storage\KonfirmasiPembayaranVoadipDet_model();
                        $d_kppd = $m_kppd->where('no_order', $val['no_order'])->first();

                        $tampil = true;
                        $checked = null;
                        if ( $edit == 'edit' ) {
                            if ( $d_kppd ) {
                                $checked = 'checked';
                            }
                        } else {
                            if ( $d_kppd ) {
                                $tampil = false;
                            }
                        }

                        if ( $tampil ) {
                            $key = str_replace('-', '', substr($d_kirim_voadip->tgl_kirim, 0, 10)).'-'.$d_kirim_voadip->no_sj;
                            $data[$key] = array(
                                'kode_supplier' => $params['supplier'],
                                'supplier' => $d_supplier->nama,
                                'tgl_sj' => substr($d_kirim_voadip->tgl_kirim, 0, 10),
                                'id_kota_kab' => $kota_kab->kode,
                                'kota_kab' => str_replace('Kab ', '', str_replace('Kota ', '', $kota_kab->nama)),
                                'id_perusahaan' => $d_perusahaan->kode,
                                'perusahaan' => $d_perusahaan->perusahaan,
                                'no_order' => $val['no_order'],
                                'no_sj' => $d_kirim_voadip->no_sj,
                                'jumlah' => $jumlah,
                                'total' => $total,
                                'detail' => $detail,
                                'checked' => $checked
                            );
                        }
                    }
                }
            }
        }

        if ( !empty($data) ) {
            ksort($data);
        }

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_voadip/list_order', $content, true);

        return $html;
    }

    public function riwayat($supplier, $perusahaan)
    {
        $content['supplier'] = $supplier;
        $content['perusahaan'] = $perusahaan;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_voadip/riwayat', $content, true);

        return $html;
    }

    public function add_form($supplier, $perusahaan)
    {
        $content['unit'] = $this->get_unit();
        $content['supplier'] = $supplier;
        $content['perusahaan'] = $perusahaan;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_voadip/add_form', $content, true);

        return $html;
    }

    public function detail_form($id)
    {
        $m_kpv = new \Model\Storage\KonfirmasiPembayaranVoadip_model();
        $d_kpv = $m_kpv->where('id', $id)->with(['d_supplier', 'd_perusahaan', 'detail', 'd_realisasi'])->first();

        $data = null;
        if ( $d_kpv ) {
            $d_kpv = $d_kpv->toArray();

            $data = $d_kpv;
        }

        $content['akses'] = $this->hakAkses;
        $content['data'] = $data;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_voadip/detail_form', $content, true);

        return $html;
    }

    public function edit_form($id, $supplier, $perusahaan)
    {
        $m_kpp = new \Model\Storage\KonfirmasiPembayaranVoadip_model();
        $d_kpp = $m_kpp->where('id', $id)->with(['d_supplier', 'd_perusahaan', 'detail'])->first();

        $data = null;
        $first_date = null;
        $last_date = null;

        $kode_unit = null;
        $total = 0;

        if ( $d_kpp ) {
            $d_kpp = $d_kpp->toArray();

            $data = $d_kpp;

            foreach ($data['detail'] as $k => $val) {
                $total += $val['total'];

                $tgl = substr($val['tgl_sj'], 0, 10);

                if ( empty($first_date) ) {
                    $first_date = $tgl;
                } else {
                    if ( $first_date > $tgl ) {
                        $first_date = $tgl;
                    }
                }

                if ( empty($last_date) ) {
                    $last_date = $tgl;
                } else {
                    if ( $last_date < $tgl ) {
                        $last_date = $tgl;
                    }
                }

                $kode_unit[ $val['kode_unit'] ] = $val['kode_unit'];
            }
        }

        $content['data'] = $data;
        $content['first_date'] = $first_date;
        $content['last_date'] = $last_date;
        $content['kode_unit'] = $kode_unit;
        $content['total'] = $total;
        $content['unit'] = $this->get_unit();
        $content['supplier'] = $supplier;
        $content['perusahaan'] = $perusahaan;

        $params = array(
            'start_date' => $first_date,
            'end_date' => $last_date,
            'kode_unit' => $kode_unit,
            'perusahaan' => $d_kpp['perusahaan'],
            'supplier' => $d_kpp['supplier']
        );

        $content['detail'] = $this->get_data_voadip_html($params, 'edit');
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_voadip/edit_form', $content, true);

        return $html;
    }

    public function konfirmasi_pembayaran()
    {
        $params = $this->input->get('params');

        $nomor = null;
        $rekening = null;
        if ( isset($params['id']) ) {
            $m_kpp = new \Model\Storage\KonfirmasiPembayaranVoadip_model();
            $d_kpp = $m_kpp->where('id', $params['id'])->first();

            $nomor = $d_kpp->nomor;
            $rekening = $d_kpp->rekening;
        }

        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $d_perusahaan = $m_perusahaan->where('kode', $params['perusahaan'])->orderBy('version', 'desc')->first();

        $m_supplier = new \Model\Storage\Supplier_model();
        $d_supplier = $m_supplier->where('nomor', $params['supplier'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->with(['banks'])->orderBy('version', 'desc')->first();

        $first_date = null;
        $last_date = null;

        $total = 0;
        foreach ($params['detail'] as $k => $val) {
            $total += $val['total'];

            $m_kirim_voadip = new \Model\Storage\KirimVoadip_model();
            $d_kirim_voadip = $m_kirim_voadip->where('no_order', $val['no_order'])->first();

            $m_terima_voadip = new \Model\Storage\TerimaVoadip_model();
            $d_terima_voadip = $m_terima_voadip->where('id_kirim_voadip', $d_kirim_voadip->id)->first();

            $datang = substr($d_terima_voadip->tgl_terima, 0, 10);

            if ( empty($first_date) ) {
                $first_date = $datang;
            } else {
                if ( $first_date > $datang ) {
                    $first_date = $datang;
                }
            }

            if ( empty($last_date) ) {
                $last_date = $datang;
            } else {
                if ( $last_date < $datang ) {
                    $last_date = $datang;
                }
            }
        }

        $data = array(
            'id' => isset($params['id']) ? $params['id'] : null,
            'nomor' => $nomor,
            'rekening' => $rekening,
            'total' => $total,
            'first_date' => $first_date,
            'last_date' => $last_date,
            'perusahaan' => $d_perusahaan->perusahaan,
            'no_perusahaan' => $d_perusahaan->kode,
            'supplier' => $d_supplier->nama,
            'no_supplier' => $d_supplier->nomor,
            'bank_supplier' => $d_supplier->banks
        );

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_voadip/konfirmasi_pembayaran', $content, true);

        echo $html;
    }

    public function save()
    {
        $params = $this->input->post('params');
        try {
            $id = null;
            foreach ($params['detail'] as $k_det => $v_det) {
                $m_kpp = new \Model\Storage\KonfirmasiPembayaranVoadip_model();
                $nomor = $m_kpp->getNextNomor();
    
                $m_kpp->nomor = $nomor;
                $m_kpp->tgl_bayar = $params['tgl_bayar'];
                $m_kpp->periode = trim($params['periode_docin']);
                $m_kpp->perusahaan = $params['perusahaan'];
                $m_kpp->supplier = $params['supplier'];
                $m_kpp->rekening = $params['rekening'];
                $m_kpp->total = $v_det['total'];
                // $m_kpp->total = $params['total'];
                $m_kpp->save();
    
                $id = $m_kpp->id;

                $m_kppd = new \Model\Storage\KonfirmasiPembayaranVoadipDet_model();
                $m_kppd->id_header = $id;
                $m_kppd->tgl_sj = $v_det['tgl_sj'];
                $m_kppd->kode_unit = $v_det['id_kab_kota'];
                $m_kppd->no_order = $v_det['no_order'];
                $m_kppd->no_sj = $v_det['no_sj'];
                $m_kppd->jumlah = $v_det['jumlah'];
                $m_kppd->total = $v_det['total'];
                $m_kppd->save();

                $id_det = $m_kppd->id;
                foreach ($v_det['detail'] as $k_det2 => $v_det2) {
                    $m_kppd2 = new \Model\Storage\KonfirmasiPembayaranVoadipDet2_model();
                    $m_kppd2->id_header = $id_det;
                    $m_kppd2->id_gudang = $v_det2['id_gudang'];
                    $m_kppd2->kode_brg = $v_det2['kode_brg'];
                    $m_kppd2->jumlah = $v_det2['jumlah'];
                    $m_kppd2->harga = $v_det2['harga'];
                    $m_kppd2->total = $v_det2['total'];
                    $m_kppd2->save();
                }

                $d_kpv = $m_kpp->where('id', $id)->first();
    
                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $d_kpv, $deskripsi_log);
            }

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $id);
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit()
    {
        $params = $this->input->post('params');
        try {
            $id = $params['id'];

            $m_kpv = new \Model\Storage\KonfirmasiPembayaranVoadip_model();
            $m_kpv->where('id', $params['id'])->update(
                array(
                    'tgl_bayar' => $params['tgl_bayar'],
                    'periode' => trim($params['periode_docin']),
                    'perusahaan' => $params['perusahaan'],
                    'supplier' => $params['supplier'],
                    'rekening' => $params['rekening'],
                    'total' => $params['total']
                )
            );

            $m_kpvd = new \Model\Storage\KonfirmasiPembayaranVoadipDet_model();
            $d_kpvd = $m_kpvd->select('id')->where('id_header', $id)->get()->toArray();

            $m_kpvd2 = new \Model\Storage\KonfirmasiPembayaranVoadipDet2_model();
            $m_kpvd2->whereIn('id_header', $d_kpvd)->delete();
            $m_kpvd->where('id_header', $id)->delete();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_kppd = new \Model\Storage\KonfirmasiPembayaranVoadipDet_model();
                $m_kppd->id_header = $id;
                $m_kppd->tgl_sj = $v_det['tgl_sj'];
                $m_kppd->kode_unit = $v_det['id_kab_kota'];
                $m_kppd->no_order = $v_det['no_order'];
                $m_kppd->no_sj = $v_det['no_sj'];
                $m_kppd->jumlah = $v_det['jumlah'];
                $m_kppd->total = $v_det['total'];
                $m_kppd->save();

                $id_det = $m_kppd->id;
                foreach ($v_det['detail'] as $k_det2 => $v_det2) {
                    $m_kppd2 = new \Model\Storage\KonfirmasiPembayaranVoadipDet2_model();
                    $m_kppd2->id_header = $id_det;
                    $m_kppd2->id_gudang = $v_det2['id_gudang'];
                    $m_kppd2->kode_brg = $v_det2['kode_brg'];
                    $m_kppd2->jumlah = $v_det2['jumlah'];
                    $m_kppd2->harga = $v_det2['harga'];
                    $m_kppd2->total = $v_det2['total'];
                    $m_kppd2->save();
                }
            }

            $d_kpv = $m_kpv->where('id', $id)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_kpv, $deskripsi_log);

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

            $m_kpv = new \Model\Storage\KonfirmasiPembayaranVoadip_model();
            $d_kpv = $m_kpv->where('id', $id)->first();

            $m_kpvd = new \Model\Storage\KonfirmasiPembayaranVoadipDet_model();
            $d_kpvd = $m_kpvd->select('id')->where('id_header', $id)->get()->toArray();

            $m_kpvd2 = new \Model\Storage\KonfirmasiPembayaranVoadipDet2_model();
            $m_kpvd2->whereIn('id_header', $d_kpvd)->delete();
            $m_kpvd->where('id_header', $id)->delete();
            $m_kpv->where('id', $id)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_kpv, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes()
    {
        $m_kpv = new \Model\Storage\KonfirmasiPembayaranDoc_model();
        $nomor = $m_kpv->getNextNomor();

        cetak_r( $nomor );
    }
}