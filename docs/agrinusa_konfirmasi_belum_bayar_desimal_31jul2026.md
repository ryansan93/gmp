# PT AGRINUSA JAYA SANTOSA (19B004) — Konfirmasi OVK Belum Dibayar dengan Nilai Desimal

> Dicek per 29 Agustus 2026, sebagai bagian dari rekonsiliasi hutang OVK
> Agrinusa (lihat [koreksi MM2607310052](../push_17_memo_doc_ke_live.sql) dan
> `docs/sinkronisasi_memo_lokal_ke_live_31jul2026.md` untuk daftar memo terkait).
>
> Daftar ini adalah konfirmasi `konfirmasi_pembayaran_voadip` supplier `19B004`
> yang **belum dibayar per 31 Juli 2026** (baik yang belum ada Realisasi
> Pembayaran sama sekali, ATAU realisasinya baru terjadi SETELAH 31 Juli —
> lihat kolom `tgl_realisasi_pertama`) dan nilai totalnya mengandung desimal
> (bukan bilangan bulat rupiah). Semuanya **normal secara bisnis** — baru
> dikonfirmasi Jun–Agu 2026, desimalnya murni dari harga per-kg yang pecahan.
> Sudah otomatis masuk hitungan hutang belum lunas di laporan Kartu Hutang Per
> Invoice V2, **bukan** sumber selisih pembulatan 0,40/0,70 yang sempat
> ditanyakan (itu murni pembulatan tampilan / efek memo pembulatan, bukan
> invoice yang hilang -- lihat riwayat percakapan sesi 29 Agu 2026).
>
> **Revisi 29 Agu 2026 (v2):** versi pertama dokumen ini salah filter "belum
> dibayar" -- mengecek "pernah dibayar KAPAN SAJA" alih-alih "dibayar SEBELUM
> 31 Juli". Akibatnya 8 invoice Juni 2026 (semuanya baru lunas 19 Agustus
> 2026, JAUH setelah cutoff) ketinggalan dari daftar semula. Sudah ditambahkan
> di bawah.

## Juni 2026 (baru lunas 19 Agustus 2026 -- masih belum dibayar per cutoff 31 Juli)

**Status: sudah dibuatkan memo pembulatan MM2607310054 (8 baris, net -0,70) --
total supplier Agrinusa sekarang persis 2.331.217.813,00, genap & cocok Excel.**

| No. Invoice | Tanggal Konfirmasi | Total | Tgl Realisasi (aktual) |
|---|---|---:|---|
| BYV/06/26/00082 | 19 Jun 2026 | 9.643.519,40 | 19 Agu 2026 |
| BYV/06/26/00108 | 23 Jun 2026 | 13.334.384,40 | 19 Agu 2026 |
| BYV/06/26/00096 | 24 Jun 2026 | 14.952.835,40 | 19 Agu 2026 |
| BYV/06/26/00099 | 24 Jun 2026 | 60.020.811,30 | 19 Agu 2026 |
| BYV/06/26/00102 | 24 Jun 2026 | 9.643.519,40 | 19 Agu 2026 |
| BYV/06/26/00138 | 30 Jun 2026 | 37.498.959,50 | 19 Agu 2026 |
| BYV/06/26/00141 | 30 Jun 2026 | 23.919.958,80 | 19 Agu 2026 |
| BYV/06/26/00145 | 30 Jun 2026 | 5.022.567,50 | 19 Agu 2026 |

## Sampai dengan 31 Juli 2026

**Status: sudah dibuatkan memo pembulatan [MM2607310053](sinkronisasi_memo_lokal_ke_live_31jul2026.md)
(14 baris, net +1,10) -- 14 invoice di bawah ini SUDAH genap rupiah di laporan.**

| No. Invoice | Tanggal | Total (asal, sebelum pembulatan) |
|---|---|---:|
| BYV/07/26/00009 | 02 Jul 2026 | 4.398.108,80 |
| BYV/07/26/00014 | 06 Jul 2026 | 50.398.668,80 |
| BYV/07/26/00041 | 08 Jul 2026 | 9.643.519,40 |
| BYV/07/26/00050 | 14 Jul 2026 | 9.387.413,80 |
| BYV/07/26/00060 | 15 Jul 2026 | 2.081.648,80 |
| BYV/07/26/00061 | 15 Jul 2026 | 52.185.078,80 |
| BYV/07/26/00077 | 16 Jul 2026 | 37.895.349,40 |
| BYV/07/26/00104 | 22 Jul 2026 | 5.022.567,50 |
| BYV/07/26/00107 | 22 Jul 2026 | 5.022.567,50 |
| BYV/07/26/00120 | 23 Jul 2026 | 2.489.622,20 |
| BYV/07/26/00143 | 27 Jul 2026 | 14.041.628,20 |
| BYV/07/26/00132 | 28 Jul 2026 | 5.022.567,50 |
| BYV/07/26/00146 | 30 Jul 2026 | 16.735.339,40 |
| BYV/07/26/00152 | 31 Jul 2026 | 2.081.648,80 |

## Agustus 2026 (di luar cutoff 31 Juli, dicantumkan untuk referensi)

**Status: memo pembulatan DITAHAN dulu per arahan user (29 Agu 2026).**

| No. Invoice | Tanggal | Total |
|---|---|---:|
| BYV/08/26/00006 | 03 Agu 2026 | 9.643.519,40 |
| BYV/08/26/00010 | 03 Agu 2026 | 7.859.295,50 |
| BYV/08/26/00005 | 03 Agu 2026 | 9.643.519,40 |
| BYV/08/26/00019 | 05 Agu 2026 | 14.409.981,30 |
| BYV/08/26/00049 | 11 Agu 2026 | 9.643.519,40 |
| BYV/08/26/00091 | 19 Agu 2026 | 9.387.413,80 |
| BYV/08/26/00089 | 19 Agu 2026 | 29.126.239,40 |
| BYV/08/26/00094 | 20 Agu 2026 | 16.961.252,50 |
| BYV/08/26/00108 | 24 Agu 2026 | 9.643.519,40 |

## Query sumber (v2, benar)

```sql
SELECT kpv.nomor, kpv.tgl_bayar, kpv.total,
    (SELECT MIN(rp.tgl_bayar) FROM realisasi_pembayaran_det rpd
     LEFT JOIN realisasi_pembayaran rp ON rpd.id_header = rp.id
     WHERE rpd.transaksi = 'VOADIP' AND rpd.no_bayar = kpv.nomor) as tgl_realisasi_pertama
FROM konfirmasi_pembayaran_voadip kpv
WHERE kpv.supplier = '19B004'
  AND kpv.total <> FLOOR(kpv.total)
  AND NOT EXISTS (
      SELECT 1 FROM realisasi_pembayaran_det rpd
      LEFT JOIN realisasi_pembayaran rp ON rpd.id_header = rp.id
      WHERE rpd.transaksi = 'VOADIP' AND rpd.no_bayar = kpv.nomor
        AND rp.tgl_bayar <= '2026-07-31'
  )
ORDER BY kpv.tgl_bayar;
```
