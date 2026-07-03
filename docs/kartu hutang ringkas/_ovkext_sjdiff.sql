SET NOCOUNT ON;
-- per SJ: konfir vs GL naik (BBM), unit MJK & JBR, cari yg beda
WITH ksj AS (
  SELECT kpvd.no_sj, MIN(kpvd.kode_unit) unit, SUM(kpvd.total) konfir
  FROM konfirmasi_pembayaran_voadip_det kpvd WHERE kpvd.kode_unit IN ('MJK','JBR') GROUP BY kpvd.no_sj
),
nsj AS (
  SELECT kv.no_sj, SUM(dj.nominal) naik
  FROM det_jurnal dj JOIN terima_voadip tv ON dj.tbl_name='terima_voadip' AND dj.tbl_id=CAST(tv.id AS varchar)
  JOIN kirim_voadip kv ON tv.id_kirim_voadip=kv.id
  WHERE dj.coa_asal='21174.000' GROUP BY kv.no_sj
)
SELECT k.no_sj, k.unit, CAST(k.konfir AS decimal(18,2)) konfir, CAST(isnull(n.naik,0) AS decimal(18,2)) gl_naik,
   CAST(k.konfir - isnull(n.naik,0) AS decimal(18,2)) beda
FROM ksj k LEFT JOIN nsj n ON n.no_sj=k.no_sj
WHERE ABS(k.konfir - isnull(n.naik,0)) > 0.001
ORDER BY k.unit, ABS(k.konfir - isnull(n.naik,0)) DESC;
