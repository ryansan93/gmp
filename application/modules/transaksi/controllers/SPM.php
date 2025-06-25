<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SPM extends Public_Controller {

    private $url;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index($segment=0)
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/jquery/easy-autocomplete/jquery.easy-autocomplete.min.js",
                "assets/jquery/maskedinput/jquery.maskedinput.min.js",
                "assets/select2/js/select2.min.js",
                "assets/jquery/list.min.js",
                "assets/transaksi/spm/js/spm.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/transaksi/spm/css/spm.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['ekspedisi'] = $this->get_ekspedisi();
            $content['data_setting'] = $this->mapping_data();
            $content['data_pme'] = $this->mapping_data_pme();
            $content['list_ekspedisi'] = $this->get_ekspedisi_pme();

            $data['title_menu'] = 'Setting Pengiriman';
            $data['view'] = $this->load->view('transaksi/spm/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function load_form()
    {
        $id = $this->input->get('id');
        $resubmit = $this->input->get('resubmit');
        $html = '';

        if ( !empty($id) ) {
            if ( !empty($resubmit) ) {
                /* NOTE : untuk edit */
                $html = $this->edit_form($id, $resubmit);
            } else {
                /* NOTE : untuk view */
                $html = $this->view_form($id, $resubmit);
            }
        } else {
            /* NOTE : untuk add */
            $html = $this->add_form();
        }

        echo $html;
    }

    public function add_form()
    {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['data'] = null;
        $content['jenis_pakan'] = $this->get_jenis_pakan();
        $html = $this->load->view('transaksi/kpm/add_form', $content);
        
        return $html;
    }

    public function view_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['ekspedisi'] = $this->get_ekspedisi();
        $content['data_setting'] = $this->mapping_data();
        $content['perusahaan'] = $this->get_data_perusahaan();

        $data['title_menu'] = 'Setting Pengiriman';
        $data['view'] = $this->load->view('transaksi/spm/index', $content, TRUE);
        $this->load->view($this->template, $data);
        
        return $html;
    }

    public function list_sp()
    {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['ekspedisi'] = $this->get_ekspedisi();
        $content['data_setting'] = $this->mapping_data();
        $content['perusahaan'] = $this->get_data_perusahaan();

        $html = $this->load->view('transaksi/spm/list_sp', $content, TRUE);

        echo $html;
    }

    public function list_spm()
    {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['data_pme'] = $this->mapping_data_pme();
        $content['list_ekspedisi'] = $this->get_ekspedisi_pme();

        $html = $this->load->view('transaksi/spm/list_spm', $content, TRUE);

        echo $html;
    }

    public function get_data()
    {
    	$m_kartu_pakan = new \Model\Storage\KartuPakan_model();
        $d_noreg = $m_kartu_pakan->select('noreg')->distinct('noreg')->get()->toArray();

        $datas = array();
        if ( !empty($d_noreg) ) {
            foreach ($d_noreg as $noreg) {
                $order_doc = $m_kartu_pakan->where('noreg', $noreg['noreg'])
                                          ->with(['data_rdim_submit', 'barang', 'kartu_pakan_detail'])
                                          ->first()->toArray();

                array_push($datas, $order_doc);
            }
        }

        return $datas;
    }

    public function get_data_setting_kirim()
    {
        $m_dpakanspm = new \Model\Storage\DetPakanSPM_model();
        $d_dpakanspm = $m_dpakanspm->select('id_detrcnkirim')->get()->toArray();

        $m_setting_kirim = new \Model\Storage\PakanDetRcnKirim_model();
        if ( count($d_dpakanspm) > 0 ) {
            $d_setting_kirim = $m_setting_kirim->whereNotIn('id', $d_dpakanspm)->with(['d_unit', 'd_barang', 'd_ekspedisi', 'd_rdim_submit'])->get()->toArray();
        } else {
            $d_setting_kirim = $m_setting_kirim->with(['d_unit', 'd_barang', 'd_ekspedisi', 'd_rdim_submit'])->get()->toArray();
        }

        return $d_setting_kirim;
    }

    public function get_ekspedisi()
    {
        $m_supl = new \Model\Storage\Supplier_model();
        $d_nomor = $m_supl->where('jenis', 'ekspedisi')->where('tipe', 'supplier')->select('nomor')->distinct('nomor')->get()->toArray();

        $datas = array();
        if ( !empty($d_nomor) ) {
            foreach ($d_nomor as $nomor) {
                $supl = $m_supl->where('nomor', $nomor['nomor'])
                               ->first()->toArray();

                array_push($datas, $supl);
            }
        }

        return $datas;
    }

    public function mapping_data()
    {
    	$_data = $this->get_data();

        $data = array();
        
        foreach ($_data as $k_data => $v_data) {
            $tot_kg = 0;
            $tot_zak = 0;
            $jml_detail_per_unit = 0;
            foreach ($v_data['kartu_pakan_detail'] as $k_kpd => $v_kpd) {

                $m_dprk = new \Model\Storage\PakanDetRcnKirim_model();
                $d_pdrk = $m_dprk->where('noreg', trim($v_data['data_rdim_submit']['noreg']) )->first();

                if ( empty($d_pdrk) ) {
                    $kandang = $v_data['data_rdim_submit']['d_kandang']['kandang'];
                    if ( strlen($kandang) < 2 ) {
                        $kandang = 0 . $v_data['data_rdim_submit']['d_kandang']['kandang'];
                    }

                    $data[ $v_data['data_rdim_submit']['d_kandang']['d_unit']['id'] ]['detail'][ $v_kpd['id'] ]['peternak'] = $v_data['data_rdim_submit']['mitra']['d_mitra']['nama'];
                    $data[ $v_data['data_rdim_submit']['d_kandang']['d_unit']['id'] ]['detail'][ $v_kpd['id'] ]['kandang'] = $kandang;
                    $data[ $v_data['data_rdim_submit']['d_kandang']['d_unit']['id'] ]['detail'][ $v_kpd['id'] ]['populasi'] = $v_data['data_rdim_submit']['populasi'];
                    $data[ $v_data['data_rdim_submit']['d_kandang']['d_unit']['id'] ]['detail'][ $v_kpd['id'] ]['noreg'] = $v_data['data_rdim_submit']['noreg'];
                    $data[ $v_data['data_rdim_submit']['d_kandang']['d_unit']['id'] ]['detail'][ $v_kpd['id'] ]['umur'] = $v_kpd['umur'];
                    $data[ $v_data['data_rdim_submit']['d_kandang']['d_unit']['id'] ]['detail'][ $v_kpd['id'] ]['kode_pakan'] = $v_kpd['d_barang']['kode'];
                    $data[ $v_data['data_rdim_submit']['d_kandang']['d_unit']['id'] ]['detail'][ $v_kpd['id'] ]['pakan'] = $v_kpd['d_barang']['nama'];
                    $data[ $v_data['data_rdim_submit']['d_kandang']['d_unit']['id'] ]['detail'][ $v_kpd['id'] ]['jml_kg'] = $v_kpd['setting'];
                    $data[ $v_data['data_rdim_submit']['d_kandang']['d_unit']['id'] ]['detail'][ $v_kpd['id'] ]['jml_zak'] = ($v_kpd['rcn_kirim'] == 0) ? 0 : ceil($v_kpd['setting'] / 50) ;
                    $data[ $v_data['data_rdim_submit']['d_kandang']['d_unit']['id'] ]['detail'][ $v_kpd['id'] ]['tgl_kirim'] = $v_kpd['tgl_kirim'];

                    $tot_kg += $v_kpd['setting'];

                    $zak = ($v_kpd['rcn_kirim'] == 0) ? 0 : ceil($v_kpd['setting'] / 50);
                    $tot_zak += $zak;

                    $jml_detail_per_unit++;
                }
            }

            if ( $jml_detail_per_unit > 0 ) {
                $data[ $v_data['data_rdim_submit']['d_kandang']['d_unit']['id'] ]['id'] = $v_data['data_rdim_submit']['d_kandang']['d_unit']['id'];
                $data[ $v_data['data_rdim_submit']['d_kandang']['d_unit']['id'] ]['nama'] = $v_data['data_rdim_submit']['d_kandang']['d_unit']['nama'];
                $data[ $v_data['data_rdim_submit']['d_kandang']['d_unit']['id'] ]['tot_kg'] = $tot_kg;
                $data[ $v_data['data_rdim_submit']['d_kandang']['d_unit']['id'] ]['tot_zak'] = $tot_zak;
            }
        }

    	return $data;
    }

    public function mapping_data_pme()
    {
        $_data = $this->get_data_setting_kirim();

        $data = array();
        
        foreach ($_data as $k_data => $v_data) {
            $data[ $v_data['unit'] ]['id'] = $v_data['unit'];
            $data[ $v_data['unit'] ]['nama'] = $v_data['d_unit']['nama'];

            $data[ $v_data['unit'] ]['detail'][ $v_data['id'] ]['id'] = $v_data['id'];
            $data[ $v_data['unit'] ]['detail'][ $v_data['id'] ]['ekspedisi'] = $v_data['ekspedisi'];
            $data[ $v_data['unit'] ]['detail'][ $v_data['id'] ]['nama_ekspedisi'] = $v_data['d_ekspedisi']['nama'];
            $data[ $v_data['unit'] ]['detail'][ $v_data['id'] ]['tgl_kirim'] = $v_data['tgl_kirim'];
            $data[ $v_data['unit'] ]['detail'][ $v_data['id'] ]['mitra'] = $v_data['d_rdim_submit']['mitra']['d_mitra']['nama'];
            $data[ $v_data['unit'] ]['detail'][ $v_data['id'] ]['kandang'] = $v_data['d_rdim_submit']['d_kandang']['kandang'];
            $data[ $v_data['unit'] ]['detail'][ $v_data['id'] ]['alamat'] = $v_data['d_rdim_submit']['mitra']['d_mitra']['alamat_jalan'];
            $data[ $v_data['unit'] ]['detail'][ $v_data['id'] ]['pakan'] = $v_data['d_barang']['nama'];
            $data[ $v_data['unit'] ]['detail'][ $v_data['id'] ]['tonase'] = $v_data['jml_kirim'];
            $data[ $v_data['unit'] ]['detail'][ $v_data['id'] ]['zak'] = $v_data['zak_kirim'];
        }

        return $data;
    }

    public function get_ekspedisi_pme()
    {
        $_data = $this->get_data_setting_kirim();

        $data = array();

        foreach ($_data as $k_data => $v_data) {
            $data[ $v_data['ekspedisi'] ] = array(
                'id' => $v_data['ekspedisi'],
                'nama' => $v_data['d_ekspedisi']['nama']
            );
        }

        return $data;
    }

    public function get_data_perusahaan()
    {
        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $d_nomor = $m_perusahaan->select('kode')->distinct('kode')->get()->toArray();

        $datas = array();
        if ( !empty($d_nomor) ) {
            foreach ($d_nomor as $nomor) {
                $perusahaan = $m_perusahaan->where('kode', $nomor['kode'])
                                          ->orderBy('version', 'desc')
                                          ->orderBy('id', 'desc')
                                          ->first()->toArray();

                array_push($datas, $perusahaan);
            }
        }

        return $datas;
    }

    public function save_per_unit()
    {
        $params = $this->input->post('params');

        try {
            $m_prk = new \Model\Storage\PakanRcnKirim_model();
            $now = $m_prk->getDate();
            $_id = $m_prk->getNextIdentity();

            $m_prk->id = $_id;
            $m_prk->tgl_trans = $now['waktu'];
            $m_prk->user_submit = $this->userid;
            $m_prk->save();

            $deskripsi_log_prk = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_prk, $deskripsi_log_prk);

            foreach ($params as $k_params => $v_params) {
                $m_dprk = new \Model\Storage\PakanDetRcnKirim_model();
                $_did = $m_dprk->getNextIdentity();

                $m_dprk->id = $_did;
                $m_dprk->id_rcnkirim = $_id;
                $m_dprk->unit = $v_params['unit'];
                $m_dprk->noreg = $v_params['noreg'];
                $m_dprk->umur = $v_params['umur'];
                $m_dprk->pakan = $v_params['pakan'];
                $m_dprk->tgl_kirim = $v_params['tgl_rcn_kirim'];
                $m_dprk->jml_kirim = $v_params['kg_rcn_kirim'];
                $m_dprk->zak_kirim = $v_params['zak_rcn_kirim'];
                $m_dprk->ekspedisi = $v_params['ekspedisi_rcn_kirim'];
                // $m_dprk->perusahaan = $v_params['perusahaan'];
                $m_dprk->save();

                $deskripsi_log_pdrk = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $m_dprk, $deskripsi_log_pdrk);
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil disimpan';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function save_spm()
    {
        $params = $this->input->post('params');

        try {
            $m_pspm = new \Model\Storage\PakanSPM_model();
            $now = $m_pspm->getDate();
            $_id = $m_pspm->getNextIdentity();
            $_no_spm = $m_pspm->getNextId();

            $m_pspm->id_spm = $_id;
            $m_pspm->no_spm = $_no_spm;
            $m_pspm->tgl_spm = $now['waktu'];
            $m_pspm->total_spm = $params['tot_tonase'];
            $m_pspm->zak_spm = $params['tot_zak'];
            $m_pspm->ekspedisi = $params['ekspedisi'];
            $m_pspm->save();

            $deskripsi_log_spm = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_pspm, $deskripsi_log_spm);

            foreach ($params['detail'] as $k_params => $v_params) {
                $m_dpspm = new \Model\Storage\DetPakanSPM_model();
                $_id_det = $m_dpspm->getNextIdentity();

                $m_dpspm->id_detspm = $_id_det;
                $m_dpspm->id_spm = $_id;
                $m_dpspm->id_detrcnkirim = $v_params['id'];
                $m_dpspm->save();

                $deskripsi_log_dpspm = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $m_dpspm, $deskripsi_log_dpspm);
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil disimpan';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function load_form_cetak_spm()
    {
        // $m_pspm = new \Model\Storage\PakanSPM_model();
        // $d_pspm = $m_pspm->whereNull('tgl_cetak')->with(['ekspedisi'])->get()->toArray();

        // $data['data'] = $d_pspm;
        $data['data'] = null;
        $this->load->view('transaksi/spm/cetak_spm_form', $data);
    }

    public function list_cetak_spm()
    {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;

        $m_pspm = new \Model\Storage\PakanSPM_model();
        $d_pspm = $m_pspm->whereNull('tgl_cetak')->with(['ekspedisi'])->get()->toArray();

        $content['data'] = $d_pspm;
        $html = $this->load->view('transaksi/spm/list_cetak_spm', $content, TRUE);

        echo $html;
    }

    public function update_tgl_cetak_spm($value='')
    {
        $no_spm = $this->input->post('params');

        try {
            $m_pspm = new \Model\Storage\PakanSPM_model();
            $now = $m_pspm->getDate();
            
            $m_pspm->where('no_spm', $no_spm)->update( array('tgl_cetak' => $now['waktu']) );

            $this->result['status'] = 1;
            $this->result['message'] = 'Tgl cetak berhasil di update.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function mapping_cetak_spm($d_pspm)
    {
        $data = array();

        if ( !empty($d_pspm) ) {
            foreach ($d_pspm as $k_data => $v_data) {
                $data = array(
                    'no_spm' => $v_data['no_spm'],
                    'tgl_spm' => $v_data['tgl_spm'],
                    'ekspedisi' => $v_data['ekspedisi']['nama'],
                    'kg' => $v_data['total_spm'],
                    'zak' => $v_data['total_spm'],
                    'detail' => array(),
                );

                $tot_kg = 0;
                $tot_zak = 0;
                foreach ($v_data['detail'] as $k_detail => $v_detail) {
                    $data['detail'][ $v_detail['rencana_kirim']['unit'] ]['id'] = $v_detail['rencana_kirim']['unit'];
                    $data['detail'][ $v_detail['rencana_kirim']['unit'] ]['nama'] = $v_detail['rencana_kirim']['d_unit']['nama'];
                    $data['detail'][ $v_detail['rencana_kirim']['unit'] ]['sub_detail'][ $v_detail['rencana_kirim']['id'] ]['id'] = $v_detail['rencana_kirim']['id'];
                    $data['detail'][ $v_detail['rencana_kirim']['unit'] ]['sub_detail'][ $v_detail['rencana_kirim']['id'] ]['tgl_kirim'] = $v_detail['rencana_kirim']['tgl_kirim'];
                    $data['detail'][ $v_detail['rencana_kirim']['unit'] ]['sub_detail'][ $v_detail['rencana_kirim']['id'] ]['peternak'] = $v_detail['rencana_kirim']['d_rdim_submit']['mitra']['d_mitra']['nama'];
                    $data['detail'][ $v_detail['rencana_kirim']['unit'] ]['sub_detail'][ $v_detail['rencana_kirim']['id'] ]['kandang'] = $v_detail['rencana_kirim']['d_rdim_submit']['d_kandang']['kandang'];
                    $data['detail'][ $v_detail['rencana_kirim']['unit'] ]['sub_detail'][ $v_detail['rencana_kirim']['id'] ]['alamat'] = $v_detail['rencana_kirim']['d_rdim_submit']['mitra']['d_mitra']['alamat_jalan'];
                    $data['detail'][ $v_detail['rencana_kirim']['unit'] ]['sub_detail'][ $v_detail['rencana_kirim']['id'] ]['kg'] = $v_detail['rencana_kirim']['jml_kirim'];
                    $data['detail'][ $v_detail['rencana_kirim']['unit'] ]['sub_detail'][ $v_detail['rencana_kirim']['id'] ]['zak'] = $v_detail['rencana_kirim']['zak_kirim'];

                    $tot_kg += $v_detail['rencana_kirim']['jml_kirim'];
                    $tot_zak += $v_detail['rencana_kirim']['zak_kirim'];
                }

                $data['detail'][ $v_detail['rencana_kirim']['unit'] ]['kg'] = $tot_kg;
                $data['detail'][ $v_detail['rencana_kirim']['unit'] ]['zak'] = $tot_zak;
            }
        }

        return $data;
    }

    public function cetak_spm($no_spm='')
    {
        $no_spm = exDecrypt($no_spm);

        $m_pspm = new \Model\Storage\PakanSPM_model();
        $now = $m_pspm->getDate();
        $d_pspm = $m_pspm->where('no_spm', $no_spm)->with(['detail', 'ekspedisi'])->get()->toArray();

        $data = $this->mapping_cetak_spm($d_pspm);

        // cetak_r( $this->mapping_cetak_spm($d_pspm) );

        // NOTE: pesan ketika nomor do tidak ditemukan
        if ( empty($d_pspm) ) {
          echo "<h1>DO gak ono... </h1><br><h3>ojo ngawur koen, la' gak pingin tak bedil...!!!</h3>";
          die('<u>Server Ngamuk</u>');
        }

        $style = array(
          'vpadding'      =>'auto',
          'hpadding'      =>'auto',
          'fgcolor'       => array(0, 0, 0),
          'bgcolor'       => false, //array(),
          'module_width'  => 1.2,
          'module_height' => 0.4,
        );

        $this->load->library('Pdf');
        $pdf = new Pdf('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
        $pdf -> setFontSubsetting(false);
        $pdf -> SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        $pdf -> SetPrintHeader(false);
        $pdf -> SetPrintFooter(false);
        $pdf -> SetAutoPageBreak(TRUE, 1);
        $pdf -> setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set margins
        // $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        // $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        // $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->AddPage();

        $html = '<b>Surat Perintah Muat<b>';
        $pdf->SetFont('', '', 14);
        // $pdf->SetFillColor(244, 0, 0, 0);
        $pdf->Rect(0, 0, $pdf->getPageWidth(), 20, 'DF', "", array(244, 244, 244));
        $pdf->writeHTMLCell(0, 0, '', 3, $html, 0, 0, 0, true, 'C', false);

        $html = '<b>Mitra Gemuk Bersama</b>';
        $pdf->SetFont('', '', 14);
        $pdf->writeHTMLCell(0, 0, '', 10, $html, 0, 0, 0, true, 'C', false);

        $html = '
            <table style="padding: 5px;">
                <tbody>
                    <tr>
                        <td style="width: 25%;"><b>NO SPM</b></td>
                        <td style="width: 3%;">:</td>
                        <td><b>'.$data['no_spm'].'</b></td>
                    </tr>
                    <tr>
                        <td style="width: 25%;"><b>Tgl SPM</b></td>
                        <td style="width: 3%;">:</td>
                        <td style="width: 30%;"><b>'.tglIndonesia($data['tgl_spm'], '-', ' ', true).'</b></td>
                        <td style="width: 10%;"><b>Jumlah</b></td>
                        <td style="width: 3%;">:</td>
                        <td><b>'.angkaDecimal($data['kg']).' Kg, '.angkaRibuan($data['zak']).' Zak</b></td>
                    </tr>
                    <tr>
                        <td style="width: 25%;"><b>Diberikan Kepada</b></td>
                        <td style="width: 3%;">:</td>
                        <td><b>'.$data['ekspedisi'].'</b></td>
                    </tr>
                </tbody>
            </table>
        ';
        $pdf->SetFont('', '', 10);
        $pdf->writeHTMLCell(0, 0, 3, 22, $html, 0, 1, 0, true, 'L', false);

        $html = '<table style="padding: 5px; width: 100%;">
                <tbody>';
        foreach ($data['detail'] as $k_detail => $v_detail) {
            $html .= '<tr>
                        <td style="width: 70%;"><b>Tujuan : '.$v_detail['nama'].'</b></td>
                        <td style="width: 33.5%; text-align: right;"><b>Total : '.angkaDecimal($v_detail['kg']).' Kg, '.angkaRibuan($v_detail['zak']).' Zak</b></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; text-align: center; width: 15%;"><b>Tgl Kirim</b></td>
                        <td style="border: 1px solid #000; text-align: center; width: 28.5%;"><b>Peternak</b></td>
                        <td style="border: 1px solid #000; text-align: center; width: 10%;"><b>Kandang</b></td>
                        <td style="border: 1px solid #000; text-align: center; width: 30%;"><b>Alamat</b></td>
                        <td style="border: 1px solid #000; text-align: center; width: 10%;"><b>Kg</b></td>
                        <td style="border: 1px solid #000; text-align: center; width: 10%;"><b>Zak</b></td>
                    </tr>';
            foreach ($v_detail['sub_detail'] as $k_sub => $v_sub) {
                $html .= '<tr>
                            <td style="border: 1px solid #000; width: 15%;">'.tglIndonesia($v_sub['tgl_kirim'], '-', ' ', true).'</td>
                            <td style="border: 1px solid #000; width: 28.5%;">'.$v_sub['peternak'].'</td>
                            <td style="border: 1px solid #000; text-align: right; width: 10%;">'.$v_sub['kandang'].'</td>
                            <td style="border: 1px solid #000; width: 30%;">'.$v_sub['alamat'].'</td>
                            <td style="border: 1px solid #000; text-align: right; width: 10%;">'.angkaDecimal($v_sub['kg']).'</td>
                            <td style="border: 1px solid #000; text-align: right; width: 10%;">'.angkaRibuan($v_sub['zak']).'</td>
                        </tr>';
            }
        }
        $html .= '</tbody>
            </table>';

        $pdf->SetFont('', '', 9);
        $pdf->writeHTMLCell(0, 0, 3, 47, $html, 0, 1, 0, true, 'L', false);

        $html = '<table style="padding-top: 20px; padding-right: 20px; padding-left: 20px;">
                    <tbody>
                        <tr>
                            <td colspan="2" style="width: 33.3%; text-align: center;">Surabaya, '.tglIndonesia(date('Y-m-d'), '-', ' ', true).'</td>
                            <td colspan="2" style="width: 33.3%; text-align: center;">Mengetahui, </td>
                            <td colspan="2" style="width: 33.3%; text-align: center;">Menyetujui, </td>
                        </tr>
                        <tr><td></td></tr>
                        <tr>
                            <td style="text-align: left;">(</td>
                            <td style="text-align: right;">)</td>
                            <td style="text-align: left;">(</td>
                            <td style="text-align: right;">)</td>
                            <td style="text-align: left;">(</td>
                            <td style="text-align: right;">)</td>
                        </tr>
                    </tbody>
                </table>';

        $pdf->SetFont('', '', 9);
        $pdf->writeHTMLCell(0, 0, 3, '', $html, 0, 1, 0, true, 'L', false);

        ob_end_clean();
        $filename = $no_spm;
        $pdf->Output($filename . '.pdf', 'I');
    }

    public function tes($no_spm='')
    {
        $no_spm = exDecrypt($no_spm);

        $m_pspm = new \Model\Storage\PakanSPM_model();
        $d_pspm = $m_pspm->where('no_spm', $no_spm)->with(['detail', 'ekspedisi'])->get()->toArray();

        $data = $this->mapping_cetak_spm($d_pspm);

        cetak_r($data);
    }
}