SET NOCOUNT ON;
PRINT '=== realisasi transfer ~4.375.678 (PLASMA) ===';
SELECT rp.nomor, rpd.no_bayar, CAST(rpd.transfer AS decimal(18,2)) transfer, rp.tgl_realisasi, kpp.invoice
FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id LEFT JOIN konfirmasi_pembayaran_peternak kpp ON kpp.nomor=rpd.no_bayar
WHERE rpd.transaksi='PLASMA' AND rpd.transfer BETWEEN 4370000 AND 4381000;
PRINT '=== mmitem nilai ~4.375.678 (semua coa) ===';
SELECT no_mm, coa_asal, coa_tujuan, CAST(nilai AS decimal(18,2)) nilai, unit, no_invoice, keterangan FROM mmitem WHERE nilai BETWEEN 4370000 AND 4381000;
PRINT '=== kmitem nilai ~4.375.678 ===';
SELECT no_km, coa_asal, coa_tujuan, CAST(nilai AS decimal(18,2)) nilai, no_invoice, keterangan FROM kmitem WHERE nilai BETWEEN 4370000 AND 4381000;
PRINT '=== MLG: selisih realisasi (konfir-invoice) vs konfir per konfir, cari yg under-pay/over-pay kecil yg jumlahnya 4,37jt ===';
SELECT kpp.nomor, kpp.invoice, CAST(kpp.total AS decimal(18,2)) konfir, CAST(ISNULL(rr.bayar,0) AS decimal(18,2)) bayar, CAST(ISNULL(rr.bayar,0)-kpp.total AS decimal(18,2)) selisih
FROM konfirmasi_pembayaran_peternak kpp
LEFT JOIN (SELECT no_bayar, SUM(transfer) bayar FROM realisasi_pembayaran_det WHERE transaksi='PLASMA' GROUP BY no_bayar) rr ON rr.no_bayar=kpp.nomor
WHERE SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3)='MLG' AND ABS(ISNULL(rr.bayar,0)-kpp.total) BETWEEN 0.01 AND 90000000
ORDER BY ABS(ISNULL(rr.bayar,0)-kpp.total) DESC;
