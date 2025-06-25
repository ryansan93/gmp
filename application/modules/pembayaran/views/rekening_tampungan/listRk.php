<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k_data => $v_data): ?>
		<tr>
			<td class="text-center"><?php echo tglIndonesia($v_data['tanggal'], '-', ' '); ?></td>
			<td class="text-left"><?php echo strtoupper($v_data['d_perusahaan']['perusahaan']); ?></td>
			<td class="text-left"><?php echo strtoupper($v_data['d_pelanggan']['nama']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_data['nominal']); ?></td>
			<td>
				<?php if ( !empty($v_data['lampiran']) ): ?>
					<a href="uploads/<?php echo $v_data['lampiran']; ?>" target="_blank"><?php echo $v_data['lampiran']; ?></a>
				<?php else: ?>
					-
				<?php endif ?>
			</td>
			<td><?php echo !empty($v_data['keterangan']) ? $v_data['keterangan'] : '-'; ?></td>
			<td>
				<button type="button" class="col-xs-12 btn btn-primary" onclick="rt.viewFormRk(this)" data-kode="<?php echo $v_data['kode']; ?>" style="padding: 0px;"><i class="fa fa-file"></i></button>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="6">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>