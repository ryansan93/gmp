SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

PRINT '=== Memo NAIK 21180.200 (GL) per unit BJN/JBR/MLG s/d Mei ===';
SELECT dj.kode_trans, dj.invoice, dj.tanggal, CAST(dj.nominal AS decimal(18,2)) nominal, m.unit, dj.keterangan
FROM det_jurnal dj JOIN mm m ON dj.kode_trans=m.no_mm
WHERE dj.coa_asal='21180.200' AND dj.kode_trans LIKE 'MM%' AND dj.tanggal<@start AND m.unit IN ('BJN','JBR','MLG','BWI')
ORDER BY m.unit, dj.tanggal;

PRINT '';
PRINT '=== Invoice lewat memo di OP (mmitem coa_asal=21180.200) BJN/JBR/MLG/BWI ===';
SELECT mi.no_mm, mi.no_invoice, CAST(mi.nilai AS decimal(18,2)) nilai, mi.coa_asal, m.unit, CAST(mi.tgl_mm AS date) tgl
FROM mmitem mi JOIN mm m ON mi.no_mm=m.no_mm
WHERE mi.coa_asal='21180.200' AND CAST(mi.tgl_mm AS date)<@start AND m.unit IN ('BJN','JBR','MLG','BWI')
ORDER BY m.unit, mi.tgl_mm;

PRINT '';
PRINT '=== GL naik BBM per unit BJN/JBR/MLG/BWI (non-memo) ===';
SELECT kpdd.kode_unit unit, CAST(SUM(dj.nominal) AS decimal(18,2)) gl_bbm_naik
FROM det_jurnal dj JOIN terima_doc td ON dj.tbl_name='terima_doc' AND dj.tbl_id=CAST(td.id AS varchar)
JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.no_order=td.no_order
WHERE dj.coa_asal='21180.200' AND dj.tanggal<@start AND kpdd.kode_unit IN ('BJN','JBR','MLG','BWI')
GROUP BY kpdd.kode_unit;

PRINT '';
PRINT '=== Op debet konfirmasi per unit BJN/JBR/MLG/BWI ===';
SELECT kpdd.kode_unit unit, CAST(SUM(kpdd.total) AS decimal(18,2)) op_konfir
FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
WHERE kpd.tgl_bayar<@start AND kpdd.kode_unit IN ('BJN','JBR','MLG','BWI')
GROUP BY kpdd.kode_unit;
