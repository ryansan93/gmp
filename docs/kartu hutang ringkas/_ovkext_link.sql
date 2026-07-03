SET NOCOUNT ON;
PRINT '=== sampel GL naik 21174 (coa_asal) JBR/MJK ==='
SELECT TOP 8 dj.kode_trans, dj.tanggal, dj.coa_asal, dj.coa_tujuan, dj.nominal, dj.unit, dj.invoice, dj.tbl_name, dj.keterangan
FROM det_jurnal dj WHERE dj.coa_asal='21174.000' AND dj.unit IN ('JBR','MJK') ORDER BY dj.tanggal DESC;
PRINT '=== sampel GL turun 21174 (coa_tujuan) ==='
SELECT TOP 8 dj.kode_trans, dj.tanggal, dj.coa_asal, dj.coa_tujuan, dj.nominal, dj.unit, dj.invoice, dj.keterangan
FROM det_jurnal dj WHERE dj.coa_tujuan='21174.000' ORDER BY dj.tanggal DESC;
PRINT '=== konfir voadip JBR/MJK (sampel) ==='
SELECT TOP 8 nomor, tgl_bayar, supplier, total FROM konfirmasi_pembayaran_voadip ORDER BY tgl_bayar DESC;
