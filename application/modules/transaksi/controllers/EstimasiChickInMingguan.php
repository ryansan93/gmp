<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EstimasiChickInMingguan extends Public_Controller {

    private $path = 'transaksi/estimasi_chick_in_mingguan/';
    private $url;
    private $akses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->akses = hakAkses($this->url);
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index($segment=0)
    {
        if ( $this->akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/transaksi/estimasi_chick_in_mingguan/js/estimasi-chick-in-mingguan.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/estimasi_chick_in_mingguan/css/estimasi-chick-in-mingguan.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->akses;

            // Load Indexx
            $data['title_menu'] = 'Estimasi Chick In Mingguan';
            $data['view'] = $this->load->view($this->path.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getPerusahaan() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                prs1.kode,
                prs1.perusahaan as nama
            from perusahaan prs1
            right join
                (select max(id) as id, kode from perusahaan group by kode) prs2
                on
                    prs1.id = prs2.id
            order by
                prs1.kode
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
            order by
                w1.kode
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
            select top 100
                est.*,
                prs.nama as nama_perusahaan,
                w.nama as nama_unit
            from estimasi_chick_in_mingguan est
            left join
                (
                    select
                        prs1.kode,
                        prs1.perusahaan as nama
                    from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    prs.kode = est.kode_perusahaan
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
                    where
                        w1.jenis = 'UN'
                ) w
                on
                    w.kode = est.kode_unit
            order by
                est.start_date desc,
                prs.nama asc,
                w.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $content['data'] = $data;
        
        $html = $this->load->view($this->path.'list', $content);

        return $html;
    }

    public function addForm() {
        $content['perusahaan'] = $this->getPerusahaan();
        $content['unit'] = $this->getUnit();

        $html = $this->load->view($this->path.'addForm', $content);

        return $html;
    }

    public function editForm() {
        $params = $this->input->get('params');

        $id = $params['id'];

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select top 100
                est.*,
                prs.nama as nama_perusahaan,
                w.nama as nama_unit
            from estimasi_chick_in_mingguan est
            left join
                (
                    select
                        prs1.kode,
                        prs1.perusahaan as nama
                    from perusahaan prs1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    prs.kode = est.kode_perusahaan
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
                    where
                        w1.jenis = 'UN'
                ) w
                on
                    w.kode = est.kode_unit
            where
                est.id = ".$id."
            order by
                est.start_date desc,
                prs.nama asc,
                w.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray()[0];
        }

        $content['data'] = $data;
        $content['perusahaan'] = $this->getPerusahaan();
        $content['unit'] = $this->getUnit();

        $html = $this->load->view($this->path.'editForm', $content);

        return $html;
    }

    public function save() {
        $params = $this->input->post('params');

        try {
            $m_est = new \Model\Storage\EstimasiChickInMingguan_model();
            $m_est->start_date = $params['start_date'];
            $m_est->end_date = $params['end_date'];
            $m_est->kode_perusahaan = $params['perusahaan'];
            $m_est->kode_unit = $params['unit'];
            $m_est->jumlah = $params['jumlah'];
            $m_est->save();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_est, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit() {
        $params = $this->input->post('params');

        try {
            $m_est = new \Model\Storage\EstimasiChickInMingguan_model();
            $m_est->where('id', $params['id'])->update(
                array(
                    'start_date' => $params['start_date'],
                    'end_date' => $params['end_date'],
                    'kode_perusahaan' => $params['perusahaan'],
                    'kode_unit' => $params['unit'],
                    'jumlah' => $params['jumlah'],
                )
            );

            $d_est = $m_est->where('id', $params['id'])->first();

            $deskripsi_log = 'di-edit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_est, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di edit.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete() {
        $params = $this->input->post('params');

        try {
            $m_est = new \Model\Storage\EstimasiChickInMingguan_model();
            $d_est = $m_est->where('id', $params['id'])->first();

            $m_est->where('id', $params['id'])->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_est, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}