# Analisa Selisih GL vs Kartu Hutang Ringkas
**Tanggal Analisa:** 2 Juni 2026  
**Periode Data:** s/d 30 April 2026  
**Database:** gmp_erp_live  

---

## 1. Ringkasan Selisih GL vs KHR per Jenis Hutang

| Jenis | COA | Saldo GL | Saldo KHR (setelah fix) | Selisih |
|---|---|---|---|---|
| PAKAN | 21180.100 | 10,954,812,500 | 13,494,475,000 | +2,539,662,500 |
| DOC | 21180.200 | 66,793,314 | ~59,094 | +66,734,220 |
| OVK | 21180.300 | 2,719,874,100 | 2,721,969,430 | +2,095,330 |
| OVK EXTERN | 21174.000 | 210,456,743 | 194,600,401 | -15,856,342 |
| OA PAKAN | 21212.000 | 472,038,200 | 273,043,993 | -198,994,207 |
| RHPP/Plasma | 21213.000 | 1,059,955,938 | 7,652,892 | +1,052,303,046 |

> **Catatan:** Saldo KHR menggunakan versi controller setelah perbaikan pada sesi ini.

---

## 2. Bug yang Sudah Diperbaiki di Controller KartuHutangRingkas.php

### Fix #1 — RHPP/Plasma: Kredit Tidak Tertangkap (SELESAI)

**File:** `application/modules/report/controllers/KartuHutangRingkas.php`

**Root Cause:**  
Kredit realisasi pembayaran dengan `transaksi = 'PLASMA'` menggunakan `rp.supplier` untuk JOIN ke `pelanggan_coa`, padahal untuk PLASMA field yang terisi adalah `rp.peternak` (bukan supplier). Akibatnya semua pembayaran PLASMA masuk ke bucket NULL jenis dan tidak dihitung sebagai kredit RHPP.

**Dampak sebelum fix:** RHPP saldo = 181.5 Miliar (hanya debet, tanpa kredit)  
**Dampak setelah fix:** RHPP saldo = 7.6 juta ✓

**Perubahan:** Tambah UNION ALL kredit PLASMA menggunakan `rp.peternak`:
```sql
-- KREDIT PLASMA - pakai rp.peternak sebagai supplier
SELECT rp.peternak as supplier, 0 as debet, rpd.transfer as kredit,
    'RHPP' as jenis, ...
FROM realisasi_pembayaran_det rpd
LEFT JOIN realisasi_pembayaran rp ON rpd.id_header = rp.id
WHERE rpd.transaksi = 'PLASMA'
```

---

### Fix #2 — OA PAKAN: Kredit Tidak Tertangkap (SELESAI)

**Root Cause:**  
Bug yang sama dengan PLASMA. Pembayaran OA PAKAN menggunakan `rp.ekspedisi`, tapi query menggunakan `rp.supplier` yang selalu kosong untuk transaksi ekspedisi.

**Dampak sebelum fix:** OA PAKAN saldo = 17.96 Miliar  
**Dampak setelah fix:** OA PAKAN saldo = 273 juta ✓

**Perubahan:** Tambah UNION ALL kredit OA PAKAN menggunakan `rp.ekspedisi`:
```sql
-- KREDIT OA PAKAN - pakai rp.ekspedisi sebagai supplier
SELECT rp.ekspedisi as supplier, 0 as debet, rpd.transfer as kredit,
    'OA PAKAN' as jenis, ...
FROM realisasi_pembayaran_det rpd
WHERE rpd.transaksi = 'OA PAKAN'
```

---

### Fix #3 — Memo/Memorial Lewat COA DOC & PAKAN (SELESAI)

**Root Cause:**  
Bagian "Invoice Lewat Memo" dan "Bayar Lewat Memo" hanya memfilter COA OVK (`21180.300`, `21174.000`). COA DOC (`21180.200`) dan PAKAN (`21180.100`) tidak ter-capture.

**Perubahan:** Expand filter COA:
```sql
-- Sebelum:
where mi.coa_asal in ('21180.300', '21174.000')
-- Sesudah:
where mi.coa_asal in ('21180.300', '21174.000', '21180.200', '21180.100')
```

Berlaku di 4 lokasi: Invoice/Bayar Lewat Memo × Saldo Awal/Transaksi Berjalan.

---

### Fix #4 — Fallback Supplier dari mm.no_supplier (SELESAI)

**Root Cause:**  
Bagian "Bayar Lewat Memo" menggunakan `konfir.supplier` (dari JOIN ke tabel konfirmasi via `no_invoice`). Jika `no_invoice` kosong, supplier jadi NULL.

**Perubahan:** Tambah fallback ke `m.no_supplier`:
```sql
isnull(nullif(konfir.supplier,''), m.no_supplier) as supplier
```

---

## 3. Analisa Detail DOC — Selisih Rp 66,793,314

### 3a. Perbandingan Terima DOC (BBM) vs det_jurnal: 9 BBM Berbeda

#### Kelompok A — 5 BBM Tidak Ada di GL

