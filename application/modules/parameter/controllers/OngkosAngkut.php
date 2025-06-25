<?php defined('BASEPATH') or exit('No direct script access allowed');

class OngkosAngkut extends Public_Controller
{
    private $pathView = 'parameter/ongkos_angkut/';
    private $url;

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
            $this->add_external_js(array(
                'assets/jquery/choosen/js/chosen.jquery.min.js',
                'assets/select2/js/select2.min.js',
                'assets/jquery/list.min.js',
                'assets/parameter/ongkos_angkut/js/ongkos-angkut.js'));
            
            $this->add_external_css(array(
                'assets/jquery/choosen/css/component-chosen.css',
                'assets/select2/css/select2.min.css',
                'assets/jquery/loading/css/loading.css',
                'assets/parameter/ongkos_angkut/css/ongkos-angkut.css'));
            $data = $this->includes;

            $content['title_panel'] = 'Master Ongkos Angkut';
            $content['akses'] = $akses;
            $content['lokasi_kb_kt'] = $this->getLokasiKbKt();

            $content['content_input_oa'] = $this->load->view($this->pathView . 'add_form', $content, true);

            $data['title_menu'] = 'Master Ongkos Angkut';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, true);
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

    public function list_oa()
    {
        $akses = hakAkses($this->url);

        $m_oa = new \Model\Storage\OngkosAngkut_model();
        $d_oa = $m_oa->with(['logs'])->where('g_status', '<>', getStatus('delete') )->orderBy('id', 'DESC')->get();

        // $content['datas'] = $d_oa;
        $content['datas'] = $this->getList();
        $content['akses'] = $akses;
        $html = $this->load->view($this->pathView . 'list', $content, true);

        echo $html;
    }

    public function getList() {
        $m_oa = new \Model\Storage\OngkosAngkut_model();
        $d_nomor = $m_oa->select('nomor')->distinct('nomor')->get()->toArray();

        $datas = array();
        if ( !empty($d_nomor) ) {
            foreach ($d_nomor as $nomor) {
                $oa = $m_oa->where('nomor', $nomor['nomor'])
                                          ->orderBy('version', 'desc')
                                          ->orderBy('id', 'desc')
                                          ->with(['logs'])
                                          ->first()->toArray();

                array_push($datas, $oa);
            }
        }

        return $datas;
    }

    public function add_form()
    {
        $akses = hakAkses($this->url);

        $content['lokasi_kb_kt'] = $this->getLokasiKbKt();
        $content['akses'] = $akses;
        $content['data'] = null;
        $html = $this->load->view('parameter/ongkos_angkut/add_form', $content);
        
        return $html;
    }

    public function view_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        $data = array();

        $m_oa = new \Model\Storage\OngkosAngkut_model();
        $d_oa = $m_oa->with(['logs', 'lampiran'])->where('id',$id)->first();

        $m_oa_item = new \Model\Storage\OaItem_model();
        $d_oa_item = $m_oa_item->where('id_oa', $id)->get()->toArray();

        $data['id'] = $d_oa['id'];
        $data['nomor'] = $d_oa['nomor'];
        $data['jns_oa'] = $d_oa['jns_oa'];
        $data['tanggal'] = $d_oa['mulai'];
        $data['alasan_tolak'] = $d_oa['alasan_tolak'];
        $data['status'] = $d_oa['g_status'];

        foreach ($d_oa['logs'] as $key => $value) {
            $data['logs'][] = $value->toArray();
        }

        $data['lampiran'] = $d_oa['lampiran'];

        foreach ($d_oa_item as $key => $val) {
            $m_lok = new \Model\Storage\Lokasi_model();
            $wilayah = $m_lok->where('id', $val['wilayah'])->first();
            $kecamatan = $m_lok->where('id', $val['kecamatan'])->first();

            if ($val['kecamatan'] == '-1') {
                $kecamatan['nama'] = '-';
            } else if ($val['kecamatan'] == 0) {
                $kecamatan['nama'] = 'ALL';
            }

            $data['detail'][] = array(
                'wilayah' => $wilayah['nama'],
                'kecamatan' => $kecamatan['nama'],
                'tarif_lama' => empty($val['ongkos_lama']) ? 0 : $val['ongkos_lama'],
                'tarif_lama2' => empty($val['ongkos_lama2']) ? 0 : $val['ongkos_lama2'],
                'tarif_baru' => $val['ongkos'],
                'tarif_baru2' => empty($val['ongkos2']) ? 0 : $val['ongkos2'],
            );
        }

        $html = 'Data not found!';
        if ($d_oa) {
            $content['data'] = $data;
            $content['akses'] = $akses;
            $html = $this->load->view('parameter/ongkos_angkut/view_form', $content);
        }

        return $html;
    }

    public function edit_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        $data = array();

        $m_oa = new \Model\Storage\OngkosAngkut_model();
        $d_oa = $m_oa->with(['logs', 'lampiran'])->where('id',$id)->first();

        $m_oa_item = new \Model\Storage\OaItem_model();
        $d_oa_item = $m_oa_item->where('id_oa', $id)->get()->toArray();

        $data['id'] = $d_oa['id'];
        $data['nomor'] = $d_oa['nomor'];
        $data['jns_oa'] = $d_oa['jns_oa'];
        $data['tanggal'] = $d_oa['mulai'];
        $data['alasan_tolak'] = $d_oa['alasan_tolak'];
        $data['status'] = $d_oa['g_status'];
        $data['lampiran'] = $d_oa['lampiran'];
        $data['version'] = $d_oa['version'];

        foreach ($d_oa['logs'] as $key => $value) {
            $data['logs'][] = $value->toArray();
        }

        foreach ($d_oa_item as $key => $val) {
            $m_lok = new \Model\Storage\Lokasi_model();
            $wilayah = $m_lok->where('id', $val['wilayah'])->first();
            $kecamatan = $m_lok->where('id', $val['kecamatan'])->first();

            if ($val['kecamatan'] == '-1') {
                $kecamatan['id'] = '-1';
                $kecamatan['nama'] = '-';
            } else if ($val['kecamatan'] == 0) {
                $kecamatan['id'] = 0;
                $kecamatan['nama'] = 'ALL';
            }

            $data['detail'][] = array(
                'wilayah' => $wilayah['id'],
                'kecamatan' => $kecamatan['id'],
                'ongkos_lama' => $val['ongkos_lama'],
                'ongkos_lama2' => $val['ongkos_lama2'],
                'ongkos' => $val['ongkos'],
                'ongkos2' => $val['ongkos2']
            );
        }

        $html = 'Data not found!';
        if ($d_oa) {
            $content['data'] = $data;
            $content['akses'] = $akses;
            $content['lokasi_kb_kt'] = $this->getLokasiKbKt();
            $html = $this->load->view($this->pathView . 'edit_form', $content, true);
        }

        return $html;
    }

    public function getDataOld(){
        $data = array();

        $jns_oa = $this->input->post('jns_oa');
        $id = $this->input->post('id');

        $m_oa = new \Model\Storage\OngkosAngkut_model();
        // $d_oa = $m_oa->with(['oa_item', 'lampiran'])->where('g_status', $status_doc)->orderBy('id', 'desc')->first();
        $d_oa = $m_oa->with(['oa_item', 'lampiran'])->where('id', $id)->first();

        if ( empty($d_oa['nomor']) ) {
            $this->result['status'] = 0;
        } else {
            $this->result['status'] = 1;
            $this->result['content'] = $d_oa->toArray();
        }

        display_json($this->result);
    }

    public function getAkses()
    {
        $akses = hakAkses($this->url);

        $akses = array(
            'submit' => $akses['a_submit'],
            'approve' => $akses['a_ack']
        );
        return $akses;
    }

    public function getLokasiKbKt()
    {
        $m_lok = new \Model\Storage\Lokasi_model();
        $d_lok = $m_lok->where('jenis','like','%KB%')->orWhere('jenis','like','%KT%')->orderBy('nama','ASC')->get()->toArray();

        return $d_lok;
    }

    public function getLokasiKc()
    {
        $id = $this->input->post('id_induk');

        $m_lok = new \Model\Storage\Lokasi_model();
        $d_lok = $m_lok->where('induk', $id)->orderBy('nama','ASC')->get()->toArray();

        $this->result['status'] = 1;
        $this->result['content'] = $d_lok;

        display_json($this->result);
    }

    public function getLists()
    {
        $m_oa = new \Model\Storage\OngkosAngkut_model();
        $d_oa = $m_oa->with(['logs'])->where('g_status', '<>', getStatus('delete') )->orderBy('id', 'DESC')->get();

        $content['datas'] = $d_oa;
        $content['akses'] = $this->getAkses();
        $html = $this->load->view($this->pathView . 'list', $content, true);
        echo $html;
    }

    public function loadContentOA()
    {
      $id = $this->input->get('id');
      $resubmit = $this->input->get('resubmit');
      $content = array();
      $html = "url not found";

      if ( !empty($id) && is_numeric($id) ) {
        // NOTE: view/edit data OA (ajax)
        $html = ($resubmit == 1) ? $this->edit($id) : $this->view($id);
      }else{
        $content['title_panel'] = 'Master Ongkos Angkut';
        $content['akses'] = $this->getAkses();
        $content['lokasi_kb_kt'] = $this->getLokasiKbKt();

        $html = $this->load->view($this->pathView . 'add_form', $content, true);
      }

      echo $html;
    }

    public function save()
    {
        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];
        $mappingFiles = mappingFiles($files);

        $id_saved = null;

        $status = $params['action'];
        $g_status = getStatus($status);

        $model = new \Model\Storage\Conf();
        $now = $model->getDate();

        $file_name = null;
        $path_name = null;
        $isMoved = 0;

        $lampiran = $params['lampiran'];
        $file = $mappingFiles[ $lampiran['sha1'] . '_' . $lampiran['name'] ] ?: '';
        $file_name = $path_name = null;
        $isMoved = 0;
        if (!empty($file)) {
            $moved = uploadFile($file);
            $isMoved = $moved['status'];
        }
        if ($isMoved) {
            $file_name = $moved['name'];
            $path_name = $moved['path'];

            $m_oa = new \Model\Storage\OngkosAngkut_model();

            $last_id = $m_oa->orderBy('id','DESC')->first();

            $m_oa->nomor = $m_oa->getNextDocNum('ADM/MOA');
            $m_oa->jns_oa = $params['jns_oa'];
            $m_oa->mulai = $params['tanggal'];
            $m_oa->g_status = $g_status;
            $m_oa->dokumen = $path_name;
            $m_oa->version = 1;
            $m_oa->save();

            $m_oa->where('id', $last_id['id'])
                 ->update(
                        array('selesai' => $now['waktu'])
                    );

            foreach ($params['detail'] as $k_val => $val) {
                $m_oa_item = new \Model\Storage\OaItem_model();

                $ongkos_baru = $val['tarif_baru'];
                $ongkos_baru2 = $val['tarif_baru2'];
                $ongkos_lama = $val['tarif_lama'];
                $ongkos_lama2 = $val['tarif_lama2'];

                if ( $val['tarif_lama'] != 0 && $val['tarif_baru'] == 0 ) {
                    $ongkos_baru = $val['tarif_lama'];
                }

                if ( $val['tarif_lama2'] != 0 && $val['tarif_baru2'] == 0 ) {
                    $ongkos_baru2 = $val['tarif_lama2'];
                }

                $m_oa_item->id_oa = $m_oa->id;
                $m_oa_item->wilayah = $val['id_kab'];
                $m_oa_item->kecamatan = $val['id_kec'];
                $m_oa_item->ongkos = $ongkos_baru;
                $m_oa_item->ongkos2 = $ongkos_baru2;
                $m_oa_item->ongkos_lama = $ongkos_lama;
                $m_oa_item->ongkos_lama2 = $ongkos_lama2;
                $m_oa_item->save();
            }

            $id_saved = $m_oa->id;

            $deskripsi_log_oa = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_oa, $deskripsi_log_oa );

            $m_lampiran = new \Model\Storage\Lampiran_model();
            $m_lampiran->tabel = 'ongkos_angkut';
            $m_lampiran->tabel_id = $m_oa->id;
            $m_lampiran->filename = $file_name;
            $m_lampiran->path = $path_name;
            $m_lampiran->status = 1;
            $m_lampiran->save();

            $deskripsi_log_lampiran = 'di' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log_lampiran );
        }else {
            display_json(['status'=>0, 'message'=>'error, segera hubungi tim IT']);
        }

        $this->result['status'] = 1;
        $this->result['content'] = array('id'=>$id_saved);
        $this->result['message'] = 'Data OA sukses di simpan';

        display_json($this->result);
    }

    public function edit()
    {
        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];
        if ( !empty($files) ) {
            $mappingFiles = mappingFiles($files);
        }

        $id_saved = null;

        $status = $params['action'];
        $g_status = getStatus($status);

        $model = new \Model\Storage\Conf();
        $now = $model->getDate();

        $file_name = null;
        $path_name = null;
        $isMoved = 0;

        $lampiran = $params['lampiran'];

        if ( !empty($lampiran['sha1']) ) {
            $file = $mappingFiles[ $lampiran['sha1'] . '_' . $lampiran['name'] ] ?: '';
        }

        $file_name = $path_name = null;
        $isMoved = 0;
        if ( isset($lampiran['sha1']) ) {
            $moved = uploadFile($file);
            $file_name = $moved['name'];
            $path_name = $moved['path'];
            $isMoved = $moved['status'];

        } elseif ( !isset($lampiran['sha1']) && !empty($lampiran['old']) ) {
            $m_lampiran = new \Model\Storage\Lampiran_model();
            $d_lampiran = $m_lampiran->where('tabel_id', $params['id'])
                                     ->where('tabel', 'ongkos_angkut')
                                     ->orderBy('id', 'desc')
                                     ->first();

            $file_name = $d_lampiran['filename'];
            $path_name = $d_lampiran['path'];
            $isMoved = 1;
        }

        if ($isMoved) {
            $m_oa = new \Model\Storage\OngkosAngkut_model();

            $last_id = $m_oa->where('id', $params['id'])->first();

            $m_oa->nomor = $params['nomor'];
            $m_oa->jns_oa = $params['jns_oa'];
            $m_oa->mulai = $params['tanggal'];
            $m_oa->g_status = $g_status;
            $m_oa->dokumen = $path_name;
            $m_oa->version = $params['version'] + 1;
            $m_oa->save();

            $m_oa->where('id', $last_id['id'])
                 ->update(
                        array('selesai' => $now['waktu'])
                    );

            foreach ($params['detail'] as $k_val => $val) {
                $m_oa_item = new \Model\Storage\OaItem_model();

                $ongkos_baru = $val['tarif_baru'];
                $ongkos_baru2 = $val['tarif_baru2'];
                $ongkos_lama = $val['tarif_lama'];
                $ongkos_lama2 = $val['tarif_lama2'];

                if ( $val['tarif_lama'] != 0 && $val['tarif_baru'] == 0 ) {
                    $ongkos_baru = $val['tarif_lama'];
                }

                if ( $val['tarif_lama2'] != 0 && $val['tarif_baru2'] == 0 ) {
                    $ongkos_baru2 = $val['tarif_lama2'];
                }

                $m_oa_item->id_oa = $m_oa->id;
                $m_oa_item->wilayah = $val['id_kab'];
                $m_oa_item->kecamatan = $val['id_kec'];
                $m_oa_item->ongkos = $ongkos_baru;
                $m_oa_item->ongkos2 = $ongkos_baru2;
                $m_oa_item->ongkos_lama = $ongkos_lama;
                $m_oa_item->ongkos_lama2 = $ongkos_lama2;
                $m_oa_item->save();
            }

            $id_saved = $m_oa->id;

            $deskripsi_log_oa = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $m_oa, $deskripsi_log_oa );

            $m_lampiran = new \Model\Storage\Lampiran_model();
            $m_lampiran->tabel = 'ongkos_angkut';
            $m_lampiran->tabel_id = $m_oa->id;
            $m_lampiran->filename = $file_name;
            $m_lampiran->path = $path_name;
            $m_lampiran->status = 1;
            $m_lampiran->save();

            $deskripsi_log_lampiran = 'di' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log_lampiran );
        }else {
            display_json(['status'=>0, 'message'=>'error, segera hubungi tim IT']);
        }

        $this->result['status'] = 1;
        $this->result['content'] = array('id'=>$id_saved);
        $this->result['message'] = 'Data OA sukses di edit';

        display_json($this->result);
    }

    public function ack() {
        $id = $this->input->post('params');

        $status = getStatus(2);

        $m_oa = new \Model\Storage\OngkosAngkut_model();
        $m_oa->where('id', $id)->update(
            array(
                'g_status' => 2
            )
        );

        $d_oa = $m_oa->where('id', $id)->first();

        $deskripsi_log_oa = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
        Modules::run( 'base/event/save', $d_oa, $deskripsi_log_oa );

        $this->result['status'] = 1;
        $this->result['message'] = 'Data OA sukses di ACK';
        $this->result['content'] = array('id' => $id);

        display_json($this->result);
    }

    public function model($status)
    {
        // if ( is_numeric($status) ) {
        //     $status = getStatus($status);
        // }

        $m_oa = new \Model\Storage\OngkosAngkut_model();
        $dashboard = $m_oa->getDashboard($status);

        return $dashboard;
    }
}
