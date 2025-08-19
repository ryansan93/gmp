<!-- <div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-4 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Tipe CN</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control jurnal_trans" data-required="1">
				<option value="">-- Pilih Transaksi Jurnal --</option>
				<?php foreach ($jurnal_trans as $key => $value): ?>
					<option value="<?php echo $value['kode']; ?>"><?php echo $value['nama']; ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div> -->
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-2 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Jenis CN</label></div>
		<div class="col-xs-12 no-padding">
			<!-- <select class="form-control jenis_cn" data-required="1">
				<?php foreach ($jenis_cn as $key => $value) { ?>
					<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
				<?php } ?>
			</select> -->

			<select class="form-control jurnal_trans" data-required="1">
				<option value="">-- Pilih Transaksi Jurnal --</option>
				<?php foreach ($jurnal_trans as $key => $value): ?>
					<option value="<?php echo $value['kode']; ?>" data-kodevoucher="<?php echo $value['kode_voucher']; ?>"><?php echo $value['nama']; ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
	<div class="col-xs-3 no-padding" style="padding-left: 5px; padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">No. CN</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control nomor" placeholder="No. CN" disabled>
		</div>
	</div>
	<div class="col-xs-4 no-padding">&nbsp;</div>
	<div class="col-xs-3 no-padding text-right" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Total CN</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-right tot_cn" data-tipe="decimal" placeholder="Total CN" data-required="1" disabled>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-2 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Tanggal CN</label></div>
		<div class="col-xs-12 no-padding">
			<div class="input-group date datetimepicker" name="tanggal" id="Tanggal">
		        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Supplier</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control supplier" data-required="1">
				<option value="">-- Pilih Supplier --</option>
				<?php foreach ($supplier as $key => $value): ?>
					<option value="<?php echo $value['nomor']; ?>"><?php echo $value['nama']; ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Gudang</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control gudang">
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
	<div class="col-xs-12 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="label-control">Keterangan CN</label></div>
		<div class="col-xs-12 no-padding">
			<textarea class="form-control ket_cn" placeholder="Keterangan CN" data-required="1"></textarea>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="overflow-x: auto;">
	<small>
		<table class="table table-bordered" style="margin-bottom: 0px; max-width: 100%; width: 100%;">
			<thead>
				<tr>
					<th class="col-xs-2">No. SJ</th>
					<th class="col-xs-2">Barang</th>
					<th class="col-xs-1">Jumlah</th>
					<th class="col-xs-4">Keterangan</th>
					<th class="col-xs-2">Nominal</th>
					<th class="col-xs-1"></th>
				</tr>
			</thead>
			<tbody>
				<tr class="head">
					<td>
						<select class="form-control no_sj">
						</select>
					</td>
					<td>
						<select class="form-control barang">
						</select>
					</td>
					<td>
						<input type="text" class="form-control text-right jumlah" data-tipe="decimal" data-required="1" placeholder="Jumlah">
					</td>
					<td>
						<textarea class="form-control ket" data-required="1" placeholder="Keterangan"></textarea>
					</td>
					<td>
						<input type="text" class="form-control text-right nominal" data-tipe="decimal" data-required="1" placeholder="Nominal" onblur="cn.hitTot()">
					</td>
					<td rowspan="2">
						<div class="col-xs-12 no-padding">
							<div class="col-xs-6 no-padding" style="padding-right: 5px;">
								<button type="button" class="col-xs-12 btn btn-primary" onclick="cn.addRow(this)"><i class="fa fa-plus"></i></button>
							</div>
							<div class="col-xs-6 no-padding" style="padding-left: 5px;">
								<button type="button" class="col-xs-12 btn btn-danger" onclick="cn.removeRow(this)"><i class="fa fa-trash"></i></button>
							</div>
						</div>
					</td>
				</tr>
				<!-- <tr class="detail">
					<td colspan="5" style="background-color: #dedede;">
						<table class="table table-bordered" style="margin-bottom: 0px;">
							<thead>
								<tr>
									<td class="col-xs-5"><b>Akun</b></td>
									<td class="col-xs-3"><b>Kredit</b></td>
									<td class="col-xs-3"><b>Debet</b></td>
									<td class="col-xs-1"></td>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<select class="form-control det_jurnal_trans" data-required="1">
										</select>
									</td>
									<td class="asal"></td>
									<td class="tujuan"></td>
									<td>
										<div class="col-xs-12 no-padding">
											<div class="col-xs-6 no-padding" style="padding-right: 5px;">
												<button type="button" class="col-xs-12 btn btn-primary" onclick="cn.addRowDet(this)"><i class="fa fa-plus"></i></button>
											</div>
											<div class="col-xs-6 no-padding" style="padding-left: 5px;">
												<button type="button" class="col-xs-12 btn btn-danger" onclick="cn.removeRowDet(this)"><i class="fa fa-trash"></i></button>
											</div>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr> -->
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="col-xs-12 btn btn-primary" onclick="cn.save()"><i class="fa fa-save"></i> Simpan</button>
</div>