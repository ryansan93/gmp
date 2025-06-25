<?php
	$readonly_input_harga = null;
	$unhide_input_harga = null;
	$hide_input_harga = 'hide';
	// cetak_r( $akses['a_khusus'] );
	if ( !empty($akses['a_khusus']) && in_array('input harga realisasi sj', $akses['a_khusus']) ) {
		$hide_input_harga = null;
		$unhide_input_harga = 'hide';
		$readonly_input_harga = 'readonly';
	}
?>

<div class="row detailed">
	<div class="col-lg-12 header">
		<hr style="padding-top: 0px; margin-top: 0px;">
		<form role="form" class="form-horizontal header">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Nama Mitra</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select id="select_mitra" class="form-control selectpicker" data-live-search="true" type="text" data-required="1" data-old="<?php echo $data['kode_unit']; ?>" <?php echo $readonly_input_harga; ?> >
						<option value="">Pilih Mitra</option>
						<?php foreach ($data_mitra as $k_dm => $v_dm): ?>
							<?php
								$selected = null;
								if ( $v_dm['nomor'] == $data['nomor'] ) {
									$selected = 'selected';
								}
							?>
							<option data-tokens="<?php echo $v_dm['nama']; ?>" value="<?php echo $v_dm['nomor']; ?>" data-kodeunit="<?php echo $v_dm['unit']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_dm['unit'].' | '.$v_dm['nama']); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">No. Reg</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select id="select_noreg" data-placeholder="Pilih No. Reg" class="form-control selectpicker" data-live-search="true" type="text" data-required="1" data-val="<?php echo $data['noreg']; ?>" data-old="<?php echo $data['noreg']; ?>" <?php echo $readonly_input_harga; ?> disabled>
						<option value="">Pilih Noreg</option>
					</select>
				</div>
			</div>

			<div class="col-xs-12 no-padding">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Tanggal Panen</label>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="input-group date datetimepicker" name="tanggal" id="tanggal">
		                <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data['tgl_panen']; ?>" data-old="<?php echo $data['tgl_panen']; ?>" disabled />
		                <span class="input-group-addon">
		                    <span class="glyphicon glyphicon-calendar"></span>
		                </span>
		            </div>
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
					<input type="text" class="form-control text-right tonase_konfir" placeholder="Tonase" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal($data['tonase']); ?>" readonly />
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

			<div class="col-xs-6 no-padding <?php echo $hide_input_harga; ?>" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
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
								<?php $readonly = ($v_data['edit_data'] == 0) ? 'readonly' : null; ?>
								<?php $disabled = ($v_data['edit_data'] == 0) ? 'disabled' : null; ?>

								<tr class="v-center header" data-iddetrpah="<?php echo $v_data['id_det_rpah']; ?>" data-noplg="<?php echo $v_data['no_pelanggan']; ?>" data-pelanggan="<?php echo $v_data['pelanggan']; ?>" data-do="<?php echo strtoupper($v_data['no_do']); ?>" data-sj="<?php echo strtoupper($v_data['no_sj']); ?>">
									<td class="text-center no_urut" style="vertical-align: top;"><?php echo $no; ?></td>
									<td class="no_do">
										<?php echo $v_data['pelanggan'].'<br>'.strtoupper($v_data['no_do']); ?>
									</td>
									<!-- <td style="vertical-align: top;">
										<div class="col-xs-12 no-padding" style="padding-left: 8px; padding-right: 8px;">
											<?php if ( !empty($v_data['lampiran']) ): ?>
								            	<a href="uploads/<?php echo $v_data['lampiran']; ?>" name="dokumen" class="text-right" target="_blank" style="padding-right: 10px;"><i class="fa fa-file"></i></a>
											<?php endif ?>
								            <label class="<?php echo $unhide_input_harga; ?>" style="margin-bottom: 0px;">
								                <input style="display: none;" placeholder="Dokumen" class="file_lampiran no-check" type="file" onchange="rsm.showNameFile(this)" data-name="no-name" data-allowtypes="jpg|jpeg|png|JPG|JPEG|PNG" data-old="<?php echo $v_data['lampiran']; ?>">
								                <i class="glyphicon glyphicon-paperclip cursor-p" title="Attachment SJ"></i> 
								            </label>
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
													<th class="col-xs-2 <?php echo $hide_input_harga; ?>" style="background-color: #adb3ff;">Harga</th>
													<th class="col-xs-1 <?php echo $unhide_input_harga; ?>" style="background-color: #adb3ff;">Action</th>
												</tr>
											</thead>
											<tbody>
												<?php if ( !empty($v_data['realisasi']) ): ?>
													<?php foreach ($v_data['realisasi'] as $k_realisasi => $v_realisasi): ?>
														<tr class="rpah_top" data-id="<?php echo $k_realisasi; ?>">
															<td class="text-right">
																<input type="text" class="form-control text-right ekor" data-tipe="integer" onblur="rsm.hit_total();" data-required="1" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="7" placeholder="Ekor" value="<?php echo angkaRibuan($v_realisasi['ekor']); ?>" <?php echo $readonly_input_harga; ?> <?php echo $readonly; ?> />
															</td>
															<td class="text-right">
																<input type="text" class="form-control text-right tonase" data-tipe="decimal" onblur="rsm.hit_total();" data-required="1" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="10" placeholder="Tonase" value="<?php echo angkaDecimal($v_realisasi['tonase']); ?>" <?php echo $readonly_input_harga; ?> <?php echo $readonly; ?> />
															</td>
															<td class="text-right">
																<input type="text" class="form-control text-right bb" data-tipe="decimal" data-required="1" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="5" placeholder="BB" value="<?php echo angkaDecimal($v_realisasi['bb']); ?>" disabled />
															</td>
															<td class="text-right <?php echo $hide_input_harga; ?>">
																<input type="text" class="form-control text-right harga" data-tipe="integer" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="7" placeholder="Harga" value="<?php echo angkaRibuan($v_realisasi['harga']); ?>" <?php echo $readonly; ?> />
															</td>
															<td rowspan="3" class="<?php echo $unhide_input_harga; ?>">
																<?php if ( empty($readonly) ): ?>
																	<div class="col-xs-12 no-padding">
																		<div class="col-xs-6 no-padding text-center">
																			<button type="button" class="btn btn-add-row btn-primary" onclick="rsm.add_row(this)"><i class="fa fa-plus"></i></button>
																		</div>
																		<div class="col-xs-6 no-padding text-center">
																			<button type="button" class="btn btn-remove-row btn-danger" onclick="rsm.remove_row(this)"><i class="fa fa-times"></i></button>
																		</div>
																	</div>
																<?php endif ?>
															</td>
														</tr>
														<tr class="rpah_bottom">
															<th style="background-color: #adb3ff;">Jenis Ayam</th>
															<td colspan="<?php echo (!empty($akses['a_khusus']) && in_array('input harga realisasi sj', $akses['a_khusus'])) ? 3 : 2; ?>">
																<select class="jenis_ayam form-control" style="height: fit-content; padding: 0px 3px 0px 3px;" data-required="1" <?php echo $readonly_input_harga; ?>  <?php echo $disabled; ?> >
																	<option value="">Pilih Jenis Ayam</option>
																	<?php foreach ($jenis_ayam as $k_ja => $v_ja): ?>
																		<?php
																			$selected = null;
																			if ( $k_ja == $v_realisasi['jenis_ayam'] ) {
																				$selected = 'selected';
																			}
																		?>
																		<option value="<?php echo $k_ja ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_ja); ?></option>
																	<?php endforeach ?>
																</select>
															</td>
														</tr>
														<tr class="rpah_bottom">
															<th style="background-color: #adb3ff;">No. Nota</th>
															<td colspan="<?php echo (!empty($akses['a_khusus']) && in_array('input harga realisasi sj', $akses['a_khusus'])) ? 3 : 2; ?>">
																<input type="text" class="form-control no_nota" placeholder="No. Nota" data-required="1" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="15" value="<?php echo $v_realisasi['no_nota'] ?>" <?php echo $readonly_input_harga; ?> <?php echo $disabled; ?>>
															</td>
														</tr>
														<?php $total_ekor += $v_realisasi['ekor']; $total_tonase += $v_realisasi['tonase']; ?>
													<?php endforeach ?>
												<?php else: ?>
													<?php if ( empty($readonly) ): ?>
														<tr class="rpah_top">
															<td class="text-right">
																<input type="text" class="form-control text-right ekor" data-tipe="integer" onblur="rsm.hit_total();" data-required="1" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="7" placeholder="Ekor" value="<?php echo angkaRibuan(0); ?>" <?php echo $readonly_input_harga; ?> />
															</td>
															<td class="text-right">
																<input type="text" class="form-control text-right tonase" data-tipe="decimal" onblur="rsm.hit_total();" data-required="1" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="10" placeholder="Tonase" value="<?php echo angkaDecimal(0); ?>" <?php echo $readonly_input_harga; ?> />
															</td>
															<td class="text-right">
																<input type="text" class="form-control text-right bb" data-tipe="decimal" data-required="1" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="5" placeholder="BB" value="<?php echo angkaDecimal(0); ?>" disabled />
															</td>
															<td class="text-right <?php echo $hide_input_harga; ?>">
																<input type="text" class="form-control text-right harga" data-tipe="integer" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="7" placeholder="Harga" value="<?php echo angkaRibuan(0); ?>" />
															</td>
															<td rowspan="2" class="<?php echo $unhide_input_harga; ?>">
																<div class="col-xs-12 no-padding">
																	<div class="col-xs-6 no-padding text-center">
																		<button type="button" class="btn btn-add-row btn-primary" onclick="rsm.add_row(this)"><i class="fa fa-plus"></i></button>
																	</div>
																	<div class="col-xs-6 no-padding text-center">
																		<button type="button" class="btn btn-remove-row btn-danger" onclick="rsm.remove_row(this)"><i class="fa fa-times"></i></button>
																	</div>
																</div>
															</td>
														</tr>
														<tr class="rpah_bottom">
															<th style="background-color: #adb3ff;">Jenis Ayam</th>
															<td colspan="<?php echo (!empty($akses['a_khusus']) && in_array('input harga realisasi sj', $akses['a_khusus'])) ? 3 : 2; ?>">
																<select class="jenis_ayam form-control" style="height: fit-content; padding: 0px 3px 0px 3px;" data-required="1" <?php echo $readonly_input_harga; ?>>
																	<option value="">Pilih Jenis Ayam</option>
																	<?php foreach ($jenis_ayam as $k_ja => $v_ja): ?>
																		<option value="<?php echo $k_ja ?>"><?php echo strtoupper($v_ja); ?></option>
																	<?php endforeach ?>
																</select>
															</td>
														</tr>
														<tr class="rpah_bottom">
															<th style="background-color: #adb3ff;">No. Nota</th>
															<td colspan="<?php echo (!empty($akses['a_khusus']) && in_array('input harga realisasi sj', $akses['a_khusus'])) ? 3 : 2; ?>">
																<input type="text" class="form-control no_nota" placeholder="No. Nota" data-required="1" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="15" <?php echo $readonly_input_harga; ?>>
															</td>
														</tr>
													<?php endif ?>
												<?php endif ?>
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
				<label class="control-label"><u>Total Pengajuan</u></label>
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
				<label class="control-label text-left col-xs-6 no-padding tot_ekor" style="padding-top: 0px; color: <?php echo $color; ?>" data-val="<?php echo $total_ekor; ?>"><?php echo angkaRibuan($total_ekor); ?></label>
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
				<label class="control-label text-left col-xs-6 no-padding tot_tonase" style="padding-top: 0px; color: <?php echo $color; ?>" data-val="<?php echo $total_tonase; ?>"><?php echo angkaDecimal($total_tonase); ?></label>
			</div>
			<div class="col-xs-12 no-padding">
				<label class="control-label text-left col-xs-3 no-padding" style="padding-top: 0px;">BB (Kg)</label>
				<label class="control-label text-left col-xs-1" style="max-width: 5%; padding-top: 0px;">:</label>
				<label class="control-label text-left col-xs-6 no-padding tot_bb" style="padding-top: 0px;"><?php echo ($total_ekor > 0&& $total_tonase > 0) ? angkaDecimal($total_tonase / $total_ekor) : 0; ?></label>
			</div>
		</form>
	</div>
	<div class="col-lg-12 detailed"><hr></div>
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<?php if ( !empty($hide_input_harga) ): ?>
				<div class="col-xs-6 no-padding" style="padding-right: 5px;">
					<button type="button" class="btn btn-primary pull-right col-xs-12" onclick="rsm.edit()"><i class="fa fa-save"></i> Simpan Perubahan</button>
				</div>
			<?php else: ?>
				<div class="col-xs-6 no-padding" style="padding-right: 5px;">
					<button type="button" class="btn btn-primary pull-right col-xs-12" onclick="rsm.edit()"><i class="fa fa-save"></i> Simpan Harga</button>
				</div>
			<?php endif ?>
			<div class="col-xs-6 no-padding" style="padding-left: 5px;">
				<button type="button" class="btn btn-danger pull-right col-xs-12" onclick="rsm.changeTabActive(this)" data-tglpanen="<?php echo $data['tgl_panen']; ?>" data-noreg="<?php echo $data['noreg']; ?>" data-nomor="<?php echo $data['nomor']; ?>" data-edit="" data-href="transaksi"><i class="fa fa-times"></i> Batal</button>
			</div>
		</form>
	</div>
</div>
