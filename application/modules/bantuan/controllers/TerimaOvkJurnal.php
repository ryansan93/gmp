<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TerimaOvkJurnal extends Public_Controller {

    private $pathView = 'bantuan/terima_ovk_jurnal/';
    private $url;
    private $hakAkses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->hakAkses = hakAkses($this->url);
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index($segment=0)
    {
        // if ( $this->hakAkses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/jquery/easy-autocomplete/jquery.easy-autocomplete.min.js",
                "assets/select2/js/select2.min.js",
                "assets/bantuan/terima_ovk_jurnal/js/terima-ovk-jurnal.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/bantuan/terima_ovk_jurnal/css/terima-ovk-jurnal.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->hakAkses;
            $content['title_panel'] = 'Terima OVK dan Jurnal';

            // Load Indexx
            $data['title_menu'] = 'Terima OVK dan Jurnal';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);
            $this->load->view($this->template, $data);
        // } else {
        //     showErrorAkses();
        // }
    }

    public function getLists()
    {
        $params         = $this->input->get('params');

        $bulan          = $params['bulan'];
        $tahun          = substr($params['tahun'], 0, 4);
        
        $angka_bulan    = (strlen($bulan) == 1) ? '0'.$bulan : $bulan;
        
        $date           = $tahun.'-'.$angka_bulan.'-01';
        $start_date     = date("Y-m-d", strtotime($date));
        $end_date       = date("Y-m-t", strtotime($date));

        $data           = $this->getData( $start_date, $end_date );

        $content['data']    = $data;
        $html               = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }

    public function getData( $start_date, $end_date ) {
        $m_conf = new \Model\Storage\Conf();
        $sql = " select 
                dtp.tbl_id,
                dtp.tgl_trans as tanggal,
                dtp.kode_trans,
                dtp.tbl_name,
                dtp.jml_terima,
                ds.jumlah as jml_stok,
                isnull(sum(ds.jumlah), 0) - isnull(dtp.jml_terima, 0) as selisih,
                ds.total as total_stok,
                sum(dj.nominal) as nominal_jurnal,
                isnull(ds.total, 0) - isnull(sum(dj.nominal), 0) as selisih_jurnal
            from 
            (
                select 
                    tv.id as tbl_id, 
                    sum(dtv.jumlah) as jml_terima, 
                    kv.no_order as kode_trans,
                    tv.tgl_terima as tgl_trans, 
                    'terima_voadip' as tbl_name
                from det_terima_voadip dtv
                left join
                    terima_voadip tv
                    on
                        dtv.id_header = tv.id
                left join
                    kirim_voadip kv
                    on
                        tv.id_kirim_voadip = kv.id
                where
                    tv.tgl_terima between '".$start_date."' and '".$end_date."'
                group by
                    tv.id, 
                    kv.no_order, 
                    tv.tgl_terima

                union all

                select
                    rv.id as tbl_id, 
                    sum(drv.jumlah) as jml_terima, 
                    rv.no_retur as kode_trans,
                    rv.tgl_retur as tgl_trans, 
                    'retur_voadip' as tbl_name
                from det_retur_voadip drv 
                left join
                    retur_voadip rv 
                    on
                        drv.id_header = rv.id
                where
                    rv.tgl_retur between '".$start_date."' and '".$end_date."'
                group by
                    rv.id, 
                    rv.no_order,
                    rv.tgl_retur,
                    rv.no_retur
            ) dtp
            left join
                (
                    select 
                        dss.tgl_trans, 
                        dss.kode_trans, 
                        sum(dss.jumlah) as jumlah,
                        sum(dss.jumlah * dss.hrg_beli) as total,
                        'terima_voadip' as tbl_name 
                    from det_stok_siklus dss
                    where 
                        dss.jenis_barang = 'voadip' and
                        dss.tgl_trans between '".$start_date."' and '".$end_date."'
                    group by
                        dss.tgl_trans, 
                        dss.kode_trans
                        
                    union all
                    
                    select
                        dsts.tgl_trans, 
                        dsts.kode_trans, 
                        sum(dsts.jumlah) as jumlah,
                        sum(dsts.jumlah * dss.hrg_beli) as total,
                        'retur_voadip' as tbl_name 
                    from det_stok_trans_siklus dsts 
                    left join
                        det_stok_siklus dss
                        on
                            dsts.id_header = dss.id
                    where
                        dsts.kode_trans like '%RTV%' and
                        dss.jenis_barang = 'voadip' and
                        dsts.tgl_trans between '".$start_date."' and '".$end_date."'
                    group by
                        dsts.tgl_trans, 
                        dsts.kode_trans

                    union all

                    select 
                        ds.tgl_trans, 
                        ds.kode_trans, 
                        sum(ds.jumlah) as jumlah,
                        sum(ds.jumlah * ds.hrg_beli) as total,
                        'terima_voadip' as tbl_name
                    from det_stok ds 
                    left join
                        stok s
                        on
                            ds.id_header = s.id
                    where 
                        ds.jenis_barang = 'voadip' and
                        s.periode between '".$start_date."' and '".$end_date."' and
                        ds.tgl_trans = s.periode and
                        ds.jenis_trans = 'ORDER'
                    group by
                        ds.tgl_trans, 
                        ds.kode_trans,
                        ds.jenis_trans
                ) ds
                on
                    ds.kode_trans = dtp.kode_trans and
                    ds.tbl_name = dtp.tbl_name
            left join
                (
                    select tbl_name, tbl_id, sum(nominal) as nominal from det_jurnal dj where tanggal between '".$start_date."' and '".$end_date."' and coa_tujuan in ('12050.000', '12051.000') group by tbl_name, tbl_id
                ) dj
                on
                    dj.tbl_name = ds.tbl_name and
                    dj.tbl_id = cast(dtp.tbl_id as varchar(50))
            where
                dtp.tgl_trans between '".$start_date."' and '".$end_date."'
            group by
                dtp.tbl_id,
                dtp.tgl_trans,
                dtp.kode_trans,
                dtp.tbl_name,
                dtp.jml_terima,
                ds.jumlah,
                ds.total
                , dj.nominal
            having
                isnull(ds.jumlah, 0) - isnull(dtp.jml_terima, 0) <> 0 or
                isnull(ds.total, 0) - isnull(sum(dj.nominal), 0) <> 0
            order by
                dtp.tgl_trans asc,
                dtp.kode_trans asc
        ";

        // echo "<pre>";
        // print_r($sql);
        // die;
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function tes() {
    }
}