<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MasterJenisSewa extends Public_Controller {

    private $pathView = 'sewa/master_jenis_sewa/';
    private $url;
    private $hakAkses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->hakAkses = hakAkses($this->url);
    }

    public function index($segment=0)
    {
        if ( $this->hakAkses['a_view'] == 1 ) {

            $this->add_external_js(array(
                "assets/sewa/master_jenis_sewa/js/master_jenis_sewa.js",
            ));

            $data = $this->includes;
            $content['akses'] = $this->hakAkses;
            $content['title_panel'] = 'Master Jenis Sewa';
            $data['title_menu'] = 'Master Jenis Sewa';

            $data['view'] = $this->load->view($this->pathView . 'v_index', $content, TRUE);
            $this->load->view($this->template, $data);

        } else {
            showErrorAkses();
        }
    }

    public function list_data()
    {
        $akses = hakAkses($this->url);
        $m_jenis = new \Model\Storage\MasterJenisSewa_model();
        $keyword = trim($this->input->post('keyword'));
        $query = $m_jenis;

        if ( !empty($keyword) ) {
            $query->where(function($query) use ($keyword) {
                $query->where('kode_jenis_sewa', 'like', '%' . $keyword . '%')
                    ->orWhere('nama_jenis_sewa', 'like', '%' . $keyword . '%');
            });
        }

        $d_jenis = $query->orderBy('id', 'asc')->get()->toArray();

        $content['akses'] = $akses;
        $content['list'] = $d_jenis;
        $html = $this->load->view($this->pathView . 'v_list', $content, TRUE);

        echo $html;
    }

    public function add_form()
    {
        $data['data'] = null;
        $this->load->view($this->pathView . 'v_form', $data);
    }

    public function edit_form()
    {
        $id = $this->input->get('id');

        $m_jenis = new \Model\Storage\MasterJenisSewa_model();
        $d_jenis = $m_jenis->where('id', $id)->first();

        $data['data'] = $d_jenis;
        $this->load->view($this->pathView . 'v_form', $data);
    }

    public function save_data()
    {
        $params = $this->input->post('params');

        try {
            $m_jenis = new \Model\Storage\MasterJenisSewa_model();

            $kodeJenisSewa = trim($params['kode_jenis_sewa']);
            $namaJenisSewa = trim($params['nama_jenis_sewa']);
            // $keterangan = trim($params['keterangan']);

            if ( empty($kodeJenisSewa) || empty($namaJenisSewa) ) {
                $this->result['message'] = 'Kode jenis sewa dan nama jenis sewa wajib diisi.';
            } else {
                $existingByKode = $m_jenis->where('kode_jenis_sewa', $kodeJenisSewa)->first();
                $existingByNama = $m_jenis->where('nama_jenis_sewa', $namaJenisSewa)->first();

                if ( $existingByKode || $existingByNama ) {
                    $this->result['message'] = 'Kode atau nama jenis sewa sudah ada.';
                } else {
                    $m_jenis->kode_jenis_sewa = $kodeJenisSewa;
                    $m_jenis->nama_jenis_sewa = $namaJenisSewa;
                    // $m_jenis->keterangan = $keterangan;
                    $m_jenis->save();

                    $id            = $m_jenis->id;
                    $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run('base/event/save', $m_jenis, $deskripsi_log, null, $id, $m_jenis);


                    $this->result['status'] = 1;
                    $this->result['message'] = 'Data berhasil disimpan';
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        }

        display_json($this->result);
    }

    public function edit_data()
    {
        $params = $this->input->post('params');

        try {
            $m_jenis = new \Model\Storage\MasterJenisSewa_model();

            $kodeJenisSewa = trim($params['kode_jenis_sewa']);
            $namaJenisSewa = trim($params['nama_jenis_sewa']);
            // $keterangan = trim($params['keterangan']);

            if ( empty($kodeJenisSewa) || empty($namaJenisSewa) ) {
                $this->result['message'] = 'Kode jenis sewa dan nama jenis sewa wajib diisi.';
            } else {
                $jenisLama = $m_jenis->where('id', $params['id'])->first();
                $m_sewa = new \Model\Storage\MasterSewa_model();

                $usedBySewa = false;
                if ( $jenisLama && !empty($jenisLama->kode_jenis_sewa) ) {
                    $usedBySewa = $m_sewa
                        ->whereRaw('LOWER(LTRIM(RTRIM(jenis_sewa))) = ?', [strtolower(trim($jenisLama->kode_jenis_sewa))])
                        ->first();
                }

                if ( $usedBySewa ) {
                    $this->result['message'] = 'Jenis sewa sudah dipakai transaksi, tidak bisa diubah.';
                } else {
                    $existingByKode = $m_jenis
                        ->where('kode_jenis_sewa', $kodeJenisSewa)
                        ->where('id', '!=', $params['id'])
                        ->first();
                    $existingByNama = $m_jenis
                        ->where('nama_jenis_sewa', $namaJenisSewa)
                        ->where('id', '!=', $params['id'])
                        ->first();

                    if ( $existingByKode || $existingByNama ) {
                        $this->result['message'] = 'Kode atau nama jenis sewa sudah ada.';
                    } else {
                        $data_update = [
                            'kode_jenis_sewa' => $kodeJenisSewa,
                            'nama_jenis_sewa' => $namaJenisSewa,
                            // 'keterangan' => $keterangan,
                        ];

                        $m_jenis->where('id', $params['id'])->update($data_update);

                        $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
                        Modules::run('base/event/update', $m_jenis, $deskripsi_log, null, $params['id'], $m_jenis);

                        $this->result['status'] = 1;
                        $this->result['message'] = 'Data berhasil diubah';
                    }
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete_data()
    {
        $id = $this->input->post('params');

        try {
            $m_jenis = new \Model\Storage\MasterJenisSewa_model();
            $jenis = $m_jenis->where('id', $id)->first();

            if ( $jenis && !empty($jenis->kode_jenis_sewa) ) {
                $m_sewa = new \Model\Storage\MasterSewa_model();
                $usedBySewa = $m_sewa
                    ->whereRaw('LOWER(LTRIM(RTRIM(jenis_sewa))) = ?', [strtolower(trim($jenis->kode_jenis_sewa))])
                    ->first();

                if ( $usedBySewa ) {
                    $this->result['message'] = 'Jenis sewa sudah dipakai transaksi, tidak bisa dihapus.';
                } else {
                    $m_jenis->where('id', $id)->delete();

                    $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run('base/event/delete', $m_jenis, $deskripsi_log, null, $id, $m_jenis);

                    $this->result['status'] = 1;
                    $this->result['message'] = 'Data berhasil dihapus';
                }
            } else {
                $m_jenis->where('id', $id)->delete();

                $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run('base/event/delete', $m_jenis, $deskripsi_log, null, $id, $m_jenis);

                $this->result['status'] = 1;
                $this->result['message'] = 'Data berhasil dihapus';
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        }

        display_json($this->result);
    }
}
