<?php defined('BASEPATH') OR exit('No direct script access allowed');

class OrderPeralatan extends Public_Controller {

    private $path = 'transaksi/order_peralatan/';
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
                "assets/transaksi/order_peralatan/js/order-peralatan.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/order_peralatan/css/order-peralatan.css",
            ));

            $data = $this->includes;

            // $mitra = $this->getMitra();
            // $peralatan = $this->get_peralatan();

            $content['akses'] = $akses;

            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();

            // Load Indexx
            $data['title_menu'] = 'Order Peralatan';
            $data['view'] = $this->load->view($this->path.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $sql_query_supplier = null;
        if (  stristr($params['supplier'], 'all') === FALSE  ) {
            $sql_query_supplier = "and op.supplier = '".$params['supplier']."'";
        }
        $sql_query_mitra = null;
        if (  stristr($params['mitra'], 'all') === FALSE  ) {
            $sql_query_mitra = "and op.mitra = '".$params['mitra']."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select op.*, mtr.nama as nama_mitra, supl.nama as nama_supplier from order_peralatan op
            right join
                (
                    select mtr1.* from mitra mtr1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) mtr2
                        on
                            mtr1.id = mtr2.id
                ) mtr
                on
                    mtr.nomor = op.mitra
            right join
                (
                    select supl1.* from pelanggan supl1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) supl2
                        on
                            supl1.id = supl2.id
                ) supl
                on
                    supl.nomor = op.supplier
            where
                op.tgl_order between '".$params['start_date']."' and '".$params['end_date']."'
                ".$sql_query_supplier."
                ".$sql_query_mitra."
        ";
        $d_op = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_op->count() > 0 ) {
            $data = $d_op->toArray();
        }

        $content['data'] = $data;

        $html = $this->load->view($this->path.'list', $content, TRUE);

        echo $html;
    }

    public function loadForm()
    {
        $params = $this->input->get('params');

        if ( isset($params['id']) && !empty($params['id']) ) {
            if ( isset($params['edit']) && !empty($params['edit']) ) {
                $html = $this->editForm( $params['id'] );
            } else {
                $html = $this->viewForm( $params['id'] );
            }
        } else {
            $html = $this->addForm();
        }

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

    public function getBarang()
    {
        $data = array();

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select b.* from barang b
            right join
                (
                    select max(id) as id, kode from barang where tipe = 'peralatan' group by kode
                ) b1
                on
                    b.id = b1.id
            order by
                b.nama asc
        ";
        $d_barang = $m_conf->hydrateRaw( $sql );

        if ( $d_barang->count() > 0 ) {
            $data = $d_barang->toArray();
        }

        return $data;
    }

    public function riwayat()
    {
        $html = null;

        $content['mitra'] = $this->getMitra();

        $html = $this->load->view($this->path.'riwayat', $content, TRUE);

        return $html;
    }

    public function addForm()
    {
        $html = null;

        $content['supplier'] = $this->getSupplier();
        $content['mitra'] = $this->getMitra();
        $content['barang'] = $this->getBarang();

        $html = $this->load->view($this->path.'addForm', $content, TRUE);

        return $html;
    }

    public function viewForm( $id )
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                op.id,
                op.no_order,
                op.tgl_order,
                op.total as grand_total,
                mtr.nama as nama_mitra, 
                supl.nama as nama_supplier,
                opd.*, 
                brg.nama as nama_barang
            from order_peralatan_detail opd
            right join
                order_peralatan op
                on
                    op.id = opd.id_header
            left join
                (
                    select brg1.* from barang brg1
                    right join
                        (select max(id) as id, kode from barang group by kode) brg2
                        on
                            brg1.id = brg2.id
                ) brg
                on
                    brg.kode = opd.kode_barang
            left join
                (
                    select mtr1.* from mitra mtr1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) mtr2
                        on
                            mtr1.id = mtr2.id
                ) mtr
                on
                    mtr.nomor = op.mitra
            left join
                (
                    select supl1.* from pelanggan supl1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) supl2
                        on
                            supl1.id = supl2.id
                ) supl
                on
                    supl.nomor = op.supplier
            where
                op.id = ".$id."
        ";
        $d_op = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_op->count() > 0 ) {
            $d_op = $d_op->toArray();
            foreach ($d_op as $k_op => $v_op) {
                $data['id'] = $v_op['id'];
                $data['no_order'] = $v_op['no_order'];
                $data['tgl_order'] = $v_op['tgl_order'];
                $data['grand_total'] = $v_op['grand_total'];
                $data['nama_mitra'] = $v_op['nama_mitra'];
                $data['nama_supplier'] = $v_op['nama_supplier'];
                $data['detail'][] = $v_op;
            }
        }

        $content['data'] = $data;

        $html = $this->load->view($this->path.'viewForm', $content, TRUE);

        return $html;
    }

    public function editForm( $id )
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                op.id,
                op.no_order,
                op.tgl_order,
                op.total,
                op.mitra, 
                op.supplier,
                opd.*, 
                brg.nama as nama_barang
            from 
                order_peralatan_detail opd
            right join
                (
                    select brg1.* from barang brg1
                    right join
                        (select max(id) as id, kode from barang group by kode) brg2
                        on
                            brg1.id = brg2.id
                ) brg
                on
                    brg.kode = opd.kode_barang
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
                    mtr.nomor = op.mitra
            right join
                (
                    select supl1.* from pelanggan supl1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) supl2
                        on
                            supl1.id = supl2.id
                ) supl
                on
                    supl.nomor = op.supplier
            where
                op.id = ".$id."
        ";
        $d_op = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_op->count() > 0 ) {
            $d_op = $d_op->toArray();
            foreach ($d_op as $k_op => $v_op) {
                $data['id'] = $v_op['id'];
                $data['no_order'] = $v_op['no_order'];
                $data['tgl_order'] = $v_op['tgl_order'];
                $data['total'] = $v_op['total'];
                $data['mitra'] = $v_op['mitra'];
                $data['supplier'] = $v_op['supplier'];
                $data['detail'][] = $v_op;
            }
        }

        $content['data'] = $data;
        $content['supplier'] = $this->getSupplier();
        $content['mitra'] = $this->getMitra();
        $content['barang'] = $this->getBarang();

        $html = $this->load->view($this->path.'editForm', $content, TRUE);

        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_op = new \Model\Storage\OrderPeralatan_model();

            $no_order = $m_op->getNextNomor('OPR/'.$params['kode_unit']);

            $m_op->no_order = $no_order;
            $m_op->tgl_order = $params['tgl_order'];
            $m_op->mitra = $params['mitra'];
            $m_op->supplier = $params['supplier'];
            $m_op->total = $params['grand_total'];
            $m_op->save();

            $id = $m_op->id;

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_opd = new \Model\Storage\OrderPeralatanDetail_model();
                $m_opd->id_header = $id;
                $m_opd->kode_barang = $v_det['kode_barang'];
                $m_opd->jumlah = $v_det['jumlah'];
                $m_opd->harga = $v_det['harga'];
                $m_opd->total = $v_det['total'];
                $m_opd->save();
            }

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_op, $deskripsi_log);

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

            $m_op = new \Model\Storage\OrderPeralatan_model();
            $m_op->where('id', $id)->update(
                array(
                    'tgl_order' => $params['tgl_order'],
                    'mitra' => $params['mitra'],
                    'supplier' => $params['supplier'],
                    'total' => $params['grand_total']
                )
            );

            $m_opd = new \Model\Storage\OrderPeralatanDetail_model();
            $m_opd->where('id_header', $id)->delete();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_opd = new \Model\Storage\OrderPeralatanDetail_model();
                $m_opd->id_header = $id;
                $m_opd->kode_barang = $v_det['kode_barang'];
                $m_opd->jumlah = $v_det['jumlah'];
                $m_opd->harga = $v_det['harga'];
                $m_opd->total = $v_det['total'];
                $m_opd->save();
            }

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $m_op, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $id);
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
            $id = $params['id'];

            $m_op = new \Model\Storage\OrderPeralatan_model();
            $d_op = $m_op->where('id', $id)->first();

            $m_opd = new \Model\Storage\OrderPeralatanDetail_model();
            $m_opd->where('id_header', $id)->delete();
            $m_op->where('id', $id)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_op, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}