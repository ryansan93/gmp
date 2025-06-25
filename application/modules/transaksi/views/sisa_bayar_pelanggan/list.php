<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="search" data-id="<?php echo $v_data['id']; ?>">
			<td><?php echo tglIndonesia($v_data['tanggal'], '-', ' '); ?></td>
			<td><?php echo strtoupper($v_data['kab_kota']); ?></td>
			<td><?php echo strtoupper($v_data['pelanggan']); ?></td>
			<td class="text-right"><?php echo angkaRibuan($v_data['saldo']); ?></td>
			<td class="text-center">
				<a class="cursor-p hapus" title="Hapus" onclick="sbp.delete(this)"><i class="fa fa-trash"></i></a>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="5">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>