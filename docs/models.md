# Eloquent Models — GM ERP

Semua model berada di `application/modules/storage/models/` dengan namespace `Model\Storage`.  
Base class: `Conf` (extends `Illuminate\Database\Eloquent\Model`).

Total model: **263+**

---

## Base Models

| File | Keterangan |
|---|---|
| `Conf.php` | Base model default (koneksi `default` / `gmp_erp_live`) |
| `log/ConfLog.php` | Base model untuk DB log (`log_history_gmp_erp_live`) |
| `mgberp/ConfMgb.php` | Base model untuk DB mgb (`mgb_erp_live`) |
| `mgberp/Mitra_model.php` | Model mitra di DB mgb |
| `log/LogTables_model.php` | Log perubahan tabel |

---

## Model Utama per Domain

### DOC & Bibit

| Model | Tabel |
|---|---|
| `Doc_model` | `doc` |
| `OrderDoc_model` | `order_doc` |
| `TerimaDoc_model` | `terima_doc` |
| `TerimaDocKet_model` | `terima_doc_ket` |
| `RealDocin_model` | `real_docin` |
| `Rdim_model` | `rdim` |
| `RdimSubmit_model` | `rdim_submit` |

### Pakan

| Model | Tabel |
|---|---|
| `Pakan_model` | `pakan` |
| `KirimPakan_model` | `kirim_pakan` |
| `KirimPakanDetail_model` | `kirim_pakan_detail` |
| `TerimaPakan_model` | `terima_pakan` |
| `TerimaPakanDetail_model` | `terima_pakan_detail` |
| `ReturPakan_model` | `retur_pakan` |
| `DetReturPakan_model` | `det_retur_pakan` |
| `OrderPakan_model` | `order_pakan` |
| `OrderPakanDetail_model` | `order_pakan_detail` |
| `PakanSPM_model` | `pakan_spm` |
| `DetPakanSPM_model` | `det_pakan_spm` |
| `PakanTerima_model` | `pakan_terima` |
| `PakanRcnKirim_model` | `pakan_rcn_kirim` |
| `PakanDetRcnKirim_model` | `pakan_det_rcn_kirim` |
| `OaPindahPakan_model` | `oa_pindah_pakan` |
| `OaItem_model` | `oa_item` |
| `KartuPakan_model` | `kartu_pakan` |
| `KartuPakanDetail_model` | `kartu_pakan_detail` |
| `SelisihPakan_model` | `selisih_pakan` |
| `StandarPakan_model` | `standar_pakan` |

### Obat/Vaksin (Voadip/OVK)

| Model | Tabel |
|---|---|
| `Vaksin_model` | `vaksin` |
| `KirimVoadip_model` | `kirim_voadip` |
| `KirimVoadipDetail_model` | `kirim_voadip_detail` |
| `TerimaVoadip_model` | `terima_voadip` |
| `TerimaVoadipDetail_model` | `terima_voadip_detail` |
| `ReturVoadip_model` | `retur_voadip` |
| `DetReturVoadip_model` | `det_retur_voadip` |
| `OrderVoadip_model` | `order_voadip` |
| `OrderVoadipDetail_model` | `order_voadip_detail` |
| `DisVoadip_model` | `dis_voadip` |
| `DisVoadipDetail_model` | `dis_voadip_detail` |

### LHK (Laporan Harian Kandang)

| Model | Tabel |
|---|---|
| `Lhk_model` | `lhk` |
| `LhkSekat_model` | `lhk_sekat` |
| `LhkPakan_model` | `lhk_pakan` |
| `LhkPeralatan_model` | `lhk_peralatan` |
| `LhkSolusi_model` | `lhk_solusi` |
| `LhkNekropsi_model` | `lhk_nekropsi` |
| `LhkFotoEkorMati_model` | `lhk_foto_ekor_mati` |
| `LhkFotoNekropsi_model` | `lhk_foto_nekropsi` |
| `LhkFotoSisaPakan_model` | `lhk_foto_sisa_pakan` |
| `HarianKandang_model` | `harian_kandang` |
| `HarianKandangBb_model` | `harian_kandang_bb` |

### Panen & RPAH

| Model | Tabel |
|---|---|
| `Konfir_model` | `konfir` (konfirmasi panen) |
| `DetKonfir_model` | `det_konfir` |
| `RealSJ_model` | `real_sj` |
| `DetRealSJ_model` | `det_real_sj` |
| `DetRealSjInv_model` | `det_real_sj_inv` |
| `Rpah_model` | `rpah` |
| `DetRpah_model` | `det_rpah` |
| `DetTimPanen_model` | `det_tim_panen` |
| `MsTimPanen_model` | `ms_tim_panen` |
| `JabatanTimPanen_model` | `jabatan_tim_panen` |

### RHPP (Rekap Hasil Panen Plasma)

