<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SkpMitra extends Public_Controller
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
				'assets/parameter/skp_mitra/js/skp-mitra.js'
			));
			$this->add_external_css(array(
				'assets/parameter/skp_mitra/css/skp-mitra.css'
			));

			$data = $this->includes;

			$content['akses'] = $akses;
			
			$data['title_menu'] = 'SKP Peternak';
			$data['view'] = $this->load->view('parameter/skp_mitra/index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function get_list()
	{
		$m_skp = new \Model\Storage\SkpMitra_model();
		$d_skp = $m_skp->with(['d_mitra', 'lampiran', 'logs'])->orderBy('id', 'desc')->get()->toArray();

		$content['data'] = $d_skp;
		$html = $this->load->view('parameter/skp_mitra/list', $content);

		echo $html;
	}

	public function get_mitra()
	{
		$m_mitra = new \Model\Storage\Mitra_model();
		$d_mitra = $m_mitra->distinct('nomor')->select('nomor', 'nama')->orderBy('nama', 'asc')->get();

		$data = null;
		if ( !empty($d_mitra) ) {
			$d_mitra = $d_mitra->toArray();

			foreach ($d_mitra as $k_mitra => $v_mitra) {
				$_d_mitra = $m_mitra->select('nomor', 'nama')->where('nomor', $v_mitra['nomor'])->orderBy('version', 'desc')->first()->toArray();

				$data[ $v_mitra['nomor'] ] = $_d_mitra;
			}
		}

		return $data;
	}

	public function add_form()
	{
        $content['mitra'] = $this->get_mitra();
        $content['title_panel'] = 'SKP Peternak';
        $this->load->view('parameter/skp_mitra/add_form', $content);
	}

	public function edit_form()
	{
		$id = $this->input->get('id');

		$m_vaksin = new \Model\Storage\Vaksin_model();
		$d_vaksin = $m_vaksin->where('id', $id)->first();

        $content['data'] = $d_vaksin;
        $this->load->view('parameter/vaksin/edit_form', $content);
	}

	public function save()
	{
		$params = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['file']) ? $_FILES['file'] : [];

		try {
            // NOTE: simpan lampiran mitra
			$lampiran = $params['lampiran'];
        	$file = !empty($files) ? $files : null;
			$file_name = $path_name = null;
			$isMoved = 0;
			if (!empty($file)) {
				$moved = uploadFile($file);
				$isMoved = $moved['status'];
			}
			if ($isMoved) {
				$file_name = $moved['name'];
				$path_name = $moved['path'];

				$m_skp = new \Model\Storage\SkpMitra_model();
				$m_skp->nomor = $params['nomor'];
				$m_skp->mulai = $params['mulai'];
				$m_skp->berakhir = $params['berakhir'];
				$m_skp->lampiran = $file_name;
				$m_skp->save();

				$deskripsi_log_skp = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
				Modules::run( 'base/event/save', $m_skp, $deskripsi_log_skp );

				$m_lampiran = new \Model\Storage\Lampiran_model();
				$m_lampiran->tabel = 'skp_mitra';
                $m_lampiran->tabel_id = $m_skp->id;
				$m_lampiran->nama_lampiran = empty($lampiran['id']) ? null : $lampiran['id'];
				$m_lampiran->filename = $file_name ;
				$m_lampiran->path = $path_name;
				$m_lampiran->status = 1;
				$m_lampiran->save();

				$deskripsi_log_lampiran = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
				Modules::run( 'base/event/save', $m_lampiran, $deskripsi_log_lampiran );

				$this->result['status'] = 1;
            	$this->result['message'] = 'Data SKP <b>'. $params['nama'] .'</b> berhasil disimpan';
			}else {
				$this->result['status'] = 0;
    			$this->result['message'] = 'error, segera hubungi tim IT';
			}
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}

	// public function edit()
	// {
	// 	$params = $this->input->post('params');

	// 	try {
	// 		$m_vaksin = new \Model\Storage\Vaksin_model();

	// 		$m_vaksin->where('id', $params['id'])->update(
	// 				array(
	// 					'nama_vaksin' => $params['nama'],
	// 					'harga' => $params['harga']
	// 				)
	// 			);

	// 		$d_vaksin = $m_vaksin->where('id', $params['id'])->first();

	// 		$deskripsi_log_vaksin = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
 //            Modules::run( 'base/event/save', $d_vaksin, $deskripsi_log_vaksin );

	// 		$this->result['status'] = 1;
 //            $this->result['message'] = 'Data vaksin berhasil di update';
 //        } catch (\Illuminate\Database\QueryException $e) {
 //            $this->result['message'] = "Gagal : " . $e->getMessage();
 //        }

 //        display_json($this->result);
	// }
}