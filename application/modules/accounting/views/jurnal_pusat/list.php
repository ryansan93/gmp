<?php if ( !empty($data) ): ?>
	<?php foreach ($data as $key => $value): ?>
		<tr class="cursor-p" onclick="jp.changeTabActive(this)" data-href="action" data-id="<?php echo $value['id_header']; ?>">
			<td><?php echo strtoupper(tglIndonesia($value['tanggal'], '-', ' ')); ?></td>
			<td class="detail_jurnal"><?php echo (isset($value['jurnal_trans_detail_nama']) && !empty($value['jurnal_trans_detail_nama'])) ? strtoupper($value['jurnal_trans_detail_nama']) : '-'; ?></td>
			<td class="perusahaan"><?php echo strtoupper($value['nama_perusahaan']); ?></td>
			<td><?php echo (isset($value['asal']) && !empty($value['asal'])) ? strtoupper($value['asal']) : '-'; ?></td>
			<td><?php echo (isset($value['tujuan']) && !empty($value['tujuan'])) ? strtoupper($value['tujuan']) : '-'; ?></td>
			<?php
				$unit = $value['nama_unit'];
				// if ( !empty($value['d_unit']) ) {
				// 	$unit = str_replace('kab ', '', $value['d_unit']['nama']);
	   //          	$unit = str_replace('kota ', '', $unit);
				// } else {
				// 	$unit = $value['unit'];
				// }
			?>
			<td><?php echo strtoupper($unit); ?></td>
			<td><?php echo strtoupper($value['keterangan']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($value['nominal']); ?></td>
		</tr>
	<?php endforeach ?>
	<!-- <?php foreach ($data as $key => $value): ?>
		<?php foreach ($value['detail'] as $k_det => $v_det): ?>
			<tr class="cursor-p" onclick="jp.changeTabActive(this)" data-href="action" data-id="<?php echo $value['id']; ?>">
				<td><?php echo strtoupper(tglIndonesia($v_det['tanggal'], '-', ' ')); ?></td>
				<td class="detail_jurnal"><?php echo (isset($v_det['jurnal_trans_detail']['nama']) && !empty($v_det['jurnal_trans_detail']['nama'])) ? strtoupper($v_det['jurnal_trans_detail']['nama']) : '-'; ?></td>
				<td class="perusahaan"><?php echo strtoupper($v_det['d_perusahaan']['perusahaan']); ?></td>
				<td><?php echo (isset($v_det['asal']) && !empty($v_det['asal'])) ? strtoupper($v_det['asal']) : '-'; ?></td>
				<td><?php echo (isset($v_det['tujuan']) && !empty($v_det['tujuan'])) ? strtoupper($v_det['tujuan']) : '-'; ?></td>
				<?php
					$unit = null;
					if ( !empty($v_det['d_unit']) ) {
						$unit = str_replace('kab ', '', $v_det['d_unit']['nama']);
		            	$unit = str_replace('kota ', '', $unit);
					} else {
						$unit = $v_det['unit'];
					}
				?>
				<td><?php echo strtoupper($unit); ?></td>
				<td><?php echo strtoupper($v_det['keterangan']); ?></td>
				<td class="text-right"><?php echo angkaDecimal($v_det['nominal']); ?></td>
			</tr>
		<?php endforeach ?>
	<?php endforeach ?> -->
<?php else: ?>
	<tr>
		<td colspan="8">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>