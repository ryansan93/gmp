/* ============================================================================
   GROUNDWORK REKONSILIASI HUTANG OA PAKAN / EKSPEDISI (COA 21212.000)
   ----------------------------------------------------------------------------
   "HUTANG ONGKOS ANGKUT". Hutang ke EKSPEDISI (bukan per unit; report unit=null).
   Tanggal: 2026-06-22. AI READ-ONLY DB.

   STRUKTUR (dari LaporanHutangRingkas.php blok OA PAKAN, baris ~214-271):
   DEBET (hutang naik) = 3 UNION, agregat per ekspedisi:
     1. KONFIR BRUTO  = kpop.total + kpop.potongan_pph_23   (tgl_bayar < periode)
     2. FREIGHT TERIMA BELUM DIKONFIR = SUM(dtp.jumlah*kp.ongkos_angkut),
        jenis_kirim='opkg', tp.tgl_terima<periode,
        NOT EXISTS konfirmasi_pembayaran_oa_pakan_det per kp.no_sj
     3. OA_PINDAH_PAKAN belum dikonfir (freight pindah + retur_pakan), NOT EXISTS konfir
     -> debet OA = SELURUH freight (ikut GL naik), bukan cuma yg ditagih.
   KREDIT (report): hanya rpd.transfer (transaksi='OA PAKAN'); TIDAK +pph (beda DOC).

   GL 21212 (det_jurnal):
     NAIK  coa_asal=21212 -> coa_tujuan 60151.000 (biaya angkut) & 60151.001 ; tbl_name=terima_pakan
     TURUN coa_tujuan=21212 <- coa_asal:
        11130.002 transfer bank | 24623.000 PPh23 | 27001.000 clearing/suspense | 11130.001
     -> hutang turun di GL = transfer + PPh + clearing (BUKAN cuma transfer).

   KONFIR: konfirmasi_pembayaran_oa_pakan (nomor BYO/...), per ekspedisi (ekspedisi_id),
     det: no_sj, ekspedisi, no_polisi, total. kpop.total = sub_total - potongan_pph_23.

   ANGKA (live, cutoff < 2026-06-01):
     D1 konfir bruto         20.742.072.530
     D2 freight blm konfir    1.787.632.500
     D3 pindah blm konfir             0
     debet_total             22.529.705.030   (vs GL naik 21.502.352.752 -> OVER ~1,03 M)
     transfer                20.263.390.094   (= GL 11130.002 20.263.390.097 OK)
     PPh23 konfir               315.962.313   (GL 24623 aktual 333.568.537 -> beda ~17,6 jt)
     clearing 27001             116.871.318,50 (BELUM dihitung di bridge)
     GL saldo_bulanan (akhir Mei) 805.885.899,50

   MASALAH BELUM TUNTAS:
   - debet rekonstruksi (22,53 M) > GL naik (21,50 M) ~1,03 M:
     dugaan D1 (konfir) & D2 (freight blm konfir) OVERLAP -> NOT EXISTS per no_sj
     tidak sempurna men-dedup (mis. 1 no_sj punya konfir sebagian / nomor SJ beda format).
   - kredit perlu = transfer + PPh(24623) + clearing(27001) agar = GL turun.
   - per-SJ konfir vs GL-naik via terima_pakan->kirim_pakan.no_sj GAGAL (gl_naik=0
     utk hampir semua) -> linkage no_sj OA beda; jangan dipakai.

   LANGKAH BERIKUT:
   1. Selesaikan overlap D1/D2: cek no_sj yg muncul di konfir DAN di freight-blm-konfir.
   2. Bangun kredit = transfer+PPh+clearing, bandingkan ke GL turun.
   3. Rekonsiliasi per-EKSPEDISI (bukan per-unit), cutoff disamakan.
   ============================================================================ */

-- Bridge grand-total (cutoff < Juni)
DECLARE @start date='2026-06-01';
DECLARE @d1 decimal(18,2), @d2 decimal(18,2), @d3 decimal(18,2), @ktr decimal(18,2), @pph decimal(18,2);
SELECT @d1=SUM(kpop.total+kpop.potongan_pph_23) FROM konfirmasi_pembayaran_oa_pakan kpop WHERE kpop.tgl_bayar<@start;
SELECT @d2=SUM(x.t) FROM (SELECT SUM(dtp.jumlah)*kp.ongkos_angkut t FROM det_terima_pakan dtp LEFT JOIN terima_pakan tp ON dtp.id_header=tp.id LEFT JOIN kirim_pakan kp ON tp.id_kirim_pakan=kp.id WHERE tp.tgl_terima<@start AND kp.jenis_kirim='opkg' AND NOT EXISTS(SELECT * FROM konfirmasi_pembayaran_oa_pakan_det kpopd WHERE no_sj=kp.no_sj) GROUP BY tp.no_bbm, kp.ekspedisi_id, kp.ongkos_angkut) x;
SELECT @ktr=SUM(rpd.transfer) FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='OA PAKAN' AND rp.tgl_realisasi<@start;
SELECT @pph=SUM(potongan_pph_23) FROM konfirmasi_pembayaran_oa_pakan WHERE tgl_bayar<@start;
SELECT CAST(@d1 AS decimal(18,2)) d1_konfir_bruto, CAST(@d2 AS decimal(18,2)) d2_freight_blm_konfir,
  CAST(@ktr AS decimal(18,2)) transfer, CAST(@pph AS decimal(18,2)) pph;

-- GL 21212 turun per lawan-akun (temukan clearing 27001)
SELECT dj.coa_asal, COUNT(*) n, CAST(SUM(dj.nominal) AS decimal(18,2)) total
FROM det_jurnal dj WHERE dj.coa_tujuan='21212.000' AND dj.tanggal<'2026-06-01'
GROUP BY dj.coa_asal ORDER BY SUM(dj.nominal) DESC;
