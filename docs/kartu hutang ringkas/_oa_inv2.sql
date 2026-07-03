SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

PRINT '=== (A) OA realisasi: breakdown semua komponen ===';
SELECT 
  CAST(SUM(rpd.transfer) AS decimal(18,2)) transfer,
  CAST(SUM(rpd.pph) AS decimal(18,2)) pph,
  CAST(SUM(rpd.cn) AS decimal(18,2)) cn,
  CAST(SUM(rpd.potongan) AS decimal(18,2)) potongan,
  CAST(SUM(rpd.uang_muka) AS decimal(18,2)) uang_muka,
  CAST(SUM(rpd.dn) AS decimal(18,2)) dn,
  CAST(SUM(rpd.bayar) AS decimal(18,2)) bayar,
  CAST(SUM(rpd.tagihan) AS decimal(18,2)) tagihan
FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
WHERE rpd.transaksi='OA PAKAN' AND rp.tgl_realisasi<@start;

PRINT '=== (B) 27001 clearing entri detail ===';
SELECT TOP 30 dj.tanggal, CAST(dj.nominal AS decimal(18,2)) nominal, dj.tbl_name, dj.invoice, dj.kode_trans, CAST(CAST(dj.keterangan AS varchar(80)) AS varchar(80)) ket
FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.coa_asal='27001.000' AND dj.tanggal<@start ORDER BY dj.tanggal;

PRINT '=== (C) overlap D1/D2 top no_sj freight blm-konfir ===';
SELECT TOP 15 kp.no_sj, CAST(SUM(dtp.jumlah)*kp.ongkos_angkut AS decimal(18,2)) freight
FROM det_terima_pakan dtp LEFT JOIN terima_pakan tp ON dtp.id_header=tp.id LEFT JOIN kirim_pakan kp ON tp.id_kirim_pakan=kp.id
WHERE tp.tgl_terima<@start AND kp.jenis_kirim='opkg'
  AND NOT EXISTS(SELECT * FROM konfirmasi_pembayaran_oa_pakan_det kpopd WHERE kpopd.no_sj=kp.no_sj)
GROUP BY kp.no_sj, tp.no_bbm, kp.ongkos_angkut ORDER BY freight DESC;
