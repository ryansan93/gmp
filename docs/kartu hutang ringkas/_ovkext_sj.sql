SET NOCOUNT ON;
PRINT '=== KONFIR SJ 047689 ==='
SELECT kpv.nomor, kpvd.no_sj, kpvd.kode_unit, kpvd.no_order, kpvd.jumlah, kpvd.total
FROM konfirmasi_pembayaran_voadip kpv JOIN konfirmasi_pembayaran_voadip_det kpvd ON kpvd.id_header=kpv.id
WHERE kpvd.no_sj='047689';
PRINT '=== GL TURUN 21174 yg keterangannya memuat 047689 ==='
SELECT dj.id, dj.kode_trans, dj.tanggal, dj.coa_asal, dj.coa_tujuan, dj.nominal, dj.unit, dj.keterangan
FROM det_jurnal dj WHERE dj.coa_tujuan='21174.000' AND dj.keterangan LIKE '%047689%';
PRINT '=== GL NAIK 21174 via terima_voadip (cari BBM utk SJ 047689) ==='
SELECT kv.* FROM kirim_voadip kv WHERE kv.no_sj='047689';
