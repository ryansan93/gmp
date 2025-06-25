<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="cursor-p" onclick="pdm.change_tab(this)" data-id="<?php echo $v_data['no_order']; ?>" data-edit="" data-href="transaksi">
			<td class="text-left"><?php echo $v_data['no_order']; ?></td>
			<td class="text-center"><?php echo strtoupper(tglIndonesia(substr($v_data['tiba'], 0, 10), '-', ' ').' '.substr($v_data['tiba'], 11, 5)); ?></td>
			<td class="text-right"><?php echo angkaRibuan($v_data['ekor']); ?></td>
			<td class="text-right"><?php echo angkaDecimalFormat($v_data['bb'], 3); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="4">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>