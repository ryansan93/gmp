<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php $total_rhpp = 0; $total_bayar = 0; ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<?php
			$kurang_bayar = '';
			if ( $v_data['bayar'] < $v_data['total_rhpp'] ) {
				$kurang_bayar = 'kurang_bayar';
			}
		?>
		<tr class="search data <?php echo $kurang_bayar; ?>">
			<td><?php echo $v_data['nama']; ?></td>
			<td class="text-center"><?php echo tglIndonesia($v_data['tgl_tutup'], '-', ' '); ?></td>
			<td class="text-center"><?php echo tglIndonesia($v_data['tgl_bayar'], '-', ' '); ?></td>
			<td class="text-right rhpp"><?php echo angkaDecimal($v_data['total_rhpp']); ?></td>
			<td class="text-right bayar"><?php echo angkaDecimal($v_data['bayar']); ?></td>
			<td class="text-left bayar"><?php echo $v_data['keterangan']; ?></td>
		</tr>
		<?php $total_rhpp += $v_data['total_rhpp']; $total_bayar += $v_data['bayar']; ?>
	<?php endforeach ?>
	<tr class="total">
		<td class="text-right" colspan="3">TOTAL</td>
		<td class="text-right total_rhpp"><?php echo angkaDecimal($total_rhpp); ?></td>
		<td class="text-right total_bayar"><?php echo angkaDecimal($total_bayar); ?></td>
		<td class="text-right"></td>
	</tr>
<?php else: ?>
	<tr>
		<td colspan="5">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>