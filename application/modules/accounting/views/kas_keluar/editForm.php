<div class="col-xs-7 no-padding" style="padding-right: 5px;">
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">No. Kas Keluar</label></div>
		<div class="col-xs-4 no-padding">
			<input type="text" class="col-xs-12 form-control no_kk uppercase" placeholder="No. Kas Keluar" value="<?php echo $data['no_kk']; ?>" disabled>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">No. COA</label></div>
		<div class="col-xs-3 no-padding" style="padding-right: 5px;">
			<select class="form-control no_coa_header" data-required="1">
				<option value="">Pilih No. COA</option>
				<?php if ( !empty($coa_header) ): ?>
					<?php foreach ($coa_header as $k_coa => $v_coa): ?>
						<?php
							$selected = null;
							if ( $v_coa['no_coa'] == $data['no_coa'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $v_coa['no_coa']; ?>" data-nama="<?php echo $v_coa['nama_coa']; ?>" <?php echo $selected; ?> ><?php echo $v_coa['no_coa']; ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
		<div class="col-xs-6 no-padding" style="padding-left: 5px;">
			<select class="form-control nama_coa_header" data-required="1">
				<option value="">Pilih Nama COA</option>
				<?php if ( !empty($coa_header) ): ?>
					<?php foreach ($coa_header as $k_coa => $v_coa): ?>
						<?php
							$selected = null;
							if ( $v_coa['no_coa'] == $data['no_coa'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $v_coa['nama_coa']; ?>" data-no="<?php echo $v_coa['no_coa']; ?>" <?php echo $selected; ?> ><?php echo $v_coa['nama_coa']; ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Tanggal Kas Keluar</label></div>
		<div class="col-xs-4 no-padding">
			<div class="input-group date datetimepicker" name="tglKk" id="TglKk">
				<input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data['tgl_kk']; ?>" />
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Supplier</label></div>
		<div class="col-xs-7 no-padding">
			<select class="form-control supplier" data-nokk="<?php echo $data['no_kk']; ?>">
				<option value="">Pilih Supplier</option>
				<?php if ( !empty($supplier) ): ?>
					<?php foreach ($supplier as $k_supplier => $v_supplier): ?>
						<?php
							$selected = null;
							if ( $v_supplier['kode_supl'] == $data['kode_supl'] ) {
								$selected = 'selected';
							}
						?>
						<option value="<?php echo $v_supplier['kode_supl']; ?>" data-nama="<?php echo $v_supplier['nama_supl']; ?>" <?php echo $selected; ?> ><?php echo $v_supplier['kode_supl'].' | '.$v_supplier['nama_supl']; ?></option>
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
	<div class="col-xs-12 no-padding hide" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Nama Bank</label></div>
		<div class="col-xs-4 no-padding">
			<input type="text" class="col-xs-12 form-control nama_bank uppercase" placeholder="Nama Bank" maxlength="20" value="<?php echo $data['nama_bank']; ?>">
		</div>
	</div>
	<div class="col-xs-12 no-padding hide" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">No. Giro</label></div>
		<div class="col-xs-4 no-padding">
			<input type="text" class="col-xs-12 form-control no_giro uppercase" placeholder="No. Giro" maxlength="20" value="<?php echo $data['no_giro']; ?>">
		</div>
	</div>
	<div class="col-xs-12 no-padding hide" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Tanggal Tempo</label></div>
		<div class="col-xs-4 no-padding">
			<div class="input-group date datetimepicker" name="tglTempo" id="TglTempo">
				<input type="text" class="form-control text-center" placeholder="Tanggal" data-tgl="<?php echo $data['tgl_tempo']; ?>" />
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding hide" style="margin-bottom: 5px;">
		<div class="col-xs-3 no-padding"><label class="control-label">Tanggal Cair</label></div>
		<div class="col-xs-4 no-padding">
			<div class="input-group date datetimepicker" name="tglCair" id="TglCair">
				<input type="text" class="form-control text-center" placeholder="Tanggal" data-tgl="<?php echo $data['tgl_cair']; ?>" />
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
			</div>
		</div>
	</div>
</div>
<div class="col-xs-5 no-padding" style="padding-left: 5px;">
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-3">&nbsp;</div>
		<div class="col-xs-3 no-padding"><label class="control-label">Total</label></div>
		<div class="col-xs-6 no-padding nilai">
			<input type="text" class="col-xs-12 form-control text-right nilai uppercase" placeholder="Total" value="<?php echo formatAngka($data['nilai']); ?>" disabled>
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
						<th>No. Invoice</th>
						<th>Nilai Invoice</th>
						<th>Nilai</th>
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
									<?php
										$ket = '';
										if ( !empty($v_det['no_lpb']) ) {
											if ( !empty($v_det['keterangan']) ) {
												$ket = strtoupper($v_det['keterangan']);
											} else {
												$ket = strtoupper('Pelunasan Hutang a.n '.$data['nama_supl'].' / '.$v_det['no_lpb']);
											}
										} else {
											$ket = strtoupper($v_det['keterangan']);
										}
									?>
									<input type="text" class="form-control keterangan uppercase" placeholder="Keterangan" maxlength="50" value="<?php echo strtoupper($ket); ?>">
								</td>
								<td style="width: 20%; max-width: 20%;">
									<select class="form-control lpb" data-val="<?php echo $v_det['no_lpb']; ?>">
										<option value="">Pilih No. Invoice</option>
									</select>
								</td>
								<td style="width: 10%; max-width: 10%;">
									<input type="text" class="form-control text-right nilai_lpb uppercase" placeholder="Nilai Invoice" data-tipe="decimal" maxlength="19" value="<?php echo formatAngka($v_det['nilai_lpb']); ?>" disabled>
								</td>
								<td style="width: 10%; max-width: 10%;">
									<input type="text" class="form-control text-right nilai uppercase" placeholder="Nilai" data-tipe="decimal" maxlength="19" data-required="1" onblur="kk.hitGrandTotal(this)" value="<?php echo formatAngka($v_det['nilai']); ?>">
								</td>
								<td style="width: 10%; max-width: 10%;">
									<div class="col-xs-12 no-padding">
										<div class="col-xs-6 no-padding" style="padding-right: 3px;">
											<button type="button" class="col-xs-12 btn btn-danger" onclick="kk.removeRow(this)"><i class="fa fa-times"></i></button>
										</div>
										<div class="col-xs-6 no-padding" style="padding-left: 3px;">
											<button type="button" class="col-xs-12 btn btn-primary" onclick="kk.addRow(this)"><i class="fa fa-plus"></i></button>
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
							<td style="width: 20%; max-width: 20%;">
								<select class="form-control lpb">
									<option value="">Pilih No. Invoice</option>
								</select>
							</td>
							<td style="width: 10%; max-width: 10%;">
								<input type="text" class="form-control text-right nilai_lpb uppercase" placeholder="Nilai Invoice" data-tipe="decimal" maxlength="19" disabled>
							</td>
							<td style="width: 10%; max-width: 10%;">
								<input type="text" class="form-control text-right nilai uppercase" placeholder="Nilai" data-tipe="decimal" maxlength="19" data-required="1" onblur="kk.hitGrandTotal(this)">
							</td>
							<td style="width: 10%; max-width: 10%;">
								<div class="col-xs-12 no-padding">
									<div class="col-xs-6 no-padding" style="padding-right: 3px;">
										<button type="button" class="col-xs-12 btn btn-danger" onclick="kk.removeRow(this)"><i class="fa fa-times"></i></button>
									</div>
									<div class="col-xs-6 no-padding" style="padding-left: 3px;">
										<button type="button" class="col-xs-12 btn btn-primary" onclick="kk.addRow(this)"><i class="fa fa-plus"></i></button>
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
    <button type="button" class="btn btn-primary pull-right" onclick="kk.edit(this)" data-kode="<?php echo $data['no_kk']; ?>" style="margin-left: 5px;">
        <i class="fa fa-save"></i>
        Update
    </button>
    <button type="button" class="btn btn-danger pull-right" onclick="kk.changeTabActive(this)" data-href="action" data-edit="" data-kode="<?php echo $data['no_kk']; ?>" style="margin-right: 5px;">
        <i class="fa fa-times"></i>
        Batal
    </button>
</div>