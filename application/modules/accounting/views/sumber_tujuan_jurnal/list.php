<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="cursor-p search" onclick="stj.view_form(this)" data-id="<?php echo $v_data['id']; ?>">
			<td><?php echo strtoupper($v_data['jurnal_trans']['nama']); ?></td>
			<td><?php echo strtoupper($v_data['nama']); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="2">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>