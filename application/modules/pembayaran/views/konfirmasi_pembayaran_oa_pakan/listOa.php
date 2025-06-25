<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="search">
			<td class="tgl_mutasi" data-val="<?php echo $v_data['tgl_mutasi']; ?>"><?php echo tglIndonesia($v_data['tgl_mutasi'], '-', ' '); ?></td>
			<td class="ekspedisi" data-val="<?php echo $v_data['ekspedisi']; ?>"><?php echo $v_data['ekspedisi']; ?></td>
			<td class="no_polisi" data-val="<?php echo $v_data['no_polisi']; ?>"><?php echo $v_data['no_polisi']; ?></td>
			<td class="no_sj" data-val="<?php echo $v_data['no_sj']; ?>"><?php echo $v_data['no_sj']; ?></td>
			<td><?php echo $v_data['asal']; ?></td>
			<td><?php echo $v_data['tujuan']; ?></td>
			<td class="text-right sub_total" data-val="<?php echo $v_data['sub_total']; ?>"><?php echo angkaDecimal($v_data['sub_total']); ?></td>
			<td class="text-center check">
				<?php $checked = !empty($v_data['checked']) ? 'checked="checked"' : '';?>
				<input type="checkbox" class="cursor-p checkSelf" target="sj" <?php echo $checked; ?> >
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="8">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>