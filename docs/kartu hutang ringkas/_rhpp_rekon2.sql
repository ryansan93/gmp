SET NOCOUNT ON;
DECLARE @cut date='2026-05-01';
;WITH rdebet AS (
  SELECT SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3) unit, SUM(kpp.total) debet
  FROM konfirmasi_pembayaran_peternak kpp WHERE kpp.tgl_bayar<@cut GROUP BY SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3)
),
rkredit AS (
  SELECT SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3) unit, SUM(rpd.transfer) kredit
  FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
  LEFT JOIN konfirmasi_pembayaran_peternak kpp ON kpp.nomor=rpd.no_bayar
  WHERE rpd.transaksi='PLASMA' AND rp.tgl_realisasi<@cut GROUP BY SUBSTRING(REPLACE(REPLACE(kpp.invoice,'INV/RHPP/G/',''),'INV/RHPP/',''),1,3)
),
gl AS (SELECT unit, -SUM(saldo_akhir) sb FROM saldo_bulanan WHERE coa='21213.000' AND tanggal=@cut GROUP BY unit)
SELECT isnull(isnull(d.unit,k.unit),g.unit) unit,
  CAST(isnull(d.debet,0)-isnull(k.kredit,0) AS decimal(18,2)) report_saldo,
  CAST(isnull(g.sb,0) AS decimal(18,2)) gl_saldo,
  CAST((isnull(d.debet,0)-isnull(k.kredit,0)) - isnull(g.sb,0) AS decimal(18,2)) selisih
FROM rdebet d FULL JOIN rkredit k ON d.unit=k.unit FULL JOIN gl g ON g.unit=isnull(d.unit,k.unit)
ORDER BY unit;
