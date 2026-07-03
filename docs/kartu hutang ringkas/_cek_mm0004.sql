SET NOCOUNT ON;
SELECT 'mm' src, no_mm, tgl_mm, unit, no_supplier, keterangan FROM mm WHERE no_mm='MM2606190004';
SELECT 'mmitem' src, no_mm, no_invoice, coa_asal, coa_tujuan, nilai, tgl_mm, keterangan FROM mmitem WHERE no_mm='MM2606190004';
SELECT 'det_jurnal' src, id dj_id, kode_trans, tanggal, coa_asal, coa_tujuan, nominal, invoice, keterangan
FROM det_jurnal WHERE kode_trans='MM2606190004' ORDER BY id;
