<!DOCTYPE html>
<html>
<head>
	<style type="text/css">
		.str { mso-number-format:\@; }
		.decimal_number_format { mso-number-format: "\#\,\#\#0.00"; }
		.number_format { mso-number-format: "\#\,\#\#0"; }
		table.bordered thead tr th, table.bordered thead tr td, table.bordered tbody tr th, table.bordered tbody tr td {
			border: 1px solid black;
		}
		.decimal_number_format_bordered { 
			mso-number-format: "\#\,\#\#0.00";
			border: 1px solid black;
		}
	</style>
</head>
<body>
	<div>
        <table class="bordered">
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th>Unit</th>
                    <th>DO</th>
                    <th>Jenis Kandang</th>
                    <th>NIK</th>
                    <th>NPWP</th>
                    <th>Kandang</th>
                    <th>Periode</th>
                    <th>PPL</th>
                    <th>Tgl Chick In</th>
                    <th>Jenis DOC</th>
                    <th>Populasi</th>
                    <th>Total</th>
                    <th>Jenis Pakan</th>
                    <th>Pakan (Kg)</th>
                    <th>Total Pemakaian Pakan Inti</th>
                    <th>Total Obat Inti</th>
                    <th>Total Obat Plasma</th>
                    <th>Tgl Awal Panen</th>
                    <th>Tgl Akhir Panen</th>
                    <th>Durasi Panen (Hari)</th>
                    <th>Umur</th>
                    <th>Deplesi (%)</th>
                    <th>FCR</th>
                    <th>BB (Kg)</th>
                    <th>IP</th>
                    <th>Mutasi (Hari)</th>
                    <th>Jumlah Ekor Terpanen</th>
                    <th>Tonase (Kg)</th>
                    <th>Hasil Penjualan Ayam</th>
                    <th>Rata2 Harga</th>
                    <th>Pendapatan Plasma</th>
                    <th>Potongan Pajak</th>
                    <th>Potongan / Tambahan</th>
                    <th>Transfer</th>
                    <th>Catatan</th>
                    <th>Tgl RHPP Ke Pusat</th>
                    <th>Tgl Transfer RHPP</th>
                    <th>Durasi RHPP Ke Pusat (Hari)</th>
                    <th>Durasi Transf (Hari)</th>
                    <th>Rata2 Pendapatan Plasma/Populasi</th>
                    <th>Modal Inti</th>
                    <th>Modal Inti Sebenarnya (Tanpa Bonus Pasar)</th>
                    <th>Laba Rugi Inti Dengan Estimasi Operasional (Rp. 300)</th>
                    <th>Laba Rugi Inti Tanpa Operasional (Rp. 300)</th>
                    <th>Biaya Operasional 300 / Kg</th>
                    <th>Materai</th>
                    <!-- <th>Cross Check Pembelian Sapronak + Pendapatan Plasma</th>
                    <th>Cross Check Laba/Rugi Inti</th>
                    <th>Cross Check Laba/Rugi Inti Manual vs Program</th>
                    <th>Jumlah RHPP</th>
                    <th>Umur x Ekor</th> -->
                </tr>
            </thead>
            <tbody>
                <?php if ( !empty($data) && count($data) > 0 ) { ?>
                    <?php foreach ($data as $key => $value) { ?>
                        <tr>
                            <td class="str"><?php echo strtoupper($value['nama_bulan']); ?></td>
                            <td class="str"><?php echo strtoupper($value['kode']); ?></td>
                            <td class="str"><?php echo strtoupper($value['nama_perusahaan']); ?></td>
                            <td class="str"><?php echo strtoupper($value['tipe_kdg']); ?></td>
                            <td class="str"><?php echo $value['nik']; ?></td>
                            <td class="str"><?php echo $value['npwp']; ?></td>
                            <td class="str"><?php echo strtoupper($value['nama_mitra'].' (KDG:'.$value['kandang'].')'); ?></td>
                            <td><?php // echo $value['']; ?></td>
                            <td class="str"><?php echo strtoupper($value['ppl']); ?></td>
                            <td><?php echo $value['tgl_docin']; ?></td>
                            <td class="str"><?php echo strtoupper($value['jenis_doc']); ?></td>
                            <td class="number_format"><?php echo $value['populasi']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['tot_doc']; ?></td>
                            <td class="str"><?php echo $value['jenis_pakan']; ?></td>
                            <td class="number_format"><?php echo $value['jml_pakan']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['tot_pakan']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['tot_obat_inti']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['tot_obat_plasma']; ?></td>
                            <td><?php echo $value['tgl_panen_awal']; ?></td>
                            <td><?php echo $value['tgl_panen_akhir']; ?></td>
                            <td class="number_format"><?php echo $value['durasi_panen']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['umur']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['deplesi']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['fcr']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['bb']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['ip']; ?></td>
                            <td class="number_format"><?php echo $value['mutasi']; ?></td>
                            <td class="number_format"><?php echo $value['jml_ekor_terpanen']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['tonase']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['hasil_penjualan_ayam']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['rata_harga']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['pdpt_plasma']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['potongan_pajak']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['potongan']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['transfer']; ?></td>
                            <td><?php // echo $value['tgl_rhpp_ke_pusat']; ?></td>
                            <td><?php echo $value['tgl_rhpp_ke_pusat']; ?></td>
                            <td><?php echo $value['tgl_transfer']; ?></td>
                            <td class="number_format"><?php echo $value['durasi_rhpp_ke_pusat']; ?></td>
                            <td class="number_format"><?php echo $value['durasi_transfer']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['rata_pdpt_plasma_per_populasi']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['modal_inti']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['modal_inti_tanpa_bonus_pasar']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['lr_inti']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['lr_inti_tanpa_opr']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['biaya_operasional']; ?></td>
                            <td class="decimal_number_format"><?php echo $value['biaya_materai']; ?></td>
                            <!-- <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td> -->
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="52">Data tidak ditemukan.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
	</div>
</body>
</html>