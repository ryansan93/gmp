SET NOCOUNT ON;
-- mmitem koreksi persediaan utk invoice ini
SELECT 'mi_inv' src, no_mm, no_invoice, coa_asal, coa_tujuan, nilai, tgl_mm, keterangan
FROM mmitem WHERE no_invoice='BYD/10/25/00195';
-- seberapa luas pola: mmitem coa_asal=12040.000 -> coa_tujuan=21180.200 (koreksi persediaan DOC turun hutang)
SELECT 'scope' src, COUNT(*) jml_baris, COUNT(DISTINCT no_invoice) jml_invoice, SUM(nilai) total_nilai
FROM mmitem WHERE coa_asal='12040.000' AND coa_tujuan='21180.200' AND no_invoice LIKE 'BYD/%';
