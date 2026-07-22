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
        $m_coa = new \Model\Storage\Coa_model();
        $m_plg = new \Model\Storage\Pelanggan_model();
        $m_supl = new \Model\Storage\Supplier_model();
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $m_jt = new \Model\Storage\JurnalTrans_model();
        $m_djt = new \Model\Storage\DetJurnalTrans_model();

        $content['coa'] = $m_coa->getDataCoa();
        $content['pelanggan'] = $m_plg->getDataPelanggan();
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
        $m_coa = new \Model\Storage\Coa_model();
        $m_plg = new \Model\Storage\Pelanggan_model();
        $m_supl = new \Model\Storage\Supplier_model();
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $m_jt = new \Model\Storage\JurnalTrans_model();
        $m_djt = new \Model\Storage\DetJurnalTrans_model();

        $m_mm = new \Model\Storage\Mm_model();
        $d_mm = $m_mm->getMm( $kode )[0];

        $m_mmi = new \Model\Storage\MmItem_model();
        $d_mmi = $m_mmi->getMmItem( $kode );
        
        $content['coa'] = $m_coa->getDataCoa();
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
            // cetak_r( $params, 1 );

            $m_mm = new \Model\Storage\Mm_model();
            $now = $m_mm->getDate();

            $no_mm = $m_mm->getKode('MM', $params['tgl_mm']);

            $m_mm->no_mm = $no_mm;
            $m_mm->tgl_mm = $params['tgl_mm'];
            $m_mm->jurnal_trans = $params['jurnal_trans'];
            $m_mm->periode = substr($params['tgl_mm'], 0, 7);
            $m_mm->no_pelanggan = $params['no_pelanggan'];
            $m_mm->pelanggan = $params['pelanggan'];
            $m_mm->no_supplier = $params['no_supplier'];
            $m_mm->supplier = $params['supplier'];
            $m_mm->keterangan = $params['keterangan'];
            $m_mm->nilai = $params['nilai'];
            // $m_mm->unit = $params['unit'];
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
                $m_mmi->coa_asal = (isset($v_det['coa_asal']) && !empty($v_det['coa_asal'])) ? $v_det['coa_asal'] : null;
                $m_mmi->coa_tujuan = (isset($v_det['coa_tujuan']) && !empty($v_det['coa_tujuan'])) ? $v_det['coa_tujuan'] : null;
                $m_mmi->keterangan = $v_det['keterangan'];
                $m_mmi->no_invoice = $v_det['no_invoice'];
                $m_mmi->nilai = $v_det['nilai'];
                $m_mmi->unit = $v_det['unit'];
                $m_mmi->save();

                $id_djt = null;
                if ( !empty($v_det['det_jurnal_trans']) ) {
                    $m_djt = new \Model\Storage\DetJurnalTrans_model();
                    $d_djt = $m_djt->where('kode', $v_det['det_jurnal_trans'])->orderBy('id', 'desc')->first();

                    $id_djt = $d_djt->id;
                }

                $m_djurnal = new \Model\Storage\DetJurnal_model();
                $m_djurnal->tanggal = $params['tgl_mm'];
                $m_djurnal->det_jurnal_trans_id = $id_djt;
                $m_djurnal->supplier = $params['no_supplier'];
                $m_djurnal->keterangan = $v_det['keterangan'];
                $m_djurnal->nominal = $v_det['nilai'];
                $m_djurnal->asal = (isset($v_det['coa_asal']) && !empty($v_det['coa_asal'])) ? $v_det['coa_asal_nama'] : null;
                $m_djurnal->coa_asal = (isset($v_det['coa_asal']) && !empty($v_det['coa_asal'])) ? $v_det['coa_asal'] : null;
                $m_djurnal->tujuan = (isset($v_det['coa_tujuan']) && !empty($v_det['coa_tujuan'])) ? $v_det['coa_tujuan_nama'] : null;
                $m_djurnal->coa_tujuan = (isset($v_det['coa_tujuan']) && !empty($v_det['coa_tujuan'])) ? $v_det['coa_tujuan'] : null;
                // $m_djurnal->unit = $params['unit'];
                $m_djurnal->unit = $v_det['unit'];
                $m_djurnal->tbl_name = $m_mm->getTable();
                $m_djurnal->tbl_id = $no_mm;
                $m_djurnal->invoice = $v_det['no_invoice'];
                $m_djurnal->kode_trans = $no_mm;
                $m_djurnal->kode_jurnal = $no_mm;
                $m_djurnal->pelanggan = $params['no_pelanggan'];
                $m_djurnal->save();
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
                    // 'unit' => $params['unit'],
                )
            );

            $m_djurnal = new \Model\Storage\DetJurnal_model();
            $m_djurnal->where('tbl_name', $m_mm->getTable())->where('tbl_id', $no_mm)->delete();

            $m_mmi = new \Model\Storage\MmItem_model();
            $m_mmi->where('no_mm', $no_mm)->delete();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_mmi = new \Model\Storage\MmItem_model();
                $m_mmi->no_mm = $no_mm;
                // $m_mmi->no_urut = $v_det['no_urut'];
                // $m_mmi->no_coa = $v_det['no_coa'];
                // $m_mmi->nilai_invoice = $v_det['nilai_invoice'];
                $m_mmi->tgl_mm = $params['tgl_mm'];
                $m_mmi->periode = substr($params['tgl_mm'], 0, 7);
                $m_mmi->det_jurnal_trans = $v_det['det_jurnal_trans'];
                $m_mmi->coa_asal = (isset($v_det['coa_asal']) && !empty($v_det['coa_asal'])) ? $v_det['coa_asal'] : null;
                $m_mmi->coa_tujuan = (isset($v_det['coa_tujuan']) && !empty($v_det['coa_tujuan'])) ? $v_det['coa_tujuan'] : null;
                $m_mmi->keterangan = $v_det['keterangan'];
                $m_mmi->no_invoice = $v_det['no_invoice'];
                $m_mmi->nilai = $v_det['nilai'];
                $m_mmi->unit = $v_det['unit'];
                $m_mmi->save();

                $id_djt = null;
                if ( !empty($v_det['det_jurnal_trans']) ) {
                    $m_djt = new \Model\Storage\DetJurnalTrans_model();
                    $d_djt = $m_djt->where('kode', $v_det['det_jurnal_trans'])->orderBy('id', 'desc')->first();

                    $id_djt = $d_djt->id;
                }

                $m_djurnal = new \Model\Storage\DetJurnal_model();
                $m_djurnal->tanggal = $params['tgl_mm'];
                $m_djurnal->det_jurnal_trans_id = $id_djt;
                $m_djurnal->supplier = $params['no_supplier'];
                $m_djurnal->keterangan = $v_det['keterangan'];
                $m_djurnal->nominal = $v_det['nilai'];
                $m_djurnal->asal = (isset($v_det['coa_asal']) && !empty($v_det['coa_asal'])) ? $v_det['coa_asal_nama'] : null;
                $m_djurnal->coa_asal = (isset($v_det['coa_asal']) && !empty($v_det['coa_asal'])) ? $v_det['coa_asal'] : null;
                $m_djurnal->tujuan = (isset($v_det['coa_tujuan']) && !empty($v_det['coa_tujuan'])) ? $v_det['coa_tujuan_nama'] : null;
                $m_djurnal->coa_tujuan = (isset($v_det['coa_tujuan']) && !empty($v_det['coa_tujuan'])) ? $v_det['coa_tujuan'] : null;
                // $m_djurnal->unit = $params['unit'];
                $m_djurnal->unit = $v_det['unit'];
                $m_djurnal->tbl_name = $m_mm->getTable();
                $m_djurnal->tbl_id = $no_mm;
                $m_djurnal->invoice = $v_det['no_invoice'];
                $m_djurnal->kode_trans = $no_mm;
                $m_djurnal->kode_jurnal = $no_mm;
                $m_djurnal->pelanggan = $params['no_pelanggan'];
                $m_djurnal->save();
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

            $m_djurnal = new \Model\Storage\DetJurnal_model();
            $m_djurnal->where('tbl_name', $m_mm->getTable())->where('tbl_id', $no_mm)->delete();
            
            $m_mmi = new \Model\Storage\MmItem_model();
            $m_mmi->where('no_mm', $no_mm)->delete();

            $m_mm->where('no_mm', $no_mm)->delete();

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

    /**************************************************************************************
     * IMPORT EXCEL (mengisi baris detail pada form add/edit yang sedang dibuka)
     **************************************************************************************/
    public function downloadTemplate() {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Import Detail Memorial');

        $header = array('Tanggal (YYYY-MM-DD)', 'No. Pelanggan', 'Nama Pelanggan', 'No. Supplier', 'Nama Supplier', 'Keterangan Header', 'Unit (Kode)', 'COA Tujuan (Debet/NAIK)', 'COA Asal (Kredit/TURUN)', 'Keterangan Detail', 'No. Invoice', 'Nilai');
        foreach ($header as $i => $h) {
            $col = toAlpha($i+1);
            $sheet->setCellValue($col.'1', $h);
            $sheet->getStyle($col.'1')->getFont()->setBold(true);
        }

        $contoh = array('2026-06-30', '', '', 'BYD001', 'CONTOH SUPPLIER', 'CONTOH BARIS - HAPUS SEBELUM IMPORT', 'MGT', '21180.200', '', 'CONTOH KETERANGAN DETAIL', 'BYD/11/25/00015', 0.27);
        foreach ($contoh as $i => $v) {
            $col = toAlpha($i+1);
            $sheet->setCellValue($col.'2', $v);
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $petunjuk = $spreadsheet->createSheet();
        $petunjuk->setTitle('Petunjuk');
        $lines = array(
            'PETUNJUK PENGISIAN IMPORT DETAIL MEMORIAL',
            '',
            '1 baris = 1 baris detail pada tabel Debet/Kredit di form Memorial.',
            '',
            'Kolom Tanggal, No/Nama Pelanggan, No/Nama Supplier, dan Keterangan Header',
            'HANYA PERLU DIISI DI BARIS PERTAMA. Baris berikutnya boleh dikosongkan,',
            'akan otomatis memakai nilai dari baris pertama (karena dalam satu file',
            'excel hanya untuk 1 memorial / 1 no. memo).',
            '',
            'Tanggal          : format YYYY-MM-DD, contoh 2026-06-30. Wajib diisi di baris pertama.',
            'No. Pelanggan    : opsional, isi jika memorial terkait pelanggan yang ada di master.',
            'Nama Pelanggan   : isi jika memorial terkait pelanggan (wajib jika No. Supplier kosong).',
            'No. Supplier     : opsional, isi jika memorial terkait supplier yang ada di master.',
            'Nama Supplier    : isi jika memorial terkait supplier (wajib jika Nama Pelanggan kosong).',
            'Keterangan Header: wajib diisi di baris pertama.',
            '',
            'Unit             : kode unit sesuai master wilayah, contoh MGT.',
            'COA Tujuan       : kode COA yang NAIK / didebet.',
            'COA Asal         : kode COA yang TURUN / dikredit.',
            '                   Isi salah satu saja (Tujuan atau Asal), tidak harus dua-duanya.',
            'Keterangan Detail: wajib diisi.',
            'No. Invoice      : opsional.',
            'Nilai            : wajib diisi, harus lebih besar dari 0.',
            '',
            'Hapus baris contoh (baris ke-2) di sheet "Import Detail Memorial" sebelum',
            'meng-upload.',
            '',
            'Data hasil import TIDAK langsung tersimpan -- hanya mengisi form yang sedang',
            'terbuka (header dan baris-baris detail). Data baru benar-benar tersimpan',
            'setelah anda menekan tombol Simpan / Update.',
        );
        foreach ($lines as $i => $line) {
            $petunjuk->setCellValue('A'.($i+1), $line);
        }
        $petunjuk->getColumnDimension('A')->setWidth(100);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'TEMPLATE_IMPORT_DETAIL_MEMORIAL.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('export_excel/'.$filename);

        $this->load->helper('download');
        force_download('export_excel/'.$filename, NULL);
    }

    public function importForm() {
        $d_content['akses'] = $this->hakAkses;
        $html = $this->load->view($this->pathView.'importForm', $d_content, true);

        echo $html;
    }

    public function importDetail() {
        $file = isset($_FILES['file']) ? $_FILES['file'] : null;

        try {
            if ( !empty($file) ) {
                $upload_path = FCPATH . "//uploads/import_file/";
                $moved = uploadFile($file, $upload_path);

                if ( $moved ) {
                    $rows = $this->readImportExcel( $moved['path'] );

                    if ( !empty($rows) && count($rows) > 0 ) {
                        $header = $this->validateImportHeader( $rows[0] );
                        $detail = $this->validateImportRows( $rows );

                        $this->result['status'] = 1;
                        $this->result['content'] = array('header' => $header, 'rows' => $detail);
                    } else {
                        $this->result['message'] = 'Data yang anda upload kosong atau formatnya tidak sesuai template.';
                    }
                } else {
                    $this->result['message'] = 'File gagal terupload, segera hubungi tim IT.';
                }
            } else {
                $this->result['message'] = 'Harap upload file terlebih dahulu.';
            }
        } catch (Exception $e) {
            $this->result['message'] = 'GAGAL : '.$e->getMessage();
        }

        display_json( $this->result );
    }

    /**
     * Bersihkan tanda kutip satu di depan (mis. "'11111.002") -- muncul saat user
     * mengetik kode di excel dengan awalan ' supaya tidak dikonversi jadi angka.
     */
    private function cleanKode( $v ) {
        $v = trim($v);
        if ( substr($v, 0, 1) === "'" ) {
            $v = trim(substr($v, 1));
        }

        return $v;
    }

    private function readImportExcel( $path_name ) {
        $path = 'uploads/import_file/'.$path_name;

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($path, \PhpOffice\PhpSpreadsheet\Reader\IReader::LOAD_WITH_CHARTS);
        // formatData = false -> ambil nilai mentah (nilai jadi angka murni tanpa format ribuan)
        $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, false, true);

        $rows = array();
        $numrow = 0;

        // header (tanggal/pelanggan/supplier/keterangan) cukup diisi di baris pertama,
        // baris berikutnya yang kosong akan memakai nilai baris sebelumnya (fill-forward)
        $h_tanggal = null;
        $h_no_pelanggan = null;
        $h_nama_pelanggan = null;
        $h_no_supplier = null;
        $h_nama_supplier = null;
        $h_keterangan_header = null;

        foreach ($sheet as $row) {
            $numrow++;
            if ( $numrow == 1 ) continue; // baris header kolom, lewati

            if ( is_numeric($row['A']) ) {
                $tanggal = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['A'])->format('Y-m-d');
            } else {
                $tanggal = trim($row['A']);
            }
            $no_pelanggan = $this->cleanKode($row['B']);
            $nama_pelanggan = trim($row['C']);
            $no_supplier = $this->cleanKode($row['D']);
            $nama_supplier = trim($row['E']);
            $keterangan_header = trim($row['F']);
            $unit = $this->cleanKode($row['G']);
            $coa_tujuan = $this->cleanKode($row['H']);
            $coa_asal = $this->cleanKode($row['I']);
            $keterangan = trim($row['J']);
            $no_invoice = $this->cleanKode($row['K']);
            $nilai = is_string($row['L']) ? trim(str_replace(',', '', $row['L'])) : $row['L'];

            if ( empty($tanggal) && empty($no_pelanggan) && empty($nama_pelanggan) && empty($no_supplier) && empty($nama_supplier) && empty($keterangan_header)
                && empty($unit) && empty($coa_tujuan) && empty($coa_asal) && empty($keterangan) && empty($nilai) ) {
                continue; // baris kosong, lewati
            }

            if ( !empty($tanggal) ) { $h_tanggal = $tanggal; } else { $tanggal = $h_tanggal; }
            if ( !empty($no_pelanggan) ) { $h_no_pelanggan = $no_pelanggan; } else { $no_pelanggan = $h_no_pelanggan; }
            if ( !empty($nama_pelanggan) ) { $h_nama_pelanggan = $nama_pelanggan; } else { $nama_pelanggan = $h_nama_pelanggan; }
            if ( !empty($no_supplier) ) { $h_no_supplier = $no_supplier; } else { $no_supplier = $h_no_supplier; }
            if ( !empty($nama_supplier) ) { $h_nama_supplier = $nama_supplier; } else { $nama_supplier = $h_nama_supplier; }
            if ( !empty($keterangan_header) ) { $h_keterangan_header = $keterangan_header; } else { $keterangan_header = $h_keterangan_header; }

            $rows[] = array(
                'row_number' => $numrow,
                'tgl_mm' => $tanggal,
                'no_pelanggan' => $no_pelanggan,
                'nama_pelanggan' => $nama_pelanggan,
                'no_supplier' => $no_supplier,
                'nama_supplier' => $nama_supplier,
                'keterangan_header' => $keterangan_header,
                'unit' => strtoupper($unit),
                'coa_tujuan' => $coa_tujuan,
                'coa_asal' => $coa_asal,
                'keterangan' => $keterangan,
                'no_invoice' => $no_invoice,
                'nilai' => $nilai,
            );
        }

        return $rows;
    }

    private function validateImportHeader( $r ) {
        $errors = array();

        /* TANGGAL */
        if ( empty($r['tgl_mm']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $r['tgl_mm']) ) {
            $errors[] = 'Format tanggal tidak valid (gunakan YYYY-MM-DD), isi di baris pertama.';
        }

        /* PELANGGAN / SUPPLIER */
        $no_pelanggan = $r['no_pelanggan'];
        $nama_pelanggan = $r['nama_pelanggan'];
        $no_supplier = $r['no_supplier'];
        $nama_supplier = $r['nama_supplier'];

        if ( empty($nama_pelanggan) && empty($nama_supplier) ) {
            $errors[] = 'Harus isi Nama Pelanggan atau Nama Supplier (baris pertama).';
        } else if ( !empty($nama_pelanggan) && !empty($nama_supplier) ) {
            $errors[] = 'Hanya boleh isi salah satu Pelanggan atau Supplier (baris pertama).';
        } else if ( !empty($nama_pelanggan) && !empty($no_pelanggan) ) {
            $m_plg = new \Model\Storage\Pelanggan_model();
            $d_plg = $m_plg->where('nomor', $no_pelanggan)->where('tipe', 'pelanggan')->orderBy('id', 'desc')->first();
            if ( empty($d_plg) ) {
                $errors[] = 'No. Pelanggan "'.$no_pelanggan.'" tidak ditemukan.';
            } else {
                $nama_pelanggan = $d_plg->nama; // samakan dengan master
            }
        } else if ( !empty($nama_supplier) && !empty($no_supplier) ) {
            $m_supl = new \Model\Storage\Supplier_model();
            $d_supl = $m_supl->where('nomor', $no_supplier)->where('tipe', 'supplier')->orderBy('id', 'desc')->first();
            if ( empty($d_supl) ) {
                $errors[] = 'No. Supplier "'.$no_supplier.'" tidak ditemukan.';
            } else {
                $nama_supplier = $d_supl->nama; // samakan dengan master
            }
        }

        /* KETERANGAN HEADER */
        if ( empty($r['keterangan_header']) ) {
            $errors[] = 'Keterangan Header wajib diisi di baris pertama.';
        }

        return array(
            'tgl_mm' => $r['tgl_mm'],
            'no_pelanggan' => $no_pelanggan,
            'nama_pelanggan' => $nama_pelanggan,
            'no_supplier' => $no_supplier,
            'nama_supplier' => $nama_supplier,
            'keterangan' => $r['keterangan_header'],
            'errors' => $errors,
        );
    }

    private function validateImportRows( $rows ) {
        $data = array();
        foreach ($rows as $r) {
            $errors = array();

            /* UNIT */
            $unit_nama = null;
            if ( empty($r['unit']) ) {
                $errors[] = 'Unit wajib diisi.';
            } else {
                $m_wilayah = new \Model\Storage\Wilayah_model();
                $d_unit = $m_wilayah->where('kode', $r['unit'])->whereNotNull('kode')->orderBy('id', 'desc')->first();
                if ( empty($d_unit) ) {
                    $errors[] = 'Unit "'.$r['unit'].'" tidak ditemukan.';
                } else {
                    $unit_nama = $d_unit->nama;
                }
            }

            /* COA -- cukup salah satu (asal atau tujuan) yang wajib diisi, sama seperti input manual */
            $coa_tujuan_nama = null;
            if ( !empty($r['coa_tujuan']) ) {
                $m_coa = new \Model\Storage\Coa_model();
                $d_coa = $m_coa->where('coa', $r['coa_tujuan'])->where('status', 1)->first();
                if ( empty($d_coa) ) {
                    $errors[] = 'COA Tujuan "'.$r['coa_tujuan'].'" tidak ditemukan.';
                } else {
                    $coa_tujuan_nama = $d_coa->nama_coa;
                }
            }

            $coa_asal_nama = null;
            if ( !empty($r['coa_asal']) ) {
                $m_coa = new \Model\Storage\Coa_model();
                $d_coa = $m_coa->where('coa', $r['coa_asal'])->where('status', 1)->first();
                if ( empty($d_coa) ) {
                    $errors[] = 'COA Asal "'.$r['coa_asal'].'" tidak ditemukan.';
                } else {
                    $coa_asal_nama = $d_coa->nama_coa;
                }
            }

            if ( empty($r['coa_tujuan']) && empty($r['coa_asal']) ) {
                $errors[] = 'Harus isi salah satu COA Tujuan (Debet) atau COA Asal (Kredit).';
            }

            /* KETERANGAN */
            if ( empty($r['keterangan']) ) {
                $errors[] = 'Keterangan wajib diisi.';
            }

            /* NILAI */
            $nilai = is_numeric($r['nilai']) ? floatval($r['nilai']) : null;
            if ( empty($nilai) || $nilai <= 0 ) {
                $errors[] = 'Nilai wajib diisi dan harus lebih besar dari 0.';
            }

            $data[] = array(
                'row_number' => $r['row_number'],
                'unit' => $r['unit'],
                'unit_nama' => $unit_nama,
                'coa_tujuan' => $r['coa_tujuan'],
                'coa_tujuan_nama' => $coa_tujuan_nama,
                'coa_asal' => $r['coa_asal'],
                'coa_asal_nama' => $coa_asal_nama,
                'keterangan' => $r['keterangan'],
                'no_invoice' => $r['no_invoice'],
                'nilai' => $nilai,
                'errors' => $errors,
            );
        }

        return $data;
    }
    /**************************************************************************************
     * END - IMPORT EXCEL
     **************************************************************************************/

    public function tes()
    {
        // $tanggal = '2025-11-10';
        // $periode = substr(str_replace('-', '', $tanggal), 2, 6);

        // cetak_r( $periode );

        $array = array(
            'MM2606120001',
            'MM2606120002',
            'MM2606120003',
            'MM2606120004',
            'MM2606120005',
            'MM2606120006',
            'MM2606120007',
            'MM2606120008',
            'MM2606120009',
            'MM2606120010',
            'MM2606120011',
            'MM2606120012',
            'MM2606120013',
            'MM2606120014',
        );

        foreach ($array as $key => $value) {
            $m_mm = new \Model\Storage\Mm_model();

            $m_djurnal = new \Model\Storage\DetJurnal_model();
            $m_djurnal->where('tbl_name', $m_mm->getTable())->where('tbl_id', $value)->delete();

            $m_mm = new \Model\Storage\Mm_model();
            $d_mm = $m_mm->where('no_mm', $value)->first()->toArray();

            $m_mmi = new \Model\Storage\MmItem_model;
            $d_mmi = $m_mmi->where('no_mm', $value)->get()->toArray();

            foreach ($d_mmi as $k_mmi => $v_mmi) {
                $m_coa = new \Model\Storage\Coa_model();
                $d_coa_asal = $m_coa->where('coa', $v_mmi['coa_asal'])->first()->toArray();
                $d_coa_tujuan = $m_coa->where('coa', $v_mmi['coa_tujuan'])->first()->toArray();

                $m_djurnal = new \Model\Storage\DetJurnal_model();
                $m_djurnal->tanggal = $d_mm['tgl_mm'];
                $m_djurnal->det_jurnal_trans_id = null;
                $m_djurnal->supplier = $d_mm['no_supplier'];
                $m_djurnal->keterangan = $v_mmi['keterangan'];
                $m_djurnal->nominal = $v_mmi['nilai'];
                $m_djurnal->asal = (isset($v_mmi['coa_asal']) && !empty($v_mmi['coa_asal'])) ? $d_coa_asal['nama_coa'] : null;
                $m_djurnal->coa_asal = (isset($v_mmi['coa_asal']) && !empty($v_mmi['coa_asal'])) ? $v_mmi['coa_asal'] : null;
                $m_djurnal->tujuan = (isset($v_mmi['coa_tujuan']) && !empty($v_mmi['coa_tujuan'])) ? $d_coa_tujuan['nama_coa'] : null;
                $m_djurnal->coa_tujuan = (isset($v_mmi['coa_tujuan']) && !empty($v_mmi['coa_tujuan'])) ? $v_mmi['coa_tujuan'] : null;
                // $m_djurnal->unit = $params['unit'];
                $m_djurnal->unit = $d_mm['unit'];
                $m_djurnal->tbl_name = $m_mm->getTable();
                $m_djurnal->tbl_id = $d_mm['no_mm'];
                $m_djurnal->invoice = $v_mmi['no_invoice'];
                $m_djurnal->kode_trans = $d_mm['no_mm'];
                $m_djurnal->kode_jurnal = $d_mm['no_mm'];
                $m_djurnal->pelanggan = $d_mm['no_pelanggan'];
                $m_djurnal->save();
            }
        }
    }
}