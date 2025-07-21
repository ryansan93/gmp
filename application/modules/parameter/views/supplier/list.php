<?php if ( !empty($data)) : ?>
	<?php foreach ($data as $k_supl => $v_supl): ?>
		<?php 
			$resubmit = null;
			if ( $v_supl['status'] == 4 ) {
				$resubmit = $v_supl['id'];
			}
		?>

		<?php 
			$red = null;
			if ( $akses['a_ack'] == 1 ){
				$status = getStatus(1);
				if ( $v_supl['status'] == $status ) {
					$red = 'red';
				}
			} else if ( $akses['a_approve'] == 1 ){
				$status = getStatus(2);
				if ( $v_supl['status'] == $status ) {
					$red = 'red';
				}
			} else {

			}
		?>

		<tr class="search <?php echo $red; ?>">
			<td class="text-center" name="id_supplier" data-nomor="<?php echo $v_supl['nip']; ?>">
				<a class="cursor-p" title="Detail Mitra" data-href="action" data-id="<?php echo $v_supl['id']; ?>" data-resubmit="<?php echo $resubmit; ?>" onclick="supl.changeTabActive(this)">
					<?php echo $v_supl['nip']; ?>
				</a> 
				<?php if ( $akses['a_submit'] == 1 ): ?>
					|| 
					<a class="cursor-p" title="Edit Mitra" data-href="action" data-id="<?php echo $v_supl['id']; ?>" data-resubmit="<?php echo 'EDIT'; ?>" onclick="supl.changeTabActive(this)" >
						<i class="fa fa-edit" aria-hidden="true"></i> 
					</a>
				<?php endif ?>
			</td>
			<td class="text-center"><?php echo strtoupper($v_supl['jenis']); ?></td>
			<td><?php echo $v_supl['nama']; ?></td>
			<td><?php echo $v_supl['nik']; ?></td>
			<td><?php echo $v_supl['alamat']; ?></td>
			<td><?php echo ($v_supl['mstatus'] == 1) ? 'AKTIF' : 'NON AKTIF'; ?></td>
			<td><?php echo $v_supl['saldo_awal']; ?></td>
			<td><?php echo $v_supl['keterangan']; ?></td>
			<td class="text-center">
				<?php if ( $v_supl['mstatus'] == 0 ): ?>
					<button id="btn-add" type="button" class="btn btn-primary cursor-p" title="AKTIF" onclick="supl.load_form_status(this)" data-id="<?php echo $v_supl['id']; ?>" data-tipe="aktif"> 
						AKTIF
					</button>
				<?php else: ?>
					<button id="btn-add" type="button" class="btn btn-danger cursor-p" title="NON AKTIF" onclick="supl.load_form_status(this)" data-id="<?php echo $v_supl['id']; ?>" data-tipe="non_aktif"> 
						NON AKTIF
					</button>
				<?php endif ?>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
		<tr>
			<td class="text-center" colspan="9">Data tidak ditemukan.</td>
		</tr>
<?php endif ?>