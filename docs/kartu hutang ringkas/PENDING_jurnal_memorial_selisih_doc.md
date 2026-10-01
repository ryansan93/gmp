# PENGINGAT — Jurnal Memorial untuk Selisih Terima DOC vs Jurnal Hutang

> Daftar invoice DOC yang masih perlu **jurnal memorial koreksi**. Tandai/hapus
> baris setelah dijurnal.

## 1. BYD/11/25/00015 — UNIT MAGETAN (MGT) — sisa **0,27** ⏳ AKAN DIJURNAL JUNI 2026

**Status:** menunggu jurnal memorial bulan Juni 2026 (rencana user).

**Ledger 21180.200 untuk invoice ini:**

| arah  | sumber                              | nominal        |
|-------|-------------------------------------|---------------:|
| NAIK  | BBM/DOC/MGT/25/11001 (terima DOC)   | 71.260.000,00  |
| NAIK  | MM2603310128 (band-aid pembulatan)  | 498,48         |
|       | **Total naik**                      | **71.260.498,48** |
| TURUN | BYR/11/25/00028 — transfer bank     | 71.082.348,75  |
| TURUN | BYR/11/25/00028 — PPh (24622)       | 178.150,00     |
|       | **Total turun**                     | **71.260.498,75** |
|       | **Saldo (naik − turun)**            | **−0,27**      |

**Akar masalah:** transfer di `BYR/11/25/00028` = 71.082.348,75 (seharusnya
71.081.850,00) → kelebihan bayar **498,75**. Band-aid `MM2603310128` menutup
**498,48**, menyisakan **0,27**.

**Tindakan:** buat memorial Juni 2026 menambah hutang **0,27** pada
COA 21180.200 unit MGT (NAIK 21180.200) untuk menutup sisa kelebihan bayar,
sehingga saldo hutang invoice ini = 0.

- [ ] Sudah dijurnal (isi no_mm di sini setelah selesai): __________

---

## 2. MM2512310055 — Hadi Suprayitno, UNIT JBR — sisa **338.485** ⏳ BELUM DIJURNAL

**Status:** ditemukan 2026-08-26 saat cek Kartu Hutang Per Invoice bulan Desember 2025.

**Kronologi:**
- Order `ODC/JBR/25/11010` (Hadi Suprayitno, unit JBR, total 135.394.000) diterima
  DOC-nya (`terima_doc` ada), tapi **tidak pernah dikonfirmasi resmi** lewat
  `konfirmasi_pembayaran_doc` — jadi tidak pernah dapat nomor invoice BYD asli.
- 11 Nov 2025: dibayar via batch transfer `BYR/11/25/00137` (id 316), tapi
  `realisasi_pembayaran_det.no_bayar` diisi kode memo `MM2512310055` (bukan
  invoice BYD asli, karena memang belum ada). Transfer tercatat 135.055.515,00.
