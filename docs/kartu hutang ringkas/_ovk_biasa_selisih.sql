/* ============================================================================
   REKONSILIASI OVK BIASA (COA 21180.300) — ringkas vs GL, per unit & per dokumen
   ----------------------------------------------------------------------------
   Konteks: laporan GL vs Laporan Hutang Ringkas untuk OVK.
   - OVK EXTERN (21174.000) sudah TUNTAS (selisih -0,68 = pembulatan sen JBR 0,48 + MJK 0,20).
   - OVK BIASA (21180.300) selisih total -458.809,18, seluruhnya di SISI NAIK,
     terurai jadi 5 dokumen (1 per unit) + sen JBR/MJK.

   METODE (sama persis dgn yg cocok 100% di EXTERN):
     selisih per unit = ringkas(saldo_akhir) - GL(saldo_bulanan)
       ringkas = konfir(<cutoff) - transfer(<cutoff) - memo_turun(<cutoff)
       GL      = -SUM(saldo_bulanan.saldo_awal) pada tanggal cutoff
     CUTOFF harus disamakan! (saldo_bulanan@2026-06-01 = posisi akhir Mei;
     kalau ringkas all-time, pembayaran Juni bikin selisih palsu besar.)

   PENENGAH: det_stok = FISIK terima_voadip (qty x hrg_beli). Pakai DISTINCT
     (kode_trans,kode_barang,jumlah,hrg_beli) utk buang duplikasi snapshot harian.
     - det_stok = konfir  -> jurnal BBM yg salah  -> benahi MEMORIAL
     - det_stok = jurnal  -> konfir yg salah       -> benahi KONFIRMASI

   AI READ-ONLY DB (hanya edit controller). Koreksi data oleh user.
   Tanggal: 2026-06-22
   ============================================================================ */

-- ---------------------------------------------------------------------------
-- 1) SELISIH PER UNIT (ringkas vs saldo_bulanan, cutoff disamakan)
-- ---------------------------------------------------------------------------
DECLARE @start date='2026-06-01';
WITH biasa_ord AS (
  SELECT DISTINCT kv.no_order FROM det_jurnal dj
  JOIN terima_voadip tv ON dj.tbl_name='terima_voadip' AND dj.tbl_id=CAST(tv.id AS varchar)
  JOIN kirim_voadip kv ON tv.id_kirim_voadip=kv.id WHERE dj.coa_asal='21180.300'
),
biasa_inv AS (
  SELECT kpv.nomor inv, MIN(kpvd.kode_unit) unit FROM konfirmasi_pembayaran_voadip kpv
  JOIN konfirmasi_pembayaran_voadip_det kpvd ON kpvd.id_header=kpv.id
  JOIN biasa_ord e ON e.no_order=kpvd.no_order
  WHERE kpv.tgl_bayar < @start GROUP BY kpv.nomor
),
konf AS (SELECT kpv.nomor inv, SUM(kpvd.total) v FROM konfirmasi_pembayaran_voadip kpv JOIN konfirmasi_pembayaran_voadip_det kpvd ON kpvd.id_header=kpv.id WHERE kpv.tgl_bayar < @start GROUP BY kpv.nomor),
byr  AS (SELECT rpd.no_bayar inv, SUM(rpd.transfer) v FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='VOADIP' AND rp.tgl_realisasi IS NOT NULL AND rp.tgl_realisasi < @start GROUP BY rpd.no_bayar),
memo_t AS (SELECT mi.no_invoice inv, SUM(mi.nilai) v FROM mmitem mi WHERE mi.coa_tujuan='21180.300' AND CAST(mi.tgl_mm AS date) < @start GROUP BY mi.no_invoice),
gl AS (SELECT unit, CAST(-SUM(saldo_awal) AS decimal(18,2)) gl_saldo FROM saldo_bulanan WHERE coa='21180.300' AND tanggal=@start GROUP BY unit)
SELECT i.unit,
  CAST(SUM(ISNULL(k.v,0))-SUM(ISNULL(b.v,0))-SUM(ISNULL(m.v,0)) AS decimal(18,2)) ringkas,
  CAST(MAX(g.gl_saldo) AS decimal(18,2)) gl_saldo,
  CAST(SUM(ISNULL(k.v,0))-SUM(ISNULL(b.v,0))-SUM(ISNULL(m.v,0)) - MAX(g.gl_saldo) AS decimal(18,2)) selisih
