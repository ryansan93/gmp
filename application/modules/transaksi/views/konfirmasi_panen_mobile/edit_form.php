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
							<?php
								$selected = null;
								if ( $v_dm['nomor'] == $data['nomor'] ) {
									$selected = 'selected';
								}
							?>
							<option data-tokens="<?php echo $v_dm['nama']; ?>" value="<?php echo $v_dm['nomor']; ?>" <?php echo $selected ?> ><?php echo strtoupper($v_dm['unit'].' | '.$v_dm['nama']); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">No. Reg</label>
				</div>
				<div class="col-xs-12 no-padding">
					<select id="select_noreg" data-placeholder="Pilih No. Reg" class="form-control selectpicker" data-live-search="true" type="text" data-required="1" data-val="<?php echo $data['noreg']; ?>" disabled>
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
		                <input type="text" class="form-control text-center uppercase" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data['tgl_panen']; ?>" />
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
					<input type="text" class="form-control umur text-right" placeholder="Umur" data-tipe="integer" data-required="1" value="<?php echo $data['umur']; ?>" disabled>
				</div>
			</div>

			<div class="col-xs-6" style="padding: 0px 0px 0px 5px; margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Populasi</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control populasi text-right" placeholder="Populasi" data-tipe="integer" data-required="1" value="<?php echo angkaRibuan($data['populasi']); ?>" disabled>
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">BB Rata2</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right bb_rata2" placeholder="BB" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal($data['bb']); ?>" disabled />
				</div>
			</div>

			<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Total Sekat</label>
				</div>
				<div class="col-xs-12 no-padding">
					<input type="text" class="form-control text-right tot_sekat" placeholder="Total" data-tipe="decimal" data-required="1" value="<?php echo angkaDecimal($data['total']); ?>" readonly />
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
						<?php $idx = 1; $jml_sekat = 0; $tot_jumlah = 0; $tot_bb = 0; ?>
						<?php foreach ($data['detail'] as $k_det => $v_det): ?>
							<tr class="v-center">
								<td class="text-center no_urut"><?php echo $idx; ?></td>
								<td><input type="text" class="form-control text-right jumlah" data-tipe="integer" onblur="kpm.hitung_total(this);" data-required="1" value="<?php echo angkaRibuan($v_det['jumlah']); ?>" /></td>
								<td>
									<input type="text" class="form-control text-right bb" data-tipe="decimal" onblur="kpm.hitung_total(this);" data-required="1" value="<?php echo angkaDecimal($v_det['bb']); ?>" />
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
							<?php
								$idx++;
								$tot_jumlah += $v_det['jumlah']; 
								$tot_bb += $v_det['bb']; 
							?>
						<?php endforeach ?>
					</tbody>
					<tfoot>
						<tr>
							<td><b>Total</b></td>
							<td class="text-right tot_jumlah"><b><?php echo angkaRibuan($tot_jumlah); ?></b></td>
							<td class="text-right tot_bb" colspan="2"><!-- <b><?php echo angkaDecimal($tot_bb); ?></b> --></td>
							<!-- <td class="text-right"></td> -->
						</tr>
					</tfoot>
				</table>
			</div>
		</form>
	</div>
	<div class="col-lg-12 detailed"><hr></div>
	<?php if ( $data['edit'] == 1 ): ?>
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="col-xs-6 no-padding" style="padding-right: 5px">
					<button type="button" class="btn btn-primary col-xs-12" onclick="kpm.edit(this)" style="width: 100%;" data-id="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
				</div>
				<div class="col-xs-6 no-padding" style="padding-left: 5px">
					<button type="button" class="btn btn-danger col-xs-12" onclick="kpm.batal_edit(this)" style="width: 100%;" data-id="<?php echo $data['id']; ?>"><i class="fa fa-times"></i> Batal</button>
				</div>
			</form>
		</div>
	<?php endif ?>
</div>
