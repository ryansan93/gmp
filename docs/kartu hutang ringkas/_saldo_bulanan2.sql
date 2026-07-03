SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

-- Saldo awal GL Juni dari saldo_bulanan (sum semua unit, tanggal = 2026-06-01)
DECLARE @sb_jun decimal(18,2);
SELECT @sb_jun = SUM(saldo_awal) FROM saldo_bulanan WHERE coa='21180.200' AND tanggal=@start;
PRINT 'saldo_bulanan Jun 2026 (sum all unit): '+ISNULL(CAST(@sb_jun AS varchar),'NULL - tdk ada data');

-- Coba Mei
DECLARE @sb_mei decimal(18,2);
SELECT @sb_mei = SUM(saldo_awal) FROM saldo_bulanan WHERE coa='21180.200' AND tanggal='2026-05-01';
PRINT 'saldo_bulanan Mei 2026               : '+ISNULL(CAST(@sb_mei AS varchar),'NULL');

-- Coba Apr
DECLARE @sb_apr decimal(18,2);
SELECT @sb_apr = SUM(saldo_awal) FROM saldo_bulanan WHERE coa='21180.200' AND tanggal='2026-04-01';
PRINT 'saldo_bulanan Apr 2026               : '+ISNULL(CAST(@sb_apr AS varchar),'NULL');

-- GL turun det_jurnal Juni
DECLARE @gl_det_mei decimal(18,2);
SELECT @gl_det_mei = SUM(CASE WHEN coa_asal='21180.200' THEN nominal ELSE -nominal END)
FROM det_jurnal WHERE (coa_asal='21180.200' OR coa_tujuan='21180.200')
AND tanggal BETWEEN '2026-05-01' AND '2026-05-31';
PRINT 'GL net Mei 2026 (dari det_jurnal)    : '+ISNULL(CAST(@gl_det_mei AS varchar),'NULL');

-- saldo_bulanan terakhir yg ada
SELECT TOP 5 tanggal, CAST(SUM(saldo_awal) AS decimal(18,2)) total_saldo FROM saldo_bulanan WHERE coa='21180.200' GROUP BY tanggal ORDER BY tanggal DESC;
