/* =====================================================================
   HUTANG per INVOICE vs NILAI TERJURNAL vs PEMBAYARAN
   ---------------------------------------------------------------------
   Kolom: tanggal | unit | no_invoice | supplier | jenis_hutang
        | total_hutang | gl_memorial_hutang | jurnal_hutang | selisih
        | bayar_transfer | bayar_cn | bayar_pph | total_bayar | sisa_hutang
        | gl_realisasi | gl_memorial | gl_turun | diff_jurnal_gl_turun
        | selisih_realisasi | selisih_cn | selisih_bayar_gl
   jurnal_hutang        = naik hutang di GL (BBM + memorial koreksi + band-aid pembulatan)
                          -> MENCERMINKAN GL.
   gl_memorial_hutang   = porsi band-aid pembulatan (MM NAIK 21180.200 counter 96010)
                          yg ikut di jurnal_hutang (mis. MM2603310128 = 498,48).
   selisih              = total_hutang - jurnal_hutang. !=0 utk invoice ber-band-aid
                          (mis. -498,48) = GL membukukan beda dari konfirmasi.
   diff_jurnal_gl_turun = jurnal_hutang - gl_turun (saldo hutang versi GL riil per invoice;
                          mis. BYD/11/25/00015 = -0,27).
   BARIS CLEARING per-unit (no_invoice='(KOREKSI/CLEARING DOC)'): menangkap entri
   21180.200 yg TAK ber-invoice (mis. KOREKSI UTANG BREEDING via 27001 zero-balance,
   spt MM2512310007 PSR 1.300 & MM2512310009 GSK 1.015). Ini membuat SUM(diff) DOC
   = saldo GL akun 21180.200 PERSIS (gap teknis Rp ditutup di sini, per unit).
   ---------------------------------------------------------------------
   PERBANDINGAN PEMBAYARAN vs GL (turun 21180.200) -- berlaku utk DOC:
     gl_realisasi   : GL turun via BYR (kode_trans BYR%), cocok kolom invoice=BYD
                      (no_sj hanya bila invoice kosong -> bayar terbaru).
     gl_memorial    : GL turun via MM (memorial/CN).
     gl_turun       : total GL turun (= gl_realisasi + gl_memorial).
     selisih_realisasi = (transfer+pph) - gl_realisasi
        -> != 0 = error pembayaran GL. Pola:
           sub-rupiah/ratusan (mis. -0,50 / -498,75) = pembulatan transfer;
           BESAR (mis. -185jt) = leg bayar SALAH LABEL ke invoice ini (ada
           pasangan invoice lain yg kurang; net antar pasangan = 0).
     selisih_cn        = bayar_cn - gl_memorial
        -> umumnya = nilai CN, karena GL sering NET CN saat booking (BBM=gross-CN)
           bukan jurnal turun -> KLASIFIKASI, bukan error.
     selisih_bayar_gl  = total_bayar - gl_turun.
   (Catatan: kolom GL ini DOC-only; jenis lain gl_*=0.)
   ---------------------------------------------------------------------
   total_hutang   : nilai tagihan dari konfirmasi_pembayaran_* / order_peralatan.
   PEMBAYARAN (dari realisasi_pembayaran_det + cn_post_det):
     bayar_transfer : SUM(realisasi_pembayaran_det.transfer) (terealisasi).
     bayar_cn       : SUM(cn_post_det.pakai) per invoice.
     bayar_pph      : DOC = 0,25% berbasis tanggal (net CN utk >=2026-01-01;
                      0 utk <=2025-09-20); jenis lain = pph tersimpan di realisasi.
     total_bayar    = transfer + cn + pph.
     sisa_hutang    = total_hutang - total_bayar  (outstanding; 0 = lunas).
   jurnal_hutang  : nilai yg di-BOOK ke akun hutang (coa_asal IN coa hutang)
                    di det_jurnal, ditautkan ke invoice lewat tanda terima
                    (tbl_name + tbl_id):
                      DOC  -> terima_doc   (no_order)
                      PAKAN-> terima_pakan (no_sj)
                      OVK  -> terima_voadip (no_sj)
   selisih        : total_hutang - jurnal_hutang
                    > 0 = ada tagihan yg BELUM/ KURANG terjurnal di hutang
                    < 0 = jurnal hutang LEBIH besar dari tagihan
   coa hutang per jenis (sisi NAIK / coa_asal):
     DOC      : 21180.200 + 21173.000(extern)   via tbl_name=terima_doc (tbl_id)
                + KOREKSI MEMORIAL (MM) di 21180.200 (lihat jdoc_mm):
                  cocok via BYD / no_bbm / no_sj (memorial sering isi kolom
                  invoice dgn no_bbm). NAIK = semua disertakan; TURUN = hanya
                  reversal booking (counter persediaan 12040 / reclass hutang),
                  CN(71105)/income(96040/96010)/clearing(27001) dikecualikan.
     PAKAN    : 21180.100 + 21172.000(extern)   via tbl_name=terima_pakan (tbl_id)
     OVK      : 21180.300 + 21174.000(extern)   via tbl_name=terima_voadip (tbl_id)
     EXPEDISI : 21212.000  via terima_pakan + oa_pindah_pakan (no_sj)
     MITRA    : 21213.000/.001  via tbl_name=rhpp/rhpp_group, kode_trans=invoice
   PPh (24622) = sisi BAYAR, bukan booking hutang -> tidak dijumlahkan.

   AKURASI per jenis (validasi total, s/d 2026-06-13):
     DOC      : SETELAH koreksi memorial disertakan -> seluruh selisih 2025
                TUNTAS. Sisa selisih ~2.70B SELURUHNYA di Juni-2026 (receipt
                baru/pipeline yg belum dijurnal). Satu-satunya residu lama:
                BYD/11/25/00015 = -498,48 (band-aid MGT MM2603310128 over 498,48).
     MITRA    : total 224.480B vs jurnal 224.721B -> -241jt (mendekati).
     EXPEDISI : total 22.422B  vs jurnal 20.845B  -> +1.576B (sebagian belum dijurnal).
     OVK      : -13,8jt (mendekati; SJ many-to-many).
     PAKAN    : jurnal > total ~14B. no_sj kirim_pakan many-to-many -> INDIKATIF.
     PERALATAN: jurnal 0 (belum dipetakan).
     LAIN-LAIN: total 7.714B = jurnal 7.714B -> selisih 0. Sumber = memo (mm,
                coa 21201/21211/21299); tak ada konfirmasi, jadi total_hutang
                diambil dari mmitem & jurnal dari det_jurnal (kode_trans=no_mm).

   BELUM DICAKUP: Hutang Expedisi/Mitra yg dibukukan lewat MEMO (mm) -- saat ini
   expedisi/mitra hanya dari jalur normal (BBM/rhpp). PERALATAN belum dipetakan.
   ===================================================================== */
