SET NOCOUNT ON;
DECLARE @inv TABLE(nomor varchar(50));
INSERT @inv VALUES ('BYD/09/25/00100'),('BYD/09/25/00070'),('BYD/09/25/00116');

SELECT i.nomor,
  (SELECT TOP 1 kpdd.no_order FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id WHERE kpd.nomor=i.nomor) as no_order,
  (SELECT CAST(SUM(kpdd.total) AS decimal(18,2)) FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id WHERE kpd.nomor=i.nomor) as konfir,
  -- BBM naik via no_order
  (SELECT CAST(SUM(dj.nominal) AS decimal(18,2))
     FROM det_jurnal dj
     WHERE dj.tbl_name='terima_doc' AND dj.coa_asal='21180.200'
       AND dj.tbl_id IN (SELECT CAST(td.id AS varchar) FROM terima_doc td
                          WHERE td.no_order = (SELECT TOP 1 kpdd.no_order FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id WHERE kpd.nomor=i.nomor))
  ) as bbm_naik
FROM @inv i;
