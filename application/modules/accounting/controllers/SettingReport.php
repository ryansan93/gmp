<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SettingReport extends Public_Controller
{
    private $pathView = 'accounting/setting_report/';
    private $url;
	private $hakAkses;

	function __construct()
	{
		parent::__construct();
        $this->url = $this->current_base_uri;
		$this->hakAkses = hakAkses($this->url);
	}

	public function index()
	{
		if ( $this->hakAkses['a_view'] == 1 ) {
			$this->add_external_js(array(
				'assets/select2/js/select2.min.js',
				'assets/accounting/setting_report/js/setting-report.js'
			));
			$this->add_external_css(array(
				'assets/select2/css/select2.min.css',
				'assets/accounting/setting_report/css/setting-report.css'
			));

			$data = $this->includes;

			$data['title_menu'] = 'Setting Report';

			$content['add_form'] = $this->addForm();
            $content['riwayat'] = $this->riwayat();

			$content['akses'] = $this->hakAkses;
			$data['view'] = $this->load->view($this->pathView . 'index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function loadForm()
    {
        $params = $this->input->get('params');
        $edit = $this->input->get('edit');

        $id = $params['id'];

        $content = array();
        $html = "url not found";
        
        if ( !empty($id) && $edit != 'edit' ) {
            // NOTE: view data BASTTB (ajax)
            $html = $this->viewForm($id);
        } else if ( !empty($id) && $edit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $html = $this->editForm($id);
        }else{
            $html = $this->addForm();
        }

        echo $html;
    }

	public function getLists()
	{
		$m_conf = new \Model\Storage\Conf();
		$sql = "
			select * from setting_report sr
		";
		$d_conf = $m_conf->hydrateRaw( $sql );

		$data = null;
		if ( $d_conf->count() > 0 ) {
			$data = $d_conf->toArray();
		}

		$content['data'] = $data;
		$html = $this->load->view($this->pathView . 'list', $content, true);

		echo $html;
	}

	public function getItemReport()
	{
		$m_conf = new \Model\Storage\Conf();
		$sql = "
			select * from item_report
		";
		$d_ir = $m_conf->hydrateRaw( $sql );

		$data = null;
		if ( $d_ir->count() > 0 ) {
			$data = $d_ir->toArray();
		}

		return $data;
	}

	public function getCoa()
	{
		$m_conf = new \Model\Storage\Conf();
		$sql = "
			select coa, nama_coa from coa
		";
		$d_coa = $m_conf->hydrateRaw( $sql );

		$data = null;
		if ( $d_coa->count() > 0 ) {
			$data = $d_coa->toArray();
		}

		return $data;
	}
	
	public function riwayat()
	{
		$content = null;

		$html = $this->load->view($this->pathView . 'riwayat', $content, true);

		return $html;
	}

	public function addForm()
	{
		$content['item_report'] = $this->getItemReport();
		$content['coa'] = $this->getCoa();

		$html = $this->load->view($this->pathView . 'addForm', $content, true);

		return $html;
	}

	public function viewForm($id)
	{
		$m_conf = new \Model\Storage\Conf();
		$sql = "
			select
				sr.id,
				sr.nama as nama_laporan,
				srg.id as id_group,
				srg.nama as nama_group,
				srgi.id as id_group_item,
				ir.nama as nama_item_report,
				srgi.item_report_id,
				c.nama_coa,
				srgi.no_coa,
				srgi.posisi,
				srgi.posisi_jurnal,
				srgi.posisi_data,
				srgi.urut
			from setting_report_group_item srgi
			right join
				item_report ir
				on
					srgi.item_report_id = ir.id
			right join
				coa c
				on
					srgi.no_coa = c.coa
			right join
				setting_report_group srg
				on
					srgi.id_header = srg.id
			right join
				setting_report sr
				on
					srg.id_header = sr.id
			where
				sr.id = ".$id."
		";
		$d_sr = $m_conf->hydrateRaw( $sql );

		$data = null;
		if ( $d_sr->count() > 0 ) {
			$d_sr = $d_sr->toArray();

			$data['id'] = $d_sr[0]['id'];
			$data['nama'] = $d_sr[0]['nama_laporan'];
			foreach ($d_sr as $k_sr => $v_sr) {
				$key_urut = (int)$v_sr['urut'];
				$key_srgi = $v_sr['id_group_item'];

				if ( !isset($data['group'][ $v_sr['id_group'] ]) ) {
					$data['group'][ $v_sr['id_group'] ] = array(
						'id' => $v_sr['id_group'],
						'nama' => $v_sr['nama_group']
					);

					$data['group'][ $v_sr['id_group'] ]['urut'][ $key_urut ]['item'][ $key_srgi ] = array(
						'id' => $v_sr['id_group_item'],
						'nama_item' => $v_sr['nama_item_report'],
						'item_report_id' => $v_sr['item_report_id'],
						'nama_coa' => $v_sr['nama_coa'],
						'coa' => $v_sr['no_coa'],
						'posisi' => $v_sr['posisi'],
						'posisi_jurnal' => $v_sr['posisi_jurnal'],
						'posisi_data' => $v_sr['posisi_data'],
						'urut' => $v_sr['urut']
					);
				} else {
					$data['group'][ $v_sr['id_group'] ]['urut'][ $key_urut ]['item'][ $key_srgi ] = array(
						'id' => $v_sr['id_group_item'],
						'nama_item' => $v_sr['nama_item_report'],
						'item_report_id' => $v_sr['item_report_id'],
						'nama_coa' => $v_sr['nama_coa'],
						'coa' => $v_sr['no_coa'],
						'posisi' => $v_sr['posisi'],
						'posisi_jurnal' => $v_sr['posisi_jurnal'],
						'posisi_data' => $v_sr['posisi_data'],
						'urut' => $v_sr['urut']
					);
				}

				ksort( $data['group'][ $v_sr['id_group'] ]['urut'] );
			}
		}

		$content['data'] = $data;
		$html = $this->load->view($this->pathView . 'viewForm', $content, true);

		return $html;
	}

	public function editForm($id)
	{
		$m_conf = new \Model\Storage\Conf();
		$sql = "
			select
				sr.id,
				sr.nama as nama_laporan,
				srg.id as id_group,
				srg.nama as nama_group,
				srgi.id as id_group_item,
				ir.nama as nama_item_report,
				srgi.item_report_id,
				c.nama_coa,
				srgi.no_coa,
				srgi.posisi,
				srgi.posisi_jurnal,
				srgi.posisi_data,
				srgi.urut
			from setting_report_group_item srgi
			right join
				item_report ir
				on
					srgi.item_report_id = ir.id
			right join
				coa c
				on
					srgi.no_coa = c.coa
			right join
				setting_report_group srg
				on
					srgi.id_header = srg.id
			right join
				setting_report sr
				on
					srg.id_header = sr.id
			where
				sr.id = ".$id."
		";
		$d_sr = $m_conf->hydrateRaw( $sql );

		$data = null;
		if ( $d_sr->count() > 0 ) {
			$d_sr = $d_sr->toArray();

			$data['id'] = $d_sr[0]['id'];
			$data['nama'] = $d_sr[0]['nama_laporan'];
			foreach ($d_sr as $k_sr => $v_sr) {
				$key_srgi = $v_sr['urut'].' | '.$v_sr['id_group_item'];

				if ( !isset($data['group'][ $v_sr['id_group'] ]) ) {
					$data['group'][ $v_sr['id_group'] ] = array(
						'id' => $v_sr['id_group'],
						'nama' => $v_sr['nama_group']
					);

					$data['group'][ $v_sr['id_group'] ]['item'][ $key_srgi ] = array(
						'id' => $v_sr['id_group_item'],
						'nama_item' => $v_sr['nama_item_report'],
						'item_report_id' => $v_sr['item_report_id'],
						'nama_coa' => $v_sr['nama_coa'],
						'coa' => $v_sr['no_coa'],
						'posisi' => $v_sr['posisi'],
						'posisi_jurnal' => $v_sr['posisi_jurnal'],
						'posisi_data' => $v_sr['posisi_data'],
						'urut' => $v_sr['urut']
					);
				} else {
					$data['group'][ $v_sr['id_group'] ]['item'][ $key_srgi ] = array(
						'id' => $v_sr['id_group_item'],
						'nama_item' => $v_sr['nama_item_report'],
						'item_report_id' => $v_sr['item_report_id'],
						'nama_coa' => $v_sr['nama_coa'],
						'coa' => $v_sr['no_coa'],
						'posisi' => $v_sr['posisi'],
						'posisi_jurnal' => $v_sr['posisi_jurnal'],
						'posisi_data' => $v_sr['posisi_data'],
						'urut' => $v_sr['urut']
					);
				}

				ksort( $data['group'][ $v_sr['id_group'] ]['item'] );
			}
		}

		// cetak_r( $this->getCoa(), 1 );

		$content['data'] = $data;
		$content['item_report'] = $this->getItemReport();
		$content['coa'] = $this->getCoa();
		$html = $this->load->view($this->pathView . 'editForm', $content, true);

		return $html;
	}

	public function save()
	{
		$params = $this->input->post('params');

		try {
			$m_sr = new \Model\Storage\SettingReport_model();
			$m_sr->nama = $params['nama_laporan'];
			$m_sr->save();

			$id_sr = $m_sr->id;

			foreach ($params['data_group'] as $k_dg => $v_dg) {
				$m_srg = new \Model\Storage\SettingReportGroup_model();
				$m_srg->id_header = $id_sr;
				$m_srg->nama = $v_dg['nama_group'];
				$m_srg->save();

				$id_srg = $m_srg->id;

				foreach ($v_dg['detail'] as $k_dgi => $v_dgi) {
					$m_srgi = new \Model\Storage\SettingReportGroupItem_model();
					$m_srgi->id_header = $id_srg;
					$m_srgi->item_report_id = $v_dgi['item'];
					$m_srgi->no_coa = $v_dgi['coa'];
					$m_srgi->posisi = $v_dgi['posisi'];
					$m_srgi->posisi_jurnal = $v_dgi['posisi_jurnal'];
					$m_srgi->posisi_data = $v_dgi['posisi_data'];
					$m_srgi->urut = $v_dgi['urut'];
					$m_srgi->save();
				}
			}

			$deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_sr, $deskripsi_log );

			$this->result['status'] = 1;
			$this->result['content'] = array('id' => $id_sr);
			$this->result['message'] = 'Data berhasil di simpan.';
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

		display_json( $this->result );
	}

	public function edit()
	{
		$params = $this->input->post('params');

		try {
			$id_sr = $params['id'];

			$m_sr = new \Model\Storage\SettingReport_model();
			$m_sr->where('id', $id_sr)->update(
				array(
					'nama' => $params['nama_laporan']
				)
			);

			$m_srg = new \Model\Storage\SettingReportGroup_model();
			$id_srg = $m_srg->select('id')->where('id_header', $id_sr)->get()->toArray();

			$m_srgi = new \Model\Storage\SettingReportGroupItem_model();
			$m_srgi->whereIn('id_header', $id_srg)->delete();
			$m_srg->where('id_header', $id_sr)->delete();

			foreach ($params['data_group'] as $k_dg => $v_dg) {
				$m_srg = new \Model\Storage\SettingReportGroup_model();
				$m_srg->id_header = $id_sr;
				$m_srg->nama = $v_dg['nama_group'];
				$m_srg->save();

				$id_srg = $m_srg->id;

				foreach ($v_dg['detail'] as $k_dgi => $v_dgi) {
					$m_srgi = new \Model\Storage\SettingReportGroupItem_model();
					$m_srgi->id_header = $id_srg;
					$m_srgi->item_report_id = $v_dgi['item'];
					$m_srgi->no_coa = trim($v_dgi['coa']);
					$m_srgi->posisi = $v_dgi['posisi'];
					$m_srgi->posisi_jurnal = $v_dgi['posisi_jurnal'];
					$m_srgi->posisi_data = $v_dgi['posisi_data'];
					$m_srgi->urut = $v_dgi['urut'];
					$m_srgi->save();
				}
			}

			$d_sr = $m_sr->where('id', $id_sr)->first();

			$deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_sr, $deskripsi_log );

			$this->result['status'] = 1;
			$this->result['content'] = array('id' => $id_sr);
			$this->result['message'] = 'Data berhasil di update.';
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

		display_json( $this->result );
	}

	public function delete()
	{
		$params = $this->input->post('params');

		try {
			$id_sr = $params['id'];

			$m_sr = new \Model\Storage\SettingReport_model();

			$d_sr = $m_sr->where('id', $id_sr)->first();

			$m_srg = new \Model\Storage\SettingReportGroup_model();
			$id_srg = $m_srg->select('id')->where('id_header', $id_sr)->get()->toArray();

			$m_srgi = new \Model\Storage\SettingReportGroupItem_model();
			$m_srgi->whereIn('id_header', $id_srg)->delete();
			$m_srg->where('id_header', $id_sr)->delete();
			$m_sr->where('id', $id_sr)->delete();

			$deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_sr, $deskripsi_log );

			$this->result['status'] = 1;
			$this->result['content'] = array('id' => $id_sr);
			$this->result['message'] = 'Data berhasil di hapus.';
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

		display_json( $this->result );
	}
}
