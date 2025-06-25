<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr>
			<td><?php echo $v_data['nama_barang']; ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['jumlah']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['harga']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['total']); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="4">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>