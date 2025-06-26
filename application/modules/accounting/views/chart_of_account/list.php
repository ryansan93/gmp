<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="search cursor-p" onclick="coa.view_form(this)" data-id="<?php echo $v_data['id']; ?>">
			<td class="hide"><?php echo strtoupper($v_data['d_perusahaan']['perusahaan']); ?></td>
			<td class="hide"><?php echo !empty($v_data['id_unit']) ? strtoupper($v_data['id_unit']) : '-'; ?></td>
			<td class="text-center"><?php echo strtoupper($v_data['coa']); ?></td>
			<td><?php echo strtoupper($v_data['nama_coa']); ?></td>
			<td><?php echo !empty($v_data['gol1']) ? strtoupper($v_data['gol1']) : '-'; ?></td>
			<td><?php echo !empty($v_data['gol2']) ? strtoupper($v_data['gol2']) : '-'; ?></td>
			<td><?php echo !empty($v_data['gol3']) ? strtoupper($v_data['gol3']) : '-'; ?></td>
			<td><?php echo !empty($v_data['gol4']) ? strtoupper($v_data['gol4']) : '-'; ?></td>
			<td><?php echo !empty($v_data['gol5']) ? strtoupper($v_data['gol5']) : '-'; ?></td>
			<td class="text-center"><?php echo ($v_data['lap'] == 'N') ? 'NERACA' : 'LABA / RUGI'; ?></td>
			<td class="text-center"><?php echo ($v_data['coa_pos'] == 'D') ? 'DEBIT' : 'KREDIT'; ?></td>
			<td class="text-center"><?php echo ($v_data['status'] == 1) ? 'AKTIF' : 'NON AKTIF'; ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="10">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>