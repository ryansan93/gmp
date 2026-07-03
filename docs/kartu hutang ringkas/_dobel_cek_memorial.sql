SET NOCOUNT ON;
/* terima_doc id pasangan dobel */
DECLARE @ids TABLE(id int);
INSERT @ids VALUES (548),(602),(698),(708),(150),(208);

/* 1. Semua jurnal yg link ke terima_doc id ini (apapun coa) */
SELECT 'jurnal_by_terima' src, dj.id dj_id, dj.tbl_id, dj.kode_trans, dj.tanggal,
       dj.coa_asal, dj.coa_tujuan, dj.nominal, dj.invoice, dj.keterangan
FROM det_jurnal dj
WHERE dj.tbl_name='terima_doc' AND dj.tbl_id IN (SELECT CAST(id AS varchar) FROM @ids)
ORDER BY dj.tbl_id, dj.tanggal;

/* 2. Semua memorial (MM) yg menyentuh 21180.200 utk invoice2 ini */
SELECT 'MM_21180200' src, dj.id dj_id, dj.kode_trans, dj.tanggal,
       dj.coa_asal, dj.coa_tujuan, dj.nominal, dj.invoice, dj.keterangan
FROM det_jurnal dj
WHERE dj.kode_trans LIKE 'MM%'
  AND (dj.coa_asal='21180.200' OR dj.coa_tujuan='21180.200')
  AND (dj.invoice IN ('BYD/11/25/00053','BYD/11/25/00195','BYD/09/25/00109')
       OR dj.keterangan LIKE '%11007%' OR dj.keterangan LIKE '%11026%' OR dj.keterangan LIKE '%09010%'
       OR dj.keterangan LIKE '%00053%' OR dj.keterangan LIKE '%00195%' OR dj.keterangan LIKE '%00109%')
ORDER BY dj.tanggal;

/* 3. mmitem yg refer invoice ini */
SELECT 'mmitem' src, mi.no_mm, mi.no_invoice, mi.coa_asal, mi.coa_tujuan, mi.nilai, mi.tgl_mm, mi.keterangan
FROM mmitem mi
WHERE mi.no_invoice IN ('BYD/11/25/00053','BYD/11/25/00195','BYD/09/25/00109')
   OR mi.keterangan LIKE '%11007%' OR mi.keterangan LIKE '%11026%' OR mi.keterangan LIKE '%09010%';
