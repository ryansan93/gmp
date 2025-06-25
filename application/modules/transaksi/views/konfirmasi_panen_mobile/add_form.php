<div class="row detailed">
	<div class="col-lg-12 detailed">
		<hr style="padding-top: 0px; margin-top: 0px;">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Nama Mitra</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select id="select_mitra" data-placeholder="Pilih Mitra" class="form-control selectpicker" data-live-search="true" type="text" data-required="1">
						<option value="">Pilih Mitra</option>
						<?php foreach ($data_mitra as $k_dm => $v_dm): ?>
							<option data-tokens="<?php echo $v_dm['nama']; ?>" value="<?php echo $v_dm['nomor']; ?>"><?php echo strtoupper($v_dm['unit'].' | '.$v_dm['nama']); ?></option>
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

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Tanggal Panen</label>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="input-group date datetimepicker" name="tanggal" id="tanggal">
		                <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-required="1" data-tgl="<?php echo prev_date(date('Y-m-d')); ?>" disabled />
		                <span class="input-group-addon">
		                    <span class="glyphicon glyphicon-calendar"></span>
		                </span>
		            </div>
				</div>
			</div>

			<div class="col-xs-6" style="padding: 0px 5px 0px 0px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Umur</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control umur text-right" placeholder="Umur" data-tipe="integer" data-required="1" disabled>
				</div>
			</div>

			<div class="col-xs-6" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Populasi</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control populasi text-right" placeholder="Populasi" data-tipe="integer" data-required="1" disabled>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">BB Rata2</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right bb_rata2" placeholder="BB" data-tipe="decimal" data-required="1" disabled />
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Total Sekat</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right tot_sekat" placeholder="Total" data-tipe="decimal" data-required="1" readonly />
				</div>
			</div>
		</form>
	</div>
	<div class="col-xs-12 detailed"><br></div>
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<label class="control-label"><u>Data Sekat</u></label>
			</div>
			<div class="col-xs-12 no-padding">
				<table class="table table-bordered data_sekat" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-1">No</th>
							<th class="col-xs-4">Jumlah</th>
							<th class="col-xs-3">BB</th>
							<th class="col-xs-3">Action</th>
						</tr>
					</thead>
					<tbody>
						<tr class="v-center">
							<td class="text-center no_urut">1</td>
							<td><input type="text" class="form-control text-right jumlah" data-tipe="integer" onblur="kpm.hitung_total(this);" data-required="1" /></td>
							<td>
								<input type="text" class="form-control text-right bb" data-tipe="decimal" onblur="kpm.hitung_total(this);" data-required="1" />
							</td>
							<td>
								<div class="col-xs-12 no-padding">
									<div class="col-xs-6 no-padding">
										<button type="button" class="btn btn-primary" onclick="kpm.add_row(this)"><i class="fa fa-plus"></i></button>
									</div>
									<div class="col-xs-6 no-padding">
										<button type="button" class="btn btn-danger" onclick="kpm.remove_row(this)"><i class="fa fa-times"></i></button>
									</div>
								</div>
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td><b>Total</b></td>
							<td class="text-right tot_jumlah"><b>0</b></td>
							<td class="text-right tot_bb" colspan="2"><!-- <b>0,00</b> --></td>
							<!-- <td class="text-right"></td> -->
						</tr>
					</tfoot>
				</table>
			</div>
		</form>
	</div>
	<div class="col-lg-12 detailed"><hr></div>
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<button type="button" class="btn btn-primary pull-right col-xs-12" onclick="kpm.save(this)"><i class="fa fa-save"></i> Simpan</button>
			</div>
		</form>
	</div>
</div>
