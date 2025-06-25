<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr>
			<td class="barang" data-kode="<?php echo $v_data['kode_barang']; ?>"><?php echo $v_data['nama_barang']; ?></td>
			<td class="text-right jml_kirim"><?php echo angkaDecimal($v_data['jumlah']); ?></td>
			<td>
				<input type="text" class="form-control text-right jml_terima" data-required="1" data-tipe="decimal" placeholder="Terima">
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="3">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>