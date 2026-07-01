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
                    case
                        when c.no_dok is not null then
                            REPLACE(c.tanggal, '-', '/')+' | '+c.no_dok
                        else
                            REPLACE(c.tanggal, '-', '/')+' | '+c.nomor
                    end as text,
                    (c.tot_cn - sum(isnull(_cpd.tot_pakai, 0))) as tot_cn,
                    c.tanggal,
                    c.supplier
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
                    c.nomor,
                    c.no_dok,
                    c.tot_cn,
                    c.tanggal,
                    c.supplier
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
        $jenis_cn = $this->input->get('jenis_cn');
        $id = $this->input->get('id');
        $supplier = $this->input->get('supplier');

        // COA hutang per jenis (dipakai untuk netting mmitem/memorial).
        $acct_map = array(
            'DOC'  => '21180.200',
            'PKN'  => '21180.100',
            'OVK'  => '21180.300',
            'RHPP' => '21213',
            'OA'   => '21212',
        );

        /* 1) SUMBER TAGIHAN (SJ) per jenis -> kolom: id, tagihan, tgl_sj, no_sj, unit
              DOC/PKN/OVK/RHPP/OA diambil dari konfirmasi_pembayaran_*,
              BKL diambil dari det_real_sj_inv. */
        $sj_sub = null;
        switch ( $jenis_cn ) {
            case 'DOC':
                $sj_sub = "
                    select
                        kpd.nomor as id,
                        sum(kpdd.total) as tagihan,
                        min(kpd.tgl_bayar) as tgl_sj,
                        max(td.no_sj) as no_sj,
                        min(kpdd.kode_unit) as unit,
                        min(kpd.supplier) as supplier
                    from konfirmasi_pembayaran_doc_det kpdd
                    left join konfirmasi_pembayaran_doc kpd on kpdd.id_header = kpd.id
                    left join (
                        select td1.* from terima_doc td1
                        right join (select max(id) as id, no_order from terima_doc group by no_order) td2 on td1.id = td2.id
                    ) td on td.no_order = kpdd.no_order
                    group by kpd.nomor
                ";
                break;
            case 'PKN':
                $sj_sub = "
                    select
                        kpp.nomor as id,
                        sum(kppd.total) as tagihan,
                        min(kppd.tgl_sj) as tgl_sj,
                        max(kppd.no_sj) as no_sj,
                        min(kppd.kode_unit) as unit,
                        min(kpp.supplier) as supplier
                    from konfirmasi_pembayaran_pakan_det kppd
                    left join konfirmasi_pembayaran_pakan kpp on kppd.id_header = kpp.id
                    group by kpp.nomor
                ";
                break;
            case 'OVK':
                $sj_sub = "
                    select
                        kpv.nomor as id,
                        kpv.total as tagihan,
                        kpv.tgl_bayar as tgl_sj,
                        kpv.nomor as no_sj,
                        kpvd.unit as unit,
                        kpv.supplier as supplier
                    from konfirmasi_pembayaran_voadip kpv
                    left join (select id_header, min(kode_unit) as unit from konfirmasi_pembayaran_voadip_det group by id_header) kpvd on kpvd.id_header = kpv.id
                ";
                break;
            case 'RHPP':
                $sj_sub = "
                    select
                        kpp.nomor as id,
                        kpp.total as tagihan,
                        kpp.tgl_bayar as tgl_sj,
                        kpp.invoice as no_sj,
                        SUBSTRING(REPLACE(REPLACE(kpp.invoice, 'INV/RHPP/G/', ''), 'INV/RHPP/', ''), 1, 3) as unit,
                        kpp.mitra as supplier
                    from konfirmasi_pembayaran_peternak kpp
                ";
                break;
            case 'OA':
                $sj_sub = "
                    select
                        kpop.nomor as id,
                        kpop.total as tagihan,
                        kpop.tgl_bayar as tgl_sj,
                        kpop.nomor as no_sj,
                        oa_unit.unit as unit,
                        kpop.ekspedisi_id as supplier
                    from konfirmasi_pembayaran_oa_pakan kpop
                    left join (
                        select kpopd.id_header,
                            min(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) as unit
                        from konfirmasi_pembayaran_oa_pakan_det kpopd
                        left join kirim_pakan kp on kp.no_sj = kpopd.no_sj
                        group by kpopd.id_header
                    ) oa_unit on oa_unit.id_header = kpop.id
                ";
                break;
            case 'BKL':
                $sj_sub = "
                    select
                        drsi.no_inv as id,
                        drsi.total as tagihan,
                        rs.tgl_panen as tgl_sj,
                        drsi.no_sj as no_sj,
                        cast(SUBSTRING(drsi.no_inv, 5, 3) as varchar(5)) as unit,
                        drs.no_pelanggan as supplier
                    from det_real_sj_inv drsi
                    left join (select max(id_header) as id_header, no_sj, no_pelanggan from det_real_sj group by no_sj, no_pelanggan) drs on drs.no_sj = drsi.no_sj
                    left join real_sj rs on rs.id = drs.id_header
                ";
                break;
        }

        if ( empty($sj_sub) ) {
            echo json_encode(null);
            return;
        }

        /* 2) PEMBAYARAN (pay) per jenis -> kolom: nomor, tot
              DOC/PKN/OVK/RHPP = transfer realisasi_pembayaran; OA = transfer + PPh23;
              BKL = jumlah bayar dari det_pembayaran_pelanggan (cn/dn lama TIDAK ikut). */
        if ( $jenis_cn == 'BKL' ) {
            $pay_sub = "
                select dpp.no_inv as nomor, sum(dpp.tagihan - (dpp.penyesuaian + dpp.sisa_tagihan)) as tot
                from det_pembayaran_pelanggan dpp
                group by dpp.no_inv
            ";
        } else if ( $jenis_cn == 'OA' ) {
            $pay_sub = "
                select rpd.no_bayar as nomor, sum(rpd.transfer) + min(isnull(kpop.potongan_pph_23, 0)) as tot
                from realisasi_pembayaran_det rpd
                left join konfirmasi_pembayaran_oa_pakan kpop on kpop.nomor = rpd.no_bayar
                group by rpd.no_bayar
            ";
        } else {
            $pay_sub = "
                select rpd.no_bayar as nomor, sum(rpd.transfer) as tot
                from realisasi_pembayaran_det rpd
                group by rpd.no_bayar
            ";
        }

        /* 3) MEMORIAL (memo) per jenis -> kolom: nomor, tot
              Supplier: netting via COA hutang (coa_tujuan menurunkan hutang = bayar; coa_asal menaikkan).
              BKL: cocokkan no_invoice saja (selalu mengurangi sisa). */
        if ( $jenis_cn == 'BKL' ) {
            $memo_sub = "
                select mi.no_invoice as nomor, sum(mi.nilai) as tot
                from mmitem mi
                where mi.no_invoice is not null and mi.no_invoice <> ''
                group by mi.no_invoice
            ";
        } else {
            $acct = $acct_map[$jenis_cn];
            $memo_sub = "
                select mi.no_invoice as nomor,
                    sum(case when mi.coa_tujuan like '".$acct."%' then mi.nilai else 0 end)
                    - sum(case when mi.coa_asal like '".$acct."%' then mi.nilai else 0 end) as tot
                from mmitem mi
                where (mi.coa_asal like '".$acct."%' or mi.coa_tujuan like '".$acct."%')
                    and mi.no_invoice is not null and mi.no_invoice <> ''
                group by mi.no_invoice
            ";
        }

        $sql_search = "";
        if ( !empty($search) ) {
            $sql_search = " and (isnull(cast(sj.no_sj as varchar(100)), '') like '%".$search."%' or isnull(cast(sj.id as varchar(100)), '') like '%".$search."%' or isnull(sj.unit, '') like '%".$search."%') ";
        }

        // filter berdasarkan supplier/pelanggan dari CN yang dipilih
        $sql_supl = "";
        if ( !empty($supplier) && $supplier != 'all' ) {
            $sql_supl = " and sj.supplier = '".$supplier."' ";
        }

        // sisa = tagihan - (pakai CN + pembayaran + memorial). Alias tak bisa dipakai di WHERE, jadi diulang.
        $sisa_expr = "(sj.tagihan - (isnull(cpd.tot_pakai, 0) + isnull(pay.tot, 0) + isnull(memo.tot, 0)))";

        $m_conf = new \Model\Storage\Conf();
        // Batasi jumlah baris supaya modal ringan; SJ spesifik dicari via kolom search (server-side).
        $sql = "
            select top (200)
                sj.id as id,
                REPLACE(convert(varchar(10), sj.tgl_sj, 23), '-', '/') + ' | ' + isnull(sj.unit, '') + ' | ' + isnull(cast(sj.no_sj as varchar(100)), cast(sj.id as varchar(100))) as text,
                sj.tagihan as tagihan,
                ".$sisa_expr." as sisa_tagihan
            from ( ".$sj_sub." ) sj
            left join (
                select cpd.nomor, sum(cpd.pakai) as tot_pakai
                from cn_post_det cpd
                left join cn_post cp on cpd.id_header = cp.id
                where cp.id <> '".$id."'
                group by cpd.nomor
            ) cpd on cpd.nomor = sj.id
            left join ( ".$pay_sub." ) pay on pay.nomor = sj.id
            left join ( ".$memo_sub." ) memo on memo.nomor = sj.id
            where
                ".$sisa_expr." > 0
                ".$sql_search."
                ".$sql_supl."
            order by
                sj.tgl_sj asc,
                sj.no_sj asc
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

            $no_inv_list = array();
            foreach ($params['detail'] as $k_det => $v_det) {
                $m_cnd = new \Model\Storage\CnPostDet_model();
                $m_cnd->id_header = $m_cn->id;
                $m_cnd->nomor = $v_det['nomor'];
                $m_cnd->pakai = $v_det['pakai'];
                $m_cnd->no_sj = isset($v_det['no_sj']) ? $v_det['no_sj'] : null;
                $m_cnd->tagihan = isset($v_det['tagihan']) ? $v_det['tagihan'] : null;
                $m_cnd->sisa = isset($v_det['sisa']) ? $v_det['sisa'] : null;
                $m_cnd->save();

                $no_inv_list[] = $v_det['nomor'];
            }

            // jurnal cn_post (semua jenis)
            Modules::run( 'base/InsertJurnal/exec', $this->url, $m_cn->id, $m_cn->id, 2);

            // BKL: selain jurnal cn_post, update det_real_sj_inv (cn, pph, netto) + jurnal ulang RealisasiSJ
            if ( $params['jenis_cn'] == 'BKL' ) {
                $this->syncBakulInvoice( $no_inv_list );
            }

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
            // jenis & invoice lama (untuk sinkronisasi bakul saat jenis/detail berubah)
            $m_cn_old = new \Model\Storage\CnPost_model();
            $d_cn_old = $m_cn_old->where('id', $params['id'])->first();
            $jenis_old = $d_cn_old ? $d_cn_old->jenis_cn : null;

            $old_inv = array();
            $m_cnd_old = new \Model\Storage\CnPostDet_model();
            $d_old_det = $m_cnd_old->where('id_header', $params['id'])->get();
            if ( $d_old_det->count() > 0 ) {
                foreach ($d_old_det->toArray() as $row) { $old_inv[] = $row['nomor']; }
            }

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
            $new_inv = array();
            foreach ($params['detail'] as $k_det => $v_det) {
                $m_cnd = new \Model\Storage\CnPostDet_model();
                $m_cnd->id_header = $params['id'];
                $m_cnd->nomor = $v_det['nomor'];
                $m_cnd->pakai = $v_det['pakai'];
                $m_cnd->no_sj = isset($v_det['no_sj']) ? $v_det['no_sj'] : null;
                $m_cnd->tagihan = isset($v_det['tagihan']) ? $v_det['tagihan'] : null;
                $m_cnd->sisa = isset($v_det['sisa']) ? $v_det['sisa'] : null;
                $m_cnd->save();

                $new_inv[] = $v_det['nomor'];
            }

            $m_cn = new \Model\Storage\CnPost_model();
            $d_cn = $m_cn->where('id', $params['id'])->first();

            // jurnal cn_post (update) - semua jenis
            Modules::run( 'base/InsertJurnal/exec', $this->url, $params['id'], $params['id'], 2);

            // sinkron invoice bakul yang terpengaruh (lama + baru); recompute idempoten
            $affected = array();
            if ( $jenis_old == 'BKL' ) { $affected = array_merge($affected, $old_inv); }
            if ( $params['jenis_cn'] == 'BKL' ) { $affected = array_merge($affected, $new_inv); }
            if ( !empty($affected) ) { $this->syncBakulInvoice( $affected ); }

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
            $jenis = $d_cn ? $d_cn->jenis_cn : null;

            // tangkap invoice bakul sebelum dihapus (untuk recompute setelah hapus)
            $old_inv = array();
            $m_cnd = new \Model\Storage\CnPostDet_model();
            $d_old_det = $m_cnd->where('id_header', $params['id'])->get();
            if ( $d_old_det->count() > 0 ) {
                foreach ($d_old_det->toArray() as $row) { $old_inv[] = $row['nomor']; }
            }

            $m_cnd = new \Model\Storage\CnPostDet_model();
            $m_cnd->where('id_header', $params['id'])->delete();

            $m_cn->where('id', $params['id'])->delete();

            Modules::run( 'base/InsertJurnal/exec', $this->url, $params['id'], $params['id'], 3);

            // BKL: recompute det_real_sj_inv (cn berkurang) + jurnal ulang RealisasiSJ
            if ( $jenis == 'BKL' && !empty($old_inv) ) {
                $this->syncBakulInvoice( $old_inv );
            }

            $deskripsi_log = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_cn, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data pemakaian CN berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    /**
     * Sinkronisasi invoice bakul setelah perubahan pemakaian CN.
     * - Isi det_real_sj_inv.cn = SUM(cn_post_det.pakai BKL) per no_inv.
     * - Hitung ulang: pph = round((bruto - cn) * tarif%, 0); total(netto) = (bruto - cn) - pph.
     * - Jurnal ulang RealisasiSJ untuk header real_sj yang terpengaruh (action = update).
     *
     * @param array $no_inv_list daftar no_inv (cn_post_det.nomor untuk BKL)
     */
    private function syncBakulInvoice( $no_inv_list ) {
        // bersihkan & unik-kan
        $list = array();
        foreach ( (array) $no_inv_list as $v ) {
            $v = trim($v);
            if ( $v !== '' ) { $list[$v] = $v; }
        }
        if ( empty($list) ) { return; }

        $in = "'".implode("', '", array_map(function($x){ return str_replace("'", "''", $x); }, array_values($list)))."'";

        $DB = $this->load->database('default', TRUE);

        // 1) Recompute cn, pph, total(netto) di det_real_sj_inv
        $sql_update = "
            UPDATE drsi SET
                cn = isnull(c.cn, 0),
                pph = case when isnull(r.rate, 0) > 0 then round((drsi.bruto - isnull(c.cn, 0)) * (r.rate/100), 0) else 0 end,
                total = (drsi.bruto - isnull(c.cn, 0)) - (case when isnull(r.rate, 0) > 0 then round((drsi.bruto - isnull(c.cn, 0)) * (r.rate/100), 0) else 0 end)
            FROM det_real_sj_inv drsi
            LEFT JOIN (
                select cpd.nomor as no_inv, sum(cpd.pakai) as cn
                from cn_post_det cpd
                inner join cn_post cp on cpd.id_header = cp.id
                where cp.jenis_cn = 'BKL'
                group by cpd.nomor
            ) c on c.no_inv = drsi.no_inv
            LEFT JOIN (
                select drs.no_sj, min(tp.pph) as rate
                from det_real_sj drs
                left join (select plg1.* from pelanggan plg1 right join (select max(id) as id, nomor from pelanggan group by nomor) plg2 on plg1.id = plg2.id) plg on plg.nomor = drs.no_pelanggan
                left join tipe_pelanggan tp on tp.id = plg.tipe_plg
                group by drs.no_sj
            ) r on r.no_sj = drsi.no_sj
            WHERE drsi.no_inv IN ( ".$in." )
        ";
        $DB->query($sql_update);

        // 2) Jurnal ulang RealisasiSJ untuk header real_sj yang terpengaruh
        $m_conf = new \Model\Storage\Conf();
        $sql_hdr = "
            select distinct drs.id_header
            from det_real_sj_inv drsi
            inner join det_real_sj drs on drs.no_sj = drsi.no_sj
            where drsi.no_inv IN ( ".$in." )
        ";
        $d_hdr = $m_conf->hydrateRaw( $sql_hdr );
        if ( $d_hdr->count() > 0 ) {
            foreach ( $d_hdr->toArray() as $row ) {
                $id_rs = $row['id_header'];
                if ( !empty($id_rs) ) {
                    Modules::run( 'base/InsertJurnal/exec', '/transaksi/RealisasiSJ', $id_rs, $id_rs, 2 );
                }
            }
        }
    }

    public function tes() {
        // $m_conf = new \Model\Storage\Conf();
        // $sql = "
        //     select * from cn_post
        // ";
        // $d_conf = $m_conf->hydrateRaw( $sql );
        // if ( $d_conf->count() > 0 ) {
        //     $d_conf = $d_conf->toArray();

        //     foreach ($d_conf as $key => $value) {
        //         Modules::run( 'base/InsertJurnal/exec', $this->url, $value['id'], $value['id'], 2);
        //     }
        // }

        Modules::run( 'base/InsertJurnal/exec', $this->url, 62, 62, 2);
    }
}