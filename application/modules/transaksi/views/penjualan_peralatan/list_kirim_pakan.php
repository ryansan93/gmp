<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="v-center">
			<td class="text-left brg" data-kode="<?php echo $v_data['kode_brg']; ?>"><?php echo strtoupper($v_data['nama_brg']); ?></td>
			<td class="text-right"><?php echo angkaRibuan($v_data['jumlah']); ?></td>
			<td>
				<input type="text" class="form-control text-right jumlah_terima" data-tipe="integer" placeholder="Jumlah" data-required="1">
			</td>
			<td>
				<input type="text" class="form-control kondisi uppercase" placeholder="Kondisi">
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="4">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>