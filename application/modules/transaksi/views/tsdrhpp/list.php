<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k => $val): ?>
		<tr class="v-center search">
			<td><?php echo strtoupper($val['mitra']); ?></td>
			<td class="noreg text-center"><?php echo $val['noreg']; ?></td>
			<td class="text-center"><?php echo $val['kandang']; ?></td>
			<td class="text-right"><?php echo angkaRibuan($val['populasi']); ?></td>
			<td class="text-center"><?php echo !empty($val['tgl_docin_real']) ? tglIndonesia( $val['tgl_docin_real'], '-', ' ' ) : '-'; ?></td>
			<td class="text-center"><?php echo tglIndonesia( $val['tgl_panen'], '-', ' ' ); ?></td>
			<td class="text-center">
				<?php if ( $val['tutup_siklus'] == 2 ): ?>
					<span class="label label-success">Sudah Tutup Siklus</span>
				<?php else: ?>
					<span class="label label-warning">Belum Tutup Siklus</span>
				<?php endif ?>
			</td>
			<td class="text-center"><?php echo !empty($val['tgl_tutup']) ? tglIndonesia( $val['tgl_tutup'], '-', ' ' ) : '-'; ?></td>
			<td class="text-center">
				<?php if ( $val['status_rhpp'] == 2 ): ?>
					<span class="label label-success">Sudah RHPP</span>
				<?php else: ?>
					<span class="label label-warning">Belum RHPP</span>
				<?php endif ?>
			</td>
			<td class="text-center">
				<button type="button" class="btn btn-primary btn-sm" onclick="tsdrhpp.changeTabActive(this)" data-noreg="<?php echo $val['noreg']; ?>" data-href="rhpp"><i class="fa fa-eye"></i> Lihat</button>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="10">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>