<div class="row content-panel">
	<div class="col-lg-12 no-padding">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified" id="myTab" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#history" data-tab="history" role="tab">Riwayat Bad Debt</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#action" data-tab="action" role="tab">Bad Debt</a>
					</li>
				</ul>
			</div>
			<div class="panel-body" style="padding-top: 0px; padding-bottom: 0px;">
				<div class="tab-content">
					<div id="history" class="tab-pane fade show active" role="tabpanel">
						<div class="col-lg-12 no-padding">
							<div class="col-lg-8 search left-inner-addon no-padding d-flex align-items-center">
								<div class="col-sm-2 no-padding">
									<label class="control-label" style="padding-top: 0px;">Tanggal Bayar</label>
								</div>
								<div class="col-sm-3">
									<div class="input-group date datetimepicker" name="startDate" id="StartDate_PP">
								        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
								        <span class="input-group-addon">
								            <span class="glyphicon glyphicon-calendar"></span>
								        </span>
								    </div>
								</div>
								<div class="col-sm-1 text-center no-padding" style="max-width: 4%;">s/d</div>
								<div class="col-sm-3">
									<div class="input-group date datetimepicker" name="endDate" id="EndDate_PP">
								        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
								        <span class="input-group-addon">
								            <span class="glyphicon glyphicon-calendar"></span>
								        </span>
								    </div>
								</div>
								<div class="col-sm-2">
									<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="bakul.get_list_pembayaran()">Tampilkan</button>
								</div>
							</div>
							<?php if ( $akses['a_submit'] == 1 ): ?>
								<div class="col-lg-4 no-padding">
									<div class="col-sm-12 no-padding">
										<button type="button" class="btn btn-success pull-right" onclick="bakul.changeTabActive(this)" data-href="action"><i class="fa fa-plus"></i> Tambah</button>
									</div>
								</div>
							<?php endif ?>
						</div>
						<div class="col-lg-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
						<div class="col-md-12 search left-inner-addon pull-right no-padding" style="margin-bottom: 10px;">
							<i class="fa fa-search"></i><input class="form-control" type="search" data-table="tbl_list_pembayaran" placeholder="Search" onkeyup="bakul.filter_all(this)">
						</div>
						<div class="col-lg-12 no-padding">
							<small>
								<table class="table table-bordered tbl_list_pembayaran" style="margin-bottom: 0px;">
									<thead>
										<tr>
											<th class="col-lg-1 text-center">Tgl Bayar</th>
											<th class="col-lg-2 text-center">Perusahaan</th>
											<th class="col-lg-2 text-center">Pelanggan</th>
											<th class="col-lg-1 text-center">Jumlah Transfer</th>
											<th class="col-lg-2 text-center">Bukti Transfer</th>
											<th class="col-lg-2 text-center">Keterangan</th>
											<th class="col-lg-1 text-center"></th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td colspan="7">Data tidak ditemukan.</td>
										</tr>
									</tbody>
								</table>
							</small>
						</div>
					</div>
					<div id="action" class="tab-pane fade" role="tabpanel">
						<?php echo $add_form; ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>