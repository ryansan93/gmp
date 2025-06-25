<?php defined('BASEPATH') or exit('No direct script access allowed');

class SaldoHarian extends Public_Controller
{
    private $pathView = 'accounting/saldo_harian/';
    private $url;
    private $akses;
    /**
     * Constructor
     */
    public function __construct()
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
    public function index()
    {
        if ( $this->akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                'assets/accounting/saldo_harian/js/saldo-harian.js'
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/accounting/saldo_harian/css/saldo-harian.css'
            ));
            $data = $this->includes;

            $content['akses'] = $this->akses;
            $content['datas'] = null;
            $content['title_panel'] = 'Saldo Harian';

            // Load Indexx
            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();
            // $content['action'] = $this->load->view($this->pathView . 'input_basttb', $content, true);

            $data['title_menu'] = 'Saldo Harian';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getPerusahaan()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                prs1.kode,
                prs1.perusahaan as nama,
                prs1.aktif
            from perusahaan prs1
            right join
                (select max(id) as id, kode from perusahaan group by kode) prs2
                on
                    prs1.id = prs2.id
            order by
                prs1.perusahaan asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getSupplier()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                plg.*
            from pelanggan plg
            right join
                (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                on
                    plg.id = plg2.id
            where
                plg.mstatus = 1
            order by
                plg.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getData()
    {
        $params = $this->input->post('params');
        try {
            $tanggal = $params['tanggal'];
            $perusahaan = $params['perusahaan'];

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select top 1
                    sh.*,
                    shd.bank_awal,
                    shd.hutang_bca,
                    shd.lr_kemarin
                from saldo_harian_det shd
                right join
                    saldo_harian sh
                    on
                        sh.id = shd.id_header
                where
                    sh.tanggal < '".$tanggal."' and
                    sh.perusahaan = '".$perusahaan."'
                order by
                    sh.tanggal desc

            ";
            $d_sld_prev = $m_conf->hydrateRaw( $sql );

            $saldo = 0;
            $hutang_bca = 0;
            $lr_kemarin = 0;
            $hutang_supplier = null;
            if ( $d_sld_prev->count() > 0 ) {
                $id = $d_sld_prev->toArray()[0]['id'];

                $saldo = $d_sld_prev->toArray()[0]['bank_awal'];
                $hutang_bca = $d_sld_prev->toArray()[0]['hutang_bca'];
                $lr_kemarin = $d_sld_prev->toArray()[0]['lr_kemarin'];

                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select 
                        shdh.supplier,
                        supl.nama as nama_supplier,
                        shdh.hutang as tot_hutang,
                        shdh.tgl_sj_terlama
                    from saldo_harian_det_hutang shdh
                    left join
                        (
                            select plg1.* from pelanggan plg1
                            right join
                                (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                                on
                                    plg1.id = plg2.id
                        ) supl
                        on
                            supl.nomor = shdh.supplier
                    where
                        shdh.id_header = '".$id."'

                ";
                $d_hutang_supplier = $m_conf->hydrateRaw( $sql );

                if ( $d_hutang_supplier->count() > 0 ) {
                    $hutang_supplier = $d_hutang_supplier->toArray();
                }
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    sum(pp.jml_transfer) as tot_bayar
                from pembayaran_pelanggan pp
                where
                    pp.tgl_bayar = '".$tanggal."' and
                    pp.perusahaan = '".$perusahaan."'
            ";
            $d_byr_bakul = $m_conf->hydrateRaw( $sql );

            $tot_bayar = 0;
            if ( $d_byr_bakul->count() > 0 ) {
                $tot_bayar = $d_byr_bakul->toArray()[0]['tot_bayar'];
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    sum(dj.nominal) as tot_bank_out
                from det_jurnal dj
                right join
                    (
                        select djt.* from det_jurnal_trans djt
                        right join
                            jurnal_trans jt
                            on
                                djt.id_header = jt.id
                        where
                            jt.mstatus = 1
                    ) djt
                    on
                        djt.sumber_coa = dj.coa_asal and
                        djt.tujuan_coa = dj.coa_tujuan
                right join
                    (
                        select * from coa where coa in ('110201', '110202', '110203', '110350')
                    ) c
                    on
                        dj.coa_asal = c.coa
                where
                    dj.tanggal = '".$tanggal."' and
                    c.id_perusahaan = '".$perusahaan."'
            ";
            $d_bank_out = $m_conf->hydrateRaw( $sql );

            $tot_bank_out = 0;
            if ( $d_bank_out->count() > 0 ) {
                $tot_bank_out = $d_bank_out->toArray()[0]['tot_bank_out'];
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    sum(rp.jml_transfer) as tot_bayar 
                from realisasi_pembayaran rp 
                where 
                    rp.tgl_bayar = '".$tanggal."' and 
                    rp.perusahaan = '".$perusahaan."'
            ";
            $d_bayar_supl = $m_conf->hydrateRaw( $sql );

            $tot_bayar_supl = 0;
            if ( $d_bayar_supl->count() > 0 ) {
                $tot_bayar_supl = $d_bayar_supl->toArray()[0]['tot_bayar'];
            }

            $data = array(
                'saldo_bank' => ($saldo + $tot_bayar) - $tot_bank_out,
                'total_transfer' => $tot_bayar_supl
            );

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    kpp.supplier,
                    supl.nama as nama_supplier,
                    sum(kpp.total) as tot_hutang,
                    kppd.tgl_sj_tertua
                from konfirmasi_pembayaran_pakan kpp
                left join
                    (select min(tgl_sj) as tgl_sj_tertua, id_header from konfirmasi_pembayaran_pakan_det group by id_header) kppd
                    on
                        kpp.id = kppd.id_header
                left join
                    (
                        select plg1.* from pelanggan plg1
                        right join
                            (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                            on
                                plg1.id = plg2.id
                    ) supl
                    on
                        supl.nomor = kpp.supplier
                where 
                    kpp.tgl_bayar = '".$tanggal."' and 
                    kpp.perusahaan = '".$perusahaan."'
                group by
                    kpp.tgl_bayar,
                    kpp.perusahaan,
                    kpp.supplier,
                    supl.nama,
                    kppd.tgl_sj_tertua
            ";
            $d_hutang_pakan = $m_conf->hydrateRaw( $sql );

            $data_hutang_pakan = null;
            if ( $d_hutang_pakan->count() > 0 ) {
                $d_hutang_pakan = $d_hutang_pakan->toArray();

                if ( !empty( $hutang_supplier ) ) {
                    foreach ($hutang_supplier as $k_hs => $v_hs) {
                        $ada = 0;
                        $data_hutang = null;
                        foreach ($d_hutang_pakan as $k_hp => $v_hp) {
                            if ( $v_hs['supplier'] == $v_hp['supplier'] ) {
                                $ada = 1;
                                $data_hutang = $v_hp;
                            }
                        }

                        if ( $ada == 1 ) {
                            $data_hutang_pakan[] = $data_hutang;
                        } else {
                            $data_hutang_pakan[] = $v_hs;
                        }
                    }
                } else {
                    $data_hutang_pakan[] = $d_hutang_pakan;
                }
            } else {
                $data_hutang_pakan = $hutang_supplier;
            }

            $lr_today = 0;
            $jumlah_rhpp_today = 0;
            $jumlah_rhpp_ekor_today = 0;
            $jumlah_rhpp_box_today = 0;
            $tot_panen_ayam_today = 0;
            $tot_penjualan_ayam_today = 0;
            $tot_nilai_doc = 0;

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    sum(r.lr_inti) as total,
                    count(r.lr_inti) as jumlah_rhpp,
                    sum(r.populasi) as jumlah_ekor,
                    (sum(r.populasi) / 100) as jumlah_box,
                    sum(r.jml_panen_kg) as tot_panen_ayam_today,
                    sum(r.tot_penjualan_ayam) as tot_penjualan_ayam,
                    sum(rd.total) as tot_nilai_doc
                from rhpp r
                right join
                    rhpp_doc rd
                    on
                        r.id = rd.id_header
                right join
                    (
                        select rs.noreg, m.nama, m.perusahaan from rdim_submit rs
                        right join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                rs.nim = mm.nim
                        right join
                            mitra m
                            on
                                mm.mitra = m.id
                    ) rdim
                    on
                        r.noreg = rdim.noreg
                right join
                    tutup_siklus ts
                    on
                        r.id_ts = ts.id
                where
                    r.jenis = 'rhpp_inti' and
                    ts.tgl_tutup = '".$tanggal."' and
                    rdim.perusahaan = '".$perusahaan."' and
                    not exists (select * from rhpp_group_noreg where noreg = r.noreg)
            ";
            $d_rhpp_today = $m_conf->hydrateRaw( $sql );

            if ( $d_rhpp_today->count() > 0 ) {
                $lr_today += $d_rhpp_today->toArray()[0]['total'];
                $jumlah_rhpp_today += $d_rhpp_today->toArray()[0]['jumlah_rhpp'];
                $jumlah_rhpp_ekor_today += $d_rhpp_today->toArray()[0]['jumlah_ekor'];
                $jumlah_rhpp_box_today += $d_rhpp_today->toArray()[0]['jumlah_box'];
                $tot_panen_ayam_today += $d_rhpp_today->toArray()[0]['tot_panen_ayam_today'];
                $tot_penjualan_ayam_today += $d_rhpp_today->toArray()[0]['tot_penjualan_ayam'];
                $tot_nilai_doc += $d_rhpp_today->toArray()[0]['tot_nilai_doc'];
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    sum(rg.lr_inti) as total,
                    count(rg.lr_inti) as jumlah_rhpp,
                    sum(rgn.populasi) as jumlah_ekor,
                    (sum(rgn.populasi) / 100) as jumlah_box,
                    sum(rg.jml_panen_kg) as tot_panen_ayam_today,
                    sum(rg.tot_penjualan_ayam) as tot_penjualan_ayam,
                    sum(rgd.total) as tot_nilai_doc
                from rhpp_group rg
                right join
                    (select id_header, sum(jumlah) as jumlah, sum(total) as total from rhpp_group_doc group by id_header) rgd
                    on
                        rg.id = rgd.id_header
                right join
                    (select id_header, sum(populasi) as populasi from rhpp_group_noreg group by id_header) rgn
                    on
                        rgn.id_header = rg.id
                right join
                    rhpp_group_header rgh 
                    on
                        rg.id_header = rgh.id
                right join
                    (
                        select m1.* from mitra m1
                        right join
                            (select max(id) as id, nomor from mitra group by nomor) m2
                            on
                                m1.id = m2.id
                    ) m
                    on
                        rgh.nomor = m.nomor
                where
                    rg.jenis = 'rhpp_inti' and
                    rgh.tgl_submit = '".$tanggal."' and
                    m.perusahaan = '".$perusahaan."'
            ";
            $d_rhpp_group_today = $m_conf->hydrateRaw( $sql );

            if ( $d_rhpp_group_today->count() > 0 ) {
                $lr_today += $d_rhpp_group_today->toArray()[0]['total'];
                $jumlah_rhpp_today += $d_rhpp_group_today->toArray()[0]['jumlah_rhpp'];
                $jumlah_rhpp_ekor_today += $d_rhpp_group_today->toArray()[0]['jumlah_ekor'];
                $jumlah_rhpp_box_today += $d_rhpp_group_today->toArray()[0]['jumlah_box'];
                $tot_panen_ayam_today += $d_rhpp_group_today->toArray()[0]['tot_panen_ayam_today'];
                $tot_penjualan_ayam_today += $d_rhpp_group_today->toArray()[0]['tot_penjualan_ayam'];
                $tot_nilai_doc += $d_rhpp_group_today->toArray()[0]['tot_nilai_doc'];
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    sum(dj.nominal) as tot_cn_pakan
                from det_jurnal dj
                right join
                    (
                        select djt.* from det_jurnal_trans djt
                        right join
                            jurnal_trans jt
                            on
                                djt.id_header = jt.id
                        where
                            jt.mstatus = 1
                    ) djt
                    on
                        djt.sumber_coa = dj.coa_asal and
                        djt.tujuan_coa = dj.coa_tujuan
                right join
                    (
                        select * from coa where coa in ('130506')
                    ) c_asal
                    on
                        dj.coa_asal = c_asal.coa
                right join
                    (
                        select * from coa where coa in ('110201', '110202', '110203', '110350')
                    ) c_tujuan
                    on
                        dj.coa_tujuan = c_tujuan.coa
                where
                    dj.tanggal = '".$tanggal."' and
                    c_tujuan.id_perusahaan = '".$perusahaan."'
            ";
            $d_cn_pakan = $m_conf->hydrateRaw( $sql );

            $cn_pakan = 0;
            if ( $d_cn_pakan->count() > 0 ) {
                $cn_pakan += $d_cn_pakan->toArray()[0]['tot_cn_pakan'];
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    sum(dj.nominal) as tot_cn_doc
                from det_jurnal dj
                right join
                    (
                        select djt.* from det_jurnal_trans djt
                        right join
                            jurnal_trans jt
                            on
                                djt.id_header = jt.id
                        where
                            jt.mstatus = 1
                    ) djt
                    on
                        djt.sumber_coa = dj.coa_asal and
                        djt.tujuan_coa = dj.coa_tujuan
                right join
                    (
                        select * from coa where coa in ('130507')
                    ) c_asal
                    on
                        dj.coa_asal = c_asal.coa
                right join
                    (
                        select * from coa where coa in ('110201', '110202', '110203', '110350')
                    ) c_tujuan
                    on
                        dj.coa_tujuan = c_tujuan.coa
                where
                    dj.tanggal = '".$tanggal."' and
                    c_tujuan.id_perusahaan = '".$perusahaan."'
            ";
            $d_cn_doc = $m_conf->hydrateRaw( $sql );

            $cn_doc = 0;
            if ( $d_cn_doc->count() > 0 ) {
                $cn_doc += $d_cn_doc->toArray()[0]['tot_cn_doc'];
            }

            $data = array(
                'saldo_bank' => ($saldo + $tot_bayar) - $tot_bank_out,
                'total_transfer' => $tot_bayar_supl,
                'hutang_pakan' => $data_hutang_pakan,
                'hutang_bca' => $hutang_bca,
                'lr_kemarin' => $lr_kemarin,
                'lr_today' => $lr_today,
                'cn_pakan' => $cn_pakan,
                'cn_doc' => $cn_doc,
                'jumlah_rhpp_today' => $jumlah_rhpp_today,
                'jumlah_rhpp_box_today' => $jumlah_rhpp_box_today,
                'laba_per_ekor' => ($lr_today != 0 && $jumlah_rhpp_ekor_today != 0) ? $lr_today / $jumlah_rhpp_ekor_today : 0,
                'harga_rata_ayam' => ($tot_penjualan_ayam_today > 0 && $tot_panen_ayam_today > 0) ? $tot_penjualan_ayam_today / $tot_panen_ayam_today : 0,
                'harga_rata_doc' => ($tot_nilai_doc != 0 && $jumlah_rhpp_ekor_today != 0) ? $tot_nilai_doc / $jumlah_rhpp_ekor_today : 0
            );

            $content['supplier'] = $this->getSupplier();
            $content['tanggal'] = $tanggal;
            $content['data'] = $data;
            $html = $this->load->view($this->pathView . 'getDataForm', $content, true);;

            $this->result['status'] = 1;
            $this->result['content'] = array(
                'html' => $html
            );
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function loadForm()
    {
        $params = $this->input->get('params');
        $edit = $this->input->get('edit');

        $id = $params['id'];

        $content = array();
        $html = "url not found";
        
        if ( !empty($id) && $edit != 'edit' ) {
            // NOTE: view data BASTTB (ajax)
            $html = $this->viewForm($id);
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

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];

        $m_sh = new \Model\Storage\SaldoHarian_model();
        if ( !in_array('all', $perusahaan) ) {
            $d_sh = $m_sh->whereBetween('tanggal', [$start_date, $end_date])->whereIn('perusahaan', $perusahaan)->with(['d_perusahaan'])->orderBy('tanggal', 'desc')->get();
        } else {
            $d_sh = $m_sh->whereBetween('tanggal', [$start_date, $end_date])->with(['d_perusahaan'])->orderBy('tanggal', 'desc')->get();
        }

        $data = null;
        if ( $d_sh->count() > 0 ) {
            $data = $d_sh->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list', $content, true);

        echo $html;
    }

    public function riwayat()
    {
        $content['perusahaan'] = $this->getPerusahaan();
        $html = $this->load->view($this->pathView . 'riwayat', $content, true);

        return $html;
    }

    public function addForm()
    {
        $content['perusahaan'] = $this->getPerusahaan();
        $content['supplier'] = $this->getSupplier();
        $html = $this->load->view($this->pathView . 'addForm', $content, true);

        return $html;
    }

    public function viewForm($id)
    {
        $m_sh = new \Model\Storage\SaldoHarian_model();
        $d_sh = $m_sh->where('id', $id)->with(['saldo_harian_det', 'd_perusahaan'])->first();

        $data = null;
        if ( $d_sh ) {
            $data = $d_sh->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'viewForm', $content, true);

        return $html;
    }

    public function editForm($id)
    {
        $m_sh = new \Model\Storage\SaldoHarian_model();
        $d_sh = $m_sh->where('id', $id)->with(['saldo_harian_det', 'd_perusahaan'])->first();

        $data = null;
        if ( $d_sh ) {
            $data = $d_sh->toArray();
        }

        $content['data'] = $data;
        $content['perusahaan'] = $this->getPerusahaan();
        $content['supplier'] = $this->getSupplier();
        $html = $this->load->view($this->pathView . 'editForm', $content, true);

        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_sh = new \Model\Storage\SaldoHarian_model();
            $m_sh->tanggal = $params['tanggal'];
            $m_sh->perusahaan = $params['perusahaan'];
            $m_sh->save();

            $id_sh = $m_sh->id;

            $m_shd = new \Model\Storage\SaldoHarianDet_model();
            $m_shd->id_header = $id_sh;
            $m_shd->bank_awal = $params['saldo_bank'];
            $m_shd->bank_akhir = 0;
            $m_shd->tot_transfer = $params['tot_transfer'];
            $m_shd->hutang_bca = $params['hut_bca'];
            $m_shd->tot_hutang = $params['tot_hutang'];
            $m_shd->lr_kemarin = $params['lr_sebelumnya'];
            $m_shd->lr_today = $params['lr_hari_ini'];
            $m_shd->cn_pakan = $params['cn_pakan'];
            $m_shd->cn_doc = $params['cn_doc'];
            $m_shd->tot_lr = $params['tot_lr'];
            $m_shd->jumlah_rhpp = $params['rhpp_selesai'];
            $m_shd->jumlah_rhpp_box = $params['rhpp_selesai_box'];
            $m_shd->laba_ekor = $params['laba_per_ekor'];
            $m_shd->harga_rata_lb = $params['harga_rata_ayam'];
            $m_shd->harga_rata_doc = $params['harga_rata_doc'];
            $m_shd->save();

            $id_shd = $m_shd->id;

            foreach ($params['hutang'] as $k_hutang => $v_hutang) {
                $m_shdh = new \Model\Storage\SaldoHarianDetHutang_model();
                $m_shdh->id_header = $id_shd;
                $m_shdh->supplier = $v_hutang['supplier'];
                $m_shdh->hutang = $v_hutang['nilai'];
                $m_shdh->tgl_sj_terlama = prev_date($params['tanggal'], $v_hutang['hari']);
                $m_shdh->save();
            }

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_sh, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $id_sh);
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
            $id_sh = $params['id'];

            $m_sh = new \Model\Storage\SaldoHarian_model();
            $m_sh->where('id', $id_sh)->update(
                array(
                    'tanggal' => $params['tanggal'],
                    'perusahaan' => $params['perusahaan']
                )
            );

            $m_shd = new \Model\Storage\SaldoHarianDet_model();
            $m_shd->where('id_header', $id_sh)->delete();

            $m_shd = new \Model\Storage\SaldoHarianDet_model();
            $m_shd->id_header = $id_sh;
            $m_shd->bank_awal = $params['saldo_bank'];
            $m_shd->bank_akhir = 0;
            $m_shd->tot_transfer = $params['tot_transfer'];
            $m_shd->hutang_bca = $params['hut_bca'];
            $m_shd->tot_hutang = $params['tot_hutang'];
            $m_shd->lr_kemarin = $params['lr_sebelumnya'];
            $m_shd->lr_today = $params['lr_hari_ini'];
            $m_shd->cn_pakan = $params['cn_pakan'];
            $m_shd->cn_doc = $params['cn_doc'];
            $m_shd->tot_lr = $params['tot_lr'];
            $m_shd->jumlah_rhpp = $params['rhpp_selesai'];
            $m_shd->jumlah_rhpp_box = $params['rhpp_selesai_box'];
            $m_shd->laba_ekor = $params['laba_per_ekor'];
            $m_shd->harga_rata_lb = $params['harga_rata_ayam'];
            $m_shd->harga_rata_doc = $params['harga_rata_doc'];
            $m_shd->save();

            $id_shd = $m_shd->id;

            $m_shdh = new \Model\Storage\SaldoHarianDetHutang_model();
            $m_shdh->where('id_header', $id_shd)->delete();

            foreach ($params['hutang'] as $k_hutang => $v_hutang) {
                $m_shdh = new \Model\Storage\SaldoHarianDetHutang_model();
                $m_shdh->id_header = $id_shd;
                $m_shdh->supplier = $v_hutang['supplier'];
                $m_shdh->hutang = $v_hutang['nilai'];
                $m_shdh->tgl_sj_terlama = prev_date($params['tanggal'], $v_hutang['hari']);
                $m_shdh->save();
            }

            $d_sh = $m_sh->where('id', $id_sh)->first();

            $deskripsi_log = 'di-edit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_sh, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $id_sh);
            $this->result['message'] = 'Data berhasil di edit.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {
            $id_sh = $params['id'];

            $m_sh = new \Model\Storage\SaldoHarian_model();
            $d_sh = $m_sh->where('id', $id_sh)->first();

            $m_shd = new \Model\Storage\SaldoHarianDet_model();
            $d_shd = $m_shd->where('id_header', $id_sh)->first();

            $m_shdh = new \Model\Storage\SaldoHarianDetHutang_model();
            $m_shdh->where('id_header', $d_shd->id)->delete();
            $m_shd->where('id_header', $id_sh)->delete();
            $m_sh->where('id', $id_sh)->delete();

            $deskripsi_log = 'di-edit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_sh, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes()
    {
        $selisih = selisihTanggal('2023-07-01', '2023-07-14');

        cetak_r( $selisih );
    }
}