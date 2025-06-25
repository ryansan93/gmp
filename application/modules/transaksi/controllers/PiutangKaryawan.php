<?php defined('BASEPATH') or exit('No direct script access allowed');

class PiutangKaryawan extends Public_Controller
{
    private $pathView = 'transaksi/piutang_karyawan/';
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
                "assets/transaksi/piutang_karyawan/js/piutang-karyawan.js"
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/piutang_karyawan/css/piutang-karyawan.css"
            ));
            $data = $this->includes;

            $content['akses'] = $this->akses;
            $content['title_panel'] = 'Piutang Karyawan';

            $content['riwayat'] = $this->riwayat();
            $content['addForm'] = $this->addForm();

            // Load Indexx
            $data['title_menu'] = 'Piutang Karyawan';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getKaryawan() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                k1.id,
                k1.nik,
                k1.nama,
                k1.jabatan
            from karyawan k1
            right join
                (
                    select max(id) as id, nik from karyawan group by nik
                ) k2
                on
                    k1.id = k2.id
            where
                k1.status = 1
            order by
                k1.level asc,
                k1.nama asc
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

    public function loadForm()
    {
        $params = $this->input->get('params');
        $edit = $this->input->get('edit');

        $id = $params['id'];

        $content = array();
        $html = "url not found
        ";
        
        if ( !empty($id) && $edit != 'edit' ) {
            // NOTE: view data BASTTB (ajax)
            $html = $this->viewForm($id);
        } else if ( !empty($id) && $edit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $html = $this->editForm($id);
        }else{
            $html = $this->addForm();
        }

        echo $html;
    }

    public function getLists() {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                p.*,
                kry.nama as nama_karyawan,
                kry.jabatan,
                prs.perusahaan as nama_perusahaan
            from piutang p
            right join
                (
                    select k1.* from karyawan k1
                    right join
                        (
                            select max(id) as id, nik from karyawan group by nik
                        ) k2
                        on
                            k1.id = k2.id
                ) kry
                on
                    kry.nik = p.karyawan
            right join
                (
                    select 
                        p1.*
                    from perusahaan p1
                    right join
                        (
                            select max(id) as id, kode from perusahaan group by kode
                        ) p2
                        on
                            p1.id = p2.id
                ) prs
                on
                    prs.kode = p.perusahaan
            where
                p.tanggal between '".$start_date."' and '".$end_date."' and
                p.jenis = '".$this->jenis."'
            order by
                p.tanggal desc,
                kry.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list', $content, TRUE);

        echo $html;
    }

    public function riwayat() {
        $content['akses'] = $this->akses;
        $content['karyawan'] = $this->getKaryawan();
        $content['perusahaan'] = $this->getPerusahaan();
        $html = $this->load->view($this->pathView . 'riwayat', $content, TRUE);

        return $html;
    }

    public function addForm() {
        $content['karyawan'] = $this->getKaryawan();
        $content['perusahaan'] = $this->getPerusahaan();
        $html = $this->load->view($this->pathView . 'addForm', $content, TRUE);

        return $html;
    }

    public function viewForm( $id ) {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                p.*,
                kry.nama as nama_karyawan,
                kry.jabatan,
                prs.perusahaan as nama_perusahaan
            from piutang p
            right join
                (
                    select k1.* from karyawan k1
                    right join
                        (
                            select max(id) as id, nik from karyawan group by nik
                        ) k2
                        on
                            k1.id = k2.id
                ) kry
                on
                    kry.nik = p.karyawan
            right join
                (
                    select 
                        p1.*
                    from perusahaan p1
                    right join
                        (
                            select max(id) as id, kode from perusahaan group by kode
                        ) p2
                        on
                            p1.id = p2.id
                ) prs
                on
                    prs.kode = p.perusahaan
            where
                p.id = ".$id."
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );
        
        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray()[0];
        }

        $content['data'] = $data;
        $content['akses'] = $this->akses;
        $html = $this->load->view($this->pathView . 'viewForm', $content, TRUE);

        return $html;
    }

    public function editForm( $id ) {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                p.*,
                kry.nama as nama_karyawan,
                kry.jabatan,
                prs.perusahaan as nama_perusahaan
            from piutang p
            right join
                (
                    select k1.* from karyawan k1
                    right join
                        (
                            select max(id) as id, nik from karyawan group by nik
                        ) k2
                        on
                            k1.id = k2.id
                ) kry
                on
                    kry.nik = p.karyawan
            right join
                (
                    select 
                        p1.*
                    from perusahaan p1
                    right join
                        (
                            select max(id) as id, kode from perusahaan group by kode
                        ) p2
                        on
                            p1.id = p2.id
                ) prs
                on
                    prs.kode = p.perusahaan
            where
                p.id = ".$id."
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );
        
        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray()[0];
        }

        $content['data'] = $data;
        $content['karyawan'] = $this->getKaryawan();
        $content['perusahaan'] = $this->getPerusahaan();
        $html = $this->load->view($this->pathView . 'editForm', $content, TRUE);

        return $html;
    }

    public function save() {
        $data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['file']) ? $_FILES['file'] : [];

        try {
            $file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];
                if ($isMoved) {
                    $file_name = $moved['name'];
                    $path_name = $moved['path'];
                }
            }

            $m_pm = new \Model\Storage\Piutang_model();
            $kode = $m_pm->getNextId_piutang('PK');

            $m_pm->jenis = $this->jenis;
            $m_pm->kode = $kode;
            $m_pm->tanggal = $data['tanggal'];
            $m_pm->karyawan = $data['karyawan'];
            $m_pm->perusahaan = $data['perusahaan'];
            $m_pm->nominal = $data['nominal'];
            $m_pm->keterangan = $data['keterangan'];
            $m_pm->path = $path_name;
            $m_pm->tf_bank = 1;
            $m_pm->save();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal NULL, NULL, NULL, ".$data['nominal'].", 'piutang', ".$m_pm->id.", NULL, 1";

            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_pm, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
            $this->result['content'] = array(
                'id' => $m_pm->id
            );
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit() {
        $data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['file']) ? $_FILES['file'] : [];

        try {
            $id = $data['id'];

            $m_pm = new \Model\Storage\Piutang_model();
            $d_pm = $m_pm->where('id', $id)->first()->toArray();

            $path_name = $d_pm['path'];
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];
                if ($isMoved) {
                    $path_name = $moved['path'];
                }
            }

            $m_pm = new \Model\Storage\Piutang_model();
            $m_pm->where('id', $id)->update(
                array(
                    'tanggal' => $data['tanggal'],
                    'karyawan' => $data['karyawan'],
                    'perusahaan' => $data['perusahaan'],
                    'nominal' => $data['nominal'],
                    'keterangan' => $data['keterangan'],
                    'path' => $path_name
                )
            );

            $m_pm = new \Model\Storage\Piutang_model();
            $d_pm_new = $m_pm->where('id', $id)->first();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal NULL, NULL, NULL, ".$data['nominal'].", 'piutang', ".$id.", ".$id.", 2";

            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_pm_new, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di ubah.';
            $this->result['content'] = array(
                'id' => $id
            );
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete() {
        $params = $this->input->post('params');

        try {
            $id = $params['id'];

            $m_pm = new \Model\Storage\Piutang_model();
            $d_pm = $m_pm->where('id', $id)->first();

            $m_pm = new \Model\Storage\Piutang_model();
            $m_pm->where('id', $id)->delete();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal NULL, NULL, NULL, 0, 'piutang', ".$id.", ".$id.", 3";

            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_pm, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes() {
        $_kode = 'PK';

        cetak_r( strlen($_kode) );

        $m_pm = new \Model\Storage\Piutang_model();
        $kode = $m_pm->getNextId_piutang($_kode);

        cetak_r( $kode );
    }
}