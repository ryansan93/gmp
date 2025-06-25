<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="search cursor-p" onclick="ir.modalViewForm(this)" data-id="<?php echo $v_data['id']; ?>">
			<td><?php echo $v_data['id']; ?></td>
			<td><?php echo $v_data['nama']; ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="2">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>