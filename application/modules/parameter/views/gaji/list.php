<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr>
			<td class="text-center"><?php echo strtoupper($v_data['nik']); ?></td>
			<td><?php echo strtoupper($v_data['nama']); ?></td>
			<td><?php echo strtoupper($v_data['unit']); ?></td>
			<td class="text-center"><?php echo strtoupper(tglIndonesia($v_data['tgl_berlaku'], '-', ' ')); ?></td>
			<td class="text-right"><?php echo angkaRibuan($v_data['gaji']); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="5">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>