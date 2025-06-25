<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Peternak extends Public_Controller {

    private $url;
    private $status_kandang = [
        1 => 'Aktif',
        0 => 'Tidak Aktif'
    ];
    private $isMobile = false;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;

        $this->load->library('Mobile_Detect');
        $detect = new Mobile_Detect();

        if ( $detect->isMobile() ) {
            $this->isMobile = true;
        }
    }

    public function index()
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(
                array(
                    'assets/jquery/maskedinput/jquery.maskedinput.min.js',
                    'assets/select2/js/select2.min.js',
                    'assets/pagination-ry/pagination.js',
                    'assets/parameter/peternak/js/peternak.js'
                )
            );
            $this->add_external_css(
                array(
                    'assets/select2/css/select2.min.css',
                    'assets/pagination-ry/pagination.css',
                    'assets/parameter/peternak/css/peternak.css'
                )
            );
            $data = $this->includes;

            $content['title_panel'] = 'Pengajuan Data Mitra';
			$content['list_mitra'] = null;

            $content['akses'] = $akses;
            $content['isMobile'] = $this->isMobile;
            if ( $this->isMobile ) {
                $content['unit'] = $this->getUnit();
                $content['kecamatan'] = $this->getKecamatan();
                $content['mitra'] = $this->getMitra();
            }
            $content['resubmit'] = null;
            $content['add_form'] = $this->add_form();

            $data['title_menu'] = 'Master Peternak';
            $data['view'] = $this->load->view('parameter/peternak/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        } 
    }

    public function getUnit() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                w.kode,
                REPLACE(REPLACE(w.nama, 'Kab ', ''), 'Kota ', '') as nama
            from wilayah w
            where
                w.jenis = 'UN'
            group by
                w.kode,
                REPLACE(REPLACE(w.nama, 'Kab ', ''), 'Kota ', '')
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ($d_conf->count() > 0) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getKecamatan() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                l.*,
                REPLACE(REPLACE(induk_kec.nama, 'Kab ', ''), 'Kota ', '') as nama_header
            from lokasi l
            left join
                lokasi induk_kec
                on
                    l.induk = induk_kec.id
            where 
                l.jenis = 'KC' and
                induk_kec.id is not null
            order by
                l.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ($d_conf->count() > 0) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getMitra() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                w.kode as kode_unit,
                REPLACE(REPLACE(w.nama, 'Kab ', ''), 'Kota ', '') as nama_unit,
                mtr.nomor,
                mtr.nama,
                prs.kode_auto as perusahaan
            from
                (
                    select mtr1.* from mitra mtr1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) mtr2
                        on
                            mtr1.id = mtr2.id
                ) mtr
            left join
                mitra_mapping mm
                on
                    mtr.id = mm.mitra
            left join
                kandang k
                on
                    mm.id = k.mitra_mapping
            left join
                wilayah w
                on
                    k.unit = w.id
            left join
                (
                    select prs1.* from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    mtr.perusahaan = prs.kode
            where
                mtr.mstatus = 1
            group by
                w.kode,
                REPLACE(REPLACE(w.nama, 'Kab ', ''), 'Kota ', ''),
                mtr.nomor,
                mtr.nama,
                prs.kode_auto
            order by
                w.kode asc,
                mtr.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ($d_conf->count() > 0) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getListMobile()
    {
        $params = $this->input->get('params');

        $sql = null;
        if ( isset($params['mitra']) && !empty($params['mitra']) ) {
            if ( empty( $sql ) ) {
                $sql = "and mtr.nomor = '".$params['mitra']."'";
            } else {
                $sql .= "and mtr.nomor = '".$params['mitra']."'";
            }
        }

        if ( isset($params['unit']) && !empty($params['unit']) ) {
            if ( empty( $sql ) ) {
                $sql = "and w.kode = '".$params['unit']."'";
            } else {
                $sql .= "and w.kode = '".$params['unit']."'";
            }
        }

        if ( isset($params['kecamatan']) && !empty($params['kecamatan']) ) {
            if ( empty( $sql ) ) {
                $sql = "and k.alamat_kecamatan = '".$params['kecamatan']."'";
            } else {
                $sql .= "and k.alamat_kecamatan = '".$params['kecamatan']."'";
            }
        }

        $m_pp = new \Model\Storage\MitraPosisi_model();
        $sql = "
            select
                w.kode as kode_unit,
                REPLACE(REPLACE(w.nama, 'Kab ', ''), 'Kota ', '') as nama_unit,
                mtr.nomor,
                mtr.nama,
                mm.nim,
                -- k.kandang,
                k.alamat_kecamatan,
                prs.kode_auto as perusahaan,
                rs.max_tgl_chickin
                -- cast(mp.lat_long as varchar(max)) as posisi,
                -- cast(mp.foto_kunjungan as varchar(max)) as foto
            from
                (
                    select mtr1.* from mitra mtr1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) mtr2
                        on
                            mtr1.id = mtr2.id
                ) mtr
            left join
                mitra_mapping mm
                on
                    mtr.id = mm.mitra
            left join
                kandang k
                on
                    mm.id = k.mitra_mapping
            left join
                wilayah w
                on
                    k.unit = w.id
            left join
                (
                    select prs1.* from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    mtr.perusahaan = prs.kode
            left join
                (
                    select mp1.* from mitra_posisi mp1
                    right join
                        (select max(id) as id, nomor, kandang from mitra_posisi group by nomor, kandang) mp2
                        on
                            mp1.id = mp2.id
                ) mp
                on
                    mtr.nomor = mp.nomor and
                    k.kandang = mp.kandang
            left join
                (
                    select max(data.datang) as max_tgl_chickin, data.nim
                    from
                    (
                        select rs.noreg, rs.nim, td.datang from rdim_submit rs
                        left join
                            (
                                select od1.* from order_doc od1
                                right join
                                    (select max(id) as id, no_order from order_doc group by no_order) od2
                                    on
                                        od1.id = od2.id
                            ) od
                            on
                                od.noreg = rs.noreg
                        left join
                            (
                                select td1.* from terima_doc td1
                                right join
                                    (select max(id) as id, no_order from terima_doc group by no_order) td2
                                    on
                                        td1.id = td2.id
                            ) td
                            on
                                td.no_order = od.no_order
                        where
                            td.id is not null
                    ) data
                    group by
                        data.nim
                ) rs
                on
                    rs.nim = mm.nim
            where
                mtr.mstatus = 1
                ".$sql."
            group by
                w.kode,
                REPLACE(REPLACE(w.nama, 'Kab ', ''), 'Kota ', ''),
                mtr.nomor,
                mtr.nama,
                mm.nim,
                -- k.kandang,
                k.alamat_kecamatan,
                prs.kode_auto,
                rs.max_tgl_chickin
                -- cast(mp.lat_long as varchar(max)),
                -- cast(mp.foto_kunjungan as varchar(max))
            order by
                -- rs.max_tgl_chickin desc,
                w.kode asc,
                mtr.nama asc
        ";
        $d_pp = $m_pp->hydrateRaw( $sql );

        $data = null;
        if ( $d_pp->count() > 0 ) {
            $data = $d_pp->toArray();
        }

        $content['isMobile'] = $this->isMobile;
        $content['data'] = $data;
        $html = $this->load->view('parameter/peternak/listMobile', $content, TRUE);

        echo $html;
    }

    public function detailMobile() {
        $params = $this->input->get('params');

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                mtr1.*,
                REPLACE(REPLACE(l_kec.nama, 'Kota ', ''), 'Kab ', '') as nama_kecamatan
            from mitra mtr1
            right join
                (select max(id) as id, nomor from mitra where nomor = '".$params['nomor']."' group by nomor) mtr2
                on
                    mtr1.id = mtr2.id
            left join
                lokasi l_kec
                on
                    mtr1.alamat_kecamatan = l_kec.id

        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;

        $data_mitra = null;
        $alamat_mitra = null;
        if ( $d_conf->count() > 0 ) {
            $data_mitra = $d_conf->toArray()[0];

            $jalan = empty($data_mitra['alamat_jalan']) ? '' : strtoupper('DSN.'.trim(str_replace('DSN.', '', $data_mitra['alamat_jalan'])));
            $rt = empty($data_mitra['alamat_rt']) ? '' : strtoupper(' RT.'.$data_mitra['alamat_rt']);
            $rw = empty($data_mitra['alamat_rw']) ? '' : strtoupper('/RW.'.$data_mitra['alamat_rw']);
            $kelurahan = empty($data_mitra['alamat_kelurahan']) ? '' : strtoupper(' ,'.$data_mitra['alamat_kelurahan']);
            $kecamatan = empty($data_mitra['nama_kecamatan']) ? '' : strtoupper(' ,'.$data_mitra['nama_kecamatan']);

            $alamat_mitra = $jalan.$rt.$rw.$kelurahan.$kecamatan;

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    *
                from telepon_mitra tm
                where
                    tm.mitra = ".$data_mitra['id']."
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
    
            $data_telp = null;
            if ( $d_conf->count() > 0 ) {
                $data_telp = $d_conf->toArray();
            }
    
    
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    k.kandang,
                    k.ekor_kapasitas as kapasitas,
                    mp.lat_long as posisi,
                    mp.foto_kunjungan as foto,
                    rs.max_tgl_chickin
                from mitra_mapping mm
                left join
                    kandang k
                    on
                        mm.id = k.mitra_mapping
                left join
                    (
                        select mp1.* from mitra_posisi mp1
                        right join
                            (select max(id) as id, nomor, kandang from mitra_posisi group by nomor, kandang) mp2
                            on
                                mp1.id = mp2.id
                    ) mp
                    on
                        mm.nomor = mp.nomor and
                        k.kandang = mp.kandang
                left join
                (
                    select max(data.datang) as max_tgl_chickin, data.nim, data.no_kandang
                    from
                    (
                        select rs.noreg, rs.nim, td.datang, k.kandang as no_kandang from rdim_submit rs
                        left join
                            (
                                select od1.* from order_doc od1
                                right join
                                    (select max(id) as id, no_order from order_doc group by no_order) od2
                                    on
                                        od1.id = od2.id
                            ) od
                            on
                                od.noreg = rs.noreg
                        left join
                            (
                                select td1.* from terima_doc td1
                                right join
                                    (select max(id) as id, no_order from terima_doc group by no_order) td2
                                    on
                                        td1.id = td2.id
                            ) td
                            on
                                td.no_order = od.no_order
                        left join
                            kandang k
                            on
                                k.id = rs.kandang
                        where
                            td.id is not null
                    ) data
                    group by
                        data.nim,
                        data.no_kandang
                ) rs
                on
                    rs.nim = mm.nim and
                    rs.no_kandang = k.kandang
                where
                    mm.mitra = ".$data_mitra['id']."
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
    
            $data_kdg = null;
            if ( $d_conf->count() > 0 ) {
                $data_kdg = $d_conf->toArray();
            }
    
            $data = array(
                'nama' => $data_mitra['nama'],
                'alamat' => $alamat_mitra,
                'telpon' => $data_telp,
                'kandang' => $data_kdg
            );
    
        }
        
        // cetak_r( $data, 1 );

        $content['data'] = $data;
        $html = $this->load->view('parameter/peternak/detailMobile', $content, TRUE);

        echo $html;
    }

    public function load_form()
    {
        $id = $this->input->get('id');
        $resubmit = $this->input->get('resubmit');
        $html = '';

        if ( !empty($id) ) {
            if ( !empty($resubmit) ) {
                /* NOTE : untuk edit */
                $html = $this->edit_form($id, $resubmit);
            } else {
                /* NOTE : untuk view */
                $html = $this->view_form($id, $resubmit);
            }
        } else {
            /* NOTE : untuk add */
            $html = $this->add_form();
        }

        echo $html;
    }

    public function amount_of_data()
    {
        $search_by = $this->input->post('search_by');
        $search_val = $this->input->post('search_val');

        $m_mitra = new \Model\Storage\Mitra_model();
        $d_nomor = null;
        if ( !empty($search_by) && !empty($search_val) ) {
            if ( stristr($search_by, 'nama') !== FALSE ) {
                $d_nomor = $m_mitra->select('nomor', 'nama')->distinct('nomor')->where('nama', 'like', '%'.$search_val.'%')->where('mstatus', 1)->orderBy('nama', 'asc')->get()->toArray();
            } else if ( stristr($search_by, 'unit') !== FALSE ) {
                $m_wilayah = new \Model\Storage\Wilayah_model();
                $d_wilayah = $m_wilayah->select('id')->where('kode', 'like', '%'.$search_val.'%')->get();

                if ( $d_wilayah->count() > 0 ) {
                    $d_wilayah = $d_wilayah->toArray();
                    $m_kdg = new \Model\Storage\Kandang_model();
                    $d_kdg = $m_kdg->select('mitra_mapping')->whereIn('unit', $d_wilayah)->get();
                    if ( $d_kdg->count() > 0 ) {
                        $d_kdg = $d_kdg->toArray();
                        $m_mm = new \Model\Storage\MitraMapping_model();
                        $d_mm = $m_mm->select('mitra')->whereIn('id', $d_kdg)->get()->toArray();

                        $d_nomor = $m_mitra->select('nomor', 'nama')->distinct('nomor')->whereIn('id', $d_mm)->orderBy('nama', 'asc')->get()->toArray();
                    }
                }
            }
        } else {
            $d_nomor = $m_mitra->select('nomor', 'nama')->distinct('nomor')->where('mstatus', 1)->orderBy('nama', 'asc')->get()->toArray();
        }

        $list_nomor = array();
        $jml_row = 25;
        $jml_page = 0;
        $idx_row = 0;
        if ( !empty($d_nomor) ) {
            foreach ($d_nomor as $k_nomor => $v_nomor) {
                if ( $idx_row == $jml_row ) {
                    $idx_row = 0;
                    $jml_page++;
                }

                $list_nomor[$jml_page][$idx_row] = $v_nomor['nomor'];

                $idx_row++;
            }
        }

        $this->result['content'] = array(
            'jml_row' => $jml_row,
            'jml_page' => count($list_nomor),
            'list' => $list_nomor
        );                     

        display_json( $this->result );
    }

    public function list_sk()
    {   
        $list_nomor = $this->input->get('params');

        $akses = hakAkses($this->url);

        $content['list_mitra'] = $this->getListIndexMitra($list_nomor);
        $content['akses'] = $akses;
        $content['resubmit'] = null;
        $html = $this->load->view('parameter/peternak/list', $content);
        
        echo $html;
    }

    public function add_form()
    {
        $akses = hakAkses($this->url);

        $content['title_panel'] = 'Pengajuan Data Mitra';
        $content['tipe_lokasi'] = $this->getTipeLokasi();
        $content['tipe_kandang'] = $this->getTipeKandang();
        $content['status_kandang'] = $this->status_kandang;
        $content['jenis_mitra'] = $this->getJenisMitra();
        $content['list_provinsi'] = $this->getLokasi('PV');
        $content['list_perwakilan'] = $this->getListPerwakilan();
        $content['list_lampiran_mitra'] = $this->getListLampiran('MITRA');
        $content['list_lampiran_kandang'] = $this->getListLampiran('KANDANG');
        $content['list_lampiran_jaminan'] = $this->getListLampiran('MITRA_JAMINAN');
        $content['perusahaan'] = $this->getPerusahaan();

        $content['akses'] = $akses;
        $content['resubmit'] = null;
        $content['data'] = null;
        $html = $this->load->view('parameter/peternak/add_form', $content, true);
        
        return $html;
    }

    public function view_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        $data_mitra = $this->getDataMitra($id);

        $content['tipe_lokasi'] = $this->getTipeLokasi();
        $content['tipe_kandang'] = $this->getTipeKandang();
        $content['jenis_mitra'] = $this->getJenisMitra();
        $content['status_kandang'] = $this->status_kandang;
        $content['mitra'] = $data_mitra['data'];
        $content['mitra_posisi'] = $data_mitra['posisi'];
        $content['list_provinsi'] = $this->getLokasi('PV');
        $content['list_perwakilan'] = $this->getListPerwakilan();
        $content['list_lampiran_mitra'] = $this->getListLampiran('MITRA');
        $content['list_lampiran_kandang'] = $this->getListLampiran('KANDANG');
        $content['list_lampiran_jaminan'] = $this->getListLampiran('MITRA_JAMINAN');
        $content['perusahaan'] = $this->getPerusahaan();

        $content['akses'] = $akses;
        $content['data'] = 'VIEW FORM';
        $content['resubmit'] = $resubmit;
        $html = $this->load->view('parameter/peternak/view_form', $content);
        
        return $html;
    }

    public function edit_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        $data_mitra = $this->getDataMitra($id);

        $content['mitra'] = $data_mitra['data'];
        $content['title_panel'] = 'Pengajuan Data Mitra';
        $content['tipe_lokasi'] = $this->getTipeLokasi();
        $content['tipe_kandang'] = $this->getTipeKandang();
        $content['status_kandang'] = $this->status_kandang;
        $content['jenis_mitra'] = $this->getJenisMitra();
        $content['list_provinsi'] = $this->getLokasi('PV');
        $content['list_perwakilan'] = $this->getListPerwakilan();
        $content['list_lampiran_mitra'] = $this->getListLampiran('MITRA');
        $content['list_lampiran_kandang'] = $this->getListLampiran('KANDANG');
        $content['list_lampiran_jaminan'] = $this->getListLampiran('MITRA_JAMINAN');
        $content['perusahaan'] = $this->getPerusahaan();

        $content['akses'] = $akses;
        $content['data'] = 'EDIT FORM';
        $content['resubmit'] = $resubmit;
        $html = $this->load->view('parameter/peternak/edit_form', $content);
        
        return $html;
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

    public function getListIndexMitra($list_nomor)
    {
        $m_mitra = new \Model\Storage\Mitra_model();

        $data = array();
        if ( !empty($list_nomor) && $list_nomor != 'undefined' ) {
            foreach ($list_nomor as $nomor) {
                $mitra = $m_mitra->where('nomor', $nomor)
                                 ->orderBy('version', 'desc')
                                 ->orderBy('id', 'desc')
                                 ->first()->toArray();

                if ( $mitra['mstatus'] == 1 ) {
                    $ket = [];
                    $keterangan = '';

                    $m_lt = new \Model\Storage\LogTables_model();
                    $d_lt = $m_lt->select('deskripsi', 'waktu')->where('tbl_name', 'mitra')->where('tbl_id', $mitra['id'])->get()->toArray();

                    foreach ($d_lt as $log){
                        $keterangan = $log['deskripsi'] . ' pada ' . dateTimeFormat($log['waktu']);
                        array_push($ket, $keterangan);
                    }
        			
        			$_unit = null;
                    $m_mm = new \Model\Storage\MitraMapping_model();
                    $d_mm = $m_mm->where('mitra', $mitra['id'])->get()->toArray();
        			foreach ($d_mm as $v_mm) {
                        $m_kdg = new \Model\Storage\Kandang_model();
                        $d_kdg = $m_kdg->where('mitra_mapping', $v_mm['id'])->with(['d_unit'])->get()->toArray();
        				foreach ( $d_kdg as $v_kdg ) {
        					$_unit[ $v_kdg['d_unit']['kode'] ] = $v_kdg['d_unit']['kode'];
        				}
        			}
        			$unit = !empty($_unit) ? implode(', ', $_unit) : '-';

                    $key = $mitra['nama'].'|'.$mitra['nomor'];
                    $data[ $key ] = array(
                        'id' => $mitra['id'],
                        'nomor' => $mitra['nomor'],
                        'ktp' => $mitra['ktp'],
                        'nama' => $mitra['nama'],
                        'alamat' => $mitra['alamat_kelurahan'] . ', ' . $mitra['alamat_jalan'] . ', RT/RW : '. $mitra['alamat_rt'] . '/' . $mitra['alamat_rw'],
                        'status' => $mitra['status'],
                        'keterangan' => $keterangan,
        				'unit' => $unit
                    );

                    // ksort($data);
                }
            }
        }

        return $data;
    }

    public function add()
    {
        $this->set_title( 'Master Mitra' );
        $this->add_external_js(array(
            'assets/jquery/easy-autocomplete/jquery.easy-autocomplete.min.js',
            'assets/jquery/maskedinput/jquery.maskedinput.min.js',
            'assets/master/mitra.js',
        ));
        $this->add_external_css(array(
            'assets/jquery/easy-autocomplete/easy-autocomplete.min.css',
            'assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css',
            'assets/master/css/mitra.css',
        ));
        $data = $this->includes;

        $order = $this->input->get('ord');
        $content['title_panel'] = 'Pengajuan Data Mitra';
        $content['tipe_lokasi'] = $this->getTipeLokasi();
        $content['tipe_kandang'] = $this->getTipeKandang();
        $content['status_kandang'] = $this->status_kandang;
        $content['jenis_mitra'] = $this->getJenisMitra();
        $content['list_provinsi'] = $this->getLokasi('PV');
        $content['list_perwakilan'] = $this->getListPerwakilan();
        $content['list_lampiran_mitra'] = $this->getListLampiran('MITRA');
        $content['list_lampiran_kandang'] = $this->getListLampiran('KANDANG');
        $content['list_lampiran_jaminan'] = $this->getListLampiran('MITRA_JAMINAN');
        $content['current_uri'] = $this->current_uri;
        // load views
        $data['content'] = $this->load->view($this->pathView . 'form_pengajuan_data_mitra', $content, TRUE);
        $this->load->view($this->template, $data);
    }

    public function view_mitra($mitra_id)
    {
        $this->set_title( 'Master Mitra' );
        $this->add_external_js(array('assets/master/mitra.js'));
        $this->add_external_css(array('assets/master/css/mitra.css'));
        $data = $this->includes;

        $order = $this->input->get('ord');
        $data_mitra = $this->getDataMitra($id);

        $content['title_panel'] = 'Master Mitra';
        $content['current_uri'] = $this->current_uri;
        $content['list_status'] = array('all', 'submit', 'ack','approve');
        $content['tipe_lokasi'] = $this->getTipeLokasi();
        $content['tipe_kandang'] = $this->getTipeKandang();
        $content['jenis_mitra'] = $this->getJenisMitra();
        $content['status_kandang'] = $this->status_kandang;
        $content['mitra'] = $data_mitra['data'];
        $content['list_provinsi'] = $this->getLokasi('PV');
        $content['list_perwakilan'] = $this->getListPerwakilan();
        $content['list_lampiran_mitra'] = $this->getListLampiran('MITRA');
        $content['list_lampiran_kandang'] = $this->getListLampiran('KANDANG');
        $content['list_lampiran_jaminan'] = $this->getListLampiran('MITRA_JAMINAN');

        $data['content'] = $this->load->view($this->pathView . 'view_data_mitra', $content, TRUE);
        $this->load->view($this->template, $data);

    }

    public function update_mitra($mitra_id)
    {
        $this->add_external_js(array(
            'assets/jquery/easy-autocomplete/jquery.easy-autocomplete.min.js',
            'assets/jquery/maskedinput/jquery.maskedinput.min.js',
            'assets/master/mitra.js',
        ));
        $this->add_external_css(array(
            'assets/jquery/easy-autocomplete/easy-autocomplete.min.css',
            'assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css',
            'assets/master/css/mitra.css',
        ));
        $data = $this->includes;

        $order = $this->input->get('ord');
        $data_mitra = $this->getDataMitra($id);

        $content['title_panel'] = 'Master Mitra';
        $content['current_uri'] = $this->current_uri;
        $content['list_status'] = array('all', 'submit', 'ack','approve');
        $content['tipe_lokasi'] = $this->getTipeLokasi();
        $content['tipe_kandang'] = $this->getTipeKandang();
        $content['jenis_mitra'] = $this->getJenisMitra();
        $content['status_kandang'] = $this->status_kandang;
        $content['mitra'] = $data_mitra['data'];
        $content['list_provinsi'] = $this->getLokasi('PV');
        $content['list_perwakilan'] = $this->getListPerwakilan();
        $content['list_lampiran_mitra'] = $this->getListLampiran('MITRA');
        $content['list_lampiran_kandang'] = $this->getListLampiran('KANDANG');
        $content['list_lampiran_jaminan'] = $this->getListLampiran('MITRA_JAMINAN');

        $data['content'] = $this->load->view($this->pathView . 'update_data_mitra', $content, TRUE);
        $this->load->view($this->template, $data);

    }

    public function getDataMitra($id)
    {
        $data = null;

        $m_mitra = new \Model\Storage\Mitra_model();
        $d_mitra = $m_mitra->with(['telepons', 'lampirans', 'lampirans_jaminan', 'dKecamatan', 'perwakilans', 'logs', 'posisi'])->find($id);

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select mp1.* from mitra_posisi mp1
            right join
                (select max(id) as id, nomor, kandang from mitra_posisi group by nomor, kandang) mp2
                on
                    mp1.id = mp2.id
            where
                mp1.nomor = '".$d_mitra->nomor."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $posisi = null;
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $key = $value['nomor'].'-'.$value['kandang'];

                $posisi[ $key ] = $value;
            }
        }

        $data = array(
            'data' => $d_mitra,
            'posisi' => $posisi
        );

        return $data;
    }

    public function getTipeLokasi()
    {
        $tipe_lokasi = $this->config->item('tipe_lokasi');
        $data = array();
        foreach ($tipe_lokasi as $key => $val) {
            if ( in_array($key, ['KB', 'KT']) ) {
                $data[$key] = $val;
            }
        }
        return $data;
    }

    public function getTipeKandang()
    {
        $tipe_kandang = $this->config->item('tipe_kandang');
        return $tipe_kandang;
    }

    public function getJenisMitra()
    {
        $jenis_mitra = $this->config->item('jenis_mitra');
        return $jenis_mitra;
    }

    public function getListLampiran($jenis)
    {
        $m_lampiran = new \Model\Storage\NamaLampiran_model();
        $d_lampiran = $m_lampiran->where('jenis', $jenis)->get();
        return $d_lampiran;
    }

    public function getListPerwakilan()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'PW')->orderBy('nama', 'ASC')->get();
        return $d_wilayah;
    }

    public function getListUnit()
    {
        $induk = $this->input->get('induk');
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah ->where('jenis', 'UN')
                                ->where('induk', $induk)
                                ->orderBy('nama', 'ASC')->get();
        $data = ($d_wilayah) ? $d_wilayah->toArray() : [];
        display_json( $data );
    }

    public function getLokasi($jenis, $induk = null)
    {
        $m_lokasi = new \Model\Storage\Lokasi_model();
        if ($induk == null) {
            $d_lokasi = $m_lokasi ->where('jenis', $jenis)->orderBy('nama', 'ASC')->get();
        }else{
            $d_lokasi = $m_lokasi ->where('jenis', $jenis)->where('induk', $induk)->orderBy('nama', 'ASC')->get();
        }
        return $d_lokasi;
    }

    public function getLokasiJson()
    {
        $jenis = $this->input->get('jenis');
        $induk = $this->input->get('induk');

        $result = $this->getLokasi($jenis, $induk);
        $this->result['content'] = $result;
        $this->result['status'] = 1;
        display_json($this->result);
    }

    public function autocomplete_lokasi()
    {
        $term = $this->input->get('term');
        $jenis = $this->input->get('tipe_lokasi');
        $induk = $this->input->get('induk');
        $data = array();
        $m_wilayah = new \Model\Storage\Lokasi_model();
        if (empty($induk)) {
            $d_wilayah = $m_wilayah ->where('jenis', $jenis)
                                    ->where('nama', 'LIKE', "%{$term}%")
                                    ->orderBy('nama', 'ASC')->get();
        }else{
            $d_wilayah = $m_wilayah ->where('jenis', $jenis)
                                    ->where('nama', 'LIKE', "%{$term}%")
                                    ->where('induk', $induk)
                                    ->orderBy('nama', 'ASC')->get();
        }

        foreach ($d_wilayah as $key => $val) {
            $data[] = array(
                'label'=>$val['nama'],
                'value'=>$val['nama'],
                'id' => $val['id']
            );
        }

        if (empty($data)) {
            $data = array(
                'label'=>"not found",
                'value'=>"",
                'id' => ""
            );
        }

        display_json($data);
    }

    public function save()
    {
        $params = $this->input->post('params');

        // NOTE: 0. prepare
        $model = new \Model\Storage\Conf();
        $now = $model->getDate();

        try {
            $status = 'submit';
            // NOTE: 1. simpan mitra
            $m_mitra = new \Model\Storage\Mitra_model();
            $id_mitra = $m_mitra->getNextIdentity();
            $nomor_mitra = $m_mitra->getNextNomor();

            $m_mitra->id = $id_mitra;
            $m_mitra->nomor = $nomor_mitra;
            $m_mitra->nama = $params['nama'];
            $m_mitra->ktp = $params['ktp'];
            $m_mitra->npwp = $params['npwp'];
            $m_mitra->skb = $params['skb'] ?: null;
            $m_mitra->tgl_habis_skb = $params['tgl_habis_skb'] ?: null;
            $m_mitra->alamat_kecamatan = $params['alamat']['kecamatan'];
            $m_mitra->alamat_kelurahan = $params['alamat']['kelurahan'];
            $m_mitra->alamat_rt = $params['alamat']['rt'] ?: null;
            $m_mitra->alamat_rw = $params['alamat']['rw'] ?: null;
            $m_mitra->alamat_jalan = $params['alamat']['alamat'] ?: null;
            $m_mitra->bank = $params['d_bank']['bank'] ?: null;
            $m_mitra->rekening_cabang_bank = $params['d_bank']['cabang'] ?: null;
            $m_mitra->rekening_nomor = $params['d_bank']['rekening'] ?: null;
            $m_mitra->rekening_pemilik = $params['d_bank']['pemilik'] ?: null;
            $m_mitra->status = $status;
            $m_mitra->keterangan_jaminan = $params['keterangan_jaminan'];
            $m_mitra->jenis = $params['jenis_mitra'];
            $m_mitra->mstatus = 1;
            $m_mitra->version = 1;
            $m_mitra->perusahaan = $params['perusahaan'];
            $m_mitra->save();

            $telepons = $params['telepons'];
            foreach ($telepons as $k => $telepon) {
                $m_telp = new \Model\Storage\TeleponMitra_model();
                $m_telp->id = $m_telp->getNextIdentity();
                $m_telp->mitra = $id_mitra;
                $m_telp->nomor = $telepon;
                $m_telp->save();
            }

            // NOTE: 2. simpan perwakilan + kandang
            $perwakilans = $params['d_perwakilans'];
            foreach ($perwakilans as $perwakilan) {
                $m_mitra_mapping = new \Model\Storage\MitraMapping_model();
                $nim = $m_mitra_mapping->getNextNim();
                $mitra_mapping_id = $m_mitra_mapping->getNextIdentity();
                $m_mitra_mapping->id = $mitra_mapping_id;
                $m_mitra_mapping->mitra = $id_mitra;
                $m_mitra_mapping->perwakilan = $perwakilan['perwakilan_id'];
                // $m_mitra_mapping->nim = $perwakilan['nim'];
                $m_mitra_mapping->nim = $nim;
                $m_mitra_mapping->nomor = $nomor_mitra;
                $m_mitra_mapping->save();

                // NOTE: 2.1 simpan kandang
                $kandangs = $perwakilan['d_kandangs'];
                foreach ($kandangs as $kandang) {
                    $m_kandang = new \Model\Storage\Kandang_model();
                    $kandang_id = $m_kandang->getNextIdentity();
                    $m_kandang->id = $kandang_id;
                    $m_kandang->mitra_mapping = $mitra_mapping_id;
                    $m_kandang->kandang = $kandang['no'];
                    $m_kandang->unit = $kandang['unit'];
                    $m_kandang->tipe = $kandang['tipe'];
                    $m_kandang->ekor_kapasitas = $kandang['kapasitas'];
                    $m_kandang->alamat_kecamatan = $kandang['alamat']['kecamatan'];
                    $m_kandang->alamat_kelurahan = $kandang['alamat']['kelurahan'];
                    $m_kandang->alamat_rt = $kandang['alamat']['rt'];
                    $m_kandang->alamat_rw = $kandang['alamat']['rw'];
                    $m_kandang->alamat_jalan = $kandang['alamat']['alamat'];
                    $m_kandang->ongkos_angkut = $kandang['ongkos_angkut'];
                    $m_kandang->grup = $kandang['grup'];
                    $m_kandang->status = $kandang['status'];
                    $m_kandang->save();

                    $bangunans = $kandang['bangunans'];
                    foreach ($bangunans as $bangunan) {
                        $m_bangunan_kandang = new \Model\Storage\BangunanKandang_model();
                        $m_bangunan_kandang->id = $m_bangunan_kandang->getNextIdentity();
                        $m_bangunan_kandang->kandang = $kandang_id;
                        $m_bangunan_kandang->bangunan = $bangunan['no'];
                        $m_bangunan_kandang->meter_panjang = $bangunan['panjang'];
                        $m_bangunan_kandang->meter_lebar = $bangunan['lebar'];
                        $m_bangunan_kandang->jumlah_unit = $bangunan['jml_unit'];
                        $m_bangunan_kandang->save();
                    }
                }
            }

            $d_mitra = $m_mitra->where('id', $id_mitra)->with(['telepons', 'perwakilans'])->first();

            $deskripsi_log_mitra = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_mitra, $deskripsi_log_mitra );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data peternak sukses di-simpan';
            $this->result['content'] = array('id' => $id_mitra);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function save_after_approve()
    {
        $params = $this->input->post('params');

        try{
            // NOTE: 0. prepare
            $model = new \Model\Storage\Conf();
            $now = $model->getDate();
            
            // NOTE: 1. update mitra
            $m_mitra = new \Model\Storage\Mitra_model();
            $id_mitra_old = $params['id_mitra'];
            $d_mitra = $m_mitra->where('id', $id_mitra_old)->first()->toArray();
            $nomor_mitra = $d_mitra['nomor'];

            // NOTE: 1. simpan mitra            
            $m_mitra = new \Model\Storage\Mitra_model();
            $id_mitra = $m_mitra->getNextIdentity();

            $m_mitra->id = $id_mitra;
            $m_mitra->nomor = $nomor_mitra;
            $m_mitra->nama = $params['nama'];
            $m_mitra->ktp = $params['ktp'];
            $m_mitra->npwp = $params['npwp'];
            $m_mitra->skb = $params['skb'] ? $params['skb'] : null;
            $m_mitra->tgl_habis_skb = $params['tgl_habis_skb'] ? $params['tgl_habis_skb'] : null;
            $m_mitra->alamat_kecamatan = $params['alamat']['kecamatan'];
            $m_mitra->alamat_kelurahan = $params['alamat']['kelurahan'];
            $m_mitra->alamat_rt = $params['alamat']['rt'] ? $params['alamat']['rt'] : 0;
            $m_mitra->alamat_rw = $params['alamat']['rw'] ? $params['alamat']['rw'] : 0;
            $m_mitra->alamat_jalan = $params['alamat']['alamat'] ? $params['alamat']['alamat'] : null;
            $m_mitra->bank = $params['d_bank']['bank'] ? $params['d_bank']['bank'] : null;
            $m_mitra->rekening_cabang_bank = $params['d_bank']['cabang'] ? $params['d_bank']['cabang'] : null;
            $m_mitra->rekening_nomor = $params['d_bank']['rekening'] ? $params['d_bank']['rekening'] : null;
            $m_mitra->rekening_pemilik = $params['d_bank']['pemilik'] ? $params['d_bank']['pemilik'] : null;
            $m_mitra->status = $d_mitra['status'];
            $m_mitra->keterangan_jaminan = $params['keterangan_jaminan'];
            $m_mitra->jenis = $params['jenis_mitra'];
            $m_mitra->mstatus = $d_mitra['mstatus'];
            $m_mitra->version = ((int) $d_mitra['version']) + 1;
            $m_mitra->perusahaan = $params['perusahaan'];
            $m_mitra->save();

            // NOTE : update telepon mitra
            $telepons = $params['telepons'];
            foreach ($telepons as $k => $telepon) {
                $m_telp = new \Model\Storage\TeleponMitra_model();
                $m_telp->id = $m_telp->getNextIdentity();
                $m_telp->mitra = $id_mitra;
                $m_telp->nomor = $telepon;
                $m_telp->save();
            }

            // NOTE: save perwakilan + kandang
            $perwakilans = $params['d_perwakilans'];
            foreach ($perwakilans as $perwakilan) {

                // NOTE: save mitra_mapping
                $m_mitra_mapping = new \Model\Storage\MitraMapping_model();
                $mitra_mapping_id = $m_mitra_mapping->getNextIdentity();
                $m_mitra_mapping->id = $mitra_mapping_id;
                $m_mitra_mapping->mitra = $id_mitra;
                $m_mitra_mapping->perwakilan = $perwakilan['perwakilan_id'];
                $m_mitra_mapping->nim = $perwakilan['nim'];
                $m_mitra_mapping->nomor = $nomor_mitra;
                $m_mitra_mapping->save();

                $kandangs = $perwakilan['d_kandangs'];
                foreach ($kandangs as $kandang) {

                    $kandang_id_old = null;
                    if ( !empty($kandang['id_kdg']) ) {
                        $kandang_id_old = $kandang['id_kdg'];
                    }

                    // NOTE: simpan kandang
                    $m_kandang = new \Model\Storage\Kandang_model();
                    $kandang_id = $m_kandang->getNextIdentity();
                    $m_kandang->id = $kandang_id;
                    $m_kandang->mitra_mapping = $mitra_mapping_id;
                    $m_kandang->kandang = $kandang['no'];
                    $m_kandang->unit = $kandang['unit'];
                    $m_kandang->tipe = $kandang['tipe'];
                    $m_kandang->ekor_kapasitas = $kandang['kapasitas'];
                    $m_kandang->alamat_kecamatan = $kandang['alamat']['kecamatan'];
                    $m_kandang->alamat_kelurahan = $kandang['alamat']['kelurahan'];
                    $m_kandang->alamat_rt = $kandang['alamat']['rt'];
                    $m_kandang->alamat_rw = $kandang['alamat']['rw'];
                    $m_kandang->alamat_jalan = $kandang['alamat']['alamat'];
                    $m_kandang->ongkos_angkut = $kandang['ongkos_angkut'];
                    $m_kandang->grup = $kandang['grup'];
                    $m_kandang->status = $kandang['status'];
                    $m_kandang->save();

                    $bangunans = $kandang['bangunans'];
                    foreach ($bangunans as $bangunan) {
                        $m_bangunan_kandang = new \Model\Storage\BangunanKandang_model();
                        $m_bangunan_kandang->id = $m_bangunan_kandang->getNextIdentity();
                        $m_bangunan_kandang->kandang = $kandang_id;
                        $m_bangunan_kandang->bangunan = $bangunan['no'];
                        $m_bangunan_kandang->meter_panjang = $bangunan['panjang'];
                        $m_bangunan_kandang->meter_lebar = $bangunan['lebar'];
                        $m_bangunan_kandang->jumlah_unit = $bangunan['jml_unit'];
                        $m_bangunan_kandang->save();
                    }
                }
            }

            $d_mitra = $m_mitra->where('id', $id_mitra)->with(['telepons', 'perwakilans'])->first();

            $deskripsi_log_mitra = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_mitra, $deskripsi_log_mitra );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data peternak sukses di-update';
            $this->result['content'] = array('id' => $id_mitra);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function uploadFile() {
		$params = json_decode($this->input->post('data'),TRUE);
		$files = isset($_FILES['files']) ? $_FILES['files'] : [];

		try {
			$id = $params['id'];
			$idx_upload = $params['idx_upload'];
			if ( isset($params['lampirans'][ $idx_upload ]) ) {
				$lampiran = $params['lampirans'][ $idx_upload ];
				$id_lampiran_old = isset($lampiran['old']) ? $lampiran['old'] : null;

				$table = 'mitra';
				$table_id = $id;
				if ( count(explode('_', $lampiran['key'])) > 1 ) {
                    $split_key = explode('_', $lampiran['key']);
					$perwakilan_id = $split_key[0];
					$no_kandang = $split_key[1];

					$m_conf = new \Model\Storage\Conf();
                    $sql = "
                        select k.* from kandang k
                        right join
                            mitra_mapping mm
                            on
                                k.mitra_mapping = mm.id
                        right join
                            mitra m
                            on
                                m.id = mm.mitra
                        where
                            m.id = ".$id." and
                            mm.perwakilan = ".$perwakilan_id." and
                            k.kandang = '".$no_kandang."'
                    ";
					$d_conf = $m_conf->hydrateRaw( $sql );

                    if ( $d_conf->count() > 0 ) {
                        $d_conf = $d_conf->toArray()[0];

                        $table = "kandang";
                        $table_id = $d_conf['id'];
                    }
				}

				$file_name = $path_name = null;
				$isMoved = 0;
				if (!empty($files)) {
					$mappingFiles = mappingFiles($files);

					$file = null;
					if ( isset($lampiran['sha1']) && !empty($lampiran['sha1']) ) {
						$file = $mappingFiles[ $lampiran['sha1'] . '_' . $lampiran['name'] ] ?: '';
					}

					if ( !empty($file) ) {
						$moved = uploadFile($file);
						$isMoved = $moved['status'];

						if ($isMoved) {
							$file_name = $moved['name'];
							$path_name = $moved['path'];

							$m_lampiran = new \Model\Storage\Lampiran_model();
							$m_lampiran->tabel = $table;
							$m_lampiran->tabel_id = $table_id;
							$m_lampiran->nama_lampiran = isset($lampiran['id']) ? $lampiran['id'] : null;
							$m_lampiran->filename = $file_name;
							$m_lampiran->path = $path_name;
							$m_lampiran->status = 1;
							$m_lampiran->save();

							$deskripsi_log = 'di-upload oleh ' . $this->userdata['detail_user']['nama_detuser'];
							Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log );
						} else {
							display_json(['status'=>0, 'message'=>'error, segera hubungi tim IT', 'cek' => 2]);
						}
					} else {
						$m_lampiran = new \Model\Storage\Lampiran_model();
						$d_lampiran_old = $m_lampiran->where('id', $id_lampiran_old)->first();

						if ( $d_lampiran_old ) {
							$m_lampiran = new \Model\Storage\Lampiran_model();
							$m_lampiran->tabel = $d_lampiran_old['tabel'];
							$m_lampiran->tabel_id = $table_id;
							$m_lampiran->nama_lampiran = $d_lampiran_old['nama_lampiran'];
							$m_lampiran->filename = $d_lampiran_old['filename'];
							$m_lampiran->path = $d_lampiran_old['path'];
							$m_lampiran->status = $d_lampiran_old['status'];
							$m_lampiran->save();
						}
					}
				} else {
					$m_lampiran = new \Model\Storage\Lampiran_model();
					$d_lampiran_old = $m_lampiran->where('id', $id_lampiran_old)->first();

					if ( $d_lampiran_old ) {
						$m_lampiran = new \Model\Storage\Lampiran_model();
						$m_lampiran->tabel = $d_lampiran_old['tabel'];
						$m_lampiran->tabel_id = $table_id;
						$m_lampiran->nama_lampiran = $d_lampiran_old['nama_lampiran'];
						$m_lampiran->filename = $d_lampiran_old['filename'];
						$m_lampiran->path = $d_lampiran_old['path'];
						$m_lampiran->status = $d_lampiran_old['status'];
						$m_lampiran->save();
					}
				}
			}

			$this->result['status'] = 1;
			$this->result['message'] = 'Data peternak sukses disimpan';
			$this->result['content'] = array('id' => $id);
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

		display_json( $this->result );
	}

    public function delete()
    {
        $id_mitra = $this->input->post('params');

        try {
            $status = 'delete';

            $m_mitra = new \Model\Storage\Mitra_model();
            $d_mitra_by_id = $m_mitra->where('id', $id_mitra)->first();
            
            $m_mitra->where('nomor', $d_mitra_by_id->nomor)->update(
                array(
                    'status' => $status,
                    'mstatus' => 0
                )
            );

            $d_mitra = $m_mitra->where('id', $id_mitra)->with(['telepons', 'perwakilans'])->first();

            $deskripsi_log_mitra = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_mitra, $deskripsi_log_mitra );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data mitra sukses di-hapus';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function ackReject()
    {
        $action = $this->input->post('action'); // sebagai status

        try {
            $mitra_ids = $this->input->post('ids');

            $deskripsi_log = 'di-' . $action . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
            foreach ($mitra_ids as $id_mitra) {
                $m_mitra = new \Model\Storage\Mitra_model();
                $now = $m_mitra->getDate();

                $d_mitra = $m_mitra->find($id_mitra);
                $d_mitra->status = $action;
                $d_mitra->save();
                Modules::run( 'base/event/update', $d_mitra, $deskripsi_log );

                $this->result['status'] = 1;
                $this->result['action'] = $action;
                $this->result['message'] = 'Data mitra sukses di-' . $action;
                $this->result['content'] = array('id'=>$id_mitra);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function logLampiran($lampiran_id, $action = 'ditambahkan')
    {
        $m_loglampiran = new \Model\Storage\LogLampiran_model();
        $m_loglampiran->lampiran_id = $lampiran_id;
        $m_loglampiran->user_id = $this->userid;
        $m_loglampiran->deskripsi = $action . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
        $m_loglampiran->save();
    }


    public function rekening_koran($mitra_id)
    {
        $this->set_title( 'Rekening Koran - Mitra' );
        $this->add_external_js(array(
            'assets/master/mitra.js',
        ));
        $this->add_external_css(array(
            'assets/bootstrap-3.3.5/css/awesome-bootstrap-checkbox.css',
            'assets/master/css/mitra.css',
        ));
        $data = $this->includes;

        $order = $this->input->get('ord');
        $data_mitra = $this->getDataMitra($mitra_id);
        
        $content['title_panel'] = 'Rekening Koran';
        $content['current_uri'] = $this->current_uri;
        $content['mitra'] = $data_mitra['data'];
        $content['akuns'] = $this->getAkunRK();
        $content['filters'] = $this->config->item('jenis_trx_rekening_koran');
        // load views
        $data['content'] = $this->load->view($this->pathView . 'rekening_koran', $content, TRUE);
        $this->load->view($this->template, $data);
    }

    public function getAkunRK()
    {
        $m_akun  = new \Model\Storage\AkunRK_model();
        return $m_akun->get();
    }

    public function getDataRK()
    {
        $mitra_id = $this->input->get('mitra_id');
        $nim_id = $this->input->get('nim_id');
        $request_row = $this->input->get('row');
        $filter_trx = $this->input->get('kode');
        $m_mitra_mapping = new \Model\Storage\MitraMapping_model();
        $datas = array();
        $d_kandang = array();

        if ($nim_id == 'ALL') {
            $datas = $m_mitra_mapping->where('mitra',$mitra_id)->with(['kandangs'])->get();
        }else{
            $datas = $m_mitra_mapping->where('id', $nim_id)->with(['kandangs'])->get();

            // NOTE: get data kandang
            $m_kandang = new \Model\Storage\Kandang_model();
            $d_kandang = $m_kandang->select(['id', 'kandang'])->where('mitra_mapping', $nim_id)->get()->toArray();
        }

        $jut = $datas->sum('jut');
        $populasi = 0;
        $id_nims = array();
        foreach ($datas as $data) {
            $kandangs = $data->kandangs;
            $populasi += $kandangs->sum('ekor_kapasitas');
            $id_nims[] = $data->id;
        }

        $m_rk = new \Model\Storage\MitraRekeningKoran_model();
        if ($filter_trx == 'ALL') {
            $d_rk = $m_rk->whereIn('mitra_mapping', $id_nims)->where('status', '!=', 'delete');
        }else{
            $d_rk = $m_rk->whereIn('mitra_mapping', $id_nims)->where('status', '!=', 'delete')->where('kode_akun', 'LIKE', $filter_trx . '%');
        }

        $content['total'] = array(
            'count_trx' => $d_rk->count(),
            'jml_kredit' => $d_rk->sum('kredit'),
            'jml_debet' => $d_rk->sum('debet'),
        );
        $content['datas'] = $d_rk->with(['lampiran','kandang'])->orderBy('id', 'ASC')->take($request_row)->get();
        $histories = $this->load->view($this->pathView . 'list_rekening_koran', $content, TRUE);

        $this->result['status'] = 1;
        $this->result['message'] = 'success';
        $this->result['content'] = array(
            'populasi' => $populasi,
            'jut' => abs($jut),
            'kandangs' => $d_kandang,
            'histories' => $histories
        );

        display_json($this->result);
    }

    public function saveRK()
    {
        $params = json_decode($this->input->post('params'),TRUE);
        $attach = isset($_FILES['attach']) ? $_FILES['attach'] : null;

        $nim_id = $params['nim_id'];
        $trx = strtoupper( $params['jenis_trx'] );

        if ($nim_id != 'ALL') {
            $kredit = $params['kredit'];
            $debet = $params['debet'];

            $m_rk = new \Model\Storage\MitraRekeningKoran_model();

            // NOTE: update status reject menjadi delete sebelum dilakukan penyimpanan RK baru supaya saldo acuan bukan bersumber dari data yang ditolak
            $m_rk->where('mitra_mapping', $nim_id)->whereStatus('reject')->update(['status'=>'delete']);

            // NOTE: ambil saldo terakhir dari nim yang bersangkutan
            $d_rk = $m_rk->where('mitra_mapping', $nim_id)->whereNotIn('status', ['delete', 'reject'])->orderBy('id', 'DESC')->first();
            $saldo = empty($d_rk) ? 0 : $d_rk->saldo;
            if ( $trx == 'DEBET' ) {
                $saldo += $debet;
            }else{
                $saldo -= $kredit;
            }

            $status = 'submit';
            $m_rk = new \Model\Storage\MitraRekeningKoran_model();
            $m_rk->mitra_mapping = $nim_id;
            $m_rk->tgl_buku = $params['tgl_buku'];
            $m_rk->kode_akun = $params['kode_akun'];
            $m_rk->bukti = $params['bukti'];
            $m_rk->phb = $params['phb'];
            $m_rk->nkk = $params['nkk'];
            $m_rk->siklus = $params['siklus'] ?: null;
            $m_rk->kandang_id = $params['kandang_id'] ?: null;
            $m_rk->keterangan = $params['keterangan'];
            $m_rk->debet = $debet;
            $m_rk->kredit = $kredit;
            $m_rk->saldo = $saldo;
            $m_rk->status = $status;
            $m_rk->save();

            $deskripsi_log = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_rk, $deskripsi_log);
            if (!empty($attach)) {
                Modules::run( 'base/lampiran/save', $m_rk, $attach);
            }

            $html = '<tr class="data">
                        <td class="tanggal">'.tglIndonesia($params['tgl_buku']).'</td>
                        <td class="akun_rk">'.$params['kode_akun'].'</td>
                        <td class="no-bukti">'.$params['bukti'].'</td>
                        <td class="no-phb">'.$params['phb'].'</td>
                        <td class="no-nkk">'.$params['nkk'].'</td>
                        <td class="keterangan">'.$params['keterangan'].'</td>
                        <td class="debet">'.angkaDecimal($debet).'</td>
                        <td class="kredit">'.angkaDecimal($kredit).'</td>
                        <td class="saldo">'.angkaDecimal($saldo).'</td>
                    </tr>';

            $this->result['status'] = 1;
            $this->result['message'] = 'success';
            $this->result['content'] = array(
                'html' => $html
            );

            display_json($this->result);
        }
    }

    public function rekening_koran_list_ack($_title = 'ACK')
    {
        if (hasAkses('master/mitra/rekening_koran')) {

        $this->set_title( 'Rekening Koran - Mitra' );
        $this->add_external_js(array(
            'assets/master/mitra.js',
        ));
        $this->add_external_css(array(
            'assets/bootstrap-3.3.5/css/awesome-bootstrap-checkbox.css',
            'assets/master/css/mitra.css',
        ));
        $data = $this->includes;

        $order = $this->input->get('ord');
        $content['title_panel'] = 'List Rekening Koran - ' . $_title;
        $content['current_uri'] = $this->current_uri;
        $datas = array();

        $pathView = '';
        if (hasAkses('master/mitra/rekening_koran/ack')){
            $datas = $this->getListRekeningKoranForAck();
            $pathView = $this->pathView . 'list_rekening_koran_for_ack';
        }
        else	// NOTE: tampilkan RK yang di reject
        if (hasAkses('master/mitra/rekening_koran/submit')){
            $datas = $this->getListRekeningKoranForAck('reject');
            $pathView = $this->pathView . 'list_rekening_koran_for_reject';
        }

        $content['datas'] = $datas;
        $data['content'] = $this->load->view($pathView, $content, TRUE);
        $this->load->view($this->template, $data);

        }else{
            showErrorAkses();
        }
    }

    public function getListRekeningKoranForAck( $status = 'submit')
    {
        $m_rk = new \Model\Storage\MitraRekeningKoran_model();
        $d_rk = $m_rk->where('status', $status)->orderBy('id', 'ASC')->with(['perwakilan','lampiran', 'kandang'])->get();
        // $d_rk = $m_rk->with(['perwakilan','lampiran'])->orderBy('id', 'ASC')->get();

        $datas = array();
        foreach ($d_rk as $rk) {
            $trx = array(
                'id' => $rk->id,
                'tanggal' => $rk->tgl_buku,
                'akun' => $rk->kode_akun,
                'bukti' => $rk->bukti,
                'phb' => $rk->phb,
                'nkk' => $rk->nkk,
                'siklus' => $rk->siklus,
                'kandang' => isset($rk->kandang) ? $rk->kandang->kandang : "-",
                'keterangan' => $rk->keterangan,
                'lampiran' => empty( $rk->lampiran) ? [] :  $rk->lampiran->toArray(),
                'debet' => $rk->debet,
                'kredit' => $rk->kredit,
                'saldo' => $rk->saldo,
            );

            $idx = $rk->perwakilan->dMitra->nama . '_|_' . $rk->mitra_mapping;
            if (! isset($datas[ $idx ])) {
                $datas[ $idx ] = array(
                    'mitra_id' => $rk->perwakilan->dMitra->id,
                    'nomor' => $rk->perwakilan->dMitra->nomor,
                    'mitra' => $rk->perwakilan->dMitra->nama,
                    'ktp' => $rk->perwakilan->dMitra->ktp,
                    'nim' => $rk->perwakilan->nim,
                    'trxs' => array()
                );
            }

            $datas[ $idx ]['trxs'][] = $trx;
        }

        ksort($datas);

        return $datas;
    }

    public function updateAckRK()
    {
        $params = $this->input->post('params');
        $nim_id = $params['nim_id'];
        $ack_ids = isset($params['ack_ids']) ? $params['ack_ids']: [];
        $reject_ids = isset($params['reject_ids']) ? $params['reject_ids']: [];
        $action = $params['action'];

        $m_rk = new \Model\Storage\MitraRekeningKoran_model();
        // NOTE: ACK
        foreach ($ack_ids as $ack_id) {
            $ack_rk = $m_rk->find($ack_id);
            $ack_rk->status = 'ack';
            $ack_rk->save();
            $event = Modules::run( 'base/event/update', $ack_rk, 'di-ack oleh ' . $this->userdata['Nama_User'] );
        }

        // NOTE: REJECT
        foreach ($reject_ids as $reject_id) {
            $reject_rk = $m_rk->find($reject_id);
            $reject_rk->status = 'reject';
            $reject_rk->save();
            $event = Modules::run( 'base/event/update', $reject_rk, 'di-reject oleh ' . $this->userdata['Nama_User'] );
        }

        if ($event) {
            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di-' . $action;
        }else{
            $this->result['message'] = 'Data gagal di-ack';
        }

        display_json($this->result);
    }

    public function saveJut()
    {
        $params = $this->input->post('params');
        $nim_id = $params['nim_id'];
        $jut = $params['jut'];

        $model = new \Model\Storage\MitraMapping_model();
        $data = $model->find($nim_id);
        $data->jut = $jut;
        $data->save();

        $event = Modules::run( 'base/event/update', $data, 'di-submit oleh ' . $this->userdata['Nama_User'] );
        if ($event) {
            $this->result['status'] = 1;
            $this->result['message'] = 'Data JUT berhasil di-submit';
        }else{
            $this->result['message'] = 'Data JUT gagal di-submit';
        }

        display_json($this->result);
    }

    public function rekening_koran_list_reject()
    {
        $this->rekening_koran_list_ack('Reject');
    }

    public function form_export_excel()
    {
        $html = $this->load->view('parameter/peternak/form_export_excel', null); 
        
        echo $html;
    }

    public function verifikasi_export_excel()
    {
        $params = $this->input->post('params');

        $username = $params['username'];
        $password = $params['password'];

        $admins = $this->config->item('auth_export_excel')['auth_peternak'];

        if ( stristr($username, $admins[0]['user']) !== FALSE && $password == $admins[0]['pin'] ) {
            $this->result['status'] = 1;
        } else {
            $this->result['message'] = 'Username dan Password yang anda masukkan tidak cocok.';
        }

        display_json($this->result);
    }

    public function export_excel()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                m.id,
                m.nomor,
                m.ktp,
                m.npwp,
                m.nama,
                k.alamat_jalan,
                k.alamat_rt,
                k.alamat_rw,
                k.alamat_kelurahan,
                l_kec.nama as kecamatan,
                l_kota.nama as kabupatan_kota,
                l_prov.nama as provinsi,
                k.kandang,
                k.ekor_kapasitas as kapasitas,
                w.kode as unit,
                m.status,
                tm.no_telp
            from kandang k
            left join
                mitra_mapping mm
                on
                    k.mitra_mapping = mm.id
            left join
                (
                    select m1.* from mitra m1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) m2
                        on
                            m1.id = m2.id
                ) m
                on  
                    m.id = mm.mitra
            left join
                (
                    select mitra, (
                        stuff((select ', ' + cast(nomor as varchar) 
                        from (select mitra, nomor from telepon_mitra group by mitra, nomor) t2
                        where tm.mitra = t2.mitra
                        for xml path(''), type).value('.', 'varchar(max)'), 1, 1, '')
                    ) as no_telp 
                    from telepon_mitra tm group by mitra
                ) tm
                on
                    tm.mitra = m.id
            left join
                wilayah w
                on
                    k.unit = w.id
            left join
                lokasi l_kec
                on
                    k.alamat_kecamatan = l_kec.id
            left join
                lokasi l_kota
                on
                    l_kec.induk = l_kota.id
            left join
                lokasi l_prov
                on
                    l_kota.induk = l_prov.id
            where
                m.id is not null and
                m.mstatus = 1
            order by
                w.kode asc,
                m.nama asc,
                k.kandang asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = array();
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $jalan = empty($value['alamat_jalan']) ? '' : strtoupper('DSN.'.trim(str_replace('DSN.', '', $value['alamat_jalan'])));
                $rt = empty($value['alamat_rt']) ? '' : strtoupper(' RT.'.$value['alamat_rt']);
                $rw = empty($value['alamat_rw']) ? '' : strtoupper('/RW.'.$value['alamat_rw']);
                $kelurahan = empty($value['alamat_kelurahan']) ? '' : strtoupper(' ,'.$value['alamat_kelurahan']);
                $kecamatan = empty($value['kecamatan']) ? '' : strtoupper(' ,'.$value['kecamatan']);
                $kabupaten = empty($value['kabupatan_kota']) ? '' : strtoupper(' ,'.$value['kabupatan_kota']);
                $provinsi = empty($value['provinsi']) ? '' : strtoupper(' ,'.$value['provinsi']);

                $alamat = $jalan.$rt.$rw.$kelurahan.$kecamatan.$kabupaten.$provinsi;

                $key = $value['unit'].'|'.$value['nama'].'|'.$value['kandang'].'|'.$value['nomor'];
                $data[ $key ] = array(
                    'id' => $value['id'],
                    'nomor' => $value['nomor'],
                    'ktp' => $value['ktp'],
                    'npwp' => $value['npwp'],
                    'nama' => $value['nama'],
                    'alamat' => $alamat,
                    'kdg' => $value['kandang'],
                    'unit' => $value['unit'],
                    'status' => $value['status'],
                    'no_telp' => $value['no_telp'],
                    'kapasitas' => $value['kapasitas']
                );

                ksort($data);
            }
        }

        $content['data'] = $data;
        $res_view_html = $this->load->view('parameter/peternak/export_excel', $content, true);

        $filename = 'export-peternak-'.str_replace('-', '', date('Y-m-d')).'.xls';

        header("Content-type: application/xls");
        header("Content-Disposition: attachment; filename=".$filename."");
        echo $res_view_html;
    }

    public function formPindahPerusahaan() {
        $params = $this->input->get('params');

        $id = $params['id'];

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.id,
                data.nomor,
                data.nama,
                data.kode_unit,
                data.kode_prs,
                data.nama_prs
            from
            (
                select 
                    m.id,
                    m.nomor,
                    m.nama,
                    k.kandang,
                    k.tipe,
                    k.ekor_kapasitas,
                    w.nama as unit,
                    w.kode as kode_unit,
                    m.perusahaan as kode_prs,
                    prs.perusahaan as nama_prs
                from (
                    select m1.* from mitra m1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) m2
                        on
                            m1.id = m2.id
                ) m
                left join
                    mitra_mapping mm 
                    on
                        m.id = mm.mitra 
                left join
                    kandang k 
                    on
                        k.mitra_mapping = mm.id
                left join
                    wilayah w 
                    on
                        k.unit = w.id
                left join
                    (
                        select prs1.* from perusahaan prs1
                        right join
                            (
                                select max(id) as id, kode from perusahaan group by kode
                            ) prs2
                            on
                                prs1.id = prs2.id
                    ) prs
                    on
                        prs.kode = m.perusahaan
            ) data
            where
                data.id = ".$id."
            group by
                data.id,
                data.nomor,
                data.nama,
                data.kode_unit,
                data.kode_prs,
                data.nama_prs
            order by
                data.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray()[0];
        }

        $content['perusahaan'] = $this->getPerusahaan();
        $content['data'] = $data;
        $html = $this->load->view('parameter/peternak/formPindahPerusahaan', $content, true);

        echo $html;
    }

    public function pindahPerusahaan() {
        $params = $this->input->post('params');

        $id = $params['id'];
        $perusahan_baru = $params['perusahan_baru'];

        // NOTE: 1. simpan mitra
        $m_mitra = new \Model\Storage\Mitra_model();
        $sql = "
            select * from mitra where id = ".$id."
        ";
        $d_mitra = $m_mitra->hydrateRaw( $sql );
        if ( $d_mitra->count() > 0 ) {
            try {
                $d_mitra = $d_mitra->toArray()[0];
                
                $status = 'submit';
                $id_mitra = $m_mitra->getNextIdentity();
                $nomor_mitra = $m_mitra->getNextNomor();

                $m_mitra = new \Model\Storage\Mitra_model();
                $m_mitra->id = $id_mitra;
                $m_mitra->nomor = $nomor_mitra;
                $m_mitra->nama = $d_mitra['nama'];
                $m_mitra->ktp = $d_mitra['ktp'];
                $m_mitra->npwp = $d_mitra['npwp'];
                $m_mitra->skb = $d_mitra['skb'];
                $m_mitra->tgl_habis_skb = $d_mitra['tgl_habis_skb'];
                $m_mitra->alamat_kecamatan = $d_mitra['alamat_kecamatan'];
                $m_mitra->alamat_kelurahan = $d_mitra['alamat_kelurahan'];
                $m_mitra->alamat_rt = $d_mitra['alamat_rt'];
                $m_mitra->alamat_rw = $d_mitra['alamat_rw'];
                $m_mitra->alamat_jalan = $d_mitra['alamat_jalan'];
                $m_mitra->bank = $d_mitra['bank'];
                $m_mitra->rekening_cabang_bank = $d_mitra['rekening_cabang_bank'];
                $m_mitra->rekening_nomor = $d_mitra['rekening_nomor'];
                $m_mitra->rekening_pemilik = $d_mitra['rekening_pemilik'];
                $m_mitra->status = $status;
                $m_mitra->keterangan_jaminan = $d_mitra['keterangan_jaminan'];
                $m_mitra->jenis = $d_mitra['jenis'];
                $m_mitra->mstatus = 1;
                $m_mitra->version = 1;
                $m_mitra->perusahaan = $perusahan_baru;
                $m_mitra->save();

                $m_telp = new \Model\Storage\TeleponMitra_model();
                $sql = "
                    select * from telepon_mitra where mitra = ".$id."
                ";
                $telepons = $m_mitra->hydrateRaw( $sql );
                if ( $telepons->count() > 0 ) {
                    $telepons = $telepons->toArray();
                    foreach ($telepons as $k => $telepon) {
                        $m_telp = new \Model\Storage\TeleponMitra_model();
                        $m_telp->id = $m_telp->getNextIdentity();
                        $m_telp->mitra = $id_mitra;
                        $m_telp->nomor = $telepon['nomor'];
                        $m_telp->save();
                    }
                }

                // NOTE: 2. simpan perwakilan + kandang
                $m_mm = new \Model\Storage\MitraMapping_model();
                $sql = "
                    select * from mitra_mapping where mitra = ".$id."
                ";
                $perwakilans = $m_mm->hydrateRaw( $sql );
                if ( $perwakilans->count() > 0 ) {
                    $perwakilans = $perwakilans->toArray();
                    foreach ($perwakilans as $k_pwk => $perwakilan) {
                        $m_mitra_mapping = new \Model\Storage\MitraMapping_model();
                        $nim = $m_mitra_mapping->getNextNim();
                        $mitra_mapping_id = $m_mitra_mapping->getNextIdentity();

                        $m_mitra_mapping->id = $mitra_mapping_id;
                        $m_mitra_mapping->mitra = $id_mitra;
                        $m_mitra_mapping->perwakilan = $perwakilan['perwakilan'];
                        $m_mitra_mapping->nim = $nim;
                        $m_mitra_mapping->nomor = $nomor_mitra;
                        $m_mitra_mapping->save();

                        $m_kandang = new \Model\Storage\Kandang_model();
                        $sql = "
                            select * from kandang where mitra_mapping = ".$perwakilan['id']."
                        ";
                        $kandangs = $m_mm->hydrateRaw( $sql );
                        if ( $kandangs->count() > 0 ) {
                            $kandangs = $kandangs->toArray();
                            foreach ($kandangs as $k_kdg => $kandang) {
                                $m_kandang = new \Model\Storage\Kandang_model();
                                $kandang_id = $m_kandang->getNextIdentity();

                                $m_kandang->id = $kandang_id;
                                $m_kandang->mitra_mapping = $mitra_mapping_id;
                                $m_kandang->kandang = $kandang['kandang'];
                                $m_kandang->unit = $kandang['unit'];
                                $m_kandang->tipe = $kandang['tipe'];
                                $m_kandang->ekor_kapasitas = $kandang['ekor_kapasitas'];
                                $m_kandang->alamat_kecamatan = $kandang['alamat_kecamatan'];
                                $m_kandang->alamat_kelurahan = $kandang['alamat_kelurahan'];
                                $m_kandang->alamat_rt = $kandang['alamat_rt'];
                                $m_kandang->alamat_rw = $kandang['alamat_rw'];
                                $m_kandang->alamat_jalan = $kandang['alamat_jalan'];
                                $m_kandang->ongkos_angkut = $kandang['ongkos_angkut'];
                                $m_kandang->grup = $kandang['grup'];
                                $m_kandang->status = $kandang['status'];
                                $m_kandang->save();

                                /** LAMPIRAN KANDANG */
                                $m_lkdg = new \Model\Storage\Lampiran_model();
                                $tbl_kdg = $m_kandang->getTable();
                                $d_lkdg = $m_lkdg->where('tabel', $tbl_kdg)->where('tabel_id', $kandang['id'])->get();
                                if ( $d_lkdg->count() > 0 ) {
                                    $d_lkdg = $d_lkdg->toArray();
                                    foreach ($d_lkdg as $k_lmitra => $v_lmitra) {
                                        $m_lkdg = new \Model\Storage\Lampiran_model();
                                        $m_lkdg->tabel = $tbl_kdg;
                                        $m_lkdg->tabel_id = $kandang_id;
                                        $m_lkdg->nama_lampiran = $v_lmitra['nama_lampiran'];
                                        $m_lkdg->filename = $v_lmitra['filename'];
                                        $m_lkdg->path = $v_lmitra['path'];
                                        $m_lkdg->status = 1;
                                        $m_lkdg->save();
                                    }
                                }
                                /** END - LAMPIRAN KANDANG */

                                $m_bk = new \Model\Storage\BangunanKandang_model();
                                $sql = "
                                    select * from bangunan_kandang where kandang = ".$kandang['id']."
                                ";
                                $bangunans = $m_bk->hydrateRaw( $sql );
                                if ( $bangunans->count() > 0 ) {
                                    $bangunans = $bangunans->toArray();
                                    foreach ($bangunans as $bangunan) {
                                        $m_bangunan_kandang = new \Model\Storage\BangunanKandang_model();
                                        $m_bangunan_kandang->id = $m_bangunan_kandang->getNextIdentity();
                                        $m_bangunan_kandang->kandang = $kandang_id;
                                        $m_bangunan_kandang->bangunan = $bangunan['bangunan'];
                                        $m_bangunan_kandang->meter_panjang = $bangunan['meter_panjang'];
                                        $m_bangunan_kandang->meter_lebar = $bangunan['meter_lebar'];
                                        $m_bangunan_kandang->jumlah_unit = $bangunan['jumlah_unit'];
                                        $m_bangunan_kandang->save();
                                    }
                                }
                            }
                        }
                    }
                }

                /** LAMPIRAN MITRA */
                $m_mitra = new \Model\Storage\Mitra_model();
                $m_lmitra = new \Model\Storage\Lampiran_model();
                $tbl_mitra = $m_mitra->getTable();
                $d_lmitra = $m_lmitra->where('tabel', $tbl_mitra)->where('tabel_id', $id)->get();
                if ( $d_lmitra->count() > 0 ) {
                    $d_lmitra = $d_lmitra->toArray();
                    foreach ($d_lmitra as $k_lmitra => $v_lmitra) {
                        $m_lmitra = new \Model\Storage\Lampiran_model();
                        $m_lmitra->tabel = $tbl_mitra;
                        $m_lmitra->tabel_id = $id_mitra;
                        $m_lmitra->nama_lampiran = $v_lmitra['nama_lampiran'];
                        $m_lmitra->filename = $v_lmitra['filename'];
                        $m_lmitra->path = $v_lmitra['path'];
                        $m_lmitra->status = 1;
                        $m_lmitra->save();
                    }
                }
                /** END - LAMPIRAN MITRA */

                /* MITRA POSISI */
                $m_mp = new \Model\Storage\MitraPosisi_model();
                $d_mp = $m_mp->where('nomor', $d_mitra['nomor'])->orderBy('id', 'desc')->first();
                if ( $d_mp ) {
                    $m_mp = new \Model\Storage\MitraPosisi_model();
                    $m_mp->tanggal = $d_mp->tanggal;
                    $m_mp->nomor = $nomor_mitra;
                    $m_mp->lat_long = $d_mp->lat_long;
                    $m_mp->foto_kunjungan = $d_mp->foto_kunjungan;
                    $m_mp->kandang = $d_mp->kandang;
                    $m_mp->save();

                    $deskripsi_log_dk = 'di-import oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/update', $m_mp, $deskripsi_log_dk );
                }
                /* END - MITRA POSISI */

                $m_mitra = new \Model\Storage\Mitra_model();
                $_d_mitra = $m_mitra->where('id', $id_mitra)->first();
                $deskripsi_log = 'di-pindah oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $_d_mitra, $deskripsi_log );

                $m_mitra_pindah = new \Model\Storage\Mitra_model();
                $m_mitra_pindah->where('id', $id)->update(
                    array(
                        'mstatus' => 0
                    )
                );
                $_d_mitra_pindah = $m_mitra_pindah->where('id', $id)->first();
                $deskripsi_log = 'di-non aktifkan oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $_d_mitra_pindah, $deskripsi_log );

                $m_mitra_pp = new \Model\Storage\MitraPindahPerusahaan_model();
                $m_mitra_pp->perusahaan_asal = $d_mitra['perusahaan'];
                $m_mitra_pp->perusahaan_tujuan = $perusahan_baru;
                $m_mitra_pp->nomor_asal = $d_mitra['nomor'];
                $m_mitra_pp->nomor_tujuan = $nomor_mitra;
                $m_mitra_pp->save();

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $m_mitra_pp, $deskripsi_log );
                
                $this->result['status'] = 1;
                $this->result['message'] = 'Data peternak berhasil pindah perusahaan.';
                $this->result['content'] = array('id' => $id_mitra);
            } catch (Exception $e) {
                $this->result['message'] = $e->getMessage();
            }
        } else {
            $this->result['message'] = 'Data peternak bermasalah, harap segera hubungi tim IT.';
        }

        display_json( $this->result );
    }

    public function model($status)
    {
        if ( is_numeric($status) ) {
            $status = getStatus($status);
        }

        $m_mitra = new \Model\Storage\Mitra_model();
        $dashboard = $m_mitra->getDashboard($status);

        return $dashboard;
    }

    public function tes()
    {
        $id = '';
        $data = $this->getDataMitra( $id )['data'];

        cetak_r( $nomor_mitra );
    }
}
