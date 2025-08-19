<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KonfirmasiPanen extends Public_Controller {

    private $url;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index($segment=0)
    {
        $akses = hakAkses($this->url);
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/transaksi/konfirmasi_panen/js/konfirmasi-panen.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/konfirmasi_panen/css/konfirmasi-panen.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['unit'] = $this->get_unit();

            // Load Indexx
            $data['title_menu'] = 'Konfirmasi Panen';
            $data['view'] = $this->load->view('transaksi/konfirmasi_panen/index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function load_form()
    {
        $akses = hakAkses($this->url);
        $params = $this->input->get('params');

        $id = (isset($params['id']) && !empty($params['id'])) ? trim($params['id']) : null;

        $data = null;

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->where('noreg', trim($params['noreg']))->with(['dKandang', 'dMitraMapping', 'data_konfir'])->first();

        $populasi = 0;

        $edit = 0;
        if ( !empty($d_rs) ) {
            $data = $d_rs->toArray();
            $populasi = $data['populasi'];

            $m_drpah = new \Model\Storage\DetRpah_model();
            $d_drpah = $m_drpah->where('id_konfir', $id)->first();
            if ( $d_drpah ) {
                $edit = 1;
            }
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                td.jml_ekor
            from order_doc od
            right join
                terima_doc td
                on
                    od.no_order = td.no_order
            where
                od.noreg = '".trim($params['noreg'])."'
        ";
        $d_terima = $m_conf->hydrateRaw( $sql );
        if ( $d_terima->count() > 0 ) {
            $populasi = $d_terima->toArray()[0]['jml_ekor'];
        }

        $content['akses'] = $akses;
        $content['data'] = $data;
        $content['edit'] = $edit;
        $content['populasi'] = $populasi;

        if ( empty($edit) || $edit == 0 ) {
            $html = $this->load->view('transaksi/konfirmasi_panen/add_form', $content);
        } else {
            $html = $this->load->view('transaksi/konfirmasi_panen/detail_form', $content);
        }
        
        return $html;
    }

    public function get_unit()
    {
        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('jenis', 'UN')->orderBy('nama', 'asc')->get()->toArray();

        return $d_wilayah;
    }

    public function get_lists()
    {
        $params = $this->input->get('params');

        $unit = $params['unit'];
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $m_wilayah = new \Model\Storage\Wilayah_model();
        $d_wilayah = $m_wilayah->where('id', $unit)->first();

        $data = null;

        $m_rs = new \Model\Storage\RdimSubmit_model();
        $d_rs = $m_rs->whereBetween('tgl_docin', [$start_date, $end_date])->with(['dKandang', 'data_konfir'])->orderBy('tgl_docin', 'asc')->get();

        if ( $d_rs->count() > 0 ) {
            $d_rs = $d_rs->toArray();
            foreach ($d_rs as $k => $v_rs) {
                $kdg = $v_rs['d_kandang'];
                if ( $d_wilayah['kode'] == $kdg['d_unit']['kode'] ) {
                    $m_mm = new \Model\Storage\MitraMapping_model();
                    $d_mm = $m_mm->where('nim', $v_rs['nim'])->orderBy('id', 'desc')->first();

                    $nama_mitra = null;
                    if ( $d_mm ) {
                        $m_mitra = new \Model\Storage\Mitra_model();
                        $d_mitra = $m_mitra->where('id', $d_mm->mitra)->orderBy('id', 'desc')->first();

                        $nama_mitra = $d_mitra->nama;
                    }

                    $populasi = $v_rs['populasi'];

                    $m_conf = new \Model\Storage\Conf();
                    $sql = "
                        select
                            td.jml_ekor
                        from order_doc od
                        right join
                            terima_doc td
                            on
                                od.no_order = td.no_order
                        where
                            od.noreg = '".$v_rs['noreg']."'
                    ";
                    $d_terima = $m_conf->hydrateRaw( $sql );
                    if ( $d_terima->count() > 0 ) {
                        $populasi = $d_terima->toArray()[0]['jml_ekor'];
                    }

                    // $data[$v_rs['id']] = $v_rs;
                    $data[$v_rs['id']] = array(
                        'id' => !empty($v_rs['data_konfir']) ? $v_rs['data_konfir']['id'] : null,
                        'tgl_panen' => !empty($v_rs['data_konfir']) ? $v_rs['data_konfir']['tgl_panen'] : null,
                        'total' => !empty($v_rs['data_konfir']) ? $v_rs['data_konfir']['total'] : null,
                        'bb_rata2' => !empty($v_rs['data_konfir']) ? $v_rs['data_konfir']['bb_rata2'] : null,
                        'tgl_docin' => tglIndonesia($v_rs['tgl_docin'], '-', ' '),
                        'noreg' => $v_rs['noreg'],
                        'nama' => $nama_mitra,
                        'populasi' => angkaRibuan($populasi),
                        'kandang' => $v_rs['d_kandang']['kandang'],
                        'unit' => $d_wilayah['kode'],
                    );
                }
            }
        }

        $content['data'] = $data;
        $html = $this->load->view('transaksi/konfirmasi_panen/list', $content, true);
            
        echo $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_konfir = new \Model\Storage\Konfir_model();
            $m_konfir->noreg = $params['noreg'];
            $m_konfir->tgl_docin = $params['tgl_docin'];
            $m_konfir->tgl_panen = $params['tgl_panen'];
            $m_konfir->populasi = $params['populasi'];
            $m_konfir->bb_rata2 = $params['bb_rata2'];
            $m_konfir->total = $params['tot_sekat'];
            $m_konfir->save();

            $id_konfir = $m_konfir->id;

            foreach ($params['data_sekat'] as $k_ds => $v_ds) {
                $m_dkonfir = new \Model\Storage\DetKonfir_model();
                $m_dkonfir->id_konfir = $id_konfir;
                $m_dkonfir->jumlah = $v_ds['jumlah'];
                $m_dkonfir->bb = $v_ds['bb'];
                $m_dkonfir->save();
            }

            $m_konfir = new \Model\Storage\Konfir_model();
            $d_konfir  = $m_konfir->where('id', $id_konfir)->with(['det_konfir'])->first();

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $d_konfir, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil disimpan';
            $this->result['content'] = array('id' => $id_konfir);            
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function update()
    {
        $params = $this->input->post('params');

        try {
            $m_konfir = new \Model\Storage\Konfir_model();

            $id_konfir = $params['id'];

            $m_konfir->where('id', $id_konfir)->update(
                array(
                    'noreg' => $params['noreg'],
                    'tgl_docin' => $params['tgl_docin'],
                    'tgl_panen' => $params['tgl_panen'],
                    'populasi' => $params['populasi'],
                    'bb_rata2' => $params['bb_rata2'],
                    'total' => $params['tot_sekat']
                )
            );

            $m_dkonfir = new \Model\Storage\DetKonfir_model();
            $m_dkonfir->where('id_konfir', $id_konfir)->delete();

            foreach ($params['data_sekat'] as $k_ds => $v_ds) {
                $m_dkonfir = new \Model\Storage\DetKonfir_model();
                $m_dkonfir->id_konfir = $id_konfir;
                $m_dkonfir->jumlah = $v_ds['jumlah'];
                $m_dkonfir->bb = $v_ds['bb'];
                $m_dkonfir->save();
            }

            $m_konfir = new \Model\Storage\Konfir_model();
            $d_konfir  = $m_konfir->where('id', $id_konfir)->with(['det_konfir'])->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $d_konfir, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di update';
            $this->result['content'] = array('id' => $id_konfir);            
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete()
    {
        $id = $this->input->post('id');

        try {
            $id_konfir = $id;

            $m_konfir = new \Model\Storage\Konfir_model();
            $d_konfir = $m_konfir->where('id', $id_konfir)->with(['det_konfir'])->first();

            $deskripsi_log = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_konfir, $deskripsi_log);

            // $m_konfir = new \Model\Storage\Konfir_model();
            $m_konfir->where('id', $id_konfir)->delete();

            $m_dkonfir = new \Model\Storage\DetKonfir_model();
            $m_dkonfir->where('id_konfir', $id_konfir)->delete();

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus';
            $this->result['content'] = array('id' => $id_konfir);            
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = "Gagal : " . $e->getMessage();
        }

        display_json($this->result);
    }
}