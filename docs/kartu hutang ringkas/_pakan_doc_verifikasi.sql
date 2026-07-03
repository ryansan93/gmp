/* ============================================================================
   VERIFIKASI PAKAN (21180.100) & DOC (21180.200) — GL vs Hutang Ringkas
   per dokumen, dgn memo (mmitem) diperhitungkan.
   ----------------------------------------------------------------------------
   Kelanjutan dari _ovk_biasa_selisih.sql. Tujuan: tabel dokumen selisih utk
   PAKAN & DOC seperti yg dibuat untuk OVK biasa.

   PELAJARAN PENTING (jangan diulang):
   - Scan "fisik" generik TIDAK reliabel: terima_doc punya kolom `version`
     (SUM ganda); det_stok DISTINCT meng-collapse baris terima identik.
   - rpd.no_bayar TIDAK 1:1 utk di-join langsung ke konfir per unit -> join
     menggandakan (sempat keluar transfer 71 M palsu utk PAKAN). Selalu pakai
     CTE terpisah (konf / byr / memo) lalu LEFT JOIN by key, JANGAN join rpd
     langsung ke tabel konfir yg sudah di-group.
   - DOC: memorial koreksi DI-TAG ke invoice SPLIT -> per-invoice tak net,
     hanya net di level UNIT. GL (det_jurnal) SUDAH memuat memo (mm) -> utk
     "GL naik efektif" pakai det_jurnal coa_asal=akun (BBM+memo) atau
     bbm + memo_naik - memo_turun.
   - memo join by no_invoice WAJIB LTRIM/RTRIM (ada trailing space -> meleset).

   Tanggal: 2026-06-22. AI READ-ONLY DB; koreksi data oleh user.
   ============================================================================ */

-- ###########################################################################
-- PAKAN (21180.100) : deteksi per no_SJ (BBM dobel = n_bbm>1), lalu cek memo
-- ###########################################################################
-- 1) Deteksi: konfir vs BBM per SJ (cutoff < Juni). n_bbm=2 -> dobel terima_pakan.
WITH bbm AS (
  SELECT kp.no_sj, COUNT(DISTINCT tp.id) n_bbm, CAST(SUM(dj.nominal) AS decimal(18,2)) gl_naik
  FROM det_jurnal dj
  JOIN terima_pakan tp ON dj.tbl_name='terima_pakan' AND dj.tbl_id=CAST(tp.id AS varchar)
  JOIN kirim_pakan kp ON tp.id_kirim_pakan=kp.id
  WHERE dj.coa_asal='21180.100' GROUP BY kp.no_sj
),
konf AS (
  SELECT kppd.no_sj, COUNT(*) n_konf, CAST(SUM(kppd.total) AS decimal(18,2)) konfir,
         MIN(kppd.kode_unit) unit, MIN(kpp.nomor) invoice, MIN(kppd.no_order) no_order
  FROM konfirmasi_pembayaran_pakan kpp JOIN konfirmasi_pembayaran_pakan_det kppd ON kppd.id_header=kpp.id
  WHERE kpp.tgl_bayar < '2026-06-01' GROUP BY kppd.no_sj
)
SELECT k.unit, k.invoice, k.no_sj, k.no_order, b.n_bbm, k.n_konf,
  k.konfir, ISNULL(b.gl_naik,0) gl_naik, CAST(k.konfir-ISNULL(b.gl_naik,0) AS decimal(18,2)) beda
FROM konf k LEFT JOIN bbm b ON b.no_sj=k.no_sj
WHERE ABS(k.konfir-ISNULL(b.gl_naik,0)) > 0.001
ORDER BY ABS(k.konfir-ISNULL(b.gl_naik,0)) DESC;
/* HASIL (live 2026-06-22): hanya 3 SJ, semua n_bbm=1 (bukan dobel; dobel sudah beres):
   BJN BYP/12/25/00940 SJ 3016286818-4910199429  konfir 73.350.000 BBM 72.000.000  +1.350.000
   BJN BYP/12/25/00975 SJ 3016293087-4910340466  konfir 73.375.000 BBM 72.100.000  +1.275.000
   MLG BYP/12/25/00567 SJ 3016275809             konfir 65.200.000 BBM 64.000.000  +1.200.000
   -> ketiganya BBM kurang (harga 8000 vs konfir 8150) TAPI ditutup memo naik:
      MM2512310064 (BJN, no_invoice=BYP, +1.350.000 & +1.275.000), MM2512310069 (MLG +1.200.000).
      net_open = konfir - (BBM + memo_naik) = 0. -> PAKAN TUNTAS.
   CATATAN: angka selisih 130jt/129jt/65jt dari query ad-hoc sebelumnya = BOGUS (join ganda). */


