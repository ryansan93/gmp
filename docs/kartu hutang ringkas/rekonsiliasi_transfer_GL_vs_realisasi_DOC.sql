/* =====================================================================
   REKONSILIASI TRANSFER GL vs REALISASI  (Hutang Niaga ORP DOC / 21180.200)
   ---------------------------------------------------------------------
   Tujuan : Mendeteksi SALAH POSTING di jurnal GL pada pembayaran DOC.
            Membandingkan jumlah transfer yang menurunkan hutang di GL
            (det_jurnal) vs nilai transfer aktual di realisasi pembayaran.

   Kenapa perlu: query KHR per-invoice TIDAK bisa mendeteksi salah posting
   GL (KHR tidak membaca det_jurnal). Selisih kecil per unit (mis. 0,87)
   sering MENYEMBUNYIKAN error ratusan rupiah yang saling menutup dengan
   selisih terima / pembulatan. Query ini menyorot error transfer langsung.

   Grain  : per BYR (pembayaran) per unit.
   Sisi GL: det_jurnal coa_tujuan=21180.200, coa_asal IN (11130.001 bank,
            27001.000 clearing). Clearing diikutkan supaya pembayaran yang
            di-split GL (bank + clearing) TIDAK jadi false-positive.
   Sisi op: SUM(realisasi_pembayaran_det.transfer) transaksi='DOC'.

   Cara baca:
     selisih_gl_minus_real > 0  -> GL catat transfer LEBIH besar
                                   -> GL over-reduksi hutang (hutang GL terlalu kecil)
     selisih_gl_minus_real < 0  -> GL catat transfer LEBIH kecil

   Tindak lanjut: untuk tiap BYR yang muncul, drill ke invoice-nya
   (lihat blok DRILL di bawah) lalu koreksi nominal transfer di jurnal GL.

   Catatan: PPh (24622.000) sengaja TIDAK diikutkan di sisi GL karena PPh
   aktual GL = PPh hitung; error yang ditemukan selama ini semuanya di
   nominal transfer. Kalau mau, tambahkan '24622.000' ke daftar coa_asal
   untuk merekonsiliasi (transfer+PPh) sekaligus.

   PENTING (update): sisi GL sudah DI-NET dengan jurnal pembulatan penyeimbang
   (21180.200 -> 96010.000) pada BYR yang sama. Banyak transfer ber-,50 yang
   SEBENARNYA sudah benar karena ada pembulatan penyeimbang (mis. id 123171
   untuk BYD/09/25/00093). Tanpa net ini, rekon over-flag. Setelah net, hanya
   error yang BENAR-BENAR belum diseimbangkan yang muncul.

   TEMUAN: pembulatan kadang ter-attribute ke UNIT yang SALAH. Contoh: transfer
   error di TAG/PRB/MGT tapi pembulatan penyeimbangnya di-post ke unit MLG ->
   muncul sebagai pasangan (+0,50 di unit X, -0,50 di MLG). Inilah sumber
   selisih MLG -1,96 (akumulasi pembulatan milik unit lain). Jadi pasangan
   +/- antar unit = MISATTRIBUTION pembulatan, bukan error transfer baru.
   ===================================================================== */

