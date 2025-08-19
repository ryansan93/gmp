<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SettingAutomaticJurnal extends Public_Controller
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
		// if ( $akses['a_view'] == 1 ) {
			$this->add_external_js(array(
				'assets/select2/js/select2.min.js',
				'assets/parameter/setting_automatic_jurnal/js/setting-automatic-jurnal.js'
			));
			$this->add_external_css(array(
				'assets/select2/css/select2.min.css',
				'assets/parameter/setting_automatic_jurnal/css/setting-automatic-jurnal.css'
			));

			$data = $this->includes;

			$content['akses'] = $akses;
			$content['riwayat'] = $this->riwayat();
			$content['addForm'] = $this->addForm();
			
			$data['title_menu'] = 'Setting Automatic Jurnal';
			$data['view'] = $this->load->view('parameter/setting_automatic_jurnal/index', $content, true);

			$this->load->view($this->template, $data);
		// } else {
		// 	showErrorAkses();
		// }
	}

	public function getFitur() {
		$m_conf = new \Model\Storage\Conf();
		$sql = "
			select 
				mf.nama_fitur,
				df.id_detfitur, 
				df.nama_detfitur 
			from detail_fitur df 
			left join
				ms_fitur mf
				on
					df.id_fitur = mf.id_fitur
			where
				mf.status = 1
			group by
				mf.nama_fitur,
				df.id_detfitur, 
				df.nama_detfitur
			order by 
				mf.nama_fitur asc,
				df.nama_detfitur asc
		";
		$d_conf = $m_conf->hydrateRaw( $sql );

		$data = null;
		if ( $d_conf->count() > 0 ) {
			$data = $d_conf->toArray();
		}

		return $data;
	}

	public function getDetJurnalTrans() {
		$m_conf = new \Model\Storage\Conf();
		$sql = "
			select 
				djt.*
			from det_jurnal_trans djt
			right join
				(select max(id) as id, kode from det_jurnal_trans group by kode) djt2
				on
					djt.id = djt2.id
			order by 
				djt.nama asc
		";
		$d_conf = $m_conf->hydrateRaw( $sql );

		$data = null;
		if ( $d_conf->count() > 0 ) {
			$data = $d_conf->toArray();
		}

		return $data;
	}

	public function getCoa() {
		$m_conf = new \Model\Storage\Conf();
		$sql = "
			select 
				c.coa as no_coa,
				c.nama_coa
			from coa c
			order by 
				c.nama_coa asc
		";
		$d_conf = $m_conf->hydrateRaw( $sql );

		$data = null;
		if ( $d_conf->count() > 0 ) {
			$data = $d_conf->toArray();
		}

		return $data;
	}

	public function getDataById($id) {
		$data = null;

		$m_conf = new \Model\Storage\Conf();
		$sql = "
			select 
				saj.*,
				df.nama_detfitur as nama,
				mf.nama_fitur
			from setting_automatic_jurnal saj
			left join
				detail_fitur df
				on
					saj.det_fitur_id = df.id_detfitur
			left join
				ms_fitur mf
				on
					df.id_fitur = mf.id_fitur
			where
				saj.id = ".$id."
		";
		$d_conf = $m_conf->hydrateRaw( $sql );

		if ( $d_conf->count() > 0 ) {
			$data = $d_conf->toArray()[0];

			$m_conf = new \Model\Storage\Conf();
			$sql = "
				select 
					sajd.*,
					coa_asal.nama_coa as nama_coa_asal,
					coa_tujuan.nama_coa as nama_coa_tujuan
				from setting_automatic_jurnal_det sajd
				left join
					coa coa_asal
					on
						sajd.coa_asal = coa_asal.coa
				left join
					coa coa_tujuan
					on
						sajd.coa_tujuan = coa_tujuan.coa
				where
					sajd.id_header = ".$id."
				order by
					sajd.urut
			";
			$d_conf = $m_conf->hydrateRaw( $sql );

			if ( $d_conf->count() > 0 ) {
				$data['detail'] = $d_conf->toArray();
			}
		}

		return $data;
	}

	public function loadForm()
    {
        $id = $this->input->get('id');
        $resubmit = $this->input->get('resubmit');
        $html = '';

        if ( !empty($id) ) {
            if ( !empty($resubmit) ) {
                /* NOTE : untuk edit */
                $html = $this->editForm($id);
            } else {
                /* NOTE : untuk view */
                $html = $this->viewForm($id);
            }
        } else {
            /* NOTE : untuk add */
            $html = $this->addForm();
        }

        echo $html;
    }

	public function getLists() {
		$params = $this->input->get('params');

		$m_conf = new \Model\Storage\Conf();
		$sql = "
			select
				saj.id,
				saj.tgl_berlaku,
				df.nama_detfitur as nama,
				mf.nama_fitur,
				saj.tbl_name
			from setting_automatic_jurnal saj 
			left join
				detail_fitur df
				on
					saj.det_fitur_id = df.id_detfitur
			left join
				ms_fitur mf
				on
					df.id_fitur = mf.id_fitur
			order by
				saj.tgl_berlaku desc
		";
		$d_conf = $m_conf->hydrateRaw( $sql );

		$data = null;
		if ( $d_conf->count() > 0 ) {
			$data = $d_conf->toArray();
		}

		$content['data'] = $data;
        $html = $this->load->view('parameter/setting_automatic_jurnal/list', $content, true);

		echo $html;
	}

    public function riwayat() {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $html = $this->load->view('parameter/setting_automatic_jurnal/riwayat', $content, true);

        return $html;
    }

    public function addForm() {
        $content['fitur'] = $this->getFitur();
        $content['det_jurnal_trans'] = $this->getDetJurnalTrans();
        $content['coa'] = $this->getCoa();
        $html = $this->load->view('parameter/setting_automatic_jurnal/addForm', $content, true);

        return $html;
    }

	public function viewForm($id) {
		$data = $this->getDataById($id);

        $content['data'] = $data;
        $html = $this->load->view('parameter/setting_automatic_jurnal/viewForm', $content, true);

        return $html;
    }

	public function editForm($id) {
		$data = $this->getDataById($id);

		$content['fitur'] = $this->getFitur();
        $content['det_jurnal_trans'] = $this->getDetJurnalTrans();
        $content['coa'] = $this->getCoa();
        $content['data'] = $data;
        $html = $this->load->view('parameter/setting_automatic_jurnal/editForm', $content, true);

        return $html;
    }

	public function save() {
		$params = $this->input->post('params');

		try {
			$m_saj = new \Model\Storage\SettingAutomaticJurnal_model();
			$m_saj->tgl_berlaku = $params['tgl_berlaku'];
			$m_saj->det_fitur_id = $params['id_detfitur'];
			$m_saj->_query = $params['query'];
			$m_saj->tbl_name = $params['tbl_name'];
			$m_saj->save();

			$id = $m_saj->id;

			foreach ($params['detail'] as $key => $value) {
				$m_sajd = new \Model\Storage\SettingAutomaticJurnalDet_model();
				$m_sajd->id_header = $id;
				$m_sajd->urut = $value['urut'];
				$m_sajd->det_jurnal_trans_kode = $value['det_jurnal_trans_kode'];
				$m_sajd->_query_coa_asal = $value['query_coa_asal'];
				$m_sajd->coa_asal = $value['coa_asal'];
				$m_sajd->_query_coa_tujuan = $value['query_coa_tujuan'];
				$m_sajd->coa_tujuan = $value['coa_tujuan'];
				$m_sajd->_ket = $value['keterangan'];
				$m_sajd->save();
			}

			$deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
			Modules::run( 'base/event/save', $m_saj, $deskripsi_log);

			$this->result['status'] = 1;
			$this->result['message'] = 'Data berhasil di simpan.';
			$this->result['content'] = array('id' => $id);
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

		display_json( $this->result );
	}

	public function edit() {
		$params = $this->input->post('params');

		try {
			$id = $params['id'];

			$m_saj = new \Model\Storage\SettingAutomaticJurnal_model();
			$m_saj->where('id', $id)->update(
				array(
					'tgl_berlaku' => $params['tgl_berlaku'],
					'det_fitur_id' => $params['id_detfitur'],
					'_query' => $params['query'],
					'tbl_name' => $params['tbl_name'],
				)
			);

			$m_sajd = new \Model\Storage\SettingAutomaticJurnalDet_model();
			$m_sajd->where('id_header', $id)->delete();

			foreach ($params['detail'] as $key => $value) {
				$m_sajd = new \Model\Storage\SettingAutomaticJurnalDet_model();
				$m_sajd->id_header = $id;
				$m_sajd->urut = $value['urut'];
				$m_sajd->det_jurnal_trans_kode = $value['det_jurnal_trans_kode'];
				$m_sajd->_query_coa_asal = $value['query_coa_asal'];
				$m_sajd->coa_asal = $value['coa_asal'];
				$m_sajd->_query_coa_tujuan = $value['query_coa_tujuan'];
				$m_sajd->coa_tujuan = $value['coa_tujuan'];
				$m_sajd->_ket = $value['keterangan'];
				$m_sajd->save();
			}

			$deskripsi_log = 'di-edit oleh ' . $this->userdata['detail_user']['nama_detuser'];
			Modules::run( 'base/event/update', $m_saj, $deskripsi_log);

			$this->result['status'] = 1;
			$this->result['message'] = 'Data berhasil di edit.';
			$this->result['content'] = array('id' => $id);
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

		display_json( $this->result );
	}

	public function delete() {
		$params = $this->input->post('params');

		try {
			$id = $params['id'];

			$m_saj = new \Model\Storage\SettingAutomaticJurnal_model();
			$d_saj = $m_saj->where('id', $id)->first();

			$m_saj->where('id', $id)->delete();

			$deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
			Modules::run( 'base/event/delete', $d_saj, $deskripsi_log);

			$this->result['status'] = 1;
			$this->result['message'] = 'Data berhasil di hapus.';
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

		display_json( $this->result );
	}
}