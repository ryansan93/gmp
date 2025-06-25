<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k => $val): ?>
		<tr>
			<td class="page0"><?php echo $val['kota']; ?></td>
			<td class="page0"><?php echo $val['peternak']; ?></td>
			<td class="page0 text-center"><?php echo $val['kandang']; ?></td>
			<td class="page0 text-right"><?php echo angkaRibuan($val['populasi']); ?></td>
			<td class="page0 text-right"><?php echo angkaRibuan($val['umur']); ?></td>
			<td class="page0 pakan"><?php echo $val['pakan']; ?></td>
			<td class="page1"><?php echo $val['no_spm']; ?></td>
			<td class="page1"><?php echo tglIndonesia($val['rcn_tgl'], '-', ' '); ?></td>
			<td class="page1 text-right"><?php echo angkaDecimal($val['rcn_kg']); ?></td>
			<td class="page1 text-right"><?php echo angkaRibuan($val['rcn_zak']); ?></td>
			<td class="page1"><?php echo $val['rcn_ekspedisi']; ?></td>
			<td class="page2"><?php echo tglIndonesia($val['real_tgl_kirim'], '-', ' '); ?></td>
			<td class="page2"><?php echo empty($val['real_tgl_tiba']) ? '-' : tglIndonesia($val['real_tgl_tiba'], '-', ' '); ?></td>
			<td class="page2"><?php echo empty($val['no_sj']) ? '-' : $val['no_sj']; ?></td>
			<td class="page2 text-right"><?php echo angkaDecimal($val['real_kg']); ?></td>
			<td class="page2 text-right"><?php echo angkaRibuan($val['real_zak']); ?></td>
			<td class="page2"><?php echo empty($val['real_ekspedisi']) ? '-' : $val['real_ekspedisi']; ?></td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="17">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>