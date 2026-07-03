<?php defined('BASEPATH') OR exit('No direct script access allowed');

class LabaRugiSummaryBulanan extends Public_Controller {

    private $path = 'report/laba_rugi_summary_bulanan/';
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
                "assets/report/laba_rugi_summary_bulanan/js/laba-rugi-summary-bulanan.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/laba_rugi_summary_bulanan/css/laba-rugi-summary-bulanan.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['perusahaan'] = $this->getPerusahaan();
            $content['title_menu'] = 'Laba Rugi Summary Bulanan';

            // Load Indexx
            $data['view'] = $this->load->view($this->path.'index', $content, TRUE);
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

    public function getSettingReportGroup()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select srg.* from setting_report_group srg
            right join
                setting_report sr
                on
                    srg.id_header = sr.id
            where
                sr.nama = 'LABA RUGI UNIT'
        ";
        $d_srg = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_srg->count() > 0 ) {
            $data = $d_srg->toArray();
        }

        return $data;
    }

    public function getSettingReportGroupItem($srg_id)
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select srg.* from setting_report_group srg
            right join
                setting_report sr
                on
                    srg.id_header = sr.id
            where
                sr.nama = 'LAPORAN LABA RUGI - SUMMARY BULANAN'
        ";
        $d_srg = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_srg->count() > 0 ) {
            $data = $d_srg->toArray();
        }

        return $data;
    }

    public function getData()
    {
        $params = $this->input->get('params');

        $perusahaan = $params['perusahaan'];
        $bulan = $params['bulan'];
        $tahun = substr($params['tahun'], 0, 4);

        $bulan_awal = 1;
        $bulan_akhir = 12;

        if ( $bulan != 'all' ) {
            $bulan_awal = $bulan;
            $bulan_akhir = $bulan;
        }

        $angka_bulan_awal = (strlen($bulan_awal) == 1) ? '0'.$bulan_awal : $bulan_awal;
        $angka_bulan_akhir = (strlen($bulan_akhir) == 1) ? '0'.$bulan_akhir : $bulan_akhir;

        $date_awal = $tahun.'-'.$angka_bulan_awal.'-01';
        $date_akhir = $tahun.'-'.$angka_bulan_akhir.'-01';

        $start_date = date("Y-m-d", strtotime($date_awal)).' 00:00:00';
        $end_date = date("Y-m-t", strtotime($date_akhir)).' 23:59:59';

        $srg = $this->getSettingReportGroup();

        $data = null;
        if ( !empty($srg) ) {
            foreach ($srg as $k_srg => $v_srg) {
                $m_conf = new \Model\Storage\Conf();
                $sql = "
                    select * from setting_report_group_item srgi where srgi.id_header = '".$v_srg['id']."'
                ";
                $d_srgi = $m_conf->hydrateRaw( $sql );

                if ( $d_srgi->count() > 0 ) {
                    $d_srgi = $d_srgi->toArray();

                    foreach ($d_srgi as $k_srgi => $v_srgi) {
                        $m_conf = new \Model\Storage\Conf();
                        $sql = "
                            select
                                srgi.item_report_id as item_report_id,
                                srgi.item_report_nama as item_report_nama,
                                case
                                    when srgi.posisi like 'debet' then
                                        case
                                            when round(sum(dj.debet) - sum(dj.kredit), 0) < 0 then
                                                abs(round(sum(dj.debet) - sum(dj.kredit), 0))
                                            else
                                                0-round(sum(dj.debet) - sum(dj.kredit), 0)
                                        end
                                    else
                                        case
                                            when round(sum(dj.debet) - sum(dj.kredit), 0) > 0 then
                                                0-round(sum(dj.debet) - sum(dj.kredit), 0)
                                            else
                                                abs(round(sum(dj.debet) - sum(dj.kredit), 0))
                                        end
                                end as nominal,
                                -- round(sum(dj.debet) - sum(dj.kredit), 0) as nominal,
                                srgi.urut
                            from
                                (
                                    select data.* from (
                                        select
                                            dj.id as id,
                                            case
                                                when dj.periode is not null then
                                                    dj.periode
                                                else
                                                    dj.tanggal
                                            end as tanggal,
                                            dj.perusahaan,
                                            case
                                                when dj.coa_tujuan = '".$v_srgi['no_coa']."' then
                                                    dj.nominal
                                                else
                                                    0
                                            end as debet,
                                            case
                                                when dj.coa_asal = '".$v_srgi['no_coa']."' then
                                                    dj.nominal
                                                else
                                                    0
                                            end as kredit,
                                            case
                                                when dj.coa_asal = '".$v_srgi['no_coa']."' then
                                                    dj.coa_asal
                                                else
                                                    dj.coa_tujuan
                                            end as coa,
                                            case
                                                when dj.coa_asal = '".$v_srgi['no_coa']."' then
                                                    dj.unit
                                                else
                                                    case
                                                        when dj.unit_tujuan is not null then
                                                            dj.unit_tujuan
                                                        else
                                                            dj.unit
                                                    end
                                            end as unit
                                        from det_jurnal dj
                                        where
                                            (dj.coa_asal = '".$v_srgi['no_coa']."' or dj.coa_tujuan = '".$v_srgi['no_coa']."') and
                                            dj.tanggal between '".$start_date."' and '".$end_date."'
                                    ) data
                                ) dj
                            left join
                                (
                                    select srgi.*, ir.nama as item_report_nama from setting_report_group_item srgi
                                    left join
                                        item_report ir
                                        on
                                            srgi.item_report_id = ir.id
                                    where
                                        srgi.id_header = '".$v_srg['id']."'
                                ) srgi
                                on
                                    dj.coa = srgi.no_coa
                            left join
                                (
                                    select kode, kode_gabung_perusahaan from perusahaan group by kode, kode_gabung_perusahaan
                                ) p
                                on
                                    dj.perusahaan = p.kode
                            -- where p.kode_gabung_perusahaan = '".$perusahaan."'
                            group by
                                srgi.item_report_id,
                                srgi.item_report_nama,
                                srgi.posisi,
                                srgi.urut
                            order by
                                srgi.urut asc
                        ";
                        $d_srgi = $m_conf->hydrateRaw( $sql );

                        if ( $d_srgi->count() > 0 ) {
                            if ( !isset($data[ $v_srg['id'] ]) ) {
                                $data[ $v_srg['id'] ] = array(
                                    'id' => $v_srg['id'],
                                    'nama' => $v_srg['nama'],
                                    'detail' => null
                                );
                            }
        
                            $d_srgi = $d_srgi->toArray();
        
                            if ( !isset($data['lr_kotor']) ) {
                                $data['lr_kotor']['nama'] = 'LABA KOTOR';
                                $data['lr_kotor']['detail'][0] = array(
                                    'nominal' => 0
                                );
                            }
        
                            foreach ($d_srgi as $key => $value) {
                                $key = $value['item_report_id'];

                                if ( !isset($data[ $v_srg['id'] ]['detail'][ $key ]) ) {
                                    $data[ $v_srg['id'] ]['detail'][ $key ] = array(
                                        'item_report_id' => $value['item_report_id'],
                                        'item_report_nama' => $value['item_report_nama'],
                                        'nominal' => $value['nominal']
                                    );
                                } else {
                                    $data[ $v_srg['id'] ]['detail'][ $key ]['nominal'] += $value['nominal'];
                                }
        
                                if ( stristr($v_srg['nama'], 'PENJUALAN') !== false || stristr($v_srg['nama'], 'BEBAN POKOK PENJUALAN') !== false ) {
                                    $data['lr_kotor']['detail'][0]['nominal'] += $value['nominal'];
                                }

                                ksort($data[ $v_srg['id'] ]['detail']);
                            }
                        }
                    }
                }
            }
        }

        $content['data'] = $data;
        $html = $this->load->view($this->path.'list', $content, TRUE);

        echo $html;
    }
}