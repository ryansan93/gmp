/* =====================================================================
   DAFTAR TERIMA DOC YANG BELUM / KURANG DIJURNAL NAIK (21180.200)
   ---------------------------------------------------------------------
   Grain : per no_order (1 baris terima DOC = 1 baris konfirmasi DOC).
   total_konfirmasi : nilai hutang DOC dari konfirmasi_pembayaran_doc_det.
   gl_naik          : SUM naik hutang (coa_asal=21180.200) yg tertaut ke
                      terima_doc no_order ini (semua versi, via tbl_id).
   selisih          : total_konfirmasi - gl_naik
                      > 0  : KURANG dijurnal (mis. belum ada BBM sama sekali)
                      < 0  : DOBEL dijurnal
   Pakai sebagai basis cleanup booking bruto (naik+turun harus sepasang).
   ===================================================================== */
SET NOCOUNT ON;

WITH tdlast AS (  -- terima_doc terbaru per no_order (utk no_bbm/no_sj)
    SELECT t.no_order, t.no_bbm, t.no_sj
    FROM terima_doc t
    JOIN (SELECT no_order, MAX(id) id FROM terima_doc GROUP BY no_order) m ON m.id=t.id
),
line AS (
    SELECT kpdd.kode_unit AS unit, kpd.nomor AS no_invoice, kpdd.no_order,
           CONVERT(varchar(10), kpd.tgl_bayar, 23) AS tgl, kpd.supplier,
           MAX(td.no_bbm) AS no_bbm, MAX(td.no_sj) AS no_sj,
           SUM(kpdd.total) AS total_konfirmasi
    FROM konfirmasi_pembayaran_doc kpd
    JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header = kpd.id
    LEFT JOIN tdlast td ON td.no_order = kpdd.no_order
    GROUP BY kpdd.kode_unit, kpd.nomor, kpdd.no_order, kpd.tgl_bayar, kpd.supplier
),
tdids AS (  -- semua id terima_doc per no_order
    SELECT no_order, id FROM terima_doc
),
glnaik AS (  -- naik 21180.200 per no_order via tbl_id terima_doc
    SELECT t.no_order, SUM(dj.nominal) AS gl_naik
    FROM tdids t
    JOIN det_jurnal dj ON dj.tbl_name='terima_doc'
                      AND dj.tbl_id = CAST(t.id AS varchar)
                      AND dj.coa_asal='21180.200'
    GROUP BY t.no_order
),
mm AS (  -- net memorial di 21180.200 (naik - turun), cocok via invoice BYD / no_bbm / no_sj
    SELECT l.no_order,
           SUM(CASE WHEN dj.coa_asal='21180.200' THEN dj.nominal
                    WHEN dj.coa_tujuan='21180.200' THEN -dj.nominal ELSE 0 END) AS mm_net
    FROM (SELECT DISTINCT no_order, no_invoice, no_bbm, no_sj FROM line) l
    JOIN det_jurnal dj
      ON dj.kode_trans LIKE 'MM%'
     AND (dj.coa_asal='21180.200' OR dj.coa_tujuan='21180.200')
     AND ( dj.invoice = l.no_invoice
        OR dj.invoice = l.no_bbm
        OR CAST(dj.keterangan AS varchar(300)) LIKE '%'+l.no_invoice+'%'
        OR (l.no_bbm IS NOT NULL AND CAST(dj.keterangan AS varchar(300)) LIKE '%'+l.no_bbm+'%')
        OR (l.no_sj  IS NOT NULL AND l.no_sj<>'' AND CAST(dj.keterangan AS varchar(300)) LIKE '%'+l.no_sj+'%') )
    GROUP BY l.no_order
)
SELECT l.unit, l.tgl, l.no_invoice, l.no_order, l.supplier,
       CAST(l.total_konfirmasi AS decimal(18,2))      AS total_konfirmasi,
       CAST(ISNULL(g.gl_naik,0) AS decimal(18,2))     AS gl_naik_bbm,
       CAST(ISNULL(m.mm_net,0) AS decimal(18,2))      AS memorial_net,
       CAST(l.total_konfirmasi - ISNULL(g.gl_naik,0) - ISNULL(m.mm_net,0) AS decimal(18,2)) AS sisa_setelah_mm,
       CASE WHEN ABS(l.total_konfirmasi - ISNULL(g.gl_naik,0) - ISNULL(m.mm_net,0)) <= 0.5 THEN 'SUDAH (via memorial)'
            WHEN l.tgl = CONVERT(varchar(10), GETDATE(), 23) THEN 'PENDING (hari ini)'
            WHEN ISNULL(g.gl_naik,0)=0 AND ISNULL(m.mm_net,0)=0 THEN 'BELUM DIJURNAL'
            WHEN l.total_konfirmasi - ISNULL(g.gl_naik,0) - ISNULL(m.mm_net,0) > 0.5 THEN 'MASIH KURANG'
            ELSE 'MASIH DOBEL/LEBIH' END AS status
FROM line l
LEFT JOIN glnaik g ON g.no_order = l.no_order
LEFT JOIN mm m ON m.no_order = l.no_order
WHERE ABS(l.total_konfirmasi - ISNULL(g.gl_naik,0)) > 0.5
ORDER BY l.unit, l.tgl, l.no_invoice;
