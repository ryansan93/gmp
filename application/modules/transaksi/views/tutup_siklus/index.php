<div class="row content-panel">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="panel-body">
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
							<select class="form-control filter" onchange="tutupSiklus.filter(this)">
								<option value="0">ALL</option>
								<option value="1">Belum Tutup Siklus</option>
								<option value="2">Sudah Tutup Siklus</option>
							</select>
						</div>
						<div class="col-sm-1">
							<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="tutupSiklus.get_lists()">Tampilkan</button>
						</div>
					</div>
				</div>
				<div class="col-lg-12 no-padding">
					<hr style="margin-top: 0px;">
				</div>
				<div class="col-lg-12 no-padding">
					<div class="col-lg-8 search left-inner-addon no-padding">
						<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_tutup_siklus" placeholder="Search" onkeyup="filter_all(this)">
					</div>
					<small>
						<table class="table table-bordered tbl_tutup_siklus" id="dataTable" width="100%" cellspacing="0">
							<thead>
								<tr>
									<th class="col-md-2">Nama Peternak</th>
									<th class="col-md-1">Noreg</th>
									<th class="col-md-1">Kandang</th>
									<th class="col-md-1">Populasi</th>
									<th class="col-md-1">Chick In</th>
									<th class="col-md-1">Panen</th>
									<th class="col-md-1">Status</th>
									<th class="col-md-2">Keterangan</th>
									<th class="col-md-2">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="9">Data tidak ditemukan.</td>
								</tr>
							</tbody>
						</table>
					</small>
				</div>
			</div>
		</form>
	</div>
</div>

<div class="modal fade" id="modal-tutup-siklus" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Tutup Siklus</h4>
			</div>
			<div class="modal-body">
				<form class="form-horizontal" role="form">
					<div class="form-group">
						<label class="col-sm-5 control-label text-left">Noreg</label>
						<div class="col-sm-7">
							<p class="form-control-static noreg"></p>
							<input type="hidden" name="noreg" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-5 control-label text-left">Tanggal Doc In</label>
						<div class="col-sm-7">
							<p class="form-control-static tgl_docin"></p>
							<input type="hidden" name="tgl_docin" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-5 control-label text-left">Tanggal Tutup Siklus</label>
						<div class="col-sm-7">
							<div class="input-group date datetimepicker" name="tgl_tutup_siklus" id="TglTutupSiklus">
						        <input type="text" class="form-control text-center" placeholder="Tutup Siklus" data-required="1" />
						        <span class="input-group-addon">
						            <span class="glyphicon glyphicon-calendar"></span>
						        </span>
						    </div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
				<button type="button" class="btn btn-primary" onclick="tutupSiklus.cekLhk(this)">Tutup Siklus</button>
			</div>
		</div>
	</div>
</div>
