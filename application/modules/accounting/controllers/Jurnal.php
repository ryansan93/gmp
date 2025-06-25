<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Jurnal extends Public_Controller
{
    private $pathView = 'accounting/jurnal/';
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
                'assets/accounting/jurnal/js/jurnal.js'
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/accounting/jurnal/css/jurnal.css'
            ));

            $data = $this->includes;

            $data['title_menu'] = 'Jurnal';

            $content['add_form'] = $this->add_form();
            $content['riwayat'] = $this->riwayat();

            $content['akses'] = $this->hakAkses;
            $data['view'] = $this->load->view($this->pathView . 'index', $content, true);

            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $m_jurnal = new \Model\Storage\Jurnal_model();
        $d_jurnal = $m_jurnal->whereBetween('tanggal', [$start_date, $end_date])->orderBy('tanggal', 'desc')->with(['jurnal_trans'])->get();

        $data = null;
        if ( $d_jurnal->count() > 0 ) {
            $d_jurnal = $d_jurnal->toArray();

            foreach ($d_jurnal as $k_jurnal => $v_jurnal) {
                $unit = $v_jurnal['unit'];
                if ( stristr($v_jurnal['unit'], 'all') === false && stristr($v_jurnal['unit'], 'pusat') === false ) {
                    $m_wilayah = new \Model\Storage\Wilayah_model();
                    $d_wilayah = $m_wilayah->where('kode', $v_jurnal['unit'])->first();

                    $unit = str_replace('kab ', '', $d_wilayah->nama);
                    $unit = str_replace('kota ', '', $unit);
                }

                $data[ $v_jurnal['id'] ] = array(
                    'id' => $v_jurnal['id'],
                    'tanggal' => $v_jurnal['tanggal'],
                    'jurnal_trans' => $v_jurnal['jurnal_trans'],
                    'unit' => $unit
                );
            }
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list', $content, true);

        echo $html;
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

    public function getUnit()
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

    public function getJurnalTrans()
    {
        $m_jt = new \Model\Storage\JurnalTrans_model();
        $d_jt = $m_jt->where('mstatus', 1)->orderBy('nama', 'asc')->with(['detail', 'sumber_tujuan'])->get();

        $data = null;
        if ( $d_jt->count() > 0 ) {
            $data = $d_jt->toArray();
        }

        return $data;
    }

    public function getSupplier()
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

    public function riwayat()
    {
        $data = null;

        $m_jt = new \Model\Storage\JurnalTrans_model();
        $d_jt = $m_jt->orderBy('nama', 'asc')->with(['detail'])->get();

        if ( $d_jt->count() > 0 ) {
            $data = $d_jt->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'riwayat', $content, true);

        return $html;
    }

    public function add_form()
    {
        $content['unit'] = $this->getUnit();
        $content['jurnal_trans'] = $this->getJurnalTrans();
        $content['supplier'] = $this->getSupplier();
        $content['perusahaan'] = $this->getPerusahaan();

        $html = $this->load->view($this->pathView . 'add_form', $content, true);

        return $html;
    }

    public function detail_form($id)
    {
        $m_jurnal = new \Model\Storage\Jurnal_model();
        $d_jurnal = $m_jurnal->where('id', $id)->with(['jurnal_trans', 'detail'])->first();

        $data = null;
        if ( $d_jurnal ) {
            $d_jurnal = $d_jurnal->toArray();

            $unit = $d_jurnal['unit'];
            if ( stristr($d_jurnal['unit'], 'all') === false && stristr($d_jurnal['unit'], 'pusat') ) {
                $m_wilayah = new \Model\Storage\Wilayah_model();
                $d_wilayah = $m_wilayah->where('kode', $d_jurnal['unit'])->first();

                $unit = str_replace('kab ', '', $d_wilayah->nama);
                $unit = str_replace('kota ', '', $unit);
            }

            $data = array(
                'id' => $d_jurnal['id'],
                'tanggal' => $d_jurnal['tanggal'],
                'jurnal_trans' => $d_jurnal['jurnal_trans'],
                'unit' => $unit,
                'detail' => $d_jurnal['detail']
            );
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'detail_form', $content, true);

        return $html;
    }

    public function edit_form($id)
    {
        $m_jurnal = new \Model\Storage\Jurnal_model();
        $d_jurnal = $m_jurnal->where('id', $id)->with(['jurnal_trans', 'detail'])->first();

        $data = null;
        if ( $d_jurnal ) {
            $d_jurnal = $d_jurnal->toArray();

            $data = $d_jurnal;
        }

        $content['unit'] = $this->getUnit();
        $content['jurnal_trans'] = $this->getJurnalTrans();
        $content['supplier'] = $this->getSupplier();
        $content['perusahaan'] = $this->getPerusahaan();
        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'edit_form', $content, true);

        return $html;
    }

    public function save()
    {
        $params = $this->input->post('params');
        try {
            $m_jurnal = new \Model\Storage\Jurnal_model();

            $m_jurnal->tanggal = $params['tanggal'];
            $m_jurnal->unit = $params['unit'];
            $m_jurnal->jurnal_trans_id = $params['jurnal_trans_id'];
            $m_jurnal->save();

            $id = $m_jurnal->id;
            foreach ($params['detail'] as $k_det => $v_det) {
                $m_dj = new \Model\Storage\DetJurnal_model();

                $m_dj->id_header = $id;
                $m_dj->tanggal = $v_det['tanggal'];
                $m_dj->det_jurnal_trans_id = $v_det['det_jurnal_trans_id'];
                $m_dj->jurnal_trans_sumber_tujuan_id = isset($v_det['jurnal_trans_sumber_tujuan_id']) ? $v_det['jurnal_trans_sumber_tujuan_id'] : null;
                $m_dj->supplier = isset($v_det['supplier']) ? $v_det['supplier'] : null;
                $m_dj->perusahaan = $v_det['perusahaan'];
                $m_dj->keterangan = $v_det['keterangan'];
                $m_dj->nominal = $v_det['nominal'];
                if ( isset($v_det['supplier']) && !empty($v_det['supplier']) ) {
                    $m_dj->saldo = $v_det['nominal'];
                }
                $m_dj->save();
            }

            $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
            Modules::run( 'base/event/save', $m_jurnal, $deskripsi_log);

            $this->result['status'] = 1;
            $this->result['content'] = array('id' => $id);
            $this->result['message'] = 'Data berhasil di simpan.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    // public function edit()
    // {
    //     $params = $this->input->post('params');
    //     try {
    //         $id = $params['id'];

    //         $m_jt = new \Model\Storage\JurnalTrans_model();
    //         $m_jt->where('id', $id)->update(
    //             array(
    //                 'nama' => $params['nama'],
    //                 'mstatus' => 1
    //             )
    //         );

    //         $m_djt = new \Model\Storage\DetJurnalTrans_model();
    //         $m_djt->where('id_header', $id)->delete();

    //         foreach ($params['detail'] as $k_det => $v_det) {
    //             $m_djt = new \Model\Storage\DetJurnalTrans_model();
    //             $m_djt->id_header = $id;
    //             $m_djt->nama = $v_det['nama'];
    //             $m_djt->save();
    //         }

    //         $d_jt = $m_jt->where('id', $id)->first();

    //         $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
    //         Modules::run( 'base/event/update', $d_jt, $deskripsi_log);

    //         $this->result['status'] = 1;
    //         $this->result['content'] = array('id' => $id);
    //         $this->result['message'] = 'Data berhasil di update.';
    //     } catch (Exception $e) {
    //         $this->result['message'] = $e->getMessage();
    //     }

    //     display_json( $this->result );
    // }

    // public function delete()
    // {
    //     $params = $this->input->post('params');

    //     try {
    //         $id = $params['id'];

    //         $m_jt = new \Model\Storage\JurnalTrans_model();
    //         $m_jt->where('id', $id)->update(
    //             array(
    //                 'mstatus' => 0
    //             )
    //         );

    //         $d_jt = $m_jt->where('id', $id)->first();

    //         $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
    //         Modules::run( 'base/event/delete', $d_jt, $deskripsi_log);

    //         $this->result['status'] = 1;
    //         $this->result['message'] = 'Data berhasil di hapus.';
    //     } catch (Exception $e) {
    //         $this->result['message'] = $e->getMessage();
    //     }

    //     display_json( $this->result );
    // }
}