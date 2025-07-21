<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CnVoadip extends Public_Controller {

    private $path = 'transaksi/cn_voadip/';
    private $url;
    private $akses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->akses = hakAkses($this->url);
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index($segment=0)
    {
        if ( $this->akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/transaksi/cn_voadip/js/cn-voadip.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/cn_voadip/css/cn-voadip.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->akses;

            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();

            // Load Indexx
            $data['title_menu'] = 'Credit Note OVK';
            $data['view'] = $this->load->view($this->path.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getSupplier()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select plg1.* from pelanggan plg1
            right join
                (select max(id) as id, nomor from pelanggan group by nomor) plg2
                on
                    plg1.id = plg2.id
            where
                plg1.tipe = 'supplier' and
                plg1.jenis <> 'ekspedisi'
            order by
                plg1.nama asc
        ";
        $d_supl = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_supl->count() > 0 ) {
            $data = $d_supl->toArray();
        }

        return $data;
    }

    public function getNoSj() {
        $search = $this->input->get('search');
        $type = $this->input->get('type');
        $supplier = $this->input->get('supplier');

        $sql_inv = "";
        if ( !empty($search) && !empty($type) ) {
            $sql_inv = "where UPPER(REPLACE(CONVERT(varchar, kpv.tgl_bayar, 103), '-', '/')+' | '+kpvd.no_sj) like '%".$search."%'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select kpvd.no_sj as id, REPLACE(CONVERT(varchar, kpv.tgl_bayar, 103), '-', '/')+' | '+kpvd.no_sj as text from konfirmasi_pembayaran_voadip_det kpvd
            left join
                konfirmasi_pembayaran_voadip kpv
                on
                    kpvd.id_header = kpv.id
            where
                kpv.supplier = '".$supplier."'
                ".$sql_inv."
        ";
        $d_inv = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_inv->count() > 0 ) {
            $data = $d_inv->toArray();
        }
        
        echo json_encode($data);
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $sql_query_supplier = null;
        if (  stristr($params['supplier'], 'all') === FALSE  ) {
            $sql_query_supplier = "and c.supplier = '".$params['supplier']."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                c.*,
                supl.nama as nama_supplier
            from cn c
            left join
                (
                    select plg1.* from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                        on
                            plg1.id = plg2.id
                ) supl
                on
                    supl.nomor = c.supplier
            where
                c.nomor like '%OVK%' and
                c.tanggal between '".$params['start_date']."' and '".$params['end_date']."'
                ".$sql_query_supplier."
            order by
                c.tanggal desc
        ";
        $d_cn = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_cn->count() > 0 ) {
            $data = $d_cn->toArray();
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

    public function riwayat()
    {
        $html = null;

        $content['supplier'] = $this->getSupplier();
        $html = $this->load->view($this->path.'riwayat', $content, TRUE);

        return $html;
    }

    public function addForm()
    {
        $html = null;

        $content['supplier'] = $this->getSupplier();
        $html = $this->load->view($this->path.'addForm', $content, TRUE);

        return $html;
    }

    public function viewForm($id)
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                c.*,
                supl.nama as nama_supplier
            from cn c
            left join
                (
                    select plg1.* from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                        on
                            plg1.id = plg2.id
                ) supl
                on
                    supl.nomor = c.supplier
            where
                c.id = ".$id."
            order by
                c.tanggal desc
        ";
        $d_cn = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_cn->count() > 0 ) {
            $data = $d_cn->toArray()[0];

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    cd.*,
                    kpvd.tgl_sj as tgl_sj
                from cn_det cd
                left join
                    konfirmasi_pembayaran_voadip_det kpvd
                    on
                        cd.no_sj = kpvd.no_sj
                where
                    cd.id_header = ".$id."
            ";
            $d_cnd = $m_conf->hydrateRaw( $sql );

            if ( $d_cnd->count() > 0 ) {
                $d_cnd = $d_cnd->toArray();

                $data['detail'] = $d_cnd;
            }
        }

        $content['akses'] = $this->akses;
        $content['data'] = $data;

        $html = $this->load->view($this->path.'viewForm', $content, TRUE);

        return $html;
    }

    public function editForm($id)
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                c.*,
                supl.nama as nama_supplier
            from cn c
            left join
                (
                    select plg1.* from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                        on
                            plg1.id = plg2.id
                ) supl
                on
                    supl.nomor = c.supplier
            where
                c.id = ".$id."
            order by
                c.tanggal desc
        ";
        $d_cn = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_cn->count() > 0 ) {
            $data = $d_cn->toArray()[0];

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    cd.*,
                    REPLACE(CONVERT(varchar, kpvd.tgl_sj, 103), '-', '/') as tgl_sj
                from cn_det cd
                left join
                    konfirmasi_pembayaran_voadip_det kpvd
                    on
                        cd.no_sj = kpvd.no_sj
                where
                    cd.id_header = ".$id."
            ";
            $d_cnd = $m_conf->hydrateRaw( $sql );

            if ( $d_cnd->count() > 0 ) {
                $d_cnd = $d_cnd->toArray();

                $data['detail'] = $d_cnd;
            }
        }

        $content['akses'] = $this->akses;
        $content['supplier'] = $this->getSupplier();
        $content['data'] = $data;

        $html = $this->load->view($this->path.'editForm', $content, TRUE);

        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_cn = new \Model\Storage\Cn_model();
            $nomor = $m_cn->getNextNomor('CN/OVK');

            $m_cn->nomor = $nomor;
            $m_cn->tanggal = $params['tgl_cn'];
            $m_cn->supplier = $params['supplier'];
            $m_cn->ket_cn = $params['ket_cn'];
            $m_cn->tot_cn = $params['tot_cn'];
            $m_cn->save();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_cnd = new \Model\Storage\CnDet_model();
                $m_cnd->id_header = $m_cn->id;
                $m_cnd->no_sj = $v_det['no_sj'];
                $m_cnd->ket = $v_det['ket'];
                $m_cnd->nominal = $v_det['nominal'];
                $m_cnd->save();
            }

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_cn, $deskripsi_log);
            
            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $m_cn->id);
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

            $m_cn = new \Model\Storage\Cn_model();
            $m_cn->where('id', $id)->update(
                array(
                    'tanggal' => $params['tgl_cn'],
                    'supplier' => $params['supplier'],
                    'ket_cn' => $params['ket_cn'],
                    'tot_cn' => $params['tot_cn'],
                )
            );

            $m_cnd = new \Model\Storage\CnDet_model();
            $m_cnd->where('id_header', $id)->delete();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_cnd = new \Model\Storage\CnDet_model();
                $m_cnd->id_header = $id;
                $m_cnd->no_sj = $v_det['no_sj'];
                $m_cnd->ket = $v_det['ket'];
                $m_cnd->nominal = $v_det['nominal'];
                $m_cnd->save();
            }

            $d_cn = $m_cn->where('id', $id)->first();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_cn, $deskripsi_log);
            
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

            $m_cn = new \Model\Storage\Cn_model();
            $d_cn = $m_cn->where('id', $id)->first();

            $m_cn->where('id', $id)->delete();

            $m_cnd = new \Model\Storage\CnDet_model();
            $m_cnd->where('id_header', $id)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_cn, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes() {
        $m_cn = new \Model\Storage\Cn_model();
        $nomor = $m_cn->getNextNomor('CN/OVK');

        cetak_r( $nomor, 1 );
    }
}