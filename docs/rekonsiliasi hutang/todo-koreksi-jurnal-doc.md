---
name: todo-koreksi-jurnal-doc
description: To-do koreksi jurnal/PPh untuk menol-kan sisa selisih DOC (ringkas vs GL) per unit
metadata: 
  node_type: memory
  type: project
  originSessionId: 6cc41ef4-1937-4d82-b821-082f2ba7f666
---

TO-DO koreksi data oleh user (JANGAN diubah oleh AI) untuk menol-kan sisa selisih laporan hutang ringkas DOC vs GL. Konteks lengkap di [[doc-saldo-awal-timing]].

## 🔴 Prioritas — rupiah penuh
1. **BJN +1,00** — BYD/12/25/00106 (BYR/12/25/00150): jurnal transfer 11130.001→21180.200 = 135.055.516, realisasi 135.055.515 (kelebihan 1, tanpa pembulatan). FIX: betulkan transfer → 135.055.515 (atau tambah pembulatan D.96010 K.21180.200 = 1,00).
2. **MLG −1,96** — pembulatan keliru (transfer sudah bulat tapi entri pembulatan tetap dibuat). FIX: hapus/reverse entri pembulatan D.96010 K.21180.200:
   - BYD/09/25/00100 → 0,50
   - BYD/10/25/00070 → 0,50
   - BYD/12/25/00116 → 0,50
   - BYD/12/25/00387 → 0,46

## 🟠 PSR — dasar PPh salah harga
3. **PSR −970** — BYD/10/25/00195: dasar PPh seharusnya harga 7.029 (28.116.000 → PPh 70.290), BUKAN harga 7.126 (28.504.000 → 71.260). Selisih harga 388.000 = koreksi persediaan, tidak kena PPh. GL sudah benar (70.290). FIX: dasar PPh invoice ini = 28.116.000 (harga 7029), bukan konfirmasi 28.504.000.

## 🟡 Recehan < Rp1 (opsional — hati-hati lingkaran pembulatan)
4. **MGT +0,73** — 2 invoice:
   - BYD/11/25/00015 (+0,27) = sisa koreksi 498,48 (jurnal transfer 71.082.348,75 vs realisasi 71.081.850; pembulatan memo MM2603310128 = 498,48; sisa 0,27). Terkait [[pending-jurnal-memorial-doc-mgt]].
   - BYD/12/25/00392 (+0,46) = jurnal transfer 80.933.160,46 vs realisasi 80.933.160 (tanpa pembulatan).
5. **TAG +1,00** — BYD/12/25/00139 (+0,35, pembulatan jurnal 0,15) + memo MM2603310046 (+0,65, PEMBULATAN manual tanpa invoice).
6. **MDN +0,05** — BYD/04/26/00129 (pembulatan jurnal 0,45 / memo MM2604300030).

## ✅ Selesai
PRB (tidak ada issue) · BWI · GSK · KDR · JBR · LMG · LMJ · SLM (sudah 0).

CATATAN: pola umum = jurnal transfer kelebihan / pembulatan tidak pas / dasar PPh — bukan masalah nilai konfirmasi pokok. Setelah dibenahi, minta AI verifikasi ulang.
