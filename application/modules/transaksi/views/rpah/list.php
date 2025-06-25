<?php if ( count($data) > 0 ): ?>
	<?php foreach ($data as $k => $val): ?>
		<?php 
			$color = null;
			if ( $akses['a_ack'] == 1 ) {
				if ( $val['g_status'] == getStatus('submit') ) {
					$color = 'not-yet';
				}
			}
			if ( $akses['a_submit'] == 1 ) {
				if ( $val['g_status'] == getStatus('reject') ) {
					$color = 'rejected';
				}
			}
		?>
		<tr>
			<td class="<?php echo $color; ?>"><?php echo tglIndonesia( $val['tgl_panen'], '-', ' ' ); ?></td>
			<td class="<?php echo $color; ?>"><?php echo $val['unit']; ?></td>
			<td class="text-right <?php echo $color; ?>"><?php echo angkaRibuan( $val['bottom_price'] ); ?></td>
			<td class="<?php echo $color; ?>">
				<div class="col-md-11 no-padding">
					<?php
						if ( !empty($val['log']) ) {
							$keterangan = $val['log']['deskripsi'] . ' pada ' . dateTimeFormat( $val['log']['waktu'] );
							echo $keterangan;
						} else {
							echo '-';
						}
					?>
				</div>
				<div class="col-md-1 text-right no-padding">
					<a class="cursor-p" title="DETAIL" onclick="rpah.changeTabActive(this)" data-href="rpah" data-id="<?php echo $val['id']; ?>" style="color: steelblue;">Lihat</a>
				</div>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td colspan="4">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>