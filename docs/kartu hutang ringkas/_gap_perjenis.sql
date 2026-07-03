SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

-- GL saldo per jenis (naik-turun s/d 31 Mei)
WITH gl AS (
SELECT 'DOC'      jenis, SUM(CASE WHEN coa_asal IN ('21180.200','21173.000') THEN nominal ELSE 0 END)-SUM(CASE WHEN coa_tujuan IN ('21180.200','21173.000') THEN nominal ELSE 0 END) v FROM det_jurnal WHERE (coa_asal IN ('21180.200','21173.000') OR coa_tujuan IN ('21180.200','21173.000')) AND tanggal<@start
UNION ALL
SELECT 'PAKAN',   SUM(CASE WHEN coa_asal IN ('21180.100','21172.000') THEN nominal ELSE 0 END)-SUM(CASE WHEN coa_tujuan IN ('21180.100','21172.000') THEN nominal ELSE 0 END) FROM det_jurnal WHERE (coa_asal IN ('21180.100','21172.000') OR coa_tujuan IN ('21180.100','21172.000')) AND tanggal<@start
UNION ALL
SELECT 'OVK',     SUM(CASE WHEN coa_asal='21180.300' THEN nominal ELSE 0 END)-SUM(CASE WHEN coa_tujuan='21180.300' THEN nominal ELSE 0 END) FROM det_jurnal WHERE (coa_asal='21180.300' OR coa_tujuan='21180.300') AND tanggal<@start
UNION ALL
SELECT 'OVK EXTERN', SUM(CASE WHEN coa_asal='21174.000' THEN nominal ELSE 0 END)-SUM(CASE WHEN coa_tujuan='21174.000' THEN nominal ELSE 0 END) FROM det_jurnal WHERE (coa_asal='21174.000' OR coa_tujuan='21174.000') AND tanggal<@start
UNION ALL
SELECT 'RHPP',    SUM(CASE WHEN coa_asal IN ('21213.000','21213.001') THEN nominal ELSE 0 END)-SUM(CASE WHEN coa_tujuan IN ('21213.000','21213.001') THEN nominal ELSE 0 END) FROM det_jurnal WHERE (coa_asal IN ('21213.000','21213.001') OR coa_tujuan IN ('21213.000','21213.001')) AND tanggal<@start
UNION ALL
SELECT 'OA PAKAN',SUM(CASE WHEN coa_asal='21212.000' THEN nominal ELSE 0 END)-SUM(CASE WHEN coa_tujuan='21212.000' THEN nominal ELSE 0 END) FROM det_jurnal WHERE (coa_asal='21212.000' OR coa_tujuan='21212.000') AND tanggal<@start
),
-- Operasional debet saldo awal per jenis (dari controller)
op_dbt AS (
SELECT 'DOC' jenis, SUM(kpdd.total) v FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id WHERE kpd.tgl_bayar<@start
UNION ALL
SELECT 'PAKAN', SUM(kppd.total) FROM konfirmasi_pembayaran_pakan_det kppd JOIN konfirmasi_pembayaran_pakan kpp ON kppd.id_header=kpp.id WHERE kpp.tgl_bayar<@start
UNION ALL
SELECT ISNULL(pc.kode,'OVK?'), SUM(kpv.total) FROM konfirmasi_pembayaran_voadip kpv LEFT JOIN (SELECT * FROM pelanggan_coa WHERE kode LIKE '%OVK%') pc ON pc.no_pelanggan=kpv.supplier WHERE kpv.tgl_bayar<@start GROUP BY pc.kode
UNION ALL
SELECT 'RHPP', SUM(kpp.total) FROM konfirmasi_pembayaran_peternak kpp WHERE kpp.tgl_bayar<@start
UNION ALL
SELECT 'OA PAKAN', SUM(kpop.total+kpop.potongan_pph_23) FROM konfirmasi_pembayaran_oa_pakan kpop WHERE kpop.tgl_bayar<@start
),
-- Operasional kredit saldo awal per jenis
op_krd AS (
-- DOC: transfer+pph
SELECT 'DOC' jenis, SUM(CASE WHEN k.tanggal<='2025-09-20' THEN rpd.transfer ELSE rpd.transfer+ISNULL(k.pph,0) END) v
FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
LEFT JOIN (SELECT kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar tanggal, ((kpdd.total-CASE WHEN kpd.tgl_bayar>='2026-01-01' THEN ISNULL((SELECT SUM(cpd2.pakai) FROM cn_post_det cpd2 WHERE cpd2.nomor=kpd.nomor),0) ELSE 0 END)*0.0025) pph FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id LEFT JOIN (SELECT td1.* FROM terima_doc td1 RIGHT JOIN (SELECT MAX(id) id, no_order FROM terima_doc GROUP BY no_order) td2 ON td1.id=td2.id) td ON td.no_order=kpdd.no_order GROUP BY kpdd.kode_unit,kpd.nomor,kpd.tgl_bayar,kpdd.total) k ON k.nomor=rpd.no_bayar
WHERE rpd.transaksi='DOC' AND rp.tgl_realisasi<@start
UNION ALL
-- PAKAN: transfer (no pph)
SELECT 'PAKAN', SUM(rpd.transfer) FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='PAKAN' AND rp.tgl_realisasi<@start
UNION ALL
-- VOADIP/OVK
SELECT REPLACE(rpd.transaksi,'VOADIP','OVK'), SUM(rpd.transfer) FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='VOADIP' AND rp.tgl_realisasi<@start GROUP BY rpd.transaksi
UNION ALL
-- PLASMA/RHPP
SELECT 'RHPP', SUM(rpd.transfer) FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='PLASMA' AND rp.tgl_realisasi<@start
UNION ALL
-- OA PAKAN
SELECT 'OA PAKAN', SUM(rpd.transfer) FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='OA PAKAN' AND rp.tgl_realisasi<@start
UNION ALL
-- CN per jenis
SELECT CASE WHEN cp.jenis_cn='PKN' THEN 'PAKAN' ELSE cp.jenis_cn END, SUM(cpd.pakai) FROM cn_post_det cpd JOIN cn_post cp ON cpd.id_header=cp.id WHERE cp.tanggal<@start GROUP BY cp.jenis_cn
)
SELECT g.jenis,
    CAST(ISNULL(d.v,0) AS decimal(18,2)) op_debet,
    CAST(ISNULL(k.v,0) AS decimal(18,2)) op_kredit,
    CAST(ISNULL(d.v,0)-ISNULL(k.v,0) AS decimal(18,2)) op_saldo,
    CAST(g.v AS decimal(18,2)) gl_saldo,
    CAST((ISNULL(d.v,0)-ISNULL(k.v,0))-g.v AS decimal(18,2)) selisih
FROM gl g
LEFT JOIN (SELECT jenis, SUM(v) v FROM op_dbt GROUP BY jenis) d ON d.jenis=g.jenis
LEFT JOIN (SELECT jenis, SUM(v) v FROM op_krd GROUP BY jenis) k ON k.jenis=g.jenis
ORDER BY ABS((ISNULL(d.v,0)-ISNULL(k.v,0))-g.v) DESC;