-- ###########################################################################
-- DOC (21180.200) : per-unit GL naik efektif, lalu verifikasi dokumen residual
-- ###########################################################################
-- 2) Per unit: konfir vs (BBM + memo_naik - memo_turun) dari det_jurnal.
--    (angka besar/positif per unit = pipeline Juni + timing, BUKAN open riil)
WITH konf AS (
  SELECT kpdd.kode_unit unit, CAST(SUM(kpdd.total) AS decimal(18,2)) konfir
  FROM konfirmasi_pembayaran_doc kpd JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id
  GROUP BY kpdd.kode_unit
),
gl AS (
  SELECT unit,
    CAST(SUM(CASE WHEN coa_asal='21180.200' AND tbl_name='terima_doc' THEN nominal ELSE 0 END) AS decimal(18,2)) bbm,
    CAST(SUM(CASE WHEN coa_asal='21180.200' AND tbl_name='mm' THEN nominal ELSE 0 END) AS decimal(18,2)) memo_naik,
    CAST(SUM(CASE WHEN coa_tujuan='21180.200' AND tbl_name='mm' THEN nominal ELSE 0 END) AS decimal(18,2)) memo_turun
  FROM det_jurnal WHERE coa_asal='21180.200' OR coa_tujuan='21180.200' GROUP BY unit
)
SELECT k.unit, k.konfir, g.bbm, g.memo_naik, g.memo_turun,
  CAST(k.konfir-(g.bbm+ISNULL(g.memo_naik,0)-ISNULL(g.memo_turun,0)) AS decimal(18,2)) selisih_naik
FROM konf k LEFT JOIN gl g ON g.unit=k.unit ORDER BY ABS(k.konfir-(g.bbm+ISNULL(g.memo_naik,0)-ISNULL(g.memo_turun,0))) DESC;

-- 3) Verifikasi dokumen residual/suspect: konfir vs BBM vs memo (net_open).
DECLARE @t TABLE(unit varchar(5), invoice varchar(30));
INSERT INTO @t VALUES
('MLG','BYD/10/25/00070'),('MLG','BYD/12/25/00116'),('MLG','BYD/09/25/00100'),('MLG','BYD/12/25/00387'),
('MGT','BYD/11/25/00015'),('TAG','BYD/11/25/00218'),('MJK','BYD/11/25/00105');
WITH bbm AS (
  SELECT td.no_order, CAST(SUM(dj.nominal) AS decimal(18,2)) gl_naik
  FROM det_jurnal dj JOIN terima_doc td ON dj.tbl_name='terima_doc' AND dj.tbl_id=CAST(td.id AS varchar)
  WHERE dj.coa_asal='21180.200' GROUP BY td.no_order
),
konf AS (
  SELECT kpd.nomor invoice, CAST(SUM(kpdd.total) AS decimal(18,2)) konfir, CAST(SUM(ISNULL(b.gl_naik,0)) AS decimal(18,2)) bbm
  FROM konfirmasi_pembayaran_doc kpd JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id
  LEFT JOIN bbm b ON b.no_order=kpdd.no_order GROUP BY kpd.nomor
),
memo AS (SELECT LTRIM(RTRIM(no_invoice)) inv,
   CAST(SUM(CASE WHEN coa_asal='21180.200' THEN nilai ELSE 0 END) AS decimal(18,2)) memo_naik,
   CAST(SUM(CASE WHEN coa_tujuan='21180.200' THEN nilai ELSE 0 END) AS decimal(18,2)) memo_turun, MIN(no_mm) no_mm
   FROM mmitem WHERE coa_asal='21180.200' OR coa_tujuan='21180.200' GROUP BY LTRIM(RTRIM(no_invoice)))
SELECT t.unit, t.invoice, k.konfir, k.bbm, ISNULL(m.memo_naik,0) memo_naik, ISNULL(m.memo_turun,0) memo_turun, m.no_mm,
  CAST(k.konfir-(k.bbm+ISNULL(m.memo_naik,0)-ISNULL(m.memo_turun,0)) AS decimal(18,2)) net_open
FROM @t t LEFT JOIN konf k ON k.invoice=t.invoice LEFT JOIN memo m ON m.inv=t.invoice ORDER BY t.unit, t.invoice;
/* HASIL (live 2026-06-22):
   MJK BYD/11/25/00105 : net 0  -> reconciled (memo naik MM2511300016 +2.850.400)
   TAG BYD/11/25/00218 : net 0  -> reconciled (memo naik MM2512310056 +12.114.200)
   MLG 00100/00070/00116 : net +1,00 ; 00387 : +0,92  (MLG ~1,96 sen; memo desimal
      MM2606130001 SALAH ARAH/turun -> malah +1,00, idealnya di-REVERSE)
   MGT BYD/11/25/00015 : net -499,21 (memo MM2603310128 naik 499,21 over-add; PENDING)
   -> DOC TUNTAS material; sisa hanya sen MLG + MGT 499,21 (2 memo desimal perlu diperbaiki). */
