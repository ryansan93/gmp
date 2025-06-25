<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ItemReport extends Public_Controller
{
    private $pathView = 'accounting/item_report/';
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
				'assets/accounting/item_report/js/item-report.js'
			));
			$this->add_external_css(array(
				'assets/select2/css/select2.min.css',
				'assets/accounting/item_report/css/item-report.css'
			));

			$data = $this->includes;

			$data['title_menu'] = 'Item Report';

            $content['riwayat'] = $this->riwayat();

			$content['akses'] = $this->hakAkses;
			$data['view'] = $this->load->view($this->pathView . 'index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function getLists()
	{
		$m_ir = new \Model\Storage\ItemReport_model();
        $d_ir = $m_ir->orderBy('nama', 'desc')->get();

        $data = null;
        if ( $d_ir->count() > 0 ) {
        	$data = $d_ir->toArray();
        }

		$content['data'] = $data;

		$html = $this->load->view($this->pathView . 'list', $content, true);

		echo $html;
	}

	public function riwayat()
	{
		$content = null;
		$html = $this->load->view($this->pathView . 'riwayat', $content, true);

		return $html;
	}

	public function modalAddForm()
	{
		$content = null;
		$html = $this->load->view($this->pathView . 'add_form', $content, true);

		echo $html;
	}

	public function modalViewForm()
	{
		$id = $this->input->get('id');

		$m_ir = new \Model\Storage\ItemReport_model();
		$d_ir = $m_ir->where('id', $id)->first()->toArray();

		$content['data'] = $d_ir;
		$html = $this->load->view($this->pathView . 'view_form', $content, true);

		echo $html;
	}

	public function modalEditForm()
	{
		$id = $this->input->get('id');

		$m_ir = new \Model\Storage\ItemReport_model();
		$d_ir = $m_ir->where('id', $id)->first()->toArray();

		$content['data'] = $d_ir;
		$html = $this->load->view($this->pathView . 'edit_form', $content, true);

		echo $html;
	}

	public function save()
	{
		$params = $this->input->post('params');

		try {
			$m_ir = new \Model\Storage\ItemReport_model();
			$m_ir->nama = $params['nama'];
			$m_ir->save();

			$deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_ir, $deskripsi_log );

			$this->result['status'] = 1;
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
			$m_ir = new \Model\Storage\ItemReport_model();
			$m_ir->where('id', $params['id'])->update(
				array('nama' => $params['nama'])
			);

			$d_ir = $m_ir->where('id', $params['id'])->first();

			$deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_ir, $deskripsi_log );

			$this->result['status'] = 1;
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
			$m_ir = new \Model\Storage\ItemReport_model();
			$d_ir = $m_ir->where('id', $params['id'])->first();

			$m_ir->where('id', $params['id'])->delete();

			$deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_ir, $deskripsi_log );

			$this->result['status'] = 1;
			$this->result['message'] = 'Data berhasil di hapus.';
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

		display_json( $this->result );
	}
}