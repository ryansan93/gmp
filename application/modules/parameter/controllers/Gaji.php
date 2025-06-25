<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Gaji extends Public_Controller {

    private $pathView = 'parameter/gaji/';
    private $url;
    private $hakAkses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->hakAkses = hakAkses($this->url);
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index($segment=0)
    {
        if ( $this->hakAkses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/jquery/list.min.js",
                "assets/parameter/gaji/js/gaji.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/parameter/gaji/css/gaji.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->hakAkses;
            $content['title_menu'] = 'Master Gaji Pegawai';

            $content['riwayat'] = $this->riwayatForm();
            $content['addForm'] = $this->addForm();

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function loadForm()
    {
        $params = $this->input->get('params');
        $edit = $this->input->get('edit');

        $id = $params['id'];

        $content = array();
        $html = "url not found";
        
        if ( !empty($id) && $edit != 'edit' ) {
            // NOTE: view data BASTTB (ajax)
            // $html = $this->detail_form($id);
        } else if ( !empty($id) && $edit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            // $html = $this->edit_form($id);
        }else{
            $html = $this->addForm();
        }

        echo $html;
    }

    public function getPegawai()
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
                    $kode_unit = null;
                    if ( is_numeric($v_unit['unit']) ) {
                        $m_wilayah = new \Model\Storage\Wilayah_model();
                        $d_wilayah = $m_wilayah->where('id', $v_unit['unit'])->first();

                        $nama_unit = str_replace('Kab ', '', str_replace('Kota ', '', $d_wilayah['nama']));
                        $kode_unit = $d_wilayah['kode'];

                        $data_unit[ $kode_unit ] = array(
                            'nama' => $nama_unit,
                            'kode_unit' => $kode_unit
                        );
                    } else {
                        $m_wilayah = new \Model\Storage\Wilayah_model();
                        $d_wilayah = $m_wilayah->where('jenis', 'UN')->get();

                        if ( $d_wilayah->count() > 0 ) {
                            $d_wilayah = $d_wilayah->toArray();

                            foreach ($d_wilayah as $k_wilayah => $v_wilayah) {
                                $nama_unit = str_replace('Kab ', '', str_replace('Kota ', '', $v_wilayah['nama']));
                                $kode_unit = $v_wilayah['kode'];

                                $data_unit[ $v_wilayah['kode'] ] = array(
                                    'nama' => $nama_unit,
                                    'kode_unit' => $kode_unit
                                );
                            }
                        }
                    }
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

                $list_nama_unit = '';
                $list_kode_unit = '';

                ksort($data_unit);

                $idx_unit = 0;
                foreach ($data_unit as $k_du => $v_du) {
                    $koma = ($idx_unit > 0) ? ', ' : '';
                    $list_nama_unit .= $koma.$v_du['nama'];
                    $list_kode_unit .= $koma.$v_du['kode_unit'];

                    $idx_unit++;
                }

                $d_atasan = $m_karyawan->where('id', $v_karyawan['atasan'])->first();

                $key = $v_karyawan['level'].' | '.$v_karyawan['jabatan'].' | '.$v_karyawan['nama'].' | '.$k_karyawan;

                $data[$key] = array(
                    'id' => $v_karyawan['id'],
                    'level' => $v_karyawan['level'],
                    'nik' => $v_karyawan['nik'],
                    'nama' => $v_karyawan['nama'],
                    'jabatan' => $v_karyawan['jabatan'],
                    'atasan' => $d_atasan['nama'],
                    'marketing' => $v_karyawan['marketing'],
                    'kordinator' => $v_karyawan['kordinator'],
                    'wilayah' => $data_wilayah,
                    'unit' => $data_unit,
                    'list_nama_unit' => $list_nama_unit,
                    'list_kode_unit' => $list_kode_unit
                );
            }
        }

        if ( !empty($data) ) {
            ksort($data);
        }

        return $data;
    }

    public function getLists()
    {
        $nik = $this->input->get('nik');

        $m_gaji = new \Model\Storage\Gaji_model();
        $d_gaji = $m_gaji->where('nik', $nik)->orderBy('id', 'desc')->with(['karyawan', 'gaji_unit'])->get();

        $data = null;
        if ( $d_gaji->count() > 0 ) {
            $d_gaji = $d_gaji->toArray();

            foreach ($d_gaji as $k_gaji => $v_gaji) {
                $idx_unit = 0;

                $unit = '';
                foreach ($v_gaji['gaji_unit'] as $k_gu => $v_gu) {
                    $nama_unit = str_replace('Kab ', '', str_replace('Kota ', '', $v_gu['unit']['nama']));

                    $koma = ($idx_unit > 0) ? ', ' : '';
                    $unit .= $koma.$nama_unit;

                    $idx_unit++;
                }

                $data[] = array(
                    'nik' => $v_gaji['nik'],
                    'nama' => $v_gaji['karyawan']['nama'],
                    'unit' => $unit,
                    'tgl_berlaku' => $v_gaji['tgl_berlaku'],
                    'gaji' => $v_gaji['gaji'],
                );
            }
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list', $content, true);

        echo $html;
    }

    public function riwayatForm()
    {
        $content['pegawai'] = $this->getPegawai();
        $content['akses'] = $this->hakAkses;
        $html = $this->load->view($this->pathView.'riwayat', $content, TRUE);

        return $html;
    }

    public function addForm()
    {
        $content['pegawai'] = $this->getPegawai();
        $content['akses'] = $this->hakAkses;
        $html = $this->load->view($this->pathView.'addForm', $content, TRUE);

        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_gaji = new \Model\Storage\Gaji_model();
            $m_gaji->nik = $params['nik'];
            $m_gaji->jabatan = $params['jabatan'];
            $m_gaji->tgl_berlaku = $params['tgl_berlaku'];
            $m_gaji->gaji = $params['gaji'];
            $m_gaji->save();

            $id_header = $m_gaji->id;

            $kode_unit = explode(", ", $params['kode_unit']);
            foreach ($kode_unit as $k_ku => $v_ku) {
                $m_gu = new \Model\Storage\GajiUnit_model();
                $m_gu->id_header = $id_header;
                $m_gu->unit_kode = $v_ku;
                $m_gu->save();
            }

            if ( isset($params['insentif']) && !empty($params['insentif']) ) {
                foreach ($params['insentif'] as $key => $value) {
                    $m_gi = new \Model\Storage\GajiInsentif_model();
                    $m_gi->id_header = $id_header;
                    $m_gi->keterangan = $value['keterangan'];
                    $m_gi->nominal = $value['nominal'];
                    $m_gi->save();
                }
            }

            if ( isset($params['potongan']) && !empty($params['potongan']) ) {
                foreach ($params['potongan'] as $key => $value) {
                    $m_gp = new \Model\Storage\GajiPotongan_model();
                    $m_gp->id_header = $id_header;
                    $m_gp->keterangan = $value['keterangan'];
                    $m_gp->nominal = $value['nominal'];
                    $m_gp->save();
                }
            }

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_gaji, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessag();
        }

        display_json($this->result);
    }
}