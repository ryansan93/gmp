<?php if ( !empty($data) ): ?>
	<?php foreach ($data as $key => $v_det): ?>
		<tr class="cursor-p" onclick="ju.changeTabActive(this)" data-href="action" data-id="<?php echo $v_det['id_header']; ?>">
			<td><?php echo strtoupper(tglIndonesia($v_det['tanggal'], '-', ' ')); ?></td>
			<td class="detail_jurnal"><?php echo (isset($v_det['nama']) && !empty($v_det['nama'])) ? strtoupper($v_det['nama']) : '-'; ?></td>
			<td class="perusahaan"><?php echo strtoupper($v_det['perusahaan']); ?></td>
			<td><?php echo (isset($v_det['asal']) && !empty($v_det['asal'])) ? strtoupper($v_det['asal']) : '-'; ?></td>
			<td><?php echo (isset($v_det['tujuan']) && !empty($v_det['tujuan'])) ? strtoupper($v_det['tujuan']) : '-'; ?></td>
			<?php
				$unit = $v_det['nama_unit'];
				// if ( !empty($v_det['d_unit']) ) {
				// 	$unit = str_replace('kab ', '', $v_det['d_unit']['nama']);
				// 	$unit = str_replace('kota ', '', $unit);
				// } else {
				// 	if ( $v_det['unit'] == 'pusat_gml' ) {
				// 		$unit = 'pusat gemilang';
				// 	} else {
				// 		$unit = 'pusat gemuk';
				// 	}
				// }
			?>
			<td><?php echo strtoupper($unit); ?></td>
			<td><?php echo strtoupper($v_det['keterangan']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_det['nominal']); ?></td>
		</tr>
		<?php foreach ($value['detail'] as $k_det => $v_det): ?>
		<?php endforeach ?>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="8">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>