<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="cursor-p" onclick="ppm.change_tab(this)" data-id="<?php echo $v_data['no_sj']; ?>" data-edit="" data-href="transaksi">
			<td class="text-left"><?php echo strtoupper($v_data['no_sj']); ?></td>
			<td class="text-left"><?php echo tglIndonesia($v_data['tiba'], '-', ' '); ?></td>
			<td class="text-left"><?php echo strtoupper($v_data['asal']); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="3">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>