| Model | Tabel |
|---|---|
| `Rhpp_model` | `rhpp` |
| `RhppDoc_model` | `rhpp_doc` |
| `RhppPakan_model` | `rhpp_pakan` |
| `RhppVoadip_model` | `rhpp_voadip` |
| `RhppPenjualan_model` | `rhpp_penjualan` |
| `RhppPiutang_model` | `rhpp_piutang` |
| `RhppPotongan_model` | `rhpp_potongan` |
| `RhppBonus_model` | `rhpp_bonus` |
| `RhppGroup_model` | `rhpp_group` |
| `RhppGroupHeader_model` | `rhpp_group_header` |
| `RhppGroupDoc_model` | `rhpp_group_doc` |
| `RhppGroupPakan_model` | `rhpp_group_pakan` |
| `RhppGroupVoadip_model` | `rhpp_group_voadip` |
| `RhppGroupPenjualan_model` | `rhpp_group_penjualan` |
| `RhppGroupPiutang_model` | `rhpp_group_piutang` |
| `RhppGroupPotongan_model` | `rhpp_group_potongan` |
| `RhppGroupBonus_model` | `rhpp_group_bonus` |
| `RhppGroupNoreg_model` | `rhpp_group_noreg` |

### Akuntansi & Jurnal

| Model | Tabel |
|---|---|
| `Coa_model` | `coa` (chart of account) |
| `SaCoa_model` | `sa_coa` (saldo awal COA) |
| `Jurnal_model` | `jurnal` |
| `JurnalTrans_model` | `jurnal_trans` |
| `JurnalReport_model` | `jurnal_report` |
| `JurnalMapping_model` | `jurnal_mapping` |
| `JurnalTransFitur_model` | `jurnal_trans_fitur` |
| `JurnalTransSumberTujuan_model` | `jurnal_trans_sumber_tujuan` |
| `DetJurnal_model` | `det_jurnal` |
| `DetJurnalTrans_model` | `det_jurnal_trans` |
| `SettingAutomaticJurnal_model` | `setting_automatic_jurnal` |
| `SettingAutomaticJurnalDet_model` | `setting_automatic_jurnal_det` |
| `SaldoHarian_model` | `saldo_harian` |
| `SaldoHarianDet_model` | `saldo_harian_det` |
| `SaldoHarianDetHutang_model` | `saldo_harian_det_hutang` |
| `SaldoBulanan_model` | `saldo_bulanan` |
| `SaldoBank_model` | `saldo_bank` |
| `SaldoKas_model` | `saldo_kas` |
| `Kk_model` / `KkItem_model` | Kas keluar |
| `Km_model` / `KmItem_model` | Kas masuk |
| `Mm_model` / `MmItem_model` | Memorial |
| `NoBbk_model` | Nomor bukti kas keluar |
| `NoBbm_model` | Nomor bukti kas masuk |
| `NoBukti_model` | Nomor bukti umum |
| `AkunRK_model` | Akun rekening koran |

### Credit Note & Debit Note

| Model | Tabel |
|---|---|
| `Cn_model` / `CnDet_model` | credit_note |
| `CnPost_model` / `CnPostDet_model` | posting CN |
| `CnDetJurnalTrans_model` | jurnal trans dari CN |
| `Dn_model` / `DnDet_model` | debit_note |
| `DnPost_model` / `DnPostDet_model` | posting DN |
| `DnDetJurnalTrans_model` | jurnal trans dari DN |

### Pembayaran & Piutang

| Model | Tabel |
|---|---|
| `PembayaranPelanggan_model` | `pembayaran_pelanggan` |
| `DetPembayaranPelanggan_model` | `det_pembayaran_pelanggan` |
| `PembayaranPelangganCn_model` | pembayaran + CN |
| `PembayaranPelangganDn_model` | pembayaran + DN |
| `PembayaranPelangganSaldo_model` | saldo pembayaran |
| `RealisasiPembayaran_model` | `realisasi_pembayaran` |
| `RealisasiPembayaranDet_model` | detail realisasi |
| `RealisasiPembayaranCn_model` | realisasi + CN |
| `RealisasiPembayaranDn_model` | realisasi + DN |
| `RealisasiPembayaranPotongan_model` | potongan pada realisasi |
| `RekeningMasuk_model` | `rekening_masuk` |
| `RekeningTampunganMasuk_model` | tampungan masuk |
| `RekeningTampunganKeluar_model` | tampungan keluar |
| `Piutang_model` | `piutang` |
| `BayarPiutang_model` | `bayar_piutang` |
| `SaldoMitra_model` | `saldo_mitra` |
| `SaldoPelanggan_model` | `saldo_pelanggan` |
| `SaldoPlg_model` | `saldo_plg` |
| `SisaBayarPelanggan_model` | `sisa_bayar_pelanggan` |
| `KonfirmasiPembayaranPakan_model` | konfirmasi bayar pakan |
| `KonfirmasiPembayaranVoadip_model` | konfirmasi bayar voadip |
| `KonfirmasiPembayaranDoc_model` | konfirmasi bayar DOC |
| `KonfirmasiPembayaranPeternak_model` | konfirmasi bayar peternak |
| `KonfirmasiPembayaranOaPakan_model` | konfirmasi OA pakan |

