<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Nama</label></div>
	<div class="col-xs-12 no-padding">
		<input type="text" class="form-control text-left uppercase nama" data-required="1" placeholder="Nama" />
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Kode Voucher</label></div>
	<div class="col-xs-2 no-padding">
		<input type="text" class="form-control text-left uppercase kode_voucher" data-required="1" placeholder="Kode Voucher (MAX:8)" maxlength="8" />
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Fitur</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control fitur" multiple="multiple">
				<option value="">-- Pilih Fitur --</option>
				<?php foreach ($fitur as $key => $value) { ?>
					<option value="<?php echo $value['id_detfitur'] ?>"><?php echo $value['nama_fitur'].' | '.$value['nama_detfitur'] ?></option>
				<?php } ?>
			</select>
		</div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Peruntukan</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control peruntukan" data-required="1">
				<option>-- Pilih --</option>
				<option value="0">NON UNIT</option>
				<option value="1">UNIT</option>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<label class="checkbox-inline">
		<input type="checkbox" value="1" class="jurnal_manual cursor-p"><label class="control-label" style="margin-top: 0xp; padding-top: 0px;">Jurnal Manual</label>
	</label>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<label class="control-label">Detail Transaksi</label>
	<small>
		<table class="table table-bordered detail" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-4">Nama</th>
					<th class="col-xs-3">Sumber</th>
					<th class="col-xs-3">Tujuan</th>
					<th class="col-xs-1 text-center">Submit Periode</th>
					<th class="col-xs-1"></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<input type="text" class="form-control uppercase nama_detail" data-required="1" placeholder="Nama">
					</td>
					<td>
						<select class="form-control sumber" data-required="1">
							<option value="">-- Pilih COA --</option>
							<?php foreach ($coa as $key => $value): ?>
								<option value="<?php echo $value['coa'] ?>" data-nama="<?php echo $value['nama_coa']; ?>"><?php echo $value['coa'].' | '.$value['nama_coa']; ?></option>
							<?php endforeach ?>
						</select>
					</td>
					<td>
						<select class="form-control tujuan" data-required="1">
							<option value="">-- Pilih COA --</option>
							<?php foreach ($coa as $key => $value): ?>
								<option value="<?php echo $value['coa'] ?>" data-nama="<?php echo $value['nama_coa']; ?>"><?php echo $value['coa'].' | '.$value['nama_coa']; ?></option>
							<?php endforeach ?>
						</select>
					</td>
					<td class="text-center">
						<input type="checkbox" class="cursor-p submit_periode" target="check">
					</td>
					<td>
						<div class="col-xs-6 text-center no-padding">
							<button type="button" class="btn btn-primary" onclick="tj.addRow(this)"><i class="fa fa-plus"></i></button>
						</div>
						<div class="col-xs-6 text-center no-padding">
							<button type="button" class="btn btn-danger" onclick="tj.removeRow(this)"><i class="fa fa-times"></i></button>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</small>
</div>
<!-- <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<label class="control-label">Sumber / Tujuan</label>
	<small>
		<table class="table table-bordered sumber_tujuan" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-10">Nama</th>
					<th class="col-xs-2"></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<input type="text" class="form-control nama_detail" placeholder="Nama">
					</td>
					<td>
						<div class="col-xs-6 text-center no-padding">
							<button type="button" class="btn btn-primary" onclick="tj.addRow(this)"><i class="fa fa-plus"></i></button>
						</div>
						<div class="col-xs-6 text-center no-padding">
							<button type="button" class="btn btn-danger" onclick="tj.removeRow(this)"><i class="fa fa-times"></i></button>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</small>
</div> -->
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="tj.save()"><i class="fa fa-save"></i> Simpan</button>
</div>