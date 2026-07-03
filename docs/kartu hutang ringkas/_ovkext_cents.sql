SET NOCOUNT ON;
DECLARE @end date='2026-06-14';
WITH konf AS (
  SELECT kpv.nomor inv, MIN(kpvd.kode_unit) unit, MIN(kpvd.no_order) no_order, SUM(kpvd.total) konfir
  FROM konfirmasi_pembayaran_voadip kpv JOIN konfirmasi_pembayaran_voadip_det kpvd ON kpvd.id_header=kpv.id
  WHERE kpv.tgl_bayar<=@end AND kpvd.kode_unit IN ('MJK','JBR') GROUP BY kpv.nomor
),
byr AS (SELECT rpd.no_bayar inv, SUM(rpd.transfer) transfer FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='VOADIP' AND rp.tgl_realisasi<=@end GROUP BY rpd.no_bayar),
naik AS (SELECT kv.no_order, SUM(dj.nominal) gl_naik FROM det_jurnal dj JOIN terima_voadip tv ON dj.tbl_name='terima_voadip' AND dj.tbl_id=CAST(tv.id AS varchar) JOIN kirim_voadip kv ON tv.id_kirim_voadip=kv.id WHERE dj.coa_asal='21174.000' GROUP BY kv.no_order)
SELECT k.unit, k.inv, k.no_order, CAST(k.konfir AS decimal(18,2)) konfir,
   CAST(isnull(b.transfer,0) AS decimal(18,2)) transfer,
   CAST(isnull(n.gl_naik,0) AS decimal(18,2)) gl_naik,
   CASE WHEN isnull(b.transfer,0)=0 THEN 'BELUM BAYAR' WHEN ABS(k.konfir-b.transfer)<0.005 THEN 'transfer=konfir' ELSE 'transfer beda' END status_bayar
FROM konf k LEFT JOIN byr b ON b.inv=k.inv LEFT JOIN naik n ON n.no_order=k.no_order
WHERE k.konfir <> FLOOR(k.konfir)   -- hanya yang bernilai sen
ORDER BY k.unit, k.inv;
