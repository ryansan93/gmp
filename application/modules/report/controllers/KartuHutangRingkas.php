<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class KartuHutangRingkas extends Public_Controller {

    private $pathView = 'report/kartu_hutang_ringkas/';
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
        // if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                "assets/report/kartu_hutang_ringkas/js/kartu-hutang-ringkas.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/kartu_hutang_ringkas/css/kartu-hutang-ringkas.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['supplier'] = $this->getSupplier();
            $content['jenis'] = $this->getJenis();
            $content['jenis_hutang'] = $this->getJenisHutang();
            $content['title_menu'] = 'Laporan Kartu Hutang Ringkas';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        // } else {
        //     showErrorAkses();
        // }
    }

    public function getJenis() {
        $arr = array('ekspedisi', 'plasma', 'supplier');

        return $arr;
    }

    public function getJenisHutang() {
        return array(
            array('value' => 'DOC',        'label' => 'DOC'),
            array('value' => 'PAKAN',      'label' => 'PKN'),
            array('value' => 'OVK',        'label' => 'OVK'),
            array('value' => 'OVK EXTERN', 'label' => 'OVK EXTERN'),
            array('value' => 'PERALATAN',  'label' => 'PERALATAN'),
            array('value' => 'RHPP',       'label' => 'PLASMA'),
            array('value' => 'OA PAKAN',   'label' => 'EKSPEDISI'),
        );
    }

    public function getSupplier() {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select * from 
            (
                select p1.nomor, p1.nama, 'supplier' as tipe from pelanggan p1
                right join
                    (select max(id) as id, nomor from pelanggan p where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) p2
                    on
                        p1.id = p2.id

                union all
                        
                select e1.nomor, e1.nama, 'ekspedisi' as tipe from ekspedisi e1
                right join
                    (select max(id) as id, nomor from ekspedisi e group by nomor) e2
                    on
                        e1.id = e2.id

                union all

                select m1.nomor, m1.nama, 'plasma' as tipe from mitra m1
                right join
                    (select max(id) as id, nomor from mitra group by nomor) m2
                    on
                        m1.id = m2.id
                where
                    m1.mstatus = 1
            ) supl
            order by
                supl.tipe asc,
                supl.nama asc
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getData() {
        $params = $this->input->get('params');

        $bulan = $params['bulan'];
        $tahun = substr($params['tahun'], 0, 4);
        $jenis = $params['jenis'];
        $supplier = $params['supplier'];
        $jenis_hutang = isset($params['jenis_hutang']) ? $params['jenis_hutang'] : null;

        if ( $bulan != 'all' ) {
            $i = $bulan;

            $angka_bulan = (strlen($i) == 1) ? '0'.$i : $i;

            $date = $tahun.'-'.$angka_bulan.'-01';
            $start_date = date("Y-m-d", strtotime($date));
            $end_date = date("Y-m-t", strtotime($date));
        } else {
            $i = 1;
            $angka_bulan = (strlen($i) == 1) ? '0'.$i : $i;
            $_start_date = $tahun.'-'.$angka_bulan.'-01';
            $start_date = date("Y-m-d", strtotime($_start_date));

            $i = 12;
            $angka_bulan = (strlen($i) == 1) ? '0'.$i : $i;
            $_end_date = $tahun.'-'.$angka_bulan.'-01';
            $end_date = date("Y-m-t", strtotime($_end_date));
        }

        if ( $start_date == '2026-06-01' ) {
            $end_date = '2026-06-13';
        }

        $where = null;
        if ( $jenis != 'all' ) {
            if ( empty( $where ) ) {
                $where = "where supl.tipe = '".$jenis."'";
            } else {
                $where .= " and supl.tipe = '".$jenis."'";
            }
        }

        if ( $supplier != 'all' ) {
            if ( empty( $where ) ) {
                $where = "where supl.nomor = '".$supplier."'";
            } else {
                $where .= " and supl.nomor = '".$supplier."'";
            }
        }

        if ( !empty($jenis_hutang) ) {
            if ( empty( $where ) ) {
                $where = "where data.jenis in ('".implode("', '", $jenis_hutang)."')";
            } else {
                $where .= " and data.jenis in ('".implode("', '", $jenis_hutang)."')";
            }
        }

        // cetak_r( $where, 1 );

        // $valid_jenis = array('DOC', 'PAKAN', 'OVK', 'OVK EXTERN', 'RHPP', 'OA PAKAN');
        // $jenis_hutang = isset($params['jenis_hutang']) ? (array)$params['jenis_hutang'] : array();
        // $jenis_hutang = array_values(array_filter($jenis_hutang, function($j) use ($valid_jenis) { return in_array($j, $valid_jenis); }));
        // $where_jenis = '';
        // if ( !empty($jenis_hutang) ) {
        //     $jenis_list = implode("','", $jenis_hutang);
        //     $where_jenis = "and data.jenis in ('".$jenis_list."')";
        // }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            /* ============================================================================
               V2-PORTED (disalin dari KartuHutangLengkap): DOC + PAKAN + OVK (ORP/NON ORP) dihitung dari
               #v2_sumber_hutang / #v2_pembayaran spy konsisten dgn Kartu Hutang Lengkap (memorial, DN,
               koreksi BBM dobel, PPh/CN/pembulatan). Jenis lain (OVK EXTERN, RHPP, OA PAKAN, PERALATAN)
               tetap pakai query lama di bawah.
               ============================================================================ */
            SET NOCOUNT ON;
            /* NOCOUNT wajib di batch multi-statement begini -- tanpa ini, PDO driver sqlsrv error
               IMSSP: active result contains no fields, krn fetchAll() ngambil resultset dari
               statement DDL/SELECT INTO duluan (bukan SELECT akhir). JANGAN hapus, dan JANGAN
               pecah jadi >1 panggilan hydrateRaw/statement terpisah -- #temp table TIDAK persist
               lintas panggilan PDO prepare() terpisah di driver ini. */

            /* ============================================================================
               V2-PORTED: DOC + PAKAN + OVK debt/payment source logic, copied verbatim from
               KartuHutangPerInvoiceV2.php getData() (2026-08-28/29 design), via the same port
               already applied to KartuHutangPerInvoice.php. See that file's header comment for
               full rationale of every branch below.
               ============================================================================ */
            IF OBJECT_ID('tempdb..#v2_kh_doc') IS NOT NULL BEGIN DROP TABLE #v2_kh_doc END
            select kpd.nomor, max(kpdd.kode_unit) as unit, min(td.datang) as tgl_terima
            into #v2_kh_doc
            from konfirmasi_pembayaran_doc kpd
            left join konfirmasi_pembayaran_doc_det kpdd on kpdd.id_header = kpd.id
            left join
                (
                    select t.no_order, t.datang from terima_doc t
                    join (select max(id) as id, no_order from terima_doc group by no_order) t2 on t2.id = t.id
                ) td
                on td.no_order = kpdd.no_order
            group by kpd.nomor
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE UNIQUE CLUSTERED INDEX ix_v2_kh_doc ON #v2_kh_doc(nomor)

            IF OBJECT_ID('tempdb..#v2_kh_pakan') IS NOT NULL BEGIN DROP TABLE #v2_kh_pakan END
            select kpp.nomor, max(kppd.kode_unit) as unit, min(tp.tgl_terima) as tgl_terima
            into #v2_kh_pakan
            from konfirmasi_pembayaran_pakan kpp
            left join konfirmasi_pembayaran_pakan_det kppd on kppd.id_header = kpp.id
            left join kirim_pakan kp on kp.no_order = kppd.no_order
            left join terima_pakan tp on tp.id_kirim_pakan = kp.id
            group by kpp.nomor
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE UNIQUE CLUSTERED INDEX ix_v2_kh_pakan ON #v2_kh_pakan(nomor)

            IF OBJECT_ID('tempdb..#v2_kh_ovk') IS NOT NULL BEGIN DROP TABLE #v2_kh_ovk END
            select kpv.nomor, max(kpvd.kode_unit) as unit, min(tv.tgl_terima) as tgl_terima
            into #v2_kh_ovk
            from konfirmasi_pembayaran_voadip kpv
            left join konfirmasi_pembayaran_voadip_det kpvd on kpvd.id_header = kpv.id
            left join kirim_voadip kv on kv.no_order = kpvd.no_order
            left join terima_voadip tv on tv.id_kirim_voadip = kv.id
            group by kpv.nomor
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE UNIQUE CLUSTERED INDEX ix_v2_kh_ovk ON #v2_kh_ovk(nomor)

            /* V2-PORTED (2026-09-04, sinkron dgn KartuHutangPerInvoiceV2 versi terbaru): daftar
               'invoice hantu' OVK -- nomor yg dihasilkan cabang 'Memorial'/'Memorial reversed' di
               bawah (memo standalone tanpa invoice riil). Dipakai supaya cabang 'Memorial' tidak
               menambah lagi ke invoice hantu yg sudah ada, dan cabang 'Pelunasan Memorial' bisa
               mencocokkan ke sini selain ke invoice riil. Lihat [[selisih-122-ovk-nonorp-v2]]. */
            IF OBJECT_ID('tempdb..#v2_invoice_hantu_ovk') IS NOT NULL BEGIN DROP TABLE #v2_invoice_hantu_ovk END
            select distinct isnull(nullif(mi.no_invoice, ''), mi.no_mm) as nomor
            into #v2_invoice_hantu_ovk
            from mmitem mi
            left join mm m on mi.no_mm = m.no_mm
            left hash join #v2_kh_ovk kh_ih on kh_ih.nomor = mi.no_invoice
            where (
                    (mi.coa_tujuan in ('21180.300', '21174.000') and isnull(mi.coa_asal, '') not in ('21180.300', '21174.000'))
                    or
                    (mi.coa_asal in ('21180.300', '21174.000') and nullif(mi.no_invoice, '') is null)
                  )
                  and kh_ih.nomor is null
                  and nullif(m.no_supplier, '') is not null
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE UNIQUE CLUSTERED INDEX ix_v2_invoice_hantu_ovk ON #v2_invoice_hantu_ovk(nomor)

            IF OBJECT_ID('tempdb..#v2_sumber_hutang') IS NOT NULL BEGIN DROP TABLE #v2_sumber_hutang END
            select nomor, tanggal, supplier, total, unit, kode_trans, jenis_trans, jenis_hutang
            into #v2_sumber_hutang
            from (
                select kpd.nomor, cast(isnull(konfir.tgl_terima, kpd.tgl_bayar) as date) as tanggal, kpd.supplier, kpd.total, konfir.unit, kpd.nomor as kode_trans, 'Konfirmasi Pembayaran DOC' as jenis_trans, 'DOC' as jenis_hutang
                from konfirmasi_pembayaran_doc kpd
                left join #v2_kh_doc konfir on konfir.nomor = kpd.nomor

                union all

                select isnull(nullif(mi.no_invoice, ''), mi.no_mm) as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, m.unit, mi.no_mm as kode_trans, 'Memorial' as jenis_trans, 'DOC' as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                left hash join #v2_kh_doc kh_memo on kh_memo.nomor = mi.no_invoice
                where mi.coa_tujuan = '21180.200' and kh_memo.nomor is null
                  and nullif(m.no_supplier, '') is not null

                union all

                select kpp.nomor, cast(isnull(konfirp.tgl_terima, kpp.tgl_bayar) as date) as tanggal, kpp.supplier, kpp.total, konfirp.unit, kpp.nomor as kode_trans, 'Konfirmasi Pembayaran Pakan' as jenis_trans, 'PAKAN' as jenis_hutang
                from konfirmasi_pembayaran_pakan kpp
                left join #v2_kh_pakan konfirp on konfirp.nomor = kpp.nomor

                union all

                select isnull(nullif(mi.no_invoice, ''), mi.no_mm) as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, m.unit, mi.no_mm as kode_trans, 'Memorial' as jenis_trans, 'PAKAN' as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                left hash join #v2_kh_pakan kh_memo_p on kh_memo_p.nomor = mi.no_invoice
                where mi.coa_tujuan = '21180.100' and kh_memo_p.nomor is null
                  and nullif(m.no_supplier, '') is not null

                union all

                select kpv.nomor, cast(isnull(konfirv.tgl_terima, kpv.tgl_bayar) as date) as tanggal, kpv.supplier, kpv.total, konfirv.unit, kpv.nomor as kode_trans, 'Konfirmasi Pembayaran OVK' as jenis_trans, (case when kpv.supplier = '19B004' then 'OVK ORP' else 'OVK NON ORP' end) as jenis_hutang
                from konfirmasi_pembayaran_voadip kpv
                left join #v2_kh_ovk konfirv on konfirv.nomor = kpv.nomor

                union all

                select isnull(nullif(mi.no_invoice, ''), mi.no_mm) as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, m.unit, mi.no_mm as kode_trans, 'Memorial' as jenis_trans, (case when mi.coa_tujuan = '21180.300' then 'OVK ORP' else 'OVK NON ORP' end) as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                left hash join #v2_kh_ovk kh_memo_v on kh_memo_v.nomor = mi.no_invoice
                left hash join #v2_invoice_hantu_ovk ih_v on ih_v.nomor = mi.no_invoice
                where mi.coa_tujuan in ('21180.300', '21174.000') and kh_memo_v.nomor is null
                  and isnull(mi.coa_asal, '') not in ('21180.300', '21174.000')
                  and nullif(m.no_supplier, '') is not null
                  and ih_v.nomor is null

                union all

                select isnull(nullif(mi.no_invoice, ''), mi.no_mm) as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, m.unit, mi.no_mm as kode_trans, 'Memorial' as jenis_trans, (case when mi.coa_asal = '21180.300' then 'OVK ORP' else 'OVK NON ORP' end) as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                left hash join #v2_kh_ovk kh_memo_v2 on kh_memo_v2.nomor = mi.no_invoice
                where mi.coa_asal in ('21180.300', '21174.000')
                  and nullif(mi.no_invoice, '') is null
                  and kh_memo_v2.nomor is null
                  and nullif(m.no_supplier, '') is not null

                union all

                select dpd.nomor, dp.tanggal, kpv.supplier, dpd.pakai as total, konfirv2.unit, dp.no_dn as kode_trans, 'DN' as jenis_trans, (case when kpv.supplier = '19B004' then 'OVK ORP' else 'OVK NON ORP' end) as jenis_hutang
                from dn_post_det dpd
                left join dn_post dp on dp.id = dpd.id_header
                left join konfirmasi_pembayaran_voadip kpv on kpv.nomor = dpd.nomor
                left join #v2_kh_ovk konfirv2 on konfirv2.nomor = dpd.nomor
                where dp.jenis_dn = 'OVK'

                union all

                /* DN DOC (dn_post_det, jenis_dn='DOC') -- sama seperti DN OVK di atas: DN MENAMBAH hutang invoice (kebalikan CN).
                   Mulai 2026-09 ada DN DOC (price adjustment, dijurnal lewat memorial Cr 21180.200). Pembayaran realisasi sudah
                   memuat nilai DN, jadi tanpa cabang ini hutang DOC tampak kurang & saldo negatif sebesar total DN. */
                select dpd.nomor, dp.tanggal, kpd_dn.supplier, dpd.pakai as total, konfird_dn.unit, dp.no_dn as kode_trans, 'DN' as jenis_trans, 'DOC' as jenis_hutang
                from dn_post_det dpd
                left join dn_post dp on dp.id = dpd.id_header
                left join konfirmasi_pembayaran_doc kpd_dn on kpd_dn.nomor = dpd.nomor
                left join #v2_kh_doc konfird_dn on konfird_dn.nomor = dpd.nomor
                where dp.jenis_dn = 'DOC'

                union all

                select mi.no_invoice as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, konfirv3.unit, mi.no_mm as kode_trans, 'Koreksi Tambahan Hutang OVK' as jenis_trans, (case when mi.coa_asal = '21180.300' then 'OVK ORP' else 'OVK NON ORP' end) as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                left hash join #v2_kh_ovk konfirv3 on konfirv3.nomor = mi.no_invoice
                left hash join #v2_kh_ovk kh_memo_tambah on kh_memo_tambah.nomor = mi.no_invoice
                where mi.coa_asal in ('21180.300', '21174.000') and mi.coa_tujuan not in ('21180.300', '21174.000') and nullif(mi.no_invoice, '') is not null and kh_memo_tambah.nomor is not null
            ) x
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE INDEX ix_v2_sumber_hutang_nomor ON #v2_sumber_hutang(nomor)

            IF OBJECT_ID('tempdb..#v2_docref_raw') IS NOT NULL BEGIN DROP TABLE #v2_docref_raw END
            select kpd.nomor as invoice, td.no_sj
            into #v2_docref_raw
            from konfirmasi_pembayaran_doc kpd
            join konfirmasi_pembayaran_doc_det kpdd on kpdd.id_header = kpd.id
            left join
                (
                    select t.no_order, t.no_sj from terima_doc t
                    join (select max(id) as id, no_order from terima_doc group by no_order) t2 on t2.id = t.id
                ) td
                on td.no_order = kpdd.no_order
            where td.no_sj is not null and td.no_sj <> ''
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#v2_docref_helper') IS NOT NULL BEGIN DROP TABLE #v2_docref_helper END
            select distinct invoice, tok.value as no_sj
            into #v2_docref_helper
            from #v2_docref_raw
            cross apply string_split(no_sj, ' ') tok
            where tok.value like '[0-9]%' and len(tok.value) >= 6
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#v2_dj_pph_direct') IS NOT NULL BEGIN DROP TABLE #v2_dj_pph_direct END
            select invoice, tbl_id, sum(nominal) as pph
            into #v2_dj_pph_direct
            from det_jurnal
            where tbl_name = 'realisasi_pembayaran' and coa_asal like '246%' and invoice is not null and invoice <> ''
            group by invoice, tbl_id
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#v2_dj_pph_fallback') IS NOT NULL BEGIN DROP TABLE #v2_dj_pph_fallback END
            select id, tbl_id, nominal, cast(keterangan as varchar(300)) as keterangan
            into #v2_dj_pph_fallback
            from det_jurnal
            where tbl_name = 'realisasi_pembayaran' and coa_asal like '246%' and (invoice is null or invoice = '')
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#v2_dj_pph_tokens') IS NOT NULL BEGIN DROP TABLE #v2_dj_pph_tokens END
            select id, tbl_id, nominal, tok.value as token
            into #v2_dj_pph_tokens
            from #v2_dj_pph_fallback dj
            cross apply string_split(dj.keterangan, ' ') tok
            where tok.value like '[0-9]%' and len(tok.value) >= 6
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#v2_dj_pph_fallback_dedup') IS NOT NULL BEGIN DROP TABLE #v2_dj_pph_fallback_dedup END
            select distinct dr.invoice, tk.id, tk.tbl_id, tk.nominal
            into #v2_dj_pph_fallback_dedup
            from #v2_dj_pph_tokens tk
            join #v2_docref_helper dr on dr.no_sj = tk.token
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#v2_dj_pph_fallback_matched') IS NOT NULL BEGIN DROP TABLE #v2_dj_pph_fallback_matched END
            select invoice, tbl_id, sum(nominal) as pph
            into #v2_dj_pph_fallback_matched
            from #v2_dj_pph_fallback_dedup
            group by invoice, tbl_id
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#v2_rp_single_doc') IS NOT NULL BEGIN DROP TABLE #v2_rp_single_doc END
            select rp.id as tbl_id, max(rpd.no_bayar) as invoice
            into #v2_rp_single_doc
            from realisasi_pembayaran_det rpd
            join realisasi_pembayaran rp on rpd.id_header = rp.id
            where rpd.transaksi = 'DOC'
            group by rp.id
            having count(distinct rpd.no_bayar) = 1
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#v2_dj_pph_still_unmatched') IS NOT NULL BEGIN DROP TABLE #v2_dj_pph_still_unmatched END
            select dj.id, dj.tbl_id, dj.nominal
            into #v2_dj_pph_still_unmatched
            from #v2_dj_pph_fallback dj
            left join #v2_dj_pph_fallback_dedup fd on fd.id = dj.id
            where fd.id is null
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#v2_dj_pph_single_matched') IS NOT NULL BEGIN DROP TABLE #v2_dj_pph_single_matched END
            select rsd.invoice, un.tbl_id, sum(un.nominal) as pph
            into #v2_dj_pph_single_matched
            from #v2_dj_pph_still_unmatched un
            join #v2_rp_single_doc rsd on rsd.tbl_id = un.tbl_id
            group by rsd.invoice, un.tbl_id
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#v2_docref_dash') IS NOT NULL BEGIN DROP TABLE #v2_docref_dash END
            select distinct invoice
            into #v2_docref_dash
            from #v2_docref_raw
            where ltrim(rtrim(no_sj)) = '-'
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#v2_docref_dash_batch') IS NOT NULL BEGIN DROP TABLE #v2_docref_dash_batch END
            select dd.invoice, rpd.id_header as tbl_id
            into #v2_docref_dash_batch
            from #v2_docref_dash dd
            join realisasi_pembayaran_det rpd on rpd.no_bayar = dd.invoice and rpd.transaksi = 'DOC'
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#v2_dj_pph_dash_matched') IS NOT NULL BEGIN DROP TABLE #v2_dj_pph_dash_matched END
            select db.invoice, un.tbl_id, sum(un.nominal) as pph
            into #v2_dj_pph_dash_matched
            from #v2_dj_pph_still_unmatched un
            join #v2_docref_dash_batch db on db.tbl_id = un.tbl_id
            join #v2_dj_pph_fallback dj on dj.id = un.id
            cross apply string_split(dj.keterangan, ' ') tok
            where tok.value = '-'
            group by db.invoice, un.tbl_id
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#v2_pph_helper') IS NOT NULL BEGIN DROP TABLE #v2_pph_helper END
            select invoice, tbl_id, sum(pph) as pph
            into #v2_pph_helper
            from (
                select invoice, tbl_id, pph from #v2_dj_pph_direct
                union all
                select invoice, tbl_id, pph from #v2_dj_pph_fallback_matched
                union all
                select invoice, tbl_id, pph from #v2_dj_pph_single_matched
                union all
                select invoice, tbl_id, pph from #v2_dj_pph_dash_matched
            ) x
            group by invoice, tbl_id
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE UNIQUE CLUSTERED INDEX ix_v2_pph_helper ON #v2_pph_helper(invoice, tbl_id)

            IF OBJECT_ID('tempdb..#v2_pembayaran') IS NOT NULL BEGIN DROP TABLE #v2_pembayaran END
            select nomor, tanggal, supplier, total, kode_trans, jenis_trans, jenis_hutang
            into #v2_pembayaran
            from (
                select rpd.no_bayar as nomor, rp.tgl_bayar as tanggal, rp.supplier, (rpd.transfer+rpd.potongan+rpd.uang_muka+rpd.cn+rpd.dn) as total, rp.nomor as kode_trans, 'Realisasi Pembayaran' as jenis_trans, 'DOC' as jenis_hutang
                from realisasi_pembayaran_det rpd
                left join realisasi_pembayaran rp on rpd.id_header = rp.id
                where rpd.transaksi = 'DOC'

                union all

                select cpd.nomor, cp.tanggal, kpd.supplier, cpd.pakai as total, cp.no_cn as kode_trans, 'CN' as jenis_trans, 'DOC' as jenis_hutang
                from cn_post_det cpd
                left join cn_post cp on cp.id = cpd.id_header
                left join konfirmasi_pembayaran_doc kpd on kpd.nomor = cpd.nomor
                where cp.jenis_cn = 'DOC'

                union all

                /* Sejak tgl_realisasi >= 2026-08-01, setting_automatic_jurnal Verifikasi Pembayaran
                   (id=24) SENDIRI sudah pindah pakai rpd.pph langsung sbg nominal yg diposting ke
                   GL -- utk tanggal itu ke atas, rpd.pph adalah SUMBER PALING BENAR. Matching via
                   #v2_pph_helper (parsing teks keterangan/No. SJ) rawan tabrakan No. SJ dobel
                   antar-invoice dlm 1 batch pembayaran (kasus BYD/08/26/00170, lihat
                   kartu-hutang-per-invoice-v2). Utk tanggal SEBELUM itu #v2_pph_helper tetap
                   dipakai krn rpd.pph lawas belum bisa diandalkan (banyak NULL/0). */
                select rpd.no_bayar as nomor, rp.tgl_bayar as tanggal, rp.supplier, rpd.pph as total, rp.nomor as kode_trans, 'PPh' as jenis_trans, 'DOC' as jenis_hutang
                from realisasi_pembayaran_det rpd
                left join realisasi_pembayaran rp on rpd.id_header = rp.id
                where rpd.transaksi = 'DOC' and rp.tgl_realisasi >= '2026-08-01' and isnull(rpd.pph, 0) <> 0

                union all

                select rpd.no_bayar as nomor, rp.tgl_bayar as tanggal, rp.supplier, ph.pph as total, rp.nomor as kode_trans, 'PPh' as jenis_trans, 'DOC' as jenis_hutang
                from realisasi_pembayaran_det rpd
                left join realisasi_pembayaran rp on rpd.id_header = rp.id
                inner join #v2_pph_helper ph on ph.invoice = rpd.no_bayar and ph.tbl_id = cast(rp.id as varchar)
                where rpd.transaksi = 'DOC' and rp.tgl_realisasi < '2026-08-01'

                union all

                select rpd.no_bayar as nomor, rp.tgl_bayar as tanggal, rp.supplier, rpd.pembulatan as total, rp.nomor as kode_trans, 'Pembulatan' as jenis_trans, 'DOC' as jenis_hutang
                from realisasi_pembayaran_det rpd
                left join realisasi_pembayaran rp on rpd.id_header = rp.id
                where rpd.transaksi = 'DOC' and isnull(rpd.pembulatan, 0) <> 0

                union all

                select rpd.no_bayar as nomor, rp.tgl_bayar as tanggal, rp.supplier, (rpd.transfer+rpd.potongan+rpd.uang_muka+rpd.cn+rpd.dn) as total, rp.nomor as kode_trans, 'Realisasi Pembayaran' as jenis_trans, 'PAKAN' as jenis_hutang
                from realisasi_pembayaran_det rpd
                left join realisasi_pembayaran rp on rpd.id_header = rp.id
                where rpd.transaksi = 'PAKAN'

                union all

                select cpd.nomor, cp.tanggal, kpp.supplier, cpd.pakai as total, cp.no_cn as kode_trans, 'CN' as jenis_trans, 'PAKAN' as jenis_hutang
                from cn_post_det cpd
                left join cn_post cp on cp.id = cpd.id_header
                left join konfirmasi_pembayaran_pakan kpp on kpp.nomor = cpd.nomor
                where cp.jenis_cn = 'PKN'

                union all

                select rpd.no_bayar as nomor, rp.tgl_bayar as tanggal, rp.supplier, rpd.pembulatan as total, rp.nomor as kode_trans, 'Pembulatan' as jenis_trans, 'PAKAN' as jenis_hutang
                from realisasi_pembayaran_det rpd
                left join realisasi_pembayaran rp on rpd.id_header = rp.id
                where rpd.transaksi = 'PAKAN' and isnull(rpd.pembulatan, 0) <> 0

                union all

                select rpd.no_bayar as nomor, rp.tgl_bayar as tanggal, rp.supplier, (rpd.transfer+rpd.potongan+rpd.uang_muka+rpd.cn+rpd.dn) as total, rp.nomor as kode_trans, 'Realisasi Pembayaran' as jenis_trans, (case when rp.supplier = '19B004' then 'OVK ORP' else 'OVK NON ORP' end) as jenis_hutang
                from realisasi_pembayaran_det rpd
                left join realisasi_pembayaran rp on rpd.id_header = rp.id
                where rpd.transaksi = 'VOADIP'

                union all

                select rpd.no_bayar as nomor, rp.tgl_bayar as tanggal, rp.supplier, rpd.pembulatan as total, rp.nomor as kode_trans, 'Pembulatan' as jenis_trans, (case when rp.supplier = '19B004' then 'OVK ORP' else 'OVK NON ORP' end) as jenis_hutang
                from realisasi_pembayaran_det rpd
                left join realisasi_pembayaran rp on rpd.id_header = rp.id
                where rpd.transaksi = 'VOADIP' and isnull(rpd.pembulatan, 0) <> 0

                union all

                select mi.no_invoice as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, mi.no_mm as kode_trans, 'Pelunasan Memorial (Persediaan OVK)' as jenis_trans, (case when mi.coa_tujuan = '21180.300' then 'OVK ORP' else 'OVK NON ORP' end) as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                left hash join #v2_kh_ovk kh_memo_ovk on kh_memo_ovk.nomor = mi.no_invoice
                left hash join #v2_invoice_hantu_ovk ih_memo_ovk on ih_memo_ovk.nomor = mi.no_invoice
                where mi.coa_tujuan in ('21180.300', '21174.000') and nullif(mi.no_invoice, '') is not null
                  and (kh_memo_ovk.nomor is not null or ih_memo_ovk.nomor is not null)

                union all

                select nomor, tanggal, supplier, total, kode_trans, jenis_trans, jenis_hutang
                from (
                    select mi.no_invoice as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, mi.no_mm as kode_trans, 'Pelunasan Memorial (Persediaan Pakan)' as jenis_trans, 'PAKAN' as jenis_hutang,
                        row_number() over (partition by mi.no_invoice, mi.nilai order by mi.no_mm asc) as rn_dedup
                    from mmitem mi
                    left join mm m on mi.no_mm = m.no_mm
                    left hash join #v2_kh_pakan kh_memo_pakan on kh_memo_pakan.nomor = mi.no_invoice
                    where mi.coa_tujuan = '21180.100' and nullif(mi.no_invoice, '') is not null and kh_memo_pakan.nomor is not null
                ) dedup_pelunasan_pakan
                where rn_dedup = 1

                union all

                select mi.no_mm as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, mi.no_mm as kode_trans, 'Reklasifikasi Hutang DOC ke Pakan' as jenis_trans, 'PAKAN' as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                where mi.coa_asal = '21180.200' and mi.coa_tujuan = '21180.100' and nullif(mi.no_invoice, '') is null

                union all

                select mi.no_invoice as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, mi.no_mm as kode_trans, 'Pembayaran Memorial' as jenis_trans, 'DOC' as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                left hash join (select distinct no_bayar from realisasi_pembayaran_det where transaksi = 'DOC') rp_ref on rp_ref.no_bayar = mi.no_invoice
                where mi.coa_asal = '21180.200' and nullif(mi.no_invoice, '') is not null and rp_ref.no_bayar is null

                union all

                select rtrim(substring(cast(m.keterangan as varchar(300)), patindex('%PEMBALIK ATAS%', cast(m.keterangan as varchar(300))) + 14, 50)) as nomor,
                    cast(m.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, mi.no_mm as kode_trans, 'Pembalik Memorial' as jenis_trans, 'DOC' as jenis_hutang
                from mm m
                join mmitem mi on mi.no_mm = m.no_mm
                where cast(m.keterangan as varchar(300)) like '%PEMBALIK ATAS%'
                  and mi.coa_asal = '21180.200' and nullif(mi.no_invoice, '') is null

                union all

                select mi.no_invoice as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, mi.no_mm as kode_trans, 'Pelunasan Memorial (Persediaan DOC)' as jenis_trans, 'DOC' as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                left hash join #v2_kh_doc kh_memo2 on kh_memo2.nomor = mi.no_invoice
                where mi.coa_asal = '12040.000' and mi.coa_tujuan = '21180.200' and nullif(mi.no_invoice, '') is not null and kh_memo2.nomor is not null
            ) x
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE INDEX ix_v2_pembayaran_nomor ON #v2_pembayaran(nomor)
            /* ============================================================================
               END V2-PORTED BLOCK
               ============================================================================ */

            select 
                data.supplier,
                supl.nama as nama_supplier,
                data.jenis,
                cast(isnull(sum(data.saldo_awal), 0) as decimal(15, 2)) as saldo_awal,
                cast(isnull(sum(data.debet), 0) as decimal(15, 2)) as debet,
                cast(isnull(sum(data.kredit), 0) as decimal(15, 2)) as kredit,
                cast(isnull(sum(data.saldo_akhir), 0) as decimal(15, 2)) as saldo_akhir
            from
            (
                select
                    _data.supplier,
                    _data.jenis,
                    _data.unit,
                    isnull(sum(_data.saldo_awal), 0) as saldo_awal,
                    isnull(sum(_data.debet), 0) as debet,
                    isnull(sum(_data.kredit), 0) as kredit,
                    (isnull(sum(_data.saldo_awal), 0)+isnull(sum(_data.debet), 0))-isnull(sum(_data.kredit), 0) as saldo_akhir
                from
                (
                    /* SALDO AWAL */
                    select
                        sa.supplier,
                        (isnull(sum(sa.debet), 0) - isnull(sum(sa.kredit), 0)) as saldo_awal,
                        0 as debet,
                        0 as kredit,
                        sa.jenis,
                        sa.unit
                    from
                    (
                        /* DEBET */
                        select 
                            kpd.nomor,
                            kpd.supplier,
                            kpdd.total as debet,
                            0 as kredit,
                            'DOC' as jenis,
                            kpdd.kode_unit as unit
                        from konfirmasi_pembayaran_doc_det kpdd
                        left join
                            konfirmasi_pembayaran_doc kpd
                            on
                                kpdd.id_header = kpd.id
                        left join
                            (
                                select td1.* from terima_doc td1
                                right join
                                    (select max(id) as id, no_order from terima_doc group by no_order) td2
                                    on
                                        td1.id = td2.id
                            ) td
                            on
                                td.no_order = kpdd.no_order
                        where
                            cast(td.datang as date) < '".$start_date."'

                        union all

                        select 
                            kpp.nomor, 	
                            kpp.supplier, 
                            kppd.total as debet,
                            0 as kredit,
                            'PAKAN' as jenis,
                            kppd.kode_unit as unit
                        from konfirmasi_pembayaran_pakan_det kppd
                        left join
                            konfirmasi_pembayaran_pakan kpp
                            on
                                kppd.id_header = kpp.id
                        where
                            kpp.tgl_bayar < '".$start_date."'

                        union all

                        /* OA PAKAN */
                        select
                            oa.nomor,
                            oa.supplier,
                            sum(oa.total) as debet,
                            0 as kredit,
                            'OA PAKAN' as jenis,
                            null as unit
                        from (
                            select kpop.nomor, kpop.ekspedisi_id as supplier, (kpop.total+kpop.potongan_pph_23) as total from konfirmasi_pembayaran_oa_pakan kpop
                            where
                                kpop.tgl_bayar < '".$start_date."'

                            union all

                            select tp.no_bbm as nomor, kp.ekspedisi_id as supplier, sum(dtp.jumlah)*kp.ongkos_angkut as total from det_terima_pakan dtp
                            left join
                                terima_pakan tp
                                on
                                    dtp.id_header = tp.id
                            left join
                                kirim_pakan kp
                                on
                                    tp.id_kirim_pakan = kp.id
                            where
                                tp.tgl_terima < '".$start_date."' and
                                kp.jenis_kirim = 'opkg' and
                                not exists (select * from konfirmasi_pembayaran_oa_pakan_det kpopd where no_sj = kp.no_sj)
                            group by
                                tp.no_bbm, kp.ekspedisi_id, kp.ongkos_angkut

                            union all

                            select opp.no_sj as nomor, krm.ekspedisi_id as supplier, opp.ongkos_angkut as total from oa_pindah_pakan opp
                            left join
                                (
                                    select kp.no_sj, tp.no_bbm as kode_trans, kp.ekspedisi_id, tp.tgl_terima as tanggal from kirim_pakan kp
                                    left join
                                        terima_pakan tp 
                                        on
                                            kp.id = tp.id_kirim_pakan
                                    group by
                                        kp.no_sj, tp.no_bbm, kp.ekspedisi_id, tp.tgl_terima
                                    
                                    union all
                                    
                                    select no_retur as no_sj, no_retur as kode_trans, ekspedisi_id, tgl_retur as tanggal from retur_pakan rp 
                                ) krm
                                on
                                    opp.no_sj = krm.no_sj
                            where
                                krm.tanggal < '".$start_date."' and
                                not exists (select * from konfirmasi_pembayaran_oa_pakan_det kpopd where no_sj = opp.no_sj)
                        ) oa
                        group by
                            oa.nomor,
                            oa.supplier
                        /* END - OA PAKAN */

                        union all

                        select
                            kpv.nomor, 
                            kpv.supplier, 
                            kpv.total as debet,
                            0 as kredit,
                            pc.kode as jenis,
                            kpvd.kode_unit as unit
                        from konfirmasi_pembayaran_voadip kpv
                        left join
                            konfirmasi_pembayaran_voadip_det kpvd 
                            on
                                kpv.id = kpvd.id_header
                        left join
                            (select * from pelanggan_coa where kode like '%OVK%') pc
                            on
                                pc.no_pelanggan = kpv.supplier
                        where
                            kpv.tgl_bayar < '".$start_date."'

                        union all

                        select
                            kpp.nomor,
                            kpp.mitra as supplier,
                            kpp.total as debet,
                            0 as kredit,
                            'RHPP' as jenis,
                            SUBSTRING(REPLACE(REPLACE(kpp.invoice, 'INV/RHPP/G/', ''), 'INV/RHPP/', ''), 1, 3) as unit
                        from konfirmasi_pembayaran_peternak kpp
                        where
                            kpp.tgl_bayar < '".$start_date."'

                        union all

                        select
                            op.no_order as nomor,
                            op.supplier,
                            op.total as debet,
                            0 as kredit,
                            pc.kode as jenis,
                            op.unit 
                        from order_peralatan op
                        left join
                            (select * from pelanggan_coa where kode like '%PERALATAN%') pc
                            on
                                pc.no_pelanggan = op.supplier
                        where
                            op.tgl_order < '".$start_date."'

                        union all

                        select
                            dpd.nomor,
                            konfir.supplier,
                            dpd.pakai as debet,
                            0 as kredit,
                            case
                                when dp.jenis_dn = 'PKN' then
                                    'PAKAN'
                                else
                                    dp.jenis_dn 
                            end as jenis,
                            konfir.kode_unit as unit
                        from dn_post_det dpd 
                        left join
                            dn_post dp 
                            on
                                dpd.id_header = dp.id
                        left join
                            (
                                /* DOC */
                                select
                                    kpd.nomor,
                                    kpd.supplier,
                                    kpdd.kode_unit
                                from konfirmasi_pembayaran_doc kpd 
                                left join
                                    (select id_header, kode_unit from konfirmasi_pembayaran_doc_det group by id_header, kode_unit) kpdd
                                    on
                                        kpd.id = kpdd.id_header
                                    
                                union all
                                
                                /* PAKAN */
                                select
                                    kpp.nomor,
                                    kpp.supplier,
                                    kppd.kode_unit
                                from konfirmasi_pembayaran_pakan kpp 
                                left join
                                    (select id_header, kode_unit from konfirmasi_pembayaran_pakan_det group by id_header, kode_unit) kppd
                                    on
                                        kpp.id = kppd.id_header
                                    
                                union all
                                
                                /* OVK */
                                select
                                    kpv.nomor,
                                    kpv.supplier,
                                    kpvd.kode_unit
                                from konfirmasi_pembayaran_voadip kpv 
                                left join
                                    (select id_header, kode_unit from konfirmasi_pembayaran_voadip_det group by id_header, kode_unit) kpvd
                                    on
                                        kpv.id = kpvd.id_header
                                        
                                union all
                                
                                /* RHPP */
                                select
                                    kpp.nomor,
                                    kpp.mitra as supplier,
                                    SUBSTRING(REPLACE(REPLACE(kpp.invoice, 'INV/RHPP/G/', ''), 'INV/RHPP/', ''), 1, 3) as kode_unit
                                from konfirmasi_pembayaran_peternak kpp
                                
                                union all
                                
                                /* OA PAKAN */
                                select
                                    kpop.nomor,
                                    kpop.ekspedisi_id as supplier,
                                    kpopd.kode_unit
                                from konfirmasi_pembayaran_oa_pakan kpop 
                                left join
                                    (
                                        select 
                                            kpopd.id_header, 
                                            SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 ) as kode_unit
                                        from konfirmasi_pembayaran_oa_pakan_det kpopd 
                                        left join
                                            (
                                                select no_sj, no_order from kirim_pakan kp 
                                                
                                                union all
                                                
                                                select no_retur as no_sj, no_order from retur_pakan rp 
                                            ) kp 
                                            on
                                                kpopd.no_sj = kp.no_sj
                                        group by 
                                            kpopd.id_header, 
                                            SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 )
                                    ) kpopd
                                    on
                                        kpop.id = kpopd.id_header
                            ) konfir
                            on
                                konfir.nomor = dpd.nomor
                        where
                            /* DN DOC dikecualikan: memorialnya (coa_asal 21180.200) sudah dihitung di cabang INVOICE LEWAT MEMO, jangan dobel. */
                            dp.jenis_dn <> 'DOC' and
                            dp.tanggal < '".$start_date."'

                        union all

                        /* INVOICE LEWAT MEMO */
                        select * from (
                            select
                                mi.no_mm as nomor,
                                m.no_supplier as supplier,
                                mi.nilai as debet,
                                0 as kredit,
                                pc.kode as jenis,
                                m.unit
                            from mmitem mi
                            left join
                                mm m
                                on
                                    mi.no_mm = m.no_mm
                            left join
                                pelanggan_coa pc
                                on
                                    pc.no_pelanggan = m.no_supplier and
                                    pc.no_coa = mi.coa_asal
                            where
                                mi.coa_asal in ('21180.300', '21174.000', '21180.200', '21180.100') and
                                cast(mi.tgl_mm as date) < '".$start_date."'
                        ) inv_mm
                        /* END - INVOICE LEWAT MEMO */
                        /* END - DEBET */

                        union all

                        /* KREDIT */
                        select
                            cpd.nomor,
                            konfir.supplier,
                            0 as debet,
                            cpd.pakai as kredit,
                            case
                                when cp.jenis_cn = 'PKN' then
                                    'PAKAN'
                                else
                                    cp.jenis_cn 
                            end as jenis,
                            konfir.kode_unit as unit
                        from cn_post_det cpd 
                        left join
                            cn_post cp
                            on
                                cpd.id_header = cp.id
                        left join
                            (
                                /* DOC */
                                select
                                    kpd.nomor,
                                    kpd.supplier,
                                    kpdd.kode_unit
                                from konfirmasi_pembayaran_doc kpd 
                                left join
                                    (select id_header, kode_unit from konfirmasi_pembayaran_doc_det group by id_header, kode_unit) kpdd
                                    on
                                        kpd.id = kpdd.id_header
                                    
                                union all
                                
                                /* PAKAN */
                                select
                                    kpp.nomor,
                                    kpp.supplier,
                                    kppd.kode_unit
                                from konfirmasi_pembayaran_pakan kpp 
                                left join
                                    (select id_header, kode_unit from konfirmasi_pembayaran_pakan_det group by id_header, kode_unit) kppd
                                    on
                                        kpp.id = kppd.id_header
                                    
                                union all
                                
                                /* OVK */
                                select
                                    kpv.nomor,
                                    kpv.supplier,
                                    kpvd.kode_unit
                                from konfirmasi_pembayaran_voadip kpv 
                                left join
                                    (select id_header, kode_unit from konfirmasi_pembayaran_voadip_det group by id_header, kode_unit) kpvd
                                    on
                                        kpv.id = kpvd.id_header
                                        
                                union all
                                
                                /* RHPP */
                                select
                                    kpp.nomor,
                                    kpp.mitra as supplier,
                                    SUBSTRING(REPLACE(REPLACE(kpp.invoice, 'INV/RHPP/G/', ''), 'INV/RHPP/', ''), 1, 3) as kode_unit
                                from konfirmasi_pembayaran_peternak kpp
                                
                                union all
                                
                                /* OA PAKAN */
                                select
                                    kpop.nomor,
                                    kpop.ekspedisi_id as supplier,
                                    kpopd.kode_unit
                                from konfirmasi_pembayaran_oa_pakan kpop 
                                left join
                                    (
                                        select 
                                            kpopd.id_header, 
                                            SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 ) as kode_unit
                                        from konfirmasi_pembayaran_oa_pakan_det kpopd 
                                        left join
                                            (
                                                select no_sj, no_order from kirim_pakan kp 
                                                
                                                union all
                                                
                                                select no_retur as no_sj, no_order from retur_pakan rp 
                                            ) kp 
                                            on
                                                kpopd.no_sj = kp.no_sj
                                        group by 
                                            kpopd.id_header, 
                                            SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 )
                                    ) kpopd
                                    on
                                        kpop.id = kpopd.id_header
                            ) konfir
                            on
                                konfir.nomor = cpd.nomor
                        where
                            cp.tanggal < '".$start_date."'

                        union all
                
                        select
                            bp.no_faktur,
                            op.supplier,
                            0 as debet,
                            bp.jml_bayar as kredit,
                            'PERALATAN' as jenis,
                            op.unit 
                        from bayar_peralatan bp 
                        left join
                            order_peralatan op 
                            on
                                op.no_order = bp.no_order 
                        where
                            bp.tgl_realisasi is not null and bp.tgl_realisasi < '".$start_date."'

                        union all

                        select
                            rpd.no_bayar as nomor,
                            rp.supplier,
                            0 as debet,
                            case
                                when rpd.transaksi = 'DOC' then
                                    case
                                        /* Sejak tgl_realisasi >= 2026-08-01, sistem sendiri (setting_
                                           automatic_jurnal Verifikasi Pembayaran id=24) sudah pakai
                                           rpd.pph langsung sbg nominal yg diposting ke GL -- pakai itu
                                           drpd rumus konfir.pph (0,25% x total, tanpa penyesuaian
                                           DN/CN) spy match apa yg benar2 kejurnal. Lihat kartu-hutang-
                                           per-invoice-v2 utk kasus BYD/08/26/00170. */
                                        when rp.tgl_realisasi >= '2026-08-01' then
                                            rpd.transfer+isnull(rpd.pph, 0)
                                        when konfir.tanggal <= '2025-09-20' then
                                            rpd.transfer
                                        else
                                            rpd.transfer+konfir.pph
                                            -- rpd.transfer
                                    end
                                else
                                    rpd.transfer
                            end as kredit,
                            pc.kode as jenis,
                            konfir.kode_unit as unit
                        from realisasi_pembayaran_det rpd
                        left join
                            realisasi_pembayaran rp
                            on
                                rpd.id_header = rp.id
                        left join
                            (
                                select kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar as tanggal, (kpdd.total * (0.25/100)) as pph from konfirmasi_pembayaran_doc_det kpdd 
                                left join
                                    konfirmasi_pembayaran_doc kpd 
                                    on
                                        kpdd.id_header = kpd.id
                                left join
                                    (
                                        select td1.* from terima_doc td1
                                        right join
                                            (select max(id) as id, no_order from terima_doc group by no_order) td2
                                            on
                                                td1.id = td2.id
                                    ) td
                                    on
                                        td.no_order = kpdd.no_order
                                group by
                                    kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar, kpdd.total
                                    
                                union all
                                
                                select kppd.kode_unit, kpp.nomor, null as tanggal, 0 as pph from konfirmasi_pembayaran_pakan_det kppd 
                                left join
                                    konfirmasi_pembayaran_pakan kpp 
                                    on
                                        kppd.id_header = kpp.id
                                group by
                                    kppd.kode_unit, kpp.nomor
                                    
                                union all
                                
                                select kpvd.kode_unit, kpv.nomor, null as tanggal, 0 as pph from konfirmasi_pembayaran_voadip_det kpvd 
                                left join
                                    konfirmasi_pembayaran_voadip kpv 
                                    on
                                        kpvd.id_header = kpv.id
                                group by
                                    kpvd.kode_unit, kpv.nomor

                                union all

                                select unit as kode_unit, mm.no_mm as nomor, null as tanggal, 0 as pph from mm
                            ) konfir
                            on
                                rpd.no_bayar = konfir.nomor
                        left join
                            pelanggan_coa pc
                            on
                                pc.no_pelanggan = rp.supplier and
                                pc.kode like '%'+REPLACE(rpd.transaksi, 'VOADIP', 'OVK')+'%'
                        where
                            (rp.tgl_realisasi is not null and rp.tgl_realisasi < '".$start_date."') and
                            rpd.transaksi not in ('PLASMA', 'OA PAKAN')

                        union all

                        /* KREDIT PLASMA - pakai rp.peternak sebagai supplier */
                        select
                            rpd.no_bayar as nomor,
                            rp.peternak as supplier,
                            0 as debet,
                            rpd.transfer as kredit,
                            'RHPP' as jenis,
                            SUBSTRING(REPLACE(REPLACE(rpd.no_bayar, 'INV/RHPP/G/', ''), 'INV/RHPP/', ''), 1, 3) as unit
                        from realisasi_pembayaran_det rpd
                        left join
                            realisasi_pembayaran rp
                            on
                                rpd.id_header = rp.id
                        where
                            rpd.transaksi = 'PLASMA' and
                            (rp.tgl_realisasi is not null and rp.tgl_realisasi < '".$start_date."')

                        union all

                        /* KREDIT OA PAKAN - pakai rp.ekspedisi sebagai supplier */
                        select
                            rpd.no_bayar as nomor,
                            rp.ekspedisi as supplier,
                            0 as debet,
                            rpd.transfer as kredit,
                            'OA PAKAN' as jenis,
                            null as unit
                        from realisasi_pembayaran_det rpd
                        left join
                            realisasi_pembayaran rp
                            on
                                rpd.id_header = rp.id
                        where
                            rpd.transaksi = 'OA PAKAN' and
                            (rp.tgl_realisasi is not null and rp.tgl_realisasi < '".$start_date."')

                        union all

                        /* BAYAR LEWAT MEMO */
                        select * from (
                            select
                                mi.no_invoice as nomor,
                                isnull(nullif(konfir.supplier,''), m.no_supplier) as supplier,
                                0 as debet,
                                mi.nilai as kredit,
                                pc.kode as jenis,
                                m.unit
                            from mmitem mi
                            left join
                                mm m
                                on
                                    mi.no_mm = m.no_mm
                            left join
                                (
                                    select nomor, supplier from konfirmasi_pembayaran_voadip group by nomor, supplier

                                    union all

                                    select nomor, supplier from konfirmasi_pembayaran_pakan group by nomor, supplier

                                    union all

                                    select nomor, supplier from konfirmasi_pembayaran_doc group by nomor, supplier

                                    union all

                                    select nomor, ekspedisi_id as supplier from konfirmasi_pembayaran_oa_pakan group by nomor, ekspedisi_id

                                    union all

                                    select nomor, mitra as supplier from konfirmasi_pembayaran_peternak group by nomor, mitra
                                ) konfir
                                on
                                    mi.no_invoice = konfir.nomor
                            left join
                                pelanggan_coa pc
                                on
                                    pc.no_pelanggan = isnull(nullif(konfir.supplier,''), m.no_supplier) and
                                    pc.no_coa = mi.coa_tujuan
                            where
                                mi.coa_asal not in ('71105.003') and
                                mi.coa_tujuan in ('21180.300', '21174.000', '21180.200', '21180.100') and
                                cast(mi.tgl_mm as date) < '".$start_date."'
                        ) byr_mm
                        /* END - BAYAR LEWAT MEMO */
                        /* END - KREDIT */
                    ) sa
                    where isnull(sa.jenis, '') not in ('DOC', 'PAKAN', 'OVK') /* diganti cabang V2 di bawah */
                    group by
                        sa.supplier,
                        sa.jenis,
                        sa.unit

                    union all

                    /* SALDO AWAL - V2 (DOC/PAKAN/OVK) */
                    select
                        v2sa.supplier,
                        (isnull(sum(v2sa.debet), 0) - isnull(sum(v2sa.kredit), 0)) as saldo_awal,
                        0 as debet,
                        0 as kredit,
                        v2sa.jenis,
                        cast(null as varchar(10)) as unit
                    from
                    (
                        select supplier, total as debet, 0 as kredit, case when jenis_hutang in ('OVK ORP', 'OVK NON ORP') then 'OVK' else jenis_hutang end as jenis
                        from #v2_sumber_hutang
                        where tanggal < '".$start_date."'

                        union all

                        /* Sama dgn Kartu Hutang Lengkap: pembayaran hanya dihitung utk invoice yg sudah ada (tanggal < awal periode);
                           pembayaran utk invoice yg belum/tdk ada di sumber hutang tidak ikut Saldo Awal. */
                        select supplier, 0 as debet, total as kredit, case when jenis_hutang in ('OVK ORP', 'OVK NON ORP') then 'OVK' else jenis_hutang end as jenis
                        from #v2_pembayaran
                        where tanggal < '".$start_date."'
                          and nomor in (select nomor from #v2_sumber_hutang where tanggal < '".$start_date."')
                    ) v2sa
                    group by
                        v2sa.supplier,
                        v2sa.jenis
                    /* END - SALDO AWAL */

                    union all

                    /* TRANSAKSI BULAN BERJALAN */
                    select
                        trans.supplier,
                        0 as saldo_awal,
                        isnull(sum(trans.debet), 0) as debet,
                        isnull(sum(trans.kredit), 0) as kredit,
                        trans.jenis,
                        trans.unit
                    from
                    (
                        /* DEBET */
                        select 
                            kpd.nomor,
                            kpd.supplier,
                            kpdd.total as debet,
                            0 as kredit,
                            'DOC' as jenis,
                            kpdd.kode_unit as unit
                        from konfirmasi_pembayaran_doc_det kpdd
                        left join
                            konfirmasi_pembayaran_doc kpd
                            on
                                kpdd.id_header = kpd.id
                        left join
                            (
                                select td1.* from terima_doc td1
                                right join
                                    (select max(id) as id, no_order from terima_doc group by no_order) td2
                                    on
                                        td1.id = td2.id
                            ) td
                            on
                                td.no_order = kpdd.no_order
                        where
                            cast(td.datang as date) between '".$start_date."' and '".$end_date."'

                        union all

                        select 
                            kpp.nomor, 	
                            kpp.supplier, 
                            kppd.total as debet,
                            0 as kredit,
                            'PAKAN' as jenis,
                            kppd.kode_unit as unit
                        from konfirmasi_pembayaran_pakan_det kppd
                        left join
                            konfirmasi_pembayaran_pakan kpp
                            on
                                kppd.id_header = kpp.id
                        where
                            kpp.tgl_bayar between '".$start_date."' and '".$end_date."'

                        union all

                        /* OA PAKAN */
                        select
                            oa.nomor,
                            oa.supplier,
                            sum(oa.total) as debet,
                            0 as kredit,
                            'OA PAKAN' as jenis,
                            null as unit
                        from (
                            select kpop.nomor, kpop.ekspedisi_id as supplier, (kpop.total+kpop.potongan_pph_23) as total from konfirmasi_pembayaran_oa_pakan kpop
                            where
                                kpop.tgl_bayar between '".$start_date."' and '".$end_date."'

                            union all

                            select tp.no_bbm as nomor, kp.ekspedisi_id as supplier, sum(dtp.jumlah)*kp.ongkos_angkut as total from det_terima_pakan dtp
                            left join
                                terima_pakan tp
                                on
                                    dtp.id_header = tp.id
                            left join
                                kirim_pakan kp
                                on
                                    tp.id_kirim_pakan = kp.id
                            where
                                tp.tgl_terima between '".$start_date."' and '".$end_date."' and
                                kp.jenis_kirim = 'opkg' and
                                not exists (select * from konfirmasi_pembayaran_oa_pakan_det kpopd where no_sj = kp.no_sj)
                            group by
                                tp.no_bbm, kp.ekspedisi_id, kp.ongkos_angkut

                            union all

                            select opp.no_sj as nomor, krm.ekspedisi_id as supplier, opp.ongkos_angkut as total from oa_pindah_pakan opp
                            left join
                                (
                                    select kp.no_sj, tp.no_bbm as kode_trans, kp.ekspedisi_id, tp.tgl_terima as tanggal from kirim_pakan kp
                                    left join
                                        terima_pakan tp 
                                        on
                                            kp.id = tp.id_kirim_pakan
                                    group by
                                        kp.no_sj, tp.no_bbm, kp.ekspedisi_id, tp.tgl_terima
                                    
                                    union all
                                    
                                    select no_retur as no_sj, no_retur as kode_trans, ekspedisi_id, tgl_retur as tanggal from retur_pakan rp 
                                ) krm
                                on
                                    opp.no_sj = krm.no_sj
                            where
                                krm.tanggal between '".$start_date."' and '".$end_date."' and
                                not exists (select * from konfirmasi_pembayaran_oa_pakan_det kpopd where no_sj = opp.no_sj)
                        ) oa
                        group by
                            oa.nomor,
                            oa.supplier
                        /* END - OA PAKAN */

                        union all

                        select
                            kpv.nomor, 
                            kpv.supplier, 
                            kpv.total as debet,
                            0 as kredit,
                            pc.kode as jenis,
                            kpvd.kode_unit as unit
                        from konfirmasi_pembayaran_voadip_det kpvd
                        left join
                            konfirmasi_pembayaran_voadip kpv
                            on
                                kpvd.id_header = kpv.id
                        left join
                            (select * from pelanggan_coa where kode like '%OVK%') pc
                            on
                                pc.no_pelanggan = kpv.supplier
                        where
                            kpv.tgl_bayar between '".$start_date."' and '".$end_date."'

                        union all

                        select
                            kpp.nomor,
                            kpp.mitra as supplier,
                            kpp.total as debet,
                            0 as kredit,
                            'RHPP' as jenis,
                            SUBSTRING(REPLACE(REPLACE(kpp.invoice, 'INV/RHPP/G/', ''), 'INV/RHPP/', ''), 1, 3) as unit
                        from konfirmasi_pembayaran_peternak kpp
                        where
                            kpp.tgl_bayar between '".$start_date."' and '".$end_date."'

                        union all

                        select
                            op.no_order as nomor,
                            op.supplier,
                            op.total as debet,
                            0 as kredit,
                            'PERALATAN' as jenis,
                            op.unit
                        from order_peralatan op
                        where
                            op.tgl_order between '".$start_date."' and '".$end_date."'

                        union all

                        select
                            dpd.nomor,
                            konfir.supplier,
                            dpd.pakai as debet,
                            0 as kredit,
                            case
                                when dp.jenis_dn = 'PKN' then
                                    'PAKAN'
                                else
                                    dp.jenis_dn 
                            end as jenis,
                            konfir.kode_unit as unit
                        from dn_post_det dpd 
                        left join
                            dn_post dp 
                            on
                                dpd.id_header = dp.id
                        left join
                            (
                                /* DOC */
                                select
                                    kpd.nomor,
                                    kpd.supplier,
                                    kpdd.kode_unit
                                from konfirmasi_pembayaran_doc kpd 
                                left join
                                    (select id_header, kode_unit from konfirmasi_pembayaran_doc_det group by id_header, kode_unit) kpdd
                                    on
                                        kpd.id = kpdd.id_header
                                    
                                union all
                                
                                /* PAKAN */
                                select
                                    kpp.nomor,
                                    kpp.supplier,
                                    kppd.kode_unit
                                from konfirmasi_pembayaran_pakan kpp 
                                left join
                                    (select id_header, kode_unit from konfirmasi_pembayaran_pakan_det group by id_header, kode_unit) kppd
                                    on
                                        kpp.id = kppd.id_header
                                    
                                union all
                                
                                /* OVK */
                                select
                                    kpv.nomor,
                                    kpv.supplier,
                                    kpvd.kode_unit
                                from konfirmasi_pembayaran_voadip kpv 
                                left join
                                    (select id_header, kode_unit from konfirmasi_pembayaran_voadip_det group by id_header, kode_unit) kpvd
                                    on
                                        kpv.id = kpvd.id_header
                                        
                                union all
                                
                                /* RHPP */
                                select
                                    kpp.nomor,
                                    kpp.mitra as supplier,
                                    SUBSTRING(REPLACE(REPLACE(kpp.invoice, 'INV/RHPP/G/', ''), 'INV/RHPP/', ''), 1, 3) as kode_unit
                                from konfirmasi_pembayaran_peternak kpp
                                
                                union all
                                
                                /* OA PAKAN */
                                select
                                    kpop.nomor,
                                    kpop.ekspedisi_id as supplier,
                                    kpopd.kode_unit
                                from konfirmasi_pembayaran_oa_pakan kpop 
                                left join
                                    (
                                        select 
                                            kpopd.id_header, 
                                            SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 ) as kode_unit
                                        from konfirmasi_pembayaran_oa_pakan_det kpopd 
                                        left join
                                            (
                                                select no_sj, no_order from kirim_pakan kp 
                                                
                                                union all
                                                
                                                select no_retur as no_sj, no_order from retur_pakan rp 
                                            ) kp 
                                            on
                                                kpopd.no_sj = kp.no_sj
                                        group by 
                                            kpopd.id_header, 
                                            SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 )
                                    ) kpopd
                                    on
                                        kpop.id = kpopd.id_header
                            ) konfir
                            on
                                konfir.nomor = dpd.nomor
                        where
                            /* DN DOC dikecualikan: memorialnya (coa_asal 21180.200) sudah dihitung di cabang INVOICE LEWAT MEMO, jangan dobel. */
                            dp.jenis_dn <> 'DOC' and
                            dp.tanggal between '".$start_date."' and '".$end_date."'

                        union all

                        /* INVOICE LEWAT MEMO */
                        select * from (
                            select
                                mi.no_invoice as nomor,
                                m.no_supplier as supplier,
                                mi.nilai as debet,
                                0 as kredit,
                                pc.kode as jenis,
                                m.unit
                            from mmitem mi
                            left join
                                mm m
                                on
                                    mi.no_mm = m.no_mm
                            left join
                                pelanggan_coa pc
                                on
                                    pc.no_pelanggan = m.no_supplier and
                                    pc.no_coa = mi.coa_asal
                            where
                                mi.coa_asal in ('21180.300', '21174.000', '21180.200', '21180.100') and
                                cast(mi.tgl_mm as date) between '".$start_date."' and '".$end_date."'
                        ) inv_mm
                        /* END - INVOICE LEWAT MEMO */
                        /* END - DEBET */

                        union all

                        /* KREDIT */
                        select
                            cpd.nomor,
                            konfir.supplier,
                            0 as debet,
                            cpd.pakai as kredit,
                            case
                                when cp.jenis_cn = 'PKN' then
                                    'PAKAN'
                                else
                                    cp.jenis_cn 
                            end as jenis,
                            konfir.kode_unit as unit
                        from cn_post_det cpd 
                        left join
                            cn_post cp
                            on
                                cpd.id_header = cp.id
                        left join
                            (
                                /* DOC */
                                select
                                    kpd.nomor,
                                    kpd.supplier,
                                    kpdd.kode_unit
                                from konfirmasi_pembayaran_doc kpd 
                                left join
                                    (select id_header, kode_unit from konfirmasi_pembayaran_doc_det group by id_header, kode_unit) kpdd
                                    on
                                        kpd.id = kpdd.id_header
                                    
                                union all
                                
                                /* PAKAN */
                                select
                                    kpp.nomor,
                                    kpp.supplier,
                                    kppd.kode_unit
                                from konfirmasi_pembayaran_pakan kpp 
                                left join
                                    (select id_header, kode_unit from konfirmasi_pembayaran_pakan_det group by id_header, kode_unit) kppd
                                    on
                                        kpp.id = kppd.id_header
                                    
                                union all
                                
                                /* OVK */
                                select
                                    kpv.nomor,
                                    kpv.supplier,
                                    kpvd.kode_unit
                                from konfirmasi_pembayaran_voadip kpv 
                                left join
                                    (select id_header, kode_unit from konfirmasi_pembayaran_voadip_det group by id_header, kode_unit) kpvd
                                    on
                                        kpv.id = kpvd.id_header
                                        
                                union all
                                
                                /* RHPP */
                                select
                                    kpp.nomor,
                                    kpp.mitra as supplier,
                                    SUBSTRING(REPLACE(REPLACE(kpp.invoice, 'INV/RHPP/G/', ''), 'INV/RHPP/', ''), 1, 3) as kode_unit
                                from konfirmasi_pembayaran_peternak kpp
                                
                                union all
                                
                                /* OA PAKAN */
                                select
                                    kpop.nomor,
                                    kpop.ekspedisi_id as supplier,
                                    kpopd.kode_unit
                                from konfirmasi_pembayaran_oa_pakan kpop 
                                left join
                                    (
                                        select 
                                            kpopd.id_header, 
                                            SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 ) as kode_unit
                                        from konfirmasi_pembayaran_oa_pakan_det kpopd 
                                        left join
                                            (
                                                select no_sj, no_order from kirim_pakan kp 
                                                
                                                union all
                                                
                                                select no_retur as no_sj, no_order from retur_pakan rp 
                                            ) kp 
                                            on
                                                kpopd.no_sj = kp.no_sj
                                        group by 
                                            kpopd.id_header, 
                                            SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 )
                                    ) kpopd
                                    on
                                        kpop.id = kpopd.id_header
                            ) konfir
                            on
                                konfir.nomor = cpd.nomor
                        where
                            cp.tanggal between '".$start_date."' and '".$end_date."'

                        union all
                
                        select
                            bp.no_faktur,
                            op.supplier,
                            0 as debet,
                            bp.jml_bayar as kredit,
                            'PERALATAN' as jenis,
                            op.unit 
                        from bayar_peralatan bp 
                        left join
                            order_peralatan op 
                            on
                                op.no_order = bp.no_order 
                        where
                            bp.tgl_realisasi is not null and bp.tgl_realisasi between '".$start_date."' and '".$end_date."'

                        union all

                        select
                            rp.nomor,
                            rp.supplier,
                            0 as debet,
                            case
                                when rpd.transaksi = 'DOC' then
                                    case
                                        /* Sejak tgl_realisasi >= 2026-08-01, sistem sendiri (setting_
                                           automatic_jurnal Verifikasi Pembayaran id=24) sudah pakai
                                           rpd.pph langsung sbg nominal yg diposting ke GL -- pakai itu
                                           drpd rumus konfir.pph (0,25% x total, tanpa penyesuaian
                                           DN/CN) spy match apa yg benar2 kejurnal. Lihat kartu-hutang-
                                           per-invoice-v2 utk kasus BYD/08/26/00170. */
                                        when rp.tgl_realisasi >= '2026-08-01' then
                                            rpd.transfer+isnull(rpd.pph, 0)
                                        when konfir.tanggal <= '2025-09-20' then
                                            rpd.transfer
                                        else
                                            rpd.transfer+konfir.pph
                                    end
                                else
                                    rpd.transfer
                            end as kredit,
                            pc.kode as jenis,
                            konfir.kode_unit as unit
                        from realisasi_pembayaran_det rpd
                        left join
                            realisasi_pembayaran rp
                            on
                                rpd.id_header = rp.id
                        left join
                            (
                                select kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar as tanggal, (kpdd.total * (0.25/100)) as pph from konfirmasi_pembayaran_doc_det kpdd 
                                left join
                                    konfirmasi_pembayaran_doc kpd 
                                    on
                                        kpdd.id_header = kpd.id
                                left join
                                    (
                                        select td1.* from terima_doc td1
                                        right join
                                            (select max(id) as id, no_order from terima_doc group by no_order) td2
                                            on
                                                td1.id = td2.id
                                    ) td
                                    on
                                        td.no_order = kpdd.no_order
                                group by
                                    kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar, kpdd.total
                                    
                                union all
                                
                                select kppd.kode_unit, kpp.nomor, null as tanggal, 0 as pph from konfirmasi_pembayaran_pakan_det kppd 
                                left join
                                    konfirmasi_pembayaran_pakan kpp 
                                    on
                                        kppd.id_header = kpp.id
                                group by
                                    kppd.kode_unit, kpp.nomor
                                    
                                union all
                                
                                select kpvd.kode_unit, kpv.nomor, null as tanggal, 0 as pph from konfirmasi_pembayaran_voadip_det kpvd 
                                left join
                                    konfirmasi_pembayaran_voadip kpv 
                                    on
                                        kpvd.id_header = kpv.id
                                group by
                                    kpvd.kode_unit, kpv.nomor

                                union all

                                select unit as kode_unit, mm.no_mm as nomor, null as tanggal, 0 as pph from mm
                            ) konfir
                            on
                                rpd.no_bayar = konfir.nomor
                        left join
                            pelanggan_coa pc
                            on
                                pc.no_pelanggan = rp.supplier and
                                pc.kode like '%'+REPLACE(rpd.transaksi, 'VOADIP', 'OVK')+'%'
                        where
                            (rp.tgl_realisasi is not null and rp.tgl_realisasi between '".$start_date."' and '".$end_date."') and
                            rpd.transaksi not in ('PLASMA', 'OA PAKAN')

                        union all

                        /* KREDIT PLASMA - pakai rp.peternak sebagai supplier */
                        select
                            rpd.no_bayar as nomor,
                            rp.peternak as supplier,
                            0 as debet,
                            rpd.transfer as kredit,
                            'RHPP' as jenis,
                            SUBSTRING(REPLACE(REPLACE(rpd.no_bayar, 'INV/RHPP/G/', ''), 'INV/RHPP/', ''), 1, 3) as unit
                        from realisasi_pembayaran_det rpd
                        left join
                            realisasi_pembayaran rp
                            on
                                rpd.id_header = rp.id
                        where
                            rpd.transaksi = 'PLASMA' and
                            (rp.tgl_realisasi is not null and rp.tgl_realisasi between '".$start_date."' and '".$end_date."')

                        union all

                        /* KREDIT OA PAKAN - pakai rp.ekspedisi sebagai supplier */
                        select
                            rpd.no_bayar as nomor,
                            rp.ekspedisi as supplier,
                            0 as debet,
                            rpd.transfer as kredit,
                            'OA PAKAN' as jenis,
                            null as unit
                        from realisasi_pembayaran_det rpd
                        left join
                            realisasi_pembayaran rp
                            on
                                rpd.id_header = rp.id
                        where
                            rpd.transaksi = 'OA PAKAN' and
                            (rp.tgl_realisasi is not null and rp.tgl_realisasi between '".$start_date."' and '".$end_date."')

                        union all

                        /* BAYAR LEWAT MEMO */
                        select * from (
                            select
                                mi.no_invoice as nomor,
                                isnull(nullif(konfir.supplier,''), m.no_supplier) as supplier,
                                0 as debet,
                                mi.nilai as kredit,
                                pc.kode as jenis,
                                m.unit
                            from mmitem mi
                            left join
                                mm m
                                on
                                    mi.no_mm = m.no_mm
                            left join
                                (
                                    select nomor, supplier from konfirmasi_pembayaran_voadip group by nomor, supplier

                                    union all

                                    select nomor, supplier from konfirmasi_pembayaran_pakan group by nomor, supplier

                                    union all

                                    select nomor, supplier from konfirmasi_pembayaran_doc group by nomor, supplier

                                    union all

                                    select nomor, ekspedisi_id as supplier from konfirmasi_pembayaran_oa_pakan group by nomor, ekspedisi_id

                                    union all

                                    select nomor, mitra as supplier from konfirmasi_pembayaran_peternak group by nomor, mitra
                                ) konfir
                                on
                                    mi.no_invoice = konfir.nomor
                            left join
                                pelanggan_coa pc
                                on
                                    pc.no_pelanggan = isnull(nullif(konfir.supplier,''), m.no_supplier) and
                                    pc.no_coa = mi.coa_tujuan
                            where
                                mi.coa_asal not in ('71105.003') and
                                mi.coa_tujuan in ('21180.300', '21174.000', '21180.200', '21180.100') and
                                cast(mi.tgl_mm as date) between '".$start_date."' and '".$end_date."'
                        ) byr_mm
                        /* END - BAYAR LEWAT MEMO */
                        /* END - KREDIT */
                    ) trans
                    where isnull(trans.jenis, '') not in ('DOC', 'PAKAN', 'OVK') /* diganti cabang V2 di bawah */
                    group by
                        trans.supplier,
                        trans.jenis,
                        trans.unit

                    union all

                    /* TRANSAKSI BULAN BERJALAN - V2 (DOC/PAKAN/OVK) */
                    select
                        v2t.supplier,
                        0 as saldo_awal,
                        isnull(sum(v2t.debet), 0) as debet,
                        isnull(sum(v2t.kredit), 0) as kredit,
                        v2t.jenis,
                        cast(null as varchar(10)) as unit
                    from
                    (
                        select supplier, total as debet, 0 as kredit, case when jenis_hutang in ('OVK ORP', 'OVK NON ORP') then 'OVK' else jenis_hutang end as jenis
                        from #v2_sumber_hutang
                        where tanggal between '".$start_date."' and '".$end_date."'

                        union all

                        select supplier, 0 as debet, total as kredit, case when jenis_hutang in ('OVK ORP', 'OVK NON ORP') then 'OVK' else jenis_hutang end as jenis
                        from #v2_pembayaran
                        where tanggal between '".$start_date."' and '".$end_date."'
                    ) v2t
                    group by
                        v2t.supplier,
                        v2t.jenis
                    /* END - TRANSAKSI BULAN BERJALAN */
                ) _data
                group by
                    _data.supplier,
                    _data.jenis,
                    _data.unit
            ) data
            left join
                (
                    select p1.nomor, p1.nama, p1.tipe from pelanggan p1
                    right join
                        (select max(id) as id, nomor from pelanggan p where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) p2
                        on
                            p1.id = p2.id

                    union all
                            
                    select e1.nomor, e1.nama, 'ekspedisi' as tipe from ekspedisi e1
                    right join
                        (select max(id) as id, nomor from ekspedisi e group by nomor) e2
                        on
                            e1.id = e2.id

                    union all

                    select m1.nomor, m1.nama, 'plasma' as tipe from mitra m1
                    right join
                        (select max(id) as id, nomor from mitra group by nomor) m2
                        on
                            m1.id = m2.id
                ) supl
                on
                    supl.nomor = data.supplier
            ".$where."
            group by
                data.jenis,
                -- data.unit,
                data.supplier,
                supl.nama
            order by
                supl.nama asc,
                data.jenis asc
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }
}
