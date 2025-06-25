<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BukuBankPajak extends Public_Controller {

    private $url;
    private $pathView = 'report/buku_bank_pajak/';
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
                "assets/report/buku_bank_pajak/js/buku-bank-pajak.js",
            ));
            $this->add_external_css(array(
                "assets/select2/css/select2.min.css",
                "assets/report/buku_bank_pajak/css/buku-bank-pajak.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->hakAkses;
            $content['perusahaan'] = $this->getPerusahaan();
            $content['jurnal_trans'] = $this->getJurnalTrans();
            $content['title_menu'] = 'Laporan Buku Bank dan Pajak';

            // Load Indexx
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

    public function getJurnalTrans()
    {
        $m_jt = new \Model\Storage\JurnalTrans_model();
        $d_jt = $m_jt->where('mstatus', 1)->orderBy('nama', 'asc')->with(['detail'])->get();

        $data = null;
        if ( $d_jt->count() > 0 ) {
            $d_jt = $d_jt->toArray();

            foreach ($d_jt as $k_jt => $v_jt) {
                if ( stristr($v_jt['nama'], 'pajak') !== false || stristr($v_jt['nama'], 'bank') !== false ) {
                    $data[ $v_jt['id'] ] = $v_jt;
                }
            }
        }

        return $data;
    }

    public function getRekening()
    {
        $jurnal_trans_id = $this->input->post('jurnal_trans_id');

        try {
            $m_djt = new \Model\Storage\DetJurnalTrans_model();
            $d_djt = $m_djt->where('id_header', $jurnal_trans_id)->orderBy('nama', 'asc')->get();

            $data = null;
            if ( $d_djt->count() > 0 ) {
                $data = $d_djt->toArray();
            }

            $this->result['status'] = 1;
            $this->result['content'] = $data;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $jurnal_trans_id = $params['jurnal_trans_id'];
        $_rekening = $params['rekening'];

        $rekening = null;
        if ( in_array('all', $_rekening) ) {
            $m_djt = new \Model\Storage\DetJurnalTrans_model();
            $d_djt = $m_djt->select('id')->where('id_header', $jurnal_trans_id)->orderBy('nama', 'asc')->get();

            if ( $d_djt->count() > 0 ) {
                $d_djt = $d_djt->toArray();

                foreach ($d_djt as $k_djt => $v_djt) {
                    $rekening[] = $v_djt['id'];
                }
            }
        } else {
            $rekening = $_rekening;
        }

        $data = null;
        if ( !empty($rekening) ) {
            $m_jr = new \Model\Storage\JurnalReport_model();
            $d_jr = $m_jr->where('path_menu', 'report/BukuBankPajak')->first();

            $m_dj = new \Model\Storage\DetJurnal_model();
            $d_dj = $m_dj->whereIn('det_jurnal_trans_id', $rekening)->whereBetween('tanggal', [$start_date, $end_date])->with(['jurnal_trans_detail'])->get();

            if ( $d_dj->count() > 0 ) {
                $d_dj = $d_dj->toArray();

                foreach ($d_dj as $k_dj => $v_dj) {
                    $m_jm = new \Model\Storage\JurnalMapping_model();
                    $d_jm = $m_jm->where('jurnal_report_id', $d_jr->id)->where('det_jurnal_trans_id', $v_dj['det_jurnal_trans_id'])->first();

                    if ( $d_jm ) {
                        $posisi = $d_jm->posisi;

                        $key = str_replace('-', '', $v_dj['tanggal']).' | '.$v_dj['id'];

                        $data[$key] = array(
                            'tanggal' => $v_dj['tanggal'],
                            'bank' => $v_dj['jurnal_trans_detail']['nama'],
                            'keterangan' => $v_dj['keterangan'],
                            'debet' => ($posisi == 'db') ? $v_dj['nominal'] : 0,
                            'kredit' => ($posisi == 'cr') ? $v_dj['nominal'] : 0
                        );
                    }
                }
            }
        }

        if ( !empty($data) ) {
            ksort($data);
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }
}