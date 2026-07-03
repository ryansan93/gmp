SET NOCOUNT ON;
-- apakah invoice split dobel ada di konfirmasi_pembayaran_doc?
SELECT 'konfir?' src, nomor, tgl_bayar FROM konfirmasi_pembayaran_doc
WHERE nomor IN ('BYD/11/25/00340','BYD/11/25/00341','BYD/09/25/00118','BYD/10/25/00195');
-- daftar 4 invoice dgn koreksi persediaan + keterangannya
SELECT 'list4' src, no_invoice, nilai, keterangan FROM mmitem
WHERE coa_asal='12040.000' AND coa_tujuan='21180.200' AND no_invoice LIKE 'BYD/%' ORDER BY nilai;
