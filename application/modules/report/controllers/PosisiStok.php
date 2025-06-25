<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PosisiStok extends Public_Controller {

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
                "assets/report/posisi_stok/js/posisi-stok.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/posisi_stok/css/posisi-stok.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;
            $content['title_menu'] = 'Laporan Posisi Stok';

            // Load Indexx
            $data['view'] = $this->load->view('report/posisi_stok/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_gudang_dan_barang()
    {
        $params = $this->input->post('params');

        $m_gdg = new \Model\Storage\Gudang_model();
        $d_gdg = $m_gdg->where('jenis', 'like', '%'.$params.'%')->orderBy('nama', 'asc')->get();
        $data_gdg = null;
        if ( $d_gdg->count() > 0 ) {
            $data_gdg = $d_gdg->toArray();
        }

        $m_barang = new \Model\Storage\Barang_model();
        $d_barang = $m_barang->select('kode')->distinct('kode')->where('tipe', $params)->get();
        $_data_brg = null;
        if ( $d_barang->count() > 0 ) {
            $d_barang = $d_barang->toArray();
            foreach ($d_barang as $k_brg => $v_brg) {
                $m_barang = new \Model\Storage\Barang_model();
                $_d_barang = $m_barang->where('kode', $v_brg)->where('tipe', $params)->orderBy('version', 'desc')->first();
                if ( !empty($_d_barang) ) {
                    $key = $_d_barang->nama.' | '.$_d_barang->kode;
                    $_data_brg[$key] = $_d_barang->toArray();
                }
            }
        }

        $data_brg = null;
        if ( !empty($_data_brg) ) {
            ksort($_data_brg);
            foreach ($_data_brg as $k_brg => $v_brg) {
                $data_brg[] = $v_brg;
            }
        }

        $data = array(
            'gudang' => $data_gdg,
            'barang' => $data_brg
        );

        $this->result['list_data'] = $data;

        display_json( $this->result );
    }

    public function get_data_voadip($tanggal, $kode_gudang, $kode_brg, $jenis)
    {
        $data = null;

        $m_stok = new \Model\Storage\Stok_model();
        $d_stok = $m_stok->where('periode', $tanggal)->orderBy('periode', 'asc')->get();

        $data = null;
        if ( $d_stok->count() > 0 ) {
            $data = $d_stok->toArray();
        }

        $mappingDataReport = $this->mappingDataReport( $data, $kode_brg, $kode_gudang, $jenis );

        return $mappingDataReport;
    }

    public function get_data_pakan($tanggal, $kode_gudang, $kode_brg, $jenis)
    {
        $data = null;

        $m_stok = new \Model\Storage\Stok_model();
        $d_stok = $m_stok->where('periode', $tanggal)->orderBy('periode', 'asc')->get();

        $data = null;
        if ( $d_stok->count() > 0 ) {
            $data = $d_stok->toArray();
        }

        $mappingDataReport = $this->mappingDataReport( $data, $kode_brg, $kode_gudang, $jenis );

        return $mappingDataReport;
    }

    public function mappingDataReport($_data, $_kode_brg, $_kode_gudang, $_jenis)
    {
        $kode_brg = array();
        if ( !empty( $_kode_brg ) ) {
            if ( stristr($_kode_brg, 'all') !== FALSE ) {
                $m_brg = new \Model\Storage\Barang_model();
                $d_brg = $m_brg->where('tipe', 'like', '%'.$_jenis.'%')->orderBy('nama', 'asc')->get()->toArray();

                foreach ($d_brg as $k_brg => $v_brg) {
                    $key = $v_brg['nama'].' | '.$v_brg['kode'];
                    $kode_brg[ $key ] = trim($v_brg['kode']);
                }
            } else {
                $m_brg = new \Model\Storage\Barang_model();
                $d_brg = $m_brg->where('kode', $_kode_brg)->where('tipe', 'like', '%'.$_jenis.'%')->orderBy('id', 'asc')->first()->toArray();

                if ( $d_brg ) {
                    $key = $d_brg['nama'].' | '.$d_brg['kode'];
                    $kode_brg[ $key ] = trim($d_brg['kode']);
                }
            }

            ksort( $kode_brg );
        }

        $kode_gdg = array();
        if ( !empty( $_kode_gudang ) ) {
            if ( stristr($_kode_gudang, 'all') !== FALSE ) {
                $m_gdg = new \Model\Storage\Gudang_model();
                $d_gdg = $m_gdg->where('jenis', 'like', '%'.$_jenis.'%')->orderBy('nama', 'asc')->get()->toArray();

                foreach ($d_gdg as $k_gdg => $v_gdg) {
                    $key = $v_gdg['nama'].' | '.$v_gdg['id'];
                    $kode_gdg[ $key ] = trim($v_gdg['id']);
                }
            } else {
                $m_gdg = new \Model\Storage\Gudang_model();
                $d_gdg = $m_gdg->where('id', $_kode_gudang)->where('jenis', 'like', '%'.$_jenis.'%')->orderBy('id', 'asc')->first()->toArray();

                if ( $d_gdg ) {
                    $key = $d_gdg['nama'].' | '.$d_gdg['id'];
                    $kode_gdg[ $key ] = trim($d_gdg['id']);
                }
            }

            ksort( $kode_gdg );
        }

        $data = null;
        if ( !empty($_data) ) {
            // cetak_r( $_data );
            foreach ($_data as $k_data => $v_data) {
                $gdg = implode(',', $kode_gdg);

                if ( $_jenis == 'obat') {
                    $_jenis = 'voadip';
                }

                $m_ds = new \Model\Storage\DetStok_model();
                $sql = "
                    select 
                        ds.id_header as id_header,
                        ds.kode_barang as kode_barang,
                        ds.kode_gudang as kode_gudang,
                        b.nama as nama_barang,
                        b.desimal_harga as decimal,
                        g.nama as nama_gudang,
                        ds.hrg_beli as harga_beli,
                        ds.hrg_jual as harga_jual,
                        ds.tgl_trans as tgl_trans,
                        ds.kode_trans as kode_trans,
                        cast(sum(ds.jml_stok) as float) as jumlah,
                        -- (cast(sum(ds.jml_stok) as float) + cast(case when sum(dst.jumlah) is null then 0 else sum(dst.jumlah) end as float)) as jumlah,
                        kirim.nama_jenis_trans as jenis_trans,
                        dari.nama as dari
                    from det_stok ds
                    left join 
                        (select id_header, kode_barang, sum(jumlah) as jumlah from det_stok_trans group by id_header, kode_barang) dst
                        on
                            ds.id = dst.id_header
                    left join
                        (select b2.* from barang b2 
                        right join  
                            (select max(b.id) as id, b.kode from barang b group by b.kode) b3
                            on
                                b2.id = b3.id) b
                        on
                            ds.kode_barang = b.kode
                    left join
                        gudang g
                        on
                            ds.kode_gudang = g.id
                    left join
                        (
                            select * from (
                                select 
                                    case
                                        when k.jenis_kirim = 'opkg' then
                                            case
                                                when k.jenis_tujuan <> 'peternak' then
                                                    k.asal
                                                else
                                                    rs.nim
                                            end
                                        else
                                            case
                                                when k.jenis_tujuan <> 'peternak' then
                                                    case
                                                        when k.jenis_tujuan = 'gudang' then
                                                            k.asal
                                                        else
                                                            k.tujuan
                                                    end
                                                else
                                                    rs.nim
                                            end
                                    end as tujuan,
                                    k.no_order,
                                    case
                                        when k.jenis_kirim = 'opkg' then
                                            case
                                                when k.jenis_tujuan <> 'peternak' then
                                                    case
                                                        when k.jenis_tujuan = 'gudang' then
                                                            'ORDER'
                                                        else
                                                            'RETUR'
                                                    end
                                                else
                                                    'ORDER'
                                            end
                                        else
                                            'ORDER'
                                    end as jenis_trans,
                                    case
                                        when k.jenis_kirim = 'opkg' then
                                            case
                                                when k.jenis_tujuan <> 'peternak' then
                                                    'PINDAH'
                                                else
                                                    'ORDER'
                                            end
                                        else
                                            'ORDER'
                                    end as nama_jenis_trans
                                from kirim_".$_jenis." k
                                left join
                                    rdim_submit rs 
                                    on
                                        k.tujuan = rs.noreg
                                left join
                                    gudang g 
                                    on
                                        k.tujuan = g.id 
                                        
                                UNION ALL
                                
                                select 
                                    case
                                        when r.asal <> 'peternak' then
                                            r.id_asal
                                        else
                                            rs.nim          
                                    end as tujuan,
                                    r.no_order,
                                    'RETUR' as jenis_trans,
                                    'RETUR' as nama_jenis_trans
                                from retur_".$_jenis." r
                                left join
                                    rdim_submit rs 
                                    on
                                        r.id_asal = rs.noreg
                                left join
                                    gudang g 
                                    on
                                        r.id_asal = g.id
                            ) as data
                            group by
                                data.tujuan,
                                data.no_order,
                                data.jenis_trans,
                                data.nama_jenis_trans
                        ) as kirim
                        on
                            kirim.no_order = ds.kode_trans and
                            kirim.jenis_trans = ds.jenis_trans
                    left join
                        (
                            select * from (
                                select cast(mm.nim as varchar(15)) as id, m.nama as nama from mitra m
                                right join
                                    (
                                        select max(id) as id, nomor from mitra 
                                        group by
                                            nomor
                                    ) as group_mitra
                                    on
                                        m.id = group_mitra.id
                                right join
                                    mitra_mapping mm 
                                    on
                                        m.nomor = mm.nomor
                                group by
                                    m.nama, mm.nim
                                    
                                UNION ALL
                                    
                                select cast(g.id as varchar(15)) as id, g.nama as nama from gudang g 
                                    
                                UNION ALL
                                
                                select cast(p.nomor as varchar(15)) as id, max(p.nama) as nama from pelanggan p
                                left join
                                    (
                                        select max(id) as id, nomor from pelanggan
                                        group by
                                            nomor
                                    ) as group_pelanggan
                                    on
                                        p.id = group_pelanggan.id
                                where
                                    p.tipe = 'supplier' and
                                    p.jenis <> 'ekspedisi'
                                group by
                                    p.nomor
                            ) as supplier
                        ) as dari
                        on
                            dari.id = kirim.tujuan
                    where
                        ds.id_header = ".$v_data['id']." and
                        ds.kode_gudang in (".$gdg.") and
                        ds.jml_stok is not null and
                        (ds.tgl_trans >= g.tgl_stok_opaname or g.tgl_stok_opaname is null)
                    group by
                        ds.id_header,
                        ds.kode_barang,
                        ds.kode_gudang,
                        b.nama,
                        b.desimal_harga,
                        g.nama,
                        ds.hrg_beli,
                        ds.hrg_jual,
                        ds.tgl_trans,
                        ds.kode_trans,
                        ds.jenis_trans,
                        kirim.nama_jenis_trans,
                        dari.nama,
                        g.tgl_stok_opaname
                    order by
                        ds.tgl_trans asc,
                        ds.jenis_trans desc
                ";

                $d_ds = $m_ds->hydrateRaw( $sql );

                if ( $d_ds->count() > 0 ) {
                    $d_ds = $d_ds->toArray();

                    foreach ($d_ds as $k_ds => $v_ds) {
                        if ( in_array(trim($v_ds['kode_barang']), $kode_brg) ) {
                            if ( $v_ds['jumlah'] > 0 ) {
                                $key_gudang = $v_ds['nama_gudang'].' | '.$v_ds['kode_gudang'];

                                $data[ $key_gudang ]['kode'] = $v_ds['kode_gudang'];
                                $data[ $key_gudang ]['nama'] = $v_ds['nama_gudang'];

                                $key_brg = $v_ds['nama_barang'].' | '.$v_ds['kode_barang'];

                                $data[ $key_gudang ]['detail'][ $key_brg ]['kode'] = $v_ds['kode_barang'];
                                $data[ $key_gudang ]['detail'][ $key_brg ]['nama'] = $v_ds['nama_barang'];

                                $key_masuk = str_replace('-', '', $v_ds['tgl_trans']).'-'.$v_ds['kode_trans'].'-'.$v_ds['harga_beli'].'-'.$v_ds['harga_jual'];

                                if ( !isset($data[ $key_gudang ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]) ) {
                                    
                                    $data[ $key_gudang ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['kode'] = $v_ds['kode_trans'];
                                    $data[ $key_gudang ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['dari'] = $v_ds['dari'];
                                    $data[ $key_gudang ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['tgl_trans'] = $v_ds['tgl_trans'];
                                    $data[ $key_gudang ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['masuk'] = $v_ds['jumlah'];
                                    $data[ $key_gudang ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['keluar'] = 0;
                                    $data[ $key_gudang ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['stok_akhir'] = $v_ds['jumlah'];
                                    $data[ $key_gudang ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['harga_beli'] = $v_ds['harga_beli'];
                                    $data[ $key_gudang ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['nilai_beli'] = ($v_ds['jumlah'] * $v_ds['harga_beli']);
                                    $data[ $key_gudang ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['harga_jual'] = $v_ds['harga_jual'];
                                    $data[ $key_gudang ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['decimal'] = $v_ds['decimal'];
                                    $data[ $key_gudang ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['nilai_jual'] = ($v_ds['jumlah'] * $v_ds['harga_jual']);
                                    $data[ $key_gudang ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk'][ $key_masuk ]['jenis_trans'] = $v_ds['jenis_trans'];
                                } 

                                ksort( $data[ $key_gudang ]['detail'][ $key_brg ]['detail'][ $v_ds['tgl_trans'] ]['masuk']);

                                ksort( $data );
                                ksort( $data[ $key_gudang ]['detail'] );
                                ksort( $data[ $key_gudang ]['detail'][ $key_brg ]['detail'] );
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }

    public function get_data()
    {
        $params = $this->input->post('params');

        $tanggal = $params['tanggal'];
        $jenis = $params['jenis'];
        $kode_gudang = $params['kode_gudang'];
        $kode_brg = $params['kode_brg'];

        $data = null;
        if ( $jenis == 'obat' ) {
            $data = $this->get_data_voadip($tanggal, $kode_gudang, $kode_brg, $jenis);
        } else {
            $data = $this->get_data_pakan($tanggal, $kode_gudang, $kode_brg, $jenis);
        }

        $content['data'] = $data;
        $html = $this->load->view('report/posisi_stok/list', $content, TRUE);

        $this->result['html'] = $html;

        display_json( $this->result );
    }
}