<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Tanggal</label></div>
	<div class="col-xs-12 no-padding">
		<div class="input-group date" id="tanggal">
	        <input type="text" class="form-control text-center" data-required="1" placeholder="Start Date" data-val="<?php echo $data['tanggal']; ?>" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Transaksi</label></div>
	<div class="col-xs-12 no-padding">
		<select class="form-control jurnal_trans" data-required="1" onchange="jurnal.getJurnalTrans()">
			<option value="" > Pilih </option>
			<?php foreach ($jurnal_trans as $k_jt => $v_jt): ?>
				<?php
					$selected = null;
					if ( $v_jt['id'] == $data['jurnal_trans_id'] ) {
						$selected = 'selected';
					}
				?>
				<option value="<?php echo $v_jt['id']; ?>" <?php echo $selected; ?> > <?php echo strtoupper($v_jt['nama']); ?> </option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Unit</label></div>
	<div class="col-xs-12 no-padding">
		<!-- <select class="unit" name="unit[]" multiple="multiple" width="100%" data-required="1"> -->
		<select class="unit" width="100%" data-required="1">
			<option value="all" <?php echo ($data['unit'] == 'all') ? 'selected' : ''; ?> > ALL </option>
			<option value="pusat" <?php echo ($data['unit'] == 'pusat') ? 'selected' : ''; ?> > PUSAT </option>
			<?php foreach ($unit as $key => $v_unit): ?>
				<?php
					$selected = null;
					if ( $v_unit['kode'] == $data['unit'] ) {
						$selected = 'selected';
					}
				?>
				<option value="<?php echo $v_unit['kode']; ?>" <?php echo $selected; ?> > <?php echo strtoupper($v_unit['nama']); ?> </option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<small>
		<table class="table table-bordered" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-xs-1">TANGGAL</th>
					<th class="col-xs-1">DETAIL TRANS</th>
					<th class="col-xs-2">SUMBER / TUJUAN</th>
					<th class="col-xs-2">PERUSAHAAN</th>
					<th class="col-xs-3">KETERANGAN</th>
					<th class="col-xs-2">NOMINAL</th>
					<th class="col-xs-1"></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['detail'] as $k_det => $v_det): ?>
					<tr>
						<td>
						    <input type="text" class="form-control date text-center" data-required="1" placeholder="Tanggal" id="tgl_trans" style="padding: 3px;" data-val="<?php echo $v_det['tanggal']; ?>" />
						</td>
						<td>
							<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
								<div class="col-xs-12 no-padding">
									<select class="form-control jurnal_trans_detail" data-required="1">
										<option value="" > Pilih </option>
										<?php foreach ($jurnal_trans as $k_jt => $v_jt): ?>
											<?php
												$hide = '';
												if ( $v_jt['id'] != $data['jurnal_trans_id'] ) {
													$hide = 'hide';
												}
											?>
											<?php foreach ($v_jt['detail'] as $k_jtd => $v_jtd): ?>
												<?php
													$selected = null;
													if ( $v_jtd['id'] == $v_det['det_jurnal_trans_id'] ) {
														$selected = 'selected';
													}
												?>
												<option class="<?php echo $hide; ?>" value="<?php echo $v_jtd['id']; ?>" data-idheader="<?php echo $v_jt['id']; ?>" <?php echo $selected; ?> > <?php echo strtoupper($v_jtd['nama']); ?> </option>
											<?php endforeach ?>
										<?php endforeach ?>
									</select>
								</div>
							</div>
						</td>
						<td>
							<?php
								$hide_sumber_tujuan = 'hide';
								$disabled_sumber_tujuan = 'disbaled';
								$required_sumber_tujuan = 0;
								$hide_supplier = 'hide';
								$disabled_supplier = 'disbaled';
								$required_supplier = 0;
								if ( !empty($v_det['jurnal_trans_sumber_tujuan_id']) ) {
									$hide_sumber_tujuan = '';
									$disabled_sumber_tujuan = '';
									$required_sumber_tujuan = 1;
								} else {
									$hide_supplier = '';
									$disabled_supplier = '';
									$required_supplier = 1;
								}
							?>
							<select class="form-control <?php echo $hide_sumber_tujuan; ?> sumber_tujuan" data-required="<?php echo $required_sumber_tujuan; ?>" <?php echo $disabled_sumber_tujuan; ?> >
								<option value="" > Pilih </option>
								<?php foreach ($jurnal_trans as $k_jt => $v_jt): ?>
									<?php
										$hide = '';
										if ( $v_jt['id'] != $data['jurnal_trans_id'] ) {
											$hide = 'hide';
										}
									?>
									<?php foreach ($v_jt['sumber_tujuan'] as $k_st => $v_st): ?>
										<?php
											$selected = null;
											if ( $v_st['id'] == $v_det['jurnal_trans_sumber_tujuan_id'] ) {
												$selected = 'selected';
											}
										?>
										<option class="<?php echo $hide; ?>" value="<?php echo $v_st['id']; ?>" data-idheader="<?php echo $v_jt['id']; ?>" <?php echo $selected; ?> > <?php echo strtoupper($v_st['nama']); ?> </option>
									<?php endforeach ?>
								<?php endforeach ?>
							</select>
							<select class="form-control <?php echo $hide_supplier; ?> supplier" data-required="<?php echo $required_supplier; ?>" <?php echo $disabled_supplier; ?> >
								<option value="" > Pilih </option>
								<?php foreach ($supplier as $k_supl => $v_supl): ?>
									<?php
										$selected = null;
										if ( $v_supl['nomor'] == $v_det['supplier'] ) {
											$selected = 'selected';
										}
									?>
									<option value="<?php echo $v_supl['nomor']; ?>" <?php echo $selected; ?> > <?php echo strtoupper($v_supl['nama']); ?> </option>
								<?php endforeach ?>
							</select>
						</td>
						<td>
							<select class="form-control perusahaan" data-required="1">
								<option value="" > Pilih </option>
								<?php foreach ($perusahaan as $k_prs => $v_prs): ?>
									<?php
										$selected = null;
										if ( $v_prs['kode'] == $v_det['perusahaan'] ) {
											$selected = 'selected';
										}
									?>
									<option value="<?php echo $v_prs['kode']; ?>" <?php echo $selected; ?> > <?php echo strtoupper($v_prs['nama']); ?> </option>
								<?php endforeach ?>
							</select>
						</td>
						<td>
							<textarea class="form-control keterangan" data-required="1" placeholder="Keterangan"><?php echo $v_det['keterangan']; ?></textarea>
						</td>
						<td>
							<input type="text" class="form-control text-right nominal" data-tipe="decimal" maxlength="20" placeholder="Nominal" value="<?php echo angkaDecimal($v_det['nominal']); ?>">
						</td>
						<td>
							<div class="col-xs-6 text-center no-padding">
								<button type="button" class="btn btn-primary" onclick="jurnal.addRow(this)"><i class="fa fa-plus"></i></button>
							</div>
							<div class="col-xs-6 text-center no-padding">
								<button type="button" class="btn btn-danger" onclick="jurnal.removeRow(this)"><i class="fa fa-times"></i></button>
							</div>
						</td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="jurnal.edit(this)" data-id="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan Perubahan</button>
</div>