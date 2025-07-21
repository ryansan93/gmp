<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="cursor-p" onclick="jns.editForm(this)" data-id="<?php echo $v_data['id']; ?>">
			<td><?php echo strtoupper($v_data['kode']); ?></td>
			<td><?php echo strtoupper($v_data['nama']); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="2">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>