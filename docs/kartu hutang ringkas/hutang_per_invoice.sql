/* =====================================================================
   HUTANG per INVOICE (gross / nilai tagihan)
   Sumber: semua konfirmasi_pembayaran_* + order_peralatan
   Kolom : no_invoice | supplier | jenis_hutang | total_hutang
   Catatan: ini NILAI HUTANG (tagihan), belum dikurangi pembayaran/CN.
   ===================================================================== */
SET NOCOUNT ON;

/* DOC */
SELECT kpd.nomor AS no_invoice, kpd.supplier, 'DOC' AS jenis_hutang,
       SUM(kpdd.total) AS total_hutang
FROM konfirmasi_pembayaran_doc kpd
JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header = kpd.id
GROUP BY kpd.nomor, kpd.supplier

UNION ALL

/* PAKAN */
SELECT kpp.nomor, kpp.supplier, 'PAKAN',
       SUM(kppd.total)
FROM konfirmasi_pembayaran_pakan kpp
JOIN konfirmasi_pembayaran_pakan_det kppd ON kppd.id_header = kpp.id
GROUP BY kpp.nomor, kpp.supplier

UNION ALL

/* OVK (VOADIP) */
SELECT kpv.nomor, kpv.supplier, 'OVK',
       kpv.total
FROM konfirmasi_pembayaran_voadip kpv

UNION ALL

/* OA PAKAN (ekspedisi) */
SELECT kpop.nomor, kpop.ekspedisi_id AS supplier, 'OA PAKAN',
       kpop.total
FROM konfirmasi_pembayaran_oa_pakan kpop

UNION ALL

/* RHPP / PLASMA (peternak) */
SELECT ISNULL(NULLIF(kpt.invoice,''), kpt.nomor), kpt.mitra AS supplier, 'RHPP',
       kpt.total
FROM konfirmasi_pembayaran_peternak kpt

UNION ALL

/* PERALATAN */
SELECT op.no_order, op.supplier, 'PERALATAN',
       op.total
FROM order_peralatan op

ORDER BY jenis_hutang, no_invoice;
