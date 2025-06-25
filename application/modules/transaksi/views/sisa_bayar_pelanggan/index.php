<div class="row content-panel">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-heading">
				<ul class="nav nav-tabs nav-justified">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#riwayat_sbp" data-tab="riwayat_sbp">Riwayat Sisa Bayar Pelanggan</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#sbp" data-tab="sbp">Sisa Bayar Pelanggan</a>
					</li>
				</ul>
			</div>
			<div class="panel-body">
				<div class="tab-content">
					<div id="riwayat_sbp" class="tab-pane fade show active">
						<form class="form-horizontal">
							<div class="col-lg-8 no-padding">
								<div class="col-sm-1 no-padding">
									<label class="control-label"> Periode </label>
								</div>
								<div class="col-sm-3">
									<div class="input-group date" id="datetimepicker_start" name="startDate" id="StartDate_sbp">
								        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
								        <span class="input-group-addon">
								            <span class="glyphicon glyphicon-calendar"></span>
								        </span>
								    </div>
								</div>
								<div class="col-sm-2 text-center" style="max-width: 7%; margin-top:7px;"><b>s/d</b></div>
								<div class="col-sm-3">
									<div class="input-group date" id="datetimepicker_end" name="endDate" id="EndDate_sbp">
								        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
								        <span class="input-group-addon">
								            <span class="glyphicon glyphicon-calendar"></span>
								        </span>
								    </div>
								</div>
								<div class="col-sm-2">
									<button id="btn-tampilkan" type="button" data-href="sbp" class="btn btn-primary cursor-p pull-left" title="ADD" onclick="sbp.get_lists(this)"> 
										<i class="fa fa-search" aria-hidden="true"></i> Tampilkan
									</button>
								</div>
							</div>
							<div class="col-lg-4">
								<?php if ( $akses['a_submit'] == 1 ) { ?>
									<button id="btn-add" type="button" data-href="sbp" class="btn btn-primary cursor-p pull-right" title="ADD" onclick="sbp.changeTabActive(this)"> 
										<i class="fa fa-plus" aria-hidden="true"></i> ADD
									</button>
								<?php } else { ?>
									<div class="col-lg-2 action no-padding pull-right">
										&nbsp
									</div>
								<?php } ?>
							</div>
						</form>
						<div class="col-lg-12 search left-inner-addon no-padding"><hr></div>
						<div class="col-lg-8 search left-inner-addon" style="padding: 0px 0px 5px 0px;">
							<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_sbp" placeholder="Search" onkeyup="filter_all(this)">
						</div>
						<small>
							<table class="table table-bordered tbl_sbp" id="dataTable" width="100%" cellspacing="0">
								<thead>
									<tr>
										<th class="col-md-1">Tanggal</th>
										<th class="col-md-2">Kab / Kota</th>
										<th class="col-md-2">Pelanggan</th>
										<th class="col-md-2">Saldo</th>
										<th class="col-md-1">Action</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="5">Data tidak ditemukan.</td>
									</tr>
								</tbody>
							</table>
						</small>
					</div>
					<div id="sbp" class="tab-pane fade">
						<?php echo $add_form; ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>