---
name: hutang-pakan-rekonsiliasi
description: "Metodologi & temuan rekonsiliasi hutang PAKAN (laporan ringkas vs GL), COA 21180.100"
metadata: 
  node_type: memory
  type: project
  originSessionId: 6cc41ef4-1937-4d82-b821-082f2ba7f666
---

Rekonsiliasi hutang **PAKAN** (laporan hutang ringkas vs laporan GL hutang niaga ORP PAKAN). COA = **21180.100**. saldo_bulanan 21180.100 = -23.624.717.500 (1 baris/unit, bulat). Lihat juga [[doc-saldo-awal-timing]] [[todo-koreksi-jurnal-doc]].

**Selisih awal yg ditemukan user (ringkas vs GL):** total -430.087.500. Per unit: BJN +2.625.000, KDR -150.000, LMG -130.625.000, MLG -75.325.000 (saldo akhir; saldo awal -4.125.000), PSR -161.787.500, TAG -64.825.000. Lainnya 0.

## Metodologi PAKAN (BEDA dari DOC)
- **TIDAK ada PPh** (DOC ada 0.25%). Lebih sederhana.
- NAIK hutang = BBM via `terima_pakan` (tbl_name='terima_pakan'), link: terima_pakan.id_kirim_pakan -> kirim_pakan (punya no_order OPK/UNIT/.. & no_sj). Unit dari SUBSTRING(no_order,5,3).
- TURUN = BYR (realisasi transaksi='PAKAN') + CN (cn_post jenis_cn='PKN') + memo.
- konfirmasi_pembayaran_pakan: nomor=BYP/..., kolom **invoice = nomor SJ** (3016xxxxxxx). det: kode_unit, no_order, no_sj, total.
- **KUNCI: CN di GL di-tag pakai nomor SJ** (=konfirmasi.invoice), BUKAN nomor BYP. Pembayaran (BYR) di GL pakai nomor BYP. Jadi cek CN harus map lewat konfirmasi.invoice (SJ). Salah join pakai BYP -> CN seolah hilang.

## Tipe error utama PAKAN = ERROR JURNAL GL (bukan timing/PPh spt DOC)
1. **BBM ter-jurnal DOBEL**: terima_pakan ganda (1 kirim_pakan -> 2 terima_pakan, no_bbm sama + suffix -2) -> naik 21180.100 2x. Contoh PSR SJ 3016303028 (terima_pakan id 9901+9902, BBM/PKN/S/PSR/25/11120 & -2) = +65.050.000; SJ 3016262723 = +64.000.000.
2. **Pembayaran ter-jurnal DOBEL**: BYR turun 2x (contoh PSR BYP/02/26/01237 BYR/02/26/00437 baris 11130.001->21180.100 64jt 2x).
3. **CN salah akun**: CN PKN diposting ke 21180.200 (DOC) bukan 21180.100 (PAKAN). Contoh BYP/10/25/00065 = 32.737.500 (sama dgn kasus PSR di DOC, ada memo MM2602280004).

## PSR -161.787.500 SUDAH terurai tuntas
= BBM dobel 3016303028 (65.050.000) + BBM dobel 3016262723 (64.000.000) + CN salah akun BYP/10/25/00065 (32.737.500). Semua error jurnal GL.

## Query verifikasi (READ-ONLY)
- op saldo awal per unit vs saldo_bulanan: cocok utk lokalisasi.
- per-SJ BBM naik vs konfir (cari dobel): join terima_pakan->kirim_pakan, GROUP BY no_sj, bandingkan SUM(kppd.total).
- DB: env.php sqlsrv 103.137.111.6:14330 gmp_erp_live sa/Mgb654321. READ ONLY kecuali edit controller. User benahi data sendiri.

## Fix controller (BAYAR LEWAT MEMO kontrol reklasifikasi)
Ditambahkan di KEDUA byr_mm (saldo awal & bulan berjalan) LaporanHutangRingkas.php: `and mi.coa_asal not in ('21180.300','21174.000','21180.200','21180.100','21173.000')` -- lewati memo reklasifikasi antar-hutang (mis. koreksi CN salah akun DOC<->PAKAN MM2602280004/005). Hanya 3 memo kena (PSR/MLG DOC->PAKAN, BWI OVK). Ini menyelesaikan PSR (CN BYP/10/25/00065 tidak lagi dobel di op).

## Trik no_invoice utk memo koreksi BBM (BJN solved)
BJN +2.625.000: BBM under-recorded (harga 8000 vs konfir 8150), ADA memo MM2512310064 (KOREKSI PERSEDIAAN, coa_asal=21180.100 coa_tujuan=12030) yg menaikkan hutang di GL = benar. Tapi op DOBEL (konfir sudah harga benar + memo dibaca inv_mm). USER FIX: pecah memo per-invoice & isi no_invoice = nomor konfirmasi BYP -> inv_mm NOT EXISTS jadi FALSE -> memo di-skip dari op -> BJN 0. (Sama pola trik no_invoice di CN DOC.)

STATUS PAKAN (per cek terakhir): hampir semua 0 (PSR/TAG/BJN/MLG + BWI/GSK/JBR/LMJ/MDN/MGT/MJK/PRB/SLM). 

