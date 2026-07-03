SET NOCOUNT ON;
DECLARE @start date='2026-06-01';
PRINT '=== sample det_jurnal naik 21212 unit=MGT (lihat field linkage) ===';
SELECT TOP 8 dj.tanggal, dj.unit, dj.tbl_name, dj.tbl_id, dj.kode_trans, dj.no_bukti, dj.invoice, dj.ref_id, dj.ref_kode, CAST(dj.nominal AS decimal(18,2)) nominal
FROM det_jurnal dj WHERE dj.coa_asal='21212.000' AND dj.unit='MGT' AND dj.tanggal<@start ORDER BY dj.tanggal DESC;
PRINT '=== cek apakah tbl_id cocok ke terima_pakan.id ===';
SELECT TOP 3 dj.tbl_id, tp.id AS tp_id, tp.no_bbm FROM det_jurnal dj LEFT JOIN terima_pakan tp ON tp.id=dj.tbl_id WHERE dj.coa_asal='21212.000' AND dj.tanggal<@start ORDER BY dj.id DESC;
PRINT '=== distinct tbl_name utk naik 21212 ===';
SELECT dj.tbl_name, COUNT(*) n FROM det_jurnal dj WHERE dj.coa_asal='21212.000' AND dj.tanggal<@start GROUP BY dj.tbl_name;
