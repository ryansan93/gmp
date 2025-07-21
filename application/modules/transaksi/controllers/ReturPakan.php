<?php defined('BASEPATH') or exit('No direct script access allowed');

class ReturPakan extends Public_Controller
{
    private $pathView = 'transaksi/retur_pakan/';
    private $url;
    private $hakAkses;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->hakAkses = hakAkses($this->url);
    }

    public function index()
    {
        if ( $this->hakAkses['a_view'] == 1 ) {
            $this->add_external_js(array(
              'assets/toastr/js/toastr.js',
              "assets/select2/js/select2.min.js",
              'assets/transaksi/retur_pakan/js/retur-pakan.js'
            ));
            $this->add_external_css(array(
              'assets/toastr/css/toastr.css',
              "assets/select2/css/select2.min.css",
              'assets/transaksi/retur_pakan/css/retur-pakan.css'
            ));
            $data = $this->includes;

            $content['akses'] = $this->hakAkses;
            $content_rwt['datas'] = null;
            $content['title_panel'] = 'Retur Pakan';

            // Load Indexx
            $a_content['data'] = null;
            $a_content['ekspedisi'] = $this->get_ekspedisi();
            $a_content['gudang'] = $this->get_gudang();
            $a_content['supplier'] = $this->get_supplier();
            $a_content['unit'] = $this->get_unit();
            $content['add_form'] = $this->load->view($this->pathView . 'add_form', $a_content, true);

            $data['title_menu'] = 'Retur Pakan';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_unit()
    {
        $m_wil = new \Model\Storage\Wilayah_model();
        $d_wil = $m_wil->select('nama', 'kode')->where('jenis', 'UN')->get();

        $data = null;
        if ( $d_wil->count() > 0 ) {
            $d_wil = $d_wil->toArray();
            foreach ($d_wil as $k_wil => $v_wil) {
                $nama = str_replace('Kab ', '', str_replace('Kota ', '', $v_wil['nama']));
                $kode = $v_wil['kode'];

                $key = $nama.' - '.$kode;
                $data[$key] = array(
                    'nama' => $nama,
                    'kode' => $kode
                );
            }
        }

        if ( !empty($data) ) {
            ksort($data);
        }

        return $data;
    }

    public function get_ekspedisi()
    {
        $data = null;

        $m_ekspedisi = new \Model\Storage\Ekspedisi_model();
        $sql = "
            select 
                eks.id,
                eks.nomor,
                eks.nama
            from ekspedisi eks 
            right join 
                (select max(id) as id, nomor from ekspedisi group by nomor) as e 
                on
                    eks.id = e.id
            where
                eks.mstatus = 1 
            group by
                eks.id,
                eks.nomor,
                eks.nama
            order by eks.nama asc
        ";
        $d_ekspedisi = $m_ekspedisi->hydrateRaw( $sql );
        if ( $d_ekspedisi->count() > 0 ) {
            $data = $d_ekspedisi->toArray();
        }

        return $data;
    }

    public function getAsalTujuan( $id ) {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select supplier.* from (
                select cast(mm.nim as varchar(15)) as id, m.nama as nama from mitra m
                right join
                    (
                        select max(id) as id, nomor from mitra 
                        group by
                            nomor
                    ) as group_mitra
                    on
                        m.id = group_mitra.id
                right join
                    mitra_mapping mm 
                    on
                        m.nomor = mm.nomor
                group by
                    m.nama, mm.nim
                    
                UNION ALL
                    
                select cast(g.id as varchar(15)) as id, g.nama as nama from gudang g 
                    
                UNION ALL
                
                select cast(p.nomor as varchar(15)) as id, p.nama as nama from pelanggan p
                right join
                    (
                        select max(id) as id, nomor from pelanggan
                        where
                            tipe = 'supplier' and
                            jenis <> 'ekspedisi'
                        group by
                            nomor
                    ) as group_pelanggan
                    on
                        p.id = group_pelanggan.id
            ) as supplier
            where
                supplier.id = '".$id."'
        ";
        $d_conf = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray()[0];
        }

        return $data;
    }

    public function get_lists()
    {
        $params = $this->input->get('params');

        $kode_unit = $params['kode_unit'];

        $m_rp = new \Model\Storage\ReturPakan_model();
        if ( $kode_unit != 'all' ) {
            $d_rp = $m_rp->whereBetween('tgl_retur', [$params['start_date'], $params['end_date']])->where('no_order', 'like', '%'.$kode_unit.'%')->orderBy('tgl_retur', 'desc')->with(['det_retur_pakan'])->get();
        } else {
            $d_rp = $m_rp->whereBetween('tgl_retur', [$params['start_date'], $params['end_date']])->orderBy('tgl_retur', 'desc')->with(['det_retur_pakan'])->get();
        }

        $data = null;
        if ( $d_rp->count() > 0 ) {
            $d_rp = $d_rp->toArray();
            foreach ($d_rp as $k_rp => $v_rp) {
                $m_lt = new \Model\Storage\LogTables_model();
                $d_lt = $m_lt->select('deskripsi', 'waktu')->where('tbl_name', 'retur_pakan')->where('tbl_id', $v_rp['id'])->orderBy('id', 'asc')->get();

                $jenis_retur = ($v_rp['jenis_retur'] == 'opkp') ? 'Dari Peternak (OPKP)' : 'Dari Peternak (OPKG)';
                if ( $v_rp['asal'] == 'peternak' ) {
                    $id_asal = substr($v_rp['id_asal'], 0, 7);
                    $asal = $this->getAsalTujuan( $id_asal )['nama'].' ('.$v_rp['id_asal'].')';
                } else {
                    $id_asal = $v_rp['id_asal'];
                    $asal = $this->getAsalTujuan( $id_asal )['nama'];
                }
    
                if ( $v_rp['tujuan'] == 'peternak' ) {
                    $id_tujuan = substr($v_rp['id_tujuan'], 0, 7);
                    $tujuan = $this->getAsalTujuan( $id_tujuan )['nama'].' ('.$v_rp['id_tujuan'].')';
                } else {
                    $id_tujuan = $v_rp['id_tujuan'];
                    $tujuan = $this->getAsalTujuan( $id_tujuan )['nama'];
                }

                $key = str_replace('-', '', $v_rp['tgl_retur']).'|'.$v_rp['no_order'];
                $data[ $key ] = array(
                    'id' => $v_rp['id'],
                    'no_retur' => $v_rp['no_retur'],
                    'jenis_retur' => $jenis_retur,
                    'tgl_retur' => $v_rp['tgl_retur'],
                    'no_order' => $v_rp['no_order'],
                    'asal' => $asal,
                    'tujuan' => $tujuan,
                    'detail' => $v_rp['det_retur_pakan'],
                    'logs' => ($d_lt->count() > 0) ? $d_lt->toArray() : null,
                );
            }
        }

        if ( !empty($data) ) {
            krsort($data);
        }

        $content['akses'] = $this->hakAkses;
        $content['data'] = $data;
        $html = $this->load->view('transaksi/retur_pakan/list', $content, TRUE);

        echo $html;
    }

    public function load_form()
    {
        $id = $this->input->get('id');
        $resubmit = $this->input->get('resubmit');
        $content = array();
        $html = "url not found";
        
        if ( !empty($id) && is_numeric($id) && $resubmit != 'edit' ) {
            // NOTE: view data BASTTB (ajax)
            $html = $this->view($id);
            // $html = $this->view($id);
        } else if ( !empty($id) && is_numeric($id) && $resubmit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $html = $this->update($id);
        }else{
            $content['ekspedisi'] = $this->get_ekspedisi();
            $content['gudang'] = $this->get_gudang();
            $content['supplier'] = $this->get_supplier();
            $content['unit'] = $this->get_unit();
            $html = $this->load->view('transaksi/retur_pakan/add_form', $content, TRUE);
        }

        echo $html;
    }

    public function get_gudang()
    {
        $m_gudang = new \Model\Storage\Gudang_model();
        $d_gudang = $m_gudang->where('jenis', 'PAKAN')->orderBy('nama', 'asc')->get();

        if ( $d_gudang->count() > 0 ) {
            $d_gudang = $d_gudang->toArray();
        }

        return $d_gudang;
    }

    public function get_supplier()
    {
        // $m_supplier = new \Model\Storage\Supplier_model();
        // $d_nomor = $m_supplier->select('nomor')->distinct('nomor')->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->get()->toArray();

        // $datas = array();
        // if ( !empty($d_nomor) ) {
        //     foreach ($d_nomor as $nomor) {
        //         $supplier = $m_supplier->where('tipe', 'supplier')
        //                                   ->where('nomor', $nomor['nomor'])
        //                                   ->orderBy('version', 'desc')
        //                                   ->orderBy('id', 'desc')
        //                                   ->first()->toArray();

        //         array_push($datas, $supplier);
        //     }
        // }
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select p1.* from pelanggan p1
            right join
                (
                    select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor
                ) as p2
                on
                    p1.id = p2.id
            order by
                p1.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw($sql);
        
        $datas = array();
        if ( $d_conf->count() > 0 ) {
            $datas = $d_conf->toArray();
        }

        return $datas;
    }

    public function get_op()
    {
        $params = $this->input->post('params');

        $jenis = $params['jenis'];
        $unit = $params['unit'];
        $tgl_kirim = $params['tgl_kirim'];

        $today = date('Y-m-d');
        $prev_date = prev_date($today, 40);

        $_data = array();
        $data = null;

        $m_kp = new \Model\Storage\KirimPakan_model();
        $jenis_tujuan = null;
        if ( $jenis == 'opkp' ) {
            $jenis_tujuan = 'peternak';
        } else {
            $jenis_tujuan = 'gudang';
        }

        $d_kp = $m_kp->where('jenis_tujuan', $jenis_tujuan)->whereBetween('tgl_kirim', [$tgl_kirim, $tgl_kirim])->where('no_order', 'like', '%'.strtoupper($unit).'%')->with(['retur'])->get();

        if ( $d_kp->count() > 0 ) {
            $d_kp = $d_kp->toArray();
            // $idx = 0;
            foreach ($d_kp as $k_kp => $v_kp) {
                $m_tp = new \Model\Storage\TerimaPakan_model();
                $d_tp = $m_tp->where('id_kirim_pakan', $v_kp['id'])->first();

                if ( !empty($d_tp) ) {
                    if ( empty($v_kp['retur']) ) {
                        $asal = null;
                        $id_asal = null;
                        if ( $v_kp['jenis_tujuan'] == 'peternak' ) {
                            $m_rs = new \Model\Storage\RdimSubmit_model();
                            $d_rs = $m_rs->where('noreg', $v_kp['tujuan'])->with(['mitra'])->first();

                            $asal = $d_rs->mitra->dMitra->nama.' ('.$d_rs->noreg.')';
                            $id_asal = $d_rs->noreg;
                        } else {
                            $m_gdg = new \Model\Storage\Gudang_model();
                            $d_gdg = $m_gdg->where('id', $v_kp['tujuan'])->first();

                            $asal = $d_gdg->nama;
                            $id_asal = $d_gdg->id;
                        }

                        $idx = $v_kp['no_order'];
                        $_data[ $idx ] = array(
                            'asal' => $asal,
                            'id_asal' => $id_asal,
                            'no_order' => $v_kp['no_order'],
                            'tgl_kirim' => $v_kp['tgl_kirim']
                        );
                    }
                }
            }

            if ( !empty($_data) ) {
                ksort($_data);

                $idx = 0;
                foreach ($_data as $k_data => $v_data) {
                    $data[$idx] = $v_data;

                    $idx++;
                }
            }
        }

        $this->result['status'] = 1;
        $this->result['content'] = $data;

        display_json($this->result);
    }

    public function get_detail_order_pakan()
    {
        $params = $this->input->get('params');

        $data = array();
        $asal = null;

        $m_kp = new \Model\Storage\KirimPakan_model();
        $d_kp = $m_kp->where('no_order', $params)->with(['detail'])->first();
        if ( !empty($d_kp) ) {
            $data = $d_kp->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list_data', $content, TRUE);

        echo $html;
    }

    public function get_data()
    {
        $params = $this->input->get('params');

        $data = array();

        $tgl_op = $params['tgl_op'];
        $no_order = $params['no_order'];
        $peternak = $params['peternak'];

        if ( !empty($tgl_op) && !empty($no_order) && !empty($peternak) ) {
            $m_op = new \Model\Storage\OrderPakan_model();
            $d_op = $m_op->where('no_order', $no_order)
                         ->where('tanggal', $tgl_op)
                         ->orderBy('version', 'desc')->first();

            if ( !empty($d_op) ) {
                $m_dop = new \Model\Storage\OrderPakanDetail_model();
                $d_dop = $m_dop->where('id_order', $d_op['id'])
                               ->orderBy('kode_barang', 'asc')
                               ->with(['d_barang', 'data_perusahaan', 'd_mitra'])
                               ->get()->toArray();

                if ( !empty($d_dop) ) {
                    $index_old = null;
                    $jml_brg = 0;
                    foreach ($d_dop as $k_dop => $v_dop) {
                        $index = $v_dop['kode_barang'];
                        if ( $peternak == $v_dop['kirim'] ) {
                            if ( $index != $index_old ) {
                                $jml_brg = $v_dop['jumlah'];
                            } else {
                                $jml_brg += $v_dop['jumlah'];
                            }

                            $data[ $v_dop['kode_barang'] ] = array(
                                'kode_perusahaan' => $v_dop['perusahaan'],
                                'perusahaan' => $v_dop['data_perusahaan']['perusahaan'],
                                'kode_barang' => $v_dop['kode_barang'],
                                'kategori' => $v_dop['d_barang']['kategori'],
                                'nama_barang' => $v_dop['d_barang']['nama'],
                                'kemasan' => $v_dop['kemasan'],
                                'jumlah' => $jml_brg
                            );

                            $index_old = $index;
                        }
                    }
                }
            }
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list_data', $content, TRUE);

        echo $html;
    }

    public function view($id)
    {
        $data = null;

        $m_rp = new \Model\Storage\ReturPakan_model();
        $d_rp = $m_rp->where('id', $id)->with(['det_retur_pakan', 'logs'])->first();

        if ( $d_rp ) {
            $d_rp = $d_rp->toArray();

            $m_kp = new \Model\Storage\KirimPakan_model();
            $d_kp = $m_kp->where('no_order', $d_rp['no_order'])->first();

            if ( $d_rp['asal'] == 'peternak' ) {
                $id_asal = substr($d_rp['id_asal'], 0, 7);
                $asal = $this->getAsalTujuan( $id_asal )['nama'].' ('.$d_rp['id_asal'].')';
            } else {
                $id_asal = $d_rp['id_asal'];
                $asal = $this->getAsalTujuan( $id_asal )['nama'];
            }

            if ( $d_rp['tujuan'] == 'peternak' ) {
                $id_tujuan = substr($d_rp['id_tujuan'], 0, 7);
                $tujuan = $this->getAsalTujuan( $id_tujuan )['nama'].' ('.$d_rp['id_tujuan'].')';
            } else {
                $id_tujuan = $d_rp['id_tujuan'];
                $tujuan = $this->getAsalTujuan( $id_tujuan )['nama'];
            }

            $detail = null;
            foreach ($d_rp['det_retur_pakan'] as $k_det => $v_det) {
                $m_dkp = new \Model\Storage\KirimPakanDetail_model();
                $d_dkp = $m_dkp->where('id_header', $d_kp->id)->where('item', $v_det['item'])->first()->toArray();

                $detail[ $v_det['id'] ] = array(
                    'item' => $v_det['item'],
                    'nama' => $v_det['d_barang']['nama'],
                    'jumlah_op' => $d_dkp['jumlah'],
                    'jumlah_rp' => $v_det['jumlah'],
                    'kondisi' => $v_det['kondisi']
                );
            }

            $data = array(
                'id' => $d_rp['id'],
                'jenis_retur' => $d_rp['jenis_retur'],
                'tgl_retur' => $d_rp['tgl_retur'],
                'tgl_kirim' => $d_kp->tgl_kirim,
                'no_order' => $d_rp['no_order'],
                'asal' => $asal,
                'tujuan' => $tujuan,
                'ongkos_angkut' => $d_rp['ongkos_angkut'],
                'ekspedisi' => $d_rp['ekspedisi'],
                'no_polisi' => $d_rp['no_polisi'],
                'sopir' => $d_rp['sopir'],
                'keterangan' => $d_rp['keterangan'],
                'detail' => $detail
            );
        }

        $content['akses'] = $this->hakAkses;
        $content['data'] = $data;
        $html = $this->load->view('transaksi/retur_pakan/view_form', $content, TRUE);

        return $html;
    }

    public function update($id)
    {
        $data = null;

        $m_rp = new \Model\Storage\ReturPakan_model();
        $d_rp = $m_rp->where('id', $id)->with(['det_retur_pakan', 'logs'])->first();

        if ( $d_rp ) {
            $d_rp = $d_rp->toArray();

            // cetak_r( $d_rp, 1 );

            $m_kp = new \Model\Storage\KirimPakan_model();
            $d_kp = $m_kp->where('no_order', $d_rp['no_order'])->first();

            $asal = null;
            $id_asal = null;
            if ( $d_rp['asal'] == 'peternak' ) {
                $m_rs = new \Model\Storage\RdimSubmit_model();
                $d_rs = $m_rs->where('noreg', $d_rp['id_asal'])->with(['mitra'])->first()->toArray();

                $asal = $d_rs['mitra']['d_mitra']['nama'].' ('.$d_rp['id_asal'].')';
            } else {
                $m_gdg = new \Model\Storage\Gudang_model();
                $d_gdg = $m_gdg->where('id', $d_rp['id_asal'])->first()->toArray();

                $asal = $d_gdg['nama'];
            }

            $detail = null;
            foreach ($d_rp['det_retur_pakan'] as $k_det => $v_det) {
                $m_dkp = new \Model\Storage\KirimPakanDetail_model();
                $d_dkp = $m_dkp->where('id_header', $d_kp->id)->where('item', $v_det['item'])->first()->toArray();

                $detail[ $v_det['id'] ] = array(
                    'item' => $v_det['item'],
                    'nama' => $v_det['d_barang']['nama'],
                    'jumlah_op' => $d_dkp['jumlah'],
                    'jumlah_rp' => $v_det['jumlah'],
                    'kondisi' => $v_det['kondisi']
                );
            }

            $data = array(
                'id' => $d_rp['id'],
                'jenis_retur' => $d_rp['jenis_retur'],
                'tgl_retur' => $d_rp['tgl_retur'],
                'tgl_kirim' => $d_kp->tgl_kirim,
                'no_order' => $d_rp['no_order'],
                'asal' => $asal,
                'id_asal' => $d_rp['id_asal'],
                'tujuan' => $d_rp['tujuan'],
                'id_tujuan' => $d_rp['id_tujuan'],
                'ongkos_angkut' => $d_rp['ongkos_angkut'],
                'ekspedisi' => $d_rp['ekspedisi'],
                'ekspedisi_id' => $d_rp['ekspedisi_id'],
                'no_polisi' => $d_rp['no_polisi'],
                'sopir' => $d_rp['sopir'],
                'keterangan' => $d_rp['keterangan'],
                'detail' => $detail
            );
        }

        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['data'] = $data;
        $content['ekspedisi'] = $this->get_ekspedisi();
        $content['gudang'] = $this->get_gudang();
        $content['supplier'] = $this->get_supplier();
        $content['unit'] = $this->get_unit();
        $html = $this->load->view('transaksi/retur_pakan/edit_form', $content, TRUE);

        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_rp = new \Model\Storage\ReturPakan_model();

            $no_retur = $m_rp->getNextId();

            $m_rp->no_retur = $no_retur;
            $m_rp->tgl_retur = $params['tgl_retur'];
            $m_rp->no_order = $params['no_order'];
            $m_rp->jenis_retur = $params['jenis_retur'];
            $m_rp->asal = $params['asal'];
            $m_rp->id_asal = $params['id_asal'];
            $m_rp->tujuan = $params['tujuan'];
            $m_rp->id_tujuan = $params['id_tujuan'];
            $m_rp->ongkos_angkut = $params['ongkos_angkut'];
            $m_rp->ekspedisi = $params['ekspedisi'];
            $m_rp->ekspedisi_id = $params['ekspedisi_id'];
            $m_rp->no_polisi = $params['nopol'];
            $m_rp->sopir = $params['sopir'];
            $m_rp->keterangan = $params['keterangan'];
            $m_rp->save();

            $id_rp = $m_rp->id;

            if ( !empty($params['data_detail']) ) {
                foreach ($params['data_detail'] as $k_det => $v_det) {
                    $m_drp = new \Model\Storage\DetReturPakan_model();
                    $m_drp->id_header = $id_rp;
                    $m_drp->item = $v_det['kode_brg'];
                    $m_drp->jumlah = $v_det['jml_retur'];
                    $m_drp->kondisi = $v_det['kondisi'];
                    $m_drp->save();
                }
            }

            $d_rp = $m_rp->where('id', $id_rp)->with(['det_retur_pakan'])->first();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_rp, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array(
                'id' => $id_rp,
                'tanggal' => $params['tgl_retur'],
                'delete' => 0,
                'message' => 'Data berhasil di update'
            );

            // $this->result['status'] = 1;
            // $this->result['content'] = array('id' => $id_rp);
        } catch (Exception $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit()
    {
        $params = $this->input->post('params');

        try {
            $id_rp = $params['id'];

            $m_rp = new \Model\Storage\ReturPakan_model();
            $d_rp_old = $m_rp->where('id', $id_rp)->first();

            $m_rp->where('id', $id_rp)->update(
                array(
                    'tgl_retur' => $params['tgl_retur'],
                    'no_order' => $params['no_order'],
                    'jenis_retur' => $params['jenis_retur'],
                    'asal' => $params['asal'],
                    'id_asal' => $params['id_asal'],
                    'tujuan' => $params['tujuan'],
                    'id_tujuan' => $params['id_tujuan'],
                    'ongkos_angkut' => $params['ongkos_angkut'],
                    'ekspedisi' => $params['ekspedisi'],
                    'ekspedisi_id' => $params['ekspedisi_id'],
                    'no_polisi' => $params['nopol'],
                    'sopir' => $params['sopir'],
                    'keterangan' => $params['keterangan'],
                )
            );

            $m_drp = new \Model\Storage\DetReturPakan_model;
            $m_drp->where('id_header', $id_rp)->delete();

            if ( !empty($params['data_detail']) ) {
                foreach ($params['data_detail'] as $k_det => $v_det) {
                    $m_drp = new \Model\Storage\DetReturPakan_model();
                    $m_drp->id_header = $id_rp;
                    $m_drp->item = $v_det['kode_brg'];
                    $m_drp->jumlah = $v_det['jml_retur'];
                    $m_drp->kondisi = $v_det['kondisi'];
                    $m_drp->save();
                }
            }

            $d_rp = $m_rp->where('id', $id_rp)->with(['det_retur_pakan'])->first();

            $tgl_trans = $d_rp->tgl_retur;
            if ( $d_rp_old->tgl_retur < $tgl_trans ) {
                $tgl_trans = $d_rp_old->tgl_retur;
            }

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_rp, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array(
                'id' => $id_rp,
                'tanggal' => $tgl_trans,
                'delete' => 0,
                'message' => 'Data berhasil di update'
            );
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete()
    {
        $id = $this->input->post('id');

        try {
            $m_rp = new \Model\Storage\ReturPakan_model();
            $id_rp = $id;

            $d_rp = $m_rp->where('id', $id_rp)->with(['det_retur_pakan'])->first();

            // $m_drp = new \Model\Storage\DetReturPakan_model();
            // $m_drp->where('id_header', $id_rp)->delete();

            // $m_rp = new \Model\Storage\ReturPakan_model();
            // $m_rp->where('id', $id_rp)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_rp, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array(
                'id' => $id_rp,
                'tanggal' => $d_rp->tgl_retur,
                'delete' => 1,
                'message' => 'Data berhasil di hapus'
            );
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function hitungStokByTransaksi()
    {
        $params = $this->input->post('params');

        $id = $params['id'];
        $tanggal = $params['tanggal'];
        $delete = $params['delete'];
        $message = $params['message'];

        try {
            $noreg1 = null;
            $noreg2 = null;

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
					rp.id_asal as noreg1,
					null as noreg2
				from retur_pakan rp
				where
					rp.id = ".$id."
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray()[0];

                $noreg1 = $d_conf['noreg1'];
                $noreg2 = $d_conf['noreg2'];
            }

            $conf = new \Model\Storage\Conf();
            $sql = "EXEC hitung_stok_pakan_by_transaksi 'retur_pakan', '".$id."', '".$tanggal."', ".$delete.", 0";
            $d_conf = $conf->hydrateRaw($sql);

            $conf = new \Model\Storage\Conf();
            $sql = "EXEC hitung_stok_siklus 'pakan', 'retur_pakan', '".$id."', '".$tanggal."', ".$delete.", '".$noreg1."', '".$noreg2."'";
            $d_conf = $conf->hydrateRaw($sql);

            $this->result['status'] = 1;
            $this->result['message'] = $message;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function hitungStokAwal()
    {
        $params = $this->input->post('params');

        try {
            $id_retur = $params['id_retur'];

            $date = date('Y-m-d');
            
            $m_stok = new \Model\Storage\Stok_model();
            $now = $m_stok->getDate();
            $d_stok = $m_stok->where('periode', $date)->first();

            $stok_id = null;
            if ( $d_stok ) {
                $stok_id = $d_stok->id;

                $this->hitungStok($id_retur, $stok_id);
            } else {
                $m_stok->periode = $date;
                $m_stok->user_proses = $this->userid;
                $m_stok->tgl_proses = $now['waktu'];
                $m_stok->save();

                $stok_id = $m_stok->id;
                
                $conf = new \Model\Storage\Conf();
                $sql = "EXEC get_data_stok_pakan_by_tanggal @date = '$date'";

                $d_conf = $conf->hydrateRaw($sql);

                if ( $d_conf->count() > 0 ) {
                    $d_conf = $d_conf->toArray();
                    $jml_data = count($d_conf);
                    $idx = 0;
                    foreach ($d_conf as $k_conf => $v_conf) {
                        $m_ds = new \Model\Storage\DetStok_model
                        ();
                        $m_ds->id_header = $stok_id;
                        $m_ds->tgl_trans = $v_conf['tgl_trans'];
                        $m_ds->kode_gudang = $v_conf['kode_gudang'];
                        $m_ds->kode_barang = $v_conf['kode_barang'];
                        $m_ds->jumlah = $v_conf['jumlah'];
                        $m_ds->hrg_jual = $v_conf['hrg_jual'];
                        $m_ds->hrg_beli = $v_conf['hrg_beli'];
                        $m_ds->kode_trans = $v_conf['kode_trans'];
                        $m_ds->jenis_barang = $v_conf['jenis_barang'];
                        $m_ds->jenis_trans = $v_conf['jenis_trans'];
                        $m_ds->jml_stok = $v_conf['jml_stok'];
                        $m_ds->save();

                        $idx++;

                        if ( $jml_data == $idx ) {
                            $this->hitungStok($id_retur, $stok_id);
                        }
                    }
                }
            }


            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function hitungStok($id_retur, $stok_id)
    {
        $m_retur_pakan = new \Model\Storage\ReturPakan_model();
        $d_retur_pakan = $m_retur_pakan->where('id', $id_retur)->with(['det_retur_pakan'])->first()->toArray();

        foreach ($d_retur_pakan['det_retur_pakan'] as $k_detail => $v_detail) {
            if ( $v_detail['jumlah'] > 0 ) {
                if ( stristr($d_retur_pakan['tujuan'], 'gudang') !== FALSE ) {
                    $m_kirim_pakan = new \Model\Storage\KirimPakan_model();
                    $d_kirim_pakan = $m_kirim_pakan->where('no_order', $d_retur_pakan['no_order'])->first();

                    $harga_jual = 0;
                    $harga_beli = 0;

                    if ( !empty($d_kirim_pakan) && $d_kirim_pakan->tgl_kirim <= '2022-09-06' ) {
                        $m_dkirim_pakan = new \Model\Storage\KirimPakanDetail_model();
                        $d_dkirim_pakan = $m_dkirim_pakan->where('id_header', $d_kirim_pakan->id)->where('item', $v_detail['item'])->get();

                        $jumlah = 0;
                        $nilai_beli = 0;
                        $nilai_jual = 0;
                        if ( $d_dkirim_pakan->count() > 0 ) {
                            $d_dkirim_pakan = $d_dkirim_pakan->toArray();
                            foreach ($d_dkirim_pakan as $k_ddkp => $v_ddkp) {
                                $jumlah += $v_ddkp['jumlah'];
                                $nilai_beli += $v_ddkp['nilai_beli'];
                                $nilai_jual += $v_ddkp['nilai_jual'];
                            }
                        }

                        $harga_jual = ($nilai_jual > 0 && $jumlah > 0) ? $nilai_jual / $jumlah : 0;
                        $harga_beli = ($nilai_beli > 0 && $jumlah > 0) ? $nilai_beli / $jumlah : 0;

                        // MASUk STOK GUDANG
                        $m_dstok = new \Model\Storage\DetStok_model();
                        $m_dstok->id_header = $stok_id;
                        $m_dstok->tgl_trans = $d_retur_pakan['tgl_retur'];
                        $m_dstok->kode_gudang = $d_retur_pakan['id_tujuan'];
                        $m_dstok->kode_barang = $v_detail['item'];
                        $m_dstok->jumlah = $v_detail['jumlah'];
                        $m_dstok->hrg_jual = $harga_jual;
                        $m_dstok->hrg_beli = $harga_beli;
                        $m_dstok->kode_trans = $d_retur_pakan['no_order'];
                        $m_dstok->jenis_barang = 'pakan';
                        $m_dstok->jenis_trans = 'RETUR';
                        $m_dstok->jml_stok = $v_detail['jumlah'];
                        $m_dstok->save();
                    } else {
                        $m_dstokt = new \Model\Storage\DetStokTrans_model();
                        $sql = "
                            select ds.hrg_beli, ds.hrg_jual, dst.* from det_stok_trans dst
                            left join
                                det_stok ds
                                on
                                    dst.id_header = ds.id
                            where
                                dst.kode_trans = '".$d_retur_pakan['no_order']."' and 
                                dst.kode_barang = '".$v_detail['item']."'
                            order by
                                dst.id desc
                        ";

                        $d_dstokt = $m_dstokt->hydrateRaw($sql);

                        if ( $d_dstokt->count() > 0 ) {
                            $jml_keluar = $v_detail['jumlah'];

                            $data_stok = null;

                            $idx = 0;
                            $d_dstokt = $d_dstokt->toArray();
                            while( $jml_keluar > 0 ) {
                                $stok = 0;
                                if ( $jml_keluar > $d_dstokt[$idx]['jumlah'] ) {
                                    $stok = $d_dstokt[$idx]['jumlah'];
                                    $jml_keluar = $jml_keluar - $d_dstokt[$idx]['jumlah'];
                                } else {
                                    $stok = $jml_keluar;
                                    $jml_keluar = 0;
                                }

                                $data_stok[ $idx ] = array(
                                    'tgl_trans' => $d_retur_pakan['tgl_retur'],
                                    'kode_gudang' => $d_retur_pakan['id_tujuan'],
                                    'kode_barang' => $v_detail['item'],
                                    'jumlah' => $stok,
                                    'hrg_jual' => $d_dstokt[$idx]['hrg_jual'],
                                    'hrg_beli' => $d_dstokt[$idx]['hrg_beli'],
                                    'kode_trans' => $d_retur_pakan['no_order'],
                                    'jenis_barang' => 'voadip',
                                    'jenis_trans' => 'RETUR',
                                    'jml_stok' => $stok
                                );

                                $idx++;
                            }

                            if ( !empty($data_stok) ) {
                                krsort($data_stok);

                                foreach ($data_stok as $key => $value) {
                                    $m_dstok = new \Model\Storage\DetStok_model();
                                    $m_dstok->id_header = $stok_id;
                                    $m_dstok->tgl_trans = $value['tgl_trans'];
                                    $m_dstok->kode_gudang = $value['kode_gudang'];
                                    $m_dstok->kode_barang = $value['kode_barang'];
                                    $m_dstok->jumlah = $value['jumlah'];
                                    $m_dstok->hrg_jual = $value['hrg_jual'];
                                    $m_dstok->hrg_beli = $value['hrg_beli'];
                                    $m_dstok->kode_trans = $value['kode_trans'];
                                    $m_dstok->jenis_barang = $value['jenis_barang'];
                                    $m_dstok->jenis_trans = $value['jenis_trans'];
                                    $m_dstok->jml_stok = $value['jml_stok'];
                                    $m_dstok->save();
                                }
                            }
                        }
                    }
                } else {
                    // KELUAR STOK GUDANG
                    $nilai_beli = 0;
                    $nilai_jual = 0;
                    $jml_keluar = $v_detail['jumlah'];
                    while ($jml_keluar > 0) {
                        $m_dstok = new \Model\Storage\DetStok_model();
                        $sql = "
                            select top 1 * from det_stok ds 
                            where
                                ds.id_header = ".$stok_id." and 
                                ds.kode_gudang = ".$d_retur_pakan['id_asal']." and 
                                ds.kode_barang = '".$v_detail['item']."' and 
                                ds.jml_stok > 0
                            order by
                                ds.jenis_trans desc,
                                ds.tgl_trans asc,
                                ds.kode_trans asc,
                                ds.id asc
                        ";

                        $d_dstok = $m_dstok->hydrateRaw($sql);

                        if ( $d_dstok->count() > 0 ) {
                            $d_dstok = $d_dstok->toArray()[0];

                            $harga_jual = $d_dstok['hrg_jual'];
                            $harga_beli = $d_dstok['hrg_beli'];

                            $jml_stok = $d_dstok['jml_stok'];
                            if ( $d_dstok['jml_stok'] > $jml_keluar ) {
                                $jml_stok = $jml_stok - $jml_keluar;
                                $nilai_beli += $jml_keluar*$harga_beli;
                                $nilai_jual += $jml_keluar*$harga_jual;

                                $m_dstokt = new \Model\Storage\DetStokTrans_model();
                                $m_dstokt->id_header = $d_dstok['id'];
                                $m_dstokt->kode_trans = $d_retur_pakan->no_order;
                                $m_dstokt->jumlah = $jml_keluar;
                                $m_dstokt->kode_barang = $v_detail['barang'];
                                $m_dstokt->save();

                                $jml_keluar = 0;
                            } else {
                                $jml_keluar = $jml_keluar - $d_dstok['jml_stok'];
                                $nilai_beli += $d_dstok['jml_stok']*$harga_beli;
                                $nilai_jual += $d_dstok['jml_stok']*$harga_jual;

                                $m_dstokt = new \Model\Storage\DetStokTrans_model();
                                $m_dstokt->id_header = $d_dstok['id'];
                                $m_dstokt->kode_trans = $d_retur_pakan->no_order;
                                $m_dstokt->jumlah = $d_dstok['jml_stok'];
                                $m_dstokt->kode_barang = $v_detail['barang'];
                                $m_dstokt->save();

                                $jml_stok = 0;
                            }
                            $m_dstok->where('id', $d_dstok['id'])->update(
                                array(
                                    'jml_stok' => $jml_stok
                                )
                            );
                        } else {
                            $jml_keluar = 0;
                        }
                    }
                }
            }
        }
    }

    public function listActivity()
    {
        $params = $this->input->get('params');

        $m_retur_pakan = new \Model\Storage\ReturPakan_model();
        $d_retur_pakan = $m_retur_pakan->where('id', $params['id'])->with(['logs'])->first()->toArray();

        $data = array(
            'tgl_retur' => $params['tgl_retur'],
            'no_order' => $params['no_order'],
            'asal' => $params['asal'],
            'tujuan' => $params['tujuan'],
            'logs' => $d_retur_pakan['logs']
        );

        $content['data'] = $data;
        $html = $this->load->view('transaksi/retur_pakan/list_activity', $content, true);

        echo $html;
    }

    public function tes()
    {
        $m_rp = new \Model\Storage\ReturPakan_model();
        $no_retur = $m_rp->getNextId();

        cetak_r( $no_retur );
    }
}