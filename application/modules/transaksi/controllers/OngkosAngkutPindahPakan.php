<?php defined('BASEPATH') or exit('No direct script access allowed');

class OngkosAngkutPindahPakan extends Public_Controller
{
    private $pathView = 'transaksi/ongkos_angkut_pindah_pakan/';
    private $upload_path;
    private $url;
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->upload_path = FCPATH."//uploads/";
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index()
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->load->library('Mobile_Detect');
            $detect = new Mobile_Detect();

            $this->add_external_js(
                array(
                    "assets/select2/js/select2.min.js",
                    'assets/transaksi/ongkos_angkut_pindah_pakan/js/ongkos-angkut-pindah-pakan.js'
                )
            );
            $this->add_external_css(
                array(
                    "assets/select2/css/select2.min.css",
                    'assets/transaksi/ongkos_angkut_pindah_pakan/css/ongkos-angkut-pindah-pakan.css'
                )
            );
            $data = $this->includes;

            $isMobile = true;
            if ( $detect->isMobile() ) {
                $isMobile = true;
            }

            $content['akses'] = $akses;
            $content['isMobile'] = $isMobile;
            $content['riwayat'] = $this->riwayat();
            $content['addForm'] = $this->addForm();

            $data['title_menu'] = 'Ongkos Angkut Pindah Pakan';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

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

        $m_kp = new \Model\Storage\KirimPakan_model();
        $sql = "
            select 
                oap.id,
                _data.tgl_terima,
                _data.no_sj,
                rs_asal.nama as nama_asal,
                rs_asal.id_asal as noreg_asal,
                rs_asal.jenis as jenis_asal,
                rs_tujuan.nama as nama_tujuan,
                rs_tujuan.id_tujuan as noreg_tujuan,
                rs_tujuan.jenis as jenis_tujuan,
                oap.ongkos_angkut
            from 
                (
                    select tp.tgl_terima, kp.no_sj, kp.jenis_kirim, cast(kp.asal as varchar(11)) as asal, cast(kp.tujuan as varchar(11)) as tujuan, kp.no_order as kode_trans from kirim_pakan kp
                    right join
                        terima_pakan tp
                        on
                            kp.id = tp.id_kirim_pakan

                    union all

                    select rp.tgl_retur as tgl_terima, rp.no_retur as no_sj, 'opkp' as jenis_kirim, cast(rp.id_asal as varchar(11)) as asal, cast(rp.id_tujuan as varchar(11)) as tujuan, rp.no_order as kode_trans from retur_pakan rp
                ) _data
            right join
                (
                    select cast(rs1.noreg as varchar(11)) as id_asal, m.nama as nama, 'mitra' as jenis from rdim_submit rs1
                    right join
                        (select max(mitra) as id, nim from mitra_mapping group by nim) mm
                        on
                            rs1.nim = mm.nim
                    right join
                        mitra m
                        on
                            mm.id = m.id
                    
                    union all
                    
                    select cast(g.id as varchar(11)) as id_asal, g.nama as nama, 'gudang' as jenis from gudang g
                ) rs_asal
                on
                    _data.asal = rs_asal.id_asal
            right join
                (
                    select cast(rs1.noreg as varchar(11)) as id_tujuan, m.nama as nama, 'mitra' as jenis from rdim_submit rs1
                    right join
                        (select max(mitra) as id, nim from mitra_mapping group by nim) mm
                        on
                            rs1.nim = mm.nim
                    right join
                        mitra m
                        on
                            mm.id = m.id
                    
                    union all
                    
                    select cast(g.id as varchar(11)) as id_tujuan, g.nama as nama, 'gudang' as jenis from gudang g
                ) rs_tujuan
                on
                    _data.tujuan = rs_tujuan.id_tujuan
            right join
                oa_pindah_pakan oap
                on
                    _data.no_sj = oap.no_sj
            where
                _data.tgl_terima > '2022-08-31' and
                _data.tgl_terima between '".$start_date."' and '".$end_date."'
            order by
                _data.tgl_terima asc
        ";
        $d_kp = $m_kp->hydrateRaw( $sql );

