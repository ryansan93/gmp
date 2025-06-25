<?php if ( !empty($data)) : ?>
	<?php foreach ($data as $k_doc => $v_doc): ?>
		<?php 
			$resubmit = null;
			if ( $v_doc['g_status'] == 4 ) {
				$resubmit = $v_doc['id'];
			}
		?>

		<?php 
			$red = null;
			if ( $akses['a_ack'] == 1 ){
				$status = getStatus('submit');
				if ( $v_doc['g_status'] == $status ) {
					$red = 'red';
				}
			} else if ( $akses['a_approve'] == 1 ){
				$status = getStatus('ack');
				if ( $v_doc['g_status'] == 2 ) {
					$red = 'red';
				}
			} else {

			}
		?>

		<tr>
			<td class="text-left nomor" id="no_dokumen" data-id="<?php echo $v_doc['id']; ?>"><?php echo $v_doc['nomor']; ?></td>
			<td class="text-center tanggal"><?php echo tglIndonesia($v_doc['mulai'], '-', ' '); ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_doc['doc']); ?></td>
			<td><a href="uploads/<?php echo $v_doc['dokumen']; ?>" target="_blank" title="<?php echo $v_doc['dokumen']; ?>">
				<?php echo ( strlen($v_doc['dokumen']) > 30) ? substr($v_doc['dokumen'], 0, 30) . '.....' : $v_doc['dokumen']; ?></a>
			</td>
			<td class="text-left status">
				<div class="col-md-10 no-padding">
					<?php 
						$v_logs = end($v_doc['logs']);
						echo $v_logs['deskripsi'] . ' ' . $v_logs['waktu']; 
					?>
				</div>
				<div class="col-md-1 no-padding">
					<?php if ( $akses['a_edit'] == 1 ){ ?>
						<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="EDIT" onclick="doc.changeTabActive(this)" data-id="<?php echo $v_doc['id']; ?>" data-resubmit="<?php echo 'EDIT'; ?>" data-mulai="<?php echo substr($v_doc['mulai'], 0, 10); ?>"> 
							<i class="fa fa-edit" aria-hidden="true"></i>
						</button>
					<?php } ?>
				</div>
				<div class="col-md-1 no-padding">
					<?php if ( $akses['a_view'] == 1 ){ ?>
						<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-right" title="LIHAT" onclick="doc.changeTabActive(this)" data-id="<?php echo $v_doc['id']; ?>" data-resubmit="<?php echo $resubmit; ?>" data-mulai="<?php echo substr($v_doc['mulai'], 0, 10); ?>"> 
							<i class="fa fa-file" aria-hidden="true"></i>
						</button>
					<?php } ?>
				</div>
			</td>
			<?php if ( $akses['a_ack'] == 1 || $akses['a_approve'] == 1 ) { ?>
				<td class="text-center">
					<?php if ( $v_doc['g_status'] == 1 ){ ?>
						<?php if ( $akses['a_ack'] == 1 ) { ?>
							<button type="button" class="btn btn-info" onclick="doc.ack(this)">
				 				<i class="fa fa-check" aria-hidden="true"></i> Ack
				 			</button>
				 		<?php } else { echo '-'; } ?>
				 	<?php } else if ( $v_doc['g_status'] == 2 ){ ?>
						<?php if ( $akses['a_approve'] == 1 ) { ?>
							<button type="button" class="btn btn-info" onclick="doc.approve(this)">
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