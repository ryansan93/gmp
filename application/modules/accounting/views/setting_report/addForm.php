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
							<div class="col-xs-12 no-padding">
								<div class="col-xs-12 no-padding"><label class="control-label" style="padding-top: 0px;">Nama Group</label></div>
								<div class="col-xs-12 no-padding">
									<input type="text" class="col-xs-12 form-control nama_group" data-required="1" placeholder="Nama">
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
											<th class="col-xs-2">Posisi Laporan</th>
											<th class="col-xs-2">Posisi Jurnal</th>
											<th class="col-xs-1">Posisi Data</th>
											<th class="col-xs-1"></th>
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
														<?php foreach ($item_report as $k_ir => $v_ir): ?>
															<option value="<?php echo $v_ir['id']; ?>"><?php echo strtoupper($v_ir['nama']); ?></option>
														<?php endforeach ?>
													<?php endif ?>
												</select>
											</td>
											<td>
												<select class="form-control nama_coa" data-required="1">
													<option value="">-- Pilih COA --</option>
													<?php if ( !empty($coa) ): ?>
														<?php foreach ($coa as $k_coa => $v_coa): ?>
															<option value="<?php echo $v_coa['coa']; ?>"><?php echo strtoupper($v_coa['nama_coa']); ?></option>
														<?php endforeach ?>
													<?php endif ?>
												</select>
											</td>
											<td class="coa text-center" style="vertical-align: middle;">
												-
											</td>
											<td>
												<select class="form-control posisi" data-required="1">
													<option value="">-- Pilih Posisi Laporan --</option>
													<option value="debet">DEBET</option>
													<option value="kredit">KREDIT</option>
												</select>
											</td>
											<td>
												<select class="form-control posisi_jurnal" data-required="1">
													<option value="">-- Pilih Posisi Jurnal --</option>
													<option value="asal">ASAL</option>
													<option value="tujuan">TUJUAN</option>
												</select>
											</td>
											<td>
												<select class="form-control posisi_data" data-required="1">
													<option value="">-- Pilih Posisi Data --</option>
													<option value="jurnal">JURNAL</option>
													<option value="saldo">SALDO</option>
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