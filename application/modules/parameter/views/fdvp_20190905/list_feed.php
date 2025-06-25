<?php // cetak_r($data); ?>
<?php if ( count($data) > 0 ): ?>
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

		<tr class="search head <?php echo $red; ?>" data-id="<?php echo $v_pakan['id'] ?>" >
			<td><?php echo $kategori[ $v_pakan['kategori'] ]; ?></td>
			<td class="kode"><?php echo $v_pakan['kode']; ?></td>
			<td><?php echo $v_pakan['nama']; ?></td>
			<td><?php echo $v_pakan['kode_item']; ?></td>
			<td>
				<?php
					$idx = 0;
					foreach ($v_pakan['supplier'] as $k_supl => $v_supl) {
						echo strtoupper($v_supl['data_supplier']['nama']);
						if ( $idx < count($v_pakan['supplier']) ) {
							echo '<br>';
						}
						$idx;
					}
				?>
			</td>
			<td class="text-right"><?php echo $v_pakan['umur']; ?></td>
			<td class="text-right"><?php echo angkaDecimal($v_pakan['berat']); ?></td>
			<td><?php echo $bentuk[ $v_pakan['bentuk'] ]; ?></td>
			<td class="text-right"><?php echo $v_pakan['simpan']; ?></td>
			<td>
				<div class="col-sm-10 no-padding">
					<?php 
						$last_log = $v_pakan['logs'][ count($v_pakan['logs']) - 1 ];
						$keterangan = $last_log['deskripsi'] . ' pada ' . dateTimeFormat( $last_log['waktu'] );
						echo $keterangan;
					?>
				</div>
				<?php if ( $akses['a_edit'] == 1 ){ ?>
					<div class="col-sm-2 no-padding">
						<button id="btn-edit" type="button" data-href="feed" class="btn btn-primary cursor-p pull-right" title="EDIT" onclick="fdvp.edit_form(this)" data-id="<?php echo $v_pakan['id']; ?>" data-resubmit="<?php echo 'EDIT'; ?>"> 
							<i class="fa fa-edit" aria-hidden="true"></i>
						</button>
					</div>
				<?php } ?>
				<?php if ( $akses['a_ack'] == 1 ){ ?>
					<?php if ( ($v_pakan['g_status'] != getStatus('ack')) && ($v_pakan['g_status'] != getStatus('approve')) ){ ?>
						<div class="col-sm-2 no-padding">
							<button id="btn-edit" type="button" data-href="feed" class="btn btn-primary cursor-p pull-right" title="ACK" onclick="fdvp.ack_feed(this)" data-id="<?php echo $v_pakan['id']; ?>" data-resubmit="<?php echo $resubmit; ?>"> 
								<i class="fa fa-check" aria-hidden="true"></i>
							</button>
						</div>
					<?php } ?>
				<?php } ?>
			</td>
		</tr>

		<tr class="edit hide" data-aktif="n_aktif" data-id="<?php echo $v_pakan['id'] ?>" data-status="<?php echo $v_pakan['g_status'] ?>" data-version="<?php echo $v_pakan['version'] ?>" >
			<td>
				<select class="form-control" id="kategori" data-required="1">
					<option class="empty" value="">Pilih Kategori</option>
					<?php foreach ($kategori as $key => $value): ?>
						<?php
							$selected = null;
							if ( $key == $v_pakan['kategori'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $key; ?>" <?php echo $selected; ?> ><?php echo $value; ?></option>
					<?php endforeach ?>
				</select>
			</td>
			<td>
				<input value="<?php echo $v_pakan['kode']; ?>" class="form-control" type="text" id="kode" readonly>
			</td>
			<td>
				<input value="<?php echo $v_pakan['nama']; ?>" class="form-control" type="text" id="nama_pakan" data-required="1">
			</td>
			<td>
				<input value="<?php echo $v_pakan['kode_item']; ?>" class="form-control" type="text" id="kode_item_sup" data-required="1">
			</td>
			<td>
				<select class="supplier" name="supplier[]" multiple="multiple" width="100%" placeholder="Pilih Supplier">
					<?php foreach ($list_supplier as $key => $v_supl): ?>
						<?php
							$selected = null;
							foreach ($v_pakan['supplier'] as $k_sp => $v_sp) {
								if ( $v_supl['id'] == $v_sp['id_supl'] ) {
									$selected = 'selected';

									break;
								}
							}
						?>
						<option value="<?php echo $v_supl['nip']; ?>" <?php echo $selected; ?> > <?php echo $v_supl['nama']; ?> </option>
					<?php endforeach ?>
				</select>
			</td>
			<td>
				<input value="<?php echo angkaRibuan($v_pakan['umur']); ?>" class="form-control text-right" type="text" id="umur" data-required="1" data-tipe="integer">
			</td>
			<td>
				<input value="<?php echo angkaDecimal($v_pakan['berat']); ?>" class="form-control text-right" type="text" id="berat_pakan" data-required="1" data-tipe="decimal">
			</td>
			<td>
				<select class="form-control" id="bentuk_pakan" data-required="1">
					<option class="empty" value="">Pilih Bentuk Pakan</option>
					<?php foreach ($bentuk as $key => $value): ?>
						<?php
							$selected = null;
							if ( $key == $v_pakan['bentuk'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $key; ?>" <?php echo $selected; ?> ><?php echo $value; ?></option>
					<?php endforeach ?>
				</select>
			</td>
			<td>
				<input value="<?php echo angkaRibuan($v_pakan['simpan']); ?>" class="form-control text-right" type="text" id="masa_simpan" data-required="1" data-tipe="integer">
			</td>
			<td>
				<div class="col-sm-10 no-padding">
					<?php 
						$last_log = $v_pakan['logs'][ count($v_pakan['logs']) - 1 ];
						$keterangan = $last_log['deskripsi'] . ' pada ' . dateTimeFormat( $last_log['waktu'] );
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
		<td class="text-center" colspan="10">Data tidak ditemukan.</td>
	</tr>
<?php endif ?>