<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="search cursor-p" onclick="coa.view_form(this)" data-id="<?php echo $v_data['id']; ?>">
			<td><?php echo strtoupper($v_data['d_perusahaan']['perusahaan']); ?></td>
			<td><?php echo !empty($v_data['id_unit']) ? strtoupper($v_data['id_unit']) : '-'; ?></td>
			<td><?php echo strtoupper($v_data['nama_coa']); ?></td>
			<td class="text-center"><?php echo strtoupper($v_data['coa']); ?></td>
			<td class="text-center"><?php echo ($v_data['lap'] == 'N') ? 'NERACA' : 'LABA / RUGI'; ?></td>
			<td class="text-center"><?php echo ($v_data['coa_pos'] == 'D') ? 'DEBIT' : 'KREDIT'; ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="4">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>