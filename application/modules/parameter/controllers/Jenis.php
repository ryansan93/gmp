<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Jenis extends Public_Controller
{
	private $url;
    private $pathView = 'parameter/jenis/';

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
				'assets/parameter/jenis/js/jenis.js'
			));
			$this->add_external_css(array(
				'assets/parameter/jenis/css/jenis.css'
			));

			$data = $this->includes;

			$content['akses'] = $akses;
			
			$data['title_menu'] = 'Master Jenis';
			$data['view'] = $this->load->view($this->pathView.'index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function getLists()
	{
		$m_jns = new \Model\Storage\Jenis_model();
		$d_jns = $m_jns->orderBy('kode', 'asc')->get();

		$data = null;
		if ( $d_jns->count() > 0 ) {
			$data = $d_jns->toArray();
		}

		$content['data'] = $data;
		$html = $this->load->view($this->pathView.'list', $content);

		echo $html;
	}

	public function addForm()
	{
        $content['title_panel'] = 'Master Jenis';
        $this->load->view($this->pathView.'addForm', $content);
	}

	public function editForm()
	{
		$id = $this->input->get('id');

		$m_jns = new \Model\Storage\Jenis_model();
		$d_jns = $m_jns->where('id', $id)->first()->toArray();

        $content['data'] = $d_jns;
        $html = $this->load->view($this->pathView.'editForm', $content);

        echo $html;
	}

    public function cekKodeJenis() {
        $params = $this->input->post('params');

        try {
            $id = isset($params['id']) ? $params['id'] : null;
            $kode = $params['kode'];

            $status = 0;
            $ket = null;
            if ( !empty($id) ) {
                $m_jns = new \Model\Storage\Jenis_model();
		        $d_jns = $m_jns->where('id', '<>', $id)->where('kode', $kode)->first();

                if ( $d_jns ) {
                    $status = 1;
                }
            } else {
                $m_jns = new \Model\Storage\Jenis_model();
		        $d_jns = $m_jns->where('kode', $kode)->first();

                if ( $d_jns ) {
                    $status = 1;
                }
            }

            if ( $status == 1 ) {
                $ket = 'Data kode jenis yang anda input sudah di gunakan, harap cek kembali data yang anda masukkan.';
            }

            $this->result['status'] = $status;
            $this->result['message'] = $ket;
        } catch (Exception $e) {
            $this->result['status'] = 2;
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

	public function save()
	{
		$params = $this->input->post('params');

		try {
			$m_jns = new \Model\Storage\Jenis_model();
			$m_jns->kode = $params['kode'];
			$m_jns->nama = $params['nama'];
			$m_jns->save();

			$deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_jns, $deskripsi_log );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data jenis berhasil disimpan';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}

	public function edit()
	{
		$params = $this->input->post('params');

		try {
			$m_jns = new \Model\Storage\Jenis_model();
			$m_jns->where('id', $params['id'])->update(
                array(
                    'kode' => $params['kode'],
                    'nama' => $params['nama']
                )
            );

			$d_jns = $m_jns->where('id', $params['id'])->first();

			$deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_jns, $deskripsi_log );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data jenis berhasil di update';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}
}