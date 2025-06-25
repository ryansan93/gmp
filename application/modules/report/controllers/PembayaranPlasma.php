<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PembayaranPlasma extends Public_Controller {

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
                "assets/jquery/list.min.js",
                'assets/jquery/tupage-table/jquery.tupage.table.js',
                "assets/report/pembayaran_plasma/js/pembayaran-plasma.js",
            ));
            $this->add_external_css(array(
                'assets/jquery/tupage-table/jquery.tupage.table.css',
                "assets/report/pembayaran_plasma/css/pembayaran-plasma.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;
            // $content['pelanggan'] = $this->get_pelanggan();
            $content['title_menu'] = 'Laporan Pembayaran Plasma';

            // Load Indexx
            $data['view'] = $this->load->view('report/pembayaran_plasma/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $m_conf = new \Model\Storage\Conf();

        $sql = "
            select 
                m.nama, 
                rp.tgl_bayar, 
                rhpp.tgl_tutup,
                rp.no_bukti, 
                rp.lampiran, 
                kppd.sub_total as total_rhpp, 
                rpd.bayar as bayar, 
                kppd.jenis,
                rp.keterangan
            from realisasi_pembayaran_det rpd 
            right join
                realisasi_pembayaran rp 
                on
                    rpd.id_header = rp.id 
            right join
                konfirmasi_pembayaran_peternak kpp 
                on
                    rpd.no_bayar = kpp.nomor
            right join
                konfirmasi_pembayaran_peternak_det kppd 
                on
                    kpp.id = kppd.id_header 
            right join
                (
                    select m1.* from mitra m1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) m2
                        on
                            m1.id = m2.id
                ) m
                on
                    kpp.mitra = m.nomor 
            right join
                (
                    select r.id, ts.tgl_tutup as tgl_tutup, 'RHPP' as jenis from rhpp r
                    right join
                        tutup_siklus ts 
                        on
                            r.id_ts = ts.id
                    
                    union all
                    
                    select rg.id, rgh.tgl_submit as tgl_tutup, 'RHPP GROUP' as jenis from rhpp_group rg 
                    right join
                        rhpp_group_header rgh
                        on
                            rg.id_header = rgh.id 
                ) rhpp
                on
                    rhpp.id = kppd.id_trans and
                    rhpp.jenis = kppd.jenis
            where
                rpd.transaksi like '%plasma%' and
                rp.tgl_bayar between '".$start_date."' and '".$end_date."'
            order by
                rp.tgl_bayar desc,
                m.nama asc
        ";

        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view('report/pembayaran_plasma/list', $content, TRUE);

        echo $html;
    }
}