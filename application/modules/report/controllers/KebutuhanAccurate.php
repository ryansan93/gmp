<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class KebutuhanAccurate extends Public_Controller {

    private $pathView = 'report/kebutuhan_accurate/';
    private $url;

    private $data_bayar_jual = null;

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
                "assets/report/kebutuhan_accurate/js/kebutuhan-accurate.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/kebutuhan_accurate/css/kebutuhan-accurate.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;

            $unit = $this->getUnit();
            $perusahaan = $this->getPerusahaan();
            $content['formPenjualanLb'] = $this->formPenjualanLb($unit, $perusahaan);
            $content['formPembelianDoc'] = $this->formPembelianDoc($unit, $perusahaan);
            $content['formPembelianVoadip'] = $this->formPembelianVoadip($unit, $perusahaan);
            $content['formPembelianPakan'] = $this->formPembelianPakan($unit, $perusahaan);
            $content['formReturPembelian'] = $this->formReturPembelian($unit, $perusahaan);
            $content['formDistribusiVoadip'] = $this->formDistribusiVoadip($unit, $perusahaan);
            $content['formDistribusiPakan'] = $this->formDistribusiPakan($unit, $perusahaan);
            $content['formBayarPembelian'] = $this->formBayarPembelian($unit, $perusahaan);
            $content['formBayarOa'] = $this->formBayarOa($unit, $perusahaan);
            $content['formBayarMaklon'] = $this->formBayarMaklon($unit, $perusahaan);
            $content['formBayarPenjualan'] = $this->formBayarPenjualan($unit, $perusahaan);

            $content['title_menu'] = 'Kebutuhan Accurate';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getUnit()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', '') as nama,
                w.kode
            from wilayah w
            where
                w.jenis = 'UN'
            group by
                REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', ''),
                w.kode
            order by
                REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', '') asc
        ";
        $d_unit = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_unit->count() > 0 ) {
            $data = $d_unit->toArray();
        }

        return $data;
    }

    public function getPerusahaan()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select p1.* from perusahaan p1
            right join
                (select max(id) as id, kode from perusahaan group by kode) p2
                on
                    p1.id = p2.id
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function formPenjualanLb($unit, $perusahaan) {
        $content['perusahaan'] = $perusahaan;
        $content['unit'] = $unit;
        $html = $this->load->view($this->pathView.'formPenjualanLb', $content, TRUE);

        return $html;
    }

    public function formPembelianDoc($unit, $perusahaan) {
        $content['perusahaan'] = $perusahaan;
        $content['unit'] = $unit;
        $html = $this->load->view($this->pathView.'formPembelianDoc', $content, TRUE);

        return $html;
    }

    public function formPembelianVoadip($unit, $perusahaan) {
        $content['perusahaan'] = $perusahaan;
        $content['unit'] = $unit;
        $html = $this->load->view($this->pathView.'formPembelianVoadip', $content, TRUE);

        return $html;
    }

    public function formPembelianPakan($unit, $perusahaan) {
        $content['perusahaan'] = $perusahaan;
        $content['unit'] = $unit;
        $html = $this->load->view($this->pathView.'formPembelianPakan', $content, TRUE);

        return $html;
    }

    public function formReturPembelian($unit, $perusahaan) {
        $content['perusahaan'] = $perusahaan;
        $content['unit'] = $unit;
        $html = $this->load->view($this->pathView.'formReturPembelian', $content, TRUE);

        return $html;
    }
    
    public function formDistribusiVoadip($unit, $perusahaan) {
        $content['perusahaan'] = $perusahaan;
        $content['unit'] = $unit;
        $html = $this->load->view($this->pathView.'formDistribusiVoadip', $content, TRUE);

        return $html;
    }

    public function formDistribusiPakan($unit, $perusahaan) {
        $content['perusahaan'] = $perusahaan;
        $content['unit'] = $unit;
        $html = $this->load->view($this->pathView.'formDistribusiPakan', $content, TRUE);

        return $html;
    }

    public function formBayarPembelian($unit, $perusahaan) {
        $content['perusahaan'] = $perusahaan;
        $content['unit'] = $unit;
        $html = $this->load->view($this->pathView.'formBayarPembelian', $content, TRUE);

        return $html;
    }

    public function formBayarOa($unit, $perusahaan) {
        $content['perusahaan'] = $perusahaan;
        $content['unit'] = $unit;
        $html = $this->load->view($this->pathView.'formBayarOa', $content, TRUE);

        return $html;
    }

    public function formBayarMaklon($unit, $perusahaan) {
        $content['perusahaan'] = $perusahaan;
        $content['unit'] = $unit;
        $html = $this->load->view($this->pathView.'formBayarMaklon', $content, TRUE);

        return $html;
    }

    public function formBayarPenjualan($unit, $perusahaan) {
        $content['perusahaan'] = $perusahaan;
        $content['unit'] = $unit;
        $html = $this->load->view($this->pathView.'formBayarPenjualan', $content, TRUE);

        return $html;
    }

    public function getLists() {
        $params = $this->input->get('params');

        $data = null;
        $listForm = null;
        if ( $params['jenis'] == 1 ) {
            $data = $this->getDataPenjualanLb( $params );
            $listForm = 'listPenjualanLb';
        } else if ( $params['jenis'] == 2 ) {
            $data = $this->getDataPembelianDoc( $params );
            $listForm = 'listPembelianDoc';
        } else if ( $params['jenis'] == 3 ) {
            $data = $this->getDataPembelianVoadip( $params );
            $listForm = 'listPembelianVoadip';
        } else if ( $params['jenis'] == 4 ) {
            $data = $this->getDataPembelianPakan( $params );
            $listForm = 'listPembelianPakan';
        } else if ( $params['jenis'] == 5 ) {
            $data = $this->getDataReturPembelian( $params );
            $listForm = 'listReturPembelian';
        } else if ( $params['jenis'] == 6 ) {
            $data = $this->getDataDistribusiVoadip( $params );
            $listForm = 'listDistribusiVoadip';
        } else if ( $params['jenis'] == 7 ) {
            $data = $this->getDataDistribusiPakan( $params );
            $listForm = 'listDistribusiPakan';
        } else if ( $params['jenis'] == 8 ) {
            $data = $this->getDataBayarPembelian( $params );
            $listForm = 'listBayarPembelian';
        } else if ( $params['jenis'] == 9 ) {
            $data = $this->getDataBayarOa( $params );
            $listForm = 'listBayarOa';
        } else if ( $params['jenis'] == 10 ) {
            $data = $this->getDataBayarMaklon( $params );
            $listForm = 'listBayarMaklon';
        } else if ( $params['jenis'] == 11 ) {
            $data = $this->getDataBayarPenjualan( $params );
            $listForm = 'listBayarPenjualan';
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView.$listForm, $content, TRUE);

        echo $html;
    }

    public function getDataPenjualanLb( $params ) {
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];
        $tutup_siklus = $params['tutup_siklus'];

        $sql_tutup_siklus = "";
        if ( stristr($tutup_siklus, 'all') === false ) {
            if ( $tutup_siklus == '1' ) {
                $sql_tutup_siklus .= "and rhpp.tgl_tutup is not null";
            } else {
                $sql_tutup_siklus .= "and rhpp.tgl_tutup is null";
            }
        }

        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan .= "and rdim.perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit .= "and w.kode in ('".implode("', '", $unit)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.id,
                data.kode_bakul,
                data.nik_bakul,
                data.nama_bakul,
                data.alamat_bakul,
                data.no_faktur,
                data.tanggal_panen,
                data.tanggal_rhpp,
                data.no_nota,
                data.no_nota_timbang,
                data.kode_barang,
                data.deskripsi_barang,
                data.kuantitas,
                data.harga_per_satuan_kuantitas,
                data.jumlah_ekor,
                data.kuantitas * data.harga_per_satuan_kuantitas as total,
                data.periode,
                data.unit,
                data.kode_unit,
                data.noreg,
                data.nim,
                data.nik,
                data.nama_plasma,
                data.kandang_plasma,
                data.periode_chickin,
                data.npwp_plasma,
                data.total_tagihan,
                data.perusahaan_alias,
                data.kode_accurate
            from
            (
                select 
                    drs.id,
                    drs.no_pelanggan as kode_bakul,
                    cast(plg.nik as varchar(100)) as nik_bakul,
                    UPPER(plg.nama) as nama_bakul,
                    UPPER(plg.alamat_jalan+' RT.'+cast(plg.alamat_rt as varchar(3))+'/RW.'+cast(plg.alamat_rw as varchar(3))+', KEL.'+plg.alamat_kelurahan+', '+l.nama+', '+REPLACE(REPLACE(l_head.nama, 'Kab ', ''), 'Kota ', '')) as alamat_bakul,
                    '' as no_faktur,
                    rs.tgl_panen as tanggal_panen,
                    rhpp.tgl_tutup as tanggal_rhpp,
                    drs.no_sj as no_nota,
                    drs.no_nota as no_nota_timbang,
                    UPPER(drs.jenis_ayam) as kode_barang,
                    CASE 
                        WHEN drs.jenis_ayam = 'n' THEN
                            'NORMAL'
                        WHEN drs.jenis_ayam = 'a' THEN
                            'AFKIR'
                        WHEN drs.jenis_ayam = 's' THEN
                            'SPESIAL'
                    END as deskripsi_barang,
                    drs.tonase as kuantitas,
                    drs.harga as harga_per_satuan_kuantitas,
                    drs.ekor as jumlah_ekor,
                    rhpp.tgl_docin as periode,
                    REPLACE(REPLACE(rs.unit, 'KAB ', ''), 'KOTA ', '') as unit,
                    w.kode as kode_unit,
                    rdim.noreg,
                    rdim.nim,
                    rdim.nik,
                    rdim.nama as nama_plasma,
                    cast(SUBSTRING(rs.noreg, 10, 2) as int) as kandang_plasma,
                    cast(SUBSTRING(rs.noreg, 8, 2) as int) as periode_chickin,
                    rdim.npwp as npwp_plasma,
                    drs_total.total as total_tagihan,
                    prs.alias as perusahaan_alias,
                    prs.kode_accurate
                from det_real_sj drs
                left join
                    (
                        select id_header, no_do, sum(tonase * harga) as total from det_real_sj where tonase > 0 and harga > 0 group by id_header, no_do
                    ) drs_total
                    on
                        drs.id_header = drs_total.id_header and
                        drs.no_do = drs_total.no_do
                left join
                    (
                        select rs1.* from real_sj rs1
                        right join
                            (select max(id) as id, noreg, tgl_panen from real_sj group by noreg, tgl_panen) rs2
                            on
                                rs1.id = rs2.id
                    ) rs 
                    on
                        drs.id_header = rs.id
                left join
                    (
                        select p1.* from pelanggan p1
                        right join
                            (select max(id) as id, nomor from pelanggan where tipe = 'pelanggan' group by nomor) p2
                            on
                                p1.id = p2.id
                    ) plg
                    on
                        plg.nomor = drs.no_pelanggan 
                left join
                    lokasi l
                    on
                        plg.alamat_kecamatan = l.id
                left join
                    lokasi l_head
                    on
                        l.induk = l_head.id
                left join
                    (
                        select data.noreg, data.tgl_docin, max(tgl_tutup) as tgl_tutup
                        from
                        (
                            select ts.noreg, ts.tgl_docin, ts.tgl_tutup from tutup_siklus ts 
                            
                            union all
                            
                            select rgn.noreg, rgn.tgl_docin, rgh.tgl_submit as tgl_tutup from (
                                select rgn1.* from rhpp_group_noreg rgn1
                                right join
                                    (select max(id) as id, noreg from rhpp_group_noreg group by noreg) rgn2
                                    on
                                        rgn1.id = rgn2.id
                            ) rgn
                            left join
                                rhpp_group rg 
                                on
                                    rgn.id_header = rg.id
                            left join
                                rhpp_group_header rgh 
                                on
                                    rg.id_header = rgh.id
                            group by
                                rgn.noreg, rgn.tgl_docin, rgh.tgl_submit
                        ) data
                        group by
                            data.noreg, data.tgl_docin
                    ) rhpp
                    on
                        rs.noreg = rhpp.noreg 
                left join
                    (
                        select r.noreg, r.nim, m.ktp as nik, m.nama, m.npwp, m.perusahaan from rdim_submit r
                        left join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                r.nim = mm.nim
                        left join
                            mitra m
                            on
                                m.id = mm.mitra
                    ) rdim
                    on
                        rs.noreg = rdim.noreg
                left join
                    wilayah w
                    on
                        rs.id_unit = w.id
                left join
                    (
                        select prs1.* from perusahaan prs1
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) prs2
                            on
                                prs1.id = prs2.id
                    ) prs
                    on
                        prs.kode = rdim.perusahaan
                where
                    drs.tonase > 0 and
                    drs.harga > 0 and
                    rs.tgl_panen between '".$start_date."' and '".$end_date."' -- JIKA BERDASARKAN TANGGAL PANEN
                    ".$sql_tutup_siklus."
                    ".$sql_perusahaan."
                    ".$sql_unit."
            ) data
            group by
                data.id,
                data.kode_bakul,
                data.nik_bakul,
                data.nama_bakul,
                data.alamat_bakul,
                data.no_faktur,
                data.tanggal_panen,
                data.tanggal_rhpp,
                data.no_nota,
                data.no_nota_timbang,
                data.kode_barang,
                data.deskripsi_barang,
                data.kuantitas,
                data.harga_per_satuan_kuantitas,
                data.jumlah_ekor,
                data.periode,
                data.unit,
                data.kode_unit,
                data.noreg,
                data.nim,
                data.nik,
                data.nama_plasma,
                data.kandang_plasma,
                data.periode_chickin,
                data.npwp_plasma,
                data.total_tagihan,
                data.perusahaan_alias,
                data.kode_accurate
            order by
                data.tanggal_panen asc,
                data.nama_bakul asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getDataPembelianDoc( $params ) {
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];
        $tutup_siklus = $params['tutup_siklus'];

        $sql_tutup_siklus = "";
        if ( stristr($tutup_siklus, 'all') === false ) {
            if ( $tutup_siklus == '1' ) {
                $sql_tutup_siklus .= "and rhpp.tgl_tutup is not null";
            } else {
                $sql_tutup_siklus .= "and rhpp.tgl_tutup is null";
            }
        }

        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan .= "and prs.kode in ('".implode("', '", $perusahaan)."')";
        }

        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit .= "and w.kode in ('".implode("', '", $unit)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.tanggal,
                data.perusahaan,
                data.perusahaan_alias,
                data.kode_supplier,
                data.nama_supplier,
                data.nik,
                data.npwp,
                data.no_form,
                data.no_sj,
                data.no_faktur_pembelian,
                data.tanggal_faktur,
                data.tanggal_pengiriman,
                data.kode_barang,
                data.nama_barang,
                data.kuantitas_order,
                data.kuantitas,
                data.satuan_kuantitas,
                data.harga_per_satuan_kuantitas,
                data.jumlah_box,
                data.kuantitas * data.harga_per_satuan_kuantitas as total,
                data.unit,
                data.nik_plasma,
                data.npwp_plasma,
                data.nama_plasma,
                data.kandang_plasma,
                data.periode_chickin,
                data.periode,
                data.kode_unit,
                data.nama_unit,
                data.kode_accurate
            from
            (
                select 
                    od.tgl_submit as tanggal,
                    UPPER(prs.perusahaan) as perusahaan,
                    UPPER(prs.alias) as perusahaan_alias,
                    supl.nomor as kode_supplier,
                    UPPER(supl.nama) as nama_supplier,
                    '123' as nik,
                    prs.npwp as npwp,
                    od.no_order as no_form,
                    td.no_sj as no_sj,
                    '' as no_faktur_pembelian,
                    '' as tanggal_faktur,
                    td.kirim as tanggal_pengiriman,
                    brg.kode as kode_barang,
                    UPPER(brg.nama) as nama_barang,
                    od.jml_ekor as kuantitas_order,
                    td.jml_ekor as kuantitas,
                    'ekor' as satuan_kuantitas,
                    od.harga as harga_per_satuan_kuantitas,
                    td.jml_box as jumlah_box,
                    UPPER(REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', '')) as unit,
                    rdim.nik as nik_plasma,
                    rdim.npwp as npwp_plasma,
                    UPPER(rdim.nama) as nama_plasma,
                    cast(SUBSTRING(od.noreg, 10, 2) as int) as kandang_plasma,
                    cast(SUBSTRING(od.noreg, 8, 2) as int) as periode_chickin,
                    td.datang as periode,
                    w.kode as kode_unit,
                    replace(replace(w.nama, 'Kab ', ''), 'Kota ', '') as nama_unit,
                    prs.kode_accurate
                from (
                    select od1.* from order_doc od1
                    right join
                        (select max(id) as id, no_order from order_doc group by no_order) od2
                        on
                            od1.id = od2.id
                ) od
                left join
                    (
                        select td1.* from terima_doc td1
                        right join
                            (select max(id) as id, no_order from terima_doc where no_order is not null group by no_order) td2
                            on
                                td1.id = td2.id
                    ) td
                    on
                        od.no_order = td.no_order
                left join
                    (
                        select p1.* from perusahaan p1
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) p2
                            on
                                p1.id = p2.id
                    ) prs
                    on
                        od.perusahaan = prs.kode
                left join
                    (
                        select p1.* from pelanggan p1
                        right join
                            (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) p2
                            on
                                p1.id = p2.id
                    ) supl
                    on
                        od.supplier = supl.nomor 
                left join
                    (
                        select brg1.* from barang brg1
                        right join
                            (select max(id) as id, kode from barang group by kode) brg2
                            on
                                brg1.id = brg2.id
                    ) brg
                    on
                        od.item = brg.kode
                left join
                    (
                        select data.noreg, data.tgl_docin, max(tgl_tutup) as tgl_tutup
                        from
                        (
                            select ts.noreg, ts.tgl_docin, ts.tgl_tutup from tutup_siklus ts 
                            
                            union all
                            
                            select rgn.noreg, rgn.tgl_docin, rgh.tgl_submit as tgl_tutup from (
                                select rgn1.* from rhpp_group_noreg rgn1
                                right join
                                    (select max(id) as id, noreg from rhpp_group_noreg group by noreg) rgn2
                                    on
                                        rgn1.id = rgn2.id
                            ) rgn
                            left join
                                rhpp_group rg 
                                on
                                    rgn.id_header = rg.id
                            left join
                                rhpp_group_header rgh 
                                on
                                    rg.id_header = rgh.id
                            group by
                                rgn.noreg, rgn.tgl_docin, rgh.tgl_submit
                        ) data
                        group by
                            data.noreg, data.tgl_docin
                    ) rhpp
                    on
                        od.noreg = rhpp.noreg 
                left join
                    (
                        select r.noreg, r.nim, m.ktp as nik, m.nama, m.npwp from rdim_submit r
                        left join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                r.nim = mm.nim
                        left join
                            mitra m
                            on
                                m.id = mm.mitra
                    ) rdim
                    on
                        od.noreg = rdim.noreg
                left join
                    wilayah w 
                    on
                        w.kode = SUBSTRING(od.no_order, 5, 3)
                where
                    -- td.datang between '".$start_date." 00:00:00.001' and '".$end_date." 23:59:59.999' -- JIKA BERDASARKAN TANGGAL TERIMA
                    od.tgl_submit between '".$start_date." 00:00:00.001' and '".$end_date." 23:59:59.999' -- JIKA BERDASARKAN TANGGAL ORDER
                    ".$sql_perusahaan."
                    ".$sql_unit."
                    ".$sql_tutup_siklus."
            ) data
            group by
                data.tanggal,
                data.perusahaan,
                data.perusahaan_alias,
                data.kode_supplier,
                data.nama_supplier,
                data.nik,
                data.npwp,
                data.no_form,
                data.no_sj,
                data.no_faktur_pembelian,
                data.tanggal_faktur,
                data.tanggal_pengiriman,
                data.kode_barang,
                data.nama_barang,
                data.kuantitas_order,
                data.kuantitas,
                data.satuan_kuantitas,
                data.harga_per_satuan_kuantitas,
                data.jumlah_box,
                data.unit,
                data.nik_plasma,
                data.npwp_plasma,
                data.nama_plasma,
                data.kandang_plasma,
                data.periode_chickin,
                data.periode,
                data.kode_unit,
                data.nama_unit,
                data.kode_accurate
            order by
                data.periode asc,
                data.nama_supplier asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getDataPembelianVoadip( $params ) {
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];

        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan .= "and prs.kode in ('".implode("', '", $perusahaan)."')";
        }

        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit .= "and w.kode in ('".implode("', '", $unit)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.tanggal,
                data.perusahaan,
                data.perusahaan_alias,
                data.kode_supplier,
                data.nama_supplier,
                data.nik,
                data.npwp,
                data.no_form,
                data.no_sj,
                data.no_faktur_pembelian,
                data.tanggal_faktur,
                data.tanggal_pengiriman,
                data.kode_barang,
                data.nama_barang,
                data.kuantitas,
                data.satuan_kuantitas,
                data.harga_per_satuan_kuantitas,
                data.kuantitas * data.harga_per_satuan_kuantitas as total,
                data.unit,
                data.tgl_terima,
                data.kode_unit,
                data.kode_accurate,
                data.total_tagihan
            from
            (
                select 
                    tv.tgl_terima as tanggal,
                    UPPER(prs.perusahaan) as perusahaan,
                    UPPER(prs.alias) as perusahaan_alias,
                    supl.nomor as kode_supplier,
                    UPPER(supl.nama) nama_supplier,
                    '123' as nik,
                    prs.npwp as npwp,
                    kv.no_order as no_form,
                    kv.no_sj,
                    '' as no_faktur_pembelian,
                    '' as tanggal_faktur,
                    kv.tgl_kirim as tanggal_pengiriman,
                    brg.kode as kode_barang,
                    UPPER(brg.nama) as nama_barang,
                    case
                        when dtv.id is not null then
                            dtv.jumlah
                        else
                            dkv.jumlah
                    end as kuantitas,
                    UPPER(brg.satuan) as satuan_kuantitas,
                    ds.hrg_beli as harga_per_satuan_kuantitas,
                    UPPER(REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', '')) as unit,
                    tv.tgl_terima as tgl_terima,
                    w.kode as kode_unit,
                    prs.kode_accurate,
                    ov_total.total as total_tagihan
                from det_kirim_voadip dkv
                left join
                    kirim_voadip kv 
                    on
                        dkv.id_header = kv.id
                left join
                    terima_voadip tv 
                    on
                        tv.id_kirim_voadip = kv.id
                left join
                    det_terima_voadip dtv 
                    on
                        dtv.id_header = tv.id and
                        dtv.item = dkv.item
                left join
                    (
                        select ov1.* from order_voadip ov1
                        right join
                            (select max(id) as id, no_order from order_voadip group by no_order) ov2
                            on
                                ov1.id = ov2.id
                    ) ov
                    on
                        ov.no_order  = kv.no_order
                left join
                    gudang g
                    on
                        kv.tujuan = g.id 
                left join
                    (
                        select p1.* from perusahaan p1
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) p2
                            on
                                p1.id = p2.id
                    ) prs
                    on
                        g.perusahaan = prs.kode
                left join
                    (
                        select p1.* from pelanggan p1
                        right join
                            (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) p2
                            on
                                p1.id = p2.id
                    ) supl
                    on
                        kv.asal = supl.nomor 
                left join
                    (
                        select brg1.* from barang brg1
                        right join
                            (select max(id) as id, kode from barang group by kode) brg2
                            on
                                brg1.id = brg2.id
                    ) brg
                    on
                        dkv.item = brg.kode
                left join
                    wilayah w 
                    on
                        w.kode = SUBSTRING(kv.no_order, 5, 3)
                left join
                    det_stok ds
                    on
                        ds.kode_barang = dtv.item and
                        ds.kode_trans = kv.no_order
                left join
                    (
                        select data.id_kirim, sum(data.jumlah * data.hrg_beli) as total from (
                            select kv.id as id_kirim, dtv.id_header, dtv.item, dtv.jumlah, ds.hrg_beli from det_terima_voadip dtv
                            left join
                                terima_voadip tv
                                on
                                    dtv.id_header = tv.id
                            left join
                                kirim_voadip kv
                                on
                                    tv.id_kirim_voadip = kv.id
                            left join
                                det_stok ds
                                on
                                    ds.kode_trans = kv.no_order and
                                    ds.kode_barang = dtv.item
                            group by
                                kv.id, dtv.id_header, dtv.item, dtv.jumlah, ds.hrg_beli
                        ) data
                        group by
                            data.id_kirim
                    ) ov_total
                    on
                        ov_total.id_kirim = kv.id
                where
                    kv.jenis_kirim = 'opks' and
                    -- tv.tgl_terima between '".$start_date." 00:00:00.001' and '".$end_date." 23:59:59.999' -- JIKA BERDASARKAN TANGGAL TERIMA
                    ov.tanggal between '".$start_date." 00:00:00.001' and '".$end_date." 23:59:59.999' -- JIKA BERDASARKAN TANGGAL ORDER
                    ".$sql_perusahaan."
                    ".$sql_unit."
            ) data
            group by
                data.tanggal,
                data.perusahaan,
                data.perusahaan_alias,
                data.kode_supplier,
                data.nama_supplier,
                data.nik,
                data.npwp,
                data.no_form,
                data.no_sj,
                data.no_faktur_pembelian,
                data.tanggal_faktur,
                data.tanggal_pengiriman,
                data.kode_barang,
                data.nama_barang,
                data.kuantitas,
                data.satuan_kuantitas,
                data.harga_per_satuan_kuantitas,
                data.unit,
                data.tgl_terima,
                data.kode_unit,
                data.kode_accurate,
                data.total_tagihan
            order by
                data.tanggal asc,
                data.nama_supplier asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getDataPembelianPakan( $params ) {
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];

        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan .= "and prs.kode in ('".implode("', '", $perusahaan)."')";
        }

        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit .= "and w.kode in ('".implode("', '", $unit)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.tanggal,
                data.perusahaan,
                data.perusahaan_alias,
                data.kode_supplier,
                data.nama_supplier,
                data.nik,
                data.npwp,
                data.no_form,
                data.no_sj,
                data.no_faktur_pembelian,
                data.tanggal_faktur,
                data.tanggal_pengiriman,
                data.kode_barang,
                data.nama_barang,
                data.kuantitas,
                data.satuan_kuantitas,
                data.harga_per_satuan_kuantitas,
                data.kuantitas * data.harga_per_satuan_kuantitas as total,
                data.unit,
                data.tgl_terima,
                data.kode_unit,
                data.kode_accurate,
                data.total_tagihan
            from
            (
                select 
                    tp.tgl_terima as tanggal,
                    UPPER(prs.perusahaan) as perusahaan,
                    UPPER(prs.alias) as perusahaan_alias,
                    supl.nomor as kode_supplier,
                    UPPER(supl.nama) nama_supplier,
                    '123' as nik,
                    prs.npwp as npwp,
                    kp.no_order as no_form,
                    kp.no_sj,
                    '' as no_faktur_pembelian,
                    '' as tanggal_faktur,
                    kp.tgl_kirim as tanggal_pengiriman,
                    brg.kode as kode_barang,
                    UPPER(brg.nama) as nama_barang,
                    case
                        when dtp.id is not null then
                            dtp.jumlah
                        else
                            dkp.jumlah
                    end as kuantitas,
                    'KG' as satuan_kuantitas,
                    ds.hrg_beli as harga_per_satuan_kuantitas,
                    UPPER(REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', '')) as unit,
                    tp.tgl_terima as tgl_terima,
                    w.kode as kode_unit,
                    prs.kode_accurate,
                    op_total.total as total_tagihan
                from det_kirim_pakan dkp
                left join
                    kirim_pakan kp 
                    on
                        dkp.id_header = kp.id
                left join
                    terima_pakan tp 
                    on
                        tp.id_kirim_pakan = kp.id
                left join
                    det_terima_pakan dtp 
                    on
                        dtp.id_header = tp.id and
                        dtp.item = dkp.item
                left join
                    (
                        select op1.* from order_pakan op1
                        right join
                            (select max(id) as id, no_order from order_pakan group by no_order) op2
                            on
                                op1.id = op2.id
                    ) op
                    on
                        op.no_order  = kp.no_order
                left join
                    gudang g
                    on
                        kp.tujuan = g.id 
                left join
                    (
                        select p1.* from perusahaan p1
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) p2
                            on
                                p1.id = p2.id
                    ) prs
                    on
                        g.perusahaan = prs.kode
                left join
                    (
                        select p1.* from pelanggan p1
                        right join
                            (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) p2
                            on
                                p1.id = p2.id
                    ) supl
                    on
                        kp.asal = supl.nomor 
                left join
                    (
                        select brg1.* from barang brg1
                        right join
                            (select max(id) as id, kode from barang group by kode) brg2
                            on
                                brg1.id = brg2.id
                    ) brg
                    on
                        dkp.item = brg.kode
                left join
                    wilayah w 
                    on
                        w.kode = SUBSTRING(kp.no_order, 5, 3)
                left join
                    det_stok ds
                    on
                        ds.kode_barang = dtp.item and
                        ds.kode_trans = kp.no_order 
                left join
                    (
                        select data.id_kirim, sum(data.jumlah * data.hrg_beli) as total from (
                            select kp.id as id_kirim, dtp.id_header, dtp.item, dtp.jumlah, ds.hrg_beli from det_terima_pakan dtp
                            left join
                                terima_pakan tp
                                on
                                    dtp.id_header = tp.id
                            left join
                                kirim_pakan kp
                                on
                                    tp.id_kirim_pakan = kp.id
                            left join
                                det_stok ds
                                on
                                    ds.kode_trans = kp.no_order and
                                    ds.kode_barang = dtp.item
                            group by
                                kp.id, dtp.id_header, dtp.item, dtp.jumlah, ds.hrg_beli
                        ) data
                        group by
                            data.id_kirim
                    ) op_total
                    on
                        op_total.id_kirim = kp.id
                where
                    kp.jenis_kirim = 'opks' and
                    -- tp.tgl_terima between '".$start_date."' and '".$end_date."' -- JIKA BERDASARKAN TANGGAL TERIMA
                    op.tgl_trans between '".$start_date."' and '".$end_date."' -- JIKA BERDASARKAN TANGGAL ORDER
                    ".$sql_perusahaan."
                    ".$sql_unit."
            ) data
            group by
                data.tanggal,
                data.perusahaan,
                data.perusahaan_alias,
                data.kode_supplier,
                data.nama_supplier,
                data.nik,
                data.npwp,
                data.no_form,
                data.no_sj,
                data.no_faktur_pembelian,
                data.tanggal_faktur,
                data.tanggal_pengiriman,
                data.kode_barang,
                data.nama_barang,
                data.kuantitas,
                data.satuan_kuantitas,
                data.harga_per_satuan_kuantitas,
                data.unit,
                data.tgl_terima,
                data.kode_unit,
                data.kode_accurate,
                data.total_tagihan
            order by
                data.tanggal asc,
                data.nama_supplier asc
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getDataReturPembelian( $params ) {
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];

        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan .= "and r.kode_perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.kode_supplier,
                data.nama_supplier,
                data.tanggal_retur,
                data.kode_barang,
                data.nama_barang,
                data.kuantitas,
                data.satuan_kuantitas,
                data.deskripsi_penyebab_retur
            from
            (
                select 
                    supl.nomor as kode_supplier,
                    UPPER(supl.nama) nama_supplier,
                    r.tgl_retur as tanggal_retur,
                    brg.kode as kode_barang,
                    UPPER(brg.nama) as nama_barang,
                    dr.jumlah as kuantitas,
                    UPPER(brg.satuan) as satuan_kuantitas,
                    cast(r.keterangan as varchar(250)) as deskripsi_penyebab_retur
                from
                (
                    select drp.*, 'pakan' as jenis from det_retur_pakan drp
                    
                    union all
                    
                    select drv.*, 'voadip' as jenis from det_retur_voadip drv
                ) dr
                left join
                    (
                        select rp.id, rp.tgl_retur, rp.jenis_retur, rp.no_order, rp.asal, rp.tujuan, rp.id_tujuan, rp.no_retur, rp.keterangan, 'pakan' as jenis, g.perusahaan as kode_perusahaan
                        from retur_pakan rp 
                        left join
                            gudang g
                            on
                                g.id = rp.id_asal
                        where 
                            rp.asal = 'gudang'
                        
                        union all
                        
                        select rv.id, rv.tgl_retur, rv.jenis_retur, rv.no_order, rv.asal, rv.tujuan, rv.id_tujuan, rv.no_retur, rv.keterangan, 'voadip' as jenis, g.perusahaan as kode_perusahaan
                        from retur_voadip rv 
                        left join
                            gudang g
                            on
                                g.id = rv.id_asal
                        where 
                            rv.asal = 'gudang'
                    ) r
                    on
                        r.id = dr.id_header and
                        r.jenis = dr.jenis
                left join
                    (
                        select p1.* from pelanggan p1
                        right join
                            (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) p2
                            on
                                p1.id = p2.id
                    ) supl
                    on
                        r.id_tujuan = supl.nomor 
                left join
                    (
                        select brg1.* from barang brg1
                        right join
                            (select max(id) as id, kode from barang group by kode) brg2
                            on
                                brg1.id = brg2.id
                    ) brg
                    on
                        dr.item = brg.kode
                where
                    dr.jumlah > 0 and
                    r.tgl_retur between '".$start_date."' and '".$end_date."' -- JIKA BERDASARKAN TANGGAL RETUR
                    ".$sql_perusahaan."
            ) data
            group by
                data.kode_supplier,
                data.nama_supplier,
                data.tanggal_retur,
                data.kode_barang,
                data.nama_barang,
                data.kuantitas,
                data.satuan_kuantitas,
                data.deskripsi_penyebab_retur
            order by
                data.tanggal_retur asc,
                data.nama_supplier asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getDataDistribusiVoadip( $params ) {
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];
        $tutup_siklus = $params['tutup_siklus'];

        $sql_tutup_siklus = "";
        if ( stristr($tutup_siklus, 'all') === false ) {
            if ( $tutup_siklus == '1' ) {
                $sql_tutup_siklus .= "and rhpp.tgl_tutup is not null";
            } else {
                $sql_tutup_siklus .= "and rhpp.tgl_tutup is null";
            }
        }

        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan .= "and g.perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit .= "and w.kode in ('".implode("', '", $unit)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.no_batch,
                data.tanggal_panen,
                data.jenis_pengiriman,
                data.unit,
                data.nama_plasma,
                data.kandang,
                data.periode,
                data.no_sj,
                data.kode_barang,
                data.kuantitas,
                data.satuan_kuantitas,
                data.gudang,
                data.departemen,
                data.kode_proyek
            from
            (
                select 
                    '' as no_batch,
                    '' as tanggal_panen,
                    kv.jenis_kirim as jenis_pengiriman,
                    UPPER(REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', '')) as unit,
                    UPPER(rdim.nama) as nama_plasma,
                    cast(SUBSTRING(kv.tujuan, 10, 2) as int) as kandang,
                    tv.tgl_terima as periode,
                    kv.no_sj as no_sj,
                    brg.kode as kode_barang,
                    dtv.jumlah as kuantitas,
                    UPPER(brg.satuan) as satuan_kuantitas,
                    CASE
                        WHEN kv.jenis_kirim = 'opkg' THEN
                            UPPER(g.nama)
                        ELSE
                            ''
                    END as gudang,
                    '' as departemen,
                    '' as kode_proyek
                from det_terima_voadip dtv 
                left join
                    terima_voadip tv 
                    on
                        dtv.id_header = tv.id
                left join
                    kirim_voadip kv 
                    on
                        tv.id_kirim_voadip = kv.id
                left join
                    (
                        select data.noreg, data.tgl_docin, max(tgl_tutup) as tgl_tutup
                        from
                        (
                            select ts.noreg, ts.tgl_docin, ts.tgl_tutup from tutup_siklus ts 
                            
                            union all
                            
                            select rgn.noreg, rgn.tgl_docin, rgh.tgl_submit as tgl_tutup from (
                                select rgn1.* from rhpp_group_noreg rgn1
                                right join
                                    (select max(id) as id, noreg from rhpp_group_noreg group by noreg) rgn2
                                    on
                                        rgn1.id = rgn2.id
                            ) rgn
                            left join
                                rhpp_group rg 
                                on
                                    rgn.id_header = rg.id
                            left join
                                rhpp_group_header rgh 
                                on
                                    rg.id_header = rgh.id
                            group by
                                rgn.noreg, rgn.tgl_docin, rgh.tgl_submit
                        ) data
                        group by
                            data.noreg, data.tgl_docin
                    ) rhpp
                    on
                        kv.tujuan = rhpp.noreg
                left join
                    (
                        select r.noreg, r.nim, m.ktp as nik, m.nama, m.npwp from rdim_submit r
                        left join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                r.nim = mm.nim
                        left join
                            mitra m
                            on
                                m.id = mm.mitra
                    ) rdim
                    on
                        kv.tujuan = rdim.noreg
                left join
                    (
                        select cast(id as varchar(15)) as id, nama, perusahaan from gudang
                        
                        union all
                        
                        select cast(r.noreg as varchar(15)) as id, m.nama, m.perusahaan from rdim_submit r
                        left join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                r.nim = mm.nim
                        left join
                            mitra m
                            on
                                m.id = mm.mitra
                    ) g
                    on
                        kv.asal = g.id 
                left join
                    (
                        select brg1.* from barang brg1
                        right join
                            (select max(id) as id, kode from barang group by kode) brg2
                            on
                                brg1.id = brg2.id
                    ) brg
                    on
                        dtv.item = brg.kode
                left join
                    wilayah w 
                    on
                        w.kode = SUBSTRING(kv.no_order, 4, 3)
                left join
                    det_stok ds
                    on
                        ds.kode_barang = dtv.item and
                        ds.kode_trans = kv.no_order 
                where
                    kv.jenis_tujuan = 'peternak' and
                    kv.jenis_kirim <> 'opks' and
                    tv.tgl_terima between '".$start_date."' and '".$end_date."' -- JIKA BERDASARKAN TANGGAL TERIMA
                    ".$sql_perusahaan."
                    ".$sql_unit."
                    ".$sql_tutup_siklus."
            ) data
            group by
                data.no_batch,
                data.tanggal_panen,
                data.jenis_pengiriman,
                data.unit,
                data.nama_plasma,
                data.kandang,
                data.periode,
                data.no_sj,
                data.kode_barang,
                data.kuantitas,
                data.satuan_kuantitas,
                data.gudang,
                data.departemen,
                data.kode_proyek
            order by
                data.periode asc,
                data.nama_plasma asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getDataDistribusiPakan( $params ) {
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];
        $tutup_siklus = $params['tutup_siklus'];

        $sql_tutup_siklus = "";
        if ( stristr($tutup_siklus, 'all') === false ) {
            if ( $tutup_siklus == '1' ) {
                $sql_tutup_siklus .= "and rhpp.tgl_tutup is not null";
            } else {
                $sql_tutup_siklus .= "and rhpp.tgl_tutup is null";
            }
        }

        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan .= "and g.perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit .= "and w.kode in ('".implode("', '", $unit)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.no_batch,
                data.tanggal_panen,
                data.jenis_pengiriman,
                data.unit,
                data.nama_plasma,
                data.kandang,
                data.periode,
                data.no_sj,
                data.kode_barang,
                data.kuantitas,
                data.satuan_kuantitas,
                data.gudang,
                data.departemen,
                data.kode_proyek
            from
            (
                select 
                    '' as no_batch,
                    '' as tanggal_panen,
                    kp.jenis_kirim as jenis_pengiriman,
                    UPPER(REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', '')) as unit,
                    UPPER(rdim.nama) as nama_plasma,
                    cast(SUBSTRING(kp.tujuan, 10, 2) as int) as kandang,
                    tp.tgl_terima as periode,
                    kp.no_sj as no_sj,
                    brg.kode as kode_barang,
                    dtp.jumlah as kuantitas,
                    'KG' as satuan_kuantitas,
                    UPPER(g.nama) as gudang,
                    '' as departemen,
                    '' as kode_proyek
                from det_terima_pakan dtp 
                left join
                    terima_pakan tp 
                    on
                        dtp.id_header = tp.id
                left join
                    kirim_pakan kp 
                    on
                        tp.id_kirim_pakan = kp.id
                left join
                    (
                        select data.noreg, data.tgl_docin, max(tgl_tutup) as tgl_tutup
                        from
                        (
                            select ts.noreg, ts.tgl_docin, ts.tgl_tutup from tutup_siklus ts 
                            
                            union all
                            
                            select rgn.noreg, rgn.tgl_docin, rgh.tgl_submit as tgl_tutup from (
                                select rgn1.* from rhpp_group_noreg rgn1
                                right join
                                    (select max(id) as id, noreg from rhpp_group_noreg group by noreg) rgn2
                                    on
                                        rgn1.id = rgn2.id
                            ) rgn
                            left join
                                rhpp_group rg 
                                on
                                    rgn.id_header = rg.id
                            left join
                                rhpp_group_header rgh 
                                on
                                    rg.id_header = rgh.id
                            group by
                                rgn.noreg, rgn.tgl_docin, rgh.tgl_submit
                        ) data
                        group by
                            data.noreg, data.tgl_docin
                    ) rhpp
                    on
                        kp.tujuan = rhpp.noreg
                left join
                    (
                        select r.noreg, r.nim, m.ktp as nik, m.nama, m.npwp from rdim_submit r
                        left join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                r.nim = mm.nim
                        left join
                            mitra m
                            on
                                m.id = mm.mitra
                    ) rdim
                    on
                        kp.tujuan = rdim.noreg
                left join
                    (
                        select cast(id as varchar(15)) as id, nama, perusahaan from gudang
                        
                        union all
                        
                        select cast(r.noreg as varchar(15)) as id, m.nama, m.perusahaan from rdim_submit r
                        left join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                r.nim = mm.nim
                        left join
                            mitra m
                            on
                                m.id = mm.mitra
                    ) g
                    on
                        kp.asal = g.id 
                left join
                    (
                        select brg1.* from barang brg1
                        right join
                            (select max(id) as id, kode from barang group by kode) brg2
                            on
                                brg1.id = brg2.id
                    ) brg
                    on
                        dtp.item = brg.kode
                left join
                    wilayah w 
                    on
                        w.kode = SUBSTRING(kp.no_order, 4, 3)
                left join
                    det_stok ds
                    on
                        ds.kode_barang = dtp.item and
                        ds.kode_trans = kp.no_order 
                where
                    kp.jenis_tujuan = 'peternak' and
                    kp.jenis_kirim <> 'opks' and
                    tp.tgl_terima between '".$start_date."' and '".$end_date."' -- JIKA BERDASARKAN TANGGAL TERIMA
                    ".$sql_perusahaan."
                    ".$sql_unit."
                    ".$sql_tutup_siklus."
            ) data
            group by
                data.no_batch,
                data.tanggal_panen,
                data.jenis_pengiriman,
                data.unit,
                data.nama_plasma,
                data.kandang,
                data.periode,
                data.no_sj,
                data.kode_barang,
                data.kuantitas,
                data.satuan_kuantitas,
                data.gudang,
                data.departemen,
                data.kode_proyek
            order by
                data.periode asc,
                data.nama_plasma asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getDataBayarPenjualan( $params ) {
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];
        
        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan .= "and pp.perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit .= "and w.kode in ('".implode("', '", $unit)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            SET NOCOUNT ON 
            SET ARITHABORT OFF
            SET ANSI_WARNINGS OFF

            create table #temp_jual (
                do varchar(10),
                kode_unit varchar(10),
                bank varchar(50),
                rekening varchar(50),
                no_bukti_lama varchar(max),
                no_bukti varchar(max),
                tgl_bayar date,
                transfer decimal(15, 2),
                kode_plg varchar(15),
                nama_plg varchar(100),
                nik varchar(100),
                npwp varchar(100),
                no_invoice varchar(max),
                potongan decimal(15, 2),
                status tinyint,
                keterangan varchar(max)
            )

            DECLARE @nominal decimal(15, 2)
            DECLARE @keterangan varchar(max)
            DECLARE @tbl_id int
            DECLARE @kode_perusahaan varchar(10)
            DECLARE @alias_prs varchar(10)
            DECLARE @bank_prs varchar(50)
            DECLARE @rekening_prs varchar(50)
            DECLARE @tgl_trans date
            DECLARE @nama_mitra varchar(100)
            DECLARE @saldo decimal(13, 2), @_saldo decimal(13, 2), @jumlah_transfer decimal(13, 2), @lebih_bayar decimal(13, 2), @nil_pajak decimal(13, 2), @lebih_bayar_non_saldo decimal(13, 2), @tgl_tagihan date, @bad_debt int, @tot_bayar decimal(13, 2), @no_bukti_auto varchar(50)
            DECLARE @no_do varchar(15), @jumlah_bayar decimal(13, 2), @kode_unit varchar(10), @no_sj varchar(15), @no_nota varchar(15), @no_plg varchar(15), @nama_plg varchar(100), @nik varchar(100), @npwp varchar(100)
            DECLARE @nota varchar(max)

            DECLARE pp_do CURSOR LOCAL FOR
                select 
                    pp.id,
                    pp.tgl_bayar as tgl_trans,
                    plg.nama as nama_mitra,
                    plg.nik,
                    plg.npwp,
                    pp.perusahaan as kode_perusahaan,
                    prs.alias as alias,
                    prs.bank,
                    prs.rekening,
                    pp.saldo as saldo,
                    pp.saldo as _saldo,
                    pp.jml_transfer as jumlah_transfer,
                    pp.lebih_kurang as lebih_bayar,
                    pp.nil_pajak as nil_pajak,
                    pp.non_saldo as lebih_bayar_non_saldo,
                    pp.bad_debt as bad_debt,
                    pp.total_bayar as tot_bayar,
                    dj.no_bukti as no_bukti_auto
                    -- pp.no_bukti_auto
                from pembayaran_pelanggan pp 
                left join
                    (
                        select p.* from pelanggan p
                        right join
                            ( select max(id) as id, nomor from pelanggan where tipe='pelanggan' group by nomor ) p1
                            on
                                p.id = p1.id
                        where
                            p.mstatus = 1 and
                            p.tipe = 'pelanggan'
                    ) plg
                    on
                        plg.nomor = pp.no_pelanggan
                left join
                    (
                        select prs1.* from perusahaan prs1
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) prs2
                            on
                                prs1.id = prs2.id
                    ) prs
                    on
                        prs.kode = pp.perusahaan
                left join
                    (select * from det_jurnal where tbl_name = 'pembayaran_pelanggan' and no_bukti is not null) dj
                    on 
                        pp.id = dj.tbl_id
                where
                    pp.tgl_bayar between '".$start_date."' and '".$end_date."'
                    ".$sql_perusahaan."
                group by
                    pp.id,
                    pp.tgl_bayar,
                    plg.nama,
                    plg.nik,
                    plg.npwp,
                    pp.perusahaan,
                    prs.alias,
                    prs.bank,
                    prs.rekening,
                    pp.saldo,
                    pp.saldo,
                    pp.jml_transfer,
                    pp.lebih_kurang,
                    pp.nil_pajak,
                    pp.non_saldo,
                    pp.bad_debt,
                    pp.total_bayar,
                    dj.no_bukti
                
            OPEN pp_do

            FETCH NEXT FROM pp_do INTO
                @tbl_id, @tgl_trans, @nama_mitra, @nik, @npwp, @kode_perusahaan, @alias_prs, @bank_prs, @rekening_prs, @saldo, @_saldo, @jumlah_transfer, @lebih_bayar, @nil_pajak, @lebih_bayar_non_saldo, @bad_debt, @tot_bayar, @no_bukti_auto

            WHILE @@FETCH_STATUS = 0
            BEGIN	
                DECLARE dpp_do CURSOR LOCAL FOR
                    select
                        drs.no_do,
                        dpp.jumlah_bayar,
                        w.kode as kode_unit,
                        drs.no_sj,
                        drs.no_nota,
                        drs.no_pelanggan,
                        plg.nama as nama_plg
                    from det_pembayaran_pelanggan dpp 
                    left join
                        det_real_sj drs 
                        on
                            dpp.id_do = drs.id
                    left join
                        real_sj rs 
                        on
                            drs.id_header = rs.id
                    left join
                        (
                            select p.* from pelanggan p
                            right join
                                ( select max(id) as id, nomor from pelanggan where tipe='pelanggan' group by nomor ) p1
                                on
                                    p.id = p1.id
                            where
                                p.mstatus = 1 and
                                p.tipe = 'pelanggan'
                        ) plg
                        on
                            plg.nomor = drs.no_pelanggan
                    left join
                        wilayah w 
                        on
                            w.id = rs.id_unit 
                    where 
                        dpp.jumlah_bayar > 0 and
                        dpp.id_header = @tbl_id
                        ".$sql_unit."
                    order by
                        drs.no_do asc
                        
                OPEN dpp_do
                
                FETCH NEXT FROM dpp_do INTO
                    @no_do, @jumlah_bayar, @kode_unit, @no_sj, @no_nota, @no_plg, @nama_plg
                
                WHILE @@FETCH_STATUS = 0
                BEGIN                    
                    SET @nota = @no_sj
                    IF ( @no_nota is not null )
                    BEGIN
                        SET @nota = @no_sj+'-'+@no_nota
                    END
                    
                    IF ( @nil_pajak > 0 )
                    BEGIN
                        SET @nominal = @nil_pajak
                            
                        IF ( @nil_pajak > @jumlah_bayar )
                        BEGIN
                            SET @nil_pajak = @nil_pajak - @jumlah_bayar
                            SET @jumlah_bayar = 0
                        END
                        ELSE
                        BEGIN
                            SET @jumlah_bayar = @jumlah_bayar - @nil_pajak
                            SET @nil_pajak = 0
                        END
                    END
                    
                    IF ( @lebih_bayar_non_saldo > 0 )
                    BEGIN
                        SET @nominal = @lebih_bayar_non_saldo
                            
                        IF ( @lebih_bayar_non_saldo > @jumlah_bayar )
                        BEGIN
                            SET @lebih_bayar_non_saldo = @lebih_bayar_non_saldo - @jumlah_bayar
                            SET @jumlah_bayar = 0
                        END
                        ELSE
                        BEGIN
                            SET @jumlah_bayar = @jumlah_bayar - @lebih_bayar_non_saldo
                            SET @lebih_bayar_non_saldo = 0
                        END
                    END
                    
                    IF ( @jumlah_bayar > 0  )
                    BEGIN
                        IF (@saldo > 0 and @lebih_bayar < @_saldo)
                        BEGIN
                            IF ( @saldo > @jumlah_bayar )
                            BEGIN
                                SET @nominal = @jumlah_bayar
                                
                                SET @saldo = @saldo - @jumlah_bayar
                                SET @jumlah_bayar = 0
                            END
                            ELSE
                            BEGIN
                                SET @nominal = @saldo
                                SET @jumlah_bayar = @jumlah_bayar - @saldo
                                
                                SET @saldo = 0
                            END
                                
                            IF ( @bad_debt = 1 )
                            BEGIN
                                SET @keterangan = 'BAD DEBT'
                            END
                            ELSE
                            BEGIN
                                SET @keterangan = null
                            END
                            
                            insert into #temp_jual (do, kode_unit, bank, rekening, no_bukti_lama, no_bukti, tgl_bayar, transfer, kode_plg, nama_plg, nik, npwp, no_invoice, potongan, status, keterangan) values
                            (@alias_prs, @kode_unit, @bank_prs, @rekening_prs, @no_bukti_auto, @no_bukti_auto, @tgl_trans, @jumlah_bayar, @no_plg, @nama_plg, @nik, @npwp, @nota, 0, 1, @keterangan)
                        END
                        ELSE
                        BEGIN
                            SET @nominal = @jumlah_bayar
                            
                            IF ( @bad_debt = 1 )
                            BEGIN
                                SET @keterangan = 'BAD DEBT'
                            END
                            ELSE
                            BEGIN
                                SET @keterangan = null
                            END
                            
                            insert into #temp_jual (do, kode_unit, bank, rekening, no_bukti_lama, no_bukti, tgl_bayar, transfer, kode_plg, nama_plg, nik, npwp, no_invoice, potongan, status, keterangan) values
                            (@alias_prs, @kode_unit, @bank_prs, @rekening_prs, @no_bukti_auto, @no_bukti_auto, @tgl_trans, @jumlah_bayar, @no_plg, @nama_plg, @nik, @npwp, @nota, 0, 1, @keterangan)
                        END
                    END
                    
                    FETCH NEXT FROM dpp_do INTO
                        @no_do, @jumlah_bayar, @kode_unit, @no_sj, @no_nota, @no_plg, @nama_plg
                END
                
                CLOSE dpp_do
                DEALLOCATE dpp_do
                
                IF ( @lebih_bayar > 0 and @_saldo <= @tot_bayar and @_saldo <> @lebih_bayar )
                BEGIN
                    IF ( @lebih_bayar > @_saldo )
                    BEGIN
                        SET @lebih_bayar = @lebih_bayar - @_saldo
                    END
                    
                    SET @keterangan = 'Lebih bayar pelanggan'
                    SET @nominal = @lebih_bayar
                    
                    insert into #temp_jual (do, kode_unit, bank, rekening, no_bukti_lama, no_bukti, tgl_bayar, transfer, kode_plg, nama_plg, nik, npwp, no_invoice, potongan, status, keterangan) values
                    (@alias_prs, @kode_unit, @bank_prs, @rekening_prs, @no_bukti_auto, @no_bukti_auto, @tgl_trans, @lebih_bayar, @no_plg, @nama_plg, @nik, @npwp, null, 0, 1, @keterangan)
                END
                
                FETCH NEXT FROM pp_do INTO
                    @tbl_id, @tgl_trans, @nama_mitra, @nik, @npwp, @kode_perusahaan, @alias_prs, @bank_prs, @rekening_prs, @saldo, @_saldo, @jumlah_transfer, @lebih_bayar, @nil_pajak, @lebih_bayar_non_saldo, @bad_debt, @tot_bayar, @no_bukti_auto
            END

            CLOSE pp_do
            DEALLOCATE pp_do

            select * from #temp_jual
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $this->data_bayar_jual = $data;

        return $data;
    }
    
    public function getDataBayarPembelian( $params ) {
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];
        $jenis = $params['kode_jenis'];
        
        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan .= "and rp.perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit .= "and konfir.kode_unit in ('".implode("', '", $unit)."')";
        }

        $sql_jenis = "";
        if ( !in_array('all', $jenis) ) {
            $sql_jenis .= "and konfir.jenis in ('".implode("', '", $jenis)."')";
        }

        $sql_condition = "";
        if ( !in_array('all', $perusahaan) ) {
            $q = 'where';
            if ( !empty($sql_condition) ) {
                $q = 'and';
            }
            $sql_condition .= $q." data.kode_prs in ('".implode("', '", $perusahaan)."')";
        }
        if ( !in_array('all', $unit) ) {
            $q = 'where';
            if ( !empty($sql_condition) ) {
                $q = 'and';
            }
            $sql_condition .= $q." data.kode_unit in ('".implode("', '", $unit)."')";
        }
        if ( !in_array('all', $jenis) ) {
            $q = 'where';
            if ( !empty($sql_condition) ) {
                $q = 'and';
            }
            $sql_condition .= $q." data.jenis in ('".implode("', '", $jenis)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            SET NOCOUNT ON 
            SET ARITHABORT OFF
            SET ANSI_WARNINGS OFF

            create table #temp_doc (
                kode_unit varchar(5),
                nomor varchar(15),
                no_invoice varchar(250),
                total decimal(15, 2),
                id_real int,
                sisa_tagihan decimal(15, 2)
            )

            create table #temp_pakan (
                kode_unit varchar(5),
                nomor varchar(15),
                no_invoice varchar(250),
                total decimal(15, 2),
                id_real int,
                sisa_tagihan decimal(15, 2)
            )

            create table #temp_ovk (
                kode_unit varchar(5),
                nomor varchar(15),
                no_invoice varchar(250),
                total decimal(15, 2),
                id_real int,
                sisa_tagihan decimal(15, 2)
            )

            DECLARE @d_id_real int, @d_nomor varchar(25), @d_tgl_bayar date, @d_no_bayar varchar(25), @d_tagihan decimal(15, 2), @d_bayar decimal(15, 2), @d_transaksi varchar(10)
            DECLARE @d_bayar_prev decimal(15, 2)
            DECLARE @k_tgl_sj date, @k_no_sj varchar(250), @k_total decimal(15, 2), @k_kode_unit varchar(5)
            DECLARE @sisa_tagihan decimal(15, 2), @bayar decimal(15, 2), @d_sisa_tagihan decimal(15, 2)

            DECLARE d_real_bayar CURSOR  LOCAL FOR
                select rp.id, rp.nomor, rp.tgl_bayar, rpd.no_bayar, rpd.tagihan, rpd.bayar, rpd.transaksi from realisasi_pembayaran_det rpd 
                left join
                    realisasi_pembayaran rp 
                    on
                        rpd.id_header = rp.id
                where
                    rp.tgl_bayar between '".$start_date."' and '".$end_date."' and
                    rpd.transaksi in ('DOC', 'PAKAN', 'VOADIP')
                group by
                    rp.id, rp.nomor, rp.tgl_bayar, rpd.no_bayar, rpd.tagihan, rpd.bayar, rpd.transaksi
                order by
                    rp.tgl_bayar asc,
                    rp.nomor asc
                    
            OPEN d_real_bayar
            FETCH NEXT FROM d_real_bayar INTO 
                @d_id_real, @d_nomor, @d_tgl_bayar, @d_no_bayar, @d_tagihan, @d_bayar, @d_transaksi
                    
            WHILE @@FETCH_STATUS = 0
            BEGIN	
                select 
                    @d_bayar_prev = cast(isnull(sum(rpd.bayar), 0) as decimal(15, 2))
                from realisasi_pembayaran_det rpd 
                left join
                    realisasi_pembayaran rp 
                    on
                        rpd.id_header = rp.id
                where
                    rpd.no_bayar = @d_no_bayar
                    and 
                    (
                        (
                            (
                                select count(*) 
                                from realisasi_pembayaran_det rpd2
                                left join
                                    realisasi_pembayaran rp2
                                    on
                                        rpd2.id_header = rp2.id
                                where 
                                    rpd2.no_bayar = @d_no_bayar and 
                                    rp2.tgl_bayar = @d_tgl_bayar
                            ) > 1 and 
                            SUBSTRING(rp.nomor, 5, 11) < SUBSTRING(@d_nomor, 5, 11) 
                        )
                        or
                        (
                            rp.tgl_bayar < @d_tgl_bayar
                        )
                    )
                    
                DECLARE d_konfir_bayar CURSOR  LOCAL FOR
                    select * from
                    (
                        select kppd.tgl_sj, kppd.no_sj, kppd.total, kppd.kode_unit from konfirmasi_pembayaran_pakan_det kppd 
                        left join
                            konfirmasi_pembayaran_pakan kpp 
                            on
                                kppd.id_header = kpp.id
                        where
                            kpp.nomor = @d_no_bayar
                            
                        union all
                        
                        select kpdd.tgl_order as tgl_sj, kpdd.no_order as no_sj, kpdd.total, kpdd.kode_unit from konfirmasi_pembayaran_doc_det kpdd 
                        left join
                            konfirmasi_pembayaran_doc kpd 
                            on
                                kpdd.id_header = kpd.id
                        where
                            kpd.nomor = @d_no_bayar

                        union all
                        
                        select kpvd.tgl_sj, kpvd.no_order as no_sj, kpvd.total, kpvd.kode_unit from konfirmasi_pembayaran_voadip_det kpvd 
                        left join
                            konfirmasi_pembayaran_voadip kpv 
                            on
                                kpvd.id_header = kpv.id
                        where
                            kpv.nomor = @d_no_bayar
                    ) data
                    order by
                        data.tgl_sj asc,
                        data.no_sj asc
                        
                OPEN d_konfir_bayar
                FETCH NEXT FROM d_konfir_bayar INTO 
                    @k_tgl_sj, @k_no_sj, @k_total, @k_kode_unit
                        
                WHILE @@FETCH_STATUS = 0
                BEGIN  
                    IF ( @k_total <= @d_bayar_prev )
                    BEGIN
                        SET @d_bayar_prev = @d_bayar_prev - @k_total
                    END
                    ELSE
                    BEGIN 
                        SET @sisa_tagihan = @k_total - @d_bayar_prev
                        SET @d_bayar_prev = 0
                        SET @d_sisa_tagihan = @sisa_tagihan
                        
                        IF (@sisa_tagihan < @d_bayar)
                        BEGIN
                            SET @bayar = @sisa_tagihan
                            SET @d_bayar = @d_bayar - @sisa_tagihan
                        END
                        ELSE
                        BEGIN
                            SET @bayar = @d_bayar
                            SET @sisa_tagihan = @sisa_tagihan - @d_bayar
                            SET @d_bayar = 0
                        END
                        
                        IF ( @bayar > 0 )
                        BEGIN	            
                            IF ( @d_transaksi = 'PAKAN' )
                            BEGIN
                                insert into #temp_pakan (kode_unit, nomor, no_invoice, total, id_real, sisa_tagihan) values
                                (@k_kode_unit, @d_no_bayar, @k_no_sj, @bayar, @d_id_real, @d_sisa_tagihan)
                            END
                            ELSE IF ( @d_transaksi = 'DOC' )
                            BEGIN
                                insert into #temp_doc (kode_unit, nomor, no_invoice, total, id_real, sisa_tagihan) values
                                (@k_kode_unit, @d_no_bayar, @k_no_sj, @bayar, @d_id_real, @d_sisa_tagihan)				
                            END
                            ELSE
                            BEGIN
                                insert into #temp_ovk (kode_unit, nomor, no_invoice, total, id_real, sisa_tagihan) values
                                (@k_kode_unit, @d_no_bayar, @k_no_sj, @bayar, @d_id_real, @d_sisa_tagihan)				
                            END
                        END
                    END
                    
                    FETCH NEXT FROM d_konfir_bayar INTO 
                        @k_tgl_sj, @k_no_sj, @k_total, @k_kode_unit
                END
                        
                CLOSE d_konfir_bayar
                DEALLOCATE d_konfir_bayar
                
                FETCH NEXT FROM d_real_bayar INTO 
                    @d_id_real, @d_nomor, @d_tgl_bayar, @d_no_bayar, @d_tagihan, @d_bayar, @d_transaksi
            END
                    
            CLOSE d_real_bayar
            DEALLOCATE d_real_bayar

            select * from
            (
                select
                    prs.kode as kode_prs,
                    prs.alias as do,
                    konfir.kode_unit,
                    prs.bank,
                    prs.rekening,
                    rp.no_bukti as no_bukti_lama,
                    rp.no_bukti_auto as no_bukti,
                    rp.tgl_bayar,
                    rp.jml_transfer as transfer,
                    konfir.sisa_tagihan,
                    konfir.total as bayar,
                    /*
                    case
                        when rpd.transaksi like 'doc' or rpd.transaksi like 'pakan' then
                            konfir.total
                        else
                            case
                                when rp.tgl_bayar < '2024-08-01' then
                                    konfir.total
                                else
                                    rpd.transfer
                            end
                    end as transfer,
                    */
                    -- konfir.total as transfer,
                    rp.supplier as kode_supl,
                    supl.nama as nama_supl,
                    konfir.no_invoice,
                    (rp.cn + rp.potongan) as potongan,
                    case
                        when rp.id is not null then
                            1
                        else
                            0
                    end as status,
                    konfir.jenis,
                    case 
                        when isnull(rp.cn, 0) > 0 then
                            'CREDIT NOTE'
                        else
                            null
                    end ket_cn,
                    case 
                        when isnull(rp.potongan, 0) > 0 then
                            rp_potongan.keterangan
                        else
                            null
                    end ket_potongan,
                    rp.id
                from realisasi_pembayaran_det rpd
                left join
                    realisasi_pembayaran rp 
                    on
                        rpd.id_header = rp.id
                left join
                    (
                        select DISTINCT 
                            _rpp.id_header, 
                            keterangan = substring ((
                                select ', '+djt.nama from realisasi_pembayaran_potongan rpp
                                left join
                                    det_jurnal_trans djt 
                                    on
                                        rpp.det_jurnal_trans_id = djt.id
                                where
                                    rpp.id_header = _rpp.id_header and
                                    rpp.nominal > 0
                                FOR XML path('')
                            , elements), 3, 500) 
                        from realisasi_pembayaran_potongan _rpp
                        where
                            _rpp.nominal > 0
                    ) rp_potongan
                    on
                        rp_potongan.id_header = rp.id
                left join
                    (
                        select prs1.* from perusahaan prs1
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) prs2
                            on
                                prs1.id = prs2.id
                    ) prs
                    on
                        prs.kode = rp.perusahaan
                left join
                    (
                        select
                            cast(td.kode_unit as varchar(5)) COLLATE Latin1_General_CI_AS as kode_unit,
                            cast(td.nomor as varchar(25)) COLLATE Latin1_General_CI_AS as nomor,
                            cast(td.no_invoice as varchar(250)) COLLATE Latin1_General_CI_AS as no_invoice,
                            td.total,
                            td.id_real,
                            td.sisa_tagihan,
                            'DOC' as jenis
                        from #temp_doc td

                        /*
                        select
                            kpdd.kode_unit,
                            kpd.nomor,
                            td.no_sj as no_invoice,
                            case
                                when rp.jml_transfer > rp.jml_bayar then
                                    (rp.jml_transfer / rp.populasi) * kpdd.populasi
                                else
                                    kpdd.total
                            end as total,
                            rpd.id_header as id_real,
                            'DOC' as jenis
                        from konfirmasi_pembayaran_doc_det kpdd
                        left join
                            konfirmasi_pembayaran_doc kpd
                            on
                                kpdd.id_header = kpd.id
                        left join
                            realisasi_pembayaran_det rpd
                            on
                                kpd.nomor = rpd.no_bayar
                        left join
                            (
                                select rp.id, rp.jml_transfer, rp.tgl_bayar, sum(isnull(rpd.bayar, 0)) as jml_bayar, sum(isnull(kpdd.populasi, 0)) as populasi from realisasi_pembayaran rp
                                left join
                                    realisasi_pembayaran_det rpd
                                    on
                                        rp.id = rpd.id_header
                                left join
                                    konfirmasi_pembayaran_doc kpd
                                    on
                                        rpd.no_bayar = kpd.nomor
                                left join
                                    konfirmasi_pembayaran_doc_det kpdd
                                    on
                                        kpd.id = kpdd.id_header
                                where
                                    rpd.transaksi = 'DOC'
                                group by
                                    rp.id, rp.jml_transfer, rp.tgl_bayar
                            ) rp
                            on
                                rpd.id_header = rp.id
                        left join
                            (
                                select td1.* from terima_doc td1
                                right join 
                                    ( select max(id) as id, no_order from terima_doc group by no_order ) td2
                                    on
                                        td1.id = td2.id
                            ) td
                            on
                                td.no_order = kpdd.no_order
                        group by
                            kpdd.kode_unit,
                            kpd.nomor,
                            td.no_sj,
                            kpdd.total,
                            rpd.id_header,
                            rp.jml_transfer,
                            rp.jml_bayar,
                            rp.populasi,
                            kpdd.populasi
                        */
                        
                        union all

                        select
                            cast(tp.kode_unit as varchar(5)) COLLATE Latin1_General_CI_AS as kode_unit,
                            cast(tp.nomor as varchar(25)) COLLATE Latin1_General_CI_AS as nomor,
                            cast(tp.no_invoice as varchar(250)) COLLATE Latin1_General_CI_AS as no_invoice,
                            tp.total,
                            tp.id_real,
                            tp.sisa_tagihan,
                            'PAKAN' as jenis
                        from #temp_pakan tp

                        /*
                        select
                            kppd.kode_unit,
                            kpp.nomor,
                            kppd.no_sj as no_invoice,
                            0 as total
                        from konfirmasi_pembayaran_pakan_det kppd
                        left join
                            konfirmasi_pembayaran_pakan kpp
                            on
                                kppd.id_header = kpp.id
                        group by
                            kppd.kode_unit,
                            kpp.nomor,
                            kppd.no_sj
                        */
                        
                        union all

                        select
                            cast(tv.kode_unit as varchar(5)) COLLATE Latin1_General_CI_AS as kode_unit,
                            cast(tv.nomor as varchar(25)) COLLATE Latin1_General_CI_AS as nomor,
                            cast(tv.no_invoice as varchar(250)) COLLATE Latin1_General_CI_AS as no_invoice,
                            tv.total,
                            tv.id_real,
                            tv.sisa_tagihan,
                            'OVK' as jenis
                        from #temp_ovk tv

                        /*
                        select
                            kpvd.kode_unit,
                            kpv.nomor,
                            kpvd.no_sj as no_invoice,
                            kpvd.total as total,
                            rpd.id_header as id_real,
                            'OVK' as jenis
                        from konfirmasi_pembayaran_voadip_det kpvd
                        left join
                            konfirmasi_pembayaran_voadip kpv
                            on
                                kpvd.id_header = kpv.id
                        left join
                            realisasi_pembayaran_det rpd
                            on
                                kpv.nomor = rpd.no_bayar
                        group by
                            kpvd.kode_unit,
                            kpv.nomor,
                            kpvd.no_sj,
                            kpvd.total,
                            rpd.id_header
                        */
                    ) konfir
                    on
                        konfir.nomor = rpd.no_bayar and
                        konfir.id_real = rpd.id_header
                left join
                    (
                        select plg1.* from pelanggan plg1
                        right join
                            (select max(id) as id, nomor from pelanggan p where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                            on
                                plg1.id = plg2.id
                    ) supl
                    on
                        supl.nomor = rp.supplier
                where
                    rpd.transaksi not in ('OA PAKAN', 'PLASMA') and
                    rp.tgl_bayar between '".$start_date."' and '".$end_date."' and
                    konfir.jenis is not null
                group by
                    prs.kode,
                    prs.alias,
                    konfir.kode_unit,
                    prs.bank,
                    prs.rekening,
                    rp.no_bukti,
                    rp.no_bukti_auto,
                    rp.tgl_bayar,
                    rp.jml_transfer,
                    konfir.sisa_tagihan,
                    konfir.total,
                    rpd.transaksi,
                    rpd.transfer,
                    rp.supplier,
                    supl.nama,
                    konfir.no_invoice,
                    rp.cn,
                    rp.potongan,
                    rp.id,
                    konfir.jenis,
                    rp_potongan.keterangan
                
                union all

                select 
                    prs.kode as kode_prs,
                    prs.alias as do,
                    SUBSTRING(bp.no_order, 5, 3) kode_unit,
                    prs.bank,
                    prs.rekening,
                    '-' as no_bukti_lama,
                    '-' as no_bukti,
                    bp.tgl_bayar,
                    bp.jml_bayar as transfer,
                    bp.jml_tagihan as sisa_tagihan,
                    bp.tot_bayar as bayar,
                    op.supplier as kode_supl,
                    supl.nama as nama_supl,
                    bp.no_faktur as no_invoice,
                    0 as potongan,
                    1 as status,
                    'PERALATAN' as jenis,
                    '' as ket_cn,
                    '' as ket_potongan,
                    bp.id as id
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
                where
                    bp.tgl_bayar between '".$start_date."' and '".$end_date."'

                union all

                select
                    prs.kode as kode_prs,
                    prs.alias as do,
                    case
                        when dj.unit like '%pusat%' then
                            'PUSAT'
                        else
                            dj.unit
                    end as kode_unit,
                    prs.bank,
                    prs.rekening,
                    '-' as no_bukti_lama,
                    '-' as no_bukti,
                    dj.tanggal as tgl_bayar,
                    dj.nominal as transfer,
                    dj.nominal as sisa_tagihan,
                    dj.nominal as bayar,
                    dj.supplier as kode_supl,
                    supl.nama as nama_supl,
                    dj.invoice as no_invoice,
                    0 as potongan,
                    1 as status,
                    'PERALATAN' as jenis,
                    '' as ket_cn,
                    '' as ket_potongan,
                    dj.id as id
                from det_jurnal dj
                left join
                    (
                        select p2.* from pelanggan p2
                        right join
                            (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) p1
                            on
                                p2.id = p1.id
                    ) supl
                    on
                        dj.supplier = supl.nomor
                left join
                    (
                        select prs1.* from perusahaan prs1
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) prs2
                            on
                                prs1.id = prs2.id
                    ) prs
                    on
                        dj.perusahaan = prs.kode
                where 
                    dj.coa_asal in ('110202', '110201') and  
                    dj.coa_tujuan in ('510302', '820232') and
                    dj.tanggal between '".$start_date."' and '".$end_date."'
            ) data
            ".$sql_condition."
            -- and data.sisa_tagihan <> data.bayar
            order by
                data.tgl_bayar asc,
                data.jenis asc,
                data.id asc,
                data.no_invoice asc
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        // cetak_r( $data, 1 );

        return $data;
    }

    public function getDataBayarOa( $params ) {
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];
        
        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan .= "and rp.perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit .= "and konfir.kode_unit in ('".implode("', '", $unit)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                prs.alias as do,
                konfir.kode_unit,
                prs.bank,
                prs.rekening,
                rp.no_bukti as no_bukti_lama,
                rp.no_bukti as no_bukti,
                rp.tgl_bayar,
                case
                    when rpd_tot_bayar.bayar < rp.jml_transfer then
                        rp.jml_transfer
                    else
                        rpd.transfer
                end as transfer,
                -- rpd.transfer,
                eks.nik as kode_eks,
                eks.nama as nama_eks,
                konfir.no_invoice,
                (isnull(rpd.cn, 0) + isnull(rpd.potongan, 0) + isnull(konfir.materai, 0)) as potongan,
                FLOOR(konfir.nominal_pajak) as nominal_pajak,
                'OA TRUK '+eks.nama+' - '+konfir.kode_unit+' - '+cast(cast(SUBSTRING(cast(konfir.min_tgl as varchar(10)), 9, 2) as int) as varchar(2))+'/'+cast(cast(SUBSTRING(cast(konfir.min_tgl as varchar(10)), 6, 2) as int) as varchar(2))+'-'+cast(cast(SUBSTRING(cast(konfir.max_tgl as varchar(10)), 9, 2) as int) as varchar(2))+'/'+cast(cast(SUBSTRING(cast(konfir.max_tgl as varchar(10)), 6, 2) as int) as varchar(2)) as keterangan,
                case
                    when rp.id is not null then
                        1
                    else
                        0
                end as status,
                case 
                    when isnull(rpd.cn, 0) > 0 then
                        'CREDIT NOTE'
                    else
                        null
                end ket_cn,
                case 
                    when isnull(rpd.potongan, 0) > 0 then
                        rp_potongan.keterangan
                    else
                        null
                end ket_potongan,
                case 
                    when isnull(konfir.materai, 0) > 0 then
                        'MATERAI'
                    else
                        null
                end ket_materai
            from realisasi_pembayaran_det rpd
            left join
                (select sum(bayar) as bayar, id_header from realisasi_pembayaran_det group by id_header) rpd_tot_bayar
                on
                    rpd_tot_bayar.id_header = rpd.id_header
            left join
                realisasi_pembayaran rp 
                on
                    rpd.id_header = rp.id
            left join
                (
                    select DISTINCT 
                        _rpp.id_header, 
                        keterangan = substring ((
                            select ', '+djt.nama from realisasi_pembayaran_potongan rpp
                            left join
                                det_jurnal_trans djt 
                                on
                                    rpp.det_jurnal_trans_id = djt.id
                            where
                                rpp.id_header = _rpp.id_header and
                                rpp.nominal > 0
                            FOR XML path('')
                        , elements), 3, 500) 
                    from realisasi_pembayaran_potongan _rpp
                    where
                        _rpp.nominal > 0
                ) rp_potongan
                on
                    rp_potongan.id_header = rp.id
            left join
                (
                    select prs1.* from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    prs.kode = rp.perusahaan
            left join
                (
                    select
                        w.kode as kode_unit,
                        kpop.nomor,
                        kpop.invoice as no_invoice,
                        kpop.potongan_pph_23 as nominal_pajak,
                        kpop.materai,
                        min(kpopd.tgl_mutasi) as min_tgl,
                        max(kpopd.tgl_mutasi) as max_tgl
                    from konfirmasi_pembayaran_oa_pakan_det kpopd
                    left join
                        konfirmasi_pembayaran_oa_pakan kpop
                        on
                            kpopd.id_header = kpop.id
                    left join
                        (
                            select no_sj, tujuan from kirim_pakan kp

                            union all

                            select no_retur as no_sj, id_tujuan as tujuan from retur_pakan rp
                        ) kp 
                        on
                            kp.no_sj = kpopd.no_sj
                    left join
                        (
                            select cast(id as varchar(20)) as id, unit from gudang g where unit is not null
                            
                            union all
                            
                            select
                                cast(rs.noreg as varchar(20)) as id,
                                k.unit 
                            from rdim_submit rs 
                            left join
                                kandang k
                                on
                                    rs.kandang = k.id
                            group by
                                rs.noreg,
                                k.unit 
                        ) tujuan
                        on
                            kp.tujuan = tujuan.id
                    left join
                        wilayah w
                        on
                            w.id = tujuan.unit
                    group by
                        w.kode,
                        kpop.nomor,
                        kpop.invoice,
                        kpop.potongan_pph_23,
                        kpop.materai
                ) konfir
                on
                    konfir.nomor = rpd.no_bayar
            left join
                (
                    select eks1.* from ekspedisi eks1
                    right join
                        (select max(id) as id, nomor from ekspedisi e group by nomor) eks2
                        on
                            eks1.id = eks2.id
                ) eks
                on
                    eks.nomor = rp.ekspedisi
            where
                rpd.transaksi = 'OA PAKAN' and
                rp.tgl_bayar between '".$start_date."' and '".$end_date."'
                ".$sql_perusahaan."
                ".$sql_unit."
            order by
                rp.tgl_bayar asc
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getDataBayarMaklon( $params ) {
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];
        
        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan .= "and rp.perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit .= "and konfir.kode_unit in ('".implode("', '", $unit)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                prs.alias as do,
                konfir.kode_unit,
                prs.bank,
                prs.rekening,
                rp.no_bukti as no_bukti_lama,
                rp.no_bukti as no_bukti,
                rp.tgl_bayar,
                rp.jml_transfer as transfer,
                mitra.ktp as kode_peternak,
                mitra.nama as nama_peternak,
                konfir.no_invoice,
                sum((isnull(rpd.cn, 0) + isnull(rpd.potongan, 0) + isnull(konfir.total_potongan, 0))) as potongan,
                FLOOR(konfir.nominal_pajak) as nominal_pajak,
                'RHPP '+mitra.nama+konfir.periode+'/'+konfir.kode_unit as keterangan,
                case
                    when rp.id is not null then
                        1
                    else
                        0
                end as status,
                case 
                    when sum(isnull(rpd.cn, 0)) > 0 then
                        'CREDIT NOTE'
                    else
                        null
                end ket_cn,
                case 
                    when sum(isnull(rpd.potongan, 0)) > 0 then
                        rp_potongan.keterangan
                    else
                        null
                end ket_potongan,
                case 
                    when sum(isnull(konfir.total_potongan, 0)) > 0 then
                        case
                            when konfir.ket_potongan is not null and konfir.ket_piutang is not null then
                                konfir.ket_potongan+', '+konfir.ket_piutang
                            when konfir.ket_potongan is not null and konfir.ket_piutang is null then
                                konfir.ket_potongan
                            when konfir.ket_potongan is null and konfir.ket_piutang is not null then
                                konfir.ket_piutang
                        end
                    else
                        null
                end ket_potongan2
            from realisasi_pembayaran_det rpd
            left join
                realisasi_pembayaran rp 
                on
                    rpd.id_header = rp.id
            left join
                (
                    select DISTINCT 
                        _rpp.id_header, 
                        keterangan = substring ((
                            select ', '+djt.nama from realisasi_pembayaran_potongan rpp
                            left join
                                det_jurnal_trans djt 
                                on
                                    rpp.det_jurnal_trans_id = djt.id
                            where
                                rpp.id_header = _rpp.id_header and
                                rpp.nominal > 0
                            FOR XML path('')
                        , elements), 3, 500) 
                    from realisasi_pembayaran_potongan _rpp
                    where
                        _rpp.nominal > 0
                ) rp_potongan
                on
                    rp_potongan.id_header = rp.id
            left join
                (
                    select prs1.* from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    prs.kode = rp.perusahaan
            left join
                (
                    select
                        w.kode as kode_unit,
                        kpp.nomor,
                        kpp.invoice as no_invoice,
                        rhpp.potongan_pajak as nominal_pajak,
                        rhpp.periode,
                        rhpp.total_potongan,
                        rhpp.ket_potongan,
                        rhpp.ket_piutang
                    from konfirmasi_pembayaran_peternak_det kppd
                    left join
                        konfirmasi_pembayaran_peternak kpp
                        on
                            kppd.id_header = kpp.id
                    left join
                        (
                            select 
                                'RHPP' as jenis,
                                r.id,
                                r.potongan_pajak,
                                r.total_potongan + isnull(r_piutang.nominal, 0) as total_potongan,
                                '-'+cast(cast(SUBSTRING(r.noreg, 8, 2) as int) as varchar(2)) as periode,
                                k.unit,
                                rp.keterangan as ket_potongan,
                                r_piutang.keterangan as ket_piutang
                            from rhpp r
                            left join
                                rdim_submit rs 
                                on
                                    r.noreg = rs.noreg
                            left join
                                kandang k 
                                on
                                    k.id = rs.kandang
                            left join
                                (
                                    select DISTINCT 
                                        _rp.id_header, 
                                        keterangan = substring ((
                                            select ', '+cast(rp.keterangan as varchar(max)) from rhpp_potongan rp
                                            where
                                                rp.id_header = _rp.id_header
                                            FOR XML path('')
                                        , elements), 3, 500) 
                                    from rhpp_potongan _rp
                                ) rp
                                on
                                    rp.id_header = r.id
                            left join
                                (
                                    select DISTINCT 
                                        _rp.id_header,
                                        _rp.nominal,
                                        keterangan = substring ((
                                            select ', '+cast(p.keterangan as varchar(max)) from rhpp_piutang rp
                                            left join
                                                piutang p 
                                                on
                                                    rp.piutang_kode = p.kode
                                            where
                                                rp.nominal > 0 and
                                                rp.id_header = _rp.id_header
                                            FOR XML path('')
                                        , elements), 3, 500) 
                                    from rhpp_piutang _rp
                                    where
                                        _rp.nominal > 0
                                ) r_piutang
                                on
                                    r_piutang.id_header = r.id
                            where 
                                r.jenis = 'rhpp_plasma' and
                                not exists (select * from rhpp_group_noreg where noreg = r.noreg)

                            union all

                            select 
                                'RHPP GROUP' as jenis,
                                rg.id,
                                rg.potongan_pajak,
                                rg.total_potongan + isnull(rg_piutang.nominal, 0) as total_potongan,
                                '' as periode,
                                rgn.unit,
                                rgp.keterangan as ket_potongan,
                                rg_piutang.keterangan as ket_piutang
                            from rhpp_group rg
                            left join
                                (
                                    select
                                        rgn.id_header,
                                        k.unit
                                    from rhpp_group_noreg rgn
                                    left join
                                        rdim_submit rs 
                                        on
                                            rgn.noreg = rs.noreg
                                    left join
                                        kandang k
                                        on
                                            k.id = rs.kandang
                                    group by
                                        rgn.id_header,
                                        k.unit
                                ) rgn
                                on
                                    rgn.id_header = rg.id
                            left join
                                (
                                    select DISTINCT 
                                        _rgp.id_header, 
                                        keterangan = substring ((
                                            select ', '+cast(rgp.keterangan as varchar(max)) from rhpp_group_potongan rgp
                                            where
                                                rgp.id_header = _rgp.id_header
                                            FOR XML path('')
                                        , elements), 3, 500) 
                                    from rhpp_group_potongan _rgp
                                ) rgp
                                on
                                    rgp.id_header = rg.id
                            left join
                                (
                                    select DISTINCT 
                                        _rgp.id_header, 
                                        _rgp.nominal,
                                        keterangan = substring ((
                                            select ', '+cast(p.keterangan as varchar(max)) from rhpp_group_piutang rgp
                                            left join
                                                piutang p 
                                                on
                                                    rgp.piutang_kode = p.kode
                                            where
                                                rgp.nominal > 0 and
                                                rgp.id_header = _rgp.id_header
                                            FOR XML path('')
                                        , elements), 3, 500) 
                                    from rhpp_piutang _rgp
                                    where
                                        _rgp.nominal > 0
                                ) rg_piutang
                                on
                                    rg_piutang.id_header = rg.id
                            where
                                rg.jenis = 'rhpp_plasma'
                        ) rhpp
                        on
                            kppd.id_trans = rhpp.id and
                            kppd.jenis = rhpp.jenis
                    left join
                        wilayah w
                        on
                            w.id = rhpp.unit
                    group by
                        w.kode,
                        kpp.nomor,
                        kpp.invoice,
                        rhpp.potongan_pajak,
                        rhpp.periode,
                        rhpp.total_potongan,
                        rhpp.ket_potongan,
                        rhpp.ket_piutang
                ) konfir
                on
                    konfir.nomor = rpd.no_bayar
            left join
                (
                    select mtr1.* from mitra mtr1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) mtr2
                        on
                            mtr1.id = mtr2.id
                ) mitra
                on
                    mitra.nomor = rp.peternak
            where
                rpd.transaksi = 'PLASMA' and
                rp.tgl_bayar between '".$start_date."' and '".$end_date."'
                ".$sql_perusahaan."
                ".$sql_unit."
            group by
                prs.alias,
                konfir.kode_unit,
                prs.bank,
                prs.rekening,
                rp.no_bukti,
                rp.tgl_bayar,
                rp.jml_transfer,
                mitra.ktp,
                mitra.nama,
                konfir.no_invoice,
                konfir.nominal_pajak,
                mitra.nama,
                konfir.periode,
                konfir.kode_unit,
                rp.id,
                rp_potongan.keterangan,
                konfir.ket_piutang,
                konfir.ket_potongan
            order by
                rp.tgl_bayar asc
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
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
            $data = $this->getDataPenjualanLb( $params );
            
            $filename = "PENJUALAN_LB_";
            $filename = $filename.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']).'.xls';

            $arr_header = array('DO', 'Unit', 'Unit - Nama Plasma - Periode', 'Periode', 'No. Invoice', 'Tgl Invoice', 'ID Pelanggan', 'Kode Pelanggan (NIK)', 'Nama Pelanggan', 'No. Nota Timbang', 'Kode Barang', 'Nama Barang', 'Kuantitas Barang (Kg)', 'Jumlah Ekor', 'Harga Satuan Barang', 'Total Invoice', 'Keterangan di Bagian kolom Deskripsi');
            $arr_column = null;
            if ( !empty($data) ) {
                $idx = 0;
                foreach ($data as $key => $value) {
                    $arr_column[ $idx ] = array(
                        'DO' => array('value' => strtoupper($value['perusahaan_alias']), 'data_type' => 'string'),
                        'Unit' => array('value' => strtoupper($value['unit']), 'data_type' => 'string'),
                        'Unit - Nama Plasma - Periode' => array('value' => strtoupper($value['unit'].'-'.$value['nama_plasma'].'-'.$value['kandang_plasma']), 'data_type' => 'string'),
                        'Periode' => array('value' => $value['periode_chickin'], 'data_type' => 'integer'),
                        'No. Invoice' => array('value' => !empty($value['no_nota']) ? $value['no_nota'] : '-', 'data_type' => 'string'),
                        'Tgl Invoice' => array('value' => $value['tanggal_panen'], 'data_type' => 'date'),
                        'ID Pelanggan' => array('value' => $value['kode_bakul'], 'data_type' => 'string'),
                        'Kode Pelanggan (NIK)' => array('value' => $value['nik_bakul'], 'data_type' => 'nik'),
                        'Nama Pelanggan' => array('value' => $value['nama_bakul'], 'data_type' => 'string'),
                        'No. Nota Timbang' => array('value' => $value['no_nota_timbang'], 'data_type' => 'string'),
                        'Kode Barang' => array('value' => $value['kode_barang'], 'data_type' => 'string'),
                        'Nama Barang' => array('value' => $value['deskripsi_barang'], 'data_type' => 'string'),
                        'Kuantitas Barang (Kg)' => array('value' => $value['kuantitas'], 'data_type' => 'decimal2'),
                        'Jumlah Ekor' => array('value' => $value['jumlah_ekor'], 'data_type' => 'integer'),
                        'Harga Satuan Barang' => array('value' => $value['harga_per_satuan_kuantitas'], 'data_type' => 'decimal2'),
                        'Total Invoice' => array('value' => ($value['kuantitas'] * $value['harga_per_satuan_kuantitas']), 'data_type' => 'decimal2'),
                        'Keterangan di Bagian kolom Deskripsi' => array('value' => strtoupper($value['unit'].'-'.$value['nama_plasma'].'-'.$value['kandang_plasma'].' Periode '.$value['periode_chickin']), 'data_type' => 'string'),
                    );

                    $idx++;
                }
            }

            $this->exportExcelUsingSpreadSheet( $filename, $arr_header, $arr_column );
        } else if ( $params['jenis'] == 2 ) {
            $data = $this->getDataPembelianDoc( $params );
            $filename = "PEMBELIAN_DOC_";
            $filename = $filename.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']).'.xls';

            $arr_header = array('DO', 'Unit', 'No. Invoice Supplier', 'No. Invoice', 'Tgl Invoice', 'Tgl Kedatangan', 'Kode Supplier', 'Nama Supplier', 'Kode Barang', 'Nama Barang', 'Kuantitas Barang', 'Satuan Barang', 'Jumlah Box', 'Harga Satuan Barang', 'Total Invoice', 'Unit - Nama Plasma - Kandang', 'Periode Plasma', 'Keterangan di Bagian kolom Deskripsi');
            $arr_column = null;
            if ( !empty($data) ) {
                $idx = 0;
                foreach ($data as $key => $value) {
                    $arr_column[ $idx ] = array(
                        'DO' => array('value' => strtoupper($value['perusahaan_alias']), 'data_type' => 'string'),
                        'Unit' => array('value' => strtoupper($value['nama_unit']), 'data_type' => 'string'),
                        'No. Invoice Supplier' => array('value' => strtoupper($value['no_sj']), 'data_type' => 'string'),
                        'No. Invoice' => array('value' => strtoupper($value['no_form']), 'data_type' => 'string'),
                        'Tgl Invoice' => array('value' => substr($value['tanggal'], 0, 10), 'data_type' => 'date'),
                        'Tgl Kedatangan' => array('value' => substr($value['periode'], 0, 10), 'data_type' => 'date'),
                        'Kode Supplier' => array('value' => strtoupper($value['kode_supplier']), 'data_type' => 'string'),
                        'Nama Supplier' => array('value' => strtoupper($value['nama_supplier']), 'data_type' => 'string'),
                        'Kode Barang' => array('value' => strtoupper($value['kode_barang']), 'data_type' => 'string'),
                        'Nama Barang' => array('value' => strtoupper($value['nama_barang']), 'data_type' => 'string'),
                        'Kuantitas Barang' => array('value' => $value['kuantitas'], 'data_type' => 'integer'),
                        'Satuan Barang' => array('value' => strtoupper($value['satuan_kuantitas']), 'data_type' => 'string'),
                        'Jumlah Box' => array('value' => $value['jumlah_box'], 'data_type' => 'integer'),
                        'Harga Satuan Barang' => array('value' => $value['harga_per_satuan_kuantitas'], 'data_type' => 'decimal2'),
                        'Total Invoice' => array('value' => ($value['kuantitas'] * $value['harga_per_satuan_kuantitas']), 'data_type' => 'decimal2'),
                        'Unit - Nama Plasma - Kandang' => array('value' => strtoupper($value['unit'].'-'.$value['nama_plasma'].'-'.$value['kandang_plasma']), 'data_type' => 'string'),
                        'Periode Plasma' => array('value' => $value['periode_chickin'], 'data_type' => 'integer'),
                        'Keterangan di Bagian kolom Deskripsi' => array('value' => strtoupper($value['unit'].'-'.$value['nama_plasma'].'-'.$value['kandang_plasma'].' Periode '.$value['periode_chickin']), 'data_type' => 'string'),
                    );

                    $idx++;
                }
            }

            $this->exportExcelUsingSpreadSheet( $filename, $arr_header, $arr_column );
        } else if ( $params['jenis'] == 3 ) {
            $data = $this->getDataPembelianVoadip( $params );
            $filename = "PEMBELIAN_VOADIP_";
            $filename = $filename.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']).'.xls';

            $arr_header = array('DO', 'Unit', 'No. Invoice Supplier', 'No. Invoice', 'Tgl Invoice', 'Tgl Kedatangan', 'Kode Supplier', 'Nama Supplier', 'Kode Barang', 'Nama Barang', 'Kuantitas Barang', 'Satuan Barang', 'Jumlah Box', 'Harga Satuan Barang', 'Total Invoice', 'Unit - Nama Plasma - Kandang', 'Periode Plasma', 'Keterangan di Bagian kolom Deskripsi');
            $arr_column = null;
            if ( !empty($data) ) {
                $idx = 0;
                foreach ($data as $key => $value) {
                    $arr_column[ $idx ] = array(
                        'DO' => array('value' => strtoupper($value['perusahaan_alias']), 'data_type' => 'string'),
                        'Unit' => array('value' => strtoupper($value['unit']), 'data_type' => 'string'),
                        'No. Invoice Supplier' => array('value' => strtoupper($value['no_sj']), 'data_type' => 'string'),
                        'No. Invoice' => array('value' => strtoupper($value['no_form']), 'data_type' => 'string'),
                        'Tgl Invoice' => array('value' => substr($value['tanggal'], 0, 10), 'data_type' => 'date'),
                        'Tgl Kedatangan' => array('value' => substr($value['tanggal'], 0, 10), 'data_type' => 'date'),
                        'Kode Supplier' => array('value' => strtoupper($value['kode_supplier']), 'data_type' => 'string'),
                        'Nama Supplier' => array('value' => strtoupper($value['nama_supplier']), 'data_type' => 'string'),
                        'Kode Barang' => array('value' => strtoupper($value['kode_barang']), 'data_type' => 'string'),
                        'Nama Barang' => array('value' => strtoupper($value['nama_barang']), 'data_type' => 'string'),
                        'Kuantitas Barang' => array('value' => $value['kuantitas'], 'data_type' => 'decimal2'),
                        'Satuan Barang' => array('value' => strtoupper($value['satuan_kuantitas']), 'data_type' => 'string'),
                        'Jumlah Box' => array('value' => $value['kuantitas'], 'data_type' => 'decimal2'),
                        'Harga Satuan Barang' => array('value' => $value['harga_per_satuan_kuantitas'], 'data_type' => 'decimal2'),
                        'Total Invoice' => array('value' => ($value['kuantitas'] * $value['harga_per_satuan_kuantitas']), 'data_type' => 'decimal2'),
                        'Unit - Nama Plasma - Kandang' => array('value' => '', 'data_type' => 'string'),
                        'Periode Plasma' => array('value' => '', 'data_type' => 'string'),
                        'Keterangan di Bagian kolom Deskripsi' => array('value' => '', 'data_type' => 'string'),
                    );

                    $idx++;
                }
            }

            $this->exportExcelUsingSpreadSheet( $filename, $arr_header, $arr_column );
        } else if ( $params['jenis'] == 4 ) {
            $data = $this->getDataPembelianPakan( $params );
            $filename = "PEMBELIAN_PAKAN_";
            $filename = $filename.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']).'.xls';

            $arr_header = array('DO', 'Unit', 'No. Invoice Supplier', 'No. Invoice', 'Tgl Invoice', 'Tgl Kedatangan', 'Kode Supplier', 'Nama Supplier', 'Kode Barang', 'Nama Barang', 'Kuantitas Barang', 'Satuan Barang', 'Jumlah Box', 'Harga Satuan Barang', 'Total Invoice', 'Unit - Nama Plasma - Kandang', 'Periode Plasma', 'Keterangan di Bagian kolom Deskripsi');
            $arr_column = null;
            if ( !empty($data) ) {
                $idx = 0;
                foreach ($data as $key => $value) {
                    $arr_column[ $idx ] = array(
                        'DO' => array('value' => strtoupper($value['perusahaan_alias']), 'data_type' => 'string'),
                        'Unit' => array('value' => strtoupper($value['unit']), 'data_type' => 'string'),
                        'No. Invoice Supplier' => array('value' => strtoupper($value['no_sj']), 'data_type' => 'string'),
                        'No. Invoice' => array('value' => strtoupper($value['no_form']), 'data_type' => 'string'),
                        'Tgl Invoice' => array('value' => substr($value['tanggal'], 0, 10), 'data_type' => 'date'),
                        'Tgl Kedatangan' => array('value' => substr($value['tanggal'], 0, 10), 'data_type' => 'date'),
                        'Kode Supplier' => array('value' => strtoupper($value['kode_supplier']), 'data_type' => 'string'),
                        'Nama Supplier' => array('value' => strtoupper($value['nama_supplier']), 'data_type' => 'string'),
                        'Kode Barang' => array('value' => strtoupper($value['kode_barang']), 'data_type' => 'string'),
                        'Nama Barang' => array('value' => strtoupper($value['nama_barang']), 'data_type' => 'string'),
                        'Kuantitas Barang' => array('value' => $value['kuantitas'], 'data_type' => 'integer'),
                        'Satuan Barang' => array('value' => strtoupper($value['satuan_kuantitas']), 'data_type' => 'string'),
                        'Jumlah Box' => array('value' => ($value['kuantitas'] / 50), 'data_type' => 'integer'),
                        'Harga Satuan Barang' => array('value' => $value['harga_per_satuan_kuantitas'], 'data_type' => 'decimal2'),
                        'Total Invoice' => array('value' => ($value['kuantitas'] * $value['harga_per_satuan_kuantitas']), 'data_type' => 'decimal2'),
                        'Unit - Nama Plasma - Kandang' => array('value' => '', 'data_type' => 'string'),
                        'Periode Plasma' => array('value' => '', 'data_type' => 'string'),
                        'Keterangan di Bagian kolom Deskripsi' => array('value' => '', 'data_type' => 'string'),
                    );

                    $idx++;
                }
            }

            $this->exportExcelUsingSpreadSheet( $filename, $arr_header, $arr_column );

            // $content['data'] = $data;
            // $res_view_html = $this->load->view($this->pathView.'exportExcelPembelianPakan_20260627', $content, true);
        } else if ( $params['jenis'] == 5 ) {
            $data = $this->getDataReturPembelian( $params );
            $content['data'] = $data;
            $res_view_html = $this->load->view($this->pathView.'exportExcelReturPembelian', $content, true);
            $filename = "RETUR_PEMBELIAN_";

            header("Content-type:   application/ms-excel; charset=utf-8");
            $filename = $filename.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']).'.xls';
            header("Content-Disposition: attachment; filename=".$filename."");
            echo $res_view_html;
        } else if ( $params['jenis'] == 6 ) {
            $data = $this->getDataDistribusiVoadip( $params );
            $content['data'] = $data;
            $res_view_html = $this->load->view($this->pathView.'exportExcelDistribusiVoadip', $content, true);
            $filename = "DISTRIBUSI_VOADIP_";

            header("Content-type:   application/ms-excel; charset=utf-8");
            $filename = $filename.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']).'.xls';
            header("Content-Disposition: attachment; filename=".$filename."");
            echo $res_view_html;
        } else if ( $params['jenis'] == 7 ) {
            $data = $this->getDataDistribusiPakan( $params );
            $content['data'] = $data;
            $res_view_html = $this->load->view($this->pathView.'exportExcelDistribusiPakan', $content, true);
            $filename = "DISTRIBUSI_PAKAN_";

            header("Content-type:   application/ms-excel; charset=utf-8");
            $filename = $filename.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']).'.xls';
            header("Content-Disposition: attachment; filename=".$filename."");
            echo $res_view_html;
        } else if ( $params['jenis'] == 8 ) {
            $data = $this->getDataBayarPembelian( $params );
            $filename = "BAYAR_PEMBELIAN_";
            $filename = $filename.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']).'.xls';

            $arr_header = array('DO', 'Unit', 'Sumber Kas/Bank (Nama Kas/Bank)', 'No. Bukti Bank', 'Tgl Transfer', 'Nominal Transfer', 'Kode Supplier', 'Nama Supplier', 'Jenis', 'No Invoice Yang Di Lunasi', 'Tagihan', 'Nominal Bayar', 'Nominal Pemotongan', 'Nominal UM Yang Digunakan', 'No. Bukti UM', 'Deskripsi/Catatan/Keterangan Bank');
            $arr_column = null;
            if ( !empty($data) ) {
                $idx = 0;
                $rowspan = 1; 
                $id_old = null;
                foreach ($data as $key => $value) {
                    if ( $value['id'] != $id_old ) {
                        $rowspan = array_count_values(array_column($data, 'id'))[$value['id']];
                    }

                    $arr_column[ $idx ] = array(
                        'DO' => array('value' => strtoupper($value['do']), 'data_type' => 'string'),
                        'Unit' => array('value' => strtoupper($value['kode_unit']), 'data_type' => 'string'),
                        'Sumber Kas/Bank (Nama Kas/Bank)' => ( $value['id'] == $id_old ) ? array('value' => null) : array('value' => strtoupper($value['bank'].' - '.$value['rekening']), 'data_type' => 'string', 'rowspan' => $rowspan),
                        'No. Bukti Bank' => ( $value['id'] == $id_old ) ? array('value' => null) : array('value' => strtoupper($value['no_bukti']), 'data_type' => 'string', 'rowspan' => $rowspan),
                        'Tgl Transfer' => ( $value['id'] == $id_old ) ? array('value' => null) : array('value' => $value['tgl_bayar'], 'data_type' => 'date', 'rowspan' => $rowspan),
                        'Nominal Transfer' => ( $value['id'] == $id_old ) ? array('value' => null) : array('value' => $value['transfer'], 'data_type' => 'decimal2', 'rowspan' => $rowspan),
                        'Kode Supplier' => ( $value['id'] == $id_old ) ? array('value' => null) : array('value' => strtoupper($value['kode_supl']), 'data_type' => 'string', 'rowspan' => $rowspan),
                        'Nama Supplier' => ( $value['id'] == $id_old ) ? array('value' => null) : array('value' => strtoupper($value['nama_supl']), 'data_type' => 'string', 'rowspan' => $rowspan),
                        'Jenis' => ( $value['id'] == $id_old ) ? array('value' => null) : array('value' => strtoupper($value['jenis']), 'data_type' => 'string', 'rowspan' => $rowspan),
                        'No Invoice Yang Di Lunasi' => array('value' => strtoupper($value['no_invoice']), 'data_type' => 'string'),
                        'Tagihan' => array('value' => $value['sisa_tagihan'], 'data_type' => 'decimal2'),
                        'Nominal Bayar' => array('value' => $value['bayar'], 'data_type' => 'decimal2'),
                        'Nominal Pemotongan' => ( $value['id'] == $id_old ) ? array('value' => null) : array('value' => $value['potongan'], 'data_type' => 'decimal2', 'rowspan' => $rowspan),
                        'Nominal UM Yang Digunakan' => ( $value['id'] == $id_old ) ? array('value' => null) : array('value' => 0, 'data_type' => 'decimal2', 'rowspan' => $rowspan),
                        'No. Bukti UM' => ( $value['id'] == $id_old ) ? array('value' => null) : array('value' => '', 'data_type' => 'string', 'rowspan' => $rowspan),
                        'Deskripsi/Catatan/Keterangan Bank' => ( $value['id'] == $id_old ) ? array('value' => null) : array('value' => '', 'data_type' => 'string', 'rowspan' => $rowspan)
                    );

                    $id_old = $value['id'];
                    $idx++;
                }
            }

            $this->exportExcelUsingSpreadSheet( $filename, $arr_header, $arr_column );
        } else if ( $params['jenis'] == 9 ) {
            $data = $this->getDataBayarOa( $params );
            $filename = "BAYAR_OA_";
            $filename = $filename.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']).'.xls';

            $arr_header = array('DO', 'Unit', 'Sumber Kas/Bank (Nama Kas/Bank)', 'No. Bukti Bank', 'Tgl Transfer', 'Nominal Transfer', 'Kode Ekspedisi', 'Nama Ekspedisi', 'No Invoice Yang Di Lunasi', 'Nominal Pemotongan Pajak', 'Keterangan Pemotongan Pajak', 'Nominal Pemotongan Lainnya', 'Keterangan Pemotongan Lainnya', 'Deskripsi/Catatan/Keterangan Bank');
            $arr_column = null;
            if ( !empty($data) ) {
                $idx = 0;
                foreach ($data as $key => $value) {
                    $ket_potongan = '';
                    if ( !empty($value['ket_cn']) ) {
                        $ket_potongan .= !empty($ket_potongan) ? ', '.$value['ket_cn'] : $value['ket_cn'];
                    }

                    if ( !empty($value['ket_potongan']) ) {
                        $ket_potongan .= !empty($ket_potongan) ? ', '.$value['ket_potongan'] : $value['ket_potongan'];
                    }

                    if ( !empty($value['ket_materai']) ) {
                        $ket_potongan .= !empty($ket_potongan) ? ', '.$value['ket_materai'] : $value['ket_materai'];
                    }

                    $arr_column[ $idx ] = array(
                        'DO' => array('value' => strtoupper($value['do']), 'data_type' => 'string'),
                        'Unit' => array('value' => strtoupper($value['kode_unit']), 'data_type' => 'string'),
                        'Sumber Kas/Bank (Nama Kas/Bank)' => array('value' => strtoupper($value['bank'].' - '.$value['rekening']), 'data_type' => 'string'),
                        'No. Bukti Bank' => array('value' => strtoupper($value['no_bukti']), 'data_type' => 'string'),
                        'Tgl Transfer' => array('value' => $value['tgl_bayar'], 'data_type' => 'date'),
                        'Nominal Transfer' => array('value' => $value['transfer'], 'data_type' => 'decimal2'),
                        'Kode Ekspedisi' => array('value' => strtoupper($value['kode_eks']), 'data_type' => 'nik'),
                        'Nama Ekspedisi' => array('value' => strtoupper($value['nama_eks']), 'data_type' => 'string'),
                        'No Invoice Yang Di Lunasi' => array('value' => strtoupper($value['no_invoice']), 'data_type' => 'string'),
                        'Nominal Pemotongan Pajak' => array('value' => $value['nominal_pajak'], 'data_type' => 'decimal2'),
                        'Keterangan Pemotongan Pajak' => array('value' => '', 'data_type' => 'string'),
                        'Nominal Pemotongan Lainnya' => array('value' => $value['potongan'], 'data_type' => 'decimal2'),
                        'Keterangan Pemotongan Lainnya' => array('value' => $ket_potongan, 'data_type' => 'string'),
                        'Deskripsi/Catatan/Keterangan Bank' => array('value' => strtoupper($value['keterangan']), 'data_type' => 'string')
                    );

                    $idx++;
                }
            }

            $this->exportExcelUsingSpreadSheet( $filename, $arr_header, $arr_column );
        } else if ( $params['jenis'] == 10 ) {
            $data = $this->getDataBayarMaklon( $params );
            $filename = "BAYAR_MAKLON_";
            $filename = $filename.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']).'.xls';

            $arr_header = array('DO', 'Unit', 'Sumber Kas/Bank (Nama Kas/Bank)', 'No. Bukti Bank', 'Tgl Transfer', 'Nominal Transfer', 'Kode Plasma', 'Nama Plasma', 'No Invoice Yang Di Lunasi', 'Nominal Pemotongan Pajak', 'Keterangan Pemotongan Pajak', 'Nominal Pemotongan Lainnya', 'Keterangan Pemotongan Lainnya', 'Deskripsi/Catatan/Keterangan Bank');
            $arr_column = null;
            if ( !empty($data) ) {
                $idx = 0;
                foreach ($data as $key => $value) {
                    $ket_potongan = '';
                    if ( !empty($value['ket_cn']) ) {
                        $ket_potongan .= !empty($ket_potongan) ? ', '.$value['ket_cn'] : $value['ket_cn'];
                    }

                    if ( !empty($value['ket_potongan']) ) {
                        $ket_potongan .= !empty($ket_potongan) ? ', '.$value['ket_potongan'] : $value['ket_potongan'];
                    }

                    if ( !empty($value['ket_potongan2']) ) {
                        $ket_potongan .= !empty($ket_potongan) ? ', '.$value['ket_potongan2'] : $value['ket_potongan2'];
                    }

                    $arr_column[ $idx ] = array(
                        'DO' => array('value' => strtoupper($value['do']), 'data_type' => 'string'),
                        'Unit' => array('value' => strtoupper($value['kode_unit']), 'data_type' => 'string'),
                        'Sumber Kas/Bank (Nama Kas/Bank)' => array('value' => strtoupper($value['bank'].' - '.$value['rekening']), 'data_type' => 'string'),
                        'No. Bukti Bank' => array('value' => strtoupper($value['no_bukti']), 'data_type' => 'string'),
                        'Tgl Transfer' => array('value' => $value['tgl_bayar'], 'data_type' => 'date'),
                        'Nominal Transfer' => array('value' => $value['transfer'], 'data_type' => 'decimal2'),
                        'Kode Plasma' => array('value' => strtoupper($value['kode_peternak']), 'data_type' => 'nik'),
                        'Nama Plasma' => array('value' => strtoupper($value['nama_peternak']), 'data_type' => 'string'),
                        'No Invoice Yang Di Lunasi' => array('value' => strtoupper($value['no_invoice']), 'data_type' => 'string'),
                        'Nominal Pemotongan Pajak' => array('value' => $value['nominal_pajak'], 'data_type' => 'decimal2'),
                        'Keterangan Pemotongan Pajak' => array('value' => '', 'data_type' => 'string'),
                        'Nominal Pemotongan Lainnya' => array('value' => $value['potongan'], 'data_type' => 'decimal2'),
                        'Keterangan Pemotongan Lainnya' => array('value' => $ket_potongan, 'data_type' => 'string'),
                        'Deskripsi/Catatan/Keterangan Bank' => array('value' => strtoupper($value['keterangan']), 'data_type' => 'string')
                    );

                    $idx++;
                }
            }

            $this->exportExcelUsingSpreadSheet( $filename, $arr_header, $arr_column );
        } else if ( $params['jenis'] == 11 ) {
            $data = $this->getDataBayarPenjualan( $params );
            // $data = $this->data_bayar_jual;
            $filename = "BAYAR_PENJUALAN_";
            $filename = $filename.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']).'.xls';

            $arr_header = array('DO', 'Unit', 'Sumber Kas/Bank (Nama Kas/Bank)', 'No. Bukti Bank', 'Tgl Transfer', 'Nominal Transfer', 'Kode Bakul', 'Nama Bakul', 'NIK', 'NPWP', 'No Invoice Yang Di Lunasi', 'Nominal Pemotongan', 'Nominal UM Yang Digunakan', 'No. Bukti UM', 'Deskripsi/Catatan/Keterangan Bank');
            $arr_column = null;
            if ( !empty($data) ) {
                $idx = 0;
                foreach ($data as $key => $value) {
                    $arr_column[ $idx ] = array(
                        'DO' => array('value' => strtoupper($value['do']), 'data_type' => 'string'),
                        'Unit' => array('value' => strtoupper($value['kode_unit']), 'data_type' => 'string'),
                        'Sumber Kas/Bank (Nama Kas/Bank)' => array('value' => strtoupper($value['bank'].' - '.$value['rekening']), 'data_type' => 'string'),
                        'No. Bukti Bank' => array('value' => strtoupper($value['no_bukti']), 'data_type' => 'string'),
                        'Tgl Transfer' => array('value' => $value['tgl_bayar'], 'data_type' => 'date'),
                        'Nominal Transfer' => array('value' => $value['transfer'], 'data_type' => 'decimal2'),
                        'Kode Bakul' => array('value' => strtoupper($value['kode_plg']), 'data_type' => 'string'),
                        'Nama Bakul' => array('value' => strtoupper($value['nama_plg']), 'data_type' => 'string'),
                        'NIK' => array('value' => strtoupper($value['nik']), 'data_type' => 'nik'),
                        'NPWP' => array('value' => strtoupper($value['npwp']), 'data_type' => 'nik'),
                        'No Invoice Yang Di Lunasi' => array('value' => strtoupper($value['no_invoice']), 'data_type' => 'string'),
                        'Nominal Pemotongan' => array('value' => $value['potongan'], 'data_type' => 'decimal2'),
                        'Nominal UM Yang Digunakan' => array('value' => 0, 'data_type' => 'decimal2'),
                        'No. Bukti UM' => array('value' => '', 'data_type' => 'string'),
                        'Deskripsi/Catatan/Keterangan Bank' => array('value' => $value['keterangan'], 'data_type' => 'string')
                    );

                    $idx++;
                }
            }

            $this->exportExcelUsingSpreadSheet( $filename, $arr_header, $arr_column );
        }
    }

    public function exportExcelUsingSpreadSheet( $file_name, $arr_header, $arr_column ) {
        /* Spreadsheet Init */
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        /* Excel Header */
        for ($i=0; $i < count($arr_header); $i++) { 
            $huruf = toAlpha($i+1);

            $posisi = $huruf.'1';
            $sheet->setCellValue($posisi, $arr_header[$i]);

            $styleBold = [
                'font' => [
                    'bold' => true,
                ]
            ];
            $spreadsheet->getActiveSheet()->getStyle($posisi)->applyFromArray($styleBold);
        }

        $baris = 2;
        if ( !empty($arr_column) && count($arr_column) ) {
            for ($i=0; $i < count($arr_column); $i++) {
                for ($j=0; $j < count($arr_header); $j++) {
                    $huruf = toAlpha($j+1);

                    $data = $arr_column[ $i ][ $arr_header[ $j ] ];

                    if ( !empty($data['value']) ) {
                        if ( isset($data['rowspan']) && $data['rowspan'] > 1 ) {
                            $spreadsheet->getActiveSheet()->mergeCells($huruf.$baris.':'.$huruf.(($baris+$data['rowspan'])-1));
                        }

                        if ( $data['data_type'] == 'string' ) {
                            $sheet->setCellValue($huruf.$baris, strtoupper($data['value']));
                        }

                        if ( $data['data_type'] == 'nik' ) {
                            $sheet->getCell($huruf.$baris)->setValueExplicit($data['value'], DataType::TYPE_STRING);
                            // $sheet->setCellValue($huruf.$baris, strtoupper($data['value']));
                            // $spreadsheet->getActiveSheet()->getStyle('A9')
                            //             ->getNumberFormat()
                            //             ->setFormatCode(
                            //                 '00000000000'
                            //             );
                        }

                        if ( $data['data_type'] == 'text' ) {
                            $sheet->setCellValue($huruf.$baris, strtoupper($data['value']));
                            $spreadsheet->getActiveSheet()->getStyle($huruf.$baris)
                                        ->getNumberFormat()
                                        ->setFormatCode(NumberFormat::FORMAT_GENERAL);
                        }

                        if ( $data['data_type'] == 'date' ) {
                            $dt = Date::PHPToExcel(DateTime::createFromFormat('!Y-m-d', substr($data['value'], 0, 10)));
                            $sheet->setCellValue($huruf.$baris, $dt);
                            $spreadsheet->getActiveSheet()->getStyle($huruf.$baris)
                                        ->getNumberFormat()
                                        ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
                        }

                        if ( $data['data_type'] == 'integer' ) {
                            $sheet->setCellValue($huruf.$baris, $data['value']);
                            $spreadsheet->getActiveSheet()->getStyle($huruf.$baris)
                                        ->getNumberFormat()
                                        ->setFormatCode(NumberFormat::FORMAT_NUMBER);
                        }

                        if ( $data['data_type'] == 'decimal2' ) {
                            $sheet->setCellValue($huruf.$baris, $data['value']);
                            $spreadsheet->getActiveSheet()->getStyle($huruf.$baris)
                                        ->getNumberFormat()
                                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
                        }
                    }
                }

                $baris++;
            }
        } else {
            $range1 = 'A'.$baris;
            $range2 = toAlpha(count($arr_header)).$baris;

            $spreadsheet->getActiveSheet()->mergeCells("$range1:$range2");
            $sheet->setCellValue($range1, 'Data tidak ditemukan.');
        }

        $styleArray = [
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
                'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
                'left' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
            ],
        ];
        
        $spreadsheet->getActiveSheet()->getStyle('A1:'.toAlpha(count($arr_header)).$baris)->applyFromArray($styleArray, false);

        /* Excel File Format */
        $writer = new Xlsx($spreadsheet);
        $filename = $file_name;
        $writer->save('export_excel/'.$filename);

        $this->load->helper('download');
        force_download('export_excel/'.$filename, NULL);
    }

    public function exportXml($params_encrypt)
    {
        $params = json_decode( exDecrypt($params_encrypt), true );

        $res_view_html = "TIDAK ADA DATA";
        $filename = "";
        if ( $params['jenis'] == 1 ) {
            $filename = "PENJUALAN_LB_";
            $filename = $filename.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']);

            $data = $this->getDataPenjualanLb( $params );
            $this->xmlDataPenjualanLb( $data, $filename );
        } else if ( $params['jenis'] == 2 ) {
            $filename = "PEMBELIAN_DOC_";
            $filename = $filename.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']);

            $data = $this->getDataPembelianDoc( $params );
            $this->xmlDataPembelianDoc( $data, $filename );
        } else if ( $params['jenis'] == 3 ) {
            $filename = "PEMBELIAN_VOADIP_";
            $filename = $filename.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']);

            $data = $this->getDataPembelianVoadip( $params );
            $this->xmlDataPembelianVoadip( $data, $filename );
        } else if ( $params['jenis'] == 4 ) {
            $filename = "PEMBELIAN_PAKAN_";
            $filename = $filename.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']);

            $data = $this->getDataPembelianPakan( $params );
            $this->xmlDataPembelianPakan( $data, $filename );
        } else if ( $params['jenis'] == 5 ) {
            $data = $this->getDataReturPembelian( $params );
            $content['data'] = $data;
            $res_view_html = $this->load->view($this->pathView.'exportExcelReturPembelian', $content, true);
            $filename = "RETUR_PEMBELIAN_";
        } else if ( $params['jenis'] == 6 ) {
            $data = $this->getDataDistribusiVoadip( $params );
            $content['data'] = $data;
            $res_view_html = $this->load->view($this->pathView.'exportExcelDistribusiVoadip', $content, true);
            $filename = "DISTRIBUSI_VOADIP_";
        } else if ( $params['jenis'] == 7 ) {
            $data = $this->getDataDistribusiPakan( $params );
            $content['data'] = $data;
            $res_view_html = $this->load->view($this->pathView.'exportExcelDistribusiPakan', $content, true);
            $filename = "DISTRIBUSI_PAKAN_";
        } else if ( $params['jenis'] == 8 ) {
        } else if ( $params['jenis'] == 9 ) {
        } else if ( $params['jenis'] == 10 ) {
        }
    }

    public function xmlDataPenjualanLb($data, $filename) {
        $branchCode = ($data[0]['kode_accurate'] == 'K' || $data[0]['kode_accurate'] == 'G') ? 'MGB123' : 'MVN123';

        $xml = new SimpleXMLElement('<xml/>');
        $dom = new DOMDocument('1.0');
        $dom->formatOutput = true;

        $nmexml = $dom->createElement("NMEXML");
        $nmexml->setAttribute("EximID", 1);
        $nmexml->setAttribute("BranchCode", $branchCode);
        $nmexml->setAttribute("ACCOUNTANTCOPYID", "");
        $dom->appendChild($nmexml);

        $transactions = $dom->createElement("TRANSACTIONS");
        $transactions->setAttribute("OnError", "CONTINUE");
        $nmexml->appendChild($transactions);

        $no_sj_old = null;

        $salesinvoice = null;

        $_transactionid = 1;
        $_keyid = 1;
        foreach ($data as $key => $value) {
            if ( $no_sj_old <> $value['no_nota'] ) {   
                $salesinvoice = $dom->createElement("SALESINVOICE");
                $salesinvoice->setAttribute("operation", "Add");
                $salesinvoice->setAttribute("REQUESTID", 1);
                $transactions->appendChild($salesinvoice);

                $transactionid = $dom->createElement("TRANSACTIONID", $_transactionid);
                $salesinvoice->appendChild($transactionid);
                $invoiceno = $dom->createElement("INVOICENO", $value['no_nota']);
                $salesinvoice->appendChild($invoiceno);
                $invoicedate = $dom->createElement("INVOICEDATE", $value['tanggal_panen']);
                $salesinvoice->appendChild($invoicedate);
                $tax1rate = $dom->createElement("TAX1RATE", 0);
                $salesinvoice->appendChild($tax1rate);
                $tax2rate = $dom->createElement("TAX2RATE", 0);
                $salesinvoice->appendChild($tax2rate);
                $rate = $dom->createElement("RATE", 1);
                $salesinvoice->appendChild($rate);
                $inclusivetax = $dom->createElement("INCLUSIVETAX", 1);
                $salesinvoice->appendChild($inclusivetax);
                $customeristaxable = $dom->createElement("CUSTOMERISTAXABLE", 1);
                $salesinvoice->appendChild($customeristaxable);
                $cashdiscount = $dom->createElement("CASHDISCOUNT", 1);
                $salesinvoice->appendChild($cashdiscount);
                $invoiceamount = $dom->createElement("INVOICEAMOUNT", $value['total_tagihan']);
                $salesinvoice->appendChild($invoiceamount);
                $invoiceamount = $dom->createElement("FREIGHT", 0);
                $salesinvoice->appendChild($invoiceamount);
                $invoiceamount = $dom->createElement("FOB", "C.O.D");
                $salesinvoice->appendChild($invoiceamount);
                $invoiceamount = $dom->createElement("DESCRIPTION", 'NO. NOTA - '.$value['no_nota']);
                $salesinvoice->appendChild($invoiceamount);
                $invoiceamount = $dom->createElement("SHIPDATE", $value['tanggal_panen']);
                $salesinvoice->appendChild($invoiceamount);
                $invoiceamount = $dom->createElement("FISCALRATE", 1);
                $salesinvoice->appendChild($invoiceamount);
                $invoiceamount = $dom->createElement("CUSTOMERID", $value['kode_bakul']);
                $salesinvoice->appendChild($invoiceamount);
                $invoiceamount = $dom->createElement("ARACCOUNT", "");
                $salesinvoice->appendChild($invoiceamount);
                $invoiceamount = $dom->createElement("CURRENCYNAME", "IDR");
                $salesinvoice->appendChild($invoiceamount);

                $_transactionid++;
                $_keyid = 1;
                $no_sj_old = $value['no_nota'];
            }

            $itemline = $dom->createElement("ITEMLINE");
            $itemline->setAttribute("operation", "Add");
            $salesinvoice->appendChild($itemline);

            $keyid = $dom->createElement("KeyID", $_keyid);
            $itemline->appendChild($keyid);
            $itemno = $dom->createElement("ITEMNO", $value['kode_barang']);
            $itemline->appendChild($itemno);
            $quantity = $dom->createElement("QUANTITY", $value['kuantitas']);
            $itemline->appendChild($quantity);
            $unitratio = $dom->createElement("UNITRATIO", 1);
            $itemline->appendChild($unitratio);
            $itemreserved1 = $dom->createElement("ITEMRESERVED1", $value['kuantitas']);
            $itemline->appendChild($itemreserved1);
            $itemovdesc = $dom->createElement("ITEMOVDESC", "AYAM ".$value['deskripsi_barang']);
            $itemline->appendChild($itemovdesc);
            $unitprice = $dom->createElement("UNITPRICE", $value['harga_per_satuan_kuantitas']);
            $itemline->appendChild($unitprice);
            $projectid = $dom->createElement("PROJECTID", $value['kode_unit']." - ".$value['nama_plasma']." - ".$value['noreg']);
            $itemline->appendChild($projectid);
            $deptid = $dom->createElement("DEPTID", $value['kode_accurate']." - ".$value['kode_unit']);
            $itemline->appendChild($deptid);
            $brutounitprice = $dom->createElement("BRUTOUNITPRICE", $value['harga_per_satuan_kuantitas']);
            $itemline->appendChild($brutounitprice);
            $qycontrol = $dom->createElement("QTYCONTROL", 0);
            $itemline->appendChild($qycontrol);
            $deptid = $dom->createElement("DEPTID", $value['kode_accurate']." - ".$value['kode_unit']);
            $itemline->appendChild($deptid);
            $projectid = $dom->createElement("PROJECTID", $value['kode_unit']." - ".$value['nama_plasma']." - ".$value['noreg']);
            $itemline->appendChild($projectid);

            $_keyid++;
        }

        $dom->save('export_xml/'.$filename.'.xml');

        $this->load->helper('download');
        force_download('export_xml/'.$filename.'.xml', NULL);
    }

    public function xmlDataPembelianDoc($data, $filename) {
        // cetak_r( $data, 1 );

        $branchCode = ($data[0]['kode_accurate'] == 'K' || $data[0]['kode_accurate'] == 'G') ? 'MGB123' : 'MVN123';

        $xml = new SimpleXMLElement('<xml/>');
        $dom = new DOMDocument('1.0');
        $dom->formatOutput = true;

        $nmexml = $dom->createElement("NMEXML");
        $nmexml->setAttribute("EximID", 1);
        $nmexml->setAttribute("BranchCode", $branchCode);
        $nmexml->setAttribute("ACCOUNTANTCOPYID", "");
        $dom->appendChild($nmexml);

        $transactions = $dom->createElement("TRANSACTIONS");
        $transactions->setAttribute("OnError", "CONTINUE");
        $nmexml->appendChild($transactions);

        $no_form_old = null;

        $salesinvoice = null;

        $_transactionid = 1;
        $_keyid = 1;
        foreach ($data as $key => $value) {
            if ( $no_form_old <> $value['no_form'] ) {   
                $salesinvoice = $dom->createElement("PURCHASEINVOICE");
                $salesinvoice->setAttribute("operation", "Add");
                $salesinvoice->setAttribute("REQUESTID", 1);
                $transactions->appendChild($salesinvoice);

                $transactionid = $dom->createElement("TRANSACTIONID", $_transactionid);
                $salesinvoice->appendChild($transactionid);
                $invoiceno = $dom->createElement("INVOICENO", $value['no_form']);
                $salesinvoice->appendChild($invoiceno);
                $invoicedate = $dom->createElement("INVOICEDATE", $value['tanggal']);
                $salesinvoice->appendChild($invoicedate);
                $invoiceamount = $dom->createElement("INVOICEAMOUNT", ($value['harga_per_satuan_kuantitas'] * $value['kuantitas_order']));
                $salesinvoice->appendChild($invoiceamount);
                $fob = $dom->createElement("FOB", "C.O.D");
                $salesinvoice->appendChild($fob);
                $description = $dom->createElement("DESCRIPTION", 'NO. NOTA - '.$value['no_form']);
                $salesinvoice->appendChild($description);
                $shipdate = $dom->createElement("SHIPDATE", $value['tanggal']);
                $salesinvoice->appendChild($shipdate);
                $vendorid = $dom->createElement("VENDORID", $value['kode_supplier']);
                $salesinvoice->appendChild($vendorid);
                $sequenceno = $dom->createElement("SEQUENCENO", $value['no_form']);
                $salesinvoice->appendChild($sequenceno);
                $apaccount = $dom->createElement("APACCOUNT", "");
                $salesinvoice->appendChild($apaccount);

                $_transactionid++;
                $_keyid = 1;
                $no_form_old = $value['no_form'];
            }

            $itemline = $dom->createElement("ITEMLINE");
            $itemline->setAttribute("operation", "Add");
            $salesinvoice->appendChild($itemline);

            $keyid = $dom->createElement("KeyID", $_keyid);
            $itemline->appendChild($keyid);
            $itemno = $dom->createElement("ITEMNO", $value['kode_barang']);
            $itemline->appendChild($itemno);
            $quantity = $dom->createElement("QUANTITY", $value['kuantitas_order']);
            $itemline->appendChild($quantity);
            $itemovdesc = $dom->createElement("ITEMOVDESC", $value['nama_barang']);
            $itemline->appendChild($itemovdesc);
            $brutounitprice = $dom->createElement("BRUTOUNITPRICE", $value['harga_per_satuan_kuantitas']);
            $itemline->appendChild($brutounitprice);
            $qycontrol = $dom->createElement("QTYCONTROL", 0);
            $itemline->appendChild($qycontrol);
            $deptid = $dom->createElement("DEPTID", $value['kode_accurate']." - ".$value['kode_unit']);
            $itemline->appendChild($deptid);

            $_keyid++;
        }

        $dom->save('export_xml/'.$filename.'.xml');

        $this->load->helper('download');
        force_download('export_xml/'.$filename.'.xml', NULL);
    }

    public function xmlDataPembelianVoadip($data, $filename) {
        // cetak_r( $data, 1 );

        $branchCode = ($data[0]['kode_accurate'] == 'K' || $data[0]['kode_accurate'] == 'G') ? 'MGB123' : 'MVN123';

        $xml = new SimpleXMLElement('<xml/>');
        $dom = new DOMDocument('1.0');
        $dom->formatOutput = true;

        $nmexml = $dom->createElement("NMEXML");
        $nmexml->setAttribute("EximID", 1);
        $nmexml->setAttribute("BranchCode", $branchCode);
        $nmexml->setAttribute("ACCOUNTANTCOPYID", "");
        $dom->appendChild($nmexml);

        $transactions = $dom->createElement("TRANSACTIONS");
        $transactions->setAttribute("OnError", "CONTINUE");
        $nmexml->appendChild($transactions);

        $no_form_old = null;

        $salesinvoice = null;

        $_transactionid = 1;
        $_keyid = 1;
        foreach ($data as $key => $value) {
            if ( $no_form_old <> $value['no_form'] ) {   
                $salesinvoice = $dom->createElement("PURCHASEINVOICE");
                $salesinvoice->setAttribute("operation", "Add");
                $salesinvoice->setAttribute("REQUESTID", 1);
                $transactions->appendChild($salesinvoice);

                $transactionid = $dom->createElement("TRANSACTIONID", $_transactionid);
                $salesinvoice->appendChild($transactionid);
                $invoiceno = $dom->createElement("INVOICENO", $value['no_sj']);
                $salesinvoice->appendChild($invoiceno);
                $invoicedate = $dom->createElement("INVOICEDATE", $value['tanggal']);
                $salesinvoice->appendChild($invoicedate);
                $invoiceamount = $dom->createElement("INVOICEAMOUNT", $value['total_tagihan']);
                $salesinvoice->appendChild($invoiceamount);
                $fob = $dom->createElement("FOB", "C.O.D");
                $salesinvoice->appendChild($fob);
                $description = $dom->createElement("DESCRIPTION", 'NO. NOTA - '.$value['no_sj']);
                $salesinvoice->appendChild($description);
                $shipdate = $dom->createElement("SHIPDATE", $value['tanggal']);
                $salesinvoice->appendChild($shipdate);
                $vendorid = $dom->createElement("VENDORID", $value['kode_supplier']);
                $salesinvoice->appendChild($vendorid);
                $sequenceno = $dom->createElement("SEQUENCENO", $value['no_sj']);
                $salesinvoice->appendChild($sequenceno);
                $apaccount = $dom->createElement("APACCOUNT", "");
                $salesinvoice->appendChild($apaccount);

                $_transactionid++;
                $_keyid = 1;
                $no_form_old = $value['no_form'];
            }

            $itemline = $dom->createElement("ITEMLINE");
            $itemline->setAttribute("operation", "Add");
            $salesinvoice->appendChild($itemline);

            $keyid = $dom->createElement("KeyID", $_keyid);
            $itemline->appendChild($keyid);
            $itemno = $dom->createElement("ITEMNO", $value['kode_barang']);
            $itemline->appendChild($itemno);
            $quantity = $dom->createElement("QUANTITY", $value['kuantitas']);
            $itemline->appendChild($quantity);
            $itemovdesc = $dom->createElement("ITEMOVDESC", $value['nama_barang']);
            $itemline->appendChild($itemovdesc);
            $brutounitprice = $dom->createElement("BRUTOUNITPRICE", $value['harga_per_satuan_kuantitas']);
            $itemline->appendChild($brutounitprice);
            $qycontrol = $dom->createElement("QTYCONTROL", 0);
            $itemline->appendChild($qycontrol);
            $deptid = $dom->createElement("DEPTID", $value['kode_accurate']." - ".$value['kode_unit']);
            $itemline->appendChild($deptid);

            $_keyid++;
        }

        $dom->save('export_xml/'.$filename.'.xml');

        $this->load->helper('download');
        force_download('export_xml/'.$filename.'.xml', NULL);
    }

    public function xmlDataPembelianPakan($data, $filename) {
        // cetak_r( $data, 1 );

        $branchCode = ($data[0]['kode_accurate'] == 'K' || $data[0]['kode_accurate'] == 'G') ? 'MGB123' : 'MVN123';

        $xml = new SimpleXMLElement('<xml/>');
        $dom = new DOMDocument('1.0');
        $dom->formatOutput = true;

        $nmexml = $dom->createElement("NMEXML");
        $nmexml->setAttribute("EximID", 1);
        $nmexml->setAttribute("BranchCode", $branchCode);
        $nmexml->setAttribute("ACCOUNTANTCOPYID", "");
        $dom->appendChild($nmexml);

        $transactions = $dom->createElement("TRANSACTIONS");
        $transactions->setAttribute("OnError", "CONTINUE");
        $nmexml->appendChild($transactions);

        $no_form_old = null;

        $salesinvoice = null;

        $_transactionid = 1;
        $_keyid = 1;
        foreach ($data as $key => $value) {
            if ( $no_form_old <> $value['no_form'] ) {   
                $salesinvoice = $dom->createElement("PURCHASEINVOICE");
                $salesinvoice->setAttribute("operation", "Add");
                $salesinvoice->setAttribute("REQUESTID", 1);
                $transactions->appendChild($salesinvoice);

                $transactionid = $dom->createElement("TRANSACTIONID", $_transactionid);
                $salesinvoice->appendChild($transactionid);
                $invoiceno = $dom->createElement("INVOICENO", $value['no_sj']);
                $salesinvoice->appendChild($invoiceno);
                $invoicedate = $dom->createElement("INVOICEDATE", $value['tanggal']);
                $salesinvoice->appendChild($invoicedate);
                $invoiceamount = $dom->createElement("INVOICEAMOUNT", $value['total_tagihan']);
                $salesinvoice->appendChild($invoiceamount);
                $fob = $dom->createElement("FOB", "C.O.D");
                $salesinvoice->appendChild($fob);
                $description = $dom->createElement("DESCRIPTION", 'NO. NOTA - '.$value['no_sj']);
                $salesinvoice->appendChild($description);
                $shipdate = $dom->createElement("SHIPDATE", $value['tanggal']);
                $salesinvoice->appendChild($shipdate);
                $vendorid = $dom->createElement("VENDORID", $value['kode_supplier']);
                $salesinvoice->appendChild($vendorid);
                $sequenceno = $dom->createElement("SEQUENCENO", $value['no_sj']);
                $salesinvoice->appendChild($sequenceno);
                $apaccount = $dom->createElement("APACCOUNT", "");
                $salesinvoice->appendChild($apaccount);

                $_transactionid++;
                $_keyid = 1;
                $no_form_old = $value['no_form'];
            }

            $itemline = $dom->createElement("ITEMLINE");
            $itemline->setAttribute("operation", "Add");
            $salesinvoice->appendChild($itemline);

            $keyid = $dom->createElement("KeyID", $_keyid);
            $itemline->appendChild($keyid);
            $itemno = $dom->createElement("ITEMNO", $value['kode_barang']);
            $itemline->appendChild($itemno);
            $quantity = $dom->createElement("QUANTITY", $value['kuantitas']);
            $itemline->appendChild($quantity);
            $itemovdesc = $dom->createElement("ITEMOVDESC", $value['nama_barang']);
            $itemline->appendChild($itemovdesc);
            $brutounitprice = $dom->createElement("BRUTOUNITPRICE", $value['harga_per_satuan_kuantitas']);
            $itemline->appendChild($brutounitprice);
            $qycontrol = $dom->createElement("QTYCONTROL", 0);
            $itemline->appendChild($qycontrol);
            $deptid = $dom->createElement("DEPTID", $value['kode_accurate']." - ".$value['kode_unit']);
            $itemline->appendChild($deptid);

            $_keyid++;
        }

        $dom->save('export_xml/'.$filename.'.xml');

        $this->load->helper('download');
        force_download('export_xml/'.$filename.'.xml', NULL);
    }

    public function tes() {
        // $xml = "<NMEXML EximID='10' BranchCode='MVN123' ACCOUNTANTCOPYID=''>";
        // $xml .= "<TRANSACTIONS OnError='CONTINUE'>";
        // $xml .= "<SALESINVOICE operation='Add' REQUESTID='1'>";
        // $xml .= "<TRANSACTIONID>1</TRANSACTIONID>";
        // $xml .= "</SALESINVOICE>";
        // $xml .= "</TRANSACTIONS>";
        // $xml .= "</NMEXML>";

        // $sxe = new SimpleXMLElement($xml);
        // $dom = new DOMDocument('1,0');
        // $dom->preserveWhiteSpace = false;
        // $dom->formatOutput = true;

        // Header('Content-type: text/xml');
        // $dom->loadXML($sxe->asXML());

        // echo $dom->saveXML();

        // $dom->save('coba_xml.xml');

        // $xml = new SimpleXMLElement('<xml/>');

        // for ($i = 1; $i <= 8; ++$i) {
        //     $track = $xml->addChild('track');
        //     $track->addChild('path', "song$i.mp3");
        //     $track->addChild('title', "Track $i - Track Title");
        // }

        // Header('Content-type: text/xml');
        // print($xml->asXML());

        $xml = new SimpleXMLElement('<xml/>');
        $dom = new DOMDocument('1.0');
        $dom->formatOutput = true;

        $books = $dom->createElement("books");
        $dom->appendChild($books);

        $book = $dom->createElement("book");
        $book->setAttribute("id", 1);
        $books->appendChild($book);

        $name = $dom->createElement("name", "java");
        $book->appendChild($name);

        $price = $dom->createElement("price", "200");
        $book->appendChild($price);

        echo "<xmp>".$dom->saveXML()."</xmp>";

        $dom->save('coba.xml');

        // Header('Content-type: text/xml');
        // print($xml->asXML());
    }
}