<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class KartuHutangLengkap extends Public_Controller {

    private $pathView = 'report/kartu_hutang_lengkap/';
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
                "assets/report/kartu_hutang_lengkap/js/kartu-hutang-lengkap.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/kartu_hutang_lengkap/css/kartu-hutang-lengkap.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['supplier'] = $this->getSupplier();
            $content['jenis'] = $this->getJenis();
            $content['jenis_hutang'] = $this->getJenisHutang();
            $content['title_menu'] = 'Laporan Kartu Hutang Lengkap';

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
            array('value' => 'DOC', 'label' => 'DOC'),
            array('value' => 'PAKAN', 'label' => 'PAKAN'),
            array('value' => 'OVK ORP', 'label' => 'OVK ORP'),
            array('value' => 'OVK NON ORP', 'label' => 'OVK NON ORP'),
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

        /* Filter jenis hutang di-push-down ke level union invoice (inv)/pembayaran (byr) SEBELUM
           di-agregasi jadi Saldo Awal/Transaksi Bulan Ini -- BUKAN di level outer/data, karena
           data.* hasil union 3-way (Saldo Awal + Transaksi + Bayar) tidak punya kolom jenis_hutang
           per-baris (Saldo Awal sudah di-sum lintas banyak invoice/jenis per supplier). */
        $flt_inv = null;
        $flt_byr = null;
        if ( !empty($jenis_hutang) ) {
            $list_jenis_hutang = "'".implode("', '", $jenis_hutang)."'";
            $flt_inv = "where inv.jenis_hutang in (".$list_jenis_hutang.")";
            $flt_byr = "where byr.jenis_hutang in (".$list_jenis_hutang.")";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
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

                select mi.no_invoice as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, konfirv3.unit, mi.no_mm as kode_trans, 'Koreksi Tambahan Hutang OVK' as jenis_trans, (case when mi.coa_asal = '21180.300' then 'OVK ORP' else 'OVK NON ORP' end) as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                left hash join #v2_kh_ovk konfirv3 on konfirv3.nomor = mi.no_invoice
                left hash join #v2_kh_ovk kh_memo_tambah on kh_memo_tambah.nomor = mi.no_invoice
                where mi.coa_asal in ('21180.300', '21174.000') and mi.coa_tujuan not in ('21180.300', '21174.000') and nullif(mi.no_invoice, '') is not null and kh_memo_tambah.nomor is not null
            ) x
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE INDEX ix_v2_sumber_hutang_nomor ON #v2_sumber_hutang(nomor)

            /* Pre-agregasi #v2_sumber_hutang per nomor invoice utk SALDO AWAL -- WAJIB, krn 1
               invoice bisa punya >1 baris di #v2_sumber_hutang (mis. Konfirmasi + Memorial/DN/
               Koreksi Tambahan utk invoice yg sama). Struktur SALDO AWAL laporan ini men-JOIN
               inv ke byr lewat nomor (bukan pre-netting spt V2/KartuHutangPerInvoice), jadi kalau
               inv dibiarkan multi-baris per nomor, byr (yg sudah ke-agregasi total per nomor) akan
               ke-subtract BERULANG utk tiap baris inv nomor yg sama -- salah. Pre-agregasi (sum
               total, min tanggal sbg tanggal invoice pertama muncul) menjamin 1 baris per invoice,
               konsisten dgn seluruh cabang LAINNYA di inv subquery SALDO AWAL (natural 1 baris/nomor). */
            IF OBJECT_ID('tempdb..#v2_saldo_inv') IS NOT NULL BEGIN DROP TABLE #v2_saldo_inv END
            select nomor, sum(total) as total, min(tanggal) as tanggal, max(supplier) as supplier, max(jenis_hutang) as jenis_hutang
            into #v2_saldo_inv
            from #v2_sumber_hutang
            where tanggal < '".$start_date."'
            group by nomor
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

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
                data.*,
                supl.nama as nama_supplier
            from (
                /* SALDO AWAL */
                select 
                    '".$start_date."' as tanggal,
                    inv.supplier,
                    'Saldo Awal' as jenis_trans,
                    0 as debet,
                    0 as kredit,
                    sum( (inv.total+(isnull(byr.dn, 0))) - (isnull(byr.cn, 0)+isnull(byr.potongan, 0)+isnull(byr.uang_muka, 0)+isnull(byr.transfer, 0)+isnull(byr.saldo, 0)) ) as saldo,
                    1 as urut
                from (
                    /* V2-PORTED: DOC + PAKAN + OVK debt sources (Konfirmasi + Memorial + DN + Koreksi Tambahan).
                       Dipakai #v2_saldo_inv (pre-agregasi per nomor) -- lihat #v2_saldo_inv di preamble. */
                    select nomor, supplier, total, jenis_hutang
                    from #v2_saldo_inv
                    /* END - V2-PORTED DOC/PAKAN/OVK */

                    union all

                    /* OA PAKAN */
                    select * from (
                        select kpop.nomor, kpop.ekspedisi_id as supplier, (kpop.total+kpop.potongan_pph_23) as total, 'LAINNYA' as jenis_hutang from konfirmasi_pembayaran_oa_pakan kpop
                        where
                            kpop.tgl_bayar < '".$start_date."'

                        union all

                        select tp.no_bbm as nomor, kp.ekspedisi_id as supplier, sum(dtp.jumlah)*kp.ongkos_angkut as total, 'LAINNYA' as jenis_hutang from det_terima_pakan dtp
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

                        select opp.no_sj as nomor, krm.ekspedisi_id as supplier, opp.ongkos_angkut as total, 'LAINNYA' as jenis_hutang from oa_pindah_pakan opp
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
                    /* END - OA PAKAN */

                    union all

                    select kpp.nomor, kpp.mitra as supplier, kpp.total, 'LAINNYA' as jenis_hutang from konfirmasi_pembayaran_peternak kpp
                    where
                        kpp.tgl_bayar < '".$start_date."'

                    union all

                    select op.no_order as nomor, op.supplier, op.total, 'LAINNYA' as jenis_hutang from order_peralatan op
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
                        'LAINNYA' as jenis_hutang
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
                                    rp.tgl_bayar < '".$start_date."'
                                group by 
                                    rpc.id_cn

                                union all

                                select sum(bpc.pakai) as pakai, bpc.id_cn from bayar_peralatan_cn bpc
                                left join
                                    bayar_peralatan bp
                                    on
                                        bpc.id_header = bp.id
                                where
                                    bp.tgl_bayar < '".$start_date."'
                                group by 
                                    bpc.id_cn

                                union all

                                select sum(ppc.pakai) as pakai, ppc.id_cn from pembayaran_pelanggan_cn ppc
                                left join
                                    pembayaran_pelanggan pp
                                    on
                                        ppc.id_header = pp.id
                                where
                                    pp.tgl_bayar < '".$start_date."'
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
                        'LAINNYA' as jenis_hutang
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
                                    rp.tgl_bayar < '".$start_date."'
                                group by 
                                    rpd.id_dn

                                union all

                                select sum(bpd.pakai) as pakai, bpd.id_dn from bayar_peralatan_dn bpd
                                left join
                                    bayar_peralatan bp
                                    on
                                        bpd.id_header = bp.id
                                where
                                    bp.tgl_bayar < '".$start_date."'
                                group by 
                                    bpd.id_dn

                                union all

                                select sum(ppd.pakai) as pakai, ppd.id_dn from pembayaran_pelanggan_dn ppd
                                left join
                                    pembayaran_pelanggan pp
                                    on
                                        ppd.id_header = pp.id
                                where
                                    pp.tgl_bayar < '".$start_date."'
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

                    /* INVOICE LEWAT PIUTANG */
                    select
                        p.kode as nomor,
                        p.mitra as supplier,
                        p.nominal as total,
                        'LAINNYA' as jenis_hutang
                    from piutang p
                    where
                        cast(p.tanggal as date) < '".$start_date."'
                    /* END - INVOICE LEWAT PIUTANG */
                ) inv
                left join
                    (
                        select
                            byr.nomor, 
                            sum(byr.cn) as cn, 
                            sum(byr.dn) as dn, 
                            sum(byr.potongan) as potongan, 
                            sum(byr.uang_muka) as uang_muka, 
                            sum(byr.transfer) as transfer, 
                            sum(byr.saldo) as saldo 
                        from
                        (
                            select 
                                rpd.no_bayar as nomor, 
                                sum(rpdcd.nominal) as cn, 
                                0 as dn, 
                                0 as potongan, 
                                0 as uang_muka, 
                                0 as transfer, 
                                0 as saldo 
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
    
                            select 
                                rpd.no_bayar as nomor, 
                                0 as cn, 
                                sum(rpdcd.nominal) as dn, 
                                0 as potongan, 
                                0 as uang_muka, 
                                0 as transfer, 
                                0 as saldo 
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
                                0 as saldo 
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
                                   uang_muka+PPh+pembulatan+CN+Memorial) di #v2_pembayaran (V2-PORTED block).
                                   Branch generik ini tetap jalan utk PLASMA/OA PAKAN/PERALATAN & transaksi lain. */
                                rpd.transaksi not in ('DOC', 'PAKAN', 'VOADIP')
                            group by
                                rpd.no_bayar

                            union all

                            /* V2-PORTED: DOC + PAKAN + OVK payment sources (Realisasi Pembayaran + CN + PPh +
                               Pembulatan + Pelunasan Memorial) -- lihat #v2_pembayaran di preamble. Ditaruh
                               di kolom transfer (netral, semua kolom byr di-SUM jadi 1 angka di sini). */
                            select
                                nomor,
                                0 as cn,
                                0 as dn,
                                0 as potongan,
                                0 as uang_muka,
                                sum(total) as transfer,
                                0 as saldo
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
                                0 as saldo 
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
                                0 as saldo 
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
                                sum(bp.saldo) as saldo 
                            from bayar_peralatan bp
                            where
                                bp.tgl_bayar < '".$start_date."'
                            group by
                                bp.no_order

                            union all

                            /* BAYAR LEWAT BANK */
                            select
                                invoice as nomor,
                                0 as cn, 
                                0 as dn, 
                                0 as potongan, 
                                0 as uang_muka, 
                                sum(nominal) as nominal,
                                0 as saldo
                            from det_jurnal 
                            where 
                                invoice is not null and 
                                invoice like 'PM%' and
                                tanggal < '".$start_date."'
                            group by 
                                invoice
                            /* END - BAYAR LEWAT BANK */
                        ) byr
                        group by
                            byr.nomor
                    ) byr
                    on
                        inv.nomor = byr.nomor
                ".$flt_inv."
                group by
                    inv.supplier
                /* END - SALDO AWAL */

                union all

                /* TRANSAKSI DI BULAN ITU */
                select 
                    inv.tanggal as tanggal,
                    inv.supplier, 
                    inv.kode_trans as jenis_trans,
                    inv.total as debet,
                    0 as kredit,
                    0 as saldo,
                    2 as urut
                from (
                    /* V2-PORTED: DOC + PAKAN + OVK debt sources for period -- lihat #v2_sumber_hutang di preamble */
                    select tanggal, nomor, supplier, total, kode_trans, jenis_hutang
                    from #v2_sumber_hutang
                    where tanggal between '".$start_date."' and '".$end_date."'
                    /* END - V2-PORTED DOC/PAKAN/OVK */

                    union all

                    /* OA PAKAN */
                    select * from (
                        select kpop.tgl_bayar as tanggal, kpop.nomor, kpop.ekspedisi_id as supplier, kpopd.total, tp.no_bbm as kode_trans, 'LAINNYA' as jenis_hutang from konfirmasi_pembayaran_oa_pakan_det kpopd
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

                        select tp.tgl_terima as tanggal, tp.no_bbm as nomor, kp.ekspedisi_id as supplier, sum(dtp.jumlah)*kp.ongkos_angkut as total, tp.no_bbm as kode_trans, 'LAINNYA' as jenis_hutang from det_terima_pakan dtp
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
                            tp.tgl_terima, tp.no_bbm, kp.ekspedisi_id, kp.ongkos_angkut

                        union all

                        select krm.tanggal, opp.no_sj as nomor, krm.ekspedisi_id as supplier, opp.ongkos_angkut as total, krm.kode_trans, 'LAINNYA' as jenis_hutang from oa_pindah_pakan opp
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
                    /* END - OA PAKAN */

                    union all

                    select kpp.tgl_bayar as tanggal, kpp.nomor, kpp.mitra as supplier, kppd.sub_total as total, rhpp.invoice as kode_trans, 'LAINNYA' as jenis_hutang from konfirmasi_pembayaran_peternak_det kppd
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
    
                    union all
    
                    select op.tgl_order as tanggal, op.no_order as nomor, op.supplier, op.total, op.no_order as kode_trans, 'LAINNYA' as jenis_hutang from order_peralatan op
                    where
                        op.tgl_order between '".$start_date."' and '".$end_date."'

                    union all

                    select
                        d.tanggal,
                        d.nomor,
                        case
                            when (d.supplier is not null and d.supplier <> '') then
                                d.supplier
                            when (d.mitra is not null and d.mitra <> '') then
                                d.mitra
                        end as supplier,
                        d.tot_dn as total,
                        d.nomor as kode_trans,
                        'LAINNYA' as jenis_hutang
                    from dn d
                    where
                        d.tanggal between '".$start_date."' and '".$end_date."' and
                        ((d.supplier is not null and d.supplier <> '') or (d.mitra is not null and d.mitra <> ''))

                    union all

                    /* INVOICE LEWAT PIUTANG */
                    select
                        p.tanggal,
                        p.kode as nomor,
                        p.mitra as supplier,
                        p.nominal as total,
                        p.kode as kode_trans,
                        'LAINNYA' as jenis_hutang
                    from piutang p
                    where
                        cast(p.tanggal as date) between '".$start_date."' and '".$end_date."'
                    /* END - INVOICE LEWAT PIUTANG */
                ) inv
                ".$flt_inv."
                /* END - TRANSAKSI DI BULAN ITU */

                union all

                select
                    byr.tanggal as tanggal,
                    byr.supplier, 
                    byr.kode_trans as jenis_trans,
                    byr.debet as debet,
                    byr.kredit as kredit,
                    0 as saldo,
                    2 as urut
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
                        case
                            when ( sum(rpd.potongan+rpd.uang_muka+rpd.transfer+isnull(kpop.potongan_pph_23, 0)) ) > sum(rpd.tagihan) then
                                sum(rpd.tagihan)
                            else
                                sum(rpd.potongan+rpd.uang_muka+rpd.transfer+isnull(kpop.potongan_pph_23, 0))
                        end as kredit,
                        -- sum(rpd.potongan+rpd.uang_muka+rpd.transfer+isnull(kpop.potongan_pph_23, 0)) as kredit,
                        rpd.no_bayar as kode_trans,
                        (case
                            when max(rpd.transaksi) = 'DOC' then 'DOC'
                            when max(rpd.transaksi) = 'PAKAN' then 'PAKAN'
                            when max(rpd.transaksi) = 'VOADIP' then
                                (case
                                    when
                                        (case
                                            when rp.supplier is not null and rp.supplier <> '' then rp.supplier
                                            when rp.peternak is not null and rp.peternak <> '' then rp.peternak
                                            when rp.ekspedisi is not null and rp.ekspedisi <> '' then rp.ekspedisi
                                        end) = '19B004' then 'OVK ORP'
                                    else 'OVK NON ORP'
                                end)
                            else 'LAINNYA'
                        end) as jenis_hutang
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
                        rp.tgl_bayar between '".$start_date."' and '".$end_date."' and
                        /* DOC/PAKAN/VOADIP dikecualikan -- sudah tercakup lengkap di #v2_pembayaran
                           (V2-PORTED block) di bawah, dgn logika PPh/CN/Pembulatan/Memorial yg lbh akurat. */
                        rpd.transaksi not in ('DOC', 'PAKAN', 'VOADIP')
                    group by
                        rp.tgl_bayar,
                        rp.supplier,
                        rp.peternak,
                        rp.ekspedisi,
                        rpd.no_bayar

                    union all

                    /* V2-PORTED: DOC + PAKAN + OVK payment sources for period -- lihat #v2_pembayaran di preamble */
                    select tanggal, supplier, nomor, 0 as debet, total as kredit, kode_trans, jenis_hutang
                    from #v2_pembayaran
                    where tanggal between '".$start_date."' and '".$end_date."'
                    /* END - V2-PORTED DOC/PAKAN/OVK payment */

                    union all

                    select bp.tgl_bayar as tanggal, op.supplier, bp.no_order as nomor, 0 as debet, sum(bp.jml_bayar+bp.saldo) as kredit, bp.no_faktur as kode_trans, 'LAINNYA' as jenis_hutang from bayar_peralatan bp
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
                        bp.no_faktur

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
                        c.tot_cn as kredit,
                        c.nomor as kode_trans,
                        'LAINNYA' as jenis_hutang
                    from cn c
                    where
                        c.tanggal between '".$start_date."' and '".$end_date."' and
                        ((c.supplier is not null and c.supplier <> '') or (c.mitra is not null and c.mitra <> ''))

                    union all

                    /* BAYAR LEWAT BANK */
                    select
                        dj.tanggal,
                        p.mitra as supplier,
                        dj.invoice as nomor,
                        0 as debet,
                        sum(dj.nominal) as kredit,
                        dj.tbl_id as kode_trans,
                        'LAINNYA' as jenis_hutang
                    from det_jurnal dj
                    left join
                        piutang p 
                        on
                            dj.invoice = p.kode
                    where 
                        dj.invoice is not null and 
                        dj.invoice like 'PM%' and
                        dj.tanggal between '".$start_date."' and '".$end_date."'
                    group by 
                        dj.tanggal,
                        p.mitra,
                        dj.invoice,
                        dj.tbl_id
                    /* END - BAYAR LEWAT BANK */
                ) byr
                ".$flt_byr."
            ) data
            left join
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
                ) supl
                on
                    supl.nomor = data.supplier
            ".$where."
            order by
                data.supplier asc,
                data.urut asc,
                data.tanggal asc,
                data.jenis_trans asc
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
