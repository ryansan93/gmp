SET NOCOUNT ON;
DECLARE @inv TABLE(nomor varchar(50));
INSERT @inv VALUES ('BYD/10/25/00070'),('BYD/09/25/00100'),('BYD/12/25/00116'),('BYD/12/25/00387');
SELECT dj.invoice, dj.id dj_id, dj.kode_trans, dj.tanggal, dj.coa_asal, dj.coa_tujuan, dj.nominal, dj.unit, dj.tbl_name, dj.keterangan
FROM det_jurnal dj JOIN @inv i ON i.nomor=dj.invoice
WHERE dj.coa_asal='21180.200' OR dj.coa_tujuan='21180.200'
ORDER BY dj.invoice, dj.tanggal, dj.id;
