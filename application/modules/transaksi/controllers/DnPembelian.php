<?php defined('BASEPATH') OR exit('No direct script access allowed');

class DnPembelian extends Public_Controller {

    private $path = 'transaksi/dn_pembelian/';
    private $jenis_dn = array(
        'DOC' => 'DOC',
        'PKN' => 'PAKAN',
        'OVK' => 'OVK',
        'NS' => 'NON SAPRONAK'
    );
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
                "assets/transaksi/dn_pembelian/js/dn-pembelian.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/dn_pembelian/css/dn-pembelian.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->akses;

            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();

            // Load Indexx
            $data['title_menu'] = 'Debit Note Pembelian';
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

    public function getGudang()
    {
        $search = $this->input->get('search');
        $type = $this->input->get('type');
        $_jenis_dn = $this->input->get('jenis_dn');

        $jenis_dn = null;
        if ( stristr('pkn', $_jenis_dn) !== false ) {
            $jenis_dn = 'PAKAN';
        }

        if ( stristr('ovk', $_jenis_dn) !== false ) {
            $jenis_dn = 'OBAT';
        }

        $sql_gdg = "";
        if ( !empty($search) && !empty($type) ) {
            $sql_gdg = "and gdg.nama like '%".$search."%'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                gdg.id as id,
                gdg.nama as text
            from gudang gdg
            where
                gdg.jenis = '".$jenis_dn."'
                ".$sql_gdg."
        ";
        $d_gdg = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_gdg->count() > 0 ) {
            $data = $d_gdg->toArray();
        }
        
        echo json_encode($data);
    }

    public function getNoSj() {
        $search = $this->input->get('search');
        $type = $this->input->get('type');
        $supplier = $this->input->get('supplier');
        $jenis_dn = $this->input->get('jenis_dn');

        $d_inv = null;

        if ( stristr('doc', $jenis_dn) !== false ) {
            $sql_inv = "";
            if ( !empty($search) && !empty($type) ) {
                $sql_inv = "where UPPER(REPLACE(CONVERT(varchar, kpd.tgl_bayar, 103), '-', '/')+' | '+td.no_sj) like '%".$search."%'";
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select td.no_sj as id, REPLACE(CONVERT(varchar, kpd.tgl_bayar, 103), '-', '/')+' | '+td.no_sj as text from konfirmasi_pembayaran_doc_det kpdd
                left join
                    konfirmasi_pembayaran_doc kpd
                    on
                        kpdd.id_header = kpd.id
                left join
                    (
                        select td1.* from terima_doc td1
                        right join
                            (select max(id) as id, no_order from terima_doc group by no_order) td2
                            on
                                td1.id = td2.id
                    ) td
                    on
                        td.no_order = kpdd.no_order
                where
                    kpd.supplier = '".$supplier."'
                    ".$sql_inv."
            ";
            $d_inv = $m_conf->hydrateRaw($sql);
        }

        if ( stristr('pkn', $jenis_dn) !== false ) {
            $sql_inv = "";
            if ( !empty($search) && !empty($type) ) {
                $sql_inv = "where UPPER(REPLACE(CONVERT(varchar, kppd.tgl_sj, 103), '-', '/')+' | '+kppd.no_sj) like '%".$search."%'";
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select kppd.no_sj as id, REPLACE(CONVERT(varchar, kppd.tgl_sj, 103), '-', '/')+' | '+kppd.no_sj as text from konfirmasi_pembayaran_pakan_det kppd
                left join
                    konfirmasi_pembayaran_pakan kpp
                    on
                        kppd.id_header = kpp.id
                where
                    kpp.supplier = '".$supplier."'
                    ".$sql_inv."
            ";
            $d_inv = $m_conf->hydrateRaw($sql);
        }

        if ( stristr('ovk', $jenis_dn) !== false ) {
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
        }

        $data = null;
        if ( !empty($d_inv) && $d_inv->count() > 0 ) {
            $data = $d_inv->toArray();
        }
        
        echo json_encode($data);
    }

    public function getJurnalTrans() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select jt.* from
            (
                select jt1.* from
                (
                    select jt.id, jt.nama, jt.mstatus, jt.unit, jt.kode, jt.kode_voucher, jt.jurnal_manual, jtf.det_fitur_id from jurnal_trans_fitur jtf
                    left join
                        jurnal_trans jt
                        on
                            jt.id = jtf.id_header
                    group by
                        jt.id, jt.nama, jt.mstatus, jt.unit, jt.kode, jt.kode_voucher, jt.jurnal_manual, jtf.det_fitur_id
                ) jt1
                right join
                    (select max(id) as id, kode from jurnal_trans group by kode) jt2
                    on
                        jt1.id = jt2.id
                where
                	jt1.id is not null
            ) jt
            left join
                detail_fitur df
                on
                    jt.det_fitur_id = df.id_detfitur
            where
                df.path_detfitur = '".substr($this->url, 1)."'
            order by
                jt.nama asc
        ";
        // cetak_r( $sql, 1 );
        $d_supl = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_supl->count() > 0 ) {
            $data = $d_supl->toArray();
        }

        return $data;
    }