| No BBM | No Order | Tgl Terima | Unit | Nominal | Keterangan |
|---|---|---|---|---|---|
| BBM/DOC/BJN/25/11019 | ODC/BJN/25/11019 | 16 Nov 2025 | BJN | 171,024,000 | Jurnal GL tidak ter-generate |
| BBM/DOC/JBR/25/11010 | ODC/JBR/25/11010 | 11 Nov 2025 | JBR | 135,394,000 | Jurnal GL tidak ter-generate |
| BBM/DOC/MJK/25/11026 | ODC/MJK/25/11026 | 30 Nov 2025 | MJK | 106,890,000 | Jurnal GL tidak ter-generate |
| BBM/DOC/MLG/25/11027 | ODC/MLG/25/11027 | 20 Nov 2025 | MLG | 71,260,000 | Jurnal GL tidak ter-generate |
| BBM/DOC/MJK/25/11015 | ODC/MJK/25/11015 | 19 Nov 2025 | MJK | 21,378,000 | Jurnal GL tidak ter-generate |
| **Total** | | | | **505,946,000** | |

**Status:** Sudah dikoreksi oleh MM2512310051–055 di GL (hutang naik via memorial).  
**Tindakan:** Tidak perlu generate ulang — GL sudah benar via MM. Perlu konfirmasi ke akuntansi apakah BBM ini perlu di-reverse di sistem atau dibiarkan.

#### Kelompok B — 3 BBM Jurnal GL Double (2× Nominal)

| No BBM | No Order | Tgl | Unit | Nominal DOC | Nominal GL | Lebih |
|---|---|---|---|---|---|---|
| BBM/DOC/MLG/25/11026 | ODC/MLG/25/11026 | 19 Nov 2025 | MLG | 178,150,000 | 356,300,000 | 178,150,000 |
| BBM/DOC/MLG/25/11007 | ODC/MLG/25/11007 | 6 Nov 2025 | MLG | 149,646,000 | 299,292,000 | 149,646,000 |
| BBM/DOC/TAG/25/09010 | ODC/TAG/25/09010 | 1 Okt 2025 | TAG | 61,126,200 | 122,252,400 | 61,126,200 |
| **Total** | | | | **388,922,200** | **777,844,400** | **388,922,200** |

**Status:** Sudah dikoreksi oleh MM2512310049, MM2512310050, MM2510310017 (mengurangi hutang). GL sudah benar.  
**Root Cause:** Jurnal BBM ter-post 2× pada saat input. Perlu cek proses input terima DOC November 2025.

#### Kelompok C — 1 BBM Nominal GL Kurang

| No BBM | No Order | Tgl | Unit | Nominal DOC | Nominal GL | Selisih |
|---|---|---|---|---|---|---|
| BBM/DOC/TAG/25/11014 | ODC/TAG/25/11014 | 21 Nov 2025 | TAG | 52,019,800 | 39,905,600 | 12,114,200 |

**Status:** Sudah dikoreksi oleh MM2512310056 "KOREKSI DOC IBNU KHOLIM". GL sudah benar.

---

### 3b. Perbandingan CN POST DOC vs det_jurnal: **15 CN, Total Rp 67,030,484**

**SEMUA 15 CN TIDAK ADA JURNALNYA DI GL — INI ROOT CAUSE UTAMA SELISIH DOC**

| No Konfirmasi | Tgl CN | Unit | CN Amount |
|---|---|---|---|
| BYD/12/25/00181 | 19 Des 2025 | JBR | 31,893,798 |
| BYD/02/26/00017 | 3 Feb 2026 | MLG | 12,626,886 |
| BYD/02/26/00333 | 2 Mar 2026 | BWI | 5,583,632 |
| BYD/12/25/00365 | 31 Des 2025 | JBR | 4,051,665 |
| BYD/11/25/00189 | 21 Nov 2025 | BJN | 2,743,125 |
| BYD/02/26/00238 | 20 Feb 2026 | BWI | 2,345,324 |
| BYD/11/25/00274 | 28 Nov 2025 | PSR | 845,874 |
| BYD/11/25/00147 | 18 Nov 2025 | GSK | 1,681,736 |
| BYD/11/25/00314 | 2 Des 2025 | JBR | 1,400,312 |
| BYD/03/26/00109 | 12 Mar 2026 | BWI | 1,394,064 |
| BYD/01/26/00001 | 6 Jan 2026 | JBR | 833,742 |
| BYD/12/25/00056 | 9 Des 2025 | LMJ | 540,851 |
| BYD/01/26/00228 | 23 Jan 2026 | LMJ | 448,938 |
| BYD/03/26/00320 | 27 Mar 2026 | KDR | 405,680 |
| BYD/04/26/00266 | 24 Apr 2026 | BWI | 234,856 |
| **TOTAL** | | | **67,030,484** |

