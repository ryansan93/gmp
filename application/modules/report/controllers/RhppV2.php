<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class RhppV2 extends Public_Controller {

    private $pathView = 'report/rhpp_v2/';
    private $url;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
    }

    public function index()
    {
        $akses = hakAkses($this->url);
        // if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                "assets/report/rhpp_v2/js/rhpp-v2.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                'assets/report/rhpp_v2/css/rhpp-v2.css',
            ));

            $data = $this->includes;

            $m_wilayah = new \Model\Storage\Wilayah_model();

            $content['akses'] = $akses;
            $content['title_menu'] = 'RHPP v2';
            $content['unit'] = $m_wilayah->getDataUnit();

            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        // } else {
        //     showErrorAkses();
        // }
    }

    private function _getData($unit, $start_date, $end_date)
    {
        $sql_unit = null;
        if ( stristr('all', $unit) === FALSE ) {
            $sql_unit = "and data.unit = '".$unit."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                data.*
            from
            (
                select
                    ts.tgl_tutup,
                    w.kode as unit,
                    r.mitra,
                    cast(r.noreg as varchar(200)) as noreg,
                    cast(r.kandang as varchar(200)) as kandang,
                    r.tgl_docin,
                    r.populasi,
                    r.jml_panen_ekor,
                    r.jml_panen_kg,
                    r.bb,
                    r.fcr,
                    r.deplesi,
                    r.rata_umur,
                    case when isnull(r.rata_umur, 0) > 0 then ((r.bb - isnull(td.bb, 0)) * 1000) / r.rata_umur else 0 end as adg,
                    r.ip,
                    (isnull(r.tot_penjualan_ayam, 0) - isnull(r.tot_pembelian_sapronak, 0)) as selisih_budidaya,
                    r.bonus_pasar,
                    r.bonus_kematian,
                    r.bonus_insentif_fcr,
                    r.total_bonus_insentif_listrik,
                    isnull(lpg.jumlah, 0) as insentif_lpg,
                    r.pdpt_peternak_belum_pajak as total_pendapatan_plasma,
                    r_inti.lr_inti as total_pendapatan_inti
                from rhpp r
                left join
                    tutup_siklus ts
                    on
                        r.id_ts = ts.id
                left join
                    rdim_submit rs
                    on
                        rs.noreg = r.noreg
                left join
                    kandang k
                    on
                        rs.kandang = k.id
                left join
                    wilayah w
                    on
                        w.id = k.unit
                left join
                    (select * from rhpp where jenis = 'rhpp_inti') r_inti
                    on
                        r.id_ts = r_inti.id_ts
                left join
                    (
                        select id_header, sum(jumlah) as jumlah from rhpp_bonus where keterangan like '%LPG%' group by id_header
                    ) lpg
                    on
                        lpg.id_header = r.id
                left join
                    (
                        select od1.* from order_doc od1
                        right join
                            (select max(version) as version, noreg from order_doc group by noreg) od2
                            on
                                od1.noreg = od2.noreg and od1.version = od2.version
                    ) od
                    on
                        od.noreg = r.noreg
                left join
                    (
                        select td1.* from terima_doc td1
                        right join
                            (select max(version) as version, no_order from terima_doc group by no_order) td2
                            on
                                td1.no_order = td2.no_order and td1.version = td2.version
                    ) td
                    on
                        td.no_order = od.no_order
                where
                    r.jenis = 'rhpp_plasma' and
                    not exists (select * from rhpp_group_noreg where noreg = r.noreg)

                union all

                select
                    rgh.tgl_submit as tgl_tutup,
                    rgn.kode as unit,
                    rgh.mitra,
                    rgn.noreg_list as noreg,
                    rgn.kandang,
                    rgn.tgl_docin,
                    rgn.populasi,
                    rg.jml_panen_ekor,
                    rg.jml_panen_kg,
                    rg.bb,
                    rg.fcr,
                    rg.deplesi,
                    rg.rata_umur,
                    case when isnull(rg.rata_umur, 0) > 0 then ((rg.bb - isnull(rgn.bb_first, 0)) * 1000) / rg.rata_umur else 0 end as adg,
                    rg.ip,
                    (isnull(rg.tot_penjualan_ayam, 0) - isnull(rg.tot_pembelian_sapronak, 0)) as selisih_budidaya,
                    rg.bonus_pasar,
                    rg.bonus_kematian,
                    rg.bonus_insentif_fcr,
                    rg.total_bonus_insentif_listrik,
                    isnull(lpg.jumlah, 0) as insentif_lpg,
                    rg.pdpt_peternak_belum_pajak as total_pendapatan_plasma,
                    rg_inti.lr_inti as total_pendapatan_inti
                from rhpp_group rg
                left join
                    rhpp_group_header rgh
                    on
                        rg.id_header = rgh.id
                left join
                    (
                        select DISTINCT
                            _rgn.id_header,
                            min(_rgn.tgl_docin) as tgl_docin,
                            sum(_rgn.populasi) as populasi,
                            w.kode,
                            kandang = substring ((
                                select ', '+cast(rgn.kandang as varchar(max)) from rhpp_group_noreg rgn
                                where
                                    rgn.id_header = _rgn.id_header
                                order by
                                    rgn.kandang
                                FOR XML path('')
                            , elements), 3, 500),
                            noreg_list = substring ((
                                select ', '+cast(rgn.noreg as varchar(max)) from rhpp_group_noreg rgn
                                where
                                    rgn.id_header = _rgn.id_header
                                order by
                                    rgn.noreg
                                FOR XML path('')
                            , elements), 3, 500),
                            sum(isnull(td.bb, 0) * _rgn.populasi) / nullif(sum(_rgn.populasi), 0) as bb_first
                        from rhpp_group_noreg _rgn
                        left join
                            rdim_submit rs
                            on
                                rs.noreg = _rgn.noreg
                        left join
                            kandang k
                            on
                                rs.kandang = k.id
                        left join
                            wilayah w
                            on
                                w.id = k.unit
                        left join
                            (
                                select od1.* from order_doc od1
                                right join
                                    (select max(version) as version, noreg from order_doc group by noreg) od2
                                    on
                                        od1.noreg = od2.noreg and od1.version = od2.version
                            ) od
                            on
                                od.noreg = _rgn.noreg
                        left join
                            (
                                select td1.* from terima_doc td1
                                right join
                                    (select max(version) as version, no_order from terima_doc group by no_order) td2
                                    on
                                        td1.no_order = td2.no_order and td1.version = td2.version
                            ) td
                            on
                                td.no_order = od.no_order
                        group by
                            _rgn.id_header,
                            w.kode
                    ) rgn
                    on
                        rgn.id_header = rg.id
                left join
                    (select * from rhpp_group where jenis = 'rhpp_inti') rg_inti
                    on
                        rg.id_header = rg_inti.id_header
                left join
                    (
                        select id_header, sum(jumlah) as jumlah from rhpp_group_bonus where keterangan like '%LPG%' group by id_header
                    ) lpg
                    on
                        lpg.id_header = rg.id
                where
                    rg.jenis = 'rhpp_plasma'
            ) data
            where
                data.tgl_tutup between '".$start_date."' and '".$end_date."'
                ".$sql_unit."
            order by
                data.unit asc,
                data.mitra asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        return $d_conf->count() > 0 ? $d_conf->toArray() : [];
    }

    public function getLists()
    {
        $params = $this->input->post('params');

        try {
            $data = $this->_getData( $params['unit'], $params['start_date'], $params['end_date'] );

            $content['data'] = $data;
            $html = $this->load->view($this->pathView.'list', $content, true);

            $result['status'] = 1;
            $result['html'] = $html;
        } catch (Exception $e) {
            $result['status'] = 0;
            $result['message'] = $e->getMessage();
        }

        display_json( $result );
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

        $unit = $params['unit'];
        $start_date = $params['start_date'];
        $end_date = $params['end_date'];

        $data = $this->_getData( $unit, $start_date, $end_date );

        $filename = strtoupper('RHPP_V2_PERIODE_');
        $filename = $filename.str_replace('-', '', $start_date).'_'.str_replace('-', '', $end_date).'.xls';

        // Susunan kolom PERSIS sama dgn tabel di view (index.php/list.php): identitas (3 kolom, rowspan
        // 2 baris header) -> Chick In (2 kolom) -> Performance (8 kolom) -> Pendapatan Plasma (6 kolom)
        // -> 2 kolom penutup (Total Pendapatan Plasma/Inti, rowspan 2 baris juga).
        $identitas = array(
            array('field' => 'mitra', 'label' => 'NAMA PLASMA', 'type' => 'string'),
            array('field' => 'unit', 'label' => 'UNIT', 'type' => 'string'),
            array('field' => 'noreg', 'label' => 'NOREG', 'type' => 'string'),
        );
        $chick_in = array(
            array('field' => 'tgl_docin', 'label' => 'TGL', 'type' => 'date'),
            array('field' => 'populasi', 'label' => 'POPULASI', 'type' => 'integer'),
        );
        $performance = array(
            array('field' => 'jml_panen_ekor', 'label' => 'EKOR PANEN', 'type' => 'integer'),
            array('field' => 'jml_panen_kg', 'label' => 'BERAT BADAN', 'type' => 'decimal2'),
            array('field' => 'bb', 'label' => 'BB RATA2', 'type' => 'decimal2'),
            array('field' => 'fcr', 'label' => 'FCR', 'type' => 'decimal2'),
            array('field' => 'deplesi', 'label' => 'DEPLESI', 'type' => 'decimal2'),
            array('field' => 'rata_umur', 'label' => 'RATA2 UMUR', 'type' => 'decimal2'),
            array('field' => 'adg', 'label' => 'ADG', 'type' => 'decimal2'),
            array('field' => 'ip', 'label' => 'IP', 'type' => 'decimal2'),
        );
        $pendapatan_plasma = array(
            array('field' => 'selisih_budidaya', 'label' => 'SELISIH BUDIDAYA', 'type' => 'decimal2'),
            array('field' => 'bonus_pasar', 'label' => 'BONUS PASAR', 'type' => 'decimal2'),
            array('field' => 'bonus_kematian', 'label' => 'BONUS KEMATIAN', 'type' => 'decimal2'),
            array('field' => 'bonus_insentif_fcr', 'label' => 'BONUS FCR', 'type' => 'decimal2'),
            array('field' => 'total_bonus_insentif_listrik', 'label' => 'INSENTIF LISTRIK', 'type' => 'decimal2'),
            array('field' => 'insentif_lpg', 'label' => 'INSENTIF LPG', 'type' => 'decimal2'),
        );
        $penutup = array(
            array('field' => 'total_pendapatan_plasma', 'label' => 'TOTAL PENDAPATAN PLASMA', 'type' => 'decimal2'),
            array('field' => 'total_pendapatan_inti', 'label' => 'TOTAL PENDAPATAN INTI', 'type' => 'decimal2'),
        );

        $columns = array();
        foreach ($identitas as $c) {
            $columns[] = array('field' => $c['field'], 'label' => $c['label'], 'type' => $c['type'], 'group' => null);
        }
        foreach ($chick_in as $c) {
            $columns[] = array('field' => $c['field'], 'label' => $c['label'], 'type' => $c['type'], 'group' => 'CHICK IN');
        }
        foreach ($performance as $c) {
            $columns[] = array('field' => $c['field'], 'label' => $c['label'], 'type' => $c['type'], 'group' => 'PERFORMANCE');
        }
        foreach ($pendapatan_plasma as $c) {
            $columns[] = array('field' => $c['field'], 'label' => $c['label'], 'type' => $c['type'], 'group' => 'PENDAPATAN PLASMA');
        }
        foreach ($penutup as $c) {
            $columns[] = array('field' => $c['field'], 'label' => $c['label'], 'type' => $c['type'], 'group' => null);
        }

        $arr_header = array();
        foreach ($columns as $i => $c) {
            $arr_header[] = toAlpha($i + 1);
        }

        $arr_column = null;
        $idx = 0;
        $arr_column[ $idx ] = array(
            'A' => array('value' => 'RHPP V2', 'data_type' => 'string', 'text_style' => 'bold', 'border' => 'none')
        );
        $idx++;
        $arr_column[ $idx ] = array(
            'A' => array('value' => 'PERIODE TUTUP SIKLUS '.str_replace('-', '/', $start_date).' - '.str_replace('-', '/', $end_date), 'data_type' => 'string', 'colspan' => array('A', toAlpha(count($identitas))), 'align' => 'left', 'text_style' => 'bold', 'border' => 'none'),
        );
        $idx++;

        // Row header 1 (kolom identitas & kolom penutup rowspan 2 baris; kolom grup colspan sesuai jumlah sub-kolomnya di baris ini saja)
        $row_header1 = array();
        foreach ($columns as $i => $c) {
            $letter = $arr_header[ $i ];
            if ( $c['group'] === null ) {
                $row_header1[ $letter ] = array('value' => $c['label'], 'data_type' => 'string', 'rowspan' => array($letter.'3', $letter.'4'), 'align' => 'center', 'text_style' => 'bold');
            }
        }
        $group_ranges = array();
        foreach ($columns as $i => $c) {
            if ( $c['group'] !== null ) {
                if ( !isset($group_ranges[ $c['group'] ]) ) {
                    $group_ranges[ $c['group'] ] = array('start' => $arr_header[ $i ], 'end' => $arr_header[ $i ]);
                } else {
                    $group_ranges[ $c['group'] ]['end'] = $arr_header[ $i ];
                }
            }
        }
        foreach ($group_ranges as $label => $range) {
            $row_header1[ $range['start'] ] = array('value' => $label, 'data_type' => 'string', 'colspan' => array($range['start'], $range['end']), 'align' => 'center', 'text_style' => 'bold');
        }
        $arr_column[ $idx ] = $row_header1;
        $idx++;

        // Row header 2 (sub-label Tgl/Populasi/Ekor Panen/dst, hanya utk kolom yg masuk grup)
        $row_header2 = array();
        foreach ($columns as $i => $c) {
            if ( $c['group'] !== null ) {
                $row_header2[ $arr_header[ $i ] ] = array('value' => $c['label'], 'data_type' => 'string', 'align' => 'center', 'text_style' => 'bold');
            }
        }
        $arr_column[ $idx ] = $row_header2;
        $idx++;

        $start_row_header = $idx;

        // Field yg di-sum utk baris SUB TOTAL/GRAND TOTAL (yg lain, spt FCR/BB/Deplesi/Rata2 Umur/ADG/IP,
        // adalah rata2 per siklus jadi di baris total ditampilkan rata2-nya, bukan dijumlah - sama spt
        // logic di rhpp_v2/list.php)
        $sum_fields = array('populasi', 'jml_panen_ekor', 'jml_panen_kg', 'selisih_budidaya', 'bonus_pasar', 'bonus_kematian', 'bonus_insentif_fcr', 'total_bonus_insentif_listrik', 'insentif_lpg', 'total_pendapatan_plasma', 'total_pendapatan_inti');
        $avg_fields = array('bb', 'fcr', 'deplesi', 'rata_umur', 'adg', 'ip');
        $all_fields = array_merge($sum_fields, $avg_fields);

        $unit_totals = array();
        $unit_counts = array();
        $grand_total = array_fill_keys($all_fields, 0);
        $grand_count = 0;

        foreach ($data as $v) {
            $u = $v['unit'];
            if ( !isset($unit_totals[ $u ]) ) {
                $unit_totals[ $u ] = array_fill_keys($all_fields, 0);
                $unit_counts[ $u ] = 0;
            }
            foreach ($all_fields as $f) {
                $val = isset($v[ $f ]) ? $v[ $f ] : 0;
                $unit_totals[ $u ][ $f ] += $val;
                $grand_total[ $f ] += $val;
            }
            $unit_counts[ $u ]++;
            $grand_count++;
        }

        $write_total_row = function($label, $t, $count) use (&$arr_column, &$idx, $arr_header, $columns, $sum_fields, $avg_fields) {
            $row = array();
            $label_written = false;
            foreach ($columns as $i => $c) {
                $letter = $arr_header[ $i ];
                if ( !$label_written ) {
                    $row[ $letter ] = array('value' => $label, 'data_type' => 'string', 'colspan' => array($arr_header[0], $arr_header[3]), 'align' => 'left', 'text_style' => 'bold');
                    $label_written = true;
                    continue;
                }
                if ( $i <= 3 ) {
                    continue;
                }

                if ( in_array($c['field'], $sum_fields) ) {
                    $row[ $letter ] = array('value' => $t[ $c['field'] ], 'data_type' => $c['type'], 'text_style' => 'bold');
                } elseif ( in_array($c['field'], $avg_fields) ) {
                    $row[ $letter ] = array('value' => $count > 0 ? round($t[ $c['field'] ] / $count, 2) : 0, 'data_type' => $c['type'], 'text_style' => 'bold');
                }
            }
            $arr_column[ $idx ] = $row;
            $idx++;
        };

        if ( !empty($data) ) {
            $data_count = count($data);
            foreach ($data as $key => $value) {
                $row = array();
                foreach ($columns as $i => $c) {
                    $letter = $arr_header[ $i ];
                    $cell_value = ($c['field'] === 'mitra') ? strtoupper($value[ $c['field'] ]) : $value[ $c['field'] ];
                    $row[ $letter ] = array('value' => $cell_value, 'data_type' => $c['type']);
                }
                $arr_column[ $idx ] = $row;
                $idx++;

                $is_last_row_of_unit = ($key == $data_count - 1) || ($data[ $key + 1 ]['unit'] !== $value['unit']);
                if ( $is_last_row_of_unit ) {
                    $write_total_row('SUB TOTAL '.$value['unit'], $unit_totals[ $value['unit'] ], $unit_counts[ $value['unit'] ]);
                }
            }
            $write_total_row('GRAND TOTAL', $grand_total, $grand_count);
        }

        Modules::run( 'base/ExportExcel/exportExcelUsingSpreadSheet', $filename, $arr_header, $arr_column, $start_row_header, 0 );

        $this->load->helper('download');
        force_download('export_excel/'.$filename.'.xlsx', NULL);
    }
}
