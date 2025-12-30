<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KasKeluarInternal extends Public_Controller {

    private $pathView = 'accounting/kas_keluar_internal/';
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
        if ( $this->hakAkses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/jquery/easy-autocomplete/jquery.easy-autocomplete.min.js",
                "assets/select2/js/select2.min.js",
                "assets/accounting/kas_keluar_internal/js/kas-keluar-internal.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/accounting/kas_keluar_internal/css/kas-keluar-internal.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->hakAkses;
            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();
            $content['title_panel'] = 'Kas Keluar Internal';

            // Load Indexx
            $data['title_menu'] = 'Kas Keluar Internal';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getNoreg() {
        $params = $this->input->get('params');

        $unit = $params['unit'];

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from
            (
                select
                    data.noreg,
                    data.nama_mitra,
                    data.kandang,
                    case
                        when td.datang is not null then
                            td.datang
                        else
                            data.tgl_docin
                    end as tgl_docin
                from
                (
                    select 
                        rs.noreg,
                        m.nama as nama_mitra,
                        k.kandang,
                        rs.tgl_docin
                    from 
                    (
                        select rs.noreg, rs.kandang, rs.tgl_docin, rs.nim from rdim_submit rs
                        left join
                            tutup_siklus ts
                            on
                                rs.noreg = ts.noreg
                        where
                            ts.id is null
                    ) rs
                    left join
                        kandang k
                        on
                            rs.kandang = k.id
                    left join
                        wilayah w
                        on
                            w.id = k.unit
                    left join
                        (
                            select mm1.* from mitra_mapping mm1
                            right join
                                (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                on
                                    mm1.id = mm2.id
                        ) mm
                        on
                            mm.nim = rs.nim
                    left join
                        mitra m
                        on
                            m.id = mm.mitra
                    left join
                        jenis j
                        on
                            m.jenis = j.kode
                    where
                        w.kode = '".$unit."' and
                        j.nama like '%internal%'
                ) data
                left join
                    (
                        select od1.* from order_doc od1
                        right join
                            (select max(id) as id, no_order from order_doc group by no_order) od2
                            on
                                od1.id = od2.id
                    ) od
                    on
                        od.noreg = data.noreg
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
            ) data
            order by
                data.tgl_docin asc,
                data.nama_mitra asc,
                data.kandang asc
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        $opt = null;
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                $opt .= "<option value='".$value['noreg']."'>".tglIndonesia($value['tgl_docin'], '-', ' ')." | ".$value['nama_mitra']." (KDG:".$value['kandang'].")"."</option>";
            }
        }

        echo $opt;
    }

    public function loadForm()
    {
        $id = $this->input->get('id');
        $resubmit = $this->input->get('resubmit');

        $html = null;
        if ( !empty($id) && empty($resubmit) ) {
            $html = $this->viewForm($id);
        } else if ( !empty($id) && !empty($resubmit) ) {
            $html = $this->editForm($id);
        } else {
            $html = $this->addForm();
        }

        echo $html;
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $bank = $params['bank'];

        $m_kk = new \Model\Storage\Kk_model();
        $d_kk = $m_kk->getKkByDate($start_date, $end_date, $bank);

        $content['data'] = $d_kk;
        $html = $this->load->view($this->pathView . 'list', $content, true);

        echo $html;
    }

    public function riwayat() {
        $start_date = substr(date('Y-m-d'), 0, 7).'-01';
        $end_date = date("Y-m-t", strtotime($start_date));

        $m_coa = new \Model\Storage\Coa_model();

        $content['start_date'] = $start_date;
        $content['end_date'] = $end_date;
        $content['bank'] = $m_coa->getDataKas(1, $this->userid, 1);
        $content['akses'] = $this->hakAkses;
        $html = $this->load->view($this->pathView . 'riwayat', $content, TRUE);

        return $html;
    }

    public function addForm()
    {
        $m_coa = new \Model\Storage\Coa_model();
        $m_supl = new \Model\Storage\Supplier_model();
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $m_jt = new \Model\Storage\JurnalTrans_model();
        $m_djt = new \Model\Storage\DetJurnalTrans_model();

        $content['coa'] = $m_coa->getDataCoa();
        $content['bank'] = $m_coa->getDataKas(1, $this->userid, 1);
        $content['supplier'] = $m_supl->getDataSupplier();
        $content['unit'] = $m_wilayah->getDataUnit(1, $this->userid);
        $content['jurnal_trans'] = $m_jt->getJurnalTransByUrl( $this->url );
        $content['det_jurnal_trans'] = $m_djt->getDetJurnalTransByUrl( $this->url );
        $content['akses'] = $this->hakAkses;
        $html = $this->load->view($this->pathView . 'addForm', $content, TRUE);

        return $html;
    }

    public function viewForm($kode)
    {
        $m_kk = new \Model\Storage\Kk_model();
        $d_kk = $m_kk->getKk( $kode )[0];

        $m_kki = new \Model\Storage\KkItem_model();
        $d_kki = $m_kki->getKkItem( $kode );

        $m_log = new \Model\Storage\LogTables_model();
        $d_log = $m_log->getLog($m_kk->table, $kode);

        $content['akses'] = $this->hakAkses;
        $content['data'] = $d_kk;
        $content['detail'] = $d_kki;
        $content['log'] = !empty($d_log) ? $d_log : null;

        $html = $this->load->view($this->pathView . 'viewForm', $content, TRUE);

        return $html;
    }

    public function editForm($kode)
    {
        $m_coa = new \Model\Storage\Coa_model();
        $m_supl = new \Model\Storage\Supplier_model();
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $m_jt = new \Model\Storage\JurnalTrans_model();
        $m_djt = new \Model\Storage\DetJurnalTrans_model();

        $m_kk = new \Model\Storage\Kk_model();
        $d_kk = $m_kk->getKk( $kode )[0];

        $m_kki = new \Model\Storage\KkItem_model();
        $d_kki = $m_kki->getKkItem( $kode );

        $m_log = new \Model\Storage\LogTables_model();
        $d_log = $m_log->getLog($m_kk->table, $kode);
        
        $content['coa'] = $m_coa->getDataCoa();
        $content['bank'] = $m_coa->getDataKas(1, $this->userid, 1);
        $content['supplier'] = $m_supl->getDataSupplier();
        $content['unit'] = $m_wilayah->getDataUnit(1, $this->userid);
        $content['jurnal_trans'] = $m_jt->getJurnalTransByUrl( $this->url );
        $content['det_jurnal_trans'] = $m_djt->getDetJurnalTransByUrl( $this->url );
        $content['data'] = $d_kk;
        $content['detail'] = $d_kki;

        $content['userid'] = $this->userid;
        $content['log'] = $d_log[0];

        $html = $this->load->view($this->pathView . 'editForm', $content, TRUE);

        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            // cetak_r( $params, 1 );

            $m_nbbk = new \Model\Storage\NoBbk_model();
            $m_kk = new \Model\Storage\Kk_model();

            // $no_kk = $m_nbbk->getKode('BBK');
            $no_kk = $m_nbbk->getKodeKeluar($params['kode'], $params['tgl_kk']);

            $m_nbbk->tbl_name = $m_kk->getTable();
            $m_nbbk->tbl_id = $no_kk;
            $m_nbbk->kode = $no_kk;
            $m_nbbk->save();

            $m_kk->no_kk = $no_kk;
            $m_kk->coa_bank = $params['coa_bank'];
            $m_kk->nama_bank = $params['nama_bank'];
            $m_kk->tgl_kk = $params['tgl_kk'];
            $m_kk->jurnal_trans = $params['jurnal_trans'];
            $m_kk->periode = substr($params['tgl_kk'], 0, 7);
            $m_kk->no_supplier = $params['no_supplier'];
            $m_kk->supplier = $params['supplier'];
            $m_kk->keterangan = $params['keterangan'];
            $m_kk->nilai = $params['nilai'];
            $m_kk->unit = $params['unit'];
            $m_kk->noreg = $params['noreg'];
            $m_kk->save();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_kki = new \Model\Storage\KkItem_model();
                $m_kki->no_kk = $no_kk;
                $m_kki->tgl_kk = $params['tgl_kk'];
                $m_kki->periode = substr($params['tgl_kk'], 0, 7);
                $m_kki->det_jurnal_trans = $v_det['det_jurnal_trans'];
                $m_kki->coa_asal = $v_det['coa_asal'];
                $m_kki->coa_tujuan = $v_det['coa_tujuan'];
                $m_kki->keterangan = $v_det['keterangan'];
                $m_kki->no_invoice = $v_det['no_invoice'];
                $m_kki->nilai = $v_det['nilai'];
                $m_kki->save();

                $id_djt = null;
                if ( !empty($v_det['det_jurnal_trans']) ) {
                    $m_djt = new \Model\Storage\DetJurnalTrans_model();
                    $d_djt = $m_djt->where('kode', $v_det['det_jurnal_trans'])->orderBy('id', 'desc')->first();

                    $id_djt = $d_djt->id;
                }

                $m_djurnal = new \Model\Storage\DetJurnal_model();
                $m_djurnal->tanggal = $params['tgl_kk'];
                $m_djurnal->det_jurnal_trans_id = $id_djt;
                $m_djurnal->supplier = $params['no_supplier'];
                $m_djurnal->keterangan = $v_det['keterangan'];
                $m_djurnal->nominal = $v_det['nilai'];
                $m_djurnal->asal = $v_det['coa_asal_nama'];
                $m_djurnal->coa_asal = $v_det['coa_asal'];
                $m_djurnal->tujuan = $v_det['coa_tujuan_nama'];
                $m_djurnal->coa_tujuan = $v_det['coa_tujuan'];
                $m_djurnal->unit = $params['unit'];
                $m_djurnal->tbl_name = $m_kk->getTable();
                $m_djurnal->tbl_id = $no_kk;
                $m_djurnal->invoice = $v_det['no_invoice'];
                $m_djurnal->kode_trans = $no_kk;
                $m_djurnal->kode_jurnal = $no_kk;
                $m_djurnal->noreg = $params['noreg'];
                $m_djurnal->save();
            }

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_kk, $deskripsi_log, null, $no_kk );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
            $this->result['content'] = array('id' => $no_kk);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit()
    {
        $params = $this->input->post('params');

        try {
            $m_kk = new \Model\Storage\Kk_model();
            $now = $m_kk->getDate();

            $no_kk = $params['no_kk'];

            $m_kk->where('no_kk', $no_kk)->update(
                array(
                    'coa_bank' => $params['coa_bank'],
                    'nama_bank' => $params['nama_bank'],
                    'tgl_kk' => $params['tgl_kk'],
                    'jurnal_trans' => $params['jurnal_trans'],
                    'periode' => substr($params['tgl_kk'], 0, 7),
                    'no_supplier' => $params['no_supplier'],
                    'supplier' => $params['supplier'],
                    'keterangan' => $params['keterangan'],
                    'nilai' => $params['nilai'],
                    'unit' => $params['unit'],
                    'noreg' => $params['noreg']
                )
            );

            $m_djurnal = new \Model\Storage\DetJurnal_model();
            $m_djurnal->where('tbl_name', $m_kk->getTable())->where('tbl_id', $no_kk)->delete();

            $m_kki = new \Model\Storage\KkItem_model();
            $m_kki->where('no_kk', $no_kk)->delete();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_kki = new \Model\Storage\KkItem_model();
                $m_kki->no_kk = $no_kk;
                // $m_kki->no_urut = $v_det['no_urut'];
                // $m_kki->no_coa = $v_det['no_coa'];
                // $m_kki->nilai_invoice = $v_det['nilai_invoice'];
                $m_kki->tgl_kk = $params['tgl_kk'];
                $m_kki->periode = substr($params['tgl_kk'], 0, 7);
                $m_kki->det_jurnal_trans = $v_det['det_jurnal_trans'];
                $m_kki->coa_asal = $v_det['coa_asal'];
                $m_kki->coa_tujuan = $v_det['coa_tujuan'];
                $m_kki->keterangan = $v_det['keterangan'];
                $m_kki->no_invoice = $v_det['no_invoice'];
                $m_kki->nilai = $v_det['nilai'];
                $m_kki->save();

                $id_djt = null;
                if ( !empty($v_det['det_jurnal_trans']) ) {
                    $m_djt = new \Model\Storage\DetJurnalTrans_model();
                    $d_djt = $m_djt->where('kode', $v_det['det_jurnal_trans'])->orderBy('id', 'desc')->first();

                    $id_djt = $d_djt->id;
                }

                $m_djurnal = new \Model\Storage\DetJurnal_model();
                $m_djurnal->tanggal = $params['tgl_kk'];
                $m_djurnal->det_jurnal_trans_id = $id_djt;
                $m_djurnal->supplier = $params['no_supplier'];
                $m_djurnal->keterangan = $v_det['keterangan'];
                $m_djurnal->nominal = $v_det['nilai'];
                $m_djurnal->asal = $v_det['coa_asal_nama'];
                $m_djurnal->coa_asal = $v_det['coa_asal'];
                $m_djurnal->tujuan = $v_det['coa_tujuan_nama'];
                $m_djurnal->coa_tujuan = $v_det['coa_tujuan'];
                $m_djurnal->unit = $params['unit'];
                $m_djurnal->tbl_name = $m_kk->getTable();
                $m_djurnal->tbl_id = $no_kk;
                $m_djurnal->invoice = $v_det['no_invoice'];
                $m_djurnal->kode_trans = $no_kk;
                $m_djurnal->kode_jurnal = $no_kk;
                $m_djurnal->noreg = $params['noreg'];
                $m_djurnal->save();
            }

            $d_kk = $m_kk->where('no_kk', $no_kk)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_kk, $deskripsi_log, null, $no_kk );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di update.';
            $this->result['content'] = array('id' => $no_kk);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {            
            $no_kk = $params['no_kk'];

            $m_kk = new \Model\Storage\Kk_model();
            $d_kk = $m_kk->where('no_kk', $no_kk)->first();

            $m_djurnal = new \Model\Storage\DetJurnal_model();
            $m_djurnal->where('tbl_name', $m_kk->getTable())->where('tbl_id', $no_kk)->delete();

            $m_kki = new \Model\Storage\KkItem_model();
            $m_kki->where('no_kk', $no_kk)->delete();
            
            $m_nbbk = new \Model\Storage\NoBbk_model();
            $m_nbbk->where('tbl_name', $m_kk->getTable())->where('tbl_id', $no_kk)->delete();

            $m_kk->where('no_kk', $no_kk)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_kk, $deskripsi_log, null, $no_kk );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function excryptParams()
    {
        $params = $this->input->post('params');

        try {
            $params_encrypt = exEncrypt( json_encode($params) );

            $this->result['status'] = 1;
            $this->result['content'] = $params_encrypt;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function exportExcel($params_encrypt)
    {
        $params = json_decode( exDecrypt($params_encrypt), true );

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $bank = $params['bank'];

        $m_kk = new \Model\Storage\Kk_model();
        $data = $m_kk->getKkByDate($start_date, $end_date, $bank);

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from coa where coa = '".$bank."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $nama = null;
        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray()[0];

            $nama = $d_conf['nama_coa'];
        }

        $filename = strtoupper("KAS_INTERNAL".str_replace(' ', '_', $d_conf['nama_coa'])."_");
        $filename = $filename.str_replace('-', '', $start_date).'_'.str_replace('-', '', $end_date).'.xls';

        $arr_header = array('A', 'B', 'C', 'D', 'E', 'F', 'G');
        
        $arr_column = null;
        
        $idx = 0;
        $arr_column[ $idx ] = array(
            'G' => array('value' => 'KAS KELUAR INTERNAL'.strtoupper($nama), 'data_type' => 'string', 'colspan' => array('A','G'), 'align' => 'left', 'text_style' => 'bold', 'border' => 'none'),
        );
        $idx++;
        $arr_column[ $idx ] = array(
            'G' => array('value' => 'PERIODE '.str_replace('-', '/', $start_date).' - '.str_replace('-', '/', $end_date), 'data_type' => 'string', 'colspan' => array('A','G'), 'align' => 'left', 'text_style' => 'bold', 'border' => 'none'),
        );
        $idx++;
        $arr_column[ $idx ] = array(
            'A' => array('value' => 'Tanggal', 'data_type' => 'string', 'text_style' => 'bold'),
            'B' => array('value' => 'No. Kas Keluar', 'data_type' => 'string', 'text_style' => 'bold'),
            'C' => array('value' => 'Plasma', 'data_type' => 'string', 'text_style' => 'bold'),
            'D' => array('value' => 'Supplier', 'data_type' => 'string', 'text_style' => 'bold'),
            'E' => array('value' => 'Keterangan', 'data_type' => 'string', 'text_style' => 'bold'),
            'F' => array('value' => 'Unit', 'data_type' => 'string', 'text_style' => 'bold'),
            'G' => array('value' => 'Nilai', 'data_type' => 'string', 'text_style' => 'bold'),
        );
        $idx++;
        if ( !empty($data) ) {
            foreach ($data as $key => $value) {
                $arr_column[ $idx ] = array(
                    'A' => array('value' => $value['tgl_kk'], 'data_type' => 'date'),
                    'B' => array('value' => $value['no_kk'], 'data_type' => 'string'),
                    'C' => array('value' => !empty($value['nama_mitra']) ? strtoupper($value['nama_mitra'].' ('.$value['noreg'].')') : '-', 'data_type' => 'string'),
                    'D' => array('value' => !empty($value['supplier']) ? strtoupper($value['supplier']) : '-', 'data_type' => 'string'),
                    'E' => array('value' => !empty($value['keterangan']) ? strtoupper($value['keterangan']) : '-', 'data_type' => 'string'),
                    'F' => array('value' => strtoupper($value['unit']), 'data_type' => 'string'),
                    'G' => array('value' => $value['nilai'], 'data_type' => 'decimal2'),
                );
                $idx++;
            }
        }

        Modules::run( 'base/ExportExcel/exportExcelUsingSpreadSheet', $filename, $arr_header, $arr_column, 1, 0 );

        $this->load->helper('download');
        force_download('export_excel/'.$filename.'.xlsx', NULL);
    }

    public function printPreview($no_kk) {        
        $kode = exDecrypt( $no_kk );

        $m_kk = new \Model\Storage\Kk_model();
        $d_kk = $m_kk->getKk( $kode )[0];

        $m_kki = new \Model\Storage\KkItem_model();
        $d_kki = $m_kki->getKkItem( $kode );

        $m_prs = new \Model\Storage\Perusahaan_model();
        $d_prs = $m_prs->orderBy('id', 'desc')->with(['d_kota'])->first();

        $content['perusahaan'] = $d_prs->toArray();
        $content['data'] = $d_kk;
        $content['detail'] = $d_kki;

        $res_view_html = $this->load->view($this->pathView.'exportPdf', $content, true);

        echo $res_view_html;
    }

    public function exportPdf()
    {
        $params = $this->input->post('params');

        try {
            $_no_kk = $params['kode'];
            
            $kode = exDecrypt( $_no_kk );
            // $kode = 'FP2312060006';

            $m_kk = new \Model\Storage\Kk_model();
            $d_kk = $m_kk->getKkCetak( $kode );

            $struktur = "";
            $text = "";
            foreach ($d_kk as $k_kk => $v_kk) {
                $idx = 1;
                foreach ($v_kk as $key => $value) {
                    $struktur .= '"'.$key.'"';
                    $text .= '"'.$value.'"';
                    if ( $idx < count($v_kk) ) {
                        $struktur .= ',';
                        $text .= ',';
                    }

                    $idx++;
                }

                $text .= "\n";
            }

            $content = $struktur."\n".$text;
            $fp = fopen("cetak/ckkcet.TXT","wb");
            fwrite($fp,$content);
            fclose($fp);

            system("cmd /c C:/xampp_php7/htdocs/sistem_udlancar/copy_file.bat");

            // $m_kk = new \Model\Storage\Kk_model();
            // $d_kk = $m_kk->getKk( $kode )[0];

            // $m_kki = new \Model\Storage\KkItem_model();
            // $d_kki = $m_kki->getKkItem( $kode );

            // $content['data'] = $d_kk;
            // $content['detail'] = $d_kki;

            // $res_view_html = $this->load->view($this->pathView.'exportPdf', $content, true);

            // $this->load->library('PDFGenerator');
            // // $this->pdfgenerator->generate($res_view_html, $kode, "letter", "portrait");
            // $this->pdfgenerator->upload($res_view_html, $kode, "letter", "portrait", "uploads/po/");

            // $path = "uploads/po/".$kode.".pdf";

            $this->result['status'] = 1;
            // $this->result['content'] = array('url' => $path);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes()
    {
        $m_po = new \Model\Storage\Po_model();
        $no_po = $m_po->getNextNoPo();

        cetak_r( $no_po );
    }
}