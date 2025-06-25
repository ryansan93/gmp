<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<?php 
			// $tgl_panen = '-';
			// $jumlah = 0;
			// $bb_rata2 = 0;
			// if ( !empty($v_data['data_konfir']) ) {
			// 	$tgl_panen = tglIndonesia($v_data['data_konfir']['tgl_panen'], '-', ' ');
			// 	$jumlah = $v_data['data_konfir']['total'];
			// 	$bb_rata2 = $v_data['data_konfir']['bb_rata2'];
			// }
		?>
		<!-- <tr class="cursor-p" onclick="kp.load_form(this);" data-id="<?php echo ( !empty($v_data['data_konfir']) ) ? $v_data['data_konfir']['id'] : null; ?>" title="Klik untuk input konfirmasi panen">
			<td class="text-center"><?php echo tglIndonesia($v_data['tgl_docin'], '-', ' '); ?></td>
			<td class="text-center"><?php echo $tgl_panen; ?></td>
			<td class="text-center noreg"><?php echo $v_data['noreg']; ?></td>
			<td class="text-left"><?php echo $v_data['d_mitra_mapping']['d_mitra']['nama']; ?></td>
			<td class="text-right"><?php echo angkaRibuan($v_data['populasi']); ?></td>
			<td class="text-center"><?php echo $v_data['d_kandang']['kandang']; ?></td>
			<td class="text-right"><?php echo angkaDecimal($jumlah); ?></td>
			<td class="text-right"><?php echo angkaDecimal($bb_rata2); ?></td>
		</tr> -->
		<tr class="cursor-p" onclick="kp.load_form(this);" data-id="<?php echo $v_data['id']; ?>" title="Klik untuk input konfirmasi panen">
			<td class="text-center"><?php echo $v_data['tgl_docin']; ?></td>
			<td class="text-center"><?php echo !empty($v_data['tgl_panen']) ? $v_data['tgl_panen'] : '-'; ?></td>
			<td class="text-center noreg"><?php echo $v_data['noreg']; ?></td>
			<td class="text-left"><?php echo $v_data['nama']; ?></td>
			<td class="text-right"><?php echo $v_data['populasi']; ?></td>
			<td class="text-center"><?php echo $v_data['kandang']; ?></td>
			<td class="text-right"><?php echo !empty($v_data['total']) ? $v_data['total'] : 0; ?></td>
			<td class="text-right"><?php echo !empty($v_data['bb_rata2']) ? $v_data['bb_rata2'] : 0; ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="8">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>