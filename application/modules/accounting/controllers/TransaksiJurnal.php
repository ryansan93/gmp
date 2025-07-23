<?php defined('BASEPATH') OR exit('No direct script access allowed');

class TransaksiJurnal extends Public_Controller
{
    private $pathView = 'accounting/transaksi_jurnal/';
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
				'assets/accounting/transaksi_jurnal/js/transaksi-jurnal.js'
			));
			$this->add_external_css(array(
				'assets/select2/css/select2.min.css',
				'assets/accounting/transaksi_jurnal/css/transaksi-jurnal.css'
			));

			$data = $this->includes;

			$data['title_menu'] = 'Transaksi Jurnal';

			$content['add_form'] = $this->add_form();
            $content['riwayat'] = $this->riwayat();

			$content['akses'] = $this->hakAkses;
			$data['view'] = $this->load->view($this->pathView . 'index', $content, true);

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
            $html = $this->detail_form($id);
        } else if ( !empty($id) && $edit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $html = $this->edit_form($id);
        }else{
            $html = $this->add_form();
        }

        echo $html;
    }

    public function getDataCoa()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from coa c
            order by
                c.coa asc,
                c.nama_coa asc
        ";
        $d_coa = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_coa->count() > 0 ) {
            $data = $d_coa->toArray();
        }

        return $data;
    }

    public function getFitur() {
		$m_conf = new \Model\Storage\Conf();
		$sql = "
			select 
				mf.nama_fitur,
				df.id_detfitur, 
				df.nama_detfitur 
			from detail_fitur df 
			left join
				ms_fitur mf
				on
					df.id_fitur = mf.id_fitur
			where
				mf.status = 1
			group by
				mf.nama_fitur,
				df.id_detfitur, 
				df.nama_detfitur
			order by 
				mf.nama_fitur asc,
				df.nama_detfitur asc
		";
		$d_conf = $m_conf->hydrateRaw( $sql );

		$data = null;
		if ( $d_conf->count() > 0 ) {
			$data = $d_conf->toArray();
		}

		return $data;
	}

    public function riwayat()
    {
        $data = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select max(id) as id, kode from jurnal_trans group by kode
        ";
        $d_jt_id = $m_conf->hydrateRaw( $sql );

        if ( $d_jt_id->count() > 0 ) {
            $d_jt_id = $d_jt_id->toArray();

            $id = null;
            foreach ($d_jt_id as $key => $value) {
                $id[] = $value['id'];
            }

            $m_jt = new \Model\Storage\JurnalTrans_model();
            $d_jt = $m_jt->whereIn('id', $id)->orderBy('nama', 'asc')->with(['detail', 'sumber_tujuan'])->get();

            if ( $d_jt->count() > 0 ) {
                $data = $d_jt->toArray();
            }
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'riwayat', $content, true);

        return $html;
    }

	public function add_form()
	{
        $content['fitur'] = $this->getFitur();
        $content['coa'] = $this->getDataCoa();
		$html = $this->load->view($this->pathView . 'add_form', $content, true);

		return $html;
	}

    public function detail_form($id)
    {
        $m_jt = new \Model\Storage\JurnalTrans_model();
        $d_jt = $m_jt->where('id', $id)->orderBy('nama', 'asc')->with(['fitur', 'detail', 'sumber_tujuan'])->first();

        $data = null;
        $fitur = null;
        if ( $d_jt ) {
            $d_jt = $d_jt->toArray();

            $data = $d_jt;

            if ( isset($data['fitur']) && !empty($data['fitur']) ) {
                foreach ($data['fitur'] as $k_fitur => $v_fitur) {
                    $m_conf = new \Model\Storage\JurnalTrans_model();
                    $sql = "
                        select 
                            mf.nama_fitur,
                            df.id_detfitur, 
                            df.nama_detfitur 
                        from detail_fitur df 
                        left join
                            ms_fitur mf
                            on
                                df.id_fitur = mf.id_fitur
                        where
                            df.id_detfitur = '".$v_fitur['det_fitur_id']."'
                        group by
                            mf.nama_fitur,
                            df.id_detfitur, 
                            df.nama_detfitur
                    ";
                    $d_conf = $m_conf->hydrateRaw( $sql );

                    if ( $d_conf->count() > 0 ) {
                        $d_conf = $d_conf->toArray()[0];

                        $fitur[] = $d_conf['nama_fitur'].' | '.$d_conf['nama_detfitur'];
                    }
                }
            }
        }

        $content['data'] = $data;
        $content['d_fitur'] = $fitur;
        $html = $this->load->view($this->pathView . 'detail_form', $content, true);

        return $html;
    }

    public function edit_form($id)
    {
        $m_jt = new \Model\Storage\JurnalTrans_model();
        $d_jt = $m_jt->where('id', $id)->orderBy('nama', 'asc')->with(['fitur', 'detail', 'sumber_tujuan'])->first();

        $data = null;
        $fitur = null;
        if ( $d_jt ) {
            $d_jt = $d_jt->toArray();

            $data = $d_jt;

            if ( isset($data['fitur']) && !empty($data['fitur']) ) {
                foreach ($data['fitur'] as $k_fitur => $v_fitur) {
                    $fitur[] = $v_fitur['det_fitur_id'];
                }
            }
        }

        $content['fitur'] = $this->getFitur();
        $content['coa'] = $this->getDataCoa();
        $content['data'] = $data;
        $content['d_fitur'] = $fitur;
        $html = $this->load->view($this->pathView . 'edit_form', $content, true);

        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_jt = new \Model\Storage\JurnalTrans_model();
            $kode = $m_jt->getNextId();

            $m_jt->kode = $kode;
            $m_jt->nama = $params['nama'];
            $m_jt->unit = $params['peruntukan'];
            $m_jt->kode_voucher = $params['kode_voucher'];
            $m_jt->jurnal_manual = $params['jurnal_manual'];
            $m_jt->mstatus = 1;
            $m_jt->save();

            $id = $m_jt->id;

            if ( isset($params['fitur']) && !empty($params['fitur']) ) {
                foreach ($params['fitur'] as $k_fitur => $v_fitur) {
                    $m_jtf = new \Model\Storage\JurnalTransFitur_model();
                    $m_jtf->id_header = $id;
                    $m_jtf->det_fitur_id = $v_fitur;
                    $m_jtf->save();
                }
            }

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_djt = new \Model\Storage\DetJurnalTrans_model();
                $kode_det = $m_djt->getNextIdDJT( $kode );

                $m_djt->id_header = $id;
                $m_djt->nama = $v_det['nama'];
                $m_djt->sumber = $v_det['sumber'];
                $m_djt->sumber_coa = $v_det['sumber_coa'];
                $m_djt->tujuan = $v_det['tujuan'];
                $m_djt->tujuan_coa = $v_det['tujuan_coa'];
                $m_djt->submit_periode = $v_det['submit_periode'];
                $m_djt->kode = $kode_det;
                $m_djt->save();
            }

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_jt, $deskripsi_log);

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

            $m_jt = new \Model\Storage\JurnalTrans_model();
            $d_jt = $m_jt->where('id', $id)->first();

            $m_jt->where('id', $id)->update(
                array(
                    'mstatus' => 0
                )
            );

            $m_jt = new \Model\Storage\JurnalTrans_model();
            $m_jt->kode = $d_jt->kode;
            $m_jt->nama = $params['nama'];
            $m_jt->unit = $params['peruntukan'];
            $m_jt->kode_voucher = $params['kode_voucher'];
            $m_jt->jurnal_manual = $params['jurnal_manual'];
            $m_jt->mstatus = 1;
            $m_jt->save();
            
            $id = $m_jt->id;
            
            if ( isset($params['fitur']) && !empty($params['fitur']) ) {
                foreach ($params['fitur'] as $k_fitur => $v_fitur) {
                    $m_jtf = new \Model\Storage\JurnalTransFitur_model();
                    $m_jtf->id_header = $id;
                    $m_jtf->det_fitur_id = $v_fitur;
                    $m_jtf->save();
                }
            }

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_djt = new \Model\Storage\DetJurnalTrans_model();

                if ( isset($v_det['kode']) && !empty($v_det['kode']) ) {
                    $kode_det = $v_det['kode'];
                } else {
                    $kode_det = $m_djt->getNextIdDJT( $d_jt->kode );
                }

                $m_djt->id_header = $id;
                $m_djt->nama = $v_det['nama'];
                $m_djt->sumber = $v_det['sumber'];
                $m_djt->sumber_coa = $v_det['sumber_coa'];
                $m_djt->tujuan = $v_det['tujuan'];
                $m_djt->tujuan_coa = $v_det['tujuan_coa'];
                $m_djt->submit_periode = $v_det['submit_periode'];
                $m_djt->kode = $kode_det;
                $m_djt->save();
            }

            $d_jt = $m_jt->where('id', $id)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_jt, $deskripsi_log);

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

            $m_jt = new \Model\Storage\JurnalTrans_model();
            $m_jt->where('id', $id)->update(
                array(
                    'mstatus' => 0
                )
            );

            $d_jt = $m_jt->where('id', $id)->first();

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_jt, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function tes()
    {
        // $m_conf = new \Model\Storage\Conf();
        // $sql = "
        //     select jt.kode, djt.id from det_jurnal_trans djt 
        //     right join
        //         (select min(id) as id, kode from jurnal_trans group by kode) jt
        //         on
        //             djt.id_header = jt.id
        // ";
        // $d_conf = $m_conf->hydrateRaw( $sql );

        // if ( $d_conf->count() > 0 ) {
        //     $d_conf = $d_conf->toArray();
        //     foreach ($d_conf as $key => $value) {
        //         $m_djt = new \Model\Storage\DetJurnalTrans_model();
        //         $kode = $m_djt->getNextIdDJT( $value['kode'] );

        //         $m_djt->where('id', $value['id'])->update(
        //             array(
        //                 'kode' => $kode
        //             )
        //         );
        //     }
        // }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select jt.kode, jt.nama as nama_jt, djt.id, djt.id_header, djt.nama, djt.sumber_coa, djt.tujuan_coa from det_jurnal_trans djt 
            right join
                jurnal_trans jt
                on
                    djt.id_header = jt.id
            where
                djt.kode is null
            order by
                jt.id asc,
                djt.id asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();
            foreach ($d_conf as $key => $value) {
                $m_conf = new \Model\Storage\Conf();
                $sql_tambahan = "";
                if ( $value['nama_jt'] == 'PEMBELIAN KENDARAAN' ) {
                    $sql_tambahan = "and djt.nama = '".$value['nama']."'";
                }
                $sql = "
                    select jt.kode, djt.id, djt.kode from det_jurnal_trans djt 
                    right join
                        jurnal_trans jt
                        on
                            djt.id_header = jt.id
                    where
                        djt.kode is not null and
                        jt.kode = '".$value['kode']."' and
                        djt.sumber_coa = '".$value['sumber_coa']."' and
                        djt.tujuan_coa = '".$value['tujuan_coa']."'
                        ".$sql_tambahan."
                ";
                $d_conf = $m_conf->hydrateRaw( $sql );

                $kode = null;
                if ( $d_conf->count() > 0 ) {
                    $d_conf = $d_conf->toArray()[0];
                    $kode = $d_conf['kode'];
                } else {
                    $m_djt = new \Model\Storage\DetJurnalTrans_model();
                    $kode = $m_djt->getNextIdDJT( $value['kode'] );
                }

                $m_djt = new \Model\Storage\DetJurnalTrans_model();
                $m_djt->where('id', $value['id'])->update(
                    array(
                        'kode' => $kode
                    )
                );
            }
        }

        // $m_jt = new \Model\Storage\JurnalTrans_model();
        // $kode = $m_jt->getNextId();

        // cetak_r( $kode );
    }
}