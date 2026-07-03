SET NOCOUNT ON;
DECLARE @cut date='2026-06-01';
PRINT '=== GL 21213 NAIK per lawan akun + tbl (<Juni) ===';
SELECT dj.coa_tujuan, dj.tbl_name, COUNT(*) n, CAST(SUM(dj.nominal) AS decimal(18,2)) total
FROM det_jurnal dj WHERE dj.coa_asal='21213.000' AND dj.tanggal<@cut GROUP BY dj.coa_tujuan, dj.tbl_name ORDER BY SUM(dj.nominal) DESC;
PRINT '=== GL 21213 TURUN per lawan akun + tbl (<Juni) ===';
SELECT dj.coa_asal, dj.tbl_name, COUNT(*) n, CAST(SUM(dj.nominal) AS decimal(18,2)) total
FROM det_jurnal dj WHERE dj.coa_tujuan='21213.000' AND dj.tanggal<@cut GROUP BY dj.coa_asal, dj.tbl_name ORDER BY SUM(dj.nominal) DESC;
PRINT '=== Report: DEBET konfir peternak (total) vs KREDIT realisasi PLASMA (transfer), <Juni ===';
SELECT CAST((SELECT SUM(total) FROM konfirmasi_pembayaran_peternak WHERE tgl_bayar<@cut) AS decimal(18,2)) debet_konfir,
  CAST((SELECT SUM(rpd.transfer) FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='PLASMA' AND rp.tgl_realisasi<@cut) AS decimal(18,2)) kredit_transfer;
PRINT '=== saldo_bulanan 21213 total per snapshot terakhir (end-April & end-May) ===';
SELECT tanggal, CAST(SUM(saldo_akhir) AS decimal(18,2)) total, COUNT(*) n FROM saldo_bulanan WHERE coa='21213.000' GROUP BY tanggal ORDER BY tanggal DESC;
