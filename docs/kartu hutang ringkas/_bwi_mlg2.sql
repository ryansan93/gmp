SET NOCOUNT ON;
DECLARE @cut date='2026-05-01';
PRINT '=== BWI BYM/01/26/00302: ada di GL? (rhpp generate & turun utk INV/RHPP/G/BWI/26/01/0006) ===';
SELECT dj.tanggal, dj.coa_asal, dj.coa_tujuan, dj.tbl_name, dj.invoice, CAST(dj.nominal AS decimal(18,2)) nominal
FROM det_jurnal dj WHERE (dj.coa_asal='21213.000' OR dj.coa_tujuan='21213.000') AND dj.invoice='INV/RHPP/G/BWI/26/01/0006' ORDER BY dj.tanggal;
PRINT '=== BWI: cek realisasi utk nomor BYM/01/26/00302 (mungkin no_bayar beda) ===';
SELECT rpd.no_bayar, rpd.transaksi, rp.tgl_realisasi, CAST(rpd.transfer AS decimal(18,2)) transfer FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.no_bayar='BYM/01/26/00302';
PRINT '';
PRINT '=== MLG: per BYO <cut, konfir total vs realisasi transfer, yg BEDA ===';
SELECT kpp.nomor, kpp.invoice, kpp.tgl_bayar, CAST(kpp.total AS decimal(18,2)) konfir_total,
  rp.tgl_realisasi, CAST(rpd.transfer AS decimal(18,2)) transfer
FROM konfirmasi_pembayaran_peternak kpp
LEFT JOIN realisasi_pembayaran_det rpd ON rpd.no_bayar=kpp.nomor AND rpd.transaksi='PLASMA'
LEFT JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
WHERE SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3)='MLG'
  AND (kpp.tgl_bayar<@cut OR rp.tgl_realisasi<@cut)
  AND isnull(kpp.total,0) <> isnull(rpd.transfer,0)
ORDER BY abs(isnull(kpp.total,0)-isnull(rpd.transfer,0)) DESC;
