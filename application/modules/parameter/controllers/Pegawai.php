<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Pegawai extends Public_Controller
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
				'assets/select2/js/select2.min.js',
				'assets/parameter/pegawai/js/pegawai.js'
			));
			$this->add_external_css(array(
				'assets/select2/css/select2.min.css',
				'assets/parameter/pegawai/css/pegawai.css'
			));

			$data = $this->includes;

			$content['akses'] = $akses;
			
			$data['title_menu'] = 'Master Pegawai';
			$data['view'] = $this->load->view('parameter/pegawai/index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function get_list()
	{
		$data = array();

		$m_karyawan = new \Model\Storage\Karyawan_model();
		$d_karyawan = $m_karyawan->where('status', 1)->with(['unit', 'dWilayah', 'logs'])->orderBy('level', 'asc')->get();

		if ( $d_karyawan->count() > 0 ) {
			$d_karyawan = $d_karyawan->toArray();
			foreach ($d_karyawan as $k_karyawan => $v_karyawan) {
				$data_unit = array();
				$data_wilayah = array();
				foreach ($v_karyawan['unit'] as $k_unit => $v_unit) {
					$nama_unit = $v_unit['unit'];
					if ( is_numeric($v_unit['unit']) ) {
						$m_wilayah = new \Model\Storage\Wilayah_model();
						$d_wilayah = $m_wilayah->where('id', $v_unit['unit'])->first();

						$nama_unit = $d_wilayah['nama'];
					}

					$data_unit[$k_unit] = array(
						'id' => $v_unit['id'],
						'nama' => $nama_unit
					);
				}

				foreach ($v_karyawan['d_wilayah'] as $k_wilayah => $v_wilayah) {
					$nama_wilayah = $v_wilayah['wilayah'];
					if ( is_numeric($v_wilayah['wilayah']) ) {
						$m_wilayah = new \Model\Storage\Wilayah_model();
						$d_wilayah = $m_wilayah->where('id', $v_wilayah['wilayah'])->first();

						$nama_wilayah = $d_wilayah['nama'];
					}

					$data_wilayah[$k_wilayah] = array(
						'id' => $v_wilayah['id'],
						'nama' => $nama_wilayah
					);
				}

				$d_atasan = $m_karyawan->where('id', $v_karyawan['atasan'])->first();

				$data[$k_karyawan] = array(
					'id' => $v_karyawan['id'],
					'level' => $v_karyawan['level'],
					'nik' => $v_karyawan['nik'],
					'nama' => $v_karyawan['nama'],
					'jabatan' => $v_karyawan['jabatan'],
					'atasan' => $d_atasan['nama'],
					'marketing' => $v_karyawan['marketing'],
					'kordinator' => $v_karyawan['kordinator'],
					'status' => $v_karyawan['status'],
					'wilayah' => $data_wilayah,
					'unit' => $data_unit
				);
			}
		}

		$content['data'] = $data;
		$html = $this->load->view('parameter/pegawai/list', $content);

		echo $html;
	}

	public function get_atasan()
	{
		$jabatan = $this->input->post('jabatan');
		$level = getLevelJabatan($jabatan);
		$atasan = getAtasan($jabatan);

		$d_karyawan = null;
		if ( $level != 0 ) {
			$m_karyawan = new \Model\Storage\Karyawan_model();
			$d_karyawan = $m_karyawan->where('level', '<', $level)
									 ->whereIn('jabatan', $atasan)
									 ->where('status', 1)
									 ->orderBy('level', 'asc')
									 ->get();
		}

		$this->result['status'] = 1;
		$this->result['content'] = $d_karyawan;

        display_json($this->result);
	}

	public function add_form()
	{
        $content['title_panel'] = 'Master Pegawai';
        $content['list_unit'] = $this->get_list_unit();
        $content['list_wilayah'] = $this->get_list_wilayah();
        $this->load->view('parameter/pegawai/add_form', $content);
	}

	public function edit_form()
	{
		$id = $this->input->get('id');

		$m_karyawan = new \Model\Storage\Karyawan_model();
		$d_karyawan = $m_karyawan->where('id', $id)->with(['unit', 'dWilayah'])->first()->toArray();

        $content['data'] = $d_karyawan;
        $content['list_unit'] = $this->get_list_unit();
        $content['list_wilayah'] = $this->get_list_wilayah();
        $this->load->view('parameter/pegawai/edit_form', $content);
	}

	public function get_list_unit()
	{
		$m_unit = new \Model\Storage\Wilayah_model();
		$d_unit = $m_unit->where('jenis', 'UN')->orderBy('nama')->get();

		return $d_unit;
	}

	public function get_list_wilayah()
	{
		$m_wilayah = new \Model\Storage\Wilayah_model();
		$d_wilayah = $m_wilayah->where('jenis', 'PW')->orderBy('nama')->get();

		return $d_wilayah;
	}

	public function save()
	{
		$params = $this->input->post('params');

		try {
			$m_karyawan = new \Model\Storage\Karyawan_model();

			$id_karyawan = $m_karyawan->getNextIdentity();

			$m_karyawan->id = $id_karyawan;
			$m_karyawan->level = $params['level'];
			$m_karyawan->nik = $m_karyawan->getNextNomor('K');
			$m_karyawan->atasan = $params['atasan'];
			$m_karyawan->nama = $params['nama'];
			$m_karyawan->kordinator = $params['koordinator'];
			$m_karyawan->marketing = $params['marketing'];
			$m_karyawan->jabatan = $params['jabatan'];
			$m_karyawan->status = 1;
			$m_karyawan->save();

            foreach ($params['unit'] as $k_val => $val) {
	            $m_unit_karyawan = new \Model\Storage\UnitKaryawan_model();

	            $id_unit_karyawan = $m_unit_karyawan->getNextIdentity();

				$m_unit_karyawan->id = $id_unit_karyawan;
				$m_unit_karyawan->id_karyawan = $id_karyawan;
				$m_unit_karyawan->unit = $val;
				$m_unit_karyawan->save();
            }

            foreach ($params['wilayah'] as $k_val => $val) {
	            $m_wilayah_karyawan = new \Model\Storage\WilayahKaryawan_model();

	            $id_wilayah_karyawan = $m_wilayah_karyawan->getNextIdentity();

				$m_wilayah_karyawan->id = $id_wilayah_karyawan;
				$m_wilayah_karyawan->id_karyawan = $id_karyawan;
				$m_wilayah_karyawan->wilayah = $val;
				$m_wilayah_karyawan->save();
            }

			$d_karyawan = $m_karyawan->where('id', $id_karyawan)->with(['unit', 'dWilayah'])->first();

			$deskripsi_log_karyawan = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_karyawan, $deskripsi_log_karyawan );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data karyawan berhasil disimpan';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}

	public function edit()
	{
		$params = $this->input->post('params');

		try {
			$m_karyawan = new \Model\Storage\Karyawan_model();

			$m_karyawan->where('id', $params['id'])->update(
				array(
						'status' => 0
					)
			);

			$id_karyawan = $m_karyawan->getNextIdentity();

			$m_karyawan->id = $id_karyawan;
			$m_karyawan->level = $params['level'];
			$m_karyawan->nik = $params['nik'];
			$m_karyawan->atasan = $params['atasan'];
			$m_karyawan->nama = $params['nama'];
			$m_karyawan->kordinator = $params['koordinator'];
			$m_karyawan->marketing = $params['marketing'];
			$m_karyawan->jabatan = $params['jabatan'];
			$m_karyawan->status = 1;
			$m_karyawan->save();

            foreach ($params['unit'] as $k_val => $val) {
	            $m_unit_karyawan = new \Model\Storage\UnitKaryawan_model();

	            $id_unit_karyawan = $m_unit_karyawan->getNextIdentity();

				$m_unit_karyawan->id = $id_unit_karyawan;
				$m_unit_karyawan->id_karyawan = $id_karyawan;
				$m_unit_karyawan->unit = $val;
				$m_unit_karyawan->save();
            }

            foreach ($params['wilayah'] as $k_val => $val) {
	            $m_wilayah_karyawan = new \Model\Storage\WilayahKaryawan_model();

	            $id_wilayah_karyawan = $m_wilayah_karyawan->getNextIdentity();

				$m_wilayah_karyawan->id = $id_wilayah_karyawan;
				$m_wilayah_karyawan->id_karyawan = $id_karyawan;
				$m_wilayah_karyawan->wilayah = $val;
				$m_wilayah_karyawan->save();
            }

			$d_karyawan = $m_karyawan->where('id', $id_karyawan)->with(['unit'])->first();

			$deskripsi_log_karyawan = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_karyawan, $deskripsi_log_karyawan );

			$this->result['status'] = 1;
            $this->result['message'] = 'Data karyawan berhasil di update';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
	}

	public function modalGaji()
	{
		$nik = $this->input->get('nik');

		$m_karyawan = new \Model\Storage\Karyawan_model();
		$d_karyawan = $m_karyawan->where('nik', $nik)->get()->toArray();

		cetak_r( $d_karyawan->toArray() );

		$content = null;
		$html = $this->load->view('parameter/pegawai/modal_gaji', $content);

		echo $html;
	}
}