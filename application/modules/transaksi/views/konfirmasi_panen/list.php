<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="cursor-p" onclick="kp.load_form(this);" data-noreg="<?php echo $v_data['noreg']; ?>" data-id="<?php echo $v_data['id']; ?>" title="Klik untuk input konfirmasi panen">
			<td class="text-center"><?php echo $v_data['tgl_docin']; ?></td>
			<td class="text-center"><?php echo !empty($v_data['tgl_panen']) ? $v_data['tgl_panen'] : '-'; ?></td>
			<td class="text-center noreg"><?php echo $v_data['noreg']; ?></td>
			<td class="text-left"><?php echo $v_data['nama']; ?></td>
			<td class="text-right"><?php echo $v_data['populasi']; ?></td>
			<td class="text-center"><?php echo $v_data['kandang']; ?></td>
			<td class="text-right"><?php echo !empty($v_data['total']) ? $v_data['total'] : 0; ?></td>
			<td class="text-right"><?php echo !empty($v_data['bb_rata2']) ? $v_data['bb_rata2'] : 0; ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="8">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>