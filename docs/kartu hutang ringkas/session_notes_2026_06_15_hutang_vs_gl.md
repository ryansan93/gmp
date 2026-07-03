# Catatan Sesi — Rekonsiliasi Hutang vs GL (DOC) — 2026-06-15

## Tujuan
Membandingkan **total hutang** (dari konfirmasi pembayaran) vs **nilai terjurnal**
di `det_jurnal` (akun hutang), per invoice, dan memastikan selisih DOC tahun 2025
sudah dibereskan lewat memorial.

## File yang dihasilkan / diubah (folder `docs/kartu hutang ringkas/`)
1. **`hutang_per_invoice.sql`** — hutang bruto per invoice (DOC/PAKAN/OVK/OA/RHPP/PERALATAN).
2. **`hutang_vs_jurnal.sql`** — UTAMA & LENGKAP. Kolom:
   `tanggal | no_invoice | supplier | jenis_hutang | total_hutang | jurnal_hutang | selisih`
   `tanggal | no_invoice | supplier | jenis_hutang | total_hutang | jurnal_hutang`
   `| gl_memorial_hutang | selisih | bayar_transfer | bayar_cn | bayar_pph | total_bayar | sisa_hutang`
   `| gl_realisasi | gl_memorial | gl_turun | diff_jurnal_gl_turun`
   `| selisih_realisasi | selisih_cn | selisih_bayar_gl`.
   CATATAN: jurnal_hutang kini MENCERMINKAN GL (termasuk band-aid pembulatan MM 96010).
   gl_memorial_hutang = porsi band-aid (mis. BYD/11/25/00015 = 498,48); selisih jadi -498,48
   (menandai band-aid); diff_jurnal_gl_turun = jurnal_hutang - gl_turun = -0,27 (saldo GL riil).
3. **`doc_terima_belum_dijurnal.sql`** — audit terima_doc yg belum/kurang dijurnal naik.
4. **`doc_hutang_vs_bayar.sql`** — versi DOC-only utk detail bayar vs GL turun.
5. **`PENDING_jurnal_memorial_selisih_doc.md`** — pengingat jurnal memorial.

## UPDATE (lanjutan sesi): PEMBAYARAN + GL TURUN digabung ke hutang_vs_jurnal.sql
PEMBAYARAN (realisasi_pembayaran_det + cn_post_det):
- bayar_transfer = SUM(realisasi_pembayaran_det.transfer).
- bayar_cn = SUM(cn_post_det.pakai).
- bayar_pph = DOC 0,25% berbasis tanggal (net CN utk >=2026-01-01; 0 utk <=2025-09-20);
  jenis lain = pph tersimpan. total_bayar = transfer+cn+pph. sisa_hutang = total-total_bayar.
- VALIDASI: total_hutang = transfer + CN + PPh (3 invoice uji sisa=0).

PERBANDINGAN BAYAR vs GL (det_jurnal turun 21180.200, DOC-only):
- gl_realisasi = GL turun via BYR (cocok kolom invoice=BYD; no_sj hanya bila invoice kosong).
- gl_memorial  = GL turun via MM (CN). gl_turun = gl_realisasi + gl_memorial.
- selisih_realisasi = (transfer+pph) - gl_realisasi:
    sub-rupiah/ratusan (mis. -0,50 / -498,75) = pembulatan transfer (error GL);
    BESAR (mis. -185jt) = leg bayar SALAH LABEL ke invoice ini (net antar pasangan = 0).
- selisih_cn = bayar_cn - gl_memorial -> umumnya = CN (GL net CN saat booking, bukan error).

FIX dobel-booking (penting): `glbayar` MENGECUALIKAN memorial reversal-booking
(counter persediaan 12040 / reclass hutang) dari sisi pembayaran -- itu milik sisi
JURNAL (jdoc_mm). Tanpa ini, gl_turun dobel. Contoh: BYD/09/25/00109 punya 2 versi
terima_doc (id 150 & 208) -> 2 BBM naik (det_jurnal id 3881 & 64118 = 122,25jt),
direversal MM2510310017 (counter 12040). jurnal_hutang benar 61,13jt; setelah fix
gl_turun=61,13jt, semua selisih=0.

KEPUTUSAN FINAL band-aid pembulatan (MM NAIK 21180.200 counter 96010):
- jurnal_hutang MENCERMINKAN GL -> band-aid DIMASUKKAN ke jurnal_hutang (via CTE
  jdoc_pembulatan yg diunion ke `jurnal`). Ditampilkan terpisah di kolom
  `gl_memorial_hutang`.
- Akibatnya `selisih` (total - jurnal) = -498,48 utk BYD/11/25/00015 -> MENANDAI bahwa
  GL membukukan 498,48 lebih dari konfirmasi (band-aid). Itu informatif, bukan bug.
- diff_jurnal_gl_turun = jurnal_hutang - gl_turun (sederhana) = -0,27 (saldo GL riil;
  transfer over 498,75 - band-aid 498,48). selisih_realisasi tetap -498,75 (detail transfer).
diff != 0 = saldo invoice di GL belum nol (belum lunas / error bayar / sisa pembulatan).

BUCKET CLEARING per (unit, no_mm) -- agar SUM(diff) DOC = saldo GL akun 21180.200 PERSIS:
- CTE attrkey (BYD/no_bbm/no_sj/terima_doc.id) + gl_clearing_unit (entri 21180.200 yg
  NOT EXISTS attrkey, di-net per unit & ref). Di-UNION sbg baris no_invoice = no_mm/invoice.
