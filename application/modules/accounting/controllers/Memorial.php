<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Memorial extends Public_Controller {

    private $pathView = 'accounting/memorial/';
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
                "assets/accounting/memorial/js/memorial.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/accounting/memorial/css/memorial.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->hakAkses;
            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();
            $content['title_panel'] = 'Memorial';

            // Load Indexx
            $data['title_menu'] = 'Memorial';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
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

        $m_mm = new \Model\Storage\Mm_model();
        $d_mm = $m_mm->getMmByDate($start_date, $end_date);

        $content['data'] = $d_mm;
        $html = $this->load->view($this->pathView . 'list', $content, true);

        echo $html;
    }

    // public function getNoFaktur() {
    //     $params = $this->input->get('params');

    //     $kode_cust = $params['kode_cust'];
    //     $no_mm = (isset($params['no_mm']) && !empty($params['no_mm'])) ? $params['no_mm'] : null;

    //     $m_faktur = new \Model\Storage\Faktur_model();
    //     $d_faktur = $m_faktur->getFakturDebt($kode_cust, $no_mm);

    //     $html = '<option value="">Pilih No. Faktur</option>';
    //     if ( !empty($d_faktur) && count($d_faktur) > 0 ) {
    //         foreach ($d_faktur as $k_faktur => $v_faktur) {
    //             $selected = null;
    //             $html .= '<option value="'.$v_faktur['no_faktur'].'" data-nilai="'.$v_faktur['sisa'].'" data-tglfaktur="'.substr($v_faktur['tgl_faktur'], 0, 10).'" '.$selected.' >'.str_replace('-', '/', substr($v_faktur['tgl_faktur'], 0, 10)).' | '.$v_faktur['no_faktur'].'</option>';
    //         }
    //     }

    //     echo $html;
    // }

    // public function getNoLpb() {
    //     $params = $this->input->get('params');

    //     $kode_supl = $params['kode_supl'];
    //     $no_mm = (isset($params['no_mm']) && !empty($params['no_mm'])) ? $params['no_mm'] : null;

    //     $m_bl = new \Model\Storage\Beli_model();
    //     $d_bl = $m_bl->getBeliDebt($kode_supl, $no_mm);

    //     $html = '<option value="">Pilih No. Invoice</option>';
    //     if ( !empty($d_bl) && count($d_bl) > 0 ) {
    //         foreach ($d_bl as $k_lpb => $v_lpb) {
    //             $selected = null;
    //             $html .= '<option value="'.$v_lpb['no_lpb'].'" data-nilai="'.$v_lpb['sisa'].'" data-tgllpb="'.substr($v_lpb['tgl_lpb'], 0, 10).'" '.$selected.' >'.str_replace('-', '/', substr($v_lpb['tgl_lpb'], 0, 10)).' | '.$v_lpb['no_inv'].'</option>';
    //         }
    //     }

    //     echo $html;
    // }

    public function riwayat() {
        $start_date = substr(date('Y-m-d'), 0, 7).'-01';
        $end_date = date("Y-m-t", strtotime($start_date));

        $content['start_date'] = $start_date;
        $content['end_date'] = $end_date;
        $content['akses'] = $this->hakAkses;
        $html = $this->load->view($this->pathView . 'riwayat', $content, TRUE);

        return $html;
    }

    public function addForm()
    {
        // $m_coa = new \Model\Storage\Coa_model();
        $m_plg = new \Model\Storage\Pelanggan_model();
        $m_supl = new \Model\Storage\Supplier_model();
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $m_jt = new \Model\Storage\JurnalTrans_model();
        $m_djt = new \Model\Storage\DetJurnalTrans_model();

        // $content['bank'] = $m_coa->getDataBank();
        $content['pelanggan'] = $m_plg->getDataPelanggan();
        $content['supplier'] = $m_supl->getDataSupplier();
        $content['unit'] = $m_wilayah->getDataUnit(1, $this->userid);
        $content['jurnal_trans'] = $m_jt->getJurnalTransByUrl( $this->url );
        $content['det_jurnal_trans'] = $m_djt->getDetJurnalTransByUrl( $this->url );
        $html = $this->load->view($this->pathView . 'addForm', $content, TRUE);

        return $html;
    }

    public function viewForm($kode)
    {
        $m_mm = new \Model\Storage\Mm_model();
        $d_mm = $m_mm->getMm( $kode )[0];

        $m_mmi = new \Model\Storage\MmItem_model();
        $d_mmi = $m_mmi->getMmItem( $kode );

        $m_log = new \Model\Storage\LogTables_model();
        $d_log = $m_log->getLog($m_mm->table, $kode);

        $content['akses'] = $this->hakAkses;
        $content['data'] = $d_mm;
        $content['detail'] = $d_mmi;
        $content['log'] = !empty($d_log) ? $d_log : null;

        $html = $this->load->view($this->pathView . 'viewForm', $content, TRUE);

        return $html;
    }

    public function editForm($kode)
    {
        // $m_coa = new \Model\Storage\Coa_model();
        $m_plg = new \Model\Storage\Pelanggan_model();
        $m_supl = new \Model\Storage\Supplier_model();
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $m_jt = new \Model\Storage\JurnalTrans_model();
        $m_djt = new \Model\Storage\DetJurnalTrans_model();

        $m_mm = new \Model\Storage\Mm_model();
        $d_mm = $m_mm->getMm( $kode )[0];

        $m_mmi = new \Model\Storage\MmItem_model();
        $d_mmi = $m_mmi->getMmItem( $kode );
        
        // $content['bank'] = $m_coa->getDataBank();
        $content['pelanggan'] = $m_plg->getDataPelanggan();
        $content['supplier'] = $m_supl->getDataSupplier();
        $content['unit'] = $m_wilayah->getDataUnit(1, $this->userid);
        $content['jurnal_trans'] = $m_jt->getJurnalTransByUrl( $this->url );
        $content['det_jurnal_trans'] = $m_djt->getDetJurnalTransByUrl( $this->url );
        $content['data'] = $d_mm;
        $content['detail'] = $d_mmi;

        $html = $this->load->view($this->pathView . 'editForm', $content, TRUE);

        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_mm = new \Model\Storage\Mm_model();
            $now = $m_mm->getDate();

            $no_mm = $m_mm->getKode('MM');

            $m_mm->no_mm = $no_mm;
            // $m_mm->no_coa = $params['no_coa'];
            // $m_mm->coa_bank = $params['coa_bank'];
            // $m_mm->nama_bank = $params['nama_bank'];
            $m_mm->tgl_mm = $params['tgl_mm'];
            $m_mm->jurnal_trans = $params['jurnal_trans'];
            $m_mm->periode = substr($params['tgl_mm'], 0, 7);
            $m_mm->no_pelanggan = $params['no_pelanggan'];
            $m_mm->pelanggan = $params['pelanggan'];
            $m_mm->no_supplier = $params['no_supplier'];
            $m_mm->supplier = $params['supplier'];
            // $m_mm->no_giro = $params['no_giro'];
            // $m_mm->tgl_tempo = $params['tgl_tempo'];
            // $m_mm->tgl_cair = $params['tgl_cair'];
            $m_mm->keterangan = $params['keterangan'];
            $m_mm->nilai = $params['nilai'];
            $m_mm->unit = $params['unit'];
            $m_mm->save();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_mmi = new \Model\Storage\MmItem_model();
                $m_mmi->no_mm = $no_mm;
                // $m_mmi->no_urut = $v_det['no_urut'];
                // $m_mmi->no_coa = $v_det['no_coa'];
                // $m_mmi->nilai_invoice = $v_det['nilai_invoice'];
                $m_mmi->tgl_mm = $params['tgl_mm'];
                $m_mmi->periode = substr($params['tgl_mm'], 0, 7);
                $m_mmi->det_jurnal_trans = $v_det['det_jurnal_trans'];
                $m_mmi->coa_asal = $v_det['coa_asal'];
                $m_mmi->coa_tujuan = $v_det['coa_tujuan'];
                $m_mmi->keterangan = $v_det['keterangan'];
                $m_mmi->no_invoice = $v_det['no_invoice'];
                $m_mmi->nilai = $v_det['nilai'];
                $m_mmi->save();
            }

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_mm, $deskripsi_log, null, $no_mm );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
            $this->result['content'] = array('id' => $no_mm);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit()
    {
        $params = $this->input->post('params');

        try {
            $m_mm = new \Model\Storage\Mm_model();
            $now = $m_mm->getDate();

            $no_mm = $params['no_mm'];

            $m_mm->where('no_mm', $no_mm)->update(
                array(
                    'tgl_mm' => $params['tgl_mm'],
                    'jurnal_trans' => $params['jurnal_trans'],
                    'periode' => substr($params['tgl_mm'], 0, 7),
                    'no_pelanggan' => $params['no_pelanggan'],
                    'pelanggan' => $params['pelanggan'],
                    'no_supplier' => $params['no_supplier'],
                    'supplier' => $params['supplier'],
                    'keterangan' => $params['keterangan'],
                    'nilai' => $params['nilai'],
                    'unit' => $params['unit'],
                )
            );

            $m_mmi = new \Model\Storage\MmItem_model();
            $m_mmi->where('no_mm', $no_mm)->delete();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_mmi = new \Model\Storage\MmItem_model();
                $m_mmi->no_mm = $no_mm;
                $m_mmi->tgl_mm = $params['tgl_mm'];
                $m_mmi->periode = substr($params['tgl_mm'], 0, 7);
                $m_mmi->det_jurnal_trans = $v_det['det_jurnal_trans'];
                $m_mmi->coa_asal = $v_det['coa_asal'];
                $m_mmi->coa_tujuan = $v_det['coa_tujuan'];
                $m_mmi->keterangan = $v_det['keterangan'];
                $m_mmi->no_invoice = $v_det['no_invoice'];
                $m_mmi->nilai = $v_det['nilai'];
                $m_mmi->save();
            }

            $d_mm = $m_mm->where('no_mm', $no_mm)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_mm, $deskripsi_log, null, $no_mm );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di update.';
            $this->result['content'] = array('id' => $no_mm);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {            
            $no_mm = $params['no_mm'];

            $m_mm = new \Model\Storage\Mm_model();
            $d_mm = $m_mm->where('no_mm', $no_mm)->first();

            $m_mm->where('no_mm', $no_mm)->delete();

            $m_mmi = new \Model\Storage\MmItem_model();
            $m_mmi->where('no_mm', $no_mm)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_mm, $deskripsi_log, null, $no_mm );

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

    public function printPreview($no_mm) {        
        $kode = exDecrypt( $no_mm );

        $m_mm = new \Model\Storage\Mm_model();
        $d_mm = $m_mm->getMm( $kode )[0];

        $m_mmi = new \Model\Storage\MmItem_model();
        $d_mmi = $m_mmi->getMmItem( $kode );

        $m_prs = new \Model\Storage\Perusahaan_model();
        $d_prs = $m_prs->orderBy('id', 'desc')->with(['d_kota'])->first();

        $content['perusahaan'] = $d_prs->toArray();
        $content['data'] = $d_mm;
        $content['detail'] = $d_mmi;

        $res_view_html = $this->load->view($this->pathView.'exportPdf', $content, true);

        echo $res_view_html;
    }

    public function exportPdf()
    {
        $params = $this->input->post('params');

        try {
            $_no_mm = $params['kode'];
            
            $kode = exDecrypt( $_no_mm );
            // $kode = 'FP2312060006';

            $m_mm = new \Model\Storage\Mm_model();
            $d_mm = $m_mm->getMmCetak( $kode );

            $struktur = "";
            $text = "";
            foreach ($d_mm as $k_mm => $v_mm) {
                $idx = 1;
                foreach ($v_mm as $key => $value) {
                    $struktur .= '"'.$key.'"';
                    $text .= '"'.$value.'"';
                    if ( $idx < count($v_mm) ) {
                        $struktur .= ',';
                        $text .= ',';
                    }

                    $idx++;
                }

                $text .= "\n";
            }

            $content = $struktur."\n".$text;
            $fp = fopen("cetak/cmmcet.TXT","wb");
            fwrite($fp,$content);
            fclose($fp);

            system("cmd /c C:/xampp_php7/htdocs/sistem_udlancar/copy_file.bat");

            // $m_mm = new \Model\Storage\Mm_model();
            // $d_mm = $m_mm->getMm( $kode )[0];

            // $m_mmi = new \Model\Storage\MmItem_model();
            // $d_mmi = $m_mmi->getMmItem( $kode );

            // $content['data'] = $d_mm;
            // $content['detail'] = $d_mmi;

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