        $data = null;
        if ( $d_kp->count() > 0 ) {
            $d_kp = $d_kp->toArray();

            foreach ($d_kp as $k_kp => $v_kp) {
                $data[] = array(
                    'id' => $v_kp['id'],
                    'tgl_terima' => $v_kp['tgl_terima'],
                    'tgl_terima_text' => strtoupper(tglIndonesia($v_kp['tgl_terima'], '-', ' ')),
                    'no_sj' => $v_kp['no_sj'],
                    'nama_asal' => $v_kp['nama_asal'],
                    'noreg_asal' => $v_kp['noreg_asal'],
                    'jenis_asal' => $v_kp['jenis_asal'],
                    'nama_tujuan' => $v_kp['nama_tujuan'],
                    'noreg_tujuan' => $v_kp['noreg_tujuan'],
                    'jenis_tujuan' => $v_kp['jenis_tujuan'],
                    'ongkos_angkut' => $v_kp['ongkos_angkut']
                );
            }
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list', $content, TRUE);

        echo $html;
    }

    public function loadForm()
    {
        $id = $this->input->get('id');
        $edit = $this->input->get('edit');

        $content = array();
        $html = "url not found";
        
        if ( !empty($id) && $edit != 'edit' ) {
            $html = $this->viewForm($id);
        } else if ( !empty($id) && $edit == 'edit' ) {
            $html = $this->editForm($id);
        }else{
            $html = $this->addForm();
        }

        echo $html;
    }

    public function getUnit()
    {
        $m_duser = new \Model\Storage\DetUser_model();
        $d_duser = $m_duser->where('id_user', $this->userid)->first();

        $m_karyawan = new \Model\Storage\Karyawan_model();
        $d_karyawan = $m_karyawan->where('nama', 'like', strtolower(trim($d_duser->nama_detuser)).'%')->orderBy('id', 'desc')->first();

        $data = null;
        if ( $d_karyawan ) {
            $m_ukaryawan = new \Model\Storage\UnitKaryawan_model();
            $d_ukaryawan = $m_ukaryawan->where('id_karyawan', $d_karyawan->id)->get();

            if ( $d_ukaryawan->count() > 0 ) {
                $d_ukaryawan = $d_ukaryawan->toArray();

                foreach ($d_ukaryawan as $k_ukaryawan => $v_ukaryawan) {
                    if ( stristr($v_ukaryawan['unit'], 'all') === false ) {
                        $m_wil = new \Model\Storage\Wilayah_model();
                        $d_wil = $m_wil->where('id', $v_ukaryawan['unit'])->first();

                        $nama = str_replace('Kab ', '', str_replace('Kota ', '', $d_wil->nama));
                        $kode = $d_wil->kode;

                        $key = $nama.' - '.$kode;

                        $data[$key] = array(
                            'nama' => $nama,
                            'kode' => $kode
                        );
                    } else {
                        $m_wil = new \Model\Storage\Wilayah_model();
                        $d_wil = $m_wil->select('nama', 'kode')->where('jenis', 'UN')->get();

                        if ( $d_wil->count() > 0 ) {
                            $d_wil = $d_wil->toArray();
                            foreach ($d_wil as $k_wil => $v_wil) {
                                $nama = str_replace('Kab ', '', str_replace('Kota ', '', $v_wil['nama']));
                                $kode = $v_wil['kode'];

                                $key = $nama.' - '.$kode;
                                $data[$key] = array(
                                    'nama' => $nama,
                                    'kode' => $kode
                                );
                            }
                        }
                    }
                }
            } else {
                $m_wil = new \Model\Storage\Wilayah_model();
                $d_wil = $m_wil->select('nama', 'kode')->where('jenis', 'UN')->get();

                if ( $d_wil->count() > 0 ) {
                    $d_wil = $d_wil->toArray();
                    foreach ($d_wil as $k_wil => $v_wil) {
                        $nama = str_replace('Kab ', '', str_replace('Kota ', '', $v_wil['nama']));
                        $kode = $v_wil['kode'];

                        $key = $nama.' - '.$kode;
                        $data[$key] = array(
                            'nama' => $nama,
                            'kode' => $kode
                        );
                    }
                }
            }
        } else {
            $m_wil = new \Model\Storage\Wilayah_model();
            $d_wil = $m_wil->select('nama', 'kode')->where('jenis', 'UN')->get();

            if ( $d_wil->count() > 0 ) {
                $d_wil = $d_wil->toArray();
                foreach ($d_wil as $k_wil => $v_wil) {
                    $nama = str_replace('Kab ', '', str_replace('Kota ', '', $v_wil['nama']));
                    $kode = $v_wil['kode'];

                    $key = $nama.' - '.$kode;
                    $data[$key] = array(
                        'nama' => $nama,
                        'kode' => $kode
                    );
                }
            }
        }

        if ( !empty($data) ) {
            ksort($data);
        }

        return $data;
    }

