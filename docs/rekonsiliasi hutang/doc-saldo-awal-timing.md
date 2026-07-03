---
name: doc-saldo-awal-timing
description: Selisih saldo awal DOC laporan hutang ringkas vs GL — fix tgl_bayar + sisa adalah timing memorial Juni
metadata: 
  node_type: memory
  type: project
  originSessionId: 2d88700a-742a-4e17-a402-78908158545d
---

Rekonsiliasi saldo awal DOC (1 Juni 2026) antara **laporan hutang ringkas (DOC)** vs **laporan GL hutang niaga ORP (DOC)**.

**Fix yang sudah diterapkan** (controller `LaporanHutangRingkas.php`, bagian DEBET saldo awal DOC ~baris 179-203): basis diubah dari `cast(td.datang as date) < start_date` (join terima_doc) menjadi `kpd.tgl_bayar < start_date` (drop join terima_doc). Ini menutup ~389M karena 3 invoice split (00340/00341/00118) datang-nya NULL. Selisih turun **-832M → -443M → setelah re-run -28,176,433**.

**Sisa -28M = selisih TIMING, bukan error.** Operasional sudah anggap sejumlah invoice lunas (CN/pembulatan) sejak 2025, tapi GL baru koreksi di **Juni 2026 lewat memorial MM2606120001–MM2606120014** (tgl 2026-06-12), semua turun 21180.200. Contoh utama: MM2606120008 = BYD/12/25/00181 (JBR) turun 31,893,798 (9 jurnal CN cn_post id 15-23 yang dulu ter-posting coa NULL). Karena koreksi masuk di Juni, **saldo akhir Juni harus cocok** walau saldo awal belum.

**Fix kedua (kontrol CN di BAYAR LEWAT MEMO):** memorial koreksi CN (`coa_asal='71105.003'`, `coa_tujuan='21180.200'`, semua MM260612xx Juni, total 66,795,627.91) menyebabkan CN dihitung DUA kali di ringkas — sekali via `cn_post_det` (saldo awal) dan sekali via memorial Juni. Ditambahkan kontrol di KEDUA bagian BAYAR LEWAT MEMO (saldo awal ~baris 771 & bulan berjalan ~baris 1404). User sudah isi `mmitem.no_invoice` ke-14 memorial dgn nomor BYD yg benar (match `cn_post_det.nomor`), jadi kontrol disederhanakan ke: `not (mi.coa_asal='71105.003' and exists (select 1 from cn_post_det cpd where cpd.nomor=mi.no_invoice))`. (Versi awal sempat lewat `det_jurnal.invoice` krn no_invoice masih NULL & keterangan MM2606120002 typo 01 vs 02 — sekarang tak perlu.) Efek: saldo akhir BWI/GSK/KDR/JBR/BJN konvergen ≈0.

**Fix ketiga (kontrol reklasifikasi di INVOICE LEWAT MEMO):** sisa PSR +32.35M & MLG +5.5M = memo reklasifikasi antar-akun hutang MM2602280004 (PSR 32,737,500) & MM2602280005 (MLG 5,325,000), keduanya `coa_asal=21180.200 coa_tujuan=21180.100` ("KOREKSI ATAS CN/PKN/25/10/001"). Di GL = pasangan CN(-32.7M Okt, jenis PKN, BYP/10/25/00065)+memo(+32.7M Feb)=0; ringkas tak lihat CN (jenis PKN) jadi memo-nya juga harus diabaikan. Ditambahkan di KEDUA inv_mm (saldo awal ~baris 460 & bulan berjalan ~baris 1097): `mi.coa_tujuan not in ('21180.300','21174.000','21180.200','21180.100','21173.000')` — lewati memo yang coa_tujuan-nya akun hutang lain (reklasifikasi, bukan tagihan baru; tagihan asli coa_tujuan=stok/biaya spt 12040). Hanya 3 memo kena: +MM2604270001 (BWI OVK reklas 2.5M, bonus utk OVK). Sisa setelah ini: BJN +427k, JBR +338k = PPh/pembulatan kecil.

PSR +32.35M (lihat fix ketiga) dulu arah kebalikan: CN BYP/10/25/00065 (32,737,500) diposting GL turun 21180.200 tapi operasional tak tangkap (jenis PKN).

