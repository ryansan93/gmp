SET NOCOUNT ON;
PRINT '=== det_jurnal BYR/11/25/00028 utk invoice BYD/10/25/00195 ==='
SELECT dj.id, dj.tanggal, dj.coa_asal, dj.coa_tujuan, dj.nominal, dj.unit, dj.keterangan
FROM det_jurnal dj WHERE dj.kode_trans='BYR/11/25/00028' AND dj.invoice='BYD/10/25/00195' ORDER BY dj.id;
PRINT '=== nama COA ==='
SELECT DISTINCT coa, nama_coa FROM coa WHERE coa IN ('11130.001','21180.200','24622.000','27001.000','71103.000','12040.000') ORDER BY coa;
