<div class="row content-panel">
	<div class="col-xs-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-xs-12 no-padding">
				<div class="col-xs-12 no-padding">
					<label class="control-label">Tanggal Bayar</label>
				</div>
				<div class="col-xs-6 no-padding" style="padding-right: 5px; padding-bottom: 10px">
					<div class="input-group date datetimepicker" name="startDate" id="StartDate">
				        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
				        <span class="input-group-addon">
				            <span class="glyphicon glyphicon-calendar"></span>
				        </span>
				    </div>
				</div>
				<div class="col-xs-6 no-padding" style="padding-left: 5px; padding-bottom: 10px">
					<div class="input-group date datetimepicker" name="endDate" id="EndDate">
				        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
				        <span class="input-group-addon">
				            <span class="glyphicon glyphicon-calendar"></span>
				        </span>
				    </div>
				</div>
				<div class="col-xs-12 no-padding">
					<button id="btn-tampil" type="button" data-href="action" class="col-xs-12 btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="pp.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
				</div>
				<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
				<div class="col-md-12 search left-inner-addon pull-right no-padding" style="margin-bottom: 10px;">
					<i class="fa fa-search"></i><input class="form-control" type="search" data-table="tbl_list" placeholder="Search" onkeyup="pp.filter_all(this)">
				</div>
				<div class="col-xs-12 no-padding">
					<small>
						<table class="table table-bordered tbl_list" style="margin-bottom: 0px;">
							<thead>
								<tr class="v-center">
									<th class="col-xs-3 text-center">Plasma</th>
									<th class="col-xs-1 text-center">Tgl Tutup Siklus</th>
									<th class="col-xs-1 text-center">Tgl Bayar</th>
									<th class="col-xs-2 text-center">RHPP (Rp.)</th>
									<th class="col-xs-2 text-center">Total Bayar (Rp.)</th>
									<th class="col-xs-3 text-center">Keterangan</th>
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
			</div>
		</form>
	</div>
</div>