<?php if ( !empty($data) ): ?>
	<?php $tot_cr = 0; $tot_db = 0; ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr>
			<td class="text-center"><?php echo strtoupper(tglIndonesia($v_data['tanggal'], '-', ' ')); ?></td>
			<td><?php echo $v_data['bank']; ?></td>
			<td><?php echo $v_data['keterangan']; ?></td>
			<td class="text-right"><?php echo ($v_data['debet'] == 0) ? '-' : angkaRibuan($v_data['debet']); ?></td>
			<td class="text-right"><?php echo ($v_data['kredit'] == 0) ? '-' : angkaRibuan($v_data['kredit']); ?></td>
		</tr>
		<?php 
			$tot_db += $v_data['kredit'];
			$tot_cr += $v_data['debet'];
		?>
	<?php endforeach ?>
	<tr>
		<td class="text-right" colspan="3"><b>TOTAL</b></td>
		<td class="text-right"><b><?php echo angkaRibuan($tot_db); ?></b></td>
		<td class="text-right"><b><?php echo angkaRibuan($tot_cr); ?></b></td>
	</tr>
<?php else: ?>
	<tr>
		<td colspan="5">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>