<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Nama</label></div>
	<div class="col-xs-12 no-padding">
		<input type="text" class="form-control text-left uppercase nama" data-required="1" placeholder="Nama" value="<?php echo $data['nama']; ?>" />
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Kode Voucher</label></div>
		<div class="col-xs-12 no-padding">
			<input type="text" class="form-control text-left uppercase kode_voucher" data-required="1" placeholder="Kode Voucher (MAX:5)" maxlength="5" value="<?php echo $data['kode_voucher']; ?>" />
		</div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label text-left">Peruntukan</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control peruntukan" data-required="1">
				<option>-- Pilih --</option>
				<option value="0" <?php echo (empty($data['unit']) || $data['unit'] == 0) ? 'selected' : ''; ?> >NON UNIT</option>
				<option value="1" <?php echo ($data['unit'] == 1) ? 'selected' : ''; ?>>UNIT</option>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<label class="checkbox-inline">
		<input type="checkbox" value="1" class="jurnal_manual cursor-p" <?php echo ($data['jurnal_manual'] == 1) ? 'checked' : null; ?> ><label class="control-label" style="margin-top: 0xp; padding-top: 0px;">Jurnal Manual</label>
	</label>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<label class="control-label">Detail Transaksi</label>
	<small>
		<table class="table table-bordered detail" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-1">Kode</th>
					<th class="col-xs-3">Nama</th>
					<th class="col-xs-3">Sumber</th>
					<th class="col-xs-3">Tujuan</th>
					<th class="col-xs-1 text-center">Submit Periode</th>
					<th class="col-xs-1"></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detail'] as $k_det => $v_det): ?>
					<tr>
						<td>
							<input type="text" class="form-control uppercase kode" placeholder="Kode" value="<?php echo $v_det['kode']; ?>" readonly>
						</td>
						<td>
							<input type="text" class="form-control uppercase nama_detail" data-required="1" placeholder="Nama" value="<?php echo $v_det['nama']; ?>">
						</td>
						<td>
							<select class="form-control sumber" data-required="1">
								<option value="">-- Pilih COA --</option>
								<?php foreach ($coa as $key => $value): ?>
									<?php
										$selected = null;
										if ( $value['coa'] == $v_det['sumber_coa'] ) {
											$selected = 'selected';
										}
									?>
									<option value="<?php echo $value['coa'] ?>" data-nama="<?php echo $value['nama_coa']; ?>" <?php echo $selected; ?> ><?php echo $value['coa'].' | '.$value['nama_coa']; ?></option>
								<?php endforeach ?>
							</select>
						</td>
						<td>
							<select class="form-control tujuan" data-required="1">
								<option value="">-- Pilih COA --</option>
								<?php foreach ($coa as $key => $value): ?>
									<?php
										$selected = null;
										if ( $value['coa'] == $v_det['tujuan_coa'] ) {
											$selected = 'selected';
										}
									?>
									<option value="<?php echo $value['coa'] ?>" data-nama="<?php echo $value['nama_coa']; ?>" <?php echo $selected; ?> ><?php echo $value['coa'].' | '.$value['nama_coa']; ?></option>
								<?php endforeach ?>
							</select>
						</td>
						<td class="text-center">
							<?php
								$checked = '';
								if ( $v_det['submit_periode'] == 1 ) {
									$checked = 'checked';
								}
							?>
							<input type="checkbox" class="cursor-p submit_periode" target="check" <?php echo $checked; ?> >
						</td>
						<td>
							<div class="col-xs-6 text-center no-padding">
								<button type="button" class="btn btn-primary" onclick="tj.addRow(this)"><i class="fa fa-plus"></i></button>
							</div>
							<div class="col-xs-6 text-center no-padding <?php echo !empty($v_det['kode']) ? 'hide' : ''; ?>">
								<button type="button" class="btn btn-danger" onclick="tj.removeRow(this)"><i class="fa fa-times"></i></button>
							</div>
						</td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</small>
</div>
<!-- <div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<label class="control-label">Sumber / Tujuan</label>
	<small>
		<table class="table table-bordered sumber_tujuan" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-10">Nama</th>
					<th class="col-xs-2"></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( !empty($data['sumber_tujuan']) ): ?>
					<?php foreach ($data['sumber_tujuan'] as $k_det => $v_det): ?>
						<tr>
							<td>
								<input type="text" class="form-control nama_detail" placeholder="Nama" value="<?php echo $v_det['nama']; ?>">
							</td>
							<td>
								<div class="col-xs-6 text-center no-padding">
									<button type="button" class="btn btn-primary" onclick="tj.addRow(this)"><i class="fa fa-plus"></i></button>
								</div>
								<div class="col-xs-6 text-center no-padding">
									<button type="button" class="btn btn-danger" onclick="tj.removeRow(this)"><i class="fa fa-times"></i></button>
								</div>
							</td>
						</tr>
					<?php endforeach ?>
				<?php else: ?>
					<tr>
						<td>
							<input type="text" class="form-control nama_detail" placeholder="Nama">
						</td>
						<td>
							<div class="col-xs-6 text-center no-padding">
								<button type="button" class="btn btn-primary" onclick="tj.addRow(this)"><i class="fa fa-plus"></i></button>
							</div>
							<div class="col-xs-6 text-center no-padding">
								<button type="button" class="btn btn-danger" onclick="tj.removeRow(this)"><i class="fa fa-times"></i></button>
							</div>
						</td>
					</tr>
				<?php endif ?>
			</tbody>
		</table>
	</small>
</div> -->
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="tj.edit(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-edit"></i> Simpan Perubahan</button>
</div>