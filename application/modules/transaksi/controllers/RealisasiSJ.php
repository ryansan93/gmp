<?php defined('BASEPATH') or exit('No direct script access allowed');

class RealisasiSJ extends Public_Controller
{
    private $pathView = 'transaksi/realisasi_sj/';
    private $url;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
    }

    public function index()
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
              'assets/toastr/js/toastr.js',
              'assets/transaksi/realisasi_sj/js/realisasi-sj.js'
            ));
            $this->add_external_css(array(
              'assets/toastr/css/toastr.css',
              'assets/transaksi/realisasi_sj/css/realisasi-sj.css'
            ));
            $data = $this->includes;

            $content['akses'] = $akses;
            $content['title_panel'] = 'Realisasi SJ';
            $content['unit'] = $this->get_unit();

            $a_content['akses'] = $akses;
            $content['add_form'] = $this->load->view($this->pathView . 'add_form', $a_content, TRUE);

            // Load Indexx
            $data['title_menu'] = 'Realisasi SJ';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_unit()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'UN')->orderBy('nama', 'asc')->get()->toArray();

        return $d_wilayah;
    }

    public function get_mitra()
    {
        $params = $this->input->post( 'params' );

        $data = null;
        $data_mitra = null;

        $unit = $params['unit'];
        $tgl_panen = $params['tgl_panen'];

        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('id', $unit)->first();

        $id_unit = $m_wilayah->select('id')->where('kode', $d_wilayah['kode'])->get()->toArray();

        $m_rpah = new \Model\Storage\Rpah_model();
        // $d_rpah = $m_rpah->where('id_unit', $unit)->where('tgl_panen', $tgl_panen)->whereIn('g_status', [2, 3])->with(['det_rpah'])->get();
        $d_rpah = $m_rpah->whereIn('id_unit', $id_unit)->where('tgl_panen', $tgl_panen)->with(['det_rpah_without_konfir'])->get();

        // cetak_r( $id_unit, 1 );

        if ( !empty( $d_rpah ) ) {
            $data = $d_rpah->toArray();
            foreach ($data as $k => $val) {
                foreach ($val['det_rpah_without_konfir'] as $k_det => $v_det) {
                    $ada = 0;
                    if ( !empty($data_mitra) ) {
                        foreach ($data_mitra as $k => $val) {
                            if ( $val['noreg'] == $v_det['noreg'] ) {
                                $ada = 1;
                            }
                        }
                    }

                    if ( $ada == 0 ) {
                        // $m_ts = new \Model\Storage\TutupSiklus_model();
                        // $d_ts = $m_ts->where('noreg', $v_det['noreg'])->first();

                        // if ( !$d_ts ) {
                            $m_rs = new \Model\Storage\RdimSubmit_model();
                            $d_rs = $m_rs->where('noreg', $v_det['noreg'])->with(['mitra'])->first();

                            if ( !$d_rs ) {
                                cetak_r( $v_det['noreg'] );
                            }

                            $m_kandang = new \Model\Storage\Kandang_model();
                            $d_kandang = $m_kandang->where('id', $d_rs->kandang)->with(['d_unit'])->first();

                            $data_mitra[] = array(
                                'noreg' => $v_det['noreg'],
                                'kode_unit' => $d_kandang->d_unit->kode,
                                'mitra' => $d_rs->mitra->dMitra->nama
                            );
                        // }
                    }
                }
            }
        }

        $this->result['status'] = 1;
        $this->result['content'] = $data_mitra;

        display_json($this->result);
    }

    public function get_data()
    {
        $params = $this->input->get( 'params' );

        $unit = $params['unit'];
        $tgl_panen = $params['tgl_panen'];

        $noreg = $params['noreg'];
        $resubmit = $params['resubmit'];

        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('id', $unit)->first();

        $d_rpah = null;
        $d_real_sj = null;
        if ( !empty($unit) && $tgl_panen != 'Invalid date' ) {
            $id_wilayah = $m_wilayah->select('id')->where('kode', $d_wilayah->kode)->get()->toArray();

            $m_rpah = new \Model\Storage\Rpah_model();
            $_d_rpah = $m_rpah->whereIn('id_unit', $id_wilayah)->where('tgl_panen', $tgl_panen)->with(['det_rpah_real_sj'])->get();

            if ( $_d_rpah->count() ) {
                $d_rpah = $_d_rpah->toArray();
            }

            $m_real_sj = new \Model\Storage\RealSJ_model();
            $d_real_sj = $m_real_sj->where('noreg', $noreg)->where('tgl_panen', $tgl_panen)->where('g_status', 1)->with(['logs'])->orderBy('id', 'desc')->first();
            if ( !$d_real_sj ) {
                $d_real_sj = $m_real_sj->where('noreg', $noreg)->where('tgl_panen', $tgl_panen)->with(['logs'])->orderBy('id', 'desc')->first();
            }
        }

        $akses = hakAkses($this->url);
        $content['akses'] = $akses;
        $content['noreg'] = $noreg;
        $content['data_penjualan'] = $d_rpah;
        $content['data_real_sj'] = !empty($d_real_sj) ? $d_real_sj->toArray() : null;
        $content['jenis_ayam'] = $this->config->item('jenis_ayam');

        if ( !empty($d_real_sj) && empty($resubmit) ) {
            $content['data'] = $d_real_sj;
            $html = $this->load->view($this->pathView . 'view_form', $content, TRUE);
        } else if ( !empty($d_real_sj) && !empty($resubmit) ) {
            $content['data'] = $d_real_sj;
            $html = $this->load->view($this->pathView . 'edit_form', $content, TRUE);
        } else if ( empty($d_real_sj) && empty($resubmit) ){
            $content['data'] = null;
            $html = $this->load->view($this->pathView . 'add_form', $content, TRUE);
        }

        echo $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_real_sj = new \Model\Storage\RealSJ_model();
            $m_real_sj->id_unit = $params['id_unit'];
            $m_real_sj->unit = $params['unit'];
            $m_real_sj->tgl_panen = $params['tgl_panen'];
            $m_real_sj->noreg = $params['noreg'];
            $m_real_sj->ekor = $params['ekor'];
            $m_real_sj->kg = $params['kg'];
            $m_real_sj->bb = $params['bb'];
            $m_real_sj->tara = $params['tara'];
            $m_real_sj->netto_ekor = $params['netto_ekor'];
            $m_real_sj->netto_kg = $params['netto_kg'];
            $m_real_sj->netto_bb = $params['netto_bb'];
            $m_real_sj->save();

            $id_real_sj = $m_real_sj->id;

            if ( !empty($params['detail']) ) {
                foreach ($params['detail'] as $k => $val) {
                    foreach ($val['realisasi'] as $k_real => $v_real) {
                        $m_det_real_sj = new \Model\Storage\DetRealSJ_model();
                        $m_det_real_sj->id = $m_det_real_sj->getNextIdentity();
                        $m_det_real_sj->id_header = $id_real_sj;
                        $m_det_real_sj->id_det_rpah = $val['id_det_rpah'];
                        $m_det_real_sj->no_pelanggan = $val['no_pelanggan'];
                        $m_det_real_sj->pelanggan = $val['pelanggan'];
                        $m_det_real_sj->tonase = $v_real['tonase'];
                        $m_det_real_sj->ekor = $v_real['ekor'];
                        $m_det_real_sj->bb = $v_real['bb'];
                        $m_det_real_sj->harga = $v_real['harga'];
                        $m_det_real_sj->no_do = $val['no_do'];
                        $m_det_real_sj->no_sj = $val['no_sj'];
                        $m_det_real_sj->jenis_ayam = $v_real['jenis_ayam'];
                        $m_det_real_sj->no_nota = $v_real['no_nota'];
                        $m_det_real_sj->save();
                    }
                }
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    drs.no_sj,
                    REPLACE(drs.no_sj, 'SJ', 'INV') as no_inv,
                    sum(drs.tonase) as tonase,
                    sum(drs.ekor) as ekor,
                    (sum(drs.tonase) / sum(drs.ekor)) as bb,
                    sum(drs.tonase * drs.harga) as total
                from det_real_sj drs
                where
                    drs.id_header = ".$id_real_sj."
                group by
                    drs.no_sj
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
            if ( $d_conf->count() > 0 ) {
                $d_drsi = $d_conf->toArray();

                foreach ($d_drsi as $k_drsi => $v_drsi) {
                    $m_drsi = new \Model\Storage\DetRealSjInv_model();
                    $m_drsi->no_sj = $v_drsi['no_sj'];
                    $m_drsi->no_inv = $v_drsi['no_inv'];
                    $m_drsi->tonase = $v_drsi['tonase'];
                    $m_drsi->ekor = $v_drsi['ekor'];
                    $m_drsi->bb = $v_drsi['bb'];
                    $m_drsi->total = $v_drsi['total'];
                    $m_drsi->save();
                }
            }

            // $m_conf = new \Model\Storage\Conf();
            // $sql = "exec insert_jurnal NULL, NULL, NULL, 0, 'real_sj', ".$id_real_sj.", NULL, 1";
            // $d_conf = $m_conf->hydrateRaw( $sql );

            Modules::run( 'base/InsertJurnal/exec', $this->url, $id_real_sj, null, 1);

            $d_real_sj = $m_real_sj->where('id', $id_real_sj)->with(['det_real_sj'])->first();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_real_sj, $deskripsi_log);
            
            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil disimpan';
            $this->result['content'] = array('id' => $id_real_sj);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit()
    {
        $params = $this->input->post('params');

        try {
            // cetak_r( $params, 1 );

            $m_real_sj = new \Model\Storage\RealSJ_model();

            $m_real_sj->where('id', $params['id_real_sj'])->update(
                array(
                    'ekor' => $params['ekor'],
                    'kg' => $params['kg'],
                    'bb' => $params['bb'],
                    'tara' => $params['tara'],
                    'netto_ekor' => $params['netto_ekor'],
                    'netto_kg' => $params['netto_kg'],
                    'netto_bb' => $params['netto_bb']
                )
            );

            $id_real_sj = $params['id_real_sj'];

            $m_det_real_sj = new \Model\Storage\DetRealSJ_model();
            $m_det_real_sj->where('id_header', $id_real_sj)->delete();

            if ( !empty($params['detail']) ) {
                foreach ($params['detail'] as $k => $val) {
                    foreach ($val['realisasi'] as $k_real => $v_real) {
                        $m_det_real_sj = new \Model\Storage\DetRealSJ_model();

                        $id = (isset($v_real['id']) && !empty($v_real['id'])) ? $v_real['id'] : $m_det_real_sj->getNextIdentity();
                        // if ( empty($id) ) {
                        //     $id = $m_det_real_sj->getNextIdentity();
                        // }

                        $m_drsi = new \Model\Storage\DetRealSjInv_model();
                        $m_drsi->where('no_sj', $val['no_sj'])->delete();

                        $m_det_real_sj->id = $id;
                        $m_det_real_sj->id_header = $id_real_sj;
                        $m_det_real_sj->id_det_rpah = $val['id_det_rpah'];
                        $m_det_real_sj->no_pelanggan = $val['no_pelanggan'];
                        $m_det_real_sj->pelanggan = $val['pelanggan'];
                        $m_det_real_sj->tonase = $v_real['tonase'];
                        $m_det_real_sj->ekor = $v_real['ekor'];
                        $m_det_real_sj->bb = $v_real['bb'];
                        $m_det_real_sj->harga = $v_real['harga'];
                        $m_det_real_sj->no_do = $val['no_do'];
                        $m_det_real_sj->no_sj = $val['no_sj'];
                        $m_det_real_sj->jenis_ayam = $v_real['jenis_ayam'];
                        $m_det_real_sj->no_nota = $v_real['no_nota'];
                        $m_det_real_sj->save();
                    }
                }
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    drs.no_sj,
                    REPLACE(drs.no_sj, 'SJ', 'INV') as no_inv,
                    sum(drs.tonase) as tonase,
                    sum(drs.ekor) as ekor,
                    (sum(drs.tonase) / sum(drs.ekor)) as bb,
                    sum(drs.tonase * drs.harga) as total
                from det_real_sj drs
                where
                    drs.id_header = ".$id_real_sj."
                group by
                    drs.no_sj
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );
            if ( $d_conf->count() > 0 ) {
                $d_drsi = $d_conf->toArray();

                foreach ($d_drsi as $k_drsi => $v_drsi) {
                    $m_drsi = new \Model\Storage\DetRealSjInv_model();
                    $m_drsi->no_sj = $v_drsi['no_sj'];
                    $m_drsi->no_inv = $v_drsi['no_inv'];
                    $m_drsi->tonase = $v_drsi['tonase'];
                    $m_drsi->ekor = $v_drsi['ekor'];
                    $m_drsi->bb = $v_drsi['bb'];
                    $m_drsi->total = $v_drsi['total'];
                    $m_drsi->save();
                }
            }

            // $m_conf = new \Model\Storage\Conf();
            // $sql = "exec insert_jurnal NULL, NULL, NULL, 0, 'real_sj', ".$id_real_sj.", ".$id_real_sj.", 2";
            // $d_conf = $m_conf->hydrateRaw( $sql );

            Modules::run( 'base/InsertJurnal/exec', $this->url, $id_real_sj, $id_real_sj, 2);

            $d_real_sj = $m_real_sj->where('id', $id_real_sj)->with(['det_real_sj'])->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_real_sj, $deskripsi_log);
            
            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di ubah';
            $this->result['content'] = array('id' => $id_real_sj);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete()
    {
        $id = $this->input->post('id');

        try {
            $m_real_sj = new \Model\Storage\RealSJ_model();
            $d_real_sj = $m_real_sj->where('id', $id)->with('det_real_sj')->first();

            if ( $d_real_sj ) {
                foreach ($d_real_sj['det_real_sj'] as $k_drs => $v_drs) {
                    $m_drsi = new \Model\Storage\DetRealSjInv_model();
                    $m_drsi->where('no_sj', $v_drs['no_sj'])->delete();

                    $m_drs = new \Model\Storage\DetRealSJ_model();
                    $m_drs->where('id', $v_drs['id'])->delete();
                }
            }

            $m_real_sj = new \Model\Storage\RealSJ_model();
            $m_real_sj->where('id', $id)->delete();
            
            // $m_conf = new \Model\Storage\Conf();
            // $sql = "exec insert_jurnal NULL, NULL, NULL, 0, 'real_sj', ".$id.", ".$id.", 3";
            // $d_conf = $m_conf->hydrateRaw( $sql );

            Modules::run( 'base/InsertJurnal/exec', $this->url, $id, $id, 3);

            $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_real_sj, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus';
            $this->result['content'] = array('id' => $id);            
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function tes()
    {
        $data = $this->config->item('jenis_ayam');
        // $CI->config->item('jabatan');

        cetak_r( $data );
    }
}