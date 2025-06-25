<?php defined('BASEPATH') OR exit('No direct script access allowed');

class DaftarKunjungan extends Public_Controller {

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
            $this->load->library('Mobile_Detect');
            $detect = new Mobile_Detect();

            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/compress-image/js/compress-image.js",
                "assets/marketing/daftar_kunjungan/js/daftar-kunjungan.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/marketing/daftar_kunjungan/css/daftar-kunjungan.css",
            ));

            $data = $this->includes;

            // $isMobile = false;
            // if ( $detect->isMobile() ) {
            //     $isMobile = true;
            // }

            $content['akses'] = $akses;
            $content['isMobile'] = $this->isMobile;

            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();

            // Load Indexx
            $data['title_menu'] = 'Daftar Kunjungan';
            $data['view'] = $this->load->view('marketing/daftar_kunjungan/index', $content, TRUE);
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
            // $this->load->library('Mobile_Detect');
            // $detect = new Mobile_Detect();

            // $isMobile = false;
            // if ( $detect->isMobile() ) {
            //     $isMobile = true;
            // }

            $m_dk = new \Model\Storage\DaftarKunjungan_model();
            $sql = "
                select 
                    dk.* 
                from 
                    daftar_kunjungan dk 
                where 
                    dk.no_pelanggan = '".$params['no_pelanggan']."' order by dk.tanggal desc
            ";
            $d_sk = $m_dk->hydrateRaw( $sql );

            $data = null;
            if ( $d_sk->count() > 0 ) {
                $data = $d_sk->toArray();
            }

            $content['data'] = $data;
            $content['isMobile'] = $this->isMobile;
            $html = $this->load->view('marketing/daftar_kunjungan/list', $content, TRUE);

            $this->result['status'] = 1;
            $this->result['html'] = $html;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function getLokasi()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from lokasi l where jenis like '%KB%' or jenis like '%KT%' 
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $nama = str_replace('Kab ', '', str_replace('Kota ', '', $value['nama']));

                if ( !isset( $data[ $nama ] ) ) {
                    $data[ $nama ]['nama'] = $nama;
                    $data[ $nama ]['id'][] = $value['id'];
                } else {
                    $data[ $nama ]['id'][] = $value['id'];
                }
            }

            ksort($data);
        }

        return $data;
    }

    public function getKecamatan()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from lokasi l where jenis like '%KC%' and induk <> 0 order by nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();

            // foreach ($d_conf as $key => $value) {
            //     $nama = str_replace('Kab ', '', str_replace('Kota ', '', $value['nama']));

            //     if ( !isset( $data[ $nama ] ) ) {
            //         $data[ $nama ]['nama'] = $nama;
            //         $data[ $nama ]['id'] = $value['id'];
            //     } else {
            //         $data[ $nama ]['id'] = $value['id'];
            //     }
            // }

            // ksort($data);
        }

        return $data;
    }

    public function getPelanggan()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                plg.*,
                l_kab_kota.id as kab_kota,
                REPLACE(REPLACE(l_kab_kota.nama, 'Kota ', ''), 'Kab ', '') as nama_kab_kota
            from pelanggan plg
            right join
                (select max(id) as id, nomor from pelanggan where tipe = 'pelanggan' group by nomor ) plg2
                on
                    plg.id = plg2.id
            right join
                lokasi l_kec
                on
                    plg.alamat_kecamatan = l_kec.id
            right join
                lokasi l_kab_kota
                on
                    l_kab_kota.id = l_kec.induk
            where
                plg.mstatus = 1
            order by
                plg.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getAlamat()
    {
        $params = $this->input->post('params');

        try {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    plg.*,
                    REPLACE(REPLACE(l_kec_plg.nama, 'Kota ', ''), 'Kab ', '') as nama_kecamatan_plg,
                    REPLACE(REPLACE(l_kec_usaha.nama, 'Kota ', ''), 'Kab ', '') as nama_kecamatan_usaha
                from pelanggan plg
                right join
                    (select max(id) as id, nomor from pelanggan where tipe = 'pelanggan' group by nomor ) plg2
                    on
                        plg.id = plg2.id
                left join
                    lokasi l_kec_plg
                    on
                        plg.alamat_kecamatan = l_kec_plg.id
                left join
                    lokasi l_kec_usaha
                    on
                        plg.usaha_kecamatan = l_kec_usaha.id
                where
                    plg.nomor = '".$params['no_pelanggan']."'
                order by
                    plg.nama asc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $alamat_pelanggan = null;
            $alamat_usaha = null;

            $data = null;
            if ( $d_conf->count() > 0 ) {
                $data = $d_conf->toArray()[0];

                $jalan = empty($data['alamat_jalan']) ? '' : strtoupper('DSN.'.trim(str_replace('DSN.', '', $data['alamat_jalan'])));
                $rt = empty($data['alamat_rt']) ? '' : strtoupper(' RT.'.$data['alamat_rt']);
                $rw = empty($data['alamat_rw']) ? '' : strtoupper('/RW.'.$data['alamat_rw']);
                $kelurahan = empty($data['alamat_kelurahan']) ? '' : strtoupper(' ,'.$data['alamat_kelurahan']);
                $kecamatan = empty($data['nama_kecamatan_plg']) ? '' : strtoupper(' ,'.$data['nama_kecamatan_plg']);

                $alamat_pelanggan = $jalan.$rt.$rw.$kelurahan.$kecamatan;

                $jalan = empty($data['usaha_jalan']) ? '' : strtoupper('DSN.'.trim(str_replace('DSN.', '', $data['usaha_jalan'])));
                $rt = empty($data['usaha_rt']) ? '' : strtoupper(' RT.'.$data['usaha_rt']);
                $rw = empty($data['usaha_rw']) ? '' : strtoupper('/RW.'.$data['usaha_rw']);
                $kelurahan = empty($data['usaha_kelurahan']) ? '' : strtoupper(' ,'.$data['usaha_kelurahan']);
                $kecamatan = empty($data['nama_kecamatan_usaha']) ? '' : strtoupper(' ,'.$data['nama_kecamatan_usaha']);

                $alamat_usaha = $jalan.$rt.$rw.$kelurahan.$kecamatan;
            }

            $this->result['status'] = 1;
            $this->result['content'] = array(
                'alamat_pelanggan' => $alamat_pelanggan,
                'alamat_usaha' => $alamat_usaha
            );
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function riwayat()
    {
        $content['pelanggan'] = $this->getPelanggan();
        $html = $this->load->view('marketing/daftar_kunjungan/riwayat', $content, TRUE);

        return $html;
    }

    public function addForm()
    {
        $content['pelanggan'] = $this->getPelanggan();
        $content['lokasi'] = $this->getLokasi();
        $content['kecamatan'] = $this->getKecamatan();
        $content['isMobile'] = $this->isMobile;
        $html = $this->load->view('marketing/daftar_kunjungan/addForm', $content, TRUE);

        return $html;
    }

    public function viewForm($id)
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                dk.id as id_dk,
                dk.kab_kota,
                dk.catatan,
                dk.lat_long,
                dk.foto_kunjungan,
                plg.*,
                REPLACE(REPLACE(l_kec_plg.nama, 'Kota ', ''), 'Kab ', '') as nama_kecamatan_plg,
                REPLACE(REPLACE(l_kec_usaha.nama, 'Kota ', ''), 'Kab ', '') as nama_kecamatan_usaha
            from daftar_kunjungan dk
            right join
                (
                    select plg1.* from pelanggan plg1
                    right join
                        (select max(id) as id, nomor from pelanggan where tipe = 'pelanggan' group by nomor ) plg2
                        on
                            plg1.id = plg2.id
                ) plg
                on
                    plg.nomor = dk.no_pelanggan
            right join
                lokasi l_kec_plg
                on
                    plg.alamat_kecamatan = l_kec_plg.id
            right join
                lokasi l_kec_usaha
                on
                    plg.usaha_kecamatan = l_kec_usaha.id
            where
                dk.id = '".$id."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $alamat_pelanggan = null;
        $alamat_usaha = null;

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray()[0];

            $jalan = empty($data['alamat_jalan']) ? '' : strtoupper('DSN.'.trim(str_replace('DSN.', '', $data['alamat_jalan'])));
            $rt = empty($data['alamat_rt']) ? '' : strtoupper(' RT.'.$data['alamat_rt']);
            $rw = empty($data['alamat_rw']) ? '' : strtoupper('/RW.'.$data['alamat_rw']);
            $kelurahan = empty($data['alamat_kelurahan']) ? '' : strtoupper(' ,'.$data['alamat_kelurahan']);
            $kecamatan = empty($data['nama_kecamatan_plg']) ? '' : strtoupper(' ,'.$data['nama_kecamatan_plg']);

            $alamat_pelanggan = $jalan.$rt.$rw.$kelurahan.$kecamatan;

            $jalan = empty($data['alamat_jalan']) ? '' : strtoupper('DSN.'.trim(str_replace('DSN.', '', $data['alamat_jalan'])));
            $rt = empty($data['usaha_rt']) ? '' : strtoupper(' RT.'.$data['usaha_rt']);
            $rw = empty($data['usaha_rw']) ? '' : strtoupper('/RW.'.$data['usaha_rw']);
            $kelurahan = empty($data['usaha_kelurahan']) ? '' : strtoupper(' ,'.$data['usaha_kelurahan']);
            $kecamatan = empty($data['nama_kecamatan_usaha']) ? '' : strtoupper(' ,'.$data['nama_kecamatan_usaha']);

            $alamat_usaha = $jalan.$rt.$rw.$kelurahan.$kecamatan;

            $data = array(
                'kab_kota' => $data['kab_kota'],
                'nama_pelanggan' => $data['nama'],
                'catatan' => $data['catatan'],
                'lat_long' => $data['lat_long'],
                'foto_kunjungan' => $data['foto_kunjungan'],
                'alamat_pelanggan' => $alamat_pelanggan,
                'alamat_usaha' => $alamat_usaha
            );
        }

        $content['data'] = $data;
        $content['isMobile'] = $this->isMobile;
        $html = $this->load->view('marketing/daftar_kunjungan/viewForm', $content, TRUE);

        return $html;
    }

    public function save()
    {
        $params = json_decode($this->input->post('data'),TRUE);
        $file = isset($_FILES['foto_kunjungan']) ? $_FILES['foto_kunjungan'] : [];

        try {
            // cetak_r( $params, 1 );

            $path_name  = null;
            $moved = uploadFile($file);
            $isMoved = $moved['status'];
            if ($isMoved) {
                $path_name = $moved['path'];

                if ( $params['status'] == 1 ) {
                    $m_dk = new \Model\Storage\DaftarKunjungan_model();
                    $now = $m_dk->getDate();

                    $m_dk->tanggal = $now['waktu'];
                    $m_dk->kab_kota = $params['kab_kota'];
                    $m_dk->no_pelanggan = $params['no_pelanggan'];
                    $m_dk->catatan = $params['catatan'];
                    $m_dk->lat_long = $params['lat_long'];
                    $m_dk->foto_kunjungan = $path_name;
                    $m_dk->save();

                    $deskripsi_log_dk = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/save', $m_dk, $deskripsi_log_dk );
                } else {
                    $m_pelanggan = new \Model\Storage\Pelanggan_model();
                    $pelanggan_id = $m_pelanggan->getNextIdentity();

                    $kode_jenis = "B";

                    $no_pelanggan = $m_pelanggan->getNextNomor($kode_jenis);

                    $m_pelanggan->id = $pelanggan_id;       
                    $m_pelanggan->jenis = 'eksternal';
                    $m_pelanggan->nomor = $no_pelanggan;
                    $m_pelanggan->nama = $params['nama_pelanggan'];
                    $m_pelanggan->nik = 0;
                    $m_pelanggan->cp = 0;
                    $m_pelanggan->npwp = 0;
                    $m_pelanggan->alamat_kecamatan = $params['kecamatan_plg'];
                    $m_pelanggan->alamat_kelurahan = '-';
                    $m_pelanggan->alamat_rt = $params['rt_plg'];
                    $m_pelanggan->alamat_rw = $params['rw_plg'];
                    $m_pelanggan->alamat_jalan = $params['alamat_plg'];
                    $m_pelanggan->usaha_kecamatan = $params['kecamatan_usaha'];
                    $m_pelanggan->usaha_kelurahan = '-';
                    $m_pelanggan->usaha_rt = $params['rt_usaha'];
                    $m_pelanggan->usaha_rw = $params['rw_usaha'];
                    $m_pelanggan->usaha_jalan = $params['alamat_usaha'] ?: null;
                    $m_pelanggan->status = 'submit';
                    $m_pelanggan->mstatus = 1;
                    $m_pelanggan->tipe = 'pelanggan';
                    $m_pelanggan->platform = 0;
                    $m_pelanggan->version = 1;
                    $m_pelanggan->save();

                    $deskripsi_log_pelanggan = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/save', $m_pelanggan, $deskripsi_log_pelanggan );

                    $m_conf = new \Model\Storage\Conf();
                    $sql = "
                        select
                            l_kab_kota.nama
                        from lokasi l 
                        right join
                            lokasi l_kab_kota
                            on
                                l.induk = l_kab_kota.id
                        where 
                            l.id = ".$params['kecamatan_plg']."
                    ";
                    $d_conf = $m_conf->hydrateRaw( $sql );

                    $nama_kab_kota = null;
                    if ( $d_conf->count() > 0 ) {
                        $nama = $d_conf->toArray()[0]['nama'];
                        $nama_kab_kota = strtoupper(str_replace('Kab ', '', str_replace('Kota ', '', $nama)));
                    }

                    $m_dk = new \Model\Storage\DaftarKunjungan_model();
                    $now = $m_dk->getDate();

                    $m_dk->tanggal = $now['waktu'];
                    $m_dk->kab_kota = $nama_kab_kota;
                    $m_dk->no_pelanggan = $no_pelanggan;
                    $m_dk->catatan = $params['catatan'];
                    $m_dk->lat_long = $params['lat_long'];
                    $m_dk->foto_kunjungan = $path_name;
                    $m_dk->save();

                    $deskripsi_log_dk = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run( 'base/event/save', $m_dk, $deskripsi_log_dk );
                }
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data kunjungan berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes($value='')
    {
    }
}