<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SewaKantor extends Public_Controller
{
    private $pathView = 'accounting/sewa_kantor/';
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
                'assets/accounting/sewa_kantor/js/sewa-kantor.js'
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/accounting/sewa_kantor/css/sewa-kantor.css'
            ));

            $data = $this->includes;

            $data['title_menu'] = 'Sewa Kantor';

            $content['add_form'] = $this->addForm();
            $content['riwayat'] = $this->riwayat();

            $content['akses'] = $this->hakAkses;
            $data['view'] = $this->load->view($this->pathView . 'index', $content, true);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getUnit() {
        $m_conf = new \Model\Storage\Conf();
		$sql = "
            select * from
            (
                select
                    w.kode,
                    REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', '') as nama
                from wilayah w
                right join
                    (select kode, max(id) as id from wilayah group by kode) w1
                    on
                        w.id = w1.id
                where
                    w.jenis = 'UN' and
                    w.kode is not null
            ) data
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

    public function getPerusahaan() {
        $m_conf = new \Model\Storage\Conf();
		$sql = "
            select * from
            (
                select
                    p.kode,
                    p.perusahaan as nama
                from perusahaan p
                right join
                    (select kode, max(id) as id from perusahaan group by kode) p1
                    on
                        p.id = p1.id
                where
                    p.aktif = 1
            ) data
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

    public function riwayat() {
        $content['unit'] = $this->getUnit();
        $html = $this->load->view($this->pathView . 'riwayat', $content, true);

        return $html;
    }

    public function addForm() {
        $content['unit'] = $this->getUnit();
        $content['perusahaan'] = $this->getPerusahaan();
        $html = $this->load->view($this->pathView . 'addForm', $content, true);

        return $html;
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

    public function getLists() {
        $params = $this->input->get('params');

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                p.nama as nama_perusahaan,
                w.nama as nama_unit,
                sk.nominal,
                sk.jangka_waktu,
                sk.mulai,
                sk.akhir,
                lt.deskripsi,
                lt.waktu
            from sewa_kantor sk
            left join
                (
                    select
                        p1.kode,
                        p1.perusahaan as nama
                    from perusahaan p1
                    right join
                        (select kode, max(id) as id from perusahaan group by kode) p2
                        on
                            p1.id = p2.id
                ) p
                on
                    sk.perusahaan = p.kode
            left join
                (
                    select
                        w1.kode,
                        REPLACE(REPLACE(w1.nama, 'Kota ', ''), 'Kab ', '') as nama
                    from wilayah w1
                    right join
                        (select kode, max(id) as id from wilayah group by kode) w2
                        on
                            w1.id = w2.id
                    where
                        w1.jenis = 'UN' and
                        w1.kode is not null
                ) w 
                on
                    sk.unit = w.kode
            left join
                (
                    select lt1.* from log_tables lt1 
                    right join
                        (select max(id) as id, tbl_name, tbl_id from log_tables where tbl_name = 'sewa_kantor' group by tbl_name, tbl_id) lt2
                        on
                            lt1.id = lt2.id
                ) lt
                on
                    sk.id = lt.tbl_id
            where
                sk.unit in ('".implode("', '", $params['unit'])."') and
                sk.id is not null
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list', $content, true);

        echo $html;
    }

    public function save() {
        $params = $this->input->post('params');

        try {
            $m_sk = new \Model\Storage\SewaKantor_model();
            $m_sk->perusahaan = $params['perusahaan'];
            $m_sk->unit = $params['unit'];
            $m_sk->jangka_waktu = $params['jangka_waktu'];
            $m_sk->mulai = $params['mulai'];
            $m_sk->akhir = $params['akhir'];
            $m_sk->nominal = $params['nominal'];
            $m_sk->nominal_per_bulan = ($params['jangka_waktu'] > 0 && $params['nominal'] > 0) ? $params['nominal'] / $params['jangka_waktu'] : 0;
            $m_sk->save();

            $m_conf = new \Model\Storage\Conf();
            $sql = "exec insert_jurnal 'OPERASIONAL', NULL, NULL, ".$params['nominal'].", 'sewa_kantor', ".$m_sk->id.", NULL, 1";
            $m_conf->hydrateRaw( $sql );

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_sk, $deskripsi_log );

            $this->result['status'] = 1;
			$this->result['message'] = 'Data berhasil di simpan.';
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

		display_json( $this->result );
    }

    public function tes() {
        $content['unit'] = $this->getUnit();
        $content['perusahaan'] = $this->getPerusahaan();

        cetak_r( $content );
    }
}