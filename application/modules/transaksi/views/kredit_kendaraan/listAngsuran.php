<?php if ( $tenor > 0 ): ?>
	<?php for ($i=2; $i <= $tenor; $i++) { ?>
		<tr class="data" data-no="<?php echo $i; ?>">
			<td><?php echo 'ANGSURAN KE '.$i; ?></td>
			<?php
				$jatuh_tempo = substr(next_month($tanggal, ($i-1)), 0, 7).'-'.substr($tgl_jatuh_tempo, 8, 2);
				$month = substr($jatuh_tempo, 5, 2);
				$day = substr($jatuh_tempo, 8, 2);
				$year = substr($jatuh_tempo, 0, 4);
				if ( checkdate($month, $day, $year) == false ) {
					$jatuh_tempo = date("Y-m-t", strtotime($year.'-'.$month.'-01'));
				}
			?>
			<td class="tgl_jatuh_tempo" data-val="<?php echo $jatuh_tempo; ?>"><?php echo strtoupper(tglIndonesia($jatuh_tempo, '-', ' ', true)); ?></td>
			<td class="text-right jumlah" data-val="<?php echo $angsuran; ?>"><?php echo angkaRibuan($angsuran); ?></td>

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
			<td class="text-center">
				<div class="col-xs-12 no-padding attachment">
					<a name="dokumen" class="text-right hide" target="_blank" style="padding-right: 10px;">
						<i class="fa fa-file" style="font-size: 16px;"></i>
					</a>
					<label class="control-label">
						<input style="display: none;" class="file_lampiran no-check lampiran_angsuran" type="file" data-name="name" onchange="kk.showNameFile(this, 0)" data-key="<?php echo 'ANGSURAN KE '.$i; ?>" />
						<i class="fa fa-paperclip cursor-p text-center" title="Lampiran" style="font-size: 20px;"></i> 
					</label>
				</div>
			</td>
			<td></td>
		</tr>
	<?php } ?>
<?php else: ?>
	<tr>
		<td colspan="6">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>