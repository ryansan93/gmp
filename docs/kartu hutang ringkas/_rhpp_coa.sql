SET NOCOUNT ON;
PRINT '=== cari COA hutang RHPP via det_jurnal (invoice INV/RHPP atau tbl konfirmasi_pembayaran_peternak) ===';
SELECT TOP 20 dj.coa_asal, dj.coa_tujuan, dj.tbl_name, COUNT(*) n, CAST(SUM(dj.nominal) AS decimal(18,2)) total
FROM det_jurnal dj
WHERE (dj.invoice LIKE 'INV/RHPP%' OR dj.tbl_name IN ('konfirmasi_pembayaran_peternak'))
GROUP BY dj.coa_asal, dj.coa_tujuan, dj.tbl_name ORDER BY COUNT(*) DESC;
PRINT '=== nama coa kandidat (cari di coa yg mengandung PLASMA/RHPP/PETERNAK/BAGI HASIL) ===';
SELECT no_coa, nama FROM coa WHERE nama LIKE '%PLASMA%' OR nama LIKE '%RHPP%' OR nama LIKE '%PETERNAK%' OR nama LIKE '%BAGI HASIL%' OR nama LIKE '%PANEN%';
