SET NOCOUNT ON;
PRINT '=== Apakah CN (kode_trans CN%) terjurnal turun ke 21180.200 ? ===';
SELECT TOP 15 dj.id, dj.tanggal, dj.kode_trans, dj.coa_asal, dj.coa_tujuan, dj.invoice, CAST(dj.nominal AS decimal(18,2)) nominal
FROM det_jurnal dj
WHERE dj.coa_tujuan='21180.200' AND dj.invoice LIKE 'BYD/%' AND dj.kode_trans LIKE 'CN%'
ORDER BY dj.tanggal DESC;
PRINT '';
PRINT '=== Rincian kode_trans pada GL turun 21180.200 (s/d 31 Mei) ===';
SELECT CASE WHEN dj.kode_trans LIKE 'BYR%' THEN 'BYR (transfer)'
            WHEN dj.kode_trans LIKE 'CN%' THEN 'CN'
            WHEN dj.kode_trans LIKE 'MM%' THEN 'MM (memo)'
            ELSE 'LAIN: '+LEFT(dj.kode_trans,3) END jenis_trans,
       COUNT(*) baris, CAST(SUM(dj.nominal) AS decimal(18,2)) total
FROM det_jurnal dj
WHERE dj.coa_tujuan='21180.200' AND dj.invoice LIKE 'BYD/%' AND dj.tanggal<='2026-05-31'
GROUP BY CASE WHEN dj.kode_trans LIKE 'BYR%' THEN 'BYR (transfer)'
              WHEN dj.kode_trans LIKE 'CN%' THEN 'CN'
              WHEN dj.kode_trans LIKE 'MM%' THEN 'MM (memo)'
              ELSE 'LAIN: '+LEFT(dj.kode_trans,3) END
ORDER BY total DESC;