    public function getDetJurnalTrans() {
        $search = $this->input->get('search');
        $type = $this->input->get('type');
        $jurnal_trans = $this->input->get('jurnal_trans');

        $sql_jt = "";
        if ( !empty($search) && !empty($type) ) {
            $sql_jt = "where UPPER(REPLACE(CONVERT(varchar, kpd.tgl_bayar, 103), '-', '/')+' | '+td.no_sj) like '%".$search."%'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                djt.kode as id, 
                (djt.kode+' | '+djt.nama) as text, 
                djt.sumber as asal, 
                djt.sumber_coa as coa_asal, 
                djt.tujuan as tujuan, 
                djt.tujuan_coa as coa_tujuan 
            from (
                select djt1.* from det_jurnal_trans djt1
                right join
                    (select max(id) as id, kode from det_jurnal_trans group by kode) djt2
                    on
                        djt1.id = djt2.id
            ) djt
            left join
                jurnal_trans jt
                on
                    jt.id = djt.id_header
            where
                jt.kode = '".$jurnal_trans."'
                ".$sql_jt."
        ";
        $d_jt = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_jt->count() > 0 ) {
            $data = $d_jt->toArray();
        }
        
        echo json_encode($data);
    }

    public function getBarang() {
        $search = $this->input->get('search');
        $type = $this->input->get('type');
        $_jenis_dn = $this->input->get('jenis_dn');

        $jenis_dn = null;
        if ( stristr('doc', $_jenis_dn) !== false ) {
            $jenis_dn = 'doc';
        }

        if ( stristr('pkn', $_jenis_dn) !== false ) {
            $jenis_dn = 'pakan';
        }

        if ( stristr('ovk', $_jenis_dn) !== false ) {
            $jenis_dn = 'obat';
        }

        $sql_brg = "";
        if ( !empty($search) && !empty($type) ) {
            $sql_brg = "where UPPER(b1.kode+' | '+b1.nama) like '%".$search."%'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                b1.kode as id,
                UPPER(b1.kode+' | '+b1.nama) as text
            from barang b1
            right join
                (select max(id) as id, kode from barang group by kode) b2
                on
                    b1.id = b2.id
            where
                b1.tipe like '%".$jenis_dn."%'
                ".$sql_brg."
            order by
                b1.nama
        ";
        $d_brg = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_brg->count() > 0 ) {
            $data = $d_brg->toArray();
        }
        
        echo json_encode($data);
    }

