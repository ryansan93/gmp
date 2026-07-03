---
name: todo-rekonsiliasi-hutang
description: "To-do GABUNGAN sisa koreksi rekonsiliasi hutang (DOC, PAKAN, OVK EXTERN) ringkas vs GL"
metadata: 
  node_type: memory
  type: project
  originSessionId: a2891ba5-b2f7-4760-a0e2-cf97c971078d
---

To-do gabungan koreksi data oleh user (AI READ-ONLY DB, hanya edit controller). Detail/metodologi: [[doc-saldo-awal-timing]] [[hutang-pakan-rekonsiliasi]] [[todo-koreksi-jurnal-doc]]. CATATAN: cek AI pakai saldo_bulanan (snapshot 12 Juni) — fix baru bisa tampil selisih semu; verifikasi final via laporan live user.

## DOC (sisa kecil — pembulatan/PPh/jurnal)
- **BJN +1,00** — BYD/12/25/00106: jurnal transfer 11130.001->21180.200 = 135.055.516, betulkan ke 135.055.515 (realisasi benar).
- **MLG -1,96** — hapus entri pembulatan keliru D.96010 K.21180.200 (transfer sudah bulat tapi pembulatan tetap dibuat): BYD/09/25/00100 (0,50), 00070 (0,50), 00116 (0,50), 00387 (0,46).
- **PSR -970** — BYD/10/25/00195: dasar PPh seharusnya harga 7.029 (28.116.000 -> PPh 70.290), bukan 7.126. Selisih harga 388.000 = koreksi persediaan, tak kena PPh.
- **MGT +0,73** — BYD/11/25/00015 (+0,27 sisa koreksi 498,48 jurnal transfer 71.082.348,75 vs realisasi 71.081.850) + BYD/12/25/00392 (+0,46 jurnal transfer 80.933.160,46 vs 80.933.160).
- **TAG +1,00** — BYD/12/25/00139 (0,35 pembulatan jurnal) + memo MM2603310046 (0,65 PEMBULATAN manual).
- **MDN +0,05** — BYD/04/26/00129 (pembulatan jurnal 0,45 / memo MM2604300030).
- (Sisa DOC total < Rp1000 = pembulatan desimal PPh, boleh diterima reconciled.)

## PAKAN — TUNTAS 100% (semua 15 unit 0). Tidak ada sisa.

## OVK EXTERN (COA 21174.000) — praktis tuntas
- **JBR +0,48** — SJ 002817: pembulatan sen.
- **MJK +0,20** — SJ 047689 (BBM=total 2 konfir BYV/10/25/00107+00043, balance): pembulatan sen.
- **BWI** — verifikasi laporan LIVE (cek AI tampil +2,5M tapi SEMU krn saldo_bulanan belum muat memo split MM2604270001; live harusnya 0).
- JBR & MJK keduanya < Rp1, boleh diterima reconciled.

## Fix controller yang sudah terpasang (LaporanHutangRingkas.php) — JANGAN di-revert
1. Saldo awal DOC debet: basis kpd.tgl_bayar (bukan td.datang).
2. inv_mm INVOICE LEWAT MEMO: kontrol reklasifikasi (coa_tujuan IS NULL OR NOT IN akun hutang).
3. byr_mm BAYAR LEWAT MEMO: kontrol CN (skip coa_asal=71105.003 yg sudah di cn_post) + kontrol reklasifikasi (coa_asal IS NULL OR NOT IN akun hutang).
4. PPh pembayaran lewat memo (union mm di konfir): hitung pph = 0,25% nilai DOC memo (bukan 0).

## BELUM dikerjakan
- OVK biasa (21180.300) — belum direkonsiliasi (sebagian terbantu fix BWI).
- RHPP, OA PAKAN — belum.
