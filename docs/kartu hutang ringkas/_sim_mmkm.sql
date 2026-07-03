SET NOCOUNT ON;
DECLARE @cut date='2026-06-01';
;WITH
deb AS (
  SELECT SUBSTRING(REPLACE(REPLACE(r.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3) unit, SUM(r.pdpt_peternak_sudah_pajak) d
  FROM rhpp r JOIN tutup_siklus ts ON r.id_ts=ts.id WHERE r.jenis='rhpp_plasma' AND r.invoice IS NOT NULL AND r.invoice<>'' AND r.pdpt_peternak_sudah_pajak>0 AND ts.tgl_tutup<@cut AND NOT EXISTS(SELECT 1 FROM rhpp_group_noreg gn WHERE gn.noreg=r.noreg)
  GROUP BY SUBSTRING(REPLACE(REPLACE(r.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3)
  UNION ALL SELECT SUBSTRING(REPLACE(REPLACE(rg.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3), SUM(rg.pdpt_peternak_sudah_pajak) FROM rhpp_group rg JOIN rhpp_group_header h ON rg.id_header=h.id WHERE rg.jenis='rhpp_plasma' AND rg.pdpt_peternak_sudah_pajak>0 AND h.tgl_submit<@cut GROUP BY SUBSTRING(REPLACE(REPLACE(rg.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3)
  UNION ALL SELECT ISNULL(CASE WHEN kpp.invoice IS NOT NULL THEN SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3) END, m.unit), SUM(mi.nilai) FROM mmitem mi JOIN mm m ON mi.no_mm=m.no_mm LEFT JOIN konfirmasi_pembayaran_peternak kpp ON kpp.nomor=mi.no_invoice WHERE mi.coa_asal LIKE '21213%' AND mi.tgl_mm<@cut GROUP BY ISNULL(CASE WHEN kpp.invoice IS NOT NULL THEN SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3) END, m.unit)
  UNION ALL SELECT ISNULL(CASE WHEN kpp.invoice IS NOT NULL THEN SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3) END, k.unit), SUM(ki.nilai) FROM kmitem ki JOIN km k ON ki.no_km=k.no_km LEFT JOIN konfirmasi_pembayaran_peternak kpp ON kpp.nomor=ki.no_invoice WHERE ki.coa_asal LIKE '21213%' AND ki.tgl_km<@cut GROUP BY ISNULL(CASE WHEN kpp.invoice IS NOT NULL THEN SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3) END, k.unit)
),
debu AS (SELECT unit, SUM(d) debet FROM deb GROUP BY unit),
kre AS (
  SELECT SUBSTRING(REPLACE(REPLACE(r.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3) unit, SUM(p.nominal) k FROM rhpp_piutang p JOIN rhpp r ON p.id_header=r.id JOIN tutup_siklus ts ON r.id_ts=ts.id WHERE r.jenis='rhpp_plasma' AND r.invoice IS NOT NULL AND r.invoice<>'' AND r.pdpt_peternak_sudah_pajak>0 AND ts.tgl_tutup<@cut AND NOT EXISTS(SELECT 1 FROM rhpp_group_noreg gn WHERE gn.noreg=r.noreg) GROUP BY SUBSTRING(REPLACE(REPLACE(r.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3)
  UNION ALL SELECT SUBSTRING(REPLACE(REPLACE(rg.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3), SUM(p.nominal) FROM rhpp_group_piutang p JOIN rhpp_group rg ON p.id_header=rg.id JOIN rhpp_group_header h ON rg.id_header=h.id WHERE rg.jenis='rhpp_plasma' AND rg.pdpt_peternak_sudah_pajak>0 AND h.tgl_submit<@cut GROUP BY SUBSTRING(REPLACE(REPLACE(rg.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3)
  UNION ALL SELECT SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3), SUM(rpd.transfer) FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id LEFT JOIN konfirmasi_pembayaran_peternak kpp ON kpp.nomor=rpd.no_bayar WHERE rpd.transaksi='PLASMA' AND rp.tgl_realisasi<@cut GROUP BY SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3)
  UNION ALL SELECT ISNULL(CASE WHEN kpp.invoice IS NOT NULL THEN SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3) END, m.unit), SUM(mi.nilai) FROM mmitem mi JOIN mm m ON mi.no_mm=m.no_mm LEFT JOIN konfirmasi_pembayaran_peternak kpp ON kpp.nomor=mi.no_invoice WHERE mi.coa_tujuan LIKE '21213%' AND mi.tgl_mm<@cut GROUP BY ISNULL(CASE WHEN kpp.invoice IS NOT NULL THEN SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3) END, m.unit)
  UNION ALL SELECT ISNULL(CASE WHEN kpp.invoice IS NOT NULL THEN SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3) END, k.unit), SUM(ki.nilai) FROM kmitem ki JOIN km k ON ki.no_km=k.no_km LEFT JOIN konfirmasi_pembayaran_peternak kpp ON kpp.nomor=ki.no_invoice WHERE ki.coa_tujuan LIKE '21213%' AND ki.tgl_km<@cut GROUP BY ISNULL(CASE WHEN kpp.invoice IS NOT NULL THEN SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3) END, k.unit)
),
kreu AS (SELECT unit, SUM(k) kredit FROM kre GROUP BY unit),
gl AS (SELECT unit, SUM(saldo_awal)*-1 g FROM saldo_bulanan WHERE coa LIKE '21213%' AND tanggal='2026-06-01' GROUP BY unit)
SELECT ISNULL(ISNULL(debu.unit,kreu.unit),gl.unit) unit,
  CAST(ISNULL(debu.debet,0)-ISNULL(kreu.kredit,0) AS decimal(18,2)) report_FINAL,
  CAST(ISNULL(gl.g,0) AS decimal(18,2)) gl_abs,
  CAST((ISNULL(debu.debet,0)-ISNULL(kreu.kredit,0))-ISNULL(gl.g,0) AS decimal(18,2)) selisih
FROM debu FULL JOIN kreu ON debu.unit=kreu.unit FULL JOIN gl ON gl.unit=ISNULL(debu.unit,kreu.unit)
ORDER BY unit;
