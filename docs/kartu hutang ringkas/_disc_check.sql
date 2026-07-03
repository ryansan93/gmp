SET NOCOUNT ON;
-- semua kaki tiap memo yg turun-kan 21180.200 lewat 12040.000, + apakah ada kaki ke akun biaya 71103/71400
SELECT mi.no_mm, mi.no_invoice,
  -- nilai koreksi harga (kaki 12040->21180.200)
  (SELECT SUM(x.nilai) FROM mmitem x WHERE x.no_mm=mi.no_mm AND x.coa_asal='12040.000' AND x.coa_tujuan='21180.200') as nilai_turun_hutang,
  -- apakah no_mm ini punya kaki ke akun BIAYA (penanda koreksi harga asli)
  (SELECT COUNT(*) FROM mmitem y WHERE y.no_mm=mi.no_mm AND (y.coa_asal IN ('71103.000','71400.000') OR y.coa_tujuan IN ('71103.000','71400.000'))) as ada_kaki_biaya,
  (SELECT COUNT(*) FROM mmitem z WHERE z.no_mm=mi.no_mm) as jml_kaki,
  MIN(mi.keterangan) as ket
FROM mmitem mi
WHERE mi.coa_asal='12040.000' AND mi.coa_tujuan='21180.200' AND mi.no_invoice LIKE 'BYD/%'
GROUP BY mi.no_mm, mi.no_invoice
ORDER BY nilai_turun_hutang;
