<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Neraca extends Public_Controller {

    private $path = 'report/neraca/';
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
                "assets/report/neraca/js/neraca.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/neraca/css/neraca.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['perusahaan'] = $this->getPerusahaan();
            $content['title_menu'] = 'Neraca';

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
                sr.nama = 'LAPORAN NERACA'
            order by
                isnull(srg.urut, srg.id) asc
        ";
        $d_srg = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_srg->count() > 0 ) {
            $data = $d_srg->toArray();
        }

        return $data;
    }

    /**
     * Neraca = snapshot per tanggal (saldo kumulatif sejak awal histori s/d End Date), bukan rentang
     * periode. Filter di layar tetap BULAN/TAHUN (konvensi lama gmperp) -- cuma dipakai untuk tentukan
     * End Date (akhir bulan terpilih, atau akhir Desember kalau BULAN = ALL).
     */
    private function resolveEndDate($bulan, $tahun)
    {
        $b = ($bulan != 'all') ? $bulan : 12;
        $angka_bulan = (strlen($b) == 1) ? '0'.$b : $b;
        $date = $tahun.'-'.$angka_bulan.'-01';

        return date("Y-m-t", strtotime($date)).' 23:59:59';
    }

    /**
     * Opsi B (anchor + roll-forward, pola sama dengan fix General Ledger di project bagia_sinar_jaya_corp):
     * kalau ada snapshot `saldo_bulanan` <= End Date, pakai snapshot TERAKHIR itu sebagai saldo dasar,
     * lalu roll-forward mutasi det_jurnal cuma dari (anchor, End Date] -- bukan scan seluruh histori.
     * Kalau saldo_bulanan kosong sama sekali, balik ke cara lama (hitung penuh dari awal histori).
     * Anchor GLOBAL (bukan per-COA/unit) karena Tutup Bulan menutup semua akun+unit bersamaan tiap bulan.
     */
    private function getAnchorDate($end_date)
    {
        $m_conf = new \Model\Storage\Conf();
        $d_anchor = $m_conf->hydrateRaw("select max(tanggal) as anchor from saldo_bulanan where tanggal <= '".$end_date."'");

        if ( $d_anchor->count() > 0 ) {
            $row = $d_anchor->toArray()[0];
            if ( !empty($row['anchor']) ) {
                return substr($row['anchor'], 0, 10);
            }
        }

        return null;
    }

    private function buildPrsJoin($perusahaan, $alias_unit)
    {
        $sql_prs_join  = "";
        $sql_prs_where = "";
        if ( !empty($perusahaan) && $perusahaan != 'all' ) {
            $m_conf   = new \Model\Storage\Conf();
            $d_kode   = $m_conf->hydrateRaw("select top 1 kode from perusahaan where kode_gabung_perusahaan = '".$perusahaan."'");
            $kode_prs = ($d_kode->count() > 0) ? $d_kode->toArray()[0]['kode'] : null;

            if ( !empty($kode_prs) ) {
                $sql_prs_join  = "inner join (select kode, perusahaan from wilayah group by kode, perusahaan) w on ".$alias_unit." = w.kode
                        inner join (select kode, induk from perusahaan group by kode, induk) prs on w.perusahaan = prs.kode";
                $sql_prs_where = "and (prs.kode = '".$kode_prs."' or prs.induk = '".$kode_prs."')";
            }
        }

        return array($sql_prs_join, $sql_prs_where);
    }

    /**
     * Bangun query gabungan DEBET (coa_tujuan) + KREDIT (coa_asal) dari det_jurnal untuk daftar
     * id_header (setting_report_group) tertentu, plus baris SNAPSHOT saldo_bulanan (anchor) kalau ada --
     * supaya rentang det_jurnal yang di-scan cuma (anchor, end_date], bukan sepanjang histori.
     */
    private function buildPass1Sql($in_ids, $end_date, $anchor_date, $sql_prs_join, $sql_prs_where)
    {
        $sql_tgl_bawah = !empty($anchor_date) ? "and dj.tanggal > '".$anchor_date." 23:59:59'" : "";

        $sql_snapshot = "";
        if ( !empty($anchor_date) ) {
            $sql_snapshot = "
                union all

                -- SNAPSHOT: saldo_bulanan sbg anchor (saldo_akhir = net debet-kredit kumulatif s/d anchor_date)
                select
                    srgi.id_header,
                    srgi.item_report_id,
                    ir.nama                        as item_report_nama,
                    isnull(ir.tipe, 'item')        as item_tipe,
                    isnull(sb.saldo_akhir, 0)      as debet,
                    0                               as kredit,
                    null                            as perusahaan,
                    srgi.urut,
                    isnull(srgi.sign, 1)           as sign_val,
                    sb.unit
                from saldo_bulanan sb
                inner join
                    (select id_header, no_coa, item_report_id, urut, sign from setting_report_group_item where id_header in (".$in_ids.")) srgi
                    on sb.coa = srgi.no_coa
                inner join item_report ir on srgi.item_report_id = ir.id
                where
                    sb.tanggal = '".$anchor_date."'
            ";
        }

        return "
            select
                id_header,
                item_report_id,
                item_report_nama,
                item_tipe,
                sum(debet)  as debet,
                sum(kredit) as kredit,
                urut,
                sign_val
            from (

                -- Sisi DEBET: coa_tujuan, unit filter -> isnull(unit_tujuan, unit)
                select
                    srgi.id_header,
                    srgi.item_report_id,
                    ir.nama                        as item_report_nama,
                    isnull(ir.tipe, 'item')        as item_tipe,
                    dj.nominal                     as debet,
                    0                              as kredit,
                    dj.perusahaan,
                    srgi.urut,
                    isnull(srgi.sign, 1)           as sign_val,
                    case
                        when dj.unit_tujuan is not null then dj.unit_tujuan
                        else dj.unit
                    end as unit
                from det_jurnal dj
                inner join
                    (select id_header, no_coa, item_report_id, urut, sign from setting_report_group_item where id_header in (".$in_ids.")) srgi
                    on dj.coa_tujuan = srgi.no_coa
                inner join item_report ir on srgi.item_report_id = ir.id
                where
                    dj.tanggal <= '".$end_date."'
                    ".$sql_tgl_bawah."

                union all

                -- Sisi KREDIT: coa_asal, unit filter -> unit
                select
                    srgi.id_header,
                    srgi.item_report_id,
                    ir.nama                        as item_report_nama,
                    isnull(ir.tipe, 'item')        as item_tipe,
                    0                              as debet,
                    dj.nominal                     as kredit,
                    dj.perusahaan,
                    srgi.urut,
                    isnull(srgi.sign, 1)           as sign_val,
                    dj.unit
                from det_jurnal dj
                inner join
                    (select id_header, no_coa, item_report_id, urut, sign from setting_report_group_item where id_header in (".$in_ids.")) srgi
                    on dj.coa_asal = srgi.no_coa
                inner join item_report ir on srgi.item_report_id = ir.id
                where
                    dj.tanggal <= '".$end_date."'
                    ".$sql_tgl_bawah."
                ".$sql_snapshot."

            ) combined
            ".$sql_prs_join."
            where 1=1 ".$sql_prs_where."
            group by id_header, item_report_id, item_report_nama, item_tipe, urut, sign_val
            order by id_header, urut asc
        ";
    }

    public function getData()
    {
        $params     = $this->input->get('params');
        $perusahaan = $params['perusahaan'];
        $bulan      = $params['bulan'];
        $tahun      = substr($params['tahun'], 0, 4);

        $end_date    = $this->resolveEndDate($bulan, $tahun);
        $anchor_date = $this->getAnchorDate($end_date);

        list($sql_prs_join, $sql_prs_where) = $this->buildPrsJoin($perusahaan, 'combined.unit');

        $srg  = $this->getSettingReportGroup();
        $data = null;

        if ( !empty($srg) ) {
            // Inisialisasi semua group sesuai urutan
            foreach ($srg as $v_srg) {
                $data[ $v_srg['id'] ] = array(
                    'id'     => $v_srg['id'],
                    'nama'   => $v_srg['nama'],
                    'tipe'   => !empty($v_srg['tipe']) ? $v_srg['tipe'] : 'data',
                    'detail' => array()
                );
            }

            // Kumpulkan semua id group bertipe 'data'
            $data_group_ids = array();
            foreach ($srg as $g) {
                if ( (!empty($g['tipe']) ? $g['tipe'] : 'data') === 'data' ) {
                    $data_group_ids[] = (int)$g['id'];
                }
            }

            if ( !empty($data_group_ids) ) {
                $in_ids = implode(',', $data_group_ids);

                // Pass 0 -- inisialisasi semua item yang di-mapping di setting_report_group_item
                // dengan saldo 0, supaya item yang belum ada transaksi tetap tampil (bukan hilang)
                $sql_items = "
                    select
                        srgi.id_header,
                        srgi.item_report_id,
                        ir.nama                  as item_report_nama,
                        isnull(ir.tipe, 'item')  as item_tipe,
                        isnull(srgi.sign, 1)     as sign_val
                    from setting_report_group_item srgi
                    inner join item_report ir on srgi.item_report_id = ir.id
                    where srgi.id_header in (".$in_ids.")
                    order by srgi.id_header, srgi.urut asc
                ";

                $m_conf  = new \Model\Storage\Conf();
                $d_items = $m_conf->hydrateRaw($sql_items);

                if ( $d_items->count() > 0 ) {
                    foreach ($d_items->toArray() as $v_item) {
                        $group_id = $v_item['id_header'];
                        $key      = $v_item['item_report_id'];
                        $sign     = isset($v_item['sign_val']) ? (int)$v_item['sign_val'] : 1;

                        $data[ $group_id ]['detail'][ $key ] = array(
                            'item_report_id'   => $key,
                            'item_report_nama' => $v_item['item_report_nama'],
                            'item_tipe'        => $v_item['item_tipe'] ?? 'item',
                            'debet'            => 0,
                            'kredit'           => 0,
                            'saldo'            => 0,
                            'sign'             => $sign
                        );
                    }
                }

                // Pass 1 -- satu query untuk semua group sekaligus (saldo kumulatif s/d end_date,
                // dgn anchor+roll-forward kalau ada snapshot saldo_bulanan)
                $sql = $this->buildPass1Sql($in_ids, $end_date, $anchor_date, $sql_prs_join, $sql_prs_where);

                $m_conf   = new \Model\Storage\Conf();
                $d_result = $m_conf->hydrateRaw($sql);

                if ( $d_result->count() > 0 ) {
                    $d_result = $d_result->toArray();

                    foreach ($d_result as $value) {
                        $group_id = $value['id_header'];
                        $key      = $value['item_report_id'];
                        $debet    = (float)($value['debet']  ?? 0);
                        $kredit   = (float)($value['kredit'] ?? 0);
                        $sign     = isset($value['sign_val']) ? (int)$value['sign_val'] : 1;
                        $saldo    = ($debet - $kredit) * $sign;

                        if ( !isset($data[ $group_id ]['detail'][ $key ]) ) {
                            $data[ $group_id ]['detail'][ $key ] = array(
                                'item_report_id'   => $key,
                                'item_report_nama' => $value['item_report_nama'],
                                'item_tipe'        => $value['item_tipe'] ?? 'item',
                                'debet'            => $debet,
                                'kredit'           => $kredit,
                                'saldo'            => $saldo,
                                'sign'             => $sign
                            );
                        } else {
                            $data[ $group_id ]['detail'][ $key ]['debet']  += $debet;
                            $data[ $group_id ]['detail'][ $key ]['kredit'] += $kredit;
                            $data[ $group_id ]['detail'][ $key ]['saldo']  += $saldo;
                        }
                    }

                    foreach ($data_group_ids as $gid) {
                        ksort($data[ $gid ]['detail']);
                    }
                }
            }

            // Pass 2 -- hitung group tipe 'subtotal' (mis. TOTAL AKTIVA, TOTAL PASSIVA)
            foreach ($srg as $v_srg) {
                if ( $data[ $v_srg['id'] ]['tipe'] !== 'subtotal' ) continue;

                $ref_ids = array_filter(array_map('trim', explode(',', $v_srg['ref_group_ids'] ?? '')));
                $sub_d = 0; $sub_k = 0; $sub_s = 0;

                foreach ($ref_ids as $ref_id) {
                    $ref_id = (int)$ref_id;
                    if ( !empty($data[$ref_id]['detail']) ) {
                        foreach ($data[$ref_id]['detail'] as $det) {
                            $sub_d += (float)($det['debet']  ?? 0);
                            $sub_k += (float)($det['kredit'] ?? 0);
                            // 'saldo' item di sini sudah signed ((debet-kredit)*sign), tinggal dijumlah
                            $sub_s += (float)($det['saldo']  ?? 0);
                        }
                    }
                }

                $data[ $v_srg['id'] ]['detail'] = array(
                    array(
                        'debet'     => $sub_d,
                        'kredit'    => $sub_k,
                        'saldo'     => $sub_s,
                        'item_tipe' => 'subtotal',
                        'sign'      => 1
                    )
                );
            }
        }

        $content['data'] = $data;
        echo $this->load->view($this->path.'list', $content, TRUE);
    }

    /**
     * Detail per item Neraca (di-klik dari list) -- pecah 1 item jadi baris per No. COA
     * yang di-mapping ke item itu di setting_report_group_item, beserta saldo akhirnya.
     * Pakai anchor+roll-forward yang sama dengan getData() supaya angkanya konsisten.
     */
    public function formDetail()
    {
        $params = $this->input->get('params');

        $detail = $this->getDetailCoa( $params['id_header'], $params['item_report_id'], $params['bulan'], $params['tahun'], $params['perusahaan'] );

        $content['data']   = $params;
        $content['detail'] = $detail;
        $html = $this->load->view($this->path.'detail', $content, TRUE);

        echo $html;
    }

    public function getDetailCoa( $id_header, $item_report_id, $bulan, $tahun, $perusahaan )
    {
        $id_header      = (int)$id_header;
        $item_report_id = (int)$item_report_id;

        $end_date    = $this->resolveEndDate($bulan, substr($tahun, 0, 4));
        $anchor_date = $this->getAnchorDate($end_date);

        $sql_tgl_bawah = !empty($anchor_date) ? "and tanggal > '".$anchor_date." 23:59:59'" : "";

        list($sql_prs_join, $sql_prs_where) = $this->buildPrsJoin($perusahaan, 'x.unit');
        if ( !empty($sql_prs_where) ) {
            $sql_prs_where = str_replace("and (prs.kode", "and (x.no_coa is null or prs.kode", $sql_prs_where);
        }

        $sql_snapshot = "";
        if ( !empty($anchor_date) ) {
            $sql_snapshot = "
                union all

                select
                    coa as no_coa,
                    isnull(saldo_akhir, 0) as debet,
                    0 as kredit,
                    unit
                from saldo_bulanan
                where
                    tanggal = '".$anchor_date."' and
                    coa in (select no_coa from setting_report_group_item where id_header = ".$id_header." and item_report_id = ".$item_report_id.")
            ";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                srgi.no_coa,
                c.nama_coa,
                isnull(sum(x.debet), 0)  as debet,
                isnull(sum(x.kredit), 0) as kredit,
                isnull(srgi.sign, 1)     as sign_val
            from (
                select no_coa, sign from setting_report_group_item
                where id_header = ".$id_header." and item_report_id = ".$item_report_id."
            ) srgi
            left join
                (
                    select coa1.* from coa coa1
                    right join (select max(id) as id, coa from coa group by coa) coa2 on coa1.id = coa2.id
                ) c
                on c.coa = srgi.no_coa
            left join
                (
                    select
                        coa_tujuan as no_coa,
                        nominal as debet,
                        0 as kredit,
                        case when unit_tujuan is not null then unit_tujuan else unit end as unit
                    from det_jurnal
                    where tanggal <= '".$end_date."' ".$sql_tgl_bawah."

                    union all

                    select
                        coa_asal as no_coa,
                        0 as debet,
                        nominal as kredit,
                        unit
                    from det_jurnal
                    where tanggal <= '".$end_date."' ".$sql_tgl_bawah."
                    ".$sql_snapshot."
                ) x
                on x.no_coa = srgi.no_coa
            ".$sql_prs_join."
            where 1=1 ".$sql_prs_where."
            group by srgi.no_coa, c.nama_coa, srgi.sign
            order by srgi.no_coa asc
        ";

        $d_result = $m_conf->hydrateRaw($sql);

        $data = null;
        if ( $d_result->count() > 0 ) {
            $data = array();
            foreach ($d_result->toArray() as $v) {
                $debet  = (float)($v['debet']  ?? 0);
                $kredit = (float)($v['kredit'] ?? 0);
                $sign   = isset($v['sign_val']) ? (int)$v['sign_val'] : 1;

                $data[] = array(
                    'no_coa'      => $v['no_coa'],
                    'nama_coa'    => $v['nama_coa'],
                    'saldo_akhir' => ($debet - $kredit) * $sign
                );
            }
        }

        return $data;
    }
}
