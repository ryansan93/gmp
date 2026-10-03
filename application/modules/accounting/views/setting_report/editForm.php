<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding"><label class="control-label" style="padding-top: 0px;">Nama Laporan</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="col-xs-12 form-control nama_laporan" data-required="1" placeholder="Nama" value="<?php echo $data['nama']; ?>">
		</div>
	</div>
	<div class="col-xs-12 no-padding"><br></div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding">
			<table class="table table-bordered" style="margin-bottom: 0px;">
				<tbody>
					<?php foreach ($data['group'] as $v_group): ?>
						<?php $is_subtotal = ($v_group['tipe'] === 'subtotal'); ?>
						<tr class="group">
							<td class="col-xs-10">
								<div class="col-xs-12 no-padding" style="display:flex; gap:6px; align-items:flex-end;">
									<div style="flex:0.5;">
										<label>Urutan</label>
										<input type="text" class="form-control text-center urut_group" data-required="1" data-tipe="integer" placeholder="No." value="<?php echo (int)($v_group['urut'] ?? $v_group['ordinal']); ?>">
									</div>
									<div style="flex:2;">
										<label>Nama Group <span class="badge group-badge" style="background:#777;"><?php echo $v_group['ordinal']; ?></span></label>
										<input type="text" class="form-control nama_group" data-required="1" placeholder="Nama Group" value="<?php echo htmlspecialchars($v_group['nama']); ?>">
									</div>
									<div style="flex:1;">
										<label>Tipe</label>
										<select class="form-control tipe_group" data-required="1">
											<option value="data"     <?php echo (!$is_subtotal) ? 'selected' : ''; ?>>DATA</option>
											<option value="subtotal" <?php echo $is_subtotal    ? 'selected' : ''; ?>>SUBTOTAL</option>
										</select>
									</div>
									<div style="flex:1; <?php echo !$is_subtotal ? 'display:none;' : ''; ?>" class="ref-group-wrapper">
										<label>Ref. No. Urut <small class="text-muted">(pisah koma)</small></label>
										<input type="text" class="form-control ref_group_ids" placeholder="misal: 1,2" value="<?php echo htmlspecialchars($v_group['ref_ordinals_display'] ?? ''); ?>">
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
						<tr class="item-group" <?php echo $is_subtotal ? 'style="display:none;"' : ''; ?>>
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
											<?php if (!empty($v_group['item'])): ?>
												<?php foreach ($v_group['item'] as $v_item): ?>
													<tr>
														<td>
															<input type="text" class="form-control text-center urut" data-required="1" data-tipe="integer" placeholder="No." value="<?php echo $v_item['urut']; ?>">
														</td>
														<td>
															<select class="form-control item" data-required="1">
																<option value="">-- Pilih Item --</option>
																<?php foreach ($item_report as $v_ir): ?>
																	<option value="<?php echo $v_ir['id']; ?>" <?php echo ($v_ir['id'] == $v_item['item_report_id']) ? 'selected' : ''; ?>>
																		<?php echo strtoupper($v_ir['nama']); ?>
																	</option>
																<?php endforeach ?>
															</select>
														</td>
														<td>
															<select class="form-control nama_coa" data-required="1">
																<option value="">-- Pilih COA --</option>
																<?php foreach ($coa as $v_coa): ?>
																	<option value="<?php echo $v_coa['coa']; ?>" <?php echo ($v_coa['coa'] == $v_item['coa']) ? 'selected' : ''; ?>>
																		<?php echo strtoupper($v_coa['nama_coa']); ?>
																	</option>
																<?php endforeach ?>
															</select>
														</td>
														<td class="coa text-center" style="vertical-align: middle;"><?php echo $v_item['coa']; ?></td>
														<td>
															<select class="form-control sign" data-required="1">
																<option value="1"  <?php echo ((int)$v_item['sign'] != -1) ? 'selected' : ''; ?>>+1 (Normal)</option>
																<option value="-1" <?php echo ((int)$v_item['sign'] === -1) ? 'selected' : ''; ?>>-1 (Balik Saldo)</option>
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
												<?php endforeach ?>
											<?php else: ?>
												<tr>
													<td><input type="text" class="form-control text-center urut" data-required="1" data-tipe="integer" placeholder="No."></td>
													<td>
														<select class="form-control item" data-required="1">
															<option value="">-- Pilih Item --</option>
															<?php foreach ($item_report as $v_ir): ?>
																<option value="<?php echo $v_ir['id']; ?>"><?php echo strtoupper($v_ir['nama']); ?></option>
															<?php endforeach ?>
														</select>
													</td>
													<td>
														<select class="form-control nama_coa" data-required="1">
															<option value="">-- Pilih COA --</option>
															<?php foreach ($coa as $v_coa): ?>
																<option value="<?php echo $v_coa['coa']; ?>"><?php echo strtoupper($v_coa['nama_coa']); ?></option>
															<?php endforeach ?>
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
											<?php endif ?>
										</tbody>
									</table>
								</small>
							</td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="col-xs-12 no-padding">
		<hr style="margin-top: 10px; margin-bottom: 10px;">
	</div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-6 no-padding" style="padding-right: 5px;">
			<button type="button" class="col-xs-12 btn btn-danger" onclick="sr.changeTabActive(this)" data-id="<?php echo $data['id']; ?>" data-href="action" data-edit=""><i class="fa fa-times"></i> Batal</button>
		</div>
		<div class="col-xs-6 no-padding" style="padding-left: 5px;">
			<button type="button" class="col-xs-12 btn btn-primary" onclick="sr.edit(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
		</div>
	</div>
</div>
