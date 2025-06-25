<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Group extends Public_Controller
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
				'assets/master/group/js/group.js'
			));
			$this->add_external_css(array(
				'assets/master/group/css/group.css'
			));

			$data = $this->includes;

			$data['title_menu'] = 'Master Group';

			$content['akses'] = $akses;
			$data['view'] = $this->load->view('master/group/index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function list_group()
	{
		$akses = hakAkses($this->url);

		$m_grp = new \Model\Storage\Group_model();
		$d_grp = $m_grp->with(['detail_group'])->get()->toArray();

		$content['akses'] = $akses;
		$content['list'] = $d_grp;
		$html = $this->load->view('master/group/list', $content, true);
		
		echo $html;
	}

	public function add_form()
	{
		$m_ftr = new \Model\Storage\Fitur_model();
		$d_ftr = $m_ftr->where('status', 1)->with(['detail_fitur'])->get()->toArray();

		$data['data_fitur'] = $d_ftr;
		$this->load->view('master/group/add_form', $data);
	}

	public function edit_form()
	{
		$id_group = $this->input->get('id_group');

		$m_grp = new \Model\Storage\Group_model();
		$d_grp = $m_grp->where('id_group', trim($id_group))->with(['detail_group'])->first();

		$m_ftr = new \Model\Storage\Fitur_model();
		$d_ftr = $m_ftr->where('status', 1)->with(['detail_fitur'])->get()->toArray();

		$data['data_group'] = $d_grp;
		$data['data_fitur'] = $d_ftr;

		$this->load->view('master/group/edit_form', $data);
	}

	public function save_data()
	{
		$params = $this->input->post('params');

		try {
			$cek_parent = $this->cek_parent($params['nama_group']);
			if ( !$cek_parent ) {
				$m_grp = new \Model\Storage\Group_model();

				$id_group = $m_grp->getNextId();

				$m_grp->id_group = $id_group;
				$m_grp->nama_group = $params['nama_group'];
				$m_grp->save();

				foreach ($params['detail_group'] as $key => $val) {
					$m_dgrp = new \Model\Storage\DetGroup_model();

					$id_detgroup = $m_dgrp->getNextId();

					$m_dgrp->id_detgroup = $id_detgroup;
					$m_dgrp->id_group = $id_group;
					$m_dgrp->id_detfitur = $val['id_detfitur'];
					$m_dgrp->a_view = $val['a_view'];
					$m_dgrp->a_submit = $val['a_submit'];
					$m_dgrp->a_edit = $val['a_edit'];
					$m_dgrp->a_delete = $val['a_delete'];
					$m_dgrp->a_ack = $val['a_ack'];
					$m_dgrp->a_approve = $val['a_approve'];
					$m_dgrp->save();
				}

			    $this->result['status'] = 1;
				$this->result['message'] = 'Data berhasil di simpan';
			} else {
				$this->result['message'] = 'Nama group yang anda masukkan sudah ada.';
			}
		} catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

		display_json($this->result);
	}

	public function edit_data()
	{
		$params = $this->input->post('params');

		try {
			$cek_parent = $this->cek_parent($params['nama_group'], $params['id_group']);
			if ( isset($cek_parent) ) {
				if ( $cek_parent['id_group'] == $params['id_group'] ) {
					$this->exec_edit($params);

				    $this->result['status'] = 1;
					$this->result['message'] = 'Data berhasil di edit';
				} else {
					$this->result['message'] = 'Nama group yang anda masukkan sudah ada.';	
				}
			} else {
				$this->exec_edit($params);

			    $this->result['status'] = 1;
				$this->result['message'] = 'Data berhasil di edit';
			}
		} catch (\Illuminate\Database\QueryException $e) {
			$this->result['message'] = "Gagal : " . $e->getMessage();
		}

		display_json($this->result);
	}

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

	public function cek_parent($nama_group='', $id_group=null)
	{
		$m_grp = new \Model\Storage\Group_model();

		if ( !isset($id_group) ) {
			$d_grp = $m_grp->where('nama_group', trim($nama_group))->first();
			$val = false;
			if ( isset($d_grp['id_group']) ) {
				$val = true;
			}
			return $val;
		} else {
			$d_grp = $m_grp->where('nama_group', trim($nama_group))->first();
			$data = null;
			if ( isset($d_grp['id_group']) ) {
				$data = array(
					'id_group'=>$d_grp['id_group'],
					'nama_group'=>$d_grp['nama_group']
				);
			}

			return $data;
		}

	}
}