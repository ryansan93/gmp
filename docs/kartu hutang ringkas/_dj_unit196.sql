SET NOCOUNT ON;
SELECT id, kode_trans, tanggal, coa_asal, coa_tujuan, nominal, invoice, unit, noreg
FROM det_jurnal WHERE id IN (915628,915629,915630,915631);
