<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PenerimaanPakan_old extends Public_Controller {

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
                "assets/transaksi/penerimaan_pakan/js/penerimaan-pakan.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/transaksi/penerimaan_pakan/css/penerimaan-pakan.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['unit'] = $this->get_unit();

            $data['title_menu'] = 'Penerimaan Pakan';
            $data['view'] = $this->load->view('transaksi/penerimaan_pakan/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_unit()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'UN')->orderBy('nama', 'asc')->get()->toArray();

        return $d_wilayah;
    }

    public function get_noreg()
    {
        $unit = $this->input->post('params');
        
        try {
            // $m_setting_kirim = new \Model\Storage\PakanDetRcnKirim_model();
            // $d_setting_kirim = $m_setting_kirim->where('unit', $params)->with(['get_noreg_not_terima'])->get()->toArray();

            $m_pakan_terima = new \Model\Storage\PakanTerima_model();
            $d_pakan_terima = $m_pakan_terima->select('id_detspm')->where('unit', $unit)->get()->toArray();

            $m_dpakan_spm = new \Model\Storage\DetPakanSPM_model();
            $d_dpakan_spm = $m_dpakan_spm->select('id_detrcnkirim')->whereNotIn('id_detspm', $d_pakan_terima)->get()->toArray();

            $m_setting_kirim = new \Model\Storage\PakanDetRcnKirim_model();
            $d_setting_kirim = $m_setting_kirim->select('noreg')->where('unit', $unit)->whereIn('id', $d_dpakan_spm)->groupBy('noreg')->get();

            $this->result['status'] = 1;
            $this->result['content'] = $d_setting_kirim;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function get_pakan($tipe)
    {
        $m_brg = new \Model\Storage\Barang_model();
        $d_nomor = $m_brg->select('kode')->distinct('kode')->where('tipe', $tipe)->get()->toArray();

        $supplier = 'supplier_not_pakan';
        if ( $tipe == 'pakan' ) {
            $supplier = 'supplier';
        }

        $datas = array();
        if ( !empty($d_nomor) ) {
            foreach ($d_nomor as $nomor) {
                $pelanggan = $m_brg->where('tipe', $tipe)
                                          ->where('kode', $nomor['kode'])
                                          ->orderBy('version', 'desc')
                                          ->orderBy('id', 'desc')
                                          ->with([$supplier,'logs'])
                                          ->first()->toArray();

                array_push($datas, $pelanggan);
            }
        }

        return $datas;
    }

    public function set_value()
    {
        $noreg = $this->input->post('params');
        
        try {
            $m_setting_kirim = new \Model\Storage\PakanDetRcnKirim_model();
            $d_setting_kirim = $m_setting_kirim->where('noreg', $noreg)->with(['d_ekspedisi', 'd_rdim_submit', 'd_barang', 'det_pakanspm'])->get()->toArray();

            $mapping = $this->mapping_value($d_setting_kirim);

            $content['data'] = $mapping;
            $content['pakan'] = $this->get_pakan('pakan');
            $html = $this->load->view('transaksi/penerimaan_pakan/list_penerimaan_pakan', $content, TRUE);

            $data = array(
                'mapping' => $mapping,
                'detail' => $html
            );

            $this->result['status'] = 1;
            $this->result['content'] = $data;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function mapping_value($params)
    {
        $data = array();

        if ( !empty($params) ) {
            foreach ($params as $k_data => $v_data) {
                $data[ $v_data['noreg'] ]['id_peternak'] = $v_data['d_rdim_submit']['nim'];
                $data[ $v_data['noreg'] ]['peternak'] = $v_data['d_rdim_submit']['mitra']['d_mitra']['nama'];
                $data[ $v_data['noreg'] ]['id_ekspedisi'] = $v_data['d_ekspedisi']['nomor'];
                $data[ $v_data['noreg'] ]['ekspedisi'] = $v_data['d_ekspedisi']['nama'];
                $data[ $v_data['noreg'] ]['kandang'] = $v_data['d_rdim_submit']['d_kandang']['kandang'];
                $data[ $v_data['noreg'] ]['populasi'] = $v_data['d_rdim_submit']['populasi'];

                // $data[ $v_data['noreg'] ]['detail'][ $v_data['id'] ]['id'] = $v_data['id'];
                // $data[ $v_data['noreg'] ]['detail'][ $v_data['id'] ]['id_pakan'] = $v_data['pakan'];
                // $data[ $v_data['noreg'] ]['detail'][ $v_data['id'] ]['pakan'] = $v_data['d_barang']['nama'];
                // $data[ $v_data['noreg'] ]['detail'][ $v_data['id'] ]['jml_pakan'] = $v_data['zak_kirim'];
                // $data[ $v_data['noreg'] ]['detail'][ $v_data['id'] ]['tonase'] = $v_data['jml_kirim'];

                $m_pakan_terima = new \Model\Storage\PakanTerima_model();
                $d_pakan_terima = $m_pakan_terima->where('id_detspm', $v_data['det_pakanspm']['id_detspm'])->first();

                if ( empty($d_pakan_terima) ) {
                    $data[ $v_data['noreg'] ]['detail'][] = array(
                        'id' => $v_data['id'],
                        'id_detspm' => $v_data['det_pakanspm']['id_detspm'],
                        'id_pakan' => $v_data['pakan'],
                        'pakan' => $v_data['d_barang']['nama'],
                        'jml_pakan' => $v_data['zak_kirim'],
                        'tonase' => $v_data['jml_kirim'],
                    );
                }
            }
        }

        return $data;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            foreach ($params['detail'] as $k_detail => $v_detail) {
                $m_pakan_terima = new \Model\Storage\PakanTerima_model();
                $m_pakan_terima->id = $m_pakan_terima->getNextIdentity();
                $m_pakan_terima->id_detspm = $v_detail['id_detspm'];
                $m_pakan_terima->unit = $params['unit'];
                $m_pakan_terima->noreg = $params['noreg'];
                $m_pakan_terima->tgl_terima = $params['tgl_terima'];
                $m_pakan_terima->ekspedisi = $params['ekspedisi'];
                $m_pakan_terima->no_sj = $params['no_sj'];
                $m_pakan_terima->nama_sopir = $params['nama_sopir'];
                $m_pakan_terima->nopol = $params['nopol'];
                $m_pakan_terima->pakan_terima = $v_detail['pakan_terima'];
                $m_pakan_terima->zak_terima = $v_detail['zak_terima'];
                $m_pakan_terima->kg_terima = $v_detail['kg_terima'];
                $m_pakan_terima->save();
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data Penerimaan Pakan berhasil disimpan.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function tes($no_spm='')
    {
        // $no_spm = exDecrypt($no_spm);

        // $m_pspm = new \Model\Storage\PakanSPM_model();
        // $d_pspm = $m_pspm->where('no_spm', $no_spm)->with(['detail', 'ekspedisi'])->get()->toArray();

        // $data = $this->mapping_cetak_spm($d_pspm);

        $m_pakan_terima = new \Model\Storage\PakanTerima_model();
        $d_pakan_terima = $m_pakan_terima->select('id_detspm')->get()->toArray();

        $m_dpakan_spm = new \Model\Storage\DetPakanSPM_model();
        $d_dpakan_spm = $m_dpakan_spm->select('id_detrcnkirim')->whereNotIn('id', $d_pakan_terima)->get()->toArray();

        $m_setting_kirim = new \Model\Storage\PakanDetRcnKirim_model();
        $d_setting_kirim = $m_setting_kirim->select('noreg')->whereIn('id', $d_dpakan_spm)->groupBy('noreg')->get();

        cetak_r($d_setting_kirim->toArray());
    }
}