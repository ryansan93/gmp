SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

PRINT '=== H1: TOTAL freight opkg (SEMUA terima_pakan, tanpa NOT EXISTS) vs GL naik 60151.000 (21.267.716.250) ===';
SELECT CAST(SUM(x.t) AS decimal(18,2)) all_opkg_freight FROM (
  SELECT SUM(dtp.jumlah)*kp.ongkos_angkut t
  FROM det_terima_pakan dtp LEFT JOIN terima_pakan tp ON dtp.id_header=tp.id LEFT JOIN kirim_pakan kp ON tp.id_kirim_pakan=kp.id
  WHERE tp.tgl_terima<@start AND kp.jenis_kirim='opkg'
  GROUP BY tp.no_bbm, kp.ekspedisi_id, kp.ongkos_angkut) x;

PRINT '=== H1b: GL naik 21212 by tbl_name (cek apakah semua dari terima_pakan) ===';
SELECT dj.tbl_name, COUNT(*) n, CAST(SUM(dj.nominal) AS decimal(18,2)) total
FROM det_jurnal dj WHERE dj.coa_asal='21212.000' AND dj.tanggal<@start GROUP BY dj.tbl_name ORDER BY SUM(dj.nominal) DESC;

PRINT '=== H2: oa_pindah_pakan + retur freight (D3 source) total <start ===';
SELECT CAST(SUM(opp.ongkos_angkut) AS decimal(18,2)) pindah_total FROM oa_pindah_pakan opp;

PRINT '=== H3: GL turun 21212 by tbl_name ===';
SELECT dj.tbl_name, COUNT(*) n, CAST(SUM(dj.nominal) AS decimal(18,2)) total
FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.tanggal<@start GROUP BY dj.tbl_name ORDER BY SUM(dj.nominal) DESC;

PRINT '=== H4: rekonstruksi kredit = transfer+pph+clearing+11130.001 vs GL turun total ===';
DECLARE @tr decimal(18,2)=20263390097.00,@pph decimal(18,2)=333568537.00,@cl decimal(18,2)=116871318.50,@x decimal(18,2)=254800.00;
SELECT @tr+@pph+@cl+@x AS gl_turun_total, 21502352752.00 - (@tr+@pph+@cl+@x) AS implied_saldo;
