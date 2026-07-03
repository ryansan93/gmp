# Modul & Controller — GM ERP

## Daftar Modul

| Modul | Path | Jumlah Controller | Fungsi |
|---|---|---|---|
| accounting | `application/modules/accounting/controllers/` | 25 | Jurnal, kas, bank, memorial, tutup bulan |
| api | `application/modules/api/controllers/` | 5 | REST API untuk mobile |
| bantuan | `application/modules/bantuan/controllers/` | 4 | Jurnal bantuan multi-dokumen |
| base | `application/modules/base/controllers/` | 5 | Shared utility (InsertJurnal, ExportExcel) |
| home | `application/modules/home/controllers/` | — | Dashboard |
| hris | `application/modules/hris/controllers/` | 1 | KPI karyawan |
| import | `application/modules/import/controllers/` | 8 | Import order & penerimaan |
| marketing | `application/modules/marketing/controllers/` | 1 | Daftar kunjungan |
| master | `application/modules/master/controllers/` | 5 | User, group, fitur |
| parameter | `application/modules/parameter/controllers/` | 30 | Master data bisnis |
| pembayaran | `application/modules/pembayaran/controllers/` | 18 | Pembayaran & konfirmasi |
| report | `application/modules/report/controllers/` | 61 | Seluruh laporan |
| transaksi | `application/modules/transaksi/controllers/` | 69 | Transaksi inti bisnis |
| user | `application/modules/user/controllers/` | — | Login, profil |

---

## Modul: accounting

| Controller | Fungsi |
|---|---|
| BankKeluar.php | Transaksi pengeluaran bank |
| BankMasuk.php | Transaksi penerimaan bank |
| ChartOfAccount.php | Master Chart of Account (COA) |
| ClosingHarianBank.php | Penutupan harian rekening bank |
| FiturSettingAccount.php | Setting akun per fitur |
| HitungUlang.php | Hitung ulang saldo akuntansi |
| ItemReport.php | Master item laporan keuangan |
| Jurnal.php | Jurnal umum |
| JurnalPusat.php | Jurnal pusat/kantor pusat |
| JurnalUnit.php | Jurnal per unit/kandang |
| KasKeluar.php | Transaksi pengeluaran kas |
| KasKeluarInternal.php | Kas keluar internal |
| KasMasuk.php | Transaksi penerimaan kas |
| KasMasukInternal.php | Kas masuk internal |
| MappingJurnal.php | Mapping jurnal otomatis |
| Memorial.php | Jurnal memorial |
| PostingUlang.php | Posting ulang jurnal |
| SaldoAwalCoa.php | Saldo awal per COA |
| SaldoBank.php | Posisi saldo bank |
| SaldoHarian.php | Laporan saldo harian |
| SettingReport.php | Setting group laporan keuangan |
| SewaKantor.php | Biaya sewa kantor |
| SumberTujuanJurnal.php | Master sumber/tujuan jurnal |
| TransaksiJurnal.php | Transaksi jurnal detail |
| TutupBulan.php | Proses tutup bulan akuntansi |

---

## Modul: api

| Controller | Fungsi |
|---|---|
| Mobile.php | API umum mobile (get user, dll) |
| MobileMitra.php | API data mitra untuk mobile |
| MobileRhk.php | API Rencana Harian Kandang |
| Mus.php | API MUS (multi-unit sync) |
| Mus_old.php | Versi lama MUS API |

---

## Modul: bantuan

| Controller | Fungsi |
|---|---|
| LhkJurnal.php | Generate jurnal dari LHK |
| TerimaDocJurnal.php | Generate jurnal dari penerimaan DOC |
| TerimaOvkJurnal.php | Generate jurnal dari penerimaan OVK |
| TerimaPakanJurnal.php | Generate jurnal dari penerimaan pakan |

---

## Modul: base (shared)

| Controller | Fungsi |
|---|---|
| Event.php | Event handler CI |
| ExecStoredProcedure.php | Eksekusi stored procedure |
| ExportExcel.php | Shared export ke Excel (PhpSpreadsheet) |
| InsertJurnal.php | Insert jurnal otomatis via `setting_automatic_jurnal` |
| TutupBulan.php | Shared logic tutup bulan |

---

## Modul: parameter

| Controller | Fungsi |
|---|---|
| BiayaOperasional.php | Master biaya operasional |
| Doc.php | Master DOC (bibit ayam) |
| Ekspedisi.php | Master ekspedisi/pengiriman |
| FDVP.php | Master FDVP |
| Feed.php | Master pakan |
| Gaji.php | Master komponen gaji |
| Gudang.php | Master gudang |
| Jenis.php | Master jenis barang |
| Kendaraan.php | Master kendaraan |
| MasterKBD.php | Master KBD (Kandang Budidaya) |
| Nekropsi.php | Master penyakit/nekropsi |
| OngkosAngkut.php | Master tarif ongkos angkut |
| Pegawai.php | Master pegawai/karyawan |
| Pelanggan.php | Master pelanggan (bakul) |
| PemakaianPakan.php | Standar pemakaian pakan |
| PeriodeFiskal.php | Periode fiskal/akuntansi |
| Peternak.php | Master peternak (mitra) |
| PeternakPosisi.php | Posisi peternak |
| PKW.php | Master PKW |
| PotonganPajak.php | Master potongan pajak |
| PotonganPelanggan.php | Master potongan pelanggan |
| SettingAutomaticJurnal.php | Konfigurasi jurnal otomatis |
| SkpMitra.php | SKP mitra peternak |
| Solusi.php | Master solusi/rekomendasi kandang |
| StandarBudidaya.php | Standar budidaya per periode |
| Supplier.php | Master supplier |
| Vaksin.php | Master vaksin/obat |

