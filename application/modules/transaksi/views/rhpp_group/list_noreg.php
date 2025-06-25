<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr>
			<td class="noreg"><?php echo $v_data['noreg']; ?></td>
			<td><?php echo (int) $v_data['kandang']; ?></td>
			<td><?php echo tglIndonesia($v_data['tgl_docin'], '-', ' '); ?></td>
			<td class="text-center">
				<input class="check cursor-p" target="check" type="checkbox">
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="4">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>