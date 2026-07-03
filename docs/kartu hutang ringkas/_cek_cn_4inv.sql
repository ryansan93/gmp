SET NOCOUNT ON;
DECLARE @inv TABLE(nomor varchar(50));
INSERT @inv VALUES ('BYD/09/25/00100'),('BYD/09/25/00070'),('BYD/09/25/00116'),('BYD/09/25/00387');

SELECT i.nomor,
  (SELECT COUNT(*) FROM cn_post_det cpd WHERE cpd.nomor = i.nomor) as ada_di_cn_post_det,
  (SELECT CAST(SUM(cpd.pakai) AS decimal(18,2)) FROM cn_post_det cpd WHERE cpd.nomor = i.nomor) as total_pakai_cn
FROM @inv i;
