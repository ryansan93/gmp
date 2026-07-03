SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

-- Op DEBET DOC saldo awal (tgl_bayar basis, setelah fix)
DECLARE @op_dbt decimal(18,2);
SELECT @op_dbt = SUM(kpdd.total) FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id WHERE kpd.tgl_bayar < @start;

-- Op KREDIT DOC saldo awal: transfer+pph + cn + BAYAR LEWAT MEMO
DECLARE @op_trf decimal(18,2), @op_cn decimal(18,2), @op_mm decimal(18,2);
SELECT @op_trf = SUM(CASE WHEN k.tanggal<='2025-09-20' THEN rpd.transfer ELSE rpd.transfer+ISNULL(k.pph,0) END)
FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
LEFT JOIN (SELECT kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar tanggal, ((kpdd.total-CASE WHEN kpd.tgl_bayar>='2026-01-01' THEN ISNULL((SELECT SUM(cpd2.pakai) FROM cn_post_det cpd2 WHERE cpd2.nomor=kpd.nomor),0) ELSE 0 END)*0.0025) pph FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id LEFT JOIN (SELECT td1.* FROM terima_doc td1 RIGHT JOIN (SELECT MAX(id) id, no_order FROM terima_doc GROUP BY no_order) td2 ON td1.id=td2.id) td ON td.no_order=kpdd.no_order GROUP BY kpdd.kode_unit,kpd.nomor,kpd.tgl_bayar,kpdd.total) k ON k.nomor=rpd.no_bayar
WHERE rpd.transaksi='DOC' AND rp.tgl_realisasi<@start;

SELECT @op_cn = SUM(cpd.pakai) FROM cn_post_det cpd JOIN cn_post cp ON cpd.id_header=cp.id WHERE cp.tanggal<@start AND cpd.nomor LIKE 'BYD/%';

SELECT @op_mm = SUM(mi.nilai) FROM mmitem mi JOIN mm m ON mi.no_mm=m.no_mm
WHERE mi.coa_asal NOT IN ('71105.003') AND mi.coa_tujuan='21180.200' AND CAST(mi.tgl_mm AS date)<@start;

-- GL naik & turun DOC
DECLARE @gl_naik decimal(18,2), @gl_turun decimal(18,2);
SELECT @gl_naik = SUM(nominal) FROM det_jurnal WHERE coa_asal='21180.200' AND tanggal<@start;
SELECT @gl_turun = SUM(nominal) FROM det_jurnal WHERE coa_tujuan='21180.200' AND tanggal<@start;

PRINT '=== DEBET ===';
PRINT 'Op debet (tgl_bayar)    : '+CAST(@op_dbt AS varchar);
PRINT 'GL naik (21180.200)     : '+CAST(@gl_naik AS varchar);
PRINT 'Gap debet (op-GL)       : '+CAST(@op_dbt-@gl_naik AS varchar);
PRINT '';
PRINT '=== KREDIT ===';
PRINT 'Op kredit trf+pph       : '+CAST(ISNULL(@op_trf,0) AS varchar);
PRINT 'Op kredit CN            : '+CAST(ISNULL(@op_cn,0) AS varchar);
PRINT 'Op kredit BAYAR MEMO    : '+CAST(ISNULL(@op_mm,0) AS varchar);
PRINT 'Op kredit total         : '+CAST(ISNULL(@op_trf,0)+ISNULL(@op_cn,0)+ISNULL(@op_mm,0) AS varchar);
PRINT 'GL turun (21180.200)    : '+CAST(@gl_turun AS varchar);
PRINT 'Gap kredit (op-GL)      : '+CAST((ISNULL(@op_trf,0)+ISNULL(@op_cn,0)+ISNULL(@op_mm,0))-@gl_turun AS varchar);
PRINT '';
PRINT '=== SALDO AWAL ===';
PRINT 'Op saldo awal DOC       : '+CAST(@op_dbt-(ISNULL(@op_trf,0)+ISNULL(@op_cn,0)+ISNULL(@op_mm,0)) AS varchar);
PRINT 'GL saldo awal DOC       : '+CAST(@gl_naik-@gl_turun AS varchar);
PRINT 'Selisih (op - GL)       : '+CAST((@op_dbt-(ISNULL(@op_trf,0)+ISNULL(@op_cn,0)+ISNULL(@op_mm,0)))-(@gl_naik-@gl_turun) AS varchar);

PRINT '';
PRINT '=== INVESTIGASI GAP DEBET: BBM terjurnal tapi konfirmasi tgl_bayar >= Jun ===';
SELECT COUNT(*) baris, CAST(SUM(dj.nominal) AS decimal(18,2)) nilai,
  'GL naik (BBM) tanpa konfirmasi tgl_bayar<Jun' ket
FROM det_jurnal dj
JOIN terima_doc td ON dj.tbl_name='terima_doc' AND dj.tbl_id=CAST(td.id AS varchar)
JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.no_order=td.no_order
JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
WHERE dj.coa_asal='21180.200' AND dj.tanggal<@start AND kpd.tgl_bayar>=@start;
