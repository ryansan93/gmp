<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KreditKendaraan extends Public_Controller {

    private $pathView = 'transaksi/kredit_kendaraan/';
    private $url;
    private $hakAkses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->hakAkses = hakAkses($this->url);
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
        if ( $this->hakAkses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/compress-image/js/compress-image.js",
                "assets/transaksi/kredit_kendaraan/js/kredit-kendaraan.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/kredit_kendaraan/css/kredit-kendaraan.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->hakAkses;

            $content['riwayat'] = $this->load->view($this->pathView.'riwayat', $content, TRUE);
            $content['add_form'] = $this->addForm();

            // Load Indexx
            $data['title_menu'] = 'Kredit Kendaraan';
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
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
                    'kode' => $d_perusahaan->kode
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function getUnit()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'UN')->orderBy('nama', 'asc')->get();

        $data = null;
        if ( $d_wilayah->count() > 0 ) {
            $d_wilayah = $d_wilayah->toArray();
            foreach ($d_wilayah as $key => $value) {
                $nama = str_replace('Kab ', '', str_replace('Kota ', '', $value['nama']));
                $kode = $value['kode'];

                $key = $nama.' - '.$kode;

                $data[$key] = array(
                    'nama' => $nama,
                    'kode' => $kode
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function getKendaraan()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                k.*,
                kry.nama as nama_karyawan,
                kr.unit_baru as kode_unit
            from kendaraan k
            left join
                (
                    select kr1.* from kendaraan_riwayat kr1
                    right join
                        (select min(tgl_serah_terima) as tgl_serah_terima, id_header from kendaraan_riwayat group by id_header) kr2
                        on
                            kr1.tgl_serah_terima = kr2.tgl_serah_terima and
                            kr1.id_header = kr2.id_header
                ) kr
                on
                    k.id = kr.id_header
            left join
                (
                    select k1.* from karyawan k1
                    right join
                        (select max(id) as id, nik from karyawan group by nik) k2
                        on
                            k1.id = k2.id
                ) kry
                on
                    kr.pemegang_baru = kry.nik
            order by
                kr.unit_baru asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function dataKendaraan() {
        $params = $this->input->post('params');

        try {
            $id = $params['id'];
            
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select 
                    k.*,
                    kry.nik as kode_karyawan,
                    kry.nama as nama_karyawan,
                    kr.unit_baru as kode_unit,
                    w.nama as nama_unit,
                    prs.kode as kode_perusahaan,
                    prs.perusahaan as nama_perusahaan
                from kendaraan k
                left join
                    (
                        select kr1.* from kendaraan_riwayat kr1
                        right join
                            (select min(tgl_serah_terima) as tgl_serah_terima, id_header from kendaraan_riwayat group by id_header) kr2
                            on
                                kr1.tgl_serah_terima = kr2.tgl_serah_terima and
                                kr1.id_header = kr2.id_header
                    ) kr
                    on
                        k.id = kr.id_header
                left join
                    (
                        select k1.* from karyawan k1
                        right join
                            (select max(id) as id, nik from karyawan group by nik) k2
                            on
                                k1.id = k2.id
                    ) kry
                    on
                        kr.pemegang_baru = kry.nik
                left join
                    (
                        select 
                            w1.kode,
                            REPLACE(REPLACE(w1.nama, 'Kab ', ''), 'Kota ', '') as nama
                        from wilayah w1
                        right join
                            (select max(id) as id, kode from wilayah group by kode) w2
                            on
                                w1.id = w2.id
                    ) w
                    on
                        kr.unit_baru = w.kode
                left join
                    (
                        select p1.* from perusahaan p1
                        right join
                            (select max(id) as id, kode from perusahaan group by kode) p2
                            on
                                p1.id = p2.id
                    ) prs
                    on
                        k.perusahaan = prs.kode
                where
                    k.id = ".$id."
                order by
                    kr.unit_baru asc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $data = null;
            if ( $d_conf->count() > 0 ) {
                $data = $d_conf->toArray()[0];
            }

            $this->result['status'] = 1;
            $this->result['content'] = array('kendaraan' => $data);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function getKaryawan()
    {
        $unit_kode = $this->input->post('params');

        try {
            $karyawan = $this->dataKaryawan( $unit_kode );

            $this->result['content'] = array('karyawan' => $karyawan);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function dataKaryawan($unit_kode)
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('kode', $unit_kode)->orderBy('nama', 'asc')->get();

        $karyawan = array();

        $unit_id = null;
        if ( $d_wilayah->count() > 0 ) {
            $d_wilayah = $d_wilayah->toArray();

            foreach ($d_wilayah as $key => $value) {
                $unit_id[] = $value['id'];
            }

            $unit_id[] = 'All';

            $m_uk = new \Model\Storage\UnitKaryawan_model();
            $d_uk = $m_uk->select('id_karyawan')->distinct('id_karyawan')->whereIn('unit', $unit_id)->get()->toArray();

            if ( !empty($d_uk) ) {
                foreach ($d_uk as $k_uk => $v_uk) {
                    $m_k = new \Model\Storage\Karyawan_model();
                    $d_k = $m_k->where('id', $v_uk['id_karyawan'])->where('status', 1)->first();

                    if ( !empty($d_k) ) {
                        $d_k = $d_k->toArray();
                        $key = $d_k['jabatan'].' | '.$d_k['nama'].' | '.$d_k['id'];

                        $karyawan[ $key ] = $d_k;
                    }
                }
            }
        }

        if ( !empty($karyawan) ) {
            ksort($karyawan);
        }

        return $karyawan;
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $data = null;
        $m_kk = new \Model\Storage\KreditKendaraan_model();
        if ( $params == 'all' ) {
            $d_kk = $m_kk->with(['d_perusahaan', 'd_unit', 'd_peruntukan', 'detail'])->get();
            if ( $d_kk->count() > 0 ) {
                $data = $d_kk->toArray();
            }
        } else {
            $d_kk = $m_kk->where('lunas', $params)->with(['d_perusahaan', 'd_unit', 'd_peruntukan', 'detail'])->get();
            if ( $d_kk->count() > 0 ) {
                $data = $d_kk->toArray();
            }
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }

    public function loadForm()
    {
        $params = $this->input->get('params');
        $edit = $this->input->get('edit');

        $id = $params['id'];

        $content = array();
        $html = "url not found";
        
        if ( !empty($id) && $edit != 'edit' ) {
            // NOTE: view data BASTTB (ajax)
            $html = $this->detailForm($id);
        } else if ( !empty($id) && $edit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $html = $this->editForm($id);
        }else{
            $html = $this->addForm();
        }

        echo $html;
    }

    public function addForm()
    {
        // $content['perusahaan'] = $this->getPerusahaan();
        // $content['unit'] = $this->getUnit();
        $content['kendaraan'] = $this->getKendaraan();

        $html = $this->load->view($this->pathView.'addForm', $content, TRUE);

        return $html;
    }

    public function detailForm($id)
    {
        $m_kk = new \Model\Storage\KreditKendaraan_model();
        $d_kk = $m_kk->where('kode', $id)->with(['d_perusahaan', 'd_unit', 'd_peruntukan', 'detail'])->first();

        $data = null;
        if ( $d_kk ) {
            $data = $d_kk->toArray();
        }

        $content['akses'] = $this->hakAkses;
        $content['data'] = $data;

        $html = $this->load->view($this->pathView.'detailForm', $content, TRUE);

        return $html;
    }

    public function editForm($id)
    {
        $m_kk = new \Model\Storage\KreditKendaraan_model();
        $d_kk = $m_kk->where('kode', $id)->with(['d_perusahaan', 'd_unit', 'd_peruntukan', 'detail'])->first();

        $data = null;
        // $karyawan = null;
        if ( $d_kk ) {
            $data = $d_kk->toArray();
            // $karyawan = $this->dataKaryawan($data['unit']);
        }

        // $content['perusahaan'] = $this->getPerusahaan();
        // $content['unit'] = $this->getUnit();
        // $content['karyawan'] = $karyawan;
        $content['kendaraan'] = $this->getKendaraan();
        $content['data'] = $data;

        $html = $this->load->view($this->pathView.'editForm', $content, TRUE);

        return $html;
    }

    public function generateRowAngsuran()
    {
        $params = $this->input->get('params');

        $content['tenor'] = $params['tenor'];
        $content['angsuran'] = $params['angsuran'];
        $content['tanggal'] = $params['tanggal'];
        $content['tgl_jatuh_tempo'] = $params['tgl_jatuh_tempo'];
        $html = $this->load->view($this->pathView.'listAngsuran', $content, TRUE);

        echo $html;
    }

    public function save()
    {
        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['file']) ? $_FILES['file'] : null;
        $mappingFiles = !empty($files) ? $this->mappingFiles($files) : null;

        try {
            $path_name = null;
            if ( !empty($mappingFiles) ) {
                $file = $mappingFiles['bayar_angsuran1'];
                if ( !empty($file) ) {
                    $moved = uploadFile($file);
                    $isMoved = $moved['status'];
                    if ($isMoved) {
                        $path_name = $moved['path'];
                    }
                }
            }

            $m_kk = new \Model\Storage\KreditKendaraan_model();
            $kode = $m_kk->getNextIdRibuan();

            $m_kk->kode = $kode;
            $m_kk->tanggal = $params['tanggal'];
            $m_kk->perusahaan = $params['perusahaan'];
            $m_kk->merk_jenis = $params['merk_jenis'];
            $m_kk->warna = $params['warna'];
            $m_kk->tahun = $params['tahun'];
            $m_kk->unit = $params['unit'];
            $m_kk->peruntukan = $params['peruntukan'];
            $m_kk->harga = $params['harga'];
            $m_kk->dp = $params['dp'];
            $m_kk->angsuran = $params['angsuran'];
            $m_kk->tenor = $params['tenor'];
            $m_kk->tgl_jatuh_tempo = $params['tgl_jatuh_tempo'];
            $m_kk->lunas = 0;
            $m_kk->tgl_bayar = $params['tgl_bayar_angsuran1'];
            $m_kk->lampiran = $path_name;
            $m_kk->kendaraan_id = $params['kendaraan'];
            $m_kk->save();

            $m_conf = new \Model\Storage\Conf();
            $sql = "insert_jurnal null, null, null, null, 'kredit_kendaraan', '".$kode."', null, 1";
            $d_conf = $m_conf->hydrateRaw( $sql );

            foreach ($params['detail'] as $k_det => $v_det) {
                $path_name = null;
                if ( !empty($mappingFiles) ) {
                    $file = isset($mappingFiles['ANGSURAN KE '.$v_det['angsuran_ke']]) ? $mappingFiles['ANGSURAN KE '.$v_det['angsuran_ke']] : null;
                    if ( !empty($file) ) {
                        $moved = uploadFile($file);
                        $isMoved = $moved['status'];
                        if ($isMoved) {
                            $path_name = $moved['path'];
                        }
                    }
                }

                $m_kkd = new \Model\Storage\KreditKendaraanDet_model();
                $m_kkd->kredit_kendaraan_kode = $kode;
                $m_kkd->angsuran_ke = $v_det['angsuran_ke'];
                $m_kkd->tgl_jatuh_tempo = $v_det['tgl_jatuh_tempo'];
                $m_kkd->jumlah_angsuran = $v_det['jumlah_angsuran'];
                if ( isset($v_det['tgl_bayar']) && !empty($v_det['tgl_bayar']) ) {
                    $m_kkd->tgl_bayar = $v_det['tgl_bayar'];
                }
                $m_kkd->save();

                if ( isset($v_det['tgl_bayar']) && !empty($v_det['tgl_bayar']) ) {
                    $m_conf = new \Model\Storage\Conf();
                    $sql = "insert_jurnal null, null, null, null, 'kredit_kendaraan_det', '".$kode.$v_det['angsuran_ke']."', null, 1";
                    $d_conf = $m_conf->hydrateRaw( $sql );
                }
            }

            /* UPDATE LUNAS */
            $m_kkd = new \Model\Storage\KreditKendaraanDet_model();
            $d_kkd = $m_kkd->where('kredit_kendaraan_kode', $kode)->orderBy('angsuran_ke', 'desc')->first();
            if ( $d_kkd ) {
                if ( !empty($d_kkd->tgl_bayar) ) {
                    $m_kk->where('kode', $kode)->update(
                        array(
                            'lunas' => 1
                        )
                    );
                }
            }

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_kk, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $kode);
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit()
    {
        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['file']) ? $_FILES['file'] : null;
        $mappingFiles = !empty($files) ? $this->mappingFiles($files) : null;

        try {
            cetak_r( $params, 1 );

            $path_name = null;
            if ( !empty($mappingFiles) ) {
                $file = $mappingFiles['bayar_angsuran1'];
                if ( !empty($file) ) {
                    $moved = uploadFile($file);
                    $isMoved = $moved['status'];
                    if ($isMoved) {
                        $path_name = $moved['path'];
                    }
                }
            }

            $m_kk = new \Model\Storage\KreditKendaraan_model();
            $kode = $params['kode'];

            $m_kk->where('kode', $kode)->update(
                array(
                    'tanggal' => $params['tanggal'],
                    'perusahaan' => $params['perusahaan'],
                    'merk_jenis' => $params['merk_jenis'],
                    'warna' => $params['warna'],
                    'tahun' => $params['tahun'],
                    'unit' => $params['unit'],
                    'peruntukan' => $params['peruntukan'],
                    'harga' => $params['harga'],
                    'dp' => $params['dp'],
                    'angsuran' => $params['angsuran'],
                    'tenor' => $params['tenor'],
                    'tgl_jatuh_tempo' => $params['tgl_jatuh_tempo'],
                    'tgl_bayar' => $params['tgl_bayar_angsuran1'],
                    'lunas' => 0,
                    'kendaraan_id' => $params['kendaraan'],
                )
            );
            if ( !empty($path_name) ) {
                $m_kk->where('kode', $kode)->update(
                    array(
                        'lampiran' => $path_name
                    )
                );
            }


            $m_conf = new \Model\Storage\Conf();
            $sql = "insert_jurnal null, null, null, null, 'kredit_kendaraan', '".$kode."', '".$kode."', 2";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $m_kkd = new \Model\Storage\KreditKendaraanDet_model();
            $m_kkd->where('kredit_kendaraan_kode', $kode)->delete();

            if ( isset( $params['detail'] ) && !empty( $params['detail'] ) ) {
                foreach ($params['detail'] as $k_det => $v_det) {
                    $m_kkd = new \Model\Storage\KreditKendaraanDet_model();
                    $d_kkd = $m_kkd->where('kredit_kendaraan_kode', $kode)->where('angsuran_ke', $v_det['angsuran_ke'])->first();

                    if ( $d_kkd ) {
                        $m_kkd->where('kredit_kendaraan_kode', $kode)->where('angsuran_ke', $v_det['angsuran_ke'])->update(
                            array(
                                'jumlah_angsuran' => $v_det['jumlah_angsuran']
                            )
                        );
    
                        if ( isset($v_det['tgl_bayar']) && !empty($v_det['tgl_bayar']) ) {
                            $m_kkd->where('kredit_kendaraan_kode', $kode)->where('angsuran_ke', $v_det['angsuran_ke'])->update(
                                array(
                                    'tgl_bayar' => $v_det['tgl_bayar']
                                )
                            );
                            
                            $path_name = null;
                            if ( !empty($mappingFiles) ) {
                                $file = isset($mappingFiles['ANGSURAN KE '.$v_det['angsuran_ke']]) ? $mappingFiles['ANGSURAN KE '.$v_det['angsuran_ke']] : null;
                                if ( !empty($file) ) {
                                    $moved = uploadFile($file);
                                    $isMoved = $moved['status'];
                                    if ($isMoved) {
                                        $path_name = $moved['path'];
                                    }
                                }
                            }
    
                            if ( !empty($path_name) ) {
                                $m_kkd->where('kredit_kendaraan_kode', $kode)->where('angsuran_ke', $v_det['angsuran_ke'])->update(
                                    array(
                                        'lampiran' => $path_name
                                    )
                                );
                            }
    
                            $m_conf = new \Model\Storage\Conf();
                            $sql = "insert_jurnal null, null, null, null, 'kredit_kendaraan_det', '".$kode.$v_det['angsuran_ke']."', '".$kode.$v_det['angsuran_ke']."', 2";
                            $d_conf = $m_conf->hydrateRaw( $sql );
                        } else {
                            $m_kkd->where('kredit_kendaraan_kode', $kode)->where('angsuran_ke', $v_det['angsuran_ke'])->update(
                                array(
                                    'tgl_bayar' => null,
                                    'lampiran' => null
                                )
                            );
                        }
                    } else {
                        $path_name = null;
                        if ( !empty($mappingFiles) ) {
                            $file = isset($mappingFiles['ANGSURAN KE '.$v_det['angsuran_ke']]) ? $mappingFiles['ANGSURAN KE '.$v_det['angsuran_ke']] : null;
                            if ( !empty($file) ) {
                                $moved = uploadFile($file);
                                $isMoved = $moved['status'];
                                if ($isMoved) {
                                    $path_name = $moved['path'];
                                }
                            }
                        }

                        $m_kkd = new \Model\Storage\KreditKendaraanDet_model();
                        $m_kkd->kredit_kendaraan_kode = $kode;
                        $m_kkd->angsuran_ke = $v_det['angsuran_ke'];
                        $m_kkd->tgl_jatuh_tempo = $v_det['tgl_jatuh_tempo'];
                        $m_kkd->jumlah_angsuran = $v_det['jumlah_angsuran'];
                        if ( isset($v_det['tgl_bayar']) && !empty($v_det['tgl_bayar']) ) {
                            $m_kkd->tgl_bayar = $v_det['tgl_bayar'];
                        }
                        $m_kkd->save();

                        if ( isset($v_det['tgl_bayar']) && !empty($v_det['tgl_bayar']) ) {
                            $m_conf = new \Model\Storage\Conf();
                            $sql = "insert_jurnal null, null, null, null, 'kredit_kendaraan_det', '".$kode.$v_det['angsuran_ke']."', null, 2";
                            $d_conf = $m_conf->hydrateRaw( $sql );
                        }
                    }
                }
            }

            /* UPDATE LUNAS */
            $m_kkd = new \Model\Storage\KreditKendaraanDet_model();
            $d_kkd = $m_kkd->where('kredit_kendaraan_kode', $kode)->where('angsuran_ke', $params['tenor'])->first();
            if ( $d_kkd ) {
                if ( !empty($d_kkd->tgl_bayar) ) {
                    $m_kk->where('kode', $kode)->update(
                        array(
                            'lunas' => 1
                        )
                    );
                }
            }

            $d_kk = $m_kk->where('kode', $kode)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $m_kk, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $kode);
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
            $m_kk = new \Model\Storage\KreditKendaraan_model();
            $kode = $params;

            $d_kk = $m_kk->where('kode', $kode)->first();

            $m_kkd = new \Model\Storage\KreditKendaraanDet_model();
            $d_kkd = $m_kkd->where('kredit_kendaraan_kode', $kode)->get();

            if ( $d_kkd->count() > 0 ) {
                $d_kkd = $d_kkd->toArray();

                foreach ($d_kkd as $key => $value) {
                    $m_conf = new \Model\Storage\Conf();
                    $sql = "insert_jurnal null, null, null, null, 'kredit_kendaraan_det', '".$kode.$value['angsuran_ke']."', '".$kode.$value['angsuran_ke']."', 3";
                    $d_conf = $m_conf->hydrateRaw( $sql );
                }
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "insert_jurnal null, null, null, null, 'kredit_kendaraan', '".$kode."', '".$kode."', 3";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $m_kkd->where('kredit_kendaraan_kode', $kode)->delete();
            $m_kk->where('kode', $kode)->delete();

            $deskripsi_log = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_kk, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function saveDetail()
    {
        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['file']) ? $_FILES['file'] : null;
        $mappingFiles = !empty($files) ? $this->mappingFiles($files) : null;

        try {
            $path_name = null;
            if ( !empty($mappingFiles) ) {
                foreach ($mappingFiles as $k_mf => $v_mf) {
                    if ( !empty($v_mf) ) {
                        $moved = uploadFile($v_mf);
                        $isMoved = $moved['status'];
                        if ($isMoved) {
                            $path_name = $moved['path'];
                        }
                    }
                }
            }

            $m_kkd = new \Model\Storage\KreditKendaraanDet_model();
            $m_kkd->where('kredit_kendaraan_kode', $params['kredit_kendaraan_kode'])->where('angsuran_ke', $params['angsuran_ke'])->update(
                array(
                    'tgl_bayar' => $params['tgl_bayar'],
                    'lampiran' => $path_name
                )
            );

            $m_kkd = new \Model\Storage\KreditKendaraanDet_model();
            $d_kkd = $m_kkd->where('kredit_kendaraan_kode', $params['kredit_kendaraan_kode'])->where('angsuran_ke', $params['angsuran_ke'])->first();
            if ( $d_kkd ) {
                if ( !empty($d_kkd->tgl_bayar) ) {
                    $m_kk = new \Model\Storage\KreditKendaraan_model();
                    $m_kk->where('kode', $params['kredit_kendaraan_kode'])->update(
                        array(
                            'lunas' => 1
                        )
                    );
                }
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "insert_jurnal null, null, null, null, 'kredit_kendaraan_det', '".$params['kredit_kendaraan_kode'].$params['angsuran_ke']."', '".$params['kredit_kendaraan_kode'].$params['angsuran_ke']."', 2";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function mappingFiles($files)
    {
        $mappingFiles = [];
        foreach ($files['tmp_name'] as $key => $file) {
            $sha1 = sha1_file($file);
            $index = $key;
            $mappingFiles[$index] = [
                'name' => $files['name'][$key],
                'tmp_name' => $file,
                'type' => $files['type'][$key],
                'size' => $files['size'][$key],
                'error' => $files['error'][$key]
            ];
        }
        
        return $mappingFiles;
    }

    public function modalBpkb() {
        $kode = $this->input->get('kode');

        $m_kk = new \Model\Storage\KreditKendaraan_model();
        $d_kk = $m_kk->where('kode', $kode)->first();

        $data = array(
            'kode' => $kode,
            'bpkb' => $d_kk->bpkb,
            'no_bpkb' => $d_kk->no_bpkb
        );

        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'modalBpkb', $content, TRUE);

        echo $html;
    }

    public function saveBpkb() {
        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['file']) ? $_FILES['file'] : null;
        $mappingFiles = !empty($files) ? $this->mappingFiles($files) : null;

        try {
            $path_name = null;
            if ( !empty($mappingFiles) ) {
                $file = $mappingFiles['bpkb'];
                $moved = uploadFile($file);
                $isMoved = $moved['status'];
                if ($isMoved) {
                    $path_name = $moved['path'];

                }
            }

            $m_kk = new \Model\Storage\KreditKendaraan_model();
            if ( !empty($path_name) ) {
                $m_kk->where('kode', $params['kode'])->update(
                    array(
                        'no_bpkb' => $params['no_bpkb'],
                        'bpkb' => $path_name
                    )
                );
            } else {
                $m_kk->where('kode', $params['kode'])->update(
                    array(
                        'no_bpkb' => $params['no_bpkb']
                    )
                );
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data BPKB berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}