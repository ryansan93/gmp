SET NOCOUNT ON;
-- per no_order: konfir vs GL naik (BBM), kunci no_order (bukan no_sj). Unit MJK & JBR.
WITH konf AS (
  SELECT kpvd.no_order, MIN(kpvd.kode_unit) unit, MIN(kpv.nomor) invoice, SUM(kpvd.total) konfir
  FROM konfirmasi_pembayaran_voadip kpv JOIN konfirmasi_pembayaran_voadip_det kpvd ON kpvd.id_header=kpv.id
  WHERE kpvd.kode_unit IN ('MJK','JBR') GROUP BY kpvd.no_order
),
naik AS (
  SELECT kv.no_order, SUM(dj.nominal) gl_naik
  FROM det_jurnal dj JOIN terima_voadip tv ON dj.tbl_name='terima_voadip' AND dj.tbl_id=CAST(tv.id AS varchar)
  JOIN kirim_voadip kv ON tv.id_kirim_voadip=kv.id
  WHERE dj.coa_asal='21174.000' GROUP BY kv.no_order
)
SELECT k.unit, k.invoice, k.no_order, CAST(k.konfir AS decimal(18,2)) konfir,
   CAST(isnull(n.gl_naik,0) AS decimal(18,2)) gl_naik,
   CAST(k.konfir - isnull(n.gl_naik,0) AS decimal(18,2)) beda
FROM konf k LEFT JOIN naik n ON n.no_order=k.no_order
WHERE ABS(k.konfir - isnull(n.gl_naik,0)) > 0.001 AND isnull(n.gl_naik,0) > 0
ORDER BY k.unit, ABS(k.konfir - isnull(n.gl_naik,0)) DESC;
