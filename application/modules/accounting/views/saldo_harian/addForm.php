<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding header">
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<label class="control-label">Tanggal</label>
			</div>
			<div class="col-xs-12 no-padding">
				<div class="input-group date datetimepicker" name="tanggal" id="Tanggal">
			        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" style="padding-left: 15px;" />
			        <span class="input-group-addon">
			            <span class="glyphicon glyphicon-calendar"></span>
			        </span>
			    </div>
			</div>
		</div>
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding">
				<label class="control-label">Perusahaan</label>
			</div>
			<div class="col-xs-12 no-padding">
				<select class="form-control perusahaan" data-required="1">
					<option value="">-- Pilih Perusahaan --</option>
					<?php foreach ($perusahaan as $k_perusahaan => $v_perusahaan): ?>
						<option value="<?php echo $v_perusahaan['kode']; ?>" data-aktif="<?php echo strtoupper($v_perusahaan['aktif']); ?>"><?php echo $v_perusahaan['nama']; ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="col-xs-12 no-padding btn-ambil-data" style="margin-top: 10px;">
			<button type="button" class="col-xs-12 btn btn-primary cursor-p" onclick="sld.getData()"><i class="fa fa-search"></i> Ambil Data</button>
		</div>
	</div>
	<div class="col-xs-12 no-padding">
		<hr style="margin-top: 10px; margin-bottom: 10px;">
	</div>
	<div class="col-xs-12 no-padding contain">
		<table class="table table-nobordered list_data" style="width: 100%; margin-bottom: 0px;">
			<tbody>
				<tr>
					<td style="width: 20%;">Saldo Bank (Rp.)</td>
					<td style="width: 25%;">
						<input type="text" class="form-control saldo_bank" data-required="1" placeholder="NILAI" data-tipe="decimal">
					</td>
					<td style="width: 55%;"></td>
				</tr>
				<tr>
					<td>Total Transfer (Rp.)</td>
					<td>
						<input type="text" class="form-control tot_transfer" data-required="1" placeholder="NILAI" data-tipe="decimal">
					</td>
				</tr>
				<tr>
					<td>Hutang Supplier</td>
					<td colspan="3">
						<table class="table table-nobordered list_data_hutang" style="margin-bottom: 0px;">
							<tbody>
								<tr>
									<td style="width: 40%;">
										<div class="col-xs-12 no-padding">
											<select class="form-control supplier" data-required="1">
												<option value="">-- Pilih Supplier --</option>
												<?php foreach ($supplier as $k_supplier => $v_supplier): ?>
													<option value="<?php echo $v_supplier['nomor']; ?>"><?php echo strtoupper($v_supplier['nama']); ?></option>
												<?php endforeach ?>
											</select>
										</div>
										<div class="col-xs-12 no-padding">
											<div class="col-xs-12 no-padding">
												<label class="control-label">Nota Terlama (Hari)</label>
											</div>
											<div class="col-xs-3 no-padding">
												<input type="text" class="form-control nota_terlama" data-required="1" placeholder="HARI" data-tipe="integer">
											</div>
										</div>
									</td>
									<td style="width: 25%; padding-left: 10px;">
										<input type="text" class="form-control hutang nilai_hutang" data-required="1" placeholder="NILAI" data-tipe="decimal">
									</td>
									<td style="width: 30%; padding-left: 10px;">
										<button type="button" class="btn btn-primary" onclick="sld.addRow(this);"><i class="fa fa-plus"></i></button>
										<button type="button" class="btn btn-danger" onclick="sld.removeRow(this);"><i class="fa fa-times"></i></button>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td>Hutang BCA (Rp.)</td>
					<td>
						<input type="text" class="form-control hutang hut_bca" data-required="1" placeholder="NILAI" data-tipe="decimal">
					</td>
				</tr>
				<tr>
					<td>Total Hutang (Rp.)</td>
					<td>
						<input type="text" class="form-control tot_hutang" data-required="1" placeholder="NILAI" data-tipe="decimal" disabled>
					</td>
				</tr>
				<tr>
					<td>L/R Sebelumnya (Rp.)</td>
					<td>
						<input type="text" class="form-control lr lr_sebelumnya" data-required="1" placeholder="NILAI" data-tipe="decimal">
					</td>
				</tr>
				<tr>
					<td>L/R Hari Ini (Rp.)</td>
					<td>
						<input type="text" class="form-control lr lr_hari_ini" data-required="1" placeholder="NILAI" data-tipe="decimal">
					</td>
				</tr>
				<tr>
					<td>CN Pakan (Rp.)</td>
					<td>
						<input type="text" class="form-control lr cn_pakan" data-required="1" placeholder="NILAI" data-tipe="decimal">
					</td>
				</tr>
				<tr>
					<td>CN DOC (Rp.)</td>
					<td>
						<input type="text" class="form-control lr cn_doc" data-required="1" placeholder="NILAI" data-tipe="decimal">
					</td>
				</tr>
				<tr>
					<td>Total L/R (Rp.)</td>
					<td>
						<input type="text" class="form-control tot_lr" data-required="1" placeholder="NILAI" data-tipe="decimal" disabled>
					</td>
				</tr>
				<tr>
					<td>RHPP Selesai Hari Ini</td>
					<td>
						<input type="text" class="form-control rhpp_selesai" data-required="1" placeholder="JUMLAH" data-tipe="integer">
					</td>
					<td style="padding-left: 10px;">
						<div class="col-xs-2 no-padding">
							<input type="text" class="form-control rhpp_selesai_box" data-required="1" placeholder="BOX" data-tipe="integer">
						</div>
						<div class="col-xs-1">Box</div>
					</td>
				</tr>
				<tr>
					<td>Laba (Per Ekor)</td>
					<td>
						<input type="text" class="form-control laba_per_ekor" data-required="1" placeholder="NILAI" data-tipe="decimal">
					</td>
				</tr>
				<tr>
					<td>Harga Rata2 Ayam</td>
					<td>
						<input type="text" class="form-control harga_rata_ayam" data-required="1" placeholder="NILAI" data-tipe="decimal">
					</td>
				</tr>
				<tr>
					<td>Harga Rata DOC</td>
					<td>
						<input type="text" class="form-control harga_rata_doc" data-required="1" placeholder="NILAI" data-tipe="decimal">
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="col-xs-12 no-padding">
		<hr style="margin-top: 10px; margin-bottom: 10px;">
	</div>
	<div class="col-xs-12 no-padding">
		<button type="button" class="col-xs-12 btn btn-primary cursor-p" onclick="sld.save()"><i class="fa fa-save"></i> Simpan</button>
	</div>
</div>