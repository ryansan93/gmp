<?php if ( !empty($data) ): ?>
	<?php $no = 0; ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<?php $no += 1; ?>
		<tr class="cursor-p" onclick="pp.changeTabActive(this)" data-href="action" data-id="<?php echo $v_data['id']; ?>">
			<td><?php echo $no; ?></td>
			<td><?php echo strtoupper($v_data['d_pelanggan']['nama']); ?></td>
			<td class="text-center"><?php echo strtoupper(tglIndonesia($v_data['start_date'], '-', ' ', true)); ?></td>
			<td class="text-center"><?php echo strtoupper(tglIndonesia($v_data['end_date'], '-', ' ', true)); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['potongan_persen']); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="5">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>