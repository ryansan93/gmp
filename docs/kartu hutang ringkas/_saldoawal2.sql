SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

-- KREDIT sisi report
SELECT 'trf' lbl, CAST(SUM(rpd.transfer) AS decimal(18,2)) v FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='DOC' AND rp.tgl_realisasi < @start
UNION ALL
SELECT 'cn', CAST(SUM(cpd.pakai) AS decimal(18,2)) FROM cn_post_det cpd JOIN cn_post cp ON cpd.id_header=cp.id WHERE cp.tanggal < @start AND cpd.nomor LIKE 'BYD/%'
UNION ALL
SELECT 'gl_turun', CAST(SUM(nominal) AS decimal(18,2)) FROM det_jurnal WHERE coa_tujuan='21180.200' AND tanggal < @start;

PRINT '';
PRINT '=== Gap debet: invoice tgl_bayar vs datang mismatch ===';
-- datang<Jun tapi tgl_bayar>=Jun => masuk debet report tapi belum dibayar (normal saldo awal ada hutang)
SELECT COUNT(*) baris, CAST(SUM(kpdd.total) AS decimal(18,2)) total, 'datang<Jun, tgl_bayar>=Jun' ket
FROM konfirmasi_pembayaran_doc_det kpdd
LEFT JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
LEFT JOIN (SELECT td1.* FROM terima_doc td1 RIGHT JOIN (SELECT MAX(id) id, no_order FROM terima_doc GROUP BY no_order) td2 ON td1.id=td2.id) td ON td.no_order=kpdd.no_order
WHERE CAST(td.datang AS date) < @start AND kpd.tgl_bayar >= @start
UNION ALL
-- tgl_bayar<Jun tapi datang>=Jun => ada di kredit (bayar) tapi tidak di debet report (MASALAH: kredit tanpa debet)
SELECT COUNT(*), CAST(SUM(kpdd.total) AS decimal(18,2)), 'tgl_bayar<Jun, datang>=Jun (BAYAR TANPA DEBET DI REPORT)'
FROM konfirmasi_pembayaran_doc_det kpdd
LEFT JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
LEFT JOIN (SELECT td1.* FROM terima_doc td1 RIGHT JOIN (SELECT MAX(id) id, no_order FROM terima_doc GROUP BY no_order) td2 ON td1.id=td2.id) td ON td.no_order=kpdd.no_order
WHERE kpd.tgl_bayar < @start AND CAST(td.datang AS date) >= @start
UNION ALL
-- datang IS NULL (no terima_doc sama sekali) tapi tgl_bayar < start
SELECT COUNT(*), CAST(SUM(kpdd.total) AS decimal(18,2)), 'datang NULL (tdk ada terima_doc), tgl_bayar<Jun (TIDAK MASUK DEBET REPORT!)'
FROM konfirmasi_pembayaran_doc_det kpdd
LEFT JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
LEFT JOIN (SELECT td1.* FROM terima_doc td1 RIGHT JOIN (SELECT MAX(id) id, no_order FROM terima_doc GROUP BY no_order) td2 ON td1.id=td2.id) td ON td.no_order=kpdd.no_order
WHERE td.id IS NULL AND kpd.tgl_bayar < @start;
