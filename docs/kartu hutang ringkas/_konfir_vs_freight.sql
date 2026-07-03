SET NOCOUNT ON;
DECLARE @cut date='2026-05-01';
PRINT '=== Saldo awal (<Mei) per unit: DEBET KONFIR vs DEBET FREIGHT vs GL snapshot ===';
;WITH konfir AS (
  SELECT oa_unit.unit, SUM(kpop.total+kpop.potongan_pph_23) debet
  FROM konfirmasi_pembayaran_oa_pakan kpop
  LEFT JOIN (select kpopd.id_header, min(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) unit from konfirmasi_pembayaran_oa_pakan_det kpopd left join kirim_pakan kp on kp.no_sj=kpopd.no_sj group by kpopd.id_header) oa_unit ON oa_unit.id_header=kpop.id
  WHERE kpop.tgl_bayar<@cut GROUP BY oa_unit.unit
),
freight AS (
  SELECT unit, SUM(t) debet FROM (
    SELECT substring(kp.no_sj, charindex('/',kp.no_sj+'//')+1, charindex('/',kp.no_sj+'//',charindex('/',kp.no_sj+'//')+1)-charindex('/',kp.no_sj+'//')-1) unit, sum(dtp.jumlah)*kp.ongkos_angkut t
    FROM det_terima_pakan dtp LEFT JOIN terima_pakan tp ON dtp.id_header=tp.id LEFT JOIN kirim_pakan kp ON tp.id_kirim_pakan=kp.id
    WHERE tp.tgl_terima<@cut AND kp.jenis_kirim='opkg' GROUP BY tp.no_bbm, kp.ongkos_angkut, kp.no_sj
  ) x GROUP BY unit
),
gl AS (SELECT unit, -SUM(saldo_akhir) sb FROM saldo_bulanan WHERE coa='21212.000' AND tanggal=@cut GROUP BY unit)
SELECT isnull(isnull(k.unit,f.unit),g.unit) unit,
  CAST(isnull(k.debet,0) AS decimal(18,2)) debet_konfir,
  CAST(isnull(f.debet,0) AS decimal(18,2)) debet_freight,
  CAST(isnull(k.debet,0)-isnull(f.debet,0) AS decimal(18,2)) selisih_konfir_vs_freight
FROM konfir k FULL JOIN freight f ON k.unit=f.unit FULL JOIN gl g ON g.unit=isnull(k.unit,f.unit)
ORDER BY unit;
