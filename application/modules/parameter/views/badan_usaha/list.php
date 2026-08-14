<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="cursor-p" onclick="bu.editForm(this)" data-id="<?php echo $v_data['id_badan_usaha']; ?>">
			<td><?php echo strtoupper($v_data['id_badan_usaha']); ?></td>
			<td><?php echo strtoupper($v_data['nama_badan_usaha']); ?></td>
			<td><?php echo !empty($v_data['singkatan']) ? strtoupper($v_data['singkatan']) : '-'; ?></td>
			<td><?php echo ($v_data['status_hukum'] == 1) ? 'Berbadan Hukum' : 'Bukan Berbadan Hukum'; ?></td>
			<td><?php echo ($v_data['is_terbuka'] == 1) ? 'Ya' : '-'; ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="5">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>