SET NOCOUNT ON;

DECLARE @jenis varchar(20) = 'DOC';   -- FILTER jenis_hutang. Set NULL utk semua jenis.

WITH
hutang AS (
    /* DOC */
    SELECT kpd.nomor AS no_invoice, CONVERT(varchar(10), kpd.tgl_bayar, 23) AS tanggal,
           MIN(kpdd.kode_unit) AS unit,
           kpd.supplier, 'DOC' AS jenis_hutang, SUM(kpdd.total) AS total_hutang
    FROM konfirmasi_pembayaran_doc kpd
    JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header = kpd.id
    GROUP BY kpd.nomor, kpd.tgl_bayar, kpd.supplier
    UNION ALL
    /* PAKAN */
    SELECT kpp.nomor, CONVERT(varchar(10), kpp.tgl_bayar, 23), MIN(kppd.kode_unit), kpp.supplier, 'PAKAN', SUM(kppd.total)
    FROM konfirmasi_pembayaran_pakan kpp
    JOIN konfirmasi_pembayaran_pakan_det kppd ON kppd.id_header = kpp.id
    GROUP BY kpp.nomor, kpp.tgl_bayar, kpp.supplier
    UNION ALL
    /* OVK */
    SELECT kpv.nomor, CONVERT(varchar(10), kpv.tgl_bayar, 23),
           (SELECT MIN(kode_unit) FROM konfirmasi_pembayaran_voadip_det WHERE id_header=kpv.id),
           kpv.supplier, 'OVK', kpv.total
    FROM konfirmasi_pembayaran_voadip kpv
    UNION ALL
    /* OA PAKAN */
    SELECT kpop.nomor, CONVERT(varchar(10), kpop.tgl_bayar, 23), NULL, kpop.ekspedisi_id, 'OA PAKAN', kpop.total
    FROM konfirmasi_pembayaran_oa_pakan kpop
    UNION ALL
    /* RHPP */
    SELECT ISNULL(NULLIF(kpt.invoice,''), kpt.nomor), CONVERT(varchar(10), kpt.tgl_bayar, 23),
           SUBSTRING(REPLACE(REPLACE(kpt.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3),
           kpt.mitra, 'RHPP', kpt.total
    FROM konfirmasi_pembayaran_peternak kpt
    UNION ALL
    /* PERALATAN */
    SELECT op.no_order, CONVERT(varchar(10), op.tgl_order, 23), op.unit, op.supplier, 'PERALATAN', op.total
    FROM order_peralatan op
    UNION ALL
    /* LAIN-LAIN (dari memo mm; tak ada konfirmasi) */
    SELECT mi.no_mm, CONVERT(varchar(10), MIN(mi.tgl_mm), 23), MIN(m.unit), MIN(m.no_supplier), 'LAIN-LAIN', SUM(mi.nilai)
    FROM mmitem mi
    LEFT JOIN mm m ON mi.no_mm = m.no_mm
    WHERE mi.coa_asal IN ('21201.000','21211.000','21299.000')
    GROUP BY mi.no_mm
),
recv_pair AS (  -- pasangan tanda-terima -> invoice (bisa fan-out). 'kelas' memisah jenis.
    /* DOC */
    SELECT 'DOC' kelas, 'terima_doc' AS tbl_name, td.id AS tbl_id, kpd.nomor AS no_invoice
    FROM terima_doc td
    JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.no_order = td.no_order
    JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header = kpd.id
    GROUP BY td.id, kpd.nomor
    UNION ALL
    /* PAKAN */
    SELECT 'PAKAN', 'terima_pakan', tp.id, kpp.nomor
    FROM terima_pakan tp
    JOIN kirim_pakan kp ON kp.id = tp.id_kirim_pakan
    JOIN konfirmasi_pembayaran_pakan_det kppd ON kppd.no_sj = kp.no_sj
    JOIN konfirmasi_pembayaran_pakan kpp ON kppd.id_header = kpp.id
    GROUP BY tp.id, kpp.nomor
    UNION ALL
    /* OVK */
    SELECT 'OVK', 'terima_voadip', tv.id, kpv.nomor
    FROM terima_voadip tv
    JOIN kirim_voadip kv ON kv.id = tv.id_kirim_voadip
    JOIN konfirmasi_pembayaran_voadip_det kpvd ON kpvd.no_sj = kv.no_sj
    JOIN konfirmasi_pembayaran_voadip kpv ON kpvd.id_header = kpv.id
    GROUP BY tv.id, kpv.nomor
    UNION ALL
    /* EXPEDISI - ongkos angkut via terima_pakan */
    SELECT 'EXP', 'terima_pakan', tp.id, kpop.nomor
    FROM terima_pakan tp
    JOIN kirim_pakan kp ON kp.id = tp.id_kirim_pakan
    JOIN konfirmasi_pembayaran_oa_pakan_det kpopd ON kpopd.no_sj = kp.no_sj
    JOIN konfirmasi_pembayaran_oa_pakan kpop ON kpopd.id_header = kpop.id
    GROUP BY tp.id, kpop.nomor
    UNION ALL
    /* EXPEDISI - pindah pakan */
    SELECT 'EXP', 'oa_pindah_pakan', opp.id, kpop.nomor
    FROM oa_pindah_pakan opp
    JOIN konfirmasi_pembayaran_oa_pakan_det kpopd ON kpopd.no_sj = opp.no_sj
    JOIN konfirmasi_pembayaran_oa_pakan kpop ON kpopd.id_header = kpop.id
    GROUP BY opp.id, kpop.nomor
),
recv_map AS (  -- UNIK: 1 invoice per (kelas, tanda-terima)
    SELECT kelas, tbl_name, tbl_id, no_invoice
    FROM (
        SELECT kelas, tbl_name, tbl_id, no_invoice,
               ROW_NUMBER() OVER (PARTITION BY kelas, tbl_name, tbl_id ORDER BY no_invoice) rn
        FROM recv_pair
    ) z WHERE rn = 1
),
jrecv AS (  -- nilai naik hutang per (kelas, tanda terima) sesuai COA-nya
    SELECT kelas, tbl_name, TRY_CONVERT(int, tbl_id) AS tbl_id, SUM(nominal) AS val
    FROM (
        SELECT 'DOC'   kelas, tbl_name, tbl_id, nominal FROM det_jurnal WHERE coa_asal IN ('21180.200','21173.000') AND tbl_name='terima_doc'    AND tanggal<='2026-06-13'
        UNION ALL
        SELECT 'PAKAN', tbl_name, tbl_id, nominal FROM det_jurnal WHERE coa_asal IN ('21180.100','21172.000') AND tbl_name='terima_pakan'  AND tanggal<='2026-06-13'
        UNION ALL
        SELECT 'OVK',   tbl_name, tbl_id, nominal FROM det_jurnal WHERE coa_asal IN ('21180.300','21174.000') AND tbl_name='terima_voadip' AND tanggal<='2026-06-13'
        UNION ALL
        SELECT 'EXP',   tbl_name, tbl_id, nominal FROM det_jurnal WHERE coa_asal='21212.000' AND tbl_name IN ('terima_pakan','oa_pindah_pakan') AND tanggal<='2026-06-13'
    ) dj
    GROUP BY kelas, tbl_name, TRY_CONVERT(int, tbl_id)
),
jmitra AS (  -- MITRA (RHPP): kode_trans = nomor invoice INV/RHPP/...
    SELECT dj.kode_trans AS no_invoice, SUM(dj.nominal) AS jurnal_hutang
    FROM det_jurnal dj
    WHERE dj.coa_asal IN ('21213.000','21213.001')
      AND dj.tbl_name IN ('rhpp','rhpp_group')
      AND dj.tanggal<='2026-06-13'
    GROUP BY dj.kode_trans
),
jlain AS (  -- LAIN-LAIN: naik 21201/21211/21299 via memo, kode_trans = no_mm
    SELECT dj.kode_trans AS no_invoice, SUM(dj.nominal) AS jurnal_hutang
    FROM det_jurnal dj
    WHERE dj.coa_asal IN ('21201.000','21211.000','21299.000')
      AND dj.kode_trans LIKE 'MM%'
      AND dj.tanggal<='2026-06-13'
    GROUP BY dj.kode_trans
),
docref AS (  -- DOC: BYD -> no_bbm & no_sj (utk cocokkan koreksi memorial)
    SELECT DISTINCT kpd.nomor AS no_invoice, td.no_bbm, td.no_sj
    FROM konfirmasi_pembayaran_doc kpd
    JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header = kpd.id
    LEFT JOIN (
        SELECT t.no_order, t.no_bbm, t.no_sj
        FROM terima_doc t
        JOIN (SELECT no_order, MAX(id) id FROM terima_doc GROUP BY no_order) m ON m.id = t.id
    ) td ON td.no_order = kpdd.no_order
),
jdoc_mm AS (  -- DOC: net memorial (MM) di 21180.200 (naik - turun), 1 baris MM -> 1 BYD.
    -- Aturan ASIMETRIS koreksi pembukuan hutang:
    --   NAIK  (coa_asal=21180.200)  : disertakan KECUALI counter 96010 (PEMBULATAN).
    --          Band-aid pembulatan (mis. MM2603310128 BYD/11/25/00015) bukan
    --          pembukuan hutang riil -- itu plug utk menutup kelebihan transfer,
    --          sisi BAYAR. Koreksi nyata spt "KEKURANGAN UTANG DOC" tetap masuk.
    --   TURUN (coa_tujuan=21180.200): HANYA reversal booking (counter = persediaan
    --          12040 / reclass hutang). CN (71105.003), income/pembulatan (96040/
    --          96010), clearing (27001) DIKECUALIKAN -- itu pengurang spt pembayaran.
    SELECT no_invoice,
           SUM(CASE WHEN coa_asal='21180.200' THEN nominal ELSE -nominal END) AS jurnal_hutang
    FROM (
        SELECT dj.id, dj.nominal, dj.coa_asal, MIN(r.no_invoice) AS no_invoice
        FROM det_jurnal dj
        JOIN docref r
          ON  dj.invoice = r.no_invoice
           OR dj.invoice = r.no_bbm
           OR CAST(dj.keterangan AS varchar(300)) LIKE '%'+r.no_invoice+'%'
           OR (r.no_bbm IS NOT NULL AND r.no_bbm<>'' AND CAST(dj.keterangan AS varchar(300)) LIKE '%'+r.no_bbm+'%')
           OR (r.no_sj  IS NOT NULL AND r.no_sj <>'' AND CAST(dj.keterangan AS varchar(300)) LIKE '%'+r.no_sj +'%')
        WHERE dj.kode_trans LIKE 'MM%'
          AND dj.tanggal<='2026-06-13'
          AND ( (dj.coa_asal='21180.200' AND dj.coa_tujuan <> '96010.000')   -- NAIK, kecuali pembulatan
             OR (dj.coa_tujuan='21180.200' AND dj.coa_asal IN ('12040.000','21180.100','21180.300','21174.000','21173.000','21172.000','21171.000')) )
        GROUP BY dj.id, dj.nominal, dj.coa_asal
    ) z
    GROUP BY no_invoice
),
jdoc_pembulatan AS (  -- DOC: PEMBULATAN NAIK 21180.200 (counter 96010), dari SEMUA sumber
    -- (MM band-aid + BYR pembulatan saat bayar). Dimasukkan ke jurnal_hutang agar
    -- mencerminkan GL; ditampilkan terpisah di kolom gl_memorial_hutang. 1 baris -> 1 BYD.
    SELECT no_invoice, SUM(nominal) AS val
    FROM (
        SELECT dj.id, dj.nominal, MIN(r.no_invoice) AS no_invoice
        FROM det_jurnal dj
        JOIN docref r
          ON  dj.invoice = r.no_invoice
           OR dj.invoice = r.no_bbm
           OR (r.no_sj IS NOT NULL AND r.no_sj<>'' AND CAST(dj.keterangan AS varchar(300)) LIKE '%'+r.no_sj+'%')
        WHERE dj.coa_asal='21180.200' AND dj.coa_tujuan='96010.000'   -- semua kode_trans (MM & BYR)
          AND dj.tanggal<='2026-06-13'
        GROUP BY dj.id, dj.nominal
    ) z GROUP BY no_invoice
),
jurnal AS (  -- gabungan naik hutang per invoice (termasuk band-aid pembulatan -> cerminkan GL)
    SELECT no_invoice, SUM(jurnal_hutang) AS jurnal_hutang FROM (
        SELECT r.no_invoice, SUM(jr.val) AS jurnal_hutang
        FROM jrecv jr
        JOIN recv_map r ON r.kelas = jr.kelas AND r.tbl_name = jr.tbl_name AND r.tbl_id = jr.tbl_id
        GROUP BY r.no_invoice
        UNION ALL
        SELECT no_invoice, jurnal_hutang FROM jmitra
        UNION ALL
        SELECT no_invoice, jurnal_hutang FROM jlain
        UNION ALL
        SELECT no_invoice, jurnal_hutang FROM jdoc_mm   -- DOC: koreksi memorial 21180.200
        UNION ALL
        SELECT no_invoice, val FROM jdoc_pembulatan     -- DOC: band-aid pembulatan (96010) -> jurnal cerminkan GL
    ) g GROUP BY no_invoice
),
trf AS (  -- PEMBAYARAN: transfer + pph tersimpan, per invoice (realisasi terealisasi)
    SELECT rpd.no_bayar AS no_invoice,
           SUM(rpd.transfer) AS bayar_transfer,
           SUM(ISNULL(rpd.pph,0)) AS pph_stored
    FROM realisasi_pembayaran_det rpd
    JOIN realisasi_pembayaran rp ON rpd.id_header = rp.id
    WHERE rp.tgl_realisasi IS NOT NULL AND rp.tgl_realisasi <= '2026-06-13'
    GROUP BY rpd.no_bayar
),
cnpay AS (  -- PEMBAYARAN: CN dipakai per invoice (cn_post_det)
    SELECT nomor AS no_invoice, SUM(pakai) AS bayar_cn
    FROM cn_post_det GROUP BY nomor
),
glbayar AS (  -- GL TURUN 21180.200 per invoice (DOC), dipisah realisasi(BYR) vs memorial(MM)
    -- BYR cocok via kolom invoice=BYD; no_sj hanya bila invoice kosong (bayar terbaru).
    SELECT no_invoice,
           SUM(CASE WHEN src='REAL' THEN nominal ELSE 0 END) AS gl_realisasi,
           SUM(CASE WHEN src='MM'   THEN nominal ELSE 0 END) AS gl_memorial,
           SUM(nominal) AS gl_turun
    FROM (
        SELECT dj.id, dj.nominal, MIN(r.no_invoice) AS no_invoice,
               MIN(CASE WHEN dj.kode_trans LIKE 'MM%' THEN 'MM' ELSE 'REAL' END) AS src
        FROM det_jurnal dj
        JOIN docref r
          ON  dj.invoice = r.no_invoice
           OR dj.invoice = r.no_bbm
           OR ( (dj.invoice IS NULL OR dj.invoice='')
                AND r.no_sj IS NOT NULL AND r.no_sj<>''
                AND CAST(dj.keterangan AS varchar(300)) LIKE '%'+r.no_sj+'%' )
        WHERE dj.coa_tujuan='21180.200' AND dj.tanggal<='2026-06-13'
          -- KECUALIKAN memorial reversal-booking (counter persediaan 12040 / reclass hutang):
          -- itu milik sisi JURNAL (sudah di jdoc_mm), bukan PEMBAYARAN. Kalau ikut,
          -- gl_turun jadi dobel (mis. BYD/09/25/00109 reversal BBM ganda).
          AND NOT ( dj.kode_trans LIKE 'MM%'
                    AND dj.coa_asal IN ('12040.000','21180.100','21180.300','21174.000','21173.000','21172.000','21171.000') )
        GROUP BY dj.id, dj.nominal
    ) z GROUP BY no_invoice
),
attrkey AS (  -- semua kunci yg query pakai utk atribusi DOC (BYD / no_bbm / no_sj / terima_doc.id)
    SELECT DISTINCT kpd.nomor AS byd, td.no_bbm, td.no_sj, td.id AS td_id
    FROM konfirmasi_pembayaran_doc kpd
    JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header = kpd.id
    LEFT JOIN (SELECT t.no_order, t.no_bbm, t.no_sj, t.id FROM terima_doc t) td ON td.no_order = kpdd.no_order
),
gl_clearing_unit AS (  -- BUCKET CLEARING per (UNIT, no_mm): entri 21180.200 yg TAK bisa
    -- diatribusikan ke invoice (mis. KOREKSI UTANG BREEDING via 27001 zero-balance).
    -- ref = no invoice bila ada, selainnya no_mm/kode_trans. val = net (naik - turun).
    SELECT unit, ref AS no_mm, CAST(SUM(signed) AS decimal(18,2)) AS val
    FROM (
        SELECT ISNULL(NULLIF(dj.unit_tujuan,''),dj.unit) AS unit,
               ISNULL(NULLIF(dj.invoice,''), dj.kode_trans) AS ref,
               CASE WHEN dj.coa_asal='21180.200' THEN dj.nominal ELSE -dj.nominal END AS signed
        FROM det_jurnal dj
        WHERE (dj.coa_asal='21180.200' OR dj.coa_tujuan='21180.200') AND dj.tanggal<='2026-06-13'
          AND NOT EXISTS (
            SELECT 1 FROM attrkey k WHERE
                 dj.invoice = k.byd
              OR dj.invoice = k.no_bbm
              OR (dj.tbl_name='terima_doc' AND dj.tbl_id = CAST(k.td_id AS varchar))
              OR (k.no_sj IS NOT NULL AND k.no_sj<>'' AND CAST(dj.keterangan AS varchar(300)) LIKE '%'+k.no_sj+'%')
          )
    ) z GROUP BY unit, ref
    HAVING ABS(SUM(signed)) > 0.001
)
SELECT
    h.tanggal,
    h.unit,
    h.no_invoice,
    h.supplier,
    h.jenis_hutang,
    CAST(h.total_hutang AS decimal(18,2))          AS total_hutang,
    CAST(ISNULL(pb.val,0) AS decimal(18,2)) AS gl_memorial_hutang,       -- bagian band-aid pembulatan (MM 96010) di jurnal_hutang
    CAST(ISNULL(j.jurnal_hutang,0) AS decimal(18,2)) AS jurnal_hutang,   -- termasuk band-aid pembulatan (cerminkan GL)
    CAST(h.total_hutang - ISNULL(j.jurnal_hutang,0) AS decimal(18,2)) AS selisih,
    /* ---- PEMBAYARAN (realisasi + cn_post_det) ---- */
    CAST(ISNULL(t.bayar_transfer,0) AS decimal(18,2)) AS bayar_transfer,
    CAST(ISNULL(cp.bayar_cn,0) AS decimal(18,2))      AS bayar_cn,
    CAST(bp.bayar_pph AS decimal(18,2))               AS bayar_pph,
    CAST(ISNULL(t.bayar_transfer,0) + ISNULL(cp.bayar_cn,0) + bp.bayar_pph AS decimal(18,2)) AS total_bayar,
    CAST(h.total_hutang - (ISNULL(t.bayar_transfer,0) + ISNULL(cp.bayar_cn,0) + bp.bayar_pph) AS decimal(18,2)) AS sisa_hutang,
    /* ---- GL turun (det_jurnal 21180.200) vs pembayaran [DOC] ---- */
    CAST(ISNULL(gb.gl_realisasi,0) AS decimal(18,2)) AS gl_realisasi,   -- BYR
    CAST(ISNULL(gb.gl_memorial,0) AS decimal(18,2))  AS gl_memorial,    -- MM (CN/koreksi)
    CAST(ISNULL(gb.gl_turun,0) AS decimal(18,2))     AS gl_turun,       -- = gl_realisasi + gl_memorial
    -- saldo hutang versi GL riil per invoice = jurnal_hutang (sdh termasuk band-aid) - gl_turun:
    CAST(ISNULL(j.jurnal_hutang,0) - ISNULL(gb.gl_turun,0) AS decimal(18,2)) AS diff_jurnal_gl_turun,
    -- (transfer+pph) operasional vs GL realisasi (BYR):
    CAST( (ISNULL(t.bayar_transfer,0) + bp.bayar_pph) - ISNULL(gb.gl_realisasi,0) AS decimal(18,2)) AS selisih_realisasi,
    -- CN operasional vs GL memorial:
    CAST( ISNULL(cp.bayar_cn,0) - ISNULL(gb.gl_memorial,0) AS decimal(18,2)) AS selisih_cn,
    -- total bayar operasional vs total GL turun:
    CAST( (ISNULL(t.bayar_transfer,0) + ISNULL(cp.bayar_cn,0) + bp.bayar_pph) - ISNULL(gb.gl_turun,0) AS decimal(18,2)) AS selisih_bayar_gl
FROM hutang h
LEFT JOIN jurnal j ON j.no_invoice = h.no_invoice
LEFT JOIN trf   t  ON t.no_invoice = h.no_invoice
LEFT JOIN cnpay cp ON cp.no_invoice = h.no_invoice
LEFT JOIN glbayar gb ON gb.no_invoice = h.no_invoice
LEFT JOIN jdoc_pembulatan pb ON pb.no_invoice = h.no_invoice
/* PPh: DOC = 0,25% berbasis tanggal (net CN utk 2026+); jenis lain = pph tersimpan */
CROSS APPLY (
    SELECT CASE
        WHEN h.jenis_hutang='DOC' THEN
            CASE WHEN t.bayar_transfer IS NULL THEN 0
                 WHEN h.tanggal <= '2025-09-20' THEN 0
                 WHEN h.tanggal >= '2026-01-01' THEN (h.total_hutang - ISNULL(cp.bayar_cn,0))*0.0025
                 ELSE h.total_hutang*0.0025 END
        ELSE ISNULL(t.pph_stored,0)
    END AS bayar_pph
) bp
WHERE (@jenis IS NULL OR h.jenis_hutang = @jenis)

UNION ALL
/* ---- BUCKET CLEARING per UNIT (entri 21180.200 tak ber-invoice, mis. koreksi breeding via 27001) ---- */
SELECT
    '(clearing)'                               AS tanggal,
    c.unit,
    c.no_mm                                    AS no_invoice,
    NULL                                       AS supplier,
    'DOC'                                      AS jenis_hutang,
    CAST(0 AS decimal(18,2))                   AS total_hutang,
    CAST(0 AS decimal(18,2))                   AS gl_memorial_hutang,
    CAST(CASE WHEN c.val>0 THEN c.val ELSE 0 END AS decimal(18,2)) AS jurnal_hutang,
    CAST(-(CASE WHEN c.val>0 THEN c.val ELSE 0 END) AS decimal(18,2)) AS selisih,
    CAST(0 AS decimal(18,2)) AS bayar_transfer,
    CAST(0 AS decimal(18,2)) AS bayar_cn,
    CAST(0 AS decimal(18,2)) AS bayar_pph,
    CAST(0 AS decimal(18,2)) AS total_bayar,
    CAST(0 AS decimal(18,2)) AS sisa_hutang,
    CAST(0 AS decimal(18,2)) AS gl_realisasi,
    CAST(CASE WHEN c.val<0 THEN -c.val ELSE 0 END AS decimal(18,2)) AS gl_memorial,
    CAST(CASE WHEN c.val<0 THEN -c.val ELSE 0 END AS decimal(18,2)) AS gl_turun,
    CAST(c.val AS decimal(18,2))               AS diff_jurnal_gl_turun,
    CAST(0 AS decimal(18,2)) AS selisih_realisasi,
    CAST(-(CASE WHEN c.val<0 THEN -c.val ELSE 0 END) AS decimal(18,2)) AS selisih_cn,
    CAST(-(CASE WHEN c.val<0 THEN -c.val ELSE 0 END) AS decimal(18,2)) AS selisih_bayar_gl
FROM gl_clearing_unit c
WHERE (@jenis IS NULL OR @jenis = 'DOC')   -- clearing hanya relevan utk DOC (akun 21180.200)

ORDER BY jenis_hutang, tanggal, no_invoice;
