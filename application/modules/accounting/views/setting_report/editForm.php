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
					<?php foreach ($data['group'] as $k_group => $v_group): ?>						
						<tr class="group">
							<td class="col-xs-10">
								<div class="col-xs-12 no-padding">
									<div class="col-xs-12 no-padding"><label class="control-label" style="padding-top: 0px;">Nama Group</label></div>
									<div class="col-xs-12 no-padding">
										<input type="text" class="col-xs-12 form-control nama_group" data-required="1" placeholder="Nama" value="<?php echo $v_group['nama']; ?>">
									</div>
								</div>
							</td>
							<td class="col-xs-2">
								<div class="col-xs-12 no-padding">
									<div class="col-xs-12 no-padding"><label class="control-label" style="padding-top: 0px;">&nbsp</label></div>
									<div class="col-xs-12 no-padding">
										<div class="col-xs-6 no-padding" style="padding-right: 5px;">
											<button type="button" class="col-xs-12 btn btn-danger" onclick="sr.removeRowGroup(this)"><i class="fa fa-minus"></i></button>
										</div>
										<div class="col-xs-6 no-padding" style="padding-left: 5px;">
											<button type="button" class="col-xs-12 btn btn-primary" onclick="sr.addRowGroup(this)"><i class="fa fa-plus"></i></button>
										</div>
									</div>
								</div>
							</td>
						</tr>
						<tr class="item-group">
							<td colspan="2" style="background-color: #ededed;">
								<small>
									<table class="table table-bordered" style="margin-bottom: 0px;">
										<thead>
											<tr>
												<th class="col-xs-1">Urutan</th>
												<th class="col-xs-2">Item</th>
												<th class="col-xs-2">Nama COA</th>
												<th class="col-xs-1">No. COA</th>
												<th class="col-xs-2">Posisi COA</th>
												<th class="col-xs-2">Posisi Jurnal</th>
												<th class="col-xs-1">Posisi Data</th>
												<th class="col-xs-1"></th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($v_group['item'] as $k_item => $v_item): ?>
												<tr>
													<td>
														<input type="text" class="form-control text-center urut" data-required="1" data-tipe="integer" placeholder="No." value="<?php echo $v_item['urut']; ?>">
													</td>
													<td>
														<select class="form-control item" data-required="1">
															<option value="">-- Pilih Item --</option>
															<?php if ( !empty($item_report) ): ?>
																<?php foreach ($item_report as $k_ir => $v_ir): ?>
																	<?php
																		$selected = null;
																		if ( $v_ir['id'] == $v_item['item_report_id'] ) {
																			$selected = 'selected';
																		}
																	?>
																	<option value="<?php echo $v_ir['id']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_ir['nama']); ?></option>
																<?php endforeach ?>
															<?php endif ?>
														</select>
													</td>
													<td>
														<select class="form-control nama_coa" data-required="1">
															<option value="">-- Pilih COA --</option>
															<?php if ( !empty($coa) ): ?>
																<?php foreach ($coa as $k_coa => $v_coa): ?>
																	<?php
																		$selected = null;
																		if ( $v_coa['coa'] == $v_item['coa'] ) {
																			$selected = 'selected';
																		}
																	?>
																	<option value="<?php echo $v_coa['coa']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_coa['nama_coa']); ?></option>
																<?php endforeach ?>
															<?php endif ?>
														</select>
													</td>
													<td class="coa text-center" style="vertical-align: middle;">
														<?php echo $v_item['coa']; ?>
													</td>
													<td>
														<select class="form-control posisi" data-required="1">
															<option value="">-- Pilih Posisi Laporan --</option>
															<option value="debet" <?php echo ($v_item['posisi'] == 'debet') ? 'selected' : null; ?> >DEBET</option>
															<option value="kredit" <?php echo ($v_item['posisi'] == 'kredit') ? 'selected' : null; ?> >KREDIT</option>
														</select>
													</td>
													<td>
														<select class="form-control posisi_jurnal" data-required="1">
															<option value="">-- Pilih Posisi Jurnal --</option>
															<option value="asal" <?php echo ($v_item['posisi_jurnal'] == 'asal') ? 'selected' : null; ?> >ASAL</option>
															<option value="tujuan" <?php echo ($v_item['posisi_jurnal'] == 'tujuan') ? 'selected' : null; ?> >TUJUAN</option>
														</select>
													</td>
													<td>
														<select class="form-control posisi_data" data-required="1">
															<option value="">-- Pilih Posisi Data --</option>
															<option value="jurnal" <?php echo ($v_item['posisi_data'] == 'jurnal') ? 'selected' : null; ?> >JURNAL</option>
															<option value="saldo" <?php echo ($v_item['posisi_data'] == 'saldo') ? 'selected' : null; ?> >SALDO</option>
														</select>
													</td>
													<td>
														<div class="col-xs-12 no-padding">
															<div class="col-xs-6 no-padding" style="padding-right: 5px;">
																<button type="button" class="col-xs-12 btn btn-danger" style="padding: 6px 10px;" onclick="sr.removeRowItemGroup(this)"><i class="fa fa-minus"></i></button>
															</div>
															<div class="col-xs-6 no-padding" style="padding-left: 5px;">
																<button type="button" class="col-xs-12 btn btn-primary" style="padding: 6px 10px;" onclick="sr.addRowItemGroup(this)"><i class="fa fa-plus"></i></button>
															</div>
														</div>
													</td>
												</tr>
											<?php endforeach ?>
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