---

## Modul: pembayaran

| Controller | Fungsi |
|---|---|
| Bakul.php | Pembayaran ke bakul/pelanggan |
| BakulBadDebt.php | Bad debt pelanggan |
| BakulMobile.php | Versi mobile pembayaran bakul |
| KonfirmasiPembayaranDoc.php | Konfirmasi bayar DOC |
| KonfirmasiPembayaranOaPakan.php | Konfirmasi bayar OA pakan |
| KonfirmasiPembayaranPakan.php | Konfirmasi bayar pakan |
| KonfirmasiPembayaranPeternak.php | Konfirmasi bayar peternak |
| KonfirmasiPembayaranVoadip.php | Konfirmasi bayar voadip |
| KurangLebihPembayaran.php | Selisih kurang/lebih bayar |
| PembayaranPeralatan.php | Pembayaran peralatan |
| PembayaranPiutangKaryawan.php | Pembayaran piutang karyawan |
| PembayaranPiutangMitra.php | Pembayaran piutang mitra |
| RealisasiPembayaran.php | Realisasi pembayaran |
| RekeningMasuk.php | Rekening masuk/penerimaan |
| RekeningTampungan.php | Rekening penampungan |
| VerifikasiPembayaran.php | Verifikasi pembayaran |

---

## Modul: report (61 controller)

| Controller | Laporan |
|---|---|
| Bank.php | Buku bank |
| BankStart.php | Saldo awal bank |
| BukuBankPajak.php | Buku bank pajak |
| ChickInMingguan.php | Chick-in mingguan |
| DistribusiBarang.php | Distribusi barang |
| GeneralLedger.php | General ledger |
| GeneralLedgerExternal.php | GL eksternal |
| GeneralLedgerInternal.php | GL internal |
| GeneralLedgerLengkap.php | GL lengkap |
| HutangUsaha.php | Hutang usaha |
| KartuHutangLengkap.php | Kartu hutang lengkap |
| KartuHutangPerInvoice.php | Kartu hutang per invoice |
| KartuHutangRingkas.php | Kartu hutang ringkas |
| KartuPiutangLengkap.php | Kartu piutang lengkap |
| KartuPiutangPerInvoice.php | Kartu piutang per invoice |
| KartuPiutangRingkas.php | Kartu piutang ringkas |
| KartuStok.php | Kartu stok |
| KartuStokRingkas.php | Kartu stok ringkas |
| KartuStokSiklus.php | Kartu stok per siklus |
| KasKecil.php | Buku kas kecil |
| KasStart.php | Saldo awal kas |
| KertasKerjaHpp.php | Kertas kerja HPP |
| LabaRugi.php | Laporan laba rugi |
| LabaRugiSummaryBulanan.php | Laba rugi summary bulanan |
| LaporanHarianManajemen.php | Laporan harian manajemen |
| LaporanPenjualanAyam.php | Laporan penjualan ayam |
| LaporanRhpp.php | Laporan RHPP |
| LHK.php | Laporan Harian Kandang |
| MutasiStok.php | Mutasi stok |
| Neraca.php | Neraca/balance sheet |
| PembayaranBakul.php | Laporan pembayaran bakul |
| PembayaranPlasma.php | Laporan pembayaran plasma |
| Pembelian.php | Laporan pembelian |
| PindahBarang.php | Laporan pindah barang |
| PiutangKaryawan.php | Piutang karyawan |
| PiutangMitra.php | Piutang mitra |
| PosisiStok.php | Posisi stok |
| PosisiStokAccounting.php | Posisi stok (sisi akuntansi) |
| PosisiStokSiklus.php | Posisi stok per siklus |
| RealisasiChickIn.php | Realisasi chick-in |
| RekapDataRhpp.php | Rekap data RHPP |
| RekapLebihBayar.php | Rekap lebih bayar |
| RekapPotonganPajak.php | Rekap potongan pajak |
| RiwayatPerformancePlasma.php | Riwayat performa plasma |
| RPAH.php | Laporan RPAH |
| SisaSaldoBakulPerTanggal.php | Sisa saldo bakul |
| SisaStokAyam.php | Sisa stok ayam |
| SisaStokAyamMinMax.php | Sisa stok ayam min-max |
| SisaStokBelumTutupSiklus.php | Stok belum tutup siklus |
| SisaTagihanBakulPerTanggal.php | Sisa tagihan bakul |
| SisaTagihanPerPelanggan.php | Sisa tagihan per pelanggan |
| UmurKartuHutang.php | Umur hutang (aging) |
| UmurKartuPiutang.php | Umur piutang (aging) |

