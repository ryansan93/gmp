<?php if ( !empty($data)) : ?>
	<?php foreach ($data as $k_voadip => $v_voadip): ?>
		<?php 
			$resubmit = null;
			if ( $v_voadip['g_status'] == 4 ) {
				$resubmit = $v_voadip['id'];
			}
		?>

		<?php 
			$red = null;
			if ( $akses['a_ack'] == 1 ){
				$status = getStatus('submit');
				if ( $v_voadip['g_status'] == $status ) {
					$red = 'red';
				}
			} else if ( $akses['a_approve'] == 1 ){
				$status = getStatus('ack');
				if ( $v_voadip['g_status'] == 2 ) {
					$red = 'red';
				}
			} else {

			}
		?>

		<tr class="search head <?php echo $red; ?>" data-id="<?php echo $v_voadip['id'] ?>" >
			<td><?php echo strtoupper($v_voadip['kategori']); ?></td>
			<td class="kode"><?php echo $v_voadip['kode']; ?></td>
			<td><?php echo $v_voadip['nama']; ?></td>
			<td><?php echo $v_voadip['kode_item']; ?></td>
			<!-- <td><?php echo $v_voadip['supplier_not_pakan']['nama']; ?></td> -->
			<td><?php echo $v_voadip['nama_supplier']; ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_voadip['berat']); ?></td>
			<td class="text-right"><?php echo angkaRibuan($v_voadip['isi']); ?></td>
			<td class="text-right"><?php echo strtoupper($v_voadip['satuan']); ?></td>
			<td class="text-right"><?php echo strtoupper($v_voadip['bentuk']); ?></td>
			<td class="text-right"><?php echo angkaRibuan($v_voadip['simpan']); ?></td>
			<td>
				<div class="col-sm-10 no-padding">
					<?php 
						// $last_log = $v_voadip['logs'][ count($v_voadip['logs']) - 1 ];
						// $keterangan = $last_log['deskripsi'] . ' pada ' . dateTimeFormat( $last_log['waktu'] );
						$keterangan = $v_voadip['deskripsi'] . ' pada ' . dateTimeFormat( $v_voadip['waktu'] );
						echo $keterangan;
					?>
				</div>
				<?php if ( $akses['a_edit'] == 1 ){ ?>
					<div class="col-sm-2 no-padding">
						<button id="btn-edit" type="button" data-href="doc" class="btn btn-primary cursor-p pull-right" title="EDIT" onclick="fdvp.edit_form(this)" data-id="<?php echo $v_voadip['id']; ?>" data-resubmit="<?php echo 'EDIT'; ?>"> 
							<i class="fa fa-edit" aria-hidden="true"></i>
						</button>
					</div>
				<?php } ?>
				<?php if ( $akses['a_ack'] == 1 ){ ?>
					<?php if ( ($v_voadip['g_status'] != getStatus('ack')) && ($v_voadip['g_status'] != getStatus('approve')) ){ ?>
						<div class="col-sm-2 no-padding">
							<button id="btn-edit" type="button" data-href="voadip" class="btn btn-primary cursor-p pull-right" title="ACK" onclick="fdvp.ack_voadip(this)" data-id="<?php echo $v_voadip['id']; ?>" data-resubmit="<?php echo $resubmit; ?>"> 
								<i class="fa fa-check" aria-hidden="true"></i>
							</button>
						</div>
					<?php } ?>
				<?php } ?>
			</td>
		</tr>
		<tr class="edit hide" data-aktif="n_aktif" data-id="<?php echo $v_voadip['id'] ?>" data-status="<?php echo $v_voadip['g_status'] ?>" data-version="<?php echo $v_voadip['version'] ?>" >
			<td>
				<select class="form-control" id="kategori" data-required="1">
					<option class="empty" value="">Pilih Kategori</option>
					<?php foreach ($kategori as $key => $value): ?>
						<?php
							$selected = null;
							if ( $key == $v_voadip['kategori'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $key; ?>" <?php echo $selected; ?> ><?php echo $value; ?></option>
					<?php endforeach ?>
				</select>
			</td>
			<td>
				<input value="<?php echo $v_voadip['kode']; ?>" class="form-control" type="text" id="kode" readonly>
			</td>
			<td>
				<input value="<?php echo $v_voadip['nama']; ?>" class="form-control" type="text" id="nama_voadip" data-required="1">
			</td>
			<td>
				<input value="<?php echo $v_voadip['kode_item']; ?>" class="form-control" type="text" id="kode_item_sup" data-required="1">
			</td>
			<td>
				<select class="form-control" id="supplier" data-required="1">
					<option class="empty" value="">Pilih Supplier</option>
					<?php foreach ($list_supplier as $key => $v_supl): ?>
						<?php
							$selected = null;
							if ( $key == $v_voadip['kode_supplier'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $v_supl['nip']; ?>" <?php echo $selected; ?> > <?php echo $v_supl['nama']; ?> </option>
					<?php endforeach ?>
				</select>
			</td>
			<td>
				<input value="<?php echo angkaDecimal($v_voadip['berat']); ?>" class="form-control text-right" type="text" id="dosis" data-tipe="decimal" data-required="1">
			</td>
			<td>
				<input value="<?php echo angkaRibuan($v_voadip['isi']); ?>" class="form-control text-right" type="text" id="isi" data-tipe="integer" data-required="1">
			</td>
			<td>
				<input value="<?php echo $v_voadip['satuan']; ?>" class="form-control" type="text" id="satuan" data-required="1">
			</td>
			<td>
				<select class="form-control" id="bentuk_voadip" data-required="1">
					<option class="empty" value="">Pilih Bentuk</option>
					<?php foreach ($bentuk as $key => $value): ?>
						<?php
							$selected = null;
							if ( $key == $v_voadip['bentuk'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $key ?>" <?php echo $selected; ?> ><?php echo $value; ?></option>
					<?php endforeach ?>
				</select>
			</td>
			<td>
				<input value="<?php echo angkaRibuan($v_voadip['simpan']); ?>" class="form-control text-right" type="text" id="masa_simpan" data-tipe="integer" data-required="1">
			</td>
			<td>
				<div class="col-sm-10 no-padding">
					<?php 
						// $last_log = $v_voadip['logs'][ count($v_voadip['logs']) - 1 ];
						// $keterangan = $last_log['deskripsi'] . ' pada ' . dateTimeFormat( $last_log['waktu'] );
						$keterangan = $v_voadip['deskripsi'] . ' pada ' . dateTimeFormat( $v_voadip['waktu'] );
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
		<td class="text-center" colspan="11">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>