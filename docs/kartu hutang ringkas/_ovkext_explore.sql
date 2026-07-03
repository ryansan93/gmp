SET NOCOUNT ON;
PRINT '=== kolom konfirmasi_pembayaran_voadip ==='
SELECT name FROM sys.columns WHERE object_id=OBJECT_ID('konfirmasi_pembayaran_voadip') ORDER BY column_id;
PRINT '=== GL 21174.000 ringkas per unit (naik-turun s/d 2026-06-14) ==='
SELECT dj.unit,
  CAST(SUM(CASE WHEN dj.coa_asal='21174.000' THEN dj.nominal ELSE 0 END) AS decimal(18,2)) naik,
  CAST(SUM(CASE WHEN dj.coa_tujuan='21174.000' THEN dj.nominal ELSE 0 END) AS decimal(18,2)) turun
FROM det_jurnal dj WHERE (dj.coa_asal='21174.000' OR dj.coa_tujuan='21174.000') AND dj.tanggal<='2026-06-14'
GROUP BY dj.unit ORDER BY dj.unit;
