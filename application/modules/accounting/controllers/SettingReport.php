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
			select * from setting_report sr order by sr.id asc
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
				srg.tipe as tipe_group,
				srg.urut as urut_group,
				srg.ref_group_ids,
				srgi.id as id_group_item,
				ir.nama as nama_item_report,
				srgi.item_report_id,
				c.nama_coa,
				srgi.no_coa,
				isnull(srgi.sign, 1) as sign,
				srgi.urut
			from setting_report_group srg
			left join
				setting_report_group_item srgi
				on
					srgi.id_header = srg.id
			left join
				item_report ir
				on
					srgi.item_report_id = ir.id
			left join
				coa c
				on
					srgi.no_coa = c.coa
			right join
				setting_report sr
				on
					srg.id_header = sr.id
			where
				sr.id = ".$id."
			order by
				isnull(srg.urut, srg.id) asc,
				srgi.urut asc
		";
		$d_sr = $m_conf->hydrateRaw( $sql );

		$data = null;
		if ( $d_sr->count() > 0 ) {
			$d_sr = $d_sr->toArray();

			$data['id']   = $d_sr[0]['id'];
			$data['nama'] = $d_sr[0]['nama_laporan'];

			foreach ($d_sr as $v_sr) {
				if ( !isset($data['group'][ $v_sr['id_group'] ]) ) {
					$data['group'][ $v_sr['id_group'] ] = array(
						'id'            => $v_sr['id_group'],
						'nama'          => $v_sr['nama_group'],
						'tipe'          => !empty($v_sr['tipe_group']) ? $v_sr['tipe_group'] : 'data',
						'urut_group'    => isset($v_sr['urut_group']) ? (int)$v_sr['urut_group'] : 0,
						'ref_group_ids' => $v_sr['ref_group_ids'],
						'urut'          => array()
					);
				}

				if ( !empty($v_sr['id_group_item']) ) {
					$key_urut = (int)$v_sr['urut'];
					$key_srgi = $v_sr['id_group_item'];
					$data['group'][ $v_sr['id_group'] ]['urut'][ $key_urut ]['item'][ $key_srgi ] = array(
						'id'             => $v_sr['id_group_item'],
						'nama_item'      => $v_sr['nama_item_report'],
						'item_report_id' => $v_sr['item_report_id'],
						'nama_coa'       => $v_sr['nama_coa'],
						'coa'  => $v_sr['no_coa'],
						'sign' => isset($v_sr['sign']) ? (int)$v_sr['sign'] : 1,
						'urut'           => $v_sr['urut']
					);
					ksort( $data['group'][ $v_sr['id_group'] ]['urut'] );
				}
			}

			// Compute ordinals for display
			$ordinal = 1;
			foreach ( $data['group'] as &$v_group ) { $v_group['ordinal'] = $ordinal++; }
			unset($v_group);

			$id_to_ordinal = array();
			foreach ( $data['group'] as $v_group ) { $id_to_ordinal[ $v_group['id'] ] = $v_group['ordinal']; }

			foreach ( $data['group'] as &$v_group ) {
				if ( $v_group['tipe'] === 'subtotal' && !empty($v_group['ref_group_ids']) ) {
					$ref_ords = array();
					foreach ( explode(',', $v_group['ref_group_ids']) as $rid ) {
						$rid = (int)trim($rid);
						if ( isset($id_to_ordinal[$rid]) ) $ref_ords[] = $id_to_ordinal[$rid];
					}
					$v_group['ref_ordinals_display'] = implode(',', $ref_ords);
				} else {
					$v_group['ref_ordinals_display'] = '';
				}
			}
			unset($v_group);
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
				srg.tipe as tipe_group,
				srg.urut as urut_group,
				srg.ref_group_ids,
				srgi.id as id_group_item,
				ir.nama as nama_item_report,
				srgi.item_report_id,
				c.nama_coa,
				srgi.no_coa,
				isnull(srgi.sign, 1) as sign,
				srgi.urut
			from setting_report_group srg
			left join
				setting_report_group_item srgi
				on
					srgi.id_header = srg.id
			left join
				item_report ir
				on
					srgi.item_report_id = ir.id
			left join
				coa c
				on
					srgi.no_coa = c.coa
			right join
				setting_report sr
				on
					srg.id_header = sr.id
			where
				sr.id = ".$id."
			order by
				isnull(srg.urut, srg.id) asc,
				srgi.urut asc
		";
		$d_sr = $m_conf->hydrateRaw( $sql );

		$data = null;
		if ( $d_sr->count() > 0 ) {
			$d_sr = $d_sr->toArray();

			$data['id']   = $d_sr[0]['id'];
			$data['nama'] = $d_sr[0]['nama_laporan'];

			foreach ($d_sr as $v_sr) {
				if ( !isset($data['group'][ $v_sr['id_group'] ]) ) {
					$data['group'][ $v_sr['id_group'] ] = array(
						'id'            => $v_sr['id_group'],
						'nama'          => $v_sr['nama_group'],
						'tipe'          => !empty($v_sr['tipe_group']) ? $v_sr['tipe_group'] : 'data',
						'urut'          => isset($v_sr['urut_group']) ? (int)$v_sr['urut_group'] : 0,
						'ref_group_ids' => $v_sr['ref_group_ids'],
						'item'          => array()
					);
				}

				// Only add item row if there is actual item data (subtotal groups have no items)
				if ( !empty($v_sr['id_group_item']) ) {
					$key_srgi = $v_sr['urut'] . ' | ' . $v_sr['id_group_item'];
					$data['group'][ $v_sr['id_group'] ]['item'][ $key_srgi ] = array(
						'id'             => $v_sr['id_group_item'],
						'nama_item'      => $v_sr['nama_item_report'],
						'item_report_id' => $v_sr['item_report_id'],
						'nama_coa'       => $v_sr['nama_coa'],
						'coa'  => $v_sr['no_coa'],
						'sign' => isset($v_sr['sign']) ? (int)$v_sr['sign'] : 1,
						'urut'           => $v_sr['urut']
					);
					ksort( $data['group'][ $v_sr['id_group'] ]['item'] );
				}
			}

			// Compute ordinal map: id -> ordinal position (for ref display)
			$ordinal = 1;
			foreach ( $data['group'] as &$v_group ) {
				$v_group['ordinal'] = $ordinal++;
			}
			unset($v_group);

			$id_to_ordinal = array();
			foreach ( $data['group'] as $v_group ) {
				$id_to_ordinal[ $v_group['id'] ] = $v_group['ordinal'];
			}

			foreach ( $data['group'] as &$v_group ) {
				if ( $v_group['tipe'] === 'subtotal' && !empty($v_group['ref_group_ids']) ) {
					$ref_ordinals = array();
					foreach ( explode(',', $v_group['ref_group_ids']) as $rid ) {
						$rid = (int)trim($rid);
						if ( isset($id_to_ordinal[$rid]) ) {
							$ref_ordinals[] = $id_to_ordinal[$rid];
						}
					}
					$v_group['ref_ordinals_display'] = implode(',', $ref_ordinals);
				} else {
					$v_group['ref_ordinals_display'] = '';
				}
			}
			unset($v_group);
		}

		$content['data']        = $data;
		$content['item_report'] = $this->getItemReport();
		$content['coa']         = $this->getCoa();
		$html = $this->load->view($this->pathView . 'editForm', $content, true);

		return $html;
	}

	public function exportExcel($id)
	{
		$m_conf = new \Model\Storage\Conf();
		$sql = "
			select
				sr.id,
				sr.nama as nama_laporan,
				srg.id as id_group,
				srg.nama as nama_group,
				srg.tipe as tipe_group,
				srg.urut as urut_group,
				srgi.id as id_group_item,
				ir.nama as nama_item_report,
				c.nama_coa,
				srgi.no_coa,
				isnull(srgi.sign, 1) as sign,
				srgi.urut
			from setting_report_group srg
			left join
				setting_report_group_item srgi
				on
					srgi.id_header = srg.id
			left join
				item_report ir
				on
					srgi.item_report_id = ir.id
			left join
				coa c
				on
					srgi.no_coa = c.coa
			right join
				setting_report sr
				on
					srg.id_header = sr.id
			where
				sr.id = ".$id."
			order by
				isnull(srg.urut, srg.id) asc,
				srgi.urut asc
		";
		$d_sr = $m_conf->hydrateRaw( $sql );

		$nama_laporan = 'SETTING_REPORT';
		$arr_header   = array('Urutan Group', 'Nama Group', 'Tipe', 'Urutan Item', 'Item', 'Nama COA', 'No. COA', 'Sign');
		$arr_column   = null;

		if ( $d_sr->count() > 0 ) {
			$d_sr = $d_sr->toArray();
			$nama_laporan = $d_sr[0]['nama_laporan'];

			$idx = 0;
			foreach ($d_sr as $v_sr) {
				$is_subtotal = ($v_sr['tipe_group'] === 'subtotal');
				$ada_item    = !empty($v_sr['id_group_item']);

				if ( !$ada_item && !$is_subtotal ) continue; // group 'data' kosong tanpa item, skip

				$sign      = isset($v_sr['sign']) ? (int)$v_sr['sign'] : 1;
				$sign_text = $ada_item ? (($sign === -1) ? '-1 (Balik Saldo)' : '+1 (Normal)') : '';

				$arr_column[ $idx ] = array(
					'Urutan Group' => array('value' => $v_sr['urut_group'], 'data_type' => 'integer'),
					'Nama Group'   => array('value' => $v_sr['nama_group'], 'data_type' => 'string'),
					'Tipe'         => array('value' => strtoupper(!empty($v_sr['tipe_group']) ? $v_sr['tipe_group'] : 'data'), 'data_type' => 'string'),
					'Urutan Item'  => array('value' => $ada_item ? $v_sr['urut'] : '', 'data_type' => 'text'),
					'Item'         => array('value' => $ada_item ? $v_sr['nama_item_report'] : '', 'data_type' => 'text'),
					'Nama COA'     => array('value' => $ada_item ? $v_sr['nama_coa'] : '', 'data_type' => 'text'),
					'No. COA'      => array('value' => $ada_item ? $v_sr['no_coa'] : '', 'data_type' => 'nik'),
					'Sign'         => array('value' => $sign_text, 'data_type' => 'text'),
				);

				$idx++;
			}
		}

		$filename = 'SETTING_REPORT_'.strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', $nama_laporan)).'_'.date('YmdHis');

		Modules::run( 'base/ExportExcel/exportExcelUsingSpreadSheet', $filename, $arr_header, $arr_column );

		$this->load->helper('download');
		force_download('export_excel/'.$filename.'.xlsx', NULL);
	}

	public function save()
	{
		$params = $this->input->post('params');

		try {
			$m_sr = new \Model\Storage\SettingReport_model();
			$m_sr->nama = $params['nama_laporan'];
			$m_sr->save();

			$id_sr     = $m_sr->id;
			$group_ids = array(); // ordinal (0-based) → actual DB id

			// Pass 1: insert groups and their items
			foreach ($params['data_group'] as $k_dg => $v_dg) {
				$tipe_group = !empty($v_dg['tipe_group']) ? $v_dg['tipe_group'] : 'data';

				$m_srg = new \Model\Storage\SettingReportGroup_model();
				$m_srg->id_header     = $id_sr;
				$m_srg->nama          = $v_dg['nama_group'];
				$m_srg->tipe          = $tipe_group;
				$m_srg->urut          = isset($v_dg['urut_group']) ? (int)$v_dg['urut_group'] : 0;
				$m_srg->ref_group_ids = null; // resolved in pass 2
				$m_srg->save();

				$group_ids[$k_dg] = $m_srg->id;

				if ( $tipe_group === 'data' && !empty($v_dg['detail']) ) {
					foreach ($v_dg['detail'] as $v_dgi) {
						$m_srgi = new \Model\Storage\SettingReportGroupItem_model();
						$m_srgi->id_header      = $m_srg->id;
						$m_srgi->item_report_id = $v_dgi['item'];
						$m_srgi->no_coa = $v_dgi['coa'];
						$m_srgi->urut   = $v_dgi['urut'];
						$m_srgi->sign   = isset($v_dgi['sign']) ? (int)$v_dgi['sign'] : 1;
						$m_srgi->save();
					}
				}
			}

			// Pass 2: resolve ref_group_ids (ordinal string "1,2" → actual IDs)
			foreach ($params['data_group'] as $k_dg => $v_dg) {
				$tipe_group = !empty($v_dg['tipe_group']) ? $v_dg['tipe_group'] : 'data';
				if ( $tipe_group !== 'subtotal' || empty($v_dg['ref_group_ids']) ) continue;

				$resolved = array();
				foreach ( explode(',', $v_dg['ref_group_ids']) as $ord ) {
					$idx = (int)trim($ord) - 1; // convert to 0-based index
					if ( isset($group_ids[$idx]) ) {
						$resolved[] = $group_ids[$idx];
					}
				}

				$m_srg_upd = new \Model\Storage\SettingReportGroup_model();
				$m_srg_upd->where('id', $group_ids[$k_dg])->update(array(
					'ref_group_ids' => implode(',', $resolved)
				));
			}

			$deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_sr, $deskripsi_log );

			$this->result['status']  = 1;
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
			$m_sr->where('id', $id_sr)->update(array( 'nama' => $params['nama_laporan'] ));

			// Delete existing groups & items
			$m_srg  = new \Model\Storage\SettingReportGroup_model();
			$id_srg = $m_srg->select('id')->where('id_header', $id_sr)->get()->toArray();

			$m_srgi = new \Model\Storage\SettingReportGroupItem_model();
			$m_srgi->whereIn('id_header', $id_srg)->delete();
			$m_srg->where('id_header', $id_sr)->delete();

			$group_ids = array();

			// Pass 1: re-insert groups and items
			foreach ($params['data_group'] as $k_dg => $v_dg) {
				$tipe_group = !empty($v_dg['tipe_group']) ? $v_dg['tipe_group'] : 'data';

				$m_srg = new \Model\Storage\SettingReportGroup_model();
				$m_srg->id_header     = $id_sr;
				$m_srg->nama          = $v_dg['nama_group'];
				$m_srg->tipe          = $tipe_group;
				$m_srg->urut          = isset($v_dg['urut_group']) ? (int)$v_dg['urut_group'] : 0;
				$m_srg->ref_group_ids = null;
				$m_srg->save();

				$group_ids[$k_dg] = $m_srg->id;

				if ( $tipe_group === 'data' && !empty($v_dg['detail']) ) {
					foreach ($v_dg['detail'] as $v_dgi) {
						$m_srgi = new \Model\Storage\SettingReportGroupItem_model();
						$m_srgi->id_header      = $m_srg->id;
						$m_srgi->item_report_id = $v_dgi['item'];
						$m_srgi->no_coa         = trim($v_dgi['coa']);
						$m_srgi->urut           = $v_dgi['urut'];
						$m_srgi->sign           = isset($v_dgi['sign']) ? (int)$v_dgi['sign'] : 1;
						$m_srgi->save();
					}
				}
			}

			// Pass 2: resolve ref_group_ids
			foreach ($params['data_group'] as $k_dg => $v_dg) {
				$tipe_group = !empty($v_dg['tipe_group']) ? $v_dg['tipe_group'] : 'data';
				if ( $tipe_group !== 'subtotal' || empty($v_dg['ref_group_ids']) ) continue;

				$resolved = array();
				foreach ( explode(',', $v_dg['ref_group_ids']) as $ord ) {
					$idx = (int)trim($ord) - 1;
					if ( isset($group_ids[$idx]) ) {
						$resolved[] = $group_ids[$idx];
					}
				}

				$m_srg_upd = new \Model\Storage\SettingReportGroup_model();
				$m_srg_upd->where('id', $group_ids[$k_dg])->update(array(
					'ref_group_ids' => implode(',', $resolved)
				));
			}

			$d_sr = (new \Model\Storage\SettingReport_model())->where('id', $id_sr)->first();

			$deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_sr, $deskripsi_log );

			$this->result['status']  = 1;
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
