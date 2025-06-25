<?php if ( !empty($data)) : ?>
	<?php foreach ($data as $k_plg => $v_plg): ?>
		<?php 
			$resubmit = null;
			if ( $v_plg['status'] == 4 ) {
				$resubmit = $v_plg['id'];
			}
		?>

		<?php 
			$red = null;
			if ( $akses['a_ack'] == 1 ){
				$status = getStatus(1);
				if ( $v_plg['status'] == $status ) {
					$red = 'red';
				}
			} else if ( $akses['a_approve'] == 1 ){
				$status = getStatus(2);
				if ( $v_plg['status'] == $status ) {
					$red = 'red';
				}
			} else {

			}
		?>

		<tr class="search <?php echo $red; ?>">
			<td class="text-center" name="id_pelanggan" data-nomor="<?php echo $v_plg['nip']; ?>">
				<a class="cursor-p" title="Detail Mitra" data-href="action" data-id="<?php echo $v_plg['id']; ?>" data-resubmit="<?php echo $resubmit; ?>" onclick="plg.changeTabActive(this)">
					<?php echo $v_plg['nip']; ?>
				</a> 
				<?php if ( $akses['a_submit'] == 1 ): ?>
					|| 
					<a class="cursor-p" title="Edit Mitra" data-href="action" data-id="<?php echo $v_plg['id']; ?>" data-resubmit="<?php echo 'EDIT'; ?>" onclick="plg.changeTabActive(this)" >
						<i class="fa fa-edit" aria-hidden="true"></i> 
					</a>
				<?php endif ?>
			</td>
			<td><?php echo $v_plg['nama']; ?></td>
			<td><?php echo $v_plg['nik']; ?></td>
			<td><?php echo $v_plg['alamat']; ?></td>
			<td><?php echo ($v_plg['mstatus'] == 1) ? 'AKTIF' : 'NON AKTIF'; ?></td>
			<td><?php echo $v_plg['saldo_awal']; ?></td>
			<td><?php echo $v_plg['keterangan']; ?></td>
			<td class="text-center">
				<?php if ( $v_plg['mstatus'] == 0 ): ?>
					<button id="btn-add" type="button" class="btn btn-primary cursor-p" title="AKTIF" onclick="plg.load_form_status(this)" data-id="<?php echo $v_plg['id']; ?>" data-tipe="aktif"> 
						AKTIF
					</button>
				<?php else: ?>
					<button id="btn-add" type="button" class="btn btn-danger cursor-p" title="NON AKTIF" onclick="plg.load_form_status(this)" data-id="<?php echo $v_plg['id']; ?>" data-tipe="non_aktif"> 
						NON AKTIF
					</button>
				<?php endif ?>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
		<tr>
			<td class="text-center" colspan="8">Data tidak ditemukan.</td>
		</tr>
<?php endif ?>