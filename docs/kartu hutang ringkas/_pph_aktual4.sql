SET NOCOUNT ON;
DECLARE @inv TABLE(nomor varchar(50));
INSERT @inv VALUES ('BYD/10/25/00195'),('BYD/09/25/00118'),('BYD/11/25/00341'),('BYD/11/25/00340');
SELECT i.nomor,
  (SELECT SUM(kpdd.total) FROM konfirmasi_pembayaran_doc kpd JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id WHERE kpd.nomor=i.nomor) as konfir,
  CAST((SELECT SUM(kpdd.total) FROM konfirmasi_pembayaran_doc kpd JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id WHERE kpd.nomor=i.nomor)*0.0025 AS decimal(18,2)) as pph_formula,
  CAST(ISNULL((SELECT SUM(dj.nominal) FROM det_jurnal dj WHERE dj.coa_asal='24622.000' AND dj.coa_tujuan='21180.200' AND dj.invoice=i.nomor),0) AS decimal(18,2)) as pph_aktual_GL,
  CAST(ISNULL((SELECT SUM(mi.nilai) FROM mmitem mi WHERE mi.coa_asal='12040.000' AND mi.coa_tujuan='21180.200' AND mi.no_invoice=i.nomor),0) AS decimal(18,2)) as memo_persediaan,
  -- pph kalau basis = (konfir - memo)
  CAST(((SELECT SUM(kpdd.total) FROM konfirmasi_pembayaran_doc kpd JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id WHERE kpd.nomor=i.nomor)
        - ISNULL((SELECT SUM(mi.nilai) FROM mmitem mi WHERE mi.coa_asal='12040.000' AND mi.coa_tujuan='21180.200' AND mi.no_invoice=i.nomor),0))*0.0025 AS decimal(18,2)) as pph_formula_baru
FROM @inv i;
