<?php
	$readonly_input_harga = null;
	$unhide_input_harga = null;
	$hide_input_harga = 'hide';
	if ( !empty($akses['a_khusus']) && in_array('input harga realisasi sj', $akses['a_khusus']) ) {
		$hide_input_harga = null;
		$unhide_input_harga = 'hide';
		$readonly_input_harga = 'readonly';
	}
?>

<div class="row detailed">
	<div class="col-xs-12 header">
		<hr style="padding-top: 0px; margin-top: 0px;">
		<form role="form" class="form-horizontal header">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-4 no-padding">
					<label class="control-label">Nama Mitra</label>
				</div>
				<div class="col-xs-8 no-padding">
					<label class="control-label">: <?php echo strtoupper($data['mitra']); ?></label>
				</div>
			</div>

			<div class="col-xs-12 no-padding noreg" data-val="<?php echo $data['noreg']; ?>" style="margin-bottom: 5px;">
				<div class="col-xs-4 no-padding">
					<label class="control-label">No. Reg</label>
				</div>
				<div class="col-xs-8 no-padding">
					<label class="control-label">: <?php echo strtoupper($data['noreg']); ?></label>
				</div>
			</div>

			<div class="col-xs-12 no-padding tgl_panen" data-val="<?php echo $data['tgl_panen']; ?>">
				<div class="col-xs-4 no-padding">
					<label class="control-label">Tanggal Panen</label>
				</div>
				<div class="col-xs-8 no-padding">
					<label class="control-label">: <?php echo strtoupper(tglIndonesia($data['tgl_panen'], '-', ' ')); ?></label>
				</div>
			</div>

			<div class="col-xs-6 no-padding" style="padding: 0px 5px 0px 0px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Ekor</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right ekor_konfir" placeholder="Ekor" data-tipe="integer" data-required="1" value="<?php echo angkaRibuan($data['ekor']); ?>" disabled />
				</div>
			</div>

			<div class="col-xs-6 no-padding" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Tonase</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right tonase_konfir" placeholder="Tonase" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal($data['tonase']); ?>" disabled />
				</div>
			</div>

			<div class="col-xs-6" style="padding: 0px 5px 0px 0px; margin-bottom: 5px;">
				<div class="col-xs-6 no-padding">
					<label class="control-label">Umur</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control umur text-right" placeholder="Umur" data-tipe="integer" data-required="1" value="<?php echo $data['umur']; ?>" disabled>
				</div>
			</div>

			<div class="col-xs-6 no-padding <?php echo (!empty($akses['a_khusus']) && in_array('input harga realisasi sj', $akses['a_khusus'])) ? null : 'hide'; ?>" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Harga Dasar</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right uppercase harga_dasar" placeholder="Harga Dasar" data-tipe="integer" value="<?php echo angkaRibuan($data['harga_dasar']); ?>" disabled>
				</div>
			</div>
		</form>
	</div>
	<div class="col-xs-12 detailed"><br></div>
	<?php $total_ekor = 0; $total_tonase = 0; ?>
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<label class="control-label"><u>Data Penjualan</u></label>
			</div>
			<div class="col-xs-12 no-padding">
				<small>
					<table class="table table-bordered data_do" style="margin-bottom: 0px;">
						<thead>
							<tr>
								<th class="col-xs-1">No</th>
								<th class="col-xs-9">No. DO</th>
								<!-- <th class="col-xs-2">Lampiran</th> -->
							</tr>
						</thead>
						<tbody>
							<?php $no = 1; ?>
							<?php foreach ($data['detail'] as $k_data => $v_data): ?>
								<tr class="v-center header" data-iddetrpah="<?php echo $v_data['id_det_rpah']; ?>" data-noplg="<?php echo $v_data['no_pelanggan']; ?>" data-pelanggan="<?php echo $v_data['pelanggan']; ?>" data-do="<?php echo strtoupper($v_data['no_do']); ?>" data-sj="<?php echo strtoupper($v_data['no_sj']); ?>">
									<td class="text-center no_urut" style="vertical-align: top;"><?php echo $no; ?></td>
									<td class="no_do">
										<?php echo $v_data['pelanggan'].'<br>'.strtoupper($v_data['no_do']); ?>
									</td>
									<!-- <td class="text-center" style="vertical-align: top;">
										<div class="col-xs-12 no-padding" style="padding-left: 8px; padding-right: 8px;">
								            <a href="uploads/<?php echo $v_data['lampiran']; ?>" name="dokumen" class="text-right" target="_blank" style="padding-right: 10px;"><i class="fa fa-file"></i></a>
								        </div>
									</td> -->
								</tr>
								<tr class="v-center detail">
									<td colspan="3" style="background-color: #ccc;">
										<table class="table table-bordered tbl_detail" style="margin-bottom: 0px;">
											<thead>
												<tr>
													<th class="col-xs-2" style="background-color: #adb3ff;">Ekor</th>
													<th class="col-xs-3" style="background-color: #adb3ff;">Tonase</th>
													<th class="col-xs-1" style="background-color: #adb3ff;">BB</th>
													<th class="col-xs-2 <?php echo (!empty($akses['a_khusus']) && in_array('input harga realisasi sj', $akses['a_khusus'])) ? null : 'hide'; ?>" style="background-color: #adb3ff;">Harga</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($v_data['realisasi'] as $k_realisasi => $v_realisasi): ?>
													<tr class="rpah_top">
														<td class="text-right">
															<?php echo angkaRibuan($v_realisasi['ekor']); ?>
														</td>
														<td class="text-right">
															<?php echo angkaDecimal($v_realisasi['tonase']); ?>
														</td>
														<td class="text-right">
															<?php echo angkaDecimal($v_realisasi['bb']); ?>
														</td>
														<td class="text-right <?php echo (!empty($akses['a_khusus']) && in_array('input harga realisasi sj', $akses['a_khusus'])) ? null : 'hide'; ?>">
															<?php echo angkaRibuan($v_realisasi['harga']); ?>
														</td>
													</tr>
													<tr class="rpah_bottom">
														<th style="background-color: #adb3ff;">Jenis Ayam</th>
														<td colspan="3">
															<?php 
																$_jenis_ayam = null;
																foreach ($jenis_ayam as $k_ja => $v_ja) {
																	if ( $k_ja == $v_realisasi['jenis_ayam'] ) {
																		$_jenis_ayam = $v_ja;
																	}
																}

																echo strtoupper($_jenis_ayam);
															?>
														</td>
													</tr>
													<tr class="rpah_bottom">
														<th style="background-color: #adb3ff;">No. Nota</th>
														<td colspan="3">
															<?php echo !empty($v_realisasi['no_nota']) ? strtoupper($v_realisasi['no_nota']) : '-'; ?>
														</td>
													</tr>
													<?php $total_ekor += $v_realisasi['ekor']; $total_tonase += $v_realisasi['tonase']; ?>
												<?php endforeach ?>
											</tbody>
										</table>
									</td>
								</tr>
								<?php $no++; ?>
							<?php endforeach ?>
						</tbody>
					</table>
				</small>
			</div>
		</form>
	</div>
	<div class="col-xs-12 detailed"><br></div>
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<label class="control-label"><u>Total Penjualan</u></label>
			</div>
			<div class="col-xs-12 no-padding">
				<label class="control-label text-left col-xs-3 no-padding" style="padding-top: 0px;">Ekor</label>
				<label class="control-label text-left col-xs-1" style="max-width: 5%; padding-top: 0px;">:</label>
				<?php
					$color = '#333';
					if ( $total_ekor > $data['ekor'] ) {
						$color = 'red';
					}
				?>
				<label class="control-label text-left col-xs-6 no-padding tot_ekor" style="padding-top: 0px; color: <?php echo $color; ?>"><?php echo angkaRibuan($total_ekor); ?></label>
			</div>
			<div class="col-xs-12 no-padding">
				<label class="control-label text-left col-xs-3 no-padding" style="padding-top: 0px;">Tonase (Kg)</label>
				<label class="control-label text-left col-xs-1" style="max-width: 5%; padding-top: 0px;">:</label>
				<?php
					$color = '#333';
					if ( $total_tonase > $data['tonase'] ) {
						$color = 'red';
					}
				?>
				<label class="control-label text-left col-xs-6 no-padding tot_tonase" style="padding-top: 0px; color: <?php echo $color; ?>"><?php echo angkaDecimal($total_tonase); ?></label>
			</div>
			<div class="col-xs-12 no-padding">
				<label class="control-label text-left col-xs-3 no-padding" style="padding-top: 0px;">BB (Kg)</label>
				<label class="control-label text-left col-xs-1" style="max-width: 5%; padding-top: 0px;">:</label>
				<label class="control-label text-left col-xs-6 no-padding tot_bb" style="padding-top: 0px;"><?php echo ($total_ekor > 0&& $total_tonase > 0) ? angkaDecimal($total_tonase / $total_ekor) : 0; ?></label>
			</div>
		</form>
	</div>
	<div class="col-xs-12 detailed"><hr></div>
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<?php if ( $edit_data == 1 ): ?>
				<?php if ( !empty($hide_input_harga) ): ?>
					<div class="col-xs-6 no-padding" style="padding-right: 5px;">
						<?php if ( $akses['a_edit'] == 1 ) : ?>
							<button type="button" class="btn btn-primary col-xs-12" onclick="rsm.changeTabActive(this)" data-tglpanen="<?php echo $data['tgl_panen']; ?>" data-noreg="<?php echo $data['noreg']; ?>" data-nomor="<?php echo $data['nomor']; ?>" data-edit="edit" data-href="transaksi"><i class="fa fa-edit"></i> Edit</button>
						<?php endif ?>
					</div>
					<?php if ( $hapus_data == 1 ): ?>
						<div class="col-xs-6 no-padding" style="padding-left: 5px;">
							<?php if ( $akses['a_delete'] == 1 ) : ?>
								<button type="button" class="btn btn-danger col-xs-12" onclick="rsm.delete()"><i class="fa fa-trash"></i> Hapus</button>
							<?php endif ?>
						</div>
					<?php endif ?>
				<?php else: ?>
					<div class="col-xs-12 no-padding">
						<?php if ( $akses['a_edit'] == 1 ) : ?>
							<button type="button" class="btn btn-primary col-xs-12" onclick="rsm.changeTabActive(this)" data-tglpanen="<?php echo $data['tgl_panen']; ?>" data-noreg="<?php echo $data['noreg']; ?>" data-nomor="<?php echo $data['nomor']; ?>" data-edit="edit" data-href="transaksi"><i class="fa fa-edit"></i> Edit Harga</button>
						<?php endif ?>
					</div>
				<?php endif ?>
			<?php endif ?>
		</form>
	</div>
</div>
