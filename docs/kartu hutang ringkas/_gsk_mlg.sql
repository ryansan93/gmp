SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
PRINT '=== GSK BYO/01/26/00105: detail GL turun (kenapa ada turun tanpa realisasi) ===';
SELECT dj.tanggal, dj.coa_asal, dj.coa_tujuan, dj.tbl_name, dj.kode_trans, CAST(dj.nominal AS decimal(18,2)) nominal, CAST(CAST(dj.keterangan AS varchar(90)) AS varchar(90)) ket
FROM det_jurnal dj WHERE dj.invoice='BYO/01/26/00105' AND dj.coa_tujuan='21212.000' ORDER BY dj.tanggal;
PRINT '=== GSK BYO/01/26/00105: konfirmasi & realisasi terkait ===';
SELECT 'konfir' src, kpop.nomor, kpop.tgl_bayar, CAST(kpop.total+kpop.potongan_pph_23 AS decimal(18,2)) bruto FROM konfirmasi_pembayaran_oa_pakan kpop WHERE kpop.nomor='BYO/01/26/00105'
UNION ALL
SELECT 'realisasi', rpd.no_bayar, rp.tgl_realisasi, CAST(rpd.transfer AS decimal(18,2)) FROM realisasi_pembayaran_det rpd LEFT JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.no_bayar='BYO/01/26/00105';
PRINT '';
PRINT '=== saldo_bulanan 21212 per unit (cek apakah = saldo awal GL report) ===';
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='saldo_bulanan' ORDER BY ORDINAL_POSITION;
