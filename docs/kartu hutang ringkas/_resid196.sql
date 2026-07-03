SET NOCOUNT ON;
DECLARE @inv TABLE(nomor varchar(50));
INSERT @inv VALUES ('BYD/09/25/00100'),('BYD/09/25/00070'),('BYD/09/25/00116'),('BYD/09/25/00387');

SELECT
  i.nomor,
  -- konfir (debet)
  (SELECT CAST(SUM(kpdd.total) AS decimal(18,2)) FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id WHERE kpd.nomor=i.nomor) as konfir,
  (SELECT TOP 1 kpd.tgl_bayar FROM konfirmasi_pembayaran_doc kpd WHERE kpd.nomor=i.nomor) as tgl_bayar,
  -- bayar transfer (realisasi)
  (SELECT CAST(SUM(rpd.transfer) AS decimal(18,2)) FROM realisasi_pembayaran_det rpd WHERE rpd.no_bayar=i.nomor AND rpd.transaksi='DOC') as transfer,
  -- cn
  (SELECT CAST(SUM(cpd.pakai) AS decimal(18,2)) FROM cn_post_det cpd WHERE cpd.nomor=i.nomor) as cn,
  -- GL turun aktual (via invoice col, semua kecuali memo MM ini)
  (SELECT CAST(SUM(dj.nominal) AS decimal(18,2)) FROM det_jurnal dj WHERE dj.coa_tujuan='21180.200' AND dj.invoice=i.nomor AND dj.kode_trans NOT LIKE 'MM2606190004%') as gl_turun_nonmemo,
  -- PPh aktual di GL (bagian dari BYR: coa_asal=24622.000 -> 21180.200)
  (SELECT CAST(SUM(dj.nominal) AS decimal(18,2)) FROM det_jurnal dj WHERE dj.coa_tujuan='21180.200' AND dj.invoice=i.nomor AND dj.kode_trans LIKE 'BYR%' AND dj.coa_asal='24622.000') as gl_pph_aktual,
  -- PPh formula ringkas
  CAST( ( (SELECT SUM(kpdd.total) FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id WHERE kpd.nomor=i.nomor)
          - isnull((SELECT SUM(cpd.pakai) FROM cn_post_det cpd WHERE cpd.nomor=i.nomor),0) ) * 0.0025 AS decimal(18,4)) as pph_formula,
  -- memo MM2606190004
  (SELECT CAST(SUM(mi.nilai) AS decimal(18,2)) FROM mmitem mi WHERE mi.no_mm='MM2606190004' AND mi.no_invoice=i.nomor) as memo196
FROM @inv i;
