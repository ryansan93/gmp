<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CreditNotePosting extends Public_Controller {

    private $path = 'transaksi/credit_note_posting/';
    private $jenis_cn = array(
        'DOC' => array('nama' => 'DOC', 'jenis' => 'supplier'),
        'PKN' => array('nama' => 'PAKAN', 'jenis' => 'supplier'),
        'OVK' => array('nama' => 'OVK', 'jenis' => 'supplier'),
        'RHPP' => array('nama' => 'RHPP', 'jenis' => 'mitra'),
        'OA' => array('nama' => 'OA', 'jenis' => 'ekspedisi'),
        'BKL' => array('nama' => 'BAKUL', 'jenis' => 'bakul'),
        'NS' => array('nama' => 'NON SAPRONAK', 'jenis' => 'supplier')
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
                "assets/transaksi/credit_note_posting/js/credit-note-posting.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/credit_note/_postingcss/credit-note-posting.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->akses;

            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();

            // Load Indexx
            $data['title_menu'] = 'Pemakaian Credit Note';
            $data['view'] = $this->load->view($this->path.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getCn() {
        $search = $this->input->get('search');
        $type = $this->input->get('type');
        $_jenis_cn = $this->input->get('jenis_cn');
        $id = $this->input->get('id');

        $jenis_cn = $_jenis_cn;

        $sql_cn = "";
        if ( !empty($search) && !empty($type) ) {
            $sql_cn = "where c.text like '%".$search."%'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select c.* from (
                select
                    c.id as id,
                    REPLACE(c.tanggal, '-', '/')+' | '+c.no_dok as text,
                    (c.tot_cn - sum(isnull(_cpd.tot_pakai, 0))) as tot_cn,
                    c.tanggal
                from cn c
                left join
                    (
                        select 
                            cp.id, 
                            cp.tanggal, 
                            cp.no_cn, 
                            sum(cpd.pakai) as tot_pakai
                        from cn_post_det cpd
                        left join
                            cn_post cp 
                            on
                                cpd.id_header = cp.id
                        where
                            cp.id <> '".$id."'
                        group by
                            cp.id,
                            cp.tanggal,
                            cp.no_cn
                    ) _cpd
                    on
                        _cpd.no_cn = c.id
                where
                    c.jenis_cn like '".$jenis_cn."'
                group by
                    c.id,
                    c.no_dok,
                    c.tot_cn,
                    c.tanggal
                having
                    (c.tot_cn - sum(isnull(_cpd.tot_pakai, 0))) > 0
            ) c
            ".$sql_cn."
            order by
                c.tanggal asc
        ";
        // cetak_r( $sql, 1 );
        $d_cn = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_cn->count() > 0 ) {
            $data = $d_cn->toArray();
        }
        
        echo json_encode($data);
    }

    public function getSj() {
        $search = $this->input->get('search');
        $type = $this->input->get('type');
        $_jenis_cn = $this->input->get('jenis_cn');
        $id = $this->input->get('id');

        $jenis_cn = $_jenis_cn;

        $sql_cn = "";
        if ( !empty($search) && !empty($type) ) {
            $sql_cn = "and REPLACE(sj.tgl_sj, '-', '/')+' | '+sj.no_sj like '%".$search."%'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                sj.id as id,
                REPLACE(sj.tgl_sj, '-', '/')+' | '+sj.no_sj as text,
                sj.total as tagihan,
                (sj.total - (sum(isnull(cpd.tot_pakai, 0)) + sum(isnull(rpd.tot_tf, 0)))) as sisa_tagihan
            from (
                select
                    kpdd.tgl_order as tgl_sj,
                    kpdd.no_order, 
                    td.no_sj, 
                    kpdd.total, 
                    'DOC' as jenis_cn,
                    kpd.nomor as id
                from konfirmasi_pembayaran_doc_det kpdd
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
                    td.no_sj is not null
                    and NOT EXISTS (select * from realisasi_pembayaran_det where no_bayar = kpd.nomor)

                union all

                select
                    kppd.tgl_sj,
                    kppd.no_order, 
                    kppd.no_sj, 
                    kppd.total, 
                    'PKN' as jenis_cn,
                    kpp.nomor as id
                from konfirmasi_pembayaran_pakan_det kppd 
                left join
                    konfirmasi_pembayaran_pakan kpp 
                    on
                        kppd.id_header = kpp.id
            ) sj
            left join
                (
                    select 
                        cp.id, 
                        cp.tanggal, 
                        cpd.nomor, 
                        sum(cpd.pakai) as tot_pakai
                    from cn_post_det cpd
                    left join
                        cn_post cp 
                        on
                            cpd.id_header = cp.id
                    where
                        cp.id <> '".$id."'
                    group by
                        cp.id,
                        cp.tanggal,
                        cpd.nomor
                ) cpd
                on
                    sj.id = cpd.nomor
            left join
                (
                    select
                        rp.tgl_bayar as tanggal,
                        rpd.no_bayar as nomor, 
                        sum(rpd.transfer) as tot_tf 
                    from realisasi_pembayaran_det rpd
                    left join
                        realisasi_pembayaran rp 
                        on
                            rpd.id_header = rp.id
                    group by 
                        rp.tgl_bayar,
                        rpd.no_bayar
                ) rpd
                on
                    sj.id = rpd.nomor
            /*
            left join
                (select nomor as id, sum(pakai) as tot_cn from cn_post_det group by nomor) cpd
                on
                    cpd.id = sj.id
            left join
                (select no_bayar as id, sum(transfer) as tot_tf from realisasi_pembayaran_det group by no_bayar) rpd
                on
                    rpd.id = sj.id
            */
            where
                sj.jenis_cn like '".$jenis_cn."'
                ".$sql_cn."
            group by
                sj.id,
                sj.tgl_sj,
                sj.no_sj,
                sj.total
            having
				(sj.total - (sum(isnull(cpd.tot_pakai, 0)) + sum(isnull(rpd.tot_tf, 0)))) > 0
            order by
                sj.tgl_sj asc
        ";
        $d_cn = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_cn->count() > 0 ) {
            $data = $d_cn->toArray();
        }
        
        echo json_encode($data);
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $jenis = ($params['jenis_cn'] == 'all') ? null : $params['jenis_cn'];

        $m_cn = new \Model\Storage\CnPost_model();
        $data = $m_cn->getData(null, $start_date, $end_date, null, $jenis);

        // cetak_r( $data, 1 );

        $content['data'] = $data;
        $content['jenis_cn'] = $this->jenis_cn;
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

    public function riwayat() {
        $html = null;

        $m_supl = new \Model\Storage\Supplier_model();
        $m_plg = new \Model\Storage\Pelanggan_model();
        $m_eks = new \Model\Storage\Ekspedisi_model();
        $m_mitra = new \Model\Storage\Mitra_model();

        $content['supplier'] = $m_supl->getDataSupplier(0);
        $content['pelanggan'] = $m_plg->getDataPelanggan(0);
        $content['ekspedisi'] = $m_eks->getDataEskpedisi(0);
        $content['mitra'] = $m_mitra->getDataMitra(0);
        $content['jenis_cn'] = $this->jenis_cn;
        $content['akses'] = $this->akses;
        $html = $this->load->view($this->path.'riwayat', $content, TRUE);

        return $html;
    }

    public function addForm() {
        $html = null;

        $m_supl = new \Model\Storage\Supplier_model();
        $m_plg = new \Model\Storage\Pelanggan_model();
        $m_eks = new \Model\Storage\Ekspedisi_model();
        $m_mitra = new \Model\Storage\Mitra_model();

        $content['supplier'] = $m_supl->getDataSupplier(0);
        $content['pelanggan'] = $m_plg->getDataPelanggan(0);
        $content['ekspedisi'] = $m_eks->getDataEskpedisi(0);
        $content['mitra'] = $m_mitra->getDataMitra(0);
        $content['jenis_cn'] = $this->jenis_cn;
        $content['akses'] = $this->akses;
        $html = $this->load->view($this->path.'addForm', $content, TRUE);

        return $html;
    }

    public function editForm($id) {
        $html = null;

        $m_cn = new \Model\Storage\CnPost_model();
        $data = $m_cn->getData($id)[0];

        $m_cpd = new \Model\Storage\CnPostDet_model();
        $detail = $m_cpd->getData($id);

        $m_supl = new \Model\Storage\Supplier_model();
        $m_plg = new \Model\Storage\Pelanggan_model();
        $m_eks = new \Model\Storage\Ekspedisi_model();
        $m_mitra = new \Model\Storage\Mitra_model();

        $content['data'] = $data;
        $content['detail'] = $detail;
        $content['supplier'] = $m_supl->getDataSupplier(0);
        $content['pelanggan'] = $m_plg->getDataPelanggan(0);
        $content['ekspedisi'] = $m_eks->getDataEskpedisi(0);
        $content['mitra'] = $m_mitra->getDataMitra(0);
        $content['jenis_cn'] = $this->jenis_cn;
        $content['akses'] = $this->akses;
        $html = $this->load->view($this->path.'editForm', $content, TRUE);

        return $html;
    }

    public function viewForm($id) {
        $html = null;

        $m_cn = new \Model\Storage\CnPost_model();
        $data = $m_cn->getData($id)[0];

        $m_cpd = new \Model\Storage\CnPostDet_model();
        $detail = $m_cpd->getData($id);
        
        $content['data'] = $data;
        $content['detail'] = $detail;
        $content['jenis_cn'] = $this->jenis_cn;
        $content['akses'] = $this->akses;
        $html = $this->load->view($this->path.'viewForm', $content, TRUE);

        return $html;
    }

    public function save() {
        $params = $this->input->post('params');

        try {            
            $m_cn = new \Model\Storage\CnPost_model();
            $m_cn->tanggal = $params['tanggal'];
            $m_cn->jenis_cn = $params['jenis_cn'];
            $m_cn->no_cn = $params['no_cn'];
            $m_cn->tot_pakai = $params['tot_pakai'];
            $m_cn->save();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_cnd = new \Model\Storage\CnPostDet_model();
                $m_cnd->id_header = $m_cn->id;
                $m_cnd->nomor = $v_det['nomor'];
                $m_cnd->pakai = $v_det['pakai'];
                $m_cnd->save();
            }

            Modules::run( 'base/InsertJurnal/exec', $this->url, $m_cn->id, null, 1);

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_cn, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $m_cn->id);
            $this->result['message'] = 'Data pemakaian CN berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit() {
        $params = $this->input->post('params');

        try {            
            $m_cn = new \Model\Storage\CnPost_model();
            $m_cn->where('id', $params['id'])->update(
                array(
                    'tanggal' => $params['tanggal'],
                    'jenis_cn' => $params['jenis_cn'],
                    'no_cn' => $params['no_cn'],
                    'tot_pakai' => $params['tot_pakai'],
                )
            );

            $m_cnd = new \Model\Storage\CnPostDet_model();
            $m_cnd->where('id_header', $params['id'])->delete();
            foreach ($params['detail'] as $k_det => $v_det) {
                $m_cnd = new \Model\Storage\CnPostDet_model();
                $m_cnd->id_header = $params['id'];
                $m_cnd->nomor = $v_det['nomor'];
                $m_cnd->pakai = $v_det['pakai'];
                $m_cnd->save();
            }

            $m_cn = new \Model\Storage\CnPost_model();
            $d_cn = $m_cn->where('id', $params['id'])->first();

            Modules::run( 'base/InsertJurnal/exec', $this->url, $params['id'], $params['id'], 2);

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_cn, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $params['id']);
            $this->result['message'] = 'Data pemakaian CN berhasil di update.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete() {
        $params = $this->input->post('params');

        try {
            $m_cn = new \Model\Storage\CnPost_model();
            $d_cn = $m_cn->where('id', $params['id'])->first();

            $m_cnd = new \Model\Storage\CnPostDet_model();
            $m_cnd->where('id_header', $params['id'])->delete();
            
            $m_cn->where('id', $params['id'])->delete();

            Modules::run( 'base/InsertJurnal/exec', $this->url, $params['id'], $params['id'], 3);

            $deskripsi_log = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_cn, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data pemakaian CN berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from cn_post
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                Modules::run( 'base/InsertJurnal/exec', $this->url, $value['id'], $value['id'], 2);
            }
        }
    }
}