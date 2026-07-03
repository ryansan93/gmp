SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

-- Op saldo awal per unit
WITH op_dbt AS (
    SELECT kpdd.kode_unit unit, SUM(kpdd.total) v
    FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
    WHERE kpd.tgl_bayar < @start GROUP BY kpdd.kode_unit
),
op_inv_mm AS (
    SELECT m.unit, SUM(mi.nilai) v FROM mmitem mi JOIN mm m ON mi.no_mm=m.no_mm
    WHERE mi.coa_asal='21180.200' AND CAST(mi.tgl_mm AS date)<@start
    AND NOT EXISTS (SELECT 1 FROM (SELECT nomor FROM konfirmasi_pembayaran_doc UNION ALL SELECT nomor FROM konfirmasi_pembayaran_pakan UNION ALL SELECT nomor FROM konfirmasi_pembayaran_voadip UNION ALL SELECT nomor FROM konfirmasi_pembayaran_oa_pakan UNION ALL SELECT ISNULL(NULLIF(invoice,''),nomor) FROM konfirmasi_pembayaran_peternak) kk WHERE kk.nomor=mi.no_invoice)
    GROUP BY m.unit
),
op_krd AS (
    SELECT m.unit, SUM(mi.nilai) v FROM mmitem mi JOIN mm m ON mi.no_mm=m.no_mm
    WHERE mi.coa_asal NOT IN ('71105.003') AND mi.coa_tujuan='21180.200' AND CAST(mi.tgl_mm AS date)<@start
    GROUP BY m.unit
),
-- GL naik per unit (via no_order→konfir untuk BBM, via kode_unit untuk memo)
gl_naik AS (
    SELECT kpdd.kode_unit unit, SUM(dj.nominal) v
    FROM det_jurnal dj JOIN terima_doc td ON dj.tbl_name='terima_doc' AND dj.tbl_id=CAST(td.id AS varchar)
    JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.no_order=td.no_order
    WHERE dj.coa_asal='21180.200' AND dj.tanggal<@start GROUP BY kpdd.kode_unit
    UNION ALL
    SELECT m.unit, SUM(dj.nominal) FROM det_jurnal dj JOIN mm m ON dj.kode_trans=m.no_mm
    WHERE dj.coa_asal='21180.200' AND dj.kode_trans LIKE 'MM%' AND dj.tanggal<@start GROUP BY m.unit
),
gl_turun AS (
    SELECT m.unit, SUM(dj.nominal) v FROM det_jurnal dj
    JOIN (SELECT kpd.nomor, kpdd.kode_unit unit FROM konfirmasi_pembayaran_doc kpd JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id GROUP BY kpd.nomor, kpdd.kode_unit) m ON dj.invoice=m.nomor
    WHERE dj.coa_tujuan='21180.200' AND dj.tanggal<@start GROUP BY m.unit
)
SELECT
    u.unit,
    CAST(ISNULL(d.v,0)+ISNULL(im.v,0) AS decimal(18,2)) op_debet,
    CAST(ISNULL(k.v,0) AS decimal(18,2)) op_kredit_mm,
    CAST(ISNULL(d.v,0)+ISNULL(im.v,0)-ISNULL(k.v,0) AS decimal(18,2)) op_saldo,
    CAST(ISNULL(gn.v,0) AS decimal(18,2)) gl_naik,
    CAST(ISNULL(gt.v,0) AS decimal(18,2)) gl_turun,
    CAST(ISNULL(gn.v,0)-ISNULL(gt.v,0) AS decimal(18,2)) gl_saldo,
    CAST((ISNULL(d.v,0)+ISNULL(im.v,0)-ISNULL(k.v,0))-(ISNULL(gn.v,0)-ISNULL(gt.v,0)) AS decimal(18,2)) selisih
FROM (SELECT DISTINCT unit FROM op_dbt UNION SELECT unit FROM gl_naik) u
LEFT JOIN op_dbt d ON d.unit=u.unit
LEFT JOIN op_inv_mm im ON im.unit=u.unit
LEFT JOIN op_krd k ON k.unit=u.unit
LEFT JOIN (SELECT unit, SUM(v) v FROM gl_naik GROUP BY unit) gn ON gn.unit=u.unit
LEFT JOIN gl_turun gt ON gt.unit=u.unit
ORDER BY ABS((ISNULL(d.v,0)+ISNULL(im.v,0)-ISNULL(k.v,0))-(ISNULL(gn.v,0)-ISNULL(gt.v,0))) DESC;
