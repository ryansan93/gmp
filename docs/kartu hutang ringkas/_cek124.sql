SET NOCOUNT ON;
PRINT '=== BBM naik utk BYV/11/25/00124 (cek COA: 21174 extern atau 21180.300 biasa) ==='
SELECT kpvd.no_order, kpvd.no_sj, kpvd.kode_unit, kpvd.total as konfir_det
FROM konfirmasi_pembayaran_voadip kpv JOIN konfirmasi_pembayaran_voadip_det kpvd ON kpvd.id_header=kpv.id
WHERE kpv.nomor='BYV/11/25/00124';
PRINT '=== semua baris voucher BYR/11/25/00326 utk SJ 04776 ==='
SELECT id, coa_asal, coa_tujuan, nominal, unit, keterangan FROM det_jurnal
WHERE kode_trans='BYR/11/25/00326' AND keterangan LIKE '%04776%' ORDER BY id;
PRINT '=== BBM naik (jurnal) yg ke 21180.300 atau 21174 utk order 00124 ==='
SELECT dj.id, dj.kode_trans, dj.coa_asal, dj.coa_tujuan, dj.nominal, dj.unit, dj.keterangan
FROM det_jurnal dj JOIN terima_voadip tv ON dj.tbl_name='terima_voadip' AND dj.tbl_id=CAST(tv.id AS varchar)
JOIN kirim_voadip kv ON tv.id_kirim_voadip=kv.id
WHERE kv.no_sj='04776' AND (dj.coa_asal IN ('21174.000','21180.300'));
