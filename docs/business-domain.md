# Domain Bisnis — GM ERP

## Gambaran Bisnis

GM ERP mengelola operasional **peternakan ayam broiler** dengan skema **plasma-inti**:

- **Inti (perusahaan)**: menyediakan DOC, pakan, obat/vaksin; membeli hasil panen
- **Plasma (peternak/mitra)**: memelihara ayam di kandang, menerima sapronak, menyerahkan hasil panen
- **Bakul (pelanggan)**: pembeli ayam hidup dari hasil panen

---

## Alur Bisnis End-to-End

```
1. PERSIAPAN KANDANG
   PeriodeFiskal → StandarBudidaya → RDIM (Rencana Distribusi Info Mitra)

2. PENGADAAN SAPRONAK
   OrderDoc → TerimaDoc      (DOC/bibit)
   OrderPakan → TerimaPakan  (pakan)
   OrderVoadip → TerimaVoadip (obat/vaksin)

3. DISTRIBUSI KE KANDANG
   KirimPakan → PenerimaanPakan (di kandang)
   KirimVoadip → PenerimaanVoadip (di kandang)
   ODVP (distribusi voadip ke mitra)

4. PEMELIHARAAN HARIAN
   LHK (Laporan Harian Kandang):
     - BB (Berat Badan) rata-rata
     - ADG (Average Daily Gain)
     - FCR (Feed Conversion Ratio)
     - IP (Indeks Performa)
     - DH (Deplesi Harian) = kematian
     - Pemakaian & sisa pakan
     - Nekropsi (penyakit/kematian)
     - Solusi & rekomendasi
     - Peralatan kandang
     - Foto dokumentasi

5. PANEN
   KonfirmasiPanen → RealisasiSJ (Surat Jalan) → BASTTB
   RPAH (Rekap Perhitungan Akhir Hasil)

6. PENJUALAN & TAGIHAN
   RealisasiSJ → Invoice penjualan ke bakul
   PembayaranBakul → KonfirmasiPembayaran → VerifikasiPembayaran

7. PEMBAYARAN PLASMA
   RHPP (Rekap Hasil Panen Plasma):
     - Harga jual ayam
     - Biaya DOC, pakan, voadip
     - Bonus/insentif performa
     - Potongan (utang, pajak)
   KonfirmasiPembayaranPeternak → RealisasiPembayaran

8. AKUNTANSI
   InsertJurnal (otomatis setiap transaksi)
   Memorial (jurnal manual)
   TutupBulan (closing bulanan)
   Neraca, LabaRugi, GeneralLedger
```

---

## Entitas Bisnis Utama

### Mitra (Peternak/Plasma)

- Data di tabel `mitra`
- Memiliki `id_kandang` → satu mitra bisa punya banyak kandang
- SKP (Standar Kinerja Peternak): kontrak performa
- Pola kerjasama: `pola_kerjasama` (mengatur bagi hasil)

### Bakul (Pelanggan)

- Data di tabel `pelanggan`
- Pembeli hasil panen ayam hidup
- Memiliki limit kredit, potongan, dan rekening bank
- Pembayaran: bisa tunai, transfer, atau cicilan

### Sapronak (Sarana Produksi Peternakan)

- **DOC** (Day Old Chick): bibit ayam umur 1 hari
- **Pakan**: ransum ayam (starter/finisher)
- **Voadip** / **OVK** (Obat, Vaksin, Kimia): obat dan vaksin

### Kandang

- Unit produksi utama
- Memiliki `sekat` (pembagian area kandang)
- Status: aktif/tidak aktif
- Kapasitas: jumlah populasi maksimum

### Siklus Budidaya

- Satu siklus = dari chick-in hingga panen habis
- `id_noreg` atau `id_siklus`: identifikasi siklus
- Data stok, LHK, dan RHPP terikat ke siklus

---

## Indikator Performa Kandang (KPI)

