<?php if ( count($data) > 0 ): ?>
	<?php $idx = 0; ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<?php $idx++; ?>
		<tr class="cursor-p search" title="Klik 2x untuk edit data" ondblclick="solusi.edit_form(this)" data-id="<?php echo $v_data['id']; ?>">
			<td><?php echo $idx; ?></td>
			<td><?php echo $v_data['keterangan']; ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="2">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>