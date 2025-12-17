<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php
		$tot_jumlah = 0;
		$tot_beli = 0;
	?>
	<?php foreach ($data as $key => $value): ?>
		<tr class="cursor-p">
			<td class="text-left"><?php echo strtoupper($value['jenis_trans']); ?></td>
			<td class="text-center"><?php echo tglIndonesia($value['tgl_trans'], '-', ' '); ?></td>
			<td><?php echo $value['unit']; ?></td>
			<td><?php echo strtoupper($value['nama_asal']).( !empty($value['kdg_asal']) ? ' (KDG:'.$value['kdg_asal'].')' : '' ); ?></td>
			<td><?php echo strtoupper($value['nama_tujuan']).( !empty($value['kdg_tujuan']) ? ' (KDG:'.$value['kdg_tujuan'].')' : '' ); ?></td>
			<td><?php echo $value['noreg']; ?></td>
			<td><?php echo strtoupper($value['nama_barang']); ?></td>
			<td><?php echo strtoupper($value['no_sj']); ?></td>
			<td class="text-right"><?php echo ($value['jumlah'] >= 0) ? angkaDecimal($value['jumlah']) : '('.angkaDecimal(abs($value['jumlah'])).')'; ?></td>
			<td class="text-right"><?php echo ( isset($value['oa']) && $value['oa'] > 0 ) ? angkaDecimal($value['oa']) : 0; ?></td>
			<td class="text-right"><?php echo ( isset($value['oa_mutasi']) && $value['oa_mutasi'] > 0 ) ? angkaDecimal($value['oa_mutasi']) : 0; ?></td>
			<td class="text-right"><?php echo angkaDecimal($value['hrg_beli']); ?></td>
			<td class="text-right tot_beli"><?php echo ($value['tot_beli'] >= 0) ? angkaDecimal($value['tot_beli']) : '('.angkaDecimal(abs($value['tot_beli'])).')'; ?></td>
			<!-- <td class="text-right"><?php echo angkaDecimal($value['hrg_jual']); ?></td>
			<td class="text-right tot_jual"><?php echo ($value['tot_jual'] >= 0) ? angkaDecimal($value['tot_jual']) : '('.angkaDecimal(abs($value['tot_jual'])).')'; ?></td> -->
		</tr>
		<?php
			$tot_jumlah += $value['jumlah'];
			$tot_beli += $value['tot_beli'];
		?>
	<?php endforeach ?>
	<tr>
		<td colspan="8" class="text-right"><b>TOTAL</b></td>
		<td class="text-right"><b><?php echo angkaRibuan($tot_jumlah); ?></b></td>
		<td colspan="3"></td>
		<td class="text-right"><b><?php echo angkaRibuan($tot_beli); ?></b></td>
	</tr>
<?php else: ?>
	<tr>
		<td colspan="12">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>