## STATUS: PAKAN TUNTAS 100% (semua 15 unit selisih 0). -430.087.500 -> 0. MLG jurnal dobel beres. Verifikasi terakhir: op vs saldo_bulanan = 0 semua unit.

## (HISTORIS) TO-DO PAKAN yg sudah dikerjakan
0. **MLG -71.200.000: HAPUS 1 baris jurnal BBM duplikat.** SJ 3016694176 (BBM/PKN/S/MLG/26/06134, tp_id 52430, tgl 13 Juni). Hanya 1 terima_pakan tapi det_jurnal punya 2 baris 21180.100->12030.000 @ 71.200.000 (naik dobel). Hapus salah satu baris duplikat di det_jurnal. (Beda dgn dobel terima_pakan PSR/LMG/TAG -- ini JURNAL yg dobel utk 1 terima_pakan yg sama.) Muncul di cek per-SJ: n_bbm=2 vs n_konf=1.
1. **LMG: SELESAI 0.** User naikkan konfir SJ 3016297500 ke 65.600.000 (=BBM, pola KDR). KOREKSI sebelumnya KELIRU: AI sempat sangka MM2512310063 baris 1,6M itu DUPLIKAT MM2512310070 -> SALAH. Faktanya 2 memo 1,6M itu untuk koreksi BERBEDA (invoice/SJ lain di LMG). Bukti: setelah fix, SEMUA SJ LMG BBM=konfir (cek per-SJ kosong), total BBM naik = total konfir = 73.543.962.500, op=saldo_bulanan=0. Kalau benar duplikat, unit akan over-reduce 1,6M & tidak balance. JANGAN hapus memo apa pun di LMG.

## POLA FIX (ringkasan jenis selisih BBM vs konfirmasi)
- **BBM < konfir** (BBM kurang): naik via memo coa_asal=21180.100 -> masuk INVOICE LEWAT MEMO yg PUNYA filter NOT EXISTS, jadi isi no_invoice=nomor konfirmasi -> memo ter-SKIP dari op (BJN solved).
- **BBM > konfir** (BBM kelebihan): memo turun coa_tujuan=21180.100 -> masuk BAYAR LEWAT MEMO yg TIDAK punya NOT EXISTS, jadi no_invoice TIDAK menghentikan -> DOBEL. SOLUSI (KDR DONE): NAIKKAN konfirmasi ke nilai BBM, lalu memo(turun selisih)+pembayaran = konfirmasi. Memo jadi terpakai benar, jurnal tak disentuh. KDR: konfir 65.400.000->65.550.000 (=BBM), bayar 65.400.000 + memo 150.000 = 65.550.000. SELESAI 0.
- Kenapa byr_mm tak bisa pakai no_invoice: kalau ditambah NOT EXISTS, memo PEMBAYARAN sah (settlement spt DOC MM2512310049/050) ikut hilang. Jadi tidak diubah.

KDR: SELESAI 0 (user naikkan konfir ke 65.550.000).

CATATAN PENTING: saldo_bulanan = snapshot 12 Juni, TIDAK update saat user tambah memo/perbaikan baru. Jadi cek AI via saldo_bulanan bisa tampil selisih semu utk fix yg baru. Verifikasi final harus via laporan GL live user.

## Fix kontrol NULL-handling (untuk OVK EXTERN BWI & memo split)
Kontrol reklasifikasi di inv_mm (coa_tujuan) & byr_mm (coa_asal) di LaporanHutangRingkas.php (4 lokasi: baris ~471,789,1119,1437) diubah jadi `(coa IS NULL OR coa NOT IN (akun hutang))`. Sebab: SQL `NULL NOT IN (...)` = UNKNOWN -> baris ter-skip. User pakai pola memo SPLIT satu-arah utk reklasifikasi supplier (BWI MM2604270001): baris1 coa_asal=21174 coa_tujuan=NULL no_invoice=NULL (inv_mm EXTERN debet, tambah hutang supplier baru), baris2 coa_asal=NULL coa_tujuan=21180.300 no_invoice=BYV/03/26/00073 (byr_mm OVK kredit, kurangi invoice). Dgn NULL-handling, kedua baris terbaca benar; memo reklasifikasi antar-hutang (kedua sisi payable, bukan NULL) tetap ter-skip. OVK EXTERN = COA 21174.000; supplier EXTERN dari pelanggan_coa.kode='OVK EXTERN' (no_coa 21174). CN/BBM voadip via terima_voadip->kirim_voadip->no_order.

## TO-DO OVK EXTERN (sisa pembulatan, per cek terakhir)
- **JBR +0,48** (SJ 002817 / invoice konfir voadip) = pembulatan sen, beda kecil BBM vs konfir.
- **MJK +0,20** (SJ 047689; sebenarnya BBM 47.072.700 = total 2 konfir BYV/10/25/00107 + BYV/10/25/00043, sisa cuma sen) = pembulatan.
- Keduanya < Rp1, kemungkinan pembulatan desimal — bisa diterima sbg reconciled atau ditelusuri kalau mau persis 0.
- Status OVK EXTERN: semua unit 0 KECUALI BWI (+2,5M di cek AI = SEMU krn saldo_bulanan snapshot 12 Juni belum muat memo split MM2604270001; live harusnya 0 — perlu verifikasi laporan live user) + JBR/MJK sen pembulatan.
