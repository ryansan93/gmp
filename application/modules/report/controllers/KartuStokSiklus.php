<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KartuStokSiklus extends Public_Controller {

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
                "assets/report/kartu_stok_siklus/js/kartu-stok-siklus.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/kartu_stok_siklus/css/kartu-stok-siklus.css",
            ));

            $data = $this->includes;

            $m_wil = new \Model\Storage\Wilayah_model();

            $content['akses'] = $akses;
            $content['unit'] = $m_wil->getDataUnit();
            $content['barang'] = $this->getBarang();
            $content['title_menu'] = 'Laporan Kartu Stok Siklus';

            // Load Indexx
            $data['view'] = $this->load->view('report/kartu_stok_siklus/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getBarang() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                brg1.* 
            from barang brg1
            right join
                (select max(id) as id, kode from barang group by kode) brg2
                on
                    brg1.id = brg2.id
            where
                brg1.tipe in ('pakan', 'obat')
            order by
                brg1.tipe asc,
                brg1.nama asc
        ";
        $d_brg = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_brg->count() > 0 ) {
            $data = $d_brg->toArray();
        }

        return $data;
    }

    public function getPlasma() {
        $search = $this->input->get('search');
        $type = $this->input->get('type');
        $unit = $this->input->get('unit');
        $tutup_siklus = $this->input->get('tutup_siklus');

        $sql_condition = null;
        if ( $unit != 'all' ) {
            if ( !empty($sql_condition) ) {
                $sql_condition .= " and w.kode = '".$unit."'";
            } else {
                $sql_condition = "where w.kode = '".$unit."'";
            }
        }

        if ( $tutup_siklus != 'all' ) {
            $tutup_siklus = ($tutup_siklus == 1) ? 'is null' : 'is not null';
            if ( !empty($sql_condition) ) {
                $sql_condition .= " and ts.id ".$tutup_siklus."";
            } else {
                $sql_condition = "where ts.id ".$tutup_siklus."";
            }
        }

        $sql_search = "";
        if ( !empty($search) && !empty($type) ) {
            $sql_search = "where data.text like '%".$search."%'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from
            (
                select
                    rs.noreg as id,
                    UPPER(w.kode+' | '+rs.noreg+' | '+m.nama+' (KDG:'+cast(k.kandang as varchar(2))+')') as text,
                    rs.noreg,
                    mm.nim,
                    m.nama,
                    k.kandang,
                    w.kode as unit
                from rdim_submit rs
                left join
                    tutup_siklus ts
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
                        mm.mitra = m.id
                left join
                    kandang k
                    on
                        rs.kandang = k.id
                left join
                    wilayah w
                    on
                        w.id = k.unit
                ".$sql_condition."
            ) data
            ".$sql_search."
            order by
                data.unit asc,
                data.nama asc,
                data.kandang asc
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }
        
        echo json_encode($data);
    }

    public function mappingDataReport($_unit = null, $tutup_siklus = null, $_noreg = null, $_kode_brg = null, $_jenis_brg = null, $_start_date, $_end_date)
    {
        $sql_condition = "where data.jenis_barang <> 'doc'";
        if ( $_unit != 'all' ) {
            if ( !empty($sql_condition) ) {
                $sql_condition .= " and rs.unit = '".$_unit."'";
            } else {
                $sql_condition = "where rs.unit = '".$_unit."'";
            }
        }

        if ( $tutup_siklus != 'all' ) {
            $tutup_siklus = ($tutup_siklus == 1) ? 'is null' : 'is not null';
            if ( !empty($sql_condition) ) {
                $sql_condition .= " and rs.id_ts ".$tutup_siklus."";
            } else {
                $sql_condition = "where rs.id_ts ".$tutup_siklus."";
            }
        }

        if ( $_noreg != 'all' && !empty($_noreg) ) {
            if ( !empty($sql_condition) ) {
                $sql_condition .= " and data.noreg = '".$_noreg."'";
            } else {
                $sql_condition = "where data.noreg = '".$_noreg."'";
            }
        }

        if ( $_kode_brg != 'all' ) {
            if ( !empty($sql_condition) ) {
                $sql_condition .= " and data.kode_barang = '".$_kode_brg."'";
            } else {
                $sql_condition = "where data.kode_barang = '".$_kode_brg."'";
            }
        }

        if ( $_jenis_brg != 'all' ) {
            if ( !empty($sql_condition) ) {
                $sql_condition .= " and data.jenis_barang = '".$_jenis_brg."'";
            } else {
                $sql_condition = "where data.jenis_barang = '".$_jenis_brg."'";
            }
        }

        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.*,
                brg.nama as nama_barang,
                rs.nama as nama_plasma,
                rs.kandang,
                rs.unit,
                case
                    when rs.id_ts is not null then
                        'SUDAH TUTUP SIKLUS'
                    else
                        'BELUM TUTUP SIKLUS'
                end as status
            from
            (
                /* SALDO AWAL */
                select
                    data.noreg,
                    '' as tanggal,
                    '' as jenis_trans,
                    'Saldo Awal' as kode_trans,
                    data.kode_barang,
                    data.jenis_barang,
                    0 as hrg_beli,
                    -- data.hrg_beli,
                    sum(isnull(data.jml_debet, 0)-isnull(data.jml_kredit, 0)) as jml_debet,
                    sum(isnull(data.debet, 0) - isnull(data.kredit, 0)) as debet,
                    0 as jml_kredit,
                    0 as kredit,
                    1 as urut
                from
                (
                    select
                        dss.noreg,
                        dss.tgl_trans as tanggal,
                        case
                            when dss.jenis_trans like '%order%' then
                                'DISTRIBUSI'
                            else
                                dss.jenis_trans
                        end as jenis_trans,
                        dss.kode_trans,
                        dss.kode_barang,
                        dss.jenis_barang,
                        dss.jumlah,
                        -- dss.hrg_beli,
                        (dss.jumlah * dss.hrg_beli) as nilai,
                        dss.jumlah as jml_debet,
                        (dss.jumlah * dss.hrg_beli) as debet,
                        0 as jml_kredit,
                        0 as kredit
                    from det_stok_siklus dss 
                    
                    union all
                    
                    select
                        dss.noreg,
                        dsts.tgl_trans as tanggal,
                        case
                            when dsts.tbl_name like '%terima%' then
                                'MUTASI'
                            when dsts.tbl_name like '%retur%' then
                                'RETUR'
                            else
                                dss.jenis_trans
                        end as jenis_trans,
                        case
                            when dsts.tbl_name = 'lhk' then
                                'LHK UMUR '+cast(l.umur as varchar(5))
                            else
                                dsts.kode_trans
                        end as kode_trans,
                        dsts.kode_barang,
                        dss.jenis_barang,
                        dsts.jumlah,
                        -- dss.hrg_beli,
                        (dsts.jumlah * dss.hrg_beli) as nilai,
                        0 as jml_debet,
                        0 as debet,
                        dsts.jumlah as jml_kredit,
                        (dsts.jumlah * dss.hrg_beli) as kredit
                    from det_stok_trans_siklus dsts
                    left join
                        det_stok_siklus dss 
                        on
                            dsts.id_header = dss.id
                    left join
                        lhk l
                        on
                            cast(l.id as varchar(20)) = dsts.kode_trans
                ) data
                where
                    data.noreg is not null and
                    data.tanggal < '".$_start_date."'
                group by
                    data.noreg,
                    data.kode_barang,
                    data.jenis_barang
                    -- ,data.hrg_beli
                having
                    sum(isnull(data.jml_debet, 0)-isnull(data.jml_kredit, 0)) <> 0 and
                    sum(isnull(data.debet, 0) - isnull(data.kredit, 0)) <> 0
                /* END - SALDO AWAL */

                union all

                /* MASUK */
                select
                    dss.noreg,
                    dss.tgl_trans as tanggal,
                    case
                        when dss.jenis_trans like '%order%' then
                            'DISTRIBUSI'
                        else
                            dss.jenis_trans
                    end as jenis_trans,
                    dss.kode_trans,
                    dss.kode_barang,
                    dss.jenis_barang,
                    dss.hrg_beli,
                    dss.jumlah as jml_debet,
                    (dss.jumlah * dss.hrg_beli) as debet,
                    0 as jml_kredit,
                    0 as kredit,
                    2 as urut
                from det_stok_siklus dss
                where
                    dss.tgl_trans between '".$_start_date."' and '".$_end_date."'
                /* END - MASUK */

                union all

                /* KELUAR */
                select
                    dss.noreg,
                    dsts.tgl_trans as tanggal,
                    case
                        when dsts.tbl_name like '%terima%' then
                            'MUTASI'
                        when dsts.tbl_name like '%retur%' then
                            'RETUR'
                        else
                            -- dss.jenis_trans
                            'PEMAKAIAN'
                    end as jenis_trans,
                    case
                        when dsts.tbl_name = 'lhk' then
                            'LHK UMUR '+cast(l.umur as varchar(5))
                        else
                            dsts.kode_trans
                    end as kode_trans,
                    dsts.kode_barang,
                    dss.jenis_barang,
                    dss.hrg_beli,
                    0 as jml_debet,
                    0 as debet,
                    dsts.jumlah as jml_kredit,
                    (dsts.jumlah * dss.hrg_beli) as kredit,
                    2 as urut
                from det_stok_trans_siklus dsts
                left join
                    det_stok_siklus dss 
                    on
                        dsts.id_header = dss.id
                left join
                    lhk l
                    on
                        cast(l.id as varchar(20)) = dsts.kode_trans
                where
                    dsts.tgl_trans between '".$_start_date."' and '".$_end_date."'
                /* END - KELUAR */
            ) data
            left join
                (
                    select brg1.* from barang brg1
                    right join
                        (
                            select max(id) as id, kode from barang group by kode
                        ) brg2
                        on
                            brg1.id = brg2.id
                ) brg
                on
                    data.kode_barang = brg.kode
            left join
                (
                    select * from
                    (
                        select
                            rs.noreg,
                            mm.nim,
                            m.nama,
                            k.kandang,
                            w.kode as unit,
                            ts.id as id_ts
                        from rdim_submit rs
                        left join
                            tutup_siklus ts
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
                                mm.mitra = m.id
                        left join
                            kandang k
                            on
                                rs.kandang = k.id
                        left join
                            wilayah w
                            on
                                w.id = k.unit
                    ) data
                ) rs
                on
                    data.noreg = rs.noreg
            ".$sql_condition."
            order by
                data.noreg asc,
                data.jenis_barang asc,
                brg.nama asc,
                data.tanggal asc,
                data.urut asc
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getData()
    {
        $params = $this->input->get('params');

        // cetak_r( $params, 1 );

        $bulan = $params['bulan'];
        $tahun = substr($params['tahun'], 0, 4);
        $unit = $params['unit'];
        $tutup_siklus = $params['tutup_siklus'];
        $noreg = $params['plasma'];
        $kode_barang = $params['barang'];
        $jenis_barang = ($params['jenis_barang'] == 'OBAT') ? 'VOADIP' : $params['jenis_barang'];

        if ( $bulan != 'all' ) {
            $i = $bulan;

            $angka_bulan = (strlen($i) == 1) ? '0'.$i : $i;

            $date = $tahun.'-'.$angka_bulan.'-01';
            $start_date = date("Y-m-d", strtotime($date));
            $end_date = date("Y-m-t", strtotime($date));
        } else {
            $i = 1;
            $angka_bulan = (strlen($i) == 1) ? '0'.$i : $i;
            $_start_date = $tahun.'-'.$angka_bulan.'-01';
            $start_date = date("Y-m-d", strtotime($_start_date));

            $i = 12;
            $angka_bulan = (strlen($i) == 1) ? '0'.$i : $i;
            $_end_date = $tahun.'-'.$angka_bulan.'-01';
            $end_date = date("Y-m-t", strtotime($_end_date));
        }

        $data = $this->mappingDataReport($unit, $tutup_siklus, $noreg, $kode_barang, $jenis_barang, $start_date, $end_date);

        // cetak_r( $data, 1 );

        $content['data'] = $data;
        $html = $this->load->view('report/kartu_stok_siklus/list', $content, TRUE);

        echo $html;
    }
}