<div class="col-xs-7 no-padding" style="padding-right: 5px;">
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">No. Memo</label></div>
		<div class="col-xs-4 no-padding">
			<input type="text" class="col-xs-12 form-control no_mm uppercase" placeholder="No. Memo" value="<?php echo $data['no_mm']; ?>" disabled>
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
						<option value="<?php echo $v_unit['kode']; ?>" <?php echo $selected; ?> ><?php echo $v_unit['nama']; ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Tanggal Memo</label></div>
		<div class="col-xs-4 no-padding">
			<div class="input-group date datetimepicker lock_date_fiskal" name="tglMm" id="TglMm">
				<input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data['tgl_mm']; ?>" />
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
			<input type="text" class="col-xs-12 form-control pelanggan uppercase" placeholder="Nama Pelanggan (MAX:100)" maxlength="100" data-required="1" onkeyup="mm.cekPelangganSupplier()" value="<?php echo $data['pelanggan']; ?>">
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">No. Supplier</label></div>
		<div class="col-xs-7 no-padding">
			<select class="form-control no_supplier">
				<option value="">Pilih Supplier</option>
				<?php if ( !empty($supplier) ): ?>
					<?php foreach ($supplier as $k_supl => $v_supl): ?>
						<?php
							$selected = null;
							if ( $v_supl['nomor'] == $data['no_pelanggan'] ) {
								$selected = 'selected';
							}	
						?>
						<option value="<?php echo $v_supl['nomor']; ?>" data-nama="<?php echo strtoupper($v_supl['nama']); ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_supl['nomor'].' | '.$v_supl['nama']); ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Nama Supplier</label></div>
		<div class="col-xs-7 no-padding">
			<input type="text" class="col-xs-12 form-control supplier uppercase" placeholder="Nama Supplier (MAX:100)" maxlength="100" data-required="1" onkeyup="mm.cekPelangganSupplier()" value="<?php echo $data['supplier']; ?>">
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
						<th class="col-xs-2">Transaksi</th>
						<th class="col-xs-2">Debet</th>
						<th class="col-xs-2">Kredit</th>
						<th class="col-xs-3">Keterangan</th>
						<th class="col-xs-1">No. Invoice</th>
						<th class="col-xs-1">Nilai</th>
						<th class="col-xs-1">Action</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( !empty($detail) ) { ?>
						<?php foreach ($detail as $k_det => $v_det) { ?>
							<tr class="data" data-urut="">
								<td>
									<select class="form-control det_jurnal_trans">
										<option value="">Pilih Transaksi</option>
										<?php if ( !empty($det_jurnal_trans) ): ?>
											<?php foreach ($det_jurnal_trans as $k_djt => $v_djt): ?>
												<?php
													$selected = null;
													if ( $v_det['det_jurnal_trans'] == $v_djt['kode'] ) {
														$selected = 'selected';
													}	
												?>
												<option value="<?php echo $v_djt['kode']; ?>" data-idjt="<?php echo $v_djt['id_header']; ?>" data-coaasal="<?php echo $v_djt['sumber_coa']; ?>" data-coatujuan="<?php echo $v_djt['tujuan_coa']; ?>" <?php echo $selected; ?> ><?php echo $v_djt['nama']; ?></option>
											<?php endforeach ?>
										<?php endif ?>
									</select>
								</td>
								<td>
									<select class="form-control tujuan">
										<option value="">Pilih COA</option>
										<?php if ( !empty($coa) ): ?>
											<?php foreach ($coa as $k_coa => $v_coa): ?>
												<?php
													$selected = null;
													if ( $v_coa['no_coa'] == $v_det['coa_tujuan'] ) {
														$selected = 'selected';
													}	
												?>
												<option value="<?php echo $v_coa['no_coa']; ?>" data-nama="<?php echo $v_coa['nama_coa']; ?>" <?php echo $selected; ?> ><?php echo $v_coa['no_coa'].' | '.$v_coa['nama_coa']; ?></option>
											<?php endforeach ?>
										<?php endif ?>
									</select>
								</td>
								<td>
									<select class="form-control asal">
										<option value="">Pilih COA</option>
										<?php if ( !empty($coa) ): ?>
											<?php foreach ($coa as $k_coa => $v_coa): ?>
												<?php
													$selected = null;
													if ( $v_coa['no_coa'] == $v_det['coa_asal'] ) {
														$selected = 'selected';
													}	
												?>
												<option value="<?php echo $v_coa['no_coa']; ?>" data-nama="<?php echo $v_coa['nama_coa']; ?>" <?php echo $selected; ?> ><?php echo $v_coa['no_coa'].' | '.$v_coa['nama_coa']; ?></option>
											<?php endforeach ?>
										<?php endif ?>
									</select>
								</td>
								<td>
									<input type="text" class="form-control keterangan uppercase" placeholder="Keterangan" maxlength="50" value="<?php echo $v_det['keterangan']; ?>">
								</td>
								<td>
									<input type="text" class="form-control no_invoice uppercase" placeholder="No. Invoice" maxlength="50" value="<?php echo $v_det['no_invoice']; ?>">
								</td>
								<!-- <td>
									<input type="text" class="form-control text-right nilai_faktur uppercase" placeholder="Nilai Faktur" data-tipe="decimal" maxlength="19" disabled>
								</td> -->
								<td>
									<input type="text" class="form-control text-right nilai uppercase" placeholder="Nilai" data-tipe="decimal" maxlength="19" data-required="1" onblur="mm.hitGrandTotal(this)" value="<?php echo angkaDecimal($v_det['nilai']); ?>">
								</td>
								<td>
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
						<td>
							<select class="form-control det_jurnal_trans">
								<option value="">Pilih Transaksi</option>
								<?php if ( !empty($det_jurnal_trans) ): ?>
									<?php foreach ($det_jurnal_trans as $k_djt => $v_djt): ?>
										<option value="<?php echo $v_djt['kode']; ?>" data-idjt="<?php echo $v_djt['id_header']; ?>" data-coaasal="<?php echo $v_djt['sumber_coa']; ?>" data-coatujuan="<?php echo $v_djt['tujuan_coa']; ?>"><?php echo $v_djt['nama']; ?></option>
									<?php endforeach ?>
								<?php endif ?>
							</select>
						</td>
						<td>
							<select class="form-control tujuan" data-required="1">
								<option value="">Pilih COA</option>
								<?php if ( !empty($coa) ): ?>
									<?php foreach ($coa as $k_coa => $v_coa): ?>
										<option value="<?php echo $v_coa['no_coa']; ?>" data-nama="<?php echo $v_coa['nama_coa']; ?>" ><?php echo $v_coa['no_coa'].' | '.$v_coa['nama_coa']; ?></option>
									<?php endforeach ?>
								<?php endif ?>
							</select>
						</td>
						<td>
							<select class="form-control asal" data-required="1">
								<option value="">Pilih COA</option>
								<?php if ( !empty($coa) ): ?>
									<?php foreach ($coa as $k_coa => $v_coa): ?>
										<option value="<?php echo $v_coa['no_coa']; ?>" data-nama="<?php echo $v_coa['nama_coa']; ?>" ><?php echo $v_coa['no_coa'].' | '.$v_coa['nama_coa']; ?></option>
									<?php endforeach ?>
								<?php endif ?>
							</select>
						</td>
						<td>
							<input type="text" class="form-control keterangan uppercase" placeholder="Keterangan" maxlength="50">
						</td>
						<td>
							<input type="text" class="form-control no_invoice uppercase" placeholder="No. Invoice" maxlength="50">
						</td>
						<!-- <td>
							<input type="text" class="form-control text-right nilai_faktur uppercase" placeholder="Nilai Faktur" data-tipe="decimal" maxlength="19" disabled>
						</td> -->
						<td>
							<input type="text" class="form-control text-right nilai uppercase" placeholder="Nilai" data-tipe="decimal" maxlength="19" data-required="1" onblur="mm.hitGrandTotal(this)">
						</td>
						<td>
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