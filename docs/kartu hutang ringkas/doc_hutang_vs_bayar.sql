/* =====================================================================
   DOC: HUTANG vs PEMBAYARAN (realisasi + CN) vs JURNAL GL
   ---------------------------------------------------------------------
   Per invoice DOC (nomor BYD). Membandingkan 3 sisi:
     (1) total_hutang  : konfirmasi_pembayaran_doc_det (gross).
     (2) PEMBAYARAN operasional = transfer + CN + PPh:
           bayar_transfer : realisasi_pembayaran_det.transfer (transaksi=DOC)
           bayar_cn       : cn_post_det.pakai (per nomor)
           bayar_pph      : PPh DOC (0,25%): basis tanggal:
                              tgl_bayar<=2025-09-20 -> 0 (sdh termasuk transfer)
                              tgl_bayar>=2026-01-01 -> (total-cn)*0.25%
                              selainnya             -> total*0.25%
           total_bayar    = transfer + cn + pph
     (3) GL turun (jurnal bayar) : det_jurnal coa_tujuan=21180.200 utk invoice ini
                                   (via kolom invoice=BYD / no_bbm, + keterangan no_sj).
   Kolom hasil:
     sisa_hutang   = total_hutang - total_bayar   (outstanding operasional; 0=lunas)
     selisih_bayar = total_bayar  - gl_turun       (operasional vs GL bayar;
                     <0 = GL catat bayar LEBIH besar, mis. error transfer 498,75)
   ===================================================================== */
SET NOCOUNT ON;

