<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php $no = 1; ?>
	<?php foreach ($data as $key => $value): ?>
		<tr class="cursor-p" onclick="sld.changeTabActive(this)" data-href="action" data-edit="" data-id="<?php echo $value['id']; ?>">
			<td class="text-center"><?php echo $no; ?></td>
			<td><?php echo strtoupper(tglIndonesia($value['tanggal'], '-', ' ')); ?></td>
			<td><?php echo strtoupper($value['d_perusahaan']['perusahaan']); ?></td>
		</tr>
		<?php $no++; ?>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="3">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>