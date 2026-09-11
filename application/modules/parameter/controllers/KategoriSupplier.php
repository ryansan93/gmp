<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KategoriSupplier extends Public_Controller {

    private $pathView = 'parameter/kategori_supplier/';
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
                "assets/parameter/kategori_supplier/js/kategori_supplier.js",
            ));

            $data = $this->includes;
            $content['akses'] = $this->hakAkses;
            $content['title_panel'] = 'Master Kategori Supplier';
            $data['title_menu'] = 'Master Kategori Supplier';

            $data['view'] = $this->load->view($this->pathView . 'v_index', $content, TRUE);
            $this->load->view($this->template, $data);

        } else {
            showErrorAkses();
        }
    }

    public function list_data()
    {
        $akses = hakAkses($this->url);
        $m_kategori = new \Model\Storage\KategoriSupplier_model();
        $keyword = trim($this->input->post('keyword'));
        $query = $m_kategori;

        if ( !empty($keyword) ) {
            $query->where(function($query) use ($keyword) {
                $query->where('kode_kategori', 'like', '%' . $keyword . '%')
                    ->orWhere('nama_kategori', 'like', '%' . $keyword . '%');
            });
        }

        $d_kategori = $query->orderBy('id', 'asc')->get()->toArray();

        $content['akses'] = $akses;
        $content['list'] = $d_kategori;
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

        $m_kategori = new \Model\Storage\KategoriSupplier_model();
        $d_kategori = $m_kategori->where('id', $id)->first();

        $data['data'] = $d_kategori;
        $this->load->view($this->pathView . 'v_form', $data);
    }

    public function save_data()
    {
        $params = $this->input->post('params');

        try {
            $m_kategori = new \Model\Storage\KategoriSupplier_model();

            $kodeKategori = trim($params['kode_kategori']);
            $namaKategori = trim($params['nama_kategori']);

            if ( empty($kodeKategori) || empty($namaKategori) ) {
                $this->result['message'] = 'Kode kategori dan nama kategori wajib diisi.';
            } else {
                $existingByKode = $m_kategori->where('kode_kategori', $kodeKategori)->first();
                $existingByNama = $m_kategori->where('nama_kategori', $namaKategori)->first();

                if ( $existingByKode || $existingByNama ) {
                    $this->result['message'] = 'Kode atau nama kategori sudah ada.';
                } else {
                    $m_kategori->kode_kategori = $kodeKategori;
                    $m_kategori->nama_kategori = $namaKategori;
                    $m_kategori->save();

                    $id            = $m_kategori->id;
                    $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                    Modules::run('base/event/save', $m_kategori, $deskripsi_log, null, $id, $m_kategori);

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
            $m_kategori = new \Model\Storage\KategoriSupplier_model();

            $kodeKategori = trim($params['kode_kategori']);
            $namaKategori = trim($params['nama_kategori']);

            if ( empty($kodeKategori) || empty($namaKategori) ) {
                $this->result['message'] = 'Kode kategori dan nama kategori wajib diisi.';
            } else {
                $kategoriLama = $m_kategori->where('id', $params['id'])->first();

                $usedBySupplier = false;
                if ( $kategoriLama && !empty($kategoriLama->kode_kategori) ) {
                    $m_conf = new \Model\Storage\Conf();
                    $d_used = $m_conf->hydrateRaw("
                        select top 1 1 as ada from pelanggan
                        where tipe = 'supplier' and LOWER(LTRIM(RTRIM(kategori_supplier))) = '" . strtolower(trim($kategoriLama->kode_kategori)) . "'
                    ");
                    $usedBySupplier = $d_used->count() > 0;
                }

                if ( $usedBySupplier ) {
                    $this->result['message'] = 'Kategori sudah dipakai oleh data supplier, tidak bisa diubah.';
                } else {
                    $existingByKode = $m_kategori
                        ->where('kode_kategori', $kodeKategori)
                        ->where('id', '!=', $params['id'])
                        ->first();
                    $existingByNama = $m_kategori
                        ->where('nama_kategori', $namaKategori)
                        ->where('id', '!=', $params['id'])
                        ->first();

                    if ( $existingByKode || $existingByNama ) {
                        $this->result['message'] = 'Kode atau nama kategori sudah ada.';
                    } else {
                        $data_update = [
                            'kode_kategori' => $kodeKategori,
                            'nama_kategori' => $namaKategori,
                        ];

                        $m_kategori->where('id', $params['id'])->update($data_update);

                        $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
                        Modules::run('base/event/update', $m_kategori, $deskripsi_log, null, $params['id'], $m_kategori);

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
            $m_kategori = new \Model\Storage\KategoriSupplier_model();
            $kategori = $m_kategori->where('id', $id)->first();

            $usedBySupplier = false;
            if ( $kategori && !empty($kategori->kode_kategori) ) {
                $m_conf = new \Model\Storage\Conf();
                $d_used = $m_conf->hydrateRaw("
                    select top 1 1 as ada from pelanggan
                    where tipe = 'supplier' and LOWER(LTRIM(RTRIM(kategori_supplier))) = '" . strtolower(trim($kategori->kode_kategori)) . "'
                ");
                $usedBySupplier = $d_used->count() > 0;
            }

            if ( $usedBySupplier ) {
                $this->result['message'] = 'Kategori sudah dipakai oleh data supplier, tidak bisa dihapus.';
            } else {
                $m_kategori->where('id', $id)->delete();

                $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run('base/event/delete', $m_kategori, $deskripsi_log, null, $id, $m_kategori);

                $this->result['status'] = 1;
                $this->result['message'] = 'Data berhasil dihapus';
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal : ' . $e->getMessage();
        }

        display_json($this->result);
    }
}
