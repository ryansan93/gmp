<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Feed extends Public_Controller {

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
                "assets/parameter/feed/js/feed.js",
            ));
            $this->add_external_css(array(
                "assets/parameter/feed/css/feed.css",
            ));

            $data = $this->includes;

            $m_pakan = new \Model\Storage\Pakan_model();
            $d_pakan = $m_pakan->with(['lampiran', 'logs'])->orderBy('id', 'DESC')->get()->toArray();

            $content['akses'] = $akses;
            $content['data'] = $d_pakan;
            $content['title_panel'] = 'Master FEED';

            // Load Indexx
            $data['view'] = $this->load->view('parameter/feed/index', $content, TRUE);
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
        $html = $this->load->view('parameter/feed/add_form', $content);
        
        return $html;
    }

    public function view_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        $m_pakan = new \Model\Storage\Pakan_model();
        $d_pakan = $m_pakan->where('id', $id)
                           ->with(['lampiran', 'logs'])
                           ->orderBy('id', 'DESC')
                           ->first()->toArray();

        $content['akses'] = $akses;
        $content['data'] = $d_pakan;
        $html = $this->load->view('parameter/feed/view_form', $content);
        
        return $html;
    }

    public function edit_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        $m_pakan = new \Model\Storage\Pakan_model();
        $d_pakan = $m_pakan->where('id', $id)
                           ->with(['lampiran', 'logs'])
                           ->orderBy('id', 'DESC')
                           ->first();

        $content['akses'] = $akses;
        $content['data'] = $d_pakan;
        $html = $this->load->view('parameter/feed/edit_form', $content);
        
        return $html;
    }

    public function list_feed()
    {
        $akses = hakAkses($this->url);

        $m_pakan = new \Model\Storage\Pakan_model();
        $d_pakan = $m_pakan->with(['lampiran', 'logs'])->orderBy('id', 'DESC')->get()->toArray();

        $content['akses'] = $akses;
        $content['data'] = $d_pakan;
        $html = $this->load->view('parameter/feed/list', $content);
        
        echo $html;
    }

    public function save()
    {
        $status = 'submit';

        $g_status = getStatus($status);

        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

        if (!empty($files)) {
            $mappingFiles = mappingFiles($files);
        }

        try {
            // NOTE: lampiran mitra
            $lampirans = $params['lampirans'];
            if ( !empty($lampirans) ) {
                foreach ($lampirans as $lampiran) {
                    $file = $mappingFiles[ $lampiran['sha1'] . '_' . $lampiran['name'] ] ?: '';
                    $file_name = $path_name = null;
                    $isMoved = 0;
                    if (!empty($file)) {
                        $moved = uploadFile($file);
                        $file_name = $moved['name'];
                        $path_name = $moved['path'];
                        $isMoved = $moved['status'];
                    }
                    if ($isMoved) {
                        $m_pakan = new \Model\Storage\Pakan_model();

                        $m_pakan->nomor = $m_pakan->getNextDocNum('ADM/HPKN');
                        $m_pakan->mulai = $params['tgl_berlaku'];
                        $m_pakan->g_status = $g_status;
                        $m_pakan->dokumen = $path_name;
                        $m_pakan->pakan1 = $params['pakan1'];
                        $m_pakan->pakan2 = $params['pakan2'];
                        $m_pakan->pakan3 = $params['pakan3'];
                        $m_pakan->save();

                        $last_id = $m_pakan->orderBy('id','DESC')->first();

                        $deskripsi_log_pakan = 'di-'. $status .' oleh ' . $this->userdata['detail_user']['nama_detuser'];
                        Modules::run( 'base/event/save', $m_pakan, $deskripsi_log_pakan );

                        $id_pakan = $last_id['id'];

                        $m_lampiran = new \Model\Storage\Lampiran_model();
                        $m_lampiran->tabel = 'pakan';
                        $m_lampiran->tabel_id = $id_pakan;
                        $m_lampiran->filename = $file_name ;
                        $m_lampiran->path = $path_name;
                        $m_lampiran->status = 1;
                        $m_lampiran->save();
                        Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log_pakan );
                    }else {
                        display_json(['status'=>0, 'message'=>'error, segera hubungi tim IT']);
                    }
                }
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data pakan sukses disimpan';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit()
    {
        $status = 'submit';

        $g_status = getStatus($status);

        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

        if (!empty($files)) {
            $mappingFiles = mappingFiles($files);
        }

        try {
            $deskripsi_log_pakan = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];

            $file_name = $path_name = null;
            
            // NOTE: update lampiran mitra
            $lampirans = $params['lampirans'];
            if ( !empty($lampirans) ) {
                foreach ($lampirans as $lampiran) {
                    $file = $mappingFiles[ $lampiran['sha1'] . '_' . $lampiran['name'] ] ?: '';
                    $isMoved = 0;
                    if (!empty($file)) {
                        $moved = uploadFile($file);
                        $file_name = $moved['name'];
                        $path_name = $moved['path'];
                        $isMoved = $moved['status'];
                    }
                    if ($isMoved) {
                        $m_lampiran = new \Model\Storage\Lampiran_model();

                        if ( !empty($lampiran['old']) ) {
                            $m_lampiran->where('tabel_id', $params['id'])
                                       ->where('tabel', 'pakan')
                                       ->where('path', $lampiran['old'])
                                       ->update( array('status' => 0) );
                        }

                        $m_lampiran->tabel = 'pakan';
                        $m_lampiran->tabel_id = $params['id'];
                        $m_lampiran->filename = $file_name;
                        $m_lampiran->path = $path_name;
                        $m_lampiran->status = 1;
                        $m_lampiran->save();
                        Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log_pakan );
                    }else {
                        display_json(['status'=>0, 'message'=>'error, segera hubungi tim IT']);
                    }
                }
            }

            $m_pakan = new \Model\Storage\Pakan_model();
            $m_pakan->where('id', $params['id'])->update(
                array(
                    'mulai' => $params['tgl_berlaku'],
                    'g_status' => $g_status,
                    'dokumen' => $params['dokumen'],
                    'pakan1' => $params['pakan1'],
                    'pakan2' => $params['pakan2'],
                    'pakan3' => $params['pakan3']
                )
            );

            $d_pakan = $m_pakan->where('id', $params['id'])->first();

            Modules::run( 'base/event/save', $d_pakan, $deskripsi_log_pakan );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data pakan sukses disimpan';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function ack()
    {
        $params = $this->input->post('params');

        $status = 'ack';

        $g_status = getStatus($status);

        $m_pakan = new \Model\Storage\Pakan_model();
        $m_pakan->where('id', $params['id'])
                ->update(
                    array('g_status'=>$g_status)
                    );

        $d_pakan = $m_pakan->where('id', $params['id'])->first();

        $deskripsi_log_pakan = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
        Modules::run( 'base/event/update', $d_pakan, $deskripsi_log_pakan );

        $this->result['status'] = 1;
        $this->result['action'] = strtoupper($params['action']);
        $this->result['message'] = 'Data berhasil di ACK';

        display_json($this->result);
    }

    public function approve()
    {
        $params = $this->input->post('params');

        $status = 'approve';

        $g_status = getStatus($status);

        $m_pakan = new \Model\Storage\Pakan_model();
        $m_pakan->where('id', $params['id'])
                ->update(
                    array('g_status'=>$g_status)
                    );

        $d_pakan = $m_pakan->where('id', $params['id'])->first();

        $deskripsi_log_pakan = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
        Modules::run( 'base/event/update', $d_pakan, $deskripsi_log_pakan );

        $this->result['status'] = 1;
        $this->result['action'] = strtoupper($params['action']);
        $this->result['message'] = 'Data berhasil di APPROVE';

        display_json($this->result);
    }

    public function model($status)
    {
        $m_pakan = new \Model\Storage\Pakan_model();
        $dashboard = $m_pakan->getDashboard_Pakan($status);

        return $dashboard;
    }
}