SET NOCOUNT ON;
DECLARE @start date = '2026-06-01';
DECLARE @end date   = '2026-06-30';

/* ===== RINGKAS: saldo awal (debet-kredit < start) ===== */
WITH r_awal AS (
  SELECT unit, SUM(debet) debet, SUM(kredit) kredit FROM (
    SELECT kpdd.kode_unit unit, kpdd.total debet, 0.0 kredit
    FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
    WHERE kpd.tgl_bayar < @start
    UNION ALL
    SELECT m.unit, mi.nilai, 0.0 FROM mmitem mi LEFT JOIN mm m ON mi.no_mm=m.no_mm
    WHERE mi.coa_asal='21180.200' AND CAST(mi.tgl_mm AS date)<@start
      AND NOT EXISTS (SELECT 1 FROM (
        SELECT nomor FROM konfirmasi_pembayaran_doc UNION ALL SELECT nomor FROM konfirmasi_pembayaran_pakan
        UNION ALL SELECT nomor FROM konfirmasi_pembayaran_voadip UNION ALL SELECT nomor FROM konfirmasi_pembayaran_oa_pakan
        UNION ALL SELECT ISNULL(NULLIF(invoice,''),nomor) FROM konfirmasi_pembayaran_peternak) kk WHERE kk.nomor=mi.no_invoice)
    UNION ALL
    SELECT konfir.kode_unit, 0.0, CASE WHEN konfir.tanggal<='2025-09-20' THEN rpd.transfer ELSE rpd.transfer+konfir.pph END
    FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
    JOIN (SELECT kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar tanggal,
            ((kpdd.total - CASE WHEN kpd.tgl_bayar>='2026-01-01' THEN ISNULL((SELECT SUM(cpd.pakai) FROM cn_post_det cpd WHERE cpd.nomor=kpd.nomor),0) ELSE 0 END)*(0.25/100)) pph
          FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
          GROUP BY kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar, kpdd.total) konfir ON rpd.no_bayar=konfir.nomor
    WHERE rp.tgl_realisasi IS NOT NULL AND rp.tgl_realisasi<@start AND rpd.transaksi='DOC'
    UNION ALL
    SELECT kpdd.kode_unit, 0.0, cpd.pakai FROM cn_post_det cpd JOIN cn_post cp ON cpd.id_header=cp.id
    JOIN konfirmasi_pembayaran_doc kpd ON kpd.nomor=cpd.nomor JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id
    WHERE cp.tanggal<@start AND cp.jenis_cn='DOC'
    UNION ALL
    SELECT m.unit, 0.0, mi.nilai FROM mmitem mi LEFT JOIN mm m ON mi.no_mm=m.no_mm
    WHERE mi.coa_tujuan='21180.200' AND CAST(mi.tgl_mm AS date)<@start
  ) t GROUP BY unit
),
/* ===== RINGKAS: pergerakan Juni, pisahkan memorial CN-repair (71105.003) ===== */
r_juni AS (
  SELECT unit, SUM(debet) debet, SUM(kredit) kredit, SUM(kredit_cnrepair) kredit_cnrepair FROM (
    SELECT kpdd.kode_unit unit, kpdd.total debet, 0.0 kredit, 0.0 kredit_cnrepair
    FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
    WHERE kpd.tgl_bayar BETWEEN @start AND @end
    UNION ALL
    SELECT m.unit, mi.nilai, 0.0, 0.0 FROM mmitem mi LEFT JOIN mm m ON mi.no_mm=m.no_mm
    WHERE mi.coa_asal='21180.200' AND CAST(mi.tgl_mm AS date) BETWEEN @start AND @end
      AND NOT EXISTS (SELECT 1 FROM (
        SELECT nomor FROM konfirmasi_pembayaran_doc UNION ALL SELECT nomor FROM konfirmasi_pembayaran_pakan
        UNION ALL SELECT nomor FROM konfirmasi_pembayaran_voadip UNION ALL SELECT nomor FROM konfirmasi_pembayaran_oa_pakan
        UNION ALL SELECT ISNULL(NULLIF(invoice,''),nomor) FROM konfirmasi_pembayaran_peternak) kk WHERE kk.nomor=mi.no_invoice)
    UNION ALL
    SELECT konfir.kode_unit, 0.0, CASE WHEN konfir.tanggal<='2025-09-20' THEN rpd.transfer ELSE rpd.transfer+konfir.pph END, 0.0
    FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
    JOIN (SELECT kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar tanggal,
            ((kpdd.total - CASE WHEN kpd.tgl_bayar>='2026-01-01' THEN ISNULL((SELECT SUM(cpd.pakai) FROM cn_post_det cpd WHERE cpd.nomor=kpd.nomor),0) ELSE 0 END)*(0.25/100)) pph
          FROM konfirmasi_pembayaran_doc_det kpdd JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
          GROUP BY kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar, kpdd.total) konfir ON rpd.no_bayar=konfir.nomor
    WHERE rp.tgl_realisasi BETWEEN @start AND @end AND rpd.transaksi='DOC'
    UNION ALL
    SELECT kpdd.kode_unit, 0.0, cpd.pakai, 0.0 FROM cn_post_det cpd JOIN cn_post cp ON cpd.id_header=cp.id
    JOIN konfirmasi_pembayaran_doc kpd ON kpd.nomor=cpd.nomor JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id
    WHERE cp.tanggal BETWEEN @start AND @end AND cp.jenis_cn='DOC'
    UNION ALL
    -- BAYAR LEWAT MEMO juni: pisahkan yang coa_asal=71105.003 (CN repair) ke kolom khusus
    SELECT m.unit, 0.0,
      CASE WHEN mi.coa_asal='71105.003' THEN 0.0 ELSE mi.nilai END,
      CASE WHEN mi.coa_asal='71105.003' THEN mi.nilai ELSE 0.0 END
    FROM mmitem mi LEFT JOIN mm m ON mi.no_mm=m.no_mm
    WHERE mi.coa_tujuan='21180.200' AND CAST(mi.tgl_mm AS date) BETWEEN @start AND @end
  ) t GROUP BY unit
),
gl_awal AS (SELECT unit, -SUM(saldo_awal) saldo FROM saldo_bulanan WHERE coa='21180.200' AND tanggal='2026-06-01' GROUP BY unit),
gl_juni AS (
  SELECT u.unit, SUM(u.naik)-SUM(u.turun) net FROM (
    SELECT SUBSTRING(td.no_order,5,3) unit, dj.nominal naik, 0.0 turun
    FROM det_jurnal dj JOIN terima_doc td ON dj.tbl_name='terima_doc' AND dj.tbl_id=CAST(td.id AS varchar)
    WHERE dj.coa_asal='21180.200' AND dj.tanggal BETWEEN @start AND @end
    UNION ALL
    SELECT m.unit, mi.nilai, 0.0 FROM mmitem mi JOIN mm m ON mi.no_mm=m.no_mm
    WHERE mi.coa_asal='21180.200' AND CAST(mi.tgl_mm AS date) BETWEEN @start AND @end
    UNION ALL
    SELECT ISNULL(m.unit, kpdd.kode_unit), 0.0, dj.nominal
    FROM det_jurnal dj
    LEFT JOIN mm m ON dj.kode_trans=m.no_mm
    LEFT JOIN konfirmasi_pembayaran_doc kpd ON dj.invoice=kpd.nomor
    LEFT JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.id_header=kpd.id
    WHERE dj.coa_tujuan='21180.200' AND dj.tanggal BETWEEN @start AND @end
  ) u GROUP BY u.unit
)
SELECT ra.unit,
  CAST(ra.debet-ra.kredit AS decimal(18,2)) r_awal,
  CAST(rj.debet-rj.kredit AS decimal(18,2)) r_juni_skrg,
  CAST((ra.debet-ra.kredit)+(rj.debet-rj.kredit) AS decimal(18,2)) r_akhir_skrg,
  CAST((ra.debet-ra.kredit)+(rj.debet-rj.kredit+rj.kredit_cnrepair) AS decimal(18,2)) r_akhir_fix,
  CAST(ISNULL(ga.saldo,0)+ISNULL(gj.net,0) AS decimal(18,2)) gl_akhir,
  CAST(((ra.debet-ra.kredit)+(rj.debet-rj.kredit)) - (ISNULL(ga.saldo,0)+ISNULL(gj.net,0)) AS decimal(18,2)) selisih_skrg,
  CAST(((ra.debet-ra.kredit)+(rj.debet-rj.kredit+rj.kredit_cnrepair)) - (ISNULL(ga.saldo,0)+ISNULL(gj.net,0)) AS decimal(18,2)) selisih_fix
FROM r_awal ra
LEFT JOIN r_juni rj ON rj.unit=ra.unit
LEFT JOIN gl_awal ga ON ga.unit=ra.unit
LEFT JOIN gl_juni gj ON gj.unit=ra.unit
ORDER BY ra.unit;
