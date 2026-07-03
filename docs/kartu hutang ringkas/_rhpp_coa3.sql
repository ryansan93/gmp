SET NOCOUNT ON;
PRINT '=== coa kandidat RHPP/plasma/peternak/panen ===';
SELECT DISTINCT coa, nama_coa FROM coa WHERE nama_coa LIKE '%PLASMA%' OR nama_coa LIKE '%RHPP%' OR nama_coa LIKE '%PETERNAK%' OR nama_coa LIKE '%PANEN%' OR nama_coa LIKE '%MITRA%' OR nama_coa LIKE '%BAGI%' ORDER BY coa;
PRINT '=== det_jurnal: realisasi PLASMA -> coa apa? (lewat no_bukti/keterangan PLASMA) ===';
SELECT TOP 10 dj.coa_asal, dj.coa_tujuan, dj.tbl_name, COUNT(*) n, CAST(SUM(dj.nominal) AS decimal(18,2)) total
FROM det_jurnal dj WHERE dj.tbl_name='realisasi_pembayaran' AND CAST(dj.keterangan AS varchar(50)) LIKE '%PLASMA%'
GROUP BY dj.coa_asal, dj.coa_tujuan, dj.tbl_name ORDER BY COUNT(*) DESC;
