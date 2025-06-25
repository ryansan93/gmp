<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $key => $value): ?>
		<tr>
			<td class="text-center"><?php echo tglIndonesia($value['datang'], '-', ' '); ?></td>
			<td><?php echo $value['no_sj']; ?></td>
			<td><?php echo '-'; ?></td>
			<td><?php echo $value['unit']; ?></td>
			<td><?php echo $value['supplier']; ?></td>
			<td><?php echo $value['barang']; ?></td>
			<td><?php echo $value['nama_perusahaan']; ?></td>
			<td class="text-right jumlah"><?php echo angkaDecimal($value['jumlah']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($value['harga']); ?></td>
			<td class="text-right total"><?php echo angkaDecimal($value['total']); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="10">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>