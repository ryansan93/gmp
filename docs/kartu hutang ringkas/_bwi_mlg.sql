SET NOCOUNT ON;
DECLARE @cut date='2026-05-01';
PRINT '=== BWI: konfir <cut yg realisasi-nya TIDAK <cut (debet ada, kredit blm) ===';
SELECT kpp.nomor, kpp.invoice, kpp.tgl_bayar, CAST(kpp.total AS decimal(18,2)) total,
  rp.tgl_realisasi, CAST(rpd.transfer AS decimal(18,2)) transfer
FROM konfirmasi_pembayaran_peternak kpp
LEFT JOIN realisasi_pembayaran_det rpd ON rpd.no_bayar=kpp.nomor AND rpd.transaksi='PLASMA'
LEFT JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
WHERE SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3)='BWI'
  AND kpp.tgl_bayar<@cut AND (rp.tgl_realisasi IS NULL OR rp.tgl_realisasi>=@cut)
ORDER BY kpp.tgl_bayar;
PRINT '';
PRINT '=== MLG: realisasi <cut yg konfir tgl_bayar TIDAK <cut (kredit ada, debet blm) ===';
SELECT rpd.no_bayar, kpp.invoice, kpp.tgl_bayar AS konfir_tgl, rp.tgl_realisasi, CAST(rpd.transfer AS decimal(18,2)) transfer, CAST(kpp.total AS decimal(18,2)) konfir_total
FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
LEFT JOIN konfirmasi_pembayaran_peternak kpp ON kpp.nomor=rpd.no_bayar
WHERE rpd.transaksi='PLASMA' AND rp.tgl_realisasi<@cut
  AND SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3)='MLG'
  AND (kpp.tgl_bayar IS NULL OR kpp.tgl_bayar>=@cut)
ORDER BY rp.tgl_realisasi;
