SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

-- DEBET baru = freight opkg (all) + pindah/retur (all), <start
DECLARE @debet decimal(18,2);
SELECT @debet = SUM(oa.total) FROM (
  select tp.no_bbm as nomor, kp.ekspedisi_id as supplier, sum(dtp.jumlah)*kp.ongkos_angkut as total
  from det_terima_pakan dtp
  left join terima_pakan tp on dtp.id_header=tp.id
  left join kirim_pakan kp on tp.id_kirim_pakan=kp.id
  where tp.tgl_terima<@start and kp.jenis_kirim='opkg'
  group by tp.no_bbm, kp.ekspedisi_id, kp.ongkos_angkut
  union all
  select opp.no_sj, krm.ekspedisi_id, opp.ongkos_angkut from oa_pindah_pakan opp
  left join (
    select kp.no_sj, tp.no_bbm kode_trans, kp.ekspedisi_id, tp.tgl_terima tanggal from kirim_pakan kp
    left join terima_pakan tp on kp.id=tp.id_kirim_pakan
    group by kp.no_sj, tp.no_bbm, kp.ekspedisi_id, tp.tgl_terima
    union all
    select no_retur, no_retur, ekspedisi_id, tgl_retur from retur_pakan
  ) krm on opp.no_sj=krm.no_sj
  where krm.tanggal<@start
) oa;

-- KREDIT baru = transfer + pph konfir
DECLARE @kredit decimal(18,2);
SELECT @kredit = SUM(rpd.transfer + isnull(kpop.potongan_pph_23,0))
FROM realisasi_pembayaran_det rpd
LEFT JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
LEFT JOIN konfirmasi_pembayaran_oa_pakan kpop ON kpop.nomor=rpd.no_bayar
WHERE rpd.transaksi='OA PAKAN' AND rp.tgl_realisasi IS NOT NULL AND rp.tgl_realisasi<@start;

SELECT @debet AS debet_baru, @kredit AS kredit_baru, (@debet-@kredit) AS saldo_report_baru,
       788267999.50 AS gl_saldo_detjurnal, (@debet-@kredit)-788267999.50 AS sisa_vs_gl;
