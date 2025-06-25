<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class DistribusiBarang extends Public_Controller {

    private $pathView = 'report/distribusi_barang/';
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
                "assets/report/distribusi_barang/js/distribusi-barang.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/distribusi_barang/css/distribusi-barang.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['barang'] = $this->getBarang();
            $content['unit'] = $this->getUnit();
            $content['perusahaan'] = $this->getPerusahaan();

            $content['title_menu'] = 'Distribusi Barang';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getBarang() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                brg.*
            from barang brg
            right join
                (select max(id) as id, kode from barang group by kode) _brg
                on
                    _brg.id = brg.id
            where
                brg.tipe in ('pakan', 'obat')
            order by
                brg.tipe asc,
                brg.nama asc
        ";
        $d_brg = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_brg->count() > 0 ) {
            $data = $d_brg->toArray();
        }

        return $data;
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
            select p.id, p.kode, p.perusahaan as nama from perusahaan p
            right join
                (select max(id) as id, kode from perusahaan group by kode) _p
                on
                    _p.id = p.id
            order by
                p.perusahaan asc
        ";
        $d_perusahaan = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_perusahaan->count() > 0 ) {
            $data = $d_perusahaan->toArray();
        }

        return $data;
    }

    public function getDataPakan( $start_date, $end_date, $barang, $unit, $perusahaan )
    {
        $sql_brg_kp = "";
        $sql_brg_rp = "";
        if ( !in_array('all', $barang) ) {
            $sql_brg_kp .= "and dtp.item in ('".implode("', '", $barang)."')";
            $sql_brg_rp .= "and drp.item in ('".implode("', '", $barang)."')";
        }

        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit .= "and data.unit in ('".implode("', '", $unit)."')";
        }

        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan .= "and data.kode_prs in ('".implode("', '", $perusahaan)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.datang,
                data.unit,
                data.noreg,
                data.peternak as nama_peternak,
                data.kode_barang,
                data.barang,
                data.no_order,
                data.no_sj,
                sum(data.jumlah) as jumlah,
                data.hrg_beli as hrg_beli,
                sum(data.tot_beli) as tot_beli,
                data.hrg_jual as hrg_jual,
                sum(data.tot_jual) as tot_jual,
                data.urut,
                data.kode_prs,
                data.nama_prs,
                data.jenis_kirim,
                data.jenis,
                data.posisi,
                data.jenis_peternak,
                data.oa,
                data.oa_mutasi
            from
            (
                select
                    kp.tgl_terima as datang,
                    w.kode as unit,
                    kp.peternak as noreg,
                    peternak.nama as peternak,
                    brg.kode as kode_barang,
                    brg.nama as barang,
                    kp.no_order,
                    kp.no_sj,
                    ds.jumlah,
                    ds.hrg_beli,
                    (ds.jumlah * ds.hrg_beli) as tot_beli,
                    dhs.hrg_peternak as hrg_jual,
                    (ds.jumlah * dhs.hrg_peternak) as tot_jual,
                    CASE
                        WHEN kp.jenis_kirim = 'opks' THEN
                            1
                        WHEN kp.jenis_kirim = 'opkg' THEN
                            2
                        ELSE
                            3
                    END as urut,
                    prs.kode as kode_prs,
                    prs.nama as nama_prs,
                    kp.jenis_kirim,
                    kp.jenis,
                    kp.posisi,
                    peternak.jenis as jenis_peternak,
                    kp.oa,
                    oap.oa as oa_mutasi
                from 
                    (
                        select * from (
                            select
                                kp.no_order,
                                kp.no_sj,
                                kp.asal as peternak,
                                tp.tgl_terima,
                                dtp.item,
                                kp.jenis_kirim,
                                'mutasi' as jenis,
                                'asal' as posisi,
                                kp.ongkos_angkut as oa
                            from det_terima_pakan dtp 
                            right join
                                terima_pakan tp 
                                on
                                    dtp.id_header = tp.id
                            left join
                                kirim_pakan kp 
                                on
                                    tp.id_kirim_pakan = kp.id
                            where
                                kp.jenis_kirim in ('opkp') and
                                tp.tgl_terima between '".$start_date."' and '".$end_date."'
                                ".$sql_brg_kp."

                            union all

                            select
                                kp.no_order,
                                kp.no_sj,
                                kp.tujuan as peternak,
                                tp.tgl_terima,
                                dtp.item,
                                kp.jenis_kirim,
                                case
                                    when kp.jenis_kirim = 'opkp' then
                                        'mutasi'
                                    else
                                        'distribusi'
                                end as jenis,
                                'tujuan' as posisi,
                                kp.ongkos_angkut as oa
                            from det_terima_pakan dtp 
                            right join
                                terima_pakan tp 
                                on
                                    dtp.id_header = tp.id
                            left join
                                kirim_pakan kp 
                                on
                                    tp.id_kirim_pakan = kp.id
                            where
                                kp.jenis_kirim in ('opkg', 'opkp') and
                                tp.tgl_terima between '".$start_date."' and '".$end_date."'
                                ".$sql_brg_kp."
                            
                            union all

                            select
                                rp.no_order,
                                rp.no_retur as no_sj,
                                rp.id_asal as peternak,
                                rp.tgl_retur as tgl_terima,
                                drp.item,
                                kp.jenis_kirim,
                                'retur' as jenis,
                                'asal' as posisi,
                                0 as oa
                            from det_retur_pakan drp
                            right join
                                retur_pakan rp
                                on
                                    drp.id_header = rp.id
                            left join
                                kirim_pakan kp
                                on
                                    kp.no_order = rp.no_order
                            where
                                rp.tgl_retur between '".$start_date."' and '".$end_date."'
                                ".$sql_brg_rp."
                        ) data
                    ) kp
                left join
                    (
                        select 
                            oapp.*,
                            case
                                when oapp.ongkos_angkut > 0 then
                                    oapp.ongkos_angkut / dt.jumlah
                                else
                                    0
                            end as oa
                        from oa_pindah_pakan oapp
                        left join
                            (
                                select * from (
                                    select kp.no_sj as kode_trans, sum(jumlah) as jumlah from det_terima_pakan dtp
                                    left join
                                        terima_pakan tp
                                        on
                                            dtp.id_header = tp.id
                                    left join
                                        kirim_pakan kp
                                        on
                                            tp.id_kirim_pakan = kp.id
                                    where
                                        kp.no_sj is not null
                                    group by
                                        kp.no_sj
    
                                    union all
    
                                    select rp.no_retur as kode_trans, sum(drp.jumlah) as jumlah from det_retur_pakan drp
                                    left join
                                        retur_pakan rp
                                        on
                                            drp.id_header = rp.id
                                    where
                                        rp.no_retur is not null
                                    group by
                                        rp.no_retur
                                ) data
                            ) dt
                            on
                                oapp.no_sj = dt.kode_trans
                    ) oap
                    on
                        oap.no_sj = kp.no_sj
                left join
                    (
                        select
                            ds.kode_trans,
                            ds.kode_barang,
                            ds.jumlah,
                            ds.hrg_jual,
                            ds.hrg_beli
                        from det_stok ds
                        group by
                            ds.kode_trans,
                            ds.kode_barang,
                            ds.jumlah,
                            ds.hrg_jual,
                            ds.hrg_beli

                        union all

                        select
                            dst.kode_trans,
                            ds.kode_barang,
                            dst.jumlah,
                            ds.hrg_jual,
                            ds.hrg_beli
                        from det_stok_trans dst
                        left join
                            det_stok ds
                            on
                                dst.id_header = ds.id
                        group by
                            dst.kode_trans,
                            ds.kode_barang,
                            dst.jumlah,
                            ds.hrg_jual,
                            ds.hrg_beli
                    ) ds
                    on
                        ds.kode_trans = kp.no_order and
                        ds.kode_barang = kp.item
                left join
                    (
                        select
                            '' as perusahaan,
                            cast(p1.nomor as varchar(15)) as kode,
                            p1.nama,
                            'pelanggan' as jenis
                        from pelanggan p1
                        right join
                            (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' and mstatus = 1 group by nomor) p2
                            on
                                p1.id = p2.id
                        
                        union all

                        select
                            mtr.perusahaan as perusahaan,
                            cast(rs.noreg as varchar(15)) as kode,
                            mtr.nama+' (KDG : '+SUBSTRING(rs.noreg, 10, 2)+')' as nama,
                            'peternak' as jenis
                        from rdim_submit rs
                        left join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                rs.nim = mm.nim
                        left join
                            mitra mtr
                            on
                                mtr.id = mm.mitra

                        union all

                        select
                            gdg.perusahaan as perusahaan,
                            cast(gdg.id as varchar(15)) as kode,
                            gdg.nama,
                            'gudang' as jenis
                        from gudang gdg
                    ) peternak
                    on
                        peternak.kode = kp.peternak
                left join
                    (
                        select b1.* from barang b1
                        right join
                            (select max(id) as id, kode from barang b group by kode) b2
                            on
                                b1.id = b2.id
                    ) brg
                    on
                        brg.kode = kp.item
                left join
                    (
                        select p.id, p.kode, p.perusahaan as nama from perusahaan p
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) _p
                            on
                                _p.id = p.id
                    ) prs
                    on
                        prs.kode = peternak.perusahaan 
                        -- or prs.kode = tujuan.perusahaan
                left join
                    wilayah w
                    on
                        kp.no_order like '%'+w.kode+'%'
                left join
                    rdim_submit rs
                    on
                        kp.peternak = rs.noreg
                left join
                    perwakilan_maping pm
                    on
                        pm.id = rs.format_pb
                left join
                    hitung_budidaya_item hbi
                    on
                        hbi.id = pm.id_hbi
                left join
                    (
                        select dhs.*, hs.id_sk from det_harga_sapronak dhs
                        left join
                            harga_sapronak hs
                            on
                                dhs.id_header = hs.id
                    ) dhs
                    on
                        dhs.id_sk = hbi.id_sk and
                        dhs.kode_brg = brg.kode
            ) data
            where
                data.jenis_kirim <> 'opks' and
                data.datang is not null
                ".$sql_perusahaan."
                ".$sql_unit."
            group by
                data.datang,
                data.unit,
                data.noreg,
                data.peternak,
                data.kode_barang,
                data.barang,
                data.no_order,
                data.no_sj,
                data.hrg_beli,
                data.hrg_jual,
                data.urut,
                data.kode_prs,
                data.nama_prs,
                data.jenis_kirim,
                data.jenis,
                data.posisi,
                data.jenis_peternak,
                data.oa,
                data.oa_mutasi
            order by
                data.datang asc,
                data.urut,
                data.barang asc,
                data.no_order asc
        ";
        $d_pakan = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_pakan->count() ) {
            $d_pakan = $d_pakan->toArray();

            foreach ($d_pakan as $key => $value) {
                $key = str_replace('-', '', $value['datang']).'|'.$value['unit'].'|'.$value['kode_barang'].'|'.$value['posisi'].'|'.$value['no_sj'].'|'.$value['hrg_beli'];

                if ( $value['jenis_kirim'] == 'opkp' && $value['jenis'] <> 'retur' ) {
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
                            kp.no_order = '".$value['no_order']."'
                    ";
                    $d_kp = $m_conf->hydrateRaw( $sql );

                    if ( $d_kp->count() > 0 ) {
                        $d_kp = $d_kp->toArray();

                        foreach ($d_kp as $k_kp => $v_kp) {
                            $m_conf = new \Model\Storage\Conf();
                            // $sql = "EXEC get_data_harga_pakan @no_order = '".$v_kp['no_order']."', @item = '".$v_kpd['item']."', @jumlah = '".$jml_pindah."', @no_sj_asal = '".$v_kpd['no_sj_asal']."', @pp = 1";
                            $sql = "EXEC get_data_harga_pakan @no_order = '".$v_kp['no_order']."', @item = '".$v_kp['item']."', @jumlah = '".$v_kp['jumlah']."', @no_sj_asal = '".$v_kp['no_sj_asal']."', @pp = 1";
                            $d_dhp = $m_conf->hydrateRaw( $sql );

                            $m_kp = new \Model\Storage\KirimPakan_model();
                            $sql = "
                                EXEC get_data_oa_pakan_new @no_order = '".$v_kp['no_order']."', @item = '".$v_kp['item']."', @jumlah = ".$v_kp['jumlah'].", @no_sj_asal = '".$v_kp['no_sj_asal']."', @pp = 1
                            ";
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

                                        $jumlah = ($value['posisi'] == 'asal') ? 0-$jumlah_save : $jumlah_save;
                                        $total = $v_dhp['harga'] * $jumlah;
                                        $total_jual = $value['hrg_jual'] * $jumlah;

                                        $key = str_replace('-', '', $value['datang']).'|'.$value['unit'].'|'.$value['kode_barang'].'|'.$value['no_sj'].'|'.$v_dhp['harga'].'|'.$value['posisi'].'|'.$hrg_oa;

                                        if ( !isset($data[ $key ]) ) {
                                            $data[ $key ] = array(
                                                'datang' => $value['datang'],
                                                'unit' => $value['unit'],
                                                'nama_peternak' => $value['nama_peternak'],
                                                'kode_barang' => $value['kode_barang'],
                                                'barang' => $value['barang'],
                                                'no_order' => $value['no_order'],
                                                'no_sj' => $value['no_sj'],
                                                'jumlah' => $jumlah,
                                                'hrg_beli' => $v_dhp['harga'],
                                                'tot_beli' => $total,
                                                'hrg_jual' => $value['hrg_jual'],
                                                'tot_jual' => $total_jual,
                                                'urut' => $value['urut'],
                                                'kode_prs' => $value['kode_prs'],
                                                'nama_prs' => $value['nama_prs'],
                                                'jenis_kirim' => $value['jenis_kirim'],
                                                'jenis' => $value['jenis'],
                                                'oa' => $hrg_oa,
                                                'oa_mutasi' => $value['oa_mutasi'],
                                            );
                                        } else {
                                            $data[ $key ]['jumlah'] += $jumlah;
                                            $data[ $key ]['tot_beli'] += $total;
                                            $data[ $key ]['tot_jual'] += $total_jual;
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if ( $value['jenis'] == 'retur' ) {
                        // $data[ $key ] = $value;
                        if ( $value['jenis_kirim'] == 'opkp' ) {
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
                                    kp.no_order = '".$value['no_order']."'
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

                                            $jumlah = ($value['posisi'] == 'asal') ? 0-$jumlah_save : $jumlah_save;
                                            $total = $v_kp['hrg_beli'] * $jumlah;
                                            $total_jual = $value['hrg_jual'] * $jumlah;

                                            $key = str_replace('-', '', $value['datang']).'|'.$value['unit'].'|'.$value['kode_barang'].'|'.$value['no_sj'].'|'.$v_kp['hrg_beli'].'|'.$value['posisi'].'|'.$hrg_oa;

                                            if ( !isset($data[ $key ]) ) {
                                                $data[ $key ] = array(
                                                    'datang' => $value['datang'],
                                                    'unit' => $value['unit'],
                                                    'nama_peternak' => $value['nama_peternak'],
                                                    'kode_barang' => $value['kode_barang'],
                                                    'barang' => $value['barang'],
                                                    'no_order' => $value['no_order'],
                                                    'no_sj' => $value['no_sj'],
                                                    'jumlah' => $jumlah,
                                                    'hrg_beli' => $v_kp['hrg_beli'],
                                                    'tot_beli' => $total,
                                                    'hrg_jual' => $value['hrg_jual'],
                                                    'tot_jual' => $total_jual,
                                                    'urut' => $value['urut'],
                                                    'kode_prs' => $value['kode_prs'],
                                                    'nama_prs' => $value['nama_prs'],
                                                    'jenis_kirim' => $value['jenis_kirim'],
                                                    'jenis' => $value['jenis'],
                                                    'oa' => $hrg_oa,
                                                    'oa_mutasi' => $value['oa_mutasi'],
                                                );
                                            } else {
                                                $data[ $key ]['jumlah'] += $jumlah;
                                                $data[ $key ]['tot_beli'] += $total;
                                                $data[ $key ]['tot_jual'] += $total_jual;
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            $m_conf = new \Model\Storage\Conf();
                            $sql = "
                                select
                                    kp.ongkos_angkut
                                from det_kirim_pakan dkp
                                left join
                                    kirim_pakan kp
                                    on
                                        dkp.id_header = kp.id
                                where
                                    kp.no_order = '".$value['no_order']."'
                            ";
                            $d_kp = $m_conf->hydrateRaw( $sql );

                            $ongkos_angkut = 0;
                            if ( $d_kp->count() > 0 ) {
                                $ongkos_angkut = $d_kp->toArray()[0]['ongkos_angkut'];
                            }

                            $jumlah = ($value['posisi'] == 'asal') ? 0-$value['jumlah'] : $value['jumlah'];
                            $total = $value['hrg_beli'] * $jumlah;
                            $total_jual = $value['hrg_jual'] * $jumlah;

                            $data[ $key ] = $value;
                            $data[ $key ]['jumlah'] = $jumlah;
                            $data[ $key ]['tot_beli'] = $total;
                            $data[ $key ]['tot_jual'] = $total_jual;
                            $data[ $key ]['oa'] = $ongkos_angkut;
                        }
                    } else {
                        $data[ $key ] = $value;
                    }
                }
            }

            ksort( $data );
        }

        return $data;
    }

    public function getDataVoadip( $start_date, $end_date, $barang, $unit, $perusahaan )
    {
        $sql_brg_kv = "";
        $sql_brg_rv = "";
        if ( !in_array('all', $barang) ) {
            $sql_brg_kv .= "and dtv.item in ('".implode("', '", $barang)."')";
            $sql_brg_rv .= "and drv.item in ('".implode("', '", $barang)."')";
        }

        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit .= "and data.unit in ('".implode("', '", $unit)."')";
        }

        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan .= "and data.kode_prs in ('".implode("', '", $perusahaan)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.datang,
                data.unit,
                data.noreg,
                data.peternak as nama_peternak,
                data.kode_barang,
                data.barang,
                data.no_order,
                data.no_sj,
                sum(data.jumlah) as jumlah,
                data.hrg_beli as hrg_beli,
                sum(data.tot_beli) as tot_beli,
                data.hrg_jual as hrg_jual,
                sum(data.tot_jual) as tot_jual,
                data.urut,
                data.kode_prs,
                data.nama_prs,
                data.jenis_kirim,
                data.jenis,
                data.posisi,
                data.jenis_peternak
            from
            (
                select
                    kv.tgl_terima as datang,
                    w.kode as unit,
                    kv.peternak as noreg,
                    peternak.nama as peternak,
                    brg.kode as kode_barang,
                    brg.nama as barang,
                    kv.no_order,
                    kv.no_sj,
                    ds.jumlah,
                    ds.hrg_beli,
                    (ds.jumlah * ds.hrg_beli) as tot_beli,
                    ds.hrg_jual,
                    (ds.jumlah * ds.hrg_jual) as tot_jual,
                    CASE
                        WHEN kv.jenis_kirim = 'opks' THEN
                            1
                        WHEN kv.jenis_kirim = 'opkg' THEN
                            2
                        ELSE
                            3
                    END as urut,
                    prs.kode as kode_prs,
                    prs.nama as nama_prs,
                    kv.jenis_kirim,
                    kv.jenis,
                    kv.posisi,
                    peternak.jenis as jenis_peternak
                from 
                    (
                        select
                            kv.no_order,
                            kv.no_sj,
                            kv.asal as peternak,
                            tv.tgl_terima,
                            dtv.item,
                            kv.jenis_kirim,
                            'mutasi' as jenis,
                            'asal' as posisi
                        from det_terima_voadip dtv  
                        right join
                            terima_voadip tv 
                            on
                                dtv.id_header = tv.id
                        left join
                            kirim_voadip kv 
                            on
                                tv.id_kirim_voadip = kv.id
                        where
                            kv.jenis_kirim in ('opkp') and
                            tv.tgl_terima between '".$start_date."' and '".$end_date."'
                            ".$sql_brg_kv."

                        union all

                        select
                            kv.no_order,
                            kv.no_sj,
                            kv.tujuan as peternak,
                            tv.tgl_terima,
                            dtv.item,
                            kv.jenis_kirim,
                            case
                                when kv.jenis_kirim = 'opkp' then
                                    'mutasi'
                                else
                                    'distribusi'
                            end as jenis,
                            'tujuan' as posisi
                        from det_terima_voadip dtv  
                        right join
                            terima_voadip tv 
                            on
                                dtv.id_header = tv.id
                        left join
                            kirim_voadip kv 
                            on
                                tv.id_kirim_voadip = kv.id
                        where
                            kv.jenis_kirim in ('opkg', 'opkp') and
                            tv.tgl_terima between '".$start_date."' and '".$end_date."'
                            ".$sql_brg_kv."
                        
                        union all

                        select
                            rv.no_order,
                            rv.no_retur as no_sj,
                            rv.id_asal as peternak,
                            rv.tgl_retur as tgl_terima,
                            drv.item,
                            kv.jenis_kirim,
                            'retur' as jenis,
                            'asal' as posisi
                        from det_retur_voadip drv
                        right join
                            retur_voadip rv
                            on
                                drv.id_header = rv.id
                        left join
                            kirim_voadip kv 
                            on
                                rv.no_order = kv.no_order
                        where
                            rv.tgl_retur between '".$start_date."' and '".$end_date."'
                            ".$sql_brg_rv."
                    ) kv
                left join
                    (
                        select
                            ds.kode_trans,
                            ds.kode_barang,
                            ds.jumlah,
                            ds.hrg_jual,
                            ds.hrg_beli
                        from det_stok ds
                        group by
                            ds.kode_trans,
                            ds.kode_barang,
                            ds.jumlah,
                            ds.hrg_jual,
                            ds.hrg_beli

                        union all

                        select
                            dst.kode_trans,
                            ds.kode_barang,
                            dst.jumlah,
                            ds.hrg_jual,
                            ds.hrg_beli
                        from det_stok_trans dst
                        left join
                            det_stok ds
                            on
                                dst.id_header = ds.id
                        group by
                            dst.kode_trans,
                            ds.kode_barang,
                            dst.jumlah,
                            ds.hrg_jual,
                            ds.hrg_beli
                    ) ds
                    on
                        ds.kode_trans = kv.no_order and
                        ds.kode_barang = kv.item
                left join
                    (
                        select
                            '' as perusahaan,
                            cast(p1.nomor as varchar(15)) as kode,
                            p1.nama,
                            'pelanggan' as jenis
                        from pelanggan p1
                        right join
                            (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' and mstatus = 1 group by nomor) p2
                            on
                                p1.id = p2.id
                        
                        union all

                        select
                            mtr.perusahaan as perusahaan,
                            cast(rs.noreg as varchar(15)) as kode,
                            mtr.nama+' (KDG : '+SUBSTRING(rs.noreg, 10, 2)+')' as nama,
                            'peternak' as jenis
                        from rdim_submit rs
                        left join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                rs.nim = mm.nim
                        left join
                            mitra mtr
                            on
                                mtr.id = mm.mitra

                        union all

                        select
                            gdg.perusahaan as perusahaan,
                            cast(gdg.id as varchar(15)) as kode,
                            gdg.nama,
                            'gudang' as jenis
                        from gudang gdg
                    ) peternak
                    on
                        peternak.kode = kv.peternak
                left join
                    (
                        select b1.* from barang b1
                        right join
                            (select max(id) as id, kode from barang b group by kode) b2
                            on
                                b1.id = b2.id
                    ) brg
                    on
                        brg.kode = kv.item
                left join
                    (
                        select p.id, p.kode, p.perusahaan as nama from perusahaan p
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) _p
                            on
                                _p.id = p.id
                    ) prs
                    on
                        prs.kode = peternak.perusahaan 
                        -- or prs.kode = tujuan.perusahaan
                left join
                    wilayah w
                    on
                        kv.no_order like '%'+w.kode+'%'
            ) data
            where
                data.jenis_kirim <> 'opks' and
                data.datang is not null
                -- data.datang between '".$start_date."' and '".$end_date."'
                ".$sql_perusahaan."
                ".$sql_unit."
            group by
                data.datang,
                data.unit,
                data.noreg,
                data.peternak,
                data.kode_barang,
                data.barang,
                data.no_order,
                data.no_sj,
                data.hrg_beli,
                data.hrg_jual,
                data.urut,
                data.kode_prs,
                data.nama_prs,
                data.jenis_kirim,
                data.jenis,
                data.posisi,
                data.jenis_peternak
            order by
                data.datang asc,
                data.urut,
                data.barang asc,
                data.no_order asc
        ";
        $d_ovk = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_ovk->count() ) {
            $d_ovk = $d_ovk->toArray();

            foreach ($d_ovk as $key => $value) {
                $data[ $key ] = $value;

                $jumlah = ($value['posisi'] == 'asal') ? 0-$value['jumlah'] : $value['jumlah'];
                $total = $value['hrg_beli'] * $jumlah;
                $total_jual = $value['hrg_jual'] * $jumlah;

                $data[ $key ] = $value;
                $data[ $key ]['jumlah'] = $jumlah;
                $data[ $key ]['tot_beli'] = $total;
                $data[ $key ]['tot_jual'] = $total_jual;
            }
        }

        return $data;
    }

    public function getLists()
    {
        $params = $this->input->post('params');

        try {
            $jenis = $params['jenis'];
            $barang = $params['barang'];
            $start_date = $params['start_date'].' 00:00:00.000';
            $end_date = $params['end_date'].' 23:59:59.999';
            $unit = $params['unit'];
            $perusahaan = $params['perusahaan'];

            $data = null;
            if ( stristr($jenis, 'pakan') !== FALSE ) {
                $data = $this->getDataPakan( $start_date, $end_date, $barang, $unit, $perusahaan );
            } else if ( stristr($jenis, 'voadip') !== FALSE ) {
                $data = $this->getDataVoadip( $start_date, $end_date, $barang, $unit, $perusahaan );
            }

            $content['data'] = $data;

            $html = $this->load->view($this->pathView.'list', $content, TRUE);

            $this->result['status'] = 1;
            $this->result['html'] = $html;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
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

    public function exportExcel($params_encrypt)
    {
        $params = json_decode( exDecrypt($params_encrypt), true );

        $jenis = $params['jenis'];
        $_jenis = (stristr($jenis, 'pakan') !== FALSE) ? $params['jenis'] : 'obat';
        $barang = $params['barang'];
        $start_date = $params['start_date'].' 00:00:00.000';
        $end_date = $params['end_date'].' 23:59:59.999';
        $unit = $params['unit'];
        $perusahaan = $params['perusahaan'];

        $data = null;
        if ( stristr($jenis, 'pakan') !== FALSE ) {
            $data = $this->getDataPakan( $start_date, $end_date, $barang, $unit, $perusahaan );
        } else if ( stristr($jenis, 'voadip') !== FALSE ) {
            $data = $this->getDataVoadip( $start_date, $end_date, $barang, $unit, $perusahaan );
        }
            
        $filename = 'DISTRIBUSI_'.strtoupper($jenis).'_'.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']).'.xls';

        $arr_header = array('Transaksi', 'Tanggal', 'Unit', 'Peternak', 'Barang', 'No. SJ', 'Jumlah', 'OA', 'OA Mutasi', 'Hrg Beli', 'Total Beli', 'Hrg Jual', 'Total Jual');
        $arr_column = null;
        if ( !empty($data) ) {
            $idx = 0;
            foreach ($data as $key => $value) {
                $arr_column[ $idx ] = array(
                    'Transaksi' => array('value' => strtoupper($value['jenis']), 'data_type' => 'string'),
                    'Tanggal' => array('value' => $value['datang'], 'data_type' => 'date'),
                    'Unit' => array('value' => $value['unit'], 'data_type' => 'string'),
                    'Peternak' => array('value' => strtoupper($value['nama_peternak']), 'data_type' => 'string'),
                    'Barang' => array('value' => strtoupper($value['barang']), 'data_type' => 'string'),
                    'No. SJ' => array('value' => strtoupper($value['no_sj']), 'data_type' => 'string'),
                    'Jumlah' => array('value' => $value['jumlah'], 'data_type' => 'decimal2'),
                    'OA' => array('value' => isset($value['oa']) ? $value['oa'] : 0, 'data_type' => 'decimal2'),
                    'OA Mutasi' => array('value' => isset($value['oa_mutasi']) ? $value['oa_mutasi'] : 0, 'data_type' => 'decimal2'),
                    'Hrg Beli' => array('value' => $value['hrg_beli'], 'data_type' => 'decimal2'),
                    'Total Beli' => array('value' => $value['tot_beli'], 'data_type' => 'decimal2'),
                    'Hrg Jual' => array('value' => $value['hrg_jual'], 'data_type' => 'decimal2'),
                    'Total Jual' => array('value' => $value['tot_jual'], 'data_type' => 'decimal2'),
                );

                $idx++;
            }
        }

        $this->exportExcelUsingSpreadSheet( $filename, $arr_header, $arr_column );
    }
}