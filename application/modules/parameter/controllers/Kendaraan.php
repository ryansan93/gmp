<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Kendaraan extends Public_Controller {

    private $pathView = 'parameter/kendaraan/';
    private $url;

    private $jenis = array('mobil' => 'MOBIL', 'motor' => 'SEPEDA MOTOR');

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
    }

    public function index()
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(
                array(
                    'assets/select2/js/select2.min.js',
                    'assets/parameter/kendaraan/js/kendaraan.js'
                )
            );
            $this->add_external_css(
                array(
                    'assets/select2/css/select2.min.css',
                    'assets/parameter/kendaraan/css/kendaraan.css'
                )
            );
            $data = $this->includes;

            $content['akses'] = $akses;
            $content['addForm'] = $this->addForm();

            $data['title_menu'] = 'Master Kendaraan';
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        } 
    }

    public function getKaryawan($nama = null) {
        $sql_nama = null;
        if ( !empty( $nama ) ) {
            $sql_nama = "and k1.nama = '".$nama."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select k1.* from karyawan k1
            right join
                (select max(id) as id, nik from karyawan group by nik) k2
                on
                    k1.id = k2.id
            where
                k1.status = 1
                ".$sql_nama."
            order by
                k1.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getUnit() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                w1.kode,
                REPLACE(REPLACE(w1.nama, 'Kab ', ''), 'Kota ', '') as nama
            from wilayah w1
            right join
                (select max(id) as id, kode from wilayah group by kode) w2
                on
                    w1.id = w2.id
            where
                w1.jenis = 'UN'
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
            select p1.* from perusahaan p1
            right join
                (select max(id) as id, kode from perusahaan group by kode) p2
                on
                    p1.id = p2.id
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

    public function getData( $id, $tgl_serah_terima = null ) {
        $sql_serah_terima = null;
        if ( !empty( $tgl_serah_terima )) {
            $sql_serah_terima = "and kr.tgl_serah_terima = '".$tgl_serah_terima."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                k.id,
                prs.kode as kode_perusahaan,
                prs.perusahaan as nama_perusahaan,
                k.jenis as kode_jenis,
                case
                    when k.jenis = 'mobil' then
                        'MOBIL'
                    else
                        'SEPEDA MOTOR'
                end as jenis,
                k.nopol,
                k.tgl_pembelian,
                k.merk,
                k.tipe,
                k.warna,
                k.tahun,
                k.no_bpkb,
                k.no_stnk,
                k.masa_berlaku_stnk,
                k.pajak_tahun_ke2,
                k.pajak_tahun_ke3,
                k.pajak_tahun_ke4,
                k.pajak_tahun_ke5,
                kr.tgl_serah_terima,
                w_lama.kode as kode_unit_lama,
                w_lama.nama as nama_unit_lama,
                kry_lama.nik as kode_karyawan_lama,
                kry_lama.nama as nama_karyawan_lama,
                kry_lama.jabatan as jabatan_karyawan_lama,
                w_baru.kode as kode_unit_baru,
                w_baru.nama as nama_unit_baru,
                kry_baru.nik as kode_karyawan_baru,
                kry_baru.nama as nama_karyawan_baru,
                kry_baru.jabatan as jabatan_karyawan_baru,
                kr.keterangan
            from kendaraan_riwayat kr
            left join
                kendaraan k
                on
                    kr.id_header = k.id
            left join
                (
                    select k1.* from karyawan k1
                    right join
                        (select max(id) as id, nik from karyawan group by nik) k2
                        on
                            k1.id = k2.id
                ) kry_lama
                on
                    kr.pemegang_lama = kry_lama.nik
            left join
                (
                    select k1.* from karyawan k1
                    right join
                        (select max(id) as id, nik from karyawan group by nik) k2
                        on
                            k1.id = k2.id
                ) kry_baru
                on
                    kr.pemegang_baru = kry_baru.nik
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
                ) w_lama
                on
                    kr.unit_lama = w_lama.kode
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
                ) w_baru
                on
                    kr.unit_baru = w_baru.kode
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
                ".$sql_serah_terima."
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getLists() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                k.id,
                prs.alias as nama_perusahaan,
                case
                    when k.jenis = 'mobil' then
                        'MOBIL'
                    else
                        'SEPEDA MOTOR'
                end as jenis,
                k.nopol,
                w.nama as nama_unit,
                kry.nama as nama_karyawan,
                k.merk,
                k.tipe,
                k.warna,
                k.tahun
            from kendaraan k
            left join
                (
                    select kr1.* from kendaraan_riwayat kr1
                    right join
                        (select max(tgl_serah_terima) as tgl_serah_terima, id_header from kendaraan_riwayat group by id_header) kr2
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
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $content['data'] = $data;

        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }

    public function loadForm()
    {
        $params = $this->input->get('params');

        if ( isset($params['id']) && !empty($params['id']) ) {
            if ( isset($params['edit']) && !empty($params['edit']) ) {
                $html = $this->editForm( $params['id'] );
            } else {
                $html = $this->viewForm( $params['id'] );
            }
        } else {
            $html = $this->addForm();
        }

        echo $html;
    }

    public function addForm() {
        $content['jenis'] = $this->jenis;
        $content['karyawan'] = $this->getKaryawan();
        $content['unit'] = $this->getUnit();
        $content['perusahaan'] = $this->getPerusahaan();
        $html = $this->load->view($this->pathView.'addForm', $content, TRUE);

        return $html;
    }

    public function editForm( $id ) {
        $data = $this->getData( $id );

        $content['data'] = $data;
        $content['jenis'] = $this->jenis;
        $content['karyawan'] = $this->getKaryawan();
        $content['unit'] = $this->getUnit();
        $content['perusahaan'] = $this->getPerusahaan();
        $html = $this->load->view($this->pathView.'editForm', $content, TRUE);

        return $html;
    }

    public function viewForm( $id ) {
        $data = $this->getData( $id );

        $content['data'] = $data;

        $html = $this->load->view($this->pathView.'viewForm', $content, TRUE);

        return $html;
    }

    public function save() {
        $params = $this->input->post('params');

        try {
            $m_kendaraan = new \Model\Storage\Kendaraan_model();
            $m_kendaraan->perusahaan = $params['perusahaan'];
            $m_kendaraan->jenis = $params['jenis'];
            $m_kendaraan->tgl_pembelian = $params['tgl_pembelian'];
            $m_kendaraan->merk = $params['merk'];
            $m_kendaraan->tipe = $params['tipe'];
            $m_kendaraan->warna = $params['warna'];
            $m_kendaraan->tahun = $params['tahun'];
            $m_kendaraan->nopol = $params['nopol'];
            $m_kendaraan->no_bpkb = $params['no_bpkb'];
            $m_kendaraan->no_stnk = $params['no_stnk'];
            $m_kendaraan->masa_berlaku_stnk = $params['masa_berlaku_stnk'];
            $m_kendaraan->pajak_tahun_ke2 = !empty($params['pajak_tahun_ke2']) ? $params['pajak_tahun_ke2'] : null;
            $m_kendaraan->pajak_tahun_ke3 = !empty($params['pajak_tahun_ke3']) ? $params['pajak_tahun_ke3'] : null;
            $m_kendaraan->pajak_tahun_ke4 = !empty($params['pajak_tahun_ke4']) ? $params['pajak_tahun_ke4'] : null;
            $m_kendaraan->pajak_tahun_ke5 = !empty($params['pajak_tahun_ke5']) ? $params['pajak_tahun_ke5'] : null;
            $m_kendaraan->save();

            $id = $m_kendaraan->id;

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_kendaraan_riwayat = new \Model\Storage\KendaraanRiwayat_model();
                $m_kendaraan_riwayat->id_header = $id;
                $m_kendaraan_riwayat->tgl_serah_terima = $v_det['tgl_serah_terima'];
                $m_kendaraan_riwayat->pemegang_lama = $v_det['pemegang_lama'];
                $m_kendaraan_riwayat->unit_lama = $v_det['unit_lama'];
                $m_kendaraan_riwayat->pemegang_baru = $v_det['pemegang_baru'];
                $m_kendaraan_riwayat->unit_baru = $v_det['unit_baru'];
                $m_kendaraan_riwayat->keterangan = $v_det['keterangan'];
                $m_kendaraan_riwayat->save();
            }

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_kendaraan, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
            $this->result['content'] = array('id' => $id);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit() {
        $params = $this->input->post('params');

        try {
            $id = $params['id'];

            $m_kendaraan = new \Model\Storage\Kendaraan_model();
            $m_kendaraan->where('id', $id)->update(
                array(
                    'perusahaan' => $params['perusahaan'],
                    'jenis' => $params['jenis'],
                    'tgl_pembelian' => $params['tgl_pembelian'],
                    'merk' => $params['merk'],
                    'tipe' => $params['tipe'],
                    'warna' => $params['warna'],
                    'tahun' => $params['tahun'],
                    'nopol' => $params['nopol'],
                    'no_bpkb' => $params['no_bpkb'],
                    'no_stnk' => $params['no_stnk'],
                    'masa_berlaku_stnk' => $params['masa_berlaku_stnk'],
                    'pajak_tahun_ke2' => !empty($params['pajak_tahun_ke2']) ? $params['pajak_tahun_ke2'] : null,
                    'pajak_tahun_ke3' => !empty($params['pajak_tahun_ke3']) ? $params['pajak_tahun_ke3'] : null,
                    'pajak_tahun_ke4' => !empty($params['pajak_tahun_ke4']) ? $params['pajak_tahun_ke4'] : null,
                    'pajak_tahun_ke5' => !empty($params['pajak_tahun_ke5']) ? $params['pajak_tahun_ke5'] : null
                )
            );

            $m_kendaraan_riwayat = new \Model\Storage\KendaraanRiwayat_model();
            $m_kendaraan_riwayat->where('id_header', $id)->delete();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_kendaraan_riwayat = new \Model\Storage\KendaraanRiwayat_model();
                $m_kendaraan_riwayat->id_header = $id;
                $m_kendaraan_riwayat->tgl_serah_terima = $v_det['tgl_serah_terima'];
                $m_kendaraan_riwayat->pemegang_lama = $v_det['pemegang_lama'];
                $m_kendaraan_riwayat->unit_lama = $v_det['unit_lama'];
                $m_kendaraan_riwayat->pemegang_baru = $v_det['pemegang_baru'];
                $m_kendaraan_riwayat->unit_baru = $v_det['unit_baru'];
                $m_kendaraan_riwayat->keterangan = $v_det['keterangan'];
                $m_kendaraan_riwayat->save();
            }

            $d_kendaraan = $m_kendaraan->where('id', $id)->first();

            $deskripsi_log = 'di-edit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_kendaraan, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di edit.';
            $this->result['content'] = array('id' => $id);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete() {
        $params = $this->input->post('params');

        try {
            $id = $params['id'];

            $m_kk = new \Model\Storage\KreditKendaraan_model();
            $d_kk = $m_kk->where('kendaraan_id', $id)->first();

            if ( $d_kk ) {
                $this->result['message'] = 'Data sudah di gunakan pada transaksi kredit kendaraan.';
            } else {
                $m_kendaraan = new \Model\Storage\Kendaraan_model();
                $m_kr = new \Model\Storage\KendaraanRiwayat_model();

                $d_kendaraan = $m_kendaraan->where('id', $id)->first();

                $m_kendaraan->where('id', $id)->delete();
                $m_kr->where('id_header', $id)->delete();
    
                $deskripsi_log = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/delete', $d_kendaraan, $deskripsi_log);

                $this->result['status'] = 1;
                $this->result['message'] = 'Data berhasil di hapus.';
                $this->result['content'] = array('id' => $id);
            }

        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function cetakDokumenSerahTerima($params) {
        $this->load->library('PDFGenerator');

        $_params = json_decode(exDecrypt($params), true);

        $id = $_params['id'];
        $tgl_serah_terima = $_params['tgl_serah_terima'];

        $data = $this->getData( $id, $tgl_serah_terima )[0];
        $penanggung_jawab = $this->getKaryawan('marta sutanto')[0];

        // cetak_r( $data );
        // cetak_r( $penanggung_jawab, 1 );

        $content['tgl_serah_terima'] = strtoupper(tglIndonesia($tgl_serah_terima, '-', ' ', true));
        $content['tanggal'] = strtoupper(tglIndonesia(date('Y-m-d'), '-', ' ', true));
        $content['data'] = $data;
        $content['penanggung_jawab'] = $penanggung_jawab;
        $res_view_html = $this->load->view('parameter/kendaraan/dokumenSerahTerima', $content, true);

        $this->pdfgenerator->generate($res_view_html, "DOKUMEN_SERAH_TERIMA_KENDARAAN_", 'a4', 'portrait');
    }

    public function injekKendaraan() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                kode,
                perusahaan, 
                'mobil' as jenis, 
                tgl_jatuh_tempo as tgl_pembelian,
                case
                    when merk_jenis like '%daihatsu%' then
                        'DAIHATSU'
                    else
                        'TOYOTA'
                end as merk,
                UPPER(REPLACE(REPLACE(LOWER(merk_jenis), 'daihatsu ', ''), 'toyota ', '')) as tipe,
                warna,
                tahun,
                '-' as nopol,
                '-' as no_bpkb,
                '-' as no_stnk,
                null as masa_berlaku_stnk,
                null as pajak_tahun_ke2,
                null as pajak_tahun_ke3,
                null as pajak_tahun_ke4,
                null as pajak_tahun_ke5,
                tanggal as tgl_serah_terima,
                null as pemegang_lama,
                null as unit_lama,
                peruntukan as pemegang_baru,
                unit as unit_baru,
                null as keterangan
            from kredit_kendaraan kk 
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $m_kendaraan = new \Model\Storage\Kendaraan_model();
                $m_kendaraan->perusahaan = $value['perusahaan'];
                $m_kendaraan->jenis = $value['jenis'];
                $m_kendaraan->tgl_pembelian = $value['tgl_pembelian'];
                $m_kendaraan->merk = $value['merk'];
                $m_kendaraan->tipe = $value['tipe'];
                $m_kendaraan->warna = $value['warna'];
                $m_kendaraan->tahun = $value['tahun'];
                $m_kendaraan->nopol = $value['nopol'];
                $m_kendaraan->no_bpkb = $value['no_bpkb'];
                $m_kendaraan->no_stnk = $value['no_stnk'];
                $m_kendaraan->masa_berlaku_stnk = $value['masa_berlaku_stnk'];
                $m_kendaraan->pajak_tahun_ke2 = !empty($value['pajak_tahun_ke2']) ? $value['pajak_tahun_ke2'] : null;
                $m_kendaraan->pajak_tahun_ke3 = !empty($value['pajak_tahun_ke3']) ? $value['pajak_tahun_ke3'] : null;
                $m_kendaraan->pajak_tahun_ke4 = !empty($value['pajak_tahun_ke4']) ? $value['pajak_tahun_ke4'] : null;
                $m_kendaraan->pajak_tahun_ke5 = !empty($value['pajak_tahun_ke5']) ? $value['pajak_tahun_ke5'] : null;
                $m_kendaraan->save();

                $id = $m_kendaraan->id;

                $m_kendaraan_riwayat = new \Model\Storage\KendaraanRiwayat_model();
                $m_kendaraan_riwayat->id_header = $id;
                $m_kendaraan_riwayat->tgl_serah_terima = $value['tgl_serah_terima'];
                $m_kendaraan_riwayat->pemegang_lama = $value['pemegang_lama'];
                $m_kendaraan_riwayat->unit_lama = $value['unit_lama'];
                $m_kendaraan_riwayat->pemegang_baru = $value['pemegang_baru'];
                $m_kendaraan_riwayat->unit_baru = $value['unit_baru'];
                $m_kendaraan_riwayat->keterangan = $value['keterangan'];
                $m_kendaraan_riwayat->save();

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $m_kendaraan, $deskripsi_log);

                $m_kk = new \Model\Storage\KreditKendaraan_model();
                $m_kk->where('kode', $value['kode'])->update(
                    array(
                        'kendaraan_id' => $id
                    )
                );
            }
        }
    }
}