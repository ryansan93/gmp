<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KasKeluar extends Public_Controller {

    private $pathView = 'accounting/kas_keluar/';
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
                "assets/accounting/kas_keluar/js/kas-keluar.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/accounting/kas_keluar/css/kas-keluar.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->hakAkses;
            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();
            $content['title_panel'] = 'Kas Keluar';

            // Load Indexx
            $data['title_menu'] = 'Kas Keluar';
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

        $m_kk = new \Model\Storage\Kk_model();
        $d_kk = $m_kk->getKkByDate($start_date, $end_date, 'KK');

        $content['data'] = $d_kk;
        $html = $this->load->view($this->pathView . 'list', $content, true);

        echo $html;
    }

    public function getNoLpb() {
        $params = $this->input->get('params');

        $kode_supl = $params['kode_supl'];
        $no_kk = (isset($params['no_kk']) && !empty($params['no_kk'])) ? $params['no_kk'] : null;

        $m_bl = new \Model\Storage\Beli_model();
        $d_bl = $m_bl->getBeliDebt($kode_supl, $no_kk);

        $html = '<option value="">Pilih No. Invoice</option>';
        if ( !empty($d_bl) && count($d_bl) > 0 ) {
            foreach ($d_bl as $k_lpb => $v_lpb) {
                $selected = null;
                $html .= '<option value="'.$v_lpb['no_lpb'].'" data-nilai="'.$v_lpb['sisa'].'" data-tgllpb="'.substr($v_lpb['tgl_lpb'], 0, 10).'" '.$selected.' >'.str_replace('-', '/', substr($v_lpb['tgl_lpb'], 0, 10)).' | '.$v_lpb['no_lpb'].' | '.$v_lpb['no_inv'].'</option>';
            }
        }

        echo $html;
    }

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
        $m_supl = new \Model\Storage\Supplier_model();

        $content['coa_header'] = $m_coa->getCoa( implode("' ,'", array('1101')), 'SUBSTRING(c.no_coa, 0, 5)' );
        $content['coa'] = $m_coa->getCoa();
        $content['supplier'] = $m_supl->getSupplier();
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

        $m_kk = new \Model\Storage\Kk_model();
        $d_kk = $m_kk->getKk( $kode )[0];

        $m_kki = new \Model\Storage\KkItem_model();
        $d_kki = $m_kki->getKkItem( $kode );
        
        $content['coa_header'] = $m_coa->getCoa( implode("' ,'", array('1101')), 'SUBSTRING(c.no_coa, 0, 5)' );
        $content['coa'] = $m_coa->getCoa();
        $content['supplier'] = $m_supl->getSupplier();
        $content['data'] = $d_kk;
        $content['detail'] = $d_kki;

        $html = $this->load->view($this->pathView . 'editForm', $content, TRUE);

        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_kk = new \Model\Storage\Kk_model();
            $now = $m_kk->getDate();

            $no_kk = $m_kk->getKode('KK');

            $m_kk->no_kk = $no_kk;
            $m_kk->tgl_kk = $params['tgl_kk'];
            $m_kk->no_coa = $params['no_coa'];
            $m_kk->periode = substr($params['tgl_kk'], 0, 7);
            $m_kk->kode_supl = $params['kode_supl'];
            $m_kk->nama_bank = $params['nama_bank'];
            $m_kk->no_giro = $params['no_giro'];
            $m_kk->tgl_tempo = $params['tgl_tempo'];
            $m_kk->tgl_cair = $params['tgl_cair'];
            $m_kk->keterangan = $params['keterangan'];
            $m_kk->nilai = $params['nilai'];
            $m_kk->save();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_kki = new \Model\Storage\KkItem_model();
                $m_kki->no_kk = $no_kk;
                $m_kki->tgl_kk = $params['tgl_kk'];
                $m_kki->no_urut = $v_det['no_urut'];
                $m_kki->periode = substr($params['tgl_kk'], 0, 7);
                $m_kki->no_coa = $v_det['no_coa'];
                $m_kki->keterangan = $v_det['keterangan'];
                $m_kki->no_lpb = $v_det['no_lpb'];
                $m_kki->nilai_lpb = $v_det['nilai_lpb'];
                $m_kki->nilai = $v_det['nilai'];
                $m_kki->save();
            }

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_kk, $deskripsi_log, $no_kk );

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
                    'tgl_kk' => $params['tgl_kk'],
                    'no_coa' => $params['no_coa'],
                    'periode' => substr($params['tgl_kk'], 0, 7),
                    'kode_supl' => $params['kode_supl'],
                    'nama_bank' => $params['nama_bank'],
                    'no_giro' => $params['no_giro'],
                    'tgl_tempo' => $params['tgl_tempo'],
                    'tgl_cair' => $params['tgl_cair'],
                    'keterangan' => $params['keterangan'],
                    'nilai' => $params['nilai']
                )
            );

            $m_kki = new \Model\Storage\KkItem_model();
            $m_kki->where('no_kk', $no_kk)->delete();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_kki = new \Model\Storage\KkItem_model();
                $m_kki->no_kk = $no_kk;
                $m_kki->tgl_kk = $params['tgl_kk'];
                $m_kki->no_urut = $v_det['no_urut'];
                $m_kki->periode = substr($params['tgl_kk'], 0, 7);
                $m_kki->no_coa = $v_det['no_coa'];
                $m_kki->keterangan = $v_det['keterangan'];
                $m_kki->no_lpb = $v_det['no_lpb'];
                $m_kki->nilai_lpb = $v_det['nilai_lpb'];
                $m_kki->nilai = $v_det['nilai'];
                $m_kki->save();
            }

            $d_kk = $m_kk->where('no_kk', $no_kk)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_kk, $deskripsi_log, $no_kk );

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

            $m_kk->where('no_kk', $no_kk)->delete();

            $m_kki = new \Model\Storage\KkItem_model();
            $m_kki->where('no_kk', $no_kk)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_kk, $deskripsi_log, $no_kk );

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

    public function printPreview($no_kk) {        
        $kode = exDecrypt( $no_kk );

        $m_kk = new \Model\Storage\Kk_model();
        $d_kk = $m_kk->getKk( $kode )[0];

        $m_kki = new \Model\Storage\KkItem_model();
        $d_kki = $m_kki->getKkItem( $kode );

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