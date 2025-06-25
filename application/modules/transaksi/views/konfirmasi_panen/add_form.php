<div class="modal-header">
	<span class="modal-title"><b>Add Konfirmasi Panen</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
	<div class="row detailed">
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="col-md-6 no-padding">
					<div class="col-lg-3 no-padding pull-left">
						<h5>Tanggal DOC In</h5>
					</div>
					<div class="col-lg-4 no-padding action">
					    <div class="input-group date" id="tgl_docin" name="tanggal-docin" data-tgl="<?php echo substr($data['tgl_docin'], 0, 10); ?>">
					        <input type="text" class="form-control text-center" data-required="1" value="<?php echo tglIndonesia(substr($data['tgl_docin'], 0, 10), '-', ' '); ?>" readonly />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
				</div>
				<div class="col-md-6 no-padding">
					<div class="col-lg-3 no-padding pull-left">
						<h5>Tanggal Panen</h5>
					</div>
					<div class="col-lg-4 no-padding action">
					    <div class="input-group date" id="tgl_panen" name="tanggal-panen">
					        <input type="text" class="form-control text-center" data-required="1" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
				</div>
			</form>
		</div>
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="col-md-6 no-padding">
					<div class="col-lg-3 no-padding pull-left">
						<h5>Noreg</h5>
					</div>
					<div class="col-lg-4 no-padding action">
						<input type="text" class="form-control text-left noreg" data-required="1" value="<?php echo $data['noreg']; ?>" readonly />
					</div>
				</div>
				<div class="col-md-6 no-padding">
					<div class="col-lg-3 no-padding pull-left">
						<h5>Populasi</h5>
					</div>
					<div class="col-lg-4 no-padding action">
						<input type="text" class="form-control text-right populasi" data-tipe="integer" data-required="1" value="<?php echo angkaRibuan($populasi); ?>" readonly />
					</div>
				</div>
			</form>
		</div>
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="col-md-6 no-padding">
					<div class="col-lg-3 no-padding pull-left">
						<h5>BB Rata2</h5>
					</div>
					<div class="col-lg-4 no-padding action">
						<input type="text" class="form-control text-right bb_rata2" data-tipe="decimal" data-required="1" readonly />
					</div>
				</div>
				<div class="col-md-6 no-padding">
					<div class="col-lg-3 no-padding pull-left">
						<h5>Total Sekat</h5>
					</div>
					<div class="col-lg-4 no-padding action">
						<input type="text" class="form-control text-right tot_sekat" data-tipe="decimal" data-required="1" readonly />
					</div>
				</div>
			</form>
		</div>
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="col-md-6 no-padding">
					<div class="col-lg-3 no-padding pull-left">
						<h5><u>Data Sekat</u></h5>
					</div>
				</div>
			</form>
		</div>
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="col-md-4 no-padding">
					<table class="table table-bordered data_sekat">
						<thead>
							<tr>
								<th class="col-md-1">No</th>
								<th class="col-md-8">Jumlah</th>
								<th class="col-md-4">BB</th>
							</tr>
						</thead>
						<tbody>
							<tr class="v-center">
								<td class="text-center no">1</td>
								<td><input type="text" class="form-control text-right jumlah" data-tipe="integer" onblur="kp.hitung_total(this);" data-required="1" /></td>
								<td>
									<input type="text" class="form-control text-right bb" data-tipe="decimal" onblur="kp.hitung_total(this);" data-required="1" />
									<div class="btn-ctrl">
										<span onclick="kp.removeRow(this)" class="btn_del_row_2x"></span>
										<span onclick="kp.addRow(this)" class="btn_add_row_2x"></span>
									</div>
								</td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td><b>Total</b></td>
								<td class="text-right tot_jumlah"><b>0</b></td>
								<td class="text-right tot_bb"><b>0,00</b></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</form>
		</div>
		<div class="col-lg-12 detailed no-padding">
			<hr>
		</div>
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="col-md-12 no-padding">
					<button type="button" class="btn btn-primary pull-right" onclick="kp.save(this)"><i class="fa fa-save"></i> Simpan</button>
				</div>
			</form>
		</div>
	</div>
</div>