<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="data" onclick="bakul.changeTabActive(this)" data-id="<?php echo $v_data['id']; ?>" data-edit="" data-href="transaksi">
			<td class="text-center"><?php echo tglIndonesia($v_data['tgl_bayar'], '-', ' '); ?></td>
			<td class="text-left"><?php echo strtoupper($v_data['pelanggan']); ?></td>
			<td class="text-right"><?php echo angkaRibuan($v_data['jml_transfer']); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="3">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>