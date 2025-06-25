<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RiwayatPerformancePlasma extends Public_Controller {

    private $pathView = 'report/riwayat_performance_plasma/';
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
                "assets/report/riwayat_performance_plasma/js/riwayat-performance-plasma.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/riwayat_performance_plasma/css/riwayat-performance-plasma.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['unit'] = $this->getUnit();
            $content['mitra'] = $this->getMitra();
            $content['title_menu'] = 'Riwayat Performance Plasma';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getUnit() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                w1.kode,
                REPLACE(REPLACE(w1.nama, 'Kab ', ''), 'Kota ', '') as nama
            from wilayah w1
            right join
                (select max(id) as id, kode from wilayah where kode is not null group by kode) w2
                on
                    w1.id = w2.id
            order by
                REPLACE(REPLACE(w1.nama, 'Kab ', ''), 'Kota ', '') asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getMitra() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                mtr.nomor,
                mtr.nama as nama_mitra,
                mm.nim,
                w.kode as kode_unit,
                REPLACE(REPLACE(w.nama, 'Kab ', ''), 'Kota ', '') as nama_unit,
                mtr.mstatus,
                k.kandang as no_kdg,
                prs.kode_auto as kode_perusahaan
            from kandang k
            right join
                mitra_mapping mm
                on
                    k.mitra_mapping = mm.id
            right join
                (
                    select mtr1.* from mitra mtr1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) mtr2
                        on
                            mtr1.id = mtr2.id
                ) mtr
                on
                    mm.mitra = mtr.id
            left join
                wilayah w
                on
                    w.id = k.unit
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
            -- where mtr.mstatus = 1
            group by
                mtr.nomor,
                mtr.nama,
                mm.nim,
                w.kode,
                REPLACE(REPLACE(w.nama, 'Kab ', ''), 'Kota ', ''),
                mtr.mstatus,
                k.kandang,
                prs.kode_auto
            order by
                mtr.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getData($unit, $mitra) {
        $sql_params = "";
        if ( !in_array('all', $unit) ) {
            if ( empty($sql_params) ) {
                $sql_params .= "where mtr.kode_unit in ('".implode("', '", $unit)."')";
            } else {
                $sql_params .= "and mtr.kode_unit in ('".implode("', '", $unit)."')";
            }
        }

        if ( !in_array('all', $mitra) ) {
            $_mitra = null;
            $_kdg = null;

            foreach ($mitra as $k_mtr => $v_mtr) {
                $d_mitra = json_decode( $v_mtr, 1 );

                $_mitra[] = $d_mitra['nomor'];
                $_kdg[] = $d_mitra['no_kdg'];
            }

            if ( empty($sql_params) ) {
                $sql_params .= "where rhpp.nomor in ('".implode("', '", $_mitra)."')";
                $sql_params .= "and (rhpp.kandang in ('".implode("', '", $_kdg)."') or rhpp.kandang = '')";
            } else {
                $sql_params .= "and rhpp.nomor in ('".implode("', '", $_mitra)."')";
                $sql_params .= "and (rhpp.kandang in ('".implode("', '", $_kdg)."') or rhpp.kandang = '')";
            }
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                mtr.kode_unit,
                mtr.nama_unit,
                rhpp.*
            from (
                select
                    m.nomor, 
                    m.nama as mitra, 
                    cast(cast(SUBSTRING(r.noreg, 10, 2) as int) as varchar(2)) as kandang,
                    min(rd.tanggal) as tgl_chick_in,
                    rd.barang, 
                    sum(rd.jumlah) as ekor, 
                    r.rata_umur as umur,
                    r.deplesi as deplesi,
                    r.fcr as fcr,
                    r.bb as bb,
                    r.ip as ip,
                    (r.pdpt_peternak_belum_pajak - r.potongan_pajak) as pdpt_plasma,
                    ((r.pdpt_peternak_belum_pajak - r.potongan_pajak) / sum(rd.jumlah)) as pdpt_plasma_per_ekor
                from rhpp r
                left join
                    rhpp_doc rd
                    on
                        rd.id_header = r.id
                left join
                    (
                        select mm1.* from mitra_mapping mm1
                        right join
                            (select max(id) as id, nim from mitra_mapping group by nim) mm2
                            on
                                mm1.id = mm2.id
                    ) mm
                    on
                        mm.nim = SUBSTRING(r.noreg, 0, 8)
                left join
                    mitra m
                    on
                        m.id = mm.mitra
                where
                    r.jenis = 'rhpp_plasma' and
                    not exists (select * from rhpp_group_noreg where noreg = r.noreg)
                group by
                    m.nomor, 
                    m.nama, 
                    r.noreg,
                    rd.barang, 
                    r.rata_umur,
                    r.deplesi,
                    r.fcr,
                    r.bb,
                    r.ip,
                    r.pdpt_peternak_belum_pajak,
                    r.potongan_pajak
            
                union all
            
                select 
                    rgh.nomor, 
                    rgh.mitra, 
                    '' as kandang,
                    min(rgd.tanggal) as tgl_chick_in,
                    rgd.barang, 
                    sum(rgd.jumlah) as ekor, 
                    rg.rata_umur as umur,
                    rg.deplesi as deplesi,
                    rg.fcr as fcr,
                    rg.bb as bb,
                    rg.ip as ip,
                    (rg.pdpt_peternak_belum_pajak - rg.potongan_pajak) as pdpt_plasma,
                    ((rg.pdpt_peternak_belum_pajak - rg.potongan_pajak) / sum(rgd.jumlah)) as pdpt_plasma_per_ekor
                from rhpp_group rg
                left join
                    rhpp_group_header rgh 
                    on
                        rg.id_header = rgh.id
                left join
                    rhpp_group_doc rgd 
                    on
                        rg.id = rgd.id_header
                where
                    rg.jenis = 'rhpp_plasma'
                group by
                    rgh.nomor, 
                    rgh.mitra, 
                    rgd.barang, 
                    rg.rata_umur,
                    rg.deplesi,
                    rg.fcr,
                    rg.bb,
                    rg.ip,
                    rg.pdpt_peternak_belum_pajak,
                    rg.potongan_pajak
            ) rhpp
            left join
                (
                    select
                        mtr.nomor,
                        mtr.nama as nama_mitra,
                        mm.nim,
                        w.kode as kode_unit,
                        REPLACE(REPLACE(w.nama, 'Kab ', ''), 'Kota ', '') as nama_unit,
                        mtr.mstatus
                    from kandang k
                    right join
                        mitra_mapping mm
                        on
                            k.mitra_mapping = mm.id
                    right join
                        (
                            select mtr1.* from mitra mtr1
                            right join
                                (select max(id) as id, nomor from mitra group by nomor) mtr2
                                on
                                    mtr1.id = mtr2.id
                        ) mtr
                        on
                            mm.mitra = mtr.id
                    left join
                        wilayah w
                        on
                            w.id = k.unit
                    group by
                        mtr.nomor,
                        mtr.nama,
                        mm.nim,
                        w.kode,
                        REPLACE(REPLACE(w.nama, 'Kab ', ''), 'Kota ', ''),
                        mtr.mstatus
                ) mtr
                on
                    rhpp.nomor = mtr.nomor
            ".$sql_params."
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $key_mitra = $value['kandang'].' | '.$value['nomor'];

                if ( !isset($data[ $value['kode_unit'] ]) ) {
                    $data[ $value['kode_unit'] ]['kode_unit'] = $value['kode_unit'];
                    $data[ $value['kode_unit'] ]['nama_unit'] = $value['nama_unit'];
                }

                if ( !isset($data[ $value['kode_unit'] ]['mitra'][ $key_mitra ]) ) {
                    $data[ $value['kode_unit'] ]['mitra'][ $key_mitra ]['nomor'] = $value['nomor'];
                    $data[ $value['kode_unit'] ]['mitra'][ $key_mitra ]['nama'] = $value['mitra'];
                }

                $key_det = $value['tgl_chick_in'];
                $data[ $value['kode_unit'] ]['mitra'][ $key_mitra ]['detail'][ $key_det ] = $value;

                ksort($data[ $value['kode_unit'] ]['mitra'][ $key_mitra ]['detail']);
                ksort($data[ $value['kode_unit'] ]['mitra']);
                ksort($data);
            }
        }

        return $data;
    }

    public function getLists() {
        $params = $this->input->get('params');

        $unit = $params['unit'];
        $mitra = $params['mitra'];

        $data = $this->getData( $unit, $mitra );

        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
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

        $unit = $params['unit'];
        $mitra = $params['mitra'];

        $data = $this->getData( $unit, $mitra );

        $content['data'] = $data;
        $res_view_html = $this->load->view($this->pathView.'exportExcel', $content, true);
        $filename = "RIWAYAT_PERFORMANCE_MITRA";

        // header("Content-type: application/xls");
        // header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        // header("Content-type:   application/x-msexcel; charset=utf-8");
        header("Content-type:   application/ms-excel; charset=utf-8");
        $filename = $filename.'.xls';
        header("Content-Disposition: attachment; filename=".$filename."");
        echo $res_view_html;
    }
}