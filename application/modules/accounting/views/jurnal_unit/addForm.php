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
<div class="col-xs-12 no-padding">
	<div class="col-xs-6 no-padding" style="padding-right: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Unit</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control unit" data-required="1">
				<option value="">-- Pilih --</option>
				<!-- <option value="all">ALL</option>
				<option value="pusat">PUSAT GEMUK</option>
				<option value="pusat_gml">PUSAT GEMILANG</option>
				<option value="pusat_ma">PUSAT MA</option> -->
				<?php foreach ($unit as $k_unit => $v_unit): ?>
					<option value="<?php echo $v_unit['kode']; ?>"><?php echo $v_unit['nama']; ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
	<div class="col-xs-6 no-padding" style="padding-left: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Perusahaan</label></div>
		<div class="col-xs-12 no-padding">
			<select class="form-control perusahaan" data-required="1">
				<option value="">-- Pilih --</option>
				<?php foreach ($perusahaan as $k_prs => $v_prs): ?>
					<option value="<?php echo $v_prs['kode']; ?>"> <?php echo strtoupper($v_prs['nama']); ?> </option>
				<?php endforeach ?>
			</select>
		</div>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Transaksi / Kelompok Jurnal</label></div>
	<div class="col-xs-12 no-padding">
		<select class="form-control jurnal_trans" data-required="1">
			<option value="">-- Pilih --</option>
			<?php foreach ($jurnal_trans as $k_jt => $v_jt): ?>
				<option value="<?php echo $v_jt['id']; ?>" > <?php echo strtoupper($v_jt['nama']); ?> </option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<div class="col-xs-12 no-padding"><label class="control-label text-left">Plasma</label></div>
	<div class="col-xs-12 no-padding">
		<select class="form-control noreg" disabled="disabled">
			<option value="">-- Pilih --</option>
		</select>
	</div>
</div>
<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
	<small>
		<table class="table table-bordered" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="text-center col-xs-11">Detail Transaksi</th>
					<th class="text-center col-xs-1"></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="padding: 10px;">
						<div class="col-xs-12 no-padding">
							<div class="col-xs-12 no-padding"><label class="control-label">Kode Jurnal</label></div>
							<div class="col-xs-12 no-padding">
								<input type="text" class="form-control kode_jurnal" placeholder="Kode Jurnal" readonly>
							</div>
						</div>
						<div class="col-xs-12 no-padding">
							<div class="col-xs-12 no-padding"><label class="control-label">Tanggal</label></div>
							<div class="col-xs-12 no-padding">
								<div class="input-group date" id="tgl_trans">
							        <input type="text" class="form-control text-center" data-required="1" placeholder="Tanggal" />
							        <span class="input-group-addon">
							            <span class="glyphicon glyphicon-calendar"></span>
							        </span>
							    </div>
							</div>
						</div>
						<div class="col-xs-12 no-padding">
							<div class="col-xs-6 no-padding" style="padding-right: 5px;">
								<div class="col-xs-12 no-padding"><label class="control-label">Detail Transaksi</label></div>
								<div class="col-xs-12 no-padding">
									<select class="form-control jurnal_trans_detail" data-required="1" disabled>
										<option value="">-- Pilih --</option>
										<?php foreach ($jurnal_trans as $k_jt => $v_jt): ?>
											<?php foreach ($v_jt['detail'] as $k_det => $v_det): ?>
												<option value="<?php echo $v_det['id']; ?>" data-idheader="<?php echo $v_jt['id']; ?>" > <?php echo strtoupper($v_det['nama']); ?> </option>
											<?php endforeach ?>
										<?php endforeach ?>
									</select>
								</div>
							</div>
							<div class="col-xs-6 no-padding" style="padding-left: 5px;">
								<div class="col-xs-12 no-padding"><label class="control-label">PiC</label></div>
								<div class="col-xs-12 no-padding">
									<input type="text" class="form-control pic" placeholder="PiC" data-required="1">
								</div>
							</div>
						</div>
						<div class="col-xs-12 no-padding hide">
							<div class="col-xs-1 no-padding"><label class="control-label">Rek Asal</label></div>
							<div class="col-xs-1 no-padding text-center" style="max-width: 2%;"><label class="control-label">:</label></div>
							<div class="col-xs-10 no-padding sumber_coa"><label class="control-label">-</label></div>
						</div>
						<div class="col-xs-12 no-padding hide">
							<div class="col-xs-1 no-padding"><label class="control-label">Rek Tujuan</label></div>
							<div class="col-xs-1 no-padding text-center" style="max-width: 2%;"><label class="control-label">:</label></div>
							<div class="col-xs-10 no-padding tujuan_coa"><label class="control-label">-</label></div>
						</div>
						<div class="col-xs-12 no-padding">
							<div class="col-xs-12 no-padding"><label class="control-label">Nominal</label></div>
							<div class="col-xs-12 no-padding">
								<input type="text" class="form-control text-right nominal" data-tipe="decimal" maxlength="20" placeholder="Nominal" data-required="1">
							</div>
						</div>
						<div class="col-xs-12 no-padding">
							<div class="col-xs-12 no-padding"><label class="control-label">Keterangan</label></div>
							<div class="col-xs-12 no-padding">
								<textarea class="form-control keterangan" data-required="1" placeholder="Keterangan"></textarea>
							</div>
						</div>
					</td>
					<td class="col-xs-1">
						<div class="col-xs-6 text-center no-padding">
							<button type="button" class="btn btn-primary" onclick="ju.addRow(this)"><i class="fa fa-plus"></i></button>
						</div>
						<div class="col-xs-6 text-center no-padding">
							<button type="button" class="btn btn-danger" onclick="ju.removeRow(this)"><i class="fa fa-times"></i></button>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</small>
</div>
<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-xs-12 no-padding">
	<button type="button" class="btn btn-primary pull-right" onclick="ju.save(this)"><i class="fa fa-save"></i> Simpan</button>
</div>