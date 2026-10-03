<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-12 no-padding"><label class="control-label" style="padding-top: 0px;">Laporan : <?php echo $data['nama']; ?></label></div>
	</div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding">
			<table class="table table-bordered" style="margin-bottom: 0px;">
				<tbody>
					<?php foreach ($data['group'] as $v_group): ?>
						<?php $is_subtotal = ($v_group['tipe'] === 'subtotal'); ?>
						<tr class="group" style="<?php echo $is_subtotal ? 'background-color:#fff3cd;' : ''; ?>">
							<td class="col-xs-12">
								<div class="col-xs-12 no-padding" style="display:flex; gap:10px; align-items:center;">
									<div style="min-width:60px; text-align:center;">
										<small class="text-muted" style="display:block; font-size:10px;">URUTAN</small>
										<span class="badge" style="background:#555; font-size:13px;"><?php echo (int)($v_group['urut_group'] ?? 0); ?></span>
									</div>
									<div style="flex:2;">
										<strong><?php echo htmlspecialchars($v_group['nama']); ?></strong>
									</div>
									<div>
										<span class="label <?php echo $is_subtotal ? 'label-warning' : 'label-info'; ?>">
											<?php echo strtoupper($v_group['tipe']); ?>
										</span>
									</div>
									<?php if ($is_subtotal && !empty($v_group['ref_ordinals_display'])): ?>
										<div>
											<small class="text-muted">Ref: Group <?php echo htmlspecialchars($v_group['ref_ordinals_display']); ?></small>
										</div>
									<?php endif ?>
								</div>
							</td>
						</tr>
						<?php if (!$is_subtotal): ?>
							<tr class="item-group">
								<td style="background-color: #ededed;">
									<small>
										<table class="table table-bordered" style="margin-bottom: 0px;">
											<thead>
												<tr>
													<th class="col-xs-1">Urutan</th>
													<th class="col-xs-4">Item</th>
													<th class="col-xs-4">Nama COA</th>
													<th class="col-xs-1">No. COA</th>
													<th class="col-xs-2">Sign</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($v_group['urut'] as $k_urut => $v_urut): ?>
													<?php foreach ($v_urut['item'] as $v_item): ?>
														<tr>
															<td class="text-center"><?php echo $k_urut; ?></td>
															<td><?php echo $v_item['nama_item']; ?></td>
															<td><?php echo $v_item['nama_coa']; ?></td>
															<td class="text-center"><?php echo $v_item['coa']; ?></td>
															<td class="text-center">
																<?php $sign = isset($v_item['sign']) ? (int)$v_item['sign'] : 1; ?>
																<span class="label <?php echo ($sign === -1) ? 'label-danger' : 'label-success'; ?>">
																	<?php echo ($sign === -1) ? '-1 (Balik Saldo)' : '+1 (Normal)'; ?>
																</span>
															</td>
														</tr>
													<?php endforeach ?>
												<?php endforeach ?>
											</tbody>
										</table>
									</small>
								</td>
							</tr>
						<?php endif ?>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="col-xs-12 no-padding">
		<hr style="margin-top: 10px; margin-bottom: 10px;">
	</div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-4 no-padding" style="padding-right: 5px;">
			<button type="button" class="col-xs-12 btn btn-danger" onclick="sr.delete(this)" data-id="<?php echo $data['id'] ?>"><i class="fa fa-trash"></i> Hapus</button>
		</div>
		<div class="col-xs-4 no-padding" style="padding-right: 5px; padding-left: 5px;">
			<button type="button" class="col-xs-12 btn btn-default" onclick="sr.exportExcel(this)" data-id="<?php echo $data['id'] ?>"><i class="fa fa-file-excel-o"></i> Export Excel</button>
		</div>
		<div class="col-xs-4 no-padding" style="padding-left: 5px;">
			<button type="button" class="col-xs-12 btn btn-primary" onclick="sr.changeTabActive(this)" data-edit="edit" data-href="action" data-id="<?php echo $data['id'] ?>"><i class="fa fa-edit"></i> Edit</button>
		</div>
	</div>
</div>
