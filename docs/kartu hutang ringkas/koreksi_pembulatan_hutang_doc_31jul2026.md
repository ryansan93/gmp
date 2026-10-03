# Dokumentasi — Koreksi Pembulatan Hutang DOC (COA 21180.200) per 31 Juli 2026

> Ringkasan seluruh jurnal memorial yang dibuat untuk menol-kan sisa pecahan
> rupiah (residu pembulatan) di saldo hutang niaga ORP DOC (COA `21180.200`)
> per unit, bulan Juli 2026. **Status: sudah dibuat di database LOKAL
> (`gmp_erp_live` @ `localhost,1433`) saja — BELUM di-push/insert ke server
> LIVE (`103.137.111.6,14330`).**

## Latar belakang

Saat rekonsiliasi saldo GL `21180.200` per unit untuk bulan Juli 2026,
ditemukan residu pecahan rupiah (0,01 s/d 1,05) di 7 unit. Akar masalahnya
sama di semua kasus: kolom **`realisasi_pembayaran_det.pembulatan`** (computed
column, `tagihan − transfer` saat selisihnya < Rp1) **tidak pernah dijurnal**
ke `det_jurnal` — jadi setiap kali ada pembulatan transfer (atau PPh yang
mempengaruhi jumlah transfer), GL jadi tidak pas dengan jumlah yang
sebenarnya ditransfer/ditagih.

Pola arah koreksi:
- **TURUN hutang** (debit `21180.200`, kredit COA lawan) — dipakai saat GL
  masih mencatat hutang LEBIH BESAR dari yang seharusnya (kasus "kurang
  bayar" secara pencatatan — transfer riil sudah menutup, GL belum ikut
  turun).
- **NAIK hutang** (kredit `21180.200`, debit COA lawan) — dipakai saat GL
  mencatat hutang LEBIH KECIL dari seharusnya (kasus "lebih bayar" secara
  pencatatan — GL sudah turun lebih banyak dari yang seharusnya).

COA lawan yang dipakai: `96010.000` (Selisih Pembulatan) untuk kasus umum,
kecuali `MM2607310027` (MGT) yang memakai `71105.003` karena merupakan revisi
dari memo lama `MM2606190003` (COA lawan mengikuti memo yang direvisi).

Semua entri dibuat dengan `tgl_mm = 2026-07-31`, periode `2026-07`.

## Daftar lengkap (17 memorial)

| No. Memorial | Unit | Invoice | Arah | Nominal | COA Asal → COA Tujuan | Keterangan |
|---|---|---|---:|---:|---|---|
| MM2607310018 | MLG | BYD/03/26/00019 | TURUN hutang | 0,08 | 96010.000 → 21180.200 | Koreksi pembulatan transfer |
| MM2607310019 | MLG | BYD/06/26/00047 | TURUN hutang | 0,24 | 96010.000 → 21180.200 | Koreksi pembulatan transfer |
| MM2607310020 | MLG | BYD/06/26/00257 | TURUN hutang | 0,36 | 96010.000 → 21180.200 | Koreksi pembulatan transfer |
| MM2607310021 | MLG | BYD/02/26/00017 | NAIK hutang | 0,01 | 21180.200 → 96010.000 | Koreksi sisa desimal PPh DOC |
| MM2607310023 | MGT | BYD/02/26/00054 | TURUN hutang | 0,22 | 96010.000 → 21180.200 | Koreksi pembulatan transfer |
| MM2607310024 | MGT | BYD/02/26/00285 | TURUN hutang | 0,31 | 96010.000 → 21180.200 | Koreksi pembulatan transfer |
| MM2607310025 | MGT | BYD/03/26/00364 | TURUN hutang | 0,20 | 96010.000 → 21180.200 | Koreksi pembulatan transfer |
| MM2607310027 | MGT | BYD/11/25/00015 | TURUN hutang | 0,46 | 71105.003 → 21180.200 | Koreksi sisa desimal (**revisi MM2606190003**) |
| MM2607310028 | MGT | BYD/12/25/00392 | NAIK hutang | 0,46 | 21180.200 → 96010.000 | Koreksi kelebihan transfer |
| MM2607310030 | KDR | BYD/01/26/00273 | TURUN hutang | 0,35 | 96010.000 → 21180.200 | Koreksi pembulatan transfer |
| MM2607310031 | LMJ | BYD/01/26/00228 | NAIK hutang | 0,01 | 21180.200 → 96010.000 | Koreksi sisa desimal PPh DOC |
| MM2607310032 | TAG | BYD/05/26/00138 | TURUN hutang | 0,50 | 96010.000 → 21180.200 | Koreksi pembulatan transfer |
| MM2607310033 | TAG | BYD/01/26/00053 | TURUN hutang | 0,35 | 96010.000 → 21180.200 | Koreksi pembulatan transfer |
| MM2607310034 | MDN | BYD/05/26/00208 | TURUN hutang | 0,50 | 96010.000 → 21180.200 | Koreksi pembulatan transfer |
| MM2607310035 | MDN | BYD/06/26/00360 | TURUN hutang | 0,50 | 96010.000 → 21180.200 | Koreksi pembulatan transfer |
| MM2607310036 | MDN | BYD/12/25/00019 | TURUN hutang | 0,05 | 96010.000 → 21180.200 | Koreksi pembulatan transfer |
| MM2607310037 | BWI | BYD/05/26/00117 | TURUN hutang | 0,05 | 96010.000 → 21180.200 | Koreksi pembulatan transfer |

