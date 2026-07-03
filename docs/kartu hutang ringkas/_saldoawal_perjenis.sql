SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

-- GL saldo awal per COA hutang
SELECT
    CASE coa
        WHEN '21180.200' THEN 'DOC'
        WHEN '21173.000' THEN 'DOC (ext)'
        WHEN '21180.100' THEN 'PAKAN'
        WHEN '21172.000' THEN 'PAKAN (ext)'
        WHEN '21180.300' THEN 'OVK'
        WHEN '21174.000' THEN 'OVK EXTERN'
        WHEN '21213.000' THEN 'RHPP'
        WHEN '21213.001' THEN 'RHPP.001'
        WHEN '21212.000' THEN 'OA PAKAN'
        WHEN '21299.000' THEN 'PERALATAN'
        WHEN '21201.000' THEN 'LAIN 21201'
        WHEN '21211.000' THEN 'LAIN 21211'
        ELSE coa END jenis_gl,
    CAST(SUM(naik) AS decimal(18,2)) gl_naik,
    CAST(SUM(turun) AS decimal(18,2)) gl_turun,
    CAST(SUM(naik)-SUM(turun) AS decimal(18,2)) gl_saldo
FROM (
    SELECT coa_asal coa, nominal naik, 0 turun FROM det_jurnal WHERE coa_asal IN ('21180.200','21173.000','21180.100','21172.000','21180.300','21174.000','21213.000','21213.001','21212.000','21299.000','21201.000','21211.000') AND tanggal < @start
    UNION ALL
    SELECT coa_tujuan, 0, nominal FROM det_jurnal WHERE coa_tujuan IN ('21180.200','21173.000','21180.100','21172.000','21180.300','21174.000','21213.000','21213.001','21212.000','21299.000','21201.000','21211.000') AND tanggal < @start
) z GROUP BY coa ORDER BY gl_saldo DESC;

PRINT '';
PRINT '=== Operasional saldo awal per jenis ===';
WITH sa_debet AS (
    -- DOC (basis tgl_bayar setelah fix)
    SELECT 'DOC' jenis, SUM(kpdd.total) v FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id WHERE kpd.tgl_bayar < @start
    UNION ALL
    -- PAKAN
    SELECT 'PAKAN', SUM(kppd.total) FROM konfirmasi_pembayaran_pakan_det kppd JOIN konfirmasi_pembayaran_pakan kpp ON kppd.id_header=kpp.id WHERE kpp.tgl_bayar < @start
    UNION ALL
    -- OVK (semua voadip)
    SELECT pc.kode, SUM(kpv.total) FROM konfirmasi_pembayaran_voadip kpv LEFT JOIN (SELECT * FROM pelanggan_coa WHERE kode LIKE '%OVK%') pc ON pc.no_pelanggan=kpv.supplier WHERE kpv.tgl_bayar < @start GROUP BY pc.kode
    UNION ALL
    -- RHPP
    SELECT 'RHPP', SUM(kpp.total) FROM konfirmasi_pembayaran_peternak kpp WHERE kpp.tgl_bayar < @start
    UNION ALL
    -- OA PAKAN
    SELECT 'OA PAKAN', SUM(kpop.total+kpop.potongan_pph_23) FROM konfirmasi_pembayaran_oa_pakan kpop WHERE kpop.tgl_bayar < @start
),
sa_kredit AS (
    -- Transfer+PPh DOC
    SELECT 'DOC' jenis, SUM(CASE WHEN k.tanggal <= '2025-09-20' THEN rpd.transfer ELSE rpd.transfer+k.pph END) v
    FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
    LEFT JOIN (SELECT kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar tanggal, ((kpdd.total - CASE WHEN kpd.tgl_bayar>='2026-01-01' THEN ISNULL((SELECT SUM(cpd.pakai) FROM cn_post_det cpd WHERE cpd.nomor=kpd.nomor),0) ELSE 0 END)*0.0025) pph FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id LEFT JOIN (SELECT td1.* FROM terima_doc td1 RIGHT JOIN (SELECT MAX(id) id, no_order FROM terima_doc GROUP BY no_order) td2 ON td1.id=td2.id) td ON td.no_order=kpdd.no_order GROUP BY kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar, kpdd.total) k ON k.nomor=rpd.no_bayar
    WHERE rpd.transaksi='DOC' AND rp.tgl_realisasi < @start
    UNION ALL
    -- Transfer PAKAN
    SELECT 'PAKAN', SUM(rpd.transfer) FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='PAKAN' AND rp.tgl_realisasi < @start
    UNION ALL
    -- Transfer OVK
    SELECT 'OVK', SUM(rpd.transfer) FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='OVK' AND rp.tgl_realisasi < @start
    UNION ALL
    -- Transfer RHPP
    SELECT 'RHPP', SUM(rpd.transfer) FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='RHPP' AND rp.tgl_realisasi < @start
    UNION ALL
    -- CN semua jenis
    SELECT CASE WHEN cp.jenis_cn='PKN' THEN 'PAKAN' ELSE cp.jenis_cn END, SUM(cpd.pakai) FROM cn_post_det cpd JOIN cn_post cp ON cpd.id_header=cp.id WHERE cp.tanggal < @start GROUP BY cp.jenis_cn
)
SELECT jenis,
    CAST(SUM(debet) AS decimal(18,2)) op_debet,
    CAST(SUM(kredit) AS decimal(18,2)) op_kredit,
    CAST(SUM(debet)-SUM(kredit) AS decimal(18,2)) op_saldo
FROM (
    SELECT jenis, v debet, 0 kredit FROM sa_debet
    UNION ALL
    SELECT jenis, 0, v FROM sa_kredit
) z GROUP BY jenis ORDER BY op_saldo DESC;
