<?php defined('BASEPATH') or exit('No direct script access allowed');

class PiutangKaryawan extends Public_Controller
{
    private $pathView = 'report/piutang_karyawan/';
    private $jenis = 'karyawan';
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
                "assets/report/piutang_karyawan/js/piutang-karyawan.js"
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/report/piutang_karyawan/css/piutang-karyawan.css"
            ));
            $data = $this->includes;

            $content['akses'] = $this->akses;
            $content['title_panel'] = 'Piutang Karyawan';

            $content['karyawan'] = $this->getKaryawan();
            $content['perusahaan'] = $this->getPerusahaan();

            // Load Indexx
            $data['title_menu'] = 'Piutang Karyawan';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getKaryawan() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                k1.id,
                k1.nik,
                k1.nama,
                k1.jabatan
            from karyawan k1
            right join
                (
                    select max(id) as id, nik from karyawan group by nik
                ) k2
                on
                    k1.id = k2.id
            where
                k1.status = 1
            order by
                k1.level asc,
                k1.nama asc
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

        $karyawan = $params['karyawan'];
        $perusahaan = $params['perusahaan'];

        $sql = "";
        if ( !in_array('all', $karyawan) ) {
            $sql .= "and p.karyawan in ('".implode("', '", $karyawan)."')";
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
                kry.nama as nama_karyawan,
                kry.jabatan,
                prs.perusahaan as nama_perusahaan
            from piutang p
            right join
                (
                    select k1.* from karyawan k1
                    right join
                        (
                            select max(id) as id, nik from karyawan group by nik
                        ) k2
                        on
                            k1.id = k2.id
                ) kry
                on
                    kry.nik = p.karyawan
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
                kry.nama asc
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
                        select piutang_kode, tanggal, nominal from bayar_piutang
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
                    'nama' => $v_piutang['jabatan'].' | '.$v_piutang['nama_karyawan'],
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