    public function getSjByUnit()
    {
        $params = $this->input->post('params');

        try {
            $m_kp = new \Model\Storage\KirimPakan_model();
            $sql = "
                select * from
                (
                    select 
                        tp.tgl_terima,
                        kp.no_sj,
                        kp.jenis_kirim,
                        rs_asal.nama as nama_asal,
                        rs_asal.id_asal as id_asal,
                        rs_asal.mutasi as mutasi_asal,
                        rs_tujuan.nama as nama_tujuan,
                        rs_tujuan.id_tujuan as noreg_tujuan,
                        rs_tujuan.jenis as jenis_tujuan,
                        rs_tujuan.mutasi as mutasi_tujuan,
                        kp.ekspedisi,
                        kp.no_polisi,
                        kp.sopir
                    from kirim_pakan kp
                    right join
                        (
                            select cast(rs1.noreg as varchar(11)) as id_asal, m.nama as nama, k.unit, 0 as mutasi from rdim_submit rs1
                            right join
                                (select max(mitra) as id, nim from mitra_mapping group by nim) mm
                                on
                                    rs1.nim = mm.nim
                            right join
                                mitra m
                                on
                                    mm.id = m.id
                            left join
                                kandang k
                                on
                                    k.id = rs1.kandang
                            
                            union all
                            
                            select cast(g.id as varchar(11)) as id_asal, g.nama as nama, g.unit, g.mutasi from gudang g
                        ) rs_asal
                        on
                            kp.asal = rs_asal.id_asal
                    right join
                        (
                            select cast(rs1.noreg as varchar(11)) as id_tujuan, m.nama as nama, 'mitra' as jenis, k.unit, 0 as mutasi from rdim_submit rs1
                            right join
                                (select max(mitra) as id, nim from mitra_mapping group by nim) mm
                                on
                                    rs1.nim = mm.nim
                            right join
                                mitra m
                                on
                                    mm.id = m.id
                            left join
                                kandang k
                                on
                                    k.id = rs1.kandang
                            
                            union all
                            
                            select cast(g.id as varchar(11)) as id_tujuan, g.nama as nama, 'gudang' as jenis, g.unit, g.mutasi from gudang g
                        ) rs_tujuan
                        on
                            kp.tujuan = rs_tujuan.id_tujuan
                    right join
                        terima_pakan tp
                        on
                            kp.id = tp.id_kirim_pakan
                    left join
                        wilayah w
                        on
                            w.id = rs_asal.unit
                    where
                        w.kode like '%".$params['unit']."%' and
                        (kp.jenis_kirim = 'opkp' or kp.jenis_kirim = 'opkg') and
                        tp.tgl_terima > '2022-08-31' and
                        not exists (select * from oa_pindah_pakan where no_sj = kp.no_sj)
                    group by
                        tp.tgl_terima,
                        kp.no_sj,
                        kp.jenis_kirim,
                        rs_asal.nama,
                        rs_asal.id_asal,
                        rs_asal.mutasi,
                        rs_tujuan.nama,
                        rs_tujuan.id_tujuan,
                        rs_tujuan.jenis,
                        rs_tujuan.mutasi,
                        kp.ekspedisi,
                        kp.no_polisi,
                        kp.sopir
                ) _data
                where
                    (_data.jenis_kirim = 'opkg' and _data.jenis_tujuan <> 'mitra') or
                    (_data.jenis_kirim = 'opkg' and _data.mutasi_asal = 1) or
                    _data.jenis_kirim = 'opkp'
            ";
            $d_kp = $m_kp->hydrateRaw( $sql );

            $m_rp = new \Model\Storage\ReturPakan_model();
            $sql = "
                select 
                    rp.tgl_retur as tgl_terima,
                    rp.no_retur as no_sj,
                    'opkg' as jenis_kirim,
                    rs_asal.nama as nama_asal,
                    rs_asal.id_asal as id_asal,
                    rs_tujuan.nama as nama_tujuan,
                    rs_tujuan.id_tujuan as noreg_tujuan,
                    rp.ekspedisi,
                    rp.no_polisi,
                    rp.sopir
                from retur_pakan rp
                right join
                    (
                        select cast(rs1.noreg as varchar(11)) as id_asal, m.nama as nama from rdim_submit rs1
                        right join
                            (select max(mitra) as id, nim from mitra_mapping group by nim) mm
                            on
                                rs1.nim = mm.nim
                        right join
                            mitra m
                            on
                                mm.id = m.id
                        
                        union all
                        
                        select cast(g.id as varchar(11)) as id_asal, g.nama as nama from gudang g
                    ) rs_asal
                    on
                        rp.id_asal = rs_asal.id_asal
                right join
                    (
                        select cast(g.id as varchar(11)) as id_tujuan, g.nama as nama from gudang g where mutasi = 1
                    ) rs_tujuan
                    on
                        rp.id_tujuan = rs_tujuan.id_tujuan
                where
                    rp.no_order like '%".$params['unit']."%' and
                    rp.tgl_retur > '2022-08-31' and
                    not exists (select * from oa_pindah_pakan where no_sj = rp.no_retur)
                group by
                    rp.tgl_retur,
                    rp.no_retur,
                    rs_asal.nama,
                    rs_asal.id_asal,
                    rs_tujuan.nama,
                    rs_tujuan.id_tujuan,
                    rp.ekspedisi,
                    rp.no_polisi,
                    rp.sopir
                order by
                    rp.tgl_retur asc
            ";
            $d_rp = $m_rp->hydrateRaw( $sql );

            $data = null;
            if ( $d_kp->count() > 0 ) {
                $d_kp = $d_kp->toArray();

                foreach ($d_kp as $k_kp => $v_kp) {
                    $key = str_replace('-', '', $v_kp['tgl_terima']).' | '.$v_kp['no_sj'];

                    $data[ $key ] = array(
                        'jenis_kirim' => $v_kp['jenis_kirim'],
                        'tgl_terima' => $v_kp['tgl_terima'],
                        'tgl_terima_text' => strtoupper(tglIndonesia($v_kp['tgl_terima'], '-', ' ')),
                        'no_sj' => $v_kp['no_sj'],
                        'nama_asal' => $v_kp['nama_asal'],
                        'id_asal' => $v_kp['id_asal'],
                        'nama_tujuan' => $v_kp['nama_tujuan'],
                        'noreg_tujuan' => $v_kp['noreg_tujuan'],
                        'ekspedisi' => $v_kp['ekspedisi'],
                        'no_polisi' => $v_kp['no_polisi'],
                        'sopir' => $v_kp['sopir']
                    );
                }
            }

            if ( $d_rp->count() > 0 ) {
                $d_rp = $d_rp->toArray();

                foreach ($d_rp as $k_rp => $v_rp) {
                    $key = str_replace('-', '', $v_rp['tgl_terima']).' | '.$v_rp['no_sj'];

                    $data[ $key ] = array(
                        'jenis_kirim' => $v_rp['jenis_kirim'],
                        'tgl_terima' => $v_rp['tgl_terima'],
                        'tgl_terima_text' => strtoupper(tglIndonesia($v_rp['tgl_terima'], '-', ' ')),
                        'no_sj' => $v_rp['no_sj'],
                        'nama_asal' => $v_rp['nama_asal'],
                        'id_asal' => $v_rp['id_asal'],
                        'nama_tujuan' => $v_rp['nama_tujuan'],
                        'noreg_tujuan' => $v_rp['noreg_tujuan'],
                        'ekspedisi' => $v_rp['ekspedisi'],
                        'no_polisi' => $v_rp['no_polisi'],
                        'sopir' => $v_rp['sopir']
                    );
                }
            }

            $_data = null;
            if ( !empty($data) ) {
                ksort($data);

                foreach ($data as $key => $value) {
                    $_data[] = $value;
                }
            }

            $this->result['status'] = 1;
            $this->result['content'] = $_data;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function riwayat()
    {
        $html = $this->load->view($this->pathView . 'riwayat', null, TRUE);

        return $html;
    }

    public function addForm()
    {
        $content['unit'] = $this->getUnit();
        $html = $this->load->view($this->pathView . 'addForm', $content, TRUE);

        return $html;
    }

    public function viewForm($id)
    {
        $data = null;

        $m_oapp = new \Model\Storage\OaPindahPakan_model();
        $sql = "
            select 
                _data.tgl_terima,
                _data.no_sj,
                _data.jenis_kirim,
                w.nama as unit,
                rs_asal.nama as nama_asal,
                rs_asal.id_asal as id_asal,
                rs_tujuan.nama as nama_tujuan,
                rs_tujuan.id_tujuan as noreg_tujuan,
                oap.ekspedisi,
                oap.no_polisi,
                oap.sopir,
                oap.ongkos_angkut
            from
                (
                    select tp.tgl_terima, kp.no_sj, kp.jenis_kirim, cast(kp.asal as varchar(11)) as asal, cast(kp.tujuan as varchar(11)) as tujuan, kp.no_order as kode_trans from kirim_pakan kp
                    right join
                        terima_pakan tp
                        on
                            kp.id = tp.id_kirim_pakan

                    union all

                    select rp.tgl_retur as tgl_terima, rp.no_retur as no_sj, 'opkp' as jenis_kirim, cast(rp.id_asal as varchar(11)) as asal, cast(rp.id_tujuan as varchar(11)) as tujuan, rp.no_order as kode_trans from retur_pakan rp
                ) _data
            right join
                (
                    select cast(rs1.noreg as varchar(11)) as id_asal, m.nama as nama from rdim_submit rs1
                    right join
                        (select max(mitra) as id, nim from mitra_mapping group by nim) mm
                        on
                            rs1.nim = mm.nim
                    right join
                        mitra m
                        on
                            mm.id = m.id
                    
                    union all
                    
                    select cast(g.id as varchar(11)) as id_asal, g.nama as nama from gudang g
                ) rs_asal
                on
                    _data.asal = rs_asal.id_asal
            right join
                (
                    select cast(rs1.noreg as varchar(11)) as id_tujuan, m.nama as nama from rdim_submit rs1
                    right join
                        (select max(mitra) as id, nim from mitra_mapping group by nim) mm
                        on
                            rs1.nim = mm.nim
                    right join
                        mitra m
                        on
                            mm.id = m.id
                    
                    union all
                    
                    select cast(g.id as varchar(11)) as id_tujuan, g.nama as nama from gudang g
                ) rs_tujuan
                on
                    _data.tujuan = rs_tujuan.id_tujuan
            right join
                oa_pindah_pakan oap
                on
                    _data.no_sj = oap.no_sj
            right join
                (
                    select replace(replace(nama, 'Kota ', ''), 'Kab ', '') as nama, kode from wilayah where kode is not null group by kode, replace(replace(nama, 'Kota ', ''), 'Kab ', '')
                ) w
                on
                    w.kode = SUBSTRING(_data.kode_trans, 4, 3)
            where
                oap.id = ".$id." and
                _data.tgl_terima > '2022-08-31'
            order by
                _data.tgl_terima asc
        ";
        $d_aopp = $m_oapp->hydrateRaw( $sql );

        $data = null;
        if ( $d_aopp->count() > 0 ) {
            $d_aopp = $d_aopp->toArray();

            $data = array(
                'id' => $id,
                'unit' => $d_aopp[0]['unit'],
                'jenis_kirim' => $d_aopp[0]['jenis_kirim'],
                'tgl_terima' => $d_aopp[0]['tgl_terima'],
                'tgl_terima_text' => strtoupper(tglIndonesia($d_aopp[0]['tgl_terima'], '-', ' ')),
                'no_sj' => $d_aopp[0]['no_sj'],
                'nama_asal' => $d_aopp[0]['nama_asal'],
                'id_asal' => $d_aopp[0]['id_asal'],
                'nama_tujuan' => $d_aopp[0]['nama_tujuan'],
                'noreg_tujuan' => $d_aopp[0]['noreg_tujuan'],
                'ekspedisi' => $d_aopp[0]['ekspedisi'],
                'no_polisi' => $d_aopp[0]['no_polisi'],
                'sopir' => $d_aopp[0]['sopir'],
                'ongkos_angkut' => $d_aopp[0]['ongkos_angkut']
            );
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'viewForm', $content, TRUE);

        return $html;
    }

    public function editForm($id)
    {
        $data = null;

        $m_oapp = new \Model\Storage\OaPindahPakan_model();
        $sql = "
            select 
                _data.tgl_terima,
                _data.no_sj,
                _data.jenis_kirim,
                w.kode as kode_unit,
                w.nama as unit,
                rs_asal.nama as nama_asal,
                rs_asal.id_asal as id_asal,
                rs_tujuan.nama as nama_tujuan,
                rs_tujuan.id_tujuan as noreg_tujuan,
                oap.ekspedisi,
                oap.no_polisi,
                oap.sopir,
                oap.ongkos_angkut
            from
                (
                    select tp.tgl_terima, kp.no_sj, kp.jenis_kirim, cast(kp.asal as varchar(11)) as asal, cast(kp.tujuan as varchar(11)) as tujuan, kp.no_order as kode_trans from kirim_pakan kp
                    right join
                        terima_pakan tp
                        on
                            kp.id = tp.id_kirim_pakan

                    union all

                    select rp.tgl_retur as tgl_terima, rp.no_retur as no_sj, 'opkp' as jenis_kirim, cast(rp.id_asal as varchar(11)) as asal, cast(rp.id_tujuan as varchar(11)) as tujuan, rp.no_order as kode_trans from retur_pakan rp
                ) _data
            right join
                (
                    select cast(rs1.noreg as varchar(11)) as id_asal, m.nama as nama from rdim_submit rs1
                    right join
                        (select max(mitra) as id, nim from mitra_mapping group by nim) mm
                        on
                            rs1.nim = mm.nim
                    right join
                        mitra m
                        on
                            mm.id = m.id
                    
                    union all
                    
                    select cast(g.id as varchar(11)) as id_asal, g.nama as nama from gudang g
                ) rs_asal
                on
                    _data.asal = rs_asal.id_asal
            right join
                (
                    select cast(rs1.noreg as varchar(11)) as id_tujuan, m.nama as nama from rdim_submit rs1
                    right join
                        (select max(mitra) as id, nim from mitra_mapping group by nim) mm
                        on
                            rs1.nim = mm.nim
                    right join
                        mitra m
                        on
                            mm.id = m.id
                    
                    union all
                    
                    select cast(g.id as varchar(11)) as id_tujuan, g.nama as nama from gudang g
                ) rs_tujuan
                on
                    _data.tujuan = rs_tujuan.id_tujuan
            right join
                oa_pindah_pakan oap
                on
                    _data.no_sj = oap.no_sj
            right join
                (
                    select replace(replace(nama, 'Kota ', ''), 'Kab ', '') as nama, kode from wilayah where kode is not null group by kode, replace(replace(nama, 'Kota ', ''), 'Kab ', '')
                ) w
                on
                    w.kode = SUBSTRING(_data.kode_trans, 4, 3)
            where
                oap.id = ".$id." and
                _data.tgl_terima > '2022-08-31'
            order by
                _data.tgl_terima asc
        ";
        $d_aopp = $m_oapp->hydrateRaw( $sql );

        $data = null;
        if ( $d_aopp->count() > 0 ) {
            $d_aopp = $d_aopp->toArray();

            $data = array(
                'id' => $id,
                'kode_unit' => $d_aopp[0]['kode_unit'],
                'unit' => $d_aopp[0]['unit'],
                'jenis_kirim' => $d_aopp[0]['jenis_kirim'],
                'tgl_terima' => $d_aopp[0]['tgl_terima'],
                'tgl_terima_text' => strtoupper(tglIndonesia($d_aopp[0]['tgl_terima'], '-', ' ')),
                'no_sj' => $d_aopp[0]['no_sj'],
                'nama_asal' => $d_aopp[0]['nama_asal'],
                'id_asal' => $d_aopp[0]['id_asal'],
                'nama_tujuan' => $d_aopp[0]['nama_tujuan'],
                'noreg_tujuan' => $d_aopp[0]['noreg_tujuan'],
                'ekspedisi' => $d_aopp[0]['ekspedisi'],
                'no_polisi' => $d_aopp[0]['no_polisi'],
                'sopir' => $d_aopp[0]['sopir'],
                'ongkos_angkut' => $d_aopp[0]['ongkos_angkut']
            );
        }

        $content['data'] = $data;
        $content['unit'] = $this->getUnit();
        $html = $this->load->view($this->pathView . 'editForm', $content, TRUE);

        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_oap = new \Model\Storage\OaPindahPakan_model();
            $m_oap->no_sj = $params['no_sj'];
            $m_oap->ongkos_angkut = $params['ongkos_angkut'];
            $m_oap->ekspedisi = $params['ekspedisi'];
            $m_oap->no_polisi = $params['no_polisi'];
            $m_oap->sopir = $params['sopir'];
            $m_oap->save();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal 'OA PAKAN', '".$params['no_sj']."', NULL, ".$params['ongkos_angkut'].", 'oa_pindah_pakan', ".$m_oap->id.", NULL, 1";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_oap, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit()
    {
        $params = $this->input->post('params');

        try {
            $m_oap = new \Model\Storage\OaPindahPakan_model();
            $m_oap->where('id', $params['id'])->update(
                array(
                    'no_sj' => $params['no_sj'],
                    'ongkos_angkut' => $params['ongkos_angkut'],
                    'ekspedisi' => $params['ekspedisi'],
                    'no_polisi' => $params['no_polisi'],
                    'sopir' => $params['sopir']
                )
            );

            $d_oap = $m_oap->where('id', $params['id'])->first();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal 'OA PAKAN', '".$params['no_sj']."', NULL, ".$params['ongkos_angkut'].", 'oa_pindah_pakan', ".$params['id'].", ".$params['id'].", 2";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_oap, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di update.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {
            $m_oap = new \Model\Storage\OaPindahPakan_model();
            $d_oap = $m_oap->where('id', $params['id'])->first();

            $m_oap->where('id', $params['id'])->delete();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal NULL, NULL, NULL, NULL, 'oa_pindah_pakan', ".$params['id'].", ".$params['id'].", 3";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_oap, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                opp.* 
            from oa_pindah_pakan opp
            right join
                (
                    select tp.tgl_terima, kp.no_sj, kp.jenis_kirim, cast(kp.asal as varchar(11)) as asal, cast(kp.tujuan as varchar(11)) as tujuan, kp.no_order as kode_trans from kirim_pakan kp
                    right join
                        terima_pakan tp
                        on
                            kp.id = tp.id_kirim_pakan
            
                    union all
            
                    select rp.tgl_retur as tgl_terima, rp.no_retur as no_sj, 'opkp' as jenis_kirim, cast(rp.id_asal as varchar(11)) as asal, cast(rp.id_tujuan as varchar(11)) as tujuan, rp.no_order as kode_trans from retur_pakan rp
                ) _data
                on
                    opp.no_sj = _data.no_sj
            where
                _data.tgl_terima between '2023-10-01' and '2023-10-31' and
                opp.id is not null
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $m_conf = new \Model\Storage\Conf();
                $sql = "exec insert_jurnal 'OA PAKAN', '".$value['no_sj']."', NULL, ".$value['ongkos_angkut'].", 'oa_pindah_pakan', ".$value['id'].", ".$value['id'].", 2";
                $m_conf->hydrateRaw( $sql );
            }
        }
    }
}