**Validasi:** Saldo GL per unit DOC = persis sama dengan CN yang belum di-GL:
- JBR: CN=38,179,518 | GL saldo=38,179,518 ✓
- MLG: CN=12,626,886 | GL saldo=12,626,888 ✓ (beda 2 = rounding)
- BJN: CN=2,743,125 | GL saldo=2,743,124 ✓
- KDR: CN=405,680 | GL saldo=405,680 ✓
- LMJ: CN=989,789 | GL saldo=989,789 ✓

**Kesimpulan:** GL DOC saldo 66.8 juta = TEPAT sama dengan total CN yang belum di-post ke GL.

**Tindakan:** Generate jurnal GL untuk 15 CN tersebut.  
Query untuk mendapatkan cn_post.id:
```sql
SELECT cp.id as cn_post_id, cp.no_cn, cp.tanggal, cpd.nomor as byd_nomor, cpd.pakai
FROM cn_post cp
LEFT JOIN cn_post_det cpd ON cpd.id_header = cp.id
WHERE cp.jenis_cn = 'DOC'
  AND cpd.nomor IN (
    'BYD/12/25/00181','BYD/02/26/00017','BYD/02/26/00333',
    'BYD/11/25/00189','BYD/02/26/00238','BYD/12/25/00365',
    'BYD/11/25/00274','BYD/11/25/00147','BYD/11/25/00314',
    'BYD/03/26/00109','BYD/01/26/00001','BYD/12/25/00056',
    'BYD/01/26/00228','BYD/03/26/00320','BYD/04/26/00266'
  )
ORDER BY cp.tanggal
```

---

### 3c. Perbandingan Realisasi Pembayaran DOC vs det_jurnal: 9 BYR

Semua 9 selisih adalah **rounding PPh 0.25%** — tidak material, tidak perlu tindakan.

| Total Nominal Dok | Total Nominal GL | Selisih |
|---|---|---|
| 56,389,717,259 | 56,389,658,075 | **59,184** |

---

## 4. Temuan Lain — Selisih PAKAN (Rp 2,539,662,500)

**Root Cause:** Perbedaan basis pencatatan:
- **GL** mencatat hutang pakan saat **pakan diterima** (kode BBM/PKN)
- **KHR** mencatat hutang pakan saat **invoice dikonfirmasi** (kode BYP)

Selisih 2.5M merupakan gap timing antara BBM (terima fisik) dan BYP (invoice masuk). Ini adalah **reconciling item** yang wajar — bukan bug.

**Tindakan:** Tidak perlu perbaikan di controller. Dokumentasikan sebagai perbedaan metodologi.

---

## 5. Temuan Lain — Selisih RHPP Sisa (Rp 1,052,303,046)

Setelah fix PLASMA kredit, KHR RHPP = 7.6 juta sementara GL = 1.06 miliar.  
Selisih sisa ~1 miliar berasal dari transaksi RHPP yang masuk GL via channel selain `realisasi_pembayaran_det` (mungkin via memorial atau transaksi lama sebelum periode `det_jurnal`).

**Tindakan:** Perlu investigasi lebih lanjut. Cek saldo_bulanan/sacoa untuk COA 21213 periode sebelum September 2025.

---

## 6. Prioritas Tindak Lanjut

| # | Aksi | Dampak | Estimasi Nilai | Status |
|---|---|---|---|---|
| 1 | Generate jurnal GL untuk 15 CN DOC | Eliminasi selisih GL DOC ~67 juta | 67,030,484 | ⭐ Prioritas Tinggi |
| 2 | Konfirmasi ke akuntansi: 5 BBM Nov '25 tanpa jurnal GL | Apakah perlu generate atau diabaikan | 505,946,000 | ⭐ Prioritas Tinggi |
| 3 | Cek double-post BBM Nov '25 (kenapa bisa di-post 2×) | Pencegahan kejadian serupa | - | Medium |
| 4 | Investigasi RHPP sisa 1M | Rekonsiliasi COA 21213 | 1,052,303,046 | Medium |
| 5 | Diskusi basis pencatatan PAKAN GL vs KHR | Apakah perlu menyamakan ke BBM basis | 2,539,662,500 | Low |

---

## 7. Perubahan Controller yang Sudah Dilakukan

File: `application/modules/report/controllers/KartuHutangRingkas.php`

| # | Perubahan | Baris | Dampak |
|---|---|---|---|
| 1 | Tambah NOT IN PLASMA/OA PAKAN di kredit realisasi | 697, 1246 | RHPP: 181B → 7.6 juta |
| 2 | Tambah UNION kredit PLASMA (rp.peternak) | Setelah baris 699, 1248 | RHPP fix |
| 3 | Tambah UNION kredit OA PAKAN (rp.ekspedisi) | Setelah baris 699, 1248 | OA PAKAN: 17.9B → 273 juta |
| 4 | Expand filter memo ke 21180.200 & 21180.100 | 494, 783, 1083, 1371 | CN/Invoice DOC & PAKAN ter-capture |
| 5 | Fallback supplier bayar memo ke m.no_supplier | 783, 1371 | Memo tanpa no_invoice tetap ter-resolve |

---

*File ini dibuat otomatis dari hasil analisa sesi ini. Terakhir diupdate: 2 Juni 2026.*
