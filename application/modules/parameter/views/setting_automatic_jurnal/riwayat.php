<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding">
		<button type="button" class="col-xs-12 btn btn-success" onclick="saj.changeTabActive(this)" data-id="" data-edit="" data-href="action"><i class="fa fa-plus"></i> Tambah</button>
	</div>
	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-6 no-padding" style="padding-right: 5px;">
			<div class="col-xs-12 no-padding">
				<label class="label-control">Start Date</label>
			</div>
			<div class="col-xs-12 no-padding">
				<div class="input-group date datetimepicker" name="startDate" id="StartDate">
		            <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
		            <span class="input-group-addon">
		                <span class="glyphicon glyphicon-calendar"></span>
		            </span>
		        </div>
			</div>
		</div>
		<div class="col-xs-6 no-padding" style="padding-left: 5px;">
			<div class="col-xs-12 no-padding">
				<label class="label-control">End Date</label>
			</div>
			<div class="col-xs-12 no-padding">
				<div class="input-group date datetimepicker" name="endDate" id="EndDate">
		            <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" />
		            <span class="input-group-addon">
		                <span class="glyphicon glyphicon-calendar"></span>
		            </span>
		        </div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding">
		<button type="button" class="col-xs-12 btn btn-primary" onclick="saj.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
	</div>
	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
	<div class="col-xs-12 action no-padding">
		<div class="col-lg-12 search left-inner-addon no-padding pull-right" style="margin-left: 10px;">
			<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_riwayat" placeholder="Search" onkeyup="filter_all(this)">
		</div>
	</div>
	<div class="col-xs-12 action no-padding">
		<span>Klik pada baris untuk melihat detail</span>
		<small>
			<table class="table table-bordered tbl_riwayat" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="col-xs-1">Tgl. Berlaku</th>
						<th class="col-xs-3">Fitur</th>
						<th class="col-xs-3">Nama Tabel</th>
						<!-- <th class="col-xs-2">Keterangan</th>
						<th class="col-xs-1">No. COA</th>
						<th class="col-xs-2">Asal</th>
						<th class="col-xs-1">No. COA</th>
						<th class="col-xs-2">Tujuan</th> -->
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
</div>