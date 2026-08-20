<div class="modal-header">
	<span class="modal-title"><b>EDIT JURNAL OTOMATIS</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body" style="padding-bottom: 0px;">
	<div class="row detailed">
		<div class="col-xs-12 detailed no-padding">
			<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
				<div class="alert alert-warning" style="display: block; margin-bottom: 0;">
					<i class="fa fa-warning"></i> Perubahan di sini langsung mengubah jurnal otomatis yang sudah ada. Kontrol: (1) <b>Total Debet harus sama dengan Total Kredit</b> (balance), dan (2) <b>Total nominal keseluruhan tidak boleh berbeda dari total sebelumnya</b>, yaitu <b><?php echo angkaDecimal($total_existing); ?></b>. <b>Catatan:</b> jika data pembayaran ini di-edit/di-simpan ulang di kemudian hari, jurnal akan di-generate ulang otomatis dan perubahan manual ini bisa ke-timpa.
				</div>
			</div>
			<?php if ( count($jurnal_existing) > 0 ): ?>
				<div class="col-xs-12 no-padding" style="margin-bottom: 5px;" id="tanggal_jurnal" data-tgl="<?php echo $jurnal_existing[0]['tanggal']; ?>">
					<b>Tanggal Jurnal</b> : <?php echo tglIndonesia($jurnal_existing[0]['tanggal'], '-', ' '); ?>
				</div>
			<?php endif ?>
			<div class="col-xs-12 no-padding">
				<small>
					<div class="table-responsive">
					<table class="table table-bordered" id="tbl_edit_jurnal" style="margin-bottom: 0px;">
						<thead>
							<tr>
								<th class="text-center">Asal (COA)</th>
								<th class="text-center" style="width: 70px;">Unit Asal</th>
								<th class="text-center">Tujuan (COA)</th>
								<th class="text-center" style="width: 70px;">Unit Tujuan</th>
								<th class="text-center" style="width: 140px;">Nominal</th>
								<th class="text-center">Keterangan</th>
								<th class="text-center" style="width: 70px;"></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( count($jurnal_existing) > 0 ): ?>
								<?php foreach ($jurnal_existing as $v_dj): ?>
									<tr data-id="<?php echo $v_dj['id']; ?>">
										<td>
											<select class="form-control asal">
												<option value="">Pilih COA</option>
												<?php foreach ($coa as $k_coa => $v_coa): ?>
													<?php $selected = ( $v_coa['no_coa'] == $v_dj['coa_asal'] ) ? 'selected' : null; ?>
													<option value="<?php echo $v_coa['no_coa']; ?>" data-nama="<?php echo $v_coa['nama_coa']; ?>" <?php echo $selected; ?> ><?php echo $v_coa['no_coa'].' | '.$v_coa['nama_coa']; ?></option>
												<?php endforeach ?>
											</select>
										</td>
										<td>
											<select class="form-control unit_asal">
												<option value="">-</option>
												<?php foreach ($unit as $k_unit => $v_unit): ?>
													<?php $selected = ( $v_unit['kode'] == $v_dj['unit'] ) ? 'selected' : null; ?>
													<option value="<?php echo $v_unit['kode']; ?>" <?php echo $selected; ?> ><?php echo $v_unit['kode']; ?></option>
												<?php endforeach ?>
											</select>
										</td>
										<td>
											<select class="form-control tujuan">
												<option value="">Pilih COA</option>
												<?php foreach ($coa as $k_coa => $v_coa): ?>
													<?php $selected = ( $v_coa['no_coa'] == $v_dj['coa_tujuan'] ) ? 'selected' : null; ?>
													<option value="<?php echo $v_coa['no_coa']; ?>" data-nama="<?php echo $v_coa['nama_coa']; ?>" <?php echo $selected; ?> ><?php echo $v_coa['no_coa'].' | '.$v_coa['nama_coa']; ?></option>
												<?php endforeach ?>
											</select>
										</td>
										<td>
											<select class="form-control unit_tujuan">
												<option value="">-</option>
												<?php foreach ($unit as $k_unit => $v_unit): ?>
													<?php $selected = ( $v_unit['kode'] == $v_dj['unit_tujuan'] ) ? 'selected' : null; ?>
													<option value="<?php echo $v_unit['kode']; ?>" <?php echo $selected; ?> ><?php echo $v_unit['kode']; ?></option>
												<?php endforeach ?>
											</select>
										</td>
										<td>
											<input type="text" class="form-control text-right nominal" data-tipe="decimal" maxlength="20" placeholder="Nominal" value="<?php echo angkaDecimal($v_dj['nominal']); ?>" onblur="vp.hitTotalJurnal()">
										</td>
										<td>
											<input type="text" class="form-control keterangan" placeholder="Keterangan" value="<?php echo $v_dj['keterangan']; ?>">
										</td>
										<td>
											<div class="text-center" style="margin-bottom: 5px;">
												<button type="button" class="btn btn-primary btn-xs" onclick="vp.addRowJurnal(this)"><i class="fa fa-plus"></i></button>
											</div>
											<div class="text-center">
												<button type="button" class="btn btn-danger btn-xs" onclick="vp.removeRowJurnal(this)"><i class="fa fa-times"></i></button>
											</div>
										</td>
									</tr>
								<?php endforeach ?>
							<?php else: ?>
								<tr>
									<td colspan="7" class="text-center">Belum ada jurnal</td>
								</tr>
							<?php endif ?>
						</tbody>
						<?php if ( count($jurnal_existing) > 0 ): ?>
							<tfoot>
								<tr>
									<td colspan="2" class="text-right">Total Kredit<br><b id="total_kredit_jurnal">0,00</b></td>
									<td colspan="2" class="text-right">Total Debet<br><b id="total_debet_jurnal">0,00</b></td>
									<td class="text-right">Total<br><b id="total_nominal_jurnal" data-original="<?php echo $total_existing; ?>">0,00</b></td>
									<td colspan="2" class="text-muted"><small>Sebelumnya:<br><?php echo angkaDecimal($total_existing); ?></small></td>
								</tr>
							</tfoot>
						<?php endif ?>
					</table>
					</div>
				</small>
			</div>
			<?php if ( count($jurnal_existing) > 0 ): ?>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
					<button type="button" class="btn btn-primary pull-right" onclick="vp.saveEditJurnal(this)" data-id="<?php echo $id; ?>" data-table="<?php echo $tbl_name; ?>"><i class="fa fa-save"></i> Simpan</button>
				</div>
			<?php endif ?>
		</div>
	</div>
</div>
