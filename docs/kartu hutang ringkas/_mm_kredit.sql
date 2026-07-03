SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
PRINT '=== BAYAR LEWAT MEMO kredit 21180.200 s/d Mei (di op report) ===';
SELECT mi.no_mm, mi.no_invoice, CAST(mi.nilai AS decimal(18,2)) nilai, mi.coa_asal, mi.coa_tujuan, CAST(mi.tgl_mm AS date) tgl
FROM mmitem mi JOIN mm m ON mi.no_mm=m.no_mm
WHERE mi.coa_asal NOT IN ('71105.003') AND mi.coa_tujuan='21180.200' AND CAST(mi.tgl_mm AS date)<@start
ORDER BY mi.tgl_mm;

PRINT '';
PRINT '=== GL turun 21180.200 via MM (bayar/settle lewat memo) s/d Mei ===';
SELECT dj.kode_trans, dj.invoice, dj.tanggal, CAST(dj.nominal AS decimal(18,2)) nominal, dj.keterangan
FROM det_jurnal dj WHERE dj.coa_tujuan='21180.200' AND dj.kode_trans LIKE 'MM%' AND dj.tanggal<@start
ORDER BY dj.tanggal;
