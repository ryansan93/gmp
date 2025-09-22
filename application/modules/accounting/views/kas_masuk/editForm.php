<div class="col-xs-7 no-padding" style="padding-right: 5px;">
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">No. Kas Masuk</label></div>
		<div class="col-xs-4 no-padding">
			<input type="text" class="col-xs-12 form-control no_km uppercase" placeholder="No. Kas Masuk" value="<?php echo $data['no_km']; ?>" disabled>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Voucher</label></div>
		<div class="col-xs-6 no-padding" style="padding-right: 5px;">
			<select class="form-control jurnal_trans" data-required="1">
				<?php if ( !empty($jurnal_trans) ): ?>
					<?php foreach ($jurnal_trans as $k_jt => $v_jt): ?>
						<?php
							$selected = null;
							if ( $v_jt['kode'] == $data['jurnal_trans'] ) {
								$selected = 'selected';
							}	
						?>
						<option value="<?php echo $v_jt['kode']; ?>" data-id="<?php echo $v_jt['id']; ?>" <?php echo $selected; ?> ><?php echo $v_jt['nama']; ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Unit</label></div>
		<div class="col-xs-3 no-padding" style="padding-right: 5px;">
			<select class="form-control unit" data-required="1">
				<?php if ( !empty($unit) ): ?>
					<?php foreach ($unit as $k_unit => $v_unit): ?>
						<?php
							$selected = null;
							if ( $v_unit['kode'] == $data['unit'] ) {
								$selected = 'selected';
							}	
						?>
						<option value="<?php echo $v_unit['kode']; ?>"><?php echo $v_unit['nama']; ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Tanggal Kas Masuk</label></div>
		<div class="col-xs-4 no-padding">
			<div class="input-group date datetimepicker" name="tglKm" id="TglKm">
				<input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data['tgl_km']; ?>" />
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">No. Pelanggan</label></div>
		<div class="col-xs-7 no-padding">
			<select class="form-control no_pelanggan">
				<option value="">Pilih Pelanggan</option>
				<?php if ( !empty($pelanggan) ): ?>
					<?php foreach ($pelanggan as $k_plg => $v_plg): ?>
						<?php
							$selected = null;
							if ( $v_plg['nomor'] == $data['no_pelanggan'] ) {
								$selected = 'selected';
							}	
						?>
						<option value="<?php echo $v_plg['nomor']; ?>" data-nama="<?php echo strtoupper($v_plg['nama']); ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_plg['nomor'].' | '.$v_plg['nama']); ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Nama Pelanggan</label></div>
		<div class="col-xs-7 no-padding">
			<input type="text" class="col-xs-12 form-control pelanggan uppercase" placeholder="Nama Pelanggan (MAX:100)" maxlength="100" data-required="1" value="<?php echo $data['pelanggan']; ?>">
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Keterangan</label></div>
		<div class="col-xs-9 no-padding">
			<textarea class="form-control keterangan"><?php echo $data['keterangan']; ?></textarea>
		</div>
	</div>
	<!-- <div class="col-xs-12 no-padding hide" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Nama Bank</label></div>
		<div class="col-xs-4 no-padding">
			<input type="text" class="col-xs-12 form-control nama_bank uppercase" placeholder="Nama Bank" maxlength="20" value="<?php echo $data['nama_bank']; ?>">
		</div>
	</div>
	<div class="col-xs-12 no-padding hide" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">No. Giro</label></div>
		<div class="col-xs-4 no-padding">
			<input type="text" class="col-xs-12 form-control no_giro uppercase" placeholder="No. Giro" maxlength="20" value="<?php echo $data['no_giro']; ?>">
		</div>
	</div>
	<div class="col-xs-12 no-padding hide" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Tanggal Tempo</label></div>
		<div class="col-xs-4 no-padding">
			<div class="input-group date datetimepicker" name="tglTempo" id="TglTempo">
				<input type="text" class="form-control text-center" placeholder="Tanggal" data-tgl="<?php echo $data['tgl_tempo']; ?>" />
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding hide" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Tanggal Cair</label></div>
		<div class="col-xs-4 no-padding">
			<div class="input-group date datetimepicker" name="tglCair" id="TglCair">
				<input type="text" class="form-control text-center" placeholder="Tanggal" data-tgl="<?php echo $data['tgl_cair']; ?>" />
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
			</div>
		</div>
	</div> -->
</div>
<div class="col-xs-5 no-padding" style="padding-left: 5px;">
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3">&nbsp;</div>
		<div class="col-xs-3 no-padding"><label class="control-label">Total</label></div>
		<div class="col-xs-6 no-padding nilai">
			<input type="text" class="col-xs-12 form-control text-right nilai uppercase" placeholder="Total" value="<?php echo angkaDecimal($data['nilai']); ?>" disabled>
		</div>
	</div>
</div>

<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>

