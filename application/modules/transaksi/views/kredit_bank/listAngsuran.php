<?php if ( $tenor > 0 ): ?>
	<?php for ($i=2; $i <= $tenor; $i++) { ?>
		<tr class="data" data-no="<?php echo $i; ?>">
			<td class="col-xs-2"><?php echo 'ANGSURAN KE '.$i; ?></td>
			<?php
				$jatuh_tempo = substr(next_month($tanggal, ($i-1)), 0, 7).'-'.substr($tgl_jatuh_tempo, 8, 2);
				$month = substr($jatuh_tempo, 5, 2);
				$day = substr($jatuh_tempo, 8, 2);
				$year = substr($jatuh_tempo, 0, 4);
				if ( checkdate($month, $day, $year) == false ) {
					$jatuh_tempo = date("Y-m-t", strtotime($year.'-'.$month.'-01'));
				}
			?>
			<td class="col-xs-1 text-right jumlah" data-val="<?php echo $angsuran; ?>"><?php echo angkaRibuan($angsuran); ?></td>
			<td class="text-right pokok" data-val="<?php echo $angsuran_pokok; ?>"><?php echo angkaRibuan($angsuran_pokok); ?></td>
			<td class="text-right bunga" data-val="<?php echo $angsuran_bunga; ?>"><?php echo angkaRibuan($angsuran_bunga); ?></td>
			<td class="tgl_jatuh_tempo" data-val="<?php echo $jatuh_tempo; ?>"><?php echo strtoupper(tglIndonesia($jatuh_tempo, '-', ' ', true)); ?></td>

			<?php 
				$disabled = null;
				if ( $i > 2 ) {
					$disabled = 'disabled';
				} 
			?>

			<td>
				<div class="input-group date tgl_bayar">
	                <input type="text" class="form-control uppercase text-center" placeholder="Tanggal" <?php echo $disabled; ?> />
	                <span class="input-group-addon">
	                    <span class="glyphicon glyphicon-calendar"></span>
	                </span>
	            </div>
			</td>
		</tr>
	<?php } ?>
<?php else: ?>
	<tr>
		<td colspan="6">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>