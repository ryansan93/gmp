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
					<div class="input-group date datetimepicker" name="tanggal" id="tanggal">
		                <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-required="1" data-tgl="<?php echo date('Y-m-d'); ?>" disabled />
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

			<div class="col-xs-6 no-padding hide" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Harga Dasar</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right uppercase harga_dasar" placeholder="Harga Dasar" data-tipe="integer" disabled>
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
					<table class="table table-bordered data_do" style="margin-bottom: 0px;">
						<thead>
							<tr>
								<th class="col-xs-1">No</th>
								<th class="col-xs-9">No. DO</th>
								<!-- <th class="col-xs-2">Lampiran</th> -->
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="3">Data tidak ditemukan.</td>
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
				<label class="control-label"><u>Total Penjualan</u></label>
			</div>
			<div class="col-xs-12 no-padding">
				<label class="control-label text-left col-xs-3 no-padding" style="padding-top: 0px;">Ekor</label>
				<label class="control-label text-left col-xs-1" style="max-width: 5%; padding-top: 0px;">:</label>
				<label class="control-label text-left col-xs-6 no-padding tot_ekor" style="padding-top: 0px;" data-val="0">0</label>
			</div>
			<div class="col-xs-12 no-padding">
				<label class="control-label text-left col-xs-3 no-padding" style="padding-top: 0px;">Tonase (Kg)</label>
				<label class="control-label text-left col-xs-1" style="max-width: 5%; padding-top: 0px;">:</label>
				<label class="control-label text-left col-xs-6 no-padding tot_tonase" style="padding-top: 0px;" data-val="0">0</label>
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
				<button type="button" class="btn btn-primary pull-right col-xs-12" onclick="rsm.save(this)"><i class="fa fa-save"></i> Simpan</button>
			</div>
		</form>
	</div>
</div>
