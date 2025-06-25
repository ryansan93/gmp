<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RekapBarangTidakTerpakai extends Public_Controller {

    private $pathView = 'report/rekap_barang_tidak_terpakai/';
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
                "assets/report/rekap_barang_tidak_terpakai/js/rekap-barang-tidak-terpakai.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/rekap_barang_tidak_terpakai/css/rekap-barang-tidak-terpakai.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['perusahaan'] = $this->getPerusahaan();
            $content['unit'] = $this->getUnit();
            $content['barang'] = $this->getBarang();
            $content['title_menu'] = 'Laporan Rekap Barang Tidak Terpakai';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
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
        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $kode_perusahaan = $m_perusahaan->select('kode')->distinct('kode')->get();

        $data = null;
        if ( $kode_perusahaan->count() > 0 ) {
            $kode_perusahaan = $kode_perusahaan->toArray();

            foreach ($kode_perusahaan as $k => $val) {
                $m_perusahaan = new \Model\Storage\Perusahaan_model();
                $d_perusahaan = $m_perusahaan->where('kode', $val['kode'])->orderBy('version', 'desc')->first();

                $key = strtoupper($d_perusahaan->perusahaan).' - '.$d_perusahaan['kode'];
                $data[ $key ] = array(
                    'nama' => strtoupper($d_perusahaan->perusahaan),
                    'kode' => $d_perusahaan->kode,
                    'jenis_mitra' => $d_perusahaan->jenis_mitra
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function getBarang()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                brg.kode,
                brg.nama,
                case
                    when brg.tipe = 'obat' then
                        'voadip'
                    else
                        brg.tipe
                end as tipe
            from barang brg
            right join
                (select max(id) as id, kode from barang group by kode) brg2
                on
                    brg.id = brg2.id
            where
                brg.tipe in ('pakan', 'obat')
        ";
        $d_brg = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_brg->count() > 0 ) {
            $data = $d_brg->toArray();
        }

        return $data;
    }

    public function getLists(){
        $params = $this->input->get('params');

        $perusahaan = $params['perusahaan'];
        $jenis = $params['jenis'];
        $unit = $params['unit'];
        $barang = $params['barang'];

        $content['data'] = $this->getData( $perusahaan, $unit, $jenis, $barang );
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }

    public function getData($perusahaan = null, $unit = null, $jenis = null, $barang = null) {
        $sql_perusahaan = null;
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan = "and g.perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $sql_unit = null;
        if ( !in_array('all', $unit) ) {
            $sql_unit = "and w.kode in ('".implode("', '", $unit)."')";
        }

        $sql_brg = null;
        if ( !in_array('all', $barang) ) {
            $sql_brg = "and ds.kode_barang in ('".implode("', '", $barang)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $now = $m_conf->getDate();

        $today = $now['tanggal'];

        $dt1 = strtotime($today);
        $month = strtotime("-3 month", $dt1);
        $year = strtotime("-1 year", $dt1);

        $three_month_ago = date('Y-m-d', $month);
        $one_year_ago = date('Y-m-d', $year);

        $sql = "
            select 
                ds.tgl_trans as tgl_terima, 
                krm.no_order,
                krm.no_sj,
                ds.jenis_barang,
                ds.kode_gudang,
                g.nama as nama_gudang,
                ds.kode_barang,
                brg.nama as nama_barang,
                ds.jumlah,
                ds.jml_stok,
                ds.hrg_beli,
                w.kode as kode_unit,
                REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', '') as nama_unit
            from det_stok ds
            right join
                (select max(id) as id from stok) st
                on
                    ds.id_header = st.id
            left join
                (
                    select
                        ds.tgl_trans as tgl_terima_stok,
                        ds.kode_trans,
                        ds.jenis_barang,
                        max(trm.tgl_terima) as tgl_terima_stok_keluar_akhir
                    from det_stok_trans dst
                    left join
                        det_stok ds
                        on
                            dst.id_header = ds.id
                    left join
                        kirim_".$jenis." krm
                        on
                            krm.no_order = dst.kode_trans
                    left join
                        terima_".$jenis." trm
                        on
                            trm.id_kirim_".$jenis." = krm.id
                    group by
                        ds.tgl_trans,
                        ds.kode_trans,
                        ds.jenis_barang
                ) dst
                on
                    ds.kode_trans = dst.kode_trans
            left join
                gudang g
                on
                    g.id = ds.kode_gudang
            left join
                wilayah w
                on
                    g.unit = w.id
            left join
                (
                    select brg1.* from barang brg1
                    right join
                        (select max(id) as id, kode from barang group by kode) brg2
                        on
                            brg1.id = brg2.id
                ) brg
                on
                    brg.kode = ds.kode_barang
            left join
                kirim_".$jenis." krm
                on
                    ds.kode_trans = krm.no_order
            where
                ds.jenis_barang = '".$jenis."' and
                ds.jml_stok > 0 and
                (dst.tgl_terima_stok_keluar_akhir <= '".$three_month_ago."' or ds.tgl_trans <= '".$one_year_ago."')
                ".$sql_perusahaan."
                ".$sql_unit."
                ".$sql_brg."
        ";

        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $key = $value['nama_unit'].'|'.$value['nama_gudang'].'|'.$value['kode_gudang'].'|'.$value['kode_unit'];
                $key_detail = $value['tgl_terima'].'|'.$value['no_order'].'|'.$value['nama_barang'].'|'.$value['kode_barang'].'|'.$value['jenis_barang'];

                if ( !isset($data[ $key ]) ) {
                    $data[ $key ]['kode_unit'] = $value['kode_unit'];
                    $data[ $key ]['nama_unit'] = $value['nama_unit'];
                    $data[ $key ]['kode_gudang'] = $value['kode_gudang'];
                    $data[ $key ]['nama_gudang'] = $value['nama_gudang'];
                    $data[ $key ]['total'] = $value['hrg_beli'] * $value['jml_stok'];
                } else {
                    $data[ $key ]['total'] += $value['hrg_beli'] * $value['jml_stok'];
                }

                $data[ $key ]['detail'][ $key_detail ] = $value;

                ksort( $data[ $key ]['detail'] );
            }

            ksort( $data );
        }

        return $data;
    }

    public function tes() {
        $data = $this->getData(array('all'), array('all'), 'voadip', array('all'));

        cetak_r( $data );
    }
}