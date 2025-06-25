<div class="modal-header header">
	<span class="modal-title">Kirim Pakan</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-lg-12 detailed">
			<input type="hidden" data-noreg="">

			<form role="form" class="form-horizontal">
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12 d-flex align-items-center no-padding">
						<div class="col-lg-2 text-left">Jenis Pengiriman</div>
						<div class="col-lg-3">
							<select class="form-control jenis_kirim" data-required="1">
								<option value="">-- Pilih Jenis --</option>
								<option value="opks">Order Pabrik (OPKS)</option>
								<option value="opkp">Dari Peternak (OPKP)</option>
								<option value="opkg">Dari Gudang (OPKG)</option>
							</select>
						</div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12 d-flex align-items-center no-padding">
						<div class="col-lg-2 text-left">No. Order</div>
						<div class="col-lg-3">
							<input type="text" class="form-control no_order" placeholder="No. Order" data-required="1" readonly>
						</div>
						<div class="col-lg-8">
							<b><span>* Hanya Berlaku Untuk Order Pabrik</span></b>
						</div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12 d-flex align-items-center no-padding">
						<div class="col-lg-2">Asal</div>
						<div class="col-lg-4">
							<input type="text" class="form-control asal" placeholder="Asal" data-required="1" readonly>
						</div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12 d-flex align-items-center no-padding">
						<div class="col-lg-2">Tujuan</div>
						<div class="col-lg-4">
							<input type="text" class="form-control tujuan" placeholder="Tujuan" data-required="1" readonly>
						</div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12 d-flex align-items-center no-padding">
						<div class="col-lg-2">Rencana Kirim</div>
						<div class="col-lg-3">
							<div class="input-group date datetimepicker" name="rcn_kirim" id="rcn_kirim">
						        <input type="text" class="form-control text-center" placeholder="Rencana Kirim" data-required="1" />
						        <span class="input-group-addon">
						            <span class="glyphicon glyphicon-calendar"></span>
						        </span>
						    </div>
						</div>
						<div class="col-lg-1"></div>
						<div class="col-lg-2">Ekspedisi</div>
						<div class="col-lg-4">
							<input type="text" class="form-control ekspedisi" placeholder="Ekspedisi" data-required="1">
						</div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12 d-flex align-items-center no-padding">
						<div class="col-lg-2">Tgl Kirim</div>
						<div class="col-lg-3">
							<div class="input-group date datetimepicker" name="tgl_kirim" id="tgl_kirim">
						        <input type="text" class="form-control text-center" placeholder="Tanggal Kirim" data-required="1" />
						        <span class="input-group-addon">
						            <span class="glyphicon glyphicon-calendar"></span>
						        </span>
						    </div>
						</div>
						<div class="col-lg-1"></div>
						<div class="col-lg-2">No. Polisi</div>
						<div class="col-lg-3">
							<input type="text" class="form-control no_pol" placeholder="No. Polisi" data-required="1">
						</div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12 d-flex align-items-center no-padding">
						<div class="col-lg-2">No. SJ</div>
						<div class="col-lg-3">
							<input type="text" class="form-control no_sj" placeholder="No. SJ" data-required="1" readonly>
						</div>
						<div class="col-lg-1"></div>
						<div class="col-lg-2">Sopir</div>
						<div class="col-lg-3">
							<input type="text" class="form-control sopir" placeholder="Sopir" data-required="1">
						</div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12 d-flex align-items-center">
						<table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0" style="margin-bottom: 0px;">
							<thead>
								<tr>
									<th class="col-lg-2">Jenis Pakan</th>
									<th class="col-lg-2">Jumlah</th>
									<th class="col-lg-2">Kondisi</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td></td>
									<td></td>
									<td></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-12 no-padding">
						<hr>
						<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="ADD" onclick="odvp.save_kirim_pakan(this)" style="margin-left: 10px;"> 
							<i class="fa fa-save" aria-hidden="true"></i> Simpan
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>