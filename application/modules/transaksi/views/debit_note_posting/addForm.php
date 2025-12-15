<?php if ( $akses['a_submit'] == 1 ) { ?>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-2 no-padding" style="padding-right: 5px;">
			<div class="col-xs-12 no-padding"><label class="label-control">Tanggal Pakai DN</label></div>
			<div class="col-xs-12 no-padding">
				<div class="input-group date datetimepicker lock_date_fiskal" name="tanggal" id="Tanggal">
					<input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
					<span class="input-group-addon">
						<span class="glyphicon glyphicon-calendar"></span>
					</span>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-2 no-padding">
			<div class="col-xs-12 no-padding"><label class="label-control">Jenis DN</label></div>
			<div class="col-xs-12 no-padding">
				<select class="form-control jenis_dn" data-required="1">
					<?php foreach ($jenis_dn as $key => $value) { ?>
						<option value="<?php echo $key; ?>" data-jenis="<?php echo $value['jenis']; ?>"><?php echo strtoupper($value['nama']); ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-3 no-padding">
			<div class="col-xs-12 no-padding"><label class="label-control">No. DN</label></div>
			<div class="col-xs-12 no-padding">
				<select class="form-control dn" data-required="1">
				</select>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-3 no-padding">
			<div class="col-xs-12 no-padding"><label class="label-control">Nilai DN</label></div>
			<div class="col-xs-12 no-padding">
				<input type="text" class="form-control text-right nilai_dn" data-tipe="decimal" placeholder="Nilai DN" data-required="1" disabled>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-3 no-padding">
			<div class="col-xs-12 no-padding"><label class="label-control">Total Pakai DN</label></div>
			<div class="col-xs-12 no-padding">
				<input type="text" class="form-control text-right pakai_dn" data-tipe="decimal" placeholder="Total Pakai DN" data-required="1" disabled>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
	<div class="col-xs-12 no-padding">
		<small>
			<table class="table table-bordered" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="col-xs-4">No. SJ</th>
						<th class="col-xs-2">Nominal</th>
						<th class="col-xs-2">Sisa Tagihan</th>
						<th class="col-xs-3">Pakai DN</th>
						<th class="col-xs-1">Action</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<select class="form-control no_sj" data-required="1">
							</select>
						</td>
						<td class="text-right tagihan">0</td>
						<td class="text-right sisa">0</td>
						<td>
							<div class="col-xs-2 no-padding" style="padding-right: 5px;">
								<button type="button" class="col-xs-12 btn btn-default" onclick="dn.samakanSisaTagihan(this)"><i class="fa fa-arrow-right"></i></button>
							</div>
							<div class="col-xs-10 no-padding" style="padding-left: 5px;">
								<input type="text" class="col-xs-12 form-control pakai text-right" data-tipe="decimal" data-required="1" onblur="dn.hitTotalPakai()">
							</div>
						</td>
						<td>
							<div class="col-xs-12 no-padding">
								<div class="col-xs-6 no-padding" style="padding-right: 5px;">
									<button type="button" class="col-xs-12 btn btn-danger" onclick="dn.removeRow(this)"><i class="fa fa-minus"></i></button>
								</div>
								<div class="col-xs-6 no-padding" style="padding-left: 5px;">
									<button type="button" class="col-xs-12 btn btn-primary" onclick="dn.addRow(this)"><i class="fa fa-plus"></i></button>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</small>
	</div>
	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
	<div class="col-xs-12 no-padding">
		<button type="button" class="col-xs-12 btn btn-primary" onclick="dn.save()"><i class="fa fa-save"></i> Simpan</button>
	</div>
<?php } else { ?>
	<h4>CREDIT NOTE</h4>
<?php } ?>