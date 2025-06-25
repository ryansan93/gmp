<?php defined('BASEPATH') or exit('No direct script access allowed');

class HutangUsaha extends Public_Controller
{
    private $pathView = 'report/hutang_usaha/';
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
                "assets/report/hutang_usaha/js/hutang-usaha.js"
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/report/hutang_usaha/css/hutang-usaha.css"
            ));
            $data = $this->includes;

            $content['akses'] = $this->akses;
            $content['title_panel'] = 'Hutang Usaha';

            $content['jenis'] = $this->getJenis();
            $content['supplier'] = $this->getSupplier();
            $content['perusahaan'] = $this->getPerusahaan();

            // Load Indexx
            $data['title_menu'] = 'Hutang Usaha';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getJenis() {
        $data = array(
            'doc' => 'doc',
            'pakan' => 'pakan',
            'voadip' => 'ovk',
            'oa' => 'ongkos angkut'
        );

        return $data;
    }

    public function getSupplier() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select plg1.* from pelanggan plg1
            right join
                (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                on
                    plg1.id = plg2.id
            where
                plg1.mstatus = 1
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

        $data = null;

        $perusahaan = $params['perusahaan'];
        $jenis = $params['jenis'];
        $supplier = $params['supplier'];
        
        $sql_terima_belum_konfirmasi = "";
        $sql_terima_sudah_konfirmasi = "";
        if ( !in_array('all', $perusahaan) ) {
            if ( empty($sql_terima_belum_konfirmasi) ) {
                $sql_terima_belum_konfirmasi .= "where ";
            } else {
                $sql_terima_belum_konfirmasi .= "and ";
            }
            $sql_terima_belum_konfirmasi .= "data.perusahaan in ('".implode("', '", $perusahaan)."')";
            $sql_terima_sudah_konfirmasi .= "and data.perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        if ( !in_array('all', $jenis) ) {
            if ( empty($sql_terima_belum_konfirmasi) ) {
                $sql_terima_belum_konfirmasi .= "where ";
            } else {
                $sql_terima_belum_konfirmasi .= "and ";
            }
            $sql_terima_belum_konfirmasi .= "data.jenis in ('".implode("', '", $jenis)."')";
            $sql_terima_sudah_konfirmasi .= "and data.jenis in ('".implode("', '", $jenis)."')";
        }

        if ( !in_array('all', $supplier) ) {
            if ( empty($sql_terima_belum_konfirmasi) ) {
                $sql_terima_belum_konfirmasi .= "where ";
            } else {
                $sql_terima_belum_konfirmasi .= "and ";
            }
            $sql_terima_belum_konfirmasi .= "data.supplier in ('".implode("', '", $supplier)."')";
            $sql_terima_sudah_konfirmasi .= "and data.supplier in ('".implode("', '", $supplier)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select data.*, supl.nama as nama_supplier from (
                select kp.no_order, tp.tgl_terima as tanggal, SUBSTRING(kp.no_order, 5, 3) as kode_unit, op.supplier, opd.perusahaan, sum(dtp.jumlah * opd.harga) as total, 'pakan' as jenis from det_terima_pakan dtp 
                left join
                    terima_pakan tp 
                    on
                        dtp.id_header = tp.id
                left join
                    kirim_pakan kp 
                    on
                        tp.id_kirim_pakan = kp.id
                left join
                    order_pakan op 
                    on
                        kp.no_order = op.no_order 
                left join
                    order_pakan_detail opd
                    on
                        opd.id_header = op.id and
                        opd.barang = dtp.item
                where
                    kp.jenis_kirim = 'opks' and
                    not exists (select * from konfirmasi_pembayaran_pakan_det where no_order = kp.no_order) and
                    tp.tgl_terima >= '2023-08-01'
                group by
                    kp.no_order, tp.tgl_terima, op.supplier, opd.perusahaan

                union all

                select kp.no_order, tp.tgl_terima as tanggal, SUBSTRING(kp.no_order, 5, 3) as kode_unit, kp.ekspedisi_id as supplier, rs_tujuan.perusahaan, sum(dtp.jumlah * kp.ongkos_angkut) as total, 'oa' as jenis from det_terima_pakan dtp 
                left join
                    terima_pakan tp 
                    on
                        dtp.id_header = tp.id
                left join
                    kirim_pakan kp 
                    on
                        tp.id_kirim_pakan = kp.id
                left join
                    (
                        select cast(rs1.noreg as varchar(11)) as id_tujuan, m.nama+' (KDG : '+cast(cast(SUBSTRING(rs1.noreg, 10, 2) as int) as varchar(2))+')' as nama, m.perusahaan from rdim_submit rs1
                        right join
                            (select max(mitra) as id, nim from mitra_mapping group by nim) mm
                            on
                                rs1.nim = mm.nim
                        right join
                            mitra m
                            on
                                mm.id = m.id
                        
                        union all
                        
                        select cast(g.id as varchar(11)) as id_tujuan, g.nama as nama, g.perusahaan from gudang g
                    ) rs_tujuan
                    on
                        kp.tujuan = rs_tujuan.id_tujuan
                where
                    kp.jenis_kirim <> 'opks' and
                    kp.ongkos_angkut > 0 and
                    not exists (select * from konfirmasi_pembayaran_oa_pakan_det where no_sj = kp.no_sj) and
                    tp.tgl_terima >= '2023-08-01'
                group by
                    kp.no_order, tp.tgl_terima, kp.ekspedisi_id, rs_tujuan.perusahaan
                    
                union all
                
                select kv.no_order, tv.tgl_terima as tanggal, SUBSTRING(kv.no_order, 5, 3) as kode_unit, ov.supplier, ovd.perusahaan, sum(dtv.jumlah * ovd.harga) as total, 'voadip' as jenis from det_terima_voadip dtv 
                left join
                    terima_voadip tv 
                    on
                        dtv.id_header = tv.id
                left join
                    kirim_voadip kv 
                    on
                        tv.id_kirim_voadip = kv.id
                left join
                    order_voadip ov 
                    on
                        kv.no_order = ov.no_order 
                left join
                    order_voadip_detail ovd
                    on
                        ovd.id_order = ov.id and
                        ovd.kode_barang = dtv.item 
                where
                    kv.jenis_kirim = 'opks' and
                    not exists (select * from konfirmasi_pembayaran_voadip_det where no_order = kv.no_order) and
                    tv.tgl_terima >= '2023-06-01'
                group by
                    kv.no_order, tv.tgl_terima, ov.supplier, ovd.perusahaan
                    
                union all
                
                select od.no_order, td.datang as tanggal, SUBSTRING(od.no_order, 5, 3) as kode_unit, od.supplier, od.perusahaan, sum(td.jml_ekor * od.harga) as total, 'doc' as jenis from terima_doc td 
                right join
                    order_doc od 
                    on
                        td.no_order = od.no_order 
                where
                    not exists (select * from konfirmasi_pembayaran_doc_det where no_order = od.no_order) and
                    td.datang >= '2023-06-01 00:00:00.001'
                group by
                    od.no_order, td.datang, od.supplier, od.perusahaan
            ) data
            left join
                (
                    select plg1.* from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                        on
                            plg1.id = plg2.id
                    where
                        plg1.mstatus = 1
                ) supl
                on
                    supl.nomor = data.supplier
            ".$sql_terima_belum_konfirmasi."
            order by
                data.tanggal asc
        ";
        $d_hutang_belum_konfirmasi = $m_conf->hydrateRaw( $sql );

        if ( $d_hutang_belum_konfirmasi->count() > 0 ) {
            $d_hutang_belum_konfirmasi = $d_hutang_belum_konfirmasi->toArray();

            foreach ($d_hutang_belum_konfirmasi as $k_hutang_belum_konfirmasi => $v_hutang_belum_konfirmasi) {
                $key = str_replace('-', '', substr($v_hutang_belum_konfirmasi['tanggal'], 0, 10)).'-'.$v_hutang_belum_konfirmasi['no_order'].'-'.$v_hutang_belum_konfirmasi['supplier'].'-'.$v_hutang_belum_konfirmasi['perusahaan'];

                $data[ $key ] = array(
                    'tanggal' => $v_hutang_belum_konfirmasi['tanggal'],
                    'no_order' => $v_hutang_belum_konfirmasi['no_order'],
                    'supplier' => $v_hutang_belum_konfirmasi['nama_supplier'],
                    'kode_unit' => $v_hutang_belum_konfirmasi['kode_unit'],
                    'hutang' => $v_hutang_belum_konfirmasi['total'],
                    'tot_bayar' => 0,
                    'sisa' => $v_hutang_belum_konfirmasi['total'],
                    'det_bayar' => null
                );
            }
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select data.*, supl.nama as nama_supplier from (
                select kpp.id, kppd.no_order, kppd.tgl_sj as tanggal, kppd.kode_unit, kpp.supplier, kpp.perusahaan, kppd.total, kpp.total as total_konfirmasi, sum(isnull(rpd.bayar, 0)) as total_bayar, rp.nomor as kode_bayar, rp.tgl_bayar, rp.jml_transfer, 'pakan' as jenis from konfirmasi_pembayaran_pakan_det kppd 
                right join
                    konfirmasi_pembayaran_pakan kpp 
                    on
                        kppd.id_header = kpp.id
                left join
                    realisasi_pembayaran_det rpd 
                    on
                        kpp.nomor = rpd.no_bayar 
                left join
                    realisasi_pembayaran rp 
                    on
                        rpd.id_header = rp.id 
                where
                    isnull(kpp.lunas, 0) = 0 and
                    kppd.tgl_sj >= '2023-08-01'
                group by
                    kpp.id, kppd.no_order, kppd.tgl_sj, kppd.kode_unit, kpp.supplier, kpp.perusahaan, kppd.total, kpp.total, rp.nomor, rp.tgl_bayar, rp.jml_transfer 
                    
                union all
                
                select kpop.id, kpopd.no_sj as no_order, kpopd.tgl_mutasi as tanggal, SUBSTRING(kpopd.no_sj, 4, 3) as kode_unit, kpop.ekspedisi_id as supplier, kpop.perusahaan, kpopd.total, kpop.total as total_konfirmasi, sum(isnull(rpd.bayar, 0)) as total_bayar, rp.nomor as kode_bayar, rp.tgl_bayar, rp.jml_transfer, 'oa' as jenis from konfirmasi_pembayaran_oa_pakan_det kpopd 
                right join
                    konfirmasi_pembayaran_oa_pakan kpop 
                    on
                        kpopd.id_header = kpop.id
                left join
                    realisasi_pembayaran_det rpd 
                    on
                        kpop.nomor = rpd.no_bayar 
                left join
                    realisasi_pembayaran rp 
                    on
                        rpd.id_header = rp.id 
                where
                    isnull(kpop.lunas, 0) = 0 and
                    kpopd.tgl_mutasi >= '2023-08-01'
                group by
                    kpop.id, kpopd.no_sj, kpopd.tgl_mutasi, kpop.ekspedisi_id, kpop.perusahaan, kpopd.total, kpop.total, rp.nomor, rp.tgl_bayar, rp.jml_transfer 
                    
                union all
                
                select kpv.id, kpvd.no_order, kpvd.tgl_sj as tanggal, kpvd.kode_unit, kpv.supplier, kpv.perusahaan, kpvd.total, kpv.total as total_konfirmasi, sum(isnull(rpd.bayar, 0)) as total_bayar, rp.nomor as kode_bayar, rp.tgl_bayar, rp.jml_transfer, 'voadip' as jenis from konfirmasi_pembayaran_voadip_det kpvd 
                right join
                    konfirmasi_pembayaran_voadip kpv 
                    on
                        kpvd.id_header = kpv.id
                left join
                    realisasi_pembayaran_det rpd 
                    on
                        kpv.nomor = rpd.no_bayar 
                left join
                    realisasi_pembayaran rp 
                    on
                        rpd.id_header = rp.id 
                where
                    isnull(kpv.lunas, 0) = 0 and
                    kpvd.tgl_sj >= '2023-06-01'
                group by
                    kpv.id, kpvd.no_order, kpvd.tgl_sj, kpvd.kode_unit, kpv.supplier, kpv.perusahaan, kpvd.total, kpv.total, rp.nomor, rp.tgl_bayar, rp.jml_transfer 
                
                union all
                
                select kpd.id, kpdd.no_order, kpdd.tgl_order as tanggal, kpdd.kode_unit, kpd.supplier, kpd.perusahaan, kpdd.total, kpd.total as total_konfirmasi, sum(isnull(rpd.bayar, 0)) as total_bayar, rp.nomor as kode_bayar, rp.tgl_bayar, rp.jml_transfer, 'doc' as jenis from konfirmasi_pembayaran_doc_det kpdd 
                right join
                    konfirmasi_pembayaran_doc kpd 
                    on
                        kpdd.id_header = kpd.id
                left join
                    realisasi_pembayaran_det rpd 
                    on
                        kpd.nomor = rpd.no_bayar 
                left join
                    realisasi_pembayaran rp 
                    on
                        rpd.id_header = rp.id 
                where
                    isnull(kpd.lunas, 0) = 0 and
                    kpdd.tgl_order >= '2023-06-01'
                group by
                    kpd.id, kpdd.no_order, kpdd.tgl_order, kpdd.kode_unit, kpd.supplier, kpd.perusahaan, kpdd.total, kpd.total, rp.nomor, rp.tgl_bayar, rp.jml_transfer
            ) data
            left join
                (
                    select plg1.* from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
                        on
                            plg1.id = plg2.id
                    where
                        plg1.mstatus = 1
                ) supl
                on
                    supl.nomor = data.supplier
            where
                data.total_konfirmasi > data.total_bayar
                ".$sql_terima_sudah_konfirmasi."
            order by
                data.id asc,
                data.tanggal desc,
                data.no_order desc
        ";
        $d_hutang_sudah_konfirmasi = $m_conf->hydrateRaw( $sql );

        if ( $d_hutang_sudah_konfirmasi->count() > 0 ) {
            $d_hutang_sudah_konfirmasi = $d_hutang_sudah_konfirmasi->toArray();

            $sisa_hutang = null;
            foreach ($d_hutang_sudah_konfirmasi as $k_hutang_sudah_konfirmasi => $v_hutang_sudah_konfirmasi) {
                if ( !isset($sisa_hutang[ $v_hutang_sudah_konfirmasi['id'] ]) ) {
                    $sisa_hutang[ $v_hutang_sudah_konfirmasi['id'] ] = $v_hutang_sudah_konfirmasi['total_konfirmasi'] - $v_hutang_sudah_konfirmasi['total_bayar'];
                }

                if ( $sisa_hutang[ $v_hutang_sudah_konfirmasi['id'] ] > 0 ) {
                    $key_bayar = $v_hutang_sudah_konfirmasi['kode_bayar'];
                    $key = str_replace('-', '', substr($v_hutang_sudah_konfirmasi['tanggal'], 0, 10)).'-'.$v_hutang_sudah_konfirmasi['no_order'].'-'.$v_hutang_sudah_konfirmasi['supplier'].'-'.$v_hutang_sudah_konfirmasi['perusahaan'];

                    $bayar = 0;
                    if ( $sisa_hutang[ $v_hutang_sudah_konfirmasi['id'] ] > $v_hutang_sudah_konfirmasi['total'] ) {
                        $sisa_hutang[ $v_hutang_sudah_konfirmasi['id'] ] -= $v_hutang_sudah_konfirmasi['total'];

                        $data_hutang = array(
                            'tanggal' => $v_hutang_sudah_konfirmasi['tanggal'],
                            'no_order' => $v_hutang_sudah_konfirmasi['no_order'],
                            'supplier' => $v_hutang_sudah_konfirmasi['nama_supplier'],
                            'kode_unit' => $v_hutang_sudah_konfirmasi['kode_unit'],
                            'hutang' => $v_hutang_sudah_konfirmasi['total'],
                            'tot_bayar' => 0,
                            'sisa' => $v_hutang_sudah_konfirmasi['total']
                        );

                        $data[ $key ] = $data_hutang;
                    } else {
                        $tot_bayar_sebelumnya = 0;
                        if ( isset($data[ $key ]) ) {
                            $tot_bayar_sebelumnya = $data[ $key ]['tot_bayar'];
                        }

                        $bayar = ($v_hutang_sudah_konfirmasi['total'] - $sisa_hutang[ $v_hutang_sudah_konfirmasi['id'] ]) - $tot_bayar_sebelumnya;
                        $data_hutang = array(
                            'tanggal' => $v_hutang_sudah_konfirmasi['tanggal'],
                            'no_order' => $v_hutang_sudah_konfirmasi['no_order'],
                            'supplier' => $v_hutang_sudah_konfirmasi['nama_supplier'],
                            'kode_unit' => $v_hutang_sudah_konfirmasi['kode_unit'],
                            'hutang' => $v_hutang_sudah_konfirmasi['total'],
                            'tot_bayar' => $v_hutang_sudah_konfirmasi['total'] - $sisa_hutang[ $v_hutang_sudah_konfirmasi['id'] ],
                            'sisa' => $sisa_hutang[ $v_hutang_sudah_konfirmasi['id'] ]
                        );

                        $data[ $key ] = $data_hutang;

                        $sisa_hutang[ $v_hutang_sudah_konfirmasi['id'] ] = 0;
                    }

                    if ( $bayar > 0 ) {
                        $data[ $key ]['det_bayar'][ $key_bayar ] = array(
                            'kode_bayar' => $key_bayar,
                            'tanggal' => $v_hutang_sudah_konfirmasi['tgl_bayar'],
                            'nominal' => $bayar,
                        );
                    }
                }
            }
        }

        if ( !empty($data) ) {
            ksort( $data );
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list', $content, TRUE);

        echo $html;
    }
}