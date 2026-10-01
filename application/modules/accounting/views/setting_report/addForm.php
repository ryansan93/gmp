<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding"><label class="control-label" style="padding-top: 0px;">Nama Laporan</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="col-xs-12 form-control nama_laporan" data-required="1" placeholder="Nama">
		</div>
	</div>
	<div class="col-xs-12 no-padding"><br></div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding">
			<table class="table table-bordered" style="margin-bottom: 0px;">
				<tbody>
					<tr class="group">
						<td class="col-xs-10">
							<div class="col-xs-12 no-padding" style="display:flex; gap:6px; align-items:flex-end;">
								<div style="flex:0.5;">
									<label>Urutan</label>
									<input type="text" class="form-control text-center urut_group" data-required="1" data-tipe="integer" placeholder="No.">
								</div>
								<div style="flex:2;">
									<label>Nama Group <span class="badge group-badge" style="background:#777;">1</span></label>
									<input type="text" class="form-control nama_group" data-required="1" placeholder="Nama Group">
								</div>
								<div style="flex:1;">
									<label>Tipe</label>
									<select class="form-control tipe_group" data-required="1">
										<option value="data">DATA</option>
										<option value="subtotal">SUBTOTAL</option>
									</select>
								</div>
								<div style="flex:1; display:none;" class="ref-group-wrapper">
									<label>Ref. No. Urut <small class="text-muted">(pisah koma)</small></label>
									<input type="text" class="form-control ref_group_ids" placeholder="misal: 1,2">
								</div>
							</div>
						</td>
						<td class="col-xs-2">
							<label>&nbsp;</label>
							<div class="col-xs-12 no-padding">
								<div class="col-xs-6 no-padding" style="padding-right: 5px;">
									<button type="button" class="col-xs-12 btn btn-danger" onclick="sr.removeRowGroup(this)"><i class="fa fa-minus"></i></button>
								</div>
								<div class="col-xs-6 no-padding" style="padding-left: 5px;">
									<button type="button" class="col-xs-12 btn btn-primary" onclick="sr.addRowGroup(this)"><i class="fa fa-plus"></i></button>
								</div>
							</div>
						</td>
					</tr>
					<tr class="item-group">
						<td colspan="2" style="background-color: #ededed;">
							<small>
								<table class="table table-bordered item-group-table" style="margin-bottom: 0px;">
									<thead>
										<tr>
											<th class="col-xs-1">Urutan</th>
											<th class="col-xs-3">Item</th>
											<th class="col-xs-3">Nama COA</th>
											<th class="col-xs-1">No. COA</th>
											<th class="col-xs-2">Sign</th>
											<th class="col-xs-2"></th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>
												<input type="text" class="form-control text-center urut" data-required="1" data-tipe="integer" placeholder="No.">
											</td>
											<td>
												<select class="form-control item" data-required="1">
													<option value="">-- Pilih Item --</option>
													<?php if ( !empty($item_report) ): ?>
														<?php foreach ($item_report as $v_ir): ?>
															<option value="<?php echo $v_ir['id']; ?>"><?php echo strtoupper($v_ir['nama']); ?></option>
														<?php endforeach ?>
													<?php endif ?>
												</select>
											</td>
											<td>
												<select class="form-control nama_coa" data-required="1">
													<option value="">-- Pilih COA --</option>
													<?php if ( !empty($coa) ): ?>
														<?php foreach ($coa as $v_coa): ?>
															<option value="<?php echo $v_coa['coa']; ?>"><?php echo strtoupper($v_coa['nama_coa']); ?></option>
														<?php endforeach ?>
													<?php endif ?>
												</select>
											</td>
											<td class="coa text-center" style="vertical-align: middle;">-</td>
											<td>
												<select class="form-control sign" data-required="1">
													<option value="1">+1 (Normal)</option>
													<option value="-1">-1 (Balik Saldo)</option>
												</select>
											</td>
											<td>
												<div class="col-xs-12 no-padding">
													<div class="col-xs-6 no-padding" style="padding-right: 3px;">
														<button type="button" class="col-xs-12 btn btn-danger" style="padding: 6px 8px;" onclick="sr.removeRowItemGroup(this)"><i class="fa fa-minus"></i></button>
													</div>
													<div class="col-xs-6 no-padding" style="padding-left: 3px;">
														<button type="button" class="col-xs-12 btn btn-primary" style="padding: 6px 8px;" onclick="sr.addRowItemGroup(this)"><i class="fa fa-plus"></i></button>
													</div>
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</small>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="col-xs-12 no-padding">
		<hr style="margin-top: 10px; margin-bottom: 10px;">
	</div>
	<div class="col-xs-12 no-padding">
		<button type="button" class="col-xs-12 btn btn-primary" onclick="sr.save()"><i class="fa fa-save"></i> Simpan</button>
	</div>
</div>
