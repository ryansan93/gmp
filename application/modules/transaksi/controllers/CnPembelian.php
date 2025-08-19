<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CnPembelian extends Public_Controller {

    private $path = 'transaksi/cn_pembelian/';
    private $jenis_cn = array(
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
                "assets/transaksi/cn_pembelian/js/cn-pembelian.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/cn_pembelian/css/cn-pembelian.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->akses;

            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();

            // Load Indexx
            $data['title_menu'] = 'Credit Note Pembelian';
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
        $_jenis_cn = $this->input->get('jenis_cn');

        $jenis_cn = null;
        if ( stristr('pkn', $_jenis_cn) !== false ) {
            $jenis_cn = 'PAKAN';
        }

        if ( stristr('ovk', $_jenis_cn) !== false ) {
            $jenis_cn = 'OBAT';
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
                gdg.jenis = '".$jenis_cn."'
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
        $jenis_cn = $this->input->get('jenis_cn');

        $d_inv = null;

        if ( stristr('doc', $jenis_cn) !== false ) {
            $sql_inv = "";
            if ( !empty($search) && !empty($type) ) {
                $sql_inv = "and UPPER(REPLACE(CONVERT(varchar, kpd.tgl_bayar, 103), '-', '/')+' | '+td.no_sj) like '%".$search."%'";
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

        if ( stristr('pkn', $jenis_cn) !== false ) {
            $sql_inv = "";
            if ( !empty($search) && !empty($type) ) {
                $sql_inv = "and UPPER(REPLACE(CONVERT(varchar, kppd.tgl_sj, 103), '-', '/')+' | '+kppd.no_sj) like '%".$search."%'";
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

        if ( stristr('ovk', $jenis_cn) !== false ) {
            $sql_inv = "";
            if ( !empty($search) && !empty($type) ) {
                $sql_inv = "and UPPER(REPLACE(CONVERT(varchar, kpv.tgl_bayar, 103), '-', '/')+' | '+kpvd.no_sj) like '%".$search."%'";
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
            $sql_jt = "and UPPER(djt.kode+' | '+djt.nama) like '%".$search."%'";
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
        $_jenis_cn = $this->input->get('jenis_cn');

        $jenis_cn = null;
        if ( stristr('doc', $_jenis_cn) !== false ) {
            $jenis_cn = 'doc';
        }

        if ( stristr('pkn', $_jenis_cn) !== false ) {
            $jenis_cn = 'pakan';
        }

        if ( stristr('ovk', $_jenis_cn) !== false ) {
            $jenis_cn = 'obat';
        }

        $sql_brg = "";
        if ( !empty($search) && !empty($type) ) {
            $sql_brg = "and UPPER(b1.kode+' | '+b1.nama) like '%".$search."%'";
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
                b1.tipe = '".$jenis_cn."'
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
                c.*,
                supl.nama as nama_supplier,
                gdg.nama as nama_gudang,
                jt.kode as kode_jurnal_trans,
                jt.nama as nama_jurnal_trans
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
            left join
                gudang gdg
                on
                    gdg.id = c.gudang
            left join
                (
                    select jt1.* from jurnal_trans jt1
                    right join
                        (select max(id) as id, kode from jurnal_trans group by kode) jt2
                        on
                            jt1.id = jt2.id
                ) jt
                on
                    jt.kode = c.jurnal_trans_kode
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
                    REPLACE(CONVERT(varchar, d_sj.tgl_sj, 103), '-', '/') as tgl_sj,
                    brg.nama as nama_brg
                from cn_det cd
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
                        cd.no_sj = d_sj.no_sj
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
                        brg.kode = cd.kode_brg
                where
                    cd.id_header = ".$id."
            ";
            $d_cnd = $m_conf->hydrateRaw( $sql );

            if ( $d_cnd->count() > 0 ) {
                $d_cnd = $d_cnd->toArray();

                foreach ($d_cnd as $k_cnd => $v_cnd) {
                    $data['detail'][ $k_cnd ] = $v_cnd;
    
                    // $m_conf = new \Model\Storage\Conf();
                    // $sql = "
                    //     select
                    //         cdjt.*,
                    //         djt.kode as kode_det_jurnal_trans,
                    //         djt.nama as nama_det_jurnal_trans,
                    //         djt.sumber as asal,
                    //         djt.sumber_coa as coa_asal,
                    //         djt.tujuan as tujuan,
                    //         djt.tujuan_coa as coa_tujuan,
                    //         jt.kode as kode_jurnal_trans,
                    //         jt.nama as nama_jurnal_trans
                    //     from cn_det_jurnal_trans cdjt
                    //     left join
                    //         (
                    //             select djt1.* from det_jurnal_trans djt1
                    //             right join
                    //                 (select max(id) as id, kode from det_jurnal_trans group by kode) djt2
                    //                 on
                    //                     djt1.id = djt2.id
                    //         ) djt
                    //         on
                    //             cdjt.det_jurnal_trans_kode = djt.kode
                    //     left join
                    //         jurnal_trans jt
                    //         on
                    //             jt.id = djt.id_header
                    //     where
                    //         cdjt.id_header = ".$v_cnd['id']."
                    // ";
                    // $d_cndjt = $m_conf->hydrateRaw( $sql );
    
                    // if ( $d_cndjt->count() > 0 ) {
                    //     $d_cndjt = $d_cndjt->toArray();
    
                    //     $data['kode_jurnal_trans'] = $d_cndjt[0]['kode_jurnal_trans'];
                    //     $data['nama_jurnal_trans'] = $d_cndjt[0]['nama_jurnal_trans'];
                    //     $data['detail'][ $k_cnd ]['det_jurnal_trans'] = $d_cndjt;                    
                    // }
                }
            }
        }

        return $data;
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $sql_jenis_cn = "and (";
        $jml_jenis = count($this->jenis_cn);
        $idx = 1;
        foreach ($this->jenis_cn as $k_jc => $v_jc) {
            $sql_jenis_cn .= "c.nomor like '%".$k_jc."%'";
            if ( $idx < $jml_jenis ) {
                $sql_jenis_cn .= " or ";
            }

            $idx++;
        }
        $sql_jenis_cn .= ")";

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
                c.tanggal between '".$params['start_date']."' and '".$params['end_date']."'
                ".$sql_jenis_cn."
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

        $content['jenis_cn'] = $this->jenis_cn;
        $content['jurnal_trans'] = $this->getJurnalTrans();
        $content['supplier'] = $this->getSupplier();
        $html = $this->load->view($this->path.'addForm', $content, TRUE);

        return $html;
    }

    public function viewForm($id)
    {
        $data = $this->getData($id);

        $content['akses'] = $this->akses;
        $content['jenis_cn'] = $this->jenis_cn;
        $content['data'] = $data;

        $html = $this->load->view($this->path.'viewForm', $content, TRUE);

        return $html;
    }

    public function editForm($id)
    {
        $data = $this->getData($id);

        $content['akses'] = $this->akses;
        $content['jenis_cn'] = $this->jenis_cn;
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
            $m_jt = new \Model\Storage\JurnalTrans_model();
            $d_jt = $m_jt->where('kode', $params['jurnal_trans'])->orderBy('id', 'desc')->first();

            $kode_voucher = $d_jt->kode_voucher;

            $m_cn = new \Model\Storage\Cn_model();
            // $nomor = $m_cn->getNextNomor('CN/'.$params['jenis_cn']);
            $nomor = $m_cn->getNextNomor($kode_voucher);

            $m_cn->nomor = $nomor;
            // $m_cn->jenis_cn = $params['jenis_cn'];
            $m_cn->tanggal = $params['tgl_cn'];
            $m_cn->supplier = $params['supplier'];
            $m_cn->gudang = (isset($params['gudang']) && !empty($params['gudang'])) ? $params['gudang'] : null;
            $m_cn->ket_cn = $params['ket_cn'];
            $m_cn->tot_cn = $params['tot_cn'];
            $m_cn->jurnal_trans_kode = $params['jurnal_trans'];
            $m_cn->save();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_cnd = new \Model\Storage\CnDet_model();
                $m_cnd->id_header = $m_cn->id;
                $m_cnd->no_sj = $v_det['no_sj'];
                $m_cnd->kode_brg = $v_det['kode_brg'];
                $m_cnd->jumlah = $v_det['jumlah'];
                $m_cnd->ket = $v_det['ket'];
                $m_cnd->nominal = $v_det['nominal'];
                $m_cnd->save();

                // foreach ($v_det['det_jurnal_trans'] as $k_djt => $v_djt) {
                //     $m_cdjt = new \Model\Storage\CnDetJurnalTrans_model();
                //     $m_cdjt->id_header = $m_cnd->id;
                //     $m_cdjt->det_jurnal_trans_kode = $v_djt;
                //     $m_cdjt->save();
                // }
            }

            $id = $m_cn->id;
            $id_old = null;
            $status_jurnal = 1;
            Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, $status_jurnal);

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

            $m_cnd = new \Model\Storage\CnDet_model();
            $d_cnd = $m_cnd->where('id_header', $id)->get();

            if ( $d_cnd->count() > 0 ) {
                $d_cnd = $d_cnd->toArray();

                foreach ($d_cnd as $k_cnd => $v_cnd) {
                    $m_cdjt = new \Model\Storage\CnDetJurnalTrans_model();
                    $m_cdjt->where('id_header', $v_cnd['id'])->delete();

                    $m_cnd = new \Model\Storage\CnDet_model();
                    $m_cnd->where('id', $v_cnd['id'])->delete();
                }
            }

            $m_cn = new \Model\Storage\Cn_model();
            $d_cn = $m_cn->where('id', $id)->first();

            $m_jt = new \Model\Storage\JurnalTrans_model();
            $d_jt_old = $m_jt->where('kode', $d_cn->jurnal_trans_kode)->orderBy('id', 'desc')->first();
            $d_jt_new = $m_jt->where('kode', $params['jurnal_trans'])->orderBy('id', 'desc')->first();

            $nomor = $d_cn->nomor;
            if ( $d_jt_old->kode <> $d_jt_new->kode ) {
                $kode_voucher = $d_jt_new->kode_voucher;
    
                $m_cn = new \Model\Storage\Cn_model();
                // $nomor = $m_cn->getNextNomor('CN/'.$params['jenis_cn']);
                $nomor = $m_cn->getNextNomor($kode_voucher);
            }

            $m_cn = new \Model\Storage\Cn_model();
            $m_cn->where('id', $id)->update(
                array(
                    'nomor' => $nomor,
                    // 'jenis_cn' => $params['jenis_cn'],
                    'tanggal' => $params['tgl_cn'],
                    'supplier' => $params['supplier'],
                    'gudang' => (isset($params['gudang']) && !empty($params['gudang'])) ? $params['gudang'] : null,
                    'ket_cn' => $params['ket_cn'],
                    'tot_cn' => $params['tot_cn'],
                    'jurnal_trans_kode' => $params['jurnal_trans']
                )
            );

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_cnd = new \Model\Storage\CnDet_model();
                $m_cnd->id_header = $id;
                $m_cnd->no_sj = $v_det['no_sj'];
                $m_cnd->kode_brg = $v_det['kode_brg'];
                $m_cnd->jumlah = $v_det['jumlah'];
                $m_cnd->ket = $v_det['ket'];
                $m_cnd->nominal = $v_det['nominal'];
                $m_cnd->save();

                // foreach ($v_det['det_jurnal_trans'] as $k_djt => $v_djt) {
                //     $m_cdjt = new \Model\Storage\CnDetJurnalTrans_model();
                //     $m_cdjt->id_header = $m_cnd->id;
                //     $m_cdjt->det_jurnal_trans_kode = $v_djt;
                //     $m_cdjt->save();
                // }
            }

            $d_cn = $m_cn->where('id', $id)->first();

            $id_old = $id;
            $status_jurnal = 2;
            Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, $status_jurnal);

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_cn, $deskripsi_log);
            
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

            $m_cnd = new \Model\Storage\CnDet_model();
            $d_cnd = $m_cnd->where('id_header', $id)->get();

            if ( $d_cnd->count() > 0 ) {
                $d_cnd = $d_cnd->toArray();

                foreach ($d_cnd as $k_cnd => $v_cnd) {
                    $m_cdjt = new \Model\Storage\CnDetJurnalTrans_model();
                    $m_cdjt->where('id_header', $v_cnd['id'])->delete();

                    $m_cnd = new \Model\Storage\CnDet_model();
                    $m_cnd->where('id', $v_cnd['id'])->delete();
                }
            }

            $m_cn->where('id', $id)->delete();

            $id_old = $id;
            $status_jurnal = 3;
            Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, $status_jurnal);

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
        $nomor = $m_cn->getNextNomor('CN/DOC');

        cetak_r( $nomor, 1 );
    }
}