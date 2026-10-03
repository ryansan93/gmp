# Sinkronisasi Memo Koreksi: Database Lokal → LIVE (per 28 Agustus 2026)

> Rangkuman status semua memo koreksi (`mm`/`mmitem`/`det_jurnal`) yang dibuat di
> database lokal (`gmp_erp_live` @ `localhost,1433`, mirror LIVE) sepanjang sesi
> rekonsiliasi DOC & PAKAN, dan mana saja yang **belum** ada di server LIVE
> (`103.137.111.6:14330`).

## Sudah sinkron di LIVE — TIDAK perlu tindakan

Field `no_invoice` di 5 memo koreksi duplikasi/selisih PAKAN berikut sudah
terisi benar di LIVE (dicek langsung, sudah dikerjakan di sana):

| No. Memo | Unit | No. Invoice |
|---|---|---|
| MM2512310061 | PSR | BYP/11/25/01636 (64.000.000) & BYP/11/25/01635 (8.000.000 + 57.050.000) |
| MM2512310062 | TAG | BYP/11/25/01638 (20.000.000 + 44.825.000) |
| MM2512310063 | LMG | BYP/11/25/01637, BYP/11/25/01639, BYP/12/25/01071 |
| MM2512310070 | LMG | BYP/12/25/01071 (1.600.000) |
| MM2512310071 | KDR | BYP/12/25/00566 (150.000) |

## BELUM ada di LIVE — perlu di-push (18 memo baru, semua tgl 31 Juli 2026)

Script siap-jalan (di-export langsung dari data lokal, bukan ditulis ulang
manual): [`push_17_memo_doc_ke_live.sql`](../push_17_memo_doc_ke_live.sql)

### 17 memo koreksi pembulatan/PPh DOC (COA 21180.200)

Detail lengkap & latar belakang di
[koreksi_pembulatan_hutang_doc_31jul2026.md](kartu%20hutang%20ringkas/koreksi_pembulatan_hutang_doc_31jul2026.md).

| No. Memo | Unit | Invoice | Nilai |
|---|---|---|---:|
| MM2607310018 | MLG | BYD/03/26/00019 | 0,08 |
| MM2607310019 | MLG | BYD/06/26/00047 | 0,24 |
| MM2607310020 | MLG | BYD/06/26/00257 | 0,36 |
| MM2607310021 | MLG | BYD/02/26/00017 | 0,01 |
| MM2607310023 | MGT | BYD/02/26/00054 | 0,22 |
| MM2607310024 | MGT | BYD/02/26/00285 | 0,31 |
| MM2607310025 | MGT | BYD/03/26/00364 | 0,20 |
| MM2607310027 | MGT | BYD/11/25/00015 (revisi MM2606190003) | 0,46 |
| MM2607310028 | MGT | BYD/12/25/00392 | 0,46 |
| MM2607310030 | KDR | BYD/01/26/00273 | 0,35 |
| MM2607310031 | LMJ | BYD/01/26/00228 | 0,01 |
| MM2607310032 | TAG | BYD/05/26/00138 | 0,50 |
| MM2607310033 | TAG | BYD/01/26/00053 | 0,35 |
| MM2607310034 | MDN | BYD/05/26/00208 | 0,50 |
| MM2607310035 | MDN | BYD/06/26/00360 | 0,50 |
| MM2607310036 | MDN | BYD/12/25/00019 | 0,05 |
| MM2607310037 | BWI | BYD/05/26/00117 | 0,05 |

### 1 memo koreksi jurnal PAKAN (COA 21180.100)

| No. Memo | Unit | Invoice | Nilai | Keterangan |
|---|---|---|---:|---|
| MM2607310038 | LMG | BYP/12/25/01071 | 1.600.000 | Pembalik atas MM2512310070 (duplikat murni dari MM2512310063) — koreksi GL supaya tidak dobel-hitung |

## Cara push ke LIVE

Jalankan [`push_17_memo_doc_ke_live.sql`](../push_17_memo_doc_ke_live.sql)
langsung ke server LIVE (`103.137.111.6,14330`, database `gmp_erp_live`).
Script sudah termasuk query verifikasi di akhir (menampilkan ke-18 memo yang
baru diinsert) untuk memastikan berhasil.

- [ ] Sudah di-push ke LIVE (isi tanggal & nama di sini setelah selesai): __________

## Catatan penting

- Perubahan kode (`KartuHutangPerInvoiceV2.php`, `TSDRHPP.php`, dll.) yang
  dibuat sesi ini **otomatis berlaku** begitu file di-deploy — tidak perlu
  langkah manual terpisah seperti memo database ini.
- Kredensial koneksi LIVE ada di `application/config/env.php` (jangan
  ditulis di dokumen ini).
