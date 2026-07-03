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

## Cara memantau selisih (query referensi)
- `hutang_vs_jurnal.sql` — kolom `selisih` per invoice (DOC sudah termasuk
  koreksi memorial; residu lama hanya invoice di atas, sisanya pipeline Juni-2026).
- `doc_terima_belum_dijurnal.sql` — daftar terima_doc yang belum/kurang dijurnal
  (status `BELUM DIJURNAL` / `MASIH KURANG` selain tanggal hari ini = perlu tindak).

_Catatan: di `hutang_vs_jurnal.sql`, invoice ini tampil −498,48 karena tabel
membandingkan total vs sisi NAIK saja (band-aid menggelembungkan naik). Selisih
riil pada saldo = −0,27._
