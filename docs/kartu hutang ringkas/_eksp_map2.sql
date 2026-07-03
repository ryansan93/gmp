SET NOCOUNT ON;
PRINT '=== map nama opp.ekspedisi -> ekspedisi_id (4 orphan pindah) ===';
SELECT opp.id, opp.no_sj, opp.ekspedisi AS nama_di_pindah, e.nomor AS ekspedisi_id
FROM oa_pindah_pakan opp
LEFT JOIN ekspedisi e ON e.nama = opp.ekspedisi
WHERE opp.id IN (639,907,908,957);
PRINT '=== GSK BYO/01/26/00105 ===';
SELECT (SELECT COUNT(*) FROM realisasi_pembayaran_det WHERE no_bayar='BYO/01/26/00105') AS n_realisasi,
  CAST((SELECT SUM(nominal) FROM det_jurnal WHERE coa_tujuan='21212.000' AND invoice='BYO/01/26/00105') AS decimal(18,2)) AS gl_turun_total,
  CAST((SELECT total+potongan_pph_23 FROM konfirmasi_pembayaran_oa_pakan WHERE nomor='BYO/01/26/00105') AS decimal(18,2)) AS bruto;
