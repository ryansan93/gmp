<div class="modal-header">
	<span class="modal-title"><b>DETAIL PEMBAYARAN PERALATAN</b></span>
	<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body" style="padding: 0px 15px 15px 15px;">
	<div class="row detailed">
		<div class="col-lg-12 detailed">
			<form role="form" class="form-horizontal">
				<div class="col-xs-12 no-padding">
					<div class="col-xs-2 no-padding">
						<label class="control-label">Tanggal Jual</label>
					</div>
					<div class="col-xs-10 no-padding tgl_jual" data-val="<?php echo $data['tanggal']; ?>">
						<label class="control-label">: <?php echo tglIndonesia($data['tanggal'], '-', ' '); ?></label>
					</div>
				</div>

				<div class="col-xs-12 no-padding">
					<div class="col-xs-2 no-padding">
						<label class="control-label">Total Jual</label>
					</div>
					<div class="col-xs-10 no-padding tot_jual" data-val="<?php echo $data['total']; ?>">
						<label class="control-label">: <?php echo angkaDecimal($data['total']); ?></label>
					</div>
				</div>

				<div class="col-xs-12 no-padding">
					<div class="col-xs-2 no-padding">
						<label class="control-label">Total Bayar</label>
					</div>
					<div class="col-xs-10 no-padding tot_bayar" data-val="<?php echo $data['total_bayar']; ?>">
						<label class="control-label">: <?php echo angkaDecimal($data['total_bayar']); ?></label>
					</div>
				</div>

				<div class="col-xs-12 no-padding">
					<div class="col-xs-2 no-padding">
						<label class="control-label">Sisa Bayar</label>
					</div>
					<div class="col-xs-10 no-padding sisa_bayar" data-val="<?php echo $data['sisa_bayar']; ?>">
						<label class="control-label">: <?php echo angkaDecimal($data['sisa_bayar']); ?></label>
					</div>
				</div>

				<div class="col-xs-12 no-padding">
					<div class="col-xs-2 no-padding">
						<label class="control-label">Status</label>
					</div>
					<div class="col-xs-10 no-padding status" data-val="<?php echo $data['status']; ?>">
						<?php
							$hide = 'hide';
							$red = 'blue';
							if ( stristr($data['status'], 'belum') !== FALSE ) {
								$red = 'red';
								$hide = '';
							}
						?>
						<label class="control-label">: <span style="color: <?php echo $red; ?>"><?php echo strtoupper($data['status']); ?></span></label>
					</div>
				</div>

				<div class="col-xs-12 no-padding"><hr style="margin-bottom: 10px; margin-top: 10px;"></div>

				<div class="col-xs-12 no-padding">
					<small>
						<table class="table table-bordered tbl_bayar" style="margin-bottom: 0px;">
							<thead>
								<tr>
									<th class="col-xs-3">Tgl Bayar</th>
									<th class="col-xs-4">Jumlah Bayar</th>
									<th class="col-xs-3">Lampiran</th>
									<th class="col-xs-2"></th>
								</tr>
							</thead>
							<tbody>
								<tr class="baru belum_lunas <?php echo $hide; ?>">
									<td>
										<div class="col-xs-12 no-padding" style="padding-right: 5px;">
											<div class="input-group date datetimepicker tgl_bayar">
										        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $data['tanggal']; ?>" />
										        <span class="input-group-addon">
										            <span class="glyphicon glyphicon-calendar"></span>
										        </span>
										    </div>
										</div>
									</td>
									<td>
										<input type="text" class="form-control text-right jml_bayar" data-tipe="decimal" data-required="1" placeholder="Jumlah" onblur="bpp.hit_tot_bayar(this)">
									</td>
									<td></td>
									<td class="text-center">
										<button type="button" class="btn btn-sm btn-primary cursor-p" title="ADD ROW" onclick="bpp.add_row(this)"> 
											<i class="fa fa-plus" aria-hidden="true"></i> 
										</button>
		          						<button type="button" class="btn btn-sm btn-danger cursor-p" title="REMOVE ROW" onclick="bpp.remove_row(this)"> 
		          							<i class="fa fa-minus" aria-hidden="true"></i> 
		          						</button>
									</td>
								</tr>
							</tbody>
						</table>
					</small>
				</div>

				<div class="col-xs-12 no-padding"><hr style="margin-bottom: 10px; margin-top: 10px;"></div>

				<div class="col-xs-12 no-padding <?php echo $hide; ?>">
					<button type="button" class="btn btn-primary pull-right" onclick="bpp.save(this)" data-idjual="<?php echo $data['id']; ?>"><i class="fa fa-save"></i> Simpan</button>
				</div>
				<!-- <div class="form-group">
					<div class="col-lg-12">
						<table class="table table-bordered detail" id="dataTable" width="100%" cellspacing="0">
							<thead>
								<tr>
									<th class="col-sm-4">Nama Fitur</th>
									<th>Path Fitur</th>
									<th class="col-sm-2">Action</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<input type="text" placeholder="Nama Fitur" id="nama_fitur" class="form-control" data-required="1">
									</td>
									<td>
										<input type="text" placeholder="Path Fitur" id="path_fitur" class="form-control" data-required="1">
									</td>
									<td class="text-center">
										<button id="btn-add" type="button" class="btn btn-sm btn-primary cursor-p" title="ADD ROW" onclick="add_row(this)"> 
											<i class="fa fa-plus" aria-hidden="true"></i> 
										</button>
		          						<button id="btn-remove" type="button" class="btn btn-sm btn-danger cursor-p" title="REMOVE ROW" onclick="remove_row(this)"> 
		          							<i class="fa fa-minus" aria-hidden="true"></i> 
		          						</button>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div> -->
			</form>
		</div>
	</div>
</div>