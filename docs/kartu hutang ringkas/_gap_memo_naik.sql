SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

PRINT '=== GL naik via MEMO (21180.200 coa_asal, kode_trans MM) s/d Mei ===';
SELECT dj.kode_trans, dj.invoice, dj.tanggal, CAST(dj.nominal AS decimal(18,2)) nominal, dj.keterangan
FROM det_jurnal dj
WHERE dj.coa_asal='21180.200' AND dj.kode_trans LIKE 'MM%' AND dj.tanggal < @start
ORDER BY dj.tanggal;

PRINT '';
PRINT '=== Selisih komponen: GL naik BBM vs konfirmasi ===';
DECLARE @gl_bbm decimal(18,2), @gl_mm_naik decimal(18,2), @op_konfir decimal(18,2), @op_mm_naik decimal(18,2);
SELECT @gl_bbm = SUM(dj.nominal) FROM det_jurnal dj WHERE dj.coa_asal='21180.200' AND dj.kode_trans NOT LIKE 'MM%' AND dj.tanggal<@start;
SELECT @gl_mm_naik = SUM(dj.nominal) FROM det_jurnal dj WHERE dj.coa_asal='21180.200' AND dj.kode_trans LIKE 'MM%' AND dj.tanggal<@start;
SELECT @op_konfir = SUM(kpdd.total) FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id WHERE kpd.tgl_bayar<@start;
SELECT @op_mm_naik = SUM(mi.nilai) FROM mmitem mi JOIN mm m ON mi.no_mm=m.no_mm WHERE mi.coa_asal='21180.200' AND CAST(mi.tgl_mm AS date)<@start
  AND NOT EXISTS (SELECT 1 FROM (SELECT nomor FROM konfirmasi_pembayaran_doc UNION ALL SELECT nomor FROM konfirmasi_pembayaran_pakan UNION ALL SELECT nomor FROM konfirmasi_pembayaran_voadip UNION ALL SELECT nomor FROM konfirmasi_pembayaran_oa_pakan UNION ALL SELECT ISNULL(NULLIF(invoice,''),nomor) FROM konfirmasi_pembayaran_peternak) kk WHERE kk.nomor=mi.no_invoice);
PRINT 'GL BBM naik (non-MM)    : '+CAST(ISNULL(@gl_bbm,0) AS varchar);
PRINT 'GL MM naik (memo)       : '+CAST(ISNULL(@gl_mm_naik,0) AS varchar);
PRINT 'Op konfirmasi debet     : '+CAST(ISNULL(@op_konfir,0) AS varchar);
PRINT 'Op invoice lewat memo   : '+CAST(ISNULL(@op_mm_naik,0) AS varchar);
PRINT 'GL total naik           : '+CAST(ISNULL(@gl_bbm,0)+ISNULL(@gl_mm_naik,0) AS varchar);
PRINT 'Op total debet          : '+CAST(ISNULL(@op_konfir,0)+ISNULL(@op_mm_naik,0) AS varchar);
PRINT 'Gap                     : '+CAST((ISNULL(@op_konfir,0)+ISNULL(@op_mm_naik,0))-(ISNULL(@gl_bbm,0)+ISNULL(@gl_mm_naik,0)) AS varchar);
