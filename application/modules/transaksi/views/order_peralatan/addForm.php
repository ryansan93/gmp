<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-12 no-padding">
			<label class="label-control">Tanggal Order</label>
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
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-6 no-padding" style="padding-right: 5px;">
			<div class="col-xs-12 no-padding">
				<label class="label-control">Mitra</label>
			</div>
			<div class="col-xs-12 no-padding">
				<select class="form-control mitra" data-required="1">
					<option value="">-- Pilih Mitra --</option>
					<?php foreach ($mitra as $key => $value): ?>
						<option value="<?php echo $value['nomor']; ?>" data-kodeunit="<?php echo $value['kode_unit'] ?>" ><?php echo $value['nomor'].' | '.$value['nama']; ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="col-xs-6 no-padding" style="padding-left: 5px;">
			<div class="col-xs-12 no-padding">
				<label class="label-control">Supplier</label>
			</div>
			<div class="col-xs-12 no-padding">
				<select class="form-control supplier" data-required="1">
					<option value="">-- Pilih Supplier --</option>
					<?php foreach ($supplier as $key => $value): ?>
						<option value="<?php echo $value['nomor']; ?>"><?php echo $value['nomor'].' | '.$value['nama']; ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding">
			<label class="label-control">Grand Total</label>
		</div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right grand_total" placeholder="Grand Total" data-tipe="decimal" readonly data-required="1">
		</div>
	</div>
	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
	<div class="col-xs-12 no-padding">
		<small>
			<table class="table table-bordered tbl_data" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="col-xs-4">Barang</th>
						<th class="col-xs-2">Jumlah</th>
						<th class="col-xs-2">Harga</th>
						<th class="col-xs-2">Total</th>
						<th class="col-xs-2">Action</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<select class="form-control barang" data-required="1">
								<option value="">-- Pilih Barang --</option>
								<?php foreach ($barang as $key => $value): ?>
									<option value="<?php echo $value['kode']; ?>"><?php echo $value['kode'].' | '.$value['nama']; ?></option>
								<?php endforeach ?>
							</select>
						</td>
						<td>
							<input type="text" class="form-control text-right jumlah" placeholder="Jumlah" data-tipe="decimal" data-required="1" onchange="op.hitTotal(this)">
						</td>
						<td>
							<input type="text" class="form-control text-right harga" placeholder="Harga" data-tipe="decimal" data-required="1" onchange="op.hitTotal(this)">
						</td>
						<td>
							<input type="text" class="form-control text-right total" placeholder="Total" data-tipe="decimal" readonly>
						</td>
						<td>
							<div class="col-xs-12 no-padding">
								<div class="col-xs-6 no-padding" style="padding-right: 3px;">
									<button type="button" class="col-xs-12 btn btn-primary" onclick="op.addRow(this)"><i class="fa fa-plus"></i></button>
								</div>
								<div class="col-xs-6 no-padding" style="padding-left: 3px;">
									<button type="button" class="col-xs-12 btn btn-danger" onclick="op.removeRow(this)"><i class="fa fa-times"></i></button>
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
		<button type="button" class="col-xs-12 btn btn-primary" onclick="op.save()"><i class="fa fa-save"></i> Simpan</button>
	</div>
</div>