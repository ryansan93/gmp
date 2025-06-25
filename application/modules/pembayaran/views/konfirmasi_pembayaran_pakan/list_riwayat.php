<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr>
			<td class="text-center"><?php echo $v_data['nomor']; ?></td>
			<td class="text-center"><?php echo !empty($v_data['invoice']) ? $v_data['invoice']: '-'; ?></td>
			<td class="text-center"><?php echo tglIndonesia($v_data['tgl_bayar'], '-', ' '); ?></td>
			<td><?php echo strtoupper($v_data['d_perusahaan']['perusahaan']); ?></td>
			<td><?php echo strtoupper($v_data['d_supplier']['nama']); ?></td>
			<td class="text-right"><?php echo angkaRibuan($v_data['total']); ?></td>
			<td>
				<div class="col-md-12 text-center no-padding">
					<a class="cursor-p" title="DETAIL" onclick="kpp.changeTabActive(this)" data-href="transaksi" data-id="<?php echo $v_data['id']; ?>" style="color: steelblue;">Lihat</a>
				</div>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="6">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>