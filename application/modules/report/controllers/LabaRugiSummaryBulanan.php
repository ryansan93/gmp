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
                sr.nama = 'LAPORAN LABA RUGI - SUMMARY BULANAN'
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

        // cetak_r( $params, 1);

        $perusahaan = $params['perusahaan'];
        $bulan = $params['bulan'];
        $tahun = substr($params['tahun'], 0, 4);

        // $sql_unit = "rdim_submit.kode_unit = '".$unit."' and";
        // if ( $unit == 'all' ) {
        //     $sql_unit = null;
        // }

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

                    // cetak_r( $v_srg['nama'] );
                    // cetak_r( $d_srgi );

                    foreach ($d_srgi as $k_srgi => $v_srgi) {
                        $nama_kolom = 'coa_'.$v_srgi['posisi_jurnal'];

                        $m_conf = new \Model\Storage\Conf();
                        $sql = "
                            select
                                srgi.item_report_id as item_report_id,
                                srgi.item_report_nama as item_report_nama,
                                case
                                    when srgi.posisi = 'debet' then
                                        sum(dj.nominal)
                                end as debet,
                                case
                                    when srgi.posisi = 'kredit' then
                                        sum(dj.nominal)
                                end as kredit,
                                srgi.urut
                            from
                                (
                                    select data.* from (
                                        select
                                            dj.id as id,
                                            dj.id_header as id_header,
                                            case
                                                when dj.periode is not null then
                                                    dj.periode
                                                else
                                                    dj.tanggal
                                            end as tanggal,
                                            dj.det_jurnal_trans_id,
                                            dj.jurnal_trans_sumber_tujuan_id,
                                            dj.supplier,
                                            dj.perusahaan,
                                            cast(dj.keterangan as varchar(250)) as keterangan,
                                            dj.nominal as nominal,
                                            dj.saldo,
                                            dj.ref_id,
                                            dj.asal,
                                            dj.coa_asal,
                                            dj.tujuan,
                                            dj.coa_tujuan,
                                            dj.unit,
                                            dj.pic,
                                            dj.tbl_name,
                                            dj.tbl_id as tbl_id
                                        from det_jurnal dj
                                        where
                                            dj.".$nama_kolom." = '".$v_srgi['no_coa']."' and
                                            '".$v_srgi['posisi_data']."' not like '%saldo%'

                                        union all

                                        select
                                            dj.id as id,
                                            dj.id_header as id_header,
                                            case
                                                when dj.periode is not null then
                                                    dj.periode
                                                else
                                                    dj.tanggal
                                            end as tanggal,
                                            dj.det_jurnal_trans_id,
                                            dj.jurnal_trans_sumber_tujuan_id,
                                            dj.supplier,
                                            dj.perusahaan,
                                            cast(dj.keterangan as varchar(250)) as keterangan,
                                            case
                                                when dj.coa_asal = '".$v_srgi['no_coa']."' then
                                                    (0-dj.nominal)
                                                else
                                                    dj.nominal 
                                            end as nominal,
                                            dj.saldo,
                                            dj.ref_id,
                                            dj.asal,
                                            '".$v_srgi['no_coa']."',
                                            dj.tujuan,
                                            '".$v_srgi['no_coa']."',
                                            dj.unit,
                                            dj.pic,
                                            dj.tbl_name,
                                            dj.tbl_id as tbl_id
                                        from det_jurnal dj
                                        where
                                            (dj.coa_asal = '".$v_srgi['no_coa']."' or dj.coa_tujuan = '".$v_srgi['no_coa']."') and
                                            '".$v_srgi['posisi_data']."' like '%saldo%'

                                        /*
                                        select
                                            max(dj.id) as id,
                                            max(dj.id_header) as id_header,
                                            case
                                                when dj.periode is not null then
                                                    dj.periode
                                                else
                                                    dj.tanggal
                                            end as tanggal,
                                            dj.det_jurnal_trans_id,
                                            dj.jurnal_trans_sumber_tujuan_id,
                                            dj.supplier,
                                            dj.perusahaan,
                                            cast(dj.keterangan as varchar(250)) as keterangan,
                                            max(dj.nominal) as nominal,
                                            dj.saldo,
                                            dj.ref_id,
                                            dj.asal,
                                            dj.coa_asal,
                                            dj.tujuan,
                                            dj.coa_tujuan,
                                            dj.unit,
                                            dj.pic,
                                            dj.tbl_name,
                                            max(dj.tbl_id) as tbl_id
                                        from det_jurnal dj
                                        where
                                            dj.".$nama_kolom." = '".$v_srgi['no_coa']."'
                                        group by
                                            dj.tanggal,
                                            dj.periode,
                                            dj.det_jurnal_trans_id,
                                            dj.jurnal_trans_sumber_tujuan_id,
                                            dj.supplier,
                                            dj.perusahaan,
                                            cast(dj.keterangan as varchar(250)),
                                            dj.saldo,
                                            dj.ref_id,
                                            dj.asal,
                                            dj.coa_asal,
                                            dj.tujuan,
                                            dj.coa_tujuan,
                                            dj.unit,
                                            dj.pic,
                                            dj.tbl_name
                                        */
                                    ) data
                                    where
                                        data.tanggal between '".$start_date."' and '".$end_date."'
                                ) dj
                            left join
                                (
                                    select srgi.*, ir.nama as item_report_nama from setting_report_group_item srgi
                                    right join
                                        item_report ir
                                        on
                                            srgi.item_report_id = ir.id
                                    where
                                        srgi.id_header = '".$v_srg['id']."'
                                ) srgi
                                on
                                    dj.".$nama_kolom." = srgi.no_coa and
                                    '".$v_srgi['posisi_jurnal']."' = srgi.posisi_jurnal 
                            left join
                                (
                                    select kode, kode_gabung_perusahaan from perusahaan group by kode, kode_gabung_perusahaan
                                ) p
                                on
                                    dj.perusahaan = p.kode
                            where
                                p.kode_gabung_perusahaan = '".$perusahaan."'
                            group by
                                srgi.item_report_id,
                                srgi.item_report_nama,
                                srgi.posisi,
                                srgi.urut
                            order by
                                srgi.urut asc
                        ";
                        // if ( $v_srgi['no_coa'] == '919900' ) {
                        //     // cetak_r($v_srgi);
                        //     cetak_r( $sql );
                        // }
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
                                    'debet' => 0,
                                    'kredit' => 0
                                );
                            }
        
                            foreach ($d_srgi as $key => $value) {
                                $key = $value['item_report_id'];

                                // if ( $value['item_report_nama'] == 'BIAYA GAJI KARYAWAN' ) {
                                //     cetak_r( $value );
                                // }

                                // if ( $key == 10 ) {
                                //     cetak_r( $d_srgi );
                                // }

                                if ( !isset($data[ $v_srg['id'] ]['detail'][ $key ]) ) {
                                    $data[ $v_srg['id'] ]['detail'][ $key ] = array(
                                        'item_report_id' => $value['item_report_id'],
                                        'item_report_nama' => $value['item_report_nama'],
                                        'debet' => $value['debet'],
                                        'kredit' => $value['kredit']
                                    );
                                } else {
                                    $data[ $v_srg['id'] ]['detail'][ $key ]['debet'] += $value['debet'];
                                    $data[ $v_srg['id'] ]['detail'][ $key ]['kredit'] += $value['kredit'];
                                }
        
                                if ( stristr($v_srg['nama'], 'PENJUALAN') !== false || stristr($v_srg['nama'], 'BEBAN POKOK PENJUALAN') !== false ) {
                                    $data['lr_kotor']['detail'][0]['debet'] += $value['debet'];
                                    $data['lr_kotor']['detail'][0]['kredit'] += $value['kredit'];
                                }

                                ksort($data[ $v_srg['id'] ]['detail']);
                            }
                        }
                    }
                }

                // $m_conf = new \Model\Storage\Conf();
                // $sql = "
                //     select * from
                //     (
                //         select
                //             case
                //                 when srgi_debet.posisi = 'debet' then
                //                     srgi_debet.item_report_id
                //                 when srgi_kredit.posisi = 'kredit' then
                //                     srgi_kredit.item_report_id
                //             end as item_report_id,
                //             case
                //                 when srgi_debet.posisi = 'debet' then
                //                     srgi_debet.item_report_nama
                //                 when srgi_kredit.posisi = 'kredit' then
                //                     srgi_kredit.item_report_nama
                //             end as item_report_nama,
                //             case
                //                 when srgi_debet.posisi = 'debet' then
                //                     sum(dj.nominal)
                //             end as debet,
                //             case
                //                 when srgi_kredit.posisi = 'kredit' then
                //                     sum(dj.nominal)
                //             end as kredit
                //         from (
                //                 select
                //                     max(dj.id) as id,
                //                     max(dj.id_header) as id_header,
                //                     dj.tanggal,
                //                     dj.det_jurnal_trans_id,
                //                     dj.jurnal_trans_sumber_tujuan_id,
                //                     dj.supplier,
                //                     dj.perusahaan,
                //                     cast(dj.keterangan as varchar(250)) as keterangan,
                //                     max(dj.nominal) as nominal,
                //                     dj.saldo,
                //                     dj.ref_id,
                //                     dj.asal,
                //                     dj.coa_asal,
                //                     dj.tujuan,
                //                     dj.coa_tujuan,
                //                     dj.unit,
                //                     dj.pic,
                //                     dj.tbl_name,
                //                     max(dj.tbl_id) as tbl_id
                //                 from det_jurnal dj
                //                 group by
                //                     dj.tanggal,
                //                     dj.det_jurnal_trans_id,
                //                     dj.jurnal_trans_sumber_tujuan_id,
                //                     dj.supplier,
                //                     dj.perusahaan,
                //                     cast(dj.keterangan as varchar(250)),
                //                     dj.saldo,
                //                     dj.ref_id,
                //                     dj.asal,
                //                     dj.coa_asal,
                //                     dj.tujuan,
                //                     dj.coa_tujuan,
                //                     dj.unit,
                //                     dj.pic,
                //                     dj.tbl_name
                //             ) dj
                //         left join
                //             (
                //                 select srgi.*, ir.nama as item_report_nama from setting_report_group_item srgi
                //                 right join
                //                     item_report ir
                //                     on
                //                         srgi.item_report_id = ir.id
                //                 where
                //                     srgi.posisi = 'kredit'
                //             ) srgi_kredit
                //             on
                //                 dj.coa_tujuan = srgi_kredit.no_coa 
                //                 or
                //                 dj.coa_asal = srgi_kredit.no_coa
                //         left join
                //             (
                //                 select srgi.*, ir.nama as item_report_nama from setting_report_group_item srgi
                //                 right join
                //                     item_report ir
                //                     on
                //                         srgi.item_report_id = ir.id
                //                 where
                //                     srgi.posisi = 'debet'
                //             ) srgi_debet
                //             on
                //                 dj.coa_tujuan = srgi_debet.no_coa 
                //                 or
                //                 dj.coa_asal = srgi_debet.no_coa
                //         left join
                //             (
                //                 select kode, kode_gabung_perusahaan from perusahaan group by kode, kode_gabung_perusahaan
                //             ) p
                //             on
                //                 dj.perusahaan = p.kode
                //         where
                //             p.kode_gabung_perusahaan = '".$perusahaan."' and
                //             dj.tanggal between '".$start_date."' and '".$end_date."' and
                //             (srgi_kredit.id_header = ".$v_srg['id']." or srgi_debet.id_header = ".$v_srg['id'].")
                //         group by
                //             srgi_debet.item_report_id,
                //             srgi_kredit.item_report_id,
                //             srgi_debet.item_report_nama,
                //             srgi_kredit.item_report_nama,
                //             srgi_debet.posisi,
                //             srgi_kredit.posisi
                //     ) _data
                //     where
                //         _data.item_report_id is not null
                // ";

                // $d_srgi = $m_conf->hydrateRaw( $sql );

                // if ( $d_srgi->count() > 0 ) {
                //     if ( !isset($data[ $v_srg['id'] ]) ) {
                //         $data[ $v_srg['id'] ] = array(
                //             'id' => $v_srg['id'],
                //             'nama' => $v_srg['nama'],
                //             'detail' => null
                //         );
                //     }

                //     $d_srgi = $d_srgi->toArray();

                //     if ( !isset($data['lr_kotor']) ) {
                //         $data['lr_kotor']['nama'] = 'LABA KOTOR';
                //         $data['lr_kotor']['detail'][0] = array(
                //             'debet' => 0,
                //             'kredit' => 0
                //         );
                //     }

                //     foreach ($d_srgi as $key => $value) {
                //         $data[ $v_srg['id'] ]['detail'][ $value['item_report_id'] ] = array(
                //             'item_report_id' => $value['item_report_id'],
                //             'item_report_nama' => $value['item_report_nama'],
                //             'debet' => $value['debet'],
                //             'kredit' => $value['kredit']
                //         );

                //         if ( stristr($v_srg['nama'], 'PENJUALAN') !== false || stristr($v_srg['nama'], 'BEBAN POKOK PENJUALAN') !== false ) {
                //             $data['lr_kotor']['detail'][0]['debet'] += $value['debet'];
                //             $data['lr_kotor']['detail'][0]['kredit'] += $value['kredit'];
                //         }
                //     }
                // }
            }
        }

        $content['data'] = $data;
        $html = $this->load->view($this->path.'list', $content, TRUE);

        echo $html;
    }
}