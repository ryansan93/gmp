<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr>
			<td class="text-center"><?php echo $v_data['nomor']; ?></td>
			<td class="text-center"><?php echo tglIndonesia($v_data['tgl_bayar'], '-', ' '); ?></td>
			<td><?php echo strtoupper($v_data['d_perusahaan']['perusahaan']); ?></td>
			<td><?php echo strtoupper($v_data['ekspedisi']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['total']); ?></td>
			<td>
				<div class="col-md-12 text-center no-padding">
					<a class="cursor-p" title="DETAIL" onclick="kpoap.changeTabActive(this)" data-href="transaksi" data-id="<?php echo $v_data['id']; ?>" style="color: steelblue;">Lihat</a>
				</div>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="5">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>