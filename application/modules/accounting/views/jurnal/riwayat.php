<div class="col-lg-12 no-padding">
	<div class="col-lg-8 search left-inner-addon no-padding d-flex align-items-center">
		<div class="col-sm-2 no-padding">
			<label class="control-label" style="padding-top: 0px;">Tanggal</label>
		</div>
		<div class="col-sm-3">
			<div class="input-group date datetimepicker" name="startDate" id="StartDate">
		        <input type="text" class="form-control text-center" placeholder="Start Date" data-required="1" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
		</div>
		<div class="col-sm-1 text-center no-padding" style="max-width: 4%;">s/d</div>
		<div class="col-sm-3">
			<div class="input-group date datetimepicker" name="endDate" id="EndDate">
		        <input type="text" class="form-control text-center" placeholder="End Date" data-required="1" />
		        <span class="input-group-addon">
		            <span class="glyphicon glyphicon-calendar"></span>
		        </span>
		    </div>
		</div>
		<div class="col-sm-2">
			<button id="btn-tampil" type="button" data-href="action" class="btn btn-primary cursor-p pull-left" title="TAMPIL" onclick="jurnal.getLists()">Tampilkan</button>
		</div>
	</div>
	<div class="col-lg-4 no-padding">
		<div class="col-sm-12 no-padding">
			<button type="button" class="btn btn-success pull-right" onclick="jurnal.changeTabActive(this)" data-href="action"><i class="fa fa-plus"></i> Tambah</button>
		</div>
	</div>
</div>
<div class="col-lg-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
<div class="col-md-12 search left-inner-addon pull-right no-padding" style="margin-bottom: 10px;">
	<input class="form-control" type="search" data-table="tbl_riwayat" placeholder="Search" onkeyup="jurnal.filter_all(this)">
</div>
<div class="col-lg-12 no-padding">
	<span>* Klik pada baris untuk melihat detail</span>
	<small>
		<table class="table table-bordered tbl_riwayat" style="margin-bottom: 0px;">
			<thead>
				<tr>
					<th class="col-lg-1 text-center">Tanggal</th>
					<th class="col-lg-6 text-center">Jurnal</th>
					<th class="col-lg-5 text-center">Unit</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="3">Data tidak ditemukan.</td>
				</tr>
			</tbody>
		</table>
	</small>
</div>