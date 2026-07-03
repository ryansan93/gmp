SET NOCOUNT ON;
PRINT '=== mmitem MM2601310039 ==='
SELECT no_urut, coa_asal, coa_tujuan, nilai, no_invoice, keterangan FROM mmitem WHERE no_mm='MM2601310039' ORDER BY no_urut;
PRINT '=== det_jurnal MM2601310039 ==='
SELECT id, tanggal, coa_asal, coa_tujuan, nominal, unit, invoice, keterangan FROM det_jurnal WHERE kode_trans='MM2601310039' ORDER BY id;
PRINT '=== nama COA terkait ==='
SELECT no_coa, nama FROM coa WHERE no_coa IN ('21180.200','12040.000','71103.000','71400.000','12020.000','24622.000','96010.000','71105.003') ORDER BY no_coa;
