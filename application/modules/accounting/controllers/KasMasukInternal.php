<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KasMasukInternal extends Public_Controller {

    private $pathView = 'accounting/kas_masuk_internal/';
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
                "assets/accounting/kas_masuk_internal/js/kas-masuk-internal.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/accounting/kas_masuk_internal/css/kas-masuk-internal.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->hakAkses;
            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();
            $content['title_panel'] = 'Kas Masuk Internal';

            // Load Indexx
            $data['title_menu'] = 'Kas Masuk Internal';
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

        $m_km = new \Model\Storage\Km_model();
        $d_km = $m_km->getKmByDate($start_date, $end_date, $bank);

        $content['data'] = $d_km;
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
        $m_plg = new \Model\Storage\Pelanggan_model();
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $m_jt = new \Model\Storage\JurnalTrans_model();
        $m_djt = new \Model\Storage\DetJurnalTrans_model();

        $content['coa'] = $m_coa->getDataCoa();
        $content['bank'] = $m_coa->getDataKas(1, $this->userid, 1);
        $content['pelanggan'] = $m_plg->getDataPelanggan();
        $content['unit'] = $m_wilayah->getDataUnit(1, $this->userid);
        $content['jurnal_trans'] = $m_jt->getJurnalTransByUrl( $this->url );
        $content['det_jurnal_trans'] = $m_djt->getDetJurnalTransByUrl( $this->url );
        $content['akses'] = $this->hakAkses;
        $html = $this->load->view($this->pathView . 'addForm', $content, TRUE);

        return $html;
    }

    public function viewForm($kode)
    {
        $m_km = new \Model\Storage\Km_model();
        $d_km = $m_km->getKm( $kode )[0];

        $m_kmi = new \Model\Storage\KmItem_model();
        $d_kmi = $m_kmi->getKmItem( $kode );

        $m_log = new \Model\Storage\LogTables_model();
        $d_log = $m_log->getLog($m_km->table, $kode);

        $content['akses'] = $this->hakAkses;
        $content['data'] = $d_km;
        $content['detail'] = $d_kmi;
        $content['log'] = !empty($d_log) ? $d_log : null;

        $html = $this->load->view($this->pathView . 'viewForm', $content, TRUE);

        return $html;
    }

    public function editForm($kode)
    {
        $m_coa = new \Model\Storage\Coa_model();
        $m_plg = new \Model\Storage\Pelanggan_model();
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $m_jt = new \Model\Storage\JurnalTrans_model();
        $m_djt = new \Model\Storage\DetJurnalTrans_model();

        $m_km = new \Model\Storage\Km_model();
        $d_km = $m_km->getKm( $kode )[0];

        $m_kmi = new \Model\Storage\KmItem_model();
        $d_kmi = $m_kmi->getKmItem( $kode );
        
        $content['coa'] = $m_coa->getDataCoa();
        $content['bank'] = $m_coa->getDataKas(1, $this->userid, 1);
        $content['pelanggan'] = $m_plg->getDataPelanggan();
        $content['unit'] = $m_wilayah->getDataUnit(1, $this->userid);
        $content['jurnal_trans'] = $m_jt->getJurnalTransByUrl( $this->url );
        $content['det_jurnal_trans'] = $m_djt->getDetJurnalTransByUrl( $this->url );
        $content['data'] = $d_km;
        $content['detail'] = $d_kmi;

        $html = $this->load->view($this->pathView . 'editForm', $content, TRUE);

        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_nbbm = new \Model\Storage\NoBbm_model();
            $m_km = new \Model\Storage\Km_model();

            // $no_km = $m_nbbm->getKode('BKM');
            $no_km = $m_nbbm->getKodeMasuk($params['kode'], $params['tgl_km']);

            $m_nbbm->tbl_name = $m_km->getTable();
            $m_nbbm->tbl_id = $no_km;
            $m_nbbm->kode = $no_km;
            $m_nbbm->save();

            $m_km->no_km = $no_km;
            $m_km->coa_bank = $params['coa_bank'];
            $m_km->nama_bank = $params['nama_bank'];
            $m_km->tgl_km = $params['tgl_km'];
            $m_km->jurnal_trans = $params['jurnal_trans'];
            $m_km->periode = substr($params['tgl_km'], 0, 7);
            $m_km->no_pelanggan = $params['no_pelanggan'];
            $m_km->pelanggan = $params['pelanggan'];
            $m_km->keterangan = $params['keterangan'];
            $m_km->nilai = $params['nilai'];
            $m_km->unit = $params['unit'];
            $m_km->noreg = $params['noreg'];
            $m_km->save();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_kmi = new \Model\Storage\KmItem_model();
                $m_kmi->no_km = $no_km;
                // $m_kmi->no_urut = $v_det['no_urut'];
                // $m_kmi->no_coa = $v_det['no_coa'];
                // $m_kmi->nilai_invoice = $v_det['nilai_invoice'];
                $m_kmi->tgl_km = $params['tgl_km'];
                $m_kmi->periode = substr($params['tgl_km'], 0, 7);
                $m_kmi->det_jurnal_trans = $v_det['det_jurnal_trans'];
                $m_kmi->coa_asal = $v_det['coa_asal'];
                $m_kmi->coa_tujuan = $v_det['coa_tujuan'];
                $m_kmi->keterangan = $v_det['keterangan'];
                $m_kmi->no_invoice = $v_det['no_invoice'];
                $m_kmi->nilai = $v_det['nilai'];
                $m_kmi->save();

                $id_djt = null;
                if ( !empty($v_det['det_jurnal_trans']) ) {
                    $m_djt = new \Model\Storage\DetJurnalTrans_model();
                    $d_djt = $m_djt->where('kode', $v_det['det_jurnal_trans'])->orderBy('id', 'desc')->first();

                    $id_djt = $d_djt->id;
                }

                $m_djurnal = new \Model\Storage\DetJurnal_model();
                $m_djurnal->tanggal = $params['tgl_km'];
                $m_djurnal->det_jurnal_trans_id = $id_djt;
                // $m_djurnal->supplier = $params['no_supplier'];
                $m_djurnal->keterangan = $v_det['keterangan'];
                $m_djurnal->nominal = $v_det['nilai'];
                $m_djurnal->asal = $v_det['coa_asal_nama'];
                $m_djurnal->coa_asal = $v_det['coa_asal'];
                $m_djurnal->tujuan = $v_det['coa_tujuan_nama'];
                $m_djurnal->coa_tujuan = $v_det['coa_tujuan'];
                $m_djurnal->unit = $params['unit'];
                $m_djurnal->tbl_name = $m_km->getTable();
                $m_djurnal->tbl_id = $no_km;
                $m_djurnal->invoice = $v_det['no_invoice'];
                $m_djurnal->kode_trans = $no_km;
                $m_djurnal->kode_jurnal = $no_km;
                $m_djurnal->pelanggan = $params['no_pelanggan'];
                $m_djurnal->noreg = $params['noreg'];
                $m_djurnal->save();
            }

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_km, $deskripsi_log, null, $no_km );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
            $this->result['content'] = array('id' => $no_km);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit()
    {
        $params = $this->input->post('params');

        try {
            $m_km = new \Model\Storage\Km_model();
            $now = $m_km->getDate();

            $no_km = $params['no_km'];

            $m_km->where('no_km', $no_km)->update(
                array(
                    'no_km' => $no_km,
                    'coa_bank' => $params['coa_bank'],
                    'nama_bank' => $params['nama_bank'],
                    'tgl_km' => $params['tgl_km'],
                    'jurnal_trans' => $params['jurnal_trans'],
                    'periode' => substr($params['tgl_km'], 0, 7),
                    'no_pelanggan' => $params['no_pelanggan'],
                    'pelanggan' => $params['pelanggan'],
                    'keterangan' => $params['keterangan'],
                    'nilai' => $params['nilai'],
                    'unit' => $params['unit'],
                    'noreg' => $params['noreg']
                )
            );

            $m_djurnal = new \Model\Storage\DetJurnal_model();
            $m_djurnal->where('tbl_name', $m_km->getTable())->where('tbl_id', $no_km)->delete();

            $m_kmi = new \Model\Storage\KmItem_model();
            $m_kmi->where('no_km', $no_km)->delete();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_kmi = new \Model\Storage\KmItem_model();
                $m_kmi->no_km = $no_km;
                // $m_kmi->no_urut = $v_det['no_urut'];
                // $m_kmi->no_coa = $v_det['no_coa'];
                // $m_kmi->nilai_invoice = $v_det['nilai_invoice'];
                $m_kmi->tgl_km = $params['tgl_km'];
                $m_kmi->periode = substr($params['tgl_km'], 0, 7);
                $m_kmi->det_jurnal_trans = $v_det['det_jurnal_trans'];
                $m_kmi->coa_asal = $v_det['coa_asal'];
                $m_kmi->coa_tujuan = $v_det['coa_tujuan'];
                $m_kmi->keterangan = $v_det['keterangan'];
                $m_kmi->no_invoice = $v_det['no_invoice'];
                $m_kmi->nilai = $v_det['nilai'];
                $m_kmi->save();

                $id_djt = null;
                if ( !empty($v_det['det_jurnal_trans']) ) {
                    $m_djt = new \Model\Storage\DetJurnalTrans_model();
                    $d_djt = $m_djt->where('kode', $v_det['det_jurnal_trans'])->orderBy('id', 'desc')->first();

                    $id_djt = $d_djt->id;
                }

                $m_djurnal = new \Model\Storage\DetJurnal_model();
                $m_djurnal->tanggal = $params['tgl_km'];
                $m_djurnal->det_jurnal_trans_id = $id_djt;
                // $m_djurnal->supplier = $params['no_supplier'];
                $m_djurnal->keterangan = $v_det['keterangan'];
                $m_djurnal->nominal = $v_det['nilai'];
                $m_djurnal->asal = $v_det['coa_asal_nama'];
                $m_djurnal->coa_asal = $v_det['coa_asal'];
                $m_djurnal->tujuan = $v_det['coa_tujuan_nama'];
                $m_djurnal->coa_tujuan = $v_det['coa_tujuan'];
                $m_djurnal->unit = $params['unit'];
                $m_djurnal->tbl_name = $m_km->getTable();
                $m_djurnal->tbl_id = $no_km;
                $m_djurnal->invoice = $v_det['no_invoice'];
                $m_djurnal->kode_trans = $no_km;
                $m_djurnal->kode_jurnal = $no_km;
                $m_djurnal->pelanggan = $params['no_pelanggan'];
                $m_djurnal->noreg = $params['noreg'];
                $m_djurnal->save();
            }

            $d_km = $m_km->where('no_km', $no_km)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_km, $deskripsi_log, null, $no_km );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di update.';
            $this->result['content'] = array('id' => $no_km);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {            
            $no_km = $params['no_km'];

            $m_km = new \Model\Storage\Km_model();
            $d_km = $m_km->where('no_km', $no_km)->first();

            $m_djurnal = new \Model\Storage\DetJurnal_model();
            $m_djurnal->where('tbl_name', $m_km->getTable())->where('tbl_id', $no_km)->delete();
            
            $m_kmi = new \Model\Storage\KmItem_model();
            $m_kmi->where('no_km', $no_km)->delete();
            
            $m_nbbm = new \Model\Storage\NoBbm_model();
            $m_nbbm->where('tbl_name', $m_km->getTable())->where('tbl_id', $no_km)->delete();

            $m_km->where('no_km', $no_km)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_km, $deskripsi_log, null, $no_km );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function updatePo($no_po)
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                pi.po_no as no_po,
                pi.item_kode as item_kode,
                pi.harga as harga,
                pi.jumlah as jumlah_po,
                isnull(t.jumlah_terima, 0) as jumlah_terima
            from po_item pi
            right join
                po p 
                on
                    pi.po_no = p.no_po
            left join
                (
                    select ti.item_kode, ti.harga, sum(ti.jumlah_terima) as jumlah_terima, t.po_no from terima_item ti 
                    right join
                        terima t
                        on
                            ti.terima_kode = t.kode_terima 
                    where
                        t.po_no is not null
                    group by
                        ti.item_kode, ti.harga, t.po_no
                ) t
                on
                    t.po_no = p.no_po and
                    t.item_kode = pi.item_kode
            where
                pi.jumlah > isnull(t.jumlah_terima, 0) and
                p.no_po = '".$no_po."'
        ";
        $d_po = $m_conf->hydrateRaw( $sql );

        if ( $d_po->count() == 0 ) {
            $m_po = new \Model\Storage\Po_model();
            $m_po->where('no_po', $no_po)->update(
                array('done' => 1)
            );
        } else {
            $m_po = new \Model\Storage\Po_model();
            $m_po->where('no_po', $no_po)->update(
                array('done' => 0)
            );
        }
    }

    public function printPreview($no_km) {        
        $kode = exDecrypt( $no_km );

        $m_km = new \Model\Storage\Km_model();
        $d_km = $m_km->getKm( $kode )[0];

        $m_kmi = new \Model\Storage\KmItem_model();
        $d_kmi = $m_kmi->getKmItem( $kode );

        $m_prs = new \Model\Storage\Perusahaan_model();
        $d_prs = $m_prs->orderBy('id', 'desc')->with(['d_kota'])->first();

        $content['perusahaan'] = $d_prs->toArray();
        $content['data'] = $d_km;
        $content['detail'] = $d_kmi;

        $res_view_html = $this->load->view($this->pathView.'exportPdf', $content, true);

        echo $res_view_html;
    }

    public function exportPdf()
    {
        $params = $this->input->post('params');

        try {
            $_no_km = $params['kode'];
            
            $kode = exDecrypt( $_no_km );
            // $kode = 'FP2312060006';

            $m_km = new \Model\Storage\Km_model();
            $d_km = $m_km->getKmCetak( $kode );

            $struktur = "";
            $text = "";
            foreach ($d_km as $k_km => $v_km) {
                $idx = 1;
                foreach ($v_km as $key => $value) {
                    $struktur .= '"'.$key.'"';
                    $text .= '"'.$value.'"';
                    if ( $idx < count($v_km) ) {
                        $struktur .= ',';
                        $text .= ',';
                    }

                    $idx++;
                }

                $text .= "\n";
            }

            $content = $struktur."\n".$text;
            $fp = fopen("cetak/ckmcet.TXT","wb");
            fwrite($fp,$content);
            fclose($fp);

            system("cmd /c C:/xampp_php7/htdocs/sistem_udlancar/copy_file.bat");

            // $m_km = new \Model\Storage\Km_model();
            // $d_km = $m_km->getKm( $kode )[0];

            // $m_kmi = new \Model\Storage\KmItem_model();
            // $d_kmi = $m_kmi->getKmItem( $kode );

            // $content['data'] = $d_km;
            // $content['detail'] = $d_kmi;

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