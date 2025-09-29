<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class RealisasiChickIn extends Public_Controller {

    private $pathView = 'report/realisasi_chick_in/';
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
                "assets/report/realisasi_chick_in/js/realisasi-chick-in.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/realisasi_chick_in/css/realisasi-chick-in.css",
            ));

            $data = $this->includes;

            $m_wilayah = new \Model\Storage\Wilayah_model();

            $content['unit'] = $m_wilayah->getDataUnit(1, $this->userid);
            $content['akses'] = $akses;
            $content['title_menu'] = 'Laporan Realisasi Chick In';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getLists() {
        $params = $this->input->get('params');

        // cetak_r( $params, 1 );

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $unit = $params['unit'];

        $sql_unit = null;
        if ( !in_array('all', $unit) ) {
            $sql_unit = "and data.kode_unit in ('".implode("', '", $unit)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                m.nama as nama_plasma,
                k.kandang,
                w.kode as kode_unit,
                rs.noreg,
                rs.tgl_docin as rcn_tgl_docin,
                rs.populasi as rcn_jml_ekor,
                (rs.populasi/100) as rcn_jml_box,
                td.datang as real_tgl_docin,
                td.jml_ekor as real_jml_ekor,
                td.jml_box as real_jml_box,
                td.bb,
                td.harga,
                k.alamat_jalan+' RT.'+cast(k.alamat_rt as varchar(3))+'/RW.'+cast(k.alamat_rw as varchar(3))+', '+k.alamat_kelurahan+', '+k.kecamatan+', '+k.kab_kota+', '+k.provinsi as alamat
            from rdim_submit rs
            left join
                (
                    select k.*, l_kec.nama as kecamatan, l_kab_kota.nama as kab_kota, l_prov.nama as provinsi from kandang k 
                    left join
                        lokasi l_kec
                        on
                            k.alamat_kecamatan = l_kec.id
                    left join
                        lokasi l_kab_kota
                        on
                            l_kec.induk = l_kab_kota.id
                    left join
                        lokasi l_prov
                        on
                            l_kab_kota.induk = l_prov.id
                ) k
                on
                    rs.kandang = k.id
            left join
                wilayah w
                on
                    k.unit = w.id
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
                (
                    select
                        od.noreg,
                        td.datang,
                        sum(td.jml_ekor) as jml_ekor,
                        sum(td.jml_box) as jml_box,
                        td.bb,
                        td.harga
                    from
                    (
                        select td1.* from terima_doc td1
                        right join
                            (select max(version) as version, no_order from terima_doc group by no_order) td2
                            on
                                td1.version = td2.version and
                                td1.no_order = td2.no_order
                    ) td
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
                    group by
                        od.noreg,
                        td.datang,
                        td.bb,
                        td.harga
                ) td
                on
                    rs.noreg = td.noreg
            where
                rs.tgl_docin between '".$start_date."' and '".$end_date."'
            order by
                w.kode asc,
                rs.tgl_docin asc,
                m.nama asc
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $content['data'] = $data;

        $html = $this->load->view($this->pathView.'list', $content);

        echo $html;
    }
}