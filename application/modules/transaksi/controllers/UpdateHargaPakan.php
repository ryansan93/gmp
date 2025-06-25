<?php defined('BASEPATH') OR exit('No direct script access allowed');

class UpdateHargaPakan extends Public_Controller {

    private $path = 'transaksi/update_harga_pakan/';
    private $url;
    private $akses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->akses = hakAkses($this->url);
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index($segment=0)
    {
        if ( $this->akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/transaksi/update_harga_pakan/js/update-harga-pakan.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/update_harga_pakan/css/update-harga-pakan.css",
            ));

            $data = $this->includes;

            // $mitra = $this->getMitra();
            // $peralatan = $this->get_peralatan();

            $content['akses'] = $this->akses;

            $content['pakan'] = $this->getDataPakan();
            $content['supplier'] = $this->getDataSupplier();
            // $content['add_form'] = $this->addForm();

            // Load Indexx
            $data['title_menu'] = 'Update Harga Pakan';
            $data['view'] = $this->load->view($this->path.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getDataPakan() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select brg1.* from barang brg1
            right join
                (select max(id) as id, kode from barang group by kode) brg2
                on
                    brg1.id = brg2.id
            where
                brg1.tipe = 'pakan'
            order by
                brg1.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getDataSupplier() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select plg1.* from pelanggan plg1
            right join
                (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                on
                    plg1.id = plg2.id
            order by
                plg1.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getRiwayat() {
        $date_min = prev_date( date('Y-m-d'), 30 );
        
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from log_tables lt
            where
                lt.tbl_name = 'update_harga_pakan' and
                lt.waktu >= '".$date_min."'
            order by
                lt.waktu desc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $ket = $value['deskripsi'].' '.tglIndonesia(substr($value['waktu'], 0, 10), '-', ' ', true).' '.substr($value['waktu'], 11, 5);

                $json = json_decode($value['_json'], true);
                $tgl_order = $json['tgl_order'];
                $supplier = $json['supplier'];
                $pakan = $json['pakan'];
                $harga = $json['harga'];

                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select brg1.* from barang brg1
                    right join
                        (select max(id) as id, kode from barang where tipe = 'pakan' group by kode) brg2
                        on
                            brg1.id = brg2.id
                    where
                        brg1.kode = '".$pakan."'
                ";
                $d_conf = $m_conf->hydrateRaw( $sql );

                $nama_pakan = null;
                if ( $d_conf->count() > 0 ) {
                    $nama_pakan = $d_conf->toArray()[0]['nama'];
                }

                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select plg1.* from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                        on
                            plg1.id = plg2.id
                    where
                        plg1.nomor = '".$supplier."'
                    order by
                        plg1.nama asc
                ";
                $d_conf = $m_conf->hydrateRaw( $sql );

                $nama_supplier = null;
                if ( $d_conf->count() > 0 ) {
                    $nama_supplier = $d_conf->toArray()[0]['nama'];
                }

                $data[] = array(
                    'ket' => $ket,
                    'json' => $json,
                    'tgl_order' => $tgl_order,
                    'pakan' => $nama_pakan,
                    'supplier' => $nama_supplier,
                    'harga' => $harga
                );
            }
        }

        $content['data'] = $data;
        $html = $this->load->view($this->path.'listRiwayat', $content, TRUE);

        echo $html;
    }

    public function save() {
        $params = $this->input->post('params');

        try {
            $tgl_order = $params['tgl_order'];
            $pakan = $params['pakan'];
            $supplier = $params['supplier'];
            $harga_baru = $params['harga'];

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                SET NOCOUNT ON 
                -- SET ARITHABORT OFF
                -- SET ANSI_WARNINGS OFF

                -- UPDATE ORDER
                update opd
                set
                    harga = cast(".$harga_baru." as decimal(10, 2)),
                    total = jumlah * cast(".$harga_baru." as decimal(10, 2))
                from order_pakan_detail opd
                left join
                    order_pakan op
                    on
                        opd.id_header = op.id
                where
                    opd.barang = '".$pakan."' and
                    op.rcn_kirim between '".$tgl_order."' and '".$tgl_order."' and
                    op.supplier = '".$supplier."'
                -- END - UPDATE ORDER

                -- UPDATE KONFIRMASI PEMBAYARAN DET
                update kppd
                set
                    kppd.total = opd.total
                from konfirmasi_pembayaran_pakan_det kppd
                left join
                    konfirmasi_pembayaran_pakan kpp 
                    on
                        kpp.id = kppd.id_header 
                left join
                    order_pakan op 
                    on
                        kppd.no_order = op.no_order
                left join
                    (select id_header, sum(total) as total from order_pakan_detail opd group by id_header) opd
                    on
                        op.id = opd.id_header
                where
                    op.rcn_kirim between '".$tgl_order."' and '".$tgl_order."' and
                    kppd.total <> opd.total and
                    op.supplier = '".$supplier."'
                    -- kpp.perusahaan = 'P001' and
                -- END - UPDATE KONFIRMASI PEMBAYARAN DET

                -- UPDATE KONFIRMASI PEMBAYARAN
                update kpp
                set
                    kpp.total = dt.total_detail
                from konfirmasi_pembayaran_pakan kpp 
                right join
                    (	
                        select kpp.id, kpp.total, sum(kppd.total) as total_detail 
                        from konfirmasi_pembayaran_pakan kpp 
                        left join
                            konfirmasi_pembayaran_pakan_det kppd 
                            on
                                kpp.id = kppd.id_header 
                        left join
                            order_pakan op 
                            on
                                kppd.no_order = op.no_order
                        where
                            op.rcn_kirim between '".$tgl_order."' and '".$tgl_order."' and
                            op.supplier = '".$supplier."'
                        group by
                            kpp.id, kpp.total
                    ) dt
                    on
                        kpp.id = dt.id
                where
                    kpp.total <> dt.total_detail
                -- END - UPDATE KONFIRMASI PEMBAYARAN

                -- UPDATE HARGA STOK
                update ds
                set
                	ds.hrg_beli = cast(".$harga_baru." as decimal(10, 2))
                from det_stok ds 
                left join
                    order_pakan op 
                    on
                        ds.kode_trans = op.no_order 
                where
                    op.rcn_kirim between '".$tgl_order."' and '".$tgl_order."' and
                    op.supplier = '".$supplier."' and
                    ds.kode_barang = '".$pakan."'
                -- END - UPDATE HARGA STOK

                select 
                    op.no_order, 
                    opd.*
                from order_pakan_detail opd
                left join
                    order_pakan op
                    on
                        opd.id_header = op.id
                where
                    opd.barang = '".$pakan."' and
                    op.rcn_kirim between '".$tgl_order."' and '".$tgl_order."' and
                    op.supplier = '".$supplier."'
            ";
            // cetak_r( $sql, 1 );
            $d_conf = $m_conf->hydrateRaw( $sql );

            // if ( $d_conf->count() > 0 ) {
            //     $d_conf = $d_conf->toArray();

            //     // cetak_r( $d_conf, 1 );
            // }

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', null, $deskripsi_log, 'update_harga_pakan', null, json_encode($params));

            $this->result['status'] = 1;
            $this->result['message'] = 'Data harga berhasil di update.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}