WITH unitmap AS (
    -- unit per nomor BYD (dari konfirmasi pembayaran DOC)
    SELECT kpd.nomor, MIN(d.kode_unit) AS unit
    FROM konfirmasi_pembayaran_doc kpd
    JOIN konfirmasi_pembayaran_doc_det d ON d.id_header = kpd.id
    GROUP BY kpd.nomor
),
realisasi AS (
    -- transfer aktual per pembayaran (BYR) per unit
    SELECT rp.nomor AS byr, u.unit, SUM(rpd.transfer) AS realisasi_transfer
    FROM realisasi_pembayaran_det rpd
    JOIN realisasi_pembayaran rp ON rpd.id_header = rp.id
    JOIN unitmap u ON u.nomor = rpd.no_bayar
    WHERE rpd.transaksi = 'DOC'
      AND rp.tgl_realisasi IS NOT NULL
      AND rp.tgl_realisasi <= '2026-06-13'
    GROUP BY rp.nomor, u.unit
),
gl AS (
    -- reduksi hutang via kas/clearing, DI-NET dengan pembulatan penyeimbang
    -- (banyak transfer berpecahan ,50 sudah diseimbangkan jurnal pembulatan
    --  21180.200 -> 96010.000 pada BYR & ref yang sama, mis. id 123171.
    --  Tanpa net ini, rekon akan over-flag transfer yg sebenarnya sudah benar.)
    SELECT byr, unit, SUM(val) AS gl_transfer
    FROM (
        -- (+) kas/clearing yang menurunkan hutang
        SELECT dj.kode_trans AS byr,
               ISNULL(NULLIF(dj.unit_tujuan, ''), dj.unit) AS unit,
               dj.nominal AS val
        FROM det_jurnal dj
        WHERE dj.coa_tujuan = '21180.200'
          AND dj.coa_asal IN ('11130.001', '27001.000')
          AND dj.kode_trans LIKE 'BYR/%'
          AND dj.tanggal <= '2026-06-13'

        UNION ALL

        -- (-) pembulatan penyeimbang yang menaikkan hutang kembali
        SELECT dj.kode_trans AS byr,
               ISNULL(NULLIF(dj.unit_tujuan, ''), dj.unit) AS unit,
               -dj.nominal AS val
        FROM det_jurnal dj
        WHERE dj.coa_asal = '21180.200'
          AND dj.coa_tujuan = '96010.000'
          AND dj.kode_trans LIKE 'BYR/%'
          AND dj.tanggal <= '2026-06-13'
    ) z
    GROUP BY byr, unit
)
SELECT
    r.unit,
    r.byr,
    r.realisasi_transfer,
    g.gl_transfer,
    CAST(g.gl_transfer - r.realisasi_transfer AS decimal(15,2)) AS selisih_gl_minus_real
FROM realisasi r
JOIN gl g ON g.byr = r.byr AND g.unit = r.unit
WHERE ABS(ISNULL(g.gl_transfer, 0) - r.realisasi_transfer) > 0.001
ORDER BY ABS(g.gl_transfer - r.realisasi_transfer) DESC;


/* =====================================================================
   DRILL — temukan INVOICE penyebab dalam satu BYR
   Ganti @byr dan @unit lalu jalankan blok ini.
   Membandingkan transfer realisasi per invoice vs entri transfer GL.
   =====================================================================
DECLARE @byr varchar(30) = 'BYR/11/25/00028';
DECLARE @unit varchar(10) = 'MGT';

-- transfer realisasi per invoice
SELECT rpd.no_bayar, rpd.transfer AS realisasi_transfer
FROM realisasi_pembayaran_det rpd
JOIN realisasi_pembayaran rp ON rpd.id_header = rp.id
JOIN (SELECT kpd.nomor, MIN(d.kode_unit) unit FROM konfirmasi_pembayaran_doc kpd
      JOIN konfirmasi_pembayaran_doc_det d ON d.id_header=kpd.id GROUP BY kpd.nomor) u
     ON u.nomor = rpd.no_bayar
WHERE rp.nomor = @byr AND rpd.transaksi = 'DOC' AND u.unit = @unit
ORDER BY rpd.transfer DESC;

-- entri transfer GL (bandingkan nominal & cari ref yang janggal / pecahan tak wajar)
SELECT coa_asal, nominal, CAST(keterangan AS varchar(max)) AS keterangan
FROM det_jurnal
WHERE kode_trans = @byr AND coa_tujuan = '21180.200'
  AND coa_asal IN ('11130.001','27001.000')
  AND ISNULL(NULLIF(unit_tujuan,''), unit) = @unit
ORDER BY nominal DESC;
===================================================================== */
