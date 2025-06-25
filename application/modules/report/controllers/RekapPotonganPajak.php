<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RekapPotonganPajak extends Public_Controller {

    private $pathView = 'report/rekap_potongan_pajak/';
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
                'assets/select2/js/select2.min.js',
                "assets/report/rekap_potongan_pajak/js/rekap-potongan-pajak.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/rekap_potongan_pajak/css/rekap-potongan-pajak.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['formRhpp'] = $this->formRhpp();
            $content['formOaPakan'] = $this->formOaPakan();
            $content['title_menu'] = 'Rekap Potongan Pajak';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getPerusahaan() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select p1.* from perusahaan p1
            right join
                (select max(id) as id, kode from perusahaan group by kode) p2
                on
                    p1.id = p2.id
            order by
                p1.perusahaan asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getUnit() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                REPLACE(REPLACE(w1.nama, 'Kota ', ''), 'Kab ', '') as nama,
                w1.kode
            from wilayah w1
            right join
                (select max(id) as id, kode from wilayah where kode is not null group by kode) w2
                on
                    w1.id = w2.id
            order by
                REPLACE(REPLACE(w1.nama, 'Kota ', ''), 'Kab ', '') asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function formRhpp() {
        $content['perusahaan'] = $this->getPerusahaan();
        $content['unit'] = $this->getUnit();
        $html = $this->load->view($this->pathView.'formRhpp', $content, TRUE);

        return $html;
    }

    public function formOaPakan() {
        $content['perusahaan'] = $this->getPerusahaan();
        $content['unit'] = $this->getUnit();
        $html = $this->load->view($this->pathView.'formOaPakan', $content, TRUE);

        return $html;
    }

    public function getLists() {
        $params = $this->input->get('params');

        $filename = null;
        $data = null;
        $total = null;
        if ( $params['jenis'] == 1 ) {
            $_data = $this->getDataPotonganPajakRhpp($params);
            $data = $_data['data'];
            $filename = 'listRhpp';
        } else if ( $params['jenis'] == 2 ) {
            $_data = $this->getDataPotonganPajakOaPakan($params);
            $data = $_data['data'];
            $filename = 'listOaPakan';
        }

        $content['data'] = $data;
        $content['total'] = $total;
        $html = $this->load->view($this->pathView.$filename, $content, TRUE);

        echo $html;
    }

    public function getDataPotonganPajakRhpp($params) {    
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];

        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan = "and mtr.perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit = "and mtr.kode_unit in ('".implode("', '", $unit)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from
            (
                select 
                    ts.tgl_tutup as tgl_rhpp,
                    mtr.nomor as no_mitra,
                    r.mitra as mitra,
                    cast(cast(SUBSTRING(r.noreg, 10, 2) as int) as varchar(2)) as kandang,
                    mtr.ktp,
                    REPLACE(REPLACE(mtr.npwp, '-', ''), '.', '') as npwp,
                    mtr.nama_unit,
                    mtr.alamat,
                    mtr.kab_kota,
                    mtr.prov,
                    mtr.no_telp,
                    r.pdpt_peternak_belum_pajak,
                    r.prs_potongan_pajak,
                    r.potongan_pajak,
                    (r.pdpt_peternak_belum_pajak - r.potongan_pajak) as pdpt_peternak_sudah_pajak,
                    isnull(trf.bayar, 0) as transfer,
                    mtr.perusahaan,
                    prs.perusahaan as nama_perusahaan,
                    case
                        when r.prs_potongan_pajak = 0.5 then
                            mtr.skb
                        else
                            null
                    end as no_skb,
                    case
                        when r.prs_potongan_pajak = 0.5 then
                            mtr.tgl_habis_skb
                        else
                            null
                    end as tgl_habis_skb,
                    r.invoice
                from rhpp r
                left join
                    tutup_siklus ts
                    on
                        r.id_ts = ts.id
                left join
                    (
                        select 
                            k.kandang,
                            mtr.nomor, 
                            mtr.nama, 
                            mtr.ktp, 
                            mtr.npwp, 
                            mm.nim, 
                            w.kode as kode_unit, 
                            REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', '') as nama_unit,
                            mtr.alamat_jalan+isnull(' RT.'+cast(mtr.alamat_rt as varchar(5)), null)+isnull('/RW.'+cast(mtr.alamat_rw as varchar(5)), null)+', KEL.'+mtr.alamat_kelurahan+', KEC.'+kec.nama as alamat,
                            REPLACE(REPLACE(kab_kota.nama, 'Kota ', ''), 'Kab ', '') as kab_kota,
                            prov.nama as prov,
                            tm.nomor as no_telp,
                            mtr.perusahaan,
                            mtr.skb,
                            mtr.tgl_habis_skb
                        from mitra mtr
                        right join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                mtr.id = mm.mitra
                        left join
                            (
                                select tm1.* from telepon_mitra tm1
                                right join
                                    (select max(id) as id, mitra from telepon_mitra group by mitra) tm2
                                    on
                                        tm1.id = tm2.id
                            ) tm
                            on
                                mtr.id = tm.mitra
                        left join
                            kandang k
                            on
                                k.mitra_mapping = mm.id
                        left join
                            wilayah w
                            on
                                w.id = k.unit
                        left join
                            lokasi kec
                            on
                                mtr.alamat_kecamatan = kec.id
                        left join
                            lokasi kab_kota
                            on
                                kec.induk = kab_kota.id
                        left join
                            lokasi prov
                            on
                                kab_kota.induk = prov.id
                        group by
                            k.kandang, mtr.nomor, mtr.nama, mtr.ktp, mtr.npwp, mm.nim, w.kode, w.nama, mtr.alamat_jalan, mtr.alamat_rt, mtr.alamat_rw, mtr.alamat_kelurahan, kec.nama, kab_kota.nama, prov.nama, tm.nomor, mtr.perusahaan, mtr.skb, mtr.tgl_habis_skb
                    ) mtr
                    on
                        mtr.nim = SUBSTRING(ts.noreg, 0, 8) and
                        mtr.kandang = cast(SUBSTRING(ts.noreg, 10, 2) as int)
                left join
                    (
                        select prs1.* from perusahaan prs1
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) prs2
                            on
                                prs1.id = prs2.id
                    ) prs
                    on
                        prs.kode = mtr.perusahaan
                left join
                    (
                        select kppd.id_trans as id_rhpp, rpd.transaksi, rpd.no_bayar, sum(rpd.bayar) as bayar from realisasi_pembayaran_det rpd
                        left join
                            konfirmasi_pembayaran_peternak kpp
                            on
                                rpd.no_bayar = kpp.nomor
                        left join
                            konfirmasi_pembayaran_peternak_det kppd
                            on
                                kpp.id = kppd.id_header
                        where
                            rpd.transaksi = 'PLASMA' and
                            kppd.jenis = 'RHPP'
                        group by
                            kppd.id_trans, rpd.transaksi, rpd.no_bayar
                    ) trf
                    on
                        trf.id_rhpp = r.id
                where 
                    r.jenis = 'rhpp_plasma' and
                    ts.tgl_tutup between '".$start_date."' and '".$end_date."' and
                    not exists (select * from rhpp_group_noreg where noreg = ts.noreg)
                    ".$sql_perusahaan."
                    ".$sql_unit."

                union all

                select 
                    rgh.tgl_submit as tgl_rhpp,
                    rgh.nomor as no_mitra,
                    rgh.mitra,
                    '' as kandang,
                    mtr.ktp,
                    mtr.npwp,
                    mtr.nama_unit,
                    mtr.alamat,
                    mtr.kab_kota,
                    mtr.prov,
                    mtr.no_telp,
                    rg.pdpt_peternak_belum_pajak,
                    rg.prs_potongan_pajak,
                    rg.potongan_pajak,
                    (rg.pdpt_peternak_belum_pajak - rg.potongan_pajak) as pdpt_peternak_sudah_pajak,
                    isnull(trf.bayar, 0) as transfer,
                    mtr.perusahaan,
                    prs.perusahaan as nama_perusahaan,
                    case
                        when rg.prs_potongan_pajak = 0.5 then
                            mtr.skb
                        else
                            null
                    end as no_skb,
                    case
                        when rg.prs_potongan_pajak = 0.5 then
                            mtr.tgl_habis_skb
                        else
                            null
                    end as tgl_habis_skb,
                    rg.invoice
                from rhpp_group rg
                left join
                    rhpp_group_header rgh
                    on
                        rg.id_header = rgh.id
                left join
                    (
                        select 
                            mtr.nomor, 
                            mtr.nama, 
                            mtr.ktp, 
                            mtr.npwp, 
                            mm.nim, 
                            w.kode as kode_unit, 
                            REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', '') as nama_unit,
                            mtr.alamat_jalan+isnull(' RT.'+cast(mtr.alamat_rt as varchar(5)), null)+isnull('/RW.'+cast(mtr.alamat_rw as varchar(5)), null)+', KEL.'+mtr.alamat_kelurahan+', KEC.'+kec.nama as alamat,
                            REPLACE(REPLACE(kab_kota.nama, 'Kota ', ''), 'Kab ', '') as kab_kota,
                            prov.nama as prov,
                            tm.nomor as no_telp,
                            mtr.perusahaan,
                            mtr.skb,
                            mtr.tgl_habis_skb
                        from mitra mtr
                        right join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                mtr.id = mm.mitra
                        left join
                            (
                                select tm1.* from telepon_mitra tm1
                                right join
                                    (select max(id) as id, mitra from telepon_mitra group by mitra) tm2
                                    on
                                        tm1.id = tm2.id
                            ) tm
                            on
                                mtr.id = tm.mitra
                        left join
                            kandang k
                            on
                                k.mitra_mapping = mm.id
                        left join
                            wilayah w
                            on
                                w.id = k.unit
                        left join
                            lokasi kec
                            on
                                mtr.alamat_kecamatan = kec.id
                        left join
                            lokasi kab_kota
                            on
                                kec.induk = kab_kota.id
                        left join
                            lokasi prov
                            on
                                kab_kota.induk = prov.id
                        group by
                            mtr.nomor, mtr.nama, mtr.ktp, mtr.npwp, mm.nim, w.kode, w.nama, mtr.alamat_jalan, mtr.alamat_rt, mtr.alamat_rw, mtr.alamat_kelurahan, kec.nama, kab_kota.nama, prov.nama, tm.nomor, mtr.perusahaan, mtr.skb, mtr.tgl_habis_skb
                    ) mtr
                    on
                        mtr.nomor = rgh.nomor
                left join
                    (
                        select prs1.* from perusahaan prs1
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) prs2
                            on
                                prs1.id = prs2.id
                    ) prs
                    on
                        prs.kode = mtr.perusahaan
                left join
                    (
                        select kppd.id_trans as id_rhpp, rpd.* from realisasi_pembayaran_det rpd
                        left join
                            konfirmasi_pembayaran_peternak kpp
                            on
                                rpd.no_bayar = kpp.nomor
                        left join
                            konfirmasi_pembayaran_peternak_det kppd
                            on
                                kpp.id = kppd.id_header
                        where
                            rpd.transaksi = 'PLASMA' and
                            kppd.jenis = 'RHPP GROUP'
                    ) trf
                    on
                        trf.id_rhpp = rg.id
                where
                    rg.jenis = 'rhpp_plasma' and
                    rgh.tgl_submit between '".$start_date."' and '".$end_date."'
                    ".$sql_perusahaan."
                    ".$sql_unit."
            ) rhpp
        ";
        $d_conf = $m_conf->hydrateRaw($sql);

        $data = null;

        $tot_pendapatan = 0;
        $tot_pot_pajak = 0;
        $tot_pdpt_stlh_pajak = 0;
        $tot_transfer = 0;

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $key_header = $value['nama_perusahaan'].' | '.$value['nama_unit'];
                $key_detail = $value['tgl_rhpp'].' | '.$value['mitra'].(!empty( $value['kandang'] ) ? ' | '.$value['kandang'] : '');

                if ( !isset($data[ $key_header ]) ) {
                    $data[ $key_header ] = array(
                        'perusahaan' => $value['nama_perusahaan'],
                        'unit' => $value['nama_unit'],
                        'pendapatan' => $value['pdpt_peternak_belum_pajak'],
                        'pot_pajak' => $value['potongan_pajak'],
                        'pdpt_stlh_pajak' => $value['pdpt_peternak_sudah_pajak'],
                        'transfer' => $value['transfer']
                    );
                    $data[ $key_header ]['detail'][ $key_detail ] = $value;

                    $tot_pendapatan += $value['pdpt_peternak_belum_pajak'];
                    $tot_pot_pajak += $value['potongan_pajak'];
                    $tot_pdpt_stlh_pajak += $value['pdpt_peternak_sudah_pajak'];
                    $tot_transfer += $value['transfer'];
                } else {
                    $data[ $key_header ]['pendapatan'] += $value['pdpt_peternak_belum_pajak'];
                    $data[ $key_header ]['pot_pajak'] += $value['potongan_pajak'];
                    $data[ $key_header ]['pdpt_stlh_pajak'] += $value['pdpt_peternak_belum_pajak'];
                    $data[ $key_header ]['transfer'] += $value['transfer'];

                    $data[ $key_header ]['detail'][ $key_detail ] = $value;

                    $tot_pendapatan += $value['pdpt_peternak_belum_pajak'];
                    $tot_pot_pajak += $value['potongan_pajak'];
                    $tot_pdpt_stlh_pajak += $value['pdpt_peternak_sudah_pajak'];
                    $tot_transfer += $value['transfer'];
                }

                ksort($data[ $key_header ]['detail']);
            }

            ksort($data);
        }

        $_data = array(
            'data' => $data,
            'total' => array(
                'tot_pendapatan' => $tot_pendapatan,
                'tot_pot_pajak' => $tot_pot_pajak,
                'tot_pdpt_stlh_pajak' => $tot_pdpt_stlh_pajak,
                'tot_transfer' => $tot_transfer,
            )
        );

        return $_data;
    }

    public function getDataPotonganPajakOaPakan($params) {    
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];
        $jenis_mutasi = $params['jenis_mutasi'];

        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan = "and kpop.perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit = "and kp.unit in ('".implode("', '", $unit)."')";
        }

        $sql_jenis_mutasi = "";
        if ( $jenis_mutasi != 'all' ) {
            if ( $jenis_mutasi == 'mutasi' ) {
                $sql_jenis_mutasi = "and ((kp.asal = 'peternak' and kp.tujuan = 'peternak') or (kp.asal = 'gudang' and kp.tujuan = 'gudang') or (kp.asal = 'peternak' and kp.tujuan = 'gudang'))";
            } else if ( $jenis_mutasi == 'non_mutasi' ) {
                $sql_jenis_mutasi = "and ((kp.asal = 'supplier' and kp.tujuan = 'gudang') or (kp.asal = 'gudang' and kp.tujuan = 'peternak'))";
            }
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.tgl_bayar,
                data.no_ekspedisi,
                data.ekspedisi,
                data.ktp,
                data.npwp,
                data.kode_unit, 
                data.nama_unit,
                data.alamat,
                data.kab_kota,
                data.prov,
                data.no_telp,
                data.pdpt_belum_pajak,
                data.prs_potongan_pajak,
                data.potongan_pajak,
                data.pdpt_sudah_pajak,
                data.transfer,
                data.invoice,
                data.perusahaan,
                data.nama_perusahaan,
                data.nama_npwp,
                case
                    when data.prs_potongan_pajak = 0.5 then
                        data.skb
                    else
                        null
                end as no_skb,
                case
                    when data.prs_potongan_pajak = 0.5 then
                        data.tgl_habis_skb
                    else
                        null
                end as tgl_habis_skb
            from
            (
                select 
                    kpop.tgl_bayar,
                    eks.nomor as no_ekspedisi,
                    kpop.ekspedisi,
                    eks.nik as ktp,
                    eks.npwp,
                    w.kode as kode_unit, 
                    REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', '') as nama_unit,
                    eks.alamat_jalan+isnull(' RT.'+cast(eks.alamat_rt as varchar(5)), null)+isnull('/RW.'+cast(eks.alamat_rw as varchar(5)), null)+', KEL.'+eks.alamat_kelurahan+', KEC.'+kec.nama as alamat,
                    REPLACE(REPLACE(kab_kota.nama, 'Kota ', ''), 'Kab ', '') as kab_kota,
                    prov.nama as prov,
                    eks.no_telp,
                    kpop.sub_total as pdpt_belum_pajak,
                    case
                        when kpop.potongan_pph_23 > 0 then
                            (kpop.potongan_pph_23 / kpop.sub_total) * 100
                        else
                            0
                    end as prs_potongan_pajak,
                    kpop.potongan_pph_23 as potongan_pajak,
                    kpop.total as pdpt_sudah_pajak,
                    kpop.total as transfer,
                    kpop.invoice,
                    prs.kode as perusahaan,
                    prs.perusahaan as nama_perusahaan,
                    kpop.ekspedisi as nama_npwp,
                    eks.skb,
                    eks.tgl_habis_skb
                from konfirmasi_pembayaran_oa_pakan_det kpopd
                left join
                    konfirmasi_pembayaran_oa_pakan kpop
                    on
                        kpopd.id_header = kpop.id
                left join
                    realisasi_pembayaran_det rpd
                    on
                        rpd.no_bayar = kpop.nomor
                left join
                    (
                        select
                            kp.no_sj as no_trans,
                            kp.no_order,
                            case
                                when kp.jenis_kirim = 'opks' then
                                    'supplier'
                                when kp.jenis_kirim = 'opkg' then
                                    'gudang'
                                when kp.jenis_kirim = 'opkp' then
                                    'peternak'
                            end as asal,
                            kp.jenis_tujuan as tujuan,
                            case
                                when kp.jenis_kirim = 'opks' then
                                    SUBSTRING(kp.no_order, 5, 3)
                                when kp.jenis_kirim = 'opkg' then
                                    SUBSTRING(kp.no_order, 4, 3)
                                when kp.jenis_kirim = 'opkp' then
                                    SUBSTRING(kp.no_order, 4, 3)
                                    -- w.kode
                            end as unit
                        from kirim_pakan kp
                        left join
                            rdim_submit rs
                            on
                                rs.noreg = kp.asal
                        left join
                            kandang k
                            on
                                k.id = rs.kandang
                        left join
                            wilayah w
                            on
                                w.id = k.unit 
                        
                        union all
                        
                        select
                            rp.no_retur as no_trans,
                            rp.no_order,
                            rp.asal,
                            rp.tujuan,
                            case
                                when rp.tujuan = 'supplier' then
                                    SUBSTRING(rp.no_order, 5, 3)
                                else
                                    SUBSTRING(rp.no_order, 4, 3)
                            end as unit
                        from retur_pakan rp
                    ) kp
                    on
                        kp.no_trans = kpopd.no_sj
                left join
                    (
                        select 
                            eks1.*,
                            te.nomor as no_telp
                        from ekspedisi eks1
                        right join
                            (select max(id) as id, nomor from ekspedisi group by nomor) eks2
                            on
                                eks1.id = eks2.id
                        left join
                            telp_ekspedisi te
                            on
                                eks1.id = te.ekspedisi_id
                    ) eks
                    on
                        kpop.ekspedisi_id = eks.nomor
                left join
                    (
                        select prs1.* from perusahaan prs1
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) prs2
                            on
                                prs1.id = prs2.id
                    ) prs
                    on
                        prs.kode = kpop.perusahaan
                left join
                    lokasi kec
                    on
                        eks.alamat_kecamatan = kec.id
                left join
                    lokasi kab_kota
                    on
                        kec.induk = kab_kota.id
                left join
                    lokasi prov
                    on
                        kab_kota.induk = prov.id
                left join
                    wilayah w
                    on
                        kp.unit = w.kode
                where
                    kpop.tgl_bayar between '".$start_date."' and '".$end_date."'
                    ".$sql_perusahaan."
                    ".$sql_unit."
                    ".$sql_jenis_mutasi."
                /* group by
                    kpop.tgl_bayar,
                    kpop.ekspedisi,
                    eks.nik,
                    eks.npwp,
                    w.kode, 
                    w.nama,
                    eks.alamat_jalan,
                    eks.alamat_rt,
                    eks.alamat_rw,
                    eks.alamat_kelurahan,
                    kec.nama,
                    kab_kota.nama,
                    prov.nama,
                    eks.no_telp,
                    kpop.invoice,
                    prs.kode,
                    prs.perusahaan,
                    kpop.ekspedisi,
                    eks.skb,
                    eks.tgl_habis_skb */
            ) data
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw($sql);

        $data = null;

        $tot_pendapatan = 0;
        $tot_pot_pajak = 0;
        $tot_pdpt_stlh_pajak = 0;
        $tot_transfer = 0;
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            // cetak_r( $d_conf, 1 );

            foreach ($d_conf as $key => $value) {
                $key_header = $value['nama_perusahaan'].' | '.$value['nama_unit'];
                $key_detail = $value['tgl_bayar'].' | '.$value['ekspedisi'].' | '.$value['invoice'];

                // cetak_r( $key_detail );

                if ( !isset($data[ $key_header ]) ) {
                    $data[ $key_header ] = array(
                        'perusahaan' => $value['nama_perusahaan'],
                        'unit' => $value['nama_unit'],
                        'pendapatan' => $value['pdpt_belum_pajak'],
                        'pot_pajak' => $value['potongan_pajak'],
                        'pdpt_stlh_pajak' => $value['pdpt_belum_pajak'] - $value['potongan_pajak'],
                        'transfer' => $value['transfer']
                    );
                    $data[ $key_header ]['detail'][ $key_detail ] = $value;

                    $tot_pendapatan += $value['pdpt_belum_pajak'];
                    $tot_pot_pajak += $value['potongan_pajak'];
                    $tot_pdpt_stlh_pajak += $value['pdpt_belum_pajak'] - $value['potongan_pajak'];
                    $tot_transfer += $value['transfer'];
                } else {
                    $data[ $key_header ]['pendapatan'] += $value['pdpt_belum_pajak'];
                    $data[ $key_header ]['pot_pajak'] += $value['potongan_pajak'];
                    $data[ $key_header ]['pdpt_stlh_pajak'] += $value['pdpt_belum_pajak'] - $value['potongan_pajak'];
                    $data[ $key_header ]['transfer'] += $value['transfer'];

                    $data[ $key_header ]['detail'][ $key_detail ] = $value;

                    $tot_pendapatan += $value['pdpt_belum_pajak'];
                    $tot_pot_pajak += $value['potongan_pajak'];
                    $tot_pdpt_stlh_pajak += $value['pdpt_belum_pajak'] - $value['potongan_pajak'];
                    $tot_transfer += $value['transfer'];
                }

                ksort($data[ $key_header ]['detail']);
            }

            ksort($data);
        }

        $_data = array(
            'data' => $data,
            'total' => array(
                'tot_pendapatan' => $tot_pendapatan,
                'tot_pot_pajak' => $tot_pot_pajak,
                'tot_pdpt_stlh_pajak' => $tot_pdpt_stlh_pajak,
                'tot_transfer' => $tot_transfer,
            )
        );

        return $_data;
    }

    public function excryptParams()
    {
        $params = $this->input->post('params');

        try {
            $params_encrypt = exEncrypt( json_encode($params) );

            $this->result['status'] = 1;
            $this->result['content'] = $params_encrypt;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function exportExcel($params_encrypt)
    {
        $params = json_decode( exDecrypt($params_encrypt), true );

        $res_view_html = "TIDAK ADA DATA";
        $filename = "";
        if ( $params['jenis'] == 1 ) {
            $data = $this->getDataPotonganPajakRhpp( $params );
            $content['data'] = $data['data'];
            $content['total'] = $data['total'];
            $res_view_html = $this->load->view($this->pathView.'exportExcelRhpp', $content, true);
            $filename = "REKAP_POTONGAN_PAJAK_RHPP_";
        } else if ( $params['jenis'] == 2 ) {
            $data = $this->getDataPotonganPajakOaPakan( $params );
            $content['data'] = $data['data'];
            $content['total'] = $data['total'];
            $res_view_html = $this->load->view($this->pathView.'exportExcelOaPakan', $content, true);
            $filename = "REKAP_POTONGAN_PAJAK_OA_PAKAN_";
        }

        // header("Content-type: application/xls");
        // header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        // header("Content-type:   application/x-msexcel; charset=utf-8");
        header("Content-type:   application/ms-excel; charset=utf-8");
        $filename = $filename.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']).'.xls';
        header("Content-Disposition: attachment; filename=".$filename."");
        echo $res_view_html;
    }
}