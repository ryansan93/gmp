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
				<div class="col-md-12 no-padding">
					<div class="col-md-11 no-padding text-left">
						<?php
							// if ( !empty($val['logs']) && count($val['logs']) > 0 ) {
							// 	$last_log = $val['logs'][ count($val['logs']) - 1 ];
							// 	$keterangan = $last_log['deskripsi'] . ' pada ' . dateTimeFormat( $last_log['waktu'] );
							// 	echo $keterangan;
							// } else {
							// 	echo '-';
							// }

							if ( (isset($val['deskripsi']) && !empty($val['deskripsi'])) && (isset($val['waktu']) && !empty($val['waktu'])) ) {
								$keterangan = $val['deskripsi'] . ' pada ' . dateTimeFormat( $val['waktu'] );
								echo $keterangan;
							} else {
								echo '-';
							}
						?>
					</div>
					<div class="col-md-1 no-padding text-right"><a class="cursor-p" onclick="tsdrhpp.changeTabActive(this)" data-noreg="<?php echo $val['noreg']; ?>" data-href="rhpp">Lihat</a></div>
				</div>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="7">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>