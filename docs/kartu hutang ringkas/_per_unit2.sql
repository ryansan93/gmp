SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
WITH inv_mm AS (
    SELECT m.unit,
           CASE WHEN mi.no_invoice IS NOT NULL THEN mi.no_invoice ELSE mi.no_mm END nomor,
           ISNULL(NULLIF(kd.supplier,''), m.no_supplier) supplier,
           mi.nilai
    FROM mmitem mi JOIN mm m ON mi.no_mm=m.no_mm
    LEFT JOIN (SELECT nomor,supplier FROM konfirmasi_pembayaran_doc GROUP BY nomor,supplier) kd ON kd.nomor=mi.no_invoice
    WHERE mi.coa_asal='21180.200' AND CAST(mi.tgl_mm AS date)<@start
    AND NOT EXISTS (SELECT 1 FROM (
        SELECT nomor FROM konfirmasi_pembayaran_doc UNION ALL SELECT nomor FROM konfirmasi_pembayaran_pakan
        UNION ALL SELECT nomor FROM konfirmasi_pembayaran_voadip UNION ALL SELECT nomor FROM konfirmasi_pembayaran_oa_pakan
        UNION ALL SELECT ISNULL(NULLIF(invoice,''),nomor) FROM konfirmasi_pembayaran_peternak
    ) kk WHERE kk.nomor=mi.no_invoice)
),
op_dbt_unit AS (
    SELECT kpdd.kode_unit unit, SUM(kpdd.total) konfir, 0 inv_mm FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id WHERE kpd.tgl_bayar<@start GROUP BY kpdd.kode_unit
    UNION ALL
    SELECT unit, 0, SUM(nilai) FROM inv_mm GROUP BY unit
),
gl_naik_unit AS (
    SELECT kpdd.kode_unit unit, SUM(dj.nominal) bbm, 0 mm FROM det_jurnal dj
    JOIN terima_doc td ON dj.tbl_name='terima_doc' AND dj.tbl_id=CAST(td.id AS varchar)
    JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.no_order=td.no_order
    WHERE dj.coa_asal='21180.200' AND dj.tanggal<@start GROUP BY kpdd.kode_unit
    UNION ALL
    SELECT m.unit, 0, SUM(dj.nominal) FROM det_jurnal dj JOIN mm m ON dj.kode_trans=m.no_mm
    WHERE dj.coa_asal='21180.200' AND dj.kode_trans LIKE 'MM%' AND dj.tanggal<@start GROUP BY m.unit
)
SELECT u.unit,
    CAST(SUM(d.konfir)+SUM(d.inv_mm) AS decimal(18,2)) op_debet,
    CAST(SUM(g.bbm)+SUM(g.mm) AS decimal(18,2)) gl_naik,
    CAST((SUM(d.konfir)+SUM(d.inv_mm))-(SUM(g.bbm)+SUM(g.mm)) AS decimal(18,2)) gap_debet
FROM (SELECT DISTINCT unit FROM op_dbt_unit UNION SELECT unit FROM gl_naik_unit) u
LEFT JOIN op_dbt_unit d ON d.unit=u.unit
LEFT JOIN gl_naik_unit g ON g.unit=u.unit
GROUP BY u.unit
HAVING ABS((SUM(d.konfir)+SUM(d.inv_mm))-(SUM(g.bbm)+SUM(g.mm))) > 1000
ORDER BY ABS((SUM(d.konfir)+SUM(d.inv_mm))-(SUM(g.bbm)+SUM(g.mm))) DESC;
