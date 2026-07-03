SET NOCOUNT ON;
PRINT '=== konfir OA det no_sj sample (apakah bisa map ke unit?) ===';
SELECT TOP 8 kpopd.no_sj, kpop.nomor AS byo
FROM konfirmasi_pembayaran_oa_pakan_det kpopd
JOIN konfirmasi_pembayaran_oa_pakan kpop ON kpopd.id_header=kpop.id
ORDER BY kpop.id DESC;
PRINT '=== apakah 1 BYO (pembayaran) mencakup BANYAK unit? (cek via konfir det no_sj -> kirim_pakan no_order) ===';
SELECT TOP 10 kpop.nomor AS byo, COUNT(DISTINCT SUBSTRING(kp.no_order,4,3)) AS n_unit,
  CAST(SUM(kpopd.total) AS decimal(18,2)) total_byo
FROM konfirmasi_pembayaran_oa_pakan_det kpopd
JOIN konfirmasi_pembayaran_oa_pakan kpop ON kpopd.id_header=kpop.id
LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
GROUP BY kpop.nomor
ORDER BY COUNT(DISTINCT SUBSTRING(kp.no_order,4,3)) DESC;
PRINT '=== brp konfir det no_sj yg TIDAK ketemu di kirim_pakan (leg opks beda key) ===';
SELECT COUNT(*) total_det, SUM(CASE WHEN kp.id IS NULL THEN 1 ELSE 0 END) n_nomatch
FROM konfirmasi_pembayaran_oa_pakan_det kpopd LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj;
