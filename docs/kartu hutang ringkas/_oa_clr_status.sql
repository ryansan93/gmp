SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
PRINT '=== status realisasi utk 26 BYO clearing: tgl_bayar konfir, tgl_realisasi, status ===';
SELECT k.nomor AS byo, k.tgl_bayar AS konfir_tgl,
  rp.nomor AS byr, rp.tgl_realisasi, rp.status, rp.coa_bank,
  CAST(rpd.transfer AS decimal(18,2)) transfer
FROM konfirmasi_pembayaran_oa_pakan k
LEFT JOIN realisasi_pembayaran_det rpd ON rpd.no_bayar=k.nomor AND rpd.transaksi='OA PAKAN'
LEFT JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
WHERE k.nomor IN (SELECT DISTINCT dj.invoice FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.coa_asal='27001.000' AND dj.tanggal<@start)
ORDER BY k.nomor;

PRINT '=== BYO_KOSONG (12,8jt): cari di det_jurnal entri turun invoice kosong ===';
SELECT dj.tanggal, dj.coa_asal, CAST(dj.nominal AS decimal(18,2)) nominal, dj.kode_trans, CAST(CAST(dj.keterangan AS varchar(90)) AS varchar(90)) ket
FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.tanggal<@start AND (dj.invoice IS NULL OR dj.invoice='')
ORDER BY dj.tanggal;
