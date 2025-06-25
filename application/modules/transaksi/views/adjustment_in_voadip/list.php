<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $key => $value): ?>
		<tr class="search cursor-p" onclick="aiv.changeTabActive(this)" data-id="<?php echo $value['id']; ?>" data-edit="" data-href="action">
			<td class="text-center"><?php echo strtoupper(tglIndonesia($value['tanggal'], '-', ' ')); ?></td>
			<td class="text-center"><?php echo $value['kode']; ?></td>
			<td><?php echo $value['nama_gudang']; ?></td>
			<td><?php echo $value['nama_barang']; ?></td>
			<td class="text-right"><?php echo angkaDecimal($value['jumlah']); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="5">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>