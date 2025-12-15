<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="search cursor-p data" onclick="sb.changeTabActive(this)" data-href="action" data-tanggal="<?php echo $v_data['tanggal']; ?>" data-edit="">
			<td class="text-center" data-order="<?php echo str_replace('-', '/', $v_data['tanggal']); ?>"><?php echo strtoupper(tglIndonesia($v_data['tanggal'], '-', ' ')); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['saldo']); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="2">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>