SET NOCOUNT ON;
PRINT '=== konfir det no_sj 3016xxx: ada di kirim_pakan? jenis & no_order-nya? ===';
SELECT TOP 10 kpopd.no_sj, kp.jenis_kirim, kp.no_order, kp.id
FROM konfirmasi_pembayaran_oa_pakan_det kpopd
LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
WHERE kpopd.no_sj LIKE '3016%' ORDER BY kpopd.id DESC;
PRINT '=== brp det 3016xxx total & brp match kirim_pakan ===';
SELECT COUNT(*) n_3016, SUM(CASE WHEN kp.id IS NOT NULL THEN 1 ELSE 0 END) n_match_kp
FROM konfirmasi_pembayaran_oa_pakan_det kpopd LEFT JOIN kirim_pakan kp ON kp.no_sj=kpopd.no_sj
WHERE kpopd.no_sj LIKE '3016%';
PRINT '=== 3016xxx: coba map via terima_pakan/det? cari tabel lain yg punya no_sj 3016 ===';
SELECT TOP 5 no_sj, no_order, jenis_kirim FROM kirim_pakan WHERE no_sj LIKE '3016%';
PRINT '=== distribusi format no_sj di konfir OA det ===';
SELECT CASE WHEN no_sj LIKE 'SJ/%' THEN 'SJ/XXX' WHEN no_sj LIKE '3016%' THEN '3016xxx' ELSE 'lain' END fmt, COUNT(*) n
FROM konfirmasi_pembayaran_oa_pakan_det GROUP BY CASE WHEN no_sj LIKE 'SJ/%' THEN 'SJ/XXX' WHEN no_sj LIKE '3016%' THEN '3016xxx' ELSE 'lain' END;
