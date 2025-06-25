<!-- UNTUK ISI DARI LIST PAKAI PAKAN -->
<?php foreach ($list as $key => $val) { ?>
	<?php 
		$resubmit = null;
		if ( $val['status'] == 4 ) {
			$resubmit = $val['id'];
		}
	?>

	<?php 
		$red = null;
		if ( $akses['a_ack'] == 1 ){
			$status = getStatus('submit');
			if ( $val['status'] == $status ) {
				$red = 'red';
			}
		} else if ( $akses['a_approve'] == 1 ){
			$status = getStatus('ack');
			if ( $val['status'] == 2 ) {
				$red = 'red';
			}
		} else {

		}
	?>

	<tr class="search <?php echo $red; ?>">
		<td><?php echo tglIndonesia($val['mulai'], '-', ' '); ?></td>
		<td><?php echo $val['nomor']; ?></td>
		<td>
			<div class="col-sm-10 no-padding">
				<?php 
					if ( isset($val['logs'][ count($val['logs']) - 1 ]) ) {
						$last_log = $val['logs'][ count($val['logs']) - 1 ];
						$keterangan = $last_log['deskripsi'] . ' pada ' . dateTimeFormat( $last_log['waktu'] );
					} else {
						$keterangan = '-';
					}

					echo $keterangan;
				?>
			</div>
			<div class="col-sm-1 no-padding">
				<?php if ( $akses['a_edit'] == 1 ){ ?>
					<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="EDIT" onclick="pp.changeTabActive(this)" data-id="<?php echo $val['id']; ?>" data-resubmit="<?php echo 'EDIT'; ?>" data-mulai="<?php echo $val['mulai']; ?>"> 
						<i class="fa fa-edit" aria-hidden="true"></i>
					</button>
				<?php } ?>
				<!-- <button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="LIHAT" onclick="pp.changeTabActive(this)" data-id="<?php echo $val['id']; ?>" data-resubmit="<?php echo $resubmit; ?>"> 
					<i class="fa fa-file" aria-hidden="true"></i>
				</button> -->
				<!-- <a data-toggle="tab" data-href="action" onclick="pp.changeTabActive(this)" class="pull-right cursor-p" data-id="<?php echo $val['id']; ?>" data-resubmit="<?php echo $resubmit; ?>" ><u>Lihat</u></a> -->
				<!-- <a data-toggle="tab" data-href="action" onclick="pp.changeTabActive(this)" class="pull-right cursor-p" data-id="<?php echo $val['id']; ?>" ><u>Edit</u></a> -->
			</div>
			<div class="col-sm-1 no-padding">
				<!-- <button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="EDIT" onclick="pp.changeTabActive(this)" data-id="<?php echo $val['id']; ?>"> 
					<i class="fa fa-edit" aria-hidden="true"></i>
				</button> -->
				<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="LIHAT" onclick="pp.changeTabActive(this)" data-id="<?php echo $val['id']; ?>" data-resubmit="<?php echo $resubmit; ?>" data-mulai="<?php echo $val['mulai']; ?>"> 
					<i class="fa fa-file" aria-hidden="true"></i>
				</button>
				<!-- <a data-toggle="tab" data-href="action" onclick="pp.changeTabActive(this)" class="pull-right cursor-p" data-id="<?php echo $val['id']; ?>" data-resubmit="<?php echo $resubmit; ?>" ><u>Lihat</u></a> -->
				<!-- <a data-toggle="tab" data-href="action" onclick="pp.changeTabActive(this)" class="pull-right cursor-p" data-id="<?php echo $val['id']; ?>" ><u>Edit</u></a> -->
			</div>
		</td>
	</tr>
<?php } ?>