- 31 Des 2025: dibuat jurnal memorial `MM2512310055` ("KOREKSI DOC HADI
  SUPRAYTNO KDG : 2") yang membukukan hutang DOC naik 21180.200 sebesar
  135.394.000,00 secara retroaktif.
- **PPh 0,25% (338.485,00) atas pembayaran ini TIDAK PERNAH dijurnal** — beda
  dengan `BYD/11/25/00082` (invoice lain, kebetulan totalnya identik
  135.394.000, dibayar di batch yang sama) yang PPh-nya benar tercatat. Bukan
  salah label — memang belum pernah dibuat sama sekali untuk leg ini.

**Ledger (per Kartu Hutang Per Invoice, kode "invoice" = MM2512310055):**

| arah  | sumber                                   | nominal        |
|-------|-------------------------------------------|---------------:|
| NAIK  | MM2512310055 (memorial 31 Des 2025)       | 135.394.000,00 |
| TURUN | BYR/11/25/00137 — transfer (11 Nov 2025)  | 135.055.515,00 |
| TURUN | PPh — **belum ada jurnalnya**             | 0,00           |
|       | **Saldo (naik − turun)**                  | **338.485,00** |

**Tindakan:** buat jurnal memorial PPh 22 sebesar **338.485,00** (COA 24622
PPH Psl 22, turun 21180.200 unit JBR) atas pembayaran `MM2512310055` /
Hadi Suprayitno, supaya saldo invoice ini = 0.

- [ ] Sudah dijurnal (isi no_mm di sini setelah selesai): __________

---

## 3. BYD/11/25/00332 — MM2512310051, UNIT MOJOKERTO (MJK), plasma Samsul Huda — ✅ SELESAI, BUKAN masalah data

**Status:** ditemukan 2026-08-26, sempat diduga double booking — TERNYATA
bug di laporan Kartu Hutang Per Invoice, sudah diperbaiki hari yang sama.
**Tidak perlu tindakan jurnal apapun.**

**Klarifikasi user (26 Agu 2026):** memo `MM2512310051` BUKAN hutang baru —
jurnal otomatis `terima_doc` untuk order ini (`ODC/MJK/25/11026`, terima_doc
id 843) **gagal jalan** (diverifikasi: 0 baris `det_jurnal` utk tbl_name=
'terima_doc' tbl_id='843'), jadi hutangnya dibukukan manual lewat memorial
sebagai penggantinya — bukan tambahan di atas yang sudah ada.

**Root cause bug laporan:** cabang "Invoice Lewat Memo" menghitung `mi.nilai`
sebagai hutang TAMBAHAN untuk SEMUA memo NAIK 21180.200, padahal kalau
invoice-nya SUDAH terkonfirmasi normal (ada di `konfirmasi_pembayaran_doc`
dkk), nilai itu SUDAH terhitung lewat cabang konfirmasi normal — jadi dobel
hitung. Fixed: memo NAIK yang `no_invoice`-nya cocok ke invoice yang sudah
terkonfirmasi (cek via `konfir_helper`) sekarang DIKECUALIKAN dari total
hutang (dianggap cuma "penyusulan GL", bukan hutang baru). Detail teknis di
[[kartu-hutang-per-invoice-enhancements]].

<details><summary>Kronologi & ledger lengkap (untuk referensi historis)</summary>

**Kronologi:**
- Invoice `BYD/11/25/00332` (order `ODC/MJK/25/11...`, total 106.890.000)
  dikonfirmasi normal 29 Nov 2025 lewat `konfirmasi_pembayaran_doc` seperti
  biasa (bukan kasus "belum dikonfirmasi" seperti item lain di file ini).
- 2 Des 2025: **dibayar lunas normal** — transfer 106.622.775 + PPh 267.225 =
  106.890.000, persis pas dengan total tagihan. Saldo invoice ini = 0 di titik
  ini.
- 31 Des 2025: jurnal memorial `MM2512310051` ("KOREKSI DOC PLASMA SAMSUL
  HUDA KDG:1") **menambah lagi hutang DOC 106.890.000** (NAIK 21180.200,
  referensi `invoice = BYD/11/25/00332` langsung) — **tanpa ada pembayaran
  susulan apapun setelahnya**. Sejak itu invoice ini "menggantung" lagi
  senilai persis sama dengan yang sudah pernah dibayar.

**Kecurigaan:** memo ini kemungkinan salah sasaran — dibuat dalam satu batch
bersama memo serupa lain (mis. `MM2512310055` / Hadi Suprayitno, KDG:2 — lihat
item terkait di [[pending-jurnal-memorial-doc-mgt]]) yang tujuannya membukukan
DOC yang **belum pernah dikonfirmasi** di sistem. Tapi invoice ini
(`BYD/11/25/00332`, KDG:1) BEDA kasus — sudah dikonfirmasi & lunas normal
sebelum memo ini dibuat, jadi kalau memo ini niatnya "membukukan yang belum
tercatat", ini kemungkinan salah tag ke invoice yang sudah beres.

**Ledger 21180.200 untuk invoice ini:**

| tanggal | arah | sumber | nominal |
|---------|------|--------|--------:|
| 29 Nov 2025 | NAIK | konfirmasi_pembayaran_doc (normal) | 106.890.000,00 |
| 02 Des 2025 | TURUN | BYR/12/25/00061 — transfer | 106.622.775,00 |
| 02 Des 2025 | TURUN | BYR/12/25/00061 — PPh (24622) | 267.225,00 |
| 31 Des 2025 | NAIK | MM2512310051 — memorial koreksi | 106.890.000,00 |
| | | **Saldo (naik − turun)** | **106.890.000,00** |

**Tindakan (SUDAH tidak relevan, dibiarkan sbg histori):** ~~minta konfirmasi
ke accounting, buat jurnal reversal~~ — batal, sudah dikonfirmasi user bukan
duplicate. GL/jurnal apa adanya sudah benar; yang salah cuma cara laporan
Kartu Hutang Per Invoice menghitungnya (sudah diperbaiki).

</details>

---

## Cara memantau selisih (query referensi)
- `hutang_vs_jurnal.sql` — kolom `selisih` per invoice (DOC sudah termasuk
  koreksi memorial; residu lama hanya invoice di atas, sisanya pipeline Juni-2026).
- `doc_terima_belum_dijurnal.sql` — daftar terima_doc yang belum/kurang dijurnal
  (status `BELUM DIJURNAL` / `MASIH KURANG` selain tanggal hari ini = perlu tindak).

_Catatan: di `hutang_vs_jurnal.sql`, invoice ini tampil −498,48 karena tabel
membandingkan total vs sisi NAIK saja (band-aid menggelembungkan naik). Selisih
riil pada saldo = −0,27._
