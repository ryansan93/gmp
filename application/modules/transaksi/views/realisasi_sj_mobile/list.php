<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="cursor-p <?php echo empty($v_data['g_status']) ? 'red' : null; ?>" onclick="rsm.changeTabActive(this)" data-tglpanen="<?php echo $v_data['tgl_panen']; ?>" data-noreg="<?php echo $v_data['noreg']; ?>" data-nomor="<?php echo $v_data['nomor']; ?>" data-edit="" data-href="transaksi">
			<td class="text-left"><?php echo strtoupper($v_data['mitra']).' '.$v_data
			['kandang']; ?></td>
			<td class="text-right"><?php echo angkaRibuan($v_data['ekor']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['tonase']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['bb']); ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="4">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>