| Indikator | Kepanjangan | Keterangan |
|---|---|---|
| **BB** | Berat Badan | Rata-rata berat per ekor (gram) |
| **ADG** | Average Daily Gain | Pertambahan berat per hari (gram/hari) |
| **FCR** | Feed Conversion Ratio | Rasio pakan vs berat ayam (lebih kecil = lebih baik) |
| **IP** | Indeks Performa | Skor performa keseluruhan kandang |
| **DH** | Deplesi Harian | Persentase kematian harian |
| **SR** | Survival Rate | Persentase ayam yang hidup hingga panen |

---

## Alur Dokumen

### Dokumen Pengadaan (Pembelian)

```
Purchase Request → Order [DOC/Pakan/Voadip] → Terima [DOC/Pakan/Voadip]
     (PR)              (SPM/Order)                 (Penerimaan)
                                                        │
                                                   Insert Jurnal
                                                   (Hutang Dagang naik,
                                                    Persediaan naik)
```

### Dokumen Distribusi ke Kandang

```
Terima [Pakan/Voadip] → Kirim [Pakan/Voadip] → Penerimaan di Kandang
                              (Surat Jalan)          (Kartu Stok Kandang)
                                                           │
                                                      LHK (pemakaian harian)
```

### Dokumen Panen

```
Estimasi Panen → Konfirmasi Panen → Realisasi SJ → BASTTB
                     (tanggal,             (berat,        (serah terima)
                      populasi)             harga)
                                                │
                                           Invoice/Piutang
                                           ke Bakul
                                                │
                                           Pembayaran Bakul
                                                │
                                           Realisasi Pembayaran
```

### Dokumen RHPP (Settlement ke Peternak)

```
Realisasi Panen (semua data siklus)
    │
    ├── Biaya DOC (dari TerimaDoc)
    ├── Biaya Pakan (dari TerimaPakan)
    ├── Biaya Voadip (dari TerimaVoadip)
    ├── Nilai Jual Ayam (dari RealSJ)
    ├── Bonus Performa (dari StandarPerforma)
    └── Potongan (utang, pajak PPh 21/23)
         │
         ▼
    RHPP (kalkulasi net payment ke peternak)
         │
         ▼
    KonfirmasiPembayaranPeternak → RealisasiPembayaran
```

---

## Kode Dokumen

| Kode | Dokumen |
|---|---|
| `ODC` | Order DOC |
| `TDC` | Terima DOC |
| `SPM` | Surat Permintaan Material (pakan) |
| `PKN` / `TPK` | Penerimaan Pakan |
| `KPK` | Kirim Pakan |
| `OVK` / `TVD` | Penerimaan Voadip |
| `KVD` | Kirim Voadip |
| `LHK` | Laporan Harian Kandang |
| `RPH` | Rekapitulasi Penimbangan Hasil (RPAH) |
| `RSJ` | Realisasi Surat Jalan |
| `RHPP` | Rekap Hasil Panen Plasma |
| `KK` | Kas Keluar |
| `KM` | Kas Masuk |
| `BK` | Bank Keluar |
| `BM` | Bank Masuk |
| `MM` | Memorial |
| `CN` | Credit Note |
| `DN` | Debit Note |

---

## Modul Accounting — Alur Tutup Bulan

```
1. Verifikasi semua transaksi sudah diposting
2. Hitung ulang saldo (HitungUlang)
3. TutupBulan:
   - Lock periode (tidak bisa transaksi di periode tersebut)
   - Roll saldo ke periode berikutnya (SaldoBulanan)
   - Generate laporan keuangan (Neraca, LabaRugi)
```

---

## Multi-Database Usage

| Operasi | DB |
|---|---|
| Semua transaksi bisnis | `gmp_erp_live` (default) |
| Log perubahan data | `log_history_gmp_erp_live` (log) |
| Data mitra dari sistem lain | `mgb_erp_live` (mgb) |
