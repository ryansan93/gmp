<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TerimaDocJurnal extends Public_Controller {

    private $pathView = 'bantuan/terima_doc_jurnal/';
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
                "assets/bantuan/terima_doc_jurnal/js/terima-doc-jurnal.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/bantuan/terima_doc_jurnal/css/terima-doc-jurnal.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->hakAkses;
            $content['title_panel'] = 'Terima Doc dan Jurnal';

            // Load Indexx
            $data['title_menu'] = 'Terima Doc dan Jurnal';
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
        
        $angka_bulan = (strlen($bulan) == 1) ? '0'.$bulan : $bulan;
        
        $date = $tahun.'-'.$angka_bulan.'-01';
        $start_date = date("Y-m-d", strtotime($date));
        $end_date = date("Y-m-t", strtotime($date));

        $data = $this->getData( $start_date, $end_date );

        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }

    public function getData( $start_date, $end_date ) {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                td.*,
                cast(td.datang as date) as tanggal,
                td.no_order as kode_trans,
                od.noreg,
                m.nama as nama_mitra,
                td.jml_ekor as jml_terima,
                dss.jumlah as jml_stok,
                isnull(td.jml_ekor, 0) - isnull(dss.jumlah, 0) as selisih,
                td.total as total_stok,
                dj.nominal as nominal_jurnal,
                isnull(dj.nominal, 0) - isnull(td.total, 0) as selisih_jurnal
            from terima_doc td
            left join
                (
                    select od1.* from order_doc od1
                    right join
                        (select max(id) as id, no_order from order_doc group by no_order) od2
                        on
                            od1.id = od2.id
                ) od
                on
                    td.no_order = od.no_order
            left join
                rdim_submit rs
                on
                    od.noreg = rs.noreg
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
                    mm.mitra = m.id
            left join
                (select kode_trans, sum(jumlah) as jumlah from det_stok_siklus where jenis_barang = 'doc' group by kode_trans) dss
                on
                    dss.kode_trans = td.no_order
            left join
                (select tbl_id, tbl_name, sum(nominal) as nominal from det_jurnal dj where coa_asal = '21180.200' and tbl_name = 'terima_doc' group by tbl_id, tbl_name) dj
                on
                    cast(td.id as varchar(max)) = dj.tbl_id
            where
                cast(td.datang as date) between '".$start_date."' and '".$end_date."' and
                (isnull(dss.jumlah, 0) - isnull(td.jml_ekor, 0) <> 0 or isnull(dj.nominal, 0) - isnull(td.total, 0) <> 0)
            order by
                cast(td.datang as date) asc,
                m.nama asc
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