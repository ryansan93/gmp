<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RekeningTampungan extends Public_Controller
{
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
				"assets/jquery/list.min.js",
				'assets/select2/js/select2.min.js',
				'assets/pembayaran/rekening_tampungan/js/rekening-tampungan.js'
			));
			$this->add_external_css(array(
				'assets/select2/css/select2.min.css',
				'assets/pembayaran/rekening_tampungan/css/rekening-tampungan.css'
			));

			$data = $this->includes;

			$data['title_menu'] = 'Rekening Tampungan';

			$content['rekening_masuk'] = $this->load->view('pembayaran/rekening_tampungan/rekening_masuk', null, true);
			$content['rekening_keluar'] = $this->load->view('pembayaran/rekening_tampungan/rekening_keluar', null, true);
			$content['akses'] = $this->hakAkses;
			$data['view'] = $this->load->view('pembayaran/rekening_tampungan/index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

	public function getPerusahaan()
    {
        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $kode_perusahaan = $m_perusahaan->select('kode')->distinct('kode')->get();

        $data = null;
        if ( $kode_perusahaan->count() > 0 ) {
            $kode_perusahaan = $kode_perusahaan->toArray();

            foreach ($kode_perusahaan as $k => $val) {
                $m_perusahaan = new \Model\Storage\Perusahaan_model();
                $d_perusahaan = $m_perusahaan->where('kode', $val['kode'])->orderBy('version', 'desc')->first();

                $key = strtoupper($d_perusahaan->perusahaan).' - '.$d_perusahaan['kode'];
                $data[ $key ] = array(
                    'nama' => strtoupper($d_perusahaan->perusahaan),
                    'kode' => $d_perusahaan->kode
                );
            }

            ksort($data);
        }

        return $data;
    }

	public function getListsRm()
	{
		$params = $this->input->get('params');

		$startDate = $params['startDate'];
		$endDate = $params['endDate'];

		$m_rtm = new \Model\Storage\RekeningTampunganMasuk_model();
		$d_rtm = $m_rtm->whereBetween('tanggal', [$startDate, $endDate])->with(['d_perusahaan'])->get();

		$data = null;
		if ( $d_rtm->count() > 0 ) {
			$data = $d_rtm->toArray();
		}

		$content['data'] = $data;
		$html = $this->load->view('pembayaran/rekening_tampungan/listRm', $content, true);

		echo $html;
	}

	public function addFormRm()
	{
		$content['perusahaan'] = $this->getPerusahaan();
		$html = $this->load->view('pembayaran/rekening_tampungan/addFormRm', $content, true);

		echo $html;
	}

	public function viewFormRm()
	{
		$kode = $this->input->get('kode');

		$m_rtm = new \Model\Storage\RekeningTampunganMasuk_model();
		$d_rtm = $m_rtm->where('kode', $kode)->with(['d_perusahaan'])->first();

		$content['data'] = $d_rtm->toArray();
		$html = $this->load->view('pembayaran/rekening_tampungan/viewFormRm', $content, true);

		echo $html;
	}

	public function editFormRm()
	{
		$kode = $this->input->get('kode');

		$m_rtm = new \Model\Storage\RekeningTampunganMasuk_model();
		$d_rtm = $m_rtm->where('kode', $kode)->first();

		$content['perusahaan'] = $this->getPerusahaan();
		$content['data'] = $d_rtm->toArray();
		$html = $this->load->view('pembayaran/rekening_tampungan/editFormRm', $content, true);

		echo $html;
	}

	public function saveRm()
	{
		$data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

		try {
			$file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];

	            if ($isMoved) {
	                $file_name = $moved['name'];
	                $path_name = $moved['path'];
	            }
            }

            $m_rtm = new \Model\Storage\RekeningTampunganMasuk_model();
            $kode = $m_rtm->getNextId();

            $m_rtm->kode = $kode;
			$m_rtm->tanggal = $data['tanggal'];
			$m_rtm->perusahaan = $data['perusahaan'];
			$m_rtm->nominal = $data['nominal'];
			$m_rtm->lampiran = $path_name;
			$m_rtm->keterangan = $data['keterangan'];
			$m_rtm->save();

            $d_rtm = $m_rtm->where('kode', $kode)->first();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_rtm, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

		display_json( $this->result );
	}

	public function editRm()
	{
		$data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

		try {
			$file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];

	            if ($isMoved) {
	                $file_name = $moved['name'];
	                $path_name = $moved['path'];
	            }
            }

            $kode = $data['kode'];

            $m_rtm = new \Model\Storage\RekeningTampunganMasuk_model();
            $m_rtm->where('kode', $kode)->update(
            	array(
            		'tanggal' => $data['tanggal'],
            		'perusahaan' => $data['perusahaan'],
					'nominal' => $data['nominal'],
					'lampiran' => $path_name,
					'keterangan' => $data['keterangan']
            	)
            );

            $d_rtm = $m_rtm->where('kode', $kode)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_rtm, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di update.';
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

		display_json( $this->result );
	}

	public function deleteRm()
	{
        $kode = $this->input->post('kode');

		try {
            $m_rtm = new \Model\Storage\RekeningTampunganMasuk_model();
            $d_rtm = $m_rtm->where('kode', $kode)->first();

            $m_rtm->where('kode', $kode)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_rtm, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

		display_json( $this->result );
	}

	public function getListsRk()
	{
		$params = $this->input->get('params');

		$startDate = $params['startDate'];
		$endDate = $params['endDate'];

		$m_rtm = new \Model\Storage\RekeningTampunganKeluar_model();
		$d_rtm = $m_rtm->whereBetween('tanggal', [$startDate, $endDate])->with(['d_pelanggan', 'd_perusahaan'])->get();

		$data = null;
		if ( $d_rtm->count() > 0 ) {
			$data = $d_rtm->toArray();
		}

		$content['data'] = $data;
		$html = $this->load->view('pembayaran/rekening_tampungan/listRk', $content, true);

		echo $html;
	}

	public function getDataPelanggan()
	{
		$m_plg = new \Model\Storage\Pelanggan_model();
		$sql = "
			select plg.*, l_kab.nama as kab_kota from pelanggan plg
			left join
				(select max(id) as id, nomor from pelanggan where mstatus = 1 group by nomor) plg2
				on
					plg.id = plg2.id 
			left join
				lokasi l_kec
				on
					plg.alamat_kecamatan = l_kec.id
			left join
				lokasi l_kab
				on
					l_kec.induk = l_kab.id
			where
				plg.mstatus = 1 and
				plg.tipe = 'pelanggan'
		";
		$d_plg = $m_plg->hydrateRaw($sql);

		$data = null;
		if ( $d_plg->count() > 0 ) {
			$d_plg = $d_plg->toArray();

			foreach ($d_plg as $k_plg => $v_plg) {
				$kota_kab = str_replace('Kota ', '', str_replace('Kab ', '', $v_plg['kab_kota']));
				$key = $v_plg['nama'].'|'.$v_plg['nomor'];
				$data[$key] = $v_plg;
				$data[$key]['kab_kota'] = $kota_kab;

				ksort($data);
			}
		}

		return $data;
	}

	public function addFormRk()
	{
		$content['perusahaan'] = $this->getPerusahaan();
		$content['pelanggan'] = $this->getDataPelanggan();
		$html = $this->load->view('pembayaran/rekening_tampungan/addFormRk', $content, true);

		echo $html;
	}

	public function viewFormRk()
	{
		$kode = $this->input->get('kode');

		$m_rtm = new \Model\Storage\RekeningTampunganKeluar_model();
		$d_rtm = $m_rtm->where('kode', $kode)->with(['d_pelanggan', 'd_perusahaan'])->first();

		$content['data'] = $d_rtm->toArray();
		$html = $this->load->view('pembayaran/rekening_tampungan/viewFormRk', $content, true);

		echo $html;
	}

	public function editFormRk()
	{
		$kode = $this->input->get('kode');

		$m_rtm = new \Model\Storage\RekeningTampunganKeluar_model();
		$d_rtm = $m_rtm->where('kode', $kode)->first();

		$content['perusahaan'] = $this->getPerusahaan();
		$content['pelanggan'] = $this->getDataPelanggan();
		$content['data'] = $d_rtm->toArray();
		$html = $this->load->view('pembayaran/rekening_tampungan/editFormRk', $content, true);

		echo $html;
	}

	public function saveRk()
	{
		$data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

		try {
			$file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];

	            if ($isMoved) {
	                $file_name = $moved['name'];
	                $path_name = $moved['path'];
	            }
            }

            $m_rtk = new \Model\Storage\RekeningTampunganKeluar_model();
            $kode = $m_rtk->getNextId();

            $m_rtk->kode = $kode;
			$m_rtk->tanggal = $data['tanggal'];
			$m_rtk->perusahaan = $data['perusahaan'];
			$m_rtk->no_pelanggan = $data['pelanggan'];
			$m_rtk->nominal = $data['nominal'];
			$m_rtk->lampiran = $path_name;
			$m_rtk->keterangan = $data['keterangan'];
			$m_rtk->jenis = $data['jenis'];
			$m_rtk->save();

			if ( $data['jenis'] == 1 ) {
				$m_sp = new \Model\Storage\SaldoPelanggan_model();
				$now = $m_sp->getDate();

				$d_sp = $m_sp->where('no_pelanggan', $data['pelanggan'])->where('perusahaan', $data['perusahaan'])->orderBy('id', 'desc')->first();

				$saldo = $data['nominal'];
				if ( $d_sp ) {
					$saldo += $d_sp->saldo;
				}

				$m_sp->jenis_saldo = 'D';
				$m_sp->no_pelanggan = $data['pelanggan'];
				$m_sp->id_trans = $kode;
				$m_sp->tgl_trans = $now['waktu'];
				$m_sp->jenis_trans = 'rekening_tampungan_keluar';
				$m_sp->nominal = $data['nominal'];
				$m_sp->saldo = $saldo;
				$m_sp->perusahaan = $data['perusahaan'];
				$m_sp->save();
			}

            $d_rtk = $m_rtk->where('kode', $kode)->first();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_rtk, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

		display_json( $this->result );
	}

	public function editRk()
	{
		$data = json_decode($this->input->post('data'),TRUE);
        $files = isset($_FILES['files']) ? $_FILES['files'] : [];

		try {
			$file_name = $path_name = null;
            $isMoved = 0;
            if (!empty($files)) {
                $moved = uploadFile($files);
                $isMoved = $moved['status'];

	            if ($isMoved) {
	                $file_name = $moved['name'];
	                $path_name = $moved['path'];
	            }
            }

            $kode = $data['kode'];

            $m_rtk = new \Model\Storage\RekeningTampunganKeluar_model();
            $d_rtk_prev = $m_rtk->where('kode', $kode)->first();

            $m_rtk->where('kode', $kode)->update(
            	array(
            		'tanggal' => $data['tanggal'],
            		'perusahaan' => $data['perusahaan'],
					'no_pelanggan' => $data['pelanggan'],
					'nominal' => $data['nominal'],
					'lampiran' => $path_name,
					'keterangan' => $data['keterangan'],
					'jenis' => $data['jenis']
            	)
            );

            $d_rtk = $m_rtk->where('kode', $kode)->first();

            $m_sp = new \Model\Storage\SaldoPelanggan_model();
			$now = $m_sp->getDate();

			$selisih = ($d_rtk->nominal == $d_rtk_prev->nominal) ? $d_rtk->nominal : $d_rtk->nominal - $d_rtk_prev->nominal;
            if ( $d_rtk_prev->no_pelanggan == $d_rtk->no_pelanggan && $d_rtk_prev->perusahaan == $d_rtk->perusahaan ) {
            	$d_sp = $m_sp->where('no_pelanggan', $d_rtk->no_pelanggan)->where('perusahaan', $d_rtk->perusahaan)->orderBy('id', 'desc')->first();

            	$jenis_saldo = null;
            	$saldo = !empty($d_sp) ? $d_sp->saldo : 0;
            	if ( $d_rtk->jenis == $d_rtk_prev->jenis ) {
					if ( $selisih > 0 ) {
						$saldo += abs($selisih);
						$jenis_saldo = 'D';
					} else {
						$saldo -= abs($selisih);
						$jenis_saldo = 'K';
					}
            	} else {
            		if ( $d_rtk->jenis == 1 ) {
            			$saldo += $d_rtk_prev->nominal;
						$jenis_saldo = 'D';
            		} else {
            			$saldo -= $d_rtk_prev->nominal;
						$jenis_saldo = 'K';
            		}
            	}

				$m_sp = new \Model\Storage\SaldoPelanggan_model();
				$m_sp->jenis_saldo = $jenis_saldo;
				$m_sp->no_pelanggan = $data['pelanggan'];
				$m_sp->id_trans = $kode;
				$m_sp->tgl_trans = $now['waktu'];
				$m_sp->jenis_trans = 'reverse_rekening_tampungan_keluar';
				$m_sp->nominal = $selisih;
				$m_sp->saldo = ($saldo > 0) ? $saldo : 0;
				$m_sp->perusahaan = $data['perusahaan'];
				$m_sp->save();
            } else {
            	$m_sp_prev = new \Model\Storage\SaldoPelanggan_model();
            	$d_sp_prev = $m_sp_prev->where('no_pelanggan', $d_rtk_prev->no_pelanggan)->where('perusahaan', $d_rtk_prev->perusahaan)->orderBy('id', 'desc')->first();

            	$jenis_saldo_prev = 'K';
            	$saldo_prev = !empty($d_sp_prev) ? $d_sp_prev->saldo - $selisih : 0;

				$m_sp_prev->jenis_saldo = $jenis_saldo_prev;
				$m_sp_prev->no_pelanggan = $d_rtk_prev->no_pelanggan;
				$m_sp_prev->id_trans = $kode;
				$m_sp_prev->tgl_trans = $now['waktu'];
				$m_sp_prev->jenis_trans = 'reverse_rekening_tampungan_keluar';
				$m_sp_prev->nominal = abs($selisih);
				$m_sp_prev->saldo = ($saldo_prev > 0) ? $saldo_prev : 0;
				$m_sp_prev->perusahaan = $d_rtk_prev->perusahaan;
				$m_sp_prev->save();

				if ( $d_rtk->jenis == 1 ) {
	            	$m_sp = new \Model\Storage\SaldoPelanggan_model();
					$d_sp = $m_sp->where('no_pelanggan', $d_rtk->no_pelanggan)->where('perusahaan', $d_rtk->perusahaan)->orderBy('id', 'desc')->first();

	            	$jenis_saldo = 'D';
	            	$saldo = !empty($d_sp) ? $d_sp->saldo : 0;
	            	$saldo += $selisih;

					$m_sp->jenis_saldo = $jenis_saldo;
					$m_sp->no_pelanggan = $d_rtk->no_pelanggan;
					$m_sp->id_trans = $kode;
					$m_sp->tgl_trans = $now['waktu'];
					$m_sp->jenis_trans = 'reverse_rekening_tampungan_keluar';
					$m_sp->nominal = (int) $selisih;
					$m_sp->saldo = ($saldo > 0) ? $saldo : 0;
					$m_sp->perusahaan = $d_rtk->perusahaan;
					$m_sp->save();
				}
            }

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_rtk, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di update.';
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

		display_json( $this->result );
	}

	public function deleteRk()
	{
        $kode = $this->input->post('kode');

		try {
            $m_rtk = new \Model\Storage\RekeningTampunganKeluar_model();
            $d_rtk = $m_rtk->where('kode', $kode)->first();

            if ( $d_rtk->jenis == 1 ) {
	            $selisih = $d_rtk->nominal;

	            $m_sp = new \Model\Storage\SaldoPelanggan_model();
	            $now = $m_sp->getDate();

	            $d_sp = $m_sp->where('no_pelanggan', $d_rtk->no_pelanggan)->where('perusahaan', $d_rtk->perusahaan)->orderBy('id', 'desc')->first();

	        	$jenis_saldo = 'K';
	        	$saldo = !empty($d_sp) ? $d_sp->saldo - $selisih : 0;

				$m_sp->jenis_saldo = $jenis_saldo;
				$m_sp->no_pelanggan = $d_rtk->no_pelanggan;
				$m_sp->id_trans = $kode;
				$m_sp->tgl_trans = $now['waktu'];
				$m_sp->jenis_trans = 'reverse_rekening_tampungan_keluar';
				$m_sp->nominal = $selisih;
				$m_sp->saldo = ($saldo > 0) ? $saldo : 0;
				$m_sp->perusahaan = $d_rtk->perusahaan;
				$m_sp->save();
            }

            $m_rtk->where('kode', $kode)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_rtk, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
		} catch (Exception $e) {
			$this->result['message'] = $e->getMessage();
		}

		display_json( $this->result );
	}

	public function update_nilai_kirim_voadip()
	{
		$array = array(
array('2022-09-06', 'FOSBACT PLUS @ 1 KG', '21151140501',  1 , 582750.00, 611850.00),
array('2022-09-06', 'CHICKOFIT @ 1 L', '21151140501',  2 , 240000.00, 252000.00),
array('2022-09-06', 'DESGRIN @ 1 L', '21151140501',  1 , 96316.29, 101100.00),
array('2022-09-06', 'GUMBONAL @ 100 GR', '21151140501',  10 , 135300.00, 142000.00),
array('2022-09-06', 'ASTRESVIT @ 100 GR', '21151140501',  10 , 146115.34, 153000.00),
array('2022-09-06', 'CYPROTYLOGRIN @ 100 GR', '21151140501',  10 , 312850.00, 328000.00),
array('2022-09-06', 'AGRIVIT POWER @ 100 GR', '21151140501',  10 , 141807.01, 148500.00),
		);

		$kode_unit = 'MGT';
		$kode_gudang = 18;

		$data = null;
		foreach ($array as $k_arr => $v_arr) {
			$tgl_kirim = $v_arr[0];
			$nama = $v_arr[1];
			$noreg = $v_arr[2];
			$jumlah = $v_arr[3];
			$nilai_beli = $v_arr[4];
			$nilai_jual = $v_arr[5];

			$key = $tgl_kirim.' | '.$nama.' | '.$noreg;

			if ( !isset( $data[$key] ) ) {
				$data[ $key ] = $v_arr;
			} else {
				$data[ $key ][4] += $nilai_beli;
				$data[ $key ][5] += $nilai_jual;
			}
		}

		$barang_kosong = null;
		foreach ($data as $k_arr => $v_arr) {
			$tgl_kirim = $v_arr[0];
			$nama = $v_arr[1];
			$noreg = $v_arr[2];
			$jumlah = $v_arr[3];
			$nilai_beli = $v_arr[4];
			$nilai_jual = $v_arr[5];

			$m_brg = new \Model\Storage\Barang_model();
			$d_brg = $m_brg->where('nama', 'like', '%'.trim($nama).'%')->orderBy('id', 'desc')->first();

			if ( $d_brg ) {
				$m_kv = new \Model\Storage\KirimVoadip_model();
	        	$d_kv = $m_kv->where('tgl_kirim', $tgl_kirim)->where('tujuan', trim($noreg))->get();

	        	if ( $d_kv->count() > 0 ) {
	        		$d_kv = $d_kv->toArray();
	        		foreach ($d_kv as $k_kv => $v_kv) {
		        		$m_dkv = new \Model\Storage\KirimVoadipDetail_model();
		        		$d_dkv = $m_dkv->where('id_header', $v_kv['id'])->where('item', trim($d_brg->kode))->where('jumlah', $jumlah)->first();

		        		if ( $d_dkv ) {
		        			$m_dkv->where('id', $d_dkv->id)->update(
		        				array(
		        					'nilai_beli' => $nilai_beli,
		        					'nilai_jual' => $nilai_jual
		        				)
		        			);
		        		} else {
		        			$m_kirim_apakn_detail = new \Model\Storage\KirimVoadipDetail_model();
		                    $m_kirim_apakn_detail->id_header = $v_kv['id'];
		                    $m_kirim_apakn_detail->item = trim($d_brg->kode);
		                    $m_kirim_apakn_detail->jumlah = $jumlah;
		                    $m_kirim_apakn_detail->kondisi = 'BAIK';
		                    $m_kirim_apakn_detail->save();
		        		}
	        		}
	        	} else {
	        		$m_kirim_voadip = new \Model\Storage\KirimVoadip_model();

	        		$no_order = $m_kirim_voadip->getNextIdOrder('OP/'.$kode_unit);
                	$no_sj = $m_kirim_voadip->getNextIdSj('SJ/'.$kode_unit);

	        		$m_kirim_voadip->tgl_trans = $tgl_kirim.' 00:00:00';
	                $m_kirim_voadip->tgl_kirim = $tgl_kirim;
	                $m_kirim_voadip->no_order = $no_order;
	                $m_kirim_voadip->jenis_kirim = 'opkg';
	                $m_kirim_voadip->asal = $kode_gudang;
	                $m_kirim_voadip->jenis_tujuan = 'peternak';
	                $m_kirim_voadip->tujuan = trim($noreg);
	                $m_kirim_voadip->ekspedisi = 'INJEK';
	                $m_kirim_voadip->no_polisi = 'INJEK';
	                $m_kirim_voadip->sopir = 'INJEK';
	                $m_kirim_voadip->no_sj = $no_sj;
	                $m_kirim_voadip->ongkos_angkut = 0;
	                $m_kirim_voadip->save();

	                $id_header = $m_kirim_voadip->id;

	                $m_kirim_apakn_detail = new \Model\Storage\KirimVoadipDetail_model();
                    $m_kirim_apakn_detail->id_header = $id_header;
                    $m_kirim_apakn_detail->item = trim($d_brg->kode);
                    $m_kirim_apakn_detail->jumlah = $jumlah;
                    $m_kirim_apakn_detail->kondisi = 'BAIK';
                    $m_kirim_apakn_detail->save();
	        	}
			} else {
				$barang_kosong[$nama] = array(
					'nama' => $nama
				);
			}
		}

		if ( !empty($barang_kosong) ) {
			ksort($barang_kosong);
			cetak_r( $barang_kosong );
		} else {
			cetak_r( 'Success' );
		}
	}
}