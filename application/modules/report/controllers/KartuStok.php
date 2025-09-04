<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KartuStok extends Public_Controller {

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
                "assets/report/kartu_stok/js/kartu-stok.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/kartu_stok/css/kartu-stok.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['gudang'] = $this->getGudang();
            $content['title_menu'] = 'Laporan Kartu Stok';

            // Load Indexx
            $data['view'] = $this->load->view('report/kartu_stok/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getGudang() {
        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                gdg1.* 
            from gudang gdg1
            order by
                gdg1.jenis asc,
                gdg1.nama asc
        ";
        $d_gdg = $m_conf->hydrateRaw( $sql );
        if ( $d_gdg->count() > 0 ) {
            $data = $d_gdg->toArray();
        }

        return $data;
    }

    public function get_gudang_dan_barang()
    {
        $params = $this->input->post('params');

        $data_gdg = null;
        $data_brg = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                gdg1.* 
            from gudang gdg1
            where
                gdg1.jenis like '%".$params."%'
            order by
                gdg1.nama asc
        ";
        $d_gdg = $m_conf->hydrateRaw( $sql );
        if ( $d_gdg->count() > 0 ) {
            $data_gdg = $d_gdg->toArray();
        }

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
                brg1.tipe = '".$params."'
            order by
                brg1.nama asc
        ";
        $d_brg = $m_conf->hydrateRaw( $sql );
        if ( $d_brg->count() > 0 ) {
            $data_brg = $d_brg->toArray();
        }

        $data = array(
            'gudang' => $data_gdg,
            'barang' => $data_brg
        );

        $this->result['list_data'] = $data;

        display_json( $this->result );
    }

    public function get_data_voadip($start_date, $end_date, $kode_gudang, $kode_brg, $jenis)
    {
        $data = null;

        $m_stok = new \Model\Storage\Stok_model();
        $d_stok = $m_stok->whereBetween('periode', [$start_date, $end_date])->orderBy('periode', 'asc')->get();

        $data = null;
        if ( $d_stok->count() > 0 ) {
            $data = $d_stok->toArray();
        }

        $mappingDataReport = $this->mappingDataReport( $data, $kode_brg, $kode_gudang, $jenis, $start_date, $end_date );

        return $mappingDataReport;
    }

    public function get_data_pakan($start_date, $end_date, $kode_gudang, $kode_brg, $jenis)
    {
        $data = null;

        $m_stok = new \Model\Storage\Stok_model();
        $d_stok = $m_stok->whereBetween('periode', [$start_date, $end_date])->orderBy('periode', 'asc')->get();

        $data = null;
        if ( $d_stok->count() > 0 ) {
            $data = $d_stok->toArray();
        }

        $mappingDataReport = $this->mappingDataReport( $data, $kode_brg, $kode_gudang, $jenis, $start_date, $end_date );

        return $mappingDataReport;
    }

    public function mappingDataReport($_kode_brg, $_kode_gudang, $_jenis, $_start_date, $_end_date)
    {
        $sql_jenis = null;
        if ( !empty($_jenis) ) {
            $jenis = $_jenis;
            if ( $jenis == 'obat' ) {
                $jenis = 'voadip';
            }

            $sql_jenis = "and ds.jenis_barang = '".$jenis."'";
        }

        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.*,
                gdg.nama as nama_gudang,
                brg.nama as nama_barang
            from
            (
                /* SALDO AWAL */
                select
                    '".$_start_date."' as tanggal,
                    sa.*,
                    (sa.jumlah * sa.hrg_beli) as debet,
                    0 as kredit,
                    'Saldo Awal' as kode_trans,
                    null as jenis_trans,
                    1 as urut
                from
                (
                    select
                        ds.kode_gudang,
                        ds.kode_barang,
                        ds.jenis_barang,
                        ds.hrg_beli,
                        sum(isnull(ds.jml_stok, 0) + isnull(dst.jumlah, 0)) as jumlah
                    from det_stok ds
                    left join
                        (select id_header, sum(jumlah) as jumlah from det_stok_trans group by id_header) dst
                        on
                            ds.id = dst.id_header
                    left join
                        stok s
                        on
                            ds.id_header = s.id
                    where
                        s.periode = '".$_start_date."' and
                        ds.tgl_trans < '".$_start_date."' and
                        ds.kode_gudang = '".$_kode_gudang."'
                    group by
                        ds.kode_gudang,
                        ds.kode_barang,
                        ds.jenis_barang,
                        ds.hrg_beli
                ) sa
                /* END - SALDO AWAL */

                union all

                /* MASUK */
                select
                    msk.tanggal,
                    msk.kode_gudang,
                    msk.kode_barang,
                    msk.jenis_barang,
                    msk.hrg_beli,
                    msk.jumlah,
                    (msk.jumlah * msk.hrg_beli) as debet,
                    0 as kredit,
                    msk.kode_trans as kode_trans,
                    msk.jenis_trans as jenis_trans,
                    2 as urut
                from
                (
                    select
                        ds.tgl_trans as tanggal,
                        ds.kode_gudang,
                        ds.kode_barang,
                        ds.jenis_barang,
                        ds.kode_trans,
                        ds.jenis_trans,
                        ds.hrg_beli,
                        sum(isnull(ds.jumlah, 0)) as jumlah
            --            sum(isnull(ds.jumlah, 0) + isnull(dst.jumlah, 0)) as jumlah
                    from
                    (
                        select ds1.* from det_stok ds1
                        right join
                            (
                                select min(ds.id) as id, ds.tgl_trans, ds.kode_gudang, ds.kode_barang, ds.kode_trans, ds.jenis_barang, ds.jenis_trans from det_stok ds
                                left join
                                    stok s
                                    on
                                        ds.id_header = s.id
                                where
                                    s.periode between '".$_start_date."' and '".$_end_date."' and
                                    ds.tgl_trans >= '".$_start_date."'
                                    ".$sql_jenis."
                                group by
                                    ds.tgl_trans, ds.kode_gudang, ds.kode_barang, ds.kode_trans, ds.jenis_barang, ds.jenis_trans
                            ) ds2
                            on
                                ds1.id = ds2.id
                    ) ds
                    left join
                        stok s
                        on
                            ds.id_header = s.id
                    where
                        s.periode between '".$_start_date."' and '".$_end_date."' and
                        ds.tgl_trans between '".$_start_date."' and '".$_end_date."' and
                        ds.kode_gudang = '".$_kode_gudang."'
                    group by
                        ds.tgl_trans,
                        ds.kode_gudang,
                        ds.kode_barang,
                        ds.jenis_barang,
                        ds.kode_trans,
                        ds.jenis_trans,
                        ds.hrg_beli
                ) msk
                /* END - MASUK */

                union all

                /* KELUAR */
                select
                    klwr.tanggal,
                    klwr.kode_gudang,
                    klwr.kode_barang,
                    klwr.jenis_barang,
                    klwr.hrg_beli,
                    klwr.jumlah,
                    0 as debet,
                    (klwr.jumlah * klwr.hrg_beli) as kredit,
                    klwr.kode_trans as kode_trans,
                    klwr.jenis_trans as jenis_trans,
                    3 as urut
                from
                (
                    select
                        s.periode as tanggal,
                        ds.kode_gudang,
                        ds.kode_barang,
                        ds.jenis_barang,
                        dst.kode_trans,
                        ds.jenis_trans,
                        ds.hrg_beli,
                        sum(isnull(dst.jumlah, 0)) as jumlah
                    from det_stok_trans dst
                    left join
                        det_stok ds
                        on
                            ds.id = dst.id_header
                    left join
                        stok s
                        on
                            ds.id_header = s.id
                    where
                        s.periode between '".$_start_date."' and '".$_end_date."' and
                        ds.kode_gudang = '".$_kode_gudang."'
                    group by
                        s.periode,
                        ds.kode_gudang,
                        ds.kode_barang,
                        ds.jenis_barang,
                        dst.kode_trans,
                        ds.jenis_trans,
                        ds.hrg_beli
                ) klwr
                /* END - KELUAR */
            ) data
            left join
                (
                    select * from gudang
                ) gdg
                on
                    data.kode_gudang = gdg.id
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
            order by
                data.kode_gudang asc,
                data.kode_barang asc,
                data.tanggal asc,
                data.urut asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getData()
    {
        $params = $this->input->get('params');

        // $start_date = $params['start_date'];
        // $end_date = $params['end_date'];
        // $jenis = $params['jenis'];
        // $kode_gudang = $params['kode_gudang'];
        // $kode_brg = $params['kode_brg'];

        $bulan = $params['bulan'];
        $kode_gudang = $params['gudang'];
        $tahun = substr($params['tahun'], 0, 4);

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

        $data = $this->mappingDataReport(null, $kode_gudang, null, $start_date, $end_date);

        // cetak_r( $data );

        $content['data'] = $data;
        $html = $this->load->view('report/kartu_stok/list', $content, TRUE);

        echo $html;
    }
}