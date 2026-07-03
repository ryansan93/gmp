SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

PRINT '=== P1: GL turun 21212 per BYO (invoice), pisah per coa_asal, JOIN bruto konfir ===';
PRINT '    cek apakah transfer+pph+clearing = bruto (clearing rounding) atau = bruto+clearing (clearing extra)';
;WITH gl AS (
  SELECT dj.invoice AS byo,
    SUM(CASE WHEN dj.coa_asal='11130.002' THEN dj.nominal ELSE 0 END) AS gl_transfer,
    SUM(CASE WHEN dj.coa_asal='24623.000' THEN dj.nominal ELSE 0 END) AS gl_pph,
    SUM(CASE WHEN dj.coa_asal='27001.000' THEN dj.nominal ELSE 0 END) AS gl_clearing,
    SUM(CASE WHEN dj.coa_asal='11130.001' THEN dj.nominal ELSE 0 END) AS gl_113001,
    SUM(dj.nominal) AS gl_turun
  FROM det_jurnal dj
  WHERE dj.coa_tujuan='21212.000' AND dj.tanggal<@start
  GROUP BY dj.invoice
)
SELECT TOP 25 gl.byo,
  CAST(k.total AS decimal(18,2)) konfir_net,
  CAST(k.potongan_pph_23 AS decimal(18,2)) konfir_pph,
  CAST(k.total+k.potongan_pph_23 AS decimal(18,2)) bruto,
  CAST(gl.gl_transfer AS decimal(18,2)) gl_tr,
  CAST(gl.gl_pph AS decimal(18,2)) gl_pph,
  CAST(gl.gl_clearing AS decimal(18,2)) gl_clr,
  CAST(gl.gl_turun - (k.total+k.potongan_pph_23) AS decimal(18,2)) turun_minus_bruto
FROM gl LEFT JOIN konfirmasi_pembayaran_oa_pakan k ON k.nomor=gl.byo
WHERE gl.gl_clearing<>0
ORDER BY ABS(gl.gl_clearing) DESC;

PRINT '=== P2: agregat — total turun vs total bruto utk BYO yg muncul di GL turun ===';
;WITH gl AS (
  SELECT dj.invoice AS byo, SUM(dj.nominal) AS gl_turun
  FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.tanggal<@start GROUP BY dj.invoice
)
SELECT CAST(SUM(gl.gl_turun) AS decimal(18,2)) total_gl_turun,
       CAST(SUM(k.total+k.potongan_pph_23) AS decimal(18,2)) total_bruto_matched,
       SUM(CASE WHEN k.nomor IS NULL THEN 1 ELSE 0 END) n_byo_tanpa_konfir,
       CAST(SUM(CASE WHEN k.nomor IS NULL THEN gl.gl_turun ELSE 0 END) AS decimal(18,2)) turun_byo_tanpa_konfir
FROM gl LEFT JOIN konfirmasi_pembayaran_oa_pakan k ON k.nomor=gl.byo;
