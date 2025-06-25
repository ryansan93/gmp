<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="cursor-p" onclick="pp.change_tab(this)" data-id="<?php echo $v_data['id']; ?>" data-edit="" data-href="transaksi">
			<td class="text-center"><?php echo tglIndonesia($v_data['tanggal'], '-', ' '); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['total']); ?></td>
			<!-- <td class="text-right"><?php echo angkaDecimal($v_data['bayar']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['sisa']); ?></td>
			<td class="text-center"><?php echo strtoupper($v_data['status']); ?></td -->>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="2">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>