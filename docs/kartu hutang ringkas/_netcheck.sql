SET NOCOUNT ON;
DECLARE @end date='2026-06-13';
WITH konf AS (SELECT kpd.nomor inv, kpd.tgl_bayar, CAST(SUM(kpdd.total) AS decimal(18,2)) konfir FROM konfirmasi_pembayaran_doc kpd JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id WHERE kpd.tgl_bayar<=@end GROUP BY kpd.nomor, kpd.tgl_bayar),
trf AS (SELECT rpd.no_bayar inv, SUM(rpd.transfer) transfer FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='DOC' AND rp.tgl_realisasi<=@end GROUP BY rpd.no_bayar),
cn AS (SELECT nomor inv, SUM(pakai) cn FROM cn_post_det cpd JOIN cn_post cp ON cpd.id_header=cp.id WHERE cp.tanggal<=@end GROUP BY nomor),
memo AS (SELECT dj.invoice inv, SUM(dj.nominal) memo_set FROM det_jurnal dj WHERE dj.coa_tujuan='21180.200' AND dj.kode_trans LIKE 'MM%' AND dj.invoice LIKE 'BYD/%' AND dj.tanggal<=@end GROUP BY dj.invoice)
SELECT CAST(SUM(k.konfir - (ISNULL(t.transfer,0)+ISNULL(c.cn,0)+ISNULL(m.memo_set,0)+(CASE WHEN k.tgl_bayar<='2025-09-20' THEN 0 WHEN k.tgl_bayar>='2026-01-01' THEN (k.konfir-ISNULL(c.cn,0))*0.0025 ELSE k.konfir*0.0025 END))) AS decimal(18,2)) op_saldo_doc
FROM konf k LEFT JOIN trf t ON t.inv=k.inv LEFT JOIN cn c ON c.inv=k.inv LEFT JOIN memo m ON m.inv=k.inv;
