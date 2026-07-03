SET NOCOUNT ON;
DECLARE @inv TABLE(nomor varchar(50));
INSERT @inv VALUES ('BYD/09/25/00093'),('BYD/09/25/00106'),('BYD/10/25/00241');

/* realisasi operasional */
SELECT 'OP_realisasi' src, rpd.no_bayar, rpd.transaksi, rpd.transfer
FROM realisasi_pembayaran_det rpd JOIN @inv i ON i.nomor=rpd.no_bayar WHERE rpd.transaksi='DOC';

/* SEMUA baris GL utk transaksi bayar ini (lihat bank, pph, hutang) */
SELECT 'GL_bayar' src, dj.invoice, dj.kode_trans, dj.coa_asal, dj.coa_tujuan, dj.nominal, dj.unit
FROM det_jurnal dj JOIN @inv i ON i.nomor=dj.invoice
WHERE dj.kode_trans IN (SELECT DISTINCT kode_trans FROM det_jurnal d2 JOIN @inv x ON x.nomor=d2.invoice WHERE d2.coa_tujuan='21180.200' AND d2.kode_trans LIKE 'BYR%')
ORDER BY dj.invoice, dj.coa_asal;
