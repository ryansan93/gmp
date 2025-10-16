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

                -- UPDATE HARGA STOK SIKLUS
                -- select dss.* from det_stok_siklus dss
                update dss
                set
                    dss.hrg_beli = cast(".$harga_baru." as decimal(10, 2)),
                    dss.hrg_jual = cast(".$harga_baru." as decimal(10, 2))
                from det_stok_siklus dss
                left join
                    (
                        select dst.*, ds.hrg_beli from det_stok_trans dst
                        left join
                            det_stok ds
                            on
                                dst.id_header = ds.id
                        where
                            dst.id_header in (
                                select ds.id
                                from det_stok ds 
                                left join
                                    order_pakan op 
                                    on
                                        ds.kode_trans = op.no_order 
                                where
                                    op.rcn_kirim between '".$tgl_order."' and '".$tgl_order."' and
                                    op.supplier = '".$supplier."' and
                                    ds.kode_barang = '".$pakan."'
                            )
                    ) dst
                    on	
                        dss.kode_trans = dst.kode_trans and
                        dss.kode_barang = dst.kode_barang
                where
                    dst.id is not null
                -- END - UPDATE HARGA STOK SIKLUS

                -- UPDATE HARGA STOK SIKLUS PINDAH PAKAN --
                -- select dss.* from det_stok_siklus dss
                update dss
                set
                    dss.hrg_beli = cast(".$harga_baru." as decimal(10, 2)),
                    dss.hrg_jual = cast(".$harga_baru." as decimal(10, 2))
                from det_stok_siklus dss
                left join
                    (
                        select dsts.* from det_stok_trans_siklus dsts
                        left join
                            (
                                select dss.id from det_stok_siklus dss
                                left join
                                    (
                                        select dst.*, ds.hrg_beli from det_stok_trans dst
                                        left join
                                            det_stok ds
                                            on
                                                dst.id_header = ds.id
                                        where
                                            dst.id_header in (
                                                select ds.id
                                                from det_stok ds 
                                                left join
                                                    order_pakan op 
                                                    on
                                                        ds.kode_trans = op.no_order 
                                                where
                                                    op.rcn_kirim between '".$tgl_order."' and '".$tgl_order."' and
                                                    op.supplier = '".$supplier."' and
                                                    ds.kode_barang = '".$pakan."'
                                            )
                                    ) dst
                                    on	
                                        dss.kode_trans = dst.kode_trans and
                                        dss.kode_barang = dst.kode_barang
                                where
                                    dst.id is not null
                            ) dss
                            on
                                dsts.id_header = dss.id
                        where
                            dss.id is not null and
                            dsts.tbl_name = 'terima_pakan'
                    ) dsts
                    on
                        dss.kode_trans = dsts.kode_trans and
                        dss.kode_barang = dsts.kode_barang and
                        dss.jumlah = dsts.jumlah
                where
                    dsts.id is not null
                -- END - UPDATE HARGA STOK SIKLUS PINDAH PAKAN --

                -- UPDATE HARGA STOK SIKLUS RETUR PAKAN --
                /* TUNGGU KALAU ADA */
                -- END - UPDATE HARGA STOK SIKLUS RETUR PAKAN --

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

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    dsts.tbl_name,
                    case
                        when tp.id is not null then
                            cast(tp.id as varchar(20))
                        else
                            dsts.kode_trans
                    end as kode_trans
                from det_stok_trans_siklus dsts
                left join
                    (
                        select dss.* from det_stok_siklus dss
                        left join
                            (
                                select dst.*, ds.hrg_beli from det_stok_trans dst
                                left join
                                    det_stok ds
                                    on
                                        dst.id_header = ds.id
                                where
                                    dst.id_header in (
                                        select ds.id
                                        from det_stok ds 
                                        left join
                                            order_pakan op 
                                            on
                                                ds.kode_trans = op.no_order 
                                        where
                                            op.rcn_kirim between '".$tgl_order."' and '".$tgl_order."' and
                                            op.supplier = '".$supplier."' and
                                            ds.kode_barang = '".$pakan."'
                                    )
                            ) dst
                            on	
                                dss.kode_trans = dst.kode_trans and
                                dss.kode_barang = dst.kode_barang
                        where
                            dst.id is not null

                        union all

                        select dss.* from det_stok_siklus dss
                        left join
                            (
                                select dsts.* from det_stok_trans_siklus dsts
                                left join
                                    (
                                        select dss.id from det_stok_siklus dss
                                        left join
                                            (
                                                select dst.*, ds.hrg_beli from det_stok_trans dst
                                                left join
                                                    det_stok ds
                                                    on
                                                        dst.id_header = ds.id
                                                where
                                                    dst.id_header in (
                                                        select ds.id
                                                        from det_stok ds 
                                                        left join
                                                            order_pakan op 
                                                            on
                                                                ds.kode_trans = op.no_order 
                                                        where
                                                            op.rcn_kirim between '".$tgl_order."' and '".$tgl_order."' and
                                                            op.supplier = '".$supplier."' and
                                                            ds.kode_barang = '".$pakan."'
                                                    )
                                            ) dst
                                            on	
                                                dss.kode_trans = dst.kode_trans and
                                                dss.kode_barang = dst.kode_barang
                                        where
                                            dst.id is not null
                                    ) dss
                                    on
                                        dsts.id_header = dss.id
                                where
                                    dss.id is not null and
                                    dsts.tbl_name = 'terima_pakan'
                            ) dsts
                            on
                                dss.kode_trans = dsts.kode_trans and
                                dss.kode_barang = dsts.kode_barang and
                                dss.jumlah = dsts.jumlah
                        where
                            dsts.id is not null
                    ) dss
                    on
                        dsts.id_header = dss.id
                left join
                    kirim_pakan kp
                    on
                        kp.no_order = dsts.kode_trans
                left join
                    terima_pakan tp
                    on
                        kp.id = tp.id_kirim_pakan
                where
                    dss.id is not null
            ";
            // cetak_r( $sql, 1 );
            $d_conf = $m_conf->hydrateRaw( $sql );

            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $key => $value) {
                    $m_conf = new \Model\Storage\Conf();
                    $sql = "
                        select top 1 '/'+df.path_detfitur as url from setting_automatic_jurnal saj
                        left join
                            detail_fitur df
                            on
                            saj.det_fitur_id = df.id_detfitur
                        where
                            saj.tbl_name = '".$value['tbl_name']."'
                        group by
                            df.path_detfitur
                    ";
                    $d_saj = $m_conf->hydrateRaw( $sql );

                    if ( $d_saj->count() > 0 ) {
                        $d_url = $d_saj->toArray()[0];

                        Modules::run( 'base/InsertJurnal/exec', $d_url['url'], $value['kode_trans'], $value['kode_trans'], 2);
                    }
                }
            }

            // cetak_r( $params, 1 );

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', null, $deskripsi_log, 'update_harga_pakan', null, json_encode($params));

            $this->result['status'] = 1;
            $this->result['message'] = 'Data harga berhasil di update.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes() {
        cetak_r($this->url);
    }
}