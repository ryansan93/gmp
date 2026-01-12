<?php defined('BASEPATH') OR exit('No direct script access allowed');

class LaporanRhpp extends Public_Controller {

    private $url;
    private $pathView = 'report/laporan_rhpp/';

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
                "assets/report/laporan_rhpp/js/laporan-rhpp.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/laporan_rhpp/css/laporan-rhpp.css",
            ));

            $data = $this->includes;

            $m_wilayah = new \Model\Storage\Wilayah_model();

            $content['akses'] = $akses;
            $content['title_menu'] = 'Laporan RHPP';
            $content['unit'] = $m_wilayah->getDataUnit();

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getData($jenis_rhpp, $unit, $start_date, $end_date)
    {
        $sql_jenis_rhpp = null;
        if ( stristr('all', $jenis_rhpp) === FALSE ) {
            $sql_jenis_rhpp = "and data.jenis_rhpp = '".$jenis_rhpp."'";
        }

        $sql_unit = null;
        if ( stristr('all', $unit) === FALSE ) {
            $sql_unit = "and data.unit = '".$unit."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.*
            from
            (
                select
                    ts.tgl_tutup,
                    cast(r.kandang as varchar(10)) as kandang,
                    r.mitra,
                    r.tgl_docin,
                    r.populasi,
                    r.rata_umur,
                    r.deplesi,
                    r.fcr,
                    r.bb,
                    r.ip,
                    r_plasma.pdpt_peternak_belum_pajak as lr_plasma,
                    r_plasma.pdpt_peternak_belum_pajak / r.populasi as lr_plasma_per_ekor,
                    r.lr_inti as lr_inti,
                    r.lr_inti / r.populasi as lr_inti_per_ekor,
                    case
                        when r.lr_inti < 0 then
                            0
                        else
                            1
                    end as jenis_rhpp,
                    w.kode as unit
                from rhpp r
                left join
                    (select * from rhpp where jenis = 'rhpp_plasma') r_plasma
                    on
                        r.id_ts = r_plasma.id_ts
                left join
                    tutup_siklus ts 
                    on
                        r.id_ts = ts.id
                left join
                    rdim_submit rs 
                    on
                        rs.noreg = r.noreg
                left join
                    kandang k 
                    on
                        rs.kandang = k.id
                left join
                    wilayah w 
                    on
                        w.id = k.unit 
                where
                    r.jenis = 'rhpp_inti' and
                    not exists (select * from rhpp_group_noreg where noreg = r.noreg)

                union all

                select
                    rgh.tgl_submit as tgl_tutup,
                    rgn.kandang as kandang,
                    rgh.mitra,
                    rgn.tgl_docin,
                    rgn.populasi,
                    rg.rata_umur,
                    rg.deplesi,
                    rg.fcr,
                    rg.bb,
                    rg.ip,
                    r_plasma.pdpt_peternak_belum_pajak as lr_plasma,
                    r_plasma.pdpt_peternak_belum_pajak / rgn.populasi as lr_plasma_per_ekor,
                    rg.lr_inti as lr_inti,
                    rg.lr_inti / rgn.populasi as lr_inti_per_ekor,
                    case
                        when rg.lr_inti < 0 then
                            0
                        else
                            1
                    end as jenis_rhpp,
                    rgn.kode as unit
                from rhpp_group rg 
                left join
                    (select * from rhpp_group where jenis = 'rhpp_plasma') r_plasma
                    on
                        rg.id_header = r_plasma.id_header
                left join
                    rhpp_group_header rgh 
                    on
                        rg.id_header = rgh.id
                left join
                    (
                        select DISTINCT 
                            _rgn.id_header, 
                            min(_rgn.tgl_docin) as tgl_docin,
                            sum(_rgn.populasi) as populasi,
                            w.kode,
                            kandang = substring ((
                                select ', '+cast(rgn.kandang as varchar(max)) from rhpp_group_noreg rgn 
                                where
                                    rgn.id_header = _rgn.id_header
                                order by
                                    rgn.kandang
                                FOR XML path('')
                            , elements), 3, 500) 
                        from rhpp_group_noreg _rgn
                        left join
                            rdim_submit rs 
                            on
                                rs.noreg = _rgn.noreg
                        left join
                            kandang k 
                            on
                                rs.kandang = k.id
                        left join
                            wilayah w 
                            on
                                w.id = k.unit 
                        group by
                            _rgn.id_header, 
                            w.kode
                    ) rgn
                    on
                        rgn.id_header = rg.id
                where
                    rg.jenis = 'rhpp_inti'
            ) data
            where
                data.tgl_tutup between '".$start_date."' and '".$end_date."'
                ".$sql_jenis_rhpp."
                ".$sql_unit."
            order by
                data.tgl_docin asc,
                data.mitra asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getLists()
    {
        $params = $this->input->post('params');

        try {
            $jenis_rhpp = $params['jenis_rhpp'];
            $unit = $params['unit'];
            $start_date = $params['start_date'];
            $end_date = $params['end_date'];

            $data = $this->getData( $jenis_rhpp, $unit, $start_date, $end_date );

            $content['data'] = $data;
            $html = $this->load->view($this->pathView.'list', $content, TRUE);

            $this->result['status'] = 1;
            $this->result['html'] = $html;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
    
    public function tes() {
        $tanggal = '2025-12-29';
        $tgl_awal_tahun = substr($tanggal, 0, 4);

        cetak_r( $tgl_awal_tahun );
    }
}