<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr data-id="<?php echo $k_data; ?>">
			<td class="text-center jenis" data-val=""><?php echo strtoupper($v_data['jenis']); ?></td>
			<td class="invoice" data-val="<?php echo $v_data['invoice']; ?>"><?php echo !empty($v_data['invoice']) ? $v_data['invoice'] : '-'; ?></td>
			<td class="tgl_docin" data-val="<?php echo $v_data['tgl_docin_real']; ?>"><?php echo strtoupper($v_data['tgl_docin']); ?></td>
			<td class="noreg" data-val="<?php echo $v_data['noreg']; ?>"><?php echo strtoupper($v_data['noreg']); ?></td>
			<td class="text-center kandang" data-val="<?php echo $v_data['kandang']; ?>"><?php echo strtoupper($v_data['kandang']); ?></td>
			<td class="text-right populasi" data-val="<?php echo $v_data['populasi_real']; ?>"><?php echo $v_data['populasi']; ?></td>
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