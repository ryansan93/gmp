<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BankKeluar extends Public_Controller {

    private $pathView = 'accounting/bank_keluar/';
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
                "assets/accounting/bank_keluar/js/bank-keluar.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/accounting/bank_keluar/css/bank-keluar.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->hakAkses;
            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();
            $content['title_panel'] = 'Bank Keluar';

            // Load Indexx
            $data['title_menu'] = 'Bank Keluar';
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
        $bank = $params['bank'];

        $m_kk = new \Model\Storage\Kk_model();
        $d_kk = $m_kk->getBkByDate($start_date, $end_date, $bank);

        $content['data'] = $d_kk;
        $html = $this->load->view($this->pathView . 'list', $content, true);

        echo $html;
    }

    // public function getNoLpb() {
    //     $params = $this->input->get('params');

    //     $kode_supl = $params['kode_supl'];
    //     $no_kk = (isset($params['no_kk']) && !empty($params['no_kk'])) ? $params['no_kk'] : null;

    //     $m_bl = new \Model\Storage\Beli_model();
    //     $d_bl = $m_bl->getBeliDebt($kode_supl, $no_kk);

    //     $html = '<option value="">Pilih No. LPB</option>';
    //     if ( !empty($d_bl) && count($d_bl) > 0 ) {
    //         foreach ($d_bl as $k_lpb => $v_lpb) {
    //             $selected = null;
    //             $html .= '<option value="'.$v_lpb['no_lpb'].'" data-nilai="'.$v_lpb['sisa'].'" data-tgllpb="'.substr($v_lpb['tgl_lpb'], 0, 10).'" '.$selected.' >'.str_replace('-', '/', substr($v_lpb['tgl_lpb'], 0, 10)).' | '.$v_lpb['no_lpb'].' | '.$v_lpb['no_inv'].'</option>';
    //         }
    //     }

    //     echo $html;
    // }

    public function riwayat() {
        $start_date = substr(date('Y-m-d'), 0, 7).'-01';
        $end_date = date("Y-m-t", strtotime($start_date));

        $m_coa = new \Model\Storage\Coa_model();

        $content['start_date'] = $start_date;
        $content['end_date'] = $end_date;
        $content['bank'] = $m_coa->getDataBank(1, $this->userid);
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
        $content['bank'] = $m_coa->getDataBank(1, $this->userid);
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
        $content['akses'] = $this->hakAkses;
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
        $content['bank'] = $m_coa->getDataBank(1, $this->userid);
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
            $m_nbbk = new \Model\Storage\NoBbk_model();
            $m_kk = new \Model\Storage\Kk_model();

            // $no_kk = $m_nbbk->getKode('BBK');
            $no_kk = $m_nbbk->getKodeKeluar($params['kode'], $params['tgl_kk']);

            $m_nbbk->tbl_name = $m_kk->getTable();
            $m_nbbk->tbl_id = $no_kk;
            $m_nbbk->kode = $no_kk;
            $m_nbbk->save();

            $m_kk->no_kk = $no_kk;
            // $m_kk->no_coa = $params['no_coa'];
            $m_kk->coa_bank = $params['coa_bank'];
            $m_kk->nama_bank = $params['nama_bank'];
            $m_kk->tgl_kk = $params['tgl_kk'];
            $m_kk->jurnal_trans = $params['jurnal_trans'];
            $m_kk->periode = substr($params['tgl_kk'], 0, 7);
            $m_kk->no_supplier = $params['no_supplier'];
            $m_kk->supplier = $params['supplier'];
            $m_kk->no_giro = $params['no_giro'];
            $m_kk->tgl_tempo = $params['tgl_tempo'];
            $m_kk->tgl_cair = $params['tgl_cair'];
            $m_kk->keterangan = $params['keterangan'];
            $m_kk->nilai = $params['nilai'];
            $m_kk->unit = $params['unit'];
            $m_kk->save();

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
                // $m_djurnal->pelanggan = 
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
                    'no_giro' => $params['no_giro'],
                    'tgl_tempo' => $params['tgl_tempo'],
                    'tgl_cair' => $params['tgl_cair'],
                    'keterangan' => $params['keterangan'],
                    'nilai' => $params['nilai'],
                    'unit' => $params['unit'],
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
                // $m_djurnal->pelanggan = 
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

    public function printPreview($no_bk) {        
        $kode = exDecrypt( $no_bk );

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
            $fp = fopen("cetak/cbkcet.TXT","wb");
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
        $m_nb = new \Model\Storage\NoBbk_model();
        $no_kk = $m_nb->getKodeKeluar('BCA2', '2025-10-30');

        cetak_r( $no_kk );

        // $array = array(
        //     array('kk', 'KTAG25113010', 'KTAG25113010', 'KTAG25113001'),
        //     array('kk', 'KTAG25113011', 'KTAG25113011', 'KTAG25113002'),
        //     array('kk', 'KTAG25113012', 'KTAG25113012', 'KTAG25113003'),
        //     array('kk', 'KTAG25113013', 'KTAG25113013', 'KTAG25113004'),
        //     array('kk', 'KTAG25113014', 'KTAG25113014', 'KTAG25113005'),
        //     array('kk', 'KTAG25113015', 'KTAG25113015', 'KTAG25113006'),
        // );

        // foreach ($array as $key => $value) {
        //     // cetak_r( $value );
        //     if ( $value[0] == 'realisasi_pembayaran' ) {
        //         $m_nb = new \Model\Storage\NoBbk_model();
        //         $m_nb->where('tbl_id', $value[1])->update(
        //             array('kode' => $value[3])
        //         );

        //         $m_rp = new \Model\Storage\RealisasiPembayaran_model();
        //         $m_rp->where('nomor', $value[1])->update(
        //             array('no_bukti' => $value[3])
        //         );
        //     } else {
        //         $m_nb = new \Model\Storage\NoBbk_model();
        //         $m_nb->where('kode', $value[1])->update(
        //             array(
        //                 'kode' => $value[3],
        //                 'tbl_id' => $value[3]
        //             )
        //         );

        //         $m_kk = new \Model\Storage\Kk_model();
        //         $m_kk->where('no_kk', $value[1])->update(
        //             array('no_kk' => $value[3])
        //         );

        //         $m_kki = new \Model\Storage\KkItem_model();
        //         $m_kki->where('no_kk', $value[1])->update(
        //             array('no_kk' => $value[3])
        //         );

        //         $m_dj = new \Model\Storage\DetJurnal_model();
        //         $m_dj->where('tbl_id', $value[1])->update(
        //             array(
        //                 'tbl_id' => $value[3],
        //                 'kode_trans' => $value[3],
        //                 'kode_jurnal' => $value[3]
        //             )
        //         );
        //     }
        // }
    }
}