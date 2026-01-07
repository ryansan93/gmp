<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SaldoBank extends Public_Controller {

    private $pathView = 'accounting/saldo_bank/';
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
                "assets/jquery/easy-autocomplete/jquery.easy-autocomplete.min.js",
                "assets/select2/js/select2.min.js",
                "assets/accounting/saldo_bank/js/saldo-bank.js",
            ));
            $this->add_external_css(array(
                "assets/jquery/easy-autocomplete/easy-autocomplete.min.css",
                "assets/jquery/easy-autocomplete/easy-autocomplete.themes.min.css",
                "assets/select2/css/select2.min.css",
                "assets/accounting/saldo_bank/css/saldo-bank.css",
            ));

            $data = $this->includes;

            $content['akses'] = $this->hakAkses;
            $content['riwayat'] = $this->riwayat();
            $content['add_form'] = $this->addForm();
            $content['title_panel'] = 'Saldo Bank';

            // Load Indexx
            $data['title_menu'] = 'Saldo Bank';
            $data['view'] = $this->load->view($this->pathView . 'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function loadForm()
    {
        $tanggal = $this->input->get('tanggal');
        $resubmit = $this->input->get('resubmit');

        $html = null;
        if ( !empty($tanggal) && empty($resubmit) ) {
            $html = $this->viewForm($tanggal);
        } else if ( !empty($tanggal) && !empty($resubmit) ) {
            $html = $this->editForm($tanggal);
        } else {
            $html = $this->addForm();
        }

        echo $html;
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                sb.tanggal,
                sum(sb.saldo_akhir) as saldo
            from saldo_bank sb
            where
                sb.tanggal between '".$start_date."' and '".$end_date."'
            group by
                sb.tanggal
            order by
                sb.tanggal desc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView . 'list', $content, true);

        echo $html;
    }

    public function riwayat() {
        $start_date = substr(date('Y-m-d'), 0, 7).'-01';
        $end_date = date("Y-m-t", strtotime($start_date));

        $content['start_date'] = $start_date;
        $content['end_date'] = $end_date;
        $content['akses'] = $this->hakAkses;
        $html = $this->load->view($this->pathView . 'riwayat', $content, TRUE);

        return $html;
    }

    public function addForm()
    {
        $m_coa = new \Model\Storage\Coa_model();

        $content['bank'] = $m_coa->getDataBank();
        $content['akses'] = $this->hakAkses;
        $html = $this->load->view($this->pathView . 'addForm', $content, TRUE);

        return $html;
    }

    public function viewForm($tanggal)
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                sb.*,
                sb_total.total,
                c.nama_coa
            from saldo_bank sb
            left join
                coa c
                on
                    c.coa = sb.coa
            left join
                (
                    select sum(sb.saldo_akhir) as total, sb.tanggal from saldo_bank sb group by sb.tanggal
                ) sb_total
                on
                    sb_total.tanggal = sb.tanggal
            where
                sb.tanggal between '".$tanggal."' and '".$tanggal."'
            order by
                c.coa asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $content['akses'] = $this->hakAkses;
        $content['data'] = $data;

        $html = $this->load->view($this->pathView . 'viewForm', $content, TRUE);

        return $html;
    }

    public function editForm($tanggal)
    {
        $m_coa = new \Model\Storage\Coa_model();

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                sb.*,
                sb_total.total,
                c.nama_coa
            from saldo_bank sb
            left join
                coa c
                on
                    c.coa = sb.coa
            left join
                (
                    select sum(sb.saldo_akhir) as total, sb.tanggal from saldo_bank sb group by sb.tanggal
                ) sb_total
                on
                    sb_total.tanggal = sb.tanggal
            where
                sb.tanggal between '".$tanggal."' and '".$tanggal."'
            order by
                c.coa asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $content['bank'] = $m_coa->getDataBank();
        $content['data'] = $data;

        $html = $this->load->view($this->pathView . 'editForm', $content, TRUE);

        return $html;
    }

    public function cekDataSaldo() {
        $params = $this->input->post('params');

        try {
            $tanggal_old = isset($params['tanggal_old']) ? $params['tanggal_old'] : null;
            $tanggal = $params['tanggal'];

            $sql_tgl_old = null;
            if ( !empty($tanggal_old) ) {
                $sql_tgl_old = "where tanggal <> '".$tanggal_old."'";
            }

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                select
                    sb.*
                from
                (
                    select * from saldo_bank ".$sql_tgl_old."
                )sb
                where
                    sb.tanggal between '".$tanggal."' and '".$tanggal."'
            ";
            $d_conf = $m_conf->hydrateRaw( $sql );

            $status = 0;
            $message = null;
            if ( $d_conf->count() > 0 ) {
                $status = 1;
                $message = 'Data saldo bank tanggal <b>'.strtoupper(tglIndonesia($tanggal, '-', ' ')).'</b> sudah di submit.';
            }

            $this->result['status'] = 1;
            $this->result['content'] = array('status' => $status);
            $this->result['message'] = $message;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function save()
    {
        $params = $this->input->post('params');

        try {
            $tanggal = $params['tanggal'];
            foreach ($params['detail'] as $key => $value) {
                $m_sb = new \Model\Storage\SaldoBank_model();
                $m_sb->coa = $value['coa'];
                $m_sb->tanggal = $tanggal;
                $m_sb->saldo_akhir = $value['saldo'];
                $m_sb->save();
    
                $deskripsi_log = 'di-submit oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/save', $m_sb, $deskripsi_log, null );
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di simpan.';
            $this->result['content'] = array('tanggal' => $tanggal);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function edit()
    {
        $params = $this->input->post('params');

        try {
            $tanggal_old = $params['tanggal_old'];
            $tanggal = $params['tanggal'];
            foreach ($params['detail'] as $key => $value) {
                $m_sb = new \Model\Storage\SaldoBank_model();
                $m_sb->where('tanggal', $tanggal_old)->where('coa', $value['coa'])->update(
                    array(
                        'coa' => $value['coa'],
                        'tanggal' => $tanggal,
                        'saldo_akhir' => $value['saldo'],
                    )
                );

                $d_sb = $m_sb->where('tanggal', $tanggal)->where('coa', $value['coa'])->first();
    
                $deskripsi_log = 'di-update oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/update', $d_sb, $deskripsi_log, null );
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di update.';
            $this->result['content'] = array('tanggal' => $tanggal);
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function delete()
    {
        $params = $this->input->post('params');

        try {            
            $tanggal = $params['tanggal'];

            $m_sb = new \Model\Storage\SaldoBank_model();
            $d_sb = $m_sb->where('tanggal', $tanggal)->get()->toArray();

            foreach ($d_sb as $key => $value) {
                $m_sb = new \Model\Storage\SaldoBank_model();
                $_d_sb = $m_sb->where('id', $value['id'])->first();

                $m_sb->where('id', $value['id'])->delete();

                $deskripsi_log = 'di-hapus oleh ' . $this->userdata['detail_user']['nama_detuser'];
                Modules::run( 'base/event/delete', $_d_sb, $deskripsi_log, null );
            }

            $this->result['status'] = 1;
            $this->result['message'] = 'Data berhasil di hapus.';
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }
}