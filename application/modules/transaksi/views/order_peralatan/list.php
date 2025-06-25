<?php if ( !empty( $data ) ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="search cursor-p" onclick="op.changeTabActive(this)" data-id="<?php echo $v_data['id']; ?>" data-href="action" data-edit="">
			<td><?php echo tglIndonesia($v_data['tgl_order'], '-', ' '); ?></td>
			<td class="text-center"><?php echo $v_data['no_order']; ?></td>
			<td><?php echo $v_data['nama_supplier']; ?></td>
			<td><?php echo $v_data['nama_mitra']; ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['total']); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="5">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>