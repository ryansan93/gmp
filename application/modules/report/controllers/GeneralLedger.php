<?php defined('BASEPATH') OR exit('No direct script access allowed');

class GeneralLedger extends Public_Controller {

    private $pathView = 'report/general_ledger/';
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
                'assets/select2/js/select2.min.js',
                "assets/report/general_ledger/js/general-ledger.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/general_ledger/css/general-ledger.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['perusahaan'] = $this->getPerusahaan();
            $content['title_menu'] = 'Laporan GL (Buku Besar)';

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

                $key = $d_perusahaan['kode_gabung_perusahaan'];
                $key_detail = strtoupper($d_perusahaan->perusahaan).' | '.$d_perusahaan->kode;

                $data[ $key ]['kode_gabung_perusahaan'] = $d_perusahaan['kode_gabung_perusahaan'];
                $data[ $key ]['detail'][ $key_detail ] = array(
                    'nama' => strtoupper($d_perusahaan->perusahaan),
                    'kode' => $d_perusahaan->kode,
                    'jenis_mitra' => $d_perusahaan->jenis_mitra
                );
            }

            ksort($data);
        }

        return $data;
    }

    public function getData($start_date, $end_date, $kode_gabung_perusahaan) {
        $sql_kode_gabung_perusahaan = "and dj.perusahaan in (select kode from perusahaan where kode_gabung_perusahaan = '".$kode_gabung_perusahaan."')";
        if ( $kode_gabung_perusahaan == 'all' ) {
            $sql_kode_gabung_perusahaan = null;
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                c.coa as no_coa,
                c.nama_coa,
                c.lap,
                c.coa_pos,
                case
                    when c.coa_pos = 'D' then
                        isnull(sb.saldo_awal, 0)
                end as saldo_awal_debet,
                case
                    when c.coa_pos = 'K' then
                        isnull(sb.saldo_awal, 0)
                end as saldo_awal_kredit,
                isnull(dj_asal.nominal, 0) as kredit,
                isnull(dj_tujuan.nominal, 0) as debet,
                case
                    when c.coa_pos = 'D' then
                        (( isnull(sb.saldo_awal, 0) + isnull(dj_tujuan.nominal, 0)) - isnull(dj_asal.nominal, 0))
                end as saldo_akhir_debet,
                case
                    when c.coa_pos = 'K' then
                        (( isnull(sb.saldo_awal, 0) + isnull(dj_tujuan.nominal, 0)) - isnull(dj_asal.nominal, 0))
                end as saldo_akhir_kredit,
                case
                    when c.lap = 'L' and c.coa_pos = 'D' then
                        (( isnull(sb.saldo_awal, 0) + isnull(dj_tujuan.nominal, 0)) - isnull(dj_asal.nominal, 0))
                end as lr_debet,
                case
                    when c.lap = 'L' and c.coa_pos = 'K' then
                        (( isnull(sb.saldo_awal, 0) + isnull(dj_tujuan.nominal, 0)) - isnull(dj_asal.nominal, 0))
                end as lr_kredit,
                case
                    when c.lap = 'N' and c.coa_pos = 'D' then
                        (( isnull(sb.saldo_awal, 0) + isnull(dj_tujuan.nominal, 0)) - isnull(dj_asal.nominal, 0))
                end as neraca_debet,
                case
                    when c.lap = 'N' and c.coa_pos = 'K' then
                        (( isnull(sb.saldo_awal, 0) + isnull(dj_tujuan.nominal, 0)) - isnull(dj_asal.nominal, 0))
                end as neraca_kredit
            from coa c
            left join
                (
                    select dj.coa_asal, sum(dj.nominal) as nominal 
                    from det_jurnal dj 
                    where 
                        dj.tanggal between '".$start_date."' and '".$end_date."'
                        ".$sql_kode_gabung_perusahaan."
                    group by dj.coa_asal
                ) dj_asal
                on
                    dj_asal.coa_asal = c.coa
            left join
                (
                    select dj.coa_tujuan, sum(dj.nominal) as nominal 
                    from det_jurnal dj 
                    where 
                        dj.tanggal between '".$start_date."' and '".$end_date."'
                        ".$sql_kode_gabung_perusahaan."
                    group by dj.coa_tujuan
                ) dj_tujuan
                on
                    dj_tujuan.coa_tujuan = c.coa
            left join
                (select * from saldo_bulanan where tanggal between '".$start_date."' and '".$end_date."') sb
                on
                    sb.coa = c.coa
            order by
                c.coa asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }
        
        return $data;
    }

    public function getLists() {
        $params = $this->input->get('params');

        $start_date = null;
        $end_date = null;

        $bulan = $params['bulan'];
        $tahun = substr($params['tahun'], 0, 4);
        $kode_gabung_perusahaan = $params['perusahaan'];

        $i = $bulan-1;

        $angka_bulan = (strlen($i+1) == 1) ? '0'.$i+1 : $i+1;

        $date = $tahun.'-'.$angka_bulan.'-01';
        $start_date = date("Y-m-d", strtotime($date));
        $end_date = date("Y-m-t", strtotime($date));

        $data = $this->getData( $start_date, $end_date, $kode_gabung_perusahaan );

        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }

    public function excryptParams()
    {
        $params = $this->input->post('params');

        try {
            $params_encrypt = exEncrypt( json_encode($params) );

            $this->result['status'] = 1;
            $this->result['content'] = $params_encrypt;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function exportExcel($params_encrypt)
    {
        $params = json_decode( exDecrypt($params_encrypt), true );

        $start_date = null;
        $end_date = null;

        $bulan = $params['bulan'];
        $tahun = substr($params['tahun'], 0, 4);
        $kode_gabung_perusahaan = $params['perusahaan'];

        $i = $bulan-1;

        $angka_bulan = (strlen($i+1) == 1) ? '0'.$i+1 : $i+1;

        $date = $tahun.'-'.$angka_bulan.'-01';
        $start_date = date("Y-m-d", strtotime($date));
        $end_date = date("Y-m-t", strtotime($date));

        $data = $this->getData( $start_date, $end_date, $kode_gabung_perusahaan );

        $content['data'] = $data;
        $content['periode'] = $tahun.'/'.$angka_bulan;
        $res_view_html = $this->load->view($this->pathView.'exportExcel', $content, true);
        $filename = "GENERAL_LEDGER_";

        // header("Content-type: application/xls");
        // header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        // header("Content-type:   application/x-msexcel; charset=utf-8");
        header("Content-type:   application/ms-excel; charset=utf-8");
        $filename = $filename.str_replace('-', '', $start_date).'_'.str_replace('-', '', $end_date).'.xls';
        header("Content-Disposition: attachment; filename=".$filename."");
        echo $res_view_html;
    }
}