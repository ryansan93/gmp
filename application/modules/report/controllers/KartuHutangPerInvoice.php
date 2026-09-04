<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class KartuHutangPerInvoice extends Public_Controller {

    private $pathView = 'report/kartu_hutang_per_invoice/';
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
                "assets/report/kartu_hutang_per_invoice/js/kartu-hutang-per-invoice.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/kartu_hutang_per_invoice/css/kartu-hutang-per-invoice.css",
            ));

            $data = $this->includes;

            $m_wilayah = new \Model\Storage\Wilayah_model();

            $content['akses'] = $akses;
            $content['supplier'] = $this->getSupplier();
            $content['jenis'] = $this->getJenis();
            $content['unit'] = $m_wilayah->getDataUnit();
            $content['jenis_hutang'] = $this->getJenisHutang();
            $content['title_menu'] = 'Laporan Kartu Hutang Per Invoice';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getJenis() {
        $arr = array('ekspedisi', 'plasma', 'supplier');

        return $arr;
    }

    public function getJenisHutang() {
        return array(
            array('value' => 'DOC',        'label' => 'DOC'),
            array('value' => 'PAKAN',      'label' => 'PAKAN'),
            array('value' => 'OA PAKAN',   'label' => 'OA PAKAN'),
            array('value' => 'OVK ORP',    'label' => 'OVK ORP'),
            array('value' => 'OVK NON ORP','label' => 'OVK NON ORP'),
            array('value' => 'PLASMA',     'label' => 'PLASMA'),
            array('value' => 'PERALATAN',  'label' => 'PERALATAN'),
            array('value' => 'CN',         'label' => 'CN'),
            array('value' => 'DN',         'label' => 'DN'),
            array('value' => 'MEMO',       'label' => 'MEMO'),
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
        $unit = $params['unit'];
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

        $where = null;
        if ( $jenis != 'all' ) {
            if ( empty( $where ) ) {
                $where = "where supl.tipe = '".$jenis."'";
            } else {
                $where .= "and supl.tipe = '".$jenis."'";
            }
        }

        if ( $supplier != 'all' ) {
            if ( empty( $where ) ) {
                $where = "where supl.nomor = '".$supplier."'";
            } else {
                $where .= "and supl.nomor = '".$supplier."'";
            }
        }

        if ( $unit != 'all' ) {
            if ( empty( $where ) ) {
                $where = "where data.unit = '".$unit."'";
            } else {
                $where .= "and data.unit = '".$unit."'";
            }
        }

        if ( !empty($jenis_hutang) ) {
            if ( empty( $where ) ) {
                $where = "where data.jenis_hutang in ('".implode("', '", $jenis_hutang)."')";
            } else {
                $where .= "and data.jenis_hutang in ('".implode("', '", $jenis_hutang)."')";
            }
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            SET NOCOUNT ON;
            /* NOCOUNT wajib di batch multi-statement begini -- tanpa ini, PDO driver sqlsrv (dipakai
               hydrateRaw()->select()->fetchAll()) error IMSSP: active result contains no fields,
               krn fetchAll() ngambil resultset dari statement DDL/SELECT INTO duluan (bukan SELECT
               akhir). NOCOUNT menekan pesan N-rows-affected perantara itu shg driver lompat ke
               resultset SELECT terakhir yg sungguhan. JANGAN hapus, dan JANGAN pecah jadi >1
               panggilan hydrateRaw/statement terpisah -- #temp table TIDAK persist lintas panggilan
               PDO prepare() terpisah di driver ini (sudah dicoba & gagal, root cause: sp_prepare/
               sp_execute RPC scoping), jadi HARUS tetap 1 batch besar spt ini. */

            /* #konfir_helper di-materialize jadi #temp table (bukan CTE) -- dipakai berkali-kali
               (~12x) di query utama, dan SQL Server TIDAK mematerialize CTE (re-evaluate penuh
               tiap reference). Sebelumnya bikin report lemot. */
            IF OBJECT_ID('tempdb..#konfir_helper') IS NOT NULL BEGIN DROP TABLE #konfir_helper END
            select kpd.nomor, max(kpdd.kode_unit) as unit
            into #konfir_helper
            from konfirmasi_pembayaran_doc kpd
            left join konfirmasi_pembayaran_doc_det kpdd on kpdd.id_header = kpd.id
            group by kpd.nomor

            union all

            select kpp.nomor, max(kppd.kode_unit) as unit from konfirmasi_pembayaran_pakan kpp
            left join konfirmasi_pembayaran_pakan_det kppd on kppd.id_header = kpp.id
            group by kpp.nomor

            union all

            select kpv.nomor, max(kpvd.kode_unit) as unit from konfirmasi_pembayaran_voadip kpv
            left join konfirmasi_pembayaran_voadip_det kpvd on kpvd.id_header = kpv.id
            group by kpv.nomor

            union all

            select kpp.nomor, max(SUBSTRING(REPLACE(REPLACE(rhpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3)) as unit
            from konfirmasi_pembayaran_peternak kpp
            left join konfirmasi_pembayaran_peternak_det kppd on kppd.id_header = kpp.id
            left join (
                select r.id, r.invoice, 'RHPP' as jenis_rhpp from rhpp r where r.jenis = 'rhpp_plasma'
                union all
                select rg.id, rg.invoice, 'RHPP GROUP' as jenis_rhpp from rhpp_group rg where rg.jenis = 'rhpp_plasma'
            ) rhpp on kppd.id_trans = rhpp.id and kppd.jenis = rhpp.jenis_rhpp
            group by kpp.nomor

            union all

            select kpop.nomor, max(SUBSTRING(REPLACE(REPLACE(kp.no_order,'OPK/',''),'OP/',''),1,3)) as unit
            from konfirmasi_pembayaran_oa_pakan kpop
            left join konfirmasi_pembayaran_oa_pakan_det kpopd on kpopd.id_header = kpop.id
            left join kirim_pakan kp on kp.no_sj = kpopd.no_sj
            group by kpop.nomor
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE UNIQUE CLUSTERED INDEX ix_#konfir_helper_nomor ON #konfir_helper(nomor)

            /* ============================================================================
               V2-PORTED: DOC + PAKAN + OVK debt/payment source logic, copied verbatim
               (renamed with v2_ prefix to avoid collision with legacy's own #konfir_helper)
               from KartuHutangPerInvoiceV2.php getData(), 2026-08-28/29 design. See that
               file's header comment for full rationale of every branch below.
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

            /* OA PAKAN (3-cabang union dibungkus 1 lapis select*from(...)oa) ternyata jadi
               titik lemot tersendiri -- masing2 dari 3 cabangnya cepat sendiri2 (<1 detik total
               digabung), tapi begitu dibungkus 1 lapis select*from(union) di dalam statement
               SALDO AWAL/TRANSAKSI yg lebih besar, legacy CE di LIVE salah estimasi drastis
               (30-38 detik). Materialize terpisah spt pola lain di atas. */
            IF OBJECT_ID('tempdb..#oa_saldo') IS NOT NULL BEGIN DROP TABLE #oa_saldo END
            select nomor, supplier, total, unit, jenis_hutang
            into #oa_saldo
            from (
                select kpop.nomor, kpop.ekspedisi_id as supplier, (kpop.total+kpop.potongan_pph_23) as total, konfir.unit, 'OA PAKAN' as jenis_hutang from konfirmasi_pembayaran_oa_pakan kpop
                left join
                    #konfir_helper konfir
                    on
                        konfir.nomor = kpop.nomor
                where
                    kpop.tgl_bayar < '".$start_date."'

                union all

                select tp.no_bbm as nomor, kp.ekspedisi_id as supplier, sum(dtp.jumlah)*kp.ongkos_angkut as total,
                    SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 ) as unit, 'OA PAKAN' as jenis_hutang
                from det_terima_pakan dtp
                left join
                    terima_pakan tp
                    on
                        dtp.id_header = tp.id
                left join
                    kirim_pakan kp
                    on
                        tp.id_kirim_pakan = kp.id
                left hash join
                    (select distinct no_sj from konfirmasi_pembayaran_oa_pakan_det) kpopd_ex
                    on
                        kpopd_ex.no_sj = kp.no_sj
                where
                    tp.tgl_terima < '".$start_date."' and
                    kp.jenis_kirim = 'opkg' and
                    kpopd_ex.no_sj is null
                group by
                    tp.no_bbm, kp.ekspedisi_id, kp.ongkos_angkut, kp.no_order

                union all

                select opp.no_sj as nomor, krm.ekspedisi_id as supplier, opp.ongkos_angkut as total,
                    SUBSTRING( REPLACE(REPLACE(krm.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 ) as unit, 'OA PAKAN' as jenis_hutang
                from oa_pindah_pakan opp
                left join
                    (
                        select kp.no_sj, tp.no_bbm as kode_trans, kp.ekspedisi_id, tp.tgl_terima as tanggal, kp.no_order from kirim_pakan kp
                        left join
                            terima_pakan tp
                            on
                                kp.id = tp.id_kirim_pakan
                        group by
                            kp.no_sj, tp.no_bbm, kp.ekspedisi_id, tp.tgl_terima, kp.no_order

                        union all

                        select no_retur as no_sj, no_retur as kode_trans, ekspedisi_id, tgl_retur as tanggal, no_order from retur_pakan rp
                    ) krm
                    on
                        opp.no_sj = krm.no_sj
                left hash join
                    (select distinct no_sj from konfirmasi_pembayaran_oa_pakan_det) kpopd_ex2
                    on
                        kpopd_ex2.no_sj = opp.no_sj
                where
                    krm.tanggal < '".$start_date."' and
                    kpopd_ex2.no_sj is null
            ) oa_raw
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#oa_trans') IS NOT NULL BEGIN DROP TABLE #oa_trans END
            select tanggal, nomor, supplier, total, kode_trans, unit, jenis_hutang
            into #oa_trans
            from (
                select kpop.tgl_bayar as tanggal, kpop.nomor, kpop.ekspedisi_id as supplier, kpopd.total, tp.no_bbm as kode_trans,
                    SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 ) as unit, 'OA PAKAN' as jenis_hutang
                from konfirmasi_pembayaran_oa_pakan_det kpopd
                left join
                    konfirmasi_pembayaran_oa_pakan kpop
                    on
                        kpopd.id_header = kpop.id
                left join
                    kirim_pakan kp
                    on
                        kpopd.no_sj = kp.no_sj
                left join
                    terima_pakan tp
                    on
                        kp.id = tp.id_kirim_pakan
                where
                    kpop.tgl_bayar between '".$start_date."' and '".$end_date."'

                union all

                select tp.tgl_terima as tanggal, tp.no_bbm as nomor, kp.ekspedisi_id as supplier, sum(dtp.jumlah)*kp.ongkos_angkut as total, tp.no_bbm as kode_trans,
                    SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 ) as unit, 'OA PAKAN' as jenis_hutang
                from det_terima_pakan dtp
                left join
                    terima_pakan tp
                    on
                        dtp.id_header = tp.id
                left join
                    kirim_pakan kp
                    on
                        tp.id_kirim_pakan = kp.id
                left hash join
                    (select distinct no_sj from konfirmasi_pembayaran_oa_pakan_det) kpopd_ex
                    on
                        kpopd_ex.no_sj = kp.no_sj
                where
                    tp.tgl_terima between '".$start_date."' and '".$end_date."' and
                    kp.jenis_kirim = 'opkg' and
                    kpopd_ex.no_sj is null
                group by
                    tp.tgl_terima, tp.no_bbm, kp.ekspedisi_id, kp.ongkos_angkut, kp.no_order

                union all

                select krm.tanggal, opp.no_sj as nomor, krm.ekspedisi_id as supplier, opp.ongkos_angkut as total, krm.kode_trans,
                    SUBSTRING( REPLACE(REPLACE(krm.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 ) as unit, 'OA PAKAN' as jenis_hutang
                from oa_pindah_pakan opp
                left join
                    (
                        select kp.no_sj, tp.no_bbm as kode_trans, kp.ekspedisi_id, tp.tgl_terima as tanggal, kp.no_order from kirim_pakan kp
                        left join
                            terima_pakan tp
                            on
                                kp.id = tp.id_kirim_pakan
                        group by
                            kp.no_sj, tp.no_bbm, kp.ekspedisi_id, tp.tgl_terima, kp.no_order

                        union all

                        select no_retur as no_sj, no_retur as kode_trans, ekspedisi_id, tgl_retur as tanggal, no_order from retur_pakan rp
                    ) krm
                    on
                        opp.no_sj = krm.no_sj
                left hash join
                    (select distinct no_sj from konfirmasi_pembayaran_oa_pakan_det) kpopd_ex2
                    on
                        kpopd_ex2.no_sj = opp.no_sj
                where
                    krm.tanggal between '".$start_date."' and '".$end_date."' and
                    kpopd_ex2.no_sj is null
            ) oa_raw
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            /* SALDO AWAL: #saldo_inv & #saldo_byr di-materialize terpisah (bukan inline subquery)
               -- LIVE server masih SQL Server 2012 (legacy cardinality estimator, QUERYTRACEON 2312
               tidak berlaku di situ), dan gabungan union 10-cabang + union 7-cabang dalam 1 statement
               bikin optimizer salah estimasi cost total (lemot parah, teruji >3 menit di LIVE).
               Materialize dulu masing2 spt pola #pph_helper/#pembulatan_helper sebelumnya. */
            IF OBJECT_ID('tempdb..#saldo_inv') IS NOT NULL BEGIN DROP TABLE #saldo_inv END
            select supplier, nomor, sum(total) as total, max(unit) as unit, max(jenis_hutang) as jenis_hutang
            into #saldo_inv
            from (
                    /* V2-PORTED: DOC + PAKAN + OVK debt sources (Konfirmasi + Memorial + DN + Koreksi Tambahan) -- lihat #v2_sumber_hutang di preamble */
                    select nomor, supplier, total, unit, jenis_hutang
                    from #v2_sumber_hutang
                    where tanggal < '".$start_date."'
                    /* END - V2-PORTED DOC/PAKAN/OVK */

                    union all

                    /* OA PAKAN -- lihat #oa_saldo di preamble (dimaterialize terpisah, jgn inline lagi) */
                    select * from #oa_saldo oa
                    /* END - OA PAKAN */

                    union all

                    select kpp.nomor, kpp.mitra as supplier, kpp.total, konfir.unit, 'PLASMA' as jenis_hutang from konfirmasi_pembayaran_peternak kpp
                    left join
                        #konfir_helper konfir
                        on
                            konfir.nomor = kpp.nomor
                    where
                        kpp.tgl_bayar < '".$start_date."'
    
                    union all
    
                    select op.no_order as nomor, op.supplier, op.total, op.unit, 'PERALATAN' as jenis_hutang from order_peralatan op
                    where
                        op.tgl_order < '".$start_date."'

                    union all

                    select
                        c.nomor,
                        case
                            when (c.supplier is not null and c.supplier <> '') then
                                c.supplier
                            when (c.mitra is not null and c.mitra <> '') then
                                c.mitra
                        end as supplier,
                        0 - ((c.tot_cn - isnull(rpc.pakai, 0))) as total,
                        cast(null as varchar(50)) as unit,
                        'CN' as jenis_hutang
                    from cn c
                    left join
                        (
                            select
                                sum(isnull(pakai, 0)) as pakai, id_cn
                            from
                            (
                                select sum(rpc.pakai) as pakai, rpc.id_cn from realisasi_pembayaran_cn rpc
                                left join
                                    realisasi_pembayaran rp
                                    on
                                        rpc.id_header = rp.id
                                where
                                    rp.tgl_bayar <= '".$end_date."'
                                group by 
                                    rpc.id_cn

                                union all

                                select sum(bpc.pakai) as pakai, bpc.id_cn from bayar_peralatan_cn bpc
                                left join
                                    bayar_peralatan bp
                                    on
                                        bpc.id_header = bp.id
                                where
                                    bp.tgl_bayar <= '".$end_date."'
                                group by 
                                    bpc.id_cn

                                union all

                                select sum(ppc.pakai) as pakai, ppc.id_cn from pembayaran_pelanggan_cn ppc
                                left join
                                    pembayaran_pelanggan pp
                                    on
                                        ppc.id_header = pp.id
                                where
                                    pp.tgl_bayar <= '".$end_date."'
                                group by 
                                    ppc.id_cn
                            ) rpc
                            group by
                                rpc.id_cn
                        ) rpc
                        on
                            c.id = rpc.id_cn
                    where 
                        c.tanggal < '".$start_date."' and
                        c.tot_cn > isnull(rpc.pakai, 0) and
                        ((c.supplier is not null and c.supplier <> '') or (c.mitra is not null and c.mitra <> ''))

                    union all

                    select
                        d.nomor,
                        case
                            when (d.supplier is not null and d.supplier <> '') then
                                d.supplier
                            when (d.mitra is not null and d.mitra <> '') then
                                d.mitra
                        end as supplier,
                        (d.tot_dn - isnull(rpd.pakai, 0)) as total,
                        cast(null as varchar(50)) as unit,
                        'DN' as jenis_hutang
                    from dn d
                    left join
                        (
                            select
                                sum(isnull(pakai, 0)) as pakai, id_dn
                            from
                            (
                                select sum(rpd.pakai) as pakai, rpd.id_dn from realisasi_pembayaran_dn rpd
                                left join
                                    realisasi_pembayaran rp
                                    on
                                        rpd.id_header = rp.id
                                where
                                    rp.tgl_bayar <= '".$end_date."'
                                group by 
                                    rpd.id_dn

                                union all

                                select sum(bpd.pakai) as pakai, bpd.id_dn from bayar_peralatan_dn bpd
                                left join
                                    bayar_peralatan bp
                                    on
                                        bpd.id_header = bp.id
                                where
                                    bp.tgl_bayar <= '".$end_date."'
                                group by 
                                    bpd.id_dn

                                union all

                                select sum(ppd.pakai) as pakai, ppd.id_dn from pembayaran_pelanggan_dn ppd
                                left join
                                    pembayaran_pelanggan pp
                                    on
                                        ppd.id_header = pp.id
                                where
                                    pp.tgl_bayar <= '".$end_date."'
                                group by 
                                    ppd.id_dn
                            ) rpd
                            group by
                                rpd.id_dn
                        ) rpd
                        on
                            d.id = rpd.id_dn
                    where 
                        d.tanggal < '".$start_date."' and
                        d.tot_dn > isnull(rpd.pakai, 0) and
                        ((d.supplier is not null and d.supplier <> '') or (d.mitra is not null and d.mitra <> ''))

                    union all

                    /* INVOICE LEWAT MEMO utk DOC/OVK DIHAPUS -- sudah 100% tercakup di V2-PORTED block
                       di atas (#v2_sumber_hutang, cabang 'Memorial'/'Koreksi Tambahan Hutang OVK'/'DN').
                       WHERE clause branch ini secara empiris SELALU membatasi coa_asal ke salah satu dari
                       {21180.200, 21180.300, 21174.000} shg cabang 'else MEMO' di CASE-nya TIDAK PERNAH
                       benar2 tereksekusi -- branch ini 100% DOC/OVK, tidak ada generic MEMO yg hilang. */

                    /* BAYAR TANPA INVOICE (no_bayar tidak merujuk invoice/memo manapun -- realisasi dobel-cek */
                    select
                        rpd.no_bayar as nomor,
                        case
                            when rp.supplier is not null and rp.supplier <> '' then rp.supplier
                            when rp.peternak is not null and rp.peternak <> '' then rp.peternak
                            when rp.ekspedisi is not null and rp.ekspedisi <> '' then rp.ekspedisi
                        end as supplier,
                        0 as total,
                        cast(null as varchar(50)) as unit,
                        (case when rpd.transaksi = 'VOADIP' then (case when (case when rp.supplier is not null and rp.supplier <> '' then rp.supplier when rp.peternak is not null and rp.peternak <> '' then rp.peternak when rp.ekspedisi is not null and rp.ekspedisi <> '' then rp.ekspedisi end) = '19B004' then 'OVK ORP' else 'OVK NON ORP' end) else isnull(rpd.transaksi, 'DOC') end) as jenis_hutang
                    from realisasi_pembayaran_det rpd
                    left join
                        realisasi_pembayaran rp
                        on
                            rpd.id_header = rp.id
                    left hash join
                        #konfir_helper kh
                        on
                            kh.nomor = rpd.no_bayar
                    left hash join
                        order_peralatan op
                        on
                            op.no_order = rpd.no_bayar
                    left hash join
                        cn c
                        on
                            c.nomor = rpd.no_bayar
                    left hash join
                        dn d
                        on
                            d.nomor = rpd.no_bayar
                    left hash join
                        (
                            select distinct mi.no_mm from mmitem mi
                            where
                                (
                                    mi.coa_asal in ('21180.300', '21174.000') or
                                    (mi.coa_asal = '21180.200' and mi.coa_tujuan <> '96010.000')
                                ) and
                                cast(mi.tgl_mm as date) < '".$start_date."'
                        ) mi2
                        on
                            mi2.no_mm = rpd.no_bayar
                    where
                        rp.tgl_bayar < '".$start_date."' and
                        kh.nomor is null and
                        op.no_order is null and
                        c.nomor is null and
                        d.nomor is null and
                        mi2.no_mm is null
                    /* END - BAYAR TANPA INVOICE */
            ) inv_raw
            group by supplier, nomor
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE UNIQUE CLUSTERED INDEX ix_saldo_inv ON #saldo_inv(supplier, nomor)

            IF OBJECT_ID('tempdb..#saldo_byr') IS NOT NULL BEGIN DROP TABLE #saldo_byr END
            select
                            byr.nomor,
                            sum(byr.cn) as cn,
                            sum(byr.dn) as dn,
                            sum(byr.potongan) as potongan,
                            sum(byr.uang_muka) as uang_muka,
                            sum(byr.transfer) as transfer,
                            sum(byr.saldo) as saldo,
                            sum(byr.pph) as pph,
                            sum(byr.pembulatan) as pembulatan
            into #saldo_byr
            from
            (
                            select
                                rpd.no_bayar as nomor,
                                sum(rpdcd.nominal) as cn,
                                0 as dn,
                                0 as potongan,
                                0 as uang_muka,
                                0 as transfer,
                                0 as saldo,
                                0 as pph,
                                0 as pembulatan
                            from realisasi_pembayaran_det_cn_dn rpdcd
                            left join
                                realisasi_pembayaran_det rpd
                                on
                                    rpdcd.id_header = rpd.id
                            left join
                                realisasi_pembayaran rp
                                on
                                    rpd.id_header = rp.id
                            left join
                                (
                                    select nomor, tanggal from cn c
                                    union all
                                    select nomor, tanggal from dn d
                                ) cn_dn
                                on
                                    rpdcd.nomor_cn_dn = cn_dn.nomor
                            where
                                rpdcd.nomor_cn_dn like '%CN%' and
                                rp.tgl_bayar < '".$start_date."' and
                                cn_dn.tanggal < '".$start_date."'
                            group by
                                rpd.no_bayar

                            union all

                            /* CN via cn_post_det DIHAPUS -- jenis_cn di seluruh data cuma 'DOC'/'PKN', jadi
                               branch ini 100% DOC+PAKAN, sudah tercakup di #v2_pembayaran (V2-PORTED block,
                               cabang 'CN' utk DOC & PAKAN, jenis_cn='DOC'/'PKN' masing2). */

                            select
                                rpd.no_bayar as nomor,
                                0 as cn,
                                sum(rpdcd.nominal) as dn,
                                0 as potongan,
                                0 as uang_muka,
                                0 as transfer,
                                0 as saldo,
                                0 as pph,
                                0 as pembulatan
                            from realisasi_pembayaran_det_cn_dn rpdcd
                            left join
                                realisasi_pembayaran_det rpd
                                on
                                    rpdcd.id_header = rpd.id
                            left join
                                realisasi_pembayaran rp
                                on
                                    rpd.id_header = rp.id
                            left join
                                (
                                    select nomor, tanggal from cn c
                                    union all
                                    select nomor, tanggal from dn d
                                ) cn_dn
                                on
                                    rpdcd.nomor_cn_dn = cn_dn.nomor
                            where
                                rpdcd.nomor_cn_dn like '%DN%' and
                                rp.tgl_bayar < '".$start_date."' and
                                cn_dn.tanggal < '".$start_date."'
                            group by
                                rpd.no_bayar
                            
                            union all
    
                            select
                                rpd.no_bayar as nomor,
                                0 as cn,
                                0 as dn,
                                sum(rpd.potongan) as potongan,
                                sum(rpd.uang_muka) as uang_muka,
                                sum(rpd.transfer+isnull(kpop.potongan_pph_23, 0)) as transfer,
                                0 as saldo,
                                0 as pph,
                                0 as pembulatan
                            from realisasi_pembayaran_det rpd
                            left join
                                konfirmasi_pembayaran_oa_pakan kpop
                                on
                                    rpd.no_bayar = kpop.nomor
                            left join
                                realisasi_pembayaran rp
                                on
                                    rpd.id_header = rp.id
                            where
                                rp.tgl_bayar < '".$start_date."' and
                                /* DOC/PAKAN/VOADIP dikecualikan -- sudah tercakup lengkap (transfer+potongan+
                                   uang_muka+PPh+pembulatan) di #v2_pembayaran (V2-PORTED block). Branch generik
                                   ini tetap jalan utk PLASMA/OA PAKAN & transaksi lain yg tidak difilter di sini. */
                                rpd.transaksi not in ('DOC', 'PAKAN', 'VOADIP')
                            group by
                                rpd.no_bayar

                            union all

                            /* V2-PORTED: DOC + PAKAN + OVK payment sources (Realisasi Pembayaran + CN + PPh +
                               Pembulatan + Pelunasan Memorial) -- lihat #v2_pembayaran di preamble. Ditaruh
                               di kolom transfer (netral, semua kolom byr di-SUM jadi 1 angka di #saldo_byr). */
                            select
                                nomor,
                                0 as cn,
                                0 as dn,
                                0 as potongan,
                                0 as uang_muka,
                                sum(total) as transfer,
                                0 as saldo,
                                0 as pph,
                                0 as pembulatan
                            from #v2_pembayaran
                            where tanggal < '".$start_date."'
                            group by nomor
                            /* END - V2-PORTED DOC/PAKAN/OVK payment */

                            union all

                            select
                                bp.no_order as nomor,
                                sum(bpc.pakai) as cn,
                                0 as dn,
                                0 as potongan,
                                0 as uang_muka,
                                0 as transfer,
                                0 as saldo,
                                0 as pph,
                                0 as pembulatan
                            from bayar_peralatan_cn bpc
                            left join
                                bayar_peralatan bp
                                on
                                    bpc.id_header = bp.id
                            left join
                                cn c
                                on
                                    bpc.id_cn = c.id
                            where
                                bp.tgl_bayar < '".$start_date."' and
                                c.tanggal < '".$start_date."'
                            group by
                                bp.no_order
    
                            union all
    
                            select
                                bp.no_order as nomor,
                                0 as cn,
                                sum(bpd.pakai) as dn,
                                0 as potongan,
                                0 as uang_muka,
                                0 as transfer,
                                0 as saldo,
                                0 as pph,
                                0 as pembulatan
                            from bayar_peralatan_dn bpd
                            left join
                                bayar_peralatan bp
                                on
                                    bpd.id_header = bp.id
                            left join
                                dn d
                                on
                                    bpd.id_dn = d.id
                            where
                                bp.tgl_bayar < '".$start_date."' and
                                d.tanggal < '".$start_date."'
                            group by
                                bp.no_order
    
                            union all
    
                            select
                                bp.no_order as nomor,
                                0 as cn,
                                0 as dn,
                                0 as potongan,
                                0 as uang_muka,
                                sum(bp.jml_bayar) as transfer,
                                sum(bp.saldo) as saldo,
                                0 as pph,
                                0 as pembulatan
                            from bayar_peralatan bp
                            where
                                bp.tgl_bayar < '".$start_date."'
                            group by
                                bp.no_order

                            /* BAYAR LEWAT MEMO utk DOC/OVK DIHAPUS -- 100% DOC/OVK (coa_tujuan selalu salah
                               satu dari 3 akun hutang tsb), sudah tercakup di #v2_pembayaran (V2-PORTED
                               block, cabang 'Pembayaran Memorial'/'Pelunasan Memorial (...)'/'Pembalik
                               Memorial'). PAKAN-nya sendiri (coa_tujuan=21180.100) TIDAK PERNAH ditangani
                               branch lama ini -- sekarang baru ditambahkan lewat #v2_pembayaran juga. */
                        ) byr
                        group by
                            byr.nomor
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE UNIQUE CLUSTERED INDEX ix_saldo_byr ON #saldo_byr(nomor)

            /* PPh & Pembulatan dari det_jurnal (docref_helper/pph_helper/pembulatan_helper) DIHAPUS
               2026-08-27 atas permintaan user -- itu sumber utama lemot (LIKE-join/STRING_SPLIT ke
               kolom TEXT det_jurnal.keterangan). Konsekuensi diterima: PPh/Pembulatan utk sementara
               tidak tampil sbg baris kredit, invoice terkait akan tampak residual. Kalau nanti mau
               dikembalikan, sumbernya harus dari konfirmasi_pembayaran/memorial, BUKAN det_jurnal
               (rpd.pph di realisasi_pembayaran_det selalu NULL utk DOC, jadi belum ada gantinya). */

            IF OBJECT_ID('tempdb..#data_saldo') IS NOT NULL BEGIN DROP TABLE #data_saldo END
            select
                    '".$start_date."' as tanggal,
                    inv.supplier,
                    'Saldo Awal' as jenis_trans,
                    inv.nomor as no_inv,
                    inv.nomor as kode_trans,
                    0 as debet,
                    0 as kredit,
                    sum( (inv.total+(isnull(byr.dn, 0))) - (isnull(byr.cn, 0)+isnull(byr.potongan, 0)+isnull(byr.uang_muka, 0)+isnull(byr.transfer, 0)+isnull(byr.saldo, 0)+isnull(byr.pph, 0)+isnull(byr.pembulatan, 0)) ) as saldo,
                    1 as urut,
                    max(inv.unit) as unit,
                    max(inv.jenis_hutang) as jenis_hutang
            into #data_saldo
                from #saldo_inv inv
                left join
                    #saldo_byr byr
                    on
                        inv.nomor = byr.nomor
                group by
                    inv.supplier,
                    inv.nomor
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            /* #tr_doc/#tr_pakan/#tr_voadip (versi lama) DIHAPUS -- diganti #tr_v2docpakanovk di bawah,
               sumbernya #v2_sumber_hutang (V2-PORTED block). kode_trans di sini SENGAJA diisi V2's
               jenis_trans (bkn SJ/no_order spt versi lama) krn #data_trans final select memakai
               inv.kode_trans as jenis_trans utk kolom tampilan Jenis Transaksi -- jadi baris DOC/
               PAKAN/OVK kini menampilkan label deskriptif V2 (Konfirmasi Pembayaran DOC, Memorial,
               DN, dst) drpd nomor SJ mentah spt sebelumnya. */
            IF OBJECT_ID('tempdb..#tr_v2docpakanovk') IS NOT NULL BEGIN DROP TABLE #tr_v2docpakanovk END
            select tanggal, nomor, supplier, total, jenis_trans as kode_trans, unit, jenis_hutang
            into #tr_v2docpakanovk
            from #v2_sumber_hutang
            where tanggal between '".$start_date."' and '".$end_date."'
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#tr_plasma') IS NOT NULL BEGIN DROP TABLE #tr_plasma END
            select tanggal, nomor, supplier, total, kode_trans, unit, jenis_hutang
            into #tr_plasma
            from (
                    select kpp.tgl_bayar as tanggal, kpp.nomor, kpp.mitra as supplier, kppd.sub_total as total, rhpp.invoice as kode_trans,
                        SUBSTRING(REPLACE(REPLACE(rhpp.invoice, 'INV/RHPP/G/', ''), 'INV/RHPP/', ''), 1, 3) as unit, 'PLASMA' as jenis_hutang
                    from konfirmasi_pembayaran_peternak_det kppd
                    left join
                        konfirmasi_pembayaran_peternak kpp
                        on
                            kppd.id_header = kpp.id
                    left join
                        (
                            select r.id, r.invoice, 'RHPP' as jenis_rhpp from rhpp r where r.jenis = 'rhpp_plasma' and not exists(select * from rhpp_group_noreg rgn where rgn.noreg = r.noreg)

                            union all

                            select rg.id, rg.invoice, 'RHPP GROUP' as jenis_rhpp from rhpp_group rg where rg.jenis = 'rhpp_plasma'
                        ) rhpp
                        on
                            kppd.id_trans = rhpp.id and
                            kppd.jenis = rhpp.jenis_rhpp
                    where
                        kpp.tgl_bayar between '".$start_date."' and '".$end_date."'
            ) x
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#tr_peral1') IS NOT NULL BEGIN DROP TABLE #tr_peral1 END
            select tanggal, nomor, supplier, total, kode_trans, unit, jenis_hutang
            into #tr_peral1
            from (
                    select op.tgl_order as tanggal, op.no_order as nomor, op.supplier, op.total, op.no_order as kode_trans, op.unit as unit, 'PERALATAN' as jenis_hutang from order_peralatan op
                    where
                        op.tgl_order between '".$start_date."' and '".$end_date."'
            ) x
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#tr_cndn') IS NOT NULL BEGIN DROP TABLE #tr_cndn END
            select tanggal, nomor, supplier, total, kode_trans, unit, jenis_hutang
            into #tr_cndn
            from (
                    select 
                        rp.tgl_bayar as tanggal,
                        rpd.no_bayar as nomor,
                        case
                            when rp.supplier is not null and rp.supplier <> '' then
                                rp.supplier
                            when rp.peternak is not null and rp.peternak <> '' then
                                rp.peternak
                            when rp.ekspedisi is not null and rp.ekspedisi <> '' then
                                rp.ekspedisi
                        end as supplier,
                        sum(rpdcd.nominal) as total,
                        rpdcd.nomor_cn_dn as kode_trans,
                        cast(null as varchar(50)) as unit,
                        (case when max(rpd.transaksi) = 'VOADIP' then (case when max(case when rp.supplier is not null and rp.supplier <> '' then rp.supplier when rp.peternak is not null and rp.peternak <> '' then rp.peternak when rp.ekspedisi is not null and rp.ekspedisi <> '' then rp.ekspedisi end) = '19B004' then 'OVK ORP' else 'OVK NON ORP' end) else max(rpd.transaksi) end) as jenis_hutang
                    from realisasi_pembayaran_det_cn_dn rpdcd
                    left join
                        realisasi_pembayaran_det rpd
                        on
                            rpdcd.id_header = rpd.id
                    left join
                        realisasi_pembayaran rp
                        on
                            rpd.id_header = rp.id
                    where
                        rpdcd.nomor_cn_dn like '%DN%' and
                        rp.tgl_bayar between '".$start_date."' and '".$end_date."'
                    group by
                        rp.tgl_bayar,
                        rp.supplier,
                        rp.peternak,
                        rp.ekspedisi,
                        rpd.no_bayar,
                        rpdcd.nomor_cn_dn
            ) x
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#tr_peral2') IS NOT NULL BEGIN DROP TABLE #tr_peral2 END
            select tanggal, nomor, supplier, total, kode_trans, unit, jenis_hutang
            into #tr_peral2
            from (
                    select bp.tgl_bayar as tanggal, bp.no_order as nomor, op.supplier, sum(bpd.pakai) as total, d.nomor as kode_trans, op.unit as unit, 'PERALATAN' as jenis_hutang from bayar_peralatan_dn bpd
                    left join
                        bayar_peralatan bp
                        on
                            bpd.id_header = bp.id
                    left join
                        order_peralatan op
                        on
                            op.no_order = bp.no_order
                    left join
                        dn d
                        on
                            bpd.id_dn = d.id
                    where
                        d.tanggal between '".$start_date."' and '".$end_date."'
                    group by
                        bp.tgl_bayar,
                        op.supplier,
                        bp.no_order,
                        d.nomor,
                        op.unit
            ) x
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#tr_dn') IS NOT NULL BEGIN DROP TABLE #tr_dn END
            select tanggal, nomor, supplier, total, kode_trans, unit, jenis_hutang
            into #tr_dn
            from (
                    select
                        d.tanggal,
                        d.nomor,
                        case
                            when (d.supplier is not null and d.supplier <> '') then
                                d.supplier
                            when (d.mitra is not null and d.mitra <> '') then
                                d.mitra
                        end as supplier,
                        (d.tot_dn - isnull(rpd.pakai, 0)) as total,
                        d.nomor as kode_trans,
                        cast(null as varchar(50)) as unit,
                        'DN' as jenis_hutang
                    from dn d
                    left join
                        (
                            select
                                sum(isnull(pakai, 0)) as pakai, id_dn
                            from
                            (
                                select sum(rpd.pakai) as pakai, rpd.id_dn from realisasi_pembayaran_dn rpd
                                left join
                                    realisasi_pembayaran rp
                                    on
                                        rpd.id_header = rp.id
                                where
                                    rp.tgl_bayar <= '".$end_date."'
                                group by 
                                    rpd.id_dn

                                union all

                                select sum(bpd.pakai) as pakai, bpd.id_dn from bayar_peralatan_dn bpd
                                left join
                                    bayar_peralatan bp
                                    on
                                        bpd.id_header = bp.id
                                where
                                    bp.tgl_bayar <= '".$end_date."'
                                group by 
                                    bpd.id_dn
                            ) rpd
                            group by
                                rpd.id_dn
                        ) rpd
                        on
                            d.id = rpd.id_dn
                    where 
                        d.tanggal between '".$start_date."' and '".$end_date."' and
                        d.tot_dn > isnull(rpd.pakai, 0) and
                        ((d.supplier is not null and d.supplier <> '') or (d.mitra is not null and d.mitra <> ''))
            ) x
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            /* #tr_memo (DOC/OVK invoice-via-memo, versi lama) DIHAPUS -- 100% DOC/OVK (WHERE-nya selalu
               membatasi coa_asal ke salah satu dari 3 akun hutang, 'else MEMO' di CASE-nya tak pernah
               benar2 tereksekusi), sudah tercakup di #tr_v2docpakanovk (V2-PORTED block, cabang
               'Memorial'/'Koreksi Tambahan Hutang OVK'/'DN'). PAKAN-nya juga baru ditambahkan di sana. */

            IF OBJECT_ID('tempdb..#data_trans') IS NOT NULL BEGIN DROP TABLE #data_trans END
            select 
                    inv.tanggal as tanggal,
                    inv.supplier, 
                    inv.kode_trans as jenis_trans,
                    inv.nomor as no_inv,
                    inv.nomor as kode_trans,
                    inv.total as debet,
                    0 as kredit,
                    0 as saldo,
                    2 as urut,
                    inv.unit as unit,
                    inv.jenis_hutang as jenis_hutang
            into #data_trans
            from (
                select * from #tr_v2docpakanovk
                union all
                select * from #oa_trans
                union all
                select * from #tr_plasma
                union all
                select * from #tr_peral1
                union all
                select * from #tr_cndn
                union all
                select * from #tr_peral2
                union all
                select * from #tr_dn
                ) inv
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#data_bayar') IS NOT NULL BEGIN DROP TABLE #data_bayar END
            select
                    byr.tanggal as tanggal,
                    byr.supplier, 
                    byr.kode_trans as jenis_trans,
                    byr.nomor as no_inv,
                    byr.kode_trans as kode_trans,
                    byr.debet as debet,
                    byr.kredit as kredit,
                    0 as saldo,
                    2 as urut,
                    byr.unit as unit,
                    byr.jenis_hutang as jenis_hutang
                            into #data_bayar
            from
            (
                    select 
                        rp.tgl_bayar as tanggal,
                        case
                            when rp.supplier is not null and rp.supplier <> '' then
                                rp.supplier
                            when rp.peternak is not null and rp.peternak <> '' then
                                rp.peternak
                            when rp.ekspedisi is not null and rp.ekspedisi <> '' then
                                rp.ekspedisi
                        end as supplier,
                        rpd.no_bayar as nomor,
                        0 as debet,
                        sum(rpdcd.nominal) as kredit,
                        rpdcd.nomor_cn_dn as kode_trans,
                        cast(null as varchar(50)) as unit,
                        (case when max(rpd.transaksi) = 'VOADIP' then (case when max(case when rp.supplier is not null and rp.supplier <> '' then rp.supplier when rp.peternak is not null and rp.peternak <> '' then rp.peternak when rp.ekspedisi is not null and rp.ekspedisi <> '' then rp.ekspedisi end) = '19B004' then 'OVK ORP' else 'OVK NON ORP' end) else max(rpd.transaksi) end) as jenis_hutang
                    from realisasi_pembayaran_det_cn_dn rpdcd
                    left join
                        realisasi_pembayaran_det rpd
                        on
                            rpdcd.id_header = rpd.id
                    left join
                        realisasi_pembayaran rp
                        on
                            rpd.id_header = rp.id
                    where
                        rpdcd.nomor_cn_dn like '%CN%' and
                        rp.tgl_bayar between '".$start_date."' and '".$end_date."'
                    group by
                        rp.tgl_bayar,
                        rp.supplier,
                        rp.peternak,
                        rp.ekspedisi,
                        rpd.no_bayar,
                        rpdcd.nomor_cn_dn

                    union all

                    /* CN via cn_post_det DIHAPUS -- jenis_cn cuma 'DOC'/'PKN' (100% DOC+PAKAN), sudah
                       tercakup di #v2_pembayaran (V2-PORTED block, cabang 'CN'). */

                    select
                        rp.tgl_bayar as tanggal,
                        case
                            when rp.supplier is not null and rp.supplier <> '' then
                                rp.supplier
                            when rp.peternak is not null and rp.peternak <> '' then
                                rp.peternak
                            when rp.ekspedisi is not null and rp.ekspedisi <> '' then
                                rp.ekspedisi
                        end as supplier,
                        rpd.no_bayar as nomor,
                        0 as debet,
                        sum(rpd.potongan+rpd.uang_muka+rpd.transfer+isnull(kpop.potongan_pph_23, 0)) as kredit,
                        rpd.no_bayar as kode_trans,
                        max(konfir.unit) as unit,
                        (case when max(rpd.transaksi) = 'VOADIP' then (case when max(case when rp.supplier is not null and rp.supplier <> '' then rp.supplier when rp.peternak is not null and rp.peternak <> '' then rp.peternak when rp.ekspedisi is not null and rp.ekspedisi <> '' then rp.ekspedisi end) = '19B004' then 'OVK ORP' else 'OVK NON ORP' end) else max(rpd.transaksi) end) as jenis_hutang
                    from realisasi_pembayaran_det rpd
                    left join
                        konfirmasi_pembayaran_oa_pakan kpop
                        on
                            rpd.no_bayar = kpop.nomor
                    left join
                        realisasi_pembayaran rp
                        on
                            rpd.id_header = rp.id
                    left join
                        #konfir_helper konfir
                        on
                            konfir.nomor = rpd.no_bayar
                    where
                        rp.tgl_bayar between '".$start_date."' and '".$end_date."' and
                        /* DOC/PAKAN/VOADIP dikecualikan -- sudah tercakup lengkap di #v2_pembayaran. */
                        rpd.transaksi not in ('DOC', 'PAKAN', 'VOADIP')
                    group by
                        rp.tgl_bayar,
                        rp.supplier,
                        rp.peternak,
                        rp.ekspedisi,
                        rpd.no_bayar

                    union all

                    /* V2-PORTED: DOC + PAKAN + OVK payment sources (Realisasi Pembayaran + CN + PPh +
                       Pembulatan + Pelunasan Memorial + Reklasifikasi + Pembayaran/Pembalik Memorial) --
                       lihat #v2_pembayaran di preamble. kode_trans diisi V2's jenis_trans (label
                       deskriptif) krn #data_bayar final select memakai byr.kode_trans as jenis_trans. */
                    select
                        p.tanggal as tanggal,
                        p.supplier,
                        p.nomor as nomor,
                        0 as debet,
                        p.total as kredit,
                        p.jenis_trans as kode_trans,
                        kh.unit as unit,
                        p.jenis_hutang as jenis_hutang
                    from #v2_pembayaran p
                    left join
                        (select nomor, max(unit) as unit from #v2_sumber_hutang group by nomor) kh
                        on
                            kh.nomor = p.nomor
                    where
                        p.tanggal between '".$start_date."' and '".$end_date."'
                    /* END - V2-PORTED DOC/PAKAN/OVK payment */

                    union all

                    /* PPh & Pembulatan generik (non-DOC/PAKAN/VOADIP) SEMENTARA di-skip (2026-08-27,
                       permintaan user) -- utk DOC/PAKAN/VOADIP sendiri kini sudah ditangani lengkap via
                       #v2_pembayaran (leg 'PPh'/'Pembulatan') di atas. */

                    select bp.tgl_bayar as tanggal, op.supplier, bp.no_order as nomor, 0 as debet, sum(bpc.pakai) as kredit, c.nomor as kode_trans, op.unit as unit, 'PERALATAN' as jenis_hutang from bayar_peralatan_cn bpc
                    left join
                        bayar_peralatan bp
                        on
                            bpc.id_header = bp.id
                    left join
                        order_peralatan op
                        on
                            op.no_order = bp.no_order
                    left join
                        cn c
                        on
                            bpc.id_cn = c.id
                    where
                        c.tanggal between '".$start_date."' and '".$end_date."'
                    group by
                        bp.tgl_bayar,
                        op.supplier,
                        bp.no_order,
                        c.nomor,
                        op.unit

                    union all

                    select bp.tgl_bayar as tanggal, op.supplier, bp.no_order as nomor, 0 as debet, sum(bp.jml_bayar+bp.saldo) as kredit, bp.no_faktur as kode_trans, op.unit as unit, 'PERALATAN' as jenis_hutang from bayar_peralatan bp
                    left join
                        order_peralatan op
                        on
                            op.no_order = bp.no_order
                    where
                        bp.tgl_bayar between '".$start_date."' and '".$end_date."'
                    group by
                        bp.tgl_bayar,
                        op.supplier,
                        bp.no_order,
                        bp.no_faktur,
                        op.unit

                    union all

                    select
                        c.tanggal,
                        case
                            when (c.supplier is not null and c.supplier <> '') then
                                c.supplier
                            when (c.mitra is not null and c.mitra <> '') then
                                c.mitra
                        end as supplier,
                        c.nomor,
                        0 as debet,
                        (c.tot_cn - isnull(rpc.pakai, 0)) as kredit,
                        c.nomor as kode_trans,
                        cast(null as varchar(50)) as unit,
                        'CN' as jenis_hutang
                    from cn c
                    left join
                        (
                            select
                                sum(isnull(pakai, 0)) as pakai, id_cn
                            from
                            (
                                select sum(rpc.pakai) as pakai, rpc.id_cn from realisasi_pembayaran_cn rpc
                                left join
                                    realisasi_pembayaran rp
                                    on
                                        rpc.id_header = rp.id
                                where
                                    rp.tgl_bayar <= '".$end_date."'
                                group by 
                                    rpc.id_cn

                                union all

                                select sum(bpc.pakai) as pakai, bpc.id_cn from bayar_peralatan_cn bpc
                                left join
                                    bayar_peralatan bp
                                    on
                                        bpc.id_header = bp.id
                                where
                                    bp.tgl_bayar <= '".$end_date."'
                                group by 
                                    bpc.id_cn
                            ) rpc
                            group by
                                rpc.id_cn
                        ) rpc
                        on
                            c.id = rpc.id_cn
                    where 
                        c.tanggal <= '".$end_date."' and
                        c.tot_cn > isnull(rpc.pakai, 0) and
                        ((c.supplier is not null and c.supplier <> '') or (c.mitra is not null and c.mitra <> ''))

                    /* BAYAR LEWAT MEMO utk DOC/OVK (periode) DIHAPUS -- 100% DOC/OVK, sudah tercakup di
                       #v2_pembayaran di atas (cabang 'Pembayaran Memorial'/'Pelunasan Memorial (...)'/
                       'Pembalik Memorial'). PAKAN-nya juga baru ditambahkan lewat #v2_pembayaran. */
                ) byr
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#data') IS NOT NULL BEGIN DROP TABLE #data END
            select * into #data from (
                select * from #data_saldo
                union all
                select * from #data_trans
                union all
                select * from #data_bayar
            ) zzz_data
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            ;with tgl_helper as (
                select nomor, min(tgl) as tgl from (
                    select kpd.nomor, kpd.tgl_bayar as tgl from konfirmasi_pembayaran_doc kpd
                    union all
                    select kpp.nomor, kpp.tgl_bayar from konfirmasi_pembayaran_pakan kpp
                    union all
                    select kpv.nomor, kpv.tgl_bayar from konfirmasi_pembayaran_voadip kpv
                    union all
                    select kpt.nomor, kpt.tgl_bayar from konfirmasi_pembayaran_peternak kpt
                    union all
                    select kpop.nomor, kpop.tgl_bayar from konfirmasi_pembayaran_oa_pakan kpop
                    union all
                    select op.no_order, op.tgl_order from order_peralatan op
                    union all
                    select c.nomor, c.tanggal from cn c
                    union all
                    select d.nomor, d.tanggal from dn d
                    union all
                    select mi.no_mm, mi.tgl_mm from mmitem mi
                ) x
                group by nomor
            )

            select
                data.*,
                supl.nama as nama_supplier,
                isnull(th.tgl, min(data.tanggal) over (partition by data.supplier, data.no_inv)) as inv_tanggal
            from #data data
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
                left join
                    tgl_helper th
                    on
                        th.nomor = data.no_inv
            ".$where."
            order by
                data.supplier asc,
                inv_tanggal asc,
                data.no_inv asc,
                data.urut asc,
                data.tanggal asc,
                data.jenis_trans asc
            OPTION (MAXDOP 1, QUERYTRACEON 2312)
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        // cetak_r( $data, 1 );

        // sembunyikan invoice yang tidak ada aktivitas sama sekali (saldo awal 0, tanpa debet/kredit di periode ini)
        if ( !empty($data) ) {
            $rekap_inv = array();
            foreach ($data as $v_data) {
                if ( !isset($rekap_inv[$v_data['no_inv']]) ) {
                    $rekap_inv[$v_data['no_inv']] = array('debet' => 0, 'kredit' => 0, 'saldo_awal' => 0);
                }
                $rekap_inv[$v_data['no_inv']]['debet'] += $v_data['debet'];
                $rekap_inv[$v_data['no_inv']]['kredit'] += $v_data['kredit'];
                if ( $v_data['urut'] == 1 ) {
                    $rekap_inv[$v_data['no_inv']]['saldo_awal'] = $v_data['saldo'];
                }
            }

            $data = array_values(array_filter($data, function($v_data) use ($rekap_inv) {
                $rekap = $rekap_inv[$v_data['no_inv']];
                $kosong = ( $rekap['debet'] == 0 && $rekap['kredit'] == 0 && $rekap['saldo_awal'] == 0 );
                return !$kosong;
            }));
        }

        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }
}
