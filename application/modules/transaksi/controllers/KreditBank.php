<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KreditBank extends Public_Controller {

    private $pathView = 'transaksi/kredit_bank/';
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
        $akses = hakAkses($this->url);
        if ( $this->hakAkses['a_view'] == 1 ) {
            $this->add_external_js(array(
                "assets/select2/js/select2.min.js",
                "assets/transaksi/kredit_bank/js/kredit-bank.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/transaksi/kredit_bank/css/kredit-bank.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->hakAkses;

            $content['riwayat'] = $this->load->view($this->pathView.'riwayat', $content, TRUE);
            $content['add_form'] = $this->addForm();

            // Load Indexx
            $data['title_menu'] = 'Kredit Bank';
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
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

    public function getLists()
    {
        $params = $this->input->get('params');

        $data = null;
        $m_kb = new \Model\Storage\KreditBank_model();
        if ( $params == 'all' ) {
            $d_kb = $m_kb->with(['d_perusahaan', 'detail'])->get();
            if ( $d_kb->count() > 0 ) {
                $data = $d_kb->toArray();
            }
        } else {
            $d_kb = $m_kb->where('lunas', $params)->with(['d_perusahaan', 'detail'])->get();
            if ( $d_kb->count() > 0 ) {
                $data = $d_kb->toArray();
            }
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
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
            $html = $this->detailForm($id);
        } else if ( !empty($id) && $edit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $html = $this->editForm($id);
        }else{
            $html = $this->addForm();
        }

        echo $html;
    }

    public function addForm()
    {
        $content['perusahaan'] = $this->getPerusahaan();

        $html = $this->load->view($this->pathView.'addForm', $content, TRUE);

        return $html;
    }

    public function detailForm($id)
    {
        $m_kb = new \Model\Storage\KreditBank_model();
        $d_kb = $m_kb->where('kode', $id)->with(['d_perusahaan', 'detail'])->first();

        $data = null;
        if ( $d_kb ) {
            $data = $d_kb->toArray();
        }

        $content['akses'] = $this->hakAkses;
        $content['data'] = $data;

        $html = $this->load->view($this->pathView.'detailForm', $content, TRUE);

        return $html;
    }

    public function editForm($id)
    {
        $m_kb = new \Model\Storage\KreditBank_model();
        $d_kb = $m_kb->where('kode', $id)->with(['d_perusahaan', 'detail'])->first();

        $data = null;
        if ( $d_kb ) {
            $data = $d_kb->toArray();
        }

        $content['perusahaan'] = $this->getPerusahaan();
        $content['data'] = $data;

        $html = $this->load->view($this->pathView.'editForm', $content, TRUE);

        return $html;
    }

    public function generateRowAngsuran()
    {
        $params = $this->input->get('params');

        $content['pokok_pinjaman'] = $params['pokok_pinjaman'];
        $content['bunga'] = $params['bunga'];
        $content['tenor'] = $params['tenor'];
        $content['angsuran'] = $params['angsuran'];
        $content['angsuran_pokok'] = ($params['pokok_pinjaman'] > 0 && $params['tenor'] > 0) ? $params['pokok_pinjaman'] / $params['tenor'] : 0;
        $content['angsuran_bunga'] = ($params['pokok_pinjaman'] > 0 && $params['tenor'] > 0) ? $params['bunga'] / $params['tenor'] : 0;
        $content['tanggal'] = $params['tanggal'];
        $content['tgl_jatuh_tempo'] = $params['tgl_jatuh_tempo'];
        $html = $this->load->view($this->pathView.'listAngsuran', $content, TRUE);

        echo $html;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $m_kb = new \Model\Storage\KreditBank_model();
            $kode = $m_kb->getNextIdRibuan();

            $m_kb->kode = $kode;
            $m_kb->tanggal = $params['tanggal'];
            $m_kb->perusahaan = $params['perusahaan'];
            $m_kb->jenis_kredit = $params['jenis_kredit'];
            $m_kb->bank = $params['bank'];
            $m_kb->agunan = $params['agunan'];
            $m_kb->no_dokumen = $params['no_dokumen'];
            $m_kb->pokok_pinjaman = $params['pokok_pinjaman'];
            $m_kb->bunga = $params['bunga'];
            $m_kb->bunga_per_tahun = $params['bunga_per_tahun'];
            $m_kb->angsuran = $params['angsuran'];
            $m_kb->tenor = $params['tenor'];
            $m_kb->tgl_jatuh_tempo = $params['tgl_jatuh_tempo'];
            $m_kb->lunas = 0;
            $m_kb->save();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_kbd = new \Model\Storage\KreditBankDet_model();
                $m_kbd->kredit_bank_kode = $kode;
                $m_kbd->angsuran_ke = $v_det['angsuran_ke'];
                $m_kbd->tgl_jatuh_tempo = $v_det['tgl_jatuh_tempo'];
                $m_kbd->jumlah_angsuran = $v_det['jumlah_angsuran'];
                $m_kbd->jumlah_angsuran_pokok = $v_det['jumlah_angsuran_pokok'];
                $m_kbd->jumlah_angsuran_bunga = $v_det['jumlah_angsuran_bunga'];
                if ( isset($v_det['tgl_bayar']) && !empty($v_det['tgl_bayar']) ) {
                    $m_kbd->tgl_bayar = $v_det['tgl_bayar'];
                }
                $m_kbd->save();
            }

            /* UPDATE LUNAS */
            $m_kbd = new \Model\Storage\KreditBankDet_model();
            $d_kbd = $m_kbd->where('kredit_bank_kode', $kode)->where('angsuran_ke', $params['tenor'])->first();
            if ( $d_kbd ) {
                if ( !empty($d_kbd->tgl_bayar) ) {
                    $m_kb->where('kode', $kode)->update(
                        array(
                            'lunas' => 1
                        )
                    );
                }
            }

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_kb, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $kode);
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
            $m_kb = new \Model\Storage\KreditBank_model();
            $kode = $params['kode'];

            $m_kb->where('kode', $kode)->update(
                array(
                    'tanggal' => $params['tanggal'],
                    'perusahaan' => $params['perusahaan'],
                    'jenis_kredit' => $params['jenis_kredit'],
                    'bank' => $params['bank'],
                    'agunan' => $params['agunan'],
                    'no_dokumen' => $params['no_dokumen'],
                    'pokok_pinjaman' => $params['pokok_pinjaman'],
                    'bunga' => $params['bunga'],
                    'bunga_per_tahun' => $params['bunga_per_tahun'],
                    'angsuran' => $params['angsuran'],
                    'tenor' => $params['tenor'],
                    'tgl_jatuh_tempo' => $params['tgl_jatuh_tempo'],
                    'lunas' => 0,
                )
            );

            $m_kbd = new \Model\Storage\KreditBankDet_model();
            $m_kbd->where('kredit_bank_kode', $kode)->delete();

            foreach ($params['detail'] as $k_det => $v_det) {
                $m_kbd = new \Model\Storage\KreditBankDet_model();
                $m_kbd->kredit_bank_kode = $kode;
                $m_kbd->angsuran_ke = $v_det['angsuran_ke'];
                $m_kbd->tgl_jatuh_tempo = $v_det['tgl_jatuh_tempo'];
                $m_kbd->jumlah_angsuran = $v_det['jumlah_angsuran'];
                $m_kbd->jumlah_angsuran_pokok = $v_det['jumlah_angsuran_pokok'];
                $m_kbd->jumlah_angsuran_bunga = $v_det['jumlah_angsuran_bunga'];
                if ( isset($v_det['tgl_bayar']) && !empty($v_det['tgl_bayar']) ) {
                    $m_kbd->tgl_bayar = $v_det['tgl_bayar'];
                }
                $m_kbd->save();
            }

            /* UPDATE LUNAS */
            $m_kbd = new \Model\Storage\KreditBankDet_model();
            $d_kbd = $m_kbd->where('kredit_bank_kode', $kode)->where('angsuran_ke', $params['tenor'])->first();
            if ( $d_kbd ) {
                if ( !empty($d_kbd->tgl_bayar) ) {
                    $m_kb->where('kode', $kode)->update(
                        array(
                            'lunas' => 1
                        )
                    );
                }
            }

            $d_kb = $m_kb->where('kode', $kode)->first();

            $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/update', $m_kb, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $kode);
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
            $m_kb = new \Model\Storage\KreditBank_model();
            $kode = $params;

            $d_kb = $m_kb->where('kode', $kode)->first();

            $m_kbd = new \Model\Storage\KreditBankDet_model();
            $m_kbd->where('kredit_bank_kode', $kode)->delete();
            $m_kb->where('kode', $kode)->delete();

            $deskripsi_log = 'di-delete oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/delete', $d_kb, $deskripsi_log );

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function saveDetail()
    {
        $params = $this->input->post('params');

        try {
            $m_kbd = new \Model\Storage\KreditBankDet_model();
            $m_kbd->where('kredit_bank_kode', $params['kredit_bank_kode'])->where('angsuran_ke', $params['angsuran_ke'])->update(
                array(
                    'tgl_bayar' => $params['tgl_bayar']
                )
            );

            $m_kbd = new \Model\Storage\KreditBankDet_model();
            $d_kbd = $m_kbd->where('kredit_bank_kode', $params['kredit_bank_kode'])->where('angsuran_ke', $params['angsuran_ke'])->first();
            if ( $d_kbd ) {
                if ( !empty($d_kbd->tgl_bayar) ) {
                    $m_kb = new \Model\Storage\KreditBank_model();
                    $m_kb->where('kode', $params['kredit_bank_kode'])->update(
                        array(
                            'lunas' => 1
                        )
                    );
                }
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}