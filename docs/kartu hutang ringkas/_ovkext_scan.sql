SET NOCOUNT ON;
DECLARE @end date='2026-06-14';
-- per BYV invoice (OVK EXTERN) MJK & JBR: konfir vs realisasi(transfer+cn) vs memo
WITH konf AS (
  SELECT kpv.nomor inv, MIN(kpvd.kode_unit) unit, SUM(kpvd.total) konfir
  FROM konfirmasi_pembayaran_voadip kpv JOIN konfirmasi_pembayaran_voadip_det kpvd ON kpvd.id_header=kpv.id
  WHERE kpv.tgl_bayar<=@end GROUP BY kpv.nomor
),
byr AS (
  SELECT rpd.no_bayar inv, SUM(rpd.transfer) transfer, SUM(isnull(rpd.cn,0)) cn
  FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
  WHERE rpd.transaksi='VOADIP' AND rp.tgl_realisasi<=@end GROUP BY rpd.no_bayar
),
memo AS (
  SELECT mi.no_invoice inv, SUM(mi.nilai) m FROM mmitem mi WHERE mi.coa_tujuan='21174.000' GROUP BY mi.no_invoice
)
SELECT k.inv, k.unit, CAST(k.konfir AS decimal(18,2)) konfir,
   CAST(isnull(b.transfer,0) AS decimal(18,2)) transfer,
   CAST(isnull(b.cn,0) AS decimal(18,2)) cn,
   CAST(isnull(mo.m,0) AS decimal(18,2)) memo,
   CAST(k.konfir - isnull(b.transfer,0) - isnull(b.cn,0) - isnull(mo.m,0) AS decimal(18,2)) sisa
FROM konf k LEFT JOIN byr b ON b.inv=k.inv LEFT JOIN memo mo ON mo.inv=k.inv
WHERE k.unit IN ('MJK','JBR') AND ABS(k.konfir - isnull(b.transfer,0) - isnull(b.cn,0) - isnull(mo.m,0)) > 0.001
ORDER BY k.unit, ABS(k.konfir - isnull(b.transfer,0) - isnull(b.cn,0) - isnull(mo.m,0)) DESC;
