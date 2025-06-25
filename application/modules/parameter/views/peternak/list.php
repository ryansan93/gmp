<!-- UNTUK ISI DARI LIST MITRA -->
<?php if ( count($list_mitra) > 0 ): ?>
	<?php foreach ($list_mitra as $mitra): ?>
		<?php 
			$resubmit = null;
			$mstatus = getStatus($mitra['status']);
			if ( $mstatus == 4 ) {
				$resubmit = $mitra['id'];
			}
		?>

		<?php 
			$red = null;
			if ( $akses['a_ack'] == 1 ){
				$status = getStatus('submit');

				if ( $mstatus == $status ) {
					$red = 'red';
				}
			} else if ( $akses['a_approve'] == 1 ){
				$status = getStatus('ack');
				if ( $mstatus == $status ) {
					$red = 'red';
				}
			} else {

			}
		?>

		<tr class="search <?php echo $red; ?>">
			<?php if ($akses['a_approve'] == 1 ): ?>
				<td class="text-center">
					<?php if ( $mitra['status'] == 'ack' ): ?>
						<div class="checkbox checkbox-primary">
							<input type="checkbox" class="styled styled-primary" name="mark" onclick="ptk.set_mark(this)" value="<?php echo $mitra['id'] ?>">
						</div>
					<?php else: ?>
						-
					<?php endif; ?>
				</td>
			<?php endif; ?>
			<td>
				<a class="cursor-p" title="Detail Mitra" data-href="action" data-id="<?php echo $mitra['id']; ?>" data-resubmit="<?php echo $resubmit; ?>" onclick="ptk.changeTabActive(this)">
					<?php echo $mitra['nomor']; ?> 
				</a> 
				<?php if ( $akses['a_submit'] == 1 ) : ?>
					|| 
					<a class="cursor-p" title="Edit Mitra" data-href="action" data-id="<?php echo $mitra['id']; ?>" data-resubmit="<?php echo 'EDIT'; ?>" onclick="ptk.changeTabActive(this)" >
						<i class="fa fa-edit" aria-hidden="true"></i> 
					</a>
				<?php endif ?>
				<?php if ( $akses['a_delete'] == 1 ) : ?>
					|| 
					<a class="cursor-p" title="Hapus Mitra" data-href="action" data-id="<?php echo $mitra['id']; ?>" data-resubmit="<?php echo 'EDIT'; ?>" onclick="ptk.deleteMitra(this)" style="color: red;" >
						<i class="fa fa-trash" aria-hidden="true"></i> 
					</a>
				<?php endif ?>
			</td>
			<td><?php echo $mitra['ktp']; ?></td>
			<td><?php echo $mitra['nama']; ?></td>
			<td><?php echo $mitra['unit']; ?></td>
			<td><?php echo $mitra['alamat']; ?></td>
			<td><?php echo strtoupper($mitra['status']); ?></td>
			<td><?php echo $mitra['keterangan']; ?></td>
	   </tr>
	<?php endforeach ?>
<?php else: ?>
	<tr>
		<td class="text-center" colspan="7">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>