<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr data-supplier="<?php echo $v_data['supplier']; ?>">
			<td class="text-center tgl_order" data-val="<?php echo $v_data['tgl_order']; ?>"><?php echo tglIndonesia($v_data['tgl_order'], '-', ' '); ?></td>
			<td class="kota_kab" data-val="<?php echo $v_data['id_kota_kab']; ?>"><?php echo strtoupper($v_data['kota_kab']); ?></td>
			<td class="perusahaan" data-val="<?php echo $v_data['id_perusahaan']; ?>"><?php echo strtoupper($v_data['perusahaan']); ?></td>
			<td class="no_order" data-val="<?php echo $v_data['no_order']; ?>"><?php echo strtoupper($v_data['no_order']); ?></td>
			<td class="peternak" data-val="<?php echo $v_data['no_peternak']; ?>"><?php echo strtoupper($v_data['peternak']); ?></td>
			<td class="text-center kandang" data-val="<?php echo $v_data['kandang']; ?>"><?php echo $v_data['kandang']; ?></td>
			<td class="text-right populasi" data-val="<?php echo $v_data['populasi']; ?>"><?php echo angkaRibuan($v_data['populasi']); ?></td>
			<td class="text-right harga" data-val="<?php echo $v_data['harga']; ?>"><?php echo angkaDecimal($v_data['harga']); ?></td>
			<td class="text-right total" data-val="<?php echo $v_data['total']; ?>"><?php echo angkaDecimal($v_data['total']); ?></td>
			<td class="text-center">
				<?php $checked = !empty($v_data['checked']) ? 'checked="checked"' : '';?>
				<input type="checkbox" class="cursor-p check" target="list_data" <?php echo $checked; ?> >
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="10">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>