WITH konf AS (
    SELECT kpd.nomor AS no_invoice, MIN(kpdd.kode_unit) AS unit,
           CONVERT(varchar(10), kpd.tgl_bayar, 23) AS tgl, kpd.tgl_bayar,
           SUM(kpdd.total) AS total_hutang
    FROM konfirmasi_pembayaran_doc kpd
    JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header = kpd.id
    GROUP BY kpd.nomor, kpd.tgl_bayar
),
ref AS (  -- BYD -> no_bbm / no_sj (terima_doc terbaru)
    SELECT DISTINCT kpd.nomor AS no_invoice, td.no_bbm, td.no_sj
    FROM konfirmasi_pembayaran_doc kpd
    JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header = kpd.id
    LEFT JOIN (SELECT t.no_order, t.no_bbm, t.no_sj FROM terima_doc t
               JOIN (SELECT no_order, MAX(id) id FROM terima_doc GROUP BY no_order) m ON m.id=t.id) td
      ON td.no_order = kpdd.no_order
),
trf AS (  -- transfer realisasi per invoice (DOC, sudah terealisasi)
    SELECT rpd.no_bayar AS no_invoice, SUM(rpd.transfer) AS bayar_transfer
    FROM realisasi_pembayaran_det rpd
    JOIN realisasi_pembayaran rp ON rpd.id_header = rp.id
    WHERE rpd.transaksi='DOC' AND rp.tgl_realisasi IS NOT NULL AND rp.tgl_realisasi<='2026-06-13'
    GROUP BY rpd.no_bayar
),
cn AS (  -- CN dipakai per invoice
    SELECT nomor AS no_invoice, SUM(pakai) AS bayar_cn
    FROM cn_post_det GROUP BY nomor
),
glturun AS (  -- GL bayar (turun 21180.200) per invoice, DIPISAH sumber: realisasi vs memorial
    SELECT no_invoice,
           SUM(CASE WHEN src='REAL' THEN nominal ELSE 0 END) AS gl_realisasi,
           SUM(CASE WHEN src='MM'   THEN nominal ELSE 0 END) AS gl_memorial,
           SUM(nominal) AS gl_turun
    FROM (
        SELECT dj.id, dj.nominal, MIN(r.no_invoice) AS no_invoice,
               MIN(CASE WHEN dj.kode_trans LIKE 'MM%' THEN 'MM' ELSE 'REAL' END) AS src
        FROM det_jurnal dj
        JOIN ref r
          ON  dj.invoice = r.no_invoice
           OR dj.invoice = r.no_bbm
           OR ( (dj.invoice IS NULL OR dj.invoice='')   -- no_sj hanya bila kolom invoice kosong
                AND r.no_sj IS NOT NULL AND r.no_sj<>''
                AND CAST(dj.keterangan AS varchar(300)) LIKE '%'+r.no_sj+'%' )
        WHERE dj.coa_tujuan='21180.200' AND dj.tanggal<='2026-06-13'
        GROUP BY dj.id, dj.nominal
    ) z GROUP BY no_invoice
)
SELECT
    k.unit, k.tgl, k.no_invoice,
    CAST(k.total_hutang AS decimal(18,2))            AS total_hutang,
    CAST(ISNULL(t.bayar_transfer,0) AS decimal(18,2)) AS bayar_transfer,
    CAST(ISNULL(c.bayar_cn,0) AS decimal(18,2))       AS bayar_cn,
    CAST(CASE WHEN t.bayar_transfer IS NULL THEN 0
              WHEN k.tgl_bayar <= '2025-09-20' THEN 0
              WHEN k.tgl_bayar >= '2026-01-01' THEN (k.total_hutang - ISNULL(c.bayar_cn,0))*0.0025
              ELSE k.total_hutang*0.0025 END AS decimal(18,2)) AS bayar_pph,
    CAST(ISNULL(t.bayar_transfer,0) + ISNULL(c.bayar_cn,0)
         + CASE WHEN t.bayar_transfer IS NULL THEN 0
                WHEN k.tgl_bayar <= '2025-09-20' THEN 0
                WHEN k.tgl_bayar >= '2026-01-01' THEN (k.total_hutang - ISNULL(c.bayar_cn,0))*0.0025
                ELSE k.total_hutang*0.0025 END AS decimal(18,2)) AS total_bayar,
    CAST(k.total_hutang - (ISNULL(t.bayar_transfer,0) + ISNULL(c.bayar_cn,0)
         + CASE WHEN t.bayar_transfer IS NULL THEN 0
                WHEN k.tgl_bayar <= '2025-09-20' THEN 0
                WHEN k.tgl_bayar >= '2026-01-01' THEN (k.total_hutang - ISNULL(c.bayar_cn,0))*0.0025
                ELSE k.total_hutang*0.0025 END) AS decimal(18,2)) AS sisa_hutang,
    /* ---- GL (det_jurnal turun 21180.200), dipisah sumber ---- */
    CAST(ISNULL(g.gl_realisasi,0) AS decimal(18,2))   AS gl_realisasi,   -- BYR
    CAST(ISNULL(g.gl_memorial,0) AS decimal(18,2))    AS gl_memorial,    -- MM (CN/koreksi)
    CAST(ISNULL(g.gl_turun,0) AS decimal(18,2))       AS gl_turun_total,
    /* ---- selisih operasional vs GL ---- */
    -- (transfer+pph) operasional vs GL realisasi (BYR):
    CAST( (ISNULL(t.bayar_transfer,0) + bp.pph) - ISNULL(g.gl_realisasi,0) AS decimal(18,2)) AS selisih_realisasi,
    -- CN operasional vs GL memorial:
    CAST( ISNULL(c.bayar_cn,0) - ISNULL(g.gl_memorial,0) AS decimal(18,2)) AS selisih_cn,
    -- total bayar operasional vs total GL turun:
    CAST( (ISNULL(t.bayar_transfer,0) + ISNULL(c.bayar_cn,0) + bp.pph) - ISNULL(g.gl_turun,0) AS decimal(18,2)) AS selisih_bayar
FROM konf k
LEFT JOIN trf t ON t.no_invoice = k.no_invoice
LEFT JOIN cn  c ON c.no_invoice = k.no_invoice
LEFT JOIN glturun g ON g.no_invoice = k.no_invoice
CROSS APPLY (SELECT CASE WHEN t.bayar_transfer IS NULL THEN 0
                         WHEN k.tgl_bayar <= '2025-09-20' THEN 0
                         WHEN k.tgl_bayar >= '2026-01-01' THEN (k.total_hutang - ISNULL(c.bayar_cn,0))*0.0025
                         ELSE k.total_hutang*0.0025 END AS pph) bp
ORDER BY k.unit, k.tgl, k.no_invoice;
