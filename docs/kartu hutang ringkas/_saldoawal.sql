SET NOCOUNT ON;
DECLARE @start date='2026-06-01';  -- saldo awal Juni = posisi s/d 31 Mei
-- DEBET DOC versi REPORT (basis td.datang < start, max terima_doc per no_order)
DECLARE @rep_debet decimal(18,2);
SELECT @rep_debet = SUM(kpdd.total)
FROM konfirmasi_pembayaran_doc_det kpdd
LEFT JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
LEFT JOIN (SELECT td1.* FROM terima_doc td1 RIGHT JOIN (SELECT MAX(id) id, no_order FROM terima_doc GROUP BY no_order) td2 ON td1.id=td2.id) td ON td.no_order=kpdd.no_order
WHERE CAST(td.datang AS date) < @start;
PRINT 'REPORT debet DOC (datang basis, max td)  : '+ISNULL(CAST(@rep_debet AS varchar),'NULL');

-- DEBET DOC basis tgl_bayar < start (alternatif)
DECLARE @bay_debet decimal(18,2);
SELECT @bay_debet = SUM(kpdd.total)
FROM konfirmasi_pembayaran_doc_det kpdd
LEFT JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
WHERE kpd.tgl_bayar < @start;
PRINT 'KONFIR debet DOC (tgl_bayar basis)       : '+ISNULL(CAST(@bay_debet AS varchar),'NULL');

-- GL naik DOC s/d 31 Mei (coa_asal 21180.200)
DECLARE @gl_naik decimal(18,2);
SELECT @gl_naik = SUM(nominal) FROM det_jurnal WHERE coa_asal='21180.200' AND tanggal<@start;
PRINT 'GL naik DOC (21180.200 kredit) s/d 31 Mei: '+ISNULL(CAST(@gl_naik AS varchar),'NULL');

-- GL turun DOC s/d 31 Mei
DECLARE @gl_turun decimal(18,2);
SELECT @gl_turun = SUM(nominal) FROM det_jurnal WHERE coa_tujuan='21180.200' AND tanggal<@start;
PRINT 'GL turun DOC s/d 31 Mei                  : '+ISNULL(CAST(@gl_turun AS varchar),'NULL');
PRINT '----';
PRINT 'GL saldo awal DOC (naik-turun)           : '+CAST(@gl_naik-@gl_turun AS varchar);
PRINT 'Selisih debet (report - GL naik)         : '+CAST(@rep_debet-@gl_naik AS varchar);
