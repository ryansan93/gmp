<?php if ( !empty($data) ): ?>
	<?php foreach ($data as $key => $value): ?>
		<tr class="cursor-p" onclick="jurnal.changeTabActive(this)" data-href="action" data-id="<?php echo $value['id']; ?>">
			<td><?php echo strtoupper(tglIndonesia($value['tanggal'], '-', ' ')); ?></td>
			<td><?php echo strtoupper($value['jurnal_trans']['nama']); ?></td>
			<td><?php echo strtoupper($value['unit']); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="3">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>