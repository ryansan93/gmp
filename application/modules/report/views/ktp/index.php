<div class="row content-panel">
	<div class="col-lg-12 detailed">
		<form role="form" class="form-horizontal">
			<div class="col-lg-12 no-padding">
				<div class="col-lg-8 search left-inner-addon no-padding d-flex align-items-center" style="margin-bottom: 10px;">
					<div class="col-sm-2 no-padding">
						<span> Rencana Kirim </label>
					</div>
					<div class="col-sm-3">
						<div class="input-group date datetimepicker" name="startDate" id="StartDate_SPM">
					        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
					<div class="col-sm-1 text-center no-padding" style="max-width: 4%;">s/d</div>
					<div class="col-sm-3">
						<div class="input-group date datetimepicker" name="endDate" id="EndDate_SPM">
					        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
					        <span class="input-group-addon">
					            <span class="glyphicon glyphicon-calendar"></span>
					        </span>
					    </div>
					</div>
					<div class="col-sm-2">
						<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="ktp.get_lists()">Tampilkan</button>
					</div>
				</div>
				<div class="col-lg-12 no-padding">
					<div class="col-sm-12">
						<div class="row">
							<a class="tu-float-btn tu-float-btn-left tu-table-prev" >
								<i class="fa fa-arrow-left my-float"></i>
							</a>

							<a class="tu-float-btn tu-float-btn-right tu-table-next" >
								<i class="fa fa-arrow-right my-float"></i>
							</a>
						</div>
					</div>

					<table class="table table-bordered table-hover tbl_ktp" width="100%" cellspacing="0">
						<thead>
							<tr class="v-center">
								<th class="page0 text-center" rowspan="2">Kota</th>
								<th class="page0 text-center" rowspan="2">Peternak</th>
								<th class="page0 text-center" rowspan="2">Kandang</th>
								<th class="page0 text-center" rowspan="2">Populasi</th>
								<th class="page0 text-center" rowspan="2">Umur</th>
								<th class="page0 text-center pakan" rowspan="2">Pakan</th>
								<th class="page1 text-center" colspan="5">Rencana</th>
								<th class="page2 text-center" colspan="6">Realisasi</th>
							</tr>
							<tr class="v-center">
								<th class="page1 text-center">SPM</th>
								<th class="page1 text-center">Tgl Kirim</th>
								<th class="page1 text-center">Jml Kg</th>
								<th class="page1 text-center">Jml Zak</th>
								<th class="page1 text-center">Ekspedisi</th>
								<th class="page2 text-center">Tgl Kirim</th>
								<th class="page2 text-center">Tgl Tiba</th>
								<th class="page2 text-center">No SJ</th>
								<th class="page2 text-center">Jml Kg</th>
								<th class="page2 text-center">Jml Zak</th>
								<th class="page2 text-center">Ekspedisi</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="17">Data tidak ditemukan.</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</form>
	</div>
</div>