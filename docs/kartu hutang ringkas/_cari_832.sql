SET NOCOUNT ON;
PRINT '=== cari 832701.50 / 852501.50 di det_jurnal 21212 ===';
SELECT dj.tanggal, dj.coa_asal, dj.coa_tujuan, dj.unit, dj.invoice, dj.kode_trans, CAST(dj.nominal AS decimal(18,2)) nominal
FROM det_jurnal dj WHERE (dj.coa_asal='21212.000' OR dj.coa_tujuan='21212.000') AND dj.nominal IN (832701.50, 852501.50, 832701.5, 852501.5);
PRINT '=== konfir OA MLG: total+pph = 832701.50? atau dekat ===';
SELECT kpop.nomor, kpop.tgl_bayar, CAST(kpop.total AS decimal(18,2)) total, CAST(kpop.potongan_pph_23 AS decimal(18,2)) pph
FROM konfirmasi_pembayaran_oa_pakan kpop WHERE kpop.total+kpop.potongan_pph_23 BETWEEN 830000 AND 855000;
PRINT '=== cek: realisasi OA dgn transfer dekat 832701 / 850000 ===';
SELECT rpd.no_bayar, rp.nomor byr, rp.tgl_realisasi, CAST(rpd.transfer AS decimal(18,2)) transfer
FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
WHERE rpd.transaksi='OA PAKAN' AND rpd.transfer BETWEEN 830000 AND 855000;
