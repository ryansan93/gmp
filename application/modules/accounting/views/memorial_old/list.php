<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="search cursor-p data" onclick="mm.changeTabActive(this)" data-href="action" data-kode="<?php echo $v_data['no_mm']; ?>" data-edit="">
			<td class="text-center" data-order="<?php echo str_replace('-', '/', $v_data['tgl_mm']); ?>"><?php echo strtoupper(tglIndonesia($v_data['tgl_mm'], '-', ' ')); ?></td>
			<td><?php echo strtoupper($v_data['no_mm']); ?></td>
			<td><?php echo !empty($v_data['pelanggan']) ? strtoupper($v_data['pelanggan']) : '-'; ?></td>
			<td><?php echo !empty($v_data['supplier']) ? strtoupper($v_data['supplier']) : '-'; ?></td>
			<td><?php echo !empty($v_data['keterangan']) ? strtoupper($v_data['keterangan']) : '-'; ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['nilai']); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="6">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>