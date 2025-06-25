<div class="col-md-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-md-12 no-padding"><label class="control-label">Pegawai</label></div>
	<div class="col-md-12 no-padding">
		<select class="form-control pegawai">
			<option value="">-- Pilih Pegawai --</option>
			<?php foreach ($pegawai as $k => $val): ?>
				<option value="<?php echo $val['nik']; ?>" data-namaunit="<?php echo strtoupper($val['list_nama_unit']); ?>" data-kodeunit="<?php echo strtoupper($val['list_kode_unit']); ?>" data-jabatan="<?php echo $val['jabatan']; ?>"><?php echo strtoupper($val['jabatan']).' | '.strtoupper($val['nama']); ?></option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<div class="col-md-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-md-12 no-padding"><label class="control-label">Unit</label></div>
	<div class="col-md-12 no-padding">
		<textarea class="form-control"  disabled></textarea>
		<!-- <div class="form-control unit">
			<span style="color: #aaa;">Unit Pegawai</span>
		</div> -->
	</div>
</div>
<div class="col-md-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-md-12 no-padding"><label class="control-label">Tgl Berlaku</label></div>
	<div class="col-md-12 no-padding">
		<div class="input-group date" id="tglBerlaku">
	        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
</div>
<div class="col-md-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-md-12 no-padding"><label class="control-label">Gaji</label></div>
	<div class="col-md-12 no-padding">
		<input type="text" class="form-control text-right gaji" data-tipe="decimal" data-required="1" placeholder="Gaji">
	</div>
</div>
<div class="col-md-12 no-padding">
	<hr style="margin-top: 10px; margin-bottom: 10px;">
</div>
<div class="col-md-12 no-padding" style="margin-bottom: 10px;">
	<div class="panel-body no-padding">
		<fieldset>
			<legend>
				<div class="col-sm-8 no-padding">
					Insentif
				</div>
			</legend>
			<table class="table table-bordered tbl_insentif" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="col-xs-6">Nama</th>
						<th class="col-xs-4">Nominal</th>
						<th class="col-xs-2"></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<input type="text" class="form-control nama" placeholder="Nama">
						</td>
						<td>
							<input type="text" class="form-control text-right nominal" placeholder="Nominal" data-tipe="decimal">
						</td>
						<td>
							<div class="col-xs-6">
								<button type="button" class="btn btn-primary col-xs-12" onclick="gaji.addRow(this)"><i class="fa fa-plus"></i></button>
							</div>
							<div class="col-xs-6">
								<button type="button" class="btn btn-danger col-xs-12" onclick="gaji.removeRow(this)"><i class="fa fa-times"></i></button>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	</div>
</div>
<div class="col-md-12 no-padding" style="margin-bottom: 10px;">
	<div class="panel-body no-padding">
		<fieldset>
			<legend>
				<div class="col-sm-8 no-padding">
					Potongan
				</div>
			</legend>
			<table class="table table-bordered tbl_potongan" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="col-xs-6">Nama</th>
						<th class="col-xs-4">Nominal</th>
						<th class="col-xs-2"></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<input type="text" class="form-control nama" placeholder="Nama">
						</td>
						<td>
							<input type="text" class="form-control text-right nominal" placeholder="Nominal" data-tipe="decimal">
						</td>
						<td>
							<div class="col-xs-6">
								<button type="button" class="btn btn-primary col-xs-12" onclick="gaji.addRow(this)"><i class="fa fa-plus"></i></button>
							</div>
							<div class="col-xs-6">
								<button type="button" class="btn btn-danger col-xs-12" onclick="gaji.removeRow(this)"><i class="fa fa-times"></i></button>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	</div>
</div>
<div class="col-md-12 no-padding">
	<hr style="margin-top: 10px; margin-bottom: 10px;">
</div>
<div class="col-md-12 no-padding">
	<button type="button" class="col-md-12 btn btn-primary" onclick="gaji.save()"><i class="fa fa-save"></i> Simpan</button>
</div>