<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $key => $value): ?>
		<tr class="search cursor-p" onclick="pp.changeTabActive(this)" data-id="<?php echo $value['id']; ?>" data-href="action" data-edit="">
			<td><?php echo strtoupper(tglIndonesia($value['tgl_bayar'], '-', ' ')); ?></td>
			<td><?php echo $value['no_faktur']; ?></td>
			<td><?php echo $value['nama_supplier']; ?></td>
			<td><?php echo $value['nama_mitra']; ?></td>
			<td class="text-right"><?php echo angkaDecimal($value['jml_tagihan']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($value['tot_bayar']); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="6">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>