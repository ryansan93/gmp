<div class="col-xs-12 no-padding">
	<?php // if ( $akses['a_submit'] == 1 ) { ?>
		<div class="col-xs-12 no-padding">
			<button type="button" class="col-xs-12 btn btn-success" onclick="cn.changeTabActive(this)" data-id="" data-edit="" data-href="action"><i class="fa fa-plus"></i> Tambah</button>
		</div>
	<?php // } ?>
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
		<div class="col-xs-12 no-padding">
			<div class="col-xs-12 no-padding"><label class="label-control">Jenis CN</label></div>
			<div class="col-xs-12 no-padding">
				<select class="form-control jenis_cn" data-required="1">
					<option value="all">ALL</option>
					<?php foreach ($jenis_cn as $key => $value) { ?>
						<option value="<?php echo $key; ?>" data-jenis="<?php echo $value['jenis']; ?>"><?php echo strtoupper($value['nama']); ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
	</div>
	<div class="col-xs-12 no-padding">
		<button type="button" class="col-xs-12 btn btn-primary" onclick="cn.getLists()"><i class="fa fa-search"></i> Tampilkan</button>
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
						<th class="col-xs-1">Tgl. Pakai CN</th>
						<th class="col-xs-1">Jenis CN</th>
						<th class="col-xs-3">No. CN</th>
						<th class="col-xs-5">Nominal</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="4">Data tidak ditemukan.</td>
					</tr>
				</tbody>
			</table>
		</small>
	</div>
</div>