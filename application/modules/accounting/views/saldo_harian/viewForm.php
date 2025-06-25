<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding">
		<div class="col-xs-2 no-padding">
			<label class="control-label">Tanggal</label>
		</div>
		<div class="col-xs-10 no-padding">
			<label class="control-label">: <?php echo strtoupper(tglIndonesia($data['tanggal'], '-', ' ')); ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-2 no-padding">
			<label class="control-label">Perusahaan</label>
		</div>
		<div class="col-xs-10 no-padding">
			<label class="control-label">: <?php echo strtoupper($data['d_perusahaan']['perusahaan']); ?></label>
		</div>
	</div>
	<div class="col-xs-12 no-padding">
		<hr style="margin-top: 10px; margin-bottom: 10px;">
	</div>
	<div class="col-xs-12 no-padding">
		<table class="table table-nobordered list_data" style="width: 100%; margin-bottom: 0px;">
			<tbody>
				<tr>
					<td style="width: 20%;">Saldo Bank (Rp.)</td>
					<td style="width: 25%;">: <?php echo angkaDecimal($data['saldo_harian_det']['bank_awal']); ?></td>
					<td style="width: 55%;"></td>
				</tr>
				<tr>
					<td>Total Transfer (Rp.)</td>
					<td>: <?php echo angkaDecimal($data['saldo_harian_det']['tot_transfer']); ?></td>
				</tr>
				<tr>
					<td>Hutang Supplier</td>
					<td colspan="3">
						<table class="table table-nobordered list_data_hutang" style="margin-bottom: 0px;">
							<tbody>
								<?php foreach ($data['saldo_harian_det']['saldo_harian_det_hutang'] as $k_shdh => $v_shdh): ?>
									<tr>
										<td style="width: 40%;">
											<div class="col-xs-12 no-padding">
												<?php echo strtoupper($v_shdh['d_supplier']['nama']); ?>
											</div>
											<div class="col-xs-12 no-padding">
												<div class="col-xs-12 no-padding">
													<label class="control-label">Nota Terlama (Hari) : <?php echo selisihTanggal($v_shdh['tgl_sj_terlama'], $data['tanggal']); ?></label>
												</div>
											</div>
										</td>
										<td style="width: 25%; padding-left: 10px;">
											<?php echo angkaDecimal($v_shdh['hutang']); ?>
										</td>
										<td style="width: 30%; padding-left: 10px;">
										</td>
									</tr>
								<?php endforeach ?>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td>Hutang BCA (Rp.)</td>
					<td>: <?php echo angkaDecimal($data['saldo_harian_det']['hutang_bca']); ?></td>
				</tr>
				<tr>
					<td>Total Hutang (Rp.)</td>
					<td>: <?php echo angkaDecimal($data['saldo_harian_det']['tot_hutang']); ?></td>
				</tr>
				<tr>
					<td>L/R Sebelumnya (Rp.)</td>
					<td>: <?php echo angkaDecimal($data['saldo_harian_det']['lr_kemarin']); ?></td>
				</tr>
				<tr>
					<td>L/R Hari Ini (Rp.)</td>
					<td>: <?php echo angkaDecimal($data['saldo_harian_det']['lr_today']); ?></td>
				</tr>
				<tr>
					<td>CN Pakan (Rp.)</td>
					<td>: <?php echo angkaDecimal($data['saldo_harian_det']['cn_pakan']); ?></td>
				</tr>
				<tr>
					<td>CN DOC (Rp.)</td>
					<td>: <?php echo angkaDecimal($data['saldo_harian_det']['cn_doc']); ?></td>
				</tr>
				<tr>
					<td>Total L/R (Rp.)</td>
					<td>: <?php echo angkaDecimal($data['saldo_harian_det']['tot_lr']); ?></td>
				</tr>
				<tr>
					<td>RHPP Selesai Hari Ini</td>
					<td>: <?php echo angkaRibuan($data['saldo_harian_det']['jumlah_rhpp']).' Hari ('.angkaRibuan($data['saldo_harian_det']['jumlah_rhpp_box']).' Box)'; ?></td>
				</tr>
				<tr>
					<td>Laba (Per Ekor)</td>
					<td>: <?php echo angkaDecimal($data['saldo_harian_det']['laba_ekor']); ?></td>
				</tr>
				<tr>
					<td>Harga Rata2 Ayam</td>
					<td>: <?php echo angkaDecimal($data['saldo_harian_det']['harga_rata_lb']); ?></td>
				</tr>
				<tr>
					<td>Harga Rata DOC</td>
					<td>: <?php echo angkaDecimal($data['saldo_harian_det']['harga_rata_doc']); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="col-xs-12 no-padding">
		<hr style="margin-top: 10px; margin-bottom: 10px;">
	</div>
	<div class="col-xs-12 no-padding">
		<div class="col-xs-6 no-padding" style="padding-right: 5px;">
			<button type="button" class="col-xs-12 btn btn-danger cursor-p" onclick="sld.delete(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-trash"></i> Hapus</button>
		</div>
		<div class="col-xs-6 no-padding" style="padding-left: 5px;">
			<button type="button" class="col-xs-12 btn btn-primary cursor-p" onclick="sld.changeTabActive(this)" data-href="action" data-edit="edit" data-id="<?php echo $data['id']; ?>"><i class="fa fa-edit"></i> Edit</button>
		</div>
	</div>
</div>
