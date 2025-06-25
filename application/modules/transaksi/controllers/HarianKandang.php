<?php defined('BASEPATH') or exit('No direct script access allowed');

class HarianKandang extends Public_Controller
{
    private $pathView = 'transaksi/harian_kandang/';
    private $url;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
    }

    public function index()
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
              'assets/toastr/js/toastr.js',
              'assets/transaksi/harian_kandang/js/harian-kandang.js'
            ));
            $this->add_external_css(array(
              'assets/toastr/css/toastr.css',
              'assets/transaksi/harian_kandang/css/harian-kandang.css'
            ));
            $data = $this->includes;

            $content['akses'] = $akses;
            $content_rwt['datas'] = null;
            $content['title_panel'] = 'Harian Kandang';

            $content_act['periodes'] = $this->getPeriodeRdim();

            // Load Indexx
            $content['riwayat'] = $this->load->view($this->pathView . 'list_harian_kandang', $content_rwt, true);
            $content['action'] = $this->load->view($this->pathView . 'input_harian_kandang', $content_act, true);

            $data['title_menu'] = 'Harian Kandang';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getPeriodeRdim()
    {
        $status = getStatus('ack');
        $m_rdim = new \Model\Storage\Rdim_model();
        // $d_rdim = $m_rdim->where('g_status', $status)->orderBy('id','DESC')->take(2)->get();
        $d_rdim = $m_rdim->orderBy('id','DESC')->take(2)->get();
        return $d_rdim;
    }

    public function getNoregMitraByRdim($rdimId)
    {
        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('id_rdim', $rdimId)->whereStatus(1)->with(['dMitraMapping'])->get();

        $datas = array();
        foreach ($d_rs as $rs) {
            $datas[] = array(
                'id' => $rs->id,
                'noreg' => $rs->noreg,
                'mitra' => $rs->dMitraMapping->dMitra->nama,
                'populasi' => $rs->populasi,
            );
        }

        display_json($datas);
    }

    public function getUmur()
    {
        $umur = 0;

        $noreg = $this->input->post('noreg');
        $tgl_timbang = $this->input->post('tgl_timbang');

        $m_rd = new \Model\Storage\RealDocin_model();
        $d_rd = $m_rd->where('noreg', trim($noreg))->orderBy('id', 'desc')->first();

        if ( !empty($d_rd) ) {
            $umur = selisihTanggal( $d_rd['tgl_terima'], $tgl_timbang );
        } else {
            $m_rdims = new \Model\Storage\RdimSubmit_model();
            $d_rdims = $m_rdims->where('noreg', trim($noreg))->orderBy('id', 'desc')->first();

            $umur = selisihTanggal( $d_rdims['tgl_docin'], $tgl_timbang );
        }

        display_json( $umur );
    }

    public function getLists()
    {
        $m_harian_kandang = new \Model\Storage\HarianKandang_model();
        $d_harian_kandang = $m_harian_kandang->with(['logs', 'dRdimSubmit'])->where('status', 1)->orderBy('id', 'DESC')->get()->toArray();

        $content['datas'] = $d_harian_kandang;
        $html = $this->load->view($this->pathView . 'list_harian_kandang', $content, true);

        echo $html;
    }

    public function load_form()
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
            $content['title_panel'] = 'Harian Kandang';
            $content['periodes'] = $this->getPeriodeRdim();
            $html = $this->load->view($this->pathView . 'input_harian_kandang', $content, true);
        }

        echo $html;
    }

    public function view($id)
    {
        $data = array();

        $m_harian_kandang = new \Model\Storage\HarianKandang_model();
        $d_harian_kandang = $m_harian_kandang->where('id', $id)->with(['detail', 'dRdimSubmit'])->first()->toArray();

        $m_rdim = new \Model\Storage\Rdim_model();
        $d_rdim = $m_rdim->where('id', $d_harian_kandang['d_rdim_submit']['id_rdim'])->orderBy('id','DESC')->first()->toArray();

        $content['rdim'] = $d_rdim;
        $content['data'] = $d_harian_kandang;

        $html = $this->load->view($this->pathView . 'view_harian_kandang', $content, true);

        return $html;
    }

    public function update($id)
    {
        $data = array();

        $m_harian_kandang = new \Model\Storage\HarianKandang_model();
        $d_harian_kandang = $m_harian_kandang->where('id', $id)->with(['detail', 'dRdimSubmit'])->first()->toArray();

        $m_rdim = new \Model\Storage\Rdim_model();
        $d_rdim = $m_rdim->where('id', $d_harian_kandang['d_rdim_submit']['id_rdim'])->orderBy('id','DESC')->first()->toArray();

        $content['rdim'] = $d_rdim;
        $content['data'] = $d_harian_kandang;
        $content['periodes'] = $this->getPeriodeRdim();

        $html = $this->load->view($this->pathView . 'edit_harian_kandang', $content, true);

        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        // NOTE: data detail harian kandang bb
        $details = $params['details'];

        // NOTE: save header => tb harian_kandang
        $m_hk = new \Model\Storage\HarianKandang_model();
        $m_hk->id_user = $this->userid;
        $m_hk->id_rdim_submit = $params['id_rdim_submit'];
        $m_hk->umur = $params['umur'];
        $m_hk->mati = $params['mati'];
        $m_hk->bb = $params['bb'];
        $m_hk->terima_pakan = $params['terima_pakan'];
        $m_hk->sisa_pakan = $params['sisa_pakan'];
        $m_hk->ket = $params['ket'];
        $m_hk->tgl_timbang = $params['tanggal'];
        $m_hk->status = 1;
        $m_hk->save();
        $id_hk_saved = $m_hk->id;

        $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
        Modules::run( 'base/event/save', $m_hk, $deskripsi_log);

        $i = 1;
        foreach ($details as $detail) {
            $m_hkb = new \Model\Storage\HarianKandangBb_model();
            $m_hkb->id_hk = $id_hk_saved;
            $m_hkb->no_sekat = $i++;
            $m_hkb->jml_sekat = $detail['sekat'];
            $m_hkb->bb = $detail['bb'];
            $m_hkb->save();
            Modules::run( 'base/event/save', $m_hkb, $deskripsi_log);
        }

        $this->result['status'] = 1;
        $this->result['content'] = $this->view($id_hk_saved);
        $this->result['message'] = 'Data berhasil disimpan';
        display_json($this->result);
    }

    public function edit()
    {
        $params = $this->input->post('params');

        // NOTE: data detail harian kandang bb
        $details = $params['details'];

        // NOTE: save header => tb harian_kandang
        $m_hk = new \Model\Storage\HarianKandang_model();

        $m_hk->where('id', $params['id_old'])->update(
                array(
                    'status' => 0
                )
            );

        $m_hk->id_user = $this->userid;
        $m_hk->id_rdim_submit = $params['id_rdim_submit'];
        $m_hk->umur = $params['umur'];
        $m_hk->mati = $params['mati'];
        $m_hk->bb = $params['bb'];
        $m_hk->terima_pakan = $params['terima_pakan'];
        $m_hk->sisa_pakan = $params['sisa_pakan'];
        $m_hk->ket = $params['ket'];
        $m_hk->tgl_timbang = $params['tanggal'];
        $m_hk->status = 1;
        $m_hk->save();
        $id_hk_saved = $m_hk->id;

        $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
        Modules::run( 'base/event/save', $m_hk, $deskripsi_log);

        $i = 1;
        foreach ($details as $detail) {
            $m_hkb = new \Model\Storage\HarianKandangBb_model();
            $m_hkb->id_hk = $id_hk_saved;
            $m_hkb->no_sekat = $i++;
            $m_hkb->jml_sekat = $detail['sekat'];
            $m_hkb->bb = $detail['bb'];
            $m_hkb->save();
            Modules::run( 'base/event/save', $m_hkb, $deskripsi_log);
        }

        $this->result['status'] = 1;
        $this->result['content'] = $this->view($id_hk_saved);
        $this->result['message'] = 'Data berhasil di update';
        display_json($this->result);
    }
}
