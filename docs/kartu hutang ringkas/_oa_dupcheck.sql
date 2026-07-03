SET NOCOUNT ON;
PRINT '=== max rpd per no_bayar utk OA PAKAN (harus 1 agar join pph tak dobel) ===';
SELECT TOP 5 rpd.no_bayar, COUNT(*) n FROM realisasi_pembayaran_det rpd WHERE rpd.transaksi='OA PAKAN' GROUP BY rpd.no_bayar HAVING COUNT(*)>1 ORDER BY COUNT(*) DESC;
PRINT '(kosong di atas = aman)';
PRINT '=== simulasi kredit BARU saldo-awal (<Juni) = transfer + pph via join ===';
DECLARE @start date='2026-06-01';
SELECT CAST(SUM(rpd.transfer + isnull(kpop.potongan_pph_23,0)) AS decimal(18,2)) kredit_baru,
       CAST(SUM(rpd.transfer) AS decimal(18,2)) transfer_saja
FROM realisasi_pembayaran_det rpd
LEFT JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
LEFT JOIN konfirmasi_pembayaran_oa_pakan kpop ON kpop.nomor=rpd.no_bayar
WHERE rpd.transaksi='OA PAKAN' AND rp.tgl_realisasi IS NOT NULL AND rp.tgl_realisasi<@start;
