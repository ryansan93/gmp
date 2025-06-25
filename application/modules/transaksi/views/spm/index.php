<div class="row content-panel">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#spm" data-tab="spm">Setting Pengiriman</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#pme" data-tab="pme">Perintah Muat Ekspedisi</a>
					</li>
					<!-- <li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#ppp" data-tab="ppp">Pengiriman dan Penerimaan Pakan</a>
					</li> -->
				</ul>
			</div>
			<div class="panel-body">
				<div class="tab-content">
					<div id="spm" class="tab-pane fade show active">
						<div class="col-sm-12">
							<form class="form-horizontal" role="form">
	                			<div class="form-group">
									<div class="col-sm-1 no-padding">
										<label class="control-label">Periode</label>
									</div>
									<div class="col-sm-2">
										<div class="input-group date datetimepicker" name="filter-tgl" id="filter-tgl">
									        <input type="text" class="form-control text-center" placeholder="Filter Tanggal" data-required="1" />
									        <span class="input-group-addon">
									            <span class="glyphicon glyphicon-calendar"></span>
									        </span>
									    </div>
									</div>
									<div class="col-sm-1">
										<button type="button" class="btn btn-primary" onclick="spm.filter()"><i class="fa fa-search"></i> Search</button>
									</div>
								</div>
								<div class="form-group">
									<table class="table table-bordered tbl_spm" id="dataTable" width="100%" cellspacing="0">
										<tbody>								
										</tbody>
									</table>
								</div>
							</form>
						</div>
					</div>
					<div id="pme" class="tab-pane fade">
					</div>
					<!-- <div id="ppp" class="tab-pane fade">
						<div class="col-lg-8 search left-inner-addon no-padding d-flex align-items-center" style="margin-bottom: 10px;">
							<div class="col-sm-1 no-padding">
								<span> Periode </label>
							</div>
							<div class="col-sm-3">
								<div class="input-group date datetimepicker" name="startDate" id="StartDate_ODVP">
							        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
							        <span class="input-group-addon">
							            <span class="glyphicon glyphicon-calendar"></span>
							        </span>
							    </div>
							</div>
							<div class="col-sm-1 text-center no-padding" style="max-width: 4%;">s/d</div>
							<div class="col-sm-3">
								<div class="input-group date datetimepicker" name="endDate" id="EndDate_ODVP">
							        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
							        <span class="input-group-addon">
							            <span class="glyphicon glyphicon-calendar"></span>
							        </span>
							    </div>
							</div>
							<div class="col-sm-2">
								<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="odvp.get_lists()">Tampilkan</button>
							</div>
						</div>
						<div class="col-lg-12 no-padding">
							<table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
								<thead>
									<tr class="v-center">
										<th class="text-center" rowspan="2">Kota</th>
										<th class="text-center" rowspan="2">Peternak</th>
										<th class="text-center" rowspan="2">Kandang</th>
										<th class="text-center" rowspan="2">Populasi</th>
										<th class="text-center" rowspan="2">Umur</th>
										<th class="text-center" rowspan="2">Pakan</th>
										<th class="text-center" colspan="5">Rencana</th>
										<th class="text-center" colspan="6">Realisasi</th>
									</tr>
									<tr class="v-center">
										<th class="text-center">SPM</th>
										<th class="text-center">Tgl Kirim</th>
										<th class="text-center">Jml Kg</th>
										<th class="text-center">Jml Zak</th>
										<th class="text-center">Ekspedisi</th>
										<th class="text-center">Tgl Kirim</th>
										<th class="text-center">Jml Tiba</th>
										<th class="text-center">No SJ</th>
										<th class="text-center">Jml Kg</th>
										<th class="text-center">Jml Zak</th>
										<th class="text-center">Ekspedisi</th>
									</tr>
								</thead>
							</table>
						</div>
					</div> -->
				</div>
			</div>
		</form>
	</div>
</div>