- Menangkap KOREKSI UTANG BREEDING via 27001 zero-balance tanpa invoice: PSR MM2512310007
  (1.300) + GSK MM2512310009 (1.015) = -2.315 (inilah gap query vs GL sebelumnya).
- Bonus terlihat: CN PAKAN salah-posting ke akun DOC (MM2602280004/BYP/10/25/00065 PSR 32,7jt;
  MM2602280005/BYP/09/25/00046 MLG 5,3jt) -> net 0, idealnya pindah ke akun hutang pakan.
- 1.300 (MM2512310007) = Dr Hutang DOC PSR / Cr 27001 zero-balance; BUKAN selisih invoice
  (6 no_order andek ODC/PSR/25/10004,10005,12009,12010,26/02008,02009 semua exact 0).

FILTER: DECLARE @jenis varchar(20)='DOC' di atas query (set NULL utk semua jenis).
Kolom unit ditambahkan setelah tanggal; gl_memorial_hutang sebelum jurnal_hutang.

## Cara menyambung jurnal hutang per jenis (di hutang_vs_jurnal.sql)
Jurnal "naik hutang" semuanya prefix **BBM**, kolom `invoice` KOSONG → disambung
via `tbl_name` + `tbl_id`:
- **DOC**  : 21180.200(+21173 extern) via `terima_doc` (tbl_id)
- **PAKAN**: 21180.100(+21172) via `terima_pakan` (tbl_id) — *indikatif, no_sj many-to-many*
- **OVK**  : 21180.300(+21174) via `terima_voadip` (tbl_id)
- **EXPEDISI**: 21212.000 via `terima_pakan`+`oa_pindah_pakan` (no_sj)
- **MITRA**: 21213.000/.001 via `tbl_name=rhpp/rhpp_group`, `kode_trans=invoice`
- **LAIN-LAIN**: 21201/21211/21299 via memo (mm), `kode_trans=no_mm`

## Logika koreksi MEMORIAL untuk DOC (jdoc_mm) — hasil diskusi
Memorial DOC dicocokkan ke BYD via **BYD / no_bbm / no_sj** (PENTING: memorial
sering mengisi kolom `invoice` dengan **no_bbm**, bukan nomor BYD — inilah sebabnya
banyak invoice 2025 tampak "belum dijurnal" padahal sudah dikoreksi).

Aturan **asimetris**:
- **NAIK** (coa_asal=21180.200): disertakan, KECUALI counter **96010 (pembulatan)**.
  → pembulatan band-aid (mis. MM2603310128) bukan hutang riil, tapi plug sisi bayar.
- **TURUN** (coa_tujuan=21180.200): HANYA reversal booking (counter persediaan 12040
  / reclass hutang). CN (71105.003), income (96040/96010), clearing (27001) DIKECUALIKAN
  (itu pengurang seperti pembayaran, bukan pembukuan hutang).
- 1 baris memorial → 1 BYD (`MIN(no_invoice)` per `dj.id`) supaya tak dobel hitung.

## Kesimpulan DOC (final)
- **Seluruh selisih DOC tahun 2025 SUDAH dibereskan via memorial.** Pola koreksi:
  - BBM dobel → memorial TURUN (mis. MM2512310049 Edi Santoso, MM2512310050 Lisawati).
  - BBM tak ada → memorial NAIK (mis. MM2512310051 Samsul Huda, MM2512310054 Luky,
    MM2512310053 BJN — invoice diisi no_bbm).
  - Kekurangan box → memorial NAIK (MM2511300016 "KEKURANGAN UTANG DOC - 4 BOX").
  - Selisih persediaan → memorial TURUN (MM2601310039 PSR).
- **Saldo GL DOC per unit sudah BENAR** (terbukti: JBR GL saldo 62.629.600 = KHR).
  Yang "kurang naik" ternyata sepasang dgn turun yg juga kurang / sudah dimemorial.
- **Sisa selisih DOC ~2,70B SELURUHNYA di Juni-2026** = receipt baru/pipeline,
  wajar belum dijurnal (bukan error).

## Item OPEN (perlu tindak)
- **BYD/11/25/00015 (MGT)** — sisa **0,27** di sisi saldo/bayar (transfer kelebihan
  498,75, band-aid MM2603310128 menutup 498,48). **User akan jurnal memorial Juni 2026.**
  Detail di `PENDING_jurnal_memorial_selisih_doc.md`.

## Jenis lain (belum dirapikan — untuk lain waktu)
- PAKAN: jurnal over-count ~14B (no_sj kirim_pakan many-to-many) → perlu kunci unik.
- EXPEDISI: +1,576B sebagian ongkos belum dijurnal.
- MITRA: −241jt (cek kemungkinan dobel posting rhpp).
- PERALATAN: belum dipetakan ke jurnal.
- MJK DOC: reconciling item +109jt (saldo) — sudah dianalisa, belum ditindak.

## Reconciliation tools terkait (sudah ada)
- `rekonsiliasi_saldo_GL_vs_KHR_per_unit_DOC.sql` — saldo per unit (sumber kebenaran).
- `rekonsiliasi_transfer_GL_vs_realisasi_DOC.sql` — deteksi salah posting transfer.
- `khr_breakdown_per_invoice_DOC.sql` — breakdown KHR per invoice.