Replika controller (READ-ONLY) ada di `docs/kartu hutang ringkas/_ringkas_vs_gl.sql` — cocok persis 11/14 unit. Self-check per-invoice: `docs/kartu hutang ringkas/cek_per_invoice_doc.sql`. Lihat juga [[hutang-vs-jurnal-doc]] [[pending-jurnal-memorial-doc-mgt]].

**Sisa terakhir +556,694.80 (per 13 Juni) — sudah dibedah tuntas:**
- Grup A (+944,195): GL akru PPh 0.25% atas koreksi/konfir-terhapus yg operasional tak punya konfirmasi → MM2512310053 BJN +427,560 (0.25%×171,024,000); BYD/11/25/00100 JBR +338,485 (0.25%×135,394,000); BYD/11/25/00205 MLG +178,150 (0.25%×71,260,000). 135,394,000 & 71,260,000 = nilai memo MM2512310055/MM2512310052. User SENGAJA hapus konfirmasi-nya supaya tak dobel dgn memorial — tapi PPh-nya nyangkut di GL.
- Grup B (-388,000): BYD/10/25/00195 PSR — GL BBM naik 28,504,000 vs konfirmasi 28,116,000 (selisih penerimaan DOC, bukan PPh).
- Grup C: pembulatan recehan (MGT +499, TAG +1, PRB +0.5, MDN +0.05).
- Grup A SOLVED via fix baris 664 & 1310 (kredit transfer, union `mm` di konfir): dulu `pph` di-hardcode 0 untuk pembayaran lewat memo (no_bayar=no_mm), padahal GL potong PPh 0.25%. Diganti hitung `pph = sum(mi.nilai where coa_asal='21180.200')*0.0025`. Ketiga pembayaran (BYR/11/25/00233→MM053 BJN, BYR/11/25/00137→MM055 JBR, BYR/11/25/00273→MM052 MLG) no_bayar-nya = nomor memo. Hanya 3 memo ini yg punya komponen DOC & dibayar lewat memo, jadi scope fix persis 3. Menutup tepat 944,195. User SENGAJA hapus konfirmasi (hindari dobel dgn memorial) — fix di laporan, bukan utak-atik data/GL (PPh-nya memang benar dipotong saat bayar).
- Grup B (PSR -388,000) DIAGNOSED: BYD/10/25/00195 no_order ODC/PSR/25/10011. terima_doc harga 7126 (4000 ekor=28,504,000) tapi konfirmasi+bayar harga 7029 (28,116,000); beda 97/ekor=388,000. GL hutang naik 28,504,000, bayar BYR/11/25/00028 = 28,116,000 → 388,000 menggantung di GL; operasional lunas. Tak ada CN/memo koreksi. ISU DATA (harga BBM != harga deal), bukan laporan. Saran: user posting memorial koreksi turun 21180.200 sebesar 388,000 (jika 7029 benar) ATAU perbaiki harga terima_doc. User yg putuskan & posting.
- TUNTAS: user benahi PSR 388,000 lewat konfirmasi+memo. Selisih akhir -470.20 lalu MGT dibenahi -> -968.68. REKONSILIASI DOC SELESAI/RECONCILED. Total perjalanan: -832,842,821 -> <Rp1000.
- PSR -970 (BYD/10/25/00195) -- MASUK TO-DO: dasar PPh SEHARUSNYA harga 7.029 (28.116.000 -> PPh 70.290), BUKAN 7.126 (28.504.000 -> 71.260). Selisih harga 388.000 (7126 vs 7029) = koreksi persediaan, TIDAK kena PPh. GL sudah benar (PPh 70.290 atas 7029). Ringkas over-compute PPh 970 krn formula pakai konfirmasi total 28.504.000 (yg sudah dinaikkan user ke harga 7126). FIX: PPh PSR invoice ini harus dihitung dari 28.116.000 (harga 7029), bukan dari konfirmasi 28.504.000.
- MGT: dulu +499.21 dari memo MM2603310128 (PEMBULATAN HUTANG DOC, 498.48, invoice BYD/11/25/00015) yg op hitung tapi saldo_bulanan GL tak cerminkan. User SUDAH benahi -> +0.73.
- Sisa <Rp2 per unit DIBEDAH TUNTAS (semua masalah JURNAL, bukan konfirmasi/hutang/bayar):
  - **BJN +1.00 = invoice BYD/12/25/00106** (BYR/12/25/00150): baris jurnal transfer 11130.001->21180.200 tertulis 135.055.516 padahal realisasi 135.055.515 (1 rupiah kelebihan), DAN tidak ada entri pembulatan penyeimbang. GL turun 135.394.001 vs ringkas bayar 135.394.000. FIX: betulkan transfer jurnal jadi 135.055.515 (atau tambah pembulatan D.96010 K.21180.200 = 1.00).
  - **MLG -1.96 = 4 invoice dgn pembulatan KELIRU** (transfer sudah bulat tapi entri pembulatan 0.50 tetap dibuat -> GL net jadi pecahan, nyangkut hutang): BYD/09/25/00100 (-0.50), BYD/10/25/00070 (-0.50), BYD/12/25/00116 (-0.50), BYD/12/25/00387 (-0.46). Total persis -1.96. FIX: hapus/reverse entri pembulatan D.96010.000 K.21180.200 (0.50 di 00100/00070/00116; 0.46 di 00387).
  - MEKANISME pembulatan: saat bayar, sistem auto-bikin entri "Pembulatan Rupiah Penuh" D.96010 K.21180.200. BENAR bila transfer pecahan (mis. BYD/09/25/00093 transfer 63.075.915,50 + pembulatan 0.50 -> net bulat, ringkas=GL cocok). KELIRU bila transfer sudah bulat tapi pembulatan tetap terbentuk. Ringkas tak baca entri pembulatan (acuan = konfirmasi), jadi hanya invoice dgn pembulatan-keliru yg bikin selisih.
  - Batch BYR/06/26/00242 (78 baris invoice NULL, regresi tagging Juni) TERNYATA semua bilangan bulat tanpa pembulatan -> BUKAN penyebab selisih recehan (cuma masalah kerapihan tag, opsional dibenahi).
  - **MDN +0.05** = BYD/04/26/00129: entri pembulatan jurnal 0.45 (D.96010 K.21180.200) bikin GL nyangkut. FIX: review/hapus pembulatan keliru (ATAU memo MM2604300030).
  - **MGT +0.73** = 2 invoice (KOREKSI: BYD/04/26/00306 TIDAK termasuk -- memo MM2604300031 coa_tujuan=21180.200 dibaca ringkas via byr_mm, net 0):
    * BYD/11/25/00015 (+0.27) = SISA koreksi 498.48: jurnal transfer BYR/11/25/00028 = 71.082.348,75 (kelebihan 498,75 dari realisasi 71.081.850), pembulatan memo MM2603310128 = 498,48 -> sisa 0,27 tak terkoreksi. Ini yg dimaksud user "karena 498.48". Terkait [[pending-jurnal-memorial-doc-mgt]]. FIX: tambah pembulatan 0,27 / betulkan transfer jurnal.
    * BYD/12/25/00392 (+0.46) = jurnal transfer BYR/01/26/00002 = 80.933.160,46 (kelebihan 0,46 dari realisasi 80.933.160), tanpa pembulatan. FIX: betulkan transfer jurnal jadi bulat.
  - **TAG +1.00** = BYD/12/25/00139 (+0.35, pembulatan jurnal 0.15) + memo MM2603310046 (+0.65, PEMBULATAN manual tanpa invoice). FIX: review pembulatan + memo MM2603310046.
  - PRB: user konfirmasi SUDAH SELESAI / tidak ada issue (dulu +0.50 memo MM2603310129, kini beres).
  - CATATAN: MDN/MGT/TAG ini campuran (a) entri pembulatan jurnal keliru spt MLG + (b) memo PEMBULATAN HUTANG manual (mmitem coa_asal=21180.200 coa_tujuan=96010, sering no_invoice kosong) yg dibaca ringkas sbg inv_mm. Semua <Rp1, sumbernya pembulatan, bukan konfirmasi/hutang/bayar. Mengejar ke 0 berisiko lingkaran pembulatan; pertimbangkan terima sbg reconciled.

JANGAN ubah data DB — user koreksi sendiri lewat memorial.
