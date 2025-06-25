<?php defined('BASEPATH') or exit('No direct script access allowed');

class BASTTB extends Public_Controller
{
    private $pathView = 'transaksi/basttb/';
    private $url;
    /**
     * Constructor
     */
    public function __construct()
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
    public function index()
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            // $this->set_title('Berita Acara Serah Terima Titip Budidaya');
            $this->add_external_js(array('assets/jquery-ui/js/MonthPicker.js',
                'assets/jquery/list.min.js',
                'assets/transaksi/basttb/js/basttb.js'));
            $this->add_external_css(array(
                'assets/bootstrap-3.3.5/css/awesome-bootstrap-checkbox.css',
                'assets/jquery-ui/css/MonthPicker.css',
                'assets/jquery/loading/css/loading.css',
                'assets/transaksi/basttb/css/basttb.css'));
            $data = $this->includes;

            $content['akses'] = $akses;
            $content['datas'] = null;
            $content['title_panel'] = 'Berita Acara Serah Terima Titip Budidaya (BASTTB)';

            // Load Indexx
            $content['riwayat'] = $this->load->view($this->pathView . 'list_basttb', $content, true);
            $content['action'] = $this->load->view($this->pathView . 'input_basttb', $content, true);

            $data['title_menu'] = 'Berita Acara Serah Terima Titip Budidaya (BASTTB)';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getLists()
    {
        $m_real_docin = new \Model\Storage\RealDocin_model();
        $d_real_docin = $m_real_docin->with(['logs', 'dRdimSubmit'])->orderBy('id', 'DESC')->get()->toArray();

        $content['datas'] = $d_real_docin;
        $html = $this->load->view($this->pathView . 'list_basttb', $content, true);

        echo $html;
    }

    public function getNoreg()
    {
        $_data = array();
        $data = array();

        $start_date = $this->input->post('periode');

        $end_date = date("Y-m-t", strtotime($start_date));

        $m_rdim_header = new \Model\Storage\Rdim_model();

        $m_rdim = new \Model\Storage\RdimSubmit_model();
        $d_rdim = $m_rdim->whereBetween('tgl_docin', [$start_date, $end_date] )->where('status', 1)->orderBy('noreg', 'ASC')->with(['dMitraMapping'])->get()->toArray();

        $status = getStatus('submit');

        if ( !empty($d_rdim) ) {
            foreach ($d_rdim as $key => $value) {
                $d_rdim_header = $m_rdim_header->where('id', $value['id_rdim'])->where('g_status', $status)->first();
                if ( !empty($d_rdim_header['id']) ) {
                    $_data[$value['noreg']] = array(
                        'id' => $value['id'],
                        'noreg' => $value['noreg'],
                        'id_mitra' => $value['id'],
                        'mitra' => $value['d_mitra_mapping']['d_mitra']['nama'],
                    );
                }
            }
        }

        if ( !empty($_data) ) {
            foreach ($_data as $key => $v_data) {
                $data[] = $v_data;
            }
        }

        if ( count($d_rdim) > 0 ) {
            $this->result['status'] = 1;
            $this->result['content'] = $data;
        }

        display_json($this->result);
    }

    public function loadContent_BASTTB()
    {
        $id = $this->input->get('id');
        $resubmit = $this->input->get('resubmit');
        $content = array();
        $html = "url not found";
        
        if ( !empty($id) && is_numeric($id) && $resubmit != 'edit' ) {
            // NOTE: view data BASTTB (ajax)
            $html = $this->view($id);
        } else if ( !empty($id) && is_numeric($id) && $resubmit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $html = $this->update($id);
        }else{
            $content['title_panel'] = 'Berita Acara Serah Terima Titip Budidaya';
            $html = $this->load->view($this->pathView . 'input_basttb', $content, true);
        }

        echo $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        $id_saved = null;

        $status = $params['action'];
        $g_status = getStatus($status);

        $model = new \Model\Storage\Conf();
        $now = $model->getDate();

        if ( count($params) > 0 ) {
            $m_real_docin = new \Model\Storage\RealDocin_model();

            $m_real_docin->id_user = $this->userid;
            $m_real_docin->tgl_trans = $now['waktu'];
            $m_real_docin->noreg = $params['data']['noreg'];
            $m_real_docin->rdim_submit = $params['data']['id_rdim'];
            $m_real_docin->tgl_terima = $params['data']['tgl_terima'];
            $m_real_docin->no_sj = $params['data']['no_sj'];
            $m_real_docin->ket_sj = $params['data']['ket_sj'];
            $m_real_docin->sj_box = $params['data']['sj_box'];
            $m_real_docin->sj_ekor = $params['data']['sj_ekor'];
            $m_real_docin->terima_box = $params['data']['terima_box'];
            $m_real_docin->terima_ekor = $params['data']['terima_ekor'];
            $m_real_docin->terima_mati = $params['data']['terima_mati'];
            $m_real_docin->terima_afkir = $params['data']['terima_afkir'];
            $m_real_docin->terima_awal = $params['data']['terima_awal'];
            $m_real_docin->terima_bb = $params['data']['terima_bb'];
            $m_real_docin->selisih_ekor = $params['data']['selisih_ekor'];
            $m_real_docin->ket_terima = $params['data']['ket_terima'];
            $m_real_docin->save();

            $id_saved = $m_real_docin->id;

            $deskripsi_log_gaktifitas = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_real_docin, $deskripsi_log_gaktifitas );
        }else {
            display_json(['status'=>0, 'message'=>'error, segera hubungi tim IT']);
        }

