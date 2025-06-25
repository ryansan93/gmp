<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Doc extends Public_Controller {

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
                "assets/parameter/doc/js/doc.js",
            ));
            $this->add_external_css(array(
                "assets/parameter/doc/css/doc.css",
            ));

            $data = $this->includes;

            $m_doc = new \Model\Storage\Doc_model();
            $d_doc = $m_doc->with(['lampiran', 'logs'])->orderBy('id', 'DESC')->get()->toArray();

            $content['akses'] = $akses;
            $content['data'] = $d_doc;
            $content['title_panel'] = 'Master DOC';

            // Load Indexx
            $data['view'] = $this->load->view('parameter/doc/index', $content, TRUE);
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
        $html = $this->load->view('parameter/doc/add_form', $content);
        
        return $html;
    }

    public function view_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        $m_doc = new \Model\Storage\Doc_model();
        $d_doc = $m_doc->where('id', $id)
                           ->with(['lampiran', 'logs'])
                           ->orderBy('id', 'DESC')
                           ->first()->toArray();

        $content['akses'] = $akses;
        $content['data'] = $d_doc;
        $html = $this->load->view('parameter/doc/view_form', $content);
        
        return $html;
    }

    public function edit_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        $m_doc = new \Model\Storage\Doc_model();
        $d_doc = $m_doc->where('id', $id)
                           ->with(['lampiran', 'logs'])
                           ->orderBy('id', 'DESC')
                           ->first();

        $content['akses'] = $akses;
        $content['data'] = $d_doc;
        $html = $this->load->view('parameter/doc/edit_form', $content);
        
        return $html;
    }

    public function list_doc()
    {
        $akses = hakAkses($this->url);

        $m_doc = new \Model\Storage\Doc_model();
        $d_doc = $m_doc->with(['lampiran', 'logs'])->orderBy('id', 'DESC')->get()->toArray();

        $content['akses'] = $akses;
        $content['data'] = $d_doc;
        $html = $this->load->view('parameter/doc/list', $content);
        
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
                        $m_doc = new \Model\Storage\Doc_model();

                        $m_doc->nomor = $m_doc->getNextDocNum('ADM/HPKN');
                        $m_doc->mulai = $params['tgl_berlaku'];
                        $m_doc->g_status = $g_status;
                        $m_doc->dokumen = $path_name;
                        $m_doc->doc = $params['harga_kontrak'];
                        $m_doc->save();

                        $last_id = $m_doc->orderBy('id','DESC')->first();

                        $deskripsi_log_doc = 'di-'. $status .' oleh ' . $this->userdata['detail_user']['nama_detuser'];
                        Modules::run( 'base/event/save', $m_doc, $deskripsi_log_doc );

                        $id_doc = $last_id['id'];

                        $m_lampiran = new \Model\Storage\Lampiran_model();
                        $m_lampiran->tabel = 'doc';
                        $m_lampiran->tabel_id = $id_doc;
                        $m_lampiran->filename = $file_name ;
                        $m_lampiran->path = $path_name;
                        $m_lampiran->status = 1;
                        $m_lampiran->save();
                        Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log_doc );
                    }else {
                        display_json(['status'=>0, 'message'=>'error, segera hubungi tim IT']);
                    }
                }
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data doc sukses disimpan';
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
            $deskripsi_log_doc = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];

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
                                       ->where('tabel', 'doc')
                                       ->where('path', $lampiran['old'])
                                       ->update( array('status' => 0) );
                        }

                        $m_lampiran->tabel = 'doc';
                        $m_lampiran->tabel_id = $params['id'];
                        $m_lampiran->filename = $file_name;
                        $m_lampiran->path = $path_name;
                        $m_lampiran->status = 1;
                        $m_lampiran->save();
                        Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log_doc );
                    }else {
                        display_json(['status'=>0, 'message'=>'error, segera hubungi tim IT']);
                    }
                }
            }

            $m_doc = new \Model\Storage\Doc_model();
            $m_doc->where('id', $params['id'])->update(
                array(
                    'mulai' => $params['tgl_berlaku'],
                    'g_status' => $g_status,
                    'dokumen' => $params['dokumen'],
                    'doc' => $params['harga_kontrak']
                )
            );

            $d_doc = $m_doc->where('id', $params['id'])->first();

            Modules::run( 'base/event/save', $d_doc, $deskripsi_log_doc );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data doc sukses diubah';
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

        $m_doc = new \Model\Storage\Doc_model();
        $m_doc->where('id', $params['id'])
                ->update(
                    array('g_status'=>$g_status)
                    );

        $d_doc = $m_doc->where('id', $params['id'])->first();

        $deskripsi_log_doc = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
        Modules::run( 'base/event/update', $d_doc, $deskripsi_log_doc );

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

        $m_doc = new \Model\Storage\Doc_model();
        $m_doc->where('id', $params['id'])
                ->update(
                    array('g_status'=>$g_status)
                    );

        $d_doc = $m_doc->where('id', $params['id'])->first();

        $deskripsi_log_doc = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
        Modules::run( 'base/event/update', $d_doc, $deskripsi_log_doc );

        $this->result['status'] = 1;
        $this->result['action'] = strtoupper($params['action']);
        $this->result['message'] = 'Data berhasil di APPROVE';

        display_json($this->result);
    }

    public function model($status)
    {
        $m_doc = new \Model\Storage\Doc_model();
        $dashboard = $m_doc->getDashboard_Doc($status);

        return $dashboard;
    }
}