## Hasil per unit (saldo residu sebelum → sesudah)

| Unit | Residu sebelum | Residu sesudah | Catatan |
|---|---:|---:|---|
| MLG | 0,67 | **0,00** | Selesai |
| MGT | 0,72 | **0,01** | 1 item sengaja dibiarkan — lihat di bawah |
| KDR | 0,35 | **0,00** | Selesai |
| LMJ | 0,01 | **0,00** | Selesai |
| TAG | 0,85 | **0,00** | Selesai |
| MDN | 1,05 | **0,00** | Selesai |
| BWI | 0,05 | **0,01** | 1 item sengaja dibiarkan — lihat di bawah |

### Residu yang SENGAJA tidak dikoreksi (MGT & BWI, masing-masing 0,01)

Untuk `BYD/08/26/00058` (MGT) dan `BYD/08/26/00059` (BWI), ditemukan bahwa
ada nilai CN di `cn_post_det` (masing-masing 970.422 dan 211.818) yang
**tidak pernah dijurnal ke `det_jurnal` sama sekali** — beda dengan kasus
lain yang cuma selisih pembulatan sub-rupiah. Ini indikasi masalah yang
lebih besar (CN tidak terjurnal), bukan sekadar pembulatan. Sempat dibuat
memo tambal-sulam 0,01 untuk MGT (`MM2607310029`) tapi **dihapus atas
instruksi user** — diputuskan untuk TIDAK ditutup dengan memo pembulatan,
menunggu perbaikan akar masalah CN yang tidak terjurnal.

## Root cause & rencana perbaikan jangka panjang

Root cause (`pembulatan`/PPh tidak dijurnal) sudah diperbaiki di kode
aplikasi — lihat commit `ef5cf64` ("Fix PPh tidak tersimpan & tagihan salah
di Realisasi Pembayaran DOC"), yang menyimpan PPh dengan benar dan memakai
nilai sisa (netto) sebagai basis tagihan/bayar/transfer, sehingga kasus baru
seperti ini seharusnya tidak berulang untuk pembayaran DOC yang dibuat
setelah fix ini deploy.

Untuk backfill kolom `pph` pada data DOC bulan Agustus 2026 yang terlanjur
tersimpan `pph = 0`, ada query terpisah — lihat
`update_pph_doc_agustus.sql` (scratchpad sesi terkait).

## Yang masih perlu dilakukan

- [ ] Insert 17 memorial di atas ke server **LIVE** (belum dilakukan — baru
      ada di lokal `gmp_erp_live`).
- [ ] Investigasi akar masalah CN tidak terjurnal untuk `BYD/08/26/00058`
      (MGT) dan `BYD/08/26/00059` (BWI) sebelum menutup sisa 0,01 di
      masing-masing unit.
- [ ] Unit yang belum dicek untuk residu pembulatan serupa: BJN, JBR, PSR
      (dan unit lain di luar 7 unit yang sudah diproses).
