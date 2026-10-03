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
		$d_karyawan = $m_karyawan->where('status', 1)->with(['unit', 'dWilayah', 'logs'])->orderBy('level', 'asc')->orderBy('jabatan', 'asc')->get();

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
					'atasan' => (isset($d_atasan['nama']) && !empty($d_atasan['nama'])) ? $d_atasan['nama'] : null,
					'marketing' => $v_karyawan['marketing'],
					'kordinator' => $v_karyawan['kordinator'],
					'status' => $v_karyawan['status'],
					'wilayah' => $data_wilayah,
					'unit' => $data_unit
				);
			}
		}

		$content['data'] = $data;
		$content['akses'] = hakAkses($this->url);
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

	public function injek($_nik = null) {

		$nik = null;
		if ( empty($_nik) ) {
			/* PUSAT JEMBER */
			$nik = array('K21001', 'K21002', 'K21003', 'K21005', 'K21007', 'K21010', 'K22174', 'K25232');
		} else {
			$nik = array($_nik);
		}

		$m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from mgb_erp_live.dbo.karyawan where nik in ('".implode("', '", $nik)."')
        ";
        $d_kry = $m_conf->hydrateRaw( $sql );

		if ( $d_kry->count() > 0 ) {
			$d_kry = $d_kry->toArray();

			foreach ($d_kry as $k_kry => $v_kry) {
				$m_karyawan = new \Model\Storage\Karyawan_model();
				$d_karyawan = $m_karyawan->where('nik', $v_kry['nik'])->first();

				if ( !$d_karyawan ) {
					$m_karyawan = new \Model\Storage\Karyawan_model();
					$d_kry = $m_karyawan->where('id', $v_kry['id'])->first();

					$id = $v_kry['id'];
					if ( $d_kry ) {
						$id = $m_karyawan->getNextIdentity();
					}

					$m_karyawan = new \Model\Storage\Karyawan_model();
					$m_karyawan->id = $id;
					$m_karyawan->level = $v_kry['level'];
					$m_karyawan->nik = $v_kry['nik'];
					$m_karyawan->atasan = $v_kry['atasan'];
					$m_karyawan->nama = $v_kry['nama'];
					$m_karyawan->kordinator = $v_kry['kordinator'];
					$m_karyawan->marketing = $v_kry['marketing'];
					$m_karyawan->jabatan = $v_kry['jabatan'];
					$m_karyawan->status = 1;
					$m_karyawan->save();
	
					$deskripsi_log_karyawan = 'di-injek oleh ' . $this->userdata['detail_user']['nama_detuser'];
					Modules::run( 'base/event/save', $m_karyawan, $deskripsi_log_karyawan );
	
					$m_conf = new \Model\Storage\Conf();
					$sql = "
						select * from mgb_erp_live.dbo.unit_karyawan uk where id_karyawan in (".$v_kry['id'].")
					";
					$d_uk = $m_conf->hydrateRaw( $sql );
	
					if ( $d_uk->count() > 0 ) {
						$d_uk = $d_uk->toArray();
	
						foreach ($d_uk as $k_uk => $val) {
							$m_unit_karyawan = new \Model\Storage\UnitKaryawan_model();
	
							$id_unit_karyawan = $m_unit_karyawan->getNextIdentity();
	
							$m_unit_karyawan->id = $id_unit_karyawan;
							$m_unit_karyawan->id_karyawan = $id;
							$m_unit_karyawan->unit = $val['unit'];
							$m_unit_karyawan->save();
						}
					}
			
					$m_conf = new \Model\Storage\Conf();
					$sql = "
						select * from mgb_erp_live.dbo.wilayah_karyawan wk where id_karyawan in (".$v_kry['id'].")
					";
					$d_wk = $m_conf->hydrateRaw( $sql );
	
					if ( $d_wk->count() > 0 ) {
						$d_wk = $d_wk->toArray();
	
						foreach ($d_wk as $k_wk => $val) {
							$m_wilayah_karyawan = new \Model\Storage\WilayahKaryawan_model();
	
							$id_wilayah_karyawan = $m_wilayah_karyawan->getNextIdentity();
	
							$m_wilayah_karyawan->id = $id_wilayah_karyawan;
							$m_wilayah_karyawan->id_karyawan = $id;
							$m_wilayah_karyawan->wilayah = $val['wilayah'];
							$m_wilayah_karyawan->save();
						}
					}

					/* USER */
					$m_conf = new \Model\Storage\Conf();
					$sql = "
						select du1.* from mgb_erp_live.dbo.detail_user du1 
						right join
							(
								select max(id_detuser) as id_detuser, id_user from mgb_erp_live.dbo.detail_user where UPPER(nama_detuser) = UPPER('".$v_kry['nama']."') group by id_user
							) du2
							on
								du2.id_detuser = du1.id_detuser 
					";
					$d_du = $m_conf->hydrateRaw( $sql );
	
					if ( $d_du->count() > 0 ) {
						$d_du = $d_du->toArray();
	
						foreach ($d_du as $k_du => $v_du) {
							$m_conf = new \Model\Storage\Conf();
							$sql = "
								select * from mgb_erp_live.dbo.ms_user mu where id_user = '".$v_du['id_user']."'
							";
							$d_mu = $m_conf->hydrateRaw( $sql );

							if ( $d_mu->count() > 0 ) {
								$d_mu = $d_mu->toArray();
			
								foreach ($d_mu as $k_mu => $v_mu) {
									$m_usr = new \Model\Storage\User_model();
									$m_usr->id_user = $v_mu['id_user'];
									$m_usr->username_user = $v_mu['username_user'];
									$m_usr->status_user = $v_mu['status_user'];
									$m_usr->pass_user = $v_mu['pass_user'];
									$m_usr->save();
								}
							}

							$m_dusr = new \Model\Storage\DetUser_model();
							$m_dusr->id_detuser = $v_du['id_detuser'];
							$m_dusr->id_user = $v_du['id_user'];
							$m_dusr->aktif_detuser = $v_du['aktif_detuser'];
							$m_dusr->jk_detuser = $v_du['jk_detuser'];
							$m_dusr->email_detuser = $v_du['email_detuser'];
							$m_dusr->nama_detuser = $v_du['nama_detuser'];
							$m_dusr->username_detuser = $v_du['username_detuser'];
							$m_dusr->pass_detuser = $v_du['pass_detuser'];
							$m_dusr->telp_detuser = $v_du['telp_detuser'];
							$m_dusr->id_group = $v_du['id_group'];
							$m_dusr->avatar_detuser = $v_du['avatar_detuser'];
							$m_dusr->edit_detuser = $v_du['edit_detuser'];
							$m_dusr->useredit_detuser = $v_du['useredit_detuser'];
							$m_dusr->save();
						}
					}
					/* END - USER */
				}
			}
		}
	}

	public function tesPassword () {
		$userId = 'USR2509008';

		$hash_password = password_hash($userId, PASSWORD_BCRYPT);

		cetak_r( $hash_password );
	}

	/**************************************************************************************
	 * CLONE PEGAWAI KE DATABASE GML
	 *
	 * Clone 1 data pegawai (baris karyawan versi terbaru beserta unit_karyawan &
	 * wilayah_karyawan) dari database aplikasi ini ke database GML (perusahaan lain hasil
	 * fitur "Clone Perusahaan" - lihat base/CloneCompany.php). ID dipertahankan identik ke
	 * sumber. Tidak ada lampiran/dokumen utk pegawai di skema ini.
	 *
	 * SEKALIGUS clone akun user (ms_user + detail_user) milik pegawai itu, dicocokkan lewat
	 * NAMA PERSIS (skema TIDAK punya FK dari karyawan ke user) - pola ini SUDAH ADA di
	 * codebase ini sendiri lewat Pegawai::injek() (utk data mgb_erp_live), cuma sekarang
	 * diarahkan ke GML. Password hash ikut di-copy apa adanya (disetujui eksplisit) -
	 * pegawai bisa login ke GML pakai password GMP yg sama. Akun yg SUDAH ADA di GML
	 * (dicek per id_user/id_detuser) TIDAK PERNAH ditimpa, baik saat clone baru maupun
	 * injek ulang - supaya tidak berisiko merusak akses yg mungkin sudah dipakai di GML.
	 *
	 * Guard: kalau NIK ini sudah ada di GML dan MASIH AKTIF (status=1) -> ditolak total.
	 * Kalau sudah ada tapi NONAKTIF (status=0) -> tawarkan konfirmasi injek ulang (param
	 * 'force'=1 dari frontend); kalau disetujui, data karyawan lama di GML dihapus dulu
	 * (cascade - TIDAK termasuk ms_user/detail_user) baru clone ulang jalan dari awal.
	 *
	 * Pola & helper (dbConn/getGmlDbName/cloneTableRows/cloneLogHistoryRows) SENGAJA
	 * diduplikasi persis dari parameter/Supplier.php dkk (bukan trait/base class bersama) -
	 * konsisten dgn konvensi codebase ini. cloneLokasiChain()/copyLampiranFiles() TIDAK
	 * diduplikasi di sini krn karyawan tidak punya alamat/lampiran.
	 **************************************************************************************/

	public function cloneToGml()
	{
		$akses = hakAkses($this->url);
		if ( empty($akses['a_submit']) || $akses['a_submit'] != 1 ) {
			$this->result['message'] = 'Anda tidak memiliki akses untuk clone data pegawai ke GML.';
			display_json($this->result);
			return;
		}

		$nik = $this->input->post('nik');

		try {
			if ( empty($nik) ) {
				throw new Exception('NIK pegawai tidak boleh kosong.');
			}

			$gmlDb = $this->getGmlDbName();

			// NOTE: 1. ambil versi TERBARU karyawan (konvensi max(id) per nik - lihat
			// edit() yg selalu bikin baris baru).
			$m_karyawan = new \Model\Storage\Karyawan_model();
			$sql = "
				SELECT k.* FROM karyawan k
				RIGHT JOIN (
					SELECT MAX(id) AS id, nik FROM karyawan WHERE nik = ? GROUP BY nik
				) k2 ON k.id = k2.id
			";
			$d_karyawan = $m_karyawan->hydrateRaw($sql, array($nik));
			if ( $d_karyawan->count() == 0 ) {
				throw new Exception("Data pegawai dengan NIK '{$nik}' tidak ditemukan.");
			}
			$karyawan = $d_karyawan->first();
			$karyawanId = $karyawan->id;

			// NOTE: 2. guard - kalau NIK ini sudah ada di GML: tolak total kalau MASIH
			// AKTIF (status=1), tapi kalau NONAKTIF tawarkan konfirmasi injek ulang
			// (force=1) alih2 langsung menolak - data karyawan lama di GML dihapus dulu
			// (cascade, TIDAK termasuk user) baru clone ulang.
			$force = $this->input->post('force');
			$existing = $this->dbConn()->selectOne("SELECT id, status FROM [{$gmlDb}].dbo.[karyawan] WHERE nik = ?", array($nik));
			if ( !empty($existing) ) {
				$isAktif = ( (int) $existing['status'] === 1 );
				if ( $isAktif ) {
					throw new Exception("Pegawai NIK {$nik} sudah pernah di-clone ke GML (id GML #{$existing['id']}) dan masih aktif.");
				}
				if ( empty($force) ) {
					$this->result['status'] = 0;
					$this->result['need_confirm'] = 1;
					$this->result['message'] = "Pegawai NIK {$nik} sudah pernah di-clone ke GML (id GML #{$existing['id']}) tapi berstatus tidak aktif. Injek ulang? Data karyawan lama di GML akan dihapus & diganti data terbaru dari GMP (akun user login TIDAK ikut dihapus/ditimpa).";
					display_json($this->result);
					return;
				}
				$this->deleteGmlKaryawanCascade($gmlDb, $existing['id']);
			}

			$report = array();

			// NOTE: 3. data inti pegawai.
			$report['karyawan'] = $this->cloneTableRows('karyawan', 'id = ?', array($karyawanId));
			$report['unit_karyawan'] = $this->cloneTableRows('unit_karyawan', 'id_karyawan = ?', array($karyawanId));
			$report['wilayah_karyawan'] = $this->cloneTableRows('wilayah_karyawan', 'id_karyawan = ?', array($karyawanId));

			// NOTE: 3.5. cek apakah atasan pegawai ini (karyawan.atasan = id karyawan lain,
			// TANPA FK constraint - dikonfirmasi lewat get_list() yg query where('id', atasan))
			// sudah ada di GML. SENGAJA TIDAK auto-clone rantai atasan (bisa menyeret banyak
			// orang + akun login masing2 tanpa disadari user) - cuma dikasih info supaya user
			// bisa clone manual atasannya duluan kalau perlu.
			$atasanInfo = null;
			if ( !empty($karyawan->atasan) ) {
				$existingAtasan = $this->dbConn()->selectOne("SELECT id FROM [{$gmlDb}].dbo.[karyawan] WHERE id = ?", array($karyawan->atasan));
				if ( empty($existingAtasan) ) {
					$atasanSumber = $this->dbConn()->selectOne("SELECT nik, nama FROM karyawan WHERE id = ?", array($karyawan->atasan));
					if ( !empty($atasanSumber) ) {
						$atasanInfo = "Atasan pegawai ini ({$atasanSumber['nama']}, NIK {$atasanSumber['nik']}) belum ada di GML - clone dulu atasannya kalau ingin hierarki tampil lengkap.";
					}
				}
			}
			$report['atasan_info'] = $atasanInfo;

			// NOTE: 4. akun user (ms_user + detail_user), dicocokkan lewat nama.
			$report['user'] = $this->cloneKaryawanUser($gmlDb, $karyawan->nama);

			// NOTE: 5. riwayat log_tables (tidak ada tampilan "Keterangan" khusus di list
			// Pegawai skrg, tapi tetap di-clone utk konsistensi & kelengkapan audit) +
			// snapshot log_history + 1 catatan baru langsung di GML sbg jejak provenance.
			$logIds = array_map(function($r){ return $r['id']; }, $this->dbConn()->select("SELECT id FROM log_tables WHERE tbl_name = 'karyawan' AND tbl_id = ?", array($karyawanId)));
			$report['log_tables'] = $this->cloneTableRows('log_tables', "tbl_name = 'karyawan' AND tbl_id = ?", array($karyawanId));
			$report['log_history'] = $this->cloneLogHistoryRows($logIds);

			$deskripsi_provenance = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
			$this->dbConn()->statement("
				INSERT INTO [{$gmlDb}].dbo.[log_tables] ([tbl_name],[tbl_id],[user_id],[waktu],[deskripsi],[_action])
				VALUES ('karyawan', ?, ?, GETDATE(), ?, 'insert')
			", array($karyawanId, $this->userid, $deskripsi_provenance));

			$this->result['status'] = 1;
			$this->result['message'] = "Data pegawai {$karyawan->nama} ({$nik}) berhasil di-clone ke GML.";
			if ( !empty($atasanInfo) ) {
				$this->result['message'] .= ' ' . $atasanInfo;
			}
			$this->result['content'] = $report;
		} catch (\Exception $e) {
			$this->result['message'] = 'Gagal clone ke GML: ' . $e->getMessage();
		}

		display_json($this->result);
	}

	/**
	 * Clone akun user (ms_user + detail_user) milik pegawai bernama $nama ke GML,
	 * dicocokkan lewat NAMA PERSIS (UPPER(nama_detuser) = UPPER($nama)) - lihat catatan
	 * di cloneToGml() kenapa lewat nama, bukan FK. Ambil versi TERBARU detail_user per
	 * id_user (max id_detuser), BISA lebih dari 1 id_user kalau ada beberapa akun dgn
	 * nama sama (jarang tp mungkin - mirror perilaku Pegawai::injek() yg jg loop semua
	 * match, bukan cuma ambil satu). ms_user/detail_user yg SUDAH ADA di GML dilewati
	 * (tidak pernah ditimpa).
	 */
	private function cloneKaryawanUser($gmlDb, $nama)
	{
		$report = array('ditemukan' => 0, 'ms_user' => 0, 'detail_user' => 0);

		$d_du_list = $this->dbConn()->select("
			SELECT du1.* FROM detail_user du1
			RIGHT JOIN (
				SELECT MAX(id_detuser) AS id_detuser, id_user FROM detail_user
				WHERE UPPER(nama_detuser) = UPPER(?)
				GROUP BY id_user
			) du2 ON du2.id_detuser = du1.id_detuser
		", array($nama));

		foreach ($d_du_list as $d_du) {
			$report['ditemukan']++;

			$existingUser = $this->dbConn()->selectOne("SELECT id_user FROM [{$gmlDb}].dbo.[ms_user] WHERE id_user = ?", array($d_du['id_user']));
			if ( empty($existingUser) ) {
				$report['ms_user'] += $this->cloneTableRows('ms_user', 'id_user = ?', array($d_du['id_user']));
			}

			$existingDetUser = $this->dbConn()->selectOne("SELECT id_detuser FROM [{$gmlDb}].dbo.[detail_user] WHERE id_detuser = ?", array($d_du['id_detuser']));
			if ( empty($existingDetUser) ) {
				$report['detail_user'] += $this->cloneTableRows('detail_user', 'id_detuser = ?', array($d_du['id_detuser']));
			}
		}

		return $report;
	}

	/**
	 * Hapus tuntas (cascade) 1 karyawan beserta unit_karyawan/wilayah_karyawan-nya
	 * LANGSUNG DI GML - dipakai guard cloneToGml() saat user pilih "injek ulang" utk
	 * baris GML yg statusnya sudah nonaktif. SENGAJA TIDAK menghapus ms_user/detail_user
	 * (lihat catatan cloneToGml()). $karyawanId di sini id versi GML yg mau dihapus
	 * (BUKAN id sumber). unit_karyawan->karyawan ADALAH FK constraint asli di skema
	 * (dikonfirmasi live, beda dgn kebanyakan tabel lain di app ini yg tanpa FK sama
	 * sekali) - urutan delete (child dulu) WAJIB spt ini.
	 */
	private function deleteGmlKaryawanCascade($gmlDb, $karyawanId)
	{
		$this->dbConn()->statement("DELETE FROM [{$gmlDb}].dbo.[log_tables] WHERE tbl_name = 'karyawan' AND tbl_id = ?", array($karyawanId));
		$this->dbConn()->statement("DELETE FROM [{$gmlDb}].dbo.[unit_karyawan] WHERE id_karyawan = ?", array($karyawanId));
		$this->dbConn()->statement("DELETE FROM [{$gmlDb}].dbo.[wilayah_karyawan] WHERE id_karyawan = ?", array($karyawanId));
		$this->dbConn()->statement("DELETE FROM [{$gmlDb}].dbo.[karyawan] WHERE id = ?", array($karyawanId));
	}

	/**
	 * Koneksi database (Eloquent) yg dipakai semua query raw di fitur clone-ke-GML ini.
	 * Lihat komentar versi Peternak.php::dbConn() utk detail kenapa TIDAK pakai facade
	 * \Illuminate\Support\Facades\DB (belum ter-bootstrap di app ini - Capsule::setAsGlobal()
	 * di MY_Controller.php tidak memanggil Facade::setFacadeApplication()).
	 */
	private function dbConn()
	{
		return (new \Model\Storage\Conf())->getConnection();
	}

	/**
	 * Nama database GML dari konfigurasi koneksi ('gml' di application/config/env.php).
	 * Divalidasi harus 1 SQL Server yg sama dgn koneksi 'default' krn seluruh clone di
	 * sini pakai query cross-database T-SQL langsung, bukan replikasi via jaringan.
	 */
	private function getGmlDbName()
	{
		$conn = $this->config->item('connection');
		if ( empty($conn['gml']['database']) ) {
			throw new Exception("Koneksi 'gml' belum dikonfigurasi di application/config/env.php.");
		}
		if ( $conn['gml']['host'] !== $conn['default']['host'] || (string) $conn['gml']['port'] !== (string) $conn['default']['port'] ) {
			throw new Exception("Database GML harus berada di SQL Server yang sama dengan database utama untuk fitur clone-per-pegawai ini.");
		}
		return $conn['gml']['database'];
	}

	/**
	 * Copy baris dari tabel $tbl (skema sumber/default) yg cocok $whereSql ke tabel yg
	 * sama persis di database GML (nama tabel/kolom identik, hasil clone skema fitur
	 * Clone Perusahaan) lewat query cross-database T-SQL langsung di koneksi yg sama.
	 * Kolom & status identity dideteksi dinamis lewat sys.columns (bukan hardcode) -
	 * kalau tabelnya punya identity column, otomatis dibungkus SET IDENTITY_INSERT dlm
	 * SATU batch bareng INSERT-nya (dipisah jadi 2 query terpisah terbukti gagal di
	 * CloneCompany krn IDENTITY_INSERT scope-nya per sesi koneksi).
	 * Return: jumlah baris yg akhirnya ada di GML sesuai $whereSql (0 kalau tidak ada).
	 */
	private function cloneTableRows($tbl, $whereSql, $whereBinds = array())
	{
		$gmlDb = $this->getGmlDbName();

		$cols = $this->dbConn()->select("
			SELECT c.name, c.is_identity, c.is_computed
			FROM sys.columns c
			WHERE c.object_id = OBJECT_ID(?)
			ORDER BY c.column_id
		", array($tbl));

		$insertCols = array();
		$hasIdentity = false;
		foreach ($cols as $c) {
			if ( !empty($c['is_computed']) ) {
				continue;
			}
			$insertCols[] = $c['name'];
			if ( !empty($c['is_identity']) ) {
				$hasIdentity = true;
			}
		}

		if ( empty($insertCols) ) {
			throw new Exception("Tabel '{$tbl}' tidak ditemukan di skema (pastikan skema GML sudah di-clone lewat fitur Clone Perusahaan).");
		}

		$colList = '[' . implode('], [', $insertCols) . ']';
		$targetTbl = "[{$gmlDb}].dbo.[{$tbl}]";

		$insertSql = "INSERT INTO {$targetTbl} ({$colList}) SELECT {$colList} FROM [{$tbl}] WHERE {$whereSql}";

		$sql = $hasIdentity
			? "SET IDENTITY_INSERT {$targetTbl} ON; {$insertSql}; SET IDENTITY_INSERT {$targetTbl} OFF;"
			: $insertSql;

		$this->dbConn()->statement($sql, $whereBinds);

		$countRow = $this->dbConn()->selectOne("SELECT COUNT(*) AS jml FROM {$targetTbl} WHERE {$whereSql}", $whereBinds);
		return (int) $countRow['jml'];
	}

	/**
	 * Clone baris log_history.log_tables (snapshot JSON per perubahan, ditulis Event.php
	 * saat save/update/delete) yg id_header-nya ada di $logTableIds, dari koneksi 'log'
	 * (log_history_gmp_erp2) ke 'gml_log' (log_history_GML_ERP_TEST) - pasangan database
	 * TERPISAH dari default/gml, tapi tetap 1 SQL Server yg sama jadi cukup query
	 * cross-database biasa lewat koneksi 'default' yg sudah ada (bukan koneksi PDO baru).
	 * Data ini WRITE-ONLY di seluruh aplikasi (dicek: tidak ada fitur yg membacanya balik)
	 * - di-clone murni utk kelengkapan audit trail atas permintaan eksplisit, bukan krn
	 * ada tampilan di GML yg butuh ini. Return 0 (skip diam2, bukan error) kalau koneksi
	 * 'log'/'gml_log' belum lengkap dikonfigurasi.
	 */
	private function cloneLogHistoryRows($logTableIds)
	{
		$logTableIds = array_values(array_unique(array_filter($logTableIds, function($v){ return !empty($v); })));
		if ( empty($logTableIds) ) {
			return 0;
		}

		$conn = $this->config->item('connection');
		if ( empty($conn['log']['database']) || empty($conn['gml_log']['database']) ) {
			return 0;
		}
		if ( $conn['gml_log']['host'] !== $conn['log']['host'] || (string) $conn['gml_log']['port'] !== (string) $conn['log']['port'] ) {
			throw new Exception("Database 'gml_log' harus berada di SQL Server yang sama dengan koneksi 'log'.");
		}

		$sourceLogDb = $conn['log']['database'];
		$targetLogDb = $conn['gml_log']['database'];
		$inIds = implode(',', array_map('intval', $logTableIds));

		$this->dbConn()->statement("
			INSERT INTO [{$targetLogDb}].dbo.[log_tables] ([id_header], [_json])
			SELECT [id_header], [_json] FROM [{$sourceLogDb}].dbo.[log_tables]
			WHERE id_header IN ({$inIds})
		");

		$countRow = $this->dbConn()->selectOne("SELECT COUNT(*) AS jml FROM [{$targetLogDb}].dbo.[log_tables] WHERE id_header IN ({$inIds})");
		return (int) $countRow['jml'];
	}
}