        $this->result['status'] = 1;
        $this->result['content'] = $this->view($id_saved);
        $this->result['message'] = 'Data berhasil disimpan';

        display_json($this->result);
    }

    public function edit()
    {
        $params = $this->input->post('params');

        $id_saved = null;

        $status = $params['action'];
        $g_status = getStatus($status);

        $model = new \Model\Storage\Conf();
        $now = $model->getDate();

        if ( count($params) > 0 ) {
            $m_real_docin = new \Model\Storage\RealDocin_model();

            $m_real_docin->where('id', $params['id_old'])->update(
                    array(
                        'status' => 0
                    )
                );

            $m_real_docin->id_user = $this->userid;
            $m_real_docin->tgl_trans = $now['waktu'];
            $m_real_docin->noreg = $params['data']['noreg'];
            $m_real_docin->rdim_submit = $params['data']['id_rdim'];
            $m_real_docin->tgl_terima = $params['data']['tgl_terima'];
            $m_real_docin->no_sj = $params['data']['no_sj'];
            $m_real_docin->ket_sj = $params['data']['ket_sj'];
            $m_real_docin->sj_box = $params['data']['sj_box'];
            $m_real_docin->sj_ekor = $params['data']['sj_ekor'];
            $m_real_docin->terima_box = $params['data']['terima_box'];
            $m_real_docin->terima_ekor = $params['data']['terima_ekor'];
            $m_real_docin->terima_mati = $params['data']['terima_mati'];
            $m_real_docin->terima_afkir = $params['data']['terima_afkir'];
            $m_real_docin->terima_awal = $params['data']['terima_awal'];
            $m_real_docin->terima_bb = $params['data']['terima_bb'];
            $m_real_docin->selisih_ekor = $params['data']['selisih_ekor'];
            $m_real_docin->ket_terima = $params['data']['ket_terima'];
            $m_real_docin->save();

            $id_saved = $m_real_docin->id;

            $deskripsi_log_gaktifitas = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $m_real_docin, $deskripsi_log_gaktifitas );
        }else {
            display_json(['status'=>0, 'message'=>'error, segera hubungi tim IT']);
        }

        $this->result['status'] = 1;
        $this->result['content'] = $this->view($id_saved);
        $this->result['message'] = 'Data berhasil di update';

        display_json($this->result);
    }

    public function view($id)
    {
        $data = array();

        $m_real_docin = new \Model\Storage\RealDocin_model();
        $d_real_docin = $m_real_docin->where('id',$id)->with(['logs', 'dRdimSubmit'])->first();

        $content['data'] = $d_real_docin;

        $html = $this->load->view($this->pathView . 'view_basttb', $content, true);

        return $html;
    }

    public function update($id)
    {
        $data = array();

        $m_real_docin = new \Model\Storage\RealDocin_model();
        $d_real_docin = $m_real_docin->where('id',$id)->with(['logs', 'dRdimSubmit'])->first();

        $content['data'] = $d_real_docin;

        $html = $this->load->view($this->pathView . 'edit_basttb', $content, true);

        return $html;
    }

    public function tes()
    {
        $date = "2013-09-15";
        $date_new = substr(tglIndonesia($date, '-', ', ', true), 4, 25);
        // $str = 'Jawa Timur 10';
        // $filter = numToRoman( (int) filter_var($str, FILTER_SANITIZE_NUMBER_INT) );

        echo $date_new;
    }
}
