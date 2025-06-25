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
				'assets/parameter/setting_automatic_jurnal/js/setting-automatic-jurnal.js'
			));
			$this->add_external_css(array(
				'assets/parameter/setting_automatic_jurnal/css/setting-automatic-jurnal.css'
			));

			$data = $this->includes;

			$content['akses'] = $akses;
			$content['riwayat'] = $this->riwayat();
			$content['addForm'] = $this->addForm();
			
			$data['title_menu'] = 'SettingAutomaticJurnal';
			$data['view'] = $this->load->view('parameter/setting_automatic_jurnal/index', $content, true);

			$this->load->view($this->template, $data);
		// } else {
		// 	showErrorAkses();
		// }
	}

    public function riwayat() {
        $akses = hakAkses($this->url);

        $content['akses'] = $akses;
        $html = $this->load->view('parameter/setting_automatic_jurnal/riwayat', $content, true);

        return $html;
    }

    public function addForm() {
        $content = null;
        $html = $this->load->view('parameter/setting_automatic_jurnal/addForm', $content, true);

        return $html;
    }
}