<?php if ( !empty($data)) : ?>
	<?php foreach ($data as $k_ekspedisi => $v_ekspedisi): ?>
		<?php 
			$resubmit = null;
			if ( $v_ekspedisi['status'] == 4 ) {
				$resubmit = $v_ekspedisi['id'];
			}
		?>

		<?php 
			$red = null;
			if ( $akses['a_ack'] == 1 ){
				$status = getStatus(1);
				if ( $v_ekspedisi['status'] == $status ) {
					$red = 'red';
				}
			} else if ( $akses['a_approve'] == 1 ){
				$status = getStatus(2);
				if ( $v_ekspedisi['status'] == $status ) {
					$red = 'red';
				}
			} else {

			}
		?>

		<tr class="search <?php echo $red; ?>">
			<td class="text-center" name="id_ekspedisi" data-nomor="<?php echo $v_ekspedisi['nip']; ?>">
				<a class="cursor-p" title="Detail Mitra" data-href="action" data-id="<?php echo $v_ekspedisi['id']; ?>" data-resubmit="<?php echo $resubmit; ?>" onclick="ekspedisi.changeTabActive(this)">
					<?php echo $v_ekspedisi['nip']; ?>
				</a> 
				<?php if ( $akses['a_submit'] == 1 ): ?>
					|| 
					<a class="cursor-p" title="Edit Mitra" data-href="action" data-id="<?php echo $v_ekspedisi['id']; ?>" data-resubmit="<?php echo 'EDIT'; ?>" onclick="ekspedisi.changeTabActive(this)" >
						<i class="fa fa-edit" aria-hidden="true"></i> 
					</a>
				<?php endif ?>
			</td>
			<td class="text-center"><?php echo strtoupper($v_ekspedisi['jenis']); ?></td>
			<td><?php echo $v_ekspedisi['nama']; ?></td>
			<td><?php echo $v_ekspedisi['nik']; ?></td>
			<td><?php echo $v_ekspedisi['alamat']; ?></td>
			<td><?php echo ($v_ekspedisi['mstatus'] == 1) ? 'AKTIF' : 'NON AKTIF'; ?></td>
			<td><?php echo $v_ekspedisi['saldo_awal']; ?></td>
			<td><?php echo $v_ekspedisi['keterangan']; ?></td>
			<td class="text-center">
				<?php if ( $v_ekspedisi['mstatus'] == 0 ): ?>
					<button id="btn-add" type="button" class="btn btn-primary cursor-p" title="AKTIF" onclick="ekspedisi.load_form_status(this)" data-id="<?php echo $v_ekspedisi['id']; ?>" data-tipe="aktif"> 
						AKTIF
					</button>
				<?php else: ?>
					<button id="btn-add" type="button" class="btn btn-danger cursor-p" title="NON AKTIF" onclick="ekspedisi.load_form_status(this)" data-id="<?php echo $v_ekspedisi['id']; ?>" data-tipe="non_aktif"> 
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