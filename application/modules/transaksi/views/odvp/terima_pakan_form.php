<div class="modal-header header">
	<span class="modal-title">Terima Pakan</span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body body">
	<div class="row">
		<div class="col-lg-12 detailed">
			<input type="hidden" data-noreg="">

			<form role="form" class="form-horizontal">
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12 d-flex align-items-center no-padding">
						<div class="col-lg-2">No. SJ</div>
						<div class="col-lg-3">
							<input type="text" class="form-control no_sj" placeholder="No. SJ">
						</div>
						<div class="col-lg-1"></div>
						<div class="col-lg-2">No. Polisi</div>
						<div class="col-lg-3">
							<input type="text" class="form-control no_pol" placeholder="No. Polisi">
						</div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12 d-flex align-items-center no-padding">
						<div class="col-lg-2">Ekspedisi</div>
						<div class="col-lg-3">
							<input type="text" class="form-control ekspedisi" placeholder="Ekspedisi">
						</div>
						<div class="col-lg-1"></div>
						<div class="col-lg-2">Sopir</div>
						<div class="col-lg-3">
							<input type="text" class="form-control sopir" placeholder="Sopir">
						</div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12 d-flex align-items-center no-padding">
						<div class="col-lg-2">Jenis Pengiriman</div>
						<div class="col-lg-3">
							<input type="text" class="form-control jenis_kirim" placeholder="Jenis Pengiriman" readonly>
						</div>
						<div class="col-lg-1"></div>
						<div class="col-lg-2">No. Order</div>
						<div class="col-lg-3">
							<input type="text" class="form-control no_order" placeholder="No. Order">
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
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12 d-flex align-items-center no-padding">
						<div class="col-lg-2">Tanggal Tiba</div>
						<div class="col-lg-3">
							<div class="input-group date datetimepicker" name="tgl_tiba" id="tgl_tiba">
						        <input type="text" class="form-control text-center" placeholder="Tanggal Tiba" data-required="1" />
						        <span class="input-group-addon">
						            <span class="glyphicon glyphicon-calendar"></span>
						        </span>
						    </div>
						</div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12 d-flex align-items-center no-padding">
						<div class="col-lg-2">Asal</div>
						<div class="col-lg-3">
							<input type="text" class="form-control asal" placeholder="Asal" readonly>
						</div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12 d-flex align-items-center no-padding">
						<div class="col-lg-2">Tujuan</div>
						<div class="col-lg-3">
							<input type="text" class="form-control tujuan" placeholder="Tujuan" readonly>
						</div>
					</div>
				</div>
				<div class="form-group d-flex align-items-center">
					<div class="col-lg-12 d-flex align-items-center">
						<table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0" style="margin-bottom: 0px;">
							<thead>
								<tr>
									<th class="text-center col-lg-2" rowspan="2">Jenis Pakan</th>
									<th class="text-center" colspan="2">Kirim</th>
									<th class="text-center" colspan="2">Terima</th>
								</tr>
								<tr>
									<th class="text-center col-md-1">Jumlah</th>
									<th class="text-center col-md-1">Kondisi</th>
									<th class="text-center col-md-1">Jumlah</th>
									<th class="text-center col-md-1">Kondisi</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td></td>
									<td></td>
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
						<button id="btn-add" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="ADD" onclick="odvp.save_order_pakan(this)" style="margin-left: 10px;"> 
							<i class="fa fa-save" aria-hidden="true"></i> Simpan
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>