<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AksesKhusus extends Public_Controller
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
                'assets/master/akses_khusus/js/akses-khusus.js',
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
            ));

            $data = $this->includes;
            $data['title_menu'] = 'Akses Khusus';

            $content['akses'] = $akses;
            $data['view'] = $this->load->view('master/akses_khusus/index', $content, true);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function get_lists()
    {
        $akses = hakAkses($this->url);

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            SELECT
                ak.id,
                ak.id_group,
                mg.nama_group,
                ak.id_detfitur,
                df.nama_detfitur,
                df.path_detfitur,
                ak.akses_khusus
            FROM akses_khusus ak
            LEFT JOIN ms_group mg ON mg.id_group = ak.id_group
            LEFT JOIN detail_fitur df ON df.id_detfitur = ak.id_detfitur
            ORDER BY mg.nama_group ASC, df.nama_detfitur ASC
        ";
        $d_data = $m_conf->hydrateRaw($sql);

        $content['akses'] = $akses;
        $content['list']  = $d_data->count() > 0 ? $d_data->toArray() : [];

        $html = $this->load->view('master/akses_khusus/list', $content, true);
        echo $html;
    }

    public function get_form()
    {
        $id = $this->input->get('id');

        $data = null;
        if ( !empty($id) ) {
            $m_ak = new \Model\Storage\AksesKhusus_model();
            $d_ak = $m_ak->where('id', $id)->first();
            if ( $d_ak ) {
                $data = $d_ak->toArray();
            }
        }

        // Ambil list group
        $m_conf = new \Model\Storage\Conf();
        $d_group = $m_conf->hydrateRaw("SELECT id_group, nama_group FROM ms_group ORDER BY nama_group ASC");
        $groups  = $d_group->count() > 0 ? $d_group->toArray() : [];

        // Ambil list fitur
        $d_fitur = $m_conf->hydrateRaw("SELECT id_detfitur, nama_detfitur, path_detfitur FROM detail_fitur ORDER BY nama_detfitur ASC");
        $fiturs  = $d_fitur->count() > 0 ? $d_fitur->toArray() : [];

        $content['data']   = $data;
        $content['groups'] = $groups;
        $content['fiturs'] = $fiturs;

        $this->load->view('master/akses_khusus/form', $content);
    }

    public function save_data()
    {
        $params = $this->input->post('params');

        try {
            $id_group     = trim($params['id_group']);
            $id_detfitur  = isset($params['id_detfitur']) ? trim($params['id_detfitur']) : '';
            $akses_khusus = trim($params['akses_khusus']);

            // Cek duplikat — gunakan instance terpisah
            $m_cek = new \Model\Storage\AksesKhusus_model();
            $query = $m_cek->where('id_group', $id_group)
                           ->where('akses_khusus', $akses_khusus);
            if ( $id_detfitur !== '' ) {
                $query = $query->where('id_detfitur', $id_detfitur);
            }
            $cek = $query->first();

            if ( $cek ) {
                $this->result['message'] = 'Data akses khusus sudah ada untuk kombinasi tersebut.';
            } else {
                // Fresh instance — tidak ada query state
                $m_save               = new \Model\Storage\AksesKhusus_model();
                $m_save->id_group     = $id_group;
                $m_save->id_detfitur  = $id_detfitur;
                $m_save->akses_khusus = $akses_khusus;
                $m_save->save();

                $this->result['status']  = 1;
                $this->result['message'] = 'Data Akses Khusus berhasil disimpan.';
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal: ' . $e->getMessage();
        }

        display_json($this->result);
    }

    public function update_data()
    {
        $params = $this->input->post('params');

        try {
            $id           = (int) $params['id'];
            $id_group     = trim($params['id_group']);
            $id_detfitur  = isset($params['id_detfitur']) ? trim($params['id_detfitur']) : '';
            $akses_khusus = trim($params['akses_khusus']);

            // Cek duplikat (exclude id sendiri) — gunakan instance terpisah
            $m_cek = new \Model\Storage\AksesKhusus_model();
            $query = $m_cek->where('id_group', $id_group)
                           ->where('akses_khusus', $akses_khusus)
                           ->where('id', '<>', $id);
            if ( $id_detfitur !== '' ) {
                $query = $query->where('id_detfitur', $id_detfitur);
            }
            $cek = $query->first();

            if ( $cek ) {
                $this->result['message'] = 'Data akses khusus sudah ada untuk kombinasi tersebut.';
            } else {
                $m_upd = new \Model\Storage\AksesKhusus_model();
                $m_upd->where('id', $id)->update(array(
                    'id_group'     => $id_group,
                    'id_detfitur'  => $id_detfitur,
                    'akses_khusus' => $akses_khusus,
                ));

                $this->result['status']  = 1;
                $this->result['message'] = 'Data Akses Khusus berhasil diupdate.';
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal: ' . $e->getMessage();
        }

        display_json($this->result);
    }

    public function delete_data()
    {
        $id = $this->input->post('params');

        try {
            $m_ak = new \Model\Storage\AksesKhusus_model();
            $m_ak->where('id', $id)->delete();

            $this->result['status']  = 1;
            $this->result['message'] = 'Data Akses Khusus berhasil dihapus.';
        } catch (\Illuminate\Database\QueryException $e) {
            $this->result['message'] = 'Gagal: ' . $e->getMessage();
        }

        display_json($this->result);
    }
}
