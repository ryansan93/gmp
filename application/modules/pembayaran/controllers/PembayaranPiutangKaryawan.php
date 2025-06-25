<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PembayaranPiutangKaryawan extends Public_Controller
{
    private $pathView = 'pembayaran/pembayaran_piutang_karyawan/';
    private $url;
    private $hakAkses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->hakAkses = hakAkses($this->url);
    }

    public function index()
    {
        if ( $this->hakAkses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                'assets/pembayaran/pembayaran_piutang_karyawan/js/pembayaran-piutang-karyawan.js'
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/pembayaran/pembayaran_piutang_karyawan/css/pembayaran-piutang-karyawan.css'
            ));

            $data = $this->includes;

            $content['add_form'] = $this->addForm();
            $content['riwayat'] = $this->riwayat();
            
            $data['title_menu'] = 'Pembayaran Piutang Karyawan';
            $data['view'] = $this->load->view($this->pathView.'index', $content, true);

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
        $html = "url not found";
        
        if ( !empty($id) && $edit != 'edit' ) {
            // NOTE: view data BASTTB (ajax)
            $html = $this->viewForm( $id );
        } else if ( !empty($id) && $edit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $html = $this->editForm( $id );
        }else{
            $html = $this->addForm();
        }

        echo $html;
    }

    public function getKodePiutang() {
        $params = $this->input->post('params');

        try {
            $karyawan = $params['karyawan'];
            $perusahaan = $params['perusahaan'];
            $piutang_kode = isset($params['piutang_kode']) ? $params['piutang_kode'] : null;

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select data.* from 
                (
                    select 
                        p.tanggal,
                        p.kode,
                        (p.nominal - isnull(bp.nominal, 0)) as sisa_piutang
                    from piutang p
                    left join
                        (
                            select
                                sum(data.nominal) as nominal,
                                data.piutang_kode
                            from (
                                select sum(nominal) as nominal, piutang_kode from bayar_piutang group by piutang_kode
                                
                                union all

                                select sum(nominal) as nominal, piutang_kode from rhpp_piutang group by piutang_kode

                                union all

                                select sum(nominal) as nominal, piutang_kode from rhpp_group_piutang group by piutang_kode
                            ) data
                            group by
                                data.piutang_kode
                        ) bp
                        on
                            p.kode = bp.piutang_kode
                    where
                        p.nominal > isnull(bp.nominal, 0) and
                        p.karyawan = '".$karyawan."' and
                        p.perusahaan = '".$perusahaan."' and
                        p.kode not in ('".$piutang_kode."')

                    union all

                    select 
                        p.tanggal,
                        p.kode,
                        (p.nominal - isnull(bp.nominal, 0)) as sisa_piutang
                    from piutang p
                    left join
                        (select sum(nominal) as nominal, piutang_kode from bayar_piutang group by piutang_kode) bp
                        on
                            p.kode = bp.piutang_kode
                    where
                        p.kode in ('".$piutang_kode."')
                ) data
                order by
                    data.tanggal asc,
                    data.kode asc
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $data = array();
            if ( $d_conf->count() > 0 ) {
                $data = $d_conf->toArray();
            }

            $this->result['status'] = 1;
            $this->result['content'] = $data;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function getLists() {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                bp.*,
                p.tanggal as tgl_piutang,
                kry.nama as nama_karyawan,
                kry.jabatan,
                prs.perusahaan as nama_perusahaan
            from bayar_piutang bp
            right join
                piutang p
                on
                    bp.piutang_kode = p.kode
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
                    kry.nik = bp.karyawan
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
                    prs.kode = bp.perusahaan
            where
                bp.tanggal between '".$start_date."' and '".$end_date."'
            order by
                bp.tanggal desc,
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

    public function getData( $id ) {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                bp.*,
                p.tanggal as tgl_piutang,
                kry.nama as nama_karyawan,
                kry.jabatan,
                prs.perusahaan as nama_perusahaan
            from bayar_piutang bp
            right join
                piutang p
                on
                    bp.piutang_kode = p.kode
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
                    kry.nik = bp.karyawan
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
                    prs.kode = bp.perusahaan
            where
                bp.id = ".$id."
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );
        
        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray()[0];
        }

        return $data;
    }

    public function riwayat() {
        $content['akses'] = $this->hakAkses;
        $content['karyawan'] = $this->getKaryawan();
        $content['perusahaan'] = $this->getPerusahaan();
        $html =  $this->load->view($this->pathView.'riwayat', $content, true);

        return $html;
    }

    public function addForm() {
        $content['karyawan'] = $this->getKaryawan();
        $content['perusahaan'] = $this->getPerusahaan();
        $html =  $this->load->view($this->pathView.'addForm', $content, true);

        return $html;
    }

    public function viewForm( $id ) {
        $content['data'] = $this->getData( $id );
        $content['akses'] = $this->hakAkses;
        $html = $this->load->view($this->pathView . 'viewForm', $content, TRUE);

        return $html;
    }

    public function editForm( $id ) {
        $content['data'] = $this->getData( $id );
        $content['karyawan'] = $this->getKaryawan();
        $content['perusahaan'] = $this->getPerusahaan();
        $html =  $this->load->view($this->pathView.'editForm', $content, true);

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

            $jenis = 'karyawan';

            $m_bpm = new \Model\Storage\BayarPiutang_model();
            $kode = $m_bpm->getNextId_bpiutang('BPK');

            $m_bpm->kode = $kode;
            $m_bpm->piutang_kode = $data['piutang_kode'];
            $m_bpm->tanggal = $data['tanggal'];
            $m_bpm->karyawan = $data['karyawan'];
            $m_bpm->perusahaan = $data['perusahaan'];
            $m_bpm->sisa_piutang = $data['sisa_piutang'];
            $m_bpm->nominal = $data['nominal'];
            $m_bpm->keterangan = $data['keterangan'];
            $m_bpm->path = $path_name;
            $m_bpm->jns_bayar = $data['jns_bayar'];
            $m_bpm->save();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal NULL, NULL, NULL, ".$data['nominal'].", 'bayar_piutang', ".$m_bpm->id.", NULL, 1";

            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_bpm, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
            $this->result['content'] = array(
                'id' => $m_bpm->id
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

            $m_bpm = new \Model\Storage\BayarPiutang_model();
            $d_bpm = $m_bpm->where('id', $id)->first()->toArray();

            $path_name = $d_bpm['path'];
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];
                if ($isMoved) {
                    $path_name = $moved['path'];
                }
            }

            $m_bpm = new \Model\Storage\BayarPiutang_model();
            $m_bpm->where('id', $id)->update(
                array(
                    'piutang_kode' => $data['piutang_kode'],
                    'tanggal' => $data['tanggal'],
                    'karyawan' => $data['karyawan'],
                    'perusahaan' => $data['perusahaan'],
                    'sisa_piutang' => $data['sisa_piutang'],
                    'nominal' => $data['nominal'],
                    'keterangan' => $data['keterangan'],
                    'path' => $path_name,
                    'jns_bayar' => $data['jns_bayar']
                )
            );

            $m_bpm = new \Model\Storage\BayarPiutang_model();
            $d_bpm_new = $m_bpm->where('id', $id)->first();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal NULL, NULL, NULL, ".$data['nominal'].", 'bayar_piutang', ".$id.", ".$id.", 2";

            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_bpm_new, $deskripsi_log );

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

            $m_bpm = new \Model\Storage\BayarPiutang_model();
            $d_bpm = $m_bpm->where('id', $id)->first();

            $m_bpm = new \Model\Storage\BayarPiutang_model();
            $m_bpm->where('id', $id)->delete();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal NULL, NULL, NULL, 0, 'bayar_piutang', ".$id.", ".$id.", 3";

            $d_conf = $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_bpm, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json( $this->result );
    }
}