---

## Modul: transaksi (69 controller)

| Controller | Fungsi |
|---|---|
| **DOC / Bibit** | |
| ODVP.php | Order/distribusi voadip (multi-fungsi) |
| PenerimaanDocMobile.php | Penerimaan DOC via mobile |
| Rdim.php | RDIM (Rencana Distribusi dan Informasi Mitra) |
| Rdim_2_tanggal.php | RDIM dengan 2 tanggal |
| **Pakan** | |
| PenerimaanPakan.php | Penerimaan pakan dari supplier |
| PenerimaanPakanMobile.php | Penerimaan pakan via mobile |
| PengirimanPakan.php | Pengiriman pakan ke kandang |
| PengirimanPenerimaanPakan.php | Pengiriman & penerimaan pakan gabungan |
| ReturPakan.php | Retur pakan |
| SPM.php | Surat Permintaan Material (pakan) |
| UpdateHargaPakan.php | Update harga pakan |
| **Obat/Vaksin (Voadip)** | |
| PenerimaanVoadip.php | Penerimaan voadip dari supplier |
| PenerimaanVoadipMobile.php | Penerimaan voadip via mobile |
| PengirimanVoadip.php | Pengiriman voadip ke kandang |
| PengirimanPenerimaanOvk.php | Pengiriman & penerimaan OVK |
| ReturVoadip.php | Retur voadip |
| DistribusiVoadip.php | Distribusi voadip ke mitra |
| **Kandang** | |
| LHK.php | Laporan Harian Kandang (full) |
| LHK_WITH_PAKAN.php | LHK dengan data pakan |
| LHK_without_peralatan.php | LHK tanpa peralatan |
| HarianKandang.php | Data harian kandang |
| **Panen** | |
| KonfirmasiPanen.php | Konfirmasi panen |
| KonfirmasiPanenMobile.php | Konfirmasi panen mobile |
| RealisasiSJ.php | Realisasi surat jalan panen |
| RealisasiSjMobile.php | Realisasi SJ via mobile |
| RPAH.php | Rekap Perhitungan Akhir Hasil (panen) |
| RpahMobile.php | RPAH via mobile |
| ApprovalRpah.php | Approval RPAH |
| BASTTB.php | Berita Acara Serah Terima Ternak Broiler |
| **Keuangan & Hutang/Piutang** | |
| KreditBank.php | Kredit bank |
| KreditKendaraan.php | Kredit kendaraan |
| GajiKaryawan.php | Penggajian karyawan |
| PiutangKaryawan.php | Piutang karyawan |
| PiutangMitra.php | Piutang mitra |
| SisaBayarPelanggan.php | Sisa bayar pelanggan |
| BayarPenjualanPeralatan.php | Bayar penjualan peralatan |
| PenjualanPeralatan.php | Penjualan peralatan |
| CreditNote.php / CreditNotePosting.php | Credit note & posting |
| DebitNote.php / DebitNotePosting.php | Debit note & posting |
| CnPembelian.php / CnPenjualan.php | CN pembelian/penjualan |
| DnPembelian.php / DnPenjualan.php | DN pembelian/penjualan |
| CnVoadip.php / DnVoadip.php | CN/DN voadip |
| Posting.php | Posting transaksi |
| **Stok & Adjustment** | |
| HitungStok.php | Hitung stok (trigger stored procedure) |
| AdjustmentInDoc.php | Adjustment in DOC |
| AdjustmentInPakan.php | Adjustment in pakan |
| AdjustmentInVoadip.php | Adjustment in voadip |
| AdjustmentOutPakan.php | Adjustment out pakan |
| AdjustmentOutVoadip.php | Adjustment out voadip |
| PindahBudidaya.php | Pindah budidaya antar kandang |
| TSDRHPP.php | Transfer saldo dari RHPP |
| RhppGroup.php | RHPP group |
| **Lain-lain** | |
| EstimasiChickInMingguan.php | Estimasi chick-in mingguan |
| OngkosAngkutPindahPakan.php | Ongkos angkut pindah pakan |
| KPM.php | KPM (Kontrak Pembelian Mitra) |
| PurchaseRequest.php | Purchase request |
| OrderPeralatan.php | Order peralatan |
| PenerimaanPeralatan.php | Penerimaan peralatan |

---

## Modul: import

| Controller | Fungsi |
|---|---|
| KirimPakan.php | Kirim pakan (import) |
| KirimVoadip.php | Kirim voadip (import) |
| OrderDoc.php | Order DOC (import) |
| OrderPakan.php | Order pakan (import) |
| OrderVoadip.php | Order voadip (import) |
| RDIM.php | RDIM (import) |
| TerimaDoc.php | Terima DOC (import) |
| TerimaPakan.php | Terima pakan (import) |
| TerimaVoadip.php | Terima voadip (import) |
