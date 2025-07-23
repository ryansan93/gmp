<div class="col-xs-12 no-padding">
	<div class="col-xs-12 no-padding">
		<button type="button" class="col-xs-12 btn btn-success" onclick="dn.changeTabActive(this)" data-id="" data-edit="" data-href="action"><i class="fa fa-plus"></i> Tambah</button>
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
	<div class="col-xs-12 no-padding" style="margin-bottom: 10px;">
		<div class="col-xs-12 no-padding" style="padding-right: 5px;">
			<div class="col-xs-12 no-padding"><label class="control-label">Pelanggan</label></div>
			<div class="col-xs-12 no-padding">
				<select class="form-control pelanggan" data-required="1">
					<option value="all">ALL</option>
					<?php foreach ($pelanggan as $key => $value): ?>
						<option value="<?php echo $value['nomor']; ?>"><?php echo $value['nama']; ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding">
		<button type="button" class="col-xs-12 btn btn-primary" onclick="dn.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
	</div>
	<div class="col-xs-12 no-padding"><hr style="margin-top: 10px; margin-bottom: 10px;"></div>
	<div class="col-xs-12 action no-padding">
		<div class="col-lg-12 search left-inner-addon no-padding pull-right" style="margin-left: 10px;">
			<i class="glyphicon glyphicon-search"></i><input class="form-control" type="search" data-table="tbl_riwayat" placeholder="Search" onkeyup="filter_all(this)">
		</div>
	</div>
	<div class="col-xs-12 action no-padding">
        <span>* Klik pada baris untuk melihat detail.</span>
		<small>
			<table class="table table-bordered tbl_riwayat" style="margin-bottom: 0px;">
				<thead>
					<tr>
						<th class="col-xs-1">Tgl. DN</th>
						<th class="col-xs-1">No. DN</th>
						<th class="col-xs-2">Pelanggan</th>
						<th class="col-xs-2">Mitra</th>
						<th class="col-xs-4">Keterangan</th>
						<th class="col-xs-2">Nominal</th>
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
</div>