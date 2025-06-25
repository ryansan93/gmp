<?php defined('BASEPATH') or exit('No direct script access allowed');

class PiutangMitra extends Public_Controller
{
    private $pathView = 'transaksi/piutang_mitra/';
    private $jenis = 'mitra';
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
                "assets/transaksi/piutang_mitra/js/piutang-mitra.js"
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/piutang_mitra/css/piutang-mitra.css"
            ));
            $data = $this->includes;

            $content['akses'] = $this->akses;
            $content['title_panel'] = 'Piutang Plasma';

            $content['riwayat'] = $this->riwayat();
            $content['addForm'] = $this->addForm();

            // Load Indexx
            $data['title_menu'] = 'Piutang Plasma';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getMitra() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                m1.id,
                m1.nomor,
                m1.nama,
                w.kode,
                prs.kode_auto as kode_perusahaan
            from mitra m1
            right join
                (
                    select max(id) as id, nomor from mitra group by nomor
                ) m2
                on
                    m1.id = m2.id
            left join
                mitra_mapping mm
                on
                    mm.mitra = m1.id
            left join
                (
                    select kdg1.* from kandang kdg1
                    right join
                        (select max(id) as id, mitra_mapping from kandang group by mitra_mapping) kdg2
                        on
                            kdg1.id = kdg2.id
                ) kdg
                on
                    kdg.mitra_mapping = mm.id
            left join
                wilayah w
                on
                    w.id = kdg.unit
            left join
                (
                    select prs1.* from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    m1.perusahaan = prs.kode
            where
                    m1.mstatus = 1
            order by
                w.kode asc,
                m1.nama asc
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
                mtr.nama as nama_mitra,
                mtr.kode_unit,
                prs.perusahaan as nama_perusahaan
            from piutang p
            right join
                (
                    select m1.*, w.kode as kode_unit from mitra m1
                    right join
                        (
                            select max(id) as id, nomor from mitra group by nomor
                        ) m2
                        on
                            m1.id = m2.id
                    left join
                        mitra_mapping mm
                        on
                            mm.mitra = m1.id
                    left join
                        (
                            select kdg1.* from kandang kdg1
                            right join
                                (select max(id) as id, mitra_mapping from kandang group by mitra_mapping) kdg2
                                on
                                    kdg1.id = kdg2.id
                        ) kdg
                        on
                            kdg.mitra_mapping = mm.id
                    left join
                        wilayah w
                        on
                            w.id = kdg.unit
                ) mtr
                on
                    mtr.nomor = p.mitra
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
                mtr.nama asc
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
        $content['mitra'] = $this->getMitra();
        $content['perusahaan'] = $this->getPerusahaan();
        $html = $this->load->view($this->pathView . 'riwayat', $content, TRUE);

        return $html;
    }

    public function addForm() {
        $content['mitra'] = $this->getMitra();
        $content['perusahaan'] = $this->getPerusahaan();
        $html = $this->load->view($this->pathView . 'addForm', $content, TRUE);

        return $html;
    }

    public function viewForm( $id ) {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                p.*,
                mtr.nama as nama_mitra,
                mtr.kode_unit,
                prs.perusahaan as nama_perusahaan
            from piutang p
            right join
                (
                    select m1.*, w.kode as kode_unit from mitra m1
                    right join
                        (
                            select max(id) as id, nomor from mitra group by nomor
                        ) m2
                        on
                            m1.id = m2.id
                    left join
                        mitra_mapping mm
                        on
                            mm.mitra = m1.id
                    left join
                        (
                            select kdg1.* from kandang kdg1
                            right join
                                (select max(id) as id, mitra_mapping from kandang group by mitra_mapping) kdg2
                                on
                                    kdg1.id = kdg2.id
                        ) kdg
                        on
                            kdg.mitra_mapping = mm.id
                    left join
                        wilayah w
                        on
                            w.id = kdg.unit
                ) mtr
                on
                    mtr.nomor = p.mitra
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
                mtr.nama as nama_mitra,
                mtr.kode_unit,
                prs.perusahaan as nama_perusahaan
            from piutang p
            right join
                (
                    select m1.*, w.kode as kode_unit from mitra m1
                    right join
                        (
                            select max(id) as id, nomor from mitra group by nomor
                        ) m2
                        on
                            m1.id = m2.id
                    left join
                        mitra_mapping mm
                        on
                            mm.mitra = m1.id
                    left join
                        (
                            select kdg1.* from kandang kdg1
                            right join
                                (select max(id) as id, mitra_mapping from kandang group by mitra_mapping) kdg2
                                on
                                    kdg1.id = kdg2.id
                        ) kdg
                        on
                            kdg.mitra_mapping = mm.id
                    left join
                        wilayah w
                        on
                            w.id = kdg.unit
                ) mtr
                on
                    mtr.nomor = p.mitra
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
        $content['mitra'] = $this->getMitra();
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
            $kode = $m_pm->getNextId_piutang('PM');

            $m_pm->jenis = $this->jenis;
            $m_pm->kode = $kode;
            $m_pm->tanggal = $data['tanggal'];
            $m_pm->mitra = $data['mitra'];
            $m_pm->perusahaan = $data['perusahaan'];
            $m_pm->nominal = $data['nominal'];
            $m_pm->keterangan = $data['keterangan'];
            $m_pm->path = $path_name;
            $m_pm->tf_bank = $data['tf_bank'];
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
                    'mitra' => $data['mitra'],
                    'perusahaan' => $data['perusahaan'],
                    'nominal' => $data['nominal'],
                    'keterangan' => $data['keterangan'],
                    'path' => $path_name,
                    'tf_bank' => $data['tf_bank'],
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

    public function pindahPerusahaanForm() {
        $id = $this->input->get('id');

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                prs_asal.perusahaan as nama_perusahaan_asal,
                mtr_asal.nomor as kode_mitra_asal,
                mtr_asal.nama as nama_mitra_asal,
                prs_tujuan.perusahaan as nama_perusahaan_tujuan,
                mtr_tujuan.nomor as kode_mitra_tujuan,
                mtr_tujuan.nama as nama_mitra_tujuan,
                p.nominal as nominal_piutang,
                isnull(bayar.nominal, 0) as nominal_bayar,
                (p.nominal - isnull(bayar.nominal, 0)) as sisa_piutang
            from piutang p
            left join
                mitra_pindah_perusahaan mpp
                on
                    mpp.nomor_asal = p.mitra
            left join
                (
                    select mtr1.* from mitra mtr1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) mtr2
                        on
                            mtr1.id = mtr2.id
                ) mtr_asal
                on
                    mtr_asal.nomor = mpp.nomor_asal
            left join
                (
                    select mtr1.* from mitra mtr1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) mtr2
                        on
                            mtr1.id = mtr2.id
                ) mtr_tujuan
                on
                    mtr_tujuan.nomor = mpp.nomor_tujuan
            left join
                (
                    select prs1.* from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs_asal
                on
                    mtr_asal.perusahaan = prs_asal.kode
            left join
                (
                    select prs1.* from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs_tujuan
                on
                    mtr_tujuan.perusahaan = prs_tujuan.kode
            left join
                (
                    select
                        data.piutang_kode,
                        sum(data.nominal) as nominal
                    from (
                        select piutang_kode, sum(nominal) as nominal from bayar_piutang group by piutang_kode

                        union all

                        select piutang_kode, sum(nominal) as nominal from rhpp_piutang group by piutang_kode

                        union all

                        select piutang_kode, sum(nominal) as nominal from rhpp_group_piutang group by piutang_kode
                    ) data
                    group by
                        data.piutang_kode
                ) bayar
                on
                    p.kode = bayar.piutang_kode
            where 
                p.id = ".$id."

        ";
        $d_conf = $m_conf->hydrateRaw( $sql );
        
        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray()[0];
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'pindahPerusahaanForm', $content, TRUE);

        echo $html;
    }

    public function tes() {
        $_kode = 'PM';

        cetak_r( strlen($_kode) );

        $m_pm = new \Model\Storage\Piutang_model();
        $kode = $m_pm->getNextId_piutang($_kode);

        cetak_r( $kode );
    }
}