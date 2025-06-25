<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr class="header" style="background-color: #dedede;">
			<td class="text-left" colspan="9"><b><?php echo $v_data['nama'].' | '.$v_data['nama_perusahaan']; ?></b></td>
		</tr>
		<?php $idx_row = 0; ?>
		<?php foreach ($v_data['detail'] as $k_det => $v_det): ?>
			<tr class="detail">
				<td class="text-left"><?php echo tglIndonesia($v_det['tgl_do'], '-', ' '); ?></td>
				<td class="text-left"><?php echo tglIndonesia($v_det['tgl_bayar'], '-', ' '); ?></td>
				<td class="text-left"><?php echo $v_det['no_do']; ?></td>
				<td class="text-left"><?php echo $v_det['no_sj']; ?></td>
				<td class="text-right"><?php echo angkaDecimal($v_det['tagihan']); ?></td>
				<?php if ( $idx_row == 0 ): ?>
					<td class="text-right" rowspan="<?php echo count($v_data['detail']); ?>"><?php echo angkaDecimal($v_data['total_tagihan']); ?></td>
					<td class="text-right" rowspan="<?php echo count($v_data['detail']); ?>"><?php echo angkaDecimal($v_data['total_bayar']); ?></td>
					<td class="text-right" rowspan="<?php echo count($v_data['detail']); ?>"><?php echo angkaDecimal($v_data['lebih_bayar']); ?></td>
				<?php endif ?>
				<td class="text-center"><?php echo $v_det['lama_bayar']; ?></td>
			</tr>
			<?php $idx_row++; ?>
		<?php endforeach ?>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="9">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>