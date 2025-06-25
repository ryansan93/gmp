<?php if ( !empty($data)) : ?>
	<?php foreach ($data as $k_pakan => $v_pakan): ?>
		<?php 
			$resubmit = null;
			if ( $v_pakan['g_status'] == 4 ) {
				$resubmit = $v_pakan['id'];
			}
		?>

		<?php 
			$red = null;
			if ( $akses['a_ack'] == 1 ){
				$status = getStatus('submit');
				if ( $v_pakan['g_status'] == $status ) {
					$red = 'red';
				}
			} else if ( $akses['a_approve'] == 1 ){
				$status = getStatus('ack');
				if ( $v_pakan['g_status'] == 2 ) {
					$red = 'red';
				}
			} else {

			}
		?>

		<tr>
			<td class="text-left nomor" id="no_dokumen" data-id="<?php echo $v_pakan['id']; ?>"><?php echo $v_pakan['nomor']; ?></td>
			<td class="text-center tanggal"><?php echo tglIndonesia($v_pakan['mulai'], '-', ' '); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_pakan['pakan1']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_pakan['pakan2']); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_pakan['pakan3']); ?></td>
			<td><a href="uploads/<?php echo $v_pakan['dokumen']; ?>" target="_blank" title="<?php echo $v_pakan['dokumen']; ?>">
				<?php echo ( strlen($v_pakan['dokumen']) > 15) ? substr($v_pakan['dokumen'], 0, 15) . '.....' : $v_pakan['dokumen']; ?></a>
			</td>
			<td class="text-left status">
				<div class="col-md-10 no-padding">
					<?php 
						$v_logs = end($v_pakan['logs']);
						echo $v_logs['deskripsi'] . ' ' . $v_logs['waktu']; 
					?>
				</div>
				<div class="col-md-1 no-padding">
					<?php if ( $akses['a_edit'] == 1 ){ ?>
						<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="EDIT" onclick="feed.changeTabActive(this)" data-id="<?php echo $v_pakan['id']; ?>" data-resubmit="<?php echo 'EDIT'; ?>" data-mulai="<?php echo substr($v_pakan['mulai'], 0, 10); ?>"> 
							<i class="fa fa-edit" aria-hidden="true"></i>
						</button>
					<?php } ?>
				</div>
				<div class="col-md-1 no-padding">
					<?php if ( $akses['a_view'] == 1 ){ ?>
						<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="LIHAT" onclick="feed.changeTabActive(this)" data-id="<?php echo $v_pakan['id']; ?>" data-resubmit="<?php echo $resubmit; ?>" data-mulai="<?php echo substr($v_pakan['mulai'], 0, 10); ?>"> 
							<i class="fa fa-file" aria-hidden="true"></i>
						</button>
					<?php } ?>
				</div>
			</td>
			<?php if ( $akses['a_ack'] == 1 || $akses['a_approve'] == 1 ) { ?>
				<td class="text-center">
					<?php if ( $v_pakan['g_status'] == 1 ){ ?>
						<?php if ( $akses['a_ack'] == 1 ) { ?>
							<button type="button" class="btn btn-info" onclick="feed.ack(this)">
				 				<i class="fa fa-check" aria-hidden="true"></i> Ack
				 			</button>
				 		<?php } else { echo '-'; } ?>
				 	<?php } else if ( $v_pakan['g_status'] == 2 ){ ?>
						<?php if ( $akses['a_approve'] == 1 ) { ?>
							<button type="button" class="btn btn-info" onclick="feed.approve(this)">
				 				<i class="fa fa-check" aria-hidden="true"></i> Approve
				 			</button>
						<?php } else { echo '-'; } ?>
					<?php } else { echo '-'; } ?>
				</td>
			<?php } ?>
		</tr>
	<?php endforeach ?>
<?php else: ?>
		<tr>
			<td class="text-center" colspan="7">Data tidak ditemukan.</td>
		</tr>
<?php endif ?>