FROM biasa_inv i
LEFT JOIN konf k ON k.inv=i.inv LEFT JOIN byr b ON b.inv=i.inv LEFT JOIN memo_t m ON m.inv=i.inv LEFT JOIN gl g ON g.unit=i.unit
GROUP BY i.unit
ORDER BY ABS(SUM(ISNULL(k.v,0))-SUM(ISNULL(b.v,0))-SUM(ISNULL(m.v,0)) - MAX(g.gl_saldo)) DESC;
/* HASIL: 8 unit klop 0; material: GSK -555.000, LMG +240.000, PSR -146.158,50,
   KDR +1.350, BWI +1.000; sen JBR -0,48 MJK -0,20. Total = -458.809,18 (= sisi naik). */


-- ---------------------------------------------------------------------------
-- 2) DOKUMEN PENYEBAB (matched pair: konfir vs GL-naik per no_order)
--    Saring n.gl_naik>0 (yg gl_naik=0 = noise link no_order OVO vs SJ).
-- ---------------------------------------------------------------------------
WITH naik AS (
  SELECT kv.no_order, CAST(SUM(dj.nominal) AS decimal(18,2)) gl_naik
  FROM det_jurnal dj
  JOIN terima_voadip tv ON dj.tbl_name='terima_voadip' AND dj.tbl_id=CAST(tv.id AS varchar)
  JOIN kirim_voadip kv ON tv.id_kirim_voadip=kv.id
  WHERE dj.coa_asal='21180.300' GROUP BY kv.no_order
),
konf AS (
  SELECT kpvd.no_order, MIN(kpvd.kode_unit) unit, MIN(kpv.nomor) invoice, CAST(SUM(kpvd.total) AS decimal(18,2)) konfir
  FROM konfirmasi_pembayaran_voadip kpv JOIN konfirmasi_pembayaran_voadip_det kpvd ON kpvd.id_header=kpv.id
  GROUP BY kpvd.no_order
)
SELECT k.unit, k.invoice, k.no_order, k.konfir, n.gl_naik, CAST(k.konfir-n.gl_naik AS decimal(18,2)) beda
FROM konf k JOIN naik n ON n.no_order=k.no_order
WHERE ABS(k.konfir-n.gl_naik) > 0.001
ORDER BY ABS(k.konfir-n.gl_naik) DESC;


