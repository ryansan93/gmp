SET NOCOUNT ON;
PRINT '=== tanggal GL (det_jurnal) utk 4 pindah orphan -> tgl_terima seharusnya ===';
SELECT opp.id, opp.no_sj, CAST(opp.ongkos_angkut AS decimal(18,2)) ongkos, opp.tgl_terima AS tgl_terima_skrg,
  dj.tanggal AS tgl_gl, dj.unit AS gl_unit, dj.coa_asal, dj.coa_tujuan, CAST(dj.nominal AS decimal(18,2)) dj_nominal
FROM oa_pindah_pakan opp
JOIN det_jurnal dj ON dj.tbl_id = opp.id AND dj.tbl_name='oa_pindah_pakan' AND dj.coa_asal='21212.000'
WHERE opp.id IN (907,908,957,639)
ORDER BY opp.id;
