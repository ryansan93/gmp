<?php if ( $akses['a_submit'] == 1 ) { ?>
	<div class="col-xs-12 no-padding" style="margin-bottom: 5px;">
		<button type="button" class="col-xs-12 btn btn-success pull-right" onclick="bk.changeTabActive(this)" data-href="action" data-edit=""><i class="fa fa-plus"></i> ADD</button>
	</div>

	<div class="col-xs-12 no-padding"><hr></div>
<?php } ?>

<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding-right: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Tgl Awal</label>
	</div>
	<div class="col-xs-12 no-padding">
		<div class="input-group date datetimepicker" name="startDate" id="StartDate">
	        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $start_date; ?>" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
</div>

<div class="col-xs-6 no-padding" style="margin-bottom: 5px; padding-left: 5px;">
	<div class="col-xs-12 no-padding">
		<label class="control-label">Tgl Akhir</label>
	</div>
	<div class="col-xs-12 no-padding">
		<div class="input-group date datetimepicker" name="endDate" id="EndDate">
	        <input type="text" class="form-control text-center" placeholder="Tanggal" data-required="1" data-tgl="<?php echo $end_date; ?>" />
	        <span class="input-group-addon">
	            <span class="glyphicon glyphicon-calendar"></span>
	        </span>
	    </div>
	</div>
</div>

<div class="col-xs-12 no-padding">
	<button type="button" class="col-xs-12 btn btn-primary pull-right tampilkan_riwayat" onclick="bk.getLists(this)"><i class="fa fa-search"></i> Tampilkan</button>
</div>

<div class="col-xs-12 no-padding"><hr></div>

<!-- <div class="col-xs-12 search left-inner-addon pull-right no-padding" style="padding-bottom: 10px;">
	<i class="fa fa-search"></i><input class="form-control" type="search" data-table="tbl_riwayat" placeholder="Search" onkeyup="filter_all(this)">
</div> -->

<div class="col-xs-12 no-padding">
	<small>
		<table class="table table-bordered tbl_riwayat">
			<thead>
				<tr>
					<th class="col-xs-1">Tanggal</th>
					<th class="col-xs-2">No. Bank Keluar</th>
					<th class="col-xs-2">Supplier</th>
					<th class="col-xs-3">Keterangan</th>
					<th class="col-xs-2">Nama Bank</th>
					<th class="col-xs-1">Nilai</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="6">Data tidak ditemukan.</td>
				</tr>
			</tbody>
		</table>
	</small>
</div>