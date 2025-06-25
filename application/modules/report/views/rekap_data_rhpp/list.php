<?php if ( !empty($data) && count($data) > 0 ) { ?>
	<?php $no = 1; ?>
	<?php foreach ($data as $key => $value) { ?>
		<tr>
			<td><?php echo $no; ?></td>
			<td><?php echo strtoupper($value['nama_peternak']); ?></td>
			<td><?php echo strtoupper($value['kandang']); ?></td>
			<td class="text-center"><?php echo $value['periode']; ?></td>
			<td><?php echo strtoupper(tglIndonesia($value['tgl_docin'], '-', ' ')); ?></td>
			<td><?php echo strtoupper($value['jenis']); ?></td>
			<td><?php echo strtoupper(str_replace('_', ' ', $value['ketegori'])); ?></td>
			<td><?php echo strtoupper(tglIndonesia($value['tgl_distribusi'], '-', ' ')); ?></td>
			<td><?php // echo $value['-- nota']; ?></td>
			<td><?php echo strtoupper($value['barang']); ?></td>
			<td class="text-right box_sak" data-val="<?php echo $value['box_sak']; ?>"><?php echo ($value['box_sak'] > 0) ? angkaDecimal($value['box_sak']) : '('.angkaDecimal(abs($value['box_sak'])).')'; ?></td>
			<td class="text-right jumlah" data-val="<?php echo $value['jumlah']; ?>"><?php echo ($value['jumlah'] > 0) ? angkaDecimal($value['jumlah']) : '('.angkaDecimal(abs($value['jumlah'])).')'; ?></td>
			<td class="text-right"><?php echo angkaDecimal($value['harga']); ?></td>
			<td class="text-right total" data-val="<?php echo $value['total']; ?>"><?php echo ($value['total'] > 0) ? angkaDecimal($value['total']) : '('.angkaDecimal(abs($value['total'])).')'; ?></td>
			<td class="text-right mutasi_barang" data-val="<?php echo $value['mutasi_barang']; ?>"><?php echo ($value['mutasi_barang'] > 0) ? angkaDecimal($value['mutasi_barang']) : '('.angkaDecimal(abs($value['mutasi_barang'])).')'; ?></td>
			<td class="text-right nominal" data-val="<?php echo $value['nominal']; ?>"><?php echo ($value['nominal'] > 0) ? angkaDecimal($value['nominal']) : '('.angkaDecimal(abs($value['nominal'])).')'; ?></td>
			<td class="text-right mutasi_box_sak" data-val="<?php echo $value['mutasi_box_sak']; ?>"><?php echo ($value['mutasi_box_sak'] > 0) ? angkaDecimal($value['mutasi_box_sak']) : '('.angkaDecimal(abs($value['mutasi_box_sak'])).')'; ?></td>
			<td><?php echo strtoupper($value['do']); ?></td>
			<td><?php echo strtoupper($value['unit']); ?></td>
			<td><?php echo strtoupper(tglIndonesia($value['tgl_awal_panen'], '-', ' ')); ?></td>
			<td><?php echo strtoupper(tglIndonesia($value['tgl_akhir_panen'], '-', ' ')); ?></td>
		</tr>
		<?php $no++; ?>
	<?php } ?>
<?php } else { ?>
	<tr>
		<td colspan="21">Data tidak ditemukan.</td>
	</tr>
<?php } ?>