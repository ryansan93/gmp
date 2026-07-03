SET NOCOUNT ON;
/* Semua MM koreksi double jurnal yg turun-kan 21180.200 (cari by keterangan + nilai supplier) */
SELECT 'MM_koreksi' src, dj.id dj_id, dj.kode_trans, dj.tanggal, dj.coa_asal, dj.coa_tujuan,
       dj.nominal, dj.invoice, dj.keterangan
FROM det_jurnal dj
WHERE dj.kode_trans LIKE 'MM%'
  AND dj.coa_tujuan='21180.200'
  AND (dj.keterangan LIKE '%DOUBLE%' OR dj.keterangan LIKE '%DOBEL%' OR dj.keterangan LIKE '%KOREKSI%')
  AND dj.tanggal < '2026-06-01'
ORDER BY dj.tanggal;
