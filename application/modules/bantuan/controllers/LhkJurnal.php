<?php defined('BASEPATH') OR exit('No direct script access allowed');

class LhkJurnal extends Public_Controller {

    private $pathView = 'bantuan/lhk_jurnal/';
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
                "assets/bantuan/lhk_jurnal/js/lhk-jurnal.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/bantuan/lhk_jurnal/css/lhk-jurnal.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->hakAkses;
            $content['title_panel'] = 'LHK dan Jurnal';

            // Load Indexx
            $data['title_menu'] = 'LHK dan Jurnal';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);
            $this->load->view($this->template, $data);
        // } else {
        //     showErrorAkses();
        // }
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $bulan = $params['bulan'];
        $tahun = substr($params['tahun'], 0, 4);
        $unit = $params['unit'];
        
        $angka_bulan = (strlen($bulan) == 1) ? '0'.$bulan : $bulan;
        
        $date = $tahun.'-'.$angka_bulan.'-01';
        $start_date = date("Y-m-d", strtotime($date));
        $end_date = date("Y-m-t", strtotime($date));

        $data = $this->getData( $start_date, $end_date, $unit );

        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }

    public function getData( $start_date, $end_date, $unit ) {
        $sql_unit = "";
        if ( stristr('all', $unit) === FALSE ) {
            $sql_unit = "where w.kode = '".$unit."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.*,
                m.nama as nama_mitra,
                w.kode as unit
            from (
                select 
                    l.tanggal as tanggal,
                    l.noreg,
                    l.id as kode_trans,
                    l.umur,
                    isnull(sum(dsts.jumlah), 0) as jml_stok,
                    (l.selisih * 50) as jml_lhk,
                    isnull(sum(dsts.jumlah), 0) - (l.selisih * 50) as selisih,
                    isnull(sum(dsts.total), 0) as total_stok,
                    isnull(sum(dj.nominal), 0) as nominal_jurnal,
                    isnull(sum(dsts.total), 0) - isnull(sum(dj.nominal), 0) as selisih_jurnal
                from 
                (
                        select l.noreg, l.tanggal, isnull(max(l_prev.umur), 0) as umur_prev, l.id, l.umur, l.pakai_pakan, isnull(max(l_prev.pakai_pakan), 0) as pp_prev, l.pakai_pakan-isnull(max(l_prev.pakai_pakan), 0) as selisih from lhk l
                        left join
                            (select * from lhk) l_prev
                            on
                                l_prev.noreg = l.noreg and
                                l_prev.umur < l.umur
                        group by
                            l.noreg, l.tanggal, l.id, l.umur, l.pakai_pakan
                    ) l
                left join
                    (
                        select
                            dsts.kode_trans,
                            sum(dsts.jumlah) as jumlah,
                            sum(dsts.jumlah * dss.hrg_beli) as total
                        from det_stok_trans_siklus dsts
                        left join
                            det_stok_siklus dss
                            on
                                dss.id = dsts.id_header
                        where
                            dsts.tbl_name = 'lhk' and
                            dss.jenis_barang = 'pakan'
                        group by
                            dsts.kode_trans
                    ) dsts 
                    on
                        dsts.kode_trans = l.id
                left join
                    (select tbl_id, sum(nominal) as nominal from det_jurnal where tbl_name = 'lhk' and coa_tujuan = '71101.000' group by tbl_id) dj
                    on
                        dj.tbl_id = l.id
                where
                    l.tanggal between '".$start_date."' and '".$end_date."'
                group by
                    l.tanggal,
                    l.noreg,
                    l.id,
                    l.selisih,
                    l.umur,
                    dj.nominal
                having
                    isnull(sum(dsts.jumlah), 0) - (isnull(l.selisih, 0) * 50) <> 0 or
                    isnull(sum(dsts.total), 0) - isnull(sum(dj.nominal), 0) <> 0
            ) data
            left join
                rdim_submit rs
                on
                    data.noreg = rs.noreg
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
                mitra m
                on
                    m.id = mm.mitra
            left join
                kandang k
                on
                    rs.kandang = k.id
            left join
                wilayah w
                on
                    w.id = k.unit
            ".$sql_unit."
            order by
                data.tanggal asc,
                data.noreg asc
        ";
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