SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

-- realized BYO set (rp.tgl_realisasi<start)
;WITH realized AS (
  SELECT DISTINCT rpd.no_bayar AS byo
  FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
  WHERE rpd.transaksi='OA PAKAN' AND rp.tgl_realisasi<@start
),
glturun AS (
  SELECT dj.invoice AS byo, dj.coa_asal, dj.nominal, dj.tanggal
  FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.tanggal<@start
)
SELECT
  CASE WHEN g.byo IS NULL OR g.byo='' THEN 'BYO_KOSONG'
       WHEN r.byo IS NOT NULL THEN 'realized<start'
       ELSE 'BYO_TIDAK_realized<start' END AS kelas,
  g.coa_asal,
  COUNT(*) n,
  CAST(SUM(g.nominal) AS decimal(18,2)) total
FROM glturun g LEFT JOIN realized r ON r.byo=g.byo
GROUP BY CASE WHEN g.byo IS NULL OR g.byo='' THEN 'BYO_KOSONG' WHEN r.byo IS NOT NULL THEN 'realized<start' ELSE 'BYO_TIDAK_realized<start' END, g.coa_asal
ORDER BY kelas, total DESC;

PRINT '=== ringkas per kelas ===';
;WITH realized AS (
  SELECT DISTINCT rpd.no_bayar AS byo
  FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
  WHERE rpd.transaksi='OA PAKAN' AND rp.tgl_realisasi<@start
),
glturun AS (SELECT dj.invoice AS byo, dj.nominal FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.tanggal<@start)
SELECT
  CASE WHEN g.byo IS NULL OR g.byo='' THEN 'BYO_KOSONG' WHEN r.byo IS NOT NULL THEN 'realized<start' ELSE 'BYO_TIDAK_realized<start' END AS kelas,
  COUNT(*) n, CAST(SUM(g.nominal) AS decimal(18,2)) total
FROM glturun g LEFT JOIN realized r ON r.byo=g.byo
GROUP BY CASE WHEN g.byo IS NULL OR g.byo='' THEN 'BYO_KOSONG' WHEN r.byo IS NOT NULL THEN 'realized<start' ELSE 'BYO_TIDAK_realized<start' END;
