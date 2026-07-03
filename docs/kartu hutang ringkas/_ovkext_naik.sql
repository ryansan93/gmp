SET NOCOUNT ON;
PRINT '=== GL NAIK 21174 (BBM via terima_voadip) utk SJ 047689 / order MJK 10001+10005 ==='
SELECT dj.id, dj.kode_trans, dj.tanggal, dj.coa_asal, dj.coa_tujuan, dj.nominal, dj.unit, kv.no_sj, kv.no_order
FROM det_jurnal dj
JOIN terima_voadip tv ON dj.tbl_name='terima_voadip' AND dj.tbl_id=CAST(tv.id AS varchar)
JOIN kirim_voadip kv ON tv.id_kirim_voadip=kv.id
WHERE dj.coa_asal='21174.000' AND kv.no_sj='047689';
PRINT '=== RINGKAS SJ 047689 ==='
SELECT
 (SELECT SUM(kpvd.total) FROM konfirmasi_pembayaran_voadip_det kpvd WHERE kpvd.no_sj='047689') as konfir,
 (SELECT SUM(dj.nominal) FROM det_jurnal dj JOIN terima_voadip tv ON dj.tbl_name='terima_voadip' AND dj.tbl_id=CAST(tv.id AS varchar) JOIN kirim_voadip kv ON tv.id_kirim_voadip=kv.id WHERE dj.coa_asal='21174.000' AND kv.no_sj='047689') as gl_naik,
 (SELECT SUM(dj.nominal) FROM det_jurnal dj WHERE dj.coa_tujuan='21174.000' AND dj.keterangan LIKE '%047689%') as gl_turun;
