SET NOCOUNT ON;
SELECT rpd.no_bayar, rpd.transaksi, rpd.tagihan, rpd.transfer, rpd.cn, rpd.potongan, rpd.pph, rpd.dn
FROM realisasi_pembayaran_det rpd WHERE rpd.no_bayar IN
('BYD/10/25/00195','BYD/09/25/00100','BYD/11/25/00341','BYD/11/25/00340','BYD/09/25/00118') AND rpd.transaksi='DOC';
-- cek apakah pph banyak yang NULL/0 (apakah field ini dipakai konsisten utk DOC)
SELECT CASE WHEN pph IS NULL THEN 'NULL' WHEN pph=0 THEN 'ZERO' ELSE 'ISI' END status, COUNT(*) jml
FROM realisasi_pembayaran_det WHERE transaksi='DOC' GROUP BY CASE WHEN pph IS NULL THEN 'NULL' WHEN pph=0 THEN 'ZERO' ELSE 'ISI' END;
