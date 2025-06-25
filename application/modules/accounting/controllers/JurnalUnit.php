<?php defined('BASEPATH') OR exit('No direct script access allowed');

class JurnalUnit extends Public_Controller
{
    private $pathView = 'accounting/jurnal_unit/';
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
                'assets/accounting/jurnal_unit/js/jurnal-unit.js'
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/accounting/jurnal_unit/css/jurnal-unit.css'
            ));

            $data = $this->includes;

            $data['title_menu'] = 'Jurnal Unit';

            $content['add_form'] = $this->addForm();
            $content['riwayat'] = $this->riwayat();

            $content['akses'] = $this->hakAkses;
            $data['view'] = $this->load->view($this->pathView . 'index', $content, true);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $unit = $params['unit'];

        $sql_unit = "";
        if ( $unit != 'all' ) {
            $sql_unit = "and dj.unit = '".$unit."'";
        }

        $m_jurnal = new \Model\Storage\Jurnal_model();
        $sql = "
            select
                dj.id_header,
                dj.id,
                dj.tanggal,
                djt.nama,
                prs.perusahaan,
                dj.asal,
                dj.tujuan,
                case
                    when dj.unit like 'all' then
                        'ALL'
                    when dj.unit like 'pusat_gml' then
                        'pusat gemilang'
                    when dj.unit like 'pusat' then
                        'pusat gemuk'
                    when dj.unit like 'pusat_ma' then
                        'pusat ma'
                    when dj.unit like 'pusat_mv' then
                        'pusat mv'
                    else
                        REPLACE(REPLACE(w.nama, 'Kab ', ''), 'Kota ', '')
                end as nama_unit,
                LTRIM(RTRIM(cast(dj.keterangan as varchar(max)))) as keterangan,
                dj.nominal
            from det_jurnal dj
            left join
                det_jurnal_trans djt
                on
                    djt.id = dj.det_jurnal_trans_id
            left join
                jurnal_trans jt
                on
                    jt.id = djt.id_header
            left join
                (
                    select prs1.* from perusahaan prs1
                    right join
                        (
                            select max(id) as id, kode from perusahaan group by kode
                        ) prs2
                        on
                            prs1.id = prs2.id
                ) prs
                on
                    prs.kode = dj.perusahaan
            left join
                (
                    select w1.* from wilayah w1
                    right join
                        (select max(id) as id, kode from wilayah group by kode) w2
                        on
                            w1.id = w2.id
                ) w
                on
                    w.kode = dj.unit
            where
                dj.tanggal between '".$start_date."' and '".$end_date."' and
                jt.unit = 1
                ".$sql_unit."
            order by
                dj.tanggal asc,
                djt.nama asc
        ";
        $d_jurnal = $m_jurnal->hydrateRaw( $sql );
        
        // $d_jurnal = null;
        // if ( $unit != 'all' ) {
        //     $d_jurnal = $m_jurnal->whereBetween('tanggal', [$start_date, $end_date])->where('unit', $unit)->orderBy('tanggal', 'desc')->with(['jurnal_trans', 'detail'])->get();
        // } else {
        //     $d_jurnal = $m_jurnal->whereBetween('tanggal', [$start_date, $end_date])->orderBy('tanggal', 'desc')->with(['jurnal_trans', 'detail'])->get();
        // }

        $data = null;
        if ( $d_jurnal->count() > 0 ) {
            $data = $d_jurnal->toArray();

            // foreach ($d_jurnal as $k_jurnal => $v_jurnal) {
            //     if ( $v_jurnal['jurnal_trans']['unit'] == 1 ) {
            //         $data[] = $v_jurnal;
            //     }
            // }
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list', $content, true);

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
            $html = $this->viewForm($id);
        } else if ( !empty($id) && $edit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $html = $this->editForm($id);
        }else{
            $html = $this->addForm();
        }

        echo $html;
    }

    public function getUnit()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'UN')->orderBy('nama', 'asc')->get();

        $data = null;
        if ( $d_wilayah->count() > 0 ) {
            $d_wilayah = $d_wilayah->toArray();

            foreach ($d_wilayah as $k_wil => $v_wil) {
                $nama = trim(str_replace('KAB ', '', str_replace('KOTA ', '', strtoupper($v_wil['nama']))));
                $data[ $nama.' - '.$v_wil['kode'] ] = array(
                    'nama' => $nama,
                    'kode' => $v_wil['kode']
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function getJurnalTrans()
    {
        $m_jt = new \Model\Storage\JurnalTrans_model();
        $d_jt = $m_jt->where('unit', 1)->where('mstatus', 1)->orderBy('nama', 'asc')->with(['detail', 'sumber_tujuan'])->get();

        $data = null;
        if ( $d_jt->count() > 0 ) {
            $data = $d_jt->toArray();
        }

        return $data;
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

    public function getSumberTujuanCoa()
    {
        $params = $this->input->post('params');

        // cetak_r( $params, 1 );
        try {
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select * from det_jurnal_trans djt
                where
                    id = ".$params."
            ";
            $d_djt = $m_conf->hydrateRaw( $sql );

            $data = null;
            if ( $d_djt->count() > 0 ) {
                $d_djt = $d_djt->toArray()[0];

                $data = array(
                    'sumber' => $d_djt['sumber'],
                    'sumber_coa' => $d_djt['sumber_coa'],
                    'tujuan' => $d_djt['tujuan'],
                    'tujuan_coa' => $d_djt['tujuan_coa'],
                );
            }

            $this->result['status'] = 1;
            $this->result['content'] = $data;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function getNoreg()
    {
        $params = $this->input->post('params');

        try {
            $m_conf = new \Model\Storage\Conf();
            $now = $m_conf->getDate();

            $unit = $params['unit'];

            $sql_unit = "";
            if ( $unit != 'all' ) {
                $sql_unit = "and w.kode = '".$unit."'";
            }

            $tgl_min = prev_date( $now['tanggal'], 90 );

            $sql = "
                select 
                    rs.noreg,
                    CONVERT(VARCHAR(10), td.datang, 103) as tgl_terima,
                    cast(SUBSTRING(rs.noreg, LEN(rs.noreg)-1, 2) as int) as kandang,
                    m.nama as nama_mitra
                from rdim_submit rs
                right join
                    kandang k
                    on
                        k.id = rs.kandang
                right join
                    wilayah w
                    on
                        k.unit = w.id
                right join
                    order_doc od
                    on
                        rs.noreg = od.noreg
                right join
                    terima_doc td
                    on
                        od.no_order = td.no_order
                right join
                    (
                        select mm1.* from mitra_mapping mm1
                        right join
                            (select max(id) as id, nim from mitra_mapping group by nim) mm2
                            on
                                mm1.id = mm2.id
                    ) mm
                    on
                        rs.nim = mm.nim
                right join
                    mitra m
                    on
                        mm.mitra = m.id
                where
                    not exists (select * from tutup_siklus ts where ts.noreg = rs.noreg) and
                    rs.noreg is not null and
                    td.datang is not null and
                    td.datang > '".$tgl_min.' 00:00:00'."'
                    ".$sql_unit."
                group by
                    rs.noreg,
                    td.datang,
                    m.nama
                order by
                    td.datang desc
            ";
            $d_djt = $m_conf->hydrateRaw( $sql );

            if ( $d_djt->count() > 0 ) {
                $d_djt = $d_djt->toArray();
            }

            $this->result['status'] = 1;
            $this->result['content'] = $d_djt;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function riwayat()
    {
        $data = null;

        $m_jt = new \Model\Storage\JurnalTrans_model();
        $d_jt = $m_jt->orderBy('nama', 'asc')->with(['detail'])->get();

        if ( $d_jt->count() > 0 ) {
            $data = $d_jt->toArray();
        }

        $content['unit'] = $this->getUnit();
        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'riwayat', $content, true);

        return $html;
    }

    public function addForm()
    {
        $content['unit'] = $this->getUnit();
        $content['jurnal_trans'] = $this->getJurnalTrans();
        $content['perusahaan'] = $this->getPerusahaan();

        $html = $this->load->view($this->pathView . 'addForm', $content, true);

        return $html;
    }

    public function viewForm($id)
    {
        $data = $this->getData( $id );

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'viewForm', $content, true);

        return $html;
    }

    public function editForm($id)
    {
        $data = $this->getData( $id );

        $content['unit'] = $this->getUnit();
        $content['jurnal_trans'] = $this->getJurnalTrans();
        $content['perusahaan'] = $this->getPerusahaan();
        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'editForm', $content, true);

        return $html;
    }

    public function getData( $id ) {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                j.id,
                j.tanggal,
                j.unit,
                j.jurnal_trans_id,
                jt.nama as nama_jurnal_trans,
                jt.kode as kode_jurnal_trans,
                dj.id as id_detail,
                dj.tanggal as tgl_detail,
                dj.det_jurnal_trans_id,
                djt.nama as nama_det_jurnal_trans,
                dj.jurnal_trans_sumber_tujuan_id,
                dj.supplier,
                supl.nama as nama_supplier,
                dj.perusahaan,
                prs.perusahaan as nama_perusahaan,
                dj.keterangan,
                dj.nominal,
                dj.saldo,
                dj.ref_id,
                dj.asal,
                dj.coa_asal,
                dj.tujuan,
                dj.coa_tujuan,
                dj.unit,
                w.nama as nama_unit,
                dj.pic,
                dj.tbl_name,
                dj.tbl_id,
                dj.noreg
            from det_jurnal dj
            right join
                jurnal j
                on
                    dj.id_header = j.id
            left join
                jurnal_trans jt
                on
                    j.jurnal_trans_id = jt.id
            left join
                det_jurnal_trans djt
                on
                    dj.det_jurnal_trans_id = djt.id
            left join
                (
                    select p1.* from perusahaan p1
                    right join
                        (select max(id) as id, kode from perusahaan group by kode) p2
                        on
                            p1.id = p2.id
                ) prs
                on
                    prs.kode = dj.perusahaan
            left join
                (
                    select p.* from pelanggan p
                    right join
                        (select max(id) as id, nomor from pelanggan p where tipe = 'supplier' and jenis <> 'ekspedisi' and mstatus = 1 group by nomor) p2
                        on
                            p.id = p2.id
                ) supl
                on
                    supl.nomor = dj.supplier
            left join
                (
                    select w1.* from wilayah w1
                    right join
                        (select max(id) as id, kode from wilayah group by kode) w2
                        on
                            w1.id = w2.id
                ) w
                on
                    w.kode = dj.unit
            where
                j.id = ".$id."
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();
            
            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select jt.id from jurnal_trans jt
                where
                jt.kode = '".$d_conf[0]['kode_jurnal_trans']."'
            ";
            $d_list_id_header = $m_conf->hydrateRaw( $sql );
            
            $data_list_id_header = array();
            if ( $d_list_id_header->count() > 0 ) {
                $d_list_id_header = $d_list_id_header->toArray();

                foreach ($d_list_id_header as $k_list_header => $v_list_header) {
                    $data_list_id_header[] = $v_list_header['id'];
                }
            }

            $data = array(
                'id' => $d_conf[0]['id'],
                'tanggal' => $d_conf[0]['tanggal'],
                'jurnal_trans_id' => $d_conf[0]['jurnal_trans_id'],
                'nama_jurnal_trans' => $d_conf[0]['nama_jurnal_trans'],
                'perusahaan' => $d_conf[0]['perusahaan'],
                'nama_perusahaan' => $d_conf[0]['nama_perusahaan'],
                'unit' => $d_conf[0]['unit'],
                'nama_unit' => $d_conf[0]['nama_unit'],
                'list_id' => $data_list_id_header
            );
            foreach ($d_conf as $key => $value) {


                if ( !isset($data['plasma']) ) {
                    $noreg = $value['noreg'];

                    if ( !empty($noreg) ) {
                        $m_conf = new \Model\Storage\Conf();
                        $sql = "
                            select 
                                rs.noreg,
                                CONVERT(VARCHAR(10), td.datang, 103) as tgl_terima,
                                cast(SUBSTRING(rs.noreg, LEN(rs.noreg)-1, 2) as int) as kandang,
                                m.nama as nama_mitra
                            from rdim_submit rs
                            right join
                                order_doc od
                                on
                                    rs.noreg = od.noreg
                            right join
                                terima_doc td
                                on
                                    od.no_order = td.no_order
                            right join
                                (
                                    select mm1.* from mitra_mapping mm1
                                    right join
                                        (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                        on
                                            mm1.id = mm2.id
                                ) mm
                                on
                                    rs.nim = mm.nim
                            right join
                                mitra m
                                on
                                    mm.mitra = m.id
                            where
                                rs.noreg = '".$noreg."'
                            group by
                                rs.noreg,
                                td.datang,
                                m.nama
                            order by
                                td.datang desc
                        ";
                        $d_rs = $m_conf->hydrateRaw( $sql );

                        if ( $d_rs->count() > 0 ) {
                            $d_rs = $d_rs->toArray()[0];

                            $data['plasma'] = $d_rs;
                        }
                    }
                }

                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select djt.id from det_jurnal_trans djt
                    where
                        djt.sumber_coa = '".$value['coa_asal']."' and
                        djt.tujuan_coa = '".$value['coa_tujuan']."'
                ";
                $d_list_id_detail = $m_conf->hydrateRaw( $sql );

                $data_list_id_detail = array();
                if ( $d_list_id_detail->count() > 0 ) {
                    $d_list_id_detail = $d_list_id_detail->toArray();

                    foreach ($d_list_id_detail as $k_list_detail => $v_list_detail) {
                        $data_list_id_detail[] = $v_list_detail['id'];
                    }
                }

                $data['detail'][ $value['id_detail'] ] = array(
                    'id' => $value['id_detail'],
                    'id_header' => $id,
                    'tanggal' => $value['tgl_detail'],
                    'det_jurnal_trans_id' => $value['det_jurnal_trans_id'],
                    'nama_det_jurnal_trans' => $value['nama_det_jurnal_trans'],
                    'jurnal_trans_sumber_tujuan_id' => $value['jurnal_trans_sumber_tujuan_id'],
                    'supplier' => $value['supplier'],
                    'nama_supplier' => $value['nama_supplier'],
                    'perusahaan' => $value['perusahaan'],
                    'nama_perusahaan' => $value['nama_perusahaan'],
                    'keterangan' => $value['keterangan'],
                    'nominal' => $value['nominal'],
                    'saldo' => $value['saldo'],
                    'ref_id' => $value['ref_id'],
                    'asal' => $value['asal'],
                    'coa_asal' => $value['coa_asal'],
                    'tujuan' => $value['tujuan'],
                    'coa_tujuan' => $value['coa_tujuan'],
                    'unit' => $value['unit'],
                    'nama_unit' => $value['nama_unit'],
                    'pic' => $value['pic'],
                    'tbl_name' => $value['tbl_name'],
                    'tbl_id' => $value['tbl_id'],
                    'noreg' => $value['noreg'],
                    'list_id' => $data_list_id_detail
                );
            }
        }

        return $data;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_jurnal = new \Model\Storage\Jurnal_model();

            $m_jurnal->tanggal = $params['tanggal'];
            $m_jurnal->jurnal_trans_id = $params['jurnal_trans_id'];
            $m_jurnal->unit = $params['unit'];
            $m_jurnal->save();

            $id = $m_jurnal->id;
            foreach ($params['detail'] as $k_det => $v_det) {
                $m_dj = new \Model\Storage\DetJurnal_model();

                $m_dj->id_header = $id;
                $m_dj->tanggal = $v_det['tanggal'];
                $m_dj->det_jurnal_trans_id = $v_det['det_jurnal_trans_id'];
                $m_dj->perusahaan = $params['perusahaan'];
                $m_dj->keterangan = $v_det['keterangan'];
                $m_dj->nominal = $v_det['nominal'];
                $m_dj->asal = $v_det['sumber'];
                $m_dj->coa_asal = $v_det['sumber_coa'];
                $m_dj->tujuan = $v_det['tujuan'];
                $m_dj->coa_tujuan = $v_det['tujuan_coa'];
                $m_dj->unit = $params['unit'];
                $m_dj->pic = $v_det['pic'];
                $m_dj->noreg = (isset($params['noreg']) && !empty($params['noreg'])) ? $params['noreg'] : null;
                $m_dj->save();
            }

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_jurnal, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $id);
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit()
    {
        $params = $this->input->post('params');

        try {
            $id = $params['id'];

            $m_jurnal = new \Model\Storage\Jurnal_model();
            $m_jurnal->where('id', $id)->update(
                array(
                    'tanggal' => $params['tanggal'],
                    'jurnal_trans_id' => $params['jurnal_trans_id'],
                    'unit' => $params['unit']
                )
            );

            $m_dj = new \Model\Storage\DetJurnal_model();
            $m_dj->where('id_header', $id)->delete();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_dj = new \Model\Storage\DetJurnal_model();

                $m_dj->id_header = $id;
                $m_dj->tanggal = $v_det['tanggal'];
                $m_dj->det_jurnal_trans_id = $v_det['det_jurnal_trans_id'];
                $m_dj->perusahaan = $params['perusahaan'];
                $m_dj->keterangan = $v_det['keterangan'];
                $m_dj->nominal = $v_det['nominal'];
                $m_dj->asal = $v_det['sumber'];
                $m_dj->coa_asal = $v_det['sumber_coa'];
                $m_dj->tujuan = $v_det['tujuan'];
                $m_dj->coa_tujuan = $v_det['tujuan_coa'];
                $m_dj->unit = $params['unit'];
                $m_dj->pic = $v_det['pic'];
                $m_dj->noreg = (isset($params['noreg']) && !empty($params['noreg'])) ? $params['noreg'] : null;
                $m_dj->save();
            }

            $d_jurnal = $m_jurnal->where('id', $id)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_jurnal, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $id);
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
            $id = $params['id'];

            $m_jurnal = new \Model\Storage\Jurnal_model();
            $d_jurnal = $m_jurnal->where('id', $id)->first();

            $m_dj = new \Model\Storage\DetJurnal_model();
            $m_dj->where('id_header', $id)->delete();

            $m_jurnal->where('id', $id)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_jurnal, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}