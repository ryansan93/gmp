<?php defined('BASEPATH') OR exit('No direct script access allowed');

class KartuHutangPerInvoiceV2 extends Public_Controller {

    private $pathView = 'report/kartu_hutang_per_invoice_v2/';
    private $url;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    public function index($segment=0)
    {
        $akses = hakAkses($this->url);
        // if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                "assets/report/kartu_hutang_per_invoice_v2/js/kartu-hutang-per-invoice-v2.js",
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
            $content['title_menu'] = 'Laporan Kartu Hutang Per Invoice (V2)';

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
            array('value' => 'DOC', 'label' => 'DOC'),
            array('value' => 'PAKAN', 'label' => 'PAKAN'),
            array('value' => 'OVK ORP', 'label' => 'OVK ORP'),
            array('value' => 'OVK NON ORP', 'label' => 'OVK NON ORP'),
            array('value' => 'PERALATAN', 'label' => 'PERALATAN'),
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

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $jenis = $params['jenis'];
        $supplier = $params['supplier'];
        $unit = $params['unit'];
        $jenis_hutang = isset($params['jenis_hutang']) ? $params['jenis_hutang'] : null;

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

        if ( $unit != 'all' ) {
            if ( empty( $where ) ) {
                $where = "where data.unit = '".$unit."'";
            } else {
                $where .= " and data.unit = '".$unit."'";
            }
        }

        if ( !empty($jenis_hutang) ) {
            if ( empty( $where ) ) {
                $where = "where data.jenis_hutang in ('".implode("', '", $jenis_hutang)."')";
            } else {
                $where .= " and data.jenis_hutang in ('".implode("', '", $jenis_hutang)."')";
            }
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            SET NOCOUNT ON;

            /* ============================================================================
               KARTU HUTANG PER INVOICE V2 -- DOC + PAKAN + OVK (2026-08-28)

               Desain baru, jauh lebih sederhana dari versi lama. Ketiga jenis pakai pola sama,
               dibedakan lewat kolom jenis_hutang ('DOC' / 'PAKAN' / 'OVK ORP' / 'OVK NON ORP') yg ditandai eksplisit
               di setiap cabang UNION -- BUKAN ditebak belakangan dari format nomor invoice.

               DOC:
               - Asal hutang: (1) Konfirmasi Pembayaran DOC, (2) Memorial yg mengkredit
                 COA 21180.200 'Hutang Niaga ORP (DOC)'
               - Pembayaran : (1) Realisasi Pembayaran (transaksi='DOC'), (2) CN (cn_post_det,
                 jenis_cn='DOC'), (3) PPh & Pembulatan dari realisasi_pembayaran_det/det_jurnal,
                 (4) beberapa pola memorial edge-case (lihat komentar masing2 di bawah).

               PAKAN:
               - Asal hutang: (1) Konfirmasi Pembayaran Pakan, (2) Memorial yg mengkredit
                 COA 21180.100 'Hutang Niaga ORP (Pakan)'
               - Pembayaran : (1) Realisasi Pembayaran (transaksi='PAKAN'), (2) CN (cn_post_det,
                 jenis_cn='PKN' -- BUKAN 'PAKAN', beda dari kode jenis_transaksi di modul lain),
                 (3) Pembulatan, (4) Pelunasan Memorial (Persediaan Pakan).
               - PPh TIDAK ADA utk PAKAN (rpd.pph & seluruh pipeline #dj_pph_* di bawah ini
                 khusus DOC saja) -- konfirmasi user 2026-08-28. Pola memorial edge-case DOC
                 lain (Pembayaran Memorial/Pembalik Memorial) BELUM direplikasi ke PAKAN krn
                 belum ada bukti pola yg sama terjadi di data PAKAN -- tambahkan kalau ditemukan
                 kasus serupa saat rekonsiliasi.
               - Pelunasan Memorial (Persediaan Pakan) -- coa_asal=12030.000, coa_tujuan=21180.100,
                 no_invoice diisi manual ke invoice yg dikoreksi -- dipakai utk kasus duplikasi
                 konfirmasi_pembayaran_pakan (No. SJ 3016262723/3016297657/3016303000/3016303014/
                 3016303028, unit PSR/TAG/LMG, Nov 2025, dikoreksi via memo MM2512310061/62/63).
                 Awalnya sempat dicoba deteksi otomatis di level SJ (tanpa no_invoice), TAPI
                 diganti balik ke pola no_invoice spt DOC (lebih auditable & konsisten) setelah
                 no_invoice ke-5 memo itu diisi manual ke invoice DUPLIKAT-nya masing2.

               OVK:
               - PENTING -- ADA 2 COA HUTANG OVK (ketahuan 2026-08-29): 21180.300 'Hutang Niaga
                 ORP (OVK)' KHUSUS supplier 19B004/PT AGRINUSA JAYA SANTOSA (satu2nya yg pakai
                 terima_voadip sbg sumber auto-jurnal GL-nya), dan 21174.000 'Hutang Niaga Extern
                 (OVK)' utk SEMUA supplier OVK LAINNYA (Medion, Romindo, Vetindo, CV-CV, dll --
                 terverifikasi: 0 baris terima_voadip AJS nyasar ke 21174.000, & sebaliknya).
                 Laporan ini SENGAJA tidak membedakan -- semua kondisi WHERE di bawah cek KEDUA
                 akun (`in ('21180.300','21174.000')`) krn laporan cuma peduli TOTAL hutang OVK
                 per invoice/supplier, bukan klasifikasi internal/eksternal-nya. Memo koreksi utk
                 supplier NON-AJS harus pakai 21174.000 (bukan 21180.300) -- 15 memo sesi ini
                 (MM2607310039-051, semua non-AJS) sempat salah pakai 21180.300 lalu direklasifikasi
                 ke 21174.000 setelah temuan ini; MM2607310052/53/54 (AJS) TETAP 21180.300 (benar).
               - Asal hutang: (1) Konfirmasi Pembayaran OVK, (2) Memorial yg mengkredit akun hutang
                 OVK (21180.300 utk AJS / 21174.000 utk lainnya), (3) DN (dn_post_det, jenis_dn='OVK' --
                 SATU2NYA dari ketiga jenis yg benar2 pakai DN; dn.jenis_dn di seluruh data cuma
                 ada 'OVK'). DN sengaja ditaruh di ASAL HUTANG (bukan Pembayaran spt CN) krn
                 arahnya justru MENAMBAH hutang -- terverifikasi empiris: invoice BYV/10/25/00156
                 (konfirmasi 18.259.299,25) + DN (91.018,50) = 18.350.317,75, PERSIS sama dgn
                 Realisasi Pembayaran aktualnya. Sempat salah taruh di Pembayaran dulu (dikira
                 simetris dgn CN), ketahuan dari residual invoice itu persis -182.037 (2x nilai DN).
               - Pembayaran : (1) Realisasi Pembayaran (transaksi='VOADIP' -- BEDA dari kode
                 jenis_hutang laporan ini), (2) Pembulatan.
               - PPh TIDAK ADA (sama spt PAKAN, rpd.pph selalu NULL/0 utk VOADIP).
               - CN TIDAK ADA SAMA SEKALI utk OVK (cn_post.jenis_cn di seluruh data cuma ada
                 'DOC'/'PKN', tidak pernah 'OVK').
               - Supplier OVK lebih dari satu (obat/vaksin dari PT Medion, Agrinusa, Romindo,
                 Vetindo, Mitra Buana, dll -- beda dari DOC/PAKAN yg cuma dari 19B005/JAPFA),
                 tapi laporan ini sudah dirancang generik per-supplier sejak awal jadi otomatis
                 kebentuk grup terpisah, tidak perlu perubahan struktur.
               - Pelunasan Memorial (Persediaan OVK) -- coa_tujuan=21180.300, no_invoice diisi
                 manual ke invoice yg dikoreksi -- dipakai utk kasus duplikasi
                 konfirmasi_pembayaran_voadip (SJ J851125000017 & SSK251141-0480, unit TAG, Nov
                 2025, dikoreksi via memo MM2607310039/40). Pola Pembalik Memorial spt DOC BELUM
                 direplikasi ke OVK krn belum ada bukti kasus serupa -- tambahkan kalau ditemukan
                 saat rekonsiliasi.
               - Koreksi Tambahan Hutang OVK (di #sumber_hutang) -- coa_asal=21180.300, KEBALIKAN
                 arah dari Pelunasan Memorial di atas. Dipakai saat konfirmasi_pembayaran_voadip_det
                 .total (level SJ) ke-input LEBIH RENDAH dari yg sebenarnya -- beda root-cause dari
                 kasus duplikasi di atas (bukan 2x-hitung, tapi 1 baris dgn nilai salah/kurang).
                 Ketahuan dari cross-check ke Excel sumber (bukan ke konfirmasi_pembayaran_voadip_det2
                 -- item breakdown itu SERING kosong/tidak diisi & jgn dijadikan acuan, per arahan
                 user 2026-08-29). 3 kasus ditemukan lewat rekonsiliasi Excel s.d. 31 Juli 2026: SJ
                 001916 unit KDR (BYV/10/25/00205, PT double di det shg malah kurang arah -- dikoreksi
                 turun via Pelunasan Memorial biasa, MM2607310041), SJ 04776 unit MJK
                 (BYV/11/25/00124, MM2607310042) & SJ 002817 unit JBR (BYV/12/25/00079,
                 MM2607310043) sama-sama det KELEBIHAN dari Excel -- dikoreksi turun. SJ 003/LMJ/2025
                 unit LMJ (BYV/12/25/00077, CV MITRA GEMUK BERSAMA) JUSTRU det KURANG dari Excel
                 (3.967.500 vs 4.045.400) -- satu-satunya yg butuh branch baru ini, dikoreksi naik
                 via memo MM2607310044.

               Umum (berlaku ketiga jenis):
               - realisasi_pembayaran_det_cn_dn (mekanisme CN-via-payment-event) kosong utk
                 DOC, PAKAN, & OVK di data ini, jadi tdk perlu 2 jalur CN spt versi lama
               - Tanggal TERIMA (bukan tgl_bayar konfirmasi) dipakai utk filter Tanggal Awal/Akhir
                 di ketiganya -- DOC (terima_doc.datang), PAKAN (terima_pakan.tgl_terima), OVK
                 (terima_voadip.tgl_terima) -- supaya mengikuti kapan barang benar2 diterima,
                 bukan tanggal rencana bayar. Sempat dicoba ganti PAKAN ke tanggal ORDER
                 (order_pakan.tgl_trans) 2026-08-28, tapi dikembalikan lagi ke tanggal terima di
                 hari yg sama -- lihat komentar #konfir_helper, #konfir_helper_pakan, &
                 #konfir_helper_ovk.
               ============================================================================ */

            /* Tanggal 'terima' per invoice = tanggal DOC datang di kandang (terima_doc.datang),
               diambil PALING AWAL kalau 1 invoice mencakup >1 order/SJ. Dipakai sbg tanggal
               pembentuk hutang (menggantikan kpd.tgl_bayar) supaya filter Tanggal Awal/Akhir
               di laporan ini mengikuti kapan DOC-nya benar2 diterima, bukan tanggal rencana bayar.
               Fallback ke kpd.tgl_bayar kalau tidak ketemu terima_doc-nya (jarang, ~22 dari 3000+
               baris konfirmasi sejak 2026 -- biasanya invoice via memo/tanpa jurnal otomatis). */
            IF OBJECT_ID('tempdb..#konfir_helper') IS NOT NULL BEGIN DROP TABLE #konfir_helper END
            select kpd.nomor, max(kpdd.kode_unit) as unit, min(td.datang) as tgl_terima
            into #konfir_helper
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

            CREATE UNIQUE CLUSTERED INDEX ix_konfir_helper ON #konfir_helper(nomor)

            /* Sama seperti #konfir_helper di atas, versi PAKAN. no_order pada
               konfirmasi_pembayaran_pakan_det match 1:1 ke kirim_pakan.no_order (beda dari DOC
               yg lewat terima_doc) -- 100% baris det ketemu tgl_terima-nya di data ini, tapi
               tetap pakai LEFT JOIN + fallback ke tgl_bayar spy aman kalau ada data masa depan
               yg tidak lengkap. */
            IF OBJECT_ID('tempdb..#konfir_helper_pakan') IS NOT NULL BEGIN DROP TABLE #konfir_helper_pakan END
            select kpp.nomor, max(kppd.kode_unit) as unit, min(tp.tgl_terima) as tgl_terima
            into #konfir_helper_pakan
            from konfirmasi_pembayaran_pakan kpp
            left join konfirmasi_pembayaran_pakan_det kppd on kppd.id_header = kpp.id
            left join kirim_pakan kp on kp.no_order = kppd.no_order
            left join terima_pakan tp on tp.id_kirim_pakan = kp.id
            group by kpp.nomor
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE UNIQUE CLUSTERED INDEX ix_konfir_helper_pakan ON #konfir_helper_pakan(nomor)

            /* Sama seperti #konfir_helper_pakan di atas, versi OVK. no_order pada
               konfirmasi_pembayaran_voadip_det match 1:1 ke kirim_voadip.no_order -- 100% baris
               det ketemu tgl_terima-nya di data ini (1793/1793), tapi tetap pakai LEFT JOIN +
               fallback ke tgl_bayar spy aman kalau ada data masa depan yg tidak lengkap. */
            IF OBJECT_ID('tempdb..#konfir_helper_ovk') IS NOT NULL BEGIN DROP TABLE #konfir_helper_ovk END
            select kpv.nomor, max(kpvd.kode_unit) as unit, min(tv.tgl_terima) as tgl_terima
            into #konfir_helper_ovk
            from konfirmasi_pembayaran_voadip kpv
            left join konfirmasi_pembayaran_voadip_det kpvd on kpvd.id_header = kpv.id
            left join kirim_voadip kv on kv.no_order = kpvd.no_order
            left join terima_voadip tv on tv.id_kirim_voadip = kv.id
            group by kpv.nomor
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE UNIQUE CLUSTERED INDEX ix_konfir_helper_ovk ON #konfir_helper_ovk(nomor)

            /* Daftar 'invoice hantu' OVK (2026-09-04) -- nomor yg dihasilkan cabang 'Memorial'/
               'Memorial reversed' di bawah (memo standalone tanpa invoice riil, coa_asal ATAU
               coa_tujuan menyentuh SATU akun hutang OVK, sisi lawannya BUKAN akun OVK, supplier
               terisi). Dipakai 2 arah: (1) cabang 'Memorial' HARUS kecualikan baris yg no_invoice-nya
               menunjuk ke nomor ini (spy tidak nambah lagi ke invoice hantu yg SUDAH ada -- kasus
               nyata: entri baru yg sengaja merujuk 'MM2606130002' sbg No. Invoice, niatnya
               MELUNASI invoice hantu itu, bukan menambah lagi), (2) cabang 'Pelunasan Memorial'
               boleh mencocokkan ke sini SELAIN ke invoice riil (#konfir_helper_ovk) -- supaya
               user bisa benar2 'melunasi' invoice hantu spt invoice biasa. Lihat
               [[selisih-122-ovk-nonorp-v2]]. */
            IF OBJECT_ID('tempdb..#invoice_hantu_ovk') IS NOT NULL BEGIN DROP TABLE #invoice_hantu_ovk END
            select distinct isnull(nullif(mi.no_invoice, ''), mi.no_mm) as nomor
            into #invoice_hantu_ovk
            from mmitem mi
            left join mm m on mi.no_mm = m.no_mm
            left hash join #konfir_helper_ovk kh_ih on kh_ih.nomor = mi.no_invoice
            where (
                    (mi.coa_tujuan in ('21180.300', '21174.000') and isnull(mi.coa_asal, '') not in ('21180.300', '21174.000'))
                    or
                    (mi.coa_asal in ('21180.300', '21174.000') and nullif(mi.no_invoice, '') is null)
                  )
                  and kh_ih.nomor is null
                  and nullif(m.no_supplier, '') is not null
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE UNIQUE CLUSTERED INDEX ix_invoice_hantu_ovk ON #invoice_hantu_ovk(nomor)

            IF OBJECT_ID('tempdb..#sumber_hutang') IS NOT NULL BEGIN DROP TABLE #sumber_hutang END
            select nomor, tanggal, supplier, total, unit, kode_trans, jenis_trans, jenis_hutang
            into #sumber_hutang
            from (
                select kpd.nomor, cast(isnull(konfir.tgl_terima, kpd.tgl_bayar) as date) as tanggal, kpd.supplier, kpd.total, konfir.unit, kpd.nomor as kode_trans, 'Konfirmasi Pembayaran DOC' as jenis_trans, 'DOC' as jenis_hutang
                from konfirmasi_pembayaran_doc kpd
                left join #konfir_helper konfir on konfir.nomor = kpd.nomor

                union all

                /* Kecualikan memo yg invoice-nya SUDAH ada di konfirmasi_pembayaran_doc -- memo begini
                   biasanya cuma penyusulan jurnal GL krn jurnal otomatis terima_doc gagal jalan,
                   BUKAN hutang baru (nilainya persis sama dgn konfirmasi). Kalau tetap diikutkan,
                   hutangnya kehitung dobel. Pola sama dgn fix BYD/11/25/00332 di laporan lama. */
                select isnull(nullif(mi.no_invoice, ''), mi.no_mm) as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, isnull(nullif(mi.unit, ''), m.unit) as unit, mi.no_mm as kode_trans, 'Memorial' as jenis_trans, 'DOC' as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                left hash join #konfir_helper kh_memo on kh_memo.nomor = mi.no_invoice
                where mi.coa_tujuan = '21180.200' and kh_memo.nomor is null
                  and nullif(m.no_supplier, '') is not null

                union all

                select kpp.nomor, cast(isnull(konfirp.tgl_terima, kpp.tgl_bayar) as date) as tanggal, kpp.supplier, kpp.total, konfirp.unit, kpp.nomor as kode_trans, 'Konfirmasi Pembayaran Pakan' as jenis_trans, 'PAKAN' as jenis_hutang
                from konfirmasi_pembayaran_pakan kpp
                left join #konfir_helper_pakan konfirp on konfirp.nomor = kpp.nomor

                union all

                /* Pola sama dgn memo DOC di atas -- kecualikan memo yg invoice-nya sudah ada di
                   konfirmasi_pembayaran_pakan supaya tidak dobel hitung. */
                select isnull(nullif(mi.no_invoice, ''), mi.no_mm) as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, isnull(nullif(mi.unit, ''), m.unit) as unit, mi.no_mm as kode_trans, 'Memorial' as jenis_trans, 'PAKAN' as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                left hash join #konfir_helper_pakan kh_memo_p on kh_memo_p.nomor = mi.no_invoice
                where mi.coa_tujuan = '21180.100' and kh_memo_p.nomor is null
                  and nullif(m.no_supplier, '') is not null

                union all

                select kpv.nomor, cast(isnull(konfirv.tgl_terima, kpv.tgl_bayar) as date) as tanggal, kpv.supplier, kpv.total, konfirv.unit, kpv.nomor as kode_trans, 'Konfirmasi Pembayaran OVK' as jenis_trans, (case when kpv.supplier = '19B004' then 'OVK ORP' else 'OVK NON ORP' end) as jenis_hutang
                from konfirmasi_pembayaran_voadip kpv
                left join #konfir_helper_ovk konfirv on konfirv.nomor = kpv.nomor

                union all

                /* Pola sama dgn memo DOC/PAKAN di atas -- kecualikan memo yg invoice-nya sudah
                   ada di konfirmasi_pembayaran_voadip supaya tidak dobel hitung.

                   Memo TANPA supplier DAN tanpa no_invoice dikecualikan (2026-09-03, kasus
                   MM2608310021 'RECLASS PENGAKUAN KOREKSI HUTANG BYL-SLM-MDN') -- 4 baris mmitem
                   semuanya lewat akun wash 27001.000 (Profit Center Zero Balance), net efek ke
                   SETIAP akun yg disentuh (12050.000, 21180.300, 27001.000 sendiri) = NOL
                   (self-cancelling by construction), murni reklas internal tanpa hutang riil.
                   TAPI krn no_invoice kosong di 2 dari 4 baris, branch ini (coa_tujuan) DAN branch
                   'Memorial reversed' di bawah (coa_asal) SAMA-SAMA nangkep baris berbeda dari
                   memo yg sama sbg 'invoice hantu' bernomor no_mm-nya sendiri -- nambah hutang ORP
                   palsu 2x91.027,75 = 182.055,50 yg tidak pernah ada secara GL. Aturan umum (bukan
                   whitelist per no_mm) -- m.no_supplier kosong berarti memo ini murni reklas
                   internal/wash, tidak terkait supplier manapun, jadi TIDAK PERNAH relevan
                   dimasukkan sbg 'invoice hantu' ke laporan hutang per-invoice ini, no_mm apapun.
                   Lihat [[selisih-122-ovk-nonorp-v2]]. */
                /* coa_asal HARUS BUKAN akun hutang OVK (2026-09-03) -- kalau kedua sisi (asal &
                   tujuan) sama2 akun hutang OVK, coa_ASAL (sisi kredit = hutang mana yg BENAR2
                   bertambah) yg semestinya menentukan tag, bukan coa_tujuan (cuma akun lawan) --
                   diserahkan ke branch 'Memorial reversed' di bawah. Sebelumnya branch ini TIDAK
                   py guard simetris ini (beda dari 'Memorial reversed' yg sudah py), jadi kasus
                   spt MM2606130002/MM2606190005 (coa_asal=21174.000 tapi coa_tujuan=21180.300,
                   no_invoice sengaja dikosongkan user 2026-09-03 spy jadi invoice standalone)
                   salah ke-tag 'OVK ORP' dari coa_tujuan, padahal kreditnya jelas ke 21174.000
                   (NON ORP). Lihat [[selisih-122-ovk-nonorp-v2]]. */
                select isnull(nullif(mi.no_invoice, ''), mi.no_mm) as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, isnull(nullif(mi.unit, ''), m.unit) as unit, mi.no_mm as kode_trans, 'Memorial' as jenis_trans, (case when mi.coa_tujuan = '21180.300' then 'OVK ORP' else 'OVK NON ORP' end) as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                left hash join #konfir_helper_ovk kh_memo_v on kh_memo_v.nomor = mi.no_invoice
                left hash join #invoice_hantu_ovk ih_v on ih_v.nomor = mi.no_invoice
                where mi.coa_tujuan in ('21180.300', '21174.000') and kh_memo_v.nomor is null
                  and isnull(mi.coa_asal, '') not in ('21180.300', '21174.000')
                  and nullif(m.no_supplier, '') is not null
                  and ih_v.nomor is null

                union all

                /* Varian 'Memorial' KEBALIKAN arah -- coa_ASAL (bukan coa_tujuan) yg kena akun
                   hutang OVK, standalone (no_invoice kosong/tak match). Ketemu dari kasus
                   MM2604270001 (Vetindo, SJ 2685): baris kreditnya (21174.000, tanpa no_invoice)
                   PAKAI POLARITAS GL YG BENAR (coa_asal=hutang=kredit=tambah, sesuai konvensi
                   terima_voadip) tapi jadi TAK KEBACA branch 'Memorial' di atas yg justru
                   mensyaratkan coa_tujuan (pola historis dari memo Medion, polaritas terbalik).
                   Excel MEMBUKTIKAN 2,5jt ini hutang Vetindo yg sah (2026-08-29) -- tanpa branch
                   ini baris tsb TAK PERNAH muncul di laporan sama sekali utk supplier manapun.
                   coa_tujuan BOLEH kosong ATAU akun hutang OVK yg satunya (2026-09-03, guard lama
                   dilepas) -- kasus 'kedua sisi sama2 akun hutang OVK' TIDAK otomatis reklasifikasi
                   antar-akun genuine; setelah diaudit tuntas (query terpisah thd SELURUH mmitem),
                   pola ini di data SEKARANG cuma milik MM2606130002/MM2606190005 (koreksi
                   pembulatan salah akun, BUKAN reklas) -- diputuskan coa_asal (sisi kredit) yg
                   menentukan tag, bukan mengecualikan baris ini sepenuhnya. Guard simetris
                   dipindah ke branch 'Memorial' di atas (coa_tujuan HARUS BUKAN akun OVK) supaya
                   kedua branch tidak saling tumpang tindih utk 1 baris yg sama. Kalau nanti
                   ditemukan kasus reklas genuine baru dgn pola sama, cek ulang via query audit yg
                   sama sebelum asumsi ini masih valid. Lihat [[selisih-122-ovk-nonorp-v2]]. */
                select isnull(nullif(mi.no_invoice, ''), mi.no_mm) as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, isnull(nullif(mi.unit, ''), m.unit) as unit, mi.no_mm as kode_trans, 'Memorial' as jenis_trans, (case when mi.coa_asal = '21180.300' then 'OVK ORP' else 'OVK NON ORP' end) as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                left hash join #konfir_helper_ovk kh_memo_v2 on kh_memo_v2.nomor = mi.no_invoice
                where mi.coa_asal in ('21180.300', '21174.000')
                  and nullif(mi.no_invoice, '') is null
                  and kh_memo_v2.nomor is null
                  and nullif(m.no_supplier, '') is not null

                union all

                /* DN (dn_post_det, jenis_dn='OVK') -- BEDA arah dari CN: DN justru MENAMBAH hutang,
                   bukan mengurangi. Terverifikasi empiris: invoice BYV/10/25/00156 (konfirmasi
                   18.259.299,25) + DN ini (91.018,50) = 18.350.317,75, PERSIS sama dgn Realisasi
                   Pembayaran aktualnya -- kalau DN diletakkan di #pembayaran (dikira spt CN),
                   nilainya kehitung dobel-kurang (residual -182.037, 2x nilai DN). */
                select dpd.nomor, dp.tanggal, kpv.supplier, dpd.pakai as total, konfirv2.unit, dp.no_dn as kode_trans, 'DN' as jenis_trans, (case when kpv.supplier = '19B004' then 'OVK ORP' else 'OVK NON ORP' end) as jenis_hutang
                from dn_post_det dpd
                left join dn_post dp on dp.id = dpd.id_header
                left join konfirmasi_pembayaran_voadip kpv on kpv.nomor = dpd.nomor
                left join #konfir_helper_ovk konfirv2 on konfirv2.nomor = dpd.nomor
                where dp.jenis_dn = 'OVK'

                union all

                /* Koreksi TAMBAHAN hutang OVK utk invoice yg SUDAH terkonfirmasi -- coa_asal=21180.300
                   (menambah hutang, KEBALIKAN arah dari 'Pelunasan Memorial (Persediaan OVK)' di
                   #pembayaran). Dipakai saat konfirmasi_pembayaran_voadip_det.total (level SJ) ke-input
                   LEBIH RENDAH dari yg sebenarnya -- ketahuan dari cross-check ke Excel sumber (kasus SJ
                   003/LMJ/2025 unit LMJ, BYV/12/25/00077, CV MITRA GEMUK BERSAMA: det.total tercatat
                   3.967.500 tapi rincian item Excel & realisasi pembayaran aktual 4.045.400, selisih
                   77.900, dikoreksi via memo MM2607310044). no_invoice WAJIB diisi & invoice-nya HARUS
                   sudah ada konfirmasi (kh_memo_tambah.nomor is not null) spy tidak nyasar jadi invoice
                   hantu baru -- otomatis terpisah dari branch 'Memorial' di atas yg justru mensyaratkan
                   kh_memo_v.nomor IS NULL, jadi tidak akan pernah tumpang tindih.
                   coa_tujuan HARUS BUKAN salah satu dari 2 akun hutang OVK (21180.300/21174.000) --
                   kalau kedua sisi (asal & tujuan) sama2 akun hutang OVK, itu REKLASIFIKASI ANTAR-AKUN
                   (internal<->eksternal, mis. MM2604270001 Vetindo), BUKAN nambah hutang murni -- kalau
                   tidak dikecualikan, entry itu KEHITUNG DOBEL (nambah di sini via coa_asal, DAN kurang
                   di 'Pelunasan Memorial' di bawah via coa_tujuan) -- ketahuan dari residual 0,68/-0,25
                   di CV Mitra Gemilang/Gemuk stlh kedua cabang di-broaden cek 2 akun (2026-08-29).

                   MM2606130002/MM2606190005 (CV Mitra Gemilang Bersinar, 25S053, coa_tujuan=21180.300
                   SALAH pilih akun -- harusnya akun netral) SEMPAT di-whitelist eksplisit by no_mm di
                   sini (2026-09-03) supaya lolos guard di atas, krn TERBUKTI BUKAN reklasifikasi genuine
                   (invoice-nya sama2 NON-ORP, masing2 mm cuma 1 baris tanpa pasangan spt kasus Vetindo).
                   Whitelist itu SUDAH DILEPAS LAGI (2026-09-03, sesi sama) -- diganti pendekatan GL:
                   memo pembalik baru (via akun netral 96010.000, TIDAK menyentuh 2 akun OVK sekaligus
                   dlm 1 baris) utk membatalkan kredit 0,68 yg salah arah tsb dari 21174.000 & memulihkan
                   21180.300, tanpa perlu exception apa pun di query ini. Residual GL-vs-laporan utk
                   21174.000 target 389.023.650,68 (bukan 651,36) stlh memo pembalik itu diposting.
                   Lihat [[selisih-122-ovk-nonorp-v2]]. */
                select mi.no_invoice as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, konfirv3.unit, mi.no_mm as kode_trans, 'Koreksi Tambahan Hutang OVK' as jenis_trans, (case when mi.coa_asal = '21180.300' then 'OVK ORP' else 'OVK NON ORP' end) as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                left hash join #konfir_helper_ovk konfirv3 on konfirv3.nomor = mi.no_invoice
                left hash join #konfir_helper_ovk kh_memo_tambah on kh_memo_tambah.nomor = mi.no_invoice
                where mi.coa_asal in ('21180.300', '21174.000')
                  and mi.coa_tujuan not in ('21180.300', '21174.000')
                  and nullif(mi.no_invoice, '') is not null and kh_memo_tambah.nomor is not null

                union all

                /* PERALATAN -- Asal hutang dari order_peralatan (bukan jurnal GL). Pola sama spt
                   DOC/PAKAN/OVK: setiap order jadi 1 baris debet, di-join per nomor (no_order) dgn
                   pembayarannya (#pembayaran branch 'Bayar Peralatan') utk hitung saldo per invoice.
                   Supplier & unit diambil dari order_peralatan-nya langsung. */
                select op.no_order as nomor, cast(op.tgl_order as date) as tanggal, op.supplier, op.total as total, op.unit, op.no_order as kode_trans, 'Order Peralatan' as jenis_trans, 'PERALATAN' as jenis_hutang
                from order_peralatan op
            ) x
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE INDEX ix_sumber_hutang_nomor ON #sumber_hutang(nomor)

            /* PPh -- tidak ada sumber selain det_jurnal (rpd.pph selalu NULL utk DOC). SETIAP tahap
               di-materialize ke #temp SENDIRI-SENDIRI (JANGAN digabung jadi 1 statement CTE+CROSS
               APPLY+JOIN) -- det_jurnal.keterangan bertipe TEXT (legacy), dan kalau CROSS APPLY
               STRING_SPLIT atas kolom TEXT digabung langsung dgn JOIN lain dlm 1 statement, legacy
               cardinality estimator (SQL Server 2012 di LIVE) salah total mengestimasi cost --
               teruji >2 menit vs <1 detik kalau dipisah begini (root-cause sama sesi sebelumnya). */
            /* terima_doc.no_sj kadang berisi LEBIH DARI SATU no_sj digabung jadi 1 string dipisah
               spasi (mis. '3201421198 3201421199', kasus BYD/06/26/00126) -- bukan 1 baris per
               no_sj. Kalau tidak di-split, PPh-nya invoice itu tidak pernah ke-match (invoice NULL
               di det_jurnal, keterangan-nya jg berisi kedua no_sj itu, tapi match exact gagal). */
            IF OBJECT_ID('tempdb..#docref_raw') IS NOT NULL BEGIN DROP TABLE #docref_raw END
            select kpd.nomor as invoice, td.no_sj
            into #docref_raw
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

            /* no_sj/keterangan kadang gabung banyak no_sj dipisah '&' atau ',' selain spasi (mis.
               '3201444456 & 3201444455') -- begitu di-split by spasi, simbol pemisahnya sendiri
               ('&', ',') ikut jadi token TERSENDIRI. Karena BANYAK baris beda invoice sama2 punya
               token sampah itu, mereka salah cocok satu sama lain lewat situ (PPh invoice A ke-
               atribusi ke invoice B). Filter token yg bukan kode SJ asli (harus mulai angka & cukup
               panjang) SEBELUM dipakai matching -- root cause kasus BYD/07/26/00393 (dobel PPh). */
            IF OBJECT_ID('tempdb..#docref_helper') IS NOT NULL BEGIN DROP TABLE #docref_helper END
            select distinct invoice, tok.value as no_sj
            into #docref_helper
            from #docref_raw
            cross apply string_split(no_sj, ' ') tok
            where tok.value like '[0-9]%' and len(tok.value) >= 6
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#dj_pph_direct') IS NOT NULL BEGIN DROP TABLE #dj_pph_direct END
            select invoice, tbl_id, sum(nominal) as pph
            into #dj_pph_direct
            from det_jurnal
            where tbl_name = 'realisasi_pembayaran' and coa_asal like '246%' and invoice is not null and invoice <> ''
            group by invoice, tbl_id
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#dj_pph_fallback') IS NOT NULL BEGIN DROP TABLE #dj_pph_fallback END
            select id, tbl_id, nominal, cast(keterangan as varchar(300)) as keterangan
            into #dj_pph_fallback
            from det_jurnal
            where tbl_name = 'realisasi_pembayaran' and coa_asal like '246%' and (invoice is null or invoice = '')
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#dj_pph_tokens') IS NOT NULL BEGIN DROP TABLE #dj_pph_tokens END
            select id, tbl_id, nominal, tok.value as token
            into #dj_pph_tokens
            from #dj_pph_fallback dj
            cross apply string_split(dj.keterangan, ' ') tok
            where tok.value like '[0-9]%' and len(tok.value) >= 6
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            /* keterangan (mis. 'PEMBAYARAN DOC 3201421198 3201421199') dan no_sj di #docref_helper
               BISA sama2 berisi >1 token (order gabungan/multi-SJ, kasus BYD/06/26/00126) -- kalau
               langsung SUM(nominal) tanpa dedup dulu, hasil match token x token bikin nominal yg
               SAMA (1 baris det_jurnal) ke-hitung berkali-kali (PPh jadi dobel/tripel). Dedup ke
               1 baris per (id det_jurnal asli) dulu SEBELUM sum per invoice. */
            IF OBJECT_ID('tempdb..#dj_pph_fallback_dedup') IS NOT NULL BEGIN DROP TABLE #dj_pph_fallback_dedup END
            select distinct dr.invoice, tk.id, tk.tbl_id, tk.nominal
            into #dj_pph_fallback_dedup
            from #dj_pph_tokens tk
            join #docref_helper dr on dr.no_sj = tk.token
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#dj_pph_fallback_matched') IS NOT NULL BEGIN DROP TABLE #dj_pph_fallback_matched END
            select invoice, tbl_id, sum(nominal) as pph
            into #dj_pph_fallback_matched
            from #dj_pph_fallback_dedup
            group by invoice, tbl_id
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            /* Fallback tingkat 3: PPh yg invoice DAN keterangan-nya sama2 NULL (kasus BYD/06/26/00208
               -- tidak ada teks apa pun utk dicocokkan). Kalau batch pembayaran (tbl_id/realisasi_
               pembayaran.id) itu cuma punya SATU invoice DOC, aman diatribusikan ke situ (tidak
               ambigu). Batch dgn >1 invoice DOC TIDAK disentuh -- lebih baik residual drpd salah. */
            IF OBJECT_ID('tempdb..#rp_single_doc') IS NOT NULL BEGIN DROP TABLE #rp_single_doc END
            select rp.id as tbl_id, max(rpd.no_bayar) as invoice
            into #rp_single_doc
            from realisasi_pembayaran_det rpd
            join realisasi_pembayaran rp on rpd.id_header = rp.id
            where rpd.transaksi = 'DOC'
            group by rp.id
            having count(distinct rpd.no_bayar) = 1
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#dj_pph_still_unmatched') IS NOT NULL BEGIN DROP TABLE #dj_pph_still_unmatched END
            select dj.id, dj.tbl_id, dj.nominal
            into #dj_pph_still_unmatched
            from #dj_pph_fallback dj
            left join #dj_pph_fallback_dedup fd on fd.id = dj.id
            where fd.id is null
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#dj_pph_single_matched') IS NOT NULL BEGIN DROP TABLE #dj_pph_single_matched END
            select rsd.invoice, un.tbl_id, sum(un.nominal) as pph
            into #dj_pph_single_matched
            from #dj_pph_still_unmatched un
            join #rp_single_doc rsd on rsd.tbl_id = un.tbl_id
            group by rsd.invoice, un.tbl_id
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            /* Tier 4 -- kadang terima_doc.no_sj diisi placeholder '-' (bukan kosong, bukan nomor SJ
               beneran). keterangan det_jurnal-nya jadi PEMBAYARAN DOC - -- token '-' tidak lolos
               filter numerik jadi tidak pernah match di tier 2. Selama invoice pemilik order '-' itu
               & baris det_jurnal '-' itu ada di batch pembayaran (tbl_id) yang sama, aman dicocokkan
               langsung (di-scope per-batch supaya tidak nyilang kalau kelak ada >1 kasus serupa). */
            IF OBJECT_ID('tempdb..#docref_dash') IS NOT NULL BEGIN DROP TABLE #docref_dash END
            select distinct invoice
            into #docref_dash
            from #docref_raw
            where ltrim(rtrim(no_sj)) = '-'
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#docref_dash_batch') IS NOT NULL BEGIN DROP TABLE #docref_dash_batch END
            select dd.invoice, rpd.id_header as tbl_id
            into #docref_dash_batch
            from #docref_dash dd
            join realisasi_pembayaran_det rpd on rpd.no_bayar = dd.invoice and rpd.transaksi = 'DOC'
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#dj_pph_dash_matched') IS NOT NULL BEGIN DROP TABLE #dj_pph_dash_matched END
            select db.invoice, un.tbl_id, sum(un.nominal) as pph
            into #dj_pph_dash_matched
            from #dj_pph_still_unmatched un
            join #docref_dash_batch db on db.tbl_id = un.tbl_id
            join #dj_pph_fallback dj on dj.id = un.id
            cross apply string_split(dj.keterangan, ' ') tok
            where tok.value = '-'
            group by db.invoice, un.tbl_id
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#pph_helper') IS NOT NULL BEGIN DROP TABLE #pph_helper END
            select invoice, tbl_id, sum(pph) as pph
            into #pph_helper
            from (
                select invoice, tbl_id, pph from #dj_pph_direct
                union all
                select invoice, tbl_id, pph from #dj_pph_fallback_matched
                union all
                select invoice, tbl_id, pph from #dj_pph_single_matched
                union all
                select invoice, tbl_id, pph from #dj_pph_dash_matched
            ) x
            group by invoice, tbl_id
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE UNIQUE CLUSTERED INDEX ix_pph_helper ON #pph_helper(invoice, tbl_id)

            IF OBJECT_ID('tempdb..#pembayaran') IS NOT NULL BEGIN DROP TABLE #pembayaran END
            select nomor, tanggal, supplier, total, kode_trans, jenis_trans, jenis_hutang
            into #pembayaran
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
                   GL (bukan hitung ulang dari kpd.total spt sebelumnya) -- jadi utk tanggal itu ke
                   atas, rpd.pph adalah SUMBER PALING BENAR, bukan cuma pengganti. Matching via
                   #pph_helper (parsing teks keterangan/No. SJ) malah rawan tabrakan No. SJ dobel
                   antar-invoice dlm 1 batch pembayaran (kasus BYD/08/26/00170, 14 Ags 2026: No. SJ
                   3201450839 kepakai 2 invoice sekaligus, PPh invoice lain ikut ke-hitung -- laporan
                   sempat tampilkan 330.383 padahal rpd.pph accurate-nya cuma 181.178, pas dgn rumus
                   0,25% x kpd.total). Utk tanggal SEBELUM itu #pph_helper tetap dipakai krn rpd.pph
                   lawas belum bisa diandalkan (banyak NULL/0). */
                select rpd.no_bayar as nomor, rp.tgl_bayar as tanggal, rp.supplier, rpd.pph as total, rp.nomor as kode_trans, 'PPh' as jenis_trans, 'DOC' as jenis_hutang
                from realisasi_pembayaran_det rpd
                left join realisasi_pembayaran rp on rpd.id_header = rp.id
                where rpd.transaksi = 'DOC' and rp.tgl_realisasi >= '2026-08-01' and isnull(rpd.pph, 0) <> 0

                union all

                select rpd.no_bayar as nomor, rp.tgl_bayar as tanggal, rp.supplier, ph.pph as total, rp.nomor as kode_trans, 'PPh' as jenis_trans, 'DOC' as jenis_hutang
                from realisasi_pembayaran_det rpd
                left join realisasi_pembayaran rp on rpd.id_header = rp.id
                inner join #pph_helper ph on ph.invoice = rpd.no_bayar and ph.tbl_id = cast(rp.id as varchar)
                where rpd.transaksi = 'DOC' and rp.tgl_realisasi < '2026-08-01'

                union all

                select rpd.no_bayar as nomor, rp.tgl_bayar as tanggal, rp.supplier, rpd.pembulatan as total, rp.nomor as kode_trans, 'Pembulatan' as jenis_trans, 'DOC' as jenis_hutang
                from realisasi_pembayaran_det rpd
                left join realisasi_pembayaran rp on rpd.id_header = rp.id
                where rpd.transaksi = 'DOC' and isnull(rpd.pembulatan, 0) <> 0

                union all

                /* PAKAN -- tidak ada leg PPh (rpd.pph selalu NULL/0 utk PAKAN, konfirmasi user). */
                select rpd.no_bayar as nomor, rp.tgl_bayar as tanggal, rp.supplier, (rpd.transfer+rpd.potongan+rpd.uang_muka+rpd.cn+rpd.dn) as total, rp.nomor as kode_trans, 'Realisasi Pembayaran' as jenis_trans, 'PAKAN' as jenis_hutang
                from realisasi_pembayaran_det rpd
                left join realisasi_pembayaran rp on rpd.id_header = rp.id
                where rpd.transaksi = 'PAKAN'

                union all

                /* jenis_cn utk pelunasan PAKAN di cn_post = 'PKN', BUKAN 'PAKAN'. */
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

                /* OVK -- transaksi di realisasi_pembayaran_det pakai kode 'VOADIP', bukan 'OVK'.
                   Tidak ada leg PPh (sama spt PAKAN, rpd.pph selalu NULL/0 utk VOADIP). Tidak ada
                   leg CN sama sekali (cn_post.jenis_cn cuma ada 'DOC'/'PKN', tidak ada utk OVK di
                   data ini) -- tapi ADA leg DN (dn_post.jenis_dn cuma ada 'OVK', kebalikan dari
                   PAKAN/DOC yg sama sekali tidak pakai DN). */
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

                /* Pelunasan lewat memo yg mendebit 21180.300 (kurangi hutang OVK) -- pola sama dgn
                   Pelunasan Memorial (Persediaan Pakan). Dipakai utk koreksi duplikasi konfirmasi
                   (kasus SJ J851125000017 & SSK251141-0480, unit TAG, Nov 2025, dikoreksi via memo
                   MM2607310039/40, no_invoice diisi ke invoice DUPLIKAT-nya spy nyambung ke sini).
                   coa_asal tidak dibatasi (sama spt PAKAN, jaga2 varian akun lawan lain nanti).

                   CATATAN (2026-09-03): beberapa memo koreksi 'Selisih Revisi Harga OVK'/'Selisih
                   Konfirmasi vs Pembayaran OVK' pernah ditemukan REDUNDAN dgn kpv.total yg sudah
                   direvisi duluan (lihat [[selisih-122-ovk-nonorp-v2]]) -- sengaja TIDAK dikecualikan
                   di query (no hardcode by no_mm/invoice, atas permintaan user 2026-09-03). Kalau
                   ditemukan kasus serupa, perbaiki NILAI baris mmitem-nya langsung di data (pola
                   BYV/12/25/00080: turunkan nilai baris 'selisih' yg redundan ke selisih riilnya),
                   BUKAN tambal query ini.

                   MM2606130002/MM2606190005: riwayat lengkap kasus ini ada di
                   [[selisih-122-ovk-nonorp-v2]] (sempat di-whitelist, dilepas lagi, kredit
                   0,68-nya sekarang ke-tag benar via perbaikan branch 'Memorial' di atas).

                   'invoice hantu' SBG TARGET PELUNASAN (2026-09-04) -- selain invoice riil
                   (#konfir_helper_ovk), No. Invoice di sini boleh JUGA menunjuk ke nomor 'invoice
                   hantu' (#invoice_hantu_ovk, dari cabang 'Memorial'/'Memorial reversed') -- supaya
                   user bisa benar2 'melunasi' invoice hantu spt invoice biasa (kasus nyata: entri
                   baru merujuk No. Invoice='MM2606130002' utk melunasi invoice hantu itu). Tanpa
                   ini, baris begini gagal tercocok kemana pun (kh_memo_ovk gagal krn bukan invoice
                   riil) & JADI HILANG dari laporan sama sekali walau sudah real posting GL. */
                select mi.no_invoice as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, mi.no_mm as kode_trans, 'Pelunasan Memorial (Persediaan OVK)' as jenis_trans, (case when mi.coa_tujuan = '21180.300' then 'OVK ORP' else 'OVK NON ORP' end) as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                left hash join #konfir_helper_ovk kh_memo_ovk on kh_memo_ovk.nomor = mi.no_invoice
                left hash join #invoice_hantu_ovk ih_memo_ovk on ih_memo_ovk.nomor = mi.no_invoice
                where mi.coa_tujuan in ('21180.300', '21174.000') and nullif(mi.no_invoice, '') is not null
                  and (kh_memo_ovk.nomor is not null or ih_memo_ovk.nomor is not null)

                union all

                /* Pelunasan lewat memo yg mendebit 21180.100 (kurangi hutang PAKAN) -- coa_asal
                   (akun lawan/persediaan) SENGAJA TIDAK dibatasi ke akun tertentu, krn sudah ketemu
                   2 varian: 12030.000 'Persediaan Pakan' utk kasus duplikasi konfirmasi (SJ
                   3016262723 dkk, PSR/TAG/LMG Nov 2025, memo MM2512310061/62/63) & 12041.000
                   'Persediaan Pakan Di Kandang' utk kasus selisih harga/update (BYP/12/25/00566
                   KDR, memo MM2512310071) -- kemungkinan varian lain akan muncul lagi. Safety net
                   cukup dari sisi HUTANG-nya sendiri: no_invoice HARUS diisi manual & invoice-nya
                   HARUS sudah ada konfirmasi (kh_memo_pakan.nomor is not null) -- otomatis terpisah
                   dari branch 'Reklasifikasi Hutang DOC ke Pakan' di atas yg justru mensyaratkan
                   no_invoice KOSONG, jadi tidak akan pernah tumpang tindih.

                   DEDUP (row_number partition by no_invoice+nilai, ambil no_mm PALING AWAL): kasus
                   nyata BYP/12/25/01071 (LMG) dikoreksi 1.600.000 oleh DUA memo terpisah tanpa
                   sengaja (MM2512310063 & MM2512310070, sama2 31 Des 2025) -- periode sudah tutup
                   jadi mmitem-nya TIDAK BISA dihapus/diubah, koreksinya cuma bisa di level laporan
                   ini. Kalau kelak ada kasus SAH 2 koreksi beda alasan yg kebetulan invoice+nilai-nya
                   sama persis, ini akan salah exclude satu -- risiko diterima krn belum pernah
                   ditemukan kasus spt itu, dan efeknya immaterial (nilai kecil, invoice tunggal). */
                select nomor, tanggal, supplier, total, kode_trans, jenis_trans, jenis_hutang
                from (
                    select mi.no_invoice as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, mi.no_mm as kode_trans, 'Pelunasan Memorial (Persediaan Pakan)' as jenis_trans, 'PAKAN' as jenis_hutang,
                        row_number() over (partition by mi.no_invoice, mi.nilai order by mi.no_mm asc) as rn_dedup
                    from mmitem mi
                    left join mm m on mi.no_mm = m.no_mm
                    left hash join #konfir_helper_pakan kh_memo_pakan on kh_memo_pakan.nomor = mi.no_invoice
                    where mi.coa_tujuan = '21180.100' and nullif(mi.no_invoice, '') is not null and kh_memo_pakan.nomor is not null
                ) dedup_pelunasan_pakan
                where rn_dedup = 1

                union all

                /* Reklasifikasi hutang DOC -> PAKAN (coa_asal=21180.200 -> coa_tujuan=21180.100) --
                   dipakai saat CN PAKAN salah dijurnal ke akun hutang DOC (kasus CN/PKN/25/10/001 &
                   /002, MM2602280004/05, Feb 2026), jadi hutang DOC dikembalikan naik & hutang PAKAN
                   diturunkan. Tidak terhubung ke 1 invoice tertentu (murni koreksi antar-akun, no_sj/
                   invoice-nya sendiri sudah lama lunas normal) -- SELF-CANCELLING lewat #konfir_helper
                   -nya sendiri, muncul sbg pelunasan atas 'invoice hantu' bernomor no_mm-nya sendiri yg
                   SUDAH dibuat #sumber_hutang Memorial branch PAKAN di atas (coa_tujuan=21180.100),
                   supaya bersih di 0 tanpa dipaksakan nempel ke invoice yg salah. */
                select mi.no_mm as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, mi.no_mm as kode_trans, 'Reklasifikasi Hutang DOC ke Pakan' as jenis_trans, 'PAKAN' as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                where mi.coa_asal = '21180.200' and mi.coa_tujuan = '21180.100' and nullif(mi.no_invoice, '') is null

                union all

                /* Pembayaran lewat memorial -- mendebit 21180.200 (mengurangi hutang DOC) langsung,
                   di luar alur realisasi_pembayaran biasa. Kecualikan: (1) no_invoice kosong (tidak
                   bisa dikaitkan ke invoice/nomor mana pun), (2) invoice yg SUDAH ada Realisasi
                   Pembayaran -- ketahuan dari kasus BYD/11/25/00332: realisasi sudah ada
                   (106.622.775) TAPI ada juga memo (106.890.000, selisih 267.225 diduga PPh) yg
                   ternyata cuma susulan jurnal GL utk pembayaran yg sama, bukan pembayaran tambahan.
                   Kalau tidak dikecualikan, invoice itu kehitung dibayar dobel (saldo minus salah).
                   CATATAN: no_invoice yg menunjuk ke no_mm memo LAIN (bukan invoice asli) SENGAJA
                   TIDAK dikecualikan -- itu justru pola jurnal-balik-murni yg valid (kasus
                   MM2511300017/MM2512310072: memo A nambah hutang tanpa no_invoice shg jadi
                   invoice hantu bernomor no_mm-nya sendiri di #sumber_hutang, lalu memo B
                   membalikkannya persis via no_invoice=no_mm memo A -- harus tetap dihitung sbg
                   pelunasan invoice hantu itu, bukan dibuang). */
                select mi.no_invoice as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, mi.no_mm as kode_trans, 'Pembayaran Memorial' as jenis_trans, 'DOC' as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                left hash join (select distinct no_bayar from realisasi_pembayaran_det where transaksi = 'DOC') rp_ref on rp_ref.no_bayar = mi.no_invoice
                where mi.coa_asal = '21180.200' and nullif(mi.no_invoice, '') is not null and rp_ref.no_bayar is null

                union all

                /* Pembalik memo -- kasus MM2606300034/35 membalik MM2512310009/07 (koreksi utang
                   breeding via PCZB), TAPI no_invoice-nya KOSONG di kedua sisi (beda dari kasus
                   MM2511300017/MM2512310072 di atas yg masih terhubung via no_invoice). Satu-satunya
                   penanda hubungannya ada di teks keterangan header mm: PEMBALIK ATAS diikuti no_mm
                   asal. Parse teks itu utk dapat no_mm yg dibalik, lalu jadikan itu sbg nomor supaya
                   nyambung ke invoice hantu (no_mm asal) di #sumber_hutang. */
                select rtrim(substring(cast(m.keterangan as varchar(300)), patindex('%PEMBALIK ATAS%', cast(m.keterangan as varchar(300))) + 14, 50)) as nomor,
                    cast(m.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, mi.no_mm as kode_trans, 'Pembalik Memorial' as jenis_trans, 'DOC' as jenis_hutang
                from mm m
                join mmitem mi on mi.no_mm = m.no_mm
                where cast(m.keterangan as varchar(300)) like '%PEMBALIK ATAS%'
                  and mi.coa_asal = '21180.200' and nullif(mi.no_invoice, '') is null

                union all

                /* Pelunasan lewat memo Persediaan DOC (coa_asal=12040.000 -> coa_tujuan=21180.200) --
                   KEBALIKAN arah dari 'Pembayaran Memorial' di atas (yg justru kredit 21180.200,
                   arah nambah hutang). Kasus BYD/09/25/00118: jurnal otomatis terima_doc gagal, jadi
                   dibuat memo koreksi persediaan yg IKUT mengkredit 21180.200 -- tapi krn invoice-nya
                   SUDAH terhitung hutang dari konfirmasi, dan (per arahan user) DOC ini dianggap sudah
                   dilunasi lewat memo ini, maka HANYA dihitung sbg pelunasan kalau invoice-nya sudah
                   ada konfirmasi (kh_memo.nomor is not null) -- kalau BELUM ada konfirmasi, biarkan
                   masuk jalur normal (#sumber_hutang, sbg hutang baru, bukan pelunasan). */
                select mi.no_invoice as nomor, cast(mi.tgl_mm as date) as tanggal, m.no_supplier as supplier, mi.nilai as total, mi.no_mm as kode_trans, 'Pelunasan Memorial (Persediaan DOC)' as jenis_trans, 'DOC' as jenis_hutang
                from mmitem mi
                left join mm m on mi.no_mm = m.no_mm
                left hash join #konfir_helper kh_memo2 on kh_memo2.nomor = mi.no_invoice
                where mi.coa_asal = '12040.000' and mi.coa_tujuan = '21180.200' and nullif(mi.no_invoice, '') is not null and kh_memo2.nomor is not null

                union all

                /* PERALATAN -- Pembayaran dari bayar_peralatan. Nomor dipakai = no_order order-nya
                   (bukan no_faktur) supaya nyambung ke baris debet 'Order Peralatan' di #sumber_hutang
                   utk perhitungan saldo per invoice -- pola sama dgn cara DOC/PAKAN/OVK men-net
                   #data_saldo per nomor. Supplier ambil dari order_peralatan. */
                select bp.no_order as nomor, cast(bp.tgl_realisasi as date) as tanggal, op.supplier, bp.jml_bayar as total, bp.no_faktur as kode_trans, 'Bayar Peralatan' as jenis_trans, 'PERALATAN' as jenis_hutang
                from bayar_peralatan bp
                left join order_peralatan op on op.no_order = bp.no_order
                where bp.tgl_realisasi is not null
            ) x
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE INDEX ix_pembayaran_nomor ON #pembayaran(nomor)

            /* Supplier kanonik per nomor invoice -- beberapa memo koreksi kecil (mis. sisa
               pembulatan) punya supplier kosong. Kalau dibiarkan apa adanya, baris itu bisa
               ke-sort ke grup supplier yg salah di list.php (grouping berbasis data.supplier),
               dan kalau dipakai sbg kunci agregasi malah bikin saldo salah drastis (lihat
               #data_saldo). Semua baris utk 1 nomor HARUS pakai supplier yg sama. */
            IF OBJECT_ID('tempdb..#nomor_supplier') IS NOT NULL BEGIN DROP TABLE #nomor_supplier END
            select nomor, max(supplier) as supplier
            into #nomor_supplier
            from (
                select nomor, nullif(supplier, '') as supplier from #sumber_hutang
                union all
                select nomor, nullif(supplier, '') as supplier from #pembayaran
            ) x
            group by nomor
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE UNIQUE CLUSTERED INDEX ix_nomor_supplier ON #nomor_supplier(nomor)

            /* Tanggal Invoice = tanggal ASAL invoice pertama kali muncul jadi hutang (konfirmasi
               atau memorial, mana yg lebih dulu) -- beda dari kolom Tanggal per baris yg
               menunjukkan tanggal TIAP transaksi (bisa beberapa baris/tanggal utk 1 invoice yg sama). */
            IF OBJECT_ID('tempdb..#nomor_tanggal') IS NOT NULL BEGIN DROP TABLE #nomor_tanggal END
            select nomor, min(tanggal) as tanggal_invoice
            into #nomor_tanggal
            from #sumber_hutang
            group by nomor
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            CREATE UNIQUE CLUSTERED INDEX ix_nomor_tanggal ON #nomor_tanggal(nomor)

            /* PENTING (2026-09-03) -- 'b' (pembayaran sebelum start_date) di-join ke 'h' PAKAI
               jenis_hutang, bukan cuma nomor. Sebelumnya b di-sum tanpa filter tag sama sekali,
               jadi kalau 1 nomor invoice py baris #pembayaran yg (krn alasan struktural lain, mis.
               memo koreksi yg coa_tujuan-nya kebetulan nyentuh akun hutang jenis LAIN) ke-tag beda
               dari jenis_hutang invoice-nya sendiri, baris itu TETAP ikut kepotong dari saldo awal
               invoice ybs -- padahal scr akun GL, baris itu belum tentu benar2 mengurangi hutang
               jenis yg sama (kasus nyata: MM2606130002/MM2606190005, coa_asal=21174.000 tapi
               coa_tujuan=21180.300, ke-tag 'OVK ORP' dari coa_tujuan-nya, padahal invoice-nya
               'OVK NON ORP' -- nilainya malah kepotong dobel arah salah, GL vs laporan py selisih
               2x nilainya krn GL anggap ini PENAMBAH 21174.000, laporan anggap PENGURANG invoice
               NON ORP). Filter jenis_hutang di JOIN memastikan 'b' cuma menjumlah baris yg
               tag-nya SAMA dgn tag invoice tsb sendiri -- baris yg tag-nya beda otomatis tidak ikut
               kepotong (spt seharusnya, krn scr definisi bukan bagian dari jenis_hutang invoice
               itu). Lihat [[selisih-122-ovk-nonorp-v2]]. */
            IF OBJECT_ID('tempdb..#data_saldo') IS NOT NULL BEGIN DROP TABLE #data_saldo END
            select
                '".$start_date."' as tanggal,
                ns.supplier,
                'Saldo Awal' as jenis_trans,
                h.nomor as no_inv,
                h.nomor as kode_trans,
                0 as debet,
                0 as kredit,
                (h.total - isnull(b.total, 0)) as saldo,
                1 as urut,
                h.unit,
                h.jenis_hutang,
                nt.tanggal_invoice
            into #data_saldo
            from (
                select nomor, sum(total) as total, max(unit) as unit, max(jenis_hutang) as jenis_hutang
                from #sumber_hutang
                where tanggal < '".$start_date."'
                group by nomor
            ) h
            left join
                (
                    select nomor, jenis_hutang, sum(total) as total
                    from #pembayaran
                    where tanggal < '".$start_date."'
                    group by nomor, jenis_hutang
                ) b
                on h.nomor = b.nomor and h.jenis_hutang = b.jenis_hutang
            left join #nomor_supplier ns on ns.nomor = h.nomor
            left join #nomor_tanggal nt on nt.nomor = h.nomor
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#data_trans') IS NOT NULL BEGIN DROP TABLE #data_trans END
            select
                sh.tanggal,
                ns.supplier,
                sh.jenis_trans,
                sh.nomor as no_inv,
                sh.kode_trans,
                sh.total as debet,
                0 as kredit,
                0 as saldo,
                2 as urut,
                sh.unit,
                sh.jenis_hutang,
                nt.tanggal_invoice
            into #data_trans
            from #sumber_hutang sh
            left join #nomor_supplier ns on ns.nomor = sh.nomor
            left join #nomor_tanggal nt on nt.nomor = sh.nomor
            where sh.tanggal between '".$start_date."' and '".$end_date."'
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            IF OBJECT_ID('tempdb..#data_bayar') IS NOT NULL BEGIN DROP TABLE #data_bayar END
            select
                p.tanggal,
                ns.supplier,
                p.jenis_trans,
                p.nomor as no_inv,
                p.kode_trans,
                0 as debet,
                p.total as kredit,
                0 as saldo,
                2 as urut,
                sh.unit,
                p.jenis_hutang,
                nt.tanggal_invoice
            into #data_bayar
            from #pembayaran p
            left join
                (select nomor, max(unit) as unit from #sumber_hutang group by nomor) sh
                on sh.nomor = p.nomor
            left join #nomor_supplier ns on ns.nomor = p.nomor
            left join #nomor_tanggal nt on nt.nomor = p.nomor
            where p.tanggal between '".$start_date."' and '".$end_date."'
            OPTION (MAXDOP 1, QUERYTRACEON 2312)

            select
                data.*,
                supl.nama as nama_supplier
            from (
                select * from #data_saldo
                union all
                select * from #data_trans
                union all
                select * from #data_bayar
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
            order by
                data.supplier asc,
                data.tanggal_invoice asc,
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
