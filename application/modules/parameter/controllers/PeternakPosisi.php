<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PeternakPosisi extends Public_Controller {

    private $url;
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
                "assets/select2/js/select2.min.js",
                "assets/compress-image/js/compress-image.js",
                "assets/parameter/peternak_posisi/js/peternak-posisi.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/parameter/peternak_posisi/css/peternak-posisi.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['isMobile'] = $this->isMobile;

            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();

            // Load Indexx
            $data['title_menu'] = 'Peternak';
            $data['view'] = $this->load->view('parameter/peternak_posisi/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function loadForm()
    {
        $params = $this->input->post('params');

        $html = null;
        if ( empty($params['id']) && empty($params['edit']) ) {
            $html = $this->addForm();
        } else if ( !empty($params['id']) && empty($params['edit']) ) {
            $html = $this->viewForm($params['id']);
        }

        $this->result['html'] = $html;

        display_json( $this->result );
    }

    public function getLists()
    {
        $params = $this->input->post('params');

        try {
            $m_pp = new \Model\Storage\MitraPosisi_model();
            $sql = "
                select 
                    pp.* 
                from 
                    mitra_posisi pp
                where 
                    pp.nomor = '".$params['no_plasma']."' order by pp.tanggal desc
            ";
            $d_pp = $m_pp->hydrateRaw( $sql );

            $data = null;
            if ( $d_pp->count() > 0 ) {
                $data = $d_pp->toArray();
            }

            $content['data'] = $data;
            $content['isMobile'] = $this->isMobile;
            $html = $this->load->view('parameter/peternak_posisi/list', $content, TRUE);

            $this->result['status'] = 1;
            $this->result['html'] = $html;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function getMitra()
    {
        $data = array();

        $m_duser = new \Model\Storage\DetUser_model();
        $d_duser = $m_duser->where('id_user', $this->userid)->first();

        $m_karyawan = new \Model\Storage\Karyawan_model();
        $d_karyawan = $m_karyawan->where('nama', 'like', strtolower(trim($d_duser->nama_detuser)).'%')->where('status', 1)->first();

        $kode_unit = null;
        $kode_unit_all = null;
        if ( $d_karyawan ) {
            $m_ukaryawan = new \Model\Storage\UnitKaryawan_model();
            $d_ukaryawan = $m_ukaryawan->where('id_karyawan', $d_karyawan->id)->get()->toArray();

            foreach ($d_ukaryawan as $k_ukaryawan => $v_ukaryawan) {
                if ( $v_ukaryawan['unit'] != 'all' ) {
                    $m_wil = new \Model\Storage\Wilayah_model();
                    $d_wil = $m_wil->where('id', $v_ukaryawan['unit'])->first();

                    $kode_unit[ $d_wil->kode ] = $d_wil->kode;
                } else {
                    $kode_unit_all = $v_ukaryawan['unit'];
                }
            }
        } else {
            $kode_unit_all = 'all';
        }

        $sql_unit = null;
        if ( empty($kode_unit_all) and !empty($kode_unit) ) {
            $sql_unit = "where w.kode in ('".implode("', '", $kode_unit)."')";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                data.nomor,
                data.nama,
                w.kode,
                REPLACE(REPLACE(w.nama, 'Kab ', ''), 'Kota ', '') as nama_unit
            from
            (
                select 
                    mtr.nomor,
                    mtr.nama,
                    k.unit
                from kandang k
                right join
                    (
                        select 
                            mm1.*
                        from mitra_mapping mm1
                        right join
                            (select max(id) as id, nim from mitra_mapping group by nim) mm2
                            on
                                mm1.id = mm2.id
                    ) mm
                    on
                        k.mitra_mapping = mm.id
                left join
                    mitra mtr
                    on
                        mm.mitra = mtr.id
                where
                    mtr.mstatus = 1
                group by
                    mtr.nomor,
                    mtr.nama,
                    k.unit
            ) data
            left join
                wilayah w
                on
                    data.unit = w.id
            ".$sql_unit."
            group by
                data.nomor,
                data.nama,
                w.kode,
                REPLACE(REPLACE(w.nama, 'Kab ', ''), 'Kota ', '')
            order by
                data.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getKandang() {
        $params = $this->input->post('params');

        try {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    k.*,
                    REPLACE(REPLACE(l_kec.nama, 'Kota ', ''), 'Kab ', '') as nama_kecamatan
                from kandang k
                left join
                    (
                        select mm1.* from mitra_mapping mm1
                        right join
                            (select max(id) as id, nim, nomor from mitra_mapping group by nim, nomor) mm2
                            on
                                mm1.id = mm2.id
                    ) mm
                    on
                        k.mitra_mapping = mm.id
                left join
                    lokasi l_kec
                    on
                        k.alamat_kecamatan = l_kec.id
                where
                    mm.nomor = '".$params['no_plasma']."'
                order by
                    k.kandang asc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $alamat_plasma = null;

            $data = null;
            if ( $d_conf->count() > 0 ) {
                $d_conf = $d_conf->toArray();

                foreach ($d_conf as $k_kdg => $v_kdg) {
                    $data[] = $v_kdg['kandang'];
                }

                ksort( $data );

                // $jalan = empty($data['alamat_jalan']) ? '' : strtoupper('DSN.'.trim(str_replace('DSN.', '', $data['alamat_jalan'])));
                // $rt = empty($data['alamat_rt']) ? '' : strtoupper(' RT.'.$data['alamat_rt']);
                // $rw = empty($data['alamat_rw']) ? '' : strtoupper('/RW.'.$data['alamat_rw']);
                // $kelurahan = empty($data['alamat_kelurahan']) ? '' : strtoupper(' ,'.$data['alamat_kelurahan']);
                // $kecamatan = empty($data['nama_kecamatan']) ? '' : strtoupper(' ,'.$data['nama_kecamatan']);

                // $alamat_plasma = $jalan.$rt.$rw.$kelurahan.$kecamatan;
            }

            $this->result['status'] = 1;
            $this->result['content'] = array(
                'kandang' => $data
            );
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function getAlamat()
    {
        $params = $this->input->post('params');

        try {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    mtr.*,
                    REPLACE(REPLACE(l_kec.nama, 'Kota ', ''), 'Kab ', '') as nama_kecamatan
                from mitra mtr
                right join
                    (select max(id) as id, nomor from mitra group by nomor ) mtr2
                    on
                        mtr.id = mtr2.id
                right join
                    lokasi l_kec
                    on
                        mtr.alamat_kecamatan = l_kec.id
                where
                    mtr2.nomor = '".$params['no_plasma']."'
                order by
                    mtr.nama asc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $alamat_plasma = null;

            $data = null;
            if ( $d_conf->count() > 0 ) {
                $data = $d_conf->toArray()[0];

                $jalan = empty($data['alamat_jalan']) ? '' : strtoupper('DSN.'.trim(str_replace('DSN.', '', $data['alamat_jalan'])));
                $rt = empty($data['alamat_rt']) ? '' : strtoupper(' RT.'.$data['alamat_rt']);
                $rw = empty($data['alamat_rw']) ? '' : strtoupper('/RW.'.$data['alamat_rw']);
                $kelurahan = empty($data['alamat_kelurahan']) ? '' : strtoupper(' ,'.$data['alamat_kelurahan']);
                $kecamatan = empty($data['nama_kecamatan']) ? '' : strtoupper(' ,'.$data['nama_kecamatan']);

                $alamat_plasma = $jalan.$rt.$rw.$kelurahan.$kecamatan;
            }

            $this->result['status'] = 1;
            $this->result['content'] = array(
                'alamat_plasma' => $alamat_plasma
            );
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function riwayat()
    {
        $content['mitra'] = $this->getMitra();
        $content['isMobile'] = $this->isMobile;
        $html = $this->load->view('parameter/peternak_posisi/riwayat', $content, TRUE);

        return $html;
    }

    public function addForm()
    {
        $content['mitra'] = $this->getMitra();
        $content['isMobile'] = $this->isMobile;
        $html = $this->load->view('parameter/peternak_posisi/addForm', $content, TRUE);

        return $html;
    }

    public function viewForm($id)
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                mp.id as id_dk,
                mp.lat_long,
                mp.foto_kunjungan,
                mtr.*,
                REPLACE(REPLACE(l_kec.nama, 'Kota ', ''), 'Kab ', '') as nama_kecamatan
            from mitra_posisi mp
            right join
                (
                    select mtr1.* from mitra mtr1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor ) mtr2
                        on
                            mtr1.id = mtr2.id
                ) mtr
                on
                    mtr.nomor = mp.nomor
            right join
                lokasi l_kec
                on
                    mtr.alamat_kecamatan = l_kec.id
            where
                mp.id = '".$id."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $alamat_plasma = null;

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray()[0];

            $jalan = empty($data['alamat_jalan']) ? '' : strtoupper('DSN.'.trim(str_replace('DSN.', '', $data['alamat_jalan'])));
            $rt = empty($data['alamat_rt']) ? '' : strtoupper(' RT.'.$data['alamat_rt']);
            $rw = empty($data['alamat_rw']) ? '' : strtoupper('/RW.'.$data['alamat_rw']);
            $kelurahan = empty($data['alamat_kelurahan']) ? '' : strtoupper(' ,'.$data['alamat_kelurahan']);
            $kecamatan = empty($data['nama_kecamatan']) ? '' : strtoupper(' ,'.$data['nama_kecamatan']);

            $alamat_plasma = $jalan.$rt.$rw.$kelurahan.$kecamatan;

            $data = array(
                'nama_plasma' => $data['nama'],
                'lat_long' => $data['lat_long'],
                'foto_kunjungan' => $data['foto_kunjungan'],
                'alamat_plasma' => $alamat_plasma,
            );
        }

        $content['data'] = $data;
        $content['isMobile'] = $this->isMobile;
        $html = $this->load->view('parameter/peternak_posisi/viewForm', $content, TRUE);

        return $html;
    }

    public function save()
    {
        $params = json_decode($this->input->post('data'),TRUE);
        $file = isset($_FILES['foto_kunjungan']) ? $_FILES['foto_kunjungan'] : [];

        try {
            $path_name  = null;
            $moved = uploadFile($file);
            $isMoved = $moved['status'];
            if ($isMoved) {
                $path_name = $moved['path'];

                $m_mp = new \Model\Storage\MitraPosisi_model();
                $now = $m_mp->getDate();

                $m_mp->tanggal = $now['waktu'];
                $m_mp->nomor = $params['no_plasma'];
                $m_mp->lat_long = $params['lat_long'];
                $m_mp->foto_kunjungan = $path_name;
                $m_mp->kandang = $params['kandang'];
                $m_mp->save();

                $deskripsi_log_dk = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $m_mp, $deskripsi_log_dk );
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data peternak berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes($value='')
    {
    }
}