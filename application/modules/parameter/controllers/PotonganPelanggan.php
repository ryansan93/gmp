<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PotonganPelanggan extends Public_Controller
{
	private $pathView = 'parameter/potongan_pelanggan/';
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
				'assets/parameter/potongan_pelanggan/js/potongan-pelanggan.js'
			));
			$this->add_external_css(array(
				'assets/parameter/potongan_pelanggan/css/potongan-pelanggan.css'
			));

			$data = $this->includes;

			$content['akses'] = $this->hakAkses;
			$content['add_form'] = $this->add_form();
			
			$data['title_menu'] = 'Master Potongan Pajak';
			$data['view'] = $this->load->view($this->pathView.'index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function get_lists()
	{
		$data = array();

		$m_pp = new \Model\Storage\PotonganPelanggan_model();
		$d_pp = $m_pp->with(['d_pelanggan'])->orderBy('id', 'desc')->get();

		$data = null;
		if ( $d_pp->count() > 0 ) {
			$data = $d_pp->toArray();
		}

		$content['data'] = $data;
		$html = $this->load->view($this->pathView.'list', $content);

		echo $html;
	}

	public function get_pelanggan()
    {
        $m_pelanggan = new \Model\Storage\Pelanggan_model();
        $d_no = $m_pelanggan->distinct('nomor')->select('nomor')->where('tipe', 'pelanggan')->get()->toArray();

        $data_pelanggan = null;
        if ( count($d_no) > 0 ) {
            foreach ($d_no as $k => $val) {
                $m_plg = new \Model\Storage\Pelanggan_model();
                $d_pelanggan = $m_plg->select('nomor', 'nama', 'alamat_kecamatan')->where('nomor', $val['nomor'])->where('tipe', 'pelanggan')->orderBy('version', 'desc')->first()->toArray();

                $m_kecamatan = new \Model\Storage\Lokasi_model();
                $d_kecamatan = $m_kecamatan->where('id', $d_pelanggan['alamat_kecamatan'])->first();
                $d_kab_kota = $m_kecamatan->where('id', $d_kecamatan->induk)->first();

                $key = $d_pelanggan['nama'].' - '.$val['nomor'];
                $data_pelanggan[ $key ] = $d_pelanggan;
                $data_pelanggan[ $key ]['kab_kota'] = $d_kab_kota->nama;
            }
        }

        if ( !empty($data_pelanggan) ) {
            ksort($data_pelanggan);
        }

        return $data_pelanggan;
    }

    public function load_form()
    {
        $id = $this->input->get('id');
        $resubmit = $this->input->get('resubmit');
        $html = '';

        if ( !empty($id) ) {
            if ( !empty($resubmit) ) {
                /* NOTE : untuk edit */
                $html = $this->edit_form($id);
            } else {
                /* NOTE : untuk view */
                $html = $this->view_form($id);
            }
        } else {
            /* NOTE : untuk add */
            $html = $this->add_form();
        }

        echo $html;
    }

	public function add_form()
	{
        $content['pelanggan'] = $this->get_pelanggan();
        $content['title_panel'] = 'Master Potongan Pajak';
        $html = $this->load->view($this->pathView.'add_form', $content, true);

        return $html;
	}

	public function edit_form($id)
	{
		$m_pp = new \Model\Storage\PotonganPelanggan_model();
		$d_pp = $m_pp->where('id', $id)->with(['d_pelanggan'])->first()->toArray();

		$content['pelanggan'] = $this->get_pelanggan();
        $content['data'] = $d_pp;
        $html = $this->load->view($this->pathView.'edit_form', $content, true);

        return $html;
	}

	public function view_form($id)
	{
		$m_pp = new \Model\Storage\PotonganPelanggan_model();
		$d_pp = $m_pp->where('id', $id)->with(['d_pelanggan'])->first()->toArray();

        $content['data'] = $d_pp;
        $content['title_panel'] = 'Master Potongan Pelanggan';
        $html = $this->load->view($this->pathView.'view_form', $content, true);

        return $html;
	}

	public function save()
	{
		$params = $this->input->post('params');

		try {
			$m_pp = new \Model\Storage\PotonganPelanggan_model();

			$m_pp->no_pelanggan = $params['pelanggan'];
			$m_pp->potongan_persen = $params['potongan'];
			$m_pp->start_date = $params['start_date'];
			$m_pp->end_date = $params['end_date'];
			$m_pp->aktif = $params['aktif'];
			$m_pp->save();

			$deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_pp, $deskripsi_log );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di-simpan';
            $this->result['content'] = array('id' => $m_pp->id);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}

	public function edit()
	{
		$params = $this->input->post('params');

		try {
			$m_pp = new \Model\Storage\PotonganPelanggan_model();
			$m_pp->where('id', $params['id'])->update(
				array(
					'no_pelanggan' => $params['pelanggan'],
					'potongan_persen' => $params['potongan'],
					'start_date' => $params['start_date'],
					'end_date' => $params['end_date'],
					'aktif' => $params['aktif']
				)
			);

			$d_pp = $m_pp->where('id', $params['id'])->first();

			$deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_pp, $deskripsi_log );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di-update';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}

	public function delete()
	{
		$id = $this->input->post('id');

		try {
			$m_pp = new \Model\Storage\PotonganPelanggan_model();
			$d_pp = $m_pp->where('id', $id)->first();

			$m_pp->where('id', $id)->delete();

			$deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_pp, $deskripsi_log );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di-hapus';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}
}