    public function getData($id)
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                d.*,
                supl.nama as nama_supplier,
                gdg.nama as nama_gudang
            from dn d
            left join
                (
                    select plg1.* from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                        on
                            plg1.id = plg2.id
                ) supl
                on
                    supl.nomor = d.supplier
            left join
                gudang gdg
                on
                    gdg.id = d.gudang
            where
                d.id = ".$id."
            order by
                d.tanggal desc
        ";
        $d_dn = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_dn->count() > 0 ) {
            $data = $d_dn->toArray()[0];

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    dd.*,
                    REPLACE(CONVERT(varchar, d_sj.tgl_sj, 103), '-', '/') as tgl_sj,
                    brg.nama as nama_brg
                from dn_det dd
                left join
                    (
                        select td1.no_sj, td1.datang as tgl_sj from terima_doc td1
                        right join
                            (select max(id) as id, no_order from terima_doc group by no_order) td2
                            on
                                td1.id = td2.id

                        union all

                        select kppd.no_sj, kppd.tgl_sj from konfirmasi_pembayaran_pakan_det kppd
                        
                        union all
                        
                        select kpvd.no_sj, kpvd.tgl_sj from konfirmasi_pembayaran_voadip_det kpvd
                    ) d_sj
                    on
                        dd.no_sj = d_sj.no_sj
                left join
                    (
                        select 
                            b1.kode,
                            b1.nama
                        from barang b1
                        right join
                            (select max(id) as id, kode from barang group by kode) b2
                            on
                                b1.id = b2.id
                    ) brg
                    on
                        brg.kode = dd.kode_brg
                where
                    dd.id_header = ".$id."
            ";
            $d_dnd = $m_conf->hydrateRaw( $sql );

            if ( $d_dnd->count() > 0 ) {
                $d_dnd = $d_dnd->toArray();

                foreach ($d_dnd as $k_dnd => $v_dnd) {
                    $data['detail'][ $k_dnd ] = $v_dnd;
    
                    $m_conf = new \Model\Storage\Conf();
                    $sql = "
                        select
                            ddjt.*,
                            djt.kode as kode_det_jurnal_trans,
                            djt.nama as nama_det_jurnal_trans,
                            djt.sumber as asal,
                            djt.sumber_coa as coa_asal,
                            djt.tujuan as tujuan,
                            djt.tujuan_coa as coa_tujuan,
                            jt.kode as kode_jurnal_trans,
                            jt.nama as nama_jurnal_trans
                        from dn_det_jurnal_trans ddjt
                        left join
                            (
                                select djt1.* from det_jurnal_trans djt1
                                right join
                                    (select max(id) as id, kode from det_jurnal_trans group by kode) djt2
                                    on
                                        djt1.id = djt2.id
                            ) djt
                            on
                                ddjt.det_jurnal_trans_kode = djt.kode
                        left join
                            jurnal_trans jt
                            on
                                jt.id = djt.id_header
                        where
                            ddjt.id_header = ".$v_dnd['id']."
                    ";
                    $d_dndjt = $m_conf->hydrateRaw( $sql );
    
                    if ( $d_dndjt->count() > 0 ) {
                        $d_dndjt = $d_dndjt->toArray();
    
                        $data['kode_jurnal_trans'] = $d_dndjt[0]['kode_jurnal_trans'];
                        $data['nama_jurnal_trans'] = $d_dndjt[0]['nama_jurnal_trans'];
                        $data['detail'][ $k_dnd ]['det_jurnal_trans'] = $d_dndjt;                    
                    }
                }
            }
        }

        return $data;
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $sql_query_supplier = null;
        if (  stristr($params['supplier'], 'all') === FALSE  ) {
            $sql_query_supplier = "and d.supplier = '".$params['supplier']."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                d.*,
                supl.nama as nama_supplier
            from dn d
            left join
                (
                    select plg1.* from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                        on
                            plg1.id = plg2.id
                ) supl
                on
                    supl.nomor = d.supplier
            where
                d.tanggal between '".$params['start_date']."' and '".$params['end_date']."'
                ".$sql_query_supplier."
            order by
                d.tanggal desc
        ";
        $d_dn = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_dn->count() > 0 ) {
            $data = $d_dn->toArray();
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

        $content['jenis_dn'] = $this->jenis_dn;
        $content['jurnal_trans'] = $this->getJurnalTrans();
        $content['supplier'] = $this->getSupplier();
        $html = $this->load->view($this->path.'addForm', $content, TRUE);

        return $html;
    }

    public function viewForm($id)
    {
        $data = $this->getData($id);

        $content['akses'] = $this->akses;
        $content['jenis_dn'] = $this->jenis_dn;
        $content['data'] = $data;

        $html = $this->load->view($this->path.'viewForm', $content, TRUE);

        return $html;
    }

    public function editForm($id)
    {
        $data = $this->getData($id);

        $content['akses'] = $this->akses;
        $content['jenis_dn'] = $this->jenis_dn;
        $content['jurnal_trans'] = $this->getJurnalTrans();
        $content['supplier'] = $this->getSupplier();
        $content['data'] = $data;

        $html = $this->load->view($this->path.'editForm', $content, TRUE);

        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_dn = new \Model\Storage\Dn_model();
            $nomor = $m_dn->getNextNomor('DN/'.$params['jenis_dn']);

            $m_dn->nomor = $nomor;
            $m_dn->jenis_dn = $params['jenis_dn'];
            $m_dn->tanggal = $params['tgl_dn'];
            $m_dn->supplier = $params['supplier'];
            $m_dn->gudang = (isset($params['gudang']) && !empty($params['gudang'])) ? $params['gudang'] : null;
            $m_dn->ket_dn = $params['ket_dn'];
            $m_dn->tot_dn = $params['tot_dn'];
            $m_dn->save();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_dnd = new \Model\Storage\DnDet_model();
                $m_dnd->id_header = $m_dn->id;
                $m_dnd->no_sj = $v_det['no_sj'];
                $m_dnd->kode_brg = $v_det['kode_brg'];
                $m_dnd->jumlah = $v_det['jumlah'];
                $m_dnd->ket = $v_det['ket'];
                $m_dnd->nominal = $v_det['nominal'];
                $m_dnd->save();

                foreach ($v_det['det_jurnal_trans'] as $k_djt => $v_djt) {
                    $m_cdjt = new \Model\Storage\DnDetJurnalTrans_model();
                    $m_cdjt->id_header = $m_dnd->id;
                    $m_cdjt->det_jurnal_trans_kode = $v_djt;
                    $m_cdjt->save();
                }
            }

            $id = $m_dn->id;
            $id_old = null;
            $status_jurnal = 1;
            Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, $status_jurnal);

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_dn, $deskripsi_log);
            
            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $m_dn->id);
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

            $m_dnd = new \Model\Storage\DnDet_model();
            $d_dnd = $m_dnd->where('id_header', $id)->get();

            if ( $d_dnd->count() > 0 ) {
                $d_dnd = $d_dnd->toArray();

                foreach ($d_dnd as $k_dnd => $v_dnd) {
                    $m_cdjt = new \Model\Storage\DnDetJurnalTrans_model();
                    $m_cdjt->where('id_header', $v_dnd['id'])->delete();

                    $m_dnd = new \Model\Storage\DnDet_model();
                    $m_dnd->where('id', $v_dnd['id'])->delete();
                }
            }

            $m_dn = new \Model\Storage\Dn_model();
            $m_dn->where('id', $id)->update(
                array(
                    'jenis_dn' => $params['jenis_dn'],
                    'tanggal' => $params['tgl_dn'],
                    'supplier' => $params['supplier'],
                    'gudang' => (isset($params['gudang']) && !empty($params['gudang'])) ? $params['gudang'] : null,
                    'ket_dn' => $params['ket_dn'],
                    'tot_dn' => $params['tot_dn']
                )
            );

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_dnd = new \Model\Storage\DnDet_model();
                $m_dnd->id_header = $id;
                $m_dnd->no_sj = $v_det['no_sj'];
                $m_dnd->kode_brg = $v_det['kode_brg'];
                $m_dnd->jumlah = $v_det['jumlah'];
                $m_dnd->ket = $v_det['ket'];
                $m_dnd->nominal = $v_det['nominal'];
                $m_dnd->save();

                foreach ($v_det['det_jurnal_trans'] as $k_djt => $v_djt) {
                    $m_cdjt = new \Model\Storage\DnDetJurnalTrans_model();
                    $m_cdjt->id_header = $m_dnd->id;
                    $m_cdjt->det_jurnal_trans_kode = $v_djt;
                    $m_cdjt->save();
                }
            }

            $d_dn = $m_dn->where('id', $id)->first();

            $id_old = $id;
            $status_jurnal = 2;
            Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, $status_jurnal);

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_dn, $deskripsi_log);
            
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

            $m_dn = new \Model\Storage\Dn_model();
            $d_dn = $m_dn->where('id', $id)->first();

            $m_dnd = new \Model\Storage\DnDet_model();
            $d_dnd = $m_dnd->where('id_header', $id)->get();

            if ( $d_dnd->count() > 0 ) {
                $d_dnd = $d_dnd->toArray();

                foreach ($d_dnd as $k_dnd => $v_dnd) {
                    $m_cdjt = new \Model\Storage\DnDetJurnalTrans_model();
                    $m_cdjt->where('id_header', $v_dnd['id'])->delete();

                    $m_dnd = new \Model\Storage\DnDet_model();
                    $m_dnd->where('id', $v_dnd['id'])->delete();
                }
            }

            $m_dn->where('id', $id)->delete();

            $id_old = $id;
            $status_jurnal = 3;
            Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, $status_jurnal);

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_dn, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes() {
        $m_dn = new \Model\Storage\Dn_model();
        $nomor = $m_dn->getNextNomor('DN/DOC');

        cetak_r( $nomor, 1 );
    }
}