SET NOCOUNT ON;
/* Deteksi 1 order -> banyak penerimaan (terima_doc), beserta status jurnal & konfirmasi */
SELECT
    td.no_order,
    COUNT(*)                                   as jml_terima,
    COUNT(DISTINCT td.no_terima)               as jml_no_terima,
    MIN(td.datang)                             as datang_pertama,
    MAX(td.datang)                             as datang_terakhir,
    SUM(CASE WHEN td.datang IS NULL THEN 1 ELSE 0 END) as terima_tanpa_datang,
    SUM(CASE WHEN td.no_bbm IS NULL OR td.no_bbm='' THEN 1 ELSE 0 END) as terima_tanpa_bbm,
    -- jurnal hutang naik (BBM) yang ter-link ke terima_doc ini
    (SELECT COUNT(*) FROM det_jurnal dj
       WHERE dj.tbl_name='terima_doc' AND dj.coa_asal='21180.200'
         AND dj.tbl_id IN (SELECT CAST(t2.id AS varchar) FROM terima_doc t2 WHERE t2.no_order=td.no_order)
    ) as jml_jurnal_bbm,
    (SELECT CAST(SUM(dj.nominal) AS decimal(18,2)) FROM det_jurnal dj
       WHERE dj.tbl_name='terima_doc' AND dj.coa_asal='21180.200'
         AND dj.tbl_id IN (SELECT CAST(t2.id AS varchar) FROM terima_doc t2 WHERE t2.no_order=td.no_order)
    ) as nilai_jurnal_bbm,
    -- konfirmasi yang menagih order ini
    (SELECT COUNT(DISTINCT kpd.nomor) FROM konfirmasi_pembayaran_doc_det kpdd
       JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
       WHERE kpdd.no_order=td.no_order) as jml_konfir
FROM terima_doc td
GROUP BY td.no_order
HAVING COUNT(*) > 1
ORDER BY COUNT(*) DESC, td.no_order;
