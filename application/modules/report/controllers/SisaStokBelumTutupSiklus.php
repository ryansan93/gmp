<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class SisaStokBelumTutupSiklus extends Public_Controller {

    private $url;
    private $pathView = 'report/sisa_stok_belum_tutup_siklus/';

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
                'assets/report/sisa_stok_belum_tutup_siklus/js/sisa-stok-belum-tutup-siklus.js',
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/report/sisa_stok_belum_tutup_siklus/css/sisa-stok-belum-tutup-siklus.css',
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['jenis'] = array(
                'doc' => 'DOC', 
                'pakan' => 'PAKAN', 
                'voadip' => 'OVK'
            );
            $content['unit'] = $this->getUnit();
            $content['perusahaan'] = $this->getPerusahaan();
            $content['title_menu'] = 'Laporan Sisa Stok Belum Tutup Siklus';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getUnit()
    {
        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                w1.kode,
                REPLACE(REPLACE(w1.nama, 'Kota ', ''), 'Kab ', '') as nama
            from wilayah w1
            right join
                (select max(id) as id, kode from wilayah group by kode) w2
                on
                    w1.id = w2.id
            order by
                REPLACE(REPLACE(w1.nama, 'Kota ', ''), 'Kab ', '') asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getPerusahaan()
    {
        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                p1.kode,
                p1.perusahaan as nama
            from perusahaan p1
            right join
                (select max(id) as id, kode from perusahaan group by kode) p2
                on
                    p1.id = p2.id
            order by
                p1.perusahaan asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getLists() {
        $params = $this->input->get('params');

        $tanggal = $params['tanggal'];
        $jenis = $params['jenis'];
        $unit = $params['unit'];
        $perusahaan = $params['perusahaan'];

        $data_doc = null;
        $data_pakan = null;
        $data_voadip = null;

        if ( in_array('all', $jenis) || in_array('doc', $jenis) ) {
            $data_doc = $this->getDataDoc($tanggal, $unit, $perusahaan);
        }
        if ( in_array('all', $jenis) || in_array('pakan', $jenis) ) {
            $data_pakan = $this->getDataPakan($tanggal, $unit, $perusahaan);
        }
        if ( in_array('all', $jenis) || in_array('voadip', $jenis) ) {
            $data_voadip = $this->getDataVoadip($tanggal, $unit, $perusahaan);
        }

        // $content['data_doc'] = $data_doc;
        // $content['data_pakan'] = $data_pakan;
        // $content['data_voadip'] = $data_voadip;

        $data = null;

        if ( !empty($data_doc) ) {
            if ( !empty( $data ) ) {
                $data = array_merge( $data, $data_doc['data'] );
            } else {
                $data = $data_doc['data'];
            }
        }

        if ( !empty($data_pakan) ) {
            if ( !empty( $data ) ) {
                $data = array_merge( $data, $data_pakan['data'] );
            } else {
                $data = $data_pakan['data'];
            }
        }

        if ( !empty($data_voadip) ) {
            if ( !empty( $data ) ) {
                $data = array_merge( $data, $data_voadip['data'] );
            } else {
                $data = $data_voadip['data'];
            }
        }

        if ( !empty( $data ) ) {
            ksort( $data );
        }

        $content['data'] = $data;

        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }

    public function getDataDoc($tanggal, $unit, $perusahaan)
    {
        $return = null;

        try {
            $data = null;

            $sql_unit = null;
            if ( !in_array('all', $unit) ) {
                $sql_unit = "and w.kode in ('".implode("', '", $unit)."')";
            }

            $sql_perusahaan = null;
            if ( !in_array('all', $perusahaan) ) {
                $sql_perusahaan = "and prs.kode in ('".implode("', '", $perusahaan)."')";
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    'doc' as jenis,
                    rs.noreg,
                    od.tgl_submit as tgl_beli,
                    td.datang as tgl_distribusi,
                    od.item as kode_brg,
                    td.jml_box as zak,
                    td.jml_ekor as tonase,
                    0 as ongkos,
                    td.harga as hrg_beli,
                    td.total as tot_beli,
                    '' as jenis_kirim,
                    rs.nama_mitra+' '+cast(cast(SUBSTRING(rs.noreg, 10, 2) as int) as varchar(2)) as nama_mitra,
                    rs.nama_perusahaan,
                    rs.nama_unit,
                    rs.kode_unit,
                    brg.nama as nama_barang,
                    'distribusi' as jenis_transaksi,
                    REPLACE(CONVERT(varchar(10), td.datang, 23), '-', '')+'|'+rs.kode_unit+'|'+od.item+'|'+od.no_order+'|'+cast(td.harga as varchar(30))+'|tujuan|0' as _key
                from (
                        select 
                            rs.*, 
                            m.nama as nama_mitra,
                            prs.perusahaan as nama_perusahaan,
                            SUBSTRING(od.no_order, 5, 3) as kode_unit,
                            w.nama as nama_unit
                        from rdim_submit rs
                        left join
                            (
                                select od1.* from order_doc od1
                                right join
                                    (select max(id) as id, noreg from order_doc group by noreg) od2
                                    on
                                        od1.id = od2.id
                            ) od
                            on
                                rs.noreg = od.noreg
                        left join
                            (
                                select td1.* from terima_doc td1
                                right join
                                    (select max(id) as id, no_order from terima_doc group by no_order) td2
                                    on
                                        td1.id = td2.id
                            ) td
                            on
                                td.no_order = od.no_order
                        left join
                            (select * from tutup_siklus where tgl_tutup <= '".$tanggal."') ts
                            on
                                ts.noreg = rs.noreg
                        left join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                mm.nim = rs.nim
                        left join
                            mitra m
                            on
                                m.id = mm.mitra
                        left join
                            (
                                select prs1.* from perusahaan prs1
                                right join
                                    (select max(id) as id, kode from perusahaan group by kode) prs2
                                    on
                                        prs1.id = prs2.id
                            ) prs
                            on
                                prs.kode = od.perusahaan
                        left join
                            (
                                select 
                                    w1.kode, 
                                    REPLACE(REPLACE(w1.nama, 'Kab ', ''), 'Kota ', '') as nama
                                from wilayah w1
                                right join
                                    (select max(id) as id, kode from wilayah where kode is not null group by kode) w2
                                    on
                                        w1.id = w2.id
                            ) w
                            on
                                w.kode = SUBSTRING(od.no_order, 5, 3)
                        where
                            ts.id is null and
                            td.datang between '2024-01-01 00:00:00.001' and '".$tanggal." 23:59:59.999'
                            ".$sql_unit."
                            ".$sql_perusahaan."
                    ) rs
                left join
                    (
                        select od1.* from order_doc od1
                        right join
                            (select max(id) as id, noreg from order_doc group by noreg) od2
                            on
                                od1.id = od2.id
                    ) od
                    on
                        od.noreg = rs.noreg
                left join
                    (
                        select td1.* from terima_doc td1
                        right join
                            (select max(id) as id, no_order from terima_doc group by no_order) td2
                            on
                                td1.id = td2.id
                    ) td
                    on
                        od.no_order = td.no_order
                left join
                    (
                        select brg1.* from barang brg1
                        right join
                            (select max(id) as id, kode from barang group by kode) brg2
                            on
                                brg1.id = brg2.id
                    ) brg
                    on
                        brg.kode = od.item
                where
                    rs.noreg is not null
                order by
                    td.datang asc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $k_data => $v_data) {
                    $data[ $v_data['_key'] ] = $v_data;
                }
            }

            $return['status'] = 1;
            $return['data'] = $data;
        } catch (Exception $e) {
            $return['status'] = 0;
            $return['message'] = $e->getMessage();
        }

        return $return;
    }

    public function getDataPakan($tanggal, $unit, $perusahaan)
    {
        $return = null;

        try {
            $sql_unit = null;
            if ( !in_array('all', $unit) ) {
                $sql_unit = "and w.kode in ('".implode("', '", $unit)."')";
            }

            $sql_perusahaan = null;
            if ( !in_array('all', $perusahaan) ) {
                $sql_perusahaan = "and prs.kode in ('".implode("', '", $perusahaan)."')";
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    'pakan' as jenis,
                    rs.noreg,
                    ds.tgl_trans as tgl_beli,
                    tp.tgl_terima as tgl_distribusi,
                    dtp.item as kode_brg,
                    (dst.jumlah / 50) as zak,
                    dst.jumlah as tonase,
                    kp.ongkos_angkut as ongkos,
                    ds.hrg_beli as hrg_beli,
                    (dst.jumlah * ds.hrg_beli) as tot_beli,
                    kp.jenis_kirim as jenis_kirim,
                    rs.nama_mitra+' '+cast(cast(SUBSTRING(rs.noreg, 10, 2) as int) as varchar(2)) as nama_mitra,
                    rs.nama_perusahaan,
                    rs.nama_unit,
                    rs.kode_unit,
                    brg.nama as nama_barang,
                    'distribusi' as jenis_transaksi,
                    REPLACE(CONVERT(varchar(10), tp.tgl_terima, 23), '-', '')+'|'+rs.kode_unit+'|'+dtp.item+'|'+kp.no_order+'|'+cast(ds.hrg_beli as varchar(10))+'|tujuan|'+cast(kp.ongkos_angkut as varchar(30)) as _key
                from (
                        select 
                            rs.*, 
                            m.nama as nama_mitra,
                            prs.perusahaan as nama_perusahaan,
                            SUBSTRING(od.no_order, 5, 3) as kode_unit,
                            w.nama as nama_unit
                        from rdim_submit rs
                        left join
                            (
                                select od1.* from order_doc od1
                                right join
                                    (select max(id) as id, noreg from order_doc group by noreg) od2
                                    on
                                        od1.id = od2.id
                            ) od
                            on
                                rs.noreg = od.noreg
                        left join
                            (
                                select td1.* from terima_doc td1
                                right join
                                    (select max(id) as id, no_order from terima_doc group by no_order) td2
                                    on
                                        td1.id = td2.id
                            ) td
                            on
                                td.no_order = od.no_order
                        left join
                            (select * from tutup_siklus where tgl_tutup <= '".$tanggal."') ts
                            on
                                ts.noreg = rs.noreg
                        left join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                mm.nim = rs.nim
                        left join
                            mitra m
                            on
                                m.id = mm.mitra
                        left join
                            (
                                select prs1.* from perusahaan prs1
                                right join
                                    (select max(id) as id, kode from perusahaan group by kode) prs2
                                    on
                                        prs1.id = prs2.id
                            ) prs
                            on
                                prs.kode = od.perusahaan
                        left join
                            (
                                select 
                                    w1.kode, 
                                    REPLACE(REPLACE(w1.nama, 'Kab ', ''), 'Kota ', '') as nama
                                from wilayah w1
                                right join
                                    (select max(id) as id, kode from wilayah where kode is not null group by kode) w2
                                    on
                                        w1.id = w2.id
                            ) w
                            on
                                w.kode = SUBSTRING(od.no_order, 5, 3)
                        where
                            ts.id is null and
                            td.datang between '2024-01-01 00:00:00.001' and '".$tanggal." 23:59:59.999'
                            ".$sql_unit."
                            ".$sql_perusahaan."
                    ) rs
                left join
                    kirim_pakan kp
                    on
                        kp.tujuan = rs.noreg
                left join
                    (
                        select tp1.* from terima_pakan tp1
                        right join
                            (select max(id) as id, id_kirim_pakan from terima_pakan group by id_kirim_pakan) tp2
                            on
                                tp1.id = tp2.id
                    ) tp
                    on
                        kp.id = tp.id_kirim_pakan
                left join
                    det_terima_pakan dtp
                    on
                        dtp.id_header = tp.id
                left join
                    det_stok_trans dst
                    on
                        dst.kode_trans = kp.no_order and
                        dst.kode_barang = dtp.item
                left join
                    det_stok ds
                    on
                        ds.id = dst.id_header
                left join
                    (
                        select brg1.* from barang brg1
                        right join
                            (select max(id) as id, kode from barang group by kode) brg2
                            on
                                brg1.id = brg2.id
                    ) brg
                    on
                        brg.kode = dtp.item
                where
                    kp.jenis_kirim = 'opkg' and
                    tp.tgl_terima <= '".$tanggal."' and
                    rs.noreg is not null
                order by
                    tp.tgl_terima asc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
            $data_opkg = null;
            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $k_opkg => $v_opkg) {
                    $data_opkg[ $v_opkg['_key'] ] = $v_opkg;
                }
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    'pakan' as jenis,
                    rs.noreg,
                    rs.nama_mitra+' '+cast(cast(SUBSTRING(rs.noreg, 10, 2) as int) as varchar(2)) as nama_mitra,
                    rs.nama_perusahaan,
                    rs.nama_unit,
                    rs.kode_unit,
                    brg.nama as nama_barang,
                    tp.tgl_terima as tgl_terima,
                    kp.no_order,
                    kp.jenis_kirim,
                    dkp.item as kode_barang,
                    dkp.jumlah,
                    dkp.no_sj_asal,
                    case
                        when rs.noreg = kp.asal then
                            'asal'
                        else
                            'tujuan'
                    end as posisi,
                    'mutasi' as jenis_transaksi
                from (
                        select 
                            rs.*, 
                            m.nama as nama_mitra,
                            prs.perusahaan as nama_perusahaan,
                            SUBSTRING(od.no_order, 5, 3) as kode_unit,
                            w.nama as nama_unit
                        from rdim_submit rs
                        left join
                            (
                                select od1.* from order_doc od1
                                right join
                                    (select max(id) as id, noreg from order_doc group by noreg) od2
                                    on
                                        od1.id = od2.id
                            ) od
                            on
                                rs.noreg = od.noreg
                        left join
                            (
                                select td1.* from terima_doc td1
                                right join
                                    (select max(id) as id, no_order from terima_doc group by no_order) td2
                                    on
                                        td1.id = td2.id
                            ) td
                            on
                                td.no_order = od.no_order
                        left join
                            (select * from tutup_siklus where tgl_tutup <= '".$tanggal."') ts
                            on
                                ts.noreg = rs.noreg
                        left join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                mm.nim = rs.nim
                        left join
                            mitra m
                            on
                                m.id = mm.mitra
                        left join
                            (
                                select prs1.* from perusahaan prs1
                                right join
                                    (select max(id) as id, kode from perusahaan group by kode) prs2
                                    on
                                        prs1.id = prs2.id
                            ) prs
                            on
                                prs.kode = od.perusahaan
                        left join
                            (
                                select 
                                    w1.kode, 
                                    REPLACE(REPLACE(w1.nama, 'Kab ', ''), 'Kota ', '') as nama
                                from wilayah w1
                                right join
                                    (select max(id) as id, kode from wilayah where kode is not null group by kode) w2
                                    on
                                        w1.id = w2.id
                            ) w
                            on
                                w.kode = SUBSTRING(od.no_order, 5, 3)
                        where
                            ts.id is null and
                            td.datang between '2024-01-01 00:00:00.001' and '".$tanggal." 23:59:59.999'
                            ".$sql_unit."
                            ".$sql_perusahaan."
                    ) rs
                left join
                    kirim_pakan kp
                    on
                        kp.tujuan = rs.noreg or kp.asal = rs.noreg
                left join
                    det_kirim_pakan dkp
                    on
                        kp.id = dkp.id_header
                left join
                    (
                        select tp1.* from terima_pakan tp1
                        right join
                            (select max(id) as id, id_kirim_pakan from terima_pakan group by id_kirim_pakan) tp2
                            on
                                tp1.id = tp2.id
                    ) tp
                    on
                        kp.id = tp.id_kirim_pakan
                left join
                    (
                        select brg1.* from barang brg1
                        right join
                            (select max(id) as id, kode from barang group by kode) brg2
                            on
                                brg1.id = brg2.id
                    ) brg
                    on
                        brg.kode = dkp.item
                where
                    kp.jenis_kirim = 'opkp' and
                    tp.tgl_terima <= '".$tanggal."' and
                    rs.noreg is not null
                order by
                    tp.tgl_terima asc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
            $data_opkp = null;
            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $k_kp => $v_kp) {
                    $m_conf = new \Model\Storage\Conf();
                    $sql = "EXEC get_data_harga_pakan @no_order = '".$v_kp['no_order']."', @item = '".$v_kp['kode_barang']."', @jumlah = '".$v_kp['jumlah']."', @no_sj_asal = '".$v_kp['no_sj_asal']."', @pp = 1";
                    $d_dhp = $m_conf->hydrateRaw( $sql );

                    $m_kp = new \Model\Storage\KirimPakan_model();
                    $sql = "EXEC get_data_oa_pakan_new @no_order = '".$v_kp['no_order']."', @item = '".$v_kp['kode_barang']."', @jumlah = ".$v_kp['jumlah'].", @no_sj_asal = '".$v_kp['no_sj_asal']."', @pp = 1";
                    $d_oa_pakan = $m_kp->hydrateRaw( $sql );

                    $data_oa_pakan = null;
                    if ( $d_oa_pakan->count() > 0 ) {
                        $data_oa_pakan = $d_oa_pakan->toArray();
                    }

                    $idx_oa = 0;
                    if ( $d_dhp->count() > 0 ) {
                        $d_dhp = $d_dhp->toArray();


                        foreach ($d_dhp as $k_dhp => $v_dhp) {
                            $_jumlah = $v_dhp['jumlah'];

                            $jumlah_save = $_jumlah;
                            while ( $_jumlah > 0 ) {
                                $hrg_oa = 0;
                                if ( !empty($data_oa_pakan) ) {
                                    if ( $_jumlah < $data_oa_pakan[ $idx_oa ]['jumlah'] ) {
                                        $hrg_oa = $data_oa_pakan[ $idx_oa ]['oa'];
                                        $jumlah_save = $_jumlah;
                                        $_jumlah = 0;
                                    } else {
                                        $hrg_oa = $data_oa_pakan[ $idx_oa ]['oa'];
                                        $jumlah_save = $data_oa_pakan[ $idx_oa ]['jumlah'];
                                        $_jumlah = $_jumlah - $data_oa_pakan[ $idx_oa ]['jumlah'];
                                        $idx_oa++;
                                    }
                                }

                                $jumlah = ($v_kp['posisi'] == 'asal') ? 0-$jumlah_save : $jumlah_save;
                                $total = $v_dhp['harga'] * $jumlah;

                                $key = str_replace('-', '', $v_kp['tgl_terima']).'|'.$v_kp['kode_unit'].'|'.$v_kp['kode_barang'].'|'.$v_kp['no_order'].'|'.$v_dhp['harga'].'|'.$v_kp['posisi'].'|'.$hrg_oa;

                                if ( !isset($data_opkp[ $key ]) ) {
                                    $data_opkp[ $key ] = array(
                                        'jenis' => $v_kp['jenis'],
                                        'noreg' => $v_kp['noreg'],
                                        'tgl_beli' => $v_kp['tgl_terima'],
                                        'tgl_distribusi' => $v_kp['tgl_terima'],
                                        'kode_brg' => $v_kp['kode_barang'],
                                        'zak' => ($jumlah / 50),
                                        'tonase' => $jumlah,
                                        'ongkos' => $hrg_oa,
                                        'hrg_beli' => $v_dhp['harga'],
                                        'tot_beli' => $total,
                                        'jenis_kirim' => $v_kp['jenis_kirim'],
                                        'nama_mitra' => $v_kp['nama_mitra'],
                                        'nama_perusahaan' => $v_kp['nama_perusahaan'],
                                        'nama_unit' => $v_kp['nama_unit'],
                                        'kode_unit' => $v_kp['kode_unit'],
                                        'nama_barang' => $v_kp['nama_barang'],
                                        'jenis_transaksi' => $v_kp['jenis_transaksi'],
                                        '_key' => $key
                                    );
                                } else {
                                    $data_opkp[ $key ]['zak'] += ($jumlah / 50);
                                    $data_opkp[ $key ]['tonase'] += $jumlah;
                                    $data_opkp[ $key ]['tot_beli'] += $total;
                                }
                            }
                        }
                    }
                }
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    'pakan' as jenis,
                    rs.noreg,
                    rp.tgl_retur as tgl_beli,
                    rp.tgl_retur as tgl_distribusi,
                    drp.item as kode_brg,
                    (ds.jumlah / 50) as zak,
                    ds.jumlah as tonase,
                    case
                        when kp.jenis_kirim = 'opkg' then
                            kp.ongkos_angkut
                        else
                            0
                    end as ongkos,
                    ds.hrg_beli as hrg_beli,
                    (ds.jumlah * ds.hrg_beli) as tot_beli,
                    kp.jenis_kirim as jenis_kirim,
                    rs.nama_mitra+' '+cast(cast(SUBSTRING(rs.noreg, 10, 2) as int) as varchar(2)) as nama_mitra,
                    rs.nama_perusahaan,
                    rs.nama_unit,
                    rs.kode_unit,
                    brg.nama as nama_barang,
                    'asal' as posisi,
                    'retur' as jenis_transaksi
                from (
                        select 
                            rs.*, 
                            m.nama as nama_mitra,
                            prs.perusahaan as nama_perusahaan,
                            SUBSTRING(od.no_order, 5, 3) as kode_unit,
                            w.nama as nama_unit
                        from rdim_submit rs
                        left join
                            (
                                select od1.* from order_doc od1
                                right join
                                    (select max(id) as id, noreg from order_doc group by noreg) od2
                                    on
                                        od1.id = od2.id
                            ) od
                            on
                                rs.noreg = od.noreg
                        left join
                            (
                                select td1.* from terima_doc td1
                                right join
                                    (select max(id) as id, no_order from terima_doc group by no_order) td2
                                    on
                                        td1.id = td2.id
                            ) td
                            on
                                td.no_order = od.no_order
                        left join
                            (select * from tutup_siklus where tgl_tutup <= '".$tanggal."') ts
                            on
                                ts.noreg = rs.noreg
                        left join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                mm.nim = rs.nim
                        left join
                            mitra m
                            on
                                m.id = mm.mitra
                        left join
                            (
                                select prs1.* from perusahaan prs1
                                right join
                                    (select max(id) as id, kode from perusahaan group by kode) prs2
                                    on
                                        prs1.id = prs2.id
                            ) prs
                            on
                                prs.kode = od.perusahaan
                        left join
                            (
                                select 
                                    w1.kode, 
                                    REPLACE(REPLACE(w1.nama, 'Kab ', ''), 'Kota ', '') as nama
                                from wilayah w1
                                right join
                                    (select max(id) as id, kode from wilayah where kode is not null group by kode) w2
                                    on
                                        w1.id = w2.id
                            ) w
                            on
                                w.kode = SUBSTRING(od.no_order, 5, 3)
                        where
                            ts.id is null and
                            td.datang between '2024-01-01 00:00:00.001' and '".$tanggal." 23:59:59.999'
                            ".$sql_unit."
                            ".$sql_perusahaan."
                    ) rs
                left join
                    retur_pakan rp
                    on
                        rp.id_asal = rs.noreg
                left join
                    det_retur_pakan drp
                    on
                        rp.id = drp.id_header
                left join
                    (
                        select
                            ds.kode_trans,
                            ds.kode_barang,
                            ds.jumlah,
                            ds.hrg_jual,
                            ds.hrg_beli
                        from det_stok ds
                        where jenis_trans = 'RETUR' and jenis_barang = 'pakan'
                        group by
                            ds.kode_trans,
                            ds.kode_barang,
                            ds.jumlah,
                            ds.hrg_jual,
                            ds.hrg_beli
                    ) ds
                    on
                        ds.kode_trans = rp.no_order and
                        ds.kode_barang = drp.item
                left join
                    kirim_pakan kp
                    on
                        rp.no_order = kp.no_order
                left join
                    (
                        select brg1.* from barang brg1
                        right join
                            (select max(id) as id, kode from barang group by kode) brg2
                            on
                                brg1.id = brg2.id
                    ) brg
                    on
                        brg.kode = drp.item
                where
                    rp.tgl_retur <= '".$tanggal."' and
                    rs.noreg is not null
                order by
                    rp.tgl_retur asc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
            $data_retur = null;
            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $k_rp => $v_rp) {
                    $key = str_replace('-', '', $v_rp['tgl_beli']).'|'.$v_rp['unit'].'|'.$v_rp['kode_barang'].'|'.$v_rp['posisi'].'|'.$v_rp['no_order'].'|'.$v_rp['hrg_beli'].'|'.$v_rp['ongkos'];

                    if ( $v_kp['jenis_kirim'] == 'opkp' ) {
                        $m_conf = new \Model\Storage\Conf();
                        $sql = "
                            select
                                kp.no_order,
                                dkp.item,
                                dkp.jumlah,
                                dkp.no_sj_asal
                            from det_kirim_pakan dkp
                            left join
                                kirim_pakan kp
                                on
                                    dkp.id_header = kp.id
                            where
                                kp.no_order = '".$v_rp['no_order']."'
                        ";
                        $d_kp = $m_conf->hydrateRaw( $sql );

                        if ( $d_kp->count() > 0 ) {
                            $d_kp = $d_kp->toArray();

                            foreach ($d_kp as $k_kp => $v_kp) {
                                $_jumlah = $v_kp['jumlah'];

                                $m_kp = new \Model\Storage\KirimPakan_model();
                                $sql = "
                                    EXEC get_data_oa_pakan_new @no_order = '".$v_kp['no_order']."', @item = '".$v_kp['item']."', @jumlah = ".$v_kp['jumlah'].", @no_sj_asal = '".$v_kp['no_sj_asal']."', @pp = 1
                                ";
                                $d_oa_pakan = $m_kp->hydrateRaw( $sql );

                                $idx_oa = 0;
                                $data_oa_pakan = null;
                                if ( $d_oa_pakan->count() > 0 ) {
                                    $data_oa_pakan = $d_oa_pakan->toArray();

                                    $jumlah_save = $_jumlah;
                                    while ( $_jumlah > 0 ) {
                                        $hrg_oa = 0;
                                        if ( !empty($data_oa_pakan) ) {
                                            if ( $_jumlah < $data_oa_pakan[ $idx_oa ]['jumlah'] ) {
                                                $hrg_oa = $data_oa_pakan[ $idx_oa ]['oa'];
                                                $jumlah_save = $_jumlah;
                                                $_jumlah = 0;
                                            } else {
                                                $hrg_oa = $data_oa_pakan[ $idx_oa ]['oa'];
                                                $jumlah_save = $data_oa_pakan[ $idx_oa ]['jumlah'];
                                                $_jumlah = $_jumlah - $data_oa_pakan[ $idx_oa ]['jumlah'];
                                                $idx_oa++;
                                            }
                                        }

                                        $jumlah = ($v_rp['posisi'] == 'asal') ? 0-$jumlah_save : $jumlah_save;
                                        $total = $v_rp['hrg_beli'] * $jumlah;

                                        $key = str_replace('-', '', $v_rp['tgl_beli']).'|'.$v_rp['unit'].'|'.$v_rp['kode_barang'].'|'.$v_rp['posisi'].'|'.$v_rp['no_order'].'|'.$v_rp['hrg_beli'].'|'.$hrg_oa;

                                        if ( !isset($data_retur[ $key ]) ) {
                                            $data_retur[ $key ] = array(
                                                'jenis' => $v_rp['jenis'],
                                                'noreg' => $v_rp['noreg'],
                                                'tgl_beli' => $v_rp['tgl_beli'],
                                                'tgl_distribusi' => $v_rp['tgl_beli'],
                                                'kode_brg' => $v_rp['kode_barang'],
                                                'zak' => ($jumlah / 50),
                                                'tonase' => $jumlah,
                                                'ongkos' => $hrg_oa,
                                                'hrg_beli' => $v_rp['hrg_beli'],
                                                'tot_beli' => $total,
                                                'jenis_kirim' => $v_rp['jenis_kirim'],
                                                'nama_mitra' => $v_rp['nama_mitra'],
                                                'nama_perusahaan' => $v_rp['nama_perusahaan'],
                                                'nama_unit' => $v_rp['nama_unit'],
                                                'kode_unit' => $v_rp['kode_unit'],
                                                'nama_barang' => $v_rp['nama_barang'],
                                                'jenis_transaksi' => $v_rp['jenis_transaksi'],
                                                '_key' => $key
                                            );
                                        } else {
                                            $data_retur[ $key ]['zak'] += ($jumlah / 50);
                                            $data_retur[ $key ]['tonase'] += $jumlah;
                                            $data_retur[ $key ]['tot_beli'] += $total;
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $data_retur[ $key ] = $v_rp;
                        $data_retur[ $key ]['_key'] = $key;
                    }
                }
            }

            $data = null;

            if ( !empty($data_opkg) ) {
                if ( !empty( $data ) ) {
                    $data = array_merge( $data, $data_opkg );
                } else {
                    $data = $data_opkg;
                }
            }

            if ( !empty($data_opkp) ) {
                if ( !empty( $data ) ) {
                    $data = array_merge( $data, $data_opkp );
                } else {
                    $data = $data_opkp;
                }
            }

            if ( !empty($data_retur) ) {
                if ( !empty( $data ) ) {
                    $data = array_merge( $data, $data_retur );
                } else {
                    $data = $data_retur;
                }
            }
            
            if ( !empty( $data ) ) {
                ksort( $data );
            }

            $return['status'] = 1;
            $return['data'] = $data;
        } catch (Exception $e) {
            $return['status'] = 0;
            $return['message'] = $e->getMessage();
        }

        return $return;
    }

    public function getDataVoadip($tanggal, $unit, $perusahaan)
    {
        $return = null;

        try {
            $sql_unit = null;
            if ( !in_array('all', $unit) ) {
                $sql_unit = "and w.kode in ('".implode("', '", $unit)."')";
            }

            $sql_perusahaan = null;
            if ( !in_array('all', $perusahaan) ) {
                $sql_perusahaan = "and prs.kode in ('".implode("', '", $perusahaan)."')";
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    'voadip' as jenis,
                    rs.noreg,
                    ds.tgl_trans as tgl_beli,
                    tv.tgl_terima as tgl_distribusi,
                    dtv.item as kode_brg,
                    0 as zak,
                    dst.jumlah as tonase,
                    0 as ongkos,
                    ds.hrg_beli as hrg_beli,
                    (dst.jumlah * ds.hrg_beli) as tot_beli,
                    kv.jenis_kirim as jenis_kirim,
                    rs.nama_mitra+' '+cast(cast(SUBSTRING(rs.noreg, 10, 2) as int) as varchar(2)) as nama_mitra,
                    rs.nama_perusahaan,
                    rs.nama_unit,
                    rs.kode_unit,
                    brg.nama as nama_barang,
                    'distribusi' as jenis_transaksi,
                    REPLACE(CONVERT(varchar(10), tv.tgl_terima, 23), '-', '')+'|'+rs.kode_unit+'|'+dtv.item+'|'+kv.no_order+'|'+cast(ds.hrg_beli as varchar(30))+'|tujuan|0' as _key
                from (
                        select 
                            rs.*, 
                            m.nama as nama_mitra,
                            prs.perusahaan as nama_perusahaan,
                            SUBSTRING(od.no_order, 5, 3) as kode_unit,
                            w.nama as nama_unit
                        from rdim_submit rs
                        left join
                            (
                                select od1.* from order_doc od1
                                right join
                                    (select max(id) as id, noreg from order_doc group by noreg) od2
                                    on
                                        od1.id = od2.id
                            ) od
                            on
                                rs.noreg = od.noreg
                        left join
                            (
                                select td1.* from terima_doc td1
                                right join
                                    (select max(id) as id, no_order from terima_doc group by no_order) td2
                                    on
                                        td1.id = td2.id
                            ) td
                            on
                                td.no_order = od.no_order
                        left join
                            (select * from tutup_siklus where tgl_tutup <= '".$tanggal."') ts
                            on
                                ts.noreg = rs.noreg
                        left join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                mm.nim = rs.nim
                        left join
                            mitra m
                            on
                                m.id = mm.mitra
                        left join
                            (
                                select prs1.* from perusahaan prs1
                                right join
                                    (select max(id) as id, kode from perusahaan group by kode) prs2
                                    on
                                        prs1.id = prs2.id
                            ) prs
                            on
                                prs.kode = od.perusahaan
                        left join
                            (
                                select 
                                    w1.kode, 
                                    REPLACE(REPLACE(w1.nama, 'Kab ', ''), 'Kota ', '') as nama
                                from wilayah w1
                                right join
                                    (select max(id) as id, kode from wilayah where kode is not null group by kode) w2
                                    on
                                        w1.id = w2.id
                            ) w
                            on
                                w.kode = SUBSTRING(od.no_order, 5, 3)
                        where
                            ts.id is null and
                            td.datang between '2024-01-01 00:00:00.001' and '".$tanggal." 23:59:59.999'
                            ".$sql_unit."
                            ".$sql_perusahaan."
                    ) rs
                left join
                    kirim_voadip kv
                    on
                        kv.tujuan = rs.noreg
                left join
                    (
                        select tv1.* from terima_voadip tv1
                        right join
                            (select max(id) as id, id_kirim_voadip from terima_voadip group by id_kirim_voadip) tv2
                            on
                                tv1.id = tv2.id
                    ) tv
                    on
                        kv.id = tv.id_kirim_voadip
                left join
                    det_terima_voadip dtv
                    on
                        dtv.id_header = tv.id
                left join
                    det_stok_trans dst
                    on
                        dst.kode_trans = kv.no_order and
                        dst.kode_barang = dtv.item
                left join
                    det_stok ds
                    on
                        ds.id = dst.id_header
                left join
                    (
                        select brg1.* from barang brg1
                        right join
                            (select max(id) as id, kode from barang group by kode) brg2
                            on
                                brg1.id = brg2.id
                    ) brg
                    on
                        brg.kode = dtv.item
                where
                    kv.jenis_kirim = 'opkg' and
                    tv.tgl_terima <= '".$tanggal."' and
                    rs.noreg is not null
                order by
                    tv.tgl_terima asc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
            $data_opkg = null;
            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $k_opkg => $v_opkg) {
                    $data_opkg[ $v_opkg['_key'] ] = $v_opkg;
                }
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    'voadip' as jenis,
                    rs.noreg,
                    rv.tgl_retur as tgl_beli,
                    rv.tgl_retur as tgl_distribusi,
                    drv.item as kode_brg,
                    0 as zak,
                    (0-ds.jumlah) as tonase,
                    0 as ongkos,
                    ds.hrg_beli as hrg_beli,
                    ((0-ds.jumlah) * ds.hrg_beli) as tot_beli,
                    kv.jenis_kirim as jenis_kirim,
                    rs.nama_mitra+' '+cast(cast(SUBSTRING(rs.noreg, 10, 2) as int) as varchar(2)) as nama_mitra,
                    rs.nama_perusahaan,
                    rs.nama_unit,
                    rs.kode_unit,
                    brg.nama as nama_barang,
                    'retur' as jenis_transaksi,
                    REPLACE(CONVERT(varchar(10), rv.tgl_retur, 23), '-', '')+'|'+rs.kode_unit+'|'+drv.item+'|'+kv.no_order+'|'+cast(ds.hrg_beli as varchar(30))+'|tujuan|0' as _key
                from (
                        select 
                            rs.*, 
                            m.nama as nama_mitra,
                            prs.perusahaan as nama_perusahaan,
                            SUBSTRING(od.no_order, 5, 3) as kode_unit,
                            w.nama as nama_unit
                        from rdim_submit rs
                        left join
                            (
                                select od1.* from order_doc od1
                                right join
                                    (select max(id) as id, noreg from order_doc group by noreg) od2
                                    on
                                        od1.id = od2.id
                            ) od
                            on
                                rs.noreg = od.noreg
                        left join
                            (
                                select td1.* from terima_doc td1
                                right join
                                    (select max(id) as id, no_order from terima_doc group by no_order) td2
                                    on
                                        td1.id = td2.id
                            ) td
                            on
                                td.no_order = od.no_order
                        left join
                            (select * from tutup_siklus where tgl_tutup <= '".$tanggal."') ts
                            on
                                ts.noreg = rs.noreg
                        left join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                mm.nim = rs.nim
                        left join
                            mitra m
                            on
                                m.id = mm.mitra
                        left join
                            (
                                select prs1.* from perusahaan prs1
                                right join
                                    (select max(id) as id, kode from perusahaan group by kode) prs2
                                    on
                                        prs1.id = prs2.id
                            ) prs
                            on
                                prs.kode = od.perusahaan
                        left join
                            (
                                select 
                                    w1.kode, 
                                    REPLACE(REPLACE(w1.nama, 'Kab ', ''), 'Kota ', '') as nama
                                from wilayah w1
                                right join
                                    (select max(id) as id, kode from wilayah where kode is not null group by kode) w2
                                    on
                                        w1.id = w2.id
                            ) w
                            on
                                w.kode = SUBSTRING(od.no_order, 5, 3)
                        where
                            ts.id is null and
                            td.datang between '2024-01-01 00:00:00.001' and '".$tanggal." 23:59:59.999'
                            ".$sql_unit."
                            ".$sql_perusahaan."
                    ) rs
                left join
                    retur_voadip rv
                    on
                        rv.id_asal = rs.noreg
                left join
                    det_retur_voadip drv
                    on
                        rv.id = drv.id_header
                left join
                    (
                        select
                            ds.kode_trans,
                            ds.kode_barang,
                            ds.jumlah,
                            ds.hrg_jual,
                            ds.hrg_beli
                        from det_stok ds
                        where jenis_trans = 'RETUR' and jenis_barang = 'voadip'
                        group by
                            ds.kode_trans,
                            ds.kode_barang,
                            ds.jumlah,
                            ds.hrg_jual,
                            ds.hrg_beli
                    ) ds
                    on
                        ds.kode_trans = rv.no_order and
                        ds.kode_barang = drv.item
                left join
                    kirim_voadip kv
                    on
                        rv.no_order = kv.no_order
                left join
                    (
                        select brg1.* from barang brg1
                        right join
                            (select max(id) as id, kode from barang group by kode) brg2
                            on
                                brg1.id = brg2.id
                    ) brg
                    on
                        brg.kode = drv.item
                where
                    rv.tgl_retur <= '".$tanggal."' and
                    rs.noreg is not null and
                    drv.jumlah > 0
                order by
                    rv.tgl_retur asc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
            $data_retur = null;
            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $k_retur => $v_retur) {
                    $data_retur[ $v_retur['_key'] ] = $v_retur;
                }
            }

            $data = null;

            if ( !empty($data_opkg) ) {
                if ( !empty( $data ) ) {
                    $data = array_merge( $data, $data_opkg );
                } else {
                    $data = $data_opkg;
                }
            }

            if ( !empty($data_retur) ) {
                if ( !empty( $data ) ) {
                    $data = array_merge( $data, $data_retur );
                } else {
                    $data = $data_retur;
                }
            }
            
            if ( !empty( $data ) ) {
                ksort( $data );
            }

            $return['status'] = 1;
            $return['data'] = $data;
        } catch (Exception $e) {
            $return['status'] = 0;
            $return['message'] = $e->getMessage();
        }

        return $return;
    }

    public function encryptParams()
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

        $tanggal = $params['tanggal'];
        $jenis = $params['jenis'];
        $unit = $params['unit'];
        $perusahaan = $params['perusahaan'];

        $data_doc = null;
        $data_pakan = null;
        $data_voadip = null;

        if ( in_array('all', $jenis) || in_array('doc', $jenis) ) {
            $data_doc = $this->getDataDoc($tanggal, $unit, $perusahaan);
        }
        if ( in_array('all', $jenis) || in_array('pakan', $jenis) ) {
            $data_pakan = $this->getDataPakan($tanggal, $unit, $perusahaan);
        }
        if ( in_array('all', $jenis) || in_array('voadip', $jenis) ) {
            $data_voadip = $this->getDataVoadip($tanggal, $unit, $perusahaan);
        }

        $data = null;

        if ( !empty($data_doc) ) {
            if ( !empty( $data ) ) {
                $data = array_merge( $data, $data_doc['data'] );
            } else {
                $data = $data_doc['data'];
            }
        }

        if ( !empty($data_pakan) ) {
            if ( !empty( $data ) ) {
                $data = array_merge( $data, $data_pakan['data'] );
            } else {
                $data = $data_pakan['data'];
            }
        }

        if ( !empty($data_voadip) ) {
            if ( !empty( $data ) ) {
                $data = array_merge( $data, $data_voadip['data'] );
            } else {
                $data = $data_voadip['data'];
            }
        }

        if ( !empty( $data ) ) {
            ksort( $data );
        }

        $filename = "LAPORAN_STOK_BELUM_TUTUP_SIKLUS_PER_";
        $filename = $filename.str_replace('-', '', $tanggal).'.xls';

        $arr_header = array('Transaksi', 'Tanggal', 'Perusahaan', 'Plasma', 'Periode', 'Unit', 'Jenis', 'Box / Zak', 'Ekor / Tonase / Pcs', 'Ongkos', 'Hrg Beli', 'Tot Beli', 'Tot OA');
        $arr_column = null;
        if ( !empty($data) ) {
            $idx = 0;

            $tot_zak = 0;
            $tot_tonase = 0;
            $tot_beli = 0;
            $tot_oa = 0;

            foreach ($data as $key => $value) {
                $arr_column[ $idx ] = array(
                    'Transaksi' => array('value' => strtoupper($value['jenis_transaksi']), 'data_type' => 'string'),
                    'Tanggal' => array('value' => $value['tgl_distribusi'], 'data_type' => 'date'),
                    'Perusahaan' => array('value' => strtoupper($value['nama_perusahaan']), 'data_type' => 'string'),
                    'Plasma' => array('value' => strtoupper($value['nama_mitra']), 'data_type' => 'string'),
                    'Periode' => array('value' => '-', 'data_type' => 'string'),
                    'Unit' => array('value' => $value['kode_unit'], 'data_type' => 'string'),
                    'Jenis' => array('value' => $value['nama_barang'], 'data_type' => 'string'),
                    'Box / Zak' => array('value' => $value['zak'], 'data_type' => 'decimal2'),
                    'Ekor / Tonase / Pcs' => array('value' => $value['tonase'], 'data_type' => 'decimal2'),
                    'Ongkos' => array('value' => $value['ongkos'], 'data_type' => 'decimal2'),
                    'Hrg Beli' => array('value' => $value['hrg_beli'], 'data_type' => 'decimal2'),
                    'Tot Beli' => array('value' => $value['tot_beli'], 'data_type' => 'decimal2'),
                    'Tot OA' => array('value' => ($value['tonase'] * $value['ongkos']), 'data_type' => 'decimal2'),
                );

                $tot_zak += $value['zak'];
                $tot_tonase += $value['tonase'];
                $tot_beli += $value['tot_beli'];
                $tot_oa += ($value['tonase'] * $value['ongkos']);


                $idx++;
            }

            $arr_column[ $idx ] = array(
                'Jenis' => array('value' => 'TOTAL', 'data_type' => 'string', 'colspan' => array('A', 'G'), 'text_style' => 'bold'),
                'Box / Zak' => array('value' => $tot_zak, 'data_type' => 'decimal2', 'text_style' => 'bold'),
                'Ekor / Tonase / Pcs' => array('value' => $tot_tonase, 'data_type' => 'decimal2', 'text_style' => 'bold'),
                'Hrg Beli' => array('value' => '', 'data_type' => 'decimal2', 'colspan' => array('J', 'K'), 'text_style' => 'bold'),
                'Tot Beli' => array('value' => $tot_beli, 'data_type' => 'decimal2', 'text_style' => 'bold'),
                'Tot OA' => array('value' => $tot_oa, 'data_type' => 'decimal2', 'text_style' => 'bold')
            );
        }

        $this->exportExcelUsingSpreadSheet( $filename, $arr_header, $arr_column );
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
                    
                    if ( isset($arr_column[ $i ][ $arr_header[ $j ] ]) ) {
                        $data = $arr_column[ $i ][ $arr_header[ $j ] ];

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
    
                        if ( isset($data['text_style']) ) {
                            if ( $data['text_style'] == 'bold' ) {
                                $sheet->getStyle($huruf.$baris)->getFont()->setBold(true);
                            }
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
}