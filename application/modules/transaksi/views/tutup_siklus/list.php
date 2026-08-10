<?php if ( !empty($data) && count($data) > 0 ): ?>
	<?php foreach ($data as $k => $val): ?>
		<tr class="v-center search">
			<td><?php echo strtoupper($val['mitra']); ?></td>
			<td class="noreg"><?php echo $val['noreg']; ?></td>
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
			<td class="text-left">
				<?php
					if ( (isset($val['deskripsi']) && !empty($val['deskripsi'])) && (isset($val['waktu']) && !empty($val['waktu'])) ) {
						echo $val['deskripsi'] . ' pada ' . dateTimeFormat( $val['waktu'] );
					} else {
						echo '-';
					}
				?>
			</td>
			<td class="text-center">
				<?php if ( $val['tutup_siklus'] == 1 && empty($val['tgl_panen']) === false && !empty($akses['a_submit']) ): ?>
					<button
						type="button"
						class="btn btn-primary btn-sm"
						onclick="tutupSiklus.openModal(this)"
						data-noreg="<?php echo $val['noreg']; ?>"
						data-tgl_docin="<?php echo $val['tgl_docin_real']; ?>"
						data-tgl_docin_display="<?php echo !empty($val['tgl_docin_real']) ? tglIndonesia( $val['tgl_docin_real'], '-', ' ' ) : '-'; ?>"
					>
						<i class="fa fa-check"></i> Tutup Siklus
					</button>
				<?php elseif ( $val['tutup_siklus'] == 2 && $val['status_rhpp'] == 1 && !empty($akses['a_delete']) ): ?>
					<button
						type="button"
						class="btn btn-danger btn-sm"
						onclick="tutupSiklus.hapus(this)"
						data-noreg="<?php echo $val['noreg']; ?>"
					>
						<i class="fa fa-trash"></i> Hapus
					</button>
				<?php else: ?>
					-
				<?php endif ?>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="8">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>
