<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Tanggal</label></div>
	<div class="col-xs-12 no-padding">
		<div class="input-group date" id="tanggal">
	        <input type="text" class="form-control text-center" data-required="1" placeholder="Start Date" />
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
				<option value="<?php echo $v_jt['id']; ?>" > <?php echo strtoupper($v_jt['nama']); ?> </option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Unit</label></div>
	<div class="col-xs-12 no-padding">
		<!-- <select class="unit" name="unit[]" multiple="multiple" width="100%" data-required="1"> -->
		<select class="unit" width="100%" data-required="1">
			<option value="all" > ALL </option>
			<option value="pusat" > PUSAT </option>
			<?php foreach ($unit as $key => $v_unit): ?>
				<option value="<?php echo $v_unit['kode']; ?>" > <?php echo strtoupper($v_unit['nama']); ?> </option>
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
				<tr>
					<td>
					    <input type="text" class="form-control date text-center" data-required="1" placeholder="Tanggal" id="tgl_trans" style="padding: 3px;" />
					</td>
					<td>
						<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
							<div class="col-xs-12 no-padding">
								<select class="form-control jurnal_trans_detail" data-required="1" disabled>
									<option value="" > Pilih </option>
									<?php foreach ($jurnal_trans as $k_jt => $v_jt): ?>
										<?php foreach ($v_jt['detail'] as $k_det => $v_det): ?>
											<option value="<?php echo $v_det['id']; ?>" data-idheader="<?php echo $v_jt['id']; ?>" > <?php echo strtoupper($v_det['nama']); ?> </option>
										<?php endforeach ?>
									<?php endforeach ?>
								</select>
							</div>
						</div>
					</td>
					<td>
						<select class="form-control sumber_tujuan" data-required="1" disabled>
							<option value="" > Pilih </option>
							<?php foreach ($jurnal_trans as $k_jt => $v_jt): ?>
								<?php foreach ($v_jt['sumber_tujuan'] as $k_det => $v_det): ?>
									<option value="<?php echo $v_det['id']; ?>" data-idheader="<?php echo $v_jt['id']; ?>" > <?php echo strtoupper($v_det['nama']); ?> </option>
								<?php endforeach ?>
							<?php endforeach ?>
						</select>
						<select class="form-control supplier hide" data-required="1" disabled>
							<option value="" > Pilih </option>
							<?php foreach ($supplier as $k_supl => $v_supl): ?>
								<option value="<?php echo $v_supl['nomor']; ?>" > <?php echo strtoupper($v_supl['nama']); ?> </option>
							<?php endforeach ?>
						</select>
					</td>
					<td>
						<select class="form-control perusahaan" data-required="1">
							<option value="" > Pilih </option>
							<?php foreach ($perusahaan as $k_prs => $v_prs): ?>
								<option value="<?php echo $v_prs['kode']; ?>" > <?php echo strtoupper($v_prs['nama']); ?> </option>
							<?php endforeach ?>
						</select>
					</td>
					<td>
						<textarea class="form-control keterangan" data-required="1" placeholder="Keterangan"></textarea>
					</td>
					<td>
						<input type="text" class="form-control text-right nominal" data-tipe="decimal" maxlength="20" placeholder="Nominal">
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
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="jurnal.save(this)"><i class="fa fa-save"></i> Simpan</button>
</div>