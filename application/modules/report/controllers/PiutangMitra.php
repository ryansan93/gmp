<?php defined('BASEPATH') or exit('No direct script access allowed');

class PiutangMitra extends Public_Controller
{
    private $pathView = 'report/piutang_mitra/';
    private $jenis = 'mitra';
    private $url;
    private $akses;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->akses = hakAkses($this->url);
    }

    public function index()
    {
        if ( $this->akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/report/piutang_mitra/js/piutang-mitra.js"
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/report/piutang_mitra/css/piutang-mitra.css"
            ));
            $data = $this->includes;

            $content['akses'] = $this->akses;
            $content['title_panel'] = 'Piutang Plasma';

            $content['mitra'] = $this->getMitra();
            $content['perusahaan'] = $this->getPerusahaan();

            // Load Indexx
            $data['title_menu'] = 'Piutang Plasma';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getMitra() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                m1.id,
                m1.nomor,
                m1.nama,
                w.kode,
                prs.kode_auto as kode_perusahaan
            from mitra m1
            right join
                (
                    select max(id) as id, nomor from mitra group by nomor
                ) m2
                on
                    m1.id = m2.id
            left join
                mitra_mapping mm
                on
                    mm.mitra = m1.id
            left join
                (
                    select kdg1.* from kandang kdg1
                    right join
                        (select max(id) as id, mitra_mapping from kandang group by mitra_mapping) kdg2
                        on
                            kdg1.id = kdg2.id
                ) kdg
                on
                    kdg.mitra_mapping = mm.id
            left join
                wilayah w
                on
                    w.id = kdg.unit
            left join
                (
                    select prs1.* from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    m1.perusahaan = prs.kode
            where
                    m1.mstatus = 1
            order by
                w.kode asc,
                m1.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getPerusahaan() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                p1.kode as nomor,
                p1.perusahaan as nama
            from perusahaan p1
            right join
                (
                    select max(id) as id, kode from perusahaan group by kode
                ) p2
                on
                    p1.id = p2.id
            where
                p1.aktif = 1
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

    public function getLists() {
        $params = $this->input->get('params');

        $mitra = $params['mitra'];
        $perusahaan = $params['perusahaan'];

        $sql = "";
        if ( !in_array('all', $mitra) ) {
            $sql .= "and p.mitra in ('".implode("', '", $mitra)."')";
        }

        if ( !in_array('all', $perusahaan) ) {
            $sql .= "and p.perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                p.*,
                bp.nominal as tot_bayar,
                (p.nominal - isnull(bp.nominal, 0)) as sisa_piutang,
                mtr.nama as nama_mitra,
                mtr.kode_unit,
                prs.perusahaan as nama_perusahaan
            from piutang p
            right join
                (
                    select m1.*, w.kode as kode_unit from mitra m1
                    right join
                        (
                            select max(id) as id, nomor from mitra group by nomor
                        ) m2
                        on
                            m1.id = m2.id
                    left join
                        mitra_mapping mm
                        on
                            mm.mitra = m1.id
                    left join
                        (
                            select kdg1.* from kandang kdg1
                            right join
                                (select max(id) as id, mitra_mapping from kandang group by mitra_mapping) kdg2
                                on
                                    kdg1.id = kdg2.id
                        ) kdg
                        on
                            kdg.mitra_mapping = mm.id
                    left join
                        wilayah w
                        on
                            w.id = kdg.unit
                ) mtr
                on
                    mtr.nomor = p.mitra
            right join
                (
                    select 
                        p1.*
                    from perusahaan p1
                    right join
                        (
                            select max(id) as id, kode from perusahaan group by kode
                        ) p2
                        on
                            p1.id = p2.id
                ) prs
                on
                    prs.kode = p.perusahaan
            left join
                (
                    select
                        data.piutang_kode,
                        sum(data.nominal) as nominal
                    from (
                        select piutang_kode, sum(nominal) as nominal from bayar_piutang group by piutang_kode

                        union all

                        select piutang_kode, sum(nominal) as nominal from rhpp_piutang group by piutang_kode

                        union all

                        select piutang_kode, sum(nominal) as nominal from rhpp_group_piutang group by piutang_kode
                    ) data
                    group by
                        data.piutang_kode
                ) bp
                on
                    p.kode = bp.piutang_kode
            where
                p.jenis = '".$this->jenis."'
                ".$sql."
            order by
                p.tanggal desc,
                mtr.nama asc
        ";
        $d_piutang = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_piutang->count() > 0 ) {
            $d_piutang = $d_piutang->toArray();

            foreach ($d_piutang as $k_piutang => $v_piutang) {
                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select
                        data.*
                    from (
                        select piutang_kode, tanggal, nominal, 'NON RHPP' as jenis from bayar_piutang

                        union all

                        select 
                            rp.piutang_kode,
                            ts.tgl_tutup as tanggal,
                            rp.nominal,
                            'RHPP' as jenis
                        from rhpp_piutang rp
                        right join
                            rhpp r
                            on
                                rp.id_header = r.id
                        right join
                            tutup_siklus ts
                            on
                                ts.id = r.id_ts

                        union all

                        select 
                            rgp.piutang_kode, 
                            rgh.tgl_submit as tanggal,
                            rgp.nominal,
                            'RHPP GROUP' as jenis
                        from rhpp_group_piutang rgp
                        right join
                            rhpp_group rg
                            on
                                rgp.id_header = rg.id
                        right join
                            rhpp_group_header rgh
                            on
                                rgh.id = rg.id_header
                    ) data
                    where
                        data.piutang_kode = '".$v_piutang['kode']."'
                    order by
                        data.tanggal desc
                ";
                $d_bayar_piutang = $m_conf->hydrateRaw( $sql );

                $det_bayar = null;
                if ( $d_bayar_piutang->count() > 0 ) {
                    $det_bayar = $d_bayar_piutang->toArray();
                }

                $data[ $v_piutang['kode'] ] = array(
                    'kode' => $v_piutang['kode'],
                    'tanggal' => $v_piutang['tanggal'],
                    'nama' => $v_piutang['kode_unit'].' | '.$v_piutang['nama_mitra'],
                    'perusahaan' => $v_piutang['nama_perusahaan'],
                    'piutang' => $v_piutang['nominal'],
                    'tot_bayar' => $v_piutang['tot_bayar'],
                    'sisa' => $v_piutang['sisa_piutang'],
                    'det_bayar' => $det_bayar
                );
            }
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list', $content, TRUE);

        echo $html;
    }
}