<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CnPenjualan extends Public_Controller {

    private $path = 'transaksi/cn_penjualan/';
    private $jenis_cn = array(
        'LB' => 'LB',
        // 'PR' => 'PERALATAN',
        'RHPP' => 'RHPP'
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
                "assets/transaksi/cn_penjualan/js/cn-penjualan.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/cn_penjualan/css/cn-penjualan.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->akses;

            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();

            // Load Indexx
            $data['title_menu'] = 'Credit Note Penjualan';
            $data['view'] = $this->load->view($this->path.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getPelanggan()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select plg1.* from pelanggan plg1
            right join
                (select max(id) as id, nomor from pelanggan group by nomor) plg2
                on
                    plg1.id = plg2.id
            where
                plg1.tipe <> 'supplier' and
                plg1.jenis <> 'ekspedisi'
            order by
                plg1.nama asc
        ";
        $d_plg = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_plg->count() > 0 ) {
            $data = $d_plg->toArray();
        }

        return $data;
    }

    public function getMitra()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select mtr1.* from mitra mtr1
            right join
                (select max(id) as id, nomor from mitra group by nomor) mtr2
                on
                    mtr1.id = mtr2.id
            order by
                mtr1.nama asc
        ";
        $d_mtr = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_mtr->count() > 0 ) {
            $data = $d_mtr->toArray();
        }

        return $data;
    }

    public function getNoSj() {
        $search = $this->input->get('search');
        $type = $this->input->get('type');
        $pelanggan = $this->input->get('pelanggan');
        $mitra = $this->input->get('mitra');
        $jenis_cn = $this->input->get('jenis_cn');

        $d_inv = null;

        if ( stristr('pr', $jenis_cn) !== false ) {
            $sql_inv = "";
            if ( !empty($search) && !empty($type) ) {
                $sql_inv = "where UPPER(REPLACE(CONVERT(varchar, pp.tanggal, 103), '-', '/')+' | '+pp.no_sj) like '%".$search."%'";
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select pp.no_sj as id, REPLACE(CONVERT(varchar, pp.tanggal, 103), '-', '/')+' | '+pp.no_sj as text from penjualan_peralatan pp
                left join
                    (
                        select mtr1.* from mitra mtr1
                        right join
                            (select max(id) as id, nomor from mitra group by nomor) mtr2
                            on
                                mtr1.id = mtr2.id
                    ) m
                    on
                        m.nomor = pp.mitra
                where
                    pp.mitra = '".$mitra."'
                    ".$sql_inv."
            ";
            $d_inv = $m_conf->hydrateRaw($sql);
        }

        if ( stristr('lb', $jenis_cn) !== false ) {
            $sql_inv = "";
            if ( !empty($search) && !empty($type) ) {
                $sql_inv = "where UPPER(REPLACE(CONVERT(varchar, drs.tgl_sj, 103), '-', '/')+' | '+drs.no_sj) like '%".$search."%'";
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select drs.no_sj as id, REPLACE(CONVERT(varchar, drs.tgl_sj, 103), '-', '/')+' | '+drs.no_sj as text 
                from
                    (
                        select rs.tgl_panen as tgl_sj, drs.no_pelanggan, drs.no_sj 
                        from det_real_sj drs
                        left join
                            real_sj rs 
                            on
                                drs.id_header = rs.id
                        group by 
                            rs.tgl_panen, drs.no_pelanggan, drs.no_sj
                    ) drs
                left join
                    (
                        select plg1.* from pelanggan plg1
                        right join
                            (select max(id) as id, nomor from pelanggan group by nomor) plg2
                            on
                                plg1.id = plg2.id
                        where
                            plg1.tipe <> 'supplier' and
                            plg1.jenis <> 'ekspedisi'
                    ) plg
                    on
                        plg.nomor = drs.no_pelanggan
                where
                    drs.no_pelanggan = '".$pelanggan."'
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
            $sql_jt = "where UPPER(djt.kode+' | '+djt.nama) like '%".$search."%'";
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
        if ( stristr('pr', $_jenis_cn) !== false ) {
            $jenis_cn = 'peralatan';
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
                plg.nama as nama_pelanggan,
                m.nama as nama_mitra
            from cn c
            left join
                (
                    select plg1.* from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe <> 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                        on
                            plg1.id = plg2.id
                ) plg
                on
                    plg.nomor = c.pelanggan
            left join
                (
                    select mtr1.* from mitra mtr1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) mtr2
                        on
                            mtr1.id = mtr2.id
                ) m
                on
                    m.nomor = c.mitra
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
                        select pp.no_sj, pp.tanggal as tgl_sj from penjualan_peralatan pp
                        
                        union all
                        
                        select drs.no_sj, rs.tgl_panen as tgl_sj
                        from det_real_sj drs
                        left join
                            real_sj rs 
                            on
                                drs.id_header = rs.id
                        group by 
                            drs.no_sj, rs.tgl_panen
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
    
                    $m_conf = new \Model\Storage\Conf();
                    $sql = "
                        select
                            cdjt.*,
                            djt.kode as kode_det_jurnal_trans,
                            djt.nama as nama_det_jurnal_trans,
                            djt.sumber as asal,
                            djt.sumber_coa as coa_asal,
                            djt.tujuan as tujuan,
                            djt.tujuan_coa as coa_tujuan,
                            jt.kode as kode_jurnal_trans,
                            jt.nama as nama_jurnal_trans
                        from cn_det_jurnal_trans cdjt
                        left join
                            (
                                select djt1.* from det_jurnal_trans djt1
                                right join
                                    (select max(id) as id, kode from det_jurnal_trans group by kode) djt2
                                    on
                                        djt1.id = djt2.id
                            ) djt
                            on
                                cdjt.det_jurnal_trans_kode = djt.kode
                        left join
                            jurnal_trans jt
                            on
                                jt.id = djt.id_header
                        where
                            cdjt.id_header = ".$v_cnd['id']."
                    ";
                    $d_cndjt = $m_conf->hydrateRaw( $sql );
    
                    if ( $d_cndjt->count() > 0 ) {
                        $d_cndjt = $d_cndjt->toArray();
    
                        $data['kode_jurnal_trans'] = $d_cndjt[0]['kode_jurnal_trans'];
                        $data['nama_jurnal_trans'] = $d_cndjt[0]['nama_jurnal_trans'];
                        $data['detail'][ $k_cnd ]['det_jurnal_trans'] = $d_cndjt;                    
                    }
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

        $sql_query_pelanggan = null;
        if (  stristr($params['pelanggan'], 'all') === FALSE  ) {
            $sql_query_pelanggan = "and c.pelanggan = '".$params['pelanggan']."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                c.*,
                plg.nama as nama_pelanggan
            from cn c
            left join
                (
                    select plg1.* from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe <> 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                        on
                            plg1.id = plg2.id
                ) plg
                on
                    plg.nomor = c.pelanggan
            where
                c.tanggal between '".$params['start_date']."' and '".$params['end_date']."'
                ".$sql_jenis_cn."
                ".$sql_query_pelanggan."
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

        $content['pelanggan'] = $this->getPelanggan();
        $content['mitra'] = $this->getMitra();
        $html = $this->load->view($this->path.'riwayat', $content, TRUE);

        return $html;
    }

    public function addForm()
    {
        $html = null;

        $content['jenis_cn'] = $this->jenis_cn;
        $content['jurnal_trans'] = $this->getJurnalTrans();
        $content['pelanggan'] = $this->getPelanggan();
        $content['mitra'] = $this->getMitra();
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
        $content['pelanggan'] = $this->getPelanggan();
        $content['mitra'] = $this->getMitra();
        $content['data'] = $data;

        $html = $this->load->view($this->path.'editForm', $content, TRUE);

        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_cn = new \Model\Storage\Cn_model();
            $nomor = $m_cn->getNextNomor('CN/'.$params['jenis_cn']);

            $m_cn->nomor = $nomor;
            $m_cn->jenis_cn = $params['jenis_cn'];
            $m_cn->tanggal = $params['tgl_cn'];
            $m_cn->pelanggan = $params['pelanggan'];
            $m_cn->mitra = (isset($params['mitra']) && !empty($params['mitra'])) ? $params['mitra'] : null;
            $m_cn->ket_cn = $params['ket_cn'];
            $m_cn->tot_cn = $params['tot_cn'];
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

                foreach ($v_det['det_jurnal_trans'] as $k_djt => $v_djt) {
                    $m_cdjt = new \Model\Storage\CnDetJurnalTrans_model();
                    $m_cdjt->id_header = $m_cnd->id;
                    $m_cdjt->det_jurnal_trans_kode = $v_djt;
                    $m_cdjt->save();
                }
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
            $m_cn->where('id', $id)->update(
                array(
                    'jenis_cn' => $params['jenis_cn'],
                    'tanggal' => $params['tgl_cn'],
                    'pelanggan' => $params['pelanggan'],
                    'mitra' => (isset($params['mitra']) && !empty($params['mitra'])) ? $params['mitra'] : null,
                    'ket_cn' => $params['ket_cn'],
                    'tot_cn' => $params['tot_cn']
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

                foreach ($v_det['det_jurnal_trans'] as $k_djt => $v_djt) {
                    $m_cdjt = new \Model\Storage\CnDetJurnalTrans_model();
                    $m_cdjt->id_header = $m_cnd->id;
                    $m_cdjt->det_jurnal_trans_kode = $v_djt;
                    $m_cdjt->save();
                }
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