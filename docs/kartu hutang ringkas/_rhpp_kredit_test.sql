SET NOCOUNT ON;
DECLARE @cut date='2026-06-01';
PRINT '=== cek nomor konfir peternak unik? (HAVING>1 = bahaya dobel) ===';
SELECT TOP 3 nomor, COUNT(*) n FROM konfirmasi_pembayaran_peternak GROUP BY nomor HAVING COUNT(*)>1;
PRINT '(kosong = aman)';
PRINT '=== kredit per unit BARU (via join konfir) + total vs transfer asli ===';
SELECT SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3) unit, CAST(SUM(rpd.transfer) AS decimal(18,2)) kredit
FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
LEFT JOIN konfirmasi_pembayaran_peternak kpp ON kpp.nomor=rpd.no_bayar
WHERE rpd.transaksi='PLASMA' AND rp.tgl_realisasi<@cut
GROUP BY SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3) ORDER BY unit;
PRINT '=== total kredit join vs total transfer asli (harus sama) ===';
SELECT CAST(SUM(rpd.transfer) AS decimal(18,2)) total_join FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id LEFT JOIN konfirmasi_pembayaran_peternak kpp ON kpp.nomor=rpd.no_bayar WHERE rpd.transaksi='PLASMA' AND rp.tgl_realisasi<@cut;
