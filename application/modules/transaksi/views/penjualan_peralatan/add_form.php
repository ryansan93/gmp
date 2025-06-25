<div class="row detailed">
	<div class="col-lg-12 detailed">
		<hr style="padding-top: 0px; margin-top: 0px;">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">No. Transaksi</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control no_transaksi" readonly>
				</div>
			</div>

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

			<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding-right: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Tanggal</label>
				</div>
				<div class="col-xs-12 no-padding">
		            <div class="input-group date datetimepicker" name="tanggal" id="tanggal">
		                <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
		                <span class="input-group-addon">
		                    <span class="glyphicon glyphicon-calendar"></span>
		                </span>
		            </div>
				</div>
	        </div>

			<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding-left: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Total</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right total" data-tipe="decimal" placeholder="Total" data-required="1" readonly>
				</div>
			</div>

			<!-- <div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding-right: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Bayar</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right bayar" data-tipe="decimal" placeholder="Bayar" data-required="1"  onblur="pp.hit_total()">
				</div>
			</div>

			<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding-left: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Sisa Bayar</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right sisa_bayar" data-tipe="decimal" placeholder="Sisa Bayar" data-required="1" readonly>
				</div>
			</div> -->
		</form>
	</div>
	<div class="col-xs-12 detailed"><br></div>
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<table class="table table-bordered data_brg" style="margin-bottom: 0px;">
					<thead>
						<tr>
							<th class="col-xs-3">Nama Peralatan</th>
							<th class="col-xs-2">Jumlah</th>
							<th class="col-xs-2">Harga</th>
							<th class="col-xs-2">Sub Total</th>
							<th class="col-xs-1"></th>
						</tr>
					</thead>
					<tbody>
						<tr class="v-center">
							<td>
								<select data-placeholder="Pilih Peralatan" class="form-control select_peralatan" data-live-search="true" type="text" data-required="1">
									<option value="">Pilih Peralatan</option>
									<?php foreach ($data_peralatan as $k_dp => $v_dp): ?>
										<option data-tokens="<?php echo $v_dp['nama']; ?>" value="<?php echo $v_dp['kode']; ?>"><?php echo strtoupper($v_dp['nama']); ?></option>
									<?php endforeach ?>
								</select>
							</td>
							<td>
								<input type="text" class="form-control text-right jumlah" data-tipe="integer" placeholder="Jumlah" data-required="1" onblur="pp.hit_total()">
							</td>
							<td>
								<input type="text" class="form-control text-right harga" data-tipe="decimal" placeholder="Harga" data-required="1" onblur="pp.hit_total()">
							</td>
							<td class="text-right sub_total">0</td>
							<td>
								<div class="col-xs-12 no-padding">
									<div class="col-xs-6 no-padding">
										<button type="button" class="btn btn-primary" onclick="pp.add_row(this)"><i class="fa fa-plus"></i></button>
									</div>
									<div class="col-xs-6 no-padding">
										<button type="button" class="btn btn-danger" onclick="pp.remove_row(this)"><i class="fa fa-times"></i></button>
									</div>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</form>
	</div>
	<div class="col-lg-12 detailed"><hr></div>
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<button type="button" class="btn btn-primary pull-right col-xs-12" onclick="pp.save(this)"><i class="fa fa-save"></i> Simpan</button>
			</div>
		</form>
	</div>
</div>
