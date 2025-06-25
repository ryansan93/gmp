<?php if ( !empty($data) ): ?>
	<?php foreach ($data as $key => $value): ?>
		<tr class="cursor-p search" onclick="mj.changeTabActive(this)" data-href="action" data-id="<?php echo $value['id']; ?>">
			<td><?php echo strtoupper($value['det_jurnal_trans']['nama']); ?></td>
			<td><?php echo strtoupper($value['jurnal_report']['nama']); ?></td>
			<td><?php echo ($value['posisi'] == 'cr') ? 'KREDIT' : 'DEBET'; ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="3">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>