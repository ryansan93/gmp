<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KirimTerimaPakan extends Public_Controller {

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
                "assets/jquery/list.min.js",
                'assets/jquery/tupage-table/jquery.tupage.table.js',
                "assets/report/ktp/js/ktp.js",
            ));
            $this->add_external_css(array(
                'assets/jquery/tupage-table/jquery.tupage.table.css',
                "assets/report/ktp/css/ktp.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['data'] = null;
            $content['title_menu'] = 'Pengiriman dan Penerimaan Pakan';

            // Load Indexx
            $data['view'] = $this->load->view('report/ktp/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function list_ktp()
    {
        $akses = hakAkses($this->url);

        $params = $this->input->post('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $data = $this->mapping_data($start_date, $end_date);

        $content['akses'] = $akses;
        $content['data'] = $data;
        $html = $this->load->view('report/ktp/list', $content, true);

        $this->result['status'] = 1;
        $this->result['content'] = $html;

        display_json($this->result);
    }

    public function get_data($start_date, $end_date)
    {
        $m_pdrcnkirim = new \Model\Storage\PakanDetRcnKirim_model();
        $d_pdrcnkirim = $m_pdrcnkirim->whereBetween('tgl_kirim', [$start_date, $end_date])
                                     ->with(['d_unit', 'd_barang', 'd_ekspedisi', 'd_rdim_submit', 'det_pakanspm'])
                                     ->get()->toArray();

        return $d_pdrcnkirim;
    }

    public function mapping_data($start_date, $end_date)
    {
        $data = array();
        $_data = $this->get_data($start_date, $end_date);

        foreach ($_data as $k => $val) {
            $data[] = array(
                'kota' => $val['d_unit']['nama'],
                'peternak' => $val['d_rdim_submit']['mitra']['d_mitra']['nama'],
                'kandang' => $val['d_rdim_submit']['d_kandang']['kandang'],
                'populasi' => $val['d_rdim_submit']['populasi'],
                'umur' => $val['umur'],
                'pakan' => $val['d_barang']['nama'],
                'no_spm' => $val['det_pakanspm']['pakan_spm']['no_spm'],
                'rcn_tgl' => $val['tgl_kirim'],
                'rcn_kg' => $val['jml_kirim'],
                'rcn_zak' => $val['zak_kirim'],
                'rcn_ekspedisi' => $val['d_ekspedisi']['nama'],
                'real_tgl_kirim' => $val['det_pakanspm']['pakan_spm']['tgl_spm'],
                'real_tgl_tiba' => $val['det_pakanspm']['terima_pakan']['tgl_terima'],
                'no_sj' => $val['det_pakanspm']['terima_pakan']['no_sj'],
                'real_kg' => $val['det_pakanspm']['terima_pakan']['kg_terima'],
                'real_zak' => $val['det_pakanspm']['terima_pakan']['zak_terima'],
                'real_ekspedisi' => $val['det_pakanspm']['terima_pakan']['d_ekspedisi']['nama']
            );
        }

        $d = $this->msort($data, 'kota');

        return $d;
    }

    public function msort($array, $key, $sort_flags = SORT_REGULAR) {
        if (is_array($array) && count($array) > 0) {
            if (!empty($key)) {
                $mapping = array();
                foreach ($array as $k => $v) {
                    $sort_key = '';
                    if (!is_array($key)) {
                        $sort_key = $v[$key];
                    } else {
                        // @TODO This should be fixed, now it will be sorted as string
                        foreach ($key as $key_key) {
                            $sort_key .= $v[$key_key];
                        }
                        $sort_flags = SORT_STRING;
                    }
                    $mapping[$k] = $sort_key;
                }
                asort($mapping, $sort_flags);
                $sorted = array();
                foreach ($mapping as $k => $v) {
                    $sorted[] = $array[$k];
                }
                return $sorted;
            }
        }
        return $array;
    }
}