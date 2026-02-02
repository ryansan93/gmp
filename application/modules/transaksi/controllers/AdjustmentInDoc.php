<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AdjustmentInDoc extends Public_Controller {

    private $path = 'transaksi/adjustment_in_doc/';
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
                "assets/transaksi/adjustment_in_doc/js/adjustment-in-doc.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/adjustment_in_doc/css/adjustment-in-doc.css",
            ));

            $data = $this->includes;

            // $mitra = $this->getMitra();
            // $peralatan = $this->get_peralatan();

            $content['akses'] = $this->akses;

            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();

            // Load Indexx
            $data['title_menu'] = 'Adjustment In DOC';
            $data['view'] = $this->load->view($this->path.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getPlasma() {
        $search = $this->input->get('search');
        $type = $this->input->get('type');
        $all = $this->input->get('all');

        $sql_condition = null;
        $sql_search = "";
        if ( !empty($search) && !empty($type) ) {
            $sql_search = "where data.text like '%".$search."%'";
        }

        $sql_all = "";
        if ( !empty($all) ) {
            $sql_all = "
                select
                    'all' as id,
                    UPPER('ALL') as text,
                    null as nim,
                    'all' as nama,
                    null as unit

                union all
            ";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from
            (
                ".$sql_all."

                select
                    m.nomor as id,
                    UPPER(w.kode+' | '+m.nama) as text,
                    mm.nim,
                    m.nama,
                    w.kode as unit
                from mitra m
                left join
                    (
                        select mm1.* from mitra_mapping mm1
                        right join
                            (select max(id) as id, nim from mitra_mapping group by nim) mm2
                            on
                                mm1.id = mm2.id
                    ) mm
                    on
                        m.id = mm.mitra
                left join
                    kandang k
                    on
                        mm.id = k.mitra_mapping
                left join
                    wilayah w
                    on
                        w.id = k.unit
                where
                    mm.id is not null and
	                m.mstatus <> 0
                group by
                    m.nomor,
                    mm.nim,
                    m.nama,
                    w.kode
            ) data
            ".$sql_search."
            order by
                data.unit asc,
                data.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }
        
        echo json_encode($data);
    }

    public function getNoreg() {
        $search = $this->input->get('search');
        $type = $this->input->get('type');
        $mitra = $this->input->get('mitra');

        $sql_condition = null;
        $sql_search = "";
        if ( !empty($search) && !empty($type) ) {
            $sql_search = "where data.text like '%".$search."%'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from
            (   
                select
                    data.noreg as id,
                    UPPER(data.tanggal+' | '+data.noreg) as text,
                    data.*
                from
                (
                    select
                        rs.noreg,
                        mm.nim,
                        m.nama,
                        w.kode as unit,
                        case
                            when td.id is not null then
                                CONVERT(VARCHAR(10), td.datang, 103)
                            else
                                CONVERT(VARCHAR(10), rs.tgl_docin, 103)
                        end as tanggal
                    from rdim_submit rs
                    left join
                        (
                            select mm1.* from mitra_mapping mm1
                            right join
                                (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                on
                                    mm1.id = mm2.id
                        ) mm
                        on
                            rs.nim = mm.nim
                    left join
                        mitra m
                        on
                            m.id = mm.mitra
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
                    left join
                        wilayah w
                        on
                            w.id = k.unit
                    where
                        m.nomor = '".$mitra."'
                ) data     
            ) data
            ".$sql_search."
            order by
                data.unit asc,
                data.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }
        
        echo json_encode($data);
    }

    public function getBarang()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "select * from barang b where tipe = 'doc'";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $sql_query_plasma = null;
        if (  stristr($params['mitra'], 'all') === FALSE  ) {
            $sql_query_plasma = "and m.nomor = '".$params['mitra']."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                adjin.*, m.nama as nama_mitra
            from adjin_doc adjin
            left join
                rdim_submit rs
                on
                    adjin.noreg = rs.noreg
            left join
                (
                    select mm1.* from mitra_mapping mm1
                    right join
                        (select max(id) as id, nim from mitra_mapping group by nim) mm2
                        on
                            mm1.id = mm2.id
                ) mm
                on
                    rs.nim = mm.nim
            left join
                mitra m
                on
                    mm.mitra = m.id
            where
                adjin.tanggal between '".$params['start_date']."' and '".$params['end_date']."'
                ".$sql_query_plasma."
        ";
        $d_adjin = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_adjin->count() > 0 ) {
            $data = $d_adjin->toArray();
        }

        $content['data'] = $data;

        $html = $this->load->view($this->path.'list', $content, TRUE);

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

    public function riwayat()
    {
        $content = null;
        $html = $this->load->view($this->path.'riwayat', $content, TRUE);

        return $html;
    }

    public function addForm()
    {
        $html = null;

        // $content['gudang'] = $this->getGudang();
        $content['barang'] = $this->getBarang();

        $html = $this->load->view($this->path.'addForm', $content, TRUE);

        return $html;
    }

    public function viewForm($id)
    {
        $html = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                adjin.*, m.nama as nama_mitra, brg.nama as nama_barang 
            from adjin_doc adjin
            left join
                (
                    select m1.* from mitra m1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) m2
                        on
                            m1.id = m2.id
                ) m
                on
                    m.nomor = adjin.mitra
            left join
                (
                    select b.* from barang b 
                    right join
                        (select max(id) as id, kode from barang group by kode) brg
                        on
                            b.id = brg.id
                ) brg
                on
                    brg.kode = adjin.kode_barang
            where
                adjin.id = ".$id."
        ";
        $d_adjin = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_adjin->count() > 0 ) {
            $data = $d_adjin->toArray()[0];
        }

        $content['akses'] = $this->akses;
        $content['data'] = $data;

        $html = $this->load->view($this->path.'viewForm', $content, TRUE);

        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_adjin = new \Model\Storage\AdjinDoc_model();
            $now = $m_adjin->getDate();

            $kode = $m_adjin->getNextId();

            $m_adjin->kode = $kode;
            $m_adjin->tanggal = $params['tgl_adjust'];
            $m_adjin->mitra = $params['mitra'];
            $m_adjin->noreg = $params['noreg'];
            $m_adjin->kode_barang = $params['barang'];
            $m_adjin->harga = $params['harga'];
            $m_adjin->jumlah = $params['jumlah'];
            $m_adjin->keterangan = $params['keterangan'];
            $m_adjin->save();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_adjin, $deskripsi_log);

            $this->result['status'] = 1;
            // $this->result['content'] = array('id' => $m_adjin->id);
            $this->result['content'] = array(
                'id' => $m_adjin->id,
                'tanggal' => $params['tgl_adjust'],
                'status' => 2,
                'message' => 'Data berhasil di simpan.',
                'noreg' => $params['noreg']
            );
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {
            $m_adjin = new \Model\Storage\AdjinDoc_model();
            $d_adjin = $m_adjin->where('id', $params['id'])->first();

            $m_adjin->where('id', $params['id'])->delete();

            $tanggal = $d_adjin->tanggal;
            $noreg = $d_adjin->noreg;

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_adjin, $deskripsi_log);

            $this->result['status'] = 1;
            // $this->result['content'] = array('id' => $m_adjin->id);
            $this->result['content'] = array(
                'id' => $params['id'],
                'tanggal' => $tanggal,
                'status' => 3,
                'message' => 'Data berhasil di hapus.',
                'noreg' => $noreg
            );
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function execHitStokSiklus() {
        $params = $this->input->post('params');

        try {
            $id = $params['id'];
            $tanggal = substr($params['tanggal'], 0, 10);
            $status = $params['status'];
            $noreg = $params['noreg'];

            $conf = new \Model\Storage\Conf();
            $sql = "EXEC hitung_stok_siklus 'doc', 'adjin_doc', '".$id."', '".$tanggal."', ".$status.", ".$noreg.", null";
            $d_conf = $conf->hydrateRaw($sql);

            $this->result['status'] = 1;
            $this->result['content'] = $params;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function execInsertJurnal() {
        $params = $this->input->post('params');

        try {
            $id = $params['id'];
            $id_old = $params['id'];
            $status = $params['status'];

            Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id_old, $status);

            $this->result['status'] = 1;
            $this->result['content'] = $params;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}