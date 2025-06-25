<div class="row content-panel detailed">
	<div class="col-xs-12" style="padding: 0px 10px; height: 100%;">
		<h1 class="text-center">MITRA GEMUK BERSAMA</h1>
		<div class="col-xs-12 no-padding text-left"><hr class="hr-notifikasi" style="margin-top: 5px; margin-bottom: 15px;"></div>

		<fieldset style="margin-bottom: 15px;">
			<legend>
				<div class="col-xs-12 no-padding">
					<b>NOTIFIKASI</b>
				</div>
			</legend>
			<div class="col-xs-12 no-padding notifContain">
				Tidak ada notifikasi.
			</div>
		</fieldset>

		<?php if ( !empty($formDashboardDirut) ): ?>
			<fieldset>
				<legend>
					<div class="col-xs-12 no-padding">
						<b>DASHBOARD</b>
					</div>
				</legend>
				<?php echo $formDashboardDirut; ?>
			</fieldset>
		<?php endif ?>

		<!-- 
		<?php // else: ?>
			<?php if ( isset($list_notif) && !empty($list_notif) ) { ?>
				<div class="col-xs-12 no-padding text-center" style="font-size: 20px;"><b>NOTIFIKASI</b></div>
				<div class="col-xs-12 no-padding text-left"><hr class="hr-notifikasi" style="margin-top: 5px; margin-bottom: 5px;"></div>
				<?php foreach ($list_notif as $k_ln => $v_ln) { ?>
					<?php foreach ($v_ln['data'] as $k => $val) { ?>
						<?php
							$color_status = '';
							if ( $val['gstatus'] == getStatus('reject') ) {
								$color_status = 'color: red;';
							}

							if ( $val['gstatus'] == getStatus('submit') ) {
								$color_status = 'color: blue;';
							}

							$onclick_link = '';
							if ( isset($v_ln['link']) && !empty($v_ln['link']) ) {
								$link = $v_ln['link'];
								$onclick_link = $link;
								if ( isset($val['key']) && !empty($val['key']) ) {
									$key = $val['key'];
									$link .= '/'.$key;
									$onclick_link = $link;
								}
							}
						?>
						<small>
							<?php
								$action = null;
								if ( $v_ln['jenis'] == 'window.open' ) {
									$action = "window.open('".$onclick_link."', '_self')";
								}

								// if ( $v_ln['jenis'] == 'window.open' ) {
								// 	$action = "window.open('".$onclick_link."', '_self')";
								// }
							?>
							<div class="col-xs-12 no-padding contain-notif cursor-p" onclick="<?php echo $action; ?>">
								<div class="col-xs-12 no-padding"><b><?php echo strtoupper($v_ln['nama_fitur'].' | <span style="'.$color_status.'">'.$val['nama_status'].'</span>'); ?></b></div>
								<div class="col-xs-12 no-padding"><?php echo strtoupper($val['keterangan']); ?></div>
								<div class="col-xs-12 no-padding"><?php echo strtoupper($val['deskripsi'].' '.substr($val['waktu'], 0, 16)); ?></div>
							</div>
						</small>
					<?php } ?>
				<?php } ?>
			<?php } else { ?>
				<h1>MITRA GEMUK BERSAMA</h1>
			<?php } ?>
		<?php // endif ?>
		-->
	</div>
</div>