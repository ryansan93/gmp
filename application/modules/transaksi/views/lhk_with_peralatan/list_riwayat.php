<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="cursor-p" onclick="lhk.change_tab(this)" data-id="<?php echo $v_data['id']; ?>" data-edit="" data-href="transaksi">
			<td class="text-center"><?php echo $v_data['umur']; ?></td>
			<td class="text-right"><?php echo angkaRibuan($v_data['pakai_pakan']); ?></td>
			<td class="text-right"><?php echo angkaRibuan($v_data['sisa_pakan']); ?></td>
			<td class="text-right"><?php echo angkaRibuan($v_data['ekor_mati']); ?></td>
			<td class="text-right"><?php echo angkaDecimalFormat($v_data['bb'], 3); ?></td>
			<td class="text-right"><?php echo angkaDecimalFormat($v_data['fcr'], 3); ?></td>
			<td class="text-right"><?php echo angkaDecimalFormat($v_data['ip'], 3); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="6">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>