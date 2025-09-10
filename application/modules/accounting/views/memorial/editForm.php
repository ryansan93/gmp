<div class="col-xs-7 no-padding" style="padding-right: 5px;">
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">No. Memo</label></div>
		<div class="col-xs-4 no-padding">
			<input type="text" class="col-xs-12 form-control no_mm uppercase" placeholder="No. Memo" value="<?php echo $data['no_mm']; ?>" disabled>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Tanggal Memo</label></div>
		<div class="col-xs-4 no-padding">
			<div class="input-group date datetimepicker" name="tglMm" id="TglMm">
				<input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data['tgl_mm']; ?>" />
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Customer</label></div>
		<div class="col-xs-7 no-padding">
			<select class="form-control customer" data-nomm="<?php echo $data['no_mm']; ?>">
				<option value="">Pilih Customer</option>
				<?php if ( !empty($customer) ): ?>
					<?php foreach ($customer as $k_customer => $v_customer): ?>
						<?php
							$selected = null;
							if ( $v_customer['kode_cust'] == $data['kode_cust'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $v_customer['kode_cust']; ?>" <?php echo $selected; ?> ><?php echo $v_customer['kode_cust'].' | '.$v_customer['nama_cust']; ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Supplier</label></div>
		<div class="col-xs-7 no-padding">
			<select class="form-control supplier" data-nomm="<?php echo $data['no_mm']; ?>">
				<option value="">Pilih Suppplier</option>
				<?php if ( !empty($supplier) ): ?>
					<?php foreach ($supplier as $k_supplier => $v_supplier): ?>
						<?php
							$selected = null;
							if ( $v_supplier['kode_supl'] == $data['kode_supl'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $v_supplier['kode_supl']; ?>" <?php echo $selected; ?> ><?php echo $v_supplier['kode_supl'].' | '.$v_supplier['nama_supl']; ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Keterangan</label></div>
		<div class="col-xs-9 no-padding">
			<textarea class="form-control keterangan"><?php echo $data['keterangan']; ?></textarea>
		</div>
	</div>
</div>
<div class="col-xs-5 no-padding" style="padding-left: 5px;">
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3">&nbsp;</div>
		<div class="col-xs-3 no-padding"><label class="control-label">Total Debet</label></div>
		<div class="col-xs-6 no-padding">
			<input type="text" class="col-xs-12 form-control text-right tot_debet uppercase" placeholder="Total" value="<?php echo formatAngka($data['tot_debet']); ?>" disabled>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3">&nbsp;</div>
		<div class="col-xs-3 no-padding"><label class="control-label">Total Kredit</label></div>
		<div class="col-xs-6 no-padding">
			<input type="text" class="col-xs-12 form-control text-right tot_kredit uppercase" placeholder="Total" value="<?php echo formatAngka($data['tot_kredit']); ?>" disabled>
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
						<th>No. COA</th>
						<th>Nama. COA</th>
						<th>Keterangan</th>
						<th>No. Faktur</th>
						<th>No. Invoice</th>
						<th>Debet</th>
						<th>Kredit</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( !empty($detail) ) { ?>
						<?php foreach ($detail as $k_det => $v_det) { ?>
							<tr class="data" data-urut="<?php echo $v_det['no_urut']; ?>">
								<td style="width: 10%; max-width: 10%;">
									<select class="form-control no_coa" data-required="1">
										<option value="">Pilih No. COA</option>
										<?php if ( !empty($coa) ): ?>
											<?php foreach ($coa as $k_coa => $v_coa): ?>
												<?php
													$selected = null;
													if ( $v_coa['no_coa'] == $v_det['no_coa'] ) {
														$selected = 'selected';
													}
												?>
												<option value="<?php echo $v_coa['no_coa']; ?>" data-nama="<?php echo $v_coa['nama_coa']; ?>" <?php echo $selected; ?> ><?php echo $v_coa['no_coa']; ?></option>
											<?php endforeach ?>
										<?php endif ?>
									</select>
								</td>
								<td style="width: 20%; max-width: 20%;">
									<select class="form-control nama_coa" data-required="1">
										<option value="">Pilih Nama COA</option>
										<?php if ( !empty($coa) ): ?>
											<?php foreach ($coa as $k_coa => $v_coa): ?>
												<?php
													$selected = null;
													if ( $v_coa['no_coa'] == $v_det['no_coa'] ) {
														$selected = 'selected';
													}
												?>
												<option value="<?php echo $v_coa['nama_coa']; ?>" data-no="<?php echo $v_coa['no_coa']; ?>" <?php echo $selected; ?> ><?php echo $v_coa['nama_coa']; ?></option>
											<?php endforeach ?>
										<?php endif ?>
									</select>
								</td>
								<td style="width: 20%; max-width: 20%;">
									<input type="text" class="form-control keterangan uppercase" placeholder="Keterangan" maxlength="50" value="<?php echo strtoupper($v_det['keterangan']); ?>">
								</td>
								<td style="width: 13%; max-width: 13%;">
									<select class="form-control faktur" data-val="<?php echo $v_det['no_faktur']; ?>">
										<option value="">Pilih No. Faktur</option>
									</select>
								</td>
								<td style="width: 13%; max-width: 13%;">
									<select class="form-control lpb" data-val="<?php echo $v_det['no_lpb']; ?>">
										<option value="">Pilih No. LPB</option>
									</select>
								</td>
								<td style="width: 9%; max-width: 9%;">
									<input type="text" class="form-control text-right debet uppercase" placeholder="Nilai" data-tipe="decimal" maxlength="19" onblur="mm.hitGrandTotal(this)" value="<?php echo formatAngka($v_det['debet']); ?>">
								</td>
								<td style="width: 9%; max-width: 9%;">
									<input type="text" class="form-control text-right kredit uppercase" placeholder="Nilai" data-tipe="decimal" maxlength="19" data-required="1" onblur="mm.hitGrandTotal(this)" value="<?php echo formatAngka($v_det['kredit']); ?>">
								</td>
								<td style="width: 6%; max-width: 6%;">
									<div class="col-xs-12 no-padding">
										<div class="col-xs-6 no-padding" style="padding-right: 3px;">
											<button type="button" class="col-xs-12 btn btn-danger" onclick="mm.removeRow(this)"><i class="fa fa-times"></i></button>
										</div>
										<div class="col-xs-6 no-padding" style="padding-left: 3px;">
											<button type="button" class="col-xs-12 btn btn-primary" onclick="mm.addRow(this)"><i class="fa fa-plus"></i></button>
										</div>
									</div>
								</td>
							</tr>
						<?php } ?>
					<?php } else { ?>
						<tr class="data" data-urut="">
							<td style="width: 10%; max-width: 10%;">
								<select class="form-control no_coa" data-required="1">
									<option value="">Pilih No. COA</option>
									<?php if ( !empty($coa) ): ?>
										<?php foreach ($coa as $k_coa => $v_coa): ?>
											<option value="<?php echo $v_coa['no_coa']; ?>" data-nama="<?php echo $v_coa['nama_coa']; ?>"><?php echo $v_coa['no_coa']; ?></option>
										<?php endforeach ?>
									<?php endif ?>
								</select>
							</td>
							<td style="width: 20%; max-width: 20%;">
								<select class="form-control nama_coa" data-required="1">
									<option value="">Pilih Nama COA</option>
									<?php if ( !empty($coa) ): ?>
										<?php foreach ($coa as $k_coa => $v_coa): ?>
											<option value="<?php echo $v_coa['nama_coa']; ?>" data-no="<?php echo $v_coa['no_coa']; ?>"><?php echo $v_coa['nama_coa']; ?></option>
										<?php endforeach ?>
									<?php endif ?>
								</select>
							</td>
							<td style="width: 20%; max-width: 20%;">
								<input type="text" class="form-control keterangan uppercase" placeholder="Keterangan" maxlength="50">
							</td>
							<td style="width: 13%; max-width: 13%;">
								<select class="form-control faktur">
									<option value="">Pilih No. Faktur</option>
								</select>
							</td>
							<td style="width: 13%; max-width: 13%;">
								<select class="form-control lpb">
									<option value="">Pilih No. LPB</option>
								</select>
							</td>
							<td style="width: 9%; max-width: 9%;">
								<input type="text" class="form-control text-right debet uppercase" placeholder="Nilai" data-tipe="decimal" maxlength="19" onblur="mm.hitGrandTotal(this)">
							</td>
							<td style="width: 9%; max-width: 9%;">
								<input type="text" class="form-control text-right kredit uppercase" placeholder="Nilai" data-tipe="decimal" maxlength="19" onblur="mm.hitGrandTotal(this)">
							</td>
							<td style="width: 6%; max-width: 6%;">
								<div class="col-xs-12 no-padding">
									<div class="col-xs-6 no-padding" style="padding-right: 3px;">
										<button type="button" class="col-xs-12 btn btn-danger" onclick="mm.removeRow(this)"><i class="fa fa-times"></i></button>
									</div>
									<div class="col-xs-6 no-padding" style="padding-left: 3px;">
										<button type="button" class="col-xs-12 btn btn-primary" onclick="mm.addRow(this)"><i class="fa fa-plus"></i></button>
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
    <button type="button" class="btn btn-primary pull-right" onclick="mm.edit(this)" data-kode="<?php echo $data['no_mm']; ?>" style="margin-left: 5px;">
        <i class="fa fa-save"></i>
        Update
    </button>
    <button type="button" class="btn btn-danger pull-right" onclick="mm.changeTabActive(this)" data-href="action" data-edit="" data-kode="<?php echo $data['no_mm']; ?>" style="margin-right: 5px;">
        <i class="fa fa-times"></i>
        Batal
    </button>
</div>