-- ---------------------------------------------------------------------------
-- 3) TABEL FINAL: terima(det_stok=fisik) vs konfir vs jurnal BBM vs bayar
--    + waktu input jurnal (log_tables.waktu)
-- ---------------------------------------------------------------------------
DECLARE @t TABLE(unit varchar(5), invoice varchar(30), no_order varchar(30));
INSERT INTO @t VALUES
('GSK','BYV/01/26/00004','OVO/GSK/25/11011'),
('LMG','BYV/12/25/00080','OVO/LMG/25/10008'),
('PSR','BYV/05/26/00123','OVO/PSR/26/05011'),
('KDR','BYV/01/26/00070','OVO/KDR/25/11006'),
('BWI','BYV/05/26/00070','OVO/BWI/26/05012');
WITH ds_terima AS (   -- FISIK dari det_stok (DISTINCT buang snapshot harian)
  SELECT kode_trans, CAST(SUM(jumlah*hrg_beli) AS decimal(18,2)) terima_detstok
  FROM (SELECT DISTINCT kode_trans, kode_barang, jumlah, hrg_beli
        FROM det_stok WHERE jenis_barang='voadip' AND jenis_trans='ORDER'
          AND kode_trans IN ('OVO/GSK/25/11011','OVO/LMG/25/10008','OVO/PSR/26/05011','OVO/KDR/25/11006','OVO/BWI/26/05012')) d
  GROUP BY kode_trans
),
terima AS (
  SELECT kv.no_order, CAST(SUM(dj.nominal) AS decimal(18,2)) bbm_nilai, MIN(dj.tanggal) jurnal_tgl_terima, MIN(tv.id) terima_id
  FROM det_jurnal dj JOIN terima_voadip tv ON dj.tbl_name='terima_voadip' AND dj.tbl_id=CAST(tv.id AS varchar)
  JOIN kirim_voadip kv ON tv.id_kirim_voadip=kv.id WHERE dj.coa_asal='21180.300' GROUP BY kv.no_order
),
konf AS (SELECT kpvd.no_order, CAST(SUM(kpvd.total) AS decimal(18,2)) konfir FROM konfirmasi_pembayaran_voadip kpv JOIN konfirmasi_pembayaran_voadip_det kpvd ON kpvd.id_header=kpv.id GROUP BY kpvd.no_order),
bayar AS (SELECT rpd.no_bayar inv, MIN(rp.tgl_realisasi) tgl_bayar, MIN(rp.id) rp_id, CAST(SUM(rpd.transfer) AS decimal(18,2)) bayar_nominal FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='VOADIP' GROUP BY rpd.no_bayar)
SELECT t.unit, t.invoice, t.no_order,
  ds.terima_detstok AS terima_doc,            -- FISIK (det_stok)
  k.konfir AS konfir_pembayaran_voadip,
  CONVERT(varchar(19), ltt.waktu, 120)+' / '+CAST(te.bbm_nilai AS varchar(30))  AS jurnal_waktu_terima_doc,  -- BBM/GL-naik
  ISNULL(CONVERT(varchar(19), ltb.waktu, 120)+' / '+CAST(b.bayar_nominal AS varchar(30)),'belum dibayar') AS jurnal_waktu_bayar
FROM @t t
LEFT JOIN ds_terima ds ON ds.kode_trans=t.no_order
LEFT JOIN terima te ON te.no_order=t.no_order
LEFT JOIN konf k ON k.no_order=t.no_order
LEFT JOIN bayar b ON b.inv=t.invoice
LEFT JOIN (SELECT tbl_id, MIN(waktu) waktu FROM log_tables WHERE tbl_name='terima_voadip' GROUP BY tbl_id) ltt ON ltt.tbl_id=te.terima_id
LEFT JOIN (SELECT tbl_id, MIN(waktu) waktu FROM log_tables WHERE tbl_name='realisasi_pembayaran' GROUP BY tbl_id) ltb ON ltb.tbl_id=b.rp_id
ORDER BY t.unit;

/* ============================================================================
   HASIL & DIAGNOSA (2026-06-22)
   ----------------------------------------------------------------------------
   Unit | Invoice         | Order            | det_stok(fisik) | konfir       | jurnal BBM      | diagnosa
   GSK  | BYV/01/26/00004 | OVO/GSK/25/11011 | 48.151.800,00   | 48.151.800   | 48.706.800 (+555.000) | BBM kelebihan -> MEMORIAL (turunkan)
   LMG  | BYV/12/25/00080 | OVO/LMG/25/10008 | 6.182.640,00    | 6.182.640    | 5.942.640 (-240.000)  | BBM kurang   -> MEMORIAL (naikkan)
   KDR  | BYV/01/26/00070 | OVO/KDR/25/11006 | 2.009.265,00    | 2.009.265    | 2.007.915 (-1.350)    | BBM kurang   -> MEMORIAL (naikkan)
   BWI  | BYV/05/26/00070 | OVO/BWI/26/05012 | 20.622.360,00   | 20.623.360   | 20.622.360 (cocok)    | KONFIR +1.000 salah -> benahi KONFIRMASI
   PSR  | BYV/05/26/00123 | OVO/PSR/26/05011 | 5.392.798,50    | 5.246.640    | 5.392.798,50 (cocok)  | KONFIR -146.158,50 -> benahi KONFIRMASI

   ATURAN: det_stok=konfir -> jurnal BBM salah (MEMORIAL).
           det_stok=jurnal -> konfir salah (KONFIRMASI).
   waktu_input jurnal terima vs bayar sering beda bulan -> perhatikan periode memorial.
   ============================================================================ */
