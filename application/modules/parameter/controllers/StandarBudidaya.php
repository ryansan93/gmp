<?php defined('BASEPATH') OR exit('No direct script access allowed');

class StandarBudidaya extends Public_Controller
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
				'assets/parameter/standar_budidaya/js/standar-budidaya.js'
			));
			$this->add_external_css(array(
				'assets/parameter/standar_budidaya/css/standar-budidaya.css'
			));

			$data = $this->includes;


			$content['akses'] = $akses;
			$content['list'] = $this->list_standar_budidaya();
			
			$data['title_menu'] = 'Master Standar Budidaya';
			$data['view'] = $this->load->view('parameter/standar_budidaya/index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function list_standar_budidaya()
	{
		$m_sb = new \Model\Storage\StandarBudidaya_model();
		$d_sb = $m_sb->with(['details', 'logs'])->get()->toArray();
		
		return $d_sb;
	}

	public function list_sb()
	{
		$akses = hakAkses($this->url);

		$content['akses'] = $akses;
		$content['list'] = $this->list_standar_budidaya();
		$html = $this->load->view('parameter/standar_budidaya/list', $content);
		
		echo $html;
	}

	public function load_form()
	{
		$id = $this->input->get('id');
		$resubmit = $this->input->get('resubmit');
		$html = '';

		if ( !empty($id) ) {
			$html = $this->view_form($id, $resubmit);
		} else {
			$html = $this->add_form();
		}

		echo $html;
	}

	public function view_form($id, $resubmit)
	{
		$akses = hakAkses($this->url);

		$m_sb = new \Model\Storage\StandarBudidaya_model();
		$d_sb = $m_sb->where('id', $id)->with(['details', 'logs'])->get()->toArray();

		$content['akses'] = $akses;
		$content['data'] = $d_sb;
		$content['resubmit'] = $resubmit;
		$html = $this->load->view('parameter/standar_budidaya/view_form', $content);
		
		return $html;
	}

	public function add_form()
	{
		$content = null;
		$html = $this->load->view('parameter/standar_budidaya/add_form', $content);
		
		return $html;
	}

	public function save_data()
	{
		$params = $this->input->post('params');

		try {
			$reject_id = isset($params['reject_id']) ? $params['reject_id'] : null;
			$tanggal_berlaku = $params['tanggal'];
			$detail_budidaya = $params['detail_budidaya'];

			$m_sb = new \Model\Storage\StandarBudidaya_model();
			$last_doc = $m_sb->orderBy('id','DESC')->first();
			$status_doc = getStatus('submit');

			// NOTE: save header standar budidaya
			$next_doc_number = $m_sb->getNextDocNum('ADM/SBD');
			$m_sb->nomor = $next_doc_number;
			$m_sb->mulai = $tanggal_berlaku;
			$m_sb->g_status = $status_doc;
			$m_sb->save();

			$deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
			Modules::run( 'base/event/save', $m_sb, $deskripsi_log);
			$id_saved = $m_sb->id;

			if ($id_saved) {
				// NOTE: update tanggal selesai pada dokumen sebelumnya
				if ($last_doc) {
					$end_date = prev_date($tanggal_berlaku);
					$m_sb->whereId($last_doc->id)->update(['selesai' => $end_date]);
				}

				foreach ($detail_budidaya as $val) {
					$daya_hidup = ($val['daya_hidup'] > 0) ? $val['daya_hidup'] / 100 : 0;

					$m_sbi = new \Model\Storage\DetStandarBudidaya_model();
					$m_sbi->id_budidaya = $id_saved;
					$m_sbi->umur = $val['umur'];
					$m_sbi->daya_hidup = $daya_hidup;
					$m_sbi->fcr = $val['fcr'];
					$m_sbi->kons_pakan_harian = $val['kons_pakan_harian'];
					$m_sbi->bb = $val['bb'];
					$m_sbi->ip = $val['ip'];
					$m_sbi->save();
					Modules::run( 'base/event/save', $m_sbi, $deskripsi_log);
				}
			}

			$this->result['status'] = 1;
			$this->result['message'] = 'Data berhasil disimpan';
			$this->result['content'] = array('id'=>$id_saved, 'tgl_mulai'=>$tanggal_berlaku);
		} catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

		display_json($this->result);
	}

	public function edit_data()
	{
		$params = $this->input->post('params');

		try {
			$edit_id = $params['edit_id'];
			$reject_id = isset($params['reject_id']) ? $params['reject_id'] : null;
			$tanggal_berlaku = $params['tanggal'];
			$detail_budidaya = $params['detail_budidaya'];

			$m_sb = new \Model\Storage\StandarBudidaya_model();
			$last_doc = $m_sb->orderBy('id','DESC')->first();
			$status_doc = getStatus('submit');

			// NOTE: update header standar budidaya
			$m_sb->where('id', $edit_id)->update(
				array(
					'mulai' => $tanggal_berlaku,
					'g_status' => $status_doc
				)
			);

			$d_sb = $m_sb->where('id', $edit_id)->first();

			$deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
			Modules::run( 'base/event/update', $d_sb, $deskripsi_log);
			$id_saved = $d_sb['id'];

			if ($id_saved) {
				$m_dsb = new \Model\Storage\DetStandarBudidaya_model();
				$m_dsb->where('id', $edit_id)->delete();

				foreach ($detail_budidaya as $val) {
					$daya_hidup = ($val['daya_hidup'] > 0) ? $val['daya_hidup'] / 100 : 0;

					$m_sbi = new \Model\Storage\DetStandarBudidaya_model();
					$m_sbi->id_budidaya = $id_saved;
					$m_sbi->umur = $val['umur'];
					$m_sbi->daya_hidup = $daya_hidup;
					$m_sbi->fcr = $val['fcr'];
					$m_sbi->kons_pakan_harian = $val['kons_pakan_harian'];
					$m_sbi->bb = $val['bb'];
					$m_sbi->ip = $val['ip'];
					$m_sbi->save();
					Modules::run( 'base/event/save', $m_sbi, $deskripsi_log);
				}
			}

			$this->result['status'] = 1;
			$this->result['message'] = 'Data berhasil diupdate';
			$this->result['content'] = array('id'=>$id_saved, 'tgl_mulai'=>$tanggal_berlaku);
		} catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

		display_json($this->result);
	}

	public function ack_data()
	{
		$id = $this->input->post('id');

		try {
			$m_sb = new \Model\Storage\StandarBudidaya_model();
			$status_doc = getStatus('ack');

			// NOTE: ack header standar performa
			$m_sb->where('id', $id)->update(
				array(
					'g_status' => $status_doc
				)
			);

			$d_sb = $m_sb->where('id', $id)->first();

			$deskripsi_log = 'di-ack oleh ' . $this->userdata['detail_user']['nama_detuser'];
			Modules::run( 'base/event/update', $d_sb, $deskripsi_log);

		    $this->result['status'] = 1;
			$this->result['message'] = 'Data berhasil di ACK';
			$this->result['content'] = array('id'=>$id, 'tgl_mulai'=>$d_sb['mulai']);
		} catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

		display_json($this->result);
	}

	public function approve_data()
	{
		$id = $this->input->post('id');

		try {
			$m_sb = new \Model\Storage\StandarBudidaya_model();
			$status_doc = getStatus('approve');

			// NOTE: ack header standar performa
			$m_sb->where('id', $id)->update(
				array(
					'g_status' => $status_doc
				)
			);

			$d_sb = $m_sb->where('id', $id)->first();

			$deskripsi_log = 'di-approve oleh ' . $this->userdata['detail_user']['nama_detuser'];
			Modules::run( 'base/event/update', $d_sb, $deskripsi_log);

		    $this->result['status'] = 1;
			$this->result['message'] = 'Data berhasil di APPROVE';
			$this->result['content'] = array('id'=>$id, 'tgl_mulai'=>$d_sb['mulai']);
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
		$m_sb = new \Model\Storage\StandarBudidaya_model();
		$dashboard = $m_sb->getDashboard($status);

		return $dashboard;
	}
}