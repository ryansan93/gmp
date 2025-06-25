<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PembayaranPeralatan extends Public_Controller
{
    private $path = 'pembayaran/pembayaran_peralatan/';

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
                "assets/compress-image/js/compress-image.js",
                'assets/pembayaran/pembayaran_peralatan/js/pembayaran-peralatan.js'
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/pembayaran/pembayaran_peralatan/css/pembayaran-peralatan.css'
            ));

            $data = $this->includes;

            $data['title_menu'] = 'Pembayaran Peralatan';

            $mitra = null;

            $content['add_form'] = $this->addForm();
            $content['riwayat'] = $this->riwayat();

            $content['akses'] = $this->hakAkses;
            $data['view'] = $this->load->view($this->path.'index', $content, true);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function loadForm()
    {
        $params = $this->input->get('params');

        $id = $params['id'];
        $edit = isset($params['edit']) ? $params['edit'] : null;

        $content = array();
        $html = "url not found";
        
        if ( !empty($id) && $edit != 'edit' ) {
            // NOTE: view data BASTTB (ajax)
            $html = $this->viewForm( $id );
        } else if ( !empty($id) && $edit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $html = $this->editForm($id);
        }else{
            $html = $this->addForm();
        }

        echo $html;
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $start_date = $params['startDate'];
        $end_date = $params['endDate'];
        $supplier = $params['supplier'];
        $mitra = $params['mitra'];

        $sql_supplier = null;
        if ( stristr($supplier[0], 'all') === false ) {
            $sql_supplier = "and op.supplier in ('".implode("', '", $supplier)."')";
        }

        $sql_mitra = null;
        if ( stristr($mitra[0], 'all') === false ) {
            $sql_mitra = "and op.mitra in ('".implode("', '", $mitra)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                bp.id,
                bp.no_faktur, 
                bp.tgl_bayar, 
                supl.nama as nama_supplier, 
                mtr.nama as nama_mitra,
                bp.jml_tagihan,
                bp.tot_bayar
            from bayar_peralatan bp
            right join
                order_peralatan op
                on
                    bp.no_order = op.no_order
            right join
                (
                    select mtr1.* from mitra mtr1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) mtr2
                        on
                            mtr1.id = mtr2.id
                ) mtr
                on
                    op.mitra = mtr.nomor
            right join
                (
                    select p2.* from pelanggan p2
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) p1
                        on
                            p2.id = p1.id
                ) supl
                on
                    op.supplier = supl.nomor
            where
                bp.tgl_bayar between '".$start_date."' and '".$end_date."'
                ".$sql_supplier."
                ".$sql_mitra."
            order by
                bp.tgl_bayar desc,
                mtr.nama asc
        ";
        $d_bp = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_bp->count() > 0 ) {
            $data = $d_bp->toArray();
        }

        $content['data'] = $data;

        $html = $this->load->view($this->path.'list', $content, true);

        echo $html;
    }

    public function getMitra()
    {
        $data = array();

        $m_duser = new \Model\Storage\DetUser_model();
        $d_duser = $m_duser->where('id_user', $this->userid)->first();

        $m_karyawan = new \Model\Storage\Karyawan_model();
        $d_karyawan = $m_karyawan->where('nama', 'like', strtolower(trim($d_duser->nama_detuser)).'%')->orderBy('id', 'desc')->first();

        $kode_unit = array();
        $kode_unit_all = null;
        if ( $d_karyawan ) {
            $m_ukaryawan = new \Model\Storage\UnitKaryawan_model();
            $d_ukaryawan = $m_ukaryawan->where('id_karyawan', $d_karyawan->id)->get();

            if ( $d_ukaryawan->count() > 0 ) {
                $d_ukaryawan = $d_ukaryawan->toArray();

                foreach ($d_ukaryawan as $k_ukaryawan => $v_ukaryawan) {
                    if ( stristr($v_ukaryawan['unit'], 'all') === false ) {
                        $m_wil = new \Model\Storage\Wilayah_model();
                        $d_wil = $m_wil->where('id', $v_ukaryawan['unit'])->first();

                        array_push($kode_unit, $d_wil->kode);
                        // $kode_unit = $d_wil->kode;
                    } else {
                        $m_wil = new \Model\Storage\Wilayah_model();
                        $sql = "
                            select kode from wilayah where kode is not null group by kode
                        ";
                        $d_wil = $m_wil->hydrateRaw($sql);

                        if ( $d_wil->count() > 0 ) {
                            $d_wil = $d_wil->toArray();

                            foreach ($d_wil as $key => $value) {
                                array_push($kode_unit, $value['kode']);
                            }
                        }
                    }
                }
            } else {
                $m_wil = new \Model\Storage\Wilayah_model();
                $sql = "
                    select kode from wilayah where kode is not null group by kode
                ";
                $d_wil = $m_wil->hydrateRaw($sql);

                if ( $d_wil->count() > 0 ) {
                    $d_wil = $d_wil->toArray();

                    foreach ($d_wil as $key => $value) {
                        array_push($kode_unit, $value['kode']);
                    }
                }
            }
        } else {
            $m_wil = new \Model\Storage\Wilayah_model();
            $sql = "
                select kode from wilayah where kode is not null group by kode
            ";
            $d_wil = $m_wil->hydrateRaw($sql);

            if ( $d_wil->count() > 0 ) {
                $d_wil = $d_wil->toArray();

                foreach ($d_wil as $key => $value) {
                    array_push($kode_unit, $value['kode']);
                }
            }
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from
                (
                    select
                        m.nomor,
                        m.nama,
                        w.kode as kode_unit
                    from kandang k
                    right join
                        (
                            select mm1.* from mitra_mapping mm1
                            right join
                                (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                on
                                    mm1.id = mm2.id
                        ) mm
                        on
                            mm.id = k.mitra_mapping
                    right join
                        mitra m 
                        on
                            mm.mitra = m.id
                    right join
                        wilayah w
                        on
                            w.id = k.unit
                    where
                        m.mstatus = 1
                    group by
                        m.nomor,
                        m.nama,
                        w.kode
                ) as data
            where
                data.kode_unit in ('".implode("', '", $kode_unit)."')
            order by
                nama asc
        ";
        $d_mitra = $m_conf->hydrateRaw( $sql );

        if ( $d_mitra->count() > 0 ) {
            $data = $d_mitra->toArray();
        }

        return $data;
    }

    public function getSupplier()
    {
        $data = array();

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select p.* from pelanggan p
            right join
                (
                    select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor
                ) p1
                on
                    p.id = p1.id
            where
                p.mstatus = 1
            order by
                p.nama asc
        ";
        $d_supplier = $m_conf->hydrateRaw( $sql );

        if ( $d_supplier->count() > 0 ) {
            $data = $d_supplier->toArray();
        }

        return $data;
    }

    public function getNoOrder()
    {
        $params = $this->input->post( 'params' );

        try {
            $supplier = $params['supplier'];

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select op.*, mtr.nama as nama_mitra from order_peralatan op
                right join
                    (
                        select mtr1.* from mitra mtr1
                        right join
                            (select max(id) as id, nomor from mitra group by nomor) mtr2
                            on
                                mtr1.id = mtr2.id
                    ) mtr
                    on
                        op.mitra = mtr.nomor
                where
                    op.supplier = '".$supplier."'
                order by
                    op.tgl_order asc,
                    op.no_order asc
            ";
            $d_no_order = $m_conf->hydrateRaw( $sql );

            $data = null;
            if ( $d_no_order->count() > 0 ) {
                $d_no_order = $d_no_order->toArray();

                foreach ($d_no_order as $key => $value) {
                    $data[ $key ] = array(
                        'id' => $value['id'],
                        'no_order' => $value['no_order'],
                        'tgl_order' => tglIndonesia($value['tgl_order'], '-', ' '),
                        'supplier' => $value['supplier'],
                        'mitra' => $value['mitra'],
                        'total' => $value['total'],
                        'nama_mitra' => $value['nama_mitra']
                    );
                }
            }

            $this->result['status'] = 1;
            $this->result['content'] = $data;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function getDetailOrder()
    {
        $params = $this->input->get('params');

        $no_order = $params['no_order'];

        $data = $this->getDataDetailOrder( $no_order );

        $content['data'] = $data;

        $html = $this->load->view($this->path.'detailOrder', $content, true);

        echo $html;
    }

    public function getDataDetailOrder($no_order)
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select opd.*, brg.nama as nama_barang from order_peralatan_detail opd
            right join
                (
                    select brg1.* from barang brg1
                    right join
                        (select max(id) as id, kode from barang group by kode) brg2
                        on
                            brg1.id = brg2.id
                ) brg
                on
                    opd.kode_barang = brg.kode
            right join
                order_peralatan op
                on
                    op.id = opd.id_header
            where
                op.no_order = '".$no_order."'
            order by
                brg.nama asc
        ";
        $d_no_order = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_no_order->count() > 0 ) {
            $data = $d_no_order->toArray();
        }

        return $data;
    }

    public function riwayat()
    {
        $content['mitra'] = $this->getMitra();
        $content['supplier'] = $this->getSupplier();

        $html = $this->load->view($this->path.'riwayat', $content, true);

        return $html;
    }

    public function addForm()
    {
        $content['supplier'] = $this->getSupplier();

        $html = $this->load->view($this->path.'addForm', $content, true);

        return $html;
    }

    public function viewForm($id)
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                bp.id, 
                bp.tgl_bayar,
                supl.nama as nama_supplier,
                mtr.nama as nama_mitra,
                bp.no_order,
                bp.no_faktur,
                bp.jml_tagihan,
                bp.saldo,
                bp.jml_bayar,
                bp.tot_bayar,
                bp.lampiran,
                opd.*, 
                brg.nama as nama_barang 
            from order_peralatan_detail opd
            right join
                (
                    select brg1.* from barang brg1
                    right join
                        (select max(id) as id, kode from barang group by kode) brg2
                        on
                            brg1.id = brg2.id
                ) brg
                on
                    opd.kode_barang = brg.kode
            right join
                order_peralatan op
                on
                    op.id = opd.id_header
            right join
                (
                    select mtr1.* from mitra mtr1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) mtr2
                        on
                            mtr1.id = mtr2.id
                ) mtr
                on
                    op.mitra = mtr.nomor
            right join
                (
                    select p2.* from pelanggan p2
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) p1
                        on
                            p2.id = p1.id
                ) supl
                on
                    op.supplier = supl.nomor
            right join
                bayar_peralatan bp
                on
                    bp.no_order = op.no_order
            where
                bp.id = '".$id."'
            order by
                brg.nama asc
        ";
        $d_pp = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_pp->count() > 0 ) {
            $d_pp = $d_pp->toArray();

            $data = array(
                'id' => $d_pp[0]['id'],
                'tgl_bayar' => $d_pp[0]['tgl_bayar'],
                'nama_supplier' => $d_pp[0]['nama_supplier'],
                'nama_mitra' => $d_pp[0]['nama_mitra'],
                'no_order' => $d_pp[0]['no_order'],
                'no_faktur' => $d_pp[0]['no_faktur'],
                'jml_tagihan' => $d_pp[0]['jml_tagihan'],
                'saldo' => $d_pp[0]['saldo'],
                'jml_bayar' => $d_pp[0]['jml_bayar'],
                'tot_bayar' => $d_pp[0]['tot_bayar'],
                'lampiran' => $d_pp[0]['lampiran'],
                'detail' => null,
            );
            foreach ($d_pp as $key => $value) {
                $data['detail'][ $key ] = array(
                    'kode_barang' => $value['kode_barang'],
                    'jumlah' => $value['jumlah'],
                    'harga' => $value['harga'],
                    'total' => $value['total'],
                    'nama_barang' => $value['nama_barang']
                );
            }
        }

        $content['data'] = $data;

        $html = $this->load->view($this->path.'viewForm', $content, true);

        return $html;
    }

    public function editForm($id)
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                bp.id, 
                bp.tgl_bayar,
                op.supplier,
                op.mitra,
                supl.nama as nama_supplier,
                mtr.nama as nama_mitra,
                bp.no_order,
                bp.no_faktur,
                bp.jml_tagihan,
                bp.saldo,
                bp.jml_bayar,
                bp.tot_bayar,
                bp.lampiran,
                opd.*, 
                brg.nama as nama_barang 
            from order_peralatan_detail opd
            right join
                (
                    select brg1.* from barang brg1
                    right join
                        (select max(id) as id, kode from barang group by kode) brg2
                        on
                            brg1.id = brg2.id
                ) brg
                on
                    opd.kode_barang = brg.kode
            right join
                order_peralatan op
                on
                    op.id = opd.id_header
            right join
                (
                    select mtr1.* from mitra mtr1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) mtr2
                        on
                            mtr1.id = mtr2.id
                ) mtr
                on
                    op.mitra = mtr.nomor
            right join
                (
                    select p2.* from pelanggan p2
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) p1
                        on
                            p2.id = p1.id
                ) supl
                on
                    op.supplier = supl.nomor
            right join
                bayar_peralatan bp
                on
                    bp.no_order = op.no_order
            where
                bp.id = '".$id."'
            order by
                brg.nama asc
        ";
        $d_pp = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_pp->count() > 0 ) {
            $d_pp = $d_pp->toArray();

            $data = array(
                'id' => $d_pp[0]['id'],
                'tgl_bayar' => $d_pp[0]['tgl_bayar'],
                'supplier' => $d_pp[0]['supplier'],
                'mitra' => $d_pp[0]['mitra'],
                'nama_supplier' => $d_pp[0]['nama_supplier'],
                'nama_mitra' => $d_pp[0]['nama_mitra'],
                'no_order' => $d_pp[0]['no_order'],
                'no_faktur' => $d_pp[0]['no_faktur'],
                'jml_tagihan' => $d_pp[0]['jml_tagihan'],
                'saldo' => $d_pp[0]['saldo'],
                'jml_bayar' => $d_pp[0]['jml_bayar'],
                'tot_bayar' => $d_pp[0]['tot_bayar'],
                'lampiran' => $d_pp[0]['lampiran'],
                'detail' => null,
            );
            foreach ($d_pp as $key => $value) {
                $data['detail'][ $key ] = array(
                    'kode_barang' => $value['kode_barang'],
                    'jumlah' => $value['jumlah'],
                    'harga' => $value['harga'],
                    'total' => $value['total'],
                    'nama_barang' => $value['nama_barang']
                );
            }
        }

        $content['supplier'] = $this->getSupplier();
        $content['data'] = $data;

        $html = $this->load->view($this->path.'editForm', $content, true);

        return $html;
    }

    public function save()
    {
        $data = json_decode($this->input->post('data'),TRUE);
        $file = isset($_FILES['file']) ? $_FILES['file'] : [];

        try {
            $file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($file)) {
                $moved = uploadFile($file);
                $isMoved = $moved['status'];
            }
            if ($isMoved) {
                $file_name = $moved['name'];
                $path_name = $moved['path'];

                $m_bp = new \Model\Storage\BayarPeralatan_model();
                $m_bp->no_order = $data['no_order'];
                $m_bp->tgl_bayar = $data['tgl_bayar'];
                $m_bp->jml_tagihan = $data['jml_tagihan'];
                $m_bp->saldo = $data['saldo'];
                $m_bp->jml_bayar = $data['jml_bayar'];
                $m_bp->tot_bayar = $data['tot_bayar'];
                $m_bp->status = ($data['jml_tagihan'] <= $data['tot_bayar']) ? 'LUNAS' : 'BELUM';
                $m_bp->no_faktur = $data['no_faktur'];
                $m_bp->lampiran = $path_name;
                $m_bp->save();

                $m_conf = new \Model\Storage\Conf();
                $sql = "exec insert_jurnal 'PERALATAN', '".$data['no_order']."', NULL, 0, 'bayar_peralatan', ".$m_bp->id.", NULL, 1";
                $d_conf = $m_conf->hydrateRaw( $sql );

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $m_bp, $deskripsi_log);

                $this->result['status'] = 1;
                $this->result['content'] = array('id' => $m_bp->id);
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
        $file = isset($_FILES['file']) ? $_FILES['file'] : [];

        try {
            $path_name = null;

            $m_bp = new \Model\Storage\BayarPeralatan_model();
            $d_bp = $m_bp->where('id', $data['id'])->first();

            $path_name = $d_bp->lampiran;

            $isMoved = 0;
            if (!empty($file)) {
                $moved = uploadFile($file);
                $isMoved = $moved['status'];
            }
            if ($isMoved) {
                $path_name = $moved['path'];
            }

            $m_bp = new \Model\Storage\BayarPeralatan_model();
            $m_bp->where('id', $data['id'])->update(
                array(
                    'no_order' => $data['no_order'],
                    'tgl_bayar' => $data['tgl_bayar'],
                    'jml_tagihan' => $data['jml_tagihan'],
                    'saldo' => $data['saldo'],
                    'jml_bayar' => $data['jml_bayar'],
                    'tot_bayar' => $data['tot_bayar'],
                    'status' => ($data['jml_tagihan'] <= $data['tot_bayar']) ? 'LUNAS' : 'BELUM',
                    'no_faktur' => $data['no_faktur'],
                    'lampiran' => $path_name
                )
            );

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal 'PERALATAN', '".$data['no_order']."', NULL, 0, 'bayar_peralatan', ".$data['id'].", ".$data['id'].", 2";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'di-ubah oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $m_bp, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $data['id']);
            $this->result['message'] = 'Data berhasil di ubah.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {
            $path_name = null;

            $m_bp = new \Model\Storage\BayarPeralatan_model();
            $d_bp = $m_bp->where('id', $params['id'])->first();

            $m_bp->where('id', $params['id'])->delete();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal NULL, NULL, NULL, NULL, 'bayar_peralatan', ".$params['id'].", ".$params['id'].", 3";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'di-ubah oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_bp, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}