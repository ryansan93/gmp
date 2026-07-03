SET NOCOUNT ON;
DECLARE @cut date='2026-05-01';
PRINT '=== realisasi PLASMA no_bayar DOBEL (>1 baris) <cut, fokus MLG ===';
SELECT rpd.no_bayar, COUNT(*) n, CAST(SUM(rpd.transfer) AS decimal(18,2)) total_transfer
FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
LEFT JOIN konfirmasi_pembayaran_peternak kpp ON kpp.nomor=rpd.no_bayar
WHERE rpd.transaksi='PLASMA' AND rp.tgl_realisasi<@cut AND SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3)='MLG'
GROUP BY rpd.no_bayar HAVING COUNT(*)>1 ORDER BY COUNT(*) DESC;
PRINT '=== detail BYM/03/26/00081 (semua baris realisasi) ===';
SELECT rp.nomor byr, rpd.no_bayar, rp.tgl_realisasi, CAST(rpd.transfer AS decimal(18,2)) transfer, CAST(rpd.bayar AS decimal(18,2)) bayar, CAST(rpd.cn AS decimal(18,2)) cn
FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.no_bayar='BYM/03/26/00081';
PRINT '=== total realisasi MLG <cut: jumlah baris vs jumlah konfir MLG <cut ===';
SELECT 
 (SELECT COUNT(*) FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id LEFT JOIN konfirmasi_pembayaran_peternak kpp ON kpp.nomor=rpd.no_bayar WHERE rpd.transaksi='PLASMA' AND rp.tgl_realisasi<@cut AND SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3)='MLG') n_realisasi,
 (SELECT COUNT(*) FROM konfirmasi_pembayaran_peternak kpp WHERE kpp.tgl_bayar<@cut AND SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3)='MLG') n_konfir;
