SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

PRINT '=== KREDIT per jenis dari realisasi_pembayaran ===';
SELECT rpd.transaksi, CAST(SUM(rpd.transfer) AS decimal(18,2)) transfer_total, COUNT(*) baris
FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
WHERE rp.tgl_realisasi < @start
GROUP BY rpd.transaksi ORDER BY transfer_total DESC;

PRINT '';
PRINT '=== KREDIT CN per jenis ===';
SELECT CASE WHEN cp.jenis_cn='PKN' THEN 'PAKAN' ELSE cp.jenis_cn END jenis, CAST(SUM(cpd.pakai) AS decimal(18,2)) cn_total
FROM cn_post_det cpd JOIN cn_post cp ON cpd.id_header=cp.id WHERE cp.tanggal < @start GROUP BY cp.jenis_cn;

PRINT '';
PRINT '=== GL turun per COA vs Operasional transfer per jenis ===';
-- Cek RHPP: GL turun 21213 vs operasional RHPP kredit
DECLARE @gl_rhpp decimal(18,2), @op_rhpp decimal(18,2);
SELECT @gl_rhpp = SUM(nominal) FROM det_jurnal WHERE coa_tujuan IN ('21213.000','21213.001') AND tanggal<@start;
SELECT @op_rhpp = SUM(rpd.transfer) FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='RHPP' AND rp.tgl_realisasi<@start;
PRINT 'GL turun RHPP      : '+CAST(ISNULL(@gl_rhpp,0) AS varchar);
PRINT 'Op kredit RHPP     : '+CAST(ISNULL(@op_rhpp,0) AS varchar);
PRINT 'Selisih RHPP       : '+CAST(ISNULL(@op_rhpp,0)-ISNULL(@gl_rhpp,0) AS varchar);

-- OA PAKAN: GL turun 21212 vs op
DECLARE @gl_oap decimal(18,2), @op_oap decimal(18,2);
SELECT @gl_oap = SUM(nominal) FROM det_jurnal WHERE coa_tujuan='21212.000' AND tanggal<@start;
SELECT @op_oap = SUM(rpd.transfer) FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='OA PAKAN' AND rp.tgl_realisasi<@start;
PRINT 'GL turun OA PAKAN  : '+CAST(ISNULL(@gl_oap,0) AS varchar);
PRINT 'Op kredit OA PAKAN : '+CAST(ISNULL(@op_oap,0) AS varchar);
PRINT 'Selisih OA PAKAN   : '+CAST(ISNULL(@op_oap,0)-ISNULL(@gl_oap,0) AS varchar);
