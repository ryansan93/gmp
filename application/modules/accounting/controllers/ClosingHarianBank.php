<?php defined('BASEPATH') or exit('No direct script access allowed');

class ClosingHarianBank extends Public_Controller
{
    private $pathView = 'accounting/closing_harian_bank/';
    private $url;
    private $akses;
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->akses = hakAkses($this->url);
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index()
    {
        if ( $this->akses['a_view'] == 1 ) {
            // $this->set_title('Berita Acara Serah Terima Titip Budidaya');
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                'assets/accounting/closing_harian_bank/js/closing-harian-bank.js')
            );
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/accounting/closing_harian_bank/css/closing-harian-bank.css')
            );
            $data = $this->includes;

            $content['akses'] = $this->akses;
            $content['title_panel'] = 'Closing Harian Bank';

            // Load Indexx
            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();

            $data['title_menu'] = 'Closing Harian Bank';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);

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
            $html = $this->viewForm($id);
        } else if ( !empty($id) && $edit == 'edit' ) {
            // NOTE: edit data BASTTB (ajax)
            $html = $this->editForm($id);
        }else{
            $html = $this->addForm();
        }

        echo $html;
    }

    public function getLists () {
        $params = $this->input->get('params');

        $coa = $params['bank'];
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                sb.*,
                c.nama_coa
            from saldo_bank sb
            left join
                coa c
                on
                    sb.coa = c.coa
            where
                sb.coa = '".$coa."' and
                sb.tanggal between '".$start_date."' and '".$end_date."'
            order by
                sb.tanggal
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list', $content, TRUE);

        echo $html;
    }

    public function getBank() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "select * from coa c where nama_coa like '%bca%' order by nama_coa asc";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function riwayat () {
        $content['bank'] = $this->getBank();
        $html = $this->load->view($this->pathView . 'riwayat', $content, TRUE);

        return $html;
    }

    public function addForm () {
        $content['bank'] = $this->getBank();
        $html = $this->load->view($this->pathView . 'addForm', $content, TRUE);

        return $html;
    }

    public function viewForm ($id) {
        $coa = null;
        $tanggal = null;

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select 
                sb.id,
                sb.coa,
                sb.tanggal,
                isnull(sb.saldo_awal, 0) as saldo_awal,
                isnull(sb.saldo_akhir, 0) as saldo_akhir,
                c.nama_coa
            from saldo_bank sb
            left join
                coa c
                on
                    sb.coa = c.coa
            where
                sb.id = '".$id."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        $detail = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray()[0];

            $detail = $this->_getDataHarian( $data['coa'], $data['tanggal'] );
        }

        $content['data'] = $data;
        $content['detail'] = !empty($detail) ? $detail['data'] : null;
        $html = $this->load->view($this->pathView . 'viewForm', $content, TRUE);

        return $html;
    }

    public function getDataHarian() {
        $params = $this->input->post('params');

        try {
            $coa = $params['bank'];
            $tanggal = $params['tanggal'];

            $data = $this->_getDataHarian( $coa, $tanggal );
            
            $content['data'] = $data['data'];
            $html = $this->load->view($this->pathView.'listHarian', $content, TRUE);

            $this->result['status'] = 1;
            $this->result['content'] = array(
                'save' => $data['save'],
                'html' => $html
            );
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function _getDataHarian($coa, $tanggal) {
        $save = 1;

        $data = null;
        $data[0] = array(
            'id' => null,
            'tanggal' => $tanggal,
            'keterangan' => 'SALDO AWAL',
            'debit' => 0,
            'kredit' => 0,
            'nama_jurnal_trans' => 'SALDO AWAL'
        );

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select top 1 sb.* from saldo_bank sb
            where
                sb.coa = '".$coa."' and
                sb.tanggal = '".$tanggal."'
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray()[0];

            if ( !empty($d_conf['saldo_akhir']) ) {
                $save = 0;
            }

            $data[0] = array(
                'id' => $d_conf['id'],
                'tanggal' => $tanggal,
                'keterangan' => 'SALDO AWAL',
                'debit' => $d_conf['saldo_awal'],
                'kredit' => 0,
                'nama_jurnal_trans' => 'SALDO AWAL'
            );
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                dj.id, 
                dj.tanggal, 
                dj.keterangan,
                CASE 
                    WHEN djt.tujuan_coa = '".$coa."' THEN
                        dj.nominal 
                    ELSE
                        0
                END as debit,
                CASE 
                    WHEN djt.sumber_coa = '".$coa."' THEN
                        dj.nominal 
                    ELSE
                        0
                END as kredit,
                djt.nama_aktif as nama_jurnal_trans
            from det_jurnal dj 
            left join
                (
                    select 
                        djt.id, 
                        djt.sumber_coa, 
                        djt.tujuan_coa, 
                        djt_aktif.nama as nama_aktif 
                    from det_jurnal_trans djt
                    right join
                        (
                            select djt.* from det_jurnal_trans djt
                            right join
                                jurnal_trans jt
                                on
                                    djt.id_header = jt.id
                            where
                                jt.mstatus = 1
                        ) djt_aktif
                        on
                            djt.sumber_coa = djt_aktif.sumber_coa and
                            djt.tujuan_coa = djt_aktif.tujuan_coa
                    left join
                        coa c_sumber
                        on
                            djt.sumber_coa = c_sumber.coa 
                    left join
                        coa c_tujuan
                        on
                            djt.tujuan_coa = c_tujuan.coa
                    where
                        (c_sumber.nama_coa like '%bca%' or c_tujuan.nama_coa like '%bca%')
                    group by
                        djt.id, 
                        djt.sumber_coa, 
                        djt.tujuan_coa, 
                        djt_aktif.nama  
                ) djt
                on
                    dj.det_jurnal_trans_id = djt.id
            where
                dj.nominal > 0 and
                (djt.sumber_coa = '".$coa."' or djt.tujuan_coa = '".$coa."') and
                dj.tanggal between '".$tanggal."' and '".$tanggal."'
            order by
                dj.tanggal asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $d_conf = $d_conf->toArray();

            foreach ($d_conf as $key => $value) {
                array_push( $data, $value );
            }
        }

        $_data = array(
            'save' => $save,
            'data' => $data
        );

        return $_data;
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $coa = $params['bank'];
            $tanggal = $params['tanggal'];
            $saldo_akhir = (isset($params['saldo_akhir'])) ? $params['saldo_akhir'] : 0;

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select top 1
                    sb.id
                from saldo_bank sb
                where
                    sb.tanggal = '".$tanggal."' and
                    sb.coa = '".$coa."'
            ";
            $d_sb = $m_conf->hydrateRaw( $sql );

            if ( $d_sb->count() > 0 ) {
                $id = $d_sb->toArray()[0]['id'];

                $m_sb1 = new \Model\Storage\SaldoBank_model();
                $now = $m_sb1->getDate();

                $waktu = $now['waktu'];

                $m_sb1->where('id', $id)->update(
                    array(
                        'saldo_akhir' => $saldo_akhir
                    )
                );

                $d_sb1 = $m_sb1->where('id', $id)->first();

                $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $d_sb1, $deskripsi_log );

                $m_sb2 = new \Model\Storage\SaldoBank_model();
                $m_sb2->tgl_trans = $waktu;
                $m_sb2->coa = $coa;
                $m_sb2->tanggal = next_date( $tanggal );
                $m_sb2->saldo_awal = $saldo_akhir;
                $m_sb2->saldo_akhir = null;
                $m_sb2->save();

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $m_sb2, $deskripsi_log );
            } else {
                $m_sb1 = new \Model\Storage\SaldoBank_model();
                $now = $m_sb1->getDate();

                $waktu = $now['waktu'];

                $m_sb1->tgl_trans = $waktu;
                $m_sb1->coa = $coa;
                $m_sb1->tanggal = $tanggal;
                $m_sb1->saldo_awal = 0;
                $m_sb1->saldo_akhir = $saldo_akhir;
                $m_sb1->save();

                $id = $m_sb1->id;

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $m_sb1, $deskripsi_log );

                $m_sb2 = new \Model\Storage\SaldoBank_model();
                $m_sb2->tgl_trans = $waktu;
                $m_sb2->coa = $coa;
                $m_sb2->tanggal = next_date( $tanggal );
                $m_sb2->saldo_awal = $saldo_akhir;
                $m_sb2->saldo_akhir = null;
                $m_sb2->save();

                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $m_sb2, $deskripsi_log );
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di tutup.';
            $this->result['content'] = array('id' => $id);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}