### Master Data

| Model | Tabel |
|---|---|
| `Mitra_model` | `mitra` (peternak/plasma) |
| `MitraMapping_model` | mapping mitra |
| `MitraPosisi_model` | posisi mitra |
| `MitraRekeningKoran_model` | rekening koran mitra |
| `Pelanggan_model` | `pelanggan` (bakul) |
| `TelpPelanggan_model` | telpon pelanggan |
| `BankPelanggan_model` | bank pelanggan |
| `Supplier_model` | `supplier` |
| `SupplierPakan_model` | supplier pakan |
| `Karyawan_model` | `karyawan` |
| `UnitKaryawan_model` | unit karyawan |
| `WilayahKaryawan_model` | wilayah karyawan |
| `User_model` | `user` |
| `UserMobile_model` | user mobile |
| `Gudang_model` | `gudang` |
| `Kandang_model` | `kandang` |
| `BangunanKandang_model` | bangunan kandang |
| `Lokasi_model` | `lokasi` |
| `Wilayah_model` | `wilayah` |
| `Ekspedisi_model` | `ekspedisi` |
| `EkspedisiPph23_model` | PPh 23 ekspedisi |
| `BankEkspedisi_model` | bank ekspedisi |
| `Kendaraan_model` | `kendaraan` |
| `KendaraanRiwayat_model` | riwayat kendaraan |
| `Barang_model` | `barang` |
| `Perusahaan_model` | `perusahaan` |
| `PerwakilanMaping_model` | perwakilan maping |
| `Jenis_model` | `jenis` |
| `Nekropsi_model` | `nekropsi` |
| `Solusi_model` | `solusi` |
| `BiayaOperasional_model` | `biaya_operasional` |
| `PotonganPajak_model` | `potongan_pajak` |
| `PotonganPelanggan_model` | `potongan_pelanggan` |
| `SkpMitra_model` | `skp_mitra` |

### Stok

| Model | Tabel |
|---|---|
| `Stok_model` | `stok` |
| `DetStok_model` | `det_stok` |
| `DetStokTrans_model` | `det_stok_trans` |

### Gaji & HRIS

| Model | Tabel |
|---|---|
| `Gaji_model` | `gaji` |
| `GajiKaryawan_model` | `gaji_karyawan` |
| `GajiInsentif_model` | `gaji_insentif` |
| `GajiPotongan_model` | `gaji_potongan` |
| `GajiUnit_model` | `gaji_unit` |
| `BonusInsentifListrik_model` | `bonus_insentif_listrik` |

### Peralatan

| Model | Tabel |
|---|---|
| `OrderPeralatan_model` | `order_peralatan` |
| `OrderPeralatanDetail_model` | `order_peralatan_detail` |
| `TerimaPeralatan_model` | `terima_peralatan` |
| `TerimaPeralatanDetail_model` | `terima_peralatan_detail` |
| `PenjualanPeralatan_model` | `penjualan_peralatan` |
| `PenjualanPeralatanDetail_model` | `penjualan_peralatan_detail` |
| `BayarPeralatan_model` | `bayar_peralatan` |
| `BayarPenjualanPeralatan_model` | `bayar_penjualan_peralatan` |

### Fitur & Akses

| Model | Tabel |
|---|---|
| `Fitur_model` | `fitur` |
| `DetFitur_model` | `det_fitur` |
| `Group_model` | `group` |
| `DetGroup_model` | `det_group` |
| `DetUser_model` | `det_user` |
| `AksesKhusus_model` | `akses_khusus` |
| `SettingReport_model` | `setting_report` |
| `SettingReportGroup_model` | `setting_report_group` |
| `SettingReportGroupItem_model` | `setting_report_group_item` |

---

## Cara Penggunaan Model

```php
// Instantiasi
$m = new \Model\Storage\Lhk_model();

// Query
$data = $m->where('id_kandang', $id)->get()->toArray();

// Raw query
$data = $m->hydrateRaw("SELECT * FROM lhk WHERE id = ?", [$id]);

// Stored procedure
$data = $m->runSp("EXEC hitung_stok_siklus @tipe=?, @id=?", [$tipe, $id]);

// Eager loading
$data = $m->with('lhk_sekat', 'lhk_pakan')->find($id);
```

---

## Konvensi Model

- Semua extends `Conf` (bukan `Model` langsung)
- `$incrementing = false` — ID tidak auto-increment (dibuat manual)
- `$timestamps = false` — tidak ada created_at/updated_at otomatis
- Relasi ditulis dengan full classpath: `'\Model\Storage\LhkSekat_model'`
- Nama model: `NamaEntitas_model` (snake_case dengan suffix `_model`)
