<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KonfirmasiPembayaranDoc extends Public_Controller
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
				'assets/select2/js/select2.min.js',
				'assets/pembayaran/konfirmasi_pembayaran_doc/js/konfirmasi-pembayaran-doc.js'
			));
			$this->add_external_css(array(
				'assets/select2/css/select2.min.css',
				'assets/pembayaran/konfirmasi_pembayaran_doc/css/konfirmasi-pembayaran-doc.css'
			));

			$data = $this->includes;

			$data['title_menu'] = 'Konfirmasi Pembayaran Doc';

            $supplier = $this->get_supplier();
            $perusahaan = $this->get_perusahaan();

			$content['add_form'] = $this->add_form($supplier, $perusahaan);
            $content['riwayat'] = $this->riwayat($supplier, $perusahaan);

			$content['akses'] = $this->hakAkses;
			$data['view'] = $this->load->view('pembayaran/konfirmasi_pembayaran_doc/index', $content, true);

			$this->load->view($this->template, $data);
		} else {
			showErrorAkses();
		}
	}

    public function load_form()
    {
        $params = $this->input->get('params');
        $edit = $this->input->get('edit');

        $id = $params['id'];

        $content = array();
        $html = "url not found";
        
        if ( !empty($id) && $edit != 'edit' ) {
            // NOTE: view data BASTTB (ajax)
            $html = $this->detail_form( $id );
        } else if ( !empty($id) && $edit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $supplier = $this->get_supplier();
            $perusahaan = $this->get_perusahaan();
            $html = $this->edit_form($id, $supplier, $perusahaan);
        }else{
            $supplier = $this->get_supplier();
            $perusahaan = $this->get_perusahaan();
            $html = $this->add_form($supplier, $perusahaan);
        }

        echo $html;
    }

	public function get_unit()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'UN')->orderBy('nama', 'asc')->get();

        $data = null;
        if ( $d_wilayah->count() > 0 ) {
            $d_wilayah = $d_wilayah->toArray();

            foreach ($d_wilayah as $k_wil => $v_wil) {
                $nama = trim(str_replace('KAB ', '', str_replace('KOTA ', '', strtoupper($v_wil['nama']))));
                $data[ $nama.' - '.$v_wil['kode'] ] = array(
                    'nama' => $nama,
                    'kode' => $v_wil['kode']
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function get_supplier()
    {
        $m_supplier = new \Model\Storage\Supplier_model();
        $nomor_supplier = $m_supplier->select('nomor')->distinct('nomor')->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->get();

        $data = null;
        if ( $nomor_supplier->count() > 0 ) {
            $nomor_supplier = $nomor_supplier->toArray();

            foreach ($nomor_supplier as $k => $val) {
                $m_supplier = new \Model\Storage\Supplier_model();
                $d_supplier = $m_supplier->where('nomor', $val['nomor'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->orderBy('version', 'desc')->first();

                $key = strtoupper($d_supplier->nama).' - '.$d_supplier['nomor'];
                $data[ $key ] = array(
                    'nama' => strtoupper($d_supplier->nama),
                    'nomor' => $d_supplier->nomor
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function get_perusahaan()
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

    public function get_lists()
    {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $kode_supplier = null;
        $kode_perusahaan = null;

        $m_supplier = new \Model\Storage\Supplier_model();
        foreach ($params['supplier'] as $k => $val) {
            $d_supplier = null;
            if ( $val != 'all' ) {
                $d_supplier = $m_supplier->where('nomor', $val)->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->get();
            } else {
                $d_supplier = $m_supplier->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->get();
            }

            if ( !empty($d_supplier) ) {
                $d_supplier = $d_supplier->toArray();

                foreach ($d_supplier as $k_supl => $v_supl) {
                    $kode_supplier[] = $v_supl['nomor'];
                }
            }
        }

        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        foreach ($params['perusahaan'] as $k => $val) {
            $d_perusahaan = null;
            if ( $val != 'all' ) {
                $d_perusahaan = $m_perusahaan->where('kode', $val)->get();
            } else {
                $d_perusahaan = $m_perusahaan->get();
            }

            if ( !empty($d_perusahaan) ) {
                $d_perusahaan = $d_perusahaan->toArray();

                foreach ($d_perusahaan as $k_perusahaan => $v_perusahaan) {
                    $kode_perusahaan[] = $v_perusahaan['kode'];
                }
            }
        }

        $m_kpd = new \Model\Storage\KonfirmasiPembayaranDoc_model();
        $d_kpd = $m_kpd->whereBetween('tgl_bayar', [$start_date, $end_date])
                       ->whereIn('supplier', $kode_supplier)
                       ->whereIn('perusahaan', $kode_perusahaan)
                       ->with(['d_supplier', 'd_perusahaan'])->orderBy('tgl_bayar', 'desc')->get();

        if ( $d_kpd->count() > 0 ) {
            $d_kpd = $d_kpd->toArray();
        }

        $content['data'] = $d_kpd;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_doc/list_riwayat', $content, true);

        echo $html;
    }

    public function get_data_doc()
    {
    	$params = $this->input->get('params');

    	$html = $this->get_data_doc_html( $params );

        echo $html;
    }

    public function get_data_doc_html($params, $edit = null)
    {
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $m_wilayah = new \Model\Storage\Wilayah_model();

        $kode_unit = null;
        foreach ($params['kode_unit'] as $k_ku => $v_ku) {
            $d_wilayah = null;
            if ( $v_ku != 'all' ) {
                $d_wilayah = $m_wilayah->where('jenis', 'UN')->where('kode', $v_ku)->get();
            } else {
                $d_wilayah = $m_wilayah->where('jenis', 'UN')->get();
            }

            if ( !empty($d_wilayah) ) {
                $d_wilayah = $d_wilayah->toArray();

                foreach ($d_wilayah as $k_wil => $v_wil) {
                    $kode_unit[] = $v_wil['id'];
                }
            }
        }

        $m_order_doc = new \Model\Storage\OrderDoc_model();
        $d_order_doc = $m_order_doc->whereBetween('tgl_submit', [$start_date.' 00:00:00', $end_date.' 23:59:59'])->where('perusahaan', $params['perusahaan'])->where('supplier', $params['supplier'])->with(['data_perusahaan'])->get();

        $data = null;
        if ( $d_order_doc->count() > 0 ) {
            $d_order_doc = $d_order_doc->toArray();

            foreach ($d_order_doc as $k => $val) {
                $ambil = true;

                $m_terima_doc = new \Model\Storage\TerimaDoc_model();
                $d_terima_doc = $m_terima_doc->where('no_order', trim($val['no_order']))->first();

                // if ( $d_terima_doc ) {
                    $m_rs = new \Model\Storage\RdimSubmit_model();
                    $d_rs = $m_rs->where('noreg', trim($val['noreg']))->with(['mitra'])->orderBy('id', 'desc')->first();

                    if ( !empty($d_rs) ) {
                        $m_kdg = new \Model\Storage\Kandang_model();
                        $d_kdg = $m_kdg->where('id', $d_rs->kandang)->whereIn('unit', $kode_unit)->first();

                        if ( !$d_kdg ) {
                            $ambil = false;
                        }

                        if ( $ambil ) {
                            $m_wilayah = new \Model\Storage\Wilayah_model();
                            $kota_kab = $m_wilayah->where('id', $d_kdg['unit'])->first();

                            $m_kpdd = new \Model\Storage\KonfirmasiPembayaranDocDet_model();
                            $d_kpdd = $m_kpdd->where('no_order', $val['no_order'])->first();

                            $tampil = true;
                            $checked = null;
                            if ( $edit == 'edit' ) {
                                if ( $d_kpdd ) {
                                    $checked = 'checked';
                                }
                            } else {
                                if ( $d_kpdd ) {
                                    $tampil = false;
                                }
                            }

                            if ( $tampil ) {
                                $key = str_replace('-', '', substr($val['rencana_tiba'], 0, 10)).'-'.$val['no_order'].'-'.$val['noreg'];
                                $data[$key] = array(
                                    'supplier' => $val['supplier'],
                                    'tgl_order' => substr($val['rencana_tiba'], 0, 10),
                                    'id_kota_kab' => $kota_kab->kode,
                                    'kota_kab' => str_replace('Kab ', '', str_replace('Kota ', '', $kota_kab->nama)),
                                    'id_perusahaan' => $val['data_perusahaan']['kode'],
                                    'perusahaan' => $val['data_perusahaan']['perusahaan'],
                                    'no_order' => $val['no_order'],
                                    'no_peternak' => $d_rs->mitra->dMitra->nomor,
                                    'peternak' => $d_rs->mitra->dMitra->nama,
                                    'kandang' => (int) substr($val['noreg'], -2),
                                    'populasi' => $d_rs['populasi'],
                                    'harga' => $val['harga'],
                                    'total' => $val['total'],
                                    'checked' => $checked
                                );
                            }
                        }
                    }
                // }
            }
        }

        if ( !empty($data) ) {
            ksort($data);
        }

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_doc/list_order', $content, true);

        return $html;
    }

    public function riwayat($supplier, $perusahaan)
    {
        $content['supplier'] = $supplier;
        $content['perusahaan'] = $perusahaan;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_doc/riwayat', $content, true);

        return $html;
    }

	public function add_form($supplier, $perusahaan)
	{
        $content['unit'] = $this->get_unit();
        $content['supplier'] = $supplier;
		$content['perusahaan'] = $perusahaan;
		$html = $this->load->view('pembayaran/konfirmasi_pembayaran_doc/add_form', $content, true);

		return $html;
	}

    public function detail_form($id)
    {
        $m_kpd = new \Model\Storage\KonfirmasiPembayaranDoc_model();
        $d_kpd = $m_kpd->where('id', $id)->with(['d_supplier', 'd_perusahaan', 'detail', 'd_realisasi'])->first();

        $data = null;
        if ( $d_kpd ) {
            $d_kpd = $d_kpd->toArray();

            $data = $d_kpd;
        }

        $content['akses'] = $this->hakAkses;
        $content['data'] = $data;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_doc/detail_form', $content, true);

        return $html;
    }

    public function edit_form($id, $supplier, $perusahaan)
    {
        $m_kpd = new \Model\Storage\KonfirmasiPembayaranDoc_model();
        $d_kpd = $m_kpd->where('id', $id)->with(['d_supplier', 'd_perusahaan', 'detail'])->first();

        $data = null;
        $first_date = null;
        $last_date = null;

        $kode_unit = null;
        $total = 0;

        if ( $d_kpd ) {
            $d_kpd = $d_kpd->toArray();

            $data = $d_kpd;

            foreach ($data['detail'] as $k => $val) {
                $total += $val['total'];

                $tgl = substr($val['tgl_order'], 0, 10);

                if ( empty($first_date) ) {
                    $first_date = $tgl;
                } else {
                    if ( $first_date > $tgl ) {
                        $first_date = $tgl;
                    }
                }

                if ( empty($last_date) ) {
                    $last_date = $tgl;
                } else {
                    if ( $last_date < $tgl ) {
                        $last_date = $tgl;
                    }
                }

                $kode_unit[ $val['kode_unit'] ] = $val['kode_unit'];
            }
        }

        $content['data'] = $data;
        $content['first_date'] = $first_date;
        $content['last_date'] = $last_date;
        $content['kode_unit'] = $kode_unit;
        $content['total'] = $total;
        $content['unit'] = $this->get_unit();
        $content['supplier'] = $supplier;
        $content['perusahaan'] = $perusahaan;

        $params = array(
            'start_date' => $first_date,
            'end_date' => $last_date,
            'kode_unit' => $kode_unit,
            'perusahaan' => $d_kpd['perusahaan'],
            'supplier' => $d_kpd['supplier']
        );

        $content['detail'] = $this->get_data_doc_html($params, 'edit');
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_doc/edit_form', $content, true);

        return $html;
    }

    public function konfirmasi_pembayaran()
    {
        $params = $this->input->post('params');

        $nomor = null;
        $rekening = null;
        if ( isset($params['id']) ) {
            $m_kpd = new \Model\Storage\KonfirmasiPembayaranDoc_model();
            $d_kpd = $m_kpd->where('id', $params['id'])->first();

            $nomor = $d_kpd->nomor;
            $rekening = $d_kpd->rekening;
        }

        $m_perusahaan = new \Model\Storage\Perusahaan_model();
        $d_perusahaan = $m_perusahaan->where('kode', $params['perusahaan'])->orderBy('version', 'desc')->first();

        $m_supplier = new \Model\Storage\Supplier_model();
        $d_supplier = $m_supplier->where('nomor', $params['supplier'])->where('tipe', 'supplier')->where('jenis', '<>', 'ekspedisi')->with(['banks'])->orderBy('version', 'desc')->first();

        $first_date = null;
        $last_date = null;

        $total = 0;
        foreach ($params['detail'] as $k => $val) {
            $total += $val['total'];

            // $m_terima_doc = new \Model\Storage\TerimaDoc_model();
            // $d_terima_doc = $m_terima_doc->where('no_order', $val['no_order'])->first();

            // $datang = substr($d_terima_doc->datang, 0, 10);

            $m_order_doc = new \Model\Storage\OrderDoc_model();
            $d_order_doc = $m_order_doc->where('no_order', $val['no_order'])->first();

            $datang = substr($d_order_doc->rencana_tiba, 0, 10);

            if ( empty($first_date) ) {
                $first_date = $datang;
            } else {
                if ( $first_date > $datang ) {
                    $first_date = $datang;
                }
            }

            if ( empty($last_date) ) {
                $last_date = $datang;
            } else {
                if ( $last_date < $datang ) {
                    $last_date = $datang;
                }
            }
        }

        $data = array(
            'id' => isset($params['id']) ? $params['id'] : null,
            'nomor' => $nomor,
            'rekening' => $rekening,
            'total' => $total,
            'first_date' => $first_date,
            'last_date' => $last_date,
            'perusahaan' => $d_perusahaan->perusahaan,
            'no_perusahaan' => $d_perusahaan->kode,
            'supplier' => $d_supplier->nama,
            'no_supplier' => $d_supplier->nomor,
            'bank_supplier' => $d_supplier->banks
        );

        $content['data'] = $data;
        $html = $this->load->view('pembayaran/konfirmasi_pembayaran_doc/konfirmasi_pembayaran', $content, true);

        $this->result['html'] = $html;

        display_json( $this->result );

        // echo $html;
    }

    public function save()
    {
        $params = $this->input->post('params');
        try {
            $id = null;
            foreach ($params['detail'] as $k_det => $v_det) {
                $m_kpd = new \Model\Storage\KonfirmasiPembayaranDoc_model();
                $nomor = $m_kpd->getNextNomor();
    
                $m_kpd->nomor = $nomor;
                $m_kpd->tgl_bayar = $params['tgl_bayar'];
                $m_kpd->periode = trim($params['periode_docin']);
                $m_kpd->perusahaan = $params['perusahaan'];
                $m_kpd->supplier = $params['supplier'];
                $m_kpd->rekening = $params['rekening'];
                $m_kpd->total = $v_det['total'];
                // $m_kpd->total = $params['total'];
                $m_kpd->save();
    
                $id = $m_kpd->id;

                $m_kpdd = new \Model\Storage\KonfirmasiPembayaranDocDet_model();
                $m_kpdd->id_header = $id;
                $m_kpdd->tgl_order = $v_det['tgl_order'];
                $m_kpdd->kode_unit = $v_det['id_kab_kota'];
                $m_kpdd->no_order = $v_det['no_order'];
                $m_kpdd->mitra = $v_det['no_peternak'];
                $m_kpdd->kandang = $v_det['kandang'];
                $m_kpdd->populasi = $v_det['populasi'];
                $m_kpdd->harga = $v_det['harga'];
                $m_kpdd->total = $v_det['total'];
                $m_kpdd->save();

                $d_kpd = $m_kpd->where('id', $id)->first();
    
                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $d_kpd, $deskripsi_log);
            }

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $id);
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
            $id = $params['id'];

            $m_kpd = new \Model\Storage\KonfirmasiPembayaranDoc_model();
            $m_kpd->where('id', $params['id'])->update(
                array(
                    'tgl_bayar' => $params['tgl_bayar'],
                    'periode' => trim($params['periode_docin']),
                    'perusahaan' => $params['perusahaan'],
                    'supplier' => $params['supplier'],
                    'rekening' => $params['rekening'],
                    'total' => $params['total']
                )
            );

            $m_kpdd = new \Model\Storage\KonfirmasiPembayaranDocDet_model();
            $m_kpdd->where('id_header', $id)->delete();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_kpdd = new \Model\Storage\KonfirmasiPembayaranDocDet_model();
                $m_kpdd->id_header = $id;
                $m_kpdd->tgl_order = $v_det['tgl_order'];
                $m_kpdd->kode_unit = $v_det['id_kab_kota'];
                $m_kpdd->no_order = $v_det['no_order'];
                $m_kpdd->mitra = $v_det['no_peternak'];
                $m_kpdd->kandang = $v_det['kandang'];
                $m_kpdd->populasi = $v_det['populasi'];
                $m_kpdd->harga = $v_det['harga'];
                $m_kpdd->total = $v_det['total'];
                $m_kpdd->save();
            }

            $d_kpd = $m_kpd->where('id', $id)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_kpd, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $id);
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
            $id = $params['id'];

            $m_kpd = new \Model\Storage\KonfirmasiPembayaranDoc_model();
            $d_kpd = $m_kpd->where('id', $id)->first();

            $m_kpdd = new \Model\Storage\KonfirmasiPembayaranDocDet_model();
            $m_kpdd->where('id_header', $id)->delete();

            $m_kpd->where('id', $id)->delete();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_kpd, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes()
    {
        $m_kpd = new \Model\Storage\KonfirmasiPembayaranDoc_model();
        $nomor = $m_kpd->getNextNomor();

        cetak_r( $nomor );
    }
}