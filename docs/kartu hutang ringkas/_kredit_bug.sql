SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

PRINT '=== Jumlah det rows per invoice (potensi multiplication di kredit) ===';
SELECT
    COUNT(*) total_det_rows,
    COUNT(DISTINCT kpd.nomor) total_invoices,
    CAST(COUNT(*)*1.0/COUNT(DISTINCT kpd.nomor) AS decimal(10,2)) avg_det_per_inv,
    CAST(SUM(kpdd.total) AS decimal(18,2)) sum_konfir
FROM konfirmasi_pembayaran_doc_det kpdd
JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
WHERE kpd.tgl_bayar < @start;

PRINT '';
PRINT '=== Kredit transfer jika multiply vs seharusnya ===';
-- Seharusnya: SUM transfer dari realisasi (satu row per invoice)
DECLARE @trf_actual decimal(18,2);
SELECT @trf_actual = SUM(rpd.transfer) FROM realisasi_pembayaran_det rpd
JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
WHERE rpd.transaksi='DOC' AND rp.tgl_realisasi < @start;
PRINT 'Transfer aktual (1 row per invoice): '+CAST(@trf_actual AS varchar);

-- Setelah multiply (join ke konfir yang punya n det rows per invoice):
DECLARE @trf_multiply decimal(18,2);
SELECT @trf_multiply = SUM(rpd.transfer) FROM realisasi_pembayaran_det rpd
JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
JOIN (
    SELECT kpd.nomor FROM konfirmasi_pembayaran_doc_det kpdd
    JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
    LEFT JOIN (SELECT td1.* FROM terima_doc td1 RIGHT JOIN (SELECT MAX(id) id,no_order FROM terima_doc GROUP BY no_order) td2 ON td1.id=td2.id) td ON td.no_order=kpdd.no_order
    WHERE kpd.tgl_bayar<@start
    GROUP BY kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar, kpdd.total
) konfir ON konfir.nomor=rpd.no_bayar
WHERE rpd.transaksi='DOC' AND rp.tgl_realisasi<@start;
PRINT 'Transfer setelah join konfir (n det): '+CAST(@trf_multiply AS varchar);
PRINT 'Selisih inflation: '+CAST(@trf_multiply-@trf_actual AS varchar);
