<?php if ( $akses['a_submit'] == 1 ) { ?>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Tanggal Saldo Bank</label></div>
		<div class="col-xs-12 no-padding">
			<div class="input-group date datetimepicker lock_date_fiskal" name="tanggal" id="Tanggal">
				<input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-tgl="<?php echo date('Y-m-d'); ?>" />
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<div class="col-xs-12 no-padding"><label class="control-label">Total</label></div>
		<div class="col-xs-12 no-padding nilai">
			<input type="text" class="col-xs-12 form-control text-right uppercase" placeholder="Total" disabled>
		</div>
	</div>

	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>

	<div class="col-xs-12 no-padding">
		<div class="col-xs-12 no-padding" style="overflow-x: auto;">
			<small>
				<table class="table table-bordered tbl_detail" style="margin-bottom: 0px; max-width: 100%; width: 100%;">
					<thead>
						<tr>
							<th class="col-xs-2">COA</th>
							<th class="col-xs-3">Nama COA</th>
							<th class="col-xs-2">Saldo</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($bank as $key => $value) { ?>
							<tr>
								<td class="no_coa"><?php echo $value['no_coa']; ?></td>
								<td><?php echo $value['nama_coa']; ?></td>
								<td>
									<input type="text" class="form-control text-right saldo uppercase" placeholder="Saldo" data-tipe="decimal" maxlength="19" data-required="1" onblur="sb.hitGrandTotal(this)">
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
		<button type="button" class="btn btn-primary pull-right" onclick="sb.save()"><i class="fa fa-save"></i> Simpan</button>
	</div>
<?php } else { ?>
	<h4>SALDO BANK</h4>
<?php } ?>