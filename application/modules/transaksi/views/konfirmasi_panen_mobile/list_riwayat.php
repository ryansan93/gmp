<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="cursor-p" onclick="kpm.change_tab(this)" data-id="<?php echo $v_data['id']; ?>" data-edit="" data-href="transaksi">
			<td class="text-center"><?php echo $v_data['umur']; ?></td>
			<td class="text-center"><?php echo strtoupper(tglIndonesia($v_data['tgl_panen'], '-', ' ')); ?></td>
			<td class="text-right"><?php echo angkaRibuan($v_data['total']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['bb_rata2']); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="6">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>