<div class="row detailed">
	<div class="col-lg-12 header">
		<hr style="padding-top: 0px; margin-top: 0px;">
		<form role="form" class="form-horizontal header">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Nama Mitra</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select id="select_mitra" class="form-control selectpicker" data-live-search="true" type="text" data-required="1">
						<option value="">Pilih Mitra</option>
						<?php foreach ($data_mitra as $k_dm => $v_dm): ?>
							<option data-tokens="<?php echo $v_dm['nama']; ?>" value="<?php echo $v_dm['nomor']; ?>" data-kodeunit="<?php echo $v_dm['unit']; ?>"><?php echo strtoupper($v_dm['unit'].' | '.$v_dm['nama']); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">No. Reg</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select id="select_noreg" data-placeholder="Pilih No. Reg" class="form-control selectpicker" data-live-search="true" type="text" data-required="1" disabled>
						<option value="">Pilih Noreg</option>
					</select>
				</div>
			</div>

			<div class="col-xs-12 no-padding">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Tanggal Panen</label>
				</div>
				<div class="col-xs-12 no-padding">
					<!-- <div class="input-group date datetimepicker" name="tanggal" id="tanggal">
		                <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-required="1" disabled />
		                <span class="input-group-addon">
		                    <span class="glyphicon glyphicon-calendar"></span>
		                </span>
		            </div> -->
		            <select id="select_tgl_konfir" class="form-control selectpicker" data-live-search="true" type="text" data-required="1" disabled>
						<option value="">Pilih Tanggal</option>
					</select>
				</div>
			</div>

			<div class="col-xs-6 no-padding" style="padding: 0px 5px 0px 0px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Ekor</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right ekor_konfir" placeholder="Ekor" data-tipe="integer" data-required="1" disabled />
				</div>
			</div>

			<div class="col-xs-6 no-padding" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Tonase</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right tonase_konfir" placeholder="Tonase" data-tipe="decimal" data-required="1" readonly />
				</div>
			</div>

			<div class="col-xs-6" style="padding: 0px 5px 0px 0px; margin-bottom: 5px;">
				<div class="col-xs-6 no-padding">
					<label class="control-label">Umur</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control umur text-right" placeholder="Umur" data-tipe="integer" data-required="1" disabled>
				</div>
			</div>

			<div class="col-xs-6 no-padding" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Harga Dasar</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right uppercase harga_dasar" placeholder="Harga Dasar" data-tipe="integer" data-required="1">
				</div>
			</div>
		</form>
	</div>
	<div class="col-xs-12 detailed"><br></div>
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<label class="control-label"><u>Data Penjualan</u></label>
			</div>
			<div class="col-xs-12 no-padding">
				<small>
					<table class="table table-bordered data_plg" style="margin-bottom: 0px;">
						<thead>
							<tr>
								<th class="col-xs-1">No</th>
								<th class="col-xs-10">Pelanggan</th>
								<th class="col-xs-1">Action</th>
							</tr>
						</thead>
						<tbody>
							<tr class="v-center header">
								<td class="text-center no_urut">1</td>
								<td class="pelanggan">
									<select class="form-control select_pelanggan" data-live-search="true" type="text" data-required="1">
										<option value="">Pilih Pelanggan</option>
										<?php foreach ($data_pelanggan as $k_dp => $v_dp): ?>
											<option data-tokens="<?php echo strtoupper($v_dp['nama']).' ('.strtoupper(str_replace('Kab ', '', $v_dp['kab_kota'])).')'; ?>" value="<?php echo $v_dp['nomor']; ?>"><?php echo strtoupper($v_dp['nama']).' ('.strtoupper(str_replace('Kab ', '', $v_dp['kab_kota'])).')'; ?></option>
										<?php endforeach ?>
									</select>
								</td>								
								<td>
									<div class="col-xs-12 no-padding">
										<div class="col-xs-6 no-padding text-center">
											<button type="button" class="btn btn-add-row btn-primary" onclick="rm.add_row_plg(this)"><i class="fa fa-plus"></i></button>
										</div>
										<div class="col-xs-6 no-padding text-center">
											<button type="button" class="btn btn-remove-row btn-danger" onclick="rm.remove_row_plg(this)"><i class="fa fa-times"></i></button>
										</div>
									</div>
								</td>
							</tr>
							<tr class="v-center detail">
								<td colspan="3" style="background-color: #ccc;">
									<table class="table table-bordered tbl_detail" style="margin-bottom: 0px;">
										<thead>
											<tr>
												<th class="col-xs-2" style="background-color: #adb3ff;">Ekor</th>
												<th class="col-xs-3" style="background-color: #adb3ff;">Tonase</th>
												<th class="col-xs-1" style="background-color: #adb3ff;">BB</th>
												<th class="col-xs-2" style="background-color: #adb3ff;">Harga</th>
												<th class="col-xs-1" style="background-color: #adb3ff;">Action</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>
													<input type="text" class="form-control text-right ekor" data-tipe="integer" onblur="rm.hit_total();" data-required="1" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="7" placeholder="Ekor" />
												</td>
												<td>
													<input type="text" class="form-control text-right tonase" data-tipe="decimal" onblur="rm.hit_total();" data-required="1" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="10" placeholder="Tonase" />
												</td>
												<td>
													<input type="text" class="form-control text-right bb" data-tipe="decimal" data-required="1" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="5" placeholder="BB" disabled />
												</td>
												<td>
													<input type="text" class="form-control text-right harga" data-tipe="integer" data-required="1" style="height: fit-content; padding: 0px 3px 0px 3px;" maxlength="7" placeholder="Harga" />
												</td>
												<td>
													<div class="col-xs-12 no-padding">
														<div class="col-xs-6 no-padding text-center">
															<button type="button" class="btn btn-add-row btn-primary" onclick="rm.add_row(this)"><i class="fa fa-plus"></i></button>
														</div>
														<div class="col-xs-6 no-padding text-center">
															<button type="button" class="btn btn-remove-row btn-danger" onclick="rm.remove_row(this)"><i class="fa fa-times"></i></button>
														</div>
													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
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
				<label class="control-label text-left col-xs-6 no-padding tot_ekor" style="padding-top: 0px;">0</label>
			</div>
			<div class="col-xs-12 no-padding">
				<label class="control-label text-left col-xs-3 no-padding" style="padding-top: 0px;">Tonase (Kg)</label>
				<label class="control-label text-left col-xs-1" style="max-width: 5%; padding-top: 0px;">:</label>
				<label class="control-label text-left col-xs-6 no-padding tot_tonase" style="padding-top: 0px;">0</label>
			</div>
			<div class="col-xs-12 no-padding">
				<label class="control-label text-left col-xs-3 no-padding" style="padding-top: 0px;">BB (Kg)</label>
				<label class="control-label text-left col-xs-1" style="max-width: 5%; padding-top: 0px;">:</label>
				<label class="control-label text-left col-xs-6 no-padding tot_bb" style="padding-top: 0px;">0</label>
			</div>
		</form>
	</div>
	<div class="col-lg-12 detailed"><hr></div>
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<button type="button" class="btn btn-primary pull-right col-xs-12" onclick="rm.save(this)"><i class="fa fa-save"></i> Simpan</button>
			</div>
		</form>
	</div>
</div>
