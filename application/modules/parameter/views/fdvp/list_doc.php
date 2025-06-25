<?php // cetak_r($data); ?>
<?php if ( count($data) > 0 ) : ?>
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
		<tr class="search head <?php echo $red; ?>" data-id="<?php echo $v_doc['id'] ?>" >
			<td><?php echo $v_doc['kategori']; ?></td>
			<td class="kode"><?php echo $v_doc['kode']; ?></td>
			<td><?php echo $v_doc['nama']; ?></td>
			<td><?php echo $v_doc['kode_item']; ?></td>
			<!-- <td><?php echo $v_doc['supplier_not_pakan']['nama']; ?></td> -->
			<td><?php echo $v_doc['nama_supplier']; ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_doc['berat']); ?></td>
			<td class="text-right"><?php echo angkaRibuan($v_doc['isi']); ?></td>
			<td>
				<div class="col-sm-10 no-padding">
					<?php 
						// $last_log = $v_doc['logs'][ count($v_doc['logs']) - 1 ];
						// $keterangan = $last_log['deskripsi'] . ' pada ' . dateTimeFormat( $last_log['waktu'] );
						$keterangan = $v_doc['deskripsi'] . ' pada ' . dateTimeFormat( $v_doc['waktu'] );
						echo $keterangan;
					?>
				</div>
				<?php if ( $akses['a_edit'] == 1 ){ ?>
					<div class="col-sm-2 no-padding">
						<button id="btn-edit" type="button" data-href="doc" class="btn btn-primary cursor-p pull-right" title="EDIT" onclick="fdvp.edit_form(this)" data-id="<?php echo $v_doc['id']; ?>" data-resubmit="<?php echo 'EDIT'; ?>"> 
							<i class="fa fa-edit" aria-hidden="true"></i>
						</button>
					</div>
				<?php } ?>
				<?php if ( $akses['a_ack'] == 1 ){ ?>
					<?php if ( ($v_doc['g_status'] != getStatus('ack')) && ($v_doc['g_status'] != getStatus('approve')) ){ ?>
						<div class="col-sm-2 no-padding">
							<button id="btn-edit" type="button" data-href="doc" class="btn btn-primary cursor-p pull-right" title="ACK" onclick="fdvp.ack_doc(this)" data-id="<?php echo $v_doc['id']; ?>" data-resubmit="<?php echo $resubmit; ?>"> 
								<i class="fa fa-check" aria-hidden="true"></i>
							</button>
						</div>
					<?php } ?>
				<?php } ?>
			</td>
		</tr>
		<tr class="edit hide" data-aktif="n_aktif" data-id="<?php echo $v_doc['id'] ?>" data-status="<?php echo $v_doc['g_status'] ?>" data-version="<?php echo $v_doc['version'] ?>" >
			<td>
				<input value="<?php echo $v_doc['kategori']; ?>" class="form-control" type="text" id="kategori" data-required="1">
			</td>
			<td>
				<input value="<?php echo $v_doc['kode']; ?>" class="form-control" type="text" id="kode" readonly>
			</td>
			<td>
				<input value="<?php echo $v_doc['nama']; ?>" class="form-control" type="text" id="nama_doc" data-required="1">
			</td>
			<td>
				<input value="<?php echo $v_doc['kode_item']; ?>" class="form-control" type="text" id="kode_item_sup" data-required="1">
			</td>
			<td>
				<!-- <input class="form-control" type="text" id="supplier" data-required="1"> -->
				<select class="form-control" id="supplier" data-required="1">
					<option class="empty" value="">Pilih Supplier</option>
					<?php foreach ($list_supplier as $key => $v_supl): ?>
						<?php
							$selected = null;
							if ( $key == $v_doc['kode_supplier'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $v_supl['nip']; ?>" <?php echo $selected; ?> > <?php echo $v_supl['nama']; ?> </option>
					<?php endforeach ?>
				</select>
			</td>
			<td>
				<input value="<?php echo angkaDecimal($v_doc['berat']); ?>" class="form-control text-right" type="text" id="berat" data-required="1" data-tipe="decimal">
			</td>
			<td>
				<input value="<?php echo angkaRibuan($v_doc['isi']); ?>" class="form-control text-right" type="text" id="isi" data-required="1" data-tipe="integer">
			</td>
			<td>
				<div class="col-sm-10 no-padding">
					<?php 
						// $last_log = $v_doc['logs'][ count($v_doc['logs']) - 1 ];
						// $keterangan = $last_log['deskripsi'] . ' pada ' . dateTimeFormat( $last_log['waktu'] );
						$keterangan = $v_doc['deskripsi'] . ' pada ' . dateTimeFormat( $v_doc['waktu'] );
						echo $keterangan;
					?>
					<!-- <input value="<?php echo $keterangan; ?>" type="text" class="form-control" readonly> -->
				</div>
				<?php if ( $akses['a_edit'] == 1 ){ ?>
					<div class="col-sm-2 no-padding">
						<button id="btn-cancel" type="button" data-href="feed" class="btn btn-danger cursor-p pull-right" title="CANCEL" onclick="fdvp.cancel_edit(this)"> 
							<i class="fa fa-times" aria-hidden="true"></i>
						</button>
					</div>
				<?php } ?>
			</td>
		</tr>
	<?php endforeach ?>
<?php else: ?>
	<tr class="empty">
		<td class="text-center" colspan="8">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>