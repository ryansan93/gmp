<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MasterKBD extends Public_Controller
{
	private $url;

	function __construct()
	{
		parent::__construct();
		$this->url = $this->current_base_uri;
	}

	public function index()
	{
		$akses = hakAkses($this->url);
		if ( $akses['a_view'] == 1 ) {
			$this->add_external_js(array(
				'assets/parameter/master_kbd/js/master_kbd.js'
			));
			$this->add_external_css(array(
				'assets/parameter/master_kbd/css/master_kbd.css'
			));

			$data = $this->includes;

            $m_pk = new \Model\Storage\PolaKerjasama_model();
            $d_pk_bdy = $m_pk->where('pola', 'like', '%budidaya%')->get()->toArray();
            $d_pk_km = $m_pk->where('pola', 'like', '%kemitraan%')->get()->toArray();

            $m_pw = new \Model\Storage\Wilayah_model();
            // $d_pw = $m_pw->where('jenis', 'like', '%PW%')->orderBy('nama', 'asc')->get()->toArray();
            $d_pw = $m_pw->where('jenis', 'like', '%PW%')->where('nama', 'not like', '%jawa%')->orderBy('nama', 'asc')->get()->toArray();

            $content['pola_budidaya'] = $d_pk_bdy;
            $content['pola_kemitraan'] = $d_pk_km;
            $content['perwakilan'] = $d_pw;
			$data['title_menu'] = 'Master Kontrak, Bonus Dan Denda';

            $content['nama_lampiran'] = $this->get_nama_lampiran();

			$content['akses'] = $akses;
			$content['list'] = $this->list_sapronak_kesepakatan();
			$data['view'] = $this->load->view('parameter/master_kbd/index', $content, true);

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

    public function get_nama_lampiran()
    {
        $m_nama_lampiran = new \Model\Storage\NamaLampiran_model();
        $d_nama_lampiran = $m_nama_lampiran->where('jenis', 'SAPRONAK_KESEPAKATAN')->get()->toArray();

        return $d_nama_lampiran;
    }

    public function list_sapronak_kesepakatan()
    {
        $m_sk = new \Model\Storage\SapronakKesepakatan_model();
        $d_nomor = $m_sk->select('nomor')->distinct('nomor')->get()->toArray();

        $data = null;
        foreach ($d_nomor as $nomor) {
            $d_sk = $m_sk->where('nomor', $nomor['nomor'])->where('g_status','<>',getStatus('delete'))->with(['harga_sapronak', 'harga_performa', 'harga_sepakat', 'lampiran', 'logs', 'pola_kerjasama'])->orderBy('version', 'DESC')->first();

            if ( !empty($d_sk) ) {
                $data[ $d_sk['id'] ] = $d_sk->toArray();
            }
        }

        if ( !empty($data) ) {
            krsort($data);
        }

        return $data;
    }

    public function list_sk()
    {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $content['list'] = $this->list_sapronak_kesepakatan();
        $html = $this->load->view('parameter/master_kbd/list', $content);
        
        echo $html;
    }

	public function view_form($id, $resubmit)
	{
		$akses = hakAkses($this->url);

		$m_sk = new \Model\Storage\SapronakKesepakatan_model();
        $d_sk = $m_sk->where('g_status','<>',getStatus('delete'))
        			 ->where('id', $id)
        			 ->with(['harga_sapronak', 'harga_performa', 'harga_sepakat', 'lampiran', 'logs', 'pola_kerjasama'])
        			 ->orderBy('id', 'DESC')->first();

        $show_header = null;
        $show_detail = null;
        if ( $akses['a_approve'] == 1 ) {
            $show_header = 'show';
            if ( $d_sk['g_status'] != getStatus('submit') ) {
                $show_detail = 'show2';
            } else {
                $show_detail = 'show';
            }
        } else if ( $akses['a_submit'] == 1 ) {
            if ( $d_sk['g_status'] != getStatus('submit') ) {
                $show_header = 'show';
                $show_detail = 'show2';
            } 
        }

        $l_voadip_sup = null;
        $l_doc_sup = null;
        $l_pakan_sup = null;
        // $l_oa_doc = null;
        // $l_oa_pakan = null;
        foreach ($d_sk['lampiran'] as $key => $v_lampiran) {
            if ( $v_lampiran['d_nama_lampiran']['sequence'] == 1 ) {
                $l_voadip_sup = $v_lampiran->toArray();
            }
            if ( $v_lampiran['d_nama_lampiran']['sequence'] == 2 ) {
                $l_doc_sup = $v_lampiran->toArray();
            }
            if ( $v_lampiran['d_nama_lampiran']['sequence'] == 3 ) {
                $l_pakan_sup = $v_lampiran->toArray();
            }
            // if ( $v_lampiran['d_nama_lampiran']['sequence'] == 4 ) {
            //     $l_oa_doc = $v_lampiran->toArray();
            // }
            // if ( $v_lampiran['d_nama_lampiran']['sequence'] == 5 ) {
            //     $l_oa_pakan = $v_lampiran->toArray();
            // }
        }

        $content['show_header'] = $show_header;
        $content['show_detail'] = $show_detail;

        $content['l_voadip_sup'] = $l_voadip_sup;
        $content['l_doc_sup'] = $l_doc_sup;
        $content['l_pakan_sup'] = $l_pakan_sup;
        // $content['l_oa_doc'] = $l_oa_doc;
        // $content['l_oa_pakan'] = $l_oa_pakan;
        $content['tbl_logs'] = $this->getLogs( formatURL($d_sk->nomor) );

		$content['akses'] = $akses;
		$content['data'] = $d_sk;
		$html = $this->load->view('parameter/master_kbd/view_form', $content);
		
		return $html;
	}

    public function getLogs($nomor = null) {
        $m_sk = new \Model\Storage\SapronakKesepakatan_model;
        $d_sk = $m_sk->where('nomor', unformatURL($nomor))->orderBy('id', 'desc')->get()->toArray();

        $logs = array();
        foreach ($d_sk as $key => $v_sk) {
            $m_log = new \Model\Storage\LogTables_model;
            $d_log = $m_log->where('tbl_name', 'sapronak_kesepakatan')->where('tbl_id', $v_sk['id'])->get()->toArray();

            if ( !empty($d_log) ) {
                foreach ($d_log as $key => $v_log) {
                    $logs[] = $v_log;
                }
            }
        }

        return $logs;
    }

	public function add_form()
	{
		$akses = hakAkses($this->url);

        $m_pk = new \Model\Storage\PolaKerjasama_model();
        $d_pk_bdy = $m_pk->where('pola', 'like', '%budidaya%')->get()->toArray();
        $d_pk_km = $m_pk->where('pola', 'like', '%kemitraan%')->get()->toArray();

        $m_pw = new \Model\Storage\Wilayah_model();
        // $d_pw = $m_pw->where('jenis', 'like', '%PW%')->orderBy('nama', 'asc')->get()->toArray();
        $d_pw = $m_pw->where('jenis', 'like', '%PW%')->where('nama', 'not like', '%jawa timur%')->orderBy('nama', 'asc')->get()->toArray();

        $content['pola_budidaya'] = $d_pk_bdy;
        $content['pola_kemitraan'] = $d_pk_km;
        $content['perwakilan'] = $d_pw;
        $content['nama_lampiran'] = $this->get_nama_lampiran();

		$content['akses'] = $akses;
		$content['data'] = null;
		$html = $this->load->view('parameter/master_kbd/add_form', $content);
		
		return $html;
	}

    public function edit_form($id, $resubmit)
    {
        $akses = hakAkses($this->url);

        $m_sk = new \Model\Storage\SapronakKesepakatan_model();
        $d_sk = $m_sk->where('g_status','<>',getStatus('delete'))
                     ->where('id', $id)
                     ->with(['harga_sapronak', 'harga_performa', 'harga_sepakat', 'lampiran', 'logs', 'pola_kerjasama'])
                     ->orderBy('id', 'DESC')->first();

        $m_pk = new \Model\Storage\PolaKerjasama_model();
        $d_pk_bdy = $m_pk->where('pola', 'like', '%budidaya%')->get()->toArray();
        $d_pk_km = $m_pk->where('pola', 'like', '%kemitraan%')->get()->toArray();

        $m_pw = new \Model\Storage\Wilayah_model();
        // $d_pw = $m_pw->where('jenis', 'like', '%PW%')->orderBy('nama', 'asc')->get()->toArray();
        $d_pw = $m_pw->where('jenis', 'like', '%PW%')->where('nama', 'not like', '%jawa timur%')->orderBy('nama', 'asc')->get()->toArray();

        $l_voadip_sup = null;
        $l_doc_sup = null;
        $l_pakan_sup = null;
        // $l_oa_doc = null;
        // $l_oa_pakan = null;
        foreach ($d_sk['lampiran'] as $key => $v_lampiran) {
            if ( $v_lampiran['d_nama_lampiran']['sequence'] == 1 ) {
                $l_voadip_sup = $v_lampiran->toArray();
            }
            if ( $v_lampiran['d_nama_lampiran']['sequence'] == 2 ) {
                $l_doc_sup = $v_lampiran->toArray();
            }
            if ( $v_lampiran['d_nama_lampiran']['sequence'] == 3 ) {
                $l_pakan_sup = $v_lampiran->toArray();
            }
            // if ( $v_lampiran['d_nama_lampiran']['sequence'] == 4 ) {
            //     $l_oa_doc = $v_lampiran->toArray();
            // }
            // if ( $v_lampiran['d_nama_lampiran']['sequence'] == 5 ) {
            //     $l_oa_pakan = $v_lampiran->toArray();
            // }
        }

        $content['pola_budidaya'] = $d_pk_bdy;
        $content['pola_kemitraan'] = $d_pk_km;
        $content['perwakilan'] = $d_pw;
        $content['nama_lampiran'] = $this->get_nama_lampiran();

        $content['l_voadip_sup'] = $l_voadip_sup;
        $content['l_doc_sup'] = $l_doc_sup;
        $content['l_pakan_sup'] = $l_pakan_sup;
        // $content['l_oa_doc'] = $l_oa_doc;
        // $content['l_oa_pakan'] = $l_oa_pakan;

        $content['akses'] = $akses;
        $content['data'] = $d_sk;
        $html = $this->load->view('parameter/master_kbd/edit_form', $content);
        
        return $html;
    }


	public function list_sb()
	{
		$akses = hakAkses($this->url);

		$content['akses'] = $akses;
		$content['list'] = $this->list_standar_budidaya();
		$html = $this->load->view('parameter/standar_budidaya/list', $content);
		
		echo $html;
	}

	public function save_data()
    {
        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];
        $mappingFiles = mappingFiles($files);

        $status = $params['action'];

        $g_status = getStatus($status);

        $model = new \Model\Storage\Conf();
        $now = $model->getDate();

        $id = null;
        $tgl_mulai = null;

        try {
        	$m_sk = new \Model\Storage\SapronakKesepakatan_model();
            $m_sk->nomor = $m_sk->getNextDocNum('ADM/HSK');
            $m_sk->pola = $params['pola'];
            $m_sk->item_pola = $params['item_pola'];
            $m_sk->mulai = $params['tgl_berlaku'];
            $m_sk->g_status = $g_status;
            $m_sk->note = $params['note'];
            $m_sk->version = 1;
            $m_sk->save();

            $id_sk = $m_sk->id;
            $tgl_mulai = $params['tgl_berlaku'];

            $m_hs = new \Model\Storage\HargaSapronak_model();
            $m_hs->id_sk = $m_sk->id;
            $m_hs->biaya_ops = $params['harga_sapronak']['biaya_opr'];
            $m_hs->jam_keuntungan = $params['harga_sapronak']['jaminan'];
            // $m_hs->oa_pakan = $params['harga_sapronak']['oa_pakan'];
            $m_hs->voadip = $params['harga_sapronak']['voadip'];
            $m_hs->doc = $params['harga_sapronak']['doc'];
            $m_hs->pakan1 = $params['harga_sapronak']['pakan1'];
            $m_hs->pakan2 = $params['harga_sapronak']['pakan2'];
            $m_hs->pakan3 = $params['harga_sapronak']['pakan3'];
            // $m_hs->oa_doc = $params['harga_sapronak']['oa_doc'];
            $m_hs->voadip_mitra = $params['harga_sapronak']['voadip_mitra'];
            $m_hs->doc_mitra = $params['harga_sapronak']['doc_mitra'];
            $m_hs->pakan1_mitra = $params['harga_sapronak']['pakan1_mitra'];
            $m_hs->pakan2_mitra = $params['harga_sapronak']['pakan2_mitra'];
            $m_hs->pakan3_mitra = $params['harga_sapronak']['pakan3_mitra'];
            // $m_hs->oa_doc_dok = $params['harga_sapronak']['oa_doc_dok'];
            // $m_hs->oa_pakan_dok = $params['harga_sapronak']['oa_pakan_dok'];
            $m_hs->voadip_dok = $params['harga_sapronak']['voadip_dok'];
            $m_hs->doc_dok = $params['harga_sapronak']['doc_dok'];
            $m_hs->pakan1_dok = $params['harga_sapronak']['pakan1_dok'];
            $m_hs->save();

            $m_hp = new \Model\Storage\HargaPerforma_model();
            $m_hp->id_sk = $m_sk->id;
            $m_hp->dh = $params['performa']['dh'];
            $m_hp->bb = $params['performa']['bb'];
            $m_hp->fcr = $params['performa']['fcr'];
            $m_hp->umur = $params['performa']['umur'];
            $m_hp->ip = $params['performa']['ip'];
            $m_hp->ie = $params['performa']['ie'];
            $m_hp->tot_pakan = $params['performa']['kebutuhan_pakan'];
            $m_hp->pakan1 = $params['performa']['pakan1'];
            $m_hp->pakan2 = $params['performa']['pakan2'];
            $m_hp->pakan3 = $params['performa']['pakan3'];
            $m_hp->save();

            foreach ($params['harga_kesepakatan'] as $k_sp => $v_sp) {
                $m_sp = new \Model\Storage\HargaSepakat_model();
                $m_sp->id_sk = $m_sk->id;
                $m_sp->range_min = (empty($v_sp['range_min'])) ? 0 : $v_sp['range_min'];
                $m_sp->range_max = $v_sp['range_max'];
                $m_sp->harga = $v_sp['harga'];
                $m_sp->hpp = $v_sp['hpp'];
                $m_sp->save();
            }

            foreach ($params['standar_pakan'] as $k_stp => $v_stp) {
                $m_stp = new \Model\Storage\StandarPakan_model();
                $m_stp->id_sk = $m_sk->id;
                $m_stp->bb_awal = $v_stp['bb_awal'];
                $m_stp->bb_akhir = $v_stp['bb_akhir'];
                $m_stp->standar_min = $v_stp['standar_min'];
                $m_stp->save();
            }

            foreach ($params['selisih_pakan'] as $k_ssp => $v_ssp) {
                $m_ssp = new \Model\Storage\SelisihPakan_model();
                $m_ssp->id_sk = $m_sk->id;
                $m_ssp->range_awal = $v_ssp['range_awal'];
                $m_ssp->range_akhir = $v_ssp['range_akhir'];
                $m_ssp->selisih = $v_ssp['selisih'];
                $m_ssp->tarif = $v_ssp['tarif'];
                $m_ssp->save();
            }

            $m_hbi = new \Model\Storage\HitungBudidayaItem_model();
            $m_hbi->id_sk =  $m_sk->id;
            $m_hbi->pola_kemitraan = $params['hitung_budidaya']['pola_kemitraan'];
            $m_hbi->bonus_fcr = $params['hitung_budidaya']['bonus_fcr'];
            $m_hbi->bonus_ch = $params['hitung_budidaya']['bonus_ch'];
            $m_hbi->bonus_ip = $params['hitung_budidaya']['bonus_ip'];
            $m_hbi->bonus_dh = $params['hitung_budidaya']['bonus_dh'];
            $m_hbi->bonus_bb = $params['hitung_budidaya']['bonus_bb'];
            $m_hbi->save();

            foreach ($params['perwakilan'] as $key => $v_pwk) {
                if ( isset($v_pwk['id']) ) {
                    $m_pwk = new \Model\Storage\PerwakilanMaping_model();
                    $m_pwk->id_hbi = $m_hbi->id;
                    $m_pwk->id_pwk = $v_pwk['id'];
                    $m_pwk->nama_pwk = $v_pwk['nama'];
                    $m_pwk->save();
                }
            }

            $deskripsi_log_sk = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_sk, $deskripsi_log_sk );

            // NOTE: simpan lampiran mitra
			$lampirans = $params['lampirans'];
			foreach ($lampirans as $lampiran) {
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

					$m_lampiran = new \Model\Storage\Lampiran_model();
					$m_lampiran->tabel = 'sapronak_kesepakatan';
                    $m_lampiran->tabel_id = $m_sk->id;
					$m_lampiran->nama_lampiran = empty($lampiran['id']) ? null : $lampiran['id'];
					$m_lampiran->filename = $file_name ;
					$m_lampiran->path = $path_name;
					$m_lampiran->status = 1;
					$m_lampiran->save();

					$deskripsi_log_lampiran = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
					Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log_lampiran );
				}else {
					$this->result['status'] = 0;
        			$this->result['message'] = 'error, segera hubungi tim IT';
				}
			}

			$this->result['status'] = 1;
        	$this->result['message'] = 'Data berhasil disimpan';
        	$this->result['content'] = array('id'=>$id_sk, 'tgl_mulai'=>$tgl_mulai);
        } catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

        display_json($this->result);
    }

	public function edit_data()
	{
		$params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];
        if ( !empty($files) ) {
        	$mappingFiles = mappingFiles($files);
        }

        $status = $params['action'];

        $g_status = getStatus($status);

        $model = new \Model\Storage\Conf();
        $now = $model->getDate();

        $id = null;
        $tgl_mulai = null;

        try {
        	$m_sk = new \Model\Storage\SapronakKesepakatan_model();
            $d_sk = $m_sk->where('id', $params['id'])->first();

            $m_sk->nomor = $d_sk['nomor'];
            $m_sk->pola = $params['pola'];
            $m_sk->item_pola = $params['item_pola'];
            $m_sk->mulai = $params['tgl_berlaku'];
            $m_sk->g_status = $d_sk['g_status'];
            $m_sk->note = $params['note'];
            $m_sk->version = $d_sk['version'] + 1;
            $m_sk->save();

            $id_sk = $m_sk->id;
            $tgl_mulai = $params['tgl_berlaku'];

            $m_hs = new \Model\Storage\HargaSapronak_model();
            $m_hs->id_sk = $m_sk->id;
            $m_hs->biaya_ops = $params['harga_sapronak']['biaya_opr'];
            $m_hs->jam_keuntungan = $params['harga_sapronak']['jaminan'];
            // $m_hs->oa_pakan = $params['harga_sapronak']['oa_pakan'];
            $m_hs->voadip = $params['harga_sapronak']['voadip'];
            $m_hs->doc = $params['harga_sapronak']['doc'];
            $m_hs->pakan1 = $params['harga_sapronak']['pakan1'];
            $m_hs->pakan2 = $params['harga_sapronak']['pakan2'];
            $m_hs->pakan3 = $params['harga_sapronak']['pakan3'];
            // $m_hs->oa_doc = $params['harga_sapronak']['oa_doc'];
            $m_hs->voadip_mitra = $params['harga_sapronak']['voadip_mitra'];
            $m_hs->doc_mitra = $params['harga_sapronak']['doc_mitra'];
            $m_hs->pakan1_mitra = $params['harga_sapronak']['pakan1_mitra'];
            $m_hs->pakan2_mitra = $params['harga_sapronak']['pakan2_mitra'];
            $m_hs->pakan3_mitra = $params['harga_sapronak']['pakan3_mitra'];
            // $m_hs->oa_doc_dok = $params['harga_sapronak']['oa_doc_dok'];
            // $m_hs->oa_pakan_dok = $params['harga_sapronak']['oa_pakan_dok'];
            $m_hs->voadip_dok = $params['harga_sapronak']['voadip_dok'];
            $m_hs->doc_dok = $params['harga_sapronak']['doc_dok'];
            $m_hs->pakan1_dok = $params['harga_sapronak']['pakan1_dok'];
            $m_hs->save();

            $m_hp = new \Model\Storage\HargaPerforma_model();
            $m_hp->id_sk = $m_sk->id;
            $m_hp->dh = $params['performa']['dh'];
            $m_hp->bb = $params['performa']['bb'];
            $m_hp->fcr = $params['performa']['fcr'];
            $m_hp->umur = $params['performa']['umur'];
            $m_hp->ip = $params['performa']['ip'];
            $m_hp->ie = $params['performa']['ie'];
            $m_hp->tot_pakan = $params['performa']['kebutuhan_pakan'];
            $m_hp->pakan1 = $params['performa']['pakan1'];
            $m_hp->pakan2 = $params['performa']['pakan2'];
            $m_hp->pakan3 = $params['performa']['pakan3'];
            $m_hp->save();

            foreach ($params['harga_kesepakatan'] as $k_sp => $v_sp) {
                $m_sp = new \Model\Storage\HargaSepakat_model();
                $m_sp->id_sk = $m_sk->id;
                $m_sp->range_min = (empty($v_sp['range_min'])) ? 0 : $v_sp['range_min'];
                $m_sp->range_max = $v_sp['range_max'];
                $m_sp->harga = $v_sp['harga'];
                $m_sp->hpp = $v_sp['hpp'];
                $m_sp->save();
            }

            foreach ($params['standar_pakan'] as $k_stp => $v_stp) {
                $m_stp = new \Model\Storage\StandarPakan_model();
                $m_stp->id_sk = $m_sk->id;
                $m_stp->bb_awal = $v_stp['bb_awal'];
                $m_stp->bb_akhir = $v_stp['bb_akhir'];
                $m_stp->standar_min = $v_stp['standar_min'];
                $m_stp->save();
            }

            foreach ($params['selisih_pakan'] as $k_ssp => $v_ssp) {
                $m_ssp = new \Model\Storage\SelisihPakan_model();
                $m_ssp->id_sk = $m_sk->id;
                $m_ssp->range_awal = $v_ssp['range_awal'];
                $m_ssp->range_akhir = $v_ssp['range_akhir'];
                $m_ssp->selisih = $v_ssp['selisih'];
                $m_ssp->tarif = $v_ssp['tarif'];
                $m_ssp->save();
            }

            $m_hbi = new \Model\Storage\HitungBudidayaItem_model();
            $m_hbi->id_sk =  $m_sk->id;
            $m_hbi->pola_kemitraan = $params['hitung_budidaya']['pola_kemitraan'];
            $m_hbi->bonus_fcr = $params['hitung_budidaya']['bonus_fcr'];
            $m_hbi->bonus_ch = $params['hitung_budidaya']['bonus_ch'];
            $m_hbi->bonus_ip = $params['hitung_budidaya']['bonus_ip'];
            $m_hbi->bonus_dh = $params['hitung_budidaya']['bonus_dh'];
            $m_hbi->bonus_bb = $params['hitung_budidaya']['bonus_bb'];
            $m_hbi->save();

            foreach ($params['perwakilan'] as $key => $v_pwk) {
                if ( isset($v_pwk['id']) ) {
                    $m_pwk = new \Model\Storage\PerwakilanMaping_model();
                    $m_pwk->id_hbi = $m_hbi->id;
                    $m_pwk->id_pwk = $v_pwk['id'];
                    $m_pwk->nama_pwk = $v_pwk['nama'];
                    $m_pwk->save();
                }
            }

            $deskripsi_log_sk = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $m_sk, $deskripsi_log_sk );


            // NOTE: simpan lampiran mitra
			$lampirans = $params['lampirans'];
            if ( !empty($lampirans) ) {
    			foreach ($lampirans as $lampiran) {
                    if ( !empty($lampiran['sha1']) ) {
    				    $file = $mappingFiles[ $lampiran['sha1'] . '_' . $lampiran['name'] ] ?: '';
                    }

    				$file_name = $path_name = null;
    				$isMoved = 0;
    				if ( !empty($lampiran['sha1']) ) {
    					$moved = uploadFile($file);
    					$file_name = $moved['name'];
    					$path_name = $moved['path'];
                        $isMoved = $moved['status'];
                    } elseif ( empty($lampiran['sha1']) && !empty($lampiran['old']) ) {
                        $m_lampiran = new \Model\Storage\Lampiran_model();
                        $d_lampiran = $m_lampiran->where('tabel_id', $params['id'])
                                                 ->where('tabel', 'sapronak_kesepakatan')
                                                 ->where('nama_lampiran', $lampiran['id'])
                                                 ->orderBy('id', 'desc')
                                                 ->first();

                        $file_name = $d_lampiran['filename'];
                        $path_name = $d_lampiran['path'];
                        $isMoved = 1;
                    }

                    if ($isMoved) {
    					$m_lampiran = new \Model\Storage\Lampiran_model();
    					$m_lampiran->tabel = 'sapronak_kesepakatan';
                        $m_lampiran->tabel_id = $m_sk->id;
    					$m_lampiran->nama_lampiran = empty($lampiran['id']) ? null : $lampiran['id'];
    					$m_lampiran->filename = $file_name ;
    					$m_lampiran->path = $path_name;
    					$m_lampiran->status = 1;
    					$m_lampiran->save();

    					$deskripsi_log_lampiran = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
    					Modules::run( 'base/event/update', $m_lampiran, $deskripsi_log_lampiran );
    				}else {
    					$this->result['status'] = 0;
            			$this->result['message'] = 'error, segera hubungi tim IT';
    				}
    			}
            }

			$this->result['status'] = 1;
        	$this->result['message'] = 'Data berhasil diubah';
        	$this->result['content'] = array('id'=>$id_sk, 'tgl_mulai'=>$tgl_mulai);
        } catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

        display_json($this->result);
	}

    public function save_copy()
    {
        $params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];
        if ( !empty($files) ) {
            $mappingFiles = mappingFiles($files);
        }

        $status = $params['action'];

        $g_status = getStatus($status);

        $model = new \Model\Storage\Conf();
        $now = $model->getDate();

        $id = null;
        $tgl_mulai = null;

        try {
            $m_sk = new \Model\Storage\SapronakKesepakatan_model();
            $m_sk->nomor = $m_sk->getNextDocNum('ADM/HSK');
            $m_sk->pola = $params['pola'];
            $m_sk->item_pola = $params['item_pola'];
            $m_sk->mulai = $params['tgl_berlaku'];
            $m_sk->g_status = $g_status;
            $m_sk->note = $params['note'];
            $m_sk->version = 1;
            $m_sk->save();

            $id_sk = $m_sk->id;
            $tgl_mulai = $params['tgl_berlaku'];

            $m_hs = new \Model\Storage\HargaSapronak_model();
            $m_hs->id_sk = $m_sk->id;
            $m_hs->biaya_ops = $params['harga_sapronak']['biaya_opr'];
            $m_hs->jam_keuntungan = $params['harga_sapronak']['jaminan'];
            // $m_hs->oa_pakan = $params['harga_sapronak']['oa_pakan'];
            $m_hs->voadip = $params['harga_sapronak']['voadip'];
            $m_hs->doc = $params['harga_sapronak']['doc'];
            $m_hs->pakan1 = $params['harga_sapronak']['pakan1'];
            $m_hs->pakan2 = $params['harga_sapronak']['pakan2'];
            $m_hs->pakan3 = $params['harga_sapronak']['pakan3'];
            // $m_hs->oa_doc = $params['harga_sapronak']['oa_doc'];
            $m_hs->voadip_mitra = $params['harga_sapronak']['voadip_mitra'];
            $m_hs->doc_mitra = $params['harga_sapronak']['doc_mitra'];
            $m_hs->pakan1_mitra = $params['harga_sapronak']['pakan1_mitra'];
            $m_hs->pakan2_mitra = $params['harga_sapronak']['pakan2_mitra'];
            $m_hs->pakan3_mitra = $params['harga_sapronak']['pakan3_mitra'];
            // $m_hs->oa_doc_dok = $params['harga_sapronak']['oa_doc_dok'];
            // $m_hs->oa_pakan_dok = $params['harga_sapronak']['oa_pakan_dok'];
            $m_hs->voadip_dok = $params['harga_sapronak']['voadip_dok'];
            $m_hs->doc_dok = $params['harga_sapronak']['doc_dok'];
            $m_hs->pakan1_dok = $params['harga_sapronak']['pakan1_dok'];
            $m_hs->save();

            $m_hp = new \Model\Storage\HargaPerforma_model();
            $m_hp->id_sk = $m_sk->id;
            $m_hp->dh = $params['performa']['dh'];
            $m_hp->bb = $params['performa']['bb'];
            $m_hp->fcr = $params['performa']['fcr'];
            $m_hp->umur = $params['performa']['umur'];
            $m_hp->ip = $params['performa']['ip'];
            $m_hp->ie = $params['performa']['ie'];
            $m_hp->tot_pakan = $params['performa']['kebutuhan_pakan'];
            $m_hp->pakan1 = $params['performa']['pakan1'];
            $m_hp->pakan2 = $params['performa']['pakan2'];
            $m_hp->pakan3 = $params['performa']['pakan3'];
            $m_hp->save();

            foreach ($params['harga_kesepakatan'] as $k_sp => $v_sp) {
                $m_sp = new \Model\Storage\HargaSepakat_model();
                $m_sp->id_sk = $m_sk->id;
                $m_sp->range_min = (empty($v_sp['range_min'])) ? 0 : $v_sp['range_min'];
                $m_sp->range_max = $v_sp['range_max'];
                $m_sp->harga = $v_sp['harga'];
                $m_sp->hpp = $v_sp['hpp'];
                $m_sp->save();
            }

            foreach ($params['standar_pakan'] as $k_stp => $v_stp) {
                $m_stp = new \Model\Storage\StandarPakan_model();
                $m_stp->id_sk = $m_sk->id;
                $m_stp->bb_awal = $v_stp['bb_awal'];
                $m_stp->bb_akhir = $v_stp['bb_akhir'];
                $m_stp->standar_min = $v_stp['standar_min'];
                $m_stp->save();
            }

            foreach ($params['selisih_pakan'] as $k_ssp => $v_ssp) {
                $m_ssp = new \Model\Storage\SelisihPakan_model();
                $m_ssp->id_sk = $m_sk->id;
                $m_ssp->range_awal = $v_ssp['range_awal'];
                $m_ssp->range_akhir = $v_ssp['range_akhir'];
                $m_ssp->selisih = $v_ssp['selisih'];
                $m_ssp->tarif = $v_ssp['tarif'];
                $m_ssp->save();
            }

            $m_hbi = new \Model\Storage\HitungBudidayaItem_model();
            $m_hbi->id_sk =  $m_sk->id;
            $m_hbi->pola_kemitraan = $params['hitung_budidaya']['pola_kemitraan'];
            $m_hbi->bonus_fcr = $params['hitung_budidaya']['bonus_fcr'];
            $m_hbi->bonus_ch = $params['hitung_budidaya']['bonus_ch'];
            $m_hbi->bonus_ip = $params['hitung_budidaya']['bonus_ip'];
            $m_hbi->bonus_dh = $params['hitung_budidaya']['bonus_dh'];
            $m_hbi->bonus_bb = $params['hitung_budidaya']['bonus_bb'];
            $m_hbi->save();

            foreach ($params['perwakilan'] as $key => $v_pwk) {
                if ( isset($v_pwk['id']) ) {
                    $m_pwk = new \Model\Storage\PerwakilanMaping_model();
                    $m_pwk->id_hbi = $m_hbi->id;
                    $m_pwk->id_pwk = $v_pwk['id'];
                    $m_pwk->nama_pwk = $v_pwk['nama'];
                    $m_pwk->save();
                }
            }

            $deskripsi_log_sk = 'di-' . $status . ' oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_sk, $deskripsi_log_sk );

            // NOTE: simpan lampiran mitra
            $lampirans = $params['lampirans'];
            if ( !empty($lampirans) ) {
                foreach ($lampirans as $lampiran) {
                    if ( !empty($lampiran['sha1']) ) {
                        $file = $mappingFiles[ $lampiran['sha1'] . '_' . $lampiran['name'] ] ?: '';
                    }

                    $file_name = $path_name = null;
                    $isMoved = 0;
                    if ( !empty($lampiran['sha1']) ) {
                        $moved = uploadFile($file);
                        $file_name = $moved['name'];
                        $path_name = $moved['path'];
                        $isMoved = $moved['status'];
                    } elseif ( empty($lampiran['sha1']) && !empty($lampiran['old']) ) {
                        $m_lampiran = new \Model\Storage\Lampiran_model();
                        $d_lampiran = $m_lampiran->where('tabel_id', $params['id'])
                                                 ->where('tabel', 'sapronak_kesepakatan')
                                                 ->where('nama_lampiran', $lampiran['id'])
                                                 ->orderBy('id', 'desc')
                                                 ->first();

                        $file_name = $d_lampiran['filename'];
                        $path_name = $d_lampiran['path'];
                        $isMoved = 1;
                    }

                    if ($isMoved) {
                        $m_lampiran = new \Model\Storage\Lampiran_model();
                        $m_lampiran->tabel = 'sapronak_kesepakatan';
                        $m_lampiran->tabel_id = $m_sk->id;
                        $m_lampiran->nama_lampiran = empty($lampiran['id']) ? null : $lampiran['id'];
                        $m_lampiran->filename = $file_name ;
                        $m_lampiran->path = $path_name;
                        $m_lampiran->status = 1;
                        $m_lampiran->save();

                        $deskripsi_log_lampiran = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
                        Modules::run( 'base/event/update', $m_lampiran, $deskripsi_log_lampiran );
                    }else {
                        $this->result['status'] = 0;
                        $this->result['message'] = 'error, segera hubungi tim IT';
                    }
                }
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil disimpan';
            $this->result['content'] = array('id'=>$id_sk, 'tgl_mulai'=>$tgl_mulai);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    // NOTE : EDIT OLD WITHOUT HISTORY
    // public function edit_data()
    // {
    //     $params = json_decode($this->input->post('data'),TRUE);
    //     $files = isset($_FILES['files']) ? $_FILES['files'] : [];
    //     if ( !empty($files) ) {
    //         $mappingFiles = mappingFiles($files);
    //     }

    //     $status = $params['action'];

    //     $g_status = getStatus($status);

    //     $model = new \Model\Storage\Conf();
    //     $now = $model->getDate();

    //     $id = null;
    //     $tgl_mulai = null;

    //     try {
    //         $id_sk = $params['id'];

    //         $m_sk = new \Model\Storage\SapronakKesepakatan_model();
    //         $m_sk->where('id', $id_sk)->update(
    //             array(
    //                 'pola' => $params['pola'],
    //                 'item_pola' => $params['item_pola'],
    //                 'mulai' => $params['tgl_berlaku'],
    //                 'g_status' => $g_status,
    //                 'note' => $params['note']
    //             )
    //         );

    //         $tgl_mulai = $params['tgl_berlaku'];

    //         $m_hs = new \Model\Storage\HargaSapronak_model();
    //         $m_hs->where('id_sk', $id_sk)->update(
    //             array(
    //                 'biaya_ops' => $params['harga_sapronak']['biaya_opr'],
    //                 'jam_keuntungan' => $params['harga_sapronak']['jaminan'],
    //                 'oa_pakan' => $params['harga_sapronak']['oa_pakan'],
    //                 'voadip' => $params['harga_sapronak']['voadip'],
    //                 'doc' => $params['harga_sapronak']['doc'],
    //                 'pakan1' => $params['harga_sapronak']['pakan1'],
    //                 'pakan2' => $params['harga_sapronak']['pakan2'],
    //                 'pakan3' => $params['harga_sapronak']['pakan3'],
    //                 'oa_doc' => $params['harga_sapronak']['oa_doc'],
    //                 'voadip_mitra' => $params['harga_sapronak']['voadip_mitra'],
    //                 'doc_mitra' => $params['harga_sapronak']['doc_mitra'],
    //                 'pakan1_mitra' => $params['harga_sapronak']['pakan1_mitra'],
    //                 'pakan2_mitra' => $params['harga_sapronak']['pakan2_mitra'],
    //                 'pakan3_mitra' => $params['harga_sapronak']['pakan3_mitra'],
    //                 'oa_doc_dok' => $params['harga_sapronak']['oa_doc_dok'],
    //                 'oa_pakan_dok' => $params['harga_sapronak']['oa_pakan_dok'],
    //                 'voadip_dok' => $params['harga_sapronak']['voadip_dok'],
    //                 'doc_dok' => $params['harga_sapronak']['doc_dok'],
    //                 'pakan1_dok' => $params['harga_sapronak']['pakan1_dok']
    //             )
    //         );

    //         $m_hp = new \Model\Storage\HargaPerforma_model();
    //         $m_hp->where('id_sk', $id_sk)->update(
    //             array(
    //                 'dh' => $params['performa']['dh'],
    //                 'bb' => $params['performa']['bb'],
    //                 'fcr' => $params['performa']['fcr'],
    //                 'umur' => $params['performa']['umur'],
    //                 'ip' => $params['performa']['ip'],
    //                 'ie' => $params['performa']['ie'],
    //                 'tot_pakan' => $params['performa']['kebutuhan_pakan'],
    //                 'pakan1' => $params['performa']['pakan1'],
    //                 'pakan2' => $params['performa']['pakan2'],
    //                 'pakan3' => $params['performa']['pakan3']
    //             )
    //         );

    //         $m_sp = new \Model\Storage\HargaSepakat_model();
    //         $m_sp->where('id_sk', $id_sk)->delete();

    //         foreach ($params['harga_kesepakatan'] as $k_sp => $v_sp) {
    //             $m_sp = new \Model\Storage\HargaSepakat_model();
    //             $m_sp->id_sk = $id_sk;
    //             $m_sp->range_min = (empty($v_sp['range_min'])) ? 0 : $v_sp['range_min'];
    //             $m_sp->range_max = $v_sp['range_max'];
    //             $m_sp->harga = $v_sp['harga'];
    //             $m_sp->hpp = $v_sp['hpp'];
    //             $m_sp->save();
    //         }

    //         $m_stp = new \Model\Storage\StandarPakan_model();
    //         $m_stp->where('id_sk', $id_sk)->delete();

    //         foreach ($params['standar_pakan'] as $k_stp => $v_stp) {
    //             $m_stp = new \Model\Storage\StandarPakan_model();
    //             $m_stp->id_sk = $id_sk;
    //             $m_stp->bb_awal = $v_stp['bb_awal'];
    //             $m_stp->bb_akhir = $v_stp['bb_akhir'];
    //             $m_stp->standar_min = $v_stp['standar_min'];
    //             $m_stp->save();
    //         }

    //         $m_ssp = new \Model\Storage\SelisihPakan_model();
    //         $m_ssp->where('id_sk', $id_sk)->delete();

    //         foreach ($params['selisih_pakan'] as $k_ssp => $v_ssp) {
    //             $m_ssp = new \Model\Storage\SelisihPakan_model();
    //             $m_ssp->id_sk = $id_sk;
    //             $m_ssp->range_awal = $v_ssp['range_awal'];
    //             $m_ssp->range_akhir = $v_ssp['range_akhir'];
    //             $m_ssp->selisih = $v_ssp['selisih'];
    //             $m_ssp->tarif = $v_ssp['tarif'];
    //             $m_ssp->save();
    //         }

    //         $m_hbi = new \Model\Storage\HitungBudidayaItem_model();
    //         $m_hbi->where('id_sk', $id_sk)->update(
    //             array(
    //                 'pola_kemitraan' => $params['hitung_budidaya']['pola_kemitraan'],
    //                 'bonus_fcr' => $params['hitung_budidaya']['bonus_fcr'],
    //                 'bonus_ch' => $params['hitung_budidaya']['bonus_ch'],
    //                 'bonus_ip' => $params['hitung_budidaya']['bonus_ip'],
    //                 'bonus_dh' => $params['hitung_budidaya']['bonus_dh'],
    //                 'bonus_bb' => $params['hitung_budidaya']['bonus_bb']
    //             )
    //         );

    //         $d_hbi = $m_hbi->where('id_sk', $id_sk)->first();

    //         $m_pwk = new \Model\Storage\PerwakilanMaping_model();
    //         $m_pwk->where('id_hbi', $d_hbi['id'])->delete();

    //         foreach ($params['perwakilan'] as $key => $v_pwk) {
    //             $m_pwk = new \Model\Storage\PerwakilanMaping_model();
    //             if ( isset($v_pwk['id']) ) {
    //                 $m_pwk->id_hbi = $d_hbi['id'];
    //                 $m_pwk->id_pwk = $v_pwk['id'];
    //                 $m_pwk->nama_pwk = $v_pwk['nama'];
    //                 $m_pwk->save();
    //             }
    //         }

    //         $d_sk = $m_sk->where('id', $id_sk)->first();

    //         $deskripsi_log_sk = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
    //         Modules::run( 'base/event/update', $d_sk, $deskripsi_log_sk );

    //         // NOTE: simpan lampiran mitra
    //         $lampirans = $params['lampirans'];
    //         foreach ($lampirans as $lampiran) {
    //             $file = $mappingFiles[ $lampiran['sha1'] . '_' . $lampiran['name'] ] ?: '';
    //             $file_name = $path_name = null;
    //             $isMoved = 0;
    //             if (!empty($file)) {
    //                 $moved = uploadFile($file);
    //                 $isMoved = $moved['status'];
    //             }
    //             if ($isMoved) {
    //                 $file_name = $moved['name'];
    //                 $path_name = $moved['path'];

    //                 $m_lampiran = new \Model\Storage\Lampiran_model();
    //                 $m_lampiran->tabel = 'sapronak_kesepakatan';
    //                 $m_lampiran->tabel_id = $m_sk->id;
    //                 $m_lampiran->filename = $file_name ;
    //                 $m_lampiran->path = $path_name;
    //                 $m_lampiran->status = 1;
    //                 $m_lampiran->save();

    //                 $deskripsi_log_lampiran = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
    //                 Modules::run( 'base/event/update', $m_lampiran, $deskripsi_log_lampiran );
    //             }else {
    //                 $this->result['status'] = 0;
    //                 $this->result['message'] = 'error, segera hubungi tim IT';
    //             }
    //         }

    //         $this->result['status'] = 1;
    //         $this->result['message'] = 'Data berhasil diubah';
    //         $this->result['content'] = array('id'=>$id_sk, 'tgl_mulai'=>$tgl_mulai);
    //     } catch (\Illuminate\Database\QueryException $e) {
    //         $this->result['message'] = "Gagal : " . $e->getMessage();
    //     }

    //     display_json($this->result);
    // }

	public function ack_data()
	{
		$id_sk = $this->input->post('params');

		try {
			$m_sk = new \Model\Storage\SapronakKesepakatan_model();
			$status_doc = getStatus('ack');

			// NOTE: ack header sapronak_kesepakatan
			$m_sk->where('id', $id_sk)->update(
				array(
					'g_status' => $status_doc
				)
			);

			$d_sk = $m_sk->where('id', $id_sk)->first();

			$deskripsi_log = 'di-ack oleh ' . $this->userdata['detail_user']['nama_detuser'];
			Modules::run( 'base/event/update', $d_sk, $deskripsi_log);

		    $this->result['status'] = 1;
			$this->result['message'] = 'Data berhasil di ACK';
			$this->result['content'] = array('id'=>$id_sk, 'tgl_mulai'=>$d_sk['mulai']);
		} catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

		display_json($this->result);
	}

	public function approve_data()
	{
		$id_sk = $this->input->post('params');

		try {
			$m_sk = new \Model\Storage\SapronakKesepakatan_model();
			$status_doc = getStatus('approve');

			// NOTE: ack header sapronak_kesepakatan
            $m_sk->where('id', $id_sk)->update(
                array(
                    'g_status' => $status_doc
                )
            );

            $d_sk = $m_sk->where('id', $id_sk)->first();

			$deskripsi_log = 'di-approve oleh ' . $this->userdata['detail_user']['nama_detuser'];
			Modules::run( 'base/event/update', $d_sk, $deskripsi_log);

		    $this->result['status'] = 1;
			$this->result['message'] = 'Data berhasil di APPROVE';
			$this->result['content'] = array('id'=>$id_sk, 'tgl_mulai'=>$d_sk['mulai']);
		} catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

		display_json($this->result);
	}

	public function delete_data()
	{
		$id_group = $this->input->post('params');

		try {
			$m_dgrp = new \Model\Storage\DetGroup_model();			
			$m_dgrp->where('id_group', $id_group)->delete();

			$m_grp = new \Model\Storage\Group_model();
			$m_grp->where('id_group', $id_group)->delete();

		    $this->result['status'] = 1;
			$this->result['message'] = 'Data berhasil di hapus';
		} catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

		display_json($this->result);
	}

	public function model($status)
	{
		$m_sp = new \Model\Storage\SapronakKesepakatan_model();
		$dashboard = $m_sp->getDashboard($status);

		return $dashboard;
	}
}