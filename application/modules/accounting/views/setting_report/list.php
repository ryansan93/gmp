<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $key => $value): ?>
		<tr class="cursor-p" onclick="sr.changeTabActive(this)" data-id="<?php echo $value['id']; ?>" data-href="action" data-edit="">
			<td><?php echo $value['nama']; ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td>Data tidak ditemukan.</td>
	</tr>
<?php endif ?>