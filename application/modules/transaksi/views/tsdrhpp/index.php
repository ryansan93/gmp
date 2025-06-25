<div class="row content-panel">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#tutup_siklus" data-tab="tutup_siklus">Tutup Siklus</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#rhpp" data-tab="rhpp">RHPP</a>
					</li>
				</ul>
			</div>
			<div class="panel-body">
				<div class="tab-content">
					<div id="tutup_siklus" class="tab-pane fade show active">
						<div class="col-lg-12 no-padding">
							<div class="col-lg-12 search left-inner-addon no-padding action">
								<div class="col-sm-2 no-padding" style="width: 10%;">
									<label class="control-label">Periode Doc In</label>
								</div>
								<div class="col-sm-2">
									<div class="input-group date datetimepicker" name="startDate" id="StartDate">
								        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
								        <span class="input-group-addon">
								            <span class="glyphicon glyphicon-calendar"></span>
								        </span>
								    </div>
								</div>
								<div class="col-sm-1 text-center no-padding" style="max-width: 4%;"><label class="control-label">s/d</label></div>
								<div class="col-sm-2">
									<div class="input-group date datetimepicker" name="endDate" id="EndDate">
								        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
								        <span class="input-group-addon">
								            <span class="glyphicon glyphicon-calendar"></span>
								        </span>
								    </div>
								</div>
								<div class="col-sm-3">
									<select class="form-control filter" onchange="tsdrhpp.filter(this)">
										<option value="0">ALL</option>
										<option value="1">Belum Tutup Siklus</option>
										<option value="2">Sudah Tutup Siklus</option>
									</select>
								</div>
								<div class="col-sm-1">
									<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="tsdrhpp.get_lists()">Tampilkan</button>
								</div>
							</div>
						</div>
						<div class="col-lg-12 no-padding">
							<hr style="margin-top: 0px;">
						</div>
						<div class="col-lg-12 no-padding">
							<div class="col-lg-8 search left-inner-addon no-padding">
								<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_rhpp" placeholder="Search" onkeyup="filter_all(this)">
							</div>
							<div class="col-lg-4 action no-padding">
								<div class="col-lg-2 action no-padding pull-right">
									&nbsp
								</div>
							</div>
							<small>
								<table class="table table-bordered tbl_rhpp" id="dataTable" width="100%" cellspacing="0">
									<thead>
										<tr>
											<th class="col-md-2">Nama Peternak</th>
											<th class="col-md-1">Noreg</th>
											<th class="col-md-1">Kandang</th>
											<th class="col-md-1">Populasi</th>
											<th class="col-md-1">Chick In</th>
											<th class="col-md-1">Panen</th>
											<th class="col-md-4">Status</th>
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
					<div id="rhpp" class="tab-pane fade">
						<h4>RHPP</h4>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>