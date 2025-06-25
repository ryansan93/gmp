<table class="table table-nobordered list_data" style="width: 100%; margin-bottom: 0px;">
	<tbody>
		<tr>
			<td style="width: 20%;">Saldo Bank (Rp.)</td>
			<td style="width: 25%;">
				<input type="text" class="form-control saldo_bank" data-required="1" placeholder="NILAI" data-tipe="decimal" value="<?php echo angkaDecimal($data['saldo_bank']); ?>">
			</td>
			<td style="width: 55%;"></td>
		</tr>
		<tr>
			<td>Total Transfer (Rp.)</td>
			<td>
				<input type="text" class="form-control tot_transfer" data-required="1" placeholder="NILAI" data-tipe="decimal" value="<?php echo angkaDecimal($data['total_transfer']); ?>">
			</td>
		</tr>
		<?php $tot_hutang = 0; ?>
		<tr>
			<td>Hutang Supplier</td>
			<td colspan="3">
				<table class="table table-nobordered list_data_hutang" style="margin-bottom: 0px;">
					<tbody>
						<?php if ( !empty($data['hutang_pakan']) && count($data['hutang_pakan']) ): ?>
							<?php foreach ($data['hutang_pakan'] as $k_hp => $v_hp): ?>
								<tr>
									<td style="width: 40%;">
										<div class="col-xs-12 no-padding">
											<select class="form-control supplier" data-required="1">
												<option value="">-- Pilih Supplier --</option>
												<?php foreach ($supplier as $k_supplier => $v_supplier): ?>
													<?php
														$selected = null;
														if ( $v_supplier['nomor'] == $v_hp['supplier'] ) {
															$selected = 'selected';
														}
													?>
													<option value="<?php echo $v_supplier['nomor']; ?>" <?php echo $selected; ?> ><?php echo strtoupper($v_supplier['nama']); ?></option>
												<?php endforeach ?>
											</select>
										</div>
										<div class="col-xs-12 no-padding">
											<div class="col-xs-12 no-padding">
												<label class="control-label">Nota Terlama (Hari)</label>
											</div>
											<div class="col-xs-3 no-padding">
												<input type="text" class="form-control nota_terlama" data-required="1" placeholder="HARI" data-tipe="integer" value="<?php echo selisihTanggal($v_hp['tgl_sj_terlama'], $tanggal); ?>">
											</div>
										</div>
									</td>
									<td style="width: 25%; padding-left: 10px;">
										<input type="text" class="form-control hutang nilai_hutang" data-required="1" placeholder="NILAI" data-tipe="decimal" value="<?php echo angkaDecimal($v_hp['tot_hutang']); ?>">
									</td>
									<td style="width: 30%; padding-left: 10px;">
										<button type="button" class="btn btn-primary" onclick="sld.addRow(this);"><i class="fa fa-plus"></i></button>
										<button type="button" class="btn btn-danger" onclick="sld.removeRow(this);"><i class="fa fa-times"></i></button>
									</td>
								</tr>
								<?php $tot_hutang += $v_hp['tot_hutang']; ?>
							<?php endforeach ?>
						<?php else: ?>
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
						<?php endif ?>
					</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<td>Hutang BCA (Rp.)</td>
			<td>
				<input type="text" class="form-control hutang hut_bca" data-required="1" placeholder="NILAI" data-tipe="decimal" value="<?php echo angkaDecimal($data['hutang_bca']); ?>">
			</td>
		</tr>
		<tr>
			<td>Total Hutang (Rp.)</td>
			<td>
				<?php $tot_hutang += $data['hutang_bca']; ?>
				<input type="text" class="form-control tot_hutang" data-required="1" placeholder="NILAI" data-tipe="decimal" disabled value="<?php echo angkaDecimal($tot_hutang); ?>">
			</td>
		</tr>
		<?php $tot_lr = 0; ?>
		<tr>
			<td>L/R Sebelumnya (Rp.)</td>
			<td>
				<?php $tot_lr += $data['lr_kemarin']; ?>
				<input type="text" class="form-control lr lr_sebelumnya" data-required="1" placeholder="NILAI" data-tipe="decimal" value="<?php echo angkaDecimal($data['lr_kemarin']); ?>">
			</td>
		</tr>
		<tr>
			<td>L/R Hari Ini (Rp.)</td>
			<td>
				<?php $tot_lr += $data['lr_today']; ?>
				<input type="text" class="form-control lr lr_hari_ini" data-required="1" placeholder="NILAI" data-tipe="decimal" value="<?php echo angkaDecimal($data['lr_today']); ?>">
			</td>
		</tr>
		<tr>
			<td>CN Pakan (Rp.)</td>
			<td>
				<?php $tot_lr += $data['cn_pakan']; ?>
				<input type="text" class="form-control lr cn_pakan" data-required="1" placeholder="NILAI" data-tipe="decimal" value="<?php echo angkaDecimal($data['cn_pakan']); ?>">
			</td>
		</tr>
		<tr>
			<td>CN DOC (Rp.)</td>
			<td>
				<?php $tot_lr += $data['cn_doc']; ?>
				<input type="text" class="form-control lr cn_doc" data-required="1" placeholder="NILAI" data-tipe="decimal" value="<?php echo angkaDecimal($data['cn_doc']); ?>">
			</td>
		</tr>
		<tr>
			<td>Total L/R (Rp.)</td>
			<td>
				<input type="text" class="form-control tot_lr" data-required="1" placeholder="NILAI" data-tipe="decimal" disabled value="<?php echo angkaDecimal($tot_lr); ?>">
			</td>
		</tr>
		<tr>
			<td>RHPP Selesai Hari Ini</td>
			<td>
				<input type="text" class="form-control rhpp_selesai" data-required="1" placeholder="JUMLAH" data-tipe="integer" value="<?php echo angkaRibuan($data['jumlah_rhpp_today']); ?>">
			</td>
			<td style="padding-left: 10px;">
				<div class="col-xs-2 no-padding">
					<input type="text" class="form-control rhpp_selesai_box" data-required="1" placeholder="BOX" data-tipe="integer" value="<?php echo angkaRibuan($data['jumlah_rhpp_box_today']); ?>">
				</div>
				<div class="col-xs-1">Box</div>
			</td>
		</tr>
		<tr>
			<td>Laba (Per Ekor)</td>
			<td>
				<input type="text" class="form-control laba_per_ekor" data-required="1" placeholder="NILAI" data-tipe="decimal" value="<?php echo angkaDecimal($data['laba_per_ekor']); ?>">
			</td>
		</tr>
		<tr>
			<td>Harga Rata2 Ayam</td>
			<td>
				<input type="text" class="form-control harga_rata_ayam" data-required="1" placeholder="NILAI" data-tipe="decimal" value="<?php echo angkaDecimal($data['harga_rata_ayam']); ?>">
			</td>
		</tr>
		<tr>
			<td>Harga Rata DOC</td>
			<td>
				<input type="text" class="form-control harga_rata_doc" data-required="1" placeholder="NILAI" data-tipe="decimal" value="<?php echo angkaDecimal($data['harga_rata_doc']); ?>">
			</td>
		</tr>
	</tbody>
</table>