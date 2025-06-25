<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PindahBarang extends Public_Controller {

    private $pathView = 'report/pindah_barang/';
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
                "assets/report/pindah_barang/js/pindah-barang.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/pindah_barang/css/pindah-barang.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['title_menu'] = 'Laporan Pindah Barang';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getLists() {
        $params = $this->input->get('params');

        $jenis_laporan = $params['jenis'];

        $column_no_sj_asal = "'-'";
        if ( $jenis_laporan == 'pakan' ) {
            $column_no_sj_asal = "dk.no_sj_asal";
        }

        $sql_optional = "";
        if ( $params['jenis_filter'] == 'tanggal' ) {
            $start_date = $params['start_date'];
            $end_date = $params['end_date'];
            $sql_optional = "where data.tgl_terima between '".$start_date."' and '".$end_date."'";
        } else {
            $no_sj_asal = $params['no_sj_asal'];
            $sql_optional = "where data.no_sj_asal = '".$no_sj_asal."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.tgl_terima,
                data.no_sj_asal,
                data.no_sj,
                data.nama_asal,
                data.nama_tujuan,
                data.nama_barang,
                sum(data.jumlah) as jumlah
            from
            (
                select
                    t.tgl_terima,
                    k.no_sj_asal,
                    k.no_sj,
                    asal.nama as nama_asal,
                    tujuan.nama as nama_tujuan,
                    brg.nama as nama_barang,
                    case
                        when dt.jumlah > k.jumlah then
                            k.jumlah
                        else    		
                            dt.jumlah
                    end as jumlah
                from det_terima_".$jenis_laporan." dt
                left join
                    terima_".$jenis_laporan." t
                    on
                        t.id = dt.id_header
                left join
                    (
                        select 
                            dk.id,
                            dk.id_header,
                            dk.item,
                            dk.jumlah,
                            ".$column_no_sj_asal." as no_sj_asal,
                            k.no_sj,
                            k.jenis_kirim,
                            k.asal,
                            k.jenis_tujuan,
                            k.tujuan from det_kirim_".$jenis_laporan." dk
                        right join
                            kirim_".$jenis_laporan." k
                            on
                                dk.id_header = k.id
                    ) k
                    on
                        k.id_header = t.id_kirim_".$jenis_laporan." and
                        k.item = dt.item
                left join
                    (
                        select cast(g.id as varchar(15)) as id, g.nama from gudang g
                        
                        union all
                
                        select cast(rs.noreg as varchar(15)) as id, mtr.nama+' (KDG : '+cast(cast(SUBSTRING(rs.noreg, 10, 2) as int) as varchar(5))+')' as nama from rdim_submit rs 
                        left join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                rs.nim = mm.nim
                        left join
                            mitra mtr
                            on
                                mm.mitra = mtr.id
                    ) asal
                    on
                        k.asal = asal.id
                left join
                    (
                        select cast(g.id as varchar(15)) as id, g.nama from gudang g
                        
                        union all
                
                        select cast(rs.noreg as varchar(15)) as id, mtr.nama+' (KDG : '+cast(cast(SUBSTRING(rs.noreg, 10, 2) as int) as varchar(5))+')' as nama from rdim_submit rs 
                        left join
                            (
                                select mm1.* from mitra_mapping mm1
                                right join
                                    (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                    on
                                        mm1.id = mm2.id
                            ) mm
                            on
                                rs.nim = mm.nim
                        left join
                            mitra mtr
                            on
                                mm.mitra = mtr.id
                    ) tujuan
                    on
                        k.tujuan = tujuan.id
                left join
                    (
                        select brg1.* from barang brg1
                        right join
                            (select max(id) as id, kode from barang group by kode) brg2
                            on
                                brg1.id = brg2.id
                    ) brg
                    on
                        brg.kode = dt.item
                where
                    t.tgl_terima is not null and
                    k.jenis_kirim = 'opkp' or (k.jenis_kirim = 'opkg' and k.jenis_tujuan = 'gudang')
            ) data
            ".$sql_optional."
            group by
                data.tgl_terima,
                data.no_sj_asal,
                data.no_sj,
                data.nama_asal,
                data.nama_tujuan,
                data.nama_barang
            order by
                data.tgl_terima asc,
                data.nama_asal asc

            -- select * from det_
        ";
        $d_conf = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);
        echo $html;
    }
}