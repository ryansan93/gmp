/* DAFTAR INVOICE PAKAN OUTSTANDING (belum dibayar / bayar != tagihan)
   outstanding = konfir - (transfer + cn + memo). Tanpa PPh. ~0 = lunas. */
SET NOCOUNT ON;
DECLARE @end date = '2026-06-13';
WITH konf AS (
  SELECT kpp.nomor, MIN(kppd.kode_unit) unit, kpp.supplier, kpp.tgl_bayar,
         CAST(SUM(kppd.total) AS decimal(18,2)) konfir
  FROM konfirmasi_pembayaran_pakan kpp JOIN konfirmasi_pembayaran_pakan_det kppd ON kppd.id_header=kpp.id
  WHERE kpp.tgl_bayar<=@end GROUP BY kpp.nomor, kpp.supplier, kpp.tgl_bayar
),
trf AS (SELECT rpd.no_bayar inv, SUM(rpd.transfer) transfer FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='PAKAN' AND rp.tgl_realisasi<=@end GROUP BY rpd.no_bayar),
cn AS (SELECT nomor inv, SUM(pakai) cn FROM cn_post_det cpd JOIN cn_post cp ON cpd.id_header=cp.id WHERE cp.jenis_cn='PKN' AND cp.tanggal<=@end GROUP BY nomor),
memo AS (SELECT dj.invoice inv, SUM(dj.nominal) memo FROM det_jurnal dj WHERE dj.coa_tujuan='21180.100' AND dj.kode_trans LIKE 'MM%' AND dj.invoice LIKE 'BYP/%' AND dj.coa_asal NOT IN ('71105.001') AND dj.tanggal<=@end GROUP BY dj.invoice)
SELECT k.unit, k.nomor, k.supplier, k.tgl_bayar, k.konfir,
  CAST(ISNULL(t.transfer,0) AS decimal(18,2)) transfer,
  CAST(ISNULL(c.cn,0) AS decimal(18,2)) cn,
  CAST(ISNULL(m.memo,0) AS decimal(18,2)) memo,
  CAST(k.konfir-(ISNULL(t.transfer,0)+ISNULL(c.cn,0)+ISNULL(m.memo,0)) AS decimal(18,2)) outstanding,
  CASE WHEN ISNULL(t.transfer,0)=0 AND ISNULL(c.cn,0)=0 AND ISNULL(m.memo,0)=0 THEN 'BELUM BAYAR' ELSE 'PARSIAL/BEDA' END status
FROM konf k
LEFT JOIN trf t ON t.inv=k.nomor LEFT JOIN cn c ON c.inv=k.nomor LEFT JOIN memo m ON m.inv=k.nomor
WHERE ABS(k.konfir-(ISNULL(t.transfer,0)+ISNULL(c.cn,0)+ISNULL(m.memo,0))) > 1
ORDER BY k.unit, k.tgl_bayar;
