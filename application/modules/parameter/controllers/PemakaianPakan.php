<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PemakaianPakan extends Public_Controller
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
				'assets/parameter/pemakaian_pakan/js/pemakaian_pakan.js'
			));
			$this->add_external_css(array(
				'assets/parameter/pemakaian_pakan/css/pemakaian_pakan.css'
			));

			$data = $this->includes;


			$content['akses'] = $akses;
			$content['list'] = $this->list_standar_performa();
			
			$data['title_menu'] = 'Master Pemakaian Pakan';
			$data['view'] = $this->load->view('parameter/pemakaian_pakan/index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function list_standar_performa()
	{
		$m_sp = new \Model\Storage\StandarPerforma_model();
        $d_nomor = $m_sp->select('nomor')->distinct('nomor')->get()->toArray();

        $data = null;
        foreach ($d_nomor as $nomor) {
            $d_sp = $m_sp->where('nomor', $nomor['nomor'])->orderBy('version', 'desc')->with(['details', 'logs'])->first();

            if ( !empty($d_sp) ) {
                $data[ $d_sp['id'] ] = $d_sp->toArray();
            }
        }

        krsort($data);

        // cetak_r($data);

        return $data;
	}

	public function list_sp()
	{
		$akses = hakAkses($this->url);

		$content['akses'] = $akses;
		$content['list'] = $this->list_standar_performa();
		$html = $this->load->view('parameter/pemakaian_pakan/list', $content);
		
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

		$m_sp = new \Model\Storage\StandarPerforma_model();
		$d_sp = $m_sp->where('id', $id)->with(['details', 'logs'])->get()->toArray();

		$content['akses'] = $akses;
		$content['data'] = $d_sp;
		$content['resubmit'] = $resubmit;
		$content['tbl_logs'] = $this->getLogs( formatURL($d_sp[0]['nomor']) );
		$html = $this->load->view('parameter/pemakaian_pakan/view_form', $content);
		
		return $html;
	}

	public function getLogs($nomor = null) {
        $m_sp = new \Model\Storage\StandarPerforma_model;
        $d_sp = $m_sp->where('nomor', unformatURL($nomor))->orderBy('id', 'desc')->get()->toArray();

        $logs = array();
        foreach ($d_sp as $key => $v_sp) {
            $m_log = new \Model\Storage\LogTables_model;
            $d_log = $m_log->where('tbl_name', 'standart_performa')->where('tbl_id', $v_sp['id'])->get()->toArray();

            if ( !empty($d_log) ) {
                foreach ($d_log as $key => $v_log) {
                    $logs[ $v_log['id'] ] = $v_log;
                }
            }
        }

        krsort($logs);

        return $logs;
    }

	public function add_form()
	{
		$content = null;
		$html = $this->load->view('parameter/pemakaian_pakan/add_form', $content);
		
		return $html;
	}

	public function save_data()
	{
		$params = $this->input->post('params');

		try {
			$reject_id = isset($params['reject_id']) ? $params['reject_id'] : null;
			$tanggal_berlaku = $params['tanggal'];
			$detail_performa = $params['detail_performa'];

			$m_sp = new \Model\Storage\StandarPerforma_model();
			$last_doc = $m_sp->orderBy('id','DESC')->first();
			$status_doc = getStatus('submit');

			// NOTE: save header standar performa
			$next_doc_number = $m_sp->getNextDocNum('ADM/SPR');
			$m_sp->nomor = $next_doc_number;
			$m_sp->mulai = $tanggal_berlaku;
			$m_sp->status = $status_doc;
			$m_sp->version = 1;
			$m_sp->save();

			$deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
			Modules::run( 'base/event/save', $m_sp, $deskripsi_log);
			$id_saved = $m_sp->id;

			if ($id_saved) {
				// NOTE: update tanggal selesai pada dokumen sebelumnya
				if ($last_doc) {
					$end_date = prev_date($tanggal_berlaku);
					$m_sp->whereId($last_doc->id)->update(['selesai' => $end_date]);
				}

				foreach ($detail_performa as $val) {

					$daya_hidup = ($val['daya_hidup'] > 0) ? $val['daya_hidup'] / 100 : 0;
					$mortalitas = ($val['mortalitas'] > 0) ? $val['mortalitas'] / 100 : 0;

					$m_dsp = new \Model\Storage\DetStandarPerforma_model();
					$m_dsp->id_performa = $id_saved;
					$m_dsp->umur = $val['umur'];
					$m_dsp->daya_hidup = $daya_hidup;
					$m_dsp->mortalitas = $mortalitas;
					$m_dsp->kons_pakan = $val['kons_pakan'];
					$m_dsp->kons_pakan_harian = $val['kons_pakan_harian'];
					$m_dsp->bb = $val['bb'];
					$m_dsp->adg = $val['adg'];
					$m_dsp->fcr = $val['fcr'];
					$m_dsp->save();
					Modules::run( 'base/event/save', $m_dsp, $deskripsi_log);

				}

				// NOTE: delete data yg direject -> update status = 0 (delete)
				if (! empty($reject_id) ) {
					$m_sp = new \Model\Storage\StandarPerforma_model();
					$reject = $m_sp->find($reject_id);
					if ($reject) {
						$reject->status = getStatus('delete');
						$reject->save();

						$deskripsi_log = 'di-delete oleh ' . $this->userdata['Nama_User'] . ' {resubmit_id : ' . $id_saved . '}';
						Modules::run( 'base/event/delete', $reject, $deskripsi_log);
					}
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
			$detail_performa = $params['detail_performa'];

			$m_sp = new \Model\Storage\StandarPerforma_model();
			$d_sp = $m_sp->where('id', $edit_id)->first()->toArray();

			$last_doc = $m_sp->orderBy('id','DESC')->first();

			// NOTE: update standart performa edit
			$m_sp->where('nomor', $d_sp['nomor'])->update(
				array(
					'selesai' => $tanggal_berlaku
				)
			);

			// NOTE: save header standar performa
			$m_sp->nomor = $d_sp['nomor'];
			$m_sp->mulai = $tanggal_berlaku;
			$m_sp->status = $d_sp['status'];
			$m_sp->version = $d_sp['version'] + 1;
			$m_sp->save();

			$deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
			Modules::run( 'base/event/update', $m_sp, $deskripsi_log);
			$id_saved = $m_sp->id;

			if ($id_saved) {
				foreach ($detail_performa as $val) {

					$daya_hidup = ($val['daya_hidup'] > 0) ? $val['daya_hidup'] / 100 : 0;
					$mortalitas = ($val['mortalitas'] > 0) ? $val['mortalitas'] / 100 : 0;

					$m_dsp = new \Model\Storage\DetStandarPerforma_model();
					$m_dsp->id_performa = $id_saved;
					$m_dsp->umur = $val['umur'];
					$m_dsp->daya_hidup = $daya_hidup;
					$m_dsp->mortalitas = $mortalitas;
					$m_dsp->kons_pakan = $val['kons_pakan'];
					$m_dsp->kons_pakan_harian = $val['kons_pakan_harian'];
					$m_dsp->bb = $val['bb'];
					$m_dsp->adg = $val['adg'];
					$m_dsp->fcr = $val['fcr'];
					$m_dsp->save();
					Modules::run( 'base/event/update', $m_dsp, $deskripsi_log);
				}

				// NOTE: delete data yg direject -> update status = 0 (delete)
				if (! empty($reject_id) ) {
					$m_sp = new \Model\Storage\StandarPerforma_model();
					$reject = $m_sp->find($reject_id);
					if ($reject) {
						$reject->status = getStatus('delete');
						$reject->save();

						$deskripsi_log = 'di-delete oleh ' . $this->userdata['Nama_User'] . ' {resubmit_id : ' . $id_saved . '}';
						Modules::run( 'base/event/delete', $reject, $deskripsi_log);
					}
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

	// NOTE : EDIT DATA OLD
	// public function edit_data()
	// {
	// 	$params = $this->input->post('params');

	// 	try {
	// 		$edit_id = $params['edit_id'];
	// 		$reject_id = isset($params['reject_id']) ? $params['reject_id'] : null;
	// 		$tanggal_berlaku = $params['tanggal'];
	// 		$detail_performa = $params['detail_performa'];

	// 		$m_sp = new \Model\Storage\StandarPerforma_model();
	// 		$last_doc = $m_sp->orderBy('id','DESC')->first();
	// 		$status_doc = getStatus('submit');

	// 		// NOTE: update header standar performa
	// 		$m_sp->where('id', $edit_id)->update(
	// 			array(
	// 				'mulai' => $tanggal_berlaku,
	// 				'status' => $status_doc
	// 			)
	// 		);

	// 		$d_sp = $m_sp->where('id', $edit_id)->first();

	// 		$deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
	// 		Modules::run( 'base/event/update', $d_sp, $deskripsi_log);
	// 		$id_saved = $d_sp['id'];

	// 		if ($id_saved) {
	// 			$m_dsp = new \Model\Storage\DetStandarPerforma_model();
	// 			$m_dsp->where('id', $edit_id)->delete();

	// 			foreach ($detail_performa as $val) {

	// 				$daya_hidup = ($val['daya_hidup'] > 0) ? $val['daya_hidup'] / 100 : 0;
	// 				$mortalitas = ($val['mortalitas'] > 0) ? $val['mortalitas'] / 100 : 0;

	// 				$m_dsp = new \Model\Storage\DetStandarPerforma_model();
	// 				$m_dsp->id_performa = $edit_id;
	// 				$m_dsp->umur = $val['umur'];
	// 				$m_dsp->daya_hidup = $daya_hidup;
	// 				$m_dsp->mortalitas = $mortalitas;
	// 				$m_dsp->kons_pakan = $val['kons_pakan'];
	// 				$m_dsp->kons_pakan_harian = $val['kons_pakan_harian'];
	// 				$m_dsp->bb = $val['bb'];
	// 				$m_dsp->adg = $val['adg'];
	// 				$m_dsp->fcr = $val['fcr'];
	// 				$m_dsp->save();
	// 				Modules::run( 'base/event/update', $m_dsp, $deskripsi_log);
	// 			}

	// 			// NOTE: delete data yg direject -> update status = 0 (delete)
	// 			if (! empty($reject_id) ) {
	// 				$m_sp = new \Model\Storage\StandarPerforma_model();
	// 				$reject = $m_sp->find($reject_id);
	// 				if ($reject) {
	// 					$reject->status = getStatus('delete');
	// 					$reject->save();

	// 					$deskripsi_log = 'di-delete oleh ' . $this->userdata['Nama_User'] . ' {resubmit_id : ' . $id_saved . '}';
	// 					Modules::run( 'base/event/delete', $reject, $deskripsi_log);
	// 				}
	// 			}
	// 		}

	// 		$this->result['status'] = 1;
	// 		$this->result['message'] = 'Data berhasil diupdate';
	// 		$this->result['content'] = array('id'=>$id_saved, 'tgl_mulai'=>$tanggal_berlaku);
	// 	} catch (\Illuminate\Database\QueryException $e) {
	// 		$this->result['message'] = "Gagal : " . $e->getMessage();
	// 	}

	// 	display_json($this->result);
	// }

	public function exec_edit($params)
	{
		$m_grp = new \Model\Storage\Group_model();

		$id_group = $params['id_group'];

		$m_grp->where('id_group', $id_group)->update(
			array('nama_group'=>$params['nama_group'])
		);

		$m_dgrp = new \Model\Storage\DetGroup_model();
		$m_dgrp->where('id_group', $id_group)->delete();

		foreach ($params['detail_group'] as $key => $val) {
			$m_dgrp = new \Model\Storage\DetGroup_model();

			$id_detgroup = $m_dgrp->getNextId();

			$m_dgrp->id_detgroup = $id_detgroup;
			$m_dgrp->id_detfitur = $val['id_detfitur'];
			$m_dgrp->id_group = $id_group;
			$m_dgrp->a_view = $val['a_view'];
			$m_dgrp->a_submit = $val['a_submit'];
			$m_dgrp->a_edit = $val['a_edit'];
			$m_dgrp->a_delete = $val['a_delete'];
			$m_dgrp->a_ack = $val['a_ack'];
			$m_dgrp->a_approve = $val['a_approve'];
			$m_dgrp->save();
		}
	}

	public function ack_data()
	{
		$id = $this->input->post('id');

		try {
			$m_sp = new \Model\Storage\StandarPerforma_model();
			$status_doc = getStatus('ack');

			// NOTE: ack header standar performa
			$m_sp->where('id', $id)->update(
				array(
					'status' => $status_doc
				)
			);

			$d_sp = $m_sp->where('id', $id)->first();

			$deskripsi_log = 'di-ack oleh ' . $this->userdata['detail_user']['nama_detuser'];
			Modules::run( 'base/event/update', $d_sp, $deskripsi_log);

		    $this->result['status'] = 1;
			$this->result['message'] = 'Data berhasil di ACK';
			$this->result['content'] = array('id'=>$id, 'tgl_mulai'=>$d_sp['mulai']);
		} catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

		display_json($this->result);
	}

	public function approve_data()
	{
		$id = $this->input->post('id');

		try {
			$m_sp = new \Model\Storage\StandarPerforma_model();
			$status_doc = getStatus('approve');

			// NOTE: ack header standar performa
			$m_sp->where('id', $id)->update(
				array(
					'status' => $status_doc
				)
			);

			$d_sp = $m_sp->where('id', $id)->first();

			$deskripsi_log = 'di-approve oleh ' . $this->userdata['detail_user']['nama_detuser'];
			Modules::run( 'base/event/update', $d_sp, $deskripsi_log);

		    $this->result['status'] = 1;
			$this->result['message'] = 'Data berhasil di APPROVE';
			$this->result['content'] = array('id'=>$id, 'tgl_mulai'=>$d_sp['mulai']);
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
		$m_sp = new \Model\Storage\StandarPerforma_model();
		$dashboard = $m_sp->getDashboard($status);

		return $dashboard;
	}
}