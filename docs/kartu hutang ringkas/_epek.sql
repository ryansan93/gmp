SET NOCOUNT ON;
PRINT '=== RHPP EPEK ALAMSYAH / INV/RHPP/MLG/26/05/0033 ===';
SELECT id, invoice, mitra, noreg, jenis, CAST(pdpt_peternak_sudah_pajak AS decimal(18,2)) pdpt FROM rhpp WHERE invoice='INV/RHPP/MLG/26/05/0033' OR mitra LIKE '%EPEK%';
PRINT '=== konfir utk invoice itu ===';
SELECT nomor, invoice, mitra, CAST(total AS decimal(18,2)) total FROM konfirmasi_pembayaran_peternak WHERE invoice='INV/RHPP/MLG/26/05/0033';
PRINT '=== realisasi utk konfir EPEK ===';
SELECT rp.nomor bukti, rpd.no_bayar, CAST(rpd.transfer AS decimal(18,2)) transfer, rp.tgl_realisasi, rp.keterangan
FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
WHERE rpd.no_bayar IN (SELECT nomor FROM konfirmasi_pembayaran_peternak WHERE invoice='INV/RHPP/MLG/26/05/0033');
PRINT '=== SEMUA det_jurnal terkait EPEK / INV/RHPP/MLG/26/05/0033 / PM2605001 ===';
SELECT tbl_name, unit, tanggal, coa_asal, coa_tujuan, CAST(nominal AS decimal(18,2)) nominal, keterangan, no_bukti, kode_jurnal
FROM det_jurnal WHERE keterangan LIKE '%EPEK ALAMSYAH%' OR invoice='INV/RHPP/MLG/26/05/0033' OR keterangan LIKE '%INV/RHPP/MLG/26/05/0033%' ORDER BY tanggal;
PRINT '=== rhpp_piutang / rhpp_group_piutang utk EPEK (PM2605001?) ===';
SELECT 'single' src, p.* FROM rhpp_piutang p WHERE p.piutang_kode='PM2605001';
SELECT 'group' src, p.* FROM rhpp_group_piutang p WHERE p.piutang_kode='PM2605001';