<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding" style="overflow-x: auto;">
		<small>
			<table class="table table-bordered tbl_detail" style="margin-bottom: 0px; max-width: 100%; width: 100%;">
				<thead>
					<tr>
						<th>Transaksi</th>
						<th>Keterangan</th>
						<th>No. Invoice</th>
						<!-- <th>Nilai Invoice</th> -->
						<th>Nilai</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( !empty($detail) ) { ?>
						<?php foreach ($detail as $k_det => $v_det) { ?>
							<tr class="data" data-urut="<?php echo $v_det['no_urut']; ?>">
								<td style="width: 20%; max-width: 20%;">
									<select class="form-control det_jurnal_trans" data-required="1">
										<option value="">Pilih Transaksi</option>
										<?php if ( !empty($det_jurnal_trans) ): ?>
											<?php foreach ($det_jurnal_trans as $k_djt => $v_djt): ?>
												<?php
													$selected = null;
													if ( $v_djt['kode'] == $v_det['det_jurnal_trans'] ) {
														$selected = 'selected';
													}
												?>
												<option value="<?php echo $v_djt['kode']; ?>" data-idjt="<?php echo $v_djt['id_header']; ?>" data-coaasal="<?php echo $v_djt['sumber_coa']; ?>" data-coatujuan="<?php echo $v_djt['tujuan_coa']; ?>" <?php echo $selected; ?> ><?php echo $v_djt['nama']; ?></option>
											<?php endforeach ?>
										<?php endif ?>
									</select>
								</td>
								<td style="width: 30%; max-width: 30%;">
									<?php $ket = strtoupper($v_det['keterangan']); ?>
									<input type="text" class="form-control keterangan uppercase" placeholder="Keterangan" maxlength="50" value="<?php echo strtoupper($ket); ?>">
								</td>
								<td style="width: 20%; max-width: 20%;">
									<input type="text" class="form-control no_invoice uppercase" placeholder="No. Invoice" maxlength="50" value="<?php echo $v_det['no_invoice']; ?>" >
								</td>
								<!-- <td style="width: 10%; max-width: 10%;">
									<input type="text" class="form-control text-right nilai_faktur uppercase" placeholder="Nilai Faktur" data-tipe="decimal" maxlength="19" value="<?php echo angkaDecimal($v_det['nilai_faktur']); ?>" disabled>
								</td> -->
								<td style="width: 15%; max-width: 15%;">
									<input type="text" class="form-control text-right nilai uppercase" placeholder="Nilai" data-tipe="decimal" maxlength="19" data-required="1" onblur="km.hitGrandTotal(this)" value="<?php echo angkaDecimal($v_det['nilai']); ?>">
								</td>
								<td style="width: 15%; max-width: 15%;">
									<div class="col-xs-12 no-padding">
										<div class="col-xs-6 no-padding" style="padding-right: 3px;">
											<button type="button" class="col-xs-12 btn btn-danger" onclick="km.removeRow(this)"><i class="fa fa-times"></i></button>
										</div>
										<div class="col-xs-6 no-padding" style="padding-left: 3px;">
											<button type="button" class="col-xs-12 btn btn-primary" onclick="km.addRow(this)"><i class="fa fa-plus"></i></button>
										</div>
									</div>
								</td>
							</tr>
						<?php } ?>
					<?php } else { ?>
						<tr class="data" data-urut="">
							<td style="width: 20%; max-width: 20%;">
								<select class="form-control det_jurnal_trans" data-required="1">
									<option value="">Pilih Transaksi</option>
									<?php if ( !empty($det_jurnal_trans) ): ?>
										<?php foreach ($det_jurnal_trans as $k_djt => $v_djt): ?>
											<option value="<?php echo $v_djt['kode']; ?>" data-idjt="<?php echo $v_djt['id_header']; ?>" data-coaasal="<?php echo $v_djt['sumber_coa']; ?>" data-coatujuan="<?php echo $v_djt['tujuan_coa']; ?>"><?php echo $v_djt['nama']; ?></option>
										<?php endforeach ?>
									<?php endif ?>
								</select>
							</td>
							<td style="width: 30%; max-width: 30%;">
								<input type="text" class="form-control keterangan uppercase" placeholder="Keterangan" maxlength="50">
							</td>
							<td style="width: 20%; max-width: 20%;">
								<input type="text" class="form-control no_invoice uppercase" placeholder="No. Invoice" maxlength="50">
							</td>
							<!-- <td style="width: 10%; max-width: 10%;">
								<input type="text" class="form-control text-right nilai_faktur uppercase" placeholder="Nilai Faktur" data-tipe="decimal" maxlength="19" disabled>
							</td> -->
							<td style="width: 15%; max-width: 15%;">
								<input type="text" class="form-control text-right nilai uppercase" placeholder="Nilai" data-tipe="decimal" maxlength="19" data-required="1" onblur="km.hitGrandTotal(this)">
							</td>
							<td style="width: 15%; max-width: 15%;">
								<div class="col-xs-12 no-padding">
									<div class="col-xs-6 no-padding" style="padding-right: 3px;">
										<button type="button" class="col-xs-12 btn btn-danger" onclick="km.removeRow(this)"><i class="fa fa-times"></i></button>
									</div>
									<div class="col-xs-6 no-padding" style="padding-left: 3px;">
										<button type="button" class="col-xs-12 btn btn-primary" onclick="km.addRow(this)"><i class="fa fa-plus"></i></button>
									</div>
								</div>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</small>
	</div>
</div>

<div class="col-xs-12 no-padding"><hr></div>

<div class="col-xs-12 no-padding">
    <button type="button" class="btn btn-primary pull-right" onclick="km.edit(this)" data-kode="<?php echo $data['no_km']; ?>" style="margin-left: 5px;">
        <i class="fa fa-save"></i>
        Update
    </button>
    <button type="button" class="btn btn-danger pull-right" onclick="km.changeTabActive(this)" data-href="action" data-edit="" data-kode="<?php echo $data['no_km']; ?>" style="margin-right: 5px;">
        <i class="fa fa-times"></i>
        Batal
    </button>
</div>